Application.RelationshipOfficer = (function () {
  var recs_per_page = 21;
  var modURL = "?module=relationship_officer";
  var winsize = Ext.getBody().getViewSize();
  var updatePagination = function (cmp) {
    recs_per_page = updateRecsPerPage(cmp);
  };
  var gridSelectionChangedro = function (sm) {
    if (
      !Ext.isEmpty(
        Ext.getCmp("gridpanelforBaRealtOffcr")
          .getSelectionModel()
          .getSelections()
      )
    ) {
      var ID = Ext.getCmp("gridpanelforBaRealtOffcr").getSelectionModel().getSelections()[0].data.id;
      var roName = Ext.getCmp("gridpanelforBaRealtOffcr").getSelectionModel().getSelections()[0].data.roName;
      Application.RelationshipOfficer.Cache.roId = ID;
      Application.RelationshipOfficer.Cache.roName = roName;
      var roStatus = Ext.getCmp("gridpanelforBaRealtOffcr").getSelectionModel().getSelections()[0].data.roStatus;
      console.log('roStatus');
      console.log(roStatus);
      switch(roStatus){
        case '1':
          Ext.getCmp('acceptRo').show();
          Ext.getCmp('rejectRo').show();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '2':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').show();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '3':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '4':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '5':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').show();
          Ext.getCmp('interviewFailed').show();
          Ext.getCmp('interviewHold').show();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '6':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').show();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '7':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '8':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').show();
          Ext.getCmp('interviewFailed').show();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '9':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').show();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '10':
            Ext.getCmp('acceptRo').hide();
            Ext.getCmp('rejectRo').hide();
            Ext.getCmp('scheduleInterview').hide();
            Ext.getCmp('interviewPassed').hide();
            Ext.getCmp('interviewFailed').hide();
            Ext.getCmp('interviewHold').hide();
            Ext.getCmp('updateAppointment').hide();
            Ext.getCmp('acceptAppointment').hide();
            Ext.getCmp('reviewAppointment').show();
            Ext.getCmp('trainRO').hide();
            Ext.getCmp('updateRO').hide();
            Ext.getCmp('completeOnboardingRO').hide();
            break;
        case '11':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').show();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '12':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').show();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '13':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').show();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
		case '14':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').show();
		  Ext.getCmp('completeOnboardingRO').hide();
          break;
        case '15':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').show();
          Ext.getCmp('completeOnboardingRO').show();
          break;
        case '16':
          Ext.getCmp('acceptRo').hide();
          Ext.getCmp('rejectRo').hide();
          Ext.getCmp('scheduleInterview').hide();
          Ext.getCmp('interviewPassed').hide();
          Ext.getCmp('interviewFailed').hide();
          Ext.getCmp('interviewHold').hide();
          Ext.getCmp('updateAppointment').hide();
          Ext.getCmp('acceptAppointment').hide();
          Ext.getCmp('reviewAppointment').hide();
          Ext.getCmp('trainRO').hide();
          Ext.getCmp('updateRO').hide();
          Ext.getCmp('completeOnboardingRO').hide();
          break;
      }
      Application.RelationshipOfficer.roViewMode(ID);
    }
  };
  var relationshipOfficerDetailsView = function () {
    return new Ext.Panel({
      layout: "fit",
      region: "south",
      border: false,
      hideBorders: true,
      height: winsize.height * 0.35,
      autoScroll: true,
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
  var _baRelOffcrStore = function () {
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
          Ext.getCmp("gridpanelforBaRealtOffcr").getView().refresh();
          Ext.getCmp("gridpanelforBaRealtOffcr")
            .getSelectionModel()
            .selectRow(0);
        },
      },
    });
    return _Store;
  };
  var _relationshipOfficerGrid = function () {
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
      id: "gridpanelforBaRealtOffcr",
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
          selectionchange: gridSelectionChangedro,
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
          Ext.getCmp("gridpanelforBaRealtOffcr")
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
  var relationshipOfficerMainPanel = function (id) {
    var mid = 0;
    var panel = new Ext.Panel({
      layout: "border",
      border: false,
      frame: false,
      bodyStyle: { "background-color": "white" },
      hideBorders: true,
      id: id,
      title: "Relationship Officer",
      items: [
        _relationshipOfficerGrid(),
        new Ext.Panel({
          title: "View Details",
          frame: false,
          border: true,
          region: "east",
          width: winsize.width * 0.4,
          autoScroll: true,
          height: winsize.height * 0.5,
          id: "relationshipOfficerPanel",
          items: [relationshipOfficerDetailsView(), supportTicketLogs()],
          buttonAlign: "right",
          buttons: [{
            text: "Accept",
            id: "acceptRo",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
            tabIndex: 200,
            handler: function () {
              Application.RelationshipOfficer.roActions(Application.RelationshipOfficer.Cache.roId,'accept');
            },
          },{
            text: "Reject",
            id: "rejectRo",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/reject.png",
            tabIndex: 201,
            handler: function () {
              Application.RelationshipOfficer.roActions(Application.RelationshipOfficer.Cache.roId,'reject');
            },
          },{
            text: "Schedule Interview",
            id: "scheduleInterview",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
            tabIndex: 202,
            handler: function () {
              Application.RelationshipOfficer.scheduleInterview(Application.RelationshipOfficer.Cache.roId);
            },
          },{
            text: "Interview Passed",
            id: "interviewPassed",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
            tabIndex: 204,
            handler: function () {
              Application.RelationshipOfficer.roActions(Application.RelationshipOfficer.Cache.roId,'interview-passed');
            }
          },{
            text: "Interview Failed",
            id: "interviewFailed",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/reject.png",
            tabIndex: 204,
            handler: function () {
              Application.RelationshipOfficer.roActions(Application.RelationshipOfficer.Cache.roId,'interview-failed');
            }
          },{
            text: "Interview On Hold",
            id: "interviewHold",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/action.png",
            tabIndex: 204,
            handler: function () {
              Application.RelationshipOfficer.roActions(Application.RelationshipOfficer.Cache.roId,'interview-hold');
            }
          },{
            text: "Upload Draft Appointment Order",
            id: "updateAppointment",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
            tabIndex: 203,
            handler: function () {
              Application.RelationshipOfficer.uploadAppointment(Application.RelationshipOfficer.Cache.roId,'upload');
            },
          },{
            text: "Accept Appointment",
            id: "acceptAppointment",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
            tabIndex: 203,
            handler: function () {
              Application.RelationshipOfficer.uploadAppointment(Application.RelationshipOfficer.Cache.roId,'accept');
            },
          },{
            text: "Review Appointment",
            id: "reviewAppointment",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
            tabIndex: 203,
            handler: function () {
              Application.RelationshipOfficer.reveiwAppointment(Application.RelationshipOfficer.Cache.roId);
              
            },
          },{
            text: "Training",
            id: "trainRO",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
            tabIndex: 203,
            handler: function () {
              Application.RelationshipOfficer.trainingUpdates(Application.RelationshipOfficer.Cache.roId,Application.RelationshipOfficer.Cache.roName);
            },
          },{
            text: "Update Details",
            id: "updateRO",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/edit.png",
            tabIndex: 203,
            handler: function () {
              Ext.Ajax.request({
                url: modURL + '&op=loadRODetails',
                method: 'POST',
                params: {
                    roId: Application.RelationshipOfficer.Cache.roId,
                },
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    var img_url, thumpurl;
                    if (tmp.data.roVisitingcard == '' || tmp.data.roVisitingcard == null) {
                      thumpurl = '/resources/images/default.png';                        
                    } else {
                      thumpurl = tmp.data.roVisitingcard;
                    }
                    if (tmp.data.roIdcard == '' || tmp.data.roIdcard == null) {
                      img_url = '/resources/images/default.png';
                    } else {                        
                        img_url = tmp.data.roIdcard;
                    }
                    Application.RelationshipOfficer.updateRODetails(Application.RelationshipOfficer.Cache.roId, img_url,thumpurl);
                    
                }
            })
              
            },
          },{
            text: "Onboarding Completed",
            id: "completeOnboardingRO",
            hidden:true,
            icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
            tabIndex: 203,
            handler: function () {
              Application.RelationshipOfficer.completeOnboarding(Application.RelationshipOfficer.Cache.roId);
            },
          }]
        }),
      ],
    });
    return panel;
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
                Ext.getCmp('s3_albumBucketName').setValue(tmp.data.grzBucketName);
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
function addPhoto(type) {

  var grzBucketName = Ext.getCmp('grzBucketName').getValue();
  var bucketRegion = Ext.getCmp('bucketRegion').getValue();
  var filepath = Ext.getCmp('oncompleteurl').getValue();
  console.log(filepath);
  AWS.config.update({
      region: bucketRegion,
      credentials: new AWS.Credentials(
              Ext.getCmp('accessKey').getValue(),
              Ext.getCmp('secretKey').getValue(), null
              )
  });
  var s3 = new AWS.S3({
      apiVersion: '2006-03-01',
      params: {Bucket: grzBucketName}
  });
  switch (type) {
      case 'main':
          files = document.getElementById('associated_idcardfile-file').files;
          break;
      case 'thump':
          files = document.getElementById('associated_visitingcardfile-file').files;
          break;
  }
  console.log(files);
  if (!files.length) {
      winLoadMask.hide();
      return alert('Please choose a file to upload first.');
  }
  var file = files[0];
  var filesize = files[0]['size'];
  var size=(filesize)/1000;
  if (size > 200) {
      winLoadMask.hide();
      return alert('File size should not exceed 200KB.');
  }
  var actualfileName = file.name;
  var file_Name = JSON.stringify(actualfileName).slice(1, -1);
  var fileExt = file_Name.split('.').pop();

  var fileName = uuidv4();
  fileName = fileName + '.' + fileExt;
  console.log(filesize);
  console.log(size);
  s3.upload({
      Key: filepath + fileName,
      /*file_Name*/ /*from server*/
      Body: file,
      ACL: 'public-read'
  }, function (err, data) {

      if (err) {
          winLoadMask.hide();
          //var img_src = Ext.BLANK_IMAGE_URL;
          var img_src_main = '/resources/images/awesomeupload/no_image.png';
          var img_src_list = '/resources/images/awesomeupload/no_image.png';
          switch (type) {
              case 'main':
                  Ext.getCmp('idcard_image_panel').update({'img_src': img_src_main});
                  break;
              case 'thump':
                  Ext.getCmp('visitingcard_image_panel').update({'thumpimg_src': img_src_list});
                  break;
          }
          return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
      }
      if (!Ext.isEmpty(data.Location)) {
          //var img_width = Ext.getCmp('widgetImageWidth').getValue();
          //var img_height = Ext.getCmp('widgetImageHeight').getValue();
          var img = new Image();
          img.onload = function () {
              var flag = 1;
              /*if (this.width != img_width || this.height != img_height) {
                  Ext.Msg.alert("Notification", 'Image size should be ' + img_width + '*' + img_height);
                  winLoadMask.hide();
                  flag = 0;
              }*/

              if (flag == 1) {
                  winLoadMask.hide();
          Ext.Msg.alert("Notification", 'File has been uploaded successfully.');
          Application.RelationshipOfficer.UploadedFileLocation = data.Location;
          Application.RelationshipOfficer.UploadedFileBucket = data.Bucket;
          Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
          switch (type) {
              case 'main':
                  Ext.getCmp('aws_file_locationidcard').setValue(data.Location);
                  Ext.getCmp('idcard_image_panel').update({'img_src': Application.RelationshipOfficer.UploadedFileLocation});
                  break;
              case 'thump':
                  Ext.getCmp('aws_file_locationvisitingcard').setValue(data.Location);
                  Ext.getCmp('visitingcard_image_panel').update({'thumpimg_src': Application.RelationshipOfficer.UploadedFileLocation});
                  break;
          }
              }

          }
          img.src = data.Location;

          

      }else{
          var img_src_main = '/resources/images/awesomeupload/no_image.png';
          var img_src_list = '/resources/images/awesomeupload/no_image.png';
          switch (type) {
              case 'main':
                  Ext.getCmp('idcard_image_panel').update({'img_src': img_src_main});
                  break;
              case 'thump':
                  Ext.getCmp('visitingcard_image_panel').update({'thumpimg_src': img_src_list});
                  break;
          }
      }
  });
}
var roDetailUpdateForm = function () {  
  var _roDetailUpdateForm = new Ext.Panel({
      layout: "fit",
      region: 'west',
      width: 300,
      border: false,
      //autoHeight:true,
      items: [
          new Ext.form.FormPanel({
              id: 'formpanelRelationshipOfficer',
              frame: false,
              border: false,
              autoHeight: true,
              autoScroll: true,
              labelWidth: 120,
              labelAlign: 'top',
              bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
              items: [{
                      xtype: 'textfield',
                      fieldLabel: 'Resource ID',
                      emptyText: 'Resource ID',
                      id: 'roResourceId',
                      name: 'n[roResourceId]',
                      anchor: '98%',
                      tabIndex: 500,
                      maxLength: 300,
                      listeners: {
                          
                      }
                  }, 
                  {
                      xtype: 'textfield',
                      id: 'roId',
                      name: 'n[roId]',
                      hidden: true
                  },{
                  xtype: 'textfield',
                  fieldLabel: 'Email ID',
                  emptyText: 'Email ID',
                  id: 'roEmailId',
                  name: 'n[roEmailId]',
                  anchor: '98%',
                  tabIndex: 501,
                  maxLength: 300,
                  listeners: {
                      
                  }
              },{
                xtype: 'textfield',
                fieldLabel: 'Mobile ID',
                emptyText: 'Mobile ID',
                id: 'roMobile',
                name: 'n[roMobile]',
                anchor: '98%',
                tabIndex: 502,
                maxLength: 300,
                listeners: {
                    
                }
            },{
              xtype: 'textfield',
              fieldLabel: 'IMEI No',
              emptyText: 'IMEI No',
              id: 'roImeiNo',
              name: 'n[roImeiNo]',
              anchor: '98%',
              tabIndex: 503,
              maxLength: 300,
              listeners: {
                  
              }
          },{
            xtype: 'textfield',
            fieldLabel: 'Courier Way Bill',
            emptyText: 'Courier Way Bill',
            id: 'roCourierWaybill',
            name: 'n[roCourierWaybill]',
            anchor: '98%',
            tabIndex: 504,
            maxLength: 300,
            listeners: {
                
            }
        },{
          xtype: 'datefield',
          fieldLabel: 'Courier Date',
          emptyText: 'Courier Date',
          id: 'roCourierDate',
          name: 'n[roCourierDate]',
          anchor: '98%',
          format: 'd/m/Y',
          tabIndex: 505,
          listeners: {
              
          }
      },{
        xtype: 'textfield',
        fieldLabel: 'Licence No',
        emptyText: 'Licence No',
        id: 'roLicenceNo',
        name: 'n[roLicenceNo]',
        anchor: '98%',
        tabIndex: 506,
        maxLength: 300,
        listeners: {
            
        }
    },{
      xtype: 'textfield',
      fieldLabel: 'Aadhaar',
      emptyText: 'Aadhaar',
      id: 'roAadhaar',
      name: 'n[roAadhaar]',
      anchor: '98%',
      tabIndex: 507,
      maxLength: 300,
      listeners: {
          
      }
  }
              ]
          })
      ],
      listeners: {
          afterrender: function () {
              
          }
      }
  });
  return _roDetailUpdateForm;
};


var roPhotoUploadForm = function (img, thumpurl) {
  return new Ext.Panel({
      id: 'uploadformpanel',
      items: [                
          {
              xtype: 'hidden',
              id: 'aws_file_locationidcard',
              name: 'aws_file_locationidcard'
          }, {
              xtype: 'hidden',
              id: 'aws_file_locationvisitingcard',
              name: 'aws_file_locationvisitingcard'
          }, {
              xtype: 'hidden',
              id: 'aws_file_bucket',
              name: 'aws_file_bucket'
          },
          new Ext.form.FormPanel({
              id: 'main_image_upload',
              layout: 'form',
              fileUpload: true,
              //autoHeight: true,
              height :600,
              hidLabel: true,
              frame: true,
              items: [{
                      layout: 'column',
                      border: false,
                      items: [{
                              columnWidth: 1,
                              layout: 'form',
                              id: 'idcard_image_panel_form',
                              height:300,
                              border: false,
                              items: [new Ext.Panel({
                                      layout: "fit",
                                      id: 'idcard_image_panel',
                                      tpl: new Ext.XTemplate('<div class="details-outer-event">',
                                              '<img style="width: 100%; max-width: 300px; max-height: 300px;" src="{img_src}"></img>',
                                              '</div>')
                                  }), {
                                      xtype: 'box',
                                      // fieldLabel: 'Current Image',
                                      width: 468,
                                      height: 210,
                                      id: 'exist_img_box',
                                      //autoEl: { tag: 'img', src: img, width: 120, height: 100 }
                                      autoEl: {tag: 'img', src: img, width: '468', height: 210}

                                  }, {
                                      xtype: 'fileuploadfield',
                                      id: 'associated_idcardfile',
                                      anchor: '98%',
                                      name: 'associated_idcardfile',
                                      allowBlank: true,
                                      //tabIndex: 511,
                                      buttonOnly: true,
                                      buttonCfg: {
                                          text: 'Upload ID',
                                          //iconCls:'so_upload',
                                          width: 80
                                      },
                                      validator: function (v) {
                                          if (v != '') {
                                              v = v.toLowerCase();
                                              var exp = /^.*\.(png|jpg|jpeg|gif)$/i;
                                              if (!(exp.test(v))) {
                                                  Ext.Msg.alert("Notification", "Upload a valid image file");
                                                  return;
                                              }

                                              var associated_idcardfile = Ext.getCmp('associated_idcardfile').getValue();
                                              if (associated_idcardfile == '') {
                                                  Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                                  return;
                                              }

//                                    var main_image_upload = Ext.getCmp('main_image_upload').getForm();
//                                    if (main_image_upload.isValid()) {
                                              Ext.getCmp('exist_img_box').hide();
                                              winLoadMask.show();
                                              console.log('main here');
                                              addPhoto('main');
//                                    }
                                              return true;
                                          }
                                      }
                                  }]
                          }, {
                              columnWidth: 1,
                              layout: 'form',
                              height:300,
                              id: 'visitingcard_image_panel_form',
                              border: false,
                              items: [new Ext.Panel({
                                      layout: "fit",
                                      id: 'visitingcard_image_panel',
                                      tpl: new Ext.XTemplate('<div class="details-outer-events">',
                                              '<img style="width: 100%; max-width: 300px; max-height: 300px;" src="{thumpimg_src}"></img>',
                                              '</div>')
                                  }), {
                                      xtype: 'box',
                                      width: 468,
                                      height: 210,
                                      id: 'exist_thumpimg_box',
                                      autoEl: {tag: 'img', src: thumpurl, width: '468', height: 210}
                                      //autoEl: {tag: 'img', src: img, width: 'auto', height: 200}

                                  }, {
                                      xtype: 'fileuploadfield',
                                      id: 'associated_visitingcardfile',
                                      anchor: '98%',
                                      name: 'associated_visitingcardfile',
                                      allowBlank: true,
                                      buttonOnly: true,
                                      //tabIndex: 512,
                                      buttonCfg: {
                                          text: 'Upload Visiting',
                                          //iconCls:'so_upload',
                                          width: 80
                                      },
                                      validator: function (vt) {
                                          if (vt != '') {
                                              vt = vt.toLowerCase();
                                              var exp = /^.*\.(png|jpg|jpeg|gif)$/i;
                                              if (!(exp.test(vt))) {
                                                  Ext.Msg.alert("Notification", "Upload a valid image file");
                                                  return;
                                              }

                                              var associated_visitingcardfile = Ext.getCmp('associated_visitingcardfile').getValue();
                                              if (associated_visitingcardfile == '') {
                                                  Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                                  return;
                                              }

//                                    var main_image_upload = Ext.getCmp('main_image_upload').getForm();
//                                    if (main_image_upload.isValid()) {
                                              Ext.getCmp('exist_thumpimg_box').hide();
                                              winLoadMask.show();
                                              console.log('thump here');
                                              addPhoto('thump');
                                              //}
                                              return true;
                                          }
                                      }
                                  }]
                          }
                      ]
                  }, {
                      xtype: 'hidden',
                      id: 'file_name',
                      name: 'file_name'
                  }, {
                      xtype: 'hidden',
                      id: 'grzBucketName',
                      name: 'grzBucketName'
                  }, {
                      xtype: 'hidden',
                      id: 'accessKey',
                      name: 'accessKey'
                  }, {
                      xtype: 'hidden',
                      id: 'secretKey',
                      name: 'secretKey'
                  }, {
                      xtype: 'hidden',
                      id: 'bucketRegion',
                      name: 'bucketRegion'
                  },
                  {
                      xtype: 'hidden',
                      id: 'oncompleteurl',
                      name: 'oncompleteurl'
                  },
                  {
                      xtype: 'hidden',
                      id: 'img_path_db',
                      name: 'img_path_db'
                  }
              ]
          })
      ]
  });
};
var supportTicketLogs = function () {
  var __relofficrLogGridStore = new Ext.data.JsonStore({
    autoLoad: true,
    url: modURL + "&op=listROLogs",
    method: "post",
    fields: ["id","roId","roRemarks","roInterviewLink","roInterviewDate","roInterviewTime",
    "roAppointmentOrder","roCreatedOn","roCreatedBy","createdByName","roStatusName"
    ],
    remoteSort: true,
  });

  var __relofficrLogGridFilter = new Ext.ux.grid.GridFilters({
    filters: [
      {
        type: "string",
        dataIndex: "roStatusName",
      },
    ],
  });
  __relofficrLogGridFilter.remote = true;
  __relofficrLogGridFilter.autoReload = true;
  var __relofficrLogGrid = new Ext.grid.GridPanel({
    id: "gridROLogGrid",
    region: "north",
    height: 200,
    width: winsize.width * 0.38,
    frame: true,
    hidden: true,
    border: false,
    title: "Logs",
    autoScroll: true,
    store: __relofficrLogGridStore,
    sm: new Ext.grid.RowSelectionModel({
      singleSelect: true,
    }),
    viewConfig: {
      forceFit: true,
    },
    plugins: [__relofficrLogGridFilter],
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
  return __relofficrLogGrid;
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
var roSupportMainTabPanel =function(id,ojRoContact){
  var src = modURL + '&op=order_details_view&order_auto_id=0';
  var iframesrc = Application.RetalineOmni.Cache.merchantpath;
  var _leadPanel = new Ext.Panel({
      layout: 'column',
      //autoHeight:true,
      height: winsize.height*.9,
      frame: true,
      bodyStyle: {"background-color": "white"},
      title: 'RO Suppport',
      hideBorders: true,
      id: id,
      tbar: [{
              xtype: 'textfield',
              tabIndex: 602,
              vtype: 'phonespec',
              id: 'roSearchParameter',
              name: 'roSearchParameter',
              emptyText: 'Enter Mobile Number',
              allowBlank: false,
              anchor: '95%',
              maxLength: 100,
              listeners: {
                  
              }
          }, {
              xtype: 'button',
              text: 'Load',
              id: 'showBtntoms',
              iconCls: 'rollback',
              style: "padding-left: 10px;",
              handler: function () {
                  var customerPhone = Ext.getCmp('roSearchParameter').getValue();

              }
          },{
              xtype: 'button',
              text: 'Search',
              id: 'showBtnSearchms',
              iconCls: 'finascop_search_btn',
              style: "padding-left: 10px;",
              handler: function () {

              }
          }, {
              xtype: 'button',
              text: 'Call',
              tooltip: 'Call',
              hidden: true,
              icon: './resources/images/default/icons/call.png',
              id: 'merchantCall',
              iconCls: 'my-icon1',
              handler: function () {

              }
          },'->',
          {
              xtype: 'button',
              text: 'Call Failed',
              tooltip: 'Call Failed',
              hidden: true,
              iconCls: 'my-icon1',
              icon: './resources/images/default/icons/call_failed.png',
              id: 'merchantCallFail',
              handler: function () {                    
                  //merchantCallCommunicationWindow('Failed');
              }
          },'|',{
              xtype: 'button',
              text: 'Call Dropped',
              tooltip: 'Call Dropped',
              hidden: true,
              icon: './resources/images/default/icons/call_dropped.png',
              id: 'merchantCallDrop',
              handler: function () {                    
                  //merchantCallCommunicationWindow('Dropped');
              }
          },'|',{
              xtype: 'button',
              text: 'Call Completed',
              tooltip: 'Call Completed',
              hidden: true,
              icon: './resources/images/default/icons/call_completed.png',
              id: 'merchantCallCompleted',
              handler: function () {   
                  //merchantCallCommunicationWindow('Completed');           

              }
          }],
      items: [relationshipOfficerMainPanel('roOutboundPanel')],
          fbar:[{
              xtype: 'button',
              text: 'Close',
              tooltip: 'Close',
              hidden: true,
              iconCls: 'my-icon1',
              icon: './resources/images/default/icons/delete.png',
              id: 'merchantCallClose',
              handler: function () {
              }
          }],
          listeners:{
              afterrender:function(){
                  var params = (new URL(document.location)).searchParams;
                  if(params.has("phone") === true){
                  var phone = params.get("phone");
                  Ext.getCmp('roSearchParameter').setValue(phone);
                  }else if(parseInt(ojRoContact)){
                      console.log('part2');
                      Ext.getCmp('roSearchParameter').setValue(ojRoContact);
                      searchRO(ojRoContact,'indirect');
                  }else{
                      console.log('part3');
                      Ext.getCmp('roSearchParameter').setValue('');
                  }
                  if(Ext.isEmpty(Ext.getCmp('roSearchParameter').getValue())){
                      console.log('part4');
                      Ext.getCmp('roSearchParameter').setValue('');

                  }
              }
          }
  });
  return _leadPanel;
};
var searchRO = function(customerPhone,method) {
  if (!Ext.isEmpty(customerPhone)) {
      Ext.Ajax.request({
          waitMsg: 'Processing',
          method: 'POST',
          url: modURL + '&op=checkROExist',
          params: {
              customerPhone: customerPhone
          },
          success: function (response) {
          },
          failure: function (response) {
              var tmp = Ext.util.JSON.decode(response.responseText);
              Ext.MessageBox.alert('Error', tmp.msg);
          }
      });
  } else {
      Ext.MessageBox.alert('Error', "Check the data entered");
  }
};
  return {
    Cache: {},
    initRO: function () {
        var panelId = "panelRelationshipOfficer";
        var listRelationshipOfficer = Ext.getCmp(panelId);
        if (Ext.isEmpty(listRelationshipOfficer)) {
            listRelationshipOfficer = relationshipOfficerMainPanel(panelId);
          Application.UI.addTab(listRelationshipOfficer);
          listRelationshipOfficer.doLayout();
        } else {
          Application.UI.addTab(listRelationshipOfficer);
        }
      },roViewMode: function () {
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
              Ext.getCmp("gridROLogGrid").show();
              Ext.getCmp("gridROLogGrid")
              .getStore()
              .load({
                params: {
                  roId: id,
                },
              });
            }
            Ext.getCmp("relationshipOfficerPanel").doLayout();
          },
          failure: function () {
            Ext.MessageBox.alert("Error", "Error occured while sending data");
          },
        });
        Ext.getCmp("relationshipOfficerPanel").doLayout();
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
              Ext.getCmp("gridpanelforBaRealtOffcr")
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
      },scheduleInterview:function(roId){

        var updateROStatusWindow = new Ext.Window({
          id: "windowToScheduleInterview",
          iconCls: "schedule-interview",
          shadow: false,
          width: winsize.width * 0.4,
          height: winsize.height * 0.4,
          title: "Schedule Interview",
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
                          fieldLabel: "Meeting Link",
                          xtype: "textarea",
                          allowblank: false,
                          id: "roInterviewLink",
                          width: 330,
                          tabIndex: 400,
                          height: 50,
                          anchor: "95%",
                        },
                      ],
                    },
                    {
                      columnWidth: .5,
                      layout: "form",
                      frame: false,
                      border: false,
                      labelAlign: "top",
                      items: [{
                        xtype: 'datefield',
                        allowBlank: true,
                        fieldLabel: 'Date',
                        editable: false,
                        anchor: '97%',
                        tabIndex: 401,
                        minValue: new Date(),
                        id: 'roInterviewDate',
                        format: 'd/m/Y',
                        name: 'roInterviewDate'
                    }
                        
                      ],
                    },{
                      columnWidth: .5,
                      layout: "form",
                      frame: false,
                      border: false,
                      labelAlign: "top",
                      items: [{
                        xtype: 'timefield',
                        fieldLabel: 'Time',
                        id: 'roInterviewTime',
                        name: 'roInterviewTime',
                        anchor: '98%',
                        allowBlank: false,
                        tabIndex: 402,
                        minValue: '9:00 AM',
                        maxValue: '10:00 PM'
                    }                       
                      ],
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
                updateROStatusWindow.close();
              },
            },
            {
              xtype: "button",
              icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
              text: "Save",
              handler: function () {
                var roInterviewLink = Ext.getCmp("roInterviewLink").getValue();
                var roInterviewDate = Ext.getCmp("roInterviewDate").getValue();
                var roInterviewTime = Ext.getCmp("roInterviewTime").getValue();
                if (!Ext.isEmpty(roInterviewLink) && !Ext.isEmpty(roInterviewDate) && !Ext.isEmpty(roInterviewTime)) {
                  Ext.Ajax.request({
                    waitMsg: "Processing",
                    method: "POST",
                    url: modURL + "&op=roActions",
                    params: {
                      action:'schedule-interview',
                      roInterviewLink: roInterviewLink,
                      roInterviewDate: roInterviewDate,
                      roInterviewTime: roInterviewTime,
                      id:roId
                    },
                    success: function (response) {
                      var tmp = Ext.util.JSON.decode(response.responseText);
                      if (tmp.success === true) {
                        updateROStatusWindow.close();
                        Ext.getCmp("gridpanelforBaRealtOffcr").getStore().load();
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
      },uploadAppointment:function(roId,action){        
        fileS3BucketView();
        var appointmentUploadWindow = new Ext.Window({
          id: "windowToUpgradeProspect",
          iconCls: "vender-items",
          shadow: false,
          width: winsize.width * 0.4,
          height: winsize.height * 0.4,
          title: "Update Appointment Order - "+action,
          layout: "fit",
          resizable: false,
          plain: true,
          constrain: true,
          draggable: true,
          modal: true,
          items: [
            new Ext.FormPanel({
              layout: "form",
              id:"updateAppointmentOrderFormPanel",
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
                      columnWidth: .20,
                      border: false,
                      buttons: [
                          {
                              xtype: 'fileuploadfield',
                              style: 'margin-bottom:5px;',
                              id: 'fileuploadfieldAttachFileCustomers',
                              border: false,
                              anchor: '99%',
                              name: 'fileuploadfieldAttachFileCustomers',
                              allowBlank: false,
                              buttonOnly: true,
                              buttonCfg: {
                                  text: 'Upload File',
                                  border: false,
                                  width: 80
                              },
                              validator: function (v) {
                                  if (v != '')
                                  {
                                      v = v.toLowerCase();
                                      var exp = /^.*\.(pdf)$/i;
                                      if (!(exp.test(v)))
                                      {
                                        Ext.Msg.alert("Notification", "Upload a valid file of format.");
                                          return;
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
                      items: [{xtype: 'displayfield', width: 180,id: 'supporting', cls: 'fpcls', value: 'File uploaded successfully', hidden: true}]
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
                appointmentUploadWindow.close();
              },
            },
            {
              xtype: "button",
              icon: IMAGE_BASE_PATH + "/default/icons/accept.png",
              text: "Save",
              handler: function () {
                var crmRemarks = Ext.getCmp("crmRemarks").getValue();
                var appointmentFile = Ext.getCmp("s3filepath").getValue();
                if (!Ext.isEmpty(crmRemarks) && !Ext.isEmpty(appointmentFile)) {
                  Ext.Ajax.request({
                    waitMsg: "Processing",
                    method: "POST",
                    url: modURL + "&op=uploadAppointment",
                    params: {
                      action:action,
                      id: roId,
                      crmRemarks: crmRemarks,
                      appointmentFile:appointmentFile
                    },
                    success: function (response) {
                      var tmp = Ext.util.JSON.decode(response.responseText);
                      if (tmp.success === true) {
                        appointmentUploadWindow.close();
                        Ext.getCmp("gridpanelforBaRealtOffcr").getStore().load();
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
                  Ext.MessageBox.alert("Error", "Some fields are still missing.");
                }
              },
            },
          ],
          listeners: {
            load: function () {},
          },
        });
    
        appointmentUploadWindow.doLayout();
        appointmentUploadWindow.show();
        appointmentUploadWindow.center();
      },reveiwAppointment:function(roId){
        Ext.Ajax.request({
          waitMsg: "Processing",
          method: "POST",
          url: modURL + "&op=loadAppointmentLink",
          params: {            
            id:roId
          },
          success: function (response) {
            var tmp = Ext.util.JSON.decode(response.responseText);
            if (tmp.success === true) {
              Application.RelationshipOfficer.loadAppointment(roId,tmp.path);
            } else {
              Ext.MessageBox.alert("Error", tmp.msg);
            }
          },
          failure: function (response) {
            var tmp = Ext.util.JSON.decode(response.responseText);
            Ext.MessageBox.alert("Error", tmp.msg);
          },
        });
      },loadAppointment : function(roId,path){
        var extension = path.split(".").pop();
        var embedhtml ;
        switch(extension){
            case 'pdf':
                path = "https://mozilla.github.io/pdf.js/web/viewer.html?file="+path;
                embedhtml = '<iframe src="'+path+'" width="100%" height="100%" style="border: none;"></iframe>';
                break;
            case 'docx':
                path = "https://docs.google.com/viewer?url="+path+"&embedded=true";
                embedhtml = '<iframe src="'+path+'" width="100%" height="100%" style="border: none;"></iframe>';
                break;
            default:                   
                path = path;
                embedhtml = '<embed src="'+path+'" width="100%" height="100%" style="border: none;">';
                break;
        }
        var win_id = "view_documents";
        var view_documents_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(view_documents_window)) {
            view_documents_window = new Ext.Window({
                id: win_id,
                title: 'View Details',
                layout: 'fit',
                width: winsize.width * 0.7,
                height: 500,
                iconCls: 'icon-add-table',
                plain: false,
                constrain: true,
                modal: true,
                frame: true,
                resizable: true,
                items: [{
                        region: 'center',
                        border: false,
                        html: embedhtml
                    }],
                    fbar:[{
                      xtype: 'button',
                      text: 'Verify',
                      tabIndex: 603,
                      iconCls: 'my-icon31',
                      style: "padding-left: 10px;",
                      handler: function () {
                        Application.RelationshipOfficer.roActions(Application.RelationshipOfficer.Cache.roId,'verify-appointment');  
                        view_documents_window.close();
                      }
                  }, {
                      xtype: 'button',
                      text: 'Rebut',
                      iconCls: 'reject',
                      style: "padding-left: 10px;",
                      handler: function () {
                        Application.RelationshipOfficer.reasonForAction(Application.RelationshipOfficer.Cache.roId,'rebut-appointment');
                        view_documents_window.close();
                      }
                  }]
            });

        }

        view_documents_window.doLayout();
        view_documents_window.show();
        view_documents_window.center();
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
                    maxValue: new Date(),
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
                              Ext.getCmp("gridpanelforBaRealtOffcr").getStore().load();
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
  },updateRODetails: function(menu_id, img_url,thumpurl){

    console.log('menu_id', menu_id);
    if (menu_id == '')
        var tit = 'Create RO Details';
    else
        var tit = 'Edit RO Details';
    var resultWindow = new Ext.Window({
        id: 'windowNewsfeedSliderWindow',
        title: tit,
        //iconCls: 'vender-items',
        shadow: false,
        width: 950,
        height: 650,
        layout: 'border',
        resizable: false,
        plain: true,
        constrain: true,
        draggable: true,
        modal: true,
        closable: false,
        items: [roDetailUpdateForm(), new Ext.Panel({
                layout: "fit",
                region: 'center',
                width: 620,
                border: false,
                items: [roPhotoUploadForm(img_url,thumpurl)]
            })],
        buttons: [
            {
                text: 'Cancel',
                id: 'cms_newsfeedcancel_btn',
                icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                iconCls: 'my-icon1',
                tabIndex: 505,
                handler: function () {
                    resultWindow.close();
                }
            }, {
                text: "Save",
                id: 'cms_newsfeedsave_btn',
                icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                iconCls: 'thes_save',
                tabIndex: 504,
                handler: function () {
                    var edit_status;                    


                    if (menu_id == '')
                        edit_status = 0;
                    else
                        edit_status = 1;
                    Ext.Ajax.request({
                        url: modURL + '&op=saveRODetails',
                        method: 'POST',
                        params: {
                            edit_status:edit_status,
                            id: menu_id,
                            roResourceId: Ext.getCmp('roResourceId').getValue(),                                
                            roEmailId: Ext.getCmp('roEmailId').getValue(),
                            roMobile: Ext.getCmp('roMobile').getRawValue(),
                            roImeiNo: Ext.getCmp('roImeiNo').getValue(),                                
                            roCourierWaybill: Ext.getCmp('roCourierWaybill').getRawValue(),
                            roCourierDate:Ext.getCmp('roCourierDate').getValue(),
                            roLicenceNo:Ext.getCmp('roLicenceNo').getRawValue(),
                            roAadhaar: Ext.getCmp('roAadhaar').getValue(),
                            roIdcard:Ext.getCmp('aws_file_locationidcard').getValue(),
                            roVisitingcard:Ext.getCmp('aws_file_locationvisitingcard').getValue(),
                        },
                        success: function (response) {
                            var tmp = Ext.decode(response.responseText);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.example.msg('Success', tmp.message);    
                                resultWindow.close();
                                Ext.getCmp("gridpanelforBaRealtOffcr").getStore().load();

                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.Msg.alert("Notification.", tmp.message);
                            } else if (tmp.success === true && tmp.img_valid === false) {
                                Ext.Msg.alert("Notification.", tmp.message);
                            } else {
                                Ext.Msg.alert("Notification", "Please enter all required fields");
                            }
                        },
                        failure: function (response) {
                            var tmp = Ext.util.JSON.decode(response.responseText);
                            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                        }

                    })



                }
            }
        ],
        listeners: {
            afterrender: function () {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var upload_type = 1;
                winLoadMask = new Ext.LoadMask(Ext.getCmp('windowNewsfeedSliderWindow').getEl());
                winLoadMask.msg = 'Please wait...';
                if (menu_id > 0) {
                    Ext.getCmp('main_image_upload').getForm().load({
                        waitTitle: 'Please Wait',
                        waitMsg: 'Loading...',
                        url: modURL + '&op=get_img_s3_details',
                        params: {
                            rid: menu_id,
                            apikey: _SESSION.apikey,
                            tstamp: t_stamp
                        }
                    });
                    Ext.getCmp('formpanelRelationshipOfficer').getForm().load({
                      waitTitle: 'Please Wait',
                      waitMsg: 'Loading...',
                      url: modURL + '&op=loadRODetails',
                      params: {
                          roId: menu_id,
                          apikey: _SESSION.apikey,
                          tstamp: t_stamp
                      }
                  });

                }
            }
        }

    });
    resultWindow.doLayout();
    resultWindow.show();
    resultWindow.center();
  },completeOnboarding:function(roId){
    Ext.Ajax.request({
      url: modURL + '&op=completeROProcess',
      method: 'POST',
      params: {
        roId:roId
      },
      success: function (response) {
          var tmp = Ext.decode(response.responseText);
          if (tmp.success === true && tmp.valid === true) {
              Application.example.msg('Success', tmp.message);    
              //resultWindow.close();
              Ext.getCmp("gridpanelforBaRealtOffcr").getStore().load();

          } else if (tmp.success === true && tmp.valid === false) {
              Ext.Msg.alert("Notification.", tmp.message);
          } else if (tmp.success === false && tmp.img_valid === false) {
              Ext.Msg.alert("Notification.", tmp.message);
          } else {
              Ext.Msg.alert("Notification", "Please enter all required fields");
          }
      },
      failure: function (response) {
          var tmp = Ext.util.JSON.decode(response.responseText);
          Ext.MessageBox.alert('Notification', 'Please enter all required fields');
      }

  })
  },outboundCallsRO:function(roMobile){
    var panelId = 'outrosupportmainpanel';            
    var reswindow = new Ext.Window({
        title: Application.OutboundCalls.Cache.jobTitle,
        id:'outrosupportmainWindow',
        modal: true,
        constrain: true,
        layout: 'fit',
        maximized: true,
        floating: true,
        shadow: false,
        closable:false,
        items: [roSupportMainTabPanel(panelId,roMobile)],
        buttons: [
        ]
    });
    reswindow.doLayout();
    reswindow.show();
    reswindow.center();
  },reasonForAction:function(roId,action){
    var updateROStatusWindow = new Ext.Window({
      id: "windowToUpdateReason",
      shadow: false,
      width: winsize.width * 0.4,
      height: winsize.height * 0.4,
      title: "Reason for the action",
      layout: "fit",
      resizable: false,
      plain: true,
      constrain: true,
      draggable: true,
      modal: true,
      items: [
        new Ext.FormPanel({
          layout: "form",
          id: "updateROActionReasonFormPanel",
          height: 75,
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
                      fieldLabel: "Reason",
                      xtype: "textarea",
                      allowblank: false,
                      id: "roActionReason",
                      width: 330,
                      tabIndex: 400,
                      height: 50,
                      anchor: "95%",
                    },
                  ],
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
            updateROStatusWindow.close();
          },
        },
        {
          xtype: "button",
          icon: IMAGE_BASE_PATH + "/default/icons/disk.png",
          text: "Save",
          handler: function () {
            var roActionReason = Ext.getCmp("roActionReason").getValue();
            if (!Ext.isEmpty(roActionReason)) {
              Ext.Ajax.request({
                waitMsg: "Processing",
                method: "POST",
                url: modURL + "&op=roActions",
                params: {
                  action:action,
                  roActionReason: roActionReason,
                  id:roId
                },
                success: function (response) {
                  var tmp = Ext.util.JSON.decode(response.responseText);
                  if (tmp.success === true) {
                    updateROStatusWindow.close();
                    Ext.getCmp("gridpanelforBaRealtOffcr").getStore().load();
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
  }
     };
})();
