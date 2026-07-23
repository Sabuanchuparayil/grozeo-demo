Application.Crm_FContact = (function () {
  var winsize = Ext.getBody().getViewSize();
  var modURL = "?module=crm_fcontact";
  var recs_per_page = 21;
  var WinMask;
  var imgpath = IMAGE_BASE_PATH;
  var onGridResize = function (cmp) {
    recs_per_page = 21;
  };
  var gridSelectionChangedcat = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp(
          "gridMarketingViewcontacts" + Application.Crm_FContact.Cache.typeName
        )
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp(
        "gridMarketingViewcontacts" + Application.Crm_FContact.Cache.typeName
      )
        .getSelectionModel()
        .getSelections()[0].data.id;
      Ext.getCmp(
        "tabpanelMarketingContact" + Application.Crm_FContact.Cache.typeName
      ).setActiveTab(0);
      Application.Crm_FContact.ViewMode(ID);
    } else {
      Application.Crm_FContact.Cache.crco_id = 0;
      Application.Crm_FContact.ViewMode(0);
    }
  };
  var marketingPanel = function (id, typeName) {
    var contactsPanel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      title: "Contacts",
      id: id,
      items: [gridViewContactDetails(typeName), panelContactt(typeName)],
    });
    return contactsPanel;
  };

  var tableCotactDetails = function () {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var apikey = _SESSION.apikey;
    var src =
      "?module=Crm_fContact&op=loadEditData&crco_id=" +
      Application.Crm_FContact.Cache.crco_id +
      "&tstamp=" +
      t_stamp +
      "&apikey=" +
      apikey;
    var contactsPanel = new Ext.Panel({
      id: "tableCrmContactsDataview" + Application.Crm_FContact.Cache.typeName,
      region: "center",
      width: winsize.width * 0.39,
      items: [
        {
          region: "center",
          defaults: {
            frame: false,
          },
          html:
            '<iframe id="downloadContactsIframe' +
            Application.Crm_FContact.Cache.typeName +
            '" name="downloadContactsIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
            src +
            '"; ></iframe>',
        },
      ],
    });
    return contactsPanel;
  };
  var panelContactt = function (typeName) {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var apikey = _SESSION.apikey;
    var src =
      "?module=Crm_fContact&op=loadEditData&crco_id=" +
      Application.Crm_FContact.Cache.crco_id +
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
      id: "tabpanelMarketingContact" + typeName,
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
        },
      ],
      fbar: [],
      listeners: {
        afterrender: function (component) {},
        tabchange: function (s, tab) {
          switch (tab.title) {
            case "Contact Details":
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
          "id",
          "crco_orgName",
          "crco_orgPincode",
          "crco_orgAddress",
          "crco_indContactperson",
          "crco_indMobile",
          "crco_orgContactNo",
          "crco_orgEmail",
          "contactType",
          "contactMode",
          "crmu_id",
          "crco_isActive",
          "crco_CreatedFrom",
          "crco_CreatedBy",
          "crco_gplace",
          "crco_location",
          "crco_CreatedOn",
        ]
      ),
      groupField: "",
      sortInfo: {
        field: "id",
        direction: "ASC",
      },
      root: "data",
      autoLoad: true,
      listeners: {
        load: function (store, record, options) {
          if (record.length > 0) {
            Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .selectRow(0);
          }
        },
        beforeload: function (store, e) {
          this.baseParams.typeName = typeName;
          this.baseParams.type = Application.Crm_FContact.Cache.type;
        },
      },
    });
  };
  var gridViewContactDetails = function (typeName) {
    var _jsonStoreContactGrid = contactGridStore(typeName);
    var _contactsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "crco_orgName",
        },
        {
          type: "string",
          dataIndex: "crco_indMobile",
        },
        {
          type: "string",
          dataIndex: "crco_orgEmail",
        },
      ],
    });
    _contactsGridFilter.remote = true;
    _contactsGridFilter.autoReload = true;
    var contactsGrid = new Ext.grid.GridPanel({
      id: "gridMarketingViewcontacts" + typeName,
      layout: "fit",
      store: _jsonStoreContactGrid,
      region: "center",
      frame: true,
      border: false,
      plugins: [_contactsGridFilter],
      loadMask: true,
      columns: [
        {
          header: "Contact Name",
          dataIndex: "crco_orgName",
          sortable: true,
          width: 200,
        },
        {
          header: "Contact Number",
          dataIndex: "crco_indMobile",
          sortable: true,
        },
        {
          header: "Email Address",
          dataIndex: "crco_orgEmail",
          sortable: true,
          width: 200,
        },
        {
          header: "Type",
          dataIndex: "contactType",
          sortable: true,
        },
        {
          header: "Mode",
          dataIndex: "contactMode",
          sortable: true,
          hideable: true,
          hidden: true,
        },
        {
          header: "Created From",
          dataIndex: "crco_CreatedFrom",
          sortable: true,
          hideable: true,
        },
        {
          header: "Created By",
          dataIndex: "crco_CreatedBy",
          sortable: true,
          hideable: true,
        },{
          header: "Created On",
          dataIndex: "crco_CreatedOn",
          sortable: true,
          hideable: true,
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
            contactform();
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
          selectionchange: gridSelectionChangedcat,
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
            var crco_id = Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .getSelections()[0].data.id;
            var crmu_id = Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .getSelections()[0].data.crmu_id;
            var crco_isActive = Ext.getCmp(
              "gridMarketingViewcontacts" + typeName
            )
              .getSelectionModel()
              .getSelections()[0].data.crco_isActive;
            if (crco_isActive == 0) {
              contactform(crco_id);
            } else {
              Ext.MessageBox.alert(
                "Notification",
                "Verified Contacts not possible to edit"
              );
            }
          },
        },
        {
          text: "Verify",
          handler: function () {
            var crco_id = Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .getSelections()[0].data.id;
            var crco_isActive = Ext.getCmp(
              "gridMarketingViewcontacts" + typeName
            )
              .getSelectionModel()
              .getSelections()[0].data.crco_isActive;
            var crmu_id = Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .getSelections()[0].data.crmu_id;
            var _customerId = crco_id;
            Ext.MessageBox.confirm(
              "Confirm",
              "Is it verified?",
              function (btn, text) {
                if (btn == "yes") {
                  Ext.Ajax.request({
                    waitMsg: "Processing",
                    method: "POST",
                    url: modURL + "&op=verifyContact",
                    params: {
                      id: _customerId,
                    },
                    success: function (response) {
                      var tmp = Ext.util.JSON.decode(response.responseText);
                      if (tmp.success === true) {
                        Application.example.msg("Notification", tmp.msg);
                        Ext.getCmp("gridMarketingViewcontacts" + typeName)
                          .getStore()
                          .reload();
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
          },
        },
        {
          text: "Move to Lead",
          hidden:true,
          handler: function () {
            var crco_id = Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .getSelections()[0].data.id;
            var crco_isActive = Ext.getCmp(
              "gridMarketingViewcontacts" + typeName
            )
              .getSelectionModel()
              .getSelections()[0].data.crco_isActive;
            var crmu_id = Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .getSelections()[0].data.crmu_id;
            if (crco_isActive == 1 && crmu_id != 6) {
              Ext.MessageBox.confirm(
                "Confirm",
                "Do you want to move this as Lead?",
                function (btn, text) {
                  if (btn == "yes") {
                    Application.Crm_FContact.insertCommunication(crco_id);
                  }
                }
              );
            } else {
              if (crmu_id == 6) {
                Ext.MessageBox.alert(
                  "Notification",
                  "Contact is already moved to Lead."
                );
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Contact is not qualified."
                );
              }
            }
          },
        },{
          text: "Assign Area",
          handler: function () {
            var crco_id = Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .getSelections()[0].data.id;
            var crco_isActive = Ext.getCmp(
              "gridMarketingViewcontacts" + typeName
            )
              .getSelectionModel()
              .getSelections()[0].data.crco_isActive;
            var crmu_id = Ext.getCmp("gridMarketingViewcontacts" + typeName)
              .getSelectionModel()
              .getSelections()[0].data.crmu_id;
            if (crco_isActive == 1 && crmu_id != 6) {
              Application.Crm_FContact.mapAreaToContact(crco_id,typeName);
            } else {
              if (crmu_id == 6) {
                Ext.MessageBox.alert(
                  "Notification",
                  "Contact is already moved to Lead."
                );
              } else {
                Ext.MessageBox.alert(
                  "Notification",
                  "Contact is not qualified."
                );
              }
            }
          },
        }
      ],
    });
    fcontactsActionMenu.showAt(e.getXY());
  };
  var contactToLead = function (id, activeStatus) {
    Ext.Ajax.request({
      waitMsg: "Processing",
      method: "POST",
      url: modURL + "&op=convertToLead",
      params: {
        crco_id: id,
        status: activeStatus,
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        if (tmp.success === true) {
          Ext.getCmp(
            "gridMarketingViewcontacts" +
              Application.Crm_FContact.Cache.typeName
          )
            .getStore()
            .reload();
        } else {
          Ext.MessageBox.alert("Error", "Invalid data");
        }
      },
      failure: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        Ext.MessageBox.alert("Error", "Error occurred");
      },
    });
  };
  var contactUserDetails = function (id, currentStatus) {
    var contactid = id;
    Ext.Ajax.request({
      waitMsg: "Processing",
      method: "POST",
      url: modURL + "&op=getUserid",
      params: {
        crco_id: id,
        status: currentStatus,
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        if (tmp.success === true) {
          Ext.getCmp(
            "gridMarketingViewcontacts" +
              Application.Crm_FContact.Cache.typeName
          )
            .getStore()
            .load({
              callback: function (record, options, success) {
                var gridPanel = Ext.getCmp(
                  "gridMarketingViewcontacts" +
                    Application.Crm_FContact.Cache.typeName
                );
                var index = gridPanel.store.find("crco_id", id);
                gridPanel.getSelectionModel().selectRow(index);
                //loadContactWindow(contactid);
                contactform(contactid);
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

  var referenceStores = function () {
    var _referenceStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getReference",
      method: "post",
      fields: ["referers_id", "referers_contact_id"],
      remoteSort: true,
    });
    return _referenceStore;
  };
  var storeRetailCategory = function () {
    var _referenceStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getRetailCategory",
      method: "post",
      fields: ["business_category_id", "business_category_name"],
      remoteSort: true,
    });
    return _referenceStore;
  };
  var storeContactType = function () {
    var _referenceStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getContactType",
      method: "post",
      fields: ["id", "name"],
      remoteSort: true,
      listeners: {
        beforeload: function () {
          this.baseParams.type = Application.Crm_FContact.Cache.type;
        },
      },
    });
    return _referenceStore;
  };
  function initAutocompleteText() {
    var input = document.getElementById("crco_location");
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
            document.getElementById("crco_orgCountry").value =
              place.address_components[i].long_name;
            break;
          case "street_number":
            document.getElementById("crco_orgAddress").value =
              place.address_components[i].long_name;
            break;
          case "route":
            document.getElementById("crco_groute").value =
              place.address_components[i].long_name;
            break;
          case "postal_code_suffix":
            document.getElementById("crco_gpostsuffix").value =
              place.address_components[i].long_name;
            break;
          case "locality":
            document.getElementById("crco_glocality").value =
              place.address_components[i].long_name;
            break;
          case "administrative_area_level_1":
            document.getElementById("crco_gplace").value =
              place.address_components[i].long_name;
            break;
        }
      }

      document.getElementById("crco_orgAddress").value =
        place.formatted_address;
      document.getElementById("glatitude").value = latitude;
      document.getElementById("glongitude").value = longitude;
    });
  }
  var contactform = function (contactid) {
    var retailCategoryStore = storeRetailCategory();
    var contactTypeStore = storeContactType();
    var contactform = new Ext.Window({
      width: 500,
      autoHeight: true,
      id: "add_contactWindow" + Application.Crm_FContact.Cache.typeName,
      shadow: false,
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      modal: true,
      closable: false,
      items: [
        new Ext.form.FormPanel({
          id: "formpanelAddContact" + Application.Crm_FContact.Cache.typeName,
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
                      id: "id",
                      hidden: true,
                    },
                    {
                      xtype: "textfield",
                      id: "statusABOhidden",
                      hidden: true,
                    },
                    {
                      layout: "column",
                      fieldLabel: "Store Name",
                      maxLength: 250,
                      tabIndex: 300,
                      xtype: "textfield",
                      anchor: "98%",
                      allowBlank: false,
                      id: "crco_orgName",
                      name: "crco_orgName",
                      hideBorders: true,
                      border: false,
                    },
                  ],
                },
                {
                  layout: "form",
                  columnWidth: 0.5,
                  border: false,
                  items: [
                    {
                      fieldLabel: "Contact Type",
                      xtype: "lovcombo",
                      displayField: "name",
                      valueField: "id",
                      mode: "local",
                      id: "addcrco_type",
                      hiddenName: "addcrco_type",
                      typeAhead: true,
                      hidden: true,
                      triggerAction: "all",
                      lazyRender: true,
                      tabIndex: 301,
                      anchor: "98%",
                      minChars: 2,
                      store: contactTypeStore,
                      editable: true,
                    },
                    {
                      xtype: "combo",
                      fieldLabel: "Contact Type",
                      emptyText: "Contact Type",
                      id: "crco_type",
                      name: "crco_type",
                      tabIndex: 301,
                      labelStyle: mandatory_label,
                      mode: "local",
                      typeAhead: true,
                      hidden: true,
                      forceSelection: true,
                      editable: true,
                      anchor: "98%",
                      store: contactTypeStore,
                      triggerAction: "all",
                      minChars: 2,
                      displayField: "name",
                      valueField: "id",
                      hiddenName: "crco_type",
                      listeners: {
                        select: function () {},
                      },
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
                      fieldLabel: "Location",
                      tabIndex: 302,
                      id: "crco_location",
                      name: "crco_location",
                      anchor: "98%",
                      allowBlank: false,
                      maxLength: 1000,
                      listeners: {
                        focus: function () {
                          initAutocompleteText();
                        },
                      },
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
                      fieldLabel: "Post Code",
                      tabIndex: 303,
                      id: "crco_orgPincode",
                      name: "crco_orgPincode",
                      anchor: "98%",
                      allowBlank: false,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Country",
                      tabIndex: 303,
                      id: "crco_orgCountry",
                      name: "crco_orgCountry",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Route",
                      tabIndex: 303,
                      id: "crco_groute",
                      name: "crco_groute",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Locality",
                      tabIndex: 303,
                      id: "crco_glocality",
                      name: "crco_glocality",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Place",
                      tabIndex: 303,
                      id: "crco_gplace",
                      name: "crco_gplace",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Latitude",
                      tabIndex: 303,
                      id: "glatitude",
                      name: "glatitude",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Longitude",
                      tabIndex: 303,
                      id: "glongitude",
                      name: "glongitude",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                  ],
                },
                {
                  layout: "form",
                  columnWidth: 1.0,
                  border: false,
                  items: [
                    {
                      xtype: "textfield",
                      fieldLabel: "Address",
                      tabIndex: 304,
                      id: "crco_orgAddress",
                      name: "crco_orgAddress",
                      anchor: "98%",
                      allowBlank: false,
                      maxLength: 1000,
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
                      fieldLabel: "Contact Person",
                      tabIndex: 305,
                      id: "crco_indContactperson",
                      name: "crco_indContactperson",
                      anchor: "98%",
                      allowBlank: false,
                      maxLength: 100,
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
                      fieldLabel: "Contact Number",
                      tabIndex: 306,
                      id: "crco_indMobile",
                      name: "crco_indMobile",
                      anchor: "98%",
                      allowBlank: false,
                      maxLength: 100,
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
                      fieldLabel: "Telephone No",
                      tabIndex: 307,
                      id: "crco_orgContactNo",
                      name: "crco_orgContactNo",
                      anchor: "98%",
                      allowBlank: false,
                      maxLength: 100,
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
                      fieldLabel: "Email",
                      tabIndex: 308,
                      id: "crco_orgEmail",
                      name: "crco_orgEmail",
                      anchor: "98%",
                      vtype: "email",
                      allowBlank: false,
                      maxLength: 100,
                    },
                  ],
                },
                {
                  layout: "form",
                  columnWidth: 0.5,
                  border: false,
                  items: [
                    {
                      xtype: "checkbox",
                      hidden: true,
                      id: "retailCategory_isOthers",
                      tabIndex: 102,
                      inputValue: 1,
                      anchor: "99%",
                      name: "retailCategory_isOthers",
                      labelAlign: "right",
                      boxLabel: "Others",
                      listeners: {
                        check: function (checkbox, checked) {
                          if (checked == true) {
                            Ext.getCmp("retailCategory")
                              .getStore()
                              .load({
                                params: {
                                  business_category_ingroup: '0',
                                },
                              });
                          } else {
                            Ext.getCmp("retailCategory")
                              .getStore()
                              .load({
                                params: {
                                  business_category_ingroup: '1',
                                },
                              });
                          }
                        },
                      },
                    },
                  ],
                },
                {
                  layout: "form",
                  columnWidth: 0.5,
                  border: false,
                  items: [
                    {
                      fieldLabel: "Business Category",
                      xtype: "combo",
                      displayField: "business_category_name",
                      valueField: "business_category_id",
                      mode: "local",
                      id: "retailCategory",
                      hiddenName: "retailCategory",
                      typeAhead: true,
                      triggerAction: "all",
                      lazyRender: true,
                      tabIndex: 309,
                      anchor: "98%",
                      minChars: 2,
                      store: retailCategoryStore,
                      editable: true,
                      listeners: {
                        select: function () {
                          if (Ext.getCmp("retailCategory").getValue() == -1) {
                            Ext.getCmp("retailCategory").getStore().removeAll();
                            Ext.getCmp("retailCategory").setValue('');
                            Ext.getCmp("retailCategory_isOthers").show();
                            Ext.getCmp("retailCategory_isOthers").setValue(1);     
                            Ext.getCmp("retailCategory")
                              .getStore()
                              .load({
                                params: {
                                  business_category_ingroup: '0',
                                },
                              });
                          }
                        },
                      },
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
          tabIndex: 311,
          handler: function () {
            Ext.getCmp(
              "gridMarketingViewcontacts" +
                Application.Crm_FContact.Cache.typeName
            )
              .getStore()
              .load();
            contactform.close();
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
            var _customerId = Ext.getCmp("id").getValue();
            var _addContactData = Ext.getCmp(
              "formpanelAddContact" + Application.Crm_FContact.Cache.typeName
            );

            contactInsertion(_customerId);
          },
        },
      ],
    });
    if (contactid > 0) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var regFee_form = Ext.getCmp(
        "formpanelAddContact" + Application.Crm_FContact.Cache.typeName
      ).getForm();
      regFee_form.load({
        params: {
          EditStatus: 1,
          _edit_crco_id: contactid,
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
          Ext.getCmp("crco_type").getStore().load();
          Ext.getCmp("crco_type").setValue(tmp.data.crco_type);
          Ext.getCmp("crco_type").setRawValue(tmp.data.crco_typeName);
          Ext.getCmp("crco_type").show();
          Ext.getCmp("crco_type").allowBlank = false;
          if(tmp.data.retailCategory_isOthers == 1){
            Ext.getCmp("retailCategory_isOthers").show();
          }
        },
        failure: function (form, action) {},
      });
    } else {
      Ext.getCmp("addcrco_type").show();
      Ext.getCmp("addcrco_type").allowBlank = false;
    }
    contactform.doLayout();
    contactform.show();
    contactform.center();
    return contactform;
  };

  var contactInsertion = function (_customerId) {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var _addeditform = Ext.getCmp(
      "formpanelAddContact" + Application.Crm_FContact.Cache.typeName
    );
    if (_customerId > 0) {
      _customerId = _customerId;
      Application.Crm_FContact.Cache.upcrocid = _customerId;
    } else {
      _customerId = "-";
      Application.Crm_FContact.Cache.upcrocid = 0;
    }
    if (_addeditform.getForm().isValid()) {
      Application.Crm_FContact.insertData();
    } else {
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  var loadContactWindow = function (contactid) {
    var toolbar = new Ext.Toolbar({
      layout: "column",
      items: [
        {
          columnWidth: 0.8,
          anchor: "95%",
          html:
            "Company:" +
            _SESSION.finascop_current_company +
            "  Branch: " +
            _SESSION.current_branch,
        },
        {
          text: "Cancel",
          anchor: "90%",
          columnWidth: 0.1,
          xtype: "button",
          icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
          tabIndex: 33,
          handler: function () {
            Ext.getCmp(
              "gridMarketingViewcontacts" +
                Application.Crm_FContact.Cache.typeName
            )
              .getStore()
              .load();
            contactWindowforMarketing.close();
          },
        },
        {
          text: "Save",
          anchor: "95%",
          columnWidth: 0.1,
          bodyStyle: { margin: "0px 0px 0px 140px" },
          xtype: "button",
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          tabIndex: 32,
          handler: function () {
            var _customerId = Ext.getCmp("id").getValue();
            var _addContactData = Ext.getCmp(
              "formpanelAddContact" + Application.Crm_FContact.Cache.typeName
            );

            contactInsertion(_customerId);
          },
        },
      ],
    });
    var contactWindowforMarketing = Ext.getCmp(
      "contactWindowforMarketing" + Application.Crm_FContact.Cache.typeName
    );
    if (Ext.isEmpty(contactWindowforMarketing)) {
      contactWindowforMarketing = new Ext.Window({
        id:
          "contactWindowforMarketing" + Application.Crm_FContact.Cache.typeName,
        title: "Contact Details",
        layout: "fit",
        width: winsize.width * 0.6,
        height: 500,
        autoScroll: true,
        shadow: false,
        modal: true,
        resizable: false,
        items: [contactform()],
        bbar: toolbar,
      });
    }
    if (contactid > 0) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var regFee_form = Ext.getCmp(
        "formpanelAddContact" + Application.Crm_FContact.Cache.typeName
      ).getForm();
      regFee_form.load({
        params: {
          EditStatus: 1,
          _edit_crco_id: contactid,
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        url: modURL + "&op=loadEditData",
        waitMsg: "Loading...",
        success: function (form, action) {
          eval("var tmp=" + action.response.responseText);
          var tmp = Ext.decode(action.response.responseText);
        },
        failure: function (form, action) {},
      });
    }
    contactWindowforMarketing.doLayout();
    contactWindowforMarketing.show();
    contactWindowforMarketing.center();
  };
  var communication_panel = function () {
    var record = Ext.getCmp(
      "gridMarketingViewcontacts" + Application.Crm_FContact.Cache.typeName
    )
      .getSelectionModel()
      .getSelected();
    var crco_id = record.data.crco_id;
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    Ext.Ajax.request({
      url: modURL + "&op=getCommunication",
      method: "POST",
      params: {
        crco_id: crco_id,
        apikey: _SESSION.apikey,
        tstamp: t_stamp,
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        var propertygridPanel = Ext.getCmp("PanelCommunicationId");
        propertygridPanel.update(tmp.data);
      },
      failure: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        Ext.MessageBox.alert("Error", tmp.msg);
      },
    });
  };
  var addFile = function () {
    var albumBucketName = Ext.getCmp("s3_albumBucketName").getValue();
    var bucketRegion = Ext.getCmp("s3_bucketRegion").getValue();
    AWS.config.update({
      region: bucketRegion,
      credentials: new AWS.Credentials(
        Ext.getCmp("s3_accessKey").getValue(),
        Ext.getCmp("s3_secretKey").getValue(),
        null
      ),
    });
    var s3 = new AWS.S3({
      apiVersion: "2006-03-01",
      params: { Bucket: albumBucketName },
    });
    var files = document.getElementById(
      "fileuploadfieldMarketingAddcommunicationAttachfile-file"
    ).files;
    if (!files.length) {
      return alert("Please choose a file to upload first.");
    }
    var file = files[0];
    var actualfileName = file.name;
    var file_Name = JSON.stringify(actualfileName).slice(1, -1);
    var fileExt = file_Name.split(".").pop();
    var fileName = uuidv4();
    fileName = fileName + "." + fileExt;
    Ext.getCmp("s3_filename").setValue(fileName);
    s3.upload(
      {
        Key: fileName,
        Body: file,
        ACL: "public-read",
      },
      function (err, data) {
        if (err) {
          var img_src = Ext.BLANK_IMAGE_URL;
          return Ext.Msg.alert(
            "Notification",
            "There was an error uploading your photo: " + err.message
          );
        }
        if (!Ext.isEmpty(data.Location)) {
          Ext.MessageBox.show({
            msg: "Uploading, Please wait...",
            progressText: "",
            width: 300,
            wait: true,
            waitConfig: {
              duration: 5000,
              increment: 15,
              text: "Saving...",
              scope: this,
              fn: function () {
                Ext.getCmp("supporting").show();
                Ext.getCmp("supporting").disable();
                Ext.MessageBox.hide();
                Application.example.msg(
                  "Notification",
                  "File uploaded successfully."
                );
              },
            },
          });
          Ext.getCmp("s3filepath").setValue(data.Location);
        }
      }
    );
  };
  var areaStore = function (crco_id) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAreaForContact",
        method: "post",
      }),
      fields: ["id","areaName","areaLocation","areaLatitude","distance"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function () {
          this.baseParams.crco_id = crco_id;
        },
        load: function () {
          Ext.getCmp("gridpanelContactareas").getSelectionModel().selectRow(0);
        },
      },
    });
    return _Store;
  };
  var mapAreatoContacGrid = function(crco_id){
    var _baStore = areaStore(crco_id);
    var _bagridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _baStore,
      autoScroll: true,
      width: 400,
      height: 250,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelContactareas",
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
        },
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {},
      },
      tbar: [
      ],
    });
    return _bagridPanel;
  };
  return {
    Cache: {},
    initfContacts: function (type) {
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
      Application.Crm_FContact.Cache.type = type;
      Application.Crm_FContact.Cache.typeName = typeName;
      var panelId = "panelMarketingfContact" + typeName;
      var contact_panel = Ext.getCmp(panelId);
      if (Ext.isEmpty(contact_panel)) {
        contact_panel = marketingPanel(panelId, typeName);
        Application.UI.addTab(contact_panel);
        contact_panel.doLayout();
      } else {
        Application.UI.addTab(contact_panel);
        contact_panel.doLayout();
      }
    },
    ViewMode: function () {
      var contact_id = arguments[0];
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var is_UnAttended = arguments[1];
      var apikey = _SESSION.apikey;
      Ext.get(
        "downloadContactsIframe" + Application.Crm_FContact.Cache.typeName
      ).dom.src =
        modURL +
        "&op=loadEditData&crco_id=" +
        contact_id +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey;
    },
    insertCommunication: function (contactId) {
      var record = Ext.getCmp(
        "gridMarketingViewcontacts" + Application.Crm_FContact.Cache.typeName
      )
        .getSelectionModel()
        .getSelected();
      var tappanell = Ext.getCmp(
        "tabpanelMarketingContact" + Application.Crm_FContact.Cache.typeName
      );

      var t = new Date();
      var t_stamp = t.format("YmdHis");
      Ext.Ajax.request({
        waitMsg: "Processing",
        method: "POST",
        url: modURL + "&op=moveToLead",
        params: {
          contactId: contactId,
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        success: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          if (tmp.success == true) {
            Application.example.msg("Success", tmp.msg);
            Ext.getCmp(
              "gridMarketingViewcontacts" +
                Application.Crm_FContact.Cache.typeName
            )
              .getStore()
              .reload();
            Ext.getCmp(
              "tabpanelMarketingContact" +
                Application.Crm_FContact.Cache.typeName
            ).setActiveTab(0);
            Application.Crm_FContact.Cache.crco_id = 0;
          } else {
            Ext.MessageBox.alert("Notification", tmp.msg);
          }
        },
        failure: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert("Error", tmp.msg);
        },
      });
      //            }
    },
    insertData: function () {
      var _addContactData = Ext.getCmp(
        "formpanelAddContact" + Application.Crm_FContact.Cache.typeName
      );
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      _addContactData.getForm().submit({
        waitMsg: "saving.... ",
        url: modURL + "&op=insertContactAndMoveToLead", // insertAddContactData
        params: {
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        success: function (form, action) {
          var res = Ext.decode(action.response.responseText);
          if (res.success === true) {
            Application.example.msg("Success", res.msg);
            Ext.getCmp(
              "add_contactWindow" + Application.Crm_FContact.Cache.typeName
            ).close();
            Ext.getCmp(
              "gridMarketingViewcontacts" +
                Application.Crm_FContact.Cache.typeName
            )
              .getStore()
              .load({
                callback: function (record, options, success) {
                  if (Application.Crm_FContact.Cache.upcrocid > 0) {
                    var gridPanel = Ext.getCmp(
                      "gridMarketingViewcontacts" +
                        Application.Crm_FContact.Cache.typeName
                    );
                    var index = gridPanel.store.find(
                      "crco_id",
                      Application.Crm_FContact.Cache.upcrocid
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
    },mapAreaToContact: function (crco_id,typeName) {
      var _addNewWindow = new Ext.Window({
        title: "Assign Area to Contact",
        layout: "fit",
        width: winsize.width * 0.6,
        height: winsize.height * 0.8,
        resizable: false,
        draggable: true,
        closable: true,
        modal: true,
        bodyStyle: { "background-color": "white" },
        items: [mapAreatoContacGrid(crco_id)],
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
              var areaId = Ext.getCmp("gridpanelContactareas")
              .getSelectionModel()
              .getSelections()[0].data.id;
            if (areaId > 0) {
              Ext.Ajax.request({
                url: modURL + "&op=upgradeToLead",
                method: "POST",
                params: {
                  contactId: crco_id,
                  areaId: areaId,
                },
                success: function (response) {
                  var tmp = Ext.decode(response.responseText);
                  if (tmp.success == true) {
                    Application.example.msg("Success", tmp.msg);
                    _addNewWindow.close();
                    Ext.getCmp("gridMarketingViewcontacts" + typeName).getStore().load();
                  } else {
                    Ext.Msg.alert("Error", tmp.msg);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", tmp.msg);
                },
              });
            } else {
              Ext.Msg.alert(
                "Notification",
                "Select an area and proceed."
              );
            }
            },
          },
        ],
        
      });
      _addNewWindow.doLayout();
      _addNewWindow.show();
      _addNewWindow.center();
      Ext.getCmp('gridpanelContactareas').getStore().load({
        baseParams: {"crco_id": crco_id}
    });
    }
  };
})();
