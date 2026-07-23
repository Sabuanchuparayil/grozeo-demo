Application.Crm_FLead = (function () {
  var winsize = Ext.getBody().getViewSize();
  var modURL = "?module=crm_leads";
  var recs_per_page = 21;
  var WinMask;
  var imgpath = IMAGE_BASE_PATH;

  function updatePagination(cmp) {
    recs_per_page = finascop_update_recs_per_page(cmp);
  }
  var crmLeadGridStore = function () {
    return new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=getLeadDetails",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "",
          root: "data",
        },
        [
          "id","assignedROName","assignedRO",
          "crle_orgName","areaId",
          "crle_orgEmail",
          "crle_indMobile",
          "crle_orgPincode",
          "crle_orgCountry",
          "crle_isActive",
          "crle_groute",
          "crle_glocality",
          "crle_gplace",
          "isLeadAreaAssigned",
          "baName",
          "areaName",
          "crle_CreatedFrom",
          "crle_CreatedBy","crle_CreatedOn"
        ]
      ),
      groupField: "",
      sortInfo: {
        field: "crle_orgName",
        direction: "ASC",
      },
      root: "data",
      autoLoad: true,
      //    remoteSort: true,
      listeners: {
        load: function (store, record, options) {
          if (record.length > 0) {
            Ext.getCmp(
              "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
            )
              .getSelectionModel()
              .selectRow(0);
          }
        },
        beforeload: function (store, e) {
          this.baseParams.typeName = Application.Crm_FLead.Cache.typeName;
          this.baseParams.type = Application.Crm_FLead.Cache.type;
        },
      },
    });
  };
  var crmLeadsCommunicationPanel = function () {
    var _communicationPanel = new Ext.Panel({
      region: "center",
      id: "LeadsPanelCommunicationId" + Application.Crm_FLead.Cache.typeName,
      height: winsize.height * 0.55,
      tpl: new Ext.XTemplate(
        '<div class="cdetails-outer no-border" style="width:100%; margin:15px auto;"><h3 class="history"></h3><ul class="anexure">',
        '<tpl for=".">',
        '<div class="details-outer">',
        '<table style="width:100%;">',
        "<tr>",
        '<td ><span class="field" style="color:grey;font-weight:bold"><img src = {calender}></span><span class="crmdate">{date_and_time}</span></td>',
        '<td ><span class="field" style="color:grey;font-weight:bold;align:right;"><img src={crmm_name}></span><span class="crmname" >{resource}</span></td>',
        "</tr>",
        "<tpl if=\"remark != ''\">",
        "<tr>",
        '<td colspan="2" style= "padding-top : 5px">',
        '<span span class="crmtext">{remark}</span>',
        "</td>",
        "</tr>",
        "</tpl>",
        "<tr>",
        '<td style= "padding-top : 5px"><span class="crmresponse">{response} {crsc_ScheduleDate}</span> </td>',
        "</tr>",
        '<td colspan="2">',
        "<hr></td></tr>",
        "</table>",
        "</div></li>",
        "</tpl>",
        "</ul></div>",
        "<style>.field{ padding-right: 10px; }</style>"
      ),
    });
    return _communicationPanel;
  };

  var htmlpanelfun = function () {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var apikey = _SESSION.apikey;
    var src =
      "?module=crm_leads&op=loadEditLeadData&id=" +
      Application.Crm_FLead.Cache.id +
      "&tstamp=" +
      t_stamp +
      "&apikey=" +
      apikey;
    var _htmlpanel = new Ext.Panel({
      id: "htmlid" + Application.Crm_FLead.Cache.typeName,
      region: "center",
      frame: false,
      border: false,
      bodyStyle: { "background-color": "white" },
      width: winsize.width * 0.39,
      cls: "left_side_panel",
      items: [
        {
          html:
            '<iframe id="downloadIframelead' +
            Application.Crm_FLead.Cache.typeName +
            '" name="downloadIframelead" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
            src +
            '"; ></iframe>',
        },
      ],
    });
    return _htmlpanel;
  };
  var panelLead = function () {
    var panel = new Ext.TabPanel({
      id: "tabpanelMarketingLead" + Application.Crm_FLead.Cache.typeName,
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
          title: "Lead Details",
          id:
            "tabpanelMarketingLeadAddlead" +
            Application.Crm_FLead.Cache.typeName,
          layout: "border",
          items: [htmlpanelfun()],
        },
      ],
      listeners: {
        afterrender: function (component) {
          Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .selectRow(0);
          var _tabpanel = Ext.getCmp(
            "tabpanelMarketingLead" + Application.Crm_FLead.Cache.typeName
          );
          _tabpanel.setActiveTab(0);
          Ext.getCmp("htmlid" + Application.Crm_FLead.Cache.typeName).show();
        },
        tabchange: function (s, tab) {
          var _current = Application.Crm_FLead.Cache.status;
          switch (tab.title) {
            case "Lead Details":
              if (_current == "UnAttended") {
                Ext.getCmp(
                  "htmlid" + Application.Crm_FLead.Cache.typeName
                ).show();
              } else {
                Ext.getCmp(
                  "htmlid" + Application.Crm_FLead.Cache.typeName
                ).show();
              }
              break;
          }
        },
      },
    });
    return panel;
  };

  var marketingLeadsPanel = function (id) {
    var _leadPanel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      title: "Leads",
      hideBorders: true,
      id: id,
      items: [crmLeadGrid(), panelLead()],
    });
    return _leadPanel;
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
  //    var referenceStores = function () {
  //        var _referenceStore = new Ext.data.JsonStore({
  //            autoLoad: true,
  //            url: modURL + '&op=getReferences',
  //            method: 'post',
  //            fields: ['referers_ids', 'referers_contact_ids'],
  //            remoteSort: true
  //        });
  //        return _referenceStore;
  //
  //    };
  var csv_upload = function () {
    var refererStore = referenceStores();
    var win_csv = new Ext.Window({
      layout: "fit",
      width: 400,
      autoHeight: true,
      border: false,
      title: "Upload File",
      icon: "./resources/images/submenuicons/upload_fl.png",
      //iconCls: 'upload',
      shadow: false,
      floating: true,
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      items: [
        new Ext.form.FormPanel({
          labelAlign: "top",
          labelSeparator: "",
          bodyStyle: {
            "background-color": "white",
            padding: "5px 5px 5px 10px",
          },
          autoHeight: true,
          id: "csv_form",
          frame: false,
          border: false,
          fileUpload: true,
          items: [
            {
              layout: "form",
              columnWidth: 0.3,
              items: [
                {
                  xtype: "combo",
                  mode: "local",
                  fieldLabel: "Referer",
                  id: "referers_ids",
                  displayField: "referers_contact_id",
                  valueField: "referers_id",
                  hiddenName: "referers_id",
                  allowBlank: false,
                  anchor: "98%",
                  tabIndex: 9,
                  triggerAction: "all",
                  lazyRender: true,
                  store: refererStore,
                },
                {
                  fieldLabel: "Upload File",
                  labelAlign: "top",
                  xtype: "fileuploadfield",
                  accept: ".csv",
                  id: "excel_file",
                  allowBlank: false,
                  name: "excel_file",
                  tabIndex: 1,
                  msgTarget: "under",
                  anchor: "98%",
                  validator: function (v) {
                    if (v != "") {
                      //var exp = /^.*.(.xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                      var exp = /^.*\.csv$/;
                      if (!exp.test(v)) {
                        return "Upload a valid CSV file.";
                      }
                      return true;
                    }
                    var filen = Ext.getCmp("excel_file").getValue();
                    if (filen == "") {
                      return "Upload a file.";
                    }
                  },
                },
              ],
              buttons: [
                {
                  //iconCls: 'csv',
                  icon: IMAGE_BASE_PATH + "/default/icons/finascop_disk.png",
                  text: "Upload",
                  handler: function () {
                    var csv_form = Ext.getCmp("csv_form").getForm();
                    var referers_ids = Ext.getCmp("referers_ids").getValue();
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    if (csv_form.isValid()) {
                      csv_form.submit({
                        url: modURL + "&op=uploadcsvFile",
                        waitTitle: "Please Wait..",
                        waitMsg: "Saving data...",
                        params: {
                          referers_ids: referers_ids,
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp,
                        },
                        success: function (csv_form, action) {
                          var result = Ext.decode(action.response.responseText);
                          if (
                            result.valid === true &&
                            result.success === true
                          ) {
                            win_csv.close();
                            Application.example.msg(
                              "Notification",
                              "Details saved Successfully"
                            );
                          }
                        },
                        failure: function () {
                          Ext.Msg.alert(
                            "Error",
                            "Supplied CSV File could not be validated. ",
                            function (btn) {
                              if (btn === "ok") {
                                Ext.Msg.hide();
                                win_csv.close();
                              }
                            }
                          );
                        },
                      });
                    }
                  },
                },
              ],
            },
          ],
        }),
      ],
    });
    win_csv.show();
    win_csv.doLayout();
    win_csv.center();
  };
  var leadform = function (contactid) {
    var retailCategoryStore = storeRetailCategory();
    var contactTypeStore = storeContactType();
    var leadform = new Ext.Window({
      width: 500,
      autoHeight: true,
      id: "add_leadWindow",
      shadow: false,
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      modal: true,
      closable: false,
      items: [
        new Ext.form.FormPanel({
          id: "formpanelAddLead" + Application.Crm_FLead.Cache.typeName,
          title: !Ext.isEmpty(contactid) ? "Edit Lead" : "Create Lead",
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
                      id: "crle_orgName",
                      name: "crle_orgName",
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
                      fieldLabel: "Lead Type",
                      xtype: "lovcombo",
                      displayField: "name",
                      valueField: "id",
                      mode: "local",
                      id: "addcrle_type",
                      hiddenName: "addcrle_type",
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
                      fieldLabel: "Lead Type",
                      emptyText: "Lead Type",
                      id: "crle_type",
                      name: "crle_type",
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
                      hiddenName: "crle_type",
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
                      id: "crle_location",
                      name: "crle_location",
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
                      id: "crle_orgPincode",
                      name: "crle_orgPincode",
                      anchor: "98%",
                      allowBlank: false,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Country",
                      tabIndex: 303,
                      id: "crle_orgCountry",
                      name: "crle_orgCountry",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Route",
                      tabIndex: 303,
                      id: "crle_groute",
                      name: "crle_groute",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Locality",
                      tabIndex: 303,
                      id: "crle_glocality",
                      name: "crle_glocality",
                      anchor: "98%",
                      hidden: true,
                      maxLength: 100,
                    },
                    {
                      xtype: "textfield",
                      fieldLabel: "Place",
                      tabIndex: 303,
                      id: "crle_gplace",
                      name: "crle_gplace",
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
                      id: "crle_orgAddress",
                      name: "crle_orgAddress",
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
                      id: "crle_indContactperson",
                      name: "crle_indContactperson",
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
                      id: "crle_indMobile",
                      name: "crle_indMobile",
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
                      id: "crle_orgContactNo",
                      name: "crle_orgContactNo",
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
                      id: "crle_orgEmail",
                      name: "crle_orgEmail",
                      anchor: "98%",
                      vtype: "email",
                      allowBlank: false,
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
              "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
            )
              .getStore()
              .load();
            leadform.close();
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
              "formpanelAddLead" + Application.Crm_FLead.Cache.typeName
            );

            leadInsertion(_customerId);
          },
        },
      ],
    });
    if (contactid > 0) {
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var regFee_form = Ext.getCmp(
        "formpanelAddLead" + Application.Crm_FLead.Cache.typeName
      ).getForm();
      regFee_form.load({
        params: {
          EditStatus: 1,
          _edit_crle_id: contactid,
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        url: modURL + "&op=loadEditData",
        waitMsg: "Loading...",
        success: function (form, action) {
          eval("var tmp=" + action.response.responseText);
          var tmp = Ext.decode(action.response.responseText);
          Ext.getCmp("crle_type").getStore().load();
          Ext.getCmp("crle_type").setValue(tmp.data.crle_type);
          Ext.getCmp("crle_type").setRawValue(tmp.data.crle_typeName);
          Ext.getCmp("crle_type").show();
          Ext.getCmp("crle_type").allowBlank = false;
        },
        failure: function (form, action) {},
      });
    } else {
      Ext.getCmp("addcrle_type").show();
      Ext.getCmp("addcrle_type").allowBlank = false;
    }
    leadform.doLayout();
    leadform.show();
    leadform.center();
    return leadform;
  };
  var leadInsertion = function (_customerId) {
    var t = new Date();
    var t_stamp = t.format("YmdHis");

    var _addeditform = Ext.getCmp(
      "formpanelAddLead" + Application.Crm_FLead.Cache.typeName
    );
    if (_customerId > 0) {
      _customerId = _customerId;
      Application.Crm_FLead.Cache.upcrclid = _customerId;
    } else {
      _customerId = "-";
      Application.Crm_FLead.Cache.upcrclid = 0;
    }
    if (_addeditform.getForm().isValid()) {
      Application.Crm_FLead.editLeadData();
    } else {
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  function initAutocompleteText() {
    var input = document.getElementById("crle_location");
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
            document.getElementById("crle_orgPincode").value =
              place.address_components[i].long_name;
            break;
          case "country":
            document.getElementById("crle_orgCountry").value =
              place.address_components[i].long_name;
            break;
          case "street_number":
            document.getElementById("crle_orgAddress").value =
              place.address_components[i].long_name;
            break;
          case "route":
            document.getElementById("crle_groute").value =
              place.address_components[i].long_name;
            break;
          case "postal_code_suffix":
            document.getElementById("crle_gpostsuffix").value =
              place.address_components[i].long_name;
            break;
          case "locality":
            document.getElementById("crle_glocality").value =
              place.address_components[i].long_name;
            break;
          case "administrative_area_level_1":
            document.getElementById("crle_gplace").value =
              place.address_components[i].long_name;
            break;
        }
      }

      document.getElementById("crle_orgAddress").value =
        place.formatted_address;
      document.getElementById("glatitude").value = latitude;
      document.getElementById("glongitude").value = longitude;
    });
  }
  var crmLeadGrid = function () {
    var _gridStore = crmLeadGridStore();
    var filters = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "crle_orgName",
        },
        {
          type: "string",
          dataIndex: "crle_indMobile",
        },
        {
          type: "string",
          dataIndex: "crle_orgEmail",
        },
        {
          type: "string",
          dataIndex: "crle_gplace",
        },{
          type: "string",
          dataIndex: "areaName",
        },{
          type: "string",
          dataIndex: "assignedROName",
        },
      ],
    });
    filters.remote = true;
    filters.autoReload = true;

    var _crmLeadGrid = new Ext.grid.GridPanel({
      id: "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName,
      store: _gridStore,
      region: "center",
      frame: true,
      border: false,
      plugins: [filters],
      loadMask: true,
      columns: [
        {
          header: "Lead Name",
          dataIndex: "crle_orgName",
          sortable: true,
          width: 200,
        },
        {
          header: "Contact Number",
          dataIndex: "crle_indMobile",
          sortable: true,
          width: 150,
        },
        {
          header: "Area",
          dataIndex: "areaName",
          sortable: true,
        },
        {
          header: "Created From",
          dataIndex: "crle_CreatedFrom",
          sortable: true,
        },
        {
          header: "Created On",
          dataIndex: "crle_CreatedOn",
          sortable: true,
        },
        {
          header: "Assigned To",
          dataIndex: "crle_CreatedBy",
          sortable: true,
        },
        {
          header: "RO",
          dataIndex: "assignedROName",
          sortable: true,
        },
        {
          header: "State/Province",
          dataIndex: "crle_gplace",
          sortable: true,
          width: 200,
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
              leadsActionMenu.showAt(e.getXY());
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
      tbar: [
        {
          xtype: "button",
          text: "Create Lead",
          tooltip: "Create Lead",
          iconCls: "finascop_add",
          handler: function () {
            leadform();
          },
        },
        {
          xtype: "button",
          iconCls: "csv",
          text: "Import",
          hidden: true,
          handler: function () {
            csv_upload();
          },
        },
      ],
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
          selectionchange: gridSelectionChangedLead,
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
          Application.Crm_FLead.Cache.id = ID;
          if (columnIndex != 5) {
            var _tabpanel = Ext.getCmp(
              "tabpanelMarketingLead" + Application.Crm_FLead.Cache.typeName
            );

            Ext.getCmp("htmlid" + Application.Crm_FLead.Cache.typeName).show();
            if (record.STATUS == "UnAttended") {
              Application.Crm_FLead.Cache.status = record.STATUS;
              _tabpanel.setActiveTab(0);
              Ext.getCmp(
                "htmlid" + Application.Crm_FLead.Cache.typeName
              ).show();
            } else {
              Application.Crm_FLead.ViewMode(
                Application.Crm_FLead.Cache.id,
                "NOT"
              );
              Application.Crm_FLead.Cache.status = record.STATUS;
              _tabpanel.setActiveTab(0);
            }
          }
        },
        viewready: updatePagination,
      },
    });
    return _crmLeadGrid;
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
    });
    return _referenceStore;
  };
  var leadsActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Edit",
        handler: function () {
          var id = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.id;
          leadform(id);
        },
      },
      {
        text: "Delegate Lead",
        handler: function () {
          var id = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.id;
          var isLeadAreaAssigned = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.isLeadAreaAssigned;
            var leadName = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.crle_orgName;
            var leadAreaID = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.areaId;
            var areaName = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.areaName;
            var assignedRO = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.assignedRO;
            var assignedROName = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.assignedROName;
          //manuallyAssignArea(id);
          Application.Crm_FLead.mapAreaToLead(id,Application.Crm_FLead.Cache.typeName,isLeadAreaAssigned,leadName,leadAreaID,areaName,assignedRO,assignedROName);
        },
      },
      {
        text: "Schedule Meetings",
        handler: function () {
          var id = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.id;
          var isLeadAreaAssigned = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.isLeadAreaAssigned;
          if (isLeadAreaAssigned == 1) {
            sheduleMeetings(id);
          } else {
            Ext.MessageBox.alert("Notification", "Lead is not yet assigned");
          }
        },
      },
      {
        text: "Conduct Survey",
        handler: function () {
          var id = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.id;
            loadSurvey(id);
        },
      },
      {
        text: "Update Lead Stages",
        id: "btnLeadStages",
        handler: function () {
          var id = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.id;
          var isLeadAreaAssigned = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.isLeadAreaAssigned;
          if (isLeadAreaAssigned == 1) {
            convertToProspect(id);
          } else {
            Ext.MessageBox.alert("Notification", "Lead is not yet assigned");
          }
        },
      },
      {
        text: "Upgrade to Prospect",
        id: "btnToProspect",
        handler: function () {
          var id = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.id;
          var isLeadAreaAssigned = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.isLeadAreaAssigned;
          var leadname = Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getSelectionModel()
            .getSelections()[0].data.crle_orgName;
            var leadEmail = Ext.getCmp(
              "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
            )
              .getSelectionModel()
              .getSelections()[0].data.crle_orgEmail;
              if(!Ext.isEmpty(leadEmail)){
                Ext.MessageBox.confirm(
                  "Confirm",
                  "So you want to upgrade " + leadname + " to prospect?",
                  function (btn, text) {
                    if (btn == "yes") {
                      upgradToProspect(id);
                    }
                  }
                );
              }else{
                Ext.MessageBox.alert("Notification", "Update email and proceed");
              }
          
        },
      },{
        id:'btnupgradeMerchant',
        text: "Upgrade Merchant",
        handler: function () {
          var id = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.id;
          var isLeadAreaAssigned = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.isLeadAreaAssigned;
          var leadname = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.crle_orgName;
            var leadEmail = Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getSelectionModel().getSelections()[0].data.crle_orgEmail;
              if(!Ext.isEmpty(leadEmail)){
                Ext.MessageBox.confirm(
                  "Confirm","So you want to upgrade " + leadname + " to GroSmart Merchant?",
                  function (btn, text) {
                    if (btn == "yes") {
                      upgradToGroSmartMerchant(id);
                    }
                  }
                );
              }else{
                Ext.MessageBox.alert("Notification", "Update email and proceed");
              }         
        }
      }
    ],
  });
  var crmStatusStore = function () {
    var store = new Ext.data.JsonStore({
      url: modURL + "&op=getCrmStatus",
      method: "post",
      autoLoad: true,
      fields: ["crmu_id", "crmu_name"],
      root: "data",
    });
    return store;
  };
  var convertToProspect = function (id) {
    var storeCrmStatus = crmStatusStore();
    var cpfrmLeadWindow = new Ext.Window({
      id: "windowToConvertProspect",
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
            "convertToProspectFormPanel" + Application.Crm_FLead.Cache.typeName,
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
                      displayField: "crmu_name",
                      valueField: "crmu_id",
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

                          if (value == 3) {
                            var datefield = Ext.getCmp("crmFollowupDate");
                            datefield.enable();
                            datefield.reset();
                            var _date = new Date();
                            datefield.setValue(_date);
                            datefield.minValue = new Date(_date);
                          } else if (value == 4) {
                            var d = new Date();
                            var cur_mnth = d.getMonth();
                            d.setMonth(cur_mnth + 6);
                            var datefield = Ext.getCmp("crmFollowupDate");
                            datefield.reset();
                            datefield.setValue(d);
                            datefield.minValue = new Date(d);
                            datefield.disable();
                          } else if (value == 5) {
                            var datefield = Ext.getCmp("crmFollowupDate");
                            datefield.reset();
                            //datefield.disable();
                            var _date = new Date();
                            datefield.setValue(_date);
                            datefield.minValue = new Date(_date);
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
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
          text: "Save",
          handler: function () {
            var crmStatus = Ext.getCmp("crmStatus").getValue();
            var crmFollowupDate = Ext.getCmp("crmFollowupDate").getValue();
            var crmRemarks = Ext.getCmp("crmRemarks").getValue();
            if (crmStatus > 0) {
              Ext.Ajax.request({
                waitMsg: "Processing",
                method: "POST",
                url: modURL + "&op=convertLeadtoProspect",
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
                      "gridMarketingLeadsList" +
                        Application.Crm_FLead.Cache.typeName
                    )
                      .getStore()
                      .load({
                        callback: function (record, options, success) {
                          cpfrmLeadWindow.close();
                          Application.example.msg("Notification", tmp.msg);
                          var gridPanel = Ext.getCmp(
                            "gridMarketingLeadsList" +
                              Application.Crm_FLead.Cache.typeName
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
  var manuallyAssignArea = function (id) {
    Ext.Ajax.request({
      waitMsg: "Processing",
      method: "POST",
      url: modURL + "&op=assignAreaManually",
      params: {
        leadId: id,
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        if (tmp.success === true) {
          Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getStore()
            .load({
              callback: function (record, options, success) {
                var gridPanel = Ext.getCmp(
                  "gridMarketingLeadsList" +
                    Application.Crm_FLead.Cache.typeName
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
  var leadUserDetails = function (id, currentStatus) {
    var lead_id = id;
    Ext.Ajax.request({
      waitMsg: "Processing",
      method: "POST",
      url: modURL + "&op=getUserid",
      params: {
        id: id,
        status: currentStatus,
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        if (tmp.success === true) {
          Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getStore()
            .load({
              callback: function (record, options, success) {
                var gridPanel = Ext.getCmp(
                  "gridMarketingLeadsList" +
                    Application.Crm_FLead.Cache.typeName
                );
                var index = gridPanel.store.find("id", id);
                gridPanel.getSelectionModel().selectRow(index);
                loadLeadWindow(lead_id);
              },
            });
        } else {
          Ext.MessageBox.alert("Error", tmp.msg);
        }
      },
      failure: function (form, action) {
        var res = Ext.decode(action.response.responseText);
        Ext.MessageBox.alert("Error", res.errors.msg);
      },
    });
  };

  var gridSelectionChangedLead = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp(
          "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
        )
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp(
        "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
      )
        .getSelectionModel()
        .getSelections()[0].data.id;
      Ext.getCmp(
        "tabpanelMarketingLead" + Application.Crm_FLead.Cache.typeName
      ).setActiveTab(0);
      Application.Crm_FLead.ViewMode(ID);
    } else {
      console.log("is it here");
      Application.Crm_FLead.Cache.id = 0;
      Application.Crm_FLead.ViewMode(0);
    }
    switch(Application.Crm_FLead.Cache.typeName){
      case 'RM':
        Ext.getCmp("btnToProspect").show();
        Ext.getCmp("btnLeadStages").hide();
        Ext.getCmp("btnupgradeMerchant").hide();
        break;
      case 'WM':
        Ext.getCmp("btnToProspect").hide();
        Ext.getCmp("btnLeadStages").show();
        Ext.getCmp("btnupgradeMerchant").show();
        break;
      default:
        Ext.getCmp("btnToProspect").hide();
        Ext.getCmp("btnLeadStages").show();
        Ext.getCmp("btnupgradeMerchant").hide();
        break;
    }    
  };
  var upgradToProspect = function (id) {
    Ext.Ajax.request({
      waitMsg: "Processing",
      method: "POST",
      url: modURL + "&op=convertLeadtoProspect",
      params: {
        leadId: id,
        crmStatus: 3,
        crmFollowupDate: new Date(),
        crmRemarks: "Converted to Prospect",
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        if (tmp.success === true) {
          Ext.getCmp(
            "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
          )
            .getStore()
            .load({
              callback: function (record, options, success) {
                cpfrmLeadWindow.close();
                Application.example.msg("Notification", tmp.msg);
                var gridPanel = Ext.getCmp(
                  "gridMarketingLeadsList" +
                    Application.Crm_FLead.Cache.typeName
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
  var areaROStorefn = function(){
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listROforArea",
        method: "post",
      }),
      fields: [
        "id",
        "roName",        
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        beforeload: function () {          
        },
        load: function () {
        },
      },
    });
    return _Store;
  };
  var areaStore = function (crle_id, typeName, isLeadAreaAssigned) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAreaForLead",
        method: "post",
      }),
      fields: [
        "id",
        "areaName",
        "areaLocation",
        "areaLatitude",
        "distance",
        "currentArea",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: true,
      listeners: {
        beforeload: function () {
          this.baseParams.crle_id = crle_id;
          this.baseParams.typeName = typeName;
          this.baseParams.isLeadAreaAssigned = isLeadAreaAssigned;
        },
        load: function () {
        },
      },
    });
    return _Store;
  };
  var mapAreatoLeadGrid = function (crle_id, typeName, isLeadAreaAssigned) {
    var _baStore = areaStore(crle_id, typeName, isLeadAreaAssigned);
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
      id: "gridpanelLeadareas",
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
        },
        {
          header: "Distance",
          dataIndex: "distance",
          sortable: true,
          tooltip: "Distance",
          hideable: true,
        },
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {
          if (record.get("currentArea") == 1) {
            return "finascop_indicateColGUMLEAFGREEN";
          } else {
            return "";
          }
        },
      },
      tbar: [],
    });
    return _bagridPanel;
  };
  var sheduleMeetings = function (id) {
    var cpfrmLeadWindow = new Ext.Window({
      id: "windowToConvertProspect",
      iconCls: "vender-items",
      shadow: false,
      width: winsize.width * 0.4,
      height: winsize.height * 0.4,
      title: "Schedule Lead Meetings",
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
            "convertToProspectFormPanel" + Application.Crm_FLead.Cache.typeName,
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
                      fieldLabel: "Select Date",
                      xtype: "datefield",
                      allowblank: false,
                      id: "crmscheduleDate",
                      anchor: "95%",
                      format: "d/m/Y",
                      minValue: new Date()
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
                      xtype: "timefield",
                      fieldLabel: "Time",
                      id: "crmscheduleTime",
                      name: "crmscheduleTime",
                      anchor: "95%",
                      allowBlank: false,
                      width: 100,
                      tabIndex: 501,
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
                      id: "crmScheduleRemarks",
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
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
          text: "Save",
          handler: function () {
            var crmscheduleTime = Ext.getCmp("crmscheduleTime").getValue();
            var crmscheduleDate = Ext.getCmp("crmscheduleDate").getValue();
            var crmScheduleRemarks =
              Ext.getCmp("crmScheduleRemarks").getValue();
            if (!Ext.isEmpty(crmscheduleTime)) {
              Ext.Ajax.request({
                waitMsg: "Processing",
                method: "POST",
                url: modURL + "&op=scheduleMeetings",
                params: {
                  leadId: id,
                  crmscheduleTime: crmscheduleTime,
                  crmscheduleDate: crmscheduleDate,
                  crmScheduleRemarks: crmScheduleRemarks,
                },
                success: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp(
                      "gridMarketingLeadsList" +
                        Application.Crm_FLead.Cache.typeName
                    )
                      .getStore()
                      .load({
                        callback: function (record, options, success) {
                          cpfrmLeadWindow.close();
                          Application.example.msg("Notification", tmp.msg);
                          var gridPanel = Ext.getCmp(
                            "gridMarketingLeadsList" +
                              Application.Crm_FLead.Cache.typeName
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
              Ext.MessageBox.alert("Error", "Choose meeting time and proceed.");
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
  var loadSurvey = function(leadId){
    var printLabResultsPanel = loadSurveyPanel(leadId);
    if (Ext.isEmpty(crmSurveyDetaislWindow)) {
        var crmSurveyDetaislWindow = new Ext.Window({
            id: 'crmSurveyDetaislWindow',
            plain: true,
            modal: true,
            constrain: true,
            resizable: false,
            title: 'Survey Details',
            width: winsize.width * 0.7,
            autoHeight: true,
            items: [printLabResultsPanel],
            buttons: [{
                    text: 'Cancel',
                    handler: function () {
                        crmSurveyDetaislWindow.close();
                    }
                }],
        });
    }
    crmSurveyDetaislWindow.doLayout();
    crmSurveyDetaislWindow.show();
    crmSurveyDetaislWindow.center();
  };
  var loadSurveyPanel = function(leadId){
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var src = '?module=crm_leads&op=loadSurveyDetails&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp + '&leadId=' + leadId;
    var myPanel = new Ext.Panel({
        layout: 'border',
        height: 500,
        id: 'crmSurveyPanel',
        items: [{
                region: 'center',
                border: false,
                html: '<iframe src="' + src +
                        '" id="iframeRequest" name="iframeRequest" ' +
                        'width="100%" height="100%" style="border:none">'
            }]
    });
    return myPanel;
  };
  var mapAreatoLeadForm = function(crle_id, typeName, isLeadAreaAssigned,leadName,leadAreaID,areaName,assignedRO,assignedROName){
   var areaROStore = areaROStorefn();
    return  new Ext.FormPanel({
      layout: "form",
      id:"leadAreaForm",
      height: 150,
      columnWidth: 1,
      frame: true,
      border: true,
      items: [{
        fieldLabel: 'Lead',
        xtype: 'displayfield',
        id: 'leadName',
        name: 'leadName',
        value: leadName
    }, {
      xtype: "combo",
      id: "dlegateArea",
      name: "dlegateArea",
      mode: "local",
      typeAhead: true,
      forceSelection: true,
      emptyText: "Select Area",
      fieldLabel: "Area",
      editable: true,
      anchor: "97%",
      store: areaStore(crle_id, typeName, isLeadAreaAssigned),
      triggerAction: "all",
      minChars: 2,
      displayField: "areaName",
      valueField: "id",
      hiddenName: "dlegateArea",
      listeners: {
        select: function () {
          var value = Ext.getCmp("dlegateArea").getValue();
          Ext.getCmp('dlegateAreaRO').reset();
          Ext.getCmp('dlegateAreaRO').getStore().load({
            params: {
                areaId: value
            }
        });
        },
      },
    },{
      xtype: "combo",
      id: "dlegateAreaRO",
      name: "dlegateAreaRO",
      mode: "local",
      typeAhead: true,
      forceSelection: true,
      emptyText: "Select RO",
      fieldLabel: "RO",
      editable: true,
      anchor: "97%",
      store: areaROStore,
      triggerAction: "all",
      minChars: 2,
      displayField: "roName",
      valueField: "id",
      hiddenName: "dlegateAreaRO",
      listeners: {
        select: function () {
        },
      },
    }
      ],
    });
  };
  var upgradToGroSmartMerchant = function (leadId) {
      var _addNewWindow = new Ext.Window({
          title: "Convert Lead to GroSmart Merchant",
          layout: "fit",
          width: winsize.width * 0.4,
          height: winsize.height * 0.5,
          resizable: false,
          draggable: true,
          closable: true,
          modal: true,
          bodyStyle: { "background-color": "white" },
          items: [mapLeadtoMerchant(leadId)],
          buttons: [
            {
              text: "Close",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              iconCls: "my-icon1",
              tabIndex: 511,
              handler: function () {
                _addNewWindow.close();
              },
            },
            {
              text: "Upgrade",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              tabIndex: 511,
              handler: function () {
                var sgId = Ext.getCmp('gridpanelLeadMerchants').getSelectionModel().getSelections()[0].data.store_group_id;
                var grosmartMerchant = Ext.getCmp('gridpanelLeadMerchants').getSelectionModel().getSelections()[0].data.store_group_grosmartMerchant;
                if(grosmartMerchant == 0){
                  Ext.Ajax.request({
                    url: modURL + "&op=convertLeadtoProspect",
                    method: "POST",
                    params: { 
                      sgId: sgId,
                      leadId:leadId,
                      crmStatus:10,
                      crmFollowupDate: new Date(),
                      crmRemarks: "Converted to GroSmart Merchant",
                    },
                    success: function (res) {
                      var tmp = Ext.decode(res.responseText);
                      if (tmp.success === true) {
                        Application.example.msg("Notification", tmp.msg);
                        _addNewWindow.close();
                      }
                    },
                    failure: function () {
                      Ext.MessageBox.alert("Error", "Error occured while sending data");
                    },
                  });
                }else{
                  Ext.MessageBox.alert("Notification", "Already in GroSmart Merchant.");
                }
                
              },
            },
          ],
          listeners:{
            afterrender: function () {
              var t = new Date();
              var t_stamp = t.format("YmdHis");            
                            
          }
          }
        });
        _addNewWindow.doLayout();
        _addNewWindow.show();
        _addNewWindow.center();
  };
  var mapLeadtoMerchant = function (crle_id) {
    var _baStore = merchantStore(crle_id);
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
      id: "gridpanelLeadMerchants",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Store Group",
          dataIndex: "store_group_name",
          sortable: true,
          tooltip: "Store Group",
          hideable: true,
        },
        {
          header: "Contact Number",
          dataIndex: "contactNumber",
          sortable: true,
          tooltip: "Contact Number",
          hideable: true,
        },{
          header: "GroSmart Merchant",
          dataIndex: "grosmartMerchant",
          sortable: true,
          tooltip: "GroSmart Merchant",
          hideable: true,
        },{
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
          hideable: true,
        }
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {
          
        },
      },
      tbar: [{
                xtype: 'textfield',
                tabIndex: 702,
                id: 'leadMerchantSearch',
                name: 'leadMerchantSearch',
                allowBlank: false,
                width:300,
                anchor: '95%',
                maxLength: 100,
                listeners: {
                    afterrender: function (field) {
                        Ext.defer(function () {
                            field.focus(true, 100);
                        }, 1);
                    }
                }
            }, {
                xtype: 'button',
                text: 'Search',
                iconCls: 'finascop_search_btn',
                style: "padding-left: 10px;",
                tabIndex: 703,
                handler: function () {
                  if(!Ext.isEmpty(Ext.getCmp('leadMerchantSearch').getValue())){
                    Ext.getCmp('gridpanelLeadMerchants').getStore().load({
                    params: {
                          sgName: Ext.getCmp('leadMerchantSearch').getValue()
                      }
                    });
                  }else{
                    Ext.MessageBox.alert("Notification", "Search field is missing.");
                  }
                  
                }
            }  ],
    });
    return _bagridPanel;
  };
  var merchantStore = function (crle_id) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listMerchants",
        method: "post",
      }),
      fields: [
        "store_group_id",
        "store_group_name",
        "store_group_grosmartMerchant",
        "grosmartMerchant","contactNumber"
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        beforeload: function () {
          
        },
        load: function () {
        },
      },
    });
    return _Store;
  };
  return {
    Cache: {},
    initLeads: function (type) {
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
      Application.Crm_FLead.Cache.type = type;
      Application.Crm_FLead.Cache.typeName = typeName;
      var panelId = "leadspanel" + typeName;
      var leads_panel = Ext.getCmp(panelId);
      if (Ext.isEmpty(leads_panel)) {
        leads_panel = marketingLeadsPanel(panelId);
        Application.UI.addTab(leads_panel);
        leads_panel.doLayout();
      } else {
        Application.UI.addTab(leads_panel);
        leads_panel.doLayout();
      }
    },
    ViewMode: function () {
      var lead_id = arguments[0];
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var is_UnAttended = arguments[1];
      var apikey = _SESSION.apikey;
      Ext.get(
        "downloadIframelead" + Application.Crm_FLead.Cache.typeName
      ).dom.src =
        modURL +
        "&op=loadEditLeadData&id=" +
        lead_id +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey +
        "&is_UnAttended=" +
        is_UnAttended;
    },
    editLeadData: function () {
      var _editForm = Ext.getCmp(
        "formpanelAddLead" + Application.Crm_FLead.Cache.typeName
      );
      var _customerId = Ext.getCmp("id").getValue();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      _editForm.getForm().submit({
        waitMsg: "saving.... ",
        url: modURL,
        params: {
          op: "EditLeadDetails",
          customer_id: _customerId,
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        success: function (form, action) {
          var tmp = Ext.decode(action.response.responseText);
          if (tmp.success == true) {
            Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
              Ext.getCmp("add_leadWindow").close(); //leadWindowforMarketing

              Ext.getCmp(
                "gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName
              )
                .getStore()
                .load({
                  callback: function (record, options, success) {
                    if (_customerId > 0) {
                      console.log("here inside if");
                      var gridPanel = Ext.getCmp("gridMarketingLeadsList");
                      var index = gridPanel.store.find("id", _customerId);
                      gridPanel.getSelectionModel().selectRow(index);
                    }
                  },
                });
              Ext.getCmp(
                "htmlid" + Application.Crm_FLead.Cache.typeName
              ).show();
            });
          }
        },
        failure: function (form, action) {
          var res = Ext.decode(action.response.responseText);
          Ext.MessageBox.alert("Error", res.errors.msg);
        },
      });
    },
    mapAreaToLead: function (crle_id, typeName, isLeadAreaAssigned,leadName,leadAreaID,areaName,assignedRO,assignedROName) {
      var _addNewWindow = new Ext.Window({
        title: "Delegate Area to Lead",
        layout: "fit",
        width: winsize.width * 0.3,
        height: winsize.height * 0.3,
        resizable: false,
        draggable: true,
        closable: true,
        modal: true,
        bodyStyle: { "background-color": "white" },
        items: [
          //mapAreatoLeadGrid(crle_id, typeName, isLeadAreaAssigned)
          mapAreatoLeadForm(crle_id, typeName, isLeadAreaAssigned,leadName,leadAreaID,areaName,assignedRO,assignedROName)
        ],
        buttons: [
          {
            text: "Close",
            icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
            iconCls: "my-icon1",
            tabIndex: 511,
            handler: function () {
              _addNewWindow.close();
            },
          },
          {
            text: "Delegate",
            icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
            tabIndex: 511,
            handler: function () {
              var areaId = Ext.getCmp('dlegateArea').getValue();
              var roId = Ext.getCmp('dlegateAreaRO').getValue();
              if (areaId > 0 && roId > 0) {
                Ext.Ajax.request({
                  url: modURL + "&op=delegateLead",
                  method: "POST",
                  params: {
                    crle_id: crle_id,
                    areaId: Ext.getCmp('dlegateArea').getValue(),
                    areaName: Ext.getCmp('dlegateArea').getRawValue(),
                    roId: Ext.getCmp('dlegateAreaRO').getValue(),
                    roName: Ext.getCmp('dlegateAreaRO').getRawValue(),
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success == true) {
                      Application.example.msg("Success", tmp.msg);
                      _addNewWindow.close();
                      Ext.getCmp("gridMarketingLeadsList" + Application.Crm_FLead.Cache.typeName).getStore().load();
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
                Ext.Msg.alert("Notification", "Select an area and proceed.");
              }
            },
          },
        ],
        listeners:{
          afterrender: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");            
if(isLeadAreaAssigned == 1){
Ext.getCmp('dlegateArea').setValue(leadAreaID);
Ext.getCmp('dlegateArea').setRawValue(areaName);
Ext.getCmp('dlegateAreaRO').setValue(assignedRO);
Ext.getCmp('dlegateAreaRO').setRawValue(assignedROName);
Ext.getCmp('dlegateAreaRO').getStore().load({
  params: {
      areaId: leadAreaID
  }
});

}
            
        }
        }
      });
      _addNewWindow.doLayout();
      _addNewWindow.show();
      _addNewWindow.center();
    },
  };
})();
