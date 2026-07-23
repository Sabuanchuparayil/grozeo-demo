Application.ProductReport = (function () {
  var recs_per_page = 25;
  var PdtEnrtyLastParameters;
  var modURL = "?module=product_report";
  var current_type;
  var winLoadMask;
  var winsize = Ext.getBody().getViewSize();
  var loadCount;
  var getProductEntryReportGrid = function () {
    var getPrdctEntryReportStore = function () {
      var PrdctEntryReportStore = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=getPrdctEntryReportGridData",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "stit_ID",
            root: "data",
          },
          [
            "stit_ID","stit_category_name",
            "createdOn",
            "createdBy",
            "verifedBy",
            "updatedOn",
            "updatedBy",
            "stit_SKU",
            "isVerified",
            "verifedOn",
          ]
        ),
        sortInfo: {
          field: "stit_ID",
          direction: "DESC",
        },
        groupField: "",
        groupDir: "ASC",
        remoteSort: true,
        autoLoad: false,
        root: "data",
        listeners: {
          load: function (a, b, options) {
            //PdtEnrtyLastParameters = options.params;
            this.baseParams.search_prdent_from_date = Ext.getCmp(
              "search_prdent_from_date"
            ).getValue();
            this.baseParams.search_prdent_to_date = Ext.getCmp(
              "search_prdent_to_date"
            ).getValue();
            this.baseParams.actionType = Ext.getCmp("actionType").getValue();
          },
        },
      });

      return PrdctEntryReportStore;
    };
    var gridFilters = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },{
          type: "string",
          dataIndex: "createdBy",
        },{
          type: "string",
          dataIndex: "updatedBy",
        },{
          type: "string",
          dataIndex: "verifedBy",
        }
      ],
    });
    gridFilters.remote = true;
    gridFilters.autoReload = true;
    var B2CSalesReport_store = getPrdctEntryReportStore();
    var grid = new Ext.grid.GridPanel({
      store: B2CSalesReport_store,
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      plugins: [gridFilters],
      id: "productenrty_report_grid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          sortable: true,
          dataIndex: "stit_SKU",
          tooltip: "SKU",
          width: 200,
        },{
          header: "Sub Category",
          sortable: true,
          dataIndex: "stit_category_name",
          tooltip: "Sub Category"
        },
        {
          header: "Created Date",
          sortable: true,
          dataIndex: "createdOn",
          tooltip: "Date",
        },
        {
          header: "Created By",
          sortable: true,
          dataIndex: "createdBy",
          tooltip: "Created By",
        },
        {
          header: "Updated Date",
          sortable: true,
          dataIndex: "updatedOn",
          tooltip: "Updated Date",
        },
        {
          header: "Updated By",
          sortable: true,
          dataIndex: "updatedBy",
          tooltip: "Updated By",
        },
        {
          header: "Verified Date",
          sortable: true,
          dataIndex: "verifedOn",
          tooltip: "Verified Date",
        },
        {
          header: "Verified By",
          sortable: true,
          dataIndex: "verifedBy",
          tooltip: "Verified By",
        },
      ],
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        getRowClass: function (record, index, params, store) {
         
        },
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      tbar: [
        { html: "&nbsp; Type : &nbsp;" },
        {
          xtype: "combo",
          fieldLabel: "Type",
          emptyText: "Choose Type",
          id: "actionType",
          name: "actionType",
          mode: "local",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          anchor: "95%",
          store: new Ext.data.JsonStore({
            fields: ["id", "name"],
            data: [
              { id: "Add", name: "Created" },
              { id: "Edit", name: "Edited" },
              { id: "Verify", name: "Verified" },
            ],
          }),
          triggerAction: "all",
          minChars: 2,
          displayField: "name",
          valueField: "id",
          hiddenName: "actionType",
          tabIndex: 509,
        },
        { html: "&nbsp; From Date : &nbsp;" },
        {
          xtype: "datefield",
          width: 120,
          id: "search_prdent_from_date",
          name: "search_prdent_from_date",
          format: "d/m/Y",
          value: new Date(),
        },
        { html: "&nbsp; To Date : &nbsp;" },
        {
          xtype: "datefield",
          width: 120,
          id: "search_prdent_to_date",
          name: "search_prdent_to_date",
          format: "d/m/Y",
          value: new Date(),
        },
        "-",
        {
          xtype: "button",
          text: "Search",
          iconCls: "finascop_search_btn",
          handler: function () {
            var actionType = Ext.getCmp("actionType").getValue();
            var search_prdent_from_date = Ext.getCmp(
              "search_prdent_from_date"
            ).getValue();
            var search_prdent_to_date = Ext.getCmp(
              "search_prdent_to_date"
            ).getValue();
            Ext.getCmp("productenrty_report_grid").filters.clearFilters();
            Ext.getCmp("productenrty_report_grid")
              .getStore()
              .load({
                params: {
                  actionType: actionType,
                  search_prdent_from_date: search_prdent_from_date,
                  search_prdent_to_date: search_prdent_to_date,
                },
              });
          },
        },
        "-",
        {
          xtype: "button",
          text: "Export to Excel",
          iconCls: "icon_excel",
          handler: function () {
            if (B2CSalesReport_store.getTotalCount() <= 0) {
              Ext.Msg.confirm(
                "Notification",
                "Grid does not contain any data to export!<br> Do you still wants to export?",
                function (btn) {
                  if (btn == "no") {
                    return;
                  }
                }
              );
            }

            var indexes = [];
            var heads = [];
            /**
             * @modified by Aparna Ravvendran <aparna@saturn.in>
             * @modified On 27-Mar-2013
             *
             * pass total no of headers of selected grid for export excel
             */
            var filterData = Ext.encode(PdtEnrtyLastParameters);
            for (
              var i = 0;
              i <
              Ext.getCmp("productenrty_report_grid")
                .getColumnModel()
                .getColumnCount();
              i++
            ) {
              if (
                Ext.getCmp("productenrty_report_grid")
                  .getColumnModel()
                  .isHidden(i) !== true
              ) {
                indexes[indexes.length] = Ext.getCmp(
                  "productenrty_report_grid"
                ).getColumnModel().config[i].dataIndex;
                heads[heads.length] = Ext.getCmp(
                  "productenrty_report_grid"
                ).getColumnModel().config[i].header;
              }
              var dataindexes = Ext.encode(indexes);
              var headers = Ext.encode(heads);
            }

            postToUrl(
              modURL + "&op=prdctEntryReportsexportexcel",
              {
                dataindexes: dataindexes,
                headers: headers,
                filterData: filterData,
              },
              "post",
              "downloadIframe"
            );
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: B2CSalesReport_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      stripeRows: true,
    });
    return grid;
  };
  var getProductEntryReportPanel = function (panelId) {
    var ProductEntryReportGrid = getProductEntryReportGrid();
    var panel = new Ext.Panel({
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      layout: "fit",
      border: false,
      id: panelId,
      title: "Product Entry Report",
      items: [ProductEntryReportGrid],
    });
    return panel;
  };
  var getProductReportPanel = function (panelId) {
    var ProductReportGrid = getProductReportGrid();
    var panel = new Ext.Panel({
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      layout: "fit",
      border: false,
      id: panelId,
      title: "Product Report",
      items: [ProductReportGrid],
    });
    return panel;
  };
  var getProductReportGrid = function(){

    var getPrdctReportStore = function () {
      var store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listItemMasterData",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "stit_ID",
            root: "data",
          },
          [
            "ItemId","department","mainCategory",
            "stit_itemName",
            "hsn_code",
            "product_is_home",
            "stit_category_name",
            "stit_brand_name",
            "imgCount",
            "stit_displaylabel",
            "stit_product_variant",
            "stit_quantity",
            "least_package_type_name",
            "createdBy",
            "createdOn",
            { name: "tax", type: "number" },
            "groups",
            "description",
            "stit_itemERPId",
            "stit_HSN_code",
            "hasMrp",
            "stit_status",
            "statusName",
            "stit_SKU",
            "isVerified",
            "isFeatured",
            "isPopular",
          ]
        ),
        sortInfo: {
          field: "ItemId",
          direction: "DESC",
        },
        groupField: "",
        groupDir: "ASC",
        remoteSort: true,
        autoLoad: true,
        root: "data",
        listeners: {
          load: function (a, b, options) {
            
          },
        },
      });
      
      return store;
    };
    var gridFilters = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
        {
          type: "string",
          dataIndex: "stit_itemName",
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
          dataIndex: "stit_product_variant",
        },
        {
          type: "string",
          dataIndex: "stit_quantity",
        },
        {
          type: "string",
          dataIndex: "isFeatured",
        },
        {
          type: "string",
          dataIndex: "isPopular",
        },
        {
          type: "numeric",
          dataIndex: "tax",
        },
        {
          type: "string",
          dataIndex: "stit_HSN_code",
        },
        {
          type: "string",
          dataIndex: "least_package_type_name",
        },
        {
          type: "string",
          dataIndex: "createdBy",
        },
        {
          type: "string",
          dataIndex: "hasMrp",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "statusName",
        },
        {
          type: "string",
          dataIndex: "stit_displaylabel",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isVerified",
        },
        {
          type: "date",
          dataIndex: "createdOn",
        },
      ],
    });
    gridFilters.remote = true;
    gridFilters.autoReload = true;
    var B2CSalesReport_store = getPrdctReportStore();
    var grid = new Ext.grid.GridPanel({
      store: B2CSalesReport_store,
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      plugins: [gridFilters],
      id: "product_report_grid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          sortable: true,
          hideable: true,
          dataIndex: "stit_SKU",
          listeners: {
            click: function (value) {
              var stit_SKU = Ext.getCmp("itemmaster_grid")
                .getSelectionModel()
                .getSelections()[0].data.stit_SKU;
              window.open("http://google.com/search?q=" + stit_SKU);
            },
          },
        },
        /*{
          header: "Department",
          sortable: true,
          hideable: true,
          hidden: true,
          dataIndex: "department",
        },
        {
          header: "Category",
          sortable: true,
          hideable: true,
          hidden: true,
          dataIndex: "mainCategory",
        },*/
        {
          header: "Sub Category",
          sortable: true,
          hideable: true,
          dataIndex: "stit_category_name",
        },
        {
          header: "Brand",
          sortable: true,
          hideable: true,
          dataIndex: "stit_brand_name",
        },
        {
          header: "Product Master",
          sortable: true,
          hideable: true,
          dataIndex: "stit_itemName",
        },
        {
          header: "Variant",
          sortable: true,
          hideable: true,
          dataIndex: "stit_product_variant",
        },
        {
          header: "Net Weight",
          sortable: true,
          hideable: true,
          dataIndex: "stit_quantity",
        },
        {
          header: "HSN Code",
          sortable: true,
          hideable: true,
          align: "right",
          dataIndex: "stit_HSN_code",
        },
        {
          header: "Tax",
          id: "tax",
          xtype: "numbercolumn",
          sortable: true,
          hideable: true,
          dataIndex: "tax",
          align: "right",
          width: 50,
        },
        {
          header: "Least Package",
          sortable: true,
          hideable: true,
          align: "right",
          dataIndex: "least_package_type_name",
        },
        {
          header: "Status",
          hideable: true,
          sortable: true,
          dataIndex: "statusName",
          align: "right",
          width: 50,
        },
        {
          header: "Image Count",
          hideable: true,
          sortable: true,
          dataIndex: "imgCount",
          align: "right",
          width: 50,
        },
        {
          header: "Image",
          hideable: true,
          sortable: true,
          dataIndex: "stit_displaylabel",
          width: 50,
        },
        {
          header: "Has MRP",
          hideable: true,
          sortable: true,
          dataIndex: "hasMrp",
          width: 50,
          tooltip: "Has MRP",
        },
        {
          header: "Is Verified",
          hideable: true,
          dataIndex: "isVerified",
          width: 50,
          tooltip: "Is Verified",
        },
        {
          header: "Featured",
          hideable: true,
          sortable: true,
          hidden: true,
          dataIndex: "isFeatured",
          width: 50,
          tooltip: "Featured",
        },
        {
          header: "Popular",
          hideable: true,
          sortable: true,
          hidden: true,
          dataIndex: "isPopular",
          width: 50,
          tooltip: "Popular",
        },
        {
          header: "Created By",
          hideable: true,
          sortable: true,
          dataIndex: "createdBy",
          width: 50,
          tooltip: "Created By",
        },
        {
          header: "Created On",
          hideable: true,
          sortable: true,
          dataIndex: "createdOn",
          width: 50,
          tooltip: "Created On",
        },
      ],
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        getRowClass: function (record, index, params, store) {
         
        },
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      tbar: [
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: B2CSalesReport_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      stripeRows: true,
    });
    return grid;
  };
  var getProductExportLogReportPanel = function(panelId){
    var ProductExportLogGrid = getProductExportLogReportGrid();
    var panel = new Ext.Panel({
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      layout: "fit",
      border: false,
      id: panelId,
      title: "Product Export Log Report",
      items: [ProductExportLogGrid],
    });
    return panel;
  };
  var getProductExportLogReportGrid = function(){

    var getPrdctExportLogReportStore = function () {
      var PrdctEntryReportStore = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=getPrdctExportLogReportGridData",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "stit_ID",
            root: "data",
          },
          [
            "grozeo_stitId","stit_category_name","id","stit_SKU",
            "enteredOn","product_stitId",
            "enteredBy"
          ]
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
          load: function (a, b, options) {
            //PdtEnrtyLastParameters = options.params;
            this.baseParams.search_prdexp_from_date = Ext.getCmp(
              "search_prdexp_from_date"
            ).getValue();
            this.baseParams.search_prdexp_to_date = Ext.getCmp(
              "search_prdexp_to_date"
            ).getValue();
            
          },
        },
      });

      return PrdctEntryReportStore;
    };
    var gridFilters = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },{
          type: "string",
          dataIndex: "enteredBy",
        }
      ],
    });
    gridFilters.remote = true;
    gridFilters.autoReload = true;
    var ProductExportLogReport_store = getPrdctExportLogReportStore();
    var grid = new Ext.grid.GridPanel({
      store: ProductExportLogReport_store,
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      plugins: [gridFilters],
      id: "productexport_report_grid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          sortable: true,
          dataIndex: "stit_SKU",
          tooltip: "SKU",
          width: 200,
        },{
          header: "Sub Category",
          sortable: true,
          dataIndex: "stit_category_name",
          tooltip: "Sub Category"
        },
        {
          header: "Date",
          sortable: true,
          dataIndex: "enteredOn",
          tooltip: "Date",
        },
        {
          header: "By",
          sortable: true,
          dataIndex: "enteredBy",
          tooltip: "By",
        },        
      ],
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        getRowClass: function (record, index, params, store) {
         
        },
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      tbar: [
        { html: "&nbsp; From Date : &nbsp;" },
        {
          xtype: "datefield",
          width: 120,
          id: "search_prdexp_from_date",
          name: "search_prdexp_from_date",
          format: "d/m/Y",
          value: new Date(),
        },
        { html: "&nbsp; To Date : &nbsp;" },
        {
          xtype: "datefield",
          width: 120,
          id: "search_prdexp_to_date",
          name: "search_prdexp_to_date",
          format: "d/m/Y",
          value: new Date(),
        },
        "-",
        {
          xtype: "button",
          text: "Search",
          iconCls: "finascop_search_btn",
          handler: function () {
            var search_prdexp_from_date = Ext.getCmp(
              "search_prdexp_from_date"
            ).getValue();
            var search_prdexp_to_date = Ext.getCmp(
              "search_prdexp_to_date"
            ).getValue();
            Ext.getCmp("productexport_report_grid").filters.clearFilters();
            Ext.getCmp("productexport_report_grid")
              .getStore()
              .load({
                params: {
                  search_prdexp_from_date: search_prdexp_from_date,
                  search_prdexp_to_date: search_prdexp_to_date,
                },
              });
          },
        },
        "-",
        {
          xtype: "button",
          text: "Export to Excel",
          iconCls: "icon_excel",
          handler: function () {
            if (ProductExportLogReport_store.getTotalCount() <= 0) {
              Ext.Msg.confirm(
                "Notification",
                "Grid does not contain any data to export!<br> Do you still wants to export?",
                function (btn) {
                  if (btn == "no") {
                    return;
                  }
                }
              );
            }

            var indexes = [];
            var heads = [];
            /**
             * @modified by Aparna Ravvendran <aparna@saturn.in>
             * @modified On 27-Mar-2013
             *
             * pass total no of headers of selected grid for export excel
             */
            var filterData = Ext.encode();
            for (
              var i = 0;
              i <
              Ext.getCmp("productexport_report_grid")
                .getColumnModel()
                .getColumnCount();
              i++
            ) {
              if (
                Ext.getCmp("productexport_report_grid")
                  .getColumnModel()
                  .isHidden(i) !== true
              ) {
                indexes[indexes.length] = Ext.getCmp(
                  "productexport_report_grid"
                ).getColumnModel().config[i].dataIndex;
                heads[heads.length] = Ext.getCmp(
                  "productexport_report_grid"
                ).getColumnModel().config[i].header;
              }
              var dataindexes = Ext.encode(indexes);
              var headers = Ext.encode(heads);
            }

            postToUrl(
              modURL + "&op=prdctExportLogReportsexportexcel",
              {
                dataindexes: dataindexes,
                headers: headers,
                filterData: filterData,
              },
              "post",
              "downloadIframe"
            );
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: ProductExportLogReport_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      stripeRows: true,
    });
    return grid;
  };
  var getProductImageReportPanel = function (panelId) {
    var ProductImageReportGrid = getProductImageReportGrid();
    var panel = new Ext.Panel({
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      layout: "fit",
      border: false,
      id: panelId,
      title: "Product Image Report",
      items: [ProductImageReportGrid],
    });
    return panel;
  };
  var getProductImageReportGrid = function(){

    var getPrdctImageReportStore = function () {
      var store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=getImageLogReport",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "id",
            root: "data",
          },
          ["stit_SKU","stit_category_name","createdBy","stit_brand_name","image_type","enteredBy","id","stit_itemName","created_at"
          ]
        ),
        sortInfo: {
          field: "id",
          direction: "DESC",
        },
        groupField: "",
        groupDir: "ASC",
        remoteSort: true,
        autoLoad: true,
        root: "data",
        listeners: {
          load: function (a, b, options) {
            
          },
        },
      });
      
      return store;
    };
    var gridFilters = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
        {
          type: "string",
          dataIndex: "stit_itemName",
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
          type: "date",
          dataIndex: "createdOn",
        },
      ],
    });
    gridFilters.remote = true;
    gridFilters.autoReload = true;
    var B2CSalesReport_store = getPrdctImageReportStore();
    var grid = new Ext.grid.GridPanel({
      store: B2CSalesReport_store,
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      plugins: [gridFilters],
      id: "product_image_grid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          sortable: true,
          hideable: true,
          dataIndex: "stit_SKU",
          
        },
        {
          header: "Sub Category",
          sortable: true,
          hideable: true,
          dataIndex: "stit_category_name",
        },
        {
          header: "Brand",
          sortable: true,
          hideable: true,
          dataIndex: "stit_brand_name",
        },
        {
          header: "Product Master",
          sortable: true,
          hideable: true,
          dataIndex: "stit_itemName",
        },{
          header: "Image Type",
          sortable: true,
          hideable: true,
          dataIndex: "image_type",
        },
        {
          header: "Created On",
          sortable: true,
          hideable: true,
          dataIndex: "created_at",
        },{
          header: "Created by",
          sortable: true,
          hideable: true,
          dataIndex: "enteredBy",
        },
        
      ],
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        getRowClass: function (record, index, params, store) {
         
        },
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      tbar: [
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: B2CSalesReport_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      stripeRows: true,
    });
    return grid;
  };
  return {
    Cache: {},
    ProductEntryReport: function () {
      var panelId = "ProductEntryReportPanelID";
      var ProductEntryReportPanel = Ext.getCmp(panelId);
      if (Ext.isEmpty(ProductEntryReportPanel)) {
        ProductEntryReportPanel = getProductEntryReportPanel(panelId);
        Application.UI.addTab(ProductEntryReportPanel);
        ProductEntryReportPanel.doLayout();
      } else {
        Application.UI.addTab(ProductEntryReportPanel);
      }
    },
    ProductReport: function () {
      var panelId = "ProductReportPanelID";
      var ProductReportPanel = Ext.getCmp(panelId);
      if (Ext.isEmpty(ProductReportPanel)) {
        ProductReportPanel = getProductReportPanel(panelId);
        Application.UI.addTab(ProductReportPanel);
        ProductReportPanel.doLayout();
      } else {
        Application.UI.addTab(ProductReportPanel);
      }
    },ProductExportLogReport: function () {
      var panelId = "ProductExportLogReportPanelID";
      var ProductExportLogReportPanel = Ext.getCmp(panelId);
      if (Ext.isEmpty(ProductExportLogReportPanel)) {
        ProductExportLogReportPanel = getProductExportLogReportPanel(panelId);
        Application.UI.addTab(ProductExportLogReportPanel);
        ProductExportLogReportPanel.doLayout();
      } else {
        Application.UI.addTab(ProductExportLogReportPanel);
      }
    },ProductImageReport: function () {
      var panelId = "ProductImageReportPanelID";
      var ProductImageReportPanel = Ext.getCmp(panelId);
      if (Ext.isEmpty(ProductImageReportPanel)) {
        ProductImageReportPanel = getProductImageReportPanel(panelId);
        Application.UI.addTab(ProductImageReportPanel);
        ProductImageReportPanel.doLayout();
      } else {
        Application.UI.addTab(ProductImageReportPanel);
      }
    }
  };
})();
