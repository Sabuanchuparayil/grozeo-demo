Application.ProductAttributes = (function () {
  var RECS_PER_PAGE = 12;
  var modURL = "?module=product_attributes";
  var winsize = Ext.getBody().getViewSize();
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var subCategoryStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getSubCategory",
      method: "post",
      autoLoad: true,
      fields: ["sub_category_id", "sub_category"],
      root: "data",
      remoteSort: true,
    });
    return store;
  };
  var gridSelectionChangedattributeValue = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewAttributeValuedata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewAttributeValuedata")
        .getSelectionModel()
        .getSelections()[0].data.id;
      Application.ProductAttributes.ViewAttributeValue(ID);
    }
  };
  var gridSelectionChangedattributeMaster = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewAttributeMasterdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewAttributeMasterdata")
        .getSelectionModel()
        .getSelections()[0].data.id;
      Application.ProductAttributes.ViewAttributeMaster(ID);
    }
  };
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
  var AttributeMasterForm = function () {
    var mappSubCategoryCombostore = subCategoryStore();
    var pribusinessTypeComboStore = businessTypeComboStorePrimary();
    var deptComboStore = departmentComboStore();
    var categComboStore = categoryComboStore();
    var _attributeMasterFormPanel = new Ext.form.FormPanel({
      id: "formpanelAttributeMaster",
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
          fieldLabel: "Attribute",
          id: "name",
          name: "n[name]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 33,
          maxValue: 100,
          maxLength: 20,
        },{
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
              var value = Ext.getCmp("main_category").getValue();
              mappSubCategoryCombostore.baseParams.category = this.value;
              mappSubCategoryCombostore.load();
            },
          },
        },
        {
          xtype: "lovcombo",
          store: mappSubCategoryCombostore,
          mode: "local",
          id: "attibuteSubCategory",
          allowBlank: false,
          fieldLabel: "Sub Category",
          hiddenName: "n[attibuteSubCategory]",
          displayField: "sub_category",
          valueField: "sub_category_id",
          typeAhead: true,
          anchor: "98%",
          editable: true,
          minChars: 2,
          selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 35,
          listeners: {
            select: function () {},
          },
        },{
          xtype: "radiogroup",
          anchor: "98%",
          mode: "remote",
          id: "valueMode",
          forceSelection: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 36,
          items: [
            {
              boxLabel: "Multi-Select",
              id: "valueMode1",
              name: "valueMode",
              inputValue: "1",
              listeners: {
                check: function (rgp, checked) {                  
                },
              },
            },
            {
              boxLabel: "Single Line",
              id: "valueMode2",
              name: "valueMode",
              inputValue: "2",
              listeners: {
                check: function (rgp, checked) {
                },
              },
            },
            {
              boxLabel: "Multi-Line Text",
              id: "valueMode3",
              name: "valueMode",
              inputValue: "3",
              listeners: {
                check: function (rgp, checked) {                  
                },
              },
            },
          ],
        },
        {
          xtype: "panel",
          layout: "column",
          border: false,
          items: [
            {
              layout: "form",
              columnWidth: 0.25,
              border: false,
              items: [
                {
                  fieldLabel: "Display As",
                  xtype: "combo",
                  displayField: "typeName",
                  valueField: "typeId",
                  mode: "local",
                  id: "displayAs",
                  hiddenName: "displayAs",
                  name: "n[displayAs]",
                  typeAhead: true,
                  minChars: 1,
                  triggerAction: "all",
                  selectOnFocus: true,
                  lazyRender: true,
                  anchor: "97%",
                  store: new Ext.data.JsonStore({
                    fields: ["typeId", "typeName"],
                    data: [
                      { typeId: "1", typeName: "Block" },
                      { typeId: "2", typeName: "List" },
                    ],
                  }),
                  editable: true,
                  width: 120,
                  tabIndex: 100,
                  listeners: {},
                },
              ],
            },
            {
              layout: "form",
              columnWidth: 0.25,
              border: false,
              items: [
                {
                  hidden: true,
                  xtype: "textfield",
                  id: "displayOrder",
                  emptyText: "Display Order",
                  tabIndex: 103,
                  allowBlank: false,
                  name: "n[displayOrder]",
                },
              ],
            },
            {
              layout: "form",
              columnWidth: 0.25,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  id: "inProductBrief",
                  name: "n[inProductBrief]",
                  boxLabel: "Show in Product Breif",
                  allowBlank: true,
                  inputValue: 1,
                  listeners: {
                    check: function (checkbox, checked) {},
                  },
                },
              ],
            },
            {
              layout: "form",
              columnWidth: 0.25,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  id: "inProductDetail",
                  name: "inProductDetail",
                  boxLabel: "Show in Product Detail",
                  allowBlank: true,
                  inputValue: 1,
                  listeners: {
                    check: function (checkbox, checked) {},
                  },
                },
              ],
            },
          ],
        },
        {
          xtype: "textfield",
          id: "id",
          name: "n[id]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 36,
          emptyText: "Set status..",
          id: "comboAttributeMasterStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("id").getValue())) {
            var recordSelected = Ext.getCmp("comboAttributeMasterStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboAttributeMasterStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _attributeMasterFormPanel;
  };
  var AttributeMasterMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelAttributeMasterDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {name} </td></tr>',
        '<tr><th width="40%">Subcategories </th><td>  {subCategory} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var AttributeMasterMasterStore = function () {
    var _attributeMasterMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAttributeMaster",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "id",
          root: "data",
        },
        ["id", "name", "status","subCategory"]
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
          Ext.getCmp("gridpanelMasterDataviewAttributeMasterdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewAttributeMasterdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _attributeMasterMasterStore;
  };
  var AttributeMasterMainGrid = function () {
    var _attributeMasterStore = AttributeMasterMasterStore();
    var _attributeMasterGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "name",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _attributeMasterGridFilter.remote = true;
    _attributeMasterGridFilter.autoReload = true;
    var _attributeMastermaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _attributeMasterStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewAttributeMasterdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _attributeMasterGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "ID",
          dataIndex: "id",
          sortable: true,
          hidden: true,
          tooltip: "ID",
        },
        {
          header: "Attributes",
          dataIndex: "name",
          sortable: true,
          tooltip: "Attributes",
          hideable: false,
        },
        {
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },{
          header: "Subcategory",
          dataIndex: "subCategory",
          sortable: true,
          tooltip: "Subcategory",
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _attributeMasterStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedattributeMaster,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("id");
          if (!Ext.isEmpty(ID)) {
            Application.ProductAttributes.Cache.id = ID;
            Ext.getCmp("formpanelAttributeMaster").hide();
            Application.ProductAttributes.ViewAttributeMaster(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _attributeMasterStore.load();
        },
      },
      tbar: [
        {
          text: "Create Attributes",
          tooltip: "Create Attributes ",
          icon: "./resources/images/submenuicons/add.png",
          iconCls: "my-icon1",
          hidden:true,
          handler: function () {
            Application.ProductAttributes.AttributeMasterAddEdit = "Add";
            var attributeMasterForm = Ext.getCmp(
              "formpanelAttributeMaster"
            ).getForm();
            Ext.getCmp("panelMasterAttributeMasterParent").setTitle(
              "Attributes Details"
            );
            loadedForm = null;
            attributeMasterForm.reset();
            Ext.getCmp("name").focus(false, 100);
            /*<?php if (user_access("product_attributes", "saveAttributeMaster")) { ?> */
            Ext.getCmp("buttonMasterAttributeMasterEdit").hide();
            Ext.getCmp("buttonMasterAttributeMasterSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterAttributeMasterCancel").show();
            Ext.getCmp("formpanelAttributeMaster").show();
            Ext.getCmp("panelAttributeMasterDetailsView").hide();
            Ext.getCmp("panelMasterAttributeMasterParent").doLayout();
          },
        },
      ],
    });
    return _attributeMastermaingridPanel;
  };
  var saveAttributeMaster = function () {
    var ptId = Ext.getCmp("id").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("name").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboAttributeMasterStatus").getValue())
    ) {
      if (Ext.getCmp("inProductDetail").getValue() == true) {
        var inProductDetail = 1;
      } else {
        var inProductDetail = 0;
      }
      if (Ext.getCmp("inProductBrief").getValue() == true) {
        var inProductBrief = 1;
      } else {
        var inProductBrief = 0;
      }

      Ext.Ajax.request({
        url: modURL + "&op=saveAttributeMaster",
        method: "POST",
        params: {
          id: Ext.getCmp("id").getValue(),
          name: Ext.getCmp("name").getValue(),
          attibuteSubCategory: Ext.getCmp("attibuteSubCategory").getValue(),
          status: Ext.getCmp("comboAttributeMasterStatus").getValue(),
          displayAs: Ext.getCmp("displayAs").getValue(),
          displayOrder: Ext.getCmp("displayOrder").getValue(),
          inProductDetail: inProductDetail,
          inProductBrief: inProductBrief,
          valueMode: Ext.getCmp('valueMode').getValue()
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.ProductAttributes.AttributeMasterAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewAttributeMasterdata")
              );
              Ext.getCmp("formpanelAttributeMaster").getForm().reset();
              Ext.getCmp(
                "gridpanelMasterDataviewAttributeMasterdata"
              ).store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewAttributeMasterdata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewAttributeMasterdata"
                    );
                    var index = gridPanel.store.find("id", ptId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
            }
            Application.ProductAttributes.AttributeMasterAddEdit = "";
            Application.ProductAttributes.ViewAttributeMaster(tmp.data.id);
          } else if (tmp.success === true && tmp.valid === false) {
            Ext.Msg.alert("Notification.", tmp.message);
          } else if (tmp.success === true && tmp.img_valid === false) {
            Ext.Msg.alert("Notification.", tmp.message);
          } else {
            Ext.Msg.alert("Error", "Data you are trying to save is not valid.");
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
  var masterPanelforAttributeMaster = function (id) {
    var _mpanelforAttributeMaster = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Attribute Master",
      id: id,
      iconCls: "my-icon448",
      items: [
        AttributeMasterMainGrid(),
        new Ext.Panel({
          title: "Attributes Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterAttributeMasterParent",
          height: winsize.height * 0.6,
          items: [AttributeMasterForm(), AttributeMasterMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 38,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterAttributeMasterCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewAttributeMasterdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp(
                    "gridpanelMasterDataviewAttributeMasterdata"
                  )
                    .getSelectionModel()
                    .getSelections()[0].data.id;
                  Application.ProductAttributes.ViewAttributeMaster(ID);
                }
              },
            },
            /*<?php if (user_access("product_attributes", "saveAttributeMaster")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              iconCls: "edit",
              id: "buttonMasterAttributeMasterEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
              tabIndex: 37,
              hidden:true,
              handler: function () {
                var ID = Ext.getCmp(
                  "gridpanelMasterDataviewAttributeMasterdata"
                )
                  .getSelectionModel()
                  .getSelections()[0].data.id;
                Application.ProductAttributes.EditAttributeMasterView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 39,
              cls: "left-right-buttons",
              id: "buttonMasterAttributeMasterSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveAttributeMaster();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforAttributeMaster;
  };
  var masterPanelforAttributeValue = function () {
    var _mpanelforAttributeValue = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Attribute Value",
      id: id,
      iconCls: "my-icon448",
      items: [
        AttributeValueMainGrid(),
        new Ext.Panel({
          title: "Attributes Values",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterAttributeValueParent",
          height: winsize.height * 0.6,
          items: [attributeValueSubGrid()], //AttributeValueForm()AttributeValueDetailsView()
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 38,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonAttributeValueCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewAttributeValuedata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp(
                    "gridpanelMasterDataviewAttributeValuedata"
                  )
                    .getSelectionModel()
                    .getSelections()[0].data.id;
                  Application.ProductAttributes.ViewAttributeValue(ID);
                }
              },
            },
            /*<?php if (user_access("product_attributes", "saveAttributeValue")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              iconCls: "edit",
              id: "buttonAttributeValueEdit",
              hidden:true,
              icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
              tabIndex: 37,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterDataviewAttributeValuedata")
                  .getSelectionModel()
                  .getSelections()[0].data.id;
                Application.ProductAttributes.EditAttributeValueView(ID);
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforAttributeValue;
  };
  var AttributeValueStore = function () {
    var _attributeMasterMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAttributeValueMain",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "id",
          root: "data",
        },
        ["id", "name", "valueCount","subCategory"]
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
          Ext.getCmp("gridpanelMasterDataviewAttributeValuedata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewAttributeValuedata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _attributeMasterMasterStore;
  };
  var AttributeValueMainGrid = function () {
    var _attributeValueStore = AttributeValueStore();
    var _attributeValueGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "name",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _attributeValueGridFilter.remote = true;
    _attributeValueGridFilter.autoReload = true;
    var _attributeValuemaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _attributeValueStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewAttributeValuedata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _attributeValueGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "ID",
          dataIndex: "id",
          sortable: true,
          hidden: true,
          tooltip: "ID",
        },
        {
          header: "Attributes",
          dataIndex: "name",
          sortable: true,
          tooltip: "Attributes",
          hideable: false,
        },
        {
          header: "Count",
          dataIndex: "valueCount",
          sortable: true,
          tooltip: "Count",
        },{
          header: "Subcategory",
          dataIndex: "subCategory",
          sortable: true,
          tooltip: "Subcategory",
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _attributeValueStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedattributeValue,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("id");
          if (!Ext.isEmpty(ID)) {
            Application.ProductAttributes.Cache.id = ID;
           // Ext.getCmp("formpanelAttributeValue").hide();
            Application.ProductAttributes.ViewAttributeValue(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _attributeValueStore.load();
        },
      },
      tbar: [
        {
          text: "Create Attribute Values",
          tooltip: "Create Attribute Values ",
          icon: "./resources/images/submenuicons/add.png",
          iconCls: "my-icon1",
          hidden:true,
          handler: function () {
            Application.ProductAttributes.AttributeValueAddEdit = "Add";
            //var attributeMasterForm = Ext.getCmp("formpanelAttributeValue").getForm();
            Ext.getCmp("panelMasterAttributeValueParent").setTitle(
              "Attributes Details"
            );
            loadedForm = null;
            //attributeMasterForm.reset();

            /*<?php if (user_access("product_attributes", "saveAttributeValue")) { ?> */
            Ext.getCmp("buttonAttributeValueEdit").hide();
            /*<?php } ?> */
            Ext.getCmp("buttonAttributeValueCancel").show();
            Ext.getCmp('valueName').enable();Ext.getCmp('attributes').enable();Ext.getCmp('addValueBtn').enable();
            //Ext.getCmp("formpanelAttributeValue").show();
            //Ext.getCmp("panelAttributeValueDetailsView").hide();
            //Ext.getCmp("panelMasterAttributeValueParent").doLayout();
          },
        },
      ],
    });
    return _attributeValuemaingridPanel;
  };
  var attributeComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getAttributes",
      method: "post",
      autoLoad: true,
      fields: ["id", "name"],
      root: "data",
      remoteSort: true,
    });
    return store;
  };
  var AttributeValueForm = function () {
    var attributeCombo = attributeComboStore();
    var _attributeMasterFormPanel = new Ext.form.FormPanel({
      id: "formpanelAttributeValue",
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
          xtype: "combo",
          store: attributeCombo,
          mode: "local",
          id: "attributes",
          allowBlank: false,
          fieldLabel: "Attributes",
          hiddenName: "n[attributes]",
          displayField: "name",
          valueField: "id",
          typeAhead: true,
          anchor: "98%",
          editable: true,
          minChars: 2,
          selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 35,
          listeners: {
            select: function () {
              var attributeId = Ext.getCmp("attributes").getValue();
              Ext.getCmp("gridAttributeValueList")
                .getStore()
                .load({
                  params: {
                    attributeId: attributeId,
                  },
                });
            },
          },
        },
        {
          xtype: "panel",
          layout: "column",
          border: false,
          items: [
            {
              layout: "form",
              columnWidth: 0.3,
              border: false,
              items: [
                {
                  xtype: "textfield",
                  id: "valueName",
                  emptyText: "Value",
                  tabIndex: 103,
                  allowBlank: false,
                  name: "n[valueName]",
                },
              ],
            },
            {
              layout: "form",
              columnWidth: 0.3,
              border: false,
              items: [
                {
                  xtype: "spacer",
                  height: 13,
                },
                {
                  xtype: "button",
                  text: "Add",
                  id:"addValueBtn",
                  iconCls: "add",
                  tabIndex: 504,
                  handler: function () {
                    if(!Ext.isEmpty(Ext.getCmp("attributes").getValue()) && !Ext.isEmpty(Ext.getCmp("valueName").getValue())){
                      Ext.Ajax.request({
                        url: modURL + "&op=saveAttributeValue",
                        method: "POST",
                        params: {
                          attributeId: Ext.getCmp("attributes").getValue(),
                          valueName: Ext.getCmp("valueName").getValue(),
                        },
                        success: function (response) {
                          var tmp = Ext.decode(response.responseText);
                          if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg("Success", tmp.message);
                            Ext.getCmp("gridAttributeValueList")
                              .getStore()
                              .load({
                                params: {
                                  attributeId:
                                    Ext.getCmp("attributes").getValue(),
                                },
                              });
                            Ext.getCmp("valueName").reset();
                          } else if (
                            tmp.success === true &&
                            tmp.valid === false
                          ) {
                            Ext.Msg.alert("Notification.", tmp.message);
                          } else if (
                            tmp.success === true &&
                            tmp.img_valid === false
                          ) {
                            Ext.Msg.alert("Notification.", tmp.message);
                          } else {
                            Ext.Msg.alert(
                              "Error",
                              "Data you are trying to save is not valid."
                            );
                          }
                        },
                        failure: function (response) {
                          var tmp = Ext.util.JSON.decode(response.responseText);
                          Ext.MessageBox.alert("Error", tmp.msg);
                        },
                      });
                    }else{
                      Ext.Msg.alert("Notification.", "Enter valid data and proceed");
                    }
                    
                  },
                },
              ],
            },
          ],
        },
        attributeValueSubGrid(),
      ],
      listeners: {
        afterrender: function () {},
      },
    });
    return _attributeMasterFormPanel;
  };
  var avgridStoreFunc = function () {
    var _attributeValueStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAttributeValueSubGrid",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "id",
          root: "data",
        },
        ["id", "valueName", "attributeId"]
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
        }
      }
    });
    return _attributeValueStore;
  };
  var attributeValueSubGrid = function () {
	 var attributeCombo = attributeComboStore();
    var _attributeValueStore = avgridStoreFunc();
    var _attributeValueGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "valueName",
        }
      ],
    });
    _attributeValueGridFilter.remote = true;
    _attributeValueGridFilter.autoReload = true;
    var _attributeValuemaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      height: winsize.height * 0.5,
      frame: false,
      border: false,
      loadMask: true,
      store: _attributeValueStore,
      iconCls: "money",
      id: "gridAttributeValueList",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _attributeValueGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Values",
          dataIndex: "valueName",
          sortable: true,
          tooltip: "Values",
          hideable: false,
        }
      ],
	  tbar: [{
      html: '&nbsp;Attributes : &nbsp;',
  },        {
          xtype: "combo",
          store: attributeCombo,
          mode: "local",
          id: "attributes",
          allowBlank: false,
          fieldLabel: "Attributes",
          hiddenName: "n[attributes]",
          displayField: "name",
          valueField: "id",
          typeAhead: true,
          anchor: "98%",
          editable: true,
          minChars: 2,
          selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 35,
          listeners: {
            select: function () {
              var attributeId = Ext.getCmp("attributes").getValue();
              Ext.getCmp("gridAttributeValueList")
                .getStore()
                .load({
                  params: {
                    attributeId: attributeId,
                  },
                });
            },
          },
        },{
          html: '&nbsp;Value : &nbsp;',
      },
        {
          xtype: "textfield",
          id: "valueName",
          emptyText: "Value",
          tabIndex: 103,
          allowBlank: false,
          name: "n[valueName]",
        },
        {
          xtype: "button",
          text: "Add",
          id:"addValueBtn",
          iconCls: "add",
          tabIndex: 504,
          handler: function () {
            Ext.Ajax.request({
              url: modURL + "&op=saveAttributeValue",
              method: "POST",
              params: {
                attributeId: Ext.getCmp("attributes").getValue(),
                valueName: Ext.getCmp("valueName").getValue(),
              },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true && tmp.valid === true) {
                  Application.example.msg("Success", tmp.message);
                  Ext.getCmp("gridAttributeValueList")
                    .getStore()
                    .load({
                      params: {
                        attributeId: Ext.getCmp("attributes").getValue(),
                      },
                    });
                  Ext.getCmp("valueName").reset();
                } else if (tmp.success === true && tmp.valid === false) {
                  Ext.Msg.alert("Notification.", tmp.message);
                } else if (tmp.success === true && tmp.img_valid === false) {
                  Ext.Msg.alert("Notification.", tmp.message);
                } else {
                  Ext.Msg.alert(
                    "Error",
                    "Data you are trying to save is not valid."
                  );
                }
              },
              failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert("Error", tmp.msg);
              },
            });
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true        
      }),
      listeners: {
      }
    });
    return _attributeValuemaingridPanel;
  };
  var AttributeValueDetailsView = function () {
    var grid = new Ext.Panel({
      region: "center",
      id: "panelAttributeValueDetailsView",
      height: winsize.height * 0.6,
      autoScroll: true,
      tpl: new Ext.XTemplate(
        '<div class="cdetails-outer no-border" style="width:100%; margin:15px auto;"><h3 class="history"></h3><ul class="anexure">',
        '<tpl for=".">',
        '<li><div class="details-outer">',
        '<table style="width:100%;">',
        "<tr>",
        '<td ><span class="crmname" >{valueName}</span></td>',
        "</tr>",
        "</table>",
        "</div></li>",
        "</tpl>",
        "</ul></div>",
        "<style>.field{ padding-right: 10px; }</style>"
      ),
    });
    return grid;
  };
  return {
    Cache: {},
    initAttributeMaster: function () {
      var _storegroupPanelId = "panelMasterMainAttributeMaster";
      var _masterPanelAttributeMaster = Ext.getCmp(_storegroupPanelId);
      if (Ext.isEmpty(_masterPanelAttributeMaster)) {
        _masterPanelAttributeMaster =
          masterPanelforAttributeMaster(_storegroupPanelId);
        Application.UI.addTab(_masterPanelAttributeMaster);
        _masterPanelAttributeMaster.doLayout();
      } else {
        Application.UI.addTab(_masterPanelAttributeMaster);
      }
    },
    ViewAttributeMaster: function () {
      var id = arguments[0];
      /*<?php if (user_access("product_attributes", "saveAttributeMaster")) { ?> */
      Ext.getCmp("buttonMasterAttributeMasterEdit").hide();
      Ext.getCmp("buttonMasterAttributeMasterSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterAttributeMasterCancel").hide();
      Ext.getCmp("formpanelAttributeMaster").hide();
      Ext.getCmp("panelAttributeMasterDetailsView").show();
      Ext.getCmp("panelMasterAttributeMasterParent").doLayout();
      Ext.getCmp("panelMasterAttributeMasterParent").setTitle(
        "View Attributes Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=attributeMasterdetailsView",
        method: "POST",
        params: { id: id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelAttributeMasterDetailsView"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterAttributeMasterParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterAttributeMasterParent").doLayout();
    },
    EditAttributeMasterView: function () {
      Application.ProductAttributes.AttributeMasterAddEdit = "Edit";
      Ext.getCmp("panelMasterAttributeMasterParent").doLayout();
      Ext.getCmp("panelMasterAttributeMasterParent").setTitle(
        "Edit Attributes Details"
      );
      Ext.getCmp("formpanelAttributeMaster").show();
      Ext.getCmp("panelAttributeMasterDetailsView").hide();
      /*<?php if (user_access("product_attributes", "saveAttributeMaster")) { ?> */
      Ext.getCmp("buttonMasterAttributeMasterEdit").hide();
      Ext.getCmp("buttonMasterAttributeMasterSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterAttributeMasterCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var attributeMasterForm = Ext.getCmp(
          "formpanelAttributeMaster"
        ).getForm();
        attributeMasterForm.load({
          params: {
            id: arguments[0],
          },
          url: modURL + "&op=attributeMaster_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            Ext.getCmp('parent_categorysc').getStore().baseParams.primaryBt = tmp.data.primary_businessTypesc;
												Ext.getCmp('parent_categorysc').getStore().load();
												Ext.getCmp('parent_categorysc').setRawValue(tmp.data.parent_categoryname);
												
												Ext.getCmp('main_category').getStore().baseParams.department = tmp.data.parent_categorysc;
												Ext.getCmp('main_category').getStore().load();
												Ext.getCmp('main_category').setRawValue(tmp.data.main_categoryName);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    initAttributeValue: function () {
      var _attriValuePanelId = "panelMasterMainAttributeValue";
      var _masterPanelAttributeValue = Ext.getCmp(_attriValuePanelId);
      if (Ext.isEmpty(_masterPanelAttributeValue)) {
        _masterPanelAttributeValue =
          masterPanelforAttributeValue(_attriValuePanelId);
        Application.UI.addTab(_masterPanelAttributeValue);
        _masterPanelAttributeValue.doLayout();
      } else {
        Application.UI.addTab(_masterPanelAttributeValue);
      }
    },
    ViewAttributeValue: function (attributeId) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      Ext.Ajax.request({
        url: modURL + "&op=avDetailView",
        method: "POST",
        params: {
          attributeId: attributeId,
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        success: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          //Ext.getCmp("formpanelAttributeValue").hide();
          Ext.getCmp("buttonAttributeValueCancel").hide();
          Ext.getCmp("buttonAttributeValueEdit").hide();
          
          Ext.getCmp("gridAttributeValueList")
              .getStore()
              .load({
                params: {
                  attributeId: attributeId,
                },
              });
              Ext.getCmp('valueName').disable();Ext.getCmp('attributes').disable();Ext.getCmp('addValueBtn').disable();
          /*Ext.getCmp("panelAttributeValueDetailsView").show();
          Ext.getCmp("panelAttributeValueDetailsView").doLayout();
          Ext.getCmp("panelAttributeValueDetailsView").setTitle(
            "View Attributes Values"
          );
          var propertygridPanel = Ext.getCmp("panelAttributeValueDetailsView");
          propertygridPanel.update(tmp.data);*/
        },
        failure: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", "Issue in saving");
        },
      });
    },
    EditAttributeValueView: function (attributeId) {
      Application.ProductAttributes.AttributeValueAddEdit = "Edit";
      Ext.getCmp("panelMasterAttributeValueParent").doLayout();
      Ext.getCmp("panelMasterAttributeValueParent").setTitle(
        "Attributes Values"
      );
      //Ext.getCmp("formpanelAttributeValue").show();
      //Ext.getCmp("panelAttributeValueDetailsView").hide();
      /*<?php if (user_access("product_attributes", "saveAttributeValue")) { ?> */
      Ext.getCmp("buttonAttributeValueEdit").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonAttributeValueCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(attributeId)) {

        Ext.Ajax.request({
          url: modURL + "&op=attributeValue_form_load",
          method: "POST",
          params: {
            id: attributeId
          },
          success: function (response) {
            var tmp = Ext.util.JSON.decode(response.responseText);               
            
            Ext.getCmp("gridAttributeValueList")
              .getStore()
              .load({
                params: {
                  attributeId: attributeId,
                },
              });
              Ext.getCmp('valueName').enable();Ext.getCmp('attributes').enable();Ext.getCmp('addValueBtn').enable();
              Ext.getCmp('attributes').setValue(attributeId);
          },
          failure: function (response) {
            var tmp = Ext.util.JSON.decode(response.responseText);
            Ext.MessageBox.alert("Error", "Issue in saving");
          },
        });


      }
    },
  };
})();
