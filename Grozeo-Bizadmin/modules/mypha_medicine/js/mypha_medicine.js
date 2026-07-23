Application.MyphaMedicine = function () {
    var recs_per_page = 12;
    var modURL = '?module=mypha_medicine';
    var winsize = Ext.getBody().getViewSize();
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    ;
    var gridSelectionChangedMedicine = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('mypharmaMedicine').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('mypharmaMedicine').getSelectionModel().getSelections()[0].data.medicineMaster_id;
            Application.MyphaMedicine.Cache.medId = ID;
            Application.MyphaMedicine.ViewMedicine(ID);
        }
    };
    var medicineStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMedicines',
                method: 'post'
            }),
            fields: ['medicineMaster_id', 'medicineMaster_name', 'category_name', 'manufacture_name', 'medicine_type_name', 'photo_count'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp('mypharmaMedicine').getSelectionModel().selectRow(0);
                }
            }
        });
        store.setDefaultSort('medicineMaster_name', 'ASC');
        return store;
    };
    var medicineGrid = function () {
        var centre_store = medicineStore();
        var centre_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    name: 'medicineMaster_name'
                }, {
                    type: 'string',
                    name: 'category_name'
                }, {
                    type: 'string',
                    name: 'medicine_type_name'
                }]
        });
        centre_filter.remote = true;
        centre_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: centre_store,
            layout: 'fit',
            region: 'center',
            frame: false,
            border: false,
            //iconCls: 'company',
            plugins: [centre_filter],
            id: 'mypharmaMedicine',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Medicine',
                    id: 'dept_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'medicineMaster_name',
                    tooltip: 'Medicine'
                },
                {
                    header: 'Type',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'medicine_type_name',
                    tooltip: 'Type'
                }, {
                    header: 'Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'category_name',
                    tooltip: 'Category'
                }, {
                    header: 'Is Photo',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'photo_count',
                    tooltip: 'Is Photo'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            iconCls: 'edit',
                            tooltip: 'Edit Details',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var medicineMaster_id = record.get('medicineMaster_id');
                                Application.MyphaMedicine.myphMedicine(medicineMaster_id);
                            }
                        }, {
                            iconCls: 'my-icon43',
                            tooltip: 'Upload Images',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var medicineMaster_id = record.get('medicineMaster_id');
                            }
                        }, {
                            iconCls: 'edit_profile',
                            tooltip: 'Warnings',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var medicineMaster_id = record.get('medicineMaster_id');
                                Application.MyphaMedicine.myphMedWarninig(medicineMaster_id);
                            }
                        }]
                }],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMedicine
                }
            }),
            listeners: {
                viewready: updatePagination
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Add New',
                    iconCls: 'add',
                    handler: function () {
                        Application.MyphaMedicine.myphMedicine('');
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: centre_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [centre_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'dept_name_auto_exp'
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
    var fnmedCategoryStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=medCategory',
            method: 'post',
            fields: ['category_id', 'category_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return natureOfGroupStore;
    };
    var fndiseaseStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=medDisease',
            method: 'post',
            fields: ['disease_id', 'disease_name'],
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
    var medicineForm = function () {
        var manufactureStore = fnmanufactureStore();
        var medTypeStore = fnmedTypeStore();
        var medCategoryStore = fnmedCategoryStore();
        var diseaseStore = fndiseaseStore();
        var compositionStore = fncompositionStore();
        var form = new Ext.FormPanel({
            layout: 'form',
            id: 'myphaMedicineForm',
            columnWidth: 1,
            height: 490,
            frame: true,
            border: true,
            labelAlign: 'top',
            items: [{
                    xtype: 'hidden',
                    id: 'medicineMaster_id',
                    name: 'medicineMaster_id'
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Name of a medicine',
                    emptyText: 'Name of a medicine',
                    id: 'medicineMaster_name',
                    name: 'medicineMaster_name',
                    anchor: '98%',
                    tabIndex: 1,
                    allowBlank: false
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            columnWidth: .33,
                            layout: 'form',
                            border: false,
                            items: [{
                                    fieldLabel: 'Manufacture',
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
                                    tabIndex: 2,
                                }]
                        }, {
                            columnWidth: .33,
                            layout: 'form',
                            border: false,
                            items: [{
                                    hiddenName: 'medicine_type',
                                    xtype: 'combo',
                                    name: 'medicine_type',
                                    fieldLabel: 'Type',
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
                                    tabIndex: 3,
                                    typeAhead: true,
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 1
                                }]
                        }, {
                            columnWidth: .33,
                            layout: 'form',
                            border: false,
                            items: [{
                                    hiddenName: 'medicine_category',
                                    xtype: 'combo',
                                    name: 'category_name',
                                    fieldLabel: 'Category',
                                    id: 'medicine_category',
                                    anchor: '98%',
                                    displayField: 'category_name',
                                    valueField: 'category_id',
                                    store: medCategoryStore,
                                    triggerAction: 'all',
                                    forceSelection: true,
                                    tabIndex: 4,
                                    selectOnFocus: true,
                                    mode: 'local',
                                    typeAhead: true,
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 1
                                }]
                        }
                    ]
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            columnWidth: .7,
                            layout: 'form',
                            border: false,
                            items: [{
                                    fieldLabel: 'Medicine Content(Salt Composition)',
                                    xtype: 'combo',
                                    displayField: 'composition_name',
                                    valueField: 'composition_id',
                                    mode: 'local',
                                    id: 'medicine_content',
                                    hiddenName: 'medicine_content',
                                    name: 'medicine_content',
                                    typeAhead: true,
                                    minChars: 3,
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    lazyRender: true,
                                    anchor: '98%',
                                    hideTrigger: true,
                                    store: compositionStore,
                                    editable: true,
                                    msgTarget: 'under',
                                    tabIndex: 5,
                                }]
                        }, {
                            columnWidth: .28,
                            layout: 'form',
                            border: false,
                            items: [{
                                    hiddenName: 'medicine_diseases',
                                    xtype: 'lovcombo',
                                    name: 'medicine_diseases',
                                    fieldLabel: 'Medicine used for',
                                    id: 'medicine_diseases',
                                    anchor: '100%',
                                    displayField: 'disease_name',
                                    valueField: 'disease_id',
                                    store: diseaseStore,
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    mode: 'local',
                                    forceSelection: true,
                                    tabIndex: 6,
                                    typeAhead: true,
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 1
                                }]
                        }

                    ]
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'About Medicine (Overview)',
                    emptyText: 'About Medicine',
                    anchor: '98%',
                    name: 'medicine_about',
                    id: 'medicine_about',
                    height: 75,
                    tabIndex: 7

                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [{
                                    layout: 'column',
                                    columnWidth: 1,
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .9,
                                            items: [{
                                                    xtype: 'textarea',
                                                    fieldLabel: 'How to use',
                                                    emptyText: 'How to use',
                                                    name: 'medicine_use',
                                                    id: 'medicine_use',
                                                    anchor: '100%',
                                                    height: 70,
                                                    tabIndex: 8

                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .1,
                                            defaults: {
                                                style: 'margin-top:14px'
                                            },
                                            items: [{
                                                    xtype: 'button',
                                                    iconCls: 'Search-icon',
                                                    scale: 'large',
                                                    anchor: '70%',
                                                    tabIndex: 9,
                                                    handler: function () {
                                                        searchWindow('Use');
                                                    }

                                                }]
                                        }]
                                }]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [{
                                    layout: 'column',
                                    columnWidth: 1,
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .9,
                                            items: [
                                                {
                                                    xtype: 'textarea',
                                                    fieldLabel: 'Side Effects',
                                                    emptyText: 'Side Effects',
                                                    anchor: '100%',
                                                    name: 'medicine_sideeffects',
                                                    id: 'medicine_sideeffects',
                                                    height: 70,
                                                    tabIndex: 10

                                                }]}, {
                                            layout: 'form',
                                            columnWidth: .1,
                                            defaults: {
                                                style: 'margin-top:18px'
                                            },
                                            items: [{
                                                    xtype: 'button',
                                                    iconCls: 'Search-icon',
                                                    anchor: '70%',
                                                    tabIndex: 11,
                                                    scale: 'large',
                                                    handler: function () {
                                                        searchWindow('SF');
                                                    }
                                                }]
                                        }]
                                }
                            ]
                        }]
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [{
                                    layout: 'column',
                                    columnWidth: 1,
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .9,
                                            items: [{
                                                    xtype: 'textarea',
                                                    fieldLabel: 'How it works',
                                                    emptyText: 'How it works',
                                                    name: 'medicine_works',
                                                    id: 'medicine_works',
                                                    anchor: '100%',
                                                    height: 70,
                                                    tabIndex: 12

                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .1,
                                            defaults: {
                                                style: 'margin-top:18px'
                                            },
                                            items: [{
                                                    xtype: 'button',
                                                    anchor: '70%',
                                                    iconCls: 'Search-icon',
                                                    scale: 'large',
                                                    tabIndex: 13,
                                                    handler: function () {
                                                        searchWindow('Works');
                                                    }

                                                }]
                                        }]
                                }
                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [{
                                    layout: 'column',
                                    columnWidth: 1,
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .9,
                                            items: [{
                                                    xtype: 'textarea',
                                                    fieldLabel: 'More information',
                                                    emptyText: 'More information ie,storage details etc.',
                                                    name: 'medicine_morInfo',
                                                    id: 'medicine_morInfo',
                                                    height: 70,
                                                    anchor: '100%',
                                                    tabIndex: 14

                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .1,
                                            defaults: {
                                                style: 'margin-top:18px'
                                            },
                                            items: [{
                                                    xtype: 'button',
                                                    anchor: '70%',
                                                    iconCls: 'Search-icon',
                                                    tabIndex: 15,
                                                    scale: 'large',
                                                    handler: function () {
                                                        searchWindow('Info');
                                                    }
                                                }]
                                        }]
                                }]
                        }]
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: .4,
                            defaults: {
                                style: 'margin-top:16px'
                            },
                            items: [{
                                    xtype: 'checkbox',
                                    checked: true,
                                    name: 'medicine_isPrescription',
                                    id: 'medicine_isPrescription',
                                    boxLabel: '<strong>Prescription required</strong>',
                                    autoHeight: true,
                                    inputValue: true,
                                    uncheckedValue: false,
                                    hideLabel: true,
                                    tabIndex: 16
                                }]

                        }]
                }]});
        return form;
    };
    var dashboardFilterForm = function () {
        var type = arguments[0];
        var form = new Ext.form.FormPanel({
            frame: false,
            border: false,
            width: winsize.width * .6,
            hideBorders: true,
            hideLabel: true,
            modal: true,
            autoScroll: true,
            layout: 'column',
            items: [
                {
                    columnWidth: .85,
                    layout: 'form',
                    border: false,
                    bodyStyle: {"background-color": "white", "padding": "5px"},
                    items: [
                        {
                            hideLabel: true,
                            xtype: 'textfield',
                            id: 'search_field',
                            emptyText: 'Enter search value',
                            name: 'n[search_field]',
                            allowBlank: false,
                            anchor: '98%',
                            listeners: {
                                change: function () {
                                    var search_field = Ext.getCmp('search_field').getValue();

                                    Ext.getCmp('dash_search_grid').getStore().load({
                                        params: {
                                            search_field: search_field,
                                            type: type
                                        }
                                    });
                                }
                            }
                        }
                    ]
                }, {
                    columnWidth: .15,
                    layout: 'form',
                    border: false,
                    bodyStyle: {"background-color": "white", "padding": "5px"},
                    items: [{
                            xtype: 'button',
                            text: 'Search',
                            handler: function () {
                                var search_field = Ext.getCmp('search_field').getValue();
                                Ext.getCmp('dash_search_grid').getStore().load({
                                    params: {
                                        search_field: search_field,
                                        type: type
                                    }
                                });
                            }
                        }
                    ]
                },
                {
                    columnWidth: 1,
                    height: 300,
                    border: false,
                    bodyStyle: {"background-color": "white", "padding": "5px"},
                    title: type + ' Data',
                    items: [dashboardSearchGrid(type)]
                }
            ]
        });
        return form;
    };

    var dashboardSearchGridStore = function () {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=dashboardSearchGridStore',
            root: 'data',
            idProperty: 'search_id',
            autoLaod: false,
            totalProperty: 'totalCount',
            fields: ['search_name', 'search_id']
        });
        return store;
    };
    var dashboardSearchGrid = function () {
        var type = arguments[0];
        var dashboardSearchStore = dashboardSearchGridStore();

        var grid = new Ext.grid.GridPanel({
            id: 'dash_search_grid',
            autoScroll: true,
            animCollapse: false,
            frame: false,
            viewConfig: {
                forceFit: true, markDirty: false
            },
            stripeRows: true,
            layout: 'fit',
            border: false,
            hideBorders: true,
            store: dashboardSearchStore,
            height: 250,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: type,
                    sortable: true,
                    dataIndex: 'search_name',
                    tooltip: type,
                }], listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                },
                celldblclick: function () {
                    var data = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections();
                    if (data != '') {
                        var search_id = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.search_id;
                        var search_name = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.search_name;

                        if (!Ext.isEmpty(search_id) && !Ext.isEmpty(search_name)) {
                            Ext.Ajax.request({
                                waitTitle: 'Please Wait',
                                waitMsg: 'Sending...',
                                url: modURL + '&op=getSearchDetails',
                                params: {
                                    search_name: search_name,
                                    search_id: search_id,
                                    type: type
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        Ext.getCmp('dashboardFilterWindow').close();
                                        switch (type) {
                                            case 'Use':
                                                Ext.getCmp('medicine_use').setValue(tmp.searchValue);
                                                break;
                                            case 'SF':
                                                Ext.getCmp('medicine_sideeffects').setValue(tmp.searchValue);
                                                break;
                                            case 'Works':
                                                Ext.getCmp('medicine_works').setValue(tmp.searchValue);
                                                break;
                                            case 'Info':
                                                Ext.getCmp('medicine_morInfo').setValue(tmp.searchValue);
                                                break;
                                            case 'Warning':
                                                Ext.getCmp('med_warnings').setValue(tmp.searchValue);
                                                break;
                                        }
                                    }
                                }
                            });
                        } else {
                            Ext.MessageBox.alert("Notification", "Should have a member.");
                        }
                    }
                }
            }
        });
        return grid;
    };
    var searchWindow = function (type) {
        console.log('type', type);
        var dashboard_filter_form = dashboardFilterForm(type);
        var dashboardFilterWindow = new Ext.Window({
            id: 'dashboardFilterWindow',
            modal: true,
            constrain: true,
            width: winsize.width * .6,
            resizable: false,
            floating: true,
            title: type + ' Search',
            shadow: false,
            items: [dashboard_filter_form],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    text: 'Close',
                    handler: function () {
                        dashboardFilterWindow.close();
                    }
                },
                {
                    text: 'Select',
                    width: 80,
                    handler: function () {
                        var data = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections();
                        if (data != '') {
                            var search_id = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.search_id;
                            var search_name = Ext.getCmp('dash_search_grid').getSelectionModel().getSelections()[0].data.search_name;

                            //Ext.getCmp('filter_value').setRawValue(search_name);
                            if (!Ext.isEmpty(search_id) && !Ext.isEmpty(search_name)) {
                                Ext.Ajax.request({
                                    waitTitle: 'Please Wait',
                                    waitMsg: 'Sending...',
                                    url: modURL + '&op=getSearchDetails',
                                    params: {
                                        search_name: search_name,
                                        search_id: search_id,
                                        type: type
                                    },
                                    success: function (response, options) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true) {
                                            dashboardFilterWindow.close();
                                            switch (type) {
                                                case 'Use':
                                                    Ext.getCmp('medicine_use').setValue(tmp.searchValue);
                                                    break;
                                                case 'SF':
                                                    Ext.getCmp('medicine_sideeffects').setValue(tmp.searchValue);
                                                    break;
                                                case 'Works':
                                                    Ext.getCmp('medicine_works').setValue(tmp.searchValue);
                                                    break;
                                                case 'Info':
                                                    Ext.getCmp('medicine_morInfo').setValue(tmp.searchValue);
                                                    break;
                                                case 'Warning':
                                                    Ext.getCmp('med_warnings').setValue(tmp.searchValue);
                                                    break;
                                            }

                                        }
                                    }
                                });
                            }

                        }
                    }
                }
            ]

        });
        Ext.getCmp('search_field').focus(false, 100);
        var dashboardFilterWindow = Ext.getCmp('dashboardFilterWindow');
        dashboardFilterWindow.show();
        dashboardFilterWindow.doLayout();
        dashboardFilterWindow.center();
    };
    var saveMedicineData = function (medId, type) {
        Application.MyphaMedicine.medSaveType = type;
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
                action: 'Save Medicine' + type,
                module: 'Master',
                op: 'saveDisease',
                extrainfo: 'Disease save',
                id: mstId
            };
            APICall(params, Application.MyphaMedicine.medicineSave, form_data);
        } else {
            Application.example.msg('Error', 'Check the required fields');
        }

    };
    var medicineParentPanel = function (id) {

        var _medicineParentPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Medicine',
            id: id,
            //iconCls: 'company',
            items: [
                medicineGrid(),
                new Ext.Panel({
                    title: 'Medicine Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterMedicine',
                    height: winsize.height * 0.6,
                    layout: 'border',
                    items: [{
                            region: 'north',
                            height: 300,
                            items: medicineDetailsView()
                        }, {
                            region: 'center',
                            layout: 'fit',
                            items: medWarningGrid(Application.MyphaMedicine.Cache.medId, 'view')
                        }],
                    buttonAlign: 'right'
                })
            ]
        });
        return _medicineParentPanel;
    };
    var medicineDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplateMasterMedicineViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Medicine </th><td>  {medicineMaster_name} </td></tr>',
                    '<tr><th width="40%">Category </th><td>  {category_name} </td></tr>',
                    '<tr><th width="40%">Type </th><td>  {medicine_type_name} </td></tr>',
                    '<tr><th width="40%">Manufacture </th><td>  {manufacture_name} </td></tr>',
                    '<tr><th width="40%">Content </th><td>  {composition_name} </td></tr>',
                    '<tr><th width="40%">About Medicine </th><td>  {medicine_about} </td></tr>',
                    '<tr><th width="40%">Medicine Use </th><td>  {medicine_use} </td></tr>',
                    '<tr><th width="40%">Medicine Side Effects </th><td>  {medicine_sideeffects} </td></tr>',
                    '<tr><th width="40%">Medicine Works </th><td>  {medicine_works} </td></tr>',
                    '<tr><th width="40%">Additional Info </th><td>  {medicine_morInfo} </td></tr>',
                    '<tr><th width="40%">Diseases </th><td>  {disease} </td></tr>',
                    '<tr><th width="40%">Presription </th><td>  {medicine_isPrescription} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var storeWarningCatgeoryStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=medWarningCategory',
            fields: ['warningCategory_id', 'warningCategory_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true
        });
        return natureOfGroupStore;
    };
    var medWarningForm = function (medId) {
        var warningCatgeoryStore = storeWarningCatgeoryStore();
        var poItemStockformPanel = new Ext.form.FormPanel({
            height: 200,
            frame: true,
            border: false,
            id: 'medWarningFormPanel',
            layout: 'column',
            labelAlign: 'top',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.9,
                            items: [
                                {
                                    xtype: 'textarea',
                                    hideLabel: true,
                                    emptyText: 'Type warning message here',
                                    id: 'med_warnings',
                                    name: 'med_warnings',
                                    anchor: '100%'
                                }
                            ]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.1,
                            style: 'margin-top:5px;',
                            items: [{
                                    xtype: 'button',
                                    iconCls: 'Search-icon',
                                    scale: 'large',
                                    anchor: '50%',
                                    handler: function () {
                                        searchWindow('Warning');
                                    }

                                }]
                        }]
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.4,
                            items: [{
                                    xtype: 'combo',
                                    hideLabel: true,
                                    emptyText: 'Choose warning releated to',
                                    id: 'warning_Catgeory',
                                    name: 'warning_Catgeory',
                                    anchor: '99%',
                                    displayField: 'warningCategory_name',
                                    valueField: 'warningCategory_id',
                                    hiddenName: 'warning_Catgeory',
                                    store: warningCatgeoryStore,
                                    triggerAction: 'all',
                                    allowBlank: false,
                                    mode: 'remote',
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    enableKeyEvents: true,
                                    autoSelect: true,
                                    listeners: {
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.2,
                            style: 'margin-top:3px;',
                            items: [{
                                    xtype: 'button',
                                    text: 'Save',
                                    handler: function () {
                                        if (!Ext.isEmpty(Ext.getCmp('med_warnings').getValue() && Ext.getCmp('warning_Catgeory').getValue())) {
                                            var warningCat = Ext.getCmp('warning_Catgeory').getValue();
                                            var warnMsg = Ext.getCmp('med_warnings').getValue();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=addWarningsToMed',
                                                method: 'POST',
                                                params: {
                                                    warningCat: warningCat,
                                                    warnMsg: warnMsg,
                                                    medId: medId
                                                },
                                                success: function (response) {
                                                    var res = Ext.decode(response.responseText);
                                                    if (res.success === true) {
                                                        Application.example.msg('Success', res.msg);
                                                        Ext.getCmp('medWarningGridPanel').getStore().load({
                                                            params: {
                                                                medId: medId
                                                            }
                                                        });
                                                        Ext.getCmp('medWarningFormPanel').getForm().reset();
                                                    } else if (res.error) {
                                                        Ext.MessageBox.alert("Error", res.error);
                                                    } else {
                                                        Ext.MessageBox.alert("Notification", res.msg);
                                                    }
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", 'Please fill the fields');
                                        }
                                    }

                                }]
                        }]

                }]
        });
        return poItemStockformPanel;
    };
    var storemedWarningEntryGridStore = function (medId) {
        var warningStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listMedicineWarnings',
            fields: ['medicineWarnings_id', 'medicineMaster_id', 'medicinWarnings', 'warningCategory_id', 'warningCategory_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        medId: medId
                    };
                }

            }
        });
        return warningStore;
    };

    var medWarningGrid = function (medId, diffId) {
        var medWarningEntryGridStore = storemedWarningEntryGridStore(medId);
        var medWarningEntryGrid = new Ext.grid.GridPanel({
            store: medWarningEntryGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'medWarningGridPanel' + diffId,
            loadMask: true,
            columns: [
                {
                    header: 'Warnings',
                    sortable: true,
                    dataIndex: 'warningCategory_name',
                    tooltip: 'Warnings',
                    width: 50
                },
                {
                    header: '',
                    sortable: true,
                    dataIndex: 'medicinWarnings',
                    tooltip: 'Warnings'
                }],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {},
            stripeRows: true,
        });
        return medWarningEntryGrid;
    };
    return {
        Cache: {},
        initMedicine: function () {
            var panelId = 'parentPanelFormypharmaMedicine';
            var medicineParent_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(medicineParent_panel)) {
                medicineParent_panel = medicineParentPanel(panelId);
                Application.UI.addTab(medicineParent_panel);
                medicineParent_panel.doLayout();
            } else {
                Application.UI.addTab(medicineParent_panel);
            }
        },
        myphMedicine: function (medId) {

            var form = medicineForm();
            var medicine_window = Ext.getCmp('medicine_window');
            if (Ext.isEmpty(medicine_window)) {
                medicine_window = new Ext.Window({
                    id: 'medicine_window',
                    title: 'Medicine Details',
                    layout: 'fit',
                    width: winsize.width * .8,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    height: 490,
                    items: [form],
                    shadow: false,
                    buttons: [{text: 'SAVE & CLOSE',
                            tabindex: 17,
                            handler: function () {
                                saveMedicineData(medId, 'SC');
                            }
                        }, {
                            text: 'SAVE & ADD NEW',
                            tabindex: 17,
                            handler: function () {
                                saveMedicineData(medId, 'SA');
                            }
                        }, {
                            text: 'SAVE & KEEP DATA',
                            tabindex: 19,
                            handler: function () {
                                saveMedicineData(medId, 'SK');

                            }
                        }

                    ]
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
                        Ext.getCmp('medicine_category').setValue(tmp.data.medicine_category);
                        Ext.getCmp('medicine_category').setRawValue(tmp.data.category_name);
                        Ext.getCmp('medicine_category').getStore().load();
                        Ext.getCmp('medicine_type').setValue(tmp.data.medicine_type);
                        Ext.getCmp('medicine_type').setRawValue(tmp.data.medicine_type_name);
                        Ext.getCmp('medicine_type').getStore().load();
                        Ext.getCmp('medicine_diseases').setValue(tmp.data.medicine_type);
                        Ext.getCmp('medicine_diseases').getStore().load();
                        Ext.getCmp('medicine_manufacture').setValue(tmp.data.medicine_manufacture);
                        Ext.getCmp('medicine_manufacture').setRawValue(tmp.data.manufacture_name);
                        Ext.getCmp('medicine_manufacture').getStore().load();

                    },
                    failure: function (form, action) {
                    }
                });
            }
            medicine_window.doLayout();
            medicine_window.show(this);
            medicine_window.center();
        },
        medicineSave: function () {

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
                        type: Application.MyphaMedicine.medSaveType
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            switch (Application.MyphaMedicine.medSaveType) {
                                case 'SC':
                                    Ext.getCmp('medicine_window').close();
                                    break;
                                case 'SA':
                                    Ext.getCmp('myphaMedicineForm').getForm().reset();
                                    break;
                                case 'SK':
                                    Ext.getCmp('medicineMaster_id').reset();
                                    break;
                            }
                            Application.example.msg('Success', tmp.message);
                            recs_per_page = updateRecsPerPage(Ext.getCmp('mypharmaMedicine'));
                            Ext.getCmp('mypharmaMedicine').store.reload({
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
        }, ViewMedicine: function (ID) {

            var medicineMaster_id = arguments[0];
            Ext.getCmp('xtemplateMasterMedicineViewDetails').show();
            Ext.getCmp('panelMasterMedicine').doLayout();
            Ext.getCmp('panelMasterMedicine').setTitle('Medicine Details');
            Ext.Ajax.request({
                url: modURL + '&op=medicineDetailsView',
                method: 'POST',
                params: {medicineMaster_id: medicineMaster_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplateMasterMedicineViewDetails');
                        visualsDescPanel.update(tmp);
                        Ext.getCmp('medWarningGridPanelview').getStore().load({
                            params: {
                                medId: medicineMaster_id
                            }
                        });
                    }
                    Ext.getCmp('panelMasterMedicine').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterMedicine').doLayout();
        }, myphMedWarninig: function (medId) {
            var medicineWarninig_window = Ext.getCmp('medicineWarninig_window');
            if (Ext.isEmpty(medicineWarninig_window)) {
                medicineWarninig_window = new Ext.Window({
                    id: 'medicineWarninig_window',
                    title: 'Medicine Warnings',
                    layout: 'border',
                    width: winsize.width * .6,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    height: 500,
                    items: [
                        {
                            region: 'center',
                            layout: 'fit',
                            height: 200,
                            items: medWarningForm(medId)
                        }, {
                            region: 'south',
                            layout: 'fit',
                            height: 300,
                            items: medWarningGrid(medId, '')
                        }
                    ],
                    shadow: false,
                    buttons: [{
                            text: 'CLOSE',
                            tabindex: 17,
                            handler: function () {
                                medicineWarninig_window.close();
                                Ext.getCmp('medWarningGridPanelview').getStore().load({
                                    params: {
                                        medId: medId
                                    }
                                });
                            }
                        }
                    ]
                });
            }
            if (!Ext.isEmpty(medId)) {

            }
            medicineWarninig_window.doLayout();
            medicineWarninig_window.show(this);
            medicineWarninig_window.center();
        }

    }

}();