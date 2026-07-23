Application.RetalineGoodPurchaseNote = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 19;
    var partytype = 0;
    var modURL = '?module=retaline_grn';
    var pomodURL = '?module=finascop_purchase_order';
    var dateRange = parseInt(BETWEEN_DATES_TYPE_A);
    var total_mrp = 0;
    var onGridResize = function (cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    };

    var retalinrGRNPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id,
            title: 'Direct Purchase',
            iconCls: 'finascop_invoice',
            items: [GoodReceiveNoteRetalineGrid()]
        });
        return panel;
    };
    var vendorStore = new Ext.data.JsonStore({
        fields: ['stpa_id', 'stpa_Fname'],
        url: modURL + '&op=getVendorName',
        autoLoad: true,
        method: 'post',
    });
    var receivedToStore = function () {
        var centralStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: pomodURL + '&op=getCentralStores',
            autoLoad: true,
            method: 'post',
            listeners: {
                load: function (thisstore, records, options) {
                    Ext.getCmp('po_billing_to').setValue(_SESSION.finascop_current_branch_id);
                }
            }
        });
        return centralStore;
    };


    var vendorItemStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + '&op=vendorItemStore',
        method: 'post',
        fields: ['stit_ID', 'stit_SKU']
    });
    var packTypeStore = new Ext.data.JsonStore({
        url: pomodURL + '&op=getPTStore',
        fields: ['package_type_id', 'package_type_name'],
        totalProperty: 'totalCount',
        root: 'data',
        autoload: true,
        remoteFilter: true,
        listeners: {
            beforeload: function () {
                this.baseParams.stdpckl11 = '';
                this.baseParams.stdpckl12 = '';
                this.baseParams.stdpckl21 = '';
                this.baseParams.stdpckl31 = '';
                this.baseParams.stdpckl41 = '';
            },
            load: function (store, rec, opt) {
                if (store.totalLength > 0) { //totalLength == 1
                    Ext.getCmp('itemUnitForm').setValue(store.getAt(0).get("package_type_id"));
                }
            }
        }
    });
    var GRNRetalineGridStore = function () {
        var _catStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getGoodReceiveNoteRetaline',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'retgrn_uniqueid',
                root: 'data'
            }, ['retgrn_uniqueid', 'retgrn_name', 'retgrn_createdon', 'vendorName', 'retgrn_status', 'retgrn_billingTo', 'receivedAt']),
            sortInfo: {
                field: 'retgrn_createdon',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelRetalineGRNDataview').getSelectionModel().selectRow(0);
                }
            }
        });

        return _catStore;
    };
    var GoodReceiveNoteRetalineGrid = function () {
        var _grnGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'retgrn_name'
                }, {
                    type: 'string',
                    dataIndex: 'retgrn_createdon'
                }, {
                    type: 'string',
                    dataIndex: 'vendorName'
                }]
        });
        _grnGridFilter.remote = true;
        _grnGridFilter.autoReload = true;
        var _gridStore = GRNRetalineGridStore();

        var _gridPanel = new Ext.grid.GridPanel({
            id: 'gridpanelRetalineGRNDataview',
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            iconCls: 'my-icon444',
            store: _gridStore,
            loadMask: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            tbar: [{
                    text: 'Create Direct Purchase',
                    tooltip: 'Create Direct Purchase',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.RetalineGoodPurchaseNote.newWindowForGRN();
                    }
                }],
            plugins: [new Ext.ux.grid.GroupSummary(), _grnGridFilter],
            columns: [
                {
                    header: 'PR Number',
                    sortable: true,
                    dataIndex: 'retgrn_name',
                    tooltip: 'PR Number',
                    hideable: false
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'retgrn_createdon',
                    tooltip: 'Date'
                }, {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'vendorName',
                    tooltip: 'Vendor',
                    hideable: false
                }, {
                    header: 'Received At',
                    sortable: true,
                    dataIndex: 'receivedAt',
                    tooltip: 'Received At',
                    hideable: false
                },
                {
                    xtype: 'actioncolumn',
                    //header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            grnRetalineActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedPrereqisite
                }
            }),
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true
        });
        return _gridPanel;
    };
    var grnRetalineActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit / View",
                handler: function () {
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    var retgrn_uniqueid = Ext.getCmp('gridpanelRetalineGRNDataview').getSelectionModel().getSelections()[0].data.retgrn_uniqueid;
                    var retgrn_status = Ext.getCmp('gridpanelRetalineGRNDataview').getSelectionModel().getSelections()[0].data.retgrn_status;
                    if (retgrn_status == 0) {
                        Application.RetalineGoodPurchaseNote.editPOWindowForVendor(retgrn_uniqueid);
                    } else {
                        Application.RetalineGoodPurchaseNote.viewGRNDetails(retgrn_uniqueid);
                    }

                }
            }, {
                text: "Print",
                handler: function () {
                    var retgrn_uniqueid = Ext.getCmp('gridpanelRetalineGRNDataview').getSelectionModel().getSelections()[0].data.retgrn_uniqueid;
                    Application.RetalineGoodPurchaseNote.printPO(retgrn_uniqueid);
                }
            }, {
                text: 'Delete',
                handler: function () {
                    var retgrn_uniqueid = Ext.getCmp('gridpanelRetalineGRNDataview').getSelectionModel().getSelections()[0].data.retgrn_uniqueid;
                    var retgrn_status = Ext.getCmp('gridpanelRetalineGRNDataview').getSelectionModel().getSelections()[0].data.retgrn_status;
                    if (retgrn_status == 0) {
                        Application.RetalineGoodPurchaseNote.deleteGRNRetaline(retgrn_uniqueid);
                    } else {
                        Ext.MessageBox.alert('Notification', 'Sorry, You are not allowed to delete this GRN.');
                    }

                }
            }]
    });

    var gridSelectionChangedPrereqisite = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelRetalineGRNDataview').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelRetalineGRNDataview').getSelectionModel().getSelections()[0].data.fpo_id;
        }
    };
    var grnRetalineCreateForm = function () {
        var purUni = '';
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'newPOFormForVendor',
            layout: 'column',
            bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 5px"},
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 3px 5px 3px"},
                    items: [{
                            xtype: 'hidden', id: 'uniqueId', name: 'uniqueId'
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .17,
                            labelAlign: 'top',
                            labelWidth: 70,
                            items: [
                                {
                                    xtype: 'datefield',
                                    fieldLabel: 'Date',
                                    id: 'po_date',
                                    name: 'po_date',
                                    anchor: '97%',
                                    allowBlank: false,
                                    tabIndex: 830,
                                    format: "d/m/Y",
                                    maxValue: new Date(),
                                    value: new Date(),
                                    editable: false
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: 0.30,
                            labelAlign: 'top',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Received At',
                                    emptyText: 'Received At',
                                    id: 'po_billing_to',
                                    name: 'po_billing_to',
                                    labelStyle: mandatory_label,
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '95%',
                                    store: receivedToStore(),
                                    allowBlank: false,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    hiddenName: 'po_billing_to',
                                    tabIndex: 832
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.17,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Vendor',
                                    emptyText: 'Choose Vendor',
                                    id: 'pe_party',
                                    name: 'pe_party',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'remote',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '95%',
                                    store: vendorStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'stpa_Fname',
                                    valueField: 'stpa_id',
                                    hiddenName: 'pe_party',
                                    tabIndex: 831,
                                    listeners: {
                                        select: function () {
                                            var pe_party = Ext.getCmp('pe_party').getValue();
                                            Ext.getCmp('pe_partyItems').getStore().clearData();
                                            Ext.getCmp('pe_partyItems').reset();
                                            Ext.getCmp('pe_partyItems').getStore().load({
                                                params: {
                                                    stpa_id: pe_party
                                                }
                                            });
                                            Ext.Ajax.request({
                                                url: pomodURL + '&op=vendorDetails',
                                                method: 'POST',
                                                params: {pe_party: pe_party},
                                                success: function (res) {
                                                    var tmp = Ext.decode(res.responseText);
                                                    Ext.getCmp('vendorDetails').show();

                                                    Ext.getCmp('vendorName').setValue(tmp.stpa_Fname);
                                                    Ext.getCmp('vendorMobile').setValue(tmp.stpa_MobileNo);
                                                    Ext.getCmp('vendorContact').setValue(tmp.stpa_ContactPerson);
                                                    Ext.getCmp('vendorEmail').setValue(tmp.stpa_Email);

                                                    Ext.getCmp('vendorCity').setValue(tmp.stpa_City);
                                                    //Ext.getCmp('vendorPinc').setValue(tmp.stpa_PINCODE);

                                                    //Ext.getCmp('vendorGST').setValue(tmp.stpa_GSTIN);
                                                    //Ext.getCmp('vendorVisfreq').setValue(tmp.stpa_visitFrequency);
                                                    //Ext.getCmp('vendorOncall').setValue(tmp.stpa_oncallDelivery);

                                                },
                                                failure: function () {
                                                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                }
                                            });

                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .17,
                            labelAlign: 'top',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'PR Number',
                                    id: 'po_number',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_number]',
                                    anchor: '95%',
                                    value: 'Not Genereated',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .18,
                            labelAlign: 'top',
                            labelWidth: 70,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Collected By',
                                    id: 'po_ordered_by',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_ordered_by]',
                                    anchor: '95%',
                                    value: _SESSION.FirstName,
                                }
                            ]
                        }]
                }, {
                    xtype: 'fieldset',
                    title: 'Vendor Details',
                    collapsible: true,
                    columnWidth: 1,
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    id: 'vendorDetails',
                    hidden: true,
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;,margin-left: 15px;',
                            columnWidth: .25,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Name',
                                    id: 'vendorName',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'City',
                                    id: 'vendorCity',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Mobile',
                                    id: 'vendorMobile',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Contact Person',
                                    id: 'vendorContact',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Email',
                                    id: 'vendorEmail',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            hidden: true,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Visit Frequency',
                                    id: 'vendorVisfreq',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 35,
                            hidden: true,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'On Call Delivery',
                                    id: 'vendorOncall',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }]
                }, {
                    xtype: 'fieldset',
                    layout: 'column',
                    collapsible: true,
                    title: 'Item Details',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'itemHistory',
                    hidden: true,
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;,margin-left: 15px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Unit',
                                    id: 'itemUnitFormdet',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;,margin-left: 15px;',
                            columnWidth: .15,
                            labelAlign: 'top',
                            labelWidth: 60,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Vendor Projected',
                                    id: 'projectedStock',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            labelAlign: 'top',
                            labelWidth: 80,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Total Projected',
                                    id: 'totalProjecteStock',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .25,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Last Purchase Rate',
                                    id: 'itemLPP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .30,
                            labelAlign: 'top',
                            labelWidth: 80,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Least Purchase Rate',
                                    id: 'itemLP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, ]
                }, {
                    layout: 'column',
                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 3px"},
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'column',
                            style: 'margin-bottom:3px;',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.4,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Items',
                                            id: 'pe_partyItems',
                                            name: 'pe_partyItems',
                                            labelStyle: mandatory_label,
                                            allowBlank: true,
                                            labelAlign: 'top',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: vendorItemStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'stit_SKU',
                                            valueField: 'stit_ID',
                                            hiddenName: 'pe_partyItems',
                                            tabIndex: 833, listeners: {
                                                select: function () {
                                                    var pe_partyItems = Ext.getCmp('pe_partyItems').getValue();
                                                    var po_billing_to = Ext.getCmp('po_billing_to').getValue();
                                                    var pe_party = Ext.getCmp('pe_party').getValue();
                                                    var dc_date = Ext.getCmp('po_date').getValue();
                                                    if (po_billing_to > 0 && pe_party > 0) {
                                                        Ext.Ajax.request({
                                                            url: modURL + '&op=itemHistory',
                                                            method: 'POST',
                                                            params: {pe_partyItems: pe_partyItems, po_billing_to: po_billing_to, dc_date: dc_date, pe_party: pe_party},
                                                            success: function (res) {
                                                                var tmp = Ext.decode(res.responseText);

                                                                if (tmp.csb_package_type_name != '') {
                                                                    purUni = ' - ' + tmp.csb_package_type_name;
                                                                } else {
                                                                    purUni = '';
                                                                }
                                                                Ext.getCmp('itemHistory').show();
                                                                Ext.getCmp('projectedStock').setValue(tmp.projectedStock);
                                                                Ext.getCmp('totalProjecteStock').setValue(tmp.totalProjecteStock);
                                                                //Ext.getCmp('itemHSN').setValue(tmp.stit_HSN_code);
                                                                Ext.getCmp('itemLP').setValue(tmp.last_mrp);
                                                                //Ext.getCmp('itemGST').setValue(tmp.stit_GST);
                                                                Ext.getCmp('itemLPP').setValue(tmp.last_prchaserate);
                                                                //Ext.getCmp('itemOptimumQty').setValue(tmp.optiQty);
                                                                //Ext.getCmp('itemCSCount').setValue(tmp.itemCSCount);
                                                                //Ext.getCmp('itemMinQty').setValue(tmp.minQty);
                                                                //Ext.getCmp('itemUnit').setValue(tmp.csb_package_type_name);
                                                                Ext.getCmp('itemUnitForm').setValue(tmp.itemUnitForm);
                                                                Ext.getCmp('itemUnitFormdet').setValue(tmp.itemUnitForm);

                                                                Application.RetalineGoodPurchaseNote.Cache.leastSKU = tmp.itemUnitForm;

                                                                Ext.getCmp('itemSmaalStockUnit').setValue(tmp.itemSmaalStockUnit);
                                                                Ext.getCmp('itemUnitForm').getStore().load({
                                                                    params: {
                                                                        isMedicine: tmp.isMedicine,
                                                                        stdpckl11: tmp.stdpckl11_package_type_id,
                                                                        stdpckl12: tmp.stdpckl12_package_type_id,
                                                                        stdpckl21: tmp.stdpckl21_package_type_id,
                                                                        stdpckl31: tmp.stdpckl31_package_type_id,
                                                                        stdpckl41: tmp.stdpckl41_package_type_id
                                                                    }
                                                                });

                                                            },
                                                            failure: function () {
                                                                Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                            }
                                                        });
                                                    } else {
                                                        Ext.MessageBox.alert('Notification', 'Choose Received at.');
                                                        Ext.getCmp('pe_partyItems').getStore().clearData();
                                                        Ext.getCmp('pe_partyItems').reset();
                                                    }


                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Qty',
                                            align: 'right',
                                            id: 'retgrnd_itemqty',
                                            name: 'n[retgrnd_itemqty]',
                                            anchor: '85%',
                                            allowBlank: false,
                                            allowNegative: false,
                                            allowDecimals: true,
                                            tabIndex: 834,
                                            listeners: {
                                                change: function () {
                                                    var retgrnd_itemqty = Ext.getCmp('retgrnd_itemqty').getValue();
                                                    var retgrnd_itemoffrrate = Ext.getCmp('retgrnd_itemoffrrate').getValue();
                                                    var amount = retgrnd_itemqty * retgrnd_itemoffrrate;
                                                    Ext.getCmp('retgrnd_amount').setValue(amount);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.2,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Units',
                                            id: 'itemUnitForm',
                                            name: 'itemUnitForm',
                                            labelStyle: mandatory_label,
                                            allowBlank: true,
                                            labelAlign: 'top',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '85%',
                                            store: packTypeStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'package_type_name',
                                            valueField: 'package_type_id',
                                            hiddenName: 'itemUnitForm',
                                            tabIndex: 835, listeners: {
                                                select: function () {
                                                    Ext.getCmp('retgrnd_itemoffrrate').label.update = 'Price / ' + Ext.getCmp('itemUnitForm').getRawValue();
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Price / Unit',
                                            align: 'right',
                                            id: 'retgrnd_itemoffrrate',
                                            name: 'n[retgrnd_itemoffrrate]',
                                            anchor: '85%',
                                            allowBlank: false,
                                            tabIndex: 836,
                                            listeners: {
                                                change: function () {
                                                    var retgrnd_itemqty = Ext.getCmp('retgrnd_itemqty').getValue();
                                                    var retgrnd_itemoffrrate = Ext.getCmp('retgrnd_itemoffrrate').getValue();
                                                    var amount = retgrnd_itemqty * retgrnd_itemoffrrate;
                                                    Ext.getCmp('retgrnd_amount').setValue(amount);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Amount',
                                            align: 'right',
                                            id: 'retgrnd_amount',
                                            name: 'n[retgrnd_amount]',
                                            anchor: '85%',
                                            allowBlank: false,
                                            tabIndex: 836,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'button',
                                            text: 'Add',
                                            tabIndex: 837,
                                            anchor: '85%',
                                            id: 'addItemsToPO',
                                            iconCls: 'finascop_add',
                                            style: 'margin-top:17px;',
                                            handler: function () {
                                                if ((Ext.getCmp('pe_partyItems').getValue() > 0) && (Ext.getCmp('retgrnd_amount').getValue() > 0)) {
                                                    Ext.Ajax.request({
                                                        waitMsg: 'Processing',
                                                        method: 'POST',
                                                        url: modURL + '&op=saveItemGRNRetqDetails',
                                                        params: {
                                                            retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet,
                                                            retgrnd_vendorid: Ext.getCmp('pe_party').getValue(),
                                                            retgrnd_itemid: Ext.getCmp('pe_partyItems').getValue(),
                                                            retgrnd_itemname: Ext.getCmp('pe_partyItems').getRawValue(),
                                                            retgrnd_itemoffrrate: Ext.getCmp('retgrnd_itemoffrrate').getRawValue(),
                                                            retgrnd_itemqty: Ext.getCmp('retgrnd_itemqty').getValue(),
                                                            retgrnd_purchasingUnit: Ext.getCmp('itemUnitForm').getValue(),
                                                            retgrn_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                                            poretgrn_updatedon: Application.RetalineGoodPurchaseNote.Cache.poretgrn_updatedon,
                                                            retgrnd_amount: Ext.getCmp('retgrnd_amount').getValue(),
                                                        },
                                                        success: function (response) {
                                                            var tmp = Ext.decode(response.responseText);
                                                            if (tmp.success === true)
                                                            {
                                                                Application.example.msg('Success', tmp.msg);
                                                                Application.RetalineGoodPurchaseNote.Cache.poretgrn_updatedon = tmp.date;
                                                                Ext.getCmp('itemHistory').hide();
                                                                Ext.getCmp('grnRetalineItemGrid').store.load({
                                                                    params: {
                                                                        retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet
                                                                    },
                                                                    callback: function () {
                                                                        Ext.getCmp('pe_partyItems').reset();
                                                                        Ext.getCmp('retgrnd_itemqty').reset();
                                                                        Ext.getCmp('itemUnitForm').reset();
                                                                        Ext.getCmp('retgrnd_itemoffrrate').reset();
                                                                        Ext.getCmp('retgrnd_amount').reset();
                                                                    }
                                                                });
                                                                Ext.getCmp('grnRetalineItemGrid').getStore().load({
                                                                    params: {
                                                                        retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet
                                                                    }
                                                                });

                                                            } else
                                                            {
                                                                Ext.MessageBox.alert("Failure", "Error moving Data, Kindly reload.");
                                                                Ext.getCmp('windowGRNRetalineCreate').close();

                                                            }
                                                        },
                                                        failure: function (response) {
                                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                                            Ext.MessageBox.alert('Error', tmp.msg);
                                                        }
                                                    });
                                                } else {
                                                    Ext.Msg.alert("Notification.", 'Data entered is invalid');
                                                }

                                            }
                                        }]
                                }]
                        }, {
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .50,
                                    labelAlign: 'top',
                                    hideLabel: 'true',
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            fieldLabel: '',
                                            id: 'itemSmaalStockUnit',
                                            style: {'font-weight': 'bold'},
                                            anchor: '97%'
                                        }
                                    ]
                                }]

                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Vendor Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'po_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [
                                        grnRetalineItemGrid()
                                    ]
                                }]
                        }]
                }]
        });
        return panel;
    };
    var grnRetalineItemGrid = function () {

        var GoodReceiveNoteItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listRetGrndetailsStore',
                    method: 'post'
                }),
                fields: ['retgrnd_amount', 'unitName', 'retgrnd_itemid', 'retgrnd_itemname', 'retgrnd_itemoffrrate', 'retgrnd_itemqty', 'retgrnd_itemoffrqty', 'retgrnd_totalqty', 'retgrnd_balanceqty', 'retgrnd_itemoffrrate', 'retgrnd_itemoffrrateet', 'itemDisc',
                    'retgrnd_itemaddidisc', 'retgrnd_effectiverate', 'retgrnd_idiscountcalculs', {name: 'retgrnd_amount', type: 'float'}, {name: 'retgrnd_netamount', type: 'float'}, {name: 'retgrnd_initialnetamount', type: 'float'}, 'retgrnd_gendiscount', 'retgrnd_shippingcharge'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.poId = Application.Finascop_Accounts_Purchase.Cache.POID;
                    }
                }

            });
            return itemStore;
        };
        var itemGridStore = GoodReceiveNoteItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'grnRetalineItemGrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: retgrnuisiteGoodReceiveNoteColModel(),
            tbar: [],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true
        });
        return itemgrid_panel;
    };
    var retgrnuisiteGoodReceiveNoteColModel = function ()
    {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'retgrnd_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'retgrnd_itemqty',
                tooltip: 'Qty',
                align: 'right',
                width: 50
            }, {
                header: 'Unit',
                sortable: true,
                hideable: false,
                dataIndex: 'unitName',
                tooltip: 'Unit',
                width: 50
            }, {
                header: 'Price',
                sortable: true,
                hideable: false,
                dataIndex: 'retgrnd_itemoffrrate',
                tooltip: 'Price',
                align: 'right',
                width: 50
            }, {
                header: 'Amount',
                sortable: true,
                hideable: false,
                dataIndex: 'retgrnd_amount',
                tooltip: 'Amount',
                align: 'right',
                width: 50
            }, {
                xtype: 'actioncolumn',
                hideable: false,
                width: 25,
                items: [{
                        tooltip: 'Remove Item',
                        iconCls: 'finascop_delete',
                        handler: function (grid, rowIndex, colIndex) {
                            var record = grid.store.getAt(rowIndex);
                            Ext.Msg.confirm('Notification', 'Do you really want to remove this item?', function (btn) {
                                if (btn == 'yes') {
                                    Application.example.msg('Success', 'Removed item');
                                    removeFromItemStore(grid, rowIndex, record);
                                }
                            });
                        }
                    }]
            }
        ]);
    };
    var removeFromItemStore = function (grid, indx, record)
    {

        Ext.Ajax.request({
            url: modURL + '&op=deleteVendorItemFromGRNRetaline',
            method: 'POST',
            params: {
                itemid: record.get('retgrnd_itemid'),
                uid: Application.RetalineGoodPurchaseNote.Cache.GRNRet
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true)
                {
                    Ext.getCmp('grnRetalineItemGrid').getStore().load({
                        params: {
                            retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet
                        }
                    });

                } else
                {
                    Ext.MessageBox.alert('Failed');
                }
            }
        });
    };
    var vendornewGRNViewForm = function (poId) {

        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'poViewForm',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .25,
                            labelAlign: 'left',
                            labelWidth: 42,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Vendor',
                                    id: 'stpa_Fname',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 1
                                }]
                        },
                        {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .25,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'PR Number',
                                    id: 'retgrn_name',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 1
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 50,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'PR Date',
                                    id: 'retgrn_date',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 2
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .30,
                            labelAlign: 'left',
                            labelWidth: 80,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Received At',
                                    id: 'centralStore',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }
                            ]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Vendor Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [
                                        viewGRNItemGrid(poId)
                                    ]
                                }]
                        }]
                }]
        });
        return panel;
    };
    var viewGRNItemGrid = function (poId) {

        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=viewListGRNdetailsStore',
                    method: 'post'
                }),
                fields: ['retgrnd_uniqueid', 'retgrnd_itemid', 'retgrnd_itemname', 'retgrnd_itemqty', 'retgrnd_itemoffrrate', 'retgrnd_purchasingUnit', 'unitName'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.poId = poId;
                    }
                }

            });
            return itemStore;
        };
        var itemGridStore = PurchaseOrderItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'newPOItemGrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: viewOrderColModel(),
            tbar: [],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true
        });
        return itemgrid_panel;
    };
    var viewOrderColModel = function ()
    {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'retgrnd_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'retgrnd_itemqty',
                tooltip: 'Qty',
                align: 'right',
                width: 60
            }, {
                header: 'Unit',
                sortable: true,
                hideable: false,
                dataIndex: 'unitName',
                tooltip: 'Unit',
                width: 60
            }, {
                header: 'Rate/Unit',
                sortable: true,
                dataIndex: 'retgrnd_itemoffrrate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate',
                align: 'right',
                width: 60
            },
        ]);
    };
    var retalineDistributionChartGrid = function (id) {
        var branch_store = distriChartStore();

        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });

        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'rdc_uid'
                }, {
                    type: 'date',
                    dataIndex: 'rdc_createdOn'
                },
                {
                    type: 'string',
                    dataIndex: 'status_name',
                    value: ['Chart Created'],
                },
                {
                    type: 'string',
                    dataIndex: 'sourcename'
                }, {
                    type: 'string',
                    dataIndex: 'rdc_type'
                }, {
                    type: 'string',
                    dataIndex: 'branch'
                }
            ]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Distribution Chart',
            iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Distribution Chart No',
                    id: 'branch_name_auto_expsi',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'rdc_uid',
                    tooltip: 'Stock Request No'
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'rdc_createdOn',
                    tooltip: 'Created Date'
                },
                {
                    header: 'Source',
                    sortable: true,
                    dataIndex: 'sourcename',
                    tooltip: 'Source'
                },
                {
                    header: 'Destination',
                    sortable: true,
                    dataIndex: 'branch',
                    tooltip: 'Destination'
                }, {
                    header: 'Initiated Branch',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'initiatedBranch',
                    tooltip: 'Initiated Branch'
                },
                {
                    header: 'Type',
                    hidden: true,
                    dataIndex: 'rdc_type',
                    tooltip: 'Type'
                },
                {
                    header: 'Status',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'status_name',
                    tooltip: 'Status'
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
                            var rdc_status = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_status;
                            disriChartActionMenu(e, rdc_status)
                        }
                    }
                }
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
                viewready: onGridResize,
                afterrender: function () {
                    if (_SESSION.br_PyramidLevel == 1) {
                        Ext.getCmp("comboxbranchnamedc").show();
                        Ext.getCmp("dcBranchDataeditCS").hide();
                    } else {
                        Ext.getCmp("comboxbranchnamedc").hide();
                        Ext.getCmp("dcBranchDataeditCS").show();
                        Ext.getCmp("dcBranchDataeditCS").setValue(_SESSION.current_branch);
                    }
                },
                selectionchange: gridSelectionChangedDistriChart
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Distribution Chart',
                    tooltip: 'Create Distribution Chart',
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
                                Application.RetalineGoodPurchaseNote.Cache.dcuuid = uid;
                                createRetalineDistributionChart(0);
                            }
                        });

                    }
                }, {
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'dcBranchDataeditCS',
                    name: 'dcBranchDataeditCS',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    setReadOnly: true,
                    hidden: true
                }, {
                    xtype: 'combo',
                    id: 'comboxbranchnamedc',
                    name: 'comboxbranchnamedc',
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
                    hiddenName: 'comboxbranchnamedc',
                    listeners: {
                        select: function () {
                            Ext.getCmp('retalineDistributionChart_main_panel').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    id: 'idshowbtntr',
                    handler: function () {
                        var branchName = Ext.getCmp('comboxbranchnamedc').getValue();
                        Ext.getCmp('retalineDistributionChart_main_panel').getStore().load({
                            params: {
                                branchName: branchName
                            }
                        });
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
            autoExpandColumn: 'branch_name_auto_expsi'
        });
        return grid_panel;
    };

    var gridSelectionChangedDistriChart = function () {
        if (!Ext.isEmpty(Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections())) {
            var rdc_status = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_status;
            var rdc_id = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_id;
        }
    };
    var distriChartStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetalineDistributionChart',
                method: 'post'
            }),
            fields: ['rdc_id', 'rdc_uid', 'rdc_source', 'rdc_destination', 'rdc_status', 'rdc_createdOn', 'sourcename', 'branch', 'status_name', 'rdc_type', 'initiatedBranch', 'rdc_initiatedBy'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    this.baseParams.branchName = Ext.getCmp('comboxbranchnamedc').getValue();
                }
            }
        });
        store.setDefaultSort('rdc_createdOn', 'DESC');
        return store;
    };
    var createRetalineDistributionChart = function () {
        var resultWindow = new Ext.Window({
            id: "windowRetalineDistributionChart",
            iconCls: 'vender-items',
            shadow: false,
            height: 600,
            width: 950,
            title: 'Create New Distribution Chart',
            layout: 'border',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [{
                    region: 'center',
                    layout: 'fit',
                    height: 130,
                    items: createRetalineDistributionChartform()
                }, {
                    region: 'south',
                    layout: 'fit',
                    height: 470,
                    items: createRetalineDistributionChartItemStockGrid()
                }],
            bbar: ['->',
                {
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Cancel',
                    handler: function () {
                        Ext.getCmp('windowRetalineDistributionChart').close();
                        Ext.getCmp('retalineDistributionChart_main_panel').getStore().load();
                    }

                }, {
                    xtype: 'button',
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        var dcItems = getMigratedData('retalineDistributionChartItemGridPanel');
                        var itemStore = Ext.getCmp('retalineDistributionChartItemGridPanel').getStore();
                        var rdc_packedItemQty = itemStore.sum('rdc_packedItemQty');
                        var inStockValue = Ext.getCmp('disChrt_inStock').getValue();
                        var alottedValue = Ext.getCmp('disChrt_alotted').getValue();
                        var totalAlottedStock = itemStore.sum('rdc_ApprovedItemQty') + itemStore.sum('rdc_packedItemQty');
                        console.log('totalAlottedStock', totalAlottedStock);
                        if (rdc_packedItemQty > 0) {
                            if (totalAlottedStock <= inStockValue) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=saveDistributionChart',
                                    method: 'POST',
                                    params: {
                                        uuid: Application.RetalineGoodPurchaseNote.Cache.dcuuid,
                                        dc_ItemId: Ext.getCmp('dc_ItemId').getValue(),
                                        dc_source: Ext.getCmp('dc_source').getValue(),
                                        dc_date: Ext.getCmp('dc_date').getValue(),
                                        dcItems: dcItems
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.msg);
                                            Application.RetalineGoodPurchaseNote.Cache.dcuuid = '';
                                            Ext.getCmp('retalineDistributionChart_main_panel').getStore().reload();
                                            resultWindow.close();
                                            resetDistributionData();


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
                            } else {
                                Ext.MessageBox.alert("Notification", 'Item Quantity exceeds current stock');
                            }

                        }
                        else
                        {
                            Ext.MessageBox.alert("Notification", 'Enter quantity in To Pack.');
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
    var rtrSourceStore = function (isCPD) {
        var store = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranch',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                beforeload: function () {
                    this.baseParams.type = isCPD;
                },
                load: function () {
                    Ext.getCmp("dc_source").setValue(_SESSION.finascop_current_branch_id);
                    Ext.getCmp('dc_ItemId').getStore().load({
                        params: {
                            sourceBranch: Ext.getCmp("dc_source").getValue()
                        }
                    });
                }
            }
        });
        return store;
    };
    var rtItemStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['stit_ID', 'stit_itemName', 'stit_SKU', 'packageName', 'id', 'fsbg_id', 'fpod_leastSKUmrp'],
            url: modURL + '&op=getItemName',
            autoLoad: true,
            method: 'post'
        });
        return store;
    };
    var createRetalineDistributionChartform = function (rdc_id) {

        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var itemsStore = rtItemStore();
        var sourceStore = rtrSourceStore(1);
        var poItemStockformPanel = new Ext.form.FormPanel({
            height: 130,
            frame: true,
            border: false,
            id: 'distributionChartItemFormPanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'datefield',
                                    fieldLabel: 'Date',
                                    id: 'dc_date',
                                    name: 'dc_date',
                                    anchor: '97%',
                                    allowBlank: false,
                                    tabIndex: 699,
                                    format: "d-m-Y",
                                    maxValue: new Date(),
                                    value: new Date()
                                }]
                        }, {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.20,
                            items: [{
                                    xtype: 'combo',
                                    id: 'dc_source',
                                    name: 'dc_source',
                                    mode: 'local',
                                    typeAhead: true,
                                    selectOnFocus: true,
                                    fieldLabel: 'Source',
                                    editable: true,
                                    anchor: '97%',
                                    store: sourceStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    tabIndex: 700,
                                    hiddenName: 'dc_source',
                                    listeners: {
                                        select: function () {
                                            var dc_source = Ext.getCmp('dc_source').getValue();
                                            Ext.getCmp('dc_ItemId').getStore().load({
                                                params: {
                                                    sourceBranch: dc_source
                                                }
                                            });
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.50,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Items',
                                    id: 'dc_ItemId',
                                    name: 'dc_ItemId',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    editable: true,
                                    anchor: '98%',
                                    store: itemsStore,
                                    triggerAction: 'all',
                                    minChars: 1,
                                    selectOnFocus: false,
                                    displayField: 'stit_SKU',
                                    valueField: 'stit_ID',
                                    hiddenName: 'dc_ItemId',
                                    tabIndex: 701,
                                    listeners: {
                                        select: function (cmbo, record, index) {
                                            showData();

                                        }
                                    }
                                }]
                        }]
                }]
        });
        return poItemStockformPanel;
    };
    var showData = function () {
        updateToItemQuantity();
        var dc_source = Ext.getCmp('dc_source').getValue();
        var dc_ItemId = Ext.getCmp('dc_ItemId').getValue();
        var dc_date = Ext.getCmp('dc_date').getValue();
        Ext.Ajax.request({
            url: modURL + '&op=toLoadAllStocks',
            method: 'POST',
            params: {
                dc_source: dc_source,
                dc_ItemId: dc_ItemId,
                dc_date: dc_date
            },
            success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true && tmp.valid === true) {
                    Ext.getCmp('disChrt_projected').setValue(tmp.projectedStock);
                    Ext.getCmp('disChrt_inStock').setValue(tmp.inStock);
                    Ext.getCmp('disChrt_alotted').setValue(tmp.allottedStock);
                    var balance = parseFloat(tmp.inStock) - parseFloat(tmp.allottedStock);
                    Ext.getCmp('disChrt_balance').setValue(balance);
                } else {
                    Ext.Msg.alert("Error", 'Entered data is not valid.');
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.message);
            }
        });
        Ext.getCmp('retalineDistributionChartItemGridPanel').getStore().load({
            params: {
                dc_source: dc_source,
                dc_ItemId: dc_ItemId,
                dc_date: dc_date
            }
        });
    };

    var resetDistributionData = function () {
        createRetalineDistributionChart();
        //Ext.getCmp('retalineDistributionChartItemGridPanel').getStore().removeAll();
    };
    var createRetalineDistributionChartItemStockGrid = function () {

        var retalineTransferRequestItemGridStore = retalineItemDistributionChartStore();
        var poStockEntryGrid = new Ext.grid.EditorGridPanel({
            store: retalineTransferRequestItemGridStore,
            frame: false,
            border: true,
            height: 250,
            autoScroll: true,
            plugins: [],
            id: 'retalineDistributionChartItemGridPanel',
            iconCls: 'finascop_dataentry',
            clicksToEdit: 2,
            loadMask: true,
            tbar: [{'html': '<b>&nbsp;&nbsp;Projected:&nbsp;</b>', style: {
                        'font-size': '12px'
                    }}, {
                    xtype: 'displayfield',
                    fieldLabel: 'Projected',
                    id: 'disChrt_projected',
                    name: 'disChrt_projected',
                    value: 0,
                    tabIndex: 703,
                    style: {
                        'font-size': '13px',
                        'font-weight': 'bold'
                    },
                    anchor: '97%'
                }, {'html': '<b>&nbsp;&nbsp;In Stock:&nbsp;</b>', style: {
                        'font-size': '12px'
                    }}, {
                    xtype: 'displayfield',
                    fieldLabel: 'In stock',
                    id: 'disChrt_inStock',
                    name: 'disChrt_inStock',
                    tabIndex: 702,
                    value: 0,
                    editable: false,
                    style: {
                        'font-size': '13px',
                        'font-weight': 'bold'
                    },
                    anchor: '97%'
                }, {'html': '<b>&nbsp;&nbsp;Alotted:&nbsp;</b>', style: {
                        'font-size': '12px'
                    }}, {
                    xtype: 'displayfield',
                    fieldLabel: 'Alotted',
                    id: 'disChrt_alotted',
                    name: 'disChrt_alotted',
                    value: 0,
                    tabIndex: 703,
                    style: {
                        'font-size': '13px',
                        'font-weight': 'bold'
                    },
                    anchor: '97%'
                }, {'html': '<b>&nbsp;&nbsp;Balance:&nbsp;</b>', style: {
                        'font-size': '13px'
                    }}, {
                    xtype: 'displayfield',
                    fieldLabel: 'Balance',
                    id: 'disChrt_balance',
                    value: 0,
                    style: {
                        'font-size': '13px',
                        'font-weight': 'bold'
                    },
                    name: 'disChrt_balance',
                    tabIndex: 703,
                    anchor: '97%'
                }],
            columns: [
                {
                    header: 'Mart',
                    sortable: true,
                    dataIndex: 'br_Name',
                    width: 150,
                    tooltip: 'Mart'
                }, {
                    header: 'Closing Stock',
                    sortable: true,
                    dataIndex: 'rdc_ClosingItemQty',
                    align: 'right',
                    tooltip: 'Closing Stock'
                },
                {
                    header: 'Stock Requested',
                    sortable: true,
                    dataIndex: 'fstr_RequiredItemQty',
                    align: 'right',
                    tooltip: 'Stock Requested'
                }, {
                    header: 'Packed Quantity',
                    dataIndex: 'rdc_ApprovedItemQty',
                    xtype: 'numbercolumn',
                    tooltip: 'Packed Quantity',
                    align: 'right',
                }, {
                    header: 'To Pack',
                    sortable: true,
                    flex: 1,
                    xtype: 'numbercolumn',
                    dataIndex: 'rdc_packedItemQty',
                    align: 'right',
                    tooltip: 'To Pack',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3
                    }
                }, {
                    header: 'Unit',
                    dataIndex: 'unitName',
                    tooltip: 'Unit'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Actions',
                    hideable: false,
                    groupable: false,
                    hidden: true,
                    width: 80,
                    items: [
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Delete Order',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                //deleteItemDistributionChart(record.get('fstrd_id'));
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
            listeners: {afteredit: updateToItemQuantity},
            stripeRows: true,
        });
        return poStockEntryGrid;
    };
    var updateToItemQuantity = function (grid_event) {
        //var data = grid_event.record.data;
        //console.log('data', data);
        Ext.getCmp('disChrt_alotted').setValue(0);
        Ext.getCmp('disChrt_balance').setValue(0);
        var alottedValue = 0, stockValue = 0;
        var itemStore = Ext.getCmp('retalineDistributionChartItemGridPanel').getStore();
        alottedValue = itemStore.sum('rdc_ApprovedItemQty') + itemStore.sum('rdc_packedItemQty');
        console.log('alottedValue', alottedValue);
        stockValue = Ext.getCmp('disChrt_inStock').getValue();
        stockValue = parseFloat(stockValue);
        console.log('stockValue', stockValue);
        if (alottedValue <= stockValue) {
            if (alottedValue == 0) {
                Ext.getCmp('disChrt_alotted').setValue(0);
                Ext.getCmp('disChrt_balance').setValue(stockValue);
            } else {
                Ext.getCmp('disChrt_alotted').setValue(alottedValue);
                var disChrt_balance = stockValue - alottedValue;
                Ext.getCmp('disChrt_balance').setValue(disChrt_balance);
            }

        } else {
            //grid_event.record.data.rdc_packedItemQty = 0;
            Ext.MessageBox.alert('Notification', 'Item Quantity exceeds current stock');
        }

    };
    var disriChartActionMenu = function (e, rdc_status) {
        var stockrequestActionMenu = new Ext.menu.Menu({
            items: [{text: "View",
                    handler: function () {
                        var rdc_id = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_id;
                        var rdc_status = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_status;
                        var branchDesi = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.branch;
                        Application.RetalineGoodPurchaseNote.viewDistributionChartItems(rdc_id, branchDesi);

                    }
                }, {
                    text: "Print Packing List",
                    handler: function () {
                        var rdc_id = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_id;
                        var rdc_status = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_status;
                        var branchDesi = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.branch;
                        printPackingSlip(rdc_id, rdc_status, branchDesi);


                    }
                }, {
                    text: "Manage Packing",
                    handler: function () {
                        var rdc_id = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_id;
                        var rdc_status = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.rdc_status;
                        var branchDesi = Ext.getCmp('retalineDistributionChart_main_panel').getSelectionModel().getSelections()[0].data.branch;
                        if (rdc_status > 1) {
                            //Application.RetalineGoodPurchaseNote.viewDistributionChartItems(rdc_id, branchDesi);
                            Ext.MessageBox.alert('Notification', 'Already Packed');
                        } else {
                            manualPackingWindow(rdc_id, branchDesi);
                        }

                    }
                }]
        });
        stockrequestActionMenu.showAt(e.getXY());
    };
    var retalineItemDistributionChartStore = function () {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listDistributionChartItemStore',
            fields: ['br_ID', 'br_Name', 'fstr_RequiredItemQty', 'unitName', {name: 'rdc_packedItemQty', type: 'float'}, {name: 'rdc_ApprovedItemQty', type: 'float'}, {name: 'rdc_ClosingItemQty', type: 'float'}],
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
    var ViewOnlyDistributionChartGrid = function (rdc_id) {
        var viewTeansfer_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'rdc_ItemName'
                }, {
                    type: 'string',
                    dataIndex: 'rdc_ApprovedItemQty'
                }
            ]
        });
        viewTeansfer_filter.remote = true;
        viewTeansfer_filter.autoReload = true;
        var _CreateOrdergridPanel = new Ext.grid.EditorGridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _StoreSi(rdc_id),
            autoScroll: true,
            width: winsize.width * 0.6,
            plugins: [viewTeansfer_filter],
            height: 250,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelViewactionItemsdata',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    dataIndex: 'rdc_ItemName',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                }, {
                    header: 'Chart Qty',
                    dataIndex: 'rdc_ApprovedItemQty',
                    sortable: true,
                    tooltip: 'Chart Quantity',
                    hideable: true,
                    align: 'right'
                },
                {
                    header: 'Packed Qty',
                    dataIndex: 'rdc_TransferedItemQty',
                    sortable: true,
                    tooltip: 'Quantity',
                    hideable: true,
                    align: 'right'
                }, {
                    header: 'Unit',
                    dataIndex: 'packageType',
                    sortable: true,
                    tooltip: 'Unit',
                    hideable: false
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelViewactionItemsdata').getStore().load({
                        params: {
                            rdc_id: rdc_id
                        }
                    });
                }
            }
        });
        return _CreateOrdergridPanel;
    };
    var ViewDistributionChartGrid = function (rdc_id) {
        var viewTeansfer_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'rdc_ItemName'
                }, {
                    type: 'string',
                    dataIndex: 'rdc_ApprovedItemQty'
                }
            ]
        });
        viewTeansfer_filter.remote = true;
        viewTeansfer_filter.autoReload = true;
        var _CreateOrdergridPanel = new Ext.grid.EditorGridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _StoreSi(rdc_id),
            autoScroll: true,
            width: winsize.width * 0.6,
            plugins: [viewTeansfer_filter],
            height: 250,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelVieItemsdata',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    dataIndex: 'rdc_ItemName',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                }, {
                    header: 'Current Stock',
                    dataIndex: 'currentstock',
                    sortable: true,
                    tooltip: 'Current Stock',
                    hideable: true,
                    align: 'right'
                },
                {
                    header: 'Alotted Qty',
                    dataIndex: 'rdc_ApprovedItemQty',
                    sortable: true,
                    tooltip: 'Alotted Quantity',
                    hideable: true,
                    align: 'right'
                }, {
                    header: 'Packed Qty',
                    dataIndex: 'rdc_TransferedItemQty',
                    xtype: 'numbercolumn',
                    sortable: true,
                    tooltip: 'Packed Quantity',
                    hideable: true,
                    align: 'right',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield',
                        allowNegative: false,
                        allowDecimals: true,
                    }
                }, {
                    header: 'Unit',
                    dataIndex: 'packageType',
                    sortable: true,
                    tooltip: 'Unit',
                    hideable: false
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    width: 25,
                    items: [{
                            iconCls: 'finascop_delete',
                            tooltip: 'Remove Item',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteItemDistributionChartBeforePack(record.get('rdcd_id'), record.get('rdc_id'));
                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('rdc_TransferedItemQty') > record.get('currentstock'))
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else {
                        return '';
                    }
                }
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelVieItemsdata').getStore().load({
                        params: {
                            rdc_id: rdc_id
                        }
                    });
                }
            }
        });
        return _CreateOrdergridPanel;
    };
    var _StoreSi = function (rdc_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listViewDistributionChartData',
                method: 'post'
            }),
            fields: ['rdc_id', 'rdcd_id', 'rdc_ItemId', 'rdc_RequiredItemQty', 'rdc_ApprovedItemQty', 'rdc_ItemName', 'subcategoryname', 'status_name', 'fstrd_status', 'category_name',
                'packageType', 'rdc_ItemUnits', 'rdc_leastSKUCount', 'least_package_type_id', 'least_package_type', {name: 'rdc_TransferedItemQty', type: 'float'}, 'currentstock'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.rdc_id = rdc_id;
                }
            }
        });
        return _Store;
    };
    var deleteItemDistributionChartBeforePack = function (rdcd_id, rdc_id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteItemBfrPack',
                    params: {
                        rdcd_id: rdcd_id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed item');
                            Ext.getCmp('gridpanelVieItemsdata').getStore().load({
                                params: {
                                    rdc_id: rdc_id
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
    var stlAlertItemStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listStckAlertItems',
            fields: ['stit_id', 'branch_id', 'item_count', 'mrp', 'selling_price', 'stit_SKU', 'rpd_quantity', 'total'],
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            totalProperty: 'totalCount',
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.stal_branch = Ext.getCmp('stal_branch').getValue();
                    this.baseParams.sa_date = Ext.getCmp('sa_date').getValue();
                }
            }

        });
        return store;
    };
    var stckAlertProductsGrid = function () {
        var stlAlertItemStorenew = stlAlertItemStore();
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
            store: stlAlertItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelStockAlertItem',
            columns: [{
                    header: 'SKU',
                    width: 400,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Projected Stock',
                    dataIndex: 'rpd_quantity',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'In Stock',
                    dataIndex: 'item_count',
                    hideable: true,
                    align: 'right',
                    sortable: true,
                }, {
                    header: 'Total',
                    dataIndex: 'total',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Unit',
                    dataIndex: 'selling_price',
                    hideable: true,
                    hidden: true,
                    align: 'right'
                }],
            tbar: [],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                cellClick: function (grid, rowIndex, colIdx, e) {
                    var rec = grid.getDataSource().getAt(rowIndex);
                    var cellVal = rec.data[grid.getColumnModel().getDataIndex(colIdx)];
                    copy(cellVal);
                }
            }
        });
        return addItem;
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
                    if (_SESSION.br_PyramidLevel > 1) {
                        Ext.getCmp('stal_branch').setValue(_SESSION.finascop_current_branch_id);
                        var stal_branch = Ext.getCmp('stal_branch').getValue();
                        var sa_date = Ext.getCmp('sa_date').getValue();
                        Ext.get('iframeAlert').dom.src = '?module=retaline_grn&op=printSendAlertList&stal_branch=' + stal_branch + '&sa_date=' + sa_date;
//                        Ext.getCmp('gridpanelStockAlertItem').getStore().baseParams.stal_branch = _SESSION.finascop_current_branch_id;
//                        Ext.getCmp('gridpanelStockAlertItem').getStore().baseParams.sa_date = Ext.getCmp('sa_date').getValue();
//                        Ext.getCmp('gridpanelStockAlertItem').getStore().load();
                    }

                }
            }
        });
        return store;
    };
    var manualPackingWindow = function (rdc_id, branchDesi) {
//        var _Orderpickerform = AddNewOrderPickerForm();
        var barcodesearch_field = "";
        var fsto_id = fsto_id;
        var _manualPackingWindow = new Ext.Window({
            title: 'Packing for ' + branchDesi,
            layout: 'fit',
            width: winsize.width * 0.7,
            height: winsize.height * 0.6,
            resizable: false,
            draggable: true,
            modal: true,
            id: 'manualPackingWind',
            closable: false,
            bodyStyle: {"background-color": "white"},
            items: [ViewDistributionChartGrid(rdc_id)],
            buttons: [{
                    html: '&nbsp;Extra Charges : &nbsp;',
                }, {
                    xtype: 'numberfield',
                    id: 'extraCharges',
                    name: 'extraCharges',
                    anchor: '98%',
                    tabIndex: 109,
                    emptyText: 'Extra Charges',
                    value: 0
                }, '-', {
                    html: '&nbsp;No of Box : &nbsp;',
                }, {
                    xtype: 'numberfield',
                    id: 'tonoofbags',
                    name: 'tonoofbags',
                    anchor: '98%',
                    tabIndex: 109,
                    emptyText: 'No of Box',
                    value: 0
                }, {
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    tabIndex: 511,
                    handler: function () {
                        _manualPackingWindow.close();
                    }
                }, {
                    text: 'Submit',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    cls: 'left-right-buttons',
                    diasabled: 'true',
                    id: 'submit_button',
                    handler: function () {
                        var itemGriddata = getMigratedData('gridpanelVieItemsdata');
                        var rdc_id = Ext.getCmp("retalineDistributionChart_main_panel").getSelectionModel().getSelections()[0].data.rdc_id;
                        var itemStore = Ext.getCmp('gridpanelVieItemsdata').getStore();
                        var rdc_ApprovedItemQty = itemStore.sum('rdc_ApprovedItemQty');
                        var rdc_TransferedItemQty = itemStore.sum('rdc_TransferedItemQty');
                        var noofbags = Ext.getCmp('tonoofbags').getValue();
                        var extraCharges = Ext.getCmp('extraCharges').getValue();
                        if ((rdc_TransferedItemQty > 0) && (noofbags >= 1)) {
                            Ext.Ajax.request({
                                url: modURL + '&op=rdcCreateTransferOrder',
                                method: 'POST',
                                params: {
                                    itemGriddata: itemGriddata,
                                    rdc_id: rdc_id,
                                    noofbags: noofbags,
                                    extraCharges: extraCharges
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true && tmp.valid == true) {
                                        if (tmp.order_id > 0) {
                                            Ext.Ajax.request({
                                                url: modURL + '&op=submitManualPacking',
                                                method: 'POST',
                                                params: {
                                                    itemGriddata: itemGriddata,
                                                    rdc_id: rdc_id,
                                                    noofbags: noofbags,
                                                    extraCharges: extraCharges,
                                                    order_id: tmp.order_id,
                                                    fsto_uid: tmp.fsto_uid
                                                },
                                                success: function (response) {
                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success == true && tmp.valid == true) {
                                                        Application.RetalineTransferOrder.viewPackageWindow(tmp.packcount, tmp.data);
                                                        _manualPackingWindow.close();
                                                        var branchName = Ext.getCmp('comboxbranchnamedc').getValue();
                                                        Ext.getCmp('retalineDistributionChart_main_panel').getStore().load({
                                                            params: {
                                                                branchName: branchName
                                                            }
                                                        });
                                                    } else if (tmp.success == true && tmp.valid == false) {
                                                        Ext.Msg.alert("Notification", tmp.msg);
                                                    } else if (tmp.status == 'error') {
                                                        Ext.Msg.alert("Error", tmp.error.msg);
                                                    } else {
                                                        if (!Ext.isEmpty(tmp.data)) {
                                                            Ext.Msg.alert("Error", tmp.data);
                                                        }
                                                        if (!Ext.isEmpty(tmp.error.msg)) {
                                                            Ext.Msg.alert("Error", tmp.error.msg);
                                                        }
                                                        if (!Ext.isEmpty(tmp.msg)) {
                                                            Ext.Msg.alert("Error", tmp.msg);
                                                        }

                                                    }
                                                },
                                                failure: function (response) {
                                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                                    Ext.MessageBox.alert('Error', tmp.msg);
                                                }
                                            });
                                        } else {
                                            Ext.Msg.alert("Error", "Order not created.");
                                        }

                                    } else if (tmp.success == true && tmp.valid == false) {
                                        Ext.Msg.alert("Notification", tmp.msg);
                                    } else if (tmp.success == false && tmp.valid == false) {
                                        Ext.Msg.alert("Notification", tmp.msg);
                                    } else if (tmp.status == 'error') {
                                        Ext.Msg.alert("Error", tmp.error.msg);
                                    } else {
                                        Ext.Msg.alert("Error", tmp.msg);
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.msg);
                                }
                            });

                        } else {
                            Ext.MessageBox.alert('Notification', 'No of box should be atleast 1.');
                            if (Ext.getCmp('tonoofbags').value <= 0) {
                                Ext.getCmp('tonoofbags').markInvalid();
                            }

                        }
                    }

                }],
            listeners: {
                afterrender: function () {

                }

            }


        });
        _manualPackingWindow.doLayout();
        _manualPackingWindow.show();
        _manualPackingWindow.center();
    };
    var addItemStore = function () {
        var sellingPricestore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listSelectedItems',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['branch_id', 'stitId', 'stit_SKU', 'item_count', 'selling_price', 'fpod_customerRatePikup', 'fpod_skuPurchaseRange', 'fpod_skuPurchaseQty', 'fpod_skuAvgPurchaseRate', 'fpod_skuLastPurchaseRate',
                'fpod_leastSKUepr', 'fpod_customerRatePikup', 'fpod_effectivemargin', 'selling_price', 'lastdate']),
            sortInfo: {
                field: 'stitId',
                direction: "ASC"
            },
            remoteSort: true,
            groupField: '',
            groupDir: '',
            listeners: {
                load: function () {
                    Ext.getCmp('nootitemto').focus();
                }
            }
        });
        return sellingPricestore;
    };
    var selectedPrductsGrid = function () {
        var addItemStorenew = addItemStore();
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
        var addItem = new Ext.grid.EditorGridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelUpdateSellingPrice',
            columns: [{
                    header: 'SKU',
                    width: 250,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'New Purchase Range',
                    dataIndex: 'fpod_skuPurchaseRange',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Purchase Qty',
                    dataIndex: 'fpod_skuPurchaseQty',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Average Purchase Rate',
                    dataIndex: 'fpod_skuAvgPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Last Purchase Rate',
                    dataIndex: 'fpod_skuLastPurchaseRate',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'EPR',
                    dataIndex: 'fpod_leastSKUepr',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Current Price',
                    dataIndex: 'fpod_customerRatePikup',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Effective Margin',
                    dataIndex: 'fpod_effectivemargin',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'New Selling Price',
                    dataIndex: 'selling_price',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        allowBlank: false,
                        xtype: 'numberfield'
                    }
                }],
            tbar: [{html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstoreBaseStation(),
                    id: 'search_baseStation',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'search_baseStation',
                    name: 'search_baseStation',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelUpdateSellingPrice').getStore().removeAll();
                        }
                    }
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('search_baseStation').getValue() > 0) {
                            Ext.getCmp('gridpanelUpdateSellingPrice').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('search_baseStation').getValue()
                                }
                            })
                        } else {
                            Ext.MessageBox.alert('Notification', "Selling Price updation is possible for Base Station(Distributors)");
                        }

                    }
                }, '->', {html: 'Last selling price updated on:'}, , {
                    id: 'nootitemto',
                    name: 'nootitemto',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var totalValue = 0, actualValue = 0;
                            Ext.getCmp('nootitemto').setValue(Ext.getCmp('gridpanelUpdateSellingPrice').getStore().data.items[0].data.lastdate); //store_stock

                        }
                    }
                }],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('fpod_effectivemargin') < 0)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('fpod_effectivemargin') < -10)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
            }),
            listeners: {
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var branchstoreBaseStation = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getBaseStationBranch',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (thisstore, records, options) {
                    if (_SESSION.br_PyramidLevel == 3) {
                        Ext.getCmp('search_baseStation').setValue(_SESSION.finascop_current_branch_id);
                    }

                }
            }
        });
        return store;
    };
    var allbranchstore = function (ind) {
        var store = new Ext.data.JsonStore({
            fields: ['br_Name', 'br_ID'],
            url: modURL + '&op=getAllBranch',
            method: 'post',
            autoLoad: true,
            root: 'data',
            remoteSort: true,
            listeners: {
                load: function (thisstore, records, options) {
                }
            }
        });
        return store;
    };

    var salesPrductsGrid = function () {
        var addItemStorenew = salesItemStore();
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
        var addItem = new Ext.grid.EditorGridPanel({
            store: addItemStorenew,
            height: 400,
            frame: true,
            border: false,
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelUpdateSales',
            columns: [{
                    header: 'Item',
                    width: 400,
                    dataIndex: 'stit_SKU',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Unit',
                    dataIndex: 'least_package_type_name',
                    hideable: true,
                    sortable: true,
                }, {
                    header: 'Opening stock',
                    dataIndex: 'openingStockCount',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Purchase',
                    dataIndex: 'purchasedItem',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Transfer',
                    dataIndex: 'transfereCumSales',
                    hideable: true,
                    hidden: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Damage',
                    dataIndex: 'damagedItem',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Return',
                    dataIndex: 'returnItem',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Sales',
                    dataIndex: 'salesEntry',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3,
                        id: 'salesEntry'
                    }
                }, {
                    header: 'Balance',
                    dataIndex: 'closingCount',
                    hideable: true,
                    sortable: true,
                    hidden: true,
                    align: 'right',
                    editor: {
                        //allowBlank: false,
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3
                    }
                }, {
                    header: 'Price',
                    dataIndex: 'institutinalPrice',
                    hideable: true,
                    sortable: true,
                    align: 'right',
                    editor: {
                        //allowBlank: false,
                        xtype: 'numberfield',
                        forcePrecision: true,
                        decimalPrecision: 3
                    }
                }, {
                    header: 'Shortage',
                    dataIndex: 'shortageItem',
                    hideable: true,
                    sortable: true,
                    hidden: true,
                    align: 'right'
                }, {
                    header: 'Wastage',
                    dataIndex: 'wastageCount',
                    hideable: true,
                    hidden: true,
                    sortable: true,
                    align: 'right'
                }],
            tbar: [{html: 'Branch : &nbsp;'}, {
                    xtype: 'combo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: allbranchstore(),
                    id: 'search_allBranch',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'search_allBranch',
                    name: 'search_allBranch',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            Ext.getCmp('gridpanelUpdateSales').getStore().removeAll();
                        }
                    }
                }, '|', {
                    xtype: 'datefield',
                    fieldLabel: 'Date',
                    id: 'sales_dateview',
                    name: 'sales_dateview',
                    anchor: '97%',
                    allowBlank: false,
                    tabIndex: 830,
                    format: "d/m/Y",
                    maxValue: new Date(),
                    value: new Date()
                }, {
                    xtype: 'button',
                    text: 'Show',
                    style: "padding-left: 10px;",
                    handler: function () {
                        if (Ext.getCmp('search_allBranch').getValue() > 0) {
                            Ext.getCmp('gridpanelUpdateSales').getStore().load({
                                params: {
                                    baseStation: Ext.getCmp('search_allBranch').getValue(),
                                    sales_dateview: Ext.getCmp('sales_dateview').getValue()
                                }
                            })
                        } else {
                            Ext.MessageBox.alert('Notification', "Choose Branch to update sales)");
                        }

                    }
                }],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                'beforeedit': function (e) {
                    var rec = e.record;

//                    var me = this;
//                    var allowed = !!me.isEditAllowed;
//                    if (rec.data.isBrOnline == true) {
//                        return;
//                    } else {
//                        me.isEditAllowed = true;
//                    }
//                    return allowed;
                },
                afteredit: updateSalesEntry
            }
        });
        return addItem;
    };
    var salesItemStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listSalesItems',
            fields: ['isBrOnline', 'branch_id', 'stitItemId', 'stit_SKU', 'item_count', 'openingStockCount', 'closingCount', 'purchasedItem', 'receiveddItem', 'shortageItem', 'transfereCumSales', 'wastageCount',
                'returnItem', 'damagedItem', 'salesEntry', 'least_package_type_name', 'institutinalPrice'],
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            totalProperty: 'totalCount',
            listeners: {
                load: function (store, rec, opt) {
                    if (store.totalLength == 1) {
                        if (store.getAt(0).get("isBrOnline") == true) {
                            Ext.getCmp('salesEntry').setReadOnly(true);
                        } else {
                            Ext.getCmp('salesEntry').setReadOnly(false);
                        }

                    }
                }
            }

        });
        return store;
    };
    var printPackingSlip = function (rdc_id, rdc_status, branchDesi) {
        var printSlipPanel = createprintSlipPanel(rdc_id, rdc_status, branchDesi);
        var printSlipWindow = Ext.getCmp('printSlipWindow');
        if (Ext.isEmpty(printSlipWindow)) {
            printSlipWindow = new Ext.Window({
                id: 'printOrderWindow',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Packing Slip',
                width: 950,
                autoHeight: true,
                items: [printSlipPanel],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon61',
                        handler: function () {
                            printSlipWindow.close();
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
                        if (!Ext.isEmpty(Ext.getCmp('manage_order_grid'))) {
                            Ext.getCmp('manage_order_grid').getStore().load();
                        }
                    }
                }
            });
        }
        printSlipWindow.doLayout();
        printSlipWindow.show();
        printSlipWindow.center();
    };
    var createprintSlipPanel = function (rdc_id, rdc_status, branchDesi) {
        var src = '?module=retaline_grn&op=printPackingList&rdc_id=' + rdc_id + '&rdc_status=' + rdc_status + '&branchDesi=' + branchDesi;
        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 500,
            id: 'printSlipPanel',
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
    function copy(txt) {
        var flashcopier = 'flashcopier';
        if (!document.getElementById(flashcopier)) {
            var divholder = document.createElement('div');
            divholder.id = flashcopier;
            document.body.appendChild(divholder);
        }
        document.getElementById(flashcopier).innerHTML = '';
        var divinfo = '<embed src="_clipboard.swf" FlashVars="clipboard=' + encodeURIComponent(txt) + '" width="0" height="0" type="application/x-shockwave-flash"></embed>';
        document.getElementById(flashcopier).innerHTML = divinfo;
    }
    ;
    var viewSendAlertPanel = function () {
        var src = '?module=retaline_grn&op=printSendAlertList&stal_branch=' + stal_branch + '&sa_date=' + sa_date;
        var myPanel = new Ext.Panel({
            layout: 'border',
            height: 500,
            id: 'printSendAlertPanel',
            title: 'Stock Alert of ' + stal_branchName + ' on ' + sa_date,
            items: [{
                    region: 'center',
                    border: false,
                    html: '<iframe src="' + src +
                            '" id="iframeAlert" name="iframeAlert" ' +
                            'width="100%" height="100%" allow="clipboard-read; clipboard-write" style="border:none">'
                }]
        });
        return myPanel;
    };
    var printSendAlertSlip = function (stal_branch, sa_date, stckAlertItems) {
        var printAlertPanel = viewSendAlertPanel(stal_branch, sa_date, stckAlertItems);
        var printSlipWindow = Ext.getCmp('printAlertWindow');
        if (Ext.isEmpty(printSlipWindow)) {
            printSlipWindow = new Ext.Window({
                id: 'printAlertWindow',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                title: 'Send Alert',
                width: 950,
                autoHeight: true,
                items: [printAlertPanel],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon61',
                        handler: function () {
                            printSlipWindow.close();
                        }
                    }, {
                        text: 'Send',
                        icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                        iconCls: 'my-icon141',
                        handler: function () {
                            var copyText = Ext.getDom('iframeAlert').contentWindow.document.getElementById('alertdetails');
                            copyText.select();
                            navigator.clipboard.writeText(copyText.value);
                            window.open("https://web.whatsapp.com", '_blank');
                        }
                    }],
                listeners: {
                }
            });
        }
        printSlipWindow.doLayout();
        printSlipWindow.show();
        printSlipWindow.center();
    };
    var updateSalesEntry = function (grid_event) {
        var rec = grid_event.record;
        if (parseFloat(rec.data.salesEntry) <= parseFloat(rec.data.openingStockCount) && (parseFloat(rec.data.openingStockCount) > 0)) {
            var wastage = (parseFloat(rec.data.openingStockCount) + parseFloat(rec.data.purchasedItem)) - ((parseFloat(rec.data.salesEntry) + parseFloat(rec.data.damagedItem) + parseFloat(rec.data.returnItem)) + parseFloat(rec.data.closingCount));
            //var wastage = (parseFloat(rec.data.openingStockCount) + parseFloat(rec.data.purchasedItem)) - (parseFloat(rec.data.salesEntry) + parseFloat(rec.data.damagedItem) + parseFloat(rec.data.returnItem)) - parseFloat(rec.data.closingCount);
            if (wastage >= 0) {
                grid_event.record.set('wastageCount', wastage);
            } else {
                grid_event.record.set('salesEntry', 0);
                grid_event.record.set('closingCount', 0);
            }

        } else {
            //grid_event.record.set('salesEntry', 0);
        }


    };
    var updateSaleStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetalineUpdateSale',
                method: 'post'
            }),
            fields: ['rsp_id', 'rsp_uuid', 'rsp_branch', 'rsp_date', 'branchName'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function () {
                }
            }
        });
        store.setDefaultSort('rsp_createdOn', 'DESC');
        return store;
    };

    var retalineUpdateSalesGrid = function (id) {
        var branch_store = updateSaleStore();


        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'branch'
                }
            ]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Manage Sales Update',
            iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch',
                    id: 'branch_name_auto_expsi',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'branchName',
                    tooltip: 'Branch'
                },
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'rsp_date',
                    tooltip: 'Date'
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
                            var rsp_uuid = Ext.getCmp('retalineUpdateSales_main_panel').getSelectionModel().getSelections()[0].data.rsp_uuid;
                            updatSaleActionMenu(e)
                        }
                    }
                }
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
                viewready: onGridResize,
                afterrender: function () {

                },
                selectionchange: gridSelectionChangedupdateSale
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Update Sales',
                    tooltip: 'Update Sales',
                    iconCls: 'add',
                    handler: function () {
                        Application.RetalineGoodPurchaseNote.updateSales();
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
            autoExpandColumn: 'branch_name_auto_expsi'
        });
        return grid_panel;
    };
    var updatSaleActionMenu = function (e) {
        var stockrequestActionMenu = new Ext.menu.Menu({
            items: [{text: "View",
                    handler: function () {
                        var rsp_uuid = Ext.getCmp('retalineUpdateSales_main_panel').getSelectionModel().getSelections()[0].data.rsp_uuid;
                        Application.RetalineGoodPurchaseNote.viewUpdateSales(rsp_uuid);

                    }
                }]
        });
        stockrequestActionMenu.showAt(e.getXY());
    };
    var gridSelectionChangedupdateSale = function () {
        if (!Ext.isEmpty(Ext.getCmp('retalineUpdateSales_main_panel').getSelectionModel().getSelections())) {
            var rdc_status = Ext.getCmp('retalineUpdateSales_main_panel').getSelectionModel().getSelections()[0].data.rdc_status;
            var rdc_id = Ext.getCmp('retalineUpdateSales_main_panel').getSelectionModel().getSelections()[0].data.rdc_id;
        }
    };
    var salesUpdateViewGrid = function (rsp_uuid) {
        var viewTeansfer_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'rdc_ItemName'
                }
            ]
        });
        viewTeansfer_filter.remote = true;
        viewTeansfer_filter.autoReload = true;
        var _salesUpdateViewGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: salesUpdateViewStore(rsp_uuid),
            autoScroll: true,
            width: winsize.width * 0.8,
            plugins: [viewTeansfer_filter],
            height: 250,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelViewactionItemsdata',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    dataIndex: 'rsp_ItemName',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                }, {
                    header: 'Opening stock',
                    dataIndex: 'rsp_opening',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Purchase',
                    dataIndex: 'rsp_purchase',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Sales',
                    dataIndex: 'rsp_sales',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Damage',
                    dataIndex: 'rsp_damage',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Return',
                    dataIndex: 'rsp_return',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Balance',
                    dataIndex: 'rsp_closing',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }, {
                    header: 'Wastage',
                    dataIndex: 'rsp_wastage',
                    hideable: true,
                    sortable: true,
                    align: 'right'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {

                }
            }
        });
        return _salesUpdateViewGridPanel;
    };
    var salesUpdateViewStore = function (rsp_uuid) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listViewUpdateSales',
                method: 'post'
            }),
            fields: ['rsp_id', 'rsp_uuid', 'rsp_branch', 'rsp_date', 'rsp_item', 'rsp_count', 'rsp_transfer', 'rsp_opening', 'rsp_purchase', 'rsp_sales', 'rsp_damage', 'rsp_return', 'rsp_wastage', 'rsp_closing', 'rsp_ItemName'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.rsp_uuid = rsp_uuid;
                }
            }
        });
        return _Store;
    };
    return{
        Cache: {},
        initRetalineGoodReceiveNote: function () {
            var _panelId = 'purchase_order_panel_retgrnisite';
            var _retalinrGRNPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_retalinrGRNPanel)) {
                _retalinrGRNPanel = retalinrGRNPanel(_panelId);
                Application.UI.addTab(_retalinrGRNPanel);
                _retalinrGRNPanel.doLayout();
            } else {
                Application.UI.addTab(_retalinrGRNPanel);
                _retalinrGRNPanel.doLayout();
            }
        },
        newWindowForGRN: function () {
            var grnRetalineForm = grnRetalineCreateForm();
            Application.RetalineGoodPurchaseNote.Cache.poretgrnuisite_updatedon = '';
            var vendorPonewWindow = new Ext.Window({
                id: "windowGRNRetalineCreate",
                title: 'Create Direct Purchase',
                //iconCls: 'vender-items',
                shadow: false,
                width: winsize.width * 0.67,
                height: winsize.height * 0.43,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [grnRetalineForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Save & Close',
                        tabIndex: 925,
                        handler: function () {
                            if ((Ext.getCmp('po_billing_to').getValue() > 0) && (Ext.getCmp('pe_party').getValue() > 0)) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=retGRNSaveClose',
                                    method: 'POST',
                                    params: {
                                        retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet,
                                        retgrn_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                        retgrn_date: Ext.getCmp('po_date').getValue(),
                                        isConfirm: 0
                                    },
                                    success: function (response) {

                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == true) {

                                            Ext.getCmp('windowGRNRetalineCreate').close();
                                            Ext.getCmp('gridpanelRetalineGRNDataview').getStore().load();

                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }

                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
                            }
                        }
                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/arrow_right.png',
                        text: 'Confirm & Close',
                        tabIndex: 924,
                        handler: function () {
                            if (Ext.getCmp('grnRetalineItemGrid').getStore().getCount() > 0) {
                                if ((Ext.getCmp('po_billing_to').getValue() > 0) && (Ext.getCmp('pe_party').getValue() > 0)) {
                                    Ext.MessageBox.confirm('Confirm', 'After confirmation It is not possible to edit the data.', function (btn, text) {
                                        if (btn == 'yes') {
                                            Ext.Ajax.request({
                                                url: modURL + '&op=retGRNSaveClose',
                                                method: 'POST',
                                                params: {
                                                    retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet,
                                                    retgrn_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                                    retgrn_date: Ext.getCmp('po_date').getValue(),
                                                    isConfirm: 1
                                                },
                                                success: function (response) {

                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success == true) {
                                                        Ext.Ajax.request({
                                                            url: modURL + '&op=updateChildStock',
                                                            method: 'POST',
                                                            params: {
                                                                retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet,
                                                                retgrn_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                                                retgrn_date: Ext.getCmp('po_date').getValue(),
                                                            },
                                                            success: function (response) {

                                                                var tmp = Ext.decode(response.responseText);
                                                                if (tmp.success == true) {

                                                                    Ext.getCmp('windowGRNRetalineCreate').close();
                                                                    Ext.getCmp('gridpanelRetalineGRNDataview').getStore().load();

                                                                } else {
                                                                    Ext.MessageBox.alert("Notification", tmp.msg);
                                                                }

                                                            },
                                                            failure: function (response, options) {
                                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                            }
                                                        });


                                                    } else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }

                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        }
                                    });

                                } else {
                                    Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
                                }
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please add the items to proceed');
                            }


                        }
                    }
                ]
            });
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
                    Application.RetalineGoodPurchaseNote.Cache.GRNRet = uid;
                    Ext.getCmp('uniqueId').setValue(uid);
                }
            });
            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        },
        deleteGRNRetaline: function (retgrn_uniqueid) {
            var t = new Date();
            //var retgrn_uniqueid = arguments[0];
            var t_stamp = t.format("YmdHis");
            Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        waitMsg: 'Processing',
                        url: modURL + '&op=deleteGRNDetails',
                        method: 'POST',
                        params: {
                            retgrn_uniqueid: retgrn_uniqueid
                        },
                        success: function (response) {
                            var tmp = Ext.decode(response.responseText);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.example.msg('Success', tmp.message);
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelRetalineGRNDataview'));
                                Ext.getCmp('gridpanelRetalineGRNDataview').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
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
        },
        editPOWindowForVendor: function (uniqueid) {
            var vendornewPOForm = grnRetalineCreateForm(uniqueid);
            var vendorPonewWindow = new Ext.Window({
                id: "windowGRNRetalineCreate",
                title: 'Confirm Direct Purchase',
                iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1180,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [vendornewPOForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Save & Close',
                        tabIndex: 523,
                        handler: function () {
                            Ext.Ajax.request({
                                url: modURL + '&op=retGRNSaveClose',
                                method: 'POST',
                                params: {
                                    retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet,
                                    retgrn_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                    isConfirm: 0
                                },
                                success: function (response) {

                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true) {

                                        Ext.getCmp('windowGRNRetalineCreate').close();
                                        Ext.getCmp('gridpanelRetalineGRNDataview').getStore().load();

                                    } else {
                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                    }

                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                }
                            });

                        }
                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/arrow_right.png',
                        text: 'Confirm',
                        tabIndex: 522,
                        handler: function () {
                            if (Ext.getCmp('grnRetalineItemGrid').getStore().getCount() > 0) {
                                if ((Ext.getCmp('po_billing_to').getValue() > 0) && (Ext.getCmp('pe_party').getValue() > 0)) {
                                    Ext.MessageBox.confirm('Confirm', 'After confirmation It is not possible to edit the data.', function (btn, text) {
                                        if (btn == 'yes') {
                                            Ext.Ajax.request({
                                                url: modURL + '&op=retGRNSaveClose',
                                                method: 'POST',
                                                params: {
                                                    retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet,
                                                    retgrn_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                                    retgrn_date: Ext.getCmp('po_date').getValue(),
                                                    isConfirm: 1
                                                },
                                                success: function (response) {

                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success == true) {

                                                        Ext.Ajax.request({
                                                            url: modURL + '&op=updateChildStock',
                                                            method: 'POST',
                                                            params: {
                                                                retgrnd_uniqueid: Application.RetalineGoodPurchaseNote.Cache.GRNRet,
                                                                retgrn_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                                                retgrn_date: Ext.getCmp('po_date').getValue(),
                                                            },
                                                            success: function (response) {

                                                                var tmp = Ext.decode(response.responseText);
                                                                if (tmp.success == true) {
                                                                    Application.example.msg('Success', tmp.msg);

                                                                    Ext.getCmp('windowGRNRetalineCreate').close();
                                                                    Ext.getCmp('gridpanelRetalineGRNDataview').getStore().load();

                                                                } else {
                                                                    Ext.MessageBox.alert("Notification", tmp.msg);
                                                                }

                                                            },
                                                            failure: function (response, options) {
                                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                            }
                                                        });
                                                    } else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }

                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        }
                                    });

                                } else {
                                    Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
                                }
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please add the items to proceed');
                            }

                        }
                    }
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");

            Ext.Ajax.request({
                url: modURL + '&op=loadGoodReceiveNote',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    uniqueid: uniqueid

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    Ext.getCmp('itemHistory').hide();
                    Ext.getCmp('grnRetalineItemGrid').store.load({
                        params: {
                            retgrnd_uniqueid: uniqueid
                        },
                        callback: function () {
                            Application.RetalineGoodPurchaseNote.Cache.poretgrn_updatedon = tmp.retgrn_updatedon;
                            Application.RetalineGoodPurchaseNote.Cache.GRNRet = uniqueid;
                            Ext.getCmp('uniqueId').setValue(uniqueid);
                            Ext.getCmp('pe_party').setReadOnly(true);
                            Ext.getCmp('pe_party').setHideTrigger(true);
                            Ext.getCmp('pe_party').setValue(tmp.retgrn_vendor);
                            Ext.getCmp('po_billing_to').setValue(tmp.retgrn_billingTo);
                            Ext.getCmp('pe_partyItems').getStore().load({
                                params: {
                                    stpa_id: tmp.retgrn_vendor
                                }
                            });

                        }
                    });

                }
            });
            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        }, printGRNRetaline: function (PoId) {

            var printLabResultsPanel = Application.RetalineGoodPurchaseNote.createprintGRNRetPanel(PoId);
            if (Ext.isEmpty(printRetalinGRNDetaislWindow)) {
                var printRetalinGRNDetaislWindow = new Ext.Window({
                    id: 'printRetalinGRNDetaislWindow',
                    plain: true,
                    modal: true,
                    constrain: true,
                    resizable: false,
                    title: 'Good Receipt Note',
                    width: winsize.width * 0.7,
                    autoHeight: true,
                    items: [printLabResultsPanel],
                    buttons: [{
                            text: 'Cancel',
                            handler: function () {
                                printRetalinGRNDetaislWindow.close();
                            }
                        }, {
                            text: 'Print',
                            handler: function () {
                                var params = {
                                    // order_id: order_id,
                                    action: 1
                                };
                                //updateOrderStatus(params);
                                iframeRequest.focus();
                                iframeRequest.print();
                                printRetalinGRNDetaislWindow.close();
                            }
                        }],
                });
            }
            printRetalinGRNDetaislWindow.doLayout();
            printRetalinGRNDetaislWindow.show();
            printRetalinGRNDetaislWindow.center();
        }, createprintGRNRetPanel: function (PoId) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var src = '?module=retaline_grn&op=printGRNRetDetails&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp + '&PoId=' + PoId;
            var myPanel = new Ext.Panel({
                layout: 'border',
                height: 500,
                id: 'printRetalinGRNPanel',
                items: [{
                        region: 'center',
                        border: false,
                        html: '<iframe src="' + src +
                                '" id="iframeRequest" name="iframeRequest" ' +
                                'width="100%" height="100%" style="border:none">'
                    }]
            });
            return myPanel;
        }, viewGRNDetails: function (poId) {
            var vendornewGRNForm = vendornewGRNViewForm(poId);
            var poViewWindow = new Ext.Window({
                id: "windowFinascopStocknewGRNView",
                title: 'View Direct Purchase',
                //iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1200,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: false,
                closable: false,
                items: [vendornewGRNForm],
                buttons: [{
                        text: 'Close',
                        tabIndex: 519,
                        handler: function () {
                            Ext.getCmp('windowFinascopStocknewGRNView').close();
                        }
                    }
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=getGRNDetails',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    poId: poId

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    console.log(tmp);
                    Ext.getCmp('stpa_Fname').setValue(tmp.stpa_Fname);
                    Ext.getCmp('centralStore').setValue(tmp.centralStore);
                    Ext.getCmp('retgrn_name').setValue(tmp.retgrn_name);
                    Ext.getCmp('retgrn_date').setValue(tmp.retgrn_date);
                    Ext.getCmp('newPOItemGrid').getStore().load({
                        params: {
                            poId: poId
                        }
                    });
                }
            });
            poViewWindow.doLayout();
            poViewWindow.show();
            poViewWindow.center();
        }, printPO: function (PoId) {
            var printLabResultsPanel = Application.RetalineGoodPurchaseNote.createprintPOPanel(PoId);
            if (Ext.isEmpty(printRetalinPODetaislWindow)) {
                var printRetalinPODetaislWindow = new Ext.Window({
                    id: 'printRetalinPODetaislWindow',
                    plain: true,
                    modal: true,
                    constrain: true,
                    resizable: false,
                    title: 'Print Goods Receive Note',
                    width: winsize.width * 0.7,
                    autoHeight: true,
                    items: [printLabResultsPanel],
                    buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            handler: function () {
                                printRetalinPODetaislWindow.close();
                            }
                        }, {
                            text: 'Print',
                            icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                            handler: function () {
                                var params = {
                                    action: 1
                                };
                                iframeRequest.focus();
                                iframeRequest.print();
                                printRetalinPODetaislWindow.close();
                            }
                        }],
                });
            }
            printRetalinPODetaislWindow.doLayout();
            printRetalinPODetaislWindow.show();
            printRetalinPODetaislWindow.center();
        }, createprintPOPanel: function (PoId) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var src = '?module=retaline_grn&op=printGRNDetails&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp + '&PoId=' + PoId;
            var myPanel = new Ext.Panel({
                layout: 'border',
                height: 500,
                id: 'printRetalinPOPanel',
                items: [{
                        region: 'center',
                        border: false,
                        html: '<iframe src="' + src +
                                '" id="iframeRequest" name="iframeRequest" ' +
                                'width="100%" height="100%" style="border:none">'
                    }]
            });
            return myPanel;
        }, initDistributionChart: function () {
            var panelId = 'retalineDistributionChart_main_panel';
            var branch_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(branch_panel)) {
                branch_panel = retalineDistributionChartGrid(panelId);
                Application.UI.addTab(branch_panel);
                branch_panel.doLayout();
            } else {
                Application.UI.addTab(branch_panel);
            }
            return branch_panel;
        }, viewDistributionChartItems: function (rdc_id, branchDesi) {
            var rdc_id = rdc_id;
            var _addnewtransferWindow = new Ext.Window({
                title: 'Distribution Chart | ' + branchDesi,
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 600,
                resizable: false,
                draggable: true,
                closable: true,
                id: 'windowSIView',
                bodyStyle: {"background-color": "white"},
                items: [ViewOnlyDistributionChartGrid(rdc_id)]
            });
            _addnewtransferWindow.doLayout();
            _addnewtransferWindow.show();
            _addnewtransferWindow.center();
        }, initStockAlert: function () {
            var src = '?module=retaline_grn&op=printSendAlertList&stal_branch=0&sa_date=';
            var resultWindow = new Ext.Window({
                id: "windowStockAlert",
                title: 'Stock Alert',
                shadow: false,
                height: 600,
                width: winsize.width * 0.65,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [new Ext.Panel({
                        layout: 'border',
                        height: 500,
                        id: 'printSendAlertPanel',
                        items: [{
                                region: 'center',
                                border: false,
                                html: '<iframe src="' + src +
                                        '" id="iframeAlert" name="iframeAlert" ' +
                                        'width="100%" height="100%" allow="clipboard-read; clipboard-write" style="border:none">'
                            }]
                    })],
                tbar: [{'html': '&nbsp;&nbsp;'}, {
                        xtype: 'datefield',
                        fieldLabel: 'Date',
                        id: 'sa_date',
                        name: 'sa_date',
                        anchor: '97%',
                        allowBlank: false,
                        tabIndex: 830,
                        format: "d-m-Y",
                        maxValue: new Date(),
                        value: new Date(),
                        listeners: {
                            change: function () {
                                var stal_branch = Ext.getCmp('stal_branch').getValue();
                                var sa_date = Ext.getCmp('sa_date').getValue();
                                Ext.get('iframeAlert').dom.src = '?module=retaline_grn&op=printSendAlertList&stal_branch=' + stal_branch + '&sa_date=' + sa_date;
                            }
                        }
                    }, {'html': '<b>&nbsp;Choose Source:&nbsp;</b>'}, {
                        xtype: 'combo',
                        mode: 'local',
                        fieldLabel: 'Base Station',
                        typeAhead: true,
                        editable: true,
                        emptyText: 'Select',
                        anchor: '100%',
                        store: branchstore('pjStk'),
                        id: 'stal_branch',
                        triggerAction: 'all',
                        displayField: 'br_Name',
                        allowBlank: false,
                        valueField: 'br_ID',
                        hiddenName: 'stal_branch',
                        name: 'stal_branch',
                        minChars: 1,
                        listeners: {
                            select: function () {
                                var stal_branch = Ext.getCmp('stal_branch').getValue();
                                var sa_date = Ext.getCmp('sa_date').getValue();
                                Ext.get('iframeAlert').dom.src = '?module=retaline_grn&op=printSendAlertList&stal_branch=' + stal_branch + '&sa_date=' + sa_date;
//                                Ext.getCmp('gridpanelStockAlertItem').getStore().baseParams.stal_branch = Ext.getCmp('stal_branch').getValue();
//                                Ext.getCmp('gridpanelStockAlertItem').getStore().baseParams.sa_date = Ext.getCmp('sa_date').getValue();
//                                Ext.getCmp('gridpanelStockAlertItem').getStore().load();
                            }
                        }
                    }, ],
                bbar: ['->',
                    {
                        text: 'Print',
                        handler: function () {
                            iframeAlert.focus();
                            iframeAlert.print();
                        }

                    },
                    {
                        text: 'Send Alert',
                        handler: function () {
                            var copyText = Ext.getDom('iframeAlert').contentWindow.document.getElementById('alertdetails');
                            //copyText.select();
                            //navigator.clipboard.writeText(copyText.value);
                            window.open("https://web.whatsapp.com", '_blank');
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
        }, updateSellingPrice: function () {

            var resultWindow = new Ext.Window({
                id: "windowFinascopStockAddvenderitemCreatevendoritem",
                title: 'Fish Mart Selling Price',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [selectedPrductsGrid()],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        handler: function () {
                            Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();

                        }

                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Save',
                        handler: function () {
                            var spUpdatedItems = getMigratedData('gridpanelUpdateSellingPrice');
                            Ext.Ajax.request({
                                url: modURL,
                                params: {
                                    op: 'updateSellingPrice',
                                    uuid: Application.RetalineGoodPurchaseNote.Cache.updateUUID,
                                    spUpdatedItems: spUpdatedItems,
                                    search_baseStation: Ext.getCmp('search_baseStation').getValue()
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();
                                        Application.RetalineGoodPurchaseNote.Cache.updateUUID = '';
                                    }
                                }
                            });

                        }

                    }

                ], listeners: {
                    afterrender: function () {

                    }

                }

            });
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
                    Application.RetalineGoodPurchaseNote.Cache.updateUUID = uid;
                }
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        }, updateSales: function () {

            var resultWindow = new Ext.Window({
                id: "windowBranchSalesUpdate",
                title: 'Sales Update',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                closable: true,
                modal: true,
                items: [salesPrductsGrid()],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Save & Close',
                        handler: function () {
                            var spUpdatedItems = getMigratedData('gridpanelUpdateSales');
                            Ext.Ajax.request({
                                url: modURL,
                                params: {
                                    op: 'updateSales',
                                    uuid: Application.RetalineGoodPurchaseNote.Cache.updateSalesUUID,
                                    spUpdatedItems: spUpdatedItems,
                                    search_baseStation: Ext.getCmp('search_allBranch').getValue(),
                                    sales_dateview: Ext.getCmp('sales_dateview').getValue(),
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        Ext.Ajax.request({
                                            url: modURL + '&op=updateStock',
                                            method: 'POST',
                                            params: {
                                                uuid: Application.RetalineGoodPurchaseNote.Cache.updateSalesUUID,
                                                spUpdatedItems: spUpdatedItems,
                                                search_baseStation: Ext.getCmp('search_allBranch').getValue(),
                                                sales_dateview: Ext.getCmp('sales_dateview').getValue(),
                                            },
                                            success: function (response) {

                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success == true) {

                                                    Application.example.msg('Success', tmp.msg);
                                                    Ext.getCmp('windowBranchSalesUpdate').close();
                                                    Application.RetalineGoodPurchaseNote.Cache.updateSalesUUID = '';
                                                    Ext.getCmp('retalineUpdateSales_main_panel').getStore().load();

                                                } else {
                                                    Ext.MessageBox.alert("Notification", tmp.msg);
                                                }

                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            }
                                        });

                                    }
                                }
                            });

                        }

                    }

                ], listeners: {
                    afterrender: function () {

                    }

                }

            });
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
                    Application.RetalineGoodPurchaseNote.Cache.updateSalesUUID = uid;
                }
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        }, initUpdateSales: function () {
            var panelId = 'retalineUpdateSales_main_panel';
            var updatSale_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(updatSale_panel)) {
                updatSale_panel = retalineUpdateSalesGrid(panelId);
                Application.UI.addTab(updatSale_panel);
                updatSale_panel.doLayout();
            } else {
                Application.UI.addTab(updatSale_panel);
            }
            return updatSale_panel;
        }, viewUpdateSales: function (rdc_id, branchDesi) {
            var rdc_id = rdc_id;
            var _addnewtransferWindow = new Ext.Window({
                title: 'View Sale Details',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: winsize.width * 0.8,
                resizable: false,
                draggable: true,
                closable: true,
                id: 'windowSIView',
                bodyStyle: {"background-color": "white"},
                items: [salesUpdateViewGrid(rdc_id)]
            });
            _addnewtransferWindow.doLayout();
            _addnewtransferWindow.show();
            _addnewtransferWindow.center();
        },
    }
}();