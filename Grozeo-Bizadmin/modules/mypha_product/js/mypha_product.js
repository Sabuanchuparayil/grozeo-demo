Application.MyphaProduct = (function () {
  var winsize = Ext.getBody().getViewSize();
  var modURL = "?module=mypha_product";
  var recs_per_page = 12;
  function updatePagination(cmp) {
    recs_per_page = finascop_update_recs_per_page(cmp);
  }
  var itemmasterStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listItemMasterData",
        method: "post",
      }),
      fields: [
        "product_category",
        "ItemId",
        "stit_itemName",
        "hsn_code",
        "product_is_home",
        "stit_category_name",
        "stit_brand_name",
        "imgCount",
        "stit_product_variant",
        "stit_quantity",
        "least_package_type_name",
        { name: "tax", type: "number" },
        { name: "mrp", type: "float" },
        "groups",
        "description",
        "stit_itemERPId",
        "stit_HSN_code",
        { name: "convertable_on", type: "number" },
        { name: "convertable_off", type: "number" },
        { name: "list_in_sales_on", type: "number" },
        { name: "list_in_sales_off", type: "number" },
        { name: "stock_enabled", type: "number" },
        { name: "stock_disabled", type: "number" },
        { name: "list_in_purchase_on", type: "number" },
        { name: "list_in_purchase_off", type: "number" },
        { name: "tangible_on", type: "number" },
        { name: "tangible_off", type: "number" },
        { name: "total_mrp", type: "float" },
        { name: "tax_total", type: "float" },
        "stit_status",
        "statusName",
        "stit_SKU",
        "isVerified",
        "isFeatured",
        "isPopular",
        "stit_displaylabel",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        load: function (store, record, options) {
          //                    Ext.getCmp('itemmaster_grid').getSelectionModel().selectRow(0);
          //                    if (record.length > 0) {
          //                        var record = Ext.getCmp('itemmaster_grid').getSelectionModel().getSelected();
          //
          //                        total_mrp = record.get('total_mrp');
          //                        var tax_total = record.get('tax_total');
          //                        var number_of_col = Ext.getCmp('itemmaster_grid').getColumnModel().config.length;
          //                        var tooltipmaker = Ext.getCmp('itemmaster_grid').getColumnModel().config;
          //
          //                        for (var i = 1; i < number_of_col; i++) {
          //                            var column_header = tooltipmaker[i].header;
          //                            if (column_header == 'MRP')
          //                            {
          //                                tooltipmaker[i].tooltip = 'Total MRP : ' + total_mrp;
          //                            }
          //                            if (column_header == 'Tax')
          //                            {
          //                                tooltipmaker[i].tooltip = 'Total Tax : ' + tax_total;
          //                            }
          //
          //                        }
          //                    }
        },
      },
    });
    store.setDefaultSort("stit_itemName", "ASC");
    return store;
  };
  var itemmasterData = function () {
    Ext.getCmp("itemmaster_grid").getStore().setDefaultSort("ItemName", "asc");
    Ext.getCmp("itemmaster_grid").getStore().removeAll();
    Ext.getCmp("itemmaster_grid")
      .getStore()
      .load({
        params: {
          start: 0,
          limit: recs_per_page,
        },
      });
  };
  var updateSalesPackageTtpeStore = function (field, combofield) {
    var comboStore = Ext.getCmp(combofield).getStore();
    var newValue = Ext.getCmp(field).getValue();
    var exist = comboStore.find("id", newValue);
    var newRawValue = Ext.getCmp(field).getRawValue();
    Ext.getCmp("ccsb_package_type_id")
      .getStore()
      .load({
        params: {
          stdpckl11: Ext.getCmp("least_package_type_id").getValue(),
          stdpckl12: Ext.getCmp("stit_package_type_id").getValue(),
          stdpckl21: Ext.getCmp("stdpckl21_package_type_id").getValue(),
          stdpckl31: Ext.getCmp("stdpckl31_package_type_id").getValue(),
          stdpckl41: Ext.getCmp("stdpckl41_package_type_id").getValue(),
        },
      });
    //        if (exist == -1) {
    //            var row = new Ext.data.Record.create({
    //                name: 'id',
    //                name: 'name'
    //            });
    //            var r = new row({
    //                'id': newValue,
    //                'name': newRawValue
    //            });
    //            console.log('r', r);
    //            Ext.getCmp('ccsb_package_type_id').getStore().add([r]);
    //        }
    //comboStore.load();
  };
  updateProductSku = function () {
    var brandName = Ext.getCmp("pdt_brand").getRawValue();
    var itemName = Ext.getCmp("item").getRawValue();
    var variant = Ext.getCmp("stit_product_variant").getValue();
    var qty = Ext.getCmp("stit_quantity").getValue();
    var lptName = Ext.getCmp("least_package_type_id").getRawValue();
    var productSKU =
      brandName + " " + itemName + " " + variant + " " + qty + " " + lptName;

    //var mesku = "remove Generic and Private from the my sku";
    var remo = ["Generic", "Private Product"];
    var regex = new RegExp(remo.join("|"), "gi");
    var productSKU = productSKU.replace(regex, "");
    var finalSKu = productSKU.trim();
    Ext.getCmp("stit_SKU").setValue(finalSKu);
  };
  var attributeItemPanel = function (
    ItemId,
    nameArray,
    count,
    attributeIdArray,
    valueModeArray
  ) {
    var formConfig = {
      //defaults: {
      anchor: "98%",
      labelAlign: "top",
      id: "attributePrdctFormDetails",
      height: winsize.height * 0.55,
      frame: true,
      labelWidth: 100,
      //autoScroll: true,
      layout: "column",
      items: [
        setFormFields(
          ItemId,
          nameArray,
          count,
          attributeIdArray,
          valueModeArray
        ),
      ],
    };

    return new Ext.form.FormPanel(formConfig);
  };
  var attributeItemPanelForm = function () {
    var formConfig = {
      //defaults: {
      anchor: "98%",
      labelAlign: "top",
      id: "attributePrdctFormDetails",
      frame: true,
      labelWidth: 100,
      autoScroll: true,
      layout: "column",
      items: [setFormFields([], 0, [])],
    };

    return new Ext.form.FormPanel(formConfig);
  };
  var attributeValueComboStore = function (attributeId) {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getAttributeValues",
      method: "post",
      fields: ["id", "valueName", "attributeId"],
      //totalProperty: 'totalCount',
      root: "data",
      listeners: {
        beforeload: function () {
          this.baseParams.attributeId = attributeId;
        },
      },
    });
    return store;
  };
  var setFormFields = function (
    ItemId,
    attributesArr,
    count,
    attributesIdArr,
    valueModeArray
  ) {
    try {
      var fields = [],
        data = [];
      for (var k = 0; k < attributesArr.length; k++) {
        var p = k + 1;

        switch (valueModeArray[k]) {
          case "1":
            fields.push({
              layout: "form",
              labelAlign: "top",
              border: true,
              style: "margin-bottom:3px;margin-left:3px;",
              columnWidth: 1,
              items: [
                {
                  xtype: "hidden",
                  iid: "attId_" + attributesIdArr[k],
                  name: "attId_" + attributesIdArr[k],
                  value: attributesIdArr[k],
                },
                {
                  xtype: "hidden",
                  iid: "valueMode_" + attributesIdArr[k],
                  name: "valueMode_" + attributesIdArr[k],
                  value: 1,
                },
                {
                  fieldLabel: attributesArr[k],
                  xtype: "lovcombo",
                  store: attributeValueComboStore(attributesIdArr[k]),
                  mode: "local",
                  id: "attValues_" + attributesIdArr[k],
                  name: "attValues_" + attributesIdArr[k],
                  hiddenName: "attValues_" + attributesIdArr[k],
                  allowBlank: false,
                  displayField: "valueName",
                  valueField: "id",
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
            });
            break;
          case "2":
            fields.push({
              layout: "form",
              labelAlign: "top",
              border: true,
              style: "margin-bottom:3px;margin-left:3px;",
              columnWidth: 1,
              items: [
                {
                  xtype: "hidden",
                  iid: "attId_" + attributesIdArr[k],
                  name: "attId_" + attributesIdArr[k],
                  value: attributesIdArr[k],
                },
                {
                  xtype: "hidden",
                  iid: "valueMode_" + attributesIdArr[k],
                  name: "valueMode_" + attributesIdArr[k],
                  value: 2,
                },
                {
                  fieldLabel: attributesArr[k],
                  xtype: "textfield",
                  id: "attValues_" + attributesIdArr[k],
                  name: "attValues_" + attributesIdArr[k],
                  tabIndex: 193,
                  allowBlank: false,
                  anchor: "95%",
                },
              ],
            });
            break;
          case "3":
            fields.push({
              layout: "form",
              labelAlign: "top",
              border: true,
              style: "margin-bottom:3px;margin-left:3px;",
              columnWidth: 1,
              items: [
                {
                  xtype: "hidden",
                  iid: "attId_" + attributesIdArr[k],
                  name: "attId_" + attributesIdArr[k],
                  value: attributesIdArr[k],
                },
                {
                  xtype: "hidden",
                  iid: "valueMode_" + attributesIdArr[k],
                  name: "valueMode_" + attributesIdArr[k],
                  value: 3,
                },
                {
                  fieldLabel: attributesArr[k],
                  xtype: "textarea",
                  id: "attValues_" + attributesIdArr[k],
                  name: "attValues_" + attributesIdArr[k],
                  tabIndex: 193,
                  height: 180,
                  allowBlank: false,
                  anchor: "95%",
                },
              ],
            });
            break;
        }
      }

      return fields;
    } catch (ex) {
      Ext.Msg.alert("Field Error", "Exceptions : " + ex.message);
    }
  };
  var getSubCategoryAttributes = function () {
    Ext.Ajax.request({
      url: modURL + "&op=getSubCategoryAttributes",
      method: "POST",
      params: {
        prdctCategory: Ext.getCmp("product_category").getValue(),
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        count = tmp.totalCount;
        var attributeCombination = tmp.data;
        var attributeIdArray = attributeCombination.map(
          (item) => item.attributeId
        );
        var nameArray = attributeCombination.map((item) => item.name);
        var valueModeArray = attributeCombination.map((item) => item.valueMode);

        setFormFields(nameArray, count, attributeIdArray, valueModeArray);
      },
      failure: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        Ext.MessageBox.alert("Error", "Issue in saving");
      },
    });
  };
  var viewItemPanel = function () {
    var CntryOrginStore = function () {
      var CntryOrgStore = new Ext.data.JsonStore({
        url: modURL + "&op=getOrginCountry",
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
    url: modURL + "&op=getGstStore",
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
        url: modURL + "&op=gethsnStore",
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
        url: modURL + "&op=getPackMastrStore",
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
        url: modURL + "&op=getPTStore",
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
        url: modURL + "&op=getItemMasterStockGroups",
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

                        Ext.getCmp("iteParentCategory").show();
                        Ext.getCmp('itemProcessingTime').setValue(tmp.processingTime);
                        Ext.getCmp("iteParentCategory").setValue(
                          tmp.categoryCombination
                        );
                        if(tmp.isPerishable == 1){
                          Ext.getCmp('courierDelivery').disable();
                          Ext.getCmp('courierDelivery').setValue(0);
                        }else{
                          Ext.getCmp('courierDelivery').enable();
                        }
                        if(tmp.hasRestaurantService == 1){
                          hsnStore.baseParams.query = '996331';
                          hsnStore.load();                          
                        }
                        //getSubCategoryAttributes();
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
              fieldLabel: "Food Type",
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
                  { id: "4", name: "Edible" },
                  { id: "1", name: "Edible - Vegetarian" },
                  { id: "2", name: "Edible - Non Vegetarian" },
                  { id: "3", name: "Edible - Vegan" },
                ],
              }),
              triggerAction: "all",
              minChars: 2,
              displayField: "name",
              valueField: "id",
              hiddenName: "stit_foodtype",
              tabIndex: 513,
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
              tabIndex: 514,
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
          columnWidth: 0.2,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              width: 100,
              id: "stit_itemReturnTime",
              name: "stit_itemReturnTime",
              tabIndex: 510,
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
          labelWidth: 100,
          items: [
            {
              xtype: "checkbox",
              checked: false,
              id: "stit_custInitiate",
              name: "stit_custInitiate",
              inputValue: 1,
              tabIndex: 523,
              fieldLabel: "Spot return",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.10,
          labelAlign: "top",
          items: [
            {
              xtype: "numberfield",
              id: "item_length",
              anchor: "99%",
              emptyText: "Length",
              name: "item_length",
              fieldLabel: "Length",
              tabIndex: 550,
              maxLength: 10,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.10,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              id: "item_breadth",
              emptyText: "Breadth",
              name: "item_breadth",
              fieldLabel: "Breadth",
              anchor: "99%",
              tabIndex: 551,
              maxLength: 10,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.10,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              id: "item_height",
              emptyText: "Height",
              name: "item_height",
              fieldLabel: "Height",
              anchor: "99%",
              tabIndex: 552,
              maxLength: 10,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.10,
          labelAlign: "top",
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Image Name",
              emptyText: "Image Name",
              id: "stit_displaylabel",
              name: "stit_displaylabel",
              anchor: "80%",
              width: 100,
              maxLength: 250,
              tabIndex: 509,
            },
          ],
        },{
          layout: "form",
          columnWidth: 0.20,
          labelAlign: "top",
          items: [
            {
              xtype: "numberfield",
              fieldLabel: "Processing Time(mts)",
              emptyText: "Processing Time in Minutes",
              id: "itemProcessingTime",
              name: "itemProcessingTime",
              anchor: "99%",
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
              tabIndex: 515,
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
              tabIndex: 516,
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
              tabIndex: 517,
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
              tabIndex: 518,
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
              tabIndex: 519,
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
              tabIndex: 520,
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
              tabIndex: 521,
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
              tabIndex: 522,
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
              tabIndex: 525,
              msgTarget: "qtip",
              hidden: true,
            },
            {
              xtype: "combo",
              fieldLabel: "Group",
              id: "itemgroup",
              hiddenName: "itemgroup",
              anchor: "95%",
              tabIndex: 526,
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
              xtype: "textarea",
              fieldLabel: "Short Description",
              id: "description",
              name: "description",
              anchor: "95%",
              maxLength: 1000,
              tabIndex: 527,
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
          items: [
            {
              xtype: "templateeditormce",
              fieldLabel: "Long Description",
              anchor: "95%",
              id: "stit_long_description",
              name: "stit_long_description",
              maxLength: 7000,
              height: 250,
              tabIndex: 528,
              listeners: {},
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
              xtype: "textarea",
              fieldLabel: "Ingridients",
              anchor: "95%",
              id: "stit_ingredients",
              name: "stit_ingredients",
              maxLength: 7000,
              height: 220,
              tabIndex: 529,
              listeners: {},
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
              xtype: "textarea",
              fieldLabel: "Preperation & Use",
              anchor: "95%",
              id: "stit_preparation_use",
              name: "stit_preparation_use",
              maxLength: 7000,
              height: 220,
              tabIndex: 517,
              listeners: {},
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
              xtype: "textarea",
              fieldLabel: "Allergens",
              anchor: "95%",
              id: "stit_allergens",
              name: "stit_allergens",
              maxLength: 7000,
              height: 220,
              tabIndex: 530,
              listeners: {},
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
              xtype: "textarea",
              fieldLabel: "Nutrtion Label",
              anchor: "95%",
              id: "stit_nutritionlabel",
              name: "stit_nutritionlabel",
              maxLength: 7000,
              height: 220,
              tabIndex: 531,
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
          hidden:true,
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
                  allowBlank: false,
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
                  width: 65,
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
                  tabIndex: 525,
                  maxLength: 10,
                  allowBlank: true,
                },
                {
                  xtype: "numberfield",
                  id: "stit11_minimumqty",
                  emptyText: "Min Qty",
                  name: "stit11_minimumqty",
                  fieldLabel: "Minimum Qty",
                  width: 80,
                  tabIndex: 526,
                  maxLength: 10,
                  allowBlank: true,
                },
                {
                  xtype: "numberfield",
                  id: "stit11_maximumqty",
                  emptyText: "Max Qty",
                  name: "stit11_maximumqty",
                  fieldLabel: "Maximium Qty",
                  width: 80,
                  tabIndex: 527,
                  maxLength: 10,
                  allowBlank: true,
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
                  tabIndex: 528,
                  maxLength: 10,
                  allowBlank: true,
                },
                {
                  xtype: "numberfield",
                  id: "stit12_minimumqty",
                  emptyText: "Min Qty",
                  name: "stit12_minimumqty",
                  fieldLabel: "Mininimum Qty",
                  width: 80,
                  tabIndex: 529,
                  maxLength: 10,
                  allowBlank: true,
                },
                {
                  xtype: "numberfield",
                  id: "stit12_maximumqty",
                  emptyText: "Max Qty",
                  name: "stit12_maximumqty",
                  fieldLabel: "Maximium Qty",
                  width: 80,
                  tabIndex: 530,
                  maxLength: 10,
                  allowBlank: true,
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
                  tabIndex: 531,
                  maxLength: 10,
                  allowBlank: true,
                },
                {
                  xtype: "numberfield",
                  id: "stit13_minimumqty",
                  emptyText: "Min Qty",
                  name: "stit13_minimumqty",
                  fieldLabel: "Minimum Qty",
                  width: 80,
                  tabIndex: 532,
                  maxLength: 10,
                  allowBlank: true,
                },
                {
                  xtype: "numberfield",
                  id: "stit13_maximumqty",
                  emptyText: "Max Qty",
                  name: "stit13_maximumqty",
                  fieldLabel: "Maximium Qty",
                  width: 80,
                  tabIndex: 533,
                  maxLength: 10,
                  allowBlank: true,
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
                  tabIndex: 534,
                  maxValue: 100,
                  allowBlank: true,
                },
                {
                  xtype: "numberfield",
                  emptyText: "Retailer Buffer %",
                  id: "stii_csbretail",
                  name: "stii_csbretail",
                  anchor: "75%",
                  width: 120,
                  tabIndex: 535,
                  maxValue: 100,
                  allowBlank: true,
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
              tabIndex: 536,
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
                  allowBlank: true,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "Purchasing unit",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 537,
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
                  allowBlank: false,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "Package Type",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 538, //reconciliation_template_store
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
                  tabIndex: 561,
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
                  allowBlank: false,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "From CS as",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 539,
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
              fieldLabel: "Stokist Sale",
              combineErrors: false,
              items: [
                {
                  xtype: "combo",
                  displayField: "package_type_name",
                  valueField: "package_type_id",
                  mode: "local",
                  id: "csb_package_type_id",
                  name: "csb_package_type_id",
                  allowBlank: false,
                  forceSelection: true,
                  fieldLabel: "Package Type",
                  emptyText: "Central Store Package",
                  anchor: "98%",
                  typeAhead: true,
                  triggerAction: "all",
                  lazyRender: true,
                  editable: true,
                  minChars: 2,
                  tabIndex: 540,
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
  var saveItemMaster = function (ItemId, dup) {
    WinMask = new Ext.LoadMask(Ext.getCmp("itemPanel").getEl());
    WinMask.show();
    var is_featured = Ext.getCmp("featured").getValue();
    var is_popular = Ext.getCmp("popular").getValue();

    var is_courierDelivery = Ext.getCmp("courierDelivery").getValue();
    var is_directDelivery = Ext.getCmp("directDelivery").getValue();
    var isRRPApplicable = Ext.getCmp("isRRPApplicable").getValue();
    var isdirectPurchase = Ext.getCmp("directPurchase").getValue();

    var is_stdPacking = Ext.getCmp("stit_stdPacking").getValue();
    var is_salesUnit = Ext.getCmp("stit_salesUnit").getValue();
    var stit_stdPacking = is_stdPacking === true ? "1" : "0";
    var stit_salesUnit = is_salesUnit === true ? "1" : "0";

    var isstit_custInitiate = Ext.getCmp("stit_custInitiate").getValue();
    var featured = is_featured === true ? "1" : "0";
    var popular = is_popular === true ? "1" : "0";
    var courierDelivery = is_courierDelivery === true ? "1" : "0";
    var directDelivery = is_directDelivery === true ? "1" : "0";
    var isRRPApplicable = isRRPApplicable === true ? "1" : "0";
    var directPurchase = isdirectPurchase === true ? "1" : "0";
    var stit_custInitiate = isstit_custInitiate === true ? "1" : "0";
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    if (Ext.getCmp("stit_long_description").getValue().length <= 7000) {
      if (Ext.getCmp("itemPanel").getForm().isValid()) {
        var form_data = {
          stit_stdPacking: stit_stdPacking,
          stit_salesUnit: stit_salesUnit,
          stit_package_type_id: Ext.getCmp("stit_package_type_id").getValue(),
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
          dupitem: dup,
          stit_SKU: Ext.getCmp("stit_SKU").getValue(),
          item: Ext.getCmp("item").getValue(),
          item_name: Ext.getCmp("item").getRawValue(),
          id: ItemId,
          HSN: Ext.getCmp("HSN").getValue(),
          HSN_code: Ext.getCmp("HSN").getRawValue(),
          stit_itemERPId: Ext.getCmp("stit_itemERPId").getValue(),
          stit_itemBarcode: Ext.getCmp("stit_itemBarcode").getValue(),
          stit_itemReturnTime: Ext.getCmp("stit_itemReturnTime").getValue(),
          stit_package_master: Ext.getCmp("stit_package_master").getValue(),
          stit_courierWt: Ext.getCmp("stit_courierWt").getValue(),
          GST: Ext.getCmp("GST").getValue(),
          taxValueId:Ext.getCmp("taxValueId").getValue(),
          display_label: Ext.getCmp("stit_displaylabel").getValue(),
          MRP: Ext.getCmp("MRP").getValue(),
          itemgroup: Ext.getCmp("itemgroup").getValue(),
          description: Ext.getCmp("description").getValue(),
          stit_product_variant: Ext.getCmp("stit_product_variant").getValue(),
          product_category: Ext.getCmp("product_category").getValue(),
          stit_category_name: Ext.getCmp("product_category").getRawValue(),
          pdt_brand: Ext.getCmp("pdt_brand").getValue(),
          stit_brand_name: Ext.getCmp("pdt_brand").getRawValue(),
          featured: featured,
          stit_long_description: Ext.getCmp("stit_long_description").getValue(),
          stit_quantity: Ext.getCmp("stit_quantity").getValue(),
          popular: popular,
          stit_custInitiate: stit_custInitiate,
          least_package_type_id: Ext.getCmp("least_package_type_id").getValue(),
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
          cosb_package_type_id: Ext.getCmp("cosb_package_type_id").getValue(),
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
          ccsb_package_type_id: Ext.getCmp("ccsb_package_type_id").getValue(),
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
          rs_package_type_name: Ext.getCmp("rs_package_type_id").getRawValue(),
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
          cs_package_type_name: Ext.getCmp("cs_package_type_id").getRawValue(),
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
          ds_package_type_name: Ext.getCmp("ds_package_type_id").getRawValue(),
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
          tstamp: t_stamp,
          itemProcessingTime:Ext.getCmp("itemProcessingTime").getValue(),
        };
        var params = {
          action: "Insert",
          module: "mypha_product",
          op: "saveItemMaster",
          id: ItemId,
          extrainfo: "fsr",
        };
        Application.MyphaProduct.Cache.dup = dup;
        APICall(params, Application.MyphaProduct.saveItemData, form_data);
      } else {
        Ext.MessageBox.alert(
          "Notification",
          "Please enter all required fields"
        );
      }
    } else {
      Ext.MessageBox.alert("Error", "Long Description exceeds 7000 characters");
    }
    WinMask.hide();
  };
  var addItem = function (itemid, dup) {
    var title = itemid == 0 || dup == "D" ? "Create Product" : "Edit Product";
    var item_form = viewItemPanel();
    //var item_attribute = attributeItemPanel();
    var addItem_window = Ext.getCmp("addItem_window");
    if (Ext.isEmpty(addItem_window)) {
      var addItem_window = new Ext.Window({
        id: "addItem_window",
        title: title,
        //iconCls: 'finascop_additem',
        modal: true,
        layout: "fit",
        width: winsize.width * 0.9,
        height: 500,
        //autoHeight: true,
        //shadow: false,
        resizable: false,
        items: [
          new Ext.TabPanel({
            border: false,
            activeTab: 0,
            height: 500,
            deferredRender: false,
            id: "product_tab",
            items: [
              {
                title: "Basic",
                layout: "fit",
                items: item_form,
              } /*,{
                title: 'Attribute',
                  layout: 'fit',
                  items: item_attribute
              }*/,
            ],
          }),
        ],
        buttons: [
          {
            xtype: "button",
            text: "Verify",
            icon: IMAGE_BASE_PATH + "/default/icons/approve.png",
            tabindex: 6,
            align: "right",
            hidden: true,
            id: "PrdctVerify",
            handler: function () {
              if (itemid > 0) {
                Ext.Ajax.request({
                  url: modURL + "&op=verifyProdct",
                  method: "POST",
                  params: {
                    itemid: itemid,
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                      Application.example.msg("Success", tmp.message);

                      if (itemid > 0) {
                        addItem_window.close();
                        Ext.getCmp("itemmaster_grid")
                          .getView()
                          .refreshRow({
                            callback: function (record, options, success) {
                              var gridPanel = Ext.getCmp("itemmaster_grid");
                              var index = gridPanel.store.find(
                                "ItemId",
                                itemid
                              );
                              gridPanel.getSelectionModel().selectRow(index);
                            },
                          });
                      }
                    } else if (tmp.success === true && tmp.valid === false) {
                      //Ext.Msg.alert("Notification.", tmp.message);
                      Application.example.msg("Notification", tmp.message);

                      if (itemid > 0) {
                        addItem_window.close();
                        Ext.getCmp("itemmaster_grid")
                          .getView()
                          .refreshRow({
                            callback: function (record, options, success) {
                              var gridPanel = Ext.getCmp("itemmaster_grid");
                              var index = gridPanel.store.find(
                                "ItemId",
                                itemid
                              );
                              gridPanel.getSelectionModel().selectRow(index);
                            },
                          });
                      }
                    } else if (
                      tmp.success === true &&
                      tmp.img_valid === false
                    ) {
                      Ext.Msg.alert("Notification.", tmp.message);
                    } else {
                      Ext.Msg.alert("Error", tmp.message);
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
          {
            text: "Cancel",
            tabIndex: 140,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            handler: function () {
              addItem_window.close();
            },
          },
          {
            text: "Save",
            hidden: true,
            tabIndex: 139,
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
            handler: function () {
              saveItemMaster(itemid, dup);
            },
          },
        ],
      });
    }
    if (!Ext.isEmpty(itemid)) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      item_form.load({
        url: modURL + "&op=getItemMaster_EditData",
        params: {
          id: itemid,
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        success: function (frm, action) {
          var tmp = Ext.decode(action.response.responseText);

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
            Ext.getCmp("pdt_brand").getStore().load();
            Ext.getCmp("pdt_brand").setRawValue(tmp.data.stit_brand_name);
            Ext.getCmp("product_category").getStore().load();
            Ext.getCmp("product_category").setRawValue(
              tmp.data.stit_category_name
            );
            Ext.getCmp("HSN").getStore().load();
            Ext.getCmp("HSN").setRawValue(tmp.data.stit_HSN_code);
            Ext.getCmp("taxValueId").getStore().baseParams.hsnId = tmp.data.stit_hsnId;
            Ext.getCmp("taxValueId").getStore().load();
            Ext.getCmp("taxValueId").setValue(tmp.data.taxValueId);
            Ext.getCmp("taxValueId").setRawValue(tmp.data.GST);
            Ext.getCmp("stit_package_master").getStore().load();
            Ext.getCmp("stit_package_master").setRawValue(tmp.data.rpckm_name);
            Ext.getCmp("stit_long_description").setValue(
              tmp.data.stit_long_description
            );
            Ext.getCmp("stit_nutritionlabel").setValue(
              tmp.data.stit_nutritionlabel
            );
            Ext.getCmp("stit_ingredients").setValue(tmp.data.stit_ingredients);
            //Ext.getCmp('PrductExtraFeilds').setActiveTab(1);

            Ext.getCmp("stit_preparation_use").setValue(
              tmp.data.stit_preparation_use
            );
            //Ext.getCmp('PrductExtraFeilds').setActiveTab(2);

            Ext.getCmp("stit_allergens").setValue(tmp.data.stit_allergens);
            //Ext.getCmp('PrductExtraFeilds').setActiveTab(3);

            //Ext.getCmp('PrductExtraFeilds').setActiveTab(0);
            //Ext.getCmp('stit_ingredients').setValue(tmp.data.stit_ingredients);
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
                  var catdet = Ext.decode(res.responseText);
                  Ext.getCmp("iteParentCategory").show();
                  Ext.getCmp("iteParentCategory").setValue(
                    catdet.categoryCombination
                  );
                  if(catdet.hasRestaurantService == 1){
                    Ext.getCmp("HSN").getStore().baseParams.query = '996331';
                    Ext.getCmp("HSN").getStore().load();                        
                   
                  }else{
                    Ext.getCmp("HSN").getStore().load();
                    
                  }
                  if(catdet.isPerishable == 1){
                    Ext.getCmp('courierDelivery').disable();
                    Ext.getCmp('courierDelivery').setValue(0);
                  }else{
                    Ext.getCmp('courierDelivery').enable();
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
            Ext.getCmp("PrdctVerify").hide();
          }
        },
      });
    }
    addItem_window.show();
    addItem_window.doLayout();
    addItem_window.center();
  };
  var itemGridAction = function () {
    var action = new Ext.ux.grid.RowActions({
      autoWidth: false,
      hideMode: "display",
      width: 150,
      actions: [
        {
          sortable: false,
          tooltip: "Edit Item",
          iconCls: "finascop_edit",
          callback: function (grid, rec, row, col) {
            addItem(rec.get("ItemId"), "");
          },
        },
        {
          tooltip: "Duplicate",
          iconCls: "product_duplicate",
          callback: function (grid, rec, row, col) {
            addItem(rec.get("ItemId"), "D");
          },
        },
      ],
    });
    return action;
  };
  var productActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "View",
        //iconCls: 'finascop_edit',
        handler: function () {
          var ItemId = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          Application.MyphaProduct.Cache.aspLevel = 0;
          addItem(ItemId, "");
        },
      },
      {
        text: "Duplicate",
        hidden:true,
        //iconCls: 'product_duplicate',
        handler: function () {
          var ItemId = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          Application.MyphaProduct.Cache.aspLevel = 0;
          addItem(ItemId, "D");
        },
      },
      {
        text: "Images",
        //iconCls: 'product_duplicate',
        handler: function () {
          var ItemId = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          Application.VLSLUpload.showImages(ItemId);
        },
      },
      {
        text: "Status Change",
        hidden:true,
        //iconCls: 'product_duplicate',
        handler: function () {
          var ItemId = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          var status = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.stit_status;
          Application.MyphaProduct.StatusChange(ItemId, status, "Product");
        },
      },
      {
        text: "Product Codes",
        handler: function () {
          var ItemId = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          var stit_SKU = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.stit_SKU;
          Application.MyphaProduct.createProductCodes(ItemId, stit_SKU);
        },
      },
      {
        text: "Manage Attribute",
        //iconCls: 'finascop_edit',
        handler: function () {
          var ItemId = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          var product_category = Ext.getCmp("itemmaster_grid")
            .getSelectionModel()
            .getSelections()[0].data.product_category;
          Ext.Ajax.request({
            url: modURL + "&op=getSubCategoryAttributes",
            method: "POST",
            params: {
              prdctCategory: product_category,
            },
            success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              var count = tmp.totalCount;
              if (count > 0) {
                var attributeCombination = tmp.data;
                var attributeIdArray = attributeCombination.map(
                  (item) => item.attributeId
                );
                var nameArray = attributeCombination.map((item) => item.name);
                var valueModeArray = attributeCombination.map(
                  (item) => item.valueMode
                );

                manageAttribute(
                  ItemId,
                  nameArray,
                  count,
                  attributeIdArray,
                  valueModeArray
                );
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Attributes not available"
                );
              }
            },
            failure: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              Ext.MessageBox.alert("Error", "Issue in saving");
            },
          });
        },
      },{
        text: "Google Search",
        handler: function () {
          var stit_SKU = Ext.getCmp("itemmaster_grid")
                .getSelectionModel()
                .getSelections()[0].data.stit_SKU;
              window.open("http://google.com/search?q=" + stit_SKU);
        },
      }
    ],
  });
  var privateProductActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "View",
        //iconCls: 'finascop_edit',
        handler: function () {
          var ItemId = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          Application.MyphaProduct.Cache.aspLevel = 0;
          addItem(ItemId, "");
        },
      },
      {
        text: "Duplicate",
        hidden:true,
        //iconCls: 'product_duplicate',
        handler: function () {
          var ItemId = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          Application.MyphaProduct.Cache.aspLevel = 0;
          addItem(ItemId, "D");
        },
      },
      {
        text: "Images",
        //iconCls: 'product_duplicate',
        handler: function () {
          var ItemId = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          Application.VLSLUpload.showImages(ItemId);
        },
      },
      {
        text: "Status Change",
        hidden:true,
        //iconCls: 'product_duplicate',
        handler: function () {
          var ItemId = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          var status = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.stit_status;
          Application.MyphaProduct.StatusChange(ItemId, status, "Product");
        },
      },
      {
        text: "Product Codes",
        handler: function () {
          var ItemId = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          var stit_SKU = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.stit_SKU;
          Application.MyphaProduct.createProductCodes(ItemId, stit_SKU);
        },
      },
      {
        text: "Manage Attribute",
        //iconCls: 'finascop_edit',
        handler: function () {
          var ItemId = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.ItemId;
          var product_category = Ext.getCmp("privateproduct_grid")
            .getSelectionModel()
            .getSelections()[0].data.product_category;
          Ext.Ajax.request({
            url: modURL + "&op=getSubCategoryAttributes",
            method: "POST",
            params: {
              prdctCategory: product_category,
            },
            success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              var count = tmp.totalCount;
              if (count > 0) {
                var attributeCombination = tmp.data;
                var attributeIdArray = attributeCombination.map(
                  (item) => item.attributeId
                );
                var nameArray = attributeCombination.map((item) => item.name);
                var valueModeArray = attributeCombination.map(
                  (item) => item.valueMode
                );

                manageAttribute(
                  ItemId,
                  nameArray,
                  count,
                  attributeIdArray,
                  valueModeArray
                );
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Attributes not available"
                );
              }
            },
            failure: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              Ext.MessageBox.alert("Error", "Issue in saving");
            },
          });
        },
      },{
        text: "Google Search",
        handler: function () {
          var stit_SKU = Ext.getCmp("privateproduct_grid")
                .getSelectionModel()
                .getSelections()[0].data.stit_SKU;
              window.open("http://google.com/search?q=" + stit_SKU);
        },
      }
    ],
  });
  var manageAttribute = function (
    ItemId,
    nameArray,
    count,
    attributeIdArray,
    valueModeArray
  ) {
    var packagePanel = attributeItemPanel(
      ItemId,
      nameArray,
      count,
      attributeIdArray,
      valueModeArray
    );
    var win_id = "view_attribute_details";
    var view_documents_window = Ext.getCmp(win_id);
    if (Ext.isEmpty(view_documents_window)) {
      view_documents_window = new Ext.Window({
        id: win_id,
        title: "Attribute Details",
        layout: "fit",
        width: winsize.width * 0.4,
        height: winsize.height * 0.6,
        //iconCls: 'icon-add-table',
        plain: false,
        constrain: true,
        modal: true,
        frame: true,
        resizable: true,
        autoScroll: true,
        items: [packagePanel],
        buttons: [
          {
            text: "Cancel",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            iconCls: "finascop_my-icon1",
            handler: function () {
              Ext.getCmp("view_attribute_details").close();
            },
          },
          {
            text: "Save",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_approve.png",
            hidden:true,
            handler: function () {
              var store_form = Ext.getCmp(
                "attributePrdctFormDetails"
              ).getForm();
              if (store_form.isValid()) {
                store_form.submit({
                  url: modURL,
                  waitMsg: "Saving Details....",
                  waitTitle: "Please Wait...",
                  params: {
                    op: "saveAttributeInProducts",
                    apikey: _SESSION.apikey,
                    ItemId: ItemId,
                    count: count,
                    attributeIds: Ext.encode(attributeIdArray),
                  },
                  success: function (response, action) {
                    Ext.getCmp("view_attribute_details").close();
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
          close: function () {},
        },
      });
    }
    Ext.getCmp("attributePrdctFormDetails")
      .getForm()
      .load({
        params: {
          ItemId: ItemId,
          count: count,
          attributeIds: Ext.encode(attributeIdArray),
        },
        url: modURL + "&op=attributePrdct_form_load",
        waitMsg: "Loading...",
        success: function (form, action) {
          var tmp = Ext.decode(action.response.responseText);
        },
        failure: function (form, action) {
          Ext.Msg.alert("Error.", "This error");
        },
      });
    view_documents_window.doLayout();
    view_documents_window.show();
    view_documents_window.center();
  };
  var itemmasterGrid = function () {
    var itemmaster_store = itemmasterStore();
    var action = itemGridAction();
    var ItemMaster_filter = new Ext.ux.grid.GridFilters({
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
          dataIndex: "stit_displaylabel",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "statusName",
        },
        //                {
        //                    type: 'string',
        //                    dataIndex: 'imgCount'
        //                },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isVerified",
        },
      ],
    });
    ItemMaster_filter.remote = true;
    ItemMaster_filter.autoReload = true;
    var itemmaster_grid_panel = new Ext.grid.GridPanel({
      store: itemmaster_store,
      frame: false,
      border: false,
      height: 360,
      id: "itemmaster_grid",
      title: "Products",
      loadMask: true,
      plugins: [ItemMaster_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SKU",
          sortable: true,
          hideable: true,
          dataIndex: "stit_SKU",
        },
        {
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
          xtype: "actioncolumn",
          header: "",
          hideable: true,
          iconCls: "downarrow",
          tooltip: "Choose Actions",
          listeners: {
            click: function (a, grid, rowindex, e) {
              var record = grid.store.getAt(rowindex);
              grid.getSelectionModel().selectRow(rowindex);
              productActionMenu.showAt(e.getXY());
              //action
            },
          },
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
        viewready: updatePagination,
      },
      tbar: [
        /*     <?php if (user_access("mypha_product", "saveItemMaster")) { ?> */
        {
          xtype: "button",
          text: "Create Product",
          tooltip: "Create Product",
          iconCls: "finascop_add",
          hidden: true,
          handler: function () {
            Application.MyphaProduct.Cache.aspLevel = 0;
            addItem(0, "");
          },
        },
        /*<?php } ?> */
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: itemmaster_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
        plugins: [ItemMaster_filter],
      }),
    });

    return itemmaster_grid_panel;
  };

  var createRetalineTransferRequestform = function (itemId, stit_SKU) {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var storeGroupStore = storeGroupStorefn();
    var branchStore = rtrBranchStore(0);
    var poItemStockformPanel = new Ext.form.FormPanel({
      frame: true,
      border: false,
      id: "poItemStockItemFormPanel",
      layout: "column",
      items: [
        {
          layout: "form",
          labelAlign: "top",
          columnWidth: 0.25,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Master ID",
              id: "stit_MasterID",
              name: "stit_MasterID",
              allowBlank: false,
              labelAlign: "top",
              anchor: "99%",
              value: itemId,
            },
          ],
        },
        {
          layout: "form",
          labelAlign: "top",
          columnWidth: 0.75,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "SKU",
              id: "stit_SKU",
              name: "stit_SKU",
              allowBlank: false,
              labelAlign: "top",
              anchor: "95%",
              value: stit_SKU,
            },
          ],
        },
        {
          layout: "form",
          labelAlign: "top",
          columnWidth: 0.15,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Code",
              id: "stit_code",
              name: "stit_code",
              allowBlank: false,
              labelAlign: "top",
              anchor: "99%",
            },
          ],
        },
        {
          layout: "form",
          labelAlign: "top",
          columnWidth: 0.18,
          items: [
            {
              xtype: "combo",
              displayField: "name",
              valueField: "id",
              id: "stit_codeType",
              name: "stit_codeType",
              mode: "local",
              typeAhead: true,
              forceSelection: true,
              fieldLabel: "Code Type",
              editable: true,
              anchor: "97%",
              triggerAction: "all",
              minChars: 2,
              hiddenName: "stit_codeType",
              store: new Ext.data.JsonStore({
                fields: ["id", "name"],
                data: [
                  { id: "Company Barcode", name: "Company Barcode" },
                  { id: "Store Code", name: "Store Code" },
                ],
              }),
              listeners: {
                select: function (combo, record, index) {
                  var type = this.value;
                  if (type == "Company Barcode") {
                    Ext.getCmp("stit_storeGroup").hide();
                    Ext.getCmp("stit_store").hide();
                    Ext.getCmp("stit_isAllStores").hide();
                    Ext.getCmp("allStoresPanel").hide();
                  } else if (type == "Store Code") {
                    Ext.getCmp("stit_storeGroup").show();
                    Ext.getCmp("stit_store").show();
                    Ext.getCmp("stit_isAllStores").show();
                    Ext.getCmp("allStoresPanel").show();
                  }
                },
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.23,
          labelAlign: "top",
          items: [
            {
              xtype: "combo",
              id: "stit_storeGroup",
              name: "stit_storeGroup",
              mode: "local",
              typeAhead: true,
              forceSelection: true,
              fieldLabel: "Store Group",
              editable: true,
              anchor: "97%",
              store: storeGroupStore,
              triggerAction: "all",
              minChars: 2,
              displayField: "store_group_name",
              valueField: "store_group_id",
              hiddenName: "stit_storeGroup",
              listeners: {
                select: function (combo, record, index) {
                  var type = this.value;
                  branchStore.baseParams.store_group = this.value;
                  branchStore.load();
                },
              },
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.13,
          items: [
            {
              labelAlign: "top",
              xtype: "compositefield",
              id: "AllStores",
              name: "AllStores",
              hideLabel: true,
              combineErrors: false,
              border: false,
              items: [
                {
                  xtype: "checkbox",
                  checked: false,
                  inputValue: 1,
                  hideLabel: true,
                  style: {
                    marginRight: "1px",
                    marginLeft: "2px",
                    marginTop: "21px",
                  },
                  id: "stit_isAllStores",
                  name: "stit_isAllStores",
                  inputValue: 1,
                  tabIndex: 14,
                  listeners: {
                    check: function (cb1, checked) {
                      if (checked == true) {
                        Ext.getCmp("stit_store").disable();
                        Ext.getCmp("stit_store").reset();
                      } else {
                        Ext.getCmp("stit_store").enable();
                      }
                    },
                  },
                },
                {
                  bodyStyle: { padding: "20px 5px 5px 0px" },
                  id: "allStoresPanel",
                  border: false,
                  html: "All Stores",
                },
              ],
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.2,
          labelAlign: "top",
          items: [
            {
              xtype: "combo",
              id: "stit_store",
              name: "stit_store",
              mode: "local",
              typeAhead: true,
              forceSelection: true,
              fieldLabel: "Store",
              editable: true,
              anchor: "97%",
              store: branchStore,
              triggerAction: "all",
              minChars: 2,
              displayField: "br_Name",
              valueField: "br_ID",
              hiddenName: "stit_store",
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.1,
          items: [
            {
              xtype: "button",
              text: "Add",
              hidden:true,
              iconCls: "finascop_add",
              style: "margin-top:16px;",
              handler: function () {
                var stit_isAllStores;
                if (
                  !Ext.isEmpty(Ext.getCmp("stit_MasterID").getValue()) &&
                  !Ext.isEmpty(Ext.getCmp("stit_code").getValue()) &&
                  !Ext.isEmpty(Ext.getCmp("stit_codeType").getValue())
                ) {
                  if (Ext.getCmp("stit_isAllStores").getValue() == true) {
                    stit_isAllStores = 1;
                  } else {
                    stit_isAllStores = 0;
                  }

                  Ext.Ajax.request({
                    url: modURL + "&op=addCodesToStore",
                    method: "POST",
                    params: {
                      stit_MasterID: Ext.getCmp("stit_MasterID").getValue(),
                      stit_code: Ext.getCmp("stit_code").getValue(),
                      stit_codeType: Ext.getCmp("stit_codeType").getValue(),
                      stit_storeGroup: Ext.getCmp("stit_storeGroup").getValue(),
                      stit_store: Ext.getCmp("stit_store").getValue(),
                      stit_isAllStores: stit_isAllStores,
                    },
                    success: function (response) {
                      var tmp = Ext.decode(response.responseText);
                      if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg("Success", tmp.msg);
                        Ext.getCmp("productCodeGridPanel")
                          .getStore()
                          .load({
                            params: {
                              itemId: itemId,
                            },
                          });
                        Ext.getCmp("stit_code").reset();
                        Ext.getCmp("stit_codeType").reset();
                        Ext.getCmp("stit_storeGroup").reset();
                        Ext.getCmp("stit_store").reset();
                      } else if (tmp.success === true && tmp.valid === false) {
                        Ext.Msg.alert("Notification.", tmp.msg);
                      } else if (
                        tmp.success === true &&
                        tmp.img_valid === false
                      ) {
                        Ext.Msg.alert("Notification.", tmp.msg);
                      } else {
                        Ext.Msg.alert("Notification", tmp.msg);
                      }
                    },
                    failure: function (response) {
                      var tmp = Ext.util.JSON.decode(response.responseText);
                      Ext.MessageBox.alert("Error", tmp.msg);
                    },
                  });
                } else {
                  Ext.getCmp("poItemStockItemFormPanel").getForm().isValid();
                  Ext.MessageBox.alert(
                    "Notification",
                    "Please enter valid data."
                  );
                }
              },
            },
          ],
        },
      ],
    });
    return poItemStockformPanel;
  };
  var retalineProductCodeItemStore = function (ItemId, stit_SKU) {
    var purchase_invoice_store = new Ext.data.JsonStore({
      method: "post",
      url: modURL + "&op=listProductCodeItemStore",
      fields: [
        "fsipc_id",
        "fsipc_stit_id",
        "fsipc_code",
        "fsipc_codeType",
        "fsipc_storeGroup",
        "fsipc_store",
        "fsipc_createdOn",
        "fsipc_createdBy",
        "br_Name",
        "store_group_name",
      ],
      totalProperty: "totalCount",
      root: "data",
      autoLoad: true,
      listeners: {
        beforeload: function (store, opt) {
          store.baseParams = {
            itemId: ItemId,
          };
        },
      },
    });
    return purchase_invoice_store;
  };
  var createRetalineProductCodeItemStockGrid = function (ItemId, stit_SKU) {
    var productCodeItemGridStore = retalineProductCodeItemStore(
      ItemId,
      stit_SKU
    );
    var poStockEntryGrid = new Ext.grid.GridPanel({
      store: productCodeItemGridStore,
      frame: false,
      border: true,
      height: 250,
      autoScroll: true,
      plugins: [],
      id: "productCodeGridPanel",
      iconCls: "finascop_dataentry",
      loadMask: true,
      columns: [
        {
          header: "Code",
          sortable: true,
          dataIndex: "fsipc_code",
          width: 150,
          tooltip: "Code",
        },
        {
          header: "Type",
          sortable: true,
          dataIndex: "fsipc_codeType",
          align: "right",
          tooltip: "Type",
        },
        {
          header: "Store Group",
          sortable: true,
          dataIndex: "store_group_name",
          align: "right",
          tooltip: "Store Group",
        },
        {
          header: "Store",
          sortable: true,
          dataIndex: "br_Name",
          align: "right",
          tooltip: "Store",
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
              hidden:true,
              tooltip: "Delete Code",
              handler: function (grid, rowIndex, colIndex) {
                var record = grid.store.getAt(rowIndex);
                deleteItem(record.get("fsipc_id"));
              },
            },
            //                        {
            //                            iconCls: 'my-icon18',
            //                            tooltip: 'Edit Order',
            //                            handler: function (grid, rowIndex, colIndex) {
            //                                var record = grid.store.getAt(rowIndex);
            //
            //                            }
            //                        }
          ],
        },
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index) {},
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
      }),
      listeners: {},
      stripeRows: true,
    });
    return poStockEntryGrid;
  };
  var storeGroupStorefn = function () {
    var store = new Ext.data.JsonStore({
      fields: ["store_group_id", "store_group_name"],
      url: modURL + "&op=getStoreGroup",
      autoLoad: true,
      method: "post",
    });
    return store;
  };
  var rtrBranchStore = function (isCPD) {
    var store = new Ext.data.JsonStore({
      fields: ["br_ID", "br_Name"],
      url: modURL + "&op=getBranchName",
      method: "post",
      autoLoad: true,
      listeners: {
        beforeload: function () {
          //this.baseParams.type = isCPD;
        },
      },
    });
    return store;
  };
  var deleteItem = function (id) {
    Ext.MessageBox.confirm(
      "Confirm",
      "Do you want to remove this item?",
      function (btn, text) {
        if (btn == "yes") {
          Ext.Ajax.request({
            waitMsg: "Processing",
            method: "POST",
            url: modURL + "&op=deleteItem",
            params: {
              fsipc_id: id,
            },
            success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              if (tmp.success === true) {
                Application.example.msg("Success", "Removed item");
                Ext.getCmp("productCodeGridPanel").getStore().reload();
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
  var privateProductGrid = function () {
    var itemmaster_store = privateProductStore();
    var action = itemGridAction();
    var ItemMaster_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "store_group_name",
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
          dataIndex: "stit_displaylabel",
        },
        {
          type: "list",
          options: ["Active", "Inactive"],
          phpMode: true,
          dataIndex: "statusName",
        },
        {
          type: "list",
          options: ["Yes", "No"],
          phpMode: true,
          dataIndex: "isVerified",
        },
      ],
    });
    ItemMaster_filter.remote = true;
    ItemMaster_filter.autoReload = true;
    var itemmaster_grid_panel = new Ext.grid.GridPanel({
      store: itemmaster_store,
      frame: false,
      border: false,
      height: 360,
      id: "privateproduct_grid",
      title: "Products",
      loadMask: true,
      plugins: [ItemMaster_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Store Group",
          sortable: true,
          hideable: true,
          dataIndex: "store_group_name",
        },
        {
          header: "SKU",
          sortable: true,
          hideable: true,
          dataIndex: "stit_SKU",
        },
        {
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
          xtype: "actioncolumn",
          header: "",
          hideable: true,
          iconCls: "downarrow",
          tooltip: "Choose Actions",
          listeners: {
            click: function (a, grid, rowindex, e) {
              var record = grid.store.getAt(rowindex);
              grid.getSelectionModel().selectRow(rowindex);
              privateProductActionMenu.showAt(e.getXY());
            },
          },
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
        viewready: updatePagination,
      },
      tbar: [
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: itemmaster_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
        plugins: [ItemMaster_filter],
      }),
    });

    return itemmaster_grid_panel;
  };
  var privateProductStore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listPrivateProducts",
        method: "post",
      }),
      fields: [
        "product_category","store_group_name",
        "ItemId",
        "stit_itemName",
        "hsn_code",
        "product_is_home",
        "stit_category_name",
        "stit_brand_name",
        "imgCount",
        "stit_product_variant",
        "stit_quantity",
        "least_package_type_name",
        { name: "tax", type: "number" },
        { name: "mrp", type: "float" },
        "groups",
        "description",
        "stit_itemERPId",
        "stit_HSN_code",
        { name: "convertable_on", type: "number" },
        { name: "convertable_off", type: "number" },
        { name: "list_in_sales_on", type: "number" },
        { name: "list_in_sales_off", type: "number" },
        { name: "stock_enabled", type: "number" },
        { name: "stock_disabled", type: "number" },
        { name: "list_in_purchase_on", type: "number" },
        { name: "list_in_purchase_off", type: "number" },
        { name: "tangible_on", type: "number" },
        { name: "tangible_off", type: "number" },
        { name: "total_mrp", type: "float" },
        { name: "tax_total", type: "float" },
        "stit_status",
        "statusName",
        "stit_SKU",
        "isVerified",
        "isFeatured",
        "isPopular",
        "stit_displaylabel",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        load: function (store, record, options) {
        },
      },
    });
    store.setDefaultSort("stit_itemName", "ASC");
    return store;
  };
  return {
    Cache: {},
    initItemmaster: function () {
      var _currentStockPanelId = "itemmaster_grid";
      var _masterPanelBrand = Ext.getCmp(_currentStockPanelId);
      if (Ext.isEmpty(_masterPanelBrand)) {
        _masterPanelBrand = itemmasterGrid(_currentStockPanelId);
        Application.UI.addTab(_masterPanelBrand);
        _masterPanelBrand.doLayout();
      } else {
        Application.UI.addTab(_masterPanelBrand);
      }

      //var panelId = 'itemmaster_master_panel';
      //            var panelId = 'itemmaster_grid';
      //            var itemmaster_main = Ext.getCmp(panelId);
      //            if (Ext.isEmpty(itemmaster_main)) {
      //                //itemmaster_main = itemmasterMasterPanel(panelId);
      //                itemmaster_main = itemmasterGrid(panelId);
      //            }
      //            itemmasterData();
      //            Application.UI.addTab(itemmaster_main);
      //            itemmaster_main.doLayout();
      //            return itemmaster_main;
    },
    saveItemData: function () {
      var itemId = Ext.getCmp("itemId").getValue();
      var itemMaster_window = Ext.getCmp("addItem_window");
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
      //Ext.getCmp('PrductExtraFeilds').setActiveTab(1);
      // Ext.getCmp('PrductExtraFeilds').setActiveTab(2)
      //Ext.getCmp('PrductExtraFeilds').setActiveTab(3)
      /*
             
             */
      Ext.Ajax.request({
        url: modURL + "&op=saveItemMaster",
        method: "POST",
        params: {
          stit_stdPacking: stit_stdPacking,
          stit_salesUnit: stit_salesUnit,
          stit_package_type_id: Ext.getCmp("stit_package_type_id").getValue(),
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
          stit_product_variant: Ext.getCmp("stit_product_variant").getValue(),
          product_category: Ext.getCmp("product_category").getValue(),
          pdt_brand: Ext.getCmp("pdt_brand").getValue(),
          featured: featured,
          popular: popular,
          stit_custInitiate: stit_custInitiate,
          stit_long_description: Ext.getCmp("stit_long_description").getValue(),
          stit_quantity: Ext.getCmp("stit_quantity").getValue(),
          apikey: _SESSION.apikey,
          least_package_type_id: Ext.getCmp("least_package_type_id").getValue(),
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
          cosb_package_type_id: Ext.getCmp("cosb_package_type_id").getValue(),
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
          ccsb_package_type_id: Ext.getCmp("ccsb_package_type_id").getValue(),
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
          rs_package_type_name: Ext.getCmp("rs_package_type_id").getRawValue(),
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
          cs_package_type_name: Ext.getCmp("cs_package_type_id").getRawValue(),
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
          ds_package_type_name: Ext.getCmp("ds_package_type_id").getRawValue(),
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
          stit_ingredients: Ext.getCmp("stit_ingredients").getValue(),
          stit_preparation_use: Ext.getCmp("stit_preparation_use").getValue(),
          stit_allergens: Ext.getCmp("stit_allergens").getValue(),
          stit_nutritionlabel: Ext.getCmp("stit_nutritionlabel").getValue(),
          item_length: Ext.getCmp("item_length").getValue(),
          item_breadth: Ext.getCmp("item_breadth").getValue(),
          item_height: Ext.getCmp("item_height").getValue(),
          tstamp: t_stamp,
          itemProcessingTime:Ext.getCmp("itemProcessingTime").getValue(),
        }, //stit_ingredients,stit_preparation_use,stit_allergens,stit_nutritionlabel
        success: function (response) {
          var tmp = Ext.decode(response.responseText);

          if (tmp.success === true) {
            Application.example.msg("Notification", tmp.msg);
            itemMaster_window.close();
            if (itemId > 0) {
              Ext.getCmp("itemmaster_grid")
                .getView()
                .refreshRow({
                  callback: function (record, options, success) {
                    var gridPanel = Ext.getCmp("itemmaster_grid");
                    var index = gridPanel.store.find("ItemId", itemId);
                    gridPanel.getSelectionModel().selectRow(index);
                  },
                });
            } else {
              Ext.getCmp("itemmaster_grid").getStore().load();
            }
          } else {
            Ext.MessageBox.alert("Error", tmp.msg);
            itemMaster_window.close();
          }
        },
        //

        failure: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", "tmp.msg");
        },
      });
    },
    StatusChange: function (id, status, type) {
      Ext.Ajax.request({
        url: modURL + "&op=statusChange",
        method: "POST",
        waitMsg: "Processing",
        params: {
          stit_id: id,
          status: status,
        },
        failure: function (response, options) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", "tmp.msg");
        },
        success: function (response, options) {
          var tmp = Ext.decode(response.responseText);

          if (tmp.success === true) {
            Ext.MessageBox.alert("Notification", tmp.msg);
            if (type == "Product") {
              Ext.getCmp("itemmaster_grid").getStore().load();
            } else {
              Ext.getCmp("medicineproducts_grid").getStore().load();
            }
          } else {
            Ext.MessageBox.alert("Error", tmp.msg);
          }
        },
      });
    },
    createProductCodes: function (ItemId, stit_SKU) {
      var resultWindow = new Ext.Window({
        id: "windowCreateProductCodes",
        iconCls: "vender-items",
        shadow: false,
        height: 625,
        width: 800,
        title: "Manage Product Codes",
        layout: "border",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [
          {
            region: "center",
            layout: "fit",
            height: 175,
            items: createRetalineTransferRequestform(ItemId, stit_SKU),
          },
          {
            region: "south",
            layout: "fit",
            height: 450,
            items: createRetalineProductCodeItemStockGrid(ItemId, stit_SKU),
          },
        ],
        buttons: [
          {
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
            text: "Cancel",
            handler: function () {
              Ext.getCmp("windowCreateProductCodes").close();
              Ext.getCmp("retalineTransferRequest_main_panel")
                .getStore()
                .load();
            },
          },
        ],
        listeners: {
          afterrender: function () {},
        },
      });

      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    },initPrivateProducts: function () {
      var _currentStockPanelId = "privateproduct_grid";
      var _masterPanelBrand = Ext.getCmp(_currentStockPanelId);
      if (Ext.isEmpty(_masterPanelBrand)) {
        _masterPanelBrand = privateProductGrid(_currentStockPanelId);
        Application.UI.addTab(_masterPanelBrand);
        _masterPanelBrand.doLayout();
      } else {
        Application.UI.addTab(_masterPanelBrand);
      }
      //return itemmaster_main;
    }
  };
})();
