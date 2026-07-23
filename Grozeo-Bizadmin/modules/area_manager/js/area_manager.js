Application.AreaManager = (function () {
    var recs_per_page = 21;
    var modURL = "?module=area_manager";
    var winsize = Ext.getBody().getViewSize();
    var updatePagination = function (cmp) {
      recs_per_page = updateRecsPerPage(cmp);
    };
    var areaManagerDetailsView = function () {
        return new Ext.Panel({
          layout: "fit",
          region: "south",
          border: false,
          hideBorders: true,
          height: winsize.height * 0.35,
          autoScroll: true,
          id: "amMasterDetailsViewPanel",
          tpl: new Ext.XTemplate(
            '<div class="details-outer">',
            '<table border="0" width="100%" class="details_view_table">',
            '<tr><th width="40%">Name </th><td> {roName} </td></tr>',
            '<tr><th width="40%">Mobile </th><td> {roMobile} </td></tr>',
            '<tr><th width="40%">Address </th><td> {roAddress} </td></tr>',
            '<tpl if="roPincode != \'\'"><tr><th width="40%"> Pin </th><td> {roPincode} </td></tr></tpl>',
            '<tr><th width="40%">Qualification </th><td> {roQualification} </td></tr>',
            '<tr><th width="40%">Experience </th><td> {roExperience} </td></tr>',
            '<tr><th width="40%">Contact Person </th><td> {roContactPerson} </td></tr>',
            '<tr><th width="40%">Contact Mobile </th><td> {roContactMobile} </td></tr>',
            '<tr><th width="40%">Blood Group </th><td> {roBloodGroup} </td></tr>',
            '<tr><th width="40%">Licence No </th><td> {roLicenceNo} </td></tr>',
            '<tr><th width="40%">'+PAN+' No </th><td> {roPanNo} </td></tr>',
            '<tr><th width="40%">'+AADHAR+'</th><td> {roAadhaar} </td></tr>',
            '<tr><th width="40%">Bank Account</th><td> {roBankAccount} </td></tr>',
            '<tr><th width="40%">'+UPI+'</th><td> {roUPI} </td></tr>',
            "</table>",
            "</div>"
          ),
        });
      };
      var _baRelOffcrStore = function () {
        var _Store = new Ext.data.JsonStore({
          method: "post",
          proxy: new Ext.data.HttpProxy({
            url: modURL + "&op=listAreaManager",
            method: "post",
          }),
          fields: [
            "id",
            "roName",
            "roMobile",
            "roAddress",
            "roPincode",
            "rost_id",
            "rodst_Id",
            "roQualification",
            "roExperience",
            "roContactPerson",
            "roContactMobile",
            "roUsername",
            "roPassword",
            "roBloodGroup",
            "roLicenceNo",
            "roPanNo",
            "roAadhaar",
            "roBankAccount",
            "roUPI",
            "roBusAssociate",
            "roArea","areaName","dst_Name","st_name","baName","roStatus","roStatusName"
          ],
          totalProperty: "totalCount",
          root: "data",
          remoteSort: true,
          autoLoad: true,
          listeners: {
            beforeload: function (store, e) {
            },
            load: function () {
              Ext.getCmp("gridpanelforBaAreaManager").getView().refresh();
              Ext.getCmp("gridpanelforBaAreaManager")
                .getSelectionModel()
                .selectRow(0);
            },
          },
        });
        return _Store;
      };
      var _areaManagerGrid = function () {
        var _baRelOffcrStorefn = _baRelOffcrStore();
        var __roGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "roName",
            },{
              type: "string",
              dataIndex: "roMobile",
            },{
              type: "string",
              dataIndex: "areaName",
            },{
              type: "string",
              dataIndex: "baName",
            },{
              type: "string",
              dataIndex: "roStatusName",
            }
          ],
        });
        __roGridFilter.remote = true;
        __roGridFilter.autoReload = true;
        var _dispatchgridPanel = new Ext.grid.GridPanel({
          layout: "fit",
          region: "center",
          frame: false,
          border: false,
          loadMask: true,
          store: _baRelOffcrStorefn,
          iconCls: "money",
          autoScroll: true,
          width: winsize.width * 0.4,
          height: 300,
          bodyStyle: { "background-color": "white" },
          id: "gridpanelforBaAreaManager",
          plugins:[__roGridFilter],
          columns: [
            new Ext.grid.RowNumberer(),
            {
              header: "Name",
              dataIndex: "roName",
              sortable: true,
              tooltip: "Name",
              hideable: false,
            },
            {
              header: "Mobile",
              dataIndex: "roMobile",
              sortable: true,
              tooltip: "Mobile",
              hideable: false,
              width: 150,
            },
            {
              header: "Area",
              dataIndex: "areaName",
              sortable: true,
              tooltip: "Area",
              hideable: false,
            },
            {
              header: "State",
              dataIndex: "st_name",
              sortable: true,
              tooltip: "State",
              hideable: false,
            },
            {
              header: "District",
              dataIndex: "dst_Name",
              sortable: true,
              tooltip: "District",
              hideable: false,
            },{
              header: "Associate",
              dataIndex: "baName",
              sortable: true,
              tooltip: "Associate",
              hideable: false,
            },{
              header: "Status",
              dataIndex: "roStatusName",
              sortable: true,
              tooltip: "Status",
              hideable: false,
            }
          ],
          viewConfig: {
            forceFit: true,
            getRowClass: function (record, index, params, store) {},
          },
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
              selectionchange: gridSelectionChangedam,
            },
          }),
          bbar: new Ext.PagingToolbar({
            pageSize: 22,
            store: _baRelOffcrStorefn,
            displayInfo: true,
            displayMsg: "Displaying records {0} - {1} of {2}",
            emptyMsg: "No pages to display",
          }),
          listeners: {
            afterrender: function () {
              Ext.getCmp("gridpanelforBaAreaManager")
                .getStore()
                .load({
                  params: {
                  },
                });
            },
          },
        });
        return _dispatchgridPanel;
      };
      var areaManagerMainPanel = function (id) {
        var mid = 0;
        var panel = new Ext.Panel({
          layout: "border",
          border: false,
          frame: false,
          bodyStyle: { "background-color": "white" },
          hideBorders: true,
          id: id,
          title: "Area Manager",
          items: [
            _areaManagerGrid(),
            new Ext.Panel({
              title: "View Details",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.4,
              autoScroll: true,
              height: winsize.height * 0.5,
              id: "areaManagerPanel",
              items: [areaManagerDetailsView(), amLogs()],
              buttonAlign: "right",
              buttons: [{
                text: "Accept",
                id: "acceptAM",
                hidden:true,
                icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
                tabIndex: 200,
                handler: function () {
                  Application.AreaManager.roActions(Application.AreaManager.Cache.roId,'accept');
                },
              },{
                text: "Training",
                id: "trainAM",
                hidden:true,
                icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
                tabIndex: 203,
                handler: function () {
                  Application.AreaManager.trainingUpdates(Application.AreaManager.Cache.roId,Application.AreaManager.Cache.roName);
                },
              }]
            }),
          ],
        });
        return panel;
      };
      var gridSelectionChangedam = function (sm) {
        if (
          !Ext.isEmpty(
            Ext.getCmp("gridpanelforBaAreaManager")
              .getSelectionModel()
              .getSelections()
          )
        ) {
          var ID = Ext.getCmp("gridpanelforBaAreaManager").getSelectionModel().getSelections()[0].data.id;
          var roName = Ext.getCmp("gridpanelforBaAreaManager").getSelectionModel().getSelections()[0].data.roName;
          Application.AreaManager.Cache.roId = ID;
          Application.AreaManager.Cache.roName = roName;
          var roStatus = Ext.getCmp("gridpanelforBaAreaManager").getSelectionModel().getSelections()[0].data.roStatus;
          console.log('roStatus');
          console.log(roStatus);
          switch(roStatus){
            case '1':
              Ext.getCmp('acceptAM').show();
              Ext.getCmp('trainAM').hide();
              break;
            case '2':
              Ext.getCmp('acceptAM').hide();
              Ext.getCmp('trainAM').show();
            case '12':
              Ext.getCmp('acceptAM').hide();
              Ext.getCmp('trainAM').show();
              break;
            case '13':
              Ext.getCmp('acceptAM').hide();
              Ext.getCmp('trainAM').hide();
              break;
          }
          Application.AreaManager.amViewMode(ID);
        }
      };
      var amLogs = function () {
        var __amLogGridStore = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=listAMLogs",
          method: "post",
          fields: ["id","roId","roRemarks","roInterviewLink","roInterviewDate","roInterviewTime",
          "roAppointmentOrder","roCreatedOn","roCreatedBy","createdByName","roStatusName"
          ],
          remoteSort: true,
        });
      
        var __amLogGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "roStatusName",
            },
          ],
        });
        __amLogGridFilter.remote = true;
        __amLogGridFilter.autoReload = true;
        var __amLogGrid = new Ext.grid.GridPanel({
          id: "gridAMLogGrid",
          region: "north",
          height: 200,
          width: winsize.width * 0.38,
          frame: true,
          hidden: true,
          border: false,
          title: "Logs",
          autoScroll: true,
          store: __amLogGridStore,
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true,
          }),
          viewConfig: {
            forceFit: true,
          },
          plugins: [__amLogGridFilter],
          fields: ["id","roId","roRemarks","roInterviewLink","roInterviewDate","roInterviewTime",
          "roAppointmentOrder","roCreatedOn","roCreatedBy","createdByName","roStatusName"
          ],
          colModel: new Ext.grid.ColumnModel({
            columns: [
              {
                header: "Date",
                dataIndex: "roCreatedOn",
              },
              {
                header: "User",
                dataIndex: "createdByName",
              },
              
              {
                header: "Status",
                dataIndex: "roStatusName",
              },        
              {
                header: "Remarks",
                dataIndex: "roRemarks",
                renderer: qtipRenderer,
              },
            ],
          }),
          iconCls: "icon-grid",
          listeners: {
            afterrender: function () {},
          },
        });
        return __amLogGrid;
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
      var topicStores = function () {
        var _topicStore = new Ext.data.JsonStore({
            autoLoad: false,
            url: modURL + '&op=getTopics',
            method: 'post',
            fields: ['id', 'topicName'],
            remoteSort: true
        });
        return _topicStore;
      };
      var moduleStores = function () {
      var _topicStore = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + '&op=getModules',
          method: 'post',
          fields: ['id', 'name'],
          remoteSort: true
      });
      return _topicStore;
      };
      var training_grid_panel = function(aaId){    
        //var _aatrainingModuelStore = trainingModuelStore(aaId);  
        var _aatrainingModuelStore = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=listTrainingModules",
          method: "post",
          fields: ["tmtId","ModuleId","moduleName","topicName","checked","trainingId","trainingComments","trainingDate"],
          remoteSort: true,
          listeners: {
            beforeload:function(){
              this.baseParams.aaId = aaId;
            }
          }
        });
      
        var __rcGridFilter = new Ext.ux.grid.GridFilters({
          filters: [
            {
              type: "string",
              dataIndex: "moduleName",
            },{
              type: "string",
              dataIndex: "topicName",
            },
          ],
        });
        __rcGridFilter.remote = true;
        __rcGridFilter.autoReload = true;
        var _vrmTrainingModuleGrid = new Ext.grid.GridPanel({
          id: "gridUserTraining",
          height: 435,
          width: winsize.width * 0.8,
          frame: true,
          border: false,
          autoScroll: true,
          store: _aatrainingModuelStore,
          sm: new Ext.grid.RowSelectionModel({
            singleSelect: true      
        }),   
          plugins: [__rcGridFilter],//new Ext.ux.grid.GroupSummary(),
          fields: ["tmtId","ModuleId","moduleName","topicName","checked","trainingId","trainingComment","trainingDate"],
          colModel: new Ext.grid.ColumnModel({
            columns: [
              new Ext.grid.RowNumberer(),
              {
                header: "Date",
                dataIndex: "trainingDate",
              },
              {
                header: "Module",
                dataIndex: "moduleName",
              },{
                header: "Topic",
                dataIndex: "topicName",
              },{
                header: "Comment",
                dataIndex: "trainingComments",
              }
            ]
          }),
          iconCls: "icon-grid",
          /*view: new Ext.grid.GroupingView({
            forceFit: true,
            deferEmptyText: false,
            getRowClass: function (record, index, params, store) {},
            emptyText:
              '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            groupTextTpl:
              '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
          }),*/viewConfig: {
            forceFit: true,
          },
          listeners: {
            afterrender: function () {
             
            },
          }
        });
        return _vrmTrainingModuleGrid;
      };
    return {
        Cache: {},
        initAM: function () {
            var panelId = "panelAreaManager";
            var listAreaManager = Ext.getCmp(panelId);
            if (Ext.isEmpty(listAreaManager)) {
                listAreaManager = areaManagerMainPanel(panelId);
              Application.UI.addTab(listAreaManager);
              listAreaManager.doLayout();
            } else {
              Application.UI.addTab(listAreaManager);
            }
          },amViewMode: function () {
            var id = arguments[0];
            Ext.getCmp("amMasterDetailsViewPanel").show();
            Ext.getCmp("areaManagerPanel").setTitle("View Details");
            Ext.getCmp("areaManagerPanel").doLayout();
            Ext.Ajax.request({
              url: modURL + "&op=amDetailsView",
              method: "POST",
              params: { id: id },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  var visualsDescPanel = Ext.getCmp("amMasterDetailsViewPanel");
                  visualsDescPanel.update(tmp);
                  Ext.getCmp("gridAMLogGrid").show();
                  Ext.getCmp("gridAMLogGrid")
                  .getStore()
                  .load({
                    params: {
                      roId: id,
                    },
                  });
                }
                Ext.getCmp("areaManagerPanel").doLayout();
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
            Ext.getCmp("areaManagerPanel").doLayout();
          },roActions: function(id,action){
            var id = arguments[0];
            var action = arguments[1];
            Ext.Ajax.request({
              url: modURL + "&op=roActions",
              method: "POST",
              params: { id: id,action: action },
              success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true) {
                  Ext.getCmp("gridpanelforBaAreaManager")
                .getStore()
                .load({
                  params: {
                  },
                });
                }
              },
              failure: function () {
                Ext.MessageBox.alert("Error", "Error occured while sending data");
              },
            });
          },trainingUpdates: function(aaId,titleName){
            var topicStore = topicStores();
            var moduleStore = moduleStores();
            var win_id = "incubatee_training_structure";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {
                win = new Ext.Window({
                    id: win_id,
                    title: 'Training Modules for ' + titleName,
                    width: winsize.width * 0.83,
                    height: 600,              
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: new Ext.form.FormPanel({
                      id: 'formpanelAddSMSTemplate',
                      bodyStyle: {"background-color": "white", "padding": "5px 5px 59px 10px", height: 500},
                      labelAlign: 'top',
                      layout: 'column',
                      items: [{
                        layout: 'form',
                        border: false,
                        columnWidth: 0.33,
                        items: [{
                          fieldLabel: 'Date',
                          emptyText: 'Date',
                          xtype: 'datefield',
                          id: 'trainingDate',
                          name: 'trainingDate',
                          format: "d/m/Y",
                          anchor: '98%',
                      }]
                    }, {
                        layout: 'form',
                        border: false,
                        columnWidth: 0.33,
                        items: [{
                                xtype: 'combo',
                                displayField: 'name',
                                valueField: 'id',
                                mode: 'local',
                                id: 'trainingModule',
                                name: 'trainingModule',
                                forceSelection: true,
                                allowBlank: false,
                                fieldLabel: 'Module',
                                emptyText: 'Module',
                                anchor: '98%',
                                typeAhead: true,
                                triggerAction: 'all',
                                lazyRender: true,
                                editable: true,
                                minChars: 2,
                                tabIndex: 101,
                                store: moduleStore,
                                listeners: {
                                    select: function (combo, record, index) {
                                        topicStore.baseParams.ModuleId = this.value;
                                        topicStore.load();
                                    }
                                }
                            }
                        ]
                    },
                    {
                        layout: 'form',
                        border: false,
                        columnWidth: 0.33,
                        items: [
                            {
                                xtype: 'combo',
                                displayField: 'topicName',
                                valueField: 'id',
                                mode: 'local',
                                id: 'trainingTopics',
                                name: 'trainingTopics',
                                forceSelection: true,
                                fieldLabel: 'Topics',
                                emptyText: 'Topics',
                                anchor: '99%',
                                allowBlank: false,
                                typeAhead: true,
                                triggerAction: 'all',
                                lazyRender: true,
                                editable: true,
                                minChars: 2,
                                tabIndex: 102,
                                store: topicStore
                            }
                        ]
                    },{
                      layout: 'form',
                      border: false,
                      columnWidth: 0.90,
                      items: [{
                        fieldLabel: 'Comments',
                        emptyText: 'Comments',
                        xtype: 'textfield',
                        maxLength: 2000,
                        id: 'trainingComments',
                        name: 'trainingComments',
                        anchor: '98%',
                    }]
                  },{
                    layout: 'form',
                    border: false,
                    columnWidth: 0.10,
                    items: [{
                      xtype: 'button',
                      style: 'margin:15px 8px 0px 0px',
                      anchor: '90%',
                      tabIndex: 105,
                      text: 'Add',
                      handler: function () {
                        Ext.Ajax.request({
                          url: modURL + "&op=saveTrainings",
                          method: "POST",
                          params: {
                            aaId:aaId,
                            trainingDate:Ext.getCmp('trainingDate').getValue(),
                            trainingTopics:Ext.getCmp('trainingTopics').getValue(),
                            trainingModule:Ext.getCmp('trainingModule').getValue(),
                            trainingComments:Ext.getCmp('trainingComments').getValue(),
                          },
                          success: function (response) {
                            var tmp = Ext.decode(response.responseText);
                            if (tmp.success === true && tmp.valid === true) {
                              Application.example.msg("Success", tmp.message);
                              Ext.getCmp('gridUserTraining').getStore().load({
                                baseParams: {"aaId": aaId}
                              });
                              Ext.getCmp('trainingTopics').reset();
                              Ext.getCmp('trainingModule').reset();
                              Ext.getCmp('trainingComments').reset();
                              //win.close();
                            } else if (tmp.success === true && tmp.valid === false) {
                              Ext.Msg.alert("Notification.", tmp.message);
                            } else if (tmp.success === true && tmp.img_valid === false) {
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
                  }]
                },{
                  layout: 'form',
                  border: false,
                  columnWidth: 1,
                  items: [training_grid_panel(aaId)]
              }]
            }), 
                    buttons: [
                      {
                            text: 'Save',
                            tabIndex: 501,
                            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                            handler: function () {
                              aaTrainingIds = [];
      
                              var store_fields = Ext.getCmp("gridUserTraining")
                                .getSelectionModel()
                                .getSelections();
                              for (var i = 0; i < store_fields.length; i++) {
                                aaTrainingIds[i] = store_fields[i].data.tmtId;
                              }
                              Ext.Ajax.request({
                                url: modURL + "&op=confirmTrainings",
                                method: "POST",
                                params: {
                                  aaId:aaId
                                },
                                success: function (response) {
                                  var tmp = Ext.decode(response.responseText);
                                  if (tmp.success === true && tmp.valid === true) {
                                    Application.example.msg("Success", tmp.message);
                                    win.close();
                                    Ext.getCmp("gridpanelforBaAreaManager").getStore().load();
                                  } else if (tmp.success === true && tmp.valid === false) {
                                    Ext.Msg.alert("Notification.", tmp.message);
                                  } else if (tmp.success === true && tmp.img_valid === false) {
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
                      {
                            text: 'Close',
                            tabIndex: 502,
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            handler: function () {
                                win.close();
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                          Ext.getCmp('gridUserTraining').getStore().load({
                            baseParams: {"aaId": aaId}
                          });
                        }
                    }
                });
            }
      
            win.doLayout();
            win.show(this);
            win.center();
        }
    };
})();