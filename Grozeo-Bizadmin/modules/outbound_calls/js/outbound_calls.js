Application.OutboundCalls = (function () {
  var RECS_PER_PAGE = 23;
  var callRecLastParameters,jobLogLastParameters;
  var modURL = "?module=outbound_calls";
  var winsize = Ext.getBody().getViewSize();
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var qtipRenderer = function (value, metadata, record, rowIndex, colIndex, store) {
    metadata.attr = 'ext:qtip="' + value + '"  ext:qwidth="auto" ';
    return value;

};
  var masterPanelforOutboundCalls = function (id) {
    var _mpanelforOutboundCalls = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Outbound Calls",
      id: id,
      iconCls: "my-icon448",
      items: [OutboundCallsMainGrid(),
        obJoblogGrid()],
    });
    return _mpanelforOutboundCalls;
  };
  var OutboundJobLogStore = function () {
    var __obJobLogGridStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listObJobLogs",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        ["callAction","callRemarks","createdOn","createdBy","entryFrom","entryFromName","callActionName",
        "createdByName","createdDate"]
      ),
      sortInfo: {
        field: "createdOn",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {        
      },
    });   
    return __obJobLogGridStore;
  };
  var obJoblogGrid = function () {
    var _outboundJobLogStore = OutboundJobLogStore();
    var __obJobLogGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
      {
        type: "string",
        dataIndex: "createdOn",
      },
    ],
    });
    __obJobLogGridFilter.remote = true;
    __obJobLogGridFilter.autoReload = true;
    var _outboundJobLogGridPanel = new Ext.grid.GridPanel({
      region: "east",
	  width: winsize.width * 0.3,
		height: winsize.height * 0.6,
      layout: "fit",
      frame: true,
      border: false,
      loadMask: true,
      store: _outboundJobLogStore,
      iconCls: "money",
      title:"Job Log",
      id: "gridObJobLogGrid",      
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), __obJobLogGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Date",
          dataIndex: "createdOn",
        },
        {
          header: "User",
          dataIndex: "createdByName",
        },{
          header: "From",
          dataIndex: "entryFromName",
        },{
          header: "Action",
          dataIndex: "callActionName",
        },
        {
          header: "Remarks",
          dataIndex: "callRemarks",
          renderer: qtipRenderer,
        }
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _outboundJobLogStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,       
      }),
      listeners: {
      afterrender: function () {},
    },
      tbar: [
      ],
    });
    return _outboundJobLogGridPanel;
  };
  var OutboundCallsMasterStore = function () {
    var _outboundcallsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listOutboundCalls",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "eventId",
          "eventName",
          "calleeId",
          "calleeName",
          "calleeMobile",
          "calleeType",
          "calleeTypeName",
          "eventRank",
          "status",
          "statusName",
          "createdDate",
          "createdTime",
          "assignedTo",
          "assignedOn",
          "callerName",
          "followupDate","followupOn"
        ]
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
          Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
            .getSelectionModel()
            .selectRow(0);

            var ojcount = this.getCount();
            if(ojcount > 0){
              if(ojcount == 1){
                Ext.getCmp('ojExitbtn').show();
                Ext.getCmp('ojStartbtn').show();
              }else{
                Ext.getCmp('ojStartbtn').show();
                Ext.getCmp('ojExitbtn').hide();
              }              
            }else{
              Ext.getCmp('ojStartbtn').hide();
              Ext.getCmp('ojExitbtn').hide();
            }
        },
      },
    });
    return _outboundcallsMasterStore;
  };
  var OutboundCallsMainGrid = function () {
    var _outboundcallsStore = OutboundCallsMasterStore();
    var _outboundcallsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "date",
          dataIndex: "createdDate",
        },{
          type: "date",
          dataIndex: "createdDate",
        },
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "calleeName",
        },
        {
          type: "string",
          dataIndex: "calleeMobile",
        },
        {
          type: "string",
          dataIndex: "calleeTypeName",
        },
        {
          type: "string",
          dataIndex: "statusName",
        },
        {
          type: "string",
          dataIndex: "callerName",
        },
      ],
    });
    _outboundcallsGridFilter.remote = true;
    _outboundcallsGridFilter.autoReload = true;
    var _outboundcallsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _outboundcallsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewOutboundCallsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _outboundcallsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Date of Registration",
          hideable: false,
        },
        {
          header: "Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Time",
          hideable: false,
        },
        {
          header: "Event",
          dataIndex: "eventName",
          sortable: true,
          tooltip: "Event",
          hideable: false,
        },
        {
          header: "Callee",
          dataIndex: "calleeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Mobile",
          dataIndex: "calleeMobile",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Type of Call",
          dataIndex: "calleeTypeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Instance Type",
          dataIndex: "instanceType",
          hidden: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Rank",
          dataIndex: "eventRank",
          sortable: true,
          tooltip: "Rank",
          hideable: false,
        },
        {
          header: "Status",
          dataIndex: "statusName",
          sortable: true,
          tooltip: "Status",
          hideable: false,
        },
        {
          header: "Caller",
          dataIndex: "callerName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },{
          header: "Followup On",
          dataIndex: "followupOn",
          sortable: true,
          tooltip: "Followup On",
          hideable: false,
        },
        {
          header: "Followup Date",
          dataIndex: "followupDate",
          sortable: true,
          tooltip: "Followup Date",
          hideable: false,
        },
        {
          header: "Actions",
          hideable: true,
          xtype: "actioncolumn",
          sortable: false,
          groupable: false,
          items: [/*     <?php if (user_access("outbound_calls", "actioncoloumn")) { ?> */
            {
              iconCls: "move-to-contactss",
              tooltip: "Start Job",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                console.log(record);
                var jobId = record.get("id");
                var calleeMobile = record.get("calleeMobile");
                var calleeType = record.get("calleeType");
                Application.OutboundCalls.AcceptJob(
                  jobId,
                  calleeMobile,
                  calleeType
                );
              },
            }/*<?php } ?> */
          ],
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _outboundcallsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedoutboundcalls,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("store_group_id");
          if (!Ext.isEmpty(ID)) {
            Application.OutboundCalls.Cache.store_group_id = ID;
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _outboundcallsStore.load();          
          
        },load:function () {
          
        }
      },
      tbar: [
        {
          iconCls: "refresh",
          xtype: "button",
          hidden:true,
          text: "Generate Jobs",
          tooltip: "Generate Jobs",
          icon: "./resources/images/default/icons/add.png",
          handler: function () {
            Ext.Ajax.request({
              url: modURL + "&op=startJob",
              method: "POST",
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  Application.example.msg("Success", tmp.msg);
                  _outboundcallsStore.load();
                } else {
                  Ext.MessageBox.alert("Error", tmp.msg);
                }
              },
              failure: function () {
                Ext.MessageBox.alert(
                  "Error",
                  "Error occured while sending data"
                );
              },
            });
          },
        },
        {
          iconCls: "finascop_add",
          xtype: "button",
          text: "Create Job",
          tooltip: "Create Job",          
          icon: "./resources/images/default/icons/add.png",
          handler: function () {
            Application.OutboundCalls.createJobDetails();
          },
        },
        {
          iconCls: "move-to-contactss",
          xtype: "button",
          text: "Start/Resume Job",
          id:"ojStartbtn",
          hidden:true,
          tooltip: "Start/Resume Job",
          icon: "./resources/images/default/icons/add.png",
          handler: function () {
            Application.OutboundCalls.ChooseJob();
          },
        },
        {
          xtype: "button",
          text: "Exit Job",
          tooltip: "Exit Job",
          id:"ojExitbtn",
          hidden:true,
          icon: "./resources/images/default/icons/delete.png",
          handler: function () {
            Ext.Ajax.request({
              waitMsg: "Processing",
              url: modURL,
              params: {
                op: "exitJobFromUser",
              },
              failure: function (response, options) {
                Ext.MessageBox.alert("Notification", ACTION_FAIL);
              },
              success: function (response, options) {
                eval("var tmp=" + response.responseText);
                if (tmp.success === true) {
                  Application.example.msg("Success", tmp.msg);
                  _outboundcallsStore.load();
                }
              },
            });
          },
        },
      ],
    });
    return _outboundcallsmaingridPanel;
  };
  var mtsptActionMenu = function (e) {
    var myTickActionMenu = new Ext.menu.Menu({
      items: [
        {
          text: "Start Job",
          handler: function () {
            var jobId = Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
              .getSelectionModel()
              .getSelections()[0].data.id;
            var calleeMobile = Ext.getCmp(
              "gridpanelMasterDataviewOutboundCallsdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.calleeMobile;
            var calleeType = Ext.getCmp(
              "gridpanelMasterDataviewOutboundCallsdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.calleeType;
            Application.OutboundCalls.AcceptJob(
              jobId,
              calleeMobile,
              calleeType
            );
          },
        },
      ],
    });
    myTickActionMenu.showAt(e.getXY());
  };
  var gridSelectionChangedoutboundcalls = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
        .getSelectionModel()
        .getSelections()[0].data.store_group_id;
        var jobId = Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
        .getSelectionModel()
        .getSelections()[0].data.id;
        
            Ext.getCmp("gridObJobLogGrid")
              .getStore()
              .load({
                params: {
                  jobId: jobId,
                },
              });

    }
  };
  var gridSelectionChangedFollowupCalls = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewFollowupCallsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewFollowupCallsdata")
        .getSelectionModel()
        .getSelections()[0].data.store_group_id;
    }
  };
  var gridSelectionChangedoutboundEvents = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewOutboundEventsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewOutboundEventsdata")
        .getSelectionModel()
        .getSelections()[0].data.id;
    }
  };
  var OutboundEventsMasterStore = function () {
    var _outboundcallsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listOutboundEvents",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        ["id", "UserId", "eventName", "userName", "rank"]
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
          Ext.getCmp("gridpanelMasterDataviewOutboundEventsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewOutboundEventsdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _outboundcallsMasterStore;
  };
  var masterPanelforOutboundEvents = function (id) {
    var _mpanelforOutboundEvents = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Call Events",
      id: id,
      iconCls: "my-icon448",
      items: [OutboundEventsMainGrid()],
    });
    return _mpanelforOutboundEvents;
  };
  var OutboundEventsMainGrid = function () {
    var _outboundeventsStore = OutboundEventsMasterStore();
    var _outboundcallsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "userName",
        },
      ],
    });
    _outboundcallsGridFilter.remote = true;
    _outboundcallsGridFilter.autoReload = true;
    var _outboundcallsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _outboundeventsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewOutboundEventsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _outboundcallsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Events",
          dataIndex: "eventName",
          sortable: true,
          tooltip: "Events",
          hideable: false,
        },
        {
          header: "User",
          dataIndex: "userName",
          sortable: true,
          tooltip: "User",
          hideable: false,
        },
        {
          header: "Rank",
          dataIndex: "rank",
          sortable: true,
          tooltip: "Rank",
          hideable: false,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _outboundeventsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedoutboundEvents,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
        },
        resize: onGridResize,
        afterrender: function () {
          _outboundeventsStore.load();
        },
      },
      tbar: [],
    });
    return _outboundcallsmaingridPanel;
  };
  var availableCallEventStore = function (userId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAvailableCallEvents",
        method: "post",
      }),
      fields: ["id", "eventName", "pdtCount"],
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
  var check_box = new Ext.grid.CheckboxSelectionModel({
    multiSelect: true,
    checkOnly: true,
    showHeaderCheckbox: false,
  });
  var availableCallEventGrid = function (userId) {
    var availableCallEvent_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
      ],
    });
    availableCallEvent_filter.remote = true;
    availableCallEvent_filter.autoReload = true;
    var _availSugridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      title: "Unassigned Call Events",
      frame: false,
      border: false,
      loadMask: true,
      store: availableCallEventStore(userId),
      //iconCls: 'money',
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforAvailableCallEvents",
      sm: check_box,
      plugins: [availableCallEvent_filter],
      columns: [
        check_box,
        new Ext.grid.RowNumberer(),
        {
          header: "CallEvents",
          dataIndex: "eventName",
          sortable: true,
          tooltip: "CallEvents",
          hideable: false,
          width: 200,
        },
      ],
      bbar: [
        "->",
        {
          icon: IMAGE_BASE_PATH + "/default/icons/Forward.png",
          xtype: "button",
          text: "Map to User",
          handler: function () {
            var selectitem = Ext.getCmp("gridpanelforAvailableCallEvents")
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var brandarr = [];
            for (var i = 0; i < selectedcount; i++) {
              brandarr.push(selectitem[i].data.id);
            }
            if (selectedcount != 0) {
              Application.OutboundCalls.mapCallEventToUser(brandarr, userId);
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
      listeners: {
        afterrender: function (grid) {},
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _availSugridPanel;
  };
  var userMappedCallEventStore = function (userId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listUserMappedCallEvents",
        method: "post",
      }),
      fields: ["id", "eventName", "pdtCount"],
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
  var mappedUserCallEvents = function (userId) {
    var mappedUserCallEvent_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
      ],
    });
    mappedUserCallEvent_filter.remote = true;
    mappedUserCallEvent_filter.autoReload = true;
    var _mapedSUgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: userMappedCallEventStore(userId),
      //iconCls: 'money',
      height: 500,
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforUserMappedCallEvents",
      plugins: [mappedUserCallEvent_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Call Events",
          dataIndex: "eventName",
          sortable: true,
          tooltip: "Call Events",
          hideable: false,
          width: 200,
        },
        {
          xtype: "actioncolumn",
          header: "Action",
          hideable: true,
          sortable: false,
          groupable: false,
          tooltip: "Action",
          items: [
            {
              iconCls: "arch",
              tooltip: "Cancel Call Event",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);

                Ext.Ajax.request({
                  waitMsg: "Processing",
                  url: modURL,
                  params: {
                    op: "removeCallEventFromUser",
                    supportUnitId: record.get("suId"),
                    userId: userId,
                  },
                  failure: function (response, options) {
                    Ext.MessageBox.alert("Notification", ACTION_FAIL);
                  },
                  success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {
                      Application.example.msg("Notification", tmp.msg);
                      Ext.getCmp("gridpanelforUserMappedCallEvents")
                        .getStore()
                        .load({
                          params: {
                            userId: userId,
                          },
                        });
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
      listeners: {
        afterrender: function () {},
        rowdblclick: function (grid, rowIndex, e) {},
      },
    });
    return _mapedSUgridPanel;
  };
  var masterPanelforOutboundCallLog = function (id) {
    var _mpanelforOutboundEvents = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Job Logs",
      id: id,
      iconCls: "my-icon448",
      items: [
        OutboundCallLogMainGrid(),
        new Ext.Panel({
          title: "Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "callLogDetailParentPanel",
          height: winsize.height * 0.6,
          items: [callLogMasterDetailsView()],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return _mpanelforOutboundEvents;
  };
  var callLogGridStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listCallLogs",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "articleId",
          root: "data",
        },
        [
          "id",
          "actionBy",
          "actionOn",
          "actionRemark",
          "jobTitle",
          "calleeName",
          "calleeMobile",
          "caller",
          "eventName","createdOn","isManual","createdBy","calledOn"
        ]
      ),
      sortInfo: {
        field: "id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function (a,b,options) {
          Ext.getCmp("callLogMasterGrid").getView().refresh();
          Ext.getCmp("callLogMasterGrid").getSelectionModel().selectRow(0);    
          jobLogLastParameters = options.params;      
        },
      },
    });
    return _store;
  };
  var OutboundCallLogMainGrid = function () {
    var CallerStore = new Ext.data.JsonStore({
      fields: ['UserId', 'UserName'],
      url: modURL + '&op=getUserName',
      autoLoad: true,
      method: 'post',
      listeners: {
          load: function (thisstore, records, options) {
          }
      }
  });
    var callLogGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "calleeName",
        },
        {
          type: "string",
          dataIndex: "caller",
        },
        {
          type: "string",
          dataIndex: "calleeMobile",
        },{
          type: "date",
          dataIndex: "calledOn",
        },
      ],
    });
    callLogGridFilter.remote = true;
    callLogGridFilter.autoReload = true;
    var _callLogGridStore = callLogGridStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _callLogGridStore,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      //iconCls: 'my-icon444',
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [callLogGridFilter],
      id: "callLogMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Event",
          sortable: true,
          dataIndex: "eventName",
          tooltip: "Event",
          hideable: true,
        },
        {
          header: "Callee",
          sortable: true,
          dataIndex: "calleeName",
          tooltip: "Callee",
          hideable: true,
        },{
          header: "Mobile",
          sortable: true,
          dataIndex: "calleeMobile",
          tooltip: "Mobile",
          hideable: true,
        },{
          header: "Called On",
          sortable: true,
          dataIndex: "calledOn",
          tooltip: "Called On",
          hideable: true,
        },
        {
          header: "Call Time",
          sortable: true,
          dataIndex: "actionOn",
          tooltip: "Chapter",
          hideable: true,
        },
        {
          header: "Caller",
          sortable: true,
          dataIndex: "caller",
          tooltip: "Caller",
        },
        {
          header: "Action",
          sortable: true,
          dataIndex: "actionRemark",
          tooltip: "Action",
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedCallLog,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        resize: onGridResize,
        afterrender: function () {
          _callLogGridStore.load();
        },
      },
      tbar: [{
        html: '&nbsp;Caller : &nbsp;',
    },
    {
        xtype: 'combo',
        id: 'search_clcaller_Name',
        name: 'search_clcaller_Name',
        mode: 'local',
        typeAhead: true,
        forceSelection: true,
        fieldLabel: 'Caller',
        editable: true,
        anchor: '97%',
        store: CallerStore,
        triggerAction: 'all',
        minChars: 2,
        displayField: 'UserName',
        valueField: 'UserId',
        hiddenName: 'search_clcaller_Name',
    },
    {html: '&nbsp; From Date : &nbsp;'},
    {
        xtype: 'datefield',
        width: 120,
        id: 'search_cl_from_date',
        name: 'search_cl_from_date',
        format: 'd/m/Y',
        value: new Date()
    }, {html: '&nbsp; To Date : &nbsp;'},
    {
        xtype: 'datefield',
        width: 120,
        id: 'search_cl_to_date',
        name: 'search_cl_to_date',
        format: 'd/m/Y',
        value: new Date()
    },
    '-',
    {
        xtype: 'button',
        text: 'Search',
        iconCls: 'finascop_search_btn',
        handler: function () {
            var search_cl_from_date = Ext.getCmp('search_cl_from_date').getValue();
            var search_cl_to_date = Ext.getCmp('search_cl_to_date').getValue();
            Ext.getCmp('callLogMasterGrid').filters.clearFilters();
            Ext.getCmp('callLogMasterGrid').getStore().load({
                params: {
                    caller_Name: Ext.getCmp('search_clcaller_Name').getValue(),
                    search_cl_from_date: search_cl_from_date,
                    search_cl_to_date: search_cl_to_date
                }
            });


        }
    }, '-',
    {
        xtype: 'button',
        text: 'Export to Excel',
        iconCls: 'icon_excel',
        handler: function () {
            if (_callLogGridStore.getTotalCount() <= 0) {
                Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                    if (btn == 'no') {
                        return;
                    }
                });
            }

            var indexes = [];
            var heads = [];
            
            var filterData = Ext.encode(jobLogLastParameters);
            for (var i = 0; i < Ext.getCmp('callLogMasterGrid').getColumnModel().getColumnCount(); i++) {
                if (Ext.getCmp('callLogMasterGrid').getColumnModel().isHidden(i) !== true) {

                    indexes[indexes.length] = Ext.getCmp('callLogMasterGrid').getColumnModel().config[i].dataIndex;
                    heads[heads.length] = Ext.getCmp('callLogMasterGrid').getColumnModel().config[i].header;
                }
                var dataindexes = Ext.encode(indexes);
                var headers = Ext.encode(heads);
            }

            postToUrl(modURL + '&op=callLogReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
        }
    }],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _callLogGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [callLogGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  var gridSelectionChangedCallLog = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("callLogMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("callLogMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.id;
      Application.OutboundCalls.ViewDetails(ID);
    }
  };
  var callLogMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "callLogMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Title </th><td> {jobTitle} </td></tr>',
        '<tr><th width="40%">Date </th><td> {createdOn} </td></tr>',
        '<tr><th width="40%">Job By </th><td> {createdBy} </td></tr>',
        '<tr><th width="40%">Callee </th><td> {calleeName} </td></tr>',
        '<tr><th width="40%">Mobile </th><td> {calleeMobile} </td></tr>',
        '<tr><th width="40%">Event </th><td> {eventName} </td></tr>',
        '<tr><th width="40%">Action </th><td> {actionRemark} </td></tr>',
        '<tr><th width="40%">Action On</th><td> {actionOn} </td></tr>',
        "</table>",
        "</div>"
      ),
    });
  };
  var jobUserTypeComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getUserType",
      method: "post",
      fields: ["id", "name"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var jobEventsComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getEvents",
      method: "post",
      fields: ["id", "eventName", "eventRank"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var CreateJobForm = function () {
    var jobUserType = jobUserTypeComboStore();
    var jobEvents = jobEventsComboStore();
    var _supportticketsFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterSupportTickets",
      frame: false,
      border: false,
      autoHeight: true,
      layout: "column",
      autoScroll: true,
      labelWidth: 120,
      fileUpload: true,
      labelAlign: "top",
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      items: [
        {
          columnWidth: 0.5,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Job Title",
              id: "jobTitle",
              name: "n[jobTitle]",
              anchor: "98%",
              tabIndex: 501,
              allowBlank: false,
              maxLength: 249,
            },
          ],
        },
        {
          columnWidth: 0.5,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "combo",
              id: "calleeType",
              fieldLabel: "User Type",
              name: "n[calleeType]",
              hiddenName: "n[calleeType]",
              store: jobUserType,
              forceSelection: true,
              triggerAction: "all",
              typeAhead: true,
              selectOnFocus: true,
              allowBlank: false,
              mode: "local",
              valueField: "id",
              displayField: "name",
              anchor: "95%",
              tabIndex: 502
            },
          ],
        },
        {
          columnWidth: 0.5,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Mobile",
              id: "calleeMobile",
              name: "n[calleeMobile]",
              anchor: "98%",
              tabIndex: 503,
              allowBlank: false,
              maxLength: 10,
            },
          ],
        },
        {
          columnWidth: 0.5,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Callee",
              id: "calleeName",
              name: "n[calleeName]",
              anchor: "98%",
              tabIndex: 504,
              allowBlank: false,
              maxLength: 300,
            },
          ],
        },
        {
          columnWidth: 0.5,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "combo",
              id: "eventId",
              fieldLabel: "Event",
              name: "n[eventId]",
              hiddenName: "n[eventId]",
              store: jobEvents,
              forceSelection: true,
              triggerAction: "all",
              typeAhead: true,
              selectOnFocus: true,
              mode: "local",
              valueField: "id",
              displayField: "eventName",
              anchor: "95%",
              tabIndex: 505,
              select: function (combo, record, index) {
                var value = record.data.eventRank;
                if (value > 0) {
                  Ext.getCmp('eventRank').setValue(value);
                }
              }
            },
          ],
        },{
          columnWidth: 0.5,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Rank",
              id: "eventRank",
              name: "n[eventRank]",
              anchor: "98%",
              tabIndex: 506,
              allowBlank: false,
            }
          ],
        },{
          columnWidth: 1,
          layout: "form",
          border: false,
          items: [{
          xtype: "textarea",
          fieldLabel: "Reason",
          id: "jobDescription",
          name: "n[jobDescription]",
          anchor: "98%",
          tabIndex: 507,
        }]
      }
      ],
      listeners: {
        load: function () {},
      },
    });
    return _supportticketsFormPanel;
  };
  var saveOutboundJobs = function () {
    var _st_save = Ext.getCmp("formpanelMasterSupportTickets");
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    if (_st_save.getForm().isValid()) {
      _st_save.getForm().submit({
        waitTitle: "Please Wait",
        waitMsg: "Saving",
        url: modURL + "&op=saveOutboundJobs",        
        success: function (form, action) {
          var tmp = Ext.decode(action.response.responseText);
          if (tmp.success == true) {
            Application.example.msg("Notification", tmp.msg);
            Ext.getCmp("createJobWindow").close();
            Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
              .getStore()
              .load();
          } else {
            Ext.MessageBox.alert("Error", tmp.msg);
          }
        },
        failure: function (response) {
          var tmp = Ext.decode(response.responseText);
          Ext.MessageBox.alert("Error", tmp);
        },
      });
    } else {
      Ext.MessageBox.alert(
        "Notification",
        "Please provide information for the required field."
      );
    }
  };
  var masterPanelforOutboundCallRecordings = function (id) {
    var _mpanelforOutboundEvents = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "fit",
      border: false,
      title: "Call Recordings",
      id: id,
      iconCls: "my-icon448",
      items: [
        OutboundCallRecordingsMainGrid()        
      ],
    });
    return _mpanelforOutboundEvents;
  };
  var gridSelectionChangedCallRecordings = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("CallRecordingsMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("CallRecordingsMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.id;
      
    }
  };
  var OutboundCallRecordingsMainGrid = function(){
    var CallerStore = new Ext.data.JsonStore({
      fields: ['UserId', 'UserName'],
      url: modURL + '&op=getUserName',
      autoLoad: true,
      method: 'post',
      listeners: {
          load: function (thisstore, records, options) {
          }
      }
  });
    var callRecordingGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "CallerID",
        },{
          type: "string",
          dataIndex: "AgentName",
        },{
          type: "string",
          dataIndex: "Status",
        },{
          type: "string",
          dataIndex: "AgentStatus",
        },{
          type: "string",
          dataIndex: "CustomerStatus",
        },{
          type: "date",
          dataIndex: "calledOn",
        }
      ],
    });
    callRecordingGridFilter.remote = true;
    callRecordingGridFilter.autoReload = true;
    var _callRecordingGridStore = callRecordingGridStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _callRecordingGridStore,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      //iconCls: 'my-icon444',
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [callRecordingGridFilter],
      id: "CallRecordingsMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: 'Callee',
          dataIndex: 'CallerID'
      },{
        header: 'Called On',
        dataIndex: 'calledOn',
    },
        {
          header: 'Call Initiated ',
          dataIndex: 'StartTime',
      },{
        header: 'Call Ended',
        dataIndex: 'EndTime',
    },{
      header: 'Time to Answer',
      dataIndex: 'TimeToAnswer',      
  },{
    header: 'Call Duration',
    dataIndex: 'CallDuration',      
},
      {
          header: 'Talk Duration',
          dataIndex: 'Duration',
          hideable: true,
          hidden: true
      },{
          header: 'Caller',
          dataIndex: 'AgentName'
      },{
        header: 'Disposition ',
        dataIndex: 'Disposition ',
        hideable: true,
        hidden: true
    },{
      header: 'Hangup By',
      dataIndex: 'HangupBy'
  },{
    header: 'Call Status',
    dataIndex: 'Status'
  },{
  header: 'Comments',
  dataIndex: 'Comments',
  hidden:true,
  renderer: qtipRenderer
  },{
  header: 'Dial Status',
  dataIndex: 'DialStatus',
  hideable: true,
        hidden: true
  },{
  header: 'Agent Status',
  dataIndex: 'AgentStatus',
  hideable: true,
        hidden: true
},{
  header: 'Callee Status',
  dataIndex: 'CustomerStatus',
  hideable: true,
        hidden: true
},{
  header: 'Conference Duration',
  dataIndex: 'ConfDuration',
  hideable: true,
        hidden: true
},{
  header: 'Wrap up Duration',
  dataIndex: 'WrapUpDuration',
  hideable: true,
        hidden: true
},{
  header: 'Hold Duration',
  dataIndex: 'HoldDuration',
  hideable: true,
        hidden: true
},{
          xtype: 'templatecolumn',
          dataIndex: 'AudioURL',
          width:100,
          tpl: '<audio style ="width:100px;height:20px;" controls src={AudioURL}></audio>'
      }
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedCallRecordings,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        resize: onGridResize,
        afterrender: function () {
          _callRecordingGridStore.load();
        },
      },
      tbar: [{
        html: '&nbsp;Caller : &nbsp;',
    },
    {
        xtype: 'combo',
        id: 'search_caller_Name',
        name: 'search_caller_Name',
        mode: 'local',
        typeAhead: true,
        forceSelection: true,
        fieldLabel: 'Caller',
        editable: true,
        anchor: '97%',
        store: CallerStore,
        triggerAction: 'all',
        minChars: 2,
        displayField: 'UserName',
        valueField: 'UserId',
        hiddenName: 'search_caller_Name',
    },
    {html: '&nbsp; From Date : &nbsp;'},
    {
        xtype: 'datefield',
        width: 120,
        id: 'search_cr_from_date',
        name: 'search_cr_from_date',
        format: 'd/m/Y',
        value: new Date()
    }, {html: '&nbsp; To Date : &nbsp;'},
    {
        xtype: 'datefield',
        width: 120,
        id: 'search_cr_to_date',
        name: 'search_cr_to_date',
        format: 'd/m/Y',
        value: new Date()
    },
    '-',
    {
        xtype: 'button',
        text: 'Search',
        iconCls: 'finascop_search_btn',
        handler: function () {
            var search_cr_from_date = Ext.getCmp('search_cr_from_date').getValue();
            var search_cr_to_date = Ext.getCmp('search_cr_to_date').getValue();
            Ext.getCmp('CallRecordingsMasterGrid').filters.clearFilters();
            Ext.getCmp('CallRecordingsMasterGrid').getStore().load({
                params: {
                    caller_Name: Ext.getCmp('search_caller_Name').getRawValue(),
                    search_cr_from_date: search_cr_from_date,
                    search_cr_to_date: search_cr_to_date
                }
            });

            //var storefilter = Ext.getCmp('CallRecordingsMasterGrid');
            // activateFilter(storefilter, 'caller_Name', Ext.getCmp('search_caller_Name').getRawValue());

        }
    }, '-',
    {
        xtype: 'button',
        text: 'Export to Excel',
        iconCls: 'icon_excel',
        handler: function () {
            if (_callRecordingGridStore.getTotalCount() <= 0) {
                Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                    if (btn == 'no') {
                        return;
                    }
                });
            }

            var indexes = [];
            var heads = [];
            
            var filterData = Ext.encode(callRecLastParameters);
            for (var i = 0; i < Ext.getCmp('CallRecordingsMasterGrid').getColumnModel().getColumnCount(); i++) {
                if (Ext.getCmp('CallRecordingsMasterGrid').getColumnModel().isHidden(i) !== true && Ext.getCmp('CallRecordingsMasterGrid').getColumnModel().config[i].header != 'AudioFile') {

                    indexes[indexes.length] = Ext.getCmp('CallRecordingsMasterGrid').getColumnModel().config[i].dataIndex;
                    heads[heads.length] = Ext.getCmp('CallRecordingsMasterGrid').getColumnModel().config[i].header;
                }
                var dataindexes = Ext.encode(indexes);
                var headers = Ext.encode(heads);
            }

            postToUrl(modURL + '&op=callRecordReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
        }
    }],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _callRecordingGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [callRecordingGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  var callRecordingGridStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listRecordings",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "id",
          root: "data",
        },
        ['id','AudioFile','HoldDuration','WrapUpDuration','ConfDuration','CustomerStatus','AgentStatus',
        'DialStatus','Comments','Status','CallerID','StartTime','EndTime','TimeToAnswer','CallDuration',
        'Duration','AgentName','Disposition','HangupBy','calledOn','AudioURL']
      ),
      sortInfo: {
        field: "id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function (a, b, options) {
          Ext.getCmp("CallRecordingsMasterGrid").getView().refresh();
          Ext.getCmp("CallRecordingsMasterGrid").getSelectionModel().selectRow(0);    
          callRecLastParameters = options.params;      
        },
      },
    });
    return _store;
  };
  var FollowupCallsMasterStore = function () {
    var _outboundcallsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listFollowupCalls",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "eventId",
          "eventName",
          "calleeId",
          "calleeName",
          "calleeMobile",
          "calleeType",
          "calleeTypeName",
          "eventRank",
          "status",
          "statusName",
          "createdDate",
          "createdTime",
          "assignedTo",
          "assignedOn",
          "callerName",
          "followupDate",
        ]
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
          Ext.getCmp("gridpanelMasterDataviewFollowupCallsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewFollowupCallsdata")
            .getSelectionModel()
            .selectRow(0);

            var ojcount = this.getCount();
            if(ojcount > 0){
              if(ojcount == 1){
                Ext.getCmp('fpExitbtn').show();
                Ext.getCmp('fpStartbtn').hide();
              }else{
                Ext.getCmp('fpStartbtn').show();
                Ext.getCmp('fpExitbtn').hide();
              }              
            }else{
              Ext.getCmp('fpStartbtn').hide();
              Ext.getCmp('fpExitbtn').hide();
            }
        },
      },
    });
    return _outboundcallsMasterStore;
  };
  var masterPanelforFollowupCalls = function (id) {
    var _mpanelforFollowupCalls = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Followup Calls",
      id: id,
      iconCls: "my-icon448",
      items: [FollowupCallsMainGrid()],
    });
    return _mpanelforFollowupCalls;
  };
  var FollowupCallsMainGrid = function () {
    var _followupcallsStore = FollowupCallsMasterStore();
    var _followupcallsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "calleeName",
        },
        {
          type: "string",
          dataIndex: "calleeMobile",
        },
        {
          type: "string",
          dataIndex: "calleeTypeName",
        },
        {
          type: "string",
          dataIndex: "statusName",
        },
        {
          type: "string",
          dataIndex: "callerName",
        },
      ],
    });
    _followupcallsGridFilter.remote = true;
    _followupcallsGridFilter.autoReload = true;
    var _followupcallsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _followupcallsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewFollowupCallsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _followupcallsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Date of Registration",
          hideable: false,
        },
        {
          header: "Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Time",
          hideable: false,
        },
        {
          header: "Event",
          dataIndex: "eventName",
          sortable: true,
          tooltip: "Event",
          hideable: false,
        },
        {
          header: "Callee",
          dataIndex: "calleeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Mobile",
          dataIndex: "calleeMobile",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Type of Call",
          dataIndex: "calleeTypeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Instance Type",
          dataIndex: "instanceType",
          hidden: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Rank",
          dataIndex: "eventRank",
          sortable: true,
          tooltip: "Rank",
          hideable: false,
        },
        {
          header: "Status",
          dataIndex: "statusName",
          sortable: true,
          tooltip: "Status",
          hideable: false,
        },
        {
          header: "Caller",
          dataIndex: "callerName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Followup Date",
          dataIndex: "followupDate",
          sortable: true,
          tooltip: "Followup Date",
          hideable: false,
        },
        {
          header: "Actions",
          hideable: true,
          xtype: "actioncolumn",
          sortable: false,
          groupable: false,
          items: [
            {
              iconCls: "move-to-contactss",
              tooltip: "Start Job",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);
                console.log(record);
                var jobId = record.get("id");
                var calleeMobile = record.get("calleeMobile");
                var calleeType = record.get("calleeType");
                Application.OutboundCalls.AcceptJob(
                  jobId,
                  calleeMobile,
                  calleeType
                );
              },
            }
          ],
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _followupcallsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedFollowupCalls,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("store_group_id");
          if (!Ext.isEmpty(ID)) {
            Application.OutboundCalls.Cache.store_group_id = ID;
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _followupcallsStore.load();          
          
        },load:function () {
          
        }
      },
      tbar: [
        {
          iconCls: "move-to-contactss",
          xtype: "button",
          text: "Start Job",
          id:"fpStartbtn",
          hidden:true,
          tooltip: "Start Job",
          icon: "./resources/images/default/icons/add.png",
          handler: function () {
            Application.OutboundCalls.ChooseJob();
          },
        },
        {
          xtype: "button",
          text: "Exit Job",
          tooltip: "Exit Job",
          id:"fpExitbtn",
          hidden:true,
          icon: "./resources/images/default/icons/delete.png",
          handler: function () {
            Ext.Ajax.request({
              waitMsg: "Processing",
              url: modURL,
              params: {
                op: "exitJobFromUser",
              },
              failure: function (response, options) {
                Ext.MessageBox.alert("Notification", ACTION_FAIL);
              },
              success: function (response, options) {
                eval("var tmp=" + response.responseText);
                if (tmp.success === true) {
                  Application.example.msg("Success", tmp.msg);
                  _followupcallsStore.load();
                }
              },
            });
          },
        },
      ],
    });
    return _followupcallsmaingridPanel;
  };
  var masterPanelforOutboundCallCommunication = function (id) {
    var _mpanelforOutboundEvents = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Call Logs",
      id: id,
      iconCls: "my-icon448",
      items: [
        OutboundcallCommunicationMainGrid(),
        new Ext.Panel({
          title: "Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "callCommunicationDetailParentPanel",
          height: winsize.height * 0.6,
          items: [callCommunicationMasterDetailsView()],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return _mpanelforOutboundEvents;
  };
  var callCommunicationGridStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listcallCommunications",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "articleId",
          root: "data",
        },
        [
          "id",
          "actionBy",
          "actionOn",
          "actionRemark",
          "jobTitle",
          "calleeName",
          "calleeMobile",
          "caller",
          "eventName","createdOn","isManual","createdBy","calledOn"
        ]
      ),
      sortInfo: {
        field: "id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("callCommunicationMasterGrid").getView().refresh();
          Ext.getCmp("callCommunicationMasterGrid").getSelectionModel().selectRow(0);          
        },
      },
    });
    return _store;
  };
  var OutboundcallCommunicationMainGrid = function () {
    var callCommunicationGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "calleeName",
        },
        {
          type: "string",
          dataIndex: "caller",
        },
        {
          type: "string",
          dataIndex: "calleeMobile",
        },{
          type: "date",
          dataIndex: "calledOn",
        },
      ],
    });
    callCommunicationGridFilter.remote = true;
    callCommunicationGridFilter.autoReload = true;
    var _callCommunicationGridStore = callCommunicationGridStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _callCommunicationGridStore,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      //iconCls: 'my-icon444',
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [callCommunicationGridFilter],
      id: "callCommunicationMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Event",
          sortable: true,
          dataIndex: "eventName",
          tooltip: "Event",
          hideable: true,
        },
        {
          header: "Callee",
          sortable: true,
          dataIndex: "calleeName",
          tooltip: "Callee",
          hideable: true,
        },{
          header: "Mobile",
          sortable: true,
          dataIndex: "calleeMobile",
          tooltip: "Mobile",
          hideable: true,
        },{
          header: "Called On",
          sortable: true,
          dataIndex: "calledOn",
          tooltip: "Called On",
          hideable: true,
        },
        {
          header: "Call Time",
          sortable: true,
          dataIndex: "createdOn",
          tooltip: "Call Time",
          hideable: true,
        },
        {
          header: "Caller",
          sortable: true,
          dataIndex: "caller",
          tooltip: "Caller",
        },
        {
          header: "Communication",
          sortable: true,
          dataIndex: "actionRemark",
          tooltip: "Communication",
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedcallCommunication,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        resize: onGridResize,
        afterrender: function () {
          _callCommunicationGridStore.load();
        },
      },
      tbar: [],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _callCommunicationGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [callCommunicationGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  var gridSelectionChangedcallCommunication = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("callCommunicationMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("callCommunicationMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.id;
      Application.OutboundCalls.ViewCallCommunicationDetails(ID);
    }
  };
  var callCommunicationMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "callCommunicationMasterDetailsViewPanel",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Title </th><td> {jobTitle} </td></tr>',
        '<tr><th width="40%">Date </th><td> {createdOn} </td></tr>',
        '<tr><th width="40%">Job By </th><td> {createdBy} </td></tr>',
        '<tr><th width="40%">Callee </th><td> {calleeName} </td></tr>',
        '<tr><th width="40%">Mobile </th><td> {calleeMobile} </td></tr>',
        '<tr><th width="40%">Event </th><td> {eventName} </td></tr>',
        '<tr><th width="40%">Communication </th><td> {actionRemark} </td></tr>',
        '<tr><th width="40%">Action On</th><td> {actionOn} </td></tr>',
        "</table>",
        "</div>"
      ),
    });
  };
  var masterPanelforOutboundJobDetails = function (id) {
    var _mpanelforOutboundEvents = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Call Logs",
      id: id,
      iconCls: "my-icon448",
      items: [
        OutboundcallMainGrid(),
        new Ext.Panel({
          title: "Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          id: "jobDetailParentPanel",
          height: winsize.height * 0.6,
          items: [OutboundJoblogGrid()],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return _mpanelforOutboundEvents;
  };
  var jobDetailGridStore = function () {
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAlljobs",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "eventId",
          "eventName",
          "calleeId",
          "calleeName",
          "calleeMobile",
          "calleeType",
          "calleeTypeName",
          "eventRank",
          "status",
          "statusName",
          "createdDate",
          "createdTime",
          "assignedTo",
          "assignedOn",
          "callerName",
          "followupDate","followupOn"
        ]
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
          Ext.getCmp("jobDetailMasterGrid").getView().refresh();
          Ext.getCmp("jobDetailMasterGrid").getSelectionModel().selectRow(0);          
        },
      },
    });
    return _store;
  };
  var OutboundcallMainGrid = function () {
    var jobDetailGridFilter = new Ext.ux.grid.GridFilters({
      filters: [       
        {
          type: "date",
          dataIndex: "createdDate",
        },{
          type: "date",
          dataIndex: "createdDate",
        },
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "calleeName",
        },
        {
          type: "string",
          dataIndex: "calleeMobile",
        },
        {
          type: "string",
          dataIndex: "calleeTypeName",
        },
        {
          type: "string",
          dataIndex: "statusName",
        },
        {
          type: "string",
          dataIndex: "callerName",
        },
      ],
    });
    jobDetailGridFilter.remote = true;
    jobDetailGridFilter.autoReload = true;
    var _jobDetailGridStore = jobDetailGridStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _jobDetailGridStore,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      //iconCls: 'my-icon444',
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [jobDetailGridFilter],
      id: "jobDetailMasterGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Date of Registration",
          hideable: false,
        },
        {
          header: "Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Time",
          hideable: false,
        },
        {
          header: "Event",
          dataIndex: "eventName",
          sortable: true,
          tooltip: "Event",
          hideable: false,
        },
        {
          header: "Callee",
          dataIndex: "calleeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Mobile",
          dataIndex: "calleeMobile",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Type of Call",
          dataIndex: "calleeTypeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Instance Type",
          dataIndex: "instanceType",
          hidden: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Rank",
          dataIndex: "eventRank",
          sortable: true,
          tooltip: "Rank",
          hideable: false,
        },
        {
          header: "Status",
          dataIndex: "statusName",
          sortable: true,
          tooltip: "Status",
          hideable: false,
        },
        {
          header: "Caller",
          dataIndex: "callerName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },{
          header: "Followup On",
          dataIndex: "followupOn",
          sortable: true,
          tooltip: "Followup On",
          hideable: false,
        },
        {
          header: "Followup Date",
          dataIndex: "followupDate",
          sortable: true,
          tooltip: "Followup Date",
          hideable: false,
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedJobDetail,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        resize: onGridResize,
        afterrender: function () {
          _jobDetailGridStore.load();
        },
      },
      tbar: [],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _jobDetailGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [jobDetailGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  var gridSelectionChangedJobDetail = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("jobDetailMasterGrid").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("jobDetailMasterGrid")
        .getSelectionModel()
        .getSelections()[0].data.id;
        Ext.getCmp('jobLogSubGrid').getStore().load({
          params:{
          JobId: ID
          }
        });
    }
  };
  var OutboundJoblogGrid = function(){
    var callCommunicationGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "calleeName",
        },
        {
          type: "string",
          dataIndex: "caller",
        },
        {
          type: "string",
          dataIndex: "calleeMobile",
        },{
          type: "date",
          dataIndex: "calledOn",
        },
      ],
    });
    callCommunicationGridFilter.remote = true;
    callCommunicationGridFilter.autoReload = true;
    var _callCommunicationGridStore = jobLogGridStore();
    var _gridPanel = new Ext.grid.GridPanel({
      store: _callCommunicationGridStore,
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      //iconCls: 'my-icon444',
      viewConfig: {
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      plugins: [callCommunicationGridFilter],
      id: "jobLogSubGrid",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Event",
          sortable: true,
          dataIndex: "eventName",
          tooltip: "Event",
          hideable: true,
        },
        {
          header: "Callee",
          sortable: true,
          dataIndex: "calleeName",
          tooltip: "Callee",
          hideable: true,
        },{
          header: "Mobile",
          sortable: true,
          dataIndex: "calleeMobile",
          tooltip: "Mobile",
          hideable: true,
        },{
          header: "Called On",
          sortable: true,
          dataIndex: "calledOn",
          tooltip: "Called On",
          hideable: true,
        },
        {
          header: "Call Time",
          sortable: true,
          dataIndex: "createdOn",
          tooltip: "Call Time",
          hideable: true,
        },
        {
          header: "Caller",
          sortable: true,
          dataIndex: "caller",
          tooltip: "Caller",
        },
        {
          header: "Communication",
          sortable: true,
          dataIndex: "actionRemark",
          tooltip: "Communication",
        },
      ],
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {},
        resize: onGridResize,
        afterrender: function () {
          _callCommunicationGridStore.load();
        },
      },
      tbar: [],
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _callCommunicationGridStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
        plugins: [callCommunicationGridFilter],
      }),
      stripeRows: true,
      autoExpandColumn: "pdt_name_col",
    });
    return _gridPanel;
  };
  var jobLogGridStore = function(){
    
    var _store = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listcallCommunications",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "articleId",
          root: "data",
        },
        [
          "id",
          "actionBy",
          "actionOn",
          "actionRemark",
          "jobTitle",
          "calleeName",
          "calleeMobile",
          "caller",
          "eventName","createdOn","isManual","createdBy","calledOn"
        ]
      ),
      sortInfo: {
        field: "id",
        direction: "DESC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
        },
      },
    });
    return _store;
  };

  var masterPanelforClosedCalls = function (id) {
    var _mpanelforClosedCalls = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Closed Jobs",
      id: id,
      iconCls: "my-icon448",
      items: [ClosedCallsMainGrid(),closedJoblogGrid()],
    });
    return _mpanelforClosedCalls;
  };
  var ClosedCallsMainGrid = function () {
    var _closedcallsStore = ClosedCallsMasterStore();
    var _closedcallsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "calleeName",
        },
        {
          type: "string",
          dataIndex: "calleeMobile",
        },
        {
          type: "string",
          dataIndex: "calleeTypeName",
        },
        {
          type: "string",
          dataIndex: "statusName",
        },
        {
          type: "string",
          dataIndex: "callerName",
        },
      ],
    });
    _closedcallsGridFilter.remote = true;
    _closedcallsGridFilter.autoReload = true;
    var _closedcallsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _closedcallsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewClosedCallsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _closedcallsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Date of Registration",
          hideable: false,
        },
        {
          header: "Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Time",
          hideable: false,
        },
        {
          header: "Event",
          dataIndex: "eventName",
          sortable: true,
          tooltip: "Event",
          hideable: false,
        },
        {
          header: "Callee",
          dataIndex: "calleeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Mobile",
          dataIndex: "calleeMobile",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Type of Call",
          dataIndex: "calleeTypeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Instance Type",
          dataIndex: "instanceType",
          hidden: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Rank",
          dataIndex: "eventRank",
          sortable: true,
          tooltip: "Rank",
          hideable: false,
        },
        {
          header: "Status",
          dataIndex: "statusName",
          sortable: true,
          tooltip: "Status",
          hideable: false,
        },
        {
          header: "Caller",
          dataIndex: "callerName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Closed Date",
          dataIndex: "completedOn",
          sortable: true,
          tooltip: "Closed Date",
          hideable: false,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _closedcallsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedClosedCalls,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("store_group_id");
          if (!Ext.isEmpty(ID)) {
            Application.OutboundCalls.Cache.store_group_id = ID;
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _closedcallsStore.load();          
          
        },load:function () {
          
        }
      },
      tbar: [],
    });
    return _closedcallsmaingridPanel;
  };
  var ClosedCallsMasterStore = function () {
    var _outboundcallsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listClosedCalls",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "eventId",
          "eventName",
          "calleeId",
          "calleeName",
          "calleeMobile",
          "calleeType",
          "calleeTypeName",
          "eventRank",
          "status",
          "statusName",
          "createdDate",
          "createdTime",
          "assignedTo",
          "assignedOn",
          "callerName",
          "completedOn",
        ]
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
          Ext.getCmp("gridpanelMasterDataviewClosedCallsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewClosedCallsdata")
            .getSelectionModel()
            .selectRow(0);

        },
      },
    });
    return _outboundcallsMasterStore;
  };
  var gridSelectionChangedClosedCalls = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewClosedCallsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewClosedCallsdata")
        .getSelectionModel()
        .getSelections()[0].data.store_group_id;
        var jobId = Ext.getCmp("gridpanelMasterDataviewClosedCallsdata")
        .getSelectionModel()
        .getSelections()[0].data.id;
        
            Ext.getCmp("gridClosedJobLogGrid")
              .getStore()
              .load({
                params: {
                  jobId: jobId,
                },
              });
    }
  };
  var closedJoblogGrid = function () {
    var _closedJobLogStore = OutboundJobLogStore();
    var __closedJobLogGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
      {
        type: "string",
        dataIndex: "createdOn",
      },
    ],
    });
    __closedJobLogGridFilter.remote = true;
    __closedJobLogGridFilter.autoReload = true;
    var _closedJobLogGridPanel = new Ext.grid.GridPanel({
      region: "east",
	  width: winsize.width * 0.3,
		height: winsize.height * 0.6,
      layout: "fit",
      frame: true,
      border: false,
      loadMask: true,
      store: _closedJobLogStore,
      iconCls: "money",
      title:"Job Log",
      id: "gridClosedJobLogGrid",      
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), __closedJobLogGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Date",
          dataIndex: "createdOn",
        },
        {
          header: "User",
          dataIndex: "createdByName",
        },{
          header: "From",
          dataIndex: "entryFromName",
        },{
          header: "Action",
          dataIndex: "callActionName",
        },
        {
          header: "Remarks",
          dataIndex: "callRemarks",
          renderer: qtipRenderer,
        }
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _closedJobLogStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,       
      }),
      listeners: {
      afterrender: function () {},
    },
      tbar: [
      ],
    });
    return _closedJobLogGridPanel;
  };
  var masterPanelforMissedCalls = function (id) {
    var _mpanelforMissedCalls = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Missed Jobs",
      id: id,
      iconCls: "my-icon448",
      items: [MissedCallsMainGrid(),missedJoblogGrid()],
    });
    return _mpanelforMissedCalls;
  };
  var MissedCallsMainGrid = function () {
    var _missedcallsStore = MissedCallsMasterStore();
    var _missedcallsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "eventName",
        },
        {
          type: "string",
          dataIndex: "calleeName",
        },
        {
          type: "string",
          dataIndex: "calleeMobile",
        },
        {
          type: "string",
          dataIndex: "calleeTypeName",
        },
        {
          type: "string",
          dataIndex: "statusName",
        },
        {
          type: "string",
          dataIndex: "callerName",
        },
      ],
    });
    _missedcallsGridFilter.remote = true;
    _missedcallsGridFilter.autoReload = true;
    var _missedcallsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _missedcallsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewMissedCallsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _missedcallsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Date of Registration",
          hideable: false,
        },
        {
          header: "Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Time",
          hideable: false,
        },
        {
          header: "Event",
          dataIndex: "eventName",
          sortable: true,
          tooltip: "Event",
          hideable: false,
        },
        {
          header: "Callee",
          dataIndex: "calleeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Mobile",
          dataIndex: "calleeMobile",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Type of Call",
          dataIndex: "calleeTypeName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Instance Type",
          dataIndex: "instanceType",
          hidden: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Rank",
          dataIndex: "eventRank",
          sortable: true,
          tooltip: "Rank",
          hideable: false,
        },
        {
          header: "Status",
          dataIndex: "statusName",
          sortable: true,
          tooltip: "Status",
          hideable: false,
        },
        {
          header: "Caller",
          dataIndex: "callerName",
          sortable: true,
          tooltip: "Area",
          hideable: false,
        },
        {
          header: "Missed Date",
          dataIndex: "completedOn",
          sortable: true,
          tooltip: "Missed Date",
          hideable: false,
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _missedcallsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedMissedCalls,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("store_group_id");
          if (!Ext.isEmpty(ID)) {
            Application.OutboundCalls.Cache.store_group_id = ID;
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _missedcallsStore.load();          
          
        },load:function () {
          
        }
      },
      tbar: [],
    });
    return _missedcallsmaingridPanel;
  };
  var MissedCallsMasterStore = function () {
    var _outboundcallsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listMissedCalls",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "id",
          "eventId",
          "eventName",
          "calleeId",
          "calleeName",
          "calleeMobile",
          "calleeType",
          "calleeTypeName",
          "eventRank",
          "status",
          "statusName",
          "createdDate",
          "createdTime",
          "assignedTo",
          "assignedOn",
          "callerName",
          "completedOn",
        ]
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
          Ext.getCmp("gridpanelMasterDataviewMissedCallsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewMissedCallsdata")
            .getSelectionModel()
            .selectRow(0);

        },
      },
    });
    return _outboundcallsMasterStore;
  };
  var gridSelectionChangedMissedCalls = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewMissedCallsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewMissedCallsdata")
        .getSelectionModel()
        .getSelections()[0].data.store_group_id;
        var jobId = Ext.getCmp("gridpanelMasterDataviewMissedCallsdata")
        .getSelectionModel()
        .getSelections()[0].data.id;
        
            Ext.getCmp("gridMissedJobLogGrid")
              .getStore()
              .load({
                params: {
                  jobId: jobId,
                },
              });
    }
  };
  var missedJoblogGrid = function () {
    var _missedJobLogStore = OutboundJobLogStore();
    var __missedJobLogGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
      {
        type: "string",
        dataIndex: "createdOn",
      },
    ],
    });
    __missedJobLogGridFilter.remote = true;
    __missedJobLogGridFilter.autoReload = true;
    var _missedJobLogGridPanel = new Ext.grid.GridPanel({
      region: "east",
	  width: winsize.width * 0.3,
		height: winsize.height * 0.6,
      layout: "fit",
      frame: true,
      border: false,
      loadMask: true,
      store: _missedJobLogStore,
      iconCls: "money",
      title:"Job Log",
      id: "gridMissedJobLogGrid",      
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), __missedJobLogGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Date",
          dataIndex: "createdOn",
        },
        {
          header: "User",
          dataIndex: "createdByName",
        },{
          header: "From",
          dataIndex: "entryFromName",
        },{
          header: "Action",
          dataIndex: "callActionName",
        },
        {
          header: "Remarks",
          dataIndex: "callRemarks",
          renderer: qtipRenderer,
        }
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _missedJobLogStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,       
      }),
      listeners: {
      afterrender: function () {},
    },
      tbar: [
      ],
    });
    return _missedJobLogGridPanel;
  };
  return {
    Cache: {},
    initOutboundCalls: function () {
      var _outboundCallsPanelId = "panelMasterMainOutboundCalls";
      var _masterPanelOutboundCalls = Ext.getCmp(_outboundCallsPanelId);
      if (Ext.isEmpty(_masterPanelOutboundCalls)) {
        _masterPanelOutboundCalls = masterPanelforOutboundCalls(
          _outboundCallsPanelId
        );
        Application.UI.addTab(_masterPanelOutboundCalls);
        _masterPanelOutboundCalls.doLayout();
      } else {
        Application.UI.addTab(_masterPanelOutboundCalls);
      }
    },
    AcceptJob: function () {
      var jobId = arguments[0];
      var phone = arguments[1];
      var calleeType = arguments[2];
      Application.OutboundCalls.Cache.jobId = jobId;
      Application.OutboundCalls.Cache.calleeType = calleeType;
      Ext.Ajax.request({
        url: modURL + "&op=acceptJob",
        method: "POST",
        params: { jobId: jobId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success == true) {
            Application.OutboundCalls.Cache.jobId = tmp.jobId;
            Application.OutboundCalls.Cache.jobTitle = tmp.jobTitle;
            Application.OutboundCalls.Cache.calleeType = tmp.calleeType;
            Application.example.msg("Notification", tmp.msg);
            Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
              .getStore()
              .load({
                callback: function () {
                  console.log("calleeType");
                  console.log(calleeType);
                  switch (calleeType) {
                    case "1":
                      Application.RetalineOmni.outboundCallsCustomer(phone);
                      break;
                    case "2":
                      Application.RetalineOmni.outboundCallsMerchant(phone);
                      break;
                  }
                },
              });
          } else {
            Ext.MessageBox.alert("Notification", tmp.msg);
          }
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
    },
    initOutboundEvents: function () {
      var _outboundEventsPanelId = "panelMasterMainOutboundEvents";
      var _masterPanelOutboundEvents = Ext.getCmp(_outboundEventsPanelId);
      if (Ext.isEmpty(_masterPanelOutboundEvents)) {
        _masterPanelOutboundEvents = masterPanelforOutboundEvents(
          _outboundEventsPanelId
        );
        Application.UI.addTab(_masterPanelOutboundEvents);
        _masterPanelOutboundEvents.doLayout();
      } else {
        Application.UI.addTab(_masterPanelOutboundEvents);
      }
    },
    assignEventsToUser: function (userId) {
      var availableCallEventWindowid = Ext.getCmp("availableCallEventsWindow");
      if (Ext.isEmpty(availableCallEventWindowid)) {
        availableCallEventWindowid = new Ext.Window({
          id: "availableCallEventsWindow",
          title: "Assign Call Events to User",
          modal: true,
          height: 500,
          width: winsize.width * 0.4,
          shadow: false,
          resizable: false,
          layout: "border",
          items: [
            availableCallEventGrid(userId),
            new Ext.Panel({
              title: "Assigned Call Events",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.2,
              cls: "left_side_panel",
              height: 500,
              autoScroll: true,
              items: [mappedUserCallEvents(userId)],
              buttons: [],
              fbar: [],
            }),
          ],
          buttons: [
            {
              text: "Close",
              iconCls: "my-icon61",
              handler: function () {
                availableCallEventWindowid.close();
              },
            },
          ],
          listeners: {
            close: function () {},
          },
        });
      }
      availableCallEventWindowid.doLayout();
      availableCallEventWindowid.show();
      availableCallEventWindowid.center();
    },
    mapCallEventToUser: function (brandarr, userId) {
      Ext.Ajax.request({
        url: modURL + "&op=mapCallEventsToUser",
        method: "post",
        params: {
          brandarr: Ext.encode(brandarr),
          userId: userId,
        },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {
            Ext.getCmp(
              "gridpanelforAvailableCallEvents"
            ).getStore().baseParams.userId = userId;
            Ext.getCmp("gridpanelforAvailableCallEvents").getStore().load();

            Ext.getCmp(
              "gridpanelforUserMappedCallEvents"
            ).getStore().baseParams.userId = userId;
            Ext.getCmp("gridpanelforUserMappedCallEvents").getStore().load();
          }
        },
      });
    },
    ChooseJob: function () {
      Ext.Ajax.request({
        url: modURL + "&op=chooseJob",
        method: "POST",
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success == true) {
            Application.OutboundCalls.Cache.jobId = tmp.jobId;
            Application.OutboundCalls.Cache.jobTitle = tmp.jobTitle;
            Application.OutboundCalls.Cache.calleeType = tmp.calleeType;

            Application.example.msg("Notification", tmp.msg);
            Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata")
              .getStore()
              .load({
                callback: function () {
                  switch (tmp.calleeType) {
                    case "1":
                      Application.RetalineOmni.outboundCallsCustomer(tmp.phone);
                      break;
                    case "2":
                      Application.RetalineOmni.outboundCallsMerchant(tmp.phone);
                      break;
                    case "4":
                      Application.RelationshipOfficer.outboundCallsRO(tmp.phone);
                      break;
                  }
                },
              });
          } else {
            Ext.MessageBox.alert("Notification", tmp.msg);
          }
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
    },
    initOutboundCallLog: function () {
      var _outboundCallLogPanelId = "panelMasterMainOutboundCallLog";
      var _masterPanelOutboundCallLog = Ext.getCmp(_outboundCallLogPanelId);
      if (Ext.isEmpty(_masterPanelOutboundCallLog)) {
        _masterPanelOutboundCallLog = masterPanelforOutboundCallLog(
          _outboundCallLogPanelId
        );
        Application.UI.addTab(_masterPanelOutboundCallLog);
        _masterPanelOutboundCallLog.doLayout();
      } else {
        Application.UI.addTab(_masterPanelOutboundCallLog);
      }
    },
    ViewDetails: function () {
      var jobId = arguments[0];
      Ext.getCmp("callLogDetailParentPanel").setTitle("View Details");
      Ext.getCmp("callLogDetailParentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=callLogDetailsView",
        method: "POST",
        params: { jobId: jobId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("callLogMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          } else {
            var visualsDescPanel = Ext.getCmp("callLogMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("callLogDetailParentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("callLogDetailParentPanel").doLayout();
    },
    createJobDetails: function () {
      var reswindow = new Ext.Window({
        title: "Create Job",
        width: 650,
        autoHeight: true,
        plain: true,
        constrainHeader: true,
        modal: true,
        frame: true,
        border: false,
        iconCls: "",
        resizable: false,
        id: "createJobWindow",
        items: [CreateJobForm()],
        buttons: [
          {
            text: "Cancel",
            iconCls: "so_upload",
            tabIndex: 506,
            handler: function () {
              reswindow.close();
            },
          },
          {
            text: "Save",
            iconCls: "so_upload",
            tabIndex: 505,
            handler: function () {
              saveOutboundJobs();
            },
          },
        ],
        listeners: {
          afterrender: function () {},
        },
      });
      reswindow.doLayout();
      reswindow.show();
      reswindow.center();
    },initOutboundCallRecordings: function () {
      var _outboundCallRecordingsPanelId = "panelMasterMainOutboundCallRecordings";
      var _masterPanelOutboundCallRecordings = Ext.getCmp(_outboundCallRecordingsPanelId);
      if (Ext.isEmpty(_masterPanelOutboundCallRecordings)) {
        _masterPanelOutboundCallRecordings = masterPanelforOutboundCallRecordings(
          _outboundCallRecordingsPanelId
        );
        Application.UI.addTab(_masterPanelOutboundCallRecordings);
        _masterPanelOutboundCallRecordings.doLayout();
      } else {
        Application.UI.addTab(_masterPanelOutboundCallRecordings);
      }
    },initFollowupCalls: function () {
      var _followupCallsPanelId = "panelMasterMainFollowupCalls";
      var _masterPanelFollowupCalls = Ext.getCmp(_followupCallsPanelId);
      if (Ext.isEmpty(_masterPanelFollowupCalls)) {
        _masterPanelFollowupCalls = masterPanelforFollowupCalls(
          _followupCallsPanelId
        );
        Application.UI.addTab(_masterPanelFollowupCalls);
        _masterPanelFollowupCalls.doLayout();
      } else {
        Application.UI.addTab(_masterPanelFollowupCalls);
      }
    },initOutboundCallCommunication: function () {
      var _outboundCallCommunicationPanelId = "panelMasterMainOutboundCallCommunication";
      var _masterPanelOutboundCallCommunication = Ext.getCmp(_outboundCallCommunicationPanelId);
      if (Ext.isEmpty(_masterPanelOutboundCallCommunication)) {
        _masterPanelOutboundCallCommunication = masterPanelforOutboundCallCommunication(
          _outboundCallCommunicationPanelId
        );
        Application.UI.addTab(_masterPanelOutboundCallCommunication);
        _masterPanelOutboundCallCommunication.doLayout();
      } else {
        Application.UI.addTab(_masterPanelOutboundCallCommunication);
      }
    },ViewCallCommunicationDetails: function () {
      var jobId = arguments[0];
      Ext.getCmp("callCommunicationDetailParentPanel").setTitle("View Details");
      Ext.getCmp("callCommunicationDetailParentPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=callCommunicationDetailsView",
        method: "POST",
        params: { jobId: jobId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("callCommunicationMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          } else {
            var visualsDescPanel = Ext.getCmp("callCommunicationMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("callCommunicationDetailParentPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("callCommunicationDetailParentPanel").doLayout();
    },initClosedCalls: function () {
      var _closedCallsPanelId = "panelMasterMainClosedCalls";
      var _masterPanelClosedCalls = Ext.getCmp(_closedCallsPanelId);
      if (Ext.isEmpty(_masterPanelClosedCalls)) {
        _masterPanelClosedCalls = masterPanelforClosedCalls(
          _closedCallsPanelId
        );
        Application.UI.addTab(_masterPanelClosedCalls);
        _masterPanelClosedCalls.doLayout();
      } else {
        Application.UI.addTab(_masterPanelClosedCalls);
      }
    },initMissedCalls: function () {
      var _missedCallsPanelId = "panelMasterMainMissedCalls";
      var _masterPanelMissedCalls = Ext.getCmp(_missedCallsPanelId);
      if (Ext.isEmpty(_masterPanelMissedCalls)) {
        _masterPanelMissedCalls = masterPanelforMissedCalls(
          _missedCallsPanelId
        );
        Application.UI.addTab(_masterPanelMissedCalls);
        _masterPanelMissedCalls.doLayout();
      } else {
        Application.UI.addTab(_masterPanelMissedCalls);
      }
    }
  };
})();
