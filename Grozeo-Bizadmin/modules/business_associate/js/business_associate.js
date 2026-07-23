Application.BusinessAssociate = (function () {
  var recs_per_page = 12;
  var modURL = "?module=business_associate";
  var winsize = Ext.getBody().getViewSize();
  var updatePagination = function (cmp) {
    recs_per_page = updateRecsPerPage(cmp);
  };
  var areaMarker; /*= [
    {
      lat: 8.507007481504532,
      lng: 76.95167541503906,
      marker: {
        title: "you are here",
        draggable: true,
      },
      listeners: {
        onFailure: function () {
          Ext.MessageBox.alert("Failed locating city ");
        },
        onSuccess: function (point) {},
        dragend: function (markerAt) {
          Ext.getCmp("areaLatitude").setValue(markerAt.latLng.lat());
          Ext.getCmp("areaLongitude").setValue(markerAt.latLng.lng());
        },
      },
    },
  ];*/
  var businessAssociateMainPanel = function (id) {
    var mid = 0;
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      id: id,
      title: "Business Associate",
      items: [
        businessAssociateGrid(),
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
            businessAssociateItem(mid),
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
                  id: "buttonbusinessAssociateCancel",
                  tabIndex: 414,
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  iconCls: "my-icon61",
                  hidden: true,
                  handler: function () {
                    Ext.getCmp(
                      "panelBusinessAssociateDetails"
                    ).getForm().trackResetOnLoad = false;
                    Ext.getCmp("panelBusinessAssociateDetails").hide();

                    Ext.getCmp("buttonbusinessAssociateSave").hide();
                    Ext.getCmp("buttonbusinessAssociateCancel").hide();
                    Ext.getCmp("tabpanelBusinessAssociate").show();
                    var grid = Ext.getCmp("gridpanelbusinessAssociate");
                    grid.getSelectionModel().clearSelections();

                    var form = Ext.getCmp(
                      "panelBusinessAssociateDetails"
                    ).getForm();
                    form.reset();
                    Ext.getCmp("gridpanelbusinessAssociate").getStore().load();
                  },
                },
                {
                  text: "Save",
                  xtype: "button",
                  width: 75,
                  columnWidth: 0.15,
                  id: "buttonbusinessAssociateSave",
                  tabIndex: 417,
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  iconCls: "thes_save",
                  hidden: true,
                  handler: function () {
                    var cust_id = Ext.getCmp("fbarhiddenid").getValue();
                    if (
                      Ext.getCmp("panelBusinessAssociateDetails")
                        .getForm()
                        .isValid()
                    ) {
                      if (
                        cust_id == "" ||
                        cust_id == 0 ||
                        cust_id == "undefined"
                      ) {
                        Application.BusinessAssociate.saveBusinessAssociateDetails(
                          0
                        );
                      } else {
                        Application.BusinessAssociate.saveBusinessAssociateDetails(
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
                  id: "buttonbusinessAssociateEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
                  hidden: true,
                  handler: function () {
                    Ext.getCmp("property_grid_id").hide();
                    Ext.getCmp("panelBusinessAssociateDetails").show();
                    Ext.getCmp("panelBusinessAssociateDetails").setTitle(
                      "Edit Form"
                    );
                    Ext.getCmp("buttonbusinessAssociateCancel").show();
                    Ext.getCmp("buttonbusinessAssociateSave").show();
                    Ext.getCmp("buttonbusinessAssociateEdit").hide();
                    Ext.getCmp("tabpanelBusinessAssociate").hide();
                    var baId = Ext.getCmp("gridpanelbusinessAssociate")
                      .getSelectionModel()
                      .getSelections()[0].data.baId;
                    var _Editformdata = Ext.getCmp(
                      "panelBusinessAssociateDetails"
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
                        url: modURL + "&op=editFormDataLoad",
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
                           Ext.getCmp("businessType").getStore().load();
                          Ext.getCmp("businessType").setValue(tmp.data.type);
                          Ext.getCmp("businessType").setRawValue(tmp.data.businessType);
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

                            mapLoadBA();
                          
                        },
                      });
                    }
                    businessAssociateid_load =
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
  var businessAssociateGridStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listBusinessAssociate",
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
        "baType","type","businessType","baStatus","status"
      ],
      totalProperty: "totalCount",
      root: "data",
      listeners: {
        beforeload: function () {
          //this.baseParams.partytype = partytype;
        },
        load: function () {
          Ext.getCmp("gridpanelbusinessAssociate")
            .getSelectionModel()
            .selectRow(0);
          if (
            !Ext.isEmpty(
              Ext.getCmp("gridpanelbusinessAssociate")
                .getSelectionModel()
                .getSelections()
            )
          ) {
            Ext.getCmp("property_grid_id").hide();

            Ext.getCmp("fbarhiddenid").reset();

            Ext.getCmp("buttonbusinessAssociateCancel").show();
            Ext.getCmp("buttonbusinessAssociateSave").show();
            Ext.getCmp("buttonbusinessAssociateEdit").hide();
            var data = Ext.getCmp("gridpanelbusinessAssociate")
              .getSelectionModel()
              .getSelections()[0].data;
            Application.BusinessAssociate.ViewMode(data);
          }
        },
      },
    });
    return store;
  };
  var customerGridAction = function () {
    var action = new Ext.ux.grid.RowActions({
      autoWidth: false,
      hideMode: "display",
      width: 50,
      actions: [
        {
          sortable: false,
          tooltip: "Edit Business Associate",
          iconCls: "finascop_edit",
          callback: function (grid, rec, row, col) {
            Ext.getCmp("property_grid_id").hide();
            Ext.getCmp("panelBusinessAssociateDetails").show();
            Ext.getCmp("buttonbusinessAssociateCancel").show();
            Ext.getCmp("buttonbusinessAssociateSave").show();
            businessAssociateItem(rec.data);
          },
        },
      ],
    });
    return action;
  };
  var baActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Manage Relationship Officer",
        handler: function () {
          var baId = Ext.getCmp("gridpanelbusinessAssociate")
            .getSelectionModel()
            .getSelections()[0].data.baId;
          Application.BusinessAssociate.viewRelationshipOfficers(baId);
        },
      },
    ],
  });
  var businessAssociateGrid = function () {
    var district_store = districtStoreEdit();
    var grid_store = businessAssociateGridStore();
    var action = customerGridAction();
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
          dataIndex: "baCity",
        },
        {
          type: "string",
          dataIndex: "baPincode",
        },
        {
          type: "string",
          dataIndex: "st_name",
        },
        {
          type: "string",
          dataIndex: "dst_Name",
        },{
          type: "string",
          dataIndex: "businessType",
        },
      ],
    });
    customerGrid_filter.remote = true;
    customerGrid_filter.autoReload = true;

    var SP_grid = new Ext.grid.GridPanel({
      store: grid_store,
      id: "gridpanelbusinessAssociate",
      region: "center",
      frame: true,
      border: false,
      layout: "fit",
      loadMask: true,
      plugins: [customerGrid_filter, action],
      columns: [
        {
          header: "Name",
          sortable: true,
          dataIndex: "baName",
          width: 175,
        },
        {
          header: "Type of Business",
          sortable: true,
          dataIndex: "businessType",
          width: 175,
        },
        {
          header: GST,
          sortable: true,
          hidden: true,
          dataIndex: "baGSTIN",
          width: 175,
        },{
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
        },
        {
          header: "City",
          sortable: true,
          dataIndex: "baCity",
          width: 175,
        },
        {
          header: "Post Codes",
          sortable: true,
          dataIndex: "baPincode",
          width: 175,
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
              baActionMenu.showAt(e.getXY());
            },
          },
        },
      ],
      viewConfig: {
        forceFit: true,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      tbar: [
        {
          xtype: "button",
          text: "Create Business Associate",
          tooltip: "Create Business Associate",
          iconCls: "finascop_add",
          handler: function () {
            var form = Ext.getCmp("panelBusinessAssociateDetails").getForm();
            form.trackResetOnLoad = false;
            form.reset();

            Ext.getCmp("property_grid_id").hide();

            Ext.getCmp("fbarhiddenid").reset();

            Ext.getCmp("baLname").reset();
            Ext.getCmp("panelBusinessAssociateDetails").setTitle("Add Form");

            Ext.getCmp("panelBusinessAssociateDetails").show();
            Ext.getCmp("buttonbusinessAssociateCancel").show();
            Ext.getCmp("buttonbusinessAssociateSave").show();
            Ext.getCmp("buttonbusinessAssociateEdit").hide();
            Ext.getCmp("tabpanelBusinessAssociate").hide();
          },
        },
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
        viewready: updatePagination,
        rowclick: function (grid, rowIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("baId");
          var data = record.data;
          Application.BusinessAssociate.ViewMode(data);
        },
      },
    });
    return SP_grid;
  };

  
  var territoryStoreEdit = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getTerritory",
      method: "post",
      fields: ["id", "name"],
      totalProperty: "totalCount",
      root: "data",
    });

    return store;
  };
  // store for state// edit party
  var stateStoreEdit = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getStates",
      method: "post",
      fields: ["st_ID", "st_name"],
      totalProperty: "totalCount",
      root: "data",
    });

    return store;
  };
  var districtStoreEdit = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: false,
      url: modURL + "&op=getDistrict",
      method: "post",
      fields: ["dst_ID", "dst_Name"],
      totalProperty: "totalCount",
      root: "data",
    });

    return store;
  };
  var areaStoreEdit = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: false,
      url: modURL + "&op=getArea",
      method: "post",
      fields: ["id", "areaName"],
      totalProperty: "totalCount",
      root: "data",
    });
    return store;
  };
  var businessPartnerComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getBusinessPartners",
      method: "post",
      fields: ["unique_key","id", "bpName","networkType"],
      totalProperty: "totalCount",
      root: "data",
    });

    return store;
  };

  //Right panel for tab panel starts here
  function businessAssociateItem(mid) {
    //if(mid != 0)
    console.log("mid", mid);
    var lati, longi;
    if (mid == 0) {
      lati = 8.507007481504532;
      longi = 76.95167541503906;
    }
    var PartyEditPanel = editBusinessAssociate(mid, lati, longi);

    var businessAssociateitempanel = new Ext.Panel({
      frame: false,
      border: false,
      collapsible: true,
      title: "Business Associate details",
      flex: 1,
      layout: "vbox",
      layoutConfig: {
        align: "stretch",
        pack: "start",
      },
      id: "panelbusinessAssociate",
      items: [
        PartyEditPanel,
        new Ext.TabPanel({
          activeTab: 0,
          flex: 1,
          plain: true,
          frame: true,
          id: "tabpanelBusinessAssociate",
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
                "<tr><th>Type :</th><td> {baTypeName}</td></tr>",
                "<tr><th>Mode :</th><td> {baModeName}</td></tr>",
                "<tpl if='baMode == \"2\"'><tr><th>Partner :</th><td> {bpName}</td></tr></tpl>",
                "<tr><th>Address:</th><td>  {baAddress}</td></tr>",
                "<tr><th>Email:</th><td>  {baEmail}</td></tr>",
                "<tr><th>Contact Person:</th><td>  {baContactPerson}</td></tr>",
                "<tr><th>"+PAN+" No.:</th><td>  {baPanNo}</td></tr>",
                "<tr><th>Mobile No.:</th><td>  {baMobileNo}</td></tr>",
                "<tr><th>City :</th><td>  {baCity}</td></tr>",
                "<tr><th>Post Codes :</th><td>  {baPincode}</td></tr>",
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
                var _businessAssociateId =
                  Ext.getCmp("fbarhiddenid").getValue();
                console.log("businessAssociate id is", _businessAssociateId);
                if (
                  _businessAssociateId == "" ||
                  _businessAssociateId == undefined
                ) {
                  Ext.getCmp("buttonbusinessAssociateEdit").hide();
                } else {
                  Ext.getCmp("buttonbusinessAssociateEdit").show();
                }
              }
            },
          },
        }),
      ],
    });
    return businessAssociateitempanel;
  }
  var editBusinessAssociate = function (mid, map_lat, map_long) {
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
      id: "panelBusinessAssociateDetails",
      width: winsize.width * 0.4,
      flex: 1,
      layout: "form",
      //autoHeight: true,
      header: false,
      trackResetOnLoad: false,
      title: "Edit Business Associate",
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
              columnWidth: 1,
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
                  fieldLabel: "City",
                  id: "baCity",
                  name: "baCity",
                  tabIndex: 401,
                  inputType: "text",
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
                  fieldLabel: "Type of Business",
                  emptyText: "Choose Type",
                  id: "businessType",
                  name: "businessType",
                  tabIndex: 402,
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
                      { id: "1", name: "Company" },
                      { id: "2", name: "LLP" },{ id: "3", name: "Firm" },{ id: "4", name: "Trust" },
                      { id: "5", name: "Proprietorship" }
                    ],
                  }),
                  triggerAction: "all",
                  minChars: 2,
                  displayField: "name",
                  valueField: "id",
                  hiddenName: "businessType",
                  listeners: {
                    select: function () {

                    },
                  },
                },{
                  xtype: "combo",
                  fieldLabel: "Type",
                  emptyText: "Choose Type",
                  id: "baType",
                  name: "baType",
                  tabIndex: 402,
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
                      ///{ id: "1", name: "Area Associate" },
                      { id: "2", name: "Business Associate" },
                    ],
                  }),
                  triggerAction: "all",
                  minChars: 2,
                  displayField: "name",
                  valueField: "id",
                  hiddenName: "baType",
                  value:2,
                  hidden:true,
                  listeners: {
                    select: function () {

                    },
                  },
                }
              ],
            },  
            {
              columnWidth: 0.33,
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
                  allowBlank: false,
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
              columnWidth: 0.33,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "textfield",
                  fieldLabel: "Contact No",
                  id: "baMobileNo",
                  name: "baMobileNo",
                  //vtype: "phonespec",
                  tabIndex: 412,
                  anchor: "97%",
                  allowBlank: false,
                  maxLength: 15,
                  minLength: 10,
                  maxLengthText: "The maximum length for this field is 10",
                  listeners: {
                    change: function (field) {
                        if (field.getValue().charAt(0) == '0') {
                            field.setValue(field.getValue().slice(1));
                        }
                    }
                }
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
                      Ext.getCmp('baType').setValue(2);
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
                }
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
              },
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
              columnWidth: .33,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [mkCombo({
                  type: STATUS_COMBO_DATA,
                  value: "id",
                  display: "text",
                  allowBlank: false,
                  name: "baStatus",
                  fieldLabel: "Status",
                  emptyText: "Set status..",
                  tabIndex: 805,
                  id: "baStatus",
                })]
        }
          ],
        },
        {
          xtype: "textarea",
          height: 50,
          fieldLabel: "Address",
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
        },{
          xtype: "textfield",
          fieldLabel: "Location",
          id: "baLocation",
          name: "baLocation",
          anchor: "98%",
          //allowBlank: false,
          tabIndex: 101,
          maxValue: 100,
          listeners: {
            focus: function () {
              initAutocompleteTextBA();
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
                }
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
                  maxLength:18,
                  name: "baPincode",
                  //allowBlank: false,
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
                  //allowBlank: false,
                },
              ],
            },
          ],
        },
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
        }
        
      ],listeners:{
        afterrender:function(){
          Ext.getCmp('baType').setValue(2);
        }
      }
    });
    return _partyPanel;
  };
  var gridSelectionChangedArea = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridPanelAreaMain").getSelectionModel().getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridPanelAreaMain")
        .getSelectionModel()
        .getSelections()[0].data.id;
      Application.BusinessAssociate.ViewArea(ID);
      Application.BusinessAssociate.Cache.id = ID;
    }
  };
  var AreaMasterDetailsView = function () {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var apikey = _SESSION.apikey;
    var src =
      "?module=area&op=AreadetailsView&id=" +
      Application.BusinessAssociate.Cache.id +
      "&tstamp=" +
      t_stamp +
      "&apikey=" +
      apikey;
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "panelSlbeesAreaDetailsView",
      items: [
        {
          html:
            '<iframe id="downloadIframearea" name="downloadIframearea" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' +
            src +
            '"; ></iframe>',
        },
      ],
    });
  };

  var masterPanelforArea = function (id) {
    //console.log("Iam from main panel");
    var masterPanelforArea = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Manage Area",
      id: id,
      items: [
        AreaMainGrid(),
        new Ext.Panel({
          title: "Area Details",
          frame: false,
          border: false,
          region: "east",
          width: winsize.width * 0.45,
          cls: "left_side_panel",
          id: "panelAreaParent",
          height: winsize.height * 0.7,
          autoScroll: true,
          items: [
            AreaTypeForm(),
            {
              region: "north",
              hideBorders: true,
              border: false,
              autoHeight: true,
              items: AreaMasterDetailsView(),
            },
          ],
          buttonAlign: "right",
          fbar: [
            {
              text: "Edit",
              // iconCls: 'left-right-buttons',
              id: "buttonAreaEdit",
              icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
              tabIndex: 110,
              handler: function () {
                var ID = Ext.getCmp("gridPanelAreaMain")
                  .getSelectionModel()
                  .getSelections()[0].data.id;
                Application.BusinessAssociate.EditAreaForm(ID);
              },
            },
            {
              text: "Cancel",
              tabIndex: 109,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
              id: "buttonAreaCancel",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridPanelAreaMain")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp("gridPanelAreaMain")
                    .getSelectionModel()
                    .getSelections()[0].data.id;
                  Application.BusinessAssociate.ViewArea(ID);
                }
              },
            },
            {
              text: "Save",
              tabIndex: 108,
              cls: "left-right-buttons",
              id: "buttonAreaSave",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              hidden: true,
              handler: function () {
                saveAreaType();
              },
            },
          ],
        }),
      ],
    });
    return masterPanelforArea;
  };
  var AreaMainGrid = function () {
    var areastore = AreaStore();
    var areagridfilters = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "areaName",
        },{
          type: "string",
          dataIndex: "st_name",
        },
        {
          type: "string",
          dataIndex: "dst_Name",
        },
      ],
    });
    areagridfilters.remote = true;
    areagridfilters.autoReload = true;
    var areagrid = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: true,
      border: false,
      loadMask: true,
      store: areastore,
      iconCls: "money",
      id: "gridPanelAreaMain",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), areagridfilters],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Name",
          dataIndex: "areaName",
          sortable: true,
          tooltip: "Title",
          hideable: false,
        },
        {
          header: "Location",
          dataIndex: "areaLocation",
          sortable: true,
          tooltip: "Location",
          hideable: true,
        },
        {
          header: "Span",
          dataIndex: "areaSpan",
          sortable: true,
          tooltip: "Span",
          hideable: true,
        },
        {
          header: "Latitude",
          dataIndex: "areaLatitude",
          sortable: true,
          tooltip: "Latitude",
          hideable: true,
        },
        {
          header: "Longitude",
          dataIndex: "areaLongitude",
          sortable: true,
          tooltip: "Longitude",
          hideable: true,
        },
        {
          header: "Associate",
          dataIndex: "areaBusinessAssociateName",
          sortable: true,
          tooltip: "Associate",
          hideable: true,
        },{
          header: STATE,
          dataIndex: "st_name",
          sortable: true,
          tooltip: STATE,
          hideable: true,
        },{
          header: DISTRICT,
          dataIndex: "dst_Name",
          sortable: true,
          tooltip: DISTRICT,
          hideable: true,
        },
        {
          xtype: "actioncolumn",
          //header: 'Action',
          hideable: true,
          iconCls: "downarrow",
          tooltip: "Choose Actions",
          listeners: {
            click: function (a, grid, rowindex, e) {
              var record = grid.store.getAt(rowindex);
              grid.getSelectionModel().selectRow(rowindex);
              areaActionMenu.showAt(e.getXY());
            },
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: recs_per_page,
        store: areastore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedArea,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("id");
          if (!Ext.isEmpty(ID)) {
            Application.BusinessAssociate.Cache.id = ID;
            Application.BusinessAssociate.ViewArea(ID);
          }
        },
        viewready: updatePagination,
        afterrender: function () {
          areastore.load();
        },
      },
      tbar: [
        {
          text: "Add Area",
          hidden:true,
          tooltip: "Add Area ",
          iconCls: "finascop_add",
          handler: function () {
            Application.BusinessAssociate.Cache.areamap_lat = '';
            Application.BusinessAssociate.Cache.areamap_long = '';
            Application.BusinessAssociate.Cache.areamap_lat = 8.507007481504532;
            Application.BusinessAssociate.Cache.areamap_long = 76.95167541503906;
            Application.BusinessAssociate.AreaType = "Add";
            var areaTypeForm = Ext.getCmp("formpanelMasterArea").getForm();
            Ext.getCmp("panelAreaParent").setTitle("Add Area Details");
            loadedForm = null;
            areaTypeForm.reset();
            Ext.getCmp("areaName").focus(false, 100);
            Ext.getCmp("buttonAreaEdit").hide();
            Ext.getCmp("buttonAreaSave").show();
            Ext.getCmp("buttonAreaCancel").show();
            Ext.getCmp("formpanelMasterArea").show();
            Ext.getCmp("panelSlbeesAreaDetailsView").hide();
            Ext.getCmp("panelAreaParent").doLayout();            
            console.log("add new");
          },
        },{
          text: "Map View",
          tooltip: "Map View",
          icon: IMAGE_BASE_PATH + "/default/icons/map.png",
          handler: function () {
            Application.BusinessAssociate.showMap();
          }
        }
      ],
    });
    return areagrid;
  };
  var areaActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Add Market Associate",
        handler: function () {
          var id = Ext.getCmp("gridPanelAreaMain")
            .getSelectionModel()
            .getSelections()[0].data.id;
          Application.BusinessAssociate.mapBusinessAssociate(id);
        },
      },
    ],
  });
  var AreaStore = function () {
    //console.log("iam from store");
    var _areaStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAreas",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          idProperty: "id",
          root: "data",
        },
        [
          "id",
          "areaName",
          "areaLocation",
          "areaSpan",
          "areaLatitude",
          "areaLongitude",
          "areaBusinessAssociate",
          "areaBusinessAssociateName","st_name", "dst_Name"
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
          Ext.getCmp("gridPanelAreaMain").getView().refresh();
          Ext.getCmp("gridPanelAreaMain").getSelectionModel().selectRow(0);
        },
      },
    });
    return _areaStore;
  };
  var saveAreaType = function () {
    project_events = [];
    equp_rent = [];
    if (Ext.getCmp("formpanelMasterArea").getForm().isValid()) {
      Ext.Ajax.request({
        url: modURL + "&op=saveArea",
        method: "POST",
        params: {
          id: Ext.getCmp("id").getValue(),
          areaName: Ext.getCmp("areaName").getValue(),
          areaLocation: Ext.getCmp("areaLocation").getValue(),
          areaSpan: Ext.getCmp("areaSpan").getValue(),
          //areaBusinessAssociate: Ext.getCmp("areaBusinessAssociate").getValue(),
          areaLatitude: Ext.getCmp("areaLatitude").getValue(),
          areaLongitude: Ext.getCmp("areaLongitude").getValue(),
          areaState: Ext.getCmp("areaState").getValue(),
          areaDistrict: Ext.getCmp("areaDistrict").getValue(),
          areaTerritory: Ext.getCmp("areaTerritory").getValue(),
          divisionId: Ext.getCmp("divisionId").getValue(),
        },
        success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
            Application.example.msg("Success", tmp.msg);
            if (Application.BusinessAssociate.AreaTypeAdd == "Add") {
              recs_per_page = updateRecsPerPage(
                Ext.getCmp("gridPanelAreaMain")
              );
              Ext.getCmp("formpanelMasterAreaType").getForm().reset();
              Ext.getCmp("gridPanelAreaMain").store.reload({
                params: {
                  start: 0,
                  limit: recs_per_page,
                },
              });
            } else {
              if (Ext.getCmp("id").getValue() > 0) {
                Ext.getCmp("gridPanelAreaMain").selModel.getSelected().data =
                  tmp.data;
              }

              Ext.getCmp("gridPanelAreaMain").getStore().reload();
              Ext.getCmp("gridPanelAreaMain").getView().refresh();
            }
            Application.BusinessAssociate.AreaTypeAdd = "";
            Application.BusinessAssociate.ViewArea(tmp.data.id);
          } else if (tmp.success === true && tmp.valid === false) {
            Ext.Msg.alert("Notification.", tmp.msg);
          } else if (tmp.success === true && tmp.img_valid === false) {
            Ext.Msg.alert("Notification.", tmp.msg);
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
      Ext.MessageBox.alert("Notification", "Please enter all required fields");
    }
  };
  function areaCircle(span){
    console.log("areaCircle"); 
    var map,spanValue;
    switch(DISTANCE){
      case 'km':
        spanValue = span*1000;
        break;
      case 'mile':
        spanValue = span/0.00062137;
        break;
    }
    var geocoder = (geocoder = new google.maps.Geocoder());
	  var placeLoaded = document.getElementById("areaLocation").value;
    var areaLatitude = document.getElementById("areaLatitude").value;
    var areaLongitude = document.getElementById("areaLongitude").value;
    
    var latlng = new google.maps.LatLng(areaLatitude, areaLongitude);
    var mapOptions = {
      //center: latlng,
      //zoom: 9,
      //mapTypeId: google.maps.MapTypeId.ROADMAP
  };
    //var el=document.getElementById("areagooglemap");
    //map = new google.maps.Map(el, mapOptions);
    console.log("areaCircle"); 
	  //console.log(map);
      geocoder.geocode({'address': placeLoaded }, function (results, status) {
		  console.log(status);
        if (status == google.maps.GeocoderStatus.OK) {
          if (results[0]) {  
            Ext.getCmp("areagooglemap").clearMarkers();
            my_marker = [];
            my_marker.push({
              //geoCodeAddr: areapincode,
			  geoCodeAddr: placeLoaded,
			  position: results[0].geometry.location,
              setCenter: true,
              marker: {
                title: "Click and Drag to Move Around",
                draggable: true,
              },
              listeners: {
                onFailure: function () {},
                tilesloaded: function (markerAt) {
                  console.log("markerAt", markerAt);
                  Ext.getCmp("areaLatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(markerAt.latLng.lng());
                  
                },
                onSuccess: function (point) {
                  console.log("pointareacircle", point);
                  Ext.getCmp("areaLatitude").setValue(point.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(point.latLng.lng());

                },
                dragend: function (markerAt) {
                  Ext.getCmp("areaLatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(markerAt.latLng.lng());
                },
              },
              icon: null,
            });
          }
        }
      });

      Ext.defer(function () {
		  console.log("my_marker", my_marker);        
		    Ext.getCmp("areagooglemap").addScaleControl();
		    Ext.getCmp("areagooglemap").setCenter = location;
		    Ext.getCmp("areagooglemap").setPosition = location;
        Ext.getCmp("areagooglemap").clearMarkers();
        Ext.getCmp("areagooglemap").repaint(13);
        Ext.getCmp("areagooglemap").addMarkers(my_marker);
        Ext.getCmp("areagooglemap").setRadius(my_marker,spanValue,areaLatitude, areaLongitude);
      }, 1200);        
  }
  function mapLoadBA() {
    var geocoder = (geocoder = new google.maps.Geocoder());
	  var baLocation = document.getElementById("baLocation").value;
    var baPincode = document.getElementById("baPincode").value;
    if(baLocation == ""){
      placeLoaded = baPincode;
    }else{
      placeLoaded = baLocation;
    }
	  console.log(placeLoaded);
      geocoder.geocode({'address': placeLoaded }, function (results, status) {
		  console.log(status);
        if (status == google.maps.GeocoderStatus.OK) {
          if (results[0]) {
			  console.log(results[0]);            
            var bapincode =
              results[0].address_components[
                results[0].address_components.length - 1
              ].long_name;
            Ext.getCmp("bagooglemap").clearMarkers();
            my_marker = [];
            my_marker.push({
              //geoCodeAddr: areapincode,
			  geoCodeAddr: placeLoaded,
			  position: results[0].geometry.location,
              setCenter: true,
              marker: {
                title: "Click and Drag to Move Around",
                draggable: true,
              },
              listeners: {
                onFailure: function () {},
                tilesloaded: function (markerAt) {
                  console.log("markerAt", markerAt);
                  Ext.getCmp("balatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("balongitude").setValue(markerAt.latLng.lng());
                },
                onSuccess: function (point) {
                  console.log("point", point);
                  Ext.getCmp("balatitude").setValue(point.latLng.lat());
                  Ext.getCmp("balongitude").setValue(point.latLng.lng());
                },
                dragend: function (markerAt) {
                  Ext.getCmp("balatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("balongitude").setValue(markerAt.latLng.lng());
                },
              },
              icon: null,
            });
          }
        }
      });

      Ext.defer(function () {
		  console.log("my_marker", my_marker);        
		    Ext.getCmp("bagooglemap").addScaleControl();
		    Ext.getCmp("bagooglemap").setCenter = location;
		    Ext.getCmp("bagooglemap").setPosition = location;
        Ext.getCmp("bagooglemap").clearMarkers();
        Ext.getCmp("bagooglemap").repaint(13);
        Ext.getCmp("bagooglemap").addMarkers(my_marker);
      }, 1200);
    
  }
  function mapLoad() {
    console.log("mapLoad");  

    var geocoder = (geocoder = new google.maps.Geocoder());
	  var placeLoaded = document.getElementById("areaLocation").value;
	  console.log(placeLoaded);
      geocoder.geocode({'address': placeLoaded }, function (results, status) {
		  console.log(status);
        if (status == google.maps.GeocoderStatus.OK) {
          if (results[0]) {
			  console.log(results[0]);
            
            var areapincode =
              results[0].address_components[
                results[0].address_components.length - 1
              ].long_name;
            console.log("areapincode", areapincode);            
            Ext.getCmp("areagooglemap").clearMarkers();
            my_marker = [];
            my_marker.push({
              //geoCodeAddr: areapincode,
			  geoCodeAddr: placeLoaded,
			  position: results[0].geometry.location,
              setCenter: true,
              marker: {
                title: "Click and Drag to Move Around",
                draggable: true,
              },
              listeners: {
                onFailure: function () {},
                tilesloaded: function (markerAt) {
                  console.log("markerAt", markerAt);
                  Ext.getCmp("areaLatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(markerAt.latLng.lng());
                },
                onSuccess: function (point) {
                  console.log("point", point);
                  Ext.getCmp("areaLatitude").setValue(point.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(point.latLng.lng());
                },
                dragend: function (markerAt) {
                  Ext.getCmp("areaLatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(markerAt.latLng.lng());
                },
              },
              icon: null,
            });
          }
        }
      });

      Ext.defer(function () {
		  console.log("my_marker", my_marker);        
		    Ext.getCmp("areagooglemap").addScaleControl();
		    Ext.getCmp("areagooglemap").setCenter = location;
		    Ext.getCmp("areagooglemap").setPosition = location;
        Ext.getCmp("areagooglemap").clearMarkers();
        Ext.getCmp("areagooglemap").repaint(13);
        Ext.getCmp("areagooglemap").addMarkers(my_marker);
      }, 1200);
    
  }
  function initAutocompleteTextBA() {
    var input = document.getElementById("baLocation");
    var searchBox = new google.maps.places.SearchBox(input);
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.addListener("place_changed", function () {
      var place = autocomplete.getPlace();
      var latitude = place.geometry.location.lat();
      var longitude = place.geometry.location.lng();
      var latlng = new google.maps.LatLng(latitude, longitude);
      document.getElementById("balatitude").value = latitude;
      document.getElementById("balongitude").value = longitude;

      var geocoder = (geocoder = new google.maps.Geocoder());
	  var placeLoaded = document.getElementById("baLocation").value;
      geocoder.geocode({'address': placeLoaded }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          if (results[0]) {
            var bapincode =
              results[0].address_components[
                results[0].address_components.length - 1
              ].long_name;
              document.getElementById("baPincode").value = bapincode;
            console.log("bapincode", bapincode);
            console.log("latlng", latlng);
			console.log("place", place);
			console.log("location", results[0].geometry.location);
            //marker.setPosition(place.geometry.location);
            Ext.getCmp("bagooglemap").clearMarkers();
            my_marker = [];
            my_marker.push({
              //geoCodeAddr: areapincode,
			      geoCodeAddr: placeLoaded,
			      position: results[0].geometry.location,
              setCenter: true,
              marker: {
                title: "Click and Drag to Move Around",
                draggable: true,
              },
              listeners: {
                onFailure: function () {},
                tilesloaded: function (markerAt) {
                  console.log("markerAt", markerAt);
                  Ext.getCmp("balatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("balongitude").setValue(markerAt.latLng.lng());
                },
                onSuccess: function (point) {
                  console.log("point", point);
                  Ext.getCmp("balatitude").setValue(point.latLng.lat());
                  Ext.getCmp("balongitude").setValue(point.latLng.lng());
                },
                dragend: function (markerAt) {
                  Ext.getCmp("balatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("balongitude").setValue(markerAt.latLng.lng());
                },
              },
              icon: null,
            });
          }
        }
      });

      Ext.defer(function () {
		  console.log("my_marker", my_marker);       
		Ext.getCmp("bagooglemap").addScaleControl();
		Ext.getCmp("bagooglemap").setCenter = location;
		Ext.getCmp("bagooglemap").setPosition = location;
        Ext.getCmp("bagooglemap").clearMarkers();
        Ext.getCmp("bagooglemap").repaint(13);
        Ext.getCmp("bagooglemap").addMarkers(my_marker);
      }, 1200);
    });
  }
  function initAutocompleteTextArea() {
    console.log("initAutocompleteTextArea");
    var areapincode;
    var input = document.getElementById("areaLocation");
	
    var searchBox = new google.maps.places.SearchBox(input);
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.addListener("place_changed", function () {
      var place = autocomplete.getPlace();
      var latitude = place.geometry.location.lat();
      var longitude = place.geometry.location.lng();
      var latlng = new google.maps.LatLng(latitude, longitude);
      document.getElementById("areaLatitude").value = latitude;
      document.getElementById("areaLongitude").value = longitude;

      var geocoder = (geocoder = new google.maps.Geocoder());
	  var placeLoaded = document.getElementById("areaLocation").value;
      geocoder.geocode({'address': placeLoaded }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          if (results[0]) {
            var address = results[0].formatted_address;
            var areapincode =
              results[0].address_components[
                results[0].address_components.length - 1
              ].long_name;
            var country =
              results[0].address_components[
                results[0].address_components.length - 2
              ].long_name;
            var state =
              results[0].address_components[
                results[0].address_components.length - 3
              ].long_name;
            var city =
              results[0].address_components[
                results[0].address_components.length - 4
              ].long_name;
var location = results[0].geometry.location;
            console.log("areapincode", areapincode);
            console.log("latlng", latlng);
			console.log("place", place);
			console.log("location", results[0].geometry.location);
            //marker.setPosition(place.geometry.location);
            Ext.getCmp("areagooglemap").clearMarkers();
            my_marker = [];
            my_marker.push({
              //geoCodeAddr: areapincode,
			  geoCodeAddr: placeLoaded,
			  position: results[0].geometry.location,
              setCenter: true,
              marker: {
                title: "Click and Drag to Move Around",
                draggable: true,
              },
              listeners: {
                onFailure: function () {},
                tilesloaded: function (markerAt) {
                  console.log("markerAt", markerAt);
                  Ext.getCmp("areaLatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(markerAt.latLng.lng());
                },
                onSuccess: function (point) {
                  console.log("point", point);
                  Ext.getCmp("areaLatitude").setValue(point.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(point.latLng.lng());
                },
                dragend: function (markerAt) {
                  Ext.getCmp("areaLatitude").setValue(markerAt.latLng.lat());
                  Ext.getCmp("areaLongitude").setValue(markerAt.latLng.lng());
                },
              },
              icon: null,
            });
          }
        }
      });

      
      //Ext.getCmp("areagooglemap").setCenter = latlng;	  
      //Ext.getCmp("areagooglemap").clearMarkers();
      //Ext.getCmp("areagooglemap").addMarkers(my_marker);
      Ext.defer(function () {
		  console.log("my_marker", my_marker);
        //var point = Ext.getCmp("areagooglemap").getCenterLatLng();
        //Ext.getCmp("areaLatitude").setValue(point.lat);
        //Ext.getCmp("areaLongitude").setValue(point.lng);
		Ext.getCmp("areagooglemap").addScaleControl();
		Ext.getCmp("areagooglemap").setCenter = location;
		Ext.getCmp("areagooglemap").setPosition = location;
        Ext.getCmp("areagooglemap").clearMarkers();
        Ext.getCmp("areagooglemap").repaint(13);
        Ext.getCmp("areagooglemap").addMarkers(my_marker);
      }, 1200);
    });
  }
  var businessAssociateStore = function () {
    var _businessAssociateStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getbusinessAssociate",
      method: "post",
      fields: ["id", "baName"],
      remoteSort: true,
    });
    return _businessAssociateStore;
  };
  var AreaTypeForm = function () {
    var _businessAssociateStore = businessAssociateStore();
    
    var _territory_store = territoryStoreEdit();
    var _state_store = stateStoreEdit();
    var district_store = districtStoreEdit();
    var _areaTypeFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterArea",
      frame: false,
      border: false,
      hidden: true,
      autoHeight: true,
      autoScroll: true,
      labelWidth: 120,
      labelAlign: "top",
      bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
      items: [
        {
          xtype: "textfield",
          id: "id",
          name: "n[id]",
          hidden: true,
        },
        {
          xtype: "textfield",
          fieldLabel: "Name",
          id: "areaName",
          name: "n[areaName]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 100,
          maxValue: 100,
        },
        {
          xtype: "textfield",
          fieldLabel: "Location",
          id: "areaLocation",
          name: "n[areaLocation]",
          anchor: "98%",
          allowBlank: false,
          tabIndex: 101,
          maxValue: 100,
          listeners: {
            focus: function () {
              initAutocompleteTextArea();
            },
          },
        },{
          xtype: "panel",
          layout: "column",
          frame: false,
          border: false,
          items: [{
            xtype: "hidden",
            id: "divisionId",
            name: "n[divisionId]",
            hidden: true,
          },{
            columnWidth: 0.33,
            layout: "form",
            frame: false,
            border: false,
            labelAlign: "top",
            items: [{
              xtype: "combo",
              fieldLabel: 'Territory',
              name: "areaTerritory",
              id: "areaTerritory",
              hiddenName: "areaTerritory",
              anchor: "97%",
              store: _territory_store,
              valueField: "id",
              tabIndex: 102,
              displayField: "name",
              forceSelection: true,
              triggerAction: "all",
              typeAhead: true,
              selectOnFocus: true,
              mode: "local",
              listeners: {
                select: function () {
                  var value = Ext.getCmp("areaTerritory").getValue();                    
                  
                },
              },
            }]
          },{
            columnWidth: 0.33,
            layout: "form",
            frame: false,
            border: false,
            labelAlign: "top",
            items: [
              {
                xtype: "combo",
                fieldLabel: STATE,
                name: "areaState",
                id: "areaState",
                hiddenName: "state",
                anchor: "97%",
                store: _state_store,
                valueField: "st_ID",
                tabIndex: 103,
                displayField: "st_name",
                forceSelection: true,
                triggerAction: "all",
                typeAhead: true,
                allowBlank: false,
                selectOnFocus: true,
                mode: "local",
                listeners: {
                  select: function () {
                    var value = Ext.getCmp("areaDistrict").getValue();
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
          },{
            columnWidth: 0.33,
            layout: "form",
            frame: false,
            border: false,
            labelAlign: "top",
            hideBorders: true,
            items: [
              {
                xtype: "combo",
                fieldLabel: DISTRICT,
                id: "areaDistrict",
                name: "areaDistrict",
                tabIndex: 104,
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
          }]
        },
        {
          xtype: "gmappanel",
          gmapType: "map",
          id: "areagooglemap",
          zoomLevel: 8,
          height: 230,
          minGeoAccuracy: 4,
          scaleControl: true,
          mapConfOpts: [
            "enableScrollWheelZoom",
            "enableDoubleClickZoom",
            "enableDragging",
          ],
          mapControls: ["GSmallMapControl", "GMapTypeControl"],
          setCenter: {
            lat: DEF_LATITUDE,
            lng: DEF_LONGITUDE,
            //lat:  Application.BusinessAssociate.Cache.areamap_lat,
            //lng:  Application.BusinessAssociate.Cache.areamap_long,
          },
          repaint: function (zoomlevel) {
            var gmappanel = Ext.getCmp("areagooglemap");
            if (zoomlevel) {
              gmappanel.zoomLevel = zoomlevel;
              gmappanel.getMap().setZoom(zoomlevel);
            }
            gmappanel.onMapReady();
          },
          markers: areaMarker,
        },
        {
          xtype: "compositefield",
          fieldLabel: "Latitude, Longitude & Span",
          combineErrors: false,
          items: [
            {
              xtype: "textfield",
              id: "areaLatitude",
              emptyText: "Latitude",
              tabIndex: 105,
              allowBlank: false,
              name: "n[areaLatitude]",
            },
            {
              xtype: "textfield",
              id: "areaLongitude",
              emptyText: "Longitude",
              tabIndex: 106,
              allowBlank: false,
              name: "n[areaLongitude]",
            },
            {
              xtype: "textfield",
              emptyText: "Area Span",
              fieldLabel: "Area Span",
              id: "areaSpan",
              allowBlank: false,
              tabIndex: 107,
              name: "n[areaSpan]",
              listeners:{
                change:function(){
                  var areaSpan = Ext.getCmp('areaSpan').getValue();
                  areaCircle(areaSpan);
                }
              }
            },{ html: "&nbsp;"+DISTANCE}
          ],
        },
      ],
      listeners: {
        afterrender: function () {
          if (Ext.isEmpty(Ext.getCmp("id").getValue())) {
            
          }
        },
      },
    });
    return _areaTypeFormPanel;
  };
  var BusinessPartnerMainPanel = function (id) {
    var mid = 0;
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "bpckground-color": "white" },
      hideBorders: true,
      id: id,
      title: "Business Partner",
      items: [
        BusinessPartnerGrid(),
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
            BusinessPartnerItem(mid),
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
                  id: "buttonBusinessPartnerCancel",
                  tabIndex: 414,
                  icon: IMAGE_BASE_PATH + "/default/icons/cancel.png",
                  iconCls: "my-icon61",
                  hidden: true,
                  handler: function () {
                    Ext.getCmp(
                      "panelBusinessPartnerDetails"
                    ).getForm().trackResetOnLoad = false;
                    Ext.getCmp("panelBusinessPartnerDetails").hide();

                    Ext.getCmp("buttonBusinessPartnerSave").hide();
                    Ext.getCmp("buttonBusinessPartnerCancel").hide();
                    Ext.getCmp("tabpanelBusinessPartner").show();
                    var grid = Ext.getCmp("gridpanelBusinessPartner");
                    grid.getSelectionModel().clearSelections();

                    var form = Ext.getCmp(
                      "panelBusinessPartnerDetails"
                    ).getForm();
                    form.reset();
                    Ext.getCmp("gridpanelBusinessPartner").getStore().load();
                  },
                },
                {
                  text: "Save",
                  xtype: "button",
                  width: 75,
                  columnWidth: 0.15,
                  id: "buttonBusinessPartnerSave",
                  tabIndex: 413,
                  icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
                  iconCls: "thes_save",
                  hidden: true,
                  handler: function () {
                    var cust_id = Ext.getCmp("partnerhiddenid").getValue();
                    if (
                      Ext.getCmp("panelBusinessPartnerDetails")
                        .getForm()
                        .isValid()
                    ) {
                      if (
                        cust_id == "" ||
                        cust_id == 0 ||
                        cust_id == "undefined"
                      ) {
                        Application.BusinessAssociate.saveBusinessPartnerDetails(
                          0
                        );
                      } else {
                        Application.BusinessAssociate.saveBusinessPartnerDetails(
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
                  width: 75,
                  columnWidth: 0.15,
                  id: "buttonBusinessPartnerEdit",
                  icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
                  hidden: true,
                  handler: function () {
                    Ext.getCmp("property_grid_partner").hide();
                    Ext.getCmp("panelBusinessPartnerDetails").show();
                    Ext.getCmp("panelBusinessPartnerDetails").setTitle(
                      "Edit Form"
                    );
                    Ext.getCmp("buttonBusinessPartnerCancel").show();
                    Ext.getCmp("buttonBusinessPartnerSave").show();
                    Ext.getCmp("buttonBusinessPartnerEdit").hide();
                    Ext.getCmp("tabpanelBusinessPartner").hide();
                    var bpId = Ext.getCmp("gridpanelBusinessPartner")
                      .getSelectionModel()
                      .getSelections()[0].data.bpId;
                    var _Editformdata = Ext.getCmp(
                      "panelBusinessPartnerDetails"
                    ).getForm();
                    console.log("bpId");
                    console.log(bpId);
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");

                    Ext.getCmp("partnerhiddenid").setValue(bpId);

                    if (bpId != 0 || bpId != "") {
                      _Editformdata.load({
                        params: {
                          bpId: bpId,
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp,
                        },
                        url: modURL + "&op=editFormDataLoadPartner",
                        waitMsg: "Loading...",
                        success: function (form, action) {
                          var tmp = Ext.decode(action.response.responseText);
                          console.log("temp:", tmp);
                          Ext.getCmp("st_id").getStore().load();
                          Ext.getCmp("dst_Id").getStore().baseParams.st_Id =
                            tmp.data.st_id;
                          Ext.getCmp("dst_Id").getStore().load();
                          Ext.getCmp("dst_Id").setRawValue(tmp.data.dst_Name);
                          
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
                            }
                                                

                          Ext.getCmp("baEmail").setReadOnly(true);
                          xt.getCmp("baMobileNo").setReadOnly(true);
                        },
                      });
                    }
                    BusinessPartnerid_load =
                      Ext.getCmp("partnerhiddenid").getValue();
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
  var BusinessPartnerGridStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listBusinessPartner",
      method: "post",
      fields: [
        "bpId",
        "bpName",
        "bpPhone",
        "bpAddress",
        "bpCity",
        "bpPincode",
        "bpGSTIN",
        "dst_Id",
        "br_id",
        "bpContactPerson",
        "bpMobileNo",
        "bpEmail",
        "bpPanNo",
        "bplatitude",
        "bplongitude",
        "dst_Name",
        "st_name",
      ],
      totalProperty: "totalCount",
      root: "data",
      listeners: {
        beforeload: function () {
          //this.baseParams.partytype = partytype;
        },
        load: function () {
          Ext.getCmp("gridpanelBusinessPartner")
            .getSelectionModel()
            .selectRow(0);
          if (
            !Ext.isEmpty(
              Ext.getCmp("gridpanelBusinessPartner")
                .getSelectionModel()
                .getSelections()
            )
          ) {
            Ext.getCmp("property_grid_partner").hide();

            Ext.getCmp("partnerhiddenid").reset();

            Ext.getCmp("buttonBusinessPartnerCancel").show();
            Ext.getCmp("buttonBusinessPartnerSave").show();
            Ext.getCmp("buttonBusinessPartnerEdit").hide();
            var data = Ext.getCmp("gridpanelBusinessPartner")
              .getSelectionModel()
              .getSelections()[0].data;
            Application.BusinessAssociate.ViewModeBP(data);
          }
        },
      },
    });
    return store;
  };
  var customerGridAction = function () {
    var action = new Ext.ux.grid.RowActions({
      autoWidth: false,
      hideMode: "display",
      width: 50,
      actions: [
        {
          sortable: false,
          tooltip: "Edit Business Partner",
          iconCls: "finascop_edit",
          callback: function (grid, rec, row, col) {
            Ext.getCmp("property_grid_partner").hide();
            Ext.getCmp("panelBusinessPartnerDetails").show();
            Ext.getCmp("buttonBusinessPartnerCancel").show();
            Ext.getCmp("buttonBusinessPartnerSave").show();
            BusinessPartnerItem(rec.data);
          },
        },
      ],
    });
    return action;
  };
  var BusinessPartnerGrid = function () {
    var grid_store = BusinessPartnerGridStore();
    var action = customerGridAction();
    var customerGrid_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "bpName",
        },
        {
          type: "string",
          dataIndex: "bpLname",
        },
        {
          type: "string",
          dataIndex: "bpGSTIN",
        },
        {
          type: "string",
          dataIndex: "bpCity",
        },
        {
          type: "string",
          dataIndex: "bpPincode",
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
      id: "gridpanelBusinessPartner",
      region: "center",
      frame: true,
      border: false,
      layout: "fit",
      loadMask: true,
      plugins: [customerGrid_filter, action],
      columns: [
        {
          header: "Name of Business",
          sortable: true,
          dataIndex: "bpName",
          width: 175,
        },
        {
          header: GST,
          sortable: true,
          dataIndex: "bpGSTIN",
          width: 175,
        },
        {
          header: "City",
          sortable: true,
          dataIndex: "bpCity",
          width: 175,
        },
        {
          header: "Post Codes",
          sortable: true,
          dataIndex: "bpPincode",
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
        },
      ],
      viewConfig: {
        forceFit: true,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
      },
      tbar: [
        {
          xtype: "button",
          text: "Create Business Partner",
          tooltip: "Create Business Partner",
          iconCls: "finascop_add",
          handler: function () {
            var form = Ext.getCmp("panelBusinessPartnerDetails").getForm();
            form.trackResetOnLoad = false;
            form.reset();

            Ext.getCmp("property_grid_partner").hide();

            Ext.getCmp("partnerhiddenid").reset();

            Ext.getCmp("bpLname").reset();
            Ext.getCmp("panelBusinessPartnerDetails").setTitle("Add Form");

            Ext.getCmp("panelBusinessPartnerDetails").show();
            Ext.getCmp("buttonBusinessPartnerCancel").show();
            Ext.getCmp("buttonBusinessPartnerSave").show();
            Ext.getCmp("buttonBusinessPartnerEdit").hide();
            Ext.getCmp("tabpanelBusinessPartner").hide();
          },
        },
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
        viewready: updatePagination,
        rowclick: function (grid, rowIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("bpId");
          var data = record.data;
          Application.BusinessAssociate.ViewModeBP(data);
        },
      },
    });
    return SP_grid;
  };
  function BusinessPartnerItem(mid) {
    //if(mid != 0)
    console.log("mid", mid);
    var lati, longi;
    if (mid == 0) {
      lati = 8.507007481504532;
      longi = 76.95167541503906;
    }
    var BusPartnerEditPanel = editBusinessPartner(mid, lati, longi);

    var BusinessPartneritempanel = new Ext.Panel({
      frame: false,
      border: false,
      collapsible: true,
      title: "Business Partner details",
      flex: 1,
      layout: "vbox",
      layoutConfig: {
        align: "stretch",
        pack: "start",
      },
      id: "panelBusinessPartner",
      items: [
        BusPartnerEditPanel,
        new Ext.TabPanel({
          activeTab: 0,
          flex: 1,
          plain: true,
          frame: true,
          id: "tabpanelBusinessPartner",
          items: [
            {
              title: "Details",
              id: "property_grid_partner",
              width: winsize.width * 0.4,
              items: [
                {
                  xtype: "hidden",
                  id: "partnerhiddenid",
                  hidden: true,
                },
              ],
              tpl: new Ext.XTemplate(
                '<div class="details-outer">',
                '<table border="0" width="99%" class="details_view_table">',
                "<tr><th>Name :</th><td> {bpName}</td></tr>",
                "<tr><th>Address:</th><td>  {bpAddress}</td></tr>",
                "<tr><th>Email:</th><td>  {bpEmail}</td></tr>",
                "<tr><th>Contact Person:</th><td>  {bpContactPerson}</td></tr>",
                "<tr><th>"+PAN+".:</th><td>  {bpPanNo}</td></tr>",
                "<tr><th>Mobile No.:</th><td>  {bpMobileNo}</td></tr>",
                "<tr><th>City :</th><td>  {bpCity}</td></tr>",
                "<tr><th>Post Codes :</th><td>  {bpPincode}</td></tr>",
                "<tr><th>"+STATE+" :</th><td>  {st_name}</td></tr>",
                "<tr><th>"+DISTRICT+" :</th><td>  {dst_Name}</td></tr>",
                "<tr><th>"+GST+" :</th><td>  {bpGSTIN}</td></tr>",
                "</table>",
                "</div>"
              ),
            },
          ],
          listeners: {
            tabchange: function (sd, tab) {
              if (tab.id == "property_grid_partner") {
                var _BusinessPartnerId =
                  Ext.getCmp("partnerhiddenid").getValue();
                console.log("BusinessPartner id is", _BusinessPartnerId);
                if (
                  _BusinessPartnerId == "" ||
                  _BusinessPartnerId == undefined
                ) {
                  Ext.getCmp("buttonBusinessPartnerEdit").hide();
                } else {
                  Ext.getCmp("buttonBusinessPartnerEdit").show();
                }
              }
            },
          },
        }),
      ],
    });
    return BusinessPartneritempanel;
  }
  var editBusinessPartner = function (mid, map_lat, map_long) {
    //var _associated_Branch = associatedBranch();
    var _state_store = stateStoreEdit();
    var district_store = districtStoreEdit();
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
            Ext.getCmp("bplatitude").setValue(markerAt.latLng.lat());
            Ext.getCmp("bplongitude").setValue(markerAt.latLng.lng());
          },
        },
      },
    ];

    var _patnerBisinessPanel = new Ext.FormPanel({
      frame: false,
      border: false,
      hideBorders: true,
      bodyStyle: { "bpckground-color": "white", padding: "5px 5px 5px 10px" },
      monitorValid: true,
      id: "panelBusinessPartnerDetails",
      width: winsize.width * 0.4,
      flex: 1,
      layout: "form",
      //autoHeight: true,
      header: false,
      trackResetOnLoad: false,
      title: "Edit Business Partner",
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
              id: "bpId",
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
                  fieldLabel: "Name of Business",
                  id: "bpName",
                  name: "bpName",
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
                  id: "bpLname",
                  hidden: true,
                  name: "bpLname",
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
                  fieldLabel: "City",
                  id: "bpCity",
                  name: "bpCity",
                  tabIndex: 401,
                  inputType: "text",
                  anchor: "97%",
                  allowBlank: false,
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
          xtype: "textarea",
          height: 50,
          fieldLabel: "Addresss",
          id: "bpAddress",
          name: "bpAddress",
          tabIndex: 402,
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
                  id: "st_id",
                  hiddenName: "state",
                  anchor: "97%",
                  store: _state_store,
                  valueField: "st_ID",
                  tabIndex: 403,
                  displayField: "st_name",
                  forceSelection: true,
                  triggerAction: "all",
                  typeAhead: true,
                  allowBlank: false,
                  selectOnFocus: true,
                  mode: "local",
                  listeners: {
                    select: function () {
                      var value = Ext.getCmp("dst_Id").getValue();
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
                  id: "dst_Id",
                  name: "dst_Id",
                  tabIndex: 404,
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
                  id: "bpPincode",
                  anchor: "98%",
                  xtype: "textfield",
                  name: "bpPincode",
                  allowBlank: false,
                  //vtype: 'phonespec',
                  tabIndex: 405,
                  listeners: {
                    change: function () {
                      if (!Ext.isEmpty(Ext.getCmp("bpPincode").getValue())) {
                        Ext.getCmp("bpmapbutton").enable();
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
              columnWidth: 0.4,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              items: [
                {
                  xtype: "textfield",
                  fieldLabel: GST,
                  id: "bpGSTIN",
                  name: "bpGSTIN",
                  tabIndex: 406,
                  anchor: "97%",
                  //allowBlank: false,
                  msgTarget: "under",
                  //gstText: 'Not a valid GST Number.',
                  //vtype: 'gst'
                },
              ],
            },
            {
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "textfield",
                  fieldLabel: PAN+" No",
                  id: "bpPanNo",
                  tabIndex: 407,
                  name: "bpPanNo",
                  anchor: "97%",
                  allowBlank: false,
                },
              ],
            },
            {
              columnWidth: 0.3,
              layout: "form",
              frame: false,
              border: false,
              labelAlign: "top",
              hideBorders: true,
              items: [
                {
                  xtype: "combo",
                  fieldLabel: "Type of Organization",
                  emptyText: "Type",
                  id: "bpType",
                  name: "bpType",
                  tabIndex: 402,
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
                      { id: "1", name: "Sole Proprietorship" },
                      { id: "2", name: "Partnership" },
                      { id: "3", name: "LLP" },
                      { id: "4", name: "Private limited company" },
                      { id: "5", name: "Society" },
                      { id: "6", name: "Trust" },
                    ],
                  }),
                  triggerAction: "all",
                  minChars: 2,
                  displayField: "name",
                  valueField: "id",
                  hiddenName: "bpType",
                  listeners: {
                    select: function () {},
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
                  fieldLabel: "Contact Person",
                  id: "bpContactPerson",
                  name: "bpContactPerson",
                  tabIndex: 408,
                  anchor: "97%",
                  allowBlank: false,
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
                  id: "bpMobileNo",
                  name: "bpMobileNo",
                  vtype: "phonespec",
                  tabIndex: 409,
                  anchor: "97%",
                  allowBlank: false,
                  maxLength: 15,
                  minLength: 10,
                  maxLengthText: "The maximum length for this field is 10",
                  listeners: {
                    change: function (field) {
                        if (field.getValue().charAt(0) == '0') {
                            field.setValue(field.getValue().slice(1));
                        }
                    }
                }
                },
              ],
            },
          ],
        },
        {
          xtype: "textfield",
          fieldLabel: "Email ID",
          id: "bpEmail",
          name: "bpEmail",
          tabIndex: 410,
          anchor: "99%",
          // allowBlank: false,
          vtype: "email",
        },
        {
          layout: "form",
          columnWidth: 0.35,
          border: false,
          items: [
            {
              xtype: "gmappanel",
              gmapType: "map",
              id: "bpgooglemap",
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
                var gmappanel = Ext.getCmp("bpgooglemap");
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
                          id: "bpmapbutton",
                          name: "bpmapbutton",
                          disabled: true,
                          text: "Find Coordinates",
                          tabIndex: 424,
                          handler: function () {
                            Ext.getCmp("bpgooglemap").clearMarkers();
                            my_marker = [];
                            my_marker.push({
                              geoCodeAddr: Ext.getCmp("bpPincode").getValue(),
                              setCenter: true,
                              marker: {
                                title: "Click and Drag to Move Around",
                                draggable: true,
                              },
                              listeners: {
                                onFailure: function () {},
                                tilesloaded: function (markerAt) {
                                  console.log("markerAt", markerAt);
                                  Ext.getCmp("bplatitude").setValue(
                                    markerAt.latLng.lat()
                                  );
                                  Ext.getCmp("bplongitude").setValue(
                                    markerAt.latLng.lng()
                                  );
                                },
                                onSuccess: function (point) {
                                  console.log("point", point);
                                  Ext.getCmp("bplatitude").setValue(
                                    point.latLng.lat()
                                  );
                                  Ext.getCmp("bplongitude").setValue(
                                    point.latLng.lng()
                                  );
                                },
                                dragend: function (markerAt) {
                                  Ext.getCmp("bplatitude").setValue(
                                    markerAt.latLng.lat()
                                  );
                                  Ext.getCmp("bplongitude").setValue(
                                    markerAt.latLng.lng()
                                  );
                                },
                              },
                              icon: null,
                            });
                            Ext.getCmp("bpgooglemap").addScaleControl();
                            Ext.getCmp("bpgooglemap").clearMarkers();
                            Ext.getCmp("bpgooglemap").addMarkers(my_marker);
                            Ext.defer(function () {
                              var point =
                                Ext.getCmp("bpgooglemap").getCenterLatLng();
                              Ext.getCmp("bplatitude").setValue(point.lat);
                              Ext.getCmp("bplongitude").setValue(point.lng);
                              Ext.getCmp("bpgooglemap").clearMarkers();
                              Ext.getCmp("bpgooglemap").repaint(13);
                              Ext.getCmp("bpgooglemap").addMarkers(my_marker);
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
                          tabIndex: 425,
                          id: "bplatitude",
                          name: "bplatitude",
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
                          tabIndex: 426,
                          id: "bplongitude",
                          name: "bplongitude",
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
    return _patnerBisinessPanel;
  };
  var baStore = function () {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=ListBatoMap",
        method: "post",
      }),
      fields: ["id", "baName", "baMobileNo"],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelAssignBa").getSelectionModel().selectRow(0);
        },
      },
    });
    return _Store;
  };
  var mapBusinessAssociateGrid = function () {
    var searchState_store = stateStoreEdit();
    var serchdistrict_store = districtStoreEdit();
    var _baStore = baStore();
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
      id: "gridpanelAssignBa",
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Name",
          dataIndex: "baName",
          sortable: true,
          tooltip: "Name",
          hideable: true,
        },
        {
          header: "Mobile",
          dataIndex: "baMobileNo",
          sortable: true,
          tooltip: "Mobile",
          hideable: true,
        },
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {},
      },
      tbar: [
        { html: STATE+" : &nbsp;" },
        {
          xtype: "combo",
          fieldLabel: STATE,
          id: "searchState",
          hiddenName: "searchState",
          anchor: "97%",
          store: searchState_store,
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
              var value = Ext.getCmp("searchDistrict").getValue();

              serchdistrict_store.baseParams.st_Id = this.value;
              serchdistrict_store.load();
            },
          },
        },
        { html: DISTRICT+": &nbsp;" },
        {
          xtype: "combo",
          fieldLabel: DISTRICT,
          id: "searchDistrict",
          name: "searchDistrict",
          tabIndex: 407,
          hiddenName: "searchDistrict",
          anchor: "97%",
          allowBlank: false,
          store: serchdistrict_store,
          valueField: "dst_ID",
          displayField: "dst_Name",
          forceSelection: true,
          triggerAction: "all",
          typeAhead: true,
          selectOnFocus: true,
          mode: "local",
        },
        {
          xtype: "button",
          text: "Show",
          style: "padding-left: 10px;",
          handler: function () {
            Ext.getCmp("gridpanelAssignBa").getStore().baseParams.searchState =
              Ext.getCmp("searchState").getValue();
            Ext.getCmp(
              "gridpanelAssignBa"
            ).getStore().baseParams.searchDistrict =
              Ext.getCmp("searchDistrict").getValue();
            Ext.getCmp("gridpanelAssignBa").getStore().load();
          },
        },
      ],
    });
    return _bagridPanel;
  };
  var _relationshipOfficerGrid = function (customerId) {
    var _dispatchgridPanel = new Ext.grid.GridPanel({
      layout: "fit",
      region: "center",
      frame: false,
      border: false,
      loadMask: true,
      store: _baRelOffcrStore(customerId),
      iconCls: "money",
      autoScroll: true,
      width: winsize.width * 0.4,
      height: 300,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforBaRealtOffcr",
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
          hidden: true,
          tooltip: "Mobile",
          hideable: false,
          width: 150,
        },
        {
          header: "Contact Person",
          dataIndex: "roContactPerson",
          sortable: true,
          tooltip: "Contact Person",
          hideable: false,
        },
        {
          header: "Contact No",
          dataIndex: "roContactMobile",
          sortable: true,
          hidden: true,
          tooltip: "Contact No",
          hideable: false,
        },
        {
          header: "Pin",
          dataIndex: "roPincode",
          sortable: true,
          hidden: true,
          tooltip: "Pin",
          hideable: false,
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
              roActionMenu.showAt(e.getXY());
              //action
            },
          },
        },
      ],
      viewConfig: {
        forceFit: true,
        getRowClass: function (record, index, params, store) {},
      },
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedro,
        },
      }),
      listeners: {
        afterrender: function () {
          Ext.getCmp("gridpanelforBaRealtOffcr")
            .getStore()
            .load({
              params: {
                baId: customerId,
              },
            });
        },
      },
    });
    return _dispatchgridPanel;
  };
  var roActionMenu = new Ext.menu.Menu({
    items: [
      {
        text: "Update Status",
        handler: function () {
          var roId = Ext.getCmp("gridpanelforBaRealtOffcr")
            .getSelectionModel()
            .getSelections()[0].data.id;
          updateROStatus(roId);
        },
      },
    ],
  });
  var updateROStatus = function (roId) {
    var updateROStatusWindow = new Ext.Window({
      id: "windowToUpdateROStatus",
      iconCls: "vender-items",
      shadow: false,
      width: winsize.width * 0.4,
      height: winsize.height * 0.4,
      title: "Update Status",
      layout: "fit",
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      modal: true,
      items: [
        new Ext.FormPanel({
          layout: "form",
          id: "updateROStatusFormPanel",
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
                  columnWidth: 1,
                  layout: "form",
                  frame: false,
                  border: false,
                  labelAlign: "top",
                  items: [
                    {
                      xtype: "combo",
                      fieldLabel: "Status",
                      emptyText: "Choose Status",
                      id: "crmStatus",
                      name: "crmStatus",
                      tabIndex: 402,
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
                          { id: "2", name: "Approved" },
                          { id: "3", name: "Hold" },
                          { id: "4", name: "Rejected" },
                        ],
                      }),
                      triggerAction: "all",
                      minChars: 2,
                      displayField: "name",
                      valueField: "id",
                      hiddenName: "crmStatus",
                      listeners: {
                        select: function () {},
                      },
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
            updateROStatusWindow.close();
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
                url: modURL + "&op=updateRoStatus",
                params: {
                  roId: id,
                  roStatus: crmStatus,
                  roRemarks: crmRemarks,
                },
                success: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp("gridMarketingProspectsList")
                      .getStore()
                      .load({
                        callback: function (record, options, success) {
                          updateROStatusWindow.close();
                          Application.example.msg("Notification", tmp.msg);
                          var gridPanel = Ext.getCmp(
                            "gridMarketingProspectsList"
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

    updateROStatusWindow.doLayout();
    updateROStatusWindow.show();
    updateROStatusWindow.center();
  };
  var gridSelectionChangedro = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelforBaRealtOffcr")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelforBaRealtOffcr")
        .getSelectionModel()
        .getSelections()[0].data.id;
      Application.BusinessAssociate.roViewMode(ID);
    }
  };
  var relationshipOfficerDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      border: false,
      hideBorders: true,
      autoHeight: true,
      id: "roMasterDetailsViewPanel",
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
  var _baRelOffcrStore = function (customerId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listRelationshipOfficer",
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
        "roArea",
      ],
      totalProperty: "totalCount",
      root: "data",
      remoteSort: true,
      autoLoad: false,
      listeners: {
        beforeload: function (store, e) {
          this.baseParams.baId = customerId;
        },
        load: function () {
          Ext.getCmp("gridpanelforBaRealtOffcr").getView().refresh();
          Ext.getCmp("gridpanelforBaRealtOffcr")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _Store;
  };
  var areaSearchForm  = function(){
    var _state_store = stateStoreEdit();
    var district_store = districtStoreEdit();
    var area_store = areaStoreEdit()
    var form = new Ext.FormPanel({
      id: 'area_search_form',
      labelAlign: 'top',
      autoHeight: true,
      frame: true,
      items: [{
        layout: 'column',
        items: [{
          columnWidth: .7,
          style: 'margin-left:5px',
          layout: 'form',
          items: [{
            xtype: "gmappanel",
            gmapType: "map",
            id: "areaViewgooglemap",
            zoomLevel: 8,
            minGeoAccuracy: 4,
            scaleControl: true,
            height: winsize.height * 0.9,
            mapConfOpts: [
              "enableScrollWheelZoom",
              "enableDoubleClickZoom",
              "enableDragging",
            ],
            mapControls: ["GSmallMapControl", "GMapTypeControl"],
            setCenter: {
              lat: DEF_LATITUDE,
              lng: DEF_LONGITUDE,
            },
            repaint: function (zoomlevel) {
              var gmappanel = Ext.getCmp("areaViewgooglemap");
              if (zoomlevel) {
                gmappanel.zoomLevel = zoomlevel;
                gmappanel.getMap().setZoom(zoomlevel);
              }
              gmappanel.onMapReady();
            },
          }]
        },
        {
          columnWidth: .3,
          style: 'margin-left:5px',
          layout: 'form',
          items: [{
            xtype: "combo",
            fieldLabel: STATE,
            name: "st_id",
            id: "bast_id",
            hiddenName: "state",
            anchor: "97%",
            store: _state_store,
            valueField: "st_ID",
            tabIndex: 411,
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
          },{
            xtype: "combo",
            fieldLabel: DISTRICT,
            id: "badst_Id",
            name: "dst_Id",
            tabIndex: 412,
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
            listeners: {
              select: function () {
                var value = Ext.getCmp("badst_Id").getValue();
                console.log(
                  "District value after dst",
                  value,
                  this.value
                );
                area_store.baseParams.st_Id = Ext.getCmp("bast_id").getValue();
                area_store.baseParams.dst_ID = this.value;
                area_store.load();
              },
            }
          },{
            xtype: "combo",
            fieldLabel: 'Area',
            id: "searchArea",
            name: "searchArea",
            tabIndex: 413,
            hiddenName: "searchArea",
            anchor: "97%",
            allowBlank: false,
            store: area_store,
            valueField: "id",
            displayField: "areaName",
            forceSelection: true,
            triggerAction: "all",
            typeAhead: true,
            selectOnFocus: true,
            mode: "local",
          },{
            xtype: "button",
            align:"right",
            fieldLabel: "Load in Map",
            tooltip: "Load in Map",
            icon: IMAGE_BASE_PATH + "/default/icons/map.png",
            iconCls: "my-icon1",
            text: "Load in Map",
            tabIndex: 414,
            handler: function () {
              var stateId = Ext.getCmp("bast_id").getValue();
              var districtId = Ext.getCmp("badst_Id").getValue();
              var areaId = Ext.getCmp("searchArea").getValue();
              viewAreainMap(stateId,districtId,areaId);
            },
          },new Ext.Panel({
            layout: "fit",
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: "circleDetailsPanel",
          })]
        }]
      },]
  });
  return form;
  };
  var viewAreainMap = function(stateId,districtId,areaId){
    Ext.Ajax.request({
      waitMsg: 'Please wait...',
      url: modURL,
      params: {
          op: 'loadGeography',
          stateId:stateId,
          districtId:districtId,
          areaId:areaId
      },
      failure: function (response, options) {
      },
      success: function (response, options) {
        var tmp = Ext.decode(response.responseText);
        console.log('loadGeography');
        console.log(tmp.data);
        if (tmp.success == true) {
        Ext.defer(function () {
          Ext.getCmp("areaViewgooglemap").repaint(11);
          Ext.getCmp("areaViewgooglemap").drawCircles(tmp.data,'circleDetailsPanel');
          }, 1200);

        } else {
          Ext.Msg.alert("Error", tmp.msg);
        }
      }
  });
  };
  return {
    Cache: {},
    initParty: function () {
      var panelId = "panelBusinessAssociate";
      var listBusinessAssociate = Ext.getCmp(panelId);
      if (Ext.isEmpty(listBusinessAssociate)) {
        listBusinessAssociate = businessAssociateMainPanel(panelId);
        Application.UI.addTab(listBusinessAssociate);
        listBusinessAssociate.doLayout();
      } else {
        Application.UI.addTab(listBusinessAssociate);
      }
    },
    saveBusinessAssociateDetails: function () {
      var _editBusinessAssociate = Ext.getCmp("panelBusinessAssociateDetails");
      var cust_id = Ext.getCmp("fbarhiddenid").getValue();

      var t = new Date();
      var t_stamp = t.format("YmdHis");

      _editBusinessAssociate.getForm().submit({
        waitMsg: "saving.... ",
        url: modURL,
        params: {
          op: "EditbusinessAssociateDetails",
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
            Ext.getCmp("gridpanelbusinessAssociate").getStore().reload();
            Ext.getCmp("panelBusinessAssociateDetails").getForm().reset();
            Ext.getCmp("panelBusinessAssociateDetails").hide();
            Ext.getCmp("tabpanelBusinessAssociate").show();
            Ext.getCmp("buttonbusinessAssociateSave").hide();
            Ext.getCmp("buttonbusinessAssociateCancel").hide();
          } else {
            Ext.MessageBox.alert("Notification", tmp.msg);
          }
        },
        failure: function (form, action) {
          var tmp = Ext.decode(action.response.responseText);
          Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {});
        },
      });
    },
    ViewMode: function (data) {
      Ext.getCmp("property_grid_id").show();
      Ext.getCmp("panelBusinessAssociateDetails").hide();
      Ext.getCmp("buttonbusinessAssociateCancel").hide();
      Ext.getCmp("buttonbusinessAssociateEdit").show();
      Ext.getCmp("buttonbusinessAssociateSave").hide();
      Ext.getCmp("tabpanelBusinessAssociate").show();
      var propertygridPanel = Ext.getCmp("property_grid_id");
      console.log(data);
      Ext.getCmp("fbarhiddenid").setValue(data.baId);
      propertygridPanel.update(data);
    },
    Config: {},
    Prefix: Ext.id(),
    ViewArea: function () {
      var id = arguments[0];
      Ext.getCmp("buttonAreaEdit").show();
      Ext.getCmp("buttonAreaSave").hide();
      Ext.getCmp("buttonAreaCancel").hide();
      Ext.getCmp("formpanelMasterArea").hide();
      Ext.getCmp("panelSlbeesAreaDetailsView").show();
      Ext.getCmp("panelAreaParent").doLayout();
      Ext.getCmp("panelAreaParent").setTitle("Area Details");
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      var apikey = _SESSION.apikey;
      Ext.get("downloadIframearea").dom.src =
        modURL +
        "&op=AreadetailsView&id=" +
        id +
        "&tstamp=" +
        t_stamp +
        "&apikey=" +
        apikey;
      Ext.getCmp("panelAreaParent").doLayout();
    },
    EditAreaForm: function () {
      Application.BusinessAssociate.AreaTypeAdd = "Edit";
      Ext.getCmp("panelAreaParent").doLayout();
      Ext.getCmp("panelAreaParent").setTitle("Edit Area details");
      Ext.getCmp("formpanelMasterArea").show();
      Ext.getCmp("panelSlbeesAreaDetailsView").hide();
      Ext.getCmp("buttonAreaEdit").hide();
      Ext.getCmp("buttonAreaSave").show();
      Ext.getCmp("buttonAreaCancel").show();
      var t = new Date();
      var t_stamp = t.format("YmdHis");
      if (!Ext.isEmpty(arguments[0])) {
        var areaTypeForm = Ext.getCmp("formpanelMasterArea").getForm();
        areaTypeForm.load({
          params: {
            id: arguments[0],
            apikey: _SESSION.apikey,
            tstamp: t_stamp,
          },
          url: modURL + "&op=area_form_load",
          waitMsg: "Loading...",
          success: function (form, action) {
            var tmp = Ext.decode(action.response.responseText);
            Ext.getCmp("areaState").getStore().load();
                          Ext.getCmp("areaState").setValue(tmp.data.areaState);
                          Ext.getCmp("areaState").setRawValue(tmp.data.st_name);
                          Ext.getCmp("areaState").getStore().baseParams.st_Id =
                            tmp.data.areaState;
                          Ext.getCmp("areaDistrict").getStore().load();
                          Ext.getCmp("areaDistrict").setValue(tmp.data.areaDistrict);
                          Ext.getCmp("areaDistrict").setRawValue(tmp.data.dst_Name);
            Application.BusinessAssociate.Cache.areamap_lat = tmp.data.areaLatitude;
            Application.BusinessAssociate.Cache.areamap_long = tmp.data.areaLongitude;
            mapLoad();
            areaCircle(tmp.data.areaSpan);
          },
          failure: function (form, action) {
            Ext.Msg.alert("Error.", "This error");
          },
        });
      }
    },
    initArea: function () {
      var areaPanelID = "panelMasterMainArea";
      var masterPanelForArea = Ext.getCmp(areaPanelID);
      if (Ext.isEmpty(masterPanelForArea)) {
        masterPanelForArea = masterPanelforArea(areaPanelID);
        Application.UI.addTab(masterPanelForArea);
        masterPanelForArea.doLayout();
      } else {
        Application.UI.addTab(masterPanelForArea);
      }
    },
    initBusinessParty: function () {
      var panelId = "panelBusinessPartnerPanel";
      var listBusinessPartner = Ext.getCmp(panelId);
      if (Ext.isEmpty(listBusinessPartner)) {
        listBusinessPartner = BusinessPartnerMainPanel(panelId);
        Application.UI.addTab(listBusinessPartner);
        listBusinessPartner.doLayout();
      } else {
        Application.UI.addTab(listBusinessPartner);
      }
    },
    saveBusinessPartnerDetails: function () {
      var _editBusinessPartner = Ext.getCmp("panelBusinessPartnerDetails");
      var cust_id = Ext.getCmp("partnerhiddenid").getValue();

      var t = new Date();
      var t_stamp = t.format("YmdHis");

      _editBusinessPartner.getForm().submit({
        waitMsg: "saving.... ",
        url: modURL,
        params: {
          op: "EditBusinessPartnerDetails",
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
            Ext.getCmp("gridpanelBusinessPartner").getStore().reload();
            Ext.getCmp("panelBusinessPartnerDetails").getForm().reset();
            Ext.getCmp("panelBusinessPartnerDetails").hide();
            Ext.getCmp("tabpanelBusinessPartner").show();
            Ext.getCmp("buttonBusinessPartnerSave").hide();
            Ext.getCmp("buttonBusinessPartnerCancel").hide();
          } else {
            Ext.MessageBox.alert("Notification", tmp.msg);
          }
        },
        failure: function (form, action) {
          var tmp = Ext.decode(action.response.responseText);
          Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {});
        },
      });
    },
    ViewModeBP: function (data) {
      Ext.getCmp("property_grid_partner").show();
      Ext.getCmp("panelBusinessPartnerDetails").hide();
      Ext.getCmp("buttonBusinessPartnerCancel").hide();
      Ext.getCmp("buttonBusinessPartnerEdit").show();
      Ext.getCmp("buttonBusinessPartnerSave").hide();
      Ext.getCmp("tabpanelBusinessPartner").show();
      var propertygridPanel = Ext.getCmp("property_grid_partner");
      console.log(data);
      Ext.getCmp("partnerhiddenid").setValue(data.bpId);
      propertygridPanel.update(data);
    },
    mapBusinessAssociate: function (areaId) {
      var _addNewWindow = new Ext.Window({
        title: "Assign Associate",
        layout: "fit",
        width: winsize.width * 0.6,
        height: winsize.height * 0.8,
        resizable: false,
        draggable: true,
        closable: true,
        modal: true,
        bodyStyle: { "background-color": "white" },
        items: [mapBusinessAssociateGrid()],
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
              Ext.getCmp("assignBAbtn").disable();
              var executive_id = Ext.getCmp("gridpanelAssignBa")
                .getSelectionModel()
                .getSelections()[0].data.id;
              if (executive_id > 0) {
                Ext.Ajax.request({
                  url: modURL + "&op=assignBa",
                  method: "POST",
                  params: {
                    executive_id: executive_id,
                    areaId: areaId,
                  },
                  success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success == true) {
                      Application.example.msg("Success", tmp.msg);
                      _addNewWindow.close();
                      Ext.getCmp("gridPanelAreaMain").getStore().load();
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
                  "Select associate and proceed."
                );
              }
            },
          },
        ],
      });
      _addNewWindow.doLayout();
      _addNewWindow.show();
      _addNewWindow.center();
    },
    viewRelationshipOfficers: function (customerId) {
      var _polledItemsWindow = new Ext.Window({
        title: "Relationship Officers",
        iconCls: "dispatch",
        layout: "border",
        height: winsize.height * 0.8,
        width: winsize.width * 0.8,
        resizable: false,
        draggable: true,
        closable: true,
        modal: true,
        bodyStyle: { "background-color": "white" },
        items: [
          _relationshipOfficerGrid(customerId),
          new Ext.Panel({
            title: "View Details",
            frame: false,
            border: true,
            region: "east",
            width: winsize.width * 0.4,
            autoScroll: true,
            height: winsize.height * 0.55,
            id: "relationshipOfficerPanel",
            items: [relationshipOfficerDetailsView()],
            buttonAlign: "right",
          }),
        ],
        fbar: [],
      });
      _polledItemsWindow.doLayout();
      _polledItemsWindow.show();
      _polledItemsWindow.center();
    },
    roViewMode: function () {
      var id = arguments[0];
      Ext.getCmp("roMasterDetailsViewPanel").show();
      Ext.getCmp("relationshipOfficerPanel").setTitle("View Details");
      Ext.getCmp("relationshipOfficerPanel").doLayout();
      Ext.Ajax.request({
        url: modURL + "&op=roDetailsView",
        method: "POST",
        params: { id: id },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp("roMasterDetailsViewPanel");
            visualsDescPanel.update(tmp);
          }
          Ext.getCmp("relationshipOfficerPanel").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("relationshipOfficerPanel").doLayout();
    },showMap: function () {
      var _addressMapWindow = new Ext.Window({
          layout: 'fit',
          height: winsize.height * 0.9,
          width: winsize.width * 0.8,
          resizable: false,
          draggable: true,
          closable: true,
          modal: true,
          bodyStyle: {"background-color": "white"},
          items: [areaSearchForm()],
          fbar: []

      });
      _addressMapWindow.doLayout();
      _addressMapWindow.show();
      _addressMapWindow.center();

  }
  };
})();
