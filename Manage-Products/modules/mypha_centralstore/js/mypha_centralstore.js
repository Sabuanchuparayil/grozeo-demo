Application.MyphaCentralStore = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=mypha_centralstore';
    var recs_per_page = 12;
    var my_marker;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var totalComboCount;
    var myMask, centralStoreLoadedForm;
    var district_ID = new Array();
    var comboLoadComplete = function () {

        if (!Ext.isEmpty(centralStoreLoadedForm) && centralStoreLoadedForm != null) {
            centralStoreLoadedForm[0].loadRecord(Ext.decode(centralStoreLoadedForm[1]));
        }
        if (totalComboCount == 0) {
            myMask.hide();
            centralStoreLoadedForm = null;
        }
    };
    var centralStoreJsonStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCentralStores',
                method: 'post'
            }),
            fields: ['br_ID', 'br_Name', 'br_District', 'br_State', 'br_Address', 'br_Fax', 'br_defRetailor', 'br_defDistributor', 'br_storeGroup',
                'br_Email', 'br_Phone', 'br_Incharge', 'br_status', 'company', 'branch_shortname', 'comp_id', 'br_pincode', 'br_Lat', 'br_Lng', 'br_csdefault'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        store.setDefaultSort('br_Name', 'ASC');
        return store;
    };

    var createCentralStoreGrid = function (id) {
        var centralStore_jsonstore = centralStoreJsonStore();
        var centralStore_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'branch_shortname'
                }, {
                    type: 'string',
                    dataIndex: 'br_District'
                }, {
                    type: 'string',
                    dataIndex: 'br_State'
                }, {
                    type: 'string',
                    dataIndex: 'br_Fax'
                }, {
                    type: 'string',
                    dataIndex: 'br_Email'
                }, {
                    type: 'string',
                    dataIndex: 'br_Phone'
                }, {
                    type: 'string',
                    dataIndex: 'br_Incharge'
                }, {
                    type: 'string',
                    dataIndex: 'company'
                }]
        });
        centralStore_filter.remote = true;
        centralStore_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: centralStore_jsonstore,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Central Stores',
            plugins: [centralStore_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Central Store Name',
                    id: 'centralStore_name',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Central Store Name'
                },
                {
                    header: 'Central Store Short Name',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'branch_shortname',
                    tooltip: 'Central Store Short Name'
                },
                {
                    header: 'Company',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'company',
                    tooltip: 'Company'
                },
                {
                    header: 'District',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_District',
                    tooltip: 'District'
                },
                {
                    header: 'State',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_State',
                    tooltip: 'State'
                },
                {
                    header: 'Smart Phone',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Fax',
                    tooltip: 'Smart Phone'
                },
                {
                    header: 'Email',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Email',
                    tooltip: 'Email'
                },
                {
                    header: 'Contact No.',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Phone',
                    tooltip: 'Contact No.'
                },
                {
                    header: 'Incharge',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_Incharge',
                    tooltip: 'Incharge'
                },
                {
                    header: 'Default Distributor',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_defDistributor',
                    tooltip: 'Default Distributor'
                },
                {
                    header: 'Default Retailor',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'br_defRetailor',
                    tooltip: 'Default Retailor'
                },
                {
                    header: 'Status',
                    id: 'br_status',
                    sortable: true,
                    dataIndex: 'br_status'
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
                            var br_status = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_status;
                            //console.log('br_status:'+ br_status);
                            if (br_status == 'Inactive')
                                centralStoreActionMenu2.showAt(e.getXY());
                            else
                                centralStoreActionMenu1.showAt(e.getXY());

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
                 tooltip: 'Action',
                 items: [{
                 iconCls: 'edit',
                 tooltip: 'Edit Central Store Details',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var map_Lat = record.get('br_Lat');
                 var map_Long = record.get('br_Lng');
                 centralStoreDetails(record.get('br_ID'), map_Lat, map_Long);
                 
                 
                 }
                 },
                 {
                 getClass: function (v, meta, rec) {
                 if (rec.get('br_status') == 'Active')
                 {
                 this.items[1].tooltip = 'Deactivate Central Store';
                 return 'now_active';
                 } else
                 {
                 this.items[1].tooltip = 'Activate Central Store';
                 return 'now_inactive';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Central Store?', function (btn, text) {
                 if (btn == 'yes')
                 {
                 changeBranchStatus(record.get('br_ID'), record.get('br_status'), record.get('comp_id'));
                 }
                 });
                 }
                 }, {
                 iconCls: 'icon-add-table',
                 tooltip: 'Configuration Setting',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var br_ID = record.get('br_ID');
                 Application.MyphaCentralStore.Cache.br_ID = br_ID;
                 ConfigurationWindow(br_ID);
                 }
                 }, {
                 getClass: function (v, meta, rec) {
                 var data = rec.data;
                 var _isDefault = data.br_csdefault;
                 if (_isDefault == 0) {
                 this.items[2].tooltip = 'Set Default';
                 //return 'user_disabled';
                 return 'hideicon';
                 }
                 else {
                 //return 'user_enabled';
                 return 'hideicon';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex, itm, evn) {
                 var record = grid.getStore().getAt(rowIndex);
                 Ext.Ajax.request({waitMsg: 'Processing',
                 url: modURL,
                 params: {
                 op: 'setCSDefault',
                 br_ID: record.get('br_ID'),
                 pyramid: 2
                 },
                 failure: function (response, options) {
                 Ext.MessageBox.alert('Notification', ACTION_FAIL);
                 },
                 success: function (response, options) {
                 eval("var tmp=" + response.responseText);
                 if (tmp.success === true) {
                 Application.example.msg('Notification', tmp.msg);
                 Ext.getCmp(id).getStore().reload();
                 }
                 }
                 });
                 }
                 }]
                 }*/
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('br_csdefault') == 1)
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
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
                    text: 'Create Central Store',
                    tooltip: 'Create Central Store',
                    iconCls: 'add',
                    handler: function () {
                        centralStoreDetails(0, 8.507007481504532, 76.95167541503906);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: centralStore_jsonstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [centralStore_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'centralStore_name'
        });
        return grid_panel;
    };
    var centralStoreActionMenu1 = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {

                    var br_ID = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_Lat = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_Lat;
                    var br_Lng = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_Lng;
                    centralStoreDetails(br_ID, br_Lat, br_Lng);
                }
            }, {
                text: "Deactivate",
                handler: function () {
                    //var record = grid.store.getAt(rowIndex);
                    var br_ID = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_status = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_status;
                    var comp_id = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.comp_id;
                    Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Central Store?', function (btn, text) {
                        if (btn == 'yes')
                        {
                            changeBranchStatus(br_ID, br_status, comp_id);
                        }
                    });
                }
            }, {
                text: "Configuration Setting",
                handler: function () {
                    var br_ID = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_ID;
                    Application.MyphaCentralStore.Cache.br_ID = br_ID;
                    ConfigurationWindow('br_ID');
                }
            }]
    });
    var centralStoreActionMenu2 = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {

                    var br_ID = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_Lat = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_Lat;
                    var br_Lng = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_Lng;
                    centralStoreDetails(br_ID, br_Lat, br_Lng);
                }
            }, {
                text: "Activate",
                handler: function () {
                    var br_ID = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_status = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_status;
                    var comp_id = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.comp_id;
                    Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Central Store?', function (btn, text) {
                        if (btn == 'yes')
                        {
                            changeBranchStatus(br_ID, br_status, comp_id);
                        }
                    });
                }
            }, {
                text: "Configuration Setting",
                handler: function () {
                    var br_ID = Ext.getCmp('panelCentralStore').getSelectionModel().getSelections()[0].data.br_ID;
                    Application.MyphaCentralStore.Cache.br_ID = br_ID;
                    ConfigurationWindow('br_ID');
                }
            }]
    });

    var changeBranchStatus = function () {

        Application.MyphaCentralStore.br_ID = arguments[0];
        Application.MyphaCentralStore.br_status = arguments[1];
        Application.MyphaCentralStore.comp_id = arguments[2];
        var form_data = {
            br_ID: Application.MyphaCentralStore.br_ID,
            br_status: Application.MyphaCentralStore.br_status,
            comp_id: Application.MyphaCentralStore.comp_id
        };
        var params = {
            action: 'Update',
            module: 'mypha_centralstore',
            op: 'changeStatus',
            extrainfo: 'asd',
            id: Application.MyphaCentralStore.br_ID

        };

        APICall(params, Application.MyphaCentralStore.changeStatus, form_data);
    };

    var brComboStore = function (ind) {
        return  new Ext.data.ArrayStore({
            url: modURL + '&op=getComboStore&ind=' + ind,
            method: 'POST',
            autoLoad: true,
            fields: ['id', 'name'],
            listeners: {
                load: function (store, rec, opt) {
                    if (store.totalLength > 0) { //totalLength == 1
                        if (ind == 3) {
                            Ext.getCmp('br_Company').setValue(store.getAt(0).get("id"));
                        }

                    }
                }
            }
        });
    };

    var centralStoreForm = function (map_lat, map_long) {
        my_marker = [{
                lat: map_lat,
                lng: map_long,
                marker: {
                    title: "you are here",
                    draggable: false
                },
                listeners: {
                    "onFailure": function () {
                        Ext.MessageBox.alert('Failed locating city ');
                    },
                    "onSuccess": function (point) {

                    },
                    "dragend": function (markerAt) {
                        Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                        Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                    }
                }
            }];

        var form = new Ext.FormPanel({
            labelAlign: 'top',
            autoHeight: true,
            url: modURL + "&op=saveCentralStore",
            width: 850,
            frame: true,
            id: 'centralstore_form',
            layout: 'column',
            items: [{
                    layout: 'form',
                    columnWidth: 0.45,
                    hideBorders: true,
                    defaults: {
                        xtype: 'textfield',
                        anchor: '95%',
//                        allowBlank: false,
                        //style: 'margin:10px 0 10px 0',
                        hideBorders: true
                    },
                    items: [{
                            xtype: 'combo',
                            store: brComboStore(3),
                            mode: 'remote',
                            id: 'br_Company',
                            fieldLabel: 'Company',
                            hiddenName: 'br_Company',
                            displayField: 'name',
                            valueField: 'id',
                            editable: true,
                            typeAhead: true,
                            minChars: 2,
                            selectOnFocus: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            allowBlank: false,
                            tabIndex: 1,
                            listeners: {
//                                        afterrender: function (field) {
//                                        Ext.defer(function () {
//                                        field.focus(true, 100);
//                                    }, 1);
//                                        }
                            }
                        }, {
                            xtype: 'hidden',
                            allowBlank: true,
                            id: 'br_ID',
                            name: 'br_ID'
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.7,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            fieldLabel: 'Central Store Name',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'br_Name',
                                            allowBlank: false,
                                            name: 'br_Name',
                                            maxLength: 250,
                                            tabIndex: 2,
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1);
                                                }
                                            }
                                        }],
                                }, {
                                    columnWidth: 0.3,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Central Store Code',
                                            xtype: 'textfield',
                                            anchor: '98%',
                                            id: 'branch_shortname',
                                            name: 'branch_shortname',
                                            allowBlank: false,
                                            minLength: 4,
                                            maxLength: 4,
                                            tabIndex: 3
                                        }]
                                }]
                        },
                        {
                            fieldLabel: 'Address',
                            id: 'br_Address',
                            name: 'br_Address',
                            allowBlank: false,
                            maxLength: 765,
                            height: 90,
                            xtype: 'textarea',
                            tabIndex: 4
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.4,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            store: brComboStore(1),
                                            mode: 'local',
                                            id: 'br_State',
                                            anchor: '98%',
                                            fieldLabel: 'State',
                                            hiddenName: 'br_State',
                                            displayField: 'name',
                                            valueField: 'id',
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            allowBlank: false,
                                            tabIndex: 5,
                                            listeners: {
                                                select: function () {
                                                    var dist = Ext.getCmp('br_District');
                                                    var dist_str = dist.getStore();
                                                    dist.reset();
                                                    dist_str.removeAll();
                                                    dist_str.baseParams = {
                                                        ind: 2,
                                                        state: this.getValue()
                                                    };
                                                    dist_str.load();
                                                }
                                            }
                                        }],
                                }, {
                                    columnWidth: 0.4,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            xtype: 'combo',
                                            store: brComboStore(2),
                                            mode: 'local',
                                            anchor: '98%',
                                            id: 'br_District',
                                            fieldLabel: 'District',
                                            hiddenName: 'br_District',
                                            displayField: 'name',
                                            valueField: 'id',
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            allowBlank: false,
                                            tabIndex: 6,
                                        }]
                                },
                                {
                                    columnWidth: 0.2,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Post Code',
                                            id: 'br_pincode',
                                            anchor: '98%',
                                            xtype: 'textfield',
                                            name: 'br_pincode',
                                            //vtype: 'phonespec',
                                            allowBlank: false,
                                            tabIndex: 7,
                                            listeners: {
                                                focus: function () {
                                                    if (!Ext.isEmpty(Ext.getCmp('br_pincode').getValue()))
                                                    {
                                                        Ext.getCmp('map_button1').enable();
                                                    }
                                                },
                                                change: function () {
                                                    var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                    Ext.getCmp('br_Lat').setValue(point.lat);
                                                    Ext.getCmp('br_Lng').setValue(point.lng);
                                                    if (!Ext.isEmpty(Ext.getCmp('br_pincode').getValue())) {
                                                        Ext.getCmp('map_button1').enable();
                                                    }
                                                }
                                            }
                                        }]
                                }]
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            id: 'br_Incharge',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            fieldLabel: 'Contact Person',
                                            maxLength: 250,
                                            name: 'br_Incharge',
                                            allowBlank: false,
                                            tabIndex: 8
                                        }],
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Telephone',
                                            xtype: 'numberfield',
                                            anchor: '99%',
                                            id: 'br_Phone',
                                            name: 'br_Phone',
                                            minLength: 10,
                                            maxLength: 10,
                                            msgTarget: 'under',
                                            minLengthText: 'The minimum length for this field is 10',
                                            maxLengthText: 'The maximum length for this field is 10',
                                            allowBlank: false,
                                            //vtype: 'phone',
                                            tabIndex: 9
                                        }]
                                }]
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            fieldLabel: 'Email',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'br_Email',
                                            name: 'br_Email',
                                            allowBlank: false,
                                            regex: /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/,
                                            regexText: 'Invalid Email',
                                            msgTarget: 'under',
                                            vtype: 'email',
                                            tabIndex: 10
                                        }],
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Mobile',
                                            xtype: 'numberfield',
                                            anchor: '99%',
                                            id: 'br_Fax',
                                            name: 'br_Fax',
                                            allowBlank: false,
                                            //vtype: 'phone',
                                            minLength: 10,
                                            maxLength: 10,
                                            msgTarget: 'under',
                                            minLengthText: 'The minimum length for this field is 10',
                                            maxLengthText: 'The maximum length for this field is 10',
                                            tabIndex: 11
                                        }]
                                }]
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            store: brComboStore(5),
                                            mode: 'local',
                                            id: 'br_storeGroup',
                                            allowBlank: false,
                                            fieldLabel: 'Store Group',
                                            hiddenName: 'br_storeGroup',
                                            displayField: 'name',
                                            valueField: 'id',
                                            anchor: '95%',
                                            typeAhead: true,
                                            editable: true,
                                            minChars: 2,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 12
                                        }]
                                }, {
                                    columnWidth: 0.5,
                                    hidden: true,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: []
                                }]
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.5,
                                    hidden: true,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [
                                        {
                                            xtype: 'combo',
                                            displayField: 'ptname',
                                            valueField: 'ptid',
                                            anchor: '98%',
                                            mode: 'local',
                                            allowBlank: true,
                                            id: 'br_stockLevel',
                                            name: 'br_stockLevel',
                                            hiddenName: 'br_stockLevel',
                                            forceSelection: true,
                                            fieldLabel: 'Stock Level',
                                            emptyText: 'Stock Level',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            //tabIndex: 12,
                                            store: new Ext.data.JsonStore({
                                                fields: ['ptid', 'ptname'],
                                                data: [{ptid: 1, ptname: 'Level 1'}, {ptid: 2, ptname: 'Level 2'}, {ptid: 3, ptname: 'Level 3'}]
                                            })
                                        }
                                    ],
                                },
                                {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    hidden: true,
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            xtype: 'combo',
                                            store: brComboStore(4),
                                            mode: 'remote',
                                            id: 'br_cpd',
                                            anchor: '98%',
                                            fieldLabel: 'Central Store',
                                            hiddenName: 'br_cpd',
                                            allowBlank: true,
                                            displayField: 'name',
                                            valueField: 'id',
                                            typeAhead: true,
                                            selectOnFocus: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            //tabIndex: 13
                                        }]
                                }]
                        },
                        {
                            xtype: 'fieldset',
                            anchor: '98%',
                            layout: 'column',
                            hideBorders: true,
                            border: false,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .4,
                                    hidden: true,
                                    items: [{
                                            xtype: 'checkbox',
                                            id: 'id_apibranch',
                                            name: 'br_defaultapi',
                                            allowBlank: true,
                                            boxLabel: 'Default Central Store',
                                            listeners: {
                                                check: function (checkbox, checked) {
                                                    if (checked == true) {
                                                        Ext.getCmp('apibranch').setValue('1');
                                                    } else {
                                                        Ext.getCmp('apibranch').setValue('0');
                                                    }
                                                }
                                            }
                                        },
                                        {
                                            xtype: 'hidden',
                                            id: 'apibranch',
                                            name: 'br_defaultapibranch',
                                            value: 0
                                        },
                                        {
                                            xtype: 'hidden',
                                            allowBlank: true,
                                            id: 'br_PyramidLevel',
                                            name: 'br_PyramidLevel',
                                            value: 2
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .3,
                                    items: [{
                                            xtype: 'checkbox',
                                            hidden: true,
                                            id: 'br_sales',
                                            name: 'br_sales',
                                            allowBlank: true,
                                            boxLabel: 'Sales',
                                            checked: true,
                                            inputValue: 1,
                                            listeners: {
                                                check: function (checkbox, checked) {
                                                }
                                            }
                                        }]
                                }]
                        }
