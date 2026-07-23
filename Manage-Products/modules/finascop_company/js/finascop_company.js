Application.Finascop_Company = function () {

    var recs_per_page = 12;
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=finascop_company';

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var companyStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCompany',
                method: 'post'
            }),
            fields: ['comp_id', 'comp_name', 'comp_shortname', 'cmp_Typ', 'cmp_PAN',
                'comp_Ph', 'comp_Fax',
                'cmp_status', 'auditing_company', 'comp_ReferenceId', 'comp_gstno', 'comp_fssaino', 'comp_dlno1'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        store.setDefaultSort('comp_name', 'ASC');
        return store;
    };

    var companyGrid = function (id) {
        var company_store = companyStore();
        //var action = companyGridAction();
        var company_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'comp_name'
                },
//                {
//                    type: 'string',
//                    dataIndex: 'comp_shortname'
//                }, 
                {
                    type: 'string',
                    dataIndex: 'cmp_PAN'
                },
                {
                    type: 'string',
                    dataIndex: 'comp_Ph'
                },
//                {
//                    type: 'string',
//                    dataIndex: 'comp_ReferenceId'
//                }, 
                {
                    type: 'string',
                    dataIndex: 'comp_Fax'
                }, {
                    type: 'string',
                    dataIndex: 'comp_gstno'
                }, {
                    type: 'string',
                    dataIndex: 'comp_fssaino'
                }, {
                    type: 'string',
                    dataIndex: 'comp_dlno1'
                }]
        });
        company_filter.remote = true;
        company_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: company_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Company',
            //iconCls: 'company',
            plugins: [company_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Company Name',
                    id: 'company_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'comp_name',
                    tooltip: 'Company Name'
                },
//                {
//                    header: 'Short Name',
//                    sortable: true,
//                    dataIndex: 'comp_shortname',
//                    tooltip: 'Short Name'
//                }, 
                {
                    header: 'PAN',
                    sortable: true,
                    dataIndex: 'cmp_PAN',
                    tooltip: 'PAN'
                }, {
                    header: 'Contact No.1',
                    sortable: true,
                    dataIndex: 'comp_Ph',
                    tooltip: 'Contact No.'
                },
