Application.PartnerMasters = (function () {
    var recs_per_page = 22;
    var modURL = "?module=partner_masters";
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
      recs_per_page = finascop_update_recs_per_page(cmp);
    };
    var rbc_retailCategory = new Array();
    var masterPanelforStoreCategory = function (id) {
        var _mpanelforStoreCategory = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "Store Categorys",
          id: id,
          iconCls: "my-icon448",
          items: [
            StoreCategoryMainGrid(),
            new Ext.Panel({
              title: "Store Category Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              autoScroll: true,
              cls: "left_side_panel",
              id: "panelMasterStoreCategoryParent",
              height: winsize.height * 0.6,
              items: [StoreCategoryForm(), StoreCategoryMasterDetailsView(),RCinStoreCategory()],
              buttonAlign: "right",
              fbar: [
                /*<?php if (user_access("partner_masters", "saveStoreCategorys")) { ?> */ {
                  text: "Edit",
                  cls: "left-right-buttons",
                  iconCls: "edit",
                  id: "buttonMasterStoreCategoryEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
                  tabIndex: 4,
                  handler: function () {
                    var ID = Ext.getCmp(
                      "gridpanelMasterDataviewStoreCategorysdata"
                    )
                      .getSelectionModel()
                      .getSelections()[0].data.business_category_id;
                    Application.PartnerMasters.EditStoreCategorysView(ID);
                  },
                },
                {
                  text: "Cancel",
                  tabIndex: 5,
                  cls: "left-right-buttons",
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  id: "buttonMasterStoreCategoryCancel",
                  hidden: true,
                  handler: function () {
                    if (
                      !Ext.isEmpty(
                        Ext.getCmp("gridpanelMasterDataviewStoreCategorysdata")
                          .getSelectionModel()
                          .getSelections()
                      )
                    ) {
                      var ID = Ext.getCmp(
                        "gridpanelMasterDataviewStoreCategorysdata"
                      )
                        .getSelectionModel()
                        .getSelections()[0].data.business_category_id;
                      Application.PartnerMasters.ViewStoreCategorys(ID);
                    }
                  },
                },
                {
                  text: "Save",
                  tabIndex: 4,
                  cls: "left-right-buttons",
                  id: "buttonMasterStoreCategorySave",
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  hidden: true,
                  handler: function () {
                    saveStoreCategorys();
                  },
                } /*<?php } ?> */,
              ],
            }),
          ],
        });
        return _mpanelforStoreCategory;
      };
      var StoreCategoryForm = function () {
        var _storecategorysFormPanel = new Ext.form.FormPanel({
          id: "formpanelMasterStoreCategorys",
          frame: false,
          border: false,
          hidden: true,
          autoHeight: true,
          autoScroll: true,
          labelWidth: 120,
          labelAlign: "top",
          bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
          items: [
            {
              xtype: "spacer",
              height: 10,
            },
            {
              xtype: "textfield",
              fieldLabel: "Store Category",
              id: "business_category_name",
              name: "n[business_category_name]",
              anchor: "98%",
              allowBlank: false,
              tabIndex: 1,
              maxValue: 100,
              maxLength: 20,
            },RetailCategoryGrid(),{
              xtype: "spacer",
              height: 5,
            },
            {
              layout: "column",
              border: false,
              items:[
                {
                  layout: "form",
                  border: false,
                  columnWidth: 0.5,
                  items: [{
                    xtype: "checkbox",
                    id: "business_category_ingroup",
                    name: "n[business_category_ingroup]",
                    boxLabel: "Active Services",
                    allowBlank: true,
                    inputValue: 1,
                    listeners: {
                      check: function (checkbox, checked) {},
                    },
                  }]
                },
                {
                  layout: "form",
                  border: false,
                  columnWidth: 0.5,
                  items: [mkCombo({
                    type: STATUS_COMBO_DATA,
                    value: "id",
                    display: "text",
                    name: "n[status]",
                    fieldLabel: "Status",
                    tabIndex: 2,
                    emptyText: "Set status..",
                    id: "comboMasterStoreCategorysStatus",
                  })]
                }
              ]
            },
            {
              xtype: "textfield",
              id: "business_category_id",
              name: "n[business_category_id]",
              hidden: true,
            }      
          ],
          listeners: {
            afterrender: function () {
              if (Ext.isEmpty(Ext.getCmp("business_category_id").getValue())) {
                var recordSelected = Ext.getCmp(
                  "comboMasterStoreCategorysStatus"
                )
                  .getStore()
                  .getAt(0);
                Ext.getCmp("comboMasterStoreCategorysStatus").setValue(
                  recordSelected.get("id")
                );
              }
            },
          },
        });
        return _storecategorysFormPanel;
      };
      var StoreCategoryMasterDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          region:"south",
          border: false,
          hideBorders: true,
          height: winsize.height * 0.3,
          id: "panelMasterStoreCategorysDetailsView",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Name </th><td>  {business_category_name} </td></tr>',
            '<tr><th width="40%">Status</th><td>',
            "<tpl if=\"status == '1'\">Active</tpl>",
            "<tpl if=\"status == '0'\">Inactive</tpl>",
            "</td></tr>",
            "</table>",
            "</div>"
          ),
        });
      };
      var StoreCategorysMasterStore = function () {
        var _storecategorysMasterStore = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listStoreCategorys",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "business_category_id",
              root: "data",
            },
            [
              "business_category_id",
              "business_category_name",
              "status",
              "business_category_ingroup",
              "storeGroup",
            ]
          ),
          sortInfo: {
            field: "business_category_id",
            direction: "DESC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              Ext.getCmp("gridpanelMasterDataviewStoreCategorysdata")
                .getView()
                .refresh();
              Ext.getCmp("gridpanelMasterDataviewStoreCategorysdata")
                .getSelectionModel()
                .selectRow(0);
            },
          },
        });
        return _storecategorysMasterStore;
      };
      var StoreCategoryMainGrid = function () {
        var _storecategorysStore = StoreCategorysMasterStore();
        var _storecategorysGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "business_category_name",
            },
            {
              type: "string",
              dataIndex: "status",
            },
            {
              type: "string",
              dataIndex: "storeGroup",
            },
            {
              type: "list",
              options: ["Active", "Inactive"],
              phpMode: true,
              dataIndex: "status",
            },
          ],
        });
        _storecategorysGridFilter.remote = true;
        _storecategorysGridFilter.autoReload = true;
        var _storecategorysmaingridPanel = new Ext.grid.GridPanel({
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          loadMask: true,
          store: _storecategorysStore,
          iconCls: "money",
          id: "gridpanelMasterDataviewStoreCategorysdata",
          view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),
          plugins: [new Ext.ux.grid.GroupSummary(), _storecategorysGridFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Store Category",
              dataIndex: "business_category_name",
              sortable: true,
              tooltip: "Store Category",
              hideable: false,
            },
            {
              header: "Is Group",
              dataIndex: "business_category_ingroup",
              sortable: true,
              tooltip: "Is Group",
            },
            {
              header: "Store Group",
              dataIndex: "storeGroup",
              sortable: true,
              tooltip: "Store Group",
            },
            {
              header: "Status",
              dataIndex: "status",
              sortable: true,
              tooltip: "Status",
            },
          ],
          viewConfig: {
            forceFit: true,
          },
          bbar: new Ext.PagingToolbar({
            pageSize: recs_per_page,
            store: _storecategorysStore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedstorecategorys,
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("business_category_id");
              if (!Ext.isEmpty(ID)) {
                Application.PartnerMasters.Cache.business_category_id = ID;
                Ext.getCmp("formpanelMasterStoreCategorys").hide();
                Application.PartnerMasters.ViewStoreCategorys(ID);
              }
            },
            resize: onGridResize,
            afterrender: function () {
              _storecategorysStore.load();
            },
          },
          tbar: [
            {
              text: "Create Store Category",
              tooltip: "Create Store Category ",
              icon: "./resources/images/submenuicons/add.png",
              hidden:true,
              iconCls: "my-icon1",
              handler: function () {
                Application.PartnerMasters.StoreCategorysAddEdit = "Add";
                var storecategorysForm = Ext.getCmp(
                  "formpanelMasterStoreCategorys"
                ).getForm();
                Ext.getCmp("panelMasterStoreCategoryParent").setTitle(
                  "Create Store Category Details"
                );
                loadedForm = null;
                storecategorysForm.reset();
                Ext.getCmp("business_category_name").focus(false, 100);
                /*<?php if (user_access("partner_masters", "saveStoreCategorys")) { ?> */
                Ext.getCmp("gridStoreCatRetailCatList").hide();
                Ext.getCmp("buttonMasterStoreCategoryEdit").hide();
                Ext.getCmp("buttonMasterStoreCategorySave").show();
                /*<?php } ?> */
                Ext.getCmp("buttonMasterStoreCategoryCancel").show();
                Ext.getCmp("formpanelMasterStoreCategorys").show();
                Ext.getCmp("panelMasterStoreCategorysDetailsView").hide();
                Ext.getCmp("panelMasterStoreCategoryParent").doLayout();
                Ext.getCmp("gridStoreCatRetailCat")
                  .getStore()
                  .load({
                    params: {
                      business_category_id: 0,
                      edit_status: 0,
                    },
                  });
              },
            },
          ],
        });
        return _storecategorysmaingridPanel;
      };
      var saveStoreCategorys = function () {
        rbc_retailCategory = [];
    
        var store_fields = Ext.getCmp("gridStoreCatRetailCat")
          .getSelectionModel()
          .getSelections();
        for (var i = 0; i < store_fields.length; i++) {
          rbc_retailCategory[i] = store_fields[i].data.business_type_id;
        }
        var ptId = Ext.getCmp("business_category_id").getValue();
        if (
          !Ext.isEmpty(Ext.getCmp("business_category_name").getValue()) &&
          !Ext.isEmpty(Ext.getCmp("comboMasterStoreCategorysStatus").getValue())
        ) {
          Ext.Ajax.request({
            url: modURL + "&op=saveStoreCategorys",
            method: "POST",
            params: {
              rbc_retailCategory: rbc_retailCategory.toString(),
              id: Ext.getCmp("business_category_id").getValue(),
              name: Ext.getCmp("business_category_name").getValue(),
              status: Ext.getCmp("comboMasterStoreCategorysStatus").getValue(),
              business_category_ingroup: Ext.getCmp(
                "business_category_ingroup"
              ).getValue(),
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (
                  Application.PartnerMasters.StoreCategorysAddEdit == "Add"
                ) {
                  recs_per_page = updateRecsPerPage(
                    Ext.getCmp("gridpanelMasterDataviewStoreCategorysdata")
                  );
                  Ext.getCmp("formpanelMasterStoreCategorys").getForm().reset();
                  Ext.getCmp(
                    "gridpanelMasterDataviewStoreCategorysdata"
                  ).store.reload({
                    params: {
                      start: 0,
                      limit: recs_per_page,
                    },
                  });
                } else {
                  Ext.getCmp("gridpanelMasterDataviewStoreCategorysdata")
                    .getStore()
                    .load({
                      callback: function (record, options, success) {
                        var gridPanel = Ext.getCmp(
                          "gridpanelMasterDataviewStoreCategorysdata"
                        );
                        var index = gridPanel.store.find(
                          "business_category_id",
                          ptId
                        );
                        gridPanel.getSelectionModel().selectRow(index);
                      },
                    });
                  //                            Ext.getCmp('gridpanelMasterDataviewStoreCategorysdata').selModel.getSelected().data = tmp.data;
                  //                            Ext.getCmp('gridpanelMasterDataviewStoreCategorysdata').getStore().reload();
                  //                            Ext.getCmp('gridpanelMasterDataviewStoreCategorysdata').getView().refresh();
                }
                Application.PartnerMasters.StoreCategorysAddEdit = "";
                Application.PartnerMasters.ViewStoreCategorys(
                  tmp.data.business_category_id
                );
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
      var gridSelectionChangedstorecategorys = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelMasterDataviewStoreCategorysdata")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelMasterDataviewStoreCategorysdata")
            .getSelectionModel()
            .getSelections()[0].data.business_categorye_id;
          Application.PartnerMasters.ViewStoreCategorys(ID);
        }
      };
      var RetailCategoryGrid = function () {
        var _bcRetailCategGridStore = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=getBusinessCatRetailCat",
          method: "post",
          fields: ["business_type_id", "business_type_name", "checked"],
          // root: 'data',
          remoteSort: true,
        });
        var ck_selection = new Ext.grid.CheckboxSelectionModel({
          multiSelect: true,
          checkOnly: true,
          listeners: {
            rowdeselect: function (sm, rowIndex, record) {
              var ind = rbc_retailCategory.indexOf(record.get("business_type_id"));
              if (ind > -1) rbc_retailCategory.splice(ind, 1);
              record.set("checked", "false");
            },
            rowselect: function (sm, rowIndex, record) {
              var ind = rbc_retailCategory.indexOf(record.get("business_type_id"));
              if (ind == -1)
                rbc_retailCategory.push(record.get("business_type_id"));
              record.set("checked", "true");
            },
          },
        });
        var __rcGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "business_type_name",
            },
          ],
        });
        __rcGridFilter.remote = true;
        __rcGridFilter.autoReload = true;
        var _albumProjectEventGrid = new Ext.grid.GridPanel({
          id: "gridStoreCatRetailCat",
          region: "center",
          height: 200,
          width: winsize.width * 0.38,
          frame: true,
          border: false,
          autoScroll: true,
          store: _bcRetailCategGridStore,
          selModel: ck_selection,
          viewConfig: {
            forceFit: true,
          },
          plugins: [__rcGridFilter],
          fields: ["business_type_id", "business_type_name"],
          colModel: new Ext.grid.ColumnModel({
            columns: [
              ck_selection,
              {
                header: "Retail Category",
                dataIndex: "business_type_name",
              },
            ],
          }),
          iconCls: "icon-grid",
          listeners: {
            afterrender: function () {
              {
                var me = this;
                _bcRetailCategGridStore.on("load", function () {
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
      var RCinStoreCategory = function(){
        var _bcRetailCategGridStore = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=listStoreCatRetailCat",
          method: "post",
          fields: ["business_type_id", "business_type_name", "checked"],
          remoteSort: true,
        });
        
        var __rcGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "business_type_name",
            },
          ],
        });
        __rcGridFilter.remote = true;
        __rcGridFilter.autoReload = true;
        var _albumProjectEventGrid = new Ext.grid.GridPanel({
          id: "gridStoreCatRetailCatList",
          region: "north",
          height: 200,
          width: winsize.width * 0.38,
          frame: true,
          hidden:true,
          border: false,
          autoScroll: true,
          store: _bcRetailCategGridStore,
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true
        }),
          viewConfig: {
            forceFit: true,
          },
          plugins: [__rcGridFilter],
          fields: ["business_type_id", "business_type_name"],
          colModel: new Ext.grid.ColumnModel({
            columns: [
              {
                header: "Retail Category",
                dataIndex: "business_type_name",
              },
            ],
          }),
          iconCls: "icon-grid",
          listeners: {
            afterrender: function () {
              
            },
          },
        });
        return _albumProjectEventGrid;
      };

      var gridSelectionChangedcat = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelPrivateCategory")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelPrivateCategory")
            .getSelectionModel()
            .getSelections()[0].data.vc_id;
          Ext.getCmp("tabpanelPrivateCategory").setActiveTab(0);
          Application.PartnerMasters.Cache.vc_id = ID;
          Application.PartnerMasters.ViewMode(ID);
        } else {
          Application.PartnerMasters.Cache.vc_id = 0;
          Application.PartnerMasters.ViewMode(0);
        }
      };
    
      var ListmainPvtCPanel = function (id) {
        var panel = new Ext.Panel({
          layout: "border",
          border: false,
          frame: false,
          bodyStyle: { "background-color": "white" },
          hideBorders: true,
          id: id,
          title: "Private Categories",
          items: [PvtCatmainGrid(), pvtCatTabPanel()],
        });
        return panel;
      };
      var vcGridStore = function () {
        var store = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=listPrivateCategory",
          method: "post",
          fields: [
            "vc_id",
            "vc_name",
            "vc_parentCategoryId",
            "vc_categoryId",
            "parent_category",
            "category_name",
            "vc_status",
            "hasImage","store_group_name"
          ],
          totalProperty: "totalCount",
          root: "data",
          listeners: {
            beforeload: function () {},
            load: function () {
              loadCount++;
              if (loadCount == 1) {
                Ext.getCmp("gridpanelPrivateCategory")
                  .getSelectionModel()
                  .selectRow(0);
              }
              //console.log('loadCount: ' + loadCount);
            },
          },
        });
        return store;
      };
      var PvtCatmainGrid = function () {
        var grid_store = vcGridStore();
        var customerGrid_filter = new Ext.ux.grid.GridFilters({
          remote: true,
          filters: [
            {
              type: "string",
              dataIndex: "vc_name",
            },{
                type: "string",
                dataIndex: "store_group_name",
              },
            {
              type: "string",
              dataIndex: "parent_category",
            },
            {
              type: "string",
              dataIndex: "category_name",
            },
            {
              type: "list",
              options: ["Active", "Inactive"],
              phpMode: true,
              dataIndex: "vc_status",
            },
          ],
        });
        customerGrid_filter.remote = true;
        customerGrid_filter.autoReload = true;
    
        var SP_grid = new Ext.grid.GridPanel({
          store: grid_store,
          id: "gridpanelPrivateCategory",
          region: "center",
          width: winsize.width * 0.5,
          frame: true,
          border: false,
          layout: "fit",
          loadMask: true,
          plugins: [customerGrid_filter],
          columns: [
            {
              header: "Private Category",
              sortable: true,
              dataIndex: "vc_name",
              width: 175,
            },
            {
              header: "Department ",
              sortable: true,
              dataIndex: "parent_category",
              width: 175,
            },
            {
              header: "Category",
              sortable: true,
              dataIndex: "category_name",
              width: 175,
            },
            {
                header: "Store Group",
                sortable: true,
                dataIndex: "store_group_name",
              },
            {
              header: "Status",
              dataIndex: "vc_status",
              sortable: true,
              tooltip: "Status",
            },
            {
              header: "Has Image",
              dataIndex: "hasImage",
              sortable: true,
              tooltip: "Has Image",
            },
            {
              xtype: "actioncolumn",
              header: "Action",
              hideable: true,
              iconCls: "downarrow",
              tooltip: "Choose Actions",
              listeners: {
                click: function (a, grid, rowindex, e) {
                  var record = grid.store.getAt(rowindex);
                  grid.getSelectionModel().selectRow(rowindex);
                  vcActionMenu.showAt(e.getXY());
                  //action
                },
              },
            },
          ],
          viewConfig: {
            forceFit: true,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          },
          tbar: [
            {
              xtype: "button",
              hidden:true,
              text: "Create Private Category",
              tooltip: "Create Private Category",
              iconCls: "finascop_add",
              handler: function () {
                Application.PartnerMasters.addNewPrivateCategory(0);
              },
            },
          ],
          bbar: new Ext.PagingToolbar({
            pageSize: recs_per_page,
            store: grid_store,
            displayInfo: true,
            plugins: [customerGrid_filter],
            displayMsg: "Displaying items {0} - {1} of {2}",
            emptyMsg: "No records to display",
          }),
          stripeRows: true,
          sm: new Ext.grid.RowSelectionModel({
            singleSelected: true,
            listeners: {
              selectionchange: gridSelectionChangedcat,
            },
          }),
          listeners: {
            rowclick: function (grid, rowIndex, e) {},
          },
        });
        return SP_grid;
      };
      var vcActionMenu = new Ext.menu.Menu({
        items: [
          {
            text: "Edit Private Category",
            handler: function () {
              var vc_id = Ext.getCmp("gridpanelPrivateCategory")
                .getSelectionModel()
                .getSelections()[0].data.vc_id;
    
              Application.PartnerMasters.addNewPrivateCategory(vc_id);
            },
          },
          {
            text: "Add Items",
            handler: function () {
              addVendorItem(Application.PartnerMasters.Cache.vc_id);
            },
          },
          {
            text: "Upload Image",
            handler: function () {
              var vc_id = Ext.getCmp("gridpanelPrivateCategory")
                .getSelectionModel()
                .getSelections()[0].data.vc_id;
    
              var uploadtype = "virtualCategory";
              Ext.Ajax.request({
                url: modURL + "&op=getPvtCImage",
                method: "POST",
                params: {
                  vc_id: vc_id,
                },
                success: function (res) {
                  var tmp = Ext.decode(res.responseText);
                  console.log("temp is -", tmp);
                  if (tmp.data != "") {
                    var img_url = tmp.data[0].image_url;
                    Application.PartnerMasters.uploadimageCategory(
                      vc_id,
                      uploadtype,
                      img_url
                    );
                  } else {
                    var img_url = "";
                    Application.PartnerMasters.uploadimageCategory(
                      vc_id,
                      uploadtype,
                      img_url
                    );
                  }
                },
              });
            },
          },
          {
            text: "Status Change",
            handler: function () {
              var vc_id = Ext.getCmp("gridpanelPrivateCategory")
                .getSelectionModel()
                .getSelections()[0].data.vc_id;
              var vc_status = Ext.getCmp("gridpanelPrivateCategory")
                .getSelectionModel()
                .getSelections()[0].data.vc_status;
              Ext.Ajax.request({
                url: modURL + "&op=statusChange",
                method: "POST",
                waitMsg: "Processing",
                params: {
                  vc_id: vc_id,
                  vc_status: vc_status,
                },
                failure: function (response, options) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", "tmp.msg");
                },
                success: function (response, options) {
                  var tmp = Ext.decode(response.responseText);
    
                  if (tmp.success === true) {
                    Application.example.msg("Success", tmp.msg);
                    Ext.getCmp("gridpanelPrivateCategory").getStore().load();
                  } else {
                    Ext.MessageBox.alert("Error", tmp.msg);
                  }
                },
              });
            },
          },
        ],
      });
      var pvtCatTabPanel = function () {
        var panel = new Ext.TabPanel({
          region: "east",
          width: winsize.width * 0.5,
          height: winsize.height * 0.6,
          activeTab: 0,
          flex: 1,
          plain: true,
          frame: true,
          id: "tabpanelPrivateCategory",
          items: [
            {
              title: "Details",
              id: "property_grid_id",
              width: winsize.width * 0.5,
              tpl: new Ext.XTemplate(
                '<div class="details-outer">',
                '<table border="0" width="99%" class="details_view_table">',
                "<tr><th>Private Category :</th><td> {vc_name} </td></tr>",
                "<tr><th>In Home Menu:</th><td>  {vc_isHome}</td></tr>",
                "<tr><th>In Category List:</th><td>  {vc_isInCategory}</td></tr>",
                "<tpl if=\"vc_isInCategory == 'Yes'\">",
                "<tr><th>Department:</th><td>  {parent_category}</td></tr>",
                "<tr><th>Category:</th><td>  {category_name}</td></tr>",
                "</tpl>",
                "<tr><th>Status:</th><td>  {vc_status}</td></tr>",
                '<tpl if="image_url != null">',
                "<tpl if=\"image_url != ''\">",
                "<tr><td>",
                '<div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div>',
                "</td></tr>",
                "</tpl>",
                "</tpl>",
                "</table>",
                "</div>"
              ),
            },
            {
              title: "Items",
              frame: false,
              width: winsize.width * 0.6,
              border: false,
              items: [additemGrid()],
            },
          ],
          listeners: {
            tabchange: function (sd, tab) {},
          },
        });
        return panel;
      };
      var additemGrid = function () {
        var addItemStorenew = addItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
          //remote: true,
          local: true,
          filters: [
            {
              type: "string",
              dataIndex: "itemName",
            },
            {
              type: "string",
              dataIndex: "itemType",
            },
            {
              type: "string",
              dataIndex: "stit_brand_name",
            },
            {
              type: "string",
              dataIndex: "stit_quantity",
            },
            {
              type: "string",
              dataIndex: "least_package_type_name",
            },
          ],
        });
        vendoritem_filter.remote = true;
        vendoritem_filter.autoReload = true;
        var addItem = new Ext.grid.GridPanel({
          store: addItemStorenew,
          height: winsize.height * 0.7,
          frame: true,
          border: false,
          layout: "fit",
          region: "center",
          loadMask: true,
          plugins: [vendoritem_filter],
          id: "gridpanelVendorAdditem",
          columns: [
            {
              header: "Item Name",
              width: 400,
              dataIndex: "itemName",
              hideable: true,
              sortable: true,
            },
            {
              header: "Item Type",
              dataIndex: "itemType",
              hideable: true,
              sortable: true,
            },
            {
              header: "Sub Category",
              dataIndex: "stit_category_name",
              hideable: true,
              sortable: true,
            },
            {
              header: "Brand",
              dataIndex: "stit_brand_name",
              hideable: true,
              sortable: true,
            },
            {
              header: "Quantity",
              dataIndex: "stit_quantity",
              hideable: true,
              sortable: false,
            },
            {
              header: "Least Packing Unit",
              dataIndex: "least_package_type_name",
              hideable: true,
              sortable: true,
            },
            {
              xtype: "actioncolumn",
              header: "Actions",
              hideable: false,
              groupable: false,
              width: 80,
              items: [
                {
                  iconCls: "remove-enquiry",
                  tooltip: "Delete Order",
                  handler: function (grid, rowIndex, colIndex) {
                    var record = grid.store.getAt(rowIndex);
                    deleteItem(record.get("stpi_id"), record.get("vc_id"));
                  },
                },
              ],
            },
          ],
          tbar: new Ext.Toolbar({
            items: [
              {
                xtype: "button",
                text: "Add Items",
                tooltip: "Add Items",
                iconCls: "add",
                handler: function () {
                  addVendorItem(Application.PartnerMasters.Cache.vc_id);
                },
              },
            ],
          }),
          viewConfig: {
            forceFit: true,
          },
          listeners: {
            rowclick: function (grid, rowIndex, e) {},
          },
        });
        return addItem;
      };
      var deleteItem = function (id, vc_id) {
        Ext.MessageBox.confirm(
          "Confirm",
          "Do you want to remove this item?",
          function (btn, text) {
            if (btn == "yes") {
              Ext.Ajax.request({
                waitMsg: "Processing",
                method: "POST",
                url: modURL + "&op=deletePvtCItem",
                params: {
                  id: id,
                },
                success: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  if (tmp.success === true) {
                    Application.example.msg("Success", "Removed item");
                    Ext.getCmp("gridpanelVendorAdditem")
                      .getStore()
                      .load({
                        params: {
                          vc_id: vc_id,
                        },
                      });
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", "Error occurred");
                },
              });
            }
          }
        );
      };
      var addItemStore = function () {
        var store = new Ext.data.JsonStore({
          method: "post",
          url: modURL + "&op=listitempvtcat",
          fields: [
            "itemType",
            "itemId",
            "itemName",
            "stit_type",
            "stpi_id",
            "stit_brand_name",
            "stit_quantity",
            "least_package_type_name",
            "vc_id",
            "stit_category_name",
          ],
          remoteSort: true,
          root: "data",
          totalProperty: "totalCount",
        });
        return store;
      };
      var addVendorItem = function (cid) {
        current_type = 1;
        var resultWindow = new Ext.Window({
          id: "windowFinascopStockAddvenderitemCreatevendoritem",
          title: "Private Category Items",
          //iconCls: 'vender-items',
          shadow: false,
          height: 400,
          width: 800,
          layout: "fit",
          resizable: false,
          plain: true,
          constrain: true,
          draggable: true,
          modal: true,
          items: [vendorGrid(cid)],
          buttons: [
            {
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
              text: "Cancel",
              handler: function () {
                Ext.getCmp(
                  "windowFinascopStockAddvenderitemCreatevendoritem"
                ).close();
              },
            },
            {
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
              text: "Save",
              handler: function () {
                var selectitem = Ext.getCmp(
                  "gridFinascopStockVenderitemGridgeneration"
                )
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                var itemarr = [];
                for (var i = 0; i < selectedcount; i++) {
                  itemarr.push(selectitem[i].data.stit_ID);
                }
                if (selectedcount != 0) {
                  Application.PartnerMasters.Cache.cid = cid;
                  Application.PartnerMasters.Cache.itemarr = itemarr;
                  var itemType = Ext.getCmp(
                    "radiobuttonLngConfId"
                  ).getValue();
                  Application.PartnerMasters.Cache.itemType = itemType;
                  Application.PartnerMasters.saveCheckedItem(
                    cid,
                    itemarr,
                    itemType
                  );
                } else {
                  Ext.MessageBox.alert(
                    "Notification",
                    "Please check,Some box entries are not valid."
                  );
                }
              },
            },
          ],
          listeners: {
            afterrender: function () {
              if (_SESSION.IS_MEDICINE_REQUIRED != 1) {
                Ext.getCmp("radiobuttonLngConfId").hide();
              }
            },
          },
        });
    
        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
      };
      var vendorGrid = function (cid) {
        var vendorcol = venderItemColmodel();
        var venderstore = venderItemStore();
        var vendorlist = vendortb();
    
        var vendor_filter = new Ext.ux.grid.GridFilters({
          remote: true,
          filters: [
            {
              type: "string",
              dataIndex: "stit_SKU",
            },
            {
              type: "string",
              dataIndex: "stit_category_name",
            },
            {
              type: "string",
              dataIndex: "stit_brand_name",
            },
            {
              type: "string",
              dataIndex: "stit_itemName",
            },
          ],
        });
        vendor_filter.remote = true;
        vendor_filter.autoReload = true;
    
        var vendorItemgrid = new Ext.grid.GridPanel({
          hideMode: "display",
          loadMask: true,
          store: venderstore,
          colModel: vendorcol,
          tbar: vendorlist,
          plugins: [vendor_filter],
          width: 800,
          height: 400,
          frame: false,
          border: false,
          hideBorders: true,
          //iconCls: 'icon-grid',
          id: "gridFinascopStockVenderitemGridgeneration",
          sm: check_box,
          viewConfig: {
            forceFit: true,
          },
        });
        return vendorItemgrid;
      };
      var rowno = new Ext.grid.RowNumberer();
      rowno.width = 30;
      var check_box = new Ext.grid.CheckboxSelectionModel({
        multiSelect: true,
      });
      var venderItemColmodel = function () {
        var colmodel = new Ext.grid.ColumnModel({
          sortable: true,
          columns: [
            check_box,
            rowno,
            {
              header: "SKU Name",
              width: 200,
              dataIndex: "stit_SKU",
              sortable: true,
            },
            {
              header: "Product Master",
              dataIndex: "stit_itemName",
              hideable: true,
              sortable: true,
            },
            {
              header: "Brand",
              dataIndex: "stit_brand_name",
              hideable: true,
              sortable: true,
            },
            {
              header: "Category",
              dataIndex: "mainCategory",
              hideable: true,
              sortable: false,
            },
            {
              header: "Sub Category",
              dataIndex: "stit_category_name",
              hideable: true,
              sortable: false,
            },
          ],
        });
        return colmodel;
      };
      var venderItemStore = function () {
        var store = new Ext.data.JsonStore({
          method: "POST",
          url: modURL + "&op=pvtCatitemlisting",
          totalProperty: "totalCount",
          root: "data",
          remoteSort: true,
          autoLoad: false,
          fields: [
            "stit_itemName",
            "stit_ID",
            "stit_brand_name",
            "stit_SKU",
            "stit_quantity",
            "least_package_type_name",
            "stit_category_name",
            "mainCategory",
            "department",
          ],
          listeners: {
            beforeload: function (thisStore, options) {
              thisStore.baseParams.current_type = current_type;
            },
          },
        });
    
        return store;
      };
      var vendortb = function () {
        var tbar = new Ext.Toolbar({
          //layout: 'column',
          style: "margin:5px 1px 5px 1px;",
          //labelWidth: 100,
          labelAlign: "left",
          frame: false,
          border: false,
          hideBorders: true,
          items: [
            {
              xtype: "radiogroup",
              width: 150,
              id: "radiobuttonLngConfId",
              //columnWidth: 0.25,
              items: [
                {
                  boxLabel: "Medicine",
                  name: "rb-auto",
                  inputValue: 1,
                  labelWidth: 100,
                },
                {
                  boxLabel: "Product",
                  name: "rb-auto",
                  inputValue: 2,
                  labelWidth: 100,
                  checked: true,
                },
              ],
              listeners: {
                change: function (event, checked) {
                  var current_firstid = event.items.items[0].inputValue;
                  var current_secondid = event.items.items[1].inputValue;
                  //var current_thirdid = event.items.items[2].inputValue;
                  var radioid = Ext.getCmp("radiobuttonLngConfId").getValue();
    
                  if (radioid == current_secondid) {
                    var item_name = "";
                    //filterItems(item_name, radioid);
                  } else if (radioid == current_firstid) {
                    var item_name = "";
                    //filterItems(item_name, radioid);
                  }
                },
              },
            },
            {
              //columnWidth: 0.4,
              xtype: "textfield",
              id: "radiosearch",
              width: 500,
              listeners: {
                specialkey: function (field, e) {
                  if (e.getKey() == e.ENTER) {
                    var item_search_item = Ext.getCmp(
                      "radiobuttonLngConfId"
                    ).getValue();
                    var search_bar = Ext.getCmp("radiosearch").getValue();
                    if (item_search_item != 0 && search_bar != "") {
                      filterItems(search_bar, item_search_item);
                    }
                  }
                },
              },
            },
            {
              frame: false,
              border: false,
              // columnWidth: 0.025
            },
            {
              icon: IMAGE_BASE_PATH + "/default/icons/search.png",
              xtype: "button",
              //columnWidth: 0.075,
              text: "Search",
              handler: function () {
                var item_search_item = Ext.getCmp(
                  "radiobuttonLngConfId"
                ).getValue();
                var search_bar = Ext.getCmp("radiosearch").getValue();
                if (item_search_item != 0 && search_bar != "") {
                  filterItems(search_bar, item_search_item);
                }
              },
            },
            {
              frame: false,
              border: false,
              // columnWidth: 0.025
            },
            {
              icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
              xtype: "button",
              //columnWidth: 0.075,
              text: "Reset",
              handler: function () {
                Ext.getCmp("radiosearch").reset();
                filterItems("", "");
                //Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getStore().reload();
              },
            },
          ],
        });
    
        return tbar;
      };
      var filterItems = function (item_name, radio_id) {
        var gridvalue = Ext.getCmp(
          "gridFinascopStockVenderitemGridgeneration"
        ).getStore();
        current_type = radio_id;
    
        gridvalue.baseParams = {
          currentItem: item_name,
          current_type: radio_id,
        };
        gridvalue.load();
      };
      var categoryuploadForm = function (img) {
        return new Ext.Panel({
          height: "400",
          items: [
            new Ext.Panel({
              layout: "fit",
              id: "cat_main_image_panel",
              tpl: new Ext.XTemplate(
                '<div class="details-outer">',
                '<img width="200" id="demo123" height="200" src="{img_root}"></img>',
                "</div>"
              ),
            }),
            {
              xtype: "hidden",
              id: "aws_file_location",
              name: "aws_file_location",
            },
            {
              xtype: "hidden",
              id: "aws_file_bucket",
              name: "aws_file_bucket",
            },
            new Ext.form.FormPanel({
              id: "category_image_upload",
              layout: "form",
              fileUpload: true,
              autoHeight: true,
              frame: true,
              items: [
                {
                  xtype: "hidden",
                  id: "file_name",
                  name: "file_name",
                },
                {
                  xtype: "hidden",
                  id: "albumBucketName",
                  name: "albumBucketName",
                },
                {
                  xtype: "hidden",
                  id: "accessKey",
                  name: "accessKey",
                },
                {
                  xtype: "hidden",
                  id: "secretKey",
                  name: "secretKey",
                },
                {
                  xtype: "hidden",
                  id: "bucketRegion",
                  name: "bucketRegion",
                },
                {
                  xtype: "hidden",
                  id: "oncompleteurl",
                  name: "oncompleteurl",
                },
                {
                  xtype: "hidden",
                  id: "img_path_db",
                  name: "img_path_db",
                },
                {
                  xtype: "box",
                  width: 200,
                  height: 200,
                  id: "exist_img_box",
                  autoEl: { tag: "img", src: img, width: 200, height: 200 },
                },
              ],
              buttons: [
                {
                  xtype: "fileuploadfield",
                  id: "categoryimg_file",
                  anchor: "98%",
                  fieldLabel: "Select File",
                  name: "file",
                  allowBlank: true,
                  buttonOnly: true,
                  // hidden: true,
                  buttonCfg: {
                    text: "Choose Image",
                    //iconCls: 'finascop_upload_file',
                    width: 80,
                  },
                  validator: function (v) {
                    if (v != "") {
                      v = v.toLowerCase();
                      var exp = /^.*\.(png|jpg|gif)$/i;
                      if (!exp.test(v)) {
                        Ext.Msg.alert("Notification", "Upload a valid image file");
                        return;
                        //return 'Upload a valid image file of format JPG.';
                      }
    
                      var categoryimg_file =
                        Ext.getCmp("categoryimg_file").getValue();
                      if (categoryimg_file == "") {
                        Ext.Msg.alert(
                          "Notification",
                          "Please choose a scanned file to upload"
                        );
                        return;
                      }
    
                      var category_image_upload = Ext.getCmp(
                        "category_image_upload"
                      ).getForm();
                      if (category_image_upload.isValid()) {
                        Ext.getCmp("exist_img_box").hide();
                        winLoadMask.show();
                        addPhoto();
                      }
                      return true;
                    }
                  },
                },
              ],
            }),
          ],
        });
        // });
      };
      function uuidv4() {
        return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
          /[xy]/g,
          function (c) {
            var r = (Math.random() * 16) | 0,
              v = c == "x" ? r : (r & 0x3) | 0x8;
            return v.toString(16);
          }
        );
      }
      function addPhoto() {
        var albumBucketName = Ext.getCmp("albumBucketName").getValue();
        var bucketRegion = Ext.getCmp("bucketRegion").getValue();
        var filepath = Ext.getCmp("oncompleteurl").getValue();
        AWS.config.update({
          region: bucketRegion,
          credentials: new AWS.Credentials(
            Ext.getCmp("accessKey").getValue(),
            Ext.getCmp("secretKey").getValue(),
            null
          ),
        });
        var s3 = new AWS.S3({
          apiVersion: "2006-03-01",
          params: { Bucket: albumBucketName },
        });
        var files = document.getElementById("categoryimg_file-file").files;
        if (!files.length) {
          winLoadMask.hide();
          return alert("Please choose a file to upload first.");
        }
        var file = files[0];
        var actualfileName = file.name;
        var file_Name = JSON.stringify(actualfileName).slice(1, -1);
        var fileExt = file_Name.split(".").pop();
    
        var fileName = uuidv4();
        fileName = fileName + "." + fileExt;
    
        s3.upload(
          {
            Key: filepath + fileName /*file_Name*/ /*from server*/,
            Body: file,
            ACL: "public-read",
          },
          function (err, data) {
            if (err) {
              winLoadMask.hide();
              var img_src = Ext.BLANK_IMAGE_URL;
              Ext.getCmp("cat_main_image_panel").update({ img_root: img_src });
              return Ext.Msg.alert(
                "Notification",
                "There was an error uploading your photo: " + err.message
              );
            }
            if (!Ext.isEmpty(data.Location)) {
              winLoadMask.hide();
              Application.example.msg(
                "Notification",
                "File uploaded successfully."
              );
              Application.PartnerMasters.UploadedFileLocation = data.Location;
              Application.PartnerMasters.UploadedFileBucket = data.Bucket;
              Ext.getCmp("aws_file_bucket").setValue(data.Bucket);
              Ext.getCmp("aws_file_location").setValue(data.Location);
              /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
              Ext.getCmp("cat_main_image_panel").update({
                img_root: Application.PartnerMasters.UploadedFileLocation,
              });
            }
          }
        );
      }

      var ConfiguredMobileStore = function () {
        var store = new Ext.data.JsonStore({
          method: "post",
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listConfiguredMobiles",
            method: "post",
          }),
          fields: ["otp","mobile"],
          totalProperty: "totalCount",
          root: "data",
          autoLoad: true,
          listeners: {
            load: function () {},
          },
        });
        return store;
      };
      var masterPanelGridforconfiguredMobiles = function (id) {
        var confMobile_store = ConfiguredMobileStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "mobile",
            },
          ],
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;
    
        var grid_panel = new Ext.grid.GridPanel({
          store: confMobile_store,
          layout: "fit",
          frame: false,
          border: false,
          title: "OTP Configured Mobiles",
          plugins: [branch_filter],
          id: id,
          loadMask: true,
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Mobile",
              sortable: true,
              hideable: true,
              dataIndex: "mobile",
              tooltip: "Mobile",
            },
            {
              header: "OTP",
              sortable: true,
              hideable: true,
              dataIndex: "otp",
              tooltip: "OTP",
            },{
              xtype: "actioncolumn",
              header: "Actions",
              hideable: false,
              groupable: false,
              width: 80,
              items: [
                {
                  iconCls: "remove-enquiry",
                  tooltip: "Delete",
                  handler: function (grid, rowIndex, colIndex) {
                    var record = grid.store.getAt(rowIndex);
                    Ext.MessageBox.confirm(
                      "Confirm",
                      "Do you want to remove this item?",
                      function (btn, text) {
                        if (btn == "yes") {
                          Ext.Ajax.request({
                            waitMsg: "Processing",
                            method: "POST",
                            url: modURL + "&op=deleteMobiles",
                            params: {
                              mobile: record.get("mobile"),
                            },
                            success: function (response) {
                              var tmp = Ext.util.JSON.decode(response.responseText);
                              if (tmp.success === true) {
                                Application.example.msg("Success", "Removed mobile");
                                Ext.getCmp(id)
                                  .getStore()
                                  .load();
                              }
                            },
                            failure: function (response) {
                              var tmp = Ext.util.JSON.decode(response.responseText);
                              Ext.MessageBox.alert("Error", "Error occurred");
                            },
                          });
                        }
                      }
                    );
                  },
                },
              ],
            }
          ],
          viewConfig: {
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          },
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
          }),
          listeners: {
            resize: onGridResize,
            afterrender: function () {},
          },
          tbar: [{
            html: '&nbsp;Mobile : &nbsp;',
        }, {
            fieldLabel: 'Mobile',
            xtype: 'textfield',
            id: 'confmobile',
            name: 'confmobile',
            tabIndex: 102,
            width: 80,
            anchor: '97%',
        }, {
          html: '&nbsp;OTP : &nbsp;',
      }, {
          fieldLabel: 'OTP',
          xtype: 'numberfield',
          id: 'confotp',
          name: 'confotp',
          tabIndex: 102,
          width: 80,
          anchor: '97%',
      },{
        html: '&nbsp;&nbsp;',
    }, {
        text: 'Save',
        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
        tabIndex: 103,
        handler: function () {
          

          var t = new Date();
          var t_stamp = t.format("YmdHis");

          var confmobile = Ext.getCmp('confmobile').getValue();
          var confotp = Ext.getCmp('confotp').getValue();

          if ((confmobile > 0) && (confotp > 0)) {
                  Ext.Ajax.request({
                      url: modURL + '&op=saveConfiguredMobiles',
                      params: {confmobile: confmobile, confotp: confotp},
                      failure: function (response) {
                          Ext.MessageBox.alert('Error', response.responseText);

                      },
                      success: function (response, options) {
                        var tmp = Ext.decode(response.responseText);
                        console.log('tmp', tmp);
                        if (tmp.success === true) {
                            Application.example.msg('Success', tmp.message);
                            Ext.getCmp('panelMasterMainConfiguredMobiles').getStore().load();
                            Ext.getCmp('confmobile').reset();
                            Ext.getCmp('confotp').reset();
                        } else if (tmp.success === false) {
                            Ext.Msg.alert('Error', tmp.message);
                        } else if (tmp.success === false && tmp.valid === false) {
                            Ext.Msg.alert('Error', tmp.message);
                        } else {
                            Ext.Msg.alert('Error', tmp.message);
                        }
                      }
                  });
              


          } else {
              Ext.Msg.alert('Notification', 'Please enter all required fields');
          }
        }
    }
          ],
          bbar: new Ext.PagingToolbar({
            pageSize: recs_per_page,
            store: confMobile_store,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No records to display",
          }),
          stripeRows: true,
        });
        return grid_panel;
      };
      var Languagestore = function () {
        var store = new Ext.data.JsonStore({
          method: "post",
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listLanguages",
            method: "post",
          }),
          fields: ["languageName","languageCode","id"],
          totalProperty: "totalCount",
          root: "data",
          autoLoad: true,
          listeners: {
            load: function () {},
          },
        });
        return store;
      };
      var masterPanelGridforLanguages = function (id) {
        var confLanguages_store = Languagestore();
        var language_filter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "language",
            },
          ],
        });
        language_filter.remote = true;
        language_filter.autoReload = true;
    
        var grid_panel = new Ext.grid.GridPanel({
          store: confLanguages_store,
          layout: "fit",
          frame: false,
          border: false,
          title: "Manage Languages",
          plugins: [language_filter],
          id: id,
          loadMask: true,
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Langauges",
              sortable: true,
              hideable: true,
              dataIndex: "languageName",
              tooltip: "Langauges",
            },
            {
              header: "Code",
              sortable: true,
              hideable: true,
              dataIndex: "languageCode",
              tooltip: "Code",
            }
          ],
          viewConfig: {
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          },
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
          }),
          listeners: {
            resize: onGridResize,
            rowdblclick: function (grid, rowIndex, e) {
              var rec = grid.getStore().getAt(rowIndex);
              Ext.getCmp('langId').setValue(rec.get('id'));
              Ext.getCmp('languageName').setValue(rec.get('languageName'));
              Ext.getCmp('languageCode').setValue(rec.get('languageCode'));
          },
          afterrender: function () {},
          },
          tbar: [{
            xtype: 'hidden',
            id: 'langId',
            name: 'langId',
        },{
            html: '&nbsp;Language : &nbsp;',
        }, {
            fieldLabel: 'Mobile',
            xtype: 'textfield',
            id: 'languageName',
            name: 'languageName',
            tabIndex: 102,
            width: 80,
            anchor: '97%',
        }, {
          html: '&nbsp; Code : &nbsp;',
      }, {
          fieldLabel: 'Code',
          xtype: 'textfield',
          id: 'languageCode',
          name: 'languageCode',
          tabIndex: 103,
          width: 80,
          anchor: '97%',
      },{
        html: '&nbsp; &nbsp;',
    }, {
        text: 'Save',
        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
        tabIndex: 104,
        handler: function () {         

          var t = new Date();
          var t_stamp = t.format("YmdHis");

          var langId = Ext.getCmp('langId').getValue();
          var languageName = Ext.getCmp('languageName').getValue();
          var languageCode = Ext.getCmp('languageCode').getValue();

          if (!Ext.isEmpty(languageName) && (!Ext.isEmpty(languageCode) > 0)) {
                  Ext.Ajax.request({
                      url: modURL + '&op=saveLanguages',
                      params: {languageName: languageName, languageCode: languageCode,langId:langId},
                      failure: function (response) {
                          Ext.MessageBox.alert('Error', response.responseText);

                      },
                      success: function (response, options) {
                        var tmp = Ext.decode(response.responseText);
                        console.log('tmp', tmp);
                        if (tmp.success === true) {
                            Application.example.msg('Success', tmp.message);
                            Ext.getCmp('panelMasterMainLanguages').getStore().load();
                            Ext.getCmp('languageName').reset();
                            Ext.getCmp('languageCode').reset();
                            Ext.getCmp('langId').reset();
                        } else if (tmp.success === false) {
                            Ext.Msg.alert('Error', tmp.message);
                        } else if (tmp.success === false && tmp.valid === false) {
                            Ext.Msg.alert('Error', tmp.message);
                        } else {
                            Ext.Msg.alert('Error', tmp.message);
                        }
                      }
                  });
              


          } else {
              Ext.Msg.alert('Notification', 'Please enter all required fields');
          }
        }
    }
          ],
          bbar: new Ext.PagingToolbar({
            pageSize: recs_per_page,
            store: confLanguages_store,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No records to display",
          }),
          stripeRows: true,
        });
        return grid_panel;
      };
      var panelGridforLanguageSettings = function(id){        
        var panel = new Ext.Panel({
          layout: "border",
          border: false,
          frame: false,
          bodyStyle: { "background-color": "white" },
          hideBorders: true,
          id: id,
          title: "Translation Management",
          items: [languageTranslationGrid(), new Ext.Panel({
            title: "Language Translation",
            frame: false,
            border: true,
            region: "east",
            width: winsize.width * 0.4,
            cls: "left_side_panel",
            height: 500,
            autoScroll: true,
            items: [translationForm()],
            buttons: [],
            fbar: [],
          })],
        });
        return panel;
      };
      var translationForm = function(){
        var _trnslationFormPanel = new Ext.form.FormPanel({
          id: "formpanelTranslation",
          hidden:true,
          frame: false,
          border: false,
          autoHeight: true,
          layout: "column",
          autoScroll: true,
          labelWidth: 120,
          fileUpload: true,
          labelAlign: "top",
          bodyStyle: { "background-color": "white", "padding": "5px 5px 5px 10px" },
          items: [            
            {
              columnWidth: 1,
              layout: "form",
              border: false,
              items: [{
              style: {'font-weight': 'bold'},
              xtype: "displayfield",
              fieldLabel: "Source Data",
              id: "sourceData",
              name: "sourceData",
              readOnly: true,
              anchor: "98%",
              tabIndex: 507,
            }]
          },{
            columnWidth: 1,
            layout: "form",
            border: false,
            items: [{
              xtype: 'button',
              style: { 'font-weight': 'bold',"float": "right", "padding": "5px 5px 5px 10px"},
              text: 'Translate',
              handler: function () { 
                var sourceData = Ext.getCmp('sourceData').getValue(); 
                var languageId = Ext.getCmp('languageId').getValue(); 
                Application.PartnerMasters.fetchTranslation(languageId,sourceData);  
              }
          }]
          },{
            columnWidth: 1,
            layout: "form",
            border: false,
            items: [{
              xtype: "textarea",
              fieldLabel: "Converted Data in Target Language",
              id: "trnslatedData",
              name: "trnslatedData",
              style: {'font-size': '15px',"padding": "5px 5px 5px 10px"},
              anchor: "98%",
              tabIndex: 507,
              height: 100
            },{
              hidden:true,
              xtype: "templateeditormce",
              style: {'font-size': '15px',"padding": "5px 5px 5px 10px"},
              fieldLabel: "Converted Data in Target Language",
              anchor: "95%",
              id: "trnslatedDataRich",
              name: "trnslatedDataRich",
              maxLength: 7000,
              height: 250,
              tabIndex: 528,
              listeners: {},
            }]
          }
          ],
          buttons: [
            {
              text: "Reset",
              tabIndex: 506,
              handler: function () {
                Ext.getCmp('formpanelTranslation').getForm().reset();
              }
            },
            {
              text: "Save",
              tabIndex: 505,
              handler: function () {
                updateTranslatedValue();
              }
            }
          ],
          listeners: {
            load: function () {},
          },
        });
        return _trnslationFormPanel;
      };
      var languageTranslationGrid = function(){
        var lngMasterStore = languageMasterStore();
        var languageTranslation_filter = new Ext.ux.grid.GridFilters({
          remote: true,
          filters: [
            {
              type: "string",
              dataIndex: "ResourceValue",
            },
          ],
        });
        languageTranslation_filter.remote = true;
        languageTranslation_filter.autoReload = true;
        var _availSugridPanel = new Ext.grid.GridPanel({
          region: "center",
          layout: "fit",
          title: " ",
          frame: false,
          border: false,
          loadMask: true,
          store: languageTranslationData(),
          autoScroll: true,
          bodyStyle: { "background-color": "white" },
          id: "gridpanelforAvailableLngConfig",
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              //selectionchange: gridSelectionChangedstorecategorys,
            },
          }),
          plugins: [languageTranslation_filter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Name",
              dataIndex: "ResourceName",
              hideable: true,
              hidden:true,
              tooltip: "Name",
              hideable: false,
              width: 100,
            },{
              header: "Source Data",
              dataIndex: "ResourceValue",
              sortable: true,
              tooltip: "Source Data",
              hideable: false,
            },{
              header: "Data Type",
              dataIndex: "dataType",
              sortable: true,
              tooltip: "Data Type",
              hideable: false,
            },{
              header: "Date Added",
              dataIndex: "createdOn",
              sortable: true,
              tooltip: "Date Added",
              hideable: false,
            },{
              header: "Mapped Value",
              dataIndex: "MappedResourceValue",
              hidden: true,
              tooltip: "Mapped Value",
              hideable: false,
              width: 200,
              editor: {
                allowBlank: false,
                xtype: 'textfield',
                allowNegative: false,
                allowDecimals: true,
            }
            }
          ],
          tbar: [{
            xtype: 'radiogroup',
            width: 100,
            hidden:true,
            id: 'radiobuttonLngConfigId',
            //columnWidth: 0.25,
            items: [
                {boxLabel: 'Label', name: 'rb-auto', inputValue: 1, labelWidth: 100,checked: true},
                {boxLabel: 'Data', name: 'rb-auto', inputValue: 2, labelWidth: 100}

            ],
            listeners: {
                change: function (event, checked)
                {
                    var current_firstid = event.items.items[0].inputValue;
                    var current_secondid = event.items.items[1].inputValue;
                    //var current_thirdid = event.items.items[2].inputValue;
                    var radioid = Ext.getCmp('radiobuttonLngConfigId').getValue();
                }
            }
        },{
          xtype: "combo",
          emptyText: "Select Target Language",
          store: lngMasterStore,
          mode: "local",
          id: "languageId",
          fieldLabel: "Language",
          hiddenName: "languageId",
          displayField: "name",
          valueField: "id",
          typeAhead: true,
          tabIndex: 503,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          triggerAction: "all",
          lazyRender: true,
          width: 150,
          listeners: {
            select: function () {
              Ext.getCmp('gridpanelforAvailableLngConfig').getStore().load({
                params: {"languageId": Ext.getCmp('languageId').getValue()}
              });
            },
          },
        },{
          xtype: "button",
          text: "Add Source Data",
          id:"addSourceData",
          tooltip: "Add Source Data",
          icon: "./resources/images/default/icons/add.png",
          handler: function () {
            Application.PartnerMasters.addSourceData();
          },
        },{
          xtype: "button",
          text: "Start Job",
          id:"translationStartbtn",
          tooltip: "Start Job",
          icon: "./resources/images/default/icons/add.png",
          handler: function () {
            var language = Ext.getCmp('languageId').getValue();
            if(language > 0){
              Application.PartnerMasters.ChooseTranslation();
            }else{
              Ext.MessageBox.alert('Notification', 'Choose target language and proceed.');
            }
            
          },
        },{
          xtype: "button",
          text: "Search in Data Set",
          id:"translationSearchbtn",
          tooltip: "Search in Data Set",
          icon: "./resources/images/default/icons/search.png",
          handler: function () {
          },
        },{
          xtype: "button",
          text: "Show Converted Data",
          id:"convertedData",
          tooltip: "Show Converted Data",
          icon: "./resources/images/default/icons/candidate_edit_btn.png",
          handler: function () {
            var language = Ext.getCmp('languageId').getValue();
            var languageName = Ext.getCmp('languageId').getRawValue();
            if(language > 0){
              Application.PartnerMasters.showConvertedData(language,languageName);
            }else{
              Ext.MessageBox.alert('Notification', 'Choose target language and proceed.');
            }
          },
        }],
          viewConfig: {
            forceFit: true,
          },
          listeners: {
            afterrender: function (grid) {},
            rowdblclick: function (grid, rowIndex, e) {},
            afteredit: updateResourceValue
          },
          bbar: new Ext.PagingToolbar({
            pageSize: recs_per_page,
            store: languageTranslationData(),
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          })
        });
        return _availSugridPanel;
      };
      var languageMasterStore = function () {
        var store = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=getLanguage",
          method: "post",
          fields: ["id", "name"],
          //totalProperty: 'totalCount',
          root: "data",
        });
        return store;
      };
      
      function updateResourceValue(grid_event) {
        var data = Ext.encode(grid_event.record.data);
        Ext.Ajax.request({
            waitMsg: 'Please wait...',
            url: modURL + '&op=updateLanguageValue',
            params: {
                data: Ext.encode(grid_event.record.data),
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', 'Save Failed');
            },
            success: function (response, options) {
                if (response.responseText != "") {
                    eval('var tmp=' + response.responseText);
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Application.example.msg('Notification', tmp.msg);
                    } else if (tmp.success === false) {
                        Ext.MessageBox.alert('Notification', 'Error Occured while saving', function (btn) {
                            if (btn == 'ok') {
                            }
                        });
                    }
                }
            }
        });
    }
      var languageTranslationData = function () {
        var _Store = new Ext.data.JsonStore({
          method: "post",
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listSourceDataLabels",
            method: "post",
          }),
          fields: ["Id","ResourceName","ResourceValue","LanguageId","createdOn","dataType","sourceType"],
          totalProperty: "totalCount",
          root: "data",
          remoteSort: true,
          autoLoad: false,
          listeners: {
            beforeload: function (store, e) {
            },
          },
        });
        return _Store;
      };
      function updateTranslatedValue() {
        var trnslatedDataContent;
        if(!Ext.isEmpty(Ext.getCmp('trnslatedData').getValue()) || !Ext.isEmpty(Ext.getCmp('trnslatedDataRich').getValue())){
          if(Application.PartnerMasters.Cache.dataType == 'Rich Text'){
            trnslatedDataContent = Ext.getCmp('trnslatedDataRich').getValue();
          }else{
            trnslatedDataContent = Ext.getCmp('trnslatedData').getValue();
          }
          Ext.Ajax.request({
            waitMsg: 'Please wait...',
            url: modURL + '&op=updateTranslation',
            params: {
              trnslatedData: trnslatedDataContent,
              sourceId: Application.PartnerMasters.Cache.ResourceId,
              ResourceName: Application.PartnerMasters.Cache.ResourceName,
              sourceType: Application.PartnerMasters.Cache.sourceType,
              dataType: Application.PartnerMasters.Cache.dataType,
              LanguageId: Ext.getCmp('languageId').getValue()
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', 'Save Failed');
            },
            success: function (response, options) {
                if (response.responseText != "") {
                    eval('var tmp=' + response.responseText);
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Application.example.msg('Notification', tmp.msg);
                        Ext.getCmp('formpanelTranslation').getForm().reset();
                        Ext.getCmp('trnslatedDataRich').setValue('');
                        Ext.getCmp('gridpanelforAvailableLngConfig').getStore().load({
                          params: {"languageId": Ext.getCmp('languageId').getValue()}
                        });
                        Application.PartnerMasters.ChooseTranslation();
                        
                    } else if (tmp.success === false) {
                        Ext.MessageBox.alert('Notification', 'Error Occured while saving', function (btn) {
                            if (btn == 'ok') {
                            }
                        });
                    }
                }
            }
        });
        }else{
          Ext.MessageBox.alert('Notification', 'Please proceed after entering data.');
        }
        
    }
    var availableLanguageGrid = function (userId) {
      var availableLanguage_filter = new Ext.ux.grid.GridFilters({
        remote: true,
        filters: [
          {
            type: "string",
            dataIndex: "name",
          },
        ],
      });
      availableLanguage_filter.remote = true;
      availableLanguage_filter.autoReload = true;
      var _availLanggridPanel = new Ext.grid.GridPanel({
        region: "center",
        layout: "fit",
        title: "Languages to Assign",
        frame: false,
        border: false,
        loadMask: true,
        store: availableLanguageStore(userId),
        //iconCls: 'money',
        autoScroll: true,
        bodyStyle: { "background-color": "white" },
        id: "gridpanelforAvailableLanguage",
        sm: check_box,
        plugins: [availableLanguage_filter],
        columns: [
          check_box,
          new Ext.grid.RowNumberer(),
          {
            header: "Language",
            dataIndex: "name",
            sortable: true,
            tooltip: "Language",
            hideable: false,
            width: 200,
          },
        ],
        bbar: [
          "->",
          {
            icon: IMAGE_BASE_PATH + "/default/icons/Forward.png",
            xtype: "button",
            text: "Map to User",
            handler: function () {
              var selectitem = Ext.getCmp("gridpanelforAvailableLanguage")
                .getSelectionModel()
                .getSelections();
              var selectedcount = selectitem.length;
              var brandarr = [];
              for (var i = 0; i < selectedcount; i++) {
                brandarr.push(selectitem[i].data.id);
              }
              if (selectedcount != 0) {
                Application.PartnerMasters.mapLanguageToUser(brandarr, userId);
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Please select a product and proceed.."
                );
              }
            },
          },
        ],
        viewConfig: {
          forceFit: true,
        },
        listeners: {
          afterrender: function (grid) {},
          rowdblclick: function (grid, rowIndex, e) {},
        },
      });
      return _availLanggridPanel;
    };
    var availableLanguageStore = function (userId) {
      var _Store = new Ext.data.JsonStore({
        method: "post",
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listAvailableLanguage",
          method: "post",
        }),
        fields: ["id", "name", "pdtCount"],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true,
        listeners: {
          beforeload: function (store, e) {
            this.baseParams.userId = userId;
          },
        },
      });
      return _Store;
    };
    var userMappedLanguageStore = function (userId) {
      var _Store = new Ext.data.JsonStore({
        method: "post",
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listUserMappedLanguage",
          method: "post",
        }),
        fields: ["id", "name", "pdtCount"],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true,
        listeners: {
          beforeload: function (store, e) {
            this.baseParams.userId = userId;
          },
        },
      });
      return _Store;
    };
    var mappedUserLanguage = function (userId) {
      var mappedUserLanguage_filter = new Ext.ux.grid.GridFilters({
        remote: true,
        filters: [
          {
            type: "string",
            dataIndex: "name",
          },
        ],
      });
      mappedUserLanguage_filter.remote = true;
      mappedUserLanguage_filter.autoReload = true;
      var _mapedSUgridPanel = new Ext.grid.GridPanel({
        region: "center",
        layout: "fit",
        frame: false,
        border: false,
        loadMask: true,
        store: userMappedLanguageStore(userId),
        //iconCls: 'money',
        height: 500,
        autoScroll: true,
        bodyStyle: { "background-color": "white" },
        id: "gridpanelforUserMappedLanguage",
        plugins: [mappedUserLanguage_filter],
        columns: [
          new Ext.grid.RowNumberer(),
          {
            header: "Language",
            dataIndex: "name",
            sortable: true,
            tooltip: "Language",
            hideable: false,
            width: 200,
          },
          {
            xtype: "actioncolumn",
            header: "Action",
            hideable: true,
            sortable: false,
            groupable: false,
            tooltip: "Action",
            items: [
              {
                iconCls: "arch",
                tooltip: "Remove Language",
                handler: function (grid, rowIndex, colIndex, itm, evn) {
                  var record = grid.getStore().getAt(rowIndex);
  
                  Ext.Ajax.request({
                    waitMsg: "Processing",
                    url: modURL,
                    params: {
                      op: "removeCallEventFromUser",
                      langiageId: record.get("langiageId"),
                      userId: userId,
                    },
                    failure: function (response, options) {
                      Ext.MessageBox.alert("Notification", ACTION_FAIL);
                    },
                    success: function (response, options) {
                      eval("var tmp=" + response.responseText);
                      if (tmp.success === true) {
                        Application.example.msg("Notification", tmp.msg);
                        Ext.getCmp("gridpanelforUserMappedLanguage")
                          .getStore()
                          .load({
                            params: {
                              userId: userId,
                            },
                          });
                      }
                    }
                  });
                }
              },
            ],
          },
        ],
        viewConfig: {
          forceFit: true,
        },
        listeners: {
          afterrender: function () {},
          rowdblclick: function (grid, rowIndex, e) {},
        },
      });
      return _mapedSUgridPanel;
    };
    var panelGridforTranslatedData = function(id,language,languageName){    
      var panel = new Ext.Panel({
        layout: "border",
        border: false,
        frame: false,
        bodyStyle: { "background-color": "white" },
        hideBorders: true,
        id: id,
        title: "Converted Datas for - "+languageName,
        items: [translatedDataGrid(language)],
      });
      return panel;
    };
    var translatedDataGrid = function(language){
      console.log('languagegrid',language);  
      var translatedData_filter = new Ext.ux.grid.GridFilters({
        remote: true,
        filters: [
          {
            type: "string",
            dataIndex: "ResourceValue",
          },
        ],
      });
      translatedData_filter.remote = true;
      translatedData_filter.autoReload = true;
      var _availSugridPanel = new Ext.grid.GridPanel({
        region: "center",
        layout: "fit",
        title: " ",
        frame: false,
        border: false,
        loadMask: true,
        store: translatedDataStore(language),
        autoScroll: true,
        bodyStyle: { "background-color": "white" },
        id: "gridpanelforTranslatedDataConfig",
        sm: new Ext.grid.RowSelectionModel({
          singleSelect: true,
          listeners: {
          },
        }),
        plugins: [translatedData_filter],
        columns: [
          new Ext.grid.RowNumberer(),
          {
            header: "Name",
            dataIndex: "ResourceName",
            hideable: true,
            hidden:true,
            tooltip: "Name",
            hideable: false,
            width: 100,
          },{
            header: "Source Data",
            dataIndex: "ResourceValue",
            sortable: true,
            tooltip: "Source Data",
            hideable: false,
          },{
            header: "Mapped Data",
            dataIndex: "mappedValue",
            sortable: true,
            tooltip: "Mapped Data",
            hideable: false,
          },{
            header: "Data Type",
            dataIndex: "dataType",
            sortable: true,
            tooltip: "Data Type",
            hideable: false,
          },{
            header: "Date Added",
            dataIndex: "createdOn",
            sortable: true,
            tooltip: "Date Added",
            hideable: false,
          }
        ],
        tbar: [],
        viewConfig: {
          forceFit: true,
        },
        listeners: {
          resize: onGridResize,
        },
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: translatedDataStore(language),
          displayInfo: true,
          displayMsg: "Displaying records {0} - {1} of {2}",
          emptyMsg: "No pages to display",
        })
      });
      return _availSugridPanel;
    };
  var translatedDataStore = function (language) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listTranslatedData",
        method: "post",
      }),
      fields: ["Id","ResourceName","ResourceValue","LanguageId","createdOn","dataType","sourceType","mappedValue"],
      totalProperty: "totalCount",
      root: "data",
      autoLoad: true,
      listeners: {
        beforeload: function (store, e) {
          store.baseParams.languageId = language;
        }
      },
    });
      return _Store;
    };
    return {
        Cache: {},
        initStoreCategory: function () {
            var _storecategoryPanelId = "panelMasterMainStoreCategory";
            var _masterPanelStoreCategory = Ext.getCmp(_storecategoryPanelId);
            if (Ext.isEmpty(_masterPanelStoreCategory)) {
              _masterPanelStoreCategory = masterPanelforStoreCategory(
                _storecategoryPanelId
              );
              Application.UI.addTab(_masterPanelStoreCategory);
              _masterPanelStoreCategory.doLayout();
            } else {
              Application.UI.addTab(_masterPanelStoreCategory);
            }
          },
          ViewStoreCategorys: function () {
            var business_category_id = arguments[0];
            /*<?php if (user_access("partner_masters", "saveStoreCategorys")) { ?> */
            Ext.getCmp("buttonMasterStoreCategoryEdit").show();
            Ext.getCmp("buttonMasterStoreCategorySave").hide();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterStoreCategoryCancel").hide();
            Ext.getCmp("formpanelMasterStoreCategorys").hide();
            Ext.getCmp("panelMasterStoreCategorysDetailsView").show();
            Ext.getCmp("panelMasterStoreCategoryParent").doLayout();
            Ext.getCmp("panelMasterStoreCategoryParent").setTitle(
              "View Store Category Details"
            );
            Ext.Ajax.request({
              url: modURL + "&op=storecategorysdetailsView",
              method: "POST",
              params: { business_category_id: business_category_id },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp(
                    "panelMasterStoreCategorysDetailsView"
                  );
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("panelMasterStoreCategoryParent").doLayout();
                Ext.getCmp("gridStoreCatRetailCatList").show();
                Ext.getCmp("gridStoreCatRetailCatList")
                .getStore()
                .load({
                  params: {
                    business_category_id: business_category_id
                  },
                });
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("panelMasterStoreCategoryParent").doLayout();
          },
          EditStoreCategorysView: function () {
            Application.PartnerMasters.StoreCategorysAddEdit = "Edit";
            Ext.getCmp("panelMasterStoreCategoryParent").doLayout();
            Ext.getCmp("panelMasterStoreCategoryParent").setTitle(
              "Edit Store Category Details"
            );
            Ext.getCmp("formpanelMasterStoreCategorys").show();
            Ext.getCmp("panelMasterStoreCategorysDetailsView").hide();
            /*<?php if (user_access("partner_masters", "saveStoreCategorys")) { ?> */
            Ext.getCmp("buttonMasterStoreCategoryEdit").hide();
            Ext.getCmp("buttonMasterStoreCategorySave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterStoreCategoryCancel").show();
            Ext.getCmp("gridStoreCatRetailCatList").hide();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
              var storecategorysForm = Ext.getCmp(
                "formpanelMasterStoreCategorys"
              ).getForm();
              storecategorysForm.load({
                params: {
                  business_category_id: arguments[0],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=storecategorys_form_load",
                waitMsg: "Loading...",
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
              Ext.getCmp("gridStoreCatRetailCat")
                .getStore()
                .load({
                  params: {
                    business_category_id: arguments[0],
                    edit_status: 1,
                  },
                });
            }
          },initPrivateCategory: function (type) {
            loadCount = 0;
            var panelId = "privateCategoryMainPanel";
            var listVendor = Ext.getCmp(panelId);
            if (Ext.isEmpty(listVendor)) {
              listVendor = ListmainPvtCPanel(panelId);
              Application.UI.addTab(listVendor);
              listVendor.doLayout();
            } else {
              Application.UI.addTab(listVendor);
            }
          },
          addNewPrivateCategory: function (vc_id) {
            var title;
            if (vc_id > 0) {
              title = "Edit Private Category";
            } else {
              title = "Create Private Category";
            }
      
            var win_id = "windowAddNewPvtC";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {
              win = new Ext.Window({
                id: win_id,
                title: title,
                layout: "fit",
                width: winsize.width * 0.4,
                height: 250,
                plain: false,
                constrainHeader: true,
                modal: true,
                frame: true,
                border: false,
                resizable: false,
                items: new Ext.FormPanel({
                  id: "formpanelPrivateCategory",
                  autoHeight: true,
                  frame: true,
                  border: false,
                  labelAlign: "top",
                  bodyStyle: {
                    "background-color": "F1F1F1",
                    padding: "5px 5px 0px 5px",
                  },
                  //bodyStyle: {"padding": "5px"},
                  items: [
                    {
                      xtype: "textfield",
                      id: "vc_id",
                      name: "n[vc_id]",
                      hidden: true,
                    },
                    {
                      xtype: "panel",
                      layout: "column",
                      frame: false,
                      border: false,
                      bodyStyle: {
                        "background-color": "F1F1F1",
                        padding: "5px 2px 0px 2px",
                      },
                      items: [
                        {
                          columnWidth: 1,
                          layout: "form",
                          frame: false,
                          border: false,
                          bodyStyle: {
                            "background-color": "F1F1F1",
                            padding: "5px 2px 0px 2px",
                          },
                          items: [
                            {
                              xtype: "textfield",
                              id: "vc_name",
                              name: "n[vc_name]",
                              fieldLabel: "Private Category",
                              allowBlank: false,
                              tabIndex: 101,
                              anchor: "100%",
                            },
                          ],
                        },
                      ],
                    },
                    {
                      xtype: "panel",
                      columnWidth: 1,
                      layout: "column",
                      frame: false,
                      border: false,
                      bodyStyle: {
                        "background-color": "F1F1F1",
                        padding: "0px 2px 0px 3px",
                      },
                      items: [
                        {
                          columnWidth: 0.35,
                          layout: "form",
                          frame: false,
                          border: false,
                          bodyStyle: {
                            "background-color": "F1F1F1",
                            padding: "0px 2px 0px 3px",
                          },
                          items: [
                            {
                              xtype: "checkbox",
                              checked: false,
                              id: "vc_isHome",
                              tabIndex: 102,
                              anchor: "99%",
                              name: "n[vc_isHome]",
                              labelAlign: "right",
                              inputValue: 1,
                              boxLabel: "Include in Home Menu",
                            },
                          ],
                        },
                        {
                          columnWidth: 0.35,
                          layout: "form",
                          frame: false,
                          border: false,
                          bodyStyle: {
                            "background-color": "F1F1F1",
                            padding: "0px 2px 0px 2px",
                          },
                          items: [
                            {
                              xtype: "checkbox",
                              checked: false,
                              anchor: "99%",
                              id: "vc_isInCategory",
                              name: "n[vc_isInCategory]",
                              inputValue: 1,
                              labelAlign: "right",
                              tabIndex: 103,
                              boxLabel: "Show in Category List",
                              listeners: {
                                check: function (cbo, checked) {
                                  if (checked == true) {
                                    Ext.getCmp("vc_parentCategoryId").enable();
                                    Ext.getCmp("vc_categoryId").enable();
                                    Ext.getCmp(
                                      "vc_parentCategoryId"
                                    ).allowBlank = false;
                                    Ext.getCmp("vc_categoryId").allowBlank = false;
                                  } else {
                                    //Ext.getCmp('vc_parentCategoryId').reset();
                                    //Ext.getCmp('vc_categoryId').reset();
                                    Ext.getCmp("vc_parentCategoryId").disable();
                                    Ext.getCmp("vc_categoryId").disable();
                                    Ext.getCmp(
                                      "vc_parentCategoryId"
                                    ).allowBlank = true;
                                    Ext.getCmp("vc_categoryId").allowBlank = true;
                                  }
                                },
                                change: function () {
                                  if (
                                    Ext.getCmp("vc_isInCategory").getValue() == false
                                  ) {
                                    Ext.getCmp("vc_parentCategoryId").reset();
                                    Ext.getCmp("vc_categoryId").reset();
                                  }
                                },
                              },
                            },
                          ],
                        },
                      ],
                    },
                    {
                      xtype: "panel",
                      columnWidth: 1,
                      layout: "column",
                      frame: false,
                      border: false,
                      bodyStyle: {
                        "background-color": "F1F1F1",
                        padding: "10px 2px 10px 2px",
                      },
                      items: [
                        {
                          columnWidth: 0.35,
                          layout: "form",
                          frame: false,
                          border: false,
                          labelAlign: "top",
                          bodyStyle: {
                            "background-color": "F1F1F1",
                            padding: "10px 5px 10px 5px",
                          },
                          items: [
                            mkCombo({
                              type: "mypha_productparent_category",
                              value: "parent_category_id",
                              display: "parent_category",
                              name: "n[vc_parentCategoryId]",
                              fieldLabel: "Show Under",
                              emptyText: "Select Department",
                              tabIndex: 104,
                              anchor: "99%",
                              id: "vc_parentCategoryId",
                              listeners: false,
                              cx: "S_1",
                            }),
                          ],
                        },
                        {
                          columnWidth: 0.35,
                          layout: "form",
                          frame: false,
                          border: false,
                          bodyStyle: {
                            "background-color": "F1F1F1",
                            padding: "10px 5px 10px 5px",
                          },
                          items: [
                            mkCombo({
                              type: "mypha_productcategory",
                              value: "category_id",
                              display: "category_name",
                              name: "n[vc_categoryId]",
                              fieldLabel: "Category",
                              hideLabel: true,
                              emptyText: "Select Category",
                              allowBlank: true,
                              tabIndex: 105,
                              anchor: "99%",
                              id: "vc_categoryId",
                              listeners: false,
                              cx: "S_1",
                            }),
                          ],
                        },
                        {
                          columnWidth: 0.3,
                          layout: "form",
                          frame: false,
                          border: false,
                          bodyStyle: {
                            "background-color": "F1F1F1",
                            padding: "10px 2px 10px 2px",
                          },
                          items: [
                            mkCombo({
                              type: STATUS_COMBO_DATA,
                              value: "id",
                              display: "text",
                              anchor: "100%",
                              name: "n[vc_status]",
                              fieldLabel: "Status",
                              tabIndex: 106,
                              emptyText: "Set status..",
                              id: "vc_status",
                            }),
                          ],
                        },
                      ],
                    },
                  ],
                }),
                buttons: [
                  {
                    text: "Cancel",
                    id: "Cancel_btns",
                    icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
                    iconCls: "finascop_my-icon1",
                    tabIndex: 108,
                    handler: function () {
                      win.close();
                    },
                  },
                  {
                    text: "Save",
                    id: "save_btn",
                    icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
                    iconCls: "finascop_my-icon1",
                    tabIndex: 107,
                    handler: function () {
                      Application.PartnerMasters.savePrivateCategory();
                    },
                  },
                ],
                listeners: {
                  afterrender: function () {
                    if (Ext.getCmp("vc_isInCategory").getValue() == true) {
                      Ext.getCmp("vc_parentCategoryId").enable();
                      Ext.getCmp("vc_categoryId").enable();
                      Ext.getCmp("vc_parentCategoryId").allowBlank = false;
                      Ext.getCmp("vc_categoryId").allowBlank = false;
                    } else {
                      Ext.getCmp("vc_parentCategoryId").disable();
                      Ext.getCmp("vc_categoryId").disable();
                      Ext.getCmp("vc_parentCategoryId").allowBlank = true;
                      Ext.getCmp("vc_categoryId").allowBlank = true;
                    }
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    winLoadMask = new Ext.LoadMask(
                      Ext.getCmp("windowAddNewPvtC").getEl()
                    );
                    winLoadMask.msg = "Please wait...";
                    if (vc_id > 0) {
                      Ext.getCmp("formpanelPrivateCategory")
                        .getForm()
                        .load({
                          waitTitle: "Please Wait",
                          waitMsg: "Loading...",
                          url: modURL + "&op=getPvtCDetails",
                          params: {
                            vc_id: vc_id,
                            apikey: _SESSION.apikey,
                            tstamp: t_stamp,
                          },
                          success: function (form, action) {
                            var tmp = Ext.decode(action.response.responseText);
                            if (tmp.data.vc_categoryId == 0) {
                              Ext.getCmp("vc_categoryId").reset();
                            }
                            if (tmp.data.vc_parentCategoryId == 0) {
                              Ext.getCmp("vc_parentCategoryId").reset();
                            }
                          },
                          failure: function (form, action) {
                            Ext.Msg.alert("Error.", "This error");
                          },
                        });
                    }
                  },
                },
              });
            }
            win.doLayout();
            win.show(this);
            win.center();
          },
          savePrivateCategory: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp("formpanelPrivateCategory").getForm();
            if (store_form.isValid()) {
              store_form.submit({
                url: modURL + "&op=savePrivateCategory",
                waitMsg: "Saving Details....",
                waitTitle: "Please Wait...",
                params: {
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                success: function (response, action) {
                  var tmp = Ext.decode(action.response.responseText);
                  if (tmp.success === true && tmp.valid === true) {
                    Ext.getCmp("windowAddNewPvtC").close();
                    Application.example.msg("Success", tmp.message);
                    if (Application.PartnerMasters.PvtCAddEdit == "Add") {
                      recs_per_page = updateRecsPerPage(
                        Ext.getCmp("gridpanelPrivateCategory")
                      );
                      Ext.getCmp("formpanelPrivateCategory").getForm().reset();
                      Ext.getCmp("gridpanelPrivateCategory").store.reload({
                        params: {
                          start: 0,
                          limit: recs_per_page,
                        },
                      });
                    } else {
                      Ext.getCmp(
                        "gridpanelPrivateCategory"
                      ).selModel.getSelected().data.vc_id = tmp.data.vc_id;
                      Ext.getCmp("gridpanelPrivateCategory").getStore().reload();
                      Ext.getCmp("gridpanelPrivateCategory").getView().refresh();
                    }
                    Application.PartnerMasters.PvtCAddEdit = "";
                    Application.PartnerMasters.ViewMode(tmp.data.vc_id);
                  } else if (tmp.success === true && tmp.valid === false) {
                    Ext.Msg.alert("Notification.", tmp.message);
                  } else if (tmp.success === true && tmp.img_valid === false) {
                    Ext.Msg.alert("Notification.", tmp.message);
                  } else {
                    Ext.Msg.alert("Error", tmp.message);
                  }
                },
                failure: function (elm, conf) {
                  if (conf.failureType === "server") {
                    var result = Ext.decode(conf.response.responseText);
                    Ext.Msg.alert("Error", result.message);
                  } else {
                    Ext.MessageBox.alert(
                      "Notification",
                      "Please enter all required fields"
                    );
                  }
                },
              });
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please enter all required fields"
              );
            }
          },
          ViewMode: function (data) {
            var vc_id = arguments[0];
      
            Ext.Ajax.request({
              url: modURL + "&op=pvtCatDetailsView",
              method: "POST",
              params: { vc_id: vc_id },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp("property_grid_id");
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("property_grid_id").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            var _addItemgrid_store = Ext.getCmp("gridpanelVendorAdditem").getStore();
            _addItemgrid_store.load({
              params: {
                vc_id: vc_id,
              },
            });
          },
          saveCheckedItem: function (cid, itemarr) {
            var cid = Application.PartnerMasters.Cache.cid;
            var itemarr = Application.PartnerMasters.Cache.itemarr;
            var itemtype = Application.PartnerMasters.Cache.itemType;
            Ext.Ajax.request({
              url: modURL + "&op=saveitemPvtC",
              method: "post",
              params: { cid: cid, itemarr: Ext.encode(itemarr), itemtype: itemtype },
              success: function (resp) {
                var res = Ext.decode(resp.responseText);
                if (res.success === true) {
                  Application.example.msg(
                    "Success",
                    "Item details has been saved successfully."
                  );
                  Ext.getCmp("gridpanelVendorAdditem").getStore().reload();
                  Ext.getCmp(
                    "windowFinascopStockAddvenderitemCreatevendoritem"
                  ).close();
                } else {
                  Ext.MessageBox.alert(
                    "Notification",
                    "Product(s) already mapped.",
                    function (btn) {
                      Ext.getCmp("gridpanelVendorAdditem").getStore().reload();
                      Ext.getCmp(
                        "windowFinascopStockAddvenderitemCreatevendoritem"
                      ).close();
                    }
                  );
                }
              },
            });
          },
          uploadimageCategory: function (rid, uploadtype, img_url) {
            if (uploadtype === "privateCategory") {
              var main_img_panel = categoryuploadForm(img_url);
            }
      
            var window_id = "catuploadwindow";
            var catuploadwindow = new Ext.Window({
              id: window_id,
              title: "Upload Image",
              layout: "fit",
              width: 230,
              autoHeight: true,
              plain: true,
              constrainHeader: true,
              modal: true,
              frame: true,
              iconCls: "finascop_dataentry_receipt",
              resizable: false,
              closable: false,
              items: main_img_panel,
              listeners: {
                afterrender: function () {
                  var t = new Date();
                  var t_stamp = t.format("YmdHis");
                  winLoadMask = new Ext.LoadMask(
                    Ext.getCmp("catuploadwindow").getEl()
                  );
                  winLoadMask.msg = "Please wait...";
                  if (uploadtype === "privateCategory") {
                    Ext.getCmp("category_image_upload")
                      .getForm()
                      .load({
                        waitTitle: "Please Wait",
                        waitMsg: "Loading...",
                        url: modURL + "&op=get_catimg_s3_details",
                        params: {
                          rid: rid,
                          uploadtype: uploadtype,
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp,
                        },
                      });
                  }
                },
              },
              buttons: [
                {
                  text: "Cancel",
                  icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
                  iconCls: "finascop_my-icon1",
                  handler: function () {
                    catuploadwindow.close();
                  },
                },
                {
                  text: "Upload",
                  icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
                  iconCls: "finascop_my-icon1",
                  id: "saveButton",
                  handler: function () {
                    if (uploadtype === "privateCategory") {
                      Application.PartnerMasters.savePvtCategoryImage();
                    }
                  },
                },
              ],
            });
            catuploadwindow.doLayout();
            catuploadwindow.show(this);
            catuploadwindow.center();
          },
          savePvtCategoryImage: function () {
            var bucket_name = Ext.getCmp("albumBucketName").getValue();
            var file_name = Ext.getCmp("file_name").getValue();
      
            if (bucket_name != "" && file_name != "") {
              Ext.Ajax.request({
                url: modURL + "&op=savePvtCategoryImage",
                method: "POST",
                params: {
                  vc_id: Application.PartnerMasters.Cache.vc_id,
                  uploaded_file_name: file_name,
                  bucket: bucket_name,
                  filepath: Ext.getCmp("aws_file_location").getValue(),
                  bucket_path: Ext.getCmp("oncompleteurl").getValue(),
                },
                success: function (resp) {
                  var res = Ext.decode(resp.responseText);
                  if (res.success === true) {
                    Application.example.msg("Notification", "Image saved..");
                    Ext.getCmp("catuploadwindow").close();
                  } else {
                    Ext.Msg.alert("Error", "Image not saved. Try again");
                  }
                },
                failure: function (elm, conf) {
                  if (conf.failureType === "server") {
                    var result = Ext.decode(conf.response.responseText);
                    Ext.Msg.alert("Notification", result.error);
                  } else {
                    var result = Ext.decode(conf.response.responseText);
                    Ext.MessageBox.alert("Error", result.error);
                  }
                },
              });
            }
          },initConfiguredMobiles: function () {
            var _ConfiguredMobilesPanelId = "panelMasterMainConfiguredMobiles";
            var _masterPanelConfiguredMobiles = Ext.getCmp(_ConfiguredMobilesPanelId);
            if (Ext.isEmpty(_masterPanelConfiguredMobiles)) {
              _masterPanelConfiguredMobiles = masterPanelGridforconfiguredMobiles(
                _ConfiguredMobilesPanelId
              );
              Application.UI.addTab(_masterPanelConfiguredMobiles);
              _masterPanelConfiguredMobiles.doLayout();
            } else {
              Application.UI.addTab(_masterPanelConfiguredMobiles);
            }
          },initLanguages: function () {
            var _LanguagesPanelId = "panelMasterMainLanguages";
            var _masterPanelLanguages = Ext.getCmp(_LanguagesPanelId);
            if (Ext.isEmpty(_masterPanelLanguages)) {
              _masterPanelLanguages = masterPanelGridforLanguages(
                _LanguagesPanelId
              );
              Application.UI.addTab(_masterPanelLanguages);
              _masterPanelLanguages.doLayout();
            } else {
              Application.UI.addTab(_masterPanelLanguages);
            }
          },initLanguagesSettings: function () {
            var _LanguagesSettingPanelId = "panelLanguageSettings";
            var _panelLanguageSetting = Ext.getCmp(_LanguagesSettingPanelId);
            if (Ext.isEmpty(_panelLanguageSetting)) {
              _panelLanguageSetting = panelGridforLanguageSettings(
                _LanguagesSettingPanelId
              );
              Application.UI.addTab(_panelLanguageSetting);
              _panelLanguageSetting.doLayout();
            } else {
              Application.UI.addTab(_panelLanguageSetting);
            }
          },ChooseTranslation: function(){
            Ext.Ajax.request({
              url: modURL + "&op=chooseTranslationJob",
              method: "POST",
              params:{
                LanguageId : Ext.getCmp('languageId').getValue()
              },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success == true) {
                  Application.PartnerMasters.Cache.ResourceId = tmp.ResourceId;
                  Application.PartnerMasters.Cache.ResourceValue = tmp.ResourceValue;
                  Application.PartnerMasters.Cache.ResourceName = tmp.ResourceName;
                  Application.PartnerMasters.Cache.sourceType = tmp.sourceType;
                  Application.PartnerMasters.Cache.dataType = tmp.dataType;
      
                  Application.example.msg("Notification", tmp.msg);
                  Ext.getCmp('formpanelTranslation').show();
                  if(Application.PartnerMasters.Cache.dataType == 'Rich Text'){
                    Ext.getCmp('trnslatedDataRich').show();
                    Ext.getCmp('trnslatedData').hide();                    
                  }else{
                    Ext.getCmp('trnslatedData').show();
                  }
                  Ext.getCmp('sourceData').setValue(tmp.ResourceValue);
                  
                } else {
                  Ext.MessageBox.alert("Notification", tmp.msg);
                }
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
          },fetchTranslation: function(languageId,sourceData){
            Ext.Ajax.request({
              url: modURL + "&op=generateTranslatedData",
              method: "POST",
              params:{
                LanguageId: languageId,
                sourceData: sourceData
              },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                console.log(tmp.data.data.translations[0]);
                var aitrnslatedData = tmp.data.data.translations[0].translatedText; 
                /*var specialCharsRegex = /[^a-zA-Z0-9\s\n:.,]/g; 
                aitrnslatedData = aitrnslatedData.replace(specialCharsRegex, '');  */                      
                if (tmp.success === true) {
                  Ext.getCmp("trnslatedData").setValue(aitrnslatedData);
                  Ext.getCmp("trnslatedDataRich").setValue(aitrnslatedData);
                } else {
                  Ext.Msg.alert("Notification.", "Failed to load data,please check...");
                }
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
          },assignLanguagesToUser: function (userId) {
            var availableLanguageWindowid = Ext.getCmp("availableLanguagesWindow");
            if (Ext.isEmpty(availableLanguageWindowid)) {
              availableLanguageWindowid = new Ext.Window({
                id: "availableLanguagesWindow",
                title: "Assign Translation Languages to User",
                modal: true,
                height: 500,
                width: winsize.width * 0.8,
                shadow: false,
                resizable: false,
                layout: "border",
                items: [
                  availableLanguageGrid(userId),
                  new Ext.Panel({
                    title: "Assigned Languages",
                    frame: false,
                    border: true,
                    region: "east",
                    width: winsize.width * 0.4,
                    cls: "left_side_panel",
                    height: 500,
                    autoScroll: true,
                    items: [mappedUserLanguage(userId)],
                    buttons: [],
                    fbar: [],
                  }),
                ],
                buttons: [
                  {
                    text: "Close",
                    iconCls: "my-icon61",
                    handler: function () {
                      availableLanguageWindowid.close();
                    }
                  }
                ],
                listeners: {
                  close: function () {},
                },
              });
            }
            availableLanguageWindowid.doLayout();
            availableLanguageWindowid.show();
            availableLanguageWindowid.center();
          },mapLanguageToUser: function (brandarr, userId) {
            Ext.Ajax.request({
              url: modURL + "&op=mapLanguageToUser",
              method: "post",
              params: {
                brandarr: Ext.encode(brandarr),
                userId: userId,
              },
              success: function (resp) {
                var res = Ext.decode(resp.responseText);
                if (res.success === true) {
                  Ext.getCmp(
                    "gridpanelforAvailableLanguage"
                  ).getStore().baseParams.userId = userId;
                  Ext.getCmp("gridpanelforAvailableLanguage").getStore().load();
      
                  Ext.getCmp(
                    "gridpanelforUserMappedLanguage"
                  ).getStore().baseParams.userId = userId;
                  Ext.getCmp("gridpanelforUserMappedLanguage").getStore().load();
                }
              },
            });
          },addSourceData: function(){            
            var sourceDataWindowid = Ext.getCmp("availableLanguagesWindow");
            if (Ext.isEmpty(sourceDataWindowid)) {
              sourceDataWindowid = new Ext.Window({
                id: "sourceDataWindow",
                title: "Add Source Data for Translation",
                modal: true,
                height: 180,
                width: winsize.width * 0.5,
                shadow: false,
                resizable: false,
                layout: "fit",
                items: new Ext.FormPanel({
                  id: "formpanelSourceData",
                  autoHeight: true,
                  frame: true,
                  border: false,
                  labelAlign: "top",
                  bodyStyle: {
                    "background-color": "F1F1F1",
                    padding: "5px 5px 0px 5px",
                  },
                  items: [{
                    columnWidth: 1,
                    layout: "form",
                    border: false,
                    items: [{
                    xtype: "textfield",
                    fieldLabel: "Name",
                    id: "ResourceName",
                    name: "ResourceName",
                    anchor: "98%",
                    tabIndex: 507,
                  }]
                },{
                  columnWidth: 1,
                  layout: "form",
                  border: false,
                  items: [{
                  xtype: "textfield",
                  fieldLabel: "Value",
                  id: "ResourceValue",
                  name: "ResourceValue",
                  anchor: "98%",
                  tabIndex: 508,
                }]
              }],
                }),
                buttons: [
                  {
                    text: "Save",
                    cls: "left-right-buttons",
                    icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                    tabIndex: 509,
                    handler: function () {
                      var ResourceName = Ext.getCmp("ResourceName").getValue();
                      var ResourceValue = Ext.getCmp("ResourceValue").getValue();
                      if(!Ext.isEmpty(Ext.getCmp("ResourceName").getValue()) && !Ext.isEmpty(Ext.getCmp("ResourceValue").getValue())){
                        Ext.Ajax.request({
                          url: modURL + "&op=saveSourceData",
                          method: "POST",
                          waitMsg: "Processing",
                          params: {
                            ResourceName: ResourceName,
                            ResourceValue: ResourceValue,
                          },
                          failure: function (response, options) {
                            var tmp = Ext.util.JSON.decode(response.responseText);
                            Ext.MessageBox.alert("Error", tmp.message);
                          },
                          success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
              
                            if (tmp.success === true) {
                              Application.example.msg("Success", tmp.message);
                              Ext.getCmp("formpanelSourceData").getForm().reset();
                            } else {
                              Ext.MessageBox.alert("Error", tmp.msg);
                            }
                          },
                        });
                      }else{
                        Ext.MessageBox.alert("Notifications", "Proceed after entering name and value ");
                      }
              
                      
                    }
                  },
                  {
                    text: "Close",
                    iconCls: "my-icon61",
                    tabIndex: 509,
                    handler: function () {
                      sourceDataWindowid.close();
                    }
                  }
                ],
                listeners: {
                  close: function () {},
                },
              });
            }
            sourceDataWindowid.doLayout();
            sourceDataWindowid.show();
            sourceDataWindowid.center();
          },showConvertedData: function (language,languageName) {
            var _ConvertedDataPanelId = "panelConvertedData";
            var _panelConvertedData = Ext.getCmp(_ConvertedDataPanelId);
            if (Ext.isEmpty(_panelConvertedData)) {
              _panelConvertedData = panelGridforTranslatedData(
                _ConvertedDataPanelId,language,languageName
              );
              Application.UI.addTab(_panelConvertedData);
              _panelConvertedData.doLayout();
            } else {
              Application.UI.addTab(_panelConvertedData);
            }
          }
    };
})();