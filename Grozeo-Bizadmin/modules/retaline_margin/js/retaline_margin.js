Application.Retaline_margin = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_margin';
    var winsize = Ext.getBody().getViewSize();
    var RECS_PER_PAGE = 15;
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };

    var proxy = new Ext.data.HttpProxy({
        url: modURL,
        method: 'post'
    });

    var gridSelectionChangedmargin = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMargindata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewMargindata').getSelectionModel().getSelections()[0].data.bmd_id;
            Application.Retaline_margin.ViewMargin(ID);
        }
    };
    var MarginMasterStore = function () {
        var _marginsMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetalimMarginDetail',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'bmd_id',
                root: 'data'
            }, ['bmd_id', 'bmd_name', 'status', 'is_default']),
            sortInfo: {
                field: 'bmd_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewMargindata').getView().refresh();
                    Ext.getCmp('gridpanelMasterDataviewMargindata').getSelectionModel().selectRow(0);
                }
            }
        });

        return _marginsMasterStore;
    };
    var MarginMainGrid = function () {
        var _marginStore = MarginMasterStore();
        var _marginGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'bmd_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }
            ]
        });
        _marginGridFilter.remote = true;
        _marginGridFilter.autoReload = true;
        var _marginmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _marginStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewMargindata',
//            view: new Ext.grid.GroupingView({
//                forceFit: true,
//                deferEmptyText: false,
//                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
//                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
//            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _marginGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    dataIndex: 'bmd_name',
                    sortable: true,
                    tooltip: 'Margin Name',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                },
                {
                    header: 'Action',
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    items: [{
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _isDefault = data.is_default;
                                if (_isDefault == 0) {
                                    this.items[0].tooltip = 'Set Default';
                                    return 'margindistributin_inactive';
                                }
                                else {
                                    this.items[0].tooltip = 'Clear Default';
                                    return 'margindistributin_active';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                Ext.Ajax.request({waitMsg: 'Processing',
                                    url: modURL,
                                    params: {
                                        op: 'setDefault',
                                        bmd_id: record.get('bmd_id')
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    },
                                    success: function (response, options) {
                                        eval("var tmp=" + response.responseText);
                                        if (tmp.success === true) {
                                            Application.example.msg('Notification', tmp.msg);
                                            Ext.getCmp('gridpanelMasterDataviewMargindata').getStore().reload();
                                        }
                                    }
                                });
                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('is_default') == 1)
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _marginStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedmargin
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('bmd_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.Retaline_margin.Cache.bmd_id = ID;
                        Ext.getCmp('formpanelMasterMargin').hide();
                        Application.Retaline_margin.ViewMargin(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _marginStore.load();
                }
            },
            tbar: [{
                    text: 'Create Retail Margin',
                    tooltip: 'Create Retail Margin',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Retaline_margin.MarginAddEdit = 'Add';
                        var marginForm = Ext.getCmp('formpanelMasterMargin').getForm();
                        Ext.getCmp('panelMasterMarginParent').setTitle('Create Retail Margin Details');
                        loadedForm = null;
                        marginForm.reset();
                        //Ext.getCmp('tag_name').focus(false, 100);
                        Ext.getCmp('buttonMasterMarginSave').show();
                        Ext.getCmp('buttonMasterMarginCancel').show();
                        Ext.getCmp('formpanelMasterMargin').show();
                        Ext.getCmp('comboMasterMarginStatus').setValue(1);
                        Ext.getCmp('panelMasterMarginDetailsView').hide();
                        Ext.getCmp('panelMasterMarginParent').doLayout();
                    }
                }]
        });
        return _marginmaingridPanel;
    };
    var MarginMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterMarginDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>  {bmd_name} </td></tr>',
//                    '<tr><th width="40%">Management </th><td>  {bmd_management} </td></tr>',
                    '<tr><th width="40%">Company </th><td>  {bmd_company} </td></tr>',
                    '<tr><th width="40%">Central Store </th><td>  {bmd_hub} </td></tr>',
                    '<tr><th width="40%">Distributor </th><td>  {bmd_distributor} </td></tr>',
                    '<tr><th width="40%">Retailer </th><td>  {bmd_retailor} </td></tr>',
//                    '<tr><th width="40%">Technology </th><td>  {bmd_technology} </td></tr>',
//                    '<tr><th width="40%">Promotion </th><td>  {bmd_promotion} </td></tr>',
                    '<tr><th width="40%">Operations </th><td>  {bmd_incentive} </td></tr>',
//                    '<tr><th width="40%">Logistics </th><td>  {bmd_logistics} </td></tr>',
                    '<tr><th width="40%">Driver </th><td>  {bmd_driver} </td></tr>',
                    '<tr><th width="40%">Courier </th><td>  {bmd_courier} </td></tr>',