//                        {
//                            xtype: 'hidden',
//                            id: 'br_IsCPD',
//                            name: 'br_IsCPD',
//                            allowBlank: true,
//                            value: 1
//                        }
                    ]
                },
                {
                    layout: 'form',
                    columnWidth: 0.55,
                    title: 'MAP',
                    region: 'center',
                    items: [{
                            xtype: 'gmappanel',
                            gmapType: 'map',
                            id: 'branchgooglemap',
                            zoomLevel: 8,
                            height: 380,
                            minGeoAccuracy: 4,
                            scaleControl: true,
                            mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
                            mapControls: ['GSmallMapControl', 'GMapTypeControl'],
                            setCenter: {
                                lat: map_lat,
                                lng: map_long
                            },
                            repaint: function (zoomlevel) {
                                var gmappanel = Ext.getCmp('branchgooglemap');
                                if (zoomlevel) {
                                    gmappanel.zoomLevel = zoomlevel;
                                    gmappanel.getMap().setZoom(zoomlevel);
                                }
                                gmappanel.onMapReady();

                            },
                            markers: my_marker
                        },
                        {
                            layout: 'form',
                            columnWidth: .5,
                            //style: 'margin-left:5px;',
                            items: [{
                                    xtype: 'fieldset',
                                    title: 'Map Coordinates',
                                    //height: 200,
                                    autoHeight: true,
                                    items: [{
                                            layout: 'column',
                                            items: [{
                                                    layout: 'form',
                                                    columnWidth: .3,
                                                    items: [{
                                                            xtype: 'button',
                                                            fieldLabel: 'Coordinates',
                                                            tooltip: 'Locate latitude and longitude',
                                                            icon: IMAGE_BASE_PATH + "/default/icons/map.png",
                                                            iconCls: 'my-icon1',
                                                            id: 'map_button1',
                                                            name: 'map_button1',
                                                            disabled: true,
                                                            text: 'Find Coordinates',
                                                            tabIndex: 13,
                                                            handler: function () {
                                                                Ext.getCmp('branchgooglemap').clearMarkers();
                                                                my_marker = [];
                                                                //Ext.getCmp('branchgooglemap').clearMarkers();
                                                                my_marker.push({
                                                                    geoCodeAddr: Ext.getCmp("br_pincode").getValue(),
                                                                    setCenter: true,
                                                                    marker: {
                                                                        title: "Click and Drag to Move Around",
                                                                        draggable: true
                                                                    },
                                                                    listeners: {
                                                                        onFailure: function () {

                                                                        },
                                                                        "tilesloaded": function (markerAt) {
                                                                            Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                                                                            Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                                                                        },
                                                                        onSuccess: function (point) {
                                                                            Ext.getCmp('br_Lat').setValue(point.latLng.lat());
                                                                            Ext.getCmp('br_Lng').setValue(point.latLng.lng());
                                                                        },
                                                                        "dragend": function (markerAt) {
                                                                            Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                                                                            Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                                                                        }
                                                                    },
                                                                    icon: null
                                                                });
                                                                Ext.getCmp('branchgooglemap').addScaleControl();
                                                                Ext.getCmp('branchgooglemap').clearMarkers();
                                                                Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                Ext.defer(function () {
                                                                    var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                                    Ext.getCmp('br_Lat').setValue(point.lat);
                                                                    Ext.getCmp('br_Lng').setValue(point.lng);
                                                                    Ext.getCmp('branchgooglemap').clearMarkers();
                                                                    Ext.getCmp('branchgooglemap').repaint(13);
                                                                    Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                }, 1200);
                                                            }
                                                        }]
                                                }, {
                                                    layout: 'form',
                                                    columnWidth: .35,
                                                    items: [{
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Latitude',
                                                            //style: 'padding-left:5px;',
                                                            id: 'br_Lat',
                                                            name: 'br_Lat',
                                                            tabIndex: 14,
                                                            allowBlank: false,
                                                            anchor: '95%'
                                                        }]
                                                }, {
                                                    layout: 'form',
                                                    columnWidth: .35,
                                                    items: [{
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Longitude',
                                                            id: 'br_Lng',
                                                            name: 'br_Lng',
                                                            tabIndex: 15,
                                                            allowBlank: false,
                                                            anchor: '95%'
                                                        }]
                                                }]
                                        }]
                                }]
                        }
                    ]
                }]

        });
        return form;
    };

    var centralStoreDetails = function () {

        var br_ID = arguments[0];
        var map_lat = arguments[1];
        var map_long = arguments[2];
        var win_id = "centralstore_details_window";

        var centralstore_details_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(centralstore_details_window)) {
            var centralStore_form = centralStoreForm(map_lat, map_long);
            centralstore_details_window = new Ext.Window({
                id: win_id,
                title: 'Create Central Store',
                layout: 'fit',
                height: 585,
                width: 860,
                autoScroll: true,
                plain: true,
                modal: true,
                frame: true,
                resizable: false,
                items: centralStore_form,
                fbar: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            centralstore_details_window.close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            if (centralStore_form.getForm().isValid()) {
                                var form_data = centralStore_form.getForm().getValues();
                                var params = {
                                    action: 'Insert',
                                    module: 'mypha_centralstore',
                                    op: 'saveCentralStore',
                                    id: '0',
                                    extrainfo: 'asd'
                                };
                                if (br_ID > 0) {
                                    params.action = 'Update';
                                    params.id = br_ID;
                                }
                                APICall(params, Application.MyphaCentralStore.saveCentralStore, form_data);

                            } else {
                                Ext.MessageBox.alert("Notification", "Please enter all required fields");
                            }
                        }
                    }],
                listeners: {
                    afterrender: function () {
                        //Ext.getCmp('br_Company').focus(true);
                        if (_SESSION.UserId == 1) {
//                            Ext.getCmp('branchIsCPD').show();
                        } else {
//                            Ext.getCmp('branchIsCPD').hide();
                        }
                        if (!Ext.isEmpty(br_ID) && br_ID > 0) {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");

                            myMask = new Ext.LoadMask(Ext.getCmp('centralstore_details_window').getEl(), {msg: "Please wait..."});
                            myMask.show();

                            centralStore_form.load({
                                url: modURL + '&op=getDetails',
                                params: {
                                    'id': br_ID,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                },
                                success: function (frm, action) {

                                    eval('var tmp=' + action.response.responseText);
                                    centralStoreLoadedForm = [frm, action.response.responseText];
                                    totalComboCount = 5;


                                    centralstore_details_window.setTitle("Edit Central Store Details : " + Ext.getCmp("br_Name").getValue());
                                    var br_State = Ext.getCmp('br_State');


                                    br_State.getStore().load({
                                        params: {
                                            ind: 1
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });

                                    var br_District = Ext.getCmp('br_District');
                                    br_District.getStore().baseParams.state = br_State.getValue();

                                    br_District.getStore().load({
                                        params: {
                                            ind: 2
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });

                                    Ext.getCmp('br_Company').getStore().load({
                                        params: {
                                            ind: 3

                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        },
                                    });
                                    Ext.getCmp('br_Company').setHideTrigger(true);
                                    Ext.getCmp('br_Company').setReadOnly(true);
//                                    var brIsCpd = Ext.getCmp('branchIsCPD').getValue();
//                                    if (brIsCpd == 'on') {
//                                        Ext.getCmp('br_IsCPD').setValue(1);
//                                    }
                                    var br_Cpd = Ext.getCmp('br_cpd');
                                    br_Cpd.getStore().load({
//                                        params: {
//                                            ind: 4
//                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });
                                    var br_storeGroup = Ext.getCmp('br_storeGroup');
                                    br_storeGroup.getStore().load({
                                        params: {
                                            ind: 5
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });


                                }
                            });
                        }
                    }
                }
            });
        }

        centralstore_details_window.doLayout();
        centralstore_details_window.show(this);
        centralstore_details_window.center();
    };




    var ConfigurationWindow = function (br_ID) {
        /* Store For State Combo */

        var conSettingWindow = Ext.getCmp('AddData');
        if (Ext.isEmpty(conSettingWindow)) {
            var conSettingWindow = new Ext.Window({
                id: 'AddData',
                title: 'Configuration Setting',
                //iconCls: 'additem',
                shadow: false,
                width: 700,
                autoScroll: true,
                height: 500,
                layout: 'border',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                closable: true,
                items: [
                    new Ext.Panel({
                        layout: "column",
                        region: 'center',
                        width: 700,
                        border: false,
                        items: [
                            {
                                xtype: 'container',
                                layout: 'hbox',
                                width: 300,
                                items: districtGrid()
                            }, {
                                xtype: 'container',
                                layout: 'hbox',
                                width: 380,
                                items: detailsGrid()

                            }]
                    })
                ]
            });
        }
        conSettingWindow.show();
        conSettingWindow.doLayout();
        conSettingWindow.center();
    };

    var districtGrid = function (br_ID) {
        var _stateStore = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=loadStateCombo',
            fields: ['st_ID', 'st_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            remoteSort: true
        });
        var dist = br_ID;
        var districtStore = district_Store(dist);
        var chk_model = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    console.log(record);
                    var ind = district_ID.indexOf(record.get('dst_Id'));
                    if (ind > -1)
                        district_ID.splice(ind, 1);
                    record.set('checked', 'false');
                },
                rowselect: function (sm, rowIndex, record) {
                    console.log(record);
                    var ind = district_ID.indexOf(record.get('dst_Id'));
                    if (ind == -1)
                        district_ID.push(record.get('dst_Id'));
                    record.set('checked', 'true');
                }
            }
        });
        var _districtGrid = new Ext.grid.GridPanel({
            store: districtStore,
            region: 'center',
            autoScroll: true,
            id: 'districtGridID',
            height: 470,
            title: 'Districts',
            border: true,
            frame: true,
            loadMask: true,
            selModel: chk_model,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText:
                        '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl:
                        '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            columns: [chk_model, {
                    header: "District",
                    dataIndex: 'dst_Name',
                    id: 'dst_Name',
                    flex: 1,
                    sortable: true
                }],
            sm: chk_model,
            viewConfig: {
                forceFit: true
            },
            stripeRows: true,
            listeners: {
                afterrender: function () {
                    {
                        var me = this;
                        districtStore.on('load', function () {
                            var data = me.getStore().data.items;
                            var recs = [];
                            Ext.each(data, function (item, index) {
                                if (item.data.checked == 1) {
                                    recs.push(index);
                                }
                            });
                            me.getSelectionModel().selectRows(recs);
                        });
                    }
                }
            }, tbar: [{
                    xtype: "combo",
                    emptyText: "State",
                    name: "st_ID",
                    id: "st_ID",
                    anchor: "98%",
                    store: _stateStore,
                    mode: "local",
                    selectOnFocus: true,
                    hiddenName: "st_ID",
                    displayField: "st_name",
                    valueField: "st_ID",
                    typeAhead: true,
                    triggerAction: "all",
                    lazyRender: true,
                    allowBlank: true,
                    editable: true,
                    hideTrigger: false,
                    tabIndex: 16,
                    listeners: {
                        select: function () {

                            Ext.getCmp('districtGridID').getStore().load({
                                params: {
                                    st_ID: Ext.getCmp('st_ID').getValue()
                                }
                            });

                        }
                    }
                },
                {
                    xtype: 'button',
                    tooltip: 'Add New ',
                    text: 'Add',
                    hidden: true,
                    iconCls: 'finascop_add',
                    width: 60,
                    tabIndex: 17,
                    handler: function () {
                        Ext.getCmp('districtGridID').getStore().load({
                            params: {
                                st_ID: Ext.getCmp('st_ID').getValue()
                            }
                        });
                    }
                }],
            fbar: [{
                    text: "Move",
                    cls: 'left-right-buttons',
                    id: 'buttonListMove',
                    iconCls: 'move-to-contacts',
                    tabIndex: 18,
                    handler: function () {
                        var store_fields = Ext.getCmp('districtGridID').getSelectionModel().getSelections();
                        if (store_fields.length > 0) {
                            var dst_Id = new Array();
                            for (var i = 0; i < store_fields.length; i++) {
                                dst_Id[i] = store_fields[i].data.dst_Id;
                            }
                            if (dst_Id.length > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=cenStoreConSettings',
                                    method: 'POST',
                                    params: {
                                        districts: Ext.encode(dst_Id),
                                        state: Ext.getCmp('st_ID').getValue(),
                                        centarlStoreId: Application.MyphaCentralStore.Cache.br_ID
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        Application.example.msg('Success', tmp.message);
                                        Ext.getCmp('detailsGridID').getStore().load({
                                            baseParams: {
                                                centarlStoreId: Application.MyphaCentralStore.Cache.br_ID
                                            }
                                        });
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert('Notification', 'You have to assign resource before sending OTP');
                            }

                        } else {
                            Ext.MessageBox.alert('Notification', 'Please select resources');
                        }

                    }
                }]
        });
        return _districtGrid;
    };
    var district_Store = function (dist) {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listDistrict',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'dst_Id',
                root: 'data'
            }, ['dst_Id', 'dst_Name', 'checked']),
            sortInfo: {
                field: 'dst_Id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            autoLoad: false,
            root: 'data',
            remoteSort: true,
            listeners: {
                beforeload: function () {
                    this.baseParams.centarlStoreId = Application.MyphaCentralStore.Cache.br_ID;
                }

            }
        });
        return store;
    };
    var _detailsStore = function (det) {
        var detstore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMappedDistricts',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'dst_Id',
                root: 'data'
            }, ['mcsc_district', 'dst_Name', 'mcsc_state', 'mcsc_id', 'state']),
            sortInfo: {
                field: 'dst_Id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            autoLoad: false,
            root: 'data',
            remoteSort: true,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.centarlStoreId = Application.MyphaCentralStore.Cache.br_ID;
                }
            }
        });
        return detstore;
    };
    var detailsGrid = function () {
        var detailsStore = _detailsStore();
        var _detailsGrid = new Ext.grid.GridPanel({
            store: detailsStore,
            id: 'detailsGridID',
            layout: 'fit',
            title: 'Mapped Districts',
            frame: true,
            border: true,
            height: 470,
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
            columns: [{
                    header: "State",
                    dataIndex: 'state',
                    sortable: true
                }, {
                    header: "Districts",
                    dataIndex: 'dst_Name',
                    sortable: true
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    items: [{
                            tooltip: 'Remove Item',
                            iconCls: 'finascop_delete',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.Msg.confirm('Notification', 'Do you really want to remove this ?', function (btn) {
                                    if (btn == 'yes') {
                                        removeMappedDistrict(grid, rowIndex, record);
                                    }
                                });
                            }
                        }]
                }

            ],
            listeners: {
                afterrender: function () {
                    detailsStore.load();
                }
            },
            stripeRows: true,
        });
        return _detailsGrid;
    };

    var removeMappedDistrict = function (grid, indx, record)
    {

        Ext.Ajax.request({
            url: modURL + '&op=deleteMappedDistrict',
            method: 'POST',
            params: {
                mcsc_id: record.get('mcsc_id'),
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true)
                {
                    Application.example.msg('Success', 'Removed district from Central Store');
                    Ext.getCmp('detailsGridID').getStore().load({
                        params: {
                            centarlStoreId: Application.MyphaCentralStore.Cache.br_ID
                        }
                    });
                    Ext.getCmp('panelCentralStore').getStore().reload();
//                    function (btn) {
//                        Ext.getCmp('detailsGridID').getStore().load({
//                            params: {
//                                centarlStoreId: Application.MyphaCentralStore.Cache.br_ID
//                            }
//                        });
//                    }

                } else
                {
                    Ext.MessageBox.alert('Failed');
                }
            }
        });

    };


    return {
        Cache: {},
        init: function () {
            var _centralStorePanelId = 'panelCentralStore';
            var _centralStorePanel = Ext.getCmp(_centralStorePanelId);
            if (Ext.isEmpty(_centralStorePanel)) {
                _centralStorePanel = createCentralStoreGrid(_centralStorePanelId);
                Application.UI.addTab(_centralStorePanel);
                _centralStorePanel.doLayout();
            } else {
                Application.UI.addTab(_centralStorePanel);
                _centralStorePanel.doLayout();
            }
        },
        saveCentralStore: function () {
            var centralstore_form = Ext.getCmp('centralstore_form');

            var t = new Date();
            var t_stamp = t.format("YmdHis");

            Ext.MessageBox.show({
                msg: 'Saving your data, please wait...',
                title: 'Wait...',
                progressText: 'Saving...',
                width: 300,
                wait: true
            });

            centralstore_form.getForm().submit({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (centralstore_form, action) {
                    Ext.MessageBox.hide();
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Ext.getCmp('centralstore_details_window').close();
                        Application.example.msg("Success", "Central Store details has been saved successfully. Map default Distributor and Retailor for this Central Store.");
                        Ext.getCmp('panelCentralStore').getStore().reload();
                    }
                },
                failure: function (centralstore_form, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: (obj.error) ? obj.error : obj.errors.reason, buttons: Ext.MessageBox.OK,
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
                    br_ID: Application.MyphaCentralStore.br_ID,
                    br_status: Application.MyphaCentralStore.br_status,
                    comp_id: Application.MyphaCentralStore.comp_id
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {
                        Application.example.msg("Success", "Central Store status has been changed successfully");
                        Ext.getCmp('panelCentralStore').getStore().reload();

//                        function (btn) {
//
//                            Ext.getCmp('panelCentralStore').getStore().reload();
//                        });
                    }
                }
            });
        },
        cenStoreConSettings: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var _testResultsForm = Ext.getCmp('conSettingForm').getForm();
            if (_testResultsForm.isValid()) {
                _testResultsForm.submit({
                    url: modURL + '&op=cenStoreConSettings',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        br_ID: Application.MyphaCentralStore.Cache.br_ID,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp,
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Ext.getCmp('conSettingForm').getForm().reset();
                            Application.example.msg('Success', tmp.message);
                            Ext.getCmp('districtGridID').getStore().load({
                                params: {
                                    br_ID: Application.MyphaCentralStore.Cache.br_ID
                                }
                            });
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
        }

    }
}();
