Application.BusinessDivision = (function () {
    var RECS_PER_PAGE = 21;
    var modURL = "?module=business_division";
    var winsize = Ext.getBody().getViewSize();
    var loadCount;
    var onGridResize = function (cmp) {
      RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionChangedZones = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelMasterDataviewZonedata")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelMasterDataviewZonedata")
            .getSelectionModel()
            .getSelections()[0].data.zone_id;
          Application.BusinessDivision.ViewZones(ID);
        }
      };
    var masterPanelforZone = function (id) {
        var _mpanelforZone = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "Zones",
          id: id,
          iconCls: "my-icon448",
          items: [
            ZoneMainGrid(),
            new Ext.Panel({
              title: "Zone Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              autoScroll: true,
              cls: "left_side_panel",
              id: "panelMasterZoneParent",
              height: winsize.height * 0.6,
              items: [ZoneForm(), ZoneMasterDetailsView()],
              buttonAlign: "right",
              fbar: [
                /*<?php if (user_access("business_division", "saveZones")) { ?> */ {
                  text: "Edit",
                  cls: "left-right-buttons",
                  iconCls: "edit",
                  id: "buttonMasterZoneEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
                  tabIndex: 4,
                  hidden:true,
                  handler: function () {
                    var ID = Ext.getCmp("gridpanelMasterDataviewZonedata")
                      .getSelectionModel()
                      .getSelections()[0].data.zone_id;
                    Application.BusinessDivision.EditZonesView(ID);
                  },
                },
                {
                  text: "Cancel",
                  tabIndex: 5,
                  cls: "left-right-buttons",
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  id: "buttonMasterZoneCancel",
                  hidden: true,
                  handler: function () {
                    if (
                      !Ext.isEmpty(
                        Ext.getCmp("gridpanelMasterDataviewZonedata")
                          .getSelectionModel()
                          .getSelections()
                      )
                    ) {
                      var ID = Ext.getCmp(
                        "gridpanelMasterDataviewZonedata"
                      )
                        .getSelectionModel()
                        .getSelections()[0].data.zone_id;
                      Application.BusinessDivision.ViewZones(ID);
                    }
                  },
                },
                {
                  text: "Save",
                  tabIndex: 4,
                  cls: "left-right-buttons",
                  id: "buttonMasterZoneSave",
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  hidden: true,
                  handler: function () {
                    saveZones();
                  },
                } /*<?php } ?> */,
              ],
            }),
          ],
        });
        return _mpanelforZone;
      };
      var ZoneForm = function () {
        var _zonesFormPanel = new Ext.form.FormPanel({
          id: "formpanelMasterZone",
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
              fieldLabel: "Zone",
              id: "zone_name",
              name: "n[zone_name]",
              anchor: "98%",
              allowBlank: false,
              tabIndex: 1,
              maxLength: 100,
            },
            {
              xtype: "textfield",
              id: "zone_id",
              name: "n[zone_id]",
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
              id: "comboZoneStatus",
            }),
          ],
          listeners: {
            afterrender: function () {
              if (Ext.isEmpty(Ext.getCmp("zone_id").getValue())) {
                var recordSelected = Ext.getCmp("comboZoneStatus")
                  .getStore()
                  .getAt(0);
                Ext.getCmp("comboZoneStatus").setValue(
                  recordSelected.get("id")
                );
              }
            },
          },
        });
        return _zonesFormPanel;
      };
      var ZoneMasterDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          border: false,
          hideBorders: true,
          autoHeight: true,
          id: "panelMasterZoneDetailsView",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Name </th><td>  {zone_name} </td></tr>',
            '<tr><th width="40%">Status</th><td>',
            "<tpl if=\"status == '1'\">Active</tpl>",
            "<tpl if=\"status == '0'\">Inactive</tpl>",
            "</td></tr>",
            "</table>",
            "</div>"
          ),
        });
      };
      var ZoneMasterStore = function () {
        var _zonesMasterStore = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listZone",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "zone_id",
              root: "data",
            },
            ["zone_id", "zone_name", "status"]
          ),
          sortInfo: {
            field: "zone_id",
            direction: "DESC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              Ext.getCmp("gridpanelMasterDataviewZonedata")
                .getView()
                .refresh();
              Ext.getCmp("gridpanelMasterDataviewZonedata")
                .getSelectionModel()
                .selectRow(0);
            },
          },
        });
        return _zonesMasterStore;
      };
      var ZoneMainGrid = function () {
        var _zonesStore = ZoneMasterStore();
        var _zonesGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "zone_name",
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
        _zonesGridFilter.remote = true;
        _zonesGridFilter.autoReload = true;
        var _zonesmaingridPanel = new Ext.grid.GridPanel({
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          loadMask: true,
          store: _zonesStore,
          iconCls: "money",
          id: "gridpanelMasterDataviewZonedata",
          view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),
          plugins: [new Ext.ux.grid.GroupSummary(), _zonesGridFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Zone",
              dataIndex: "zone_name",
              sortable: true,
              tooltip: "Zone",
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
            store: _zonesStore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedZones,
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("zone_id");
              if (!Ext.isEmpty(ID)) {
                Application.BusinessDivision.Cache.zone_id = ID;
                Ext.getCmp("formpanelMasterZone").hide();
                Application.BusinessDivision.ViewZones(ID);
              }
            },
            resize: onGridResize,
            afterrender: function () {
              _zonesStore.load();
            },
          },
          tbar: [
            {
              text: "Create Zone",
              tooltip: "Create Zone ",
              icon: "./resources/images/submenuicons/add.png",
              iconCls: "my-icon1",
              handler: function () {
                Application.BusinessDivision.ZonesAddEdit = "Add";
                var zonesForm = Ext.getCmp(
                  "formpanelMasterZone"
                ).getForm();
                Ext.getCmp("panelMasterZoneParent").setTitle(
                  "Create Zone Details"
                );
                loadedForm = null;
                zonesForm.reset();
                Ext.getCmp("zone_name").focus(false, 100);
                /*<?php if (user_access("business_division", "saveZones")) { ?> */
                Ext.getCmp("buttonMasterZoneEdit").hide();
                Ext.getCmp("buttonMasterZoneSave").show();
                /*<?php } ?> */
                Ext.getCmp("buttonMasterZoneCancel").show();
                Ext.getCmp("formpanelMasterZone").show();
                Ext.getCmp("comboZoneStatus").setValue(1)
                Ext.getCmp("panelMasterZoneDetailsView").hide();
                Ext.getCmp("panelMasterZoneParent").doLayout();
              },
            },
          ],
        });
        return _zonesmaingridPanel;
      };
      var saveZones = function () {
        var ptId = Ext.getCmp("zone_id").getValue();
        if (
          !Ext.isEmpty(Ext.getCmp("zone_name").getValue()) &&
          !Ext.isEmpty(Ext.getCmp("comboZoneStatus").getValue())
        ) {
          Ext.Ajax.request({
            url: modURL + "&op=saveZones",
            method: "POST",
            params: {
              id: Ext.getCmp("zone_id").getValue(),
              name: Ext.getCmp("zone_name").getValue(),
              status: Ext.getCmp("comboZoneStatus").getValue(),
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (Application.BusinessDivision.ZonesAddEdit == "Add") {
                  RECS_PER_PAGE = updateRecsPerPage(
                    Ext.getCmp("gridpanelMasterDataviewZonedata")
                  );
                  Ext.getCmp("formpanelMasterZone").getForm().reset();
                  Ext.getCmp(
                    "gridpanelMasterDataviewZonedata"
                  ).store.reload({
                    params: {
                      start: 0,
                      limit: RECS_PER_PAGE,
                    },
                  });
                } else {
                  Ext.getCmp("gridpanelMasterDataviewZonedata")
                    .getStore()
                    .load({
                      callback: function (record, options, success) {
                        var gridPanel = Ext.getCmp(
                          "gridpanelMasterDataviewZonedata"
                        );
                        var index = gridPanel.store.find("zone_id", ptId);
                        gridPanel.getSelectionModel().selectRow(index);
                      },
                    });
                }
                Application.BusinessDivision.ZonesAddEdit = "";
                Application.BusinessDivision.ViewZones(
                  tmp.data.zone_id
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
      var gridSelectionChangedRegions = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelMasterDataviewRegiondata")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelMasterDataviewRegiondata")
            .getSelectionModel()
            .getSelections()[0].data.region_id;
          Application.BusinessDivision.ViewRegions(ID);
        }
      };
    var masterPanelforRegion = function (id) {
        var _mpanelforRegion = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "Regions",
          id: id,
          iconCls: "my-icon448",
          items: [
            RegionMainGrid(),
            new Ext.Panel({
              title: "Region Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              autoScroll: true,
              cls: "left_side_panel",
              id: "panelMasterRegionParent",
              height: winsize.height * 0.6,
              items: [RegionForm(), RegionMasterDetailsView()],
              buttonAlign: "right",
              fbar: [
                /*<?php if (user_access("business_division", "isSuperAdmin")) { ?> */ {
                  text: "Edit",
                  cls: "left-right-buttons",
                  iconCls: "edit",
                  id: "buttonMasterRegionEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
                  tabIndex: 4,
                  hidden:true,
                  handler: function () {
                    var ID = Ext.getCmp("gridpanelMasterDataviewRegiondata")
                      .getSelectionModel()
                      .getSelections()[0].data.region_id;
                    Application.BusinessDivision.EditRegionsView(ID);
                  },
                },
                {
                  text: "Cancel",
                  tabIndex: 5,
                  cls: "left-right-buttons",
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  id: "buttonMasterRegionCancel",
                  hidden: true,
                  handler: function () {
                    if (
                      !Ext.isEmpty(
                        Ext.getCmp("gridpanelMasterDataviewRegiondata")
                          .getSelectionModel()
                          .getSelections()
                      )
                    ) {
                      var ID = Ext.getCmp(
                        "gridpanelMasterDataviewRegiondata"
                      )
                        .getSelectionModel()
                        .getSelections()[0].data.region_id;
                      Application.BusinessDivision.ViewRegions(ID);
                    }
                  },
                },
                {
                  text: "Save",
                  tabIndex: 4,
                  cls: "left-right-buttons",
                  id: "buttonMasterRegionSave",
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  hidden: true,
                  handler: function () {
                    saveRegions();
                  },
                } /*<?php } ?> */,
              ],
            }),
          ],
        });
        return _mpanelforRegion;
      };
      var zoneComboStorefn = function () {
        var store = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=getZones",
          method: "post",
          fields: ["id", "name"],
          root: "data",
        });
        return store;
      };
      var RegionForm = function () {
        var zoneComboStore = zoneComboStorefn();
        var _regionsFormPanel = new Ext.form.FormPanel({
          id: "formpanelMasterRegion",
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
              fieldLabel: "Region",
              id: "region_name",
              name: "n[region_name]",
              anchor: "98%",
              allowBlank: false,
              tabIndex: 1,
              maxLength: 100,
            },
            {
              xtype: "textfield",
              id: "region_id",
              name: "n[region_id]",
              hidden: true,
            },{
                xtype: "combo",
                store: zoneComboStore,
                mode: "local",
                id: "regionZoneId",
                allowBlank: true,
                fieldLabel: "Zone",
                hiddenName: "n[regionZoneId]",
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
                    var value = Ext.getCmp("regionZoneId").getValue();
                  },
                },
              },
            mkCombo({
              type: STATUS_COMBO_DATA,
              value: "id",
              display: "text",
              name: "n[status]",
              fieldLabel: "Status",
              tabIndex: 2,
              emptyText: "Set status..",
              id: "comboRegionStatus",
            }),
          ],
          listeners: {
            afterrender: function () {
              if (Ext.isEmpty(Ext.getCmp("region_id").getValue())) {
                var recordSelected = Ext.getCmp("comboRegionStatus")
                  .getStore()
                  .getAt(0);
                Ext.getCmp("comboRegionStatus").setValue(
                  recordSelected.get("id")
                );
              }
            },
          },
        });
        return _regionsFormPanel;
      };
      var RegionMasterDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          border: false,
          hideBorders: true,
          autoHeight: true,
          id: "panelMasterRegionDetailsView",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Name </th><td>  {region_name} </td></tr>',
            '<tr><th width="40%">Zone </th><td>  {zone_name} </td></tr>',
            '<tr><th width="40%">Status</th><td>',
            "<tpl if=\"status == '1'\">Active</tpl>",
            "<tpl if=\"status == '0'\">Inactive</tpl>",
            "</td></tr>",
            "</table>",
            "</div>"
          ),
        });
      };
      var RegionMasterStore = function () {
        var _regionsMasterStore = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listRegion",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "region_id",
              root: "data",
            },
            ["region_id", "region_name", "status","zone_name"]
          ),
          sortInfo: {
            field: "region_id",
            direction: "DESC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              Ext.getCmp("gridpanelMasterDataviewRegiondata")
                .getView()
                .refresh();
              Ext.getCmp("gridpanelMasterDataviewRegiondata")
                .getSelectionModel()
                .selectRow(0);
            },
          },
        });
        return _regionsMasterStore;
      };
      var RegionMainGrid = function () {
        var _regionsStore = RegionMasterStore();
        var _regionsGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "region_name",
            },
            {
              type: "string",
              dataIndex: "zone_name",
            },
            {
              type: "list",
              options: ["Active", "Inactive"],
              phpMode: true,
              dataIndex: "status",
            },
          ],
        });
        _regionsGridFilter.remote = true;
        _regionsGridFilter.autoReload = true;
        var _regionsmaingridPanel = new Ext.grid.GridPanel({
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          loadMask: true,
          store: _regionsStore,
          iconCls: "money",
          id: "gridpanelMasterDataviewRegiondata",
          view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),
          plugins: [new Ext.ux.grid.GroupSummary(), _regionsGridFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Region",
              dataIndex: "region_name",
              sortable: true,
              tooltip: "Region",
              hideable: false,
            },{
              header: "Zone",
              dataIndex: "zone_name",
              sortable: true,
              tooltip: "Zone",
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
            store: _regionsStore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedRegions,
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("region_id");
              if (!Ext.isEmpty(ID)) {
                Application.BusinessDivision.Cache.region_id = ID;
                Ext.getCmp("formpanelMasterRegion").hide();
                Application.BusinessDivision.ViewRegions(ID);
              }
            },
            resize: onGridResize,
            afterrender: function () {
              _regionsStore.load();
            },
          },
          tbar: [
            {
              text: "Create Region",
              tooltip: "Create Region ",
              icon: "./resources/images/submenuicons/add.png",
              iconCls: "my-icon1",
              handler: function () {
                Application.BusinessDivision.RegionsAddEdit = "Add";
                var regionsForm = Ext.getCmp(
                  "formpanelMasterRegion"
                ).getForm();
                Ext.getCmp("panelMasterRegionParent").setTitle(
                  "Create Region Details"
                );
                loadedForm = null;
                regionsForm.reset();
                Ext.getCmp("region_name").focus(false, 100);
                /*<?php if (user_access("business_division", "isSuperAdmin")) { ?> */
                Ext.getCmp("buttonMasterRegionEdit").hide();
                Ext.getCmp("buttonMasterRegionSave").show();
                /*<?php } ?> */
                Ext.getCmp("buttonMasterRegionCancel").show();
                Ext.getCmp("formpanelMasterRegion").show();
                Ext.getCmp("comboRegionStatus").setValue(1)
                Ext.getCmp("panelMasterRegionDetailsView").hide();
                Ext.getCmp("panelMasterRegionParent").doLayout();
              },
            },
          ],
        });
        return _regionsmaingridPanel;
      };
      var saveRegions = function () {
        var ptId = Ext.getCmp("region_id").getValue();
        if (
          !Ext.isEmpty(Ext.getCmp("region_name").getValue()) &&
          !Ext.isEmpty(Ext.getCmp("comboRegionStatus").getValue())
        ) {
          Ext.Ajax.request({
            url: modURL + "&op=saveRegions",
            method: "POST",
            params: {
              id: Ext.getCmp("region_id").getValue(),
              name: Ext.getCmp("region_name").getValue(),
              parentId: Ext.getCmp("regionZoneId").getValue(),
              status: Ext.getCmp("comboRegionStatus").getValue(),
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (Application.BusinessDivision.RegionsAddEdit == "Add") {
                  RECS_PER_PAGE = updateRecsPerPage(
                    Ext.getCmp("gridpanelMasterDataviewRegiondata")
                  );
                  Ext.getCmp("formpanelMasterRegion").getForm().reset();
                  Ext.getCmp(
                    "gridpanelMasterDataviewRegiondata"
                  ).store.reload({
                    params: {
                      start: 0,
                      limit: RECS_PER_PAGE,
                    },
                  });
                } else {
                  Ext.getCmp("gridpanelMasterDataviewRegiondata")
                    .getStore()
                    .load({
                      callback: function (record, options, success) {
                        var gridPanel = Ext.getCmp(
                          "gridpanelMasterDataviewRegiondata"
                        );
                        var index = gridPanel.store.find("region_id", ptId);
                        gridPanel.getSelectionModel().selectRow(index);
                      },
                    });
                }
                Application.BusinessDivision.RegionsAddEdit = "";
                Application.BusinessDivision.ViewRegions(
                  tmp.data.region_id
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
      var gridSelectionChangedTerritorys = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelMasterDataviewTerritorydata")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelMasterDataviewTerritorydata")
            .getSelectionModel()
            .getSelections()[0].data.territory_id;
          Application.BusinessDivision.ViewTerritorys(ID);
        }
      };
    var masterPanelforTerritory = function (id) {
        var _mpanelforTerritory = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "Territory",
          id: id,
          iconCls: "my-icon448",
          items: [
            TerritoryMainGrid(),
            new Ext.Panel({
              title: "Territory Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              autoScroll: true,
              cls: "left_side_panel",
              id: "panelMasterTerritoryParent",
              height: winsize.height * 0.6,
              items: [TerritoryForm(), TerritoryMasterDetailsView()],
              buttonAlign: "right",
              fbar: [
                /*<?php if (user_access("business_division", "isSuperAdmin")) { ?> */ {
                  text: "Edit",
                  cls: "left-right-buttons",
                  iconCls: "edit",
                  id: "buttonMasterTerritoryEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
                  tabIndex: 4,
                  hidden:true,
                  handler: function () {
                    var ID = Ext.getCmp("gridpanelMasterDataviewTerritorydata")
                      .getSelectionModel()
                      .getSelections()[0].data.territory_id;
                    Application.BusinessDivision.EditTerritorysView(ID);
                  },
                },
                {
                  text: "Cancel",
                  tabIndex: 5,
                  cls: "left-right-buttons",
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  id: "buttonMasterTerritoryCancel",
                  hidden: true,
                  handler: function () {
                    if (
                      !Ext.isEmpty(
                        Ext.getCmp("gridpanelMasterDataviewTerritorydata")
                          .getSelectionModel()
                          .getSelections()
                      )
                    ) {
                      var ID = Ext.getCmp(
                        "gridpanelMasterDataviewTerritorydata"
                      )
                        .getSelectionModel()
                        .getSelections()[0].data.territory_id;
                      Application.BusinessDivision.ViewTerritorys(ID);
                    }
                  },
                },
                {
                  text: "Save",
                  tabIndex: 4,
                  cls: "left-right-buttons",
                  id: "buttonMasterTerritorySave",
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  hidden: true,
                  handler: function () {
                    saveTerritorys();
                  },
                } /*<?php } ?> */,
              ],
            }),
          ],
        });
        return _mpanelforTerritory;
      };
      var zoneComboStorefn = function () {
        var store = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=getZones",
          method: "post",
          fields: ["id", "name"],
          root: "data",
        });
        return store;
      };
      var regionComboStorefn = function () {
        var store = new Ext.data.JsonStore({
          url: modURL + "&op=getRegion",
          method: "post",
          fields: ["id", "name"],
          root: "data",
        });
        return store;
      };
      var territoryComboStorefn = function () {
        var store = new Ext.data.JsonStore({
          url: modURL + "&op=getTerritory",
          method: "post",
          fields: ["id", "name"],
          root: "data",
        });
        return store;
      };
      var TerritoryForm = function () {
        var zoneComboStore = zoneComboStorefn();
        var regionComboStore = regionComboStorefn();
        var _territorysFormPanel = new Ext.form.FormPanel({
          id: "formpanelMasterTerritory",
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
              fieldLabel: "Territory",
              id: "territory_name",
              name: "n[territory_name]",
              anchor: "98%",
              allowBlank: false,
              tabIndex: 1,
              maxLength: 100,
            },
            {
              xtype: "textfield",
              id: "territory_id",
              name: "n[territory_id]",
              hidden: true,
            },{
                xtype: "combo",
                store: zoneComboStore,
                mode: "local",
                id: "territoryZoneId",
                allowBlank: true,
                fieldLabel: "Zone",
                hiddenName: "n[territoryZoneId]",
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
                    var value = Ext.getCmp("territoryRegionId").getValue();
                    regionComboStore.baseParams.zoneId = this.value;
                    regionComboStore.load();
                  },
                },
              },
              {
                xtype: "combo",
                store: regionComboStore,
                mode: "local",
                id: "territoryRegionId",
                allowBlank: true,
                fieldLabel: "Region",
                hiddenName: "n[territoryRegionId]",
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
              },
            mkCombo({
              type: STATUS_COMBO_DATA,
              value: "id",
              display: "text",
              name: "n[status]",
              fieldLabel: "Status",
              tabIndex: 2,
              emptyText: "Set status..",
              id: "comboTerritoryStatus",
            }),
          ],
          listeners: {
            afterrender: function () {
              if (Ext.isEmpty(Ext.getCmp("territory_id").getValue())) {
                var recordSelected = Ext.getCmp("comboTerritoryStatus")
                  .getStore()
                  .getAt(0);
                Ext.getCmp("comboTerritoryStatus").setValue(
                  recordSelected.get("id")
                );
              }
            },
          },
        });
        return _territorysFormPanel;
      };
      var TerritoryMasterDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          border: false,
          hideBorders: true,
          autoHeight: true,
          id: "panelMasterTerritoryDetailsView",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Zone </th><td>  {zone_name} </td></tr>',
            '<tr><th width="40%">Region </th><td>  {region_name} </td></tr>',
            '<tr><th width="40%">Name </th><td>  {territory_name} </td></tr>',            
            '<tr><th width="40%">Status</th><td>',
            "<tpl if=\"comboTerritoryStatus == '1'\">Active</tpl>",
            "<tpl if=\"comboTerritoryStatus == '0'\">Inactive</tpl>",
            "</td></tr>",
            "</table>",
            "</div>"
          ),
        });
      };
      var TerritoryMasterStore = function () {
        var _territorysMasterStore = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listTerritory",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "territory_id",
              root: "data",
            },
            ["territory_id", "territory_name", "status","zone_name","region_name"]
          ),
          sortInfo: {
            field: "territory_id",
            direction: "DESC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              Ext.getCmp("gridpanelMasterDataviewTerritorydata")
                .getView()
                .refresh();
              Ext.getCmp("gridpanelMasterDataviewTerritorydata")
                .getSelectionModel()
                .selectRow(0);
            },
          },
        });
        return _territorysMasterStore;
      };
      var TerritoryMainGrid = function () {
        var _territorysStore = TerritoryMasterStore();
        var _territorysGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "territory_name",
            },
            {
              type: "string",
              dataIndex: "zone_name",
            },{
              type: "string",
              dataIndex: "region_name",
            },
            {
              type: "list",
              options: ["Active", "Inactive"],
              phpMode: true,
              dataIndex: "status",
            },
          ],
        });
        _territorysGridFilter.remote = true;
        _territorysGridFilter.autoReload = true;
        var _territorysmaingridPanel = new Ext.grid.GridPanel({
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          loadMask: true,
          store: _territorysStore,
          iconCls: "money",
          id: "gridpanelMasterDataviewTerritorydata",
          view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),
          plugins: [new Ext.ux.grid.GroupSummary(), _territorysGridFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Territory",
              dataIndex: "territory_name",
              sortable: true,
              tooltip: "Territory",
              hideable: false,
            },{
              header: "Region",
              dataIndex: "region_name",
              sortable: true,
              tooltip: "Region",
              hideable: false,
            },{
              header: "Zone",
              dataIndex: "zone_name",
              sortable: true,
              tooltip: "Zone",
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
            store: _territorysStore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedTerritorys,
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("territory_id");
              if (!Ext.isEmpty(ID)) {
                Application.BusinessDivision.Cache.territory_id = ID;
                Ext.getCmp("formpanelMasterTerritory").hide();
                Application.BusinessDivision.ViewTerritorys(ID);
              }
            },
            resize: onGridResize,
            afterrender: function () {
              _territorysStore.load();
            },
          },
          tbar: [
            {
              text: "Create Territory",
              tooltip: "Create Territory ",
              icon: "./resources/images/submenuicons/add.png",
              iconCls: "my-icon1",
              handler: function () {
                Application.BusinessDivision.TerritorysAddEdit = "Add";
                var territorysForm = Ext.getCmp(
                  "formpanelMasterTerritory"
                ).getForm();
                Ext.getCmp("panelMasterTerritoryParent").setTitle(
                  "Create Territory Details"
                );
                loadedForm = null;
                territorysForm.reset();
                Ext.getCmp("territory_name").focus(false, 100);
                /*<?php if (user_access("business_division", "isSuperAdmin")) { ?> */
                Ext.getCmp("buttonMasterTerritoryEdit").hide();
                Ext.getCmp("buttonMasterTerritorySave").show();
                /*<?php } ?> */
                Ext.getCmp("buttonMasterTerritoryCancel").show();
                Ext.getCmp("formpanelMasterTerritory").show();
                Ext.getCmp("comboTerritoryStatus").setValue(1)
                Ext.getCmp("panelMasterTerritoryDetailsView").hide();
                Ext.getCmp("panelMasterTerritoryParent").doLayout();
              },
            },
          ],
        });
        return _territorysmaingridPanel;
      };
      var saveTerritorys = function () {
        var ptId = Ext.getCmp("territory_id").getValue();
        if (
          !Ext.isEmpty(Ext.getCmp("territory_name").getValue()) &&
          !Ext.isEmpty(Ext.getCmp("comboTerritoryStatus").getValue())
        ) {
          Ext.Ajax.request({
            url: modURL + "&op=saveTerritorys",
            method: "POST",
            params: {
              id: Ext.getCmp("territory_id").getValue(),
              name: Ext.getCmp("territory_name").getValue(),
              parentId: Ext.getCmp("territoryRegionId").getValue(),
              status: Ext.getCmp("comboTerritoryStatus").getValue(),
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (Application.BusinessDivision.TerritorysAddEdit == "Add") {
                  RECS_PER_PAGE = updateRecsPerPage(
                    Ext.getCmp("gridpanelMasterDataviewTerritorydata")
                  );
                  Ext.getCmp("formpanelMasterTerritory").getForm().reset();
                  Ext.getCmp(
                    "gridpanelMasterDataviewTerritorydata"
                  ).store.reload({
                    params: {
                      start: 0,
                      limit: RECS_PER_PAGE,
                    },
                  });
                } else {
                  Ext.getCmp("gridpanelMasterDataviewTerritorydata")
                    .getStore()
                    .load({
                      callback: function (record, options, success) {
                        var gridPanel = Ext.getCmp(
                          "gridpanelMasterDataviewTerritorydata"
                        );
                        var index = gridPanel.store.find("territory_id", ptId);
                        gridPanel.getSelectionModel().selectRow(index);
                      },
                    });
                }
                Application.BusinessDivision.TerritorysAddEdit = "";
                Application.BusinessDivision.ViewTerritorys(
                  tmp.data.territory_id
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
      var gridSelectionChangedDepartments = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelMasterDataviewDepartmentdata")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelMasterDataviewDepartmentdata")
            .getSelectionModel()
            .getSelections()[0].data.department_id;
          Application.BusinessDivision.ViewDepartments(ID);
        }
      };
    var masterPanelforDepartment = function (id) {
        var _mpanelforDepartment = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "Departments",
          id: id,
          iconCls: "my-icon448",
          items: [
            DepartmentMainGrid(),
            new Ext.Panel({
              title: "Department Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              autoScroll: true,
              cls: "left_side_panel",
              id: "panelMasterDepartmentParent",
              height: winsize.height * 0.6,
              items: [DepartmentForm(), DepartmentMasterDetailsView()],
              buttonAlign: "right",
              fbar: [
                /*<?php if (user_access("business_division", "saveDepartments")) { ?> */ {
                  text: "Edit",
                  cls: "left-right-buttons",
                  iconCls: "edit",
                  id: "buttonMasterDepartmentEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
                  tabIndex: 4,
                  hidden:true,
                  handler: function () {
                    var ID = Ext.getCmp("gridpanelMasterDataviewDepartmentdata")
                      .getSelectionModel()
                      .getSelections()[0].data.department_id;
                    Application.BusinessDivision.EditDepartmentsView(ID);
                  },
                },
                {
                  text: "Cancel",
                  tabIndex: 5,
                  cls: "left-right-buttons",
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  id: "buttonMasterDepartmentCancel",
                  hidden: true,
                  handler: function () {
                    if (
                      !Ext.isEmpty(
                        Ext.getCmp("gridpanelMasterDataviewDepartmentdata")
                          .getSelectionModel()
                          .getSelections()
                      )
                    ) {
                      var ID = Ext.getCmp(
                        "gridpanelMasterDataviewDepartmentdata"
                      )
                        .getSelectionModel()
                        .getSelections()[0].data.department_id;
                      Application.BusinessDivision.ViewDepartments(ID);
                    }
                  },
                },
                {
                  text: "Save",
                  tabIndex: 4,
                  cls: "left-right-buttons",
                  id: "buttonMasterDepartmentSave",
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  hidden: true,
                  handler: function () {
                    saveDepartments();
                  },
                } /*<?php } ?> */,
              ],
            }),
          ],
        });
        return _mpanelforDepartment;
      };
      var parentDepartmentComboStorefn = function () {
        var store = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=getParentDepartment",
          method: "post",
          fields: ["id","name"],
          totalProperty: "totalCount",
          root: "data",
        });
    
        return store;
      };
      var DepartmentForm = function () {
        var parentDepartmentStore = parentDepartmentComboStorefn();
        var _departmentsFormPanel = new Ext.form.FormPanel({
          id: "formpanelMasterDepartment",
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
              fieldLabel: "Department",
              id: "department_name",
              name: "n[department_name]",
              anchor: "90%",
              allowBlank: false,
              tabIndex: 901,
              maxLength: 150,
            },
            {
              xtype: "textfield",
              id: "department_id",
              name: "n[department_id]",
              hidden: true,
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
                      id: "isParent",
                      name: "n[isParent]",
                      inputValue: 1,
                      boxLabel: "Parent Department",
                      anchor: "99%",
                      listeners: {
                        check: function (cbo, checked) {
                          if (checked == true) {
                            Ext.getCmp("parentDepartment").reset(); 
                            Ext.getCmp("parentDepartment").hide();                            
                          } else {
                            Ext.getCmp("parentDepartment").show();
                            
                          }
                        },
                      }
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
                      xtype: "combo",
                      fieldLabel: "Parent Department",
                      id: "parentDepartment",
                      name: "parentDepartment",
                      tabIndex: 902,
                      hiddenName: "parentDepartment",
                      anchor: "99%",
                      store: parentDepartmentStore,
                      valueField: "id",
                      displayField: "name",
                      forceSelection: true,
                      triggerAction: "all",
                      typeAhead: true,
                      selectOnFocus: true,
                      mode: "local",
                      listeners: {
                        select: function (combo, record, index) {
    
                        }
                      }
                    }
                  ],
                },
                {
                  columnWidth: 0.3,
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
                      tabIndex: 903,
                      anchor: "99%",
                      emptyText: "Set status..",
                      id: "comboDepartmentStatus",
                    })
                  ]
                }
              ]
            }
          ],
          listeners: {
            afterrender: function () {
              if (Ext.isEmpty(Ext.getCmp("department_id").getValue())) {
                var recordSelected = Ext.getCmp("comboDepartmentStatus")
                  .getStore()
                  .getAt(0);
                Ext.getCmp("comboDepartmentStatus").setValue(
                  recordSelected.get("id")
                );
              }
            },
          },
        });
        return _departmentsFormPanel;
      };
      var DepartmentMasterDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          border: false,
          hideBorders: true,
          autoHeight: true,
          id: "panelMasterDepartmentDetailsView",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Name </th><td>  {department_name} </td></tr>',
            '<tr><th width="40%">Parent Department </th><td>  {parentDepartmentName} </td></tr>',
            '<tr><th width="40%">Status</th><td>',
            "<tpl if=\"status == '1'\">Active</tpl>",
            "<tpl if=\"status == '0'\">Inactive</tpl>",
            "</td></tr>",
            "</table>",
            "</div>"
          ),
        });
      };
      var DepartmentMasterStore = function () {
        var _departmentsMasterStore = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listDepartment",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "department_id",
              root: "data",
            },
            ["department_id", "department_name", "status"]
          ),
          sortInfo: {
            field: "department_id",
            direction: "DESC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              Ext.getCmp("gridpanelMasterDataviewDepartmentdata")
                .getView()
                .refresh();
              Ext.getCmp("gridpanelMasterDataviewDepartmentdata")
                .getSelectionModel()
                .selectRow(0);
            },
          },
        });
        return _departmentsMasterStore;
      };
      var DepartmentMainGrid = function () {
        var _departmentsStore = DepartmentMasterStore();
        var _departmentsGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "department_name",
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
        _departmentsGridFilter.remote = true;
        _departmentsGridFilter.autoReload = true;
        var _departmentsmaingridPanel = new Ext.grid.GridPanel({
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          loadMask: true,
          store: _departmentsStore,
          iconCls: "money",
          id: "gridpanelMasterDataviewDepartmentdata",
          view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),
          plugins: [new Ext.ux.grid.GroupSummary(), _departmentsGridFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Department",
              dataIndex: "department_name",
              sortable: true,
              tooltip: "Department",
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
            store: _departmentsStore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedDepartments,
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("department_id");
              if (!Ext.isEmpty(ID)) {
                Application.BusinessDivision.Cache.department_id = ID;
                Ext.getCmp("formpanelMasterDepartment").hide();
                Application.BusinessDivision.ViewDepartments(ID);
              }
            },
            resize: onGridResize,
            afterrender: function () {
              _departmentsStore.load();
            },
          },
          tbar: [
            {
              text: "Create Department",
              tooltip: "Create Department ",
              icon: "./resources/images/submenuicons/add.png",
              iconCls: "my-icon1",
              handler: function () {
                Application.BusinessDivision.DepartmentsAddEdit = "Add";
                var departmentsForm = Ext.getCmp(
                  "formpanelMasterDepartment"
                ).getForm();
                Ext.getCmp("panelMasterDepartmentParent").setTitle(
                  "Create Department Details"
                );
                loadedForm = null;
                departmentsForm.reset();
                Ext.getCmp("department_name").focus(false, 100);
                /*<?php if (user_access("business_division", "saveDepartments")) { ?> */
                Ext.getCmp("buttonMasterDepartmentEdit").hide();
                Ext.getCmp("buttonMasterDepartmentSave").show();
                /*<?php } ?> */
                Ext.getCmp("buttonMasterDepartmentCancel").show();
                Ext.getCmp("formpanelMasterDepartment").show();
                Ext.getCmp("comboDepartmentStatus").setValue(1)
                Ext.getCmp("panelMasterDepartmentDetailsView").hide();
                Ext.getCmp("panelMasterDepartmentParent").doLayout();
              },
            },
          ],
        });
        return _departmentsmaingridPanel;
      };
      var saveDepartments = function () {
        var ptId = Ext.getCmp("department_id").getValue();
        if (
          !Ext.isEmpty(Ext.getCmp("department_name").getValue()) &&
          !Ext.isEmpty(Ext.getCmp("comboDepartmentStatus").getValue())
        ) {
          Ext.Ajax.request({
            url: modURL + "&op=saveDepartments",
            method: "POST",
            params: {
              id: Ext.getCmp("department_id").getValue(),
              name: Ext.getCmp("department_name").getValue(),
              parentId: Ext.getCmp("parentDepartment").getValue(),
              status: Ext.getCmp("comboDepartmentStatus").getValue(),
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (Application.BusinessDivision.DepartmentsAddEdit == "Add") {
                  RECS_PER_PAGE = updateRecsPerPage(
                    Ext.getCmp("gridpanelMasterDataviewDepartmentdata")
                  );
                  Ext.getCmp("formpanelMasterDepartment").getForm().reset();
                  Ext.getCmp(
                    "gridpanelMasterDataviewDepartmentdata"
                  ).store.reload({
                    params: {
                      start: 0,
                      limit: RECS_PER_PAGE,
                    },
                  });
                } else {
                  Ext.getCmp("gridpanelMasterDataviewDepartmentdata")
                    .getStore()
                    .load({
                      callback: function (record, options, success) {
                        var gridPanel = Ext.getCmp(
                          "gridpanelMasterDataviewDepartmentdata"
                        );
                        var index = gridPanel.store.find("department_id", ptId);
                        gridPanel.getSelectionModel().selectRow(index);
                      },
                    });
                }
                Application.BusinessDivision.DepartmentsAddEdit = "";
                Application.BusinessDivision.ViewDepartments(
                  tmp.data.department_id
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

      var gridSelectionChangedDesignations = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelMasterDataviewDesignationdata")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelMasterDataviewDesignationdata")
            .getSelectionModel()
            .getSelections()[0].data.designation_id;
          Application.BusinessDivision.ViewDesignations(ID);
        }
      };
    var masterPanelforDesignation = function (id) {
        var _mpanelforDesignation = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "Designations",
          id: id,
          iconCls: "my-icon448",
          items: [
            DesignationMainGrid(),
            new Ext.Panel({
              title: "Designation Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              autoScroll: true,
              cls: "left_side_panel",
              id: "panelMasterDesignationParent",
              height: winsize.height * 0.6,
              items: [DesignationForm(), DesignationMasterDetailsView()],
              buttonAlign: "right",
              fbar: [
                /*<?php if (user_access("business_division", "saveDesignations")) { ?> */ {
                  text: "Edit",
                  cls: "left-right-buttons",
                  iconCls: "edit",
                  id: "buttonMasterDesignationEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
                  tabIndex: 4,
                  hidden:true,
                  handler: function () {
                    var ID = Ext.getCmp("gridpanelMasterDataviewDesignationdata")
                      .getSelectionModel()
                      .getSelections()[0].data.designation_id;
                    Application.BusinessDivision.EditDesignationsView(ID);
                  },
                },
                {
                  text: "Cancel",
                  tabIndex: 5,
                  cls: "left-right-buttons",
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  id: "buttonMasterDesignationCancel",
                  hidden: true,
                  handler: function () {
                    if (
                      !Ext.isEmpty(
                        Ext.getCmp("gridpanelMasterDataviewDesignationdata")
                          .getSelectionModel()
                          .getSelections()
                      )
                    ) {
                      var ID = Ext.getCmp(
                        "gridpanelMasterDataviewDesignationdata"
                      )
                        .getSelectionModel()
                        .getSelections()[0].data.designation_id;
                      Application.BusinessDivision.ViewDesignations(ID);
                    }
                  },
                },
                {
                  text: "Save",
                  tabIndex: 4,
                  cls: "left-right-buttons",
                  id: "buttonMasterDesignationSave",
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  hidden: true,
                  handler: function () {
                    saveDesignations();
                  },
                } /*<?php } ?> */,
              ],
            }),
          ],
        });
        return _mpanelforDesignation;
      };
      var roleComboStorefn = function () {
        var store = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=getRoles",
          method: "post",
          fields: ["id", "name"],
          root: "data",
        });
        return store;
      };
      var DesignationForm = function () {
        var roleComboStore = roleComboStorefn();
        var _designationsFormPanel = new Ext.form.FormPanel({
          id: "formpanelMasterDesignation",
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
              fieldLabel: "Designation",
              id: "designation_name",
              name: "n[designation_name]",
              anchor: "98%",
              allowBlank: false,
              tabIndex: 1,
              maxLength: 100,
            },
            {
              xtype: "textfield",
              id: "designation_id",
              name: "n[designation_id]",
              hidden: true,
            },{
                xtype: "combo",
                store: roleComboStore,
                mode: "local",
                id: "designationRoleId",
                allowBlank: true,
                fieldLabel: "Role",
                hiddenName: "n[designationRoleId]",
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
                    var value = Ext.getCmp("designationRoleId").getValue();
                  },
                },
              },
            mkCombo({
              type: STATUS_COMBO_DATA,
              value: "id",
              display: "text",
              name: "n[status]",
              fieldLabel: "Status",
              tabIndex: 2,
              emptyText: "Set status..",
              id: "comboDesignationStatus",
            }),
          ],
          listeners: {
            afterrender: function () {
              if (Ext.isEmpty(Ext.getCmp("designation_id").getValue())) {
                var recordSelected = Ext.getCmp("comboDesignationStatus")
                  .getStore()
                  .getAt(0);
                Ext.getCmp("comboDesignationStatus").setValue(
                  recordSelected.get("id")
                );
              }
            },
          },
        });
        return _designationsFormPanel;
      };
      var DesignationMasterDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          border: false,
          hideBorders: true,
          autoHeight: true,
          id: "panelMasterDesignationDetailsView",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Name </th><td>  {designation_name} </td></tr>',
            '<tr><th width="40%">Role </th><td>  {role_name} </td></tr>',
            '<tr><th width="40%">Status</th><td>',
            "<tpl if=\"status == '1'\">Active</tpl>",
            "<tpl if=\"status == '0'\">Inactive</tpl>",
            "</td></tr>",
            "</table>",
            "</div>"
          ),
        });
      };
      var DesignationMasterStore = function () {
        var _designationsMasterStore = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listDesignation",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "designation_id",
              root: "data",
            },
            ["designation_id", "designation_name", "status"]
          ),
          sortInfo: {
            field: "designation_id",
            direction: "DESC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              Ext.getCmp("gridpanelMasterDataviewDesignationdata")
                .getView()
                .refresh();
              Ext.getCmp("gridpanelMasterDataviewDesignationdata")
                .getSelectionModel()
                .selectRow(0);
            },
          },
        });
        return _designationsMasterStore;
      };
      var DesignationMainGrid = function () {
        var _designationsStore = DesignationMasterStore();
        var _designationsGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "designation_name",
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
        _designationsGridFilter.remote = true;
        _designationsGridFilter.autoReload = true;
        var _designationsmaingridPanel = new Ext.grid.GridPanel({
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          loadMask: true,
          store: _designationsStore,
          iconCls: "money",
          id: "gridpanelMasterDataviewDesignationdata",
          view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),
          plugins: [new Ext.ux.grid.GroupSummary(), _designationsGridFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Designation",
              dataIndex: "designation_name",
              sortable: true,
              tooltip: "Designation",
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
            store: _designationsStore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedDesignations,
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("designation_id");
              if (!Ext.isEmpty(ID)) {
                Application.BusinessDivision.Cache.designation_id = ID;
                Ext.getCmp("formpanelMasterDesignation").hide();
                Application.BusinessDivision.ViewDesignations(ID);
              }
            },
            resize: onGridResize,
            afterrender: function () {
              _designationsStore.load();
            },
          },
          tbar: [
            {
              text: "Create Designation",
              tooltip: "Create Designation ",
              icon: "./resources/images/submenuicons/add.png",
              iconCls: "my-icon1",
              handler: function () {
                Application.BusinessDivision.DesignationsAddEdit = "Add";
                var designationsForm = Ext.getCmp(
                  "formpanelMasterDesignation"
                ).getForm();
                Ext.getCmp("panelMasterDesignationParent").setTitle(
                  "Create Designation Details"
                );
                loadedForm = null;
                designationsForm.reset();
                Ext.getCmp("designation_name").focus(false, 100);
                /*<?php if (user_access("business_division", "saveDesignations")) { ?> */
                Ext.getCmp("buttonMasterDesignationEdit").hide();
                Ext.getCmp("buttonMasterDesignationSave").show();
                /*<?php } ?> */
                Ext.getCmp("buttonMasterDesignationCancel").show();
                Ext.getCmp("formpanelMasterDesignation").show();
                Ext.getCmp("comboDesignationStatus").setValue(1)
                Ext.getCmp("panelMasterDesignationDetailsView").hide();
                Ext.getCmp("panelMasterDesignationParent").doLayout();
              },
            },
          ],
        });
        return _designationsmaingridPanel;
      };
      var saveDesignations = function () {
        var ptId = Ext.getCmp("designation_id").getValue();
        if (
          !Ext.isEmpty(Ext.getCmp("designation_name").getValue()) &&
          !Ext.isEmpty(Ext.getCmp("comboDesignationStatus").getValue())
        ) {
          Ext.Ajax.request({
            url: modURL + "&op=saveDesignations",
            method: "POST",
            params: {
              id: Ext.getCmp("designation_id").getValue(),
              name: Ext.getCmp("designation_name").getValue(),
              parentId: Ext.getCmp("designationRoleId").getValue(),
              status: Ext.getCmp("comboDesignationStatus").getValue(),
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (Application.BusinessDivision.DesignationsAddEdit == "Add") {
                  RECS_PER_PAGE = updateRecsPerPage(
                    Ext.getCmp("gridpanelMasterDataviewDesignationdata")
                  );
                  Ext.getCmp("formpanelMasterDesignation").getForm().reset();
                  Ext.getCmp(
                    "gridpanelMasterDataviewDesignationdata"
                  ).store.reload({
                    params: {
                      start: 0,
                      limit: RECS_PER_PAGE,
                    },
                  });
                } else {
                  Ext.getCmp("gridpanelMasterDataviewDesignationdata")
                    .getStore()
                    .load({
                      callback: function (record, options, success) {
                        var gridPanel = Ext.getCmp(
                          "gridpanelMasterDataviewDesignationdata"
                        );
                        var index = gridPanel.store.find("designation_id", ptId);
                        gridPanel.getSelectionModel().selectRow(index);
                      },
                    });
                }
                Application.BusinessDivision.DesignationsAddEdit = "";
                Application.BusinessDivision.ViewDesignations(
                  tmp.data.designation_id
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
      var gridSelectionChangedAreaDivisions = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
            .getSelectionModel()
            .getSelections()[0].data.areaDivision_id;
            var areaId = Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
            .getSelectionModel()
            .getSelections()[0].data.areaId;
          Application.BusinessDivision.ViewAreaDivisions(ID,areaId);
        }
      };
    var masterPanelforAreaDivision = function (id) {
        var _mpanelforAreaDivision = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "Area",
          id: id,
          iconCls: "my-icon448",
          items: [
            AreaDivisionMainGrid(),
            new Ext.Panel({
              title: "Area Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              autoScroll: true,
              cls: "left_side_panel",
              id: "panelMasterAreaDivisionParent",
              height: winsize.height * 0.6,
              items: [AreaDivisionForm(), AreaDivisionMasterDetailsView()],
              buttonAlign: "right",
              fbar: [
                /*<?php if (user_access("business_division", "saveAreaDivisions")) { ?> */ {
                  text: "Edit",
                  cls: "left-right-buttons",
                  iconCls: "edit",
                  id: "buttonMasterAreaDivisionEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/mem_edit.png",
                  tabIndex: 4,
                  hidden:true,
                  handler: function () {
                    var ID = Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
                      .getSelectionModel()
                      .getSelections()[0].data.areaDivision_id;
                      var areaId = Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
                      .getSelectionModel()
                      .getSelections()[0].data.areaId;
                    Application.BusinessDivision.EditAreaDivisionsView(ID,areaId);
                  },
                },
                {
                  text: "Cancel",
                  tabIndex: 5,
                  cls: "left-right-buttons",
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  id: "buttonMasterAreaDivisionCancel",
                  hidden: true,
                  handler: function () {
                    if (
                      !Ext.isEmpty(
                        Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
                          .getSelectionModel()
                          .getSelections()
                      )
                    ) {
                      var ID = Ext.getCmp(
                        "gridpanelMasterDataviewAreaDivisiondata"
                      )
                        .getSelectionModel()
                        .getSelections()[0].data.areaDivision_id;
                        var areaId = Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
            .getSelectionModel()
            .getSelections()[0].data.areaId;
                      Application.BusinessDivision.ViewAreaDivisions(ID,areaId);
                    }
                  },
                },
                {
                  text: "Save",
                  tabIndex: 4,
                  cls: "left-right-buttons",
                  id: "buttonMasterAreaDivisionSave",
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  hidden: true,
                  handler: function () {
                    saveAreaDivisions();
                  },
                } /*<?php } ?> */,
              ],
            }),
          ],
        });
        return _mpanelforAreaDivision;
      };
var AreaDivisionForm = function () {
        var zoneComboStore = zoneComboStorefn();
        var regionComboStore = regionComboStorefn();
        var territoryComboStore = territoryComboStorefn();
        var _areaDivisionsFormPanel = new Ext.form.FormPanel({
          id: "formpanelMasterAreaDivision",
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
              fieldLabel: "Area",
              id: "areaDivision_name",
              name: "n[areaDivision_name]",
              anchor: "98%",
              allowBlank: false,
              tabIndex: 1,
              maxLength: 100,
            },
            {
              xtype: "textfield",
              id: "areaDivision_id",
              name: "n[areaDivision_id]",
              hidden: true,
            },{
                xtype: "combo",
                store: zoneComboStore,
                mode: "local",
                id: "areaDivisionZoneId",
                allowBlank: true,
                fieldLabel: "Zone",
                hiddenName: "n[areaDivisionZoneId]",
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
                    var value = Ext.getCmp("areaDivisionZoneId").getValue();
                    regionComboStore.baseParams.zoneId = this.value;
                    regionComboStore.load();
                  },
                },
              },
              {
                xtype: "combo",
                store: regionComboStore,
                mode: "local",
                id: "areaDivisionRegionId",
                allowBlank: true,
                fieldLabel: "Region",
                hiddenName: "n[areaDivisionRegionId]",
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
                    var value = Ext.getCmp("areaDivisionRegionId").getValue();
                    territoryComboStore.baseParams.regionId = this.value;
                    territoryComboStore.load();
                  },
                },
              },
              {
                xtype: "combo",
                store: territoryComboStore,
                mode: "local",
                id: "areaDivisionTerritoryId",
                allowBlank: true,
                fieldLabel: "Territory",
                hiddenName: "n[areaDivisionTerritoryId]",
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
              },
            mkCombo({
              type: STATUS_COMBO_DATA,
              value: "id",
              display: "text",
              name: "n[status]",
              fieldLabel: "Status",
              tabIndex: 2,
              emptyText: "Set status..",
              id: "comboAreaDivisionStatus",
            }),
          ],
          listeners: {
            afterrender: function () {
              if (Ext.isEmpty(Ext.getCmp("areaDivision_id").getValue())) {
                var recordSelected = Ext.getCmp("comboAreaDivisionStatus")
                  .getStore()
                  .getAt(0);
                Ext.getCmp("comboAreaDivisionStatus").setValue(
                  recordSelected.get("id")
                );
              }
            },
          },
        });
        return _areaDivisionsFormPanel;
      };
      var AreaDivisionMasterDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          border: false,
          hideBorders: true,
          autoHeight: true,
          id: "panelMasterAreaDivisionDetailsView",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Zone </th><td>  {zone_name} </td></tr>',
            '<tr><th width="40%">Region </th><td>  {region_name} </td></tr>',
            '<tr><th width="40%">Territory </th><td>  {territory_name} </td></tr>',
            '<tr><th width="40%">Name </th><td>  {areaDivision_name} </td></tr>',            
            '<tr><th width="40%">Status</th><td>',
            "<tpl if=\"comboAreaDivisionStatus == '1'\">Active</tpl>",
            "<tpl if=\"comboAreaDivisionStatus == '0'\">Inactive</tpl>",
            "</td></tr>",
            "</table>",
            "</div>"
          ),
        });
      };
      var AreaDivisionMasterStore = function () {
        var _areaDivisionsMasterStore = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listAreaDivision",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "areaId",
              root: "data",
            },
            ["areaDivision_id", "areaDivision_name", "status","areaId","territory_name","region_name","zone_name"]
          ),
          sortInfo: {
            field: "areaDivision_id",
            direction: "DESC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
                .getView()
                .refresh();
              Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
                .getSelectionModel()
                .selectRow(0);
            },
          },
        });
        return _areaDivisionsMasterStore;
      };
      var AreaDivisionMainGrid = function () {
        var _areaDivisionsStore = AreaDivisionMasterStore();
        var _areaDivisionsGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "areaDivision_name",
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
        _areaDivisionsGridFilter.remote = true;
        _areaDivisionsGridFilter.autoReload = true;
        var _areaDivisionsmaingridPanel = new Ext.grid.GridPanel({
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          loadMask: true,
          store: _areaDivisionsStore,
          iconCls: "money",
          id: "gridpanelMasterDataviewAreaDivisiondata",
          view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),
          plugins: [new Ext.ux.grid.GroupSummary(), _areaDivisionsGridFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Area",
              dataIndex: "areaDivision_name",
              sortable: true,
              tooltip: "Area",
              hideable: false,
            },
            {
              header: "Territory",
              dataIndex: "territory_name",
              sortable: true,
              tooltip: "Territory",
            },
            {
              header: "Region",
              dataIndex: "region_name",
              sortable: true,
              tooltip: "Region",
            },
            {
              header: "Zone",
              dataIndex: "zone_name",
              sortable: true,
              tooltip: "Zone",
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
            store: _areaDivisionsStore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedAreaDivisions,
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("areaDivision_id");
              var areaId = record.get("areaId");;
              if (!Ext.isEmpty(ID)) {
                Application.BusinessDivision.Cache.areaDivision_id = ID;
                Ext.getCmp("formpanelMasterAreaDivision").hide();
                Application.BusinessDivision.ViewAreaDivisions(ID,areaId);
              }
            },
            resize: onGridResize,
            afterrender: function () {
              _areaDivisionsStore.load();
            },
          },
          tbar: [
            {
              text: "Create Area",
              tooltip: "Create Area",
              icon: "./resources/images/submenuicons/add.png",
              iconCls: "my-icon1",
              handler: function () {
                Application.BusinessDivision.AreaDivisionsAddEdit = "Add";
                var areaDivisionsForm = Ext.getCmp(
                  "formpanelMasterAreaDivision"
                ).getForm();
                Ext.getCmp("panelMasterAreaDivisionParent").setTitle(
                  "Create Area Details"
                );
                loadedForm = null;
                areaDivisionsForm.reset();
                Ext.getCmp("areaDivision_name").focus(false, 100);
                /*<?php if (user_access("business_division", "saveAreaDivisions")) { ?> */
                Ext.getCmp("buttonMasterAreaDivisionEdit").hide();
                Ext.getCmp("buttonMasterAreaDivisionSave").show();
                /*<?php } ?> */
                Ext.getCmp("buttonMasterAreaDivisionCancel").show();
                Ext.getCmp("formpanelMasterAreaDivision").show();
                Ext.getCmp("comboAreaDivisionStatus").setValue(1)
                Ext.getCmp("panelMasterAreaDivisionDetailsView").hide();
                Ext.getCmp("panelMasterAreaDivisionParent").doLayout();
              },
            },
          ],
        });
        return _areaDivisionsmaingridPanel;
      };
      var saveAreaDivisions = function () {
        var ptId = Ext.getCmp("areaDivision_id").getValue();
        if (
          !Ext.isEmpty(Ext.getCmp("areaDivision_name").getValue()) &&
          !Ext.isEmpty(Ext.getCmp("comboAreaDivisionStatus").getValue())
        ) {
          Ext.Ajax.request({
            url: modURL + "&op=saveAreaDivisions",
            method: "POST",
            params: {
              id: Ext.getCmp("areaDivision_id").getValue(),
              name: Ext.getCmp("areaDivision_name").getValue(),
              parentId: Ext.getCmp("areaDivisionTerritoryId").getValue(),
              status: Ext.getCmp("comboAreaDivisionStatus").getValue(),
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                if (Application.BusinessDivision.AreaDivisionsAddEdit == "Add") {
                  RECS_PER_PAGE = updateRecsPerPage(
                    Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
                  );
                  Ext.getCmp("formpanelMasterAreaDivision").getForm().reset();
                  Ext.getCmp(
                    "gridpanelMasterDataviewAreaDivisiondata"
                  ).store.reload({
                    params: {
                      start: 0,
                      limit: RECS_PER_PAGE,
                    },
                  });
                } else {
                  Ext.getCmp("gridpanelMasterDataviewAreaDivisiondata")
                    .getStore()
                    .load({
                      callback: function (record, options, success) {
                        var gridPanel = Ext.getCmp(
                          "gridpanelMasterDataviewAreaDivisiondata"
                        );
                        var index = gridPanel.store.find("areaDivision_id", ptId);
                        gridPanel.getSelectionModel().selectRow(index);
                      },
                    });
                }
                Application.BusinessDivision.AreaDivisionsAddEdit = "";
                Application.BusinessDivision.ViewAreaDivisions(
                  tmp.data.areaDivision_id
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
    return {
        Cache: {},
        initZone: function () {
            var _zonePanelId = "panelMasterMainZone";
            var _masterPanelZone = Ext.getCmp(_zonePanelId);
            if (Ext.isEmpty(_masterPanelZone)) {
              _masterPanelZone =
                masterPanelforZone(_zonePanelId);
              Application.UI.addTab(_masterPanelZone);
              _masterPanelZone.doLayout();
            } else {
              Application.UI.addTab(_masterPanelZone);
            }
          },
          ViewZones: function () {
            var zone_id = arguments[0];
            /*<?php if (user_access("business_division", "saveZones")) { ?> */
            Ext.getCmp("buttonMasterZoneEdit").show();
            Ext.getCmp("buttonMasterZoneSave").hide();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterZoneCancel").hide();
            Ext.getCmp("formpanelMasterZone").hide();
            Ext.getCmp("panelMasterZoneDetailsView").show();
            Ext.getCmp("panelMasterZoneParent").doLayout();
            Ext.getCmp("panelMasterZoneParent").setTitle(
              "View Zone Details"
            );
            Ext.Ajax.request({
              url: modURL + "&op=zonesdetailsView",
              method: "POST",
              params: { zone_id: zone_id },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp(
                    "panelMasterZoneDetailsView"
                  );
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("panelMasterZoneParent").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("panelMasterZoneParent").doLayout();
          },
          EditZonesView: function () {
            Application.BusinessDivision.ZonesAddEdit = "Edit";
            Ext.getCmp("panelMasterZoneParent").doLayout();
            Ext.getCmp("panelMasterZoneParent").setTitle(
              "Edit Zone Details"
            );
            Ext.getCmp("formpanelMasterZone").show();
            Ext.getCmp("panelMasterZoneDetailsView").hide();
            /*<?php if (user_access("business_division", "saveZones")) { ?> */
            Ext.getCmp("buttonMasterZoneEdit").hide();
            Ext.getCmp("buttonMasterZoneSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterZoneCancel").show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
              var zonesForm = Ext.getCmp(
                "formpanelMasterZone"
              ).getForm();
              zonesForm.load({
                params: {
                  zone_id: arguments[0],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=zones_form_load",
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
          initRegion: function () {
            var _regionPanelId = "panelMasterMainRegion";
            var _masterPanelRegion = Ext.getCmp(_regionPanelId);
            if (Ext.isEmpty(_masterPanelRegion)) {
              _masterPanelRegion =
                masterPanelforRegion(_regionPanelId);
              Application.UI.addTab(_masterPanelRegion);
              _masterPanelRegion.doLayout();
            } else {
              Application.UI.addTab(_masterPanelRegion);
            }
          },
          ViewRegions: function () {
            var region_id = arguments[0];
            /*<?php if (user_access("business_division", "isSuperAdmin")) { ?> */
            Ext.getCmp("buttonMasterRegionEdit").show();
            Ext.getCmp("buttonMasterRegionSave").hide();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterRegionCancel").hide();
            Ext.getCmp("formpanelMasterRegion").hide();
            Ext.getCmp("panelMasterRegionDetailsView").show();
            Ext.getCmp("panelMasterRegionParent").doLayout();
            Ext.getCmp("panelMasterRegionParent").setTitle(
              "View Region Details"
            );
            Ext.Ajax.request({
              url: modURL + "&op=regionsdetailsView",
              method: "POST",
              params: { region_id: region_id },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp(
                    "panelMasterRegionDetailsView"
                  );
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("panelMasterRegionParent").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("panelMasterRegionParent").doLayout();
          },
          EditRegionsView: function () {
            Application.BusinessDivision.RegionsAddEdit = "Edit";
            Ext.getCmp("panelMasterRegionParent").doLayout();
            Ext.getCmp("panelMasterRegionParent").setTitle(
              "Edit Region Details"
            );
            Ext.getCmp("formpanelMasterRegion").show();
            Ext.getCmp("panelMasterRegionDetailsView").hide();
            /*<?php if (user_access("business_division", "isSuperAdmin")) { ?> */
            Ext.getCmp("buttonMasterRegionEdit").hide();
            Ext.getCmp("buttonMasterRegionSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterRegionCancel").show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
              var regionsForm = Ext.getCmp(
                "formpanelMasterRegion"
              ).getForm();
              regionsForm.load({
                params: {
                  region_id: arguments[0],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=regions_form_load",
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
          initTerritory: function () {
            var _territoryPanelId = "panelMasterMainTerritory";
            var _masterPanelTerritory = Ext.getCmp(_territoryPanelId);
            if (Ext.isEmpty(_masterPanelTerritory)) {
              _masterPanelTerritory =
                masterPanelforTerritory(_territoryPanelId);
              Application.UI.addTab(_masterPanelTerritory);
              _masterPanelTerritory.doLayout();
            } else {
              Application.UI.addTab(_masterPanelTerritory);
            }
          },
          ViewTerritorys: function () {
            var territory_id = arguments[0];
            /*<?php if (user_access("business_division", "saveTerritorys")) { ?> */
            Ext.getCmp("buttonMasterTerritoryEdit").show();
            Ext.getCmp("buttonMasterTerritorySave").hide();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterTerritoryCancel").hide();
            Ext.getCmp("formpanelMasterTerritory").hide();
            Ext.getCmp("panelMasterTerritoryDetailsView").show();
            Ext.getCmp("panelMasterTerritoryParent").doLayout();
            Ext.getCmp("panelMasterTerritoryParent").setTitle(
              "View Territory Details"
            );
            Ext.Ajax.request({
              url: modURL + "&op=territorysdetailsView",
              method: "POST",
              params: { territory_id: territory_id },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp(
                    "panelMasterTerritoryDetailsView"
                  );
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("panelMasterTerritoryParent").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("panelMasterTerritoryParent").doLayout();
          },
          EditTerritorysView: function () {
            Application.BusinessDivision.TerritorysAddEdit = "Edit";
            Ext.getCmp("panelMasterTerritoryParent").doLayout();
            Ext.getCmp("panelMasterTerritoryParent").setTitle(
              "Edit Territory Details"
            );
            Ext.getCmp("formpanelMasterTerritory").show();
            Ext.getCmp("panelMasterTerritoryDetailsView").hide();
            /*<?php if (user_access("business_division", "saveTerritorys")) { ?> */
            Ext.getCmp("buttonMasterTerritoryEdit").hide();
            Ext.getCmp("buttonMasterTerritorySave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterTerritoryCancel").show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
              var territorysForm = Ext.getCmp(
                "formpanelMasterTerritory"
              ).getForm();
              territorysForm.load({
                params: {
                  territory_id: arguments[0],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=territorys_form_load",
                waitMsg: "Loading...",
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                  Ext.getCmp("territoryRegionId").getStore().baseParams.zoneId = tmp.data.territoryZoneId;
                  Ext.getCmp("territoryRegionId").getStore().load();
                  Ext.getCmp("territoryRegionId").setValue(tmp.data.territoryRegionId);
                  Ext.getCmp("territoryRegionId").setRawValue(tmp.data.region_name);

                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
            }
          },
          initDepartment: function () {
            var _departmentPanelId = "panelMasterMainDepartment";
            var _masterPanelDepartment = Ext.getCmp(_departmentPanelId);
            if (Ext.isEmpty(_masterPanelDepartment)) {
              _masterPanelDepartment =
                masterPanelforDepartment(_departmentPanelId);
              Application.UI.addTab(_masterPanelDepartment);
              _masterPanelDepartment.doLayout();
            } else {
              Application.UI.addTab(_masterPanelDepartment);
            }
          },
          ViewDepartments: function () {
            var department_id = arguments[0];
            /*<?php if (user_access("business_division", "saveDepartments")) { ?> */
            Ext.getCmp("buttonMasterDepartmentEdit").show();
            Ext.getCmp("buttonMasterDepartmentSave").hide();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterDepartmentCancel").hide();
            Ext.getCmp("formpanelMasterDepartment").hide();
            Ext.getCmp("panelMasterDepartmentDetailsView").show();
            Ext.getCmp("panelMasterDepartmentParent").doLayout();
            Ext.getCmp("panelMasterDepartmentParent").setTitle(
              "View Department Details"
            );
            Ext.Ajax.request({
              url: modURL + "&op=departmentsdetailsView",
              method: "POST",
              params: { department_id: department_id },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp(
                    "panelMasterDepartmentDetailsView"
                  );
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("panelMasterDepartmentParent").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("panelMasterDepartmentParent").doLayout();
          },
          EditDepartmentsView: function () {
            Application.BusinessDivision.DepartmentsAddEdit = "Edit";
            Ext.getCmp("panelMasterDepartmentParent").doLayout();
            Ext.getCmp("panelMasterDepartmentParent").setTitle(
              "Edit Department Details"
            );
            Ext.getCmp("formpanelMasterDepartment").show();
            Ext.getCmp("panelMasterDepartmentDetailsView").hide();
            /*<?php if (user_access("business_division", "saveDepartments")) { ?> */
            Ext.getCmp("buttonMasterDepartmentEdit").hide();
            Ext.getCmp("buttonMasterDepartmentSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterDepartmentCancel").show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
              var departmentsForm = Ext.getCmp(
                "formpanelMasterDepartment"
              ).getForm();
              departmentsForm.load({
                params: {
                  department_id: arguments[0],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=departments_form_load",
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
          initDesignation: function () {
            var _designationPanelId = "panelMasterMainDesignation";
            var _masterPanelDesignation = Ext.getCmp(_designationPanelId);
            if (Ext.isEmpty(_masterPanelDesignation)) {
              _masterPanelDesignation =
                masterPanelforDesignation(_designationPanelId);
              Application.UI.addTab(_masterPanelDesignation);
              _masterPanelDesignation.doLayout();
            } else {
              Application.UI.addTab(_masterPanelDesignation);
            }
          },
          ViewDesignations: function () {
            var designation_id = arguments[0];
            /*<?php if (user_access("business_division", "saveDesignations")) { ?> */
            Ext.getCmp("buttonMasterDesignationEdit").show();
            Ext.getCmp("buttonMasterDesignationSave").hide();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterDesignationCancel").hide();
            Ext.getCmp("formpanelMasterDesignation").hide();
            Ext.getCmp("panelMasterDesignationDetailsView").show();
            Ext.getCmp("panelMasterDesignationParent").doLayout();
            Ext.getCmp("panelMasterDesignationParent").setTitle(
              "View Designation Details"
            );
            Ext.Ajax.request({
              url: modURL + "&op=designationsdetailsView",
              method: "POST",
              params: { designation_id: designation_id },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp(
                    "panelMasterDesignationDetailsView"
                  );
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("panelMasterDesignationParent").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("panelMasterDesignationParent").doLayout();
          },
          EditDesignationsView: function () {
            Application.BusinessDivision.DesignationsAddEdit = "Edit";
            Ext.getCmp("panelMasterDesignationParent").doLayout();
            Ext.getCmp("panelMasterDesignationParent").setTitle(
              "Edit Designation Details"
            );
            Ext.getCmp("formpanelMasterDesignation").show();
            Ext.getCmp("panelMasterDesignationDetailsView").hide();
            /*<?php if (user_access("business_division", "saveDesignations")) { ?> */
            Ext.getCmp("buttonMasterDesignationEdit").hide();
            Ext.getCmp("buttonMasterDesignationSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterDesignationCancel").show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
              var designationsForm = Ext.getCmp(
                "formpanelMasterDesignation"
              ).getForm();
              designationsForm.load({
                params: {
                  designation_id: arguments[0],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=designations_form_load",
                waitMsg: "Loading...",
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
            }
          },initAreaDivision: function () {
            var _areaDivisionPanelId = "panelMasterMainAreaDivision";
            var _masterPanelAreaDivision = Ext.getCmp(_areaDivisionPanelId);
            if (Ext.isEmpty(_masterPanelAreaDivision)) {
              _masterPanelAreaDivision =
                masterPanelforAreaDivision(_areaDivisionPanelId);
              Application.UI.addTab(_masterPanelAreaDivision);
              _masterPanelAreaDivision.doLayout();
            } else {
              Application.UI.addTab(_masterPanelAreaDivision);
            }
          },
          ViewAreaDivisions: function () {
            var areaDivision_id = arguments[0];
            var areaId = arguments[1];
            /*<?php if (user_access("business_division", "saveAreaDivisions")) { ?> */
            Ext.getCmp("buttonMasterAreaDivisionEdit").show();
            Ext.getCmp("buttonMasterAreaDivisionSave").hide();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterAreaDivisionCancel").hide();
            Ext.getCmp("formpanelMasterAreaDivision").hide();
            Ext.getCmp("panelMasterAreaDivisionDetailsView").show();
            Ext.getCmp("panelMasterAreaDivisionParent").doLayout();
            Ext.getCmp("panelMasterAreaDivisionParent").setTitle(
              "View Area Details"
            );
            Ext.Ajax.request({
              url: modURL + "&op=areaDivisionsdetailsView",
              method: "POST",
              params: { areaDivision_id: areaDivision_id,areaId:areaId },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp(
                    "panelMasterAreaDivisionDetailsView"
                  );
                  visualsDescPanel.update(tmp);
                }
                Ext.getCmp("panelMasterAreaDivisionParent").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("panelMasterAreaDivisionParent").doLayout();
          },
          EditAreaDivisionsView: function () {
            Application.BusinessDivision.AreaDivisionsAddEdit = "Edit";
            Ext.getCmp("panelMasterAreaDivisionParent").doLayout();
            Ext.getCmp("panelMasterAreaDivisionParent").setTitle(
              "Edit Area Details"
            );
            Ext.getCmp("formpanelMasterAreaDivision").show();
            Ext.getCmp("panelMasterAreaDivisionDetailsView").hide();
            /*<?php if (user_access("business_division", "saveAreaDivisions")) { ?> */
            Ext.getCmp("buttonMasterAreaDivisionEdit").hide();
            Ext.getCmp("buttonMasterAreaDivisionSave").show();
            /*<?php } ?> */
            Ext.getCmp("buttonMasterAreaDivisionCancel").show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0]) || !Ext.isEmpty(arguments[1])) {
              var areaDivisionsForm = Ext.getCmp(
                "formpanelMasterAreaDivision"
              ).getForm();
              areaDivisionsForm.load({
                params: {
                  areaDivision_id: arguments[0],
                  areaId:arguments[1],
                  apikey: _SESSION.apikey,
                  tstamp: t_stamp,
                },
                url: modURL + "&op=areaDivisions_form_load",
                waitMsg: "Loading...",
                success: function (form, action) {
                  var tmp = Ext.decode(action.response.responseText);
                  Ext.getCmp('areaDivisionRegionId').getStore().baseParams.zoneId = tmp.data.areaDivisionZoneId;
                  Ext.getCmp('areaDivisionRegionId').getStore().load();
                  Ext.getCmp('areaDivisionRegionId').setValue(tmp.data.areaDivisionRegionId);
                  Ext.getCmp('areaDivisionRegionId').setRawValue(tmp.data.region_name);
                  Ext.getCmp('areaDivisionTerritoryId').getStore().baseParams.regionId = tmp.data.areaDivisionRegionId;
                  Ext.getCmp('areaDivisionTerritoryId').getStore().load();
                  Ext.getCmp('areaDivisionTerritoryId').setValue(tmp.data.areaDivisionTerritoryId);
                  Ext.getCmp('areaDivisionTerritoryId').setRawValue(tmp.data.territory_name);
                },
                failure: function (form, action) {
                  Ext.Msg.alert("Error.", "This error");
                },
              });
            }
          },
    };
})();