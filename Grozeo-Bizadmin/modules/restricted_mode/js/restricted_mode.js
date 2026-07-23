Application.RestrictedMode = (function () {
  var RECS_PER_PAGE = 23;
  var modURL = "?module=restricted_mode";
  var winsize = Ext.getBody().getViewSize();
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var stit_ID = new Array();
  var ckSelection = function () {
      return new Ext.grid.CheckboxSelectionModel({
          multiSelect: true,
          checkOnly: true,
          listeners: {
              rowdeselect: function (sm, rowIndex, record) {
                  var ind = stit_ID.indexOf(record.get('stit_ID'));
                  if (ind == -1) {
                    
                      record.set('checked', 'false');
                  }

              },
              rowselect: function (sm, rowIndex, record) {
                  var ind = stit_ID.indexOf(record.get('stit_ID'));
                  if (ind == -1) {
                  }
                 
                  record.set('checked', 'true');
              }, selectAll: function () {
                  var store = this.grid.getStore();
                  store.each(function (record) {
                      if (record.get('stit_ID') > 0) {
                      }
                  });
              }
          }
      });
  };
  var branddetStore = function () {
    var brandStore = new Ext.data.ArrayStore({
      url: modURL + "&op=brandName",
      method: "POST",
      autoLoad: true,
      fields: ["brand_id", "brand_name"],
    });
    return brandStore;
  };
  var ProductBrandListGrid = function () {
    var brandStore = branddetStore();
    var ck_selection = ckSelection();
    var branchD_filter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "fstr_ItemName",
        },
        {
          type: "string",
          dataIndex: "fstr_ApprovedItemQty",
        },
        {
          type: "string",
          dataIndex: "category_name",
        },
        {
          type: "string",
          dataIndex: "subcategoryname",
        },
        {
          type: "string",
          dataIndex: "status_name",
        },
        {
          type: "list",
          dataIndex: "status_name",
          options: ["Requested", "Ordered", "Deleted"],
          value: ["Requested"],
          phpMode: true,
        },
      ],
    });
    branchD_filter.remote = true;
    branchD_filter.autoReload = true;
    var _pdtDeletegridPanel = new Ext.grid.GridPanel({
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _StoreCashCollect(),
      autoScroll: true,
      width: winsize.width * 0.8,
      plugins: [branchD_filter],
      height: 400,
      bodyStyle: { "background-color": "white" },
      sm: ck_selection,
      id: "gridpanelProductDelete",
      tbar:[{ html: "&nbsp;Brand : &nbsp;" },
      {
        xtype: "combo",
        fieldLabel: "Brand",
        width: 100,
        id: "prddelbrand_id",
        hiddenName: "n[prddelbrand_id]",
        anchor: "98%",
        displayField: "brand_name",
        valueField: "brand_id",
        triggerAction: "all",
        forceSelection: false,
        selectOnFocus: false,
        mode: "local",
        typeAhead: true,
        lazyRender: false,
        editable: true,
        minChars: 2,
        tabIndex: 501,
        store: brandStore,
        listeners: {
          select: function (cmbo, record, index) {
            var prddelbrand_id = Ext.getCmp("prddelbrand_id").getValue();
            if (prddelbrand_id > 0) {
              Ext.getCmp("gridpanelProductDelete")
                .getStore()
                .load({
                  params: {
                    prddelbrand_id: prddelbrand_id,
                  },
                });
            }
          },
        },
      }],
      columns: [
        ck_selection,
        {
            header: "Brand",
            width: 250,
            dataIndex: "stit_brand_name",
            hideable: true,
            sortable: true,
          },
          {
            header: "SKU",
            width: 400,
            dataIndex: "stit_SKU",
            hideable: true,
            sortable: true,
          },          
          {
            header: "Department",
            width: 200,
            dataIndex: "parent_category",
            hideable: true,
            sortable: true,
          },
          {
            header: "Category",
            width: 200,
            dataIndex: "category_name",
            hideable: true,
            sortable: true,
          },
          {
            header: "Sub Category",
            width: 250,
            dataIndex: "stit_category_name",
            hideable: true,
            sortable: true,
          },
          {
            header: "Variant",
            dataIndex: "stit_product_variant",
            hideable: true,
            sortable: true,
          },
          {
            header: "Quantity",
            dataIndex: "stit_quantity",
            hideable: true,
            sortable: true,
          },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
            var prddelbrand_id = Ext.getCmp("prddelbrand_id").getValue();
            if (prddelbrand_id > 0) {
              Ext.getCmp("gridpanelProductDelete")
                .getStore()
                .load({
                  params: {
                    prddelbrand_id: prddelbrand_id,
                  },
                });
            }
        },
      },
    });
    return _pdtDeletegridPanel;
  };
  var _StoreCashCollect = function () {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listProductsToDelete",
        method: "post",
      }),
      fields: [
        "stit_ID",
        "stit_SKU",
        "stit_itemName",
        "product_category",
        "stit_category_name",
        "stit_brand_name",
        "med_manufacturename",
        "category_name",
        "parent_category",
        "itemMrp",
        "stit_product_variant",
        "stit_quantity",
      ],
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
  return {
    Cache: {},
    productHardDelete: function () {
      var productHardDeleteWindow = new Ext.Window({
        layout: "fit",
        height: 400,
        width: 900,
        resizable: false,
        draggable: true,
        closable: true,
        id: "productHardDelete_win",
        title: "Products",
        iconCls: "server_cache",
        plain: true,
        constrain: true,
        modal: true,
        border: false,
        items: [ProductBrandListGrid()],
        buttonAlign: "left",
        fbar: [
          "->",
          { html: "Date" },
          {
            fieldLabel: "Date",
            labelAlign: "left",
            xtype: "datefield",
            format: "Y-m-d H:i:s",
            id: "cashCollectedDate",
            name: "cashCollectedDate",
            anchor: "98%",
            tabIndex: 40,
            allowBlank: false,
            value: new Date().format("Y-m-d H:i:s"),
          },
          {
            text: "Delete Products",
            cls: "left-right-buttons",
            tooltip: "Delete Products",
            icon: IMAGE_BASE_PATH + "/default/icons/delete.png",
            handler: function () {
              var store_fields = Ext.getCmp("gridpanelProductDelete")
                .getSelectionModel()
                .getSelections();
              if (store_fields.length > 0) {
                var selquorIds = new Array();
                for (var i = 0; i < store_fields.length; i++) {
                  selquorIds[i] = store_fields[i].data.stit_ID;
                }
                Ext.Ajax.request({
                  url: modURL + "&op=deleteProduct",
                  method: "POST",
                  params: {
                    quorIds: Ext.encode(selquorIds),
                    cashCollectedDate:
                      Ext.getCmp("cashCollectedDate").getValue(),
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    Application.example.msg("Success", tmp.msg);
                    _addnewtransferWindow.close();
                  },
                  failure: function (response, options) {
                    Ext.MessageBox.alert("Notification", ACTION_FAIL);
                  },
                });
              } else {
                Ext.MessageBox.alert("Notification", "Please select Items");
              }
            },
          },
        ],
      });

      productHardDeleteWindow.show();
    },
  };
})();
