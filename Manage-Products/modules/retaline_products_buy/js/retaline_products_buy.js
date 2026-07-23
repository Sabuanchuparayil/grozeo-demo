Application.RetBuyPrdcts = function () {
    var recs_per_page = 18;
    var modURL = '?module=retaline_products_buy';
    var current_type;
    var winLoadMask;
    var winsize = Ext.getBody().getViewSize();
    var loadCount;

    var gridSelectionChangedmyPrdcts = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelRetBuyPrdcts').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelRetBuyPrdcts').getSelectionModel().getSelections()[0].data.stit_ID;
            Application.RetBuyPrdcts.Cache.stit_ID = ID;
            Application.RetBuyPrdcts.ViewMode(ID);
        } else {
            Application.RetBuyPrdcts.Cache.stit_ID = 0;
            Application.RetBuyPrdcts.ViewMode(ID);
        }
    };
    var gridSelectionChangedPrdctsLog = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelLogBuyPrdcts').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelLogBuyPrdcts').getSelectionModel().getSelections()[0].data.stit_ID;
        }
    };

    var ListmainUsrPdtctsPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            id: id,
            title: 'My Products',
            items: [mainGrid(), vcTabPanel()]
        });
        return panel;
    };
    var vcTabPanel = function () {
        var panel = new Ext.Panel({
            region: 'east',
            width: winsize.width * 0.5,
            height: winsize.height * 0.6,
            autoScroll: true,
            cls: 'left_side_panel',
            plain: true,
            frame: false,
            border: false,
            id: 'tabpanelVirtualCategory',
            items: [
                {
                    title: 'Details',
                    layout: 'fit',
                    id: 'userPrdtsDeatailsView',
                    width: winsize.width * 0.48,
                    tpl: new Ext.XTemplate('<div class="details-outer">',
                            '<table border="0" width="99%" class="details_view_table">',
                            '<tr><th>SKU :</th><td> {stit_SKU} </td></tr>',
                            '<tr><th>Brand:</th><td>  {stit_brand_name}</td></tr>',
                            '<tr><th>Product Master:</th><td>  {stit_itemName}</td></tr>',
                            '<tr><th>Variant:</th><td>  {stit_product_variant}</td></tr>',
                            '<tr><th>Net Wt.:</th><td>  {stit_quantity}</td></tr>',
                            '<tr><th>Sub Category:</th><td>  {stit_category_name}</td></tr>',
                            '<tr><th>HSN Code:</th><td>  {stit_HSN_code}</td></tr>',
                            '<tr><th>GST:</th><td>  {stit_GST}</td></tr>',
                            '<tr><th>Return Time:</th><td>  {stit_itemReturnTime}</td></tr>',
                            '<tr><th>Country of Orgin:</th><td>  {stit_orgin_countryname}</td></tr>',
                            '<tr><th>Least Package Type:</th><td>  {least_package_type_name}</td></tr>',
                            '<tr><th>Ingredients:</th><td>  {stit_ingredients}</td></tr>',
                            '<tr><th>Preperation & Use:</th><td>  {stit_preparation_use}</td></tr>',
                            '<tr><th>Allergens:</th><td>  {stit_allergens}</td></tr>',
                            '<tr><th>Nutrition Label:</th><td>  {stit_nutritionlabel}</td></tr>',
                            '<tr><th>Short Description:</th><td>  {stit_Description}</td></tr>',
                            '<tr><th>Long Description:</th><td>  {stit_long_description}</td></tr>',
                            '<tpl if="image_urlend != null">',
                            '<tpl if="image_urlend != \'\'">',
                            '<tr><td>',
                            '<tr><th>Deafult Image:</th><td><div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div></td></tr>',
                            '</td></tr>',
                            '</tpl>',
                            '</tpl>',
                            '</table>',
                            '</div>')
                }]
        });
        return panel;
    };
    var itemmasterStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listItemMasterData',
                method: 'post'
            }),
            fields: ['stit_SKU', 'stit_itemName', 'stit_HSN_code', 'stit_category_name', 'stit_brand_name', 'stit_quantity', 'stit_product_variant', 'least_package_type_name', 'stit_ID'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelRetBuyPrdcts').getSelectionModel().selectRow(0);
                }
            }
        });
        store.setDefaultSort('stit_itemName', 'ASC');
        return store;
    };
    var mainGrid = function () {
        var grid_store = itemmasterStore();
        var customerGrid_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'stit_itemName'
                }, {
                    type: 'string',
                    dataIndex: 'stit_category_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_product_variant'
                }, {
                    type: 'string',
                    dataIndex: 'stit_quantity'
                },
            ]
        });
        customerGrid_filter.remote = true;
        customerGrid_filter.autoReload = true;

        var SP_grid = new Ext.grid.GridPanel({
            store: grid_store,
            id: 'gridpanelRetBuyPrdcts',
            region: 'center',
            frame: true,
            border: false,
            layout: 'fit',
            loadMask: true,
            plugins: [customerGrid_filter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'SKU',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'stit_SKU'
                }, {
                    header: 'Brand',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'stit_brand_name'
                },
                {
                    header: 'Product Master',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'stit_itemName'
                }, {
                    header: 'Variant',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'stit_product_variant'
                }, {
                    header: 'Net Weight',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'stit_quantity'
                },
                {
                    dataIndex: ' ',
                    width: 20
                }
            ],
            viewConfig: {
                forceFit: true,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Choose Products',
                    tooltip: 'Choose Products',
                    iconCls: 'finascop_add',
                    handler: function () {
                        Application.RetBuyPrdcts.mapProducts();
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: grid_store,
                displayInfo: true, plugins: [customerGrid_filter],
                displayMsg: 'Displaying items {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedmyPrdcts
                }}),
            listeners: {
                rowclick: function (grid, rowIndex, e) {

                }}});
        return SP_grid;
    };


    var deleteItem = function (id, stit_ID) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteSelectedUserItems',
                    params: {
                        id: id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed item');
                            Ext.getCmp('gridpanelVendorAdditem').getStore().load({
                                params: {
                                    stit_ID: stit_ID
                                }
                            });
                            var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                            var search_bar = Ext.getCmp('radiosearch').getValue();
                            if (item_search_item != 0 && search_bar != '') {
                                filterItems(search_bar, item_search_item);
                            }
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
    var addItemStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listSelectedItems',
            fields: ['UserId', 'rup_id', 'stit_type', 'itemType', 'itemId', 'itemName', 'stit_brand_name', 'stit_itemName', 'stit_product_variant'],
            remoteSort: true,
            root: 'data',
            totalProperty: 'totalCount'

        });
        return store;
    };

    var productsGrid = function (cid) {
        var vendorcol = venderItemColmodel();
        var venderstore = prdctSearchStore();
        var vendorlist = prdctSearchtb();

        var vendor_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'stit_category_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_itemName'
                }
            ]
        });
        vendor_filter.remote = true;
        vendor_filter.autoReload = true;

        var vendorItemgrid = new Ext.grid.GridPanel({
            loadMask: true,
            store: venderstore,
            colModel: vendorcol,
            tbar: vendorlist,
            plugins: [vendor_filter],
            region: 'center',
            height: 400,
            frame: false,
            border: false,
            hideBorders: true,
            id: 'gridFinascopStockVenderitemGridgeneration',
            sm: check_box,
            bbar: [{html: 'Total : ', align: 'left'}, {
                    id: 'audit_receipt_total_amount',
                    name: 'audit_receipt_total_amount',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    align: 'left',
                    value: 0,
                }, '->', {
                    iconCls: 'move-to-contacts',
                    xtype: 'button',
                    text: 'Move Selected Items',
                    handler: function () {
                        var selectitem = Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var itemarr = [];
                        for (var i = 0; i < selectedcount; i++)
                        {
                            itemarr.push(selectitem[i].data.stit_ID);
                        }
                        if (selectedcount != 0)
                        {
                            Application.RetBuyPrdcts.Cache.cid = cid;
                            Application.RetBuyPrdcts.Cache.itemarr = itemarr;
                            var itemType = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                            Application.RetBuyPrdcts.Cache.itemType = itemType;
                            Application.RetBuyPrdcts.saveCheckedItem(itemarr, itemType);
                        } else
                        {
                            Ext.MessageBox.alert("Notification", "Please check,Some box entries are not valid.");
                        }
                    }

                }],
            viewConfig: {
                forceFit: true
            }
        });
        return vendorItemgrid;
    };
    var rowno = new Ext.grid.RowNumberer();
    rowno.width = 30;
    var check_box = new Ext.grid.CheckboxSelectionModel({
        multiSelect: true,
        checkOnly: true,
        listeners: {
        }
    });
    var venderItemColmodel = function () {
        var colmodel = new Ext.grid.ColumnModel({
            sortable: true,
            columns: [
                check_box,
                rowno,
                {header: 'SKU Name', width: 200, dataIndex: 'stit_SKU', sortable: true},
                {
                    header: 'Product Master',
                    dataIndex: 'stit_itemName',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Brand',
                    dataIndex: 'stit_brand_name',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Variant',
                    dataIndex: 'stit_product_variant',
                    hideable: true,
                    sortable: false
                }
            ]
        });
        return colmodel;
    };
    var prdctSearchStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'POST',
            url: modURL + '&op=retProdctListing',
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            fields: ['stit_itemName', 'stit_ID', 'stit_brand_name', 'stit_SKU', 'stit_quantity', 'least_package_type_name', 'stit_category_name', 'stit_product_variant'],
            listeners: {
                beforeload: function (thisStore, options) {
                    thisStore.baseParams.current_type = current_type;
                },
                load: function (store, records, options) {
                    var storeCount = store.data.length;
                    storeCount = storeCount + ' Products';
                    Ext.getCmp('audit_receipt_total_amount').setValue(storeCount);
                }
            }
        });


        return store;
    };
    var prdctSearchtb = function () {
        var tbar = new Ext.Toolbar({
            style: 'margin:5px 1px 5px 1px;',
            labelAlign: 'left',
            frame: false,
            border: false,
            hideBorders: true,
            items: [
                {
                    xtype: 'radiogroup',
                    width: 150,
                    id: 'radiobuttonFinascopStockId',
                    items: [
                        {boxLabel: 'Medicine', name: 'rb-auto', inputValue: 1, labelWidth: 100},
                        {boxLabel: 'Product', name: 'rb-auto', inputValue: 2, labelWidth: 100, checked: true}

                    ],
                    listeners: {
                        change: function (event, checked)
                        {
                            var current_firstid = event.items.items[0].inputValue;
                            var current_secondid = event.items.items[1].inputValue;
                            var radioid = Ext.getCmp('radiobuttonFinascopStockId').getValue();

                            if (radioid == current_secondid)
                            {
                                var item_name = '';
                            } else if (radioid == current_firstid)
                            {
                                var item_name = '';
                            }


                        }
                    }
                },
                {
                    xtype: 'textfield',
                    id: 'radiosearch',
                    width: 350,
                    listeners: {
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                                var search_bar = Ext.getCmp('radiosearch').getValue();
                                if (item_search_item != 0 && search_bar != '')
                                {
                                    filterItems(search_bar, item_search_item);
                                }
                            }
                        }
                    }
                },
                {
                    frame: false,
                    border: false,
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/search.png',
                    xtype: 'button',
                    text: 'Search',
                    handler: function () {
                        var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                        var search_bar = Ext.getCmp('radiosearch').getValue();
                        if (item_search_item != 0 && search_bar != '')
                        {
                            filterItems(search_bar, item_search_item);
                        }
                    }
                },
                {
                    frame: false,
                    border: false,
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/reset.png',
                    xtype: 'button',
                    text: 'Reset',
                    handler: function () {
                        Ext.getCmp('radiosearch').reset();
                        filterItems('', '');
                    }

                }

            ]



        });
        return tbar;
    };
    var filterItems = function (item_name, radio_id)
    {
        var gridvalue = Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getStore();
        current_type = radio_id;
        gridvalue.baseParams = {
            currentItem: item_name,
            current_type: radio_id
        }
        gridvalue.load();
    };

    var selectedPrductsGrid = function () {
        var addItemStorenew = addItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            local: true,
            filters: [
                {
                    type: 'string',
                    dataIndex: 'itemName'
                }, {
                    type: 'string',
                    dataIndex: 'itemType'
                }, {
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_quantity'
                }, {
                    type: 'string',
                    dataIndex: 'least_package_type_name'
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
            id: 'gridpanelVendorAdditem',
            columns: [{
                    header: 'SKU',
                    width: 400,
                    dataIndex: 'itemName',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Product Master',
                    dataIndex: 'stit_itemName',
                    hideable: true,
                    sortable: true,
                },
                {
                    header: 'Item Type',
                    dataIndex: 'itemType',
                    hideable: true,
                    sortable: true,
                    hidden: true
                }, {
                    header: 'Brand',
                    dataIndex: 'stit_brand_name',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Variant',
                    dataIndex: 'stit_product_variant',
                    hideable: true,
                    sortable: false
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
                                deleteItem(record.get('rup_id'), record.get('stit_ID'));
                            }
                        }
                    ]

                }],
            tbar: [],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                rowclick: function (grid, rowIndex, e) {

                }
            }
        });
        return addItem;
    };
    var ListUsrPdtctsLogPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'fit',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            id: id,
            title: 'Product Purchase History',
            items: [prdctPurchaseLogGrid()]
        });
        return panel;
    };
    var prdctPurchaseLogStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPprdctPurchaseLog',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'ruph_id',
                root: 'data'
            }, ['ruph_id', 'stit_type', 'ruph_createdOn', 'stit_SKU', 'stit_ID', 'UserName', 'itemCount', 'itemPrice']),
            sortInfo: {
                field: 'ruph_createdOn',
                direction: "DESC"
            },
            groupField: 'ruph_createdOn',
            groupDir: 'DESC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function (ste, est) {
                    Ext.getCmp('nootitemto').focus();
                    Ext.getCmp('nootitempp').focus();
                }
            }
        });
        return store;
    };
    var prdctPurchaseLogGrid = function () {

        var grid_store = prdctPurchaseLogStore();
        var customerGrid_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'date',
                    dataIndex: 'ruph_createdOn'
                }
            ]
        });
        customerGrid_filter.remote = true;
        customerGrid_filter.autoReload = true;

        var SP_grid = new Ext.grid.GridPanel({
            store: grid_store,
            id: 'gridpanelLogBuyPrdcts',
            region: 'center',
            frame: true,
            border: false,
            layout: 'fit',
            loadMask: true,
            plugins: [new Ext.ux.grid.GroupSummary(), customerGrid_filter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Purchased On',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'ruph_createdOn'
                },
                {
                    header: 'No of products',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'itemCount',
                }, {
                    header: 'Price',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'itemPrice'
                }
            ],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            tbar: ['->', {'html': ' Total Products: '}, {
                    id: 'nootitemto',
                    name: 'nootitemto',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    xtype: 'textfield',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var itemInROStore = Ext.getCmp('gridpanelLogBuyPrdcts').getStore().sum('itemCount');
                            Ext.getCmp('nootitemto').setValue(itemInROStore); //store_stock

                        }
                    }
                }, {'html': ' Total Amount: '}, {
                    id: 'nootitempp',
                    name: 'nootitempp',
                    xtype: 'textfield',
                    border: 0,
                    hideBorders: true,
                    cls: 'textboxasLabel',
                    fieldStyle: 'background:none',
                    readOnly: true,
                    listeners: {
                        focus: function () {
                            var itemInROStore = Ext.getCmp('gridpanelLogBuyPrdcts').getStore().sum('itemPrice');
                            itemInROStore = itemInROStore + ' INR';
                            Ext.getCmp('nootitempp').setValue(itemInROStore); //store_stock

                        }
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: grid_store,
                displayInfo: true, plugins: [customerGrid_filter],
                displayMsg: 'Displaying items {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedPrdctsLog
                }}),
            listeners: {
                rowclick: function (grid, rowIndex, e) {

                }}});
        return SP_grid;
    };
    return{
        Cache: {},
        initMyPrdcts: function (type) {
            loadCount = 0;
            var panelId = 'usrProductsMainPanel';
            var listVendor = Ext.getCmp(panelId);
            if (Ext.isEmpty(listVendor))
            {
                listVendor = ListmainUsrPdtctsPanel(panelId);
                Application.UI.addTab(listVendor);
                listVendor.doLayout();
            } else
            {
                Application.UI.addTab(listVendor);
            }

        }, mapProducts: function (cid) {
            current_type = 1;
            var resultWindow = new Ext.Window({
                id: "windowFinascopStockAddvenderitemCreatevendoritem",
                title: 'Choose Products',
                shadow: false,
                height: 600,
                width: winsize.width * 0.85,
                layout: 'border',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [productsGrid(cid),
                    new Ext.Panel({
                        region: 'east',
                        frame: false,
                        border: true,
                        layout: 'fit',
                        autoScroll: false,
                        title: 'Selected Products',
                        bodyStyle: {"background-color": "white"},
                        id: 'order_parent_panel',
                        width: winsize.width * 0.4,
                        items: [selectedPrductsGrid(cid)]
                    })
                ],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        handler: function () {
                            Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();
                        }

                    },
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Save',
                        handler: function () {
                            var selectitem = Ext.getCmp('gridpanelVendorAdditem').getStore().totalLength;

                            if (selectitem != 0)
                            {
                                Application.RetBuyPrdcts.saveMappedProducts();
                            } else
                            {
                                Ext.MessageBox.alert("Notification", "Choose items to save.");
                            }
                        }
                    }

                ], listeners: {
                    afterrender: function () {
                        if (_SESSION.IS_MEDICINE_REQUIRED != 1) {
                            Ext.getCmp('radiobuttonFinascopStockId').hide();
                        }

                    }

                }

            });

            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();

        }, saveCheckedItem: function (itemarr, itemType) {
            Ext.Ajax.request({
                url: modURL + '&op=saveUserProducts',
                method: 'post',
                params: {itemarr: Ext.encode(itemarr), itemtype: itemType},
                success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true)
                    {
                        Ext.getCmp('gridpanelVendorAdditem').getStore().reload({
                            params: {
                                rup_status: 0
                            }
                        });
                        var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                        var search_bar = Ext.getCmp('radiosearch').getValue();
                        if (item_search_item != 0 && search_bar != '') {
                            filterItems(search_bar, item_search_item);
                        }
                    }

                }
            });

        }, saveMappedProducts: function (cid, itemarr) {

            Ext.Ajax.request({
                url: modURL + '&op=confirmMappedPrdcts',
                method: 'post',
                success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true)
                    {
                        Application.example.msg('Success', 'Item details has been saved successfully.');
                        //Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
                        Ext.getCmp('gridpanelVendorAdditem').getStore().reload({
                            params: {
                                rup_status: 0
                            }
                        });
                        var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                        var search_bar = Ext.getCmp('radiosearch').getValue();
                        if (item_search_item != 0 && search_bar != '') {
                            filterItems(search_bar, item_search_item);
                        }
                        //Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();
                        if (Ext.getCmp('gridpanelRetBuyPrdcts').getStore().getCount() > 0) {
                            Ext.getCmp('gridpanelRetBuyPrdcts').getStore().reload();
                        }

                    } else
                    {
                        Ext.MessageBox.alert('Notification', 'Product(s) already mapped.',
                                function (btn) {
                                    Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
                                    Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();

                                });
                    }
                }
            });

        }, userPrdctsLog: function (type) {
            loadCount = 0;
            var panelId = 'productLogMainPanel';
            var listVendor = Ext.getCmp(panelId);
            if (Ext.isEmpty(listVendor))
            {
                listVendor = ListUsrPdtctsLogPanel(panelId);
                Application.UI.addTab(listVendor);
                listVendor.doLayout();
            } else
            {
                Application.UI.addTab(listVendor);
            }

        }, ViewMode: function (data) {
            var stit_ID = arguments[0];

            Ext.Ajax.request({
                url: modURL + '&op=usrPrdtsDetailsView',
                method: 'POST',
                params: {stit_ID: stit_ID},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('userPrdtsDeatailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('userPrdtsDeatailsView').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
        }
    };

}();