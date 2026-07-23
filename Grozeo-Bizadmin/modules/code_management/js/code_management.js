Application.CodeManagement = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=code_management';
    var recs_per_page = 21;

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    function generateRandomCode(length) {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        return result;
    }
    var codeMgmtGridStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getCodeMagmtDetails',
            fields: ['id', 'invitationCode', 'codeType', 'validity', 'status','referrerTypeName','crpr_CreatedOn','crpr_ExpiredOn','referrerName','statusName','codeTypeName','validityName'],
            totalProperty: 'totalCount',
            root: 'data',
            //id: 'id',
            //remoteSort: true,
            autoLoad: true,
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridpanelMarketingCodeMagmtList').getSelectionModel().selectRow(0);
                    }
                },
                beforeload: function (store, e) {
                    store.removeAll();
                    //this.baseParams.codeType = -1; 
                }
            }
        });
        return store;
    };
    var codeMgmtGridFun = function () {
        var _gridStore = codeMgmtGridStore();
        var _enquiryGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'invitationCode'
                },{
                    type: 'string',
                    dataIndex: 'referrerTypeName'
                },
                {
                    type: 'string',
                    dataIndex: 'codeTypeName'
                },
                {
                    type: 'string',
                    dataIndex: 'validityName'
                }

            ]
        });
        _enquiryGridFilter.remote = true;
        _enquiryGridFilter.autoReload = false;
        var _codeMgmtGrid = new Ext.grid.GridPanel({
            id: 'gridpanelMarketingCodeMagmtList',
            store: _gridStore,
            region: 'center',
            frame: true,
            plugins: [_enquiryGridFilter],
            border: false,
            loadMask: true,
            columns: [
                {
                    header: 'Code',
                    dataIndex: 'invitationCode',
                    sortable: true,
                },
                {
                    header: 'Code Type',
                    dataIndex: 'codeTypeName',
                    sortable: true,
                },
                {
                    header: 'Validity',
                    dataIndex: 'validityName',
                    sortable: true,
                },{
                    header: 'Referrer Type',
                    dataIndex: 'referrerTypeName',
                    sortable: true,
                },{
                    header: 'Referrer',
                    dataIndex: 'referrerName',
                    sortable: true,
                },{
                    header: 'Created Date',
                    dataIndex: 'crpr_CreatedOn',
                    sortable: true,
                },{
                    header: 'Expiry Date',
                    dataIndex: 'crpr_ExpiredOn',
                    sortable: true,
                },{
                    header: 'Status',
                    dataIndex: 'statusName',
                    sortable: true,
                }
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            tbar:[
                {
                  text: "Create Code",
                  tooltip: "Create Code",
                  icon: "./resources/images/default/icons/add.png",
                  handler: function () {         
                    var code = generateRandomCode(6).toUpperCase();
                    Application.CodeManagement.addCodes(code,0);           
                  }
                },'-',
                {
                    text: "Search Codes",
                    tooltip: "Search Codes",
                    icon: "./resources/images/default/icons/search.png",
                    handler: function () {  
                        Application.CodeManagement.searchCodes();       
                    }
                  }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: 'No records to display'
            }),
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangeCodeMagmt
                }
            }),
            listeners: {
                cellclick: function (grid, rowIndex, columnIndex, e) {
                    if (columnIndex != 5) {
                        var record = grid.getStore().getAt(rowIndex);
                        var record = record.data;
                        Ext.getCmp('panelMarketingCodeMagmtHtmlview').show();
                        var ID = record.id;
                        Application.CodeManagement.Cache.id = ID;
                        Application.CodeManagement.ViewMode(Application.CodeManagement.Cache.id);
                    }
                },
                viewready: updatePagination
            }
        });
        return _codeMgmtGrid;
    };
    var enquiryToContact = function (id, activeStatus, type) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=convertToContact',
            params: {
                id: id,
                status: activeStatus,
                type: type
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {
                    //Ext.MessageBox.alert("Success", "CodeMagmt converted to contact", function (btn) { 
                    Ext.getCmp('gridpanelMarketingCodeMagmtList').getStore().reload();
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
    var removeCodeMagmt = function (id) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=removeCodeMagmt',
            params: {
                id: id,
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {

                    Ext.getCmp('gridpanelMarketingCodeMagmtList').getStore().reload();

                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };
    var panelCodeMagmt = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '?module=code_management&op=loadCodeMagmtData&id=' + Application.CodeManagement.Cache.id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var panel = new Ext.Panel({
            id: 'panelMarketingCodeMagmtHtmlview',
            region: 'east',
            title: 'Code Management Details',
            frame: false,
            border: true,
            bodyStyle: {
                "background-color": "white"
            },
            width: winsize.width * 0.4,
            defaults: {
                layout: 'fit',
                autoScroll: true,
                frame: false
            },
            cls: 'left_side_panel',
            items: [{
                    html: '<iframe id="downloadIframeCodeMagmt'+'" name="downloadIframeCodeMagmt" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' + src + '"; ></iframe>'
                }],
            listeners: {
                'afterrender': function (component) {
                    Ext.getCmp('gridpanelMarketingCodeMagmtList').getSelectionModel().selectRow(0);
                    Ext.getCmp('panelMarketingCodeMagmtHtmlview').show();
                }
            }
        });
        return panel;
    };
    var gridSelectionChangeCodeMagmt = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMarketingCodeMagmtList').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMarketingCodeMagmtList').getSelectionModel().getSelections()[0].data.id;
            console.log('Inside selectionchange', ID);
            Application.CodeManagement.ViewMode(ID);
        } else {
            Application.CodeManagement.ViewMode('');
        }
    };
    var marketingCodeMagmtPanel = function (id) {
        var _enquiryPanel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            title: 'Code Management',
            hideBorders: true,
            id: id,
            items: [codeMgmtGridFun(), panelCodeMagmt()]
        });
        return _enquiryPanel;
    };
    var referrerTypeStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + '&op=storeForReferreType',
        method: 'post',
        fields: ['id', 'name']
    });
    var referrerStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + '&op=storeForReferrer',
        method: 'post',
        fields: ['id', 'name']
    });
    var CodeMgmtForm = function (code,id) {
        var _codeMgmtFormPanel = new Ext.form.FormPanel({
            id: 'formpanelCodeMgmt',
            frame: false,
            border: false,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            bodyStyle: {"background-color": "F1F1F1", "padding": "5px"},
            layout: 'column',
            items: [{
                    xtype: 'spacer',
                    height: 10
                }, {
                    xtype: 'hidden', id: 'id', name: 'id'
                }, {
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Code',
                            emptyText: 'Code',
                            id: 'invitationCode',
                            name: 'invitationCode',
                            allowBlank: false,
                            editable:false,
                            anchor: '99%',
                            tabIndex: 800,
                            value: code
                        }]
                },{
                    layout: 'form',
                    columnWidth: .25,
                    frame: false,
                    border: false,
                    labelAlign: 'top',
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Code Type',
                            emptyText: 'Choose Code Type',
                            id: 'codeType',
                            name: 'codeType',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: new Ext.data.JsonStore({
                                fields: ['id', 'name'],
                                data: [{id: '1', name: 'Referral'}, 
                                   // {id: '2', name: 'Invitation'}, {id: '3', name: 'Conversion'}
                                ]
                            }),
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'name',
                            valueField: 'id',
                            hiddenName: 'codeType',
                            tabIndex: 801,
                            listeners: {
                                select: function () {
                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    frame: false,
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Validity',
                            emptyText: 'Choose Validity',
                            id: 'validity',
                            name: 'validity',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: new Ext.data.JsonStore({
                                fields: ['id', 'name'],
                                data: [{id: '1', name: 'Single'}, {id: '2', name: 'Multiple'}]
                            }),
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'name',
                            valueField: 'id',
                            hiddenName: 'validity',
                            tabIndex: 802,
                            listeners: {
                                select: function (combo, record) {

                                }
                            }
                        }]
                },{
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                        xtype: 'combo',
                        fieldLabel: 'Referrrer Type',
                        emptyText: 'Choose Referrrer Type',
                        id: 'referrerType',
                        name: 'referrerType',
                        labelStyle: mandatory_label,
                        allowBlank: false,
                        mode: 'local',
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        anchor: '97%',
                        store: referrerTypeStore,
                        triggerAction: 'all',
                        minChars: 2,
                        displayField: 'name',
                        valueField: 'id',
                        hiddenName: 'referrerType',
                        tabIndex: 803,
                        listeners: {
                            select: function (combo, record) {
                                var type = record.data.id;
                                console.log('type', type);
                                Ext.getCmp('referrerId').reset();
                                if(Ext.getCmp('referrerType').getValue() > 0){
                                    Ext.getCmp('referrerId').show();
                                    Ext.getCmp('referrerName').hide();
                                    Ext.getCmp('referrerId').getStore().load({
                                        params: {
                                            type: Ext.getCmp('referrerType').getValue()
                                        }
                                    });
                                }else{
                                    Ext.getCmp('referrerId').hide();
                                    Ext.getCmp('referrerName').show();
                                }
                                

                            }
                        }
                    }]

                },{
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                        hidden:true,
                        xtype: 'textfield',
                        fieldLabel: 'Referrrer',
                        emptyText: 'Choose Referrrer',
                        id: 'referrerName',
                        name: 'referrerName',
                        anchor: '97%',
                        tabIndex: 804,
                        listeners: {
                            select: function () {

                            }
                        }
                    }]

                },{
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                        hidden:true,
                        xtype: 'combo',
                        fieldLabel: 'Referrrer',
                        emptyText: 'Choose Referrrer',
                        id: 'referrerId',
                        name: 'referrerId',
                        mode: 'local',
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        anchor: '97%',
                        store: referrerStore,
                        triggerAction: 'all',
                        minChars: 2,
                        displayField: 'name',
                        valueField: 'id',
                        hiddenName: 'referrerId',
                        tabIndex: 804,
                        listeners: {
                            select: function () {

                            }
                        }
                    }]

                },
                {
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                        xtype: 'datefield',
                        fieldLabel: 'Expiry Date',
                        id: 'crpr_ExpiredOn',
                        name: 'crpr_ExpiredOn',
                        anchor: '98%',
                        allowBlank: false,
                        tabIndex: 805,
                        format: 'd-m-Y',
                        minValue: new Date(),
                        value: new Date(new Date().setFullYear(new Date().getFullYear() + 1))
                    }]

                },{
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                        xtype: 'timefield',
                        fieldLabel: 'Time',
                        id: 'crpr_ExpiredTime',
                        name: 'crpr_ExpiredTime',
                        anchor: '98%',
                        allowBlank: false,
                        format: "H:i",
                        tabIndex: 806,
                    }]

                },{
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [mkCombo({
                        type: STATUS_COMBO_DATA,
                        value: "id",
                        display: "text",
                        name: "status",
                        fieldLabel: "Status",
                        emptyText: "Set status..",
                        editable: false,
                        typeAhead: false,
                        tabIndex: 806,
                        id: "status",
                      })]

                },
                {
                    layout: 'form',
                    columnWidth: .93,
                    labelAlign: 'left',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'spacer',
                            height: 5
                        }]

                },{
                    layout: 'form',
                    columnWidth: .80,                    
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    labelAlign: 'right',
                    labelWidth: 250,
                    items: [{
                        xtype: "checkbox",
                        checked: false,                        
                        id: "blockMerchant",
                        name: "blockMerchant",
                        inputValue: 1,                        
                        tabIndex: 523,
                        fieldLabel: "Block Merchant admin access after store creation",
                        listeners: {
                            check: function (checkbox, checked) {
                                if (checked == true) {
                                    Ext.getCmp("codePlanType").enable();
                                }else{
                                    Ext.getCmp("codePlanType").reset();
                                    Ext.getCmp("codePlanType").disable();
                                }
                            },
                          }
                      }]
                },{
                    layout: 'form',
                    columnWidth: 1,
                    labelAlign: 'left',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                        xtype: 'radiogroup',
                        anchor: '98%',
                        disabled: true,
                        id: 'codePlanType',
                        tabIndex: 805,
                        items: [{
                            boxLabel: 'Show ‘Plan Upgrade UI’ after Store Creation',
                            name: 'codePlanType',
                            inputValue: '1',
                            labelWidth: 220,
                            anchor: '95%',
                            tabIndex: 20
                        }, {
                            boxLabel: 'Show ‘Pending Actions UI’ with Plan Upgrade',
                            name: 'codePlanType',
                            inputValue: '2',
                            labelWidth: 220,
                            anchor: '95%',
                            tabIndex: 25
                        }]
                }]
                },{
                    layout: 'form',
                    columnWidth: .93,
                    labelAlign: 'left',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'spacer',
                            height: 5
                        }]

                }
            ],
            listeners: {
                afterrender: function () {

                }
            }
        });
        return _codeMgmtFormPanel;
    };
    return {
        Cache: {},
        initCodeMagmt: function (type) {            
            var panelId = 'codeMgmtPanel';
            var enquiry_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(enquiry_panel)) {
                enquiry_panel = marketingCodeMagmtPanel(panelId);
                Application.UI.addTab(enquiry_panel);
                enquiry_panel.doLayout();
            } else {
                Application.UI.addTab(enquiry_panel);
                enquiry_panel.doLayout();
            }
        },
        ViewMode: function () {
            var entry_id = arguments[0];
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var apikey = _SESSION.apikey;
            Ext.get('downloadIframeCodeMagmt').dom.src = modURL + '&op=loadCodeMagmtData&id=' + entry_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        },addCodes: function (code,poId) {
            var codeMgmtForm = CodeMgmtForm(code,poId);
            var codeMgmtWindow = new Ext.Window({
                id: "windowCodeMgmt",
                title: 'Create Codes',
                shadow: false,
                height: 560,
                width: 800,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: false,
                closable: true,
                items: [codeMgmtForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 808,
                        handler: function () {
                            Ext.getCmp('windowCodeMgmt').close();
                        }
                    },
                    {
                        text: 'Save',
                        tabIndex: 807,
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            var store_form = Ext.getCmp('formpanelCodeMgmt').getForm();
                            if (store_form.isValid()) {
                                store_form.submit({
                                    url: modURL,
                                    waitMsg: 'Saving Details....',
                                    waitTitle: 'Please Wait...',
                                    params: {
                                        op: 'saveCodes',
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp,
                                    },
                                    success: function (response, action) {
                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success === true) {
                                            Ext.getCmp('windowCodeMgmt').close();
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('gridpanelMarketingCodeMagmtList').getStore().load();

                                        } else if (tmp.success === false) {
                                            Ext.Msg.alert("Notification.", tmp.msg);
                                            Ext.getCmp('windowCodeMgmt').close();
                                        } else {
                                            Ext.Msg.alert("Error", tmp.msg);
                                            Ext.getCmp('windowCodeMgmt').close();
                                        }
                                    },
                                    failure: function (elm, conf, action) {
                                        if (conf.failureType === 'server') {
                                            var result = Ext.decode(conf.response.responseText);
                                            console.log('result', result);
                                            Ext.Msg.alert('Error', result.msg);
                                        } else {
                                            Ext.MessageBox.alert('Error', 'Check the required fields');
                                        }
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert('Notification', 'Check the required fields.');
                            }

                        }
                    }
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var deliveryrulesForm = Ext.getCmp('formpanelCodeMgmt').getForm();
                Ext.Ajax.request({
                    params: {
                        id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=deliveryrules_form_load',
                    waitMsg: 'Loading...',
                    method: 'POST',
                    success: function (res) {
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
            codeMgmtWindow.doLayout();
            codeMgmtWindow.show();
            codeMgmtWindow.center();
        },searchCodes: function(){            
            var searchCodeMgmtWindow = new Ext.Window({
                id: "windowSearchCodeMgmt",
                title: 'Search Codes',
                shadow: false,
                height: 220,
                width: 800,
                modal: true,
                layout: 'column',
                autoHeight: true,
                resizable: false,
                closable: true,
                items: [{
                    layout: 'form',
                    columnWidth: .25,
                    frame: false,
                    border: false,
                    labelAlign: 'top',
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Code Type',
                            emptyText: 'Choose Code Type',
                            id: 'codeType',
                            name: 'codeType',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: new Ext.data.JsonStore({
                                fields: ['id', 'name'],
                                data: [{id: '1', name: 'Referral'}, 
                                    //{id: '2', name: 'Invitation'}, {id: '3', name: 'Conversion'}
                                ]
                            }),
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'name',
                            valueField: 'id',
                            hiddenName: 'codeType',
                            tabIndex: 801,
                            listeners: {
                                select: function () {
                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    frame: false,
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Validity',
                            emptyText: 'Choose Validity',
                            id: 'validity',
                            name: 'validity',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: new Ext.data.JsonStore({
                                fields: ['id', 'name'],
                                data: [{id: '1', name: 'Single'}, {id: '2', name: 'Multiple'}]
                            }),
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'name',
                            valueField: 'id',
                            hiddenName: 'validity',
                            tabIndex: 802,
                            listeners: {
                                select: function (combo, record) {

                                }
                            }
                        }]
                },{
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                        xtype: 'combo',
                        fieldLabel: 'Referrrer Type',
                        emptyText: 'Choose Referrrer Type',
                        id: 'referrerType',
                        name: 'referrerType',
                        labelStyle: mandatory_label,
                        allowBlank: false,
                        mode: 'local',
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        anchor: '97%',
                        store: referrerTypeStore,
                        triggerAction: 'all',
                        minChars: 2,
                        displayField: 'name',
                        valueField: 'id',
                        hiddenName: 'referrerType',
                        tabIndex: 803,
                        listeners: {
                            select: function (combo, record) {
                                var type = record.data.id;
                                console.log('type', type);
                                Ext.getCmp('referrerId').reset();
                                Ext.getCmp('referrerId').getStore().load({
                                    params: {
                                        type: Ext.getCmp('referrerType').getValue()
                                    }
                                });

                            }
                        }
                    }]

                },{
                    layout: 'form',
                    columnWidth: .25,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                        xtype: 'combo',
                        fieldLabel: 'Referrrer',
                        emptyText: 'Choose Referrrer',
                        id: 'referrerId',
                        name: 'referrerId',
                        mode: 'local',
                        typeAhead: true,
                        forceSelection: true,
                        editable: true,
                        anchor: '97%',
                        store: referrerStore,
                        triggerAction: 'all',
                        minChars: 2,
                        displayField: 'name',
                        valueField: 'id',
                        hiddenName: 'referrerId',
                        tabIndex: 804,
                        listeners: {
                            select: function () {

                            }
                        }
                    }]

                }],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 808,
                        handler: function () {
                            Ext.getCmp('windowSearchCodeMgmt').close();
                        }
                    },
                    {
                        text: 'Search',
                        tabIndex: 807,
                        icon: IMAGE_BASE_PATH + '/default/icons/search.png',
                        handler: function () {
                            Ext.getCmp('gridpanelMarketingCodeMagmtList').getStore().load({
                                params:{
                                    codeType:Ext.getCmp('codeType').getValue(),
                                    validity:Ext.getCmp('validity').getValue(),
                                    referrerType:Ext.getCmp('referrerType').getValue(),
                                    referrerId:Ext.getCmp('referrerId').getValue()
                                }
                            });
                            searchCodeMgmtWindow.close();
                        }
                    }
                ]
            });
            
            searchCodeMgmtWindow.doLayout();
            searchCodeMgmtWindow.show();
            searchCodeMgmtWindow.center();
        }
    }
}();