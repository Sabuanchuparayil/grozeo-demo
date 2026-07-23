Application.ProductScrapper = (function () {
  var recs_per_page = 18;
  var modURL = "?module=product_scrapper";
  var winLoadMask;
  var winsize = Ext.getBody().getViewSize();
  var loadCount;
  var check_box = new Ext.grid.CheckboxSelectionModel({
    multiSelect: true,
    checkOnly: true,
    listeners: {},
  });
  var tooltipRenderer = function (value,meta,record,rowindx,colindx,store) {
    meta.attr = 'ext:qtip="' + record.get("Value") + '"';
    return value;
  };
  var prdctScrapperMapStore = function (gsId) {
    var store = new Ext.data.JsonStore({
      method: "POST",
      url: modURL + "&op=gsScrapMapData",
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      fields: [
        "id","Title","Value","gsId","isChanged"       
      ],
      listeners: {
        beforeload: function (thisStore, options) {
          thisStore.baseParams.gsId = gsId;
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
  var prdctScrapperStore = function () {
    var store = new Ext.data.JsonStore({
      method: "POST",
      url: modURL + "&op=gsScrapData",
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      fields: [
        "id","Title","Value","gsId"       
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

  var productScrapperGrid = function (gsId,gtinValue,gtinName) {
    var prdctScraprStore = prdctScrapperStore(gsId,gtinValue,gtinName);

    var prdctScrap_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        
        {
          type: "string",
          dataIndex: "Title",
        },
        {
          type: "string",
          dataIndex: "Value",
        }        
      ],
    });
    prdctScrap_filter.remote = true;
    prdctScrap_filter.autoReload = true;

    var prdctScrapGrid = new Ext.grid.GridPanel({
      title:"Product Search",
      store: prdctScraprStore, 
      plugins: [prdctScrap_filter],
      region: "center",
      width: winsize.width * 0.4,
      height: 550,
      frame: true,
      border: true,
      loadMask: true,
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      hideBorders: true,
      id: "gridProductScrap",
      sm: check_box,
      columns: [
        check_box,
        new Ext.grid.RowNumberer(),
        {
          header: "Attribute",
          sortable: true,
          width: 50,
          dataIndex: "Title",
        },
        {
          header: "Result",
          sortable: true,
          dataIndex: "Value",
          width: 180,
          renderer: tooltipRenderer,
        }
      ],bbar:[
        "->",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/Forward.png",
          xtype: "button",
          text: "Map",
          handler: function () {
            var selectitem = Ext.getCmp("gridProductScrap")
              .getSelectionModel()
              .getSelections();              
            var selectedcount = selectitem.length;
            Ext.getCmp("gridProductScrapDataMap").getStore().baseParams.gsId = gsId;
            Ext.getCmp("gridProductScrapDataMap").getStore().load({
              callback: function (record, options, success) {
                if (selectedcount != 0) {
              
                  for (var i = 0; i < selectedcount; i++) {
                    console.log('rowindex');                
                    var row = Ext.getCmp("gridProductScrap").store.indexOf(selectitem[i]);
                    console.log(row);
                    var Scraprecord = Ext.getCmp("gridProductScrap").getStore().getAt(row);
                  var ScrapMapRecord = Ext.getCmp("gridProductScrapDataMap").getStore().getAt(row);
                  ScrapMapRecord.set("Value", Scraprecord.get("Value"));
                  ScrapMapRecord.set("isChanged", 1);
                  }
    
                  
                } else {
                  Ext.MessageBox.alert(
                    "Notification",
                    "Please select and proceed.."
                  );
                }
              },
            });
            
          },
        }]
    });
    return prdctScrapGrid;
  };
  var productMapGrid = function (gsId,gtinValue,gtinName,ind) {
    var prdctScraprStore = prdctScrapperMapStore(gsId,gtinValue,gtinName);

    var prdctScrap_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        
        {
          type: "string",
          dataIndex: "Title",
        },
        {
          type: "string",
          dataIndex: "Value",
        },        
      ],
    });
    prdctScrap_filter.remote = true;
    prdctScrap_filter.autoReload = true;

    var prdctScrapMapGrid = new Ext.grid.GridPanel({
      loadMask: true,
      title:"Product Bank",
      store: prdctScraprStore, 
      plugins: [prdctScrap_filter],
      region: "east",
      width: winsize.width * 0.4,
      height: 550,
      frame: true,
      border: false,
      hideBorders: true,
      loadMask: true,
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      id: "gridProductScrapDataMap",
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
      }),
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Attribute",
          sortable: true,
          width: 50,
          dataIndex: "Title",
        },
        {
          header: "Result",
          sortable: true,
          dataIndex: "Value",
          width: 180,
        }
      ],bbar:[
        "->",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/save.png",
          xtype: "button",
          text: "Save & Proceed",
          handler: function () {
            var selectitem = Ext.getCmp("gridProductScrapDataMap")
              .getSelectionModel()
              .getSelections();
              var mappedScrapData = getMigratedData("gridProductScrapDataMap");
              Ext.Ajax.request({
                url: modURL + "&op=mergeScrapdataToGS1Products",
                method: "POST",
                params: {
                  mappedScrapData: mappedScrapData,gsId:gsId
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp('windowProductScrapper').close();
                    Application.GS1Products.FinalzeProduct(ind);


                    Application.example.msg("Success", tmp.msg);
                    
                  } else {
                    Ext.MessageBox.alert("Notification", tmp.msg);
                  }
                },
                failure: function (response, options) {
                  Ext.MessageBox.alert("Notification", ACTION_FAIL);
                },
              });
          },
        }]
    });
    return prdctScrapMapGrid;
  };
  var getMigratedData = function (gridid) {
    var j = Ext.pluck(Ext.getCmp(gridid).getStore().getRange(), "data");
    return Ext.encode(j);
  };
  return {
    Cache: {},
    initProductSrapper: function (gsId,gtinValue,gtinName,ind) {
      current_type = 1;
      var resultWindow = new Ext.Window({
        id: "windowProductScrapper",
        title: "Search Details",
        shadow: false,
        height: 600,
        width: winsize.width * 0.85,
        layout: "border",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [productScrapperGrid(gsId,gtinValue,gtinName),productMapGrid(gsId,gtinValue,gtinName,ind)],
        tbar: [
          { html: " URL : ", align: "left" },
          {
              xtype: "textfield",
              fieldLabel: "Search by URL",
              id: "tpSiteUrl",
              name: "tpSiteUrl",
              allowBlank: false,
              width:950,
              labelAlign: "top",
              anchor: "95%",
              listeners:{
                  change:function(){
                      var tpSiteUrl = Ext.getCmp('tpSiteUrl').getValue();
                      var substring = "amazon.in";
                      if(tpSiteUrl.includes(substring) != true){
                          Ext.getCmp('tpSiteUrl').reset();
                          Ext.MessageBox.alert("Error", "The URL you enetered is either wrong or incorrect");
                      }
                  }
              }
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
              var tpSiteUrl = Ext.getCmp('tpSiteUrl').getValue();
                      var substring = "amazon.in";
                      if(tpSiteUrl.includes(substring) == true){
                        var myMask = new Ext.LoadMask(Ext.getBody(), {msg:"Please wait..."});
                        myMask.show();
                          Ext.Ajax.request({
                              url: modURL + "&op=fetchScrpData",
                              method: "POST",
                              params: { gtinValue:gtinValue,gtinName: gtinName,tpSiteUrl:tpSiteUrl,gsId:gsId },
                              success: function (res) {
                                var tmp = Ext.decode(res.responseText);
                                if (tmp.success === true && tmp.valid == true) {   
                                  
                                  var gridvalue = Ext.getCmp(
                                    "gridProductScrap"
                                  ).getStore();
                                  gridvalue.baseParams = {
                                    gsId:gsId
                                  };
                                  gridvalue.load();
                                  myMask.hide();
                                }else{
                                  Ext.MessageBox.alert("Notification", tmp.msg);
                                }
                              },
                              failure: function () {
                                Ext.MessageBox.alert("Error", "Error occured while sending data");
                              },
                            });
                      }else{
                          Ext.getCmp('tpSiteUrl').reset();
                          Ext.MessageBox.alert("Error", "The URL you enetered is either wrong or incorrect");
                      }
            },
          }
        ],
        buttons: [],
        listeners: {
          afterrender: function () {},
        },
      });

      resultWindow.doLayout();
      resultWindow.show();
      resultWindow.center();
    },
  };
})();
