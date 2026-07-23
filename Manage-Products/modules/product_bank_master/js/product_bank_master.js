Application.ProductBankMaster = (function () {
  var recs_per_page = 26;
  var modURL = "?module=product_bank_master";
  var current_type;
  var loadCount;
  var winLoadMask;
  var winsize = Ext.getBody().getViewSize();
  var loadCount;
  var rowno = new Ext.grid.RowNumberer();
  rowno.width = 30;
  function updatePagination(cmp) {
    recs_per_page = update_recs_per_page(cmp);
  }
  var pdtBankLogGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listImportedLog",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "category",
          "categoryName",
          "subCategory",
          "subCategoryName",
          "totalResults",
          "totalPage",
          "resultsPerPage",
          "insertedData",
          "isComplete",
          "startDate",
          "endDate",
          "importStatus",
          "importedOn",
          "isReconciled",
          "isReconciledStatus",
          "gcpID",
          "manufactureName",
          "relatedBrands",
        ]
      ),
      sortInfo: {
        field: "startDate",
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
  var tooltipRenderer = function (
    value,
    meta,
    record,
    rowindx,
    colindx,
    store
  ) {
    meta.attr = 'ext:qtip="' + record.get("relatedBrands") + '"';
    return value;
  };
  var importedLogGrid = function () {
    var _fsuGridStore = pdtBankLogGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "manufactureName",
        },
        {
          type: "string",
          dataIndex: "gcpID",
        },
        {
          type: "list",
          options: ["Importing Data", "Import Completed"],
          phpMode: true,
          dataIndex: "importStatus",
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
      id: "importedLogGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        getRowClass: function (record, index, params, store) {
          if (record.get("isComplete") == 1) {
            return "finascop_indicateColGUMLEAFGREEN";
          } else {
            return "";
          }
        },
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _fsuFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Id",
          dataIndex: "id",
          sortable: true,
          tooltip: "Id",
          hideable: true,
        },
        {
          header: "Manufacture",
          dataIndex: "manufactureName",
          sortable: true,
          tooltip: "Manufacture",
        },
        {
          header: "Brands",
          dataIndex: "relatedBrands",
          sortable: true,
          tooltip: "Brands",
          renderer: tooltipRenderer,
        },
        {
          header: "GCP",
          dataIndex: "gcpID",
          sortable: true,
          tooltip: "GCP",
        },
        {
          header: "Category Id",
          dataIndex: "category",
          sortable: true,
          tooltip: "Category Id",
          hidden: true,
        },
        {
          header: "Category",
          dataIndex: "categoryName",
          sortable: true,
          tooltip: "Category",
          hideable: true,
          hidden: true,
        },
        {
          header: "Sub Category Id",
          dataIndex: "subCategory",
          sortable: true,
          tooltip: "Sub Category Id",
          hidden: true,
        },
        {
          header: "Sub Category",
          dataIndex: "subCategoryName",
          sortable: true,
          tooltip: "Sub Category",
          hideable: true,
          hidden: true,
        },
        {
          header: "Total Products",
          dataIndex: "totalResults",
          sortable: true,
          tooltip: "Total Products",
          hideable: true,
        },
        {
          header: "Total Pages",
          dataIndex: "totalPage",
          sortable: true,
          tooltip: "Total Pages",
          hideable: true,
        },
        {
          header: "Inserted Products",
          dataIndex: "insertedData",
          sortable: true,
          tooltip: "Inserted Products",
          hideable: true,
        },
        {
          header: "Start Date",
          dataIndex: "startDate",
          sortable: true,
          tooltip: "Start Date",
          hideable: true,
        },
        {
          header: "End Date",
          dataIndex: "endDate",
          hidden: true,
          tooltip: "End Date",
          hideable: true,
        },
        {
          header: "Status",
          dataIndex: "importStatus",
          tooltip: "Status",
          hideable: true,
        },
        {
          header: "Completed",
          dataIndex: "isComplete",
          hidden: true,
          tooltip: "Completed",
          hideable: true,
        },
        {
          header: "Last Import",
          dataIndex: "importedOn",
          hidden: true,
          tooltip: "Last Import",
          hideable: true,
        },
        {
          header: "Reconciled",
          dataIndex: "isReconciledStatus",
          tooltip: "Reconciled",
          hideable: true,
          sortable: true,
        },
        {
          xtype: "actioncolumn",
          hideable: true,
          iconCls: "downarrow",
          tooltip: "Choose Actions",
          items: [
            {
              getClass: function (v, meta, rec) {
                if (rec.get("isComplete") == 1) {
                  this.items[0].tooltip = "Choose Actions";
                  return "downarrow";
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
              if (record.data.isComplete == 1) {
                gsLogActionMenu.showAt(e.getXY());
              }
            },
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          //selectionchange: gridSelectionImporChanged
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },
      tbar: [
        {
          xtype: "button",
          text: "Invoke Import",
          tabIndex: 37,
          handler: function () {
            Ext.Ajax.request({
              waitMsg: "Please wait...",
              url: modURL + "&op=checkImport",
              method: "POST",
              params: {},
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                console.log(tmp);
                if (tmp.success === true) {
                  Application.ProductBankMaster.importDataFromProductMaster();
                } else {
                  Ext.Msg.alert("Notification.", tmp.msg);
                }
              },
              failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.Msg.alert(
                  "Notification",
                  "Data import is processing......"
                );
              },
            });
          },
        },
      ],
    });
    return _fsuGridPanel;
  };
  var gsLogActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Reconcile Products",
        handler: function () {
          var category = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.category;
          var subCategory = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.subCategory;
          var isComplete = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.isComplete;
          var isReconciled = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.isReconciled;
          var gcpID = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.gcpID;
          if (isReconciled == 0) {
            Ext.Ajax.request({
              url: modURL + "&op=checProductValidity",
              method: "post",
              params: {
                category: category,
                subCategory: subCategory,
                gcpID: gcpID,
              },
              success: function (resp) {
                var res = Ext.decode(resp.responseText);
                if (res.success === true) {
                  Ext.MessageBox.confirm(
                    "Confirm",
                    res.msg + "Do you wish to reconcile?",
                    function (btn, text) {
                      if (btn == "yes") {
                        Ext.Ajax.request({
                          url: modURL + "&op=reconcileProduct",
                          method: "post",
                          params: {
                            category: category,
                            subCategory: subCategory,
                            gcpID: gcpID,
                          },
                          success: function (resp) {
                            var res = Ext.decode(resp.responseText);
                            if (res.success == true) {
                              Application.example.msg("Success", res.msg);
                              Ext.getCmp("importedLogGridPanel").getStore().load();
                            } else {
                                var errorMsg = res.msg;
                              Ext.MessageBox.alert("Notification", errorMsg);
                              
                            }
                          },
                        });
                      }
                    }
                  );
                } else {
                    var errorMsg = res.msg;
                    Ext.MessageBox.alert("Notification", errorMsg);
                  //Ext.getCmp("importedLogGridPanel").getStore().load();
                }
              },
            });
          } else {
            Ext.MessageBox.alert("Notification", "Already Reconciled.");
            //Ext.getCmp("importedLogGridPanel").getStore().load();
          }
        },
      },
      {
        text: "View Error Log",
        handler: function () {
          var category = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.category;
          var subCategory = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.subCategory;
          var isReconciled = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.isReconciled;
            var manufactureName = Ext.getCmp("importedLogGridPanel")
            .getSelectionModel()
            .getSelections()[0].data.manufactureName;
          if (isReconciled == 1) {
            Application.ProductBankMaster.getReconciledData(
              category,
              subCategory,manufactureName
            );
          } else {
            Ext.MessageBox.alert("Notification", "Not Yet Reconciled.");
          }
        },
      },
    ],
  });

  var importedLogMainPanel = function (id) {
    var _fsuPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Import Data",
      iconCls: "dispatch",
      id: id,
      items: [importedLogGrid()],
    });
    return _fsuPanel;
  };
  var importedCategoryMainPanel = function (id) {
    var _fsuPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Category",
      iconCls: "dispatch",
      id: id,
      items: [importedCategoryGrid()],
    });
    return _fsuPanel;
  };
  var pdtBankCategoryGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listImportedCategory",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        ["id", "categoryName"]
      ),
      sortInfo: {
        field: "id",
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

  var gridSelectionImporChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("importedCategoryGridPanel")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("importedCategoryGridPanel")
        .getSelectionModel()
        .getSelections()[0].data.id;
    }
  };
  var importedCategoryGrid = function () {
    var _fsuGridStore = pdtBankCategoryGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "categoryName",
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
      id: "importedCategoryGridPanel",
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
          header: "Category Id",
          dataIndex: "id",
          sortable: true,
          tooltip: "Category Id",
          hidden: true,
        },
        {
          header: "Category Name",
          dataIndex: "categoryName",
          sortable: true,
          tooltip: "Category Name",
          hideable: true,
          width: 200,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          //selectionchange: gridSelectionImporChanged
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },
      tbar: [],
    });
    return _fsuGridPanel;
  };
  var gs1CategoryComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getgs1Category",
      method: "post",
      autoLoad: true,
      fields: ["id", "categoryName"],
      root: "data",
    });
    return store;
  };
  var importedSubCategoryMainPanel = function (id) {
    var _iscuPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Sub Category",
      iconCls: "dispatch",
      id: id,
      items: [importedSubCategoryGrid()],
    });
    return _iscuPanel;
  };
  var pdtBankSubCategoryGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listImportedSubCategory",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "subCategoryName",
          root: "data",
        },
        ["id", "categoryName", "subCategoryName"]
      ),
      sortInfo: {
        field: "subCategoryName",
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
  var importedSubCategoryGrid = function () {
    var _fsuGridStore = pdtBankSubCategoryGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "categoryName",
        },
        {
          type: "string",
          dataIndex: "subCategoryName",
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
      id: "importedSubCategoryGridPanel",
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
          header: "Sub Category Id",
          dataIndex: "id",
          sortable: true,
          tooltip: "Category Id",
          hidden: true,
        },
        {
          header: "Sub Category",
          dataIndex: "subCategoryName",
          sortable: true,
          tooltip: "Sub Category",
          hideable: true,
        },
        {
          header: "Category Name",
          dataIndex: "categoryName",
          sortable: true,
          tooltip: "Category Name",
          hideable: true,
          width: 200,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          //selectionchange: gridSelectionImporChanged
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },
      tbar: [],
    });
    return _fsuGridPanel;
  };
  var importedCompanyMainPanel = function (id) {
    var _iCompPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Company",
      iconCls: "dispatch",
      id: id,
      items: [importedCompanyGrid()],
    });
    return _iCompPanel;
  };
  var pdtBankCompanyGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listImportedCompany",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        ["id", "companyName", "gcp"]
      ),
      sortInfo: {
        field: "companyName",
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
  var importedCompanyGrid = function () {
    var _fsuGridStore = pdtBankCompanyGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "companyName",
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
      id: "importedCompanyGridPanel",
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
          header: "Id",
          dataIndex: "id",
          sortable: true,
          tooltip: "Id",
          hidden: true,
        },
        {
          header: "Company",
          dataIndex: "companyName",
          sortable: true,
          tooltip: "Company",
          hideable: true,
        },
        {
          header: "GCP",
          dataIndex: "gcp",
          sortable: true,
          tooltip: "GCP",
          hideable: true,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          //selectionchange: gridSelectionImporChanged
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },
      tbar: [],
    });
    return _fsuGridPanel;
  };
  var importedBrandMainPanel = function (id) {
    var _iBrandPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Brand",
      iconCls: "dispatch",
      id: id,
      items: [importedBrandGrid()],
    });
    return _iBrandPanel;
  };
  var pdtBankBrandGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listImportedBrand",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "brandName",
          "companyName",
          "isEnabled",
          "enableStatus",
          "assignedTO",
          "gpcCode","prdctCount"
        ]
      ),
      sortInfo: {
        field: "brandName",
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
  var importedBrandGrid = function () {
    var _fsuGridStore = pdtBankBrandGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "brandName",
        },
        {
          type: "string",
          dataIndex: "gpcCode",
        },
        {
          type: "string",
          dataIndex: "enableStatus",
        },
        {
          type: "string",
          dataIndex: "assignedTO",
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
      id: "importedBrandGridPanel",
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
          header: "Id",
          dataIndex: "id",
          sortable: true,
          tooltip: "Id",
          hidden: true,
        },
        {
          header: "Brand",
          dataIndex: "brandName",
          sortable: true,
          tooltip: "Brand",
          hideable: true,
        },
        {
          header: "Code",
          dataIndex: "gpcCode",
          sortable: true,
          tooltip: "Code",
          hideable: true,
        },
        {
          header: "Enabled",
          dataIndex: "enableStatus",
          sortable: true,
          tooltip: "Enabled",
          hideable: true,
        },
        {
          header: "Assigned To",
          dataIndex: "assignedTO",
          sortable: true,
          tooltip: "Assigned To",
          hideable: true,
        },{
          header: "Product Count",
          dataIndex:"prdctCount",
          sortable: true,
          tooltip: "Product Count",
          hideable: true,
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
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var isEnabled = data.isEnabled;
                if (isEnabled == 0) {
                  this.items[0].tooltip = "Enable Brand";
                  return "drinactive";
                } else {
                  this.items[0].tooltip = "Disable Brand";
                  return "dractive";
                }
              },
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                var isEnabled = record.get("isEnabled");
                if (isEnabled == 1) {
                  Ext.MessageBox.confirm(
                    "Confirm",
                    "Do you wish to disable brand?",
                    function (btn, text) {
                      if (btn == "yes") {
                        Ext.Ajax.request({
                          waitMsg: "Processing",
                          url: modURL,
                          params: {
                            op: "setBrandEnable",
                            id: record.get("id"),
                            isEnabled: record.get("isEnabled"),
                          },
                          failure: function (response, options) {
                            Ext.MessageBox.alert("Notification", ACTION_FAIL);
                          },
                          success: function (response, options) {
                            eval("var tmp=" + response.responseText);
                            if (tmp.success === true) {
                              Application.example.msg("Notification", tmp.msg);
                              Ext.getCmp("importedBrandGridPanel")
                                .getStore()
                                .reload();
                            }
                          },
                        });
                      }
                    }
                  );
                } else {
                  Ext.Ajax.request({
                    waitMsg: "Processing",
                    url: modURL,
                    params: {
                      op: "setBrandEnable",
                      id: record.get("id"),
                      isEnabled: record.get("isEnabled"),
                    },
                    failure: function (response, options) {
                      Ext.MessageBox.alert("Notification", ACTION_FAIL);
                    },
                    success: function (response, options) {
                      eval("var tmp=" + response.responseText);
                      if (tmp.success === true) {
                        Application.example.msg("Notification", tmp.msg);
                        Ext.getCmp("importedBrandGridPanel")
                          .getStore()
                          .reload();
                      }
                    },
                  });
                }
              },
            },
          ],
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          //selectionchange: gridSelectionImporChanged
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },
      tbar: [],
    });
    return _fsuGridPanel;
  };
  var importedProductMainPanel = function (id) {
    var _iBrandPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Products",
      iconCls: "dispatch",
      id: id,
      items: [
        importedProductBankGrid(),
        new Ext.Panel({
          title: "Product Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelImportedProductBank",
          height: winsize.height * 0.6,
          items: [importedProductDetailsView()],
          buttonAlign: "right",
          fbar: [],
          buttons: [            
            {
              text: "Show Images",
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                
                var selectitem = Ext.getCmp("importedProductBankGridPanel")
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                  var gs1Id = Ext.getCmp("importedProductBankGridPanel")
                  .getSelectionModel()
                  .getSelections()[0].data.id;
                  Application.GS1Products.showImages(gs1Id, 0);
                }
              },
            }
          ]
        }),
      ],
    });
    return _iBrandPanel;
  };
  var pdtBankGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listImportedProductBank",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "brand",
          "name",
          "gtin",
          "caution",
          "sku_code",
          "category",
          "sub_category",
          "gpc_code",
        ]
      ),
      sortInfo: {
        field: "name",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          this.baseParams.category =
            Ext.getCmp("searchBankCategory").getValue();
          this.baseParams.subcategory = Ext.getCmp(
            "searchBankSubCategory"
          ).getValue();
          this.baseParams.brand = Ext.getCmp("searchBankBrand").getValue();
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("importedProductBankGridPanel")
              .getSelectionModel()
              .selectRow(0);
          }
        },
      },
    });
    return _finascopStockUploadList;
  };
  var importedProductBankGrid = function () {
    var gs1CategoryStore = gs1CategoryComboStore();
    var gs1SubCategoryStore = gs1SubCategoryComboStore();
    var _fsuGridStore = pdtBankGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "brand",
        },
        {
          type: "string",
          dataIndex: "name",
        },
        {
          type: "string",
          dataIndex: "gtin",
        },
        {
          type: "string",
          dataIndex: "category",
        },
        {
          type: "string",
          dataIndex: "sub_category",
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
      id: "importedProductBankGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [_fsuFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brand",
          sortable: true,
          width: 80,
          dataIndex: "brand",
        },
        {
          header: "Name",
          sortable: true,
          dataIndex: "name",
          width: 80,
        },
        {
          header: "GTIN",
          sortable: true,
          width: 80,
          dataIndex: "gtin",
        },
        {
          header: "Category",
          sortable: true,
          width: 80,
          dataIndex: "category",
        },
        {
          header: "Sub Category",
          sortable: true,
          width: 80,
          dataIndex: "sub_category",
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_fsuFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionImportPdtBnkChanged,
        },
      }),
      resize: updatePagination,
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {
          _fsuGridStore.load();
        },
      },
      tbar: [
        {
          xtype: "combo",
          mode: "local",
          typeAhead: true,
          editable: true,
          emptyText: "Brand",
          anchor: "100%",
          store: gs1BrandComboStore(),
          id: "searchBankBrand",
          triggerAction: "all",
          displayField: "brandName",
          allowBlank: false,
          valueField: "id",
          hiddenName: "searchBankBrand",
          name: "searchBankBrand",
          minChars: 1,
          listeners: {
            select: function () {
              var category = Ext.getCmp("searchBankCategory").getValue();
              var subcategory = Ext.getCmp("searchBankSubCategory").getValue();
              var brand = Ext.getCmp("searchBankBrand").getValue();
              Ext.getCmp("importedProductBankGridPanel").getStore().clearData();
              Ext.getCmp("importedProductBankGridPanel")
                .getStore()
                .load({
                  params: {
                    category: category,
                    subcategory: subcategory,
                    brand: brand,
                  },
                });
            },
          },
        },
        {
          xtype: "combo",
          store: gs1CategoryStore,
          mode: "local",
          id: "searchBankCategory",
          allowBlank: true,
          emptyText: "Category",
          hiddenName: "searchBankCategory",
          displayField: "categoryName",
          valueField: "id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 102,
          listeners: {
            select: function () {
              var value = Ext.getCmp("searchBankCategory").getValue();
              //var value = Ext.getCmp("primary_businessType").getValue();
              Ext.getCmp("searchBankSubCategory").reset();
              gs1SubCategoryStore.baseParams.category = this.value;
              gs1SubCategoryStore.load();
            },
          },
        },
        "-",
        {
          xtype: "combo",
          store: gs1SubCategoryStore,
          mode: "local",
          id: "searchBankSubCategory",
          allowBlank: true,
          emptyText: "Sub Category",
          hiddenName: "searchBankSubCategory",
          displayField: "subCategoryName",
          valueField: "id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 35,
          listeners: {
            select: function () {
              var category = Ext.getCmp("searchBankCategory").getValue();
              var subcategory = Ext.getCmp("searchBankSubCategory").getValue();
              Ext.getCmp("importedProductBankGridPanel").getStore().clearData();
              Ext.getCmp("importedProductBankGridPanel")
                .getStore()
                .load({
                  params: {
                    category: category,
                    subcategory: subcategory,
                  },
                });
            },
          },
        },
        "-",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/search.png",
          xtype: "button",
          text: "Search",
          tabIndex: 37,
          handler: function () {
            var category = Ext.getCmp("searchBankCategory").getValue();
            var subcategory = Ext.getCmp("searchBankSubCategory").getValue();
            Ext.getCmp("importedProductBankGridPanel").getStore().clearData();
            Ext.getCmp("importedProductBankGridPanel")
              .getStore()
              .load({
                params: {
                  category: category,
                  subcategory: subcategory,
                },
              });
            
          },
        },"->",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/tick.png",
          xtype: "button",
          text: "Enable Brand",
          tabIndex: 37,
          handler: function () {
            var brand = Ext.getCmp("searchBankBrand").getValue();
            if(brand > 0){
              Ext.Ajax.request({
                waitMsg: "Processing",
                url: modURL,
                params: {
                  op: "setBrandEnable",
                  id: brand,
                  from:'source',
                  isEnabled: 0,
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
                success: function (response, options) {
                  eval("var tmp=" + response.responseText);
                  if (tmp.success === true) {
                    Application.example.msg("Notification", tmp.msg);
                   
                  }
                },
              });
            }else{
              Ext.MessageBox.alert(
                "Notification",
                "Choose brand to enable."
              );
            }
            
          },
        },
      ],
    });
    return _fsuGridPanel;
  };
  var gridSelectionImportPdtBnkChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("importedProductBankGridPanel")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("importedProductBankGridPanel")
        .getSelectionModel()
        .getSelections()[0].data.id;
      Application.ProductBankMaster.ViewMode(ID);
    }
  };
  var importedProductDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "xtemplateMasterImportedProductBankViewDetails",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {name} </td></tr>',
        '<tr><th width="40%">Brand </th><td>  {brand} </td></tr>',
        '<tr><th width="40%">Category </th><td>  {category} </td></tr>',
        '<tr><th width="40%">Sub Category </th><td>  {sub_category} </td></tr>',
        '<tr><th width="40%">GTIN </th><td>  {gtin} </td></tr>',
        '<tr><th width="40%">Caution </th><td>  {caution} </td></tr>',
        '<tr><th width="40%">SKU Code </th><td>  {sku_code} </td></tr>',
        '<tr><th width="40%">Description </th><td>  {description} </td></tr>',
        '<tr><th width="40%">GPC Code </th><td>  {gpc_code} </td></tr>',
        '<tr><th width="40%">Marketing Info </th><td>  {marketing_info} </td></tr>',
        '<tr><th width="40%">Derived Description </th><td>  {derived_description} </td></tr>',
        '<tr><th width="40%">Country of Orgin </th><td>  {country_of_origin} </td></tr>',
        '<tr><th width="40%">Type </th><td>  {type} </td></tr>',
        '<tr><th width="40%">Packing Type </th><td>  {packaging_type} </td></tr>',
        '<tr><th width="40%">Primary GTIN </th><td>  {primary_gtin} </td></tr>',
        '<tr><th width="40%">Company </th><td>  {company_detail} </td></tr>',
        '<tr><th width="40%">HS Code </th><td>  {hs_code} </td></tr>',
        '<tr><th width="40%">GST </th><td>  {igst} </td></tr>',
        '<tr><th width="40%">Margin </th><td>  {margin} </td></tr>',
        '<tr><th width="40%">Weights & Measures </th><td>  {weights_and_measures} </td></tr>',
        '<tr><th width="40%">Dimensions </th><td>  {dimensions} </td></tr>',
        '<tr><th width="40%">Attributes </th><td>  {attributes} </td></tr>',
        '<tr><th width="40%">Case Configuration </th><td>  {case_configuration} </td></tr>',
        "</table>",
        "</div>"
      ),
    });
  };
  var gs1BrandComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getgs1AllBrands",
      method: "post",
      autoLoad: true,
      fields: ["id", "brandName"],
      root: "data",
    });
    return store;
  };
  var gs1CompanyComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getgs1Company",
      method: "post",
      autoLoad: true,
      fields: ["id", "gcp"],
      root: "data",
    });
    return store;
  };
  var gs1BrandCompanyComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getgs1CompanyBrand",
      method: "post",
      autoLoad: true,
      fields: ["prefix", "brand"],
      root: "data",
    });
    return store;
  };
  var gs1SubCategoryComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getgs1SubCategory",
      method: "post",
      autoLoad: true,
      fields: ["id", "subCategoryName"],
      root: "data",
    });
    return store;
  };
  var reconciledDataStore = function (category, subcategory,manufactureName) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=reconciledProduct",
        method: "post",
      }),
      fields: [
        "id",
        "brand",
        "name",
        "gtin",
        "description",
        "category",
        "sub_category",
        "gpc_code",
        "hs_code",
        "igst",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.category = category;
          this.baseParams.subcategory = subcategory;
          this.baseParams.manufactureName =manufactureName;
        },
      },
    });
    return _Store;
  };
  var reconciledDataGrid = function (category, subcategory,manufactureName) {
    var reconciledDataStorefn = reconciledDataStore(category, subcategory,manufactureName);
    var reconciledData_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "name",
        },
        {
          type: "string",
          dataIndex: "brand",
        },
        {
          type: "string",
          dataIndex: "gtin",
        },
        {
          type: "string",
          dataIndex: "category",
        },
        {
          type: "string",
          dataIndex: "sub_category",
        },
      ],
    });
    reconciledData_filter.remote = true;
    reconciledData_filter.autoReload = true;
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      height: 500,
      width: winsize.width * 0.7,
      store: reconciledDataStorefn,
      //iconCls: 'money',
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforRelatedBrands",
      plugins: [reconciledData_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        rowno,
        { header: "Name", width: 200, dataIndex: "name", sortable: true },
        { header: "GTIN", dataIndex: "gtin", sortable: true },
        { header: "Brand", dataIndex: "brand", hideable: true, sortable: true },
        {
          header: "Category",
          dataIndex: "category",
          hideable: true,
          sortable: true,
        },
        {
          header: "Sub Category",
          dataIndex: "sub_category",
          hideable: true,
          sortable: false,
        },
        { header: "HSN", dataIndex: "hs_code", sortable: true },
        { header: "GST", dataIndex: "igst", sortable: true },
        { width: 20, dataIndex: " " },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: reconciledDataStorefn,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
        plugins: [reconciledData_filter],
      }),
      listeners: {
        afterrender: function () {},
        rowdblclick: function (grid, rowIndex, e) {
          var rec = grid.getStore().getAt(rowIndex);
        },
      },
    });
    return _dispatchgridPanel;
  };
  var check_box = new Ext.grid.CheckboxSelectionModel({
    multiSelect: true,
    checkOnly: true,
    showHeaderCheckbox: false,
  });
  var availableBrandStore = function (userId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAvailableBrands",
        method: "post",
      }),
      fields: ["id", "brandName","pdtCount"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function (store, e) {},
      },
    });
    return _Store;
  };
  var userMappedBrandStore = function (userId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listUserMappedBrands",
        method: "post",
      }),
      fields: ["id", "brandName","pdtCount"],
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
  var availableBrandGrid = function (userId) {
    var availableBrand_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "brandName",
        },
      ],
    });
    availableBrand_filter.remote = true;
    availableBrand_filter.autoReload = true;
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      title: "Unassigned Brands",
      frame: false,
      border: false,
      loadMask: true,
      store: availableBrandStore(userId),
      //iconCls: 'money',
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforAvailableBrands",
      sm: check_box,
      plugins: [availableBrand_filter],
      columns: [
        check_box,
        new Ext.grid.RowNumberer(),
        {
          header: "Brands",
          dataIndex: "brandName",
          sortable: true,
          tooltip: "Brands",
          hideable: false,
          width: 200,
        },{
          header: "Count",
          dataIndex:"pdtCount",
          sortable: true,
          tooltip: "Count",
          hideable: false
        }
      ],
      bbar: [
        "->",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/Forward.png",
          xtype: "button",
          text: "Map to User",
          handler: function () {
            var selectitem = Ext.getCmp("gridpanelforAvailableBrands")
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var brandarr = [];
            for (var i = 0; i < selectedcount; i++) {
              brandarr.push(selectitem[i].data.id);
            }
            if (selectedcount != 0) {
              Application.ProductBankMaster.mapBrandToUser(brandarr, userId);
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
    return _dispatchgridPanel;
  };
  var mappedUserBrands = function (userId) {
    var mappedUserBrand_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "brandName",
        },
      ],
    });
    mappedUserBrand_filter.remote = true;
    mappedUserBrand_filter.autoReload = true;
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: userMappedBrandStore(userId),
      //iconCls: 'money',
      height: 500,
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforUserMappedBrands",
      plugins: [mappedUserBrand_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brands",
          dataIndex: "brandName",
          sortable: true,
          tooltip: "Brands",
          hideable: false,
          width: 200,
        },{
          header: "Count",
          dataIndex: "pdtCount",
          sortable: true,
          tooltip: "Count",
          hideable: false
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
              tooltip: "Cancel Brand",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);

                Ext.Ajax.request({
                  waitMsg: "Processing",
                  url: modURL,
                  params: {
                    op: "removeBrandFromUser",
                    brandId: record.get("id"),
                    userId: userId,
                  },
                  failure: function (response, options) {
                    Ext.MessageBox.alert("Notification", ACTION_FAIL);
                  },
                  success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {
                      Application.example.msg("Notification", tmp.msg);
                      Ext.getCmp("gridpanelforUserMappedBrands")
                        .getStore()
                        .load({
                          params: {
                            userId: userId,
                          },
                        });
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
      },
      listeners: {
        afterrender: function () {},
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _dispatchgridPanel;
  };
  var skippedProductMainPanel = function (id) {
    var _iBrandPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Skipped Products",
      iconCls: "dispatch",
      id: id,
      items: [
        skippedProductBankGrid(),
        new Ext.Panel({
          title: "Product Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelSkippedProductBank",
          height: winsize.height * 0.6,
          items: [importedProductDetailsView()],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return _iBrandPanel;
  };
  var skippedProductBankGrid = function () {
    var gs1CategoryStore = gs1CategoryComboStore();
    var gs1SubCategoryStore = gs1SubCategoryComboStore();
    var _fsuGridStore = skippedPdtBankGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "brand",
        },
        {
          type: "string",
          dataIndex: "name",
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
      id: "skippedProductBankGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [_fsuFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brand",
          sortable: true,
          width: 80,
          dataIndex: "brand",
        },
        {
          header: "Name",
          sortable: true,
          dataIndex: "name",
          width: 80,
        },
        {
          header: "GTIN",
          sortable: true,
          width: 80,
          dataIndex: "gtin",
        },
        {
          header: "Category",
          sortable: true,
          width: 80,
          dataIndex: "category",
        },
        {
          header: "Sub Category",
          sortable: true,
          width: 80,
          dataIndex: "sub_category",
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_fsuFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionSkipPdtBnkChanged,
        },
      }),
      resize: updatePagination,
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {
          _fsuGridStore.load();
        },
      },
      tbar: [
        {
          xtype: "combo",
          store: gs1CategoryStore,
          mode: "local",
          id: "skipBankCategory",
          allowBlank: true,
          emptyText: "Category",
          hiddenName: "skipBankCategory",
          displayField: "categoryName",
          valueField: "id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 102,
          listeners: {
            select: function () {
              var value = Ext.getCmp("skipBankCategory").getValue();
              //var value = Ext.getCmp("primary_businessType").getValue();
              Ext.getCmp("skipBankSubCategory").reset();
              gs1SubCategoryStore.baseParams.category = this.value;
              gs1SubCategoryStore.load();
            },
          },
        },
        "-",
        {
          xtype: "combo",
          store: gs1SubCategoryStore,
          mode: "local",
          id: "skipBankSubCategory",
          allowBlank: true,
          emptyText: "Sub Category",
          hiddenName: "skipBankSubCategory",
          displayField: "subCategoryName",
          valueField: "id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 35,
          listeners: {
            select: function () {
              var category = Ext.getCmp("skipBankCategory").getValue();
              var subcategory = Ext.getCmp("skipBankSubCategory").getValue();
              Ext.getCmp("skippedProductBankGridPanel").getStore().clearData();
              Ext.getCmp("skippedProductBankGridPanel")
                .getStore()
                .load({
                  params: {
                    category: category,
                    subcategory: subcategory,
                  },
                });
            },
          },
        },
        "-",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/search.png",
          xtype: "button",
          text: "Search",
          tabIndex: 37,
          handler: function () {
            var category = Ext.getCmp("skipBankCategory").getValue();
            var subcategory = Ext.getCmp("skipBankSubCategory").getValue();
            Ext.getCmp("skippedProductBankGridPanel").getStore().clearData();
            Ext.getCmp("skippedProductBankGridPanel")
              .getStore()
              .load({
                params: {
                  category: category,
                  subcategory: subcategory,
                },
              });
            _;
          },
        },
      ],
    });
    return _fsuGridPanel;
  };
  var skippedPdtBankGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSkippedProductBank",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "brand",
          "name",
          "gtin",
          "caution",
          "sku_code",
          "category",
          "sub_category",
          "gpc_code",
        ]
      ),
      sortInfo: {
        field: "name",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          this.baseParams.category = Ext.getCmp("skipBankCategory").getValue();
          this.baseParams.subcategory = Ext.getCmp(
            "skipBankSubCategory"
          ).getValue();
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("skippedProductBankGridPanel")
              .getSelectionModel()
              .selectRow(0);
          }
        },
      },
    });
    return _finascopStockUploadList;
  };
  var skippedProductDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "xtemplateMasterSkippedProductBankViewDetails",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Name </th><td>  {name} </td></tr>',
        '<tr><th width="40%">Brand </th><td>  {brand} </td></tr>',
        '<tr><th width="40%">Category </th><td>  {category} </td></tr>',
        '<tr><th width="40%">Sub Category </th><td>  {sub_category} </td></tr>',
        '<tr><th width="40%">GTIN </th><td>  {gtin} </td></tr>',
        '<tr><th width="40%">Caution </th><td>  {caution} </td></tr>',
        '<tr><th width="40%">SKU Code </th><td>  {sku_code} </td></tr>',
        '<tr><th width="40%">Description </th><td>  {description} </td></tr>',
        '<tr><th width="40%">GPC Code </th><td>  {gpc_code} </td></tr>',
        '<tr><th width="40%">Marketing Info </th><td>  {marketing_info} </td></tr>',
        '<tr><th width="40%">Derived Description </th><td>  {derived_description} </td></tr>',
        '<tr><th width="40%">Country of Orgin </th><td>  {country_of_origin} </td></tr>',
        '<tr><th width="40%">Type </th><td>  {type} </td></tr>',
        '<tr><th width="40%">Packing Type </th><td>  {packaging_type} </td></tr>',
        '<tr><th width="40%">Primary GTIN </th><td>  {primary_gtin} </td></tr>',
        '<tr><th width="40%">Company </th><td>  {company_detail} </td></tr>',
        '<tr><th width="40%">HS Code </th><td>  {hs_code} </td></tr>',
        '<tr><th width="40%">GST </th><td>  {igst} </td></tr>',
        '<tr><th width="40%">Margin </th><td>  {margin} </td></tr>',
        '<tr><th width="40%">Weights & Measures </th><td>  {weights_and_measures} </td></tr>',
        '<tr><th width="40%">Dimensions </th><td>  {dimensions} </td></tr>',
        '<tr><th width="40%">Attributes </th><td>  {attributes} </td></tr>',
        '<tr><th width="40%">Case Configuration </th><td>  {case_configuration} </td></tr>',
        "</table>",
        "</div>"
      ),
    });
  };
  var gridSelectionSkipPdtBnkChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("skippedProductBankGridPanel")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("skippedProductBankGridPanel")
        .getSelectionModel()
        .getSelections()[0].data.id;
      Application.ProductBankMaster.ViewSkipMode(ID);
    }
  };
  var mappedBrandMainPanel = function (id) {
    var _iBrandPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Mapped Brands",
      iconCls: "dispatch",
      id: id,
      items: [mappedBrandGrid()],
    });
    return _iBrandPanel;
  };
  var mappedBrandGrid = function () {
    var _fsuGridStore = pdtBankMappedBrandGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "brandName",
        },
        {
          type: "string",
          dataIndex: "companyName",
        },
        {
          type: "string",
          dataIndex: "enableStatus",
        },
        {
          type: "string",
          dataIndex: "assignedTO",
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
      id: "mappedBrandGridPanel",
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
          header: "Id",
          dataIndex: "id",
          sortable: true,
          tooltip: "Id",
          hidden: true,
        },
        {
          header: "Brand",
          dataIndex: "brandName",
          sortable: true,
          tooltip: "Brand",
          hideable: true,
        },
        {
          header: "Company",
          dataIndex: "companyName",
          sortable: true,
          tooltip: "Company",
          hideable: true,
        },
        {
          header: "Enabled",
          dataIndex: "enableStatus",
          sortable: true,
          tooltip: "Enabled",
          hideable: true,
        },
        {
          header: "Assigned To",
          dataIndex: "assignedTO",
          sortable: true,
          tooltip: "Assigned To",
          hideable: true,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          //selectionchange: gridSelectionImporChanged
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },
      tbar: [],
    });
    return _fsuGridPanel;
  };
  var pdtBankMappedBrandGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listMappedBrand",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "brandName",
          "companyName",
          "isEnabled",
          "enableStatus",
          "assignedTO",
        ]
      ),
      sortInfo: {
        field: "brandName",
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
  var importedBrandCompanyMainPanel = function (id) {
    var _iBrandPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Activate Brands",
      iconCls: "dispatch",
      id: id,
      items: [
        importedBrandComapnyGrid(),
        new Ext.Panel({
          title: "Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelBrandCompany",
          height: winsize.height * 0.6,
          items: [importedBrandCompanyDetailsView(), relatedBrandsofCompany()],
          buttonAlign: "right",
          fbar: [],
          buttons: [
            {
              text: "Activate Brands",
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("importedBrandCompanyGridPanel")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("importedBrandCompanyGridPanel")
                    .getSelectionModel()
                    .getSelections()[0].data.id;
                  var prefix = Ext.getCmp("importedBrandCompanyGridPanel")
                    .getSelectionModel()
                    .getSelections()[0].data.prefix;
                  Ext.Ajax.request({
                    url: modURL + "&op=enablerelatedBrand",
                    method: "POST",
                    waitMsg: "Processing",
                    params: {
                      prefix: prefix,
                    },
                    failure: function (response, options) {
                      var tmp = Ext.util.JSON.decode(response.responseText);
                      Ext.MessageBox.alert("Error", "tmp.msg");
                    },
                    success: function (response, options) {
                      var tmp = Ext.decode(response.responseText);

                      if (tmp.success === true) {
                        Application.example.msg("Notification", tmp.msg);
                        Ext.getCmp("importedBrandCompanyGridPanel")
                          .getStore()
                          .load();
                      } else {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                      }
                    },
                  });
                }
              },
            },
          ],
        }),
      ],
    });
    return _iBrandPanel;
  };
  var relatedBrandGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listRelatedBrand",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        ["id", "brandname"]
      ),
      sortInfo: {
        field: "brandname",
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
    return _finascopStockUploadList;
  };
  var relatedBrandsofCompany = function () {
    var _fsuGridStore = relatedBrandGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "brandname",
        },
      ],
    });
    _fsuFilter.remote = true;
    _fsuFilter.autoReload = true;
    var _fsuGridPanel = new Ext.grid.GridPanel({
      region: "south",
      width: winsize.width * 0.38,
      height: winsize.height * 0.4,
      autoScroll: true,
      layout: "fit",
      hidden: true,
      frame: false,
      border: false,
      loadMask: true,
      title: "Other Brands of Manufacture",
      store: _fsuGridStore,
      id: "relatedBrandGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [_fsuFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brand",
          sortable: true,
          width: 80,
          dataIndex: "brandname",
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      resize: updatePagination,
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {
          _fsuGridStore.load();
        },
      },
      tbar: [],
    });
    return _fsuGridPanel;
  };
  var brandCmpnyGridStore = function () {
    var _finascopStockUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listBrandCompany",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "brandCompany",
          "prefix",
          "companyname",
          "brandname",
          "productCount",
          "status",
          "isEnabled",
        ]
      ),
      sortInfo: {
        field: "brandname",
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
    return _finascopStockUploadList;
  };
  var importedBrandComapnyGrid = function () {
    var _fsuGridStore = brandCmpnyGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "brandCompany",
        },
        {
          type: "string",
          dataIndex: "companyname",
        },
        {
          type: "string",
          dataIndex: "prefix",
        },
        {
          type: "string",
          dataIndex: "brandname",
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
      id: "importedBrandCompanyGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        getRowClass: function (record, index, params, store) {
          if (record.get("isEnabled") > 0) {
            return "finascop_indicateColGUMLEAFGREEN";
          } else {
            return "";
          }
        },
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [_fsuFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brand & Manufacture",
          sortable: true,
          width: 80,
          dataIndex: "brandCompany",
        },
        {
          header: "Manufacture",
          sortable: true,
          dataIndex: "companyname",
          width: 80,
        },
        {
          header: "GCP",
          sortable: true,
          width: 80,
          dataIndex: "prefix",
        },
        {
          xtype: "actioncolumn",
          header: "-",
          width: 30,
          hideable: false,
          items: [],
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [_fsuFilter],
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionImportBrandCmpnyChanged,
        },
      }),
      resize: updatePagination,
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {
          _fsuGridStore.load();
        },
      },
      tbar: [],
    });
    return _fsuGridPanel;
  };
  var gridSelectionImportBrandCmpnyChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("importedBrandCompanyGridPanel")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("importedBrandCompanyGridPanel")
        .getSelectionModel()
        .getSelections()[0].data.id;
      var prefix = Ext.getCmp("importedBrandCompanyGridPanel")
        .getSelectionModel()
        .getSelections()[0].data.prefix;
      Application.ProductBankMaster.ViewModeBC(ID, prefix);
    }
  };
  var importedBrandCompanyDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      height: winsize.height * 0.2,
      autoScroll: true,
      id: "xtemplateMasterBrandCompanyViewDetails",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Brand </th><td>  {brandname} </td></tr>',
        '<tr><th width="40%">Manufacture </th><td>  {companyname} </td></tr>',
        '<tr><th width="40%">Product Count </th><td>  {productCount} </td></tr>',
        "</table>",
        "</div>"
      ),
    });
  };
  return {
    Cache: {},
    importedLogData: function () {
      var _prdctBankImportLogPanelId = "panelMasterMainProductBankLog";
      var __prdctBankImportLogPanelIdPanel = Ext.getCmp(
        _prdctBankImportLogPanelId
      );
      if (Ext.isEmpty(__prdctBankImportLogPanelIdPanel)) {
        __prdctBankImportLogPanelIdPanel = importedLogMainPanel(
          _prdctBankImportLogPanelId
        );
        Application.UI.addTab(__prdctBankImportLogPanelIdPanel);
        __prdctBankImportLogPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__prdctBankImportLogPanelIdPanel);
      }
    },
    skippedProductBankData: function () {
      var _skipPrdctBankImportPanelId = "panelMasterMainProductBankSkip";
      var __skipPprdctBankImportPanelIdPanel = Ext.getCmp(
        _skipPrdctBankImportPanelId
      );
      if (Ext.isEmpty(__skipPprdctBankImportPanelIdPanel)) {
        __skipPprdctBankImportPanelIdPanel = skippedProductMainPanel(
          _skipPrdctBankImportPanelId
        );
        Application.UI.addTab(__skipPprdctBankImportPanelIdPanel);
        __skipPprdctBankImportPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__skipPprdctBankImportPanelIdPanel);
      }
    },
    importedProductBankData: function () {
      var _prdctBankImportPanelId = "panelMasterMainProductBank";
      var __prdctBankImportPanelIdPanel = Ext.getCmp(_prdctBankImportPanelId);
      if (Ext.isEmpty(__prdctBankImportPanelIdPanel)) {
        __prdctBankImportPanelIdPanel = importedProductMainPanel(
          _prdctBankImportPanelId
        );
        Application.UI.addTab(__prdctBankImportPanelIdPanel);
        __prdctBankImportPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__prdctBankImportPanelIdPanel);
      }
    },
    importedBrandData: function () {
      var _prdctBankBrandImportPanelId = "panelMasterMainBrand";
      var __prdctBankBrandImportPanelIdPanel = Ext.getCmp(
        _prdctBankBrandImportPanelId
      );
      if (Ext.isEmpty(__prdctBankBrandImportPanelIdPanel)) {
        __prdctBankBrandImportPanelIdPanel = importedBrandMainPanel(
          _prdctBankBrandImportPanelId
        );
        Application.UI.addTab(__prdctBankBrandImportPanelIdPanel);
        __prdctBankBrandImportPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__prdctBankBrandImportPanelIdPanel);
      }
    },
    importedCompanyData: function () {
      var _prdctBankCompanyImportPanelId = "panelMasterMainCompany";
      var __prdctBankCompanyImportPanelIdPanel = Ext.getCmp(
        _prdctBankCompanyImportPanelId
      );
      if (Ext.isEmpty(__prdctBankCompanyImportPanelIdPanel)) {
        __prdctBankCompanyImportPanelIdPanel = importedCompanyMainPanel(
          _prdctBankCompanyImportPanelId
        );
        Application.UI.addTab(__prdctBankCompanyImportPanelIdPanel);
        __prdctBankCompanyImportPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__prdctBankCompanyImportPanelIdPanel);
      }
    },
    importedCategoryData: function () {
      var _prdctBankCategImportPanelId = "panelMasterMainCategory";
      var __prdctBankCategImportPanelIdPanel = Ext.getCmp(
        _prdctBankCategImportPanelId
      );
      if (Ext.isEmpty(__prdctBankCategImportPanelIdPanel)) {
        __prdctBankCategImportPanelIdPanel = importedCategoryMainPanel(
          _prdctBankCategImportPanelId
        );
        Application.UI.addTab(__prdctBankCategImportPanelIdPanel);
        __prdctBankCategImportPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__prdctBankCategImportPanelIdPanel);
      }
    },
    importedSubCategoryData: function () {
      var _prdctBankSubCategImportPanelId = "panelMasterMainSubCategory";
      var __prdctBankSubCategImportPanelIdPanel = Ext.getCmp(
        _prdctBankSubCategImportPanelId
      );
      if (Ext.isEmpty(__prdctBankSubCategImportPanelIdPanel)) {
        __prdctBankSubCategImportPanelIdPanel = importedSubCategoryMainPanel(
          _prdctBankSubCategImportPanelId
        );
        Application.UI.addTab(__prdctBankSubCategImportPanelIdPanel);
        __prdctBankSubCategImportPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__prdctBankSubCategImportPanelIdPanel);
      }
    },
    importDataMasters: function (itemarr) {
      var gs1BrandStore = gs1BrandComboStore();
      var gs1CompanyStore = gs1CompanyComboStore();
      var gs1CategoryStore = gs1CategoryComboStore();
      var gs1SubCategoryStore = gs1SubCategoryComboStore();
      var addNewReturnOrderWindow = new Ext.Window({
        id: "importWindowForProductMaster",
        iconCls: "vender-items",
        shadow: false,
        width: winsize.width * 0.3,
        height: winsize.height * 0.4,
        title: "Import Masters",
        layout: "fit",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [
          new Ext.FormPanel({
            layout: "form",
            id: "importFormPanel",
            height: 150,
            columnWidth: 1,
            frame: true,
            border: true,
            labelAlign: "top",
            items: [
              {
                xtype: "radiogroup",
                anchor: "98%",
                mode: "remote",
                allowBlank: false,
                forceSelection: true,
                triggerAction: "all",
                lazyRender: true,
                id: "importType",
                minChars: 2,
                tabIndex: 12,
                items: [
                  {
                    boxLabel: "Category",
                    id: "importType1",
                    name: "importType",
                    inputValue: "Category",
                    checked: true,
                    listeners: {
                      check: function (rgp, checked) {
                        if (checked == true) {
                          Ext.getCmp("pdtBankCategory").hide();
                          Ext.getCmp("pdtBankSubCategory").hide();
                        }
                      },
                    },
                  },
                  {
                    boxLabel: "Sub Category",
                    id: "importType2",
                    name: "importType",
                    inputValue: "Sub Category",
                    listeners: {
                      check: function (rgp, checked) {
                        if (checked == true) {
                          Ext.getCmp("pdtBankCategory").show();
                          Ext.getCmp("pdtBankCategory").getStore().load();
                        }
                      },
                    },
                  },
                  {
                    boxLabel: "Company",
                    id: "importType3",
                    name: "importType",
                    inputValue: "Company",
                    listeners: {
                      check: function (rgp, checked) {
                        if (checked == true) {
                          Ext.getCmp("pdtBankCategory").hide();
                          Ext.getCmp("pdtBankSubCategory").hide();
                          Ext.getCmp("pdtBankCompany").hide();
                        }
                      },
                    },
                  },
                ],
              },
              {
                xtype: "combo",
                store: gs1CategoryStore,
                mode: "local",
                id: "pdtBankCategory",
                allowBlank: true,
                fieldLabel: "Category",
                hiddenName: "pdtBankCategory",
                displayField: "categoryName",
                valueField: "id",
                typeAhead: true,
                forceSelection: true,
                editable: true,
                minChars: 2,
                hidden: true,
                anchor: "98%",
                triggerAction: "all",
                lazyRender: true,
                tabIndex: 102,
                listeners: {
                  select: function () {
                    var value = Ext.getCmp("pdtBankCategory").getValue();
                    //var value = Ext.getCmp("primary_businessType").getValue();
                    gs1SubCategoryStore.baseParams.category = this.value;
                    gs1SubCategoryStore.load();
                  },
                },
              },
              {
                xtype: "combo",
                store: gs1SubCategoryStore,
                mode: "local",
                hidden: true,
                id: "pdtBankSubCategory",
                allowBlank: true,
                fieldLabel: "Sub Category",
                hiddenName: "pdtBankSubCategory",
                displayField: "subCategoryName",
                valueField: "id",
                typeAhead: true,
                forceSelection: true,
                editable: true,
                minChars: 2,
                anchor: "98%",
                triggerAction: "all",
                lazyRender: true,
                tabIndex: 35,
                listeners: {
                  select: function () {},
                },
              },
              {
                xtype: "combo",
                store: gs1CompanyStore,
                mode: "local",
                hidden: true,
                id: "pdtBankCompany",
                allowBlank: true,
                fieldLabel: "GCP",
                hiddenName: "pdtBankCompany",
                displayField: "gcp",
                valueField: "id",
                typeAhead: true,
                forceSelection: true,
                editable: true,
                minChars: 2,
                anchor: "98%",
                triggerAction: "all",
                lazyRender: true,
                tabIndex: 36,
                listeners: {
                  select: function () {},
                },
              },
            ],
          }),
        ],
        bbar: [
          {
            hidden: true,
            xtype: "checkbox",
            id: "updateProducts",
            tabIndex: 103,
            align: "left",
            inputValue: 1,
            anchor: "99%",
            name: "updateProducts",
            labelAlign: "right",
            boxLabel: "Update Existing",
            listeners: {
              check: function (checkbox, checked) {
                if (checked == true) {
                  Application.GS1Products.Cache.updateProducts = 1;
                } else {
                  Application.GS1Products.Cache.updateProducts = 0;
                }
              },
            },
          },
          "->",
          {
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
            text: "Import",
            align: "right",
            handler: function () {
              var importType = Ext.getCmp("importType").getValue();
              var category = Ext.getCmp("pdtBankCategory").getValue();
              var subCategory = Ext.getCmp("pdtBankSubCategory").getValue();
              var brand = Ext.getCmp("pdtBankCompany").getValue();
              var updateProducts;
              if (Ext.getCmp("updateProducts").getValue() == true) {
                updateProducts = 1;
              } else {
                updateProducts = 0;
              }
              Ext.Ajax.request({
                waitMsg: "Please wait...",
                url: modURL + "&op=importDataFromProductBank",
                method: "POST",
                params: {
                  importType: importType,
                  category: category,
                  brand: brand,
                  subCategory: subCategory,
                  updateProducts: updateProducts,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  console.log(tmp);
                  if (tmp.status === "success") {
                    Ext.Msg.alert("Notification.", tmp.message);
                  } else {
                    Ext.Msg.alert("Notification.", tmp.msg);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.Msg.alert(
                    "Notification",
                    "Data import is processing......"
                  );
                },
              });
              addNewReturnOrderWindow.close();
            },
          },
          {
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
            text: "Get Details",
            hidden: true,
            align: "right",
            handler: function () {
              var importType = Ext.getCmp("importType").getValue();
              var category = Ext.getCmp("pdtBankCategory").getValue();
              var subCategory = Ext.getCmp("pdtBankSubCategory").getValue();
              var brand = Ext.getCmp("pdtBankCompany").getValue();
              var updateProducts;
              if (Ext.getCmp("updateProducts").getValue() == true) {
                updateProducts = 1;
              } else {
                updateProducts = 0;
              }
              Ext.Ajax.request({
                waitMsg: "Please wait...",
                url: modURL + "&op=getDataFromProductBank",
                method: "POST",
                params: {
                  importType: importType,
                  category: category,
                  brand: brand,
                  subCategory: subCategory,
                  updateProducts: updateProducts,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  console.log(tmp);
                  if (tmp.status === "success") {
                    Ext.Msg.alert("Notification.", tmp.message);
                  } else {
                    Ext.Msg.alert("Notification.", tmp.message);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.Msg.alert("Error", "Data import is processing......");
                },
              });
            },
          },
        ],
        listeners: {
          load: function () {},
        },
      });

      addNewReturnOrderWindow.doLayout();
      addNewReturnOrderWindow.show();
      addNewReturnOrderWindow.center();
    },
    importDataFromProductMaster: function (itemarr) {
      var gs1BrandStore = gs1BrandComboStore();
      var gs1BrandCompanyStore = gs1BrandCompanyComboStore();
      var gs1CategoryStore = gs1CategoryComboStore();
      var gs1SubCategoryStore = gs1SubCategoryComboStore();
      var addNewReturnOrderWindow = new Ext.Window({
        id: "importWindowForProductMaster",
        iconCls: "vender-items",
        shadow: false,
        width: winsize.width * 0.3,
        height: winsize.height * 0.4,
        title: "Import Data From Product Bank",
        layout: "fit",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [
          new Ext.FormPanel({
            layout: "form",
            id: "importFormPanel",
            height: 150,
            columnWidth: 1,
            frame: true,
            border: true,
            labelAlign: "top",
            items: [
              {
                xtype: "radiogroup",
                anchor: "98%",
                mode: "remote",
                hidden: true,
                allowBlank: false,
                forceSelection: true,
                triggerAction: "all",
                lazyRender: true,
                id: "importType",
                minChars: 2,
                tabIndex: 12,
                items: [
                  {
                    boxLabel: "Product",
                    id: "importType4",
                    name: "importType",
                    checked: true,
                    inputValue: "Product",
                    listeners: {
                      check: function (rgp, checked) {
                        if (checked == true) {
                        }
                      },
                    },
                  },
                ],
              },
              {
                xtype: "radiogroup",
                anchor: "98%",
                mode: "remote",
                allowBlank: false,
                forceSelection: true,
                triggerAction: "all",
                lazyRender: true,
                id: "importWise",
                minChars: 2,
                tabIndex: 12,
                items: [
                  {
                    boxLabel: "Category",
                    id: "importWise1",
                    name: "importWise",
                    inputValue: "Category",
                    hidden: true,
                    listeners: {
                      check: function (rgp, checked) {
                        if (checked == true) {
                          Ext.getCmp("pdtBankCategory").show();
                          Ext.getCmp("pdtBankSubCategory").show();
                          Ext.getCmp("pdtBankCategory").getStore().load();
                        }
                      },
                    },
                  },
                  {
                    boxLabel: "Brand",
                    id: "importWise2",
                    name: "importWise",
                    inputValue: "Brand",
                    checked: true,
                    listeners: {
                      check: function (rgp, checked) {
                        if (checked == true) {
                          Ext.getCmp("pdtBrandCompany").show();
                          Ext.getCmp("pdtBrandCompany").getStore().load();
                        }
                      },
                    },
                  },
                ],
              },
              {
                xtype: "combo",
                store: gs1CategoryStore,
                mode: "local",
                id: "pdtBankCategory",
                allowBlank: true,
                fieldLabel: "Category",
                hiddenName: "pdtBankCategory",
                displayField: "categoryName",
                valueField: "id",
                typeAhead: true,
                forceSelection: true,
                editable: true,
                minChars: 2,
                hidden: true,
                anchor: "98%",
                triggerAction: "all",
                lazyRender: true,
                tabIndex: 102,
                listeners: {
                  select: function () {
                    var value = Ext.getCmp("pdtBankCategory").getValue();
                    //var value = Ext.getCmp("primary_businessType").getValue();
                    gs1SubCategoryStore.baseParams.category = this.value;
                    gs1SubCategoryStore.load();
                  },
                },
              },
              {
                xtype: "combo",
                store: gs1SubCategoryStore,
                mode: "local",
                hidden: true,
                id: "pdtBankSubCategory",
                allowBlank: true,
                fieldLabel: "Sub Category",
                hiddenName: "pdtBankSubCategory",
                displayField: "subCategoryName",
                valueField: "id",
                typeAhead: true,
                forceSelection: true,
                editable: true,
                minChars: 2,
                anchor: "98%",
                triggerAction: "all",
                lazyRender: true,
                tabIndex: 35,
                listeners: {
                  select: function () {},
                },
              },
              {
                xtype: "combo",
                store: gs1BrandCompanyStore,
                mode: "local",
                id: "pdtBrandCompany",
                allowBlank: true,
                fieldLabel: "Brand",
                hiddenName: "pdtBrandCompany",
                displayField: "brand",
                valueField: "prefix",
                typeAhead: true,
                forceSelection: true,
                editable: true,
                minChars: 2,
                anchor: "98%",
                triggerAction: "all",
                lazyRender: true,
                tabIndex: 36,
                listeners: {
                  select: function () {},
                },
              },
            ],
          }),
        ],
        bbar: [
          {
            hidden: true,
            xtype: "checkbox",
            id: "updateProducts",
            tabIndex: 103,
            align: "left",
            inputValue: 1,
            anchor: "99%",
            name: "updateProducts",
            labelAlign: "right",
            boxLabel: "Update Existing",
            listeners: {
              check: function (checkbox, checked) {
                if (checked == true) {
                  Application.GS1Products.Cache.updateProducts = 1;
                } else {
                  Application.GS1Products.Cache.updateProducts = 0;
                }
              },
            },
          },
          "->",
          {
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
            text: "Import",
            align: "right",
            handler: function () {
              var importType = Ext.getCmp("importType").getValue();
              var category = Ext.getCmp("pdtBankCategory").getValue();
              var subCategory = Ext.getCmp("pdtBankSubCategory").getValue();
              var brand = Ext.getCmp("pdtBrandCompany").getValue();
              var updateProducts;
              if (Ext.getCmp("updateProducts").getValue() == true) {
                updateProducts = 1;
              } else {
                updateProducts = 0;
              }
              Ext.Ajax.request({
                waitMsg: "Please wait...",
                url: modURL + "&op=importDataFromProductBank",
                method: "POST",
                params: {
                  importType: importType,
                  category: category,
                  brand: brand,
                  subCategory: subCategory,
                  updateProducts: updateProducts,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  console.log(tmp);
                  if (tmp.status === "success") {
                    Ext.Msg.alert("Notification.", tmp.message);
                  } else {
                    Ext.Msg.alert("Notification.", tmp.msg);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.Msg.alert(
                    "Notification",
                    "Data import is processing......"
                  );
                },
              });
              addNewReturnOrderWindow.close();
            },
          },
          {
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
            text: "Get Details",
            hidden: true,
            align: "right",
            handler: function () {
              var importType = Ext.getCmp("importType").getValue();
              var category = Ext.getCmp("pdtBankCategory").getValue();
              var subCategory = Ext.getCmp("pdtBankSubCategory").getValue();
              var brand = Ext.getCmp("pdtBankCompany").getValue();
              var updateProducts;
              if (Ext.getCmp("updateProducts").getValue() == true) {
                updateProducts = 1;
              } else {
                updateProducts = 0;
              }
              Ext.Ajax.request({
                waitMsg: "Please wait...",
                url: modURL + "&op=getDataFromProductBank",
                method: "POST",
                params: {
                  importType: importType,
                  category: category,
                  brand: brand,
                  subCategory: subCategory,
                  updateProducts: updateProducts,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  console.log(tmp);
                  if (tmp.status === "success") {
                    Ext.Msg.alert("Notification.", tmp.message);
                  } else {
                    Ext.Msg.alert("Notification.", tmp.message);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.Msg.alert("Error", "Data import is processing......");
                },
              });
            },
          },
        ],
        listeners: {
          load: function () {},
        },
      });

      addNewReturnOrderWindow.doLayout();
      addNewReturnOrderWindow.show();
      addNewReturnOrderWindow.center();
    },
    ViewMode: function () {
      var id = arguments[0];

      Ext.getCmp("xtemplateMasterImportedProductBankViewDetails").show();
      Ext.getCmp("panelImportedProductBank").doLayout();
      Ext.getCmp("panelImportedProductBank").setTitle("View Details");
      Ext.Ajax.request({
        url: modURL + "&op=prdctdetailsView",
        method: "POST",
        params: { id: id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "xtemplateMasterImportedProductBankViewDetails"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelImportedProductBank").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelImportedProductBank").doLayout();
    },
    getReconciledData: function (caregory, subCategory,manufactureName) {
      var reconciledDataWindowid = Ext.getCmp("reconciledDataWindow");
      if (Ext.isEmpty(reconciledDataWindowid)) {
        reconciledDataWindowid = new Ext.Window({
          id: "relatedBrandWindow",
          title: "Reconciled Products",
          modal: true,
          height: 600,
          width: winsize.width * 0.7,
          shadow: false,
          resizable: false,
          items: [reconciledDataGrid(caregory, subCategory,manufactureName)],
          buttons: [
            {
              text: "Close",
              iconCls: "my-icon61",
              handler: function () {
                reconciledDataWindowid.close();
              },
            },
          ],
          listeners: {
            close: function () {},
          },
        });
      }
      reconciledDataWindowid.doLayout();
      reconciledDataWindowid.show();
      reconciledDataWindowid.center();
    },
    assignBrands: function (userId) {
      var availableBrandWindowid = Ext.getCmp("availableBrandWindow");
      if (Ext.isEmpty(availableBrandWindowid)) {
        availableBrandWindowid = new Ext.Window({
          id: "availableBrandWindow",
          title: "Assign Brands to User",
          modal: true,
          height: 500,
          width: winsize.width * 0.8,
          shadow: false,
          resizable: false,
          layout: "border",
          items: [
            availableBrandGrid(userId),
            new Ext.Panel({
              title: "Assigned Brands",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              cls: "left_side_panel",
              height: 500,
              autoScroll: true,
              items: [mappedUserBrands(userId)],
              buttons: [],
              fbar: [],
            }),
          ],
          buttons: [
            {
              text: "Close",
              iconCls: "my-icon61",
              handler: function () {
                availableBrandWindowid.close();
              },
            },
          ],
          listeners: {
            close: function () {},
          },
        });
      }
      availableBrandWindowid.doLayout();
      availableBrandWindowid.show();
      availableBrandWindowid.center();
    },
    mapBrandToUser: function (brandarr, userId) {
      Ext.Ajax.request({
        url: modURL + "&op=mapBrandToUser",
        method: "post",
        params: {
          brandarr: Ext.encode(brandarr),
          userId: userId,
        },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {
            Ext.getCmp("gridpanelforAvailableBrands").getStore().load();
            Ext.getCmp(
              "gridpanelforUserMappedBrands"
            ).getStore().baseParams.userId = userId;
            Ext.getCmp("gridpanelforUserMappedBrands").getStore().load();
          }
        },
      });
    },
    ViewSkipMode: function () {
      var id = arguments[0];

      Ext.getCmp("xtemplateMasterSkippedProductBankViewDetails").show();
      Ext.getCmp("panelSkippedProductBank").doLayout();
      Ext.getCmp("panelSkippedProductBank").setTitle("View Details");
      Ext.Ajax.request({
        url: modURL + "&op=prdctdetailsView",
        method: "POST",
        params: { id: id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "xtemplateMasterSkippedProductBankViewDetails"
            );
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelSkippedProductBank").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelSkippedProductBank").doLayout();
    },
    mappedBrandData: function () {
      var _prdctBankBrandMappedPanelId = "panelMasterMainBrandMapped";
      var __prdctBankBrandMappedPanelIdPanel = Ext.getCmp(
        _prdctBankBrandMappedPanelId
      );
      if (Ext.isEmpty(__prdctBankBrandMappedPanelIdPanel)) {
        __prdctBankBrandMappedPanelIdPanel = mappedBrandMainPanel(
          _prdctBankBrandMappedPanelId
        );
        Application.UI.addTab(__prdctBankBrandMappedPanelIdPanel);
        __prdctBankBrandMappedPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__prdctBankBrandMappedPanelIdPanel);
      }
    },
    activateBrandData: function () {
      var _companyBrandImportPanelId = "panelMasterMainCompanyBrand";
      var __cmpnyBrndImportPanelIdPanel = Ext.getCmp(
        _companyBrandImportPanelId
      );
      if (Ext.isEmpty(__cmpnyBrndImportPanelIdPanel)) {
        __cmpnyBrndImportPanelIdPanel = importedBrandCompanyMainPanel(
          _companyBrandImportPanelId
        );
        Application.UI.addTab(__cmpnyBrndImportPanelIdPanel);
        __cmpnyBrndImportPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__cmpnyBrndImportPanelIdPanel);
      }
    },
    ViewModeBC: function () {
      var id = arguments[0];
      var prefix = arguments[1];

      Ext.getCmp("xtemplateMasterBrandCompanyViewDetails").show();
      Ext.getCmp("panelBrandCompany").doLayout();
      Ext.getCmp("panelBrandCompany").setTitle("View Details");
      Ext.Ajax.request({
        url: modURL + "&op=bcdetailsView",
        method: "POST",
        params: { id: id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "xtemplateMasterBrandCompanyViewDetails"
            );
            visualsDescPanel.update(tmp);
            Ext.getCmp("relatedBrandGridPanel").show();
            Ext.getCmp("relatedBrandGridPanel")
              .getStore()
              .load({
                params: {
                  prefix: prefix,
                  id: id,
                },
              });
          }
          Ext.getCmp("panelBrandCompany").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelBrandCompany").doLayout();
    },
  };
})();
