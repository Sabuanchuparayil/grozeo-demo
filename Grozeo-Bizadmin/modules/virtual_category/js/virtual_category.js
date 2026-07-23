Application.VirtualCategory = function () {
  var recs_per_page = 23;
  var modURL = "?module=virtual_category";
  var current_type;
  var winLoadMask;
  var winsize = Ext.getBody().getViewSize();
  var loadCount;
  //var winsize = getWindowSize();
  var onGridResize = function (cmp) {
    recs_per_page = finascop_update_recs_per_page(cmp);
  };

  var gridSelectionChangedcat = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelVirtualCategory")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelVirtualCategory")
        .getSelectionModel()
        .getSelections()[0].data.vc_id;
      Ext.getCmp("tabpanelVirtualCategory").setActiveTab(0);
      Application.VirtualCategory.Cache.vc_id = ID;
      Application.VirtualCategory.ViewMode(ID);
    } else {
      Application.VirtualCategory.Cache.vc_id = 0;
      Application.VirtualCategory.ViewMode(0);
    }
  };

  var ListmainVCPanel = function (id) {
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      id: id,
      title: "Virtual Categories",
      items: [mainGrid(), vcTabPanel()],
    });
    return panel;
  };
  var vcGridStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listVirtualCategory",
      method: "post",
      fields: [
        "vc_id",
        "vc_name",
        "vc_parentCategoryId",
        "vc_categoryId",
        "parent_category",
        "category_name",
        "vc_status",
        "hasImage",
      ],
      totalProperty: "totalCount",
      root: "data",
      listeners: {
        beforeload: function () {},
        load: function () {
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("gridpanelVirtualCategory")
              .getSelectionModel()
              .selectRow(0);
          }
          //console.log('loadCount: ' + loadCount);
        },
      },
    });
    return store;
  };
  var mainGrid = function () {
    var grid_store = vcGridStore();
    var customerGrid_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "vc_name",
        },
        {
          type: "string",
          dataIndex: "parent_category",
        },
        {
          type: "string",
          dataIndex: "category_name",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "vc_status",
        },
      ],
    });
    customerGrid_filter.remote = true;
    customerGrid_filter.autoReload = true;

    var SP_grid = new Ext.grid.GridPanel({
      store: grid_store,
      id: "gridpanelVirtualCategory",
      region: "center",
      width: winsize.width * 0.5,
      frame: true,
      border: false,
      layout: "fit",
      loadMask: true,
      plugins: [customerGrid_filter],
      columns: [
        {
          header: "Virtual Category",
          sortable: true,
          dataIndex: "vc_name",
          width: 175,
        },
        {
          header: "Department ",
          sortable: true,
          dataIndex: "parent_category",
          width: 175,
        },
        {
          header: "Category",
          sortable: true,
          dataIndex: "category_name",
          width: 175,
        },
        {
          header: "Status",
          dataIndex: "vc_status",
          sortable: true,
          tooltip: "Status",
        },
        {
          header: "Has Image",
          dataIndex: "hasImage",
          sortable: true,
          tooltip: "Has Image",
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
              vcActionMenu.showAt(e.getXY());
              //action
            },
          },
        },
      ],
      viewConfig: {
        forceFit: true,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      tbar: [
        {
          xtype: "button",
          text: "Create Virtual Category",
          tooltip: "Create Virtual Category",
          iconCls: "finascop_add",
          handler: function () {
            Application.VirtualCategory.addNewVirtualCategory(0);
          },
        },
      ],
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
          selectionchange: gridSelectionChangedcat,
        },
      }),
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return SP_grid;
  };
  var vcActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Edit Virtual Category",
        handler: function () {
          var vc_id = Ext.getCmp("gridpanelVirtualCategory")
            .getSelectionModel()
            .getSelections()[0].data.vc_id;

          Application.VirtualCategory.addNewVirtualCategory(vc_id);
        },
      },
      {
        text: "Add Items",
        handler: function () {
          addVendorItem(Application.VirtualCategory.Cache.vc_id);
        },
      },
      {
        text: "Upload Image",
        handler: function () {
          var vc_id = Ext.getCmp("gridpanelVirtualCategory")
            .getSelectionModel()
            .getSelections()[0].data.vc_id;

          var uploadtype = "virtualCategory";
          Ext.Ajax.request({
            url: modURL + "&op=getVCImage",
            method: "POST",
            params: {
              vc_id: vc_id,
            },
            success: function (res) {
              var tmp = Ext.decode(res.responseText);
              console.log("temp is -", tmp);
              if (tmp.data != "") {
                var img_url = tmp.data[0].image_url;
                Application.VirtualCategory.uploadimageCategory(
                  vc_id,
                  uploadtype,
                  img_url
                );
              } else {
                var img_url = "";
                Application.VirtualCategory.uploadimageCategory(
                  vc_id,
                  uploadtype,
                  img_url
                );
              }
            },
          });
        },
      },
      {
        text: "Status Change",
        handler: function () {
          var vc_id = Ext.getCmp("gridpanelVirtualCategory")
            .getSelectionModel()
            .getSelections()[0].data.vc_id;
          var vc_status = Ext.getCmp("gridpanelVirtualCategory")
            .getSelectionModel()
            .getSelections()[0].data.vc_status;
          Ext.Ajax.request({
            url: modURL + "&op=statusChange",
            method: "POST",
            waitMsg: "Processing",
            params: {
              vc_id: vc_id,
              vc_status: vc_status,
            },
            failure: function (response, options) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              Ext.MessageBox.alert("Error", "tmp.msg");
            },
            success: function (response, options) {
              var tmp = Ext.decode(response.responseText);

              if (tmp.success === true) {
                Application.example.msg("Success", tmp.msg);
                Ext.getCmp("gridpanelVirtualCategory").getStore().load();
              } else {
                Ext.MessageBox.alert("Error", tmp.msg);
              }
            },
          });
        },
      },
    ],
  });
  var vcTabPanel = function () {
    var panel = new Ext.TabPanel({
      region: "east",
      width: winsize.width * 0.5,
      height: winsize.height * 0.6,
      activeTab: 0,
      flex: 1,
      plain: true,
      frame: true,
      id: "tabpanelVirtualCategory",
      items: [
        {
          title: "Details",
          id: "property_grid_id",
          width: winsize.width * 0.5,
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="99%" class="details_view_table">',
            "<tr><th>Virtual Category :</th><td> {vc_name} </td></tr>",
            "<tr><th>In Home Menu:</th><td>  {vc_isHome}</td></tr>",
            "<tr><th>In Category List:</th><td>  {vc_isInCategory}</td></tr>",
            "<tpl if=\"vc_isInCategory == 'Yes'\">",
            "<tr><th>Department:</th><td>  {parent_category}</td></tr>",
            "<tr><th>Category:</th><td>  {category_name}</td></tr>",
            "</tpl>",
            "<tr><th>Status:</th><td>  {vc_status}</td></tr>",
            '<tpl if="image_url != null">',
            "<tpl if=\"image_url != ''\">",
            "<tr><td>",
            '<div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div>',
            "</td></tr>",
            "</tpl>",
            "</tpl>",
            "</table>",
            "</div>"
          ),
        },
        {
          title: "Items",
          frame: false,
          width: winsize.width * 0.6,
          border: false,
          items: [additemGrid()],
        },
      ],
      listeners: {
        tabchange: function (sd, tab) {},
      },
    });
    return panel;
  };
  var additemGrid = function () {
    var addItemStorenew = addItemStore();
    var vendoritem_filter = new Ext.ux.grid.GridFilters({
      //remote: true,
      local: true,
      filters: [
        {
          type: "string",
          dataIndex: "itemName",
        },
        {
          type: "string",
          dataIndex: "itemType",
        },
        {
          type: "string",
          dataIndex: "stit_brand_name",
        },
        {
          type: "string",
          dataIndex: "stit_quantity",
        },
        {
          type: "string",
          dataIndex: "least_package_type_name",
        },
      ],
    });
    vendoritem_filter.remote = true;
    vendoritem_filter.autoReload = true;
    var addItem = new Ext.grid.GridPanel({
      store: addItemStorenew,
      height: winsize.height * 0.7,
      frame: true,
      border: false,
      layout: "fit",
      region: "center",
      loadMask: true,
      plugins: [vendoritem_filter],
      id: "gridpanelVendorAdditem",
      columns: [
        {
          header: "Item Name",
          width: 400,
          dataIndex: "itemName",
          hideable: true,
          sortable: true,
        },
        {
          header: "Item Type",
          dataIndex: "itemType",
          hideable: true,
          sortable: true,
        },
        {
          header: "Sub Category",
          dataIndex: "stit_category_name",
          hideable: true,
          sortable: true,
        },
        {
          header: "Brand",
          dataIndex: "stit_brand_name",
          hideable: true,
          sortable: true,
        },
        {
          header: "Quantity",
          dataIndex: "stit_quantity",
          hideable: true,
          sortable: false,
        },
        {
          header: "Least Packing Unit",
          dataIndex: "least_package_type_name",
          hideable: true,
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
              iconCls: "remove-enquiry",
              tooltip: "Delete Order",
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                deleteItem(record.get("stpi_id"), record.get("vc_id"));
              },
            },
          ],
        },
      ],
      tbar: new Ext.Toolbar({
        items: [
          {
            xtype: "button",
            text: "Add Items",
            tooltip: "Add Items",
            iconCls: "add",
            handler: function () {
              addVendorItem(Application.VirtualCategory.Cache.vc_id);
            },
          },
        ],
      }),
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return addItem;
  };
  var deleteItem = function (id, vc_id) {
    Ext.MessageBox.confirm(
      "Confirm",
      "Do you want to remove this item?",
      function (btn, text) {
        if (btn == "yes") {
          Ext.Ajax.request({
            waitMsg: "Processing",
            method: "POST",
            url: modURL + "&op=deleteVCItem",
            params: {
              id: id,
            },
            success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              if (tmp.success === true) {
                Application.example.msg("Success", "Removed item");
                Ext.getCmp("gridpanelVendorAdditem")
                  .getStore()
                  .load({
                    params: {
                      vc_id: vc_id,
                    },
                  });
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
  var addItemStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      url: modURL + "&op=listitemvc",
      fields: [
        "itemType",
        "itemId",
        "itemName",
        "stit_type",
        "stpi_id",
        "stit_brand_name",
        "stit_quantity",
        "least_package_type_name",
        "vc_id",
        "stit_category_name",
      ],
      remoteSort: true,
      root: "data",
      totalProperty: "totalCount",
    });
    return store;
  };
  var addVendorItem = function (cid) {
    current_type = 1;
    var resultWindow = new Ext.Window({
      id: "windowFinascopStockAddvenderitemCreatevendoritem",
      title: "Virtual Category Items",
      //iconCls: 'vender-items',
      shadow: false,
      height: 400,
      width: 800,
      layout: "fit",
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      modal: true,
      items: [vendorGrid(cid)],
      buttons: [
        {
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
          text: "Cancel",
          handler: function () {
            Ext.getCmp(
              "windowFinascopStockAddvenderitemCreatevendoritem"
            ).close();
          },
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
          text: "Save",
          handler: function () {
            var selectitem = Ext.getCmp(
              "gridFinascopStockVenderitemGridgeneration"
            )
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var itemarr = [];
            for (var i = 0; i < selectedcount; i++) {
              itemarr.push(selectitem[i].data.stit_ID);
            }
            if (selectedcount != 0) {
              Application.VirtualCategory.Cache.cid = cid;
              Application.VirtualCategory.Cache.itemarr = itemarr;
              var itemType = Ext.getCmp(
                "radiobuttonFinascopStockId"
              ).getValue();
              Application.VirtualCategory.Cache.itemType = itemType;
              Application.VirtualCategory.saveCheckedItem(
                cid,
                itemarr,
                itemType
              );
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please check,Some box entries are not valid."
              );
            }
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

    resultWindow.doLayout();
    resultWindow.show();
    resultWindow.center();
  };
  var vendorGrid = function (cid) {
    var vendorcol = venderItemColmodel();
    var venderstore = venderItemStore();
    var vendorlist = vendortb();

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
      hideMode: "display",
      loadMask: true,
      store: venderstore,
      colModel: vendorcol,
      tbar: vendorlist,
      plugins: [vendor_filter],
      width: 800,
      height: 400,
      frame: false,
      border: false,
      hideBorders: true,
      //iconCls: 'icon-grid',
      id: "gridFinascopStockVenderitemGridgeneration",
      sm: check_box,
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
  });
  var venderItemColmodel = function () {
    var colmodel = new Ext.grid.ColumnModel({
      sortable: true,
      columns: [
        check_box,
        rowno,
        {
          header: "SKU Name",
          width: 200,
          dataIndex: "stit_SKU",
          sortable: true,
        },
        {
          header: "Product Master",
          dataIndex: "stit_itemName",
          hideable: true,
          sortable: true,
        },
        {
          header: "Brand",
          dataIndex: "stit_brand_name",
          hideable: true,
          sortable: true,
        },
        {
          header: "Category",
          dataIndex: "mainCategory",
          hideable: true,
          sortable: false,
        },
        {
          header: "Sub Category",
          dataIndex: "stit_category_name",
          hideable: true,
          sortable: false,
        },
      ],
    });
    return colmodel;
  };
  var venderItemStore = function () {
    var store = new Ext.data.JsonStore({
      method: "POST",
      url: modURL + "&op=vcitemlisting",
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
        "mainCategory",
        "department",
      ],
      listeners: {
        beforeload: function (thisStore, options) {
          thisStore.baseParams.current_type = current_type;
        },
      },
    });

    return store;
  };
  var vendortb = function () {
    var tbar = new Ext.Toolbar({
      //layout: 'column',
      style: "margin:5px 1px 5px 1px;",
      //labelWidth: 100,
      labelAlign: "left",
      frame: false,
      border: false,
      hideBorders: true,
      items: [
        {
          xtype: "radiogroup",
          width: 150,
          id: "radiobuttonFinascopStockId",
          //columnWidth: 0.25,
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
              var current_firstid = event.items.items[0].inputValue;
              var current_secondid = event.items.items[1].inputValue;
              //var current_thirdid = event.items.items[2].inputValue;
              var radioid = Ext.getCmp("radiobuttonFinascopStockId").getValue();

              if (radioid == current_secondid) {
                var item_name = "";
                //filterItems(item_name, radioid);
              } else if (radioid == current_firstid) {
                var item_name = "";
                //filterItems(item_name, radioid);
              }
            },
          },
        },
        {
          //columnWidth: 0.4,
          xtype: "textfield",
          id: "radiosearch",
          width: 500,
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
          // columnWidth: 0.025
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/search.png",
          xtype: "button",
          //columnWidth: 0.075,
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
          // columnWidth: 0.025
        },
        {
          icon: IMAGE_BASE_PATH + "/default/icons/reset.png",
          xtype: "button",
          //columnWidth: 0.075,
          text: "Reset",
          handler: function () {
            Ext.getCmp("radiosearch").reset();
            filterItems("", "");
            //Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getStore().reload();
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
  var categoryuploadForm = function (img) {
    return new Ext.Panel({
      height: "400",
      items: [
        new Ext.Panel({
          layout: "fit",
          id: "cat_main_image_panel",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<img width="200" id="demo123" height="200" src="{img_root}"></img>',
            "</div>"
          ),
        }),
        {
          xtype: "hidden",
          id: "aws_file_location",
          name: "aws_file_location",
        },
        {
          xtype: "hidden",
          id: "aws_file_bucket",
          name: "aws_file_bucket",
        },
        new Ext.form.FormPanel({
          id: "category_image_upload",
          layout: "form",
          fileUpload: true,
          autoHeight: true,
          frame: true,
          items: [
            {
              xtype: "hidden",
              id: "file_name",
              name: "file_name",
            },
            {
              xtype: "hidden",
              id: "albumBucketName",
              name: "albumBucketName",
            },
            {
              xtype: "hidden",
              id: "accessKey",
              name: "accessKey",
            },
            {
              xtype: "hidden",
              id: "secretKey",
              name: "secretKey",
            },
            {
              xtype: "hidden",
              id: "bucketRegion",
              name: "bucketRegion",
            },
            {
              xtype: "hidden",
              id: "oncompleteurl",
              name: "oncompleteurl",
            },
            {
              xtype: "hidden",
              id: "img_path_db",
              name: "img_path_db",
            },
            {
              xtype: "box",
              width: 200,
              height: 200,
              id: "exist_img_box",
              autoEl: { tag: "img", src: img, width: 200, height: 200 },
            },
          ],
          buttons: [
            {
              xtype: "fileuploadfield",
              id: "categoryimg_file",
              anchor: "98%",
              fieldLabel: "Select File",
              name: "file",
              allowBlank: true,
              buttonOnly: true,
              // hidden: true,
              buttonCfg: {
                text: "Choose Image",
                //iconCls: 'finascop_upload_file',
                width: 80,
              },
              validator: function (v) {
                if (v != "") {
                  v = v.toLowerCase();
                  var exp = /^.*\.(png|jpg|gif)$/i;
                  if (!exp.test(v)) {
                    Ext.Msg.alert("Notification", "Upload a valid image file");
                    return;
                    //return 'Upload a valid image file of format JPG.';
                  }

                  var categoryimg_file =
                    Ext.getCmp("categoryimg_file").getValue();
                  if (categoryimg_file == "") {
                    Ext.Msg.alert(
                      "Notification",
                      "Please choose a scanned file to upload"
                    );
                    return;
                  }

                  var category_image_upload = Ext.getCmp(
                    "category_image_upload"
                  ).getForm();
                  if (category_image_upload.isValid()) {
                    Ext.getCmp("exist_img_box").hide();
                    winLoadMask.show();
                    addPhoto();
                  }
                  return true;
                }
              },
            },
          ],
        }),
      ],
    });
    // });
  };
  function uuidv4() {
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
      /[xy]/g,
      function (c) {
        var r = (Math.random() * 16) | 0,
          v = c == "x" ? r : (r & 0x3) | 0x8;
        return v.toString(16);
      }
    );
  }
  function addPhoto() {
    var albumBucketName = Ext.getCmp("albumBucketName").getValue();
    var bucketRegion = Ext.getCmp("bucketRegion").getValue();
    var filepath = Ext.getCmp("oncompleteurl").getValue();
    AWS.config.update({
      region: bucketRegion,
      credentials: new AWS.Credentials(
        Ext.getCmp("accessKey").getValue(),
        Ext.getCmp("secretKey").getValue(),
        null
      ),
    });
    var s3 = new AWS.S3({
      apiVersion: "2006-03-01",
      params: { Bucket: albumBucketName },
    });
    var files = document.getElementById("categoryimg_file-file").files;
    if (!files.length) {
      winLoadMask.hide();
      return alert("Please choose a file to upload first.");
    }
    var file = files[0];
    var actualfileName = file.name;
    var file_Name = JSON.stringify(actualfileName).slice(1, -1);
    var fileExt = file_Name.split(".").pop();

    var fileName = uuidv4();
    fileName = fileName + "." + fileExt;

    s3.upload(
      {
        Key: filepath + fileName /*file_Name*/ /*from server*/,
        Body: file,
        ACL: "public-read",
      },
      function (err, data) {
        if (err) {
          winLoadMask.hide();
          var img_src = Ext.BLANK_IMAGE_URL;
          Ext.getCmp("cat_main_image_panel").update({ img_root: img_src });
          return Ext.Msg.alert(
            "Notification",
            "There was an error uploading your photo: " + err.message
          );
        }
        if (!Ext.isEmpty(data.Location)) {
          winLoadMask.hide();
          Application.example.msg(
            "Notification",
            "File uploaded successfully."
          );
          Application.VirtualCategory.UploadedFileLocation = data.Location;
          Application.VirtualCategory.UploadedFileBucket = data.Bucket;
          Ext.getCmp("aws_file_bucket").setValue(data.Bucket);
          Ext.getCmp("aws_file_location").setValue(data.Location);
          /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
          Ext.getCmp("cat_main_image_panel").update({
            img_root: Application.VirtualCategory.UploadedFileLocation,
          });
        }
      }
    );
  }
  var tolerancePanel = function (id) {
    var _adPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Tolerance",
      id: id,
      items: [toleranceGrid()],
    });
    return _adPanel;
  };
  var toleranceGridstore = function () {
    var _toleranceList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listtolerance",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "rtm_id",
          root: "data",
        },
        ["rtm_id", "rtm_value", "rtm_percentage", "rtm_default"]
      ),
      sortInfo: {
        field: "rtm_id",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("toleranceGridPanel").getSelectionModel().selectRow(0);
        },
      },
    });
    return _toleranceList;
  };

  var toleranceGrid = function () {
    var _toleranceGridstore = toleranceGridstore();
    var _toleranceFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "rtm_value",
        },
        {
          type: "string",
          dataIndex: "rtm_percentage",
        },
      ],
    });
    _toleranceFilter.remote = true;
    _toleranceFilter.autoReload = true;
    var _gridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _toleranceGridstore,
      //iconCls: 'money',
      id: "toleranceGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _toleranceFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Percentage",
          dataIndex: "rtm_percentage",
          sortable: true,
          tooltip: "Type",
          hideable: true,
        },
        {
          header: "Amount",
          id: "rtm_value_auto_exp",
          dataIndex: "rtm_value",
          sortable: true,
          tooltip: "Value",
          hideable: true,
        },
        {
          header: "Action",
          xtype: "actioncolumn",
          hideable: false,
          sortable: false,
          groupable: false,
          items: [
            {
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var _isDefault = data.rtm_default;
                if (_isDefault == 0) {
                  this.items[0].tooltip = "Set Default";
                  return "margindistributin_inactive";
                } else {
                  this.items[0].tooltip = "Clear Default";
                  return "margindistributin_active";
                }
              },
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Ext.Ajax.request({
                  waitMsg: "Processing",
                  url: modURL,
                  params: {
                    op: "setDefault",
                    rtm_id: record.get("rtm_id"),
                    rtm_percentage: record.get("rtm_percentage"),
                  },
                  failure: function (response, options) {
                    Ext.MessageBox.alert("Notification", ACTION_FAIL);
                  },
                  success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {
                      Application.example.msg("Notification", tmp.msg);
                      Ext.getCmp("toleranceGridPanel").getStore().reload();
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
      tbar: [
        { html: "&nbsp;Amount : &nbsp;" },
        {
          xtype: "numberfield",
          fieldLabel: "Value",
          id: "rtm_value",
          name: "rtm_value",
          anchor: "98%",
          allowBlank: false,
          width: 100,
          tabIndex: 501,
        },
        { html: "&nbsp;Percentage : &nbsp;" },
        {
          xtype: "numberfield",
          fieldLabel: "Percentage",
          id: "rtm_percentage",
          name: "rtm_percentage",
          anchor: "98%",
          allowBlank: false,
          width: 100,
          tabIndex: 502,
        },
        {
          xtype: "button",
          text: "Add",
          iconCls: "add",
          tabIndex: 503,
          handler: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
            if (
              !Ext.isEmpty(Ext.getCmp("rtm_value").getValue()) &&
              !Ext.isEmpty(Ext.getCmp("rtm_percentage").getValue())
            ) {
              Ext.Ajax.request({
                url: modURL + "&op=saveTolerance",
                method: "POST",
                params: {
                  rtm_value: Ext.getCmp("rtm_value").getValue(),
                  rtm_percentage: Ext.getCmp("rtm_percentage").getValue(),
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true) {
                    Application.example.msg("Success", tmp.msg);
                    Ext.getCmp("toleranceGridPanel").getStore().load();
                    Ext.getCmp("rtm_percentage").reset();
                    Ext.getCmp("rtm_value").reset();
                  } else {
                    Ext.MessageBox.alert("Notification", tmp.msg);
                  }
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
        store: _toleranceGridstore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionToleranceChanged,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("rtm_id");

          if (!Ext.isEmpty(ID)) {
            Application.VirtualCategory.Cache.rtm_id = ID;
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _toleranceGridstore.load();
        },
      },
    });
    return _gridPanel;
  };
  var gridSelectionToleranceChanged = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("toleranceGridPanel").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("toleranceGridPanel")
        .getSelectionModel()
        .getSelections()[0].data.rtm_id;
    }
  };
  var packMasterPanel = function (id) {
    var _pckMstrPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Package Master",
      id: id,
      items: [packageMasterGrid()],
    });
    return _pckMstrPanel;
  };
  var packageMasterGridstore = function () {
    var _packageMasterGridList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listPackageMaster",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "rpckm_id",
          root: "data",
        },
        [
          "rpckm_id",
          "rpckm_name",
          "rpckm_type",
          "rpckm_typeName",
          "rpckm_length",
          "rpckm_breadth",
          "rpckm_height","br_Name","store_group_name"
        ]
      ),
      sortInfo: {
        field: "rpckm_id",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("packageMasterGridGridPanel")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _packageMasterGridList;
  };

  var packageMasterGrid = function () {
    var _packageMasterGridGridstore = packageMasterGridstore();
    var _packageMasterFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "rpckm_name",
        },
        {
          type: "string",
          dataIndex: "rpckm_typeName",
        },
        {
          type: "string",
          dataIndex: "br_Name",
        },
        {
          type: "string",
          dataIndex: "store_group_name",
        },
      ],
    });
    _packageMasterFilter.remote = true;
    _packageMasterFilter.autoReload = true;
    var _pmgridPanel = new Ext.grid.GridPanel({
      layout: "fit",
      region: "center",
      frame: false,
      border: false,
      loadMask: true,
      store: _packageMasterGridGridstore,
      id: "packageMasterGridGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _packageMasterFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Package",
          dataIndex: "rpckm_name",
          sortable: true,
          tooltip: "Package",
          hideable: true,
        },
        {
          header: "Type",
          dataIndex: "rpckm_typeName",
          sortable: true,
          tooltip: "Type",
          hideable: true,
        },
        {
          header: "Length",
          dataIndex: "rpckm_length",
          sortable: true,
          tooltip: "Length",
          hideable: true,
        },
        {
          header: "Breadth",
          dataIndex: "rpckm_breadth",
          sortable: true,
          tooltip: "Breadth",
          hideable: true,
        },
        {
          header: "Height",
          dataIndex: "rpckm_height",
          sortable: true,
          tooltip: "Height",
          hideable: true,
        },{
          header: "Store Group",
          dataIndex: "store_group_name",
          sortable: true,
          tooltip: "Store Group",
          hideable: true,
        },{
          header: "Branch",
          dataIndex: "br_Name",
          sortable: true,
          tooltip: "Branch",
          hideable: true,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      tbar: [
        { html: "&nbsp;Package : &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: "Value",
          id: "rpckm_name",
          name: "rpckm_name",
          emptyText: "Enter Package Name",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 501,
        },
        { html: "&nbsp;Type : &nbsp;" },
        {
          xtype: "combo",
          displayField: "name",
          valueField: "id",
          mode: "local",
          id: "rpckm_type",
          name: "rpckm_type",
          forceSelection: true,
          fieldLabel: "Type",
          emptyText: "Choose Delivery Type",
          anchor: "98%",
          typeAhead: true,
          triggerAction: "all",
          lazyRender: true,
          editable: true,
          minChars: 2,
          tabIndex: 502,
          store: new Ext.data.JsonStore({
            fields: ["id", "name"],
            data: [
              { id: "1", name: "Quick" },
              { id: "2", name: "Courier" },
            ],
          }),
        },
        { html: "&nbsp;Length : &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: "Length",
          id: "rpckm_length",
          name: "rpckm_length",
          anchor: "98%",
          emptyText: " in cm",
          allowBlank: false,
          width: 100,
          tabIndex: 503,
        },
        { html: "&nbsp;Breadth : &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: "Breadth",
          id: "rpckm_breadth",
          name: "rpckm_breadth",
          anchor: "98%",
          allowBlank: false,
          emptyText: " in cm",
          width: 100,
          tabIndex: 504,
        },
        { html: "&nbsp;Height : &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: "Height",
          id: "rpckm_height",
          name: "rpckm_height",
          anchor: "98%",
          allowBlank: false,
          emptyText: " in cm",
          width: 100,
          tabIndex: 505,
        },/*     <?php if (user_access("finascop_branch", "isSuperAdmin")) { ?> */
        {
          xtype: "button",
          text: "Add",
          iconCls: "add",
          tabIndex: 506,
          handler: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
            if (
              !Ext.isEmpty(Ext.getCmp("rpckm_name").getValue()) &&
              !Ext.isEmpty(Ext.getCmp("rpckm_type").getValue())
            ) {
              Ext.Ajax.request({
                url: modURL + "&op=savePackageMaster",
                method: "POST",
                params: {
                  rpckm_name: Ext.getCmp("rpckm_name").getValue(),
                  rpckm_type: Ext.getCmp("rpckm_type").getValue(),
                  rpckm_length: Ext.getCmp("rpckm_length").getValue(),
                  rpckm_breadth: Ext.getCmp("rpckm_breadth").getValue(),
                  rpckm_height: Ext.getCmp("rpckm_height").getValue(),
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true) {
                    Application.example.msg("Success", tmp.msg);
                    Ext.getCmp("packageMasterGridGridPanel").getStore().load();
                    Ext.getCmp("rpckm_name").reset();
                    Ext.getCmp("rpckm_type").reset();
                    Ext.getCmp("rpckm_length").reset();
                    Ext.getCmp("rpckm_breadth").reset();
                    Ext.getCmp("rpckm_height").reset();
                  } else {
                    Ext.MessageBox.alert("Notification", tmp.msg);
                  }
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
              });
            } else {
              Ext.MessageBox.alert("Error", "Check the required fields");
            }
          },
        },/*<?php } ?> */
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _packageMasterGridGridstore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("rpckm_id");
        },
        resize: onGridResize,
        afterrender: function () {
          _packageMasterGridGridstore.load();
        },
      },
    });
    return _pmgridPanel;
  };

  var wholesalerPanel = function (id) {
    var _adPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Wholesaler",
      id: id,
      items: [wholesalerGrid()],
    });
    return _adPanel;
  };
  var wholesalerGridstore = function () {
    var _toleranceList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listwholesaler",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "br_ID",
          root: "data",
        },
        ["br_ID", "br_Name", "br_Phone", "branch_shortname", "br_pincode"]
      ),
      sortInfo: {
        field: "br_ID",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("wholesalerGridPanel").getSelectionModel().selectRow(0);
        },
      },
    });
    return _toleranceList;
  };
  var wholesalerGrid = function () {
    var _wholesalerGridstore = wholesalerGridstore();
    var _wholesalerFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "rtlr_code",
        },
        {
          type: "string",
          dataIndex: "rtlr_name",
        },
      ],
    });
    _wholesalerFilter.remote = true;
    _wholesalerFilter.autoReload = true;
    var _gridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _wholesalerGridstore,
      //iconCls: 'money',
      id: "wholesalerGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _wholesalerFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Name",
          dataIndex: "br_Name",
          sortable: true,
          tooltip: "Name",
          hideable: true,
        },
        {
          header: "Code",
          dataIndex: "branch_shortname",
          sortable: true,
          tooltip: "Code",
          hideable: true,
        },
        {
          header: "Phone",
          dataIndex: "br_Phone",
          sortable: true,
          tooltip: "Phone",
          hideable: true,
        },
        {
          header: "Postcode",
          dataIndex: "br_pincode",
          sortable: true,
          tooltip: "Postcode",
          hideable: true,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      tbar: [
        { html: "&nbsp;Code : &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: "Code",
          id: "rtlr_code",
          name: "rtlr_code",
          anchor: "98%",
          allowBlank: false,
          width: 100,
          tabIndex: 501,
        },
        {
          xtype: "button",
          text: "Add",
          iconCls: "add",
          tabIndex: 503,
          handler: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
           
            if (!Ext.isEmpty(Ext.getCmp("rtlr_code").getValue())) {
              Ext.Ajax.request({
                url: modURL + "&op=getWholesalerDetails",
                method: "POST",
                params: {
                  rtlr_code: Ext.getCmp("rtlr_code").getValue(),
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true) {
                    var brdetails = tmp.data.br_Name+','+tmp.data.br_Address;
                    Ext.MessageBox.confirm('Confirm', 'Do you want to convert '+brdetails+' as wholesaler ?', function (btn) {
                      if (btn == 'yes') {
                        Ext.Ajax.request({
                          url: modURL + "&op=saveWholesaler",
                          method: "POST",
                          params: {
                            rtlr_code: Ext.getCmp("rtlr_code").getValue(),
                          },
                          success: function (response) {
                            var tmp = Ext.decode(response.responseText);
                            if (tmp.success == true) {
                              Application.example.msg("Success", tmp.msg);
                              Ext.getCmp("wholesalerGridPanel").getStore().load();
                              Ext.getCmp("rtlr_code").reset();
                            } else {
                              Ext.MessageBox.alert("Notification", tmp.msg);
                            }
                          },
                          failure: function (response, options) {
                            Ext.MessageBox.alert("Notification", ACTION_FAIL);
                          },
                        });
                      }
                  });
                  } else {
                    Ext.MessageBox.alert("Notification", tmp.msg);
                  }
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
              });
              
            } else {
              Ext.MessageBox.alert("Error", "Add shortname and proceed.");
            }
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _wholesalerGridstore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
        },
        resize: onGridResize,
        afterrender: function () {
          _wholesalerGridstore.load();
        },
      },
    });
    return _gridPanel;
  };
  var sponseredProductPanel = function (id) {
    var _adPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Sponsored Products",
      id: id,
      items: [sponsereProductGrid()],
    });
    return _adPanel;
  };
  var sponseredProductGridstore = function () {
    var store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSponseredProducts",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "spId",
          root: "data",
        },
        [
          "sponsered_margin","spStatus","margin_difference","current_margin","discount_selling_price",
          "br_Name",
          "stit_SKU",
          "stit_itemName",
          "product_category",
          "stit_category_name",
          "stit_brand_name",
          "med_manufacturename",
          "category_name",
          "parent_category",
          "item_count",
          "mrp",
          "margin",
          "stit_GST",
          "fpod_customerRateHmDel",
          "fpod_customerRateCouDel",
          "fpod_customerRatePikup",
          "type",
          "spId",
          "retailPrice",
          "csPrice",
          "stit_quantity",
          "issponsered",
          "sponsered",
          "spId",
          "selling_price",
        ]
      ),
      sortInfo: {
        field: "spId",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("sponseredProductGridPanel")
            .getSelectionModel()
            .selectRow(0);
        },
        beforeload: function () {
          var prouctsWithLM = Ext.getCmp("prouctsWithLM").getValue();
          this.baseParams.prouctsWithLM = prouctsWithLM;
        },
      },
    });
    return store;
  };
  var sponsereProductGrid = function () {
    var _spnsrdPrdctGridstore = sponseredProductGridstore();
    var _spnsredPrdctFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "br_Name",
        },
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
          dataIndex: "product_category",
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
          dataIndex: "med_manufacturename",
        },
        {
          type: "string",
          dataIndex: "category_name",
        },
        {
          type: "string",
          dataIndex: "parent_category",
        },
        {
          type: "string",
          dataIndex: "sponsered",
        },
      ],
    });
    _spnsredPrdctFilter.remote = true;
    _spnsredPrdctFilter.autoReload = true;
    var _gridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _spnsrdPrdctGridstore,
      id: "sponseredProductGridPanel",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _spnsredPrdctFilter],
      tbar: [
        {
          xtype: "checkbox",
          checked: true,
          id: "prouctsWithLM",
          tabIndex: 102,
          inputValue:1,
          anchor: "99%",
          name: "prouctsWithLM",
          labelAlign: "right",
          boxLabel: "Show Margin Lowered Products",
          listeners: {
            check: function (checkbox, checked) {
              if (checked == true) {
                Ext.getCmp("sponseredProductGridPanel")
                  .getStore()
                  .load({
                    params: {
                      prouctsWithLM: Ext.getCmp("prouctsWithLM").getValue(),
                    },
                  });
              } else {
                Ext.getCmp("sponseredProductGridPanel").getStore()
                  .load({
                    params: {
                      prouctsWithLM: false,
                    },
                  })
              }
            },
          },
        },
      ],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          id: "sp_auto_exp",
          sortable: true,
          hideable: false,
          dataIndex: "stit_SKU",
          tooltip: "SKU",
          width: 200,
        },
        {
          header: "Wholesaler",
          sortable: true,
          hideable: false,
          dataIndex: "br_Name",
          tooltip: "Wholesaler",
        },
        {
          header: "Brand",
          sortable: true,
          hideable: true,
          dataIndex: "stit_brand_name",
          tooltip: "Brand",
          width: 200,
        },
        {
          header: "Department",
          sortable: true,
          hideable: true,
          dataIndex: "parent_category",
          tooltip: "Department",
        },
        {
          header: "Category",
          dataIndex: "category_name",
          sortable: true,
          hideable: true,
          tooltip: "Category",
        },
        {
          header: "Sub Category",
          dataIndex: "stit_category_name",
          sortable: true,
          hideable: false,
          tooltip: "Sub Category",
          hidden: true,
        },
        {
          header: "Product Master",
          sortable: true,
          hideable: false,
          dataIndex: "stit_itemName",
          tooltip: "Product Master",
        },
        {
          header: "Quantity",
          sortable: true,
          hideable: false,
          align: "right",
          dataIndex: "stit_quantity",
          tooltip: "Quantity",
        },
        {
          header: "GST",
          hidden: true,
          align: "right",
          dataIndex: "stit_GST",
          tooltip: "GST",
        },
        {
          header: MRP,
          sortable: true,
          align: "right",
          dataIndex: "mrp",
          tooltip: MRP,
        },
        {
          header: "Retailer Price",
          sortable: true,
          align: "right",
          dataIndex: "selling_price",
          tooltip: "Retailer Price",
        },{
          header: "Wholesale Price",
          sortable: true,
          align: "right",
          dataIndex: "discount_selling_price",
          tooltip: "Wholesale Price",
        },
        {
          header: "Approved Margin",
          sortable: true,
          align: "right",
          dataIndex: "sponsered_margin",
          tooltip: "Approved Margin",
        },{
          header: "Current Margin",
          sortable: true,
          align: "right",
          dataIndex: "current_margin",
          tooltip: "Current Margin",
        },{
          header: "Margin Difference",
          sortable: true,
          align: "right",
          dataIndex: "margin_difference",
          tooltip: "Margin Difference",
        },
        {
          header: "Courier",
          sortable: true,
          align: "right",
          hideable: false,
          dataIndex: "fpod_customerRateCouDel",
          tooltip: "Courier",
        },
        {
          header: "Manual",
          sortable: true,
          hideable: false,
          align: "right",
          dataIndex: "fpod_customerRateHmDel",
          tooltip: "Manual",
        },
        {
          header: "Pick up",
          sortable: true,
          hideable: false,
          align: "right",
          dataIndex: "fpod_customerRatePikup",
          tooltip: "Pick up",
        },
        {
          header: "Distributor",
          sortable: true,
          hidden: true,
          align: "right",
          dataIndex: "csPrice",
          tooltip: "Distributor",
        },
        {
          header: "Retailor",
          sortable: true,
          hidden: true,
          align: "right",
          dataIndex: "retailPrice",
          tooltip: "Retailor",
        },
        {
          header: "Sponsored",
          sortable: true,
          hideable: false,
          dataIndex: "sponsered",
          tooltip: "Sponsored",
        },{
          header: "Sponsored Status",
          sortable: true,
          hideable: false,
          dataIndex: "spStatus",
          tooltip: "Sponsored Status",
        },
        {
          xtype: "actioncolumn",
          header: "Actions",
          hideable: false,
          groupable: false,
          width: 80,
          items: [
            {
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var _isDefault = data.issponsered;
                if (_isDefault != 1) {
                  this.items[0].tooltip = "Select as Sponsered";
                  return "drinactive";
                } else {
                  this.items[0].tooltip = "Clear Sponsered";
                  return "dractive";
                }
              },
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                selectAsSponsered(
                  record.get("spId"),
                  record.get("issponsered")
                );
              },
            },{},{}
          ],
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: _spnsrdPrdctGridstore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
        },
        resize: onGridResize,
        afterrender: function () {
          _spnsrdPrdctGridstore.load();
        },
      },
    });
    return _gridPanel;
  };
  var selectAsSponsered = function (id, issponsered) {
    Ext.Ajax.request({
      waitMsg: "Processing",
      method: "POST",
      url: modURL + "&op=addItemasSponsered",
      params: {
        id: id,
        issponsered: issponsered,
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        if (tmp.success === true) {
          Application.example.msg("Success", "Item Added");
          //Ext.getCmp('sponseredProductGridPanel').getStore().load();
        }
      },
      failure: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        Ext.MessageBox.alert("Error", "Error occurred");
      },
    });
  };
  var rtItemStore = function () {
    var store = new Ext.data.JsonStore({
      fields: [
        "stit_ID",
        "stit_itemName",
        "stit_SKU",
        "packageName",
        "id",
        "fsbg_id",
        "fpod_leastSKUmrp",
      ],
      url: modURL + "&op=getItemName",
      autoLoad: true,
      method: "post",
    });
    return store;
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
  var mrpProductDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "mrpProductDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Product </th><td> {stit_SKU} </td></tr>',
        '<tr><th width="40%">Department </th><td> {deptName} </td></tr>',
        '<tr><th width="40%">Category </th><td> {categoryName} </td></tr>',
        '<tr><th width="40%">Sub Category </th><td> {stit_category_name} </td></tr>',
        '<tr><th width="40%">Brand </th><td> {stit_brand_name} </td></tr>',
        '<tr><th width="40%">Product Master </th><td> {stit_itemName} </td></tr>',
        '<tr><th width="40%">Variant </th><td> {stit_product_variant} </td></tr>',
        '<tr><th width="40%">Quantity </th><td> {stit_quantity} </td></tr>',
        "</table>",
        "</div>"
      ),
    });
  };
  var mainMrpProductPanel = function (id) {
    var brandStore = branddetStore();
    var itemsStore = rtItemStore();
    var panel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Manage "+MRP,
      id: id,
      iconCls: "scheduled",
      buttonAlign: "right",
      tbar: [
        { html: "&nbsp;Brand : &nbsp;" },
        {
          xtype: "combo",
          fieldLabel: "Brand",
          width: 100,
          id: "mrpbrand_id",
          hiddenName: "n[mrpbrand_id]",
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
              var mrpbrand_id = Ext.getCmp("mrpbrand_id").getValue();
              if (mrpbrand_id > 0) {
                Ext.getCmp("mrpItemId").reset();
                Ext.getCmp("mrpItemId").getStore().clearData();
                Ext.getCmp("mrpItemId")
                  .getStore()
                  .load({
                    params: {
                      mrpbrand_id: mrpbrand_id,
                    },
                  });
              }
            },
          },
        },
        { html: "&nbsp;SKU : &nbsp;" },
        {
          xtype: "combo",
          fieldLabel: "Items",
          id: "mrpItemId",
          name: "mrpItemId",
          labelStyle: mandatory_label,
          allowBlank: false,
          mode: "local",
          typeAhead: true,
          editable: true,
          anchor: "98%",
          store: itemsStore,
          triggerAction: "all",
          //hideTrigger: true,
          minChars: 1,
          selectOnFocus: false,
          displayField: "stit_SKU",
          valueField: "stit_ID",
          hiddenName: "mrpItemId",
          tabIndex: 702,
          listeners: {
            select: function (cmbo, record, index) {
              var itemId = Ext.getCmp("mrpItemId").getValue();
              if (itemId > 0) {
                Application.VirtualCategory.ViewItemDetails(itemId);
              }
            },
          },
        },
        { html: "&nbsp;"+MRP+": &nbsp;" },
        {
          xtype: "textfield",
          fieldLabel: MRP,
          width: 100,
          id: "itemMrp",
          name: "itemMrp",
          allowBlank: false,
          tabIndex: 703,
          anchor: "97%",
          listeners: {},
        },
        {
          xtype: "button",
          text: "Add",
          tabIndex: 705,
          anchor: "85%",
          iconCls: "finascop_add",
          style: "margin-top:7px;",
          handler: function () {
            if (
              !Ext.isEmpty(Ext.getCmp("mrpItemId").getValue()) &&
              !Ext.isEmpty(Ext.getCmp("itemMrp").getValue())
            ) {
              Ext.Ajax.request({
                url: modURL + "&op=addItemsMrps",
                method: "POST",
                params: {
                  mrpItemId: Ext.getCmp("mrpItemId").getValue(),
                  itemMrp: Ext.getCmp("itemMrp").getValue(),
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success === true && tmp.valid === true) {
                    Application.example.msg("Success", tmp.msg);
                    var visualsDescPanel = Ext.getCmp(
                      "mrpProductDetailsViewPanel"
                    );
                    visualsDescPanel.update("");
                    Ext.getCmp("gridpanelItemMrp").getStore().load();
                    Ext.getCmp("itemMrp").reset();
                    Ext.getCmp("mrpbrand_id").reset();
                    Ext.getCmp("mrpItemId").reset();
                  } else if (tmp.success === true && tmp.valid === false) {
                    Ext.Msg.alert("Notification.", tmp.msg);
                  } else if (tmp.success === false && tmp.img_valid === false) {
                    Ext.Msg.alert("Notification.", tmp.msg);
                  } else {
                    Ext.Msg.alert("Error", "Entered data is not valid.");
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
                "Please choose item and quantity"
              );
            }
          },
        },
      ],
      items: [
        new Ext.Panel({
          title: "Item Details",
          frame: false,
          border: true,
          region: "center",
          autoScroll: true,
          cls: "left_side_panel",
          id: "itemPanel",
          items: [mrpProductDetailsView()],
        }),
        new Ext.Panel({
          region: "east",
          frame: false,
          border: true,
          layout: "fit",
          autoScroll: false,
          title: "Products "+MRP,
          bodyStyle: { "background-color": "white" },
          id: "order_parent_panel",
          width: winsize.width * 0.65,
          items: [mrpPrductsGrid()],
        }),
      ],
      listeners: {
        afterrender: function () {},
      },
    });
    return panel;
  };
  var mrpPrductsGrid = function () {
    var addItemStorenew = mrpProductStore();
    var vendoritem_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
        {
          type: "string",
          dataIndex: "parent_category",
        },
        {
          type: "string",
          dataIndex: "category_name",
        },
        {
          type: "string",
          dataIndex: "stit_category_name",
        },
        {
          type: "string",
          dataIndex: "stit_brand_name",
        },
      ],
    });
    vendoritem_filter.remote = true;
    vendoritem_filter.autoReload = true;
    var addItem = new Ext.grid.EditorGridPanel({
      store: addItemStorenew,
      frame: true,
      border: false,
      loadMask: true,
      region: "center",
      plugins: [vendoritem_filter],
      id: "gridpanelItemMrp",
      tbar: new Ext.Toolbar({
        items: [
          {
            xtype: "radiogroup",
            id: "radiobuttonProductMrp",
            width: 450,
            items: [
              {
                id: "prouctsWithPrice",
                name: "gridselecter",
                tabIndex: 1024,
                inputValue: 1,
                checked: true,
                labelWidth: 300,
                boxLabel: "With Price",
                listeners: {
                  check: function (checkbox, checked) {
                    if (checked == true) {
                    }
                  },
                },
              },
              {
                id: "prouctsWithOutPrice",
                name: "gridselecter",
                tabIndex: 1025,
                labelWidth: 300,
                boxLabel: "With out Price",
                inputValue: 2,
                listeners: {
                  check: function (checkbox, checked) {
                    if (checked == true) {
                    }
                  },
                },
              },
              {
                id: "showAll",
                name: "gridselecter",
                tabIndex: 1025,
                labelWidth: 100,
                boxLabel: "Show All",
                inputValue: 3,
                listeners: {
                  check: function (checkbox, checked) {
                    if (checked == true) {
                    }
                  },
                },
              },{
                id: "prouctsWithPriceVerified",
                name: "gridselecter",
                tabIndex: 1024,
                inputValue: 4,
                labelWidth: 100,
                boxLabel: "Verified",
                listeners: {
                  check: function (checkbox, checked) {
                    if (checked == true) {
                    }
                  },
                },
              },
              {
                id: "prouctsWithPriceUnverified",
                name: "gridselecter",
                tabIndex: 1025,
                labelWidth: 100,                  
                checked: true,
                boxLabel: "Un Verified",
                inputValue: 5,
                listeners: {
                  check: function (checkbox, checked) {
                    if (checked == true) {
                    }
                  },
                },
              }
            ],
            listeners: {
              change: function (event, checked) {
                console.log("events", event);
                var current_firstid = event.items.items[0].inputValue;
                var current_sec = event.items.items[1].inputValue;
                var current_third = event.items.items[2].inputValue;
                console.log("current_firstid", current_firstid);

                var radioid = Ext.getCmp("radiobuttonProductMrp").getValue();

                Ext.getCmp("gridpanelItemMrp")
                  .getStore()
                  .load({
                    params: {
                      gridselecter: radioid,
                    },
                  });
              },
            },
          },
        ],
      }),
      columns: [
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
          header: MRP,
          width: 200,
          align: "right",
          dataIndex: "itemMrp",
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
        },{
          header: "Location",
          dataIndex: "location",
          hideable: true,
          sortable: true,
        },{
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          iconCls: "downarrow",
          tooltip: "Choose Actions",
          listeners: {
            click: function (a, grid, rowindex, e) {
              var record = grid.store.getAt(rowindex);
              grid.getSelectionModel().selectRow(rowindex);
              mrpActionMenu.showAt(e.getXY());
            },
          },
        }
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: addItemStorenew,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [vendoritem_filter],
      }),
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
        viewready: onGridResize,
        //afteredit: updateMrp,
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return addItem;
  };
  var mrpActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Edit "+MRP,
        handler: function () {
          var record = Ext.getCmp("gridpanelItemMrp").getSelectionModel().getSelections()[0];
          if(record.data.mrpVreify == '0'){
            modifyProductMrp(record,'edit'); 
          }else{
            Ext.MessageBox.alert("Notification", MRP+" already verified");
          }
          

        }
      },{
        text: "Add "+MRP,
        handler: function () {
          var record = Ext.getCmp("gridpanelItemMrp").getSelectionModel().getSelections()[0];
          modifyProductMrp(record,'add');  
        }
      },{
        text: "Verify "+MRP,
        handler: function () {
          var record = Ext.getCmp("gridpanelItemMrp").getSelectionModel().getSelections()[0];
          if(record.data.mrpVreify == '0'){
            modifyProductMrp(record,'verify'); 
          }else{
            Ext.MessageBox.alert("Notification", MRP+" already verified");
          }
          
         }
      }
    ],
  });
  var mrpLocationStoreFn = function () {
    var locationStore = new Ext.data.JsonStore({
        url: modURL + '&op=mrpLocations',
        method: 'post',
        fields: ['id', 'name'],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true
    });
    return locationStore;
};
  var modifyProductMrp = function (record,event) {    
    if (Ext.isEmpty(modifyProductMrp_window)) {
      var mrpLocationStore = mrpLocationStoreFn();
      var modifyProductMrp_window = new Ext.Window({
        id: "modifyProductMrp_window",
        layout: "fit",
        width: 350,
        title: "Modify "+MRP,
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
            id: "upPackedQty_form",
            labelAlign: "left",
            height: 100,
            items: [  
              {
                xtype: "hidden",
                id: "mrpId",
              },
              {
                xtype: "hidden",
                id: "itemStitId",
              },
              {
                xtype: "displayfield",
                hideLabel:true,
                id: "itemMrpSKU",
                value: record.data.stit_SKU,
                style: "padding-top:10px;font-weight:bold;padding-bottom:10px;",
              },{
                layout: 'column',
                border: false,
                items: [
                  {
                    columnWidth: .5,
                    layout: 'form',
                    border: false,
                    labelAlign: 'top',
                    items: [{
                      fieldLabel: MRP,
                      xtype: "numberfield",
                      minValue: 0,
                      anchor: '90%',
                      id: "itemMrp",
                      name: "itemMrp",
                      allowNegative: false,
                      allowDecimals: false,
                      tabIndex: 121,
                      allowBlank: false,
                    }]
                  },{
                    columnWidth: .5,
                    layout: 'form',
                    border: false,
                    labelAlign: 'top',
                    items: [{
                      fieldLabel: "Location",
                      anchor: '90%',
                      xtype: "textfield",
                      id: "itemMrpLocation",
                      hidden:true,
                      name: "itemMrpLocation",
                      tabIndex: 122,
                      allowBlank: false,
                    },{
                      xtype: 'combo',
                      fieldLabel: 'Location',
                      id: 'itemMrpLocationCombo',
                      hiddenName: 'itemMrpLocationCombo',
                      anchor: '90%',
                      displayField: 'name',
                      valueField: 'id',
                      triggerAction: 'all',
                      forceSelection: true,
                      selectOnFocus: true,
                      mode: 'local',
                      hidden:true,
                      typeAhead: true,
                      lazyRender: true,
                      editable: true,
                      minChars: 2,
                      tabIndex: 123,
                      store: mrpLocationStore,
                      listeners:{
                        select: function(){
                          var Location = Ext.getCmp("itemMrpLocationCombo").getRawValue();
                          Ext.getCmp('itemMrpLocation').setValue(Location);
                        }
                      }
                  }]
                  }
                ]
              },
                           
            ],listeners:{
              afterrender: function(){
                
                switch(event){
                  case 'add':
                    Ext.getCmp('itemMrp').setValue(0);
                    Ext.getCmp('itemMrpLocation').setValue('');
                    Ext.getCmp('itemMrpLocationCombo').show();
                    Ext.getCmp('itemMrpLocation').hide();
                    Ext.getCmp('mrpId').setValue(0);
                    Ext.getCmp('itemStitId').setValue(record.data.stit_ID);
                    break;
                  case 'edit':
                    Ext.getCmp('itemMrp').setValue(record.data.itemMrp);
                    Ext.getCmp('itemMrpLocationCombo').hide();
                    Ext.getCmp('itemMrpLocation').hide();
                    Ext.getCmp('itemMrpLocation').setValue(record.data.location);
                    Ext.getCmp('itemMrpLocationCombo').show();
                    Ext.getCmp('itemMrpLocationCombo').setValue(record.data.location);
                    Ext.getCmp('itemMrpLocationCombo').setRawValue(record.data.location);
                    Ext.getCmp('mrpId').setValue(record.data.id);
                    Ext.getCmp('itemStitId').setValue(record.data.stit_ID);
                    break;
                  case 'verify':
                    Ext.getCmp('itemMrp').setValue(record.data.itemMrp);
                    Ext.getCmp('itemMrp').setReadOnly(true);
                    Ext.getCmp('itemMrpLocation').show();
                    Ext.getCmp('itemMrpLocation').setValue(record.data.location);
                    Ext.getCmp('itemMrpLocation').setReadOnly(true);
                    Ext.getCmp('itemMrpLocationCombo').hide();
                    Ext.getCmp('mrpId').setValue(record.data.id);
                    Ext.getCmp('itemStitId').setValue(record.data.stit_ID);
                    break;
                }
                if (Ext.isEmpty(Ext.getCmp("itemMrpLocation").getValue())) {
                  Ext.getCmp("itemMrpLocationCombo").setValue('Pan India');
                  Ext.getCmp('itemMrpLocation').setValue('Pan India');
                }
                
              },load:function(){
                if (Ext.isEmpty(Ext.getCmp("itemMrpLocation").getValue())) {
                  Ext.getCmp("itemMrpLocationCombo").setValue('Pan India');
                  Ext.getCmp('itemMrpLocation').setValue('Pan India');
                }
              }
            }
          }),
        ],
        buttons: [
          {
            text: "Cancel",
            id: "btnCancel",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            iconCls: "my-icon1",
            tabIndex: 124,
            handler: function () {
              modifyProductMrp_window.close();
            },
          },
          {
            text: "Save",
            id: "btnsave",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            iconCls: "my-icon1",
            tabIndex: 125,
            handler: function () {
              updateMrp(event);
            },
          },
        ],
        
      });
    }
    
    modifyProductMrp_window.doLayout();
    modifyProductMrp_window.show(this);
    modifyProductMrp_window.center();
  };
  function updateMrp(event) {
    var opurl;
    switch(event){
      case 'add':
        opurl = "&op=addItemsMrps";
        break;
      case 'edit':
        opurl = "&op=addItemsMrps";
        break;
      case 'verify':
        opurl = "&op=verifyMrp";
        break;
    }
    Ext.Ajax.request({
      url: modURL + opurl,
      method: "POST",
      params: {
        event:event,
        id: Ext.getCmp('mrpId').getValue(),
        mrpItemId: Ext.getCmp('itemStitId').getValue(),
        itemMrp: Ext.getCmp('itemMrp').getValue(),
        location: Ext.getCmp('itemMrpLocation').getValue(),
      },
      success: function (response) {
        var tmp = Ext.decode(response.responseText);
        if (tmp.success === true && tmp.valid === true) {
          Application.example.msg("Success", tmp.msg);
          Ext.getCmp('modifyProductMrp_window').close();
          var radioid = Ext.getCmp("radiobuttonProductMrp").getValue();

                             /* Ext.getCmp('gridpanelItemMrp').getStore().load({
                                  params: {
                                      gridselecter: radioid
                                  }
                              });*/
        } else if (tmp.success === true && tmp.valid === false) {
          Ext.Msg.alert("Notification.", tmp.msg);
        } else if (tmp.success === false && tmp.img_valid === false) {
          Ext.Msg.alert("Notification.", tmp.msg);
        } else {
          Ext.Msg.alert("Error", "Entered data is not valid.");
        }
      },
      failure: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        Ext.MessageBox.alert("Error", tmp.message);
      },
    });
  }
  var mrpProductStore = function () {
    var store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listItemMrp",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "stit_ID",
          "stit_SKU",
          "stit_itemName",
          "product_category",
          "stit_category_name",
          "stit_brand_name",
          "med_manufacturename",
          "category_name",
          "parent_category",
          "itemMrp","location","mrpVreify",
          "stit_product_variant",
          "stit_quantity",
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
        beforeload: function (store, e) {
          var radioid = Ext.getCmp("radiobuttonProductMrp").getValue();
          this.baseParams.gridselecter = radioid;
        },
      },
    });
    return store;
  };
  var mrpProductPanel = function (id) {
    var _adPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Products",
      id: id,
      items: [mrpPrductsGrid()],
    });
    return _adPanel;
  };
  var ListmainPCPanel = function (id) {
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      id: id,
      title: "Private Categories",
      items: [mainPCGrid(), pcTabPanel()],
    });
    return panel;
  };
  var mainPCGrid = function(){

    var grid_store = pcGridStore();
    var customerGrid_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "vc_name",
        },
        {
          type: "string",
          dataIndex: "parent_category",
        },
        {
          type: "string",
          dataIndex: "category_name",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "vc_status",
        },
      ],
    });
    customerGrid_filter.remote = true;
    customerGrid_filter.autoReload = true;

    var SP_grid = new Ext.grid.GridPanel({
      store: grid_store,
      id: "gridpanelPrivateCategory",
      region: "center",
      width: winsize.width * 0.5,
      frame: true,
      border: false,
      layout: "fit",
      loadMask: true,
      plugins: [customerGrid_filter],
      columns: [ {
        header: "Store Group",
        sortable: true,
        dataIndex: "storeGroup"
      },
        {
          header: "Private Category",
          sortable: true,
          dataIndex: "vc_name",
          width: 175,
        },
        {
          header: "Department ",
          sortable: true,
          dataIndex: "parent_category",
          width: 175,
        },
        {
          header: "Category",
          sortable: true,
          dataIndex: "category_name",
          width: 175,
        },
        {
          header: "Status",
          dataIndex: "vc_status",
          sortable: true,
          tooltip: "Status",
        },
        {
          header: "Has Image",
          dataIndex: "hasImage",
          sortable: true,
          tooltip: "Has Image",
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
              vcActionMenu.showAt(e.getXY());
              //action
            },
          },
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
          selectionchange: gridSelectionChangedPC,
        },
      }),
      listeners: {
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return SP_grid;
  };
  var gridSelectionChangedPC = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelPrivateCategory")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelPrivateCategory")
        .getSelectionModel()
        .getSelections()[0].data.vc_id;
      Ext.getCmp("gridpanelPrivateCategory").setActiveTab(0);
      Application.VirtualCategory.Cache.vc_id = ID;
      Application.VirtualCategory.ViewModePC(ID);
    } else {
      Application.VirtualCategory.Cache.vc_id = 0;
      Application.VirtualCategory.ViewModePC(0);
    }
  };
  var pcGridStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listPrivateCategory",
      method: "post",
      fields: [
        "vc_id",
        "vc_name",
        "vc_parentCategoryId",
        "vc_categoryId",
        "parent_category",
        "category_name",
        "vc_status",
        "hasImage","storeGroup"
      ],
      totalProperty: "totalCount",
      root: "data",
      listeners: {
        beforeload: function () {},
        load: function () {
          loadCount++;
          if (loadCount == 1) {
            Ext.getCmp("gridpanelVirtualCategory")
              .getSelectionModel()
              .selectRow(0);
          }
          //console.log('loadCount: ' + loadCount);
        },
      },
    });
    return store;
  };
  var pcTabPanel = function () {
    var panel = new Ext.TabPanel({
      region: "east",
      width: winsize.width * 0.5,
      height: winsize.height * 0.6,
      activeTab: 0,
      flex: 1,
      plain: true,
      frame: true,
      id: "tabpanelPrivateCategory",
      items: [
        {
          title: "Details",
          id: "pcView",
          width: winsize.width * 0.5,
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="99%" class="details_view_table">',
            "<tr><th>Virtual Category :</th><td> {vc_name} </td></tr>",
            "<tr><th>In Home Menu:</th><td>  {vc_isHome}</td></tr>",
            "<tr><th>In Category List:</th><td>  {vc_isInCategory}</td></tr>",
            "<tpl if=\"vc_isInCategory == 'Yes'\">",
            "<tr><th>Department:</th><td>  {parent_category}</td></tr>",
            "<tr><th>Category:</th><td>  {category_name}</td></tr>",
            "</tpl>",
            "<tr><th>Status:</th><td>  {vc_status}</td></tr>",
            '<tpl if="image_url != null">',
            "<tpl if=\"image_url != ''\">",
            "<tr><td>",
            '<div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div>',
            "</td></tr>",
            "</tpl>",
            "</tpl>",
            "</table>",
            "</div>"
          ),
        },
        {
          title: "Items",
          frame: false,
          width: winsize.width * 0.6,
          border: false,
          items: [additemGrid()],
        },
      ],
      listeners: {
        tabchange: function (sd, tab) {},
      },
    });
    return panel;
  };
  var mrpVerifyProductPanel = function (id) {
    var _adPanel = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Verify "+MRP,
      id: id,
      items: [mrpVerifyGrid()],
    });
    return _adPanel;
  };
  var mrpProductVerifyStore = function () {
    var store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listItemMrptoVerify",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "stit_ID",
          "stit_SKU",
          "stit_itemName",
          "product_category",
          "stit_category_name",
          "stit_brand_name",
          "med_manufacturename",
          "category_name",
          "parent_category",
          "itemMrp","mrpVreify",
          "stit_product_variant",
          "stit_quantity","location"
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
        beforeload: function (store, e) {
          var radioid = Ext.getCmp("radiobuttonProductMrpVerify").getValue();
          this.baseParams.gridselecter = radioid;
        },
      },
    });
    return store;
  };
  var mrpVerifyGrid = function () {
    var addItemStorenew = mrpProductVerifyStore();
    var vendoritem_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
        {
          type: "string",
          dataIndex: "parent_category",
        },
        {
          type: "string",
          dataIndex: "category_name",
        },
        {
          type: "string",
          dataIndex: "stit_category_name",
        },
        {
          type: "string",
          dataIndex: "stit_brand_name",
        },
      ],
    });
    vendoritem_filter.remote = true;
    vendoritem_filter.autoReload = true;
    var addItem = new Ext.grid.EditorGridPanel({
      store: addItemStorenew,
      frame: true,
      border: false,
      loadMask: true,
      region: "center",
      plugins: [vendoritem_filter],
      id: "gridpanelItemMrpVerify",
      tbar: new Ext.Toolbar({
        items: [
          {
            xtype: "radiogroup",
            id: "radiobuttonProductMrpVerify",
            width: 450,
            items: [
              {
                id: "prouctsWithPriceVerified",
                name: "gridselecter",
                tabIndex: 1024,
                inputValue: 1,
                labelWidth: 200,
                boxLabel: "Verified",
                listeners: {
                  check: function (checkbox, checked) {
                    if (checked == true) {
                    }
                  },
                },
              },
              {
                id: "prouctsWithPriceUnverified",
                name: "gridselecter",
                tabIndex: 1025,
                labelWidth: 200,                  
                checked: true,
                boxLabel: "Un Verified",
                inputValue: 0,
                listeners: {
                  check: function (checkbox, checked) {
                    if (checked == true) {
                    }
                  },
                },
              }
            ],
            listeners: {
              change: function (event, checked) {
                console.log("events", event);
                var current_firstid = event.items.items[0].inputValue;
                var current_sec = event.items.items[1].inputValue;
                console.log("current_firstid", current_firstid);

                var radioid = Ext.getCmp("radiobuttonProductMrpVerify").getValue();

                Ext.getCmp("gridpanelItemMrpVerify")
                  .getStore()
                  .load({
                    params: {
                      gridselecter: radioid,
                    },
                  });
              },
            },
          },
        ],
      }),
      columns: [          
        {
          header: "SKU",
          width: 400,
          dataIndex: "stit_SKU",
          hideable: true,
          sortable: true,
        },{
          header: "Brand",
          width: 250,
          dataIndex: "stit_brand_name",
          hideable: true,
          sortable: true,
        }, {
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
          header: MRP,
          width: 200,
          align: "right",
          dataIndex: "itemMrp",
          hideable: true,
          sortable: true,
          /*editor: {
            allowBlank: false,
            xtype: "numberfield",
          },*/
        },{
          header: "Location",
          width: 250,
          dataIndex: "location",
          hideable: true,
          sortable: true,
        },{
          header: "Action",
          xtype: "actioncolumn",
          hideable: false,
          sortable: false,
          groupable: false,
          items: [
            {   
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var _isDefault = data.mrpVreify;
                if (_isDefault == 0) {
                  return "approve";
                } else {
                  return " ";
                }
              },             
              tooltip:"Verify "+MRP,
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Ext.MessageBox.confirm(
                  "Confirm",
                  "Do you want to confirm the current "+MRP+" to this item?",
                  function (btn, text) {
                    if (btn == "yes") {
                      Ext.Ajax.request({
                        waitMsg: "Processing",
                        url: modURL,
                        params: {
                          op: "verifyMrp",
                          id: record.get("id")
                        },
                        failure: function (response, options) {
                          Ext.MessageBox.alert("Notification", ACTION_FAIL);
                        },
                        success: function (response, options) {
                          eval("var tmp=" + response.responseText);
                          if (tmp.success === true) {
                            Application.example.msg("Notification", tmp.msg);
                            Ext.getCmp("gridpanelItemMrpVerify").getStore().reload();
                          }
                        },
                      });
                    }
                  }
                );
                
              },
            },
          ],
        }
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: addItemStorenew,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [vendoritem_filter],
      }),
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
        viewready: onGridResize,
        //afteredit: updateMrp,
        rowclick: function (grid, rowIndex, e) {},
      },
    });
    return addItem;
  };
  return {
    Cache: {},
    initVirtualCategory: function (type) {
      loadCount = 0;
      var panelId = "virtualCategoryMainPanel";
      var listVendor = Ext.getCmp(panelId);
      if (Ext.isEmpty(listVendor)) {
        listVendor = ListmainVCPanel(panelId);
        Application.UI.addTab(listVendor);
        listVendor.doLayout();
      } else {
        Application.UI.addTab(listVendor);
      }
    },
    addNewVirtualCategory: function (vc_id) {
      var title;
      if (vc_id > 0) {
        title = "Edit Virtual Category";
      } else {
        title = "Create Virtual Category";
      }

      var win_id = "windowAddNewVC";
      var win = Ext.getCmp(win_id);
      if (Ext.isEmpty(win)) {
        win = new Ext.Window({
          id: win_id,
          title: title,
          layout: "fit",
          width: winsize.width * 0.4,
          height: 250,
          plain: false,
          constrainHeader: true,
          modal: true,
          frame: true,
          border: false,
          resizable: false,
          items: new Ext.FormPanel({
            id: "formpanelVirtualCategory",
            autoHeight: true,
            frame: true,
            border: false,
            labelAlign: "top",
            bodyStyle: {
              "background-color": "F1F1F1",
              padding: "5px 5px 0px 5px",
            },
            //bodyStyle: {"padding": "5px"},
            items: [
              {
                xtype: "textfield",
                id: "vc_id",
                name: "n[vc_id]",
                hidden: true,
              },
              {
                xtype: "panel",
                layout: "column",
                frame: false,
                border: false,
                bodyStyle: {
                  "background-color": "F1F1F1",
                  padding: "5px 2px 0px 2px",
                },
                items: [
                  {
                    columnWidth: 1,
                    layout: "form",
                    frame: false,
                    border: false,
                    bodyStyle: {
                      "background-color": "F1F1F1",
                      padding: "5px 2px 0px 2px",
                    },
                    items: [
                      {
                        xtype: "textfield",
                        id: "vc_name",
                        name: "n[vc_name]",
                        fieldLabel: "Virtual Category",
                        allowBlank: false,
                        tabIndex: 101,
                        anchor: "100%",
                      },
                    ],
                  },
                ],
              },
              {
                xtype: "panel",
                columnWidth: 1,
                layout: "column",
                frame: false,
                border: false,
                bodyStyle: {
                  "background-color": "F1F1F1",
                  padding: "0px 2px 0px 3px",
                },
                items: [
                  {
                    columnWidth: 0.35,
                    layout: "form",
                    frame: false,
                    border: false,
                    bodyStyle: {
                      "background-color": "F1F1F1",
                      padding: "0px 2px 0px 3px",
                    },
                    items: [
                      {
                        xtype: "checkbox",
                        checked: false,
                        id: "vc_isHome",
                        tabIndex: 102,
                        anchor: "99%",
                        name: "n[vc_isHome]",
                        labelAlign: "right",
                        inputValue: 1,
                        boxLabel: "Include in Home Menu",
                      },
                    ],
                  },
                  {
                    columnWidth: 0.35,
                    layout: "form",
                    frame: false,
                    border: false,
                    bodyStyle: {
                      "background-color": "F1F1F1",
                      padding: "0px 2px 0px 2px",
                    },
                    items: [
                      {
                        xtype: "checkbox",
                        checked: false,
                        anchor: "99%",
                        id: "vc_isInCategory",
                        name: "n[vc_isInCategory]",
                        inputValue: 1,
                        labelAlign: "right",
                        tabIndex: 103,
                        boxLabel: "Show in Category List",
                        listeners: {
                          check: function (cbo, checked) {
                            if (checked == true) {
                              Ext.getCmp("vc_parentCategoryId").enable();
                              Ext.getCmp("vc_categoryId").enable();
                              Ext.getCmp(
                                "vc_parentCategoryId"
                              ).allowBlank = false;
                              Ext.getCmp("vc_categoryId").allowBlank = false;
                            } else {
                              //Ext.getCmp('vc_parentCategoryId').reset();
                              //Ext.getCmp('vc_categoryId').reset();
                              Ext.getCmp("vc_parentCategoryId").disable();
                              Ext.getCmp("vc_categoryId").disable();
                              Ext.getCmp(
                                "vc_parentCategoryId"
                              ).allowBlank = true;
                              Ext.getCmp("vc_categoryId").allowBlank = true;
                            }
                          },
                          change: function () {
                            if (
                              Ext.getCmp("vc_isInCategory").getValue() == false
                            ) {
                              Ext.getCmp("vc_parentCategoryId").reset();
                              Ext.getCmp("vc_categoryId").reset();
                            }
                          },
                        },
                      },
                    ],
                  },
                ],
              },
              {
                xtype: "panel",
                columnWidth: 1,
                layout: "column",
                frame: false,
                border: false,
                bodyStyle: {
                  "background-color": "F1F1F1",
                  padding: "10px 2px 10px 2px",
                },
                items: [
                  {
                    columnWidth: 0.35,
                    layout: "form",
                    frame: false,
                    border: false,
                    labelAlign: "top",
                    bodyStyle: {
                      "background-color": "F1F1F1",
                      padding: "10px 5px 10px 5px",
                    },
                    items: [
                      mkCombo({
                        type: "mypha_productparent_category",
                        value: "parent_category_id",
                        display: "parent_category",
                        name: "n[vc_parentCategoryId]",
                        fieldLabel: "Show Under",
                        emptyText: "Select Department",
                        tabIndex: 104,
                        anchor: "99%",
                        id: "vc_parentCategoryId",
                        listeners: false,
                        cx: "S_1",
                      }),
                    ],
                  },
                  {
                    columnWidth: 0.35,
                    layout: "form",
                    frame: false,
                    border: false,
                    bodyStyle: {
                      "background-color": "F1F1F1",
                      padding: "10px 5px 10px 5px",
                    },
                    items: [
                      mkCombo({
                        type: "mypha_productcategory",
                        value: "category_id",
                        display: "category_name",
                        name: "n[vc_categoryId]",
                        fieldLabel: "Category",
                        hideLabel: true,
                        emptyText: "Select Category",
                        allowBlank: true,
                        tabIndex: 105,
                        anchor: "99%",
                        id: "vc_categoryId",
                        listeners: false,
                        cx: "S_1",
                      }),
                    ],
                  },
                  {
                    columnWidth: 0.3,
                    layout: "form",
                    frame: false,
                    border: false,
                    bodyStyle: {
                      "background-color": "F1F1F1",
                      padding: "10px 2px 10px 2px",
                    },
                    items: [
                      mkCombo({
                        type: STATUS_COMBO_DATA,
                        value: "id",
                        display: "text",
                        anchor: "100%",
                        name: "n[vc_status]",
                        fieldLabel: "Status",
                        tabIndex: 106,
                        emptyText: "Set status..",
                        id: "vc_status",
                      }),
                    ],
                  },
                ],
              },
            ],
          }),
          buttons: [
            {
              text: "Cancel",
              id: "Cancel_btns",
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
              iconCls: "finascop_my-icon1",
              tabIndex: 108,
              handler: function () {
                win.close();
              },
            },
            {
              text: "Save",
              id: "save_btn",
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
              iconCls: "finascop_my-icon1",
              tabIndex: 107,
              handler: function () {
                Application.VirtualCategory.saveVirtualCategory();
              },
            },
          ],
          listeners: {
            afterrender: function () {
              if (Ext.getCmp("vc_isInCategory").getValue() == true) {
                Ext.getCmp("vc_parentCategoryId").enable();
                Ext.getCmp("vc_categoryId").enable();
                Ext.getCmp("vc_parentCategoryId").allowBlank = false;
                Ext.getCmp("vc_categoryId").allowBlank = false;
              } else {
                Ext.getCmp("vc_parentCategoryId").disable();
                Ext.getCmp("vc_categoryId").disable();
                Ext.getCmp("vc_parentCategoryId").allowBlank = true;
                Ext.getCmp("vc_categoryId").allowBlank = true;
              }
              var t = new Date();
              var t_stamp = t.format("YmdHis");
              winLoadMask = new Ext.LoadMask(
                Ext.getCmp("windowAddNewVC").getEl()
              );
              winLoadMask.msg = "Please wait...";
              if (vc_id > 0) {
                Ext.getCmp("formpanelVirtualCategory")
                  .getForm()
                  .load({
                    waitTitle: "Please Wait",
                    waitMsg: "Loading...",
                    url: modURL + "&op=getVCDetails",
                    params: {
                      vc_id: vc_id,
                      apikey: _SESSION.apikey,
                      tstamp: t_stamp,
                    },
                    success: function (form, action) {
                      var tmp = Ext.decode(action.response.responseText);
                      if (tmp.data.vc_categoryId == 0) {
                        Ext.getCmp("vc_categoryId").reset();
                      }
                      if (tmp.data.vc_parentCategoryId == 0) {
                        Ext.getCmp("vc_parentCategoryId").reset();
                      }
                    },
                    failure: function (form, action) {
                      Ext.Msg.alert("Error.", "This error");
                    },
                  });
              }
            },
          },
        });
      }
      win.doLayout();
      win.show(this);
      win.center();
    },
    saveVirtualCategory: function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var store_form = Ext.getCmp("formpanelVirtualCategory").getForm();
      if (store_form.isValid()) {
        store_form.submit({
          url: modURL + "&op=saveVirtualCategory",
          waitMsg: "Saving Details....",
          waitTitle: "Please Wait...",
          params: {
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          success: function (response, action) {
            var tmp = Ext.decode(action.response.responseText);
            if (tmp.success === true && tmp.valid === true) {
              Ext.getCmp("windowAddNewVC").close();
              Application.example.msg("Success", tmp.message);
              if (Application.VirtualCategory.VCAddEdit == "Add") {
                recs_per_page = updateRecsPerPage(
                  Ext.getCmp("gridpanelVirtualCategory")
                );
                Ext.getCmp("formpanelVirtualCategory").getForm().reset();
                Ext.getCmp("gridpanelVirtualCategory").store.reload({
                  params: {
                    start: 0,
                    limit: recs_per_page,
                  },
                });
              } else {
                Ext.getCmp(
                  "gridpanelVirtualCategory"
                ).selModel.getSelected().data.vc_id = tmp.data.vc_id;
                Ext.getCmp("gridpanelVirtualCategory").getStore().reload();
                Ext.getCmp("gridpanelVirtualCategory").getView().refresh();
              }
              Application.VirtualCategory.VCAddEdit = "";
              Application.VirtualCategory.ViewMode(tmp.data.vc_id);
            } else if (tmp.success === true && tmp.valid === false) {
              Ext.Msg.alert("Notification.", tmp.message);
            } else if (tmp.success === true && tmp.img_valid === false) {
              Ext.Msg.alert("Notification.", tmp.message);
            } else {
              Ext.Msg.alert("Error", tmp.message);
            }
          },
          failure: function (elm, conf) {
            if (conf.failureType === "server") {
              var result = Ext.decode(conf.response.responseText);
              Ext.Msg.alert("Error", result.message);
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please enter all required fields"
              );
            }
          },
        });
      } else {
        Ext.MessageBox.alert(
          "Notification",
          "Please enter all required fields"
        );
      }
    },
    ViewMode: function (data) {
      var vc_id = arguments[0];

      Ext.Ajax.request({
        url: modURL + "&op=vcDetailsView",
        method: "POST",
        params: { vc_id: vc_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("property_grid_id");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("property_grid_id").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      var _addItemgrid_store = Ext.getCmp("gridpanelVendorAdditem").getStore();
      _addItemgrid_store.load({
        params: {
          vc_id: vc_id,
        },
      });
    },
    saveCheckedItem: function (cid, itemarr) {
      var cid = Application.VirtualCategory.Cache.cid;
      var itemarr = Application.VirtualCategory.Cache.itemarr;
      var itemtype = Application.VirtualCategory.Cache.itemType;
      Ext.Ajax.request({
        url: modURL + "&op=saveitemVC",
        method: "post",
        params: { cid: cid, itemarr: Ext.encode(itemarr), itemtype: itemtype },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {
            Application.example.msg(
              "Success",
              "Item details has been saved successfully."
            );
            Ext.getCmp("gridpanelVendorAdditem").getStore().reload();
            Ext.getCmp(
              "windowFinascopStockAddvenderitemCreatevendoritem"
            ).close();
          } else {
            Ext.MessageBox.alert(
              "Notification",
              "Product(s) already mapped.",
              function (btn) {
                Ext.getCmp("gridpanelVendorAdditem").getStore().reload();
                Ext.getCmp(
                  "windowFinascopStockAddvenderitemCreatevendoritem"
                ).close();
              }
            );
          }
        },
      });
    },
    uploadimageCategory: function (rid, uploadtype, img_url) {
      if (uploadtype === "virtualCategory") {
        var main_img_panel = categoryuploadForm(img_url);
      }

      var window_id = "catuploadwindow";
      var catuploadwindow = new Ext.Window({
        id: window_id,
        title: "Upload Image",
        layout: "fit",
        width: 230,
        autoHeight: true,
        plain: true,
        constrainHeader: true,
        modal: true,
        frame: true,
        iconCls: "finascop_dataentry_receipt",
        resizable: false,
        closable: false,
        items: main_img_panel,
        listeners: {
          afterrender: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            winLoadMask = new Ext.LoadMask(
              Ext.getCmp("catuploadwindow").getEl()
            );
            winLoadMask.msg = "Please wait...";
            if (uploadtype === "virtualCategory") {
              Ext.getCmp("category_image_upload")
                .getForm()
                .load({
                  waitTitle: "Please Wait",
                  waitMsg: "Loading...",
                  url: modURL + "&op=get_catimg_s3_details",
                  params: {
                    rid: rid,
                    uploadtype: uploadtype,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                  },
                });
            }
          },
        },
        buttons: [
          {
            text: "Cancel",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            iconCls: "finascop_my-icon1",
            handler: function () {
              catuploadwindow.close();
            },
          },
          {
            text: "Upload",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
            iconCls: "finascop_my-icon1",
            id: "saveButton",
            handler: function () {
              if (uploadtype === "virtualCategory") {
                Application.VirtualCategory.saveVCategoryImage();
              }
            },
          },
        ],
      });
      catuploadwindow.doLayout();
      catuploadwindow.show(this);
      catuploadwindow.center();
    },
    saveVCategoryImage: function () {
      var bucket_name = Ext.getCmp("albumBucketName").getValue();
      var file_name = Ext.getCmp("file_name").getValue();

      if (bucket_name != "" && file_name != "") {
        Ext.Ajax.request({
          url: modURL + "&op=saveVCategoryImage",
          method: "POST",
          params: {
            vc_id: Application.VirtualCategory.Cache.vc_id,
            uploaded_file_name: file_name,
            bucket: bucket_name,
            filepath: Ext.getCmp("aws_file_location").getValue(),
            bucket_path: Ext.getCmp("oncompleteurl").getValue(),
          },
          success: function (resp) {
            var res = Ext.decode(resp.responseText);
            if (res.success === true) {
              Application.example.msg("Notification", "Image saved..");
              Ext.getCmp("catuploadwindow").close();
            } else {
              Ext.Msg.alert("Error", "Image not saved. Try again");
            }
          },
          failure: function (elm, conf) {
            if (conf.failureType === "server") {
              var result = Ext.decode(conf.response.responseText);
              Ext.Msg.alert("Notification", result.error);
            } else {
              var result = Ext.decode(conf.response.responseText);
              Ext.MessageBox.alert("Error", result.error);
            }
          },
        });
      }
    },
    initTolerance: function () {
      var _tolerancePanelId = "panelTolerance";
      var _tolerancePanel = Ext.getCmp(_tolerancePanelId);
      if (Ext.isEmpty(_tolerancePanel)) {
        _tolerancePanel = tolerancePanel(_tolerancePanelId);
        Application.UI.addTab(_tolerancePanel);
        _tolerancePanel.doLayout();
      } else {
        Application.UI.addTab(_tolerancePanel);
      }
    },
    initPackageMaster: function () {
      var _packMastrPanelId = "panelPackageMaster";
      var _packMastrPanelId = Ext.getCmp(_packMastrPanelId);
      if (Ext.isEmpty(_packMastrPanelId)) {
        _packMastrPanelId = packMasterPanel(_packMastrPanelId);
        Application.UI.addTab(_packMastrPanelId);
        _packMastrPanelId.doLayout();
      } else {
        Application.UI.addTab(_packMastrPanelId);
      }
    },
    initWholesaler: function () {
      var _wholesalerPanelId = "panelWholesaler";
      var _wholesalerPanel = Ext.getCmp(_wholesalerPanelId);
      if (Ext.isEmpty(_wholesalerPanel)) {
        _wholesalerPanel = wholesalerPanel(_wholesalerPanelId);
        Application.UI.addTab(_wholesalerPanel);
        _wholesalerPanel.doLayout();
      } else {
        Application.UI.addTab(_wholesalerPanel);
      }
    },
    initSponseredProduct: function () {
      var _sponseredProductPanelId = "panelSponseredProduct";
      var _sponseredProductPanel = Ext.getCmp(_sponseredProductPanelId);
      if (Ext.isEmpty(_sponseredProductPanel)) {
        _sponseredProductPanel = sponseredProductPanel(
          _sponseredProductPanelId
        );
        Application.UI.addTab(_sponseredProductPanel);
        _sponseredProductPanel.doLayout();
      } else {
        Application.UI.addTab(_sponseredProductPanel);
      }
    },
    initMrpProducts: function () {
      var panelId = "mrpforpdt";
      var mrpforpdt_main = Ext.getCmp(panelId);
      if (Ext.isEmpty(mrpforpdt_main)) {
        mrpforpdt_main = mrpProductPanel(panelId);
      }
      Application.UI.addTab(mrpforpdt_main);
      mrpforpdt_main.doLayout();
      return mrpforpdt_main;
    },
    ViewItemDetails: function () {
      var itemId = arguments[0];

      Ext.getCmp("itemPanel").setTitle("View Item Details");
      Ext.getCmp("mrpProductDetailsViewPanel").show();
      Ext.getCmp("itemPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=itemDetailsView",
        method: "POST",
        params: { itemId: itemId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("mrpProductDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("mrpforpdt").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("itemPanel").doLayout();
    },initPrivateCategory: function (type) {
      loadCount = 0;
      var panelId = "privateCategoryMainPanel";
      var listVendor = Ext.getCmp(panelId);
      if (Ext.isEmpty(listVendor)) {
        listVendor = ListmainPCPanel(panelId);
        Application.UI.addTab(listVendor);
        listVendor.doLayout();
      } else {
        Application.UI.addTab(listVendor);
      }
    },ViewModePC: function (data) {
      var vc_id = arguments[0];

      Ext.Ajax.request({
        url: modURL + "&op=vcDetailsView",
        method: "POST",
        params: { vc_id: vc_id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("pcView");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("pcView").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      var _addItemgrid_store = Ext.getCmp("gridpanelVendorAdditem").getStore();
      _addItemgrid_store.load({
        params: {
          vc_id: vc_id,
        },
      });
    },initMrpVerification: function () {
      var panelId = "mrpVerificationforpdt";
      var mrpverifypdt_main = Ext.getCmp(panelId);
      if (Ext.isEmpty(mrpverifypdt_main)) {
        mrpverifypdt_main = mrpVerifyProductPanel(panelId);
      }
      Application.UI.addTab(mrpverifypdt_main);
      mrpverifypdt_main.doLayout();
      return mrpverifypdt_main;
    },
  };
}();