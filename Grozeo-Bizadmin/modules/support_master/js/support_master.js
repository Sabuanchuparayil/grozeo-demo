Application.SupportMaster = (function () {
  var RECS_PER_PAGE = 23;
  var modURL = "?module=support_master";
  var winsize = Ext.getBody().getViewSize();
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var sq_articles = new Array();
  var supTyp_supUnit = new Array();
  var masterPanelforSupportUnit = function (id) {
    var _mpanelforSupportUnit = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Support Units",
      id: id,
      iconCls: "my-icon448",
      items: [
        SupportUnitMainGrid(),
        new Ext.Panel({
          title: "Support Unit Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterSupportUnitParent",
          height: winsize.height * 0.6,
          items: [SupportUnitForm(), SupportUnitMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            /*<?php if (user_access("support_master", "saveSupportUnits")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              iconCls: "edit",
              id: "buttonMasterSupportUnitEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
              tabIndex: 4,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
                  .getSelectionModel()
                  .getSelections()[0].data.suId;
                Application.SupportMaster.EditSupportUnitsView(ID);
              },
            },
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterSupportUnitCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
                    .getSelectionModel()
                    .getSelections()[0].data.suId;
                  Application.SupportMaster.ViewSupportUnits(ID);
                }
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "buttonMasterSupportUnitSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveSupportUnits();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforSupportUnit;
  };
  var SupportUnitForm = function () {
    var _supportunitsFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterSupportUnits",
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
          fieldLabel: "Support Unit",
          id: "suName",
          name: "n[suName]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxLength: 100,
        },
        {
          xtype: "textfield",
          id: "suId",
          name: "n[suId]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[status]",
          fieldLabel: "Status",
          allowBlank: false,
          tabIndex: 2,
          emptyText: "Set status..",
          id: "comboMasterSupportUnitsStatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("suId").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterSupportUnitsStatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterSupportUnitsStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _supportunitsFormPanel;
  };
  var SupportUnitMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelMasterSupportUnitsDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {suName} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var SupportUnitsMasterStore = function () {
    var _supportunitsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSupportUnits",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "suId",
          root: "data",
        },
        ["suId", "suName", "status"]
      ),
      sortInfo: {
        field: "suId",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _supportunitsMasterStore;
  };
  var SupportUnitMainGrid = function () {
    var _supportunitsStore = SupportUnitsMasterStore();
    var _supportunitsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "suName",
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
    _supportunitsGridFilter.remote = true;
    _supportunitsGridFilter.autoReload = true;
    var _supportunitsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _supportunitsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewSupportUnitsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _supportunitsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Support Unit",
          dataIndex: "suName",
          sortable: true,
          tooltip: "Support Unit",
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
        store: _supportunitsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedsupportunits,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("suId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportMaster.Cache.suId = ID;
            Ext.getCmp("formpanelMasterSupportUnits").hide();
            Application.SupportMaster.ViewSupportUnits(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _supportunitsStore.load();
        },
      },
      tbar: [
        {
          text: "Create Support Unit",
          tooltip: "Create Support Unit ",
          icon: "./resources/images/submenuicons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.SupportMaster.SupportUnitsAddEdit = "Add";
            var supportunitsForm = Ext.getCmp(
              "formpanelMasterSupportUnits"
            ).getForm();
            Ext.getCmp("panelMasterSupportUnitParent").setTitle(
              "Create Support Unit Details"
            );
            loadedForm = null;
            supportunitsForm.reset();
            Ext.getCmp("suName").focus(false, 100);
            /*<?php if (user_access("support_master", "saveSupportUnits")) { ?> */
            Ext.getCmp("buttonMasterSupportUnitEdit").hide();
            Ext.getCmp("buttonMasterSupportUnitSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterSupportUnitCancel").show();
            Ext.getCmp("formpanelMasterSupportUnits").show();
            Ext.getCmp("comboMasterSupportUnitsStatus").setValue(1);
            Ext.getCmp("panelMasterSupportUnitsDetailsView").hide();
            Ext.getCmp("panelMasterSupportUnitParent").doLayout();
          },
        },
      ],
    });
    return _supportunitsmaingridPanel;
  };
  var saveSupportUnits = function () {
    var ptId = Ext.getCmp("suId").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("suName").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterSupportUnitsStatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveSupportUnits",
        method: "POST",
        params: {
          id: Ext.getCmp("suId").getValue(),
          name: Ext.getCmp("suName").getValue(),
          status: Ext.getCmp("comboMasterSupportUnitsStatus").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.SupportMaster.SupportUnitsAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
              );
              Ext.getCmp("formpanelMasterSupportUnits").getForm().reset();
              Ext.getCmp(
                "gridpanelMasterDataviewSupportUnitsdata"
              ).store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewSupportUnitsdata"
                    );
                    var index = gridPanel.store.find("suId", ptId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
              //                            Ext.getCmp('gridpanelMasterDataviewSupportUnitsdata').selModel.getSelected().data = tmp.data;
              //                            Ext.getCmp('gridpanelMasterDataviewSupportUnitsdata').getStore().reload();
              //                            Ext.getCmp('gridpanelMasterDataviewSupportUnitsdata').getView().refresh();
            }
            Application.SupportMaster.SupportUnitsAddEdit = "";
            Application.SupportMaster.ViewSupportUnits(tmp.data.suId);
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
  var gridSelectionChangedsupportunits = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewSupportUnitsdata")
        .getSelectionModel()
        .getSelections()[0].data.suId;
      Application.SupportMaster.ViewSupportUnits(ID);
    }
  };
  var supportChapterPanel = function (id) {
    var _parentPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Chapters",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        supportChapterGrid(),
        new Ext.Panel({
          title: "Chapter Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterSupportChapterParent",
          height: winsize.height * 0.6,
          items: [supportChapterForm(), supportChapterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 503,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterSupportChapterCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterListingSupportChapter")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridpanelMasterListingSupportChapter")
                    .getSelectionModel()
                    .getSelections()[0].data.brand_id;
                  Application.SupportMaster.ViewSupportChapterMode(ID);
                }
              },
            },
            /*<?php if (user_access("support_master", "saveSupportChapter")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "buttonMasterSupportChapterEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 502,
              handler: function () {
                var ID = Ext.getCmp("gridpanelMasterListingSupportChapter")
                  .getSelectionModel()
                  .getSelections()[0].data.scId;
                Application.SupportMaster.EditSupportChapterView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 502,
              cls: "left-right-buttons",
              id: "buttonMasterSupportChapterSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveSupportChapter(id);
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _parentPanel;
  };
  var supportChapterGridstore = function () {
    var _supportChapterList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSupportChapter",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "scId",
          root: "data",
        },
        ["scId", "scName", "scUnitId", "status", "scUnitName"]
      ),
      sortInfo: {
        field: "scId",
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
            Ext.getCmp("gridpanelMasterListingSupportChapter")
              .getSelectionModel()
              .selectRow(0);
          }
        },
      },
    });
    return _supportChapterList;
  };
  var supportChapterForm = function () {
    var _supportChapterForm = new Ext.FormPanel({
      id: "formpanelMasterSupportChapter",
      frame: false,
      border: false,
      hidden: true,
      labelAlign: "top",
      autoHeight: true,
      labelWidth: 100,
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      items: [
        {
          xtype: "textfield",
          fieldLabel: "Chapter",
          id: "scName",
          name: "n[scName]",
          anchor: "98%",
          allowBlank: false,
          width: 300,
          tabIndex: 102,
          maxLength: 300,
        },
        mkCombo({
          type: "support_unit",
          allowBlank: false,
          value: "id",
          display: "name",
          name: "n[scUnitId]",
          fieldLabel: "Support Unit",
          emptyText: "Select Support Unit",
          tabIndex: 101,
          anchor: "98%",
          id: "scUnitId",
          listeners: false,
          cx: "S_1",
        }),
        {
          xtype: "textfield",
          id: "scId",
          name: "n[scId]",
          hidden: true,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          allowBlank: false,
          name: "n[status]",
          fieldLabel: "Status",
          tabIndex: 103,
          emptyText: "Set status..",
          id: "comboMasterSupportChapterstatus",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("scId").getValue())) {
            var recordSelected = Ext.getCmp("comboMasterSupportChapterstatus")
              .getStore()
              .getAt(0);
            Ext.getCmp("comboMasterSupportChapterstatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _supportChapterForm;
  };
  var saveSupportChapter = function () {
    var parCcat = Ext.getCmp("scId").getValue();
    if (
      Ext.getCmp("gridpanelMasterListingSupportChapter").getStore().getCount() >
      0
    ) {
      var index = Ext.getCmp("gridpanelMasterListingSupportChapter")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }

    var lastOptions = Ext.getCmp(
      "gridpanelMasterListingSupportChapter"
    ).getStore().lastOptions;
    if (
      !Ext.isEmpty(Ext.getCmp("scName").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("comboMasterSupportChapterstatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveSupportChapter",
        method: "POST",
        params: {
          id: Ext.getCmp("scId").getValue(),
          name: Ext.getCmp("scName").getValue(),
          status: Ext.getCmp("comboMasterSupportChapterstatus").getValue(),
          scUnitId: Ext.getCmp("scUnitId").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.SupportMaster.SupportChapterAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterListingSupportChapter")
              );
              Ext.getCmp("formpanelMasterSupportChapter").getForm().reset();
              Ext.getCmp("gridpanelMasterListingSupportChapter").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterListingSupportChapter")
                .getStore()
                .reload(lastOptions);
              var gridPanel = Ext.getCmp(
                "gridpanelMasterListingSupportChapter"
              );
              gridPanel.getSelectionModel().selectRow(index);
              Application.SupportMaster.ViewSupportChapterMode(tmp.data.scId);
            }
            Application.SupportMaster.SupportChapterAddEdit = "";
            Application.SupportMaster.ViewSupportChapterMode(tmp.data.scId);
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
  var supportChapterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "xtemplateMasterSupportChapterViewDetails",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Chapter </th><td>  {scName} </td></tr>',
        '<tr><th width="40%">Support Unit </th><td>  {chapterSupportUnitName} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var supportChapterGrid = function () {
    var _supportChapterGridstore = supportChapterGridstore();
    var _supportChapterFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "scName",
        },
        {
          type: "string",
          dataIndex: "status",
        },
        {
          type: "string",
          dataIndex: "scUnitName",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _supportChapterFilter.remote = true;
    _supportChapterFilter.autoReload = true;
    var _gridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _supportChapterGridstore,
      //iconCls: 'money',
      id: "gridpanelMasterListingSupportChapter",
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [_supportChapterFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Chapter",
          dataIndex: "scName",
          sortable: true,
          tooltip: "Chapter",
          hideable: true,
        },
        {
          header: "Support Unit",
          dataIndex: "scUnitName",
          sortable: true,
          tooltip: "Support Unit",
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
        store: _supportChapterGridstore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_supportChapterFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedsupportChapter,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("scId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportMaster.Cache.scId = ID;
            Ext.getCmp("formpanelMasterSupportChapter").hide();
            Application.SupportMaster.ViewSupportChapterMode(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _supportChapterGridstore.load();
        },
      },
      tbar: [
        {
          text: "Create Chapter",
          tooltip: "Create Chapter",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.SupportMaster.SupportChapterAddEdit = "Add";
            var masterForm = Ext.getCmp(
              "formpanelMasterSupportChapter"
            ).getForm();
            Ext.getCmp("panelMasterSupportChapterParent").setTitle(
              "Create Chapter Details"
            );
            loadedForm = null;
            masterForm.reset();
            Ext.getCmp("scName").focus(false, 100);
            /*<?php if (user_access("support_master", "saveSupportChapter")) { ?> */
            Ext.getCmp("buttonMasterSupportChapterEdit").hide();
            Ext.getCmp("buttonMasterSupportChapterSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterSupportChapterCancel").show();
            Ext.getCmp("formpanelMasterSupportChapter").show();
            Ext.getCmp("comboMasterSupportChapterstatus").setValue(1);
            Ext.getCmp("xtemplateMasterSupportChapterViewDetails").hide();
            Ext.getCmp("panelMasterSupportChapterParent").doLayout();
          },
        },
      ],
    });
    return _gridPanel;
  };
  var gridSelectionChangedsupportChapter = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterListingSupportChapter")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterListingSupportChapter")
        .getSelectionModel()
        .getSelections()[0].data.scId;
      Application.SupportMaster.ViewSupportChapterMode(ID);
    }
  };
  var SupportTopicMasterStore = function () {
    var _catStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listTopic",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "stId",
          root: "data",
        },
        ["stId", "stName", "status", "stChapterId", "stChapterName"]
      ),
      sortInfo: {
        field: "stName",
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
            Ext.getCmp("SupportTopicMasterGrid")
              .getSelectionModel()
              .selectRow(0);
          }
        },
      },
    });
    return _catStore;
  };
  var TopicGrid = function () {
    var _topicGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "stName",
        },
        {
          type: "string",
          dataIndex: "stChapterName",
        },
      ],
    });
    _topicGridFilter.remote = true;
    _topicGridFilter.autoReload = true;
    var _SupportTopicMasterStore = SupportTopicMasterStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _SupportTopicMasterStore,
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
      plugins: [_topicGridFilter],
      id: "SupportTopicMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Topic",
          sortable: true,
          dataIndex: "stName",
          tooltip: "Topic",
          hideable: true,
        },
        {
          header: "Chapter",
          sortable: true,
          dataIndex: "stChapterName",
          tooltip: "Chapter",
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedTopic,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("stId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportMaster.Cache.stId = ID;
            Ext.getCmp("supportTopicMasterForm").hide();
            Application.SupportMaster.supportTopicViewMode(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _SupportTopicMasterStore.load();
        },
      },
      tbar: [
        {
          text: "Create Topic",
          tooltip: "Create Topic",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.SupportMaster.catAddEdit = "Add";
            var masterForm = Ext.getCmp("supportTopicMasterForm").getForm();
            loadedForm = null;
            masterForm.reset();
            Ext.getCmp("stName").focus(false, 100);
            /*<?php if (user_access("support_master", "saveTopic")) { ?> */
            Ext.getCmp("topicEditBtn").hide();
            Ext.getCmp("topicSaveBtn").show();
            /*<?php } ?> */
            Ext.getCmp("topicCancelBtn").show();
            Ext.getCmp("supportTopicMasterForm").show();
            Ext.getCmp("TopicMasterDetailsViewPanel").hide();
            Ext.getCmp("statusTopic").setValue(1);
            Ext.getCmp("TopicparentPanel").doLayout();
            Ext.getCmp("TopicparentPanel").setTitle("Create Topic Details");
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _SupportTopicMasterStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_topicGridFilter],
      }),
      stripeRows: true,
    });
    return _gridPanel;
  };
