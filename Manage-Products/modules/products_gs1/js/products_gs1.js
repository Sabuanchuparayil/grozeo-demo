Application.GS1Products = (function () {
  var recs_per_page = 18;
  var modURL = "?module=products_gs1";
  var modbmURL = "?module=product_bank_master";
  var modProURL = "?module=mypha_product";
  var uplmodURL = "?module=product_bank_upload";
  var current_type;
  var winLoadMask;
  var winsize = Ext.getBody().getViewSize();
  var loadCount;
  var rowno = new Ext.grid.RowNumberer();
  rowno.width = 30;
  function updatePagination(cmp) {
    recs_per_page = update_recs_per_page(cmp);
  }
  var check_box = new Ext.grid.CheckboxSelectionModel({
    multiSelect: true,
    checkOnly: true,
    listeners: {},
  });
  var venderItemColmodel = function () {
    var colmodel = new Ext.grid.ColumnModel({
      sortable: true,
      columns: [
        check_box,
        rowno,
        { header: "Name", width: 200, dataIndex: "name", sortable: true },
        { header: "GTIN", dataIndex: "gtin", sortable: true, hideable: true },
        { header: "Description", dataIndex: "description", sortable: true },
        { header: "Brand",dataIndex: "brand",hideable: true,sortable: true },
        { header: "Source Category",dataIndex: "category",hidden: true,sortable: true },
        { header: "Source Sub Category",dataIndex: "sub_category",hidden: true,sortable: true },
        { header: "Retail Category", dataIndex: "retailCategoryName", sortable: true, hideable: true },
        { header: "Department", dataIndex: "departmentName", sortable: true, hideable: true },
        { header: "Category", dataIndex: "categoryName", sortable: true, hideable: true },
        { header: "Sub Category", dataIndex: "subCategName", sortable: true, hideable: true },
        { header: "HSN", dataIndex: "hs_code", sortable: true, hideable: true },
        { header: "GST", dataIndex: "igst", sortable: true, hideable: true },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          sortable: false,
          groupable: false,
          tooltip: "Action",
          items: [
            {
              icon: IMAGE_BASE_PATH + "/default/icons/Forward.png",
              tooltip: "Map Products",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Application.ProductBankUpload.mapUnAvailProducts(record.get("id"),record.get("brand"),record.get("name"));
                var catbuttonToClick = Ext.getCmp('catProductSerach');
                catbuttonToClick.handler.call(catbuttonToClick, catbuttonToClick, null);
              },
            },
            {
              iconCls: "search_btn",
              tooltip: "Search GTIN",
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.getStore().getAt(rowIndex);
                var gtinValue = record.get("gtin");
                window.open("http://google.com/search?q=" + gtinValue);
                //window.open("http://www.digit-eyes.com/cgi-bin/digiteyes.cgi?upcCode="+gtinValue+"&action=lookupUpc&go=Go%21");
              },
            },
            {
              tooltip: "Show Image",
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var isImage = 0;
                console.log(data);

                var image_front = data.image_front;
                console.log(image_front);
                var image_back = data.image_back;
                console.log(image_back);
                var image_top = data.image_top;
                console.log(image_top);
                var image_bottom = data.image_bottom;
                var image_left = data.image_left;
                var image_right = data.image_right;
                var image_top_left = data.image_top_left;
                var image_top_right = data.image_top_right;
                if (image_front !== "" && typeof image_front !== "undefined") {
                  isImage++;
                }
                if (image_back !== "" && typeof image_back !== "undefined") {
                  isImage++;
                }
                if (image_top !== "" && typeof image_top !== "undefined") {
                  isImage++;
                }
                if (
                  image_bottom !== "" &&
                  typeof image_bottom !== "undefined"
                ) {
                  isImage++;
                }
                if (image_left !== "" && typeof image_left !== "undefined") {
                  isImage++;
                }
                if (image_right !== "" && typeof image_right !== "undefined") {
                  isImage++;
                }
                if (
                  image_top_left !== "" &&
                  typeof image_top_left !== "undefined"
                ) {
                  isImage++;
                }
                if (
                  image_top_right !== "" &&
                  typeof image_top_right !== "undefined"
                ) {
                  isImage++;
                }
                console.log(isImage);
                if (isImage > 0) {
                  return "camera";
                } else {
                  return "finascop_hideicon";
                }
              },
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                var gs1Id = record.get("id");
                var gtin = record.get("gtin");
                var image_front = record.get("image_front");
                var image_back = record.get("image_back");
                var image_top = record.get("image_top");
                var image_bottom = record.get("image_bottom");
                var image_left = record.get("image_left");
                var image_right = record.get("image_right");
                var image_top_left = record.get("image_top_left");
                var image_top_right = record.get("image_top_right");

                showImages(gs1Id, 0,gtin);
              },
            },
          ],
        },
      ],
    });
    return colmodel;
  };
  var prdctSearchStore = function () {
    var store = new Ext.data.JsonStore({
      method: "POST",
      url: modURL + "&op=gs1ProdctListing",
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
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
        "image_front",
        "image_back",
        "image_top",
        "image_bottom",
        "image_left",
        "image_right",
        "image_top_left",
        "image_top_right",
      ],
      listeners: {
        beforeload: function (thisStore, options) {
          thisStore.baseParams.current_type = current_type;
        },
        load: function (store, records, options) {
          var storeCount = store.data.length;
          storeCount = storeCount + " Products";
          //Ext.getCmp('audit_receipt_total_amount').setValue(storeCount);
        },
      },
    });

    return store;
  };
  var gs1SkipStatusComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getSkipStatus",
      method: "post",
      autoLoad: true,
      fields: ["id", "name"],
      root: "data",
    });
    return store;
  };
  var gs1BrandAllComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getEnabledBrands",
      method: "post",
      autoLoad: true,
      fields: ["id", "brandName"],
      root: "data",
    });
    return store;
  };
  var gs1BrandHsnComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getEnabledBrandsforHsn",
      method: "post",
      autoLoad: true,
      fields: ["id", "brandName"],
      root: "data",
    });
    return store;
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
  var masterBrandComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getMasterBrands",
      method: "post",
      autoLoad: true,
      fields: ["brand_id", "brand_name"],
      root: "data",
    });
    return store;
  };
  var masterItemComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getMasterItems",
      method: "post",
      autoLoad: true,
      fields: ["itemname_id", "item_name"],
      root: "data",
    });
    return store;
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
  var filterItems = function (brand, category, subcategory, archive,merchantId) {
    var gridvalue = Ext.getCmp(
      "gridFinascopStockVenderitemGridgeneration"
    ).getStore();
    gridvalue.baseParams = {
      brand: brand,
      category: category,
      subcategory: subcategory,
      archive: archive,
      merchantId:merchantId
    };
    gridvalue.load();
  };
  var merchantStore = function () {
    var merchant_store = new Ext.data.JsonStore({
      url: uplmodURL + "&op=getMerchantStore",
      fields: ["id", "name"],
      totalProperty: "totalCount",
      root: "data",
      autoload: true,
      remoteFilter: true,
      listeners: {},
    });
    return merchant_store;
  };
  var prdctSearchtb = function () {
    var gs1BrandStore = gs1BrandAllComboStore();
    var gs1CategoryStore = gs1CategoryComboStore();
    var gs1SubCategoryStore = gs1SubCategoryComboStore();
    var tbar = new Ext.Toolbar({
      style: "margin:5px 1px 5px 1px;",
      labelAlign: "left",
      frame: false,
      border: false,
      hideBorders: true,
      items: [
        {
          hidden: true,
          xtype: "combo",
          store: gs1CategoryStore,
          mode: "local",
          id: "gs1FilterCategory",
          allowBlank: true,
          fieldLabel: "Category",
          hiddenName: "n[gs1FilterCategory]",
          displayField: "categoryName",
          valueField: "id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function () {
              var value = Ext.getCmp("gs1FilterCategory").getValue();
              filterItems("", value, "", 0,"");
              //var value = Ext.getCmp("primary_businessType").getValue();
              gs1SubCategoryStore.baseParams.category = this.value;
              gs1SubCategoryStore.load();
            },
          },
        },
        {
          hidden: true,
          xtype: "combo",
          store: gs1SubCategoryStore,
          mode: "local",
          id: "gs1FilterSubCategory",
          allowBlank: true,
          fieldLabel: "Sub Category",
          hiddenName: "n[gs1FilterSubCategory]",
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
              var value = Ext.getCmp("gs1FilterSubCategory").getValue();
              filterItems("", "", value, "",0,"");
              gs1BrandStore.baseParams.category =
                Ext.getCmp("gs1FilterCategory").getValue();
              gs1BrandStore.baseParams.subcategory = this.value;
              gs1BrandStore.load();
            },
          },
        },
        {
          frame: false,
          border: false,
        },
        { html: " Retailer: " },
        {
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
                  listeners: {},
                },{
          frame: false,
          border: false,
        },
        { html: " Brand: " },
        {
          xtype: "combo",
          store: gs1BrandStore,
          mode: "local",
          id: "gs1FilterBrand",
          allowBlank: true,
          fieldLabel: "Brand",
          hiddenName: "n[gs1FilterBrand]",
          displayField: "brandName",
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
            select: function () {
              var value = Ext.getCmp("gs1FilterBrand").getValue();
              var sgId = Ext.getCmp("merchantId").getValue();
              if (Ext.getCmp("showBaseArchived").getValue() == true) {
                var archive = 1;
              } else {
                var archive = 0;
              }
              filterItems(value, "", "", "", archive,sgId);
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
          id:"catProductSerach",
          tabIndex: 37,
          handler: function () {
            var brand = Ext.getCmp("gs1FilterBrand").getValue();
            var category = Ext.getCmp("gs1FilterCategory").getValue();
            var subcategory = Ext.getCmp("gs1FilterSubCategory").getValue();
            var sgId = Ext.getCmp("merchantId").getValue();
            if (Ext.getCmp("showBaseArchived").getValue() == true) {
              var archive = 1;
            } else {
              var archive = 0;
            }
            if (sgId > 0 || brand > 0) {
              filterItems(brand, category, subcategory, archive,sgId);

              /*Ext.Ajax.request({
                url: modURL + "&op=checkReconcile",
                method: "post",
                params: { brand: brand },
                success: function (resp) {
                  var res = Ext.decode(resp.responseText);
                  if (res.success == true && res.valid == true) {
                    
                  } else {
                    Ext.MessageBox.alert(
                      "Notification",
                      "Brand is not reconciled yet - "+res.prefix
                    );
                  }
                },
              });*/
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Choose brand/Retailer before search"
              );
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
          tabIndex: 38,
          handler: function () {
            Ext.getCmp("gs1FilterBrand").reset();
            Ext.getCmp("gs1FilterCategory").reset();
            Ext.getCmp("gs1FilterSubCategory").reset();
            Ext.getCmp("merchantId").reset();
            filterItems(0, 0, 0, 0,0);
          },
        },
        "->",
        {
          xtype: "button",
          text: "Skip",
          icon: IMAGE_BASE_PATH + "/default/icons/arrow_right.png",
          handler: function () {
            var selectitem = Ext.getCmp(
              "gridFinascopStockVenderitemGridgeneration"
            )
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemarr = [];
            for (var i = 0; i < selectedcount; i++) {
              itemarr.push(selectitem[i].data.id);
            }
            if (selectedcount != 0) {
              //Application.GS1Products.Cache.cid = cid;
              Application.GS1Products.Cache.itemarr = itemarr;
              Application.GS1Products.archiveCheckedItem(itemarr);
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please select a product and proceed.."
              );
            }
          },
        },
        {
          frame: false,
          border: false,
        },
        {
          xtype: "checkbox",
          checked: false,
          id: "showBaseArchived",
          name: "showBaseArchived",
          inputValue: 1,
          boxLabel: "Show Skipped",
          listeners: {
            check: function (cb1, checked) {
              var brand = Ext.getCmp("gs1FilterBrand").getValue();
              var category = Ext.getCmp("gs1FilterCategory").getValue();
              var subcategory = Ext.getCmp("gs1FilterSubCategory").getValue();
              var sgId = Ext.getCmp("merchantId").getValue();
              if (checked == true) {
                filterItems(brand, category, subcategory, 1,sgId);
              } else {
                filterItems(brand, category, subcategory, 0,sgId);
              }
            },
          },
        },
        {
          frame: false,
          border: false,
        },
        {
          hidden: true,
          xtype: "button",
          text: "Related Brands",
          tabIndex: 39,
          handler: function () {
            var category = Ext.getCmp("gs1FilterCategory").getValue();
            var subcategory = Ext.getCmp("gs1FilterSubCategory").getValue();
            if (category > 0 && subcategory > 0) {
              loadRelatedBrands(category, subcategory);
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Choose category and sub category before search"
              );
            }
          },
        },
      ],
    });
    return tbar;
  };
  var loadRelatedBrands = function (category, subcategory) {
    var relatedBrandWindowid = Ext.getCmp("relatedBrandWindow");
    if (Ext.isEmpty(relatedBrandWindowid)) {
      relatedBrandWindowid = new Ext.Window({
        id: "relatedBrandWindow",
        title: "Imported Brands",
        modal: true,
        height: 500,
        width: winsize.width * 0.4,
        shadow: false,
        resizable: false,
        items: [relatedBrandGrid(category, subcategory)],
        buttons: [
          {
            text: "Cancel",
            iconCls: "my-icon61",
            handler: function () {
              relatedBrandWindowid.close();
            },
          },
        ],
        listeners: {
          close: function () {},
        },
      });
    }
    relatedBrandWindowid.doLayout();
    relatedBrandWindowid.show();
    relatedBrandWindowid.center();
  };
  var relatedBrandStore = function (category, subcategory) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listDistinctBrands",
        method: "post",
      }),
      fields: ["brandId", "brand"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.category = category;
          this.baseParams.subcategory = subcategory;
        },
      },
    });
    return _Store;
  };
  var relatedBrandGrid = function (category, subcategory) {
    var relatedBrand_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "brand",
        },
      ],
    });
    relatedBrand_filter.remote = true;
    relatedBrand_filter.autoReload = true;
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      height: 500,
      width: winsize.width * 0.4,
      store: relatedBrandStore(category, subcategory),
      //iconCls: 'money',
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforRelatedBrands",
      plugins: [relatedBrand_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brands",
          dataIndex: "brand",
          sortable: true,
          tooltip: "Brands",
          hideable: false,
          width: 200,
        },
        { width: 20, dataIndex: " " },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("gridpanelforRelatedBrands")
            .getStore()
            .load({
              params: {
                category: category,
                subcategory: subcategory,
              },
            });
        },
        rowdblclick: function (grid, rowIndex, e) {
          var rec = grid.getStore().getAt(rowIndex);
          var brand = rec.get("brand");
          Ext.getCmp("gs1FilterBrand").setValue(brand);
          Ext.getCmp("relatedBrandWindow").close();
        },
      },
    });
    return _dispatchgridPanel;
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
  var subCategoryComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getSubCategory",
      method: "post",
      fields: ["sub_category_id", "sub_category"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var mrpItemComboStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getMrpsofGs1Products",
      method: "post",
      fields: ["id", "mrp"],
      root: "data",
      listeners:{
        load: function (store, rec, opt) {
          if (store.totalLength > 0) {               
                  Ext.getCmp('itemMrpCom').setValue(store.getAt(0).get("id"));             

          }
      }
      }
    });
    return store;
  };
  var productsGrid = function (cid) {
    var vendorcol = venderItemColmodel();
    var venderstore = prdctSearchStore();
    var vendorlist = prdctSearchtb();
    var pribusinessTypeComboStore = businessTypeComboStorePrimary();
    var deptComboStore = departmentComboStore();
    var categComboStore = categoryComboStore();
    var subCategComboStore = subCategoryComboStore();

    var vendor_filter = new Ext.ux.grid.GridFilters({
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
        {
          type: "string",
          dataIndex: "hs_code",
        },{
          type: "string",
          dataIndex: "description",
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
      sm: check_box,
      bbar: [
        { html: "MAP TO GROZEO : ", align: "left" },
        {
          xtype: "combo",
          store: pribusinessTypeComboStore,
          mode: "local",
          id: "primary_businessType",
          allowBlank: true,
          emptyText: "Retail Category",
          hiddenName: "n[primary_businessType]",
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
              var value = Ext.getCmp("primary_businessType").getValue();
              deptComboStore.baseParams.primaryBt = this.value;
              deptComboStore.load();
            },
          },
        },
        {
          frame: false,
          border: false,
        },
        {
          xtype: "combo",
          store: deptComboStore,
          mode: "local",
          id: "parent_category",
          allowBlank: true,
          emptyText: "Department",
          hiddenName: "n[parent_category]",
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
              var value = record.data.parent_category_id;
              categComboStore.baseParams.department = this.value;
              categComboStore.load();
            },
          },
        },
        {
          frame: false,
          border: false,
        },
        {
          xtype: "combo",
          store: categComboStore,
          mode: "local",
          id: "main_category",
          allowBlank: true,
          emptyText: "Category",
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
              var value = record.data.category_id;
              subCategComboStore.baseParams.category = this.value;
              subCategComboStore.load();
            },
          },
        },
        {
          frame: false,
          border: false,
        },
        {
          xtype: "combo",
          store: subCategComboStore,
          mode: "local",
          id: "sub_category",
          allowBlank: true,
          emptyText: "Sub Category",
          hiddenName: "n[sub_category]",
          displayField: "sub_category",
          valueField: "sub_category_id",
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
              var value = record.data.category_id;
            },
          },
        },
        {
          frame: false,
          border: false,
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          xtype: "button",
          text: "Submit",
          handler: function () {
            var selectitem = Ext.getCmp(
              "gridFinascopStockVenderitemGridgeneration"
            )
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemarr = [];
            for (var i = 0; i < selectedcount; i++) {
              itemarr.push(selectitem[i].data.id);
            }
            if (selectedcount != 0) {
              Application.GS1Products.Cache.cid = cid;
              Application.GS1Products.Cache.itemarr = itemarr;
              Application.GS1Products.saveCheckedItem(itemarr);
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please select a product and proceed.."
              );
            }
          },
        },
        "->",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/search.png",
          xtype: "button",
          text: "Search",
          tabIndex: 38,
          handler: function () {
            Application.GS1Products.searchCategory();
          },
        },{
          frame: false,
          border: false,
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
          xtype: "button",
          text: "Reset",
          tabIndex: 38,
          handler: function () {
            Ext.getCmp("parent_category").reset();
            Ext.getCmp("main_category").reset();
            Ext.getCmp("sub_category").reset();
          },
        },
        {
          frame: false,
          border: false,
        },        
        {
          icon: IMAGE_BASE_PATH + "/default/icons/force_logout.png",
          xtype: "button",
          text: "Close",
          tabIndex: 38,
          handler: function () {
            Ext.getCmp(
              "windowFinascopStockAddvenderitemCreatevendoritem"
            ).close();
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
    });
    return vendorItemgrid;
  };
  var productMrpStore = function (gs1Id) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listMrpsofGs1Products",
        method: "post",
      }),
      fields: ["id", "productId", "mrp", "target_market", "location"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.gs1Id = gs1Id;
        },
      },
    });
    return _Store;
  };
  var productMrpGrid = function (gs1Id) {
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      height: 500,
      width: winsize.width * 0.4,
      store: productMrpStore(gs1Id),
      //iconCls: 'money',
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforProductMrps",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Location",
          dataIndex: "location",
          sortable: true,
          tooltip: "Location",
          hideable: false,
          width: 200,
        },
        {
          header: "MRP",
          dataIndex: "mrp",
          sortable: true,
          align: "right",
          tooltip: "MRP",
          width: 50,
          hideable: false,
        },
        {
          header: "Target Market",
          dataIndex: "target_market",
          sortable: true,
          tooltip: "Target Market",
          width: 50,
          hideable: false,
        },
        { width: 20, dataIndex: " " },
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("gridpanelforProductMrps")
            .getStore()
            .load({
              params: {
                gs1Id: gs1Id,
              },
            });
        },
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _dispatchgridPanel;
  };
  var showMrps = function () {
    var gs1Id = arguments[0];
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var prdctMRPWindowid = Ext.getCmp("productMRPWindow");
    if (Ext.isEmpty(prdctMRPWindowid)) {
      prdctMRPWindowid = new Ext.Window({
        id: "productMRPWindow",
        title: "MRP Details",
        modal: true,
        height: 500,
        width: winsize.width * 0.4,
        shadow: false,
        resizable: false,
        items: [productMrpGrid(gs1Id)],
        buttons: [
          {
            text: "Cancel",
            iconCls: "my-icon61",
            handler: function () {
              prdctMRPWindowid.close();
            },
          },
        ],
        listeners: {
          close: function () {},
        },
      });
    }
    prdctMRPWindowid.doLayout();
    prdctMRPWindowid.show();
    prdctMRPWindowid.center();
  };
  var showProductDetails = function () {
    var gs1Id = arguments[0];
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var prdctDetailWindowid = Ext.getCmp("productDetailsWindow");
    if (Ext.isEmpty(prdctDetailWindowid)) {
      prdctDetailWindowid = new Ext.Window({
        id: "productDetailsWindow",
        title: "Product Details",
        modal: true,
        height: 500,
        width: winsize.width * 0.7,
        shadow: false,
        resizable: false,
        items: [importedProductDetailsView(gs1Id)],
        buttons: [
          {
            text: "Cancel",
            iconCls: "my-icon61",
            handler: function () {
              prdctDetailWindowid.close();
            },
          },
        ],
        listeners: {
          close: function () {},
        },
      });
    }
    prdctDetailWindowid.doLayout();
    prdctDetailWindowid.show();
    prdctDetailWindowid.center();
  };
  var showImages = function () {
    var gs1Id = arguments[0];
    var type = arguments[1];
    var gtin = arguments[2];
    //var printOrderPanel = createprintInvoicePanel(order_auto_id, order_generated_id);
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var src =
      "?module=products_gs1&op=showImages&gs1Id=" + gs1Id + "&type=" + type+ "&gtin=" + gtin;
    var printOrderWindowid = Ext.getCmp("printOrderWindow");
    if (Ext.isEmpty(printOrderWindowid)) {
      printOrderWindowid = new Ext.Window({
        id: "printOrderWindow",
        title: "Image Details",
        modal: true,
        height: 500,
        width: winsize.width * 0.7,
        shadow: false,
        resizable: false,
        items: [
          new Ext.Panel({
            layout: "fit",
            border: false,
            width: winsize.width * 0.7,
            autoScroll: true,
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
          }),
        ],
        buttons: [
          {
            text: "Cancel",
            iconCls: "my-icon61",
            handler: function () {
              printOrderWindowid.close();
            },
          },
        ],
        listeners: {
          close: function () {},
        },
      });
    }
    printOrderWindowid.doLayout();
    printOrderWindowid.show();
    printOrderWindowid.center();
  };
  var importedProductDetailsView = function (gs1Id) {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      height: 500,
      autoScroll: true,
      id: "xtemplateMasterImportedProductBankViewDetails",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="45%">Name </th><td>  {name} </td></tr>',
        '<tr><th width="45%">Brand </th><td>  {brand} </td></tr>',
        '<tr><th width="45%">Category </th><td>  {category} </td></tr>',
        '<tr><th width="45%">Sub Category </th><td>  {sub_category} </td></tr>',
        '<tr><th width="45%">GTIN </th><td>  {gtin} </td></tr>',
        '<tr><th width="45%">Caution </th><td>  {caution} </td></tr>',
        '<tr><th width="45%">SKU Code </th><td>  {sku_code} </td></tr>',
        '<tr><th width="45%">Description </th><td>  {description} </td></tr>',
        '<tr><th width="45%">GPC Code </th><td>  {gpc_code} </td></tr>',
        '<tr><th width="45%">Marketing Info </th><td>  {marketing_info} </td></tr>',
        '<tr><th width="45%">Derived Description </th><td>  {derived_description} </td></tr>',
        '<tr><th width="45%">Country of Orgin </th><td>  {country_of_origin} </td></tr>',
        '<tr><th width="45%">Type </th><td>  {type} </td></tr>',
        '<tr><th width="45%">Packing Type </th><td>  {packaging_type} </td></tr>',
        '<tr><th width="45%">Primary GTIN </th><td>  {primary_gtin} </td></tr>',
        '<tr><th width="45%">Company </th><td>  {company_detail} </td></tr>',
        '<tr><th width="45%">HS Code </th><td>  {hs_code} </td></tr>',
        '<tr><th width="45%">GST </th><td>  {igst} </td></tr>',
        '<tr><th width="45%">Margin </th><td>  {margin} </td></tr>',
        '<tr><th width="45%">Weights & Measures </th><td>  {weights_and_measures} </td></tr>',
        '<tr><th width="45%">Dimensions </th><td>  {dimensions} </td></tr>',
        '<tr><th width="45%">Attributes </th><td>  {attributes} </td></tr>',
        '<tr><th width="45%">Case Configuration </th><td>  {case_configuration} </td></tr>',
        "</table>",
        "</div>"
      ),
      listeners: {
        afterrender: function () {
          Ext.Ajax.request({
            url: modbmURL + "&op=prdctdetailsView",
            method: "POST",
            params: { id: gs1Id },
            success: function (res) {
              var tmp = Ext.decode(res.responseText);
              if (tmp.success === true) {
                var visualsDescPanel = Ext.getCmp(
                  "xtemplateMasterImportedProductBankViewDetails"
                );
                visualsDescPanel.update(tmp);
              }
            },
            failure: function () {
              Ext.MessageBox.alert("Error", "Error occured while sending data");
            },
          });
        },
      },
    });
  };
  var gs1ProductsPanel = function (id, ind, title) {
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
        gs1ProductsGrid(ind),
        new Ext.Panel({
          title: "Product Details",
          collapsible: true,
          collapseMode: 'mini',
          collapsed: true,
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.55,
          cls: "left_side_panel",
          id: "panelGS1ProductViewDetails" + ind,
          height: winsize.height * 0.6,
          autoScroll: true,
          items: [GS1ProductDetailsView(ind)],
          fbar: [],
          buttons: [
            {
              text: "Show Details",
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                  var gs1Id = Ext.getCmp("id" + ind).getValue();
                  showProductDetails(gs1Id);
                }
              },
            },
            {
              text: "Show MRPs",
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                  var gs1Id = Ext.getCmp("id" + ind).getValue();
                  showMrps(gs1Id);
                }
              },
            },
            {
              text: "Show Images",
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                  var gs1Id = Ext.getCmp("id" + ind).getValue();
                  var gtinValue = Ext.getCmp("gtin" + ind).getValue();
                  showImages(gs1Id, 1,gtinValue);
                }
              },
            },
            {
              text: "Reset",
              tabIndex: 139,
              buttonAlign: "left",
              handler: function () {
                Ext.getCmp("gs1ProductPanel" + ind)
                  .getForm()
                  .reset();
              },
            },
            {
              text: "Save & Proceed",
              buttonAlign: "left",
              tabIndex: 139,
              handler: function () {
                Application.GS1Products.FinalzeProduct(ind);
              },
            },
          ],
          fbar: [],
        }),
      ],
      listeners: {
        afterrender: function () {},
      },
    });
    return panel;
  };
  var getSeletedItem = function(ind){
    var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                   var pdId = selectitem[0].data.id;

                }else{
                  Ext.Msg.alert("Notification",
                "Please choose a product and proceed.");
                }
  };
  var gs1FormLoad = function(ind){
    var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                  var pdId = selectitem[0].data.id;
                  var gs1ProductForm = Ext.getCmp(
                  "gs1ProductPanel" + ind
                ).getForm();
                gs1ProductForm.reset();
                gs1ProductForm.load({
                  url: modURL + "&op=getGs1Product_EditData",
                  waitMsg: 'Loading...',
                  params: {
                    id: pdId,
                  },
                  success: function (frm, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    Ext.getCmp(
                      "itemMrpCom" + ind
                    ).getStore().baseParams.gs1Id = pdId;
                    Ext.getCmp("itemMrpCom" + ind)
                      .getStore()
                      .load();
                    Ext.getCmp("department" + ind)
                      .getStore()
                      .load();
                    Ext.getCmp("department" + ind).setRawValue(
                      tmp.data.deptName
                    );
                    Ext.getCmp(
                      "categoryGr" + ind
                    ).getStore().baseParams.department = tmp.data.department;
                    Ext.getCmp("categoryGr" + ind)
                      .getStore()
                      .load();
                    Ext.getCmp("categoryGr" + ind).setRawValue(
                      tmp.data.grCategory
                    );
                    Ext.getCmp(
                      "subCategoryGr" + ind
                    ).getStore().baseParams.category = tmp.data.categoryGr;
                    Ext.getCmp("subCategoryGr" + ind)
                      .getStore()
                      .load();
                    Ext.getCmp("subCategoryGr" + ind).setRawValue(
                      tmp.data.grSubCategory
                    );
                    if (Ext.isEmpty(tmp.data.edibility)) {
                      Ext.getCmp("edibility" + ind).setValue(0);
                    }
                    if (tmp.data.isArchived > 0) {
                      Ext.getCmp("skipReasonOnform" + ind).show();
                      Ext.getCmp("skipReasonOnform" + ind).setValue(
                        "Reason:" + tmp.data.skipReasonOnform
                      );
                    } else {
                      Ext.getCmp("skipReasonOnform" + ind).hide();
                    }
                  },
                });
                }

                
  };
  var gs1ProductsGrid = function (ind) {
    var chk_model = check_model(ind);
    var qugeo_store = gs1MappingProductsStore(ind);
    var qugeo_select = gs1Select(ind);
    var gs1ProductsFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "itemName",
        },
        {
          type: "string",
          dataIndex: "brand",
        },
      ],
    });
    gs1ProductsFilter.remote = true;
    gs1ProductsFilter.autoReload = true;
    var qugeo_grid = new Ext.grid.EditorGridPanel({
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
        getRowClass: function (record, index, params, store) {
          if (
            record.get("isBrandTrimmed") == 1 &&
            record.get("hasMappedItemMaster") == 1
          ) {
            return "finascop_indicateColPALEGREEN";
          } else if (
            record.get("isBrandTrimmed") == 1 &&
            record.get("hasMappedItemMaster") == 0 &&
            record.get("isProMasterGenerated") == 1
          ) {
            return "finascop_indicateColPINK";
          } else if (
            record.get("isBrandTrimmed") == 1 &&
            record.get("hasMappedItemMaster") == 0
          ) {
            return "finascop_indicateColLIGHTORANGE";
          } else {
            return "";
          }
        },
      },
      plugins: [new Ext.ux.grid.GroupSummary(), gs1ProductsFilter],
      id: "gs1_mapping_grid" + ind,
      columns: [
        chk_model,
        {
          header: "Item Name",
          sortable: true,
          dataIndex: "itemName",
          width: 80,
        },
        {
          header: "Brand",
          sortable: true,
          width: 80,
          dataIndex: "brand",
        },
        {
          header: "Item Master",
          sortable: true,
          width: 80,
          dataIndex: "productMaster",
          editor: {
            allowBlank: false,
            xtype: "textfield",
          },
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          sortable: false,
          groupable: false,
          tooltip: "Action",
          items: [{
              icon: IMAGE_BASE_PATH + "/default/icons/Forward.png",
              tooltip: "Map Products",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Application.ProductBankUpload.mapUnAvailProducts(record.get("id"),record.get("brand"),record.get("name"));
                var prdbuttonToClick = Ext.getCmp('convProductSerach');
                prdbuttonToClick.handler.call(prdbuttonToClick, prdbuttonToClick, null);
              },
            },
            {
              iconCls: "viewProductDetail",
              tooltip: "Save & Proceed",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                var pdId = record.get("id");

                
                var gs1ProductForm = Ext.getCmp(
                  "gs1ProductPanel" + ind
                ).getForm();
                gs1ProductForm.reset();
                gs1ProductForm.load({
                  url: modURL + "&op=getGs1Product_EditData",
                  waitMsg: 'Loading...',
                  params: {
                    id: pdId,
                  },
                  success: function (frm, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    Ext.getCmp(
                      "itemMrpCom" + ind
                    ).getStore().baseParams.gs1Id = pdId;
                    Ext.getCmp("itemMrpCom" + ind)
                      .getStore()
                      .load();
                    Ext.getCmp("department" + ind)
                      .getStore()
                      .load();
                    Ext.getCmp("department" + ind).setRawValue(
                      tmp.data.deptName
                    );
                    Ext.getCmp(
                      "categoryGr" + ind
                    ).getStore().baseParams.department = tmp.data.department;
                    Ext.getCmp("categoryGr" + ind)
                      .getStore()
                      .load();
                    Ext.getCmp("categoryGr" + ind).setRawValue(
                      tmp.data.grCategory
                    );
                    Ext.getCmp(
                      "subCategoryGr" + ind
                    ).getStore().baseParams.category = tmp.data.categoryGr;
                    Ext.getCmp("subCategoryGr" + ind)
                      .getStore()
                      .load();
                    Ext.getCmp("subCategoryGr" + ind).setRawValue(
                      tmp.data.grSubCategory
                    );
                    if (Ext.isEmpty(tmp.data.edibility)) {
                      Ext.getCmp("edibility" + ind).setValue(0);
                    }
                    if (tmp.data.isArchived > 0) {
                      Ext.getCmp("skipReasonOnform" + ind).show();
                      Ext.getCmp("skipReasonOnform" + ind).setValue(
                        "Reason:" + tmp.data.skipReasonOnform
                      );
                    } else {
                      Ext.getCmp("skipReasonOnform" + ind).hide();
                    }

                    var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                    .getSelectionModel()
                    .getSelections();
                  var selectedcount = selectitem.length;
                  if (selectedcount == 1) {
                    var gs1Id = Ext.getCmp("id" + ind).getValue();
                    var isBrandTrimmed = Ext.getCmp(
                      "isBrandTrimmed" + ind
                    ).getValue();
                    var hasMappedItemMaster = Ext.getCmp(
                      "hasMappedItemMaster" + ind
                    ).getValue();
                    if (hasMappedItemMaster > 0) {
                      Application.GS1Products.chooseProductType(ind,pdId);
                      } else {
                      setTimeout(function () {
                        Ext.MessageBox.alert(
                        "Notification",
                        "Brand trimming/Product Master Creation missing"
                      );  
                        }, 10);
                      
                    }
                  } else {
                    Ext.MessageBox.alert(
                      "Notification",
                      "Select One item and proceed"
                    );
                  }
                    
                    
                  },
                });
                
                
              },
            },
          ],
        },
      ],
      sm: chk_model,
      listeners: {
        resize: updatePagination,
        celldblClick: function (grid, rowIndex, columnIndex, e) {},
        afteredit: function (grid_event) {
          updateToItemQuantity(grid_event);
        },
        cellClick: function (grid, rowIndex, columnIndex, e) {
          qugeo_grid.getSelectionModel().selectRow(rowIndex);
        },
        afterrender: function () {
          if (ind == "skip") {
            Ext.getCmp("showConverted" + ind).hide();
            Ext.getCmp("convetPrdctsArchive" + ind).setText = "Archive";
          } else {
            Ext.getCmp("showConverted" + ind).show();
            Ext.getCmp("convetPrdctsArchive" + ind).setText = "Skip";
          }
        },
      },
      tbar: [{ html: " Retailer: " },
        {
                  fieldLabel: "Store Group",
                  xtype: "combo",
                  displayField: "name",
                  valueField: "id",
                  mode: "remote",
                  id: "merchantIdprd"+ ind,
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
                    select: function () {
              
            }
                  },
                },{
          frame: false,
          border: false,
        },
        { html: "Select Brand : &nbsp;", id: "branch_label" + ind },
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
            },
          },
        },
        {
          frame: false,
          border: false,
        },{
          icon: IMAGE_BASE_PATH + "/default/icons/search.png",
          xtype: "button",
          id:"convProductSerach",
          text: "Search",
          tabIndex: 37,
          handler: function () {
            var sgId = Ext.getCmp("merchantIdprd" + ind).getValue();
            var brand = Ext.getCmp("search_brand" + ind).getValue();
            if(sgId > 0 || brand > 0){
              Ext.getCmp("gs1_mapping_grid" + ind).getStore().removeAll();
              Ext.getCmp("gs1_mapping_grid" + ind).getStore().baseParams.merchantId = Ext.getCmp("merchantIdprd" + ind).getValue();
              Ext.getCmp("gs1_mapping_grid" + ind).getStore().baseParams.brand_id = Ext.getCmp("search_brand" + ind).getValue();
              Ext.getCmp("gs1_mapping_grid" + ind).getStore().baseParams.ind = ind;
              Ext.getCmp("gs1_mapping_grid" + ind).getStore().load();
            }else{
              Ext.MessageBox.alert(
                "Notification",
                "Choose brand before search"
              );
            }
            
          },
        },{
          frame: false,
          border: false,
        },
        {
          xtype: "checkbox",
          checked: false,
          id: "showConverted" + ind,
          name: "showConverted",
          inputValue: 1,
          boxLabel: "Show Converted Products",
          listeners: {
            check: function (cb1, checked) {
              if (checked == true) {
                Ext.getCmp("gs1_mapping_grid" + ind)
                  .getStore()
                  .removeAll();
                Ext.getCmp(
                  "gs1_mapping_grid" + ind
                ).getStore().baseParams.brand_id = Ext.getCmp(
                  "search_brand" + ind
                ).getValue();
                Ext.getCmp(
                  "gs1_mapping_grid" + ind
                ).getStore().baseParams.showConverted = 1;
                Ext.getCmp("gs1_mapping_grid" + ind)
                  .getStore()
                  .load();
              } else {
                Ext.getCmp("gs1_mapping_grid" + ind)
                  .getStore()
                  .removeAll();
                Ext.getCmp(
                  "gs1_mapping_grid" + ind
                ).getStore().baseParams.brand_id = Ext.getCmp(
                  "search_brand" + ind
                ).getValue();
                Ext.getCmp(
                  "gs1_mapping_grid" + ind
                ).getStore().baseParams.showConverted = 0;
                Ext.getCmp("gs1_mapping_grid" + ind)
                  .getStore()
                  .load();
              }
            },
          },
        },
        {
          frame: false,
          border: false,
        },
        {
          xtype: "checkbox",
          checked: false,
          id: "showArchived" + ind,
          name: "showArchived",
          inputValue: 1,
          boxLabel: "Show Skipped",
          hidden: true,
          listeners: {
            check: function (cb1, checked) {
              if (checked == true) {
                Ext.getCmp("gs1_mapping_grid" + ind)
                  .getStore()
                  .removeAll();
                Ext.getCmp(
                  "gs1_mapping_grid" + ind
                ).getStore().baseParams.brand_id = Ext.getCmp(
                  "search_brand" + ind
                ).getValue();
                Ext.getCmp(
                  "gs1_mapping_grid" + ind
                ).getStore().baseParams.showArchived = 1;
                Ext.getCmp("gs1_mapping_grid" + ind)
                  .getStore()
                  .load();
              } else {
                Ext.getCmp("gs1_mapping_grid" + ind)
                  .getStore()
                  .removeAll();
                Ext.getCmp(
                  "gs1_mapping_grid" + ind
                ).getStore().baseParams.brand_id = Ext.getCmp(
                  "search_brand" + ind
                ).getValue();
                Ext.getCmp(
                  "gs1_mapping_grid" + ind
                ).getStore().baseParams.showArchived = 0;
                Ext.getCmp("gs1_mapping_grid" + ind)
                  .getStore()
                  .load();
              }
            },
          },
        },
      ],
      bbar: [
        {
          xtype: "button",
          border: true,
          text: "Skip",
          id: "convetPrdctsArchive" + ind,
          iconCls: "list_users",
          handler: function () {
            var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemarr = [];
            for (var i = 0; i < selectedcount; i++) {
              itemarr.push(selectitem[i].data.id);
            }
            if (selectedcount != 0) {
              Application.GS1Products.skipOrder(itemarr, ind);
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please choose products and proceed."
              );
            }
          },
        } /* <?php if (user_access("products_gs1", "trimProductBrands")) { ?> */,
        '|',{
          xtype: "button",
          text: "Trim Brand",
          iconCls: "list_users",
          handler: function () {
            var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemarr = [];
            for (var i = 0; i < selectedcount; i++) {
              itemarr.push(selectitem[i].data.id);
            }
            if (selectedcount != 0) {
              Ext.Ajax.request({
                url: modURL + "&op=trimProductBrands",
                method: "post",
                params: { type: "brandTrim", itemarr: Ext.encode(itemarr) },
                success: function (resp) {
                  var res = Ext.decode(resp.responseText);
                  if (res.success === true) {
                    Application.example.msg("Success", "Saved successfully.");
                    Ext.getCmp(
                      "gs1_mapping_grid" + ind
                    ).getStore().baseParams.brand_id = Ext.getCmp(
                      "search_brand" + ind
                    ).getValue();
                    Ext.getCmp("gs1_mapping_grid" + ind)
                      .getStore()
                      .load();
                  } else {
                    Ext.MessageBox.alert(
                      "Notification",
                      "Issue in brand trim.",
                      function (btn) {
                        Ext.getCmp(
                          "gs1_mapping_grid" + ind
                        ).getStore().baseParams.brand_id = Ext.getCmp(
                          "search_brand" + ind
                        ).getValue();
                        Ext.getCmp("gs1_mapping_grid" + ind)
                          .getStore()
                          .load();
                      }
                    );
                  }
                },
              });
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please choose products and proceed."
              );
            }
          },
        } /* <?php } ?> */ /* <?php if (user_access("products_gs1", "generateGS1ItemMaster")) { ?> */,
        '|',{
          xtype: "button",
          text: "Mark Item Master as Private Item",
          iconCls: "list_users",
          handler: function () {
            console.log("itemBrandMappedCount");
            var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemMasterItemsArr = [];
            var itemBrandMappedCount = 0;
            if (selectedcount > 0) {
              for (var i = 0; i < selectedcount; i++) {
                itemMasterItemsArr.push(selectitem[i].data.id);
                if (selectitem[i].data.isBrandTrimmed == 1) {
                  itemBrandMappedCount++;
                }
              }
              console.log(itemBrandMappedCount);
              if (selectedcount == itemBrandMappedCount) {
                Ext.Ajax.request({
                  url: modURL + "&op=mapConfirmGS1ItemMaster",
                  method: "POST",
                  waitMsg: "Processing",
                  params: {
                    itemarr: Ext.encode(itemMasterItemsArr),
                  },
                  failure: function (response, options) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert("Error", "tmp.msg");
                  },
                  success: function (response, options) {
                    var tmp = Ext.decode(response.responseText);

                    if (tmp.success === true) {
                      Ext.getCmp(
                        "gs1_mapping_grid" + ind
                      ).getStore().baseParams.brand_id = Ext.getCmp(
                        "search_brand" + ind
                      ).getValue();
                      Ext.getCmp("gs1_mapping_grid" + ind)
                        .getStore()
                        .load();
                    } else {
                      Ext.MessageBox.alert("Error", tmp.msg);
                    }
                  },
                });
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "The process of trimming the brand has not been completed for the selected items..."
                );
              }
            }
          },
        } /* <?php } ?> */ /* <?php if (user_access("products_gs1", "generateGS1ItemMaster")) { ?> */,
        {
          xtype: "button",
          hidden:true,
          text: "Generate Item",
          id: "genItemMaster",
          iconCls: "list_users",
          handler: function () {
            var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemMasterItemsArr = [];
            console.log(selectedcount);
            if (selectedcount > 0) {
              for (var i = 0; i < selectedcount; i++) {
                itemMasterItemsArr.push(selectitem[i].data.id);
              }
              console.log(itemMasterItemsArr);
              Ext.Ajax.request({
                url: modURL + "&op=generateGS1ItemMaster",
                method: "POST",
                waitMsg: "Processing",
                params: {
                  itemarr: Ext.encode(itemMasterItemsArr),
                },
                failure: function (response, options) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", "tmp.msg");
                },
                success: function (response, options) {
                  var tmp = Ext.decode(response.responseText);

                  if (tmp.success === true) {
                    Ext.getCmp(
                      "gs1_mapping_grid" + ind
                    ).getStore().baseParams.brand_id = Ext.getCmp(
                      "search_brand" + ind
                    ).getValue();
                    Ext.getCmp("gs1_mapping_grid" + ind)
                      .getStore()
                      .load();
                  } else {
                    Ext.MessageBox.alert("Error", tmp.msg);
                  }
                },
              });
            }
          },
        },
        /* <?php } ?> */ /* <?php if (user_access("products_gs1", "confirmGeneratedMaster")) { ?> */ {
          xtype: "button",
          hidden:true,
          text: "Confirm Item",
          iconCls: "list_users",
          handler: function () {
            console.log("itemBrandMappedCount");
            var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemMasterItemsArr = [];
            var itemBrandMappedCount = 0;
            if (selectedcount > 0) {
              for (var i = 0; i < selectedcount; i++) {
                itemMasterItemsArr.push(selectitem[i].data.id);
                if (selectitem[i].data.isBrandTrimmed == 1) {
                  itemBrandMappedCount++;
                }
              }
              console.log(itemBrandMappedCount);
              if (selectedcount == itemBrandMappedCount) {
                Application.GS1Products.confirmItemMasterName(
                  itemMasterItemsArr,
                  ind
                );
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "The process of trimming the brand has not been completed for the selected items..."
                );
              }
            }
          },
        } /* <?php } ?> */ /* <?php if (user_access("products_gs1", "mapItemMastertoProducts")) { ?> */,
        '|',{
          xtype: "button",
          text: "Choose or Create Item",
          iconCls: "list_users",
          handler: function () {
            console.log("itemBrandMappedCount");
            var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemMasterItemsArr = [];
            var itemBrandMappedCount = 0;
            if (selectedcount > 0) {
              for (var i = 0; i < selectedcount; i++) {
                itemMasterItemsArr.push(selectitem[i].data.id);
                if (selectitem[i].data.isBrandTrimmed == 1) {
                  itemBrandMappedCount++;
                }
              }
              console.log(itemBrandMappedCount);
              //if(selectedcount == itemBrandMappedCount){
              Application.GS1Products.createItemMasterName(
                itemMasterItemsArr,
                ind
              );
              //}else{
              //  Ext.MessageBox.alert("Notification", "Some Items are not brand trimmed");
              //}
            }
          },
        } /* <?php } ?> */,
        '|',{
          buttonAlign: "right",
            text: "Images",
            iconCls: "list_users",
            icon: IMAGE_BASE_PATH + "/default/icons/upload_image.png",
            handler: function () {
              var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            if (selectedcount == 1) {
              var gsId = selectitem[0].data.id;
              var gtin = selectitem[0].data.gtin;
              Application.GS1Products.viewProducImages(gsId,gtin);
            }
              
            },
        },
        '|',{
          xtype: "button",
          icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
          text: "Reload",
          handler: function () {
            Ext.getCmp(
              "gs1_mapping_grid" + ind
            ).getStore().baseParams.brand_id = Ext.getCmp(
              "search_brand" + ind
            ).getValue();
            Ext.getCmp("gs1_mapping_grid" + ind)
              .getStore()
              .load();
            Ext.getCmp("gs1ProductPanel" + ind)
              .getForm()
              .reset();
          },
        },'->',{
              xtype: "button",
              iconCls: 'apikey',
              text: "Show Details",
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                   var gs1Id = selectitem[0].data.id;
                    showProductDetails(gs1Id);
                }else{
                  Ext.Msg.alert("Notification",
                "Please choose a product and proceed.");
                }
                /*var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                  var gs1Id = Ext.getCmp("id" + ind).getValue();
                  showProductDetails(gs1Id);
                }*/
              },
            },
            {
              xtype: "button",
              text: "Show MRPs",
              iconCls: 'apikey',
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                   var gs1Id = selectitem[0].data.id;
                    showMrps(gs1Id);
                }else{
                  Ext.Msg.alert("Notification",
                "Please choose a product and proceed.");
                }
                /*var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                  var gs1Id = Ext.getCmp("id" + ind).getValue();
                  showMrps(gs1Id);
                }*/
              },
            },
            {
              xtype: "button",
              text: "Show Images",
              icon: IMAGE_BASE_PATH + "/default/icons/upload_image.png",
              tabIndex: 139,
              buttonAlign: "right",
              handler: function () {
                var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                   var gs1Id = selectitem[0].data.id;
                   var gtinValue = selectitem[0].data.gtin;
                  showImages(gs1Id, 1,gtinValue);
                }else{
                  Ext.Msg.alert("Notification",
                "Please choose a product and proceed.");
                }

                /*var selectitem = Ext.getCmp("gs1_mapping_grid" + ind)
                  .getSelectionModel()
                  .getSelections();
                var selectedcount = selectitem.length;
                if (selectedcount == 1) {
                  var gs1Id = Ext.getCmp("id" + ind).getValue();
                  var gtinValue = Ext.getCmp("gtin" + ind).getValue();
                  showImages(gs1Id, 1,gtinValue);
                }*/
              },
            },
            {
              xtype: "button",
              text: "Reset",
              hidden:true,
              iconCls: 'my-icon1',
              tabIndex: 139,
              buttonAlign: "left",
              handler: function () {
                Ext.getCmp("gs1ProductPanel" + ind)
                  .getForm()
                  .reset();
              },
            },
            {
              xtype: "button",
              hidden:true,
              iconCls: 'my-icon1',
              text: "Save & Proceed",
              buttonAlign: "left",
              hidden:true,
              tabIndex: 139,
              handler: function () {
                Application.GS1Products.FinalzeProduct(ind);
              },
            }
      ],
      stripeRows: true,
    });
    return qugeo_grid;
  };
  function updateToItemQuantity(grid_event, labId) {
    var data = Ext.encode(grid_event.record.data);
    Ext.Ajax.request({
      waitMsg: "Please wait...",
      url: modURL + "&op=updateItemMasterName",
      params: {
        data: Ext.encode(grid_event.record.data),
      },
      failure: function (response, options) {
        Ext.MessageBox.alert("Notification", "Save Failed");
      },
      success: function (response, options) {
        if (response.responseText != "") {
          eval("var tmp=" + response.responseText);
          var tmp = Ext.decode(response.responseText);
          if (tmp.success !== undefined && tmp.success === true) {
            Application.example.msg("Notification", tmp.msg);
          } else if (tmp.success === false) {
            Ext.MessageBox.alert(
              "Notification",
              "Error Occured while saving",
              function (btn) {
                if (btn == "ok") {
                }
              }
            );
          }
        }
      },
    });
  }
  var GS1ProductDetailsView = function (ind) {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var apikey = _SESSION.apikey;

    var deptComboStore = departmentComboStore();
    var categComboStore = categoryComboStore();
    var subCategComboStore = subCategoryComboStore();
    var mrpComboStore = mrpItemComboStore();

    var itemPanel = new Ext.FormPanel({
      id: "gs1ProductPanel" + ind,
      height: 500,
      autoScroll: true,
      frame: true,
      layout: "column",
      monitorValid: true,
      items: [
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Brand",
              id: "brand" + ind,
              name: "brand",
              allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Product Master",
              id: "productMaster" + ind,
              name: "productMaster",
              allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Variant",
              id: "variant" + ind,
              name: "variant",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Content Net Wt",
              id: "netWeight" + ind,
              name: "netWeight",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Content Net Unit",
              id: "netUnit" + ind,
              name: "netUnit",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Manufacturer/Supplier",
              id: "company_detail" + ind,
              name: "company_detail",
              //allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "HSN",
              id: "hs_code" + ind,
              name: "hs_code",
              //allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Package Type",
              id: "packaging_type" + ind,
              name: "packaging_type",
             // allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Pack Measurement Unit",
              id: "packageUnit" + ind,
              name: "packageUnit",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Package Height",
              id: "packageHeight" + ind,
              name: "packageHeight",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Package Width",
              id: "packageWidth" + ind,
              name: "packageWidth",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Package Depth",
              id: "packageDepth" + ind,
              name: "packageDepth",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Package Weight Unit",
              id: "packageWtUnt" + ind,
              name: "packageWtUnt",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Package Weight",
              id: "packageWt" + ind,
              name: "packageWt",
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Country of Orgin",
              id: "country_of_origin" + ind,
              name: "country_of_origin",
              //allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 1,
          labelAlign: "top",
          items: [
            {
              xtype: "textarea",
              fieldLabel: "Short Description",
              id: "gs1description" + ind,
              name: "description",
              //allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "combo",
              fieldLabel: "Edibility",
              emptyText: "Edibility",
              id: "edibility" + ind,
              name: "edibility",
              mode: "local",
              typeAhead: true,
              autoSelect: true,
              typeAheadDelay: true,
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
              hiddenName: "edibility",
              tabIndex: 514,
            },
            /*{
              xtype: "textfield",
              fieldLabel: "Edibility",
              id: "edibility",
              name: "edibility",
              labelAlign: "top",
              anchor: "95%",
            },*/
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Orginal Category",
              id: "category" + ind,
              name: "category",
              //allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Orginal Sub Category",
              id: "gs1sub_category" + ind,
              name: "sub_category",
              //allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "hidden",
              fieldLabel: "Name",
              id: "gtinName" + ind,
              name: "gtinName",
              anchor: "95%",
              allowBlank: false,
            },
            {
              xtype: "hidden",
              fieldLabel: "Product Id",
              id: "id" + ind,
              name: "id",
              anchor: "95%",
              allowBlank: false,
            },
            {
              xtype: "hidden",
              id: "isMastersMapped" + ind,
              name: "isMastersMapped",
              anchor: "95%",
              allowBlank: false,
            },
            {
              xtype: "hidden",
              id: "isBrandTrimmed" + ind,
              name: "isBrandTrimmed",
              anchor: "95%",
              allowBlank: false,
            },
            {
              xtype: "hidden",
              id: "hasMappedItemMaster" + ind,
              name: "hasMappedItemMaster",
              anchor: "95%",
              allowBlank: false,
            },
            {
              xtype: "combo",
              store: deptComboStore,
              mode: "local",
              id: "department" + ind,
              allowBlank: true,
              emptyText: "Department",
              hiddenName: "department",
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
              hideTrigger: true,
              tabIndex: 34,
              listeners: {
                select: function (combo, record, index) {
                  var value = record.data.parent_category_id;
                  categComboStore.baseParams.department = this.value;
                  categComboStore.load();
                },
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "combo",
              store: categComboStore,
              mode: "local",
              id: "categoryGr" + ind,
              allowBlank: true,
              emptyText: "Category",
              hiddenName: "categoryGr",
              displayField: "category_name",
              valueField: "category_id",
              typeAhead: true,
              forceSelection: true,
              hideTrigger: true,
              editable: true,
              minChars: 2,
              anchor: "98%",
              //selectOnFocus: true,
              triggerAction: "all",
              lazyRender: true,
              tabIndex: 34,
              listeners: {
                select: function (combo, record, index) {
                  var value = record.data.category_id;
                  subCategComboStore.baseParams.category = this.value;
                  subCategComboStore.load();
                },
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.33,
          labelAlign: "top",
          items: [
            {
              xtype: "combo",
              store: subCategComboStore,
              mode: "local",
              id: "subCategoryGr" + ind,
              allowBlank: true,
              emptyText: "Sub Category",
              hiddenName: "subCategoryGr",
              displayField: "sub_category",
              valueField: "sub_category_id",
              typeAhead: true,
              forceSelection: true,
              editable: true,
              minChars: 2,
              anchor: "98%",
              //selectOnFocus: true,
              triggerAction: "all",
              hideTrigger: true,
              lazyRender: true,
              tabIndex: 34,
              listeners: {
                select: function (combo, record, index) {
                  var value = record.data.category_id;
                },
              },
            },
          ],
        },{
          layout: "form",
          columnWidth: 0.10,
          labelAlign: "top",
          hidden:true,
          items: [
            {
              xtype: "combo",
              store: mrpComboStore,
              mode: "local",
              id: "itemMrpCom" + ind,
              allowBlank: true,              
              fieldLabel: "MRP",
              emptyText: "MRP",
              hiddenName: "itemMrpCom",
              displayField: "mrp",
              valueField: "id",
              typeAhead: true,
              forceSelection: true,
              editable: true,
              minChars: 2,
              anchor: "98%",
              triggerAction: "all",
              lazyRender: true,
              tabIndex: 34,
              listeners: {
                select: function (combo, record, index) {
                  
                },
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: .20,
          bodyStyle: { "padding-top": "12px","padding-left": "3px" },
          labelAlign: "top",
          items: [{
            hideLabel: true,
            xtype: "displayfield",
            fieldLabel: " ",
            id: "gtin" + ind,
            name: "gtin",
            style: {
              "font-weight": "bold",
              "font-size": "20px",
              "color": "gray",
            },
            anchor: "98%",
            listeners: {
              afterrender: function (view) {
                // Listener for onclick
                /*view.getEl().on("click", function () {
                  console.log(view.value);
                  var gtinValue = Ext.getCmp("gtin" + ind).getValue();
                  window.open("http://google.com/search?q=" + gtinValue);
                });*/
              },
            },
          }
          ],
        },{
          layout: "form",
          columnWidth: 0.10,
          labelAlign: "right",
          labelWidth: 120,
          bodyStyle: { "padding-top": "15px" },
          items: [{
            xtype: "button",
            text: "Barcode",
            align: "right",
            tabIndex: 538,
            iconCls: "search",
            handler: function () {
              var gtinValue = Ext.getCmp("gtin" + ind).getValue();
              window.open("http://www.digit-eyes.com/cgi-bin/digiteyes.cgi?upcCode="+gtinValue+"&action=lookupUpc&go=Go%21");
              //window.open("http://google.com/search?q=" + gtinValue);
            }
          }]
        },{
          layout: "form",
          columnWidth: 0.10,
          labelAlign: "right",
          labelWidth: 120,
          bodyStyle: { "padding-top": "15px" },
          items: [{
            xtype: "button",
            text: "Name",
            align: "right",
            tabIndex: 538,
            iconCls: "search",
            handler: function () {
              var gtinName = Ext.getCmp("gtinName" + ind).getValue();
              window.open("http://google.com/search?q=" + gtinName);
            }
          }]
        },{
          layout: "form",
          columnWidth: 0.10,
          labelWidth: 120,
          bodyStyle: { "padding-top": "15px" },
          items: [{
            xtype: "button",
            text: "TP Site",
            align: "right",
            tabIndex: 538,
            iconCls: "search",
            handler: function () {
              var gtinName = Ext.getCmp("gtinName" + ind).getValue();
              var gtinValue = Ext.getCmp("gtin" + ind).getValue();
              var gsId = Ext.getCmp("id" + ind).getValue();
              Application.ProductScrapper.initProductSrapper(gsId,gtinValue,gtinName,ind);
            }
          }]
        },{
          layout: "form",
          columnWidth: 0.30,
          labelAlign: "right",
          labelWidth: 120,
          bodyStyle: { "padding-top": "15px" },
          items: [{
            xtype: "checkbox",
            id: "barcodeUnVerified",
            tabIndex: 103,
            align: "left",
            inputValue: 1,
            anchor: "99%",
            name: "barcodeUnVerified",
            labelAlign: "right",
            boxLabel: "Barcode not verified",
            listeners: {
              check: function (checkbox, checked) {
                
              },
            },
          }]
        },
        {
          layout: "form",
          columnWidth: 1,
          labelAlign: "top",
          items: [
            {
              hideLabel: true,
              xtype: "displayfield",
              fieldLabel: " ",
              width: 150,
              id: "skipReasonOnform" + ind,
              name: "skipReasonOnform",
              style: { "font-weight": "bold" },
              anchor: "97%",
            },
          ],
        },
      ],
    });
    return itemPanel;
  };

  var gs1MappingProductsStore = function (ind) {
    var qugeo_store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listProcessedProducts",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "id",
          root: "data",
        },
        [
          "id",
          "brand",
          "name",
          "gtin",
          "description",
          "category",
          "sub_category",
          "gpc_code",
          "productMaster",
          "itemName",
          "isBrandTrimmed",
          "brandTrimmedName",
          "hasMappedItemMaster",
          "isProMasterGenerated",
        ]
      ),
      sortInfo: {
        field: "id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "DESC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gs1_mapping_grid" + ind)
            .getView()
            .refresh();
          //Ext.getCmp("gs1_mapping_grid").getSelectionModel().selectRow(0);
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
  var check_model = function (ind) {
    return new Ext.grid.CheckboxSelectionModel({
      multiSelect: true,
      dataIndex: "checked",
      checkOnly: true,
      listeners: {
        selectionchange: function (selModel) {
          if (
            !Ext.isEmpty(
              Ext.getCmp("gs1_mapping_grid" + ind)
                .getSelectionModel()
                .getSelections()
            )
          ) {
            var ID = Ext.getCmp("gs1_mapping_grid" + ind)
              .getSelectionModel()
              .getSelections()[0].data.id;
            var gs1ProductForm = Ext.getCmp("gs1ProductPanel" + ind).getForm();
            gs1ProductForm.reset();
          }
        },
        rowdeselect: function (sm, rowIndex, record) {
          record.set("checked", "false");
          var gs1ProductForm = Ext.getCmp("gs1ProductPanel" + ind).getForm();
          gs1ProductForm.reset();
        },
        rowselect: function (sm, rowIndex, record) {
          record.set("checked", "true");
          //Ext.getCmp("gs1_mapping_grid").getSelectionModel().selectRow(rowIndex);
        },
      },
    });
  };
  var itemMasterMapStore = function (itemarr) {
    var qugeo_store = new Ext.data.JsonStore({
      url: modURL + "&op=listProductsToMapItemMaster",
      fields: [
        "id",
        "brand",
        "name",
        "gtin",
        "description",
        "category",
        "sub_category",
        "gpc_code",
        "productMaster",
        "itemName",
        "isBrandTrimmed",
        "brandTrimmedName",
        "hasMappedItemMaster",
        "isProMasterGenerated",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function () {
          this.baseParams.itemarr = Ext.encode(itemarr);
        },
      },
    });
    return qugeo_store;
  };
  var retalineItemMasterGrid = function (itemarr) {
    var addItemMaster_store = itemMasterMapStore(itemarr);
    var productMasterFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "itemName",
        },
        {
          type: "string",
          dataIndex: "brand",
        },
      ],
    });
    productMasterFilter.remote = true;
    productMasterFilter.autoReload = true;

    var grid_panel = new Ext.grid.GridPanel({
      store: addItemMaster_store,
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
      plugins: [new Ext.ux.grid.GroupSummary(), productMasterFilter],
      id: "gs1itemmaster_mapping_grid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brand",
          sortable: true,
          width: 80,
          dataIndex: "brand",
        },
        {
          header: "Item Name",
          sortable: true,
          dataIndex: "itemName",
          width: 80,
        },
        {
          header: "Item Master",
          sortable: true,
          width: 80,
          dataIndex: "productMaster",
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
      }),
      listeners: {
        resize: updatePagination,
        celldblClick: function (grid, rowIndex, columnIndex, e) {},
      },
      tbar: [],
      bbar: [],
      stripeRows: true,
    });
    return grid_panel;
  };
  var searchProductsGrid = function () {
    var searchProductcol = prdtSearchItemColmodel();
    var searchProductstore = exportPrdctSearchStore();
    var searchProductlist = exportPrdctSearchtb();

    var vendor_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [{ type: "string", dataIndex: "brand" },{ type: "string", dataIndex: "stit_SKU" },{ type: "string", dataIndex: "isExported" }],
    });
    vendor_filter.remote = true;
    vendor_filter.autoReload = true;

    var vendorItemgrid = new Ext.grid.GridPanel({
      loadMask: true,
      store: searchProductstore,
      colModel: searchProductcol,
      tbar: searchProductlist,
      plugins: [vendor_filter],
      region: "center",
      height: 400,
      frame: false,
      border: false,
      hideBorders: true,
      id: "productSearchGrid",
      sm: check_box,
      bbar: [
        "->",
        {
          xtype: "button",
          text: "Export",
          handler: function () {
            var selectitem = Ext.getCmp("productSearchGrid")
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var productsarr = [];
            for (var i = 0; i < selectedcount; i++) {
              productsarr.push(selectitem[i].data.stit_ID);
            }
            if (selectedcount != 0) {
              //Application.GS1Products.Cache.cid = cid;
              Application.GS1Products.Cache.productsarr = productsarr;
              Application.GS1Products.exportCheckedItem(productsarr);
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
    });
    return vendorItemgrid;
  };
  var prdtSearchItemColmodel = function () {
    var colmodel = new Ext.grid.ColumnModel({
      sortable: true,
      columns: [
        check_box,
        rowno,
        { header: "Name", width: 200, dataIndex: "stit_SKU", sortable: true },
        { header: "GTIN", width: 200, dataIndex: "gtin", sortable: true },
        {
          header: "Brand",
          dataIndex: "stit_brand_name",
          hideable: true,
          sortable: true,
        },
        {
          header: "Sub Category",
          dataIndex: "stit_category_name",
          hideable: true,
          sortable: false,
        },{
          header: "Is Exported",
          dataIndex: "isExported",
          hideable: true,
          sortable: false,
        },
      ],
    });
    return colmodel;
  };
  var exportPrdctSearchStore = function () {
    var store = new Ext.data.JsonStore({
      method: "POST",
      url: modURL + "&op=ProdctListing",
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      fields: [
        "stit_ID",
        "stit_SKU",
        "stit_category_name",
        "gtin",
        "stit_brand_name",
        "image_front",
        "image_back",
        "image_top",
        "image_bottom",
        "image_left",
        "image_right",
        "image_top_left",
        "image_top_right","isExported"
      ],
      listeners: {
        beforeload: function (thisStore, options) {
          thisStore.baseParams.current_type = current_type;
        },
        load: function (store, records, options) {
          var storeCount = store.data.length;
          storeCount = storeCount + " Products";
          //Ext.getCmp('audit_receipt_total_amount').setValue(storeCount);
        },
      },
    });

    return store;
  };
  var exportPrdctSearchtb = function () {
    var masterBrandStore = masterBrandComboStore();
    var tbar = new Ext.Toolbar({
      style: "margin:5px 1px 5px 1px;",
      labelAlign: "left",
      frame: false,
      border: false,
      hideBorders: true,
      items: [
        { html: " Brand: " },
        {
          xtype: "combo",
          store: masterBrandStore,
          mode: "local",
          id: "mstrFilterBrand",
          allowBlank: true,
          fieldLabel: "Brand",
          hiddenName: "n[mstrFilterBrand]",
          displayField: "brand_name",
          valueField: "brand_id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 36,
          listeners: {
            select: function () {
              var value = Ext.getCmp("mstrFilterBrand").getValue();
              filterProducts(value, "", "");
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
          tabIndex: 37,
          handler: function () {
            var brand = Ext.getCmp("mstrFilterBrand").getValue();
            filterProducts(brand, "", "");
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
          tabIndex: 38,
          handler: function () {
            Ext.getCmp("mstrFilterBrand").reset();
            filterProducts(0, 0, 0);
          },
        },
      ],
    });
    return tbar;
  };
  var filterProducts = function (brand) {
    var gridvalue = Ext.getCmp("productSearchGrid").getStore();
    gridvalue.baseParams = {
      brand: brand,
    };
    gridvalue.load();
  };

  var splashGridStores = function (gsId,gtin) {
    var store = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url: modURL + '&op=listProductimages',
            method: 'post'
        }), reader: new Ext.data.JsonReader({
            totalProperty: 'totalCount',
            idProperty: 'id',
            root: 'data'
        }, ['id','slgalimg_path','imagetype','thumpimg_path','status','imagename']),
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
            this.baseParams.gsId = gsId;
            this.baseParams.gtin = gtin;
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
  var check_modelImage =function(gsId,gtin){
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
  var file_grid = function (gsId,gtin) {
    var imagechk_model = check_modelImage(gsId,gtin);
    var projectFileStore = splashGridStores(gsId,gtin);
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
        sm: imagechk_model,
        frame: true,
        border: false,
        hideBorders: true,
        columns: [new Ext.grid.RowNumberer(),
          imagechk_model,
            {
                header: 'Name',
                sortable: true,
                dataIndex: 'imagename',
                hideable: false,
                tooltip: 'Name'                  
  
            },{
                header: 'Image',
                sortable: true,
                dataIndex: 'thumpimg_path',
                hideable: false,
                tooltip: 'Image',
                renderer: function (value, metadata, record) {                  
                    return '<img height = "200px" src="' + record.data.thumpimg_path + '">';
                }
  
            }],
        tbar: []
    });
    return grid;
  };
  var existingHasnComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getExistingHsns",
      method: "post",
      fields: ['taxValueId','hsn_id','hsnGst','hsn_code','codeGst'],
      root: "data",
    });
    return store;
  };
  var prdcthsnSearchStore = function () {
    var store = new Ext.data.JsonStore({
      method: "POST",
      url: modURL + "&op=hsnProdctListing",
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
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
        "igst","retailCategoryName","departmentName","categoryName","subCategName",
        "image_front",
        "image_back",
        "image_top",
        "image_bottom",
        "image_left",
        "image_right",
        "image_top_left",
        "image_top_right",
      ],
      listeners: {
        beforeload: function (thisStore, options) {
          thisStore.baseParams.current_type = current_type;
        },
        load: function (store, records, options) {
          var storeCount = store.data.length;
          storeCount = storeCount + " Products";
          //Ext.getCmp('audit_receipt_total_amount').setValue(storeCount);
        },
      },
    });

    return store;
  };
  var prdctSearchHsntb = function () {
    var gs1BrandStore = gs1BrandHsnComboStore();
    var gs1CategoryStore = gs1CategoryComboStore();
    var gs1SubCategoryStore = gs1SubCategoryComboStore();
    var tbar = new Ext.Toolbar({
      style: "margin:5px 1px 5px 1px;",
      labelAlign: "left",
      frame: false,
      border: false,
      hideBorders: true,
      items: [
        {
          hidden: true,
          xtype: "combo",
          store: gs1CategoryStore,
          mode: "local",
          id: "gs1FilterCategory",
          allowBlank: true,
          fieldLabel: "Category",
          hiddenName: "n[gs1FilterCategory]",
          displayField: "categoryName",
          valueField: "id",
          typeAhead: true,
          forceSelection: true,
          editable: true,
          minChars: 2,
          anchor: "98%",
          triggerAction: "all",
          lazyRender: true,
          tabIndex: 34,
          listeners: {
            select: function () {
              var value = Ext.getCmp("gs1FilterCategory").getValue();
              filterHsnItems("", value, "", 0,0);
              //var value = Ext.getCmp("primary_businessType").getValue();
              gs1SubCategoryStore.baseParams.category = this.value;
              gs1SubCategoryStore.load();
            },
          },
        },
        {
          hidden: true,
          xtype: "combo",
          store: gs1SubCategoryStore,
          mode: "local",
          id: "gs1FilterSubCategory",
          allowBlank: true,
          fieldLabel: "Sub Category",
          hiddenName: "n[gs1FilterSubCategory]",
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
              var value = Ext.getCmp("gs1FilterSubCategory").getValue();
              filterHsnItems("", "", value, "",0);
              gs1BrandStore.baseParams.category =
                Ext.getCmp("gs1FilterCategory").getValue();
              gs1BrandStore.baseParams.subcategory = this.value;
              gs1BrandStore.load();
            },
          },
        },
        {
          frame: false,
          border: false,
        },{ html: " Retailer: " },
        {
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
                  listeners: {},
                },{
          frame: false,
          border: false,
        },
        { html: " Brand: " },
        {
          xtype: "combo",
          store: gs1BrandStore,
          mode: "local",
          id: "gs1FilterBrand",
          allowBlank: true,
          fieldLabel: "Brand",
          hiddenName: "n[gs1FilterBrand]",
          displayField: "brandName",
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
            select: function () {
              var value = Ext.getCmp("gs1FilterBrand").getValue();
              var sgId = Ext.getCmp("merchantId").getValue();
              var archive = 0;
              filterHsnItems(value, 0, 0, 0,sgId);
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
          tabIndex: 37,
          handler: function () {
            var brand = Ext.getCmp("gs1FilterBrand").getValue();
            var category = Ext.getCmp("gs1FilterCategory").getValue();
            var subcategory = Ext.getCmp("gs1FilterSubCategory").getValue();
            var sgId = Ext.getCmp("merchantId").getValue();
            var archive = 0;
            if (sgId > 0 || brand > 0) {
              filterHsnItems(brand, category, subcategory, archive,sgId);

            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Choose brand before search"
              );
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
          tabIndex: 38,
          handler: function () {
            Ext.getCmp("gs1FilterBrand").reset();
            Ext.getCmp("gs1FilterCategory").reset();
            Ext.getCmp("gs1FilterSubCategory").reset();
            Ext.getCmp("merchantId").reset();
            filterHsnItems(0, 0, 0, 0,0);
          },
        }
      ],
    });
    return tbar;
  };
  var filterHsnItems = function (brand, category, subcategory, archive,merchantId) {
    var gridvalue = Ext.getCmp(
      "gridHsnMapitemGridgeneration"
    ).getStore();
    gridvalue.baseParams = {
      brand: brand,
      category: category,
      subcategory: subcategory,
      archive: archive,
      merchantId:merchantId
    };
    gridvalue.load();
  };
  var productHSNGrid = function (cid) {
    var vendorcol = venderItemColmodel();
    var hsnmapstore = prdcthsnSearchStore();
    var vendorlist = prdctSearchHsntb();
    var exHsnComboStore = existingHasnComboStore();   

    var vendor_filter = new Ext.ux.grid.GridFilters({
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
          dataIndex: "hs_code",
        },{
          type: "string",
          dataIndex: "description",
        },
      ],
    });
    vendor_filter.remote = true;
    vendor_filter.autoReload = true;

    var vendorItemgrid = new Ext.grid.GridPanel({
      loadMask: true,
      store: hsnmapstore,
      colModel: vendorcol,
      tbar: vendorlist,
      plugins: [vendor_filter],
      region: "center",
      height: 400,
      frame: false,
      border: false,
      hideBorders: true,
      id: "gridHsnMapitemGridgeneration",
      sm: check_box,
      bbar: [
        { html: "MAP HSN : ", align: "left" },
        {
          xtype: "combo",
          store: exHsnComboStore,
          mode: "local",
          id: "existingHsns",
          allowBlank: true,
          emptyText: "HSN",
          hiddenName: "n[existingHsns]",
          displayField: "codeGst",
          valueField: "taxValueId",
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
              var hsnId = record.data.hsn_id;
              var taxValueId = record.data.taxValueId;
              var hsn_code = record.data.hsn_code;
              var hsnGst = record.data.hsnGst;
              Ext.getCmp("hsnNew").setValue(hsn_code);
              Ext.getCmp("taxNew").setValue(hsnGst);
              Ext.getCmp("hsnId").setValue(hsnId);
              Ext.getCmp("taxValueId").setValue(taxValueId);              
            },
          },
        },
        {
          frame: false,
          border: false,
        },{
          html: "&nbsp;New HSN : &nbsp;",
        },
        {
          xtype: "textfield",
          id: "hsnNew",
          name: "hsnNew",
          anchor: "95%",
          tabIndex: 1,
          maxLength: 200,
        },{
          html: "&nbsp;TAX : &nbsp;",
        },
        {
          xtype: "numberfield",
          id: "taxNew",
          name: "taxNew",
          anchor: "95%",
          tabIndex: 2,
        },{
          xtype: "hidden",
          id: "hsnId",
          name: "hsnId",
        },{
          xtype: "hidden",
          id: "taxValueId",
          name: "taxValueId",
        },
        {
          frame: false,
          border: false,
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          xtype: "button",
          text: "Submit",
          handler: function () {
            var selectitem = Ext.getCmp(
              "gridHsnMapitemGridgeneration"
            )
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemarr = [];
            for (var i = 0; i < selectedcount; i++) {
              itemarr.push(selectitem[i].data.id);
            }
            if (selectedcount != 0) {
              Application.GS1Products.Cache.cid = cid;
              Application.GS1Products.Cache.itemarr = itemarr;
              Application.GS1Products.saveHSNtoCheckedItem(itemarr);
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please select a product and proceed.."
              );
            }
          },
        },
        "->",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
          xtype: "button",
          text: "Reset",
          tabIndex: 38,
          handler: function () {
            Ext.getCmp("existingHsns").reset();
            Ext.getCmp("taxNew").reset();
            Ext.getCmp("taxNew").reset();
            Ext.getCmp("hsnId").reset();
            Ext.getCmp("taxValueId").reset();
          },
        },
        {
          frame: false,
          border: false,
        },
        {
          frame: false,
          border: false,
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/force_logout.png",
          xtype: "button",
          text: "Close",
          tabIndex: 38,
          handler: function () {
            Ext.getCmp(
              "windowToMapHsntoItems"
            ).close();
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
    });
    return vendorItemgrid;
  };
  var categorySearchStore = function () {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listMatchedCatgeorys",
        method: "post",
      }),
      fields: ["retailCategory", "department", "category", "subCategory","sub_category_id",
        "business_type_id","parent_category_id","main_category"],
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
  var catgeorySearchGrid = function () {
    var catsearch_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "retailCategory",
        },{
          type: "string",
          dataIndex: "department",
        },{
          type: "string",
          dataIndex: "category",
        },{
          type: "string",
          dataIndex: "subCategory",
        },
      ],
    });
    catsearch_filter.remote = true;
    catsearch_filter.autoReload = true;
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      height: winsize.height * 0.5,
      width: winsize.width * 0.6,
      store: categorySearchStore(),
      autoScroll: true,
      plugins:[catsearch_filter],
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforCategorySearch",
      tbar:[{
            html: "&nbsp;Search for : &nbsp;",
          },{
            xtype: "textfield",
            id: "searchCategory",
            name: "searchCategory",
            anchor: "98%",
            tabIndex: 1,
            maxLength: 200,
          },{
          icon: IMAGE_BASE_PATH + "/default/icons/search.png",
          xtype: "button",
          text: "Search",
          id:"catProductSerach",
          tabIndex: 37,
          handler: function () {
            var searchCategory = Ext.getCmp("searchCategory").getValue();
            Ext.getCmp('gridpanelforCategorySearch').getStore().load({
              params:{
                searchCategory : searchCategory
              }
            });
          },
        }],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Retail Category",
          dataIndex: "retailCategory",
          sortable: true,
          tooltip: "Retail Category",
          hideable: false,
          width: 80,
        },
        {
          header: "Department",
          dataIndex: "department",
          sortable: true,
          align: "right",
          tooltip: "Department",
          width: 80,
          hideable: false,
        },
        {
          header: "Catgeory",
          dataIndex: "category",
          sortable: true,
          tooltip: "Catgeory",
          width: 80,
          hideable: false,
        },{
          header: "Sub Catgeory",
          dataIndex: "subCategory",
          sortable: true,
          tooltip: "Sub Catgeory",
          width: 80,
          hideable: false,
        },        
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          sortable: false,
          groupable: false,
          tooltip: "Action",
          items: [{
              icon: IMAGE_BASE_PATH + "/default/icons/Forward.png",
              tooltip: "Choose Category",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                var business_type_id = record.get("business_type_id");
                var retailCategory = record.get("retailCategory");
                var parent_category_id = record.get("parent_category_id");
                var department = record.get("department");
                var main_category = record.get("main_category");
                var category = record.get("category");
                var sub_category_id = record.get("sub_category_id");
                var subCategory = record.get("subCategory");
                Ext.getCmp("primary_businessType").setValue(business_type_id);
                Ext.getCmp("primary_businessType").setRawValue(retailCategory);
                Ext.getCmp('parent_category').getStore().baseParams.primaryBt = business_type_id;
                Ext.getCmp('parent_category').getStore().load();
                Ext.getCmp('parent_category').setValue(parent_category_id);
                Ext.getCmp('parent_category').setRawValue(department);
                Ext.getCmp('main_category').getStore().baseParams.department = parent_category_id;
                Ext.getCmp('main_category').getStore().load();
                Ext.getCmp('main_category').setValue(main_category);
                Ext.getCmp('main_category').setRawValue(category);
                Ext.getCmp('sub_category').getStore().baseParams.category = main_category;
                Ext.getCmp('sub_category').getStore().load();
                Ext.getCmp('sub_category').setValue(sub_category_id);
                Ext.getCmp('sub_category').setRawValue(subCategory);
                
                Ext.getCmp('windowToSearchCategory').close();
              },
            }]
        },{ width: 20, dataIndex: " " }
      ],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {          
        },
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _dispatchgridPanel;
  };
  return {
    Cache: {},
    mapProducts: function (cid) {
      current_type = 1;
      var resultWindow = new Ext.Window({
        id: "windowFinascopStockAddvenderitemCreatevendoritem",
        title: "Map Imported Products with Category",
        shadow: false,
        height: 600,
        width: winsize.width * 0.85,
        layout: "border",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [productsGrid(cid)],
        buttons: [],
        listeners: {
          afterrender: function () {},
        },
      });

      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    },
    archiveCheckedItem: function (itemarr) {
      var brand = Ext.getCmp("gs1FilterBrand").getValue();
      var gcategory = Ext.getCmp("gs1FilterCategory").getValue();
      var gsubCategory = Ext.getCmp("gs1FilterSubCategory").getValue();
      var sgId = Ext.getCmp("merchantId").getValue();
      if (Ext.getCmp("showBaseArchived").getValue() == true) {
        var archive = 1;
      } else {
        var archive = 0;
      }

      Ext.Ajax.request({
        url: modURL + "&op=archiveBaseProducts",
        method: "post",
        params: {
          itemarr: Ext.encode(itemarr),
        },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {
            filterItems(brand, gcategory, gsubCategory, archive,sgId);
          }
        },
      });
    },
    saveCheckedItem: function (itemarr) {
      var retailCategory = Ext.getCmp("primary_businessType").getValue();
      var department = Ext.getCmp("parent_category").getValue();
      var category = Ext.getCmp("main_category").getValue();
      var subCategory = Ext.getCmp("sub_category").getValue();

      var brand = Ext.getCmp("gs1FilterBrand").getValue();
      var gcategory = Ext.getCmp("gs1FilterCategory").getValue();
      var gsubCategory = Ext.getCmp("gs1FilterSubCategory").getValue();
      var sgId = Ext.getCmp("merchantId").getValue();
      if (Ext.getCmp("showBaseArchived").getValue() == true) {
        var archive = 1;
      } else {
        var archive = 0;
      }

      Ext.Ajax.request({
        url: modURL + "&op=mapMastertoProducts",
        method: "post",
        params: {
          itemarr: Ext.encode(itemarr),
          retailCategory: retailCategory,
          department: department,
          category: category,
          subCategory: subCategory,
        },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {
            /*Ext.getCmp("gs1FilterBrand").reset();
            Ext.getCmp("gs1FilterCategory").reset();
            Ext.getCmp("gs1FilterSubCategory").reset();
			
			Ext.getCmp("primary_businessType").reset();
			Ext.getCmp("parent_category").reset();
			Ext.getCmp("main_category").reset();
			Ext.getCmp("sub_category").reset();*/
            filterItems(brand, gcategory, gsubCategory, archive,sgId);
          }
        },
      });
    },
    mapGs1Products: function () {
      var main_panel_id = "main_panelScheduledJobs";
      var main_panel = Ext.getCmp(main_panel_id);
      var title = "Convert Source Products";
      if (Ext.isEmpty(main_panel)) {
        main_panel = gs1ProductsPanel(main_panel_id, "", title);
        Application.UI.addTab(main_panel);
        main_panel.doLayout();
      } else {
        Application.UI.addTab(main_panel);
      }
    },
    mapGs1SkippedProducts: function () {
      var main_panel_id = "main_panelSkippedProducts";
      var main_panel = Ext.getCmp(main_panel_id);
      var title = "Manage Skipped Products";
      if (Ext.isEmpty(main_panel)) {
        main_panel = gs1ProductsPanel(main_panel_id, "skip", title);
        Application.UI.addTab(main_panel);
        main_panel.doLayout();
      } else {
        Application.UI.addTab(main_panel);
      }
    },
    createItemMasterName: function (itemarr, ind) {
      var masterItemStore = masterItemComboStore();
      var addNewReturnOrderWindow = new Ext.Window({
        id: "windowRetalineItemMaster",
        iconCls: "vender-items",
        shadow: false,
        width: winsize.width * 0.8,
        height: winsize.height * 0.8,
        title: "Item Master",
        layout: "border",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [retalineItemMasterGrid(itemarr)],
        fbar: [
          {
            html: "&nbsp;Choose Item Master : &nbsp;",
          },
          {
            xtype: "combo",
            store: masterItemStore,
            mode: "local",
            id: "mstrFilterItem",
            allowBlank: true,
            fieldLabel: "Item",
            hiddenName: "mstrFilterItem",
            displayField: "item_name",
            valueField: "itemname_id",
            typeAhead: true,
            forceSelection: true,
            editable: true,
            minChars: 2,
            anchor: "98%",
            triggerAction: "all",
            lazyRender: true,
            tabIndex: 36,
            listeners: {
              select: function () {
                var value = Ext.getCmp("mstrFilterItem").getRawValue();
                Ext.getCmp("itemMasterNew").setValue(value);
              },
            },
          },
          {
            html: "&nbsp;Item Master : &nbsp;",
          },
          {
            xtype: "textfield",
            id: "itemMasterNew",
            name: "itemMasterNew",
            anchor: "98%",
            tabIndex: 1,
            maxLength: 200,
          },
          {
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
            text: "Save",
            handler: function () {
              var itemMasterName = Ext.getCmp("itemMasterNew").getValue();
              if (!Ext.isEmpty(itemMasterName)) {
                Ext.Ajax.request({
                  url: modURL + "&op=mapItemMastertoProducts",
                  method: "post",
                  params: {
                    itemarr: Ext.encode(itemarr),
                    itemMasterName: Ext.getCmp("itemMasterNew").getValue(),
                  },
                  success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true) {
                      Ext.getCmp("windowRetalineItemMaster").close();
                      Ext.getCmp(
                        "gs1_mapping_grid" + ind
                      ).getStore().baseParams.brand_id = Ext.getCmp(
                        "search_brand" + ind
                      ).getValue();
                      Ext.getCmp("gs1_mapping_grid" + ind)
                        .getStore()
                        .load();
                    }
                  },
                });
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Item master should be there."
                );
              }
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
    confirmItemMasterName: function (itemarr, ind) {
      Ext.Msg.confirm(
        "Notification",
        "Are you proceeding based on the generated item master ?",
        function (btn) {
          if (btn == "yes") {
            Ext.Ajax.request({
              url: modURL + "&op=confirmGeneratedMaster",
              method: "post",
              params: {
                itemarr: Ext.encode(itemarr),
              },
              success: function (resp) {
                var res = Ext.decode(resp.responseText);
                if (res.success === true) {
                  Application.example.msg("Success", res.msg);
                  Ext.getCmp(
                    "gs1_mapping_grid" + ind
                  ).getStore().baseParams.brand_id = Ext.getCmp(
                    "search_brand" + ind
                  ).getValue();
                  Ext.getCmp("gs1_mapping_grid" + ind)
                    .getStore()
                    .load();
                }
              },
            });
          }
        }
      );
    },
    searchProducts: function () {
      var resultWindow = new Ext.Window({
        id: "searchProduct",
        title: "Search Product and Export to Grozeo",
        shadow: false,
        height: 600,
        width: winsize.width * 0.85,
        layout: "border",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [searchProductsGrid()],
        buttons: [],
        listeners: {
          afterrender: function () {},
        },
      });

      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    },
    exportCheckedItem: function (itemarr) {
      //var mainbrand = Ext.getCmp("mainFilterBrand").getValue();

      Ext.Ajax.request({
        url: modProURL + "&op=exportAllData",
        method: "POST",
        waitMsg: "Processing",
        params: {
          isBilkExport: 1,
          itemarr: Ext.encode(itemarr),
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
    },
    importData: function () {
      var _prdctBankImportPanelId = "prodctBankImport";
      var __prdctBankImportPanelIdPanel = Ext.getCmp(_prdctBankImportPanelId);
      if (Ext.isEmpty(__prdctBankImportPanelIdPanel)) {
        __prdctBankImportPanelIdPanel = importDataMainPanel(
          _prdctBankImportPanelId
        );
        Application.UI.addTab(__prdctBankImportPanelIdPanel);
        __prdctBankImportPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__prdctBankImportPanelIdPanel);
      }
    },
    skipOrder: function (itemarr, ind) {
      var gs1SkipStore = gs1SkipStatusComboStore();
      var win_canc_id = "windowSkipOrder";
      var win_canc = Ext.getCmp(win_canc_id);
      if (Ext.isEmpty(win_canc)) {
        win_canc = new Ext.Window({
          id: win_canc_id,
          title: "Reason for skipping",
          layout: "fit",
          width: 430,
          height: 150,
          plain: true,
          constrainHeader: true,
          modal: true,
          frame: true,
          resizable: false,
          bodyStyle: { "background-color": "white" },
          items: new Ext.Panel({
            autoHeight: true,
            border: false,
            layout: "column",
            bodyStyle: { "background-color": "white", padding: "20px" },
            items: [
              {
                layout: "form",
                columnWidth: 1,
                border: false,
                labelAlign: "top",
                hideBorders: true,
                // bodyStyle: {"background-color": "white"},
                items: [
                  {
                    xtype: "combo",
                    store: gs1SkipStore,
                    mode: "local",
                    id: "skipReson",
                    allowBlank: true,
                    fieldLabel: "Reason",
                    hiddenName: "skipReson",
                    displayField: "name",
                    valueField: "id",
                    typeAhead: true,
                    forceSelection: true,
                    editable: true,
                    minChars: 2,
                    anchor: "98%",
                    triggerAction: "all",
                    lazyRender: true,
                    tabIndex: 36,
                  },
                ],
              },
            ],
          }),
          buttonAlign: "right",
          fbar: [
            {
              text: "Save",
              xtype: "button",
              //                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
              width: 80,
              handler: function () {
                var skipReason = Ext.getCmp("skipReson").getValue();
                Ext.Ajax.request({
                  url: modURL + "&op=archiveProduct",
                  method: "post",
                  params: {
                    type: "archive",
                    itemarr: Ext.encode(itemarr),
                    ind: ind,
                    skipReason: skipReason,
                  },
                  success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true) {
                      Application.example.msg("Success", "Saved successfully.");
                      Ext.getCmp("windowSkipOrder").close();
                      Ext.getCmp(
                        "gs1_mapping_grid" + ind
                      ).getStore().baseParams.brand_id = Ext.getCmp(
                        "search_brand" + ind
                      ).getValue();
                      Ext.getCmp("gs1_mapping_grid" + ind)
                        .getStore()
                        .load();
                    } else {
                      Ext.MessageBox.alert(
                        "Notification",
                        "Issue in skipping products.",
                        function (btn) {
                          Ext.getCmp(
                            "gs1_mapping_grid" + ind
                          ).getStore().baseParams.brand_id = Ext.getCmp(
                            "search_brand" + ind
                          ).getValue();
                          Ext.getCmp("gs1_mapping_grid" + ind)
                            .getStore()
                            .load();
                        }
                      );
                    }
                  },
                });
              },
            },
          ],
        });
      }
      win_canc.doLayout();
      win_canc.show(this);
      win_canc.center();
    },FinalzeProduct: function (ind,prdType) {      
      var gs1Id = Ext.getCmp("id" + ind).getValue();
        //if (hasMappedItemMaster > 0 && isBrandTrimmed > 0) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var gs1Form = Ext.getCmp("gs1ProductPanel" + ind).getForm();
        if (gs1Form.isValid()) {
          gs1Form.submit({
            url: modURL + "&op=updateGS1Products",
            waitMsg: "Saving Details....",
            waitTitle: "Please Wait...",
            params: {
              apikey: _SESSION.apikey,
              tstamp: t_stamp,             
            },
            success: function (response, action) {
              var tmp = Ext.decode(action.response.responseText);
              if (tmp.success === true && tmp.valid === true) {
                Application.example.msg("Success", tmp.msg);
                Ext.getCmp('windowToMapProductType').close();
                Application.MyphaProduct.createProductfromGs1(
                  gs1Id,prdType
                );
              } else if (
                tmp.success === true &&
                tmp.valid === false
              ) {
                Ext.Msg.alert("Error", tmp.message);
              } else if (
                tmp.success === false &&
                tmp.valid === false
              ) {
                Ext.Msg.alert("Error", tmp.message);
              } else {
                Ext.Msg.alert("Error", tmp.message);
              }
            },
            failure: function (elm, conf) {
              if (conf.failureType == "server") {
                var result = Ext.decode(conf.response.responseText);
                console.log(result.message);
                Ext.Msg.alert("Status", result.message);
              } else {
                Ext.Msg.alert(
                  "Notification",
                  "Please enter all required fields"
                );
              }
            },
          });
        } else {
          Ext.Msg.alert(
            "Notification",
            "Please enter all required fields"
          );
        }
      
    },showImages: function () {
      var gs1Id = arguments[0];
      var type = arguments[0];
      //var printOrderPanel = createprintInvoicePanel(order_auto_id, order_generated_id);
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var src =
        "?module=products_gs1&op=showImages&gs1Id=" + gs1Id + "&type=" + type;
      var printOrderWindowid = Ext.getCmp("printOrderWindow");
      if (Ext.isEmpty(printOrderWindowid)) {
        printOrderWindowid = new Ext.Window({
          id: "printOrderWindow",
          title: "Image Details",
          modal: true,
          height: 500,
          width: winsize.width * 0.7,
          shadow: false,
          resizable: false,
          items: [
            new Ext.Panel({
              layout: "fit",
              border: false,
              width: winsize.width * 0.7,
              autoScroll: true,
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
            }),
          ],
          buttons: [
            {
              text: "Cancel",
              iconCls: "my-icon61",
              handler: function () {
                printOrderWindowid.close();
              },
            },
          ],
          listeners: {
            close: function () {},
          },
        });
      }
      printOrderWindowid.doLayout();
      printOrderWindowid.show();
      printOrderWindowid.center();
    },viewProducImages:function(gsId,gtin){
      var mode = "Image";
      var _addnewItemsWindow = new Ext.Window({
        title: "Image Details",
        layout: "fit",
        height: winsize.height * 0.6,
        width: winsize.width * 0.8,
        id: "confirmProductImagesWindow",
        resizable: true,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [file_grid(gsId,gtin)],//fnEastPanel(mode)
        fbar: [{
          xtype: "button",
          text: "Confirm Images",
          iconCls: "list_users",
          handler: function () {
            console.log("itemBrandMappedCount");
            var selectitem = Ext.getCmp("splash_grid")
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemMasterItemsArr = [];
            var itemMasterImagesArr = [];
            var itemBrandMappedCount = 0;
            if (selectedcount > 0 && selectedcount <= 5) {
              for (var i = 0; i < selectedcount; i++) {
                itemMasterItemsArr.push(selectitem[i].data.id);  
                itemMasterImagesArr.push(selectitem[i].data.thumpimg_path);              
              }
              Application.GS1Products.confirmCheckedImages(gsId,gtin,itemMasterItemsArr,itemMasterImagesArr);
            }else{
              Ext.MessageBox.alert(
                "Notification",
                "Choose images but not exceed 5 images.."
              );
            }
          },
        }],
      });
      _addnewItemsWindow.doLayout();
      _addnewItemsWindow.show();
      _addnewItemsWindow.center();
    },confirmCheckedImages: function (gsId,gtin,itemMasterItemsArr,itemMasterImagesArr) {      
      Ext.Ajax.request({
        url: modURL + "&op=confirmSelectedImages",
        method: "post",
        params: {
          gsId:gsId,
          gtin:gtin,
          itemMasterItemsArr: Ext.encode(itemMasterItemsArr),
          itemMasterImagesArr: Ext.encode(itemMasterImagesArr),
        },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {
                Application.example.msg("Success", tmp.msg);     
                Ext.getCmp('confirmProductImagesWindow').close();          
              
          }
        },
      });
    },
    mapHSNtoProducts: function (cid) {
      current_type = 1;
      var resultWindow = new Ext.Window({
        id: "windowToMapHsntoItems",
        title: "Map Imported Products with HSN",
        shadow: false,
        height: 600,
        width: winsize.width * 0.85,
        layout: "border",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [productHSNGrid(cid)],
        buttons: [],
        listeners: {
          afterrender: function () {},
        },
      });

      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    },saveHSNtoCheckedItem: function (itemarr) {
      var hsnId = Ext.getCmp("hsnId").getValue();
      var hsnValue = Ext.getCmp("hsnNew").getValue();
      var taxValue = Ext.getCmp("taxNew").getValue();
      var taxValueId = Ext.getCmp("taxValueId").getValue();

      var brand = Ext.getCmp("gs1FilterBrand").getValue();
      var gcategory = Ext.getCmp("gs1FilterCategory").getValue();
      var gsubCategory = Ext.getCmp("gs1FilterSubCategory").getValue();
      var sgId = Ext.getCmp("merchantId").getValue();
      var archive = 0;

      Ext.Ajax.request({
        url: modURL + "&op=mapHSNtoProducts",
        method: "post",
        params: {
          itemarr: Ext.encode(itemarr),
          hsnId: hsnId,
          hsnValue: hsnValue,
          taxValue: taxValue,
          taxValueId:taxValueId,
          brandId:brand
        },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {            
            filterHsnItems(brand, gcategory, gsubCategory, archive,sgId);
          }
        },
      });
    },chooseProductType: function(ind,pdId){
      var resultWindow = new Ext.Window({
        id: "windowToMapProductType",
        title: "Choose Product Type",
        shadow: false,
        height: 100,
        width: winsize.width * 0.3,
        bodyStyle: { "background-color": "white" },
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [{
              bodyStyle: { "background-color": "white" },
              fieldLabel: "Type",
              xtype: "radiogroup",
              id: "isPrivate",
              items: [
                { boxLabel: "Merchant Product", name: "isPrivate", inputValue: 1 },
                { boxLabel: "Public Product", name: "isPrivate", inputValue: 0 },
              ],
              listeners: {
                change: function (event, checked) {
                  var radioid = Ext.getCmp("isPrivate").getValue();
                  
                },
              },
            }],
        buttons: [{
              text: "Cancel",
              iconCls: "my-icon61",
              handler: function () {
                resultWindow.close();
              }
            },{
              text: "Submit",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              handler: function () {
                var prdType = Ext.getCmp("isPrivate").getValue();
                Application.GS1Products.FinalzeProduct(ind,prdType);
              }
      }],
        listeners: {
          afterrender: function () {},
        },
      });

      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    },searchCategory: function(){
      var catSearchWindow = new Ext.Window({
        id: "windowToSearchCategory",
        title: "Search Catgeories",
        shadow: false,
        height: winsize.height * 0.5,
        width: winsize.width * 0.6,
        bodyStyle: { "background-color": "white" },
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [catgeorySearchGrid()],
        buttons: [],
        listeners: {
          afterrender: function () {},
        },
      });

      catSearchWindow.doLayout();
      catSearchWindow.show();
      catSearchWindow.center();
    }
  };
})();