//                    '<tr><th width="40%">Pickup </th><td>  {bmd_pickup} </td></tr>',
//                    '<tr><th width="40%">Bank </th><td>  {bmd_bank} </td></tr>',
                    '<tr><th width="40%">Balance </th><td>  {bmd_customer} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var MarginForm = function () {
        var _marginFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterMargin',
            frame: false,
            border: false,
            hidden: true,
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
                    fieldLabel: 'Margin Name',
                    id: 'bmd_name',
                    name: 'n[bmd_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 1,
                    maxValue: 100,
                    maxLength: 20,
                    listeners: {
                        afterrender: function (field) {
                            Ext.defer(function () {
                                field.focus(true, 100);
                            }, 1);
                        }
                    }
                },
                {
                    layout: 'column',
                    border: false,
                    items: [
//                        {
//                            columnWidth: .5,
//                            layout: 'form',
//                            border: false,
//                            items: [
//                                {
//                                    xtype: 'numberfield',
//                                    fieldLabel: 'Management',
//                                    id: 'bmd_management',
//                                    name: 'n[bmd_management]',
//                                    anchor: '98%',
//                                    allowBlank: false,
//                                    tabIndex: 1,
//                                    hidden:true,
//                                    value:0,
//                                    maxValue: 100,
//                                    maxLength: 20,
//                                    listeners: {
//                                        change: function () {
//                                            customerpercentcalculus();
//                                        }
//                                    }
//                                },
//                            ]
//                        },
                        {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Company',
                                    id: 'bmd_company',
                                    name: 'n[bmd_company]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 1,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                            customerpercentcalculus();
                                        }
                                    }
                                },
                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Central Store',
                                    id: 'bmd_hub',
                                    name: 'n[bmd_hub]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 1,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                            customerpercentcalculus();
                                        }
                                    }
                                }
                            ]
                        }

                    ]

                },
                {
                    layout: 'column',
                    border: false,
                    items: [
                        {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Distributor',
                                    id: 'bmd_distributor',
                                    name: 'n[bmd_distributor]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 1,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                            customerpercentcalculus();
                                        }
                                    }
                                }


                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Retailer',
                                    id: 'bmd_retailor',
                                    name: 'n[bmd_retailor]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 1,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                            customerpercentcalculus();
                                        }
                                    }
                                }
                            ]
                        },
                    ]

                },
//                {
//                    layout: 'column',
//                    border: false,
//                    items: [
//                        {
//                            columnWidth: .5,
//                            layout: 'form',
//                            border: false,
//                            items: [
//                                {
//                                    xtype: 'numberfield',
//                                    fieldLabel: 'Technology',
//                                    id: 'bmd_technology',
//                                    name: 'n[bmd_technology]',
//                                    anchor: '98%',
//                                    allowBlank: false,
//                                    tabIndex: 1,
//                                    maxValue: 100,
//                                    maxLength: 20,
//                                    listeners: {
//                                        change: function () {
//                                            customerpercentcalculus();
//                                        }
//                                    }
//                                }
//                            ]
//                        }
//
//                    ]
//
//                },
                {
                    layout: 'column',
                    border: false,
                    items: [
//                        {
//                            columnWidth: .5,
//                            layout: 'form',
//                            border: false,
//                            items: [
//                                {
//                                    xtype: 'numberfield',
//                                    fieldLabel: 'Promotion',
//                                    id: 'bmd_promotion',
//                                    name: 'n[bmd_promotion]',
//                                    anchor: '98%',
//                                    allowBlank: false,
//                                    tabIndex: 1,
//                                    maxValue: 100,
//                                    maxLength: 20,
//                                    listeners: {
//                                        change: function () {
//                                            customerpercentcalculus();
//                                        }
//                                    }
//                                }
//                            ]
//                        },
                        {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Driver',
                                    id: 'bmd_driver',
                                    name: 'n[bmd_driver]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 1,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                            customerpercentcalculus();
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Operations',
                                    id: 'bmd_incentive',
                                    name: 'n[bmd_incentive]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 1,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                            customerpercentcalculus();
                                        }
                                    }
                                }
                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Courier',
                                    id: 'bmd_courier',
                                    name: 'n[bmd_courier]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 1,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                            customerpercentcalculus();
                                        }
                                    }
                                }

                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [mkCombo({
                                    "type": STATUS_COMBO_DATA,
                                    "value": "id",
                                    "display": "text",
                                    "name": "n[status]",
                                    "fieldLabel": "Status",
                                    "tabIndex": 2,
                                    "emptyText": "Set status..",
                                    "id": 'comboMasterMarginStatus'
                                })

                            ]
                        }

                    ]

                },
