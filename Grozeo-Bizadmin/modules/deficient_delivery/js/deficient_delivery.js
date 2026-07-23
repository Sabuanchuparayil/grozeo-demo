Application.DeficientDelivery = function () {
    var recs_per_page = 23;
    var modURL = "?module=deficient_delivery";
    var current_type;
    var winLoadMask;
    var winsize = Ext.getBody().getViewSize();
    var loadCount;
    //var winsize = getWindowSize();
    var onGridResize = function (cmp) {
      recs_per_page = finascop_update_recs_per_page(cmp);
    };
  
    var tolerancePanel = function (id) {
      var _adPanel = new Ext.Panel({
        frame: false,
        hideBorders: true,
        layout: "border",
        border: false,
        title: "Delivery Tolerance",
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
              Application.DeficientDelivery.Cache.rtm_id = ID;
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
    
    var loadMask = function (ind) {
        return new Ext.LoadMask(Ext.getCmp('main_panelDeficientDelivery').getEl(),
                {msg: "Please wait..."});
    };    

    var gridSelectionChanged = function () {
        if (!Ext.isEmpty(Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.quor_id;
            var quor_Type = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.quor_Type;
            var quor_Status = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.quor_Status;
            var quor_TransferOrder_Type = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
            var orderMethod = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
            var quor_DeliveryMethodsAllowed = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.quor_DeliveryMethodsAllowed;
            Application.DeficientDelivery.orderStatus = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.dls_DelStatus;
            Application.DeficientDelivery.deficiecyValue = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.amtDeficit;
            Application.DeficientDelivery.amtRequired = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.amtRequired;
            Application.DeficientDelivery.storeShare = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.storeShare;
            Application.DeficientDelivery.paidAmt = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.paidAmt;
            Application.DeficientDelivery.amtAdjusted = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.amtAdjusted;
            Application.DeficientDelivery.deliveryTolerance = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.deliveryTolerance;
            Application.DeficientDelivery.order_delivery_charge = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.order_delivery_charge;
            Application.DeficientDelivery.Cache.quor_id = ID;
            Application.DeficientDelivery.Cache.quor_Status = quor_Status;
            
            Application.DeficientDelivery.ViewMode(ID);
            console.log('Application.DeficientDelivery.Cache.vphbutton', Application.DeficientDelivery.Cache.vphbutton);
            if (Application.DeficientDelivery.Cache.vphbutton == 1) {
                Ext.getCmp('djviewPolledHistory').show();
            } else {
                Ext.getCmp('djviewPolledHistory').hide();
            }
        }
    };
    var deliveryDeficientJobsStore = function (ind) {
        var qugeo_store = new Ext.data.JsonStore({
            url: modURL + '&op=listDeficientDelivery',
            fields: ['fstr_id','booking_no', 'customer', 'source', 'destination', 'quor_PickupName', 'quor_PickupPhone', 'quor_Status', 'quor_Deliverybr_id', 'quor_ItemReturned', 'IsPickup', 'sourcecontact', 'quor_Pickupbr_id',
                'drivetype', 'quor_id', 'bk_brk_br_id', 'quor_PickupLat', 'quor_PickupLng', 'quor_DeliveryLat', 'quor_DeliveryLng', 'pickupmapicon', 'deliverymapicon', 'dls_DelStatus', 'quor_TypeName', 'areaName',
                'quor_ScheduleOpeningTime', 'quor_Type', 'quor_DeliveryName', 'quor_DeliveryPhone', 'quor_TransferOrder_Type', 'orgOrderDate', 'orderMethod', 'quor_DeliveryMethodsAllowed', 'quor_AmountCollectible','quor_Paymode',
                'amtRequired','storeGroup','storeShare','amtDeficit','amtAdjusted','paidAmt','order_delivery_charge','partner_id',{
                    name: 'booked_at',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                }],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp('delivery_deficient_grid').getView().refresh();
                    Ext.getCmp('delivery_deficient_grid').getSelectionModel().selectRow(0);

                },
                beforeload: function () {
                    this.baseParams.ind = ind;
                    this.baseParams.br_id = Ext.getCmp('search_branch' + ind).getValue();
                }
            }
        });
        return qugeo_store;
    };
    function qugeoSelect() {
        return  new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
                selectionchange: gridSelectionChanged
            }
        });
    }
    var ddGridActionMenu = function () {
        return new Ext.menu.Menu({
            items: [{
                    text: "View Log",
                    id: "ddViewLog",
                    handler: function () {
                        var record = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0];
                        orderLogWindow(record.data.booking_no, record.data.fstr_id);
                    }
                }]
        });
    };
    var orderLogWindow = function () {
        var order_generated_id = arguments[0];
        var order_auto_id = arguments[1];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var src = '?module=order_processing&op=oreder_userlog_dtlsview&order_auto_id=' + order_auto_id + '&order_generated_id=' + order_generated_id + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;
        var oreder_userlog_win = Ext.getCmp('ORDER_userlog_win');
        if (Ext.isEmpty(oreder_userlog_win)) {
            var oreder_userlog_win = new Ext.Window({
                id: 'ORDER_userlog_win',
                title: 'Order History ::' + order_generated_id,
                iconCls: 'userlog',
                modal: true,
                height: 400,
                width: 700,
                shadow: false,
                resizable: false,
                items: [
                    new Ext.Panel({
                        layout: 'fit',
                        border: false,
                        width: 700,
                        autoScroll: true,
                        // id: 'oreder_userlog_dtlsview',
                        items: [{
                                region: 'center',
                                border: false,
                                html: '<iframe src="' + src +
                                        '" id="iframeRequest" name="iframeRequest" ' +
                                        'width="100%" height="100%" style="border:none">'
                            }]
                    })
                ],
                buttons: [{
                        iconCls: 'my-icon61',
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        tabIndex: 130,
                        handler: function () {
                            Ext.getCmp('ORDER_userlog_win').close();
                        }
                    }]
            });
        }

        oreder_userlog_win.doLayout();
        oreder_userlog_win.show();
        oreder_userlog_win.center();

    };
    var deliveryDeficientJobsGrid = function (ind) {
        var ddGridActionColumn = ddGridActionMenu();
        var qugeo_store = deliveryDeficientJobsStore(ind);
        var qugeo_select = qugeoSelect();
        var deliveryDeficientJobsFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'booking_no'
                }, {
                    type: 'string',
                    dataIndex: 'quor_PickupName'
                }, {
                    type: 'string',
                    dataIndex: 'quor_PickupPhone'
                },
                {
                    type: 'string',
                    dataIndex: 'quor_TypeName'
                },
                {
                    type: 'string',
                    dataIndex: 'booked_at'
                }]
        });
        deliveryDeficientJobsFilter.remote = true;
        deliveryDeficientJobsFilter.autoReload = true;
        var qugeo_grid = new Ext.grid.GridPanel({
            store: qugeo_store,
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [new Ext.ux.grid.GroupSummary(), deliveryDeficientJobsFilter],
            id: 'delivery_deficient_grid',
            columns: [{
                header: 'Date of Entry',
                sortable: true,
                width: 80,
                dataIndex: 'booked_at',
                renderer: function (value, metadata, record) {
                    dateret = Ext.util.Format.date(value, 'd-m-Y');
                    return dateret;
                }
            },{
                    header: 'Date of Entry',
                    sortable: true,
                    hidden: true,
                    width: 80,
                    dataIndex: 'orgOrderDate'
                }, {
                    header: 'Order No.',
                    sortable: true,
                    dataIndex: 'booking_no',
                    width: 80
                },  {
                    header: 'Consignee',
                    sortable: true,
                    dataIndex: 'customer',
                    hidden: true
                }, {
                    header: 'Amt Calculated',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'order_delivery_charge',
                },{
                    header: 'Store Share',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'storeShare',
                },{
                    header: 'Customer Paid',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'paidAmt',
                },{
                    header: 'Amt Required',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'amtRequired',
                },{
                    header: 'Deficit',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'amtDeficit',
                },{
                    header: 'Adjusted',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'amtAdjusted',
                },{
                    header: 'Store Group',
                    sortable: true,
                    dataIndex: 'storeGroup',
                },{
                    header: 'Store',
                    sortable: true,
                    dataIndex: 'source',
                }, {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'quor_TypeName',
                    width: 80,
                    hidden: true
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'dls_DelStatus',
                    width: 120,
                    hidden: true
                }, {
                    header: 'STH Opening Time',
                    sortable: true,
                    dataIndex: 'quor_ScheduleOpeningTime',
                    tooltip: 'Scheduled Opening Time',
                    hidden: true
                }, {
                    header: 'Customer',
                    sortable: true,
                    dataIndex: 'quor_DeliveryName',
                    hidden: true
                }, {
                    header: 'Customer Contact',
                    sortable: true,
                    dataIndex: 'quor_DeliveryPhone',
                    hidden: true
                }, {
                    hidden: true,
                    header: 'Contact Name',
                    sortable: true,
                    dataIndex: 'quor_PickupName',
                    hidden: true
                }, {
                    header: 'Contact NO',
                    sortable: true,
                    dataIndex: 'quor_PickupPhone',
                    hidden: true
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    tooltip: 'Choose Actions',
                    iconCls: 'downarrow',
                    items: [
                    ], listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            ddGridActionColumn.showAt(e.getXY());
                        }
                    }
                }
            ],
            sm: qugeo_select,
            listeners: {
                resize: onGridResize,
                celldblClick: function (grid, rowIndex, columnIndex, e) {
                }
            },
            tbar: [{html: 'Area : &nbsp;', id: 'branch_label' + ind}, 
                {
                    xtype: 'combo',
                    mode: 'remote',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: areaStore(ind),
                    id: 'search_branch' + ind,
                    triggerAction: 'all',
                    displayField: 'areaName',
                    allowBlank: false,
                    valueField: 'id',
                    hiddenName: 'areaId',
                    name: 'areaId',
                    minChars: 1,
                    tabIndex: 200,
                    listeners: {
                        select: function () {
                            Ext.getCmp('delivery_deficient_grid').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    id: 'id_show' + ind,
                    tabIndex: 201,
                    handler: function () {
                        Ext.getCmp('searchOrderNumber').reset();
                        showData(ind);
                    }
                },{
                    xtype: 'button',
                    text: 'Show Resolved',
                    style: "padding-left: 10px;",
                    tabIndex: 202,
                    handler: function () {
                        var branch = Ext.getCmp('search_branch' + ind).getValue();

                        Ext.getCmp('delivery_deficient_grid').getStore().baseParams.areaId = branch;
                        Ext.getCmp('delivery_deficient_grid').getStore().baseParams.resolved = 1;
                        Ext.getCmp('delivery_deficient_grid').getStore().load();

                    }
                },'->',{
                    xtype: 'textfield',
                    fieldLabel: 'Order Number',
                    hideLabel: true,
                    emptyText: 'Order Number',
                    id: 'searchOrderNumber',
                    name: 'searchOrderNumber',
                    labelAlign: 'left',
                    anchor: '97%',
                    tabIndex: 203
                },{
                    xtype: 'button',
                    text: 'Search',
                    id: 'order_search' + ind,
                    tabIndex: 204,
                    handler: function () {
                        var branch = Ext.getCmp('search_branch' + ind).getValue();
                        if(!Ext.isEmpty(Ext.getCmp('searchOrderNumber').getRawValue())){
                            Ext.getCmp('delivery_deficient_grid').getStore().baseParams.areaId = branch;
                            Ext.getCmp('delivery_deficient_grid').getStore().baseParams.resolved = 0;
                            Ext.getCmp('delivery_deficient_grid').getStore().baseParams.searchOrderNumber = Ext.getCmp('searchOrderNumber').getRawValue();
                            Ext.getCmp('delivery_deficient_grid').getStore().load();
                        }else{
                            Ext.MessageBox.alert('Notification', "Proceed after entering Order Number.");
                        }
                        
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: qugeo_store,
                displayInfo: true,
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true
        });
        return qugeo_grid;
    };
    var showData = function (ind) {
        var branch = Ext.getCmp('search_branch' + ind).getValue();
        Ext.getCmp('delivery_deficient_grid').getStore().baseParams.br_id = branch;
        Ext.getCmp('delivery_deficient_grid').getStore().baseParams.resolved = 0;
        Ext.getCmp('delivery_deficient_grid').getStore().baseParams.searchOrderNumber = "";
        Ext.getCmp('delivery_deficient_grid').getStore().load();

    };
    var DeficientDeliveryDetailsView = function () {        
        var contactsPanel = new Ext.FormPanel({
            region: 'north',
            layout: 'column',
            hidden: true,
            id: "deliveryDeficientForm",
            height: 120,
            labelAlign: 'top',            
            items: [{
                layout: 'form',
                columnWidth: .33,
                frame: false,
                border: false,
                items: [{
                    labelWidth: "180px",
                    xtype: "displayfield",
                    fieldLabel: "Delivery Charges Calculated",
                    id: "calculatedDeliveryCharges",
                    name: "calculatedDeliveryCharges",
                    anchor: "98%",
                    width: 100,
                    tabIndex: 501,
                    style: "color:red;text-transform: uppercase;",
                  }]
            },{
                layout: 'form',
                columnWidth: .33,
                frame: false,
                border: false,
                items: [{
                    labelWidth: 100,
                    xtype: "displayfield",
                    fieldLabel: "Store Share",
                    id: "storeShare",
                    name: "storeShare",
                    anchor: "98%",
                    width: 100,
                    tabIndex: 501,
                    style: "color:red;text-transform: uppercase;",
                  }]
            },{
                layout: 'form',
                columnWidth: .33,
                frame: false,
                border: false,
                items: [{
                    labelWidth: "180px",
                    xtype: "displayfield",
                    fieldLabel: "Delivery Charges Collected",
                    id: "collectedDeliveryCharge",
                    name: "collectedDeliveryCharge",
                    anchor: "98%",
                    width: 100,
                    tabIndex: 501,
                    style: "color:red;text-transform: uppercase;",
                  }]
            },{
                layout: 'form',
                columnWidth: .33,
                frame: false,
                border: false,
                items: [{
                    labelWidth: "180px",
                    xtype: "displayfield",
                    fieldLabel: "Quoted By Delivery Partner",
                    id: "quotedCharge",
                    name: "quotedCharge",
                    anchor: "98%",
                    width: 100,
                    tabIndex: 501,
                    style: "color:red;text-transform: uppercase;",
                  }]
            },{
                layout: 'form',
                columnWidth: .33,
                frame: false,
                border: false,
                items: [{
                    labelWidth: 100,
                    xtype: "displayfield",
                    fieldLabel: "Tolerance",
                    id: "deliveryTolerance",
                    name: "deliveryTolerance",
                    anchor: "98%",
                    width: 100,
                    tabIndex: 501,
                    style: "color:red;text-transform: uppercase;",
                  }]
            },{
                layout: 'form',
                columnWidth: .33,
                frame: false,
                border: false,
                items: [{
                    xtype: "displayfield",
                    fieldLabel: "Deficiency",
                    id: "orderDeficiency",
                    name: "orderDeficiency",
                    style: "color:red;",
                    anchor: "98%",
                    width: 100,
                    tabIndex: 502,
                    value: 0
                  }]
            },{
                layout: 'form',
                columnWidth: .30,
                frame: false,
                border: false,
                items: [{
                    labelWidth: 80,
                    xtype: "displayfield",
                    fieldLabel: "Status",
                    id: "orderStaus",
                    name: "orderStaus",
                    anchor: "98%",
                    width: 150,
                    tabIndex: 501,
                    style: "color:red;text-transform: uppercase;",
                    value: "Deficient Delivery Cost"
                  }]
            },{
                layout: 'form',
                columnWidth: .40,
                frame: false,
                border: false,
                items: [{
                    xtype: 'combo',
                    mode: 'local',
                    fieldLabel: "Resolve",
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select Resolution',
                    anchor: "98%",
                    width: 100,
                    store: new Ext.data.JsonStore({
                        fields: ['id', 'name'],
                        data: [{id: '1', name: 'Merchant bear Deficiency'},{id: '2', name: 'Merchant manage Delivery'}
                            ,{id: '3', name: 'Merchant demands Cancel'}
                        ]
                    }),
                    id: 'resolveId',
                    triggerAction: 'all',
                    displayField: 'name',
                    allowBlank: false,
                    valueField: 'id',
                    hiddenName: 'resolveId',
                    name: 'resolveId',
                    minChars: 1,
                    tabIndex: 503,
                    listeners: {
                        select: function (combo, record, index) {
                            
                            
                        }
                    }
                }]
            },{
                layout: 'form',
                columnWidth: .10,
                frame: false,
                bodyStyle: {"padding-top": "14px"},
                border: false,
                items: [{
                    xtype: "button",
                    text: "Save",
                    tabIndex: 504,
                    handler: function () {
                        var action = Ext.getCmp('resolveId').getValue();
                        var newCharge = Application.DeficientDelivery.amtRequired;
                        var deficiecyValue = Application.DeficientDelivery.deficiecyValue;
                        var partner_id = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.partner_id;
                        deliveryDeficiencyAction(action,newCharge,deficiecyValue,partner_id);
                    }
                  }]
            }],
            listeners:{                
                afterrender:function(){
                    
                }
            }
        });
        return contactsPanel;
    };
    var deliveryDeficientJobsPanel = function (id, ind, title) {
        var src = '';
        //var src = '?module=retaline_deliveryjobs&op=order_details_viewdj&quor_id=' + Application.DeficientDelivery.Cache.quor_id;
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: title,
            id: id,
            iconCls: 'scheduled',
            buttonAlign: "right",
            items: [deliveryDeficientJobsGrid(ind),
                new Ext.Panel({
                    title: 'Order Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelDeficientDeliveryViewDetails',
                    height: winsize.height * 0.6,
                    items: [DeficientDeliveryDetailsView(),
                        {
                        id: 'details_view_panel_deliveryjobs',
                        layout: "fit",
                        region: "south",
                        border: false,
                        hideBorders: true,
                        height: winsize.height * 0.6,
                        html: '<iframe id="iframe_order_deljobdtls" name="iframe_order_deljobdtls" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>',
                    }],
                    fbar: [{
                            text: 'View Delivery Challan',
                            iconCls: 'finascop_viewfile',
                            tooltip: 'View Delivery Challan',
                            id: 'djviewDeliveryChallan',
                            hidden: true,
                            handler: function () {
                                var record = Ext.getCmp("delivery_deficient_grid").getSelectionModel().getSelections()[0];
                                var quor_id = record.data.quor_id;
                                Application.DeficientDelivery.viewDeliveryChallan(quor_id);
                            }
                        }, {
                            text: 'View Drive Polls',
                            iconCls: 'list_users',
                            tooltip: 'View Drive Polls',
                            id: 'djviewPolledHistory',
                            hidden: true,
                            handler: function () {
                                var record = Ext.getCmp("delivery_deficient_grid").getSelectionModel().getSelections()[0];
                                var quor_id = record.data.quor_id;
                                Application.DeficientDelivery.viewPolledHistory(quor_id);
                            }
                        },{
                            text: "Print Invoice",
                            id: "djPrintInvoice",
                            tooltip: 'Print Invoice',
                            hidden: true,
                            handler: function () {
                                var quor_id = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.quor_id;
                                Application.RetalinProcurement.printPackingSlip(quor_id, 'dj');
                        }
                        }, {
                            text: "Delivery Note",
                            id: "djPrintDeliveryNote",
                            tooltip: 'Delivery Note',
                            hidden: true,
                            handler: function () {
                                var quor_id = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0].data.quor_id;
                                Application.RetalinProcurement.printDeliveryNote(quor_id, 'dj');
                            }
                        }

                    ]
                })],
                listeners:{
                    afterrender:function(){
                        
                    }
                }
        });
        return panel;
    };
    var areaStore = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['areaName', 'id'],
            url: modURL + '&op=getArea',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (thisstore, records, options) {
                    showData(ind);
                }
            }
            //totalProperty: 'totalCount'
        });
        return store;
    };
    var mapPanel = function (id, ind, jobrecord) {
        var centerParam = {
            lat: "8.52414",
            lng: "76.93664"
        };
        return {xtype: 'gmappanel',
            region: 'center',
            zoomLevel: 10,
            minGeoAccuracy: 4,
            gmapType: 'map',
            scaleControl: true,
            id: id,
            anchor: '99%',
            mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
            mapControls: ['GSmallMapControl', 'GMapTypeControl', 'NonExistantControl'],
            setCenter: centerParam,
            /* markers: markers,*/
            listeners: {
                afterrender: function () {
                    setTimeout(function () {
                        Ext.getCmp(id).clearMarkers();
                        if (jobrecord) {
                            console.log('jobrecord', jobrecord);

                            Application.DeficientDelivery.Markers[ind] = [];

                            Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + jobrecord.get('booking_no') + '</b>');
                            Ext.getCmp('hdnVehicleId' + ind).setValue('');
                            Ext.getCmp('vehicle_disp' + ind).setValue('');

                            Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);


                            var action = jobrecord.get('drivetype');
                            if (jobrecord.get('drivetype') != 'PICKUP') {
                                var title = jobrecord.get('quor_DeliveryName') + ', ' + jobrecord.get('quor_DeliveryPhone');
                                var vehlatitude = jobrecord.get('quor_DeliveryLat');
                                var vehlongitude = jobrecord.get('quor_DeliveryLng');
                                Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                            } else {
                                var title = jobrecord.get('quor_PickupName') + ', ' + jobrecord.get('quor_PickupPhone');
                                var vehlatitude = jobrecord.get('quor_PickupLat');
                                var vehlongitude = jobrecord.get('quor_PickupLng');
                                Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                            }
                            var mymarker = [];

                            //Ext.getCmp('location_map' + ind).clearMarkers();
                            var bounds = {value: null};
                            loadMarker(jobrecord, ind, mymarker);

                            console.log('vehicle_marker', mymarker);
                            Application.DeficientDelivery.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true, bounds);
                            Ext.getCmp('location_map' + ind).animateMarker(Application.DeficientDelivery.Markers[ind][0], 'BOUNCE', 'START');
                            Ext.getCmp('location_map' + ind).getMap().setZoom(10);

//                        if (!Ext.isEmpty(jobrecord.get('quor_Pickupbr_id'))) {
//                            this.baseParams.br_id = jobrecord.get('quor_Pickupbr_id');
//                            this.baseParams.longitude = vehlongitude;
//                            this.baseParams.latitude = vehlatitude;
//                            this.baseParams.action = action;
//                            this.baseParams.bk_ID = jobrecord.get('quor_id');
//                        } else
//                            return false;

                        }



                        if (id == 'max_map') {
                            Ext.getCmp('max_map').addMarkers(Application.DeficientDelivery.Markers[ind], true);
                        }
                    }, 1000);
                }
            }
        };
    };

    var hiredVehicleStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['v_ID', 'v_No'],
            url: modURL + '&op=gethiredVehicleStore',
            method: 'post',
            autoLoad: true,
        });
        return store;
    };
    var courierCargoStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['mst_courier_id', 'mst_courier_name'],
            url: modURL + '&op=getcourierCargoStore',
            method: 'post',
            autoLoad: true,
        });
        return store;
    };
    var customerPickupFormOld = function () {
        var _customerPickupFormPanel = new Ext.form.FormPanel({
            width: winsize.width * 0.4,
            height: winsize.height * 0.5,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            layout: 'column',
            //bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.34,
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Pickup By',
                            id: 'textfieldDispatchPickup',
                            name: 'textfieldDispatchPickup',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 800,
                            maxLength: 150,
                            listeners: {
                                afterrender: function (field) {
                                    Ext.defer(function () {
                                        field.focus(true, 100);
                                    }, 1000);
                                },
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Mobile Number',
                            id: 'textfieldDispatchPickupMobile',
                            name: 'textfieldDispatchPickupMobile',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 810,
                            maxLength: 10
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'OTP',
                            id: 'textfieldDispatchPickupOtp',
                            name: 'textfieldDispatchPickupOtp',
                            anchor: '95%',
                            allowBlank: true,
                            tabIndex: 820,
                            maxLength: 4
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.50,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'datefield',
                            fieldLabel: 'Date',
                            id: 'textfieldcpd_pickudate',
                            name: 'textfieldcpd_pickudate',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 830,
                            format: "d/m/Y",
                            maxValue: new Date(),
                            value: new Date()
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.50,
                    items: [{
                            xtype: 'timefield',
                            fieldLabel: 'Time',
                            id: 'textfieldcpd_pickuptime',
                            name: 'textfieldcpd_pickuptime',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 840,
                            minValue: '9:00 AM',
                            maxValue: '6:00 PM',
                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                        }]
                }]
        });
        return _customerPickupFormPanel;
    };
    var customerPickupForm = function (quor_Paymode,quor_AmountCollectible) {      
        
        var _customerPickupFormPanel = new Ext.form.FormPanel({
            width: winsize.width * 0.45,
            height: winsize.height * 0.25,
            frame: true,
            border: false,
            labelWidth: 120,
            labelAlign: 'top',
            layout: 'column',
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.33,
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Pickup By',
                            id: 'textfieldDispatchPickup',
                            name: 'textfieldDispatchPickup',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 800,
                            maxLength: 150,
                            listeners: {
                                afterrender: function (field) {
                                    Ext.defer(function () {
                                        field.focus(true, 100);
                                    }, 1000);
                                },
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Mobile Number',
                            id: 'textfieldDispatchPickupMobile',
                            name: 'textfieldDispatchPickupMobile',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 801,
                            maxLength: 10
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'OTP',
                            id: 'textfieldDispatchPickupOtp',
                            name: 'textfieldDispatchPickupOtp',
                            anchor: '95%',
                            allowBlank: true,
                            tabIndex: 807,
                            maxLength: 4
                        }]
                },{
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'datefield',
                            fieldLabel: 'Date',
                            id: 'textfieldcpd_pickudate',
                            name: 'textfieldcpd_pickudate',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 802,
                            format: "d/m/Y",
                            maxValue: new Date(),
                            value: new Date()
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.33,
                    items: [{
                            xtype: 'timefield',
                            fieldLabel: 'Time',
                            id: 'textfieldcpd_pickuptime',
                            name: 'textfieldcpd_pickuptime',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 803,
                            minValue: '9:00 AM',
                            maxValue: '6:00 PM',
                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                        }]
                },{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                        layout: 'form',
                        columnWidth: .33,
                        bodyStyle: {"padding-top": "20px"},
                        labelAlign: 'left',
                        items: [{
                                labelStyle:'font-weight: bold;',
                                xtype: 'displayfield',
                                fieldLabel: 'To be collected',
                                id: 'textfieldTobeCollected',
                                name: 'textfieldTobeCollected',
                                anchor: '95%',
                                tabIndex: 804,
                            }]
                    },{
                        layout: 'form',
                        columnWidth: .33,
                        items: [{
                            xtype: 'radiogroup',
                            anchor: '98%',
                            fieldLabel: '',
                            id: 'deliveredAmtType',
                            tabIndex: 805,
                            items: [{
                                boxLabel: 'Cash',
                                name: 'dat-auto',
                                inputValue: 'Cash',
                                labelWidth: 50,
                                anchor: '95%',
                                tabIndex: 20
                            }, {
                                boxLabel: 'Bank',
                                name: 'dat-auto',
                                inputValue: 'Bank',
                                labelWidth: 50,
                                anchor: '95%',
                                tabIndex: 25
                            }],
                            listeners: {
                                change: function (event, checked) {
                                    var current_firstid = event.items.items[0].inputValue;
                                    var current_secondid = event.items.items[1].inputValue;
                                    //var current_thirdid = event.items.items[2].inputValue;
                                    var radioid = Ext.getCmp('deliveredAmtType').getValue();
                                    console.log('current_secondid', current_secondid);
                                    console.log('radioid', radioid);
                                    if (radioid == current_secondid)
                                    {
                                        Ext.getCmp('bankTransactionNo').show();
                                    } else if (radioid == current_firstid)
                                    {
                                        Ext.getCmp('bankTransactionNo').hide();
                                    }
                                }
                            }
                        }]
                    },{
                        layout: 'form',
                        columnWidth: 0.33,
                        hideLabel:true,
                        items: [{
                            emptyText: 'Transaction Id',
                            id: 'bankTransactionNo',
                            name: 'bankTransactionNo',
                            xtype: 'textfield',
                            labelAlign: 'top',
                            anchor: '98%',
                            minLength: 2,
                            allowBlank: true,
                            maxLength: 300,
                            tabIndex: 806,
                            hidden: true
                        }]
                    }]
                }],
                listeners:{
                    afterrender: function(){
                        var tobecollected = (quor_AmountCollectible > 0?quor_AmountCollectible:quor_Paymode);
                        Ext.getCmp('textfieldTobeCollected').setValue(tobecollected);
                        if(quor_AmountCollectible > 0){
                            Ext.getCmp('deliveredAmtType').show();
                        }else{
                            Ext.getCmp('deliveredAmtType').hide();
                        }
                    }
                }
        });
        return _customerPickupFormPanel;
    };
    var discourierCargoForm = function () {
        var _discourierCargoFormFormPanel = new Ext.form.FormPanel({
            width: winsize.width * 0.4,
            height: winsize.height * 0.4,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            layout: 'column',
            //bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.50,
                    items: [{
                            xtype: 'combo',
                            displayField: 'mst_courier_name',
                            valueField: 'mst_courier_id',
                            mode: 'local',
                            id: 'dispatchCourier',
                            forceSelection: true,
                            fieldLabel: 'Cargo / Courier',
                            emptyText: 'Select',
                            anchor: '95%',
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            editable: true,
                            allowBlank: false,
                            minChars: 2,
                            tabIndex: 300,
                            store: courierCargoStore(),
                            listeners: {
                                afterrender: function (field) {
                                    Ext.defer(function () {
                                        field.focus(true, 100);
                                    }, 1000);
                                },
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.50,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Way Bill Number',
                            id: 'textfieldQCNnumber',
                            name: 'textfieldQCNnumber',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 310,
                            maxLength: 20
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.50,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'datefield',
                            fieldLabel: 'Date',
                            id: 'textfieldcpd_couriredate',
                            name: 'textfieldcpd_couriredate',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 320,
                            format: "d/m/Y",
                            maxValue: new Date(),
                            value: new Date()
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.50,
                    items: [{
                            xtype: 'timefield',
                            fieldLabel: 'Time',
                            id: 'textfieldcpd_couriretime',
                            name: 'textfieldcpd_couriretime',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 330,
                            minValue: '9:00 AM',
                            maxValue: '6:00 PM',
                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                        }]
                }]
        });
        return _discourierCargoFormFormPanel;
    };
    var AddNewDispatchForm = function () {
        var _dispatchFormPanel = new Ext.form.FormPanel({
            width: 100,
            height: 100,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'radiogroup',
                                            labelWidth: 100,
                                            fieldLabel: 'Vehicle Type',
                                            id: 'radiobuttonVehicleType',
                                            items: [{
                                                    boxLabel: 'Leased',
                                                    name: 'rb-auto',
                                                    inputValue: 'Leased',
                                                    labelWidth: 100,
                                                    tabIndex: 500,
                                                    listeners: {
                                                        afterrender: function (field) {
                                                            Ext.defer(function () {
                                                                field.focus(true, 100);
                                                            }, 1000);
                                                        }
                                                    }
                                                },
                                                {
                                                    boxLabel: 'Hired',
                                                    name: 'rb-auto',
                                                    inputValue: 'Hired',
                                                    labelWidth: 100,
                                                    tabIndex: 510,
                                                },
                                            ],
                                            listeners: {
                                                change: function (event, checked)
                                                {
                                                    var radioid = Ext.getCmp('radiobuttonVehicleType').getValue();

                                                    switch (radioid) {
                                                        case 'Leased':
                                                            Ext.getCmp('hiredVehicle').hide();
                                                            Ext.getCmp('hiredVehicle').allowBlank = true;
                                                            break;
                                                        case 'Hired':
                                                            Ext.getCmp('hiredVehicle').show();
                                                            Ext.getCmp('hiredVehicle').allowBlank = false;
                                                            break;
                                                    }
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDriver').reset();
                                                    Ext.getCmp('textfieldDispatchDriverContact').reset();
                                                    Ext.getCmp('textfieldDispatchLrgcn').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDispatchdate').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDispatchtime').reset();
                                                }
                                            }
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'v_No',
                                            valueField: 'v_ID',
                                            mode: 'local',
                                            id: 'hiredVehicle',
                                            forceSelection: true,
                                            fieldLabel: 'Vehicle',
                                            emptyText: 'Select Vehicle',
                                            anchor: '95%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            hidden: true,
                                            tabIndex: 520,
                                            store: hiredVehicleStore(),
                                            listeners: {
                                                select: function (e, b) {
                                                    //var hiredVehicle = Ext.getCmp('hiredVehicle').getRawValue();
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').setValue(Ext.getCmp('hiredVehicle').getRawValue());
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').setReadOnly(true);
                                                }
                                            }
                                        }]
                                }]
                        }]
                },
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Vehicle Number',
                                            id: 'textfieldcpd_dispatchVehicleNo',
                                            name: 'textfieldcpd_dispatchVehicleNo',
                                            anchor: '95%',
                                            allowBlank: false,
                                            tabIndex: 530,
                                            maxValue: 100,
                                            maxLength: 20,
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Driver',
                                            id: 'textfieldcpd_dispatchDriver',
                                            name: 'textfieldcpd_dispatchDriver',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 540,
                                            maxLength: 100
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Contact No',
                                            id: 'textfieldDispatchDriverContact',
                                            name: 'textfieldDispatchDriverContact',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 550,
                                            maxLength: 20
                                        }]
                                }]
                        }]

                }, {
                    layout: 'column',
                    items: [{
                            columnWidth: 1,
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'LR / GCN Number',
                                            id: 'textfieldDispatchLrgcn',
                                            name: 'textfieldDispatchLrgcn',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 560,
                                            maxLength: 20
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'datefield',
                                            fieldLabel: 'Dispatch Date',
                                            id: 'textfieldcpd_dispatchDispatchdate',
                                            name: 'textfieldcpd_dispatchDispatchdate',
                                            anchor: '95%',
                                            allowBlank: false,
                                            tabIndex: 570,
                                            format: "d/m/Y",
                                            maxValue: new Date(),
                                            value: new Date()
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'timefield',
                                            fieldLabel: 'Dispatch Time',
                                            id: 'textfieldcpd_dispatchDispatchtime',
                                            name: 'textfieldcpd_dispatchDispatchtime',
                                            anchor: '97%',
                                            allowBlank: false,
                                            tabIndex: 580,
                                            minValue: '9:00 AM',
                                            maxValue: '6:00 PM',
                                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                                        }]
                                }]
                        }]

                }]
        });
        return _dispatchFormPanel;
    };
    var showMap = function (ind) {

        var panel = new Ext.Panel({
            layout: 'border',
            items: mapPanel('max_map', ind, '')
        });

        var map_win = new Ext.Window({
            width: winsize.width * 0.9,
            height: winsize.height * 0.9,
            plain: true,
            modal: true,
            frame: true,
            constrainHeader: true,
            layout: 'fit',
            items: panel,
            buttons: [{
                    text: 'Cancel',
                    tabIndex: '10',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        map_win.close();
                    }
                }]
        });

        map_win.doLayout();
        map_win.show(this);
        map_win.center();

    };
    var assignSchedule = function (ind) {
        Ext.Msg.show({
            title: 'Saving!',
            msg: '',
            progressText: 'Please Wait...',
            width: 300,
            progress: true,
            closable: false,
            wait: true
        });
        var sel = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections()[0];
        var selectitem = Ext.getCmp('delivery_deficient_grid').getSelectionModel().getSelections();
        var selectedcount = selectitem.length;
        var quor_idarr = [];
        for (var i = 0; i < selectedcount; i++)
        {
            quor_idarr.push(selectitem[i].data.quor_id);
        }

        if (!Ext.isEmpty(sel) && !Ext.isEmpty(Ext.getCmp('hdnVehicleId' + ind).getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=saveQugeo',
                method: 'POST',
                params: {
                    quorIds: Ext.encode(quor_idarr),
                    qugeobk_NO: sel.get('quor_id'), //quor_Deliverybr_id,quor_Pickupbr_id
                    br_id: (sel.get('drivetype') == 'PICKUP' ? sel.get('quor_Pickupbr_id') : sel.get('quor_Deliverybr_id')),
                    hdnVehicleId: Ext.getCmp('hdnVehicleId' + ind).getValue(),
                    type: sel.get('drivetype'),
                    handling_br_id: (sel.get('drivetype') == 'PICKUP' ? sel.get('quor_Pickupbr_id') : sel.get('quor_Deliverybr_id'))
                },
                success: function (res) {

                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success == true) {
                        Ext.Msg.hide();
                        Ext.MessageBox.alert('Notification', tmp.msg, function (btn) {
                            if (btn == 'ok')
                                Ext.getCmp('pushToDriveWindow').close();
                            showData(ind);
                        });

                    } else {
                        Ext.Msg.hide();
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }
                }
            });
        } else {
            Ext.MessageBox.alert('Notification', "Please select a booking and vehicle");
        }
    };
    var vehicleStore = function (jobrecord, ind) {
        var store = new Ext.data.JsonStore({
            //proxy: proxy,
            url: modURL + '&op=loadVehicle',
            fields: ['v_ID', 'v_No', 'Latitude', 'Longitude', 'LastLocationDtTm',
                'DriverName', 'Vehicletypename', 'updated_date', 'vehicle_marker',
                'MaxLoad', 'CurrentLoad', 'v_MapIcon','mobno'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    console.log('jobrecord', jobrecord);

                    Application.DeficientDelivery.Markers[ind] = [];

                    Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + jobrecord.get('booking_no') + '</b>');
                    Ext.getCmp('hdnVehicleId' + ind).setValue('');
                    Ext.getCmp('vehicle_disp' + ind).setValue('');

                    Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);


                    var action = jobrecord.get('drivetype');
                    if (jobrecord.get('drivetype') != 'PICKUP') {
                        var title = jobrecord.get('quor_DeliveryName') + ', ' + jobrecord.get('quor_DeliveryPhone');
                        var vehlatitude = jobrecord.get('quor_DeliveryLat');
                        var vehlongitude = jobrecord.get('quor_DeliveryLng');
                        Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                    } else {
                        var title = jobrecord.get('quor_PickupName') + ', ' + jobrecord.get('quor_PickupPhone');
                        var vehlatitude = jobrecord.get('quor_PickupLat');
                        var vehlongitude = jobrecord.get('quor_PickupLng');
                        Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                    }
                    Ext.getCmp('location_map' + ind).clearMarkers();

                    if (!Ext.isEmpty(jobrecord.get('quor_Pickupbr_id'))) {
                        this.baseParams.br_id = jobrecord.get('quor_Pickupbr_id');
                        this.baseParams.longitude = vehlongitude;
                        this.baseParams.latitude = vehlatitude;
                        this.baseParams.action = action;
                        this.baseParams.bk_ID = jobrecord.get('quor_id');
                    } else
                        return false;


                },
                load: function (vehicle_store, e) {
                    // loadMarker(jobrecord, ind);
                }
            }
        });
        return store;
    };
    var loadMarker = function (jobrecord, ind, parkmymarker) {
        var loadingMask = loadMask(ind);
        loadingMask.show();


        var action = jobrecord.get('drivetype');

        var title = jobrecord.get('quor_DeliveryName') + ', ' + jobrecord.get('quor_DeliveryPhone');
        parkmymarker.push({
            lat: jobrecord.get('quor_DeliveryLat'),
            lng: jobrecord.get('quor_DeliveryLng'),
            marker: {
                title: title,
                draggable: false
            },
            icon: jobrecord.get('deliverymapicon')/*GMAP_LOCATION_ICON*/
        });

        if (jobrecord.get('drivetype') == 'PICKUP') {
            title = '';
            title = jobrecord.get('quor_PickupName') + ', ' + jobrecord.get('quor_PickupPhone');
            Ext.getCmp('save_schedule' + ind).setText('To Pickup');
            parkmymarker.push({
                lat: jobrecord.get('quor_PickupLat'),
                lng: jobrecord.get('quor_PickupLng'),
                marker: {
                    title: title,
                    draggable: false
                },
                icon: jobrecord.get('pickupmapicon')/*GMAP_LOCATION_ICON*/
            });
        } else {
            Ext.getCmp('save_schedule' + ind).setText('To Deliver');
        }



        //var parkmymarker = [];


        loadingMask.hide();
        // Application.DeficientDelivery.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(parkmymarker, true);
        // Ext.getCmp('location_map' + ind).getMap().setZoom(10);
    };
    var vehicleGrid = function (jobrecord, ind) {
        var store = vehicleStore(jobrecord, ind);
        var sm = new Ext.grid.RowSelectionModel({
            singleSelect: true
        });
        var grid = new Ext.grid.GridPanel({
            store: store,
            height: 200,
            viewConfig: {
                forceFit: true
            },
            id: 'vehicle_grid' + ind,
            columns: [
                {
                    header: 'V. Reg. No.',
                    dataIndex: 'v_No'
                }, {
                    header: 'D. Name',
                    dataIndex: 'DriverName'
                },{
                    header: 'Mobile',
                    dataIndex: 'mobno'
                }, {
                    header: 'V. Type',
                    dataIndex: 'Vehicletypename'
                }, {
                    header: 'Last Updation',
                    dataIndex: 'LastLocationDtTm',
                    renderer: function (val, meta, rec) {
                        var selected_date = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                        var show = selected_date[1] + '-' + selected_date[2] + '-' + selected_date[3] + ' ' + selected_date[4] + ':' + selected_date[5] + ':' + selected_date[6];
                        //rec.set('updated_date', show);
                        return show;
                    }
                }],
            sm: sm,
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var vehicle_marker = record.get('vehicle_marker');
                    console.log('record', record);
                    var vehicle_store = grid.getStore();
                    if (vehicle_store.getCount() > 0) {
                        var store = vehicle_store.getRange();
                        /*      var veh_marker = [];*/
                        var a = 1;
                        //Ext.each(store, function (rec) {
                        // console.log('vrec', rec);
                        var mymarker = [];
                        mymarker.push({
                            lat: record.get('Latitude'),
                            lng: record.get('Longitude'),
                            marker: {
                                title: record.get('v_No'),
                                draggable: false
                            },
                            listeners: {
                                click: function () {
                                    var extraInfo = this.extraInfo;
                                    //Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);
                                }
                            },
                            icon: record.get('v_MapIcon'),
                            extraInfo: record.data
                        });
                        record.set('vehicle_marker', a);
                        a++;
                        console.log('vehicle_marker', a);
                        Ext.getCmp('location_map' + ind).clearMarkers();
                        console.log('vehicle_marker1', mymarker);
                        loadMarker(jobrecord, ind, mymarker);
                        console.log('vehicle_marker', mymarker);
                        Application.DeficientDelivery.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true);
                        Ext.getCmp('location_map' + ind).animateMarker(Application.DeficientDelivery.Markers[ind][0], 'BOUNCE', 'START');
                        Ext.getCmp('location_map' + ind).getMap().setZoom(10);

                        // });
                    }
                    setTimeout(function () {
                        Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);
                        Ext.getCmp('vehicle_disp' + ind).setValue('Vehicle No. : <b>' + record.get('v_No') + '</b>');
                        Ext.getCmp('hdnVehicleId' + ind).setValue(record.get('v_ID'));
                    }, 50);
                    // Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);



                    setTimeout(function () {
                        Ext.getCmp('location_map' + ind).animateMarker(Application.DeficientDelivery.Markers[ind][0], 'BOUNCE', 'STOP');
                    }, 2000);


                }
            },
            stripeRows: true
        });
        return grid;
    };
    var _gbHistoryStore = function (orderId, orderType, type) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPolledHistoryDj',
                method: 'post'
            }),
            fields: ['boy_name', 'ordersreqtatus', 'created_at', 'updated_at', 'accepted_time', 'scan_start_time', 'last_scan_time', 'completed_time', 'isopenPoll'],
            totalProperty: 'totalCount',
            root: 'data',
            // remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.orderId = orderId;
                }
            }
        });
        return _Store;
    };
    var _gbHistoryGrid = function (orderId) {
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _gbHistoryStore(orderId),
            iconCls: 'money',
            autoScroll: true,
            width: winsize.width * 0.4,
            height: 300,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforGBHistory',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Boy',
                    dataIndex: 'boy_name',
                    sortable: true,
                    tooltip: 'Boy',
                    hideable: false,
                    width: 150
                },
                {
                    header: 'Status',
                    dataIndex: 'ordersreqtatus',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false
                }, {
                    header: 'Is Open Poll',
                    dataIndex: 'isopenPoll',
                    sortable: true,
                    tooltip: 'Is Open Poll',
                    hideable: false
                },
                {
                    header: 'Created At',
                    dataIndex: 'created_at',
                    sortable: true,
                    tooltip: 'Created At',
                    hideable: false
                },
                {
                    header: 'Last Updated At',
                    dataIndex: 'updated_at',
                    sortable: true,
                    tooltip: 'Updated At',
                    hideable: false
                },
                {
                    header: 'Accepted On',
                    dataIndex: 'accepted_time',
                    sortable: true,
                    tooltip: 'Accepted On',
                    hidden: true,
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforGBHistory').getStore().load({
                        params: {
                            orderId: orderId
                        }
                    });
                }
            }
        });
        return _dispatchgridPanel;
    };
    var driverPickupForm = function () {
        var _customerPickupFormPanel = new Ext.form.FormPanel({
            width: winsize.width * 0.4,
            height: winsize.height * 0.5,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            layout: 'column',
            labelAlign: 'top',
            //bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.34,
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Pickup By',
                            id: 'textfieldDispatchPickup',
                            name: 'textfieldDispatchPickup',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 400,
                            maxLength: 150,
                            listeners: {
                                afterrender: function (field) {
                                    Ext.defer(function () {
                                        field.focus(true, 100);
                                    }, 1000);
                                },
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Mobile Number',
                            id: 'textfieldDispatchPickupMobile',
                            name: 'textfieldDispatchPickupMobile',
                            anchor: '95%',
                            allowBlank: false,
                            tabIndex: 410,
                            maxLength: 10,
                            minLength: 10,
                            listeners: {
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.33,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'OTP',
                            id: 'textfieldDispatchPickupOtp',
                            name: 'textfieldDispatchPickupOtp',
                            anchor: '95%',
                            allowBlank: true,
                            tabIndex: 420,
                            maxLength: 4
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.50,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'datefield',
                            fieldLabel: 'Date',
                            id: 'textfieldcpd_pickudate',
                            name: 'textfieldcpd_pickudate',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 430,
                            format: "d/m/Y",
                            maxValue: new Date(),
                            value: new Date()
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.50,
                    items: [{
                            xtype: 'timefield',
                            fieldLabel: 'Time',
                            id: 'textfieldcpd_pickuptime',
                            name: 'textfieldcpd_pickuptime',
                            anchor: '97%',
                            allowBlank: false,
                            tabIndex: 440,
                            minValue: '9:00 AM',
                            maxValue: '6:00 PM',
                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                        }]
                }]
        });
        return _customerPickupFormPanel;
    };

    var manualDeliveryForm = function () {
        var _manualDeliveryFormPanel = new Ext.form.FormPanel({
//            width: winsize.width * 0.4,
//            height: winsize.height * 0.3,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            items: [{
                    layout: 'column',
                    border: false,
                    items: [{
                            layout: 'form',
                            columnWidth: .33,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'datefield',
                                    fieldLabel: 'Date',
                                    id: 'textfieldManualDeliveredDate',
                                    name: 'textfieldManualDeliveredDate',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 10,
                                    format: "d/m/Y",
                                    maxValue: new Date(),
                                    value: new Date(),
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: .33,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'timefield',
                                    fieldLabel: 'Time',
                                    id: 'textfieldManualDeliveredtime',
                                    name: 'textfieldManualDeliveredtime',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 20,
                                    minValue: '9:00 AM',
                                    maxValue: '6:00 PM',
                                    value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .34,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Delivered By',
                                    id: 'textfieldManualDelivered',
                                    name: 'textfieldManualDelivered',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 30,
                                    maxLength: 150,
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1000);
                                        },
                                    }
                                }]
                        }]
                }]
        });
        return _manualDeliveryFormPanel;
    };

    var deliveryDeficiencyAction = function(action,newCharge,deficiecyValue,partner_id){

        Ext.Msg.show({
            title: 'Saving!',
            msg: '',
            progressText: 'Please Wait...',
            width: 300,
            progress: true,
            closable: false,
            wait: true
        });
        

        if (Application.DeficientDelivery.Cache.quor_id > 0 && Application.DeficientDelivery.Cache.quor_Status == 41) {
            Ext.Ajax.request({
                url: modURL + '&op=actionOnDeliveryDeficientOrder',
                method: 'POST',
                params: {
                    action:action,
                    quor_id: Application.DeficientDelivery.Cache.quor_id,
                    newCharge:newCharge,
                    deficiencyValue:deficiecyValue,
                    partner_id:partner_id
                },
                success: function (res) {

                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success == true) {
                        Ext.Msg.hide();
                        Ext.MessageBox.alert('Notification', tmp.msg, function (btn) {
                            if (btn == 'ok')
                            showData('dd');
                        });

                    } else {
                        Ext.Msg.hide();
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }
                }
            });
        } else {
            Ext.MessageBox.alert('Notification', "Choose a valid order to proceed");
        }
    };
    var areaTolerancePanel = function (id) {
        var _atPanel = new Ext.Panel({
          frame: false,
          hideBorders: true,
          layout: "border",
          border: false,
          title: "Area Tolerance",
          id: id,
          items: [areaToleranceGrid()],
        });
        return _atPanel;
      };
      var areaToleranceGridstore = function () {
        var _toleranceList = new Ext.data.GroupingStore({
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listareatolerance",
            method: "post",
          }),
          reader: new Ext.data.JsonReader(
            {
              totalProperty: "totalCount",
              idProperty: "id",
              root: "data",
            },
            ["id", "areaName", "tolerance"]
          ),
          sortInfo: {
            field: "id",
            direction: "ASC",
          },
          groupField: "",
          groupDir: "ASC",
          remoteSort: true,
          autoLoad: false,
          root: "data",
          listeners: {
            load: function () {
              Ext.getCmp("areaToleranceGridPanel").getSelectionModel().selectRow(0);
            },
          },
        });
        return _toleranceList;
      };
    
      var areaToleranceGrid = function () {
        var _areaToleranceGridstore = areaToleranceGridstore();
        var _areaToleranceFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "areaName",
            }
          ],
        });
        _areaToleranceFilter.remote = true;
        _areaToleranceFilter.autoReload = true;
        var _gridPanel = new Ext.grid.EditorGridPanel({
          region: "center",
          layout: "fit",
          frame: false,
          border: false,
          loadMask: true,
          store: _areaToleranceGridstore,
          //iconCls: 'money',
          id: "areaToleranceGridPanel",
          view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            emptyText:
              '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),
          plugins: [new Ext.ux.grid.GroupSummary(), _areaToleranceFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Area",
              dataIndex: "areaName",
              sortable: true,
              tooltip: "Area",
              hideable: true,
            },
            {
              header: "Tolerance",
              dataIndex: "tolerance",
              sortable: true,
              tooltip: "Tolerance",
              hideable: true,
              editor: {
                allowBlank: false,
                xtype: 'numberfield'
                }
            }
          ],
          viewConfig: {
            forceFit: true,
          },
          tbar: ['->',{ html: "&nbsp;Delivery Tolerance : &nbsp;" },
            {
              xtype: "numberfield",
              fieldLabel: "Value",
              id: "defaultTolerance",
              name: "defaultTolerance",
              anchor: "98%",
              editable:false,
              allowBlank: false,
              width: 100,
              tabIndex: 501,
              value: _SESSION.DEFAULT_DT +' %'
            }],
          bbar: new Ext.PagingToolbar({
            pageSize: recs_per_page,
            store: _areaToleranceGridstore,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
                
            },
          }),
          listeners: {
            cellClick: function (grid, rowIndex, columnIndex, e) {
              var record = grid.getStore().getAt(rowIndex);
              var ID = record.get("id");
    
              if (!Ext.isEmpty(ID)) {
                Application.DeficientDelivery.Cache.areaId = ID;
              }
            },
            resize: onGridResize,
            afteredit: updateAreaTolerance,
            afterrender: function () {
              _areaToleranceGridstore.load();
            },
          },
        });
        return _gridPanel;
      };
      function updateAreaTolerance(grid_event) {
        var data = Ext.encode(grid_event.record.data);
        Ext.Ajax.request({
            waitMsg: 'Please wait...',
            url: modURL + '&op=updateAreaTolerance',
            params: {
                data: Ext.encode(grid_event.record.data),
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', 'Save Failed');
            },
            success: function (response, options) {
                if (response.responseText != "") {
                    eval('var tmp=' + response.responseText);
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Application.example.msg('Notification', tmp.msg);
                    } else if (tmp.success === false) {
                        Ext.MessageBox.alert('Notification', 'Error Occured while saving', function (btn) {
                            if (btn == 'ok') {
                            }
                        });
                    }
                }
            }
        });
    }
    return {
      Cache: {},
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
      },addDeficientDelivery: function () {
        Application.DeficientDelivery.Markers = [];
        Application.DeficientDelivery.Markers['dj'] = [];
        var main_panel_id = 'main_panelDeficientDelivery';
        var main_panel = Ext.getCmp(main_panel_id);
        var title = 'Deficient Delivery';
        if (Ext.isEmpty(main_panel)) {
            main_panel = deliveryDeficientJobsPanel(main_panel_id, 'dd', title);
            Application.UI.addTab(main_panel);
            main_panel.doLayout();
        } else {
            Application.UI.addTab(main_panel);
        }
    },ViewMode: function () {
        
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var quor_id = arguments[0];
        Ext.getCmp('details_view_panel_deliveryjobs').show();
              
        Ext.getCmp("panelDeficientDeliveryViewDetails").doLayout();
        Ext.get('iframe_order_deljobdtls').dom.src = modURL + '&op=order_details_viewdd&quor_id=' + quor_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;     
        if(quor_id > 0){
            Ext.getCmp('deliveryDeficientForm').show();
            Ext.getCmp('orderStaus').setValue(Application.DeficientDelivery.orderStatus);
            Ext.getCmp('orderDeficiency').setValue(Application.DeficientDelivery.deficiecyValue);
            Ext.getCmp('deliveryTolerance').setValue(Application.DeficientDelivery.deliveryTolerance);
            Ext.getCmp('quotedCharge').setRawValue(Application.DeficientDelivery.amtRequired);
            Ext.getCmp('collectedDeliveryCharge').setRawValue(Application.DeficientDelivery.order_delivery_charge);
            Ext.getCmp('storeShare').setRawValue(Application.DeficientDelivery.storeShare);
            Ext.getCmp('calculatedDeliveryCharges').setRawValue(Application.DeficientDelivery.paidAmt);
        }else{
            Ext.getCmp('deliveryDeficientForm').hide();
        } 
       
    },areaTolerance: function () {
        var _areaTolerancePanelId = "panelTolerance";
        var _areaTolerancePanel = Ext.getCmp(_areaTolerancePanelId);
        if (Ext.isEmpty(_areaTolerancePanel)) {
            _areaTolerancePanel = areaTolerancePanel(_areaTolerancePanelId);
          Application.UI.addTab(_areaTolerancePanel);
          _areaTolerancePanel.doLayout();
        } else {
          Application.UI.addTab(_areaTolerancePanel);
        }
      }
    };
  }();