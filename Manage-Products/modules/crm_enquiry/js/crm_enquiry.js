Application.Crm_Enquiry = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=crm_enquiry';
    var recs_per_page = 16;

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var crmEnquiryGridStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getEnquiryDetails',
            fields: ['crme_id', 'crme_name', 'crme_mobile', 'crme_email', 'crmm_IsActive'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'crme_id',
            remoteSort: true,
             autoLoad: true,
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridpanelMarketingEnquiryList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        return store;
    };
    var crmEnquiryGridFun = function () {
        var _gridStore = crmEnquiryGridStore();
        var _enquiryGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'crme_name'
                },
                {
                    type: 'string',
                    dataIndex: 'crme_mobile'
                },
                {
                    type: 'string',
                    dataIndex: 'crme_email'
                }

            ]
        });
        _enquiryGridFilter.remote = true;
        _enquiryGridFilter.autoReload = true;
        var _crmEnquiryGrid = new Ext.grid.GridPanel({
            id: 'gridpanelMarketingEnquiryList',
            store: _gridStore,
            region: 'center',
            frame: true,
            plugins: [_enquiryGridFilter],
            border: false,
            loadMask: true,
            columns: [
                {
                    header: 'Name',
                    dataIndex: 'crme_name',
                    sortable: true,
                    width:200
                },
                {
                    header: 'Contact Number',
                    dataIndex: 'crme_mobile',
                    sortable: true,
                    width:100
                },
                {
                    header: 'Email Address',
                    dataIndex: 'crme_email',
                    sortable: true,
                    width:200
                },
                {
                    header: 'Actions',
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    items: [
                        {
                            iconCls: 'move-to-contactss',
                            tooltip: 'Move to Contacts',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                console.log(record);
                                var _active = record.data.crmm_IsActive;
                                if (_active == 1) {
                                    Ext.MessageBox.confirm('Confirm', 'Do you want to move this as contact?', function (btn, text) {
                                        if (btn == 'yes') {
                                            enquiryToContact(record.get('crme_id'), record.get('crmm_IsActive'), record.get('crme_IsOrganization'));
                                        }
                                    });
                                }
                            }
                        },
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Remove',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var _active = record.data.crmm_IsActive
                                Ext.MessageBox.confirm('Confirm', 'Are you sure want to remove this?', function (btn, text) {
                                    if (btn == 'yes') {
                                        removeEnquiry(record.get('crme_id'));
                                    }
                                });
                            }
                        }
                    ]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
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
                    selectionchange: gridSelectionChangeEnquiry
                }
            }),
            listeners: {
                cellclick: function (grid, rowIndex, columnIndex, e) {
                    if (columnIndex != 5) {
                        var record = grid.getStore().getAt(rowIndex);
                        var record = record.data;
                        Ext.getCmp('panelMarketingEnquiryHtmlview').show();
                        var ID = record.crme_id;
                        Application.Crm_Enquiry.Cache.crme_id = ID;
                        Application.Crm_Enquiry.ViewMode(Application.Crm_Enquiry.Cache.crme_id);
                    }
                },
                viewready: updatePagination
            }
        });
        return _crmEnquiryGrid;
    };
    var enquiryToContact = function (id, activeStatus, type) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=convertToContact',
            params: {
                crme_id: id,
                status: activeStatus,
                type: type
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {
                    //Ext.MessageBox.alert("Success", "Enquiry converted to contact", function (btn) { 
                    Ext.getCmp('gridpanelMarketingEnquiryList').getStore().reload();
                    // });
                }else{
                    Ext.MessageBox.alert('Error', "Invalid data");
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };
    var removeEnquiry = function (id) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=removeEnquiry',
            params: {
                crme_id: id,
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {

                    Ext.getCmp('gridpanelMarketingEnquiryList').getStore().reload();

                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };
    var panelEnquiry = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '?module=Crm_Enquiry&op=loadEnquiryData&crme_id=' + Application.Crm_Enquiry.Cache.crme_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var panel = new Ext.Panel({
            id: 'panelMarketingEnquiryHtmlview',
            region: 'east',
            title: 'Enquiry Details',
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
                    html: '<iframe id="downloadIframeEnquiry" name="downloadIframeEnquiry" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' + src + '"; ></iframe>'
                }],
            listeners: {
                'afterrender': function (component) {
                    Ext.getCmp('gridpanelMarketingEnquiryList').getSelectionModel().selectRow(0);
                    Ext.getCmp('panelMarketingEnquiryHtmlview').show();
                }
            }
        });
        return panel;
    };
    var gridSelectionChangeEnquiry = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMarketingEnquiryList').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMarketingEnquiryList').getSelectionModel().getSelections()[0].data.crme_id;
            console.log('Inside selectionchange', ID);
            Application.Crm_Enquiry.ViewMode(ID);
        } else {
            Application.Crm_Enquiry.ViewMode('');
        }
    };
    var marketingEnquiryPanel = function (id) {
        var _enquiryPanel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            title: 'Enquiries',
            hideBorders: true,
            id: id,
            items: [crmEnquiryGridFun(), panelEnquiry()]
        });
        return _enquiryPanel;
    };

    return {
        Cache: {},
        initEnquiry: function () {
            var panelId = 'enquirypanel';
            var enquiry_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(enquiry_panel)) {
                enquiry_panel = marketingEnquiryPanel(panelId);
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
            Ext.get('downloadIframeEnquiry').dom.src = modURL + '&op=loadEnquiryData&crme_id=' + entry_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        }
    }
}();