//                {
//                    header: 'Reference ID',
//                    sortable: true,
//                    width: 175,
//                    dataIndex: 'comp_ReferenceId',
//                    tooltip: 'Reference ID'
//                }, 
                {
                    header: 'Contact No.2',
                    sortable: true,
                    dataIndex: 'comp_Fax',
                    tooltip: 'Contact No.2'
                }, {
                    header: 'GST / VAT',
                    sortable: true,
                    dataIndex: 'comp_gstno',
                    tooltip: 'GST / VAT'
                }, {
                    header: 'FSSAI / EORI',
                    sortable: true,
                    dataIndex: 'comp_fssaino',
                    tooltip: 'FSSAI / EORI'
                }, {
                    header: 'Reg Number',
                    sortable: true,
                    dataIndex: 'comp_dlno1',
                    tooltip: 'Reg Number'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            companyActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }

                /*{
                 xtype: 'actioncolumn',
                 header: 'Action',
                 hideable: false,
                 sortable: false,
                 groupable: false,
                 iconCls: 'downarrow',
                 tooltip: 'Action',
                 items: [{
                 iconCls: 'finascop_edit',
                 tooltip: 'Edit Company Details',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 companyDetails(record.get('comp_id'));
                 }
                 },
                 {
                 getClass: function (v, meta, rec) {
                 if (rec.get('cmp_status') == 'Active') {
                 this.items[1].tooltip = 'Deactivate Company';
                 return 'now_active';
                 } else {
                 this.items[1].tooltip = 'Activate Company';
                 return 'now_inactive';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this company?', function (btn, text) {
                 if (btn == 'yes') {
                 console.log('Company ID : ' + record.get('comp_id'));
                 changeCompanyStatus(record.get('comp_id'), record.get('cmp_status'));
                 }
                 });
                 
                 }
                 },
                 {
                 iconCls: 'finascop_add',
                 tooltip: 'Valid Api Domains',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 apidomainDetails(record.get('comp_id'));
                 console.log(record.get('comp_id'));
                 // loadapidomains();
                 }
                 },
                 {
                 iconCls: 'finascop_viewfile',
                 tooltip: 'Show Company ID',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 showCompanyID(record.get('comp_ReferenceId'));
                 }
                 }, {
                 icon: IMAGE_BASE_PATH + "/default/icons/map.png",
                 tooltip: 'View Branches',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var compId = record.get('comp_id')
                 Application.Finascop_Company.Markers = [];
                 Application.Finascop_Company.Markers['comBrnch'] = [];
                 Ext.Ajax.request({
                 waitMsg: 'Processing',
                 url: modURL,
                 params: {
                 op: 'getCompanyBranches',
                 compId: compId
                 },
                 failure: function (response, options) {
                 Ext.MessageBox.alert('Notification', ACTION_FAIL);
                 },
                 success: function (response, options) {
                 var tmp = Ext.decode(response.responseText);
                 console.log('tmp', tmp);
                 console.log('branches', Object.keys(tmp).length);
                 if (Object.keys(tmp).length > 0) {
                 showCompanyBranches(record.get('comp_id'), 'comBrnch', tmp);
                 } else {
                 Ext.MessageBox.alert('Error', 'No branches added.');
                 }
                 
                 },
                 failure: function (response) {
                 var tmp = Ext.util.JSON.decode(response.responseText);
                 Ext.MessageBox.alert('Error', 'Error Occured');
                 }
                 });
                 
                 }
                 }
                 ]
                 }*/
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: company_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [company_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'company_name_auto_exp'
        });
        return grid_panel;
    };
    var companyGridAction = function () {
        var action = new Ext.ux.grid.RowActions({
            autoWidth: false,
            hideMode: 'display',
            width: 150,
            actions: [
                {
                    iconCls: 'finascop_edit',
                    tooltip: 'Edit',
                    handler: function (grid, rowIndex, colIndex) {
                        var record = grid.store.getAt(rowIndex);
                        companyDetails(record.get('comp_id'));
                    }
                },
                {
                    getClass: function (v, meta, rec) {
                        if (rec.get('cmp_status') == 'Active') {
                            this.items[1].tooltip = 'Deactivate Company';
                            return 'now_active';
                        } else {
                            this.items[1].tooltip = 'Activate Company';
                            return 'now_inactive';
                        }
                    },
                    handler: function (grid, rowIndex, colIndex) {
                        var record = grid.store.getAt(rowIndex);
                        Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this company?', function (btn, text) {
                            if (btn == 'yes') {
                                console.log('Company ID : ' + record.get('comp_id'));
                                changeCompanyStatus(record.get('comp_id'), record.get('cmp_status'));
                            }
                        });

                    }
                },
                {
                    iconCls: 'finascop_add',
                    tooltip: 'Api',
                    handler: function (grid, rowIndex, colIndex) {
                        var record = grid.store.getAt(rowIndex);
                        apidomainDetails(record.get('comp_id'));
                        console.log(record.get('comp_id'));
                        // loadapidomains();
                    }
                },
                {
                    iconCls: 'finascop_viewfile',
                    tooltip: 'Company ID',
                    handler: function (grid, rowIndex, colIndex) {
                        var record = grid.store.getAt(rowIndex);
                        showCompanyID(record.get('comp_ReferenceId'));
                    }
                },
                {
                    icon: IMAGE_BASE_PATH + "/default/icons/map.png",
                    tooltip: 'Branches',
                    handler: function (grid, rowIndex, colIndex) {
                        var record = grid.store.getAt(rowIndex);
                        var compId = record.get('comp_id')
                        Application.Finascop_Company.Markers = [];
                        Application.Finascop_Company.Markers['comBrnch'] = [];
                        Ext.Ajax.request({
                            waitMsg: 'Processing',
                            url: modURL,
                            params: {
                                op: 'getCompanyBranches',
                                compId: compId
                            },
                            failure: function (response, options) {
                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                            },
                            success: function (response, options) {
                                var tmp = Ext.decode(response.responseText);
                                console.log('tmp', tmp);
                                console.log('branches', Object.keys(tmp).length);
                                if (Object.keys(tmp).length > 0) {
                                    showCompanyBranches(record.get('comp_id'), 'comBrnch', tmp);
                                } else {
                                    Ext.MessageBox.alert('Error', 'No branches added.');
                                }

                            },
                            failure: function (response) {
                                var tmp = Ext.util.JSON.decode(response.responseText);
                                Ext.MessageBox.alert('Error', 'Error Occured');
                            }
                        });

                    }
                }
            ]
        });
        return action;
    };
    var companyActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {
                    var comp_id = Ext.getCmp('company_main_panel').getSelectionModel().getSelections()[0].data.comp_id;
                    companyDetails(comp_id);
                }
            }, {
                text: "Status Change",
                /*getClass: function (v, meta, rec) {
                 if (rec.get('cmp_status') == 'Active') {
                 this.items[1].tooltip = 'Deactivate Company';
                 return 'now_active';
                 } else {
                 this.items[1].tooltip = 'Activate Company';
                 return 'now_inactive';
                 }
                 },*/
                handler: function () {
                    var comp_id = Ext.getCmp('company_main_panel').getSelectionModel().getSelections()[0].data.comp_id;
                    var status = Ext.getCmp('company_main_panel').getSelectionModel().getSelections()[0].data.cmp_status;
                    changeCompanyStatus(comp_id, status, 'Company ID : ');

                }

            }, {
                text: "API",
                handler: function () {
                    var comp_id = Ext.getCmp('company_main_panel').getSelectionModel().getSelections()[0].data.comp_id;
                    apidomainDetails(comp_id);
                }
            }, {
                text: "Company ID",
                handler: function () {
                    var comp_ReferenceId = Ext.getCmp('company_main_panel').getSelectionModel().getSelections()[0].data.comp_ReferenceId;
                    showCompanyID(comp_ReferenceId);
                }
            }, {
                text: "Branches",
                handler: function () {
                    var comp_id = Ext.getCmp('company_main_panel').getSelectionModel().getSelections()[0].data.comp_id;
                    var comBrnch = Ext.getCmp('company_main_panel').getSelectionModel().getSelections()[0].data.comBrnch;
                    Ext.Ajax.request({
                        waitMsg: 'Processing',
                        url: modURL,
                        params: {
                            op: 'getCompanyBranches',
                            compId: comp_id
                        },
                        failure: function (response, options) {
                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            console.log('branches', Object.keys(tmp).length);
                            if (Object.keys(tmp).length > 0) {
                                showCompanyBranches(comp_id, comBrnch, tmp);
                            } else {
                                Ext.MessageBox.alert('Error', 'No branches added.');
                            }

                        },
                        failure: function (response) {
                            var tmp = Ext.util.JSON.decode(response.responseText);
                            Ext.MessageBox.alert('Error', 'Error Occured');
                        }
                    });
                }

            }]
    });

    var changeCompanyStatus = function (cmp_id, cmp_status) {

        Application.Finascop_Company.comp_id = arguments[0];
        Application.Finascop_Company.cmp_status = arguments[1];

        var form_data = {
            comp_id: Application.Finascop_Company.comp_id,
            cmp_status: Application.Finascop_Company.cmp_status
        };
        var params = {
            action: 'Update',
            module: 'finascop_company',
            op: 'changeStatus',
            extrainfo: 'asd',
            id: Application.Finascop_Company.comp_id
        };

        APICall(params, Application.Finascop_Company.changeStatus, form_data);

    };

    var companyForm = function () {
        var form = new Ext.FormPanel({
            id: 'company_details_form',
            labelAlign: 'top',
            autoHeight: true,
            url: modURL + "&op=saveCompany",
            frame: true,
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'form',
                            columnWidth: 0.5,
                            defaults: {
                                xtype: 'textfield',
                                anchor: '98%',
                            },
                            items: [{
                                    xtype: 'hidden',
                                    id: 'comp_id',
                                    name: 'comp_id'
                                }, {
                                    fieldLabel: 'Company Name',
                                    id: 'comp_name',
                                    name: 'comp_name',
                                    allowBlank: false,
                                    maxLength: 100
                                }, {
                                    fieldLabel: 'PAN',
                                    id: 'cmp_PAN',
                                    name: 'cmp_PAN',
                                    allowBlank: true,
                                    maxLength: 10,
                                    regex: /^[A-Za-z]{5}\d{4}[A-Za-z]{1}$/,
                                    invalidText: 'Invalid PAN. Eg.XXXXX9999X'
                                }, {
                                    fieldLabel: 'Contact No.1',
                                    id: 'comp_Ph',
                                    allowBlank: false,
                                    name: 'comp_Ph'
                                }, {
                                    fieldLabel: 'Contact No.2',
                                    id: 'comp_Fax',
                                    //allowBlank: false,
                                    name: 'comp_Fax'
                                }, {
                                    fieldLabel: 'Reg Number 1',
                                    id: 'comp_dlno1',
                                    allowBlank: false,
                                    name: 'comp_dlno1'
                                }, {
                                    fieldLabel: 'FSSAI / EORI',
                                    id: 'comp_fssaino',
                                    allowBlank: false,
                                    name: 'comp_fssaino'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.5,
                            defaults: {
                                xtype: 'textfield',
                                anchor: '98%',
                            },
                            items: [{
                                    fieldLabel: 'Short Name',
                                    id: 'comp_shortname',
                                    allowBlank: false,
                                    name: 'comp_shortname',
                                    maxLength: 3
                                }, {
                                    xtype: 'checkbox',
                                    boxLabel: 'Is an auditing company',
                                    id: 'auditing_company',
                                    name: 'auditing_company',
                                    inputValue: 'Yes',
                                    allowBlank: false,
                                    hideLabel: true
                                }, {
                                    fieldLabel: 'Address',
                                    id: 'comp_address',
                                    name: 'comp_address',
                                    allowBlank: false,
                                    maxLength: 300,
                                    height: 88,
                                    xtype: 'textarea'
                                }, {
                                    fieldLabel: 'Reg Number 2',
                                    id: 'comp_dlno2',
                                    name: 'comp_dlno2'
                                }, {
                                    fieldLabel: 'GST / VAT',
                                    id: 'comp_gstno',
                                    allowBlank: false,
                                    name: 'comp_gstno'
                                }]
                        }]
                }]
        });
        return form;
    };

    var recs_per_page = 10;
    /* history_store = store for history grid   */
    var cratedomainStore = function () {
        var domain_store = new Ext.data.JsonStore({
            url: '?module=finascop_company&op=getapiDomains',
            fields: ['comp_id', 'apidomains'],
            root: 'data',
            id: 'id_apidomain',
            totalProperty: 'totalcount',
            // turn on remote sorting
            remoteSort: true
        });
        return domain_store;
    };

    var loadapidomains = function (cmpid) {
        Ext.getCmp('griddomain').getStore().setDefaultSort('apidomains', 'asc');
        Ext.getCmp('griddomain').getStore().removeAll();
        Ext.getCmp('griddomain').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page,
                'cmp_id': cmpid
            }
        });
    };

    var apidomainGrid = function () {
        var adm_store = cratedomainStore();
//var action = domainAction();
        var domainGrid = new Ext.grid.GridPanel({
            store: adm_store,
            layout: 'fit',
            forceFit: true,
            height: 150,
            // title: '',
            //iconCls: '',
            id: 'griddomain',
            // plugins: [action],
            remoteSort: false,
            columns: [{
                    header: 'Valid Api Domains',
                    dataIndex: 'apidomains',
                    // id: 'addon',
                    sortable: false

                }, {
                    xtype: 'actioncolumn',
                    //header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    width: 15,
                    items: [{
                            iconCls: 'my-icon57',
                            tooltip: 'Delete Api Domain',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.MessageBox.confirm("Confirm", "Are you sure to delete this record?", function (btn) {
                                    if (btn == "yes") {
                                        deleteDomain(record.get('comp_id'), record.get('apidomains'));
                                    }
                                });
                            }
                        }



                    ]
                }],
            viewConfig: {
                forceFit: true
            },
            stripeRows: true


        });
        return domainGrid;
    };

    var deleteDomain = function (comp_id, apidomains) {

        Ext.Ajax.request({
            waitMsg: 'Deleting...',
            url: modURL,
            params: {
                //delete selected mapping        
                op: 'deleteValidip',
                comp_id: comp_id,
                validip: apidomains

            },
            success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success == true) {
                    //Ext.getCmp('griddomain').store.removeAll();
                    Ext.getCmp('griddomain').store.reload({
                        callback: function () {
                            Application.example.msg("Success", "Record has been deleted successfully!");
                        }
                    });
                }
            }
        });
    }

    var addValidDomain = function (id) {
        var row = new Ext.data.Record.create({
            name: 'apidomains'

        });
        var r = new row({
            id: id,
            apidomains: Ext.getCmp('id_apidomain').getValue()
        });
        Ext.getCmp('griddomain').stopEditing(); //stops any acitve editing
        Ext.getCmp('griddomain').getStore().add(r);

    }

    var apiddomainfrm = function (cmpid) {

        //var validIPAddress = /^(?=\d+\.\d+\.\d+\.\d+$)(?:(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])\.?){4}$/;
        var validIPAddress = /^(([1-9]?\d|1\d\d|2[0-5][0-5]|2[0-4]\d)\.){3}([1-9]?\d|1\d\d|2[0-5][0-5]|2[0-4]\d)$/;
        Ext.apply(Ext.form.VTypes, {
            validIp: function (val, field) {
                return validIPAddress.test(val);
            },
            validIpText: 'Not a Valid IP.'
        });

        var vadFrm = new Ext.FormPanel({
            frame: true,
            url: modURL + '&op=saveApiDomains',
            id: 'id_vadform',
            border: false,
            autoHeight: true,
            labelAlign: 'left',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    labelAlign: 'top',
                    items: [{
                            layout: 'form',
                            columnWidth: .8,
                            items: {
                                xtype: 'textfield',
                                fieldLabel: 'Valid Api Domain',
                                id: 'validip',
                                anchor: '98%',
                                tabIndex: 15,
                                vtype: 'validIp'
                            }
                        }, {
                            layout: 'form',
                            columnWidth: .2,
                            items: {
                                xtype: 'button',
                                iconCls: 'add',
                                tabIndex: 16,
                                tooltip: 'Add valid api domain',
                                // style: 'margin-left:5px',
                                style: 'padding-top:18px;',
                                handler: function () {
                                    if (Ext.isEmpty(Ext.getCmp('validip').getValue()))
                                    {
                                        Ext.MessageBox.alert("Notification", "Please Enter Api Domain");
                                        return;
                                    }
                                    var cForm = Ext.getCmp('id_vadform');
                                    var time = new Date();
                                    var t_stamp = time.format("YmdHis");

                                    cForm.getForm().submit({
                                        params: {
                                            cmp_id: cmpid,
                                            apikey: _SESSION.apikey,
                                            tstamp: t_stamp
                                        },
                                        waitTitle: 'Please Wait!',
                                        waitMsg: 'Saving data...',
                                        success: function (form, action) {
                                            eval('var tmp=' + action.response.responseText);
                                            // var tmp = Ext.decode(response.responseText);
                                            if (tmp.success == true) {
                                                Ext.getCmp('validip').reset();
                                                Ext.getCmp('griddomain').store.reload({
                                                    callback: function () {
                                                        Application.example.msg("Notification", tmp.msg); 
//                                                        function (btn) {
//
//                                                        });
                                                    }
                                                });
                                            } else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }
                                        },
                                        failure: function (form, action) {
                                            Ext.MessageBox.alert("Notification", action.response.responseText, function (btn) {

                                            });
                                        }
                                    });
                                }
                            }
                        }]

                }, apidomainGrid()]
        });
        return vadFrm;
    };

    var showCompanyID = function () {
        var companyID = arguments[0];
        var win_id = "company_id_window";

        var company_id_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(company_id_window)) {
            company_id_window = new Ext.Window({
                id: win_id,
                title: 'View Company ID',
                layout: 'fit',
                modal: true,
                width: 300,
                items: [{
                        xtype: 'textfield',
                        value: companyID,
                        anchor: '98%',
                        editable: false,
                        readOnly: true,
                        fieldLabel: 'Company ID'
                    }]

            });
            company_id_window.doLayout();
            company_id_window.show(this);
            company_id_window.center();
        }
    }

    var companyDetails = function () {

        var comp_id = arguments[0];
        var win_id = "company_details_window";

        var company_details_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(company_details_window)) {
            var cForm = companyForm();
            company_details_window = new Ext.Window({
                id: win_id,
                title: 'Create New Company',
                layout: 'fit',
                width: 600,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                //iconCls: '',
                resizable: false,
                items: cForm,
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            company_details_window.close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            if (cForm.getForm().isValid()) {
                                Application.Finascop_Company.comp_id = comp_id;
                                if (Ext.getCmp('auditing_company').getValue() == false) {

                                    var form_data = {
                                        comp_id: Application.Finascop_Company.comp_id
                                    };
                                    var params = {
                                        action: 'Insert',
                                        module: 'finascop_company',
                                        op: 'checkAudit',
                                        extrainfo: 'asd',
                                        id: Application.Finascop_Company.comp_id
                                    };

                                    APICall(params, Application.Finascop_Company.checkAuditor, form_data);

                                } else
                                    Application.Finascop_Company.saveCompanyFn();

                            } else {
                                Ext.MessageBox.alert("Notification", "Please enter all required fields");
                            }
                        }
                    }],
                listeners: {
                    afterrender: function () {
                        if (!Ext.isEmpty(comp_id) && comp_id > 0) {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");

                            var myMask = new Ext.LoadMask(Ext.getCmp('company_details_window').getEl(), {msg: "Please wait..."});
                            myMask.show();

                            cForm.load({
                                url: modURL + '&op=getDetails',
                                params: {
                                    'id': comp_id,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                },
                                success: function (frm, action) {
                                    eval('var tmp=' + action.response.responseText);
                                    company_details_window.setTitle("Edit Details : " + Ext.getCmp("comp_name").getValue());
                                    console.log(tmp);
                                    if (tmp.data.auditing_company == 'Yes')
                                        Ext.getCmp('auditing_company').setValue(true);
                                    myMask.hide();
                                }
                            });
                        }
                    }
                }
            });
        }

        company_details_window.doLayout();
        company_details_window.show(this);
        company_details_window.center();
    };

    var apidomainDetails = function (cmpid) {

        var wind_id = "apidomain_details_window";

        var apidomain_details_window = Ext.getCmp(wind_id);
        if (Ext.isEmpty(apidomain_details_window)) {
            var adForm = apiddomainfrm(cmpid);
            apidomain_details_window = new Ext.Window({
                id: wind_id,
                title: 'Api Domains',
                layout: 'fit',
                width: 350,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                iconCls: '',
                resizable: false,
                items: adForm,
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            apidomain_details_window.close();
                        }
                    }]

            });
        }

        apidomain_details_window.doLayout();
        apidomain_details_window.show(this);
        apidomain_details_window.center();
        loadapidomains(cmpid);
    };
    var showMap = function (ind) {

        var panel = new Ext.Panel({
            layout: 'border',
            items: mapPanel('max_map', ind)
        });

        var map_win = new Ext.Window({
            width: winsize.width * 0.9,
            height: winsize.height * 0.9,
            plain: true,
            modal: true,
            frame: true,
            constrainHeader: true,
            layout: 'border',
            items: panel,
            buttons: [{
                    text: 'Cancel',
                    tabIndex: '10',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        map_win.close();
                    }
                }]
        });

        map_win.doLayout();
        map_win.show(this);
        map_win.center();
    };

    var showCompanyBranches = function (companyId, ind, tmp) {

        var _addNewWindow = new Ext.Window({
            title: 'Company Branches',
            layout: 'fit',
            width: winsize.width * 0.9,
            height: winsize.height * 0.9,
            resizable: false,
            id: 'showCompanyBranches',
            draggable: true,
            closable: true,
            buttonAlign: 'left',
            modal: true,
            bodyStyle: {"background-color": "white"},
            items: [mapPanel('location_map' + ind, ind, tmp)],
            tools: [{
                    id: 'maximize',
                    handler: function () {
                        showMap(ind);
                    }
                }],
            fbar: [],
            buttons: [{html: "<img src='https://retalinemapicons.s3-ap-southeast-1.amazonaws.com/wht-circle-sm.png' title='CPD'> CPD"},
                {html: "<img src='https://retalinemapicons.s3-ap-southeast-1.amazonaws.com/grn-circle-sm.png' title='CS'> CS"},
                {html: "<img src='https://retalinemapicons.s3-ap-southeast-1.amazonaws.com/red-circle-sm.png' title='Distributor'> Distributor"},
                {html: "<img src='https://retalinemapicons.s3-ap-southeast-1.amazonaws.com/ylw-circle-sm.png' title='Retailor'> Retailor"},
                '->',
                {
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    tabIndex: 511,
                    handler: function () {
                        _addNewWindow.close();
                    }
                }],
            listeners: {
                afterrender: function () {


                }
            }
        });
        _addNewWindow.doLayout();
        _addNewWindow.show();
        _addNewWindow.center();
    };
    var mapPanel = function (id, ind, tmp) {
        var centerParam = {
            lat: 8.779388,
            lng: 76.269909
        };
        return {xtype: 'gmappanel',
            region: 'center',
            zoomLevel: 4,
            minGeoAccuracy: 4,
            gmapType: 'map',
            //scaleControl: true,
            id: id,
            anchor: '99%',
            mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
            mapControls: ['GSmallMapControl', 'GMapTypeControl', 'NonExistantControl'],
            setCenter: centerParam,
            /* markers: markers,*/
            listeners: {
                afterrender: function () {
                    setTimeout(function () {

                        //console.log('afterrender tmp', tmp.length);
                        if (tmp.length > 0) {
                            var mymarker = [];
                            //var a = 1;
                            Ext.each(tmp, function (rec) {
                                var contentString = '<div id="content">' +
                                        '<h1 class="firstHeading">' + rec.br_Name + '</h1>' +
                                        '<table style="font-size:10px;color:#333">' +
                                        '<tr><td>Phone</td><td> : ' + rec.br_Phone + '</td></tr>' +
                                        '<tr><td>Pincode </td><td> : ' + rec.br_pincode + '</td></tr>' +
                                        '</table>' +
                                        '</div>';
                                switch (rec.br_PyramidLevel) {
                                    case '1':
                                        var mapicon = "https://retalinemapicons.s3-ap-southeast-1.amazonaws.com/wht-circle-sm.png";
                                        break;
                                    case '2':
                                        var mapicon = "https://retalinemapicons.s3-ap-southeast-1.amazonaws.com/grn-circle-sm.png";
                                        break;
                                    case '3':
                                        var mapicon = "https://retalinemapicons.s3-ap-southeast-1.amazonaws.com/red-circle-sm.png";
                                        break;
                                    case '4':
                                        var mapicon = "https://retalinemapicons.s3-ap-southeast-1.amazonaws.com/ylw-circle-sm.png";
                                        break;
                                }
                                mymarker.push({
                                    lat: rec.br_Lat,
                                    lng: rec.br_Lng,
                                    marker: {
                                        title: rec.br_Name,
                                        draggable: false,
                                        infoWindow: {content: contentString}
                                    },
                                    listeners: {
                                        click: function () {
                                            var extraInfo = this.extraInfo;
                                            //Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);
                                        }
                                    },
                                    icon: mapicon,
                                    extraInfo: rec.br_status
                                });
                                //rec.set('b_marker', a);
                                //a++;
                            });
                            var bounds = {value: null};
                            Ext.getCmp('location_map' + ind).addMarkers(mymarker, true, bounds);
                            Ext.getCmp(id).clearMarkers();
                            //console.log('b_marker', a);
                            //Ext.getCmp('location_map' + ind).clearMarkers();
                            //console.log('vehicle_marker1', mymarker);
                            Application.Finascop_Company.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true, bounds);
                            //Ext.getCmp('location_map' + ind).animateMarker(Application.Finascop_Company.Markers[ind][0], 'BOUNCE', 'START');
                           
                        }

                        setTimeout(function () {
                            // Ext.getCmp('location_map' + ind).animateMarker(Application.Finascop_Company.Markers[ind][0], 'BOUNCE', 'STOP');
                        }, 2000);

                        if (id == 'max_map') {
                            Ext.getCmp('max_map').addMarkers(Application.Finascop_Company.Markers[ind], true);
                        }
                    }, 1000);
                }
            }
        };
    };


    return {
        initCompany: function () {
            var panelId = 'company_main_panel';
            var company_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(company_panel)) {
                company_panel = companyGrid(panelId);
            }
            Application.UI.addTab(company_panel);
            company_panel.doLayout();
            //return company_panel;
        },
        saveCompanyFn: function () {
            var form_data = Ext.getCmp('company_details_form').getForm().getValues();
            var params = {
                action: 'Insert',
                module: 'finascop_company',
                op: 'saveCompany',
                id: '0',
                extrainfo: 'asd'
            };
            if (Application.Finascop_Company.comp_id > 0) {
                params.action = 'Update';
                params.id = Application.Finascop_Company.comp_id;
            }
            APICall(params, Application.Finascop_Company.SaveCompany, form_data);

        },
        SaveCompany: function () {
            var cForm = Ext.getCmp('company_details_form');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            cForm.getForm().submit({
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                success: function (cForm, action) {
                    eval('var tmp=' + action.response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Ext.getCmp('company_details_window').close();
                        Application.example.msg("Success", "Company details has been saved successfully");
                        Ext.getCmp('company_main_panel').getStore().reload();
//                        function (btn) {
//                            Ext.getCmp('company_main_panel').getStore().reload();
//                        });
                    }
                },
                failure: function (cForm, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: (obj.error) ? obj.error : obj.errors.reason,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    } else {
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: MAN_FIELD,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    }
                }
            });
        },
        changeStatus: function () {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'changeStatus',
                    comp_id: Application.Finascop_Company.comp_id,
                    cmp_status: Application.Finascop_Company.cmp_status
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Ext.MessageBox.alert("Success", "Company status has been changed successfully", function (btn) {

                            Ext.getCmp('company_main_panel').getStore().reload();
                        });
                    } else if (tmp.valid === false) {
                        // console.log(tmp);
                        Ext.MessageBox.alert("Notification", tmp.errors, function (btn) {

                            Ext.getCmp('company_main_panel').getStore().reload();
                        });
                    }
                }
            });
        },
        CompanyMainPanel: function () {
            return [{
                    region: 'center',
                    border: false,
                    layout: 'fit',
                    items: Application.Finascop_Company.initCompany()
                }];
        },
        checkAuditor: function () {

            Ext.Ajax.request({
                waitMsg: 'Checking',
                url: modURL,
                params: {
                    op: 'checkAudit',
                    comp_id: Application.Finascop_Company.comp_id
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.Finascop_Company.saveCompanyFn();
                    } else if (tmp.valid === false) {
                        Ext.MessageBox.alert('Notification', tmp.msg);

                    }
                }
            });
        }
    };
}();