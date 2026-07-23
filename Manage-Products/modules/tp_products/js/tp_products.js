Application.ThirdPartyProducts = (function () {
  var recs_per_page = 21;
  var calculated_recs_per_page = false;
  var modURL = "?module=tp_products";
  var promodURL = "?module=mypha_product";
  var upmodURL = "?module=product_bank_upload";
  var current_type;
  var winLoadMask;
  var winsize = Ext.getBody().getViewSize();
  var loadCount;

  var gsgridSelectionChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelGSCompare").getSelectionModel().getSelections()
      )
    ) {
      var pdId = Ext.getCmp("gridpanelGSCompare")
        .getSelectionModel()
        .getSelections()[0].data.productId;
      Ext.Ajax.request({
        url: modURL + "&op=gsPrdtsDetailsView",
        method: "POST",
        params: { stit_ID: pdId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("panelGSProductViewDetails");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelGSProductViewDetails").doLayout();

          var productId = Ext.getCmp("gridpanelGSCompare")
              .getSelectionModel()
              .getSelections()[0].data.productId;
            if (productId > 0) {
              Ext.getCmp("combineProductId").show();
            } else {
              Ext.getCmp("combineProductId").hide();
              
            }
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
    }
  };
  var tpgridSelectionChanged = function () {
    if (
      !Ext.isEmpty(
        Ext.getCmp("tp_mapping_grid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("tp_mapping_grid")
        .getSelectionModel()
        .getSelections()[0].data.stit_ID;
      var stit_SKU = Ext.getCmp("tp_mapping_grid")
        .getSelectionModel()
        .getSelections()[0].data.stit_SKU;
      var brand = Ext.getCmp("tp_mapping_grid")
        .getSelectionModel()
        .getSelections()[0].data.stit_brand_name;
      var stit_itemName = Ext.getCmp("tp_mapping_grid")
        .getSelectionModel()
        .getSelections()[0].data.stit_itemName;

      Ext.getCmp("search_gsbrand").reset();
      Ext.Ajax.request({
        url: modURL + "&op=tpPrdtsDetailsView",
        method: "POST",
        params: { stit_ID: ID },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("panelTPProductViewDetails");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("panelTPProductViewDetails").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("gridpanelGSCompare")
        .getStore()
        .load({
          params: {
            stit_SKU: stit_SKU,
            brand: brand,
            itemname: stit_itemName,
          },
          callback: function () {
            Ext.getCmp("skipProductId").show();
              /*if (stit_itemName == "Private Product") {
                Ext.getCmp("createProductId").show();
              } else {
                Ext.getCmp("createProductId").hide();
              }*/
              

            
          },
        });
    } else {
      Ext.getCmp("combineProductId").hide();
      Ext.getCmp("createProductId").show();
      Ext.getCmp("skipProductId").hide();
    }
  };
  var gridSelectionChangedtpPrdcts = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelThirdPartyProducts")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelThirdPartyProducts")
        .getSelectionModel()
        .getSelections()[0].data.stit_ID;
      var tpStatus = Ext.getCmp("gridpanelThirdPartyProducts")
        .getSelectionModel()
        .getSelections()[0].data.tpStatus;
      if (tpStatus == 2) {
        Ext.getCmp("buttonMerchantPrdct").show();
        Ext.getCmp("buttonPublicPrdct").show();
      } else {
        Ext.getCmp("buttonMerchantPrdct").hide();
        Ext.getCmp("buttonPublicPrdct").hide();
      }
      Application.ThirdPartyProducts.Cache.stit_ID = ID;
      Application.ThirdPartyProducts.ViewMode(ID);
    } else {
      Application.ThirdPartyProducts.Cache.stit_ID = 0;
      Application.ThirdPartyProducts.ViewMode(ID);
    }
  };

  var ListmainTpPdtctsPanel = function (id) {
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      id: id,
      title: "Private Products",
      items: [mainGrid(), tpTabPanel()],
    });
    return panel;
  };
  var tpTabPanel = function () {
    var panel = new Ext.Panel({
      region: "east",
      width: winsize.width * 0.5,
      height: winsize.height * 0.6,
      autoScroll: true,
      cls: "left_side_panel",
      plain: true,
      frame: false,
      border: false,
      id: "tabpanelTPDetails",
      items: [
        {
          title: "Details",
          layout: "fit",
          id: "tpPrdtsDeatailsView",
          width: winsize.width * 0.48,
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="99%" class="details_view_table">',
            "<tr><th>SKU :</th><td> {stit_SKU} </td></tr>",
            "<tr><th>Brand:</th><td>  {stit_brand_name}</td></tr>",
            "<tr><th>Product Master:</th><td>  {stit_itemName}</td></tr>",
            "<tr><th>Variant:</th><td>  {stit_product_variant}</td></tr>",
            "<tr><th>Net Wt.:</th><td>  {stit_quantity}</td></tr>",
            "<tr><th>Sub Category:</th><td>  {stit_category_name}</td></tr>",
            "<tr><th>HSN Code:</th><td>  {stit_HSN_code}</td></tr>",
            "<tr><th>GST:</th><td>  {stit_GST}</td></tr>",
            "<tr><th>Return Time:</th><td>  {stit_itemReturnTime}</td></tr>",
            "<tr><th>Country of Orgin:</th><td>  {stit_orgin_countryname}</td></tr>",
            "<tr><th>Least Package Type:</th><td>  {least_package_type_name}</td></tr>",
            "<tr><th>Short Description:</th><td>  {stit_Description}</td></tr>",
            "<tr><th>Long Description:</th><td>  {stit_long_description}</td></tr>",
            '<tpl if="image_urlend != null">',
            "<tpl if=\"image_urlend != ''\">",
            "<tr><td>",
            '<tr><th>Deafult Image:</th><td><div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div></td></tr>',
            "</td></tr>",
            "</tpl>",
            "</tpl>",
            "</table>",
            "</div>"
          ),
        },
      ],
      buttons: [
        {
          text: "Public Product",
          tabIndex: 139,
          id: "buttonPublicPrdct",
          buttonAlign: "left",
          handler: function () {
            if (
              !Ext.isEmpty(
                Ext.getCmp("gridpanelThirdPartyProducts")
                  .getSelectionModel()
                  .getSelections()
              )
            ) {
              var stit_ID = Ext.getCmp("gridpanelThirdPartyProducts")
                .getSelectionModel()
                .getSelections()[0].data.stit_ID;
              Ext.Ajax.request({
                url: modURL + "&op=convertTPProducts",
                method: "POST",
                params: {
                  stit_ID: stit_ID,
                  status: 1,
                },
                success: function (res) {
                  var tmp = Ext.decode(res.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp("gridpanelThirdPartyProducts").getStore().load();
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
          },
        },
        {
          text: "Merchant Product",
          tabIndex: 139,
          id: "buttonMerchantPrdct",
          buttonAlign: "left",
          handler: function () {
            if (
              !Ext.isEmpty(
                Ext.getCmp("gridpanelThirdPartyProducts")
                  .getSelectionModel()
                  .getSelections()
              )
            ) {
              var stit_ID = Ext.getCmp("gridpanelThirdPartyProducts")
                .getSelectionModel()
                .getSelections()[0].data.stit_ID;
              Ext.Ajax.request({
                url: modURL + "&op=convertTPProducts",
                method: "POST",
                params: {
                  stit_ID: stit_ID,
                  status: 2,
                },
                success: function (res) {
                  var tmp = Ext.decode(res.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp("gridpanelThirdPartyProducts").getStore().load();
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
          },
        },
      ],
    });
    return panel;
  };
  var gs1BrandComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getgs1Brands",
      method: "post",
      autoLoad: true,
      fields: ["id", "brandName"],
      root: "data",
    });
    return store;
  };
  var itemmasterStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listItemMasterDataTP",
        method: "post",
      }),
      fields: [
        "stit_SKU",
        "stit_itemName",
        "stit_HSN_code",
        "stit_category_name",
        "stit_brand_name",
        "stit_quantity",
        "stit_product_variant",
        "least_package_type_name",
        "stit_ID",
        "tpStatus",
        "tpStatusName",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        load: function () {
          this.baseParams.merchantId = Ext.getCmp("merchantId").getValue();
          Ext.getCmp('gridpanelThirdPartyProducts').getSelectionModel().selectRow(0);
        },
      },
    });
    store.setDefaultSort("stit_ID", "ASC");
    return store;
  };
  var mainGrid = function () {
    var grid_store = itemmasterStore();
    var customerGrid_filter = new Ext.ux.grid.GridFilters({
      remote: true,
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
          dataIndex: "tpStatusName",
        },
      ],
    });
    customerGrid_filter.remote = true;
    customerGrid_filter.autoReload = true;

    var SP_grid = new Ext.grid.GridPanel({
      store: grid_store,
      id: "gridpanelThirdPartyProducts",
      region: "center",
      frame: true,
      border: false,
      layout: "fit",
      loadMask: true,
      plugins: [customerGrid_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          sortable: true,
          hideable: true,
          dataIndex: "stit_SKU",
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
          header: "Net Weight",
          sortable: true,
          hideable: true,
          dataIndex: "stit_quantity",
        },
        {
          header: "Status",
          sortable: true,
          hideable: true,
          dataIndex: "tpStatusName",
        },
        {
          dataIndex: " ",
          width: 20,
        },
      ],
      viewConfig: {
        forceFit: true,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      tbar: [{ html: " StoreGroup: " },{
              fieldLabel: "Store Group",
              xtype: "combo",
              displayField: "name",
              valueField: "id",
              mode: "remote",
              id: "merchantId",
              name: "merchantId",
              emptyText: "Select Store Group",
              anchor: "95%",
              allowBlank: false,
              typeAhead: true,
              triggerAction: "all",
              lazyRender: true,
              store: merchantStore(),
              editable: true,
              tabIndex: 507,
              minChars: 2,
              listeners: {
                select: function (combo, record, index) {
                },
              },
            },{
          frame: false,
          border: false,
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/search.png",
          xtype: "button",
          text: "Search",
          tabIndex: 37,
          handler: function () {
            var merchantId = Ext.getCmp("merchantId").getValue();
            Ext.getCmp('gridpanelThirdPartyProducts').getStore().load({
              params:{
                merchantId:merchantId
              }
            });
          },
        }],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: grid_store,
        displayInfo: true,
        plugins: [customerGrid_filter],
        displayMsg: "Displaying items {0} - {1} of {2}",
        emptyMsg: "No records to display",
      }),
      stripeRows: true,
      sm: new Ext.grid.RowSelectionModel({
        singleSelected: true,
        listeners: {
          selectionchange: gridSelectionChangedtpPrdcts,
        },
      }),
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return SP_grid;
  };
