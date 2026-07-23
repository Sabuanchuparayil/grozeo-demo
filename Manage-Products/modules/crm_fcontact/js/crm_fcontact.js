Application.Crm_FContact = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=crm_fcontact';
    var recs_per_page = 15;
    var WinMask;
    var imgpath = IMAGE_BASE_PATH;
    var onGridResize = function (cmp) {
        recs_per_page = 15;
    };
    var gridSelectionChangedcat = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().getSelections()[0].data.crco_id;
            Ext.getCmp('tabpanelMarketingContact').setActiveTab(0);
            Application.Crm_FContact.ViewMode(ID);
        } else {
            Application.Crm_FContact.Cache.crco_id = 0;
            Application.Crm_FContact.ViewMode(0);
        }
    };
    var marketingPanel = function (id) {
        var contactsPanel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            title: 'Contacts',
            id: id,
            items: [gridViewContactDetails(), panelContactt()]
        });
        return contactsPanel;
    };

    var tableCotactDetails = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '?module=Crm_fContact&op=loadEditData&crco_id=' + Application.Crm_FContact.Cache.crco_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var contactsPanel = new Ext.Panel({
            id: 'tableCrmContactsDataview',
            region: 'center',
            width: winsize.width * 0.39,
            items: [{
                    region: 'center',
                    defaults: {
                        frame: false
                    },
                    html: '<iframe id="downloadContactsIframe" name="downloadContactsIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="' + src + '"; ></iframe>'
                }
            ]
        });
        return contactsPanel;
    };
    var panelContactt = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '?module=Crm_fContact&op=loadEditData&crco_id=' + Application.Crm_FContact.Cache.crco_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var panel = new Ext.TabPanel({
            region: 'east',
            frame: false,
            activeTab: 0,
            tabPosition: 'top',
            border: true,
            bodyStyle: {
                "background-color": "white"
            },
            id: 'tabpanelMarketingContact',
            width: winsize.width * 0.4,
            height: winsize.height * 0.6,
            defaults: {
                layout: 'fit',
                autoScroll: true,
                frame: false
            },
            items: [
                {
                    title: 'View Contact Details',
                    id: 'tabMarketingAddContact',
                    layout: 'border',
                    items: [tableCotactDetails()]
                },
            ],
            fbar: [
            ],
            listeners: {
                'afterrender': function (component) {

                },
                tabchange: function (s, tab) {

                    switch (tab.title) {
                        case 'Contact Details':
                            break;

                    }
                }
            }
        });
        return panel;
    };
    var contactGridStore = function () {
        return new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getContactDetails',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: '',
                root: 'data'
            },
            ['contact_name', 'mobile', 'email', 'ABO', 'STATUS', 'TYPE', 'crco_id', 'crco_userId', 'crco_isActive', 'ABO', 'crmu_id', 'crpt_id', 'crco_projectDate', 'crco_location', 'crco_noOfEvents']),
            groupField: '',
            sortInfo: {
                field: 'contact_name',
                direction: 'ASC'
            },
            root: 'data',
            autoLoad: true,
        //    remoteSort: true,
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().selectRow(0);
                    }

                }
            }
        });
    };
    var gridViewContactDetails = function () {
        var _jsonStoreContactGrid = contactGridStore();
        var _contactsGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'contact_name'
                },
                {
                    type: 'string',
                    dataIndex: 'mobile'
                },
                {
                    type: 'string',
                    dataIndex: 'email'
                }, {
                    type: 'string',
                    dataIndex: 'TYPE'
                }

            ]
        });
        _contactsGridFilter.remote = true;
        _contactsGridFilter.autoReload = true;
        var contactsGrid = new Ext.grid.GridPanel({
            id: 'gridMarketingViewcontacts',
            layout: 'fit',
            store: _jsonStoreContactGrid,
            region: 'center',
            frame: true,
            border: false,
            plugins: [_contactsGridFilter],
            loadMask: true,
            columns: [
                {
                    header: 'Contact Name',
                    dataIndex: 'contact_name',
                    sortable: true,
                    width: 200
                },
                {
                    header: 'Contact Number',
                    dataIndex: 'mobile',
                    sortable: true,
                    width: 100
                },
                {
                    header: 'Email Address',
                    dataIndex: 'email',
                    sortable: true,
                    width: 200
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
                            fcontactsActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                    header: 'Actions',
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    items: [
                        {
                            text: ' ',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _currentstatus = data.STATUS;
                                var _currentuser = data.crco_userId;
                                this.items[1].tooltip = 'Edit Contact Details';
                                return 'finascop_edit';
                            },
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var _status = record.data.STATUS;
                                var _active = record.data.crco_isActive;
                                contactform(record.get('crco_id'));
                            }
                        },
                        {
                            text: ' ',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _currentstatus = data.STATUS;
                                this.items[1].tooltip = 'Move to Lead';
                                return 'move-to-contactss';
                            },
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var _status = record.data.STATUS;
                                var _active = record.data.crco_isActive;
                                if (_active == 1) {
                                    Ext.MessageBox.confirm('Confirm', 'Do you want to move this as Lead?', function (btn, text) {
                                        if (btn == 'yes') {
                                            Application.Crm_FContact.insertCommunication(record.get('crco_id'));
                                        }
                                    });
                                }
                            }
                        }
                    ]
                }*/
            ],
            tbar: [{
                    xtype: 'button',
                    text: 'Create Contact',
                    tooltip: 'Create Contact',
                    iconCls: 'finascop_add',
                    handler: function () {
                        contactform();
                    }
                }],
            viewConfig: {
                forceFit: true
            },
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('STATUS') == 'Assigned') {
                        return '';
                    } else if (record.get('STATUS') == 'UnAttended') {
                        return '';
                    } else if (record.get('STATUS') == 'Call Later') {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return 'finascop_indicateColPINK';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _jsonStoreContactGrid,
                displayInfo: true,
                displayMsg: 'Displaying items {0} - {1} of {2}',
                emptyMsg: "No records to display",
                items: [{
                        html: '<div class="finascop_color_wr">\<div class="color-light-yellow_small"></div><div class="text_c"> CallLater </div>\
                    <div class="color-light-red_small"></div> <div class="text_c">Not Interested</div>\
                </div> '
                    }]
            }),
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedcat
                }
            }),
            listeners: {
                afterrender: function () {
                    _jsonStoreContactGrid.load();
                },
                cellclick: function (grid, rowIndex, columnIndex, e) {
                },
                resize: onGridResize
            }
        });
        return contactsGrid;
    };
    var fcontactsActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () { 
                    var crco_id = Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().getSelections()[0].data.crco_id;
                    contactform(crco_id);
                }
            }, {
                text: "Move to Lead",
                handler: function () {
                                var crco_id = Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().getSelections()[0].data.crco_id;
                                var STATUS = Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().getSelections()[0].data.STATUS;
                                var crco_isActive = Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().getSelections()[0].data.crco_isActive;
                                if (crco_isActive == 1) {
                                    Ext.MessageBox.confirm('Confirm', 'Do you want to move this as Lead?', function (btn, text) {
                                        if (btn == 'yes') {
                                            Application.Crm_FContact.insertCommunication(crco_id);
                                        }
                                    });
                                }
                }
            }]
    });
    var contactToLead = function (id, activeStatus) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=convertToLead',
            params: {
                crco_id: id,
                status: activeStatus
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {
                    Ext.getCmp('gridMarketingViewcontacts').getStore().reload();
                } else {
                    Ext.MessageBox.alert('Error', "Invalid data");
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });
    };
    var contactUserDetails = function (id, currentStatus) {
        var contactid = id;
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=getUserid',
            params: {
                crco_id: id,
                status: currentStatus
            },
            success: function (response) {

                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {

                    Ext.getCmp('gridMarketingViewcontacts').getStore().load(
                            {
                                callback: function (record, options, success) {
                                    var gridPanel = Ext.getCmp('gridMarketingViewcontacts');
                                    var index = gridPanel.store.find('crco_id', id);
                                    gridPanel.getSelectionModel().selectRow(index);
                                    //loadContactWindow(contactid);
                                    contactform(contactid);
                                }
                            }
                    );
                } else {
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
   
    var referenceStores = function () {
        var _referenceStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getReference',
            method: 'post',
            fields: ['referers_id', 'referers_contact_id'],
            remoteSort: true
        });
        return _referenceStore;

    };
    var contactform = function (contactid) {
        var referenceStore = referenceStores();
        var contactform = new Ext.Window({
            width: 500,
            height: 230,
            id: 'add_contactWindow',
            shadow: false,
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelAddContact',
                    title: !Ext.isEmpty(contactid)?'Edit Contact':'Create Contact',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 59px 10px"},
                    labelAlign: 'top',
                    items: [
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 1.0,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'customerId',
                                            hidden: true
                                        },
                                        {
                                            xtype: 'textfield',
                                            id: 'statusABOhidden',
                                            hidden: true
                                        },
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Name',
                                            maxLength: 250,
                                            tabIndex: 300,
                                            xtype: 'textfield',
                                            anchor: '95%',
                                            allowBlank: false,
                                            id: 'textfieldContactDetailsContactPerson',
                                            name: 'textfieldContactDetailsContactPerson',
                                            hideBorders: true,
                                            border: false,
                                        },
                                    ]
                                }
                                
                            ]
                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: '1.0',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Mobile',
                                            tabIndex: 301,
                                            id: 'numberfieldContactDetailsprimaryMobile',
                                            name: 'numberfieldContactDetailsprimaryMobile',
                                            anchor: '95%',
                                            minLength: 10,
                                            maxLength: 10,
                                            allowBlank: false,
                                            msgTarget: 'under',
                                            maxLengthText: "Maximum length for the field is 12",
                                            minLengthText: "Minimum length for the field is 10"
                                        }
                                    ]
                                }, 
                                {
                                    layout: 'form',
                                    columnWidth: '1.0',
                                    border: false,
                                    items: [
                                        {
                                    layout: 'form',
                                    columnWidth: 0.5,
                                    border: false,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            fieldLabel: 'Email',
                                            tabIndex: 302,
                                            id: 'textfieldContactDetailsEmail',
                                            name: 'textfieldContactDetailsEmail',
                                            anchor: '95%',
                                            vtype: 'email',
                                            allowBlank: false,
                                            maxLength: 100,
                                        }
                                    ]
                                }
