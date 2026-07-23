Application.Crm_ConsultingPartner = (function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = "?module=crm_consulting_partner";
    var bamodURL = "?module=business_associate";
    var recs_per_page = 25;
    var WinMask;
    var expandAll = false;
    var imgpath = IMAGE_BASE_PATH;
    var aa_trainingTopics = new Array();
    var onGridResize = function (cmp) {
      recs_per_page = 25;
    };
    var gridSelectionChangedaaContact = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp(
            "gridAssociatePartnerContacts" + Application.Crm_ConsultingPartner.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()
        )
      ) {
        var ID = Ext.getCmp(
          "gridAssociatePartnerContacts" + Application.Crm_ConsultingPartner.Cache.typeName
        )
          .getSelectionModel()
          .getSelections()[0].data.aaId;
        Ext.getCmp(
          "tabpanelAssociatePartnerContact" + Application.Crm_ConsultingPartner.Cache.typeName
        ).setActiveTab(0);
        Application.Crm_ConsultingPartner.Cache.aaId = ID;
        Application.Crm_ConsultingPartner.ViewModeContact(ID);
      } else {
        Application.Crm_ConsultingPartner.Cache.aaId = 0;
        Application.Crm_ConsultingPartner.ViewModeContact(0);
      }
    };
    var associatePartnerContactPanel = function (id, typeName) {
      var contactsPanel = new Ext.Panel({
        layout: "border",
        border: false,
        frame: false,
        bodyStyle: { "background-color": "white" },
        hideBorders: true,
        title: "Consulting Partner Contacts",
        id: id,
        items: [gridAPContactDetails(typeName), panelAPContact(typeName)],
      });
      return contactsPanel;
    };
  
    var tableCotactDetails = function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey;
      var contactsPanel = new Ext.Panel({
        id: "tableCrmContactsDataview" + Application.Crm_ConsultingPartner.Cache.typeName,
        region: "center",
        width: winsize.width * 0.39,
        items: [
          {
            region: "center",
            defaults: {
              frame: false,
            },
            html:
              '<iframe id="downloadAPContactsIframe' +
              Application.Crm_ConsultingPartner.Cache.typeName +
              '" name="downloadAPContactsIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
              src +
              '"; ></iframe>',
          },
        ],
      });
      return contactsPanel;
    };
    var panelAPContact = function (typeName) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey +
        "&typeName=" +
        typeName;
      var panel = new Ext.TabPanel({
        region: "east",
        frame: false,
        activeTab: 0,
        tabPosition: "top",
        border: true,
        bodyStyle: {
          "background-color": "white",
        },
        id: "tabpanelAssociatePartnerContact" + typeName,
        width: winsize.width * 0.4,
        height: winsize.height * 0.6,
        defaults: {
          layout: "fit",
          autoScroll: true,
          frame: false,
        },
        items: [
          {
            title: "View Contact Details",
            id: "tabMarketingAddContact" + typeName,
            layout: "border",
            items: [tableCotactDetails()],
          },{
            title: "Log",
            id: "tabMarketingCommunicationContact" + typeName,
            layout: "border",
            items: [communicationLogDetails('Contact')],
          }
        ],
        fbar: [],
        listeners: {
          afterrender: function (component) {},
          tabchange: function (s, tab) {
            switch (tab.title) {
              case "Log":
                Ext.getCmp('gridCommunicationListContact').getStore().load({
                  params:{
                    aaId: Application.Crm_ConsultingPartner.Cache.aaId
                  }
                });
                break;
            }
          },
        },
      });
      return panel;
    };
    var contactGridStore = function (typeName) {
      return new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=getContactDetails",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "",
            root: "data",
          },
          [
            "aaId",
            "aaName",
            "aaMobile",
            "aaEmail",
            "businessVertical",
            "professionId","professionName",
            "businessVertical",
            "organisationName",
            "organisationType",
            "gstNumber",
            "businessLocation",
            "locationGlatitude",
            "locationGlongitude",
            "createdBy","createdByName",
            "createdOn",
            "updatedBy",
            "updatedOn",
          ]
        ),
        groupField: "",
        sortInfo: {
          field: "aaId",
          direction: "ASC",
        },
        root: "data",
        autoLoad: true,
        listeners: {
          load: function (store, record, options) {
            if (record.length > 0) {
              Ext.getCmp("gridAssociatePartnerContacts" + typeName)
                .getSelectionModel()
                .selectRow(0);
            }
          },
          beforeload: function (store, e) {
            this.baseParams.typeName = typeName;
            this.baseParams.type = Application.Crm_ConsultingPartner.Cache.type;
          },
        },
      });
    };
    var gridAPContactDetails = function (typeName) {
      var _jsonStoreContactGrid = contactGridStore(typeName);
      var _contactsGridFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "aaName",
          },
          {
            type: "string",
            dataIndex: "aaMobile",
          },
          {
            type: "string",
            dataIndex: "aaEmail",
          },
        ],
      });
      _contactsGridFilter.remote = true;
      _contactsGridFilter.autoReload = true;
      var contactsGrid = new Ext.grid.GridPanel({
        id: "gridAssociatePartnerContacts" + typeName,
        layout: "fit",
        store: _jsonStoreContactGrid,
        region: "center",
        frame: true,
        border: false,
        plugins: [_contactsGridFilter],
        loadMask: true,
        columns: [
          {
            header: "Name",
            dataIndex: "aaName",
            sortable: true,
            width: 200,
          },
          {
            header: "Mobile",
            dataIndex: "aaMobile",
            sortable: true,
          },
          {
            header: "Email",
            dataIndex: "aaEmail",
            sortable: true,
            width: 200,
          },
          {
            header: "Profession",
            dataIndex: "professionName",
            sortable: true,
          },          
          {
            header: "Created By",
            dataIndex: "createdByName",
            sortable: true,
            hideable: true,
          },{
            header: "Created On",
            dataIndex: "createdOn",
            sortable: true,
          },
          {
            xtype: "actioncolumn",
            header: "Action",
            hideable: false,
            iconCls: "downarrow",
            tooltip: "Choose Actions",
            listeners: {
              click: function (a, grid, rowindex, e) {
                var record = grid.store.getAt(rowindex);
                grid.getSelectionModel().selectRow(rowindex);
                fcontactsActionMenu(e, typeName);
                //action
              },
            },
          },
        ],
        tbar: [
          {
            xtype: "button",
            text: "Create Contact",
            tooltip: "Create Contact",
            iconCls: "finascop_add",
            handler: function () {
              APContactForm();
            },
          },
        ],
        viewConfig: {
          forceFit: true,
        },
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          getRowClass: function (record, index, params, store) {},
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: _jsonStoreContactGrid,
          displayInfo: true,
          displayMsg: "Displaying items {0} - {1} of {2}",
          emptyMsg: "No records to display",
          items: [,],
        }),
        stripeRows: true,
        sm: new Ext.grid.RowSelectionModel({
          singleSelected: true,
          listeners: {
            selectionchange: gridSelectionChangedaaContact,
          },
        }),
        listeners: {
          afterrender: function () {
            _jsonStoreContactGrid.load();
          },
          cellclick: function (grid, rowIndex, columnIndex, e) {},
          resize: onGridResize,
        },
      });
      return contactsGrid;
    };
    var fcontactsActionMenu = function (e, typeName) {
      var fcontactsActionMenu = new Ext.menu.Menu({
        items: [
          {
            text: "Edit",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerContacts" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;             
                APContactForm(aaId);
              
            },
          },
          {
            text: "Move to Lead",
            hidden:true,
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerContacts" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
              
            },
          },
        ],
      });
      fcontactsActionMenu.showAt(e.getXY());
    };
   
   
    var apPreferredArea = function () {
      var _referenceStore = new Ext.data.JsonStore({
        url: modURL + "&op=getPreferredAreas",
        method: "post",
        fields: ["id", "areaName"],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true,
      });      
      return _referenceStore;
    };
    var apProffession = function () {
        var _referenceStore = new Ext.data.JsonStore({
          autoLoad: true,
          url: modURL + "&op=getProffession",
          method: "post",
          fields: ["id", "name"],
          remoteSort: true,
          listeners: {
            beforeload: function () {
            },
          },
        });
        return _referenceStore;
      };
      var stateStoreEdit = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: bamodURL + '&op=getStates',
            method: 'post',
            fields: ['st_ID', 'st_name'],
            totalProperty: 'totalCount',
            root: 'data'

        });

        return store;
    };
    var districtStoreEdit = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: false,
            url: bamodURL + '&op=getDistrict',
            method: 'post',
            fields: ['dst_ID', 'dst_Name'],
            totalProperty: 'totalCount',
            root: 'data'
        });

        return store;
    };
    function initAutocompleteText() {
      var input = document.getElementById("businessLocation");
      var searchBox = new google.maps.places.SearchBox(input);
      var autocomplete = new google.maps.places.Autocomplete(input);
      console.log("autocomplete");
      console.log(autocomplete);
      autocomplete.addListener("place_changed", function () {
        var place = autocomplete.getPlace();
        var latitude = place.geometry.location.lat();
        var longitude = place.geometry.location.lng();
        console.log(place.address_components);
        for (var i = 0; i < place.address_components.length; i++) {
          var addressTypes = place.address_components[i].types[0];
          console.log("addressTypes");
          console.log(addressTypes);
          switch (addressTypes) {
            case "postal_code":
              document.getElementById("crco_orgPincode").value =
                place.address_components[i].long_name;
              break;
            case "country":
              document.getElementById("locationCountry").value =
                place.address_components[i].long_name;
              break;            
            case "route":
              document.getElementById("locationGroute").value =
                place.address_components[i].long_name;
              break;
            case "postal_code_suffix":
              document.getElementById("crco_gpostsuffix").value =
                place.address_components[i].long_name;
              break;
            case "locality":
              document.getElementById("locationGlocality").value =
                place.address_components[i].long_name;
              break;
            case "administrative_area_level_1":
              document.getElementById("locationGplace").value =
                place.address_components[i].long_name;
              break;
          }
        } 
       
        document.getElementById("locationGlatitude").value = latitude;
        document.getElementById("locationGlongitude").value = longitude;
        Ext.getCmp('preferredArea').getStore().load({
            baseParams: {"locationGlatitude": latitude,"locationGlongitude": longitude}
        });

      });
    }
    var APContactForm = function (contactid) {
      var preferedAreaStore = apPreferredArea();
      var apProffessionStore = apProffession();
      var _state_store = stateStoreEdit();
      var district_store = districtStoreEdit();
      var APContactForm = new Ext.Window({
        width: 500,
        autoHeight: true,
        id: "add_contactWindow" + Application.Crm_ConsultingPartner.Cache.typeName,
        shadow: false,
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        closable: false,
        items: [
          new Ext.form.FormPanel({
            id: "formpanelAddContact" + Application.Crm_ConsultingPartner.Cache.typeName,
            title: !Ext.isEmpty(contactid) ? "Edit Contact" : "Create Contact",
            bodyStyle: {
              "background-color": "white",
              padding: "5px 5px 59px 10px",
            },
            labelAlign: "top",
            items: [
              {
                layout: "column",
                border: false,
                items: [
                  {
                    layout: "form",
                    border: false,
                    columnWidth: 0.5,
                    items: [
                      {
                        xtype: "textfield",
                        id: "aaId",
                        hidden: true,
                      },                      
                      {
                        layout: "column",
                        fieldLabel: "Name",
                        maxLength: 250,
                        tabIndex: 300,
                        xtype: "textfield",
                        anchor: "98%",
                        allowBlank: false,
                        id: "aaName",
                        name: "aaName",
                        hideBorders: true,
                        border: false,
                      }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        layout: "column",
                        fieldLabel: "Mobile",
                        maxLength: 250,
                        tabIndex: 300,
                        xtype: "textfield",
                        anchor: "98%",
                        allowBlank: false,
                        id: "aaMobile",
                        name: "aaMobile",
                        hideBorders: true,
                        border: false,
                      }
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        layout: "column",
                        fieldLabel: "Email",
                        maxLength: 250,
                        tabIndex: 300,
                        xtype: "textfield",
                        anchor: "98%",
                        allowBlank: false,
                        id: "aaEmail",
                        name: "aaEmail",
                        hideBorders: true,
                        border: false,
                      }
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        xtype: "combo",
                        fieldLabel: "Proffession",
                        emptyText: "Proffession",
                        id: "professionId",
                        name: "professionId",
                        allowBlank: false,
                        tabIndex: 301,
                        labelStyle: mandatory_label,
                        mode: "local",
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        anchor: "98%",
                        store: apProffessionStore,
                        triggerAction: "all",
                        minChars: 2,
                        displayField: "name",
                        valueField: "id",
                        hiddenName: "professionId",
                        listeners: {
                          select: function () {
                            var professionId = Ext.getCmp('professionId').getValue();
                            /*if(professionId == 1){
                                Ext.getCmp('businessVertical').show();
                                Ext.getCmp('organisationName').show();
                                Ext.getCmp('organisationType').show();
                                Ext.getCmp('gstNumber').show();
                            }else{
                                Ext.getCmp('businessVertical').hide();
                                Ext.getCmp('organisationName').hide();
                                Ext.getCmp('organisationType').hide();
                                Ext.getCmp('gstNumber').hide();
                            }*/
                          },
                        },
                      }
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "PAN",
                        tabIndex: 305,
                        id: "panNumber",
                        name: "panNumber",
                        anchor: "98%",
                        maxLength: 100,
                      },
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: GST+" Number",
                        tabIndex: 303,
                        id: "gstNumber",
                        name: "gstNumber",
                        anchor: "98%",
                        maxLength: 100,
                      },                      
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 1,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "Business Location",
                        tabIndex: 302,
                        id: "businessLocation",
                        name: "businessLocation",
                        anchor: "98%",
                        maxLength: 1000,
                        listeners: {
                          focus: function () {
                            initAutocompleteText();
                          },
                        },
                      },{
                        xtype: "textfield",
                        fieldLabel: "Country",
                        tabIndex: 303,
                        id: "locationCountry",
                        name: "locationCountry",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Route",
                        tabIndex: 303,
                        id: "locationGroute",
                        name: "locationGroute",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Locality",
                        tabIndex: 303,
                        id: "locationGlocality",
                        name: "locationGlocality",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Place",
                        tabIndex: 303,
                        id: "locationGplace",
                        name: "locationGplace",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Latitude",
                        tabIndex: 303,
                        id: "locationGlatitude",
                        name: "locationGlatitude",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Longitude",
                        tabIndex: 303,
                        id: "locationGlongitude",
                        name: "locationGlongitude",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "Business Vertical",
                        tabIndex: 305,
                        id: "businessVertical",
                        name: "businessVertical",
                        anchor: "98%",
                        maxLength: 100,
                        hidden:true
                      },
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "Organisation Name",
                        tabIndex: 306,
                        id: "organisationName",
                        name: "organisationName",
                        anchor: "98%",
                        maxLength: 100,
                        hidden:true
                      },
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "Type of Organisation",
                        tabIndex: 307,
                        id: "organisationType",
                        name: "organisationType",
                        anchor: "98%",
                        maxLength: 100,
                        hidden:true
                      },
                    ],
                  },                  
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                        {
                          hidden:true,
                            fieldLabel: "Preffered Area",
                            xtype: "lovcombo",
                            displayField: "areaName",
                            valueField: "id",
                            mode: "local",
                            id: "preferredArea",
                            hiddenName: "preferredArea",
                            typeAhead: true,
                            triggerAction: "all",
                            lazyRender: true,
                            tabIndex: 301,
                            anchor: "98%",
                            minChars: 2,
                            store: preferedAreaStore,
                            editable: true,
                            listeners: {
                              select: function () {
                                var preferredArea = Ext.getCmp("preferredArea").getValue();
                                var areasArray = preferredArea.split(",");
                                if (areasArray.includes("-1")) {
                                  Ext.getCmp("as_st_id").show();
                                  Ext.getCmp("as_dst_Id").show();
                                }else{
                                  Ext.getCmp("as_st_id").hide();
                                  Ext.getCmp("as_dst_Id").hide();
                                }
                                
                              },
                            }
                          }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        xtype: 'combo',
                        fieldLabel: 'State',
                        name: 'as_st_id',
                        id: 'as_st_id',
                        hiddenName: 'as_st_id',
                        anchor: '97%',
                        store: _state_store,
                        valueField: 'st_ID',
                        tabIndex: 403,
                        displayField: 'st_name',
                        forceSelection: true,
                        triggerAction: 'all',
                        typeAhead: true,
                        selectOnFocus: true,
                        mode: 'local',
                        listeners: {
                            select: function () {

                                var value = Ext.getCmp('as_st_id').getValue();
                                console.log("District value after dst", value, this.value);

                                district_store.baseParams.st_Id = this.value;
                                district_store.load();
                            }
                        }
                    }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        xtype: 'combo',
                        fieldLabel: 'District',
                        id: 'as_dst_Id',
                        name: 'as_dst_Id',
                        tabIndex: 404,
                        hiddenName: 'as_dst_Id',
                        anchor: '97%',
                        store: district_store,
                        valueField: 'dst_ID',
                        displayField: 'dst_Name',
                        forceSelection: true,
                        triggerAction: 'all',
                        typeAhead: true,
                        selectOnFocus: true,
                        mode: 'local',
                    }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 1,
                    border: false,
                    items: [{
                    xtype: "textarea",
                    height: 50,
                    fieldLabel: "Address",
                    id: "cpAddress",
                    name: "cpAddress",
                    tabIndex: 405,
                    anchor: "99%",
                    allowBlank: false,
                    listeners: {
                      
                    },
                  }],
                  }
                  
                ],
              },
            ],
          }),
        ],
        fbar: [
          {
            text: "Cancel",
            anchor: "90%",
            columnWidth: 0.1,
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            tabIndex: 311,
            handler: function () {
              Ext.getCmp(
                "gridAssociatePartnerContacts" +
                  Application.Crm_ConsultingPartner.Cache.typeName
              )
                .getStore()
                .load();
              APContactForm.close();
            },
          },
          {
            text: "Save",
            anchor: "95%",
            columnWidth: 0.1,
            tabIndex: 310,
            bodyStyle: { margin: "0px 0px 0px 140px" },
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            tabIndex: 304,
            handler: function () {
              var areaAssociateId = Ext.getCmp("aaId").getValue();
              var _addContactData = Ext.getCmp(
                "formpanelAddContact" + Application.Crm_ConsultingPartner.Cache.typeName
              ); 
              contactInsertion(areaAssociateId);
            },
          },
        ],
      });
      if (contactid > 0) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var regFee_form = Ext.getCmp(
          "formpanelAddContact" + Application.Crm_ConsultingPartner.Cache.typeName
        ).getForm();
        regFee_form.load({
          params: {
            EditStatus: 1,
            _edit_aaId: contactid,
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=loadEditData",
          waitMsg: "Loading...",
          success: function (form, action) {
            eval("var tmp=" + action.response.responseText);
            var tmp = Ext.decode(action.response.responseText);
            console.log('formload');
            console.log(tmp);       
            Ext.getCmp('professionId').getStore().load();
            Ext.getCmp('professionId').setValue(tmp.data.professionId); 
            Ext.getCmp('professionId').setRawValue(tmp.data.professionName); 
            
            Ext.getCmp('as_dst_Id').getStore().baseParams.st_Id = tmp.data.as_st_id;
            Ext.getCmp('as_dst_Id').getStore().load();
            Ext.getCmp('as_dst_Id').setValue(tmp.data.as_dst_Id); 
            
          },
          failure: function (form, action) {},
        });
      } else {
      }
      APContactForm.doLayout();
      APContactForm.show();
      APContactForm.center();
      return APContactForm;
    };
  
    var contactInsertion = function (areaAssociateId) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var _addeditform = Ext.getCmp(
        "formpanelAddContact" + Application.Crm_ConsultingPartner.Cache.typeName
      );
      if (areaAssociateId > 0) {
        areaAssociateId = areaAssociateId;
        Application.Crm_ConsultingPartner.Cache.upcrocid = areaAssociateId;
      } else {
        areaAssociateId = "-";
        Application.Crm_ConsultingPartner.Cache.upcrocid = 0;
      }
      if (_addeditform.getForm().isValid()) {
        Application.Crm_ConsultingPartner.insertData();
      } else {
        Ext.MessageBox.alert("Notification", "Please enter all required fields");
      }
    };  
    var associatePartnerEnquiryPanel = function (id, typeName) {
      var contactsPanel = new Ext.Panel({
        layout: "border",
        border: false,
        frame: false,
        bodyStyle: { "background-color": "white" },
        hideBorders: true,
        title: "Consulting Partner Enquiry",
        id: id,
        items: [gridAPEnquiryDetails(typeName), panelAPEnquiry(typeName)],
      });
      return contactsPanel;
    };
    var enquiryGridStore = function (typeName) {
      return new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listEnquiryDetails",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "",
            root: "data",
          },
          [
            "aaId",
            "aaName",
            "aaMobile",
            "aaEmail",
            "businessVertical",
            "professionId","professionName",
            "businessVertical",
            "organisationName",
            "organisationType",
            "gstNumber",
            "businessLocation",
            "locationGlatitude",
            "locationGlongitude",
            "createdBy","createdByName",
            "createdOn",
            "updatedBy",
            "updatedOn",
          ]
        ),
        groupField: "",
        sortInfo: {
          field: "aaId",
          direction: "ASC",
        },
        root: "data",
        autoLoad: true,
        listeners: {
          load: function (store, record, options) {
            if (record.length > 0) {
              Ext.getCmp("gridAssociatePartnerEnquiry" + typeName)
                .getSelectionModel()
                .selectRow(0);
            }
          },
          beforeload: function (store, e) {
            this.baseParams.typeName = typeName;
            this.baseParams.type = Application.Crm_ConsultingPartner.Cache.type;
          },
        },
      });
    };
    var gridAPEnquiryDetails = function (typeName) {
      var _jsonStoreEnquiryGrid = enquiryGridStore(typeName);
      var _enquiryGridFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "aaName",
          },
          {
            type: "string",
            dataIndex: "aaMobile",
          },
          {
            type: "string",
            dataIndex: "aaEmail",
          },
        ],
      });
      _enquiryGridFilter.remote = true;
      _enquiryGridFilter.autoReload = true;
      var enquiryGrid = new Ext.grid.GridPanel({
        id: "gridAssociatePartnerEnquiry" + typeName,
        layout: "fit",
        store: _jsonStoreEnquiryGrid,
        region: "center",
        frame: true,
        border: false,
        plugins: [_enquiryGridFilter],
        loadMask: true,
        columns: [
          {
            header: "Name",
            dataIndex: "aaName",
            sortable: true,
            width: 200,
          },
          {
            header: "Mobile",
            dataIndex: "aaMobile",
            sortable: true,
          },
          {
            header: "Email",
            dataIndex: "aaEmail",
            sortable: true,
            width: 200,
          },
          {
            header: "Profession",
            dataIndex: "professionName",
            sortable: true,
          },{
            header: "Created Date",
            dataIndex: "createdOn",
            sortable: true,
          }, {
            xtype: "actioncolumn",
            header: "Action",
            hideable: false,
            iconCls: "downarrow",
            tooltip: "Choose Actions",
            listeners: {
              click: function (a, grid, rowindex, e) {
                var record = grid.store.getAt(rowindex);
                grid.getSelectionModel().selectRow(rowindex);
                aaEnquiryActionMenu(e, typeName);
                //action
              },
            },
          },
        ],
        tbar: [
        ],
        viewConfig: {
          forceFit: true,
        },
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          getRowClass: function (record, index, params, store) {},
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: _jsonStoreEnquiryGrid,
          displayInfo: true,
          displayMsg: "Displaying items {0} - {1} of {2}",
          emptyMsg: "No records to display",
          items: [,],
        }),
        stripeRows: true,
        sm: new Ext.grid.RowSelectionModel({
          singleSelected: true,
          listeners: {
            selectionchange: gridSelectionChangedaaEnquiry,
          },
        }),
        listeners: {
          afterrender: function () {
            _jsonStoreEnquiryGrid.load();
          },
          cellclick: function (grid, rowIndex, columnIndex, e) {},
          resize: onGridResize,
        },
      });
      return enquiryGrid;
    };
    var aaEnquiryActionMenu = function (e, typeName) {
      var aaEnquiryActionMenu = new Ext.menu.Menu({
        items: [          
          {
            text: "Move to Contact",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerEnquiry" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                Ext.MessageBox.confirm('Confirm', 'Do you want to move this as contact?', function (btn, text) {
                  if (btn == 'yes') {
                      enquiryToContact(aaId,typeName);
                  }
              });
            },
          },{
            text: "Remove",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerEnquiry" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                Ext.MessageBox.confirm('Confirm', 'Do you want to remove ?', function (btn, text) {
                  if (btn == 'yes') {
                    removeGeneralEnquiry(aaId,typeName);
                  }
              });
            }
          }
        ],
      });
      aaEnquiryActionMenu.showAt(e.getXY());
    };
    var enquiryToContact = function (id,typeName) {
      Ext.Ajax.request({
          waitMsg: 'Processing',
          method: 'POST',
          url: modURL + '&op=convertToContact',
          params: {
              enquiryId: id
          },
          success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              if (tmp.success === true) {
                  //Ext.MessageBox.alert("Success", "Enquiry converted to contact", function (btn) { 
                  Ext.getCmp('gridAssociatePartnerEnquiry'+typeName).getStore().reload();
                  // });
              }else{
                  Ext.MessageBox.alert('Error', tmp.msg);
              }
          },
          failure: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              Ext.MessageBox.alert('Error', "Error occurred");
          }
      });
  };
    var gridSelectionChangedaaEnquiry = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp(
            "gridAssociatePartnerEnquiry" + Application.Crm_ConsultingPartner.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()
        )
      ) {
        var ID = Ext.getCmp(
          "gridAssociatePartnerEnquiry" + Application.Crm_ConsultingPartner.Cache.typeName
        )
          .getSelectionModel()
          .getSelections()[0].data.aaId;
        Ext.getCmp(
          "tabpanelAssociatePartnerEnquiry" + Application.Crm_ConsultingPartner.Cache.typeName
        ).setActiveTab(0);
        Application.Crm_ConsultingPartner.Cache.aaId = ID;
        Application.Crm_ConsultingPartner.ViewModeEnquiry(ID);
      } else {
        Application.Crm_ConsultingPartner.Cache.aaId = 0;
        Application.Crm_ConsultingPartner.ViewModeEnquiry(0);
      }
    };
    var panelAPEnquiry = function (typeName) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey +
        "&typeName=" +
        typeName;
      var panel = new Ext.TabPanel({
        region: "east",
        frame: false,
        activeTab: 0,
        tabPosition: "top",
        border: true,
        bodyStyle: {
          "background-color": "white",
        },
        id: "tabpanelAssociatePartnerEnquiry" + typeName,
        width: winsize.width * 0.4,
        height: winsize.height * 0.6,
        defaults: {
          layout: "fit",
          autoScroll: true,
          frame: false,
        },
        items: [
          {
            title: "View Details",
            id: "tabMarketingAddEnquiry" + typeName,
            layout: "border",
            items: [tableEnquiryDetails()],
          },
          {
            title: "Log",
            id: "tabMarketingCommunicationEnquiry" + typeName,
            layout: "border",
            items: [communicationLogDetails('Enquiry')],
          }
        ],
        fbar: [],
        listeners: {
          afterrender: function (component) {},
          tabchange: function (s, tab) {
            switch (tab.title) {
              case "Log":
                Ext.getCmp('gridCommunicationListEnquiry').getStore().load({
                  params:{
                    aaId: Application.Crm_ConsultingPartner.Cache.aaId
                  }
                });
                break;
            }
          },
        },
      });
      return panel;
    };
    var tableEnquiryDetails = function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey;
      var enquiryPanel = new Ext.Panel({
        id: "tableCrmEnquiryDataview" + Application.Crm_ConsultingPartner.Cache.typeName,
        region: "center",
        width: winsize.width * 0.39,
        items: [
          {
            region: "center",
            defaults: {
              frame: false,
            },
            html:
              '<iframe id="downloadAPEnquiryIframe' +
              Application.Crm_ConsultingPartner.Cache.typeName +
              '" name="downloadAPEnquiryIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
              src +
              '"; ></iframe>',
          },
        ],
      });
      return enquiryPanel;
    };
    var associatePartnerLeadPanel = function (id, typeName) {
      var leadsPanel = new Ext.Panel({
        layout: "border",
        border: false,
        frame: false,
        bodyStyle: { "background-color": "white" },
        hideBorders: true,
        title: "Consulting Partner Lead",
        id: id,
        items: [gridAPLeadDetails(typeName), panelAPLead(typeName)],
      });
      return leadsPanel;
    };
    var leadGridStore = function (typeName) {
      return new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listLeadDetails",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "",
            root: "data",
          },
          [
            "aaId",
            "aaName",
            "aaMobile",
            "aaEmail",
            "businessVertical",
            "professionId","professionName",
            "businessVertical",
            "organisationName",
            "organisationType",
            "gstNumber",
            "businessLocation",
            "locationGlatitude",
            "locationGlongitude",
            "createdBy","createdByName","stageName",
            "createdOn",
            "updatedBy",
            "updatedOn",
          ]
        ),
        groupField: "",
        sortInfo: {
          field: "aaId",
          direction: "ASC",
        },
        root: "data",
        autoLoad: true,
        listeners: {
          load: function (store, record, options) {
            if (record.length > 0) {
              Ext.getCmp("gridAssociatePartnerLead" + typeName)
                .getSelectionModel()
                .selectRow(0);
            }
          },
          beforeload: function (store, e) {
            this.baseParams.typeName = typeName;
            this.baseParams.type = Application.Crm_ConsultingPartner.Cache.type;
          },
        },
      });
    };
    var gridAPLeadDetails = function (typeName) {
      var _jsonStoreLeadGrid = leadGridStore(typeName);
      var _leadGridFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "aaName",
          },
          {
            type: "string",
            dataIndex: "aaMobile",
          },
          {
            type: "string",
            dataIndex: "aaEmail",
          },
        ],
      });
      _leadGridFilter.remote = true;
      _leadGridFilter.autoReload = true;
      var leadGrid = new Ext.grid.GridPanel({
        id: "gridAssociatePartnerLead" + typeName,
        layout: "fit",
        store: _jsonStoreLeadGrid,
        region: "center",
        frame: true,
        border: false,
        plugins: [_leadGridFilter],
        loadMask: true,
        columns: [
          {
            header: "Name",
            dataIndex: "aaName",
            sortable: true,
            width: 200,
          },
          {
            header: "Mobile",
            dataIndex: "aaMobile",
            sortable: true,
          },
          {
            header: "Email",
            dataIndex: "aaEmail",
            sortable: true,
            width: 200,
          },
          {
            header: "Profession",
            dataIndex: "professionName",
            sortable: true,
          },          
          {
            header: "Created By",
            dataIndex: "createdByName",
            sortable: true,
            hideable: true,
          },{
            header: "Created On",
            dataIndex: "createdOn",
            sortable: true,
          },{
            header: "Stage",
            dataIndex: "stageName",
            sortable: true,
          },  
          {
            xtype: "actioncolumn",
            header: "Action",
            hideable: false,
            iconCls: "downarrow",
            tooltip: "Choose Actions",
            listeners: {
              click: function (a, grid, rowindex, e) {
                var record = grid.store.getAt(rowindex);
                grid.getSelectionModel().selectRow(rowindex);
                aaLeadActionMenu(e, typeName);
                //action
              },
            },
          },
        ],
        tbar: [
        ],
        viewConfig: {
          forceFit: true,
        },
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          getRowClass: function (record, index, params, store) {},
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: _jsonStoreLeadGrid,
          displayInfo: true,
          displayMsg: "Displaying items {0} - {1} of {2}",
          emptyMsg: "No records to display",
          items: [,],
        }),
        stripeRows: true,
        sm: new Ext.grid.RowSelectionModel({
          singleSelected: true,
          listeners: {
            selectionchange: gridSelectionChangedaaLead,
          },
        }),
        listeners: {
          afterrender: function () {
            _jsonStoreLeadGrid.load();
          },
          cellclick: function (grid, rowIndex, columnIndex, e) {},
          resize: onGridResize,
        },
      });
      return leadGrid;
    };
    var gridSelectionChangedaaLead = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp(
            "gridAssociatePartnerLead" + Application.Crm_ConsultingPartner.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()
        )
      ) {
        var ID = Ext.getCmp(
          "gridAssociatePartnerLead" + Application.Crm_ConsultingPartner.Cache.typeName
        )
          .getSelectionModel()
          .getSelections()[0].data.aaId;
        Ext.getCmp(
          "tabpanelAssociatePartnerLead" + Application.Crm_ConsultingPartner.Cache.typeName
        ).setActiveTab(0);
        Application.Crm_ConsultingPartner.Cache.aaId = ID;
        Application.Crm_ConsultingPartner.ViewModeLead(ID);
      } else {
        Application.Crm_ConsultingPartner.Cache.aaId = 0;
        Application.Crm_ConsultingPartner.ViewModeLead(0);
      }
    };
    var panelAPLead = function (typeName) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey +
        "&typeName=" +
        typeName;
      var panel = new Ext.TabPanel({
        region: "east",
        frame: false,
        activeTab: 0,
        tabPosition: "top",
        border: true,
        bodyStyle: {
          "background-color": "white",
        },
        id: "tabpanelAssociatePartnerLead" + typeName,
        width: winsize.width * 0.4,
        height: winsize.height * 0.6,
        defaults: {
          layout: "fit",
          autoScroll: true,
          frame: false,
        },
        items: [
          {
            title: "View Details",
            id: "tabMarketingAddLead" + typeName,
            layout: "border",
            items: [tableLeadDetails()],
          },{
            title: "Log",
            id: "tabMarketingCommunicationLead" + typeName,
            layout: "border",
            items: [communicationLogDetails('Lead')],
          }
        ],
        fbar: [],
        listeners: {
          afterrender: function (component) {},
          tabchange: function (s, tab) {
            switch (tab.title) {
              case "Log":
                Ext.getCmp('gridCommunicationListLead').getStore().load({
                  params:{
                    aaId: Application.Crm_ConsultingPartner.Cache.aaId
                  }
                });
                break;
            }
          },
        },
      });
      return panel;
    };
    var tableLeadDetails = function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey;
      var leadPanel = new Ext.Panel({
        id: "tableCrmLeadDataview" + Application.Crm_ConsultingPartner.Cache.typeName,
        region: "center",
        width: winsize.width * 0.39,
        items: [
          {
            region: "center",
            defaults: {
              frame: false,
            },
            html:
              '<iframe id="downloadAPLeadIframe' +
              Application.Crm_ConsultingPartner.Cache.typeName +
              '" name="downloadAPLeadIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
              src +
              '"; ></iframe>',
          },
        ],
      });
      return leadPanel;
    };
    var aaLeadActionMenu = function (e, typeName) {
      var aaEnquiryActionMenu = new Ext.menu.Menu({
        items: [          
          {
            text: "Update Lead Stages",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerLead" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                upgradeLeadStages(aaId);
            },
          },{
            text: "Upgrade to Prospect",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerLead" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                Ext.MessageBox.confirm('Confirm', 'Do you want to move this as prospect?', function (btn, text) {
                  if (btn == 'yes') {
                    upgradToProspect(aaId);
                  }
              });
            },
          }
        ],
      });
      aaEnquiryActionMenu.showAt(e.getXY());
    };
    var leadStageStore = function () {
      var store = new Ext.data.JsonStore({
        url: modURL + "&op=getCrmLeadStage",
        method: "post",
        autoLoad: true,
        fields: ["id", "name"],
        root: "data",
      });
      return store;
    };
    var upgradeLeadStages = function (id) {
      var storeCrmStatus = leadStageStore();
      var cpfrmLeadWindow = new Ext.Window({
        id: "windowToUpgradeLead",
        iconCls: "vender-items",
        shadow: false,
        width: winsize.width * 0.4,
        height: winsize.height * 0.4,
        title: "Update Lead Stages",
        layout: "fit",
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        items: [
          new Ext.FormPanel({
            layout: "form",
            id:
              "upgradeLeadStageFormPanel" + Application.Crm_ConsultingPartner.Cache.typeName,
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
                        store: storeCrmStatus,
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
        fbar: [
          {
            text: "Cancel",
            anchor: "90%",
            columnWidth: 0.1,
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            tabIndex: 33,
            handler: function () {
              cpfrmLeadWindow.close();
            },
          },
          {
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            text: "Save",
            handler: function () {
              var crmStatus = Ext.getCmp("crmStatus").getValue();
              var crmFollowupDate = Ext.getCmp("crmFollowupDate").getValue();
              var crmRemarks = Ext.getCmp("crmRemarks").getValue();
              if (crmStatus > 0) {
                Ext.Ajax.request({
                  waitMsg: "Processing",
                  method: "POST",
                  url: modURL + "&op=upgradeLeadStages",
                  params: {
                    leadId: id,
                    crmStatus: crmStatus,
                    crmFollowupDate: crmFollowupDate,
                    crmRemarks: crmRemarks,
                  },
                  success: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    if (tmp.success === true) {
                      Ext.getCmp(
                        "gridAssociatePartnerLead" +
                          Application.Crm_ConsultingPartner.Cache.typeName
                      )
                        .getStore()
                        .load({
                          callback: function (record, options, success) {
                            cpfrmLeadWindow.close();
                            Application.example.msg("Notification", tmp.msg);
                            var gridPanel = Ext.getCmp(
                              "gridAssociatePartnerLead" +
                                Application.Crm_ConsultingPartner.Cache.typeName
                            );
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
              } else {
                Ext.MessageBox.alert("Error", "Choose status and proceed.");
              }
            },
          },
        ],
        listeners: {
          load: function () {},
        },
      });
  
      cpfrmLeadWindow.doLayout();
      cpfrmLeadWindow.show();
      cpfrmLeadWindow.center();
    };
    var upgradToProspect = function (aaId) {
      Ext.Ajax.request({
        waitMsg: "Processing",
        method: "POST",
        url: modURL + "&op=upgradeLeadStages",
        params: {
          leadId: aaId,
          crmStatus: 4,
          crmFollowupDate: new Date(),
          crmRemarks: "Converted to Prospect",
        },
        success: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          if (tmp.success === true) {
            Ext.getCmp(
              "gridAssociatePartnerLead" + Application.Crm_ConsultingPartner.Cache.typeName
            )
              .getStore()
              .load({
                callback: function (record, options, success) {
                  cpfrmLeadWindow.close();
                  Application.example.msg("Notification", tmp.msg);
                  var gridPanel = Ext.getCmp(
                    "gridAssociatePartnerLead" +
                      Application.Crm_ConsultingPartner.Cache.typeName
                  );
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
    };
    var associatePartnerProspectPanel = function (id, typeName) {
      var prospectsPanel = new Ext.Panel({
        layout: "border",
        border: false,
        frame: false,
        bodyStyle: { "background-color": "white" },
        hideBorders: true,
        title: "Consulting Partner Prospect",
        id: id,
        items: [gridAPProspectDetails(typeName), panelAPProspect(typeName)],
      });
      return prospectsPanel;
    };
    var prospectGridStore = function (typeName) {
      return new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listProspectDetails",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "",
            root: "data",
          },
          [
            "aaId",
            "aaName",
            "aaMobile",
            "aaEmail",
            "businessVertical",
            "professionId","professionName",
            "businessVertical",
            "organisationName",
            "organisationType",
            "gstNumber",
            "businessLocation",
            "locationGlatitude",
            "locationGlongitude",
            "createdBy","createdByName","stageName","stageId",
            "createdOn",
            "updatedBy",
            "updatedOn",
          ]
        ),
        groupField: "",
        sortInfo: {
          field: "aaId",
          direction: "ASC",
        },
        root: "data",
        autoLoad: true,
        listeners: {
          load: function (store, record, options) {
            if (record.length > 0) {
              Ext.getCmp("gridAssociatePartnerProspect" + typeName)
                .getSelectionModel()
                .selectRow(0);
            }
          },
          beforeload: function (store, e) {
            this.baseParams.typeName = typeName;
            this.baseParams.type = Application.Crm_ConsultingPartner.Cache.type;
          },
        },
      });
    };
    var gridAPProspectDetails = function (typeName) {
      var _jsonStoreProspectGrid = prospectGridStore(typeName);
      var _prospectGridFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "aaName",
          },
          {
            type: "string",
            dataIndex: "aaMobile",
          },
          {
            type: "string",
            dataIndex: "aaEmail",
          },
        ],
      });
      _prospectGridFilter.remote = true;
      _prospectGridFilter.autoReload = true;
      var prospectGrid = new Ext.grid.GridPanel({
        id: "gridAssociatePartnerProspect" + typeName,
        layout: "fit",
        store: _jsonStoreProspectGrid,
        region: "center",
        frame: true,
        border: false,
        plugins: [_prospectGridFilter],
        loadMask: true,
        columns: [
          {
            header: "Name",
            dataIndex: "aaName",
            sortable: true,
            width: 200,
          },
          {
            header: "Mobile",
            dataIndex: "aaMobile",
            sortable: true,
          },
          {
            header: "Email",
            dataIndex: "aaEmail",
            sortable: true,
            width: 200,
          },
          {
            header: "Profession",
            dataIndex: "professionName",
            sortable: true,
          },          
          {
            header: "Created By",
            dataIndex: "createdByName",
            sortable: true,
            hideable: true,
          },{
            header: "Created On",
            dataIndex: "createdOn",
            sortable: true,
          },{
            header: "Stage",
            dataIndex: "stageName",
            sortable: true,
          },
          {
            xtype: "actioncolumn",
            header: "Action",
            hideable: false,
            iconCls: "downarrow",
            tooltip: "Choose Actions",
            listeners: {
              click: function (a, grid, rowindex, e) {
                var record = grid.store.getAt(rowindex);
                grid.getSelectionModel().selectRow(rowindex);
                aaProspectActionMenu(e, typeName);
                //action
              },
            },
          },
        ],
        tbar: [
          {
            xtype: "button",
            text: "Create Prospect",
            tooltip: "Create Prospect",
            iconCls: "finascop_add",
            handler: function () {
              APProspectForm(0,0);
            },
          },
        ],
        viewConfig: {
          forceFit: true,
        },
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          getRowClass: function (record, index, params, store) {},
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: _jsonStoreProspectGrid,
          displayInfo: true,
          displayMsg: "Displaying items {0} - {1} of {2}",
          emptyMsg: "No records to display",
          items: [,],
        }),
        stripeRows: true,
        sm: new Ext.grid.RowSelectionModel({
          singleSelected: true,
          listeners: {
            selectionchange: gridSelectionChangedaaProspect,
          },
        }),
        listeners: {
          afterrender: function () {
            _jsonStoreProspectGrid.load();
          },
          cellclick: function (grid, rowIndex, columnIndex, e) {},
          resize: onGridResize,
        },
      });
      return prospectGrid;
    };
    var gridSelectionChangedaaProspect = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp(
            "gridAssociatePartnerProspect" + Application.Crm_ConsultingPartner.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()
        )
      ) {
        var ID = Ext.getCmp(
          "gridAssociatePartnerProspect" + Application.Crm_ConsultingPartner.Cache.typeName
        )
          .getSelectionModel()
          .getSelections()[0].data.aaId;
        Ext.getCmp(
          "tabpanelAssociatePartnerProspect" + Application.Crm_ConsultingPartner.Cache.typeName
        ).setActiveTab(0);
        Application.Crm_ConsultingPartner.Cache.aaId = ID;
        Application.Crm_ConsultingPartner.ViewModeProspect(ID);
      } else {
        Application.Crm_ConsultingPartner.Cache.aaId = 0;
        Application.Crm_ConsultingPartner.ViewModeProspect(0);
      }
    };
    var panelAPProspect = function (typeName) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey +
        "&typeName=" +
        typeName;
      var panel = new Ext.TabPanel({
        region: "east",
        frame: false,
        activeTab: 0,
        tabPosition: "top",
        border: true,
        bodyStyle: {
          "background-color": "white",
        },
        id: "tabpanelAssociatePartnerProspect" + typeName,
        width: winsize.width * 0.4,
        height: winsize.height * 0.6,
        defaults: {
          layout: "fit",
          autoScroll: true,
          frame: false,
        },
        items: [
          {
            title: "View Details",
            id: "tabMarketingAddProspect" + typeName,
            layout: "border",
            items: [tableProspectDetails()],
          },{
            title: "Log",
            id: "tabMarketingCommunicationProspect" + typeName,
            layout: "border",
            items: [communicationLogDetails('Prospect')],
          }
        ],
        fbar: [],
        listeners: {
          afterrender: function (component) {},
          tabchange: function (s, tab) {
            switch (tab.title) {
              case "Log":
                Ext.getCmp('gridCommunicationListProspect').getStore().load({
                  params:{
                    aaId: Application.Crm_ConsultingPartner.Cache.aaId
                  }
                });
                break;
            }
          },
        },
      });
      return panel;
    };
    var tableProspectDetails = function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey;
      var prospectPanel = new Ext.Panel({
        id: "tableCrmProspectDataview" + Application.Crm_ConsultingPartner.Cache.typeName,
        region: "center",
        width: winsize.width * 0.39,
        items: [
          {
            region: "center",
            defaults: {
              frame: false,
            },
            html:
              '<iframe id="downloadAPProspectIframe' +
              Application.Crm_ConsultingPartner.Cache.typeName +
              '" name="downloadAPProspectIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
              src +
              '"; ></iframe>',
          },
        ],
      });
      return prospectPanel;
    };
    var aaProspectActionMenu = function (e, typeName) {
      var aaEnquiryActionMenu = new Ext.menu.Menu({
        items: [          
          {
            text: "Approve Prospect",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerProspect" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                  //Application.Crm_ConsultingPartner.approveProspect(aaId,typeName);   
                  Ext.MessageBox.confirm('Confirm', 'Do you want to Approve Prospect?', function (btn, text) {
                    if (btn == 'yes') {
                      APProspectForm(aaId,1);
                      
                    }
                });
                             
                
            }
          },{
            text: "Update Prospect Stages",
            hidden:true,
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerProspect" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                upgradeProspectStages(aaId);
            }
          },
        ],
      });
      aaEnquiryActionMenu.showAt(e.getXY());
    };
    var APProspectForm = function (prospectid,approve) {
      var preferedAreaStore = apPreferredArea();
      var apProffessionStore = apProffession();
      var _state_store = stateStoreEdit();
      var district_store = districtStoreEdit();
      var APProspectForm = new Ext.Window({
        width: 500,
        autoHeight: true,
        id: "add_prospectWindow" + Application.Crm_ConsultingPartner.Cache.typeName,
        shadow: false,
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        closable: false,
        items: [
          new Ext.form.FormPanel({
            id: "formpanelAddProspect" + Application.Crm_ConsultingPartner.Cache.typeName,
            title: !Ext.isEmpty(prospectid) ? "Edit Prospect" : "Create Prospect",
            bodyStyle: {
              "background-color": "white",
              padding: "5px 5px 59px 10px",
            },
            labelAlign: "top",
            items: [
              {
                layout: "column",
                border: false,
                items: [
                  {
                    layout: "form",
                    border: false,
                    columnWidth: 0.5,
                    items: [
                      {
                        xtype: "textfield",
                        id: "aaId",
                        hidden: true,
                      },                      
                      {
                        layout: "column",
                        fieldLabel: "Name",
                        maxLength: 250,
                        tabIndex: 300,
                        xtype: "textfield",
                        anchor: "98%",
                        allowBlank: false,
                        id: "aaName",
                        name: "aaName",
                        hideBorders: true,
                        border: false,
                      }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        layout: "column",
                        fieldLabel: "Mobile",
                        maxLength: 250,
                        tabIndex: 300,
                        xtype: "textfield",
                        anchor: "98%",
                        allowBlank: false,
                        id: "aaMobile",
                        name: "aaMobile",
                        hideBorders: true,
                        border: false,
                      }
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        layout: "column",
                        fieldLabel: "Email",
                        maxLength: 250,
                        tabIndex: 300,
                        xtype: "textfield",
                        anchor: "98%",
                        allowBlank: false,
                        id: "aaEmail",
                        name: "aaEmail",
                        hideBorders: true,
                        border: false,
                      }
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        xtype: "combo",
                        fieldLabel: "Proffession",
                        emptyText: "Proffession",
                        id: "professionId",
                        name: "professionId",
                        allowBlank: false,
                        tabIndex: 301,
                        labelStyle: mandatory_label,
                        mode: "local",
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        anchor: "98%",
                        store: apProffessionStore,
                        triggerAction: "all",
                        minChars: 2,
                        displayField: "name",
                        valueField: "id",
                        hiddenName: "professionId",
                        listeners: {
                          select: function () {
                            var professionId = Ext.getCmp('professionId').getValue();
                            /*if(professionId == 1){
                                Ext.getCmp('businessVertical').show();
                                Ext.getCmp('organisationName').show();
                                Ext.getCmp('organisationType').show();
                                Ext.getCmp('gstNumber').show();
                            }else{
                                Ext.getCmp('businessVertical').hide();
                                Ext.getCmp('organisationName').hide();
                                Ext.getCmp('organisationType').hide();
                                Ext.getCmp('gstNumber').hide();
                            }*/
                          },
                        },
                      }
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "PAN",
                        tabIndex: 305,
                        id: "panNumber",
                        name: "panNumber",
                        anchor: "98%",
                        maxLength: 100,
                      },
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: GST+" Number",
                        tabIndex: 303,
                        id: "gstNumber",
                        name: "gstNumber",
                        anchor: "98%",
                        maxLength: 100,
                      },                      
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "Business Vertical",
                        tabIndex: 305,
                        id: "businessVertical",
                        name: "businessVertical",
                        anchor: "98%",
                        maxLength: 100,
                        hidden:true
                      },
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "Organisation Name",
                        tabIndex: 306,
                        id: "organisationName",
                        name: "organisationName",
                        anchor: "98%",
                        maxLength: 100,
                        hidden:true
                      },
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "Type of Organisation",
                        tabIndex: 307,
                        id: "organisationType",
                        name: "organisationType",
                        anchor: "98%",
                        maxLength: 100,
                        hidden:true
                      },
                    ],
                  },                  
                  {
                    layout: "form",
                    columnWidth: 1,
                    border: false,
                    items: [
                      {
                        xtype: "textfield",
                        fieldLabel: "Business Location",
                        tabIndex: 302,
                        id: "businessLocation",
                        name: "businessLocation",
                        anchor: "98%",
                        maxLength: 1000,
                        listeners: {
                          focus: function () {
                            initAutocompleteText();
                          },
                        },
                      },{
                        xtype: "textfield",
                        fieldLabel: "Country",
                        tabIndex: 303,
                        id: "locationCountry",
                        name: "locationCountry",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Route",
                        tabIndex: 303,
                        id: "locationGroute",
                        name: "locationGroute",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Locality",
                        tabIndex: 303,
                        id: "locationGlocality",
                        name: "locationGlocality",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Place",
                        tabIndex: 303,
                        id: "locationGplace",
                        name: "locationGplace",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Latitude",
                        tabIndex: 303,
                        id: "locationGlatitude",
                        name: "locationGlatitude",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      },
                      {
                        xtype: "textfield",
                        fieldLabel: "Longitude",
                        tabIndex: 303,
                        id: "locationGlongitude",
                        name: "locationGlongitude",
                        anchor: "98%",
                        hidden: true,
                        maxLength: 100,
                      }
                    ],
                  },{
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [
                        {
                          hidden:true,
                          xtype: "lovcombo",
                          store: preferedAreaStore,
                          mode: "local",
                          id: "preferredArea",
                          allowBlank: true,
                          fieldLabel: "Area",
                          hiddenName: "preferredArea",
                          displayField: "areaName",
                          valueField: "id",
                          typeAhead: true,
                          anchor: "98%",
                          editable: true,
                          minChars: 2,
                          selectOnFocus: true,
                          triggerAction: "all",
                          lazyRender: true,
                          tabIndex: 35,                            
                            listeners: {
                              select: function () {
                                var preferredArea = Ext.getCmp("preferredArea").getValue();
                                var areasArray = preferredArea.split(",");
                                if (areasArray.includes("-1")) {
                                  Ext.getCmp("as_st_id").show();
                                  Ext.getCmp("as_dst_Id").show();
                                }else{
                                  Ext.getCmp("as_st_id").hide();
                                  Ext.getCmp("as_dst_Id").hide();
                                }
                                
                              },
                            }
                          }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        xtype: 'combo',
                        fieldLabel: 'State',
                        name: 'as_st_id',
                        id: 'as_st_id',
                        hiddenName: 'as_st_id',
                        anchor: '97%',
                        store: _state_store,
                        valueField: 'st_ID',
                        tabIndex: 403,
                        displayField: 'st_name',
                        forceSelection: true,
                        triggerAction: 'all',
                        typeAhead: true,
                        selectOnFocus: true,
                        mode: 'local',
                        listeners: {
                            select: function () {

                                var value = Ext.getCmp('as_st_id').getValue();
                                console.log("District value after dst", value, this.value);

                                district_store.baseParams.st_Id = this.value;
                                district_store.load();
                            }
                        }
                    }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 0.5,
                    border: false,
                    items: [{
                        xtype: 'combo',
                        fieldLabel: 'District',
                        id: 'as_dst_Id',
                        name: 'as_dst_Id',
                        tabIndex: 404,
                        hiddenName: 'as_dst_Id',
                        anchor: '97%',
                        store: district_store,
                        valueField: 'dst_ID',
                        displayField: 'dst_Name',
                        forceSelection: true,
                        triggerAction: 'all',
                        typeAhead: true,
                        selectOnFocus: true,
                        mode: 'local',
                    }
                    ],
                  },
                  {
                    layout: "form",
                    columnWidth: 1,
                    border: false,
                    items: [{
                    xtype: "textarea",
                    height: 50,
                    fieldLabel: "Address",
                    id: "cpAddress",
                    name: "cpAddress",
                    tabIndex: 405,
                    anchor: "99%",
                    allowBlank: false,
                    listeners: {
                      
                    },
                  }],
                  }
                ],
              },
            ],
          }),
        ],
        fbar: [
          {
            text: "Cancel",
            anchor: "90%",
            columnWidth: 0.1,
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            tabIndex: 311,
            handler: function () {
              Ext.getCmp("gridAssociatePartnerProspect" +Application.Crm_ConsultingPartner.Cache.typeName).getStore().load();
              APProspectForm.close();
            },
          },
          {
            text: "Save",
            anchor: "95%",
            columnWidth: 0.1,
            tabIndex: 310,
            bodyStyle: { margin: "0px 0px 0px 140px" },
            xtype: "button",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            tabIndex: 304,
            handler: function () {
              var areaAssociateId = Ext.getCmp("aaId").getValue();
              var _addProspectData = Ext.getCmp(
                "formpanelAddProspect" + Application.Crm_ConsultingPartner.Cache.typeName
              ); 
              prospectInsertion(areaAssociateId,approve);
            },
          },
        ],
      });
      if (prospectid > 0) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var regFee_form = Ext.getCmp(
          "formpanelAddProspect" + Application.Crm_ConsultingPartner.Cache.typeName
        ).getForm();
        regFee_form.load({
          params: {
            EditStatus: 1,
            _edit_aaId: prospectid,
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=loadEditData",
          waitMsg: "Loading...",
          success: function (form, action) {
            eval("var tmp=" + action.response.responseText);
            var tmp = Ext.decode(action.response.responseText);
            console.log('formload');
            console.log(tmp);
            Ext.getCmp('professionId').getStore().load();
            Ext.getCmp('professionId').setValue(tmp.data.professionId); 
            Ext.getCmp('professionId').setRawValue(tmp.data.professionName);
            
            Ext.getCmp('as_st_id').getStore().load();
            Ext.getCmp('as_st_id').setValue(tmp.data.as_st_id); 
            Ext.getCmp('as_st_id').setRawValue(tmp.data.stateName);
            
            Ext.getCmp('as_dst_Id').getStore().baseParams.st_Id = tmp.data.as_st_id;
            Ext.getCmp('as_dst_Id').getStore().load();
            Ext.getCmp('as_dst_Id').setValue(tmp.data.as_dst_Id); 
            Ext.getCmp('as_dst_Id').setRawValue(tmp.data.distritName);
          },
          failure: function (form, action) {},
        });
      } else {
      }
      APProspectForm.doLayout();
      APProspectForm.show();
      APProspectForm.center();
      return APProspectForm;
    };
    var prospectInsertion = function (areaAssociateId,approve) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var _addeditform = Ext.getCmp(
        "formpanelAddProspect" + Application.Crm_ConsultingPartner.Cache.typeName
      );
      if (areaAssociateId > 0) {
        areaAssociateId = areaAssociateId;
        Application.Crm_ConsultingPartner.Cache.upcrocid = areaAssociateId;
      } else {
        areaAssociateId = "-";
        Application.Crm_ConsultingPartner.Cache.upcrocid = 0;
      }
      if (_addeditform.getForm().isValid()) {
        Application.Crm_ConsultingPartner.insertProspectData(areaAssociateId,approve);
      } else {
        Ext.MessageBox.alert("Notification", "Please enter all required fields");
      }
    }; 
    var mapAreatoAreaAssociateGrid = function(aaId){
      var ck_selection = ckSelection();
      var _aaProspectAreaStore = areaStore(aaId);
      var _aaProspectAreaGridPanel = new Ext.grid.GridPanel({
        region: "center",
        layout: "fit",
        frame: false,
        border: false,
        loadMask: true,
        store: _aaProspectAreaStore,
        autoScroll: true,
        width: 400,
        height: 250,
        bodyStyle: { "background-color": "white" },
        id: "gridpanelProspectareas",
        sm: ck_selection,
        columns: [
          ck_selection,
          {
            header: "Area",
            dataIndex: "areaName",
            sortable: true,
            tooltip: "Area",
            hideable: true,
          },
          {
            header: "Location",
            dataIndex: "areaLocation",
            sortable: true,
            tooltip: "Location",
            hideable: true,
          },{
            header: "Distance",
            dataIndex: "distance",
            sortable: true,
            tooltip: "Distance",
            hideable: true,
          },{
            header: "Assigned",
            dataIndex: "assigned",
            sortable: true,
            tooltip: "Assigned",
            hideable: true,
          }
        ],
        viewConfig: {
          forceFit: true,
          getRowClass: function (record, index, params, store) {},
        },
        tbar: [
        ],
        listeners: {
          afterrender: function () {
            {
              var me = this;
              _aaProspectAreaStore.on("load", function () {
                var data = me.getStore().data.items;
                var recs = [];
                Ext.each(data, function (item, index) {
                  if (item.data.checked == 1) {
                    recs.push(index);
                  }
                });
                me.getSelectionModel().selectRows(recs);
              });
            }
          },
        }
      });
      return _aaProspectAreaGridPanel;
    };
    var areaStore = function (aaId) {
      var _Store = new Ext.data.JsonStore({
        method: "post",
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listAreaForProspect",
          method: "post",
        }),
        fields: ["id","areaName","areaLocation","areaLatitude","distance","checked","areaConsultingPartnerFinal","assigned"],
        totalProperty: "totalCount",
        root: "data",
        remoteSort: true,
        autoLoad: true,
        listeners: {
          beforeload: function () {
            this.baseParams.aaId = aaId;
          },
          load: function () {
          },
        },
      });
      return _Store;
    };
    var areaAssteId = new Array();
    var ckSelection = function () {
      return new Ext.grid.CheckboxSelectionModel({
          multiSelect: true,
          checkOnly: true,
          listeners: {
              rowdeselect: function (sm, rowIndex, record) {
                  var ind = areaAssteId.indexOf(record.get('id'));                  
                  if (ind == -1) {
                      record.set('checked', 'false');                      
                      //Application.Crm_ConsultingPartner.Cache.isAssigned -= parseInt(record.get('areaConsultingPartnerFinal'));
                  }
              },
              rowselect: function (sm, rowIndex, record) {
                  var ind = areaAssteId.indexOf(record.get('id'));                  
                  if (ind == -1) {
                    //Application.Crm_ConsultingPartner.Cache.isAssigned -= parseInt(record.get('areaConsultingPartnerFinal'));
                  }
                  record.set('checked', 'true');
              }
          }
      });
  };
  var prospectStageStore = function(){
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getCrmProspectStage",
      method: "post",
      autoLoad: true,
      fields: ["id", "name"],
      root: "data",
    });
    return store;
  };
  var upgradeProspectStages = function (id) {
    var storeCrmStatus = prospectStageStore();
    fileS3BucketView();
    var cpfrmProspectWindow = new Ext.Window({
      id: "windowToUpgradeProspect",
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
          id:"upgradeProspectStageFormPanel" + Application.Crm_ConsultingPartner.Cache.typeName,
          height: 150,
          fileUpload: true,
          columnWidth: 1,
          frame: true,
          border: true,
          items: [
            {
              xtype: "panel",
              layout: "column",
              items: [
                {
                  xtype: 'hidden',
                  id: 's3_filename',
                  name: 's3_filename'
              }, {
                  xtype: 'hidden',
                  id: 's3_albumBucketName',
                  name: 's3_albumBucketName'
              }, {
                  xtype: 'hidden',
                  id: 's3_accessKey',
                  name: 's3_accessKey'
              }, {
                  xtype: 'hidden',
                  id: 's3_secretKey',
                  name: 's3_secretKey'
              }, {
                  xtype: 'hidden',
                  id: 's3_bucketRegion',
                  name: 's3_bucketRegion'
              },
              {
                  xtype: 'hidden',
                  id: 's3_oncompleteurl',
                  name: 's3_oncompleteurl'
              },
              {
                  xtype: 'hidden',
                  id: 's3_img_path_db',
                  name: 's3_img_path_db'
              },{
                xtype: 'hidden',
                id: 's3_bucketFolder',
                name: 's3_bucketFolder'
            },
              {
                  xtype: 'hidden',
                  id: 's3filepath',
                  name: 's3filepath'
              },
                {
                  columnWidth: 0.5,
                  layout: "form",
                  frame: false,
                  border: false,
                  labelAlign: "top",
                  items: [
                    {
                      xtype: "combo",
                      store: storeCrmStatus,
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
                          var prospectStage  = Ext.getCmp('crmStatus').getValue();
                          if(prospectStage == 9){
                            Ext.getCmp('fileuploadfieldAttachFileCustomers').show();
                          }else{
                            Ext.getCmp('fileuploadfieldAttachFileCustomers').hide();
                          }
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
                {
                  columnWidth: .16,
                  border: false,
                  buttons: [
                      {
                          xtype: 'fileuploadfield',
                          hidden:true,
                          style: 'margin-bottom:5px;',
                          id: 'fileuploadfieldAttachFileCustomers',
                          border: false,
                          anchor: '97%',
                          name: 'fileuploadfieldAttachFileCustomers',
                          allowBlank: true,
                          buttonOnly: true,
                          buttonCfg: {
                              text: 'Upload File',
                              border: false,
                              width: 100
                          },
                          validator: function (v) {
                              if (v != '')
                              {
                                  v = v.toLowerCase();
                                  var exp = /^.*\.(png|jpg|gif|pdf|docx)$/i;
                                  if (!(exp.test(v)))
                                  {
                                      return 'Upload a valid image file of format JPG.';
                                  }

                                  var associated_file = Ext.getCmp('fileuploadfieldAttachFileCustomers').getValue();
                                  if (associated_file == '')
                                  {
                                      Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                      return;
                                  }
                                  addFile();
                                  return true;
                              }
                          }
                      }
                  ]
              }, {
                  columnWidth: 1,
                  layout: 'form',
                  border: false,
                  items: [{xtype: 'displayfield', id: 'supporting', cls: 'fpcls', value: 'File uploaded successfully', hidden: true}]
              }
              ],
            },
          ],
        }),
      ],
      fbar: [
        {
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
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          text: "Save",
          handler: function () {
            var crmStatus = Ext.getCmp("crmStatus").getValue();
            var crmFollowupDate = Ext.getCmp("crmFollowupDate").getValue();
            var crmRemarks = Ext.getCmp("crmRemarks").getValue();
            if (crmStatus > 0 && !Ext.isEmpty(crmFollowupDate)) {
              Ext.Ajax.request({
                waitMsg: "Processing",
                method: "POST",
                url: modURL + "&op=upgradeProspectStages",
                params: {
                  leadId: id,
                  crmStatus: crmStatus,
                  crmFollowupDate: crmFollowupDate,
                  crmRemarks: crmRemarks,
                },
                success: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp(
                      "gridAssociatePartnerProspect" +
                        Application.Crm_ConsultingPartner.Cache.typeName
                    )
                      .getStore()
                      .load({
                        callback: function (record, options, success) {
                          cpfrmProspectWindow.close();
                          Application.example.msg("Notification", tmp.msg);
                          var gridPanel = Ext.getCmp(
                            "gridAssociatePartnerProspect" +
                              Application.Crm_ConsultingPartner.Cache.typeName
                          );
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
            } else {
              Ext.MessageBox.alert("Error", "Choose status and proceed.");
            }
          },
        },
      ],
      listeners: {
        load: function () {},
      },
    });

    cpfrmProspectWindow.doLayout();
    cpfrmProspectWindow.show();
    cpfrmProspectWindow.center();
  };
  var uuidv4 = function () {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
};
  var fileS3BucketView = function () {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var uuid = uuidv4(); // for xxxx to field

    Ext.Ajax.request({
        url: modURL + '&op=get_file_s3_details',
        method: 'POST',
        params: {
            rid: uuid,
            apikey: _SESSION.apikey,
            tstamp: t_stamp

        },
        success: function (response) {
            var tmp = Ext.util.JSON.decode(response.responseText);
            if (tmp.success !== undefined && tmp.success === true)
            {

                Ext.getCmp('s3_accessKey').setValue(tmp.data.accessKey);
                Ext.getCmp('s3_albumBucketName').setValue(tmp.data.albumBucketName);
                Ext.getCmp('s3_secretKey').setValue(tmp.data.secretKey);
                Ext.getCmp('s3_bucketRegion').setValue(tmp.data.bucketRegion);
                Ext.getCmp('s3_bucketFolder').setValue(tmp.data.oncompleteurl);
            }
        },
        failure: function (response) {
            var tmp = Ext.util.JSON.decode(response.responseText);
            Ext.MessageBox.alert('Error', 'Issue in saving');
        }
    });
};
var addFile = function () {
  var albumBucketName = Ext.getCmp('s3_albumBucketName').getValue();
  var bucketRegion = Ext.getCmp('s3_bucketRegion').getValue();
  var filepath = Ext.getCmp("s3_bucketFolder").getValue();
  AWS.config.update({
      region: bucketRegion,
      credentials: new AWS.Credentials(
              Ext.getCmp('s3_accessKey').getValue(),
              Ext.getCmp('s3_secretKey').getValue(), null
              )
  });
  var s3 = new AWS.S3({
      apiVersion: '2006-03-01',
      params: {Bucket: albumBucketName}
  });
  var files = document.getElementById('fileuploadfieldAttachFileCustomers-file').files;
  if (!files.length)
  {
      return alert('Please choose a file to upload first.');
  }
  var file = files[0];
  var actualfileName = file.name;
  var file_Name = JSON.stringify(actualfileName).slice(1, -1);
  var fileExt = file_Name.split('.').pop();
  var fileName = uuidv4();
  fileName = fileName + '.' + fileExt;
  Ext.getCmp('s3_filename').setValue(fileName);
  s3.upload({
      Key: filepath + fileName,
      Body: file,
      ACL: 'public-read'
  },
  function (err, data) {

      if (err)
      {
          var img_src = Ext.BLANK_IMAGE_URL;
          return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
      }
      if (!Ext.isEmpty(data.Location))
      {
          Ext.MessageBox.show({
              msg: 'Uploading, Please wait...',
              progressText: '',
              width: 300,
              wait: true,
              waitConfig:
                      {
                          duration: 5000,
                          increment: 15,
                          text: 'Saving...',
                          scope: this,
                          fn: function () {
                              Ext.MessageBox.hide();
                              Application.example.msg('Notification', 'File uploaded successfully.');
                              Ext.getCmp('supporting').show();
                              Ext.getCmp('supporting').disable();
                          }
                      }
          });
          Ext.getCmp('s3filepath').setValue(data.Location);
      }
  });
};
var associatePartnerOppurtunityPanel = function (id, typeName) {
  var oppurtunitysPanel = new Ext.Panel({
    layout: "border",
    border: false,
    frame: false,
    bodyStyle: { "background-color": "white" },
    hideBorders: true,
    title: "Consulting Partner Oppurtunity",
    id: id,
    items: [gridAPOppurtunityDetails(typeName), panelAPOppurtunity(typeName)],
  });
  return oppurtunitysPanel;
};
var oppurtunityGridStore = function (typeName) {
  return new Ext.data.GroupingStore({
    proxy: new Ext.data.HttpProxy({
      url: modURL + "&op=listOppurtunityDetails",
      method: "post",
    }),
    reader: new Ext.data.JsonReader(
      {
        totalProperty: "totalCount",
        idProperty: "",
        root: "data",
      },
      [
        "aaId",
        "aaName",
        "aaMobile",
        "aaEmail",
        "businessVertical",
        "professionId","professionName",
        "businessVertical",
        "organisationName",
        "organisationType",
        "gstNumber",
        "businessLocation",
        "locationGlatitude",
        "locationGlongitude",
        "createdBy","createdByName","stageName","stageId",
        "createdOn",
        "updatedBy",
        "updatedOn",
      ]
    ),
    groupField: "",
    sortInfo: {
      field: "aaId",
      direction: "ASC",
    },
    root: "data",
    autoLoad: true,
    listeners: {
      load: function (store, record, options) {
        if (record.length > 0) {
          Ext.getCmp("gridAssociatePartnerOppurtunity" + typeName)
            .getSelectionModel()
            .selectRow(0);
        }
      },
      beforeload: function (store, e) {
        this.baseParams.typeName = typeName;
        this.baseParams.type = Application.Crm_ConsultingPartner.Cache.type;
      },
    },
  });
};
var gridAPOppurtunityDetails = function (typeName) {
  var _jsonStoreOppurtunityGrid = oppurtunityGridStore(typeName);
  var _oppurtunityGridFilter = new Ext.ux.grid.GridFilters({
    filters: [
      {
        type: "string",
        dataIndex: "aaName",
      },
      {
        type: "string",
        dataIndex: "aaMobile",
      },
      {
        type: "string",
        dataIndex: "aaEmail",
      },
    ],
  });
  _oppurtunityGridFilter.remote = true;
  _oppurtunityGridFilter.autoReload = true;
  var oppurtunityGrid = new Ext.grid.GridPanel({
    id: "gridAssociatePartnerOppurtunity" + typeName,
    layout: "fit",
    store: _jsonStoreOppurtunityGrid,
    region: "center",
    frame: true,
    border: false,
    plugins: [_oppurtunityGridFilter],
    loadMask: true,
    columns: [
      {
        header: "Name",
        dataIndex: "aaName",
        sortable: true,
        width: 200,
      },
      {
        header: "Mobile",
        dataIndex: "aaMobile",
        sortable: true,
      },
      {
        header: "Email",
        dataIndex: "aaEmail",
        sortable: true,
        width: 200,
      },
      {
        header: "Profession",
        dataIndex: "professionName",
        sortable: true,
      },          
      {
        header: "Created From",
        dataIndex: "createdByName",
        sortable: true,
        hideable: true,
      },{
        header: "Created On",
        dataIndex: "createdOn",
        sortable: true,
      }, {
        header: "Stage",
        dataIndex: "stageName",
        sortable: true,
      },
      {
        xtype: "actioncolumn",
        header: "Action",
        hideable: false,
        iconCls: "downarrow",
        tooltip: "Choose Actions",
        listeners: {
          click: function (a, grid, rowindex, e) {
            var record = grid.store.getAt(rowindex);
            grid.getSelectionModel().selectRow(rowindex);
            aaOppurtunityActionMenu(e, typeName);
            //action
          },
        },
      },
    ],
    tbar: [
    ],
    viewConfig: {
      forceFit: true,
    },
    view: new Ext.grid.GroupingView({
      forceFit: true,
      deferEmptyText: false,
      getRowClass: function (record, index, params, store) {},
      emptyText:
        '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      groupTextTpl:
        '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
    }),
    bbar: new Ext.PagingToolbar({
      pageSize: recs_per_page,
      store: _jsonStoreOppurtunityGrid,
      displayInfo: true,
      displayMsg: "Displaying items {0} - {1} of {2}",
      emptyMsg: "No records to display",
      items: [,],
    }),
    stripeRows: true,
    sm: new Ext.grid.RowSelectionModel({
      singleSelected: true,
      listeners: {
        selectionchange: gridSelectionChangedaaOppurtunity,
      },
    }),
    listeners: {
      afterrender: function () {
        _jsonStoreOppurtunityGrid.load();
      },
      cellclick: function (grid, rowIndex, columnIndex, e) {},
      resize: onGridResize,
    },
  });
  return oppurtunityGrid;
};
var gridAPOppurtunityDetails = function (typeName) {
      var _jsonStoreOppurtunityGrid = oppurtunityGridStore(typeName);
      var _oppurtunityGridFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "aaName",
          },
          {
            type: "string",
            dataIndex: "aaMobile",
          },
          {
            type: "string",
            dataIndex: "aaEmail",
          },
        ],
      });
      _oppurtunityGridFilter.remote = true;
      _oppurtunityGridFilter.autoReload = true;
      var oppurtunityGrid = new Ext.grid.GridPanel({
        id: "gridAssociatePartnerOppurtunity" + typeName,
        layout: "fit",
        store: _jsonStoreOppurtunityGrid,
        region: "center",
        frame: true,
        border: false,
        plugins: [_oppurtunityGridFilter],
        loadMask: true,
        columns: [
          {
            header: "Name",
            dataIndex: "aaName",
            sortable: true,
            width: 200,
          },
          {
            header: "Mobile",
            dataIndex: "aaMobile",
            sortable: true,
          },
          {
            header: "Email",
            dataIndex: "aaEmail",
            sortable: true,
            width: 200,
          },
          {
            header: "Profession",
            dataIndex: "professionName",
            sortable: true,
          },          
          {
            header: "Created By",
            dataIndex: "createdByName",
            sortable: true,
            hideable: true,
          },{
            header: "Created On",
            dataIndex: "createdOn",
            sortable: true,
          },{
            header: "Stage",
            dataIndex: "stageName",
            sortable: true,
          },
          {
            xtype: "actioncolumn",
            header: "Action",
            hideable: false,
            iconCls: "downarrow",
            tooltip: "Choose Actions",
            listeners: {
              click: function (a, grid, rowindex, e) {
                var record = grid.store.getAt(rowindex);
                grid.getSelectionModel().selectRow(rowindex);
                aaOppurtunityActionMenu(e, typeName);
                //action
              },
            },
          },
        ],
        tbar: [
        ],
        viewConfig: {
          forceFit: true,
        },
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          getRowClass: function (record, index, params, store) {},
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: _jsonStoreOppurtunityGrid,
          displayInfo: true,
          displayMsg: "Displaying items {0} - {1} of {2}",
          emptyMsg: "No records to display",
          items: [,],
        }),
        stripeRows: true,
        sm: new Ext.grid.RowSelectionModel({
          singleSelected: true,
          listeners: {
            selectionchange: gridSelectionChangedaaOppurtunity,
          },
        }),
        listeners: {
          afterrender: function () {
            _jsonStoreOppurtunityGrid.load();
          },
          cellclick: function (grid, rowIndex, columnIndex, e) {},
          resize: onGridResize,
        },
      });
      return oppurtunityGrid;
    };
    var gridSelectionChangedaaOppurtunity = function (sm) {
      if (
        !Ext.isEmpty(
          Ext.getCmp(
            "gridAssociatePartnerOppurtunity" + Application.Crm_ConsultingPartner.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()
        )
      ) {
        var ID = Ext.getCmp(
          "gridAssociatePartnerOppurtunity" + Application.Crm_ConsultingPartner.Cache.typeName
        )
          .getSelectionModel()
          .getSelections()[0].data.aaId;
        Ext.getCmp(
          "tabpanelAssociatePartnerOppurtunity" + Application.Crm_ConsultingPartner.Cache.typeName
        ).setActiveTab(0);
        Application.Crm_ConsultingPartner.Cache.aaId = ID;
        Application.Crm_ConsultingPartner.ViewModeOppurtunity(ID);
      } else {
        Application.Crm_ConsultingPartner.Cache.aaId = 0;
        Application.Crm_ConsultingPartner.ViewModeOppurtunity(0);
      }
    };
    var panelAPOppurtunity = function (typeName) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey +
        "&typeName=" +
        typeName;
      var panel = new Ext.TabPanel({
        region: "east",
        frame: false,
        activeTab: 0,
        tabPosition: "top",
        border: true,
        bodyStyle: {
          "background-color": "white",
        },
        id: "tabpanelAssociatePartnerOppurtunity" + typeName,
        width: winsize.width * 0.4,
        height: winsize.height * 0.6,
        defaults: {
          layout: "fit",
          autoScroll: true,
          frame: false,
        },
        items: [
          {
            title: "View Details",
            id: "tabMarketingAddOppurtunity" + typeName,
            layout: "border",
            items: [tableOppurtunityDetails()],
          },{
            title: "Log",
            id: "tabMarketingCommunicationOppurtunity" + typeName,
            layout: "border",
            items: [communicationLogDetails('Oppurtunity')],
          }
        ],
        fbar: [],
        listeners: {
          afterrender: function (component) {},
          tabchange: function (s, tab) {
            switch (tab.title) {
              case "Log":
                Ext.getCmp('gridCommunicationListOppurtunity').getStore().load({
                  params:{
                    aaId: Application.Crm_ConsultingPartner.Cache.aaId
                  }
                });
                break;
            }
          },
        },
      });
      return panel;
    };
    var tableOppurtunityDetails = function () {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      var src =
        "?module=crm_associate_partner&op=loadEditData&aaId=" +
        Application.Crm_ConsultingPartner.Cache.aaId +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey;
      var oppurtunityPanel = new Ext.Panel({
        id: "tableCrmOppurtunityDataview" + Application.Crm_ConsultingPartner.Cache.typeName,
        region: "center",
        width: winsize.width * 0.39,
        items: [
          {
            region: "center",
            defaults: {
              frame: false,
            },
            html:
              '<iframe id="downloadAPOppurtunityIframe' +
              Application.Crm_ConsultingPartner.Cache.typeName +
              '" name="downloadAPOppurtunityIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
              src +
              '"; ></iframe>',
          },
        ],
      });
      return oppurtunityPanel;
    };
    var aaOppurtunityActionMenu = function (e, typeName) {
      var aaEnquiryActionMenu = new Ext.menu.Menu({
        items: [          
          {
            text: "Approve Oppurtunity",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerOppurtunity" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                Application.Crm_ConsultingPartner.approveOppurtunity(aaId,typeName);
            }
          },{
            text: "Update Oppurtunity Stages",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerOppurtunity" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                upgradeOppurtunityStages(aaId);
            }
          },{
            text: "Move to Incubatee",
            handler: function () {
              var aaId = Ext.getCmp("gridAssociatePartnerOppurtunity" + typeName)
                .getSelectionModel()
                .getSelections()[0].data.aaId;
                Ext.MessageBox.confirm('Confirm', 'Did you create business associate for this Opportunity?', function (btn, text) {
                  if (btn == 'yes') {
                      moveToIncubatee(aaId,typeName);
                  }
              });
            }
          }
        ],
      });
      aaEnquiryActionMenu.showAt(e.getXY());
    };
    var associatePartnerIncubateePanel = function (id, typeName) {
      var incubateesPanel = new Ext.Panel({
        layout: "border",
        border: false,
        frame: false,
        bodyStyle: { "background-color": "white" },
        hideBorders: true,
        title: "Consulting Partner Incubatee",
        id: id,
        items: [gridAPIncubateeDetails(typeName), panelAPIncubatee(typeName)],
      });
      return incubateesPanel;
    };
    var incubateeGridStore = function (typeName) {
      return new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listIncubateeDetails",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "",
            root: "data",
          },
          [
            "aaId",
            "aaName",
            "aaMobile",
            "aaEmail",
            "businessVertical",
            "professionId","professionName",
            "businessVertical",
            "organisationName",
            "organisationType",
            "gstNumber",
            "businessLocation",
            "locationGlatitude",
            "locationGlongitude",
            "createdBy","createdByName","stageName","stageId",
            "createdOn",
            "updatedBy",
            "updatedOn",
          ]
        ),
        groupField: "",
        sortInfo: {
          field: "aaId",
          direction: "ASC",
        },
        root: "data",
        autoLoad: true,
        listeners: {
          load: function (store, record, options) {
            if (record.length > 0) {
              Ext.getCmp("gridAssociatePartnerIncubatee" + typeName)
                .getSelectionModel()
                .selectRow(0);
            }
          },
          beforeload: function (store, e) {
            this.baseParams.typeName = typeName;
            this.baseParams.type = Application.Crm_ConsultingPartner.Cache.type;
          },
        },
      });
    };
    var gridAPIncubateeDetails = function (typeName) {
      var _jsonStoreIncubateeGrid = incubateeGridStore(typeName);
      var _incubateeGridFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "aaName",
          },
          {
            type: "string",
            dataIndex: "aaMobile",
          },
          {
            type: "string",
            dataIndex: "aaEmail",
          },
        ],
      });
      _incubateeGridFilter.remote = true;
      _incubateeGridFilter.autoReload = true;
      var incubateeGrid = new Ext.grid.GridPanel({
        id: "gridAssociatePartnerIncubatee" + typeName,
        layout: "fit",
        store: _jsonStoreIncubateeGrid,
        region: "center",
        frame: true,
        border: false,
        plugins: [_incubateeGridFilter],
        loadMask: true,
        columns: [
          {
            header: "Name",
            dataIndex: "aaName",
            sortable: true,
            width: 200,
          },
          {
            header: "Mobile",
            dataIndex: "aaMobile",
            sortable: true,
          },
          {
            header: "Email",
            dataIndex: "aaEmail",
            sortable: true,
            width: 200,
          },
          {
            header: "Profession",
            dataIndex: "professionName",
            sortable: true,
          },          
          {
            header: "Created By",
            dataIndex: "createdByName",
            sortable: true,
            hideable: true,
          },{
            header: "Created On",
            dataIndex: "createdOn",
            sortable: true,
          },{
            header: "Stage",
            dataIndex: "stageName",
            sortable: true,
          },
          {
            xtype: "actioncolumn",
            header: "Action",
            hideable: false,
            iconCls: "downarrow",
            tooltip: "Choose Actions",
            listeners: {
              click: function (a, grid, rowindex, e) {
                var record = grid.store.getAt(rowindex);
                grid.getSelectionModel().selectRow(rowindex);
                aaIncubateeActionMenu(e, typeName);
                //action
              },
            },
          },
        ],
        tbar: [
        ],
        viewConfig: {
          forceFit: true,
        },
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          getRowClass: function (record, index, params, store) {},
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: _jsonStoreIncubateeGrid,
          displayInfo: true,
          displayMsg: "Displaying items {0} - {1} of {2}",
          emptyMsg: "No records to display",
          items: [,],
        }),
        stripeRows: true,
        sm: new Ext.grid.RowSelectionModel({
          singleSelected: true,
          listeners: {
            selectionchange: gridSelectionChangedaaIncubatee,
          },
        }),
        listeners: {
          afterrender: function () {
            _jsonStoreIncubateeGrid.load();
          },
          cellclick: function (grid, rowIndex, columnIndex, e) {},
          resize: onGridResize,
        },
      });
      return incubateeGrid;
    };
    var gridAPIncubateeDetails = function (typeName) {
          var _jsonStoreIncubateeGrid = incubateeGridStore(typeName);
          var _incubateeGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
              {
                type: "string",
                dataIndex: "aaName",
              },
              {
                type: "string",
                dataIndex: "aaMobile",
              },
              {
                type: "string",
                dataIndex: "aaEmail",
              },
            ],
          });
          _incubateeGridFilter.remote = true;
          _incubateeGridFilter.autoReload = true;
          var incubateeGrid = new Ext.grid.GridPanel({
            id: "gridAssociatePartnerIncubatee" + typeName,
            layout: "fit",
            store: _jsonStoreIncubateeGrid,
            region: "center",
            frame: true,
            border: false,
            plugins: [_incubateeGridFilter],
            loadMask: true,
            columns: [
              {
                header: "Name",
                dataIndex: "aaName",
                sortable: true,
                width: 200,
              },
              {
                header: "Mobile",
                dataIndex: "aaMobile",
                sortable: true,
              },
              {
                header: "Email",
                dataIndex: "aaEmail",
                sortable: true,
                width: 200,
              },
              {
                header: "Profession",
                dataIndex: "professionName",
                sortable: true,
              },          
              {
                header: "Created By",
                dataIndex: "createdByName",
                sortable: true,
                hideable: true,
              },{
                header: "Created On",
                dataIndex: "createdOn",
                sortable: true,
              },{
                header: "Stage",
                dataIndex: "stageName",
                sortable: true,
              },
              {
                xtype: "actioncolumn",
                header: "Action",
                hideable: false,
                iconCls: "downarrow",
                tooltip: "Choose Actions",
                listeners: {
                  click: function (a, grid, rowindex, e) {
                    var record = grid.store.getAt(rowindex);
                    grid.getSelectionModel().selectRow(rowindex);
                    aaIncubateeActionMenu(e, typeName);
                    //action
                  },
                },
              },
            ],
            tbar: [
            ],
            viewConfig: {
              forceFit: true,
            },
            view: new Ext.grid.GroupingView({
              forceFit: true,
              deferEmptyText: false,
              getRowClass: function (record, index, params, store) {},
              emptyText:
                '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
              groupTextTpl:
                '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
            }),
            bbar: new Ext.PagingToolbar({
              pageSize: recs_per_page,
              store: _jsonStoreIncubateeGrid,
              displayInfo: true,
              displayMsg: "Displaying items {0} - {1} of {2}",
              emptyMsg: "No records to display",
              items: [,],
            }),
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
              singleSelected: true,
              listeners: {
                selectionchange: gridSelectionChangedaaIncubatee,
              },
            }),
            listeners: {
              afterrender: function () {
                _jsonStoreIncubateeGrid.load();
              },
              cellclick: function (grid, rowIndex, columnIndex, e) {},
              resize: onGridResize,
            },
          });
          return incubateeGrid;
        };
        var gridSelectionChangedaaIncubatee = function (sm) {
          if (
            !Ext.isEmpty(
              Ext.getCmp(
                "gridAssociatePartnerIncubatee" + Application.Crm_ConsultingPartner.Cache.typeName
              )
                .getSelectionModel()
                .getSelections()
            )
          ) {
            var ID = Ext.getCmp(
              "gridAssociatePartnerIncubatee" + Application.Crm_ConsultingPartner.Cache.typeName
            )
              .getSelectionModel()
              .getSelections()[0].data.aaId;
            Ext.getCmp(
              "tabpanelAssociatePartnerIncubatee" + Application.Crm_ConsultingPartner.Cache.typeName
            ).setActiveTab(0);
            Application.Crm_ConsultingPartner.Cache.aaId = ID;
            Application.Crm_ConsultingPartner.ViewModeIncubatee(ID);
          } else {

            Application.Crm_ConsultingPartner.Cache.aaId = 0;
            Application.Crm_ConsultingPartner.ViewModeIncubatee(0);
          }
        };
        var panelAPIncubatee = function (typeName) {
          var t = new Date();
          var t_stamp = t.format("YmdHis");
          var apikey = _SESSION.apikey;
          var src =
            "?module=crm_associate_partner&op=loadEditData&aaId=" +
            Application.Crm_ConsultingPartner.Cache.aaId +
            "&tstamp=" +
            t_stamp +
            "&apikey=" +
            apikey +
            "&typeName=" +
            typeName;
          var panel = new Ext.TabPanel({
            region: "east",
            frame: false,
            activeTab: 0,
            tabPosition: "top",
            border: true,
            bodyStyle: {
              "background-color": "white",
            },
            id: "tabpanelAssociatePartnerIncubatee" + typeName,
            width: winsize.width * 0.4,
            height: winsize.height * 0.6,
            defaults: {
              layout: "fit",
              autoScroll: true,
              frame: false,
            },
            items: [
              {
                title: "View Details",
                id: "tabMarketingAddIncubatee" + typeName,
                layout: "border",
                items: [tableIncubateeDetails()],
              },
              {
                title: "Log",
                id: "tabMarketingCommunicationIncubatee" + typeName,
                layout: "border",
                items: [communicationLogDetails('Incubatee')],
              }
            ],
            fbar: [],
            listeners: {
              afterrender: function (component) {},
              tabchange: function (s, tab) {
                switch (tab.title) {
                  case "Log":
                    Ext.getCmp('gridCommunicationListIncubatee').getStore().load({
                      params:{
                        aaId: Application.Crm_ConsultingPartner.Cache.aaId
                      }
                    });
                    break;
                }
              },
            },
          });
          return panel;
        };
        var tableIncubateeDetails = function () {
          var t = new Date();
          var t_stamp = t.format("YmdHis");
          var apikey = _SESSION.apikey;
          var src =
            "?module=crm_associate_partner&op=loadEditData&aaId=" +
            Application.Crm_ConsultingPartner.Cache.aaId +
            "&tstamp=" +
            t_stamp +
            "&apikey=" +
            apikey;
          var incubateePanel = new Ext.Panel({
            id: "tableCrmIncubateeDataview" + Application.Crm_ConsultingPartner.Cache.typeName,
            region: "center",
            width: winsize.width * 0.39,
            items: [
              {
                region: "center",
                defaults: {
                  frame: false,
                },
                html:
                  '<iframe id="downloadAPIncubateeIframe' +
                  Application.Crm_ConsultingPartner.Cache.typeName +
                  '" name="downloadAPIncubateeIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
                  src +
                  '"; ></iframe>',
              },
            ],
          });
          return incubateePanel;
        };
        var aaIncubateeActionMenu = function (e, typeName) {
          var aaEnquiryActionMenu = new Ext.menu.Menu({
            items: [          
              {
                text: "Training Updates",
                handler: function () {
                  var aaId = Ext.getCmp("gridAssociatePartnerIncubatee" + typeName)
                    .getSelectionModel()
                    .getSelections()[0].data.aaId;
                    var aaName = Ext.getCmp("gridAssociatePartnerIncubatee" + typeName)
                    .getSelectionModel()
                    .getSelections()[0].data.aaName;
                    Application.Crm_ConsultingPartner.trainingUpdates(aaId,aaName);
                }
              }
            ],
          });
          aaEnquiryActionMenu.showAt(e.getXY());
        };
        var mapAreatoAreaAssociateOppurtunityGrid = function(aaId){
          var ck_selection = ckSelection();
          var _aaProspectAreaStore = areaOppurtunityStore(aaId);
          var _aaProspectAreaGridPanel = new Ext.grid.GridPanel({
            region: "center",
            layout: "fit",
            frame: false,
            border: false,
            loadMask: true,
            store: _aaProspectAreaStore,
            autoScroll: true,
            width: 400,
            height: 250,
            bodyStyle: { "background-color": "white" },
            id: "gridpanelOppurtunityareas",
            sm: ck_selection,
            columns: [
              ck_selection,
              {
                header: "Area",
                dataIndex: "areaName",
                sortable: true,
                tooltip: "Area",
                hideable: true,
              },
              {
                header: "Location",
                dataIndex: "areaLocation",
                sortable: true,
                tooltip: "Location",
                hideable: true,
              },{
                header: "Distance",
                dataIndex: "distance",
                sortable: true,
                tooltip: "Distance",
                hideable: true,
              },{
                header: "Assigned",
                dataIndex: "assigned",
                sortable: true,
                tooltip: "Assigned",
                hideable: true,
              }
            ],
            viewConfig: {
              forceFit: true,
              getRowClass: function (record, index, params, store) {},
            },
            tbar: [
            ],
          });
          return _aaProspectAreaGridPanel;
        };
        var areaOppurtunityStore = function (aaId) {
          var _Store = new Ext.data.JsonStore({
            method: "post",
            proxy: new Ext.data.HttpProxy({
              url: modURL + "&op=listAreaForOppurtunity",
              method: "post",
            }),
            fields: ["id","areaName","areaLocation","areaLatitude","distance","areaConsultingPartnerFinal","assigned"],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true,
            listeners: {
              beforeload: function () {
                this.baseParams.aaId = aaId;
              },
              load: function () {
              },
            },
          });
          return _Store;
        };
        var oppurtunityStageStore = function(){
          var store = new Ext.data.JsonStore({
            url: modURL + "&op=getCrmOppurtunityStage",
            method: "post",
            autoLoad: true,
            fields: ["id", "name"],
            root: "data",
          });
          return store;
        };
        var upgradeOppurtunityStages = function (id) {
          var storeCrmStatus = oppurtunityStageStore();
          fileS3BucketView();
          var cpfrmOppurtunityWindow = new Ext.Window({
            id: "windowToUpgradeOppurtunity",
            iconCls: "vender-items",
            shadow: false,
            width: winsize.width * 0.4,
            height: winsize.height * 0.4,
            title: "Update Oppurtunity Stages",
            layout: "fit",
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [
              new Ext.FormPanel({
                layout: "form",
                id:"upgradeOppurtunityStageFormPanel" + Application.Crm_ConsultingPartner.Cache.typeName,
                height: 150,
                fileUpload: true,
                columnWidth: 1,
                frame: true,
                border: true,
                items: [
                  {
                    xtype: "panel",
                    layout: "column",
                    items: [
                      {
                        xtype: 'hidden',
                        id: 's3_filename',
                        name: 's3_filename'
                    }, {
                        xtype: 'hidden',
                        id: 's3_albumBucketName',
                        name: 's3_albumBucketName'
                    }, {
                        xtype: 'hidden',
                        id: 's3_accessKey',
                        name: 's3_accessKey'
                    }, {
                        xtype: 'hidden',
                        id: 's3_secretKey',
                        name: 's3_secretKey'
                    }, {
                        xtype: 'hidden',
                        id: 's3_bucketRegion',
                        name: 's3_bucketRegion'
                    },
                    {
                        xtype: 'hidden',
                        id: 's3_oncompleteurl',
                        name: 's3_oncompleteurl'
                    },
                    {
                        xtype: 'hidden',
                        id: 's3_img_path_db',
                        name: 's3_img_path_db'
                    },{
                      xtype: 'hidden',
                      id: 's3_bucketFolder',
                      name: 's3_bucketFolder'
                  },
                    {
                        xtype: 'hidden',
                        id: 's3filepath',
                        name: 's3filepath'
                    },
                      {
                        columnWidth: 0.5,
                        layout: "form",
                        frame: false,
                        border: false,
                        labelAlign: "top",
                        items: [
                          {
                            xtype: "combo",
                            store: storeCrmStatus,
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
                                var oppurtunityStage  = Ext.getCmp('crmStatus').getValue();
                                if(oppurtunityStage == 12){
                                  Ext.getCmp('areaLockedTill').show();
                                  Ext.getCmp('crmFollowupDate').hide();
                                }else{
                                  Ext.getCmp('crmFollowupDate').show();
                                  Ext.getCmp('areaLockedTill').hide();
                                }
                                if(oppurtunityStage == 13){
                                  Ext.getCmp('fileuploadfieldAttachFileCustomers').show();
                                }else{
                                  Ext.getCmp('fileuploadfieldAttachFileCustomers').hide();
                                }
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
                            fieldLabel: "Extend Area Locking",
                            xtype: "datefield",
                            hidden:true,
                            allowblank: false,
                            id: "areaLockingTime",
                            anchor: "95%",
                            format: "d/m/Y",
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
                      {
                        columnWidth: .16,
                        border: false,
                        buttons: [
                            {
                                xtype: 'fileuploadfield',
                                hidden:true,
                                style: 'margin-bottom:5px;',
                                id: 'fileuploadfieldAttachFileCustomers',
                                border: false,
                                anchor: '97%',
                                name: 'fileuploadfieldAttachFileCustomers',
                                allowBlank: true,
                                buttonOnly: true,
                                buttonCfg: {
                                    text: 'Upload File',
                                    border: false,
                                    width: 100
                                },
                                validator: function (v) {
                                    if (v != '')
                                    {
                                        v = v.toLowerCase();
                                        var exp = /^.*\.(png|jpg|gif|pdf|docx)$/i;
                                        if (!(exp.test(v)))
                                        {
                                            return 'Upload a valid image file of format JPG.';
                                        }
      
                                        var associated_file = Ext.getCmp('fileuploadfieldAttachFileCustomers').getValue();
                                        if (associated_file == '')
                                        {
                                            Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                            return;
                                        }
                                        addFile();
                                        return true;
                                    }
                                }
                            }
                        ]
                    }, {
                        columnWidth: 1,
                        layout: 'form',
                        border: false,
                        items: [{xtype: 'displayfield', id: 'supporting', cls: 'fpcls', value: 'File uploaded successfully', hidden: true}]
                    }
                    ],
                  },
                ],
              }),
            ],
            fbar: [
              {
                text: "Cancel",
                anchor: "90%",
                columnWidth: 0.1,
                xtype: "button",
                icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                tabIndex: 33,
                handler: function () {
                  cpfrmOppurtunityWindow.close();
                },
              },
              {
                xtype: "button",
                icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                text: "Save",
                handler: function () {
                  var crmStatus = Ext.getCmp("crmStatus").getValue();
                  var crmFollowupDate = Ext.getCmp("crmFollowupDate").getValue();
                  var areaLockedTill = Ext.getCmp("areaLockedTill").getValue();
                  var crmRemarks = Ext.getCmp("crmRemarks").getValue();
                  if (crmStatus > 0 && !Ext.isEmpty(crmFollowupDate)) {
                    Ext.Ajax.request({
                      waitMsg: "Processing",
                      method: "POST",
                      url: modURL + "&op=upgradeOppurtunityStages",
                      params: {
                        leadId: id,
                        crmStatus: crmStatus,
                        crmFollowupDate: crmFollowupDate,
                        crmRemarks: crmRemarks,
                        areaLockedTill:areaLockedTill
                      },
                      success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                          Ext.getCmp(
                            "gridAssociatePartnerOppurtunity" +
                              Application.Crm_ConsultingPartner.Cache.typeName
                          )
                            .getStore()
                            .load({
                              callback: function (record, options, success) {
                                cpfrmOppurtunityWindow.close();
                                Application.example.msg("Notification", tmp.msg);
                                var gridPanel = Ext.getCmp(
                                  "gridAssociatePartnerOppurtunity" +
                                    Application.Crm_ConsultingPartner.Cache.typeName
                                );
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
                  } else {
                    Ext.MessageBox.alert("Error", "Choose status and proceed.");
                  }
                },
              },
            ],
            listeners: {
              load: function () {},
            },
          });
      
          cpfrmOppurtunityWindow.doLayout();
          cpfrmOppurtunityWindow.show();
          cpfrmOppurtunityWindow.center();
        };
        var moveToIncubatee = function (id,typeName) {
          Ext.Ajax.request({
              waitMsg: 'Processing',
              method: 'POST',
              url: modURL + '&op=upgradeOppurtunityStages',
              params: {
                  leadId: id,
                  crmStatus:15
              },
              success: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  if (tmp.success === true) {
                      Ext.getCmp('gridAssociatePartnerOppurtunity'+typeName).getStore().reload();
                  }else{
                      Ext.MessageBox.alert('Error', tmp.msg);
                  }
              },
              failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert('Error', "Error occurred");
              }
          });
      };
      var training_tree_panel = function (id) {

        var tree = new Ext.tree.TreePanel({
            region: 'center',
            useArrows: false,
            autoScroll: true,
            animate: true,
            enableDD: true,
            containerScroll: true,
            border: true,
            overClass: 'x-view-over',
            itemSelector: 'div.thumb-wrap',
            dataUrl: modURL + '&op=getTraingModules' + '&user_id=' + id,
            id: 'user_permission_tree',
            mask: true,
            maskDisabled: false,
            maskConfig: {msg: "Loading items..."},
            root: {
                nodeType: 'async',
                text: '',
                draggable: false,
                id: 'permission_tree_root',
                cls: 'nature_group'
            },
            listeners: {
                load: function () {
                    setTimeout(function () {
                        if (WinMask)
                            WinMask.hide();
                    }, 500);
                },
                checkchange:function (node,checked){
                  Ext.Ajax.request({
                    url: modURL + '&op=saveTrainings',
                    params: {
                        user_id: id,
                        perm_op: node.attributes['trainingId'],
                        node_checked:checked
                    },
                    success: function (response, options) {

                    }
                  });
                }
            }
        });
        //Ext.getCmp('ldg_company').setValue(store.getAt('0').get('comp_id'));
        tree.getRootNode().expand();
        return tree;
    };
    var  trainingModuelStore = function(aaId){
      return new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listTrainingModules",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "",
            root: "data",
          },
          ["tmtId", "ModuleId","topicName", "moduleName","checked","trainingId","trainingComments","trainingDate"]
        ),
        groupField: "",
        sortInfo: {
          field: "tmtId",
          direction: "ASC",
        },
        root: "data",
        autoLoad: true,
        listeners: {
          beforeload:function(){
            this.baseParams.aaId = aaId;
          }
        },
      });
    }
    var training_grid_panel = function(aaId){    
      //var _aatrainingModuelStore = trainingModuelStore(aaId);  
      var _aatrainingModuelStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + "&op=listTrainingModules",
        method: "post",
        fields: ["tmtId","ModuleId","moduleName","topicName","checked","trainingId","trainingComment","trainingDate"],
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
              dataIndex: "trainingComment",
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
    var associatePartnerInstarPanel = function (id, typeName) {
      var instarsPanel = new Ext.Panel({
        layout: "border",
        border: false,
        frame: false,
        bodyStyle: { "background-color": "white" },
        hideBorders: true,
        title: "Consulting Partner Instar",
        id: id,
        items: [gridAPInstarDetails(typeName), panelAPInstar(typeName)],
      });
      return instarsPanel;
    };
    var instarGridStore = function (typeName) {
      return new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
          url: modURL + "&op=listInstarDetails",
          method: "post",
        }),
        reader: new Ext.data.JsonReader(
          {
            totalProperty: "totalCount",
            idProperty: "",
            root: "data",
          },
          [
            "aaId",
            "aaName",
            "aaMobile",
            "aaEmail",
            "businessVertical",
            "professionId","professionName",
            "businessVertical",
            "organisationName",
            "organisationType",
            "gstNumber",
            "businessLocation",
            "locationGlatitude",
            "locationGlongitude",
            "createdBy","createdByName","stageName","stageId",
            "createdOn",
            "updatedBy",
            "updatedOn",
          ]
        ),
        groupField: "",
        sortInfo: {
          field: "aaId",
          direction: "ASC",
        },
        root: "data",
        autoLoad: true,
        listeners: {
          load: function (store, record, options) {
            if (record.length > 0) {
              Ext.getCmp("gridAssociatePartnerInstar" + typeName)
                .getSelectionModel()
                .selectRow(0);
            }
          },
          beforeload: function (store, e) {
            this.baseParams.typeName = typeName;
            this.baseParams.type = Application.Crm_ConsultingPartner.Cache.type;
          },
        },
      });
    };
    var gridAPInstarDetails = function (typeName) {
      var _jsonStoreInstarGrid = instarGridStore(typeName);
      var _instarGridFilter = new Ext.ux.grid.GridFilters({
        filters: [
          {
            type: "string",
            dataIndex: "aaName",
          },
          {
            type: "string",
            dataIndex: "aaMobile",
          },
          {
            type: "string",
            dataIndex: "aaEmail",
          },
        ],
      });
      _instarGridFilter.remote = true;
      _instarGridFilter.autoReload = true;
      var instarGrid = new Ext.grid.GridPanel({
        id: "gridAssociatePartnerInstar" + typeName,
        layout: "fit",
        store: _jsonStoreInstarGrid,
        region: "center",
        frame: true,
        border: false,
        plugins: [_instarGridFilter],
        loadMask: true,
        columns: [
          {
            header: "Name",
            dataIndex: "aaName",
            sortable: true,
            width: 200,
          },
          {
            header: "Mobile",
            dataIndex: "aaMobile",
            sortable: true,
          },
          {
            header: "Email",
            dataIndex: "aaEmail",
            sortable: true,
            width: 200,
          },
          {
            header: "Profession",
            dataIndex: "professionName",
            sortable: true,
          },          
          {
            header: "Created By",
            dataIndex: "createdByName",
            sortable: true,
            hideable: true,
          },{
            header: "Created On",
            dataIndex: "createdOn",
            sortable: true,
          },{
            header: "Stage",
            dataIndex: "stageName",
            sortable: true,
          },
          {
            xtype: "actioncolumn",
            header: "Action",
            hideable: false,
            iconCls: "downarrow",
            tooltip: "Choose Actions",
            listeners: {
              click: function (a, grid, rowindex, e) {
                var record = grid.store.getAt(rowindex);
                grid.getSelectionModel().selectRow(rowindex);
                aaInstarActionMenu(e, typeName);
                //action
              },
            },
          },
        ],
        tbar: [
        ],
        viewConfig: {
          forceFit: true,
        },
        view: new Ext.grid.GroupingView({
          forceFit: true,
          deferEmptyText: false,
          getRowClass: function (record, index, params, store) {},
          emptyText:
            '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
          groupTextTpl:
            '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
        }),
        bbar: new Ext.PagingToolbar({
          pageSize: recs_per_page,
          store: _jsonStoreInstarGrid,
          displayInfo: true,
          displayMsg: "Displaying items {0} - {1} of {2}",
          emptyMsg: "No records to display",
          items: [,],
        }),
        stripeRows: true,
        sm: new Ext.grid.RowSelectionModel({
          singleSelected: true,
          listeners: {
            selectionchange: gridSelectionChangedaaInstar,
          },
        }),
        listeners: {
          afterrender: function () {
            _jsonStoreInstarGrid.load();
          },
          cellclick: function (grid, rowIndex, columnIndex, e) {},
          resize: onGridResize,
        },
      });
      return instarGrid;
    };
    var gridAPInstarDetails = function (typeName) {
          var _jsonStoreInstarGrid = instarGridStore(typeName);
          var _instarGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
              {
                type: "string",
                dataIndex: "aaName",
              },
              {
                type: "string",
                dataIndex: "aaMobile",
              },
              {
                type: "string",
                dataIndex: "aaEmail",
              },
            ],
          });
          _instarGridFilter.remote = true;
          _instarGridFilter.autoReload = true;
          var instarGrid = new Ext.grid.GridPanel({
            id: "gridAssociatePartnerInstar" + typeName,
            layout: "fit",
            store: _jsonStoreInstarGrid,
            region: "center",
            frame: true,
            border: false,
            plugins: [_instarGridFilter],
            loadMask: true,
            columns: [
              {
                header: "Name",
                dataIndex: "aaName",
                sortable: true,
                width: 200,
              },
              {
                header: "Mobile",
                dataIndex: "aaMobile",
                sortable: true,
              },
              {
                header: "Email",
                dataIndex: "aaEmail",
                sortable: true,
                width: 200,
              },
              {
                header: "Profession",
                dataIndex: "professionName",
                sortable: true,
              },          
              {
                header: "Created By",
                dataIndex: "createdByName",
                sortable: true,
                hideable: true,
              },{
                header: "Created On",
                dataIndex: "createdOn",
                sortable: true,
              },{
                header: "Stage",
                dataIndex: "stageName",
                sortable: true,
              },
              {
                xtype: "actioncolumn",
                header: "Action",
                hideable: false,
                iconCls: "downarrow",
                tooltip: "Choose Actions",
                listeners: {
                  click: function (a, grid, rowindex, e) {
                    var record = grid.store.getAt(rowindex);
                    grid.getSelectionModel().selectRow(rowindex);
                    aaInstarActionMenu(e, typeName);
                    //action
                  },
                },
              },
            ],
            tbar: [
            ],
            viewConfig: {
              forceFit: true,
            },
            view: new Ext.grid.GroupingView({
              forceFit: true,
              deferEmptyText: false,
              getRowClass: function (record, index, params, store) {},
              emptyText:
                '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
              groupTextTpl:
                '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
            }),
            bbar: new Ext.PagingToolbar({
              pageSize: recs_per_page,
              store: _jsonStoreInstarGrid,
              displayInfo: true,
              displayMsg: "Displaying items {0} - {1} of {2}",
              emptyMsg: "No records to display",
              items: [,],
            }),
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
              singleSelected: true,
              listeners: {
                selectionchange: gridSelectionChangedaaInstar,
              },
            }),
            listeners: {
              afterrender: function () {
                _jsonStoreInstarGrid.load();
              },
              cellclick: function (grid, rowIndex, columnIndex, e) {},
              resize: onGridResize,
            },
          });
          return instarGrid;
        };
        var gridSelectionChangedaaInstar = function (sm) {
          if (
            !Ext.isEmpty(
              Ext.getCmp(
                "gridAssociatePartnerInstar" + Application.Crm_ConsultingPartner.Cache.typeName
              )
                .getSelectionModel()
                .getSelections()
            )
          ) {
            var ID = Ext.getCmp(
              "gridAssociatePartnerInstar" + Application.Crm_ConsultingPartner.Cache.typeName
            )
              .getSelectionModel()
              .getSelections()[0].data.aaId;
            Ext.getCmp(
              "tabpanelAssociatePartnerInstar" + Application.Crm_ConsultingPartner.Cache.typeName
            ).setActiveTab(0);
            Application.Crm_ConsultingPartner.Cache.aaId = ID;
            Application.Crm_ConsultingPartner.ViewModeInstar(ID);
          } else {
            Application.Crm_ConsultingPartner.Cache.aaId = 0;
            Application.Crm_ConsultingPartner.ViewModeInstar(0);
          }
        };
        var panelAPInstar = function (typeName) {
          var t = new Date();
          var t_stamp = t.format("YmdHis");
          var apikey = _SESSION.apikey;
          var src =
            "?module=crm_associate_partner&op=loadEditData&aaId=" +
            Application.Crm_ConsultingPartner.Cache.aaId +
            "&tstamp=" +
            t_stamp +
            "&apikey=" +
            apikey +
            "&typeName=" +
            typeName;
          var panel = new Ext.TabPanel({
            region: "east",
            frame: false,
            activeTab: 0,
            tabPosition: "top",
            border: true,
            bodyStyle: {
              "background-color": "white",
            },
            id: "tabpanelAssociatePartnerInstar" + typeName,
            width: winsize.width * 0.4,
            height: winsize.height * 0.6,
            defaults: {
              layout: "fit",
              autoScroll: true,
              frame: false,
            },
            items: [
              {
                title: "View Details",
                id: "tabMarketingAddInstar" + typeName,
                layout: "border",
                items: [tableInstarDetails()],
              },{
                title: "Log",
                id: "tabMarketingCommunicationInstar" + typeName,
                layout: "border",
                items: [communicationLogDetails('Instar')],
              }
            ],
            fbar: [],
            listeners: {
              afterrender: function (component) {},
              tabchange: function (s, tab) {
                switch (tab.title) {
                  case "View Details":
                    break;
                    case "Log":
                      Ext.getCmp('gridCommunicationListInstar').getStore().load({
                        params:{
                          aaId: Application.Crm_ConsultingPartner.Cache.aaId
                        }
                      });
                      break;
                }
              },
            },
          });
          return panel;
        };
        var tableInstarDetails = function () {
          var t = new Date();
          var t_stamp = t.format("YmdHis");
          var apikey = _SESSION.apikey;
          var src =
            "?module=crm_associate_partner&op=loadEditData&aaId=" +
            Application.Crm_ConsultingPartner.Cache.aaId +
            "&tstamp=" +
            t_stamp +
            "&apikey=" +
            apikey;
          var instarPanel = new Ext.Panel({
            id: "tableCrmInstarDataview" + Application.Crm_ConsultingPartner.Cache.typeName,
            region: "center",
            width: winsize.width * 0.39,
            items: [
              {
                region: "center",
                defaults: {
                  frame: false,
                },
                html:
                  '<iframe id="downloadAPInstarIframe' +
                  Application.Crm_ConsultingPartner.Cache.typeName +
                  '" name="downloadAPInstarIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
                  src +
                  '"; ></iframe>',
              },
            ],
          });
          return instarPanel;
        };
        var aaInstarActionMenu = function (e, typeName) {
          var aaInstarActions = new Ext.menu.Menu({
            items: [          
             {
                text: "Upgrade Instar",
                handler: function () {
                  var aaId = Ext.getCmp("gridAssociatePartnerInstar" + typeName)
                    .getSelectionModel()
                    .getSelections()[0].data.aaId;
                    upgradeInstarStages(aaId);
                }
              }
            ],
          });
          aaInstarActions.showAt(e.getXY());
        };
        var upgradeInstarStages = function(aaId){
          var _addNewWindow = new Ext.Window({
            title: "Areas",
            layout: "fit",
            width: winsize.width * 0.6,
            height: winsize.height * 0.8,
            resizable: false,
            draggable: true,
            closable: true,
            modal: true,
            bodyStyle: { "background-color": "white" },
            items: [mapAreatoIncubateeGrid(aaId)],
            buttons: [
              {
                text: "Cancel",
                icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                iconCls: "my-icon1",
                tabIndex: 511,
                handler: function () {
                  _addNewWindow.close();
                },
              },
              {
                text: "Assign",
                icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                tabIndex: 511,
                id: "assignBAbtn",
                handler: function () {
                  var store_fields = Ext.getCmp('gridpanelInstarareas').getSelectionModel().getSelections();
                  var isAssigned = 0;
                  if (store_fields.length > 0) {
                    if (store_fields.length > 0) {
                      areaIds = [];
                      for (var i = 0; i < store_fields.length; i++) {
                        areaIds[i] = store_fields[i].data.id;
                        if(store_fields[i].data.areaConsultingPartnerFinal > 0){
                          isAssigned ++;
                        }
                        
                      }
                    }    
                    console.log('isAssigned',isAssigned);
                    if(isAssigned == 0 && areaIds.length == 1){
                      Ext.Ajax.request({
                        url: modURL + "&op=confirmAreaAssociate",
                        method: "POST",
                        params: {
                          aaId: aaId,
                          areaId: areaIds[0],
                        },
                        success: function (response) {
                          var tmp = Ext.decode(response.responseText);
                          if (tmp.success == true) {
                            Application.example.msg("Success", tmp.msg);
                            _addNewWindow.close();
                            Ext.getCmp("gridAssociatePartnerInstar" + typeName).getStore().load();
                          } else {
                            Ext.Msg.alert("Error", tmp.msg);
                          }
                        },
                        failure: function (response) {
                          var tmp = Ext.util.JSON.decode(response.responseText);
                          Ext.MessageBox.alert("Error", tmp.msg);
                        },
                      });
                    }else{
                      Ext.MessageBox.alert("Notification", "Selected areas are already assigned.");
                    }            
                  
                } else {
                  Ext.Msg.alert(
                    "Notification",
                    "Select area and proceed."
                  );
                }
                },
              },
            ],
            
          });
          _addNewWindow.doLayout();
          _addNewWindow.show();
          _addNewWindow.center();
         
        };
        var areaIncubateeStore = function (aaId) {
          var _Store = new Ext.data.JsonStore({
            method: "post",
            proxy: new Ext.data.HttpProxy({
              url: modURL + "&op=listAreaForIncubatee",
              method: "post",
            }),
            fields: ["id","areaName","areaLocation","areaLatitude","distance","checked","areaConsultingPartnerFinal","assigned"],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true,
            listeners: {
              beforeload: function () {
                this.baseParams.aaId = aaId;
              },
              load: function () {
              },
            },
          });
          return _Store;
        };
        var mapAreatoIncubateeGrid = function(aaId){
          var ck_selection = ckSelection();
          var _aaInstarAreaStore = areaIncubateeStore(aaId);
          var _aaInstarAreaGridPanel = new Ext.grid.GridPanel({
            region: "center",
            layout: "fit",
            frame: false,
            border: false,
            loadMask: true,
            store: _aaInstarAreaStore,
            autoScroll: true,
            width: 400,
            height: 250,
            bodyStyle: { "background-color": "white" },
            id: "gridpanelInstarareas",
            sm: ck_selection,
            columns: [
              ck_selection,
              {
                header: "Area",
                dataIndex: "areaName",
                sortable: true,
                tooltip: "Area",
                hideable: true,
              },
              {
                header: "Location",
                dataIndex: "areaLocation",
                sortable: true,
                tooltip: "Location",
                hideable: true,
              },{
                header: "Assigned",
                dataIndex: "assigned",
                sortable: true,
                tooltip: "Assigned",
                hideable: true,
              }
            ],
            viewConfig: {
              forceFit: true,
              getRowClass: function (record, index, params, store) {},
            },
            tbar: [
            ],
            listeners: {
              afterrender: function () {
                {
                  var me = this;
                  _aaInstarAreaStore.on("load", function () {
                    var data = me.getStore().data.items;
                    var recs = [];
                    Ext.each(data, function (item, index) {
                      if (item.data.checked == 1) {
                        recs.push(index);
                      }
                    });
                    me.getSelectionModel().selectRows(recs);
                  });
                }
              },
            }
          });
          return _aaInstarAreaGridPanel;
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
    var communicationLogDetails = function(type){
      var _communicationStore = crmCommunicationStore();
      var grid = new Ext.grid.GridPanel({
          id: 'gridCommunicationList'+type,
          region: 'center',
          columnWidth: .7,
          width: winsize.width * 0.39,
          autoScroll: true,
          frame: true,
          border: false,
          store: _communicationStore,
          loadMask: true,
          viewConfig: {
              forceFit: true
          },
          colModel: new Ext.grid.ColumnModel({
              columns: [
                  {
                      header: 'Date and Time',
                      dataIndex: 'createdOn',
                      sortable: true,
                      width: 175
                  }, {
                      header: 'Type',
                      dataIndex: 'typeName'
                  },
                  {
                      header: 'Stage',
                      dataIndex: 'stageName'
                  },
                  {
                      header: 'Remark',
                      dataIndex: 'remark'
                  }
              ]

          }),
          iconCls: 'icon-grid'
      });
      return grid;
    };
    var crmCommunicationStore = function(){
      var _store = new Ext.data.JsonStore({
        url: modURL + '&op=getAACommunication',
        fields: ['id','entryTypeId','typeName','stageId','stageName','remark','createdOn'],
        remoteSort: true,
        method: 'post',
        totalProperty: 'totalCount',
        root: 'data',
        listeners: {
            beforeload: function (store, record, options) {
                
            }
        }
    });
    
    return _store;
    };
    var removeGeneralEnquiry = function (id) {
      Ext.Ajax.request({
          waitMsg: 'Processing',
          method: 'POST',
          url: modURL + '&op=removeGeneralEnquiry',
          params: {
              id: id,
          },
          success: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              if (tmp.success === true) {

                  Ext.getCmp('gridAssociatePartnerEnquiry'+Application.Crm_ConsultingPartner.Cache.typeName).getStore().reload();

              }
          },
          failure: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              Ext.MessageBox.alert('Error', "Error occurred");
          }
      });
  };
  var consultingPartnerMainPanel = function (id) {
    var mid = 0;
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      id: id,
      title: "Consulting Partner",
      items: [
        consultingPartnerGrid(),
        {
          border: false,
          frame: false,
          region: "east",
          layout: "vbox",
          layoutConfig: {
            align: "stretch",
            pack: "start",
          },
          width: winsize.width * 0.4,
          items: [
            consultingPartnerItem(mid),
            new Ext.Panel({
              border: false,
              frame: true,
              layout: "column",
              height: 50,
              items: [
                {
                  text: "Cancel",
                  xtype: "button",
                  width: 75,
                  columnWidth: 0.15,
                  id: "buttonconsultingPartnerCancel",
                  tabIndex: 414,
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  iconCls: "my-icon61",
                  hidden: true,
                  handler: function () {
                    Ext.getCmp(
                      "panelConsultingPartnerFinalDetails"
                    ).getForm().trackResetOnLoad = false;
                    Ext.getCmp("panelConsultingPartnerFinalDetails").hide();

                    Ext.getCmp("buttonconsultingPartnerSave").hide();
                    Ext.getCmp("buttonconsultingPartnerCancel").hide();
                    Ext.getCmp("tabpanelConsultingPartnerFinal").show();
                    var grid = Ext.getCmp("gridpanelconsultingPartner");
                    grid.getSelectionModel().clearSelections();

                    var form = Ext.getCmp(
                      "panelConsultingPartnerFinalDetails"
                    ).getForm();
                    form.reset();
                    Ext.getCmp("gridpanelconsultingPartner").getStore().load();
                  },
                },
                {
                  text: "Save",
                  xtype: "button",
                  width: 75,
                  columnWidth: 0.15,
                  id: "buttonconsultingPartnerSave",
                  tabIndex: 417,
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  iconCls: "thes_save",
                  hidden: true,
                  handler: function () {
                    var cust_id = Ext.getCmp("fbarhiddenid").getValue();
                    if (
                      Ext.getCmp("panelConsultingPartnerFinalDetails")
                        .getForm()
                        .isValid()
                    ) {
                      if (
                        cust_id == "" ||
                        cust_id == 0 ||
                        cust_id == "undefined"
                      ) {
                        Application.Crm_ConsultingPartner.saveConsultingPartnerFinalDetails(
                          0
                        );
                      } else {
                        Application.Crm_ConsultingPartner.saveConsultingPartnerFinalDetails(
                          0
                        );
                      }
                    } else {
                      console.log("form is not valid");
                    }
                  },
                },
                {
                  text: "Edit",
                  xtype: "button",
                  tabIndex: 418,
                  width: 75,
                  columnWidth: 0.15,
                  id: "buttonconsultingPartnerEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
                  hidden: true,
                  handler: function () {
                    Ext.getCmp("property_grid_id").hide();
                    Ext.getCmp("panelConsultingPartnerFinalDetails").show();
                    Ext.getCmp("panelConsultingPartnerFinalDetails").setTitle(
                      "Edit Form"
                    );
                    Ext.getCmp("buttonconsultingPartnerCancel").show();
                    Ext.getCmp("buttonconsultingPartnerSave").show();
                    Ext.getCmp("buttonconsultingPartnerEdit").hide();
                    Ext.getCmp("tabpanelConsultingPartnerFinal").hide();
                    var baId = Ext.getCmp("gridpanelconsultingPartner")
                      .getSelectionModel()
                      .getSelections()[0].data.baId;
                    var _Editformdata = Ext.getCmp(
                      "panelConsultingPartnerFinalDetails"
                    ).getForm();
                    console.log("baId");
                    console.log(baId);
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");

                    Ext.getCmp("fbarhiddenid").setValue(baId);

                    if (baId != 0 || baId != "") {
                      _Editformdata.load({
                        params: {
                          baId: baId,
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp,
                        },
                        url: bamodURL + "&op=editFormDataLoad",
                        waitMsg: "Loading...",
                        success: function (form, action) {
                          var tmp = Ext.decode(action.response.responseText);
                          Ext.getCmp("baMobileNo").setReadOnly(true);
                          Ext.getCmp("baEmail").setReadOnly(true);
                          Ext.getCmp("bast_id").getStore().load();
                          Ext.getCmp("bast_id").setValue(tmp.data.st_id);
                          Ext.getCmp("bast_id").setRawValue(tmp.data.st_name);
                          Ext.getCmp("badst_Id").getStore().baseParams.st_Id =
                            tmp.data.st_id;
                          Ext.getCmp("badst_Id").getStore().load();
                          Ext.getCmp("badst_Id").setValue(tmp.data.dst_Id);
                          Ext.getCmp("badst_Id").setRawValue(tmp.data.dst_Name);
						  if(tmp.data.baType == '1' && tmp.data.baMode == '2'){
                              Ext.getCmp("bptnrId").show();
                              Ext.getCmp("bptnrId").getStore().baseParams.type = 1;
                              Ext.getCmp("bptnrId").getStore().baseParams.mode = 2;
                              Ext.getCmp("bptnrId").getStore().load();
                              if(tmp.data.bptnrId > 0){
                              Ext.getCmp("bptnrId").setValue(tmp.data.bptnrId);
                              Ext.getCmp("bptnrId").setRawValue(tmp.data.bpName);
                              }
                            }else if(tmp.data.baType == '2' && tmp.data.baMode == '2'){
                              Ext.getCmp("bptnrId").show();
                              Ext.getCmp("bptnrId").getStore().load();
                              Ext.getCmp("bptnrId").setValue(tmp.data.bptnrId);
                              Ext.getCmp("bptnrId").setRawValue(tmp.data.bpName);
                            }else{
                              Ext.getCmp("baIsPartner").show();
							  Ext.getCmp("bptnrId").hide();
                            }
                          
                        },
                      });
                    }
                    consultingPartnerid_load =
                      Ext.getCmp("fbarhiddenid").getValue();
                  },
                },
              ],
            }),
          ],
        },
      ],
    });
    return panel;
  };
  var consultingPartnerGridStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listConsultingPartnerFinal",
      method: "post",
      fields: [
        "baId",
        "baName",
        "baPhone",
        "baAddress",
        "baCity",
        "baPincode",
        "baGSTIN",
        "dst_Id",
        "br_id",
        "baContactPerson",
        "baMobileNo",
        "baEmail",
        "baPanNo",
        "balatitude",
        "balongitude",
        "dst_Name",
        "st_name",
        "baTypeName",
        "baModeName",
        "bpId",
        "bpName",
        "baMode",
        "baType",
      ],
      totalProperty: "totalCount",
      root: "data",
      listeners: {
        beforeload: function () {
          //this.baseParams.partytype = partytype;
        },
        load: function () {
          Ext.getCmp("gridpanelconsultingPartner")
            .getSelectionModel()
            .selectRow(0);
          if (
            !Ext.isEmpty(
              Ext.getCmp("gridpanelconsultingPartner")
                .getSelectionModel()
                .getSelections()
            )
          ) {
            Ext.getCmp("property_grid_id").hide();

            Ext.getCmp("fbarhiddenid").reset();

            Ext.getCmp("buttonconsultingPartnerCancel").show();
            Ext.getCmp("buttonconsultingPartnerSave").show();
            Ext.getCmp("buttonconsultingPartnerEdit").hide();
            var data = Ext.getCmp("gridpanelconsultingPartner")
              .getSelectionModel()
              .getSelections()[0].data;
            Application.Crm_ConsultingPartner.ViewMode(data);
          }
        },
      },
    });
    return store;
  };
  var consultingPartnerGrid = function () {
    var grid_store = consultingPartnerGridStore();
    var customerGrid_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "baName",
        },
        {
          type: "string",
          dataIndex: "baLname",
        },
        {
          type: "string",
          dataIndex: "baGSTIN",
        },
        {
          type: "string",
          dataIndex: "baEmail",
        },
        {
          type: "string",
          dataIndex: "baPhone",
        },
        {
          type: "string",
          dataIndex: "st_name",
        },
        {
          type: "string",
          dataIndex: "dst_Name",
        },
      ],
    });
    customerGrid_filter.remote = true;
    customerGrid_filter.autoReload = true;

    var SP_grid = new Ext.grid.GridPanel({
      store: grid_store,
      id: "gridpanelconsultingPartner",
      region: "center",
      frame: true,
      border: false,
      layout: "fit",
      loadMask: true,
      plugins: [customerGrid_filter],
      columns: [
        {
          header: "Name",
          sortable: true,
          dataIndex: "baName",
          width: 175,
        },
        {
          header: GST,
          sortable: true,
          dataIndex: "baGSTIN",
          width: 175,
        },
        {
          header: "Email",
          sortable: true,
          dataIndex: "baEmail",
          width: 175,
        },
        {
          header: "Mobile",
          sortable: true,
          dataIndex: "baPhone",
          width: 175,
        },
        {
          header: STATE,
          sortable: true,
          dataIndex: "st_name",
          width: 175,
        },
        {
          header: DISTRICT,
          sortable: true,
          dataIndex: "dst_Name",
          width: 175,
        }
      ],
      viewConfig: {
        forceFit: true,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      tbar: [
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
      }),
      listeners: {
        viewready: onGridResize,
        rowclick: function (grid, rowIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("baId");
          var data = record.data;
          Application.Crm_ConsultingPartner.ViewMode(data);
        },
      },
    });
    return SP_grid;
  };
  //Right panel for tab panel starts here
  function consultingPartnerItem(mid) {
    //if(mid != 0)
    console.log("mid", mid);
    var lati, longi;
    if (mid == 0) {
      lati = 8.507007481504532;
      longi = 76.95167541503906;
    }
    var PartyEditPanel = editConsultingPartnerFinal(mid, lati, longi);

    var consultingPartneritempanel = new Ext.Panel({
      frame: false,
      border: false,
      collapsible: true,
      title: "Consulting Partner Details",
      flex: 1,
      layout: "vbox",
      layoutConfig: {
        align: "stretch",
        pack: "start",
      },
      id: "panelconsultingPartner",
      items: [
        PartyEditPanel,
        new Ext.TabPanel({
          activeTab: 0,
          flex: 1,
          plain: true,
          frame: true,
          id: "tabpanelConsultingPartnerFinal",
          items: [
            {
              title: "Details",
              id: "property_grid_id",
              width: winsize.width * 0.4,
              items: [
                {
                  xtype: "hidden",
                  id: "fbarhiddenid",
                  hidden: true,
                },
              ],
              tpl: new Ext.XTemplate(
                '<div class="details-outer">',
                '<table border="0" width="99%" class="details_view_table">',
                "<tr><th>Name :</th><td> {baName}</td></tr>",
                "<tr><th>Type :</th><td> Consulting Partner</td></tr>",
                "<tr><th>Mode :</th><td> {baModeName}</td></tr>",
                "<tr><th>Address:</th><td>  {baAddress}</td></tr>",
                "<tr><th>Email:</th><td>  {baEmail}</td></tr>",
                "<tr><th>"+PAN+" No.:</th><td>  {baPanNo}</td></tr>",
                "<tr><th>Mobile No.:</th><td>  {baMobileNo}</td></tr>",
                "<tr><th>"+STATE+" :</th><td>  {st_name}</td></tr>",
                "<tr><th>"+DISTRICT+" :</th><td>  {dst_Name}</td></tr>",
                "<tr><th>"+GST+" :</th><td>  {baGSTIN}</td></tr>",
                "</table>",
                "</div>"
              ),
            },
          ],
          listeners: {
            tabchange: function (sd, tab) {
              if (tab.id == "property_grid_id") {
                var _consultingPartnerId =
                  Ext.getCmp("fbarhiddenid").getValue();
                console.log("consultingPartner id is", _consultingPartnerId);
                if (
                  _consultingPartnerId == "" ||
                  _consultingPartnerId == undefined
                ) {
                  Ext.getCmp("buttonconsultingPartnerEdit").hide();
                } else {
                  Ext.getCmp("buttonconsultingPartnerEdit").show();
                }
              }
            },
          },
        }),
      ],
    });
    return consultingPartneritempanel;
  }
  var businessPartnerComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: bamodURL + "&op=getBusinessPartners",
      method: "post",
      fields: ["unique_key","id", "bpName","networkType"],
      totalProperty: "totalCount",
      root: "data",
    });

    return store;
  };
  var editConsultingPartnerFinal = function (mid, map_lat, map_long) {
    //var _associated_Branch = associatedBranch();
    var _state_store = stateStoreEdit();
    var district_store = districtStoreEdit();
    var businessPartnerStore = businessPartnerComboStore();
    my_marker = [
      {
        lat: map_lat,
        lng: map_long,
        marker: {
          title: "you are here",
          draggable: false,
        },
        listeners: {
          onFailure: function () {
            Ext.MessageBox.alert("Failed locating city ");
          },
          onSuccess: function (point) {},
          dragend: function (markerAt) {
            Ext.getCmp("balatitude").setValue(markerAt.latLng.lat());
            Ext.getCmp("balongitude").setValue(markerAt.latLng.lng());
          },
        },
      },
    ];

    var _partyPanel = new Ext.FormPanel({
      frame: false,
      border: false,
      hideBorders: true,
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      monitorValid: true,
      id: "panelConsultingPartnerFinalDetails",
      width: winsize.width * 0.4,
      flex: 1,
      layout: "form",
      //autoHeight: true,
      header: false,
      trackResetOnLoad: false,
      title: "Edit Consulting Partner",
      autoScroll: true,
      height: 500,
      labelAlign: "top",
      hidden: true,
      items: [
        {
          xtype: "panel",
          layout: "column",
          items: [
            {
              xtype: "hidden",
              id: "baId",
              hidden: true,
            },
            {
              columnWidth: 0.65,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              items: [
                {
                  xtype: "textfield",
                  fieldLabel: "Name",
                  id: "baName",
                  name: "baName",
                  tabIndex: 400,
                  anchor: "97%",
                  allowBlank: false,
                  style: { "text-transform": "uppercase" },
                  listeners: {
                    change: function (field, newValue, oldValue) {
                      field.setValue(newValue.toUpperCase());
                    },
                    afterrender: function (field) {
                      Ext.defer(function () {
                        field.focus(true, 100);
                      }, 1);
                    },
                  },
                },
                {
                  xtype: "textfield",
                  fieldLabel: "Last Name",
                  id: "baLname",
                  hidden: true,
                  name: "baLname",
                  //tabIndex: 401,
                  anchor: "97%",
                  allowBlank: true,
                  style: { "text-transform": "uppercase" },
                  listeners: {
                    change: function (field, newValue, oldValue) {
                      field.setValue(newValue.toUpperCase());
                    },
                  },
                },
              ],
            },
            {
              columnWidth: 0.35,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "textfield",
                  fieldLabel: "Email ID",
                  id: "baEmail",
                  name: "baEmail",
                  tabIndex: 413,
                  anchor: "99%",
                  allowBlank: false,
                  vtype: "email",
                },                
              ],
            },
            {
              columnWidth: 0.33,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [{
                  xtype: "textfield",
                  fieldLabel: "City",
                  id: "baCity",
                  name: "baCity",
                  tabIndex: 401,
                  inputType: "text",
                  anchor: "97%",
                  hidden: true,
                  allowBlank: true,
                  style: { "text-transform": "uppercase" },
                  listeners: {
                    change: function (field, newValue, oldValue) {
                      field.setValue(newValue.toUpperCase());
                    },
                  },
                },
                {
                  xtype: "combo",
                  fieldLabel: "Type",
                  emptyText: "Choose Type",
                  id: "baType",
                  name: "baType",
                  tabIndex: 402,
                  labelStyle: mandatory_label,
                  allowBlank: true,
                  hidden: true,
                  mode: "local",
                  typeAhead: true,
                  forceSelection: true,
                  editable: true,
                  anchor: "97%",
                  store: new Ext.data.JsonStore({
                    fields: ["id", "name"],
                    data: [
                      { id: "1", name: "Area Associate" },
                      { id: "2", name: "Business Associate" },
                    ],
                  }),
                  triggerAction: "all",
                  minChars: 2,
                  displayField: "name",
                  value:0,
                  valueField: "id",
                  hiddenName: "baType",
                  listeners: {
                    select: function () {

                    },
                  },
                },
              ],
            },{
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "textfield",
                  fieldLabel: "Mobile No",
                  id: "baMobileNo",
                  name: "baMobileNo",
                  vtype: "phonespec",
                  tabIndex: 412,
                  anchor: "97%",
                  allowBlank: false,
                  maxLength: 15,
                  minLength: 10,
                  maxLengthText: "The maximum length for this field is 15",
                },
              ],
            },
            {
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "combo",
                  fieldLabel: "Mode",
                  emptyText: "Choose Mode",
                  id: "baMode",
                  name: "baMode",
                  labelStyle: mandatory_label,
                  allowBlank: false,
                  mode: "local",
                  typeAhead: true,
                  forceSelection: true,
                  editable: true,
                  anchor: "97%",
                  store: new Ext.data.JsonStore({
                    fields: ["id", "name"],
                    data: [
                      { id: "1", name: "Direct" },
                      { id: "2", name: "Network" },
                    ],
                  }),
                  triggerAction: "all",
                  minChars: 2,
                  displayField: "name",
                  valueField: "id",
                  hiddenName: "baMode",
                  tabIndex: 403,
                  listeners: {
                    select: function (combo, record, index) {
                      var type = this.value;
                      if (type == "1") {
                        Ext.getCmp("bptnrId").hide();
                        Ext.getCmp("bptnrId").allowBlank = true;
                        Ext.getCmp('baIsPartner').show();
                      } else if (type == "2") {
                        Ext.getCmp('baIsPartner').hide();
                        Ext.getCmp("bptnrId").show();
                        Ext.getCmp("bptnrId").allowBlank = false;
                        businessPartnerStore.baseParams.type = Ext.getCmp('baType').getValue();
                        businessPartnerStore.baseParams.mode = this.value;
                        businessPartnerStore.load();
                      }
                    },
                  },
                },
              ],
            },
            {
              columnWidth: 0.33,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "combo",
                  fieldLabel: "Other Business Associate",
                  id: "bptnrId",
                  name: "bptnrId",
                  hidden: true,
                  tabIndex: 404,
                  hiddenName: "bptnrId",
                  anchor: "97%",
                  store: businessPartnerStore,
                  valueField: "id",
                  displayField: "bpName",
                  forceSelection: true,
                  triggerAction: "all",
                  typeAhead: true,
                  selectOnFocus: true,
                  mode: "local",
                  listeners: {
                    select: function (combo, record, index) {
                      Ext.getCmp('areaTypeId').setValue(record.data.networkType);

                    }
                  }
                },{
                  xtype: "hidden",
                  id: "areaTypeId",
                  hidden: true,
                }
              ],
            },{
              columnWidth: 0.33,
              layout: "form",
              frame: false,
              border: false,
              hideBorders: true,
              items: [{
                xtype: "checkbox",
                id: "baIsPartner",
                name: "baIsPartner",
                boxLabel: "Area Partner",
                allowBlank: true,
                inputValue: 1,
                hidden: true,
                listeners: {
                  check: function (checkbox, checked) {},
                },
              }
              ],
            },
          ],
        },
        {
          xtype: "textarea",
          height: 50,
          fieldLabel: "Addresss",
          id: "baAddress",
          name: "baAddress",
          tabIndex: 405,
          anchor: "99%",
          allowBlank: false,
          style: { "text-transform": "uppercase" },
          listeners: {
            change: function (field, newValue, oldValue) {
              field.setValue(newValue.toUpperCase());
            },
          },
        },
        {
          xtype: "panel",
          frame: false,
          border: false,
          layout: "column",
          items: [
            {
              columnWidth: 0.4,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              items: [
                {
                  xtype: "combo",
                  fieldLabel: STATE,
                  name: "st_id",
                  id: "bast_id",
                  hiddenName: "state",
                  anchor: "97%",
                  store: _state_store,
                  valueField: "st_ID",
                  tabIndex: 406,
                  displayField: "st_name",
                  forceSelection: true,
                  triggerAction: "all",
                  typeAhead: true,
                  allowBlank: false,
                  selectOnFocus: true,
                  mode: "local",
                  listeners: {
                    select: function () {
                      var value = Ext.getCmp("badst_Id").getValue();
                      console.log(
                        "District value after dst",
                        value,
                        this.value
                      );

                      district_store.baseParams.st_Id = this.value;
                      district_store.load();
                    },
                  },
                },
              ],
            },
            {
              columnWidth: 0.4,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "combo",
                  fieldLabel: DISTRICT,
                  id: "badst_Id",
                  name: "dst_Id",
                  tabIndex: 407,
                  hiddenName: "c_district",
                  anchor: "97%",
                  allowBlank: false,
                  store: district_store,
                  valueField: "dst_ID",
                  displayField: "dst_Name",
                  forceSelection: true,
                  triggerAction: "all",
                  typeAhead: true,
                  selectOnFocus: true,
                  mode: "local",
                },
              ],
            },
            {
              columnWidth: 0.2,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  fieldLabel: "Post Codes",
                  id: "baPincode",
                  anchor: "98%",
                  xtype: "textfield",
                  name: "baPincode",
                  allowBlank: false,
                  //vtype: 'phonespec',
                  tabIndex: 408,
                  listeners: {
                    change: function () {
                      if (!Ext.isEmpty(Ext.getCmp("baPincode").getValue())) {
                        Ext.getCmp("bamapbutton").enable();
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
          frame: false,
          border: false,
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
                  xtype: "textfield",
                  fieldLabel: GST,
                  id: "baGSTIN",
                  name: "baGSTIN",
                  tabIndex: 409,
                  anchor: "97%",
                  //allowBlank: false,
                  msgTarget: "under",
                  //gstText: 'Not a valid GST Number.',
                  //vtype: 'gst'
                },
              ],
            },
            {
              columnWidth: 0.5,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "textfield",
                  fieldLabel: PAN+" No",
                  id: "baPanNo",
                  tabIndex: 410,
                  name: "baPanNo",
                  anchor: "97%",
                  allowBlank: false,
                },
              ],
            },
          ],
        },
        {
          xtype: "panel",
          frame: false,
          border: false,
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
                  xtype: "textfield",
                  fieldLabel: "Contact Person",
                  id: "baContactPerson",
                  name: "baContactPerson",
                  tabIndex: 411,
                  anchor: "97%",
                  allowBlank: true,
                  hidden: true,
                  style: { "text-transform": "uppercase" },
                  listeners: {
                    change: function (field, newValue, oldValue) {
                      field.setValue(newValue.toUpperCase());
                    },
                  },
                },
              ],
            },
            
          ],
        },        
        {
          layout: "form",
          columnWidth: 0.35,
          border: false,
          items: [
            {
              xtype: "gmappanel",
              gmapType: "map",
              id: "bagooglemap",
              zoomLevel: 8,
              height: 280,
              minGeoAccuracy: 4,
              scaleControl: true,
              mapConfOpts: [
                "enableScrollWheelZoom",
                "enableDoubleClickZoom",
                "enableDragging",
              ],
              mapControls: ["GSmallMapControl", "GMapTypeControl"],
              setCenter: {
                lat: map_lat,
                lng: map_long,
              },
              repaint: function (zoomlevel) {
                var gmappanel = Ext.getCmp("bagooglemap");
                if (zoomlevel) {
                  gmappanel.zoomLevel = zoomlevel;
                  gmappanel.getMap().setZoom(zoomlevel);
                }
                gmappanel.onMapReady();
              },
              markers: my_marker,
            },
          ],
        },
        {
          layout: "form",
          columnWidth: 0.45,
          border: false,
          //style: 'margin-left:5px;',
          items: [
            {
              xtype: "fieldset",
              title: "Map Coordinates",
              border: false,
              //height: 200,
              autoHeight: true,
              items: [
                {
                  layout: "column",
                  border: false,
                  items: [
                    {
                      layout: "form",
                      columnWidth: 0.3,
                      border: false,
                      items: [
                        {
                          xtype: "button",
                          fieldLabel: "Coordinates",
                          tooltip: "Locate latitude and longitude",
                          icon: IMAGE_BASE_PATH + "/default/icons/map.png",
                          iconCls: "my-icon1",
                          id: "bamapbutton",
                          name: "bamapbutton",
                          disabled: true,
                          text: "Find Coordinates",
                          tabIndex: 414,
                          handler: function () {
                            Ext.getCmp("bagooglemap").clearMarkers();
                            my_marker = [];
                            my_marker.push({
                              geoCodeAddr: Ext.getCmp("baPincode").getValue(),
                              setCenter: true,
                              marker: {
                                title: "Click and Drag to Move Around",
                                draggable: true,
                              },
                              listeners: {
                                onFailure: function () {},
                                tilesloaded: function (markerAt) {
                                  console.log("markerAt", markerAt);
                                  Ext.getCmp("balatitude").setValue(
                                    markerAt.latLng.lat()
                                  );
                                  Ext.getCmp("balongitude").setValue(
                                    markerAt.latLng.lng()
                                  );
                                },
                                onSuccess: function (point) {
                                  console.log("point", point);
                                  Ext.getCmp("balatitude").setValue(
                                    point.latLng.lat()
                                  );
                                  Ext.getCmp("balongitude").setValue(
                                    point.latLng.lng()
                                  );
                                },
                                dragend: function (markerAt) {
                                  Ext.getCmp("balatitude").setValue(
                                    markerAt.latLng.lat()
                                  );
                                  Ext.getCmp("balongitude").setValue(
                                    markerAt.latLng.lng()
                                  );
                                },
                              },
                              icon: null,
                            });
                            Ext.getCmp("bagooglemap").addScaleControl();
                            Ext.getCmp("bagooglemap").clearMarkers();
                            Ext.getCmp("bagooglemap").addMarkers(my_marker);
                            Ext.defer(function () {
                              var point =
                                Ext.getCmp("bagooglemap").getCenterLatLng();
                              Ext.getCmp("balatitude").setValue(point.lat);
                              Ext.getCmp("balongitude").setValue(point.lng);
                              Ext.getCmp("bagooglemap").clearMarkers();
                              Ext.getCmp("bagooglemap").repaint(13);
                              Ext.getCmp("bagooglemap").addMarkers(my_marker);
                            }, 1200);
                          },
                        },
                      ],
                    },
                    {
                      layout: "form",
                      columnWidth: 0.35,
                      style: "padding-bottom:50px;",
                      border: false,
                      items: [
                        {
                          xtype: "textfield",
                          fieldLabel: "Latitude",
                          //style: 'padding-left:5px;',
                          allowBlank: false,
                          tabIndex: 415,
                          id: "balatitude",
                          name: "balatitude",
                          anchor: "95%",
                        },
                      ],
                    },
                    {
                      layout: "form",
                      border: false,
                      columnWidth: 0.35,
                      items: [
                        {
                          xtype: "textfield",
                          fieldLabel: "Longitude",
                          allowBlank: false,
                          tabIndex: 416,
                          id: "balongitude",
                          name: "balongitude",
                          anchor: "95%",
                        },
                      ],
                    },
                  ],
                },
              ],
            },
          ],
        },
      ],
    });
    return _partyPanel;
  };
    return {
      Cache: {},
      initAPContacts: function (type) {
        var typeName;
        switch (type) {
          case 1:
            typeName = "CP";
            break;
        
        }
        Application.Crm_ConsultingPartner.Cache.type = type;
        Application.Crm_ConsultingPartner.Cache.typeName = typeName;
        var panelId = "panelAssociatePartnerContact" + typeName;
        var contact_panel = Ext.getCmp(panelId);
        if (Ext.isEmpty(contact_panel)) {
          contact_panel = associatePartnerContactPanel(panelId, typeName);
          Application.UI.addTab(contact_panel);
          contact_panel.doLayout();
        } else {
          Application.UI.addTab(contact_panel);
          contact_panel.doLayout();
        }
      },
      ViewModeContact: function () {
        var contact_id = arguments[0];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        Ext.get(
          "downloadAPContactsIframe" + Application.Crm_ConsultingPartner.Cache.typeName
        ).dom.src =
          modURL +
          "&op=loadEditData&aaId=" +
          contact_id +
          "&tstamp=" +
          t_stamp +
          "&apikey=" +
          apikey;
      },
      insertData: function () {
        var _addContactData = Ext.getCmp(
          "formpanelAddContact" + Application.Crm_ConsultingPartner.Cache.typeName
        );
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        _addContactData.getForm().submit({
          waitMsg: "saving.... ",
          url: modURL + "&op=insertContactAndMoveToLead", 
          params: {
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          success: function (form, action) {
            var res = Ext.decode(action.response.responseText);
            if (res.success === true) {
              Application.example.msg("Success", res.msg);
              Ext.getCmp(
                "add_contactWindow" + Application.Crm_ConsultingPartner.Cache.typeName
              ).close();
              Ext.getCmp(
                "gridAssociatePartnerContacts" +
                  Application.Crm_ConsultingPartner.Cache.typeName
              )
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    if (Application.Crm_ConsultingPartner.Cache.upcrocid > 0) {
                      var gridPanel = Ext.getCmp(
                        "gridAssociatePartnerContacts" +
                          Application.Crm_ConsultingPartner.Cache.typeName
                      );
                      var index = gridPanel.store.find(
                        "aaId",
                        Application.Crm_ConsultingPartner.Cache.upcrocid
                      );
                      gridPanel.getSelectionModel().selectRow(index);
                    }
                  },
                });
            } else {
              Ext.MessageBox.alert("Notification", res.msg);
            }
          },
          failure: function (form, action) {
            var res = Ext.decode(action.response.responseText);
            Ext.MessageBox.alert("Notification", res.msg);
          },
        });
      },initAPEnquiry: function (type) {
        var typeName;
        switch (type) {
          case 1:
            typeName = "CP";
            break;
        }
        Application.Crm_ConsultingPartner.Cache.type = type;
        Application.Crm_ConsultingPartner.Cache.typeName = typeName;
        var panelId = "panelAssociatePartnerEnqiry" + typeName;
        var enquiry_panel = Ext.getCmp(panelId);
        if (Ext.isEmpty(enquiry_panel)) {
          enquiry_panel = associatePartnerEnquiryPanel(panelId, typeName);
          Application.UI.addTab(enquiry_panel);
          enquiry_panel.doLayout();
        } else {
          Application.UI.addTab(enquiry_panel);
          enquiry_panel.doLayout();
        }
      },ViewModeEnquiry: function () {
        var contact_id = arguments[0];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        Ext.get(
          "downloadAPEnquiryIframe" + Application.Crm_ConsultingPartner.Cache.typeName
        ).dom.src =
          modURL +
          "&op=loadEditData&aaId=" +
          contact_id +
          "&tstamp=" +
          t_stamp +
          "&apikey=" +
          apikey;
      },initAPLead: function (type) {
        var typeName;
        switch (type) {
          case 1:
            typeName = "CP";
            break;
        }
        Application.Crm_ConsultingPartner.Cache.type = type;
        Application.Crm_ConsultingPartner.Cache.typeName = typeName;
        var panelId = "panelAssociatePartnerLead" + typeName;
        var lead_panel = Ext.getCmp(panelId);
        if (Ext.isEmpty(lead_panel)) {
          lead_panel = associatePartnerLeadPanel(panelId, typeName);
          Application.UI.addTab(lead_panel);
          lead_panel.doLayout();
        } else {
          Application.UI.addTab(lead_panel);
          lead_panel.doLayout();
        }
      },ViewModeLead: function () {
        var contact_id = arguments[0];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        Ext.get(
          "downloadAPLeadIframe" + Application.Crm_ConsultingPartner.Cache.typeName
        ).dom.src =
          modURL +
          "&op=loadEditData&aaId=" +
          contact_id +
          "&tstamp=" +
          t_stamp +
          "&apikey=" +
          apikey;
      },initAPProspect: function (type) {
        var typeName;
        switch (type) {
          case 1:
            typeName = "CP";
            break;
        }
        Application.Crm_ConsultingPartner.Cache.type = type;
        Application.Crm_ConsultingPartner.Cache.typeName = typeName;
        var panelId = "panelAssociatePartnerProspect" + typeName;
        var prospect_panel = Ext.getCmp(panelId);
        if (Ext.isEmpty(prospect_panel)) {
          prospect_panel = associatePartnerProspectPanel(panelId, typeName);
          Application.UI.addTab(prospect_panel);
          prospect_panel.doLayout();
        } else {
          Application.UI.addTab(prospect_panel);
          prospect_panel.doLayout();
        }
      },ViewModeProspect: function () {
        var contact_id = arguments[0];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        Ext.get(
          "downloadAPProspectIframe" + Application.Crm_ConsultingPartner.Cache.typeName
        ).dom.src =
          modURL +
          "&op=loadEditData&aaId=" +
          contact_id +
          "&tstamp=" +
          t_stamp +
          "&apikey=" +
          apikey;
      },insertProspectData: function (areaAssociateId,approve) {
        var _addProspectData = Ext.getCmp(
          "formpanelAddProspect" + Application.Crm_ConsultingPartner.Cache.typeName
        );
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        _addProspectData.getForm().submit({
          waitMsg: "saving.... ",
          url: modURL + "&op=insertProspects",
          params: {
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          success: function (form, action) {
            var res = Ext.decode(action.response.responseText);
            if (res.success === true) {
              
              if(approve == '1'){
                console.log('approve'+approve);
                      if(!Ext.isEmpty(Ext.getCmp('businessLocation').getValue()) && !Ext.isEmpty(Ext.getCmp('as_st_id').getValue())){
                      console.log('if'+approve);
                  Ext.Ajax.request({
                        url: modURL + "&op=approveProspect",
                        method: "POST",
                        params: {
                          aaId: areaAssociateId
                        },
                        success: function (response) {
                          var tmp = Ext.decode(response.responseText);
                          if (tmp.success == true) {
                            Application.example.msg("Success", tmp.msg);
                            Ext.getCmp("gridAssociatePartnerProspect" + typeName).getStore().load();
                          } else {
                            Ext.Msg.alert("Error", tmp.msg);
                            
                          }
                        },
                        failure: function (response) {
                          var tmp = Ext.util.JSON.decode(response.responseText);
                          Ext.MessageBox.alert("Error", tmp.msg);
                        },
                      });
                    }else{
                      console.log('else');
                      Ext.MessageBox.alert("Notification", "Enter Missing Feilds and proceed");
                    }
                      
                    }
              Application.example.msg("Success", res.msg);
              Ext.getCmp(
                "add_prospectWindow" + Application.Crm_ConsultingPartner.Cache.typeName
              ).close();
              Ext.getCmp(
                "gridAssociatePartnerProspect" +
                  Application.Crm_ConsultingPartner.Cache.typeName
              )
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    if (Application.Crm_ConsultingPartner.Cache.upcrocid > 0) {
                      
                      var gridPanel = Ext.getCmp(
                        "gridAssociatePartnerProspect" +
                          Application.Crm_ConsultingPartner.Cache.typeName
                      );
                      var index = gridPanel.store.find(
                        "aaId",
                        Application.Crm_ConsultingPartner.Cache.upcrocid
                      );
                      gridPanel.getSelectionModel().selectRow(index);
                    }
                    
                  },
                });
            } else {
              Ext.MessageBox.alert("Notification", res.msg);
            }
          },
          failure: function (form, action) {
            var res = Ext.decode(action.response.responseText);
            Ext.MessageBox.alert("Notification", res.msg);
          },
        });
      },approveProspect: function (aaId,typeName) {
        var _addNewWindow = new Ext.Window({
          title: "Areas",
          layout: "fit",
          width: winsize.width * 0.6,
          height: winsize.height * 0.8,
          resizable: false,
          draggable: true,
          closable: true,
          modal: true,
          bodyStyle: { "background-color": "white" },
          items: [mapAreatoAreaAssociateGrid(aaId)],
          buttons: [
            {
              text: "Cancel",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              iconCls: "my-icon1",
              tabIndex: 511,
              handler: function () {
                _addNewWindow.close();
              },
            },
            {
              text: "Assign",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              tabIndex: 511,
              id: "assignBAbtn",
              handler: function () {
                var store_fields = Ext.getCmp('gridpanelProspectareas').getSelectionModel().getSelections();
                var isAssigned = 0;
                if (store_fields.length > 0) {
                  if (store_fields.length > 0) {
                    areaIds = [];
                    for (var i = 0; i < store_fields.length; i++) {
                      areaIds[i] = store_fields[i].data.id;
                      if(store_fields[i].data.areaConsultingPartnerFinal > 0){
                        isAssigned ++;
                      }
                      
                    }
                  }    
                  console.log('isAssigned',isAssigned);
                  if(isAssigned == 0){
                    Ext.Ajax.request({
                      url: modURL + "&op=mapAreaApproveProspect",
                      method: "POST",
                      params: {
                        aaId: aaId,
                        areaIds: Ext.encode(areaIds),
                      },
                      success: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success == true) {
                          Application.example.msg("Success", tmp.msg);
                          _addNewWindow.close();
                          Ext.getCmp("gridAssociatePartnerProspect" + typeName).getStore().load();
                        } else {
                          Ext.Msg.alert("Error", tmp.msg);
                        }
                      },
                      failure: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        Ext.MessageBox.alert("Error", tmp.msg);
                      },
                    });
                  }else{
                    Ext.MessageBox.alert("Notification", "Selected areas are already assigned.");
                  }            
                
              } else {
                Ext.Msg.alert(
                  "Notification",
                  "Select area and proceed."
                );
              }
              },
            },
          ],
          
        });
        _addNewWindow.doLayout();
        _addNewWindow.show();
        _addNewWindow.center();
        Ext.getCmp('gridpanelProspectareas').getStore().load({
          baseParams: {"aaId": aaId}
      });
      },initAPOppurtunity: function (type) {
        var typeName;
        switch (type) {
          case 1:
            typeName = "CP";
            break;
        }
        Application.Crm_ConsultingPartner.Cache.type = type;
        Application.Crm_ConsultingPartner.Cache.typeName = typeName;
        var panelId = "panelAssociatePartnerOppurtunity" + typeName;
        var oppurtunity_panel = Ext.getCmp(panelId);
        if (Ext.isEmpty(oppurtunity_panel)) {
          oppurtunity_panel = associatePartnerOppurtunityPanel(panelId, typeName);
          Application.UI.addTab(oppurtunity_panel);
          oppurtunity_panel.doLayout();
        } else {
          Application.UI.addTab(oppurtunity_panel);
          oppurtunity_panel.doLayout();
        }
      },ViewModeOppurtunity: function () {
        var contact_id = arguments[0];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        Ext.get(
          "downloadAPOppurtunityIframe" + Application.Crm_ConsultingPartner.Cache.typeName
        ).dom.src =
          modURL +
          "&op=loadEditData&aaId=" +
          contact_id +
          "&tstamp=" +
          t_stamp +
          "&apikey=" +
          apikey;
      },initAPIncubatee: function (type) {
        var typeName;
        switch (type) {
          case 1:
            typeName = "CP";
            break;
        }
        Application.Crm_ConsultingPartner.Cache.type = type;
        Application.Crm_ConsultingPartner.Cache.typeName = typeName;
        var panelId = "panelAssociatePartnerIncubatee" + typeName;
        var incubatee_panel = Ext.getCmp(panelId);
        if (Ext.isEmpty(incubatee_panel)) {
          incubatee_panel = associatePartnerIncubateePanel(panelId, typeName);
          Application.UI.addTab(incubatee_panel);
          incubatee_panel.doLayout();
        } else {
          Application.UI.addTab(incubatee_panel);
          incubatee_panel.doLayout();
        }
      },ViewModeIncubatee: function () {
        var contact_id = arguments[0];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        Ext.get(
          "downloadAPIncubateeIframe" + Application.Crm_ConsultingPartner.Cache.typeName
        ).dom.src =
          modURL +
          "&op=loadEditData&aaId=" +
          contact_id +
          "&tstamp=" +
          t_stamp +
          "&apikey=" +
          apikey;
      },approveOppurtunity: function (aaId,typeName) {
        var _addNewWindow = new Ext.Window({
          title: "Areas",
          layout: "fit",
          width: winsize.width * 0.6,
          height: winsize.height * 0.8,
          resizable: false,
          draggable: true,
          closable: true,
          modal: true,
          bodyStyle: { "background-color": "white" },
          items: [mapAreatoAreaAssociateOppurtunityGrid(aaId)],
          buttons: [
            {
              text: "Cancel",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              iconCls: "my-icon1",
              tabIndex: 511,
              handler: function () {
                _addNewWindow.close();
              },
            },
            {
              text: "Approve",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              tabIndex: 511,
              handler: function () {
                var isAssigned = 0;
                var store_fields = Ext.getCmp('gridpanelOppurtunityareas').getSelectionModel().getSelections();
                if (store_fields.length > 0) {
                  if (store_fields.length > 0) {
                    areaIds = [];
                    for (var i = 0; i < store_fields.length; i++) {
                      areaIds[i] = store_fields[i].data.id;
                      if(store_fields[i].data.areaConsultingPartnerFinal > 0){
                        isAssigned ++;
                      }
                    }
                  }  
                  if(isAssigned == 0){
                    Ext.Ajax.request({
                      url: modURL + "&op=approveOppurtunityArea",
                      method: "POST",
                      params: {
                        aaId: aaId,
                        areaIds: Ext.encode(areaIds),
                      },
                      success: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success == true) {
                          Application.example.msg("Success", tmp.msg);
                          _addNewWindow.close();
                          Ext.getCmp("gridAssociatePartnerOppurtunity" + typeName).getStore().load();
                        } else {
                          Ext.Msg.alert("Error", tmp.msg);
                        }
                      },
                      failure: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        Ext.MessageBox.alert("Error", tmp.msg);
                      },
                    });
                  }else{
                    Ext.Msg.alert(
                      "Notification",
                      "Remeove assigned areas from the selection."
                    );
                  }              
                
              } else {
                Ext.Msg.alert(
                  "Notification",
                  "Select area and proceed."
                );
              }
              },
            },
          ],
          
        });
        _addNewWindow.doLayout();
        _addNewWindow.show();
        _addNewWindow.center();
        Ext.getCmp('gridpanelOppurtunityareas').getStore().load({
          baseParams: {"aaId": aaId}
      });
      },trainingUpdatesTree: function(aaId,titleName){
        var win_id = "incubatee_training_structure";
        var win = Ext.getCmp(win_id);
        if (Ext.isEmpty(win)) {

            win = new Ext.Window({
                id: win_id,
                title: 'Training Modules for ' + titleName,
                layout: 'fit',
                width: 700,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                resizable: false,
                items: 
                new Ext.Panel({
                    height: 500,
                    border: false,
                    layout: 'border',
                    items: [training_tree_panel(aaId)]
                }), buttons: [
                  {
                        text: 'Expand All',
                        id:'xpandUser',
                        tabIndex: 501,
                        icon: IMAGE_BASE_PATH + "/default/icons/finascop_restart.png",
                        handler: function () {
                            if(!expandAll){
                            Ext.getCmp('user_permission_tree').expandAll();
                            Ext.getCmp('xpandUser').setText('Collapse All');
                            expandAll = true;
                          }else{
                            Ext.getCmp('user_permission_tree').collapseAll();
                            Ext.getCmp('user_permission_tree').getRootNode().expand();
                            Ext.getCmp('xpandUser').setText('Expand All');
                            expandAll = false;                          
                          }
                                //Ext.getCmp('user_permission_tree').getRootNode().reload();
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
                        WinMask = new Ext.LoadMask(Ext.getCmp('incubatee_training_structure').getEl());
                        WinMask.show();
                    }
                }
            });
        }

        win.doLayout();
        win.show(this);
        win.center();
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
  },initAPInstar: function (type) {
    var typeName;
    switch (type) {
      case 1:
        typeName = "CP";
        break;
    }
    Application.Crm_ConsultingPartner.Cache.type = type;
    Application.Crm_ConsultingPartner.Cache.typeName = typeName;
    var panelId = "panelAssociatePartnerInstar" + typeName;
    var instar_panel = Ext.getCmp(panelId);
    if (Ext.isEmpty(instar_panel)) {
      instar_panel = associatePartnerInstarPanel(panelId, typeName);
      Application.UI.addTab(instar_panel);
      instar_panel.doLayout();
    } else {
      Application.UI.addTab(instar_panel);
      instar_panel.doLayout();
    }
  },ViewModeInstar: function () {
    var contact_id = arguments[0];
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var apikey = _SESSION.apikey;
    Ext.get(
      "downloadAPInstarIframe" + Application.Crm_ConsultingPartner.Cache.typeName
    ).dom.src =
      modURL +
      "&op=loadEditData&aaId=" +
      contact_id +
      "&tstamp=" +
      t_stamp +
      "&apikey=" +
      apikey;
  },initConsultingPartner: function () {
    var panelId = "panelConsultingPartnerFinal";
    var listConsultingPartnerFinal = Ext.getCmp(panelId);
    if (Ext.isEmpty(listConsultingPartnerFinal)) {
      listConsultingPartnerFinal = consultingPartnerMainPanel(panelId);
      Application.UI.addTab(listConsultingPartnerFinal);
      listConsultingPartnerFinal.doLayout();
    } else {
      Application.UI.addTab(listConsultingPartnerFinal);
    }
  },ViewMode: function (data) {
    Ext.getCmp("property_grid_id").show();
    Ext.getCmp("panelConsultingPartnerFinalDetails").hide();
    Ext.getCmp("buttonconsultingPartnerCancel").hide();
    Ext.getCmp("buttonconsultingPartnerEdit").show();
    Ext.getCmp("buttonconsultingPartnerSave").hide();
    Ext.getCmp("tabpanelConsultingPartnerFinal").show();
    var propertygridPanel = Ext.getCmp("property_grid_id");
    console.log(data);
    Ext.getCmp("fbarhiddenid").setValue(data.baId);
    propertygridPanel.update(data);
  },saveConsultingPartnerFinalDetails: function () {
    var _editConsultingPartnerFinal = Ext.getCmp("panelConsultingPartnerFinalDetails");
    var cust_id = Ext.getCmp("fbarhiddenid").getValue();

    var t = new Date();
    var t_stamp = t.format("YmdHis");

    _editConsultingPartnerFinal.getForm().submit({
      waitMsg: "saving.... ",
      url: modURL,
      params: {
        op: "EditconsultingPartnerDetails",
        customer_id: cust_id,
        apikey: _SESSION.apikey,
        tstamp: t_stamp,
        //partytype: partytype
      },
      success: function (form, action) {
        var tmp = Ext.decode(action.response.responseText);
        //console.log("Temp is", tmp);
        if (tmp.success == true) {
          Application.example.msg("Notification", tmp.msg);
          Ext.getCmp("gridpanelconsultingPartner").getStore().reload();
          Ext.getCmp("panelConsultingPartnerFinalDetails").getForm().reset();
          Ext.getCmp("panelConsultingPartnerFinalDetails").hide();
          Ext.getCmp("tabpanelConsultingPartnerFinal").show();
          Ext.getCmp("buttonconsultingPartnerSave").hide();
          Ext.getCmp("buttonconsultingPartnerCancel").hide();
        } else {
          Ext.MessageBox.alert("Notification", tmp.msg);
        }
      },
      failure: function (form, action) {
        var tmp = Ext.decode(action.response.responseText);
        Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {});
      },
    });
  }
  };
  })();
  