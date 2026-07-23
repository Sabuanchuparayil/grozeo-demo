Application.SupportTicket = (function () {
  var RECS_PER_PAGE = 23;
  var modURL = "?module=support_ticket";
  var winsize = Ext.getBody().getViewSize();
  var onGridResize = function (cmp) {
    RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
  };
  var supportTypeComboStore = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=getSupportType",
      method: "post",
      fields: ["typeId", "typeName"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
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
  var supportUnitComboStorePrimary = function () {
    var store = new Ext.data.JsonStore({
      autoLoad: false,
      url: modURL + "&op=getSupportUnit",
      method: "post",
      fields: ["suId", "suName"],
      //totalProperty: 'totalCount',
      root: "data",
    });
    return store;
  };
  var masterPanelforSupportTicket = function (id) {
    var suComboStore = supportUnitComboStorePrimary();
    var _mpanelforSupportTicket = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Support Tickets",
      id: id,
      iconCls: "my-icon448",
      items: [
        SupportTicketMainGrid(),
        new Ext.Panel({
          title: "Support Ticket Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterSupportTicketParent",
          height: winsize.height * 0.6,
          items: [SupportTicketMasterDetailsView(), supportTicketLogs()],
          buttonAlign: "right",
          fbar: [
            {
              hidden: true,
              emptyText: "Choose Support Unit",
              xtype: "combo",
              store: suComboStore,
              mode: "local",
              id: "assignSuUnitId",
              fieldLabel: "Support Unit",
              hiddenName: "assignSuUnitId",
              displayField: "suName",
              valueField: "suId",
              typeAhead: true,
              tabIndex: 503,
              forceSelection: true,
              editable: true,
              minChars: 2,
              anchor: "98%",
              //selectOnFocus: true,
              triggerAction: "all",
              lazyRender: true,
              listeners: {},
            },
            {
              text: "Assign",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/assign.png",
              id: "ticketAssignBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewSupportTicketsdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp(
                    "gridpanelMasterDataviewSupportTicketsdata"
                  )
                    .getSelectionModel()
                    .getSelections()[0].data.ticketId;
                  var assignSuUnitId = Ext.getCmp("assignSuUnitId").getValue();
                  if (assignSuUnitId > 0) {
                    Application.SupportTicket.AssignSupportTickets(
                      ID,
                      assignSuUnitId
                    );
                  } else {
                    Ext.MessageBox.alert(
                      "Notification",
                      "Choose Support Unit and proceed."
                    );
                  }
                }
              },
            },
          ],
        }),
      ],
    });
    return _mpanelforSupportTicket;
  };
  var showData = function (supportType, contactNo) {
    Ext.Ajax.request({
      url: modURL + "&op=checkAvailablity",
      method: "POST",
      params: { supportType: supportType, contactNo: contactNo },
      success: function (res) {
        var tmp = Ext.decode(res.responseText);
        if (tmp.success === true) {
          Ext.getCmp("ticketContactName").show();
          Ext.getCmp("ticketContactEmail").show();
          if (tmp.supportTypeName != "Public") {
            Ext.getCmp("ticketContactName").setReadOnly(true);
            Ext.getCmp("ticketContactName").setReadOnly(true);
            Ext.getCmp("ticketContactName").setValue(tmp.cname);
            Ext.getCmp("ticketContactEmail").setValue(tmp.email);
          }
        } else {
          var visualsDescPanel = Ext.getCmp("CustDetailsViewPanel");
          visualsDescPanel.update(tmp);
        }
        Ext.getCmp("CustViePanel").doLayout();
      },
      failure: function () {
        Ext.MessageBox.alert("Error", "Error occured while sending data");
      },
    });
  };
  var SupportTicketForm = function () {
    var suComboStore = supportUnitComboStorePrimary();
    var supTypeComboStore = supportTypeComboStore();
    var _supportticketsFormPanel = new Ext.form.FormPanel({
      id: "formpanelMasterSupportTickets",
      frame: true,
      width: winsize.width * 0.5,
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
          columnWidth: 0.40,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "combo",
              store: supTypeComboStore,
              mode: "local",
              id: "ticketSupTypeId",
              fieldLabel: "Support Beneficiary",
              hiddenName: "n[ticketSupTypeId]",
              displayField: "typeName",
              valueField: "typeId",
              typeAhead: true,
              tabIndex: 503,
              forceSelection: true,
              editable: true,
              minChars: 2,
              anchor: "95%",
              //selectOnFocus: true,
              triggerAction: "all",
              lazyRender: true,
              listeners: {
                select: function () {
                  var value = Ext.getCmp("ticketSupTypeId").getValue();
                  suComboStore.baseParams.suTypeId = this.value;
                  suComboStore.load();
                },
              },
            },
          ],
        },
        {
          columnWidth: 0.40,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "textfield",
              fieldLabel: "Mobile",
              id: "ticketContactNo",
              name: "n[ticketContactNo]",
              anchor: "95%",
              tabIndex: 501,
              allowBlank: false,
              maxLength: 15,
            },
          ],
        },
        {
          columnWidth: 0.20,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "button",
              text: "Check Availability",
              iconCls: "show",
              id:'stcheckAvail',
              style: "padding-top: 12px;",
              handler: function () {
                var ticketContactNo = Ext.getCmp("ticketContactNo").getValue();
                var supportType = Ext.getCmp("ticketSupTypeId").getValue();
                if (!Ext.isEmpty(ticketContactNo))
                  showData(supportType, ticketContactNo);
              },
            },
          ],
        },
        {
          columnWidth: 0.5,
          layout: "form",
          border: false,
          items: [
            {
              hideLabel: true,
              xtype: "textfield",
              border: false,
              fieldLabel: " ",
              emptyText: "Name",
              width: 150,
              hidden: true,
              allowBlank: false,
              id: "ticketContactName",
              name: "n[ticketContactName]",
              style: { "font-weight": "bold" },
              anchor: "95%",
            },
          ],
        },
        {
          columnWidth: 0.5,
          layout: "form",
          border: false,
          items: [
            {
              hideLabel: true,
              xtype: "textfield",
              fieldLabel: " ",
              emptyText: "Email",
              border: false,
              width: 150,
              hidden: true,
              id: "ticketContactEmail",
              name: "n[ticketContactEmail]",
              style: { "font-weight": "bold" },
              anchor: "95%",
            },
          ],
        },
        {
          columnWidth: 1,
          layout: "form",
          border: false,
          items: [
            {
              xtype: "hidden",
              id: "s3_filename",
              name: "s3_filename",
            },
            {
              xtype: "hidden",
              id: "s3_albumBucketName",
              name: "s3_albumBucketName",
            },
            {
              xtype: "hidden",
              id: "s3_accessKey",
              name: "s3_accessKey",
            },
            {
              xtype: "hidden",
              id: "s3_secretKey",
              name: "s3_secretKey",
            },
            {
              xtype: "hidden",
              id: "s3_bucketRegion",
              name: "s3_bucketRegion",
            },{
              xtype: "hidden",
              id: "s3_bucketFolder",
              name: "s3_bucketFolder",
            },
            {
              xtype: "hidden",
              id: "s3_oncompleteurl",
              name: "s3_oncompleteurl",
            },
            {
              xtype: "hidden",
              id: "s3_img_path_db",
              name: "s3_img_path_db",
            },
            {
              xtype: "hidden",
              id: "s3filepath",
              name: "s3filepath",
            },
            {
              xtype: "spacer",
              height: 10,
            },
            {
              xtype: "textfield",
              fieldLabel: "Support Request",
              id: "ticketTitle",
              name: "n[ticketTitle]",
              anchor: "98%",
              tabIndex: 501,
              allowBlank: false,
              maxLength: 100,
            },
            {
              xtype: "textarea",
              fieldLabel: "Description",
              id: "ticketDescription",
              name: "n[ticketDescription]",
              anchor: "98%",
              height: 270,
              tabIndex: 502,
              allowBlank: false,
              maxLength: 900,
            },
            {
              xtype: "combo",
              store: suComboStore,
              mode: "local",
              id: "ticketSuId",
              fieldLabel: "Support Unit",
              hiddenName: "n[ticketSuId]",
              displayField: "suName",
              valueField: "suId",
              typeAhead: true,
              tabIndex: 503,
              forceSelection: true,
              editable: true,
              minChars: 2,
              anchor: "98%",
              //selectOnFocus: true,
              triggerAction: "all",
              lazyRender: true,
              listeners: {
                select: function () {},
              },
            },
            {
              xtype: "fileuploadfield",
              style: "margin-bottom:5px;",
              id: "fileuploadfieldAttachFileSupport",
              border: false,
              tabIndex: 504,
              anchor: "95%",
              name: "n[fileuploadfieldAttachFileSupport]",
              allowBlank: true,
              buttonOnly: true,
              buttonCfg: {
                text: "Upload File",
                border: false,
                width: 100,
                buttonAlign: "left",
              },
              validator: function (v) {
                if (v != "") {
                  v = v.toLowerCase();
                  var exp = /^.*\.(png|jpg|gif|pdf|docx)$/i;
                  if (!exp.test(v)) {
                    return "Upload a valid image file of format JPG.";
                  }

                  var associated_file = Ext.getCmp(
                    "fileuploadfieldAttachFileSupport"
                  ).getValue();
                  if (associated_file == "") {
                    Ext.Msg.alert(
                      "Notification",
                      "Please choose a scanned file to upload"
                    );
                    return;
                  }
                  addFile();
                  return true;
                }
              },
            },
            {
              xtype: "displayfield",
              id: "supporting",
              cls: "fpcls",
              value: "File uploaded successfully",
              hidden: true,
            },
            {
              xtype: "textfield",
              id: "ticketId",
              name: "n[ticketId]",
              hidden: true,
            },
          ],
        },
      ],
      listeners: {
        load: function () {},
      },
    });
    return _supportticketsFormPanel;
  };
  var addFile = function () {
    var albumBucketName = Ext.getCmp("s3_albumBucketName").getValue();
    var bucketRegion = Ext.getCmp("s3_bucketRegion").getValue();
    var filepath = Ext.getCmp("s3_bucketFolder").getValue();
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
      "fileuploadfieldAttachFileSupport-file"
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
        Key: filepath + fileName,
        Body: file,
        ACL: "public-read",
      },
      function (err, data) {
        if (err) {
          var img_src = Ext.BLANK_IMAGE_URL;
          return Ext.Msg.alert(
            "Notification",
            "There was an error uploading : " + err.message
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
                Ext.MessageBox.hide();
                Application.example.msg(
                  "Notification",
                  "File uploaded successfully."
                );
                Ext.getCmp("supporting").show();
                Ext.getCmp("supporting").disable();
              },
            },
          });
          Ext.getCmp("s3filepath").setValue(data.Location);
        }
      }
    );
  };
  var uuidv4 = function () {
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
      /[xy]/g,
      function (c) {
        var r = (Math.random() * 16) | 0,
          v = c == "x" ? r : (r & 0x3) | 0x8;
        return v.toString(16);
      }
    );
  };
  var fileS3BucketView = function () {
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    var uuid = uuidv4(); // for xxxx to field

    Ext.Ajax.request({
      url: modURL + "&op=get_file_s3_details",
      method: "POST",
      params: {
        rid: uuid,
        apikey: _SESSION.apikey,
        tstamp: t_stamp,
      },
      success: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        if (tmp.success !== undefined && tmp.success === true) {
          Ext.getCmp("s3_accessKey").setValue(tmp.data.accessKey);
          Ext.getCmp("s3_albumBucketName").setValue(tmp.data.albumBucketName);
          Ext.getCmp("s3_secretKey").setValue(tmp.data.secretKey);
          Ext.getCmp("s3_bucketRegion").setValue(tmp.data.bucketRegion);
          Ext.getCmp("s3_bucketFolder").setValue(tmp.data.folder);
        }
      },
      failure: function (response) {
        var tmp = Ext.util.JSON.decode(response.responseText);
        Ext.MessageBox.alert("Error", "Issue in saving");
      },
    });
  };
  var SupportTicketsMasterStore = function () {
    var _supportticketsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listSupportTickets",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "ticketId",
          "ticketStatus",
          "ticketSuName",
          "ticketSupTypeId",
          "status",
          "ticketNumber",
          "createdOn",
          "createdBy",
          "ticketOwner",
          "ticketTitle",
          "ticketType",
          "ticketSuId","ticketStageName",
          "ticketSupTypeName","ticketAssignedTo","ticketAssignedToName","createdDate","createdTime"
        ]
      ),
      sortInfo: {
        field: "ticketId",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewSupportTicketsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewSupportTicketsdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _supportticketsMasterStore;
  };
  var SupportTicketMainGrid = function () {
    var _supportticketsStore = SupportTicketsMasterStore();
    var _supportticketsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "ticketNumber",
        },
        {
          type: "string",
          dataIndex: "ticketSuName",
        },{
          type: "string",
          dataIndex: "ticketOwner",
        },{
          type: "string",
          dataIndex: "ticketSupTypeName",
        },{
          type: "string",
          dataIndex: "ticketSuName",
        },{
          type: "string",
          dataIndex: "ticketAssignedToName",
        },{
          type: "string",
          dataIndex: "ticketStageName",
        },{
          type: "date",
          dataIndex: "createdDate",
        },        
        {
          type: "list",
          options: ["Assigned", "Unassigned"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _supportticketsGridFilter.remote = true;
    _supportticketsGridFilter.autoReload = true;
    var _supportticketsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _supportticketsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewSupportTicketsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _supportticketsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Created Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Created Date",
          hideable: false,
        },{
          header: "Created Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Created Time",
          hideable: false,
        },{
          header: "Date",
          dataIndex: "createdOn",
          hidden: true,
          tooltip: "Date",
          hideable: false,
        },
        {
          header: "Ticket Owner",
          dataIndex: "ticketOwner",
          sortable: true,
          tooltip: "Ticket Owner",
          hideable: false,
        },
        {
          header: "Ticket Type",
          dataIndex: "ticketSupTypeName",
          sortable: true,
          tooltip: "Ticket Type",
          hideable: false,
        },
        {
          header: "Ticket Number",
          dataIndex: "ticketNumber",
          sortable: true,
          tooltip: "Ticket Number",
          hideable: false,
        },{
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },
        {
          header: "Support Unit",
          dataIndex: "ticketSuName",
          sortable: true,
          tooltip: "Support Unit",
          hideable: false,
        },
        {
          header: "Resource",
          dataIndex: "ticketAssignedToName",
          sortable: true,
          tooltip: "Resource",
        },{
          header: "Stage",
          dataIndex: "ticketStageName",
          sortable: true,
          tooltip: "Stage",
        }
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _supportticketsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedsupporttickets,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("ticketId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportTicket.Cache.ticketId = ID;
            // Ext.getCmp("formpanelMasterSupportTickets").hide();
            Application.SupportTicket.ViewSupportTickets(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _supportticketsStore.load();
        },
      },
      tbar: [
        {
          text: "Create Support Ticket",
          tooltip: "Create Support Ticket ",
          icon: "./resources/images/submenuicons/add.png",
          iconCls: "my-icon1",
          handler: function () {
            supportRequestWindow();
          },
        },
      ],
    });
    return _supportticketsmaingridPanel;
  };
  var gridSelectionChangedsupporttickets = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewSupportTicketsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewSupportTicketsdata")
        .getSelectionModel()
        .getSelections()[0].data.ticketId;
      var ticketStatus = Ext.getCmp("gridpanelMasterDataviewSupportTicketsdata")
        .getSelectionModel()
        .getSelections()[0].data.ticketStatus;
      var ticketSupTypeId = Ext.getCmp(
        "gridpanelMasterDataviewSupportTicketsdata"
      )
        .getSelectionModel()
        .getSelections()[0].data.ticketSupTypeId;
      if (ticketStatus == 2) {
        Ext.getCmp("ticketAssignBtn").show();
        Ext.getCmp("assignSuUnitId").show();
        Ext.getCmp("assignSuUnitId").getStore().baseParams.suTypeId =
          ticketSupTypeId;
        Ext.getCmp("assignSuUnitId").getStore().load();
      } else {
        Ext.getCmp("ticketAssignBtn").hide();
        Ext.getCmp("assignSuUnitId").hide();
      }
      Application.SupportTicket.ViewSupportTickets(ID);
    }
  };
  var SupportTicketMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      region: "south",
      border: false,
      hideBorders: true,
      height: winsize.height * 0.35,
      autoScroll: true,
      id: "panelMasterSupportTicketsDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Ticket Number </th><td>  {ticketNumber} </td></tr>',
        '<tr><th width="40%">Title </th><td>  {ticketTitle} </td></tr>',
        '<tr><th width="40%">Description </th><td>  {ticketDescription} </td></tr>',
        '<tr><th width="40%">Support Unit </th><td>  {ticketSuName} </td></tr>',
        '<tr><th width="40%">Contact No: </th><td>  {ticketContactNo} </td></tr>',
        '<tr><th width="40%">Contact Name </th><td>  {ticketContactName} </td></tr>',
        '<tr><th width="40%">Contact Email </th><td>  {ticketContactEmail} </td></tr>',
        '<tr><th width="40%">Created On </th><td>  {createdOn} </td></tr>',
        '<tr><th width="40%">Created From </th><td>  {createdFrom} </td></tr>',
        '<tr><th width="40%">Ticket Owner </th><td>  {ticketOwner} </td></tr>',
        '<tr><th width="40%">File</th><td>',
        "<tpl if=\"files != ' '\"><a href = '{filepath}' target= '_blank'>{files}</a></tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var saveSupportTickets = function () {
    var _st_save = Ext.getCmp("formpanelMasterSupportTickets");
    var t = new Date();
    var t_stamp = t.format("YmdHis");
    if (_st_save.getForm().isValid()) {
      _st_save.getForm().submit({
        waitTitle: "Please Wait",
        waitMsg: "Saving",
        url: modURL + "&op=saveSupportTickets",
        params: {
          apikey: _SESSION.apikey,
          tstamp: t_stamp,
        },
        success: function (form, action) {
          var tmp = Ext.decode(action.response.responseText);
          if (tmp.success == true) {
            Application.example.msg("Notification", tmp.msg);
            Ext.getCmp("supportRequestWindow").close();
            Ext.getCmp("gridpanelMasterDataviewSupportTicketsdata")
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
  var supportRequestWindow = function (_val) {
    fileS3BucketView();
    var reswindow = new Ext.Window({
      title: "Suppot Request",
      width: winsize.width * 0.5,
      autoHeight: true,
      plain: true,
      constrainHeader: true,
      modal: true,
      frame: true,
      border: false,
      iconCls: "",
      resizable: false,
      id: "supportRequestWindow",
      items: [SupportTicketForm()],
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
            saveSupportTickets();
          },
        },
      ],
    });
    reswindow.doLayout();
    reswindow.show();
    reswindow.center();
  };
  var availableSupportUnitGrid = function (userId) {
    var availableSupportUnit_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "suName",
        },
      ],
    });
    availableSupportUnit_filter.remote = true;
    availableSupportUnit_filter.autoReload = true;
    var _availSugridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      title: "Unassigned Support Unit",
      frame: false,
      border: false,
      loadMask: true,
      store: availableSupportUnitStore(userId),
      //iconCls: 'money',
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforAvailableSupportUnits",
      sm: check_box,
      plugins: [availableSupportUnit_filter],
      columns: [
        check_box,
        new Ext.grid.RowNumberer(),
        {
          header: "Support Units",
          dataIndex: "suName",
          sortable: true,
          tooltip: "Support Units",
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
            var selectitem = Ext.getCmp("gridpanelforAvailableSupportUnits")
              .getSelectionModel()
              .getSelections();
            var selectedcount = selectitem.length;
            var brandarr = [];
            for (var i = 0; i < selectedcount; i++) {
              brandarr.push(selectitem[i].data.suId);
            }
            if (selectedcount != 0) {
              Application.SupportTicket.mapSupportUnitToUser(brandarr, userId);
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
  var mappedUserSupportUnits = function (userId) {
    var mappedUserSupportUnit_filter = new Ext.ux.grid.GridFilters({
      remote: true,
      filters: [
        {
          type: "string",
          dataIndex: "suName",
        },
      ],
    });
    mappedUserSupportUnit_filter.remote = true;
    mappedUserSupportUnit_filter.autoReload = true;
    var _mapedSUgridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: userMappedSupportUnitStore(userId),
      //iconCls: 'money',
      height: 500,
      autoScroll: true,
      bodyStyle: { "background-color": "white" },
      id: "gridpanelforUserMappedSupportUnits",
      plugins: [mappedUserSupportUnit_filter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "SupportUnits",
          dataIndex: "suName",
          sortable: true,
          tooltip: "SupportUnits",
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
              tooltip: "Cancel SupportUnit",
              handler: function (grid, rowIndex, colIndex, itm, evn) {
                var record = grid.getStore().getAt(rowIndex);

                Ext.Ajax.request({
                  waitMsg: "Processing",
                  url: modURL,
                  params: {
                    op: "removeSupportUnitFromUser",
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
                      Ext.getCmp("gridpanelforUserMappedSupportUnits")
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
  var check_box = new Ext.grid.CheckboxSelectionModel({
    multiSelect: true,
    checkOnly: true,
    showHeaderCheckbox: false,
  });
  var availableSupportUnitStore = function (userId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAvailableSupportUnits",
        method: "post",
      }),
      fields: ["suId", "suName", "pdtCount"],
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
  var userMappedSupportUnitStore = function (userId) {
    var _Store = new Ext.data.JsonStore({
      method: "post",
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listUserMappedSupportUnits",
        method: "post",
      }),
      fields: ["suId", "suName", "pdtCount"],
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
  var masterPanelforAssignedTicket = function (id) {
    var _mpanelforSupportTicket = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Unit Assigned Tickets",
      id: id,
      iconCls: "my-icon448",
      items: [
        AssignedTicketMainGrid(),
        new Ext.Panel({
          title: "Support Ticket Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterAssignedTicketParent",
          height: winsize.height * 0.6,
          items: [AssignedTicketMasterDetailsView(), assignedTicketLogs()],
          buttonAlign: "right",
          fbar: [
            {
              text: "Accept",
              tabIndex: 5,
              cls: "left-right-buttons",
              icon: IMAGE_BASE_PATH + "/default/icons/assign.png",
              id: "ticketAcceptBtn",
              hidden: true,
              handler: function () {
                if (
                  !Ext.isEmpty(
                    Ext.getCmp("gridpanelMasterDataviewAssignedTicketsdata")
                      .getSelectionModel()
                      .getSelections()
                  )
                ) {
                  var ID = Ext.getCmp(
                    "gridpanelMasterDataviewAssignedTicketsdata"
                  )
                    .getSelectionModel()
                    .getSelections()[0].data.ticketId;

                  Application.SupportTicket.AcceptSupportTickets(ID);
                }
              },
            },
          ],
        }),
      ],
    });
    return _mpanelforSupportTicket;
  };
  var AssignedTicketsMasterStore = function () {
    var _assignedticketsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listAssignedTickets",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "ticketId",
          "isAssigned",
          "ticketStatus",
          "ticketSuName",
          "ticketSupTypeId",
          "status",
          "ticketNumber",
          "createdOn",
          "createdBy",
          "ticketOwner",
          "ticketTitle",
          "ticketType",
          "ticketSuId","ticketStageName",
          "ticketSupTypeName","ticketAssignedTo","ticketAssignedToName","createdDate","createdTime"
        ]
      ),
      sortInfo: {
        field: "ticketId",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewAssignedTicketsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewAssignedTicketsdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _assignedticketsMasterStore;
  };
  var AssignedTicketMainGrid = function () {
    var _assignedticketsStore = AssignedTicketsMasterStore();
    var _assignedticketsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "ticketNumber",
        },
        {
          type: "string",
          dataIndex: "ticketSuName",
        },{
          type: "string",
          dataIndex: "ticketSupTypeName",
        },{
          type: "string",
          dataIndex: "ticketSuName",
        },{
          type: "string",
          dataIndex: "ticketAssignedToName",
        },{
          type: "string",
          dataIndex: "ticketStageName",
        },{
          type: "date",
          dataIndex: "createdDate",
        },
        {
          type: "list",
          options: ["Assigned", "Unassigned"],
          phpMode: true,
          dataIndex: "status",
        },
      ],
    });
    _assignedticketsGridFilter.remote = true;
    _assignedticketsGridFilter.autoReload = true;
    var _supportticketsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _assignedticketsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewAssignedTicketsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _assignedticketsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Created Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Created Date",
          hideable: false,
        },{
          header: "Created Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Created Time",
          hideable: false,
        },
        {
          header: "Date",
          dataIndex: "createdOn",
          hidden: true,
          tooltip: "Date",
          hideable: false,
        },
        {
          header: "Ticket Owner",
          dataIndex: "ticketOwner",
          sortable: true,
          tooltip: "Ticket Owner",
          hideable: false,
        },
        {
          header: "Ticket Type",
          dataIndex: "ticketSupTypeName",
          sortable: true,
          tooltip: "Ticket Type",
          hideable: false,
        },
        {
          header: "Ticket Number",
          dataIndex: "ticketNumber",
          sortable: true,
          tooltip: "Ticket Number",
          hideable: false,
        },        
        {
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },{
          header: "Support Unit",
          dataIndex: "ticketSuName",
          sortable: true,
          tooltip: "Support Unit",
          hideable: false,
        },{
          header: "Resource",
          dataIndex: "ticketAssignedToName",
          sortable: true,
          tooltip: "Resource",
        }
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _assignedticketsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedAssignedtickets,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("ticketId");
          var isAssigned = record.get("isAssigned");
          if (!Ext.isEmpty(ID)) {
            Application.SupportTicket.Cache.assignticketId = ID;
            Application.SupportTicket.ViewAssignedTickets(ID);
            if (
              Ext.getCmp(
                "gridpanelMasterDataviewAssignedTicketsdata"
              ).getSelectionModel().last == 0 &&
              isAssigned == 0
            ) {
              Ext.getCmp("ticketAcceptBtn").show();
            } else {
              Ext.getCmp("ticketAcceptBtn").hide();
            }
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _assignedticketsStore.load();
        },
      },
      tbar: [],
    });
    return _supportticketsmaingridPanel;
  };
  var gridSelectionChangedAssignedtickets = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewAssignedTicketsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewAssignedTicketsdata")
        .getSelectionModel()
        .getSelections()[0].data.ticketId;
      var isAssigned = Ext.getCmp("gridpanelMasterDataviewAssignedTicketsdata")
        .getSelectionModel()
        .getSelections()[0].data.isAssigned;
      console.log("isAssigned");
      console.log(isAssigned);
      console.log(
        Ext.getCmp(
          "gridpanelMasterDataviewAssignedTicketsdata"
        ).getSelectionModel().last
      );
      if (
        Ext.getCmp(
          "gridpanelMasterDataviewAssignedTicketsdata"
        ).getSelectionModel().last == 0 &&
        isAssigned == 0
      ) {
        Ext.getCmp("ticketAcceptBtn").show();
      } else {
        Ext.getCmp("ticketAcceptBtn").hide();
      }
      Application.SupportTicket.ViewAssignedTickets(ID);
    }
  };
  var AssignedTicketMasterDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      region: "south",
      border: false,
      hideBorders: true,
      height: winsize.height * 0.35,
      autoScroll: true,
      id: "panelMasterAssignedTicketsDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Ticket Number </th><td>  {ticketNumber} </td></tr>',
        '<tr><th width="40%">Title </th><td>  {ticketTitle} </td></tr>',
        '<tr><th width="40%">Description </th><td>  {ticketDescription} </td></tr>',
        '<tr><th width="40%">Support Unit </th><td>  {ticketSuName} </td></tr>',
        '<tr><th width="40%">Contact No: </th><td>  {ticketContactNo} </td></tr>',
        '<tr><th width="40%">Contact Name </th><td>  {ticketContactName} </td></tr>',
        '<tr><th width="40%">Contact Email </th><td>  {ticketContactEmail} </td></tr>',
        '<tr><th width="40%">Created On </th><td>  {createdOn} </td></tr>',
        '<tr><th width="40%">Created From </th><td>  {createdFrom} </td></tr>',
        '<tr><th width="40%">Ticket Owner </th><td>  {ticketOwner} </td></tr>',
        '<tr><th width="40%">File</th><td>',
        "<tpl if=\"files != ' '\"><a href = '{filepath}' target= '_blank'>{files}</a></tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var masterPanelforMySupportTickets = function (id) {
    var _mpanelforMySupportTicket = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "My Tickets",
      id: id,
      iconCls: "my-icon448",
      items: [
        mySupportTicketMainGrid(),
        new Ext.Panel({
          title: "Support Ticket Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterMyAssignedTicketParent",
          height: winsize.height * 0.6,
          items: [mySupportTicketDetailsView(), mySupportTicketLogs()],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return _mpanelforMySupportTicket;
  };
  var mySupportTicketsMasterStore = function () {
    var _assignedticketsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listMyAssignedTickets",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "ticketId",
          "isAssigned",
          "ticketStatus",
          "ticketSuName",
          "ticketSupTypeId",
          "status",
          "tiketStage","ticketStageName",
          "ticketNumber",
          "createdOn",
          "createdBy",
          "ticketOwner",
          "ticketTitle",
          "ticketType",
          "ticketSuId",
          "ticketSupTypeName","ticketAssignedTo","ticketAssignedToName","createdDate","createdTime"
        ]
      ),
      sortInfo: {
        field: "ticketId",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewMySupportTicketsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewMySupportTicketsdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _assignedticketsMasterStore;
  };
  var mySupportTicketMainGrid = function () {
    var _mySupportTicketsStore = mySupportTicketsMasterStore();
    var _myTicketsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "ticketNumber",
        },
        {
          type: "string",
          dataIndex: "ticketSuName",
        },{
          type: "string",
          dataIndex: "ticketSupTypeName",
        },{
          type: "string",
          dataIndex: "ticketSuName",
        },{
          type: "string",
          dataIndex: "ticketAssignedToName",
        },{
          type: "string",
          dataIndex: "ticketStageName",
        },{
          type: "date",
          dataIndex: "createdDate",
        },
        {
          type: "list",
          options: ["Assigned", "Unassigned"],
          phpMode: true,
          dataIndex: "status",
        }
      ],
    });
    _myTicketsGridFilter.remote = true;
    _myTicketsGridFilter.autoReload = true;
    var _supportticketsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _mySupportTicketsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewMySupportTicketsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _myTicketsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Created Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Created Date",
          hideable: false,
        },{
          header: "Created Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Created Time",
          hideable: false,
        },
        {
          header: "Date",
          dataIndex: "createdOn",
          sortable: true,
          tooltip: "Date",
          hideable: false,
        },
        {
          header: "Ticket Owner",
          dataIndex: "ticketOwner",
          sortable: true,
          tooltip: "Ticket Owner",
          hideable: false,
        },
        {
          header: "Ticket Type",
          dataIndex: "ticketSupTypeName",
          sortable: true,
          tooltip: "Ticket Type",
          hideable: false,
        },
        {
          header: "Ticket Number",
          dataIndex: "ticketNumber",
          sortable: true,
          tooltip: "Ticket Number",
          hideable: false,
        },
        {
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },
        {
          header: "Support Unit",
          dataIndex: "ticketSuName",
          sortable: true,
          tooltip: "Support Unit",
          hideable: false,
        },        
        {
          header: "Stage",
          dataIndex: "ticketStageName",
          sortable: true,
          tooltip: "Stage",
        },{
          header: "Resource",
          dataIndex: "ticketAssignedToName",
          sortable: true,
          tooltip: "Resource",
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
              var ticketId = Ext.getCmp(
                "gridpanelMasterDataviewMySupportTicketsdata"
              )
                .getSelectionModel()
                .getSelections()[0].data.ticketId;
              myticketActionMenu(e, ticketId);
              //action
            },
          },
        },
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _mySupportTicketsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedMyAssignedtickets,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("ticketId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportTicket.Cache.myticketId = ID;
            Application.SupportTicket.ViewMySupportTickets(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _mySupportTicketsStore.load();
        },
      },
      tbar: [],
    });
    return _supportticketsmaingridPanel;
  };
  var myticketActionMenu = function (e, ticketId) {
    var myTickActionMenu = new Ext.menu.Menu({
      items: [
        {
          text: "Transfer Ticket",
          handler: function () {
            var ticketId = Ext.getCmp(
              "gridpanelMasterDataviewMySupportTicketsdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.ticketId;
            var ticketSupTypeId = Ext.getCmp(
              "gridpanelMasterDataviewMySupportTicketsdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.ticketSupTypeId;
            transferTicket(ticketId, ticketSupTypeId);
          },
        },
        {
          text: "Request Feedback",
          handler: function () {
            var fstr_id = Ext.getCmp(
              "gridpanelMasterDataviewMySupportTicketsdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.ticketId;
            transferActions(ticketId, 5);
          },
        },
        {
          text: "Resolve",
          handler: function () {
            var fstr_id = Ext.getCmp(
              "gridpanelMasterDataviewMySupportTicketsdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.ticketId;
            transferActions(ticketId, 6);
          },
        },
        {
          text: "Escalate",
          handler: function () {
            var fstr_id = Ext.getCmp(
              "gridpanelMasterDataviewMySupportTicketsdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.ticketId;
            transferActions(ticketId, 7);
          },
        },
        {
          text: "Skip",
          handler: function () {
            var fstr_id = Ext.getCmp(
              "gridpanelMasterDataviewMySupportTicketsdata"
            )
              .getSelectionModel()
              .getSelections()[0].data.ticketId;
            transferActions(ticketId, 8);
          },
        },
      ],
    });
    myTickActionMenu.showAt(e.getXY());
  };
  var gridSelectionChangedMyAssignedtickets = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewMySupportTicketsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewMySupportTicketsdata")
        .getSelectionModel()
        .getSelections()[0].data.ticketId;
      var isAssigned = Ext.getCmp("gridpanelMasterDataviewMySupportTicketsdata")
        .getSelectionModel()
        .getSelections()[0].data.isAssigned;

      Application.SupportTicket.ViewMySupportTickets(ID);
    }
  };
  var mySupportTicketDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      region: "south",
      border: false,
      hideBorders: true,
      height: winsize.height * 0.35,
      autoScroll: true,
      id: "panelMasterMySupportTicketsDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Ticket Number </th><td>  {ticketNumber} </td></tr>',
        '<tr><th width="40%">Title </th><td>  {ticketTitle} </td></tr>',
        '<tr><th width="40%">Description </th><td>  {ticketDescription} </td></tr>',
        '<tr><th width="40%">Support Unit </th><td>  {ticketSuName} </td></tr>',
        '<tr><th width="40%">Contact No: </th><td>  {ticketContactNo} </td></tr>',
        '<tr><th width="40%">Contact Name </th><td>  {ticketContactName} </td></tr>',
        '<tr><th width="40%">Contact Email </th><td>  {ticketContactEmail} </td></tr>',
        '<tr><th width="40%">Created On </th><td>  {createdOn} </td></tr>',
        '<tr><th width="40%">Created From </th><td>  {createdFrom} </td></tr>',
        '<tr><th width="40%">Ticket Owner </th><td>  {ticketOwner} </td></tr>',
        '<tr><th width="40%">File</th><td>',
        "<tpl if=\"files != ' '\"><a href = '{filepath}' target= '_blank'>{files}</a></tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  var supportTicketLogs = function () {
    var __mySupportTicketLogGridStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listTicketLogs",
      method: "post",
      fields: [
        "ticketId",
        "ticketType",
        "ticketStatus",
        "ticketStage",
        "ticketRemarks",
        "ticketSupportUnit",
        "createdBy",
        "createdOn",
        "suName",
        "status",
        "tiketStage",
        "ticketTypeName","createdByName"
      ],
      remoteSort: true,
    });

    var __mySupportTicketLogGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "tiketStage",
        },
      ],
    });
    __mySupportTicketLogGridFilter.remote = true;
    __mySupportTicketLogGridFilter.autoReload = true;
    var __mySupportTicketLogGrid = new Ext.grid.GridPanel({
      id: "gridSupportTicketLogGrid",
      region: "north",
      height: 200,
      width: winsize.width * 0.38,
      frame: true,
      hidden: true,
      border: false,
      title: "Ticket Logs",
      autoScroll: true,
      store: __mySupportTicketLogGridStore,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
      }),
      viewConfig: {
        forceFit: true,
      },
      plugins: [__mySupportTicketLogGridFilter],
      fields: [
        "ticketId",
        "ticketType",
        "ticketStatus",
        "ticketStage",
        "ticketRemarks",
        "ticketSupportUnit",
        "createdBy",
        "createdOn",
        "suName",
        "status",
        "tiketStage",
        "ticketTypeName","createdByName"
      ],
      colModel: new Ext.grid.ColumnModel({
        columns: [
          {
            header: "Date",
            dataIndex: "createdOn",
          },
          {
            header: "User",
            dataIndex: "createdByName",
          },
          {
            header: "Type",
            dataIndex: "ticketTypeName",
          },
          {
            header: "Status",
            dataIndex: "status",
          },
          {
            header: "Stage",
            dataIndex: "tiketStage",
          },
          {
            header: "Remarks",
            dataIndex: "ticketRemarks",
            renderer: qtipRenderer,
          },
        ],
      }),
      iconCls: "icon-grid",
      listeners: {
        afterrender: function () {},
      },
    });
    return __mySupportTicketLogGrid;
  };
  var assignedTicketLogs = function () {
    var __mySupportTicketLogGridStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listTicketLogs",
      method: "post",
      fields: [
        "ticketId",
        "ticketType",
        "ticketStatus",
        "ticketStage",
        "ticketRemarks",
        "ticketSupportUnit",
        "createdBy","createdByName",
        "createdOn",
        "suName",
        "status",
        "tiketStage",
        "ticketTypeName",
      ],
      remoteSort: true,
    });

    var __mySupportTicketLogGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "tiketStage",
        },
      ],
    });
    __mySupportTicketLogGridFilter.remote = true;
    __mySupportTicketLogGridFilter.autoReload = true;
    var __mySupportTicketLogGrid = new Ext.grid.GridPanel({
      id: "gridAssignedTicketLogGrid",
      region: "north",
      height: 200,
      width: winsize.width * 0.38,
      frame: true,
      hidden: true,
      border: false,
      title: "Ticket Logs",
      autoScroll: true,
      store: __mySupportTicketLogGridStore,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
      }),
      viewConfig: {
        forceFit: true,
      },
      plugins: [__mySupportTicketLogGridFilter],
      fields: [
        "ticketId",
        "ticketType",
        "ticketStatus",
        "ticketStage",
        "ticketRemarks",
        "ticketSupportUnit",
        "createdBy",
        "createdOn",
        "suName",
        "status",
        "tiketStage",
        "ticketTypeName","createdByName"
      ],
      colModel: new Ext.grid.ColumnModel({
        columns: [
          {
            header: "Date",
            dataIndex: "createdOn",
          },
          {
            header: "User",
            dataIndex: "createdByName",
          },
          {
            header: "Type",
            dataIndex: "ticketTypeName",
          },
          {
            header: "Status",
            dataIndex: "status",
          },
          {
            header: "Stage",
            dataIndex: "tiketStage",
          },
          {
            header: "Remarks",
            dataIndex: "ticketRemarks",
            renderer: qtipRenderer,
          },
        ],
      }),
      iconCls: "icon-grid",
      listeners: {
        afterrender: function () {},
      },
    });
    return __mySupportTicketLogGrid;
  };
  var mySupportTicketLogs = function () {
    var __mySupportTicketLogGridStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listTicketLogs",
      method: "post",
      fields: [
        "ticketId",
        "ticketType",
        "ticketStatus",
        "ticketStage",
        "ticketRemarks",
        "ticketSupportUnit",
        "createdBy","createdByName",
        "createdOn",
        "suName",
        "status",
        "tiketStage",
        "ticketTypeName",
      ],
      remoteSort: true,
    });

    var __mySupportTicketLogGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "tiketStage",
        },
      ],
    });
    __mySupportTicketLogGridFilter.remote = true;
    __mySupportTicketLogGridFilter.autoReload = true;
    var __mySupportTicketLogGrid = new Ext.grid.GridPanel({
      id: "gridMyTicketLogGrid",
      region: "north",
      height: 200,
      width: winsize.width * 0.38,
      frame: true,
      hidden: true,
      border: false,
      title: "Ticket Logs",
      autoScroll: true,
      store: __mySupportTicketLogGridStore,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
      }),
      viewConfig: {
        forceFit: true,
      },
      plugins: [__mySupportTicketLogGridFilter],
      fields: [
        "ticketId",
        "ticketType",
        "ticketStatus",
        "ticketStage",
        "ticketRemarks",
        "ticketSupportUnit",
        "createdBy",
        "createdOn",
        "suName",
        "status",
        "tiketStage",
        "ticketTypeName","createdByName"
      ],
      colModel: new Ext.grid.ColumnModel({
        columns: [
          {
            header: "Date",
            dataIndex: "createdOn",
          },
          {
            header: "User",
            dataIndex: "createdByName",
          },
          {
            header: "Type",
            dataIndex: "ticketTypeName",
          },
          {
            header: "Status",
            dataIndex: "status",
          },
          {
            header: "Stage",
            dataIndex: "tiketStage",
          },
          {
            header: "Remarks",
            dataIndex: "ticketRemarks",
            renderer: qtipRenderer,
          },
        ],
      }),
      iconCls: "icon-grid",
      listeners: {
        afterrender: function () {},
      },
    });
    return __mySupportTicketLogGrid;
  };
  var transferActions = function (ticketId, action) {
    var transfetActionTicketWindow = new Ext.Window({
      id: "windowToConvertTicketStatus",
      iconCls: "vender-items",
      shadow: false,
      width: winsize.width * 0.4,
      height: winsize.height * 0.25,
      title: "Move Tickets",
      layout: "fit",
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      modal: true,
      items: [
        new Ext.FormPanel({
          layout: "form",
          height: 150,
          columnWidth: 1,
          frame: true,
          border: true,
          labelAlign: "top",
          items: [
            {
              fieldLabel: "Remarks",
              xtype: "textarea",
              allowblank: false,
              id: "ticketRemarks",
              name: "ticketRemarks",
              width: 330,
              height: 50,
              anchor: "95%",
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
            transfetActionTicketWindow.close();
          },
        },
        {
          xtype: "button",
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
          text: "Save",
          handler: function () {
            var ticketRemarks = Ext.getCmp("ticketRemarks").getValue();
            if (!Ext.isEmpty(ticketRemarks)) {
              Ext.Ajax.request({
                waitMsg: "Processing",
                method: "POST",
                url: modURL + "&op=ticketActions",
                params: {
                  ticketId: ticketId,
                  ticketAction: action,
                  ticketRemarks: ticketRemarks,
                },
                success: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp("gridpanelMasterDataviewMySupportTicketsdata")
                      .getStore()
                      .load({
                        callback: function (record, options, success) {
                          transfetActionTicketWindow.close();
                          Application.example.msg("Notification", tmp.msg);
                        },
                      });
                  } else {
                    Ext.MessageBox.alert("Notification", tmp.msg);
                  }
                },
                failure: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  Ext.MessageBox.alert("Error", tmp.msg);
                },
              });
            } else {
              Ext.MessageBox.alert("Error", "Add remarks and proceed.");
            }
          },
        },
      ],
      listeners: {
        load: function () {},
      },
    });

    transfetActionTicketWindow.doLayout();
    transfetActionTicketWindow.show();
    transfetActionTicketWindow.center();
  };
  var transferTicket = function (ticketId, ticketSupTypeId) {
    var suComboStore = supportUnitComboStorePrimary();
    var transfetTicketWindow = new Ext.Window({
      id: "windowToConvertTicketStatus",
      iconCls: "vender-items",
      shadow: false,
      width: winsize.width * 0.4,
      height: winsize.height * 0.4,
      title: "Transfer Ticket",
      layout: "fit",
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      modal: true,
      items: [
        new Ext.FormPanel({
          layout: "form",
          id: "moveToTransferTicket",
          height: 150,
          columnWidth: 1,
          frame: true,
          border: false,
          labelAlign: "top",
          items: [
            {
              xtype: "combo",
              store: suComboStore,
              mode: "local",
              id: "ticketSupportUnit",
              allowBlank: true,
              fieldLabel: "Support Unit",
              hiddenName: "ticketSupportUnit",
              displayField: "suName",
              valueField: "suId",
              typeAhead: true,
              forceSelection: true,
              editable: true,
              minChars: 2,
              anchor: "95%",
              triggerAction: "all",
              lazyRender: true,
              tabIndex: 102,
              listeners: {
                select: function () {},
              },
            },
            {
              fieldLabel: "Remarks",
              xtype: "textarea",
              allowblank: false,
              id: "ticketRemarks",
              name: "ticketRemarks",
              width: 330,
              height: 50,
              anchor: "95%",
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
            transfetTicketWindow.close();
          },
        },
        {
          xtype: "button",
          icon: IMAGE_BASE_PATH + "/default/icons/finascop_save.png",
          text: "Save",
          handler: function () {
            var ticketSupportUnit = Ext.getCmp("ticketSupportUnit").getValue();
            var ticketRemarks = Ext.getCmp("ticketRemarks").getValue();
            if (ticketSupportUnit > 0) {
              Ext.Ajax.request({
                waitMsg: "Processing",
                method: "POST",
                url: modURL + "&op=trasferTicket",
                params: {
                  ticketId: ticketId,
                  ticketSupportUnit: ticketSupportUnit,
                  ticketRemarks: ticketRemarks,
                },
                success: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  if (tmp.success === true) {
                    Ext.getCmp("gridpanelMasterDataviewMySupportTicketsdata")
                      .getStore()
                      .load({
                        callback: function (record, options, success) {
                          transfetTicketWindow.close();
                          Application.example.msg("Notification", tmp.msg);
                        },
                      });
                  } else {
                    Ext.MessageBox.alert("Notification", tmp.msg);
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
        afterrender: function () {
          Ext.getCmp("ticketSupportUnit").getStore().baseParams.suTypeId =
            ticketSupTypeId;
          Ext.getCmp("ticketSupportUnit").getStore().load();
        },
      },
    });

    transfetTicketWindow.doLayout();
    transfetTicketWindow.show();
    transfetTicketWindow.center();
  };
  var masterPanelforResolvedSupportTickets = function (id) {
    var _mpanelforResolvedSupportTicket = new Ext.Panel({
      frame: false,
      hideBorders: true,
      layout: "border",
      border: false,
      title: "Resolved Tickets",
      id: id,
      iconCls: "my-icon448",
      items: [
        resolvedSupportTicketMainGrid(),
        new Ext.Panel({
          title: "Support Ticket Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          cls: "left_side_panel",
          id: "panelMasterResolvedAssignedTicketParent",
          height: winsize.height * 0.6,
          items: [resolvedSupportTicketDetailsView(), resolvedSupportTicketLogs()],
          buttonAlign: "right",
          fbar: [],
        }),
      ],
    });
    return _mpanelforResolvedSupportTicket;
  };
  var resolvedSupportTicketsMasterStore = function () {
    var _assignedticketsMasterStore = new Ext.data.GroupingStore({
      proxy: new Ext.data.HttpProxy({
        url: modURL + "&op=listResolvedTickets",
        method: "post",
      }),
      reader: new Ext.data.JsonReader(
        {
          totalProperty: "totalCount",
          root: "data",
        },
        [
          "ticketId",
          "isAssigned",
          "ticketStatus",
          "ticketSuName",
          "ticketSupTypeId",
          "status",
          "tiketStage","ticketStageName",
          "ticketNumber",
          "createdOn",
          "createdBy",
          "ticketOwner",
          "ticketTitle",
          "ticketType",
          "ticketSuId",
          "ticketSupTypeName","ticketAssignedTo","ticketAssignedToName","createdDate","createdTime"
        ]
      ),
      sortInfo: {
        field: "ticketId",
        direction: "ASC",
      },
      groupField: "",
      groupDir: "ASC",
      remoteSort: true,
      autoLoad: false,
      root: "data",
      listeners: {
        load: function () {
          Ext.getCmp("gridpanelMasterDataviewResolvedSupportTicketsdata")
            .getView()
            .refresh();
          Ext.getCmp("gridpanelMasterDataviewResolvedSupportTicketsdata")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _assignedticketsMasterStore;
  };
  var resolvedSupportTicketMainGrid = function () {
    var _resolvedSupportTicketsStore = resolvedSupportTicketsMasterStore();
    var _resolvedTicketsGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "ticketNumber",
        },
        {
          type: "string",
          dataIndex: "ticketSuName",
        },{
          type: "string",
          dataIndex: "ticketOwner",
        },{
          type: "string",
          dataIndex: "ticketSupTypeName",
        },{
          type: "string",
          dataIndex: "ticketSuName",
        },{
          type: "string",
          dataIndex: "ticketAssignedToName",
        },{
          type: "string",
          dataIndex: "ticketStageName",
        },{
          type: "date",
          dataIndex: "createdDate",
        }, 
        {
          type: "list",
          options: ["Assigned", "Unassigned"],
          phpMode: true,
          dataIndex: "status",
        }
      ],
    });
    _resolvedTicketsGridFilter.remote = true;
    _resolvedTicketsGridFilter.autoReload = true;
    var _supportticketsmaingridPanel = new Ext.grid.GridPanel({
      region: "center",
      layout: "fit",
      frame: false,
      border: false,
      loadMask: true,
      store: _resolvedSupportTicketsStore,
      iconCls: "money",
      id: "gridpanelMasterDataviewResolvedSupportTicketsdata",
      view: new Ext.grid.GroupingView({
        forceFit: true,
        deferEmptyText: false,
        emptyText:
          '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
        groupTextTpl:
          '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
      }),
      plugins: [new Ext.ux.grid.GroupSummary(), _resolvedTicketsGridFilter],
      columns: [
        new Ext.grid.RowNumberer(),
        {
          header: "Created Date",
          dataIndex: "createdDate",
          sortable: true,
          tooltip: "Created Date",
          hideable: false,
        },{
          header: "Created Time",
          dataIndex: "createdTime",
          sortable: true,
          tooltip: "Created Time",
          hideable: false,
        },
        {
          header: "Date",
          dataIndex: "createdOn",
          sortable: true,
          tooltip: "Date",
          hideable: false,
        },
        {
          header: "Ticket Owner",
          dataIndex: "ticketOwner",
          sortable: true,
          tooltip: "Ticket Owner",
          hideable: false,
        },
        {
          header: "Ticket Type",
          dataIndex: "ticketSupTypeName",
          sortable: true,
          tooltip: "Ticket Type",
          hideable: false,
        },
        {
          header: "Ticket Number",
          dataIndex: "ticketNumber",
          sortable: true,
          tooltip: "Ticket Number",
          hideable: false,
        },
        {
          header: "Status",
          dataIndex: "status",
          sortable: true,
          tooltip: "Status",
        },
        {
          header: "Support Unit",
          dataIndex: "ticketSuName",
          sortable: true,
          tooltip: "Support Unit",
          hideable: false,
        },        
        {
          header: "Stage",
          dataIndex: "ticketStageName",
          sortable: true,
          tooltip: "Stage",
        },{
          header: "Resource",
          dataIndex: "ticketAssignedToName",
          sortable: true,
          tooltip: "Resource",
        }
      ],
      viewConfig: {
        forceFit: true,
      },
      bbar: new Ext.PagingToolbar({
        pageSize: RECS_PER_PAGE,
        store: _resolvedSupportTicketsStore,
        displayInfo: true,
        displayMsg: "Displaying records {0} - {1} of {2}",
        emptyMsg: "No pages to display",
      }),
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
        listeners: {
          selectionchange: gridSelectionChangedResolvedtickets,
        },
      }),
      listeners: {
        cellClick: function (grid, rowIndex, columnIndex, e) {
          var record = grid.getStore().getAt(rowIndex);
          var ID = record.get("ticketId");
          if (!Ext.isEmpty(ID)) {
            Application.SupportTicket.Cache.myticketId = ID;
            Application.SupportTicket.ViewResolvedSupportTickets(ID);
          }
        },
        resize: onGridResize,
        afterrender: function () {
          _resolvedSupportTicketsStore.load();
        },
      },
      tbar: [],
    });
    return _supportticketsmaingridPanel;
  };
  
  var gridSelectionChangedResolvedtickets = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelMasterDataviewResolvedSupportTicketsdata")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelMasterDataviewResolvedSupportTicketsdata")
        .getSelectionModel()
        .getSelections()[0].data.ticketId;
      var isAssigned = Ext.getCmp("gridpanelMasterDataviewResolvedSupportTicketsdata")
        .getSelectionModel()
        .getSelections()[0].data.isAssigned;

      Application.SupportTicket.ViewResolvedSupportTickets(ID);
    }
  };
  var resolvedSupportTicketDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      region: "south",
      border: false,
      hideBorders: true,
      height: winsize.height * 0.35,
      autoScroll: true,
      id: "panelMasterResolvedSupportTicketsDetailsView",
      tpl: new Ext.XTemplate(
        '<div class="details-outer">',
        '<table border="0" width="100%" class="details_view_table">',
        '<tr><th width="40%">Ticket Number </th><td>  {ticketNumber} </td></tr>',
        '<tr><th width="40%">Title </th><td>  {ticketTitle} </td></tr>',
        '<tr><th width="40%">Description </th><td>  {ticketDescription} </td></tr>',
        '<tr><th width="40%">Support Unit </th><td>  {ticketSuName} </td></tr>',
        '<tr><th width="40%">Contact No: </th><td>  {ticketContactNo} </td></tr>',
        '<tr><th width="40%">Contact Name </th><td>  {ticketContactName} </td></tr>',
        '<tr><th width="40%">Contact Email </th><td>  {ticketContactEmail} </td></tr>',
        '<tr><th width="40%">Created On </th><td>  {createdOn} </td></tr>',
        '<tr><th width="40%">Created From </th><td>  {createdFrom} </td></tr>',
        '<tr><th width="40%">Ticket Owner </th><td>  {ticketOwner} </td></tr>',
        '<tr><th width="40%">File</th><td>',
        "<tpl if=\"files != ' '\"><a href = '{filepath}' target= '_blank'>{files}</a></tpl>",
        "</td></tr>",
        "</table>",
        "</div>"
      ),
    });
  };
  
  var resolvedSupportTicketLogs = function () {
    var __resolvedSupportTicketLogGridStore = new Ext.data.JsonStore({
      autoLoad: true,
      url: modURL + "&op=listTicketLogs",
      method: "post",
      fields: [
        "ticketId",
        "ticketType",
        "ticketStatus",
        "ticketStage",
        "ticketRemarks",
        "ticketSupportUnit",
        "createdBy","createdByName",
        "createdOn",
        "suName",
        "status",
        "tiketStage",
        "ticketTypeName",
      ],
      remoteSort: true,
    });

    var __resolvedSupportTicketLogGridFilter = new Ext.ux.grid.GridFilters({
      filters: [
        {
          type: "string",
          dataIndex: "tiketStage",
        },
      ],
    });
    __resolvedSupportTicketLogGridFilter.remote = true;
    __resolvedSupportTicketLogGridFilter.autoReload = true;
    var __resolvedSupportTicketLogGrid = new Ext.grid.GridPanel({
      id: "gridResolvedTicketLogGrid",
      region: "north",
      height: 200,
      width: winsize.width * 0.38,
      frame: true,
      hidden: true,
      border: false,
      title: "Ticket Logs",
      autoScroll: true,
      store: __resolvedSupportTicketLogGridStore,
      sm: new Ext.grid.RowSelectionModel({
        singleSelect: true,
      }),
      viewConfig: {
        forceFit: true,
      },
      plugins: [__resolvedSupportTicketLogGridFilter],
      fields: [
        "ticketId",
        "ticketType",
        "ticketStatus",
        "ticketStage",
        "ticketRemarks",
        "ticketSupportUnit",
        "createdBy",
        "createdOn",
        "suName",
        "status",
        "tiketStage",
        "ticketTypeName","createdByName"
      ],
      colModel: new Ext.grid.ColumnModel({
        columns: [
          {
            header: "Date",
            dataIndex: "createdOn",
          },
          {
            header: "User",
            dataIndex: "createdByName",
          },
          {
            header: "Type",
            dataIndex: "ticketTypeName",
          },
          {
            header: "Status",
            dataIndex: "status",
          },
          {
            header: "Stage",
            dataIndex: "tiketStage",
          },
          {
            header: "Remarks",
            dataIndex: "ticketRemarks",
            renderer: qtipRenderer,
          },
        ],
      }),
      iconCls: "icon-grid",
      listeners: {
        afterrender: function () {},
      },
    });
    return __resolvedSupportTicketLogGrid;
  };
  return {
    Cache: {},
    initSupportTicket: function () {
      var _supportTicketPanelId = "panelMasterMainSupportTicket";
      var _masterPanelSupportTicket = Ext.getCmp(_supportTicketPanelId);
      if (Ext.isEmpty(_masterPanelSupportTicket)) {
        _masterPanelSupportTicket = masterPanelforSupportTicket(
          _supportTicketPanelId
        );
        Application.UI.addTab(_masterPanelSupportTicket);
        _masterPanelSupportTicket.doLayout();
      } else {
        Application.UI.addTab(_masterPanelSupportTicket);
      }
    },
    ViewSupportTickets: function () {
      var ticketId = arguments[0];

      Ext.getCmp("panelMasterSupportTicketsDetailsView").show();
      Ext.getCmp("panelMasterSupportTicketParent").doLayout();
      Ext.getCmp("panelMasterSupportTicketParent").setTitle(
        "View Support Ticket Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=supportticketsdetailsView",
        method: "POST",
        params: { ticketId: ticketId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelMasterSupportTicketsDetailsView"
            );
            visualsDescPanel.update(tmp);
            Ext.getCmp("gridSupportTicketLogGrid").show();
            Ext.getCmp("gridSupportTicketLogGrid")
              .getStore()
              .load({
                params: {
                  ticketId: ticketId,
                },
              });
          }
          Ext.getCmp("panelMasterSupportTicketParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterSupportTicketParent").doLayout();
    },
    AssignSupportTickets: function () {
      var ticketId = arguments[0];
      var supportUnitId = arguments[1];
      Ext.Ajax.request({
        url: modURL + "&op=assignSupportUnit",
        method: "POST",
        params: { ticketId: ticketId, supportUnitId: supportUnitId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success == true) {
            Application.example.msg("Notification", tmp.msg);
            Ext.getCmp("gridpanelMasterDataviewSupportTicketsdata")
              .getStore()
              .load();
          } else {
            Ext.MessageBox.alert("Error", tmp.msg);
          }
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
    },
    assignSuToUser: function (userId) {
      var availableSupportUnitWindowid = Ext.getCmp(
        "availableSupportUnitWindow"
      );
      if (Ext.isEmpty(availableSupportUnitWindowid)) {
        availableSupportUnitWindowid = new Ext.Window({
          id: "availableSupportUnitWindow",
          title: "Assign Support Unit to User",
          modal: true,
          height: 500,
          width: winsize.width * 0.4,
          shadow: false,
          resizable: false,
          layout: "border",
          items: [
            availableSupportUnitGrid(userId),
            new Ext.Panel({
              title: "Assigned Support Unit",
              frame: false,
              border: true,
              region: "east",
              width: winsize.width * 0.2,
              cls: "left_side_panel",
              height: 500,
              autoScroll: true,
              items: [mappedUserSupportUnits(userId)],
              buttons: [],
              fbar: [],
            }),
          ],
          buttons: [
            {
              text: "Close",
              iconCls: "my-icon61",
              handler: function () {
                availableSupportUnitWindowid.close();
              },
            },
          ],
          listeners: {
            close: function () {},
          },
        });
      }
      availableSupportUnitWindowid.doLayout();
      availableSupportUnitWindowid.show();
      availableSupportUnitWindowid.center();
    },
    mapSupportUnitToUser: function (brandarr, userId) {
      Ext.Ajax.request({
        url: modURL + "&op=mapSupportUnitsToUser",
        method: "post",
        params: {
          brandarr: Ext.encode(brandarr),
          userId: userId,
        },
        success: function (resp) {
          var res = Ext.decode(resp.responseText);
          if (res.success === true) {
            Ext.getCmp(
              "gridpanelforAvailableSupportUnits"
            ).getStore().baseParams.userId = userId;
            Ext.getCmp("gridpanelforAvailableSupportUnits").getStore().load();

            Ext.getCmp(
              "gridpanelforUserMappedSupportUnits"
            ).getStore().baseParams.userId = userId;
            Ext.getCmp("gridpanelforUserMappedSupportUnits").getStore().load();
          }
        },
      });
    },
    initAssignedTicket: function () {
      var _assignedTicketPanelId = "panelMasterMainAssignedTicket";
      var _masterPanelAssignedTicket = Ext.getCmp(_assignedTicketPanelId);
      if (Ext.isEmpty(_masterPanelAssignedTicket)) {
        _masterPanelAssignedTicket = masterPanelforAssignedTicket(
          _assignedTicketPanelId
        );
        Application.UI.addTab(_masterPanelAssignedTicket);
        _masterPanelAssignedTicket.doLayout();
      } else {
        Application.UI.addTab(_masterPanelAssignedTicket);
      }
    },
    ViewAssignedTickets: function () {
      var ticketId = arguments[0];

      Ext.getCmp("panelMasterAssignedTicketsDetailsView").show();
      Ext.getCmp("panelMasterAssignedTicketParent").doLayout();
      Ext.getCmp("panelMasterAssignedTicketParent").setTitle(
        "View Support Ticket Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=supportticketsdetailsView",
        method: "POST",
        params: { ticketId: ticketId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelMasterAssignedTicketsDetailsView"
            );
            visualsDescPanel.update(tmp);
            Ext.getCmp("gridAssignedTicketLogGrid").show();
            Ext.getCmp("gridAssignedTicketLogGrid")
              .getStore()
              .load({
                params: {
                  ticketId: ticketId,
                },
              });
          }
          Ext.getCmp("panelMasterAssignedTicketParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterAssignedTicketParent").doLayout();
    },
    AcceptSupportTickets: function () {
      var ticketId = arguments[0];
      Ext.Ajax.request({
        url: modURL + "&op=acceptSupportUnit",
        method: "POST",
        params: { ticketId: ticketId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success == true) {
            Application.example.msg("Notification", tmp.msg);
            Ext.getCmp("gridpanelMasterDataviewAssignedTicketsdata")
              .getStore()
              .load();
          } else {
            Ext.MessageBox.alert("Notification", tmp.msg);
          }
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
    },
    initMySupportTicket: function () {
      var _myAssignedTicketPanelId = "panelMasterMainMySupportTicket";
      var _masterPanelMyAssignedTicket = Ext.getCmp(_myAssignedTicketPanelId);
      if (Ext.isEmpty(_masterPanelMyAssignedTicket)) {
        _masterPanelMyAssignedTicket = masterPanelforMySupportTickets(
          _myAssignedTicketPanelId
        );
        Application.UI.addTab(_masterPanelMyAssignedTicket);
        _masterPanelMyAssignedTicket.doLayout();
      } else {
        Application.UI.addTab(_masterPanelMyAssignedTicket);
      }
    },
    ViewMySupportTickets: function () {
      var ticketId = arguments[0];

      Ext.getCmp("panelMasterMySupportTicketsDetailsView").show();
      Ext.getCmp("panelMasterMyAssignedTicketParent").doLayout();
      Ext.getCmp("panelMasterMyAssignedTicketParent").setTitle(
        "View Support Ticket Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=supportticketsdetailsView",
        method: "POST",
        params: { ticketId: ticketId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelMasterMySupportTicketsDetailsView"
            );
            visualsDescPanel.update(tmp);

            Ext.getCmp("gridMyTicketLogGrid").show();
            Ext.getCmp("gridMyTicketLogGrid")
              .getStore()
              .load({
                params: {
                  ticketId: ticketId,
                },
              });
          }
          Ext.getCmp("panelMasterMyAssignedTicketParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterMyAssignedTicketParent").doLayout();
    },
    supportRequestExtWindow: function (type, mobile, name, email) {
      fileS3BucketView();
      var reswindow = new Ext.Window({
        title: "Support Request",
        width: 650,
        autoHeight: true,
        plain: true,
        constrainHeader: true,
        modal: true,
        frame: true,
        border: false,
        iconCls: "",
        resizable: false,
        id: "supportRequestWindow",
        items: [SupportTicketForm()],
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
              saveSupportTickets();
            },
          },
        ],
        listeners: {
          afterrender: function () {
            var typeId, typeName;
            switch(type){
              case 'Merchant':
                typeName = "Retailer";
              break;
              case 'Customer':
                typeName = "Customer";
              break;
            }
            
            var store = Ext.getCmp("ticketSupTypeId").getStore();
            if (!store.getCount()) {
              store.load({
                callback: function (records, operation, success) {
                  if (success) {
                    findTypeId();
                  } else {
                    console.error("Error loading store");
                  }
                },
              });
            } else {
              findTypeId();
            }

            function findTypeId() {
              store.each(function (record) {
                if (record.get("typeName") === typeName) {
                  typeId = record.get("typeId");
                  return false;
                }
              });
              Ext.getCmp("ticketSupTypeId").setValue(typeId);
              Ext.getCmp("ticketSupTypeId").setRawValue(typeName);
              Ext.getCmp("ticketSupTypeId").setHideTrigger(true);
              Ext.getCmp("ticketSuId").getStore().baseParams.suTypeId = typeId;
              Ext.getCmp("ticketSuId").getStore().load();
            }

            Ext.getCmp("ticketContactNo").setValue(mobile);
            Ext.getCmp("ticketContactName").show();
            Ext.getCmp("ticketContactName").setValue(name);
            Ext.getCmp("ticketContactEmail").show();
            Ext.getCmp("ticketContactEmail").setValue(email);
            Ext.getCmp('stcheckAvail').hide();
          },
        },
      });
      reswindow.doLayout();
      reswindow.show();
      reswindow.center();
    },
    initResolvedSupportTicket: function () {
      var _resolvedAssignedTicketPanelId = "panelMasterMainResolvedSupportTicket";
      var _masterPanelResolvedAssignedTicket = Ext.getCmp(_resolvedAssignedTicketPanelId);
      if (Ext.isEmpty(_masterPanelResolvedAssignedTicket)) {
        _masterPanelResolvedAssignedTicket = masterPanelforResolvedSupportTickets(
          _resolvedAssignedTicketPanelId
        );
        Application.UI.addTab(_masterPanelResolvedAssignedTicket);
        _masterPanelResolvedAssignedTicket.doLayout();
      } else {
        Application.UI.addTab(_masterPanelResolvedAssignedTicket);
      }
    },
    ViewResolvedSupportTickets: function () {
      var ticketId = arguments[0];

      Ext.getCmp("panelMasterResolvedSupportTicketsDetailsView").show();
      Ext.getCmp("panelMasterResolvedAssignedTicketParent").doLayout();
      Ext.getCmp("panelMasterResolvedAssignedTicketParent").setTitle(
        "View Support Ticket Details"
      );
      Ext.Ajax.request({
        url: modURL + "&op=supportticketsdetailsView",
        method: "POST",
        params: { ticketId: ticketId },
        success: function (res) {
          var tmp = Ext.decode(res.responseText);
          if (tmp.success === true) {
            var visualsDescPanel = Ext.getCmp(
              "panelMasterResolvedSupportTicketsDetailsView"
            );
            visualsDescPanel.update(tmp);

            Ext.getCmp("gridResolvedTicketLogGrid").show();
            Ext.getCmp("gridResolvedTicketLogGrid")
              .getStore()
              .load({
                params: {
                  ticketId: ticketId,
                },
              });
          }
          Ext.getCmp("panelMasterResolvedAssignedTicketParent").doLayout();
        },
        failure: function () {
          Ext.MessageBox.alert("Error", "Error occured while sending data");
        },
      });
      Ext.getCmp("panelMasterResolvedAssignedTicketParent").doLayout();
    },
  };
})();