var merchantStore = function () {
    var merchant_store = new Ext.data.JsonStore({
      url: upmodURL + "&op=getMerchantStore",
      fields: ["id", "name"],
      totalProperty: "totalCount",
      root: "data",
      autoload: true,
      remoteFilter: true,
      listeners: {},
    });
    return merchant_store;
  };
  var gsMatchedGridStore = function () {

    var store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listMatchedGs1Items",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          //idProperty: "stit_ID",
          root: "data",
        },
        ["id",
        "isExisting",
        "brand",
        "name","med_manufacturename","stit_SKU","stit_brand_name",
        "gtin",
        "category",
        "sub_category",
        "unique_key",
        "productId",]
      ),
      sortInfo: {
        field: "stit_ID",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "DESC",
      remoteSort: true,
      autoLoad: true,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelGSCompare").getSelectionModel().selectRow(0);
        },
        beforeload: function () {},
      },
    });

    return store;
    /*var store = new Ext.data.JsonStore({
      method: "post",
      url: modURL + "&op=listMatchedGs1Items",
      fields: [
        "id",
        "isExisting",
        "brand",
        "name","med_manufacturename","stit_SKU","stit_brand_name",
        "gtin",
        "category",
        "sub_category",
        "unique_key",
        "productId",
      ],
      remoteSort: true,
      root: "data",
      totalProperty: "totalCount",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelGSCompare").getSelectionModel().selectRow(0);
        },
        beforeload: function () {},
      },
    });
    return store;*/
  };

  var rowno = new Ext.grid.RowNumberer();
  rowno.width = 30;
  var filterItems = function (item_name, radio_id) {
    var gridvalue = Ext.getCmp("gridTPitemGridgeneration").getStore();
    current_type = radio_id;
    gridvalue.baseParams = {
      currentItem: item_name,
      current_type: radio_id,
    };
    gridvalue.load();
  };
  var tpProductsPanel = function (id, ind, title) {
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
        new Ext.Panel({
          title: "TP Products",
          frame: false,
          border: true,
          region: "center",
          layout: "border",
          height: winsize.height * 0.6,
          autoScroll: true,
          items: [
            tpProductsGrid(ind),
            new Ext.Panel({
              title: "Product Details",
              frame: false,
              border: true,
              region: "south",
              cls: "left_side_panel",
              id: "panelTPProductViewDetails" + ind,
              height: winsize.height * 0.3,
              autoScroll: true,
              tpl: new Ext.XTemplate(
                '<div class="details-outer">',
                '<table border="0" width="99%" class="details_view_table">',
                "<tr><th>SKU :</th><td> {stit_SKU} </td></tr>",
                "<tr><th>Brand:</th><td>  {stit_brand_name}</td></tr>",
                "<tr><th>Product Master:</th><td>  {stit_itemName}</td></tr>",
                "<tr><th>Variant:</th><td>  {stit_product_variant}</td></tr>",
                "<tr><th>Net Wt.:</th><td>  {stit_quantity}</td></tr>",
                "<tr><th>Sub Category:</th><td>  {stit_category_name}</td></tr>",
                "<tr><th>HSN Code:</th><td>  {stit_HSN_code}</td></tr>",
                "<tr><th>GST:</th><td>  {stit_GST}</td></tr>",
                "<tr><th>Return Time:</th><td>  {stit_itemReturnTime}</td></tr>",
                "<tr><th>Country of Orgin:</th><td>  {stit_orgin_countryname}</td></tr>",
                "<tr><th>Least Package Type:</th><td>  {least_package_type_name}</td></tr>",
                "<tr><th>Short Description:</th><td>  {stit_Description}</td></tr>",
                "<tr><th>Long Description:</th><td>  {stit_long_description}</td></tr>",
                '<tpl if="image_urlend != null">',
                "<tpl if=\"image_urlend != ''\">",
                "<tr><td>",
                '<tr><th>Deafult Image:</th><td><div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div></td></tr>',
                "</td></tr>",
                "</tpl>",
                "</tpl>",
                "</table>",
                "</div>"
              ),
              fbar: [],
              buttons: [],
            }),
          ],
          buttons: [],
        }),
        new Ext.Panel({
          title: "GS1 Linked Products",
          frame: false,
          border: true,
          region: "east",
          layout: "border",
          height: winsize.height * 0.6,
          autoScroll: true,
          width: winsize.width * 0.5,
          items: [
            gsProductsGrid(ind),
            new Ext.Panel({
              title: "Product Details",
              frame: false,
              border: true,
              region: "south",
              cls: "left_side_panel",
              id: "panelGSProductViewDetails" + ind,
              height: winsize.height * 0.3,
              autoScroll: true,
              tpl: new Ext.XTemplate(
                '<div class="details-outer">',
                '<table border="0" width="99%" class="details_view_table">',
                "<tr><th>GTIN :</th><td> {gtin} </td></tr>",
                "<tr><th>SKU :</th><td> {stit_SKU} </td></tr>",
                "<tr><th>Name :</th><td> {name} </td></tr>",
                "<tr><th>Brand:</th><td>  {stit_brand_name}</td></tr>",
                "<tr><th>Product Master:</th><td>  {stit_itemName}</td></tr>",
                "<tr><th>Variant:</th><td>  {stit_product_variant}</td></tr>",
                "<tr><th>Net Wt.:</th><td>  {stit_quantity}</td></tr>",
                "<tr><th>Sub Category:</th><td>  {stit_category_name}</td></tr>",
                "<tr><th>HSN Code:</th><td>  {stit_HSN_code}</td></tr>",
                "<tr><th>GST:</th><td>  {stit_GST}</td></tr>",
                "<tr><th>Return Time:</th><td>  {stit_itemReturnTime}</td></tr>",
                "<tr><th>Country of Orgin:</th><td>  {stit_orgin_countryname}</td></tr>",
                "<tr><th>Least Package Type:</th><td>  {least_package_type_name}</td></tr>",
                "<tr><th>Short Description:</th><td>  {stit_Description}</td></tr>",
                "<tr><th>Long Description:</th><td>  {stit_long_description}</td></tr>",
                "</table>",
                "</div>"
              ),
              fbar: [],
              buttons: [],
            }),
          ],
          buttons: [
            {
              text: "Create Products",
              id: "createProductId",
              tabIndex: 141,
              buttonAlign: "right",
              handler: function () {
                var tpItemId = Ext.getCmp("tp_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections()[0].data.stit_ID;
                  Application.ThirdPartyProducts.combineProducts(tpItemId,0,0,1);
              },
            },
            {
              text: "Skip Products",
              tabIndex: 140,
              id: "skipProductId",
              buttonAlign: "right",
              handler: function () {
                var tpItemId = Ext.getCmp("tp_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections()[0].data.stit_ID;

                Ext.Msg.confirm(
                  "Notification",
                  "Do you want to skip the Product ?",
                  function (btn) {
                    if (btn == "yes") {
                      Ext.Ajax.request({
                        url: modURL + "&op=skipTPProducts",
                        method: "post",
                        params: {
                          tpItemId: tpItemId,
                        },
                        success: function (resp) {
                          var res = Ext.decode(resp.responseText);
                          if (res.success === true) {
                            Application.example.msg("Notification", res.msg);
                            Ext.getCmp("tp_mapping_grid" + ind)
                              .getStore()
                              .removeAll();
                            Ext.getCmp(
                              "tp_mapping_grid" + ind
                            ).getStore().baseParams.brand_id = Ext.getCmp(
                              "search_brand" + ind
                            ).getValue();
                            Ext.getCmp(
                              "tp_mapping_grid" + ind
                            ).getStore().baseParams.ind = ind;
                            Ext.getCmp("tp_mapping_grid" + ind)
                              .getStore()
                              .load();
                          }
                        },
                      });
                    }
                  }
                );
              },
            },
            {
              text: "Combine Products",
              id: "combineProductId",
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                var tpItemId = Ext.getCmp("tp_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections()[0].data.stit_ID;
                var gsId = Ext.getCmp("gridpanelGSCompare" + ind)
                  .getSelectionModel()
                  .getSelections()[0].data.id;
                var productId = Ext.getCmp("gridpanelGSCompare" + ind)
                  .getSelectionModel()
                  .getSelections()[0].data.productId;
                if (tpItemId > 0 && productId > 0) {
                  Application.ThirdPartyProducts.combineProducts(
                    tpItemId,
                    gsId,
                    productId,0
                  );
                } else {
                  Ext.MessageBox.alert(
                    "Notification",
                    "To Combine products you need to have valid data from both TP and GS Products."
                  );
                }
              },
            },
          ],
        }),
      ],
      listeners: {
        afterrender: function () {},
      },
    });
    return panel;
  };
  var gscheckModel = function (ind) {
    return new Ext.grid.CheckboxSelectionModel({
      multiSelect: false,
      dataIndex: "checked",
      checkOnly: true,
      listeners: {
        selectionchange: function (selModel) {
          if (
            !Ext.isEmpty(
              Ext.getCmp("gridpanelGSCompare" + ind)
                .getSelectionModel()
                .getSelections()
            )
          ) {
          }
        },
        rowdeselect: function (sm, rowIndex, record) {
          record.set("checked", "false");
        },
        rowselect: function (sm, rowIndex, record) {
          record.set("checked", "true");
        },
      },
    });
  };
  var check_model = function (ind) {
    return new Ext.grid.CheckboxSelectionModel({
      multiSelect: false,
      dataIndex: "checked",
      checkOnly: true,
      listeners: {
        selectionchange: function (selModel) {
          if (
            !Ext.isEmpty(
              Ext.getCmp("tp_mapping_grid" + ind)
                .getSelectionModel()
                .getSelections()
            )
          ) {
            var ID = Ext.getCmp("tp_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections()[0].data.stit_ID;
            var stit_SKU = Ext.getCmp("tp_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections()[0].data.stit_SKU;
            var brand = Ext.getCmp("tp_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections()[0].data.stit_brand_name;
            var stit_itemName = Ext.getCmp("tp_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections()[0].data.stit_itemName;
            Ext.getCmp("gridpanelGSCompare")
              .getStore()
              .load({
                params: {
                  stit_SKU: stit_SKU,
                  brand: brand,
                  itemname: stit_itemName,
                },
              });
            Ext.getCmp("panelGSProductViewDetails").update();
          }
        },
        rowdeselect: function (sm, rowIndex, record) {
          record.set("checked", "false");
        },
        rowselect: function (sm, rowIndex, record) {
          record.set("checked", "true");
        },
      },
    });
  };
  var tpMappingProductsStore = function (ind) {
    var qugeo_store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listTpProducts",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "stit_ID",
          root: "data",
        },
        ["stit_ID", "stit_brand_name", "stit_itemName", "stit_SKU"]
      ),
      sortInfo: {
        field: "stit_ID",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "DESC",
      remoteSort: true,
      autoLoad: true,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("tp_mapping_grid" + ind)
            .getView()
            .refresh();
          //Ext.getCmp("tp_mapping_grid").getSelectionModel().selectRow(0);
        },
        beforeload: function () {},
      },
    });

    return qugeo_store;
  };
  function gs1Select(ind) {
    return new Ext.grid.RowSelectionModel({
      singleSelect: true,
      listeners: {
        selectionchange: gridSelectionChanged,
      },
    });
  }
  var gridSelectionChanged = function () {};
  var tpProductsGrid = function (ind) {
    var chk_model = check_model(ind);
    var qugeo_store = tpMappingProductsStore(ind);
    var qugeo_select = gs1Select(ind);
    var gs1ProductsFilter = new Ext.ux.grid.GridFilters({
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
          dataIndex: "stit_brand_name",
        },
      ],
    });
    gs1ProductsFilter.remote = true;
    gs1ProductsFilter.autoReload = true;
    var qugeo_grid = new Ext.grid.GridPanel({
      store: qugeo_store,
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      height: winsize.height * 0.3,
      region: "center",
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [new Ext.ux.grid.GroupSummary(), gs1ProductsFilter],
      id: "tp_mapping_grid" + ind,
      columns: [new Ext.grid.RowNumberer(),
        //chk_model,
        {
          header: "SKU",
          sortable: true,
          dataIndex: "stit_SKU",
          width: 80,
        },
        {
          header: "Brand",
          sortable: true,
          width: 80,
          dataIndex: "stit_brand_name",
        },
        {
          header: "Item Master",
          sortable: true,
          width: 80,
          dataIndex: "stit_itemName",
        },
        {
          xtype: "actioncolumn",
          header: "",
          width: 20,
          hideable: true,
          sortable: false,
          groupable: false,
          tooltip: "Action",
          items: [],
        },
      ],
      //sm: chk_model,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: tpgridSelectionChanged,
        },
      }),
      listeners: {
        resize: updatePagination,
        celldblClick: function (grid, rowIndex, columnIndex, e) {},
        afteredit: function (grid_event) {},
        cellClick: function (grid, rowIndex, columnIndex, e) {
          qugeo_grid.getSelectionModel().selectRow(rowIndex);
        },
        afterrender: function () {},
      },
      tbar: [
        { html: "Select Brand : &nbsp;", id: "tpbranch_label" + ind },
        {
          xtype: "combo",
          mode: "local",
          typeAhead: true,
          editable: true,
          emptyText: "Select",
          anchor: "100%",
          store: gs1BrandComboStore(),
          id: "search_brand" + ind,
          triggerAction: "all",
          displayField: "brandName",
          allowBlank: false,
          valueField: "id",
          hiddenName: "br_id",
          name: "br_id",
          minChars: 1,
          listeners: {
            select: function () {
              Ext.getCmp("tp_mapping_grid" + ind)
                .getStore()
                .removeAll();
              Ext.getCmp(
                "tp_mapping_grid" + ind
              ).getStore().baseParams.brand_id = Ext.getCmp(
                "search_brand" + ind
              ).getValue();
              Ext.getCmp("tp_mapping_grid" + ind).getStore().baseParams.ind =
                ind;
              Ext.getCmp("tp_mapping_grid" + ind)
                .getStore()
                .load();
            },
          },
        },
      ],
      bbar: [],
      stripeRows: true,
    });
    return qugeo_grid;
  };
  function updatePagination(cmp) {
    recs_per_page = update_recs_per_page(cmp);
  }

  var gsProductsGrid = function (ind) {
    var chk_model = gscheckModel(ind);
    var gsMatchedGridStorenew = gsMatchedGridStore();
    var vendoritem_filter = new Ext.ux.grid.GridFilters({
      local: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
        {
          type: "string",
          dataIndex: "brand",
        },
        {
          type: "string",
          dataIndex: "isExisting",
        },
      ],
    });
    vendoritem_filter.remote = true;
    vendoritem_filter.autoReload = true;
    var addItem = new Ext.grid.GridPanel({
      store: gsMatchedGridStorenew,
      height: winsize.height * 0.3,
      frame: false,
      border: false,
      region: "center",
      loadMask: true,
      plugins: [new Ext.ux.grid.GroupSummary(), vendoritem_filter],
      id: "gridpanelGSCompare",
      columns: [new Ext.grid.RowNumberer(),
        {
          header: "Name",
          dataIndex: "stit_SKU",
          hideable: true,
          sortable: true,
        },
        {
          header: "Brand",
          width: 400,
          dataIndex: "stit_brand_name",
          hideable: true,
          sortable: true,
        },        
        {
          header: "Company",
          dataIndex: "med_manufacturename",
          hideable: true,
          sortable: true,
        },
        {
          header: "Existing",
          dataIndex: "isExisting",
          hideable: true,
          sortable: false,
        },
        {
          xtype: "actioncolumn",
          header: " ",
          hideable: false,
          groupable: false,
          width: 20,
          items: [],
        },
      ],
      tbar: [
        { html: "Select Brand : &nbsp;", id: "branch_gslabel" + ind },
        {
          xtype: "combo",
          mode: "local",
          typeAhead: true,
          editable: true,
          emptyText: "Select",
          anchor: "100%",
          store: gs1BrandComboStore(),
          id: "search_gsbrand" + ind,
          triggerAction: "all",
          displayField: "brandName",
          allowBlank: false,
          valueField: "id",
          hiddenName: "br_id",
          name: "br_id",
          minChars: 1,
          listeners: {
            select: function () {
              Ext.getCmp("gridpanelGSCompare" + ind)
                .getStore()
                .removeAll();
              Ext.getCmp(
                "gridpanelGSCompare" + ind
              ).getStore().baseParams.brand_id = Ext.getCmp(
                "search_gsbrand" + ind
              ).getValue();
              Ext.getCmp("gridpanelGSCompare" + ind).getStore().baseParams.ind =
                ind;
              Ext.getCmp("gridpanelGSCompare" + ind)
                .getStore()
                .load();
            },
          },
        },
      ],
      //sm: chk_model,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gsgridSelectionChanged,
        },
      }),
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
        cellClick: function (grid, rowIndex, columnIndex, e) {
          addItem.getSelectionModel().selectRow(rowIndex);
        },
      },
    });
    return addItem;
  };
  var viewItemPanel = function () {
    var CntryOrginStore = function () {
      var CntryOrgStore = new Ext.data.JsonStore({
        url: promodURL + "&op=getOrginCountry",
        fields: ["country_id", "country_name"],
        totalProperty: "totalCount",
        root: "data",
        autoload: true,
        remoteFilter: true,
        listeners: {},
      });
      return CntryOrgStore;
    };
    var gstStorefn = function(){
      var gst_store = new Ext.data.JsonStore({
        url: promodURL + "&op=getGstStore",
        fields: ["id", "hsnGst","hsnId","hsnCess"],
        totalProperty: "totalCount",
        root: "data",
        remoteFilter: true,
        listeners: {},
      });
      return gst_store;
    };
    var hsnStore = function () {
      var hsn_store = new Ext.data.JsonStore({
        url: promodURL + "&op=gethsnStore",
        fields: ["hsn_id", "hsn_code", "gst_percent"],
        totalProperty: "totalCount",
        root: "data",
        autoload: true,
        remoteFilter: true,
        listeners: {},
      });
      return hsn_store;
    };
    var packageMasterComboStore = function () {
      var packMstr_store = new Ext.data.JsonStore({
        url: promodURL + "&op=getPackMastrStore",
        fields: ["rpckm_id", "rpckm_name"],
        totalProperty: "totalCount",
        root: "data",
        autoload: true,
        remoteFilter: true,
        listeners: {},
      });
      return packMstr_store;
    };
    var packTypeStore = function () {
      var pt_store = new Ext.data.JsonStore({
        url: promodURL + "&op=getPTStore",
        fields: ["package_type_id", "package_type_name"],
        totalProperty: "totalCount",
        root: "data",
        autoload: true,
        remoteFilter: true,
        listeners: {
          beforeload: function () {
            this.baseParams.stdpckl11 = Ext.getCmp(
              "least_package_type_id"
            ).getValue();
            this.baseParams.stdpckl12 = Ext.getCmp(
              "stit_package_type_id"
            ).getValue();
            this.baseParams.stdpckl21 = Ext.getCmp(
              "stdpckl21_package_type_id"
            ).getValue();
            this.baseParams.stdpckl31 = Ext.getCmp(
              "stdpckl31_package_type_id"
            ).getValue();
            this.baseParams.stdpckl41 = Ext.getCmp(
              "stdpckl41_package_type_id"
            ).getValue();
          },
        },
      });
      return pt_store;
    };
    var itemMastergroupStore = function () {
      var group_store = new Ext.data.JsonStore({
        autoLoad: true,
        url: promodURL + "&op=getItemMasterStockGroups",
        method: "post",
        loaded: false,
        fields: ["group_id", "group_name", "parent_group"],
        totalProperty: "totalCount",
        root: "data",
        listeners: {
          beforeload: function (store, options) {
            store.loaded = false;
          },
          load: function (store, records, options) {
            var row = new Ext.data.Record.create([
              {
                name: "group_id",
                name: "group_name",
                name: "parent_group",
              },
            ]);

            var r = new row({
              group_id: 0,
              group_name: "",
              parent_group: "",
            });

            store.insert(0, r);
            store.loaded = true;
          },
        },
      });
      return group_store;
    };

    var reconciliation_template_store = new Ext.data.JsonStore({
      method: "post",
      fields: ["value", "text"],
      data: [],
      autoLoad: true,
    });

    var itemMaster_GroupStore = itemMastergroupStore();
    var hsnStore = hsnStore();
    var gstStore = gstStorefn();
    var CntryOrgStore = CntryOrginStore();
    var packTypeStore = packTypeStore();
    var pmComboStore = packageMasterComboStore();

    var itemPanel = new Ext.FormPanel({
      id: "itemPanel",
      width: winsize.width * 0.9,
      height: 500,
      autoScroll: true,
      frame: true,
      layout: "column",
      monitorValid: true,
      items: [
        {
          layout: "form",
          columnWidth: 0.3,
          labelAlign: "top",
          items: [
            {
              xtype: "hidden",
              fieldLabel: "Product Id",
              id: "itemId",
              name: "itemId",
              anchor: "95%",
              allowBlank: false,
            },
            mkCombo({
              type: "mypha_productsubcategory",
              value: "sub_category_id",
              display: "sub_category",
              name: "product_category",
              fieldLabel: "Sub Category",
              emptyText: "Select Product Category..",
              id: "product_category",
              tabIndex: 500,
              anchor: "95%",
              cx: "S_1",
              combo_listeners: {
                select: function (combo, record, index) {
                  var value = record.data.sub_category_id;
                  if (value > 0) {
                    Ext.Ajax.request({
                      url: modURL + "&op=getItemCategory",
                      method: "POST",
                      params: { sub_category_id: value },
                      success: function (res) {
                        var tmp = Ext.decode(res.responseText);
                        console.log("getItemCategory", tmp);
                        Ext.getCmp("iteParentCategory").show();
                        Ext.getCmp("iteParentCategory").setValue(
                          tmp.categoryCombination
                        );
                        if(tmp.isPerishable == 1){
                          Ext.getCmp('courierDelivery').disable();
                          Ext.getCmp('courierDelivery').setValue(0);
                        }else{
                          Ext.getCmp('courierDelivery').enable();
                        }
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
                //                                afterrender:function(field){
                //                                       Ext.defer(function(){
                //                                       field.focus(true,100);
                //                                       },1);
                //                                     }
              },
            }),
          ],
        },
        {
          layout: "form",
          columnWidth: 0.3,
          labelAlign: "top",
          items: [
            mkCombo({
              type: "mypha_productbrands",
              value: "brand_id",
              display: "brand_name",
              name: "pdt_brand",
              fieldLabel: "Brand",
              emptyText: "Select Brand..",
              id: "pdt_brand",
              listeners: false,
              tabIndex: 501,
              anchor: "95%",
              cx: "S_1",
              combo_listeners: {
                select: function (combo, record, index) {
                  updateProductSku();
                },
              },
            }),
          ],
        },
        {
          layout: "form",
          columnWidth: 0.3,
          labelAlign: "top",
          items: [
            mkCombo({
              type: "finascop_stock_itemmastername",
              value: "itemname_id",
              display: "item_name",
              name: "item",
              fieldLabel: "Product Master",
              emptyText: "Select Product Name",
              id: "item",
              listeners: false,
              tabIndex: 502,
              anchor: "94%",
              cx: "S_1",
              combo_listeners: {
                select: function (combo, record, index) {
                  updateProductSku();
                },
              },
            }),
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Variant",
              id: "stit_product_variant",
              name: "stit_product_variant",
              anchor: "95%",
              maxLength: 250,
              tabIndex: 503,
              listeners: {
                change: function () {
                  updateProductSku();
                },
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 1,
          labelAlign: "top",
          hideLabel: true,
          items: [
            {
              hideLabel: true,
              xtype: "displayfield",
              fieldLabel: " ",
              width: 150,
              hidden: true,
              id: "iteParentCategory",
              style: { "font-weight": "bold" },
              anchor: "97%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          items: [
            {
              xtype: "numberfield",
              fieldLabel: "Quantity",
              id: "stit_qty",
              name: "stit_qty",
              anchor: "95%",
              allowNegative: false,
              allowDecimals: true,
              tabIndex: 504,
              listeners: {
                change: function () {
                  var value = Ext.getCmp("stit_unit").getValue();
                  var stit_qty = Ext.getCmp("stit_qty").getValue();
                  if (stit_qty > 0 && value > 0) {
                    var qtyLabel =
                      Ext.getCmp("stit_qty").getRawValue() +
                      " " +
                      Ext.getCmp("stit_unit").getRawValue();
                    Ext.getCmp("stit_quantity").setValue(qtyLabel);
                    updateProductSku();
                  }
                },
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          items: [
            mkCombo({
              type: "mypha_unit",
              value: "unit_id",
              display: "unit_name",
              name: "stit_unit",
              fieldLabel: "Unit",
              emptyText: "Select Units..",
              id: "stit_unit",
              allowBlank: true,
              tabIndex: 505,
              anchor: "95%",
              cx: "S_1",
              combo_listeners: {
                select: function (combo, record, index) {
                  var value = record.data.unit_id;
                  var stit_qty = Ext.getCmp("stit_qty").getValue();
                  if (stit_qty > 0 && value > 0) {
                    var qtyLabel =
                      Ext.getCmp("stit_qty").getRawValue() +
                      " " +
                      record.data.unit_name;
                    Ext.getCmp("stit_quantity").setValue(qtyLabel);
                  }
                },
              },
            }),
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Net Weight",
              id: "stit_quantity",
              name: "stit_quantity",
              anchor: "95%",
              //readOnly: true,
              maxLength: 250,
              tabIndex: 506,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          items: [
            {
              fieldLabel: "HSN",
              xtype: "combo",
              displayField: "hsn_code",
              valueField: "hsn_id",
              mode: "remote",
              id: "HSN",
              name: "HSN",
              emptyText: "Select HSN Code",
              anchor: "95%",
              allowBlank: false,
              typeAhead: true,
              triggerAction: "all",
              lazyRender: true,
              store: hsnStore,
              editable: true,
              tabIndex: 507,
              minChars: 2,
              listeners: {
                select: function (index, val) {
                  var value = Ext.getCmp("HSN").getValue();
                  gstStore.baseParams.hsnId = this.value;
                  gstStore.load();
                },
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          items: [
            {
              fieldLabel: "GST / VAT %",
              xtype: "combo",
              displayField: "hsnGst",
              valueField: "id",
              mode: "remote",
              id: "taxValueId",
              name: "taxValueId",
              emptyText: "Select GST",
              anchor: "95%",
              allowBlank: false,
              typeAhead: true,
              triggerAction: "all",
              lazyRender: true,
              store: gstStore,
              editable: true,
              tabIndex: 507,
              minChars: 2,
              listeners: {
                select: function (index, val) {

                  Ext.getCmp("GST").setValue(val.data.hsnGst);
                },
              },
            },{
              xtype: "hidden",
              fieldLabel: "GST / VAT %",
              id: "GST",
              name: "GST",
              readOnly: true,
              anchor: "95%",
              tabIndex: 508,
              allowBlank: false,
            }
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          labelWidth: 80,
          items: [
            {
              xtype: "combo",
              fieldLabel: "Edible",
              emptyText: "Choose Type",
              id: "stit_foodtype",
              name: "stit_foodtype",
              mode: "local",
              typeAhead: true,
              forceSelection: true,
              editable: true,
              anchor: "95%",
              store: new Ext.data.JsonStore({
                fields: ["id", "name"],
                data: [
                  { id: "0", name: "Not Edible" },
                  { id: "1", name: "Vegetarian" },
                  { id: "2", name: "Non Vegetarian" },
                  { id: "3", name: "Vegan" },
                ],
              }),
              triggerAction: "all",
              minChars: 2,
              displayField: "name",
              valueField: "id",
              hiddenName: "stit_foodtype",
              tabIndex: 509,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.2,
          labelAlign: "top",
          labelWidth: 100,
          items: [
            mkCombo({
              type: "finascop_country",
              value: "country_id",
              display: "country_name",
              name: "stit_orgin_country",
              fieldLabel: "Country of Orgin",
              emptyText: "Select..",
              id: "stit_orgin_country",
              listeners: false,
              allowBlank: true,
              tabIndex: 510,
              anchor: "95%",
              cx: "S_1",
            }),
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          items: [
            {
              fieldLabel: "Package",
              xtype: "combo",
              displayField: "rpckm_name",
              valueField: "rpckm_id",
              mode: "remote",
              id: "stit_package_master",
              name: "stit_package_master",
              emptyText: "Select Package",
              anchor: "95%",
              typeAhead: true,
              triggerAction: "all",
              lazyRender: true,
              store: pmComboStore,
              editable: true,
              tabIndex: 511,
              minChars: 2,
              listeners: {},
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Product Weight",
              id: "stit_courierWt",
              name: "stit_courierWt",
              anchor: "95%",
              maxLength: 250,
              tabIndex: 512,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              width: 100,
              id: "stit_itemReturnTime",
              name: "stit_itemReturnTime",
              tabIndex: 513,
              anchor: "99%",
              allowBlank: true,
              fieldLabel: "Return Time (days)",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.2,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "right",
          labelWidth: 200,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              hideLabel: true,
              id: "stit_custInitiate",
              name: "stit_custInitiate",
              labelWidth: 200,
              inputValue: 1,
              tabIndex: 514,
              boxLabel: "Spot Return Available",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              id: "item_length",
              anchor: "99%",
              emptyText: "Width",
              name: "item_length",
              fieldLabel: "Width",
              tabIndex: 515,
              maxLength: 10,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              id: "item_breadth",
              emptyText: "Depth",
              name: "item_breadth",
              fieldLabel: "Depth",
              anchor: "99%",
              tabIndex: 516,
              maxLength: 10,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              id: "item_height",
              emptyText: "Height",
              name: "item_height",
              fieldLabel: "Height",
              anchor: "99%",
              tabIndex: 517,
              maxLength: 10,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Display Label",
              emptyText: "Display Label",
              id: "stit_displaylabel",
              name: "stit_displaylabel",
              anchor: "50%",
              width: 100,
              maxLength: 250,
              tabIndex: 531,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "right",
          labelWidth: 45,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              id: "directPurchase",
              name: "directPurchase",
              inputValue: 1,
              tabIndex: 518,
              boxLabel: "Raw",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "right",
          labelWidth: 45,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              id: "featured",
              name: "featured",
              inputValue: 1,
              tabIndex: 519,
              boxLabel: "Featured",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "right",
          labelWidth: 45,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              id: "popular",
              name: "popular",
              inputValue: 1,
              tabIndex: 520,
              boxLabel: "Popular",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "right",
          labelWidth: 45,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              id: "courierDelivery",
              name: "courierDelivery",
              inputValue: 1,
              tabIndex: 521,
              boxLabel: "Courier",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "right",
          labelWidth: 45,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              id: "directDelivery",
              name: "directDelivery",
              inputValue: 1,
              tabIndex: 522,
              boxLabel: "Direct",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.15,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "right",
          labelWidth: 45,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              id: "isRRPApplicable",
              name: "isRRPApplicable",
              inputValue: 1,
              tabIndex: 523,
              boxLabel: "RRP",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.3,
          labelAlign: "top",
          labelWidth: 100,
          items: [],
        },
        {
          layout: "form",
          columnWidth: 1.03,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Item ERP Id",
              id: "stit_itemERPId",
              name: "stit_itemERPId",
              anchor: "95%",
              maxLength: 100,
              tabIndex: 524,
              allowBlank: true,
              hidden: true,
            },
            {
              xtype: "textfield",
              fieldLabel: "Item Barcode",
              id: "stit_itemBarcode",
              name: "stit_itemBarcode",
              anchor: "95%",
              maxLength: 100,
              tabIndex: 524,
              allowBlank: true,
              hidden: true,
            },
            {
              xtype: "textfield",
              fieldLabel: "MRP",
              id: "MRP",
              name: "MRP",
              anchor: "95%",
              tabIndex: 524,
              hidden: true,
              listeners: {
                blur: function (txt) {
                  txt.setValue(
                    Ext.util.Format.number(
                      txt.getValue(),
                      FINASCOP_CURRENCY_FORMAT
                    )
                  );
                },
              },
            },
            {
              xtype: "numberfield",
              id: "pdt_sale_rate",
              name: "pdt_sale_rate",
              allowNegative: false,
              allowDecimals: true,
              fieldLabel: "Sale Rate",
              anchor: "95%",
              tabIndex: 524,
              msgTarget: "qtip",
              hidden: true,
            },
            {
              xtype: "combo",
              fieldLabel: "Group",
              id: "itemgroup",
              hiddenName: "itemgroup",
              anchor: "95%",
              tabIndex: 524,
              hidden: true,
              displayField: "parent_group",
              valueField: "group_id",
              store: itemMaster_GroupStore,
              triggerAction: "all",
              forceSelection: true,
              typeAhead: true,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.5,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "top",
          items: [
            {
              xtype: "radiogroup",
              anchor: "98%",
              mode: "remote",
              forceSelection: true,
              triggerAction: "all",
              lazyRender: true,
              tabIndex: 15,
              items: [
                {
                  boxLabel: "TP",
                  id: "shortDesc1",
                  name: "short_desc",
                  inputValue: "1",
                  listeners: {
                    check: function (rgp, checked) {
                      if (checked == true) {
                        var tpdesc =
                          Application.ThirdPartyProducts.Cache.tpdesc.replace(
                            "TP : ",
                            ""
                          );
                        Ext.getCmp("description").setValue(tpdesc);
                      }
                    },
                  },
                },
                {
                  boxLabel: "Existing",
                  id: "shortDesc2",
                  name: "short_desc",
                  inputValue: "2",
                  listeners: {
                    check: function (rgp, checked) {
                      if (checked == true) {
                        var existing =
                          Application.ThirdPartyProducts.Cache.existing.replace(
                            "Existing : ",
                            ""
                          );
                        Ext.getCmp("description").setValue(existing);
                      }
                    },
                  },
                },
                {
                  boxLabel: "New",
                  id: "shortDesc3",
                  name: "short_desc",
                  inputValue: "3",
                  listeners: {
                    check: function (rgp, checked) {
                      if (checked == true) {
                        Ext.getCmp("description").reset();
                      }
                    },
                  },
                },
              ],
            },
            {
              xtype: "textarea",
              fieldLabel: "Short Description",
              id: "description",
              name: "description",
              anchor: "95%",
              maxLength: 1000,
              tabIndex: 525,
              allowBlank: false,
              height: 220,
              style: {
                color: "black",
                fontFamily: "verdana",
                fontSize: "12px",
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.5,
          bodyStyle: { "padding-top": "15px" },
          labelAlign: "top",
          items: [{
            xtype: "radiogroup",
            anchor: "98%",
            mode: "remote",
            forceSelection: true,
            triggerAction: "all",
            lazyRender: true,
            tabIndex: 15,
            items: [
              {
                boxLabel: "TP",
                id: "longDesc1",
                name: "long_desc",
                inputValue: "1",
                listeners: {
                  check: function (rgp, checked) {
                    if (checked == true) {
                      var tpdescLong =
                        Application.ThirdPartyProducts.Cache.tpdescLong.replace(
                          "TP : ",
                          ""
                        );
                      Ext.getCmp("stit_long_description").setValue(tpdescLong);
                    }
                  },
                },
              },
              {
                boxLabel: "Existing",
                id: "longDesc2",
                name: "long_desc",
                inputValue: "2",
                listeners: {
                  check: function (rgp, checked) {
                    if (checked == true) {
                      var existingLong =
                        Application.ThirdPartyProducts.Cache.existingLong.replace(
                          "Existing : ",
                          ""
                        );
                      Ext.getCmp("stit_long_description").setValue(existingLong);
                    }
                  },
                },
              },
              {
                boxLabel: "New",
                id: "longDesc3",
                name: "long_desc",
                inputValue: "3",
                listeners: {
                  check: function (rgp, checked) {
                    if (checked == true) {
                      Ext.getCmp("stit_long_description").setValue();
                    }
                  },
                },
              },
            ],
          },
            {
              xtype: "templateeditormce",
              fieldLabel: "Long Description",
              anchor: "95%",
              id: "stit_long_description",
              name: "stit_long_description",
              maxLength: 7000,
              height: 250,
              tabIndex: 526,
              listeners: {},
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.2,
          labelAlign: "top",
          items: [],
        },
        {
          layout: "form",
          columnWidth: 0.8,
          labelAlign: "top",
          items: [
            {
              xtype: "label",
              html: "&nbsp;",
            },
          ],
        },
        {
          hidden:true,
          layout: "form",
          columnWidth: 0.4,
          labelWidth: 140,
          bodyStyle: { "background-color": "F1F1F1" },
          items: [
            {
              labelAlign: "top",
              xtype: "compositefield",
              fieldLabel: "Primary Package",
              hideLabel: true,
              combineErrors: false,
              items: [
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stit_package_type_id",
                  fieldLabel: "Primary Package",
                  emptyText: "Primary Package",
                  id: "stit_package_type_id",
                  hiddenName: "stit_package_type_id",
                  listeners: false,
                  allowBlank: true,
                  anchor: "50%",
                  tabIndex: 532,
                  cx: "S_1",
                  combo_listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      var stit_package_type_id = Ext.getCmp(
                        "least_package_type_id"
                      ).getValue();
                      if (value != stit_package_type_id) {
                        Ext.getCmp("astdpck0").enable();
                        Ext.getCmp("stit_stdPacking").setValue(true);
                        Ext.getCmp("stit_salesUnit").setValue(true);
                        Ext.getCmp("stdpckl1_nos").allowBlank = false;
                      } else {
                        Ext.getCmp("astdpck0").disable();
                        Ext.getCmp("stdpckl1_nos").setValue(1);
                        Ext.getCmp("stdpckl1_nos").setReadOnly(true);
                      }
                      Ext.getCmp("stdpckl12_package_type_id").setValue(value);
                      updateSalesPackageTtpeStore(
                        "stit_package_type_id",
                        "ccsb_package_type_id"
                      );
                    },
                  },
                }),
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "least_package_type_id",
                  fieldLabel: "SKU ",
                  allowBlank: true,
                  emptyText: "SKU ",
                  id: "least_package_type_id",
                  hiddenName: "least_package_type_id",
                  listeners: false,
                  anchor: "50%",
                  tabIndex: 533,
                  cx: "S_1",
                  combo_listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      var stit_package_type_id = Ext.getCmp(
                        "stit_package_type_id"
                      ).getValue();
                      console.log("value", value);
                      console.log("stit_package_type_id", stit_package_type_id);
                      if (value != stit_package_type_id) {
                        Ext.getCmp("astdpck0").enable();
                        Ext.getCmp("stit_stdPacking").setValue(true);
                        Ext.getCmp("stit_salesUnit").setValue(true);
                        Ext.getCmp("stdpckl1_nos").allowBlank = false;
                      } else {
                        Ext.getCmp("astdpck0").disable();
                        Ext.getCmp("stdpckl1_nos").setValue(1);
                        Ext.getCmp("stdpckl1_nos").setReadOnly(true);
                      }
                      Ext.getCmp("stdpckl11_package_type_id").setValue(value);
                      updateSalesPackageTtpeStore(
                        "least_package_type_id",
                        "ccsb_package_type_id"
                      );
                      updateProductSku();
                      //                                    Ext.getCmp('ccsb_package_type_id').setValue(value);
                      //                                    Ext.getCmp('cosb_package_type_id').setValue(value);
                      //                                    Ext.getCmp('ds_package_type_id').setValue(value);
                      //                                    Ext.getCmp('rsb_package_type_id').setValue(value);
                    },
                  },
                }),
              ],
            },
            {
              xtype: "checkbox",
              checked: false,
              id: "stit_stdPacking",
              name: "stit_stdPacking",
              hideLabel: true,
              inputValue: 1,
              tabIndex: 534,
              labelWidth: 200,
              boxLabel: "Apply Standard Packing",
            },
            {
              labelAlign: "top",
              xtype: "compositefield",
              hideLabel: true,
              fieldLabel: " ",
              id: "astdpck0",
              combineErrors: false,
              items: [
                {
                  xtype: "numberfield",
                  id: "stdpckl1_nos",
                  emptyText: "Nos",
                  name: "stdpckl1_nos",
                  fieldLabel: "Nos",
                  width: 50,
                  tabIndex: 535,
                  maxLength: 10,
                  listeners: {
                    change: function () {},
                  },
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stdpckl12_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "stdpckl12_package_type_id",
                  hiddenName: "stdpckl12_package_type_id",
                  listeners: false,
                  hideTrigger: true,
                  allowBlank: true,
                  width: 55,
                  tabIndex: 536,
                  cx: "S_1",
                  combo_listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      Ext.getCmp("cos_package_type_id").setValue(value);
                      Ext.getCmp("rs_package_type_id").setValue(value);
                    },
                  },
                }),
                {
                  style: "margin-top:7px;",
                  html: " = 1 ",
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stdpckl11_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Purchasing unit",
                  id: "stdpckl11_package_type_id",
                  hiddenName: "stdpckl11_package_type_id",
                  listeners: false,
                  hideTrigger: true,
                  allowBlank: true,
                  width: 55,
                  tabIndex: 537,
                  cx: "S_1",
                  combo_listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      Ext.getCmp("stdpckl22_package_type_id").setValue(value);
                    },
                  },
                }),
                {
                  xtype: "button",
                  text: "Add",
                  align: "right",
                  tabIndex: 538,
                  iconCls: "finascop_add",
                  handler: function () {
                    Application.MyphaProduct.Cache.aspLevel++;
                    if (Application.MyphaProduct.Cache.aspLevel <= 4) {
                      Ext.getCmp(
                        "astdpck" + Application.MyphaProduct.Cache.aspLevel
                      ).show();
                      var value = Ext.getCmp(
                        "stdpckl" +
                          Application.MyphaProduct.Cache.aspLevel +
                          "1_package_type_id"
                      ).getValue();
                      var level = Application.MyphaProduct.Cache.aspLevel + 1;
                      Ext.getCmp("stdpckl" + level + "_nos").allowBlank = false;
                      Ext.getCmp(
                        "stdpckl" + level + "2_package_type_id"
                      ).setValue(value);
                    }
                  },
                },
              ],
            },
            {
              xtype: "compositefield",
              labelAlign: "top",
              hideLabel: true,
              fieldLabel: " ",
              id: "astdpck1",
              hidden: true,
              combineErrors: false,
              items: [
                {
                  xtype: "numberfield",
                  id: "stdpckl2_nos",
                  emptyText: "Nos",
                  name: "stdpckl2_nos",
                  fieldLabel: "Nos",
                  width: 50,
                  tabIndex: 526,
                  maxLength: 10,
                  listeners: {
                    change: function () {
                      var numbers = Ext.getCmp("cos_nos").getValue();
                      Ext.getCmp("rs_nos").setValue(numbers);
                    },
                  },
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stdpckl22_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "stdpckl22_package_type_id",
                  hiddenName: "stdpckl22_package_type_id",
                  hideTrigger: true,
                  allowBlank: true,
                  width: 55,
                  tabIndex: 527,
                  cx: "S_1",
                  readOnly: true,
                }),
                {
                  style: "margin-top:7px;",
                  html: " = 1 ",
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stdpckl21_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Purchasing unit",
                  id: "stdpckl21_package_type_id",
                  hiddenName: "stdpckl21_package_type_id",
                  listeners: false,
                  allowBlank: true,
                  width: 55,
                  tabIndex: 528,
                  cx: "S_1",
                  combo_listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      Ext.getCmp("stdpckl32_package_type_id").setValue(value);
                      updateSalesPackageTtpeStore(
                        "stdpckl21_package_type_id",
                        "ccsb_package_type_id"
                      );
                    },
                  },
                }),
                {
                  xtype: "button",
                  text: "Delete",
                  iconCls: "finascop_delete",
                  align: "right",
                  tabIndex: 529,
                  handler: function () {
                    Ext.getCmp("stdpckl2_nos").reset();
                    Ext.getCmp("stdpckl22_package_type_id").reset();
                    Ext.getCmp("stdpckl21_package_type_id").reset();
                    Ext.getCmp("astdpck1").hide();
                    Application.MyphaProduct.Cache.aspLevel = 0;
                  },
                },
              ],
            },
            {
              xtype: "compositefield",
              labelAlign: "top",
              hideLabel: true,
              fieldLabel: " ",
              id: "astdpck2",
              hidden: true,
              combineErrors: false,
              items: [
                {
                  xtype: "numberfield",
                  id: "stdpckl3_nos",
                  emptyText: "Nos",
                  name: "stdpckl3_nos",
                  fieldLabel: "Nos",
                  width: 50,
                  tabIndex: 529,
                  maxLength: 10,
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stdpckl32_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "stdpckl32_package_type_id",
                  hiddenName: "stdpckl32_package_type_id",
                  listeners: false,
                  allowBlank: true,
                  width: 55,
                  tabIndex: 530,
                  cx: "S_1",
                  readOnly: true,
                }),
                {
                  style: "margin-top:7px;",
                  html: " = 1 ",
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stdpckl31_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Purchasing Unit",
                  id: "stdpckl31_package_type_id",
                  hiddenName: "stdpckl31_package_type_id",
                  listeners: false,
                  allowBlank: true,
                  width: 55,
                  tabIndex: 531,
                  cx: "S_1",
                  combo_listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      Ext.getCmp("stdpckl42_package_type_id").setValue(value);
                      updateSalesPackageTtpeStore(
                        "stdpckl31_package_type_id",
                        "ccsb_package_type_id"
                      );
                    },
                  },
                }),
                {
                  xtype: "button",
                  text: "Delete",
                  iconCls: "finascop_delete",
                  align: "right",
                  tabIndex: 532,
                  handler: function () {
                    Ext.getCmp("stdpckl3_nos").reset();
                    Ext.getCmp("stdpckl32_package_type_id").reset();
                    Ext.getCmp("stdpckl31_package_type_id").reset();
                    Ext.getCmp("astdpck2").hide();
                    Application.MyphaProduct.Cache.aspLevel = 1;
                  },
                },
              ],
            },
            {
              labelAlign: "top",
              xtype: "compositefield",
              hideLabel: true,
              fieldLabel: " ",
              id: "astdpck3",
              hidden: true,
              combineErrors: false,
              items: [
                {
                  xtype: "numberfield",
                  id: "stdpckl4_nos",
                  emptyText: "Nos",
                  name: "stdpckl4_nos",
                  fieldLabel: "Nos",
                  width: 50,
                  tabIndex: 532,
                  maxLength: 10,
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stdpckl42_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "stdpckl42_package_type_id",
                  hiddenName: "stdpckl42_package_type_id",
                  width: 55,
                  allowBlank: true,
                  listeners: false,
                  tabIndex: 533,
                  cx: "S_1",
                  hideTrigger: true,
                  readOnly: true,
                }),
                {
                  style: "margin-top:7px;",
                  html: " = 1 ",
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "stdpckl41_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Purchasing Unit",
                  id: "stdpckl41_package_type_id",
                  hiddenName: "stdpckl41_package_type_id",
                  width: 55,
                  listeners: false,
                  allowBlank: true,
                  tabIndex: 534,
                  cx: "S_1",
                  combo_listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      updateSalesPackageTtpeStore(
                        "stdpckl41_package_type_id",
                        "ccsb_package_type_id"
                      );
                    },
                  },
                }),
                {
                  xtype: "button",
                  text: "Delete",
                  iconCls: "finascop_delete",
                  align: "right",
                  tabIndex: 535,
                  handler: function () {
                    Ext.getCmp("stdpckl4_nos").reset();
                    Ext.getCmp("stdpckl42_package_type_id").reset();
                    Ext.getCmp("stdpckl41_package_type_id").reset();
                    Ext.getCmp("astdpck3").hide();
                    Application.MyphaProduct.Cache.aspLevel = 2;
                  },
                },
              ],
            },
          ],
        },
        {
          hidden:true,
          layout: "form",
          columnWidth: 0.3,
          labelWidth: 150,
          labelAlign: "top",
          items: [
            {
              xtype: "compositefield",
              fieldLabel: "Level 1 (Qty in nos)",
              id: "level1_details",
              combineErrors: false,
              items: [
                {
                  xtype: "numberfield",
                  id: "stitl1_optimumqty",
                  emptyText: "Opti Qty",
                  name: "stitl1_optimumqty",
                  fieldLabel: "Optimum Qty",
                  width: 80,
                  tabIndex: 539,
                  maxLength: 10,
                  allowBlank: false,
                },
                {
                  xtype: "numberfield",
                  id: "stit11_minimumqty",
                  emptyText: "Min Qty",
                  name: "stit11_minimumqty",
                  fieldLabel: "Minimum Qty",
                  width: 80,
                  tabIndex: 540,
                  maxLength: 10,
                  allowBlank: false,
                },
                {
                  xtype: "numberfield",
                  id: "stit11_maximumqty",
                  emptyText: "Max Qty",
                  name: "stit11_maximumqty",
                  fieldLabel: "Maximium Qty",
                  width: 80,
                  tabIndex: 541,
                  maxLength: 10,
                  allowBlank: false,
                },
              ],
            },
            {
              xtype: "compositefield",
              fieldLabel: "Level 2 (Qty in nos)",
              combineErrors: false,
              items: [
                {
                  xtype: "numberfield",
                  id: "stitl2_optimumqty",
                  emptyText: "Opti Qty",
                  name: "stitl2_optimumqty",
                  fieldLabel: "Optimum Qty",
                  width: 80,
                  tabIndex: 542,
                  maxLength: 10,
                  allowBlank: false,
                },
                {
                  xtype: "numberfield",
                  id: "stit12_minimumqty",
                  emptyText: "Min Qty",
                  name: "stit12_minimumqty",
                  fieldLabel: "Mininimum Qty",
                  width: 80,
                  tabIndex: 543,
                  maxLength: 10,
                  allowBlank: false,
                },
                {
                  xtype: "numberfield",
                  id: "stit12_maximumqty",
                  emptyText: "Max Qty",
                  name: "stit12_maximumqty",
                  fieldLabel: "Maximium Qty",
                  width: 80,
                  tabIndex: 544,
                  maxLength: 10,
                  allowBlank: false,
                },
              ],
            },
            {
              xtype: "compositefield",
              fieldLabel: "Level 3 (Qty in nos)",
              combineErrors: false,
              items: [
                {
                  xtype: "numberfield",
                  id: "stitl3_optimumqty",
                  emptyText: "Opti Qty",
                  name: "stitl3_optimumqty",
                  fieldLabel: "Optimum Qty",
                  width: 80,
                  tabIndex: 545,
                  maxLength: 10,
                  allowBlank: false,
                },
                {
                  xtype: "numberfield",
                  id: "stit13_minimumqty",
                  emptyText: "Min Qty",
                  name: "stit13_minimumqty",
                  fieldLabel: "Minimum Qty",
                  width: 80,
                  tabIndex: 546,
                  maxLength: 10,
                  allowBlank: false,
                },
                {
                  xtype: "numberfield",
                  id: "stit13_maximumqty",
                  emptyText: "Max Qty",
                  name: "stit13_maximumqty",
                  fieldLabel: "Maximium Qty",
                  width: 80,
                  tabIndex: 547,
                  maxLength: 10,
                  allowBlank: false,
                },
              ],
            },
            {
              xtype: "compositefield",
              fieldLabel: "Buffer %",
              combineErrors: false,
              items: [
                {
                  xtype: "numberfield",
                  emptyText: "Distributer Buffer %",
                  id: "stii_csb",
                  name: "stii_csb",
                  anchor: "75%",
                  width: 120,
                  tabIndex: 548,
                  maxValue: 100,
                  allowBlank: false,
                },
                {
                  xtype: "numberfield",
                  emptyText: "Retailer Buffer %",
                  id: "stii_csbretail",
                  name: "stii_csbretail",
                  anchor: "75%",
                  width: 120,
                  tabIndex: 549,
                  maxValue: 100,
                  allowBlank: false,
                },
              ],
            },
          ],
        },
        {
          hidden:true,
          layout: "form",
          columnWidth: 0.3,
          labelWidth: 120,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              labelAlign: "right",
              id: "stit_salesUnit",
              name: "stit_salesUnit",
              inputValue: 1,
              tabIndex: 550,
              boxLabel: "Sales Uinit Applicable",
            },
            {
              //hidden: true,
              labelAlign: "top",
              xtype: "compositefield",
              fieldLabel: "Online Sales",
              combineErrors: false,
              items: [
                {
                  xtype: "combo",
                  displayField: "package_type_name",
                  valueField: "package_type_id",
                  mode: "local",
                  id: "cosb_package_type_id",
                  name: "cosb_package_type_id",
                  //allowBlank: false,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "Purchasing unit",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 551,
                  store: packTypeStore,
                  listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      Ext.getCmp("ccsb_package_type_id").setValue(value);
                      Ext.getCmp("rsb_package_type_id").setValue(value);
                    },
                  },
                },
                {
                  xtype: "numberfield",
                  id: "cos_nos",
                  hidden: true,
                  emptyText: "Nos",
                  name: "cos_nos",
                  fieldLabel: "Nos",
                  width: 80,
                  tabIndex: 555,
                  maxLength: 10,
                  //allowBlank: false,
                  listeners: {
                    change: function () {
                      var numbers = Ext.getCmp("cos_nos").getValue();
                      Ext.getCmp("rs_nos").setValue(numbers);
                    },
                  },
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "cos_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "cos_package_type_id",
                  hiddenName: "cos_package_type_id",
                  width: 80,
                  hidden: true,
                  allowBlank: true,
                  tabIndex: 556,
                  cx: "S_1",
                  readOnly: true,
                }),
                {
                  xtype: "numberfield",
                  hidden: true,
                  id: "cos_length",
                  emptyText: "Length",
                  name: "cos_length",
                  fieldLabel: "Length",
                  width: 100,
                  tabIndex: 557,
                  maxLength: 10,
                  listeners: {
                    change: function () {
                      var numbers = Ext.getCmp("cos_length").getValue();
                      Ext.getCmp("rs_length").setValue(numbers);
                    },
                  },
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "cos_breadth",
                  emptyText: "Breadth",
                  name: "cos_breadth",
                  fieldLabel: "Breadth",
                  width: 100,
                  tabIndex: 558,
                  maxLength: 10,
                  listeners: {
                    change: function () {
                      var numbers = Ext.getCmp("cos_breadth").getValue();
                      Ext.getCmp("rs_breadth").setValue(numbers);
                    },
                  },
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "cos_height",
                  emptyText: "Height",
                  name: "cos_height",
                  fieldLabel: "Height",
                  width: 100,
                  tabIndex: 559,
                  maxLength: 10,
                  listeners: {
                    change: function () {
                      var numbers = Ext.getCmp("cos_height").getValue();
                      Ext.getCmp("rs_height").setValue(numbers);
                    },
                  },
                },
                {
                  xtype: "numberfield",
                  hidden: true,
                  emptyText: "Weight in grams",
                  id: "cos_weight",
                  name: "cos_weight",
                  tabIndex: 560,
                  width: 100,
                  maxLength: 10,
                  listeners: {
                    change: function () {
                      var numbers = Ext.getCmp("cos_weight").getValue();
                      Ext.getCmp("rs_weight").setValue(numbers);
                    },
                  },
                },
              ],
            },
            {
              //hidden:true,
              labelAlign: "top",
              xtype: "compositefield",
              fieldLabel: "Counter Sales",
              disabled: true,
              combineErrors: false,
              items: [
                {
                  xtype: "combo",
                  displayField: "package_type_name",
                  valueField: "package_type_id",
                  mode: "local",
                  id: "ccsb_package_type_id",
                  name: "ccsb_package_type_id",
                  //allowBlank: false,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "Package Type",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 552, //reconciliation_template_store
                  store: packTypeStore,
                },
                {
                  xtype: "numberfield",
                  id: "ccs_nos",
                  emptyText: "Nos",
                  hidden: true,
                  name: "ccs_nos",
                  fieldLabel: "Nos",
                  width: 80,
                  tabIndex: 548,
                  maxLength: 10,
                  //allowBlank: false,
                  listeners: {
                    change: function () {
                      var numbers = Ext.getCmp("ccs_nos").getValue();
                      Ext.getCmp("cos_nos").setValue(numbers);
                      Ext.getCmp("rs_nos").setValue(numbers);
                    },
                  },
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "ccs_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "ccs_package_type_id",
                  hiddenName: "ccs_package_type_id",
                  listeners: false,
                  allowBlank: true,
                  width: 80,
                  hidden: true,
                  tabIndex: 549,
                  cx: "S_1",
                  combo_listeners: {
                    select: function (combo, record, index) {
                      var value = record.data.package_type_id;
                      //Ext.getCmp('cos_package_type_id').setValue(value);
                      //Ext.getCmp('rs_package_type_id').setValue(value);
                    },
                  },
                }),
                {
                  xtype: "numberfield",
                  id: "ccs_length",
                  emptyText: "Length",
                  name: "ccs_length",
                  fieldLabel: "Length",
                  width: 100,
                  hidden: true,
                  tabIndex: 550,
                  maxLength: 10,
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "ccs_breadth",
                  emptyText: "Breadth",
                  name: "ccs_breadth",
                  fieldLabel: "Breadth",
                  width: 100,
                  tabIndex: 551,
                  maxLength: 10,
                },
                {
                  xtype: "textfield",
                  id: "ccs_height",
                  hidden: true,
                  emptyText: "Height",
                  name: "ccs_height",
                  fieldLabel: "Height",
                  width: 100,
                  tabIndex: 552,
                  maxLength: 10,
                },
                {
                  xtype: "numberfield",
                  hidden: true,
                  emptyText: "Weight in grams",
                  id: "ccs_weight",
                  name: "ccs_weight",
                  tabIndex: 553,
                  width: 100,
                },
              ],
            },
            {
              hidden: true,
              xtype: "compositefield",
              fieldLabel: "SKU for Retailers",
              combineErrors: false,
              items: [
                {
                  xtype: "combo",
                  displayField: "package_type_name",
                  valueField: "package_type_id",
                  mode: "local",
                  id: "rsb_package_type_id",
                  name: "rsb_package_type_id",
                  allowBlank: true,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "From Distributor as",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 553,
                  store: packTypeStore,
                  listeners: {},
                },
                {
                  html: "Contains",
                },
                {
                  xtype: "numberfield",
                  id: "rs_nos",
                  emptyText: "Nos",
                  name: "rs_nos",
                  fieldLabel: "Nos",
                  width: 100,
                  tabIndex: 562,
                  maxLength: 10,
                  //allowBlank: false
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "rs_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "rs_package_type_id",
                  hiddenName: "rs_package_type_id",
                  listeners: false,
                  allowBlank: true,
                  width: 100,
                  tabIndex: 563,
                  cx: "S_1",
                  readOnly: true,
                }),
                {
                  xtype: "numberfield",
                  hidden: true,
                  id: "rs_length",
                  emptyText: "Length",
                  name: "rs_length",
                  fieldLabel: "Length",
                  width: 100,
                  tabIndex: 564,
                  maxLength: 10,
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "rs_breadth",
                  emptyText: "Breadth",
                  name: "rs_breadth",
                  fieldLabel: "Breadth",
                  width: 100,
                  tabIndex: 565,
                  maxLength: 10,
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "rs_height",
                  emptyText: "Height",
                  name: "rs_height",
                  fieldLabel: "Height",
                  width: 100,
                  tabIndex: 566,
                  maxLength: 10,
                },
                {
                  xtype: "numberfield",
                  hidden: true,
                  emptyText: "Weight in grams",
                  id: "rs_weight",
                  name: "rs_weight",
                  tabIndex: 567,
                  width: 100,
                  maxLength: 10,
                },
              ],
            },
            {
              labelAlign: "top",
              xtype: "compositefield",
              fieldLabel: "Distributor Sales",
              combineErrors: false,
              items: [
                {
                  xtype: "combo",
                  displayField: "package_type_name",
                  valueField: "package_type_id",
                  mode: "local",
                  id: "dsb_package_type_id",
                  name: "dsb_package_type_id",
                  //allowBlank: false,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "From CS as",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 553,
                  store: packTypeStore,
                  listeners: {},
                },
                {
                  xtype: "numberfield",
                  id: "ds_nos",
                  hidden: true,
                  emptyText: "Nos",
                  name: "ds_nos",
                  fieldLabel: "Nos",
                  width: 80,
                  tabIndex: 569,
                  maxLength: 10,
                  //allowBlank: false
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "ds_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "ds_package_type_id",
                  hiddenName: "ds_package_type_id",
                  width: 80,
                  listeners: false,
                  allowBlank: true,
                  hidden: true,
                  tabIndex: 570,
                  cx: "S_1",
                  hideTrigger: true,
                  readOnly: true,
                }),
                {
                  xtype: "numberfield",
                  hidden: true,
                  id: "ds_length",
                  emptyText: "Length",
                  name: "ds_length",
                  fieldLabel: "Length",
                  width: 100,
                  tabIndex: 571,
                  maxLength: 10,
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "ds_breadth",
                  emptyText: "Breadth",
                  name: "ds_breadth",
                  fieldLabel: "Breadth",
                  width: 100,
                  tabIndex: 572,
                  maxLength: 10,
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "ds_height",
                  emptyText: "Height",
                  name: "ds_height",
                  fieldLabel: "Height",
                  width: 100,
                  tabIndex: 573,
                  maxLength: 10,
                },
                {
                  xtype: "numberfield",
                  hidden: true,
                  emptyText: "Weight in grams",
                  id: "ds_weight",
                  name: "ds_weight",
                  tabIndex: 574,
                  width: 100,
                  maxLength: 10,
                },
              ],
            },
            {
              xtype: "compositefield",
              labelAlign: "top",
              fieldLabel: "Stockist Sale",
              combineErrors: false,
              items: [
                {
                  xtype: "combo",
                  displayField: "package_type_name",
                  valueField: "package_type_id",
                  mode: "local",
                  id: "csb_package_type_id",
                  name: "csb_package_type_id",
                  //allowBlank: false,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "Central Store Package",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 554,
                  store: packTypeStore,
                  listeners: {},
                },
                {
                  xtype: "numberfield",
                  id: "cs_nos",
                  emptyText: "Nos",
                  name: "cs_nos",
                  fieldLabel: "Nos",
                  width: 80,
                  hidden: true,
                  tabIndex: 576,
                  maxLength: 10,
                  //allowBlank: false
                },
                mkCombo({
                  type: "mypha_productpackage_type",
                  value: "package_type_id",
                  display: "package_type_name",
                  name: "cs_package_type_id",
                  fieldLabel: "Package Type",
                  emptyText: "Contains",
                  id: "cs_package_type_id",
                  hiddenName: "cs_package_type_id",
                  listeners: false,
                  allowBlank: true,
                  tabIndex: 577,
                  hidden: true,
                  width: 80,
                  cx: "S_1",
                  hideTrigger: true,
                  readOnly: true,
                }),
                {
                  xtype: "numberfield",
                  hidden: true,
                  id: "cs_length",
                  emptyText: "Length",
                  name: "cs_length",
                  fieldLabel: "Length",
                  width: 100,
                  tabIndex: 578,
                  maxLength: 10,
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "cs_breadth",
                  emptyText: "Breadth",
                  name: "cs_breadth",
                  fieldLabel: "Breadth",
                  width: 100,
                  tabIndex: 579,
                  maxLength: 10,
                },
                {
                  xtype: "textfield",
                  hidden: true,
                  id: "cs_height",
                  emptyText: "Height",
                  name: "cs_height",
                  fieldLabel: "Height",
                  width: 100,
                  tabIndex: 560,
                  maxLength: 10,
                },
                {
                  xtype: "numberfield",
                  hidden: true,
                  emptyText: "Weight in grams",
                  id: "cs_weight",
                  name: "cs_weight",
                  tabIndex: 561,
                  width: 100,
                  maxLength: 10,
                },
              ],
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 1,
          labelAlign: "top",
          labelWidth: 120,
          items: [
            {
              xtype: "textfield",
              emptyText: "SKU",
              fieldLabel: "SKU",
              id: "stit_SKU",
              name: "stit_SKU",
              anchor: "75%",
              width: 120,
              tabIndex: 536,
              maxLength: 300,
              allowBlank: false,
            },
          ],
        },
      ],
    });
    return itemPanel;
  };
  var ListMerchantPdtctsPanel = function (id) {
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      id: id,
      title: "Merchant Products",
      items: [merchantGrid(), merchantTabPanel()],
    });
    return panel;
  };
  var itemmasterMerchantStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listMerchantProduct",
        method: "post",
      }),
      fields: [
        "stit_SKU",
        "stit_itemName",
        "stit_HSN_code",
        "stit_category_name",
        "stit_brand_name",
        "stit_quantity",
        "stit_product_variant",
        "least_package_type_name",
        "stit_ID",
        "tpStatus",
        "tpStatusName",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        load: function () {
          //Ext.getCmp('gridpanelThirdPartyProducts').getSelectionModel().selectRow(0);
        },
      },
    });
    store.setDefaultSort("stit_itemName", "ASC");
    return store;
  };
  var merchantGrid =function(){

    var grid_store = itemmasterMerchantStore();
    var customerGrid_filter = new Ext.ux.grid.GridFilters({
      remote: true,
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
          dataIndex: "tpStatusName",
        },
      ],
    });
    customerGrid_filter.remote = true;
    customerGrid_filter.autoReload = true;

    var SP_grid = new Ext.grid.GridPanel({
      store: grid_store,
      id: "gridpanelMerchantProducts",
      region: "center",
      frame: true,
      border: false,
      layout: "fit",
      loadMask: true,
      plugins: [customerGrid_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          sortable: true,
          hideable: true,
          dataIndex: "stit_SKU",
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
          header: "Net Weight",
          sortable: true,
          hideable: true,
          dataIndex: "stit_quantity",
        },
        {
          header: "Status",
          sortable: true,
          hideable: true,
          dataIndex: "tpStatusName",
        },
        {
          dataIndex: " ",
          width: 20,
        },
      ],
      viewConfig: {
        forceFit: true,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      tbar: [],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: grid_store,
        displayInfo: true,
        plugins: [customerGrid_filter],
        displayMsg: "Displaying items {0} - {1} of {2}",
        emptyMsg: "No records to display",
      }),
      stripeRows: true,
      sm: new Ext.grid.RowSelectionModel({
        singleSelected: true,
        listeners: {
          selectionchange: gridSelectionChangedMerchantPrdcts,
        },
      }),
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return SP_grid;
  };
  var gridSelectionChangedMerchantPrdcts =function(sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMerchantProducts")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMerchantProducts")
        .getSelectionModel()
        .getSelections()[0].data.stit_ID;
      var tpStatus = Ext.getCmp("gridpanelMerchantProducts")
        .getSelectionModel()
        .getSelections()[0].data.tpStatus;
      
      Application.ThirdPartyProducts.Cache.stit_ID = ID;
      Application.ThirdPartyProducts.ViewModeMP(ID);
    } else {
      Application.ThirdPartyProducts.Cache.stit_ID = 0;
      Application.ThirdPartyProducts.ViewModeMP(ID);
    }
  };
  var merchantTabPanel = function () {
    var panel = new Ext.Panel({
      region: "east",
      width: winsize.width * 0.5,
      height: winsize.height * 0.6,
      autoScroll: true,
      cls: "left_side_panel",
      plain: true,
      frame: false,
      border: false,
      id: "tabpanelMerchantDetails",
      items: [
        {
          title: "Details",
          layout: "fit",
          id: "merchantPrdtsDeatailsView",
          width: winsize.width * 0.48,
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="99%" class="details_view_table">',
            "<tr><th>SKU :</th><td> {stit_SKU} </td></tr>",
            "<tr><th>Brand:</th><td>  {stit_brand_name}</td></tr>",
            "<tr><th>Product Master:</th><td>  {stit_itemName}</td></tr>",
            "<tr><th>Variant:</th><td>  {stit_product_variant}</td></tr>",
            "<tr><th>Net Wt.:</th><td>  {stit_quantity}</td></tr>",
            "<tr><th>Sub Category:</th><td>  {stit_category_name}</td></tr>",
            "<tr><th>HSN Code:</th><td>  {stit_HSN_code}</td></tr>",
            "<tr><th>GST:</th><td>  {stit_GST}</td></tr>",
            "<tr><th>Return Time:</th><td>  {stit_itemReturnTime}</td></tr>",
            "<tr><th>Country of Orgin:</th><td>  {stit_orgin_countryname}</td></tr>",
            "<tr><th>Least Package Type:</th><td>  {least_package_type_name}</td></tr>",
            "<tr><th>Short Description:</th><td>  {stit_Description}</td></tr>",
            "<tr><th>Long Description:</th><td>  {stit_long_description}</td></tr>",
            '<tpl if="image_urlend != null">',
            "<tpl if=\"image_urlend != ''\">",
            "<tr><td>",
            '<tr><th>Deafult Image:</th><td><div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div></td></tr>',
            "</td></tr>",
            "</tpl>",
            "</tpl>",
            "</table>",
            "</div>"
          ),
        },
      ],
      buttons: [
      ],
    });
    return panel;
  };
  var ListPublicPdtctsPanel = function (id) {
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      id: id,
      title: "Public Products",
      items: [publicGrid(), publicTabPanel()],
    });
    return panel;
  };
  var itemmasterPublicStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listPublicProduct",
        method: "post",
      }),
      fields: [
        "stit_SKU",
        "stit_itemName",
        "stit_HSN_code",
        "stit_category_name",
        "stit_brand_name",
        "stit_quantity",
        "stit_product_variant",
        "least_package_type_name",
        "stit_ID",
        "tpStatus",
        "tpStatusName",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        load: function () {
          //Ext.getCmp('gridpanelThirdPartyProducts').getSelectionModel().selectRow(0);
        },
      },
    });
    store.setDefaultSort("stit_itemName", "ASC");
    return store;
  };
  var publicGrid =function(){

    var grid_store = itemmasterPublicStore();
    var customerGrid_filter = new Ext.ux.grid.GridFilters({
      remote: true,
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
          dataIndex: "tpStatusName",
        },
      ],
    });
    customerGrid_filter.remote = true;
    customerGrid_filter.autoReload = true;

    var SP_grid = new Ext.grid.GridPanel({
      store: grid_store,
      id: "gridpanelPublicProducts",
      region: "center",
      frame: true,
      border: false,
      layout: "fit",
      loadMask: true,
      plugins: [customerGrid_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          sortable: true,
          hideable: true,
          dataIndex: "stit_SKU",
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
          header: "Net Weight",
          sortable: true,
          hideable: true,
          dataIndex: "stit_quantity",
        },
        {
          header: "Status",
          sortable: true,
          hideable: true,
          dataIndex: "tpStatusName",
        },
        {
          dataIndex: " ",
          width: 20,
        },
      ],
      viewConfig: {
        forceFit: true,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      tbar: [],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: grid_store,
        displayInfo: true,
        plugins: [customerGrid_filter],
        displayMsg: "Displaying items {0} - {1} of {2}",
        emptyMsg: "No records to display",
      }),
      stripeRows: true,
      sm: new Ext.grid.RowSelectionModel({
        singleSelected: true,
        listeners: {
          selectionchange: gridSelectionChangedPublicPrdcts,
        },
      }),
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return SP_grid;
  };
  var gridSelectionChangedPublicPrdcts =function(sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelPublicProducts")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelPublicProducts")
        .getSelectionModel()
        .getSelections()[0].data.stit_ID;
      var tpStatus = Ext.getCmp("gridpanelPublicProducts")
        .getSelectionModel()
        .getSelections()[0].data.tpStatus;
      
      Application.ThirdPartyProducts.Cache.stit_ID = ID;
      Application.ThirdPartyProducts.ViewModePP(ID);
    } else {
      Application.ThirdPartyProducts.Cache.stit_ID = 0;
      Application.ThirdPartyProducts.ViewModePP(ID);
    }
  };
  var publicTabPanel = function () {
    var panel = new Ext.Panel({
      region: "east",
      width: winsize.width * 0.5,
      height: winsize.height * 0.6,
      autoScroll: true,
      cls: "left_side_panel",
      plain: true,
      frame: false,
      border: false,
      id: "tabpanelPublicDetails",
      items: [
        {
          title: "Details",
          layout: "fit",
          id: "publicPrdtsDeatailsView",
          width: winsize.width * 0.48,
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="99%" class="details_view_table">',
            "<tr><th>SKU :</th><td> {stit_SKU} </td></tr>",
            "<tr><th>Brand:</th><td>  {stit_brand_name}</td></tr>",
            "<tr><th>Product Master:</th><td>  {stit_itemName}</td></tr>",
            "<tr><th>Variant:</th><td>  {stit_product_variant}</td></tr>",
            "<tr><th>Net Wt.:</th><td>  {stit_quantity}</td></tr>",
            "<tr><th>Sub Category:</th><td>  {stit_category_name}</td></tr>",
            "<tr><th>HSN Code:</th><td>  {stit_HSN_code}</td></tr>",
            "<tr><th>GST:</th><td>  {stit_GST}</td></tr>",
            "<tr><th>Return Time:</th><td>  {stit_itemReturnTime}</td></tr>",
            "<tr><th>Country of Orgin:</th><td>  {stit_orgin_countryname}</td></tr>",
            "<tr><th>Least Package Type:</th><td>  {least_package_type_name}</td></tr>",
            "<tr><th>Short Description:</th><td>  {stit_Description}</td></tr>",
            "<tr><th>Long Description:</th><td>  {stit_long_description}</td></tr>",
            '<tpl if="image_urlend != null">',
            "<tpl if=\"image_urlend != ''\">",
            "<tr><td>",
            '<tr><th>Deafult Image:</th><td><div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div></td></tr>',
            "</td></tr>",
            "</tpl>",
            "</tpl>",
            "</table>",
            "</div>"
          ),
        },
      ],
      buttons: [
      ],
    });
    return panel;
  };
