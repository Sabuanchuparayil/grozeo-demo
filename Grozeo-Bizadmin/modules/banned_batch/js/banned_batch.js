Application.BannedBatch = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=banned_batch';
    var recs_per_page = 24;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var banned_batchGridStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMedicineBannedBatch',
                method: 'post'
            }),
            fields: ['fsibb_id', 'stit_id', 'fsibb_batch', 'fsibb_createdOn', 'fsibb_createdBy', 'stit_SKU', 'fsibbCreatedBy'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        store.setDefaultSort('fsibb_id', 'ASC');
        return store;
    };
    var masterPanelforMedicineBatchGrid = function (id) {
        var branch_store = banned_batchGridStore();
        var centre_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'fsibb_batch'
                }]
        });
        centre_filter.remote = true;
        centre_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Banned Batches',
            plugins: [centre_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Medicine',
                    id: 'medicineBatch_nameauto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Medicine'
                }, {
                    header: 'Batch',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'fsibb_batch',
                    tooltip: 'Batch'
                }, {
                    header: 'Created By',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'fsibbCreatedBy',
                    tooltip: 'Created By'
                },
                {
                    header: 'Created On',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'fsibb_createdOn',
                    tooltip: 'Created On'
                },
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
            tbar: [{
                    xtype: 'button',
                    text: 'Create Banned Batch',
                    tooltip: 'Create Banned Batch',
                    iconCls: 'add',
                    handler: function () {
                        myphaBrandMedicineWindow('');
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branch_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [centre_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'medicineBatch_nameauto_exp'
        });
        return grid_panel;
    };
    var rtItemStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['stit_ID', 'stit_itemName', 'stit_SKU', ],
            url: modURL + '&op=getItemName',
            autoLoad: true,
            method: 'post'
        });
        return store;
    };
    var medicineForm = function (medId) {
        var compositionStore = rtItemStore();
        var form = new Ext.FormPanel({
            layout: 'form',
            id: 'myphaMedicineBatchForm',
            columnWidth: 1,
            height: 150,
            frame: true,
            border: true,
            labelAlign: 'top',
            items: [{
                    xtype: 'hidden',
                    id: 'fsibb_id',
                    name: 'fsibb_id'
                }, {
                    layout: 'column',
                    items: [{
                            columnWidth: .70,
                            layout: 'form',
                            border: false,
                            items: [{
                                    fieldLabel: 'Medicine Name',
                                    xtype: 'combo',
                                    displayField: 'stit_SKU',
                                    valueField: 'stit_ID',
                                    mode: 'local',
                                    id: 'medicine_master',
                                    hiddenName: 'medicine_master',
                                    name: 'medicine_master',
                                    typeAhead: true,
                                    minChars: 1,
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    lazyRender: true,
                                    anchor: '98%',
                                    allowBlank: false,
                                    store: compositionStore,
                                    editable: true,
                                    msgTarget: 'under',
                                    tabIndex: 1
                                }]
                        }, {
                            columnWidth: .30,
                            layout: 'form',
                            border: false,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Medicine Batch',
                                    emptyText: 'Medicine Batch',
                                    id: 'medicineMaster_batch',
                                    name: 'medicineMaster_batch',
                                    anchor: '98%',
                                    tabIndex: 2,
                                    allowBlank: false
                                }]
                        }
                    ]
                }]
        });
        return form;
    };
    var myphaBrandMedicineWindow = function (medId) {
        var form = medicineForm(medId);
        var medicineBatch_window = Ext.getCmp('medicineBatch_window');
        if (Ext.isEmpty(medicineBatch_window)) {
            medicineBatch_window = new Ext.Window({
                id: 'medicineBatch_window',
                title: 'Create Medicine Batch',
                layout: 'fit',
                width: winsize.width * .4,
                autoHeight: true,
                plain: true,
                modal: true,
                constrainHeader: true,
                keepTop: true,
                align: 'center',
                frame: true,
                resizable: false,
                height: 150,
                items: [form],
                shadow: false,
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        tabindex: 6,
                        handler: function () {
                            Ext.getCmp('medicineBatch_window').close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        tabindex: 5,
                        handler: function () {
                            saveMedicineBatchData(medId);
                        }
                    }]
            });
        }

        medicineBatch_window.doLayout();
        medicineBatch_window.show(this);
        medicineBatch_window.center();
        medicineBatch_window.alignTo(Ext.getBody(), "tr-tr", [-480, 10]);
    };
    var saveMedicineBatchData = function (medId) {
        var prescriptionForm = Ext.getCmp('myphaMedicineBatchForm').getForm();
        if (prescriptionForm.isValid()) {
            var form_data = {
                form: Ext.getCmp('myphaMedicineBatchForm').getForm().getValues()
            };
            if (Ext.getCmp('fsibb_id').getValue() > 0) {
                var mstId = Ext.getCmp('fsibb_id').getValue();
            } else {
                var d = new Date();
                var mstId = '-';
            }
            var params = {
                action: 'Save Medicine Batch',
                module: 'Master',
                op: 'saveMedicineBatch',
                extrainfo: 'Medicine Batch save',
                id: mstId
            };
            APICall(params, Application.BannedBatch.medicineBatchSave, form_data);
        } else {
            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
        }

    };
    return {
        Cache: {},
        initMedicineBatch: function () {
            var _currentStockPanelId = 'panelBannedMedicineBatch';
            var _masterPanelMedicineBatch = Ext.getCmp(_currentStockPanelId);
            if (Ext.isEmpty(_masterPanelMedicineBatch)) {
                _masterPanelMedicineBatch = masterPanelforMedicineBatchGrid(_currentStockPanelId);
                Application.UI.addTab(_masterPanelMedicineBatch);
                _masterPanelMedicineBatch.doLayout();
            } else {
                Application.UI.addTab(_masterPanelMedicineBatch);
            }
        }, medicineBatchSave: function () {

            var fsibb_id = Ext.getCmp('fsibb_id').getValue();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var prescriptionForm = Ext.getCmp('myphaMedicineBatchForm').getForm();
            if (prescriptionForm.isValid()) {
                prescriptionForm.submit({
                    url: modURL + '&op=saveMedicineBatchData',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);

                            Ext.getCmp('medicineMaster_batch').reset();
                            Ext.getCmp('medicine_master').reset();
                            recs_per_page = updateRecsPerPage(Ext.getCmp('panelBannedMedicineBatch'));
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert('Error', tmp.message);
                        } else if (tmp.success === false && tmp.valid === false) {
                            Ext.Msg.alert('Error', tmp.message);
                        } else {
                            Ext.Msg.alert('Error', tmp.message);
                        }

                    },
                    failure: function (elm, conf) {
                        if (conf.failureType == 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            console.log(result.message);
                            Ext.Msg.alert('Status', result.message);
                        } else {
                            Ext.Msg.alert('Notification', 'Please enter all required fields');
                        }
                    }
                });
            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        }
    }
}();