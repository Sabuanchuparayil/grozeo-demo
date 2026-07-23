Application.MyphaComposition = function () {
    var recs_per_page = 12;
    var modURL = '?module=mypha_composition';
    var winsize = Ext.getBody().getViewSize();
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    ;
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                    v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    ;
    var gridSelectionChangedmedComposition = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMastermedCompositionDataview').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMastermedCompositionDataview').getSelectionModel().getSelections()[0].data.composition_id;
            Application.MyphaComposition.ViewmedComposition(ID);
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
            }, ['composition_id', 'composition_name', 'subCategory', 'composition_status', 'subCategory_name', 'contraindications', 'special_precautions', 'interactions', 'adverse_drug_reactions',
                'adrstatus', 'isstatus', 'spstatus', 'csstatus']),
            sortInfo: {
                field: 'composition_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMastermedCompositionDataview').getSelectionModel().selectRow(0);
                }
            }
        });
        return store;
    };
    var medCompositionGrid = function () {
        var medComposition_store = medCompositionStore();
        var medComposition_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'composition_name'
                },
                {
                    type: 'string',
                    dataIndex: 'composition_status'
                }, {
                    type: 'string',
                    dataIndex: 'subCategory_name'
                }, {
                    type: 'string',
                    dataIndex: 'adrstatus'
                },
                {
                    type: 'string',
                    dataIndex: 'isstatus'
                }, {
                    type: 'string',
                    dataIndex: 'spstatus'
                }, {
                    type: 'string',
                    dataIndex: 'csstatus'
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
            store: medComposition_store,
            //iconCls: 'optickets',
            plugins: [medComposition_filter],
            id: 'gridpanelMastermedCompositionDataview',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Multiple API Drug',
                    id: 'composition_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'composition_name',
                    tooltip: 'Multiple API Drug'
                }, {
                    header: 'Drug Group',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'subCategory_name',
                    tooltip: 'Drug Group'
                }, {
                    header: 'Contraindications',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'csstatus',
                    tooltip: 'Contraindications'
                }, {
                    header: 'Special Precautions',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'spstatus',
                    tooltip: 'Special Precautions'
                }, {
                    header: 'Interactions',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'isstatus',
                    tooltip: 'Interactions'
                }, {
                    header: 'Adverse drug reactions',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'adrstatus',
                    tooltip: 'Adverse drug reactions'
                },
                {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'composition_status',
                    tooltip: 'Status'
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
                            mapiActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                    header: 'Action',
                    xtype: 'actioncolumn',
                    hideable: true,
                    sortable: false,
                    groupable: false,
                    items: [
                        {
                            iconCls: 'my-icon96',
                            tooltip: 'Add Details',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var ID = record.get('composition_id');
                                if (!Ext.isEmpty(ID)) {
                                    Application.MyphaComposition.myphSAPIContents(ID, 'multiple');
                                }
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
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedmedComposition
                }

            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('composition_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.MyphaComposition.Cache.composition_id = ID;
                        Ext.getCmp('medCompositionSaveForm').hide();
                        Application.MyphaComposition.ViewmedComposition(ID);
                    }
                },
                viewready: updatePagination,
                afterrender: function () {
                    medComposition_store.load();
                }
            },
            tbar: [/* <?php if (user_access("mypha_composition", "savemedComposition")) { ?> */{
                    xtype: 'button',
                    text: 'Create Multiple API',
                    tooltip: 'Create Multiple API',
                    iconCls: 'add',
                    handler: function () {
                        Application.MyphaComposition.medCompositionAddEdit = 'Add';
                        Application.MyphaComposition.UUID = uuidv4();
                        var packagetypesForm = Ext.getCmp('medCompositionSaveForm').getForm();
                        Ext.getCmp('panelMastermedCompositionParent').setTitle('Create Multiple API Drug Details');
                        loadedForm = null;
                        packagetypesForm.reset();
                        Ext.getCmp('medCompositionForm_name').focus(false, 100);
                        Ext.getCmp('buttonMastermedCompositionEdit').hide();
                        Ext.getCmp('buttonMastermedCompositionSave').show();
                        Ext.getCmp('buttonMastermedCompositionCancel').show();
                        Ext.getCmp('medCompositionSaveForm').show();
                        Ext.getCmp('panelMastermedCompositionDetailsView').hide();
                        Ext.getCmp('panelMastermedCompositionParent').doLayout();
                        var recordSelected = Ext.getCmp('comboMastermedCompositionStatus').getStore().getAt(0);
                        Ext.getCmp('comboMastermedCompositionStatus').setValue(recordSelected.get('id'));
                        Ext.getCmp('medContents').setValue('');
                        Ext.getCmp('contentCompos_grid_panel').getStore().load({
                            params: {
                                uuid: Application.MyphaComposition.UUID,
                                composition_id: Ext.getCmp('medComposition_id').getValue()
                            }
                        });


                    }

                }/*<?php  } ?>*/],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: medComposition_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [medComposition_filter],
            }),
            stripeRows: true,
            autoExpandColumn: 'composition_name_auto_exp'
        });
        return grid_panel;
    };
    var mapiActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {
                    var composition_id = Ext.getCmp('gridpanelMastermedCompositionDataview').getSelectionModel().getSelections()[0].data.composition_id;
                    if (!Ext.isEmpty(composition_id)) {
                                    Application.MyphaComposition.myphSAPIContents(composition_id, 'multiple');
                                }
                }
            }]
    }); 
    
    var fnmedSubCategoryStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=medSubCategory',
            method: 'post',
            fields: ['subCategory_id', 'subCategory_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return natureOfGroupStore;
    };
    var medCompositionForm = function () {
        var medsubCategoryStore = fnmedSubCategoryStore();
        var medCompositionFormSaveForm = new Ext.form.FormPanel({
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            id: 'medCompositionSaveForm',
            items: [{
                    xtype: 'hidden',
                    id: 'medComposition_id',
                    name: 'n[composition_id]'
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Multiple API Drug',
                    id: 'medCompositionForm_name',
                    name: 'n[composition_name]',
                    anchor: '98%',
                    tabIndex: 1001,
                    allowBlank: false
                }, {
                    hiddenName: 'n[subCategory_id]',
                    xtype: 'combo',
                    name: 'category_name',
                    fieldLabel: 'Drug Group',
                    id: 'subCategory',
                    anchor: '98%',
                    displayField: 'subCategory_name',
                    valueField: 'subCategory_id',
                    store: medsubCategoryStore,
                    triggerAction: 'all',
                    forceSelection: true,
                    selectOnFocus: true,
                    mode: 'local',
                    typeAhead: true,
                    lazyRender: true,
                    editable: true,
                    minChars: 1,
                    tabIndex: 1002
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[composition_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 1003,
                    "emptyText": "Set status..",
                    "id": 'comboMastermedCompositionStatus'
                }), contentGrid()
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('medComposition_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMastermedCompositionStatus').getStore().getAt(0);
                        Ext.getCmp('comboMastermedCompositionStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return medCompositionFormSaveForm;
    };

    var fnmedContentsStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=medContents',
            method: 'post',
            fields: ['composition_id', 'composition_name'],
            totalProperty: "totalCount",
            root: "data",
            remoteSort: true,
            autoLoad: true
        });
        return natureOfGroupStore;
    };
    var contentStoreComposition = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getContentsofComposition',
            method: 'post',
            fields: ['composition_id', 'content_name', 'content_id'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteFilter: true,
            autoload: true

        });

        return store;
    };
    var contentGrid = function () {

        var content_store = contentStoreComposition();
        var medContentsStore = fnmedContentsStore();
        var contentgrid_panel = new Ext.grid.GridPanel({
            store: content_store,
            enableColumnMove: false,
            frame: true,
            border: false,
            title: '',
            autoScroll: true,
            height: 300,
            id: 'contentCompos_grid_panel',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            columns: [
                {
                    header: 'Contents',
                    sortable: true,
                    dataIndex: 'content_name',
                    width: 430,
                    align: 'left'

                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            iconCls: 'my-icon12',
                            tooltip: 'Delete',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var composition_id = Ext.getCmp('medComposition_id').getValue();
                                var content_id = record.get('content_id');
                                var uuid = Application.MyphaComposition.UUID;
                                Application.MyphaComposition.deletContent(composition_id, content_id, uuid);
                            }
                        }]
                }
            ],
            tbar: [{html: '&nbsp;Ingredients:&nbsp;'},
                {
                    hiddenName: 'medContents',
                    xtype: 'combo',
                    name: 'medContents',
                    fieldLabel: 'Contents',
                    id: 'medContents',
                    anchor: '100%',
                    displayField: 'composition_name',
                    valueField: 'composition_id',
                    store: medContentsStore,
                    triggerAction: 'all',
                    forceSelection: true,
                    selectOnFocus: true,
                    mode: 'local',
                    emptyText: 'Contents',
                    typeAhead: true,
                    lazyRender: true,
                    editable: true,
                    minChars: 1,
                    tabIndex: 1004,
                    width: 350
                },
                {html: '&nbsp;'},
                {
                    xtype: 'button',
                    text: 'Add',
                    iconCls: 'finascop_add',
                    id: 'item_addbtn',
                    handler: function () {
                        addContentsToComposition();
                    }
                }
            ],
            stripeRows: true,
        });
        return contentgrid_panel;
    };
    var addContentsToComposition = function () {
        Ext.Ajax.request({
            waitTitle: 'Please Wait',
            waitMsg: 'Sending...',
            url: modURL + '&op=mapContenttoComposition',
            params: {
                uuid: Application.MyphaComposition.UUID,
                content_id: Ext.getCmp('medContents').getValue(),
                composition_id: Ext.getCmp('medComposition_id').getValue()
            },
            success: function (response, options) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true) {
                    Ext.getCmp('medContents').reset();
                    Ext.getCmp('contentCompos_grid_panel').getStore().load({
                        params: {
                            uuid: Application.MyphaComposition.UUID,
                            composition_id: Ext.getCmp('medComposition_id').getValue()
                        }
                    });

                }
            }
        });
    };
    var medCompositionMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMastermedCompositionDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Multiple API Drug </th><td>  {composition_name} </td></tr>',
                    '<tr><th width="40%">Drug Group </th><td>  {subCategory} </td></tr>',
                    '<tr><th width="40%">Content </th><td>  {content} </td></tr>',
                    '<tr><th width="40%">Contraindications </th><td>  {contraindications} </td></tr>',
                    '<tr><th width="40%">Special Precautions </th><td>  {special_precautions} </td></tr>',
                    '<tr><th width="40%">Interactions </th><td>  {interactions} </td></tr>',
                    '<tr><th width="40%">Adverse drug reactions </th><td>  {adverse_drug_reactions} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="composition_status == \'1\'">Active</tpl>',
                    '<tpl if="composition_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var medCompositionPanel = function (id) {
        var medCompositionPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Multiple API',
            id: id,
            //iconCls: 'company',
            items: [
                medCompositionGrid(),
                new Ext.Panel({
                    title: 'Multiple API Drug Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMastermedCompositionParent',
                    height: winsize.height * 0.6,
                    items: [medCompositionForm(), medCompositionMasterDetailsView()],
                    buttonAlign: 'right',
                    fbar: [
                        {
                            text: "Cancel",
                            tabIndex: 1005,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMastermedCompositionCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMastermedCompositionDataview').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMastermedCompositionDataview').getSelectionModel().getSelections()[0].data.composition_id;
                                    Application.MyphaComposition.ViewmedComposition(ID);
                                }
                            }
                        },
                        {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMastermedCompositionEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 1006,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMastermedCompositionDataview').getSelectionModel().getSelections()[0].data.composition_id;
                                Application.MyphaComposition.EditmedCompositionView(ID);
                            }
                        },
                        /* <?php if (user_access("mypha_composition", "savemedComposition")) { ?> */
                        {
                            text: "Save",
                            tabIndex: 1004,
                            cls: 'left-right-buttons',
                            id: 'buttonMastermedCompositionSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                medCompositionSave();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return medCompositionPanel;
    };
    var medCompositionSave = function () {
        var form_data = {
            form: Ext.getCmp('medCompositionSaveForm').getForm().getValues()
        };
        if (Ext.getCmp('medComposition_id').getValue() > 0) {
            var mstId = Ext.getCmp('medComposition_id').getValue();
        } else {
            var d = new Date();
            var mstId = '-';
        }
        var params = {
            action: 'Save Multiple API Drug',
            module: 'MyphaComposition',
            op: 'savemedComposition',
            extrainfo: 'Multiple API Drug save',
            id: mstId
        };
        APICall(params, Application.MyphaComposition.medCompositionSave, form_data);
    };
    var SAPIContentsForm = function () {
        var form = new Ext.FormPanel({
            id: 'myphaSAContentForm',
            height: 300,
            frame: true,
            border: true,
            labelAlign: 'top',
            items: [{
                    layout: 'column',
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .5,
                            padding: '3px',
                            items: [{
                                    xtype: 'textarea',
                                    fieldLabel: 'Contraindictions',
                                    emptyText: 'Contraindictions',
                                    name: 'contraindications',
                                    id: 'contraindications',
                                    anchor: '100%',
                                    height: 110,
                                    tabIndex: 21

                                }, {
                                    xtype: 'textarea',
                                    fieldLabel: 'Interactions',
                                    emptyText: 'Interactions',
                                    name: 'interactions',
                                    id: 'interactions',
                                    anchor: '100%',
                                    height: 110,
                                    tabIndex: 23

                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .5,
                            padding: '3px',
                            items: [{
                                    xtype: 'textarea',
                                    fieldLabel: 'Special Precautions',
                                    emptyText: 'Special Precautions',
                                    name: 'special_precautions',
                                    id: 'special_precautions',
                                    anchor: '100%',
                                    height: 110,
                                    tabIndex: 22

                                }, {
                                    xtype: 'textarea',
                                    fieldLabel: 'Adverse Drug Reactions',
                                    emptyText: 'Adverse Drug Reactions',
                                    name: 'adverse_drug_reactions',
                                    id: 'adverse_drug_reactions',
                                    anchor: '100%',
                                    height: 110,
                                    tabIndex: 24

                                }]
                        }
                    ]}
            ]
        });
        return form;
    };

    var saveSAPIContents = function (sapcId, type) {
        var prescriptionForm = Ext.getCmp('myphaSAContentForm').getForm();
        if (prescriptionForm.isValid()) {
            var form_data = {
                form: Ext.getCmp('myphaSAContentForm').getForm().getValues()
            };
            Application.MyphaComposition.Cache.sapcId = sapcId;
            Application.MyphaComposition.Cache.sapType = type;
            var params = {
                action: 'Save API Drug Details',
                module: 'Master',
                op: 'saveDisease',
                extrainfo: 'Disease save',
                id: sapcId
            };
            APICall(params, Application.MyphaComposition.apiDrugDetailsSave, form_data);
        } else {
            Application.example.msg('Error', 'Check the required fields');
        }
    };
    var _mapApiStrengthGridStore = function (compositionId) {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + "&op=listApistrengths",
                method: "post"
            }),
            reader: new Ext.data.JsonReader(
                    {
                        totalProperty: "totalCount",
                        root: "data"
                    },
            ["composition_id", "composition_name", "composition_strength", "subCategory_id", "composition_map", "composition_status", "statuscomp"]
                    ),
            sortInfo: {
                field: "composition_id",
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
                    this.baseParams.composition_map = compositionId;
                }
            }
        });
        return _store;
    };
    var apiStrengthShowGrid = function (compositionMapId) {

        var mapApiStrength_store = _mapApiStrengthGridStore(compositionMapId);
        var _mapmedIngrad_grid = new Ext.grid.GridPanel({
            id: 'apiStrength_grid_panel',
            store: mapApiStrength_store,
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
                    header: 'Strength',
                    sortable: true,
                    dataIndex: 'composition_strength',
                    tooltip: 'Strength',
                    hideable: false,
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'statuscomp',
                    tooltip: 'Status',
                    hideable: false,
                },
                {
                    xtype: 'actioncolumn',
                    hideable: false,
                    items: [
                        {
                            tooltip: 'Add Details',
                            iconCls: 'my-icon96',
                            handler: function (grid, rowIndex, colIndex)
                            {
                                var record = grid.getStore().getAt(rowIndex);
                                var ID = record.get('composition_id');
                                console.log('composition_id', ID);
                                if (!Ext.isEmpty(ID)) {
                                    Application.MyphaComposition.myphSAPIContents(ID, 'single');
                                }
                            }
                        }, {
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _type_name = data.composition_status;
                                if (_type_name == 1) {
                                    return 'approve';
                                } else {
                                    return 'reject';
                                }
                                this.items[1].tooltip = 'Status change';
                            },
                            handler: function (grid, rowIndex, colIndex)
                            {
                                var record = grid.getStore().getAt(rowIndex);
                                var ID = record.get('composition_id');
                                var composition_status = record.get('composition_status');
                                if (!Ext.isEmpty(ID)) {
                                    Application.MyphaComposition.StatusChange(ID, composition_status, compositionMapId);
                                }


                            }
                        }

                    ]
                }
            ],
            listeners: {
                afterrender: function () {
                    mapApiStrength_store.load();
                }

            },
            stripeRows: true
        });
        return _mapmedIngrad_grid;
    };

    return {
        Cache: {},
        initMedCOmposition: function () {

            var _medCompositionPanelId = 'panelMasterParentmedComposition';
            var _masterPanelmedComposition = Ext.getCmp(_medCompositionPanelId);
            if (Ext.isEmpty(_masterPanelmedComposition)) {
                _masterPanelmedComposition = medCompositionPanel(_medCompositionPanelId);
                Application.UI.addTab(_masterPanelmedComposition);
                _masterPanelmedComposition.doLayout();
            } else {
                Application.UI.addTab(_masterPanelmedComposition);
            }
        }, ViewmedComposition: function () {

            var composition_id = arguments[0];
            Ext.getCmp('buttonMastermedCompositionEdit').show();
            Ext.getCmp('buttonMastermedCompositionSave').hide();
            Ext.getCmp('buttonMastermedCompositionCancel').hide();
            Ext.getCmp('medCompositionSaveForm').hide();
            Ext.getCmp('panelMastermedCompositionDetailsView').show();
            Ext.getCmp('panelMastermedCompositionParent').doLayout();
            Ext.getCmp('panelMastermedCompositionParent').setTitle('View Multiple API Drug Details');
            Ext.Ajax.request({
                url: modURL + '&op=medCompositiondetailsView',
                method: 'POST',
                params: {composition_id: composition_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMastermedCompositionDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMastermedCompositionParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMastermedCompositionParent').doLayout();
        }, EditmedCompositionView: function () {

            Application.MyphaComposition.medCompositionAddEdit = 'Edit';
            Ext.getCmp('panelMastermedCompositionParent').doLayout();
            Ext.getCmp('panelMastermedCompositionParent').setTitle('Edit Multiple API Drug Details');
            Ext.getCmp('medCompositionSaveForm').show();
            Ext.getCmp('panelMastermedCompositionDetailsView').hide();

            Ext.getCmp('buttonMastermedCompositionEdit').hide();
            Ext.getCmp('buttonMastermedCompositionSave').show();
            Ext.getCmp('medContents').setValue('');
            Ext.getCmp('buttonMastermedCompositionCancel').show();
            if (!Ext.isEmpty(arguments[0])) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var dept_form = Ext.getCmp('medCompositionSaveForm').getForm();
                dept_form.load({
                    params: {
                        composition_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadmedComposition',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                        Ext.getCmp('subCategory').setValue(tmp.data.subCategory);
                        Ext.getCmp('subCategory').getStore().load();
                        Ext.getCmp('contentCompos_grid_panel').getStore().load({
                            params: {
                                uuid: Application.MyphaComposition.UUID,
                                composition_id: Ext.getCmp('medComposition_id').getValue()
                            }
                        });


                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }


                });
            }
        }, medCompositionSave: function () {
            var composition_id = Ext.getCmp('medComposition_id').getValue();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var prescriptionForm = Ext.getCmp('medCompositionSaveForm').getForm();
            if (prescriptionForm.isValid()) {
                prescriptionForm.submit({
                    url: modURL + '&op=savemedComposition',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                        uuid: Application.MyphaComposition.UUID
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);
                            if (Application.MyphaComposition.medCompositionAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMastermedCompositionDataview'));
                                Ext.getCmp('medCompositionSaveForm').getForm().reset();
                                Ext.getCmp('gridpanelMastermedCompositionDataview').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelMastermedCompositionDataview').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('gridpanelMastermedCompositionDataview').getView().refresh(
                                        {
                                            callback: function (record, options, success) {
                                                var gridPanel = Ext.getCmp('gridpanelMastermedCompositionDataview');
                                                var index = gridPanel.store.find('composition_id', composition_id);
                                                gridPanel.getSelectionModel().selectRow(index);
                                            }
                                        }
                                );
                            }
                            Application.MyphaComposition.medCompositionAddEdit = '';
                            Application.MyphaComposition.UUID = '';
                            Application.MyphaComposition.ViewmedComposition(tmp.data.composition_id);
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
        }, deletContent: function (composition_id, content_id, uuid) {
           Ext.MessageBox.confirm('Confirm', 'Do you want to remove this Content?', function (btn, text) {
           if (btn == 'yes') {
            Ext.Ajax.request({
                waitTitle: 'Please Wait',
                waitMsg: 'Sending...',
                url: modURL + '&op=deleteContent',
                params: {
                    composition_id: composition_id,
                    content_id: content_id,
                    uuid: uuid
                },
                success: function (response, options) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Application.example.msg('Success', tmp.message);
                        Ext.getCmp('contentCompos_grid_panel').getStore().load({
                            params: {
                                uuid: Application.MyphaComposition.UUID,
                                composition_id: Ext.getCmp('medComposition_id').getValue()
                            }
                        });

                    } else {
                        Ext.Msg.alert('Error', tmp.message);
                    }
                }
            });
             }
        });
        }, CSV_upload: function () {

            var win = new Ext.Window({
                layout: 'fit',
                width: 400,
                autoHeight: true,
                frame: false,
                border: false,
                title: 'Upload File',
                icon: './resources/images/default/icons/upload_fl.png',
                //iconCls: 'upload',
                modal: true,
                shadow: false,
                floating: true,
                items: [
                    new Ext.form.FormPanel({
                        labelAlign: 'top',
                        labelSeparator: '',
                        bodyStyle: {
                            "background-color": "white",
                            "padding": "5px 5px 5px 10px"
                        },
                        autoHeight: true,
                        id: 'csv_form',
                        frame: false,
                        border: false,
                        fileUpload: true,
                        items: [
                            {
                                layout: 'form',
                                columnWidth: .3,
                                items: [{
                                        xtype: 'combo',
                                        displayField: 'name',
                                        valueField: 'id',
                                        mode: 'local',
                                        fieldLabel: 'Table',
                                        hiddenName: 'csv_table',
                                        id: 'csv_table',
                                        allowBlank: false,
                                        anchor: '98%',
                                        tabIndex: 9,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        store: new Ext.data.JsonStore(
                                                {
                                                    fields: ['id', 'name'],
                                                    data: [{
                                                            id: 'Category',
                                                            name: 'Category'
                                                        },
                                                        {
                                                            id: 'Subcategory',
                                                            name: 'Subcategory'
                                                        },
                                                        {
                                                            id: 'Manufacture',
                                                            name: 'Manufacture'
                                                        }
                                                    ]


                                                })
                                    }, {
                                        fieldLabel: 'Upload File',
                                        labelAlign: 'top',
                                        xtype: 'fileuploadfield',
                                        accept: '.csv',
                                        id: 'excel_file',
                                        allowBlank: false,
                                        name: 'excel_file',
                                        tabIndex: 1,
                                        msgTarget: 'under',
                                        anchor: '98%',
                                        validator: function (v) {
                                            if (v != '') {
                                                //var exp = /^.*.(.xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                                                var exp = /^.*\.csv$/;
                                                if (!(exp.test(v))) {
                                                    return 'Upload a valid CSV file.';
                                                }
                                                return true;
                                            }
                                            var filen = Ext.getCmp('excel_file').getValue();
                                            if (filen == '') {
                                                return 'Upload a file.';
                                            }
                                        }
                                    }],
                                buttons: [
                                    {
                                        iconCls: 'csv',
                                        text: 'Upload',
                                        handler: function () {
                                            var csv_form = Ext.getCmp('csv_form').getForm();
                                            var table = Ext.getCmp('csv_table').getValue();
                                            var t = new Date();
                                            var t_stamp = t.format("YmdHis");
                                            if (csv_form.isValid()) {
                                                csv_form.submit({
                                                    url: modURL + '&op=uploadcsvFile',
                                                    waitTitle: 'Please Wait..',
                                                    waitMsg: 'Saving data...',
                                                    params: {
                                                        table: table,
                                                        apikey: _SESSION.apikey,
                                                        tstamp: t_stamp
                                                    },
                                                    success: function (csv_form, action) {
                                                        var result = Ext.decode(action.response.responseText);
                                                        if (result.valid === true && result.success === true) {
                                                            win.close();
                                                            Application.example.msg('Notification', 'Details saved Successfully. contentid' + result.contentid + '$dupvalues' + result.$dupvalues);
                                                        }
                                                    },
                                                    failure: function () {
                                                        Ext.Msg.alert('Error', 'Supplied CSV File could not be validated. ', function (btn) {
                                                            if (btn === 'ok') {
                                                                Ext.Msg.hide();
                                                                win.close();
                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        }
                                    }]
                            }
                        ]
                    })
                ]
            });
            win.show();
            win.doLayout();
            win.center();
        }, myphSAPIContents: function (sapcId, type) {

            var form = SAPIContentsForm(sapcId);
            var SAPIContent_window = Ext.getCmp('SAPIContent_window');
            if (Ext.isEmpty(SAPIContent_window)) {
                SAPIContent_window = new Ext.Window({
                    id: 'SAPIContent_window',
                    title: 'API Drug Details',
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
                            tabindex: 26,
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            handler: function () {
                                SAPIContent_window.close();
                            }
                        }, {
                            text: 'Save',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            tabindex: 25,
                            handler: function () {
                                saveSAPIContents(sapcId, type);
                            }
                        }

                    ]
                });
            }
            if (!Ext.isEmpty(sapcId)) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var dept_form = Ext.getCmp('myphaSAContentForm').getForm();
                dept_form.load({
                    params: {
                        composition_id: sapcId,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadSAPCDetailsData',
                    waitMsg: 'Loading...', success: function (form, action) {
                        eval('var tmp=' + action.response.responseText);
                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                    }
                });
            }
            SAPIContent_window.doLayout();
            SAPIContent_window.show(this);
            SAPIContent_window.center();
        }, apiDrugDetailsSave: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var prescriptionForm = Ext.getCmp('myphaSAContentForm').getForm();
            if (prescriptionForm.isValid()) {
                prescriptionForm.submit({
                    url: modURL + '&op=saveAPIContentDetailsData',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                        composition_id: Application.MyphaComposition.Cache.sapcId
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);
                            Ext.getCmp('SAPIContent_window').close();
                            if (Application.MyphaComposition.Cache.sapType == 'multiple') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMastermedCompositionDataview'));
                                Ext.getCmp('gridpanelMastermedCompositionDataview').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewCompositiondata'));
                                Ext.getCmp('gridpanelMasterDataviewCompositiondata').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
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
                            Ext.Msg.alert('Error', 'Check the required fields');

                        }
                    }
                });
            } else {
                Application.example.msg('Error', 'Check the required fields');

            }
        }, myphaAPIStrength: function (sapiId, type) {

            var sapiStrengthmapWindow = Ext.getCmp('windowMyphaApiStrength');
            if (Ext.isEmpty(sapiStrengthmapWindow)) {
                var sapiStrengthmapWindow = new Ext.Window({
                    id: 'windowMyphaApiStrength',
                    title: 'API Strength',
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
                                    items: [{
                                            layout: "column",
                                            border: false,
                                            items: [
                                                {
                                                    columnWidth: 0.30,
                                                    layout: "form",
                                                    border: false,
                                                    labelWidth: 50,
                                                    style: 'margin-top:17px;margin-bottom:5px;',
                                                    items: [
                                                        {
                                                            xtype: "textfield",
                                                            fieldLabel: "Strength",
                                                            id: "medStrength",
                                                            name: "medStrength",
                                                            anchor: "98%",
                                                            allowBlank: false,
                                                            tabIndex: 602
                                                        }
                                                    ]
                                                },
                                                {
                                                    columnWidth: 0.20,
                                                    layout: "form",
                                                    border: false,
                                                    style: 'margin-top:17px;margin-bottom:5px;',
                                                    items: [
                                                        {
                                                            xtype: 'button',
                                                            tooltip: 'Add New ',
                                                            text: 'Add',
                                                            iconCls: 'add',
                                                            width: 60,
                                                            tabIndex: 604,
                                                            handler: function () {
                                                                Ext.Ajax.request({
                                                                    waitTitle: 'Please Wait',
                                                                    waitMsg: 'Sending...',
                                                                    url: modURL + '&op=mapStrengthtoApi',
                                                                    params: {
                                                                        sapiId: sapiId,
                                                                        type: type,
                                                                        medStrength: Ext.getCmp('medStrength').getValue()
                                                                    },
                                                                    success: function (response, options) {
                                                                        var tmp = Ext.decode(response.responseText);
                                                                        if (tmp.success === true) {
                                                                            Application.example.msg('Success', tmp.message);
                                                                        } else {
                                                                            Ext.Msg.alert("Notification.", tmp.message);
                                                                        }
                                                                        Ext.getCmp('apiStrength_grid_panel').getStore().load({
                                                                            params: {
                                                                                composition_map: sapiId
                                                                            }
                                                                        });
                                                                    }
                                                                });
                                                            }
                                                        }
                                                    ]
                                                }
                                            ]
                                        }]
                                },
                                {
                                    region: 'center',
                                    layout: 'fit',
                                    items: apiStrengthShowGrid(sapiId)
                                }
                            ]
                        })
                    ]
                });
            }
            sapiStrengthmapWindow.show();
            sapiStrengthmapWindow.doLayout();
            sapiStrengthmapWindow.center();
        }, StatusChange: function (id, status, sapiId) {
            Ext.Ajax.request({
                url: modURL + '&op=compstatusChange',
                method: 'POST',
                waitMsg: 'Processing',
                params: {
                    composition_id: id,
                    composition_status: status
                },
                failure: function (response, options) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', 'tmp.msg');
                },
                success: function (response, options) {
                    var tmp = Ext.decode(response.responseText);

                    if (tmp.success === true) {
                        Application.example.msg('Success', tmp.msg);
                        Ext.getCmp('apiStrength_grid_panel').getStore().load({
                            params: {
                                composition_map: sapiId
                            }
                        });
                    } else {
                        Ext.MessageBox.alert("Error", tmp.msg);
                    }
                }
            });
        },
    };
}();