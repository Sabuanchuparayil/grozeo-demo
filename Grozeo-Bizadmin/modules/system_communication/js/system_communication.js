Application.SystemCommunication = (function () {
  var recs_per_page = 12;
  var modURL = "?module=system_communication";
  var winsize = Ext.getBody().getViewSize();
  var updatePagination = function (cmp) {
    recs_per_page = updateRecsPerPage(cmp);
  };
  var qtipRenderer = function (
    value,
    metadata,
    record,
    rowIndex,
    colIndex,
    store
  ) {
    metadata.attr = 'ext:qtip="' + value + '"  ext:qwidth="auto" ';
    return value;
  };
  var Communicationstore = function () {
    var store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listCommunications",
        method: "post",
      }),
      fields: [
        "id",
        "type",
        "typeId",
        "isRequired",
        "title",
        "message",
        "isRequiredStatus",
        "typeName","isActive","isActiveStatus"
      ],
      totalProperty: "totalCount",
      root: "data",
      autoLoad: true,
      listeners: {
        beforeload: function () {
          this.baseParams.current_type = Ext.getCmp("radiobuttonFinascopStockId").getValue();
        },
      },
    });
    return store;
  };
  var masterPanelforCommunicationsGrid = function (id) {
    var communication_store = Communicationstore();
    var branch_filter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "typeName",
        },
        {
          type: "string",
          dataIndex: "title",
        },
      ],
    });
    branch_filter.remote = true;
    branch_filter.autoReload = true;

    var grid_panel = new Ext.grid.GridPanel({
      store: communication_store,
      layout: "fit",
      frame: false,
      border: false,
      title: "Communications",
      plugins: [branch_filter],
      id: id,
      loadMask: true,
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Type",
          sortable: true,
          hideable: true,
          dataIndex: "typeName",
          tooltip: "Type",
        },
        {
          header: "Title",
          sortable: true,
          hideable: true,
          dataIndex: "title",
          tooltip: "Title",
        },
        {
          header: "Message",
          sortable: true,
          hideable: true,
          dataIndex: "message",
          tooltip: "Message",
          renderer: qtipRenderer,
        },
        {
          header: "Is Controllable",
          sortable: true,
          hideable: true,
          dataIndex: "isRequiredStatus",
          tooltip: "Is Controllable",
        },{
          header: "Status",
          sortable: true,
          hideable: true,
          dataIndex: "isActiveStatus",
          tooltip: "Status",
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          icon: "./resources/images/submenuicons/action.png",
          tooltip: "Choose Actions",
          items: [
            {
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var isRequired = data.isRequired;
                if (isRequired == 1) {
                  this.items[0].tooltip = "Set As Not Controllable";
                  return "status_enabled";
                } else {
                  this.items[0].tooltip = "Set As Controllable";
                  return "status_disabled";
                }
              },
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Ext.Msg.confirm(
                  "Notification",
                  "Do you really want to mark?",
                  function (btn) {
                    if (btn == "yes") {
                      Application.SystemCommunication.masrkAsRequired(
                        record.get("id"),
                        record.get("isRequired")
                      );
                    }
                  }
                );
              },
            },{
              getClass: function (v, meta, rec) {
                var data = rec.data;
                var isActive = data.isActive;
                if (isActive == 1) {
                  this.items[1].tooltip = "Mark as Inactive";
                  return "now_active";
                } else {
                  this.items[1].tooltip = "Mark as Active";
                  return "now_inactive";
                }
              },
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                Ext.Msg.confirm(
                  "Notification",
                  "Do you really want to mark?",
                  function (btn) {
                    if (btn == "yes") {
                      Application.SystemCommunication.markAsActive(
                        record.get("id"),
                        record.get("isActive")
                      );
                    }
                  }
                );
              },
            }
          ],
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
        resize: updatePagination,
        afterrender: function () {},
      },
      tbar: [
        {
          xtype: "radiogroup",
          width: 200,
          id: "radiobuttonFinascopStockId",
          //columnWidth: 0.4,
          items: [
            {
              boxLabel: "SMS",
              name: "rb-auto",
              inputValue: 1,
              labelWidth: 150,
              id: "medradio",
              checked: true,
            },
            {
              boxLabel: "Email",
              name: "rb-auto",
              inputValue: 2,
              labelWidth: 150,
            },
            {
              boxLabel: "WatsApp",
              name: "rb-auto",
              inputValue: 2,
              labelWidth: 150,
            },
          ],
          listeners: {
            change: function (event, checked) {
              var current_firstid = event.items.items[0].inputValue;
              var current_secondid = event.items.items[1].inputValue;
              var current_thirdid = event.items.items[2].inputValue;
              var radioid = Ext.getCmp("radiobuttonFinascopStockId").getValue();

              var gridvalue = Ext.getCmp(id).getStore();

              gridvalue.baseParams = {
                current_type: radioid,
              };
              gridvalue.load();
            },
          },
        },
      ],
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: communication_store,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No records to display",
      }),
      stripeRows: true,
    });
    return grid_panel;
  };
  return {
    Cache: {},
    initCommunicationMaster: function () {
      var _CommunicationMasterPanelId = "panelMasterMainCommunicationMasterGrid";
      var _masterPanelCommunicationMaster = Ext.getCmp(
        _CommunicationMasterPanelId
      );
      if (Ext.isEmpty(_masterPanelCommunicationMaster)) {
        _masterPanelCommunicationMaster = masterPanelforCommunicationsGrid(
          _CommunicationMasterPanelId
        );
        Application.UI.addTab(_masterPanelCommunicationMaster);
        _masterPanelCommunicationMaster.doLayout();
      } else {
        Application.UI.addTab(_masterPanelCommunicationMaster);
      }
    },masrkAsRequired:function(){
      var t = new Date();
            var id = arguments[0];
            var isRequired = arguments[1];
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=setAsRequired',
                method: 'POST',
                params: {id: id, isRequired: isRequired},
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Application.example.msg('Success', tmp.message);
                        Ext.getCmp('panelMasterMainCommunicationMasterGrid').getStore().reload();
                    } else {
                        Ext.Msg.alert("Error", tmp.message);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
    },markAsActive:function(){
      var t = new Date();
            var id = arguments[0];
            var isActive = arguments[1];
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=setStatus',
                method: 'POST',
                params: {id: id, isActive: isActive},
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Application.example.msg('Success', tmp.message);
                        Ext.getCmp('panelMasterMainCommunicationMasterGrid').getStore().reload();
                    } else {
                        Ext.Msg.alert("Error", tmp.message);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
    }
  };
})();