var parentQuestionComboStore = function () {
  var store = new Ext.data.JsonStore({
    autoLoad: true,
    url: modURL + "&op=getParentQuestion",
    method: "post",
    fields: ["questionId", "questionName"],
    root: "data",
  });
  return store;
};
  var supportUnitComboStorePrimary = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getSupportUnit",
      method: "post",
      fields: ["suId", "suName"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
var supportTypeComboStore = function () {
  var store = new Ext.data.JsonStore({
    autoLoad: true,
    url: modURL + "&op=getSupportType",
    method: "post",
    fields: ["typeId", "typeName"],
    //totalProperty: 'totalCount',
    root: "data",
  });
  return store;
};
  var allChapterComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getAllChapters",
      method: "post",
      fields: ["scId", "scName"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var chapterComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: false,
      url: modURL + "&op=getChapter",
      method: "post",
      fields: ["scId", "scName"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var topicComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getTopic",
      method: "post",
      fields: ["stId", "stName"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var subTopicComboStorefn = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getSubTopic",
      method: "post",
      fields: ["subTopicId", "subTopicName"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var TopicMasterForms = function () {
    var suComboStore = supportUnitComboStorePrimary();
    var chapterComboStorefn = chapterComboStore();
    var _topicFormPanel = new Ext.form.FormPanel({
      frame: false,
      border: true,
      hideBorders: true,
      labelWidth: 120,
      labelAlign: "top",
      fileUpload: true,
      autoScroll: true,
      hidden: true,
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      id: "supportTopicMasterForm",
      items: [
        {
          xtype: "textfield",
          fieldLabel: "Topic",
          id: "stName",
          name: "n[stName]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
        },
        {
          xtype: "hidden",
          id: "stId",
          name: "n[stId]",
        },
        {
          xtype: "combo",
          store: suComboStore,
          mode: "local",
          id: "topicSupportUnit",
          allowBlank: false,
          fieldLabel: "Support  Unit",
          hiddenName: "n[topicSupportUnit]",
          displayField: "suName",
          valueField: "suId",
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
              var value = Ext.getCmp("topicSupportUnit").getValue();
              chapterComboStorefn.baseParams.scUnitId = this.value;
              chapterComboStorefn.load();
            },
          },
        },
        {
          xtype: "combo",
          store: chapterComboStorefn,
          mode: "local",
          id: "topicChapter",
          allowBlank: false,
          fieldLabel: "Chapter",
          hiddenName: "n[topicChapter]",
          displayField: "scName",
          valueField: "scId",
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
              var value = record.data.scId;
              if (value > 0) {
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
          id: "TopicMap",
          style: { "font-weight": "bold" },
          anchor: "97%",
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          allowBlank: false,
          value: "id",
          display: "text",
          name: "n[comboMasterSupportTopicstatus]",
          fieldLabel: "Status",
          emptyText: "Set status..",
          editable: false,
          typeAhead: false,
          tabIndex: 3,
          id: "statusTopic",
        }),
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("stId").getValue())) {
            var recordSelected = Ext.getCmp("statusTopic").getStore().getAt(0);
            Ext.getCmp("statusTopic").setValue(recordSelected.get("id"));
          }
        },
      },
    });
    return _topicFormPanel;
  };
  var TopicMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "TopicMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Topic </th><td> {stName} </td></tr>',
        '<tr><th width="40%">Chapter </th><td> {stChapterName} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var saveTopic = function () {
    var catId = Ext.getCmp("stId").getValue();
    if (Ext.getCmp("SupportTopicMasterGrid").getStore().getCount() > 0) {
      var index = Ext.getCmp("SupportTopicMasterGrid")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }
    var lastOptions = Ext.getCmp("SupportTopicMasterGrid").getStore()
      .lastOptions;
    if (
      !Ext.isEmpty(
        Ext.getCmp("stName").getValue() &&
          Ext.getCmp("topicSupportUnit").getValue() &&
          Ext.getCmp("topicChapter").getValue() &&
          Ext.getCmp("statusTopic").getValue()
      )
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveTopic",
        method: "POST",
        params: {
          id: Ext.getCmp("stId").getValue(),
          name: Ext.getCmp("stName").getValue(),
          topicSupportUnit: Ext.getCmp("topicSupportUnit").getValue(),
          topicChapter: Ext.getCmp("topicChapter").getValue(),
          status: Ext.getCmp("statusTopic").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.SupportMaster.catAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("SupportTopicMasterGrid")
              );
              Ext.getCmp("SupportTopicMasterGrid").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("SupportTopicMasterGrid")
                .getStore()
                .reload(lastOptions);
              var gridPanel = Ext.getCmp("SupportTopicMasterGrid");
              gridPanel.getSelectionModel().selectRow(index);
              Application.SupportMaster.supportTopicViewMode(tmp.data.stId);
            }
            Application.SupportMaster.catAddEdit = "";
            Application.SupportMaster.supportTopicViewMode(tmp.data.stId);
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
  var masterPanelforTopic = function (id) {
    var _mpanelforcat = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Topic",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        TopicGrid(),
        new Ext.Panel({
          title: "Topic Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "TopicparentPanel",
          height: winsize.height * 0.6,
          items: [TopicMasterForms(), TopicMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "topicCancelBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("SupportTopicMasterGrid")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("SupportTopicMasterGrid")
                    .getSelectionModel()
                    .getSelections()[0].data.stId;
                  Application.SupportMaster.supportTopicViewMode(ID);
                }
              },
            },
            /*<?php if (user_access("support_master", "saveTopic")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "topicEditBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              hidden: true,
              handler: function () {
                var ID = Ext.getCmp("SupportTopicMasterGrid")
                  .getSelectionModel()
                  .getSelections()[0].data.stId;
                Application.SupportMaster.topicEditView(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "topicSaveBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveTopic(id);
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforcat;
  };
  var gridSelectionChangedTopic = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("SupportTopicMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("SupportTopicMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.stId;
      Application.SupportMaster.supportTopicViewMode(ID);
    }
  };
  var subTopicMasterForms = function () {
    var supportUnitComboStore = supportUnitComboStorePrimary();
    var chptrComboStore = chapterComboStore();
    var tpicComboStore = topicComboStore();
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
      id: "subtopicMasterForm",
      items: [
        {
          xtype: "textfield",
          fieldLabel: "Sub Topic",
          id: "subTopicName",
          name: "n[subTopicName]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
        },
        {
          xtype: "hidden",
          id: "subTopicId",
          name: "n[subTopicId]",
        },
        {
          xtype: "combo",
          store: supportUnitComboStore,
          mode: "local",
          id: "subTopicSuId",
          allowBlank: false,
          fieldLabel: "Support Unit",
          hiddenName: "n[subTopicSuId]",
          displayField: "suName",
          valueField: "suId",
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
              var value = Ext.getCmp("subTopicSuId").getValue();
              chptrComboStore.baseParams.scUnitId = this.value;
              chptrComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: chptrComboStore,
          mode: "local",
          id: "subTopicScId",
          allowBlank: false,
          fieldLabel: "Chapter",
          hiddenName: "n[subTopicScId]",
          displayField: "scName",
          valueField: "scId",
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
              var value = Ext.getCmp("subTopicScId").getValue();
              tpicComboStore.baseParams.stChapterId = this.value;
              tpicComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: tpicComboStore,
          mode: "local",
          id: "mainTopicId",
          allowBlank: false,
          fieldLabel: "Topic",
          hiddenName: "n[mainTopicId]",
          displayField: "stName",
          valueField: "stId",
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
              var value = record.data.mainTopicId;
              if (value > 0) {
              }
            },
          },
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          allowBlank: false,
          name: "n[status]",
          fieldLabel: "Status",
          emptyText: "Set status..",
          tabIndex: 3,
          id: "status",
        }),
      ],
    });
    return panel;
  };
  var subTopicMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "subTopicMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Sub Topic </th><td> {subTopicName} </td></tr>',
        '<tr><th width="40%">Topic </th><td> {stName} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"status == '1'\">Active</tpl>",
        "<tpl if=\"status == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var masterPanelforSubTopic = function (id) {
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Sub Topic",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        subTopicGrid(),
        new Ext.Panel({
          title: "Sub Topic Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "subTopicparentPanel",
          height: winsize.height * 0.6,
          items: [subTopicMasterForms(), subTopicMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "subTopicCancelBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("subtopicMasterGrid")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("subtopicMasterGrid")
                    .getSelectionModel()
                    .getSelections()[0].data.subTopicId;
                  Application.SupportMaster.ViewModeSubTopic(ID);
                }
              },
            },
            /*<?php if (user_access("support_master", "saveSubTopic")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "subTopicEditBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              hidden: true,
              handler: function () {
                var ID = Ext.getCmp("subtopicMasterGrid")
                  .getSelectionModel()
                  .getSelections()[0].data.subTopicId;
                Application.SupportMaster.EditViewSubTopic(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "subTopicSaveBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                savesubTopicgeory();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return panel;
  };
  var subTopicGrid = function () {
    var _subTopicGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "subTopicName",
        },
        {
          type: "string",
          dataIndex: "stName",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "substatus",
        },
      ],
    });
    _subTopicGridFilter.remote = true;
    _subTopicGridFilter.autoReload = true;
    var _subTopicGridStore = subTopicGridStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _subTopicGridStore,
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
      plugins: [_subTopicGridFilter],
      id: "subtopicMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Sub Topic",
          sortable: true,
          dataIndex: "subTopicName",
          tooltip: "Sub Topic",
          hideable: true,
        },
        {
          header: "Topic",
          sortable: true,
          dataIndex: "stName",
          tooltip: "Topic",
        },
        {
          header: "Status",
          sortable: true,
          dataIndex: "substatus",
          tooltip: "Status",
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedST,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("subTopicId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportMaster.Cache.subTopicId = ID;
            Ext.getCmp("subtopicMasterForm").hide();
            Application.SupportMaster.ViewModeSubTopic(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _subTopicGridStore.load();
        },
      },
      tbar: [
        {
          text: "Create Sub Topic",
          tooltip: "Create Sub Topic",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.SupportMaster.subtopicAddEdit = "Add";
            var masterForm = Ext.getCmp("subtopicMasterForm").getForm();
            loadedForm = null;
            masterForm.reset();
            Ext.getCmp("subTopicName").focus(false, 100);
            /*<?php if (user_access("support_master", "saveSubTopic")) { ?> */
            Ext.getCmp("subTopicEditBtn").hide();
            Ext.getCmp("subTopicSaveBtn").show();
            /*<?php } ?> */
            Ext.getCmp("subTopicCancelBtn").show();
            Ext.getCmp("subtopicMasterForm").show();
            Ext.getCmp("status").setValue(1);
            Ext.getCmp("subTopicMasterDetailsViewPanel").hide();
            Ext.getCmp("subTopicparentPanel").doLayout();
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _subTopicGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_subTopicGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "subtopic_name_col",
    });
    return _gridPanel;
  };
  var subTopicGridStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSubTopic",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "subTopicId",
          root: "data",
        },
        ["subTopicId", "subTopicName", "mainTopicId", "stName", "substatus"]
      ),
      sortInfo: {
        field: "subTopicId",
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
            Ext.getCmp("subtopicMasterGrid").getSelectionModel().selectRow(0);
          }
        },
      },
    });
    return _store;
  };
  var savesubTopicgeory = function () {
    if (Ext.getCmp("subtopicMasterGrid").getStore().getCount() > 0) {
      var index = Ext.getCmp("subtopicMasterGrid")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }

    var lastOptions = Ext.getCmp("subtopicMasterGrid").getStore().lastOptions;
    var subtopicid = Ext.getCmp("subTopicId").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("subTopicName").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("status").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("mainTopicId").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveSubTopic",
        method: "POST",
        params: {
          id: Ext.getCmp("subTopicId").getValue(),
          name: Ext.getCmp("subTopicName").getValue(),
          status: Ext.getCmp("status").getValue(),
          mainTopicId: Ext.getCmp("mainTopicId").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.SupportMaster.subtopicAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("subtopicMasterGrid")
              );
              Ext.getCmp("subtopicMasterGrid").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("subtopicMasterGrid").getStore().reload(lastOptions);
              var gridPanel = Ext.getCmp("subtopicMasterGrid");
              gridPanel.getSelectionModel().selectRow(index);
              Application.SupportMaster.ViewModeSubTopic(tmp.data.subTopicId);
            }
            Application.SupportMaster.subtopicAddEdit = "";
            Application.SupportMaster.ViewModeSubTopic(tmp.data.subTopicId);
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
  var gridSelectionChangedST = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("subtopicMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("subtopicMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.subTopicId;
      Application.SupportMaster.ViewModeSubTopic(ID);
    }
  };
  var masterPanelforArticle = function (id) {
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Article",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        articleGrid(),
        new Ext.Panel({
          title: "Article Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.6,
          autoScroll: true,
          id: "articleParentPanel",
          height: winsize.height * 0.6,
          items: [articleMasterForms(), articleMasterDetailsView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "articleCancelBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("articleMasterGrid")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("articleMasterGrid")
                    .getSelectionModel()
                    .getSelections()[0].data.articleId;
                  Application.SupportMaster.ViewModeArticle(ID);
                }
              },
            },
            /*<?php if (user_access("support_master", "saveArticle")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "articleEditBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              hidden: true,
              handler: function () {
                var ID = Ext.getCmp("articleMasterGrid")
                  .getSelectionModel()
                  .getSelections()[0].data.articleId;
                Application.SupportMaster.EditViewArticle(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "articleSaveBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveArticle();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return panel;
  };
  var articleMasterForms = function () {
    var stComboStore = supportTypeComboStore();
    var suComboStore = supportUnitComboStorePrimary();
    var chptrComboStore = allChapterComboStore();
    var tpicComboStore = topicComboStore();
    var subTopicComboStore = subTopicComboStorefn();
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
      id: "articleMasterForm",
      items: [
        {
          xtype: "spacer",
          height: 10,
        },
        {
          xtype: "textfield",
          fieldLabel: "Title",
          id: "articleName",
          name: "n[articleName]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxLength: 800,
        },
        {
          xtype: "hidden",
          id: "articleId",
          name: "n[articleId]",
        },{
          xtype: "combo",
          store: stComboStore,
          mode: "local",
          id: "articleStId",
          allowBlank: false,
          fieldLabel: "Support Benificiary",
          hiddenName: "n[articleStId]",
          displayField: "typeName",
          valueField: "typeId",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 123,
          listeners: {
            select: function () {
              var value = Ext.getCmp("articleStId").getValue();
              suComboStore.baseParams.suTypeId = this.value;
              suComboStore.load();
            },
          },
        },{
          xtype: "combo",
          store: suComboStore,
          mode: "local",
          id: "articleSuId",
          allowBlank: false,
          fieldLabel: "Support Unit",
          hiddenName: "n[articleSuId]",
          displayField: "suName",
          valueField: "suId",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 123,
          listeners: {
            select: function () {
              var value = Ext.getCmp("articleSuId").getValue();
              chptrComboStore.baseParams.scUnitId = this.value;
              chptrComboStore.load();
            },
          },
        },{
          xtype: "panel",
          layout: "column",
          frame: false,
          border: false,
          items: [{
            columnWidth: 0.5,
            layout: "form",
            frame: false,
            border: false,
            items: [
              {
                xtype: "checkbox",
                id: "isFeaturedArticle",
                name: "n[isFeaturedArticle]",
                //inputValue: 1,
                boxLabel: "Featured",
              },
            ],
          },{
            columnWidth: 0.5,
            layout: "form",
            frame: false,
            border: false,
            items: [
            ]
          }]
        },
        {
          xtype: "combo",
          store: chptrComboStore,
          mode: "local",
          id: "articleChapter",
          fieldLabel: "Chapter",
          hiddenName: "n[articleChapter]",
          displayField: "scName",
          valueField: "scId",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 801,
          listeners: {
            select: function () {
              var value = Ext.getCmp("articleChapter").getValue();
              tpicComboStore.baseParams.stChapterId = this.value;
              tpicComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: tpicComboStore,
          mode: "local",
          id: "articleTopic",
          fieldLabel: "Topic",
          hiddenName: "n[articleTopic]",
          displayField: "stName",
          valueField: "stId",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 802,
          listeners: {
            select: function (combo, record, index) {
              var value = Ext.getCmp("articleTopic").getValue();
              subTopicComboStore.baseParams.mainTopicId = this.value;
              subTopicComboStore.load();
            },
          },
        },
        {
          xtype: "combo",
          store: subTopicComboStore,
          mode: "local",
          id: "articleSubTopic",
          fieldLabel: "Sub Topic",
          hiddenName: "n[articleSubTopic]",
          displayField: "subTopicName",
          valueField: "subTopicId",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 803,
          listeners: {},
        },
        {
          xtype: "templateeditormce",
          fieldLabel: "Content",
          id: "articleContent",
          name: "n[articleContent]",
          anchor: "95%",
          height: 270,
          allowBlank: false,
          tabIndex: 804,
          maxLength: 900,
        },
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          allowBlank: false,
          name: "n[articleStatus]",
          fieldLabel: "Status",
          emptyText: "Set status..",
          tabIndex: 805,
          id: "articleStatus",
        }),
      ],
    });
    return panel;
  };
  var articleMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "articleMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Title </th></tr>',
        '<tr><td> {articleName} </td></tr>',
        '<tr><td> {articleContent} </td></tr>',
        "</table>",
        "</div>"
      ),
    });
  };
  var articleGrid = function () {
    var _articleGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "articleName",
        },
        {
          type: "string",
          dataIndex: "articleChapterName",
        },
        {
          type: "string",
          dataIndex: "articleTopicName",
        },{
          type: "string",
          dataIndex: "articleSuName",
        },{
          type: "string",
          dataIndex: "articleStName",
        },{
          type: "string",
          dataIndex: "articleSubTopicName",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "articleStatus",
        },
      ],
    });
    _articleGridFilter.remote = true;
    _articleGridFilter.autoReload = true;
    var _articleGridStore = articleGridStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _articleGridStore,
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
      plugins: [_articleGridFilter],
      id: "articleMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Name",
          sortable: true,
          dataIndex: "articleName",
          tooltip: "Name",
          hideable: true,
        },{
          header: "Division",
          sortable: true,
          dataIndex: "articleStName",
          tooltip: "Division",
          hideable: true,
        },{
          header: "Unit",
          sortable: true,
          dataIndex: "articleSuName",
          tooltip: "Unit",
          hideable: true,
        },
        {
          header: "Chapter",
          sortable: true,
          dataIndex: "articleChapterName",
          tooltip: "Chapter",
          hideable: true,
        },
        {
          header: "Topic",
          sortable: true,
          dataIndex: "articleTopicName",
          tooltip: "Topic",
        },
        {
          header: "Sub Topic",
          sortable: true,
          dataIndex: "articleSubTopicName",
          tooltip: "Sub Topic",
        },
        {
          header: "Status",
          sortable: true,
          dataIndex: "status",
          tooltip: "Status",
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedArticle,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("articleId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportMaster.Cache.articleId = ID;
            Ext.getCmp("articleMasterForm").hide();
            Application.SupportMaster.ViewModeArticle(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _articleGridStore.load();
        },
      },
      tbar: [
        {
          text: "Create Article",
          tooltip: "Create Article",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.SupportMaster.articleAddEdit = "Add";
            var masterForm = Ext.getCmp("articleMasterForm").getForm();
            loadedForm = null;
            masterForm.reset();
            //Ext.getCmp("articleContent").focus(false, 100);
            /*<?php if (user_access("support_master", "saveArticle")) { ?> */
            Ext.getCmp("articleEditBtn").hide();
            Ext.getCmp("articleSaveBtn").show();
            /*<?php } ?> */
            Ext.getCmp("articleCancelBtn").show();
            Ext.getCmp("articleMasterForm").show();
            Ext.getCmp("articleStatus").setValue(1);
            Ext.getCmp("articleContent").setValue('');
            Ext.getCmp("articleMasterDetailsViewPanel").hide();
            Ext.getCmp("articleParentPanel").doLayout();
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _articleGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_articleGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  var articleGridStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listArticle",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "articleId",
          root: "data",
        },
        [
          "articleId",
          "articleName",
          "articleChapter",
          "articleChapterName",
          "articleTopic",
          "articleTopicName",
          "articleSubTopic",
          "articleSubTopicName",
          "articleStatus",
          "status","articleSuId","articleSuName","articleStName"
        ]
      ),
      sortInfo: {
        field: "articleId",
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
            Ext.getCmp("articleMasterGrid").getSelectionModel().selectRow(0);
          }
        },
      },
    });
    return _store;
  };
  var saveArticle = function () {
    if (Ext.getCmp("articleMasterGrid").getStore().getCount() > 0) {
      var index = Ext.getCmp("articleMasterGrid")
        .getSelectionModel()
        .getSelections()[0].rowIndex;
    } else {
      var index = 0;
    }

    var lastOptions = Ext.getCmp("articleMasterGrid").getStore().lastOptions;
    var subtopicid = Ext.getCmp("articleId").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("articleSuId").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("articleStatus").getValue()) && 
      !Ext.isEmpty(Ext.getCmp("articleContent").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=savearticle",
        method: "POST",
        params: {
          articleId: Ext.getCmp("articleId").getValue(),
          isFeaturedArticle:Ext.getCmp("isFeaturedArticle").getValue(),
          articleStId: Ext.getCmp("articleStId").getValue(),
          articleSuId: Ext.getCmp("articleSuId").getValue(),
          articleName: Ext.getCmp("articleName").getValue(),
          articleChapter: Ext.getCmp("articleChapter").getValue(),
          articleTopic: Ext.getCmp("articleTopic").getValue(),
          articleSubTopic: Ext.getCmp("articleSubTopic").getValue(),
          articleStatus: Ext.getCmp("articleStatus").getValue(),
          articleContent: Ext.getCmp("articleContent").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (Application.SupportMaster.articleAddEdit == "Add") {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("articleMasterGrid")
              );
              Ext.getCmp("articleMasterGrid").store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("articleMasterGrid").getStore().reload(lastOptions);
              var gridPanel = Ext.getCmp("articleMasterGrid");
              gridPanel.getSelectionModel().selectRow(index);
              Application.SupportMaster.ViewModeArticle(tmp.data.articleId);
            }
            Application.SupportMaster.articleAddEdit = "";
            Application.SupportMaster.ViewModeArticle(tmp.data.articleId);
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
  var gridSelectionChangedArticle = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("articleMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("articleMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.articleId;
      Application.SupportMaster.ViewModeArticle(ID);
    }
  };
  var masterPanelforQuestion = function (id) {
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Question",
      id: id,
      //iconCls: 'my-icon444',
      items: [
        questionGrid(),
        new Ext.Panel({
          title: "Question Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "questionParentPanel",
          height: winsize.height * 0.6,
          items: [questionMasterForms(), questionMasterDetailsView(),questArticlListView()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "questionCancelBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("questionMasterGrid")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("questionMasterGrid")
                    .getSelectionModel()
                    .getSelections()[0].data.questionId;
                  Application.SupportMaster.ViewModeQuestion(ID);
                }
              },
            },
            /*<?php if (user_access("support_master", "saveQuestion")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              id: "questionEditBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 4,
              hidden: true,
              handler: function () {
                var ID = Ext.getCmp("questionMasterGrid")
                  .getSelectionModel()
                  .getSelections()[0].data.questionId;
                Application.SupportMaster.EditViewQuestion(ID);
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "questionSaveBtn",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveQuestions();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return panel;
  };
  var questionMasterForms = function(){

    var suComboStore = supportUnitComboStorePrimary();
    var chapterComboStorefn = chapterComboStore();
    var pqComboStore = parentQuestionComboStore();
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
      id: "questionMasterForm",
      items: [
        {
          xtype: "spacer",
          height: 10,
        },
        {
          xtype: "textfield",
          fieldLabel: "Question",
          id: "questionName",
          name: "n[questionName]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 122,
          maxLength: 200,
        },
        {
          xtype: "hidden",
          id: "questionId",
          name: "n[questionId]",
        },
        {
          xtype: "combo",
          store: suComboStore,
          mode: "local",
          id: "questionSuId",
          allowBlank: false,
          fieldLabel: "Support Unit",
          hiddenName: "n[questionSuId]",
          displayField: "suName",
          valueField: "suId",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 123,
          listeners: {
            select: function () {
              var value = Ext.getCmp("questionSuId").getValue();
              chapterComboStorefn.baseParams.scUnitId = this.value;
              chapterComboStorefn.load();
            },
          },
        },{
          xtype: "combo",
          store: chapterComboStorefn,
          mode: "local",
          id: "questionChapterId",
          allowBlank: false,
          fieldLabel: "Chapter",
          hiddenName: "n[questionChapterId]",
          displayField: "scName",
          valueField: "scId",
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
              var value = record.data.scId;
              if (value > 0) {
              }
            },
          },
        },{
          xtype: "panel",
          layout: "column",
          frame: false,
          border: false,
          items: [{
            columnWidth: 0.5,
            layout: "form",
            frame: false,
            border: false,
            items: [{
              xtype: "checkbox",
              checked: true,
              id: "isParentQuestion",
              name: "n[isParentQuestion]",
              //inputValue: 1,
              boxLabel: "Mark as Parent Question",
              listeners: {
                check: function (cb1, checked) {
                  chkd = checked;
                  if (checked == true) {
                    Ext.getCmp('parentQuestionId').hide();
                  }else{
                    Ext.getCmp('parentQuestionId').show();
                  }
                }
            }
            }
            ]
          },{
            columnWidth: 0.5,
            layout: "form",
            frame: false,
            border: false,
            items: [{
              xtype: "checkbox",
              id: "isFeaturedQuestion",
              name: "n[isFeaturedQuestion]",
              boxLabel: "Featured",
              listeners: {            
            }
            }
            ]
          }]
        }, {
          
          xtype: "combo",
          store: pqComboStore,
          mode: "local",
          id: "parentQuestionId",
          fieldLabel: "Parent Question",
          hiddenName: "n[parentQuestionId]",
          displayField: "questionName",
          valueField: "questionId",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          //selectOnFocus: true,
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 124,
          listeners: {
            select: function () {
              
            },
          },
        }, {
          xtype: "textarea",
          fieldLabel: "Content",
          id: "questionContent",
          name: "n[questionContent]",
          anchor: "95%",
          height: 270,
          allowBlank: false,
          tabIndex: 125,
          maxLength: 900,
        },/*{
          xtype: "templateeditormce",
          fieldLabel: "Content",
          id: "questionContent",
          name: "n[questionContent]",
          anchor: "95%",
          height: 270,
          allowBlank: false,
          tabIndex: 125,
          maxLength: 900,
        },*/ qesArticleGrid(),          
        mkCombo({
          type: STATUS_COMBO_DATA,
          value: "id",
          display: "text",
          name: "n[questionStatus]",
          fieldLabel: "Status",
          emptyText: "Set status..",
          tabIndex: 126,
          id: "questionStatus",
        }),
      ],
    });
    return panel;
  };
  var qesArticleGrid = function () {
    var _quesArticleGridStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getQuestionarticles",
      method: "post",
      fields: ["articleId", "articleName", "checked"],
      // root: 'data',
      remoteSort: true,
    });
    var ck_selection = new Ext.grid.CheckboxSelectionModel({
      multiSelect: true,
      checkOnly: true,
      listeners: {
        rowdeselect: function (sm, rowIndex, record) {
          var ind = sq_articles.indexOf(record.get("articleId"));
          if (ind > -1) sq_articles.splice(ind, 1);
          record.set("checked", "false");
        },
        rowselect: function (sm, rowIndex, record) {
          var ind = sq_articles.indexOf(record.get("articleId"));
          if (ind == -1)
            sq_articles.push(record.get("articleId"));
          record.set("checked", "true");
        },
      },
    });
    var __qaGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "articleName",
        },
      ],
    });
    __qaGridFilter.remote = true;
    __qaGridFilter.autoReload = true;
    var _questionArticleGrid = new Ext.grid.GridPanel({
      id: "gridQuestionArticles",
      region: "center",
      height: 200,
      width: winsize.width * 0.38,
      frame: true,
      border: false,
      autoScroll: true,
      store: _quesArticleGridStore,
      selModel: ck_selection,
      viewConfig: {
        forceFit: true,
      },
      plugins: [__qaGridFilter],
      fields: ["articleId", "articleName"],
      colModel: new Ext.grid.ColumnModel({
        columns: [
          ck_selection,
          {
            header: "Choose Article",
            dataIndex: "articleName",
          },
        ],
      }),
      iconCls: "icon-grid",
      listeners: {
        afterrender: function () {
          {
            var me = this;
            _quesArticleGridStore.on("load", function () {
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
    return _questionArticleGrid;
  };
  var questionMasterDetailsView = function(){
    return new Ext.Panel({
      layout: "fit",
      region:"south",
      border: false,
      hideBorders: true,
      height: winsize.height * 0.3,
      id: "questionMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Question </th><td> {questionName} </td></tr>',
        '<tr><th width="40%">Question Content </th><td> {questionContent} </td></tr>',
        '<tr><th width="40%">Support Unit </th><td> {questionSuName} </td></tr>',
        '<tr><th width="40%">Chapter </th><td> {questionChapterName} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"questionStatus == '1'\">Active</tpl>",
        "<tpl if=\"questionStatus == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var questionGrid = function(){

    var _questionGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "articleName",
        },
        {
          type: "string",
          dataIndex: "articleChapterName",
        },
        {
          type: "string",
          dataIndex: "articleTopicName",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "articleStatus",
        },
      ],
    });
    _questionGridFilter.remote = true;
    _questionGridFilter.autoReload = true;
    var _questionGridStore = questionGridStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _questionGridStore,
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
      plugins: [_questionGridFilter],
      id: "questionMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Question",
          sortable: true,
          dataIndex: "questionName",
          tooltip: "Question",
          hideable: true,
        },
        {
          header: "Status",
          sortable: true,
          dataIndex: "status",
          tooltip: "Status",
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedQuestion,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("questionId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportMaster.Cache.questionId = ID;
            Ext.getCmp("questionMasterForm").hide();
            Application.SupportMaster.ViewModeQuestion(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _questionGridStore.load();
        },
      },
      tbar: [
        {
          text: "Create Question",
          tooltip: "Create Question",
          icon: "./resources/images/default/icons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.SupportMaster.questionAddEdit = "Add";
            var masterForm = Ext.getCmp("questionMasterForm").getForm();
            loadedForm = null;
            masterForm.reset();
            //Ext.getCmp("articleContent").focus(false, 100);
            /*<?php if (user_access("support_master", "saveQuestion")) { ?> */
            Ext.getCmp("questionEditBtn").hide();
            Ext.getCmp("questionSaveBtn").show();
            /*<?php } ?> */
            Ext.getCmp("questionCancelBtn").show();
            Ext.getCmp("gridQaArtGrid").hide();
            Ext.getCmp("questionMasterForm").show();
            Ext.getCmp("questionStatus").setValue(1);
            Ext.getCmp("questionMasterDetailsViewPanel").hide();
            Ext.getCmp("questionParentPanel").doLayout();
            Ext.getCmp("gridQuestionArticles")
              .getStore()
              .load({
                params: {
                  questionId: 0,
                  edit_status: 0,
                },
              });
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _questionGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_questionGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  var gridSelectionChangedQuestion = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("questionMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("questionMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.questionId;
      Application.SupportMaster.ViewModeQuestion(ID);
    }
  };
  var questionGridStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listQuestion",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "questionId",
          root: "data",
        },
        [
          "questionId","questionName",
          "questionSuName",
          "questionSuId","questionChapterId","questionChapterName",
          "status",
        ]
      ),
      sortInfo: {
        field: "questionId",
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
            Ext.getCmp("questionMasterGrid").getSelectionModel().selectRow(0);
          }
        },
      },
    });
    return _store;
  };
  var saveQuestions = function () {
    sq_articles = [];

    var store_fields = Ext.getCmp("gridQuestionArticles")
      .getSelectionModel()
      .getSelections();
    for (var i = 0; i < store_fields.length; i++) {
      sq_articles[i] = store_fields[i].data.articleId;
    }
    var ptId = Ext.getCmp("questionId").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("questionName").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("questionStatus").getValue())
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveQuestion",
        method: "POST",
        params: {
          sq_articles: sq_articles.toString(),
          questionId: Ext.getCmp("questionId").getValue(),
          questionSuId: Ext.getCmp("questionSuId").getValue(),
          questionName: Ext.getCmp("questionName").getValue(),
          questionStatus: Ext.getCmp("questionStatus").getValue(),
          questionContent: Ext.getCmp("questionContent").getValue(),
          isParentQuestion: Ext.getCmp("isParentQuestion").getValue(),
          isFeaturedQuestion: Ext.getCmp("isFeaturedQuestion").getValue(),
          parentQuestionId: Ext.getCmp("parentQuestionId").getValue(),
          questionChapterId: Ext.getCmp("questionChapterId").getValue()
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (
              Application.SupportMaster.questionAddEdit == "Add"
            ) {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("questionMasterGrid")
              );
              Ext.getCmp(
                "questionMasterGrid"
              ).store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("questionMasterGrid")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "questionMasterGrid"
                    );
                    var index = gridPanel.store.find(
                      "questionId",
                      ptId
                    );
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
              
            }
            Application.SupportMaster.questionAddEdit = "";
            Application.SupportMaster.ViewModeQuestion(
              tmp.data.questionId
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
  var questArticlListView = function(){
    var __qaArtiGridStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listQuestionarticles",
      method: "post",
      fields: ["articleId", "articleName", "checked"],
      remoteSort: true,
    });
    
    var __qaArtGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "articleName",
        },
      ],
    });
    __qaArtGridFilter.remote = true;
    __qaArtGridFilter.autoReload = true;
    var __qaArtiGrid = new Ext.grid.GridPanel({
      id: "gridQaArtGrid",
      region: "north",
      height: 200,
      width: winsize.width * 0.38,
      frame: true,
      hidden:true,
      border: false,
      autoScroll: true,
      store: __qaArtiGridStore,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true
    }),
      viewConfig: {
        forceFit: true,
      },
      plugins: [__qaArtGridFilter],
      fields: ["articleId", "articleName"],
      colModel: new Ext.grid.ColumnModel({
        columns: [
          {
            header: "Article",
            dataIndex: "articleName",
          },
        ],
      }),
      iconCls: "icon-grid",
      listeners: {
        afterrender: function () {
          
        },
      },
    });
    return __qaArtiGrid;
  };
  var masterPanelforSupportType = function (id) {
    var _mpanelforSupportType = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Support Benificiarys",
      id: id,
      iconCls: "my-icon448",
      items: [
        SupportTypeMainGrid(),
        new Ext.Panel({
          title: "Support Benificiary Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterSupportTypeParent",
          height: winsize.height * 0.6,
          items: [SupportTypeForm(), SupportTypeMasterDetailsView(),SUinSupportType()],
          buttonAlign: "right",
          fbar: [
            /*<?php if (user_access("support_master", "saveSupportTypes")) { ?> */ {
              text: "Edit",
              cls: "left-right-buttons",
              iconCls: "edit",
              id: "buttonMasterSupportTypeEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
              tabIndex: 4,
              handler: function () {
                var ID = Ext.getCmp(
                  "gridpanelMasterDataviewSupportTypesdata"
                )
                  .getSelectionModel()
                  .getSelections()[0].data.typeId;
                Application.SupportMaster.EditSupportTypesView(ID);
              },
            },
            {
              text: "Cancel",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonMasterSupportTypeCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewSupportTypesdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp(
                    "gridpanelMasterDataviewSupportTypesdata"
                  )
                    .getSelectionModel()
                    .getSelections()[0].data.typeId;
                  Application.SupportMaster.ViewSupportTypes(ID);
                }
              },
            },
            {
              text: "Save",
              tabIndex: 4,
              cls: "left-right-buttons",
              id: "buttonMasterSupportTypeSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveSupportTypes();
              },
            } /*<?php } ?> */,
          ],
        }),
      ],
    });
    return _mpanelforSupportType;
  };
  var SupportTypeForm = function () {
    var _supporttypesFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterSupportTypes",
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
          fieldLabel: "Support Benificiary",
          id: "typeName",
          name: "n[typeName]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 1,
          maxValue: 100,
          maxLength: 20,
        },stSupportUnitGrid(),{
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
              items: [mkCombo({
                type: STATUS_COMBO_DATA,
                value: "id",
                display: "text",
                name: "n[status]",
                fieldLabel: "Status",
                tabIndex: 2,
                emptyText: "Set status..",
                id: "typeStatus",
              })]
            }
          ]
        },
        {
          xtype: "textfield",
          id: "typeId",
          name: "n[typeId]",
          hidden: true,
        }      
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("typeId").getValue())) {
            var recordSelected = Ext.getCmp(
              "typeStatus"
            )
              .getStore()
              .getAt(0);
            Ext.getCmp("typeStatus").setValue(
              recordSelected.get("id")
            );
          }
        },
      },
    });
    return _supporttypesFormPanel;
  };
  var SupportTypeMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      region:"south",
      border: false,
      hideBorders: true,
      height: winsize.height * 0.3,
      id: "panelMasterSupportTypesDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {typeName} </td></tr>',
        '<tr><th width="40%">Status</th><td>',
        "<tpl if=\"typeStatus == '1'\">Active</tpl>",
        "<tpl if=\"typeStatus == '0'\">Inactive</tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var SupportTypesMasterStore = function () {
    var _supporttypesMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSupportTypes",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "typeId",
          root: "data",
        },
        [
          "typeId",
          "typeName",
          "status"
        ]
      ),
      sortInfo: {
        field: "typeId",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewSupportTypesdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewSupportTypesdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _supporttypesMasterStore;
  };
  var SupportTypeMainGrid = function () {
    var _supporttypesStore = SupportTypesMasterStore();
    var _supporttypesGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "typeName",
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
    _supporttypesGridFilter.remote = true;
    _supporttypesGridFilter.autoReload = true;
    var _supporttypesmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _supporttypesStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewSupportTypesdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _supporttypesGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Support Benificiary",
          dataIndex: "typeName",
          sortable: true,
          tooltip: "Support Benificiary",
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
        store: _supporttypesStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedsupporttypes,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("typeId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportMaster.Cache.typeId = ID;
            Ext.getCmp("formpanelMasterSupportTypes").hide();
            Application.SupportMaster.ViewSupportTypes(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _supporttypesStore.load();
        },
      },
      tbar: [
        {
          text: "Create Support Benificiary",
          hidden:true,
          tooltip: "Create Support Benificiary ",
          icon: "./resources/images/submenuicons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            Application.SupportMaster.SupportTypesAddEdit = "Add";
            var supporttypesForm = Ext.getCmp(
              "formpanelMasterSupportTypes"
            ).getForm();
            Ext.getCmp("panelMasterSupportTypeParent").setTitle(
              "Create Support Benificiary Details"
            );
            loadedForm = null;
            supporttypesForm.reset();
            Ext.getCmp("typeName").focus(false, 100);
            /*<?php if (user_access("support_master", "saveSupportTypes")) { ?> */
            Ext.getCmp("gridSupTypeSupUnitList").hide();
            Ext.getCmp("buttonMasterSupportTypeEdit").hide();
            Ext.getCmp("buttonMasterSupportTypeSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterSupportTypeCancel").show();
            Ext.getCmp("formpanelMasterSupportTypes").show();
            Ext.getCmp('typeStatus').setValue(1);
            Ext.getCmp("panelMasterSupportTypesDetailsView").hide();
            Ext.getCmp("panelMasterSupportTypeParent").doLayout();
            Ext.getCmp("gridSupTypeSupUnit")
              .getStore()
              .load({
                params: {
                  typeId: 0,
                  edit_status: 0,
                },
              });
          },
        },
      ],
    });
    return _supporttypesmaingridPanel;
  };
  var saveSupportTypes = function () {
    supTyp_supUnit = [];

    var store_fields = Ext.getCmp("gridSupTypeSupUnit")
      .getSelectionModel()
      .getSelections();
    for (var i = 0; i < store_fields.length; i++) {
      supTyp_supUnit[i] = store_fields[i].data.suId;
    }
    var ptId = Ext.getCmp("typeId").getValue();
    if (
      !Ext.isEmpty(Ext.getCmp("typeName").getValue()) &&
      !Ext.isEmpty(Ext.getCmp("typeStatus").getValue() && store_fields.length > 0)
    ) {
      Ext.Ajax.request({
        url: modURL + "&op=saveSupportTypes",
        method: "POST",
        params: {
          supTyp_supUnit: supTyp_supUnit.toString(),
          id: Ext.getCmp("typeId").getValue(),
          name: Ext.getCmp("typeName").getValue(),
          status: Ext.getCmp("typeStatus").getValue(),
          
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.message);
            if (
              Application.SupportMaster.SupportTypesAddEdit == "Add"
            ) {
              RECS_PER_PAGE = updateRecsPerPage(
                Ext.getCmp("gridpanelMasterDataviewSupportTypesdata")
              );
              Ext.getCmp("formpanelMasterSupportTypes").getForm().reset();
              Ext.getCmp(
                "gridpanelMasterDataviewSupportTypesdata"
              ).store.reload({
                params: {
                  start: 0,
                  limit: RECS_PER_PAGE,
                },
              });
            } else {
              Ext.getCmp("gridpanelMasterDataviewSupportTypesdata")
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp(
                      "gridpanelMasterDataviewSupportTypesdata"
                    );
                    var index = gridPanel.store.find(
                      "typeId",
                      ptId
                    );
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
              
            }
            Application.SupportMaster.SupportTypesAddEdit = "";
            Application.SupportMaster.ViewSupportTypes(
              tmp.data.typeId
            );
          } else if (tmp.success === true && tmp.valid === false) {
            Ext.Msg.alert("Notification.", tmp.message);
          } else if (tmp.success === true && tmp.img_valid === false) {
            Ext.Msg.alert("Notification.", tmp.message);
          } else {
            Ext.Msg.alert("Error", "Issue in saving data.");
          }
        },
        failure: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", "Issue in saving data.");
        },
      });
    } else {
      Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
    }
  };
  var gridSelectionChangedsupporttypes = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewSupportTypesdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewSupportTypesdata")
        .getSelectionModel()
        .getSelections()[0].data.business_categorye_id;
      Application.SupportMaster.ViewSupportTypes(ID);
    }
  };
  var stSupportUnitGrid = function () {
    var _supTypSupportUnitGridStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getSupportTypeSupportUnit",
      method: "post",
      fields: ["suId", "suName", "checked"],
      // root: 'data',
      remoteSort: true,
    });
    var ck_selection = new Ext.grid.CheckboxSelectionModel({
      multiSelect: true,
      checkOnly: true,
      listeners: {
        rowdeselect: function (sm, rowIndex, record) {
          var ind = supTyp_supUnit.indexOf(record.get("suId"));
          if (ind > -1) supTyp_supUnit.splice(ind, 1);
          record.set("checked", "false");
        },
        rowselect: function (sm, rowIndex, record) {
          var ind = supTyp_supUnit.indexOf(record.get("suId"));
          if (ind == -1)
            supTyp_supUnit.push(record.get("suId"));
          record.set("checked", "true");
        },
      },
    });
    var __sustGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "suName",
        },
      ],
    });
    __sustGridFilter.remote = true;
    __sustGridFilter.autoReload = true;
    var _albumProjectEventGrid = new Ext.grid.GridPanel({
      id: "gridSupTypeSupUnit",
      region: "center",
      height: 200,
      width: winsize.width * 0.38,
      frame: true,
      border: false,
      autoScroll: true,
      store: _supTypSupportUnitGridStore,
      selModel: ck_selection,
      viewConfig: {
        forceFit: true,
      },
      plugins: [__sustGridFilter],
      fields: ["suId", "suName"],
      colModel: new Ext.grid.ColumnModel({
        columns: [
          ck_selection,
          {
            header: "Support Unit",
            dataIndex: "suName",
          },
        ],
      }),
      iconCls: "icon-grid",
      listeners: {
        afterrender: function () {
          {
            var me = this;
            _supTypSupportUnitGridStore.on("load", function () {
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
  var SUinSupportType = function(){
    var _supTypSupportUnitGridStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listSupportTypeSupportUnit",
      method: "post",
      fields: ["suId", "suName", "checked"],
      remoteSort: true,
    });
    
    var __sustGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "suName",
        },
      ],
    });
    __sustGridFilter.remote = true;
    __sustGridFilter.autoReload = true;
    var _albumProjectEventGrid = new Ext.grid.GridPanel({
      id: "gridSupTypeSupUnitList",
      region: "north",
      height: 200,
      width: winsize.width * 0.38,
      frame: true,
      hidden:true,
      border: false,
      autoScroll: true,
      store: _supTypSupportUnitGridStore,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true
    }),
      viewConfig: {
        forceFit: true,
      },
      plugins: [__sustGridFilter],
      fields: ["suId", "suName"],
      colModel: new Ext.grid.ColumnModel({
        columns: [
          {
            header: "Support Unit",
            dataIndex: "suName",
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
  return {
    Cache: {},
    initSupportUnit: function () {
      var _supportunitPanelId = "panelMasterMainSupportUnit";
      var _masterPanelSupportUnit = Ext.getCmp(_supportunitPanelId);
      if (Ext.isEmpty(_masterPanelSupportUnit)) {
        _masterPanelSupportUnit =
          masterPanelforSupportUnit(_supportunitPanelId);
        Application.UI.addTab(_masterPanelSupportUnit);
        _masterPanelSupportUnit.doLayout();
      } else {
        Application.UI.addTab(_masterPanelSupportUnit);
      }
    },
    ViewSupportUnits: function () {
      var suId = arguments[0];
      /*<?php if (user_access("support_master", "saveSupportUnits")) { ?> */
      Ext.getCmp("buttonMasterSupportUnitEdit").show();
      Ext.getCmp("buttonMasterSupportUnitSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterSupportUnitCancel").hide();
      Ext.getCmp("formpanelMasterSupportUnits").hide();
      Ext.getCmp("panelMasterSupportUnitsDetailsView").show();
      Ext.getCmp("panelMasterSupportUnitParent").doLayout();
      Ext.getCmp("panelMasterSupportUnitParent").setTitle(
        "View Support Unit Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=supportunitsdetailsView",
        method: "POST",
        params: { suId: suId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelMasterSupportUnitsDetailsView"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterSupportUnitParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterSupportUnitParent").doLayout();
    },
    EditSupportUnitsView: function () {
      Application.SupportMaster.SupportUnitsAddEdit = "Edit";
      Ext.getCmp("panelMasterSupportUnitParent").doLayout();
      Ext.getCmp("panelMasterSupportUnitParent").setTitle(
        "Edit Support Unit Details"
      );
      Ext.getCmp("formpanelMasterSupportUnits").show();
      Ext.getCmp("panelMasterSupportUnitsDetailsView").hide();
      /*<?php if (user_access("support_master", "saveSupportUnits")) { ?> */
      Ext.getCmp("buttonMasterSupportUnitEdit").hide();
      Ext.getCmp("buttonMasterSupportUnitSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterSupportUnitCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var supportunitsForm = Ext.getCmp(
          "formpanelMasterSupportUnits"
        ).getForm();
        supportunitsForm.load({
          params: {
            suId: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=supportunits_form_load",
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
    initSupportChapter: function () {
      loadCount = 0;
      var _supportChapterPanelId = "panelSupportChapterinMaster";
      var _supportChapterPanel = Ext.getCmp(_supportChapterPanelId);
      if (Ext.isEmpty(_supportChapterPanel)) {
        _supportChapterPanel = supportChapterPanel(_supportChapterPanelId);
        Application.UI.addTab(_supportChapterPanel);
        _supportChapterPanel.doLayout();
      } else {
        Application.UI.addTab(_supportChapterPanel);
      }
    },
    EditSupportChapterView: function () {
      Application.SupportMaster.SupportChapterAddEdit = "Edit";
      Ext.getCmp("panelMasterSupportChapterParent").doLayout();
      Ext.getCmp("panelMasterSupportChapterParent").setTitle(
        "Edit Chapter Details"
      );
      Ext.getCmp("formpanelMasterSupportChapter").show();
      Ext.getCmp("xtemplateMasterSupportChapterViewDetails").hide();
      /*<?php if (user_access("support_master", "saveSupportChapter")) { ?> */
      Ext.getCmp("buttonMasterSupportChapterEdit").hide();
      Ext.getCmp("buttonMasterSupportChapterSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterSupportChapterCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("formpanelMasterSupportChapter").getForm();
        masterForm.load({
          params: {
            scId: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=support_chapter_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            Ext.getCmp('scUnitId').setRawValue(tmp.data.chapterSupportUnitName);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewSupportChapterMode: function () {
      var scId = arguments[0];
      /*<?php if (user_access("support_master", "saveSupportChapter")) { ?> */
      Ext.getCmp("buttonMasterSupportChapterEdit").show();
      Ext.getCmp("buttonMasterSupportChapterSave").hide();
      /*<?php } ?> */
      Ext.getCmp("panelMasterSupportChapterParent").setTitle(
        "View Chapter Details"
      );
      Ext.getCmp("buttonMasterSupportChapterCancel").hide();
      Ext.getCmp("formpanelMasterSupportChapter").hide();
      Ext.getCmp("xtemplateMasterSupportChapterViewDetails").show();
      Ext.getCmp("panelMasterSupportChapterParent").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=SupportChapterdetailsView",
        method: "POST",
        params: { scId: scId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "xtemplateMasterSupportChapterViewDetails"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterSupportChapterParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterSupportChapterParent").doLayout();
    },
    initSupportTopic: function () {
      loadCount = 0;
      var _topicPanelId = "panelidforTopic";
      var _masterPanelTopic = Ext.getCmp(_topicPanelId);
      if (Ext.isEmpty(_masterPanelTopic)) {
        _masterPanelTopic = masterPanelforTopic(_topicPanelId);
        Application.UI.addTab(_masterPanelTopic);
        _masterPanelTopic.doLayout();
      } else {
        Application.UI.addTab(_masterPanelTopic);
      }
    },
    topicEditView: function () {
      Application.SupportMaster.catAddEdit = "Edit";
      Ext.getCmp("TopicparentPanel").doLayout();
      Ext.getCmp("TopicparentPanel").setTitle("Edit Topic Details");
      Ext.getCmp("supportTopicMasterForm").show();
      Ext.getCmp("TopicMasterDetailsViewPanel").hide();
      /*<?php if (user_access("support_master", "saveTopic")) { ?> */
      Ext.getCmp("topicEditBtn").hide();
      Ext.getCmp("topicSaveBtn").show();
      /*<?php } ?> */
      Ext.getCmp("topicCancelBtn").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("supportTopicMasterForm").getForm();
        masterForm.load({
          params: {
            stId: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=support_topic_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            //pdtLoadedForm = [form, action.response.responseText];
            var tmp = Ext.decode(action.response.responseText);
            Ext.getCmp("topicChapter").setValue(tmp.data.topicChapter);
            Ext.getCmp("topicChapter").setRawValue(tmp.data.stChapterName);
            Ext.getCmp("topicChapter").getStore().baseParams.scUnitId =
              tmp.data.topicSupportUnit;
            Ext.getCmp("topicChapter").getStore().load();
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    supportTopicViewMode: function () {
      var topic_id = arguments[0];
      /*<?php if (user_access("support_master", "saveTopic")) { ?> */
      Ext.getCmp("topicEditBtn").show();
      Ext.getCmp("topicSaveBtn").hide();
      /*<?php } ?> */
      Ext.getCmp("topicCancelBtn").hide();
      Ext.getCmp("supportTopicMasterForm").hide();
      Ext.getCmp("TopicMasterDetailsViewPanel").show();
      Ext.getCmp("TopicparentPanel").setTitle("View Topic Details");
      Ext.getCmp("TopicparentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=TopicdetailsView",
        method: "POST",
        params: { topic_id: topic_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("TopicMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("TopicparentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("TopicparentPanel").doLayout();
      //Ext.getCmp("parent_category").getStore().load();
    },
    initSupportSubTopic: function () {
      loadCount = 0;
      var _panelId = "panelidforSubTopic";
      var _masterPanel = Ext.getCmp(_panelId);
      if (Ext.isEmpty(_masterPanel)) {
        _masterPanel = masterPanelforSubTopic(_panelId);
        Application.UI.addTab(_masterPanel);
        _masterPanel.doLayout();
      } else {
        Application.UI.addTab(_masterPanel);
      }
    },
    EditViewSubTopic: function () {
      Application.SupportMaster.subtopicAddEdit = "Edit";
      Ext.getCmp("subTopicparentPanel").doLayout();
      Ext.getCmp("subtopicMasterForm").setTitle("Edit Sub Topic details");
      Ext.getCmp("subtopicMasterForm").show();
      Ext.getCmp("subTopicMasterDetailsViewPanel").hide();
      /*<?php if (user_access("support_master", "saveSubTopic")) { ?> */
      Ext.getCmp("subTopicEditBtn").hide();
      Ext.getCmp("subTopicSaveBtn").show();
      /*<?php } ?> */
      Ext.getCmp("subTopicCancelBtn").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("subtopicMasterForm").getForm();
        masterForm.load({
          params: {
            subTopicId: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=subtopic_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            Ext.getCmp("subTopicScId").getStore().baseParams.subTopicSuId =
              tmp.data.subTopicSuId;
            Ext.getCmp("subTopicScId").getStore().load();
            Ext.getCmp("subTopicScId").setRawValue(tmp.data.subTopicScName);

            Ext.getCmp("mainTopicId").getStore().baseParams.stChapterId =
              tmp.data.subTopicScId;
            Ext.getCmp("mainTopicId").getStore().load();
            Ext.getCmp("mainTopicId").setRawValue(tmp.data.stName);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewModeSubTopic: function () {
      var subTopicId = arguments[0];
      /*<?php if (user_access("support_master", "saveSubTopic")) { ?> */
      Ext.getCmp("subTopicEditBtn").show();
      Ext.getCmp("subTopicSaveBtn").hide();
      /*<?php } ?> */
      Ext.getCmp("subTopicCancelBtn").hide();
      Ext.getCmp("subtopicMasterForm").hide();
      Ext.getCmp("subTopicMasterDetailsViewPanel").show();
      Ext.getCmp("subTopicparentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=subTopicdetailsView",
        method: "POST",
        params: { subTopicId: subTopicId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("subTopicMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("subTopicparentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("subTopicparentPanel").doLayout();
    },
    initSupportArticle: function () {
      loadCount = 0;
      var _panelId = "panelidforArticle";
      var _masterPanel = Ext.getCmp(_panelId);
      if (Ext.isEmpty(_masterPanel)) {
        _masterPanel = masterPanelforArticle(_panelId);
        Application.UI.addTab(_masterPanel);
        _masterPanel.doLayout();
      } else {
        Application.UI.addTab(_masterPanel);
      }
    },
    EditViewArticle: function () {
      Application.SupportMaster.subtopicAddEdit = "Edit";
      Ext.getCmp("articleParentPanel").doLayout();
      Ext.getCmp("articleMasterForm").setTitle("Edit Article details");
      Ext.getCmp("articleMasterForm").show();
      Ext.getCmp("articleMasterDetailsViewPanel").hide();
      /*<?php if (user_access("support_master", "saveArticle")) { ?> */
      Ext.getCmp("articleEditBtn").hide();
      Ext.getCmp("articleSaveBtn").show();
      /*<?php } ?> */
      Ext.getCmp("articleCancelBtn").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("articleMasterForm").getForm();
        masterForm.load({
          params: {
            articleId: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=article_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            
            Ext.getCmp('articleContent').setValue(tmp.data.articleContent);
            Ext.getCmp('articleSuId').setRawValue(tmp.data.articleSuName);
            Ext.getCmp("articleSuId").getStore().baseParams.suTypeId = tmp.data.articleStId;
            Ext.getCmp("articleSuId").getStore().load();
            Ext.getCmp('articleChapter').setRawValue(tmp.data.articleChapterName);
            Ext.getCmp("articleChapter").getStore().baseParams.scUnitId = tmp.data.articleSuId;
            Ext.getCmp("articleChapter").getStore().load();
            Ext.getCmp('articleTopic').setRawValue(tmp.data.articleTopicName);
            Ext.getCmp("articleTopic").getStore().baseParams.stChapterId = tmp.data.articleChapter;
            Ext.getCmp("articleTopic").getStore().load();
            Ext.getCmp('articleSubTopic').setRawValue(tmp.data.articleSubTopicName);
            Ext.getCmp("articleSubTopic").getStore().baseParams.mainTopicId = tmp.data.articleTopic;
            Ext.getCmp("articleSubTopic").getStore().load();
            
            
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    ViewModeArticle: function () {
      var articleId = arguments[0];
      /*<?php if (user_access("support_master", "saveArticle")) { ?> */
      Ext.getCmp("articleEditBtn").show();
      Ext.getCmp("articleSaveBtn").hide();
      /*<?php } ?> */
      Ext.getCmp("articleCancelBtn").hide();
      Ext.getCmp("articleMasterForm").hide();
      Ext.getCmp("articleMasterDetailsViewPanel").show();
      Ext.getCmp("articleParentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=articleDetailsView",
        method: "POST",
        params: { articleId: articleId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("articleMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("articleParentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("articleParentPanel").doLayout();
    },
    initSupportQuestion: function () {
      loadCount = 0;
      var _panelId = "panelidforQuestion";
      var _masterPanel = Ext.getCmp(_panelId);
      if (Ext.isEmpty(_masterPanel)) {
        _masterPanel = masterPanelforQuestion(_panelId);
        Application.UI.addTab(_masterPanel);
        _masterPanel.doLayout();
      } else {
        Application.UI.addTab(_masterPanel);
      }
    },EditViewQuestion: function () {
      var questionId=  arguments[0];
      Application.SupportMaster.questionAddEdit = "Edit";
      Ext.getCmp("questionParentPanel").doLayout();
      Ext.getCmp("questionMasterForm").setTitle("Edit Question details");
      Ext.getCmp("questionMasterForm").show();
      Ext.getCmp("questionMasterDetailsViewPanel").hide();
      /*<?php if (user_access("support_master", "saveQuestion")) { ?> */
      Ext.getCmp("questionEditBtn").hide();
      Ext.getCmp("questionSaveBtn").show();
      /*<?php } ?> */
      Ext.getCmp("questionCancelBtn").show();
      Ext.getCmp("gridQaArtGrid").hide();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var masterForm = Ext.getCmp("questionMasterForm").getForm();
        masterForm.load({
          params: {
            questionId: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=question_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            if(tmp.data.isParentQuestion == 1){
              Ext.getCmp('parentQuestionId').hide();
            }else{
              Ext.getCmp('parentQuestionId').show();
            }
            Ext.getCmp("questionSuId").setValue(tmp.data.questionSuId);
            Ext.getCmp("questionSuId").setRawValue(tmp.data.questionSuName);

            Ext.getCmp("questionChapterId").setValue(tmp.data.questionChapterId);
            Ext.getCmp("questionChapterId").setRawValue(tmp.data.questionChapterName);
            Ext.getCmp("questionChapterId").getStore().baseParams.scUnitId =
              tmp.data.questionSuId;
            Ext.getCmp("questionChapterId").getStore().load();

            Ext.getCmp('questionContent').setValue(tmp.data.questionContent);
            Ext.getCmp("gridQuestionArticles")
          .getStore()
          .load({
            params: {
              questionId: questionId,
              edit_status: 1,
            },
          });
          
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },ViewModeQuestion: function () {
      var questionId = arguments[0];
      /*<?php if (user_access("support_master", "saveQuestion")) { ?> */
      Ext.getCmp("questionEditBtn").show();
      Ext.getCmp("questionSaveBtn").hide();
      /*<?php } ?> */
      Ext.getCmp("questionCancelBtn").hide();
      Ext.getCmp("questionMasterForm").hide();
      Ext.getCmp("questionMasterDetailsViewPanel").show();
      Ext.getCmp("questionParentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=questionDetailsView",
        method: "POST",
        params: { questionId: questionId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("questionMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);

            Ext.getCmp("gridQaArtGrid").show();
          Ext.getCmp("gridQaArtGrid")
          .getStore()
          .load({
            params: {
              questionId: questionId
            },
          });
          }
          Ext.getCmp("questionParentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("questionParentPanel").doLayout();
    },initSupportType: function () {
      var _supporttypePanelId = "panelMasterMainSupportType";
      var _masterPanelSupportType = Ext.getCmp(_supporttypePanelId);
      if (Ext.isEmpty(_masterPanelSupportType)) {
        _masterPanelSupportType = masterPanelforSupportType(
          _supporttypePanelId
        );
        Application.UI.addTab(_masterPanelSupportType);
        _masterPanelSupportType.doLayout();
      } else {
        Application.UI.addTab(_masterPanelSupportType);
      }
    },
    ViewSupportTypes: function () {
      var typeId = arguments[0];
      /*<?php if (user_access("support_master", "saveSupportTypes")) { ?> */
      Ext.getCmp("buttonMasterSupportTypeEdit").show();
      Ext.getCmp("buttonMasterSupportTypeSave").hide();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterSupportTypeCancel").hide();
      Ext.getCmp("formpanelMasterSupportTypes").hide();
      Ext.getCmp("panelMasterSupportTypesDetailsView").show();
      Ext.getCmp("panelMasterSupportTypeParent").doLayout();
      Ext.getCmp("panelMasterSupportTypeParent").setTitle(
        "View Support Benificiary Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=supporttypesdetailsView",
        method: "POST",
        params: { typeId: typeId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelMasterSupportTypesDetailsView"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelMasterSupportTypeParent").doLayout();
          Ext.getCmp("gridSupTypeSupUnitList").show();
          Ext.getCmp("gridSupTypeSupUnitList")
          .getStore()
          .load({
            params: {
              typeId: typeId
            },
          });
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterSupportTypeParent").doLayout();
    },
    EditSupportTypesView: function () {
      Application.SupportMaster.SupportTypesAddEdit = "Edit";
      Ext.getCmp("panelMasterSupportTypeParent").doLayout();
      Ext.getCmp("panelMasterSupportTypeParent").setTitle(
        "Edit Support Benificiary Details"
      );
      Ext.getCmp("formpanelMasterSupportTypes").show();
      Ext.getCmp("panelMasterSupportTypesDetailsView").hide();
      /*<?php if (user_access("support_master", "saveSupportTypes")) { ?> */
      Ext.getCmp("buttonMasterSupportTypeEdit").hide();
      Ext.getCmp("buttonMasterSupportTypeSave").show();
      /*<?php } ?> */
      Ext.getCmp("buttonMasterSupportTypeCancel").show();
      Ext.getCmp("gridSupTypeSupUnitList").hide();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var supporttypesForm = Ext.getCmp(
          "formpanelMasterSupportTypes"
        ).getForm();
        supporttypesForm.load({
          params: {
            typeId: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=supporttypes_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
        Ext.getCmp("gridSupTypeSupUnit")
          .getStore()
          .load({
            params: {
              typeId: arguments[0],
              edit_status: 1,
            },
          });
      }
    },
  };
})();
