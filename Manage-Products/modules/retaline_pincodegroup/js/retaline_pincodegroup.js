Application.RetalinePincodeGroup = function () {
    var recs_per_page = 12;
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_pincodegroup';
    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }
    var proxy = new Ext.data.HttpProxy({
        url: modURL + '&op=listpincodegroup',
        method: 'post'
    });
    var pincodeStore = function () {
        var pincode_store = new Ext.data.JsonStore({
            proxy: proxy,
            fields: ['rpg_id', 'rpg_name', 'rpg_pincode', 'rpg_createdOn'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'rpg_id',
            // turn on remote sorting
            remoteSort: true
        });
        return pincode_store;
    };
    var pincodeAction = function () {
        var action = new Ext.ux.grid.RowActions({
            hideMode: 'display',
            autoWidth: false,
            width: 30,
            actions: [/* <?php if (user_access("retaline_pincodegroup", "savePincode")) { ?> */{
                    sortable: false,
                    tooltip: 'Edit Pincode Group',
                    iconCls: 'finascop_edit',
                    callback: function (grid, rec, row, col) {
                        Application.RetalinePincodeGroup.addPincodeGroup(rec.data.rpg_id);
                    }
                }, {
                    sortable: false,
                    tooltip: 'Add Time Range',
                    iconCls: 'my-icon32',
                    hide: false,
                    callback: function (grid, rec, row, col) {
                        Application.RetalinePincodeGroup.addTimeRange(rec.data.rpg_id, rec.data.rpg_name, rec.data.rpg_pincode);
                    }
                }/* <?php } ?> */]
        });
        return action;
    };
    function pincodeSelect() {
        return  new Ext.grid.RowSelectionModel({
            singleSelected: true
        });
    }
    var createPincodeGroupGrid = function () {

        var pincode_store = pincodeStore();
        var pincode_select = pincodeSelect();
        var action = pincodeAction();
        /**
         * var pincodefilters = Plugin used for the  pincode_grid
         **/
        var pincodefilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'rpg_name'

                }, {
                    type: 'string',
                    dataIndex: 'rpg_pincode'

                }]
        });
        pincodefilters.remote = true;
        pincodefilters.autoReload = true;


        var pincode_grid = new Ext.grid.GridPanel({
            store: pincode_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true,
                //Condition checking and Assign color to row  
                //Assign color to Actve Pincode.
//                getRowClass: function (record, index) {
//
//                    if (record.data.is_Active == 'N')
//                    {
//                        return '';
//                    } else
//                        return 'indicateCOL';
//                }
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [action, pincodefilters],
            title: 'Pincode Group',
            iconCls: 'my-icon95',
            id: 'retaline_pincodegroupgrid',
            columns: [{
                    header: 'GroupName',
                    id: 'rpg_name',
                    sortable: true,
                    dataIndex: 'rpg_name'


                }, {
                    header: 'Pincode Attached',
                    id: 'rpg_pincode',
                    sortable: true,
                    dataIndex: 'rpg_pincode'
                }
                , action],
            sm: pincode_select,
            listeners: {
                resize: updatePagination
            },
            /* <?php if (user_access("retaline_pincodegroup", "savePincode")) { ?> */
            tbar: [{
                    text: 'Add New',
                    tooltip: 'Add New Pincode Group',
                    id: 'pincode_save_button',
                    icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.RetalinePincodeGroup.addPincodeGroup();
                    }
                }], /* <?php } ?> */
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: pincode_store,
                displayInfo: true,
                plugins: [pincodefilters],
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'rpg_name'

        });
        return pincode_grid;
    };
    var loadPincodeGroup = function () {
        Ext.getCmp('retaline_pincodegroupgrid').getStore().setDefaultSort('rpg_id', 'asc');
        Ext.getCmp('retaline_pincodegroupgrid').getStore().removeAll();
        Ext.getCmp('retaline_pincodegroupgrid').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    };
    var addpincodeFrm = function () {
        var formPincode = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'pincodeGroup_form',
            height: 200,
            labelAlign: 'top',
            items: [{
                    xtype: 'textfield',
                    id: 'rpg_name',
                    name: 'rpg_name',
                    allowBlank: false,
                    anchor: '90%',
                    fieldLabel: 'Group Name',
                    tabIndex: 400,
                    listeners: {
                        afterrender: function (field) {
                            Ext.defer(function () {
                                field.focus(true, 100);
                            }, 1);
                        }
                    }
                }, {
                    layout: 'column',
                    border: false,
                    items: [{
                            columnWidth: .70,
                            layout: 'form',
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Pincode',
                                    id: 'pincode_to',
                                    name: 'pincode_to',
                                    anchor: '98%',
                                    minLength: 6,
                                    maxLength: 6,
                                    //allowBlank: false,
                                    tabIndex: 401
                                }]
                        }, {
                            columnWidth: .30,
                            layout: 'form',
                            border: false,
                            items: [{
                                    xtype: 'spacer',
                                    height: 18
                                }, {
                                    xtype: 'button',
                                    text: 'Add Pincodes',
                                    tabIndex: 402,
                                    fieldLabel: '',
                                    iconCls: 'finascop_add',
                                    //style: 'margin-left:5px',
                                    handler: function () {
                                        var pincode_to = Ext.getCmp('pincode_to').getValue();
                                        if (pincode_to > 0) {
                                            var rpg_pincode = Ext.getCmp('rpg_pincode').getValue();
                                            var numbers = rpg_pincode.split(',');
                                            if (rpg_pincode.length > 0) {
                                                numbers.push(pincode_to);
                                                rpg_pincode = numbers.join(',');
                                                Ext.getCmp('rpg_pincode').setValue(rpg_pincode);
                                            } else {
                                                Ext.getCmp('rpg_pincode').setValue(pincode_to);
                                            }
                                            Ext.getCmp('pincode_to').reset();
                                            Ext.getCmp('pincode_to').focus();
                                        } else {
                                            Ext.MessageBox.alert("Notification", 'Not added pincode');
                                        }



                                    }
                                }]
                        }]
                }, {
                    xtype: 'textarea',
                    id: 'rpg_pincode',
                    hideLabel: true,
                    name: 'rpg_pincode',
                    //allowBlank: false,
                    // vtype: 'phonespec',
                    anchor: '97%',
                    fieldLabel: '',
                    tabIndex: 403
                },
                {
                    xtype: 'hidden',
                    id: 'rpg_id',
                    name: 'rpg_id'
                }]

        });
        return formPincode;
    };
    var _pincodeGroupTimeSlotStore = function (id, name, picodes) {
        var _faqsStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPincodeGroupTimeSlotStore',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'rpgtr_id',
                root: 'data'
            }, ['rpgtr_id', 'rpg_id', 'rpgtr_time_from', 'rpgtr_time_to', 'rpgtr_time_maxslot', 'rpgtr_time', 'rpg_name']),
            sortInfo: {
                field: 'rpgtr_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.rpg_id = id;
                }
            }
        });
        return _faqsStore;
    };
    var pincodeGroupTimeSlotGrid = function (id, name, picodes) {
        var _pincodeGroupTimeSlotridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'rpg_name'
                },
                {
                    type: 'string',
                    dataIndex: 'rpgtr_time'
                }

            ]
        });
        _pincodeGroupTimeSlotridFilter.remote = true;
        _pincodeGroupTimeSlotridFilter.autoReload = true;
        var _pincodeGroupTimeSlotGridStore = _pincodeGroupTimeSlotStore(id, name, picodes);
        var _faqgridPanel = new Ext.grid.GridPanel({
            store: _pincodeGroupTimeSlotGridStore,
            region: 'center',
            layout: 'fit',
            frame: true,
            border: true,
            //iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _pincodeGroupTimeSlotridFilter],
            id: 'pincodeGroupTimeSlotRangeMasterGrid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Time From',
                    sortable: true,
                    dataIndex: 'rpgtr_time_from',
                    tooltip: 'Time',
                    hideable: true
                }, {
                    header: 'Time To',
                    sortable: true,
                    dataIndex: 'rpgtr_time_to',
                    tooltip: 'Time',
                    hideable: true
                },
                {
                    header: 'Slot Group',
                    sortable: true,
                    dataIndex: 'rpg_name',
                    tooltip: 'Slot Group'
                },
                {
                    header: 'Slot / day',
                    dataIndex: 'rpgtr_time_maxslot',
                    sortable: true,
                    tooltip: 'Slot / day'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChangedfaq
                }
            }),
            listeners: {
                //resize: onGridResize,
                afterrender: function () {
                    _pincodeGroupTimeSlotGridStore.load();
                }
            },
            tbar: [],
            stripeRows: true,
        });
        return _faqgridPanel;
    };
    var timeRangePincodeGroupForms = function (id, name, picodes) {
        var _faqFormPanel = new Ext.form.FormPanel({
            frame: false,
            width: winsize.width * 0.3,
            border: true,
            region: 'east',
            title: 'Time Range settings - ' + name,
            labelAlign: 'top',
            autoHeight: true,
            labelWidth: 100,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            id: 'timeRangePincodeGroupMasterForm',
            items: [{
                    xtype: 'spacer',
                    height: 10
                }, {
                    xtype: 'textarea',
                    fieldLabel: '',
                    id: 'pincodes',
                    name: 'pincodes',
                    value: picodes,
                    anchor: '98%',
                    allowBlank: false,
                    editable: false,
                    tabIndex: 500,
                    maxValue: 100
                }, {
                    xtype: 'timefield',
                    fieldLabel: 'Time  From',
                    id: 'rpgtr_time_from',
                    name: 'rpgtr_time_from',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 501,
                    maxLength: 300
                }, {
                    xtype: 'timefield',
                    fieldLabel: 'Time  To',
                    id: 'rpgtr_time_to',
                    name: 'rpgtr_time_to',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 502,
                    maxLength: 300
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Max / Slot',
                    id: 'rpgtr_time_maxslot',
                    name: 'rpgtr_time_maxslot',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 503,
                    maxLength: 300
                }, {
                    xtype: 'hidden',
                    id: 'rpgtr_id',
                    name: 'rpgtr_id'
                },
            ], fbar: [{
                    xtype: 'button',
                    text: 'Save',
                    tabIndex: 402,
                    handler: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
                        if (slotForm.isValid()) {
                            slotForm.submit({
                                waitMsg: 'Saving...',
                                url: modURL,
                                params: {
                                    op: 'savePincodeGroupsTimeSlots',
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp,
                                    rpg_id: id
                                },
                                //for submit success
                                success: function (frm, action) {

                                    if (action && action.response.responseText) {
                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success == true) {
                                            Ext.MessageBox.alert("Notification", "Time slot saved.", function (btn) {
                                                if (btn == 'ok') {
                                                    Ext.getCmp('pincodeGroupTimeSlotRangeMasterGrid').getStore().load({
                                                        params: {
                                                            rpg_id: id
                                                        }
                                                    });
                                                    slotForm.reset();
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }
                                    }
                                },
                                //for submit failure
                                failure: function (frm, action) {
                                    if (action.failureType == 'server') {
                                        obj = Ext.util.JSON.decode(action.response.responseText);
                                        Ext.MessageBox.show({
                                            title: 'Error!',
                                            msg: (obj.msg) ? obj.msg : obj.error,
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.ERROR,
                                            width: 325
                                        });
                                    }
                                }
                            });
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }


                    }
                }],
            listeners: {
                afterrender: function () {
                }
            }
        });
        return _faqFormPanel;
    };
    var branchTimeSlotGrid = function (id, name, picodes) {
        var _pincodeGroupTimeSlotridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'rbds_time_from'
                }

            ]
        });
        _pincodeGroupTimeSlotridFilter.remote = true;
        _pincodeGroupTimeSlotridFilter.autoReload = true;
        var _branchTimeSlotGridStore = _branchTimeSlotStore(id, name, picodes);
        var _faqgridPanel = new Ext.grid.GridPanel({
            store: _branchTimeSlotGridStore,
            region: 'center',
            layout: 'fit',
            frame: true,
            border: true,
            //iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _pincodeGroupTimeSlotridFilter],
            id: 'branchTimeSlotRangeMasterGrid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Time From',
                    sortable: true,
                    dataIndex: 'rbds_time_from',
                    tooltip: 'Time',
                    hideable: true
                }, {
                    header: 'Time To',
                    sortable: true,
                    dataIndex: 'rbds_time_to',
                    tooltip: 'Time',
                    hideable: true
                },
                {
                    header: 'Slot / day',
                    dataIndex: 'rbds_time_maxslot',
                    sortable: true,
                    tooltip: 'Slot / day'
                }, {
                    xtype: 'actioncolumn',
                    //header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            timerangeActionMenu.showAt(e.getXY());
                        }
                    }
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    //selectionchange: gridSelectionChangedfaq
                }
            }),
            listeners: {
                //resize: onGridResize,
                afterrender: function () {
                    _branchTimeSlotGridStore.load();
                }
            },
            tbar: [{html: '&nbsp;Time From : &nbsp;'}, {
                    xtype: 'timefield',
                    fieldLabel: 'Time  From',
                    id: 'rbds_time_from',
                    name: 'rbds_time_from',
                    anchor: '98%',
                    allowBlank: false,
                    width: 100,
                    tabIndex: 501,
                }, {html: '&nbsp;Time To : &nbsp;'}, {
                    xtype: 'timefield',
                    fieldLabel: 'Time  To',
                    id: 'rbds_time_to',
                    name: 'rbds_time_to',
                    anchor: '98%',
                    allowBlank: false,
                    width: 100,
                    tabIndex: 502,
                }, {html: '&nbsp;Max / Slot : &nbsp;'}, {
                    xtype: 'numberfield',
                    fieldLabel: 'Max / Slot',
                    id: 'rbds_time_maxslot',
                    name: 'rbds_time_maxslot',
                    anchor: '98%',
                    allowBlank: false,
                    width: 100,
                    tabIndex: 503,
                    maxLength: 300
                }, {
                    xtype: 'hidden',
                    id: 'rbds_id',
                    name: 'rbds_id'
                }, {
                    xtype: 'button',
                    text: 'Add',
                    iconCls: 'add',
                    tabIndex: 504,
                    handler: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        //var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
                        if (!Ext.isEmpty(Ext.getCmp('rbds_time_from').getValue()) && !Ext.isEmpty(Ext.getCmp('rbds_time_to').getValue()) && !Ext.isEmpty(Ext.getCmp('rbds_time_maxslot').getValue())) {
                            Ext.Ajax.request({
                                url: modURL + '&op=saveBranchTimeSlots',
                                method: 'POST',
                                params: {
                                    branch_id: id,
                                    rbds_time_from: Ext.getCmp('rbds_time_from').getValue(),
                                    rbds_time_to: Ext.getCmp('rbds_time_to').getValue(),
                                    rbds_time_maxslot: Ext.getCmp('rbds_time_maxslot').getValue()

                                },
                                success: function (response) {

                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true)
                                    {
                                        Application.example.msg('Success', tmp.msg);
                                        Ext.getCmp('branchTimeSlotRangeMasterGrid').getStore().load({
                                            params: {
                                                branch_id: id
                                            }
                                        });
                                        Ext.getCmp('rbds_time_from').reset();
                                        Ext.getCmp('rbds_time_to').reset();
                                        Ext.getCmp('rbds_time_maxslot').reset();
                                    } else
                                    {
                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                    }

                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                }
                            });
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }


                    }
                }],
            stripeRows: true,
        });
        return _faqgridPanel;
    };
    var timerangeActionMenu = new Ext.menu.Menu({
        items: [{
                text: 'Delete',
                handler: function () {
                    var rbds_id = Ext.getCmp('branchTimeSlotRangeMasterGrid').getSelectionModel().getSelections()[0].data.rbds_id;
                    var branch_id = Ext.getCmp('branchTimeSlotRangeMasterGrid').getSelectionModel().getSelections()[0].data.branch_id;
                    Ext.MessageBox.confirm('Confirm', 'Do you want to remove this?', function (btn, text){
                        if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: modURL + '&op=deleteBranchTimeSlot',
                        method: 'POST',
                        params: {rbds_id: rbds_id},
                        success: function (response) {
                            var tmp = Ext.decode(response.responseText);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.example.msg('Success', tmp.message);
                                Ext.getCmp('branchTimeSlotRangeMasterGrid').getStore().load({
                                    params: {
                                        branch_id: branch_id
                                    }
                                });
                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.Msg.alert("Notification.", tmp.message);
                            } else if (tmp.success === true && tmp.img_valid === false) {
                                Ext.Msg.alert("Notification.", tmp.message);
                            } else {
                                Ext.Msg.alert("Error", tmp.message);
                            }
                        },
                        failure: function () {
                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                        }
                    });
                }
                });
                }
            }]
    });
    var timeRangeBranchForms = function (id, name, picodes) {
        var _faqFormPanel = new Ext.form.FormPanel({
            frame: false,
            width: winsize.width * 0.3,
            border: true,
            region: 'east',
            title: 'Time Range settings',
            labelAlign: 'top',
            autoHeight: true,
            labelWidth: 100,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            id: 'timeRangePincodeGroupMasterForm',
            items: [{
                    xtype: 'spacer',
                    height: 10
                }, {
                    xtype: 'timefield',
                    fieldLabel: 'Time  From',
                    id: 'rbds_time_from',
                    name: 'rbds_time_from',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 501,
                    maxLength: 300
                }, {
                    xtype: 'timefield',
                    fieldLabel: 'Time  To',
                    id: 'rbds_time_to',
                    name: 'rbds_time_to',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 502,
                    maxLength: 300
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Max / Slot',
                    id: 'rbds_time_maxslot',
                    name: 'rbds_time_maxslot',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 503,
                    maxLength: 300
                }, {
                    xtype: 'hidden',
                    id: 'rbds_id',
                    name: 'rbds_id'
                },
            ], fbar: [{
                    xtype: 'button',
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    tabIndex: 402,
                    handler: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        var slotForm = Ext.getCmp('timeRangePincodeGroupMasterForm').getForm();
                        if (slotForm.isValid()) {
                            slotForm.submit({
                                waitMsg: 'Saving...',
                                url: modURL,
                                params: {
                                    op: 'saveBranchTimeSlots',
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp,
                                    branch_id: id
                                },
                                //for submit success
                                success: function (frm, action) {

                                    if (action && action.response.responseText) {
                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success == true) {
                                            Ext.MessageBox.alert("Notification", "Time slot saved.", function (btn) {
                                                if (btn == 'ok') {
                                                    Ext.getCmp('branchTimeSlotRangeMasterGrid').getStore().load({
                                                        params: {
                                                            branch_id: id
                                                        }
                                                    });
                                                    slotForm.reset();
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }
                                    }
                                },
                                //for submit failure
                                failure: function (frm, action) {
                                    if (action.failureType == 'server') {
                                        obj = Ext.util.JSON.decode(action.response.responseText);
                                        Ext.MessageBox.show({
                                            title: 'Error!',
                                            msg: (obj.msg) ? obj.msg : obj.error,
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.ERROR,
                                            width: 325
                                        });
                                    }
                                }
                            });
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }


                    }
                }],
            listeners: {
                afterrender: function () {
                }
            }
        });
        return _faqFormPanel;
    };
    var _branchTimeSlotStore = function (id, name, picodes) {
        var _faqsStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listBranchTimeSlotStore',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'rbds_id',
                root: 'data'
            }, ['rbds_id', 'branch_id', 'rbds_time_from', 'rbds_time_to', 'rbds_time_maxslot', 'rpgtr_time', ]),
            sortInfo: {
                field: 'rbds_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.branch_id = id;
                }
            }
        });
        return _faqsStore;
    };
    return {
        init: function () {

            var retaline_pincodegroupgrid = Ext.getCmp('retaline_pincodegroupgrid');
            if (!retaline_pincodegroupgrid) {
                retaline_pincodegroupgrid = createPincodeGroupGrid();
            }
            loadPincodeGroup();
            Application.UI.addTab(retaline_pincodegroupgrid);
            retaline_pincodegroupgrid.doLayout();
        }, addPincodeGroup: function (pincode) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var pincode_winId = "pincodegroup_window";
            var pincode_window = Ext.getCmp(pincode_winId);
            if (Ext.isEmpty(pincode_window)) {
                var pincodefrm = addpincodeFrm();
                pincode_window = new Ext.Window({
                    id: 'pincode_window',
                    layout: 'fit',
                    width: 400,
                    title: 'Pincode Group',
                    autoHeight: true,
                    draggable: false,
                    //    iconCls: 'my-icon92',
                    iconCls: (pincode) ? 'my-icon99' : 'my-icon98',
                    //    iconCls: (pincode) ? 'my-icon95' : 'my-icon95',
                    plain: true,
                    constrain: true,
                    modal: true,
                    resizable: false,
                    items: [pincodefrm],
                    buttons: [{
                            text: 'Save',
                            id: 'btnsave',
                            tabIndex: 404,
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                pincodefrm.getForm().submit({
                                    waitMsg: 'Saving...',
                                    url: modURL,
                                    params: {
                                        op: 'savePincodeGroups',
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    //for submit success
                                    success: function (frm, action) {

                                        if (action && action.response.responseText) {
                                            var tmp = Ext.decode(action.response.responseText);
                                            if (tmp.success == true) {
                                                Ext.MessageBox.alert("Notification", "Pincde group saved.", function (btn) {
                                                    if (btn == 'ok') {
                                                        pincode_window.close();
                                                        loadPincodeGroup();
                                                    }
                                                });
                                            } else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }
                                        }
                                    },
                                    //for submit failure
                                    failure: function (frm, action) {
                                        if (action.failureType == 'server') {
                                            obj = Ext.util.JSON.decode(action.response.responseText);
                                            Ext.MessageBox.show({
                                                title: 'Error!',
                                                msg: (obj.msg) ? obj.msg : obj.error,
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.ERROR,
                                                width: 325});
                                        }
                                    }
                                });
                            }
                        }, {
                            text: 'Cancel',
                            id: 'btnCancel',
                            tabIndex: 405,
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                pincode_window.close();
                            }
                        }]
                });
            }
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(pincode)) {
                pincodefrm.load({
                    url: modURL + '&op=getPincodeGroup',
                    params: {
                        'rpg_id': pincode,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp

                    },
                    success: function (frm, action) {
                        var obj = Ext.decode(action.response.responseText);

                        pincode_window.setTitle("Edit Pincode Group");
                    }
                });
            }
            pincode_window.doLayout();
            pincode_window.show(this);
            pincode_window.center();
        }, addTimeRange: function (id, name, picodes) {

            var addTimeRangeToPincodeGroupWindow = Ext.getCmp('addTimeRangeToPincodeGroupWindow');
            if (Ext.isEmpty(addTimeRangeToPincodeGroupWindow)) {
                addTimeRangeToPincodeGroupWindow = new Ext.Window({
                    id: 'addTimeRangeToPincodeGroupWindow',
                    title: 'Time Range for Pincode Group',
                    layout: 'border',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    width: 1000,
                    height: 600,
                    shadow: false,
                    closable: false,
                    modal: true,
                    resizable: false,
                    items: [pincodeGroupTimeSlotGrid(id, name, picodes), timeRangePincodeGroupForms(id, name, picodes)],
                    fbar: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cross.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                addTimeRangeToPincodeGroupWindow.close();
                            }
                        }]

                });
            }
            addTimeRangeToPincodeGroupWindow.doLayout();
            addTimeRangeToPincodeGroupWindow.show();
            addTimeRangeToPincodeGroupWindow.center();
        },
        addTimeRangeForBranch: function (id, name, picodes) {

            var addTimeRangeToPincodeGroupWindow = Ext.getCmp('addTimeRangeToPincodeGroupWindow');
            if (Ext.isEmpty(addTimeRangeToPincodeGroupWindow)) {
                addTimeRangeToPincodeGroupWindow = new Ext.Window({
                    id: 'addTimeRangeToBranchWindow',
                    title: 'Time Range for Store',
                    layout: 'border',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    width: 1000,
                    height: 600,
                    shadow: false,
                    closable: false,
                    modal: true,
                    resizable: false,
                    items: [branchTimeSlotGrid(id, name, picodes)], //, timeRangeBranchForms(id, name, picodes)
                    fbar: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                addTimeRangeToPincodeGroupWindow.close();
                            }
                        }]

                });
            }
            addTimeRangeToPincodeGroupWindow.doLayout();
            addTimeRangeToPincodeGroupWindow.show();
            addTimeRangeToPincodeGroupWindow.center();
        }
    }
}
();