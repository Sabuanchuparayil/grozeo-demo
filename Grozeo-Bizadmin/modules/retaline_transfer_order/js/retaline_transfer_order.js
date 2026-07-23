Application.RetalineTransferOrder = (function () {
  var winsize = Ext.getBody().getViewSize();
  var recs_per_page = 20;
  var count = 0;
  var modURL = "?module=retaline_transfer_order";
  function updatePagination(cmp) {
    recs_per_page = finascop_update_recs_per_page(cmp);
  }
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var qtipRenderer = function (
    value,
    metadata,
    record,
    rowIndex,
    colIndex,
    store
  ) {
    metadata.attr = 'ext:qtip="' + value + '"  ext:qwidth="auto" ';
    return value;
  };
  var gridSelectionChanged = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelAddnewTransferOrderdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelAddnewTransferOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_id;
      var fsto_status = Ext.getCmp("gridpanelAddnewTransferOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_status;
      var fsto_isPurchaseReturn = Ext.getCmp("gridpanelAddnewTransferOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_isPurchaseReturn;
      var fsto_ordertype = Ext.getCmp("gridpanelAddnewTransferOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_ordertype;
      console.log("fsto_status", fsto_status);
      console.log("fsto_isPurchaseReturn", fsto_isPurchaseReturn);
      Application.RetalineTransferOrder.ViewMode(ID);

      switch (fsto_status) {
        case "4":
          if (fsto_isPurchaseReturn == 0) {
            Ext.getCmp("revoke_order_forpickr").show();
            Ext.getCmp("view_polled_history").show();
          } else {
            Ext.getCmp("view_polled_history").hide();
          }

          Ext.getCmp("assign_order_pickr").hide();
          Ext.getCmp("print_order").show();
          Ext.getCmp("convert_dispatch").hide();
          Ext.getCmp("manual_packing").hide();
          Ext.getCmp("boxdetails").hide();
          /*     <?php if (user_access("retaline_transfer_order", "updateTotalQtyinPackingOrder")) { ?> */
          Ext.getCmp("edit_order_items").hide();
          /*<?php } ?> */
          console.log("4", fsto_status);
          break;
        case "5":
          console.log("5", fsto_status);
          Ext.getCmp("boxdetails").hide();
          Ext.getCmp("revoke_order_forpickr").hide();
          Ext.getCmp("assign_order_pickr").hide();
          Ext.getCmp("manual_packing").hide();
          Ext.getCmp("print_order").show();
          if (fsto_isPurchaseReturn == 0) {
            Ext.getCmp("convert_dispatch").show();
            Ext.getCmp("view_polled_history").show();
          } else {
            Ext.getCmp("view_polled_history").hide();
          }

          /*     <?php if (user_access("retaline_transfer_order", "updateTotalQtyinPackingOrder")) { ?> */
          Ext.getCmp("edit_order_items").hide();
          /*<?php } ?> */
          break;
        case "6":
          Ext.getCmp("manual_packing").show();
          Ext.getCmp("revoke_order_forpickr").hide();
          Ext.getCmp("convert_dispatch").hide();
          Ext.getCmp("print_order").show();
          Ext.getCmp("boxdetails").hide();

          if (fsto_isPurchaseReturn == 0) {
            Ext.getCmp("assign_order_pickr").show();
            /*     <?php if (user_access("retaline_transfer_order", "updateTotalQtyinPackingOrder")) { ?> */
            if (fsto_ordertype == "B2C") {
              Ext.getCmp("edit_order_items").hide();
            } else {
              Ext.getCmp("edit_order_items").show();
            }
            /*<?php } ?> */
            Ext.getCmp("view_polled_history").show();
          } else {
            Ext.getCmp("view_polled_history").hide();
          }

          break;
        case "10":
          Ext.getCmp("print_order").show();
          Ext.getCmp("revoke_order_forpickr").hide();
          Ext.getCmp("assign_order_pickr").hide();
          Ext.getCmp("convert_dispatch").hide();
          Ext.getCmp("manual_packing").hide();
          Ext.getCmp("boxdetails").hide();
          /*     <?php if (user_access("retaline_transfer_order", "updateTotalQtyinPackingOrder")) { ?> */
          Ext.getCmp("edit_order_items").hide();
          /*<?php } ?> */
          if (fsto_isPurchaseReturn == 0) {
            Ext.getCmp("view_polled_history").show();
          } else {
            Ext.getCmp("view_polled_history").hide();
          }
          break;
          case "12":
          Ext.getCmp("print_order").show();
          Ext.getCmp("revoke_order_forpickr").hide();
          Ext.getCmp("assign_order_pickr").hide();
          Ext.getCmp("convert_dispatch").hide();
          Ext.getCmp("manual_packing").hide();
          Ext.getCmp("boxdetails").show();
          /*     <?php if (user_access("retaline_transfer_order", "updateTotalQtyinPackingOrder")) { ?> */
          Ext.getCmp("edit_order_items").hide();
          /*<?php } ?> */
          if (fsto_isPurchaseReturn == 0) {
            Ext.getCmp("view_polled_history").show();
          } else {
            Ext.getCmp("view_polled_history").hide();
          }
          break;
        default:
          console.log("default", fsto_status);
          Ext.getCmp("revoke_order_forpickr").hide();
          Ext.getCmp("assign_order_pickr").hide();
          Ext.getCmp("print_order").show();
          Ext.getCmp("convert_dispatch").hide();
          Ext.getCmp("manual_packing").hide();
          Ext.getCmp("boxdetails").hide();
          Ext.getCmp("view_polled_history").show();
          /*     <?php if (user_access("retaline_transfer_order", "updateTotalQtyinPackingOrder")) { ?> */
          Ext.getCmp("edit_order_items").hide();
          /*<?php } ?> */
          break;
      }
    }
  };
  var gridSelectionChangedSch = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelScheduledOrderdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelScheduledOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_id;
      var fsto_status = Ext.getCmp("gridpanelScheduledOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_status;
      var fsto_isPurchaseReturn = Ext.getCmp("gridpanelScheduledOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_isPurchaseReturn;
      console.log("fsto_status", fsto_status);
      console.log("fsto_isPurchaseReturn", fsto_isPurchaseReturn);
      Application.RetalineTransferOrder.ViewModeScheduled(ID);
    }
  };
  var transferOrderStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=listTransferOrderData",
      fields: [
        "fsto_id",
        "fsto_uid",
        "fstr_id",
        "fsto_status",
        "fsto_source",
        "fsto_sourcetype",
        "fsto_destination",
        "fsto_destinationtype",
        "fsto_source",
        "fsto_destination",
        "fsto_type",
        "br_parentPacking",
        "fsto_ItemWeight",
        "fsto_ItemVolume",
        "fsto_destinationName",
        "fsto_sourceName",
        "fsto_statusName",
        "fsto_ordertype",
        "fsto_isPurchaseReturn",
        "fsto_isalreadypacked",
        "ownInvoice",
        "branchType",
        {
          name: "fstoCreatedOn",
          type: "date",
          dateFormat: "d-m-Y",
        },
      ],
      totalProperty: "totalCount",
      root: "data",
      idProperty: "fsto_id",
      remoteSort: true,
      autoLoad: false,
      sortInfo: {
        field: "fstoCreatedOn",
        direction: "DESC",
      },
      listeners: {
        beforeload: function () {
          this.baseParams.branchName =
            Ext.getCmp("comboxbranchnames").getValue();
        },
        load: function (store, e) {
          Ext.getCmp("gridpanelAddnewTransferOrderdata").getView().refresh();
          Ext.getCmp("gridpanelAddnewTransferOrderdata")
            .getSelectionModel()
            .selectRow(0);
          if (
            !Ext.isEmpty(
              Ext.getCmp("gridpanelAddnewTransferOrderdata")
                .getSelectionModel()
                .getSelections()
            )
          ) {
            var ID = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            Application.RetalineTransferOrder.Cache.fsto_id = ID;
            Application.RetalineTransferOrder.ViewMode(
              Application.RetalineTransferOrder.Cache.fsto_id
            );
          }
        },
      },
    });
    return store;
  };
  var fstoGridActionMenu = function () {
    return new Ext.menu.Menu({
      items: [
        /*     <?php if (user_access("retaline_transfer_order", "updateTotalQtyinPackingOrder")) { ?> */ {
          //iconCls: 'edit',
          text: "Edit Packing Order",
          id: "edit_order_items",
          hidden: true,
          handler: function () {
            var record = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0];
            var fsto_id = record.data.fsto_id;
            var fsto_source = record.data.fsto_source;
            var fsto_ordertype = record.data.fsto_ordertype;
            Application.RetalineTransferOrder.editOrderItems(
              fsto_id,
              fsto_source,
              fsto_ordertype
            );
          },
        },
        /*<?php } ?> */ {
          //iconCls: 'application_view_detail',
          id: "view_polled_history",
          text: "View Polled History",
          id: "view_polled_history",
          handler: function () {
            var record = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0];
            var fsto_id = record.data.fsto_id;
            var fsto_source = record.data.fsto_source;
            var fsto_ordertype = record.data.fsto_ordertype;
            Application.RetalineTransferOrder.viewPolledHistory(
              fsto_id,
              fsto_source,
              fsto_ordertype
            );
          },
        },
        {
          text: "View Order Details",
          handler: function () {
            var record = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0];
            var fsto_id = record.data.fsto_id;
            var fsto_source = record.data.fsto_source;
            var fsto_ordertype = record.data.fsto_ordertype;
            Application.RetalineTransferOrder.viewItems(
              fsto_id,
              fsto_source,
              fsto_ordertype
            );
          },
        },
        {
          text: "Revoke",
          //iconCls: 'list_users',
          tooltip: "Revoke",
          id: "revoke_order_forpickr",
          hidden: true,
          handler: function () {
            //                                var record = grid.getStore().getAt(rowIndex);
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_source = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_source;
            var fsto_ordertype = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_ordertype;
            Ext.MessageBox.confirm(
              "Confirm",
              "Do you wish to revoke this?",
              function (btn, text) {
                if (btn == "yes") {
                  Application.RetalineTransferOrder.revokeOrder(
                    fsto_id,
                    fsto_source,
                    fsto_ordertype
                  );
                }
              }
            );
          },
        },
        {
          text: "Assign Order Picker",
          //                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
          //iconCls: 'list_users',
          tooltip: "Assign Order Picker",
          id: "assign_order_pickr",
          hidden: true,
          handler: function () {
            //                                var record = grid.getStore().getAt(rowIndex);
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_uid = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_uid;
            var fsto_source = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_source;
            var fsto_sourceName = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_sourceName;
            var br_parentPacking = Ext.getCmp(
              "gridpanelAddnewTransferOrderdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.br_parentPacking;
            if (br_parentPacking == 0) {
              AddNewOrderpickerWindow(
                fsto_id,
                fsto_uid,
                fsto_source,
                fsto_sourceName
              );
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Update bags count on manual packing and proceed."
              );
            }
          },
        },
        {
          text: "Convert",
          //                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
          //iconCls: 'dispatch',
          tooltip: "Convert to dispatch",
          id: "convert_dispatch",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_source = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_source;
            var fsto_ordertype = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_ordertype;
            Ext.MessageBox.confirm(
              "Confirm",
              "Do you wish to convert this?",
              function (btn, text) {
                if (btn == "yes") {
                  Application.RetalineTransferOrder.convertOrder(
                    fsto_id,
                    fsto_source,
                    fsto_ordertype
                  );
                }
              }
            );
          },
        },
        {
          text: "Manual Packing",
          //                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
          //iconCls: 'packing',
          tooltip: "Manual Packing",
          id: "manual_packing",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_destination = Ext.getCmp(
              "gridpanelAddnewTransferOrderdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.fsto_destination;
            var fsto_ordertype = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_ordertype;
            var fsto_uid = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_uid;
            var fsto_isalreadypacked = Ext.getCmp(
              "gridpanelAddnewTransferOrderdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.fsto_isalreadypacked;
            var ownInvoice = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.ownInvoice;
            var branchType = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.branchType;
            manualPackingWindow(
              fsto_id,
              fsto_destination,
              fsto_ordertype,
              fsto_uid,
              fsto_isalreadypacked,
              ownInvoice,
              branchType
            );
          },
        },{
          text: "Box Details",
          tooltip: "Box Details",
          id: "boxdetails",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_id;
            var fsto_ordertype = Ext.getCmp("gridpanelAddnewTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_ordertype;
            Ext.Ajax.request({
              url: modURL + "&op=getBoxDetails",
              method: "POST",
              params: {
                fsto_id: fsto_id,
                fsto_ordertype: fsto_ordertype,
              },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success == true) {
                  Application.example.msg("Success", tmp.msg);
                  Application.RetalineTransferOrder.addPackageWindow(tmp.packcount,tmp.data,fsto_ordertype,fsto_id);
                } else {
                  Ext.Msg.alert("Error", tmp.error.msg);
                }
              },
              failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert("Error", tmp.message);
              },
            });
          }

        },
        {
          text: "Print Order List",
          //                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
          //iconCls: 'print_invoice',
          tooltip: "Print Order List",
          id: "print_order",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            printOrder(fsto_id);
          },
        },{
          text: "Package Slip",
          tooltip: "Package Slip",
          id: "packageSlip",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_id;
            var fsto_ordertype = Ext.getCmp("gridpanelAddnewTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_ordertype;
            printPackingSlip(fsto_id,fsto_ordertype);
            
          }

        }
      ],
    });
  };
  var schedGridActionMenu = function (type) {
    return new Ext.menu.Menu({
      items: [
        {
          text: "Move to Packing Order",
          tooltip: "Move to Packing Order",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelScheduledOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_status = Ext.getCmp("gridpanelScheduledOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_status;
            var fsto_ordertype = Ext.getCmp("gridpanelScheduledOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_ordertype;
            var fstr_id = Ext.getCmp("gridpanelScheduledOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fstr_id;
            if (fsto_status == 11) {
              Ext.Ajax.request({
                url: modURL + "&op=moveToPackingOrder",
                method: "POST",
                params: {
                  fsto_id: fsto_id,
                  fsto_status: fsto_status,
                  fstr_id: fstr_id,
                  fsto_ordertype: fsto_ordertype,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true) {
                    Application.example.msg("Success", tmp.msg);
                    Ext.getCmp("gridpanelScheduledOrderdata").getStore().load();

                    showData("dj");
                  } else {
                    Ext.Msg.alert("Error", tmp.error.msg);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", tmp.message);
                },
              });
            }
          },
        },
        {
          text: "Print Order List",
          tooltip: "Print Order List",
          id: "print_order" + type,
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelScheduledOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            printOrder(fsto_id);
          },
        },{
          text: "Package Slip",
          tooltip: "Package Slip",
          id: "packageSlip",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelScheduledOrderdata").getSelectionModel().getSelections()[0].data.fsto_id;
            var fsto_ordertype = Ext.getCmp("gridpanelScheduledOrderdata").getSelectionModel().getSelections()[0].data.fsto_ordertype;
            printPackingSlip(fsto_id,fsto_ordertype);
            
          }

        }
      ],
    });
  };
  var retalineTransferOrderGrid = function () {
    var _Store = transferOrderStore();
    var fstoGridActionColumn = fstoGridActionMenu("po");
    var BranchStore = new Ext.data.JsonStore({
      fields: ["br_ID", "br_Name"],
      url: modURL + "&op=getBranchName",
      autoLoad: true,
      method: "post",
    });

    var transferOrder_filter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "fsto_uid",
        },
        {
          type: "string",
          dataIndex: "fsto_sourceName",
        },
        {
          type: "string",
          dataIndex: "fsto_destinationName",
        },
        {
          type: "string",
          dataIndex: "fsto_type",
        },
        {
          type: "string",
          dataIndex: "fsto_ordertype",
        },
        {
          type: "date",
          dataIndex: "fstoCreatedOn",
        },
        {
          type: "list",
          options: PCORDER_STATUS_OPTIONS,
          phpMode: true,
          dataIndex: "fsto_statusName",
        },
      ],
    });
    transferOrder_filter.remote = true;
    transferOrder_filter.autoReload = true;
    var _TransOrdergridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _Store,
      title: "",
      //iconCls: 'finascop_purchase',
      autoScroll: true,
      width: winsize.width * 0.4,
      height: 250,
      bodyStyle: { "background-color": "white" },
      //selModel: ck_selection,
      id: "gridpanelAddnewTransferOrderdata",
      plugins: [transferOrder_filter],
      columns: [
        {
          header: "TO No.",
          dataIndex: "fsto_uid",
          sortable: true,
          tooltip: "TO No.",
          hideable: true,
        },
        {
          header: "Consigner",
          dataIndex: "fsto_sourceName",
          sortable: true,
          tooltip: "Consigner",
        },
        {
          header: "Consignee",
          dataIndex: "fsto_destinationName",
          sortable: true,
          tooltip: "To",
          hideable: true,
        },
        {
          header: "Date",
          dataIndex: "fstoCreatedOn",
          sortable: true,
          tooltip: "Date",
          hideable: true,
          renderer: function (value, metadata, record) {
            dateret = Ext.util.Format.date(value, "d-m-Y");
            return dateret;
          },
        },
        {
          header: "Weight",
          dataIndex: "fsto_ItemWeight",
          sortable: true,
          tooltip: "Weight",
          hideable: true,
          hidden: true,
          width: 80,
        },
        {
          header: "Volume",
          dataIndex: "fsto_ItemVolume",
          sortable: true,
          tooltip: "Volume",
          hideable: true,
          hidden: true,
          width: 80,
        },
        {
          header: "Type",
          dataIndex: "fsto_ordertype",
          sortable: true,
          tooltip: "Order Type",
          hideable: true,
        },
        {
          header: "Status",
          dataIndex: "fsto_statusName",
          sortable: true,
          tooltip: "Status",
          hideable: true,
          width: 110,
          renderer: qtipRenderer,
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          // icon: './resources/images/submenuicons/action.png',
          items: [
            {
              getClass: function (v, meta, rec) {
                if (rec.get("fsto_status") > 1) {
                  this.items[0].tooltip = "Choose Actions";
                  return "actioncol";
                } else {
                  return "hideicon";
                }
              },
            },
            {
              getClass: function (v, meta, rec) {
                if (rec.get("fsto_status") == 1) {
                  return "color-trigger";
                } else {
                  return "hideicon";
                }
              },
            },
          ],
          listeners: {
            click: function (a, grid, rowindex, e) {
              var record = grid.store.getAt(rowindex);
              grid.getSelectionModel().selectRow(rowindex);
              if (record.data.fsto_status > 1) {
                fstoGridActionColumn.showAt(e.getXY());
              }
            },
          },
          //                    ,
        },
        //                {
        //                    xtype: 'actioncolumn',
        //                    header: 'Actions',
        //                    hideable: false,
        //                    sortable: false,
        //                    items: [
        //                        {
        //                            iconCls: 'list_users',
        //                            tooltip: 'View Polled History',
        //                            handler: function (grid, rowIndex, colIndex) {
        //                                var record = grid.store.getAt(rowIndex);
        //                                Application.RetalineTransferOrder.viewPolledHistory(record.get('fsto_id'), record.get('fsto_source'), record.get('fsto_ordertype'));
        //                            }
        //                        }, {
        //                            iconCls: 'my-icon96',
        //                            tooltip: 'View Item Details',
        //                            handler: function (grid, rowIndex, colIndex) {
        //                                var record = grid.store.getAt(rowIndex);
        //                                Application.RetalineTransferOrder.viewItems(record.get('fsto_id'), record.get('fsto_source'), record.get('fsto_ordertype'));
        //                            }
        //                        }
        //                    ]
        //                }
      ],
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChanged,
        },
      }),
      listeners: {
        viewready: updatePagination,
        afterrender: function () {
          if (_SESSION.br_PyramidLevel == 1) {
            Ext.getCmp("comboxbranchnames").show();
            Ext.getCmp("textboxBrmCpdDataeditCSS").hide();
          } else {
            Ext.getCmp("textboxBrmCpdDataeditCSS").show();
            Ext.getCmp("textboxBrmCpdDataeditCSS").setValue(
              _SESSION.current_branch
            );
            Ext.getCmp("comboxbranchnames").hide();
            Ext.getCmp("showBtnto").hide();
          }
        },
      },
      tbar: [
        {
          html: "&nbsp;BRANCH : &nbsp;",
        },
        {
          xtype: "textfield",
          id: "textboxBrmCpdDataeditCSS",
          name: "textboxBrmCpdDataeditCSS",
          anchor: "98%",
          tabIndex: 1,
          maxLength: 20,
          readOnly: true,
          hidden: true,
        },
        {
          xtype: "combo",
          id: "comboxbranchnames",
          name: "comboxbranchnames",
          mode: "local",
          typeAhead: true,
          forceSelection: true,
          emptyText: "Select Branch",
          fieldLabel: "Branch Name",
          editable: true,
          anchor: "97%",
          store: BranchStore,
          triggerAction: "all",
          minChars: 2,
          displayField: "br_Name",
          valueField: "br_ID",
          hiddenName: "comboxbranchnames",
          listeners: {
            select: function () {
              Ext.getCmp("gridpanelAddnewTransferOrderdata")
                .getStore()
                .removeAll();
            },
          },
        },
        {
          xtype: "button",
          text: "Show",
          iconCls: "show",
          id: "showBtnto",
          style: "padding-left: 10px;",
          handler: function () {
            var branchName = Ext.getCmp("comboxbranchnames").getValue();
            Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getStore()
              .load({
                params: {
                  branchName: branchName,
                },
              });
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _Store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
        plugins: [transferOrder_filter],
      }),
    });
    return _TransOrdergridPanel;
  };
  var printOrder = function (fsto_id) {
    var printOrderPanel = createprintOrderPanel(fsto_id);
    var printOrderWindow = Ext.getCmp("printOrderWindow");
    if (Ext.isEmpty(printOrderWindow)) {
      printOrderWindow = new Ext.Window({
        id: "printOrderWindow",
        plain: true,
        modal: true,
        constrain: true,
        resizable: false,
        title: "Retaline",
        width: 950,
        autoHeight: true,
        items: [printOrderPanel],
        buttons: [
          {
            text: "Cancel",
            iconCls: "my-icon61",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            handler: function () {
              printOrderWindow.close();
            },
          },
          {
            text: "Print",
            icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
            iconCls: "my-icon141",
            handler: function () {
              var params = {
                fsto_id: fsto_id,
                action: 1,
              };
              iframeRequest.focus();
              iframeRequest.print();
              printOrderWindow.close();
            },
          },
        ],
        listeners: {
          close: function () {
            Ext.getCmp("gridpanelAddnewTransferOrderdata").getStore().load();
          },
        },
      });
    }
    printOrderWindow.doLayout();
    printOrderWindow.show();
    printOrderWindow.center();
  };
  var createprintOrderPanel = function (fsto_id) {
    var t = new Date();
    var mode = "printOrder";
    var t_stamp = t.format("YmdHis");
    var src =
      "?module=retaline_transfer_order&op=orderPrint&fsto_id=" +
      fsto_id +
      "&mode=" +
      mode +
      "&apikey=" +
      _SESSION.apikey +
      "&tstamp=" +
      t_stamp;
    var myPanel = new Ext.Panel({
      layout: "border",
      height: 500,
      id: "printOrderPanel",
      items: [
        {
          region: "center",
          border: false,
          html:
            '<iframe src="' +
            src +
            '" id="iframeRequest" name="iframeRequest" ' +
            'width="100%" height="100%" style="border:none">',
        },
      ],
    });
    return myPanel;
  };

  var orderPickergrid = function (fsto_source, fsto_sourceName) {
    var _orderPickerStore = orderPickerStore(fsto_source);
    var _orderPickergridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _orderPickerStore,
      //iconCls: 'money',
      autoScroll: true,
      width: 400,
      height: 250,
      bodyStyle: { "background-color": "white" },
      //selModel: ck_selection,
      id: "gridpanelAddOrderPicker",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Name",
          dataIndex: "name",
          sortable: true,
          tooltip: "Name",
          hideable: true,
        },
        {
          header: "Mobile",
          dataIndex: "phone",
          sortable: true,
          tooltip: "Mobile",
          hideable: true,
        },
        {
          header: "Status",
          dataIndex: "liveStatus",
          sortable: true,
          tooltip: "Status",
          hideable: true,
        },
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {
          if (record.get("is_offline") == 0) {
            return "finascop_indicateColPALEGREEN";
          }
        },
      },
      tbar: [
        {
          xtype: "combo",
          displayField: "br_Name",
          valueField: "br_ID",
          mode: "local",
          id: "cpdnameid",
          forceSelection: true,
          fieldLabel: "Store",
          emptyText: "Select Branch",
          anchor: "98%",
          typeAhead: true,
          triggerAction: "all",
          lazyRender: true,
          editable: true,
          minChars: 2,
          tabIndex: 102,
          store: rtrBranchStore(fsto_source),
          value: fsto_sourceName,
          hideTrigger: true,
          listeners: {
            select: function () {
              Ext.getCmp("gridpanelAddOrderPicker")
                .getStore()
                .load({
                  params: {
                    pickerBranch: Ext.getCmp("cpdnameid").getValue(),
                  },
                });
            },
          },
        },
      ],
    });
    return _orderPickergridPanel;
  };

  var transferOrderActionMenu = function (
    fsto_id,
    grid,
    rowIndex,
    fsto_isalreadypacked
  ) {
    var transferOrderActionMenu = {};
    var menu = Ext.getCmp("transferOrderActionMenu");
    if (Ext.isEmpty(menu)) {
      transferOrderActionMenu = new Ext.menu.Menu({
        id: "transferOrderActionMenu",
      });
      var autoDestroy = true;
      transferOrderActionMenu.removeAll(autoDestroy);

      if (_SESSION.IS_RETALINE_LITE != 1) {
        transferOrderActionMenu.addMenuItem({
          id: "view_barcode",
          handler: function () {
            var ID = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_ItemId = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.fsto_ItemId;
            Application.RetalineTransferOrder.viewBarcodeWindow(
              ID,
              fsto_ItemId
            );
            Ext.getCmp("transferOrderActionMenu").destroy();
          },
          text: "View Barcode",
        });
      }
      var rec = Ext.getCmp("gridpanelManualPackingGrid")
        .getSelectionModel()
        .getSelections()[0].data;
      if (
        _SESSION.IS_RETALINE_LITE == "1" &&
        rec.fsto_pkdQty < rec.fsto_ItemQty &&
        fsto_isalreadypacked == 0
      ) {
        transferOrderActionMenu.addMenuItem({
          id: "marked_as_packed",
          text: "Mark As Packed",
          handler: function () {
            var record = Ext.getCmp("gridpanelManualPackingGrid")
              .getStore()
              .getAt(rowIndex); //gridpanelManualPackingGrid
            var fsto_ItemId = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.fsto_ItemId;
            var fsto_ItemQty = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.fsto_ItemQty;
            var fsto_isConversionRequired = Ext.getCmp(
              "gridpanelManualPackingGrid"
            )
              .getSelectionModel()
              .getSelections()[0].data.fsto_isConversionRequired;
            var stit_ParentItemId = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.stit_ParentItemId;
            var stit_ConvertCalcMode = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.stit_ConvertCalcMode;
            var stit_ConvertCalcRate = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.stit_ConvertCalcRate;
            var parentItemName = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.parentItemName;
            var branchType = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.branchType;
            var br_parentPacking = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.br_parentPacking;
            upPackedQty(
              fsto_id,
              fsto_ItemId,
              fsto_ItemQty,
              record,
              stit_ParentItemId,
              stit_ConvertCalcMode,
              stit_ConvertCalcRate,
              parentItemName,
              branchType,
              br_parentPacking
            );
            Ext.getCmp("transferOrderActionMenu").destroy();
          },
        });
      }
    } else {
      transferOrderActionMenu = menu;
    }

    return transferOrderActionMenu;
  };
  var manualPackingGrid = function (
    fsto_id,
    barcodesearch_field,
    fsto_destination,
    fsto_isalreadypacked
  ) {
    var _manualPackingStore = manualPackingStore(
      fsto_id,
      barcodesearch_field,
      fsto_destination,
      fsto_isalreadypacked
    );
    var _manualPackingGridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _manualPackingStore,
      //iconCls: 'money',
      autoScroll: true,
      width: winsize.width * 0.7,
      height: winsize.height * 0.6,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChanged,
        },
      }),
      bodyStyle: { "background-color": "white" },
      id: "gridpanelManualPackingGrid",
      columns: [
        {
          header: "Sl No",
          dataIndex: "slNo",
          width: 30,
          sortable: true,
          tooltip: "Sl No",
          hideable: true,
        },
        {
          header: "Item",
          width: 300,
          dataIndex: "item_name",
          sortable: true,
          tooltip: "Item",
          hideable: true,
        },
        {
          header: "MRP",
          dataIndex: "mrp",
          width: 50,
          sortable: true,
          tooltip: "MRP",
          hideable: true,
        },
        {
          header: "No of Items",
          width: 50,
          dataIndex: "fsto_ItemQty",
          sortable: true,
          tooltip: "No of Items",
          hideable: true,
        },
        {
          header: "Items Picked",
          sortable: true,
          width: 50,
          dataIndex: "fsto_pkdQty",
          hideable: false,
          tooltip: "Packed Qty",
          groupable: false,
          editor: {
            allowBlank: false,
            xtype: "numberfield",
          },
        },
        {
          header: "Required Qty",
          width: 50,
          dataIndex: "stit_ConvertCalcRate",
          sortable: true,
          tooltip: "Required Qty",
          hideable: true,
        },
        {
          header: "Picked Qty",
          width: 50,
          dataIndex: "fsto_stockValue",
          sortable: true,
          tooltip: "Picked Qty",
          hideable: true,
        },
        {
          xtype: "actioncolumn",
          //header: 'Action',
          hideable: true,
          iconCls: "downarrow",
          tooltip: "Choose Actions",
          listeners: {
            click: function (a, grid, rowIndex, e) {
              grid.getSelectionModel().selectRow(rowIndex);
              transferOrderActionMenu(
                fsto_id,
                grid,
                rowIndex,
                fsto_isalreadypacked
              ).showAt(e.getXY());
            },
          },
        },
        /*,
                 {
                 xtype: 'actioncolumn',
                 header: 'Action',
                 hideable: false,
                 sortable: false,
                 groupable: false,
                 tooltip: 'Action',
                 items: [
                 
                 {
                 getClass: function (v, meta, rec) {
                 if (_SESSION.IS_RETALINE_LITE != 1) {
                 this.items[0].tooltip = 'View Barcode';
                 return 'barcode_view';
                 }
                 else {
                 return 'hideicon';
                 }
                 },
                 id: 'view_barcode',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.getStore().getAt(rowIndex);
                 var ID = Ext.getCmp('gridpanelManualPackingGrid').getSelectionModel().getSelections()[0].data.fsto_id;
                 var fsto_ItemId = Ext.getCmp('gridpanelManualPackingGrid').getSelectionModel().getSelections()[0].data.fsto_ItemId;
                 Application.RetalineTransferOrder.viewBarcodeWindow(ID, fsto_ItemId);
                 }
                 }, 
                 {
                 id: 'marked_packed',
                 getClass: function (v, meta, rec) {
                 console.log('fsto_pkdQtymp', rec.get('fsto_pkdQty'));
                 console.log('fsto_ItemQtymp', rec.get('fsto_ItemQty'))
                 if ((_SESSION.IS_RETALINE_LITE == '1') && (rec.get('fsto_pkdQty') < rec.get('fsto_ItemQty')) && (fsto_isalreadypacked == 0)) {
                 this.items[1].tooltip = 'Mark As Packed';
                 return 'packing-add';
                 }
                 else {
                 return 'hideicon';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.getStore().getAt(rowIndex);
                 var ID = Ext.getCmp('gridpanelManualPackingGrid').getSelectionModel().getSelections()[0].data.fsto_id;
                 var fsto_ItemId = Ext.getCmp('gridpanelManualPackingGrid').getSelectionModel().getSelections()[0].data.fsto_ItemId;
                 var fsto_ItemQty = Ext.getCmp('gridpanelManualPackingGrid').getSelectionModel().getSelections()[0].data.fsto_ItemQty;
                 upPackedQty(fsto_id, fsto_ItemId, fsto_ItemQty, record);
                 
                 }
                 }
                 ]
                 }*/
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {
          if (record.get("stit_ParentItemId") > 0) {
            return "finascop_indicateColLIGHTORANGE";
          } else {
            return "";
          }
        },
      },
      tbar: [
        {
          html: "&nbsp;Enter/Scan Barcode : &nbsp;",
          id: "barcodeBr_label",
        },
        {
          xtype: "textfield",
          id: "barcode_id",
          name: "barcode_id",
          fieldLabel: "Barcode",
          style: {
            "font-size": "28px",
          },
          //emptyText: 'Enter Barcode',
          height: 50,
          anchor: "98%",
          tabIndex: 102,
          listeners: {
            change: function () {
              var barcodesearch_field = Ext.getCmp("barcode_id").getValue();
              var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
                .getSelectionModel()
                .getSelections()[0].data.fsto_id;
              var fsto_source = Ext.getCmp("gridpanelAddnewTransferOrderdata")
                .getSelectionModel()
                .getSelections()[0].data.fsto_source;
              var fsto_ItemId = Ext.getCmp("gridpanelManualPackingGrid")
                .getSelectionModel()
                .getSelections()[0].data.fsto_ItemId;
              if (barcodesearch_field) {
                Ext.Ajax.request({
                  url: modURL + "&op=barcodeCheck",
                  method: "POST",
                  params: {
                    barcodesearch_field: barcodesearch_field,
                    fsto_id: fsto_id,
                    fsto_source: fsto_source,
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success == true) {
                      var tmp = Ext.decode(response.responseText);
                      var item_id = tmp.item_id;
                      var rowIndex = Ext.getCmp(
                        "gridpanelManualPackingGrid"
                      ).store.find("fsto_ItemId", item_id);
                      var grecord = Ext.getCmp(
                        "gridpanelManualPackingGrid"
                      ).store.getAt(rowIndex);
                      var fsto_pkdQty_value = grecord.data.fsto_pkdQty;
                      var fsto_ItemQty = grecord.data.fsto_ItemQty;

                      if (fsto_ItemQty > fsto_pkdQty_value) {
                        var new_fsto_pkdQty = fsto_pkdQty_value + 1;
                        grecord.set("fsto_pkdQty", new_fsto_pkdQty);
                        Ext.getCmp("correct_id").show();
                        Ext.getCmp("wrong_id").hide();
                        Ext.getCmp("barcode_id").reset();
                        setTimeout(function () {
                          Ext.getCmp("correct_id").hide();
                        }, 1000);
                      } else {
                        Ext.MessageBox.alert(
                          "Notification",
                          "Item Quantity limit exceeded"
                        );
                        Ext.getCmp("wrong_id").show();
                        Ext.getCmp("correct_id").hide();
                        Ext.getCmp("barcode_id").reset();
                        setTimeout(function () {
                          Ext.getCmp("wrong_id").hide();
                        }, 5000);
                      }

                      var current_pkdQty_value = grecord.data.fsto_pkdQty;
                      console.log("current_pkdQty_value", current_pkdQty_value);
                      var itemStore = Ext.getCmp(
                        "gridpanelManualPackingGrid"
                      ).getStore();
                      var itemQty = itemStore.sum("fsto_ItemQty");
                      var pkdQty = itemStore.sum("fsto_pkdQty");
                      if (itemQty == pkdQty) {
                        Ext.getCmp("submit_button").enable();
                      } else {
                        Ext.getCmp("submit_button").disable();
                      }
                      Ext.getCmp("barcode_id").focus();
                    } else {
                      //                                            Ext.MessageBox.alert("Notification", tmp.msg);
                      Ext.getCmp("wrong_id").show();
                      Ext.getCmp("correct_id").hide();
                      Ext.getCmp("barcode_id").reset();
                      setTimeout(function () {
                        Ext.getCmp("wrong_id").hide();
                      }, 5000);
                      Ext.getCmp("barcode_id").focus();
                    }
                  },
                  failure: function (response, options) {
                    Ext.MessageBox.alert("Notification", ACTION_FAIL);
                    Ext.getCmp("barcode_id").focus();
                  },
                });
              }
            },
            specialkey: function (field, e) {
              if (e.getKey() == e.ENTER) {
                var barcodesearch_field = Ext.getCmp("barcode_id").getValue();
                var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
                  .getSelectionModel()
                  .getSelections()[0].data.fsto_id;
                var fsto_source = Ext.getCmp("gridpanelAddnewTransferOrderdata")
                  .getSelectionModel()
                  .getSelections()[0].data.fsto_source;
                var fsto_ItemId = Ext.getCmp("gridpanelManualPackingGrid")
                  .getSelectionModel()
                  .getSelections()[0].data.fsto_ItemId;
                if (barcodesearch_field) {
                  Ext.Ajax.request({
                    url: modURL + "&op=barcodeCheck",
                    method: "POST",
                    params: {
                      barcodesearch_field: barcodesearch_field,
                      fsto_id: fsto_id,
                      fsto_source: fsto_source,
                    },
                    success: function (response) {
                      var tmp = Ext.decode(response.responseText);
                      if (tmp.success == true) {
                        var tmp = Ext.decode(response.responseText);
                        var item_id = tmp.item_id;
                        var rowIndex = Ext.getCmp(
                          "gridpanelManualPackingGrid"
                        ).store.find("fsto_ItemId", item_id);
                        var grecord = Ext.getCmp(
                          "gridpanelManualPackingGrid"
                        ).store.getAt(rowIndex);
                        var fsto_pkdQty_value = grecord.data.fsto_pkdQty;
                        var fsto_ItemQty = grecord.data.fsto_ItemQty;

                        if (fsto_ItemQty > fsto_pkdQty_value) {
                          var new_fsto_pkdQty = fsto_pkdQty_value + 1;
                          grecord.set("fsto_pkdQty", new_fsto_pkdQty);
                          Ext.getCmp("correct_id").show();
                          Ext.getCmp("wrong_id").hide();
                          Ext.getCmp("barcode_id").reset();
                          setTimeout(function () {
                            Ext.getCmp("correct_id").hide();
                          }, 1000);
                        } else {
                          Ext.MessageBox.alert(
                            "Notification",
                            "Item Quantity limit exceeded"
                          );
                          Ext.getCmp("wrong_id").show();
                          Ext.getCmp("correct_id").hide();
                          Ext.getCmp("barcode_id").reset();
                          setTimeout(function () {
                            Ext.getCmp("wrong_id").hide();
                          }, 5000);
                        }

                        var current_pkdQty_value = grecord.data.fsto_pkdQty;
                        console.log(
                          "current_pkdQty_value",
                          current_pkdQty_value
                        );
                        var itemStore = Ext.getCmp(
                          "gridpanelManualPackingGrid"
                        ).getStore();
                        var itemQty = itemStore.sum("fsto_ItemQty");
                        var pkdQty = itemStore.sum("fsto_pkdQty");
                        if (itemQty == pkdQty) {
                          Ext.getCmp("submit_button").enable();
                        } else {
                          Ext.getCmp("submit_button").disable();
                        }
                        Ext.getCmp("barcode_id").focus();
                      } else {
                        //                                            Ext.MessageBox.alert("Notification", tmp.msg);
                        Ext.getCmp("wrong_id").show();
                        Ext.getCmp("correct_id").hide();
                        Ext.getCmp("barcode_id").reset();
                        setTimeout(function () {
                          Ext.getCmp("wrong_id").hide();
                        }, 5000);
                        Ext.getCmp("barcode_id").focus();
                      }
                    },
                    failure: function (response, options) {
                      Ext.MessageBox.alert("Notification", ACTION_FAIL);
                      //Ext.getCmp('barcode_id').focus();
                    },
                  });
                }
                //                                Ext.getCmp('barcode_id').focus();
              }
            },
          },
        },
        {
          iconCls: "correct",
          id: "correct_id",
          tabIndex: 511,
          hidden: true,
          listeners: {
            change: function () {
              setTimeout(function () {
                onGridResize(true);
              }, 10);
            },
          },
        },
        {
          iconCls: "wrong",
          id: "wrong_id",
          tabIndex: 511,
          hidden: true,
          listeners: {
            change: function () {
              setTimeout(function () {
                onGridResize(true);
              }, 10);
            },
          },
        },
      ],
    });
    return _manualPackingGridPanel;
  };
  var upPackedQty = function (
    fsto_id,
    fsto_ItemId,
    fsto_ItemQty,
    record,
    stit_ParentItemId,
    stit_ConvertCalcMode,
    stit_ConvertCalcRate,
    parentItemName,
    branchType,
    br_parentPacking
  ) {
    var isConversionRequired;
    if (stit_ParentItemId > 0 && br_parentPacking == 0) {
      isConversionRequired = false;
    } else {
      isConversionRequired = true;
    }
    if (Ext.isEmpty(upPackedQty_window)) {
      var upPackedQty_window = new Ext.Window({
        id: "upPackedQty_window",
        layout: "fit",
        width: 350,
        title: "Update Quantity",
        autoHeight: true,
        draggable: false,
        //iconCls: 'my-icon98',
        plain: true,
        constrain: true,
        modal: true,
        resizable: false,
        items: [
          new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: "upPackedQty_form",
            labelAlign: "left",
            labelWidth: 150,
            height: 140,
            items: [
              {
                xtype: "label",
                text: parentItemName,
                style: "padding-top:10px;font-weight:bold;padding-bottom:10px;",
                hidden: isConversionRequired,
              },
              {
                xtype: "spacer",
                height: 10,
              },
              {
                fieldLabel: "Order Quantity",
                xtype: "numberfield",
                minValue: 0,
                id: "packed_qty",
                name: "packed_qty",
                allowNegative: false,
                allowDecimals: false,
                tabIndex: 93,
                value: fsto_ItemQty,
                allowBlank: false,
                width: 80,
              },
              {
                xtype: "spacer",
                height: 10,
              },
              {
                fieldLabel: "Product taken for packing",
                xtype: "numberfield",
                id: "fsto_stockValue",
                name: "fsto_stockValue",
                allowNegative: false,
                allowDecimals: true,
                tabIndex: 93,
                width: 80,
                hidden: isConversionRequired,
                allowBlank: false,
                listeners: {
                  change: function () {
                    var fsto_stockValue =
                      Ext.getCmp("fsto_stockValue").getValue();
                    //                                        if (fsto_stockValue < stit_ConvertCalcRate && stit_ConvertCalcMode == 2) {
                    //                                            Ext.getCmp('fsto_stockValue').reset();
                    //                                            Ext.MessageBox.alert('Notification', "Value should be greater than conversion rate -" + stit_ConvertCalcRate);
                    //                                        }
                  },
                },
              },
            ],
          }),
        ],
        buttons: [
          {
            text: "Cancel",
            id: "btnCancel",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            iconCls: "my-icon1",
            handler: function () {
              upPackedQty_window.close();
            },
          },
          {
            text: "Update",
            id: "btnsave",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            iconCls: "my-icon1",
            handler: function () {
              var ft = Ext.getCmp("packed_qty").getValue();
              var fsto_stockValue = Ext.getCmp("fsto_stockValue").getValue();
              var pkdQuantity = parseInt(ft);
              if (pkdQuantity <= parseInt(fsto_ItemQty)) {
                record.set("fsto_pkdQty", pkdQuantity);
                record.set("fsto_stockValue", fsto_stockValue);
                upPackedQty_window.close();
                var itemStore = Ext.getCmp(
                  "gridpanelManualPackingGrid"
                ).getStore();
                var fstoItemQty = itemStore.sum("fsto_ItemQty");
                var fsto_pkdQty = itemStore.sum("fsto_pkdQty");
                var fsto_stockValue = itemStore.sum("fsto_stockValue");
                if (fstoItemQty == fsto_pkdQty) {
                  Ext.getCmp("submit_button").enable();
                } else {
                  Ext.getCmp("submit_button").disable();
                }
              } else {
                Ext.Msg.alert("Notification", "Quantity Mismatch.");
                record.set("fpod_invoiceqty", "");
              }
            },
          },
        ],
      });
    }
    upPackedQty_window.doLayout();
    upPackedQty_window.show(this);
    upPackedQty_window.center();
  };
  var viewBarcodeGrid = function (fsto_id, fsto_itemId) {
    var _viewBarcodeStore = viewBarcodeStore(fsto_id, fsto_itemId);
    var _viewBarcodeGridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _viewBarcodeStore,
      iconCls: "money",
      autoScroll: true,
      width: 300,
      height: 250,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelviewBarcodeGrid",
      columns: [
        {
          header: "Barcodes",
          dataIndex: "tmp_barcode_code",
          sortable: true,
          tooltip: "Barcodes",
          hideable: true,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
    });
    return _viewBarcodeGridPanel;
  };
  var viewBarcodeWindow = function (fsto_id, fsto_itemId) {
    var fsto_id = fsto_id;
    var fsto_itemId = fsto_itemId;
    var _viewBarcodeWindow = new Ext.Window({
      title: "Barcodes",
      layout: "fit",
      height: 300,
      width: 300,
      resizable: false,
      draggable: true,
      closable: true,
      bodyStyle: { "background-color": "white" },
      items: [viewBarcodeGrid(fsto_id, fsto_itemId)],
      buttons: [
        {
          text: "Cancel",
          icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
          iconCls: "my-icon1",
          tabIndex: 511,
          handler: function () {
            _viewBarcodeWindow.close();
          },
        },
      ],
    });
    _viewBarcodeWindow.doLayout();
    _viewBarcodeWindow.show();
    _viewBarcodeWindow.center();
  };
  var invoiceCollectionWindow = function (
    itemGriddata,
    fsto_id,
    noofbags,
    fsto_ordertype,
    fsto_uid,
    fsto_destination,
    fsto_isalreadypacked
  ) {
    var _invoicePackingWindow = new Ext.Window({
      title: "Invoice & Package Details",
      layout: "fit",
      height: 200,
      width: 600,
      resizable: false,
      draggable: true,
      modal: true,
      closable: false,
      bodyStyle: { "background-color": "white" },
      items: [
        new Ext.FormPanel({
          frame: true,
          monitorValid: true,
          id: "upInvoicePacking_form",
          height: 150,
          items: [
            {
              fieldLabel: "Invoice Date",
              xtype: "datefield",
              id: "packing_invDate",
              name: "packing_invDate",
              tabIndex: 193,
              allowBlank: false,
              format: "d-m-Y",
              maxValue: new Date(),
              anchor: "95%",
            },
            {
              fieldLabel: "Invoice No",
              xtype: "textfield",
              id: "packing_invNo",
              name: "packing_invNo",
              allowBlank: false,
              tabIndex: 194,
              anchor: "95%",
            },
            {
              fieldLabel: "Invoice Amount",
              xtype: "numberfield",
              id: "packing_invAmt",
              name: "packing_invAmt",
              allowBlank: false,
              tabIndex: 195,
              anchor: "95%",
            },
            {
              fieldLabel: "Number of Packets",
              xtype: "numberfield",
              id: "packing_totalBags",
              name: "packing_totalBags",
              allowBlank: false,
              tabIndex: 196,
              value: noofbags,
              anchor: "95%",
            },
          ],
        }),
      ],
      buttons: [
        {
          text: "Cancel",
          icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
          iconCls: "my-icon1",
          tabIndex: 511,
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_source = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_source;
            if (
              Ext.isDefined(
                Ext.getCmp("gridpanelManualPackingGrid")
                  .getSelectionModel()
                  .getSelections()[0]
              )
            ) {
              var fsto_ItemId = Ext.getCmp("gridpanelManualPackingGrid")
                .getSelectionModel()
                .getSelections()[0].data.fsto_ItemId;
            }

            Ext.Ajax.request({
              url: modURL + "&op=barcodedelete",
              method: "POST",
              params: {
                fsto_id: fsto_id,
                fsto_source: fsto_source,
              },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success == true) {
                  _invoicePackingWindow.close();
                  Ext.getCmp("manualPackingWind").close();
                } else {
                  Ext.MessageBox.alert("Notification", tmp.msg);
                }
              },
              failure: function (response, options) {
                Ext.MessageBox.alert("Notification", ACTION_FAIL);
              },
            });
          },
        },
        {
          text: "Submit",
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          cls: "left-right-buttons",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var itemStore = Ext.getCmp("gridpanelManualPackingGrid").getStore();
            var fsto_ItemQty = itemStore.sum("fsto_ItemQty");
            var fsto_pkdQty = itemStore.sum("fsto_pkdQty");
            var noofbags = Ext.getCmp("packing_totalBags").getValue();
            var packing_invDate = Ext.getCmp("packing_invDate").getValue();
            var packing_invNo = Ext.getCmp("packing_invNo").getValue();
            var packing_invAmt = Ext.getCmp("packing_invAmt").getValue();
            if (Ext.getCmp("upInvoicePacking_form").form.isValid()) {
              if (
                ((fsto_ItemQty == fsto_pkdQty && fsto_isalreadypacked == 0) ||
                  fsto_isalreadypacked == 1) &&
                noofbags >= 1
              ) {
                Ext.Ajax.request({
                  url: modURL + "&op=submitManualPacking",
                  method: "POST",
                  params: {
                    itemGriddata: itemGriddata,
                    order_id: fsto_id,
                    noofbags: noofbags,
                    fsto_ordertype: fsto_ordertype,
                    fsto_uid: fsto_uid,
                    packing_invDate: packing_invDate,
                    packing_invNo: packing_invNo,
                    packing_invAmt: packing_invAmt,
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success == true && tmp.valid == true) {
                      //Application.RetalineTransferOrder.viewPackageWindow(tmp.packcount, tmp.data);
                      if (tmp.packcount > 0) {
                        if (_SESSION.COURIER_PACKING != "1") {
                          Application.RetalineTransferOrder.viewPackageWindow(
                            tmp.packcount,
                            tmp.data
                          );
                        } else {
                          Application.RetalineTransferOrder.addPackageWindow(
                            tmp.packcount,
                            tmp.data,
                            fsto_ordertype,
                            fsto_id
                          );
                        }
                      } else {
                        Application.example.msg(
                          "Notification",
                          "Order moved for customer approval"
                        );
                        Ext.getCmp("gridpanelAddnewTransferOrderdata")
                          .getStore()
                          .load();
                      }
                      //Application.example.msg('Success', tmp.msg);
                      //Ext.getCmp("gridpanelAddnewTransferOrderdata").getStore().load();
                      _invoicePackingWindow.close();
                      Ext.getCmp("manualPackingWind").close();
                    } else if (tmp.success == true && tmp.valid == false) {
                      Ext.Msg.alert("Notification", tmp.msg);
                      if (Ext.getCmp("tonoofbags").value <= 0) {
                        Ext.getCmp("tonoofbags").markInvalid();
                      }
                    } else if (tmp.status == "error") {
                      Ext.Msg.alert("Error", tmp.error.msg);
                    } else {
                      Ext.Msg.alert("Error", tmp.data);
                    }
                  },
                  failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert("Error", tmp.msg);
                  },
                });
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Total packed quantity should be equal to total item quantity,Total coversion should be same, No of bags should be atleast 1."
                );
                if (Ext.getCmp("tonoofbags").value <= 0) {
                  Ext.getCmp("tonoofbags").markInvalid();
                }
              }
            } else {
              Ext.MessageBox.alert("Notification", "Error in submitted form");
            }
          },
        },
      ],
      listeners: {
        afterrender: function () {},
      },
    });
    _invoicePackingWindow.doLayout();
    _invoicePackingWindow.show();
    _invoicePackingWindow.center();
  };
  var manualPackingWindow = function (
    fsto_id,
    fsto_destination,
    fsto_ordertype,
    fsto_uid,
    fsto_isalreadypacked,
    ownInvoice,
    branchType
  ) {
    //        var _Orderpickerform = AddNewOrderPickerForm();
    var barcodesearch_field = "";
    var fsto_id = fsto_id;
    var _manualPackingWindow = new Ext.Window({
      title: "Manual Packing",
      layout: "fit",
      width: winsize.width * 0.7,
      height: winsize.height * 0.6,
      resizable: false,
      draggable: true,
      modal: true,
      id: "manualPackingWind",
      closable: false,
      bodyStyle: { "background-color": "white" },
      items: [
        manualPackingGrid(
          fsto_id,
          barcodesearch_field,
          fsto_destination,
          fsto_isalreadypacked
        ),
      ],
      buttons: [
        {
          html: "&nbsp;Invoice Details : &nbsp;",
          id: "invoicelabel",
          hidden: true,
        },
        {
          fieldLabel: "Invoice Date",
          xtype: "datefield",
          id: "packing_invDate",
          name: "packing_invDate",
          tabIndex: 193,
          allowBlank: false,
          format: "d-m-Y",
          maxValue: new Date(),
          anchor: "95%",
          hidden: true,
          width: 100,
          emptyText: "Date",
        },
        {
          fieldLabel: "Invoice No",
          xtype: "textfield",
          id: "packing_invNo",
          name: "packing_invNo",
          allowBlank: false,
          tabIndex: 194,
          anchor: "95%",
          hidden: true,
          width: 100,
          emptyText: "Number",
        },
        {
          fieldLabel: "Invoice Amount",
          xtype: "numberfield",
          id: "packing_invAmt",
          name: "packing_invAmt",
          allowBlank: false,
          tabIndex: 195,
          anchor: "95%",
          hidden: true,
          width: 100,
          emptyText: "Amount",
        },
        "-",
        {
          html: "&nbsp;No of bags : &nbsp;",
        },
        {
          xtype: "numberfield",
          id: "tonoofbags",
          name: "tonoofbags",
          anchor: "98%",
          tabIndex: 109,
          width: 30,
          emptyText: "No of bags",
          value: 0,
        },
        {
          text: "Cancel",
          icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
          iconCls: "my-icon1",
          tabIndex: 511,
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_source = Ext.getCmp("gridpanelAddnewTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_source;
            if (
              Ext.isDefined(
                Ext.getCmp("gridpanelManualPackingGrid")
                  .getSelectionModel()
                  .getSelections()[0]
              )
            ) {
              var fsto_ItemId = Ext.getCmp("gridpanelManualPackingGrid")
                .getSelectionModel()
                .getSelections()[0].data.fsto_ItemId;
            }
            Ext.Ajax.request({
              url: modURL + "&op=barcodedelete",
              method: "POST",
              params: {
                fsto_id: fsto_id,
                fsto_source: fsto_source,
              },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success == true) {
                  _manualPackingWindow.close();
                } else {
                  Ext.MessageBox.alert("Notification", tmp.msg);
                }
              },
              failure: function (response, options) {
                Ext.MessageBox.alert("Notification", ACTION_FAIL);
              },
            });
          },
        },
        {
          text: "Pack All",
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          cls: "left-right-buttons",
          hidden: true,
          handler: function () {
            var store = Ext.getCmp("gridpanelManualPackingGrid").getStore();
            if (store.data.length > 0) {
              store.each(function (record) {
                var pkdQuantity = record.get("fsto_ItemQty");
                record.set("fsto_pkdQty", pkdQuantity);
              });
            }
          },
        },
        {
          text: "Force Submit",
          id: "forcesubmit_button",
          iconCls: "go",
          handler: function () {
            var itemGriddata = getMigratedData("gridpanelManualPackingGrid");
            var fsto_id = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var itemStore = Ext.getCmp("gridpanelManualPackingGrid").getStore();
            var stit_ConvertCalcRate = itemStore.sum("stit_ConvertCalcRate");
            var fsto_stockValue = itemStore.sum("fsto_stockValue");
            var fsto_ItemQty = itemStore.sum("fsto_ItemQty");
            var fsto_pkdQty = itemStore.sum("fsto_pkdQty");
            var noofbags = Ext.getCmp("tonoofbags").getValue();
            Ext.getCmp("forcesubmit_button").disable();
            if (fsto_pkdQty < fsto_ItemQty) {
              Ext.Ajax.request({
                waitMsg: "Processing....................",
                url: modURL + "&op=forcesubmitManualPacking",
                method: "POST",
                params: {
                  itemGriddata: itemGriddata,
                  fsto_id: fsto_id,
                  noofbags: noofbags,
                  fsto_ordertype: fsto_ordertype,
                  fsto_uid: fsto_uid,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true && tmp.valid == true) {
                    //Application.example.msg("Success", tmp.msg);
                    Ext.Msg.alert("Notification", tmp.msg);
                    Ext.getCmp("gridpanelAddnewTransferOrderdata")
                      .getStore()
                      .load();
                    _manualPackingWindow.close();
                  } else if (tmp.success == true && tmp.valid == false) {
                    Ext.Msg.alert("Notification", tmp.msg);
                    if (Ext.getCmp("tonoofbags").value <= 0) {
                      Ext.getCmp("tonoofbags").markInvalid();
                    }
                  } else if (tmp.status == "error") {
                    Ext.Msg.alert("Error", tmp.msg);
                  } else {
                    Ext.Msg.alert("Error", tmp.msg);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", tmp.msg);
                },
              });
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Force Submit is not possible.."
              );
            }
          },
        },
        {
          text: "Submit",
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          cls: "left-right-buttons",
          diasabled: "true",
          id: "submit_button",
          handler: function () {
            Ext.getCmp("submit_button").disable();
            var itemGriddata = getMigratedData("gridpanelManualPackingGrid");
            var fsto_id = Ext.getCmp("gridpanelManualPackingGrid")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var itemStore = Ext.getCmp("gridpanelManualPackingGrid").getStore();
            var fsto_ItemQty = itemStore.sum("fsto_ItemQty");
            var fsto_pkdQty = itemStore.sum("fsto_pkdQty");
            var noofbags = Ext.getCmp("tonoofbags").getValue();
            var packing_invDate = Ext.getCmp("packing_invDate").getValue();
            var packing_invNo = Ext.getCmp("packing_invNo").getValue();
            var packing_invAmt = Ext.getCmp("packing_invAmt").getValue();
            var stit_ConvertCalcRate = itemStore.sum("stit_ConvertCalcRate");
            var fsto_stockValue = itemStore.sum("fsto_stockValue");
            if (
              ((fsto_ItemQty == fsto_pkdQty && fsto_isalreadypacked == 0) ||
                fsto_isalreadypacked == 1) &&
              noofbags >= 1
            ) {
              //_manualPackingWindow.close();
              //fsto_id, fsto_destination, fsto_ordertype, fsto_uid
              //                            if (fsto_ordertype == 'B2C') {
              //                                invoiceCollectionWindow(itemGriddata, fsto_id, noofbags, fsto_ordertype, fsto_uid, fsto_destination, fsto_isalreadypacked);
              //                            } else {
              var loadingMask = loadMask();
              loadingMask.show();
              Ext.Ajax.request({
                waitMsg: "Processing....................",
                url: modURL + "&op=submitManualPacking",
                method: "POST",
                params: {
                  itemGriddata: itemGriddata,
                  order_id: fsto_id,
                  noofbags: noofbags,
                  fsto_ordertype: fsto_ordertype,
                  fsto_uid: fsto_uid,
                  packing_invDate: packing_invDate,
                  packing_invNo: packing_invNo,
                  packing_invAmt: packing_invAmt,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true && tmp.valid == true) {
                    if (tmp.packcount > 0) {
                      if (_SESSION.COURIER_PACKING != "1") {
                        Application.RetalineTransferOrder.viewPackageWindow(
                          tmp.packcount,
                          tmp.data
                        );
                      } else {
                        Application.RetalineTransferOrder.addPackageWindow(
                          tmp.packcount,
                          tmp.data,
                          fsto_ordertype,
                          fsto_id
                        );
                      }
                    } else {
                      Application.example.msg(
                        "Notification",
                        "Order moved for customer approval"
                      );
                      Ext.getCmp("gridpanelAddnewTransferOrderdata")
                        .getStore()
                        .load();
                    }

                    //Application.example.msg('Success', tmp.msg);
                    //Ext.getCmp("gridpanelAddnewTransferOrderdata").getStore().load();
                    _manualPackingWindow.close();
                  } else if (tmp.success == true && tmp.valid == false) {
                    Ext.Msg.alert("Notification", tmp.msg);
                  } else if (tmp.status == "error") {
                    Ext.Msg.alert("Error", tmp.error.msg);
                  } else {
                    Ext.Msg.alert("Error", tmp.data);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", tmp.msg);
                },
              });
              //}
              loadingMask.hide();
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Total packed quantity should be equal to total item quantity,Total coversion should be same, No of bags should be atleast 1."
              );
              if (Ext.getCmp("tonoofbags").value <= 0) {
                Ext.getCmp("tonoofbags").markInvalid();
              }
            }
          },
        },
      ],
      listeners: {
        afterrender: function () {
          if (_SESSION.IS_RETALINE_LITE == "1") {
            Ext.getCmp("barcodeBr_label").hide();
            Ext.getCmp("barcode_id").hide();
          }

          if (fsto_ordertype == "B2C" && ownInvoice == 1) {
            Ext.getCmp("invoicelabel").show();
            Ext.getCmp("packing_invDate").show();
            Ext.getCmp("packing_invNo").show();
            Ext.getCmp("packing_invAmt").show();
          } else {
            Ext.getCmp("invoicelabel").hide();
            Ext.getCmp("packing_invDate").hide();
            Ext.getCmp("packing_invNo").hide();
            Ext.getCmp("packing_invAmt").hide();
          }

          //Ext.getCmp('submit_button').disable();
          var itemStore = Ext.getCmp("gridpanelManualPackingGrid").getStore();
          var fsto_ItemQty = itemStore.sum("fsto_ItemQty");
          var fsto_pkdQty = itemStore.sum("fsto_pkdQty");
          console.log("aitemStore", itemStore);
          console.log("afsto_ItemQty", fsto_ItemQty);
          console.log("afsto_pkdQty", fsto_pkdQty);
          console.log("fsto_isalreadypacked", fsto_isalreadypacked);
          if (fsto_ItemQty == fsto_pkdQty && fsto_isalreadypacked == 0) {
            Ext.getCmp("submit_button").enable();
          }
          if (
            fsto_isalreadypacked == 1 ||
            fsto_ordertype == "Return" ||
            branchType == 1
          ) {
            Ext.getCmp("forcesubmit_button").disable();
            Ext.getCmp("submit_button").enable();
          }
        },
      },
    });
    _manualPackingWindow.doLayout();
    _manualPackingWindow.show();
    _manualPackingWindow.center();
  };
  var loadMask = function (ind) {
    return new Ext.LoadMask(Ext.getCmp("manualPackingWind").getEl(), {
      msg: "Please wait...",
    });
  };
  var getMigratedData = function (gridid) {
    var j = Ext.pluck(Ext.getCmp(gridid).getStore().getRange(), "data");
    return Ext.encode(j);
  };
  var AddNewOrderpickerWindow = function (
    fsto_id,
    fsto_uid,
    fsto_source,
    fsto_sourceName
  ) {
    var _Orderpickerform = AddNewOrderPickerForm(fsto_source);
    var _addNewWindow = new Ext.Window({
      title: "Assign Order Picker",
      layout: "fit",
      width: winsize.width * 0.6,
      height: winsize.height * 0.8,
      resizable: false,
      draggable: true,
      closable: true,
      modal: true,
      bodyStyle: { "background-color": "white" },
      items: [orderPickergrid(fsto_source, fsto_sourceName)],
      buttons: [
        {
          text: "Cancel",
          icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
          iconCls: "my-icon1",
          tabIndex: 511,
          handler: function () {
            _addNewWindow.close();
          },
        },
        {
          text: "Assign",
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          tabIndex: 511,
          id: "assign_pickr",
          //                    hidden:true,
          handler: function () {
            var executive_id = Ext.getCmp("gridpanelAddOrderPicker")
              .getSelectionModel()
              .getSelections()[0].data.id;
            var executiveStatus = Ext.getCmp("gridpanelAddOrderPicker")
              .getSelectionModel()
              .getSelections()[0].data.is_offline;
            var executivename = Ext.getCmp("gridpanelAddOrderPicker")
              .getSelectionModel()
              .getSelections()[0].data.name;
            var order_ID = fsto_id;
            var order_NO = fsto_uid;
            var branch_ID = fsto_source;
            var type = 0;

            if (executive_id > 0 && executiveStatus == 0) {
              Ext.Ajax.request({
                url: modURL + "&op=assignOrderPicker",
                method: "POST",
                params: {
                  id: executive_id,
                  order_ID: order_ID,
                  br_ID: branch_ID,
                  type: type,
                  order_NO: order_NO,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.status == "ok") {
                    Application.example.msg("Success", tmp.msg);
                    _addNewWindow.close();
                    Ext.getCmp("gridpanelAddnewTransferOrderdata")
                      .getStore()
                      .load();
                  } else if (tmp.status == false) {
                    Ext.Msg.alert("Error", tmp.msg);
                  } else {
                    Ext.Msg.alert("Error", tmp.error.msg);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", tmp.message);
                },
              });
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "The Order Picker " + executivename + " is offline"
              );
            }
          },
        },
      ],
    });
    _addNewWindow.doLayout();
    _addNewWindow.show();
    _addNewWindow.center();
  };
  var rtrBranchStore = function (fsto_source) {
    var store = new Ext.data.JsonStore({
      fields: ["br_ID", "br_Name"],
      url: modURL + "&op=getBranches",
      method: "post",
      autoLoad: true,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.fsto_source = fsto_source;
        },
      },
    });
    return store;
  };
  var orderPickerStore = function (fsto_source) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=getOrderPicker",
        method: "post",
      }),
      fields: [
        "id",
        "name",
        "has_open_orders",
        "phone",
        "is_offline",
        "liveStatus",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.fsto_source = fsto_source;
        },
        load: function () {
          Ext.getCmp("gridpanelAddOrderPicker")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _Store;
  };
  var manualPackingStore = function (
    fsto_id,
    barcodesearch_field,
    fsto_destination,
    fsto_isalreadypacked
  ) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=getmanualPacking",
        method: "post",
      }),
      fields: [
        "slNo",
        "item_name",
        { name: "fsto_ItemQty", type: "float" },
        { name: "fsto_pkdQty", type: "float" },
        "mrp",
        "fsto_ItemId",
        "fsto_id",
        "fsto_source",
        "fsto_isConversionRequired",
        "fsto_stockValue",
        "branchType",
        "stit_ParentItemId",
        "br_parentPacking",
        "stit_ConvertCalcMode",
        "stit_ConvertCalcRate",
        "parentItemName",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        load: function (itemStore, itemRecord) {
          Ext.getCmp("gridpanelManualPackingGrid")
            .getSelectionModel()
            .selectRow(0);
          var fsto_ItemQty = itemStore.sum("fsto_ItemQty");
          var fsto_pkdQty = itemStore.sum("fsto_pkdQty");
          if (fsto_ItemQty == fsto_pkdQty && fsto_isalreadypacked == 0) {
            Ext.getCmp("submit_button").enable();
          }
          if (fsto_isalreadypacked == 1) {
            Ext.getCmp("forcesubmit_button").disable();
            Ext.getCmp("submit_button").enable();
          }
          Ext.getCmp("barcode_id").focus();
        },
        beforeload: function (store, e) {
          this.baseParams.fsto_id = fsto_id;
          this.baseParams.fsto_destination = fsto_destination;
          this.baseParams.barcodesearch_field = barcodesearch_field;
        },
      },
    });
    return _Store;
  };
  var viewBarcodeStore = function (fsto_id, fsto_itemId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=getItemBarcodes",
        method: "post",
      }),
      fields: ["tmp_barcode_code", "tmp_barcode_id"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelviewBarcodeGrid")
            .getSelectionModel()
            .selectRow(0);
        },
        beforeload: function (store, e) {
          //var fsto_source = Ext.getCmp('gridpanelAddnewTransferOrderdata').getSelectionModel().getSelections()[0].data.fsto_source;
          this.baseParams.fsto_id = fsto_id;
          this.baseParams.fsto_itemId = fsto_itemId;
          //this.baseParams.fsto_source = fsto_source;
        },
      },
    });
    return _Store;
  };

  var AddNewOrderPickerForm = function (fsto_source) {
    var _OrderPickerFormPanel = new Ext.form.FormPanel({
      frame: false,
      border: false,
      autoHeight: true,
      autoScroll: true,
      labelWidth: 80,
      labelAlign: "top",
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      items: [
        {
          xtype: "spacer",
          height: 10,
        },
        {
          xtype: "combo",
          displayField: "br_Name",
          valueField: "br_ID",
          mode: "local",
          id: "cpdnameid",
          forceSelection: true,
          fieldLabel: "Store",
          emptyText: "CPD",
          anchor: "98%",
          typeAhead: true,
          triggerAction: "all",
          lazyRender: true,
          editable: true,
          minChars: 2,
          tabIndex: 102,
          store: rtrBranchStore(fsto_source),
          listeners: {
            select: function () {
              Ext.getCmp("orderPicker")
                .getStore()
                .load({
                  params: {
                    pickerBranch: Ext.getCmp("cpdnameid").getValue(),
                  },
                });
            },
          },
        },
        {
          xtype: "combo",
          displayField: "Picker",
          valueField: "id",
          mode: "local",
          id: "orderPicker",
          forceSelection: true,
          fieldLabel: "Picker",
          emptyText: "picker",
          anchor: "98%",
          typeAhead: true,
          triggerAction: "all",
          lazyRender: true,
          editable: true,
          minChars: 2,
          tabIndex: 102,
          store: orderPickerStore(),
        },
      ],
    });
    return _OrderPickerFormPanel;
  };
  //    var DetailsViewpanel = function () {
  //        return new Ext.Panel({
  //            layout: 'fit',
  //            border: false,
  //            hideBorders: true,
  //            hidden: true,
  //            id: 'details_view_panel_transferorder',
  //            tpl: new Ext.XTemplate('<div class="details-outer">',
  //                    '<table border="0" width="99%" class="details_view_table">',
  //                    '<tr><th>Name</th><td>  {member_name}</td></tr>',
  //                    '<tr><th>DOB</th><td> {member_dob}</td></tr>',
  //                    '<tr><th>Gender</th><td>  {member_gender}</td></tr>',
  //                    '<tr><th>Address Line 1</th><td>  {member_address1}</td></tr>',
  //                    '<tr><th>Address Line 2</th><td>  {member_address2}</td></tr>',
  //                    '<tr><th>Email</th><td>  {member_email}</td></tr>',
  //                    '<tr><th>Phone</th><td>  {member_phone}</td></tr>',
  //                    '<tr><th>Member Plan </th><td>  {mst_plan_name}</td></tr>',
  //                    '<tr><th>Plan Amount</th><td>  {online_payment_amount}</td></tr>',
  //                    '<tr><th>Aadhar No</th><td>  {member_aadhar}</td></tr>',
  //                    '<tpl if="member_passport != \'\'"><tr><th>Passport No</th><td>  {member_passport}</td></tr></tpl>',
  //                    '<tr><th>Nominee Name</th><td>  {member_nominee}</td></tr>',
  //                    '<tr><th>Nominee Relation</th><td>  {member_nominee_relation}</td></tr>',
  //                    '<tr><th>Payment Status</th><td>  {payment_status}</td></tr>',
  //                    '</table>',
  //                    '</div>'),
  //        });
  //    };
  var retalineTransferOrderPanel = function (id) {
    var src =
      "?module=retaline_transfer_order&op=order_details_view&fsto_id=" +
      Application.RetalineTransferOrder.Cache.fsto_id;
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Packing Order",
      //iconCls: 'finascop_purchase',
      id: id,
      items: [
        retalineTransferOrderGrid(),
        new Ext.Panel({
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          title: "Order Details",
          height: winsize.height * 0.6,
          layout: "border",
          items: [
            {
              region: "center",
              items: [
                {
                  id: "details_view_panel_transferorder",
                  hidden: true,
                  html:
                    '<iframe id="iframe_order_productdtls" name="iframe_order_productdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' +
                    src +
                    '"; ></iframe>',
                },
              ],
            },
          ],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return panel;
  };
  var _bcdItemsStore = function (fsto_id, fsto_source, fsto_ordertype) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listItemsinTransferOrder",
        method: "post",
      }),
      fields: [
        "bcod_id",
        "bcor_id",
        "stit_ID",
        "bcod_Count",
        "stitSKU",
        "bcod_scannedcount",
        "fsto_ItemQty",
        "fsto_pkdQty",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.orderId = fsto_id;
          this.baseParams.orderType = fsto_source;
          this.baseParams.type = fsto_ordertype;
        },
      },
    });
    return _Store;
  };
  var _bcdItemsGrid = function (fsto_id, fsto_source, fsto_ordertype) {
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _bcdItemsStore(fsto_id, fsto_source, fsto_ordertype),
      //iconCls: 'money',
      autoScroll: true,
      width: 300,
      height: 300,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforOuteardItems",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Item Name",
          dataIndex: "stitSKU",
          sortable: true,
          tooltip: "Item Name",
          hideable: false,
          width: 200,
        },
        {
          header: "No of Items",
          dataIndex: "fsto_ItemQty",
          sortable: true,
          align: "right",
          tooltip: "Quantity",
          width: 50,
          hideable: false,
        },
        {
          header: "Items Picked",
          dataIndex: "fsto_pkdQty",
          sortable: true,
          align: "right",
          tooltip: "Items Picked",
          width: 50,
          hideable: false,
        },
        {
          header: "Required Quantity",
          dataIndex: "bcod_Count",
          sortable: true,
          align: "right",
          tooltip: "Required Quantity",
          width: 50,
          hideable: false,
        },
        {
          header: "Picked Qty",
          dataIndex: "bcod_scannedcount",
          sortable: true,
          align: "right",
          width: 50,
          tooltip: "Picked Qty",
          hideable: false,
        },
        { width: 20, dataIndex: " " },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("gridpanelforOuteardItems")
            .getStore()
            .load({
              params: {
                orderId: fsto_id,
                orderType: fsto_source,
                type: fsto_ordertype,
              },
            });
        },
        rowdblclick: function (grid, rowIndex, e) {
          var rec = grid.getStore().getAt(rowIndex);
          var bcod_scannedcount = rec.get("bcod_scannedcount");
          if (bcod_scannedcount > 0) {
            Application.CPD.scannedBarcodeDetails(
              fsto_id,
              fsto_source,
              rec.get("bcod_id")
            );
          }
        },
      },
    });
    return _dispatchgridPanel;
  };
  var _gbHistoryStore = function (orderId, orderType, type) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listTOPolledHistory",
        method: "post",
      }),
      fields: [
        "boy_name",
        "ordersreqtatus",
        "updated_at",
        "accepted_time",
        "scan_start_time",
        "last_scan_time",
        "completed_time",
        {
          name: "created_at",
          type: "date",
          dateFormat: "d-m-Y H:i:s",
        },
      ],
      totalProperty: "totalCount",
      root: "data",
      autoLoad: false,
      sortInfo: {
        field: "created_at",
        direction: "DESC",
      },
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.orderId = orderId;
        },
      },
    });
    return _Store;
  };
  var _gbHistoryGrid = function (orderId, orderType, type) {
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _gbHistoryStore(orderId, orderType, type),
      //iconCls: 'money',
      autoScroll: true,
      width: winsize.width * 0.4,
      height: 300,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforGBHistoryTO",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Boy",
          dataIndex: "boy_name",
          sortable: true,
          tooltip: "Boy",
          hideable: false,
          width: 150,
        },
        {
          header: "Status",
          dataIndex: "ordersreqtatus",
          sortable: true,
          tooltip: "Status",
          hideable: false,
        },
        {
          header: "Created At",
          dataIndex: "created_at",
          sortable: true,
          tooltip: "Created At",
          hideable: false,
          renderer: function (value, metadata, record) {
            dateret = Ext.util.Format.date(value, "d-m-Y H:i:s");
            return dateret;
          },
        },
        {
          header: "Updated At",
          dataIndex: "updated_at",
          sortable: true,
          tooltip: "Updated At",
          hideable: false,
        },
        {
          header: "Accepted On",
          dataIndex: "accepted_time",
          sortable: true,
          tooltip: "Accepted On",
          hidden: true,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("gridpanelforGBHistoryTO")
            .getStore()
            .load({
              params: {
                orderId: orderId,
              },
            });
        },
      },
    });
    return _dispatchgridPanel;
  };
  var createpackagePanel = function (count, packets) {
    var t = new Date();
    var mode = "printOrder";
    var t_stamp = t.format("YmdHis");
    var src =
      "?module=retaline_transfer_order&op=packageView&count=" +
      count +
      "&packets=" +
      packets +
      "&apikey=" +
      _SESSION.apikey +
      "&tstamp=" +
      t_stamp;
    var myPanel = new Ext.Panel({
      layout: "border",
      height: 500,
      id: "pakageOrderPanel",
      items: [
        {
          region: "center",
          border: false,
          html:
            '<iframe src="' +
            src +
            '" id="iframePackage" name="iframePackage" ' +
            'width="100%" height="100%" style="border:none">',
        },
      ],
    });
    return myPanel;
  };

  var createpackageDetailsPanel = function (
    count,
    packets,
    fsto_ordertype,
    fsto_id
  ) {
    var formConfig = {
      //defaults: {
      anchor: "98%",
      labelAlign: "top",
      id: "packageFormDetails",
      frame: true,
      labelWidth: 100,
      autoScroll: true,
      layout: "column",
      items: [setFormFields(count, packets, fsto_ordertype, fsto_id)],
    };

    return new Ext.form.FormPanel(formConfig);
  };
  var retalinePackagingComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getretalinePackaging",
      method: "post",
      fields: ["rpckm_id", "rpckm_name"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var setFormFields = function (count, packets, fsto_ordertype, fsto_id) {
    var myarr = [];
    if (count > 1) {
      //var myarr = packets.split(",");
      var myarr = packets;
    } else {
      myarr.push(packets);
    }

    try {
      var fields = [],
        data = [];
      for (var k = 0; k < myarr.length; k++) {
        var p = k + 1;
        fields.push(
          {
            layout: "form",
            labelAlign: "top",
            style: "margin-bottom:3px;margin-left:3px;",
            columnWidth: 0.3,
            items: [
              {
                fieldLabel: "Package Number",
                xtype: "textfield",
                id: "packets_" + p,
                name: "packets_" + p,
                tabIndex: 193,
                allowBlank: false,
                value: myarr[k],
                anchor: "95%",
                readOnly: true,
              },
            ],
          },
          {
            layout: "form",
            labelAlign: "top",
            style: "margin-bottom:3px;margin-left:3px;",
            columnWidth: 0.3,
            items: [
              {
                xtype: "combo",
                store: retalinePackagingComboStore(),
                mode: "local",
                id: "packaging_" + p,
                name: "packaging_" + p,
                hiddenName: "packaging_" + p,
                allowBlank: false,
                fieldLabel: "Choose Package",
                displayField: "rpckm_name",
                valueField: "rpckm_id",
                typeAhead: true,
                forceSelection: true,
                editable: true,
                minChars: 2,
                anchor: "95%",
                triggerAction: "all",
                lazyRender: true,
                tabIndex: 34,
                listeners: {},
              },
            ],
          },
          {
            layout: "form",
            labelAlign: "top",
            style: "margin-bottom:3px;margin-left:3px;",
            columnWidth: 0.3,
            items: [
              {
                fieldLabel: "Weight(kg)",
                xtype: "textfield",
                id: "packetweigh_" + p,
                name: "packetweigh_" + p,
                tabIndex: 193,
                allowBlank: false,
                anchor: "95%",
              },
            ],
          }
        );
      }

      fields.push({
        xtype: "hidden",
        id: fsto_id,
        name: "packorder",
        value: fsto_id,
      });
      fields.push({
        xtype: "hidden",
        id: count,
        name: "packcount",
        value: count,
      });
      fields.push({
        xtype: "hidden",
        id: fsto_ordertype,
        name: "packordertype",
        value: fsto_ordertype,
      });

      return fields;
    } catch (ex) {
      Ext.Msg.alert("Field Error", "Exceptions : " + ex.message);
    }
  };
  var productActionMenu = function (
    record,
    fsto_id,
    fsto_source,
    fsto_ordertype
  ) {
    var productMenu = new Ext.menu.Menu({
      items: [
        {
          text: "Edit",
          //iconCls: 'finascop_edit',
          handler: function () {
            updateQtyinItem(record, fsto_id, fsto_source, fsto_ordertype);
          },
        },
        {
          text: "Delete",
          handler: function () {
            var ItemId = Ext.getCmp("gridpanelforOuteardItems")
              .getSelectionModel()
              .getSelections()[0].data.ItemId;
            Ext.Msg.confirm(
              "Notification",
              "Do you really want to remove this item?",
              function (btn) {
                if (btn == "yes") {
                  removeFromItemStore(
                    record,
                    fsto_id,
                    fsto_source,
                    fsto_ordertype
                  );
                }
              }
            );
          },
        },
      ],
    });

    return productMenu;
  };

  var _orderItemsGrid = function (fsto_id, fsto_source, fsto_ordertype) {
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _bcdItemsStore(fsto_id, fsto_source, fsto_ordertype),
      iconCls: "money",
      autoScroll: true,
      width: 300,
      height: 300,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforOuteardItems",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Item Name",
          dataIndex: "stitSKU",
          sortable: true,
          tooltip: "Item Name",
          hideable: false,
          width: 200,
        },
        {
          header: "Quantity",
          dataIndex: "bcod_Count",
          sortable: true,
          align: "right",
          tooltip: "Quantity",
          width: 50,
          hideable: false,
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
              productActionMenu(
                record,
                fsto_id,
                fsto_source,
                fsto_ordertype
              ).showAt(e.getXY());
              //action
            },
          },
        },

        //                {
        //                    xtype: 'actioncolumn',
        //                    hideable: false,
        //                    width: 25,
        //                    items: [{
        //                            tooltip: 'Update Quantity',
        //                            iconCls: 'finascop_edit',
        //                            handler: function (grid, rowIndex, colIndex) {
        //                                var record = grid.store.getAt(rowIndex);
        //                                updateQtyinItem(record, fsto_id, fsto_source, fsto_ordertype);
        //                            }
        //                        }, {
        //                            tooltip: 'Remove Item',
        //                            iconCls: 'finascop_delete',
        //                            handler: function (grid, rowIndex, colIndex) {
        //                                var record = grid.store.getAt(rowIndex);
        //                                Ext.Msg.confirm('Notification', 'Do you really want to remove this item?', function (btn) {
        //                                    if (btn == 'yes') {
        //                                        removeFromItemStore(record, fsto_id, fsto_source, fsto_ordertype);
        //                                    }
        //                                });
        //                            }
        //                        }]
        //                }
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        close: function (panel) {
          var branchName = Ext.getCmp("comboxbranchnames").getValue();
          Ext.getCmp("gridpanelAddnewTransferOrderdata")
            .getStore()
            .load({
              params: {
                branchName: branchName,
              },
            });
        },
        afterrender: function () {
          Ext.getCmp("gridpanelforOuteardItems")
            .getStore()
            .load({
              params: {
                orderId: fsto_id,
                orderType: fsto_source,
                type: fsto_ordertype,
              },
            });
        },
        rowdblclick: function (grid, rowIndex, e) {
          var rec = grid.getStore().getAt(rowIndex);
          var bcod_scannedcount = rec.get("bcod_scannedcount");
          if (bcod_scannedcount > 0) {
            Application.CPD.scannedBarcodeDetails(
              fsto_id,
              fsto_source,
              rec.get("bcod_id")
            );
          }
        },
      },
    });
    return _dispatchgridPanel;
  };
  var removeFromItemStore = function (
    record,
    fsto_id,
    fsto_source,
    fsto_ordertype
  ) {
    Ext.Ajax.request({
      url: modURL + "&op=deleteFromPackingOrder",
      method: "POST",
      params: {
        fstod_id: record.get("bcod_id"),
        fsto_id: record.get("bcor_id"),
      },
      success: function (response) {
        var res = Ext.decode(response.responseText);
        if (res.success == true) {
          Application.example.msg("Success", "Removed item");
          Ext.getCmp("gridpanelforOuteardItems")
            .getStore()
            .load({
              params: {
                fsto_id: fsto_id,
                fsto_source: fsto_source,
                fsto_ordertype: fsto_ordertype,
              },
            });
          //                    function (btn) {
          //                        Ext.getCmp('gridpanelforOuteardItems').getStore().load({
          //                            params: {
          //                                fsto_id: fsto_id,
          //                                fsto_source: fsto_source,
          //                                fsto_ordertype: fsto_ordertype
          //                            }
          //                        });
          //                    });
        } else {
          Ext.MessageBox.alert("Failed");
        }
      },
    });
  };
  var updateQtyinItem = function (
    record,
    fsto_id,
    fsto_source,
    fsto_ordertype
  ) {
    if (Ext.isEmpty(upInvQty_window)) {
      var upInvQty_window = new Ext.Window({
        id: "upInvQty_window",
        layout: "fit",
        width: 430,
        title: "Update Quantity",
        autoHeight: true,
        draggable: false,
        plain: true,
        constrain: true,
        modal: true,
        resizable: false,
        items: [
          new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: "upInvQty_form",
            height: 70,
            items: [
              {
                fieldLabel: "Item",
                xtype: "displayfield",
                id: "itemName",
                hideLabel: true,
                name: "itemName",
                value: record.data.stitSKU,
              },
              {
                fieldLabel: "Quantity Receivable",
                xtype: "numberfield",
                hideLabel: true,
                id: "new_totalqty",
                width: 100,
                name: "new_totalqty",
                tabIndex: 93,
                allowBlank: false,
              },
            ],
          }),
        ],
        buttons: [
          {
            text: "Cancel",
            id: "btnCancel",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            iconCls: "my-icon1",
            handler: function () {
              upInvQty_window.close();
            },
          },
          {
            text: "Update",
            id: "btnsave",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            iconCls: "my-icon1",
            handler: function () {
              var currentQty = record.data.bcod_Count;
              var new_totalqty = Ext.getCmp("new_totalqty").getValue();
              if (new_totalqty > currentQty) {
                Ext.MessageBox.alert(
                  "Notification",
                  " Total quantity should be less than " + currentQty
                );
                Ext.getCmp("new_totalqty").reset();
              } else {
                Ext.Ajax.request({
                  waitMsg: "Processing",
                  method: "POST",
                  url: modURL + "&op=updateTotalQtyinPackingOrder",
                  params: {
                    fstod_id: record.data.bcod_id,
                    fsto_id: fsto_id,
                    currentQty: currentQty,
                    new_totalqty: new_totalqty,
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success == true) {
                      Application.example.msg("Success", tmp.msg);
                      Ext.getCmp("upInvQty_window").close();

                      Ext.getCmp("gridpanelforOuteardItems")
                        .getStore()
                        .load({
                          params: {
                            fsto_id: fsto_id,
                            fsto_source: fsto_source,
                            fsto_ordertype: fsto_ordertype,
                          },
                        });
                    } else {
                      Ext.MessageBox.alert("Failure", "Error moving Data");
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
        ],
      });
    }
    upInvQty_window.doLayout();
    upInvQty_window.show(this);
    upInvQty_window.center();
  };
  var retalineScheduledOrderPanel = function (id) {
    var src =
      "?module=retaline_transfer_order&op=order_details_view&fsto_id=" +
      Application.RetalineTransferOrder.Cache.fsto_id;
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Scheduled Jobs",
      id: id,
      items: [
        retalineScheduledOrderGrid(),
        new Ext.Panel({
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          title: "Order Details",
          height: winsize.height * 0.6,
          layout: "border",
          items: [
            {
              region: "center",
              items: [
                {
                  id: "details_view_panel_scheduledorder",
                  hidden: true,
                  html:
                    '<iframe id="iframe_schorder_productdtls" name="iframe_schorder_productdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' +
                    src +
                    '"; ></iframe>',
                },
              ],
            },
          ],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return panel;
  };
  var retalineScheduledOrderGrid = function () {
    var _Store = scheduledOrderStore();
    var fstoGridActionColumn = schedGridActionMenu("sch");
    var BranchStore = new Ext.data.JsonStore({
      fields: ["br_ID", "br_Name"],
      url: modURL + "&op=getBranchName",
      autoLoad: true,
      method: "post",
    });

    var transferOrder_filter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "fsto_uid",
        },
        {
          type: "string",
          dataIndex: "fsto_sourceName",
        },
        {
          type: "string",
          dataIndex: "fsto_destinationName",
        },
        {
          type: "string",
          dataIndex: "fsto_type",
        },
        {
          type: "string",
          dataIndex: "fsto_ordertype",
        },
        {
          type: "date",
          dataIndex: "fstoCreatedOn",
        },
        {
          type: "date",
          dataIndex: "slotDate",
        },
        {
          type: "list",
          options: PCORDER_STATUS_OPTIONS,
          phpMode: true,
          dataIndex: "fsto_statusName",
        },
      ],
    });
    transferOrder_filter.remote = true;
    transferOrder_filter.autoReload = true;
    var _TransOrdergridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _Store,
      title: "",
      iconCls: "finascop_purchase",
      autoScroll: true,
      width: winsize.width * 0.4,
      height: 250,
      bodyStyle: { "background-color": "white" },
      //selModel: ck_selection,
      id: "gridpanelScheduledOrderdata",
      plugins: [transferOrder_filter],
      columns: [
        {
          header: "TO No.",
          dataIndex: "fsto_uid",
          sortable: true,
          tooltip: "TO No.",
          hideable: true,
        },
        {
          header: "Consigner",
          dataIndex: "fsto_sourceName",
          sortable: true,
          tooltip: "Consigner",
        },
        {
          header: "Consignee",
          dataIndex: "fsto_destinationName",
          sortable: true,
          tooltip: "To",
          hideable: true,
        },
        {
          header: "Order Date",
          dataIndex: "fstoCreatedOn",
          sortable: true,
          tooltip: "Order Date",
          hideable: true,
          renderer: function (value, metadata, record) {
            dateret = Ext.util.Format.date(value, "d-m-Y");
            return dateret;
          },
        },
        {
          header: "Delivery Date",
          dataIndex: "slotDate",
          sortable: true,
          tooltip: "Delivery Date",
          hideable: true,
        },
        {
          header: "Slot Time",
          dataIndex: "slotTime",
          sortable: true,
          tooltip: "Slot Time",
          hideable: true,
        },
        {
          header: "Weight",
          dataIndex: "fsto_ItemWeight",
          sortable: true,
          tooltip: "Weight",
          hideable: true,
          hidden: true,
          width: 80,
        },
        {
          header: "Volume",
          dataIndex: "fsto_ItemVolume",
          sortable: true,
          tooltip: "Volume",
          hidden: true,
          hideable: true,
          width: 80,
        },
        {
          header: "Type",
          dataIndex: "fsto_ordertype",
          sortable: true,
          tooltip: "Order Type",
          hideable: true,
        },
        {
          header: "Status",
          dataIndex: "fsto_statusName",
          sortable: true,
          tooltip: "Status",
          hideable: true,
          width: 110,
          renderer: qtipRenderer,
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          // icon: './resources/images/submenuicons/action.png',
          items: [
            {
              getClass: function (v, meta, rec) {
                if (rec.get("fsto_status") > 1) {
                  this.items[0].tooltip = "Choose Actions";
                  return "actioncol";
                } else {
                  return "hideicon";
                }
              },
            },
            {
              getClass: function (v, meta, rec) {
                if (rec.get("fsto_status") == 1) {
                  return "color-trigger";
                } else {
                  return "hideicon";
                }
              },
            },
            {
              getClass: function (v, meta, rec) {
                if (rec.get("fsto_status") == 1) {
                  return "revert";
                } else {
                  return "hideicon";
                }
              },
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.getStore().getAt(rowIndex);
                grid.getSelectionModel().selectRow(rowIndex);
                var fsto_id = record.data.fsto_id;
                Ext.Ajax.request({
                  url: modURL + "&op=stopAutoSchedule",
                  method: "POST",
                  params: {
                    fsto_id: fsto_id,
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                      Application.example.msg("Success", tmp.msg);
                      Ext.getCmp("gridpanelScheduledOrderdata")
                        .getStore()
                        .load();
                    } else {
                      Ext.MessageBox.alert("Notification", tmp.msg);
                    }
                  },
                  failure: function (response, options) {
                    Ext.MessageBox.alert("Notification", ACTION_FAIL);
                  },
                });
              },
            },
          ],
          listeners: {
            click: function (a, grid, rowindex, e) {
              var record = grid.store.getAt(rowindex);
              grid.getSelectionModel().selectRow(rowindex);
              if (record.data.fsto_status > 1) {
                fstoGridActionColumn.showAt(e.getXY());
              }
            },
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedSch,
        },
      }),
      listeners: {
        viewready: updatePagination,
        afterrender: function () {
          if (_SESSION.current_branch_iscpd == 1) {
            Ext.getCmp("comboxbranchnamesInSch").hide();
            Ext.getCmp("textboxSchDataeditCSS").hide();
          } else {
            Ext.getCmp("textboxSchDataeditCSS").hide();
            Ext.getCmp("textboxSchDataeditCSS").setValue(
              _SESSION.current_branch
            );
            Ext.getCmp("comboxbranchnamesInSch").hide();
            Ext.getCmp("showBtntoSch").hide();
          }
        },
      },
      tbar: [
        {
          xtype: "textfield",
          id: "textboxSchDataeditCSS",
          name: "textboxSchDataeditCSS",
          anchor: "98%",
          tabIndex: 1,
          maxLength: 20,
          readOnly: true,
          hidden: true,
        },
        {
          xtype: "combo",
          id: "comboxbranchnamesInSch",
          name: "comboxbranchnamesInSch",
          mode: "local",
          typeAhead: true,
          forceSelection: true,
          emptyText: "Select Branch",
          fieldLabel: "Branch Name",
          editable: true,
          anchor: "97%",
          store: BranchStore,
          triggerAction: "all",
          minChars: 2,
          displayField: "br_Name",
          valueField: "br_ID",
          hiddenName: "comboxbranchnamesInSch",
          hidden: true,
          listeners: {
            select: function () {
              Ext.getCmp("gridpanelScheduledOrderdata").getStore().removeAll();
            },
          },
        },
        {
          xtype: "button",
          text: "Show",
          iconCls: "show",
          id: "showBtntoSch",
          hidden: true,
          style: "padding-left: 10px;",
          handler: function () {
            var branchName = Ext.getCmp("comboxbranchnamesInSch").getValue();
            Ext.getCmp("gridpanelScheduledOrderdata")
              .getStore()
              .load({
                params: {
                  branchName: branchName,
                },
              });
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _Store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
        plugins: [transferOrder_filter],
      }),
    });
    return _TransOrdergridPanel;
  };
  var scheduledOrderStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=listScheduledOrderData",
      fields: [
        "fsto_id",
        "fsto_uid",
        "fstr_id",
        "fsto_status",
        "fsto_source",
        "fsto_sourcetype",
        "fsto_destination",
        "fsto_destinationtype",
        "fsto_source",
        "fsto_destination",
        "fsto_type",
        "fsto_ItemWeight",
        "fsto_ItemVolume",
        "fsto_destinationName",
        "fsto_sourceName",
        "fsto_statusName",
        "fsto_ordertype",
        "fsto_isPurchaseReturn",
        "fsto_isalreadypacked",
        "fsto_parentOrder",
        {
          name: "fstoCreatedOn",
          type: "date",
          dateFormat: "d-m-Y",
        },
        "slotDate",
        "slotTime",
      ],
      totalProperty: "totalCount",
      root: "data",
      idProperty: "fsto_id",
      remoteSort: true,
      autoLoad: false,
      sortInfo: {
        field: "fstoCreatedOn",
        direction: "DESC",
      },
      listeners: {
        beforeload: function () {
          this.baseParams.branchName = Ext.getCmp(
            "comboxbranchnamesInSch"
          ).getValue();
        },
        load: function (store, e) {
          Ext.getCmp("gridpanelScheduledOrderdata").getView().refresh();
          Ext.getCmp("gridpanelScheduledOrderdata")
            .getSelectionModel()
            .selectRow(0);
          if (
            !Ext.isEmpty(
              Ext.getCmp("gridpanelScheduledOrderdata")
                .getSelectionModel()
                .getSelections()
            )
          ) {
            var ID = Ext.getCmp("gridpanelScheduledOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            Application.RetalineTransferOrder.Cache.fsto_id = ID;
            Application.RetalineTransferOrder.ViewModeScheduled(
              Application.RetalineTransferOrder.Cache.fsto_id
            );
          }
        },
      },
    });
    return store;
  };
  var retalineComplleTransferOrderPanel = function (id) {
    var src =
      "?module=retaline_transfer_order&op=order_details_view&fsto_id=" +
      Application.RetalineTransferOrder.Cache.fsto_id;
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Completed Order",
      id: id,
      items: [
        retalineComplTransferOrderGrid(),
        new Ext.Panel({
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          title: "Order Details",
          height: winsize.height * 0.6,
          layout: "border",
          items: [
            {
              region: "center",
              items: [
                {
                  id: "details_view_panel_transferorderComp",
                  hidden: true,
                  html:
                    '<iframe id="iframe_order_productdtlscomp" name="iframe_order_productdtlscomp"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' +
                    src +
                    '"; ></iframe>',
                },
              ],
            },
          ],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return panel;
  };
  var fstoCompGridActionMenu = function () {
    return new Ext.menu.Menu({
      items: [
        {
          text: "View Polled History",
          handler: function () {
            var record = Ext.getCmp("gridpanelComplTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0];
            var fsto_id = record.data.fsto_id;
            var fsto_source = record.data.fsto_source;
            var fsto_ordertype = record.data.fsto_ordertype;
            Application.RetalineTransferOrder.viewPolledHistory(
              fsto_id,
              fsto_source,
              fsto_ordertype
            );
          },
        },
        {
          text: "View Order Details",
          handler: function () {
            var record = Ext.getCmp("gridpanelComplTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0];
            var fsto_id = record.data.fsto_id;
            var fsto_source = record.data.fsto_source;
            var fsto_ordertype = record.data.fsto_ordertype;
            Application.RetalineTransferOrder.viewItems(
              fsto_id,
              fsto_source,
              fsto_ordertype
            );
          },
        },
        {
          text: "Print Order List",
          tooltip: "Print Order List",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelComplTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            printOrder(fsto_id);
          },
        },
        {
          text: "Generate Invoice",
          tooltip: "Generate Invoice",
          handler: function () {
            var fsto_id = Ext.getCmp("gridpanelComplTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            var fsto_ordertype = Ext.getCmp("gridpanelComplTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_ordertype;
            var fsto_isInvoiced = Ext.getCmp("gridpanelComplTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_isInvoiced;
            var isReturn = Ext.getCmp("gridpanelComplTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.isReturn;
            if (fsto_ordertype == 0 || fsto_ordertype == 4) {
              if (fsto_isInvoiced == 0 && isReturn == 0) {
                Ext.Ajax.request({
                  url: modURL + "&op=generateInvoice",
                  method: "POST",
                  params: {
                    fsto_id: fsto_id,
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success == true) {
                      Ext.getCmp("gridpanelComplTransferOrderdata")
                        .getStore()
                        .load();
                    } else {
                      Ext.MessageBox.alert("Notification", tmp.msg);
                    }
                  },
                  failure: function (response, options) {
                    Ext.MessageBox.alert("Notification", ACTION_FAIL);
                  },
                });
              } else {
                if (isReturn == 1) {
                  Ext.MessageBox.alert("Notification", "Invoice not required.");
                } else {
                  Ext.MessageBox.alert(
                    "Notification",
                    "Invoice already generated."
                  );
                }
              }
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Invoice already generated."
              );
            }
          },
        },{
          text: "Package Slip",
          tooltip: "Package Slip",
           handler: function () {
            var fsto_id = Ext.getCmp("gridpanelComplTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_id;
            var fsto_ordertype = Ext.getCmp("gridpanelComplTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_ordertype;
            printPackingSlip(fsto_id,fsto_ordertype);
            
          }

        },{
          text: "View Shipment Logs",
          tooltip: "View Shipment Logs",
           handler: function () {
            var fsto_id = Ext.getCmp("gridpanelComplTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_id;
            var fsto_ordertype = Ext.getCmp("gridpanelComplTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_ordertype;
            var fsto_hasShipmentCreated = Ext.getCmp("gridpanelComplTransferOrderdata").getSelectionModel().getSelections()[0].data.fsto_hasShipmentCreated;
            var fstr_id = Ext.getCmp("gridpanelComplTransferOrderdata").getSelectionModel().getSelections()[0].data.fstr_id;
            if (fsto_hasShipmentCreated >0) {
              showShipmentLogs(fsto_id,fsto_ordertype,fstr_id);
            } else {
                Ext.MessageBox.alert(
                "Notification",
                "Shipment logs not avaialble."
              );
            }           
            
          }

        }
      ],
    });
  };
  var gridSelectionChangedComp = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelComplTransferOrderdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelComplTransferOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_id;
      var fsto_status = Ext.getCmp("gridpanelComplTransferOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_status;
      var fsto_isPurchaseReturn = Ext.getCmp("gridpanelComplTransferOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_isPurchaseReturn;
      var fsto_ordertype = Ext.getCmp("gridpanelComplTransferOrderdata")
        .getSelectionModel()
        .getSelections()[0].data.fsto_ordertype;
      console.log("fsto_status", fsto_status);
      console.log("fsto_isPurchaseReturn", fsto_isPurchaseReturn);
      Application.RetalineTransferOrder.CompViewMode(ID);
    }
  };
  var transferOrderComplStore = function () {
    var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listTransferOrderCompleted',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fsto_id',
                root: 'data'
            }, [
        "fsto_id",
        "fsto_uid",
        "fstr_id",
        "fsto_status",
        "fsto_source",
        "fsto_sourcetype",
        "fsto_destination",
        "fsto_destinationtype",
        "fsto_source",
        "fsto_destination",
        "fsto_type",
        "br_parentPacking",
        "fsto_ordertypeName",
        "isReturn",
        "fsto_ItemWeight",
        "fsto_ItemVolume",
        "fsto_destinationName",
        "fsto_sourceName",
        "fsto_statusName",
        "fsto_ordertype",
        "fsto_isPurchaseReturn",
        "fsto_isalreadypacked",
        "ownInvoice",
        "branchType",
        "fsto_isInvoiced","fsto_shipmentStatus","fsto_hasShipmentCreated",
          {
              name: "fstoCreatedOn",
              type: "date",
              dateFormat: "d-m-Y",
            },]),
            sortInfo: {
                field: "fstoCreatedOn",
                direction: "DESC",
            },
            groupField: '',
            groupDir: 'DESC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                
        beforeload: function () {
          this.baseParams.branchName = Ext.getCmp(
            "comboxbranchnamesComp"
          ).getValue();
        },
        load: function (store, e) {
          Ext.getCmp("gridpanelComplTransferOrderdata").getView().refresh();
          Ext.getCmp("gridpanelComplTransferOrderdata")
            .getSelectionModel()
            .selectRow(0);
          if (
            !Ext.isEmpty(
              Ext.getCmp("gridpanelComplTransferOrderdata")
                .getSelectionModel()
                .getSelections()
            )
          ) {
            var ID = Ext.getCmp("gridpanelComplTransferOrderdata")
              .getSelectionModel()
              .getSelections()[0].data.fsto_id;
            Application.RetalineTransferOrder.Cache.fsto_id = ID;
            Application.RetalineTransferOrder.CompViewMode(
              Application.RetalineTransferOrder.Cache.fsto_id
            );
          }
        },
            }
        })  
    return store;
  };
  var retalineComplTransferOrderGrid = function () {
    var _Store = transferOrderComplStore();
    var fstoGridActionColumn = fstoCompGridActionMenu("po");
    var BranchStore = new Ext.data.JsonStore({
      fields: ["br_ID", "br_Name"],
      url: modURL + "&op=getBranchName",
      autoLoad: true,
      method: "post",
    });

    var transferOrder_filter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "fsto_uid",
        },
        {
          type: "string",
          dataIndex: "fsto_sourceName",
        },
        {
          type: "string",
          dataIndex: "fsto_destinationName",
        },
        {
          type: "string",
          dataIndex: "fsto_type",
        },
        {
          type: "string",
          dataIndex: "fsto_ordertypeName",
        },{
          type: "string",
          dataIndex: "fsto_shipmentStatus",
        },
        {
          type: "date",
          dataIndex: "fstoCreatedOn",
        },
        {
          type: "list",
          options: PCORDER_STATUS_OPTIONS,
          phpMode: true,
          dataIndex: "fsto_statusName",
        },
      ],
    });
    transferOrder_filter.remote = true;
    transferOrder_filter.autoReload = true;
    var _TransOrdergridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _Store,
      title: "",
      autoScroll: true,
      width: winsize.width * 0.4,
      height: 250,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelComplTransferOrderdata",
      plugins: [transferOrder_filter],
      view: new Ext.grid.GroupingView({
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fsto_shipmentStatus') == 'Shipment Created')
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else if (record.get('fsto_shipmentStatus') == 'Shipment Initiated')
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else if (record.get('fsto_shipmentStatus') == 'Shipment Failed')
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else {
                        return '';
                    }
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
      columns: [
        {
          header: "TO No.",
          dataIndex: "fsto_uid",
          sortable: true,
          tooltip: "TO No.",
          hideable: true,
        },
        {
          header: "Consigner",
          dataIndex: "fsto_sourceName",
          sortable: true,
          tooltip: "Consigner",
        },
        {
          header: "Consignee",
          dataIndex: "fsto_destinationName",
          sortable: true,
          tooltip: "To",
          hideable: true,
        },
        {
          header: "Date",
          dataIndex: "fstoCreatedOn",
          sortable: true,
          tooltip: "Date",
          hideable: true,
          renderer: function (value, metadata, record) {
            dateret = Ext.util.Format.date(value, "d-m-Y");
            return dateret;
          },
        },
        {
          header: "Weight",
          dataIndex: "fsto_ItemWeight",
          sortable: true,
          tooltip: "Weight",
          hideable: true,
          hidden: true,
          width: 80,
        },
        {
          header: "Volume",
          dataIndex: "fsto_ItemVolume",
          sortable: true,
          tooltip: "Volume",
          hideable: true,
          hidden: true,
          width: 80,
        },
        {
          header: "Type",
          dataIndex: "fsto_ordertypeName",
          sortable: true,
          tooltip: "Order Type",
          hideable: true,
        },
        {
          header: "Status",
          dataIndex: "fsto_statusName",
          sortable: true,
          tooltip: "Status",
          hideable: true,
          width: 110,
          renderer: qtipRenderer,
        },{
          header: "Shipment Status",
          dataIndex: "fsto_shipmentStatus",
          sortable: true,
          tooltip: "Status",
          hideable: true,
          width: 110,
          renderer: qtipRenderer,
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          items: [
            {
              getClass: function (v, meta, rec) {
                if (rec.get("fsto_status") > 1) {
                  this.items[0].tooltip = "Choose Actions";
                  return "actioncol";
                } else {
                  return "hideicon";
                }
              },
            },
            {
              getClass: function (v, meta, rec) {
                if (rec.get("fsto_status") == 1) {
                  return "color-trigger";
                } else {
                  return "hideicon";
                }
              },
            },
          ],
          listeners: {
            click: function (a, grid, rowindex, e) {
              var record = grid.store.getAt(rowindex);
              grid.getSelectionModel().selectRow(rowindex);
              if (record.data.fsto_status > 1) {
                fstoGridActionColumn.showAt(e.getXY());
              }
            },
          },
          //                    ,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedComp,
        },
      }),
      listeners: {
        viewready: updatePagination,
        afterrender: function () {
          if (_SESSION.br_PyramidLevel == 1) {
            Ext.getCmp("comboxbranchnamesComp").show();
            Ext.getCmp("textboxBrmCpdDataeditCSSComp").hide();
          } else {
            Ext.getCmp("textboxBrmCpdDataeditCSSComp").show();
            Ext.getCmp("textboxBrmCpdDataeditCSSComp").setValue(
              _SESSION.current_branch
            );
            Ext.getCmp("comboxbranchnamesComp").hide();
            Ext.getCmp("showBtntoComp").hide();
          }
        },
      },
      tbar: [
        {
          html: "&nbsp;BRANCH : &nbsp;",
        },
        {
          xtype: "textfield",
          id: "textboxBrmCpdDataeditCSSComp",
          name: "textboxBrmCpdDataeditCSSComp",
          anchor: "98%",
          tabIndex: 1,
          maxLength: 20,
          readOnly: true,
          hidden: true,
        },
        {
          xtype: "combo",
          id: "comboxbranchnamesComp",
          name: "comboxbranchnamesComp",
          mode: "local",
          typeAhead: true,
          forceSelection: true,
          emptyText: "Select Branch",
          fieldLabel: "Branch Name",
          editable: true,
          anchor: "97%",
          store: BranchStore,
          triggerAction: "all",
          minChars: 2,
          displayField: "br_Name",
          valueField: "br_ID",
          hiddenName: "comboxbranchnamesComp",
          listeners: {
            select: function () {
              Ext.getCmp("gridSelectionChangedComp").getStore().removeAll();
            },
          },
        },
        {
          xtype: "button",
          text: "Show",
          iconCls: "show",
          id: "showBtntoComp",
          style: "padding-left: 10px;",
          handler: function () {
            var branchName = Ext.getCmp("comboxbranchnamesComp").getValue();
            Ext.getCmp("gridSelectionChangedComp")
              .getStore()
              .load({
                params: {
                  branchName: branchName,
                },
              });
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _Store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
        plugins: [transferOrder_filter],
      }),
    });
    return _TransOrdergridPanel;
  };
  var printPackingSlip = function (fsto_id,fsto_ordertype) {
    var printPackingSlipPanel = createprintPackingSlipPanel(fsto_id,fsto_ordertype);
    var printPackingSlipWindow = Ext.getCmp("printPackingSlipWindow");
    if (Ext.isEmpty(printPackingSlipWindow)) {
      printPackingSlipWindow = new Ext.Window({
        id: "printPackingSlipWindow",
        plain: true,
        modal: true,
        constrain: true,
        resizable: false,
        title: "Retaline",
        width: 950,
        autoHeight: true,
        items: [printPackingSlipPanel],
        buttons: [
          {
            text: "Cancel",
            iconCls: "my-icon61",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            handler: function () {
              printPackingSlipWindow.close();
            },
          },
          {
            text: "Print",
            icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
            iconCls: "my-icon141",
            handler: function () {
              var params = {
                fsto_id: fsto_id,
                action: 1,
              };
              iframeRequest.focus();
              iframeRequest.print();
              printPackingSlipWindow.close();
            },
          },
        ],
        listeners: {
          close: function () {
            Ext.getCmp("gridpanelAddnewTransferOrderdata").getStore().load();
          },
        },
      });
    }
    printPackingSlipWindow.doLayout();
    printPackingSlipWindow.show();
    printPackingSlipWindow.center();
  };
  var createprintPackingSlipPanel = function (fsto_id,fsto_ordertype) {
    var t = new Date();
    var mode = "printOrder";
    var t_stamp = t.format("YmdHis");
    var src =
      "?module=retaline_transfer_order&op=getPackageSlip&fsto_id=" +
      fsto_id +
      "&fsto_ordertype=" +
      fsto_ordertype +
      "&apikey=" +
      _SESSION.apikey +
      "&tstamp=" +
      t_stamp;
    var myPanel = new Ext.Panel({
      layout: "border",
      height: 500,
      id: "printOrderPanel",
      items: [
        {
          region: "center",
          border: false,
          html:
            '<iframe src="' +
            src +
            '" id="iframeRequest" name="iframeRequest" ' +
            'width="100%" height="100%" style="border:none">',
        },
      ],
    });
    return myPanel;
  };
  var showShipmentLogs = function (fsto_id,fsto_ordertype,fstr_id) {
    var showShipmentLogPanel = createShowShipmentLogPanel(fsto_id,fsto_ordertype,fstr_id);
    var showShipmentLogWindow = Ext.getCmp("showShipmentLogWindow");
    if (Ext.isEmpty(showShipmentLogWindow)) {
      showShipmentLogWindow = new Ext.Window({
        id: "showShipmentLogWindow",
        plain: true,
        modal: true,
        constrain: true,
        resizable: false,
        title: "Shipment Logs",
        width: 950,
        autoHeight: true,
        items: [showShipmentLogPanel],
        buttons: [
          {
            text: "Cancel",
            iconCls: "my-icon61",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            handler: function () {
              showShipmentLogWindow.close();
            },
          }
        ],
        listeners: {
          close: function () {
          },
        },
      });
    }
    showShipmentLogWindow.doLayout();
    showShipmentLogWindow.show();
    showShipmentLogWindow.center();
  };
  var createShowShipmentLogPanel = function (fsto_id,fsto_ordertype,fstr_id) {
    var t = new Date();
    var mode = "printOrder";
    var t_stamp = t.format("YmdHis");
    var src =
      "?module=retaline_transfer_order&op=showShipmentLog&fsto_id=" +
      fsto_id +"&fsto_ordertype=" +
      fsto_ordertype +
      "&fstr_id=" +
      fstr_id +
      "&apikey=" +
      _SESSION.apikey +
      "&tstamp=" +
      t_stamp;
    var myPanel = new Ext.Panel({
      layout: "border",
      height: 500,
      id: "shipmentLogPanel",
      items: [
        {
          region: "center",
          border: false,
          html:
            '<iframe src="' +
            src +
            '" id="iframeRequest" name="iframeRequest" ' +
            'width="100%" height="100%" style="border:none">',
        },
      ],
    });
    return myPanel;
  };
  return {
    Cache: {},
    initOrder: function () {
      var panelId = "retalineTransferOrder_main_panel";
      var branch_panel = Ext.getCmp(panelId);
      if (Ext.isEmpty(branch_panel)) {
        branch_panel = retalineTransferOrderPanel(panelId);
        Application.UI.addTab(branch_panel);
        branch_panel.doLayout();
      } else {
        Application.UI.addTab(branch_panel);
      }
      return branch_panel;
    },
    ViewMode: function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var fsto_id = arguments[0];
      Ext.getCmp("details_view_panel_transferorder").show();

      Ext.get("iframe_order_productdtls").dom.src =
        modURL +
        "&op=order_details_view&fsto_id=" +
        fsto_id +
        "&apikey=" +
        _SESSION.apikey +
        "&tstamp=" +
        t_stamp;
      
    },
    revokeOrder: function (fsto_id, fsto_source, fsto_ordertype) {
      //  orderId, orderType, type
      Ext.Ajax.request({
        waitMsg: "Processing",
        url: modURL,
        params: {
          op: "revokeTransferOrder",
          orderId: fsto_id,
          fsto_source: fsto_source,
          type: fsto_ordertype,
        },
        failure: function (response, options) {
          Ext.MessageBox.alert("Notification", ACTION_FAIL);
        },
        success: function (response, options) {
          var tmp = Ext.decode(response.responseText);
          console.log("tmp", tmp);
          if (tmp.status == "ok") {
            Application.example.msg("Success", tmp.msg);

            Ext.getCmp("gridpanelAddnewTransferOrderdata").getStore().load();
          } else {
            Ext.Msg.alert("Error", tmp.error.msg);
          }
        },
        failure: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", tmp.message);
        },
      });
    },
    viewItems: function (fsto_id, fsto_source, fsto_ordertype) {
      var _addnewItemsWindow = new Ext.Window({
        title: "Item Details",
        //iconCls: 'dispatch',
        layout: "fit",
        height: 400,
        width: winsize.width * 0.7,
        resizable: true,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [_bcdItemsGrid(fsto_id, fsto_source, fsto_ordertype)],
        fbar: [{
          text: 'Mark Packing Completed',
          iconCls: 'list_users',
          tooltip: 'Mark Packing Completed',
          id: 'packCompleted',
          hidden:true,
          handler: function () {
              Ext.Ajax.request({
                  url: modURL + "&op=setPackCompleted",
                  method: "POST",
                  params: { fsto_id: fsto_id,fsto_ordertype:fsto_ordertype },
                  success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                      Application.example.msg('Success', tmp.msg);
                      _addnewItemsWindow.close();
                    } else {
                      Ext.MessageBox.alert("Error", tmp.msg);
                    }
                  },
                  failure: function () {
                    Ext.MessageBox.alert("Error", "Error occured while sending data");
                  },
                });
          }
  }],
      });
      _addnewItemsWindow.doLayout();
      _addnewItemsWindow.show();
      _addnewItemsWindow.center();
    },
    viewPolledHistory: function (fsto_id, fsto_source, fsto_ordertype) {
      var _polledItemsWindow = new Ext.Window({
        title: "Polled Status",
        //iconCls: 'dispatch',
        layout: "fit",
        height: 400,
        width: 900,
        resizable: false,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [_gbHistoryGrid(fsto_id, fsto_source, fsto_ordertype)],
        fbar: [{
          text: 'Set Manually Assign',
          iconCls: 'list_users',
          id: 'setManuallyAssign',
          tooltip: 'Set Drive',
          handler: function () {
              Ext.Ajax.request({
                  url: modURL + "&op=setManuallyAssign",
                  method: "POST",
                  params: { fsto_id: fsto_id },
                  success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                      Application.example.msg('Success', tmp.message);
                      _polledItemsWindow.close();
                    } else {
                      Ext.MessageBox.alert("Error", tmp.message);
                    }
                  },
                  failure: function () {
                    Ext.MessageBox.alert("Error", "Error occured while sending data");
                  },
                });
          }
  }],
      });
      _polledItemsWindow.doLayout();
      _polledItemsWindow.show();
      _polledItemsWindow.center();
    },
    convertOrder: function (fsto_id, fsto_source, fsto_ordertype) {
      //  orderId, orderType, type
      Ext.Ajax.request({
        waitMsg: "Processing",
        url: modURL,
        params: {
          op: "convertTransferOrder",
          orderId: fsto_id,
          fsto_source: fsto_source,
          type: fsto_ordertype,
        },
        failure: function (response, options) {
          Ext.MessageBox.alert("Notification", ACTION_FAIL);
        },
        success: function (response, options) {
          var tmp = Ext.decode(response.responseText);
          console.log("tmp", tmp);
          if (tmp.status == "ok") {
            Application.example.msg("Success", tmp.msg);

            Ext.getCmp("gridpanelAddnewTransferOrderdata").getStore().load();
          } else {
            Ext.Msg.alert("Error", tmp.error.msg);
          }
        },
        failure: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", tmp.message);
        },
      });
    },
    viewPackageWindow: function (packetCount, packets) {
      var packagePanel = createpackagePanel(packetCount, packets);
      var win_id = "view_documents";
      var view_documents_window = Ext.getCmp(win_id);
      if (Ext.isEmpty(view_documents_window)) {
        view_documents_window = new Ext.Window({
          id: win_id,
          title: "Package Identifier",
          layout: "fit",
          width: winsize.width * 0.4,
          height: winsize.height * 0.4,
          //iconCls: 'icon-add-table',
          plain: false,
          constrain: true,
          modal: true,
          frame: true,
          resizable: true,
          items: [packagePanel],
          buttons: [
            {
              text: "OK",
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_approve.png",
              handler: function () {
                view_documents_window.close();
                Ext.getCmp("gridpanelAddnewTransferOrderdata")
                  .getStore()
                  .load();
              },
            },
          ],
          listeners: {
            close: function () {
              Ext.getCmp("gridpanelAddnewTransferOrderdata").getStore().load();
            },
          },
        });
      }
      view_documents_window.doLayout();
      view_documents_window.show();
      view_documents_window.center();
    },
    viewBarcodeWindow: function (fsto_id, fsto_itemId) {
      var fsto_id = fsto_id;
      var fsto_itemId = fsto_itemId;
      var _viewBarcodeWindow = new Ext.Window({
        title: "Barcodes",
        layout: "fit",
        height: 300,
        width: 300,
        resizable: false,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [viewBarcodeGrid(fsto_id, fsto_itemId)],
        buttons: [
          {
            text: "Cancel",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            iconCls: "my-icon1",
            tabIndex: 511,
            handler: function () {
              _viewBarcodeWindow.close();
            },
          },
        ],
      });
      _viewBarcodeWindow.doLayout();
      _viewBarcodeWindow.show();
      _viewBarcodeWindow.center();
    },
    editOrderItems: function (fsto_id, fsto_source, fsto_ordertype) {
      var _addnewItemsWindow = new Ext.Window({
        title: "Edit Item Details",
        iconCls: "dispatch",
        layout: "fit",
        height: 400,
        width: winsize.width * 0.5,
        resizable: true,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [_orderItemsGrid(fsto_id, fsto_source, fsto_ordertype)],
        fbar: [],
        buttons: [
          {
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            text: "Cancel",
            handler: function () {
              _addnewItemsWindow.close();
              var branchName = Ext.getCmp("comboxbranchnames").getValue();
              Ext.getCmp("gridpanelAddnewTransferOrderdata")
                .getStore()
                .load({
                  params: {
                    branchName: branchName,
                  },
                });
            },
          },
        ],
      });
      _addnewItemsWindow.doLayout();
      _addnewItemsWindow.show();
      _addnewItemsWindow.center();
    },
    initScheduledOrder: function () {
      var panelId = "retalineScheduledOrder_main_panel";
      var branch_panel = Ext.getCmp(panelId);
      if (Ext.isEmpty(branch_panel)) {
        branch_panel = retalineScheduledOrderPanel(panelId);
        Application.UI.addTab(branch_panel);
        branch_panel.doLayout();
      } else {
        Application.UI.addTab(branch_panel);
      }
      return branch_panel;
    },
    ViewModeScheduled: function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var fsto_id = arguments[0];
      Ext.getCmp("details_view_panel_scheduledorder").show();

      Ext.get("iframe_schorder_productdtls").dom.src =
        modURL +
        "&op=order_details_view&fsto_id=" +
        fsto_id +
        "&apikey=" +
        _SESSION.apikey +
        "&tstamp=" +
        t_stamp;
    },
    addPackageWindow: function (packetCount, packets, fsto_ordertype, fsto_id) {
      var packagePanel = createpackageDetailsPanel(
        packetCount,
        packets,
        fsto_ordertype,
        fsto_id
      );
      var win_id = "view_documents";
      var view_documents_window = Ext.getCmp(win_id);
      if (Ext.isEmpty(view_documents_window)) {
        view_documents_window = new Ext.Window({
          id: win_id,
          title: "Package Details",
          layout: "fit",
          width: winsize.width * 0.4,
          height: winsize.height * 0.4,
          //iconCls: 'icon-add-table',
          plain: false,
          constrain: true,
          modal: true,
          frame: true,
          resizable: true,
          items: [packagePanel],
          buttons: [
            {
              text: "Skip",
              iconCls: "my-icon61",
              handler: function () {
                view_documents_window.close();
              },
            },
            {
              text: "Save",
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_approve.png",
              handler: function () {
                var store_form = Ext.getCmp("packageFormDetails").getForm();
                if (store_form.isValid()) {
                  store_form.submit({
                    url: modURL,
                    waitMsg: "Saving Details....",
                    waitTitle: "Please Wait...",
                    params: {
                      op: "savePackageDetails",
                      apikey: _SESSION.apikey,
                    },
                    success: function (response, action) {
                      var tmp = Ext.decode(action.response.responseText);
                      if (tmp.success === true) {
                        Ext.Ajax.request({
                          url: modURL + "&op=generateshipment",
                          method: "POST",
                          params: {
                            fstoId: fsto_id,
                          },
                          success: function (response) {
                            var tmp = Ext.decode(response.responseText);
                            if (tmp.success == true) {
                              Application.example.msg("Success", tmp.msg);
                              view_documents_window.close();
                              Ext.getCmp("gridpanelAddnewTransferOrderdata")
                                .getStore()
                                .load();
                            } else {
                              Ext.Msg.alert("Error", tmp.msg);
                            }
                          },
                          failure: function (response) {
                            var tmp = Ext.util.JSON.decode(
                              response.responseText
                            );
                            Ext.MessageBox.alert("Error", tmp.msg);
                          },
                        });
                      } else if (tmp.success === false) {
                        Ext.Msg.alert("Notification.", tmp.msg);
                      } else {
                        Ext.Msg.alert("Error", tmp.msg);
                      }
                    },
                    failure: function (elm, conf, action) {
                      if (conf.failureType === "server") {
                        var result = Ext.decode(conf.response.responseText);
                        console.log("result", result);
                        Ext.Msg.alert("Error", result.error);
                      } else {
                        Ext.MessageBox.alert(
                          "Error",
                          "Check the required fields"
                        );
                      }
                    },
                  });
                } else {
                  Ext.MessageBox.alert(
                    "Notification",
                    "Check the required fields."
                  );
                }
              },
            },
          ],
          listeners: {
            close: function () {
              Ext.getCmp("gridpanelAddnewTransferOrderdata").getStore().load();
            },
          },
        });
      }
      view_documents_window.doLayout();
      view_documents_window.show();
      view_documents_window.center();
    },
    initCompletedOrder: function () {
      var panelId = "retalineCompletedTransferOrder_main_panel";
      var branch_panel = Ext.getCmp(panelId);
      if (Ext.isEmpty(branch_panel)) {
        branch_panel = retalineComplleTransferOrderPanel(panelId);
        Application.UI.addTab(branch_panel);
        branch_panel.doLayout();
      } else {
        Application.UI.addTab(branch_panel);
      }
      return branch_panel;
    },
    CompViewMode: function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var fsto_id = arguments[0];
      Ext.getCmp("details_view_panel_transferorderComp").show();

      Ext.get("iframe_order_productdtlscomp").dom.src =
        modURL +
        "&op=order_details_view&fsto_id=" +
        fsto_id +
        "&apikey=" +
        _SESSION.apikey +
        "&tstamp=" +
        t_stamp;
    },
  };
})();
