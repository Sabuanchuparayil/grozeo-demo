Application.Finascop_Contract_PO = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 19;
    var partytype = 0;
    var modURL = '?module=finascop_contract_po';
    var pomodURL = '?module=finascop_purchase_order';
    var dateRange = parseInt(BETWEEN_DATES_TYPE_A);
    var total_mrp = 0;
    var onGridResize = function (cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    };
    var contractPurchaseOrderPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id,
            title: 'Rate Contract',
            iconCls: 'finascop_invoice',
            items: [PurchaseOrderContractGrid()]
        });
        return panel;
    };

    var PurchaseOrderContractGrid = function () {

        var _PoGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fcpo_name'
                }, {
                    type: 'string',
                    dataIndex: 'fcpo_createdon'
                }, {
                    type: 'string',
                    dataIndex: 'vendorName'
                }]
        });
        _PoGridFilter.remote = true;
        _PoGridFilter.autoReload = true;
        var _gridStore = PurchaseOrderAdhocGridStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'gridpanelforConratctPO',
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
                    text: 'Create Rate Contract',
                    tooltip: 'Create Rate Contract',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        contractVendorWindow();
                        //Application.Finascop_Contract_PO.newPOWindowForVendor();
                    }
                }],
            plugins: [new Ext.ux.grid.GroupSummary(), _PoGridFilter],
            columns: [
                {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'vendorName',
                    tooltip: 'Vendor',
                    hideable: false
                },
                {
                    header: 'CPR Number',
                    sortable: true,
                    dataIndex: 'fcpo_name',
                    tooltip: 'CPR Number',
                    hideable: false
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'fcpo_createdon',
                    tooltip: 'CPR Date'
                }, {
                    header: 'Last Updated On',
                    sortable: true,
                    dataIndex: 'fcpo_updatedon',
                    tooltip: 'Last Updated On'
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
                            rcActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 hideable: false,
                 sortable: false,
                 items: [{
                 getClass: function (v, meta, rec) {
                 // return 'my-icon109';
                 if (rec.get('fcpo_status') == 0) {
                 return 'approve-reject';
                 } else {
                 return 'hideicon';
                 }
                 
                 },
                 tooltip: 'Edit Rate Contract',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var t = new Date();
                 var t_stamp = t.format("YmdHis");
                 Application.Finascop_Contract_PO.Cache.Pouid = record.get('fcpo_uniqueid');
                 Application.Finascop_Contract_PO.editPOWindowForVendor(record.get('fcpo_uniqueid'));
                 }
                 }, {
                 getClass: function (v, meta, rec) {
                 // return 'my-icon109';
                 if (rec.get('fcpo_status') == 1) {
                 return 'viewfile';
                 } else {
                 return 'hideicon';
                 }
                 
                 },
                 tooltip: 'View Rate Contract',
                 handler: function (grid, rowIndex, colIndex) {
                 var fcpo_id = Ext.getCmp('gridpanelforConratctPO').getSelectionModel().getSelections()[0].data.fcpo_id;
                 Application.Finascop_Contract_PO.viewCPODetails(fcpo_id);
                 }
                 }, {
                 getClass: function (v, meta, rec) {
                 // return 'my-icon109';
                 if (rec.get('fcpo_status') == 0) {
                 return 'wrong';
                 } else {
                 return 'hideicon';
                 }
                 
                 },
                 tooltip: 'DELETE',
                 handler: function () {
                 var fcpo_uniqueid = Ext.getCmp('gridpanelforConratctPO').getSelectionModel().getSelections()[0].data.fcpo_uniqueid;
                 Application.Finascop_Contract_PO.deleteContractPO(fcpo_uniqueid);
                 }
                 }
                 ]
                 }*/
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedadhoc
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
    var rcActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View Rate Contract",
                handler: function () {
                    var fcpo_id = Ext.getCmp('gridpanelforConratctPO').getSelectionModel().getSelections()[0].data.fcpo_id;
                    Application.Finascop_Contract_PO.viewCPODetails(fcpo_id);
                }
            }]
    });

    var gridSelectionChangedadhoc = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelforConratctPO').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelforConratctPO').getSelectionModel().getSelections()[0].data.fcpo_id;
        }
    };
    var PurchaseOrderAdhocGridStore = function () {
        var _catStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getPurchaseOrderContract',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'adhoc_uniqueid',
                root: 'data'
            }, ['fcpo_uniqueid', 'fcpo_name', 'fcpo_createdon', 'vendorName', 'fcpo_status', 'fcpo_id', 'fcpo_updatedon']),
            sortInfo: {
                field: 'adhoc_createdon',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelforConratctPO').getSelectionModel().selectRow(0);
                }
            }
        });
        return _catStore;
    };
    var vendornewPOCreateForm = function () {
        var purUni = '';
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'newPOFormForVendor',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            xtype: 'hidden', id: 'uniqueId', name: 'uniqueId'
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            labelAlign: 'left',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Vendor',
                                    hideLabel: true,
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
                                    tabIndex: 902,
                                    listeners: {
                                        select: function () {
                                            var pe_party = Ext.getCmp('pe_party').getValue();
                                            Ext.getCmp('pe_partyItems').reset();
                                            Ext.getCmp('pe_partyItems').getStore().load({
                                                params: {
                                                    stpa_id: pe_party
                                                }
                                            });
                                            Ext.getCmp('po_payment_terms').getStore().load({
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
                                                    Ext.getCmp('vendorCity').setValue(tmp.stpa_City);
                                                    Ext.getCmp('vendorPinc').setValue(tmp.stpa_PINCODE);
                                                    Ext.getCmp('vendorMobile').setValue(tmp.stpa_MobileNo);
                                                    Ext.getCmp('vendorGST').setValue(tmp.stpa_GSTIN);
                                                    Ext.getCmp('vendorVisfreq').setValue(tmp.stpa_visitFrequency);
                                                    Ext.getCmp('vendorOncall').setValue(tmp.stpa_oncallDelivery);
                                                },
                                                failure: function () {
                                                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                }
                                            });
                                        },
                                        focus: function () {
                                            var pe_party = Ext.getCmp('pe_party').getValue();
                                            Ext.getCmp('pe_partyItems').reset();
                                            Ext.getCmp('pe_partyItems').getStore().load({
                                                params: {
                                                    stpa_id: pe_party
                                                }
                                            });
                                            Ext.getCmp('po_payment_terms').getStore().load({
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
                                                    Ext.getCmp('vendorCity').setValue(tmp.stpa_City);
                                                    Ext.getCmp('vendorPinc').setValue(tmp.stpa_PINCODE);
                                                    Ext.getCmp('vendorMobile').setValue(tmp.stpa_MobileNo);
                                                    Ext.getCmp('vendorGST').setValue(tmp.stpa_GSTIN);
                                                    Ext.getCmp('vendorVisfreq').setValue(tmp.stpa_visitFrequency);
                                                    Ext.getCmp('vendorOncall').setValue(tmp.stpa_oncallDelivery);
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
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'CPR Number',
                                    id: 'po_number',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_number]',
                                    anchor: '97%',
                                    value: 'Not Genereated',
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
                                    fieldLabel: 'Date',
                                    id: 'po_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    value: 'Not Genereated',
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 65,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Ordered By',
                                    id: 'po_ordered_by',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_ordered_by]',
                                    anchor: '97%',
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
                                    fieldLabel: 'Pincode',
                                    id: 'vendorPinc',
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
                                    fieldLabel: 'Tax',
                                    id: 'vendorGST',
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
                            columnWidth: .30,
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
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'HSN',
                                    id: 'itemHSN',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Tax',
                                    id: 'itemGST',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%',
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Last Purchase',
                                    id: 'itemLP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'LP Price',
                                    id: 'itemLPP',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Optimum Qty',
                                    id: 'itemOptimumQty',
                                    hidden: true,
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 35,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Min Qty',
                                    id: 'itemMinQty',
                                    hidden: true,
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .10,
                            labelAlign: 'top',
                            labelWidth: 55,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Item Count',
                                    id: 'itemCSCount',
                                    hidden: true,
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }]
                }, {
                    layout: 'column',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .35,
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
                        }, {
                            layout: 'column',
                            columnWidth: 1,
                            hidden: true,
                            items: [{
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .30,
                                    labelAlign: 'top',
                                    hideLabel: 'true',
                                    items: [{
                                            xtype: 'radio',
                                            name: 'isStandardPacking',
                                            id: 'isStandardPacking',
                                            inputValue: 1,
                                            boxLabel: 'Apply Standard Packing',
                                            listeners: {
                                                check: function (field, new_value) {
                                                    if (new_value == true) {
                                                        Application.Finascop_Contract_PO.Cache.isStandardPacking = 1;
                                                        Ext.getCmp('isNotStandardPacking').reset();
                                                    }
                                                }
                                            }

                                        }]
                                }, {
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .30,
                                    labelAlign: 'top',
                                    hideLabel: 'true',
                                    items: [{
                                            xtype: 'radio',
                                            name: 'isNotStandardPacking',
                                            id: 'isNotStandardPacking',
                                            inputValue: 2,
                                            boxLabel: 'Do Not Apply Standard Packing',
                                            listeners: {
                                                check: function (field, new_value) {
                                                    if (new_value == true) {
                                                        Application.Finascop_Contract_PO.Cache.isStandardPacking = 2;
                                                        Ext.getCmp('isStandardPacking').reset();
                                                    }
                                                }
                                            }

                                        }]
                                }]

                        }, {
                            layout: 'column',
                            style: 'margin-bottom:3px;',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.35,
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
                                            tabIndex: 904,
                                            listeners: {
                                                select: function () {
                                                    var pe_partyItems = Ext.getCmp('pe_partyItems').getValue();
                                                    if (pe_partyItems > 0) {
                                                        Ext.Ajax.request({
                                                            url: modURL + '&op=itemHistory',
                                                            method: 'POST',
                                                            params: {pe_partyItems: pe_partyItems, po_billing_to: 0, isStandardPacking: Application.Finascop_Contract_PO.Cache.isStandardPacking},
                                                            success: function (res) {
                                                                var tmp = Ext.decode(res.responseText);
                                                                console.log('itemHistory', tmp);
                                                                if (tmp.csb_package_type_name != '') {
                                                                    purUni = ' - ' + tmp.csb_package_type_name;
                                                                } else {
                                                                    purUni = '';
                                                                }
                                                                Ext.getCmp('itemHistory').show();
                                                                Ext.getCmp('itemHSN').setValue(tmp.stit_HSN_code);
                                                                Ext.getCmp('itemLP').setValue(tmp.last_mrp);
                                                                Ext.getCmp('itemGST').setValue(tmp.stit_GST);
                                                                Application.Finascop_Contract_PO.Cache.stit_GST = tmp.stit_GST;
                                                                Ext.getCmp('itemLPP').setValue(tmp.last_sp);
                                                                Ext.getCmp('itemOptimumQty').setValue(tmp.optiQty);
                                                                Ext.getCmp('itemCSCount').setValue(tmp.itemCSCount);
                                                                Ext.getCmp('itemMinQty').setValue(tmp.minQty);
                                                                Ext.getCmp('isRRPApplicable').setValue(tmp.isRRPApplicable);
                                                                //Ext.getCmp('itemUnit').setValue(tmp.csb_package_type_name);
                                                                Ext.getCmp('itemUnitForm').setValue(tmp.itemUnitForm);
                                                                Ext.getCmp('itemUnitFormdet').setValue(tmp.itemUnitForm);
                                                                Ext.getCmp('itemSmaalStockUnit').setValue(tmp.itemSmaalStockUnit);
                                                                Application.Finascop_Contract_PO.Cache.stit_fixedB2BRates = tmp.stit_fixedB2BRates;
                                                            },
                                                            failure: function () {
                                                                Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                            }
                                                        });
                                                    }


                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Unit',
                                            id: 'itemUnitForm',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 911,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.15,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Offer Rate',
                                            align: 'right',
                                            id: 'fpot_itemoffrrate',
                                            name: 'n[fpot_itemoffrrate]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 905,
                                            value: 0,
                                            listeners: {
                                                change: function (field) {
                                                    Ext.getCmp('po_gstType').reset();
                                                    Ext.getCmp('fpot_itemoffrrateExcT').reset();

                                                    var itemmrp = Ext.getCmp('fpot_itemmrp').getValue();
                                                    var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    if (itemmrp > 0) {
                                                        if (fpot_itemoffrrate < itemmrp) {
                                                            var fpot_itemqty = Ext.getCmp('fpot_itemqty').getValue();
                                                            var amount = fpot_itemqty * fpot_itemoffrrate;
                                                            Ext.getCmp('fpot_amount').setValue(amount);
                                                            console.log('e', e);
                                                        } else {
                                                            Ext.getCmp('fpot_itemoffrrate').reset();
                                                            Ext.MessageBox.alert("Notification", "Offer Rate should be less than MRP");
                                                        }
                                                    }

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.15,
                                    labelAlign: 'top',
                                    items: [{
                                            id: 'isRRPApplicable',
                                            xtype: 'hidden',
                                            name: 'isRRPApplicable'
                                        }, {
                                            xtype: 'numberfield',
                                            fieldLabel: 'MRP/RRP',
                                            id: 'fpot_itemmrp',
                                            align: 'right',
                                            name: 'n[fpot_itemmrp]',
                                            anchor: '98%',
                                            //allowBlank: false,
                                            tabIndex: 906,
                                            listeners: {
                                                change: function (field) {
                                                    var itemmrp = Ext.getCmp('fpot_itemmrp').getValue();
                                                    var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    if (fpot_itemoffrrate < itemmrp) {
                                                        var fpot_itemqty = Ext.getCmp('fpot_itemqty').getValue();
                                                        var amount = fpot_itemqty * fpot_itemoffrrate;
                                                        Ext.getCmp('fpot_amount').setValue(amount);
                                                        console.log('e', e);
                                                    } else {
                                                        Ext.getCmp('fpot_itemoffrrate').reset();
                                                        Ext.MessageBox.alert("Notification", "Offer Rate should be less than MRP");
                                                    }
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'name',
                                            valueField: 'id',
                                            mode: 'local',
                                            id: 'po_gstType',
                                            forceSelection: true,
                                            fieldLabel: 'Tax',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 907,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'Inclusive', name: 'Incl.'}, {id: 'Exclusive', name: 'Excl.'}]
                                            }), listeners: {
                                                select: function (combo, record) {
                                                    var offerRateET;
                                                    var offerRate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    if (record.data.id == 'Inclusive') {
                                                        offerRateET = (offerRate * 100) / (100 + parseFloat(Application.Finascop_Contract_PO.Cache.stit_GST));
                                                    } else {
                                                        offerRateET = offerRate;
                                                    }
                                                    offerRateET = Math.round((offerRateET + Number.EPSILON) * 100) / 100;
                                                    Ext.getCmp('fpot_itemoffrrateExcT').setValue(offerRateET);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.15,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Effective Rate',
                                            align: 'right',
                                            id: 'fpot_itemoffrrateExcT',
                                            name: 'n[fpot_itemoffrrateExcT]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 908,
                                            readOnly: true
                                        }]
                                }]
                        }, {
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .25,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Gift Name',
                                            id: 'fpot_giftname',
                                            name: 'n[fpot_giftname]',
                                            anchor: '98%',
                                            tabIndex: 915
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            align: 'right',
                                            fieldLabel: 'Gift Quantity',
                                            id: 'fpot_giftqty',
                                            name: 'n[fpot_giftqty]',
                                            anchor: '98%',
                                            tabIndex: 916
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    style: 'margin-bottom:3px;',
                                    columnWidth: .60,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Notes',
                                            id: 'fpot_notes',
                                            name: 'n[fpot_notes]',
                                            anchor: '98%',
                                            tabIndex: 917
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'button',
                                            text: 'Save',
                                            tabIndex: 918,
                                            id: 'addItemsToPO',
                                            iconCls: 'finascop_add',
                                            style: 'margin-top:17px;',
                                            handler: function () {
                                                var isRRPApplicable = Ext.getCmp('isRRPApplicable').getValue();
                                                if ((isRRPApplicable == 0) && (Ext.isEmpty(Ext.getCmp('fpot_itemmrp').getValue()))) {
                                                    Ext.MessageBox.alert('Notification', 'Enter MRP for the item');
                                                } else {
                                                    if (Ext.isEmpty(Ext.getCmp('fpot_itemmrp').getValue())) {
                                                        Application.Finascop_Contract_PO.Cache.isRRP = 1;
                                                        setItemMRPRRP(0, 0);
                                                    } else {
                                                        Application.Finascop_Contract_PO.Cache.isRRP = 0;
                                                        addItemtoPO(0, 0);
                                                    }
                                                }


                                            }
                                        }]
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
                                        contractPOItemGrid()
                                    ]
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'ptc_name',
                                    valueField: 'ptc_id',
                                    mode: 'local',
                                    id: 'po_payment_terms',
                                    forceSelection: true,
                                    fieldLabel: 'Payment Terms',
                                    emptyText: 'Payment Terms',
                                    anchor: '98%',
                                    allowBlank: false,
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 920,
                                    store: pmtTermsStore,
                                    listeners: {
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    fieldLabel: 'PO Validity',
                                    xtype: 'datefield',
                                    text: 'PO Validity',
                                    id: 'po_payment_value',
                                    name: 'po_payment_value',
                                    hiddenName: 'po_payment_value',
                                    anchor: '90%',
                                    displayField: 'po_payment_value',
                                    tabIndex: 503,
                                    format: 'Y-m-d',
                                    minValue: new Date()
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'checkbox',
                                    hidden: true,
                                    name: 'po_delivered_byvendor',
                                    id: 'po_delivered_byvendor',
                                    boxLabel: 'Delivered by Vendor',
                                    inputValue: 1


                                }
                            ]
                        }
                    ]
                }]
        });
        return panel;
    };
    var vendorStore = new Ext.data.JsonStore({
        fields: ['stpa_id', 'stpa_Fname'],
        url: modURL + '&op=getVendorName',
        autoLoad: true,
        method: 'post',
    });
    var vendorItemStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: pomodURL + '&op=vendorItemStore',
        method: 'post',
        fields: ['stit_ID', 'stit_SKU']
    });
    var pmtTermsStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: pomodURL + '&op=getPaymentTerms',
        method: 'post',
        fields: ['ptc_id', 'ptc_name'],
        root: 'data'
    });
    var contractPOItemGrid = function () {

        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listContractPodetailsStore',
                    method: 'post'
                }),
                fields: ['fcpod_vendorid', 'fcpod_itemid', 'fcpod_itemname', 'fcpod_itemmrp', 'fcpod_itemoffrrate', 'fcpod_itemoffrrateet', 'fcpod_giftname', 'fcpod_giftqty', 'fcpod_notes', 'fcpod_purchasingUnit',
                    'fcpod_leastSKUmrp', 'isStandardPacking', 'leastSKU', 'fcpod_customerRateHmDel', 'fcpod_customerRateCouDel'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.fpot_uniqueid = Application.Finascop_Contract_PO.Cache.POID;
                    }
                }

            });
            return itemStore;
        };
        var cpoprdfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fcpod_itemname'
                }]
        });
        cpoprdfilters.remote = true;
        cpoprdfilters.autoReload = true;
        var itemGridStore = PurchaseOrderItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'contractPOItemGrid',
            loadMask: true,
            plugins: [cpoprdfilters],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: purchaseItemOrderColModel(0),
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
    var purchaseItemOrderColModel = function (arguments)
    {
        console.log('arguments', arguments);
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'fcpod_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'Unit',
                sortable: true,
                hideable: false,
                dataIndex: 'leastSKU',
                tooltip: 'Unit',
                width: 60

            }, {
                header: 'Offer Rate',
                sortable: true,
                dataIndex: 'fcpod_itemoffrrate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Offer Rate',
                align: 'right',
                width: 60
            }, {
                header: 'Effective Rate',
                sortable: true,
                dataIndex: 'fcpod_itemoffrrateet',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Effective Rate',
                align: 'right',
                width: 60
            }, {
                header: 'MRP/RRP',
                sortable: true,
                hidden: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fcpod_itemmrp',
                align: 'right',
                tooltip: 'MRP/RRP (Inc. of GST)',
                width: 60
            }, {
                header: 'MRP/RRP',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fcpod_leastSKUmrp',
                align: 'right',
                tooltip: 'MRP/RRP (Inc. of GST)',
                width: 60
            },
            {
                xtype: 'actioncolumn',
                hideable: false,
                width: 25,
                items: [{
                        iconCls: 'finascop_delete',
                        tooltip: 'Remove Item',
                        handler: function (grid, rowIndex, colIndex) {
                            var record = grid.store.getAt(rowIndex);
                            Ext.Msg.confirm('Notification', 'Do you really want to remove this item?', function (btn) {
                                if (btn == 'yes') {
                                    removeFromItemStore(grid, rowIndex, record);
                                }
                            });
                        }
                    }/*, {
                     iconCls: 'edit',
                     tooltip: 'Edit Item',
                     handler: function (grid, rowIndex, colIndex) {
                     var record = grid.store.getAt(rowIndex);
                     editFromItemStore(grid, rowIndex, record);
                     }
                     }*/]
            }
        ]);
    };
    var setItemMRPRRP = function (fpot_pts, fpot_ptr) {
        if (Ext.isEmpty(fpot_pts)) {
            fpot_pts = 0;
        }
        if (Ext.isEmpty(fpot_ptr)) {
            fpot_ptr = 0;
        }
        var offerRateET, offerRateIT;
        var offerRate = Ext.getCmp('fpot_itemoffrrate').getValue();
        var po_gstType = Ext.getCmp('po_gstType').getValue();
        if (po_gstType == 'Inclusive') {
            offerRateIT = offerRate;
            offerRateET = (offerRate * 100) / (100 + parseFloat(Application.Finascop_Contract_PO.Cache.stit_GST));
        } else {
            offerRateET = offerRate;
            offerRateIT = offerRate + (offerRate * Application.Finascop_Contract_PO.Cache.stit_GST / 100);
        }
        var pe_partyItems = Ext.getCmp('pe_partyItems').getValue();
        if (pe_partyItems > 0 && offerRateIT > 0) {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                method: 'POST',
                url: pomodURL + '&op=updateRRPValue',
                params: {
                    fpot_itemoffrrate: offerRateIT,
                    pe_partyItems: pe_partyItems
                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Ext.MessageBox.confirm('Confirm', 'Calculated RRP is ' + tmp.rrpValue + ',Do you want to proceed ?', function (btn) {
                            if (btn == 'yes') {
                                Ext.getCmp('fpot_itemmrp').setValue(tmp.rrpValue);
                                addItemtoPO(fpot_pts, fpot_ptr);
                            }
                        });

                    } else {
                        Ext.MessageBox.alert('Error', tmp.msg);
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert('Error', "Check the data entered");
        }

    };
    var addItemtoPO = function (fpot_pts, fpot_ptr) {
        if (Ext.getCmp('fpot_itemoffrrate').getValue() > 0 && Ext.getCmp('fpot_itemoffrrateExcT').getValue() > 0) {

            Ext.Ajax.request({
                waitMsg: 'Processing',
                method: 'POST',
                url: modURL + '&op=saveItemPODetails',
                params: {
                    fpot_itempts: fpot_pts,
                    fpot_itemptr: fpot_ptr,
                    isStandardPacking: Application.Finascop_Contract_PO.Cache.isStandardPacking,
                    fpot_uniqueid: Application.Finascop_Contract_PO.Cache.Pouid,
                    fpot_vendorid: Ext.getCmp('pe_party').getValue(),
                    fpot_itemid: Ext.getCmp('pe_partyItems').getValue(),
                    fpot_itemname: Ext.getCmp('pe_partyItems').getRawValue(),
                    fpot_itemmrp: Ext.getCmp('fpot_itemmrp').getValue(),
                    fpot_itemoffrrate: Ext.getCmp('fpot_itemoffrrate').getValue(),
                    fpot_giftqty: Ext.getCmp('fpot_giftqty').getValue(),
                    fpot_giftname: Ext.getCmp('fpot_giftname').getValue(),
                    fpot_notes: Ext.getCmp('fpot_notes').getValue(),
                    adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                    po_delivered_byvendor: Ext.getCmp('po_delivered_byvendor').getValue(),
                    adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                    po_gstType: Ext.getCmp('po_gstType').getValue(),
                    fpot_itemoffrrateExcT: Ext.getCmp('fpot_itemoffrrateExcT').getValue(),
                    fpot_gstValue: Application.Finascop_Contract_PO.Cache.fpot_gstValue,
                    fpot_isRRP: Application.Finascop_Contract_PO.Cache.isRRP
                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true)
                    {
                        Application.example.msg('Success', tmp.msg);
                        Application.Finascop_Contract_PO.Cache.poadhoc_updatedon = tmp.date;
                        Ext.getCmp('itemHistory').hide();
                        Ext.getCmp('contractPOItemGrid').store.load({
                            params: {
                                fpot_uniqueid: Application.Finascop_Contract_PO.Cache.Pouid
                            },
                            callback: function () {
                                console.log('focus1');
                                Ext.getCmp('pe_partyItems').reset();
                                Ext.getCmp('fpot_itemmrp').reset();
                                Ext.getCmp('fpot_itemoffrrate').reset();
                                Ext.getCmp('fpot_giftqty').reset();
                                Ext.getCmp('fpot_giftname').reset();
                                Ext.getCmp('fpot_notes').reset();
                                Ext.getCmp('po_gstType').reset();
                                Ext.getCmp('fpot_itemoffrrateExcT').reset();
                                Application.Finascop_Contract_PO.Cache.isStandardPacking = 0;
                                Ext.getCmp('itemUnitForm').reset();
                                Ext.getCmp('isStandardPacking').reset();
                                Ext.getCmp('isNotStandardPacking').reset();
                                Ext.getCmp('itemSmaalStockUnit').reset();

                            }
                        });
                        Ext.getCmp('contractPOItemGrid').getStore().load({
                            params: {
                                fpot_uniqueid: Application.Finascop_Contract_PO.Cache.Pouid
                            }
                        });
                    } else
                    {
                        Ext.MessageBox.alert("Failure", "Error moving Data, Kindly reload.");
                        Ext.getCmp('windowFinascopStocknewPOCreate').close();
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.Msg.alert("Notification.", 'Please fill all the fields');
        }
    };
    var removeFromItemStore = function (grid, indx, record)
    {

        Ext.Ajax.request({
            url: modURL + '&op=deleteItemFromContractPO',
            method: 'POST',
            params: {
                itemid: record.get('fcpod_itemid'),
                uid: Application.Finascop_Contract_PO.Cache.Pouid
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true)
                {
                    Application.example.msg('Success', 'Removed item');
                    Ext.getCmp('contractPOItemGrid').getStore().load({
                        params: {
                            fpot_uniqueid: Application.Finascop_Contract_PO.Cache.Pouid
                        }
                    });
//                    function (btn) {
//                        Ext.getCmp('contractPOItemGrid').getStore().load({
//                            params: {
//                                fpot_uniqueid: Application.Finascop_Contract_PO.Cache.Pouid
//                            }
//                        });
//                    });
                } else
                {
                    Ext.MessageBox.alert('Failed');
                }
            }
        });
    };
    var vendorContractPOViewForm = function (poId) {

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
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 42,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Vendor',
                                    id: 'vendor_name',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 1
                                }]
                        },
                        {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'CPR Number',
                                    id: 'vpo_number',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 1
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .30,
                            labelAlign: 'left',
                            labelWidth: 100,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'CPR Date',
                                    id: 'vpo_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    anchor: '98%',
                                    tabIndex: 2
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .30,
                            labelAlign: 'left',
                            labelWidth: 53,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Branch',
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
                                        viewPOItemGrid(poId)
                                    ]
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.15,
                            hideBorders: true,
                            labelAlign: 'left',
                            labelWidth: 90,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Payment Terms',
                                    id: 'po_payment_terms',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            hideBorders: true,
                            labelAlign: 'left',
                            labelWidth: 65,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'PO Validity',
                                    id: 'fcpo_validDate',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }
                            ]
                        }
                    ]
                }]
        });
        return panel;
    };
    var viewPOItemGrid = function (poId) {

        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listContractPodetailsStore',
                    method: 'post'
                }),
                fields: ['fcpod_vendorid', 'fcpod_itemid', 'fcpod_itemname', 'fcpod_itemmrp', 'fcpod_itemoffrrate', 'fcpod_itemoffrrateet', 'fcpod_giftname', 'fcpod_giftqty', 'fcpod_notes', 'fcpod_purchasingUnit',
                    'fcpod_leastSKUmrp', 'isStandardPacking', 'fcpod_customerRateHmDel', 'fcpod_customerRateCouDel'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.fpot_uniqueid = Application.Finascop_Contract_PO.Cache.POID;
                    }
                }

            });
            return itemStore;
        };
        var cpoprdfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fcpod_itemname'
                }]
        });
        cpoprdfilters.remote = true;
        cpoprdfilters.autoReload = true;
        var itemGridStore = PurchaseOrderItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'newPOItemGrid',
            loadMask: true,
            plugins: [cpoprdfilters],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: viewpurchaseItemOrderColModel(1),
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
    var viewpurchaseItemOrderColModel = function (arguments)
    {
        console.log('arguments', arguments);
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'fcpod_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'Offer Rate',
                sortable: true,
                dataIndex: 'fcpod_itemoffrrate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Offer Rate',
                align: 'right',
                width: 60
            }, {
                header: 'Effective Rate',
                sortable: true,
                dataIndex: 'fcpod_itemoffrrateet',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Effective Rate',
                align: 'right',
                width: 60
            }, {
                header: 'MRP/RRP',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fcpod_itemmrp',
                align: 'right',
                tooltip: 'MRP/RRP (Inc. of GST)',
                width: 60
            }, {
                header: 'SKU MRP/RRP',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fcpod_leastSKUmrp',
                align: 'right',
                tooltip: 'SKU MRP/RRP (Inc. of GST)',
                width: 60
            }, {
                header: 'Selling Price(HD)',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fcpod_customerRateHmDel',
                align: 'right',
                tooltip: 'Selling Price(Home Delivery)',
                width: 60
            }, {
                header: 'Selling Price(CD)',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'fcpod_customerRateCouDel',
                align: 'right',
                tooltip: 'Selling Price(Courier Delivery)',
                width: 60
            }, {
                xtype: 'actioncolumn',
                hideable: false,
                width: 30,
                items: []
            }
        ]);
    };
    var contractVendorWindow = function () {
        Application.Finascop_Contract_PO.Cache.isStandardPacking = 0;
        if (Ext.isEmpty(contractVendor_Window)) {
            var contractVendor_Window = new Ext.Window({
                id: 'contractVendorId',
                layout: 'fit',
                width: 400,
                title: 'Vendor',
                autoHeight: true,
                draggable: false, iconCls: 'my-icon98',
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [new Ext.FormPanel({
                        frame: true,
                        monitorValid: true,
                        id: 'contractVendorId_form',
                        labelAlign: 'top',
                        height: 60, //'stpa_id', 'stpa_Fname'
                        items: [{
                                xtype: 'combo',
                                fieldLabel: 'Choose a Vendor',
                                //hideLabel: true,
                                //emptyText: 'Choose Vendor',
                                id: 'contract_VendorId',
                                name: 'contract_VendorId',
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
                                hiddenName: 'contract_VendorId',
                                tabIndex: 902,
                                listeners: {
                                    select: function () {
                                        var pe_party = Ext.getCmp('contract_VendorId').getValue();

                                        Ext.Ajax.request({
                                            url: modURL + '&op=contractVendorDetails',
                                            method: 'POST',
                                            params: {contract_VendorId: pe_party},
                                            success: function (res) {
                                                var tmp = Ext.decode(res.responseText);
                                                console.log('tmp.fcpo_id', tmp.fcpo_id);
                                                if (tmp.fcpo_id > 0) {
                                                    contractVendor_Window.close();
                                                    Application.Finascop_Contract_PO.editPOWindowForVendor(tmp.fcpo_uniqueid);
                                                } else {
                                                    contractVendor_Window.close();
                                                    Application.Finascop_Contract_PO.newPOWindowForVendor(pe_party);
                                                }

                                            },
                                            failure: function () {
                                                Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                            }
                                        });

                                    }
                                }
                            }]
                    })],
                buttons: [
                    {
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            contractVendor_Window.close();
                        }
                    }]
            });

        }
        contractVendor_Window.doLayout();
        contractVendor_Window.show(this);
        contractVendor_Window.center();
    };
    var editFromItemStore = function (grid, indx, record)
    {
        Ext.getCmp('pe_partyItems').setValue(record.get('fcpod_itemid'));
        Ext.getCmp('pe_partyItems').setRawValue(record.get('fcpod_itemname'));
        Ext.getCmp('pe_partyItems').focus();
        Ext.getCmp('fpot_itemoffrrate').setValue(record.get('fcpod_itemoffrrate'));
        console.log('isStandardPacking', record.get('isStandardPacking'));
        if (record.get('isStandardPacking') == 1) {
            Ext.getCmp('isStandardPacking').setValue(1);
            Ext.getCmp('fpot_itemmrp').setValue(record.get('fcpod_itemmrp'));
        } else {
            Ext.getCmp('isNotStandardPacking').setValue(2);
            Ext.getCmp('fpot_itemmrp').setValue(record.get('fcpod_leastSKUmrp'));
        }

        Ext.getCmp('fpot_itemoffrrateExcT').setValue(record.get('fcpod_itemoffrrateet'));
        Ext.getCmp('po_gstType').setValue('Exclusive');
        Ext.getCmp('fpot_giftname').setValue(record.get('fcpod_giftname'));
        Ext.getCmp('fpot_giftqty').setValue(record.get('fcpod_giftqty'));
        Ext.getCmp('fpot_notes').setValue(record.get('fcpod_notes'));


    };
    return {
        Cache: {},
        initcontractPurchaseOrder: function () {
            var _panelId = 'purchase_order_panel_contract';
            var _contractpurchaseOrderPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_contractpurchaseOrderPanel)) {
                _contractpurchaseOrderPanel = contractPurchaseOrderPanel(_panelId);
                Application.UI.addTab(_contractpurchaseOrderPanel);
                _contractpurchaseOrderPanel.doLayout();
            } else {
                Application.UI.addTab(_contractpurchaseOrderPanel);
                _contractpurchaseOrderPanel.doLayout();
            }
        }, newPOWindowForVendor: function (pe_party) {
            var vendornewPOForm = vendornewPOCreateForm();
            var vendorPonewWindow = new Ext.Window({
                id: "windowFinascopStocknewPOCreate",
                title: 'Rate Contract',
                shadow: false,
                height: 520,
                width: 1180,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: false,
                items: [vendornewPOForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        text: 'Cancel',
                        tabIndex: 925,
                        handler: function () {
                            Ext.getCmp('windowFinascopStocknewPOCreate').close();
                        }
                    } /*     <?php if (user_access("finascop_contract_po", "savePO")) { ?> */,
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/arrow_right.png',
                        text: 'Submit',
                        tabIndex: 924,
                        handler: function () {
                            if (Ext.getCmp('po_delivered_byvendor').getValue() == true) {
                                var po_delivered_byvendor = 1;
                            } else {
                                var po_delivered_byvendor = 0;
                            }
                            if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('po_payment_value').getValue())) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=contractPOSave',
                                    method: 'POST',
                                    params: {
                                        fpot_uniqueid: Application.Finascop_Contract_PO.Cache.Pouid,
                                        adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                        adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                        po_delivered_byvendor: po_delivered_byvendor,
                                        isConfirm: 1
                                    },
                                    success: function (response) {

                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == true) {

                                            Ext.getCmp('windowFinascopStocknewPOCreate').close();
                                            Ext.getCmp('gridpanelforConratctPO').getStore().load();

                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }

                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please enter Payment Terms & Validity');
                            }


                        }
                    } /*<?php } ?> */
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
                    Application.Finascop_Contract_PO.Cache.Pouid = uid;
                    Ext.getCmp('uniqueId').setValue(uid);

                    Ext.getCmp('pe_party').setValue(pe_party);
                    Ext.getCmp('pe_party').focus();
                }
            });
            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        }, editPOWindowForVendor: function (uniqueid) {
            var vendornewPOForm = vendornewPOCreateForm(uniqueid);
            var vendorPonewWindow = new Ext.Window({
                id: "windowFinascopStocknewPOCreate",
                title: 'Contract Purchase Rate',
                iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1180,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: false,
                items: [vendornewPOForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        hidden: true,
                        tabIndex: 523,
                        handler: function () {
                            Ext.getCmp('windowFinascopStocknewPOCreate').close();
                        }
                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/arrow_right.png',
                        text: 'Submit',
                        tabIndex: 522,
                        handler: function () {
                            if (Ext.getCmp('po_delivered_byvendor').getValue() == true) {
                                var po_delivered_byvendor = 1;
                            } else {
                                var po_delivered_byvendor = 0;
                            }
                            if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('po_payment_value').getValue())) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=contractPOSave',
                                    method: 'POST',
                                    params: {
                                        fpot_uniqueid: Application.Finascop_Contract_PO.Cache.Pouid,
                                        adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                        adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                        po_delivered_byvendor: po_delivered_byvendor,
                                        isConfirm: 1
                                    },
                                    success: function (response) {

                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == true) {

                                            Ext.getCmp('windowFinascopStocknewPOCreate').close();
                                            Ext.getCmp('gridpanelforConratctPO').getStore().load();

                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }

                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please enter Payment Terms & Validity');
                            }

                        }
                    }
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");

            Ext.Ajax.request({
                url: modURL + '&op=loadContractPO',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    uniqueid: uniqueid

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    Ext.getCmp('itemHistory').hide();
                    Ext.getCmp('contractPOItemGrid').store.load({
                        params: {
                            fpot_uniqueid: uniqueid
                        },
                        callback: function () {
                            Application.Finascop_POprerequest.Cache.fcpo_updatedon = tmp.fcpo_updatedon;
                            Application.Finascop_Contract_PO.Cache.Pouid = uniqueid;
                            Ext.getCmp('uniqueId').setValue(uniqueid);
                            Ext.getCmp('pe_party').setReadOnly(true);
                            Ext.getCmp('pe_party').setHideTrigger(true);
                            Ext.getCmp('pe_party').setValue(tmp.fcpo_vendor);
                            Ext.getCmp('pe_party').focus();
                            Ext.getCmp('po_number').setValue(tmp.fcpo_name);
                            Ext.getCmp('po_date').setValue(tmp.fcpo_createdon);

                            Ext.getCmp('pe_partyItems').getStore().load({
                                params: {
                                    stpa_id: tmp.fcpo_vendor
                                }
                            });
                            Ext.getCmp('po_payment_terms').setValue(tmp.fcpo_paymentTerms);
                            Ext.getCmp('po_payment_terms').getStore().load({
                                params: {
                                    stpa_id: tmp.fcpo_vendor
                                }
                            });
                            Ext.getCmp('po_payment_value').setValue(tmp.fcpo_validDate);
                            if (tmp.fcpo_delivered_byvendor == 1) {
                                Ext.getCmp('po_delivered_byvendor').setValue(true);
                            } else {
                                Ext.getCmp('po_delivered_byvendor').setValue(false);
                            }

                        }
                    });

                }
            });
            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        },
        deleteContractPO: function () {
            var t = new Date();
            var adhoc_uniqueid = arguments[0];
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=deleteContractPODetails',
                method: 'POST',
                params: {adhoc_uniqueid: adhoc_uniqueid},
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelforConratctPO'));
                        Ext.getCmp('gridpanelforConratctPO').store.reload({
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
        }, viewCPODetails: function (poId) {
            var vendContractPOForm = vendorContractPOViewForm(poId);
            var poViewWindow = new Ext.Window({
                id: "windowFinascopStocknewPoView",
                title: 'View Rate Contract',
                //iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1200,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: false,
                closable: false,
                items: [vendContractPOForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 519,
                        handler: function () {
                            Ext.getCmp('windowFinascopStocknewPoView').close();
                        }
                    }
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=getCPODetails',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    poId: poId

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    console.log(tmp);
                    Ext.getCmp('vendor_name').setValue(tmp.vendorName);
                    Ext.getCmp('centralStore').setValue(tmp.centralStore);
                    Ext.getCmp('vpo_number').setValue(tmp.fcpo_name);
                    Ext.getCmp('vpo_date').setValue(tmp.fcpo_createdon);
                    Ext.getCmp('po_payment_terms').setValue(tmp.fcpo_paymentTerms);
                    Ext.getCmp('fcpo_validDate').setValue(tmp.fcpo_validDate);
                    Ext.getCmp('newPOItemGrid').getStore().load({
                        params: {
                            fpot_uniqueid: tmp.fcpo_uniqueid
                        }
                    });
                }
            });
            poViewWindow.doLayout();
            poViewWindow.show();
            poViewWindow.center();
        }
    }
}();