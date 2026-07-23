Application.Finascop_POprerequest = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 19;
    var partytype = 0;
    var modURL = '?module=finascop_po_prerequest';
    var pomodURL = '?module=finascop_purchase_order';
    var dateRange = parseInt(BETWEEN_DATES_TYPE_A);
    var total_mrp = 0;
    var onGridResize = function (cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    };
    var prereqisitePurchaseOrderPanel = function () {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id,
            title: 'Purchase Requisite',
            iconCls: 'finascop_invoice',
            items: [PurchaseOrderPrereqisiteGrid()]
        });
        return panel;
    };
    var vendorStore = new Ext.data.JsonStore({
        fields: ['stpa_id', 'stpa_Fname'],
        url: pomodURL + '&op=getVendorName',
        autoLoad: true,
        method: 'post',
    });
    var centralStore = new Ext.data.JsonStore({
        fields: ['br_ID', 'br_Name'],
        url: pomodURL + '&op=getCentralStores',
        autoLoad: true,
        method: 'post',
    });

    var vendorItemStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: pomodURL + '&op=vendorItemStore',
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
            }
        }
    });
    var PurchaseOrderPrerquisiteGridStore = function () {
        var _catStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getPurchaseOrderPrereqisite',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'prereq_uniqueid',
                root: 'data'
            }, ['prereq_uniqueid', 'prereq_name', 'prereq_createdon', 'vendorName', 'prereq_status']),
            sortInfo: {
                field: 'prereq_createdon',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getSelectionModel().selectRow(0);
                }
            }
        });

        return _catStore;
    };
    var PurchaseOrderPrereqisiteGrid = function () {
        var _PoGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'prereq_name'
                }, {
                    type: 'string',
                    dataIndex: 'prereq_createdon'
                }, {
                    type: 'string',
                    dataIndex: 'vendorName'
                }]
        });
        _PoGridFilter.remote = true;
        _PoGridFilter.autoReload = true;
        var _gridStore = PurchaseOrderPrerquisiteGridStore();

        var _gridPanel = new Ext.grid.GridPanel({
            id: 'gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite',
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
                    text: 'Create Purchase Requisite',
                    tooltip: 'Create Purchase Requisite',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        //VendorOperationsWindow()
                        Application.Finascop_POprerequest.newPOWindowForPrerequisite();
                    }
                }, {
                    text: 'Create Contract PO',
                    tooltip: 'Create Contract PO',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    hidden: true,
                    handler: function () {
                        Application.Finascop_POprerequest.newPOWindowForVendor()
                    }
                }],
            plugins: [new Ext.ux.grid.GroupSummary(), _PoGridFilter],
            columns: [
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'prereq_name',
                    tooltip: 'Name',
                    hideable: false
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'prereq_createdon',
                    tooltip: 'Date'
                }, {
                    header: 'Vendor',
                    sortable: true,
                    dataIndex: 'vendorName',
                    tooltip: 'Vendor',
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
                            purchaserequisiteActionMenu.showAt(e.getXY());
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
                 if (rec.get('prereq_status') == 1) {
                 return 'my-icon40';
                 } else {
                 return 'hideicon';
                 }
                 
                 },
                 //iconCls: "my-icon40",
                 //icon: IMAGE_BASE_PATH + "/default/icons/printer.png",
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Application.Finascop_POprerequest.printPOPrereq(record.get('prereq_name'));
                 }
                 }, {
                 getClass: function (v, meta, rec) {
                 // return 'my-icon109';
                 if (rec.get('prereq_status') == 0) {
                 return 'approve-reject';
                 } else {
                 return 'hideicon';
                 }
                 
                 },
                 tooltip: 'Confirm PO Prerequisite',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var t = new Date();
                 var t_stamp = t.format("YmdHis");
                 Application.Finascop_POprerequest.editPOWindowForVendor(record.get('prereq_uniqueid'));
                 }
                 }, {
                 tooltip: 'DELETE',
                 iconCls: 'wrong',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var prereq_uniqueid = record.get('prereq_uniqueid');
                 Application.Finascop_POprerequest.deletePOPrereq(prereq_uniqueid);
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
    var purchaserequisiteActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Confirm",
                handler: function () {
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    var prereq_uniqueid = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getSelectionModel().getSelections()[0].data.prereq_uniqueid;
                    Application.Finascop_POprerequest.editPOWindowForVendor(prereq_uniqueid);
                }
            }, {
                text: 'Delete',
                handler: function () {
                    var prereq_uniqueid = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getSelectionModel().getSelections()[0].data.prereq_uniqueid;
                    Application.Finascop_POprerequest.deletePOPrereq(prereq_uniqueid);
                }
            }]
    });

    var gridSelectionChangedPrereqisite = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getSelectionModel().getSelections()[0].data.fpo_id;
        }
    };
    var prerequisitePOCreateForm = function () {
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
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.17,
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
                                    anchor: '95%',
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
                            columnWidth: .2,
                            labelAlign: 'left',
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
                            columnWidth: .2,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'PR Date',
                                    id: 'po_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    value: 'Not Genereated',
                                    anchor: '97%',
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .18,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Ordered By',
                                    id: 'po_ordered_by',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_ordered_by]',
                                    anchor: '95%',
                                    value: _SESSION.FirstName,
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: 0.25,
                            labelAlign: 'left',
                            labelWidth: 50,
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Ship To',
                                    emptyText: 'Ship To',
                                    id: 'po_billing_to',
                                    name: 'po_billing_to',
                                    labelStyle: mandatory_label,
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '98%',
                                    store: centralStore,
                                    allowBlank: false,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'br_Name',
                                    valueField: 'br_ID',
                                    hiddenName: 'po_billing_to',
                                    tabIndex: 903
                                }]
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
                                    fieldLabel: 'City',
                                    id: 'vendorCity',
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
                                    fieldLabel: 'GST',
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
                                    fieldLabel: 'GST',
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
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Min Qty',
                                    id: 'itemMinQty',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }]
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
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }]
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
                                    columnWidth: 0.5,
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
                                            tabIndex: 904, listeners: {
                                                select: function () {
                                                    var pe_partyItems = Ext.getCmp('pe_partyItems').getValue();
                                                    var po_billing_to = Ext.getCmp('po_billing_to').getValue();
                                                    if (po_billing_to > 0) {
                                                        Ext.Ajax.request({
                                                            url: pomodURL + '&op=itemHistory',
                                                            method: 'POST',
                                                            params: {pe_partyItems: pe_partyItems, po_billing_to: po_billing_to},
                                                            success: function (res) {
                                                                var tmp = Ext.decode(res.responseText);

                                                                if (tmp.csb_package_type_name != '') {
                                                                    purUni = ' - ' + tmp.csb_package_type_name;
                                                                } else {
                                                                    purUni = '';
                                                                }
                                                                Ext.getCmp('itemHistory').show();
                                                                Ext.getCmp('itemHSN').setValue(tmp.stit_HSN_code);
                                                                Ext.getCmp('itemLP').setValue(tmp.last_mrp);
                                                                Ext.getCmp('itemGST').setValue(tmp.stit_GST);
                                                                Ext.getCmp('itemLPP').setValue(tmp.last_sp);
                                                                Ext.getCmp('itemOptimumQty').setValue(tmp.optiQty);
                                                                Ext.getCmp('itemCSCount').setValue(tmp.itemCSCount);
                                                                Ext.getCmp('itemMinQty').setValue(tmp.minQty);
                                                                //Ext.getCmp('itemUnit').setValue(tmp.csb_package_type_name);
                                                                //Ext.getCmp('itemUnitForm').setValue(tmp.itemUnitForm);
                                                                Ext.getCmp('itemUnitFormdet').setValue(tmp.itemUnitForm);
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
                                                        Ext.MessageBox.alert('Notification', 'Choose Shipping To.');
                                                        Ext.getCmp('pe_partyItems').reset();
                                                    }


                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Qty',
                                            align: 'right',
                                            id: 'fpopredet_itemqty',
                                            name: 'n[fpopredet_itemqty]',
                                            anchor: '85%',
                                            allowBlank: true,
                                            tabIndex: 907,
                                            listeners: {
                                                change: function () {
                                                    var fpopredet_itemqty = Ext.getCmp('fpopredet_itemqty').getValue();
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.08,
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
                                            tabIndex: 910, listeners: {
                                                select: function () {
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'button',
                                            text: 'Add',
                                            tabIndex: 915,
                                            anchor: '85%',
                                            id: 'addItemsToPO',
                                            iconCls: 'finascop_add',
                                            style: 'margin-top:17px;',
                                            handler: function () {
                                                if (Ext.getCmp('pe_partyItems').getValue() > 0) {
                                                    Ext.Ajax.request({
                                                        waitMsg: 'Processing',
                                                        method: 'POST',
                                                        url: modURL + '&op=saveItemPOPreqDetails',
                                                        params: {
                                                            fpopredet_uniqueid: Application.Finascop_POprerequest.Cache.POPre,
                                                            fpopredet_vendorid: Ext.getCmp('pe_party').getValue(),
                                                            fpopredet_itemid: Ext.getCmp('pe_partyItems').getValue(),
                                                            fpopredet_itemname: Ext.getCmp('pe_partyItems').getRawValue(),
                                                            fpopredet_itemqty: Ext.getCmp('fpopredet_itemqty').getValue(),
                                                            prereq_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                                            poprereq_updatedon: Application.Finascop_POprerequest.Cache.poprereq_updatedon
                                                        },
                                                        success: function (response) {
                                                            var tmp = Ext.decode(response.responseText);
                                                            if (tmp.success === true)
                                                            {
                                                                Application.example.msg('Success', tmp.msg);
                                                                Application.Finascop_POprerequest.Cache.poprereq_updatedon = tmp.date;
                                                                Ext.getCmp('itemHistory').hide();
                                                                Ext.getCmp('prerequisitePOItemGrid').store.load({
                                                                    params: {
                                                                        fpopredet_uniqueid: Application.Finascop_POprerequest.Cache.POPre
                                                                    },
                                                                    callback: function () {
                                                                        Ext.getCmp('pe_partyItems').reset();
                                                                        Ext.getCmp('fpopredet_itemqty').reset();
                                                                        Ext.getCmp('itemUnitForm').reset();
                                                                    }
                                                                });
                                                                Ext.getCmp('prerequisitePOItemGrid').getStore().load({
                                                                    params: {
                                                                        fpopredet_uniqueid: Application.Finascop_POprerequest.Cache.POPre
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
                                                    Ext.Msg.alert("Notification.", 'Choose Items');
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
                                        prerequisitePOItemGrid()
                                    ]
                                }]
                        }]
                }]
        });
        return panel;
    };
    var prerequisitePOItemGrid = function () {

        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listPoPrereqdetailsStore',
                    method: 'post'
                }),
                fields: ['fpopredet_itemid', 'fpopredet_itemname', 'fpopredet_itemmrp', 'fpopredet_itemqty', 'fpopredet_itemoffrqty', 'fpopredet_totalqty', 'fpopredet_balanceqty', 'fpopredet_itemoffrrate', 'fpopredet_itemoffrrateet', 'itemDisc',
                    'fpopredet_itemaddidisc', 'fpopredet_effectiverate', 'fpopredet_idiscountcalculs', {name: 'fpopredet_amount', type: 'float'}, {name: 'fpopredet_netamount', type: 'float'}, {name: 'fpopredet_initialnetamount', type: 'float'}, 'fpopredet_gendiscount', 'fpopredet_shippingcharge'],
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
        var itemGridStore = PurchaseOrderItemStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'prerequisitePOItemGrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: prerequisitePurchaseOrderColModel(),
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
    var prerequisitePurchaseOrderColModel = function ()
    {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'fpopredet_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'fpopredet_itemqty',
                tooltip: 'Qty',
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
            url: modURL + '&op=deleteVendorItemFromPOPrereq',
            method: 'POST',
            params: {
                itemid: record.get('fpopredet_itemid'),
                uid: Application.Finascop_POprerequest.Cache.POPre
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true)
                {
                    Ext.getCmp('prerequisitePOItemGrid').getStore().load({
                        params: {
                            fpopredet_uniqueid: Application.Finascop_POprerequest.Cache.POPre
                        }
                    });

                } else
                {
                    Ext.MessageBox.alert('Failed');
                }
            }
        });
    };
    return{
        Cache: {},
        initPrerequestPurchaseOrder: function () {
            var _panelId = 'purchase_order_panel_prereqisite';
            var _prereqisitePurchaseOrderPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_prereqisitePurchaseOrderPanel)) {
                _prereqisitePurchaseOrderPanel = prereqisitePurchaseOrderPanel(_panelId);
                Application.UI.addTab(_prereqisitePurchaseOrderPanel);
                _prereqisitePurchaseOrderPanel.doLayout();
            } else {
                Application.UI.addTab(_prereqisitePurchaseOrderPanel);
                _prereqisitePurchaseOrderPanel.doLayout();
            }
        },
        newPOWindowForPrerequisite: function () {
            var prerequisitePOForm = prerequisitePOCreateForm();
            Application.Finascop_POprerequest.Cache.poprerequisite_updatedon = '';
            var vendorPonewWindow = new Ext.Window({
                id: "windowFinascopStocknewPOCreate",
                title: 'Create Purchase Requisite',
                //iconCls: 'vender-items',
                shadow: false,
                width: winsize.width * 0.67,
                height: winsize.height * 0.43,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [prerequisitePOForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Save & Close',
                        tabIndex: 925,
                        handler: function () {
                            if (prerequisitePOForm.getForm().isValid()) {
                            Ext.Ajax.request({
                                url: modURL + '&op=prerqSaveClose',
                                method: 'POST',
                                params: {
                                    fpopredet_uniqueid: Application.Finascop_POprerequest.Cache.POPre,
                                    prereq_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                    isConfirm: 0
                                },
                                success: function (response) {

                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true) {

                                        Ext.getCmp('windowFinascopStocknewPOCreate').close();
                                        Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getStore().load();

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
                            Ext.Ajax.request({
                                url: modURL + '&op=prerqSaveClose',
                                method: 'POST',
                                params: {
                                    fpopredet_uniqueid: Application.Finascop_POprerequest.Cache.POPre,
                                    prereq_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                    isConfirm: 1
                                },
                                success: function (response) {

                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true) {

                                        Ext.getCmp('windowFinascopStocknewPOCreate').close();
                                        Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getStore().load();

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
                    Application.Finascop_POprerequest.Cache.POPre = uid;
                    Ext.getCmp('uniqueId').setValue(uid);
                }
            });
            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        },
        deletePOPrereq: function (prereq_uniqueid) {
            var t = new Date();
            //var prereq_uniqueid = arguments[0];
            var t_stamp = t.format("YmdHis");
            Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
            if (btn == 'yes') {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                url: modURL + '&op=deletePrereqDetails',
                method: 'POST',
                params: {
                    prereq_uniqueid: prereq_uniqueid
                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite'));
                        Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').store.reload({
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
            var vendornewPOForm = prerequisitePOCreateForm(uniqueid);
            var vendorPonewWindow = new Ext.Window({
                id: "windowFinascopStocknewPOCreate",
                title: 'Confirm Purchase Order',
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
                                url: modURL + '&op=prerqSaveClose',
                                method: 'POST',
                                params: {
                                    fpopredet_uniqueid: Application.Finascop_POprerequest.Cache.POPre,
                                    prereq_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                    isConfirm: 0
                                },
                                success: function (response) {

                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true) {

                                        Ext.getCmp('windowFinascopStocknewPOCreate').close();
                                        Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getStore().load();

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
                            Ext.Ajax.request({
                                url: modURL + '&op=prerqSaveClose',
                                method: 'POST',
                                params: {
                                    fpopredet_uniqueid: Application.Finascop_POprerequest.Cache.POPre,
                                    prereq_billingTo: Ext.getCmp('po_billing_to').getValue(),
                                    isConfirm: 1
                                },
                                success: function (response) {

                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true) {

                                        Ext.getCmp('windowFinascopStocknewPOCreate').close();
                                        Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdataPrereqisite').getStore().load();

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
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");

            Ext.Ajax.request({
                url: modURL + '&op=loadPrereqPO',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    uniqueid: uniqueid

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    Ext.getCmp('itemHistory').hide();
                    Ext.getCmp('prerequisitePOItemGrid').store.load({
                        params: {
                            fpopredet_uniqueid: uniqueid
                        },
                        callback: function () {
                            Application.Finascop_POprerequest.Cache.poprereq_updatedon = tmp.prereq_updatedon;
                            Application.Finascop_POprerequest.Cache.POPre = uniqueid;
                            Ext.getCmp('uniqueId').setValue(uniqueid);
                            Ext.getCmp('pe_party').setReadOnly(true);
                            Ext.getCmp('pe_party').setHideTrigger(true);
                            Ext.getCmp('pe_party').setValue(tmp.prereq_vendor);
                            Ext.getCmp('po_billing_to').setValue(tmp.prereq_billingTo);
                            Ext.getCmp('pe_partyItems').getStore().load({
                                params: {
                                    stpa_id: tmp.prereq_vendor
                                }
                            });

                        }
                    });

                }
            });
            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        }, printPOPrereq: function (PoId) {

            var printLabResultsPanel = Application.Finascop_POprerequest.createprintPOPrePanel(PoId);
            if (Ext.isEmpty(printRetalinPODetaislWindow)) {
                var printRetalinPODetaislWindow = new Ext.Window({
                    id: 'printRetalinPODetaislWindow',
                    plain: true,
                    modal: true,
                    constrain: true,
                    resizable: false,
                    title: 'Purchase Order Prerequisite',
                    width: winsize.width * 0.7,
                    autoHeight: true,
                    items: [printLabResultsPanel],
                    buttons: [{
                            text: 'Cancel',
                            handler: function () {
                                printRetalinPODetaislWindow.close();
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
                                printRetalinPODetaislWindow.close();
                            }
                        }],
                });
            }
            printRetalinPODetaislWindow.doLayout();
            printRetalinPODetaislWindow.show();
            printRetalinPODetaislWindow.center();
        }, createprintPOPrePanel: function (PoId) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var src = '?module=finascop_po_prerequest&op=printPOPreDetails&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp + '&PoId=' + PoId;
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
        }
    }
}();