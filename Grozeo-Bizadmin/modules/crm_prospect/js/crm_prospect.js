Application.Crm_Prospect = (function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = "?module=crm_prospect";
    var recs_per_page = 21;
    var WinMask;
    var imgpath = IMAGE_BASE_PATH;
  
    function updatePagination(cmp) {
      recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var crmProspectGridStore = function () {
      return new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=getProspectDetails",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "",
            root: "data",
          },
          [
            "id",
            "crpr_orgName",
            "crpr_orgEmail",
            "crpr_indMobile",
            "crco_orgPincode",
            "crco_orgCountry",
            "crpr_isActive",
            "crpr_groute",
            "crpr_glocality",
            "crpr_gplace","invitationSent","crpr_CreatedOn"
          ]
        ),
        groupField: "",
        sortInfo: {
          field: "crpr_orgName",
          direction: "ASC",
        },
        root: "data",
        autoLoad: true,
        //    remoteSort: true,
        listeners: {
          load: function (store, record, options) {
            if (record.length > 0) {
              Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName)
                .getSelectionModel()
                .selectRow(0);
            }
          },
          beforeload: function (store, e) {
              this.baseParams.typeName = Application.Crm_Prospect.Cache.typeName;
              this.baseParams.type = Application.Crm_Prospect.Cache.type;
          }
        },
      });
    };
    
  
    var htmlpanelfun = function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_prospect&op=loadEditProspectData&id=" +
        Application.Crm_Prospect.Cache.id +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey;
      var _htmlpanel = new Ext.Panel({
        id: "htmlid"+Application.Crm_Prospect.Cache.typeName,
        region: "center",
        frame: false,
        border: false,
        bodyStyle: { "background-color": "white" },
        width: winsize.width * 0.39,
        cls: "left_side_panel",
        items: [
          {
            html:
              '<iframe id="downloadIframeprospect'+Application.Crm_Prospect.Cache.typeName+'" name="downloadIframeprospect" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
              src +
              '"; ></iframe>',
          },
        ],
      });
      return _htmlpanel;
    };
    var panelProspect = function () {
      var panel = new Ext.TabPanel({
        id: "tabpanelMarketingProspect"+Application.Crm_Prospect.Cache.typeName,
        region: "east",
        frame: false,
        activeTab: 0,
        tabPosition: "top",
        border: true,
        bodyStyle: {
          "background-color": "white",
        },
        width: winsize.width * 0.4,
        defaults: {
          layout: "fit",
          frame: false,
        },
        items: [
          {
            title: "Prospect Details",
            id: "tabpanelMarketingProspectAddprospect"+Application.Crm_Prospect.Cache.typeName,
            layout: "border",
            items: [htmlpanelfun()],
          },
        ],
        listeners: {
          afterrender: function (component) {
            Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName).getSelectionModel().selectRow(0);
            var _tabpanel = Ext.getCmp("tabpanelMarketingProspect"+Application.Crm_Prospect.Cache.typeName);
            _tabpanel.setActiveTab(0);
            Ext.getCmp("htmlid"+Application.Crm_Prospect.Cache.typeName).show();
          },
          tabchange: function (s, tab) {
            var _current = Application.Crm_Prospect.Cache.status;
            switch (tab.title) {
              case "Prospect Details":
                if (_current == "UnAttended") {
                  Ext.getCmp("htmlid"+Application.Crm_Prospect.Cache.typeName).show();
                } else {
                  Ext.getCmp("htmlid"+Application.Crm_Prospect.Cache.typeName).show();
                }
                break;
            }
          },
        },
      });
      return panel;
    };
  
    var marketingProspectsPanel = function (id) {
      var _prospectPanel = new Ext.Panel({
        layout: "border",
        border: false,
        frame: false,
        bodyStyle: { "background-color": "white" },
        title: "Prospects",
        hideBorders: true,
        id: id,
        items: [crmProspectGrid(), panelProspect()],
      });
      return _prospectPanel;
    }; 
   
    var crmProspectGrid = function () {
      var _gridStore = crmProspectGridStore();
      var filters = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "crpr_orgName",
          },
          {
            type: "string",
            dataIndex: "crpr_indMobile",
          },
          {
            type: "string",
            dataIndex: "crpr_orgEmail",
          },
          {
            type: "string",
            dataIndex: "crpr_gplace",
          },
        ],
      });
      filters.remote = true;
      filters.autoReload = true;
  
      var _crmProspectGrid = new Ext.grid.GridPanel({
        id: "gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName,
        store: _gridStore,
        region: "center",
        frame: true,
        border: false,
        plugins: [filters],
        loadMask: true,
        columns: [
          {
            header: "Prospect Name",
            dataIndex: "crpr_orgName",
            sortable: true,
            width: 200,
          },
          {
            header: "Contact Number",
            dataIndex: "crpr_indMobile",
            sortable: true,
            width: 150,
          },
          {
            header: "State/Province",
            dataIndex: "crpr_gplace",
            sortable: true,
            width: 200,
          },{
            header: "Created On",
            dataIndex: "crpr_CreatedOn",
            sortable: true,
            width: 150,
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
                prospectsActionMenu.showAt(e.getXY());
                //action
              },
            },
          },
        ],
        viewConfig: {
          forceFit: true,
        },
        view: new Ext.grid.GroupingView({
          forceFit: true,
          showGroupName: false,
          enableNoGroups: false,
          enableGroupingMenu: false,
          hideGroupedColumn: false,
          deferEmptyText: false,
          getRowClass: function (record, index, params, store) {},
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        }),
        tbar: [ ],
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: _gridStore,
          displayInfo: true,
          plugins: [filters],
          displayMsg: "Displaying records {0} - {1} of {2}",
          emptyMsg: "No records to display",
          items: [],
        }),
        stripeRows: true,
        sm: new Ext.grid.RowSelectionModel({
          singleSelected: true,
          listeners: {
            selectionchange: gridSelectionChangedProspect,
          },
        }),
        listeners: {
          afterrender: function () {
            _gridStore.load();
          },
          cellclick: function (grid, rowIndex, columnIndex, e) {
            var record = grid.getStore().getAt(rowIndex);
            var record = record.data;
            var ID = record.id;
            Application.Crm_Prospect.Cache.id = ID;
            if (columnIndex != 5) {
              var _tabpanel = Ext.getCmp("tabpanelMarketingProspect"+Application.Crm_Prospect.Cache.typeName);
  
              Ext.getCmp("htmlid"+Application.Crm_Prospect.Cache.typeName).show();
              if (record.STATUS == "UnAttended") {
                Application.Crm_Prospect.Cache.status = record.STATUS;
                _tabpanel.setActiveTab(0);
                Ext.getCmp("htmlid"+Application.Crm_Prospect.Cache.typeName).show();
              } else {
                Application.Crm_Prospect.ViewMode(
                  Application.Crm_Prospect.Cache.id,
                  "NOT"
                );
                Application.Crm_Prospect.Cache.status = record.STATUS;
                _tabpanel.setActiveTab(0);
              }
            }
          },
          viewready: updatePagination,
        },
      });
      return _crmProspectGrid;
    };
    var prospectsActionMenu = new Ext.menu.Menu({
      items: [{
        text: "Send Invitation Code",
        handler: function () {
          var id = Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName)
            .getSelectionModel()
            .getSelections()[0].data.id;
            var invitationSent = Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName)
            .getSelectionModel()
            .getSelections()[0].data.invitationSent;
            if(id > 0){
              if(invitationSent == 0){
                Ext.Ajax.request({
                  waitMsg: "Processing",
                  method: "POST",
                  url: modURL + "&op=sendInvitation",
                  params: {
                    prospectId: id
                  },
                  success: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    if (tmp.success === true) {
                      Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName)
                        .getStore()
                        .load({
                          callback: function (record, options, success) {
                            Application.example.msg(
                              "Notification",
                              tmp.msg
                            );
                            var gridPanel = Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName);
                            var index = gridPanel.store.find("id", id);
                            gridPanel.getSelectionModel().selectRow(index);
                          },
                        });
                    } else {
                      Ext.MessageBox.alert("Error", tmp.msg);
                    }
                  },
                  failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert("Error", tmp.msg);
                  },
                });
              }else{
                Ext.MessageBox.alert("Notification", "Invitation already sent");
              }
              
            }
        },
      },{
        text: "Update Stages",
        handler: function () {
          var prospectId = Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName)
            .getSelectionModel()
            .getSelections()[0].data.id;
            convertToProspectStages(prospectId);
        }
      }
      ],
    });
    var prospectStatusStore = function () {
      var store = new Ext.data.JsonStore({
        url: modURL + "&op=getCrmStatus",
        method: "post",
        autoLoad: true,
        fields: ["id", "name"],
        root: "data",
      });
      return store;
    };
    var convertToProspectStages = function (id) {
      var storeProspectStatus = prospectStatusStore();
      var cpfrmProspectWindow = new Ext.Window({
        id: "windowToConvertProspect"+Application.Crm_Prospect.Cache.typeName,
        iconCls: "vender-items",
        shadow: false,
        width: winsize.width * 0.4,
        height: winsize.height * 0.4,
        title: "Update Prospect Stages",
        layout: "fit",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [
          new Ext.FormPanel({
            layout: "form",
            id: "convertToProspectFormPanel"+Application.Crm_Prospect.Cache.typeName,
            height: 150,
            columnWidth: 1,
            frame: true,
            border: true,
            items: [
              {
                xtype: "panel",
                layout: "column",
                items: [
                  {
                    columnWidth: 0.5,
                    layout: "form",
                    frame: false,
                    border: false,
                    labelAlign: "top",
                    items: [
                      {
                        xtype: "combo",
                        store: storeProspectStatus,
                        mode: "local",
                        id: "crmStatus",
                        allowBlank: true,
                        fieldLabel: "Status",
                        hiddenName: "crmStatus",
                        displayField: "name",
                        valueField: "id",
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        minChars: 2,
                        anchor: "95%",
                        triggerAction: "all",
                        lazyRender: true,
                        tabIndex: 102,
                        listeners: {
                          select: function () {
                            var value = Ext.getCmp("crmStatus").getValue();                           
                             
                          },
                        },
                      },
                    ],
                  },
                  {
                    columnWidth: 0.5,
                    layout: "form",
                    frame: false,
                    border: false,
                    labelAlign: "top",
                    items: [
                      {
                        fieldLabel: "Select Date",
                        xtype: "datefield",
                        allowblank: false,
                        id: "crmFollowupDate",
                        anchor: "95%",
                        format: "d/m/Y",
                      },
                    ],
                  },
                  {
                    columnWidth: 1,
                    layout: "form",
                    frame: false,
                    border: false,
                    labelAlign: "top",
                    items: [
                      {
                        fieldLabel: "Remarks",
                        xtype: "textarea",
                        allowblank: false,
                        id: "crmRemarks",
                        width: 330,
                        height: 50,
                        anchor: "95%",
                      },
                    ],
                  },
                ],
              },
            ],
          }),
        ],
        fbar: [{
          text: "Cancel",
          anchor: "90%",
          columnWidth: 0.1,
          xtype: "button",
          icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
          tabIndex: 33,
          handler: function () {
            cpfrmProspectWindow.close();
          },
        },
          {
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
            text: "Save",
            handler: function () {
              var crmStatus = Ext.getCmp('crmStatus').getValue();
              var crmFollowupDate = Ext.getCmp('crmFollowupDate').getValue();
              var crmRemarks = Ext.getCmp('crmRemarks').getValue();
              if(crmStatus > 0){
                Ext.Ajax.request({
                  waitMsg: "Processing",
                  method: "POST",
                  url: modURL + "&op=updateProspectStages",
                  params: {
                    prospectId: id,
                    crmStatus: crmStatus,
                    crmFollowupDate: crmFollowupDate,
                    crmRemarks: crmRemarks
                  },
                  success: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    if (tmp.success === true) {
                      Ext.getCmp("gridMarketingProspectsList")
                        .getStore()
                        .load({
                          callback: function (record, options, success) {
                            cpfrmProspectWindow.close();
                            Application.example.msg(
                              "Notification",
                              tmp.msg
                            );
                            var gridPanel = Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName);
                            var index = gridPanel.store.find("id", id);
                            gridPanel.getSelectionModel().selectRow(index);
                          },
                        });
                    } else {
                      Ext.MessageBox.alert("Error", tmp.msg);
                    }
                  },
                  failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert("Error", tmp.msg);
                  },
                });
              }else{
                Ext.MessageBox.alert("Error", "Choose status and proceed.");
              }
              
            },
          }
        ],
        listeners: {
          load: function () {},
        },
      });
  
      cpfrmProspectWindow.doLayout();
      cpfrmProspectWindow.show();
      cpfrmProspectWindow.center();
    };
    var gridSelectionChangedProspect = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName).getSelectionModel().getSelections()
        )
      ) {
        var ID = Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName)
          .getSelectionModel()
          .getSelections()[0].data.id;
        Ext.getCmp("tabpanelMarketingProspect"+Application.Crm_Prospect.Cache.typeName).setActiveTab(0);
        Application.Crm_Prospect.ViewMode(ID);
      } else {
        console.log("is it here");
        Application.Crm_Prospect.Cache.id = 0;
        Application.Crm_Prospect.ViewMode(0);
      }
    };
  
    return {
      Cache: {},
      initProspects: function (type) {
        var typeName;
        switch (type) {
          case 1:
            typeName = "RM";
            break;
          case 2:
            typeName = "EM";
            break;
          case 3:
            typeName = "WM";
            break;
          case 4:
            typeName = "AP";
            break;
          case 5:
            typeName = "CUST";
            break;
        }
        Application.Crm_Prospect.Cache.type = type;
        Application.Crm_Prospect.Cache.typeName = typeName;
        var panelId = "prospectspanel"+typeName;
        var prospects_panel = Ext.getCmp(panelId);
        if (Ext.isEmpty(prospects_panel)) {
          prospects_panel = marketingProspectsPanel(panelId);
          Application.UI.addTab(prospects_panel);
          prospects_panel.doLayout();
        } else {
          Application.UI.addTab(prospects_panel);
          prospects_panel.doLayout();
        }
      },
      ViewMode: function () {
        var prospect_id = arguments[0];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var is_UnAttended = arguments[1];
        var apikey = _SESSION.apikey;
        Ext.get("downloadIframeprospect"+Application.Crm_Prospect.Cache.typeName).dom.src =
          modURL +
          "&op=loadEditProspectData&id=" +
          prospect_id +
          "&tstamp=" +
          t_stamp +
          "&apikey=" +
          apikey +
          "&is_UnAttended=" +
          is_UnAttended;
      },
      editProspectData: function () {
        var _editForm = Ext.getCmp("formpanelAddProspect"+Application.Crm_Prospect.Cache.typeName);
        var _customerId = Ext.getCmp("prospect_customerId").getValue();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        _editForm.getForm().submit({
          waitMsg: "saving.... ",
          url: modURL,
          params: {
            op: "EditProspectDetails",
            customer_id: _customerId,
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            if (tmp.success == true) {
              Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
                Ext.getCmp("add_prospectWindow"+Application.Crm_Prospect.Cache.typeName).close(); //prospectWindowforMarketing
  
                Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName)
                  .getStore()
                  .load({
                    callback: function (record, options, success) {
                      if (_customerId > 0) {
                        console.log("here inside if");
                        var gridPanel = Ext.getCmp("gridMarketingProspectsList"+Application.Crm_Prospect.Cache.typeName);
                        var index = gridPanel.store.find("id", _customerId);
                        gridPanel.getSelectionModel().selectRow(index);
                      }
                    },
                  });
                Ext.getCmp("htmlid"+Application.Crm_Prospect.Cache.typeName).show();
              });
            }
          },
          failure: function (form, action) {
            var res = Ext.decode(action.response.responseText);
            Ext.MessageBox.alert("Error", res.errors.msg);
          },
        });
      },
    };
  })();
  