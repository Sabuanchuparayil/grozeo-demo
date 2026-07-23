Application.OfferManagement = function () {
    var RECS_PER_PAGE = 20;
    var modURL = '?module=offer_management';
    var winsize = Ext.getBody().getViewSize();

    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('offerMgmtGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('offerMgmtGridPanel').getSelectionModel().getSelections()[0].data.bom_id;
            Application.OfferManagement.ViewMode(ID);
        }
    };
    var couponGridSelectionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().getSelections()[0].data.bom_id;
            Application.OfferManagement.CouponViewMode(ID);
        }
    };
    var gridSearchSelectionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('offerMgmtSearchGridPanel').getSelectionModel().getSelections())) {
            var stiid_fpoid = Ext.getCmp('offerMgmtSearchGridPanel').getSelectionModel().getSelections()[0].data.stiid_fpoid;
            var stiid_itemmasterid = Ext.getCmp('offerMgmtSearchGridPanel').getSelectionModel().getSelections()[0].data.stiid_itemmasterid;
            var iItemsRemaining = Ext.getCmp('offerMgmtSearchGridPanel').getSelectionModel().getSelections()[0].data.iItemsRemaining;
            Application.OfferManagement.Cache.iItemsRemaining = iItemsRemaining;
            Application.OfferManagement.Cache.stiid_fpoid = stiid_fpoid;
            Application.OfferManagement.Cache.stiid_itemmasterid = stiid_itemmasterid;
            Application.OfferManagement.AddOffer(stiid_fpoid, stiid_itemmasterid);
        }
    };


    var searchOfferItems = function () {
        var offer_add_form = panelforOfrMgmtSearchDetails();
        var resultWindow = new Ext.Window({
            id: 'windowFinascopOfferDetails',
            title: 'Create Percentage wise offer',
            shadow: false,
            height: 600,
            width: winsize.width * 0.9,
            layout: 'fit',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            closable: true,
            items: [offer_add_form],
            buttons: [
                {
                    text: 'Cancel',
                    tabIndex: 525,
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    handler: function () {
                        Ext.getCmp('windowFinascopOfferDetails').close();
                        Ext.getCmp('offerMgmtGridPanel').getStore().reload();

                    }
                }, {
                    text: 'Save',
                    tabIndex: 524,
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    handler: function () {
                        saveOfferPercentage();
                    }
                }
            ]

        });

        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();
    };
    var saveOfferPercentage = function () {
        var offerSaveForm = Ext.getCmp('offerSaveForm').getForm();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (offerSaveForm.isValid()) {
            var form_data = {
                form: Ext.getCmp('offerSaveForm').getForm().getValues(),
                PoId: Application.OfferManagement.Cache.stiid_fpoid,
                itemId: Application.OfferManagement.Cache.stiid_itemmasterid,
            };
            var params = {
                action: 'Insert Offer %',
                module: 'offer_management',
                op: 'saveOfferPercentage',
                extrainfo: 'Save Offer Percentage',
                id: Application.OfferManagement.Cache.stiid_fpoid
            };
            APICall(params, Application.OfferManagement.saveOfferPercentage, form_data);
        } else {
            Application.example.msg('Error', 'Check the required fields');
        }
    };
    var offerMgmtSearchStore = function () {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=searchOfferMgmt',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'stii_id',
                root: 'data'
            }, ['fpo_poNumber', 'stiid_itemmastername', 'iItemsRemaining', 'stiid_fpoid', 'stiid_itemmasterid', 'stii_id']),
            sortInfo: {
                field: 'stii_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('offerMgmtSearchGridPanel').getSelectionModel().selectRow(0);

                }
            }
        });
        return _store;
    };
    var offerMgmtStore = function () {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listOfferMgmt',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fpo_id',
                root: 'data'
            }, ['bom_id', 'bom_bmdCompany', 'bom_bmdHub', 'bom_bmdIncentive', 'bom_bmdTechnology', 'bom_bmdCustomer', 'bom_offrPlacement', 'bom_offrPromotion', 'bom_offrSupplier', 'stiid_fpoid', 'stiid_itemmasterid',
                'bmo_itemName', 'bmo_mrp', 'bmo_epr', 'bmo_offrrate', 'itemCount', 'bom_status', 'bom_locked']),
            //sortInfo: {
            //    field: 'fpo_id',
            //    direction: "DESC"
            //},
            groupField: '',
            groupDir: 'ASC',
        //    remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('offerMgmtGridPanel').getSelectionModel().selectRow(0);

                }
            }
        });
        return _store;
    };
    var offerMgmtGrid = function () {

        var _poGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fpo_poNumber'
                }, {
                    type: 'string',
                    dataIndex: 'bmo_itemName'
                },  {
                    type: 'string',
                    dataIndex: 'bmo_mrp'
                },  {
                    type: 'string',
                    dataIndex: 'bmo_offrrate'
                },  {
                    type: 'string',
                    dataIndex: 'bmo_epr'
                },  {
                    type: 'string',
                    dataIndex: 'itemCount'
                },   {
                    type: 'string',
                    dataIndex: 'bom_bmdCompany'
                }, {
                    type: 'string',
                    dataIndex: 'bom_bmdHub'
                }, {
                    type: 'string',
                    dataIndex: 'bom_bmdIncentive'
                },{
                    type: 'string',
                    dataIndex: 'vendor_name'
                }, {
                    type: 'list',
                    value: ['Active'],
                    dataIndex: 'fpo_Active',
                    options: ['Active', 'Inactive'],
                    phpMode: true
                }]
        });
        _poGridFilter.remote = true;
        _poGridFilter.autoReload = true;
        var _gridStore = offerMgmtStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'offerMgmtGridPanel',
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _poGridFilter],
            store: _gridStore,
            columns: [
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'bmo_itemName',
                    tooltip: 'Item',
                    hideable: false
                },
                {
                    header: 'MRP',
                    sortable: true,
                    dataIndex: 'bmo_mrp',
                    tooltip: 'MRP'
                },
                {
                    header: 'Offer Rate',
                    sortable: true,
                    dataIndex: 'bmo_offrrate',
                    tooltip: 'Offer Rate',
                    hideable: false
                }, {
                    header: 'EPR',
                    sortable: true,
                    dataIndex: 'bmo_epr',
                    tooltip: 'EPR',
                    hideable: false
                }, {
                    header: 'Count',
                    sortable: true,
                    dataIndex: 'itemCount',
                    tooltip: 'Count',
                    hideable: false
                }, {
                    header: 'Company',
                    sortable: true,
                    dataIndex: 'bom_bmdCompany',
                    tooltip: 'Company',
                    hideable: false
                }, {
                    header: 'Hub',
                    sortable: true,
                    dataIndex: 'bom_bmdHub',
                    tooltip: 'Hub',
                    hideable: false
                }, {
                    header: 'Incentive',
                    sortable: true,
                    dataIndex: 'bom_bmdIncentive',
                    tooltip: 'Incentive',
                    hideable: false
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            tooltip: 'Change Status',
                            getClass: function (v, meta, rec) {
                                if ((rec.get('bom_locked') == 0) && (rec.get('bom_status') == 'Active')) {
                                    return 'user_enabled';

                                } else {
                                    return 'hideicon';
                                }

                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var bom_id = record.data.bom_id;
                                var bom_typeName = record.data.bom_typeName;
                                Application.OfferManagement.change_status(bom_id, bom_typeName);
                            }
                        }]
                }

            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                },
                resize: onGridResize,
                afterrender: function () {
                    _gridStore.load();
                }
            },
            tbar: [
                {
                    xtype: 'button',
                    text: 'Create Offer Percentage',
                    tooltip: 'Create Offer Percentage',
                    iconCls: 'finascop_add',
                    handler: function () {
                        searchOfferItems();
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
        });
        return _gridPanel;
    };
    var offerMgmtView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: 'View Offer Details',
            autoScroll: true,
            id: 'orderManagementViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Company </th><td> {bom_bmdCompany} </td></tr>',
                    '<tr><th width="40%">Hub</th><td> {bom_bmdHub} </td></tr>',
                    '<tr><th width="40%">Incentive </th><td> {bom_bmdIncentive} </td></tr>',
                    '<tr><th width="40%">Technology </th><td> {bom_bmdTechnology} </td></tr>',
                    '<tr><th width="40%">Customer </th><td> {bom_bmdCustomer} </td></tr>',
                    '<tr><th width="40%">Offer Placement </th><td> {bom_offrPlacement} </td></tr>',
                    '<tr><th width="40%">Difference </th><td> {bom_offrDiffer} </td></tr>',
                    '<tr><th width="40%">Promotion </th><td> {bom_offrPromotion} </td></tr>',
                    '<tr><th width="40%">Supplier </th><td> {bom_offrSupplier} </td></tr>',
                    '<tr><th width="40%">Narration </th><td> {bom_narration} </td></tr>',
                    '<tr><th width="40%">Start date </th><td> {bom_startdate} </td></tr>',
                    '<tr><th width="40%">Offer Type</th><td> {bom_offfrvalidtype} </td></tr>',
                    '<tpl if="bom_offfrvalidtype != \'Till stock lasts\'"><tr><th width="40%">End Date</th><td> {bom_enddate} </td></tr></tpl>',
                    '',
                    '</td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
    };
    var panelforOfrMgmtDetails = function (id) {
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Offer Percentage',
            id: id,
            items: [offerMgmtGrid(), new Ext.Panel({
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    id: 'OfrMgmtparentPanel',
                    height: winsize.height * 0.6,
                    layout: 'border',
                    items: [{
                            region: 'center',
                            items: offerMgmtView()
                        }
                    ]
                })
            ]
        });
        return panel;
    };
    var panelforOfrMgmtSearchDetails = function () {
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Search Item',
            id: 'offerMgmtUpdate',
            items: [offerMgmtSearchGrid(), new Ext.Panel({
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    id: 'OfrMgmtparentPanel',
                    height: winsize.height * 0.6,
                    layout: 'border',
                    items: [{
                            region: 'center',
                            items: offerMgmtSearchForm()
                        }
                    ]
                })
            ]
        });
        return panel;
    };
    var itemStorefn = function () {
        var store = new Ext.data.JsonStore({
        fields: ['stit_ID', 'stit_itemName', 'stit_SKU'],
        url: modURL + '&op=getItemName',
        autoLoad: true,
        method: 'post'
    });
        return store;
    }
    var couponcategoryStore = function(){
    var categoryStore = new Ext.data.JsonStore({
        fields: ['sub_category_id', 'sub_category'],
        url: modURL + '&op=couponcategoryStore',
        autoLoad: true,
        method: 'post'
    });
        return categoryStore;
    }
    var offerMgmtSearchGrid = function () {
        var itemStore = itemStorefn();
        var _poGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fpo_poNumber'
                }, {
                    type: 'string',
                    dataIndex: 'itemName'
                }, {
                    type: 'list',
                    value: ['Active'],
                    dataIndex: 'fpo_Active',
                    options: ['Active', 'Inactive'],
                    phpMode: true
                }]
        });
        _poGridFilter.remote = true;
        _poGridFilter.autoReload = true;
        var _gridStore = offerMgmtSearchStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'offerMgmtSearchGridPanel',
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _poGridFilter],
            store: _gridStore,
            columns: [{
                    header: 'PO Number',
                    sortable: true,
                    dataIndex: 'fpo_poNumber',
                    width: 175
                },
                {
                    header: 'Item Name',
                    sortable: true,
                    dataIndex: 'stiid_itemmastername',
                    width: 175
                },
                {
                    header: 'Count',
                    sortable: true,
                    dataIndex: 'iItemsRemaining',
                    width: 175
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSearchSelectionChanged
                }
            }),
            listeners: {
            },
            tbar: [{
                    xtype: 'combo',
                    fieldLabel: 'Items',
                    hideLabel: true,
                    emptyText: 'Item Search',
                    id: 'textfieldFinascopItemname',
                    name: 'textfieldFinascopItemname',
                    labelStyle: mandatory_label,
                    allowBlank: false,
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    anchor: '100%',
                    width: 400,
                    store: itemStore,
                    //triggerAction: 'all',
                    hideTrigger: true,
                    minChars: 3,
                    selectOnFocus: false,
                    displayField: 'stit_SKU',
                    valueField: 'stit_ID',
                    hiddenName: 'textfieldFinascopItemname',
                    tabIndex: 600,
                    listeners: {
                    }
                }, /*{
                 emptyText: 'Item Name',
                 xtype: 'textfield',
                 id: 'textfieldFinascopItemname',
                 labelStyle: 'width:125px',
                 tabIndex: 600,
                 anchor: '88%',
                 editable: true,
                 listeners: {
                 afterrender: function (field) {
                 Ext.defer(function () {
                 field.focus(true, 100);
                 }, 1);
                 }
                 }
                 }, */'|', {
                    xtype: 'button',
                    text: 'Search',
                    tabIndex: 601,
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        var itemname = Ext.getCmp('textfieldFinascopItemname').getValue();
                        var itemsku = Ext.getCmp('textfieldFinascopItemname').getRawValue();
                        if (itemname != '') {
                            Ext.getCmp('offerMgmtSearchGridPanel').getStore().load({
                                params: {
                                    itemname: itemname,
                                    itemsku: itemsku
                                }
                            });
                        }
                    }
                }
            ],
            stripeRows: true,
        });
        return _gridPanel;
    };
    var calcCustomerProfit = function () {
        var bmd_percent = Ext.getCmp('bmd_percent').getValue();
        var companypd_new = Ext.getCmp('companypd_new').getValue();
        if (Ext.isEmpty(companypd_new)) {
            companypd_new = 0;
        }
        var hubpd_new = Ext.getCmp('hubpd_new').getValue();
        if (Ext.isEmpty(hubpd_new)) {
            hubpd_new = 0;
        }
        var incentivepd_new = Ext.getCmp('incentivepd_new').getValue();
        if (Ext.isEmpty(incentivepd_new)) {
            incentivepd_new = 0;
        }
        var technologypd_new = Ext.getCmp('technologypd_new').getValue();
        if (Ext.isEmpty(technologypd_new)) {
            technologypd_new = 0;
        }
        var customerpd_new = parseInt(bmd_percent) - (parseInt(companypd_new) + parseInt(hubpd_new) + parseInt(incentivepd_new) + parseInt(technologypd_new));
        Ext.getCmp('customerpd_new').setValue(customerpd_new);
    };
    var offerMgmtSearchForm = function () {
        var _formToSaveOfferDetails = new Ext.FormPanel({
            id: 'offerSaveForm',
            frame: false,
            border: false,
            autoHeight: true,
            labelWidth: 100,
            layout: 'column',
            bodyStyle: {"padding": "5px 5px 5px 25px"},
            items: [
                {
                    layout: 'form',
                    columnWidth: .3,
                    labelAlign: 'top',
                    border: false,
                    items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'PO Number',
                            labelAlign: 'left',
                            id: 'fpo_poNumber',
                            name: 'fpo_poNumber',
                            anchor: '100%',
                            allowBlank: false,
                            maxLength: 20
                        }]
                }, 
                {
                    layout: 'form',
                    columnWidth: .7,
                    labelAlign: 'top',
                    border: false,
                    items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'Item',
                            labelAlign: 'left',
                            id: 'stii_itemmastername',
                            name: 'stii_itemmastername',
                            anchor: '100%',
                            allowBlank: false,
                            maxLength: 20
                        }]
                }, 
                {
                    layout: 'form',
                    labelAlign: 'top',
                    bodyStyle: {"background-color": "Gainsboro" ,"padding": "20px 5px 5px 5px"},
                    columnWidth: 0.20,
                    border: false,
                    items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'MRP',
                            name: 'stii_mrp',
                            id: 'stii_mrp',
                            anchor: '95%',
                            align: 'right',
                            allowBlank: false,
                            maxLength: 20
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    bodyStyle: {"background-color": "Gainsboro" , "padding": "20px 5px 5px 5px"},
                    border: false,
                    items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'Offer Rate',
                            name: 'offerRate',
                            id: 'offerRate',
                            anchor: '95%',
                            align: 'right',
                            allowBlank: false,
                            width: 300,
                            maxLength: 20
                        }

                    ]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    bodyStyle: {"background-color": "Gainsboro" , "padding": "20px 5px 5px 5px"},
                    border: false,
                    items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'EPR',
                            name: 'stii_epraft',
                            id: 'stii_epraft',
                            align: 'right',
                            anchor: '95%',
                            allowBlank: false,
                            width: 300,
                            maxLength: 20
                        }

                    ]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    bodyStyle: {"background-color": "Gainsboro", "padding": "20px 5px 5px 5px"},
                    border: false,
                    items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'Selling Price',
                            name: 'stii_selpri',
                            id: 'stii_selpri',
                            anchor: '95%',
                            allowBlank: false,
                            width: 300,
                            align: 'right',
                            maxLength: 20
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    bodyStyle: {"background-color": "Gainsboro" , "padding": "20px 5px 5px 5px"},
                    border: false,
                    items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'Net Profit(%)',
                            id: 'bmd_percent',
                            name: 'bmd_percent',
                            anchor: '95%',
                            allowBlank: false,
                            width: 300,
                            maxValue: 100,
                            align: 'right',
                            maxLength: 20
                        }]
                },{
                    layout: 'form',
                    columnWidth: 1,
                    border: false,
                    style: 'margin-top:15px;margin-bottom:5px;',
                    html:'<b> Margin Distribution</b>'
                } ,{
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    border: false,
                    items: [{
                            xtype: 'compositefield',
                            labelalign: 'top',
                            fieldLabel: 'Company',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    name: 'companypd_old',
                                    id: 'companypd_old',
                                    width: '50',
                                    readOnly: true,
                                    align: 'right',
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    name: 'companypd_new',
                                    id: 'companypd_new',
                                    width: '50',
                                    tabIndex: 800,
                                    align: 'right',
                                    allowBlank: false,
                                    listeners: {
                                        change: function () {
                                            calcCustomerProfit();
                                        }
                                    }
                                }
                            ]
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    border: false,
                    items: [{
                            xtype: 'compositefield',
                            fieldLabel: 'Hub',
                            labelAlign: 'top',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    name: 'hubpd_old',
                                    id: 'hubpd_old',
                                    align: 'right',
                                    readOnly: true,
                                    width: '50',
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    name: 'hubpd_new',
                                    id: 'hubpd_new',
                                    fieldLabel: 'Hub %',
                                    align: 'right',
                                    width: '50',
                                    tabIndex: 801,
                                    allowBlank: false,
                                    listeners: {
                                        change: function () {
                                            calcCustomerProfit()
                                        }
                                    }
                                }
                            ]
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    border: false,
                    items: [{
                            xtype: 'compositefield',
                            fieldLabel: 'Incentive',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    name: 'incentivepd_old',
                                    id: 'incentivepd_old',
                                    align: 'right',
                                    readOnly: true,
                                    width: '50',
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    align: 'right',
                                    name: 'incentivepd_new',
                                    id: 'incentivepd_new',
                                    fieldLabel: 'Incentive %',
                                    width: '50',
                                    tabIndex: 802,
                                    allowBlank: false,
                                    listeners: {
                                        change: function () {
                                            calcCustomerProfit();
                                        }
                                    }
                                }
                            ]
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    border: false,
                    items: [{
                            xtype: 'compositefield',
                            fieldLabel: 'Technology',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    name: 'technologypd_old',
                                    id: 'technologypd_old',
                                    readOnly: true,
                                    width: '50',
                                    align: 'right',
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    name: 'technologypd_new',
                                    id: 'technologypd_new',
                                    fieldLabel: 'Technology %',
                                    width: '50',
                                    align: 'right',
                                    tabIndex: 803,
                                    allowBlank: false,
                                    listeners: {
                                        change: function () {
                                            calcCustomerProfit();
                                        }
                                    }
                                }
                            ]
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    border: false,
                    items: [{
                            xtype: 'compositefield',
                            fieldLabel: 'Customer',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    name: 'customerpd_old',
                                    id: 'customerpd_old',
                                    readOnly: true,
                                    width: '50',
                                    align: 'right',
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    name: 'customerpd_new',
                                    id: 'customerpd_new',
                                    fieldLabel: 'Customer %',
                                    width: '50',
                                    align: 'right',
                                    tabIndex: 804,
                                    allowBlank: false,
                                    readOnly: true
                                }
                            ]
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.25,
                    border: false,
                    items: [{
                            xtype: 'numberfield',
                            emptyText: 'Offer To Place',
                            name: 'offerlacement',
                            id: 'offerlacement',
                            fieldLabel: 'Offer To Place',
                            anchor: '98%',
                            align: 'right',
                            tabIndex: 805,
                            maxValue: 60,
                            allowBlank: false,
                            listeners: {
                                change: function () {
                                    var offerlacement = Ext.getCmp('offerlacement').getValue();
                                    if (offerlacement < 60) {
                                        var custpercent = Ext.getCmp('customerpd_new').getValue();
                                        var diffPercent = parseInt(offerlacement) - parseInt(custpercent);
                                        console.log('diffPercent', diffPercent);
                                        Ext.getCmp('difference').setValue(diffPercent);
                                    } else {
                                        Ext.MessageBox.alert('Error', 'Value shouldnot exceed 60');
                                    }
                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.25,
                    border: false,
                    items: [{
                            xtype: 'numberfield',
                            emptyText: 'Difference',
                            name: 'difference',
                            id: 'difference',
                            fieldLabel: 'Difference',
                            anchor: '98%',
                            align: 'right',
                            tabIndex: 806,
                            maxLength: 10,
                            allowBlank: false,
                            readOnly: true
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.25,
                    border: false,
                    items: [{
                            xtype: 'numberfield',
                            emptyText: 'Promotion',
                            name: 'promotion',
                            id: 'promotion',
                            align: 'right',
                            fieldLabel: 'Promotion',
                            anchor: '98%',
                            tabIndex: 807,
                            maxLength: 10,
                            allowBlank: false
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.25,
                    border: false,
                    items: [{
                            xtype: 'numberfield',
                            emptyText: 'Supplier',
                            name: 'supplier',
                            id: 'supplier',
                            fieldLabel: 'Supplier',
                            align: 'right',
                            anchor: '98%',
                            tabIndex: 808,
                            maxLength: 10,
                            allowBlank: false
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 1,
                    border: false,
                    items: [{
                            xtype: 'textfield',
                            emptyText: 'Narration',
                            name: 'narration',
                            id: 'narration',
                            fieldLabel: '',
                            anchor: '99%',
                            align: 'right',
                            tabIndex: 809,
                            maxLength: 250,
                            allowBlank: false
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    labelWidth: 80,
                    columnWidth: 0.33,
                    border: false,
                    items: [{
                            xtype: 'datefield',
                            emptyText: 'Start Date',
                            name: 'offer_start',
                            id: 'offer_start',
                            fieldLabel: 'Start Date',
                            anchor: '98%',
                            tabIndex: 810,
                            format: 'd-m-Y',
                            minValue: new Date(),
                            maxLength: 10,
                            allowBlank: false
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.33,
                    labelWidth: 80,
                    border: false,
                    items: [{
                            xtype: 'combo',
                            displayField: 'bname',
                            valueField: 'bid',
                            mode: 'local',
                            fieldLabel: 'Offer Valid',
                            hiddenName: 'offer_type',
                            id: 'offer_type',
                            emptyText: 'Offer Valid',
                            allowBlank: false,
                            anchor: '98%',
                            tabIndex: 811,
                            triggerAction: 'all',
                            lazyRender: true,
                            typeAhead: true,
                            minChars: 2,
                            editable: true,
                            store: new Ext.data.JsonStore({
                                fields: ['bid', 'bname'],
                                data: [{bid: 'Till stock lasts', bname: 'Till stock lasts'},
                                    {bid: 'Particular Date', bname: 'Particular Date'}]
                            }), listeners: {
                                select: function () {
                                    if (Ext.getCmp('offer_type').getValue() == 'Till stock lasts') {
                                        Ext.getCmp('offer_end').disable();
                                    } else {
                                        Ext.getCmp('offer_end').enable();
                                    }

                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.34,
                    labelWidth: 80,
                    border: false,
                    items: [{
                            xtype: 'datefield',
                            emptyText: 'End Date',
                            name: 'offer_end',
                            id: 'offer_end',
                            fieldLabel: 'End Date',
                            anchor: '98%',
                            format: 'd-m-Y',
                            minValue: new Date(),
                            tabIndex: 812,
                            maxLength: 10,
                            allowBlank: false
                        }]
                }
            ],
            listeners: {
                afterrender: function () {
//                    if (Ext.isEmpty(Ext.getCmp('brand_id').getValue())) {
//                        var recordSelected = Ext.getCmp('comboMasterBrandsstatus').getStore().getAt(0);
//                        Ext.getCmp('comboMasterBrandsstatus').setValue(recordSelected.get('id'));
//                    }
                }
            }
        });
        return _formToSaveOfferDetails;
    };
    var panelforCouponOfrMgmtDetails = function (id) {
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Discount Coupons',
            id: id,
            items: [couponOfferMgmtGrid(), new Ext.Panel({
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    id: 'CouponOfrMgmtparentPanel',
                    height: winsize.height * 0.6,
                    layout: 'border',
                    items: [{
                            region: 'center',
                            items: couponOfferMgmtView()
                        }
                    ]
                })
            ]
        });
        return panel;
    };
    var couponOfferMgmtStore = function () {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCouponOfferMgmt',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fpo_id',
                root: 'data'
            }, ['bom_id', 'bom_bmdCompany', 'bom_bmdHub', 'bom_bmdIncentive', 'bom_bmdTechnology', 'bom_bmdCustomer', 'bom_offrPlacement', 'bom_offrPromotion', 'bom_offrSupplier', 'stiid_itemmasterid',
                'bmo_offrrate', 'bom_typeName', 'bmo_Name', 'bom_status', 'bom_startdate', 'bom_enddate', 'bom_locked']),
            //sortInfo: {
            //    field: 'fpo_id',
            //    direction: "DESC"
            //},
            groupField: '',
            groupDir: 'ASC',
            //remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().selectRow(0);

                }
            }
        });
        return _store;
    };
    var couponOfferMgmtGrid = function () {

        var _poGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'bom_typeName'
                   
                }, {
                    type: 'string',
                    dataIndex: 'bmo_Name'
                }, {
                    type: 'string',
                    dataIndex: 'bom_startdate'
                }, {
                    type: 'string',
                    dataIndex: 'bom_enddate'
                },/* {
                    type: 'string',
                    dataIndex: 'bom_status'
                }*/{
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'bom_status'
                }
]
        });
        _poGridFilter.remote = true;
        _poGridFilter.autoReload = true;
        var _gridStore = couponOfferMgmtStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'couponOfferMgmtGridPanel',
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _poGridFilter],
            store: _gridStore,
            columns: [
                {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'bom_typeName',
                    tooltip: 'Type',
                    hideable: false
                },
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'bmo_Name',
                    tooltip: 'Name',
                    hideable: false
                }, {
                    header: 'Start Date',
                    sortable: true,
                    dataIndex: 'bom_startdate',
                    tooltip: 'Start Date',
                    hideable: false
                }, {
                    header: 'End Date',
                    sortable: true,
                    dataIndex: 'bom_enddate',
                    tooltip: 'End Date',
                    hideable: false
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'bom_status',
                    tooltip: 'Status',
                    hideable: false
                }, 
                /*{
                    xtype: 'actioncolumn',
                    //header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Action',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            var bom_locked = Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().getSelections()[0].data.bom_locked;
                            var bom_statu = Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().getSelections()[0].data.bom_statu;
                            //console.log('br_status:'+ br_status);
                                if ((bom_locked == 0) && (bom_statu == 'Active'))
                                    discountActionMenu1.showAt(e.getXY());
                                else
                                    discountActionMenu2.showAt(e.getXY());
                                
                            //action
                        }
                    }
                },*/
                {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            tooltip: 'Change Status',
                            getClass: function (v, meta, rec) {
                                if ((rec.get('bom_locked') == 0) && (rec.get('bom_status') == 'Active')) {
                                    return 'user_enabled';

                                } else {
                                    return 'hideicon';
                                }

                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var bom_id = record.data.bom_id;
                                var bom_typeName = record.data.bom_typeName;
                                Application.OfferManagement.change_status(bom_id, bom_typeName);
                            }
                        }]
                }

            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: couponGridSelectionChanged
                }
            }),
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                    _gridStore.load();
                }
            },
            tbar: [
                {
                    xtype: 'button',
                    text: 'Create Discount Coupon',
                    tooltip: 'Create Discount Coupon',
                    iconCls: 'finascop_add',
                    handler: function () {
                        Application.OfferManagement.couponOfferWindow()
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
        });
        return _gridPanel;
    };
    /*var discountActionMenu1 = new Ext.menu.Menu({
        items: [{
                text: "Deactivate",
                handler: function () {
                                //var record = grid.store.getAt(rowIndex);
                                var bom_id = Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().getSelections()[0].data.bom_id;
                                var bom_typeName = Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().getSelections()[0].data.bom_typeName;
                                Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Discount Coupon?', function (btn, text) {
                                    if (btn == 'yes')
                                    {
                                        Application.OfferManagement.change_status(bom_id, bom_typeName);
                                    }
                                });
                                
                            }
            }]
    });
    var discountActionMenu2 = new Ext.menu.Menu({
        items: [{
                text: "Activate",
                handler: function () {
                                var bom_id = Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().getSelections()[0].data.bom_id;
                                var bom_typeName = Ext.getCmp('couponOfferMgmtGridPanel').getSelectionModel().getSelections()[0].data.bom_typeName;
                                Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Discount Coupon?', function (btn, text) {
                                    if (btn == 'yes')
                                    {
                                        Application.OfferManagement.change_status(bom_id, bom_typeName);
                                    }
                                });
                            }
            }]
    });*/
    var couponOfferMgmtView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: 'View Offer Details',
            autoScroll: true,
            id: 'CouponOfferManagementViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Offer On </th><td> {bom_typeName} </td></tr>',
                    '<tr><th width="40%">Coupon Code </th><td> {bom_offerCode} </td></tr>',
                    '<tpl if="bom_offerType != \'1\'"><tr><th width="40%">Offer Applied On</th><td> {bmo_Name} </td></tr></tpl>',
                    '<tr><th width="40%">Offer to place </th><td> {bom_offrPlacement} </td></tr>',
                    '<tr><th width="40%">Company </th><td> {bom_bmdCompany} </td></tr>',
                    '<tr><th width="40%">Hub</th><td> {bom_bmdHub} </td></tr>',
                    '<tr><th width="40%">Incentive </th><td> {bom_bmdIncentive} </td></tr>',
                    '<tr><th width="40%">Technology </th><td> {bom_bmdTechnology} </td></tr>',
                    '<tr><th width="40%">Promotion </th><td> {bom_offrPromotion} </td></tr>',
                    '<tr><th width="40%">Supplier </th><td> {bom_offrSupplier} </td></tr>',
                    '<tr><th width="40%">Narration </th><td> {bom_narration} </td></tr>',
                    '<tr><th width="40%">Start date </th><td> {bom_startdate} </td></tr>',
                    '<tr><th width="40%">Offer Type</th><td> {bom_offfrvalidtype} </td></tr>',
                    '<tpl if="bom_offfrvalidtype != \'Till stock lasts\'"><tr><th width="40%">End Date</th><td> {bom_enddate} </td></tr></tpl>',
                    '',
                    '</td></tr>',
                    '</table>',
                    '</div>'
                    )
        });
    };
    var offerMgmtinCouponCreateForm = function () {
        var itemStore = itemStorefn();
        var categoryStore = couponcategoryStore();
        var _formToSaveCouponOfferDetails = new Ext.FormPanel({
            id: 'couponOfferSaveForm',
            frame: false,
            border: false,
            autoHeight: true,
            labelWidth: 100,
            layout: 'column',
            bodyStyle: {"padding": "5px 5px 5px 10px"},
            items: [{
                    columnWidth: .6,
                    layout: 'form',
                    border: false,
                    style: 'margin-bottom:3px;',
                    items: [{
                            xtype: 'hidden',
                            id: 'bom_id',
                            name: 'bom_id',
                            anchor: '95%',
                        }, {
                            fieldLabel: 'Offers',
                            xtype: 'radiogroup',
                            id: 'bom_offerType',
                            hideLabel: true,
                            items: [
                                {boxLabel: 'Coupon Offer', name: 'bom_offerType', inputValue: 1},
                                {boxLabel: 'Category Offer', name: 'bom_offerType', inputValue: 2},
                                {boxLabel: 'Item Offer', name: 'bom_offerType', inputValue: 3}
                            ], listeners: {
                                change: function (rgp, checked) {
                                    if (rgp.getValue() == '1')
                                    {
                                        Ext.getCmp('couponItems').disable();
                                        Ext.getCmp('product_category').disable();
                                    } else
                                    if (rgp.getValue() == '2')
                                    {
                                        Ext.getCmp('couponItems').disable();
                                        Ext.getCmp('product_category').enable();
                                        Ext.getCmp('product_category').allowBlank = false;
                                    } else if (rgp.getValue() == '3')
                                    {
                                        Ext.getCmp('couponItems').enable();
                                        Ext.getCmp('product_category').disable();
                                        Ext.getCmp('couponItems').allowBlank = false;
                                    }
                                }
                            }
                        }
                    ]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.5,
                    border: false,
                    style: 'margin-bottom:3px;',
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Category',
                            id: 'product_category',
                            name: 'product_category',
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '95%',
                            store: categoryStore,
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'sub_category',
                            valueField: 'sub_category_id',
                            hiddenName: 'product_category',
                            tabIndex: 400,
                            disabled: true
                        }
                    ]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.5,
                    border: false,
                    style: 'margin-bottom:3px;',
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Items',
                            emptyText: 'Item Search',
                            id: 'couponItems',
                            name: 'couponItems',
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '95%',
                            store: itemStore,
                            //triggerAction: 'all',
                            hideTrigger: true,
                            minChars: 3,
                            displayField: 'stit_SKU',
                            valueField: 'stit_ID',
                            hiddenName: 'couponItems',
                            tabIndex: 401,
                            disabled: true
                        }
                    ]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.5,
                    border: false,
                    style: 'margin-bottom:3px;',
                    items: [{
                            xtype: 'numberfield',
                            name: 'offerlacement',
                            id: 'offerlacement',
                            fieldLabel: 'Offer To Place',
                            anchor: '95%',
                            align: 'right',
                            tabIndex: 402,
                            maxValue: 60,
                            allowBlank: false,
                            msgTarget: "qtip"
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.5,
                    border: false,
                    style: 'margin-bottom:3px;',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Coupon code',
                            id: 'bom_offerCode',
                            name: 'bom_offerCode',
                            anchor: '95%',
                            maxLength: 250,
                            tabIndex: 403,
                            allowBlank: false,
                            msgTarget: "qtip"
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 1,
                    border: false,
                    xtype: 'fieldset',
                    title: 'Offer to be distributed',
                    autoHeight: true,
                    items: [{
                            xtype: 'compositefield',
                            hideLabel: true,
                            combineErrors: false,
                            items: [{
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.167,
                                    border: false,
                                    items: [{
                                            xtype: 'numberfield',
                                            id: 'bom_bmdCompany',
                                            name: 'bom_bmdCompany',
                                            allowNegative: false,
                                            allowDecimals: false,
                                            fieldLabel: 'Company',
                                            anchor: '95%',
                                            tabIndex: 404,
                                            allowBlank: false,
                                            msgTarget: "qtip"
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.167,
                                    border: false,
                                    items: [{
                                            xtype: 'numberfield',
                                            id: 'bom_bmdHub',
                                            name: 'bom_bmdHub',
                                            allowNegative: false,
                                            allowDecimals: false,
                                            fieldLabel: 'Hub',
                                            anchor: '95%',
                                            tabIndex: 405,
                                            allowBlank: false,
                                            msgTarget: "qtip"
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.167,
                                    border: false,
                                    items: [{
                                            xtype: 'numberfield',
                                            id: 'bom_bmdIncentive',
                                            name: 'bom_bmdIncentive',
                                            allowNegative: false,
                                            allowDecimals: true,
                                            allowBlank: false,
                                            fieldLabel: 'Incentive',
                                            anchor: '95%',
                                            tabIndex: 406,
                                            msgTarget: "qtip"
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.167,
                                    border: false,
                                    items: [{
                                            xtype: 'numberfield',
                                            id: 'bom_bmdTechnology',
                                            name: 'bom_bmdTechnology',
                                            allowNegative: false,
                                            allowDecimals: true,
                                            allowBlank: false,
                                            fieldLabel: 'Technology',
                                            anchor: '95%',
                                            tabIndex: 407,
                                            msgTarget: "qtip"
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.167,
                                    border: false,
                                    items: [{
                                            xtype: 'numberfield',
                                            id: 'bom_offrSupplier',
                                            name: 'bom_offrSupplier',
                                            allowNegative: false,
                                            allowDecimals: true,
                                            allowBlank: false,
                                            fieldLabel: 'Supplier',
                                            anchor: '95%',
                                            tabIndex: 408,
                                            msgTarget: "qtip"
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.167,
                                    border: false,
                                    items: [{
                                            xtype: 'numberfield',
                                            id: 'bom_offrPromotion',
                                            name: 'bom_offrPromotion',
                                            allowNegative: false,
                                            allowDecimals: true,
                                            allowBlank: false,
                                            fieldLabel: 'Promotion',
                                            anchor: '95%',
                                            tabIndex: 409,
                                            msgTarget: "qtip"
                                        }]
                                }]
                        }
                    ]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 1,
                    border: false,
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Narration',
                            name: 'narration',
                            id: 'narration',
                            anchor: '95%',
                            align: 'right',
                            tabIndex: 410,
                            maxLength: 250,
                            allowBlank: false
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    labelWidth: 80,
                    columnWidth: 0.33,
                    border: false,
                    items: [{
                            xtype: 'datefield',
                            emptyText: 'Start Date',
                            name: 'offer_start',
                            id: 'offer_start',
                            fieldLabel: 'Start Date',
                            anchor: '90%',
                            tabIndex: 411,
                            format: 'd-m-Y',
                            minValue: new Date(),
                            maxLength: 10,
                            allowBlank: false
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.33,
                    labelWidth: 80,
                    border: false,
                    items: [{
                            xtype: 'combo',
                            displayField: 'bname',
                            valueField: 'bid',
                            mode: 'local',
                            fieldLabel: 'Offer Valid',
                            hiddenName: 'offer_type',
                            id: 'offer_type',
                            emptyText: 'Offer Valid',
                            allowBlank: false,
                            anchor: '90%',
                            tabIndex: 412,
                            triggerAction: 'all',
                            lazyRender: true,
                            typeAhead: true,
                            minChars: 2,
                            editable: true,
                            store: new Ext.data.JsonStore({
                                fields: ['bid', 'bname'],
                                data: [{bid: 'Till stock lasts', bname: 'Till stock lasts'},
                                    {bid: 'Particular Date', bname: 'Particular Date'}]
                            }), listeners: {
                                select: function () {
                                    if (Ext.getCmp('offer_type').getValue() == 'Till stock lasts') {
                                        Ext.getCmp('offer_end').disable();
                                    } else {
                                        Ext.getCmp('offer_end').enable();
                                    }

                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.33,
                    labelWidth: 80,
                    border: false,
                    items: [{
                            xtype: 'datefield',
                            emptyText: 'End Date',
                            name: 'offer_end',
                            id: 'offer_end',
                            fieldLabel: 'End Date',
                            anchor: '90%',
                            format: 'd-m-Y',
                            minValue: new Date(),
                            tabIndex: 413,
                            maxLength: 10,
                            allowBlank: false
                        }]
                }, {
                    columnWidth: .6,
                    layout: 'form',
                    border: false,
                    style: 'margin-bottom:3px;',
                    items: [{
                            fieldLabel: 'Usage',
                            xtype: 'radiogroup',
                            id: 'bom_use',
                            hideLabel: true,
                            items: [
                                {boxLabel: 'One time', name: 'bom_use', inputValue: 1},
                                {boxLabel: 'Multi time', name: 'bom_use', inputValue: 2}
                            ]
                        }
                    ]
                }
            ],
            listeners: {
                afterrender: function () {
                }
            }
        });
        return _formToSaveCouponOfferDetails;
    };
    var saveOfferCoupon = function () {
        var offerSaveForm = Ext.getCmp('couponOfferSaveForm').getForm();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (offerSaveForm.isValid()) {
            var form_data = {
                form: Ext.getCmp('couponOfferSaveForm').getForm().getValues()
            };
            var params = {
                action: 'Insert Coupon Offer %',
                module: 'offer_management',
                op: 'saveOfferPercentage',
                extrainfo: 'Save Offer Percentage',
                id: 0
            };
            APICall(params, Application.OfferManagement.saveCouponOffer, form_data);
        } else {
            Application.example.msg('Error', 'Check the required fields');
        }
    };
    return{
        Cache: {},
        init: function () {
            var _panelId = 'panelidforOrderManagement';
            var _orderManagementPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_orderManagementPanel)) {
                _orderManagementPanel = panelforOfrMgmtDetails(_panelId);
                Application.UI.addTab(_orderManagementPanel);
                _orderManagementPanel.doLayout();
            } else {
                Application.UI.addTab(_orderManagementPanel);
            }
        }, ViewMode: function () {

            var bom_id = arguments[0];

            Ext.Ajax.request({
                url: modURL + '&op=ofperDetailsView',
                method: 'POST',
                params: {bom_id: bom_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('orderManagementViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('orderManagementViewPanel').doLayout();

        }, AddOffer: function () {
            var fpo_id = arguments[0];
            var item_id = arguments[1];
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.getCmp('OfrMgmtparentPanel').doLayout();
            Ext.getCmp('OfrMgmtparentPanel').setTitle('Offer Details');
            var masterForm = Ext.getCmp('offerSaveForm').getForm();
            masterForm.load({
                params: {
                    fpo_id: fpo_id,
                    item_id: item_id,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                url: modURL + '&op=offerFormLoad',
                waitMsg: 'Loading...',
                success: function (form, action) {
                    //Ext.getCmp('offerSaveForm').getForm().reset();
                    var tmp = Ext.decode(action.response.responseText);
                },
                failure: function (form, action) {
                    Ext.Msg.alert("Error.", "This error");
                }
            });
        }, saveOfferPercentage: function () {

            var offerSaveForm = Ext.getCmp('offerSaveForm').getForm();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (offerSaveForm.isValid()) {
                offerSaveForm.submit({
                    url: modURL + '&op=saveOfferPercentage',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        PoId: Application.OfferManagement.Cache.stiid_fpoid,
                        itemId: Application.OfferManagement.Cache.stiid_itemmasterid,
                        itemsRemaining: Application.OfferManagement.Cache.iItemsRemaining,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.msg);
                            Ext.getCmp('windowFinascopOfferDetails').close();
                            Ext.getCmp('offerMgmtGridPanel').getStore().load();
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.MessageBox.alert('Error', tmp.msg);
                            Ext.getCmp('offerSaveForm').getForm().reset()
                        } else {
                            Ext.MessageBox.alert('Error', tmp.msg);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType == 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Status', result.error);
                        } else {
                            Application.example.msg('Error', 'Check the required fields');
                        }
                    }
                });
            } else {
                Application.example.msg('Error', 'Check the required fields');
            }
        }, initCoupon: function () {
            var _panelId = 'panelidforCouponOfferManagement';
            var _couponOfferManagementPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_couponOfferManagementPanel)) {
                _couponOfferManagementPanel = panelforCouponOfrMgmtDetails(_panelId);
                Application.UI.addTab(_couponOfferManagementPanel);
                _couponOfferManagementPanel.doLayout();
            } else {
                Application.UI.addTab(_couponOfferManagementPanel);
            }
        }, CouponViewMode: function () {
            var bom_id = arguments[0];

            Ext.Ajax.request({
                url: modURL + '&op=ofperDetailsViewCoupon',
                method: 'POST',
                params: {bom_id: bom_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('CouponOfferManagementViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('CouponOfferManagementViewPanel').doLayout();
        }, couponOfferWindow: function () {
            var offerMgmtinCouponForm = offerMgmtinCouponCreateForm();
            var vendorPonewWindow = new Ext.Window({
                id: "windowForCouponOffer",
                title: 'Coupon Code Offer',
                shadow: false,
                height: 520,
                width: winsize.width * 0.6,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [offerMgmtinCouponForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 613,
                        handler: function () {
                            Ext.getCmp('windowForCouponOffer').close();
                        }
                    } /*     <?php if (user_access("offer_management", "saveCouponOffer")) { ?> */,
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Save',
                        tabIndex: 612,
                        handler: function () {
                            saveOfferCoupon();
                        }
                    } /*<?php } ?> */
                ]
            });

            vendorPonewWindow.doLayout();
            vendorPonewWindow.show();
            vendorPonewWindow.center();
        }, saveCouponOffer: function () {
            var offerSaveForm = Ext.getCmp('couponOfferSaveForm').getForm();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (offerSaveForm.isValid()) {
                offerSaveForm.submit({
                    url: modURL + '&op=saveCouponOffer',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.msg);
                            Ext.getCmp('windowForCouponOffer').close();
                            Ext.getCmp('couponOfferMgmtGridPanel').getStore().load();
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.MessageBox.alert('Error', tmp.msg);
                            Ext.getCmp('couponOfferSaveForm').getForm().reset()
                        } else {
                            Ext.MessageBox.alert('Error', tmp.msg);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType == 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Status', result.error);
                        } else {
                            Application.example.msg('Error', 'Check the required fields');
                        }
                    }
                });
            } else {
                Application.example.msg('Error', 'Check the required fields');
            }
        }, change_status: function () {
            Application.OfferManagement.bom_id = arguments[0];
            Application.OfferManagement.bom_typeName = arguments[1];
            var form_data = {
                bom_id: Application.OfferManagement.bom_id,
                bom_typeName: Application.OfferManagement.bom_typeName
            };
            var params = {
                action: 'Update',
                module: 'offer Management',
                op: 'offerStatus',
                extrainfo: 'asd',
                id: Application.OfferManagement.bom_id
            };
            APICall(params, Application.OfferManagement.StatusChange, form_data);
        }, StatusChange: function () {

            Ext.Ajax.request({waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'offerStatus',
                    bom_id: Application.OfferManagement.bom_id,
                    bom_typeName: Application.OfferManagement.bom_typeName
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {
                        Application.example.msg('Success', 'Offer deactivated.');
                        Ext.getCmp('userPanel').getStore().reload();
                    }
                }
            });
        }
    }
}();
