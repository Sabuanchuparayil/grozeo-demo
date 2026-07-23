Application.MyphaProductmasters = (function () {
  var RECS_PER_PAGE = 12;
  var modURL = "?module=mypha_productmasters";
  var winsize = Ext.getBody().getViewSize();
  var loadCount;
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var gridSelectionChangedmanufacture = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelProductMasterListingManufacture")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelProductMasterListingManufacture")
        .getSelectionModel()
        .getSelections()[0].data.manufacture_id;
      Application.MyphaProductmasters.ViewManufactureMode(ID);
    }
  };
  var gridSelectionChanged = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("subcatMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("subcatMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.sub_category_id;
      Application.MyphaProductmasters.ViewMode(ID);
    }
  };
  var gridSelectionChangedcat = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("catMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("catMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.category_id;
      Application.MyphaProductmasters.catViewMode(ID);
    }
  };
  var gridSelectionChangedparentCategory = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterListingParentCategory")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterListingParentCategory")
        .getSelectionModel()
        .getSelections()[0].data.parent_category_id;
      Application.MyphaProductmasters.ViewParentCategoryMode(ID);
    }
  };
  var gridSelectionChangedbusinesstypes = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewBusinessTypesdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewBusinessTypesdata")
        .getSelectionModel()
        .getSelections()[0].data.business_type_id;
      Application.MyphaProductmasters.ViewBusinessTypes(ID);
    }
  };
  var catMasterStore = function () {
    var _catStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listCategory",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "category_id",
          root: "data",
        },
        [
          "category_id",
          "cat_name",
          "parent_category","business_type_name",
          "image",
          "isHome",
          "image_url",
          "isInCategory",
          "hasImage","status"
        ]
      ),
      sortInfo: {
        field: "cat_name",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("catMasterGrid").getSelectionModel().selectRow(0);
          }
        },
      },
    });
    return _catStore;
  };
  var CategoryGrid = function () {
    var _CatGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "business_type_name",
        },
        {
          type: "string",
          dataIndex: "cat_name",
        },
        {
          type: "string",
          dataIndex: "parent_category",
        },
        {
          type: "string",
          dataIndex: "business_type_name",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isHome",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isInCategory",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "hasImage",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _CatGridFilter.remote = true;
    _CatGridFilter.autoReload = true;
    var _catMasterStore = catMasterStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _catMasterStore,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      //iconCls: 'my-icon444',
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_CatGridFilter],
      id: "catMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(), 
        {
          header: "Category",
          sortable: true,
          dataIndex: "cat_name",
          tooltip: "Category",
          hideable: true,
        }, {
          header: "Department",
          sortable: true,
          dataIndex: "parent_category",
          tooltip: "Department",
        },{
          header: "Retail Category",
          sortable: true,
          dataIndex: "business_type_name",
          tooltip: "Retail Category",
        },       
        {
          header: "Home Menu",
          sortable: true,
          dataIndex: "isHome",
          tooltip: "Home Menu",
        },
        {
          header: "In Category List",
          sortable: true,
          dataIndex: "isInCategory",
          tooltip: "In Category List",
        },
        {
          header: "Has Image",
          sortable: true,
          dataIndex: "hasImage",
          tooltip: "Has Image",
        },{
          header: "Status",
          sortable: true,
          dataIndex: "status",
          tooltip: "Status",
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
              categoryActionMenu.showAt(e.getXY());
              //action
            },
          },
        },
        /*{
                         xtype: 'actioncolumn',
                         header: 'Action',
                         hideable: true,
                         width: 40,
                         items: [{
                         sortable: false,
                         getClass: function (v, meta, rec) {
                         this.items[0].tooltip = 'Upload Category Image';
                         return 'upload';
                         
                         
                         },
                         handler: function (grid, rowIndex, colIndex) {
                         var uploadtype = 'category';
                         var record = grid.store.getAt(rowIndex);
                         var cat_id = record.data.category_id;
                         Ext.Ajax.request({
                         url: modURL + '&op=getcategoryImage',
                         method: 'POST',
                         params: {
                         cat_id: cat_id
                         },
                         success: function (res) {
                         var tmp = Ext.decode(res.responseText);
                         console.log("temp is -", tmp);
                         if (tmp.data != '')
                         {
                         var img_url = tmp.data[0].image_url;
                         Application.MyphaProductmasters.uploadimageCategory(cat_id, uploadtype, img_url);
                         }
                         else {
                         var img_url = '';
                         Application.MyphaProductmasters.uploadimageCategory(cat_id, uploadtype, img_url);
                         
                         }
                         }
                         })
                         }
                         }]
                         }*/
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedcat,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("category_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.category_id = ID;
            Ext.getCmp("catMasterForm").hide();
            Application.MyphaProductmasters.catViewMode(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _catMasterStore.load();
        },
      },
      tbar: [
        {
          text: "Create Category",
          tooltip: "Create Category",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.catAddEdit = "Add";
            var masterForm = Ext.getCmp("catMasterForm").getForm();
            loadedForm = null;
            masterForm.reset();
            Ext.getCmp("cat_name").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveCategory")) { ?> */
            Ext.getCmp("CatEditBtn").hide();
            Ext.getCmp("CatSaveBtn").show();
            /*<?php } ?> */
            Ext.getCmp("CatCancelBtn").show();
            Ext.getCmp("catMasterForm").show();
            Ext.getCmp("CatMasterDetailsViewPanel").hide();
            Ext.getCmp("statuscat").setValue(1);
            Ext.getCmp("CategoryparentPanel").doLayout();
            Ext.getCmp("CategoryparentPanel").setTitle(
              "Create Category Details"
            );
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _catMasterStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_CatGridFilter],
      }),
      stripeRows: true,
    });
    return _gridPanel;
  };
  var categoryActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Upload Image",
        handler: function () {
          var cat_id = Ext.getCmp("catMasterGrid")
            .getSelectionModel()
            .getSelections()[0].data.category_id;
          var uploadtype = "category";
          Ext.Ajax.request({
            url: modURL + "&op=getcategoryImage",
            method: "POST",
            params: {
              cat_id: cat_id,
            },
            success: function (res) {
              var tmp = Ext.decode(res.responseText);
              console.log("temp is -", tmp);
              if (tmp.data != "") {
                var img_url = tmp.data[0].image_url;
                Application.MyphaProductmasters.uploadimageCategory(
                  cat_id,
                  uploadtype,
                  img_url
                );
              } else {
                var img_url = "";
                Application.MyphaProductmasters.uploadimageCategory(
                  cat_id,
                  uploadtype,
                  img_url
                );
              }
            },
          });
        },
      },
    ],
  });
  var businessTypeComboStorePrimary = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getBusinessType",
      method: "post",
      fields: ["business_type_id", "business_type_name"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var departmentComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getDepartment",
      method: "post",
      fields: ["parent_category_id", "parent_category"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var categoryComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getCategory",
      method: "post",
      fields: ["category_id", "category_name"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var CategoryMasterForms = function () {
    var pribusinessTypeComboStore = businessTypeComboStorePrimary();
    var deptComboStore = departmentComboStore();
    var _catFormPanel = new Ext.form.FormPanel({
      frame: false,
      border: true,
      hideBorders: true,
      labelWidth: 120,
      labelAlign: "top",
      fileUpload: true,
      autoScroll: true,
      hidden: true,
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      id: "catMasterForm",
      items: [
        {
          xtype: "textfield",
          fieldLabel: "Category",
          id: "cat_name",
          name: "n[category_name]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
        },
        {
          xtype: "hidden",
          id: "category_id",
          name: "n[category_id]",
        },
        {
          xtype: "combo",
          store: pribusinessTypeComboStore,
          mode: "local",
          id: "primary_businessType",
          allowBlank: true,
          fieldLabel: "Retail Category",
          hiddenName: "n[primary_businessType]",
          displayField: "business_type_name",
          valueField: "business_type_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function () {
              var value = Ext.getCmp("primary_businessType").getValue();
              deptComboStore.baseParams.primaryBt = this.value;
              deptComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: deptComboStore,
          mode: "local",
          id: "parent_category",
          allowBlank: true,
          fieldLabel: "Department",
          hiddenName: "n[parent_category]",
          displayField: "parent_category",
          valueField: "parent_category_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function (combo, record, index) {
              var value = record.data.parent_category_id;
              if (value > 0) {
                Ext.Ajax.request({
                  url: modURL + "&op=getCategoryMap",
                  method: "POST",
                  params: { parent_category_id: value },
                  success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    console.log("getItemCategory", tmp);
                    Ext.getCmp("CategoryMap").show();
                    Ext.getCmp("CategoryMap").setValue(tmp.categoryCombination);
                    //Ext.getCmp('iteMidCategory').setValue(tmp.iteMidCategory);
                  },
                  failure: function () {
                    Ext.MessageBox.alert(
                      "Error",
                      "Error occured while sending data"
                    );
                  },
                });
              }
            },
          },
        },
        {
          hideLabel: true,
          xtype: "displayfield",
          fieldLabel: " ",
          width: 150,
          hidden: true,
          id: "CategoryMap",
          style: { "font-weight": "bold" },
          anchor: "97%",
        },
        {
          xtype: "panel",
          layout: "column",
          frame: false,
          border: false,
          items: [
            {
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "mc_isHome",
                  name: "n[isHome]",
                  inputValue: 1,
                  boxLabel: "Include in Home Menu",
                },
              ],
            },
            {
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "mc_isInCategory",
                  name: "n[isInCategory]",
                  inputValue: 1,
                  boxLabel: "Show in Category List",
                },
              ],
            },
          ],
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          emptyText: "Set status..",
          editable: false,
          typeAhead: false,
          tabIndex: 3,
          id: "statuscat",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("category_id").getValue())) {
            var recordSelected = Ext.getCmp("statuscat").getStore().getAt(0);
            Ext.getCmp("statuscat").setValue(recordSelected.get("id"));
          }
        },
      },
    });
    return _catFormPanel;
  };
  var CategoryMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "CatMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Category </th><td> {cat_name} </td></tr>',
        '<tr><th width="40%">Department </th><td> {parent_category_name} </td></tr>',
        '<tr><th width="40%">Is Home </th><td> {mc_isHome} </td></tr>',
        '<tr><th width="40%">In Category List </th><td> {mc_isInCategory} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        '<tpl if="image_url != null">',
        "<tpl if=\"image_url != ''\">",
        "<tr><td>",
        '<div border=0 ><img border=0 width="200" id="catDtlsViewPanel" height="200" src="{image_url}"></img></div>',
        "</td></tr>",
        "</tpl>",
        "</tpl>",
        "</table>",
        "</div>"
      ),
    });
  };
  var saveCatgeory = function () {
    var catId = Ext.getCmp("category_id").getValue();
    if (Ext.getCmp("catMasterGrid").getStore().getCount() > 0) {
      var index = Ext.getCmp("catMasterGrid")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }
    var lastOptions = Ext.getCmp("catMasterGrid").getStore().lastOptions;
    if (
      !Ext.isEmpty(
        Ext.getCmp("cat_name").getValue() &&
          Ext.getCmp("parent_category").getValue() &&
          Ext.getCmp("statuscat").getValue()
      )
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveCategory",
        method: "POST",
        params: {
          id: Ext.getCmp("category_id").getValue(),
          name: Ext.getCmp("cat_name").getValue(),
          parent_category: Ext.getCmp("parent_category").getValue(),
          status: Ext.getCmp("statuscat").getValue(),
          mc_isHome: Ext.getCmp("mc_isHome").getValue(),
          mc_isInCategory: Ext.getCmp("mc_isInCategory").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.catAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp("catMasterGrid"));
              Ext.getCmp("catMasterGrid").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("catMasterGrid").getStore().reload(lastOptions);
              var gridPanel = Ext.getCmp("catMasterGrid");
              gridPanel.getSelectionModel().selectRow(index);
              Application.MyphaProductmasters.catViewMode(tmp.data.category_id);
            }
            Application.MyphaProductmasters.catAddEdit = "";
            Application.MyphaProductmasters.catViewMode(tmp.data.category_id);
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
      Ext.getCmp("CategoryMap").setValue("");
    } else {
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var masterPanelforCat = function (id) {
    var _mpanelforcat = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Category",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        CategoryGrid(),
        new Ext.Panel({
          title: "Category Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "CategoryparentPanel",
          height: winsize.height * 0.6,
          items: [CategoryMasterForms(), CategoryMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "CatCancelBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("catMasterGrid")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("catMasterGrid")
                    .getSelectionModel()
                    .getSelections()[0].data.category_id;
                  Application.MyphaProductmasters.catViewMode(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveCategory")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "CatEditBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              hidden: true,
              handler: function () {
                var ID = Ext.getCmp("catMasterGrid")
                  .getSelectionModel()
                  .getSelections()[0].data.category_id;
                Application.MyphaProductmasters.catEditView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "CatSaveBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveCatgeory(id);
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforcat;
  };
  var parentCategoryPanel = function (id) {
    var _parentPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Departments",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        parentCategoryGrid(),
        new Ext.Panel({
          title: "Department Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterParentCategoryParent",
          height: winsize.height * 0.6,
          items: [parentCategoryForm(), parentCategoryDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 503,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterParentCategoryCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterListingParentCategory")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterListingParentCategory")
                    .getSelectionModel()
                    .getSelections()[0].data.brand_id;
                  Application.MyphaProductmasters.ViewParentCategoryMode(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveParentCategory")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonMasterParentCategoryEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 502,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterListingParentCategory")
                  .getSelectionModel()
                  .getSelections()[0].data.parent_category_id;
                Application.MyphaProductmasters.EditParentCategoryView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 502,
              cls: "left-right-buttons",
              id: "buttonMasterParentCategorySave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveParentCategory(id);
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _parentPanel;
  };
  var parentCategoryGridstore = function () {
    var _parentCategoryList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listParentCategory",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "parent_category_id",
          root: "data",
        },
        [
          "parent_category_id",
          "parent_category",
          "parent_category_businessType",
          "status",
          "status1",
          "isHome",
          "isInCategory",
          "hasImage",
        ]
      ),
      sortInfo: {
        field: "parent_category_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("gridpanelMasterListingParentCategory")
              .getSelectionModel()
              .selectRow(0);
          }
        },
      },
    });
    return _parentCategoryList;
  };
  var parentCategoryForm = function () {
    var _parentCategoryForm = new Ext.FormPanel({
      id: "formpanelMasterParentCategory",
      frame: false,
      border: false,
      hidden: true,
      labelAlign: "top",
      autoHeight: true,
      labelWidth: 100,
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      items: [
        mkCombo({
          type: "finascop_business_type",
          value: "business_type_id",
          display: "business_type_name",
          name: "n[parent_category_businessType]",
          fieldLabel: "Retail Category",
          emptyText: "Select Retail Category",
          tabIndex: 101,
          anchor: "98%",
          id: "parent_category_businessType",
          listeners: false,
          cx: "S_1",
        }),
        {
          xtype: "textfield",
          fieldLabel: "Department",
          id: "textfieldMasterParentCategory",
          name: "n[parent_category]",
          anchor: "98%",
          allowBlank: false,
          width: 300,
          tabIndex: 102,
          maxLength: 300,
        },
        {
          xtype: "textfield",
          id: "textfieldMasterParentCategoryId",
          name: "n[parent_category_id]",
          hidden: true,
        },
        {
          xtype: "panel",
          layout: "column",
          frame: false,
          border: false,
          items: [
            {
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "pc_isHome",
                  name: "n[isHome]",
                  inputValue: 1,
                  boxLabel: "Include in Home Menu",
                },
              ],
            },
            {
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "pc_isInCategory",
                  name: "n[isInCategory]",
                  inputValue: 1,
                  boxLabel: "Show in Category List",
                },
              ],
            },
          ],
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 103,
          emptyText: "Set status..",
          id: "comboMasterParentCategorystatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (
            Ext.isEmpty(
              Ext.getCmp("textfieldMasterParentCategoryId").getValue()
            )
          ) {
            var recordSelected = Ext.getCmp("comboMasterParentCategorystatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterParentCategorystatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _parentCategoryForm;
  };
  var saveParentCategory = function () {
    var parCcat = Ext.getCmp("textfieldMasterParentCategoryId").getValue();
    if (
      Ext.getCmp("gridpanelMasterListingParentCategory").getStore().getCount() >
      0
    ) {
      var index = Ext.getCmp("gridpanelMasterListingParentCategory")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }

    var lastOptions = Ext.getCmp(
      "gridpanelMasterListingParentCategory"
    ).getStore().lastOptions;
    if (
      !Ext.isEmpty(Ext.getCmp("textfieldMasterParentCategory").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterParentCategorystatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveParentCategory",
        method: "POST",
        params: {
          id: Ext.getCmp("textfieldMasterParentCategoryId").getValue(),
          name: Ext.getCmp("textfieldMasterParentCategory").getValue(),
          status: Ext.getCmp("comboMasterParentCategorystatus").getValue(),
          parent_category_businessType: Ext.getCmp(
            "parent_category_businessType"
          ).getValue(),
          pc_isHome: Ext.getCmp("pc_isHome").getValue(),
          pc_isInCategory: Ext.getCmp("pc_isInCategory").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (
              Application.MyphaProductmasters.ParentCategoryAddEdit == "Add"
            ) {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterListingParentCategory")
              );
              Ext.getCmp("formpanelMasterParentCategory").getForm().reset();
              Ext.getCmp("gridpanelMasterListingParentCategory").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterListingParentCategory")
                .getStore()
                .reload(lastOptions);
              var gridPanel = Ext.getCmp(
                "gridpanelMasterListingParentCategory"
              );
              gridPanel.getSelectionModel().selectRow(index);
              Application.MyphaProductmasters.ViewParentCategoryMode(
                tmp.data.parent_category_id
              );
            }
            Application.MyphaProductmasters.ParentCategoryAddEdit = "";
            Application.MyphaProductmasters.ViewParentCategoryMode(
              tmp.data.parent_category_id
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
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var parentCategoryDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "xtemplateMasterParentCategoryViewDetails",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">BusinessType </th><td>  {business_type_name} </td></tr>',
        '<tr><th width="40%">Department </th><td>  {parent_category} </td></tr>',
        '<tr><th width="40%">Home Menu</th><td>  {pc_isHome} </td></tr>',
        '<tr><th width="40%">In Category List</th><td>  {pc_isInCategory} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        '<tpl if="image_url != null">',
        "<tpl if=\"image_url != ''\">",
        "<tr><td>",
        '<div border=0 ><img border=0 width="200" id="MasterParentViewPanel" height="200" src="{image_url}"></img></div>',
        "</td></tr>",
        "</tpl>",
        "</tpl>",
        "</table>",
        "</div>"
      ),
    });
  };
  var parentCategoryGrid = function () {
    var _parentCategoryGridstore = parentCategoryGridstore();
    var _parentCategoryFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "parent_category",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "string",
          dataIndex: "parent_category_businessType",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isHome",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isInCategory",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "hasImage",
        },
      ],
    });
    _parentCategoryFilter.remote = true;
    _parentCategoryFilter.autoReload = true;
    var _gridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _parentCategoryGridstore,
      //iconCls: 'money',
      id: "gridpanelMasterListingParentCategory",
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_parentCategoryFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Department",
          dataIndex: "parent_category",
          sortable: true,
          tooltip: "Department",
          hideable: true,
        },        
        {
          header: "Retail Category",
          dataIndex: "parent_category_businessType",
          sortable: true,
          tooltip: "Retail Category",
        },{
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },
        {
          header: "Home Menu",
          sortable: true,
          dataIndex: "isHome",
          tooltip: "Home Menu",
        },
        {
          header: "In Category List",
          sortable: true,
          dataIndex: "isInCategory",
          tooltip: "In Category List",
        },
        {
          header: "Has Image",
          sortable: true,
          dataIndex: "hasImage",
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
              parentcatActionMenu.showAt(e.getXY());
              //action
            },
          },
        },
        /*{
                         xtype: 'actioncolumn',
                         header: 'Action',
                         hideable: true,
                         //iconCls: 'downarrow',
                         sortable: false,
                         items: [{
                         tooltip: 'Parent Category Image',
                         getClass: function (v, meta, rec) {
                         this.items[0].tooltip = 'Upload Parent Category Image';
                         return 'upload';
                         },
                         handler: function (grid, rowIndex, colIndex) {
                         
                         var record = grid.store.getAt(rowIndex);
                         var parentcat_id = record.data.parent_category_id;
                         var uploadtype = 'parentcategory';
                         Ext.Ajax.request({
                         url: modURL + '&op=getparentcategoryImage',
                         method: 'POST',
                         params: {
                         parentcat_id: parentcat_id
                         },
                         success: function (res) {
                         var tmp = Ext.decode(res.responseText);
                         console.log("temp is -", tmp);
                         if (tmp.data != '')
                         {
                         var img_url = tmp.data[0].image_url;
                         Application.MyphaProductmasters.uploadimageCategory(parentcat_id, uploadtype, img_url);
                         }
                         else {
                         var img_url = '';
                         Application.MyphaProductmasters.uploadimageCategory(parentcat_id, uploadtype, img_url);
                         
                         }
                         }
                         })
                         
                         }
                         }
                         
                         
                         
                         ]
                         }*/
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _parentCategoryGridstore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_parentCategoryFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedparentCategory,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("parent_category_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.parent_category_id = ID;
            Ext.getCmp("formpanelMasterParentCategory").hide();
            Application.MyphaProductmasters.ViewParentCategoryMode(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _parentCategoryGridstore.load();
        },
      },
      tbar: [
        {
          text: "Create Department",
          tooltip: "Create Department",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.ParentCategoryAddEdit = "Add";
            var masterForm = Ext.getCmp(
              "formpanelMasterParentCategory"
            ).getForm();
            Ext.getCmp("panelMasterParentCategoryParent").setTitle(
              "Create Department Details"
            );
            loadedForm = null;
            masterForm.reset();
            Ext.getCmp("textfieldMasterParentCategory").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveParentCategory")) { ?> */
            Ext.getCmp("buttonMasterParentCategoryEdit").hide();
            Ext.getCmp("buttonMasterParentCategorySave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterParentCategoryCancel").show();
            Ext.getCmp("formpanelMasterParentCategory").show();
            Ext.getCmp("comboMasterParentCategorystatus").setValue(1);
            Ext.getCmp("xtemplateMasterParentCategoryViewDetails").hide();
            Ext.getCmp("panelMasterParentCategoryParent").doLayout();
          },
        },
      ],
    });
    return _gridPanel;
  };
  var parentcatActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Upload Image",
        handler: function () {
          var parentcat_id = Ext.getCmp("gridpanelMasterListingParentCategory")
            .getSelectionModel()
            .getSelections()[0].data.parent_category_id;
          var uploadtype = "parentcategory";
          Ext.Ajax.request({
            url: modURL + "&op=getparentcategoryImage",
            method: "POST",
            params: {
              parentcat_id: parentcat_id,
            },
            success: function (res) {
              var tmp = Ext.decode(res.responseText);
              console.log("temp is -", tmp);
              if (tmp.data != "") {
                var img_url = tmp.data[0].image_url;
                Application.MyphaProductmasters.uploadimageCategory(
                  parentcat_id,
                  uploadtype,
                  img_url
                );
              } else {
                var img_url = "";
                Application.MyphaProductmasters.uploadimageCategory(
                  parentcat_id,
                  uploadtype,
                  img_url
                );
              }
            },
          });
        },
      },
    ],
  });
  var subCategoryMasterForms = function () {
    var pribusinessTypeComboStore = businessTypeComboStorePrimary();
    var deptComboStore = departmentComboStore();
    var categComboStore = categoryComboStore();
    var panel = new Ext.form.FormPanel({
      frame: false,
      border: true,
      hideBorders: true,
      labelWidth: 120,
      labelAlign: "top",
      fileUpload: true,
      autoScroll: true,
      hidden: true,
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      id: "subcatMasterForm",
      items: [
        {
          xtype: "textfield",
          fieldLabel: "Sub Category",
          id: "sub_cat",
          name: "n[sub_category]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
        },
        {
          xtype: "hidden",
          id: "sub_category_id",
          name: "n[sub_category_id]",
        },
        {
          xtype: "combo",
          store: pribusinessTypeComboStore,
          mode: "local",
          id: "primary_businessTypesc",
          allowBlank: true,
          fieldLabel: "Retail Category",
          hiddenName: "n[primary_businessTypesc]",
          displayField: "business_type_name",
          valueField: "business_type_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function () {
              var value = Ext.getCmp("primary_businessTypesc").getValue();
              deptComboStore.baseParams.primaryBt = this.value;
              deptComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: deptComboStore,
          mode: "local",
          id: "parent_categorysc",
          allowBlank: true,
          fieldLabel: "Department",
          hiddenName: "n[parent_categorysc]",
          displayField: "parent_category",
          valueField: "parent_category_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function (combo, record, index) {
              var value = Ext.getCmp("parent_categorysc").getValue();
              categComboStore.baseParams.department = this.value;
              categComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: categComboStore,
          mode: "local",
          id: "main_category",
          allowBlank: true,
          fieldLabel: "Category",
          hiddenName: "n[main_category]",
          displayField: "category_name",
          valueField: "category_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function (combo, record, index) {
              var value = record.data.category_id;
              if (value > 0) {
                Ext.Ajax.request({
                  url: modURL + "&op=getItemCategory",
                  method: "POST",
                  params: { category_id: value },
                  success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    console.log("getItemCategory", tmp);
                    Ext.getCmp("iteParentCategorysub").show();
                    Ext.getCmp("iteParentCategorysub").setValue(
                      tmp.categoryCombination
                    );
                    //Ext.getCmp('iteMidCategory').setValue(tmp.iteMidCategory);
                  },
                  failure: function () {
                    Ext.MessageBox.alert(
                      "Error",
                      "Error occured while sending data"
                    );
                  },
                });
              }
            },
          },
        },
        {
          hideLabel: true,
          xtype: "displayfield",
          fieldLabel: " ",
          width: 150,
          hidden: true,
          id: "iteParentCategorysub",
          style: { "font-weight": "bold" },
          anchor: "97%",
        },
        {
          xtype: "panel",
          layout: "column",
          frame: false,
          border: false,
          items: [
            {
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isHome",
                  name: "n[isHome]",
                  inputValue: 1,
                  boxLabel: "Include in Home Menu",
                },
              ],
            },
            {
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isInCategory",
                  name: "n[isInCategory]",
                  inputValue: 1,
                  boxLabel: "Show in Category List",
                },
              ],
            },{
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isCompositionDelator",
                  name: "n[isCompositionDelator]",
                  inputValue: 1,
                  boxLabel: "Composition Delater",
                },
              ],
            },{
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isNonGstRetailer",
                  name: "n[isNonGstRetailer]",
                  inputValue: 1,
                  boxLabel: "Non GST Retailer",
                },
              ],
            },{
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isPerishable",
                  name: "n[sbc_isPerishable]",
                  inputValue: 1,
                  boxLabel: "Perishable",
                },
              ],
            },{
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_hasRestaurantService",
                  name: "n[sbc_hasRestaurantService]",
                  inputValue: 1,
                  boxLabel: "Restaurant Service",
                },
              ],
            },{
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isProductDetail",
                  name: "n[sbc_isProductDetail]",
                  inputValue: 1,
                  boxLabel: "Product Detail",
                },
              ],
            },{
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_hasAgeVerification",
                  name: "n[sbc_hasAgeVerification]",
                  inputValue: 1,
                  boxLabel: "Age Restiction",
                },
              ],
            },{
              layout: "form",
              columnWidth: 1,
              frame: false,
              border: true,
              items: [{
                xtype: 'radiogroup',
                id: 'radiobuttonPackingMode',
                title:'Package Mode',
                items: [
                    {boxLabel: 'Group Packing', name: 'rb-auto', inputValue: 0, labelWidth: 100},
                    {boxLabel: 'Pack the items independently', name: 'rb-auto', inputValue: 1, labelWidth: 100},
                    {boxLabel: 'Pack same items together', name: 'rb-auto', inputValue: 2, labelWidth: 100}

                ],
                listeners: {
                    change: function (event, checked)
                    {
                        var current_firstid = event.items.items[0].inputValue;
                        var current_secondid = event.items.items[1].inputValue;
                        var radioid = Ext.getCmp('radiobuttonPackingMode').getValue();
                    }
                }
            }
              ],
            },{
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                xtype: "numberfield",
                fieldLabel: "Processing Time in minutes",
                id: "sbc_processingTime",
                name: "n[sbc_processingTime]",
                anchor: "98%",
                allowBlank: false,
                tabIndex: 5,
                minValue: 1,
              }            
              ]
            },{
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [    
                mkCombo({
                type: STATUS_COMBO_DATA,
                value: "id",
                display: "text",
                name: "n[status]",
                fieldLabel: "Status",
                emptyText: "Set status..",
                tabIndex: 3,
                id: "status",
              })        
              ]
            }
          ],
        }
      ],listeners: {
        afterrender: function () {
          if (
            Ext.isEmpty(
              Ext.getCmp("sub_category_id").getValue()
            )
          ) {
            var recordSelected = Ext.getCmp("status")
              .getStore()
              .getAt(0);
            Ext.getCmp("status").setValue(
              recordSelected.get("id")
            );
          }
        },
      }
    });
    return panel;
  };
  var subCategoryMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "subCatMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Sub Category </th><td> {sub_cat} </td></tr>',
        '<tr><th width="40%">Category </th><td> {main_categoryName} </td></tr>',
        '<tr><th width="40%">Department </th><td> {parent_categoryname} </td></tr>',
        '<tr><th width="40%">Retail Category </th><td> {btName} </td></tr>',
        '<tr><th width="40%">Home Menu</th><td> {sbc_isHome} </td></tr>',
        '<tr><th width="40%">In Category List</th><td> {sbc_isInCategory} </td></tr>',
        '<tr><th width="40%">Composition Delator</th><td> {sbc_isCompositionDelator} </td></tr>',
        '<tr><th width="40%">Non GST Retailer</th><td> {sbc_isNonGstRetailer} </td></tr>',
        '<tr><th width="40%">Perishable</th><td> {sbc_isPerishable} </td></tr>',
        '<tr><th width="40%">Restaurant Service</th><td> {sbc_hasRestaurantService} </td></tr>',
        '<tr><th width="40%">Product Detail</th><td> {sbc_isProductDetail} </td></tr>',
        '<tr><th width="40%">Processing Time</th><td> {processingTime} </td></tr>',
        '<tr><th width="40%">Packing Mode</th><td> {packingModeName} </td></tr>',
        '<tr><th width="40%">Age Restriction</th><td> {sbc_hasAgeVerification} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        '<tpl if="sub_category_image != null">',
        "<tpl if=\"sub_category_image != ''\">",
        "<tr><td>",
        '<div border=0 ><img border=0 width="200" id="catDtlsViewPanel" height="200" src="{sub_category_image}"></img></div>',
        "</td></tr>",
        "</tpl>",
        "</tpl>",
        "</table>",
        "</div>"
      ),
    });
  };
  var masterPanelforSubCat = function (id) {
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Sub Category",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        subCategoryGrid(),
        new Ext.Panel({
          title: "Sub Category Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "subCategoryparentPanel",
          height: winsize.height * 0.6,
          items: [subCategoryMasterForms(), subCategoryMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "subCatCancelBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("subcatMasterGrid")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("subcatMasterGrid")
                    .getSelectionModel()
                    .getSelections()[0].data.sub_category_id;
                  Application.MyphaProductmasters.ViewMode(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveSubcategory")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "subCatEditBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              hidden: true,
              handler: function () {
                var ID = Ext.getCmp("subcatMasterGrid")
                  .getSelectionModel()
                  .getSelections()[0].data.sub_category_id;
                Application.MyphaProductmasters.EditView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "subCatSaveBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                savesubCatgeory();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return panel;
  };
  var subCategoryGrid = function () {
    var _subCatGridFilter = new Ext.ux.grid.GridFilters({
      filters: [{
        type: "string",
        dataIndex: "business_type_name",
      },{
        type: "string",
        dataIndex: "parent_category",
      },
        {
          type: "string",
          dataIndex: "sub_cat",
        },
        {
          type: "string",
          dataIndex: "category_name",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isHome",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isInCategory",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "hasImage",
        },{
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "substatus",
        },
      ],
    });
    _subCatGridFilter.remote = true;
    _subCatGridFilter.autoReload = true;
    var _masterStore = masterStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _masterStore,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      //iconCls: 'my-icon444',
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_subCatGridFilter],
      id: "subcatMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),  
        {
          header: "Sub Category",
          sortable: true,
          dataIndex: "sub_cat",
          tooltip: "Sub Category",
          hideable: true,
        },  {
          header: "Category",
          sortable: true,
          dataIndex: "category_name",
          tooltip: "Category",
        },   {
          header: "Department",
          sortable: true,
          dataIndex: "parent_category",
          tooltip: "Department",
        },  {
          header: "Retail Category",
          sortable: true,
          dataIndex: "business_type_name",
          tooltip: "Retail Category",
        }, 
        {
          header: "Home Menu",
          sortable: true,
          dataIndex: "isHome",
          tooltip: "Home Menu",
        },
        {
          header: "In Category List",
          sortable: true,
          dataIndex: "isInCategory",
          tooltip: "In Category List",
        },
        {
          header: "Has Image",
          sortable: true,
          dataIndex: "hasImage",
          tooltip: "Has Image",
        },{
          header: "Status",
          sortable: true,
          dataIndex: "substatus",
          tooltip: "Status",
        },{
          header: "Processing Time",
          sortable: true,
          dataIndex: "processingTime",
          tooltip: "Processing Time",
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
              subcatActionMenu.showAt(e.getXY());
              //action
            },
          },
        },
        //                {
        //                    xtype: 'actioncolumn',
        //                    header: 'Action',
        //                    hideable: true,
        //                    width: 40,
        //                    items: [/*<?php if (user_access("masterdata", "updateMarginDistribution")) { ?> */ {
        //                            tooltip: 'Update Margin Distrbution',
        //                            iconCls: 'my-margin',
        //                            handler: function (grid, rowIndex, colIndex) {
        //                                var _mdstore = grid.getStore();
        //                                var _mdrec = _mdstore.getAt(rowIndex);
        //                                var _masterId = grid.id.toString().replace('masterGrid_', '');
        //                                Application.MyphaProductmastersData.updateMarginDistribution(_mdrec.get('sub_category_id'), _masterId);
        //                            }
        //                        }, /*<?php } ?> */  /*<?php if (user_access("mypha_productmasters", "saveSubCategoryImage")) { ?> */
        //                        {
        //                            iconCls: 'upload',
        //                            tooltip: 'Upload Sub Category Image',
        //                            handler: function (grid, rowIndex, colIndex) {
        //
        //                                var record = grid.store.getAt(rowIndex);
        //                                var subcat_id = record.data.sub_category_id;
        //                                var uploadtype = 'subcategory';
        //                                Ext.Ajax.request({
        //                                    url: modURL + '&op=getsubcategoryImage',
        //                                    method: 'POST',
        //                                    params: {
        //                                        subcat_id: subcat_id
        //                                    },
        //                                    success: function (res) {
        //                                        var tmp = Ext.decode(res.responseText);
        //                                        console.log("temp is -", tmp);
        //                                        if (tmp.data != '')
        //                                        {
        //                                            var img_url = tmp.data[0].sub_category_image;
        //                                            Application.MyphaProductmasters.uploadimageCategory(subcat_id, uploadtype, img_url);
        //                                        }
        //                                        else {
        //                                            var img_url = '';
        //                                            Application.MyphaProductmasters.uploadimageCategory(subcat_id, uploadtype, img_url);
        //
        //                                        }
        //                                    }
        //                                })
        //
        //                            }
        //                        }/*<?php } ?> */
        //
        //                    ]
        //                }
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChanged,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("sub_category_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.sub_category_id = ID;
            Ext.getCmp("subcatMasterForm").hide();
            Application.MyphaProductmasters.ViewMode(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _masterStore.load();
        },
      },
      tbar: [
        {
          text: "Create Sub Category",
          tooltip: "Create Sub Category",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.subcatAddEdit = "Add";
            var masterForm = Ext.getCmp("subcatMasterForm").getForm();
            loadedForm = null;
            masterForm.reset();
            Ext.getCmp("sub_cat").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveSubcategory")) { ?> */
            Ext.getCmp("subCatEditBtn").hide();
            Ext.getCmp("subCatSaveBtn").show();
            /*<?php } ?> */
            Ext.getCmp("subCatCancelBtn").show();
            Ext.getCmp("subcatMasterForm").show();
            Ext.getCmp("status").setValue(1);
            Ext.getCmp("subCatMasterDetailsViewPanel").hide();
            Ext.getCmp("subCategoryparentPanel").doLayout();
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _masterStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_subCatGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  var subcatActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Upload Image",
        handler: function () {
          var subcat_id = Ext.getCmp("subcatMasterGrid")
            .getSelectionModel()
            .getSelections()[0].data.sub_category_id;
          var uploadtype = "subcategory";
          Ext.Ajax.request({
            url: modURL + "&op=getsubcategoryImage",
            method: "POST",
            params: {
              subcat_id: subcat_id,
            },
            success: function (res) {
              var tmp = Ext.decode(res.responseText);
              console.log("temp is -", tmp);
              if (tmp.data != "") {
                var img_url = tmp.data[0].sub_category_image;
                Application.MyphaProductmasters.uploadimageCategory(
                  subcat_id,
                  uploadtype,
                  img_url
                );
              } else {
                var img_url = "";
                Application.MyphaProductmasters.uploadimageCategory(
                  subcat_id,
                  uploadtype,
                  img_url
                );
              }
            },
          });
        },
      },
    ],
  });
  var masterStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSubcategory",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "sub_category_id",
          root: "data",
        },
        [
          "sub_cat",
          "sub_category_id",
          "isHome",
          "isInCategory",
          "hasImage","business_type_name","parent_category","category_name","substatus","processingTime"
        ]
      ),
      sortInfo: {
        field: "sub_category_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("subcatMasterGrid").getSelectionModel().selectRow(0);
          }
        },
      },
    });
    return _store;
  };
  var savesubCatgeory = function () {
    if (Ext.getCmp("subcatMasterGrid").getStore().getCount() > 0) {
      var index = Ext.getCmp("subcatMasterGrid")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }

    var lastOptions = Ext.getCmp("subcatMasterGrid").getStore().lastOptions;
    var subcatid = Ext.getCmp("sub_category_id").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("sub_cat").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("status").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("main_category").getValue() &&
      (Ext.getCmp("sbc_processingTime").getValue() > 0))
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveSubcategory",
        method: "POST",
        params: {
          id: Ext.getCmp("sub_category_id").getValue(),
          name: Ext.getCmp("sub_cat").getValue(),
          status: Ext.getCmp("status").getValue(),
          main_category: Ext.getCmp("main_category").getValue(),
          sbc_isHome: Ext.getCmp("sbc_isHome").getValue(),
          sbc_isInCategory: Ext.getCmp("sbc_isInCategory").getValue(),
          sbc_isPerishable: Ext.getCmp("sbc_isPerishable").getValue(),
          sbc_isCompositionDelator: Ext.getCmp("sbc_isCompositionDelator").getValue(),
          sbc_isNonGstRetailer: Ext.getCmp("sbc_isNonGstRetailer").getValue(),
          sbc_hasRestaurantService:Ext.getCmp("sbc_hasRestaurantService").getValue(),
          sbc_isProductDetail: Ext.getCmp("sbc_isProductDetail").getValue(),
          sbc_processingTime: Ext.getCmp("sbc_processingTime").getValue(),
          packingMode: Ext.getCmp('radiobuttonPackingMode').getValue(),
          sbc_hasAgeVerification: Ext.getCmp("sbc_hasAgeVerification").getValue()
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.subcatAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp("subcatMasterGrid"));
              Ext.getCmp("subcatMasterGrid").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("subcatMasterGrid").getStore().reload(lastOptions);
              var gridPanel = Ext.getCmp("subcatMasterGrid");
              gridPanel.getSelectionModel().selectRow(index);
              Application.MyphaProductmasters.ViewMode(
                tmp.data.sub_category_id
              );
            }
            Application.MyphaProductmasters.subcatAddEdit = "";
            Application.MyphaProductmasters.ViewMode(tmp.data.sub_category_id);
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
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var gridSelectionChangedbrand = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterListingBrandname")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterListingBrandname")
        .getSelectionModel()
        .getSelections()[0].data.brand_id;
      Application.MyphaProductmasters.ViewBrandMode(ID);
    }
  };
  var BrandListingGridstore = function () {
    var _brandList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listBrands",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "brand_id",
          root: "data",
        },
        [
          "brand_id",
          "brand_name",
          "manufacture_name",
          "top_brand",
          "status",
          "img_url",
          "img_name",
        ]
      ),
      sortInfo: {
        field: "brand_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("gridpanelMasterListingBrandname")
              .getSelectionModel()
              .selectRow(0);
          }
          //console.log('loadCount: ' + loadCount);
        },
      },
    });
    return _brandList;
  };
  var BrandListingGrid = function () {
    var _brandListingGridstore = BrandListingGridstore();
    var _BrandListingFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "brand_name",
        },
        {
          type: "string",
          dataIndex: "manufacture_name",
        },
        {
          type: "list",
          options: ["Y", "N"],
          phpMode: true,
          dataIndex: "top_brand",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _BrandListingFilter.remote = true;
    _BrandListingFilter.autoReload = true;
    var _gridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _brandListingGridstore,
      //iconCls: 'money',
      id: "gridpanelMasterListingBrandname",
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_BrandListingFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brand",
          dataIndex: "brand_name",
          sortable: true,
          tooltip: "Brand",
          hideable: true,
        },
        {
          header: "Manufacturer/Supplier",
          dataIndex: "manufacture_name",
          sortable: true,
          tooltip: "Manufacturer/Supplier",
          hideable: true,
        },
        {
          header: "Top Brand",
          dataIndex: "top_brand",
          sortable: true,
          tooltip: "Is Top Brand?",
        },
        {
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
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
              brandActionMenu.showAt(e.getXY());
              //action
            },
          },
        },
        /*{
                         xtype: 'actioncolumn',
                         header: 'Action',
                         hideable: false,
                         width: 40,
                         items: [{
                         sortable: false,
                         getClass: function (v, meta, rec) {
                         this.items[0].tooltip = 'Upload Brand Image';
                         return 'upload';
                         
                         },
                         handler: function (grid, rowIndex, colIndex) {
                         var record = grid.store.getAt(rowIndex);
                         var brand_id = record.data.brand_id;
                         var uploadtype = 'brand';
                         Ext.Ajax.request({
                         url: modURL + '&op=getBrandImage',
                         method: 'POST',
                         params: {
                         brand_id: brand_id
                         },
                         success: function (res) {
                         var tmp = Ext.decode(res.responseText);
                         console.log("temp is -", tmp);
                         if (tmp.data != '')
                         {
                         var img_url = tmp.data[0].img_url;
                         Application.MyphaProductmasters.uploadimageCategory(brand_id, uploadtype, img_url);
                         }
                         else {
                         var img_url = '';
                         Application.MyphaProductmasters.uploadimageCategory(brand_id, uploadtype, img_url);
                         
                         }
                         }
                         })
                         
                         }
                         }]
                         }*/
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _brandListingGridstore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_BrandListingFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedbrand,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("brand_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.brand_id = ID;
            Ext.getCmp("formpanelMasterBrandSave").hide();
            Application.MyphaProductmasters.ViewBrandMode(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _brandListingGridstore.load();
        },
        beforeclose: function () {
          var filter = Ext.getCmp(
            "gridpanelMasterListingBrandname"
          ).store.isFiltered();
          console.log("filter is", filter);
        },
      },
      tbar: [
        {
          text: "Create Brand",
          tooltip: "Create Brand",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.BrandAddEdit = "Add";
            var masterForm = Ext.getCmp("formpanelMasterBrandSave").getForm();
            Ext.getCmp("panelMasterBrandsParent").setTitle(
              "Create Brand Details"
            );
            loadedForm = null;
            masterForm.reset();
            Ext.getCmp("brand_name").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveBrands")) { ?> */
            Ext.getCmp("buttonMasterBrandsEdit").hide();
            Ext.getCmp("buttonMasterBrandsSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterBrandsCancel").show();
            Ext.getCmp("formpanelMasterBrandSave").show();
            Ext.getCmp("comboMasterBrandsstatus").setValue(1);
            Ext.getCmp("xtemplateMasterBrandViewDetails").hide();
            Ext.getCmp("panelMasterBrandsParent").doLayout();
          },
        },
      ],
    });
    return _gridPanel;
  };
  var brandActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Upload Image",
        handler: function () {
          var brand_id = Ext.getCmp("gridpanelMasterListingBrandname")
            .getSelectionModel()
            .getSelections()[0].data.brand_id;
          var uploadtype = "brand";
          Ext.Ajax.request({
            url: modURL + "&op=getBrandImage",
            method: "POST",
            params: {
              brand_id: brand_id,
            },
            success: function (res) {
              var tmp = Ext.decode(res.responseText);
              console.log("temp is -", tmp);
              if (tmp.data != "") {
                var img_url = tmp.data[0].img_url;
                Application.MyphaProductmasters.uploadimageCategory(
                  brand_id,
                  uploadtype,
                  img_url
                );
              } else {
                var img_url = "";
                Application.MyphaProductmasters.uploadimageCategory(
                  brand_id,
                  uploadtype,
                  img_url
                );
              }
            },
          });
        },
      },
    ],
  });
  var BrandForm = function () {
    var _brandForm = new Ext.FormPanel({
      id: "formpanelMasterBrandSave",
      frame: false,
      border: false,
      hidden: true,
      autoHeight: true,
      labelAlign: "top",
      labelWidth: 100,
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      items: [
        {
          xtype: "textfield",
          fieldLabel: "Name",
          id: "brand_name",
          name: "n[brand_name]",
          anchor: "98%",
          allowBlank: false,
          width: 300,
          tabIndex: 700,
          maxLength: 400,
        },
        {
          xtype: "combo",
          fieldLabel: "Manufacture",
          name: "manufacture_id",
          id: "promanufacture_id",
          anchor: "98%",
          store: _productManufactureStore,
          mode: "local",
          selectOnFocus: true,
          hiddenName: "manufacture_id",
          displayField: "manufacture_name",
          valueField: "manufacture_id",
          typeAhead: true,
          triggerAction: "all",
          lazyRender: true,
          allowBlank: true,
          editable: true,
          hideTrigger: false,
          tabIndex: 701,
        },
        {
          xtype: "textfield",
          id: "brand_id",
          name: "n[brand_id]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 702,
          emptyText: "Set status..",
          id: "comboMasterBrandsstatus",
        }),
        {
          xtype: "checkbox",
          id: "top_brand",
          name: "n[top_brand]",
          inputValue: 1,
          boxLabel: "Top Brand",
        },
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("brand_id").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterBrandsstatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterBrandsstatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _brandForm;
  };
  var _productManufactureStore = new Ext.data.JsonStore({
    method: "post",
    url: modURL + "&op=loadProductManufactureCombo",
    fields: ["manufacture_id", "manufacture_name"],
    totalProperty: "totalCount",
    root: "data",
    autoLoad: true,
    remoteSort: true,
    listeners: {
      beforeload: function () {},
    },
  });
  var BrandsMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "xtemplateMasterBrandViewDetails",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Brand </th><td>  {brand_name} </td></tr>',
        '<tr><th width="40%">Manufacture </th><td>  {manufacture} </td></tr>',
        '<tr><th width="40%">Is top brand?</th><td>{top_brand}</td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        '<tpl if="img_url != null">',
        "<tpl if=\"img_url != ''\">",
        "<tr><td>",
        '<div border=0 ><img border=0 width="200" id="demo123" height="200" src="{img_url}"></img></div>',
        "</td></tr>",
        "</tpl>",
        "</tpl>",
        "</table>",
        "</div>"
      ),
    });
  };
  var saveBrand = function () {
    var brndId = Ext.getCmp("brand_id").getValue();
    if (
      Ext.getCmp("gridpanelMasterListingBrandname").getStore().getCount() > 0
    ) {
      var index = Ext.getCmp("gridpanelMasterListingBrandname")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }

    var lastOptions = Ext.getCmp("gridpanelMasterListingBrandname").getStore()
      .lastOptions;
    if (
      !Ext.isEmpty(Ext.getCmp("brand_name").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterBrandsstatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveBrands",
        method: "POST",
        params: {
          id: Ext.getCmp("brand_id").getValue(),
          name: Ext.getCmp("brand_name").getValue(),
          manufacture: Ext.getCmp("promanufacture_id").getValue(),
          status: Ext.getCmp("comboMasterBrandsstatus").getValue(),
          topbrand: Ext.getCmp("top_brand").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.BrandAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterListingBrandname")
              );
              Ext.getCmp("formpanelMasterBrandSave").getForm().reset();
              Ext.getCmp("gridpanelMasterListingBrandname")
                .getStore()
                .reload({
                  params: {
                    start: 0,
                    limit: RECS_PER_PAGE,
                  },
                });
            } else {
              Ext.getCmp("gridpanelMasterListingBrandname")
                .getStore()
                .reload(lastOptions);
              var gridPanel = Ext.getCmp("gridpanelMasterListingBrandname");
              gridPanel.getSelectionModel().selectRow(index);
              Application.MyphaProductmasters.ViewBrandMode(tmp.data.brand_id);
            }
            Application.MyphaProductmasters.BrandAddEdit = "";
            Application.MyphaProductmasters.ViewBrandMode(tmp.data.brand_id);
          } else if (tmp.success === true && tmp.valid === false) {
            Ext.Msg.alert("Notification.", tmp.message);
          } else if (tmp.success === true && tmp.img_valid === false) {
            Ext.Msg.alert("Notification.", tmp.message);
          } else {
            Ext.Msg.alert("Error", "Something unexpected occurs...");
          }
        },
        failure: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", tmp.msg);
        },
      });
    } else {
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var brandPanelforMaster = function (id) {
    var _brandPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Brand",
      id: id,
      //iconCls: 'my-iconbrand',
      items: [
        BrandListingGrid(),
        new Ext.Panel({
          title: "Brand Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterBrandsParent",
          height: winsize.height * 0.6,
          items: [BrandForm(), BrandsMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 503,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterBrandsCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterListingBrandname")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterListingBrandname")
                    .getSelectionModel()
                    .getSelections()[0].data.brand_id;
                  Application.MyphaProductmasters.ViewBrandMode(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveBrands")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonMasterBrandsEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 502,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterListingBrandname")
                  .getSelectionModel()
                  .getSelections()[0].data.brand_id;
                Application.MyphaProductmasters.EditBrandView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 502,
              cls: "left-right-buttons",
              id: "buttonMasterBrandsSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveBrand(id, name, status);
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
      listeners: {
        close: function () {},
      },
    });
    return _brandPanel;
  };
  var gridSelectionChangedpackagetypes = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
        .getSelectionModel()
        .getSelections()[0].data.package_type_id;
      Application.MyphaProductmasters.ViewPackageTypes(ID);
    }
  };
  var PackageTypesMasterStore = function () {
    var _packagetypesMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listPackageTypes",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "package_type_id",
          root: "data",
        },
        ["package_type_id", "package_type_name", "status"]
      ),
      sortInfo: {
        field: "package_type_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _packagetypesMasterStore;
  };
  var parentcategoryuploadForm = function (img) {
    return new Ext.Panel({
      height: "400",
      items: [
        new Ext.Panel({
          layout: "fit",
          id: "parent_cat_main_image_panel",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<img width="200" id="demo123" height="200" src="{img_root}"></img>',
            "</div>"
          ),
        }),
        {
          xtype: "hidden",
          id: "pc_aws_file_location",
          name: "pc_aws_file_location",
        },
        {
          xtype: "hidden",
          id: "pc_aws_file_bucket",
          name: "pc_aws_file_bucket",
        },
        new Ext.form.FormPanel({
          id: "parent_category_image_upload",
          layout: "form",
          fileUpload: true,
          autoHeight: true,
          frame: true,
          items: [
            {
              xtype: "hidden",
              id: "pc_file_name",
              name: "pc_file_name",
            },
            {
              xtype: "hidden",
              id: "pc_albumBucketName",
              name: "pc_albumBucketName",
            },
            {
              xtype: "hidden",
              id: "pc_accessKey",
              name: "pc_accessKey",
            },
            {
              xtype: "hidden",
              id: "pc_secretKey",
              name: "pc_secretKey",
            },
            {
              xtype: "hidden",
              id: "pc_bucketRegion",
              name: "pc_bucketRegion",
            },
            {
              xtype: "hidden",
              id: "pc_oncompleteurl",
              name: "pc_oncompleteurl",
            },
            {
              xtype: "hidden",
              id: "pc_img_path_db",
              name: "pc_img_path_db",
            },
            {
              xtype: "box",
              width: 200,
              height: 200,
              id: "pc_exist_img_box",
              autoEl: { tag: "img", src: img, width: 200, height: 200 },
            },
          ],
          buttons: [
            {
              xtype: "fileuploadfield",
              id: "parentcategoryimg_file",
              anchor: "98%",
              fieldLabel: "Select File",
              name: "file",
              allowBlank: true,
              buttonOnly: true,
              // hidden: true,
              buttonCfg: {
                text: "Choose File",
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

                  var parentcategoryimg_file = Ext.getCmp(
                    "parentcategoryimg_file"
                  ).getValue();
                  if (parentcategoryimg_file == "") {
                    Ext.Msg.alert(
                      "Notification",
                      "Please choose a scanned file to upload"
                    );
                    return;
                  }

                  var parent_category_image_upload = Ext.getCmp(
                    "parent_category_image_upload"
                  ).getForm();
                  if (parent_category_image_upload.isValid()) {
                    Ext.getCmp("pc_exist_img_box").hide();
                    winLoadMask.show();
                    addparentcategoryPhoto();
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
  var PackageTypeMainGrid = function () {
    var _packagetypesStore = PackageTypesMasterStore();
    var _packagetypesGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "package_type_name",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _packagetypesGridFilter.remote = true;
    _packagetypesGridFilter.autoReload = true;
    var _packagetypesmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _packagetypesStore,
      //iconCls: 'money',
      id: "gridpanelMasterDataviewPackageTypesdata",
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_packagetypesGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Package Type",
          dataIndex: "package_type_name",
          sortable: true,
          tooltip: "Package Type",
          hideable: true,
        },
        {
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _packagetypesStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_packagetypesGridFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedpackagetypes,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("package_type_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.package_type_id = ID;
            Ext.getCmp("formpanelMasterPackageTypes").hide();
            Application.MyphaProductmasters.ViewPackageTypes(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _packagetypesStore.load();
        },
      },
      tbar: [
        {
          text: "Create Package Type",
          tooltip: "Create Package Type",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.PackageTypesAddEdit = "Add";
            var packagetypesForm = Ext.getCmp(
              "formpanelMasterPackageTypes"
            ).getForm();
            Ext.getCmp("panelMasterPackageTypeParent").setTitle(
              "Create Package Type Details"
            );
            loadedForm = null;
            packagetypesForm.reset();
            Ext.getCmp("package_type_name").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "savePackageTypes")) { ?> */
            Ext.getCmp("buttonMasterPackageTypeEdit").hide();
            Ext.getCmp("buttonMasterPackageTypeSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterPackageTypeCancel").show();
            Ext.getCmp("formpanelMasterPackageTypes").show();
            Ext.getCmp("comboMasterPackageTypesStatus").setValue(1);
            Ext.getCmp("panelMasterPackageTypesDetailsView").hide();
            Ext.getCmp("panelMasterPackageTypeParent").doLayout();
          },
        },
      ],
    });
    return _packagetypesmaingridPanel;
  };
  var PackageTypeForm = function () {
    var _packagetypesFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterPackageTypes",
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
          fieldLabel: "Package Type",
          id: "package_type_name",
          name: "n[package_type_name]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
          maxLength: 20,
        },
        {
          xtype: "textfield",
          id: "package_type_id",
          name: "n[package_type_id]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 2,
          emptyText: "Set status..",
          id: "comboMasterPackageTypesStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("package_type_id").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterPackageTypesStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterPackageTypesStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _packagetypesFormPanel;
  };
  var PackageTypeMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelMasterPackageTypesDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {package_type_name} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var savePackageTypes = function () {
    var ptId = Ext.getCmp("package_type_id").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("package_type_name").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterPackageTypesStatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=savePackageTypes",
        method: "POST",
        params: {
          id: Ext.getCmp("package_type_id").getValue(),
          name: Ext.getCmp("package_type_name").getValue(),
          status: Ext.getCmp("comboMasterPackageTypesStatus").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.PackageTypesAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
              );
              Ext.getCmp("formpanelMasterPackageTypes").getForm().reset();
              Ext.getCmp(
                "gridpanelMasterDataviewPackageTypesdata"
              ).store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewPackageTypesdata"
                    );
                    var index = gridPanel.store.find("package_type_id", ptId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
            }
            Application.MyphaProductmasters.PackageTypesAddEdit = "";
            Application.MyphaProductmasters.ViewPackageTypes(
              tmp.data.package_type_id
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
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var masterPanelforPackageType = function (id) {
    var _mpanelforPackageType = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Package Type",
      id: id,
      //iconCls: 'my-icon448',
      items: [
        PackageTypeMainGrid(),
        new Ext.Panel({
          title: "Package Type Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterPackageTypeParent",
          height: winsize.height * 0.6,
          items: [PackageTypeForm(), PackageTypeMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterPackageTypeCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
                    .getSelectionModel()
                    .getSelections()[0].data.package_type_id;
                  Application.MyphaProductmasters.ViewPackageTypes(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "savePackageTypes")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonMasterPackageTypeEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterDataviewPackageTypesdata")
                  .getSelectionModel()
                  .getSelections()[0].data.package_type_id;
                Application.MyphaProductmasters.EditPackageTypesView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "buttonMasterPackageTypeSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                savePackageTypes();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforPackageType;
  };
  var gridSelectionChangedtags = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewTagsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewTagsdata")
        .getSelectionModel()
        .getSelections()[0].data.tag_id;
      Application.MyphaProductmasters.ViewTags(ID);
    }
  };
  var TagsForm = function () {
    var _tagsFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterTags",
      frame: false,
      border: false,
      hidden: true,
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
          fieldLabel: "Tag Name",
          id: "tag_name",
          name: "n[tag_name]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
          maxLength: 20,
        },
        {
          xtype: "textfield",
          id: "tag_id",
          name: "n[tag_id]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 2,
          emptyText: "Set status..",
          id: "comboMasterTagsStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("tag_id").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterTagsStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterTagsStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _tagsFormPanel;
  };
  var TagsMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelMasterTagsDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {tag_name} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var masterPanelforTags = function (id) {
    var _mpanelforTags = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Tags",
      id: id,
      //iconCls: 'my-icon449',
      items: [
        TagsMainGrid(),
        new Ext.Panel({
          title: "Tag Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterTagsParent",
          height: winsize.height * 0.6,
          items: [TagsForm(), TagsMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterTagsCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewTagsdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterDataviewTagsdata")
                    .getSelectionModel()
                    .getSelections()[0].data.tag_id;
                  Application.MyphaProductmasters.ViewTags(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveTags")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonMasterTagsEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterDataviewTagsdata")
                  .getSelectionModel()
                  .getSelections()[0].data.tag_id;
                Application.MyphaProductmasters.EditTags(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "buttonMasterTagsSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveTags();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforTags;
  };
  var TagsMasterStore = function () {
    var _tagssMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listTagsTypes",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "tag_id",
          root: "data",
        },
        ["tag_id", "tag_name", "status"]
      ),
      sortInfo: {
        field: "tag_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewTagsdata").getView().refresh();
          Ext.getCmp("gridpanelMasterDataviewTagsdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _tagssMasterStore;
  };
  var TagsMainGrid = function () {
    var _tagsStore = TagsMasterStore();
    var _tagsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "tag_name",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _tagsGridFilter.remote = true;
    _tagsGridFilter.autoReload = true;
    var _tagsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _tagsStore,
      //iconCls: 'money',
      id: "gridpanelMasterDataviewTagsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _tagsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Tag Name",
          dataIndex: "tag_name",
          sortable: true,
          tooltip: "Tag Name",
          hideable: true,
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
        pageSize: RECS_PER_PAGE,
        store: _tagsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedtags,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("tag_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.tag_id = ID;
            Ext.getCmp("formpanelMasterTags").hide();
            Application.MyphaProductmasters.ViewTags(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _tagsStore.load();
        },
      },
      tbar: [
        {
          text: "Add New",
          tooltip: "Add New ",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.TagsAddEdit = "Add";
            var tagsForm = Ext.getCmp("formpanelMasterTags").getForm();
            Ext.getCmp("panelMasterTagsParent").setTitle("Add Tag Details");
            loadedForm = null;
            tagsForm.reset();
            Ext.getCmp("tag_name").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveTags")) { ?> */
            Ext.getCmp("buttonMasterTagsEdit").hide();
            Ext.getCmp("buttonMasterTagsSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterTagsCancel").show();
            Ext.getCmp("formpanelMasterTags").show();
            Ext.getCmp("comboMasterTagsStatus").setValue(1);
            Ext.getCmp("panelMasterTagsDetailsView").hide();
            Ext.getCmp("panelMasterTagsParent").doLayout();
          },
        },
      ],
    });
    return _tagsmaingridPanel;
  };
  var saveTags = function () {
    var tagId = Ext.getCmp("tag_id").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("tag_name").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterTagsStatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveTags",
        method: "POST",
        params: {
          id: Ext.getCmp("tag_id").getValue(),
          name: Ext.getCmp("tag_name").getValue(),
          status: Ext.getCmp("comboMasterTagsStatus").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.TagsAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewTagsdata")
              );
              Ext.getCmp("formpanelMasterTags").getForm().reset();
              Ext.getCmp("gridpanelMasterDataviewTagsdata").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewTagsdata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewTagsdata"
                    );
                    var index = gridPanel.store.find("tag_id", tagId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
              //                            Ext.getCmp('gridpanelMasterDataviewTagsdata').selModel.getSelected().data = tmp.data;
              //                            Ext.getCmp('gridpanelMasterDataviewTagsdata').getStore().reload();
              //                            Ext.getCmp('gridpanelMasterDataviewTagsdata').getView().refresh();
            }
            Application.MyphaProductmasters.TagsAddEdit = "";
            Application.MyphaProductmasters.ViewTags(tmp.data.tag_id);
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
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var gridSelectionChangedRoas = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewRoasdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewRoasdata")
        .getSelectionModel()
        .getSelections()[0].data.roa_id;
      Application.MyphaProductmasters.ViewRoas(ID);
    }
  };
  var RoasMasterStore = function () {
    var _roasMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listRoas",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "roa_id",
          root: "data",
        },
        ["roa_id", "roa_name", "status"]
      ),
      sortInfo: {
        field: "roa_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewRoasdata").getView().refresh();
          Ext.getCmp("gridpanelMasterDataviewRoasdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _roasMasterStore;
  };
  var RoaMainGrid = function () {
    var _roasStore = RoasMasterStore();
    var _roasGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "roa_name",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _roasGridFilter.remote = true;
    _roasGridFilter.autoReload = true;
    var _roasmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _roasStore,
      //iconCls: 'money',
      id: "gridpanelMasterDataviewRoasdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _roasGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Roa",
          dataIndex: "roa_name",
          sortable: true,
          tooltip: "Roa",
          hideable: false,
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
        pageSize: RECS_PER_PAGE,
        store: _roasStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedRoas,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("roa_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.roa_id = ID;
            Ext.getCmp("formpanelMasterRoas").hide();
            Application.MyphaProductmasters.ViewRoas(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _roasStore.load();
        },
      },
      tbar: [
        {
          text: "Add New",
          tooltip: "Add New ",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.RoasAddEdit = "Add";
            var roasForm = Ext.getCmp("formpanelMasterRoas").getForm();
            Ext.getCmp("panelMasterRoaParent").setTitle("Add Roa Details");
            loadedForm = null;
            roasForm.reset();
            Ext.getCmp("roa_name").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveRoas")) { ?> */
            Ext.getCmp("buttonMasterRoaEdit").hide();
            Ext.getCmp("buttonMasterRoaSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterRoaCancel").show();
            Ext.getCmp("formpanelMasterRoas").show();
            Ext.getCmp("panelMasterRoasDetailsView").hide();
            Ext.getCmp("panelMasterRoaParent").doLayout();
          },
        },
      ],
    });
    return _roasmaingridPanel;
  };
  var RoaForm = function () {
    var _roasFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterRoas",
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
          fieldLabel: "Roa",
          id: "roa_name",
          name: "n[roa_name]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
          maxLength: 20,
        },
        {
          xtype: "textfield",
          id: "roa_id",
          name: "n[roa_id]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 2,
          emptyText: "Set status..",
          id: "comboMasterRoasStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("roa_id").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterRoasStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterRoasStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _roasFormPanel;
  };
  var RoaMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelMasterRoasDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {roa_name} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var saveRoas = function () {
    var ptId = Ext.getCmp("roa_id").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("roa_name").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterRoasStatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveRoas",
        method: "POST",
        params: {
          id: Ext.getCmp("roa_id").getValue(),
          name: Ext.getCmp("roa_name").getValue(),
          status: Ext.getCmp("comboMasterRoasStatus").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.RoasAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewRoasdata")
              );
              Ext.getCmp("formpanelMasterRoas").getForm().reset();
              Ext.getCmp("gridpanelMasterDataviewRoasdata").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewRoasdata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewRoasdata"
                    );
                    var index = gridPanel.store.find("roa_id", ptId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
            }
            Application.MyphaProductmasters.RoasAddEdit = "";
            Application.MyphaProductmasters.ViewRoas(tmp.data.roa_id);
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
  var masterPanelforRoa = function (id) {
    var _mpanelforRoa = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Roas",
      id: id,
      //iconCls: 'my-icon448',
      items: [
        RoaMainGrid(),
        new Ext.Panel({
          title: "Roa Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterRoaParent",
          height: winsize.height * 0.6,
          items: [RoaForm(), RoaMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterRoaCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewRoasdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterDataviewRoasdata")
                    .getSelectionModel()
                    .getSelections()[0].data.roa_id;
                  Application.MyphaProductmasters.ViewRoas(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveRoas")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonMasterRoaEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterDataviewRoasdata")
                  .getSelectionModel()
                  .getSelections()[0].data.roa_id;
                Application.MyphaProductmasters.EditRoasView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "buttonMasterRoaSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveRoas();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforRoa;
  };
  var gridSelectionChangedhsn = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewHsndata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewHsndata")
        .getSelectionModel()
        .getSelections()[0].data.hsn_id_pk;
      Application.MyphaProductmasters.ViewHSN(ID);
    }
  };
  var saveHSN = function () {
    var hsnId = Ext.getCmp("hsn_id_pk").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("hsn_code").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterHSNStatus").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("hsnGst").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveHSN",
        method: "POST",
        params: {
          id: Ext.getCmp("hsn_id_pk").getValue(),
          code: Ext.getCmp("hsn_code").getValue(),
          gst: Ext.getCmp("hsnGst").getValue(),
          cess: Ext.getCmp("hsnCess").getValue(),
          status: Ext.getCmp("comboMasterHSNStatus").getValue(),
          description:Ext.getCmp('hsn_description').getValue(),
          hsn_id:Ext.getCmp("hsn_id").getValue(),
          oldhsn_code: Ext.getCmp("oldhsn_code").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.HSNAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewHsndata")
              );
              Ext.getCmp("formpanelMasterHSN").getForm().reset();
              Ext.getCmp("gridpanelMasterDataviewHsndata").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewHsndata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewHsndata"
                    );
                    var index = gridPanel.store.find("hsn_id_pk", hsnId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
            }
            Application.MyphaProductmasters.HSNAddEdit = "";
            Application.MyphaProductmasters.ViewHSN(tmp.data.hsn_id_pk);
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
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var HSNForm = function () {
    var _hsnFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterHSN",
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
          xtype: "textfield",
          fieldLabel: "HSN Code",
          id: "hsn_code",
          name: "n[hsn_code]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 500,
          maxValue: 100,
          maxLength: 20,
        },
        {
          xtype: "textfield",
          id: "hsn_id_pk",
          name: "n[hsn_id_pk]",
          hidden: true,
        },{
          xtype: "textfield",
          id: "hsn_id",
          name: "n[hsn_id]",
          hidden: true,
        },{
          xtype: "textfield",
          id: "oldhsn_code",
          name: "n[oldhsn_code]",
          hidden: true,
        },
        {
          xtype: "textfield",
          fieldLabel: " GST / VAT %",
          id: "hsnGst",
          name: "n[hsnGst]",
          anchor: "98%",
          allowBlank: false,
          maxValue: 100,
          tabIndex: 501,
          maxLength: 20,
        },{
          xtype: "textarea",
          fieldLabel: " Description",
          id: "hsn_description",
          name: "n[hsn_description]",
          anchor: "98%",
          maxValue: 100,
          tabIndex: 501,
          maxLength: 20,
        },{
          xtype: "numberfield",
          fieldLabel: " CESS %",
          id: "hsnCess",
          name: "n[cess]",
          anchor: "98%",
          minValue: 0,
          tabIndex: 502,
          maxLength: 20,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 503,
          emptyText: "Set status..",
          id: "comboMasterHSNStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("hsn_id_pk").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterHSNStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterHSNStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _hsnFormPanel;
  };
  var HSNMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelMasterHSNDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">HSN Code </th><td>  {hsn_code} </td></tr>',
        '<tr><th width="40%"> GST / VAT % </th><td>{hsnGst}  </td></tr>',
        '<tr><th width="40%"> CESS % </th><td>{hsnCess}  </td></tr>',
        '<tr><th width="40%"> Description</th><td>{hsn_description}  </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var HSNMasterStore = function () {
    var _hsnMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listHSN",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "id",
          root: "data",
        },
        ["hsn_id", "hsn_code", "hsnGst", "status","hsnCess","hsn_id_pk"]
      ),
      sortInfo: {
        field: "id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewHsndata").getView().refresh();
          Ext.getCmp("gridpanelMasterDataviewHsndata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _hsnMasterStore;
  };
  var HSNMainGrid = function () {
    var _hsnStore = HSNMasterStore();
    var _hsnGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "hsn_code",
        },
        {
          type: "string",
          dataIndex: "hsnGst",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _hsnGridFilter.remote = true;
    _hsnGridFilter.autoReload = true;
    var _hsnmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _hsnStore,
      //iconCls: 'money',
      id: "gridpanelMasterDataviewHsndata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _hsnGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "HSN Code",
          dataIndex: "hsn_code",
          sortable: true,
          tooltip: "HSN Code",
          hideable: true,
        },
        {
          header: " GST / VAT %",
          dataIndex: "hsnGst",
          sortable: true,
          tooltip: " GST / VAT %",
          hideable: true,
        },{
          header: " CESS %",
          dataIndex: "hsnCess",
          sortable: true,
          tooltip: " CESS %",
          hideable: true,
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
        pageSize: RECS_PER_PAGE,
        store: _hsnStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedhsn,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("hsn_id_pk");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.hsn_id = ID;
            Ext.getCmp("formpanelMasterHSN").hide();
            Application.MyphaProductmasters.ViewHSN(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _hsnStore.load();
        },
      },
      tbar: [
        {
          text: "Create HSN",
          tooltip: "Create HSN",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.HSNAddEdit = "Add";
            var hsnForm = Ext.getCmp("formpanelMasterHSN").getForm();
            Ext.getCmp("panelMasterHSNParent").setTitle("Create HSN Details");
            loadedForm = null;
            hsnForm.reset();
            Ext.getCmp("hsn_code").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveHSN")) { ?> */
            Ext.getCmp("buttonMasterHSNEdit").hide();
            Ext.getCmp("buttonMasterHSNSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterHSNCancel").show();
            Ext.getCmp("formpanelMasterHSN").show();
            Ext.getCmp("comboMasterHSNStatus").setValue(1);
            Ext.getCmp("panelMasterHSNDetailsView").hide();
            Ext.getCmp("panelMasterHSNParent").doLayout();
          },
        },
      ],
    });
    return _hsnmaingridPanel;
  };
  var masterPanelforHSN = function (id) {
    var _mpanelforHSN = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "HSN",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        HSNMainGrid(),
        new Ext.Panel({
          title: "HSN Code Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterHSNParent",
          height: winsize.height * 0.6,
          items: [HSNForm(), HSNMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 504,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterHSNCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewHsndata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterDataviewHsndata")
                    .getSelectionModel()
                    .getSelections()[0].data.hsn_id_pk;
                  Application.MyphaProductmasters.ViewHSN(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveHSN")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonMasterHSNEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 503,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterDataviewHsndata")
                  .getSelectionModel()
                  .getSelections()[0].data.hsn_id_pk;
                Application.MyphaProductmasters.EditHSNView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 503,
              cls: "left-right-buttons",
              id: "buttonMasterHSNSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveHSN();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforHSN;
  };
  var saveItemName = function () {
    var itemnameId = Ext.getCmp("itemname_id").getValue();
    if (Ext.getCmp("formpanelMasterItemName").getForm().isValid()) {
      Ext.Ajax.request({
        url: modURL + "&op=saveItemName",
        method: "POST",
        params: {
          id: Ext.getCmp("itemname_id").getValue(),
          name: Ext.getCmp("item_name").getValue(),
          itemDisplayName: Ext.getCmp("itemDisplayName").getValue(),
          isItemGroup: Ext.getCmp("isItemGroup").getValue() == true ? 1 : 0,
          status: Ext.getCmp("comboMasterItemNameStatus").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.ItemNameAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewItemNamedata")
              );
              Ext.getCmp("formpanelMasterItemName").getForm().reset();
              Ext.getCmp("gridpanelMasterDataviewItemNamedata").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewItemNamedata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewItemNamedata"
                    );
                    var index = gridPanel.store.find("itemname_id", itemnameId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
              //                            Ext.getCmp('gridpanelMasterDataviewItemNamedata').selModel.getSelected().data = tmp.data;
              //                            Ext.getCmp('gridpanelMasterDataviewItemNamedata').getStore().reload();
              //                            Ext.getCmp('gridpanelMasterDataviewItemNamedata').getView().refresh();
            }
            Application.MyphaProductmasters.ItemNameAddEdit = "";
            Application.MyphaProductmasters.ViewItemName(tmp.data.itemname_id);
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
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var ItemNameMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelMasterItemNameDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name: </th><td>  {item_name} </td></tr>',
        '<tr><th width="40%">Display Name: </th><td>  {itemDisplayName} </td></tr>',
        '<tr><th width="40%">Status: </th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        '<tpl if="iteamGroupImage != null">',
        "<tpl if=\"iteamGroupImage != ''\">",
        "<tr><td>",
        '<div border=0 ><img border=0 width="200" id="prtDtlsViewPanel" height="200" src="{iteamGroupImage}"></img></div>',
        "</td></tr>",
        "</tpl>",
        "</tpl>",
        "</table>",
        "</div>"
      ),
    });
  };
  var ItemNameForm = function () {
    var _itemnameFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterItemName",
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
          xtype: "textfield",
          fieldLabel: "Name",
          id: "item_name",
          name: "n[item_name]",
          anchor: "98%",
          allowBlank: false,
          width: 300,
          tabIndex: 10,
          maxValue: 100,
          maxLength: 50,
        },
        {
          layout: "column",
          frame: false,
          border: false,
          bodyStyle: { "background-color": "white" },
          items: [
            {
              columnWidth: 1,
              layout: "column",
              frame: false,
              border: false,
              items: [
                {
                  layout: "form",
                  columnWidth: 0.5,
                  frame: false,
                  border: false,
                  items: [
                    {
                      xtype: "checkbox",
                      fieldLabel: "Group products under the product master?",
                      hideLabel: false,
                      id: "isItemGroup",
                      tabIndex: 20,
                      name: "n[isItemGroup]",
                      anchor: "90%",
                      allowBlank: true,
                      //value:0,
                      checked: false,
                      listeners: {
                        check: function (cbo, checked) {
                          if (checked == true) {
                            Ext.getCmp("itemDisplayName").show();
                            Ext.getCmp("itemDisplayName").allowBlank = false;
                            Ext.getCmp("isItemGroup").allowBlank = false;
                            //cbo.allowBlank = false
                          } else {
                            Ext.getCmp("itemDisplayName").hide();
                            Ext.getCmp("itemDisplayName").allowBlank = true;
                            Ext.getCmp("isItemGroup").allowBlank = true;
                            //cbo.allowBlank = true
                          }
                        },
                      },
                    },
                  ],
                },
                {
                  layout: "form",
                  columnWidth: 0.5,
                  frame: false,
                  border: false,
                  items: [
                    {
                      xtype: "textfield",
                      fieldLabel: "Display Name",
                      id: "itemDisplayName",
                      name: "n[itemDisplayName]",
                      anchor: "96%",
                      allowBlank: true,
                      width: 10,
                      hidden: true,
                      tabIndex: 30,
                      maxValue: 100,
                      maxLength: 50,
                    },
                  ],
                },
              ],
            },
          ],
        },
        {
          xtype: "textfield",
          id: "itemname_id",
          name: "n[itemname_id]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 40,
          emptyText: "Set status..",
          id: "comboMasterItemNameStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("itemname_id").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterItemNameStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterItemNameStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _itemnameFormPanel;
  };
  var ItemNameMasterStore = function () {
    var _itemnameMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listItemName",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "itemname_id",
          root: "data",
        },
        [
          "itemname_id",
          "item_name",
          "status",
          "isVerified",
          "iteamGroupImage",
          "isItemGroup",
          "itemDisplayName",
        ]
      ),
      sortInfo: {
        field: "itemname_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewItemNamedata").getView().refresh();
          Ext.getCmp("gridpanelMasterDataviewItemNamedata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _itemnameMasterStore;
  };
  var gridSelectionChangeditemname = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewItemNamedata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewItemNamedata")
        .getSelectionModel()
        .getSelections()[0].data.itemname_id;
      Application.MyphaProductmasters.ViewItemName(ID);
    }
  };
  var ItemNameMainGrid = function () {
    var _itemnameStore = ItemNameMasterStore();
    var _itemnameGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "item_name",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isVerified",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "itemDisplayName",
        },
      ],
    });
    _itemnameGridFilter.remote = true;
    _itemnameGridFilter.autoReload = true;
    var _itemnamemaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _itemnameStore,
      //iconCls: 'money',
      id: "gridpanelMasterDataviewItemNamedata",
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_itemnameGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Product Master Name",
          dataIndex: "item_name",
          sortable: true,
          tooltip: "Product Master Name",
          hideable: true,
        },
        {
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },
        {
          header: "Is Verified",
          dataIndex: "isVerified",
          sortable: true,
          tooltip: "Is Verified",
        },
        {
          header: "Grouping",
          dataIndex: "itemDisplayName",
          sortable: true,
          tooltip: "Grouping",
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          //iconCls: 'downarrow',
          tooltip: "Choose Actions",
          getClass: function (v, meta, rec) {
            if (rec.get("isItemGroup") == 1) {
              return "downarrow";
            } else {
              return "hideicon";
            }
          },
          listeners: {
            click: function (a, grid, rowindex, e) {
              var record = grid.store.getAt(rowindex);
              grid.getSelectionModel().selectRow(rowindex);
              var cbx_displayfield = Ext.getCmp(
                "gridpanelMasterDataviewItemNamedata"
              )
                .getSelectionModel()
                .getSelections()[0].data.cbx_displayfield;
              if (record.data.isItemGroup == 1) {
                prdtMasterActionMenu(e, cbx_displayfield);
              }
              //action
            },
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _itemnameStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_itemnameGridFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangeditemname,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("itemname_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.itemname_id = ID;
            Ext.getCmp("formpanelMasterItemName").hide();
            Application.MyphaProductmasters.ViewItemName(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _itemnameStore.load();
        },
      },
      tbar: [
        {
          text: "Create Product Master",
          tooltip: "Create Product Master",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.ItemNameAddEdit = "Add";
            var itemnameForm = Ext.getCmp("formpanelMasterItemName").getForm();
            Ext.getCmp("panelMasterItemNameParent").setTitle(
              "Create Product Master Details"
            );
            loadedForm = null;
            itemnameForm.reset();
            Ext.getCmp("item_name").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveItemName")) { ?> */
            Ext.getCmp("buttonMasterItemNameEdit").hide();
            Ext.getCmp("buttonMasterItemNameSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterItemNameCancel").show();
            /*<?php if (user_access("mypha_productmasters", "verifyProdctMaster")) { ?> */
            Ext.getCmp("buttonMasterItemNameVerify").hide();
            /*<?php } ?> */
            Ext.getCmp("formpanelMasterItemName").show();
            Ext.getCmp("comboMasterItemNameStatus").setValue(1);
            Ext.getCmp("panelMasterItemNameDetailsView").hide();
            Ext.getCmp("panelMasterItemNameParent").doLayout();
          },
        },
      ],
    });
    return _itemnamemaingridPanel;
  };
  var prdtMasterActionMenu = function (e, cbx_displayfield) {
    var prdtMasterActionMenu = new Ext.menu.Menu({
      items: [
        {
          text: "Upload Image",
          handler: function () {
            var itemname_id = Ext.getCmp("gridpanelMasterDataviewItemNamedata")
              .getSelectionModel()
              .getSelections()[0].data.itemname_id;
            var uploadtype = "productMaster";
            Ext.Ajax.request({
              url: modURL + "&op=getprdtMasterImage",
              method: "POST",
              params: {
                itemname_id: itemname_id,
              },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                console.log("temp is -", tmp);
                if (tmp.data != "") {
                  var iteamGroupImage = tmp.data[0].iteamGroupImage;
                  Application.MyphaProductmasters.uploadimageItem(
                    itemname_id,
                    uploadtype,
                    iteamGroupImage
                  );
                } else {
                  var iteamGroupImage = "";
                  Application.MyphaProductmasters.uploadimageItem(
                    itemname_id,
                    uploadtype,
                    iteamGroupImage
                  );
                }
              },
            });
          },
        },
      ],
    });
    prdtMasterActionMenu.showAt(e.getXY());
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
  function addparentcategoryPhoto() {
    var pc_albumBucketName = Ext.getCmp("pc_albumBucketName").getValue();
    var pc_bucketRegion = Ext.getCmp("pc_bucketRegion").getValue();
    var filepath = Ext.getCmp("pc_oncompleteurl").getValue();
    AWS.config.update({
      region: pc_bucketRegion,
      credentials: new AWS.Credentials(
        Ext.getCmp("pc_accessKey").getValue(),
        Ext.getCmp("pc_secretKey").getValue(),
        null
      ),
    });
    var s3 = new AWS.S3({
      apiVersion: "2006-03-01",
      params: { Bucket: pc_albumBucketName },
    });
    var files = document.getElementById("parentcategoryimg_file-file").files;
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
          Ext.getCmp("parent_cat_main_image_panel").update({
            img_root: img_src,
          });
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
          Application.MyphaProductmasters.UploadedFileLocation = data.Location;
          Application.MyphaProductmasters.UploadedFileBucket = data.Bucket;
          Ext.getCmp("pc_aws_file_bucket").setValue(data.Bucket);
          Ext.getCmp("pc_aws_file_location").setValue(data.Location);
          /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
          Ext.getCmp("parent_cat_main_image_panel").update({
            img_root: Application.MyphaProductmasters.UploadedFileLocation,
          });
        }
      }
    );
  }
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
          Application.MyphaProductmasters.UploadedFileLocation = data.Location;
          Application.MyphaProductmasters.UploadedFileBucket = data.Bucket;
          Ext.getCmp("aws_file_bucket").setValue(data.Bucket);
          Ext.getCmp("aws_file_location").setValue(data.Location);
          /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
          Ext.getCmp("cat_main_image_panel").update({
            img_root: Application.MyphaProductmasters.UploadedFileLocation,
          });
        }
      }
    );
  }
  var subcategoryuploadForm = function (img) {
    return new Ext.Panel({
      height: "400",
      items: [
        new Ext.Panel({
          layout: "fit",
          id: "sub_cat_main_image_panel",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<img width="200" id="demo123" height="200" src="{img_root}"></img>',
            "</div>"
          ),
        }),
        {
          xtype: "hidden",
          id: "sc_aws_file_location",
          name: "sc_aws_file_location",
        },
        {
          xtype: "hidden",
          id: "sc_aws_file_bucket",
          name: "sc_aws_file_bucket",
        },
        new Ext.form.FormPanel({
          id: "sub_category_image_upload",
          layout: "form",
          fileUpload: true,
          autoHeight: true,
          frame: true,
          items: [
            {
              xtype: "hidden",
              id: "sc_file_name",
              name: "sc_file_name",
            },
            {
              xtype: "hidden",
              id: "sc_albumBucketName",
              name: "sc_albumBucketName",
            },
            {
              xtype: "hidden",
              id: "sc_accessKey",
              name: "sc_accessKey",
            },
            {
              xtype: "hidden",
              id: "sc_secretKey",
              name: "sc_secretKey",
            },
            {
              xtype: "hidden",
              id: "sc_bucketRegion",
              name: "sc_bucketRegion",
            },
            {
              xtype: "hidden",
              id: "sc_oncompleteurl",
              name: "sc_oncompleteurl",
            },
            {
              xtype: "hidden",
              id: "sc_img_path_db",
              name: "sc_img_path_db",
            },
            {
              xtype: "box",
              width: 200,
              height: 200,
              id: "sc_exist_img_box",
              autoEl: { tag: "img", src: img, width: 200, height: 200 },
            },
          ],
          buttons: [
            {
              xtype: "fileuploadfield",
              id: "subcategoryimg_file",
              anchor: "98%",
              fieldLabel: "Select File",
              name: "file",
              allowBlank: true,
              buttonOnly: true,
              // hidden: true,
              buttonCfg: {
                text: "Choose File",
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

                  var subcategoryimg_file = Ext.getCmp(
                    "subcategoryimg_file"
                  ).getValue();
                  if (subcategoryimg_file == "") {
                    Ext.Msg.alert(
                      "Notification",
                      "Please choose a scanned file to upload"
                    );
                    return;
                  }

                  var sub_category_image_upload = Ext.getCmp(
                    "sub_category_image_upload"
                  ).getForm();
                  if (sub_category_image_upload.isValid()) {
                    Ext.getCmp("sc_exist_img_box").hide();
                    winLoadMask.show();
                    addsubcategoryPhoto();
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
  var branduploadForm = function (img) {
    return new Ext.Panel({
      height: "400",
      items: [
        new Ext.Panel({
          layout: "fit",
          id: "brand_image_panel",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<img width="200" id="demo123" height="200" src="{img_root}"></img>',
            "</div>"
          ),
        }),
        {
          xtype: "hidden",
          id: "br_aws_file_location",
          name: "br_aws_file_location",
        },
        {
          xtype: "hidden",
          id: "br_aws_file_bucket",
          name: "br_aws_file_bucket",
        },
        new Ext.form.FormPanel({
          id: "brand_image_upload",
          layout: "form",
          fileUpload: true,
          autoHeight: true,
          frame: true,
          items: [
            {
              xtype: "hidden",
              id: "br_file_name",
              name: "sc_file_name",
            },
            {
              xtype: "hidden",
              id: "br_albumBucketName",
              name: "br_albumBucketName",
            },
            {
              xtype: "hidden",
              id: "br_accessKey",
              name: "br_accessKey",
            },
            {
              xtype: "hidden",
              id: "br_secretKey",
              name: "br_secretKey",
            },
            {
              xtype: "hidden",
              id: "br_bucketRegion",
              name: "br_bucketRegion",
            },
            {
              xtype: "hidden",
              id: "br_oncompleteurl",
              name: "br_oncompleteurl",
            },
            {
              xtype: "hidden",
              id: "br_img_path_db",
              name: "br_img_path_db",
            },
            {
              xtype: "box",
              width: 200,
              height: 200,
              id: "br_exist_img_box",
              autoEl: { tag: "img", src: img, width: 200, height: 200 },
            },
          ],
          buttons: [
            {
              xtype: "fileuploadfield",
              id: "brandimg_file",
              anchor: "98%",
              fieldLabel: "Select File",
              name: "file",
              allowBlank: true,
              buttonOnly: true,
              // hidden: true,
              buttonCfg: {
                text: "Choose File",
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

                  var brandimg_file = Ext.getCmp("brandimg_file").getValue();
                  if (brandimg_file == "") {
                    Ext.Msg.alert(
                      "Notification",
                      "Please choose a scanned file to upload"
                    );
                    return;
                  }

                  var brand_image_upload =
                    Ext.getCmp("brand_image_upload").getForm();
                  if (brand_image_upload.isValid()) {
                    Ext.getCmp("br_exist_img_box").hide();
                    winLoadMask.show();
                    addbrandPhoto();
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
  function addsubcategoryPhoto() {
    var sc_albumBucketName = Ext.getCmp("sc_albumBucketName").getValue();
    var sc_bucketRegion = Ext.getCmp("sc_bucketRegion").getValue();
    var filepath = Ext.getCmp("sc_oncompleteurl").getValue();
    AWS.config.update({
      region: sc_bucketRegion,
      credentials: new AWS.Credentials(
        Ext.getCmp("sc_accessKey").getValue(),
        Ext.getCmp("sc_secretKey").getValue(),
        null
      ),
    });
    var s3 = new AWS.S3({
      apiVersion: "2006-03-01",
      params: { Bucket: sc_albumBucketName },
    });
    var files = document.getElementById("subcategoryimg_file-file").files;
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
          Ext.getCmp("sub_cat_main_image_panel").update({ img_root: img_src });
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
          Application.MyphaProductmasters.UploadedFileLocation = data.Location;
          Application.MyphaProductmasters.UploadedFileBucket = data.Bucket;
          Ext.getCmp("sc_aws_file_bucket").setValue(data.Bucket);
          Ext.getCmp("sc_aws_file_location").setValue(data.Location);
          /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
          Ext.getCmp("sub_cat_main_image_panel").update({
            img_root: Application.MyphaProductmasters.UploadedFileLocation,
          });
        }
      }
    );
  }
  function addbrandPhoto() {
    var br_albumBucketName = Ext.getCmp("br_albumBucketName").getValue();
    var br_bucketRegion = Ext.getCmp("br_bucketRegion").getValue();
    var filepath = Ext.getCmp("br_oncompleteurl").getValue();
    AWS.config.update({
      region: br_bucketRegion,
      credentials: new AWS.Credentials(
        Ext.getCmp("br_accessKey").getValue(),
        Ext.getCmp("br_secretKey").getValue(),
        null
      ),
    });
    var s3 = new AWS.S3({
      apiVersion: "2006-03-01",
      params: { Bucket: br_albumBucketName },
    });
    var files = document.getElementById("brandimg_file-file").files;
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
          Ext.getCmp("brand_image_panel").update({ img_root: img_src });
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
          Application.MyphaProductmasters.UploadedFileLocation = data.Location;
          Application.MyphaProductmasters.UploadedFileBucket = data.Bucket;
          Ext.getCmp("br_aws_file_bucket").setValue(data.Bucket);
          Ext.getCmp("br_aws_file_location").setValue(data.Location);
          /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
          Ext.getCmp("brand_image_panel").update({
            img_root: Application.MyphaProductmasters.UploadedFileLocation,
          });
        }
      }
    );
  }
  var productMasteruploadForm = function (img) {
    return new Ext.Panel({
      height: "400",
      items: [
        new Ext.Panel({
          layout: "fit",
          id: "prt_master_image_panel",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<img width="200" id="demo123" height="200" src="{img_root}"></img>',
            "</div>"
          ),
        }),
        {
          xtype: "hidden",
          id: "pm_aws_file_location",
          name: "pm_aws_file_location",
        },
        {
          xtype: "hidden",
          id: "pm_aws_file_bucket",
          name: "pm_aws_file_bucket",
        },
        new Ext.form.FormPanel({
          id: "product_master_image_upload",
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
              id: "pm_albumBucketName",
              name: "pm_albumBucketName",
            },
            {
              xtype: "hidden",
              id: "pm_accessKey",
              name: "pm_accessKey",
            },
            {
              xtype: "hidden",
              id: "pm_secretKey",
              name: "pm_secretKey",
            },
            {
              xtype: "hidden",
              id: "pm_bucketRegion",
              name: "pm_bucketRegion",
            },
            {
              xtype: "hidden",
              id: "pm_oncompleteurl",
              name: "pm_oncompleteurl",
            },
            {
              xtype: "hidden",
              id: "pm_img_path_db",
              name: "pm_img_path_db",
            },
            {
              xtype: "box",
              width: 200,
              height: 200,
              id: "pm_exist_img_box",
              autoEl: { tag: "img", src: img, width: 200, height: 200 },
            },
          ],
          buttons: [
            {
              xtype: "fileuploadfield",
              id: "productmasterimg_file",
              anchor: "98%",
              fieldLabel: "Select File",
              name: "file",
              allowBlank: true,
              buttonOnly: true,
              // hidden: true,
              buttonCfg: {
                text: "Choose File",
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

                  var productmasterimg_file = Ext.getCmp(
                    "productmasterimg_file"
                  ).getValue();
                  if (productmasterimg_file == "") {
                    Ext.Msg.alert(
                      "Notification",
                      "Please choose a scanned file to upload"
                    );
                    return;
                  }

                  var product_master_image_upload = Ext.getCmp(
                    "product_master_image_upload"
                  ).getForm();
                  if (product_master_image_upload.isValid()) {
                    Ext.getCmp("pm_exist_img_box").hide();
                    winLoadMask.show();
                    addprtMasterPhoto();
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
  function addprtMasterPhoto() {
    var pm_albumBucketName = Ext.getCmp("pm_albumBucketName").getValue();
    var pm_bucketRegion = Ext.getCmp("pm_bucketRegion").getValue();
    var filepath = Ext.getCmp("pm_oncompleteurl").getValue();
    AWS.config.update({
      region: pm_bucketRegion,
      credentials: new AWS.Credentials(
        Ext.getCmp("pm_accessKey").getValue(),
        Ext.getCmp("pm_secretKey").getValue(),
        null
      ),
    });
    var s3 = new AWS.S3({
      apiVersion: "2006-03-01",
      params: { Bucket: pm_albumBucketName },
    });
    var files = document.getElementById("productmasterimg_file-file").files;
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
          Ext.getCmp("prt_master_image_panel").update({ img_root: img_src });
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
          Application.MyphaProductmasters.UploadedFileLocation = data.Location;
          Application.MyphaProductmasters.UploadedFileBucket = data.Bucket;
          Ext.getCmp("pm_aws_file_bucket").setValue(data.Bucket);
          Ext.getCmp("pm_aws_file_location").setValue(data.Location);
          /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
          Ext.getCmp("prt_master_image_panel").update({
            img_root: Application.MyphaProductmasters.UploadedFileLocation,
          });
        }
      }
    );
  }
  var masterPanelforItemName = function (id) {
    var _mpanelforItemName = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Product Master",
      id: id,
      //iconCls: 'my-icon445',
      items: [
        ItemNameMainGrid(),
        new Ext.Panel({
          title: "Item Name Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterItemNameParent",
          height: winsize.height * 0.6,
          items: [ItemNameForm(), ItemNameMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            /*<?php if (user_access("mypha_productmasters", "verifyProdctMaster")) { ?> */ {
              text: "Verify",
              tabIndex: 50,
              cls: "left-right-buttons",
              id: "buttonMasterItemNameVerify",
              icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
              hidden: true,
              handler: function () {
                var itemname_id = Ext.getCmp(
                  "gridpanelMasterDataviewItemNamedata"
                )
                  .getSelectionModel()
                  .getSelections()[0].data.itemname_id;
                if (itemname_id > 0) {
                  Ext.Ajax.request({
                    url: modURL + "&op=verifyProdctMaster",
                    method: "POST",
                    params: {
                      itemid: itemname_id,
                    },
                    success: function (response) {
                      var tmp = Ext.decode(response.responseText);
                      if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg("Success", tmp.message);
                        if (itemname_id > 0) {
                          Ext.getCmp("gridpanelMasterDataviewItemNamedata")
                            .getStore()
                            .load({
                              callback: function (record, options, success) {
                                var gridPanel = Ext.getCmp(
                                  "gridpanelMasterDataviewItemNamedata"
                                );
                                var index = gridPanel.store.find(
                                  "itemname_id",
                                  itemname_id
                                );
                                gridPanel.getSelectionModel().selectRow(index);
                              },
                            });
                        }
                      } else if (tmp.success === true && tmp.valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                        if (itemname_id > 0) {
                          Ext.getCmp("gridpanelMasterDataviewItemNamedata")
                            .getStore()
                            .load({
                              callback: function (record, options, success) {
                                var gridPanel = Ext.getCmp(
                                  "gridpanelMasterDataviewItemNamedata"
                                );
                                var index = gridPanel.store.find(
                                  "itemname_id",
                                  itemname_id
                                );
                                gridPanel.getSelectionModel().selectRow(index);
                              },
                            });
                        }
                      } else if (
                        tmp.success === true &&
                        tmp.img_valid === false
                      ) {
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
                }
              },
            },
            /*<?php } ?> */ {
              text: "Cancel",
              tabIndex: 60,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterItemNameCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewItemNamedata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterDataviewItemNamedata")
                    .getSelectionModel()
                    .getSelections()[0].data.itemname_id;
                  Application.MyphaProductmasters.ViewItemName(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveItemName")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonMasterItemNameEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 70,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterDataviewItemNamedata")
                  .getSelectionModel()
                  .getSelections()[0].data.itemname_id;
                Application.MyphaProductmasters.EditItemNameView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 80,
              cls: "left-right-buttons",
              id: "buttonMasterItemNameSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveItemName();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforItemName;
  };
  var manufactureGrid = function () {
    var _manufactureGridstore = manufactureGridstore();
    var _manufactureFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "manufacture_name",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _manufactureFilter.remote = true;
    _manufactureFilter.autoReload = true;
    var _gridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _manufactureGridstore,
      //iconCls: 'money',
      id: "gridpanelProductMasterListingManufacture",
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_manufactureFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Manufacturer/Supplier",
          dataIndex: "manufacture_name",
          sortable: true,
          tooltip: "Manufacturer/Supplier",
          hideable: true,
        },
        {
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _manufactureGridstore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_manufactureFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedmanufacture,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("manufacture_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.manufacture_id = ID;
            Ext.getCmp("formpanelProductMasterManufacture").hide();
            Application.MyphaProductmasters.ViewManufactureMode(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _manufactureGridstore.load();
        },
      },
      tbar: [
        /* <?php if (user_access("mypha_productmasters", "saveProductManufacture")) { ?> */ {
          text: "Create Manufacturer/Supplier",
          tooltip: "Create Manufacturer/Supplier",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.ManufactureAddEdit = "Add";
            var masterForm = Ext.getCmp(
              "formpanelProductMasterManufacture"
            ).getForm();
            Ext.getCmp("panelProductMasterManufacture").setTitle(
              "Create Manufacturer/Supplier Details"
            );
            loadedForm = null;
            masterForm.reset();
            Ext.getCmp("textfieldProductMasterManufacture").focus(false, 100);
            Ext.getCmp("buttonProductMasterManufactureEdit").hide();
            Ext.getCmp("buttonProductMasterManufactureSave").show();
            Ext.getCmp("buttonProductMasterManufactureCancel").show();
            Ext.getCmp("formpanelProductMasterManufacture").show();
            Ext.getCmp("xtemplateProductMasterManufactureViewDetails").hide();
            Ext.getCmp("panelProductMasterManufacture").doLayout();
            var recordSelected = Ext.getCmp("manufacturProductStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("manufacturProductStatus").setValue(
              recordSelected.get("id")
            );
          },
        } /*<?php  } ?>*/,
      ],
    });
    return _gridPanel;
  };
  var manufactureGridstore = function () {
    var _manufactureList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listProductManufacture",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "manufacture_id",
          root: "data",
        },
        ["manufacture_id", "manufacture_name", "status", "status"]
      ),
      sortInfo: {
        field: "manufacture_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelProductMasterListingManufacture")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _manufactureList;
  };
  var manufactureForm = function () {
    var _manufactureForm = new Ext.FormPanel({
      id: "formpanelProductMasterManufacture",
      frame: false,
      border: false,
      hidden: true,
      autoHeight: true,
      labelWidth: 100,
      labelAlign: "top",
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      items: [
        {
          xtype: "textfield",
          fieldLabel: "Manufacturer/Supplier",
          id: "textfieldProductMasterManufacture",
          name: "n[manufacture_name]",
          anchor: "95%",
          allowBlank: false,
          tabIndex: 100,
          maxLength: 300,
        },
        {
          xtype: "textfield",
          id: "textfieldProductMasterManufactureId",
          name: "n[manufacture_id]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          anchor: "95%",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 101,
          emptyText: "Set status..",
          id: "manufacturProductStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (
            Ext.isEmpty(
              Ext.getCmp("textfieldProductMasterManufactureId").getValue()
            )
          ) {
            var recordSelected = Ext.getCmp("manufacturProductStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("manufacturProductStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _manufactureForm;
  };
  var saveProductManufacture = function () {
    var mstId;
    var form_data = {
      form: Ext.getCmp("formpanelProductMasterManufacture")
        .getForm()
        .getValues(),
    };
    if (Ext.getCmp("textfieldProductMasterManufactureId").getValue() > 0) {
      mstId = Ext.getCmp("textfieldProductMasterManufactureId").getValue();
    } else {
      var d = new Date();
      mstId = "-";
    }
    var params = {
      action: "Save Manufacture",
      module: "Master",
      op: "saveProductManufacture",
      extrainfo: "Manufacturer save",
      id: mstId,
    };
    APICall(
      params,
      Application.MyphaProductmasters.saveProductManufacture,
      form_data
    );
  };
  var manufactureDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "xtemplateProductMasterManufactureViewDetails",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Manufacturer </th><td>  {manufacture_name} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var manufacturePanel = function (id) {
    var _manufacturePanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Manufacture",
      id: id,
      items: [
        manufactureGrid(),
        new Ext.Panel({
          title: "Manufacturer/Supplier Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelProductMasterManufacture",
          height: winsize.height * 0.6,
          items: [manufactureForm(), manufactureDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 103,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonProductMasterManufactureCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelProductMasterListingManufacture")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp(
                    "gridpanelProductMasterListingManufacture"
                  )
                    .getSelectionModel()
                    .getSelections()[0].data.manufacture_id;
                  Application.MyphaProductmasters.ViewManufactureMode(ID);
                }
              },
            },
            /* <?php if (user_access("mypha_productmasters", "saveProductManufacture")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonProductMasterManufactureEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 104,
              handler: function () {
                var ID = Ext.getCmp("gridpanelProductMasterListingManufacture")
                  .getSelectionModel()
                  .getSelections()[0].data.manufacture_id;
                Application.MyphaProductmasters.EditManufactureView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 102,
              cls: "left-right-buttons",
              id: "buttonProductMasterManufactureSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveProductManufacture();
              },
            } /*<?php  } ?>*/,
          ],
        }),
      ],
    });
    return _manufacturePanel;
  };
  var masterPanelforBusinessType = function (id) {
    var _mpanelforBusinessType = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Retail Category",
      id: id,
      iconCls: "my-icon448",
      items: [
        BusinessTypeMainGrid(),
        new Ext.Panel({
          title: "Retail Category Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterBusinessTypeParent",
          height: winsize.height * 0.6,
          items: [BusinessTypeForm(), BusinessTypeMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            /*<?php if (user_access("mypha_productmasters", "saveBusinessTypes")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              iconCls: "edit",
              id: "buttonMasterBusinessTypeEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
              tabIndex: 4,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterDataviewBusinessTypesdata")
                  .getSelectionModel()
                  .getSelections()[0].data.business_type_id;
                Application.MyphaProductmasters.EditBusinessTypesView(ID);
              },
            },
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterBusinessTypeCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewBusinessTypesdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp(
                    "gridpanelMasterDataviewBusinessTypesdata"
                  )
                    .getSelectionModel()
                    .getSelections()[0].data.business_type_id;
                  Application.MyphaProductmasters.ViewBusinessTypes(ID);
                }
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "buttonMasterBusinessTypeSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveBusinessTypes();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforBusinessType;
  };
  var BusinessTypeForm = function () {
    var _businesstypesFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterBusinessTypes",
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
          fieldLabel: "Retail Category",
          id: "business_type_name",
          name: "n[business_type_name]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
          maxLength: 20,
        },
        {
          xtype: "textfield",
          id: "business_type_id",
          name: "n[business_type_id]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 2,
          emptyText: "Set status..",
          id: "comboMasterBusinessTypesStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("business_type_id").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterBusinessTypesStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterBusinessTypesStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _businesstypesFormPanel;
  };
  var BusinessTypeMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelMasterBusinessTypesDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {business_type_name} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var BusinessTypesMasterStore = function () {
    var _businesstypesMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listBusinessTypes",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "business_type_id",
          root: "data",
        },
        ["business_type_id", "business_type_name", "status"]
      ),
      sortInfo: {
        field: "business_type_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewBusinessTypesdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewBusinessTypesdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _businesstypesMasterStore;
  };
  var BusinessTypeMainGrid = function () {
    var _businesstypesStore = BusinessTypesMasterStore();
    var _businesstypesGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "business_type_name",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _businesstypesGridFilter.remote = true;
    _businesstypesGridFilter.autoReload = true;
    var _businesstypesmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _businesstypesStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewBusinessTypesdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _businesstypesGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Retail Category",
          dataIndex: "business_type_name",
          sortable: true,
          tooltip: "Retail Category",
          hideable: false,
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
        pageSize: RECS_PER_PAGE,
        store: _businesstypesStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedbusinesstypes,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("business_type_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.business_type_id = ID;
            Ext.getCmp("formpanelMasterBusinessTypes").hide();
            Application.MyphaProductmasters.ViewBusinessTypes(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _businesstypesStore.load();
        },
      },
      tbar: [
        {
          text: "Create Retail Category",
          tooltip: "Create Retail Category ",
          icon: "./resources/images/submenuicons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.MyphaProductmasters.BusinessTypesAddEdit = "Add";
            var businesstypesForm = Ext.getCmp(
              "formpanelMasterBusinessTypes"
            ).getForm();
            Ext.getCmp("panelMasterBusinessTypeParent").setTitle(
              "Create Retail Category Details"
            );
            loadedForm = null;
            businesstypesForm.reset();
            Ext.getCmp("business_type_name").focus(false, 100);
            /*<?php if (user_access("mypha_productmasters", "saveBusinessTypes")) { ?> */
            Ext.getCmp("buttonMasterBusinessTypeEdit").hide();
            Ext.getCmp("buttonMasterBusinessTypeSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterBusinessTypeCancel").show();
            Ext.getCmp("formpanelMasterBusinessTypes").show();
            Ext.getCmp("panelMasterBusinessTypesDetailsView").hide();
            Ext.getCmp("panelMasterBusinessTypeParent").doLayout();
          },
        },
      ],
    });
    return _businesstypesmaingridPanel;
  };
  var saveBusinessTypes = function () {
    var ptId = Ext.getCmp("business_type_id").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("business_type_name").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterBusinessTypesStatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveBusinessTypes",
        method: "POST",
        params: {
          id: Ext.getCmp("business_type_id").getValue(),
          name: Ext.getCmp("business_type_name").getValue(),
          status: Ext.getCmp("comboMasterBusinessTypesStatus").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.BusinessTypesAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewBusinessTypesdata")
              );
              Ext.getCmp("formpanelMasterBusinessTypes").getForm().reset();
              Ext.getCmp(
                "gridpanelMasterDataviewBusinessTypesdata"
              ).store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewBusinessTypesdata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewBusinessTypesdata"
                    );
                    var index = gridPanel.store.find("business_type_id", ptId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
              //                            Ext.getCmp('gridpanelMasterDataviewBusinessTypesdata').selModel.getSelected().data = tmp.data;
              //                            Ext.getCmp('gridpanelMasterDataviewBusinessTypesdata').getStore().reload();
              //                            Ext.getCmp('gridpanelMasterDataviewBusinessTypesdata').getView().refresh();
            }
            Application.MyphaProductmasters.BusinessTypesAddEdit = "";
            Application.MyphaProductmasters.ViewBusinessTypes(
              tmp.data.business_type_id
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

  var verifySubCategoryMasterForms = function () {
    var pribusinessTypeComboStore = businessTypeComboStorePrimary();
    var deptComboStore = departmentComboStore();
    var categComboStore = categoryComboStore();
    var panel = new Ext.form.FormPanel({
      frame: false,
      border: true,
      hideBorders: true,
      labelWidth: 120,
      labelAlign: "top",
      fileUpload: true,
      autoScroll: true,
      hidden: true,
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      id: "verifySubCatMasterForm",
      items: [
        {
          xtype: "textfield",
          fieldLabel: "Sub Category",
          id: "sub_cat",
          name: "n[sub_category]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
        },
        {
          xtype: "hidden",
          id: "sub_category_id",
          name: "n[sub_category_id]",
        },
        {
          xtype: "combo",
          store: pribusinessTypeComboStore,
          mode: "local",
          id: "primary_businessTypesc",
          allowBlank: true,
          fieldLabel: "Retail Category",
          hiddenName: "n[primary_businessTypesc]",
          displayField: "business_type_name",
          valueField: "business_type_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function () {
              var value = Ext.getCmp("primary_businessTypesc").getValue();
              deptComboStore.baseParams.primaryBt = this.value;
              deptComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: deptComboStore,
          mode: "local",
          id: "parent_categorysc",
          allowBlank: true,
          fieldLabel: "Department",
          hiddenName: "n[parent_categorysc]",
          displayField: "parent_category",
          valueField: "parent_category_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function (combo, record, index) {
              var value = Ext.getCmp("parent_categorysc").getValue();
              categComboStore.baseParams.department = this.value;
              categComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: categComboStore,
          mode: "local",
          id: "main_category",
          allowBlank: true,
          fieldLabel: "Category",
          hiddenName: "n[main_category]",
          displayField: "category_name",
          valueField: "category_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function (combo, record, index) {
              var value = record.data.category_id;
              if (value > 0) {
                Ext.Ajax.request({
                  url: modURL + "&op=getItemCategory",
                  method: "POST",
                  params: { category_id: value },
                  success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    console.log("getItemCategory", tmp);
                    Ext.getCmp("iteParentCategorysub").show();
                    Ext.getCmp("iteParentCategorysub").setValue(
                      tmp.categoryCombination
                    );
                    //Ext.getCmp('iteMidCategory').setValue(tmp.iteMidCategory);
                  },
                  failure: function () {
                    Ext.MessageBox.alert(
                      "Error",
                      "Error occured while sending data"
                    );
                  },
                });
              }
            },
          },
        },
        {
          hideLabel: true,
          xtype: "displayfield",
          fieldLabel: " ",
          width: 150,
          hidden: true,
          id: "iteParentCategorysub",
          style: { "font-weight": "bold" },
          anchor: "97%",
        },
        {
          xtype: "panel",
          layout: "column",
          frame: false,
          border: false,
          items: [
            {
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isHome",
                  name: "n[isHome]",
                  inputValue: 1,
                  boxLabel: "Include in Home Menu",
                },
              ],
            },
            {
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isInCategory",
                  name: "n[isInCategory]",
                  inputValue: 1,
                  boxLabel: "Show in Category List",
                },
              ],
            },{
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isCompositionDelator",
                  name: "n[isCompositionDelator]",
                  inputValue: 1,
                  boxLabel: "Composition Delater",
                },
              ],
            },{
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isNonGstRetailer",
                  name: "n[isNonGstRetailer]",
                  inputValue: 1,
                  boxLabel: "Non GST Retailer",
                },
              ],
            },{
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isPerishable",
                  name: "n[sbc_isPerishable]",
                  inputValue: 1,
                  boxLabel: "Perishable",
                },
              ],
            },{
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_hasRestaurantService",
                  name: "n[sbc_hasRestaurantService]",
                  inputValue: 1,
                  boxLabel: "Restaurant Service",
                },
              ],
            },{
              columnWidth: 0.25,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_isProductDetail",
                  name: "n[sbc_isProductDetail]",
                  inputValue: 1,
                  boxLabel: "Product Detail",
                },
              ],
            },{
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: true,
                  id: "sbc_hasAgeVerification",
                  name: "n[sbc_hasAgeVerification]",
                  inputValue: 1,
                  boxLabel: "Age Restiction",
                },
              ],
            },{
              layout: "form",
              columnWidth: 1,
              frame: false,
              border: true,
              items: [{
                xtype: 'radiogroup',
                id: 'radiobuttonPackingMode',
                title:'Package Mode',
                items: [
                    {boxLabel: 'Group Packing', name: 'rb-auto', inputValue: 0, labelWidth: 100},
                    {boxLabel: 'Pack the items independently', name: 'rb-auto', inputValue: 1, labelWidth: 100},
                    {boxLabel: 'Pack same items together', name: 'rb-auto', inputValue: 2, labelWidth: 100}

                ],
                listeners: {
                    change: function (event, checked)
                    {
                        var current_firstid = event.items.items[0].inputValue;
                        var current_secondid = event.items.items[1].inputValue;
                        var radioid = Ext.getCmp('radiobuttonPackingMode').getValue();
                    }
                }
            }
              ],
            },{
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [
                {
                xtype: "numberfield",
                fieldLabel: "Processing Time in minutes",
                id: "sbc_processingTime",
                name: "n[sbc_processingTime]",
                anchor: "98%",
                allowBlank: false,
                tabIndex: 5,
                minValue: 1,
              }            
              ]
            },{
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              items: [    
                mkCombo({
                type: STATUS_COMBO_DATA,
                value: "id",
                display: "text",
                name: "n[status]",
                fieldLabel: "Status",
                emptyText: "Set status..",
                tabIndex: 3,
                id: "status",
              })        
              ]
            }
          ],
        }
      ],
      listeners: {
        afterrender: function () {
          if (
            Ext.isEmpty(
              Ext.getCmp("sub_category_id").getValue()
            )
          ) {
            var recordSelected = Ext.getCmp("status")
              .getStore()
              .getAt(0);
            Ext.getCmp("status").setValue(
              recordSelected.get("id")
            );
          }
        },
      }
    });
    return panel;
  };
  var verifySubCategoryMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "verifySubCatMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Sub Category </th><td> {sub_cat} </td></tr>',
        '<tr><th width="40%">Category </th><td> {main_categoryName} </td></tr>',
        '<tr><th width="40%">Department </th><td> {parent_categoryname} </td></tr>',
        '<tr><th width="40%">Retail Category </th><td> {btName} </td></tr>',
        '<tr><th width="40%">Home Menu</th><td> {sbc_isHome} </td></tr>',
        '<tr><th width="40%">In Category List</th><td> {sbc_isInCategory} </td></tr>',
        '<tr><th width="40%">Composition Delator</th><td> {sbc_isCompositionDelator} </td></tr>',
        '<tr><th width="40%">Non GST Retailer</th><td> {sbc_isNonGstRetailer} </td></tr>',
        '<tr><th width="40%">Perishable</th><td> {sbc_isPerishable} </td></tr>',
        '<tr><th width="40%">Restaurant Service</th><td> {sbc_hasRestaurantService} </td></tr>',
        '<tr><th width="40%">Product Detail</th><td> {sbc_isProductDetail} </td></tr>',
        '<tr><th width="40%">Processing Time</th><td> {processingTime} </td></tr>',
        '<tr><th width="40%">Packing Mode</th><td> {packingModeName} </td></tr>',
        '<tr><th width="40%">Age Restriction</th><td> {sbc_hasAgeVerification} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        '<tpl if="sub_category_image != null">',
        "<tpl if=\"sub_category_image != ''\">",
        "<tr><td>",
        '<div border=0 ><img border=0 width="200" id="catDtlsViewPanel" height="200" src="{sub_category_image}"></img></div>',
        "</td></tr>",
        "</tpl>",
        "</tpl>",
        "</table>",
        "</div>"
      ),
    });
  };
  var gridSelectionChangedVerify = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("verifySubCatMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("verifySubCatMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.sub_category_id;
      Application.MyphaProductmasters.ViewModeSubCat(ID);
    }
  };
  var masterPanelforverifySubCat = function (id) {
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Verify Sub Category",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        verifySubCategoryGrid(),
        new Ext.Panel({
          title: "Sub Category Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "verifySubCategoryparentPanel",
          height: winsize.height * 0.6,
          items: [verifySubCategoryMasterForms(), verifySubCategoryMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "verifySubCatCancelBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("verifySubCatMasterGrid")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("verifySubCatMasterGrid")
                    .getSelectionModel()
                    .getSelections()[0].data.sub_category_id;
                  Application.MyphaProductmasters.ViewModeSubCat(ID);
                }
              },
            },
            /*<?php if (user_access("mypha_productmasters", "saveverifySubCategory")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "verifySubCatEditBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              hidden: true,
              handler: function () {
                var ID = Ext.getCmp("verifySubCatMasterGrid")
                  .getSelectionModel()
                  .getSelections()[0].data.sub_category_id;
                Application.MyphaProductmasters.EditViewSubCat(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "verifySubCatSaveBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveverifySubCatgeory();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return panel;
  };
  var verifySubCategoryGrid = function () {
    var _verifySubCatGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "business_type_name",
        },{
          type: "string",
          dataIndex: "parent_category",
        },
        {
          type: "string",
          dataIndex: "sub_cat",
        },
        {
          type: "string",
          dataIndex: "category_name",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isHome",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isInCategory",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "hasImage",
        },
      ],
    });
    _verifySubCatGridFilter.remote = true;
    _verifySubCatGridFilter.autoReload = true;
    var _masterStore = verifySubcatStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _masterStore,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      //iconCls: 'my-icon444',
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_verifySubCatGridFilter],
      id: "verifySubCatMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Sub Category",
          sortable: true,
          dataIndex: "sub_cat",
          tooltip: "Sub Category",
          hideable: true,
        },{
          header: "Category",
          sortable: true,
          dataIndex: "category_name",
          tooltip: "Main Category",
        },{
          header: "Department",
          sortable: true,
          dataIndex: "parent_category",
          tooltip: "Department",
          hideable: true,
        },{
          header: "Retail Category",
          sortable: true,
          dataIndex: "business_type_name",
          tooltip: "Retail Category",
          hideable: true,
        },{
          header: "Home Menu",
          sortable: true,
          hidden: true,
          dataIndex: "isHome",
          tooltip: "Home Menu",
        },
        {
          header: "In Category List",
          hidden: true,
          dataIndex: "isInCategory",
          tooltip: "In Category List",
        },
        {
          header: "Has Image",
          hidden: true,
          dataIndex: "hasImage",
          tooltip: "Has Image",
        }        
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedVerify,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("sub_category_id");
          if (!Ext.isEmpty(ID)) {
            Application.MyphaProductmasters.Cache.sub_category_id = ID;
            Ext.getCmp("verifySubCatMasterForm").hide();
            Application.MyphaProductmasters.ViewModeSubCat(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _masterStore.load();
        },
      },
      tbar: [
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _masterStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_verifySubCatGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  
  var verifySubcatStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listverifySubCategory",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "sub_category_id",
          root: "data",
        },
        [
          "sub_cat",
          "sub_category_id",
          "category_name",
          "isHome",
          "isInCategory",
          "hasImage","business_type_name","parent_category","category_name"
        ]
      ),
      sortInfo: {
        field: "sub_category_id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("verifySubCatMasterGrid").getSelectionModel().selectRow(0);
          }
        },
      },
    });
    return _store;
  };
  var saveverifySubCatgeory = function () {
    if (Ext.getCmp("verifySubCatMasterGrid").getStore().getCount() > 0) {
      var index = Ext.getCmp("verifySubCatMasterGrid")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }

    var lastOptions = Ext.getCmp("verifySubCatMasterGrid").getStore().lastOptions;
    var verifySubCatid = Ext.getCmp("sub_category_id").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("sub_cat").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("status").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("main_category").getValue() &&
      (Ext.getCmp("sbc_processingTime").getValue() > 0))
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveSubcategory",
        method: "POST",
        params: {
          isVerified:1,
          id: Ext.getCmp("sub_category_id").getValue(),
          name: Ext.getCmp("sub_cat").getValue(),
          status: Ext.getCmp("status").getValue(),
          main_category: Ext.getCmp("main_category").getValue(),
          sbc_isHome: Ext.getCmp("sbc_isHome").getValue(),
          sbc_isInCategory: Ext.getCmp("sbc_isInCategory").getValue(),
          sbc_isPerishable: Ext.getCmp("sbc_isPerishable").getValue(),
          sbc_isCompositionDelator: Ext.getCmp("sbc_isCompositionDelator").getValue(),
          sbc_isNonGstRetailer: Ext.getCmp("sbc_isNonGstRetailer").getValue(),
          sbc_hasRestaurantService:Ext.getCmp("sbc_hasRestaurantService").getValue(),
          sbc_isProductDetail: Ext.getCmp("sbc_isProductDetail").getValue(),
          sbc_processingTime: Ext.getCmp("sbc_processingTime").getValue(),
          packingMode: Ext.getCmp('radiobuttonPackingMode').getValue(),
          sbc_hasAgeVerification: Ext.getCmp("sbc_hasAgeVerification").getValue()
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.MyphaProductmasters.verifySubCatAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp("verifySubCatMasterGrid"));
              Ext.getCmp("verifySubCatMasterGrid").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("verifySubCatMasterGrid").getStore().reload(lastOptions);
              var gridPanel = Ext.getCmp("verifySubCatMasterGrid");
              gridPanel.getSelectionModel().selectRow(index);
              Application.MyphaProductmasters.ViewModeSubCat(
                tmp.data.sub_category_id
              );
            }
            Application.MyphaProductmasters.verifySubCatAddEdit = "";
            Application.MyphaProductmasters.ViewModeSubCat(tmp.data.sub_category_id);
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
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  
  //-----------------------------Public Area-------------------------------//

  return {
    Cache: {},
    initCategory: function () {
      loadCount = 0;
      var _catePanelId = "panelidforCategory";
      var _masterPanelCat = Ext.getCmp(_catePanelId);
      if (Ext.isEmpty(_masterPanelCat)) {
        _masterPanelCat = masterPanelforCat(_catePanelId);
        Application.UI.addTab(_masterPanelCat);
        _masterPanelCat.doLayout();
      } else {
        Application.UI.addTab(_masterPanelCat);
      }
    },
    catEditView: function () {
      Application.MyphaProductmasters.catAddEdit = "Edit";
      Ext.getCmp("CategoryparentPanel").doLayout();
      Ext.getCmp("CategoryparentPanel").setTitle("Edit Category Details");
      Ext.getCmp("catMasterForm").show();
      Ext.getCmp("CatMasterDetailsViewPanel").hide();
      /*<?php if (user_access("mypha_productmasters", "saveCategory")) { ?> */
      Ext.getCmp("CatEditBtn").hide();
      Ext.getCmp("CatSaveBtn").show();
      /*<?php } ?> */
      Ext.getCmp("CatCancelBtn").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("catMasterForm").getForm();
        masterForm.load({
          params: {
            category_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=cate_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            //pdtLoadedForm = [form, action.response.responseText];
            var tmp = Ext.decode(action.response.responseText);
            Ext.getCmp("parent_category").getStore().baseParams.primaryBt =
              tmp.data.retailcategory;
            Ext.getCmp("parent_category").getStore().load();
            Ext.getCmp("parent_category").setRawValue(
              tmp.data.parent_categoryname
            );
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    catViewMode: function () {
      var category_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveCategory")) { ?> */
      Ext.getCmp("CatEditBtn").show();
      Ext.getCmp("CatSaveBtn").hide();
      /*<?php } ?> */
      Ext.getCmp("CatCancelBtn").hide();
      Ext.getCmp("catMasterForm").hide();
      Ext.getCmp("CatMasterDetailsViewPanel").show();
      Ext.getCmp("CategoryparentPanel").setTitle("View Category Details");
      Ext.getCmp("CategoryparentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=CatdetailsView",
        method: "POST",
        params: { category_id: category_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("CatMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("CategoryparentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("CategoryparentPanel").doLayout();
      Ext.getCmp("parent_category").getStore().load();
    },
    initParentCategory: function () {
      loadCount = 0;
      var _parentCategoryPanelId = "panelParentCategoryinMaster";
      var _parentCategoryPanel = Ext.getCmp(_parentCategoryPanelId);
      if (Ext.isEmpty(_parentCategoryPanel)) {
        _parentCategoryPanel = parentCategoryPanel(_parentCategoryPanelId);
        Application.UI.addTab(_parentCategoryPanel);
        _parentCategoryPanel.doLayout();
      } else {
        Application.UI.addTab(_parentCategoryPanel);
      }
    },
    EditParentCategoryView: function () {
      Application.MyphaProductmasters.ParentCategoryAddEdit = "Edit";
      Ext.getCmp("panelMasterParentCategoryParent").doLayout();
      Ext.getCmp("panelMasterParentCategoryParent").setTitle(
        "Edit Department Details"
      );
      Ext.getCmp("formpanelMasterParentCategory").show();
      Ext.getCmp("xtemplateMasterParentCategoryViewDetails").hide();
      /*<?php if (user_access("mypha_productmasters", "saveParentCategory")) { ?> */
      Ext.getCmp("buttonMasterParentCategoryEdit").hide();
      Ext.getCmp("buttonMasterParentCategorySave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterParentCategoryCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("formpanelMasterParentCategory").getForm();
        masterForm.load({
          params: {
            parent_category_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=parent_category_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewParentCategoryMode: function () {
      var parent_category_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveParentCategory")) { ?> */
      Ext.getCmp("buttonMasterParentCategoryEdit").show();
      Ext.getCmp("buttonMasterParentCategorySave").hide();
      /*<?php } ?> */
      Ext.getCmp("panelMasterParentCategoryParent").setTitle(
        "View Department Details"
      );
      Ext.getCmp("buttonMasterParentCategoryCancel").hide();
      Ext.getCmp("formpanelMasterParentCategory").hide();
      Ext.getCmp("xtemplateMasterParentCategoryViewDetails").show();
      Ext.getCmp("panelMasterParentCategoryParent").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=ParentCategorydetailsView",
        method: "POST",
        params: { parent_category_id: parent_category_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "xtemplateMasterParentCategoryViewDetails"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterParentCategoryParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterParentCategoryParent").doLayout();
    },
    initSubCategory: function () {
      loadCount = 0;
      var _panelId = "panelidforSubCategory";
      var _masterPanel = Ext.getCmp(_panelId);
      if (Ext.isEmpty(_masterPanel)) {
        _masterPanel = masterPanelforSubCat(_panelId);
        Application.UI.addTab(_masterPanel);
        _masterPanel.doLayout();
      } else {
        Application.UI.addTab(_masterPanel);
      }
    },
    EditView: function () {
      Application.MyphaProductmasters.subcatAddEdit = "Edit";
      Ext.getCmp("subCategoryparentPanel").doLayout();
      Ext.getCmp("subcatMasterForm").setTitle(
        "Edit Margin Distribution details"
      );
      Ext.getCmp("subcatMasterForm").show();
      Ext.getCmp("subCatMasterDetailsViewPanel").hide();
      /*<?php if (user_access("mypha_productmasters", "saveSubcategory")) { ?> */
      Ext.getCmp("subCatEditBtn").hide();
      Ext.getCmp("subCatSaveBtn").show();
      /*<?php } ?> */
      Ext.getCmp("subCatCancelBtn").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("subcatMasterForm").getForm();
        masterForm.load({
          params: {
            sub_category_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=subcate_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            Ext.getCmp("parent_categorysc").getStore().baseParams.primaryBt =
              tmp.data.primary_businessTypesc;
            Ext.getCmp("parent_categorysc").getStore().load();
            Ext.getCmp("parent_categorysc").setRawValue(
              tmp.data.parent_categoryname
            );

            Ext.getCmp("main_category").getStore().baseParams.department =
              tmp.data.parent_categorysc;
            Ext.getCmp("main_category").getStore().load();
            Ext.getCmp("main_category").setRawValue(tmp.data.main_categoryName);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewMode: function () {
      var sub_category_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveSubcategory")) { ?> */
      Ext.getCmp("subCatEditBtn").show();
      Ext.getCmp("subCatSaveBtn").hide();
      /*<?php } ?> */
      Ext.getCmp("subCatCancelBtn").hide();
      Ext.getCmp("subcatMasterForm").hide();
      Ext.getCmp("subCatMasterDetailsViewPanel").show();
      Ext.getCmp("subCategoryparentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=subCatdetailsView",
        method: "POST",
        params: { sub_category_id: sub_category_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("subCatMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("subCategoryparentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("subCategoryparentPanel").doLayout();
      Ext.getCmp("main_category").getStore().load();
    },
    initBrand: function () {
      var _brandPanelId = "panelBrandinMaster";
      var _brandPanel = Ext.getCmp(_brandPanelId);
      loadCount = 0;
      if (Ext.isEmpty(_brandPanel)) {
        _brandPanel = brandPanelforMaster(_brandPanelId);
        Application.UI.addTab(_brandPanel);
        _brandPanel.doLayout();
      } else {
        Application.UI.addTab(_brandPanel);
      }
    },
    EditBrandView: function () {
      Application.MyphaProductmasters.BrandAddEdit = "Edit";
      Ext.getCmp("panelMasterBrandsParent").doLayout();
      Ext.getCmp("panelMasterBrandsParent").setTitle("Edit Brand Details");
      Ext.getCmp("formpanelMasterBrandSave").show();
      Ext.getCmp("xtemplateMasterBrandViewDetails").hide();
      /*<?php if (user_access("mypha_productmasters", "saveBrands")) { ?> */
      Ext.getCmp("buttonMasterBrandsEdit").hide();
      Ext.getCmp("buttonMasterBrandsSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterBrandsCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("formpanelMasterBrandSave").getForm();
        masterForm.load({
          params: {
            brand_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=brands_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewBrandMode: function () {
      var brand_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveHSN")) { ?> */
      Ext.getCmp("buttonMasterBrandsEdit").show();
      Ext.getCmp("buttonMasterBrandsSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterBrandsCancel").hide();
      Ext.getCmp("formpanelMasterBrandSave").hide();
      Ext.getCmp("xtemplateMasterBrandViewDetails").show();
      Ext.getCmp("panelMasterBrandsParent").doLayout();
      Ext.getCmp("panelMasterBrandsParent").setTitle("View Brand Details");
      Ext.Ajax.request({
        url: modURL + "&op=BranddetailsView",
        method: "POST",
        params: { brand_id: brand_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "xtemplateMasterBrandViewDetails"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterBrandsParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterBrandsParent").doLayout();
    },
    initPackageType: function () {
      var _packagetypePanelId = "panelMasterMainPackageType";
      var _masterPanelPackageType = Ext.getCmp(_packagetypePanelId);
      if (Ext.isEmpty(_masterPanelPackageType)) {
        _masterPanelPackageType =
          masterPanelforPackageType(_packagetypePanelId);
        Application.UI.addTab(_masterPanelPackageType);
        _masterPanelPackageType.doLayout();
      } else {
        Application.UI.addTab(_masterPanelPackageType);
      }
    },
    EditPackageTypesView: function () {
      Application.MyphaProductmasters.PackageTypesAddEdit = "Edit";
      Ext.getCmp("panelMasterPackageTypeParent").doLayout();
      Ext.getCmp("panelMasterPackageTypeParent").setTitle(
        "Edit Package Types Details"
      );
      Ext.getCmp("formpanelMasterPackageTypes").show();
      Ext.getCmp("panelMasterPackageTypesDetailsView").hide();
      /*<?php if (user_access("mypha_productmasters", "savePackageTypes")) { ?> */
      Ext.getCmp("buttonMasterPackageTypeEdit").hide();
      Ext.getCmp("buttonMasterPackageTypeSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterPackageTypeCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var packagetypesForm = Ext.getCmp(
          "formpanelMasterPackageTypes"
        ).getForm();
        packagetypesForm.load({
          params: {
            package_type_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=packagetypes_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewPackageTypes: function () {
      var package_type_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "savePackageTypes")) { ?> */
      Ext.getCmp("buttonMasterPackageTypeEdit").show();
      Ext.getCmp("buttonMasterPackageTypeSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterPackageTypeCancel").hide();
      Ext.getCmp("formpanelMasterPackageTypes").hide();
      Ext.getCmp("panelMasterPackageTypesDetailsView").show();
      Ext.getCmp("panelMasterPackageTypeParent").doLayout();
      Ext.getCmp("panelMasterPackageTypeParent").setTitle(
        "View Package Type Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=packagetypesdetailsView",
        method: "POST",
        params: { package_type_id: package_type_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelMasterPackageTypesDetailsView"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterPackageTypeParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterPackageTypeParent").doLayout();
    },
    initTags: function () {
      var _tagsPanelId = "panelMasterMainTags";
      var _masterPanelTags = Ext.getCmp(_tagsPanelId);
      if (Ext.isEmpty(_masterPanelTags)) {
        _masterPanelTags = masterPanelforTags(_tagsPanelId);
        Application.UI.addTab(_masterPanelTags);
        _masterPanelTags.doLayout();
      } else {
        Application.UI.addTab(_masterPanelTags);
      }
    },
    ViewTags: function () {
      var tag_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveTags")) { ?> */
      Ext.getCmp("buttonMasterTagsEdit").show();
      Ext.getCmp("buttonMasterTagsSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterTagsCancel").hide();
      Ext.getCmp("formpanelMasterTags").hide();
      Ext.getCmp("panelMasterTagsDetailsView").show();
      Ext.getCmp("panelMasterTagsParent").doLayout();
      Ext.getCmp("panelMasterTagsParent").setTitle("View Tag Details");
      Ext.Ajax.request({
        url: modURL + "&op=tagsdetailsView",
        method: "POST",
        params: { tag_id: tag_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("panelMasterTagsDetailsView");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterTagsParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterTagsParent").doLayout();
    },
    EditTags: function () {
      Application.MyphaProductmasters.TagsAddEdit = "Edit";
      Ext.getCmp("panelMasterTagsParent").doLayout();
      Ext.getCmp("panelMasterTagsParent").setTitle("Edit Tag Details");
      Ext.getCmp("formpanelMasterTags").show();
      Ext.getCmp("panelMasterTagsDetailsView").hide();
      /*<?php if (user_access("mypha_productmasters", "saveTags")) { ?> */
      Ext.getCmp("buttonMasterTagsEdit").hide();
      Ext.getCmp("buttonMasterTagsSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterTagsCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var tagsForm = Ext.getCmp("formpanelMasterTags").getForm();
        tagsForm.load({
          params: {
            tag_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=tags_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    initRoa: function () {
      var _packagetypePanelId = "panelMasterMainRoa";
      var _masterPanelRoa = Ext.getCmp(_packagetypePanelId);
      if (Ext.isEmpty(_masterPanelRoa)) {
        _masterPanelRoa = masterPanelforRoa(_packagetypePanelId);
        Application.UI.addTab(_masterPanelRoa);
        _masterPanelRoa.doLayout();
      } else {
        Application.UI.addTab(_masterPanelRoa);
      }
    },
    EditRoasView: function () {
      Application.MyphaProductmasters.RoasAddEdit = "Edit";
      Ext.getCmp("panelMasterRoaParent").doLayout();
      Ext.getCmp("panelMasterRoaParent").setTitle("Edit Roas details");
      Ext.getCmp("formpanelMasterRoas").show();
      Ext.getCmp("panelMasterRoasDetailsView").hide();
      /*<?php if (user_access("mypha_productmasters", "saveRoas")) { ?> */
      Ext.getCmp("buttonMasterRoaEdit").hide();
      Ext.getCmp("buttonMasterRoaSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterRoaCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var roasForm = Ext.getCmp("formpanelMasterRoas").getForm();
        roasForm.load({
          params: {
            roa_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=roas_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewRoas: function () {
      var roa_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveRoas")) { ?> */
      Ext.getCmp("buttonMasterRoaEdit").show();
      Ext.getCmp("buttonMasterRoaSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterRoaCancel").hide();
      Ext.getCmp("formpanelMasterRoas").hide();
      Ext.getCmp("panelMasterRoasDetailsView").show();
      Ext.getCmp("panelMasterRoaParent").doLayout();
      Ext.getCmp("panelMasterRoaParent").setTitle("Roa Details");
      Ext.Ajax.request({
        url: modURL + "&op=packagetypesdetailsView",
        method: "POST",
        params: { roa_id: roa_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("panelMasterRoasDetailsView");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterRoaParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterRoaParent").doLayout();
    },
    initHSN: function () {
      var _HSNPanelId = "panelMasterMainHSN";
      var _masterPanelHSN = Ext.getCmp(_HSNPanelId);
      if (Ext.isEmpty(_masterPanelHSN)) {
        _masterPanelHSN = masterPanelforHSN(_HSNPanelId);
        Application.UI.addTab(_masterPanelHSN);
        _masterPanelHSN.doLayout();
      } else {
        Application.UI.addTab(_masterPanelHSN);
      }
    },
    EditHSNView: function () {
      Application.MyphaProductmasters.HSNAddEdit = "Edit";
      Ext.getCmp("panelMasterHSNParent").doLayout();
      Ext.getCmp("panelMasterHSNParent").setTitle("Edit HSN Details");
      Ext.getCmp("formpanelMasterHSN").show();
      Ext.getCmp("panelMasterHSNDetailsView").hide();
      /*<?php if (user_access("mypha_productmasters", "saveHSN")) { ?> */
      Ext.getCmp("buttonMasterHSNEdit").hide();
      Ext.getCmp("buttonMasterHSNSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterHSNCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var hnsForm = Ext.getCmp("formpanelMasterHSN").getForm();
        hnsForm.load({
          params: {
            hsn_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=hns_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            //Ext.getCmp('hsn_code').setReadOnly(true);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewHSN: function () {
      var hsn_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveHSN")) { ?> */
      Ext.getCmp("buttonMasterHSNEdit").show();
      Ext.getCmp("buttonMasterHSNSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterHSNCancel").hide();
      Ext.getCmp("formpanelMasterHSN").hide();
      Ext.getCmp("panelMasterHSNDetailsView").show();
      Ext.getCmp("panelMasterHSNParent").doLayout();
      Ext.getCmp("panelMasterHSNParent").setTitle("View HSN Details");
      Ext.Ajax.request({
        url: modURL + "&op=hsndetailsView",
        method: "POST",
        params: { hsn_id: hsn_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("panelMasterHSNDetailsView");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterHSNParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterHSNParent").doLayout();
    },
    initItemName: function () {
      var _ItemNamePanelId = "panelMasterMainItemName";
      var _masterPanelItemName = Ext.getCmp(_ItemNamePanelId);
      if (Ext.isEmpty(_masterPanelItemName)) {
        _masterPanelItemName = masterPanelforItemName(_ItemNamePanelId);
        Application.UI.addTab(_masterPanelItemName);
        _masterPanelItemName.doLayout();
      } else {
        Application.UI.addTab(_masterPanelItemName);
      }
    },
    EditItemNameView: function () {
      Application.MyphaProductmasters.ItemNameAddEdit = "Edit";
      Ext.getCmp("panelMasterItemNameParent").doLayout();
      Ext.getCmp("panelMasterItemNameParent").setTitle(
        "Edit Product Master Details"
      );
      Ext.getCmp("formpanelMasterItemName").show();
      Ext.getCmp("panelMasterItemNameDetailsView").hide();
      /*<?php if (user_access("mypha_productmasters", "saveItemName")) { ?> */
      Ext.getCmp("buttonMasterItemNameEdit").hide();
      Ext.getCmp("buttonMasterItemNameSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterItemNameCancel").show();
      /*<?php if (user_access("mypha_productmasters", "verifyProdctMaster")) { ?> */
      Ext.getCmp("buttonMasterItemNameVerify").show();
      /*<?php } ?> */
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var itemnameForm = Ext.getCmp("formpanelMasterItemName").getForm();
        itemnameForm.load({
          params: {
            itemname_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=itemname_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewItemName: function () {
      var itemname_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveItemName")) { ?> */
      Ext.getCmp("buttonMasterItemNameEdit").show();
      Ext.getCmp("buttonMasterItemNameSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterItemNameCancel").hide();
      /*<?php if (user_access("mypha_productmasters", "verifyProdctMaster")) { ?> */
      Ext.getCmp("buttonMasterItemNameVerify").hide();
      /*<?php } ?> */
      Ext.getCmp("formpanelMasterItemName").hide();
      Ext.getCmp("panelMasterItemNameDetailsView").show();
      Ext.getCmp("panelMasterItemNameParent").doLayout();
      Ext.getCmp("panelMasterItemNameParent").setTitle(
        "View Product Master Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=itemnamedetailsView",
        method: "POST",
        params: { itemname_id: itemname_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("panelMasterItemNameDetailsView");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterItemNameParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterItemNameParent").doLayout();
    },
    uploadimageCategory: function (rid, uploadtype, img_url) {
      if (uploadtype === "category") {
        var main_img_panel = categoryuploadForm(img_url);
      } else if (uploadtype === "parentcategory") {
        var main_img_panel = parentcategoryuploadForm(img_url);
      } else if (uploadtype === "subcategory") {
        var main_img_panel = subcategoryuploadForm(img_url);
      } else if (uploadtype === "brand") {
        var main_img_panel = branduploadForm(img_url);
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
            if (uploadtype === "category") {
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
            } else if (uploadtype === "parentcategory") {
              Ext.getCmp("parent_category_image_upload")
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
            } else if (uploadtype === "subcategory") {
              Ext.getCmp("sub_category_image_upload")
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
            } else if (uploadtype === "brand") {
              Ext.getCmp("brand_image_upload")
                .getForm()
                .load({
                  waitTitle: "Please Wait",
                  waitMsg: "Loading...",
                  url: modURL + "&op=get_brandimg_s3_details",
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
              if (uploadtype === "category") {
                Application.MyphaProductmasters.Cache.rid = rid;
                var bucket_name = Ext.getCmp("albumBucketName").getValue();
                var file_name = Ext.getCmp("file_name").getValue();
                var file_path = Ext.getCmp("aws_file_location").getValue();
                var form_data = {
                  category_id: rid,
                  uploaded_file_name: file_name,
                  bucket: bucket_name,
                  filepath: Ext.getCmp("aws_file_location").getValue(),
                  bucket_path: Ext.getCmp("oncompleteurl").getValue(),
                };
                var params = {
                  action: "Add Categoty Image",
                  module: "mypha_productmasters",
                  op: "saveMainCategotyImage",
                  extrainfo: "Add Categoty Image",
                  id: rid,
                };
                if (file_path != "") {
                  APICall(
                    params,
                    Application.MyphaProductmasters.saveMainCategoryImage,
                    form_data
                  );
                } else {
                  Ext.Msg.alert(
                    "Notification.",
                    "Please select a valid Image file"
                  );
                }
              } else if (uploadtype === "parentcategory") {
                Application.MyphaProductmasters.Cache.rid = rid;
                var bucket_name = Ext.getCmp("pc_albumBucketName").getValue();
                var file_name = Ext.getCmp("pc_file_name").getValue();
                var file_path = Ext.getCmp("pc_aws_file_location").getValue();
                var form_data = {
                  parentcategory_id: rid,
                  uploaded_file_name: file_name,
                  bucket: bucket_name,
                  filepath: Ext.getCmp("pc_aws_file_location").getValue(),
                  bucket_path: Ext.getCmp("pc_oncompleteurl").getValue(),
                };
                var params = {
                  action: "Add Parent Categoty Image",
                  module: "mypha_productmasters",
                  op: "saveParentCategoryImage",
                  extrainfo: "Add Parent Categoty Image",
                  id: rid,
                };
                if (file_path != "") {
                  APICall(
                    params,
                    Application.MyphaProductmasters.saveParentCategoryImage,
                    form_data
                  );
                } else {
                  Ext.Msg.alert(
                    "Notification.",
                    "Please select a valid Image file"
                  );
                }
              } else if (uploadtype === "subcategory") {
                Application.MyphaProductmasters.Cache.rid = rid;
                var bucket_name = Ext.getCmp("sc_albumBucketName").getValue();
                var file_name = Ext.getCmp("sc_file_name").getValue();
                var file_path = Ext.getCmp("sc_aws_file_location").getValue();
                var form_data = {
                  subcategory_id: rid,
                  uploaded_file_name: file_name,
                  bucket: bucket_name,
                  filepath: Ext.getCmp("sc_aws_file_location").getValue(),
                  bucket_path: Ext.getCmp("sc_oncompleteurl").getValue(),
                };
                var params = {
                  action: "Add Sub Categoty Image",
                  module: "mypha_productmasters",
                  op: "saveSubCategoryImage",
                  extrainfo: "Add Sub Categoty Image",
                  id: rid,
                };
                if (file_path != "") {
                  APICall(
                    params,
                    Application.MyphaProductmasters.saveSubCategoryImage,
                    form_data
                  );
                } else {
                  Ext.Msg.alert(
                    "Notification.",
                    "Please select a valid Image file"
                  );
                }
              } else if (uploadtype === "brand") {
                Application.MyphaProductmasters.Cache.rid = rid;
                var bucket_name = Ext.getCmp("br_albumBucketName").getValue();
                var file_name = Ext.getCmp("br_file_name").getValue();
                var file_path = Ext.getCmp("br_aws_file_location").getValue();
                var form_data = {
                  subcategory_id: rid,
                  uploaded_file_name: file_name,
                  bucket: bucket_name,
                  filepath: Ext.getCmp("br_aws_file_location").getValue(),
                  bucket_path: Ext.getCmp("br_oncompleteurl").getValue(),
                };
                var params = {
                  action: "Add Brand Image",
                  module: "mypha_productmasters",
                  op: "saveBrandImage",
                  extrainfo: "Add Brnd Image",
                  id: rid,
                };
                if (file_path != "") {
                  APICall(
                    params,
                    Application.MyphaProductmasters.saveBrandImage,
                    form_data
                  );
                } else {
                  Ext.Msg.alert(
                    "Notification.",
                    "Please select a valid Image file"
                  );
                }
              }
            },
          },
        ],
      });
      catuploadwindow.doLayout();
      catuploadwindow.show(this);
      catuploadwindow.center();
    },
    saveMainCategoryImage: function () {
      var bucket_name = Ext.getCmp("albumBucketName").getValue();
      var file_name = Ext.getCmp("file_name").getValue();
      if (bucket_name != "" && file_name != "") {
        Ext.Ajax.request({
          url: modURL + "&op=saveMainCategoryImage",
          method: "POST",
          params: {
            category_id: Application.MyphaProductmasters.Cache.rid,
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
    },
    saveParentCategoryImage: function () {
      var bucket_name = Ext.getCmp("pc_albumBucketName").getValue();
      var file_name = Ext.getCmp("pc_file_name").getValue();
      if (bucket_name != "" && file_name != "") {
        Ext.Ajax.request({
          url: modURL + "&op=saveParentCategoryImage",
          method: "POST",
          params: {
            parent_category_id: Application.MyphaProductmasters.Cache.rid,
            uploaded_file_name: file_name,
            bucket: bucket_name,
            filepath: Ext.getCmp("pc_aws_file_location").getValue(),
            bucket_path: Ext.getCmp("pc_oncompleteurl").getValue(),
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
    },
    saveSubCategoryImage: function () {
      var bucket_name = Ext.getCmp("sc_albumBucketName").getValue();
      var file_name = Ext.getCmp("sc_file_name").getValue();
      if (bucket_name != "" && file_name != "") {
        Ext.Ajax.request({
          url: modURL + "&op=saveSubCategoryImage",
          method: "POST",
          params: {
            subcategory_id: Application.MyphaProductmasters.Cache.rid,
            uploaded_file_name: file_name,
            bucket: bucket_name,
            filepath: Ext.getCmp("sc_aws_file_location").getValue(),
            bucket_path: Ext.getCmp("sc_oncompleteurl").getValue(),
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
    },
    saveBrandImage: function () {
      var bucket_name = Ext.getCmp("br_albumBucketName").getValue();
      var file_name = Ext.getCmp("br_file_name").getValue();
      if (bucket_name != "" && file_name != "") {
        Ext.Ajax.request({
          url: modURL + "&op=saveBrandImage",
          method: "POST",
          params: {
            brand_id: Application.MyphaProductmasters.Cache.rid,
            uploaded_file_name: file_name,
            bucket: bucket_name,
            filepath: Ext.getCmp("br_aws_file_location").getValue(),
            bucket_path: Ext.getCmp("br_oncompleteurl").getValue(),
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
    },
    uploadimageItem: function (rid, uploadtype, iteamGroupImage) {
      if (uploadtype === "productMaster") {
        var main_itemimg_panel = productMasteruploadForm(iteamGroupImage);
      }
      var windowimg_id = "productMasteruploadwindow";
      var productMasteruploadwindow = new Ext.Window({
        id: windowimg_id,
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
        items: main_itemimg_panel,
        listeners: {
          afterrender: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            winLoadMask = new Ext.LoadMask(
              Ext.getCmp("productMasteruploadwindow").getEl()
            );
            winLoadMask.msg = "Please wait...";
            if (uploadtype === "productMaster") {
              Ext.getCmp("product_master_image_upload")
                .getForm()
                .load({
                  waitTitle: "Please Wait",
                  waitMsg: "Loading...",
                  url: modURL + "&op=get_prtmaster_s3_details",
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
              productMasteruploadwindow.close();
            },
          },
          {
            text: "Upload",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
            iconCls: "finascop_my-icon1",
            id: "savePrtButton",
            handler: function () {
              if (uploadtype === "productMaster") {
                Application.MyphaProductmasters.Cache.rid = rid;
                var prtbucket_name =
                  Ext.getCmp("pm_albumBucketName").getValue();
                var file_name = Ext.getCmp("pm_aws_file_bucket").getValue();
                var file_path = Ext.getCmp("pm_aws_file_location").getValue();
                var form_data = {
                  itemname_id: rid,
                  uploadedprt_file_name: file_name,
                  bucket: prtbucket_name,
                  filepath: Ext.getCmp("pm_aws_file_location").getValue(),
                  bucket_path: Ext.getCmp("pm_oncompleteurl").getValue(),
                };
                var params = {
                  action: "Add Product Master Image",
                  module: "mypha_productmasters",
                  op: "savePrdtMasterImage",
                  extrainfo: "Add Product Master Image",
                  id: rid,
                };
                if (file_path != "") {
                  APICall(
                    params,
                    Application.MyphaProductmasters.savePrdtMasterImage,
                    form_data
                  );
                } else {
                  Ext.Msg.alert(
                    "Notification.",
                    "Please select a valid Image file"
                  );
                }
              }
            },
          },
        ],
      });
      productMasteruploadwindow.doLayout();
      productMasteruploadwindow.show(this);
      productMasteruploadwindow.center();
    },
    savePrdtMasterImage: function () {
      var bucket_name = Ext.getCmp("pm_albumBucketName").getValue();
      var file_name = Ext.getCmp("pm_aws_file_bucket").getValue();
      if (bucket_name != "" && file_name != "") {
        Ext.Ajax.request({
          url: modURL + "&op=savePrdtMasterImage",
          method: "POST",
          params: {
            itemname_id: Application.MyphaProductmasters.Cache.rid,
            uploadedprt_file_name: file_name,
            bucket: bucket_name,
            filepath: Ext.getCmp("pm_aws_file_location").getValue(),
            bucket_path: Ext.getCmp("pm_oncompleteurl").getValue(),
          },
          success: function (resp) {
            var res = Ext.decode(resp.responseText);
            if (res.success === true) {
              Application.example.msg("Notification", "Image saved..");
              Ext.getCmp("productMasteruploadwindow").close();
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
    },
    initManufacture: function () {
      var panelId = "masterProductManufacture";
      var manufacture_panel = Ext.getCmp(panelId);
      if (Ext.isEmpty(manufacture_panel)) {
        manufacture_panel = manufacturePanel(panelId);
        Application.UI.addTab(manufacture_panel);
        manufacture_panel.doLayout();
      } else {
        Application.UI.addTab(manufacture_panel);
      }
    },
    EditManufactureView: function () {
      Application.MyphaProductmasters.ManufactureAddEdit = "Edit";
      Ext.getCmp("panelProductMasterManufacture").doLayout();
      Ext.getCmp("panelProductMasterManufacture").setTitle(
        "Edit Manufacturer/Supplier Details"
      );
      Ext.getCmp("formpanelProductMasterManufacture").show();
      Ext.getCmp("xtemplateProductMasterManufactureViewDetails").hide();
      /* <?php if (user_access("mypha_productmasters", "saveProductManufacture")) { ?> */
      Ext.getCmp("buttonProductMasterManufactureEdit").hide();
      Ext.getCmp("buttonProductMasterManufactureSave").show();
      /*<?php  } ?>*/

      Ext.getCmp("buttonProductMasterManufactureCancel").show();
      if (!Ext.isEmpty(arguments[0])) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var masterForm = Ext.getCmp(
          "formpanelProductMasterManufacture"
        ).getForm();
        masterForm.load({
          params: {
            manufacture_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=manufactureprod_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewManufactureMode: function () {
      var manufacture_id = arguments[0];
      /* <?php if (user_access("mypha_productmasters", "saveProductManufacture")) { ?> */
      Ext.getCmp("buttonProductMasterManufactureEdit").show();
      Ext.getCmp("buttonProductMasterManufactureSave").hide();
      /*<?php  } ?>*/
      Ext.getCmp("buttonProductMasterManufactureCancel").hide();
      Ext.getCmp("formpanelProductMasterManufacture").hide();
      Ext.getCmp("xtemplateProductMasterManufactureViewDetails").show();
      Ext.getCmp("panelProductMasterManufacture").doLayout();
      Ext.getCmp("panelProductMasterManufacture").setTitle(
        "View Manufacturer/Supplier Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=ManufactureProductdetailsView",
        method: "POST",
        params: { manufacture_id: manufacture_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "xtemplateProductMasterManufactureViewDetails"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelProductMasterManufacture").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelProductMasterManufacture").doLayout();
    },
    saveProductManufacture: function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var store_form = Ext.getCmp(
        "formpanelProductMasterManufacture"
      ).getForm();
      if (store_form.isValid()) {
        store_form.submit({
          url: modURL + "&op=saveProductManufacture",
          waitMsg: "Saving Details....",
          waitTitle: "Please Wait...",
          params: {
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          success: function (response, action) {
            var tmp = Ext.decode(action.response.responseText);
            if (tmp.success === true && tmp.valid === true) {
              Application.example.msg("Success", tmp.message);
              if (Application.MyphaProductmasters.ManufactureAddEdit == "Add") {
                recs_per_page = updateRecsPerPage(
                  Ext.getCmp("gridpanelProductMasterListingManufacture")
                );
                Ext.getCmp("formpanelProductMasterManufacture")
                  .getForm()
                  .reset();
                Ext.getCmp(
                  "gridpanelProductMasterListingManufacture"
                ).store.reload({
                  params: {
                    start: 0,
                    limit: recs_per_page,
                  },
                });
              } else {
                Ext.getCmp(
                  "gridpanelProductMasterListingManufacture"
                ).selModel.getSelected().data.manufacture_id =
                  tmp.data.manufacture_id;
                Ext.getCmp("gridpanelProductMasterListingManufacture")
                  .getStore()
                  .reload();
                Ext.getCmp("gridpanelProductMasterListingManufacture")
                  .getView()
                  .refresh();
              }
              Application.MyphaProductmasters.ManufactureAddEdit = "";
              Application.MyphaProductmasters.ViewManufactureMode(
                tmp.data.manufacture_id
              );
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
    initBusinessType: function () {
      var _businesstypePanelId = "panelMasterMainBusinessType";
      var _masterPanelBusinessType = Ext.getCmp(_businesstypePanelId);
      if (Ext.isEmpty(_masterPanelBusinessType)) {
        _masterPanelBusinessType =
          masterPanelforBusinessType(_businesstypePanelId);
        Application.UI.addTab(_masterPanelBusinessType);
        _masterPanelBusinessType.doLayout();
      } else {
        Application.UI.addTab(_masterPanelBusinessType);
      }
    },
    ViewBusinessTypes: function () {
      var business_type_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveBusinessTypes")) { ?> */
      Ext.getCmp("buttonMasterBusinessTypeEdit").show();
      Ext.getCmp("buttonMasterBusinessTypeSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterBusinessTypeCancel").hide();
      Ext.getCmp("formpanelMasterBusinessTypes").hide();
      Ext.getCmp("panelMasterBusinessTypesDetailsView").show();
      Ext.getCmp("panelMasterBusinessTypeParent").doLayout();
      Ext.getCmp("panelMasterBusinessTypeParent").setTitle(
        "View Retail Category Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=businesstypesdetailsView",
        method: "POST",
        params: { business_type_id: business_type_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelMasterBusinessTypesDetailsView"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterBusinessTypeParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterBusinessTypeParent").doLayout();
    },
    EditBusinessTypesView: function () {
      Application.MyphaProductmasters.BusinessTypesAddEdit = "Edit";
      Ext.getCmp("panelMasterBusinessTypeParent").doLayout();
      Ext.getCmp("panelMasterBusinessTypeParent").setTitle(
        "Edit Retail Category Details"
      );
      Ext.getCmp("formpanelMasterBusinessTypes").show();
      Ext.getCmp("panelMasterBusinessTypesDetailsView").hide();
      /*<?php if (user_access("mypha_productmasters", "saveBusinessTypes")) { ?> */
      Ext.getCmp("buttonMasterBusinessTypeEdit").hide();
      Ext.getCmp("buttonMasterBusinessTypeSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterBusinessTypeCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var businesstypesForm = Ext.getCmp(
          "formpanelMasterBusinessTypes"
        ).getForm();
        businesstypesForm.load({
          params: {
            business_type_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=businesstypes_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },initverifySubCategory: function () {
      loadCount = 0;
      var _panelId = "panelidforverifySubCategory";
      var _masterPanel = Ext.getCmp(_panelId);
      if (Ext.isEmpty(_masterPanel)) {
        _masterPanel = masterPanelforverifySubCat(_panelId);
        Application.UI.addTab(_masterPanel);
        _masterPanel.doLayout();
      } else {
        Application.UI.addTab(_masterPanel);
      }
    },
    EditViewSubCat: function () {
      Application.MyphaProductmasters.verifySubCatAddEdit = "Edit";
      Ext.getCmp("verifySubCategoryparentPanel").doLayout();
      Ext.getCmp("verifySubCatMasterForm").setTitle(
        "Edit Margin Distribution details"
      );
      Ext.getCmp("verifySubCatMasterForm").show();
      Ext.getCmp("verifySubCatMasterDetailsViewPanel").hide();
      /*<?php if (user_access("mypha_productmasters", "saveverifySubCategory")) { ?> */
      Ext.getCmp("verifySubCatEditBtn").hide();
      Ext.getCmp("verifySubCatSaveBtn").show();
      /*<?php } ?> */
      Ext.getCmp("verifySubCatCancelBtn").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("verifySubCatMasterForm").getForm();
        masterForm.load({
          params: {
            sub_category_id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=subcate_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            Ext.getCmp("parent_categorysc").getStore().baseParams.primaryBt =
              tmp.data.primary_businessTypesc;
            Ext.getCmp("parent_categorysc").getStore().load();
            Ext.getCmp("parent_categorysc").setRawValue(
              tmp.data.parent_categoryname
            );

            Ext.getCmp("main_category").getStore().baseParams.department =
              tmp.data.parent_categorysc;
            Ext.getCmp("main_category").getStore().load();
            Ext.getCmp("main_category").setRawValue(tmp.data.main_categoryName);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewModeSubCat: function () {
      var sub_category_id = arguments[0];
      /*<?php if (user_access("mypha_productmasters", "saveSubCategory")) { ?> */
      Ext.getCmp("verifySubCatEditBtn").show();
      Ext.getCmp("verifySubCatSaveBtn").hide();
      /*<?php } ?> */
      Ext.getCmp("verifySubCatCancelBtn").hide();
      Ext.getCmp("verifySubCatMasterForm").hide();
      Ext.getCmp("verifySubCatMasterDetailsViewPanel").show();
      Ext.getCmp("verifySubCategoryparentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=subCatdetailsView",
        method: "POST",
        params: { sub_category_id: sub_category_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("verifySubCatMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("verifySubCategoryparentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("verifySubCategoryparentPanel").doLayout();
      Ext.getCmp("main_category").getStore().load();
    },
  };
})();
