Application.MyphaBrands = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=mypha_brands';
    var recs_per_page = 12;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var gridSelectionChangedIngradients = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterIngradientsDataview').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterIngradientsDataview').getSelectionModel().getSelections()[0].data.ingradient_id;
            Application.MyphaBrands.ViewIngradients(ID);
        }
    };
    var medCompositionStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listmedComposition',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'composition_id',
                root: 'data'
            }, ['composition_id', 'composition_name', 'subCategory', 'category_name', 'composition_status']),
            sortInfo: {
                field: 'composition_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data'
        });
        return store;
    };
    var masterPanelformedicineSpecificationGrid = function () {

        var medComposition_store = medCompositionStore();
        var medComposition_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'composition_name'
                }, {
                    type: 'string',
                    dataIndex: 'subCategory'
                }, {
                    type: 'string',
                    dataIndex: 'category_name'
                },
                {
                    type: 'string',
                    dataIndex: 'composition_status'
                }
            ]
        });
        medComposition_filter.remote = true;
        medComposition_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            title: 'Medicine Specification',
            store: medComposition_store,
            //iconCls: 'optickets',
            plugins: [new Ext.ux.grid.GroupSummary(), medComposition_filter],
            id: 'gridpanelMastermedCompositionDataview',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Content/Composition',
                    id: 'composition_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'composition_name',
                    tooltip: 'Content/Composition'
                }, {
                    header: 'Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'category_name',
                    tooltip: 'Category'
                }, {
                    header: 'Subcategory',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'subCategory',
                    tooltip: 'Subcategory'
                },
                {
                    header: 'Status',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'composition_status',
                    tooltip: 'Status'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            text: 'add',
                            align: 'center',
                            renderer: function (value, meta, record) {
                                var id = Ext.id();
                                Ext.defer(function () {
                                    new Ext.Button({
                                        text: 'add',
                                        handler: function (btn, e) {
                                            // do whatever you want here
                                        }
                                    }).render(document.body, id);
                                }, 50);
                                return Ext.String.format('<div id="{0}"></div>', id);
                            }
                        }, {
                            xtype: 'button',
                            text: 'Contradictions',
                            tooltip: 'Contradictions',
                            scale: 'small',
                            handler: function () {
                                var record = grid.store.getAt(rowIndex);
                                var composition_id = record.get('composition_id');
                            }
                        }, {
                            xtype: 'button',
                            text: 'Interactions',
                            tooltip: 'Interactions',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var composition_id = record.get('composition_id');
                            }
                        }]
                }
            ],
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination
            },
            tbar: [],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: medComposition_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'composition_name_auto_exp'
        });
        return grid_panel;
    };
    var branchStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMedicineMasters',
                method: 'post'
            }),
            fields: ['medicineMaster_id', 'medicineMaster_name', 'ingradient_shortname', 'manufacture_name', 'medicine_type_name', 'composition_name', 'subCategory_name', 'mustatus', 'sfstatus', 'mwstatus', 'mistatus',
                'medicine_works', 'medicine_sideeffects', 'medicine_morInfo', 'medicine_use', 'isVerified'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        store.setDefaultSort('medicineMaster_name', 'ASC');
        return store;
    };
    var mediGridAction = function () {
        var action = new Ext.ux.grid.RowActions({
            autoWidth: false,
            hideMode: 'display',
            width: 150,
            actions: [
                {
                    sortable: false,
                    iconCls: 'edit',
                    tooltip: 'Edit Details',
                    callback: function (grid, rec, row, col) {
                        myphaBrandMedicineWindow(rec.get('medicineMaster_id'));
                    }
                }, {
                    iconCls: 'icon-add-table',
                    tooltip: 'Map Ingradients',
                    callback: function (grid, rec, row, col) {
                        MedIngridientAddWindow(rec.get('medicineMaster_id'));
                    }
                }, {
                    iconCls: 'edit_profile',
                    tooltip: 'Warnings',
                    callback: function (grid, rec, row, col) {
                        Application.MyphaBrands.myphMedDetails(rec.get('medicineMaster_id'));
                    }
                }, {
                    iconCls: 'icon-add-table',
                    tooltip: 'Medicine Precaution',
                    callback: function (grid, rec, row, col) {
                        MedPrecautionAddWindow(rec.get('medicineMaster_id'));
                    }
                }
            ]
        });
        return action;
    };
    var mediActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit Details",
                handler: function () {

                    var medicineMaster_id = Ext.getCmp('panelMasterBrand').getSelectionModel().getSelections()[0].data.medicineMaster_id;
                    myphaBrandMedicineWindow(medicineMaster_id);
                }
            }, {
                text: "Map Ingradients",
                handler: function () {

                    var medicineMaster_id = Ext.getCmp('panelMasterBrand').getSelectionModel().getSelections()[0].data.medicineMaster_id;
                    MedIngridientAddWindow(medicineMaster_id);
                }
            }, {
                text: "Warnings",
                handler: function () {

                    var medicineMaster_id = Ext.getCmp('panelMasterBrand').getSelectionModel().getSelections()[0].data.medicineMaster_id;
                    Application.MyphaBrands.myphMedDetails(medicineMaster_id);
                }
            }, {
                text: "Medicine Precaution",
                handler: function () {

                    var medicineMaster_id = Ext.getCmp('panelMasterBrand').getSelectionModel().getSelections()[0].data.medicineMaster_id;
                    MedPrecautionAddWindow(medicineMaster_id);
                }
            }]
    });
    var masterPanelforBrandGrid = function (id) {
        var branch_store = branchStore();
        var action = mediGridAction();
        var centre_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'medicineMaster_name'
                }, {
                    type: 'string',
                    dataIndex: 'composition_name'
                }, {
                    type: 'string',
                    dataIndex: 'medicine_type_name'
                }, {
                    type: 'string',
                    dataIndex: 'manufacture_name'
                }, {
                    type: 'string',
                    dataIndex: 'subCategory_name'
                }, {
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'mustatus'
                }, {
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'sfstatus'
                }, {
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'mwstatus'
                }, {
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'mistatus'
                }, {
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'isVerified'
                }]
        });
        centre_filter.remote = true;
        centre_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Drugs',
            //iconCls: 'medicine',
            plugins: [centre_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Drug',
                    id: 'medicineMaster_nameauto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'medicineMaster_name',
                    tooltip: 'Drug'
                }, {
                    header: 'Single/Multiple API Drug',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'composition_name',
                    tooltip: 'Single/Multiple API Drug'
                }, {
                    header: 'Drug Group',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'subCategory_name',
                    tooltip: 'Drug Group'
                },
                {
                    header: 'Dosage Form',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'medicine_type_name',
                    tooltip: 'Dosage Form'
                }, {
                    header: 'Manufacture',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'manufacture_name',
                    tooltip: 'Manufacture'
                }, {
                    header: 'MU',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'mustatus',
                    tooltip: 'Medicine Use'
                }, {
                    header: 'SF',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'sfstatus',
                    tooltip: 'Side Effects'
                }, {
                    header: 'MW',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'mwstatus',
                    tooltip: 'Medicine Works'
                }, {
                    header: 'MI',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'mistatus',
                    tooltip: 'Information'
                }, {
                    header: 'Is Verified',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'isVerified',
                    tooltip: 'Is Verified'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            mediActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }

                /*    {
                 xtype: 'actioncolumn',
                 header: 'Action',
                 hideable: true,
                 sortable: false,
                 groupable: false,
                 tooltip: 'Action',
                 items: [{
                 iconCls: 'edit',
                 tooltip: 'Edit Details',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var medicineMaster_id = record.get('medicineMaster_id');
                 myphaBrandMedicineWindow(medicineMaster_id);
                 }
                 }, {
                 iconCls: 'icon-add-table',
                 tooltip: 'Map Ingradients',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var medicineMaster_id = record.get('medicineMaster_id');
                 Application.MyphaBrands.Cache.medicineMaster_id = medicineMaster_id;
                 MedIngridientAddWindow(medicineMaster_id);
                 }
                 }, {
                 iconCls: 'edit_profile',
                 tooltip: 'Warnings',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var medicineMaster_id = record.get('medicineMaster_id');
                 Application.MyphaBrands.myphMedDetails(medicineMaster_id);
                 }
                 }, {
                 iconCls: 'icon-add-table',
                 tooltip: 'Medicine Precaution',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var medicineMaster_id = record.get('medicineMaster_id');
                 Application.MyphaBrands.Cache.medicineMaster_id = medicineMaster_id;
                 MedPrecautionAddWindow(medicineMaster_id);
                 }
                 }]
                 }*/],
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
                    text: 'Create Drugs',
                    tooltip: 'Create Drugs',
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
            autoExpandColumn: 'medicineMaster_nameauto_exp'
        });
        return grid_panel;
    };
    var fnmanufactureStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=medManufacture',
            fields: ['manufacture_id', 'manufacture_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
        });
        return natureOfGroupStore;
    };
    var fnmedTypeStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=medType',
            method: 'post',
            fields: ['medicine_type_id', 'medicine_type_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return natureOfGroupStore;
    };
    var fncompositionStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=medComposition',
            method: 'post',
            fields: ['composition_id', 'composition_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return natureOfGroupStore;
    };
    var medicineForm = function (medId) {
        var manufactureStore = fnmanufactureStore();
        var medTypeStore = fnmedTypeStore();
        var compositionStore = fncompositionStore();
        var form = new Ext.FormPanel({
            layout: 'form',
            id: 'myphaMedicineForm',
            columnWidth: 1,
            height: 150,
            frame: true,
            border: true,
            labelAlign: 'top',
            items: [{
                    xtype: 'hidden',
                    id: 'medicineMaster_id',
                    name: 'medicineMaster_id'
                }, {
                    fieldLabel: 'Medicine Generic Name',
                    xtype: 'combo',
                    displayField: 'composition_name',
                    valueField: 'composition_id',
                    mode: 'local',
                    id: 'medicine_composition',
                    hiddenName: 'medicine_composition',
                    name: 'medicine_composition',
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
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            columnWidth: .35,
                            layout: 'form',
                            border: false,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Medicine Brand Name',
                                    emptyText: 'Medicine Brand Name',
                                    id: 'medicineMaster_name',
                                    name: 'medicineMaster_name',
                                    anchor: '98%',
                                    tabIndex: 2,
                                    allowBlank: false
                                }]
                        }, {
                            columnWidth: .32,
                            layout: 'form',
                            border: false,
                            items: [{
                                    fieldLabel: 'Manufacturer/Supplier',
                                    xtype: 'combo',
                                    displayField: 'manufacture_name',
                                    valueField: 'manufacture_id',
                                    mode: 'local',
                                    id: 'medicine_manufacture',
                                    hiddenName: 'medicine_manufacture',
                                    name: 'medicine_manufacture',
                                    typeAhead: true,
                                    minChars: 1,
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    lazyRender: true,
                                    anchor: '98%',
                                    allowBlank: false,
                                    store: manufactureStore,
                                    editable: true,
                                    msgTarget: 'under',
                                    tabIndex: 3,
                                }]
                        }, {
                            columnWidth: .32,
                            layout: 'form',
                            border: false,
                            items: [{
                                    hiddenName: 'medicine_type',
                                    xtype: 'combo',
                                    name: 'medicine_type',
                                    fieldLabel: 'Dosage Form',
                                    id: 'medicine_type',
                                    anchor: '98%',
                                    displayField: 'medicine_type_name',
                                    valueField: 'medicine_type_id',
                                    store: medTypeStore,
                                    triggerAction: 'all',
                                    forceSelection: true,
                                    selectOnFocus: true,
                                    mode: 'local',
                                    allowBlank: false,
                                    tabIndex: 4,
                                    typeAhead: true,
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 1
                                }]
                        }
                    ]
                }]
        });
        return form;
    };
    var saveMedicineData = function (medId) {
        var prescriptionForm = Ext.getCmp('myphaMedicineForm').getForm();
        if (prescriptionForm.isValid()) {
            var form_data = {
                form: Ext.getCmp('myphaMedicineForm').getForm().getValues()
            };
            if (Ext.getCmp('medicineMaster_id').getValue() > 0) {
                var mstId = Ext.getCmp('medicineMaster_id').getValue();
            } else {
                var d = new Date();
                var mstId = '-';
            }
            var params = {
                action: 'Save Medicine',
                module: 'Master',
                op: 'saveMedicine',
                extrainfo: 'Medicine save',
                id: mstId
            };
            APICall(params, Application.MyphaBrands.medicineSave, form_data);
        } else {
            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
        }

    };
    var myphaBrandMedicineWindow = function (medId) {
        var form = medicineForm(medId);

        var medicine_window = Ext.getCmp('medicine_window');
        if (Ext.isEmpty(medicine_window)) {
            medicine_window = new Ext.Window({
                id: 'medicine_window',
                title: !Ext.isEmpty(medId) ? 'Edit Drug Details' : 'Create Drug Details',
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
                fbar: [{
                        xtype: 'button',
                        text: 'Verify',
                        icon: IMAGE_BASE_PATH + '/default/icons/approve.png',
                        tabindex: 6,
                        align: 'right',
                        hidden: true,
                        id: 'drugVerify',
                        handler: function () {
                            if (medId > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=verifyDrug',
                                    method: 'POST',
                                    params: {
                                        medId: medId
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.message);

                                            if (medId > 0) {
                                                Ext.getCmp('medicine_window').close();
                                                Ext.getCmp('panelMasterBrand').selModel.getSelected().data = tmp.data;
                                                Ext.getCmp('panelMasterBrand').getView().refresh(
                                                        {
                                                            callback: function (record, options, success) {
                                                                var gridPanel = Ext.getCmp('panelMasterBrand');
                                                                var index = gridPanel.store.find('medicineMaster_id', medId);
                                                                gridPanel.getSelectionModel().selectRow(index);
                                                            }
                                                        }
                                                );
                                            }
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Application.example.msg('Notification', tmp.message);
                                            //Ext.Msg.alert("Notification.", tmp.message);

                                            if (medId > 0) {
                                                Ext.getCmp('medicine_window').close();
                                                Ext.getCmp('panelMasterBrand').selModel.getSelected().data = tmp.data;
                                                Ext.getCmp('panelMasterBrand').getView().refresh(
                                                        {
                                                            callback: function (record, options, success) {
                                                                var gridPanel = Ext.getCmp('panelMasterBrand');
                                                                var index = gridPanel.store.find('medicineMaster_id', medId);
                                                                gridPanel.getSelectionModel().selectRow(index);
                                                            }
                                                        }
                                                );
                                            }
                                        } else if (tmp.success === true && tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', tmp.msg);
                                    }
                                });
                            }


                        }
                    }, {
                        xtype: 'spacer'
                    }, {
                        xtype: 'button',
                        align: 'left',
                        text: 'Cancel',
                        buttonAlign: 'left',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        tabindex: 6,
                        handler: function () {
                            Ext.getCmp('medicine_window').close();
                        }
                    }, {
                        xtype: 'button',
                        align: 'left',
                        text: 'Save',
                        buttonAlign: 'left',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        tabindex: 5,
                        handler: function () {
                            saveMedicineData(medId);
                        }
                    }]
            });
        }
        if (!Ext.isEmpty(medId)) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var dept_form = Ext.getCmp('myphaMedicineForm').getForm();
            dept_form.load({
                params: {
                    medId: medId,
                    ID: medId,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                url: modURL + '&op=loadMedicineData',
                waitMsg: 'Loading...', success: function (form, action) {
                    eval('var tmp=' + action.response.responseText);
                    var tmp = Ext.decode(action.response.responseText);
                    console.log(tmp);
                    Ext.getCmp('medicine_type').setValue(tmp.data.medicine_type);
                    Ext.getCmp('medicine_type').setRawValue(tmp.data.medicine_type_name);
                    Ext.getCmp('medicine_type').getStore().load();
                    Ext.getCmp('medicine_manufacture').setValue(tmp.data.medicine_manufacture);
                    Ext.getCmp('medicine_manufacture').setRawValue(tmp.data.manufacture_name);
                    Ext.getCmp('medicine_manufacture').getStore().load();
                    Ext.getCmp('medicine_composition').setValue(tmp.data.medicine_composition);
                    Ext.getCmp('medicine_composition').setRawValue(tmp.data.composition_name);
                    Ext.getCmp('medicine_composition').getStore().load();
                    Ext.getCmp('drugVerify').show();
                },
                failure: function (form, action) {
                }
            });
        }
        medicine_window.doLayout();
        medicine_window.show(this);
        medicine_window.center();
        medicine_window.alignTo(Ext.getBody(), "tr-tr", [-480, 10]);
    };
    var IngradientsStore = function () {
        var _ingradientsMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listIngradients',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'ingradient_id',
                root: 'data'
            }, ['ingradient_id', 'ingradient_name', 'ingradient_shortname', 'ingradient_status']),
            sortInfo: {
                field: 'ingradient_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterIngradientsDataview').getSelectionModel().selectRow(0);
                }
            }
        });
        return _ingradientsMasterStore;
    };
    var IngradientsGrid = function () {
        var Ingradients_store = IngradientsStore();
        var Ingradients_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'ingradient_name'
                },
                {
                    type: 'string',
                    dataIndex: 'ingradient_shortname'
                },
                {
                    type: 'string',
                    dataIndex: 'ingradient_status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'ingradient_status'
                }
            ]
        });
        Ingradients_filter.remote = true;
        Ingradients_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: Ingradients_store,
            //iconCls: 'optickets',
            plugins: [Ingradients_filter],
            id: 'gridpanelMasterIngradientsDataview',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Excipients',
                    id: 'Ingradients_filter_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'ingradient_name',
                    tooltip: 'Excipients'
                },
                {
                    header: 'Short Name',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'ingradient_shortname',
                    tooltip: 'Short Name'
                }, {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'ingradient_status',
                    tooltip: 'Status'
                },
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedIngradients
                }

            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('ingradient_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaBrands.Cache.ingradient_id = ID;
                        Ext.getCmp('IngradientsSaveForm').hide();
                        Application.MyphaBrands.ViewIngradients(ID);
                    }
                },
                resize: updatePagination,
                afterrender: function () {
                    Ingradients_store.load();
                }
            },
            tbar: [/* <?php if (user_access("mypha_brands", "saveIngradients")) { ?> */{
                    xtype: 'button',
                    text: 'Create Excipients',
                    tooltip: 'Create Excipients',
                    iconCls: 'add',
                    handler: function () {
                        Application.MyphaBrands.IngradientsAddEdit = 'Add';
                        var excipientForm = Ext.getCmp('IngradientsSaveForm').getForm();
                        Ext.getCmp('panelMasterIngradientsParent').setTitle('Create Excipients Detail');
                        loadedForm = null;
                        excipientForm.reset();
                        Ext.getCmp('ingradient_name').focus(false, 100);
                        Ext.getCmp('buttonMasterIngradientsEdit').hide();
                        Ext.getCmp('buttonMasterIngradientsSave').show();
                        Ext.getCmp('buttonMasterIngradientsCancel').show();
                        Ext.getCmp('IngradientsSaveForm').show();
                        Ext.getCmp('panelMasterIngradientsDetailsView').hide();
                        Ext.getCmp('panelMasterIngradientsParent').doLayout();
                        var recordSelected = Ext.getCmp('ingradient_status').getStore().getAt(0);
                        Ext.getCmp('ingradient_status').setValue(recordSelected.get('id'));
                    }

                }/*<?php  } ?>*/],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: Ingradients_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [Ingradients_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'Ingradients_filter_name_auto_exp'
        });
        return grid_panel;
    };
    var IngradientsForm = function () {
        var _IngradientsFormPanel = new Ext.form.FormPanel({
            id: 'IngradientsSaveForm',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Excipients',
                    id: 'ingradient_name',
                    name: 'n[ingradient_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 901,
                    maxLength: 350
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Short Name',
                    id: 'ingradient_shortname',
                    name: 'n[ingradient_shortname]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 902,
                    maxLength: 150
                },
                {
                    xtype: 'textfield',
                    id: 'ingradient_id',
                    name: 'n[ingradient_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[ingradient_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 903,
                    "emptyText": "Set status..",
                    "id": 'ingradient_status'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('ingradient_id').getValue())) {
                        var recordSelected = Ext.getCmp('ingradient_status').getStore().getAt(0);
                        Ext.getCmp('ingradient_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _IngradientsFormPanel;
    };
    var IngradientsMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterIngradientsDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Excipient </th><td>  {ingradient_name} </td></tr>',
                    '<tr><th width="40%">Short Name  </th><td>  {ingradient_shortname} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="ingradient_status == \'1\'">Active</tpl>',
                    '<tpl if="ingradient_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var IngradientsSave = function () {
        var form_data = {
            form: Ext.getCmp('IngradientsSaveForm').getForm().getValues()
        };
        if (Ext.getCmp('ingradient_id').getValue() > 0) {
            var mstId = Ext.getCmp('ingradient_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Excipients',
            module: 'Master',
            op: 'saveIngradients',
            extrainfo: 'Excipients save',
            id: mstId
        };
        APICall(params, Application.MyphaBrands.IngradientsSave, form_data);
    };
    var masterPanelforIngradients = function (id) {
        var IngradientsPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Excipients',
            id: id,
            items: [
                IngradientsGrid(),
                new Ext.Panel({
                    title: 'Excipient(Inactive Component) Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterIngradientsParent',
                    height: winsize.height * 0.6,
                    items: [IngradientsForm(), IngradientsMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [
                        {
                            text: "Cancel",
                            tabIndex: 905,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterIngradientsCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterIngradientsDataview').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterIngradientsDataview').getSelectionModel().getSelections()[0].data.ingradient_id;
                                    Application.MyphaBrands.ViewIngradients(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterIngradientsEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 906,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterIngradientsDataview').getSelectionModel().getSelections()[0].data.ingradient_id;
                                Application.MyphaBrands.EditIngradientsView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 904,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterIngradientsSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                IngradientsSave();
                            }
                        }
                    ]
                })
            ]
        });
        return IngradientsPanel;
    };
    var MedIngridientAddWindow = function (medicineMaster_id) {
        /* Store For Ingradient Combo */
        var _ingradientStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=loadIngridientsCombo',
            fields: ['ingradient_id', 'ingradient_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            remoteSort: true,
            listeners: {
                beforeload: function () {
                }

            }
        });
        var _unitStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=loadUnitCombo',
            fields: ['unit_id', 'unit_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            remoteSort: true,
            listeners: {
                beforeload: function () {
                }

            }
        });
        var medIngradientmapWindow = Ext.getCmp('windowIdrl_LabResultsAddTestResultsData');
        if (Ext.isEmpty(medIngradientmapWindow)) {
            var medIngradientmapWindow = new Ext.Window({
                id: 'windowIdrl_LabResultsAddTestResultsData',
                title: 'Map Ingradients',
                //iconCls: 'additem',
                shadow: false,
                width: 600,
                autoScroll: true,
                height: 400,
                layout: 'border',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                closable: true,
                items: [
                    new Ext.Panel({
                        layout: "fit",
                        region: 'center',
                        width: 550,
                        border: false,
                        items: [
                            {
                                region: 'north',
                                autoHeight: true,
                                items: new Ext.form.FormPanel({
                                    frame: false,
                                    border: false,
                                    autoHeight: true,
                                    autoScroll: true,
                                    labelWidth: 120,
                                    labelAlign: "top",
                                    bodyStyle: {
                                        "background-color": "white",
                                        padding: "5px 5px 5px 10px"
                                    },
                                    id: "medIngradientForm",
                                    items: [
                                        {
                                            xtype: 'hidden',
                                            id: 'medIngrad_id',
                                            name: 'medIngrad_id'
                                        },
                                        {
                                            layout: "column",
                                            border: false,
                                            items: [
                                                {
                                                    columnWidth: 0.50,
                                                    layout: "form",
                                                    border: false,
                                                    items: [{
                                                            xtype: "combo",
                                                            fieldLabel: "Excipient",
                                                            name: "ingradient_id",
                                                            id: "ingradient_id",
                                                            anchor: "98%",
                                                            store: _ingradientStore,
                                                            mode: "local",
                                                            selectOnFocus: true,
                                                            hiddenName: "ingradient_id",
                                                            displayField: "ingradient_name",
                                                            valueField: "ingradient_id",
                                                            typeAhead: true,
                                                            forceSelection: true,
                                                            triggerAction: "all",
                                                            lazyRender: true,
                                                            allowBlank: false,
                                                            editable: true,
                                                            minChars: 1,
                                                            tabIndex: 601,
                                                            listeners: {
                                                            }
                                                        }]
                                                },
                                                {
                                                    columnWidth: 0.15,
                                                    layout: "form",
                                                    border: false,
                                                    items: [
                                                        {
                                                            xtype: "textfield",
                                                            fieldLabel: "Quantity",
                                                            id: "medIngrad_qty",
                                                            name: "medIngrad_qty",
                                                            anchor: "98%",
                                                            allowBlank: true,
                                                            tabIndex: 602
                                                        }
                                                    ]
                                                },
                                                {
                                                    columnWidth: 0.20,
                                                    layout: "form",
                                                    border: false,
                                                    style: 'margin-top:17px;',
                                                    items: [
                                                        {
                                                            xtype: 'button',
                                                            tooltip: 'Add New ',
                                                            text: 'Add',
                                                            iconCls: 'add',
                                                            width: 60,
                                                            tabIndex: 604,
                                                            handler: function () {
                                                                /* Save Test Results */
                                                                //var medicineMaster_id = arguments[0];
                                                                var form = Ext.getCmp('medIngradientForm').getForm();
                                                                Application.MyphaBrands.Cache.medicineMaster_id = medicineMaster_id;
                                                                if (form.isValid()) {
                                                                    var form_data = {
                                                                        form: Ext.getCmp('medIngradientForm').getForm().getValues()
                                                                    };
                                                                    var params = {
                                                                        action: 'Save Details',
                                                                        module: 'mypha_brands',
                                                                        op: 'mapIngradientstoMedicine',
                                                                        extrainfo: 'Map ingradients to medicine',
                                                                        id: Application.MyphaBrands.Cache.medicineMaster_id
                                                                    };
                                                                    APICall(params, Application.MyphaBrands.mapIngradientstoMedicine, form_data);
                                                                } else {
                                                                    Application.example.msg('Warning', 'Fill the required fields');
                                                                }
                                                            }
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ]
                                })
                            },
                            {
                                region: 'center',
                                layout: 'fit',
                                items: medicineIngradientShowGrid()
                            }
                        ]
                    })
                ]
            });
        }
        medIngradientmapWindow.show();
        medIngradientmapWindow.doLayout();
        medIngradientmapWindow.center();
    };
    var MedPrecautionAddWindow = function (medicineMaster_id) {
        /* Store For Advice-Precaution Combo */
        var _adviceStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=loadAdviceCombo',
            fields: ['adi_id', 'advice_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            remoteSort: true
        });
        var _precautionStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=loadPrecautionCombo',
            fields: ['pret_id', 'precaution_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            remoteSort: true,
        });
        var content_store = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=adprecaution_load',
                fields: ['preadv_id', 'preadv_content'],
                totalProperty: 'totalCount',
                root: 'data',
                id: 'preadv_id',
                remoteSort: true
            });
            return store;
        };
        var advicePrecautionmapWindow = Ext.getCmp('windowAdvicePrecaution');
        if (Ext.isEmpty(advicePrecautionmapWindow)) {
            var advicePrecautionmapWindow = new Ext.Window({
                id: 'windowAdvicePrecaution',
                title: 'Medicine Precaution',
                //iconCls: 'additem',
                shadow: false,
                width: 600,
                autoScroll: true,
                height: 400,
                layout: 'border',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                closable: true,
                items: [
                    new Ext.Panel({
                        layout: "fit",
                        region: 'center',
                        width: 550,
                        border: false,
                        items: [{
                                region: 'north',
                                autoHeight: true,
                                items: new Ext.form.FormPanel({
                                    frame: false,
                                    border: false,
                                    autoHeight: true,
                                    autoScroll: true,
                                    labelWidth: 120,
                                    labelAlign: "top",
                                    bodyStyle: {
                                        "background-color": "white",
                                        padding: "5px 5px 5px 10px"
                                    },
                                    id: "medPrecautionForm",
                                    items: [{
                                            xtype: 'hidden',
                                            id: 'medadv_id',
                                            name: 'medadv_id'
                                        }, {
                                            layout: "column",
                                            border: false,
                                            items: [{
                                                    columnWidth: 0.45,
                                                    layout: "form",
                                                    border: false,
                                                    items: [{
                                                            xtype: 'combo',
                                                            fieldLabel: 'Advice Name',
                                                            emptyText: 'Advice Name',
                                                            id: 'adi_id',
                                                            name: 'advice_id',
                                                            hiddenName: 'advice_id',
                                                            anchor: '98%',
                                                            displayField: 'advice_name',
                                                            valueField: 'adi_id',
                                                            triggerAction: 'all',
                                                            forceSelection: true,
                                                            selectOnFocus: true,
                                                            mode: 'local',
                                                            //autoShow: true,
                                                            typeAhead: true,
                                                            lazyRender: true,
                                                            editable: true,
                                                            minChars: 2,
                                                            tabIndex: 1,
                                                            store: _adviceStore
                                                        }]
                                                }, {
                                                    columnWidth: 0.45,
                                                    layout: "form",
                                                    border: false,
                                                    items: [{
                                                            xtype: 'combo',
                                                            fieldLabel: 'Precaution Name',
                                                            emptyText: 'Precaution Name',
                                                            id: 'pret_id',
                                                            name: 'precaution_id',
                                                            hiddenName: 'precaution_id',
                                                            anchor: '98%',
                                                            displayField: 'precaution_name',
                                                            valueField: 'pret_id',
                                                            triggerAction: 'all',
                                                            forceSelection: true,
                                                            selectOnFocus: true,
                                                            mode: 'local',
                                                            typeAhead: true,
                                                            lazyRender: true,
                                                            editable: true,
                                                            minChars: 2,
                                                            tabIndex: 2,
                                                            store: _precautionStore,
                                                            listeners: {
                                                                select: function () {
                                                                    var precValue = Ext.getCmp('pret_id').getValue();
                                                                    var advValue = Ext.getCmp('adi_id').getValue();
                                                                    Ext.Ajax.request({
                                                                        url: modURL + '&op=loadPrecAdvice',
                                                                        method: 'POST',
                                                                        params: {
                                                                            precValue: precValue,
                                                                            advValue: advValue
                                                                        },
                                                                        success: function (res) {
                                                                            var tmp = Ext.decode(res.responseText);
                                                                            if (tmp.success === true) {
                                                                                Ext.getCmp('medadv_content').setValue(tmp.preadv_content);
                                                                            }
                                                                        },
                                                                        failure: function () {
                                                                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                                        }
                                                                    });
                                                                }

                                                            }
                                                        }]
                                                }, {
                                                    columnWidth: 0.10,
                                                    layout: "form",
                                                    border: false,
                                                    style: 'margin-top:17px;',
                                                    items: [{
                                                            xtype: 'button',
                                                            tooltip: 'Add New ',
                                                            text: 'Add',
                                                            iconCls: 'add',
                                                            width: 46,
                                                            //tabIndex: 604,
                                                            handler: function () {
                                                                /* Save Precaution-Advice */
//                                                                var medicineMaster_id = arguments[0];
                                                                var form = Ext.getCmp('medPrecautionForm').getForm();
                                                                Application.MyphaBrands.Cache.medicineMaster_id = medicineMaster_id;
                                                                if (form.isValid()) {
                                                                    var form_data = {
                                                                        form: Ext.getCmp('medPrecautionForm').getForm().getValues()
                                                                    };
                                                                    var params = {
                                                                        action: 'Save Details',
                                                                        module: 'mypha_brands',
                                                                        op: 'mapAdPrecautionstoMedicine',
                                                                        extrainfo: 'Map precaution-advice to medicine',
                                                                        id: Application.MyphaBrands.Cache.medicineMaster_id
                                                                    };
                                                                    APICall(params, Application.MyphaBrands.mapAdPrecautionstoMedicine, form_data);
                                                                } else {
                                                                    Application.example.msg('Warning', 'Fill the required fields');
                                                                }
                                                            }
                                                        }]
                                                }]

                                        }, {
                                            layout: "column",
                                            border: false,
                                            items: [{
                                                    columnWidth: 1,
                                                    layout: "form",
                                                    border: false,
                                                    items: [{
                                                            xtype: 'textarea',
                                                            fieldLabel: 'Content',
                                                            id: 'medadv_content',
                                                            name: 'medadv_content',
                                                            displayField: 'preadv_content',
                                                            anchor: '98%',
                                                            allowBlank: false,
                                                            tabIndex: 3,
                                                            store: content_store,
                                                            maxValue: 100
                                                        }]
                                                }]
                                        }]
                                })
                            },
                            {
                                region: 'center',
                                layout: 'fit',
                                items: precautionadGridstore()
                            }
                        ]
                    })
                ]
            });
        }
        advicePrecautionmapWindow.show();
        advicePrecautionmapWindow.doLayout();
        advicePrecautionmapWindow.center();
    };
    var _mapadPrecautionGridStore = function () {
        var _prestore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + "&op=listadPrecaution",
                method: "post"
            }),
            reader: new Ext.data.JsonReader(
                    {
                        totalProperty: "totalCount",
                        idProperty: "medadv_id",
                        root: "data"
                    },
            ["medicineMaster_id", "adi_id", "pret_id", "medadv_content", "advice_name", "precaution_name", "medadv_id"]
                    ),
            sortInfo: {
                field: "medadv_id",
                direction: "DESC"
            },
            groupField: "",
            groupDir: "ASC",
            remoteSort: true,
            autoLoad: false,
            root: "data",
            listeners: {
                load: function () {

                },
                beforeload: function (store, e) {
                    this.baseParams.medicineMaster_id = Application.MyphaBrands.Cache.medicineMaster_id;
                }
            }
        });
        return _prestore;
    };
    var precautionadGridstore = function () {
        var mapprecaution_store = _mapadPrecautionGridStore();
        var _mapprecaution_grid = new Ext.grid.GridPanel({
            id: 'gridpanelprecautionMedicineMap',
            store: mapprecaution_store,
            layout: 'fit',
            frame: false,
            border: false,
            height: 400,
            autoScroll: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText:
                        '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl:
                        '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary()],
            viewConfig: {
                forceFit: true,
            },
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Advice Name',
                    sortable: true,
                    dataIndex: 'advice_name',
                    tooltip: 'Advice Name',
                    hideable: false,
                    width: 200
                },
                {
                    header: 'Precaution Name',
                    sortable: true,
                    dataIndex: 'precaution_name',
                    tooltip: 'Precaution Name',
                    hideable: false
                }],
            listeners: {
                afterrender: function () {
                    mapprecaution_store.load();
                }
            },
            stripeRows: true
        });
        return _mapprecaution_grid;
    };
    var _mapmedIngradGridStore = function () {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + "&op=listmappedIngradientinMedi",
                method: "post"
            }),
            reader: new Ext.data.JsonReader(
                    {
                        totalProperty: "totalCount",
                        idProperty: "medIngrad_id",
                        root: "data"
                    },
            ["medicineMaster_id", "ingradient_id", "medIngrad_qty", "unit_id", "unit_name", "ingradient_name", "medIngrad_id"]
                    ),
            sortInfo: {
                field: "medIngrad_id",
                direction: "DESC"
            },
            groupField: "",
            groupDir: "ASC",
            remoteSort: true,
            autoLoad: false,
            root: "data",
            listeners: {
                load: function () {

                },
                beforeload: function (store, e) {
                    this.baseParams.medicineMaster_id = Application.MyphaBrands.Cache.medicineMaster_id;
                }
            }
        });
        return _store;
    };
    var medicineIngradientShowGrid = function () {
        var mapmedIngrad_store = _mapmedIngradGridStore();
        var _mapmedIngrad_grid = new Ext.grid.GridPanel({
            id: 'gridpanelIngradientMedicineMap',
            store: mapmedIngrad_store,
            layout: 'fit',
            frame: false,
            border: false,
            height: 400,
            autoScroll: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText:
                        '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl:
                        '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary()],
            viewConfig: {
                forceFit: true,
            },
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Excipient',
                    sortable: true,
                    dataIndex: 'ingradient_name',
                    tooltip: 'Excipient',
                    hideable: false,
                    width: 200
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'medIngrad_qty',
                    tooltip: 'Quantity',
                    hideable: false

                },
                {
                    xtype: 'actioncolumn',
                    hideable: false,
                    items: [
                        {
                            tooltip: 'Edit Details',
                            icon: IMAGE_BASE_PATH + '/default/icons/application_form_edit.png',
                            handler: function (grid, rowIndex, colIndex)
                            {
                                var record = grid.getStore().getAt(rowIndex);
                                console.log("record", record);
                                var medIngrad_id = record.get("medIngrad_id");
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                if (!Ext.isEmpty(medIngrad_id)) {
                                    var resultForm = Ext.getCmp("medIngradientForm").getForm();
                                    resultForm.load({
                                        params: {
                                            medIngrad_id: medIngrad_id,
                                            apikey: _SESSION.apikey,
                                            tstamp: t_stamp
                                        },
                                        url: modURL + "&op=loadMedicineIngradients",
                                        waitMsg: "Loading...",
                                        success: function (form, action) {

                                        },
                                        failure: function (form, action) {
                                            var tmp = Ext.decode(action.response.responseText);
                                            Ext.Msg.alert("Error.", tmp.msg);
                                        }
                                    });
                                }

                            }
                        }

                    ]
                }
            ],
            listeners: {
                afterrender: function () {
                    mapmedIngrad_store.load();
                }

            },
            stripeRows: true
        });
        return _mapmedIngrad_grid;
    };
    var medicineDetailsForm = function () {
        var form = new Ext.FormPanel({
            id: 'myphaMedicineDetailsForm',
            height: 300,
            frame: true,
            border: true,
            labelAlign: 'top',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: .5,
                            padding: '3px',
                            items: [{
                                    xtype: 'textarea',
                                    fieldLabel: 'How to use',
                                    emptyText: 'How to use',
                                    name: 'medicine_use',
                                    id: 'medicine_use',
                                    anchor: '100%',
                                    height: 110,
                                    tabIndex: 701

                                }, {
                                    xtype: 'textarea',
                                    fieldLabel: 'How it works',
                                    emptyText: 'How it works',
                                    name: 'medicine_works',
                                    id: 'medicine_works',
                                    anchor: '100%',
                                    height: 110,
                                    tabIndex: 702

                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .5,
                            padding: '3px',
                            items: [{
                                    xtype: 'textarea',
                                    fieldLabel: 'Side Effects',
                                    emptyText: 'Side Effects',
                                    anchor: '100%',
                                    name: 'medicine_sideeffects',
                                    id: 'medicine_sideeffects',
                                    height: 110,
                                    tabIndex: 703

                                }, {
                                    xtype: 'textarea',
                                    fieldLabel: 'More information',
                                    emptyText: 'More information ie,storage details etc.',
                                    name: 'medicine_morInfo',
                                    id: 'medicine_morInfo',
                                    height: 110,
                                    anchor: '100%',
                                    tabIndex: 704

                                }]
                        }]
                }]
        });
        return form;
    };
    var saveMedicineDetailsData = function (medicineMaster_id) {
        var prescriptionForm = Ext.getCmp('myphaMedicineDetailsForm').getForm();
        if (prescriptionForm.isValid()) {
            var form_data = {
                form: Ext.getCmp('myphaMedicineDetailsForm').getForm().getValues()
            };
            Application.MyphaBrands.Cache.MedId = medicineMaster_id;
            var params = {
                action: 'Save Medicine',
                module: 'Master',
                op: 'saveMedicine',
                extrainfo: 'Medicine save',
                id: medicineMaster_id
            };
            APICall(params, Application.MyphaBrands.medicineDetailsSave, form_data);
        } else {
            Application.example.msg('Error', 'Check the required fields');
        }

    }
    return {
        Cache: {},
        initBrand: function () {
            var _currentStockPanelId = 'panelMasterBrand';
            var _masterPanelBrand = Ext.getCmp(_currentStockPanelId);
            if (Ext.isEmpty(_masterPanelBrand)) {
                _masterPanelBrand = masterPanelforBrandGrid(_currentStockPanelId);
                Application.UI.addTab(_masterPanelBrand);
                _masterPanelBrand.doLayout();
            } else {
                Application.UI.addTab(_masterPanelBrand);
            }
        }, medicineSave: function () {

            var medicineMaster_id = Ext.getCmp('medicineMaster_id').getValue();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var prescriptionForm = Ext.getCmp('myphaMedicineForm').getForm();
            if (prescriptionForm.isValid()) {
                prescriptionForm.submit({
                    url: modURL + '&op=saveMedicineData',
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
                            if (Ext.getCmp('medicineMaster_id').getValue() > 0) {
                                Ext.getCmp('medicine_window').close();
                                Ext.getCmp('panelMasterBrand').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('panelMasterBrand').getView().refresh(
                                        {
                                            callback: function (record, options, success) {
                                                var gridPanel = Ext.getCmp('panelMasterBrand');
                                                var index = gridPanel.store.find('medicineMaster_id', medicineMaster_id);
                                                gridPanel.getSelectionModel().selectRow(index);
                                            }
                                        }
                                );
                            } else {
                                Ext.getCmp('medicine_manufacture').reset();
                                Ext.getCmp('medicineMaster_name').reset();
                                recs_per_page = updateRecsPerPage(Ext.getCmp('panelMasterBrand'));
                                Ext.getCmp('gridpanelMasterDataviewstoreTypedata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: RECS_PER_PAGE
                                    }
                                });
                            }


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
        },
        initMedicineSpecification: function () {
            var medicineSpecificationPanelId = 'panelMasterMedicineSpecification';
            var _masterPanelMedicineSpecification = Ext.getCmp(medicineSpecificationPanelId);
            if (Ext.isEmpty(_masterPanelMedicineSpecification)) {
                _masterPanelMedicineSpecification = masterPanelformedicineSpecificationGrid(medicineSpecificationPanelId);
                Application.UI.addTab(_masterPanelMedicineSpecification);
                _masterPanelMedicineSpecification.doLayout();
            } else {
                Application.UI.addTab(_masterPanelMedicineSpecification);
            }
        }, initIngradients: function () {
            var _ingridentsPanelId = 'panelMasterIngradients';
            var _masterPanelIngradients = Ext.getCmp(_ingridentsPanelId);
            if (Ext.isEmpty(_masterPanelIngradients)) {
                _masterPanelIngradients = masterPanelforIngradients(_ingridentsPanelId);
                Application.UI.addTab(_masterPanelIngradients);
                _masterPanelIngradients.doLayout();
            } else {
                Application.UI.addTab(_masterPanelIngradients);
            }
        }, IngradientsSave: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var prescriptionForm = Ext.getCmp('IngradientsSaveForm').getForm();
            if (prescriptionForm.isValid()) {
                prescriptionForm.submit({
                    url: modURL + '&op=saveIngradients',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);
                            if (Application.MyphaBrands.IngradientsAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterIngradientsDataview'));
                                Ext.getCmp('IngradientsSaveForm').getForm().reset();
                                Ext.getCmp('gridpanelMasterIngradientsDataview').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMasterIngradientsDataview').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMasterIngradientsDataview').getStore().load();
                            }
                            Application.MyphaBrands.IngradientsAddEdit = '';
                            Application.MyphaBrands.ViewIngradients(tmp.data.ingradient_id);
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else {
                            Ext.Msg.alert("Error", tmp.message);
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
        }, ViewIngradients: function () {

            var ingradient_id = arguments[0];
            Ext.getCmp('buttonMasterIngradientsEdit').show();
            Ext.getCmp('buttonMasterIngradientsSave').hide();
            Ext.getCmp('buttonMasterIngradientsCancel').hide();
            Ext.getCmp('IngradientsSaveForm').hide();
            Ext.getCmp('panelMasterIngradientsDetailsView').show();
            Ext.getCmp('panelMasterIngradientsParent').doLayout();
            Ext.getCmp('panelMasterIngradientsParent').setTitle('View Excipients Detail');
            Ext.Ajax.request({
                url: modURL + '&op=IngradientsdetailsView',
                method: 'POST',
                params: {ingradient_id: ingradient_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterIngradientsDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterIngradientsParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterIngradientsParent').doLayout();
        }, EditIngradientsView: function (ingradient_id) {
            Application.MyphaBrands.IngradientsAddEdit = 'Edit';
            Ext.getCmp('panelMasterIngradientsParent').doLayout();
            Ext.getCmp('panelMasterIngradientsParent').setTitle('Edit Excipients Detail');
            Ext.getCmp('IngradientsSaveForm').show();
            Ext.getCmp('panelMasterIngradientsDetailsView').hide();
            Ext.getCmp('buttonMasterIngradientsEdit').hide();
            Ext.getCmp('buttonMasterIngradientsSave').show();
            Ext.getCmp('buttonMasterIngradientsCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var dept_form = Ext.getCmp('IngradientsSaveForm').getForm();
                dept_form.load({
                    params: {
                        ingradient_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadIngradients',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }


                });
            }
        }, mapIngradientstoMedicine: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var _testResultsForm = Ext.getCmp('medIngradientForm').getForm();
            if (_testResultsForm.isValid()) {
                _testResultsForm.submit({
                    url: modURL + '&op=mapIngradientstoMedicine',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        medicineMaster_id: Application.MyphaBrands.Cache.medicineMaster_id,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Ext.getCmp('medIngradientForm').getForm().reset();
                            Application.example.msg('Success', tmp.message);
                            Ext.getCmp('gridpanelIngradientMedicineMap').getStore().reload();
                        } else if (tmp.success === true && tmp.valid === false) {
                            Application.example.msg('Error', tmp.message);
                        } else if (tmp.success === false && tmp.valid === false) {
                            Application.example.msg('Error', tmp.message);
                        } else {
                            Application.example.msg('Error', tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType == 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Status', result.message);
                        } else {
                            Application.example.msg('Warning', 'Fill the required fields');
                        }
                    }
                });
            } else {
                Application.example.msg('Warning', 'Fill the required fields');
            }
        },
        mapAdPrecautionstoMedicine: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var _testResultsForm = Ext.getCmp('medPrecautionForm').getForm();
            if (_testResultsForm.isValid()) {
                _testResultsForm.submit({
                    url: modURL + '&op=mapAdPrecautionstoMedicine',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        medicineMaster_id: Application.MyphaBrands.Cache.medicineMaster_id,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Ext.getCmp('medPrecautionForm').getForm().reset();
                            Application.example.msg('Success', tmp.message);
                            Ext.getCmp('gridpanelprecautionMedicineMap').getStore().reload();
                        } else if (tmp.success === true && tmp.valid === false) {
                            Application.example.msg('Error', tmp.message);
                        } else if (tmp.success === false && tmp.valid === false) {
                            Application.example.msg('Error', tmp.message);
                        } else {
                            Application.example.msg('Error', tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType == 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Status', result.message);
                        } else {
                            Application.example.msg('Warning', 'Fill the required fields');
                        }
                    }
                });
            } else {
                Application.example.msg('Warning', 'Fill the required fields');
            }
        },
        myphMedDetails: function (medicineMaster_id) {
            var form = medicineDetailsForm();
            var medicine_window = Ext.getCmp('medicineDetails_window');
            if (Ext.isEmpty(medicine_window)) {
                medicine_window = new Ext.Window({
                    id: 'medicineDetails_window',
                    title: 'Warnings',
                    layout: 'fit',
                    width: winsize.width * .8,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    height: 300,
                    items: [form],
                    shadow: false,
                    buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            tabindex: 705,
                            handler: function () {
                                medicine_window.close();
                            }
                        }, {
                            text: 'Save',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            tabindex: 706,
                            handler: function () {
                                saveMedicineDetailsData(medicineMaster_id);
                            }
                        }

                    ]
                });
            }
            if (!Ext.isEmpty(medicineMaster_id)) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var dept_form = Ext.getCmp('myphaMedicineDetailsForm').getForm();
                dept_form.load({
                    params: {
                        medicineMaster_id: medicineMaster_id,
                        ID: medicineMaster_id,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadMedicineDetailsData',
                    waitMsg: 'Loading...', success: function (form, action) {
                        eval('var tmp=' + action.response.responseText);
                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                    }
                });
            }
            medicine_window.doLayout();
            medicine_window.show(this);
            medicine_window.center();
        }, medicineDetailsSave: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var prescriptionForm = Ext.getCmp('myphaMedicineDetailsForm').getForm();
            if (prescriptionForm.isValid()) {
                prescriptionForm.submit({
                    url: modURL + '&op=saveMedicineDetailsData',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                        MedId: Application.MyphaBrands.Cache.MedId
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);
                            Ext.getCmp('medicineDetails_window').close();
                            recs_per_page = updateRecsPerPage(Ext.getCmp('panelMasterBrand'));
                            Ext.getCmp('panelMasterBrand').store.reload({
                                params: {
                                    start: 0,
                                    limit: recs_per_page
                                }
                            });
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
                            Ext.Msg.alert('Error', 'Check the required fields');
                        }
                    }
                });
            } else {
                Application.example.msg('Error', 'Check the required fields');
            }
        }
    }
}();