//                {
//                    layout: 'column',
//                    border: false,
//                    items: [
//                        {
//                            columnWidth: .5,
//                            layout: 'form',
//                            border: false,
//                            items: [
//                                {
//                                    xtype: 'numberfield',
//                                    fieldLabel: 'Logistics',
//                                    id: 'bmd_logistics',
//                                    name: 'n[bmd_logistics]',
//                                    anchor: '98%',
//                                    allowBlank: false,
//                                    tabIndex: 1,
//                                    maxValue: 100,
//                                    maxLength: 20,
//                                    listeners: {
//                                        change: function () {
//                                            customerpercentcalculus();
//                                        }
//                                    }
//                                }
//                            ]
//                        },
//                        
//
//                    ]
//
//                },
//                {
//                    layout: 'column',
//                    border: false,
//                    items: [{
//                            columnWidth: .5,
//                            layout: 'form',
//                            border: false,
//                            items: [
//                                
//                            ]
//                        },
//                        {
//                            columnWidth: .5,
//                            layout: 'form',
//                            border: false,
//                            items: [
//                                {
//                                    xtype: 'numberfield',
//                                    fieldLabel: 'Pickup',
//                                    id: 'bmd_pickup',
//                                    name: 'n[bmd_pickup]',
//                                    anchor: '98%',
//                                    allowBlank: false,
//                                    tabIndex: 1,
//                                    maxValue: 100,
//                                    maxLength: 20,
//                                    listeners: {
//                                        change: function () {
//                                            customerpercentcalculus();
//                                        }
//                                    }
//                                }
//                            ]
//                        }
//
//                    ]
//
//                },
                {
                    layout: 'column',
                    border: false,
                    items: [
//                        {
//                            columnWidth: .5,
//                            layout: 'form',
//                            border: false,
//                            items: [
//                                {
//                                    xtype: 'numberfield',
//                                    fieldLabel: 'Bank',
//                                    id: 'bmd_bank',
//                                    name: 'n[bmd_bank]',
//                                    anchor: '98%',
//                                    allowBlank: false,
//                                    tabIndex: 1,
//                                    maxValue: 100,
//                                    maxLength: 20,
//                                    listeners: {
//                                        change: function () {
//                                            customerpercentcalculus();
//                                        }
//                                    }
//                                }
//                            ]
//                        },
                        {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Balance',
                                    id: 'bmd_customer',
                                    name: 'n[bmd_customer]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    readOnly: true,
                                    hidden: true,
                                    tabIndex: 1,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                            customerpercentcalculus();
                                        }
                                    }
                                }
                            ]
                        }

                    ]

                },
                {
                    xtype: 'textfield',
                    id: 'bmd_id',
                    name: 'n[bmd_id]',
                    hidden: true
                }

            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('bmd_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterMarginStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterMarginStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _marginFormPanel;
    };
    var customerpercentcalculus = function () {
        //var management = (Ext.getCmp('bmd_management').getValue() > 0) ? Ext.getCmp('bmd_management').getValue() : 0.00;
        var company = (Ext.getCmp('bmd_company').getValue() > 0) ? Ext.getCmp('bmd_company').getValue() : 0.00;
        var centralStore = (Ext.getCmp('bmd_hub').getValue() > 0) ? Ext.getCmp('bmd_hub').getValue() : 0.00;
        var distributor = (Ext.getCmp('bmd_distributor').getValue() > 0) ? Ext.getCmp('bmd_distributor').getValue() : 0.00;
        var retailor = (Ext.getCmp('bmd_retailor').getValue() > 0) ? Ext.getCmp('bmd_retailor').getValue() : 0.00;
        //var technology = (Ext.getCmp('bmd_technology').getValue() > 0) ? Ext.getCmp('bmd_technology').getValue() : 0.00;
        //var promotion = (Ext.getCmp('bmd_promotion').getValue() > 0) ? Ext.getCmp('bmd_promotion').getValue() : 0.00;
        var incentive = (Ext.getCmp('bmd_incentive').getValue() > 0) ? Ext.getCmp('bmd_incentive').getValue() : 0.00;
        //var logistics = (Ext.getCmp('bmd_logistics').getValue() > 0) ? Ext.getCmp('bmd_logistics').getValue() : 0.00;
        var driver = (Ext.getCmp('bmd_driver').getValue() > 0) ? Ext.getCmp('bmd_driver').getValue() : 0.00;
        var courier = (Ext.getCmp('bmd_courier').getValue() > 0) ? Ext.getCmp('bmd_courier').getValue() : 0.00;
        //var pickup = (Ext.getCmp('bmd_pickup').getValue() > 0) ? Ext.getCmp('bmd_pickup').getValue() : 0.00;
        //var bank = (Ext.getCmp('bmd_bank').getValue() > 0) ? Ext.getCmp('bmd_bank').getValue() : 0.00;
        var management, technology, promotion, logistics, courier, pickup, bank = 0;
        //var bctotal = parseInt(management) + parseInt(company) + parseInt(centralStore) + parseInt(distributor) + parseInt(retailor) + parseInt(technology) + parseInt(promotion) + parseInt(incentive) + parseInt(logistics) + parseInt(driver) + parseInt(courier) + parseInt(pickup) + parseInt(bank);
        var bctotal = parseInt(company) + parseInt(centralStore) + parseInt(distributor) + parseInt(retailor) + parseInt(incentive);
        var highestNum = parseInt(driver) > parseInt(courier) ? parseInt(driver) : parseInt(courier);
        console.log('bctotal', bctotal);
        var cust = 100 - parseInt(bctotal);

        console.log('cust', cust);
        Ext.getCmp('bmd_customer').setValue(cust);
        var bmd_customer = (Ext.getCmp('bmd_customer').getValue() > 0) ? Ext.getCmp('bmd_customer').getValue() : 0.00;
        var total = parseInt(bctotal) + parseInt(highestNum);
        if (parseInt(total) > 100) {
            Ext.getCmp('formpanelMasterMargin').getForm().reset();
        }
    };
    var retalinMarginPanel = function (id) {
        var _mpanelforMargin = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Retail Margin',
            id: id,
            //iconCls: 'my-icon449',
            items: [MarginMainGrid(), new Ext.Panel({
                    title: 'Retail Margin Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterMarginParent',
                    height: winsize.height * 0.6,
                    items: [
                        MarginForm(), MarginMasterDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 5,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterMarginCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMargindata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewMargindata').getSelectionModel().getSelections()[0].data.bmd_id;
                                    Application.Retaline_margin.ViewMargin(ID);
                                }
                            }
                        }, {
                            text: "Save",
                            tabIndex: 4,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterMarginSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                if (Ext.getCmp('formpanelMasterMargin').getForm().isValid()) {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=saveMargin',
                                        method: 'POST',
                                        params: {
                                            bmd_name: Ext.getCmp('bmd_name').getValue(),
                                            //bmd_management: Ext.getCmp('bmd_management').getValue(),
                                            bmd_company: Ext.getCmp('bmd_company').getValue(),
                                            bmd_hub: Ext.getCmp('bmd_hub').getValue(),
                                            bmd_distributor: Ext.getCmp('bmd_distributor').getValue(),
                                            bmd_retailor: Ext.getCmp('bmd_retailor').getValue(),
                                            // bmd_technology: Ext.getCmp('bmd_technology').getValue(),
                                            //Promotion: Ext.getCmp('bmd_promotion').getValue(),
                                            bmd_incentive: Ext.getCmp('bmd_incentive').getValue(),
                                            //bmd_logistics: Ext.getCmp('bmd_logistics').getValue(),
                                            bmd_driver: Ext.getCmp('bmd_driver').getValue(),
                                            bmd_courier: Ext.getCmp('bmd_courier').getValue(),
                                            //bmd_pickup: Ext.getCmp('bmd_pickup').getValue(),
                                            //bmd_bank: Ext.getCmp('bmd_bank').getValue(),
                                            bmd_customer: Ext.getCmp('bmd_customer').getValue(),
                                            status: Ext.getCmp('comboMasterMarginStatus').getValue()
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg('Success', tmp.message);

                                                RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewMargindata'));
                                                Ext.getCmp('formpanelMasterMargin').getForm().reset();
                                                Ext.getCmp('gridpanelMasterDataviewMargindata').store.reload({
                                                    params: {
                                                        start: 0,
                                                        limit: RECS_PER_PAGE
                                                    }
                                                });

                                                Application.Retaline_margin.ViewMargin(tmp.data.bmd_id);

                                            } else if (tmp.success === true && tmp.valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else if (tmp.success === true && tmp.img_valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else {
                                                Ext.Msg.alert("Error", tmp.message);
                                            }
                                        },
                                        failure: function (response) {
                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                            Ext.MessageBox.alert('Error', tmp.message);
                                        }

                                    })
                                }
                                else {
                                    Ext.MessageBox.alert("Notification", "Please enter all required fields");
                                }
                            }

                        }

                    ]
                })
            ]
        });
        return _mpanelforMargin;
    };
    var gridSelectionChangedmarginB2B = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMargindataB2B').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewMargindataB2B').getSelectionModel().getSelections()[0].data.bmd_id;
            Application.Retaline_margin.ViewMarginB2B(ID);
        }
    };
    var gridSelectionChangedmarginItem = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelItemMargindata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelItemMargindata').getSelectionModel().getSelections()[0].data.rmim_bmd_id;
            Application.Retaline_margin.ViewItemMargin(ID);
        }
    };
    var MarginMasterStoreB2B = function () {
        var _marginsMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetalimMarginDetailB2B',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'bmd_id',
                root: 'data'
            }, ['bmd_id', 'bmd_name', 'status', 'is_default']),
            sortInfo: {
                field: 'bmd_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewMargindataB2B').getView().refresh();
                    Ext.getCmp('gridpanelMasterDataviewMargindataB2B').getSelectionModel().selectRow(0);
                }
            }
        });

        return _marginsMasterStore;
    };
    var retalinMarginB2BPanel = function (id) {
        var _mpanelforMargin = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Wholesale Margin',
            id: id,
            //iconCls: 'my-icon449',
            items: [MarginMainGridB2B(), new Ext.Panel({
                    title: 'Margin Distribution Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterMarginParentB2B',
                    height: winsize.height * 0.6,
                    items: [
                        MarginFormB2B(), MarginMasterDetailsViewB2B()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 8,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterMarginCancelB2B',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMargindataB2B').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewMargindataB2B').getSelectionModel().getSelections()[0].data.bmd_id;
                                    Application.Retaline_margin.ViewMarginB2B(ID);
                                }
                            }
                        }, {
                            text: "Save",
                            tabIndex: 7,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterMarginSaveB2B',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                if (Ext.getCmp('formpanelMasterMarginB2B').getForm().isValid()) {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=saveMarginB2B',
                                        method: 'POST',
                                        params: {
                                            bmd_name: Ext.getCmp('bmd_name_b2b').getValue(),
                                            bmd_management: Ext.getCmp('bmd_management_b2b').getValue(),
                                            bmd_company: Ext.getCmp('bmd_company_b2b').getValue(),
                                            bmd_hub: Ext.getCmp('bmd_hub_b2b').getValue(),
                                            bmd_distributor: Ext.getCmp('bmd_distributor_b2b').getValue(),
                                            status: Ext.getCmp('comboMasterMarginStatus_b2b').getValue()
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg('Success', tmp.message);

                                                RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewMargindataB2B'));
                                                Ext.getCmp('formpanelMasterMarginB2B').getForm().reset();
                                                Ext.getCmp('gridpanelMasterDataviewMargindataB2B').store.reload({
                                                    params: {
                                                        start: 0,
                                                        limit: RECS_PER_PAGE
                                                    }
                                                });

                                                Application.Retaline_margin.ViewMarginB2B(tmp.data.bmd_id);

                                            } else if (tmp.success === true && tmp.valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else if (tmp.success === true && tmp.img_valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else {
                                                Ext.Msg.alert("Error", tmp.message);
                                            }
                                        },
                                        failure: function (response) {
                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                            Ext.MessageBox.alert('Error', tmp.message);
                                        }

                                    })
                                }
                                else {
                                    Ext.MessageBox.alert("Notification", "Please enter all required fields");
                                }
                            }

                        }

                    ]
                })
            ]
        });
        return _mpanelforMargin;
    };
    var MarginMainGridB2B = function () {
        var _marginStore = MarginMasterStoreB2B();
        var _marginGridFilter = new Ext.ux.grid.GridFilters({
            filters: [
                {
                    type: 'string',
                    dataIndex: 'bmd_name'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }
            ]
        });
        _marginGridFilter.remote = true;
        _marginGridFilter.autoReload = true;
        var _marginmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _marginStore,
            //iconCls: 'money',
            id: 'gridpanelMasterDataviewMargindataB2B',
//            view: new Ext.grid.GroupingView({
//                forceFit: true,
//                deferEmptyText: false,
//                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
//                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
//            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _marginGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    dataIndex: 'bmd_name',
                    sortable: true,
                    tooltip: 'Margin Name',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                },
                {
                    header: 'Action',
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    items: [{
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _isDefault = data.is_default;
                                if (_isDefault == 0) {
                                    this.items[0].tooltip = 'Set Default';
                                    return 'margindistributin_inactive';
                                }
                                else {
                                    this.items[0].tooltip = 'Clear Default';
                                    return 'margindistributin_active';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                Ext.Ajax.request({waitMsg: 'Processing',
                                    url: modURL,
                                    params: {
                                        op: 'setDefaultB2B',
                                        bmd_id: record.get('bmd_id')
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    },
                                    success: function (response, options) {
                                        eval("var tmp=" + response.responseText);
                                        if (tmp.success === true) {
                                            Application.example.msg('Notification', tmp.msg);
                                            Ext.getCmp('gridpanelMasterDataviewMargindataB2B').getStore().reload();
                                        }
                                    }
                                });
                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('is_default') == 1)
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _marginStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedmarginB2B
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('bmd_id_b2b');
                    if (!Ext.isEmpty(ID)) {
                        Application.Retaline_margin.Cache.bmd_id = ID;
                        Ext.getCmp('formpanelMasterMarginB2B').hide();
                        Application.Retaline_margin.ViewMarginB2B(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _marginStore.load();
                }
            },
            tbar: [{
                    text: 'Create Wholesale Margin',
                    tooltip: 'Create Wholesale Margin',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.Retaline_margin.MarginAddEdit = 'Add';
                        var marginForm = Ext.getCmp('formpanelMasterMarginB2B').getForm();
                        Ext.getCmp('panelMasterMarginParentB2B').setTitle('Create Wholesale Margin Details');
                        loadedForm = null;
                        marginForm.reset();
                        //Ext.getCmp('tag_name').focus(false, 100);
                        Ext.getCmp('buttonMasterMarginSaveB2B').show();
                        Ext.getCmp('buttonMasterMarginCancelB2B').show();
                        Ext.getCmp('formpanelMasterMarginB2B').show();
                        Ext.getCmp('comboMasterMarginStatus_b2b').setValue(1);
                        Ext.getCmp('panelMasterMarginDetailsViewB2B').hide();
                        Ext.getCmp('panelMasterMarginParentB2B').doLayout();
                    }
                }]
        });
        return _marginmaingridPanel;
    };
    var MarginFormB2B = function () {
        var _marginFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterMarginB2B',
            frame: false,
            border: false,
            hidden: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [
                {
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Margin Name',
                    id: 'bmd_name_b2b',
                    name: 'n[bmd_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 1,
                    maxValue: 100,
                    maxLength: 20,
                    listeners: {
                        afterrender: function (field) {
                            Ext.defer(function () {
                                field.focus(true, 100);
                            }, 1);
                        }
                    }
                },
                {
                    layout: 'column',
                    border: false,
                    items: [{
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Operations',
                                    id: 'bmd_management_b2b',
                                    name: 'n[bmd_management]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 2,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                },
                            ]
                        },
                        {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Company',
                                    id: 'bmd_company_b2b',
                                    name: 'n[bmd_company]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 3,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                },
                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Central Store',
                                    id: 'bmd_hub_b2b',
                                    name: 'n[bmd_cs]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 4,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }
                            ]
                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Distributor',
                                    id: 'bmd_distributor_b2b',
                                    name: 'n[bmd_distributor]',
                                    anchor: '98%',
                                    allowBlank: false,
                                    allowDecimals: false,
                                    tabIndex: 5,
                                    maxValue: 100,
                                    maxLength: 20,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }


                            ]
                        }

                    ]

                },
                {
                    layout: 'column',
                    border: false,
                    items: [{
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [mkCombo({
                                    "type": STATUS_COMBO_DATA,
                                    "value": "id",
                                    "display": "text",
                                    "name": "n[status]",
                                    "fieldLabel": "Status",
                                    "tabIndex": 6,
                                    "emptyText": "Set status..",
                                    "id": 'comboMasterMarginStatus_b2b'
                                })

                            ]
                        }

                    ]

                },
                {
                    xtype: 'textfield',
                    id: 'bmd_id_b2b',
                    name: 'n[bmd_id]',
                    hidden: true
                }

            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('bmd_id_b2b').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterMarginStatus_b2b').getStore().getAt(0);
                        Ext.getCmp('comboMasterMarginStatus_b2b').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _marginFormPanel;
    };
    var MarginMasterDetailsViewB2B = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterMarginDetailsViewB2B',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>  {bmd_name} </td></tr>',
                    '<tr><th width="40%">Operations </th><td>  {bmd_management} </td></tr>',
                    '<tr><th width="40%">Company </th><td>  {bmd_company} </td></tr>',
                    '<tr><th width="40%">Central Store </th><td>  {bmd_cs} </td></tr>',
                    '<tr><th width="40%">Distributor </th><td>  {bmd_distributor} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var gridSelectionChangedmargin = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewMargindata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewMargindata').getSelectionModel().getSelections()[0].data.rmim_stit_id;
            var rmim_bmd_id = Ext.getCmp('gridpanelMasterDataviewMargindata').getSelectionModel().getSelections()[0].data.rmim_bmd_id;
            Application.Retaline_margin.ViewMargin(ID, rmim_bmd_id);
        }
    };

    var retalinMarginB2BMappingPanel = function (id) {

        var _mpanelforMargin = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Item Margin',
            id: id,
            //iconCls: 'my-icon449',
            items: [MarginItemGridB2B(), new Ext.Panel({
                    title: 'Distribution Detail',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelItemMarginParentB2B',
                    height: winsize.height * 0.6,
                    items: [MarginItemDetailsViewB2B()],
                    buttonAlign: 'right',
                    fbar: []
                })
            ]
        });
        return _mpanelforMargin;
    };
    var MarginItemDetailsViewB2B = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelItemMarginDetailsViewB2B',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>  {bmd_name} </td></tr>',
                    '<tr><th width="40%">Management </th><td>  {bmd_management} </td></tr>',
                    '<tr><th width="40%">Company </th><td>  {bmd_company} </td></tr>',
                    '<tr><th width="40%">Central Store </th><td>  {bmd_cs} </td></tr>',
                    '<tr><th width="40%">Distributor </th><td>  {bmd_distributor} </td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var MarginItemGridB2B = function () {

        var _marginStore = MarginItemStore();
        var _marginGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'list',
                    options: ['Medicine', 'Product'],
                    phpMode: true,
                    dataIndex: 'itemType'
                }, {
                    type: 'string',
                    dataIndex: 'itemBC'
                }, {
                    type: 'string',
                    dataIndex: 'itemMargin'
                }]
        });
        _marginGridFilter.remote = true;
        _marginGridFilter.autoReload = true;
        var _marginmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _marginStore,
            //iconCls: 'money',
            id: 'gridpanelItemMargindata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _marginGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    dataIndex: 'stit_SKU',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                },
                {
                    header: 'Type',
                    dataIndex: 'itemType',
                    sortable: true,
                    tooltip: 'Type'
                },
                {
                    header: 'Brand',
                    dataIndex: 'itemBC',
                    sortable: true,
                    tooltip: 'Brand'
                },
                {
                    header: 'Applied Margin Distribution',
                    dataIndex: 'itemMargin',
                    sortable: true,
                    tooltip: 'Applied Margin Distribution'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _marginStore,
                displayInfo: true,
                plugins: [_marginGridFilter],
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedmarginItem
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('bmd_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.Retaline_margin.Cache.bmd_id = ID;
                        Application.Retaline_margin.ViewItemMargin(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _marginStore.load();
                }
            },
            tbar: [{
                    text: 'Create Item Margin',
                    tooltip: 'Create Item Margin',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        windowForItemMarginMapping();
                    }
                }]
        });
        return _marginmaingridPanel;
    };
    var MarginItemStore = function () {
        var _marginsMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetalinMarginItemMappping',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'rmim_stit_id',
                root: 'data'
            }, ['rmim_stit_id', 'rmim_isMedicine', 'rmim_bmd_id', 'rmim_createdOn', 'rmim_updatedOn', 'stit_SKU', 'itemType', 'itemBC', 'itemMargin']),
            sortInfo: {
                field: 'rmim_stit_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelItemMargindata').getView().refresh();
                    Ext.getCmp('gridpanelItemMargindata').getSelectionModel().selectRow(0);
                }
            }
        });

        return _marginsMasterStore;
    };
    var marginDist = function (fsto_source) {
        var store = new Ext.data.JsonStore({
            fields: ['bmd_id', 'bmd_name'],
            url: modURL + '&op=getB2bMargins',
            method: 'post',
            autoLoad: true,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.fsto_source = fsto_source;
                }
            }
        });
        return store;
    };
    var windowForItemMarginMapping = function () {
        var _itemMarginMapWindow = new Ext.Window({
            title: 'Item Margin Distribution Mapping',
            layout: 'fit',
            height: 500,
            width: 900,
            id: 'windowForItemMarginMapping',
            resizable: false,
            draggable: true,
            modal: true,
            closable: true,
            bodyStyle: {"background-color": "white"},
            items: [itemMarginMappingGrid()],
            buttons: [{
                    html: '&nbsp;Select Margin Distribution : &nbsp;',
                },
                {
                    xtype: 'combo',
                    displayField: 'bmd_name',
                    valueField: 'bmd_id',
                    mode: 'local',
                    id: 'marginname',
                    forceSelection: true,
                    fieldLabel: 'Store',
                    emptyText: 'Margin Distribution',
                    anchor: '98%',
                    typeAhead: true,
                    triggerAction: 'all',
                    lazyRender: true,
                    editable: true,
                    minChars: 2,
                    tabIndex: 102,
                    store: marginDist(),
                    listeners: {
                    }

                },
                {
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    tabIndex: 511,
                    handler: function () {
                        _itemMarginMapWindow.close();

                    }
                },
                {
                    text: 'Apply',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    cls: 'left-right-buttons',
                    diasabled: 'true',
                    id: 'submit_button',
                    handler: function () {
                        if (Ext.getCmp('marginname').getValue() > 0) {
                            var selectitem = Ext.getCmp('gridpanelMarginItemMappinggGrid').getSelectionModel().getSelections();
                            var selectedcount = selectitem.length;
                            var itemarr = [];
                            for (var i = 0; i < selectedcount; i++)
                            {
                                itemarr.push(selectitem[i].data.stit_ID);
                            }
                            if (selectedcount != 0)
                            {
                                Application.Finascop_Stock.Cache.itemarr = itemarr;
                                var itemType = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                                var itemMargin = Ext.getCmp('marginname').getValue();
                                Application.Finascop_Stock.Cache.itemType = itemType;
                                saveCheckedItem(itemMargin, itemarr, itemType);
                            } else
                            {
                                Ext.MessageBox.alert("Notification", "Please check,Some box entries are not valid.");
                            }
                        } else {
                            Ext.MessageBox.alert("Notification", "Choose Margin Distribution.");
                        }
                    }

                }],
            listeners: {
                afterrender: function () {
                    if (_SESSION.IS_MEDICINE_REQUIRED != 1) {
                        Ext.getCmp('radiobuttonFinascopStockId').hide();
                    } 

                }

            }


        });
        _itemMarginMapWindow.doLayout();
        _itemMarginMapWindow.show();
        _itemMarginMapWindow.center();
    };
    var checkBox = new Ext.grid.CheckboxSelectionModel({
        multiSelect: true
    });
    var itemMarginMappingStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'POST',
            url: modURL + '&op=listMarginItemSearch',
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            fields: ['stit_itemName', 'stit_ID', 'brand', 'stit_SKU', 'type'],
            listeners: {
                beforeload: function (thisStore, options) {
                }
            }
        });
        return store;
    };
    var itemMarginMappingGrid = function () {
        var _itemMarginMappingGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        _itemMarginMappingGridFilter.remote = true;
        _itemMarginMappingGridFilter.autoReload = true;
        var _manualPackingStore = itemMarginMappingStore();
        var _manualPackingGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _manualPackingStore,
            //iconCls: 'money',
            autoScroll: true,
            width: 600,
            height: 250,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelMarginItemMappinggGrid',
            plugins: [_itemMarginMappingGridFilter],
            columns: [checkBox,
                {
                    header: 'Item',
                    dataIndex: 'stit_SKU',
                    sortable: true,
                    tooltip: 'Item',
                    hideable: true
                },
                {
                    header: 'Type',
                    dataIndex: 'type',
                    sortable: true,
                    tooltip: 'Type',
                    hideable: true
                },
                {
                    header: 'Brand',
                    dataIndex: 'brand',
                    sortable: true,
                    tooltip: 'Brand',
                    hideable: true
                },
                {
                    header: 'Margin Distribution',
                    hidden: true,
                    dataIndex: 'margin',
                    hideable: false,
                    tooltip: 'Margin Distribution',
                    groupable: false
                }
            ],
            viewConfig: {
                forceFit: true,
            },
            sm: checkBox,
            tbar: [
                {
                    xtype: 'radiogroup',
                    width: 200,
                    id: 'radiobuttonFinascopStockId',
                    //columnWidth: 0.4,
                    items: [
                        {boxLabel: 'Medicine', name: 'rb-auto', inputValue: 1, labelWidth: 150, id: 'medradio'},
                        {boxLabel: 'Product', name: 'rb-auto', inputValue: 2, labelWidth: 150, checked: true}

                    ],
                    listeners: {
                        change: function (event, checked)
                        {
                            var current_firstid = event.items.items[0].inputValue;
                            var current_secondid = event.items.items[1].inputValue;
                            //var current_thirdid = event.items.items[2].inputValue;
                            var radioid = Ext.getCmp('radiobuttonFinascopStockId').getValue();

                            if (radioid == current_secondid) {
                                var item_name = '';
                                //filterItems(item_name, radioid);
                            } else if (radioid == current_firstid) {
                                var item_name = '';
                                //filterItems(item_name, radioid);
                            }
                        }
                    }
                },
                {
                    //columnWidth: 0.4,
                    xtype: 'textfield',
                    id: 'radiosearch',
                    width: 500,
                    listeners: {
                        specialkey: function (field, e) {
                            var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                            console.log("combo box id", item_search_item);
                            var search_bar = Ext.getCmp('radiosearch').getValue();
                            console.log("radio search", search_bar);
                            if (item_search_item != 0 && search_bar != '')
                            {
                                searchItems(search_bar, item_search_item);
                            }
                        }
                    }
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/search.png',
                    xtype: 'button',
                    //columnWidth: 0.10,
                    width: 50,
                    text: 'Go',
                    handler: function () {
                        var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                        console.log("combo box id", item_search_item);
                        var search_bar = Ext.getCmp('radiosearch').getValue();
                        console.log("radio search", search_bar);
                        if (item_search_item != 0 && search_bar != '')
                        {
                            searchItems(search_bar, item_search_item);
                        }
                    }
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/reset.png',
                    xtype: 'button',
                    width: 100,
                    text: 'Reset',
                    handler: function () {
                        Ext.getCmp('radiosearch').reset();
                        searchItems('', '');
                    }
                }
            ]

        });
        return _manualPackingGridPanel;
    };
    var searchItems = function (item_name, radio_id)
    {
        var gridvalue = Ext.getCmp('gridpanelMarginItemMappinggGrid').getStore();

        gridvalue.baseParams = {
            currentItem: item_name,
            current_type: radio_id
        }
        gridvalue.load();

    };
    var saveCheckedItem = function (itemMargin, itemarr, itemType) {
        Ext.Ajax.request({
            url: modURL + '&op=saveItemMargins',
            method: 'post',
            params: {itemMargin: itemMargin, itemarr: Ext.encode(itemarr), itemType: itemType},
            success: function (resp) {
//                var res = Ext.decode(resp.responseText);
//                    if (res.success === true) {
//                        Application.example.msg('Success', 'Margin distribution applied for the selected items.');
//                        windowForItemMarginMapping.close();
//                        Ext.getCmp('gridpanelItemMargindata').getStore().reload();
//                        } else {
//                                    Ext.MessageBox.alert("Notification", 'No new Items added');
//                                } 
                var res = Ext.decode(resp.responseText);
                if (res.success === true)
                {
                    Application.example.msg('Success', 'Margin distribution applied for the selected items.');
                                Ext.getCmp('gridpanelItemMargindata').getStore().reload();
                                Ext.getCmp('windowForItemMarginMapping').close();
                } else
                {
                    Ext.MessageBox.alert('Notification', 'No new Items added',
                            function (btn) {
                                Ext.getCmp('gridpanelItemMargindata').getStore().reload();
                                Ext.getCmp('windowForItemMarginMapping').close();

                            });
                }
            }
        });

    };
    return {
        Cache: {},
        initRetailMargin: function () {
            var panelId = 'retalin_master_grid';
            var retalin_master_main = Ext.getCmp(panelId);
            if (Ext.isEmpty(retalin_master_main)) {
                retalin_master_main = retalinMarginPanel(panelId);
                Application.UI.addTab(retalin_master_main);
                retalin_master_main.doLayout();
            } else {
                Application.UI.addTab(retalin_master_main);
                retalin_master_main.doLayout();
            }

        },
        ViewMargin: function () {
            var bmd_id = arguments[0];
            Ext.getCmp('buttonMasterMarginSave').hide();
            Ext.getCmp('buttonMasterMarginCancel').hide();
            Ext.getCmp('formpanelMasterMargin').hide();
            Ext.getCmp('panelMasterMarginDetailsView').show();
            Ext.getCmp('panelMasterMarginParent').doLayout();
            Ext.getCmp('panelMasterMarginParent').setTitle('View Retail Margin Details');
            Ext.Ajax.request({
                url: modURL + '&op=MargindetailsView',
                method: 'POST',
                params: {bmd_id: bmd_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterMarginDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterMarginParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterMarginParent').doLayout();
        },
        initRetailMarginB2B: function () {
            var panelId = 'retalin_masterB2B_grid';
            var retalin_master_b2b = Ext.getCmp(panelId);
            if (Ext.isEmpty(retalin_master_b2b)) {
                retalin_master_b2b = retalinMarginB2BPanel(panelId);
                Application.UI.addTab(retalin_master_b2b);
                retalin_master_b2b.doLayout();
            } else {
                Application.UI.addTab(retalin_master_b2b);
                retalin_master_b2b.doLayout();
            }

        },
        ViewMarginB2B: function () {
            var bmd_id = arguments[0];
            Ext.getCmp('buttonMasterMarginSaveB2B').hide();
            Ext.getCmp('buttonMasterMarginCancelB2B').hide();
            Ext.getCmp('formpanelMasterMarginB2B').hide();
            Ext.getCmp('panelMasterMarginDetailsViewB2B').show();
            Ext.getCmp('panelMasterMarginParentB2B').doLayout();
            Ext.getCmp('panelMasterMarginParentB2B').setTitle('View Wholesale Margin Details');
            Ext.Ajax.request({
                url: modURL + '&op=MargindetailsViewB2B',
                method: 'POST',
                params: {bmd_id: bmd_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterMarginDetailsViewB2B');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterMarginParentB2B').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterMarginParentB2B').doLayout();
        },
        initRetailMarginItemMapping: function () {
            var panelId = 'retalin_item_maapping';
            var retalin_item_mapping = Ext.getCmp(panelId);
            if (Ext.isEmpty(retalin_item_mapping)) {
                retalin_item_mapping = retalinMarginB2BMappingPanel(panelId);
                Application.UI.addTab(retalin_item_mapping);
                retalin_item_mapping.doLayout();
            } else {
                Application.UI.addTab(retalin_item_mapping);
                retalin_item_mapping.doLayout();
            }

        }, ViewItemMargin: function () {
            var rmim_bmd_id = arguments[0];
            Ext.getCmp('panelItemMarginDetailsViewB2B').show();
            Ext.getCmp('panelItemMarginParentB2B').doLayout();
            Ext.getCmp('panelItemMarginParentB2B').setTitle('View Item Margin Details');
            Ext.Ajax.request({
                url: modURL + '&op=MarginItemdetailsView',
                method: 'POST',
                params: {bmd_id: rmim_bmd_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelItemMarginDetailsViewB2B');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelItemMarginParentB2B').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelItemMarginParentB2B').doLayout();
        }

    };
}();