var splashGridStores = function (tpItemId, gsId, productId,mode) {
  var store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
          url: modURL + '&op=listProductimages',
          method: 'post'
      }), reader: new Ext.data.JsonReader({
          totalProperty: 'totalCount',
          idProperty: 'id',
          root: 'data'
      }, ['id','slgalimg_path','imagetype','thumpimg_path','status']),
      sortInfo: {
          field: 'id',
          direction: "DESC"
      },
      groupField: '',
      groupDir: 'ASC',
      autoLoad: true,
      root: 'data',
      listeners:{
        beforeload:function(){
          this.baseParams.tpItemId = tpItemId;
          this.baseParams.productId = productId;
        }
      }
  });
  return store;
};
var gridSelectionChangedImage = function(){
  var ID = Ext.getCmp("splash_grid")
  .getSelectionModel()
  .getSelections()[0].data.id;
  var data = Ext.getCmp("splash_grid")
  .getSelectionModel()
  .getSelections()[0].data;
  //Application.ThirdPartyProducts.WebsiteGalleryViewMode(data)
};
var check_modelImage =function(tpItemId, gsId, productId,mode){
  return new Ext.grid.CheckboxSelectionModel({
    multiSelect: true,
    dataIndex: "checked",
    checkOnly: true,
    listeners: {
      selectionchange: function (selModel) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("splash_grid")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("splash_grid")
            .getSelectionModel()
            .getSelections()[0].data.id;
            var data = Ext.getCmp("splash_grid")
            .getSelectionModel()
            .getSelections()[0].data;
            Application.ThirdPartyProducts.WebsiteGalleryViewMode(data)
        }
      },
      rowdeselect: function (sm, rowIndex, record) {
        record.set("checked", "false");
        
      },
      rowselect: function (sm, rowIndex, record) {
        record.set("checked", "true");
      },
    },
  });
};
var file_grid = function (tpItemId, gsId, productId,mode) {
  var imagechk_model = check_modelImage(tpItemId, gsId, productId,mode);
  var projectFileStore = splashGridStores(tpItemId, gsId, productId,mode);
  var grid = new Ext.grid.GridPanel({
      store: projectFileStore,
      region:'center',
      id: 'splash_grid',
      autoScroll: true,
      view: new Ext.grid.GroupingView({
          deferEmptyText: false,
          emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          forceFit: true,
          groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
      }),
      plugins: [new Ext.ux.grid.GroupSummary()],
      viewConfig: {
          forceFit: true,
          markDirty: false
      },
      stripeRows: true,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedImage,
        },
    }),
      frame: true,
      border: false,
      hideBorders: true,
      columns: [new Ext.grid.RowNumberer(),
          {
              header: 'Name',
              sortable: true,
              dataIndex: 'imagetype',
              hideable: false,
              tooltip: 'Name'                  

          },{
              header: 'Image',
              sortable: true,
              dataIndex: 'thumpimg_path',
              hideable: false,
              tooltip: 'Image',
              renderer: function (value, metadata, record) {                  
                  return '<img src="' + record.data.thumpimg_path + '">';
              }

          }, {
            xtype: 'actioncolumn',
            hideable: false,
            sortable: false,
            items: [{
                    iconCls: '',
                    getClass: function (v, meta, rec) {
                        var data = rec.data;
                        var status = data.status;
                        if (status == 1) {
                            this.items[0].tooltip = 'Deselect to remove';
                            return 'status_enabled';
                        } else {
                            return 'finascop_hideicon';
                        }
                    },
                    handler: function (grid, rowIndex, colIndex) {
                        var record = grid.store.getAt(rowIndex);
                        var id = record.data.id;
                        var imagetype = record.data.imagetype;
                        Ext.Msg.confirm(
                          "Notification",
                          "Do you want to remove the image ?",
                          function (btn) {
                            if (btn == "yes") {
                              Ext.Ajax.request({
                                url: modURL + '&op=changeStatus',
                                method: 'POST',
                                params: {
                                  imagetype: imagetype,
                                  id:id
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        var tmp = Ext.decode(response.responseText);
                                        Application.example.msg('Success', tmp.msg);
                                        Ext.getCmp('splash_grid').getStore().load({
                                          params:{
                                            tpItemId:tpItemId,
                                            productId:productId
                                          }
                                        });
                                    }
                                    else {
                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                    }
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                }
                            });
                            }
                          }
                        );
                        
                    }
                }]
        }],
      tbar: []
  });
  return grid;
};