//                                        {
//                                            xtype: 'checkbox',
//                                            boxLabel: 'Referer',
//                                            id: 'referer_id',
//                                            name: 'referer_id',
//                                            style: 'margin: 15px 0px 0px 1px;',
//                                            inputValue: 'Yes',
//                                            //hidden: true,
//                                            hideLabel: true,
//                                        }
//                                    ]
//                                },
//                                {
//                                    layout: 'form',
//                                    columnWidth: 0.5,
//                                    border: false,
//                                    items: [
//                                        {
//                                            xtype: 'checkbox',
//                                            boxLabel: 'Refered By',
//                                            id: 'refered_by',
//                                            name: 'refered_by',
//                                            style: 'margin: 15px 0px 0px 1px;',
//                                            inputValue: 'Yes',
//                                            //hidden: true,
//                                            hideLabel: true,
//                                            listeners: {
//                                                check: function ()
//                                                {
//                                                    if (Ext.getCmp('refered_by').checked == true)
//                                                    {
//                                                        Ext.getCmp('referers_id').show();
//                                                    }
//                                                    else
//                                                    {
//                                                        Ext.getCmp('referers_id').hide();
//                                                    }
//                                                }
//                                            }
//                                        },
//                                        {
//                                            xtype: 'lovcombo',
//                                            id: 'referers_id',
//                                            store: referenceStore,
//                                            typeAhead: true,
//                                            triggerAction: 'all',
//                                            lazyRender: true,
//                                            allowblank: false,
//                                            mode: 'local',
//                                            editable: true,
//                                            emptyText: 'Select',
////                                            hidden: true,
//                                            displayField: 'referers_contact_id',
//                                            valueField: 'referers_id',
//                                            hiddenName: 'referers_id',
//                                            fieldLabel: 'Referers',
//                                            name: 'referers_id',
//                                            anchor: '95%',
//                                            minChars: 1
//                                        }
                                    ]
                                }
                            ]
                        },
