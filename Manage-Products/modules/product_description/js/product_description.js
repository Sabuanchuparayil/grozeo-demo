Application.ProductDescription = (function () {
    var recs_per_page = 26;
    var modURL = "?module=product_description";
    var upmodURL = "?module=product_bank_upload";
    var myMask = new Ext.LoadMask(Ext.getBody(), { msg: "Please wait..." });
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
    var pdtDescComboStore = function () {
        var store = new Ext.data.JsonStore({
          url: modURL + "&op=getRelatedData",
          method: "post",
          autoLoad: false,
          fields: ["id", "name"],
          root: "data",
        });
        return store;
      };
      var getProductDetailFn = function(){
        var selectedValue = Ext.getCmp("pdtBankCategory").getValue(); 
        var selectType = Ext.getCmp("selectType").getValue();
        
        if (selectedValue > 0) {
          Ext.Ajax.request({
              waitMsg: "Please wait...",
              url: modURL + "&op=getProductDetails",
              method: "POST",
              params: {
                  selectedValue:selectedValue,
                  selectType:selectType
              },
              success: function (response) {
                var tmp = Ext.decode(response.responseText);
                console.log(tmp);
                if (tmp.success === true) {
                  if(tmp.data.stit_ID > 0){
                    var details = tmp.data.stit_SKU+" of brand "+tmp.data.brand_name+" under category "+tmp.data.sub_category;
                    //var details = "SKU: "+tmp.data.stit_SKU+",Brand: "+tmp.data.brand_name+",Product Master: "+tmp.data.item_name+",Variant: "+tmp.data.stit_product_variant+",Unit: "+tmp.data.stit_quantity;
                    Ext.getCmp("prdctDetails").setValue(details);
                    Ext.getCmp("prdctId").setValue(tmp.data.stit_ID);
                    Ext.getCmp("prdctSKU").setValue(tmp.data.stit_SKU);
                  }else{
                    var details = "";
                    Ext.getCmp("prdctDetails").setValue(details);
                    Ext.getCmp("prdctId").setValue(0);
                    Ext.getCmp("prdctSKU").setValue('');
                  }
                  

                }
              },
              failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.Msg.alert(
                  "Notification",
                  "Data is processing......"
                );
              },
            });
        }
      };
    var productGenerationSKUStore = function () {
    var qugeo_store = new Ext.data.JsonStore({
      url: modURL + "&op=listProductsToMapDescription",
      fields: [
        "stit_ID","stit_brand_name",
        "stit_SKU","prdctType"
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        load: function () {
          this.baseParams.merchantId = Ext.getCmp("merchantId").getValue();
        },
      },
    });
    return qugeo_store;
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
  var productGenerationSKUGrid = function () {
    var productGenerationSKU_store = productGenerationSKUStore();
    var productMasterFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "stit_SKU",
        },
        {
          type: "string",
          dataIndex: "stit_brand_name",
        },
      ],
    });
    productMasterFilter.remote = true;
    productMasterFilter.autoReload = true;

    var grid_panel = new Ext.grid.GridPanel({
      store: productGenerationSKU_store,
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
      id: "descriptionGenerationGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Brand",
          sortable: true,
          dataIndex: "stit_brand_name",
        },
        {
          header: "SKU",
          sortable: true,
          dataIndex: "stit_SKU",
          width: 80,
        },{
          header: "Type",
          sortable: true,
          dataIndex: "prdctType",
        }
        
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
      }),
      listeners: {
        resize: updatePagination,
        celldblClick: function (grid, rowIndex, columnIndex, e) {},
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
            Ext.getCmp('descriptionGenerationGrid').getStore().load({
              params:{
                merchantId:merchantId
              }
            });
          },
        }],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: productGenerationSKU_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      stripeRows: true,
    });
    return grid_panel;
  };
    return {
      Cache: {},
      initProductDescription: function () {
        var setPdtSearchStore = pdtDescComboStore();
        var addNewReturnOrderWindow = new Ext.Window({
          id: "windowForProductDescription",
          iconCls: "",
          shadow: false,
          frame: true,
          width: winsize.width * 0.5,
          height: winsize.height * 0.8,          
          title: "Load Products for Desription Creation",
          layout: "fit",
          resizable: false,
          plain: true,
          constrain: true,
          draggable: true,
          modal: true,
          items: [
            new Ext.FormPanel({
              layout: "column",
              id: "prdctDescriptionFormPanel",
              height: 150,
              columnWidth: 1,
              autoScroll:true,
              frame: true,
              border: false,
              labelAlign: "top",
              items: [
                {
                    layout: "form",
                    columnWidth: 0.40,
                    labelAlign: "top",
                    items: [{
                        xtype: "radiogroup",
                        anchor: "98%",
                        mode: "remote",
                        allowBlank: false,
                        forceSelection: true,
                        triggerAction: "all",
                        lazyRender: true,
                        id: "selectType",
                        minChars: 2,
                        tabIndex: 12,
                        items: [                    
                          {
                            boxLabel: "Sub Category",
                            id: "importType2",
                            name: "selectType",
                            inputValue: "Sub Category",
                            listeners: {
                              check: function (rgp, checked) {
                                if (checked == true) {
                                  Ext.getCmp("prdct_long_description").setValue('');
                                  Ext.getCmp("pdtBankCategory").reset();

                                  Ext.getCmp("pdtBankCategory").getStore().baseParams.type = 1;
                                  Ext.getCmp("pdtBankCategory").getStore().load();
                                }
                              },
                            },
                          },
                          {
                            boxLabel: "Brand",
                            id: "importType3",
                            name: "selectType",
                            inputValue: "Brand",
                            listeners: {
                              check: function (rgp, checked) {
                                if (checked == true) {
                                  Ext.getCmp("prdct_long_description").setValue('');
                                  Ext.getCmp("pdtBankCategory").reset();

                                    Ext.getCmp("pdtBankCategory").getStore().baseParams.type = 2;
                                    Ext.getCmp("pdtBankCategory").getStore().load();                                }
                              },
                            },
                          }
                        ],
                      }                  
                    ]
                },{
                    layout: "form",
                    columnWidth: 0.50,
                    labelAlign: "top",
                    bodyStyle: { "padding-top": "15px" },
                    items: [{
                        xtype: "combo",
                        store: setPdtSearchStore,
                        mode: "local",
                        id: "pdtBankCategory",
                        hideLabel:true,
                        allowBlank: true,
                        fieldLabel: "Sub Category/Brand",
                        hiddenName: "pdtBankCategory",
                        displayField: "name",
                        valueField: "id",
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        minChars: 2,
                        anchor: "98%",
                        triggerAction: "all",
                        lazyRender: true,
                        tabIndex: 523,
                        listeners: {
                          select: function () {
                            var value = Ext.getCmp("pdtBankCategory").getValue();                            
                          },
                        },
                      }                 
                    ]
                },{
                    layout: "form",
                    columnWidth: .10,
                    bodyStyle: { "padding-top": "17px" },
                    labelAlign: "top",
                    items: [    {
                        xtype: "button",
                        text: "Select",
                        align: "right",
                        tabIndex: 524,
                        handler: function () {

                          getProductDetailFn();
                          
                          
                        },
                      }                  
                    ]
                },{
                    layout: "form",
                    columnWidth: 1,
                    labelAlign: "top",
                    bodyStyle: { "padding-top": "15px" },
                    items: [ {
                        xtype: "hidden",
                        fieldLabel: "Product Id",
                        id: "prdctId",
                        name: "prdctId",
                        anchor: "95%",
                        allowBlank: false,
                      },{
                        xtype: "hidden",
                        fieldLabel: "Product SKU",
                        id: "prdctSKU",
                        name: "prdctSKU",
                        anchor: "95%",
                        allowBlank: false,
                      },{
                        xtype: "textarea",
                        fieldLabel: "Short Description",
                        id: "prdctDetails",
                        name: "prdctDetails",
                        allowBlank: false,
                        hideLabel:true,
                        labelAlign: "top",
                        anchor: "98%",
                        tabIndex: 525,
                      }                     
                    ]
                },
                {
                    layout: "form",
                    columnWidth: 1,
                    labelAlign: "top",
                    bodyStyle: { "padding-top": "15px","padding-left": "220px" },
                    items: [   {
                        xtype: "button",
                        text: "Create Product Description",
                        buttonAlign: "center",
                        handler: function () {
                          var prdctDetails = Ext.getCmp("prdctDetails").getValue();
                          var prdctId = Ext.getCmp("prdctId").getValue();
                          var prdctSKU = Ext.getCmp("prdctSKU").getValue();
                          
                          WinMask = new Ext.LoadMask(Ext.getCmp('windowForProductDescription').getEl());
                          WinMask.show();
                          Ext.Ajax.request({
                            url: modURL + "&op=fetchDescription",
                            waitMsg: "Fetching Data...",
                            method: "POST",
                            params: {
                              prdctDetails: prdctDetails,
                              prdctId: prdctId,
                              prdctSKU:prdctSKU
                            },
                            success: function (response) {
                              var tmp = Ext.decode(response.responseText);
                              var aiDescription = tmp.data.candidates[0].content.parts[0].text; 
                              aiDescription = aiDescription
  // Replace **...** with <strong>...</strong>
  .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
  // Handle "* **...**: ..." as <li><strong>...</strong>: ...</li>
  .replace(/(?:^|\n)\* \*\*(.*?)\*\*: (.*?)(?=\n|\*|$)/g, '<li><strong>$1:</strong> $2</li>')
  // Handle "* ..." as <li>...</li> for plain items
  .replace(/(?:^|\n)\* (.*?)(?=\n|\*|$)/g, '<li>$1</li>')
  // Wrap all consecutive <li> elements into a <ul>
  .replace(/(<li>.*?<\/li>)+/gs, '<ul>$&</ul>')
  // Handle headings starting with ##
  .replace(/^##\s*(.+)$/gm, '<strong>$1</strong>')
  // Replace double newlines with paragraph tags
  .replace(/\n\n/g, '</p><p>')
  // Replace single newlines with <br> for line breaks
  .replace(/\n/g, '<br>')
  .trim();
                              //aiDescription = aiDescription// Replace **...** with <strong>...</strong>
                              //.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                              // Replace * ... with <ul><li>...</li></ul>
                             // .replace(/\n\* (.*?)\n/g, '<ul><li>$1</li></ul>\n')
                              // Replace double newlines with paragraph tags
                              //.replace(/\n\n/g, '</p><p>')
                              //.trim(); // Trim any leading/trailing spaces                          
                              if (tmp.success === true) {
                                Ext.getCmp("prdct_long_description").setValue(aiDescription);
                              } else {
                                Ext.Msg.alert("Notification.", "Failed to load data,please check...");
                              }
                              WinMask.hide();
                            },
                            failure: function (response) {
                              var tmp = Ext.util.JSON.decode(response.responseText);
                              Ext.Msg.alert(
                                "Notification",
                                "Data fetching........"
                              );
                              WinMask.hide();
                            },
                          });
                          
                        },
                      }            
                    ]
                },{
                    layout: "form",
                    columnWidth: 1,
                    labelAlign: "top",
                    bodyStyle: { "padding-top": "15px" },
                    items: [         {
                        xtype: "templateeditormce",
                        fieldLabel: "Long Description",
                        anchor: "98%",
                        hideLabel:true,
                        id: "prdct_long_description",
                        name: "prdct_long_description",
                        maxLength: 7000,
                        height: 350,
                        tabIndex: 526,
                        listeners: {},
                      }                
                    ]
                } 
              ],
            }),
          ],
          buttons: [            
            {
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              text: "Reset",
              align: "right",
              handler: function () {
                Ext.getCmp("prdctDescriptionFormPanel").getForm().reset();
                Ext.getCmp("prdct_long_description").setValue('');
              }
            },
            {
              icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
              text: "Save",
              align: "right",
              handler: function () {
                var prdct_long_description = Ext.getCmp("prdct_long_description").getValue();
                var prdctId = Ext.getCmp("prdctId").getValue();
                
                Ext.Ajax.request({
                  waitMsg: "Please wait...",
                  url: modURL + "&op=mapDescriptionToProducts",
                  method: "POST",
                  params: {
                    prdctId: prdctId,
                    prdct_long_description: prdct_long_description
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                      Application.example.msg("Notification", tmp.msg);
                      Ext.getCmp("prdctDetails").reset();
                      Ext.getCmp("prdct_long_description").setValue('');
                      Ext.getCmp("prdctId").reset();
                      Ext.getCmp("prdctSKU").reset();

                      getProductDetailFn();

                    } else {
                      Ext.Msg.alert("Notification.", "Check the loaded data and proceed..");
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
                
              }
            }
            
          ],
          listeners: {
            load: function () {},
          },
        });
  
        addNewReturnOrderWindow.doLayout();
        addNewReturnOrderWindow.show();
        addNewReturnOrderWindow.center();
      },generateProductDescription: function () {
        var addNewReturnOrderWindow = new Ext.Window({
          id: "windowForProductDescription",
          iconCls: "",
          shadow: false,
          frame: true,
          width: winsize.width * 0.5,
          height: winsize.height * 0.8,          
          title: "Load Products for Desription Creation",
          layout: "fit",
          resizable: false,
          plain: true,
          constrain: true,
          draggable: true,
          modal: true,
          items: [productGenerationSKUGrid()],
          fbar: [{
          xtype: "checkbox",
          checked: false,
          id: "showAll",
          name: "showAll",
          inputValue: 1,
          boxLabel: "All",
          listeners: {
            check: function (cb1, checked) {
              Ext.getCmp('enteredLimit').setValue(0);
              Ext.getCmp('enteredLimit').disable();
             
            },
          },
        },{
          html: "&nbsp;Limit : &nbsp;",
        },
        {
          xtype: "textfield",
          id: "enteredLimit",
          name: "enteredLimit",
          anchor: "95%",
          tabIndex: 1,
          value:10,
          maxLength: 3,
        },{
              xtype: "button",
              text: "Generate and Map Product Description",
              id:"descgenerator",
              buttonAlign: "center",
              handler: function () {
                var merchantId = Ext.getCmp("merchantId").getValue();
                var enteredLimit = Ext.getCmp("enteredLimit").getValue();
                Ext.getCmp('descgenerator').disable();
                myMask.show();
                Ext.Ajax.request({
                      url: modURL + "&op=generateDescription",
                      method: "post",
                      waitMsg: "Processing",
                      params: {
                        enteredLimit : enteredLimit,
                        merchantId:merchantId,
                      },
                      success: function (resp) {
                        var res = Ext.decode(resp.responseText);
                        if (res.success === true) {
                          Application.example.msg("Notification", res.msg);
                           Ext.getCmp('descgenerator').disable();
                        }else if(res.success === false){
                          Application.example.msg("Notification", res.msg);
                        }else{
                          Application.example.msg("Notification", "Processing...Proceed aftere some time.");
                        }
                        myMask.hide();
                      addNewReturnOrderWindow.close();
                      },failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.Msg.alert(
                  "Notification",
                  "Data is processing......"
                );
                myMask.hide();
                    addNewReturnOrderWindow.close();
              }
                    });
                    
              },
            },          
            {
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              text: "Reset",
              align: "right",
              handler: function () {
                Ext.getCmp("merchantId").reset();
                Ext.getCmp('descriptionGenerationGrid').getStore().load({
              params:{
                merchantId:0
              }
            });
              }
            }          
            
          ],
          listeners: {
            load: function () {},
          },
        });
  
        addNewReturnOrderWindow.doLayout();
        addNewReturnOrderWindow.show();
        addNewReturnOrderWindow.center();
      }
    };
  })();
  