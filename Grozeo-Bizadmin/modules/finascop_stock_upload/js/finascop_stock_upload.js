Application.FstockUpload = (function () {
  var RECS_PER_PAGE = 12;
  var modURL = "?module=finascop_stock_upload";
  var winsize = Ext.getBody().getViewSize();
  var current_type;
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var gridSelectionFsuChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("finstockUploadGridPanel")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("finstockUploadGridPanel")
        .getSelectionModel()
        .getSelections()[0].data.fsu_id;
    }
  };
  var gridSelectionRRPChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("manageRRPGridPanel").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("manageRRPGridPanel")
        .getSelectionModel()
        .getSelections()[0].data.fsu_id;
    }
  };
  var finascopStockUploadGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listfinascopStockUpload",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "fbiu_id",
          root: "data",
        },
        [
          "fbiu_id",
          "branch_name",
          "fbiu_status",
          "fbiu_uploadedbyapi",
          "fbiu_branch",
          {
            name: "fsu_date",
            type: "date",
            dateFormat: "d-m-Y H:i:s",
          },
        ]
      ),
      sortInfo: {
        field: "fbiu_id",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: true,
      root: "data",
      listeners: {
        load: function () {
          // Ext.getCmp('upEventGridPanel').getSelectionModel().selectRow(0);
        },
      },
    });
    return _finascopStockUploadList;
  };
  var finascopStockUploadMainPanel = function (id) {
    var _fsuPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Stock Upload",
      iconCls: "dispatch",
      id: id,
      items: [finascopStockUploadGrid()],
    });
    return _fsuPanel;
  };
  var vBranchStore = function () {
    var BranchStore = new Ext.data.JsonStore({
      fields: ["br_ID", "br_Name"],
      url: modURL + "&op=getBranchName",
      autoLoad: true,
      method: "post",
    });
    return BranchStore;
  };
  var finascopStockUploadGrid = function () {
    var BranchStore = vBranchStore();
    var _fsuGridStore = finascopStockUploadGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "branch_name",
        },
        {
          type: "string",
          dataIndex: "fbiu_id",
        },
        {
          type: "string",
          dataIndex: "fbiu_uploadedbyapi",
        },
        {
          type: "string",
          dataIndex: "fsu_date",
        },
        {
          type: "string",
          dataIndex: "fbiu_status",
        },
      ],
    });
    _fsuFilter.remote = true;
    _fsuFilter.autoReload = true;
    var _fsuGridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _fsuGridStore,
      //iconCls: 'money',
      id: "finstockUploadGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _fsuFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Ref.Id",
          dataIndex: "fbiu_id",
          sortable: true,
          tooltip: "Reference Id",
          hideable: true,
          hidden: true,
        },
        {
          header: "Branch",
          id: "fsubr_name_auto_exp",
          dataIndex: "branch_name",
          sortable: true,
          tooltip: "Branch",
          hideable: true,
          width: 200,
        },
        {
          header: "Date",
          dataIndex: "fsu_date",
          sortable: true,
          tooltip: "Date",
          renderer: function (value, metadata, record) {
            dateret = Ext.util.Format.date(value, "d-m-Y H:i:s");
            return dateret;
          },
        },
        {
          header: "Uploaded Mode",
          dataIndex: "fbiu_uploadedbyapi",
          sortable: true,
          tooltip: "Uploaded Mode",
        },
        {
          header: "Status",
          dataIndex: "fbiu_status",
          sortable: true,
          hidden: true,
          tooltip: "Status",
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          //   iconCls: 'downarrow',
          icon: "./resources/images/submenuicons/action.png",
          tooltip: "Choose Actions",
          items: [
            {
              iconCls: "application_view_detail",
              tooltip: "View Details",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Application.FstockUpload.viewUploadedStock(
                  record.get("fbiu_id"),
                  record.get("fbiu_branch")
                );
              },
            },
          ],
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionFsuChanged,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        //resize: onGridResize,
        afterrender: function () {
          if (_SESSION.br_PyramidLevel == 1) {
            Ext.getCmp("comboxbranchnamefsbiu").show();
            Ext.getCmp("textboxfsbiuDataeditCS").hide();
          } else {
            Ext.getCmp("textboxfsbiuDataeditCS").show();
            Ext.getCmp("textboxfsbiuDataeditCS").setValue(
              _SESSION.current_branch
            );
            Ext.getCmp("comboxbranchnamefsbiu").hide();
          }
          var branchName = Ext.getCmp("comboxbranchnamefsbiu").getValue();
          Ext.getCmp("finstockUploadGridPanel")
            .getStore()
            .load({
              params: {
                branchName: branchName,
              },
            });
        },
      },
      tbar: [
        {
          xtype: "combo",
          id: "comboxbranchnamefsbiu",
          name: "comboxbranchname",
          mode: "local",
          typeAhead: true,
          forceSelection: true,
          fieldLabel: "Branch Name",
          editable: true,
          anchor: "97%",
          store: BranchStore,
          triggerAction: "all",
          minChars: 2,
          displayField: "br_Name",
          valueField: "br_ID",
          hiddenName: "comboxbranchname",
          listeners: {
            select: function () {
              var branchName = Ext.getCmp("comboxbranchnamefsbiu").getValue();
              Ext.getCmp("finstockUploadGridPanel")
                .getStore()
                .load({
                  params: {
                    branchName: branchName,
                  },
                });
            },
          },
        },
        {
          html: "&nbsp;BRANCH : &nbsp;",
        },
        {
          xtype: "textfield",
          id: "textboxfsbiuDataeditCS",
          name: "textboxfsbiuDataeditCS",
          anchor: "98%",
          tabIndex: 1,
          setReadOnly: true,
          hidden: true,
        },
        "|",
        {
          text: "Update Stock",
          tooltip: "Update Stock",
          handler: function () {
            if (_SESSION.current_branch_iscpd == 1) {
              var branchName = Ext.getCmp("comboxbranchnamefsbiu").getValue();
              var branchFullName = Ext.getCmp(
                "comboxbranchnamefsbiu"
              ).getRawValue();
            } else {
              var branchName = _SESSION.finascop_current_branch_id;
              var branchFullName = _SESSION.current_branch;
            }
            if (branchName > 0) {
              if (branchName > 0) {
                Ext.Ajax.request({
                  url: modURL + "&op=checkBranchStoreType",
                  method: "POST",
                  params: {
                    branchName: branchName,
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                      Application.FstockUpload.newUpdateStock(
                        branchName,
                        branchFullName
                      );
                    } else if (tmp.success === true && tmp.valid === false) {
                      Ext.Msg.alert(
                        "Notification.",
                        "Stock update is possible only for Retail Store."
                      );
                    } else if (
                      (tmp.success === true) &
                      (tmp.img_valid === false)
                    ) {
                      Ext.Msg.alert(
                        "Notification.",
                        "Stock update is possible only for Retail Store."
                      );
                    } else {
                      Ext.Msg.alert(
                        "Error",
                        "Stock update is possible only for Retail Store."
                      );
                    }
                  },
                  failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert(
                      "Error",
                      "Stock update is possible only for Retail Store."
                    );
                  },
                });
              }
            }
          },
        },
        "|",
        {
          text: "Upload CSV",
          tooltip: "Upload CSV",
          handler: function () {
            if (_SESSION.current_branch_iscpd == 1) {
              var branchName = Ext.getCmp("comboxbranchnamefsbiu").getValue();
              var branchFullName = Ext.getCmp(
                "comboxbranchnamefsbiu"
              ).getRawValue();
              //                            if (branchName > 0) {
              //                                //importCSV(branchName, branchFullName);
              //                            } else {
              //                                Ext.Msg.alert('Notification', 'Please choose a branch to import csv.');
              //                            }
            } else {
              var branchName = _SESSION.finascop_current_branch_id;
              var branchFullName = _SESSION.current_branch;
              //importCSV(branchName, branchFullName);
            }
            if (branchName > 0) {
              Ext.Ajax.request({
                url: modURL + "&op=checkBranchStoreType",
                method: "POST",
                params: {
                  branchName: branchName,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success === true && tmp.valid === true) {
                    importCSV(branchName, branchFullName);
                  } else if (tmp.success === true && tmp.valid === false) {
                    Ext.Msg.alert(
                      "Notification.",
                      "Stock upload is possible only for Retail Store."
                    );
                  } else if (
                    (tmp.success === true) &
                    (tmp.img_valid === false)
                  ) {
                    Ext.Msg.alert(
                      "Notification.",
                      "Stock upload is possible only for Retail Store."
                    );
                  } else {
                    Ext.Msg.alert(
                      "Error",
                      "Stock upload is possible only for Retail Store."
                    );
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert(
                    "Error",
                    "Stock upload is possible only for Retail Store."
                  );
                },
              });
            }
          },
        },
        "|",
        {
          text: "Download Sample CSV",
          tooltip: "Download demo csv for importing",
          handler: function () {
            var url = "/resources/demo/exportcsvstock.csv";
            if (_SESSION.current_branch_iscpd == 1) {
              var branchName = Ext.getCmp("comboxbranchnamefsbiu").getValue();
              var branchFullName = Ext.getCmp(
                "comboxbranchnamefsbiu"
              ).getRawValue();
            } else {
              var branchName = _SESSION.finascop_current_branch_id;
              var branchFullName = _SESSION.current_branch;
            }

            if (branchName > 0) {
              Ext.Ajax.request({
                url: modURL + "&op=checkBranchStoreType",
                method: "POST",
                params: {
                  branchName: branchName,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success === true && tmp.valid === true) {
                    postToUrl(
                      modURL +
                        "&op=buildStockUploadCsv&branchName=" +
                        branchName
                    );
                  } else if (tmp.success === true && tmp.valid === false) {
                    Ext.Msg.alert(
                      "Notification.",
                      "Stock upload is possible only for Retail Store."
                    );
                  } else if (
                    (tmp.success === true) &
                    (tmp.img_valid === false)
                  ) {
                    Ext.Msg.alert(
                      "Notification.",
                      "Stock upload is possible only for Retail Store."
                    );
                  } else {
                    Ext.Msg.alert(
                      "Error",
                      "Stock upload is possible only for Retail Store."
                    );
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert(
                    "Error",
                    "Stock upload is possible only for Retail Store."
                  );
                },
              });
            }
          },
        },
      ],
    });
    return _fsuGridPanel;
  };
  var importCSV = function (branch, brName) {
    var win_csv = new Ext.Window({
      layout: "fit",
      width: 400,
      autoHeight: true,
      border: false,
      title: "Import Stock",
      icon: "./resources/images/submenuicons/upload_fl.png",
      //iconCls: 'upload',
      shadow: false,
      floating: true,
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      items: [
        new Ext.form.FormPanel({
          labelAlign: "top",
          labelSeparator: "",
          bodyStyle: {
            "background-color": "white",
            padding: "5px 5px 5px 10px",
          },
          autoHeight: true,
          id: "csv_form",
          frame: false,
          border: false,
          fileUpload: true,
          items: [
            {
              layout: "form",
              columnWidth: 0.3,
              items: [
                {
                  fieldLabel: "Upload File (.csv)",
                  labelAlign: "top",
                  xtype: "fileuploadfield",
                  accept: ".csv",
                  id: "excel_file",
                  allowBlank: false,
                  name: "excel_file",
                  tabIndex: 1,
                  msgTarget: "under",
                  anchor: "98%",
                  validator: function (v) {
                    if (v != "") {
                      //var exp = /^.*.(.xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                      var exp = /^.*\.csv$/;
                      if (!exp.test(v)) {
                        return "Upload a valid CSV file.";
                      }
                      return true;
                    }
                    var filen = Ext.getCmp("excel_file").getValue();
                    if (filen == "") {
                      return "Upload a file.";
                    }
                  },
                },
              ],
              buttons: [
                {
                  iconCls: "csv",
                  text: "Upload",
                  handler: function () {
                    var csv_form = Ext.getCmp("csv_form").getForm();
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    if (csv_form.isValid()) {
                      csv_form.submit({
                        url: modURL + "&op=uploadStockcsvFile",
                        waitTitle: "Please Wait..",
                        waitMsg: "Saving data...",
                        params: {
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp,
                          branch: branch,
                        },
                        success: function (csv_form, action) {
                          var result = Ext.decode(action.response.responseText);
                          console.log("result", result);
                          if (
                            result.valid === true &&
                            result.success === true
                          ) {
                            win_csv.close();
                            Application.FstockUpload.dispalyUploadedStock(
                              result.fbiu_id,
                              brName
                            );
                            Ext.Msg.alert(
                              "Notification",
                              result.msg,
                              function (btn) {
                                if (btn === "ok") {
                                  Ext.Msg.hide();
                                }
                              }
                            );
                          } else if (
                            result.valid === false &&
                            result.success === true
                          ) {
                            if (result.error) {
                              Ext.Msg.alert("Notification", result.error);
                            } else {
                              Ext.Msg.alert(
                                "Notification",
                                "Column in CSV doesnot Match"
                              );
                            }
                          } else {
                            if (result.error) {
                              Ext.Msg.alert("Notification", result.error);
                            }
                          }
                        },
                        failure: function () {
                          Ext.Msg.alert(
                            "Error",
                            "Supplied CSV File could not be validated. ",
                            function (btn) {
                              if (btn === "ok") {
                                Ext.Msg.hide();
                                win_csv.close();
                              }
                            }
                          );
                        },
                      });
                    }
                  },
                },
              ],
            },
          ],
        }),
      ],
    });
    win_csv.show();
    win_csv.doLayout();
    win_csv.center();
  };
  var _fsbiudDetailsStore = function (fbiu_id) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listStockUploadedItems",
        method: "post",
      }),
      fields: [
        "fbiu_id",
        "stit_sku",
        "stit_id",
        "branch_id",
        "item_count",
        "mrp",
        "selling_price",
        "stit_itemERPId",
        "discunt_selling_price",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.fbiu_id = fbiu_id;
        },
      },
    });
    return _Store;
  };
  var _dispalyUploadedStockDetailsGrid = function (fbiu_id) {
    var _fsbiudDetailsgridPanel = new Ext.grid.GridPanel({
      frame: true,
      border: false,
      loadMask: true,
      store: _fsbiudDetailsStore(fbiu_id),
      iconCls: "money",
      width: winsize.width * 0.4,
      height: 400,
      bodyStyle: { "background-color": "white" },
      id: "fsbiudDetailsgridPanelDetails",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "ERP Id",
          dataIndex: "stit_itemERPId",
          sortable: true,
          tooltip: "ERP Id",
          hideable: false,
        },
        {
          header: "Item Name",
          dataIndex: "stit_sku",
          sortable: true,
          tooltip: "Barcode",
          hideable: false,
          width: 250,
        },
        {
          header: "Count",
          dataIndex: "item_count",
          sortable: true,
          tooltip: "Details",
          hideable: false,
          align: "right",
        },
        {
          header: "MRP",
          dataIndex: "mrp",
          sortable: true,
          tooltip: "MRP",
          hideable: false,
          width: 100,
          align: "right",
        },
        {
          header: "Selling Price",
          dataIndex: "selling_price",
          sortable: true,
          tooltip: "Selling Price",
          hideable: false,
          align: "right",
        },
        {
          header: "Discouint Selling Price",
          dataIndex: "discount_selling_price",
          sortable: true,
          tooltip: "Discouint Selling Price",
          hideable: false,
          align: "right",
        },
      ],
      fbar: [
        {
          text: "Save",
          cls: "left-right-buttons",
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          handler: function () {
            var branchName;
            if (_SESSION.current_branch_iscpd == 1) {
              branchName = Ext.getCmp("comboxbranchnamefsbiu").getValue();
            } else {
              branchName = _SESSION.finascop_current_branch_id;
            }
            Ext.Ajax.request({
              url: modURL + "&op=confirmStockUpload",
              method: "POST",
              params: {
                fbiu_id: fbiu_id,
                branchName: branchName,
              },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true && tmp.valid === true) {
                  Application.example.msg("Success", tmp.msg);
                  Ext.getCmp("finstockUploadGridPanel").store.load({
                    params: {
                      branchName: branchName,
                    },
                  });
                  Ext.getCmp("fsibiudWindow").close();
                } else if (tmp.success === true && tmp.valid === false) {
                  Ext.Msg.alert("Notification.", tmp.msg);
                } else if ((tmp.success === true) & (tmp.img_valid === false)) {
                  Ext.Msg.alert("Notification.", tmp.msg);
                } else {
                  Ext.Msg.alert("Error", tmp.msg);
                }
              },
              failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert("Error", tmp.message);
              },
            });
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("fsbiudDetailsgridPanelDetails")
            .getStore()
            .load({
              params: {
                fbiu_id: fbiu_id,
              },
            });
        },
      },
    });
    return _fsbiudDetailsgridPanel;
  };
  var _viewUploadedStockDetailsGrid = function (fbiu_id) {
    var _fsbiudDetailsgridPanel = new Ext.grid.GridPanel({
      frame: true,
      border: false,
      loadMask: true,
      store: _fsbiudDetailsStore(fbiu_id),
      iconCls: "money",
      width: winsize.width * 0.4,
      height: 400,
      bodyStyle: { "background-color": "white" },
      id: "fsbiudDetailsgridPanelDetailsView",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "ERP Id",
          dataIndex: "stit_itemERPId",
          sortable: true,
          tooltip: "ERP Id",
          hideable: false,
        },
        {
          header: "Item Name",
          dataIndex: "stit_sku",
          sortable: true,
          tooltip: "Barcode",
          hideable: false,
          width: 250,
        },
        {
          header: "Count",
          dataIndex: "item_count",
          sortable: true,
          tooltip: "Details",
          hideable: false,
          align: "right",
        },
        {
          header: "MRP",
          dataIndex: "mrp",
          sortable: true,
          tooltip: "MRP",
          hideable: false,
          width: 100,
          align: "right",
        },
        {
          header: "Selling Price",
          dataIndex: "selling_price",
          sortable: true,
          tooltip: "Selling Price",
          hideable: false,
          align: "right",
        },
        {
          header: "",
          dataIndex: "",
          hideable: false,
        },
      ],
      fbar: [],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("fsbiudDetailsgridPanelDetailsView")
            .getStore()
            .load({
              params: {
                fbiu_id: fbiu_id,
              },
            });
        },
      },
    });
    return _fsbiudDetailsgridPanel;
  };
  var manageRRPMainPanel = function (id) {
    var _mrrpPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Manage RRP",
      iconCls: "dispatch",
      id: id,
      items: [manageRRPGrid()],
    });
    return _mrrpPanel;
  };
  var manageRRPGrid = function () {
    var rrpStore = new Ext.data.JsonStore({
      fields: ["rrp_detailId", "rrp_detailName"],
      url: modURL + "&op=getRrpDetailStore",
      autoLoad: false,
      method: "post",
    });
    var _rrpGridStore = rrpUploadGridStore();
    var _rrpFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "list",
          options: ["SKU", "Brand", "Itemmaster", "Subcategory"],
          phpMode: true,
          dataIndex: "rrp_typeName",
        },
        {
          type: "string",
          dataIndex: "rrp_detailName",
        },
      ],
    });
    _rrpFilter.remote = true;
    _rrpFilter.autoReload = true;
    var _mrrpGridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _rrpGridStore,
      id: "manageRRPGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _rrpFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Type",
          dataIndex: "rrp_typeName",
          sortable: true,
          tooltip: "Type",
          hideable: true,
          width: 20,
        },
        {
          header: "Detail",
          id: "rrp_detailName_auto_exp",
          dataIndex: "rrp_detailName",
          sortable: true,
          tooltip: "Detail",
          hideable: true,
          width: 200,
        },
        {
          header: "RRP Factor",
          dataIndex: "rrp_factor",
          sortable: true,
          align: "right",
          tooltip: "RRP Factor",
          width: 20,
        },
        {
          xtype: "actioncolumn",
          header: "",
          hideable: false,
          groupable: false,
          width: 80,
          items: [
            {
              iconCls: "remove-enquiry",
              tooltip: "Delete Code",
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                deleteRRPFactor(record.get("rrp_id"));
              },
            },
          ],
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _rrpGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionRRPChanged,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },
      tbar: [
        {
          html: "&nbsp;Select Type : &nbsp;",
        },
        {
          fieldLabel: "Select Type",
          xtype: "combo",
          displayField: "typeName",
          valueField: "typeId",
          mode: "local",
          id: "rrp_type",
          hiddenName: "rrp_type",
          name: "rrp_type",
          typeAhead: true,
          minChars: 1,
          triggerAction: "all",
          selectOnFocus: true,
          lazyRender: true,
          anchor: "97%",
          store: new Ext.data.JsonStore({
            fields: ["typeId", "typeName"],
            data: [
              { typeId: "1", typeName: "SKU" },
              { typeId: "2", typeName: "Brand" },
              { typeId: "3", typeName: "Item Master" },
              { typeId: "4", typeName: "Subcategory" },
            ],
          }),
          editable: true,
          width: 120,
          tabIndex: 100,
          listeners: {
            select: function () {
              var type = Ext.getCmp("rrp_type").getValue();
              Ext.getCmp("rrp_detail").reset();
              Ext.getCmp("rrp_detail")
                .getStore()
                .load({
                  params: {
                    type: type,
                  },
                });
            },
          },
        },
        {
          html: "&nbsp;Select Detail : &nbsp;",
        },
        {
          xtype: "combo",
          id: "rrp_detail",
          name: "rrp_detail",
          mode: "local",
          emptyText: "Choose",
          typeAhead: true,
          forceSelection: true,
          fieldLabel: "Select Detail",
          editable: true,
          anchor: "97%",
          width: 350,
          store: rrpStore,
          triggerAction: "all",
          minChars: 2,
          displayField: "rrp_detailName",
          valueField: "rrp_detailId",
          hiddenName: "rrp_detail",
          tabIndex: 101,
          listeners: {
            select: function () {},
          },
        },
        {
          html: "&nbsp;RRP Factor : &nbsp;",
        },
        {
          fieldLabel: "RRP Factor",
          xtype: "numberfield",
          id: "rrp_factor",
          name: "rrp_factor",
          tabIndex: 102,
          width: 80,
          anchor: "97%",
        },
        {
          html: "&nbsp;&nbsp;",
        },
        {
          text: "Save",
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
          tabIndex: 103,
          handler: function () {
            Application.FstockUpload.checkRRPAvailable();
          },
        },
        "->",
        {
          html: "&nbsp;Minimum RRP Factor : &nbsp;",
        },
        {
          xtype: "textfield",
          id: "textboxMinRrpFactor",
          name: "textboxMinRrpFactor",
          anchor: "98%",
          tabIndex: 1,
          maxLength: 20,
          readOnly: true,
          value: _SESSION.DEFAULT_RRP,
        },
      ],
    });
    return _mrrpGridPanel;
  };
  var rrpUploadGridStore = function () {
    var _manageRRPList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listRRPFactor",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "rrp_id",
          root: "data",
        },
        [
          "rrp_id",
          "rrp_type",
          "rrp_detail",
          "rrp_factor",
          "rrp_typeName",
          "rrp_detailName",
        ]
      ),
      sortInfo: {
        field: "rrp_id",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: true,
      root: "data",
      listeners: {
        load: function () {},
      },
    });
    return _manageRRPList;
  };
  var createRRPWindow = function (medId) {
    var form = rrpForm(medId);
    var retalineRRP_window = Ext.getCmp("retalineRRP_window");
    if (Ext.isEmpty(retalineRRP_window)) {
      retalineRRP_window = new Ext.Window({
        id: "retalineRRP_window",
        title: "Add RRP",
        layout: "fit",
        width: winsize.width * 0.2,
        autoHeight: true,
        plain: true,
        modal: true,
        constrainHeader: true,
        keepTop: true,
        align: "center",
        frame: true,
        resizable: false,
        height: 150,
        items: [form],
        shadow: false,
        buttons: [
          {
            text: "Cancel",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            tabindex: 6,
            handler: function () {
              Ext.getCmp("retalineRRP_window").close();
            },
          },
          {
            text: "Save",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
            tabindex: 5,
            handler: function () {
              Application.FstockUpload.retalineRRPSave(medId);
            },
          },
        ],
      });
    }

    retalineRRP_window.doLayout();
    retalineRRP_window.show(this);
    retalineRRP_window.center();
    //retalineRRP_window.alignTo(Ext.getBody(), "tr-tr", [-480, 10]);
  };
  var rrpDetailStore = function () {
    var rrpStore = new Ext.data.JsonStore({
      fields: ["rrp_detailId", "rrp_detailName"],
      url: modURL + "&op=getRrpDetailStore",
      autoLoad: false,
      method: "post",
    });
    return rrpStore;
  };
  var rrpForm = function () {
    var rrpComboStore = rrpDetailStore();
    var form = new Ext.FormPanel({
      layout: "form",
      id: "rrpFormPanel",
      height: 150,
      columnWidth: 1,
      frame: true,
      border: true,
      items: [
        {
          layout: "form",
          labelAlign: "top",
          items: [
            {
              xtype: "hidden",
              id: "rrp_id",
              name: "rrp_id",
            },
            {
              fieldLabel: "Select Type",
              xtype: "combo",
              displayField: "typeName",
              valueField: "typeId",
              mode: "local",
              id: "rrp_type",
              hiddenName: "rrp_type",
              name: "rrp_type",
              typeAhead: true,
              minChars: 1,
              triggerAction: "all",
              selectOnFocus: true,
              lazyRender: true,
              anchor: "97%",
              allowBlank: false,
              store: new Ext.data.JsonStore({
                fields: ["typeId", "typeName"],
                data: [
                  { typeId: "1", typeName: "SKU" },
                  { typeId: "2", typeName: "Brand" },
                  { typeId: "3", typeName: "Item Master" },
                  { typeId: "4", typeName: "Subcategory" },
                ],
              }),
              editable: true,
              msgTarget: "under",
              tabIndex: 1,
              listeners: {
                select: function () {
                  var type = Ext.getCmp("rrp_type").getValue();
                  Ext.getCmp("rrp_detail")
                    .getStore()
                    .load({
                      params: {
                        type: type,
                      },
                    });
                },
              },
            },
            {
              xtype: "combo",
              id: "rrp_detail",
              name: "rrp_detail",
              mode: "local",
              emptyText: "Choose",
              typeAhead: true,
              forceSelection: true,
              fieldLabel: "Select Detail",
              editable: true,
              anchor: "97%",
              store: rrpComboStore,
              triggerAction: "all",
              minChars: 2,
              displayField: "rrp_detailName",
              valueField: "rrp_detailId",
              hiddenName: "rrp_detail",
              allowBlank: false,
              listeners: {
                select: function () {},
              },
            },
          ],
        },
        {
          layout: "form",
          labelAlign: "left",
          labelWidth: 65,
          items: [
            {
              fieldLabel: "RRP Factor",
              xtype: "numberfield",
              id: "rrp_factor",
              name: "rrp_factor",
              tabIndex: 93,
              anchor: "97%",
              allowBlank: false,
            },
          ],
        },
      ],
    });
    return form;
  };
  var deleteRRPFactor = function (id) {
    Ext.MessageBox.confirm(
      "Confirm",
      "Do you want to remove this item?",
      function (btn, text) {
        if (btn == "yes") {
          Ext.Ajax.request({
            waitMsg: "Processing",
            method: "POST",
            url: modURL + "&op=deleteRRPFactor",
            params: {
              rrp_id: id,
            },
            success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              if (tmp.success === true) {
                Application.example.msg("Success", "Removed item");
                Ext.getCmp("manageRRPGridPanel").getStore().load();
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
  var oldproductsGrid = function (branchId, branchFullName) {
    var vendorcol = venderItemColmodel(branchId);
    var venderstore = prdctSearchStore(branchId, branchFullName);
    var vendorlist = prdctSearchtb();

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
      loadMask: true,
      store: venderstore,
      colModel: vendorcol,
      tbar: vendorlist,
      plugins: [vendor_filter],
      region: "center",
      height: 400,
      frame: false,
      border: false,
      hideBorders: true,
      id: "gridFinascopStockVenderitemGridgeneration",
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      bbar: [],
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
    checkOnly: true,
    listeners: {},
  });
  var venderItemColmodel = function (branchId) {
    var colmodel = new Ext.grid.ColumnModel({
      sortable: true,
      columns: [
        rowno,
        {
          header: "SKU Name",
          width: 200,
          dataIndex: "stit_SKU",
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
              iconCls: "move-to-contacts",
              tooltip: "Move Item",
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                var stit_id = record.get("stit_ID");
                var item_count = 0;
                var mrp = 0;
                var selling_price = 0;
                var uuid = Application.FstockUpload.Cache.updateUUID;
                Application.FstockUpload.updateStockDetails(
                  branchId,
                  stit_id,
                  item_count,
                  mrp,
                  selling_price,
                  uuid
                );
              },
            },
          ],
        },
      ],
    });
    return colmodel;
  };
  var prdctSearchStore = function (branchId, branchFullName) {
    var store = new Ext.data.JsonStore({
      method: "POST",
      url: modURL + "&op=missingProdctListing",
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
        "stit_product_variant",
      ],
      listeners: {
        beforeload: function (thisStore, options) {
          thisStore.baseParams.current_type = current_type;
          thisStore.baseParams.branchId = branchId;
          thisStore.baseParams.branchFullName = branchFullName;
        },
        load: function (store, records, options) {},
      },
    });

    return store;
  };
  var prdctSearchtb = function () {
    var tbar = new Ext.Toolbar({
      style: "margin:5px 1px 5px 1px;",
      labelAlign: "left",
      frame: false,
      border: false,
      hideBorders: true,
      items: [
        {
          xtype: "radiogroup",
          width: 150,
          id: "radiobuttonFinascopStockId",
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
              Ext.getCmp("stockItem").reset();
              Ext.getCmp("stitItemCount").reset();
              Ext.getCmp("stitItemMRP").reset();
              Ext.getCmp("stitItemSP").reset();
              var current_firstid = event.items.items[0].inputValue;
              var current_secondid = event.items.items[1].inputValue;
              var radioid = Ext.getCmp("radiobuttonFinascopStockId").getValue();

              if (radioid == current_secondid) {
                var item_name = "";
              } else if (radioid == current_firstid) {
                var item_name = "";
              }
            },
          },
        },
        {
          xtype: "textfield",
          id: "radiosearch",
          width: 350,
          listeners: {
            specialkey: function (field, e) {
              if (e.getKey() == e.ENTER) {
                var item_search_item = Ext.getCmp(
                  "radiobuttonFinascopStockId"
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
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/search.png",
          xtype: "button",
          text: "Search",
          handler: function () {
            var item_search_item = Ext.getCmp(
              "radiobuttonFinascopStockId"
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
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
          xtype: "button",
          text: "Reset",
          handler: function () {
            Ext.getCmp("radiosearch").reset();
            filterItems("", "");
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
  var addItemStore = function (branchId, branchFullName) {
    var store = new Ext.data.JsonStore({
      method: "post",
      url: modURL + "&op=listSelectedItems",
      fields: [
        "stit_id",
        "branch_id",
        "item_count",
        "mrp",
        "selling_price",
        "stit_SKU",
        "discount_selling_price",
      ],
      remoteSort: true,
      autoLoad: true,
      root: "data",
      totalProperty: "totalCount",
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.branchId = branchId;
          this.baseParams.branchFullName = branchFullName;
        },
      },
    });
    return store;
  };
  var selectedPrductsGrid = function (branchId, branchFullName) {
    var prdctMasterStore = new Ext.data.JsonStore({
      fields: ["stit_ID", "stit_SKU"],
      url: modURL + "&op=missingProdctListing",
      autoLoad: false,
      method: "post",
      totalProperty: "totalCount",
      root: "data",
      listeners: {
        beforeload: function () {
          this.baseParams.current_type = Ext.getCmp(
            "radiobuttonFinascopStockId"
          ).getValue();
          this.baseParams.branchId = branchId;
          this.baseParams.branchFullName = branchFullName;
        },
      },
    });
    var addItemStorenew = addItemStore(branchId, branchFullName);
    var vendoritem_filter = new Ext.ux.grid.GridFilters({
      local: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
      ],
    });
    vendoritem_filter.remote = true;
    vendoritem_filter.autoReload = true;
    var addItem = new Ext.grid.GridPanel({
      store: addItemStorenew,
      height: 400,
      frame: true,
      border: false,
      loadMask: true,
      plugins: [vendoritem_filter],
      id: "gridpanelVendorAdditem",
      columns: [
        {
          header: "SKU",
          width: 400,
          dataIndex: "stit_SKU",
          hideable: true,
          sortable: true,
        },
        {
          header: "Item Count",
          dataIndex: "item_count",
          hideable: true,
          sortable: true,
        },
        {
          header: "MRP",
          dataIndex: "mrp",
          hideable: true,
          sortable: true,
          align: "right",
        },
        {
          header: "Selling Price",
          dataIndex: "selling_price",
          hideable: true,
          sortable: true,
          align: "right",
        },
        {
          header: "Discount Selling Price",
          dataIndex: "discount_selling_price",
          hideable: true,
          sortable: true,
          align: "right",
        },
        {
          xtype: "actioncolumn",
          header: "Actions",
          hideable: false,
          groupable: false,
          width: 80,
          items: [
            {
              iconCls: "stockedit",
              tooltip: "Update Stock",
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                var stit_id = record.get("stit_id");
                var stit_SKU = record.get("stit_SKU");
                var item_count = record.get("item_count");
                var mrp = record.get("mrp");
                var selling_price = record.get("selling_price");
                var discount_selling_price = record.get(
                  "discount_selling_price"
                );
                var uuid = Application.FstockUpload.Cache.updateUUID;
                Ext.getCmp("stockItem").setValue(stit_id);
                Ext.getCmp("stockItem").setRawValue(stit_SKU);
                Ext.getCmp("stitItemCount").setValue(item_count);
                Ext.getCmp("stitItemMRP").setValue(mrp);
                Ext.getCmp("stitItemSP").setValue(selling_price);
                Ext.getCmp("stitItemDiscountSP").setValue(
                  discount_selling_price
                );

                //Application.FstockUpload.updateStockDetails(branchId, stit_id, item_count, mrp, selling_price, uuid);
              },
            },
          ],
        },
      ],
      tbar: [
        {
          xtype: "radiogroup",
          width: 150,
          id: "radiobuttonFinascopStockId",
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
              Ext.getCmp("stockItem").reset();
              Ext.getCmp("stitItemCount").reset();
              Ext.getCmp("stitItemMRP").reset();
              Ext.getCmp("stitItemSP").reset();
              Ext.getCmp("stitItemDiscountSP").reset();
              var current_firstid = event.items.items[0].inputValue;
              var current_secondid = event.items.items[1].inputValue;
              var radioid = Ext.getCmp("radiobuttonFinascopStockId").getValue();

              if (radioid == current_secondid) {
                var item_name = "";
              } else if (radioid == current_firstid) {
                var item_name = "";
              }
            },
            check: function () {
              Ext.getCmp("stockItem").reset();
              Ext.getCmp("stitItemCount").reset();
              Ext.getCmp("stitItemMRP").reset();
              Ext.getCmp("stitItemSP").reset();
              Ext.getCmp("stitItemDiscountSP").reset();
            },
          },
        },
        {
          xtype: "combo",
          fieldLabel: "Product",
          emptyText: "Product",
          id: "stockItem",
          name: "stockItem",
          labelStyle: mandatory_label,
          allowBlank: false,
          labelAlign: "top",
          mode: "remote",
          typeAhead: true,
          lazyRender: true,
          //forceSelection: true,
          editable: true,
          anchor: "99%",
          width: 450,
          store: prdctMasterStore,
          hideTrigger: true,
          triggerAction: "all",
          minChars: 1,
          displayField: "stit_SKU",
          valueField: "stit_ID",
          hiddenName: "stockItem",
          tabIndex: 501,
          listeners: {
            select: function (index, val) {},
          },
        },
        { html: "&nbsp;Item Count:&nbsp;" },
        {
          xtype: "numberfield",
          id: "stitItemCount",
          anchor: "98%",
          width: 50,
          fieldLabel: "Item Count",
        },
        { html: "&nbsp;MRP:&nbsp;" },
        {
          xtype: "numberfield",
          id: "stitItemMRP",
          anchor: "98%",
          width: 50,
          fieldLabel: "MRP",
        },
        { html: "&nbsp;Selling Price:&nbsp;" },
        {
          xtype: "numberfield",
          id: "stitItemSP",
          width: 50,
          anchor: "98%",
          fieldLabel: "Selling Price",
        },
        { html: "&nbsp;Discount Selling Price:&nbsp;" },
        {
          xtype: "numberfield",
          id: "stitItemDiscountSP",
          width: 50,
          anchor: "98%",
          fieldLabel: "Selling Price",
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
          text: "Add",
          handler: function () {
            var itrmMrp = Ext.getCmp("stitItemMRP").getValue();
            var itemSP = Ext.getCmp("stitItemSP").getValue();
            var stitItemDiscountSP =
              Ext.getCmp("stitItemDiscountSP").getValue();
            var itemId = Ext.getCmp("stockItem").getValue();
            if (itrmMrp > 0 && itemSP > 0 && itemId > 0) {
              if (itrmMrp >= itemSP) {
                Ext.Ajax.request({
                  url: modURL,
                  params: {
                    op: "saveStockDetails",
                    branchId: branchId,
                    uuid: Application.FstockUpload.Cache.updateUUID,
                    stit_id: Ext.getCmp("stockItem").getValue(),
                    stitItemCount: Ext.getCmp("stitItemCount").getValue(),
                    stitItemMRP: Ext.getCmp("stitItemMRP").getValue(),
                    stitItemSP: Ext.getCmp("stitItemSP").getValue(),
                    stitItemDiscountSP: stitItemDiscountSP,
                  },
                  failure: function (response, options) {
                    Ext.MessageBox.alert("Notification", ACTION_FAIL);
                  },
                  success: function (response, options) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                      Application.example.msg("Notification", tmp.msg);
                      Ext.getCmp("gridpanelVendorAdditem").getStore().reload();
                      Ext.getCmp("stockItem").reset();
                      Ext.getCmp("stitItemCount").reset();
                      Ext.getCmp("stitItemMRP").reset();
                      Ext.getCmp("stitItemSP").reset();
                      Ext.getCmp("stitItemDiscountSP").reset();
                    }
                  },
                });
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Invalid Price values. MRP should be greater than Selling Price."
                );
              }
            } else {
              Ext.MessageBox.alert("Notification", "Invalid data.");
            }
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return addItem;
  };
  var mainsubProductStore = function () {
    var store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSubProducts",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "subItemId",
          root: "data",
        },
        [
          "parentItemName",
          "subItemName",
          "stit_ConvertCalcMode",
          "stit_ConvertCalcRate",
          "stit_ID",
          "stit_ParentItemId",
        ]
      ),
      sortInfo: {
        field: "stit_ID",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      //remoteSort: true,
      autoLoad: true,
      root: "data",
      listeners: {
        load: function () {
          // Ext.getCmp('upEventGridPanel').getSelectionModel().selectRow(0);
        },
      },
    });
    return store;
  };
  var mainsubProductGrid = function () {
    var subPdt_store = mainsubProductStore();
    var subpdt_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "subItemName",
        },
        {
          type: "string",
          dataIndex: "subItemId",
        },
        {
          type: "string",
          dataIndex: "parentItemId",
        },
      ],
    });
    subpdt_filter.remote = true;
    subpdt_filter.autoReload = true;
    var itemmaster_grid_panel = new Ext.grid.GridPanel({
      ds: subPdt_store,
      frame: false,
      border: false,
      height: 360,
      id: "subproduct_grid",
      title: "Sub Products",
      loadMask: true,
      plugins: [subpdt_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Sub Product Name",
          sortable: true,
          hideable: true,
          dataIndex: "subItemName",
        },
        {
          header: "Parent Product Name",
          sortable: true,
          hideable: true,
          dataIndex: "parentItemName",
        },
        {
          header: "Calculation Mode",
          sortable: true,
          hideable: true,
          dataIndex: "stit_ConvertCalcMode",
        },
        {
          header: "Calculate By",
          id: "hsn_code",
          sortable: true,
          hideable: true,
          align: "right",
          dataIndex: "stit_ConvertCalcRate",
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          icon: "./resources/images/submenuicons/action.png",
          tooltip: "Choose Actions",
          items: [
            {
              iconCls: "finascop_delete",
              tooltip: "Remove",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Ext.Msg.confirm(
                  "Notification",
                  "Do you really want to remove this item?",
                  function (btn) {
                    if (btn == "yes") {
                      Application.FstockUpload.deleteSubProduct(
                        record.get("stit_ID"),
                        record.get("stit_ParentItemId")
                      );
                    }
                  }
                );
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
        resize: onGridResize,
        //viewready: onGridResize
      },
      tbar: [
        {
          xtype: "button",
          text: "Create New Product",
          iconCls: "finascop_add",
          handler: function () {
            addSubProducts(0);
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: 18,
        store: subPdt_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
      }),
    });

    return itemmaster_grid_panel;
  };
  var addSubProducts = function (itemid) {
    var title = itemid == 0 ? "Add Item" : "Edit Item";
    var addSubPrdt_window = Ext.getCmp("addSubPrdt_window");
    if (Ext.isEmpty(addSubPrdt_window)) {
      var addSubPrdt_window = new Ext.Window({
        id: "subPrdt_window",
        title: title,
        iconCls: "finascop_additem",
        modal: true,
        layout: "border",
        width: winsize.width * 0.85,
        height: 600,
        shadow: false,
        resizable: false,
        items: [
          productsGrid(),
          new Ext.Panel({
            region: "east",
            frame: false,
            border: true,
            layout: "fit",
            autoScroll: false,
            title: "Sub Products",
            bodyStyle: { "background-color": "white" },
            id: "order_parent_panel",
            width: winsize.width * 0.5,
            items: [subPrductsGrid()],
          }),
        ],
        buttons: [
          {
            text: "Cancel",
            tabIndex: 626,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            handler: function () {
              addSubPrdt_window.close();
            },
          },
          {
            text: "Save",
            tabIndex: 625,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
            handler: function () {
              saveSubProduct(itemid);
            },
          },
        ],
      });
    }

    addSubPrdt_window.show();
    addSubPrdt_window.doLayout();
    addSubPrdt_window.center();
  };
  var subProductData = function () {
    Ext.getCmp("subproduct_grid").getStore().setDefaultSort("ItemName", "asc");
    Ext.getCmp("subproduct_grid").getStore().removeAll();
    Ext.getCmp("subproduct_grid")
      .getStore()
      .load({
        params: {
          start: 0,
          limit: RECS_PER_PAGE,
        },
      });
  };
  var parentProductStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      url: modURL + "&op=listParentProducts",
      fields: ["stit_ID", "stit_SKU"],
      remoteSort: true,
      autoLoad: true,
      root: "data",
      totalProperty: "totalCount",
      listeners: {
        load: function (store, e) {
          Ext.getCmp("gridpanelParentProduct").getSelectionModel().selectRow(0);
        },
      },
    });
    return store;
  };
  var subProductStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      url: modURL + "&op=listParentSubProducts",
      fields: [
        "stit_ID",
        "stit_SKU",
        "stit_ConvertCalcMode",
        "stit_ConvertCalcRate",
        "stit_ParentItemId",
      ],
      remoteSort: true,
      autoLoad: true,
      root: "data",
      totalProperty: "totalCount",
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.parentItemId =
            Application.FstockUpload.Cache.parentItemId;
        },
      },
    });
    return store;
  };
  var gridSelectionPrdctChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelParentProduct").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelParentProduct")
        .getSelectionModel()
        .getSelections()[0].data.stit_ID;
      Application.FstockUpload.Cache.parentItemId = ID;
      Ext.getCmp("subProduct").getStore().baseParams.parentProduct =
        Application.FstockUpload.Cache.parentItemId;
      Ext.getCmp("subProduct").getStore().load();
      Ext.getCmp("gridpanelSubProduct").getStore().baseParams.parentItemId =
        Application.FstockUpload.Cache.parentItemId;
      Ext.getCmp("gridpanelSubProduct").getStore().load();
    }
  };
  var productsGrid = function () {
    var parentPrdctMasterStore = new Ext.data.JsonStore({
      fields: ["stit_ID", "stit_SKU"],
      url: modURL + "&op=parentProdcts",
      autoLoad: false,
      method: "post",
      totalProperty: "totalCount",
      root: "data",
      listeners: {
        load: function () {},
      },
    });
    var addItemStorenew = parentProductStore();
    var vendoritem_filter = new Ext.ux.grid.GridFilters({
      local: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
      ],
    });
    vendoritem_filter.remote = true;
    vendoritem_filter.autoReload = true;
    var addItem = new Ext.grid.GridPanel({
      store: addItemStorenew,
      height: 400,
      frame: true,
      title: "Primary Product",
      region: "center",
      border: false,
      loadMask: true,
      plugins: [vendoritem_filter],
      id: "gridpanelParentProduct",
      columns: [
        {
          header: "SKU",
          width: 250,
          dataIndex: "stit_SKU",
          hideable: true,
          sortable: true,
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          icon: "./resources/images/submenuicons/action.png",
          tooltip: "Choose Actions",
          items: [
            {
              iconCls: "finascop_delete",
              tooltip: "Remove",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Ext.Msg.confirm(
                  "Notification",
                  "Do you really want to remove this item?",
                  function (btn) {
                    if (btn == "yes") {
                      Ext.Ajax.request({
                        url: modURL + "&op=deleteParentPdt",
                        method: "POST",
                        params: { parentPdtId: record.get("stit_ID") },
                        success: function (response) {
                          var tmp = Ext.decode(response.responseText);
                          if (tmp.success === true) {
                            Application.example.msg("Success", tmp.message);
                            Ext.getCmp("gridpanelParentProduct")
                              .getStore()
                              .reload();
                          } else {
                            Ext.Msg.alert("Error", tmp.message);
                          }
                        },
                        failure: function () {
                          Ext.MessageBox.alert(
                            "Error",
                            "Error occured while sending data"
                          );
                        },
                      });
                    }
                  }
                );
              },
            },
          ],
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionPrdctChanged,
        },
      }),
      tbar: [
        {
          xtype: "combo",
          fieldLabel: "Product",
          emptyText: "Product",
          id: "parentProduct",
          name: "parentProduct",
          labelStyle: mandatory_label,
          allowBlank: false,
          labelAlign: "top",
          mode: "remote",
          typeAhead: true,
          lazyRender: true,
          editable: true,
          anchor: "99%",
          width: 450,
          store: parentPrdctMasterStore,
          hideTrigger: true,
          triggerAction: "all",
          minChars: 1,
          displayField: "stit_SKU",
          valueField: "stit_ID",
          hiddenName: "parentProduct",
          tabIndex: 501,
          listeners: {
            select: function (index, val) {},
          },
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
          text: "Add",
          handler: function () {
            Ext.Ajax.request({
              url: modURL + "&op=addParentProduct",
              method: "POST",
              params: { parentProduct: Ext.getCmp("parentProduct").getValue() },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true) {
                  Application.example.msg("Success", tmp.message);
                  Ext.getCmp("gridpanelParentProduct").getStore().reload();
                  Ext.getCmp("parentProduct").reset();
                } else {
                  Ext.Msg.alert("Error", tmp.message);
                }
              },
              failure: function () {
                Ext.MessageBox.alert(
                  "Error",
                  "Error occured while sending data"
                );
              },
            });
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return addItem;
  };
  var subPrductsGrid = function () {
    var subprdctMasterStore = new Ext.data.JsonStore({
      fields: ["stit_ID", "stit_SKU"],
      url: modURL + "&op=getProducts",
      autoLoad: false,
      method: "post",
      totalProperty: "totalCount",
      root: "data",
      listeners: {
        load: function () {
          this.baseParams.parentProduct =
            Application.FstockUpload.Cache.parentItemId;
        },
      },
    });
    var addItemStorenew = subProductStore();
    var vendoritem_filter = new Ext.ux.grid.GridFilters({
      local: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
      ],
    });
    vendoritem_filter.remote = true;
    vendoritem_filter.autoReload = true;
    var addItem = new Ext.grid.GridPanel({
      store: addItemStorenew,
      height: 400,
      frame: true,
      border: false,
      loadMask: true,
      plugins: [vendoritem_filter],
      id: "gridpanelSubProduct",
      columns: [
        {
          header: "SKU",
          width: 400,
          dataIndex: "stit_SKU",
          hideable: true,
          sortable: true,
        },
        {
          header: "Mode",
          width: 400,
          dataIndex: "stit_ConvertCalcMode",
          hideable: true,
          sortable: true,
        },
        {
          header: "Value",
          width: 400,
          dataIndex: "stit_ConvertCalcRate",
          hideable: true,
          sortable: true,
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          icon: "./resources/images/submenuicons/action.png",
          tooltip: "Choose Actions",
          items: [
            {
              iconCls: "finascop_delete",
              tooltip: "Remove",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Ext.Msg.confirm(
                  "Notification",
                  "Do you really want to remove this item?",
                  function (btn) {
                    if (btn == "yes") {
                      Application.FstockUpload.deleteSubProduct(
                        record.get("stit_ID"),
                        record.get("stit_ParentItemId")
                      );
                    }
                  }
                );
              },
            },
          ],
        },
      ],
      tbar: [
        { html: "&nbsp;Product:&nbsp;" },
        {
          xtype: "combo",
          fieldLabel: "Product",
          emptyText: "Product",
          id: "subProduct",
          name: "subProduct",
          labelStyle: mandatory_label,
          allowBlank: false,
          labelAlign: "top",
          mode: "remote",
          typeAhead: true,
          lazyRender: true,
          //forceSelection: true,
          editable: true,
          anchor: "99%",
          width: 350,
          store: subprdctMasterStore,
          hideTrigger: true,
          triggerAction: "all",
          minChars: 1,
          displayField: "stit_SKU",
          valueField: "stit_ID",
          hiddenName: "subProduct",
          tabIndex: 501,
          listeners: {
            select: function (index, val) {},
          },
        },
        { html: "&nbsp;Mode:&nbsp;" },
        {
          xtype: "combo",
          displayField: "name",
          valueField: "id",
          mode: "local",
          id: "rsp_calcMode",
          name: "rsp_calcMode",
          hiddenName: "rsp_calcMode",
          forceSelection: true,
          fieldLabel: "Calc. Mode",
          emptyText: "Calc. Mode",
          anchor: "97%",
          typeAhead: true,
          triggerAction: "all",
          width: 80,
          lazyRender: true,
          editable: true,
          allowBlank: false,
          minChars: 2,
          tabIndex: 910,
          store: new Ext.data.JsonStore({
            fields: ["id", "name"],
            data: [
              { id: "Price", name: "Price" },
              { id: "Stock", name: "Stock" },
            ],
          }),
          listeners: {
            select: function () {},
          },
        },
        { html: "&nbsp;Value:&nbsp;" },
        {
          xtype: "numberfield",
          fieldLabel: "Calc. By",
          align: "right",
          id: "rsp_calcBy",
          name: "rsp_calcBy",
          anchor: "97%",
          allowBlank: false,
          allowNegative: false,
          decimalPrecision: 3,
          tabIndex: 907,
          width: 40,
          listeners: {
            change: function () {},
          },
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
          text: "Add",
          handler: function () {
            var parentItemId = Application.FstockUpload.Cache.parentItemId;
            var subProduct = Ext.getCmp("subProduct").getValue();
            var rsp_calcMode = Ext.getCmp("rsp_calcMode").getValue();
            var rsp_calcBy = Ext.getCmp("rsp_calcBy").getValue();
            Ext.Ajax.request({
              url: modURL + "&op=saveSubProduct",
              method: "POST",
              params: {
                parentItemId: parentItemId,
                subProduct: subProduct,
                rsp_calcMode: rsp_calcMode,
                rsp_calcBy: rsp_calcBy,
              },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true) {
                  Application.example.msg("Success", tmp.message);
                  Ext.getCmp("gridpanelSubProduct").getStore().reload();
                  Ext.getCmp("subProduct").reset();
                  Ext.getCmp("rsp_calcMode").reset();
                  Ext.getCmp("rsp_calcBy").reset();
                } else {
                  Ext.Msg.alert("Error", tmp.message);
                }
              },
              failure: function () {
                Ext.MessageBox.alert(
                  "Error",
                  "Error occured while sending data"
                );
              },
            });
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return addItem;
  };
  var mainSupProductPanel = function (id) {
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Sub Products",
      id: id,
      iconCls: "scheduled",
      buttonAlign: "right",
      items: [
        productsGrid(),
        new Ext.Panel({
          region: "east",
          frame: false,
          border: true,
          layout: "fit",
          autoScroll: false,
          title: "Sub Products",
          bodyStyle: { "background-color": "white" },
          id: "order_parent_panel",
          width: winsize.width * 0.5,
          items: [subPrductsGrid()],
        }),
      ],
      listeners: {
        afterrender: function () {},
      },
    });
    return panel;
  };
  return {
    Cache: {},
    initFinStockUploadList: function () {
      var _finascopStockUploadPanelId = "finascopStockUpload";
      var __finascopStockUploadPanelIdPanel = Ext.getCmp(
        _finascopStockUploadPanelId
      );
      if (Ext.isEmpty(__finascopStockUploadPanelIdPanel)) {
        __finascopStockUploadPanelIdPanel = finascopStockUploadMainPanel(
          _finascopStockUploadPanelId
        );
        Application.UI.addTab(__finascopStockUploadPanelIdPanel);
        __finascopStockUploadPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__finascopStockUploadPanelIdPanel);
      }
    },
    dispalyUploadedStock: function (fbiu_id, brName) {
      var _addnewItemsWindow = new Ext.Window({
        title: "Current Stock Upload ",
        iconCls: "dispatch",
        layout: "fit",
        height: 400,
        width: winsize.width * 0.45,
        id: "fsibiudWindow",
        resizable: false,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [_dispalyUploadedStockDetailsGrid(fbiu_id)],
      });
      _addnewItemsWindow.doLayout();
      _addnewItemsWindow.show();
      _addnewItemsWindow.center();
    },
    viewUploadedStock: function (fbiu_id, brName) {
      var _addnewItemsWindow = new Ext.Window({
        title: "Current Stock Upload - " + brName,
        iconCls: "dispatch",
        layout: "fit",
        height: 400,
        width: winsize.width * 0.45,
        id: "fsibiudWindow",
        resizable: false,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [_viewUploadedStockDetailsGrid(fbiu_id)],
      });
      _addnewItemsWindow.doLayout();
      _addnewItemsWindow.show();
      _addnewItemsWindow.center();
    },
    initManageRRP: function () {
      var _manageRRPPanelId = "manageRRP";
      var ___manageRRPPanelIdPanelIdPanel = Ext.getCmp(_manageRRPPanelId);
      if (Ext.isEmpty(___manageRRPPanelIdPanelIdPanel)) {
        ___manageRRPPanelIdPanelIdPanel = manageRRPMainPanel(_manageRRPPanelId);
        Application.UI.addTab(___manageRRPPanelIdPanelIdPanel);
        ___manageRRPPanelIdPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(___manageRRPPanelIdPanelIdPanel);
      }
    },
    retalineRRPSave: function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      //var prescriptionForm = Ext.getCmp('rrpFormPanel').getForm();

      var rrp_type = Ext.getCmp("rrp_type").getValue();
      var rrp_detail = Ext.getCmp("rrp_detail").getValue();
      var rrp_factor = Ext.getCmp("rrp_factor").getValue();

      if (rrp_type > 0 && rrp_detail > 0 && rrp_factor > 0) {
        if (rrp_factor > _SESSION.DEFAULT_RRP) {
          Ext.Ajax.request({
            url: modURL + "&op=saveRRPData",
            params: {
              rrp_type: rrp_type,
              rrp_detail: rrp_detail,
              rrp_factor: rrp_factor,
            },
            failure: function (response) {
              Ext.MessageBox.alert("Error", response.responseText);
            },
            success: function (response, options) {
              var tmp = Ext.decode(response.responseText);
              console.log("tmp", tmp);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.message);
                Ext.getCmp("manageRRPGridPanel").getStore().load();
                Ext.getCmp("rrp_type").reset();
                Ext.getCmp("rrp_detail").reset();
                Ext.getCmp("rrp_factor").reset();
              } else if (tmp.success === true && tmp.valid === false) {
                Ext.Msg.alert("Error", tmp.message);
              } else if (tmp.success === false && tmp.valid === false) {
                Ext.Msg.alert("Error", tmp.message);
              } else {
                Ext.Msg.alert("Error", tmp.message);
              }
            },
          });
        } else {
          Ext.Msg.alert(
            "Notification",
            "RRP Factor should be greater than minmum value."
          );
        }
      } else {
        Ext.Msg.alert("Notification", "Please enter all required fields");
      }
    },
    checkRRPAvailable: function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      //var prescriptionForm = Ext.getCmp('rrpFormPanel').getForm();

      var rrp_type = Ext.getCmp("rrp_type").getValue();
      var rrp_detail = Ext.getCmp("rrp_detail").getValue();
      var rrp_factor = Ext.getCmp("rrp_factor").getValue();

      if (rrp_type > 0 && rrp_detail > 0 && rrp_factor > 0) {
        if (rrp_factor > _SESSION.DEFAULT_RRP) {
          Ext.Ajax.request({
            url: modURL + "&op=checkRRPData",
            params: {
              rrp_type: rrp_type,
              rrp_detail: rrp_detail,
              rrp_factor: rrp_factor,
            },
            failure: function (response) {
              Ext.MessageBox.alert("Error", response.responseText);
            },
            success: function (response, options) {
              var tmp = Ext.decode(response.responseText);
              console.log("tmp", tmp);
              if (tmp.success === true && tmp.valid === true) {
                Application.FstockUpload.retalineRRPSave();
              } else if (tmp.success === true && tmp.valid === false) {
                Ext.MessageBox.confirm("Confirm", tmp.message, function (btn) {
                  if (btn == "yes") {
                    Application.FstockUpload.retalineRRPSave();
                  }
                });
              } else if (tmp.success === false && tmp.valid === false) {
                Ext.Msg.alert("Error", tmp.message);
              } else {
                Ext.Msg.alert("Error", tmp.message);
              }
            },
          });
        } else {
          Ext.Msg.alert(
            "Notification",
            "RRP Factor should be greater than minmum value."
          );
        }
      } else {
        Ext.Msg.alert("Notification", "Please enter all required fields");
      }
    },
    updateStock: function (branchId, branchFullName) {
      current_type = 1;
      var resultWindow = new Ext.Window({
        id: "windowFinascopStockAddvenderitemCreatevendoritem",
        title: "Update Stock of -" + branchFullName,
        shadow: false,
        height: 600,
        width: winsize.width * 0.85,
        layout: "border",
        resizable: false,
        plain: true,
        closable: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [
          productsGrid(branchId, branchFullName),
          new Ext.Panel({
            region: "east",
            frame: false,
            border: true,
            layout: "fit",
            autoScroll: false,
            title: "Selected Products",
            bodyStyle: { "background-color": "white" },
            id: "order_parent_panel",
            width: winsize.width * 0.4,
            items: [selectedPrductsGrid(branchId, branchFullName)],
          }),
        ],
        buttons: [
          {
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            text: "Save & Close",
            handler: function () {
              Ext.Ajax.request({
                url: modURL,
                params: {
                  op: "cofirmStockUpdate",
                  uuid: Application.FstockUpload.Cache.updateUUID,
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
                success: function (response, options) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp(
                      "windowFinascopStockAddvenderitemCreatevendoritem"
                    ).close();
                    Ext.getCmp("finstockUploadGridPanel").store.load({
                      params: {
                        branchName: branchId,
                      },
                    });
                    Application.FstockUpload.Cache.updateUUID = "";
                  }
                },
              });
            },
          },
        ],
        listeners: {
          afterrender: function () {
            if (_SESSION.IS_MEDICINE_REQUIRED != 1) {
              Ext.getCmp("radiobuttonFinascopStockId").hide();
            }
          },
        },
      });
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      Ext.Ajax.request({
        url: modURL + "&op=generateUniqueId",
        params: {
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        method: "POST",
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          console.log("temp is -", tmp);
          var uid = tmp.uid;
          Application.FstockUpload.Cache.updateUUID = uid;
        },
      });
      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    },
    saveCheckedItem: function (itemarr, itemType) {
      Ext.Ajax.request({
        url: modURL + "&op=saveUserProducts",
        method: "post",
        params: { itemarr: Ext.encode(itemarr), itemtype: itemType },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {
            Ext.getCmp("gridpanelVendorAdditem")
              .getStore()
              .reload({
                params: {
                  rup_status: 0,
                },
              });
            var item_search_item = Ext.getCmp(
              "radiobuttonFinascopStockId"
            ).getValue();
            var search_bar = Ext.getCmp("radiosearch").getValue();
            if (item_search_item != 0 && search_bar != "") {
              filterItems(search_bar, item_search_item);
            }
          }
        },
      });
    },
    updateStockDetails: function (
      branchId,
      stit_id,
      item_count,
      mrp,
      selling_price,
      uuid
    ) {
      var usdWindow = new Ext.Window({
        id: "windowFinascopStockUpdateWin",
        title: "Update Details",
        shadow: false,
        height: 250,
        width: 250,
        layout: "fit",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [
          new Ext.FormPanel({
            labelAlign: "top",
            width: 250,
            height: 250,
            frame: true,
            id: "setStockDetails",
            items: [
              {
                xtype: "numberfield",
                id: "stitItemCount",
                value: item_count,
                anchor: "98%",
                fieldLabel: "Item Count",
              },
              {
                xtype: "numberfield",
                id: "stitItemMRP",
                value: mrp,
                anchor: "98%",
                fieldLabel: "MRP",
              },
              {
                xtype: "numberfield",
                id: "stitItemSP",
                value: selling_price,
                anchor: "98%",
                fieldLabel: "Selling Price",
              },
            ],
          }),
        ],
        buttons: [
          {
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            text: "Cancel",
            handler: function () {
              Ext.getCmp("windowFinascopStockUpdateWin").close();
            },
          },
          {
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
            text: "Save",
            handler: function () {
              Ext.Ajax.request({
                url: modURL,
                params: {
                  op: "saveStockDetails",
                  branchId: branchId,
                  uuid: uuid,
                  stit_id: stit_id,
                  stitItemCount: Ext.getCmp("stitItemCount").getValue(),
                  stitItemMRP: Ext.getCmp("stitItemMRP").getValue(),
                  stitItemSP: Ext.getCmp("stitItemSP").getValue(),
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
                success: function (response, options) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success === true) {
                    Application.example.msg("Notification", tmp.msg);
                    usdWindow.close();
                    Ext.getCmp("gridpanelVendorAdditem").getStore().reload();
                    var item_search_item = Ext.getCmp(
                      "radiobuttonFinascopStockId"
                    ).getValue();
                    var search_bar = Ext.getCmp("radiosearch").getValue();
                    if (item_search_item != 0 && search_bar != "") {
                      filterItems(search_bar, item_search_item);
                    }
                  }
                },
              });
            },
          },
        ],
        listeners: {
          afterrender: function () {},
        },
      });

      usdWindow.doLayout();
      usdWindow.show();
      usdWindow.center();
    },
    newUpdateStock: function (branchId, branchFullName) {
      current_type = 1;
      var resultWindow = new Ext.Window({
        id: "windowFinascopStockAddvenderitemCreatevendoritem",
        title: "Update Stock of -" + branchFullName,
        shadow: false,
        height: 600,
        width: winsize.width * 0.85,
        layout: "fit",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        closable: false,
        modal: true,
        items: [selectedPrductsGrid(branchId, branchFullName)],
        buttons: [
          {
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            text: "Save & Close",
            handler: function () {
              Ext.Ajax.request({
                url: modURL,
                params: {
                  op: "cofirmStockUpdate",
                  uuid: Application.FstockUpload.Cache.updateUUID,
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
                success: function (response, options) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp(
                      "windowFinascopStockAddvenderitemCreatevendoritem"
                    ).close();
                    Ext.getCmp("finstockUploadGridPanel").store.load({
                      params: {
                        branchName: branchId,
                      },
                    });
                    Application.FstockUpload.Cache.updateUUID = "";
                  }
                },
              });
            },
          },
        ],
        listeners: {
          afterrender: function () {
            if (_SESSION.IS_MEDICINE_REQUIRED != 1) {
              Ext.getCmp("radiobuttonFinascopStockId").hide();
            }
          },
        },
      });
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      Ext.Ajax.request({
        url: modURL + "&op=generateUniqueId",
        params: {
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        method: "POST",
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          console.log("temp is -", tmp);
          var uid = tmp.uid;
          Application.FstockUpload.Cache.updateUUID = uid;
        },
      });
      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    },
    initSubProducts: function () {
      var panelId = "sub_productforpdt";
      var subproduct_main = Ext.getCmp(panelId);
      if (Ext.isEmpty(subproduct_main)) {
        subproduct_main = mainSupProductPanel(panelId);
      }
      //subProductData();
      Application.UI.addTab(subproduct_main);
      subproduct_main.doLayout();
      return subproduct_main;
    },
    deleteSubProduct: function () {
      var t = new Date();
      var subPdtId = arguments[0];
      var parentPdtId = arguments[1];
      var t_stamp = t.format("YmdHis");
      Ext.Ajax.request({
        url: modURL + "&op=deleteSubPdt",
        method: "POST",
        params: { subPdtId: subPdtId, parentPdtId: parentPdtId },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true) {
            Application.example.msg("Success", tmp.message);
            Ext.getCmp("gridpanelSubProduct").getStore().reload();
          } else {
            Ext.Msg.alert("Error", tmp.message);
          }
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
    },
  };
})();
