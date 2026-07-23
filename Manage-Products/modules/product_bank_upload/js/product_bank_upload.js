Application.ProductBankUpload = (function () {
  var RECS_PER_PAGE = 23;
  var modURL = "?module=product_bank_upload";
  var promodURL = "?module=mypha_product";
  var winsize = Ext.getBody().getViewSize();
  var myMask = new Ext.LoadMask(Ext.getBody(), { msg: "Please wait..." });
  var current_type;
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var gridSelectionFsuChanged = function () {
    
  };
  var gridSelectionStreDataChanged = function(){
    if (!Ext.isEmpty(Ext.getCmp("gridpanelforstoreDataProducts").getSelectionModel().getSelections())) 
      Application.ProductBankUpload.Cache.fileSku = Ext.getCmp("gridpanelforstoreDataProducts").getSelectionModel().getSelections()[0].data.name;
  };
  var productBankUploadGridStore = function () {
    var _productBankUploadList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listproductBankUpload",
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
          "createdBy",
          "createdByName",
          "typeName",
          "totalCount","convertedCount","balanceCount","feedbackedCount","actualCount",
          "brand",
          "createdOn",
        ]
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
        beforeload: function (store, e) {
          if(Ext.getCmp('showAll').getValue() == true)
            var showAllcheck = 1;
          else
            var showAllcheck = 0;
          this.baseParams.showAll = showAllcheck;
        },
      },
    });
    return _productBankUploadList;
  };
  var productBankUploadMainPanel = function (id) {
    var _fsuPanel = new Ext.Panel({
      frame: true,
      hideBorders: true,
      layout: "fit",
      border: false,
      title: "Onboard Products",
      iconCls: "dispatch",
      id: id,
      items: [productBankUploadGrid()],
    });
    return _fsuPanel;
  };

  var productBankUploadGrid = function () {
    var _fsuGridStore = productBankUploadGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "createdByName",
        },
        {
          type: "string",
          dataIndex: "typeName",
        },
        {
          type: "string",
          dataIndex: "brand",
        },
      ],
    });
    _fsuFilter.remote = true;
    _fsuFilter.autoReload = true;
    var _fsuGridPanel = new Ext.grid.GridPanel({
      frame: true,
      border: false,
      loadMask: true,
      store: _fsuGridStore,
      id: "productBankUpload",
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
          header: "Date",
          dataIndex: "createdOn",
          sortable: true,
          tooltip: "Date",
          /*renderer: function (value, metadata, record) {
              dateret = Ext.util.Format.date(value, "d-m-Y H:i:s");
              return dateret;
            },*/
        },
        {
          header: "Brand / Store",
          dataIndex: "brand",
          sortable: true,
          tooltip: "Brand / Store",
        },
        {
          header: "Type",
          dataIndex: "typeName",
          sortable: true,
          tooltip: "Type",
        },
        {
          header: "Total Count",
          dataIndex: "totalCount",
          sortable: true,
          tooltip: "Total Count",
        },{
          header: "Actioned",
          dataIndex: "convertedCount",
          sortable: true,
          tooltip: "Actioned",
        },{
          header: "Balance",
          dataIndex: "balanceCount",
          sortable: true,
          tooltip: "Balance",
        },{
          header: "Feedbacked",
          dataIndex: "feedbackedCount",
          sortable: true,
          tooltip: "Feedbacked",
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
                Application.ProductBankUpload.viewUploadedProducts(
                  record.get("id"),
                  record.get("brand")
                );
              },
            },
            {
              icon: IMAGE_BASE_PATH + "/default/icons/Forward.png",
              tooltip: "Map Products",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Application.ProductBankUpload.mapUploadedProducts(
                  record.get("id"),
                  record.get("brand"),0
                );
              },
            },{
              icon: IMAGE_BASE_PATH + "/default/icons/download.png",
              tooltip: "Mapped Products",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Application.ProductBankUpload.downloadMappedProducts(
                  record.get("id"),
                  record.get("brand")
                );
              },
            }/* <?php if (user_access("product_bank_upload", "feedbackedProducts")) { ?> */,{
              icon: IMAGE_BASE_PATH + "/default/icons/log-view.jpg",
              tooltip: "Feedbacked Products",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);   
                Application.ProductBankUpload.mapUploadedProducts(
                  record.get("id"),
                  record.get("brand"),1
                );             
              },
            }/* <?php } ?> */
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
        afterrender: function () {},
      },
      tbar: [
        {
          xtype: "button",
          iconCls: "csv",
          text: "Upload Products",
          tooltip: "Upload Products",
          handler: function () {
            importCSV("HO", "Head Office");
          },
        },{
          xtype: "checkbox",
          checked: false,
          id: "showAll",
          name: "showAll",
          inputValue: 1,
          boxLabel: "Show All",
          listeners: {
            check: function (cb1, checked) {
              if (checked == true) {
                Ext.getCmp('productBankUpload').getStore().load({
                  params:{
                    showAll:1
                  }
                });
              } else {
                Ext.getCmp('productBankUpload').getStore().load({
                  params:{
                    showAll:0
                  }
                });
              }
            },
          },
        }
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
      title: "Onboard Products",
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
          frame: true,
          border: false,
          fileUpload: true,
          items: [
            {
              fieldLabel: "Type",
              xtype: "radiogroup",
              id: "isScrapData",
              hideLabel: true,
              items: [
                { boxLabel: "Brand Data", name: "isScrapData", inputValue: 0 },
                { boxLabel: "Store Data", name: "isScrapData", inputValue: 1 },
              ],
              listeners: {
                change: function (event, checked) {
                  var radioid = Ext.getCmp("isScrapData").getValue();
                  if (radioid == 1) {
                    Ext.getCmp("merchantId").show();
                    Ext.getCmp("upload_brand").hide();
                  } else {
                    Ext.getCmp("merchantId").hide();
                    Ext.getCmp("upload_brand").show();
                  }
                },
              },
            },
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
              listeners: {
                select: function (combo, record, index) {
                  Ext.getCmp("upload_brand").setValue(record.get("name"));
                },
              },
            },
            {
              xtype: "textfield",
              fieldLabel: "Brand/Store Name",
              id: "upload_brand",
              name: "upload_brand",
              allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
            },
            {
              fieldLabel: "Upload File (.xlsx):&nbsp;&nbsp;<a href='#' onclick='Application.ProductBankUpload.downloadUrl()' style='margin-left:90px; text-decoration: underline; color: red'>Download Sample Store Data File</a>",
              labelAlign: "top",
              xtype: "fileuploadfield",
              accept: ".xlsx",
              id: "excel_file",
              allowBlank: false,
              name: "excel_file",
              tabIndex: 1,
              msgTarget: "under",
              anchor: "98%",
              validator: function (v) {
                if (v != "") {
                  //var exp = /^.*.(.xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                  var exp = /^.*\.xlsx$/;
                  if (!exp.test(v)) {
                    return "Upload a valid file.";
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
                  var isScrapData = Ext.getCmp("isScrapData").getValue();
                  if (isScrapData == true) {
                    csv_form.submit({
                      url: modURL + "&op=uploadScrapcsvFile",
                      waitTitle: "Please Wait..",
                      waitMsg: "Saving data...",
                      params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                        branch: branch,
                        upload_brand: Ext.getCmp("upload_brand").getValue(),
                        merchantId: Ext.getCmp("merchantId").getValue(),
                      },
                      success: function (csv_form, action) {
                        var result = Ext.decode(action.response.responseText);
                        console.log("result", result);
                        if (result.valid === true && result.success === true) {
                          win_csv.close();
                          Application.ProductBankUpload.dispalyUploadedStock(
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
                          "Error occured while uploading. ",
                          function (btn) {
                            if (btn === "ok") {
                              Ext.Msg.hide();
                              win_csv.close();
                            }
                          }
                        );
                      },
                    });
                  } else {
                    csv_form.submit({
                      url: modURL + "&op=uploadStockcsvFile",
                      waitTitle: "Please Wait..",
                      waitMsg: "Saving data...",
                      params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                        branch: branch,
                        upload_brand: Ext.getCmp("upload_brand").getValue(),
                      },
                      success: function (csv_form, action) {
                        var result = Ext.decode(action.response.responseText);
                        console.log("result", result);
                        if (result.valid === true && result.success === true) {
                          win_csv.close();
                          Application.ProductBankUpload.dispalyUploadedStock(
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
                          "Error occured while uploading. ",
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
                }
              },
            },
          ],
        }),
      ],
    });
    win_csv.show();
    win_csv.doLayout();
    win_csv.center();
  };
  var _viewUploadedProductsDetailsGrid = function (fbiu_id) {
    var _vupFilter = new Ext.ux.grid.GridFilters({
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
    _vupFilter.remote = true;
    _vupFilter.autoReload = true;
    var _uploadedProductDetailsgridPanel = new Ext.grid.GridPanel({
      frame: true,
      border: false,
      loadMask: true,
      store: _uploadedProductDetailsStore(fbiu_id),
      iconCls: "money",
      width: winsize.width * 0.65,
      height: 500,
      bodyStyle: { "background-color": "white" },
      id: "uploadedProductDetailsgridPanelDetailsView",
      plugins: [_vupFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brand",
          dataIndex: "brand",
          sortable: true,
          tooltip: "Brand",
          hideable: false,
        },
        {
          header: "Name",
          dataIndex: "name",
          sortable: true,
          tooltip: "Name",
          hideable: false,
          width: 250,
        },
        {
          header: "GTIN",
          dataIndex: "gtin",
          sortable: true,
          tooltip: "GTIN",
          hideable: false,
        },{
          header: "HSN",
          dataIndex: "hs_code",
          sortable: true,
          tooltip: "HSN",
          hideable: true,
        },{
          header: "GST",
          dataIndex: "igst",
          sortable: true,
          tooltip: "GST",
          hideable: true,
        },{
          header: "Product Code",
          dataIndex: "sku_code",
          sortable: true,
          tooltip: "Product Code",
          hideable: true,
        },
        {
          header: "Product Group 1",
          dataIndex: "type",
          sortable: true,
          tooltip: "Product Group 1",
          hideable: true,
          hidden: true,
          width: 100,
        },
        {
          header: "Product Group 2",
          dataIndex: "category",
          sortable: true,
          tooltip: "Product Group 2",
          hideable: true,
          hidden: true,
        },
        {
          header: "Product Group 3",
          dataIndex: "sub_category",
          sortable: true,
          tooltip: "Product Group 3",
          hideable: true,
          hidden: true,
        },
      ],
      fbar: [],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("uploadedProductDetailsgridPanelDetailsView")
            .getStore()
            .load({
              params: {
                fbiu_id: fbiu_id,
              },
            });
        },
      },
    });
    return _uploadedProductDetailsgridPanel;
  };
  var _uploadedProductDetailsStore = function (fbiu_id) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listProductUploadedItems",
        method: "post",
      }),
      fields: [
        "id",
        "brand",
        "name",
        "gtin",
        "type",
        "category",
        "sub_category","hs_code","igst","sku_code"
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

  var _viewUploadedTPProductsDetailsGrid = function (fbiu_id) {
    var _uploadedTPProductDetailsgridPanel = new Ext.grid.GridPanel({
      frame: true,
      border: false,
      loadMask: true,
      store: _uploadedTPProductDetailsStore(fbiu_id),
      iconCls: "money",
      width: winsize.width * 0.8,
      height: 500,
      bodyStyle: { "background-color": "white" },
      id: "uploadedTPProductDetailsgridPanelDetailsView",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          dataIndex: "stit_SKU",
          sortable: true,
          tooltip: "SKU",
          hideable: false,
          width: 150,
        },
        {
          header: "HSN",
          dataIndex: "stit_HSNCode",
          sortable: true,
          tooltip: "HSN",
          hideable: false,
        },
        {
          header: "GST",
          dataIndex: "stit_GST",
          sortable: true,
          tooltip: "GST",
          hideable: false,
        },
        {
          header: "HSN Id",
          dataIndex: "stit_hsnId",
          sortable: true,
          tooltip: "HSN Id",
          hideable: false,
        },
        {
          header: "Tax Id",
          dataIndex: "taxValueId",
          sortable: true,
          tooltip: "Tax Id",
          hideable: false,
        },
        {
          header: "Brand",
          dataIndex: "stit_brand_name",
          sortable: true,
          tooltip: "Brand",
          hideable: false,
        },
        {
          header: "Brand Id",
          dataIndex: "pdt_brand",
          sortable: true,
          tooltip: "Brand Id",
          hideable: false,
        },
        {
          header: "Category",
          dataIndex: "stit_category_name",
          sortable: true,
          tooltip: "Category",
          hideable: false,
        },
        {
          header: "Category Id",
          dataIndex: "product_category",
          sortable: true,
          tooltip: "Category Id",
          hideable: false,
        },
        {
          header: "Quantity",
          dataIndex: "stit_quantity",
          sortable: true,
          tooltip: "Quantity",
          hideable: false,
        },
        {
          header: "Package Weight",
          dataIndex: "stit_courierWt",
          sortable: true,
          tooltip: "Package Weight",
          hideable: false,
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          tooltip: "Choose Actions",
          items: [
            {
              icon: "./resources/images/submenuicons/view.png",
              tooltip: "View Images",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Application.ProductBankUpload.viewImages(
                  record.get("stit_ID"),
                  fbiu_id
                );
              },
            },
          ],
        },
      ],
      fbar: [],
      viewConfig: {
        forceFit: true,
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("uploadedTPProductDetailsgridPanelDetailsView")
            .getStore()
            .load({
              params: {
                fbiu_id: fbiu_id,
              },
            });
        },
      },
    });
    return _uploadedTPProductDetailsgridPanel;
  };
  var _uploadedTPProductDetailsStore = function (fbiu_id) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listTPProductUploadedItems",
        method: "post",
      }),
      fields: [
        "stit_ID",
        "stit_SKU",
        "stit_HSNCode",
        "stit_GST",
        "stit_hsnId",
        "stit_Description",
        "stit_long_description",
        "stit_product_variant",
        "stit_brand_name",
        "pdt_brand",
        "stit_category_name",
        "product_category",
        "featured",
        "popular",
        "courierDelivery",
        "directDelivery",
        "stit_itemId",
        "stit_courierWt",
        "taxValueId",
        "stit_quantity",
        "stit_qty",
        "stit_unit",
        "stit_orgin_country",
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
  var _viewimportedProductsDetailsGrid = function (fbiu_id) {
    var _importProductDetailsgridPanel = new Ext.grid.GridPanel({
      frame: true,
      border: false,
      loadMask: true,
      store: _importedProductDetailsStore(fbiu_id),
      iconCls: "money",
      width: winsize.width * 0.8,
      height: 500,
      bodyStyle: { "background-color": "white" },
      id: "importedProductDetailsgridPanelDetailsView",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          dataIndex: "SKU",
          sortable: true,
          tooltip: "SKU",
          hideable: false,
          width: 150,
        },
        {
          header: "HSN",
          dataIndex: "HSN_code",
          sortable: true,
          tooltip: "HSN",
          hideable: false,
        },
        {
          header: "GST",
          dataIndex: "GST",
          sortable: true,
          tooltip: "GST",
          hideable: false,
        },
        {
          header: "HSN Id",
          dataIndex: "hsnId",
          sortable: true,
          tooltip: "HSN Id",
          hideable: false,
        },
        {
          header: "Tax Id",
          dataIndex: "gstId",
          sortable: true,
          tooltip: "Tax Id",
          hideable: false,
        },
        {
          header: "Brand",
          dataIndex: "Brand_Name",
          sortable: true,
          tooltip: "Brand",
          hideable: false,
        },
        {
          header: "Brand Id",
          dataIndex: "BrandId",
          sortable: true,
          tooltip: "Brand Id",
          hideable: false,
        },
        {
          header: "Category",
          dataIndex: "Category_Name",
          sortable: true,
          tooltip: "Category",
          hideable: false,
        },
        {
          header: "Category Id",
          dataIndex: "CategoryId",
          sortable: true,
          tooltip: "Category Id",
          hideable: false,
        },
        {
          header: "Quantity",
          dataIndex: "Quantity",
          sortable: true,
          tooltip: "Quantity",
          hideable: false,
        },
        {
          header: "Unit",
          dataIndex: "Unit",
          sortable: true,
          tooltip: "Unit",
          hideable: false,
        },
        {
          header: "Country",
          dataIndex: "CountryOfOrgin",
          sortable: true,
          tooltip: "Country",
          hideable: false,
        },
        {
          header: "Package Weight",
          dataIndex: "ProductWeight",
          sortable: true,
          tooltip: "Package Weight",
          hideable: false,
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
              icon: "./resources/images/submenuicons/edit.png",
              tooltip: "View Details",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                if (record.get("isVerified") == 0) {
                  Application.ProductBankUpload.verifyProducts(
                    record.get("id"),
                    fbiu_id
                  );
                } else {
                  Ext.MessageBox.alert(
                    "Notification",
                    "Product already verified."
                  );
                }
              },
            },
          ],
        },
      ],
      fbar: [],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {
          if (record.get("isVerified") != 1) {
            return "finascop_indicateColLIGHTORANGE";
          } else {
            return "";
          }
        },
      },
      listeners: {
        afterrender: function () {
          Ext.getCmp("importedProductDetailsgridPanelDetailsView")
            .getStore()
            .load({
              params: {
                fbiu_id: fbiu_id,
              },
            });
        },
      },
    });
    return _importProductDetailsgridPanel;
  };
  var _importedProductDetailsStore = function (fbiu_id) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listImportedItems",
        method: "post",
      }),
      fields: [
        "Brand_Name",
        "BrandId",
        "Category_Name",
        "CategoryId",
        "countryId",
        "CountryOfOrgin",
        "Courier_Delivery",
        "createdBy",
        "createdOn",
        "Description",
        "Direct_Delivery",
        "Featured",
        "FoodType",
        "GST",
        "gstId",
        "HSN_code",
        "hsnId",
        "id",
        "isVerified",
        "merchantId",
        "Popular",
        "Product_Variant",
        "ProductWeight",
        "Quantity",
        "Short_description",
        "SI_No",
        "SKU",
        "uid",
        "Unit",
        "UnitId",
        "validation",
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

  var productThirdPartyGridStore = function () {
    var _productThirdPartyList = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listproductThirdParty",
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
          "createdBy",
          "createdByName",
          "typeName",
          "count",
          "brand",
          "createdOn",
          "isConfirmed",
          "status",
        ]
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
    return _productThirdPartyList;
  };
  var productThirdPartyMainPanel = function (id) {
    var _fsuPanel = new Ext.Panel({
      frame: true,
      hideBorders: true,
      layout: "fit",
      border: false,
      title: "Import Products",
      iconCls: "dispatch",
      id: id,
      items: [productThirdPartyGrid()],
    });
    return _fsuPanel;
  };

  var productThirdPartyGrid = function () {
    var _fsuGridStore = productThirdPartyGridStore();
    var _fsuFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "createdByName",
        },
        {
          type: "string",
          dataIndex: "typeName",
        },
        {
          type: "string",
          dataIndex: "brand",
        },
      ],
    });
    _fsuFilter.remote = true;
    _fsuFilter.autoReload = true;
    var _fsuGridPanel = new Ext.grid.GridPanel({
      frame: true,
      border: false,
      loadMask: true,
      store: _fsuGridStore,
      title: "Import Products",
      id: "productThirdParty",
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
          header: "ID",
          dataIndex: "id",
          sortable: true,
          tooltip: "ID",
        },
        {
          header: "Date",
          dataIndex: "createdOn",
          sortable: true,
          tooltip: "Date",
          /*renderer: function (value, metadata, record) {
              dateret = Ext.util.Format.date(value, "d-m-Y H:i:s");
              return dateret;
            },*/
        },
        {
          header: "Store Group",
          dataIndex: "brand",
          sortable: true,
          tooltip: "Store Group",
        },
        {
          header: "Type",
          dataIndex: "typeName",
          sortable: true,
          tooltip: "Type",
        },
        {
          header: "Count",
          dataIndex: "count",
          sortable: true,
          tooltip: "Count",
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
                if (record.get("status") == 1) {
                  if (record.get("isConfirmed") != "1") {
                    Application.ProductBankUpload.confirmImportProducts(
                      record.get("id"),
                      record.get("brand"),
                      "Confirm the listed items"
                    );
                  } else {
                    Application.ProductBankUpload.viewUploadedTPProducts(
                      record.get("id"),
                      record.get("brand")
                    );
                  }
                } else {
                  Ext.MessageBox.alert(
                    "Notification",
                    "Imported data has been rejected."
                  );
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
        pageSize: RECS_PER_PAGE,
        store: _fsuGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },
      tbar: [
        {
          xtype: "button",
          iconCls: "csv",
          text: "Import Products",
          tooltip: "Import Products",
          handler: function () {
            importExcel("HO", "Head Office");
          },
        },
      ],
    });
    return _fsuGridPanel;
  };
  var merchantStore = function () {
    var merchant_store = new Ext.data.JsonStore({
      url: modURL + "&op=getMerchantStore",
      fields: ["id", "name"],
      totalProperty: "totalCount",
      root: "data",
      autoload: true,
      remoteFilter: true,
      listeners: {},
    });
    return merchant_store;
  };
  var importExcel = function (branch, brName) {
    var win_csv = new Ext.Window({
      layout: "fit",
      width: 400,
      autoHeight: true,
      border: false,
      title: "Import Products",
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
                },
                {
                  fieldLabel: "Upload File (.xlsx)",
                  labelAlign: "top",
                  xtype: "fileuploadfield",
                  accept: ".xlsx",
                  id: "excel_file",
                  allowBlank: false,
                  name: "excel_file",
                  tabIndex: 1,
                  msgTarget: "under",
                  anchor: "98%",
                  validator: function (v) {
                    if (v != "") {
                      //var exp = /^.*.(.xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                      var exp = /^.*\.xlsx$/;
                      if (!exp.test(v)) {
                        return "Upload a valid file.";
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
                        url: modURL + "&op=uploadExcelFile",
                        waitTitle: "Please Wait..",
                        waitMsg: "Saving data...",
                        params: {
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp,
                          branch: branch,
                          merchantId: Ext.getCmp("merchantId").getValue(),
                          merchantName: Ext.getCmp("merchantId").getRawValue(),
                        },
                        success: function (csv_form, action) {
                          var result = Ext.decode(action.response.responseText);
                          console.log("result", result);
                          if (
                            result.success === true &&
                            result.valid === true
                          ) {
                            Application.ProductBankUpload.confirmImportProducts(
                              result.uploadId,
                              brName,
                              result.msg
                            );
                            Application.example.msg("Success", result.msg);
                            win_csv.close();
                            Ext.getCmp("productThirdParty").getStore().load();
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
                            "Error occured while uploading. ",
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
  var gstStorefn = function () {
    var gst_store = new Ext.data.JsonStore({
      url: modURL + "&op=getGstStore",
      fields: ["id", "hsnGst", "hsnId", "hsnCess"],
      totalProperty: "totalCount",
      root: "data",
      remoteFilter: true,
      listeners: {},
    });
    return gst_store;
  };
  var hsnStorefn = function () {
    var hsn_store = new Ext.data.JsonStore({
      url: modURL + "&op=gethsnStore",
      fields: ["hsn_id", "hsn_code", "gst_percent"],
      totalProperty: "totalCount",
      root: "data",
      remoteFilter: true,
      autoLoad: false,
      listeners: {},
    });
    return hsn_store;
  };
  var storeDataProductStore = function (uploadId,feedback) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=liststoreDataProducts",
        method: "post",
      }),
      fields: ["id", "brand", "name"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.fbiu_id = uploadId;
          this.baseParams.feedback = feedback;
        },load: function (a, b, options) {
          mappedLastParameters = options.params;
      }
      }
    });
    return _Store;
  };
  var storeDataGrid = function (uploadId,feedback) {
    var storeDataProduct_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "brand",
        },{
          type: "string",
          dataIndex: "name",
        },
      ],
    });
    storeDataProduct_filter.remote = true;
    storeDataProduct_filter.autoReload = true;
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      title: "Source Products",
      frame: false,
      border: false,
      loadMask: true,
      store: storeDataProductStore(uploadId,feedback),
      //iconCls: 'money',
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforstoreDataProducts",
      plugins: [storeDataProduct_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brands",
          dataIndex: "brand",
          sortable: true,
          tooltip: "Brands",
          hideable: false,          
        },
        {
          header: "SKU in  File",
          dataIndex: "name",
          sortable: true,
          tooltip: "SKU",
          hideable: false,
          width: 200,
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          width: 100, // Adjust width as needed
          items: [{
            icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tooltip: "Edit Source Data",
              handler: function (grid, rowIndex, colIndex, item, event) {
                var record = grid.getStore().getAt(rowIndex);
                grid.getSelectionModel().selectRow(rowIndex);
              var recordId = record.get("id");
              var recordBrand = record.get("brand");
              var recordProduct = record.get("name");
              Application.ProductBankUpload.editSourceData(recordId,recordBrand,recordProduct);
              }
          },
            {
              icon: IMAGE_BASE_PATH + "/default/icons/search.png",
              tooltip: "Check",
              handler: function (grid, rowIndex, colIndex, item, event) {
                Application.ProductBankUpload.Cache.fileSku = '';
                // Get the record for the clicked row
                var record = grid.getStore().getAt(rowIndex);
                grid.getSelectionModel().selectRow(rowIndex);
                // Handle the action (e.g., AJAX request)
                Ext.getCmp("gridpanelforGalleryMatchedProducts")
                  .getStore()
                  .load({
                    params: {
                      prdctId: record.get("id"),
                      brand: record.get("brand"),
                      uploadId: uploadId,
                    },callback: function () {
                      Application.ProductBankUpload.Cache.fileSku = record.get("name");
                    }
                  });
                  
              },
            },
          ],
        },
      ],
      bbar: [],
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionStreDataChanged
        },
      }),
      listeners: {
        afterrender: function (grid) {},
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _dispatchgridPanel;
  };
  var matchGalleryProducts = function (userId) {
    var matchGalleryProduct_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
      ],
    });
    matchGalleryProduct_filter.remote = true;
    matchGalleryProduct_filter.autoReload = true;
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: true,
      border: false,
      loadMask: true,
      store: galleryMatchedProductStore(userId),
      //iconCls: 'money',
      height: 500,
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforGalleryMatchedProducts",
      plugins: [matchGalleryProduct_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          dataIndex: "stit_SKU",
          sortable: true,
          tooltip: "SKU",
          hideable: false,
          width: 200,
        },
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {
          if (record.get("stit_StoreGroup") > 0) {
            return "finascop_indicateColLIGHTORANGE";
          } else {
            return "";
          }
        }
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      listeners: {
        afterrender: function () {},
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _dispatchgridPanel;
  };
  var galleryMatchedProductStore = function (userId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=getMatchedProducts",
        method: "post",
      }),
      fields: ["stit_ID", "stit_brand_name", "stit_SKU","stit_StoreGroup"],
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
  var mappedProductsGrid = function(uploadId, brandName){

    var mappedProduct_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
      ],
    });
    mappedProduct_filter.remote = true;
    mappedProduct_filter.autoReload = true;
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: true,
      border: false,
      loadMask: true,
      store: mappedProductStore(uploadId),
      height: 500,
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforMappedProducts",
      plugins: [mappedProduct_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          dataIndex: "stit_SKU",
          sortable: true,
          tooltip: "SKU",
          hideable: false,
          width: 200,
        },{
          header: "Brand",
          dataIndex: "stit_brand_name",
          sortable: true,
          tooltip: "Brand",
          hideable: false,
        },{
          header: "Category",
          dataIndex: "stit_category_name",
          sortable: true,
          tooltip: "Category",
          hideable: false,
        },{
          header: "Is Exported",
          dataIndex: "isExported",
          sortable: true,
          tooltip: "Is Exported",
          hideable: false,
        }
      ],
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      listeners: {
        afterrender: function () {},
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _dispatchgridPanel;
  };
  var mappedProductStore = function (uploadId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=getMappedProducts",
        method: "post",
      }),
      fields: ["id", "stit_brand_name", "stit_SKU","stit_brand_name","stit_category_name","stit_HSNCode","stit_HSN_code","stit_GST","isExported"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.uploadId = uploadId;
        },
        load: function (a, b, options) {
          mappedLastParameters = options.params;

      }
      },
    });
    return _Store;
  };
  var productSyncMainPanel = function (id) {
    var _fsuPanel = new Ext.Panel({
      frame: true,
      hideBorders: true,
      layout: "fit",
      border: false,
      title: "Sync Products",
      iconCls: "dispatch",
      id: id,
      items: [productSyncGrid()],
    });
    return _fsuPanel;
  };
  var syncProductStore = function () {
    var _productSyncStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=gettoSyncProducts",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "id",
          root: "data",
        },
        ["id", "stit_brand_name", "stit_SKU","stit_brand_name","stit_category_name","stit_HSNCode","stit_HSN_code","stit_GST","isExported"]
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
    return _productSyncStore;
  };
  var check_box = new Ext.grid.CheckboxSelectionModel({
    multiSelect: true,
    checkOnly: true,
    listeners: {},
  });
  var productSyncGrid = function(){
    var _syncGridStore = syncProductStore();
    var _syncFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "createdByName",
        },
        {
          type: "string",
          dataIndex: "typeName",
        },
        {
          type: "string",
          dataIndex: "brand",
        },{
          type: "string",
          dataIndex: "stit_category_name",
        },
      ],
    });
    _syncFilter.remote = true;
    _syncFilter.autoReload = true;
    var _syncGridPanel = new Ext.grid.GridPanel({
      frame: true,
      border: false,
      loadMask: true,
      store: _syncGridStore,
      id: "productSyncGrid",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _syncFilter],
      columns: [
        check_box,
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          dataIndex: "stit_SKU",
          sortable: true,
          tooltip: "SKU",
          hideable: false,
          width: 200,
        },{
          header: "Brand",
          dataIndex: "stit_brand_name",
          sortable: true,
          tooltip: "Brand",
          hideable: false,
        },{
          header: "Sub Category",
          dataIndex: "stit_category_name",
          sortable: true,
          tooltip: "Category",
          hideable: false,
        },{
          header: "HSN",
          dataIndex: "stit_HSN_code",
          sortable: true,
          tooltip: "HSN",
          hideable: false,
        }
      ],
      viewConfig: {
        forceFit: true,
      },tbar: [
        "->",
        {
          xtype: "button",
          text: "Export",
          handler: function () {
            var selectitem = Ext.getCmp("productSyncGrid")
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var productsarr = [];
            for (var i = 0; i < selectedcount; i++) {
              productsarr.push(selectitem[i].data.id);
            }
            if (selectedcount != 0) {
              Ext.Ajax.request({
                url: promodURL + "&op=bulkExportAndSyncProducts",
                method: "POST",
                waitMsg: "Processing",
                params: {
                  isBilkExport: 1,
                  itemarr: Ext.encode(productsarr),
                },
                failure: function (response, options) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", "tmp.msg");
                },
                success: function (response, options) {
                  var tmp = Ext.decode(response.responseText);
        
                  if (tmp.success === true) {
                    Ext.MessageBox.alert("Notification", tmp.msg);
                    Ext.getCmp("productSyncGrid").getStore().load();
                  } else {
                    Ext.MessageBox.alert("Error", tmp.msg);
                  }
                },
              })
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Please select a product and proceed.."
              );
            }
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _syncGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: check_box,
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        afterrender: function () {},
      },      
    });
    return _syncGridPanel;
  };
  var tomatchGalleryProducts = function (sourceId,brandName,prdctName) {
    var tomatchGalleryProduct_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
      ],
    });
    tomatchGalleryProduct_filter.remote = true;
    tomatchGalleryProduct_filter.autoReload = true;
    var _tomatchgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: true,
      border: false,
      loadMask: true,
      store: galleryToMatchProductStore(sourceId,brandName,prdctName),
      //iconCls: 'money',
      height: 500,
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforGalleryToProducts",
      plugins: [tomatchGalleryProduct_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU ",
          dataIndex: "stit_SKU",
          sortable: true,
          tooltip: "SKU",
          hideable: false,
          width: 200,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {},
      }),
      listeners: {
        afterrender: function () {},
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _tomatchgridPanel;
  };
  var galleryToMatchProductStore = function (sourceId,brandName,prdctName) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=getMatchedProducts",
        method: "post",
      }),
      fields: ["stit_ID", "stit_brand_name", "stit_SKU"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.sourceId = sourceId;
          this.baseParams.brand = brandName;
          this.baseParams.prdctName = prdctName;
        },
      },
    });
    return _Store;
  };
  return {
    Cache: {},
    initPrdctBankUploadList: function () {
      var _productBankUploadPanelId = "productBankUploadMainPanel";
      var __productBankUploadPanelIdPanel = Ext.getCmp(
        _productBankUploadPanelId
      );
      if (Ext.isEmpty(__productBankUploadPanelIdPanel)) {
        __productBankUploadPanelIdPanel = productBankUploadMainPanel(
          _productBankUploadPanelId
        );
        Application.UI.addTab(__productBankUploadPanelIdPanel);
        __productBankUploadPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__productBankUploadPanelIdPanel);
      }
    },
    viewUploadedProducts: function (fbiu_id, brName) {
      var _addnewItemsWindow = new Ext.Window({
        title: "Products of - " + brName,
        iconCls: "dispatch",
        layout: "fit",
        height: 500,
        width: winsize.width * 0.65,
        id: "fsibiudWindow",
        resizable: false,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [_viewUploadedProductsDetailsGrid(fbiu_id)],
      });
      _addnewItemsWindow.doLayout();
      _addnewItemsWindow.show();
      _addnewItemsWindow.center();
    },
    initPrdctThirdPartyList: function () {
      var _productThirdPartyPanelId = "productThirdPartyPanel";
      var __productThirdPartyPanelIdPanel = Ext.getCmp(
        _productThirdPartyPanelId
      );
      if (Ext.isEmpty(__productThirdPartyPanelIdPanel)) {
        __productThirdPartyPanelIdPanel = productThirdPartyMainPanel(
          _productThirdPartyPanelId
        );
        Application.UI.addTab(__productThirdPartyPanelIdPanel);
        __productThirdPartyPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(__productThirdPartyPanelIdPanel);
      }
    },
    viewUploadedTPProducts: function (fbiu_id, brName) {
      var _addnewItemsWindow = new Ext.Window({
        title: "Products of - " + brName,
        iconCls: "dispatch",
        layout: "fit",
        height: 500,
        width: winsize.width * 0.65,
        id: "fsibiudWindow",
        resizable: false,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [_viewUploadedTPProductsDetailsGrid(fbiu_id)],
        bbar: [
          "->",
          {
            xtype: "button",
            text: "Sync Images",
            icon: IMAGE_BASE_PATH + "/default/icons/upload.png",
            tabindex: 6,
            align: "right",
            id: "PrdctImagesSync",
            handler: function () {
              if (
                Ext.getCmp("uploadedTPProductDetailsgridPanelDetailsView")
                  .getStore()
                  .getCount() > 0
              ) {
                Application.ProductBankUpload.syncImages(fbiu_id, brName);
              } else {
                Ext.MessageBox.alert("Notification", "No products to sync");
              }
            },
          },
          {
            xtype: "button",
            text: "Sync Products",
            icon: IMAGE_BASE_PATH + "/default/icons/upload.png",
            tabindex: 6,
            align: "right",
            id: "PrdctExport",
            handler: function () {
              if (
                Ext.getCmp("uploadedTPProductDetailsgridPanelDetailsView")
                  .getStore()
                  .getCount() > 0
              ) {
                Application.ProductBankUpload.exportToParent(fbiu_id, brName);
              } else {
                Ext.MessageBox.alert("Notification", "No products to sync");
              }
            },
          },
          {
            text: "Close",
            tabIndex: 557,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            handler: function () {
              _addnewItemsWindow.close();
            },
          },
        ],
      });
      _addnewItemsWindow.doLayout();
      _addnewItemsWindow.show();
      _addnewItemsWindow.center();
    },
    confirmImportProducts: function (fbiu_id, brName, message) {
      var _confirmItemsWindow = new Ext.Window({
        title: "Products to confirm on - " + brName,
        iconCls: "dispatch",
        layout: "fit",
        height: 500,
        width: winsize.width * 0.75,
        id: "importConfirmWindow",
        resizable: false,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
        items: [_viewimportedProductsDetailsGrid(fbiu_id)],
        bbar: [
          "->",
          {
            xtype: "button",
            text: "Confirm",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
            tabindex: 6,
            align: "right",
            handler: function () {
              Ext.MessageBox.confirm(
                "Confirm",
                "Do you want to proceed with verified products?",
                function (btn, text) {
                  if (btn == "yes") {
                    myMask.show();
                    Ext.Ajax.request({
                      url: modURL + "&op=cofirmProductImport",
                      method: "POST",
                      waitMsg: "Processing",
                      params: {
                        id: fbiu_id,
                        sgName: brName,
                      },
                      failure: function (response, options) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        myMask.hide();
                        Ext.MessageBox.alert("Error", "tmp.msg");
                      },
                      success: function (response, options) {
                        var tmp = Ext.decode(response.responseText);

                        if (tmp.success === true) {
                          myMask.hide();
                          _confirmItemsWindow.close();
                          Application.ProductBankUpload.viewUploadedTPProducts(
                            fbiu_id,
                            brName
                          );
                          Ext.MessageBox.alert("Notification", tmp.msg);
                        } else {
                          myMask.hide();
                          Ext.MessageBox.alert("Error", tmp.msg);
                        }
                      },
                    });
                  }
                }
              );
            },
          },
          {
            text: "Discard",
            tabIndex: 557,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            handler: function () {
              Ext.MessageBox.confirm(
                "Confirm",
                "Discard current import",
                function (btn, text) {
                  if (btn == "yes") {
                    myMask.show();
                    Ext.Ajax.request({
                      url: modURL + "&op=discardProductImport",
                      method: "POST",
                      waitMsg: "Processing",
                      params: {
                        id: fbiu_id,
                        sgName: brName,
                      },
                      failure: function (response, options) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        myMask.hide();
                        Ext.MessageBox.alert("Error", "tmp.msg");
                      },
                      success: function (response, options) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success === true) {
                          myMask.hide();
                          Ext.MessageBox.alert("Notification", tmp.msg);
                          Ext.getCmp("productThirdParty").getStore().load();
                        } else {
                          myMask.hide();
                          Ext.MessageBox.alert("Error", tmp.msg);
                        }
                      },
                    });
                  }
                }
              );
            },
          },
          {
            text: "Close",
            tabIndex: 557,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            handler: function () {
              _confirmItemsWindow.close();
            },
          },
        ],
      });
      _confirmItemsWindow.doLayout();
      _confirmItemsWindow.show();
      _confirmItemsWindow.center();
    },
    exportToParent: function (id, sgName) {
      myMask.show();
      var selectitem = Ext.getCmp(
        "uploadedTPProductDetailsgridPanelDetailsView"
      ).getStore().data.items;
      var selectedcount = Ext.getCmp(
        "uploadedTPProductDetailsgridPanelDetailsView"
      )
        .getStore()
        .getCount();
      var productsarr = [];
      for (var i = 0; i < selectedcount; i++) {
        productsarr.push(selectitem[i].data.stit_ID);
      }
      Ext.Ajax.request({
        url: modURL + "&op=syncToParentDB",
        method: "POST",
        waitMsg: "Processing",
        params: {
          tpProduct: 1,
          isBilkExport: 1,
          id: id,
          sgName: sgName,
          itemarr: Ext.encode(productsarr),
        },
        failure: function (response, options) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          myMask.hide();
          Ext.MessageBox.alert("Error", "Syncing is on process please wait");
        },
        success: function (response, options) {
          var tmp = Ext.decode(response.responseText);

          if (tmp.success === true) {
            myMask.hide();
            Ext.MessageBox.alert("Notification", tmp.msg);
          } else {
            myMask.hide();
            Ext.MessageBox.alert("Error", tmp.msg);
          }
        },
      });
    },
    syncImages: function (id, sgName) {
      myMask.show();
      var selectitem = Ext.getCmp(
        "uploadedTPProductDetailsgridPanelDetailsView"
      ).getStore().data.items;
      var selectedcount = Ext.getCmp(
        "uploadedTPProductDetailsgridPanelDetailsView"
      )
        .getStore()
        .getCount();
      var productsarr = [];
      for (var i = 0; i < selectedcount; i++) {
        productsarr.push(selectitem[i].data.stit_ID);
      }
      Ext.Ajax.request({
        url: modURL + "&op=syncTpImages",
        method: "POST",
        waitMsg: "Processing",
        params: {
          tpProduct: 1,
          isBilkExport: 1,
          id: id,
          sgName: sgName,
          itemarr: Ext.encode(productsarr),
        },
        failure: function (response, options) {
          myMask.hide();
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", "Syncing is on process please wait");
        },
        success: function (response, options) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true) {
            myMask.hide();
            Ext.MessageBox.alert("Notification", tmp.msg);
          } else {
            myMask.hide();
            Ext.MessageBox.alert("Error", tmp.msg);
          }
        },
      });
    },
    verifyProducts: function (id, fbiu_id) {
      var hsnStore = hsnStorefn();
      var gstStore = gstStorefn();
      var mapNVerifyProductsWindow = new Ext.Window({
        id: "mapProductMaster",
        iconCls: "vender-items",
        shadow: false,
        width: winsize.width * 0.3,
        height: winsize.height * 0.4,
        title: "Map and Verify Products",
        layout: "fit",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [
          new Ext.FormPanel({
            layout: "column",
            id: "mapNVerifyProductsFormPanel",
            height: 150,
            frame: true,
            border: true,
            labelAlign: "top",
            items: [
              {
                columnWidth: 0.3,
                layout: "form",
                frame: false,
                border: false,
                items: [
                  mkCombo({
                    type: "mypha_productsubcategory",
                    value: "sub_category_id",
                    display: "sub_category",
                    name: "CategoryId",
                    fieldLabel: "Sub Category",
                    emptyText: "Select Product Category..",
                    id: "CategoryId",
                    tabIndex: 500,
                    anchor: "95%",
                    allowBlank: false,
                    cx: "S_1",
                    combo_listeners: {},
                  }),
                ],
              },
              {
                columnWidth: 0.3,
                layout: "form",
                frame: false,
                border: false,
                items: [
                  mkCombo({
                    type: "mypha_productbrands",
                    value: "brand_id",
                    display: "brand_name",
                    name: "BrandId",
                    fieldLabel: "Brand",
                    emptyText: "Select Brand..",
                    id: "BrandId",
                    listeners: false,
                    allowBlank: false,
                    tabIndex: 705,
                    anchor: "98%",
                    cx: "S_1",
                    combo_listeners: {
                      select: function (combo, record, index) {},
                    },
                  }),
                ],
              },
              {
                columnWidth: 0.3,
                layout: "form",
                frame: false,
                border: false,
                items: [
                  {
                    fieldLabel: "HSN",
                    xtype: "combo",
                    displayField: "hsn_code",
                    valueField: "hsn_id",
                    mode: "remote",
                    id: "hsnId",
                    name: "hsnId",
                    emptyText: "Select HSN",
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
                        var value = Ext.getCmp("hsnId").getValue();
                        gstStore.baseParams.hsnId = this.value;
                        gstStore.load();
                      },
                    },
                  },
                ],
              },
              {
                columnWidth: 0.25,
                layout: "form",
                frame: false,
                border: false,
                items: [
                  {
                    fieldLabel: "GST / VAT %",
                    xtype: "combo",
                    displayField: "hsnGst",
                    valueField: "id",
                    mode: "remote",
                    id: "gstId",
                    name: "gstId",
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
                        //Ext.getCmp("gstId").setValue(val.data.hsnGst);
                      },
                    },
                  },
                  {
                    xtype: "hidden",
                    fieldLabel: "GST / VAT %",
                    id: "GST",
                    name: "GST",
                    readOnly: true,
                    anchor: "95%",
                    tabIndex: 508,
                    allowBlank: false,
                  },
                ],
              },
              {
                layout: "form",
                columnWidth: 0.25,
                labelAlign: "top",
                labelWidth: 100,
                items: [
                  mkCombo({
                    type: "finascop_country",
                    value: "country_id",
                    display: "country_name",
                    name: "countryId",
                    fieldLabel: "Country of Orgin",
                    emptyText: "Select..",
                    id: "countryId",
                    listeners: false,
                    allowBlank: false,
                    tabIndex: 510,
                    anchor: "95%",
                    cx: "S_1",
                  }),
                ],
              },
              {
                layout: "form",
                columnWidth: 0.25,
                labelAlign: "top",
                items: [
                  {
                    xtype: "textfield",
                    fieldLabel: "Quantity",
                    id: "Quantity",
                    name: "Quantity",
                    allowBlank: false,
                    anchor: "95%",
                    tabIndex: 504,
                    listeners: {
                      change: function () {},
                    },
                  },
                ],
              },
              {
                layout: "form",
                columnWidth: 0.25,
                labelAlign: "top",
                items: [
                  mkCombo({
                    type: "mypha_unit",
                    value: "unit_id",
                    display: "unit_name",
                    name: "UnitId",
                    fieldLabel: "Unit",
                    emptyText: "Select Units..",
                    id: "UnitId",
                    allowBlank: false,
                    tabIndex: 505,
                    anchor: "95%",
                    cx: "S_1",
                    combo_listeners: {
                      select: function (combo, record, index) {
                        var value = record.data.unit_id;
                      },
                    },
                  }),
                ],
              },
            ],
          }),
        ],
        bbar: [
          "->",
          {
            text: "Update",
            tabIndex: 556,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
            handler: function () {
              if (
                Ext.getCmp("mapNVerifyProductsFormPanel").getForm().isValid()
              ) {
                var update_form = Ext.getCmp(
                  "mapNVerifyProductsFormPanel"
                ).getForm();
                update_form.submit({
                  url: modURL + "&op=verifyProduct",
                  waitTitle: "Please Wait..",
                  waitMsg: "Saving data...",
                  params: {
                    id: id,
                    Brand_Name: Ext.getCmp("BrandId").getRawValue(),
                    Category_Name: Ext.getCmp("CategoryId").getRawValue(),
                    CountryOfOrgin: Ext.getCmp("countryId").getRawValue(),
                    pdthsnId: Ext.getCmp("hsnId").getValue(),
                    HSN_code: Ext.getCmp("hsnId").getRawValue(),
                    pdtgstId: Ext.getCmp("gstId").getValue(),
                    GST: Ext.getCmp("gstId").getRawValue(),
                    Unit: Ext.getCmp("UnitId").getRawValue(),
                  },
                  success: function (update_form, action) {
                    var result = Ext.decode(action.response.responseText);
                    if (result.valid === true && result.success === true) {
                      mapNVerifyProductsWindow.close();
                      Application.example.msg(
                        "Notification",
                        "Details saved Successfully. "
                      );
                      Ext.getCmp("importedProductDetailsgridPanelDetailsView")
                        .getStore()
                        .load({
                          params: {
                            fbiu_id: fbiu_id,
                          },
                        });
                    }
                  },
                  failure: function () {
                    Ext.Msg.alert(
                      "Error",
                      "Supplied CSV File could not be validated. ",
                      function (btn) {
                        if (btn === "ok") {
                          mapNVerifyProductsWindow.close();
                        }
                      }
                    );
                  },
                });
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Please enter all required fields"
                );
              }
            },
          },
          {
            text: "Close",
            tabIndex: 557,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            handler: function () {
              mapNVerifyProductsWindow.close();
            },
          },
        ],
        listeners: {
          afterrender: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            winLoadMask = new Ext.LoadMask(
              Ext.getCmp("mapProductMaster").getEl()
            );
            winLoadMask.msg = "Please wait...";
            if (id > 0) {
              Ext.getCmp("mapNVerifyProductsFormPanel")
                .getForm()
                .load({
                  waitTitle: "Please Wait",
                  waitMsg: "Loading...",
                  url: modURL + "&op=getprdctDetails",
                  params: {
                    id: id,
                  },
                  success: function (form, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    Ext.getCmp("BrandId").getStore().load();
                    Ext.getCmp("BrandId").setValue(tmp.data.BrandId);
                    if (tmp.data.BrandId > 0)
                      Ext.getCmp("BrandId").setRawValue(tmp.data.Brand_Name);
                    Ext.getCmp("CategoryId").getStore().load();
                    Ext.getCmp("CategoryId").setValue(tmp.data.CategoryId);
                    if (tmp.data.CategoryId > 0)
                      Ext.getCmp("CategoryId").setRawValue(
                        tmp.data.Category_Name
                      );
                    Ext.getCmp("countryId").getStore().load();
                    Ext.getCmp("countryId").setValue(tmp.data.countryId);
                    if (tmp.data.countryId > 0)
                      Ext.getCmp("countryId").setRawValue(
                        tmp.data.CountryOfOrgin
                      );
                    Ext.getCmp("UnitId").getStore().load();
                    Ext.getCmp("UnitId").setValue(tmp.data.UnitId);
                    if (tmp.data.UnitId > 0)
                      Ext.getCmp("UnitId").setRawValue(tmp.data.Unit);
                    Ext.getCmp("hsnId").getStore().load();
                    Ext.getCmp("hsnId").setValue(tmp.data.hsnId);
                    if (tmp.data.hsnId > 0)
                      Ext.getCmp("hsnId").setRawValue(tmp.data.HSN_code);
                    Ext.getCmp("gstId")
                      .getStore()
                      .load({
                        params: {
                          hsnId: tmp.data.hsnId,
                        },
                      });
                    Ext.getCmp("gstId").setRawValue(tmp.data.gstId);
                    if (tmp.data.BrandId > 0)
                      Ext.getCmp("gstId").setRawValue(tmp.data.GST);
                  },
                  failure: function (form, action) {
                    Ext.Msg.alert("Error.", "This error");
                  },
                });
            }
          },
        },
      });

      mapNVerifyProductsWindow.doLayout();
      mapNVerifyProductsWindow.show();
      mapNVerifyProductsWindow.center();
    },
    viewImages: function (productId, uploadId) {
      var src =
        "?module=product_bank_upload&op=showImages&productId=" +
        productId +
        "&uploadId=" +
        uploadId;
      var _imageItemsWindow = new Ext.Window({
        title: "View Images",
        iconCls: "dispatch",
        layout: "fit",
        height: 500,
        width: winsize.width * 0.65,
        id: "imageViewWindow",
        resizable: false,
        draggable: true,
        closable: true,
        bodyStyle: { "background-color": "white" },
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
        bbar: [
          "->",
          {
            text: "Close",
            tabIndex: 557,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            handler: function () {
              _imageItemsWindow.close();
            },
          },
        ],
      });
      _imageItemsWindow.doLayout();
      _imageItemsWindow.show();
      _imageItemsWindow.center();
    },
    mapUploadedProducts: function (fbiu_id, brName,feedback) {
      if(feedback ==1){
        var visiblity = true;
        var narVisibility = false;
      }else{
        var visiblity = false;
        var narVisibility = true;
      }
      var _mapavailableItemsWindow = new Ext.Window({
        title: "Product Availability Check",
        iconCls: "dispatch",
        layout: "fit",
        height: 500,
        width: winsize.width * 0.8,
        id: "mapAvailableProductWindow",
        resizable: false,
        draggable: true,
        closable: true,
        layout: "border",
        bodyStyle: { "background-color": "white" },
        items: [
          storeDataGrid(fbiu_id,feedback),
          new Ext.Panel({
            title: "Gallery Products",
            frame: false,
            border: true,
            region: "east",
            width: winsize.width * 0.4,
            cls: "left_side_panel",
            height: 500,
            //autoScroll: true,
            items: [matchGalleryProducts(fbiu_id)],
            buttons: [{
              hidden:visiblity,
              text: "Feedback",
                tabIndex: 139,
                buttonAlign: "right",
                handler: function () {
                  var storePrdctId = Ext.getCmp("gridpanelforstoreDataProducts").getSelectionModel().getSelections()[0].data.id;
                  if(!Ext.isEmpty(storePrdctId) && storePrdctId > 0){
                    Ext.Ajax.request({
                      url: modURL + "&op=removeProduct",
                      method: "post",
                      waitMsg: "Processing",
                      params: {
                        uploadId : fbiu_id,
                        storePrdctId:storePrdctId,
                        feedback:1
                      },
                      success: function (resp) {
                        var res = Ext.decode(resp.responseText);
                        if (res.success === true) {
                          Ext.getCmp("gridpanelforstoreDataProducts").getStore().load();
                          Ext.getCmp('gridpanelforGalleryMatchedProducts').filters.clearFilters();
                          Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().removeAll(true);
                          Ext.getCmp('gridpanelforGalleryMatchedProducts').getView().refresh();
                          Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().loadData([]);
  
                        }
                      },
                    });
                  }else{
                    Ext.MessageBox.alert("Notification", "Choose products and proceed.");
                  }
                
              },
            },{
              text: "Remove",
              hidden:narVisibility,
                tabIndex: 139,
                buttonAlign: "right",
                handler: function () {
                  var storePrdctId = Ext.getCmp("gridpanelforstoreDataProducts").getSelectionModel().getSelections()[0].data.id;
                  if(!Ext.isEmpty(storePrdctId) && storePrdctId > 0){
                    Ext.Ajax.request({
                      url: modURL + "&op=removeProduct",
                      method: "post",
                      waitMsg: "Processing",
                      params: {
                        uploadId : fbiu_id,
                        storePrdctId:storePrdctId,
                        feedback:0
                      },
                      success: function (resp) {
                        var res = Ext.decode(resp.responseText);
                        if (res.success === true) {
                          Ext.getCmp("gridpanelforstoreDataProducts").getStore().load();
                          Ext.getCmp('gridpanelforGalleryMatchedProducts').filters.clearFilters();
                          Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().removeAll(true);
                          Ext.getCmp('gridpanelforGalleryMatchedProducts').getView().refresh();
                          Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().loadData([]);
  
                        }
                      },
                    });
                  }else{
                    Ext.MessageBox.alert("Notification", "Choose products and proceed.");
                  }
                
              },
            },{
              text: "Not Available",
                tabIndex: 139,
                hidden:narVisibility,
                buttonAlign: "right",
                handler: function () {
                  var storePrdctId = Ext.getCmp("gridpanelforstoreDataProducts").getSelectionModel().getSelections()[0].data.id;
                  if(!Ext.isEmpty(storePrdctId) && storePrdctId > 0){
                    Ext.Ajax.request({
                      url: modURL + "&op=markNotAvailable",
                      method: "post",
                      waitMsg: "Processing",
                      params: {
                        uploadId : fbiu_id,
                        storePrdctId:storePrdctId,
                      },
                      success: function (resp) {
                        var res = Ext.decode(resp.responseText);
                        if (res.success === true) {
                          Ext.getCmp("gridpanelforstoreDataProducts").getStore().load();
                          Ext.getCmp('gridpanelforGalleryMatchedProducts').filters.clearFilters();
                          Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().removeAll(true);
                          Ext.getCmp('gridpanelforGalleryMatchedProducts').getView().refresh();
                          Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().loadData([]);
  
                        }
                      },
                    });
                  }else{
                    Ext.MessageBox.alert("Notification", "Choose products and proceed.");
                  }
                
              },
            },{
                text: "Map SKU",
                tabIndex: 139,
                buttonAlign: "right",
                handler: function () {
                var storePrdctId = Ext.getCmp("gridpanelforstoreDataProducts").getSelectionModel().getSelections()[0].data.id;
                var mappedPrdctId = Ext.getCmp("gridpanelforGalleryMatchedProducts").getSelectionModel().getSelections()[0].data.stit_ID;
                var storGroupId = Ext.getCmp("gridpanelforGalleryMatchedProducts").getSelectionModel().getSelections()[0].data.stit_StoreGroup;
                if(!Ext.isEmpty(storePrdctId) && !Ext.isEmpty(mappedPrdctId) && storGroupId == 0){
                  Ext.Ajax.request({
                    url: modURL + "&op=markAvaialableStoreProducts",
                    method: "post",
                    waitMsg: "Processing",
                    params: {
                      uploadId : fbiu_id,
                      storePrdctId:storePrdctId,
                      mappedPrdctId:mappedPrdctId
                    },
                    success: function (resp) {
                      var res = Ext.decode(resp.responseText);
                      if (res.success === true) {
                        Ext.getCmp("gridpanelforstoreDataProducts").getStore().load();
                        Ext.getCmp('gridpanelforGalleryMatchedProducts').filters.clearFilters();
                        Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().removeAll(true);
                        Ext.getCmp('gridpanelforGalleryMatchedProducts').getView().refresh();
                        Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().loadData([]);
                        

                      }
                    },
                  });
                }else if(!Ext.isEmpty(storePrdctId) && !Ext.isEmpty(mappedPrdctId) && storGroupId > 0){
                  Ext.Ajax.request({
                      url: modURL + "&op=removeProduct",
                      method: "post",
                      waitMsg: "Processing",
                      params: {
                        uploadId : fbiu_id,
                        storePrdctId:storePrdctId,
                        feedback:0
                      },
                      success: function (resp) {
                        var res = Ext.decode(resp.responseText);
                        if (res.success === true) {
                          Ext.getCmp("gridpanelforstoreDataProducts").getStore().load();
                          Ext.getCmp('gridpanelforGalleryMatchedProducts').filters.clearFilters();
                          Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().removeAll(true);
                          Ext.getCmp('gridpanelforGalleryMatchedProducts').getView().refresh();
                          Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().loadData([]);
  
                        }
                      },
                    });
                }else{
                  Ext.MessageBox.alert("Notification", "Choose valid products and proceed.");
                }
                  
                },
              },
              {
                text: "Export Feedbacked",
                tabIndex: 139,
                hidden:narVisibility,
                buttonAlign: "right",
                handler: function () {            
                  
                  if (Ext.getCmp('gridpanelforstoreDataProducts').getStore().getTotalCount() <= 0) {
                    Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                        if (btn == 'no') {
                            return;
                        }
                    });
                }

                var indexes = [];
                var heads = [];

                var filterData = Ext.encode(mappedLastParameters);
                for (var i = 0; i < Ext.getCmp('gridpanelforstoreDataProducts').getColumnModel().getColumnCount(); i++) {
                    if (Ext.getCmp('gridpanelforstoreDataProducts').getColumnModel().isHidden(i) !== true) {

                        indexes[indexes.length] = Ext.getCmp('gridpanelforstoreDataProducts').getColumnModel().config[i].dataIndex;
                        heads[heads.length] = Ext.getCmp('gridpanelforstoreDataProducts').getColumnModel().config[i].header;
                    }
                    var dataindexes = Ext.encode(indexes);
                    var headers = Ext.encode(heads);
                }

                postToUrl(modURL + '&op=feedbackedProductsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                  
                },
        }
            ],
            fbar: [],
          }),
        ],
      });
      _mapavailableItemsWindow.doLayout();
      _mapavailableItemsWindow.show();
      _mapavailableItemsWindow.center();
    },downloadMappedProducts :function (uploadId, brName,prdctName) {
      var _mappedItemsWindow = new Ext.Window({
        title: "Mapped Products",
        iconCls: "dispatch",
        layout: "fit",
        height: 500,
        width: winsize.width * 0.8,
        id: "viewMappedProductWindow",
        resizable: false,
        draggable: true,
        closable: true,
        layout: "fit",
        bodyStyle: { "background-color": "white" },
        items: [mappedProductsGrid(uploadId, brName)],
        buttons:[{
          hidden:true,
          text: "Sync",
          tabIndex: 139,
          buttonAlign: "right",
          handler: function () {
            if (Ext.getCmp('gridpanelforMappedProducts').getStore().getTotalCount() <= 0) {
              Ext.Msg.confirm('Notification', 'Grid does not contain any data to sync!<br> Do you still wants to sync?', function (btn) {
                  if (btn == 'no') {
                      return;
                  }
              });
          }
          Ext.Ajax.request({
            waitMsg: "Please wait...",
            url: promodURL + "&op=exportAndSyncProducts",
            method: "POST",
            waitMsg: "Processing",
            params: {
              uploadId: uploadId
            },
            success: function (response) {
              var tmp = Ext.decode(response.responseText);
              console.log(tmp);
              if (tmp.success === true) {
                Ext.Msg.alert("Notification.", tmp.message);
              } else {
                Ext.Msg.alert("Notification.", tmp.message);
              }
            },
            failure: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              Ext.Msg.alert(
                "Notification",
                "Product syncing is going on, please check after some time."
              );
            },
          });
          },
        },{
          text: "Export to Excel",
          tabIndex: 139,
          buttonAlign: "right",
          handler: function () {            
            
            if (Ext.getCmp('gridpanelforMappedProducts').getStore().getTotalCount() <= 0) {
              Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                  if (btn == 'no') {
                      return;
                  }
              });
          }

          var indexes = [];
          var heads = [];

          var filterData = Ext.encode(mappedLastParameters);
          for (var i = 0; i < Ext.getCmp('gridpanelforMappedProducts').getColumnModel().getColumnCount(); i++) {
              if (Ext.getCmp('gridpanelforMappedProducts').getColumnModel().isHidden(i) !== true) {

                  indexes[indexes.length] = Ext.getCmp('gridpanelforMappedProducts').getColumnModel().config[i].dataIndex;
                  heads[heads.length] = Ext.getCmp('gridpanelforMappedProducts').getColumnModel().config[i].header;
              }
              var dataindexes = Ext.encode(indexes);
              var headers = Ext.encode(heads);
          }

          postToUrl(modURL + '&op=mappedProductsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
            
          },
        }]
      });
      _mappedItemsWindow.doLayout();
      _mappedItemsWindow.show();
      _mappedItemsWindow.center();
    },initSyncPrdcts: function(){
      var _productSyncPanelId = "syncMatchedProduct";
      var ___productSyncPanelIdPanel = Ext.getCmp(
        _productSyncPanelId
      );
      if (Ext.isEmpty(___productSyncPanelIdPanel)) {
        ___productSyncPanelIdPanel = productSyncMainPanel(
          _productSyncPanelId
        );
        Application.UI.addTab(___productSyncPanelIdPanel);
        ___productSyncPanelIdPanel.doLayout();
      } else {
        Application.UI.addTab(___productSyncPanelIdPanel);
      }
    },mapUnAvailProducts: function(prdid, brName,prdctName){
      var _tomapItemsWindow = new Ext.Window({
        title: "Gallery Products - "+prdctName,
        iconCls: "dispatch",
        layout: "fit",
        height: 500,
        width: winsize.width * 0.8,
        id: "toMapProductWindow",
        resizable: false,
        draggable: true,
        closable: true,
        layout: "fit",
        bodyStyle: { "background-color": "white" },
        items: [tomatchGalleryProducts(prdid, brName,prdctName)],
        buttons:[{
          text: "Map SKU",
          tabIndex: 139,
          buttonAlign: "right",
          handler: function () {
          var storePrdctId = prdid;
          var mappedPrdctId = Ext.getCmp("gridpanelforGalleryToProducts").getSelectionModel().getSelections()[0].data.stit_ID;
          if(!Ext.isEmpty(storePrdctId) && !Ext.isEmpty(mappedPrdctId)){
            Ext.Ajax.request({
              url: modURL + "&op=markAvaialableStoreProducts",
              method: "post",
              waitMsg: "Processing",
              params: {
                uploadId : 0,
                storePrdctId:storePrdctId,
                mappedPrdctId:mappedPrdctId
              },
              success: function (resp) {
                var res = Ext.decode(resp.responseText);
                if (res.success === true) {
                  //Ext.getCmp("gridpanelforstoreDataProducts").getStore().load();
                  Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().removeAll(true);
                  Ext.getCmp('gridpanelforGalleryMatchedProducts').getView().refresh();
                  Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().loadData([]);

                }
              },
            });
          }else{
            Ext.MessageBox.alert("Notification", "Choose products and proceed.");
          }
            
          },
        }]
      });
      _tomapItemsWindow.doLayout();
      _tomapItemsWindow.show();
      _tomapItemsWindow.center();
    },editSourceData : function(prdid, brName,prdctName){      
      var _editSourceDataWindow = new Ext.Window({
        title: "Edit details of - "+prdctName,
        iconCls: "dispatch",
        layout: "fit",
        height: 200,
        width: winsize.width * 0.3,
        id: "editSourceDataWindow",
        resizable: false,
        draggable: true,
        closable: true,
        layout: "fit",
        bodyStyle: { "background-color": "white" },
        items: [new Ext.form.FormPanel({
          labelAlign: "top",
          labelSeparator: "",
          bodyStyle: {
            "background-color": "white",
            padding: "5px 5px 5px 10px",
          },
          autoHeight: true,
          id: "sourceData_form",
          frame: true,
          border: false,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Brand",
              id: "edit_brand",
              name: "edit_brand",
              allowBlank: false,
              value:brName,
              labelAlign: "top",
              anchor: "95%",
            },
            {
              xtype: "textfield",
              fieldLabel: "SKU in file",
              id: "edit_sku",
              name: "edit_sku",
              allowBlank: false,
              value:prdctName,
              labelAlign: "top",
              anchor: "95%",
            },            
          ]
        })],
        buttons:[{
              text: "Save",
              iconCls: "my-icon1",
              buttonAlign: "left",
              tabIndex: 139,
              handler: function () {
                Ext.Ajax.request({
                      waitMsg: "Please wait...",
                      url: modURL + "&op=updateSourceData",
                      params: {
                        prdid:prdid,
                        edit_sku:Ext.getCmp('edit_sku').getValue(),
                        edit_brand:Ext.getCmp('edit_brand').getValue()
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
                            _editSourceDataWindow.close();
                            Ext.getCmp("gridpanelforstoreDataProducts").getStore().load();
                            Ext.getCmp('gridpanelforGalleryMatchedProducts').filters.clearFilters();
                            Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().removeAll(true);
                            Ext.getCmp('gridpanelforGalleryMatchedProducts').getView().refresh();
                            Ext.getCmp("gridpanelforGalleryMatchedProducts").getStore().loadData([]);
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
          },
          {
              text: "Cancel",
              buttonAlign: "left",
              iconCls: "my-icon61",
              handler: function () {
                _editSourceDataWindow.close();
              }
            }]
      });
      _editSourceDataWindow.doLayout();
      _editSourceDataWindow.show();
      _editSourceDataWindow.center();

    },downloadUrl: function(){
      var url = '/resources/file/ProductImportFormat.xlsx';
      window.open(url, '_blank');
    }
  };
})();
