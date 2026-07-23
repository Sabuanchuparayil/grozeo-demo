Application.RetalinProcurement = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 20;
    var modURL = '?module=retaline_procurement';
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var loadMask = function (ind) {
        return new Ext.LoadMask(Ext.getCmp('main_panelDistributededJobs').getEl(),
                {msg: "Please wait..."});
    };
    var retalineProcurementStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetalineProcurement',
                method: 'post'
            }),
            fields: ['rpd_stitId', 'rpd_quantity', 'stit_SKU'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp('mainProcurementGrid').getView().refresh();
                    Ext.getCmp('mainProcurementGrid').getSelectionModel().selectRow(0);
                },
                beforeload: function () {
                    this.baseParams.rpd_recorddate = Ext.getCmp('rpd_recorddate').getValue();
                    this.baseParams.rpd_branch = Ext.getCmp('comboxbranchnameprjStk').getValue();
                }
            }
        });
        store.setDefaultSort('stit_SKU', 'ASC');
        return store;
    };

    var retalineProcurementGrid = function (id) {
        var rp_store = retalineProcurementStore();
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
            listeners: {
                load: function () {
                    if (_SESSION.IsSuperUser != 1) {
                        Ext.getCmp("comboxbranchnameprjStk").setValue(_SESSION.finascop_current_branch_id);
                        Ext.getCmp("comboxbranchnameprjStk").setHideTrigger(true);
                    }

                }
            }
        });
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }
            ]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: rp_store,
            layout: 'fit',
            region: 'center',
            frame: false,
            border: false,
            title: ' ',
            plugins: [branch_filter],
            id: 'mainProcurementGrid',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item Name',
                    id: 'item_name_auto_exptr',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item Name'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'rpd_quantity',
                    tooltip: 'Quantity',
                    align: 'right'
                }

            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChanged
                }
            }),
            listeners: {
                viewready: updatePagination,
                afterrender: function () {

//                    if (_SESSION.br_PyramidLevel == 1) {
//                        Ext.getCmp("comboxbranchnameprjStk").show();
//                        Ext.getCmp("prjStkBranchDataeditCS").hide();
//                    } else {
//                        Ext.getCmp("comboxbranchnameprjStk").hide();
//                        Ext.getCmp("prjStkBranchDataeditCS").show();
//                        Ext.getCmp("prjStkBranchDataeditCS").setValue(_SESSION.current_branch);
//                    }
                }
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Add Projected Stock',
                    tooltip: 'Add Projected Stock',
                    iconCls: 'add',
                    handler: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        Ext.Ajax.request({
                            url: modURL + '&op=generateUniqueId',
                            params: {
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp

                            },
                            method: 'POST',
                            success: function (res) {
                                var tmp = Ext.decode(res.responseText);
                                console.log("temp is -", tmp);
                                var uid = tmp.uid;
                                Application.RetalinProcurement.Cache.uuid = uid;
                                createRetalineProcurement(0);
                            }
                        });

                    }
                }, {
                    xtype: 'button',
                    text: 'Vendor wise Projected Stock',
                    tooltip: 'Vendor wise Projected Stock',
                    iconCls: 'view',
                    handler: function () {
                        viewRetalineProcurement(0);
                    }
                }, '->', {
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'prjStkBranchDataeditCS',
                    name: 'prjStkBranchDataeditCS',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                }, {
                    xtype: 'combo',
                    id: 'comboxbranchnameprjStk',
                    name: 'comboxbranchnameprjStk',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    emptyText: 'Select Branch',
                    fieldLabel: 'Branch Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchnameprjStk',
                    listeners: {
                        select: function () {
                            Ext.getCmp('mainProcurementGrid').getStore().removeAll();
                            Ext.getCmp('mainProcurementGrid').getStore().baseParams.rpd_branch = Ext.getCmp('comboxbranchnameprjStk').getValue();
                            Ext.getCmp('mainProcurementGrid').getStore().baseParams.rpd_recorddate = Ext.getCmp('rpd_recorddate').getValue();
                            Ext.getCmp('mainProcurementGrid').getStore().load();
                        }
                    }
                }, {
                    html: '&nbsp;&nbsp;',
                }, {
                    xtype: 'datefield',
                    fieldLabel: 'Date',
                    id: 'rpd_recorddate',
                    name: 'rpd_recorddate',
                    anchor: '97%',
                    allowBlank: false,
                    tabIndex: 830,
                    format: "d-m-Y",
                    maxValue: new Date(),
                    value: new Date(),
                    listeners: {
                        select: function () {
                            console.log(Ext.getCmp('rpd_recorddate').getValue());
                            Ext.getCmp('mainProcurementGrid').getStore().baseParams.rpd_branch = Ext.getCmp('comboxbranchnameprjStk').getValue();
                            Ext.getCmp('mainProcurementGrid').getStore().baseParams.rpd_recorddate = Ext.getCmp('rpd_recorddate').getValue();
                            Ext.getCmp('mainProcurementGrid').getStore().load();
                        }
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: rp_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branch_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_exptr'
        });
        return grid_panel;
    };
    var gridSelectionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('mainProcurementGrid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('mainProcurementGrid').getSelectionModel().getSelections()[0].data.rpd_stitId;
            Application.RetalinProcurement.Cache.ItemId = ID;
            Ext.getCmp('gridpanelProcurementDetails').getStore().baseParams.ItemId = ID;
            Ext.getCmp('gridpanelProcurementDetails').getStore().baseParams.rpd_recorddate = Ext.getCmp('rpd_recorddate').getValue();
            Ext.getCmp('gridpanelProcurementDetails').getStore().baseParams.rpd_branch = Ext.getCmp('comboxbranchnameprjStk').getValue();
            Ext.getCmp('gridpanelProcurementDetails').getStore().load();
        } else {
            Ext.getCmp('gridpanelProcurementDetails').getStore().baseParams.ItemId = 0;
            Ext.getCmp('gridpanelProcurementDetails').getStore().baseParams.rpd_recorddate = 0;
            Ext.getCmp('gridpanelProcurementDetails').getStore().baseParams.rpd_branch = 0;
            Ext.getCmp('gridpanelProcurementDetails').getStore().load();
        }
    };
    var rpdvendorStore = function () {
        var vendorStore = new Ext.data.JsonStore({
            fields: ['stpa_id', 'stpa_Fname'],
            url: modURL + '&op=getVendorName',
            autoLoad: true,
            method: 'post',
        });
        return vendorStore;
    };

    var createRetalineProcurementform = function () {
        var basestatiobStore = branchstorepjStk('pjStk');
        var vendorStore = rpdvendorStore();
        var poItemStockformPanel = new Ext.form.FormPanel({
            height: 150,
            frame: true,
            border: false,
            id: 'poItemStockItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.20,
                            items: [{
                                    xtype: 'datefield',
                                    fieldLabel: 'Date',
                                    id: 'rpd_date',
                                    name: 'rpd_date',
                                    anchor: '97%',
                                    allowBlank: false,
                                    tabIndex: 830,
                                    format: "d/m/Y",
                                    maxValue: new Date().add(Date.DAY, 1),
                                    value: new Date()
                                }]
                        }, {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Base Station',
                                    emptyText: 'Base Station',
                                    id: 'rpd_branch',
                                    name: 'rpd_branch',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '97%',
                                    store: basestatiobStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    hiddenName: 'rpd_branch',
                                    tabIndex: 200,
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.50,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Vendor',
                                    emptyText: 'Choose Vendor',
                                    id: 'pe_party',
                                    name: 'pe_party',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '97%',
                                    store: vendorStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'stpa_Fname',
                                    valueField: 'stpa_id',
                                    hiddenName: 'pe_party',
                                    tabIndex: 200,
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1000);
                                        },
                                        select: function () {
                                            var pe_party = Ext.getCmp('pe_party').getValue();
                                            Application.RetalinProcurement.Cache.pe_party = pe_party;
                                            Ext.getCmp('retalineProcurementItemGridPanel').getStore().load({
                                                params: {
                                                    stpa_id: pe_party
                                                }
                                            });
                                        }
                                    }
                                }]
                        }]
                }]
        });
        return poItemStockformPanel;
    };
    var branchstorepjStk = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getBranch',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (thisstore, records, options) {
                    if (_SESSION.br_PyramidLevel > 1) {
                        Ext.getCmp('rpd_branch').setValue(_SESSION.finascop_current_branch_id);
                    }

                }
            }
            //totalProperty: 'totalCount'
        });
        return store;
    };
    var branchstoreDistriJob = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getBranchDistriJob',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (thisstore, records, options) {
                    Ext.getCmp('search_branch' + ind).setValue(_SESSION.finascop_current_branch_id);
                }
            }
            //totalProperty: 'totalCount'
        });
        return store;
    };
    var retalineProcurementItemStore = function () {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listRetalineProcurementItemStore',
            fields: ['stpi_id', 'stit_type', 'itemType', 'itemId', 'itemName', 'stit_brand_name', 'stit_quantity', 'least_package_type_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        stpa_id: Application.RetalinProcurement.Cache.pe_party
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    var retalineProcurementItemStoreView = function () {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listRetalineProcurementItems',
            fields: ['rpd_stitId', 'rpd_quantity', 'stit_SKU', 'br_Name', 'least_package_type_name'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: false,
            listeners: {
                beforeload: function (store, opt) {

                }

            }
        });
        return purchase_invoice_store;
    };
    var createRetalineProcurementItemStockGrid = function () {

        var retalineProcurementItemGridStore = retalineProcurementItemStore();
        var poStockEntryGrid = new Ext.grid.EditorGridPanel({
            store: retalineProcurementItemGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'retalineProcurementItemGridPanel',
            loadMask: true,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'itemName',
                    width: 150,
                    tooltip: 'Item'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'rpd_quantity',
                    xtype: 'numbercolumn',
                    align: 'right',
                    tooltip: 'Quantity',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield',
                        allowNegative: false,
                        allowDecimals: true,
                    }
                }, {
                    header: 'SKU Unit',
                    sortable: true,
                    dataIndex: 'least_package_type_name',
                    tooltip: 'SKU Unit'
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
            afteredit: function (grid_event) {
                updateToItemQuantity(grid_event);
            }
        });
        return poStockEntryGrid;
    };

    function updateToItemQuantity(grid_event, labId) {
        var data = Ext.encode(grid_event.record.data);

    }
    var viewRetalineProcurement = function () {
        var vendorStore = rpdvendorStore();
        var resultWindow = new Ext.Window({
            id: "windowRetalineRetalineProcurement",
            iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 700,
            title: 'Vendor wise Projected Stock',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 150,
                    items: new Ext.form.FormPanel({
                        height: 150,
                        frame: true,
                        border: false,
                        id: 'poItemStockItemFormPanel',
                        layout: 'column',
                        items: [{
                                layout: 'column',
                                columnWidth: 1,
                                items: [{
                                        layout: 'form',
                                        labelAlign: 'top',
                                        columnWidth: 0.20,
                                        items: [{
                                                xtype: 'datefield',
                                                fieldLabel: 'Date',
                                                id: 'rpd_dateview',
                                                name: 'rpd_dateview',
                                                anchor: '97%',
                                                allowBlank: false,
                                                tabIndex: 830,
                                                format: "d/m/Y",
                                                maxValue: new Date(),
                                                value: new Date()
                                            }]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.50,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'combo',
                                                fieldLabel: 'Vendor',
                                                emptyText: 'Choose Vendor',
                                                id: 'pe_partyview',
                                                name: 'pe_partyview',
                                                labelStyle: mandatory_label,
                                                allowBlank: false,
                                                mode: 'local',
                                                typeAhead: true,
                                                forceSelection: true,
                                                editable: true,
                                                anchor: '97%',
                                                store: vendorStore,
                                                triggerAction: 'all',
                                                minChars: 2,
                                                displayField: 'stpa_Fname',
                                                valueField: 'stpa_id',
                                                hiddenName: 'pe_partyview',
                                                tabIndex: 200,
                                                listeners: {
                                                    select: function () {
                                                        var pe_party = Ext.getCmp('pe_partyview').getValue();
                                                        var rpd_dateview = Ext.getCmp('rpd_dateview').getValue();
                                                        Ext.getCmp('retalineProcurementItemGridPanelView').getStore().load({
                                                            params: {
                                                                stpa_id: pe_party,
                                                                rpd_dateview: rpd_dateview
                                                            }
                                                        });
                                                    }
                                                }
                                            }]
                                    }]
                            }]
                    })
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 450,
                    items: viewRetalineProcurementItemStockGrid()
                }],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Cancel',
                    handler: function () {
                        Ext.getCmp('windowRetalineRetalineProcurement').close();
                        Ext.getCmp('mainProcurementGrid').getStore().load();
                    }

                }
            ], listeners: {
                afterrender: function () {

                }
            }

        });

        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var viewRetalineProcurementItemStockGrid = function () {

        var retalineProcurementItemGridStore = retalineProcurementItemStoreView();
        var poStockEntryGrid = new Ext.grid.GridPanel({
            store: retalineProcurementItemGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'retalineProcurementItemGridPanelView',
            loadMask: true,
            columns: [
                {
                    header: 'Base Station',
                    sortable: true,
                    dataIndex: 'br_Name',
                    align: 'left',
                    tooltip: 'Base Station'
                },
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'stit_SKU',
                    width: 150,
                    tooltip: 'Item'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'rpd_quantity',
                    align: 'right',
                    tooltip: 'Quantity',
                }, {
                    header: 'SKU Unit',
                    sortable: true,
                    dataIndex: 'least_package_type_name',
                    align: 'left',
                    tooltip: 'SKU Unit'
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
        return poStockEntryGrid;
    };
    var createRetalineProcurement = function () {
        var resultWindow = new Ext.Window({
            id: "windowRetalineRetalineProcurement",
            iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 700,
            title: 'Add Projected Stock',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 150,
                    items: createRetalineProcurementform()
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 470,
                    items: createRetalineProcurementItemStockGrid()
                }],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Cancel',
                    handler: function () {
                        Ext.getCmp('windowRetalineRetalineProcurement').close();
                        Ext.getCmp('mainProcurementGrid').getStore().load();
                    }

                }, {
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        if (!Ext.isEmpty(Ext.getCmp('pe_party').getValue())) {
                            var vendorItems = getMigratedData('retalineProcurementItemGridPanel');
                            Ext.Ajax.request({
                                url: modURL + '&op=saveRetalineProcurement',
                                method: 'POST',
                                params: {
                                    uuid: Application.RetalinProcurement.Cache.uuid,
                                    rpd_date: Ext.getCmp('rpd_date').getValue(),
                                    pe_party: Ext.getCmp('pe_party').getValue(),
                                    rpd_branch: Ext.getCmp('rpd_branch').getValue(),
                                    vendorItems: vendorItems
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        Application.example.msg('Success', tmp.msg);
                                        Application.RetalinProcurement.Cache.uuid = '';
                                        Ext.getCmp('mainProcurementGrid').getStore().reload();
                                        resultWindow.close();
                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }
            ], listeners: {
                afterrender: function () {

                }
            }

        });

        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var getMigratedData = function (gridid) {
        var j = Ext.pluck(Ext.getCmp(gridid).getStore().getRange(), 'data');
        return Ext.encode(j);
    };

    var retalineProcurementPanel = function (id) {
        var src = '?module=retaline_transfer_order&op=order_details_view&fsto_id=' + Application.RetalinProcurement.Cache.fsto_id;
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Projected Stock',
            id: id,
            items: [retalineProcurementGrid(), new Ext.Panel({
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    title: 'Projected Stock Details',
                    height: winsize.height * 0.6,
                    layout: 'fit',
                    items: [procurDetailsGrid()
                    ],
                    buttonAlign: 'right',
                    fbar: [
                    ]

                })
            ]
        });
        return panel;
    };
    var procurDetailsGrid = function () {

        var addItemStorenew = procDetlsStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            local: true,
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        vendoritem_filter.remote = true;
        vendoritem_filter.autoReload = true;
        var addItem = new Ext.grid.GridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelProcurementDetails',
            columns: [{
                    header: 'Vendor',
                    width: 400,
                    dataIndex: 'stpa_Fname',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Quantity',
                    width: 400,
                    dataIndex: 'rpd_quantity',
                    hideable: true,
                    align: 'right',
                    sortable: true
                }, {
                    header: 'Received Quantity',
                    width: 400,
                    dataIndex: 'receivedQty',
                    hideable: true,
                    align: 'right',
                    sortable: true
                }, {
                    header: 'Created On',
                    width: 400,
                    dataIndex: 'rpd_createdOn',
                    hideable: true,
                    sortable: true
                }],
            tbar: [],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('rpd_status') == 1)
                    {
                        return 'finascop_indicateColCASHMERE';
                    } else {
                        return '';
                    }
                },
            },
            listeners: {
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var procDetlsStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listProcurementDetails',
            fields: ['rpd_stitId', 'rpd_quantity', 'rpd_vendor', 'rpd_createdOn', 'stpa_Fname', 'rpd_status', 'receivedQty'],
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            totalProperty: 'totalCount',
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.ItemId = Application.RetalinProcurement.Cache.ItemId;
                    this.baseParams.rpd_recorddate = Ext.getCmp('rpd_recorddate').getValue();
                    this.baseParams.rpd_branch = Ext.getCmp('comboxbranchnameprjStk').getValue();
                }
            }

        });
        return store;
    };
    var distributedJobsPanel = function (id, ind, title) {
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: title,
            id: id,
            iconCls: 'scheduled',
            buttonAlign: "right",
            items: [distributedJobsGrid(ind),
                new Ext.Panel({
                    title: 'Order Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelDistributededJobsViewDetails',
                    height: winsize.height * 0.6,
                    items: [
                        DistributededJobsDetailsView()
                    ],
                    fbar: [{
                            text: 'View Delivery Challan',
                            iconCls: 'finascop_viewfile',
                            tooltip: 'View Delivery Challan',
                            id: 'DistribtedJpobviewDeliveryChallan',
                            hidden: true,
                            handler: function () {
                                var record = Ext.getCmp("schedule_grid_DistribtedJpob").getSelectionModel().getSelections()[0];
                                var quor_id = record.data.quor_id;
                                Application.RetalinProcurement.viewDeliveryChallan(quor_id);
                            }
                        }, {
                            text: 'View Drive Polls',
                            iconCls: 'list_users',
                            tooltip: 'View Drive Polls',
                            id: 'DistribtedJpobviewPolledHistory',
                            hidden: true,
                            handler: function () {
                                var record = Ext.getCmp("schedule_grid_DistribtedJpob").getSelectionModel().getSelections()[0];
                                var quor_id = record.data.quor_id;
                                Application.RetalinProcurement.viewPolledHistory(quor_id);
                            }
                        }

                    ]
                })],
            listeners: {
                afterrender: function () {

                    if (_SESSION.typId == 3 || _SESSION.typId == 4) {
                        Ext.getCmp('search_branch' + ind).setValue(_SESSION.typdetsid);
                        Ext.getCmp('search_branch' + ind).hide();
                        Ext.getCmp('branch_label' + ind).hide();
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = _SESSION.typdetsid;
                    }

                    if (ind == 2) {
                        Ext.getCmp('save_schedule' + ind).hide();
                    }
                }
            }
        });
        return panel;
    };
    var distributedJobsGrid = function (ind) {
        var chk_model = check_model();
        var qugeo_store = distributedJobsStore(ind);
        var qugeo_select = qugeoSelect();
        var distributedJobsFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'booking_no'
                }, {
                    type: 'string',
                    dataIndex: 'quor_PickupName'
                }, {
                    type: 'string',
                    dataIndex: 'quor_PickupPhone'
                },
                {
                    type: 'string',
                    dataIndex: 'quor_TypeName'
                },
                {
                    type: 'string',
                    dataIndex: 'booked_at'
                },
                {
                    type: 'list',
                    options: ['WAITING FOR DESPATCH', 'DESPATCHED - IN TRANSIT', 'DAMAGED WHILE TRANSIT', 'AWAITING SALES TAX CLEARANCE', 'INCOMPLETE RECEIPT', 'ITEM NOT RECEIVED', 'ITEM PARTLY RECEIVED', 'FAILED-IN TRANSIT',
                        'RECEIVED IN GODOWN', 'SEND FOR DOOR DELIVERY', 'DELIVERY FAILED - DOOR LOCKED', 'DELIVERY FAILED - REFUSED', 'DELIVERY FAILED - ADDRESS NOT FOUND', 'INCOMPLETE DELIVERY', 'DELIVERY FAILED - DAMAGED',
                        'DELIVERY COMPLETED', 'RE-ROUTED', 'RE-BOOKED', 'ARBITRATED', 'PARTIAL DISPATCH', 'PARTIAL RECEIVE', 'PARTIAL DELIVERY', 'PICKUP - AT ORIGIN', 'PICKUP - POLLED', 'PICKUP - POLL REJECTED',
                        'PICKUP - POLL NO RESPONSE', 'PICKUP - HOME BRANCH FLAGGED', 'PICKUP - DIRECT DELIVERY FLAGGED', 'PICKUP - HOME BRANCH PICKED UP', 'PICKUP - DIRECT DELIVERY PICKED UP', 'PICKUP - BOOKING CANCELLED',
                        'PICKED UP - WAITING FOR ASSIGNMENT', 'DELIVERY - POLLED', 'DELIVERY - POLL REJECTED', 'DELIVERY - POLL NO RESPONSE', 'PICKUP FAILED - DOOR LOCKED', 'PICKUP FAILED - ADDRESS NOT FOUND',
                        'PICKUP FAILED - ITEM NOT READY', 'DELIVERY - DELIVERED BUT NOT CONFIRMED', 'DESPATCHED - VIA CROSS BOOKING', 'CANCELLED'],
                    phpMode: true,
                    dataIndex: 'dls_DelStatus'
                }]
        });
        distributedJobsFilter.remote = true;
        distributedJobsFilter.autoReload = true;
        var qugeo_grid = new Ext.grid.GridPanel({
            store: qugeo_store,
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            plugins: [new Ext.ux.grid.GroupSummary(), distributedJobsFilter],
            id: 'schedule_grid_' + ind,
            columns: [chk_model, {
                    header: 'Order No.',
                    sortable: true,
                    dataIndex: 'booking_no',
                    width: 80
                }, {
                    header: 'Created Date',
                    sortable: true,
                    hidden: true,
                    width: 80,
                    dataIndex: 'booked_at',
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-Y');
                        return dateret;
                    }
                }, {
                    header: 'Location',
                    sortable: true,
                    dataIndex: 'quor_DeliveryAddress',
                    hidden: true
                }, {
                    header: 'Consignee',
                    sortable: true,
                    dataIndex: 'destination',
                }, {
                    header: 'Consigner',
                    sortable: true,
                    dataIndex: 'source',
                }, {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'quor_TypeName',
                    width: 80,
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'dls_DelStatus',
                    width: 120
                }, {
                    header: 'STH Opening Time',
                    sortable: true,
                    dataIndex: 'quor_ScheduleOpeningTime',
                    tooltip: 'Scheduled Opening Time',
                    hidden: true
                }, {
                    header: 'No of Box',
                    sortable: true,
                    dataIndex: 'quor_PacketCount',
                }, {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'quor_DeliveryName',
                }, {
                    header: 'Contact',
                    sortable: true,
                    dataIndex: 'quor_DeliveryPhone',
                }, {
                    hidden: true,
                    header: 'Contact Name',
                    sortable: true,
                    dataIndex: 'quor_PickupName'
                }, {
                    header: 'Contact NO',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'quor_PickupPhone'
                }, {
                    header: 'Location',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'destination'
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
                            var quor_id = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections()[0].data.quor_id;
                            disriDelivryActionMenu(e, quor_id, ind)
                        }
                    }
                }
            ],
            sm: chk_model,
            listeners: {
                //resize: updatePagination,
                celldblClick: function (grid, rowIndex, columnIndex, e) {
                }
            },
            tbar: [{html: 'Branch : &nbsp;', id: 'branch_label' + ind}, {
                    xtype: 'combo',
                    mode: 'remote',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreDistriJob(ind),
                    id: 'search_branch' + ind,
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'br_id',
                    name: 'br_id',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('schedule_grid_' + ind).getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    id: 'id_show' + ind,
                    handler: function () {
                        Ext.getCmp('nootitemboxes').setValue(0);
                        showData(ind);
                        Ext.getCmp('DistribtedJpobManualSchedule').show();
                        Ext.getCmp('DistribtedJpobManualDeliver').show();
                        Ext.getCmp('DistribtedJpobHire').show();

                    }
                }, {
                    xtype: 'button',
                    text: 'Show Pending',
                    style: "padding-left: 10px;",
                    id: 'pendingid_show' + ind,
                    handler: function () {
                        Ext.getCmp('nootitemboxes').setValue(0);
                        var branch = Ext.getCmp('search_branch' + ind).getValue();

                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.pending = 1;
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.delivered = 0;
                        Ext.getCmp('schedule_grid_' + ind).getStore().load();

                        Ext.getCmp('DistribtedJpobManualSchedule').show();
                        Ext.getCmp('DistribtedJpobManualDeliver').show();
                        Ext.getCmp('DistribtedJpobHire').show();
                    }
                }, {
                    xtype: 'button',
                    text: 'Show Delivered',
                    style: "padding-left: 10px;",
                    handler: function () {
                        Ext.getCmp('nootitemboxes').setValue(0);
                        var branch = Ext.getCmp('search_branch' + ind).getValue();

                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.delivered = 1;
                        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.pending = 0;
                        Ext.getCmp('schedule_grid_' + ind).getStore().load();

                        Ext.getCmp('DistribtedJpobManualSchedule').hide();
                        Ext.getCmp('DistribtedJpobManualDeliver').hide();
                        Ext.getCmp('DistribtedJpobHire').hide();
                    }
                }], bbar: [{
                    text: "Assign To Drive",
                    id: "DistribtedJpobManualSchedule",
                    iconCls: 'list_users',
                    handler: function () {
                        var selectitem = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var record = [];
                        if (selectedcount > 0) {
                            for (var i = 0; i < selectedcount; i++)
                            {
                                record.push(selectitem[i].data);
                            }
                            Application.RetalinProcurement.pushToDrive(record, 'DistribtedJpob');
                        }

                    }
                }, {
                    text: "Manual Delivery",
                    id: "DistribtedJpobManualDeliver",
                    iconCls: 'list_users',
                    handler: function () {
                        var selectitem = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var record = [];
                        if (selectedcount > 0) {
                            for (var i = 0; i < selectedcount; i++)
                            {
                                record.push(selectitem[i].data);
                            }
                            Application.RetalinProcurement.manualDelivery(record, 'DistribtedJpob');
                        }

                    }
                }, {
                    text: "Dispatch via Vehicle",
                    id: "DistribtedJpobHire",
                    iconCls: 'list_users',
                    handler: function () {
                        var selectitem = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var record = [];
                        if (selectedcount > 0) {
                            for (var i = 0; i < selectedcount; i++)
                            {
                                record.push(selectitem[i].data);
                            }
                            Application.RetalinProcurement.orderDispatch(record);
                        }

                    }
                }, '->', {'html': 'No of Box:&nbsp;'}, {
                    id: 'nootitemboxes',
                    name: 'nootitemboxes',
                    allowBlank: false,
                    width: 50,
                    xtype: 'numberfield',
                    value: 0,
                    fieldlabel: 'No of Items'
                }],
            stripeRows: true
        });
        return qugeo_grid;
    };
    var DistributededJobsDetailsView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '';
        //var src = '?module=cpd_dispatch&op=dispatchdetailsView&quor_id=' + Application.CPD_Dispatch.Cache.quor_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var contactsPanel = new Ext.Panel({
            id: 'details_view_panel_distributedJobs',
            region: 'center',
            width: winsize.width * 0.4,
            items: [{
                    region: 'center',
                    defaults: {
                        frame: false
                    },
                    html: '<iframe id="iframe_order_distrjobdtls" name="iframe_order_distrjobdtls" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>'
                }
            ]
        });
        return contactsPanel;
    };

    var distributedJobsStore = function (ind) {
        var qugeo_store = new Ext.data.JsonStore({
            url: modURL + '&op=listDistributededJobs',
            fields: ['quor_PacketCount', 'booking_no', 'customer', 'source', 'destination', 'quor_PickupName', 'quor_PickupPhone', 'quor_Status', 'quor_Deliverybr_id', 'quor_ItemReturned', 'IsPickup', 'sourcecontact', 'quor_Pickupbr_id', 'printInvoice',
                'drivetype', 'quor_id', 'bk_brk_br_id', 'quor_PickupLat', 'quor_PickupLng', 'quor_DeliveryLat', 'quor_DeliveryLng', 'pickupmapicon', 'deliverymapicon', 'dls_DelStatus', 'quor_TypeName', 'br_Name', 'quor_DeliveryAddress', 'fsto_isInvoiced',
                'quor_ScheduleOpeningTime', 'quor_Type', 'quor_DeliveryName', 'quor_DeliveryPhone', 'quor_TransferOrder_Type', 'orgOrderDate', 'orderMethod', 'quor_DeliveryMethodsAllowed', 'quor_slot_date', 'quor_slot_delivery', 'deliveryLocation',
                {
                    name: 'booked_at',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                }],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function () {
                    Ext.getCmp('schedule_grid_DistribtedJpob').getView().refresh();
                    Ext.getCmp('nootitemboxes').setValue(0);
                    Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().selectRow(0);
                },
                beforeload: function () {
                    this.baseParams.ind = ind;
                    this.baseParams.br_id = Ext.getCmp('search_branch' + ind).getValue();
                }
            }
        });
        return qugeo_store;
    };
    function qugeoSelect() {
        return  new Ext.grid.RowSelectionModel({
            singleSelect: true,
            listeners: {
                selectionchange: gridSelectionChangedDistriJob
            }
        });
    }
    var gridSelectionChangedDistriJob = function () {
        if (!Ext.isEmpty(Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_id;
            var quor_Type = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_Type;
            var quor_Status = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_Status;
            var quor_TransferOrder_Type = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
            var orderMethod = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
            var quor_DeliveryMethodsAllowed = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_DeliveryMethodsAllowed;
            Application.RetalinProcurement.Cache.quor_id = ID;
            Application.RetalinProcurement.ViewMode(ID);
            console.log('Application.RetalinProcurement.Cache.vphbutton', Application.RetalinProcurement.Cache.vphbutton);
            if (Application.RetalinProcurement.Cache.vphbutton == 1) {
                Ext.getCmp('DistribtedJpobviewPolledHistory').show();
            } else {
                Ext.getCmp('DistribtedJpobviewPolledHistory').hide();
            }
        }
    };
    var branchstore = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getBranch',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (thisstore, records, options) {
                    Ext.getCmp('search_branch' + ind).setValue(_SESSION.finascop_current_branch_id);
                    showData(ind);
                }
            }
            //totalProperty: 'totalCount'
        });
        return store;
    };
    var showData = function (ind) {
        var branch = Ext.getCmp('search_branch' + ind).getValue();

        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.br_id = branch;
        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.pending = 0;
        Ext.getCmp('schedule_grid_' + ind).getStore().baseParams.delivered = 0;
        Ext.getCmp('schedule_grid_' + ind).getStore().load();

    };
    var quor_id = new Array();
    var check_model = function (vid) {
        var ApprovedItemQty = 0;
        return new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            dataIndex: 'checked',
            checkOnly: true,
            listeners: {
                selectionchange: function (selModel) {
                    var sel_data = selModel.getSelections();
                    var st = Ext.pluck(sel_data, 'data');
                    if (st.length > 0) {
                        var ID = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_id;
                        var quor_Type = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_Type;
                        var quor_Status = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_Status;
                        var quor_TransferOrder_Type = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
                        var orderMethod = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_TransferOrder_Type;
                        var quor_DeliveryMethodsAllowed = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_DeliveryMethodsAllowed;
                        var quor_Type = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.quor_Type;

                        var fsto_isInvoiced = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.fsto_isInvoiced;
                        var printInvoice = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections()[0].data.printInvoice;
                        Application.RetalinProcurement.Cache.quor_id = ID;
                        Application.RetalinProcurement.ViewMode(ID);
                        console.log('Application.RetalinProcurement.Cache.vphbutton', Application.RetalinProcurement.Cache.vphbutton);
                        var taskStatus = Ext.unique(Ext.pluck(st, 'quor_Status'));
                        var deliveryStatus = ['15', '38'];

                        if (Application.RetalinProcurement.Cache.vphbutton == 1) {
                            Ext.getCmp('DistribtedJpobviewPolledHistory').show();
                        } else {
                            Ext.getCmp('DistribtedJpobviewPolledHistory').hide();
                        }
                    }
                },
                rowdeselect: function (sm, rowIndex, record) {
                    var ind = quor_id.indexOf(record.get('quor_id'));
                    if (ind == -1) {
                        ApprovedItemQty -= parseInt(record.get('quor_PacketCount'));
                        Ext.getCmp('nootitemboxes').setValue(ApprovedItemQty);
                        record.set('checked', 'false');
                    } else {
                        Ext.getCmp('nootitemboxes').setValue(0);
                    }

                },
                rowselect: function (sm, rowIndex, record) {
                    var ind = quor_id.indexOf(record.get('quor_id'));
                    if (ind == -1) {
                        ApprovedItemQty += parseInt(record.get('quor_PacketCount'));
                        Ext.getCmp('nootitemboxes').setValue(ApprovedItemQty);
                        record.set('checked', 'true');
                    } else {
                        Ext.getCmp('nootitemboxes').setValue(0);
                    }

                }
            }
        });
    };
    var vehicleGrid = function (jobrecord, ind) {
        var store = vehicleStore(jobrecord, ind);
        var sm = new Ext.grid.RowSelectionModel({
            singleSelect: true
        });
        var grid = new Ext.grid.GridPanel({
            store: store,
            height: 200,
            viewConfig: {
                forceFit: true
            },
            id: 'vehicle_grid' + ind,
            columns: [
                {
                    header: 'V. Reg. No.',
                    dataIndex: 'v_No'
                }, {
                    header: 'D. Name',
                    dataIndex: 'DriverName'
                }, {
                    header: 'V. Type',
                    dataIndex: 'Vehicletypename'
                }, {
                    header: 'Last Updation',
                    dataIndex: 'LastLocationDtTm',
                    renderer: function (val, meta, rec) {
                        var selected_date = val.toString().match(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/);
                        var show = selected_date[1] + '-' + selected_date[2] + '-' + selected_date[3] + ' ' + selected_date[4] + ':' + selected_date[5] + ':' + selected_date[6];
                        //rec.set('updated_date', show);
                        return show;
                    }
                }],
            sm: sm,
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var vehicle_marker = record.get('vehicle_marker');
                    console.log('record', record);
                    var vehicle_store = grid.getStore();
                    if (vehicle_store.getCount() > 0) {
                        var store = vehicle_store.getRange();
                        /*      var veh_marker = [];*/
                        var a = 1;
                        //Ext.each(store, function (rec) {
                        // console.log('vrec', rec);
                        var mymarker = [];
                        mymarker.push({
                            lat: record.get('Latitude'),
                            lng: record.get('Longitude'),
                            marker: {
                                title: record.get('v_No'),
                                draggable: false
                            },
                            listeners: {
                                click: function () {
                                    var extraInfo = this.extraInfo;
                                    //Ext.getCmp('vehicle_tab' + ind).setActiveTab(1);
                                }
                            },
                            icon: record.get('v_MapIcon'),
                            extraInfo: record.data
                        });
                        record.set('vehicle_marker', a);
                        a++;
                        console.log('vehicle_marker', a);
                        Ext.getCmp('location_map' + ind).clearMarkers();
                        console.log('vehicle_marker1', mymarker);
                        loadMarker(jobrecord, ind, mymarker);
                        console.log('vehicle_marker', mymarker);
                        Application.RetalinProcurement.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true);
                        Ext.getCmp('location_map' + ind).animateMarker(Application.RetalinProcurement.Markers[ind][0], 'BOUNCE', 'START');
                        Ext.getCmp('location_map' + ind).getMap().setZoom(10);

                        // });
                    }
                    setTimeout(function () {
                        Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);
                        Ext.getCmp('vehicle_disp' + ind).setValue('Vehicle No. : <b>' + record.get('v_No') + '</b>');
                        Ext.getCmp('hdnVehicleId' + ind).setValue(record.get('v_ID'));
                    }, 50);
                    setTimeout(function () {
                        Ext.getCmp('location_map' + ind).animateMarker(Application.RetalinProcurement.Markers[ind][0], 'BOUNCE', 'STOP');
                    }, 2000);


                }
            },
            stripeRows: true
        });
        return grid;
    };
    var vehicleStore = function (jobrecord, ind) {
        var store = new Ext.data.JsonStore({
            //proxy: proxy,
            url: modURL + '&op=loadVehicle',
            fields: ['v_ID', 'v_No', 'Latitude', 'Longitude', 'LastLocationDtTm',
                'DriverName', 'Vehicletypename', 'updated_date', 'vehicle_marker',
                'MaxLoad', 'CurrentLoad', 'v_MapIcon'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: false,
            autoLoad: true,
            listeners: {
                beforeload: function () {
                    console.log('jobrecord', jobrecord);
                    console.log('jobrecord521', jobrecord[0]['booking_no']);

                    Application.RetalinProcurement.Markers[ind] = [];

                    Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + jobrecord[0]['booking_no'] + '</b>');
                    Ext.getCmp('hdnVehicleId' + ind).setValue('');
                    Ext.getCmp('vehicle_disp' + ind).setValue('');

                    Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);


                    var action = jobrecord[0]['drivetype'];
//                    if (jobrecord[0]['drivetype'] != 'PICKUP') {
//                        var title = jobrecord[0]['quor_DeliveryName'] + ', ' + jobrecord[0]['quor_DeliveryPhone'];
//                        var vehlatitude = jobrecord[0]['quor_DeliveryLat'];
//                        var vehlongitude = jobrecord[0]['quor_DeliveryLng'];
//                        Ext.getCmp('save_schedule' + ind).setText('To Deliver');
//                    } else {
                    var title = jobrecord[0]['quor_PickupName'] + ', ' + jobrecord[0]['quor_PickupPhone'];
                    var vehlatitude = jobrecord[0]['quor_PickupLat'];
                    var vehlongitude = jobrecord[0]['quor_PickupLng'];
                    Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                    //}
                    Ext.getCmp('location_map' + ind).clearMarkers();

                    if (!Ext.isEmpty(jobrecord[0]['quor_Pickupbr_id'])) {
                        this.baseParams.br_id = jobrecord[0]['quor_Pickupbr_id'];
                        this.baseParams.longitude = vehlongitude;
                        this.baseParams.latitude = vehlatitude;
                        this.baseParams.action = action;
                        this.baseParams.bk_ID = jobrecord[0]['quor_id'];
                    } else
                        return false;


                },
                load: function (vehicle_store, e) {
                    // loadMarker(jobrecord, ind);
                }
            }
        });
        return store;
    };
    var mapPanel = function (id, ind, jobrecord) {
        var centerParam = {
            lat: "8.52414",
            lng: "76.93664"
        };
        return {xtype: 'gmappanel',
            region: 'center',
            zoomLevel: 10,
            minGeoAccuracy: 4,
            gmapType: 'map',
            scaleControl: true,
            id: id,
            anchor: '99%',
            mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
            mapControls: ['GSmallMapControl', 'GMapTypeControl', 'NonExistantControl'],
            setCenter: centerParam,
            /* markers: markers,*/
            listeners: {
                afterrender: function () {
                    setTimeout(function () {
                        Ext.getCmp(id).clearMarkers();
                        if (jobrecord) {
                            console.log('jobrecord', jobrecord);
                            console.log('jobrecord588', jobrecord[0]);

                            Application.RetalinProcurement.Markers[ind] = [];

                            Ext.getCmp('booking_disp' + ind).setValue('Booking No. : <b>' + jobrecord[0]['booking_no'] + '</b>');
                            Ext.getCmp('hdnVehicleId' + ind).setValue('');
                            Ext.getCmp('vehicle_disp' + ind).setValue('');

                            Ext.getCmp('vehicle_tab' + ind).setActiveTab(0);


                            var action = jobrecord[0]['drivetype'];
                            if (jobrecord[0]['drivetype'] != 'PICKUP') {
                                var title = jobrecord[0]['quor_DeliveryName'] + ', ' + jobrecord[0]['quor_DeliveryPhone'];
                                var vehlatitude = jobrecord[0]['quor_DeliveryLat'];
                                var vehlongitude = jobrecord[0]['quor_DeliveryLng'];
                                Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                            } else {
                                var title = jobrecord[0]['quor_PickupName'] + ', ' + jobrecord[0]['quor_PickupPhone'];
                                var vehlatitude = jobrecord[0]['quor_PickupLat'];
                                var vehlongitude = jobrecord[0]['quor_PickupLng'];
                                Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                            }
                            var mymarker = [];

                            //Ext.getCmp('location_map' + ind).clearMarkers();
                            var bounds = {value: null};
                            loadMarker(jobrecord, ind, mymarker);

                            console.log('vehicle_marker', mymarker);
                            Application.RetalinProcurement.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(mymarker, true, bounds);
                            Ext.getCmp('location_map' + ind).animateMarker(Application.RetalinProcurement.Markers[ind][0], 'BOUNCE', 'START');
                            Ext.getCmp('location_map' + ind).getMap().setZoom(10);

                        }



                        if (id == 'max_map') {
                            Ext.getCmp('max_map').addMarkers(Application.RetalinProcurement.Markers[ind], true);
                        }
                    }, 1000);
                }
            }
        };
    };
    var showMap = function (ind) {

        var panel = new Ext.Panel({
            layout: 'border',
            items: mapPanel('max_map', ind, '')
        });

        var map_win = new Ext.Window({
            width: winsize.width * 0.9,
            height: winsize.height * 0.9,
            plain: true,
            modal: true,
            frame: true,
            constrainHeader: true,
            layout: 'fit',
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
    var loadMarker = function (jobrecord, ind, parkmymarker) {
        var loadingMask = loadMask(ind);
        loadingMask.show();


        var action = jobrecord[0]['drivetype'];
        for (var i = 0; i < jobrecord.length; i++) {
            var title = jobrecord[i]['quor_DeliveryName'] + ', ' + jobrecord[i]['quor_DeliveryPhone'];
            parkmymarker.push({
                lat: jobrecord[i]['quor_DeliveryLat'],
                lng: jobrecord[i]['quor_DeliveryLng'],
                marker: {
                    title: title,
                    draggable: false
                },
                icon: jobrecord[i]['deliverymapicon']/*GMAP_LOCATION_ICON*/
            });
        }


        if (jobrecord[0]['drivetype'] == 'PICKUP') {
            title = '';
            title = jobrecord[0]['quor_PickupName'] + ', ' + jobrecord[0]['quor_PickupPhone'];
            Ext.getCmp('save_schedule' + ind).setText('To Pickup');
            parkmymarker.push({
                lat: jobrecord[0]['quor_PickupLat'],
                lng: jobrecord[0]['quor_PickupLng'],
                marker: {
                    title: title,
                    draggable: false
                },
                icon: jobrecord[0]['pickupmapicon']/*GMAP_LOCATION_ICON*/
            });
        } else {
            Ext.getCmp('save_schedule' + ind).setText('To Deliver');
        }
        loadingMask.hide();
        // Application.DeliveryJobs.Markers[ind] = Ext.getCmp('location_map' + ind).addMarkers(parkmymarker, true);
        // Ext.getCmp('location_map' + ind).getMap().setZoom(10);
    };
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                    v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    ;
    var assignSchedule = function (ind) {
        Ext.Msg.show({
            title: 'Saving!',
            msg: '',
            progressText: 'Please Wait...',
            width: 300,
            progress: true,
            closable: false,
            wait: true
        });
        var sel = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections()[0];
        var selectitem = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections();
        var selectedcount = selectitem.length;
        var quor_idarr = [];
        for (var i = 0; i < selectedcount; i++)
        {
            quor_idarr.push(selectitem[i].data.quor_id);
        }

        if (!Ext.isEmpty(sel) && !Ext.isEmpty(Ext.getCmp('hdnVehicleId' + ind).getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=saveQugeo',
                method: 'POST',
                params: {
                    quorIds: Ext.encode(quor_idarr),
                    qugeobk_NO: sel.get('quor_id'), //quor_Deliverybr_id,quor_Pickupbr_id
                    br_id: (sel.get('drivetype') == 'PICKUP' ? sel.get('quor_Pickupbr_id') : sel.get('quor_Deliverybr_id')),
                    hdnVehicleId: Ext.getCmp('hdnVehicleId' + ind).getValue(),
                    type: sel.get('drivetype'),
                    handling_br_id: (sel.get('drivetype') == 'PICKUP' ? sel.get('quor_Pickupbr_id') : sel.get('quor_Deliverybr_id')),
                    uniqueId: uuidv4()
                },
                success: function (res) {

                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success == true) {
                        Ext.Msg.hide();
                        Ext.MessageBox.alert('Notification', tmp.msg, function (btn) {
                            if (btn == 'ok')
                                Ext.getCmp('pushToDriveWindow').close();
                            showData(ind);
                        });

                    } else {
                        Ext.Msg.hide();
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }
                }
            });
        } else {
            Ext.MessageBox.alert('Notification', "Please select a booking and vehicle");
        }
    };
    var manualDeliveryForm = function () {
        var _manualDeliveryFormPanel = new Ext.form.FormPanel({
//            width: winsize.width * 0.4,
//            height: winsize.height * 0.3,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            items: [{
                    layout: 'column',
                    border: false,
                    items: [{
                            layout: 'form',
                            columnWidth: .33,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'datefield',
                                    fieldLabel: 'Date',
                                    id: 'textfieldManualDeliveredDate',
                                    name: 'textfieldManualDeliveredDate',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 10,
                                    format: "d/m/Y",
                                    maxValue: new Date(),
                                    value: new Date(),
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1000);
                                        },
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: .33,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'timefield',
                                    fieldLabel: 'Time',
                                    id: 'textfieldManualDeliveredtime',
                                    name: 'textfieldManualDeliveredtime',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 20,
                                    minValue: '9:00 AM',
                                    maxValue: '6:00 PM',
                                    value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .33,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Delivered By',
                                    id: 'textfieldManualDelivered',
                                    name: 'textfieldManualDelivered',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 30,
                                    maxLength: 150
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: .99,
                            border: false,
                            bodyStyle: {"padding": "10px 5px 10px 3px"},
                            items: [{
                                    xtype: 'textarea',
                                    fieldLabel: 'Remarks',
                                    id: 'manualDeliveryRemarks',
                                    name: 'manualDeliveryRemarks',
                                    anchor: '95%',
                                    allowBlank: false,
                                    tabIndex: 30,
                                    maxLength: 150
                                }]
                        }]
                }]
        });
        return _manualDeliveryFormPanel;
    };
    var AddNewDispatchForm = function () {
        var _dispatchFormPanel = new Ext.form.FormPanel({
            width: 100,
            height: 200,
            frame: true,
            border: false,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'radiogroup',
                                            labelWidth: 100,
                                            fieldLabel: 'Vehicle Type',
                                            id: 'radiobuttonVehicleType',
                                            items: [{
                                                    boxLabel: 'Leased',
                                                    name: 'rb-auto',
                                                    inputValue: 'Leased',
                                                    labelWidth: 100,
                                                    tabIndex: 500,
                                                    listeners: {
                                                        afterrender: function (field) {
                                                            Ext.defer(function () {
                                                                field.focus(true, 100);
                                                            }, 1000);
                                                        }
                                                    }
                                                },
                                                {
                                                    boxLabel: 'Hired',
                                                    name: 'rb-auto',
                                                    inputValue: 'Hired',
                                                    labelWidth: 100,
                                                    tabIndex: 510,
                                                },
                                            ],
                                            listeners: {
                                                change: function (event, checked)
                                                {
                                                    var radioid = Ext.getCmp('radiobuttonVehicleType').getValue();

                                                    switch (radioid) {
                                                        case 'Leased':
                                                            Ext.getCmp('hiredVehicle').hide();
                                                            Ext.getCmp('hiredVehicle').allowBlank = true;
                                                            break;
                                                        case 'Hired':
                                                            Ext.getCmp('hiredVehicle').show();
                                                            Ext.getCmp('hiredVehicle').allowBlank = false;
                                                            break;
                                                    }
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDriver').reset();
                                                    Ext.getCmp('textfieldDispatchDriverContact').reset();
                                                    Ext.getCmp('textfieldDispatchLrgcn').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDispatchdate').reset();
                                                    Ext.getCmp('textfieldcpd_dispatchDispatchtime').reset();
                                                }
                                            }
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'v_No',
                                            valueField: 'v_ID',
                                            mode: 'local',
                                            id: 'hiredVehicle',
                                            forceSelection: true,
                                            fieldLabel: 'Vehicle',
                                            emptyText: 'Select Vehicle',
                                            anchor: '95%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            hidden: true,
                                            tabIndex: 520,
                                            store: hiredVehicleStore(),
                                            listeners: {
                                                select: function (e, b) {
                                                    //var hiredVehicle = Ext.getCmp('hiredVehicle').getRawValue();
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').setValue(Ext.getCmp('hiredVehicle').getRawValue());
                                                    Ext.getCmp('textfieldcpd_dispatchVehicleNo').setReadOnly(true);
                                                }
                                            }
                                        }]
                                }]
                        }]
                },
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Vehicle Number',
                                            id: 'textfieldcpd_dispatchVehicleNo',
                                            name: 'textfieldcpd_dispatchVehicleNo',
                                            anchor: '95%',
                                            allowBlank: false,
                                            tabIndex: 530,
                                            maxValue: 100,
                                            maxLength: 20,
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Driver',
                                            id: 'textfieldcpd_dispatchDriver',
                                            name: 'textfieldcpd_dispatchDriver',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 540,
                                            maxLength: 100
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Contact No',
                                            id: 'textfieldDispatchDriverContact',
                                            name: 'textfieldDispatchDriverContact',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 550,
                                            maxLength: 20
                                        }]
                                }]
                        }]

                }, {
                    layout: 'column',
                    items: [{
                            columnWidth: 1,
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'LR / GCN Number',
                                            id: 'textfieldDispatchLrgcn',
                                            name: 'textfieldDispatchLrgcn',
                                            anchor: '95%',
                                            allowBlank: false,
                                            maxValue: 100,
                                            tabIndex: 560,
                                            maxLength: 20
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'datefield',
                                            fieldLabel: 'Dispatch Date',
                                            id: 'textfieldcpd_dispatchDispatchdate',
                                            name: 'textfieldcpd_dispatchDispatchdate',
                                            anchor: '95%',
                                            allowBlank: false,
                                            tabIndex: 570,
                                            format: "d/m/Y",
                                            maxValue: new Date(),
                                            value: new Date()
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: .35,
                                    items: [{
                                            xtype: 'timefield',
                                            fieldLabel: 'Dispatch Time',
                                            id: 'textfieldcpd_dispatchDispatchtime',
                                            name: 'textfieldcpd_dispatchDispatchtime',
                                            anchor: '97%',
                                            allowBlank: false,
                                            tabIndex: 580,
                                            minValue: '9:00 AM',
                                            maxValue: '6:00 PM',
                                            value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                                        }]
                                }]
                        }]

                }]
        });
        return _dispatchFormPanel;
    };
    var masterPanelforReceiveDistributed = function (id) {
        var _mpanelforReceiveDistributed = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Receive Distributed',
            id: id,
            buttonAlign: "right",
            items: [
                ReceiveDistributedMainGrid(),
                new Ext.Panel({
                    title: 'Receive Distributed Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    cls: 'left_side_panel',
                    id: 'panelReceiveDistributedDataviewReceivedispatchDetails',
                    height: winsize.height * 0.6,
                    layout: 'border',
                    items: [
                        ReceiveDistributedMasterDetailsView()
                    ],
                    fbar: [{
                            text: "Receive Additional",
                            cls: "left-right-buttons",
                            id: "buttonReceiveDistributedAdditionalReceivedispatchDetails",
                            handler: function () {
                                var record = Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").getSelectionModel().getSelections()[0];
                                var quor_TransferOrder_id = record.data.quor_TransferOrder_id;
                                var quor_id = record.data.quor_id;
                                var quor_RefNo = record.data.quor_RefNo;
                                createReceiveAdditionalItem(quor_TransferOrder_id, quor_id, quor_RefNo);
                            }
                        }, {
                            text: "Receive",
                            cls: "left-right-buttons",
                            id: "buttonReceiveDistributedDataeditReceivedispatchDetails",
                            handler: function () {
                                var record = Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").getSelectionModel().getSelections()[0];
                                var quor_TransferOrder_id = record.data.quor_TransferOrder_id;
                                var quor_id = record.data.quor_id;
                                var quor_RefNo = record.data.quor_RefNo;
                                var fstro_receivedOn = record.data.fstro_receivedOn;
                                var fstro_receivedTime = record.data.fstro_receivedTime;
                                NewReceiveDistributedWindow(quor_id, quor_TransferOrder_id, quor_RefNo, fstro_receivedOn, fstro_receivedTime);
                            }
                        }, {
                            text: 'Print',
                            iconCls: 'my-icon141',
                            icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                            handler: function () {
                                downloadReceiveDistributedIframe.focus();
                                downloadReceiveDistributedIframe.print();
                            }
                        }
                    ]
                })
            ],
        });
        return _mpanelforReceiveDistributed;
    };
    var ReceiveDistributedMasterGridStore = function () {

        var _dispatchStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listReceiveDistributedDetails',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['quor_id', 'quor_RefNo', 'quor_Pickupbr_id', 'quor_Type', 'quor_TransferOrder_id', 'quor_Deliverybr_id', 'fstr_requestreceived', 'fstr_requestreceivedStatus', 'quor_Deliverybr_name', 'quor_Pickupbr_name',
                'quor_TypeName', 'fstro_receivedOn', 'fstro_receivedTime']),
            sortInfo: {
                field: 'quor_id',
                direction: "DESC"
            },
            remoteSort: true,
            groupField: '',
            groupDir: '',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelReceiveDistributedDataviewDispatchDetails').getView().refresh();
                    Ext.getCmp('gridpanelReceiveDistributedDataviewDispatchDetails').getSelectionModel().selectRow(0);
                }
            }
        });

        return _dispatchStore;
    };
    var ReceiveDistributedMainGrid = function () {
        var _receivedistributedStore = ReceiveDistributedMasterGridStore();
        var _receivedistributedGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'bcd_driver'
                }, {
                    type: 'string',
                    dataIndex: 'quor_RefNo'
                }, {
                    type: 'string',
                    dataIndex: 'quor_Pickupbr_name'
                }, {
                    type: 'string',
                    dataIndex: 'quor_Deliverybr_name'
                }, {
                    type: 'string',
                    dataIndex: 'quor_TypeName'
                }, {
                    type: 'string',
                    dataIndex: 'fstr_requestreceivedStatus'
                }

            ]
        });
        _receivedistributedGridFilter.remote = true;
        _receivedistributedGridFilter.autoReload = true;
        var __cpddispatchmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            //iconCls: 'money',
            store: _receivedistributedStore,
            id: 'gridpanelReceiveDistributedDataviewDispatchDetails',
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fstr_requestreceivedStatus') == 'Received')
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _receivedistributedGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order No.',
                    dataIndex: 'quor_RefNo',
                    sortable: true,
                    tooltip: 'Order No.',
                    hideable: false, groupable: false

                },
                {
                    header: 'Source',
                    dataIndex: 'quor_Pickupbr_name',
                    sortable: true,
                    tooltip: 'Source',
                    hideable: false, groupable: false
                },
                {
                    header: 'Destination',
                    dataIndex: 'quor_Deliverybr_name',
                    sortable: true,
                    tooltip: 'Destination',
                    hideable: false, groupable: false
                }, {
                    header: 'Type',
                    dataIndex: 'quor_TypeName',
                    sortable: true,
                    tooltip: 'Type',
                    hideable: false, groupable: false

                }, {
                    header: 'Status',
                    dataIndex: 'fstr_requestreceivedStatus',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false, groupable: false

                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _receivedistributedStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionReceiveDistributedDetails
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    Application.RetalinProcurement.ViewReceiveDistributedMode(record.data.quor_id);

                },
                resize: onGridResize,
                afterrender: function () {
                    _receivedistributedStore.load();

                }
            }
        });
        return __cpddispatchmaingridPanel;
    };
    var gridSelectionReceiveDistributedDetails = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").getSelectionModel().getSelections())) {
            var ID = Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.quor_id;
            var quor_TransferOrder_id = Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.quor_TransferOrder_id;
            var fstr_requestreceivedStatus = Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.fstr_requestreceivedStatus;
            var quor_Deliverybr_id = Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.quor_Deliverybr_id;
            var fstr_requestreceivedStatus = Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").getSelectionModel().getSelections()[0].data.fstr_requestreceivedStatus;
            if (fstr_requestreceivedStatus == 'Received') {
                Ext.getCmp('buttonReceiveDistributedDataeditReceivedispatchDetails').hide();
            } else {
                Ext.getCmp('buttonReceiveDistributedDataeditReceivedispatchDetails').show();
            }
            Application.RetalinProcurement.Cache.quor_id = ID;
            Application.RetalinProcurement.Cache.quor_TransferOrder_id = quor_TransferOrder_id;
            Application.RetalinProcurement.ViewReceiveDistributedMode(ID);
            Application.RetalinProcurement.Cache.quor_Deliverybr_id = quor_Deliverybr_id;
        }
    };
    var ReceiveDistributedMasterDetailsView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '';
        //var src = '?module=cpd_dispatch&op=dispatchdetailsView&quor_id=' + Application.CPD_Dispatch.Cache.quor_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        var contactsPanel = new Ext.Panel({
            id: 'panelReceiveDistributedDetailsviewReceivedispatchDetails',
            region: 'center',
            width: winsize.width * 0.4,
            height: winsize.height * 0.4,
            items: [{
                    region: 'center',
                    defaults: {
                        frame: false
                    },
                    html: '<iframe id="downloadReceiveDistributedIframe" name="downloadReceiveDistributedIframe" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>'
                }
            ]
        });
        return contactsPanel;
    };
    var NewReceiveDistributedWindow = function (quor_id, quor_TransferOrder_id, quor_RefNo, fstro_receivedOn, fstro_receivedTime) {
        var _addnewWindow = new Ext.Window({
            id: 'windowReceiveDistributedDataviewOrderDetails',
            title: 'Receive Distributed Details',
            shadow: false,
            height: 650,
            width: 800,
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 200,
                    items: ReceiveDistributedFormNEw(quor_id, quor_TransferOrder_id, quor_RefNo, fstro_receivedOn, fstro_receivedTime)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 450,
                    items: receiveItemStockGridNew(quor_id, quor_TransferOrder_id, quor_RefNo)
                }],
            fbar: [
                {
                    xtype: 'datefield',
                    fieldLabel: 'Received Date',
                    id: 'textfieldreceiveddispatchReceiveddate',
                    name: 'textfieldreceiveddispatchReceiveddate',
                    anchor: '90%',
                    allowBlank: false,
                    tabIndex: 3,
                    value: new Date(),
                    format: "d/m/Y"
                }, {
                    xtype: 'timefield',
                    fieldLabel: 'Received Time',
                    id: 'textfieldreceiveddispatchReceivedtime',
                    name: 'textfieldreceiveddispatchReceivedtime',
                    anchor: '90%',
                    tabIndex: 4,
                    minValue: '9:00 AM',
                    maxValue: '6:00 PM',
                    value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                }/* <?php if (user_access("retaline_procurement", "ReceiveDistributedDetails")) { ?> */, {
                    xtype: 'button',
                    text: 'Save',
                    id: 'stockAddinPoItems',
                    anchor: '90%',
                    iconCls: 'finascop_add',
                    handler: function () {
                        if ((!Ext.isEmpty((Ext.getCmp("textfieldreceiveddispatchReceiveddate").getValue()))))
                        {
                            var receiveItems = getMigratedData('receiveDistributedGridPanel');
                            Ext.Ajax.request({
                                url: modURL + "&op=ReceiveDistributedDetails",
                                method: "POST",
                                params: {
                                    quor_id: quor_id,
                                    quor_TransferOrder_id: quor_TransferOrder_id,
                                    receive_date: Ext.getCmp("textfieldreceiveddispatchReceiveddate").getValue(),
                                    receive_time: Ext.getCmp("textfieldreceiveddispatchReceivedtime").getValue(),
                                    receiveItems: receiveItems
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    console.log("tmp", tmp);
                                    if (tmp.success === true && tmp.valid === true) {

                                        console.log('updateItemStock');
                                        Ext.Ajax.request({
                                            url: modURL,
                                            params: {
                                                op: 'updateItemStock',
                                                quor_id: quor_id,
                                                quor_TransferOrder_id: quor_TransferOrder_id,
                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            },
                                            success: function (response, options) {
                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true) {
                                                    Application.example.msg("Success", tmp.message);
                                                    RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails"));
                                                    receivedispatch_array = [];
                                                    Ext.getCmp("gridpanelReceiveDistributedDataviewDispatchDetails").store.reload({
                                                        params: {
                                                            start: 0,
                                                            limit: RECS_PER_PAGE
                                                        }
                                                    });
                                                    Ext.getCmp('windowReceiveDistributedDataviewOrderDetails').close();
                                                }
                                            }
                                        });

                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true & tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", tmp.message);
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert("Error", tmp.message);
                                }
                            });
                        } else {
                            Ext.MessageBox.alert("Notification", "Please enter required data");
                        }

                    }

                }
                /* <?php } ?> */
            ], listeners: {
                afterrender: function () {
                }
            }

        });
        _addnewWindow.doLayout();
        _addnewWindow.show();
        _addnewWindow.center();
    };
    var ReceiveDistributedFormNEw = function (quor_id, quor_TransferOrder_id, quor_RefNo, fstro_receivedOn, fstro_receivedTime) {
        var _dispatchFormPanel = new Ext.form.FormPanel({
            id: 'formpanelReceiveDistributedDataentryReceiveDetails',
            height: 200,
            frame: true,
            border: false,
            layout: 'column',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.5,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Branch',
                            id: 'currentCPD',
                            name: 'currentCPD',
                            disabled: true,
                            value: _SESSION.current_branch,
                            anchor: '85%'
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Receive Date',
                            id: 'recDate',
                            name: 'recDate',
                            disabled: true,
                            value: fstro_receivedOn,
                            anchor: '85%'
                        }
                    ]

                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.5,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Order No.',
                            id: 'recItem',
                            name: 'recItem',
                            disabled: true,
                            value: quor_RefNo,
                            anchor: '85%'
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Receive Time',
                            id: 'recTime',
                            name: 'recTime',
                            disabled: true,
                            value: fstro_receivedTime,
                            anchor: '85%'
                        }
                    ]

                }
            ]
        });
        return _dispatchFormPanel;
    };
    var receiveItemStockGridNew = function (quor_id, quor_TransferOrder_id, quor_RefNo) {

        var poStockEntryGridStore = listReceiveItemsStore(quor_id, quor_TransferOrder_id, quor_RefNo);
        var poStockEntryGrid = new Ext.grid.EditorGridPanel({
            store: poStockEntryGridStore,
            frame: false,
            border: true,
            height: 450,
            autoScroll: true,
            plugins: [],
            id: 'receiveDistributedGridPanel',
            //iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'stii_itemmastername',
                    tooltip: 'Item'
                }, {
                    header: 'Packed Qty',
                    sortable: true,
                    dataIndex: 'stii_packedQty',
                    align: 'right',
                    tooltip: 'Packed Qty'
                }, {
                    header: 'Received Qty',
                    sortable: true,
                    dataIndex: 'fsto_ItemQtyL3Received',
                    align: 'right',
                    tooltip: 'Received Qty'
                },
                {
                    header: 'To Receive Qty',
                    sortable: true,
                    dataIndex: 'stii_toReceiveqty',
                    xtype: 'numbercolumn',
                    align: 'right',
                    tooltip: 'Received Qty',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3
                    }
                }, {
                    header: 'Receive Date',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'fstro_receivedOn',
                    tooltip: 'Receive Date'
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
        return poStockEntryGrid;
    };
    var listReceiveItemsStore = function (quor_id, quor_TransferOrder_id, quor_RefNo) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listReceiveItemsStore',
            fields: ['stii_itemmastername', 'stii_batch', 'stii_toReceiveqty', 'stii_expirydate', 'stii_id', 'stii_isgift', 'stii_createdon', 'fstro_receivedOn', 'fstro_receivedTime', 'stii_packedQty', 'fstod_id',
                'fsto_ItemId', 'fsto_pkdQty', 'fsto_ItemQty', 'fsto_ItemQtyL3Received'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        quor_RefNo: quor_RefNo,
                        quor_id: quor_id,
                        quor_TransferOrder_id: quor_TransferOrder_id
                    };
                }

            }
        });
        return purchase_invoice_store;
    };
    var hiredVehicleStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['v_ID', 'v_No'],
            url: modURL + '&op=gethiredVehicleStore',
            method: 'post',
            autoLoad: true,
        });
        return store;
    };
    var createReceiveAdditionalItem = function (quor_TransferOrder_id, quor_id, quor_RefNo) {
        var resultWindow = new Ext.Window({
            id: "windowReceiveAdditionalItem",
            iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 700,
            title: 'Receive Additional Item',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 200,
                    items: createReceiveAdditionalItemform(quor_TransferOrder_id, quor_id, quor_RefNo)
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 400,
                    items: createReceiveAdditionalItemItemStockGrid(quor_TransferOrder_id, quor_id, quor_RefNo)
                }],
            buttons: [{
                    xtype: 'datefield',
                    fieldLabel: 'Received Date',
                    id: 'textfieldreceiveddispatchReceiveddate',
                    name: 'textfieldreceiveddispatchReceiveddate',
                    anchor: '90%',
                    allowBlank: false,
                    tabIndex: 3,
                    value: new Date(),
                    format: "d/m/Y"
                }, {
                    xtype: 'timefield',
                    fieldLabel: 'Received Time',
                    id: 'textfieldreceiveddispatchReceivedtime',
                    name: 'textfieldreceiveddispatchReceivedtime',
                    anchor: '90%',
                    tabIndex: 4,
                    minValue: '9:00 AM',
                    maxValue: '6:00 PM',
                    value: new Date().toLocaleTimeString([], {timeStyle: 'short'})
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Cancel',
                    handler: function () {
                        Ext.getCmp('windowReceiveAdditionalItem').close();
                        Ext.getCmp('retalineAdditionalItemReceive_main_panel').getStore().load();
                    }

                }, {
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        var itemStore = Ext.getCmp('retalineAdditionalItemReceiveItemGridPanel').getStore();
                        var rdcai_ReceivedItemQty = itemStore.sum('rdcai_ReceivedItemQty');
                        if (rdcai_ReceivedItemQty > 0) {

                            Ext.Ajax.request({
                                url: modURL + '&op=saveAdditionalItemReceive',
                                method: 'POST',
                                params: {
                                    quor_TransferOrder_id: quor_TransferOrder_id,
                                    quor_id: quor_id,
                                    quor_RefNo: quor_RefNo,
                                    textfieldreceiveddispatchReceiveddate: Ext.getCmp('textfieldreceiveddispatchReceiveddate').getValue(),
                                    textfieldreceiveddispatchReceivedtime: Ext.getCmp('textfieldreceiveddispatchReceivedtime').getValue()
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        Application.example.msg('Success', tmp.msg);
                                        quor_TransferOrder_id = '';
                                        Ext.getCmp('gridpanelReceiveDistributedDataviewDispatchDetails').getStore().reload();
                                        resultWindow.close();
                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", tmp.message);
                                    } else {
                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.message);
                                }
                            });
                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                        }
                    }
                }
            ], listeners: {
                afterrender: function () {

                }
            }

        });

        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var additionalItemStore = function (quor_TransferOrder_id) {
        var store = new Ext.data.JsonStore({
            fields: ['stit_ID', 'stit_itemName', 'stit_SKU', 'packageName', 'id', 'fsbg_id', 'fpod_leastSKUmrp'],
            url: modURL + '&op=getAdditionalItemName',
            autoLoad: true,
            method: 'post',
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.quor_TransferOrder_id = quor_TransferOrder_id
                }
            }
        });
        return store;
    };
    var createReceiveAdditionalItemform = function (quor_TransferOrder_id, quor_id, quor_RefNo) {

        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var itemsStore = additionalItemStore(quor_TransferOrder_id);
        var poItemStockformPanel = new Ext.form.FormPanel({
            height: 200,
            frame: true,
            border: false,
            id: 'poItemStockItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.65,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Items',
                                    id: 'fstr_ItemId',
                                    name: 'fstr_ItemId',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    editable: true,
                                    anchor: '98%',
                                    store: itemsStore,
                                    triggerAction: 'all',
                                    //hideTrigger: true,
                                    minChars: 1,
                                    selectOnFocus: false,
                                    displayField: 'stit_SKU',
                                    valueField: 'stit_ID',
                                    hiddenName: 'fstr_ItemId',
                                    tabIndex: 702,
                                    listeners: {
                                        select: function (cmbo, record, index) {
                                            var fpod_leastSKUmrp = record.data.fpod_leastSKUmrp;
                                            Ext.getCmp('fstr_ItemMRP').setValue(fpod_leastSKUmrp);
                                            var itemId = Ext.getCmp('fstr_ItemId').getValue();
                                            if (itemId > 0) {
                                                Ext.getCmp('fstr_StockItemUnits').getStore().load({
                                                    params: {
                                                        itemId: itemId
                                                    }
                                                });

                                            }

                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'MRP',
                                    hidden: true,
                                    id: 'fstr_ItemMRP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Quantity',
                                    id: 'fstr_RequiredItemQty',
                                    name: 'fstr_RequiredItemQty',
                                    allowBlank: false,
                                    tabIndex: 703,
                                    anchor: '97%',
                                    listeners: {
                                        change: function () {
                                            var fstr_StockItemUnits = Ext.getCmp('fstr_StockItemUnits').getValue();
                                            var itemId = Ext.getCmp('fstr_ItemId').getValue();
                                            var fstr_RequiredItemQty = Ext.getCmp('fstr_RequiredItemQty').getValue();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=getPackingDetails',
                                                params: {fstr_StockItemUnits: fstr_StockItemUnits, itemId: itemId, fstr_RequiredItemQty: fstr_RequiredItemQty},
                                                failure: function (response) {
                                                    Ext.MessageBox.alert('Error', response.responseText);

                                                },
                                                success: function (response, options) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    console.log('tmp', tmp);
                                                    if (tmp.success === true) {
                                                        console.log('here');
                                                        Ext.getCmp('leastSKUDisplayfstr').show();
                                                        Ext.getCmp('fstrleast_package_type_id').setValue(tmp.least_package_type_id);
                                                        Ext.getCmp('fstrleast_package_type_name').setValue(tmp.least_package_type_name);
                                                        Ext.getCmp('leastSKUCountfstr').setValue(tmp.leastSKUqty);

                                                        Ext.getCmp('leastSKUDisplayfstr').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
                                                    } else {
                                                        Ext.MessageBox.alert('Error', response.responseText);

                                                    }
                                                }
                                            });
                                        }
                                    }
                                }
                            ]

                        }, {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Unit',
                                    hidden: true,
                                    id: 'fstr_RequiredItemUnit',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Units',
                                    id: 'fstr_StockItemUnits',
                                    name: 'fstr_StockItemUnits',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    labelAlign: 'top',
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '97%',
                                    store: packTypeStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'package_type_name',
                                    valueField: 'package_type_id',
                                    hiddenName: 'fstr_StockItemUnits',
                                    tabIndex: 704,
                                    listeners: {
                                        select: function () {
                                            var fstr_StockItemUnits = Ext.getCmp('fstr_StockItemUnits').getValue();
                                            var itemId = Ext.getCmp('fstr_ItemId').getValue();
                                            var fstr_RequiredItemQty = Ext.getCmp('fstr_RequiredItemQty').getValue();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=getPackingDetails',
                                                params: {fstr_StockItemUnits: fstr_StockItemUnits, itemId: itemId, fstr_RequiredItemQty: fstr_RequiredItemQty},
                                                failure: function (response) {
                                                    Ext.MessageBox.alert('Error', response.responseText);

                                                },
                                                success: function (response, options) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    console.log('tmp', tmp);
                                                    if (tmp.success === true) {
                                                        console.log('here');
                                                        Ext.getCmp('leastSKUDisplayfstr').show();
                                                        Ext.getCmp('fstrleast_package_type_id').setValue(tmp.least_package_type_id);
                                                        Ext.getCmp('fstrleast_package_type_name').setValue(tmp.least_package_type_name);
                                                        Ext.getCmp('leastSKUCountfstr').setValue(tmp.leastSKUqty);

                                                        Ext.getCmp('leastSKUDisplayfstr').setValue(tmp.leastSKUqty + ' ' + tmp.least_package_type_name);
                                                    } else {
                                                        Ext.MessageBox.alert('Error', response.responseText);

                                                    }
                                                }
                                            });
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            items: [{
                                    xtype: 'button',
                                    text: 'Add',
                                    tabIndex: 705,
                                    anchor: '85%',
                                    iconCls: 'finascop_add',
                                    style: 'margin-top:17px;',
                                    handler: function () {

                                        if (!Ext.isEmpty(Ext.getCmp('fstr_ItemId').getValue()) &&
                                                !Ext.isEmpty(Ext.getCmp('fstr_RequiredItemQty').getValue())) {

                                            Ext.Ajax.request({
                                                url: modURL + '&op=addItemstoAdditionalItemReceive',
                                                method: 'POST',
                                                params: {
                                                    quor_TransferOrder_id: quor_TransferOrder_id,
                                                    quor_id: quor_id,
                                                    quor_RefNo: quor_RefNo,
                                                    fstr_ItemId: Ext.getCmp('fstr_ItemId').getValue(),
                                                    fstr_RequiredItemQty: Ext.getCmp('fstr_RequiredItemQty').getValue(),
                                                    fstr_StockItemUnits: Ext.getCmp('fstr_StockItemUnits').getValue(),
                                                    leastSKUCountfstr: Ext.getCmp('leastSKUCountfstr').getValue(),
                                                    fstrleast_package_type_id: Ext.getCmp('fstrleast_package_type_id').getValue(),
                                                    fstr_ItemMRP: Ext.getCmp('fstr_ItemMRP').getValue()
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success === true && tmp.valid === true) {
                                                        Application.example.msg('Success', tmp.msg);
                                                        Ext.getCmp('retalineAdditionalItemReceiveItemGridPanel').getStore().load({
                                                            params: {
                                                                quor_TransferOrder_id: quor_TransferOrder_id,
                                                                quor_id: quor_id
                                                            }
                                                        });
                                                        Ext.getCmp('fstr_ItemId').reset();
                                                        Ext.getCmp('fstr_RequiredItemQty').reset();
                                                        Ext.getCmp('fstr_StockItemUnits').reset();
                                                        Ext.getCmp('leastSKUCountfstr').reset();
                                                        Ext.getCmp('leastSKUDisplayfstr').reset();
                                                        Ext.getCmp('leastSKUDisplayfstr').hide();
                                                    } else if (tmp.success === true && tmp.valid === false) {
                                                        Ext.Msg.alert("Notification.", tmp.message);
                                                    } else if (tmp.success === true && tmp.img_valid === false) {
                                                        Ext.Msg.alert("Notification.", tmp.message);
                                                    } else {
                                                        Ext.Msg.alert("Error", 'Entered data is not valid.');
                                                    }
                                                },
                                                failure: function (response) {
                                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                                    Ext.MessageBox.alert('Error', tmp.message);
                                                }
                                            });
                                        }
                                        else
                                        {
                                            Ext.MessageBox.alert("Notification", 'Please choose item and quantity');
                                        }
                                    }
                                }]
                        }, {
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.50,
                                    items: [{
                                            xtype: 'label',
                                            html: '&nbsp;'
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.45,
                                    items: [{
                                            xtype: 'displayfield',
                                            fieldLabel: 'Total SKUs',
                                            name: 'leastSKUDisplayfstr',
                                            id: 'leastSKUDisplayfstr',
                                            hidden: true
                                        }, {
                                            xtype: 'hidden',
                                            id: 'fstrleast_package_type_id',
                                            name: 'fstrleast_package_type_id'
                                        },
                                        {
                                            xtype: 'hidden',
                                            id: 'fstrleast_package_type_name',
                                            name: 'fstrleast_package_type_name'
                                        },
                                        {
                                            xtype: 'hidden',
                                            id: 'leastSKUCountfstr',
                                            name: 'leastSKUCountfstr'
                                        }]
                                }]
                        }]
                }]
        });
        return poItemStockformPanel;
    };
    var createReceiveAdditionalItemItemStockGrid = function (quor_TransferOrder_id, quor_id, quor_RefNo) {

        var retalineTransferRequestItemGridStore = retalineAdditionalItemReceiveItemStore(quor_TransferOrder_id, quor_id, quor_RefNo);
        var poStockEntryGrid = new Ext.grid.GridPanel({
            store: retalineTransferRequestItemGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'retalineAdditionalItemReceiveItemGridPanel',
            iconCls: 'finascop_dataentry',
            loadMask: true,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'fstr_ItemName',
                    width: 150,
                    tooltip: 'Item'
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'rdcai_ReceivedItemQty',
                    align: 'right',
                    tooltip: 'Quantity'
                }, {
                    header: 'Unit',
                    sortable: true,
                    dataIndex: 'unitName',
                    tooltip: 'Unit'
                }, {
                    header: 'SKU Qty',
                    sortable: true,
                    dataIndex: 'rdcai_leastSKUCount',
                    align: 'right',
                    tooltip: 'SKU Quantity'
                }, {
                    header: 'SKU Unit',
                    sortable: true,
                    dataIndex: 'leastSkuUnit',
                    tooltip: 'SKU Unit'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Delete Order',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                if (record.get('rdcai_status') == 2) {
                                    deleteItemAdditionalItemReceive(record.get('rdcai_id'), quor_TransferOrder_id, quor_id, quor_RefNo);
                                } else {
                                    Ext.MessageBox.alert("Notification", 'Item Received');
                                }

                            }
                        }
                    ]

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
        return poStockEntryGrid;
    };

    var retalineAdditionalItemReceiveItemStore = function (quor_TransferOrder_id, quor_id, quor_RefNo) {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listAdditionalItemReceiveItemStore',
            fields: ['quor_RefNo', 'rdc_id', 'rdcai_id', 'rdcai_ItemId', {name: 'rdcai_ReceivedItemQty', type: 'float'}, 'rdcai_status', 'fstr_ItemName', 'rdcai_ItemUnits', 'rdcai_leastSKUCount', 'least_package_type_id', 'leastSkuUnit', 'unitName'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (store, opt) {
                    store.baseParams = {
                        quor_TransferOrder_id: quor_TransferOrder_id,
                        quor_id: quor_id,
                        quor_RefNo: quor_RefNo
                    };
                }

            }
        });
        return purchase_invoice_store;
    };


    var deleteItemAdditionalItemReceive = function (id, quor_TransferOrder_id, quor_id, quor_RefNo) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteAdditionalItem',
                    params: {
                        fstrd_id: id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed item');
                            Ext.getCmp('retalineAdditionalItemReceiveItemGridPanel').getStore().load({
                                params: {
                                    quor_TransferOrder_id: quor_TransferOrder_id,
                                    quor_id: quor_id
                                }
                            });
                        }
                    },
                    failure: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        Ext.MessageBox.alert('Error', "Error occurred");
                    }
                });
            }
        });

    };
    var packTypeStore = new Ext.data.JsonStore({
        url: modURL + '&op=getPTStore',
        fields: ['package_type_id', 'package_type_name'],
        totalProperty: 'totalCount',
        root: 'data',
        autoload: true,
        remoteFilter: true,
        listeners: {
            load: function (store, rec, opt) {
                if (store.totalLength > 0) { //totalLength == 1
                    Ext.getCmp('fstr_StockItemUnits').setValue(store.getAt(0).get("package_type_id"));
                }
            }
        }
    });
    var disriDelivryActionMenu = function (e, rdc_status, ind) {
        var stockrequestActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "Print Invoice",
                    id: 'routePrintInvoice',
                    handler: function () {
                        var quor_id = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections()[0].data.quor_id;
                        var fsto_isInvoiced = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections()[0].data.fsto_isInvoiced;
                        var printInvoice = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections()[0].data.printInvoice;
                        if (printInvoice == 1 && fsto_isInvoiced == 1) {
                           // invoiceType(quor_id, ind);
                            Application.RetalinProcurement.printPackingSlip(quor_id,ind);
                        } else {
                            Ext.MessageBox.alert("Notification", "Invoice is not ready.");
                    }

                    }
                }, {
                    text: "Delivery Note",
                    id: 'routeDeliveryInvoice',
                    handler: function () {
                        var quor_id = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections()[0].data.quor_id;
                        Application.RetalinProcurement.printDeliveryNote(quor_id, ind);
                    }
                }]
        });
        stockrequestActionMenu.showAt(e.getXY());
    };


    var createprintDCInvoicePanel = function (quor_id) {
        var src = '?module=retaline_procurement&op=printDCInvoice&quor_id=' + quor_id;
        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 500,
            id: 'printDCInvoicePanel',
            items: [{
                    region: 'center',
                    border: false,
                    html: '<iframe src="' + src +
                            '" id="iframeSlip" name="iframeSlip" ' +
                            'width="100%" height="100%" style="border:none">'
                }]
        });
        return myPanel;
    };
    var createprintDeliveryNotePanel = function (quor_id) {
        var src = '?module=retaline_procurement&op=printDeliveryNote&quor_id=' + quor_id;
        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 500,
            id: 'printprintDeliveryNotePanel',
            items: [{
                    region: 'center',
                    border: false,
                    html: '<iframe src="' + src +
                            '" id="iframeSlip" name="iframeSlip" ' +
                            'width="100%" height="100%" style="border:none">'
                }]
        });
        return myPanel;
    };
    return{
        Cache: {},
        init: function () {
            var panelId = 'retalineProcurement_main_panel';
            var branch_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(branch_panel)) {
                branch_panel = retalineProcurementPanel(panelId);
                Application.UI.addTab(branch_panel);
                branch_panel.doLayout();
            } else {
                Application.UI.addTab(branch_panel);
            }
            return branch_panel;
        }, addDistributededJobs: function () {
            Application.RetalinProcurement.Markers = [];
            Application.RetalinProcurement.Markers['DistribtedJpob'] = [];
            var main_panel_id = 'main_panelDistributededJobs';
            var main_panel = Ext.getCmp(main_panel_id);
            var title = 'Route Delivery';
            if (Ext.isEmpty(main_panel)) {
                main_panel = distributedJobsPanel(main_panel_id, 'DistribtedJpob', title);
                Application.UI.addTab(main_panel);
                main_panel.doLayout();
            } else {
                Application.UI.addTab(main_panel);
            }
        }, ViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var quor_id = arguments[0];
            Ext.getCmp('details_view_panel_distributedJobs').show();
            Ext.getCmp("panelDistributededJobsViewDetails").doLayout();
            console.log('ViewMode');
            Ext.get('iframe_order_distrjobdtls').dom.src = modURL + '&op=order_details_viewDistribtedJpob&quor_id=' + quor_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
        }, pushToDrive: function (jobrecord, ind) {
            var _addNewWindow = new Ext.Window({
                title: 'Push to Drive',
                layout: 'border',
                width: winsize.width * 0.4,
                height: winsize.height * 0.9,
                resizable: false,
                id: 'pushToDriveWindow',
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [new Ext.TabPanel({
                        border: false,
                        activeTab: 0,
                        region: 'north',
                        height: winsize.height * 0.3,
                        deferredRender: false,
                        id: 'vehicle_tab' + ind,
                        items: [{
                                title: 'Live Vehicles',
                                layout: 'fit',
                                items: vehicleGrid(jobrecord, ind)
                            }],
                    }), mapPanel('location_map' + ind, ind, jobrecord)],
                tools: [{
                        id: 'maximize',
                        handler: function () {
                            showMap(ind);
                        }
                    }],
                tbar: ['->', {
                        text: 'Refresh',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var action = jobrecord[0]['drivetype'];
                            if (jobrecord[0]['drivetype'] != 'PICKUP') {
                                var title = jobrecord[0]['destination'];
                                var vehlatitude = jobrecord[0]['quor_DeliveryLat'];
                                var vehlongitude = jobrecord[0]['quor_DeliveryLng'];
                                Ext.getCmp('save_schedule' + ind).setText('To Deliver');
                            } else {
                                var title = jobrecord[0]['source'];
                                var vehlatitude = jobrecord[0]['quor_PickupLat'];
                                var vehlongitude = jobrecord[0]['quor_PickupLng'];
                                Ext.getCmp('save_schedule' + ind).setText('To PickUp');
                            }
                            Ext.getCmp('vehicle_griddj').getStore().load({
                                params: {
                                    br_id: jobrecord[0]['quor_Pickupbr_id'],
                                    longitude: vehlongitude,
                                    latitude: vehlatitude,
                                    action: action,
                                    bk_ID: jobrecord[0]['quor_id'],
                                    radiogroupdriverType_type: Ext.getCmp('radiogroupdriverType_type').getValue()
                                }
                            });
                        }
                    }],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 511,
                        handler: function () {
                            _addNewWindow.close();
                        }
                    }, {
                        text: 'Assign',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        id: 'save_schedule' + ind,
                        handler: function () {
                            assignSchedule(ind);
                        }
                    }], bbar: [{
                        xtype: 'hidden',
                        id: 'hdnVehicleId' + ind
                    }, {
                        xtype: 'displayfield',
                        id: 'booking_disp' + ind
                    }, {
                        xtype: 'displayfield', value: '|', style: 'margin:0 8px 0 8px;'
                    }, {
                        xtype: 'displayfield',
                        id: 'vehicle_disp' + ind
                    }],
                listeners: {
                    afterrender: function () {
                    }
                }
            });
            _addNewWindow.doLayout();
            _addNewWindow.show();
            _addNewWindow.center();
        }, manualDelivery: function (record, ind) {

            var _custPickupForm = manualDeliveryForm();
            var _custPickupWindow = new Ext.Window({
                title: 'Manual Delivery',
                layout: 'fit',
                width: winsize.width * 0.3,
                height: winsize.height * 0.4,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [_custPickupForm],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 40,
                        handler: function () {
                            _custPickupWindow.close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        tabIndex: 50,
                        handler: function () {
                            if (_custPickupForm.getForm().isValid()) {
                                var selectitem = Ext.getCmp('schedule_grid_' + ind).getSelectionModel().getSelections();
                                var selectedcount = selectitem.length;
                                var quor_idarr = [];
                                for (var i = 0; i < selectedcount; i++)
                                {
                                    quor_idarr.push(selectitem[i].data.quor_id);
                                }
                                Ext.Ajax.request({
                                    url: modURL + "&op=saveManualDelivery",
                                    method: "POST",
                                    params: {//textfieldManualDeliveredDate,textfieldManualDeliveredtime,textfieldManualDelivered
                                        delivered_by: Ext.getCmp("textfieldManualDelivered").getValue(),
                                        delivered_date: Ext.getCmp("textfieldManualDeliveredDate").getValue(),
                                        delivered_time: Ext.getCmp("textfieldManualDeliveredtime").getValue(),
                                        qmd_remarks: Ext.getCmp("manualDeliveryRemarks").getValue(),
                                        quor_id: Ext.encode(quor_idarr),
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg("Success", tmp.message);
                                            _custPickupWindow.close();
                                            showData(ind);

                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true & tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert("Error", tmp.message);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
                            }
                        }
                    }]
            });
            _custPickupWindow.doLayout();
            _custPickupWindow.show();
            _custPickupWindow.center();
        }, orderDispatch: function (quor_id, quor_Type, quor_Status) {
            var _dispatchForm = AddNewDispatchForm();
            var _dispatchWindow = new Ext.Window({
                title: 'Dispatch',
                layout: 'fit',
                width: winsize.width * 0.4,
                height: winsize.height * 0.35,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [_dispatchForm],
                listeners: {
                    afterrender: function () {
                    }
                },
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        tabIndex: 590,
                        handler: function () {
                            _dispatchWindow.close();
                        }
                    }, {
                        text: 'Dispatch',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        tabIndex: 600,
                        id: 'assign_pickr',
                        handler: function () {
                            if (_dispatchForm.getForm().isValid()) {
                                var selectitem = Ext.getCmp('schedule_grid_DistribtedJpob').getSelectionModel().getSelections();
                                var selectedcount = selectitem.length;
                                var quor_idarr = [];
                                for (var i = 0; i < selectedcount; i++)
                                {
                                    quor_idarr.push(selectitem[i].data.quor_id);
                                }
                                Ext.Ajax.request({
                                    url: modURL + "&op=dispatchEntry",
                                    method: "POST",
                                    params: {
                                        vehicle_no: Ext.getCmp("textfieldcpd_dispatchVehicleNo").getValue(),
                                        driver: Ext.getCmp("textfieldcpd_dispatchDriver").getValue(),
                                        driverContact: Ext.getCmp("textfieldDispatchDriverContact").getValue(),
                                        driverLrgcn: Ext.getCmp("textfieldDispatchLrgcn").getValue(),
                                        dispatch_date: Ext.getCmp("textfieldcpd_dispatchDispatchdate").getValue(),
                                        dispatch_time: Ext.getCmp("textfieldcpd_dispatchDispatchtime").getValue(),
                                        quor_id: Ext.encode(quor_idarr),
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg("Success", tmp.message);
                                            _dispatchWindow.close();
                                            showData('DistribtedJpob');
                                            _dispatchWindow.close();
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else if (tmp.success === true & tmp.img_valid === false) {
                                            Ext.Msg.alert("Notification.", tmp.message);
                                        } else {
                                            Ext.Msg.alert("Error", tmp.message);
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert("Error", tmp.message);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
                            }


                        }
                    }]


            });
            _dispatchWindow.doLayout();
            _dispatchWindow.show();
            _dispatchWindow.center();
        }, initReceiveDistributed: function () {
            var _receivedistributedPanelId = 'panelMasterReceive_Distributed';
            var _masterPanelReceiveDistributed = Ext.getCmp(_receivedistributedPanelId);
            if (Ext.isEmpty(_masterPanelReceiveDistributed)) {
                _masterPanelReceiveDistributed = masterPanelforReceiveDistributed(_receivedistributedPanelId);
                Application.UI.addTab(_masterPanelReceiveDistributed);
                _masterPanelReceiveDistributed.doLayout();
            } else {
                Application.UI.addTab(_masterPanelReceiveDistributed);
            }
        }, ViewReceiveDistributedMode: function () {
            var quor_id = arguments[0];
            Ext.getCmp("panelReceiveDistributedDetailsviewReceivedispatchDetails").show();
            Ext.getCmp("panelReceiveDistributedDataviewReceivedispatchDetails").doLayout();

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var apikey = _SESSION.apikey;
            var modURL = '?module=retaline_procurement';
            Ext.get('downloadReceiveDistributedIframe').dom.src = modURL + '&op=dispatchdetailsView&quor_id=' + quor_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;
        }, printPackingSlip: function (quor_id, ind) {
            var printDCInvoicePanel = createprintDCInvoicePanel(quor_id);
            var printDCInvoiceWindow = Ext.getCmp('printDCInvoiceWindow');
            if (Ext.isEmpty(printDCInvoiceWindow)) {
                printDCInvoiceWindow = new Ext.Window({
                    id: 'printOrderWindow',
                    plain: true,
                    modal: true,
                    constrain: true,
                    resizable: false,
                    title: 'Delivery Invoice',
                    width: 950,
                    autoHeight: true,
                    items: [printDCInvoicePanel],
                    buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon61',
                            handler: function () {
                                printDCInvoiceWindow.close();
                            }
                        }, {
                            text: 'Print',
                            icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                            iconCls: 'my-icon141',
                            handler: function () {
                                iframeSlip.focus();
                                iframeSlip.print();
                            }
                        }],
                    listeners: {
                        close: function () {
                            if (!Ext.isEmpty(Ext.getCmp('schedule_grid_' + ind))) {
                                Ext.getCmp('schedule_grid_' + ind).getStore().load();
                            }
                        }
                    }
                });
            }
            printDCInvoiceWindow.doLayout();
            printDCInvoiceWindow.show();
            printDCInvoiceWindow.center();
        }, printDeliveryNote: function (quor_id, ind) {
            var printDeliveryNotePanel = createprintDeliveryNotePanel(quor_id);
            var printDeliveryNoteWindow = Ext.getCmp('printDeliveryNoteWindow');
            if (Ext.isEmpty(printDeliveryNoteWindow)) {
                printDeliveryNoteWindow = new Ext.Window({
                    id: 'printOrderWindow',
                    plain: true,
                    modal: true,
                    constrain: true,
                    resizable: false,
                    title: 'Delivery Note',
                    width: 950,
                    autoHeight: true,
                    items: [printDeliveryNotePanel],
                    buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon61',
                            handler: function () {
                                printDeliveryNoteWindow.close();
                            }
                        }, {
                            text: 'Print',
                            icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                            iconCls: 'my-icon141',
                            handler: function () {
                                iframeSlip.focus();
                                iframeSlip.print();
                            }
                        }],
                    listeners: {
                        close: function () {
                            if (!Ext.isEmpty(Ext.getCmp('schedule_grid_' + ind))) {
                                Ext.getCmp('schedule_grid_' + ind).getStore().load();
                            }
                        }
                    }
                });
            }
            printDeliveryNoteWindow.doLayout();
            printDeliveryNoteWindow.show();
            printDeliveryNoteWindow.center();
        }
    };
}();