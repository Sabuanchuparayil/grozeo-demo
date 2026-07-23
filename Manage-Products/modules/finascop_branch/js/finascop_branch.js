/* global Ext */

Application.Finascop_Branch = function () {

    var recs_per_page = 12;
    var modURL = '?module=finascop_branch';
    var modcsURL = '?module=mypha_centralstore';
    var my_marker;
    var isCPD;

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var totalComboCount;
    var myMask, branchLoadedForm;
    var comboLoadComplete = function () {

        if (!Ext.isEmpty(branchLoadedForm) && branchLoadedForm != null)
        {
            branchLoadedForm[0].loadRecord(Ext.decode(branchLoadedForm[1]));
        }
        if (totalComboCount == 0)
        {
            myMask.hide();
            branchLoadedForm = null;
        }
    };
    var tree_panel = function () {

        var tree = new Ext.tree.TreePanel({
            region: 'center',
            useArrows: true,
            autoScroll: true,
            animate: true,
            enableDD: true,
            containerScroll: true,
            border: true,
            overClass: 'x-view-over',
            itemSelector: 'div.thumb-wrap',
            dataUrl: modURL + '&op=getBranchStructure',
            id: 'account_structure_tree',
            mask: true,
            maskDisabled: false,
            maskConfig: {msg: "Loading items..."},
            root: {
                nodeType: 'async',
                text: 'Account Tree',
                draggable: false,
                id: 'root'
            },
            listeners: {
                load: function () {
                    setTimeout(function () {
                        if (WinMask)
                            WinMask.hide();
                    }, 500);
                }
            }
        });
        //Ext.getCmp('ldg_company').setValue(store.getAt('0').get('comp_id'));
        tree.getRootNode().expand();
        return tree;
    };

    var branchCPDStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCPDBranch',
                method: 'post'
            }),
            fields: ['br_ID', 'br_Name', 'br_District', 'br_State', 'br_Address', 'br_Fax',
                'br_Email', 'br_Phone', 'br_Incharge', 'br_status', 'company', 'branch_shortname', 'comp_id', 'br_pincode', 'br_Lat', 'br_Lng', 'branchCPD', 'branchCpd'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function (thisStore, records, options) {
                    (thisStore.getTotalCount() > 0) ? Ext.getCmp('newCPDBtn').disable() : Ext.getCmp('newCPDBtn').enable();
                }
            }
        });
        store.setDefaultSort('br_Name', 'ASC');
        return store;
    };

    var branchCPDGrid = function (id) {
        var branchCPD_store = branchCPDStore();
        var branchCPD_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'branchCpd'
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
        branchCPD_filter.remote = true;
        branchCPD_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branchCPD_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Purchase Office',
            plugins: [branchCPD_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Purchase Office Name',
                    id: 'branchCPD_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'br_Name',
                    tooltip: 'Purchase Office Name'
                }, 
                {
                    header: 'Parent Store',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'branchCpd',
                    tooltip: 'Parent Store'
                },
                {
                    header: 'Purchase Office Short Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'branch_shortname',
                    tooltip: 'Purchase Office Short Name'
                },
                {
                    header: 'Company',
                    sortable: true,
                    dataIndex: 'company',
                    tooltip: 'Company'
                },
                {
                    header: 'District',
                    sortable: true,
                    dataIndex: 'br_District',
                    tooltip: 'District'
                },
                {
                    header: 'State',
                    sortable: true,
                    dataIndex: 'br_State',
                    tooltip: 'State'
                },
                {
                    header: 'Smart Phone',
                    sortable: true,
                    dataIndex: 'br_Fax',
                    tooltip: 'Smart Phone'
                },
                {
                    header: 'Email',
                    sortable: true,
                    dataIndex: 'br_Email',
                    tooltip: 'Email'
                },
                {
                    header: 'Contact No.',
                    sortable: true,
                    dataIndex: 'br_Phone',
                    tooltip: 'Contact No.'
                },
                {
                    header: 'Incharge',
                    sortable: true,
                    dataIndex: 'br_Incharge',
                    tooltip: 'Incharge'
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
                            branchActionMenu.showAt(e.getXY());
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
                 tooltip: 'Edit CPD Details',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var map_Lat = record.get('br_Lat');
                 var map_Long = record.get('br_Lng');
                 var isCPD = true;
                 branchDetails(record.get('br_ID'), map_Lat, map_Long, isCPD);
                 
                 }
                 }]
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
            tbar: [{
                    xtype: 'button',
                    id: 'newCPDBtn',
                    text: 'Create New Purchase Office',
                    iconCls: 'add',
                    handler: function () {
                        var isCPD;

                        branchDetails(0, 8.507007481504532, 76.95167541503906, isCPD = true);
                    }
                }, {
                    xtype: 'button',
                    id: 'branchstructure',
                    text: 'View Branch Structure',
                    tooltip: 'View Branch Structure',
                    iconCls: 'add',
                    handler: function () {
                        branchStructure();
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branchCPD_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branchCPD_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_exp'
        });
        return grid_panel;
    };
    var branchActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {
                    var isCPD = true;
                    var br_ID = Ext.getCmp('branchCPD_main_panel').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_Lat = Ext.getCmp('branchCPD_main_panel').getSelectionModel().getSelections()[0].data.br_Lat;
                    var br_Lng = Ext.getCmp('branchCPD_main_panel').getSelectionModel().getSelections()[0].data.br_Lng;
                    branchDetails(br_ID, br_Lat, br_Lng, isCPD);
                }
            }]
    });
    var proxy = new Ext.data.HttpProxy({
        url: modURL,
        method: 'post'
    });
    var company_store = function () {
        var store = new Ext.data.Store({
            proxy: proxy,
            baseParams: {
                op: 'getCompany'
            },
            reader: new Ext.data.ArrayReader({}, ['comp_id', 'comp_name'])
        });
        return store;
    };

    var branchStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listBranch',
                method: 'post'
            }),
            fields: ['br_ID', 'br_Name', 'br_District', 'br_State', 'br_Address', 'br_Fax', 'br_csdefault', 'br_defRetailor', 'br_storeGroup',
                'br_Email', 'br_Phone', 'br_Incharge', 'br_status', 'company', 'branch_shortname', 'comp_id', 'br_pincode', 'br_Lat', 'br_Lng', 'branchCPD', 'branchCpd'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        store.setDefaultSort('br_Name', 'ASC');
        return store;
    };

    var branchGrid = function (id) {
        var branch_store = branchStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'branchCpd'
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
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Distributors',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Distributor Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'br_Name',
                    tooltip: 'Distributor Name'
                }, {
                    header: 'Parent Store',
                    sortable: true,
                    dataIndex: 'branchCpd',
                    tooltip: 'Parent Store'
                }, //branchCpd
                {
                    header: 'Distributor Short Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'branch_shortname',
                    tooltip: 'Distributor Short Name'
                },
                {
                    header: 'Company',
                    sortable: true,
                    dataIndex: 'company',
                    tooltip: 'Company'
                },
                {
                    header: 'District',
                    sortable: true,
                    dataIndex: 'br_District',
                    tooltip: 'District'
                },
                {
                    header: 'State',
                    sortable: true,
                    dataIndex: 'br_State',
                    tooltip: 'State'
                },
                {
                    header: 'Smart Phone',
                    sortable: true,
                    dataIndex: 'br_Fax',
                    tooltip: 'Smart Phone'
                },
                {
                    header: 'Email',
                    sortable: true,
                    dataIndex: 'br_Email',
                    tooltip: 'Email'
                },
                {
                    header: 'Contact No.',
                    sortable: true,
                    dataIndex: 'br_Phone',
                    tooltip: 'Contact No.'
                },
                {
                    header: 'Incharge',
                    sortable: true,
                    dataIndex: 'br_Incharge',
                    tooltip: 'Incharge'
                }, {
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
                            var br_status = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_status;
                            if (br_status == 'Inactive')
                                distributorStoreActionMenu2.showAt(e.getXY());
                            else
                                distributorStoreActionMenu1.showAt(e.getXY());
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
                 tooltip: 'Edit Distributor Details',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var map_Lat = record.get('br_Lat');
                 var map_Long = record.get('br_Lng');
                 branchDetails(record.get('br_ID'), map_Lat, map_Long);
                 
                 }
                 },
                 {
                 getClass: function (v, meta, rec) {
                 if (rec.get('br_status') == 'Active')
                 {
                 this.items[1].tooltip = 'Deactivate Distributor';
                 return 'now_active';
                 } else
                 {
                 this.items[1].tooltip = 'Activate Distributor';
                 return 'now_inactive';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this branch?', function (btn, text) {
                 if (btn == 'yes')
                 {
                 changeBranchStatus(record.get('br_ID'), record.get('br_status'), record.get('comp_id'));
                 }
                 });
                 }
                 }, {
                 getClass: function (v, meta, rec) {
                 var data = rec.data;
                 var _isDefault = data.br_csdefault;
                 if (_isDefault == 0) {
                 this.items[0].tooltip = 'Set Default';
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
                 url: modcsURL,
                 params: {
                 op: 'setCSDefault',
                 br_ID: record.get('br_ID'),
                 pyramid: 3
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
                    text: 'Create Distributor',
                    tooltip: 'Create Distributor',
                    iconCls: 'add',
                    handler: function () {
                        branchDetails(0, 8.507007481504532, 76.95167541503906, false);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branch_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branch_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_exp'
        });
        return grid_panel;
    };
    var distributorStoreActionMenu1 = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {

                    var br_ID = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_Lat = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_Lat;
                    var br_Lng = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_Lng;
                    branchDetails(br_ID, br_Lat, br_Lng, false);
                }
            }, {
                text: "Deactivate",
                handler: function () {
                    //var record = grid.store.getAt(rowIndex);
                    var br_ID = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_status = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_status;
                    var comp_id = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.comp_id;
                    Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Central Store?', function (btn, text) {
                        if (btn == 'yes')
                        {
                            changeBranchStatus(br_ID, br_status, comp_id);
                        }
                    });
                }
            }]
    });
    var distributorStoreActionMenu2 = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {

                    var br_ID = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_Lat = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_Lat;
                    var br_Lng = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_Lng;
                    branchDetails(br_ID, br_Lat, br_Lng, false);
                }
            }, {
                text: "Activate",
                handler: function () {
                    //var record = grid.store.getAt(rowIndex);
                    var br_ID = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_ID;
                    var br_status = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.br_status;
                    var comp_id = Ext.getCmp('branch_main_panel').getSelectionModel().getSelections()[0].data.comp_id;
                    Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Central Store?', function (btn, text) {
                        if (btn == 'yes')
                        {
                            changeBranchStatus(br_ID, br_status, comp_id);
                        }
                    });
                }
            }]
    });

    var changeBranchStatus = function () {

        Application.Finascop_Branch.br_ID = arguments[0];
        Application.Finascop_Branch.br_status = arguments[1];
        Application.Finascop_Branch.comp_id = arguments[2];
        var form_data = {
            br_ID: Application.Finascop_Branch.br_ID,
            br_status: Application.Finascop_Branch.br_status,
            comp_id: Application.Finascop_Branch.comp_id
        };
        var params = {
            action: 'Update',
            module: 'finascop_branch',
            op: 'changeStatus',
            extrainfo: 'asd',
            id: Application.Finascop_Branch.br_ID

        };

        APICall(params, Application.Finascop_Branch.changeStatus, form_data);
    };
    var centralStoreJsonStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=centralStoreslist',
                method: 'post'
            }),
            fields: ['br_ID', 'br_Name'],
            remoteSort: true,
            autoLoad: true
        });

        store.setDefaultSort('br_Name', 'ASC');
        return store;
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
    
        function initAutocompleteText() {
        var input = document.getElementById('br_pincode');
        var searchBox = new google.maps.places.SearchBox(input);
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            var latitude = place.geometry.location.lat();
            var longitude = place.geometry.location.lng();
            document.getElementById('br_Lat').value = latitude;
            document.getElementById('br_Lng').value = longitude;
        });
    }

    var branchForm = function (map_lat, map_long, isCPD) {
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
                       console.log('point0:' + point);
                        Ext.getCmp('br_Lat').setValue(point.latLng.lat());
                        Ext.getCmp('br_Lng').setValue(point.latLng.lng());
                    },
                    "dragend": function (markerAt) {
                        Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                        Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                    }
                }
            }];
        var centralStore = centralStoreJsonStore();

        var form = new Ext.FormPanel({
            labelAlign: 'top',
            autoHeight: true,
            url: modURL + "&op=saveBranch",
            width: 850,
            frame: true,
            id: 'branch_form',
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
                    items: [
                        {
                            xtype: 'combo',
                            store: brComboStore(3),
                            mode: 'remote',
                            id: 'br_Company',
                            name: 'br_Company',
                            fieldLabel: 'Company',
                            hiddenName: 'br_Company',
                            displayField: 'name',
                            valueField: 'id',
                            typeAhead: true,
                            selectOnFocus: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            allowBlank: false,
                            tabIndex: 1,
//                            listeners: {
//                                        afterrender: function (field) {
//                                            Ext.defer(function () {
//                                                field.focus(true, 100);
//                                            }, 1);
//                                        },
//                                       }
                        },
                        {
                            xtype: 'hidden',
                            allowBlank: true,
                            id: 'br_ID',
                            name: 'br_ID'
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.7,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            fieldLabel: 'Name',
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
                                            fieldLabel: 'Code',
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
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [
                                {
                                    columnWidth: 0.4,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [
                                        {
                                            xtype: 'combo',
                                            store: brComboStore(1),
                                            mode: 'local',
                                            id: 'br_State',
                                            anchor: '98%',
                                            fieldLabel: 'State',
                                            name: 'br_State',
                                            hiddenName: 'br_State',
                                            displayField: 'name',
                                            valueField: 'id',
                                            editable: true,
                                            allowBlank: false,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            typeAhead: true,
                                            minChars: 2,
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
                                },
                                {
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
                                            name: 'br_District',
                                            hiddenName: 'br_District',
                                            displayField: 'name',
                                            valueField: 'id',
                                            allowBlank: false,
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 6
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
                                                    if (!Ext.isEmpty(Ext.getCmp('br_pincode').getValue()))
                                                    {
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
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'br_Phone',
                                            name: 'br_Phone',
                                            allowBlank: false,
                                            vtype: 'phonespec',
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
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'br_Fax',
                                            name: 'br_Fax',
                                            allowBlank: false,
                                            vtype: 'phonespec',
                                            minLength: 10,
                                            maxLength: 10,
                                            tabIndex: 11
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
                                    items: [
                                        {
                                            xtype: 'combo',
                                            displayField: 'ptname',
                                            valueField: 'ptid',
                                            anchor: '98%',
                                            mode: 'local',
                                            allowBlank: isCPD,
                                            hidden: isCPD,
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
                                            tabIndex: 12,
                                            store: new Ext.data.JsonStore({
                                                fields: ['ptid', 'ptname'],
                                                data: [{ptid: 1, ptname: 'Level 1'}, {ptid: 2, ptname: 'Level 2'}, {ptid: 3, ptname: 'Level 3'}]
                                            })
                                        }
                                    ],
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    hidden: false,
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            xtype: 'combo',
                                            mode: 'local',
                                            id: 'br_cpd',
                                            allowBlank: isCPD,
                                            hidden: isCPD,
                                            fieldLabel: 'Central Store',
                                            hiddenName: 'br_cpd',
                                            displayField: 'br_Name',
                                            valueField: 'br_ID',
                                            anchor: '95%',
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            selectOnFocus: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 13,
                                            store: centralStore
                                        }]
                                }
                            ]
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: .50,
                                    layout: 'form',
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
                                            selectOnFocus: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 14
                                        }]
                                }, {
                                    columnWidth: 0.45,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'checkbox',
                                            id: 'br_isFullfillmentbranch',
                                            style: 'margin:0px 0px 0px 0px; padding:0px 0px 0px 0px;',
                                            name: 'br_isFullfillmentbranch',
                                            hidden: (isCPD) ? true : false,
                                            allowBlank: true,
                                            boxLabel: 'Fullfillment Center',
                                            listeners: {
                                                check: function (checkbox, checked) {
                                                    if (checked == true)
                                                    {
                                                        Ext.getCmp('isFullfillmentbranch').setValue('1');
                                                    } else
                                                    {
                                                        Ext.getCmp('isFullfillmentbranch').setValue('0');
                                                    }
                                                }
                                            }
                                        },{
                                            xtype: 'checkbox',
                                            id: 'id_apibranch',
                                            style: 'margin:0px 0px 0px 0px; padding:0px 0px 0px 0px;',
                                            name: 'br_defaultapi',
                                            //hidden: (isCPD) ? true : false,
                                            hidden: true,
                                            allowBlank: true,
                                            boxLabel: 'Default Distributor',
                                            listeners: {
                                                check: function (checkbox, checked) {
                                                    if (checked == true)
                                                    {
                                                        Ext.getCmp('apibranch').setValue('1');
                                                    } else
                                                    {
                                                        Ext.getCmp('apibranch').setValue('0');
                                                    }
                                                }
                                            }
                                        }

                                    ],
                                }]
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 1,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'radiogroup',
                                            anchor: '98%',
                                            mode: 'remote',
                                            hidden: (isCPD) ? true : false,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 15,
                                            items: [{
                                                    boxLabel: 'Spoke',
                                                    id: 'br_DistributorType1',
                                                    name: 'br_StoreType',
                                                    inputValue: 'Owned',
                                                }, {
                                                    boxLabel: 'Franchise',
                                                    id: 'br_DistributorType2',
                                                    name: 'br_StoreType',
                                                    inputValue: 'Leased',
                                                }, {
                                                    boxLabel: 'Store',
                                                    id: 'br_DistributorType3',
                                                    name: 'br_StoreType',
                                                    inputValue: 'Dealer',
                                                }]
                                        }
                                    ],
                                }]
                        },
                        {
                            xtype: 'hidden',
                            allowBlank: true,
                            id: 'apibranch',
                            name: 'br_defaultapibranch',
                            value: 0
                        },{
                            xtype: 'hidden',
                            allowBlank: true,
                            id: 'isFullfillmentbranch',
                            name: 'isFullfillmentbranch',
                            value: 0
                        },
                        {
                            xtype: 'hidden',
                            allowBlank: true,
                            id: 'br_PyramidLevel',
                            name: 'br_PyramidLevel',
                            value: (isCPD) ? 1 : 3
                        },
                        {
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
                            name: 'branchgooglemap',
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
                            repaint: function(zoomlevel){
                            var gmappanel = Ext.getCmp('branchgooglemap');
                            if(zoomlevel){
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
                                                            tabIndex: 16,
                                                            handler: function () {
                                                                Ext.getCmp('branchgooglemap').clearMarkers();
                                                                my_marker = [];
                                                                my_marker.push({
                                                                    geoCodeAddr: Ext.getCmp("br_pincode").getValue(),
                                                                    setCenter: true,
                                                                    marker: {
                                                                        title: "Click and Drag to Move Around",
                                                                        draggable: true
                                                                    },
                                                                    listeners: {
                                                                        failure: function () {

                                                                        },
                                                                        success: function (point) {
                                                                            
                                                                            var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                                            Ext.getCmp('br_Lat').setValue(point.lat);
                                                                            Ext.getCmp('br_Lng').setValue(point.lng);
                                                                            Ext.getCmp('branchgooglemap').repaint(13);
                                                                            console.log('success:' + point.lat + " " + point.lng);
                                                                         },
                                                                        tilesloaded: function (markerAt) {
                                                                            console.log('markerAt0:' + markerAt);
                                                                            Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                                                                            Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                                                                        },
                                                                        afterRender : function (point) {
                                                                            var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                                            Ext.getCmp('br_Lat').setValue(point.lat);
                                                                            Ext.getCmp('br_Lng').setValue(point.lng);
                                                                            Ext.getCmp('branchgooglemap').repaint(13);
                                                                        },
                                                                        dragend: function (markerAt) {
                                                                            console.log('markerAt1:' + markerAt.latLng.lat() + ' ' + markerAt.latLng.lng());
                                                                            Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                                                                            Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                                                                        }
                                                                    },
                                                                    icon: null
                                                                });
                                                                Ext.getCmp('branchgooglemap').addScaleControl();
                                                                Ext.getCmp('branchgooglemap').clearMarkers();
                                                                Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                Ext.defer(function(){
                                                                    var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                                    Ext.getCmp('br_Lat').setValue(point.lat);
                                                                    Ext.getCmp('br_Lng').setValue(point.lng);
                                                                    Ext.getCmp('branchgooglemap').clearMarkers();
                                                                    Ext.getCmp('branchgooglemap').repaint(13);
                                                                    Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                },1200);

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
                                                            tabIndex: 17,
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
                                                            allowBlank: false,
                                                            tabIndex: 18,
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
    var branchDetails = function () {

        var br_ID = arguments[0];
        var map_lat = arguments[1];
        var map_long = arguments[2];
        var isCPD = arguments[3];

        var win_id = "branch_details_window";

        var branch_details_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(branch_details_window))
        {
            var branch_form = branchForm(map_lat, map_long, isCPD);
            branch_details_window = new Ext.Window({
                id: win_id,
                title: 'Create Distributor',
                layout: 'fit',
                height: 582,
                width: 860,
                autoScroll: true,
                plain: true,
                modal: true,
                frame: true,
                resizable: false,
                items: branch_form,
                fbar: [{
                        text: 'Cancel',
                        tabIndex: 19,
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            branch_details_window.close();
                        }
                    }, {
                        text: 'Save',
                        tabIndex: 20,
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            if (branch_form.getForm().isValid())
                            {
                                var form_data = branch_form.getForm().getValues();
                                var params = {
                                    action: 'Insert',
                                    module: 'finascop_branch',
                                    op: 'saveBranch',
                                    id: '0',
                                    extrainfo: 'asd'
                                };
                                if (br_ID > 0)
                                {
                                    params.action = 'Update';
                                    params.id = br_ID;
                                }
                                APICall(params, Application.Finascop_Branch.saveBranch, form_data);
                               
                            } else
                            {
                                Ext.MessageBox.alert("Notification", "Please enter all required fields");
                            }
                        }
                    }],
                listeners: {
                    afterrender: function () {
                        if (_SESSION.UserId == 1)
                        {
//                            Ext.getCmp('branchCPD').show();
                        } else
                        {
//                            Ext.getCmp('branchCPD').hide();
                        }
                        if (!Ext.isEmpty(br_ID) && br_ID > 0)
                        {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");

                            myMask = new Ext.LoadMask(Ext.getCmp('branch_details_window').getEl(), {msg: "Please wait..."});
                            myMask.show();

                            branch_form.load({
                                url: modURL + '&op=getDetails',
                                params: {
                                    'id': br_ID,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                },
                                success: function (frm, action) {

                                    eval('var tmp=' + action.response.responseText);
                                    branchLoadedForm = [frm, action.response.responseText];
                                    totalComboCount = 5;


                                    branch_details_window.setTitle("Edit Distributor Details : " + Ext.getCmp("br_Name").getValue());
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
//                                    var brIsCpd = Ext.getCmp('branchCPD').getValue();
//                                    if (brIsCpd == 'on')
//                                    {
//                                        Ext.getCmp('br_IsCPD').setValue(1);
//                                    }
                                    var br_Cpd = Ext.getCmp('br_cpd');
                                    br_Cpd.getStore().load({
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

                        if (isCPD == false) {
                            Ext.getCmp('br_cpd').show();
                        }
                    }
                }
            });
        }

        branch_details_window.doLayout();
        branch_details_window.show(this);
        branch_details_window.center();
    };

    var branchStructure = function () {

        var win_id = "account_structure";
        var win = Ext.getCmp(win_id);
        if (Ext.isEmpty(win)) {

            win = new Ext.Window({
                id: win_id,
                title: 'Early Structure',
                layout: 'fit',
                width: 700,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                resizable: false,
                items: new Ext.Panel({
                    height: 500,
                    border: false,
                    layout: 'border',
                    items: [tree_panel(),
                        new Ext.Panel({
                            width: 200,
                            border: false,
                            layout: 'form',
                            region: 'east',
                            title: 'Legend',
                            labelAlign: 'top',
                            items: [{
                                    html: '<div class="finascop_color_wrap"><div class="finascop_color-blue"></div><div class="finascop_color_text"> Central Store Ledger</div></div><div class="finascop_color_wrap"><div class="finascop_color-red"></div> <div class="finascop_color_text">Distributor </div></div><div class="finascop_color_wrap"><div class="finascop_color-green"></div> <div class="finascop_color_text">Retailer</div></div>'
                                }, {
                                    xtype: 'combo',
                                    triggerAction: 'all',
                                    allowBlank: false,
                                    id: 'ldg_company',
                                    fieldLabel: 'Company',
                                    hiddenName: 'ldg_company',
                                    forceSelection: true,
                                    hidden: true,
                                    editable: true,
                                    typeAhead: true,
                                    minChars: 1,
                                    displayField: 'comp_name',
                                    valueField: 'comp_id',
                                    store: company_store(),
                                    anchor: '98%',
                                    listeners: {
                                        afterrender: function () {
                                            //this.setValue(comp_id);
                                            //this.setRawValue(comp_name);
                                            this.setValue(_SESSION.finascop_current_company_id);
                                            this.setRawValue(_SESSION.finascop_current_company);
                                            this.setReadOnly(true);
                                            var ldg_company = Ext.getCmp('ldg_company').getValue();
                                            if (ldg_company > 0) {
                                                Ext.getCmp('account_structure_tree').loader.baseParams.company = ldg_company;
                                                Ext.getCmp('account_structure_tree').getRootNode().reload();
                                            }
                                        }}
                                }]
                        })]
                }), buttons: [{
                        text: 'Cancel',
                        tabIndex: 21,
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        handler: function () {
                            win.close();
                        }
                    },
                    {
                        text: 'Refresh',
                        tabIndex: 22,
                        icon: IMAGE_BASE_PATH + "/default/icons/finascop_restart.png",
                        handler: function () {
                            var ldg_company = Ext.getCmp('ldg_company').getValue();
                            if (ldg_company > 0) {
                                Ext.getCmp('account_structure_tree').loader.baseParams.company = ldg_company;
                                Ext.getCmp('account_structure_tree').getRootNode().reload();
                            }
                        }
                    }],
                listeners: {
                    afterrender: function () {
                        WinMask = new Ext.LoadMask(Ext.getCmp('account_structure').getEl());
                        WinMask.show();
                    }
                }
            });
        }

        win.doLayout();
        win.show(this);
        win.center();
    };




    return {
        initBranch: function () {
            isCPD = false;
            var panelId = 'branch_main_panel';
            var _masterPanelBranch = Ext.getCmp(panelId);
            if (Ext.isEmpty(_masterPanelBranch))
            {
                _masterPanelBranch = branchGrid(panelId);
                Application.UI.addTab(_masterPanelBranch);
                _masterPanelBranch.doLayout();
            } else
            {
                Application.UI.addTab(_masterPanelBranch);
                _masterPanelBranch.doLayout();
            }
            return _masterPanelBranch;


        },
        saveBranch: function () {
            var branch_form = Ext.getCmp('branch_form');

            var t = new Date();
            var t_stamp = t.format("YmdHis");

            Ext.MessageBox.show({
                msg: 'Saving your data, please wait...',
                title: 'Wait...',
                progressText: 'Saving...',
                width: 300,
                wait: true
            });

            branch_form.getForm().submit({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (branch_form, action) {
                    Ext.MessageBox.hide();
                    eval('var tmp=' + action.response.responseText);
                    if (tmp.success !== undefined && tmp.success === true)
                    {
                        Ext.getCmp('branch_details_window').close();
                        Application.example.msg("Success", "Details has been saved successfully");
                        if(isCPD){
                            Ext.getCmp('branchCPD_main_panel').getStore().reload();
                        }else{
                           Ext.getCmp('branch_main_panel').getStore().reload();
                        }
                    }
                },
                failure: function (branch_form, action) {
                    if (action.failureType == 'server')
                    {

                        obj = Ext.util.JSON.decode(action.response.responseText);
                        console.log('here1', obj);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: obj.errors.reason,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    } else
                    {
                        console.log('here2');
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
                    br_ID: Application.Finascop_Branch.br_ID,
                    br_status: Application.Finascop_Branch.br_status,
                    comp_id: Application.Finascop_Branch.comp_id
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true)
                    {
                        Application.example.msg("Success", "Distributor status has been changed successfully");
                        Ext.getCmp('branch_main_panel').getStore().reload();
                        
//                        function (btn) {
//
//                            Ext.getCmp('branch_main_panel').getStore().reload();
//                        });
                    }
                }
            });
        },
        initCPDBranch: function () {
            isCPD = true;
            var panelId = 'branchCPD_main_panel';
            var _masterPanelBranch = Ext.getCmp(panelId);
            if (Ext.isEmpty(_masterPanelBranch))
            {
                _masterPanelBranch = branchCPDGrid(panelId);
                Application.UI.addTab(_masterPanelBranch);
                _masterPanelBranch.doLayout();
            } else
            {
                Application.UI.addTab(_masterPanelBranch);
                _masterPanelBranch.doLayout();
            }
            return _masterPanelBranch;
        }
        // BranchMainPanel: function () {


        //     // return [{
        //     //         region: 'center',
        //     //         border: false,
        //     //         layout: 'fit',
        //     //         items: Application.Finascop_Branch.initBranch()
        //     //     }];
        // }
    };
}();











