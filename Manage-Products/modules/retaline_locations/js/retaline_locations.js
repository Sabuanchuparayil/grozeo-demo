
Application.Retaline_Locations = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_locations';
    var RECS_PER_PAGE = 12;

    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };

    var gridSelectionChangedstates = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewStatedata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewStatedata').getSelectionModel().getSelections()[0].data.st_ID;
            Application.Retaline_Locations.ViewStates(ID);
        }
    };

    var gridSelectionChangeddistrict = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getSelectionModel().getSelections()[0].data.dst_Id;
            Application.Retaline_Locations.ViewDistricts(ID);
        }
    };

    var gridSelectionChangedcountry = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getSelectionModel().getSelections()[0].data.country_id;
            Application.Retaline_Locations.ViewCountry(ID);
        }
    };

    var saveDistrictData =  function () {
        if (!Ext.isEmpty(Ext.getCmp('dst_Name').getValue() && Ext.getCmp('comboMasterDistrictStatus').getValue())) {
            Ext.Ajax.request({
                    url: modURL + '&op=saveDistrict',
                    method: 'POST',
                    params: {
                        id: Ext.getCmp('dst_Id').getValue(),
                        st_ID: Ext.getCmp('textfieldstateid').getValue(),
                        name: Ext.getCmp('dst_Name').getValue(),
                        status: Ext.getCmp('comboMasterDistrictStatus').getValue(),
                        country_id: Ext.getCmp('textfieldcountryid').getValue()
                    },
                    success: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);
                            if (Application.Retaline_Locations.DistrictAddEdit == 'Add') {
                                RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewAddDistrictdata'));
                                Ext.getCmp('formpanelMasterDistrict').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: RECS_PER_PAGE,
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getStore().reload();
                                Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getView().refresh();
                            }
                            Application.Retaline_Locations.DistrictAddEdit = '';

                            Application.Retaline_Locations.ViewDistricts(tmp.data.dst_Id);
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else {
                            Ext.Msg.alert("Error", tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Error', result.message);
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }
                    }

            });
        } else {
            Ext.MessageBox.alert("Notification", 'Please fill required data');
        }

    };

    var saveStateData = function () {
        if (!Ext.isEmpty(Ext.getCmp('st_name').getValue() && Ext.getCmp('state_code').getValue() && Ext.getCmp('comboMasterStateStatus').getValue())) {
            Ext.Ajax.request({
                    url: modURL + '&op=saveStates',
                    method: 'POST',
                    params: {
                        id: Ext.getCmp('st_ID').getValue(),
                        name: Ext.getCmp('st_name').getValue(),
                        code: Ext.getCmp('state_code').getValue(),
                        status: Ext.getCmp('comboMasterStateStatus').getValue(),
                        country_id: Ext.getCmp('textfieldcountryid').getValue()
                    },
                    success: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);
                            if (Application.Retaline_Locations.StateAddEdit == 'Add') {
                                RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewStatedata'));
                                Ext.getCmp('formpanelMasterState').getForm().reset();
                                Ext.getCmp('gridpanelMasterDataviewStatedata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: RECS_PER_PAGE
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterDataviewStatedata').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterDataviewStatedata').getStore().reload();
                                Ext.getCmp('gridpanelMasterDataviewStatedata').getView().refresh();
                            }
                            Application.Retaline_Locations.StateAddEdit = '';
                            Application.Retaline_Locations.ViewStates(tmp.data.st_ID);
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else {
                            Ext.Msg.alert("Error", tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Error', result.message);
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }
                    }

            });
        } else {
            Ext.MessageBox.alert("Notification", 'Please fill required data');
        }

        };

        var saveCountryData =  function () {
            if (!Ext.isEmpty(Ext.getCmp('textfieldBrmlocationCountryName').getValue() && 
                Ext.getCmp('textfieldBrmlocationCountryCode').getValue() && 
                Ext.getCmp('comboBrmlocationStatus').getValue())) {
                Ext.Ajax.request({
                        url: modURL + '&op=saveCountryDetails',
                        method: 'POST',
                        params: {
                            id: Ext.getCmp('country_id').getValue(),
                            name: Ext.getCmp('textfieldBrmlocationCountryName').getValue(),
                            code: Ext.getCmp('textfieldBrmlocationCountryCode').getValue(),
                            status: Ext.getCmp('comboBrmlocationStatus').getValue()
                            
                        },
                        success: function (response) {
                            var tmp = Ext.decode(response.responseText);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.example.msg('Success', tmp.message);
                                if (Application.Retaline_Locations.StateAddEdit == 'Add') {
                                    RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('gridpanelBrmLocationDataviewCountrydata'));
                                    Ext.getCmp('formpanelBrmLocationCountry').getForm().reset();
                                    Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').store.reload({
                                        params: {
                                            start: 0,
                                            limit: RECS_PER_PAGE
                                        }
                                    });
                                } else {
                                    Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').selModel.getSelected().data = tmp.data;
                                    Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getStore().reload();
                                    Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getView().refresh();
                                }
                                Application.Retaline_Locations.StateAddEdit = '';
                                Application.Retaline_Locations.ViewCountry(tmp.data.country_id);
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
                            Ext.MessageBox.alert('Error', tmp.message);
                        }

                })
            } else {
            Ext.MessageBox.alert("Notification", 'Please fill required data');
        }    

    };

    var MasterPanelforState = function () {
        var country_id = arguments[0];
        var gridId = 1;
        var _mpanelforState = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            id: 'brm_locationmasterPanelforState',
            items: [StateMainGrid(country_id, gridId), new Ext.Panel({
                    title: 'State Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.3,
                    autoScroll: false,
                    cls: 'left_side_panel',
                    id: 'panelMasterStateParent',
                    height: winsize.height * 0.5,
                    items: [
                    {
                        region: 'north',
                        height: 150,
                        items: [
                            StateForm(), StateMasterDetailsView()
                        ]

                    },
                    {
                        region: 'center',
                        layout: 'fit',
                        height : 300,
                        items: DistrictMasterPanelView()
                    } 
                    ]

                })
            ]
        });
        return _mpanelforState;
    };

    var StateForm = function () {
        var _stateFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterState',
            frame: false,
            border: false,
            hidden: true,
            height:200,
            //autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'left',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'State Name',
                    id: 'st_name',
                    name: 'n[st_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 1,
                    maxValue: 100,
                    maxLength: 20
                },
                {
                    xtype: 'textfield',
                    id: 'st_ID',
                    name: 'n[st_ID]',
                    hidden: true
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'State Code',
                    id: 'state_code',
                    name: 'n[state_code]',
                    anchor: '98%',
                    allowBlank: false,
                    maxValue: 100,
                    tabIndex: 2,
                    maxLength: 20
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 3,
                    "emptyText": "Set status..",
                    "id": 'comboMasterStateStatus'
                })
            ],
            buttonAlign: 'right',
            fbar: [
                {
                    text: "Cancel",
                    tabIndex: 5,
                    cls: 'left-right-buttons',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    id: 'buttonMasterStateCancel',
                    hidden: true,
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewStatedata').getSelectionModel().getSelections())) {
                            var ID = Ext.getCmp('gridpanelMasterDataviewStatedata').getSelectionModel().getSelections()[0].data.st_ID;
                            Application.Retaline_Locations.ViewStates(ID);
                        }
                    }
                },/*<?php if (user_access("retaline_locations", "saveStates")) { ?> */
                {
                    text: "Save",
                    tabIndex: 4,
                    cls: 'left-right-buttons',
                    id: 'buttonMasterStateSave',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    hidden: true,
                    handler: function () {
                        var _cnval = Ext.getCmp('textfieldcountryid').getValue();
                        saveStateData();
                    }

                } /*<?php } ?> */
                
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('st_ID').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterStateStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterStateStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _stateFormPanel;
    };

    var StateMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            //autoHeight: true,
            height: 140,
            id: 'panelMasterStateDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">State Name </th><td>  {st_name} </td></tr>',
                    '<tr><th width="40%">State Code </th><td>{state_code}  </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>'),
            buttonAlign: 'right',
            fbar: [/*<?php if (user_access("retaline_locations", "saveStates")) { ?> */
                {
                    text: "Edit",
                    cls: 'left-right-buttons',
                    id: 'buttonMasterStateEdit',
                    //icon: IMAGE_BASE_PATH + '/default/icons/mem_edit.png',
                    icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                    tabIndex: 4,
                    handler: function () {
                        var ID = Ext.getCmp('gridpanelMasterDataviewStatedata').getSelectionModel().getSelections()[0].data.st_ID;
                        Application.Retaline_Locations.EditStatesView(ID);
                    }
                }/*<?php } ?> */

            ]
        });
    };

    var StateMasterStore = function () {
        var country_id = arguments[0];
        var gridId = arguments[1];
        var _stateMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listStates',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'st_ID',
                root: 'data'
            }, ['st_ID', 'st_name', 'state_code', 'status', 'location']),
            sortInfo: {
                field: 'st_ID',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    if (gridId == 1)
                    {
                        Ext.getCmp('gridpanelMasterDataviewStatedata').getView().refresh();
                        Ext.getCmp('gridpanelMasterDataviewStatedata').getSelectionModel().selectRow(0);
                    }
                    else
                    {
                        Ext.getCmp('gridpanelBrmLocationDataviewStatedata').getView().refresh();
                        Ext.getCmp('gridpanelBrmLocationDataviewStatedata').getSelectionModel().selectRow(0);
                    }
                }
            },
            baseParams: {
                country_id: country_id
            }

        });

        return _stateMasterStore;
    };

    var StateMainGrid = function () {
        var country_id = arguments[0];
        var gridId = arguments[1];
        var _stateStore = StateMasterStore(country_id, gridId);
        var _stateGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'st_name'

                }, {
                    type: 'string',
                    dataIndex: 'state_code'
                }, {
                    type: 'string',
                    dataIndex: 'status'
                },{
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }]
        });
        _stateGridFilter.remote = true;
        _stateGridFilter.autoReload = true;
        var _statemaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _stateStore,
            iconCls: 'money',
            id: 'gridpanelMasterDataviewStatedata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _stateGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'State Name',
                    dataIndex: 'st_name',
                    sortable: true,
                    tooltip: 'State Name',
                    hideable: false
                },
                {
                    header: 'State Code',
                    dataIndex: 'state_code',
                    sortable: true,
                    tooltip: 'State Code',
                    hideable: false
                },
                {
                    header: 'No. of Districts',
                    dataIndex: 'location',
                    sortable: true,
                    tooltip: 'No. of Districts',
                    hideable: false
                },
                {
                    header: 'status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _stateStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedstates
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);

                    Ext.getCmp('textfieldstateid').setValue(record.data.st_ID);

                    var ID = record.get('st_ID');
                    if (!Ext.isEmpty(ID)) {
                        Application.Retaline_Locations.Cache.st_ID = ID;
                        Ext.getCmp('formpanelMasterState').hide();
                        Application.Retaline_Locations.ViewStates(ID);
                    }

                },
                resize: onGridResize,
                afterrender: function () {
                    _stateStore.load();
                    if (_stateStore.getCount() == 0)
                    {
                        Ext.getCmp('buttonMasterStateEdit').hide();
                    }

                }
            },
            tbar: [{
                    text: 'Create State',
                    tooltip: 'Create State',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Retaline_Locations.StateAddEdit = 'Add';
                        var stateForm = Ext.getCmp('formpanelMasterState').getForm();
                        Ext.getCmp('panelMasterStateParent').setTitle('Create State Details');
                        loadedForm = null;
                        stateForm.reset();
                        Ext.getCmp('st_name').focus(false, 100);
                        Ext.getCmp('buttonMasterStateEdit').hide();
                        Ext.getCmp('buttonMasterStateSave').show();
                        Ext.getCmp('buttonMasterStateCancel').show();
                        Ext.getCmp('formpanelMasterState').show();
                        Ext.getCmp('panelMasterStateDetailsView').hide();
                        Ext.getCmp('panelMasterStateParent').doLayout();
                    }
                }]
        });
        return _statemaingridPanel;
    };



    var DistrictForm = function () {
        var _stateFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterDistrict',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            hidden: true,
                    labelWidth: 120,
            labelAlign: 'left',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'District Name',
                    id: 'dst_Name',
                    name: 'n[dst_Name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 500,
                    maxValue: 100,
                    maxLength: 20
                },
                {
                    xtype: 'textfield',
                    id: 'dst_Id',
                    name: 'n[dst_Id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 506,
                    "emptyText": "Set status..",
                    "id": 'comboMasterDistrictStatus'
                })

            ],
            buttonAlign: 'right',
            fbar: [{
                    text: "Cancel",
                    tabIndex: 5,
                    cls: 'left-right-buttons',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    id: 'buttonMasterDistrictCancel',
                    hidden: true,
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getSelectionModel().getSelections())) {
                            var ID = Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getSelectionModel().getSelections()[0].data.dst_Id;
                            Application.Retaline_Locations.ViewDistricts(ID);
                        }
                    }
                }, /*<?php if (user_access("retaline_locations", "saveDistrict")) { ?> */
                {
                    text: "Save",
                    tabIndex: 4,
                    cls: 'left-right-buttons',
                    id: 'buttonMasterDistrictSave',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    hidden: true,
                    handler: function () {
                        var _val = Ext.getCmp('textfieldstateid').getValue();
                        var _cnval = Ext.getCmp('textfieldcountryid').getValue();
                        saveDistrictData();
                    }
                } /*<?php } ?> */
                
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('dst_Id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterDistrictStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterDistrictStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _stateFormPanel;
    };



    var DistrictMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            height: winsize.height * 0.2,
            id: 'panelMasterDistrictDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">District Name </th><td>  {dst_Name} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>'),
            buttonAlign: 'right',
            fbar: [/*<?php if (user_access("retaline_locations", "saveDistrict")) { ?> */
                {
                    text: "Edit",
                    cls: 'left-right-buttons',
                    id: 'buttonMasterDistrictEdit',
                    //icon: IMAGE_BASE_PATH + '/default/icons/mem_edit.png',
                    icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                    tabIndex: 4,
                    handler: function () {
                        var ID = Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getSelectionModel().getSelections()[0].data.dst_Id;
                        Application.Retaline_Locations.EditDistrictView(ID);
                    }
                }
                /*<?php } ?> */

            ]
        });
    };

    var DistrictMasterDistrictView = function () {
        var st_ID = arguments[0];
        var _districtStore = DistrictAddMasterStore(st_ID);
        var _districtGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'dst_Name'

               },
                {
                    type: 'string',
                    dataIndex: 'status'
                },{
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }]
        });
        _districtGridFilter.remote = true;
        _districtGridFilter.autoReload = true;
        var _districtmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _districtStore,
            iconCls: 'money',
            autoScroll: true,
            height: winsize.height * 0.3,
            id: 'gridpanelMasterDataviewDistrictdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _districtGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'District Name',
                    dataIndex: 'dst_Name',
                    sortable: true,
                    tooltip: 'District Name',
                    hideable: false
                },
                {
                    header: 'status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    _districtStore.load();
                }
            }
        });
        return _districtmaingridPanel;
    };

    var DistrictAddWindow = function () {
        var st_ID = arguments[0];
        var country_id = arguments[1];
        var po_add_form = masterPanelforDistrict(st_ID, country_id);
        var resultWindow = new Ext.Window({
            id: 'windowbrmlocationsDataentryAdddistrict',
            title: 'Create District',
            iconCls: 'my-icon444',
            shadow: false,
            height: 500,
            width: 1000,
            layout: 'fit',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: true,
            items: [po_add_form],
            listeners: {
                close: function () {
                    Ext.getCmp('gridpanelMasterDataviewDistrictdata').getStore().reload();
                    Ext.getCmp('gridpanelMasterDataviewDistrictdata').getView().refresh();
                }
            }
        });
        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };

    var masterPanelforDistrict = function () {
        var st_ID = arguments[0];
        var country_id = arguments[1];
        var gridId = 1;
        var _mpanelforState = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            id: 'brm_locationMasterPanelforDistrict',
            items: [DistrictMasterAddDistrictView(st_ID, country_id, gridId), new Ext.Panel({
                    title: 'District Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.3,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterDistricAddParent',
                    autoHeight: false,
                    height: winsize.height * 0.5,
                    items: [
                        DistrictForm(), DistrictMasterDetailsView()
                    ]
                
                })
            ]
        });
        return _mpanelforState;
    };

    var DistrictMasterAddDistrictView = function () {
        var st_ID = arguments[0];
        var country_id = arguments[1];
        var gridId = 1;
        var _districtStore = DistrictAddMasterStore(st_ID, country_id, gridId);
        var _districtGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'dst_Name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }]
        });
        _districtGridFilter.remote = true;
        _districtGridFilter.autoReload = true;
        var _districtmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _districtStore,
            iconCls: 'money',
            autoScroll: true,
            width: winsize.width * 0.4,
            height: 500,
            id: 'gridpanelMasterDataviewAddDistrictdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _districtGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'District Name',
                    dataIndex: 'dst_Name',
                    sortable: true,
                    tooltip: 'District Name',
                    hideable: false
                },
                {
                    header: 'status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            tbar: [{
                    text: 'Create District',
                    tooltip: 'Create District',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Retaline_Locations.DistrictAddEdit = 'Add';
                        var districtForm = Ext.getCmp('formpanelMasterDistrict').getForm();
                        Ext.getCmp('panelMasterDistricAddParent').setTitle('Create District Details');
                        loadedForm = null;
                        districtForm.reset();
                        Ext.getCmp('dst_Name').focus(false, 100);
                        Ext.getCmp('buttonMasterDistrictEdit').hide();
                        Ext.getCmp('buttonMasterDistrictSave').show();
                        Ext.getCmp('buttonMasterDistrictCancel').show();
                        Ext.getCmp('formpanelMasterDistrict').show();
                        Ext.getCmp('panelMasterDistrictDetailsView').hide();
                        Ext.getCmp('panelMasterDistricAddParent').doLayout();
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _districtStore,
                displayInfo: true,
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            listeners: {
                afterrender: function () {
                    _districtStore.load();
                    Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getSelectionModel().selectRow(0);
                    if (_districtStore.getCount() == 0) {
                        Ext.getCmp('buttonMasterDistrictEdit').hide();
                    }
                }
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangeddistrict
                }
            })
        });
        return _districtmaingridPanel;
    };

    var DistrictAddMasterStore = function () {
        var st_ID = arguments[0];
        var country_id = arguments[1];
        var gridId = arguments[2];
        var _districtMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listDistrictsAdddata',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'dst_Id',
                root: 'data'
            }, ['dst_Id', 'dst_Name', 'status']),
            sortInfo: {
                field: 'dst_Id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    if (gridId == 1)
                    {
                        Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getView().refresh();
                        Ext.getCmp('gridpanelMasterDataviewAddDistrictdata').getSelectionModel().selectRow(0);
                    }
                    else
                    {
                        Ext.getCmp('gridpanelMasterDataviewDistrictdata').getView().refresh();
                        Ext.getCmp('gridpanelMasterDataviewDistrictdata').getSelectionModel().selectRow(0);

                    }
                }
            },
            baseParams: {
                st_ID: st_ID,
                country_id: country_id
            }
        });
        return _districtMasterStore;
    };

    var DistrictMasterPanelView = function () {
        var _districtMasterPanelView = new Ext.Panel({
            title: 'District Details',
            frame: false,
            border: true,
            region: 'east',
            width: winsize.width * 0.4,
            cls: 'left_side_panel',
            id: 'panelMasterDistrictParent',
            height: winsize.height * 0.5,
            tools: [{
                    id: 'plus',
                    qtip: 'Create District',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        var _val = Ext.getCmp('textfieldstateid').getValue();
                        var _stval = Ext.getCmp('textfieldcountryid').getValue();
                        DistrictAddWindow(_val, _stval);
                    }
                }],
            items: [
                {
                    xtype: 'textfield',
                    id: 'textfieldstateid',
                    name: 'textfieldstateid',
                    hidden: true
                },
                DistrictMasterDistrictView()
            ]
        })
        return _districtMasterPanelView;
    };

    var StateAddWindow = function ()
    {
        var country_id = arguments[0];
        var po_add_form = MasterPanelforState(country_id);
        var resultWindow = new Ext.Window({
            id: 'windowbrmlocationsDataentryAddState',
            title: 'Create State',
            iconCls: 'my-icon444',
            shadow: false,
            height: 500,
            width: 1000,
            layout: 'fit',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: true,
            items: [po_add_form],
            listeners: {
                close: function () {
                    Ext.getCmp('gridpanelBrmLocationDataviewStatedata').getStore().reload();
                    Ext.getCmp('gridpanelBrmLocationDataviewStatedata').getView().refresh();
                    Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getStore().reload();
                    Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getView().refresh();

                }
            }
        });
        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };

    var CountryForm = function () {
        var _countryFormPanel = new Ext.form.FormPanel({
            id: 'formpanelBrmLocationCountry',
            frame: false,
            border: false,
            hidden: true,
            //height:150,
            autoHeight: true,
            autoScroll: true,
            hidden: true,
            labelWidth: 120,
            labelAlign: 'left',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Country Name',
                    id: 'textfieldBrmlocationCountryName',
                    name: 'n[country_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 500,
                    maxValue: 100,
                    maxLength: 20
                },
                {
                    xtype: 'textfield',
                    id: 'country_id',
                    name: 'n[country_id]',
                    hidden: true
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Country Code',
                    id: 'textfieldBrmlocationCountryCode',
                    name: 'n[country_code]',
                    anchor: '98%',
                    allowBlank: false,
                    maxValue: 100,
                    tabIndex: 501,
                    maxLength: 20
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 506,
//                    "editable" : false,
//                    "typeAhead": true,
//                    "triggerAction": "all",
//                    "lazyRender":true,
//                    "mode": "local",
                    "emptyText": "Set status..",
                    "id": 'comboBrmlocationStatus'
                })

            ],
            buttonAlign: 'right',
            fbar: [{
                    text: "Cancel",
                    tabIndex: 5,
                    cls: 'left-right-buttons',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    id: 'buttonBrmLocationCountryCancel',
                    hidden: true,
                    handler: function () {
                        //Ext.getCmp('formpanelBrmLocationCountry').getForm().reset();
                        if (!Ext.isEmpty(Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getSelectionModel().getSelections())) {
                            var ID = Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getSelectionModel().getSelections()[0].data.country_id;
                            Application.Retaline_Locations.ViewCountry(ID);
                        }
                    }
                },
                /*<?php if (user_access("retaline_locations", "saveCountryDetails")) { ?> */
                {
                    text: "Save",
                    tabIndex: 4,
                    cls: 'left-right-buttons',
                    id: 'buttonBrmLocationCountrySave',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    hidden: true,
                    handler: function () {
                        saveCountryData();
                    }

                } /*<?php } ?> */
                
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('country_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboBrmlocationStatus').getStore().getAt(0);
                        Ext.getCmp('comboBrmlocationStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _countryFormPanel;
    };

    var CountryMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            //height: 150,
            id: 'panelBrmLocationCountryDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Country Name </th><td>  {country_name} </td></tr>',
                    '<tr><th width="40%">Country Code </th><td>{country_code}  </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>'),
            buttonAlign: 'right',
            fbar: [/*<?php if (user_access("retaline_locations", "saveCountryDetails")) { ?> */
                {
                    text: "Edit",
                    cls: 'left-right-buttons',
                    id: 'buttonBrmLocationCountryEdit',
                    //icon: IMAGE_BASE_PATH + '/default/icons/mem_edit.png',
                    icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                    tabIndex: 4,
                    handler: function () {

                        var ID = Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getSelectionModel().getSelections()[0].data.country_id;
                        Application.Retaline_Locations.EditCountryView(ID);
                    }
                }
                /*<?php } ?> */
            ]
        });
    };

    var CountryBrmStore = function () {
        var _countryStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCountry',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'country_id',
                root: 'data'
            }, ['country_id', 'country_name', 'country_code', 'status', 'location', 'districts']),
            sortInfo: {
                field: 'country_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getView().refresh();
                    Ext.getCmp('gridpanelBrmLocationDataviewCountrydata').getSelectionModel().selectRow(0);
                }
            }
        });

        return _countryStore;
    };

    var CountryMainGrid = function () {
        var _countryStore = CountryBrmStore();
        var _countryGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'country_name'

                }, {
                    type: 'string',
                    dataIndex: 'country_code'
                }, {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }]
        });
        _countryGridFilter.remote = true;
        _countryGridFilter.autoReload = true;
        var __countrymaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _countryStore,
            iconCls: 'money',
            id: 'gridpanelBrmLocationDataviewCountrydata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _countryGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Country Name',
                    dataIndex: 'country_name',
                    sortable: true,
                    tooltip: 'Country name',
                    hideable: false
                },
                {
                    header: 'Country Code',
                    dataIndex: 'country_code',
                    sortable: true,
                    tooltip: 'Country code',
                    hideable: false
                },
                {
                    header: 'No. of States',
                    dataIndex: 'location',
                    align:'right',
                    sortable: true,
                    tooltip: 'No. of states',
                    hideable: false
                },
                {
                    header: 'No. of Locations',
                    dataIndex: 'districts',
                    align:'right',
                    sortable: true,
                    tooltip: 'No.of locations',
                    hideable: false
                },
                {
                    header: 'status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _countryStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedcountry
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    Ext.getCmp('textfieldcountryid').setValue(record.data.country_id);
                    var ID = record.get('country_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.Retaline_Locations.Cache.country_id = ID;
                        Ext.getCmp('formpanelBrmLocationCountry').hide();
                        Application.Retaline_Locations.ViewCountry(ID);
                        Ext.getCmp('gridpanelBrmLocationDataviewStatedata').getStore().load({
                            params: {
                                country_id: ID
                            }
                        });
                    }

                },
                resize: onGridResize,
                afterrender: function () {
                    _countryStore.load();
                    if(_countryStore.getCount() == 0){
                        Ext.getCmp('buttonBrmLocationCountryEdit').hide();
                    }
                }
            },
            tbar: [{
                    text: 'Create Location',
                    tooltip: 'Create Location',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Retaline_Locations.StateAddEdit = 'Add';
                        var countryForm = Ext.getCmp('formpanelBrmLocationCountry').getForm();
                        Ext.getCmp('panelBrmLocationCountryParent').setTitle('Add Country Details');
                        loadedForm = null;
                        countryForm.reset();
                        Ext.getCmp('textfieldBrmlocationCountryName').focus(false, 100);
                        /*<?php if (user_access("retaline_locations", "saveCountryDetails")) { ?> */
                        Ext.getCmp('buttonBrmLocationCountryEdit').hide();
                        Ext.getCmp('buttonBrmLocationCountrySave').show();
                        Ext.getCmp('buttonBrmLocationCountryCancel').show();
                        /*<?php } ?> */
                        Ext.getCmp('formpanelBrmLocationCountry').show();
                        Ext.getCmp('panelBrmLocationCountryDetailsView').hide();
                        Ext.getCmp('panelBrmLocationCountryParent').doLayout();
                    }
                }]
        });
        return __countrymaingridPanel;
    };

    var StateBrmLocationDetailsView = function () {

        var country_id = arguments[0];
        var _stateStore = StateMasterStore(country_id);
        var _stateGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'st_name'

                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }]
        });
        _stateGridFilter.remote = true;
        _stateGridFilter.autoReload = true;
        var _statemaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _stateStore,
            iconCls: 'money',
            height: winsize.height * 0.4,
            autoScroll: true,
            id: 'gridpanelBrmLocationDataviewStatedata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _stateGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'State Name',
                    dataIndex: 'st_name',
                    sortable: true,
                    tooltip: 'State name',
                    hideable: false
                },
                {
                    header: 'status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    _stateStore.load();
                }
            }
        });
        return _statemaingridPanel;
    };

    var StateMasterPanelView = function () {
        var _stateMasterPanelView = new Ext.Panel({
            title: 'State Details',
            frame: false,
            border: true,
            region: 'east',
            width: winsize.width * 0.4,
            cls: 'left_side_panel',
            id: 'panelBrmLocationStateParent',
            height: winsize.height * 0.6,
            tools: [{
                    id: 'plus',
                    qtip: 'Create State',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        var _val = Ext.getCmp('textfieldcountryid').getValue();
                        StateAddWindow(_val);
                    }
                }
            ],
            items: [
                {
                    xtype: 'textfield',
                    id: 'textfieldcountryid',
                    name: 'textfieldcountryid',
                    hidden: true
                },
                StateBrmLocationDetailsView()
            ]

        })
        return _stateMasterPanelView;
    };

    var masterPanelforCountry = function (id) {
        var _mpanelforCountry = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Location',
            id: id,
            iconCls: 'my-icon444',
            items: [
                CountryMainGrid(),
                new Ext.Panel({
                    title: 'Country Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelBrmLocationCountryParent',
                    height: winsize.height * 0.6,
                    items: [
                    {
                        region: 'north',
                        height: 150,
                        items: [CountryForm(),CountryMasterDetailsView()]
                    },
                    {
                        region: 'center',
                        layout: 'fit',
                        items: StateMasterPanelView()
                    }
                        // CountryForm(),
                        // CountryMasterDetailsView(),
                        // StateMasterPanelView()

                    ]

                })
            ]
        });
        return _mpanelforCountry;
    };
    


    //    /////////////////////////////EO Private Area///////////////////////////////

    //    ///////////////////////////////////////////////////////////////////////////
    //    //-----------------------------------------------------------------------//
    //    //------------------------Event Definition Area--------------------------//

    //    //-----------------------------------------------------------------------//
    //    ///////////////////////////////////////////////////////////////////////////


    //    ///////////////////////////////////////////////////////////////////////////
    //    //-----------------------------------------------------------------------//
    //    //-----------------------------Public Area-------------------------------//
    //    //-----------------------------------------------------------------------//
    //    //---------------------------Public Variables ---------------------------//
    //    //----------------------------Public Methods-----------------------------//
    return {
        Cache: {},
        
        
        EditDistrictView: function () {
            Application.Retaline_Locations.DistrictAddEdit = 'Edit';
            Ext.getCmp('panelMasterDistricAddParent').doLayout();
            Ext.getCmp('panelMasterDistricAddParent').setTitle('Edit District Details');
            Ext.getCmp('formpanelMasterDistrict').show();
            Ext.getCmp('panelMasterDistricAddParent').show();
            Ext.getCmp('panelMasterDistrictDetailsView').hide();
            /*<?php if (user_access("retaline_locations", "saveDistrict")) { ?> */
            Ext.getCmp('buttonMasterDistrictEdit').hide();
            Ext.getCmp('buttonMasterDistrictSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterDistrictCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var hnsForm = Ext.getCmp('formpanelMasterDistrict').getForm();
                hnsForm.load({
                    params: {
                        dst_Id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=district_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        ViewDistricts: function () {
            var dst_Id = arguments[0];
            /*<?php if (user_access("retaline_locations", "saveDistrict")) { ?> */
            Ext.getCmp('buttonMasterDistrictEdit').show();
            Ext.getCmp('buttonMasterDistrictSave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterDistrictCancel').hide();
            Ext.getCmp('formpanelMasterDistrict').hide();
            Ext.getCmp('panelMasterDistrictDetailsView').show();
            Ext.getCmp('panelMasterDistricAddParent').show();
            Ext.getCmp('panelMasterDistricAddParent').doLayout();
            Ext.getCmp('panelMasterDistricAddParent').setTitle('View District Details');
            Ext.Ajax.request({
                url: modURL + '&op=districtdetailsView',
                method: 'POST',
                params: {dst_Id: dst_Id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterDistrictDetailsView');
                        visualsDescPanel.update(tmp);
                    }

                    Ext.getCmp('panelMasterDistricAddParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterDistricAddParent').doLayout();
        },
        ViewStates: function () {
            var st_ID = arguments[0];
            /*<?php if (user_access("retaline_locations", "saveStates")) { ?> */
            Ext.getCmp('buttonMasterStateEdit').show();
            Ext.getCmp('buttonMasterStateSave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterStateCancel').hide();
            Ext.getCmp('formpanelMasterState').hide();
            Ext.getCmp('panelMasterStateDetailsView').show();
            Ext.getCmp('panelMasterDistrictParent').show();
            Ext.getCmp('panelMasterStateParent').doLayout();
            Ext.getCmp('panelMasterStateParent').setTitle('View State Details');

            Ext.Ajax.request({
                url: modURL + '&op=statedetailsView',
                method: 'POST',
                params: {st_ID: st_ID},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterStateDetailsView');
                        visualsDescPanel.update(tmp);
                    }

                    var document_grid_store = Ext.getCmp('gridpanelMasterDataviewDistrictdata').getStore();
                    document_grid_store.load({
                        params: {
                            st_ID: st_ID

                        }
                    });
                    Ext.getCmp('textfieldstateid').setValue(st_ID);

                    Ext.getCmp('panelMasterStateParent').doLayout();

                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterStateParent').doLayout();
        },
        EditStatesView: function () {
            Application.Retaline_Locations.StateAddEdit = 'Edit';
            
            Ext.getCmp('panelMasterStateDetailsView').hide();
            Ext.getCmp('formpanelMasterState').show();
             /*<?php if (user_access("retaline_locations", "saveStates")) { ?> */
            Ext.getCmp('buttonMasterStateEdit').hide();
            Ext.getCmp('buttonMasterStateSave').show();
            /*<?php } ?> */
             Ext.getCmp('buttonMasterStateCancel').show();
            Ext.getCmp('panelMasterStateParent').doLayout();
            Ext.getCmp('panelMasterStateParent').setTitle('Edit State Details');
            Ext.getCmp('panelMasterDistrictParent').show();
            
            
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var hnsForm = Ext.getCmp('formpanelMasterState').getForm();
                hnsForm.load({
                    params: {
                        st_ID: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=state_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        initLocations: function () {
            var _CountryPanelId = 'panelMasterMainCountry';
            var _masterPanelCountry = Ext.getCmp(_CountryPanelId);
            if (Ext.isEmpty(_masterPanelCountry)) {
                _masterPanelCountry = masterPanelforCountry(_CountryPanelId);
                Application.UI.addTab(_masterPanelCountry);
                _masterPanelCountry.doLayout();
            } else {
                Application.UI.addTab(_masterPanelCountry);
            }
        },
        ViewCountry: function () {
            var country_id = arguments[0];
            /*<?php if (user_access("retaline_locations", "saveCountryDetails")) { ?> */

            Ext.getCmp('buttonBrmLocationCountryEdit').show();
            Ext.getCmp('buttonBrmLocationCountrySave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonBrmLocationCountryCancel').hide();
            Ext.getCmp('formpanelBrmLocationCountry').hide();
            Ext.getCmp('panelBrmLocationCountryDetailsView').show();
            Ext.getCmp('panelBrmLocationStateParent').show();
            Ext.getCmp('panelBrmLocationCountryParent').doLayout();
            Ext.getCmp('panelBrmLocationCountryParent').setTitle('View Country Details');

            Ext.Ajax.request({
                url: modURL + '&op=CountrydetailsView',
                method: 'POST',
                params: {country_id: country_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelBrmLocationCountryDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    var document_grid_store = Ext.getCmp('gridpanelBrmLocationDataviewStatedata').getStore();
                    document_grid_store.load({
                        params: {
                            country_id: country_id

                        }
                    });
                    Ext.getCmp('textfieldcountryid').setValue(country_id);
                    var country = Ext.getCmp('textfieldcountryid').getValue();

                    Ext.getCmp('panelBrmLocationCountryParent').doLayout();

                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelBrmLocationCountryParent').doLayout();
        },
        EditCountryView: function () {

            Application.Retaline_Locations.StateAddEdit = 'Edit';
            Ext.getCmp('panelBrmLocationCountryParent').doLayout();
            Ext.getCmp('panelBrmLocationCountryParent').setTitle('Edit Country details');
            Ext.getCmp('formpanelBrmLocationCountry').show();
            Ext.getCmp('panelBrmLocationStateParent').show();
            Ext.getCmp('panelBrmLocationCountryDetailsView').hide();
            /*<?php if (user_access("retaline_locations", "saveCountryDetails")) { ?> */
            Ext.getCmp('buttonBrmLocationCountryEdit').hide();
            Ext.getCmp('buttonBrmLocationCountrySave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonBrmLocationCountryCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var hnsForm = Ext.getCmp('formpanelBrmLocationCountry').getForm();
                hnsForm.load({
                    params: {
                        country_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=country_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        Ext.Msg.alert("Error.", tmp.msg);
                    }
                });
            }
        }

    };
}();