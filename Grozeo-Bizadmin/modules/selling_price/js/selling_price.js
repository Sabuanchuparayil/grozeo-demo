Application.SellingPrice = function () {

    var RECS_PER_PAGE = 23;
    var modURL = '?module=selling_price';
    var winsize = Ext.getBody().getViewSize();
    var gridSelectionSPFChanged = function () {
        if (!Ext.isEmpty(Ext.getCmp('manageSPFGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manageSPFGridPanel').getSelectionModel().getSelections()[0].data.spf_id;
        }
    };
    var gridSelectionMinMarginChanged = function(){
        if (!Ext.isEmpty(Ext.getCmp('manageMinMarginGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manageMinMarginGridPanel').getSelectionModel().getSelections()[0].data.mm_id;
        }
    };
    var manageSPFMainPanel = function (id) {
        var _mSPFPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Selling Price Calculator',
            iconCls: 'dispatch',
            id: id,
            items: [
                manageSPFGrid()
            ]
        });
        return _mSPFPanel;
    };
    var manageSPFGrid = function () {
        var SPFStore = new Ext.data.JsonStore({
            fields: ['spf_detailId', 'spf_detailName'],
            url: modURL + '&op=getSPFDetailStore',
            autoLoad: false,
            method: 'post',
        });
        var _SPFGridStore = SPFUploadGridStore();
        var _SPFFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'list',
                    options: ['SKU', 'Brand', 'Itemmaster', 'Subcategory'],
                    phpMode: true,
                    dataIndex: 'spf_typeName'
                }, {
                    type: 'string',
                    dataIndex: 'spf_detailName'
                }
            ]
        });
        _SPFFilter.remote = true;
        _SPFFilter.autoReload = true;
        var _mSPFGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _SPFGridStore,
            id: 'manageSPFGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _SPFFilter],
            columns: [new Ext.grid.RowNumberer(),                
                {
                    header: 'Applicable To',
                    id: 'spf_detailName_auto_exp',
                    dataIndex: 'spf_detailName',
                    tooltip: 'Applicable To',
                    hideable: true,
                    width: 200
                },{
                    header: 'Brand/Product/Catgeory',
                    dataIndex: 'spf_typeName',
                    tooltip: 'Brand/Product/Catgeory',
                    hideable: true,
                    width: 20
                },
                {
                    header: 'Price Factor',
                    dataIndex: 'spf_factor',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Price Factor',
                    width: 20
                }, {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Delete',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteSPFFactor(record.get('spf_id'));
                            }
                        }
                    ]

                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _SPFGridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionSPFChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                },
                afterrender: function () {

                }
            },
            tbar: [{
                    html: '&nbsp;Select Type : &nbsp;',
                }, {
                    fieldLabel: 'Select Type',
                    xtype: 'combo',
                    displayField: 'typeName',
                    valueField: 'typeId',
                    mode: 'local',
                    id: 'spf_type',
                    hiddenName: 'spf_type',
                    name: 'spf_type',
                    typeAhead: true,
                    minChars: 1,
                    triggerAction: 'all',
                    selectOnFocus: true,
                    lazyRender: true,
                    anchor: '97%',
                    store: new Ext.data.JsonStore({
                        fields: ['typeId', 'typeName'],
                        data: [{typeId: '1', typeName: 'SKU'}, {typeId: '2', typeName: 'Brand'}, {typeId: '3', typeName: 'Item Master'}, {typeId: '4', typeName: 'Subcategory'}]
                    }),
                    editable: true,
                    width: 120,
                    tabIndex: 100,
                    listeners: {
                        select: function () {
                            var type = Ext.getCmp('spf_type').getValue();
                            Ext.getCmp('spf_detail').reset();
                            Ext.getCmp('spf_detail').getStore().load({
                                params: {
                                    type: type
                                }
                            });
                        }
                    }
                }, {
                    html: '&nbsp;Select Detail : &nbsp;',
                }, {
                    xtype: 'combo',
                    id: 'spf_detail',
                    name: 'spf_detail',
                    mode: 'local',
                    emptyText: 'Choose',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Select Detail',
                    editable: true,
                    anchor: '97%',
                    width: 350,
                    store: SPFStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'spf_detailName',
                    valueField: 'spf_detailId',
                    hiddenName: 'spf_detail',
                    tabIndex: 101,
                    listeners: {
                        select: function () {

                        }
                    }
                }, {
                    html: '&nbsp;Price Factor(%) : &nbsp;',
                }, {
                    fieldLabel: 'Factor',
                    xtype: 'numberfield',
                    id: 'spf_factor',
                    name: 'spf_factor',
                    tabIndex: 102,
                    width: 80,
                    anchor: '97%',
                }, {
                    html: '&nbsp;&nbsp;',
                }, {
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    tabIndex: 103,
                    handler: function () {
                        Application.SellingPrice.checkSPFAvailable();
                    }
                }, '->', {
                    html: '&nbsp;Minimum Price Factor : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxMinSPFFactor',
                    name: 'textboxMinSPFFactor',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    readOnly: true,
                    value: _SESSION.DEFAULT_SPF +' %'
                }]
        });
        return _mSPFGridPanel;
    };
    var SPFUploadGridStore = function () {
        var _manageSPFList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listSPFFactor',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'spf_id',
                root: 'data'
            }, ['spf_id', 'spf_type', 'spf_detail', 'spf_factor', 'spf_typeName', 'spf_detailName']),
            sortInfo: {
                field: 'spf_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'DESC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                }
            }
        });
        return _manageSPFList;
    };
    var deleteSPFFactor = function (id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this entry?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteSPFFactor',
                    params: {
                        spf_id: id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed entry');
                            Ext.getCmp('manageSPFGridPanel').getStore().load();
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
    var manageMinMarginMainPanel = function (id) {
        var _mMinMarginPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Minimum Margin',
            iconCls: 'dispatch',
            id: id,
            items: [
                manageMinMarginGrid()
            ]
        });
        return _mMinMarginPanel;
    };
    var MinMarginUploadGridStore = function(){

        var _manageMinMarginList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMinMarginFactor',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'mm_id',
                root: 'data'
            }, ['mm_id', 'mm_type', 'mm_detail', 'mm_factor', 'mm_typeName', 'mm_detailName']),
            sortInfo: {
                field: 'mm_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'DESC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                }
            }
        });
        return _manageMinMarginList;
    };
    var manageMinMarginGrid = function(){
        
        var SPFStore = new Ext.data.JsonStore({
            fields: ['spf_detailId', 'spf_detailName'],
            url: modURL + '&op=getSPFDetailStore',
            autoLoad: false,
            method: 'post',
        });
        var _MinMarginGridStore = MinMarginUploadGridStore();
        var _MinMarginFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'list',
                    options: ['SKU', 'Brand', 'Itemmaster', 'Subcategory'],
                    phpMode: true,
                    dataIndex: 'mm_typeName'
                }, {
                    type: 'string',
                    dataIndex: 'mm_detailName'
                },{
                type: 'string',
                dataIndex: 'mm_factor'
            }
            ]
        });
        _MinMarginFilter.remote = true;
        _MinMarginFilter.autoReload = true;
        var _mMinMarginGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _MinMarginGridStore,
            id: 'manageMinMarginGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _MinMarginFilter],
            columns: [new Ext.grid.RowNumberer(),                
                {
                    header: 'Applicable To',
                    id: 'mm_detailName_auto_exp',
                    dataIndex: 'mm_detailName',
                    tooltip: 'Detail',
                    hideable: true,
                    width: 200
                },{
                    header: 'Brand/Product/Category',
                    dataIndex: 'mm_typeName',
                    tooltip: 'Type',
                    hideable: true,
                    width: 20
                },
                {
                    header: 'Margin',
                    dataIndex: 'mm_factor',
                    sortable: true,
                    align: 'right',
                    tooltip: 'Margin',
                    width: 20
                }, {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Delete',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteMinMarginFactor(record.get('mm_id'));
                            }
                        }
                    ]

                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _MinMarginGridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionMinMarginChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                },
                afterrender: function () {

                }
            },
            tbar: [{
                    html: '&nbsp;Select Brand/Product/Category : &nbsp;',
                }, {
                    fieldLabel: 'Select Brand/Product/Category',
                    xtype: 'combo',
                    displayField: 'typeName',
                    valueField: 'typeId',
                    mode: 'local',
                    id: 'mm_type',
                    hiddenName: 'mm_type',
                    name: 'mm_type',
                    typeAhead: true,
                    minChars: 1,
                    triggerAction: 'all',
                    selectOnFocus: true,
                    lazyRender: true,
                    anchor: '97%',
                    store: new Ext.data.JsonStore({
                        fields: ['typeId', 'typeName'],
                        data: [{typeId: '1', typeName: 'SKU'}, {typeId: '2', typeName: 'Brand'}, {typeId: '3', typeName: 'Item Master'}, {typeId: '4', typeName: 'Subcategory'}]
                    }),
                    editable: true,
                    width: 120,
                    tabIndex: 100,
                    listeners: {
                        select: function () {
                            var type = Ext.getCmp('mm_type').getValue();
                            Ext.getCmp('mm_detail').reset();
                            Ext.getCmp('mm_detail').getStore().load({
                                params: {
                                    type: type
                                }
                            });
                        }
                    }
                }, {
                    html: '&nbsp;Select Applicable : &nbsp;',
                }, {
                    xtype: 'combo',
                    id: 'mm_detail',
                    name: 'mm_detail',
                    mode: 'local',
                    emptyText: 'Choose',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Select Detail',
                    editable: true,
                    anchor: '97%',
                    width: 350,
                    store: SPFStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'spf_detailName',
                    valueField: 'spf_detailId',
                    hiddenName: 'mm_detail',
                    tabIndex: 101,
                    listeners: {
                        select: function () {

                        }
                    }
                }, {
                    html: '&nbsp;Margin(%) : &nbsp;',
                }, {
                    fieldLabel: 'Margin',
                    xtype: 'numberfield',
                    id: 'mm_factor',
                    name: 'mm_factor',
                    tabIndex: 102,
                    width: 80,
                    anchor: '97%',
                }, {
                    html: '&nbsp;&nbsp;',
                }, {
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    tabIndex: 103,
                    handler: function () {
                        Application.SellingPrice.checkMinMarginAvailable();
                    }
                }, '->', {
                    html: '&nbsp;Minimum Margin : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxMinMarginFactor',
                    name: 'textboxMinMarginFactor',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    readOnly: true,
                    value: _SESSION.DEFAULT_MM +' %'
                }]
        });
        return _mMinMarginGridPanel;

    };
    var deleteMinMarginFactor = function (id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this entry?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteMinMarginFactor',
                    params: {
                        mm_id: id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed Entry');
                            Ext.getCmp('manageMinMarginGridPanel').getStore().load();
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
    var manageSellerPlatfChargeMainPanel = function (id) {
        var _mSellerPlatfChargePanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Seller Platform Charge',
            iconCls: 'dispatch',
            id: id,
            items: [manageSellerPlatfChargeGrid()]
        });
        return _mSellerPlatfChargePanel;
    };
    var manageSellerPlatfChargeGrid = function () {
        var SellerPlatfChargeStore = new Ext.data.JsonStore({
            fields: ['spc_detailId', 'spc_detailName'],
            url: modURL + '&op=getSellerPlatfChargeDetailStore',
            autoLoad: false,
            method: 'post',
        });
        var _SellerPlatfChargeGridStore = SellerPlatfChargeUploadGridStore();
        var _SellerPlatfChargeFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'list',
                    options: ['Product', 'Brand', 'Subcategory','Category','Department','Retail Category'],
                    phpMode: true,
                    dataIndex: 'spc_typeName'
                }, {
                    type: 'string',
                    dataIndex: 'spc_detailName'
                }
            ]
        });
        _SellerPlatfChargeFilter.remote = true;
        _SellerPlatfChargeFilter.autoReload = true;
        var _mSellerPlatfChargeGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _SellerPlatfChargeGridStore,
            id: 'manageSellerPlatfChargeGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _SellerPlatfChargeFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Applicable To',
                    id: 'spc_detailName_auto_exp',
                    dataIndex: 'spc_detailName',
                    tooltip: 'Applicable To',
                    hideable: true,
                    width: 100
                },{
                    header: 'Brand/Product/Catgeory',
                    dataIndex: 'spc_typeName',
                    tooltip: 'Brand/Product/Catgeory',
                    hideable: true,
                    width: 20
                },
                {
                    header: 'Retail Category',
                    id: 'business_type_name_auto_exp',
                    dataIndex: 'business_type_name',
                    sortable: true,
                    tooltip: 'Detail',
                    hideable: true,
                    width: 100,
                    hidden: true
                },
                {
                    header: 'Platform Charge',
                    dataIndex: 'charge',
                    sortable: true,
                    align: 'left',
                    tooltip: 'Platform Charge',
                    /*editor: {
                allowBlank: false,
                xtype: 'numberfield'
                }*/
                }, {
                    hidden:true,
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 80,
                    items: [
                        {
                            iconCls: 'remove-enquiry',
                            tooltip: 'Delete',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteSellerPlatCharge(record.get('id'));
                            }
                        }
                    ]

                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _SellerPlatfChargeGridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionSellerPlatfChargeChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                },
                afterrender: function () {
                    _SellerPlatfChargeGridStore.load();
                },
               // afteredit: updateSellerPlatformCharge,
            },
            tbar: [{
                    html: '&nbsp;Select Type : &nbsp;',
                }, {
                    fieldLabel: 'Select Type',
                    xtype: 'combo',
                    displayField: 'typeName',
                    valueField: 'typeId',
                    mode: 'local',
                    id: 'spc_type',
                    hiddenName: 'spc_type',
                    name: 'spc_type',
                    typeAhead: true,
                    minChars: 1,
                    triggerAction: 'all',
                    selectOnFocus: true,
                    lazyRender: true,
                    anchor: '97%',
                    store: new Ext.data.JsonStore({
                        fields: ['typeId', 'typeName'],
                        data: [{typeId: '1', typeName: 'Product'}, 
                            {typeId: '2', typeName: 'Brand'}, 
                            {typeId: '3', typeName: 'Subcategory'}, 
                            {typeId: '4', typeName: 'Category'},
                            {typeId: '5', typeName: 'Department'},
                            {typeId: '6', typeName: 'Retail Category'},
                            {typeId: '7', typeName: 'Branch'},
                            {typeId: '8', typeName: 'Store Group'}]
                    }),
                    editable: true,
                    width: 120,
                    tabIndex: 100,
                    listeners: {
                        select: function () {
                            var type = Ext.getCmp('spc_type').getValue();
                            Ext.getCmp('spc_detail').reset();
                            Application.SellingPrice.selectValue(type);
                            /*Ext.getCmp('spc_detail').getStore().load({
                                params: {
                                    type: type
                                }
                            });*/
                        }
                    }
                }, {
                    html: '&nbsp;Select Detail : &nbsp;',
                }, {
                    xtype: 'combo',
                    id: 'spc_detail',
                    name: 'spc_detail',
                    mode: 'local',
                    emptyText: 'Choose',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Select Detail',
                    editable: true,
                    anchor: '97%',
                    width: 350,
                    store: SellerPlatfChargeStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'spc_detailName',
                    valueField: 'spc_detailId',
                    hiddenName: 'spc_detail',
                    hideTrigger: true,
                    tabIndex: 101,
                    listeners: {
                        select: function () {

                        }
                    }
                }, {
                    html: '&nbsp;Platform Charge : &nbsp;',
                }, {
                    fieldLabel: 'Factor',
                    xtype: 'numberfield',
                    id: 'spc_factor',
                    name: 'spc_factor',
                    tabIndex: 102,
                    width: 80,
                    anchor: '97%',
                }, {
                    html: '&nbsp;&nbsp;',
                }, {
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    tabIndex: 103,
                    handler: function () {
                        Application.SellingPrice.checkSPCAvailable();
                    }
                }, '->', {
                    html: '&nbsp;Minimum Seller Platform Charge : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxMinSellerPlatCharge',
                    name: 'textboxMinSellerPlatCharge',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    readOnly: true,
                    value: _SESSION.DEFAULT_SELLER_PLATFORM_CHARGE
                }]
        });
        return _mSellerPlatfChargeGridPanel;
    };
    function updateSellerPlatformCharge(grid_event) {
        var data = Ext.encode(grid_event.record.data);
        Ext.Ajax.request({
            waitMsg: 'Please wait...',
            url: modURL + '&op=saveSPCData',
            params: {
                data: Ext.encode(grid_event.record.data),
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', 'Save Failed');
            },
            success: function (response, options) {
                if (response.responseText != "") {
                    eval('var tmp=' + response.responseText);
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Application.example.msg('Notification', tmp.msg);
                    } else if (tmp.success === false) {
                        Ext.MessageBox.alert('Notification', 'Error Occured while saving', function (btn) {
                            if (btn == 'ok') {
                            }
                        });
                    }
                }
            }
        });
    }
    var SellerPlatfChargeUploadGridStore = function () {
        var _manageSellerPlatfChargeList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listSellerPlatCharge',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'id',
                root: 'data'
            }, ['id', 'type','appliedOn', 'detailId', 'charge', 'spc_typeName', 'business_type_name','business_type_id','spc_detailName']),
            sortInfo: {
                field: 'id',
                direction: "asc"
            },
            groupField: '',
            groupDir: 'DESC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                }
            }
        });
        return _manageSellerPlatfChargeList;
    };
    var deleteSellerPlatCharge = function (id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this entry?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteSellerPlatCharge',
                    params: {
                        spc_id: id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed entry');
                            Ext.getCmp('manageSellerPlatfChargeGridPanel').getStore().load();
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
	var gridSelectionSellerPlatfChargeChanged = function () {
        if (!Ext.isEmpty(Ext.getCmp('manageSellerPlatfChargeGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manageSellerPlatfChargeGridPanel').getSelectionModel().getSelections()[0].data.spc_id;
        }
    };
    var valueGridStore = function (type) {
        var _manageSelectionList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getSellerPlatfChargeDetailStore',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'spc_detailId',
                root: 'data'
            }, ['spc_detailId', 'spc_detailName']),
            sortInfo: {
                field: 'spc_detailId',
                direction: "asc"
            },
            groupField: '',
            groupDir: 'DESC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                beforeLoad: function () {
                    this.baseParams.type = type;
                }
            }
        });
        return _manageSelectionList;
    };
    var valueSelectionGRid = function(type){
    var _valueSelectionGridStore = valueGridStore(type);
        var _valueSelectionFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'spc_detailName'
                }
            ]
        });
        _valueSelectionFilter.remote = true;
        _valueSelectionFilter.autoReload = true;
        var _valueSelectionGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _valueSelectionGridStore,
            id: 'valueSelectGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _valueSelectionFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Brand/Product/Catgeory',
                    dataIndex: 'spc_detailName',
                    tooltip: 'Brand/Product/Catgeory',
                    hideable: true,
                    width: 80
                },
                {
                    xtype: 'actioncolumn',
                    header: '',
                    hideable: false,
                    groupable: false,
                    width: 20,
                    items: [
                        {
                            iconCls: 'my-icon31',
                            tooltip: 'Choose',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.getCmp('spc_detail').setValue(record.get('spc_detailId'));
                                Ext.getCmp('spc_detail').setRawValue(record.get('spc_detailName'));
                                Ext.getCmp('selectValueWindow').close();
                            }
                        }
                    ]

                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _valueSelectionGridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                },
                afterrender: function () {
                    _valueSelectionGridStore.load();
                },
               // afteredit: updateSellerPlatformCharge,
            },
            tbar: []
        });
        return _valueSelectionGridPanel;
    };
    return {
        Cache: {},
        initmanageSPF: function () {
            var _manageSPFPanelId = 'manageSPF';
            var ___manageSPFPanelIdPanelIdPanel = Ext.getCmp(_manageSPFPanelId);
            if (Ext.isEmpty(___manageSPFPanelIdPanelIdPanel)) {
                ___manageSPFPanelIdPanelIdPanel = manageSPFMainPanel(_manageSPFPanelId);
                Application.UI.addTab(___manageSPFPanelIdPanelIdPanel);
                ___manageSPFPanelIdPanelIdPanel.doLayout();
            } else {
                Application.UI.addTab(___manageSPFPanelIdPanelIdPanel);
            }
        },retalineSPFSave: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var prescriptionForm = Ext.getCmp('SPFFormPanel').getForm();

            var spf_type = Ext.getCmp('spf_type').getValue();
            var spf_detail = Ext.getCmp('spf_detail').getValue();
            var spf_factor = Ext.getCmp('spf_factor').getValue();

            if ((spf_type > 0) && (spf_detail > 0) && (spf_factor > 0)) {
                if (spf_factor > _SESSION.DEFAULT_SPF) {
                    Ext.Ajax.request({
                        url: modURL + '&op=saveSPFData',
                        params: {spf_type: spf_type, spf_detail: spf_detail, spf_factor: spf_factor},
                        failure: function (response) {
                            Ext.MessageBox.alert('Error', response.responseText);

                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.example.msg('Success', tmp.message);
                                Ext.getCmp('manageSPFGridPanel').getStore().load();
                                Ext.getCmp('spf_type').reset();
                                Ext.getCmp('spf_detail').reset();
                                Ext.getCmp('spf_factor').reset();
                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else if (tmp.success === false && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else {
                                Ext.Msg.alert('Error', tmp.message);
                            }
                        }
                    });
                } else {
                    Ext.Msg.alert('Notification', 'SPF Factor should be greater than minmum value.');
                }


            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        },
        checkSPFAvailable: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");

            var spf_type = Ext.getCmp('spf_type').getValue();
            var spf_detail = Ext.getCmp('spf_detail').getValue();
            var spf_factor = Ext.getCmp('spf_factor').getValue();

            if ((spf_type > 0) && (spf_detail > 0) && (spf_factor > 0)) {
                if (spf_factor > _SESSION.DEFAULT_SPF) {
                    Ext.Ajax.request({
                        url: modURL + '&op=checkSPFData',
                        params: {spf_type: spf_type, spf_detail: spf_detail, spf_factor: spf_factor},
                        failure: function (response) {
                            Ext.MessageBox.alert('Error', response.responseText);

                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.SellingPrice.retalineSPFSave();
                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.MessageBox.confirm('Confirm', tmp.message, function (btn) {
                                    if (btn == 'yes') {
                                        Application.SellingPrice.retalineSPFSave();
                                    }
                                });
                            } else if (tmp.success === false && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else {
                                Ext.Msg.alert('Error', tmp.message);
                            }
                        }
                    });
                } else {
                    Ext.Msg.alert('Notification', 'SPF Factor should be greater than minmum value.');
                }


            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        },initmanageMinMargin: function () {
            var _manageMinMarginFPanelId = 'manageMinMargin';
            var ____manageMinMarginFPanelIdPanelIdPanel = Ext.getCmp(_manageMinMarginFPanelId);
            if (Ext.isEmpty(____manageMinMarginFPanelIdPanelIdPanel)) {
                ____manageMinMarginFPanelIdPanelIdPanel = manageMinMarginMainPanel(_manageMinMarginFPanelId);
                Application.UI.addTab(____manageMinMarginFPanelIdPanelIdPanel);
                ____manageMinMarginFPanelIdPanelIdPanel.doLayout();
            } else {
                Application.UI.addTab(____manageMinMarginFPanelIdPanelIdPanel);
            }
        },retalineMinMarginSave: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var prescriptionForm = Ext.getCmp('SPFFormPanel').getForm();

            var mm_type = Ext.getCmp('mm_type').getValue();
            var mm_detail = Ext.getCmp('mm_detail').getValue();
            var mm_factor = Ext.getCmp('mm_factor').getValue();

            if ((mm_type > 0) && (mm_detail > 0) && (mm_factor > 0)) {
                if (mm_factor > _SESSION.DEFAULT_MM) {
                    Ext.Ajax.request({
                        url: modURL + '&op=saveMinMarginData',
                        params: {mm_type: mm_type, mm_detail: mm_detail, mm_factor: mm_factor},
                        failure: function (response) {
                            Ext.MessageBox.alert('Error', response.responseText);

                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.example.msg('Success', tmp.message);
                                Ext.getCmp('manageMinMarginGridPanel').getStore().load();
                                Ext.getCmp('mm_type').reset();
                                Ext.getCmp('mm_detail').reset();
                                Ext.getCmp('mm_factor').reset();
                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else if (tmp.success === false && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else {
                                Ext.Msg.alert('Error', tmp.message);
                            }
                        }
                    });
                } else {
                    Ext.Msg.alert('Notification', 'Margin should be greater than minmum value.');
                }

            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        },checkMinMarginAvailable: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");

            var mm_type = Ext.getCmp('mm_type').getValue();
            var mm_detail = Ext.getCmp('mm_detail').getValue();
            var mm_factor = Ext.getCmp('mm_factor').getValue();

            if ((mm_type > 0) && (mm_detail > 0) && (mm_factor > 0)) {
                if (mm_factor > _SESSION.DEFAULT_MM) {
                    Ext.Ajax.request({
                        url: modURL + '&op=checkMinMarginData',
                        params: {mm_type: mm_type, mm_detail: mm_detail, mm_factor: mm_factor},
                        failure: function (response) {
                            Ext.MessageBox.alert('Error', response.responseText);

                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.SellingPrice.retalineMinMarginSave();
                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.MessageBox.confirm('Confirm', tmp.message, function (btn) {
                                    if (btn == 'yes') {
                                        Application.SellingPrice.retalineMinMarginSave();
                                    }
                                });
                            } else if (tmp.success === false && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else {
                                Ext.Msg.alert('Error', tmp.message);
                            }
                        }
                    });
                } else {
                    Ext.Msg.alert('Notification', 'Margin should be greater than minmum value.');
                }


            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        },initmanageSellerPlatformCharge: function () {
            var _manageSellerPlatCPanelId = 'manageSellerPlatformCharge';
            var ___manageSellerPCPanelIdPanelIdPanel = Ext.getCmp(_manageSellerPlatCPanelId);
            if (Ext.isEmpty(___manageSellerPCPanelIdPanelIdPanel)) {
                ___manageSellerPCPanelIdPanelIdPanel = manageSellerPlatfChargeMainPanel(_manageSellerPlatCPanelId);
                Application.UI.addTab(___manageSellerPCPanelIdPanelIdPanel);
                ___manageSellerPCPanelIdPanelIdPanel.doLayout();
            } else {
                Application.UI.addTab(___manageSellerPCPanelIdPanelIdPanel);
            }
        },retalineSPCSave: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var prescriptionForm = Ext.getCmp('SPFFormPanel').getForm();

            var spc_type = Ext.getCmp('spc_type').getValue();
            var spc_detail = Ext.getCmp('spc_detail').getValue();
            var spc_factor = Ext.getCmp('spc_factor').getValue();

            if ((spc_type > 0) && (spc_detail > 0) && (spc_factor > 0)) {
                if (spc_factor > _SESSION.DEFAULT_SELLER_PLATFORM_CHARGE) {
                    Ext.Ajax.request({
                        url: modURL + '&op=saveSPCData',
                        params: {spc_type: spc_type, spc_detail: spc_detail, spc_factor: spc_factor},
                        failure: function (response) {
                            Ext.MessageBox.alert('Error', response.responseText);

                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.example.msg('Success', tmp.message);
                                Ext.getCmp('manageSellerPlatfChargeGridPanel').getStore().load();
                                Ext.getCmp('spc_type').reset();
                                Ext.getCmp('spc_detail').reset();
                                Ext.getCmp('spc_factor').reset();
                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else if (tmp.success === false && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else {
                                Ext.Msg.alert('Error', tmp.message);
                            }
                        }
                    });
                } else {
                    Ext.Msg.alert('Notification', 'Seller Platform Charge should be greater than minmum value.');
                }


            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        },
        checkSPCAvailable: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");

            var spc_type = Ext.getCmp('spc_type').getValue();
            var spc_detail = Ext.getCmp('spc_detail').getValue();
            var spc_factor = Ext.getCmp('spc_factor').getValue();

            if ((spc_type > 0) && (spc_detail > 0) && (spc_factor > 0)) {
                if (spc_factor > _SESSION.DEFAULT_SELLER_PLATFORM_CHARGE) {
                    Ext.Ajax.request({
                        url: modURL + '&op=checkSPCData',
                        params: {spc_type: spc_type, spc_detail: spc_detail, spc_factor: spc_factor},
                        failure: function (response) {
                            Ext.MessageBox.alert('Error', response.responseText);

                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.SellingPrice.retalineSPCSave();
                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.MessageBox.confirm('Confirm', tmp.message, function (btn) {
                                    if (btn == 'yes') {
                                        Application.SellingPrice.retalineSPCSave();
                                    }
                                });
                            } else if (tmp.success === false && tmp.valid === false) {
                                Ext.Msg.alert('Error', tmp.message);
                            } else {
                                Ext.Msg.alert('Error', tmp.message);
                            }
                        }
                    });
                } else {
                    Ext.Msg.alert('Notification', 'Seller Platform Charge should be greater than minmum value.');
                }


            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        },selectValue: function (type) {
            var _selectValueWindow = new Ext.Window({
                layout: 'fit',
                height: winsize.height * 0.8,
                width: winsize.width * 0.8,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                id: "selectValueWindow",
                title:"Select Detail",
                bodyStyle: {"background-color": "white"},
                items: [valueSelectionGRid(type)],
                fbar: []

            });
            _selectValueWindow.doLayout();
            _selectValueWindow.show();
            _selectValueWindow.center();

  }
    }
}();