var detailsPanelfile = function (mode) {
  return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      buttonAlign: 'left',
      loadingText: 'Please Wait',
      bodyStyle: {"padding": "5px 3px"},
      id: 'file_details_view_panelslwebgal' + mode,
      tpl: new Ext.XTemplate('<div class="details-outer">',
              '<tpl if="slgalimg_path != \'\'"><tr><td><img id="preview_imageslwebgal" name="preview_imageslwebgal" src="{slgalimg_path}" width="360px" /> </td></tr></tpl>',
              '<table border="0" width="99%" class="details_view_table">',
              '<tr><th>Title</th><td>{imagetype}</td></tr>',
              '<tr><td> <input type="hidden" id="fileurl" name="fileurl" value="{slgalimg_path}"/></td><tr>',
              '</table>',
              '</div>'),
  });
};
var fnEastPanel = function (mode) {
  var _tplitem = detailsPanelfile(mode);
  var eastPanel = new Ext.Panel({
      title: "Info",
      frame: false,
      border: true,
      region: "east",
      autoScroll: true,
      bodyStyle: {"padding": "5px 3px"},
      width: winsize.width * 0.3,
      height: winsize.height * 0.6,
      buttonAlign: 'right',
      items: [_tplitem],
      id: 'file_details_parent_panelslwebgal' + mode,
  });
  return eastPanel;
};
  return {
    Cache: {},
    initTPPrdcts: function (type) {
      loadCount = 0;
      var panelId = "tpProductsMainPanel";
      var listVendor = Ext.getCmp(panelId);
      if (Ext.isEmpty(listVendor)) {
        listVendor = ListmainTpPdtctsPanel(panelId);
        Application.UI.addTab(listVendor);
        listVendor.doLayout();
      } else {
        Application.UI.addTab(listVendor);
      }
    },
    ViewMode: function (data) {
      var stit_ID = arguments[0];

      Ext.Ajax.request({
        url: modURL + "&op=tpPrdtsDetailsView",
        method: "POST",
        params: { stit_ID: stit_ID },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("tpPrdtsDeatailsView");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("tpPrdtsDeatailsView").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
    },
    convertTPProducts: function () {
      var main_panel_id = "main_panelConvertTPProducts";
      var main_panel = Ext.getCmp(main_panel_id);
      var title = "Product Verification";
      if (Ext.isEmpty(main_panel)) {
        main_panel = tpProductsPanel(main_panel_id, "", title);
        Application.UI.addTab(main_panel);
        main_panel.doLayout();
      } else {
        Application.UI.addTab(main_panel);
      }
    },
    combineProducts: function (tpItemId, gsId, productId,createProduct) {
      //Application.ThirdPartyProducts.Cache.gs1Id = gs1Id;
      if(createProduct == 1){
        var title = "Create Product";
        var savebtnTxt = "Save & Proceed";
      }else{
        var title = "Combine Product";
        var savebtnTxt = "Save & Combine";
      }
      
      var item_form = viewItemPanel();
      var combineItem_window = Ext.getCmp("combineItem_window");
      if (Ext.isEmpty(combineItem_window)) {
        var combineItem_window = new Ext.Window({
          id: "combineItem_window",
          title: title,
          modal: true,
          layout: "fit",
          width: winsize.width * 0.9,
          height: 500,
          resizable: false,
          items: [item_form],
          buttons: [{
            buttonAlign: "right",
              text: "View Images",
              id:"creatProductImage",
              hidden:true,
              tabIndex: 141,
              icon: IMAGE_BASE_PATH + "/default/icons/view.png",
              handler: function () {
                Application.ThirdPartyProducts.viewProducImages(tpItemId, gsId, productId,createProduct);
              },
          },{
              buttonAlign: "left",
              text: "Cancel",
              tabIndex: 140,
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
              handler: function () {
                combineItem_window.close();
              },
            },{
              buttonAlign: "left",
              text: savebtnTxt,
              tabIndex: 139,
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
              handler: function () {
                Application.ThirdPartyProducts.saveAndCombine(
                  tpItemId,
                  gsId,
                  productId,createProduct
                );
              },
            },
          ],
        });
      }
      item_form.load({
        url: modURL + "&op=loadProductBasedOnType",
        params: {
          tpItemId: tpItemId,
          gsId: gsId,
          productId: productId,
          createProduct:createProduct
        },
        success: function (frm, action) {
          var tmp = Ext.decode(action.response.responseText);
          console.log("tmp");
          console.log(tmp);
          if (tmp.success == true) {
            Ext.getCmp("least_package_type_id").getStore().load();
            Ext.getCmp("least_package_type_id").setRawValue(
              tmp.data.least_package_type_name
            );
            Ext.getCmp("ccsb_package_type_id")
              .getStore()
              .load({
                params: {
                  stdpckl11: tmp.data.least_package_type_id,
                  stdpckl12: tmp.data.stit_package_type_id,
                  stdpckl21: tmp.data.stdpckl21_package_type_id,
                  stdpckl31: tmp.data.stdpckl31_package_type_id,
                  stdpckl41: tmp.data.stdpckl41_package_type_id,
                },
              });
            //Ext.getCmp('cosb_package_type_id').getStore().load();
            Ext.getCmp("cosb_package_type_id").setRawValue(
              tmp.data.cosb_package_type_name
            );
            //Ext.getCmp('cos_package_type_id').getStore().load();
            Ext.getCmp("cos_package_type_id").setRawValue(
              tmp.data.cos_package_type_name
            );
            //Ext.getCmp('ccs_package_type_id').getStore().load();
            Ext.getCmp("ccs_package_type_id").setRawValue(
              tmp.data.ccs_package_type_name
            );
            //Ext.getCmp('ccsb_package_type_id').getStore().load();
            Ext.getCmp("ccsb_package_type_id").setRawValue(
              tmp.data.ccsb_package_type_name
            );
            //Ext.getCmp('rs_package_type_id').getStore().load();
            Ext.getCmp("rs_package_type_id").setRawValue(
              tmp.data.rs_package_type_name
            );
            //Ext.getCmp('rsb_package_type_id').getStore().load();
            Ext.getCmp("rsb_package_type_id").setRawValue(
              tmp.data.rsb_package_type_name
            );
            //Ext.getCmp('cs_package_type_id').getStore().load();
            Ext.getCmp("cs_package_type_id").setRawValue(
              tmp.data.cs_package_type_name
            );
            //Ext.getCmp('csb_package_type_id').getStore().load();
            Ext.getCmp("csb_package_type_id").setRawValue(
              tmp.data.csb_package_type_name
            );
            //Ext.getCmp('ds_package_type_id').getStore().load();
            Ext.getCmp("ds_package_type_id").setRawValue(
              tmp.data.ds_package_type_name
            );
            //Ext.getCmp('dsb_package_type_id').getStore().load();
            Ext.getCmp("dsb_package_type_id").setRawValue(
              tmp.data.dsb_package_type_name
            );

            Ext.getCmp("item").getStore().load();
            Ext.getCmp("item").setRawValue(tmp.data.stit_itemName);
            Ext.getCmp("stit_orgin_country").getStore().load();
            Ext.getCmp("stit_orgin_country").setRawValue(
              tmp.data.orgCountryName
            );
            Ext.getCmp("stit_orgin_country").setHideTrigger(true);
            Ext.getCmp("pdt_brand").getStore().load();
            Ext.getCmp("pdt_brand").setRawValue(tmp.data.stit_brand_name);
            Ext.getCmp("pdt_brand").setHideTrigger(true);
            Ext.getCmp("product_category").getStore().load();
            Ext.getCmp("product_category").setRawValue(
              tmp.data.stit_category_name
            );
            Ext.getCmp("product_category").setHideTrigger(true);
            Ext.getCmp("HSN").getStore().load();
            Ext.getCmp("taxValueId").getStore().baseParams.hsnId = tmp.data.stit_hsnId;
            Ext.getCmp("taxValueId").getStore().load();
            Ext.getCmp("taxValueId").setValue(tmp.data.taxValueId);
            Ext.getCmp("taxValueId").setRawValue(tmp.data.GST);
            Ext.getCmp("HSN").setRawValue(tmp.data.stit_HSN_code);
            Ext.getCmp("stit_long_description").setValue(
              tmp.data.stit_long_description
            );

            Ext.getCmp("stit_package_master").getStore().load();
            Ext.getCmp("stit_package_master").setRawValue(tmp.data.rpckm_name);

            var product_category = Ext.getCmp("product_category").getValue();
            if (
              tmp.data.stit_package_type_id > 0 &&
              tmp.data.least_package_type_id > 0 &&
              tmp.data.stit_package_type_id == tmp.data.least_package_type_id
            ) {
              Ext.getCmp("astdpck0").disable();
            } else {
              Ext.getCmp("astdpck0").enable();
            }
            if (product_category > 0) {
              Ext.Ajax.request({
                url: modURL + "&op=getItemCategory",
                method: "POST",
                params: { sub_category_id: product_category },
                success: function (res) {
                  var tmp = Ext.decode(res.responseText);
                  Ext.getCmp("iteParentCategory").show();
                  Ext.getCmp("iteParentCategory").setValue(
                    tmp.categoryCombination
                  );
                  if(tmp.isPerishable == 1){
                    Ext.getCmp('courierDelivery').disable();
                  }else{
                    Ext.getCmp('courierDelivery').enable();
                  }
                  // Ext.getCmp('iteMidCategory').setValue(tmp.iteMidCategory);
                },
                failure: function () {
                  Ext.MessageBox.alert(
                    "Error",
                    "Error occured while sending data"
                  );
                },
              });
            }

            if (tmp.data.stdpckl1_nos > 0) {
              Application.MyphaProduct.Cache.aspLevel = 0;
            }
            if (tmp.data.stdpckl2_nos > 0) {
              Application.MyphaProduct.Cache.aspLevel = 1;
              Ext.getCmp("astdpck1").show();
            }
            if (tmp.data.stdpckl3_nos > 0) {
              Application.MyphaProduct.Cache.aspLevel = 2;
              Ext.getCmp("astdpck2").show();
            }
            if (tmp.data.stdpckl4_nos > 0) {
              Application.MyphaProduct.Cache.aspLevel = 3;
              Ext.getCmp("astdpck3").show();
            }
            //Ext.getCmp("PrdctVerify").show();
            if(createProduct == 0){
              Application.ThirdPartyProducts.Cache.existing = "Existing : " + tmp.data.description;
              Application.ThirdPartyProducts.Cache.tpdesc = "TP : " + tmp.data.tpDescription;
              Application.ThirdPartyProducts.Cache.existingLong = "Existing : " + tmp.data.stit_long_description;
              Application.ThirdPartyProducts.Cache.tpdescLong =  "TP : " + tmp.data.tpLongDescription;

            
            }else{
              Application.ThirdPartyProducts.Cache.existing = tmp.data.description;
              Application.ThirdPartyProducts.Cache.tpdesc = '';
              Application.ThirdPartyProducts.Cache.existingLong = tmp.data.stit_long_description;
              Application.ThirdPartyProducts.Cache.tpdescLong = '';
            }

            Application.ThirdPartyProducts.Cache.descCombine =
              Application.ThirdPartyProducts.Cache.existing +
              "\n\n" +
              Application.ThirdPartyProducts.Cache.tpdesc;

              Application.ThirdPartyProducts.Cache.descCombineLong =
              Application.ThirdPartyProducts.Cache.existingLong +
              "\n\n" +
              Application.ThirdPartyProducts.Cache.tpdescLong;

              Ext.getCmp("description").setValue(
                Application.ThirdPartyProducts.Cache.descCombine
              );
            Ext.getCmp("stit_long_description").setValue(
              Application.ThirdPartyProducts.Cache.descCombineLong
            );
            if(tmp.data.imageCount > 0){
              Ext.getCmp('creatProductImage').show();
            }else{
              Ext.getCmp('creatProductImage').hide();
            }
            
          }
        },
      });

      combineItem_window.show();
      combineItem_window.doLayout();
      combineItem_window.center();
    },
    saveAndCombine: function (tpItemId, gsId, productId,createProduct) {
      var itemId = Ext.getCmp("itemId").getValue();

      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var is_featured = Ext.getCmp("featured").getValue();
      var is_popular = Ext.getCmp("popular").getValue();
      var is_courierDelivery = Ext.getCmp("courierDelivery").getValue();
      var is_directDelivery = Ext.getCmp("directDelivery").getValue();
      var isRRPApplicable = Ext.getCmp("isRRPApplicable").getValue();
      var isdirectPurchase = Ext.getCmp("directPurchase").getValue();
      var isstit_custInitiate = Ext.getCmp("stit_custInitiate").getValue();
      var featured = is_featured === true ? "1" : "0";
      var popular = is_popular === true ? "1" : "0";
      var courierDelivery = is_courierDelivery === true ? "1" : "0";
      var directDelivery = is_directDelivery === true ? "1" : "0";
      var isRRPApplicable = isRRPApplicable === true ? "1" : "0";
      var stit_custInitiate = isstit_custInitiate === true ? "1" : "0";
      var directPurchase = isdirectPurchase === true ? "1" : "0";
      var is_stdPacking = Ext.getCmp("stit_stdPacking").getValue();
      var is_salesUnit = Ext.getCmp("stit_salesUnit").getValue();
      var stit_stdPacking = is_stdPacking === true ? "1" : "0";
      var stit_salesUnit = is_salesUnit === true ? "1" : "0";
      if (Ext.getCmp("stit_long_description").getValue().length <= 7000) {
        if (Ext.getCmp("itemPanel").getForm().isValid()) {
          Ext.Ajax.request({
            url: modURL + "&op=saveAndCombineProduct",
            method: "POST",
            params: {
              createProduct:createProduct,
              tpItemId: tpItemId,
              gsId: gsId,
              productId: productId,
              stit_stdPacking: stit_stdPacking,
              stit_salesUnit: stit_salesUnit,
              stit_package_type_id: Ext.getCmp(
                "stit_package_type_id"
              ).getValue(),
              stdpckl11_package_type_id: Ext.getCmp(
                "stdpckl11_package_type_id"
              ).getValue(),
              stdpckl1_nos: Ext.getCmp("stdpckl1_nos").getValue(),
              stdpckl12_package_type_id: Ext.getCmp(
                "stdpckl12_package_type_id"
              ).getValue(),
              stdpckl21_package_type_id: Ext.getCmp(
                "stdpckl21_package_type_id"
              ).getValue(),
              stdpckl2_nos: Ext.getCmp("stdpckl2_nos").getValue(),
              stdpckl22_package_type_id: Ext.getCmp(
                "stdpckl22_package_type_id"
              ).getValue(),
              stdpckl31_package_type_id: Ext.getCmp(
                "stdpckl31_package_type_id"
              ).getValue(),
              stdpckl3_nos: Ext.getCmp("stdpckl3_nos").getValue(),
              stdpckl32_package_type_id: Ext.getCmp(
                "stdpckl32_package_type_id"
              ).getValue(),
              stdpckl41_package_type_id: Ext.getCmp(
                "stdpckl41_package_type_id"
              ).getValue(),
              stdpckl4_nos: Ext.getCmp("stdpckl4_nos").getValue(),
              stdpckl42_package_type_id: Ext.getCmp(
                "stdpckl42_package_type_id"
              ).getValue(),
              dupitem: Application.MyphaProduct.Cache.dup,
              stit_SKU: Ext.getCmp("stit_SKU").getValue(),
              item_name: Ext.getCmp("item").getRawValue(),
              stit_itemERPId: Ext.getCmp("stit_itemERPId").getValue(),
              stit_itemBarcode: Ext.getCmp("stit_itemBarcode").getValue(),
              stit_itemReturnTime: Ext.getCmp("stit_itemReturnTime").getValue(),
              stit_package_master: Ext.getCmp("stit_package_master").getValue(),
              stit_courierWt: Ext.getCmp("stit_courierWt").getValue(),
              HSN_code: Ext.getCmp("HSN").getRawValue(),
              stit_category_name: Ext.getCmp("product_category").getRawValue(),
              stit_brand_name: Ext.getCmp("pdt_brand").getRawValue(),
              item: Ext.getCmp("item").getValue(),
              id: Ext.getCmp("itemId").getValue(),
              HSN: Ext.getCmp("HSN").getValue(),
              display_label: Ext.getCmp("stit_displaylabel").getValue(),
              GST: Ext.getCmp("GST").getValue(),
              taxValueId:Ext.getCmp("taxValueId").getValue(),
              MRP: Ext.getCmp("MRP").getValue(),
              itemgroup: Ext.getCmp("itemgroup").getValue(),
              description: Ext.getCmp("description").getValue(),
              stit_product_variant: Ext.getCmp(
                "stit_product_variant"
              ).getValue(),
              product_category: Ext.getCmp("product_category").getValue(),
              pdt_brand: Ext.getCmp("pdt_brand").getValue(),
              featured: featured,
              popular: popular,
              stit_custInitiate: stit_custInitiate,
              stit_long_description: Ext.getCmp(
                "stit_long_description"
              ).getValue(),
              stit_quantity: Ext.getCmp("stit_quantity").getValue(),
              apikey: _SESSION.apikey,
              least_package_type_id: Ext.getCmp(
                "least_package_type_id"
              ).getValue(),
              least_package_type_name: Ext.getCmp(
                "least_package_type_id"
              ).getRawValue(),
              stitl1_optimumqty: Ext.getCmp("stitl1_optimumqty").getValue(),
              stitl2_optimumqty: Ext.getCmp("stitl2_optimumqty").getValue(),
              stitl3_optimumqty: Ext.getCmp("stitl3_optimumqty").getValue(),
              stit11_minimumqty: Ext.getCmp("stit11_minimumqty").getValue(),
              stit12_minimumqty: Ext.getCmp("stit12_minimumqty").getValue(),
              stit13_minimumqty: Ext.getCmp("stit13_minimumqty").getValue(),
              stit11_maximumqty: Ext.getCmp("stit11_maximumqty").getValue(),
              stit12_maximumqty: Ext.getCmp("stit12_maximumqty").getValue(),
              stit13_maximumqty: Ext.getCmp("stit13_maximumqty").getValue(),
              stii_csb: Ext.getCmp("stii_csb").getValue(),
              cos_nos: Ext.getCmp("cos_nos").getValue(),
              cos_package_type_id: Ext.getCmp("cos_package_type_id").getValue(),
              cos_package_type_name: Ext.getCmp(
                "cos_package_type_id"
              ).getRawValue(),
              cosb_package_type_id: Ext.getCmp(
                "cosb_package_type_id"
              ).getValue(),
              cosb_package_type_name: Ext.getCmp(
                "cosb_package_type_id"
              ).getRawValue(),
              cos_length: Ext.getCmp("cos_length").getValue(),
              cos_breadth: Ext.getCmp("cos_breadth").getValue(),
              cos_height: Ext.getCmp("cos_height").getValue(),
              cos_weight: Ext.getCmp("cos_weight").getValue(),
              cos_volume:
                Ext.getCmp("cos_length").getValue() *
                Ext.getCmp("cos_breadth").getValue() *
                Ext.getCmp("cos_height").getValue(),
              ccs_nos: Ext.getCmp("ccs_nos").getValue(),
              ccs_package_type_id: Ext.getCmp("ccs_package_type_id").getValue(),
              ccs_package_type_name: Ext.getCmp(
                "ccs_package_type_id"
              ).getRawValue(),
              ccsb_package_type_id: Ext.getCmp(
                "ccsb_package_type_id"
              ).getValue(),
              ccsb_package_type_name: Ext.getCmp(
                "ccsb_package_type_id"
              ).getRawValue(),
              ccs_length: Ext.getCmp("ccs_length").getValue(),
              ccs_breadth: Ext.getCmp("ccs_breadth").getValue(),
              ccs_height: Ext.getCmp("ccs_height").getValue(),
              ccs_weight: Ext.getCmp("ccs_weight").getValue(),
              ccs_volume:
                Ext.getCmp("ccs_length").getValue() *
                Ext.getCmp("ccs_breadth").getValue() *
                Ext.getCmp("ccs_height").getValue(),
              rs_nos: Ext.getCmp("rs_nos").getValue(),
              rs_package_type_id: Ext.getCmp("rs_package_type_id").getValue(),
              rs_package_type_name:
                Ext.getCmp("rs_package_type_id").getRawValue(),
              rsb_package_type_id: Ext.getCmp("rsb_package_type_id").getValue(),
              rsb_package_type_name: Ext.getCmp(
                "rsb_package_type_id"
              ).getRawValue(),
              rs_length: Ext.getCmp("rs_length").getValue(),
              rs_breadth: Ext.getCmp("rs_breadth").getValue(),
              rs_height: Ext.getCmp("rs_height").getValue(),
              rs_weight: Ext.getCmp("rs_weight").getValue(),
              rs_volume:
                Ext.getCmp("rs_length").getValue() *
                Ext.getCmp("rs_breadth").getValue() *
                Ext.getCmp("rs_height").getValue(),
              cs_nos: Ext.getCmp("cs_nos").getValue(),
              cs_package_type_id: Ext.getCmp("cs_package_type_id").getValue(),
              cs_package_type_name:
                Ext.getCmp("cs_package_type_id").getRawValue(),
              csb_package_type_id: Ext.getCmp("csb_package_type_id").getValue(),
              csb_package_type_name: Ext.getCmp(
                "csb_package_type_id"
              ).getRawValue(),
              cs_length: Ext.getCmp("cs_length").getValue(),
              cs_breadth: Ext.getCmp("cs_breadth").getValue(),
              cs_height: Ext.getCmp("cs_height").getValue(),
              cs_weight: Ext.getCmp("cs_weight").getValue(),
              cs_volume:
                Ext.getCmp("cs_length").getValue() *
                Ext.getCmp("cs_breadth").getValue() *
                Ext.getCmp("cs_height").getValue(),
              ds_nos: Ext.getCmp("ds_nos").getValue(),
              ds_package_type_id: Ext.getCmp("ds_package_type_id").getValue(),
              ds_package_type_name:
                Ext.getCmp("ds_package_type_id").getRawValue(),
              dsb_package_type_id: Ext.getCmp("dsb_package_type_id").getValue(),
              dsb_package_type_name: Ext.getCmp(
                "dsb_package_type_id"
              ).getRawValue(),
              ds_length: Ext.getCmp("ds_length").getValue(),
              ds_breadth: Ext.getCmp("ds_breadth").getValue(),
              ds_height: Ext.getCmp("ds_height").getValue(),
              ds_weight: Ext.getCmp("ds_weight").getValue(),
              ds_volume:
                Ext.getCmp("ds_length").getValue() *
                Ext.getCmp("ds_breadth").getValue() *
                Ext.getCmp("ds_height").getValue(),
              stii_csbretail: Ext.getCmp("stii_csbretail").getValue(),
              courierDelivery: courierDelivery,
              directDelivery: directDelivery,
              isRRPApplicable: isRRPApplicable,
              directPurchase: directPurchase,
              stit_foodtype: Ext.getCmp("stit_foodtype").getValue(),
              stit_orgin_country: Ext.getCmp("stit_orgin_country").getValue(),
              stit_unit: Ext.getCmp("stit_unit").getValue(),
              stit_qty: Ext.getCmp("stit_qty").getValue(),
              item_length: Ext.getCmp("item_length").getValue(),
              item_breadth: Ext.getCmp("item_breadth").getValue(),
              item_height: Ext.getCmp("item_height").getValue(),
              tstamp: t_stamp,
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);

              if (tmp.success === true) {
                Application.example.msg("Notification", tmp.msg);
                Ext.getCmp("combineItem_window").close();
                if(tmp.toExport == 1){
                  Ext.Ajax.request({
                    url: promodURL + "&op=exportDataToParentDB",
                    method: "POST",
                    waitMsg: "Processing",
                    params: {
                      ItemId: tmp.stit_ID,
                      stit_SKU: '',
                      tpProduct:1
                    },
                    failure: function (response, options) {
                      var tmp = Ext.util.JSON.decode(response.responseText);
                      Ext.MessageBox.alert("Error", "tmp.msg");
                    },
                    success: function (response, options) {
                      var tmp = Ext.decode(response.responseText);
            
                      if (tmp.success === true) {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                      } else {
                        Ext.MessageBox.alert("Error", tmp.msg);
                      }
                    },
                  });
                }
                Ext.getCmp("tp_mapping_grid").getStore().removeAll();
              Ext.getCmp("tp_mapping_grid").getStore().baseParams.brand_id =
                Ext.getCmp("search_brand").getValue();
              Ext.getCmp("tp_mapping_grid")
                .getStore()
                .load();
              } else {
                console.log('success else');
                var errorMsg;
                if(!Ext.isEmpty(tmp.msg))
                errorMsg = tmp.msg;
                else 
                errorMsg = tmp.error;
                Ext.MessageBox.alert("Error", errorMsg, function() {
                  Ext.getCmp("tp_mapping_grid").getStore().removeAll();
              Ext.getCmp("tp_mapping_grid").getStore().baseParams.brand_id =
                Ext.getCmp("search_brand").getValue();
              Ext.getCmp("tp_mapping_grid")
                .getStore()
                .load();
              });
              
              // Close the alert after a delay (e.g., 3000 ms = 3 seconds)
              Ext.defer(function() {
                  Ext.MessageBox.hide();
              }, 30000);
                //itemMaster_window.close();
              }
              
            },
            failure: function (response) {
              console.log('failure');
              var tmp = Ext.util.JSON.decode(response.responseText);
              var errorMsg = tmp.msg + tmp.error;
              Ext.MessageBox.alert("Error", errorMsg, function() {
                // Optionally, you can add a callback function here if needed.
            });
            
            // Close the alert after a delay (e.g., 3000 ms = 3 seconds)
            Ext.defer(function() {
                Ext.MessageBox.hide();
            }, 30000);
            },
          });
        } else {
          Ext.MessageBox.alert(
            "Notification",
            "Please enter all required fields"
          );
        }
      } else {
        Ext.MessageBox.alert(
          "Error",
          "Long Description exceeds 7000 characters"
        );
      }
    },initMerchantPrdcts: function (type) {
      loadCount = 0;
      var panelId = "merchantProductsMainPanel";
      var listVendor = Ext.getCmp(panelId);
      if (Ext.isEmpty(listVendor)) {
        listVendor = ListMerchantPdtctsPanel(panelId);
        Application.UI.addTab(listVendor);
        listVendor.doLayout();
      } else {
        Application.UI.addTab(listVendor);
      }
    },
    ViewModeMP: function (data) {
      var stit_ID = arguments[0];

      Ext.Ajax.request({
        url: modURL + "&op=tpPrdtsDetailsView",
        method: "POST",
        params: { stit_ID: stit_ID },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("merchantPrdtsDeatailsView");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("merchantPrdtsDeatailsView").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
    },viewProducImages:function(tpItemId, gsId, productId,createProduct){
      var mode = "Image";
      var _addnewItemsWindow = new Ext.Window({
        title: "Image Details",
        layout: "fit",
        height: winsize.height * 0.6,
        width: winsize.width * 0.8,
        resizable: true,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [file_grid(tpItemId, gsId, productId,mode)],//fnEastPanel(mode)
        fbar: [],
      });
      _addnewItemsWindow.doLayout();
      _addnewItemsWindow.show();
      _addnewItemsWindow.center();
    },WebsiteGalleryViewMode: function (data) {
      Ext.getCmp("file_details_view_panelslwebgalImage").show();
      var visualsDescPanel = Ext.getCmp("file_details_view_panelslwebgalImage");
      visualsDescPanel.update(data);
      Ext.getCmp("file_details_parent_panelslwebgalImage").doLayout();
      
  },initPublicPrdcts: function (type) {
    loadCount = 0;
    var panelId = "publicProductsMainPanel";
    var listVendor = Ext.getCmp(panelId);
    if (Ext.isEmpty(listVendor)) {
      listVendor = ListPublicPdtctsPanel(panelId);
      Application.UI.addTab(listVendor);
      listVendor.doLayout();
    } else {
      Application.UI.addTab(listVendor);
    }
  },ViewModePP: function (data) {
    var stit_ID = arguments[0];

    Ext.Ajax.request({
      url: modURL + "&op=tpPrdtsDetailsView",
      method: "POST",
      params: { stit_ID: stit_ID },
      success: function (res) {
        var tmp = Ext.decode(res.responseText);
        if (tmp.success === true) {
          var visualsDescPanel = Ext.getCmp("publicPrdtsDeatailsView");
          visualsDescPanel.update(tmp);
        }
        Ext.getCmp("publicPrdtsDeatailsView").doLayout();
      },
      failure: function () {
        Ext.MessageBox.alert("Error", "Error occured while sending data");
      },
    });
  }
  };
})();