//                        {
//                            layout: 'column',
//                            border: false,
//                            items: [
//                                {
//                                    layout: 'form',
//                                    columnWidth: .5,
//                                    border: false,
//                                    items: [
//                                         {
//                                            xtype: 'checkbox',
//                                            boxLabel: 'Referer',
//                                            id: 'referer_id',
//                                            name: 'referer_id',
//                                            style: 'margin: 15px 0px 0px 1px;',
//                                            inputValue: 'Yes',
//                                            //hidden: true,
//                                            hideLabel: true,
//                                        }
//                                    ]
//                                }]
//                        }
                    ]

                })
            ],
            fbar: [{
                    text: "Cancel",
                    anchor: '90%',
                    columnWidth: 0.1,
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    tabIndex: 303,
                    handler: function () {
                        Ext.getCmp('gridMarketingViewcontacts').getStore().load();
                        contactform.close();
                    }
                },
                {
                    text: 'Save',
                    anchor: '95%',
                    columnWidth: 0.1,
                    bodyStyle: {'margin': '0px 0px 0px 140px'},
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    tabIndex: 304,
                    handler: function () {
                        var _customerId = Ext.getCmp('customerId').getValue();
                        var _addContactData = Ext.getCmp('formpanelAddContact');
                        Ext.getCmp('numberfieldContactDetailsprimaryMobile').allowBlank = false;
                        Ext.getCmp('textfieldContactDetailsContactPerson').allowBlank = false;
                        Ext.getCmp('numberfieldContactDetailsprimaryMobile').allowBlank = false;
//                        Ext.getCmp('textfieldContactDetailsEmail').allowBlank = false;
                        contactInsertion(_customerId);
                    }
                }



            ]

        });
        if (contactid > 0) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var regFee_form = Ext.getCmp('formpanelAddContact').getForm();
            regFee_form.load({
                params: {
                    EditStatus: 1,
                    _edit_crco_id: contactid,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                url: modURL + '&op=loadEditData',
                waitMsg: 'Loading...', success: function (form, action) {
                    eval('var tmp=' + action.response.responseText);
                    var tmp = Ext.decode(action.response.responseText);
//                    if (tmp.data.referedBy_status == 1) {
//                        Ext.getDom('refered_by').checked = true;
                        Ext.getCmp('referers_id').show();
                        Ext.getCmp('referers_id').setValue(tmp.data.crco_reference);
                        Ext.getCmp('referers_id').getStore().load();
//                    }
                    if(tmp.data.reference_status == 1)
                        Ext.getDom('referer_id').checked = true;
                },
                failure: function (form, action) {
                }
            });
        }
        contactform.doLayout();
        contactform.show();
        contactform.center();
        return contactform;
    }

    var contactInsertion = function (_customerId) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var _individualMobile = Ext.getCmp('numberfieldContactDetailsprimaryMobile').getValue();
        var _individualName = Ext.getCmp('textfieldContactDetailsContactPerson').getValue();
        var _addeditform = Ext.getCmp('formpanelAddContact');
        if (_customerId > 0) {
            _customerId = _customerId;
            Application.Crm_FContact.Cache.upcrocid = _customerId;
        } else {
            _customerId = '-';
            Application.Crm_FContact.Cache.upcrocid = 0;
        }
        if (_addeditform.getForm().isValid()) {
            if (_individualName != '' && _individualMobile != '') {
                var form_data = {
                    textfieldContactDetailsContactPerson: Ext.getCmp('textfieldContactDetailsContactPerson').getValue(),
                    contact_primarymob: Ext.getCmp('numberfieldContactDetailsprimaryMobile').getValue(),
                    textfieldContactDetailsEmail: Ext.getCmp('textfieldContactDetailsEmail').getValue(),
//                    reference: Ext.getCmp('referers_id').getValue(),
//                    refered_by: Ext.getCmp('refered_by').getValue(),
//                    referer_id: Ext.getCmp('referer_id').getValue()
                };
                var params = {
                    action: 'Insert',
                    module: 'Crm_fContact',
                    op: 'insertAddContactData',
                    id: _customerId,
                    extrainfo: 'INS'
                };
                APICall(params, Application.Crm_FContact.insertData, form_data);
            }


        } else
        {

            Ext.MessageBox.alert('Notification', "Please enter all required fields");
        }

    };
    var loadContactWindow = function (contactid) {

        var toolbar = new Ext.Toolbar(
                {
                    layout: 'column',
                    items: [{columnWidth: 0.8,
                            anchor: '95%',
                            html: 'Company:' + _SESSION.finascop_current_company + '  Branch: ' + _SESSION.current_branch,
                        },
                        {
                            text: "Cancel",
                            anchor: '90%',
                            columnWidth: 0.1,
                            xtype: 'button',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            tabIndex: 33,
                            handler: function () {
                                Ext.getCmp('gridMarketingViewcontacts').getStore().load();
                                contactWindowforMarketing.close();
                            }
                        },
                        {
                            text: 'Save',
                            anchor: '95%',
                            columnWidth: 0.1,
                            bodyStyle: {'margin': '0px 0px 0px 140px'},
                            xtype: 'button',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            tabIndex: 32,
                            handler: function () {
                                var _customerId = Ext.getCmp('customerId').getValue();
                                var _addContactData = Ext.getCmp('formpanelAddContact');
                                Ext.getCmp('numberfieldContactDetailsprimaryMobile').allowBlank = false;
                                Ext.getCmp('textfieldContactDetailsContactPerson').allowBlank = false;
                                Ext.getCmp('numberfieldContactDetailsprimaryMobile').allowBlank = false;
                                Ext.getCmp('textfieldContactDetailsEmail').allowBlank = false;
                                contactInsertion(_customerId);
                            }
                        }
                    ]
                });
        var contactWindowforMarketing = Ext.getCmp('contactWindowforMarketing');
        if (Ext.isEmpty(contactWindowforMarketing)) {
            contactWindowforMarketing = new Ext.Window({
                id: 'contactWindowforMarketing',
                title: 'Contact Details',
                layout: 'fit',
                width: winsize.width * 0.6,
                height: 210,
                shadow: false,
                modal: true,
                resizable: false,
                items: [contactform()],
                bbar: toolbar
            });
        }
        if (contactid > 0) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var regFee_form = Ext.getCmp('formpanelAddContact').getForm();
            regFee_form.load({
                params: {
                    EditStatus: 1,
                    _edit_crco_id: contactid,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                url: modURL + '&op=loadEditData',
                waitMsg: 'Loading...', success: function (form, action) {
                    eval('var tmp=' + action.response.responseText);
                    var tmp = Ext.decode(action.response.responseText);
                },
                failure: function (form, action) {
                }
            });
        }
        contactWindowforMarketing.doLayout();
        contactWindowforMarketing.show();
        contactWindowforMarketing.center();
    };
    var communication_panel = function () {

        var record = Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().getSelected();
        var crco_id = record.data.crco_id;
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        Ext.Ajax.request({
            url: modURL + '&op=getCommunication',
            method: 'POST',
            params: {
                crco_id: crco_id,
                apikey: _SESSION.apikey,
                tstamp: t_stamp
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                var propertygridPanel = Ext.getCmp('PanelCommunicationId');
                propertygridPanel.update(tmp.data);
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
    var addFile = function () {

        var albumBucketName = Ext.getCmp('s3_albumBucketName').getValue();
        var bucketRegion = Ext.getCmp('s3_bucketRegion').getValue();
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
        var files = document.getElementById('fileuploadfieldMarketingAddcommunicationAttachfile-file').files;
        if (!files.length) {

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
            Key: fileName,
            Body: file,
            ACL: 'public-read'
        }, function (err, data) {

            if (err) {

                var img_src = Ext.BLANK_IMAGE_URL;
                return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location)) {
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
                                    Ext.getCmp('supporting').show();
                                    Ext.getCmp('supporting').disable();
                                    Ext.MessageBox.hide();
                                    Application.example.msg('Notification', 'File uploaded successfully.');
                                }
                            }
                });
                Ext.getCmp('s3filepath').setValue(data.Location);
            }
        });
    };
    return {
        Cache: {},
        initfContacts: function () {
            var panelId = 'panelMarketingfContact';
            var contact_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(contact_panel)) {
                contact_panel = marketingPanel(panelId);
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
            Ext.get('downloadContactsIframe').dom.src = modURL + '&op=loadEditData&crco_id=' + contact_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        }, insertCommunication: function (contactId) {
            var record = Ext.getCmp('gridMarketingViewcontacts').getSelectionModel().getSelected();
            var tappanell = Ext.getCmp('tabpanelMarketingContact');

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                waitMsg: 'Processing',
                method: 'POST',
                url: modURL + '&op=insertCommunicationData',
                params: {
                    contactId: contactId,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    if (tmp.success == true) {
                        Application.example.msg('Success', tmp.msg);
                        Ext.getCmp('gridMarketingViewcontacts').getStore().reload();
                            Ext.getCmp('tabpanelMarketingContact').setActiveTab(0);
                            Application.Crm_FContact.Cache.crco_id = 0;
//                        function (btn) {
//                            Ext.getCmp('gridMarketingViewcontacts').getStore().reload();
//                            Ext.getCmp('tabpanelMarketingContact').setActiveTab(0);
//                            Application.Crm_FContact.Cache.crco_id = 0;
//                        });
                    } else {
                        Ext.MessageBox.alert('Error', 'Invalid data');
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
//            }
        }, insertData: function () {
            var _addContactData = Ext.getCmp('formpanelAddContact');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            _addContactData.getForm().submit({
                waitMsg: "saving.... ",
                url: modURL + '&op=insertAddContactData',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                },
                success: function (form, action) {
                    var res = Ext.decode(action.response.responseText);
                    if (res.success === true) {
                        Application.example.msg('Success', res.msg);
                        Ext.getCmp('add_contactWindow').close();
                            Ext.getCmp('gridMarketingViewcontacts').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            if (Application.Crm_FContact.Cache.upcrocid > 0) {
                                                var gridPanel = Ext.getCmp('gridMarketingViewcontacts');
                                                var index = gridPanel.store.find('crco_id', Application.Crm_FContact.Cache.upcrocid);
                                                gridPanel.getSelectionModel().selectRow(index);
                                            }
                                        }
                                    }
                            );
//                        function (btn) {
//                            Ext.getCmp('add_contactWindow').close();
//                            Ext.getCmp('gridMarketingViewcontacts').getStore().load(
//                                    {
//                                        callback: function (record, options, success) {
//                                            if (Application.Crm_FContact.Cache.upcrocid > 0) {
//                                                var gridPanel = Ext.getCmp('gridMarketingViewcontacts');
//                                                var index = gridPanel.store.find('crco_id', Application.Crm_FContact.Cache.upcrocid);
//                                                gridPanel.getSelectionModel().selectRow(index);
//                                            }
//                                        }
//                                    }
//                            );
//                        };
                    }
                    else{
                        Ext.MessageBox.alert('Success', res.msg);
                    }
                },
                failure: function (form, action) {
                    var res = Ext.decode(action.response.responseText);
                    Ext.MessageBox.alert('Error', res.errors.msg);
                }
            })
        }
    }
}();


