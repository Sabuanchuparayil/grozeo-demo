Application.OrderBOD = (function () {
  var recs_per_page = 12;
  var modURL = "?module=order_bod";
  var winsize = Ext.getBody().getViewSize();
  var updatePagination = function (cmp) {
    recs_per_page = updateRecsPerPage(cmp);
  };
  var gridSelectionChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("bod_collection_grid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("bod_collection_grid")
        .getSelectionModel()
        .getSelections()[0].data.quor_id;
      var quor_Type = Ext.getCmp("bod_collection_grid")
        .getSelectionModel()
        .getSelections()[0].data.quor_Type;
      var quor_Status = Ext.getCmp("bod_collection_grid")
        .getSelectionModel()
        .getSelections()[0].data.quor_Status;
      var quor_TransferOrder_Type = Ext.getCmp("bod_collection_grid")
        .getSelectionModel()
        .getSelections()[0].data.quor_TransferOrder_Type;
      var orderMethod = Ext.getCmp("bod_collection_grid")
        .getSelectionModel()
        .getSelections()[0].data.quor_TransferOrder_Type;
      var quor_DeliveryMethodsAllowed = Ext.getCmp("bod_collection_grid")
        .getSelectionModel()
        .getSelections()[0].data.quor_DeliveryMethodsAllowed;
      //defineMenuinDeliJobs(quor_DeliveryMethodsAllowed);
      Application.OrderBOD.Cache.quor_id = ID;
      Application.OrderBOD.ViewMode(ID);
      Ext.getCmp("acceptCashBtn").show();
    }
  };
  function qugeoSelect() {
    return new Ext.grid.RowSelectionModel({
      singleSelect: true,
      listeners: {
        selectionchange: gridSelectionChanged,
      },
    });
  }
  var bodCollectionJobsStore = function (ind) {
    var qugeo_store = new Ext.data.JsonStore({
      url: modURL + "&op=listbodCollectionJobs",
      fields: [
        "booking_no",
        "customer",
        "source",
        "destination",
        "quor_PickupName",
        "quor_PickupPhone",
        "quor_Status",
        "quor_Deliverybr_id",
        "quor_ItemReturned",
        "IsPickup",
        "sourcecontact",
        "quor_Pickupbr_id",
        "drivetype",
        "quor_id",
        "bk_brk_br_id",
        "quor_PickupLat",
        "quor_PickupLng",
        "quor_DeliveryLat",
        "quor_DeliveryLng",
        "pickupmapicon",
        "deliverymapicon",
        "dls_DelStatus",
        "quor_TypeName",
        "br_Name",
        "NetAmt",
        "driver",
        "quor_ScheduleOpeningTime",
        "quor_Type",
        "quor_DeliveryName",
        "quor_DeliveryPhone",
        "quor_TransferOrder_Type",
        "orgOrderDate",
        "orderMethod",
        "quor_DeliveryMethodsAllowed",
        "order_ondel_bankref_id",
        {
          name: "booked_at",
          type: "date",
          dateFormat: "d-m-Y",
        },
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        load: function () {
          Ext.getCmp("bod_collection_grid").getView().refresh();
          Ext.getCmp("bod_collection_grid").getSelectionModel().selectRow(0);
        },
        beforeload: function () {
          this.baseParams.ind = ind;
          this.baseParams.br_id = Ext.getCmp("search_branch" + ind).getValue();
        },
      },
    });
    return qugeo_store;
  };
  var bodCollectionJobsGrid = function (ind) {
    var qugeo_store = bodCollectionJobsStore(ind);
    var qugeo_select = qugeoSelect();
    var bodCollectionJobsFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "booking_no",
        },
        {
          type: "string",
          dataIndex: "quor_PickupName",
        },
        {
          type: "string",
          dataIndex: "quor_PickupPhone",
        },
        {
          type: "string",
          dataIndex: "quor_TypeName",
        },
        {
          type: "string",
          dataIndex: "date",
        },
        {
          type: "list",
          options: [
            "WAITING FOR DESPATCH",
            "DESPATCHED - IN TRANSIT",
            "DAMAGED WHILE TRANSIT",
            "AWAITING SALES TAX CLEARANCE",
            "INCOMPLETE RECEIPT",
            "ITEM NOT RECEIVED",
            "ITEM PARTLY RECEIVED",
            "FAILED-IN TRANSIT",
            "RECEIVED IN GODOWN",
            "SEND FOR DOOR DELIVERY",
            "DELIVERY FAILED - DOOR LOCKED",
            "DELIVERY FAILED - REFUSED",
            "DELIVERY FAILED - ADDRESS NOT FOUND",
            "INCOMPLETE DELIVERY",
            "DELIVERY FAILED - DAMAGED",
            "DELIVERY COMPLETED",
            "RE-ROUTED",
            "RE-BOOKED",
            "ARBITRATED",
            "PARTIAL DISPATCH",
            "PARTIAL RECEIVE",
            "PARTIAL DELIVERY",
            "PICKUP - AT ORIGIN",
            "PICKUP - POLLED",
            "PICKUP - POLL REJECTED",
            "PICKUP - POLL NO RESPONSE",
            "PICKUP - HOME BRANCH FLAGGED",
            "PICKUP - DIRECT DELIVERY FLAGGED",
            "PICKUP - HOME BRANCH PICKED UP",
            "PICKUP - DIRECT DELIVERY PICKED UP",
            "PICKUP - BOOKING CANCELLED",
            "PICKED UP - WAITING FOR ASSIGNMENT",
            "DELIVERY - POLLED",
            "DELIVERY - POLL REJECTED",
            "DELIVERY - POLL NO RESPONSE",
            "PICKUP FAILED - DOOR LOCKED",
            "PICKUP FAILED - ADDRESS NOT FOUND",
            "PICKUP FAILED - ITEM NOT READY",
            "DELIVERY - DELIVERED BUT NOT CONFIRMED",
            "DESPATCHED - VIA CROSS BOOKING",
            "CANCELLED",
          ],
          phpMode: true,
          dataIndex: "dls_DelStatus",
        },
      ],
    });
    bodCollectionJobsFilter.remote = true;
    bodCollectionJobsFilter.autoReload = true;
    var qugeo_grid = new Ext.grid.GridPanel({
      store: qugeo_store,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [new Ext.ux.grid.GroupSummary(), bodCollectionJobsFilter],
      id: "bod_collection_grid",
      columns: [
        {
          header: "Order Date",
          sortable: true,
          width: 80,
          dataIndex: "booked_at",
        },
        {
          header: "Order No.",
          sortable: true,
          dataIndex: "booking_no",
          width: 80,
        },
        {
          header: "Consignee",
          sortable: true,
          dataIndex: "customer",
          hidden: true,
        },
        {
          header: "Source",
          sortable: true,
          dataIndex: "source",
        },
        {
          header: "Delivery Boy",
          sortable: true,
          dataIndex: "driver",
        },
        {
          header: "Amount",
          sortable: true,
          dataIndex: "NetAmt",
        },
        {
          header: "Bank Reference",
          sortable: true,
          dataIndex: "order_ondel_bankref_id",
        },
      ],
      sm: qugeo_select,
      listeners: {
        resize: updatePagination,
        celldblClick: function (grid, rowIndex, columnIndex, e) {},
      },
      tbar: [
        { html: "Branch : &nbsp;", id: "branch_label" + ind },
        {
          xtype: "combo",
          mode: "remote",
          typeAhead: true,
          editable: true,
          emptyText: "Select",
          anchor: "100%",
          store: branchstore(ind),
          id: "search_branch" + ind,
          triggerAction: "all",
          displayField: "br_Name",
          allowBlank: false,
          valueField: "br_ID",
          hiddenName: "br_id",
          name: "br_id",
          minChars: 1,
          listeners: {
            select: function () {
              Ext.getCmp("bod_collection_grid").getStore().removeAll();
            },
          },
        },
        {
          xtype: "button",
          text: "Show",
          iconCls: "show",
          style: "padding-left: 10px;",
          id: "id_show" + ind,
          handler: function () {
            showData(ind);
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: qugeo_store,
        displayInfo: true,
        displayMsg: "Displaying pages {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      stripeRows: true,
    });
    return qugeo_grid;
  };
  var showData = function (ind) {
    var branch = Ext.getCmp("search_branch" + ind).getValue();

    Ext.getCmp("bod_collection_grid").getStore().baseParams.br_id = branch;
    Ext.getCmp("bod_collection_grid").getStore().load();
  };
  var bodCollectionJobsDetailsView = function () {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var apikey = _SESSION.apikey;
    var src = "";
    var contactsPanel = new Ext.Panel({
      id: "details_view_panel_bodCollectionJobs",
      region: "center",
      width: winsize.width * 0.4,
      items: [
        {
          region: "center",
          defaults: {
            frame: false,
          },
          html:
            '<iframe id="iframe_order_bodjobdtls" name="iframe_order_bodjobdtls" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' +
            src +
            '"; ></iframe>',
        },
      ],
    });
    return contactsPanel;
  };
  var branchstore = function (ind) {
    var store = new Ext.data.JsonStore({
      fields: ["br_Name", "br_ID"],
      url: modURL + "&op=getBranch",
      method: "post",
      autoLoad: true,
      root: "data",
      remoteSort: true,
      listeners: {
        load: function (thisstore, records, options) {
          Ext.getCmp("search_branch" + ind).setValue(
            _SESSION.finascop_current_branch_id
          );
          showData(ind);
        },
      },
      //totalProperty: 'totalCount'
    });
    return store;
  };
  var bodCollectionJobsPanel = function (id, ind, title) {
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: title,
      id: id,
      iconCls: "scheduled",
      buttonAlign: "right",
      items: [
        bodCollectionJobsGrid(ind),
        new Ext.Panel({
          title: "Order Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          cls: "left_side_panel",
          id: "panelbodCollectionJobsViewDetails",
          height: winsize.height * 0.6,
          items: [bodCollectionJobsDetailsView()],
          fbar: [
            {
              text: "Accept",
              id: "acceptCashBtn",
              tooltip: "Accept",
              hidden: true,
              handler: function () {
                var quor_id = Ext.getCmp("bod_collection_grid")
                  .getSelectionModel()
                  .getSelections()[0].data.quor_id;
                Ext.Msg.show({
                  title: "Confirm",
                  msg: "Do you want to accept?",
                  buttons: Ext.MessageBox.OKCANCEL,
                  fn: function (btn) {
                    if (btn == "ok") {
                      var action = "Delivery";
                      updateStatus(quor_id);
                    }
                  },
                });
              },
            },
          ],
        }),
      ],
      listeners: {
        afterrender: function () {
          if (_SESSION.typId == 3 || _SESSION.typId == 4) {
            Ext.getCmp("search_branch" + ind).setValue(_SESSION.typdetsid);
            Ext.getCmp("search_branch" + ind).hide();
            Ext.getCmp("branch_label" + ind).hide();
            Ext.getCmp("bod_collection_grid").getStore().baseParams.br_id =
              _SESSION.typdetsid;
          }

          if (ind == 2) {
            Ext.getCmp("save_schedule" + ind).hide();
          }
        },
      },
    });
    return panel;
  };
  var updateStatus = function (quor_id) {
    Ext.Ajax.request({
      url: modURL + "&op=acceptAmount",
      method: "POST",
      waitMsg: "Please wait...",
      params: {
        quor_id: quor_id,
      },
      success: function (res) {
        var tmp = Ext.decode(res.responseText);
        if (tmp.success == true) {
          Ext.Msg.hide();
          Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
            if (btn == "ok") showData(ind);
          });
        } else {
          Ext.Msg.hide();
          Ext.MessageBox.alert("Notification", tmp.msg);
        }
      },
    });
  };
  var PGChargeStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listPGCharges",
        method: "post",
      }),
      fields: ["pgChargeId", "pgChargeName", "pgChargePercentage","pgChargeIsDefault"],
      totalProperty: "totalCount",
      root: "data",
      autoLoad: true,
      listeners: {
        load: function () {},
      },
    });
    return store;
  };
  var masterPanelforPGChargesGrid = function (id) {
    var purpose_store = PGChargeStore();
    var branch_filter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "pgChargeName",
        },
      ],
    });
    branch_filter.remote = true;
    branch_filter.autoReload = true;

    var grid_panel = new Ext.grid.GridPanel({
      store: purpose_store,
      layout: "fit",
      frame: false,
      border: false,
      title: "PG Charges",
      plugins: [branch_filter],
      id: id,
      loadMask: true,
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Charge Name",
          sortable: true,
          hideable: true,
          dataIndex: "pgChargeName",
          tooltip: "Charge Name",
        },
        {
          header: "Percentage",
          sortable: true,
          hideable: true,
          dataIndex: "pgChargePercentage",
          tooltip: "Percentage",
        },
        {
          xtype: "actioncolumn",
          hideable: false,
          width: 50,
          items: [
            /*<?php if (user_access("order_bod", "deletePGChargeEntry")) { ?> */ {
              iconCls: "finascop_delete",
              tooltip: "Remove",
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                deletePGChargeEntry(record.get("pgChargeId"));
              },
            } /*<?php } ?> */,
            {
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var _isDefault = data.pgChargeIsDefault;
                if (_isDefault == 0) {
                  this.items[0].tooltip = "Set Default";
                  return "drinactive";
                } else {
                  this.items[0].tooltip = "Clear Default";
                  return "dractive";
                }
              },
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                  Ext.Ajax.request({
                    waitMsg: "Processing",
                    url: modURL,
                    params: {
                      op: "setPGChargeDefault",
                      pgChargeId: record.get("pgChargeId"),
                      pgChargeIsDefault: record.get("pgChargeIsDefault"),
                    },
                    failure: function (response, options) {
                      Ext.MessageBox.alert("Notification", ACTION_FAIL);
                    },
                    success: function (response, options) {
                      eval("var tmp=" + response.responseText);
                      if (tmp.success === true) {
                        Application.example.msg("Notification", tmp.msg);
                        Ext.getCmp(id)
                          .getStore()
                          .reload();
                      }
                    },
                  });
                
              },
            },
          ],
        },
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
        resize: updatePagination,
        afterrender: function () {},
      },
      tbar: [
        { html: "&nbsp;Charge Name : &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: "Charge Name",
          id: "pgChargeName",
          name: "pgChargeName",
          anchor: "98%",
          width: 250,
          tabIndex: 301,
          maxLength: 500,
        },
        { html: "&nbsp;Percentage : &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: "Percentage",
          id: "pgChargePercentage",
          name: "pgChargePercentage",
          anchor: "98%",
          width: 120,
          tabIndex: 301,
          maxLength: 500,
        },
        {
          xtype: "button",
          text: "Add",
          iconCls: "add",
          tabIndex: 303,
          handler: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
            if (!Ext.isEmpty(Ext.getCmp("pgChargeName").getValue())) {
              Ext.getBody().mask("Loading...");
              Ext.Ajax.request({
                url: modURL + "&op=savePGCharges",
                method: "POST",
                params: {
                  pgChargeName: Ext.getCmp("pgChargeName").getValue(),
                  pgChargePercentage:
                    Ext.getCmp("pgChargePercentage").getValue(),
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true) {
                    Application.example.msg("Success", tmp.msg);
                    Ext.getCmp(id).getStore().load();
                    Ext.getCmp("pgChargeName").reset();
                    Ext.getCmp("pgChargePercentage").reset();
                  } else {
                    Ext.MessageBox.alert("Notification", tmp.msg);
                  }
                  Ext.getBody().unmask();
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
              });
            } else {
              Ext.MessageBox.alert("Error", "Check the required fields");
            }
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: purpose_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
      }),
      stripeRows: true,
    });
    return grid_panel;
  };
  var deletePGChargeEntry = function (pgChargeId) {
    Ext.MessageBox.confirm(
      "Confirm",
      "Do you want to remove this entry?",
      function (btn, text) {
        if (btn == "yes") {
          Ext.Ajax.request({
            waitMsg: "Processing",
            method: "POST",
            url: modURL + "&op=deletePGChargeEntry",
            params: {
              pgChargeId: pgChargeId,
            },
            success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              if (tmp.success === true) {
                Application.example.msg("Success", "Removed entry.");
                Ext.getCmp("panelMasterMainPGChargesMaster").getStore().load();
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
  var SettlementDaystore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSettlementDays",
        method: "post",
      }),
      fields: ["sdId", "sdName", "sdDays","sdIsDefault"],
      totalProperty: "totalCount",
      root: "data",
      autoLoad: true,
      listeners: {
        load: function () {},
      },
    });
    return store;
  };
  var masterPanelforSettlementDaysGrid = function (id) {
    var purpose_store = SettlementDaystore();
    var branch_filter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "sdName",
        },
      ],
    });
    branch_filter.remote = true;
    branch_filter.autoReload = true;

    var grid_panel = new Ext.grid.GridPanel({
      store: purpose_store,
      layout: "fit",
      frame: false,
      border: false,
      title: "Settlement Days",
      plugins: [branch_filter],
      id: id,
      loadMask: true,
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Name",
          sortable: true,
          hideable: true,
          dataIndex: "sdName",
          tooltip: "Name",
        },
        {
          header: "Days",
          sortable: true,
          hideable: true,
          dataIndex: "sdDays",
          tooltip: "Days",
        },
        {
          xtype: "actioncolumn",
          hideable: false,
          width: 50,
          items: [
            /*<?php if (user_access("order_bod", "deletePGChargeEntry")) { ?> */ {
              iconCls: "finascop_delete",
              tooltip: "Remove",
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                deleteSettlementDaysEntry(record.get("sdId"));
              },
            } /*<?php } ?> */,
            {
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var _isDefault = data.sdIsDefault;
                if (_isDefault == 0) {
                  this.items[0].tooltip = "Set Default";
                  return "drinactive";
                } else {
                  this.items[0].tooltip = "Clear Default";
                  return "dractive";
                }
              },
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                  Ext.Ajax.request({
                    waitMsg: "Processing",
                    url: modURL,
                    params: {
                      op: "setSettlementDayseDefault",
                      sdId: record.get("sdId"),
                      sdIsDefault: record.get("sdIsDefault"),
                    },
                    failure: function (response, options) {
                      Ext.MessageBox.alert("Notification", ACTION_FAIL);
                    },
                    success: function (response, options) {
                      eval("var tmp=" + response.responseText);
                      if (tmp.success === true) {
                        Application.example.msg("Notification", tmp.msg);
                        Ext.getCmp(id)
                          .getStore()
                          .reload();
                      }
                    },
                  });
                
              },
            },
          ],
        },
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
        resize: updatePagination,
        afterrender: function () {},
      },
      tbar: [
        { html: "&nbsp;Name : &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: "Name",
          id: "sdName",
          name: "sdName",
          anchor: "98%",
          width: 250,
          tabIndex: 301,
          maxLength: 500,
        },
        { html: "&nbsp;Days : &nbsp;" },
        {
          xtype: "numberfield",
          fieldLabel: "Days",
          id: "sdDays",
          name: "sdDays",
          anchor: "98%",
          width: 120,
          tabIndex: 301,
          maxLength: 500,
        },
        {
          xtype: "button",
          text: "Add",
          iconCls: "add",
          tabIndex: 303,
          handler: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
            if (!Ext.isEmpty(Ext.getCmp("sdDays").getValue())) {
              Ext.getBody().mask("Loading...");
              Ext.Ajax.request({
                url: modURL + "&op=saveSettlementDays",
                method: "POST",
                params: {
                  sdName: Ext.getCmp("sdName").getValue(),
                  sdDays: Ext.getCmp("sdDays").getValue(),
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true) {
                    Application.example.msg("Success", tmp.msg);
                    Ext.getCmp(id).getStore().load();
                    Ext.getCmp("sdName").reset();
                    Ext.getCmp("sdDays").reset();
                  } else {
                    Ext.MessageBox.alert("Notification", tmp.msg);
                  }
                  Ext.getBody().unmask();
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
              });
            } else {
              Ext.MessageBox.alert("Error", "Check the required fields");
            }
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: purpose_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
      }),
      stripeRows: true,
    });
    return grid_panel;
  };
  var deleteSettlementDaysEntry = function (sdId) {
    Ext.MessageBox.confirm(
      "Confirm",
      "Do you want to remove this entry?",
      function (btn, text) {
        if (btn == "yes") {
          Ext.Ajax.request({
            waitMsg: "Processing",
            method: "POST",
            url: modURL + "&op=deleteSettlementDaysEntry",
            params: {
              sdId: sdId,
            },
            success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              if (tmp.success === true) {
                Application.example.msg("Success", "Removed entry.");
                Ext.getCmp("panelMasterMainSettlementDaysMaster").getStore().load();
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
  return {
    Cache: {},
    addbodCollectionJobs: function () {
      var main_panel_id = "main_panelbodCollectionJobs";
      var main_panel = Ext.getCmp(main_panel_id);
      var title = "Bank On Delivery";
      if (Ext.isEmpty(main_panel)) {
        main_panel = bodCollectionJobsPanel(main_panel_id, "bod", title);
        Application.UI.addTab(main_panel);
        main_panel.doLayout();
      } else {
        Application.UI.addTab(main_panel);
      }
    },
    ViewMode: function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var quor_id = arguments[0];
      Ext.getCmp("details_view_panel_bodCollectionJobs").show();
      Ext.getCmp("panelbodCollectionJobsViewDetails").doLayout();

      Ext.get("iframe_order_bodjobdtls").dom.src =
        modURL +
        "&op=order_details_viewbod&quor_id=" +
        quor_id +
        "&apikey=" +
        _SESSION.apikey +
        "&tstamp=" +
        t_stamp;
    },
    initPGChargesMaster: function () {
      var _PGChargesMasterPanelId = "panelMasterMainPGChargesMaster";
      var _masterPanelPGChargesMaster = Ext.getCmp(_PGChargesMasterPanelId);
      if (Ext.isEmpty(_masterPanelPGChargesMaster)) {
        _masterPanelPGChargesMaster = masterPanelforPGChargesGrid(
          _PGChargesMasterPanelId
        );
        Application.UI.addTab(_masterPanelPGChargesMaster);
        _masterPanelPGChargesMaster.doLayout();
      } else {
        Application.UI.addTab(_masterPanelPGChargesMaster);
      }
    },initSettlementDaysMaster: function () {
      var _SettlementDaysMasterPanelId = "panelMasterMainSettlementDaysMaster";
      var _masterPanelSettlementDaysMaster = Ext.getCmp(_SettlementDaysMasterPanelId);
      if (Ext.isEmpty(_masterPanelSettlementDaysMaster)) {
        _masterPanelSettlementDaysMaster = masterPanelforSettlementDaysGrid(
          _SettlementDaysMasterPanelId
        );
        Application.UI.addTab(_masterPanelSettlementDaysMaster);
        _masterPanelSettlementDaysMaster.doLayout();
      } else {
        Application.UI.addTab(_masterPanelSettlementDaysMaster);
      }
    },
  };
  //////////////////////////////EO Public Area///////////////////////////////
})();
