Application.VirtualCategory = function () {
    var recs_per_page = 18;
    var modURL = '?module=virtual_category';
    var current_type;
    var winLoadMask;
    var winsize = Ext.getBody().getViewSize();
    var loadCount;
    //var winsize = getWindowSize();

    var gridSelectionChangedcat = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelVirtualCategory').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelVirtualCategory').getSelectionModel().getSelections()[0].data.vc_id;
            Ext.getCmp('tabpanelVirtualCategory').setActiveTab(0);
            Application.VirtualCategory.Cache.vc_id = ID;
            Application.VirtualCategory.ViewMode(ID);
        } else {
            Application.VirtualCategory.Cache.vc_id = 0;
            Application.VirtualCategory.ViewMode(0);
        }
    };

    var ListmainVCPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            id: id,
            title: 'Virtual Categories',
            items: [mainGrid(), vcTabPanel()]
        });
        return panel;
    };
    var vcGridStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=listVirtualCategory',
            method: 'post',
            fields: ['vc_id', 'vc_name', 'vc_parentCategoryId', 'vc_categoryId', 'parent_category', 'category_name', 'vc_status'],
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function () {
                }, load: function () {
                    loadCount++;
                    if (loadCount == 1) {
                        Ext.getCmp('gridpanelVirtualCategory').getSelectionModel().selectRow(0);
                    }
                    //console.log('loadCount: ' + loadCount);
                }
            }
        });
        return store;
    };
    var mainGrid = function () {

        var grid_store = vcGridStore();
        var customerGrid_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'vc_name'
                }, {
                    type: 'string',
                    dataIndex: 'parent_category'
                }, {
                    type: 'string',
                    dataIndex: 'category_name'
                }, {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'vc_status'
                }
            ]
        });
        customerGrid_filter.remote = true;
        customerGrid_filter.autoReload = true;

        var SP_grid = new Ext.grid.GridPanel({
            store: grid_store,
            id: 'gridpanelVirtualCategory',
            region: 'center',
            width: winsize.width * 0.5,
            frame: true,
            border: false,
            layout: 'fit',
            loadMask: true,
            plugins: [customerGrid_filter],
            columns: [
                {
                    header: 'Virtual Category',
                    sortable: true,
                    dataIndex: 'vc_name',
                    width: 175
                },
                {
                    header: 'Department ',
                    sortable: true,
                    dataIndex: 'parent_category',
                    width: 175
                },
                {
                    header: 'Category',
                    sortable: true,
                    dataIndex: 'category_name',
                    width: 175
                }, {
                    header: 'Status',
                    dataIndex: 'vc_status',
                    sortable: true,
                    tooltip: 'Status'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            vcActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
            ],
            viewConfig: {
                forceFit: true,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Virtual Category',
                    tooltip: 'Create Virtual Category',
                    iconCls: 'finascop_add',
                    handler: function () {
                        Application.VirtualCategory.addNewVirtualCategory(0);
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
                    selectionchange: gridSelectionChangedcat
                }}),
            listeners: {
                rowclick: function (grid, rowIndex, e) {

                }}});
        return SP_grid;
    };
    var vcActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit Virtual Category",
                handler: function () {
                    var vc_id = Ext.getCmp('gridpanelVirtualCategory').getSelectionModel().getSelections()[0].data.vc_id;

                    Application.VirtualCategory.addNewVirtualCategory(vc_id);

                }
            }, {
                text: "Add Items",
                handler: function () {
                    addVendorItem(Application.VirtualCategory.Cache.vc_id);

                }
            }, {
                text: "Upload Image",
                handler: function () {
                    var vc_id = Ext.getCmp('gridpanelVirtualCategory').getSelectionModel().getSelections()[0].data.vc_id;


                    var uploadtype = 'virtualCategory';
                    Ext.Ajax.request({
                        url: modURL + '&op=getVCImage',
                        method: 'POST',
                        params: {
                            vc_id: vc_id
                        },
                        success: function (res) {
                            var tmp = Ext.decode(res.responseText);
                            console.log("temp is -", tmp);
                            if (tmp.data != '')
                            {
                                var img_url = tmp.data[0].image_url;
                                Application.VirtualCategory.uploadimageCategory(vc_id, uploadtype, img_url);
                            }
                            else {
                                var img_url = '';
                                Application.VirtualCategory.uploadimageCategory(vc_id, uploadtype, img_url);

                            }
                        }
                    })
                }
            }, {
                text: "Status Change",
                handler: function () {
                    var vc_id = Ext.getCmp('gridpanelVirtualCategory').getSelectionModel().getSelections()[0].data.vc_id;
                    var vc_status = Ext.getCmp('gridpanelVirtualCategory').getSelectionModel().getSelections()[0].data.vc_status;
                    Ext.Ajax.request({
                        url: modURL + '&op=statusChange',
                        method: 'POST',
                        waitMsg: 'Processing',
                        params: {
                            vc_id: vc_id,
                            vc_status: vc_status
                        },
                        failure: function (response, options) {
                            var tmp = Ext.util.JSON.decode(response.responseText);
                            Ext.MessageBox.alert('Error', 'tmp.msg');
                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);

                            if (tmp.success === true) {
                                Application.example.msg('Success', tmp.msg);
                                Ext.getCmp('gridpanelVirtualCategory').getStore().load();


                            } else {
                                Ext.MessageBox.alert("Error", tmp.msg);
                            }
                        }
                    });
                }
            }]
    });
    var vcTabPanel = function () {
        var panel = new Ext.TabPanel({
            region: 'east',
            width: winsize.width * 0.5,
            height: winsize.height * 0.6,
            activeTab: 0,
            flex: 1,
            plain: true,
            frame: true,
            id: 'tabpanelVirtualCategory',
            items: [
                {
                    title: 'Details',
                    id: 'property_grid_id',
                    width: winsize.width * 0.5,
                    tpl: new Ext.XTemplate('<div class="details-outer">',
                            '<table border="0" width="99%" class="details_view_table">',
                            '<tr><th>Virtual Category :</th><td> {vc_name} </td></tr>',
                            '<tr><th>In Home Menu:</th><td>  {vc_isHome}</td></tr>',
                            '<tr><th>In Category List:</th><td>  {vc_isInCategory}</td></tr>',
                            '<tpl if="vc_isInCategory == \'Yes\'">',
                            '<tr><th>Department:</th><td>  {parent_category}</td></tr>',
                            '<tr><th>Category:</th><td>  {category_name}</td></tr>',
                            '</tpl>',
                            '<tr><th>Status:</th><td>  {vc_status}</td></tr>',
                            '<tpl if="image_url != null">',
                            '<tpl if="image_url != \'\'">',
                            '<tr><td>',
                            '<div border=0 ><img border=0 width="200" id="vcDtlsViewPanel" height="200" src="{image_url}"></img></div>',
                            '</td></tr>',
                            '</tpl>',
                            '</tpl>',
                            '</table>',
                            '</div>')
                },
                {
                    id: 'gridpanelPurchaseAdditem',
                    title: 'Items',
                    frame: false, width: winsize.width * 0.6,
                    border: false,
                    items: [additemGrid()]
                }],
            listeners: {
                tabchange: function (sd, tab)
                {


                }
            }
        });
        return panel;
    };
    var additemGrid = function () {
        var addItemStorenew = addItemStore();
        var vendoritem_filter = new Ext.ux.grid.GridFilters({
            //remote: true,
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
            height: winsize.height * 0.7,
            frame: true,
            border: false,
            layout: 'fit',
            region: 'center',
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelVendorAdditem',
            columns: [{
                    header: 'Item Name',
                    width: 400,
                    dataIndex: 'itemName',
                    hideable: true,
                    sortable: true
                },
                {
                    header: 'Item Type',
                    dataIndex: 'itemType',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Brand',
                    dataIndex: 'stit_brand_name',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Quantity',
                    dataIndex: 'stit_quantity',
                    hideable: true,
                    sortable: false
                }, {
                    header: 'Least Packing Unit',
                    dataIndex: 'least_package_type_name',
                    hideable: true,
                    sortable: true
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
                                deleteItem(record.get('stpi_id'), record.get('vc_id'));
                            }
                        }
                    ]

                }],
            tbar: new Ext.Toolbar({
                items: [{
                        xtype: 'button', text: 'Add Items',
                        tooltip: 'Add Items',
                        iconCls: 'add',
                        handler: function () {
                            addVendorItem(Application.VirtualCategory.Cache.vc_id);
                        }
                    }
                ]
            }),
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
    var deleteItem = function (id, vc_id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteVCItem',
                    params: {
                        id: id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed item');
                            Ext.getCmp('gridpanelVendorAdditem').getStore().load({
                                params: {
                                    vc_id: vc_id
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
    var addItemStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listitemvc',
            fields: ['itemType', 'itemId', 'itemName', 'stit_type', 'stpi_id', 'stit_brand_name', 'stit_quantity', 'least_package_type_name', 'vc_id'],
            remoteSort: true,
            root: 'data',
            totalProperty: 'totalCount'

        });
        return store;
    };
    var addVendorItem = function (cid) {
        current_type = 1;
        var resultWindow = new Ext.Window({
            id: "windowFinascopStockAddvenderitemCreatevendoritem",
            title: 'Virtual Category Items',
            //iconCls: 'vender-items',
            shadow: false,
            height: 400,
            width: 800,
            layout: 'fit',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [vendorGrid(cid)],
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
                        var selectitem = Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var itemarr = [];
                        for (var i = 0; i < selectedcount; i++)
                        {
                            itemarr.push(selectitem[i].data.stit_ID);
                        }
                        if (selectedcount != 0)
                        {
                            Application.VirtualCategory.Cache.cid = cid;
                            Application.VirtualCategory.Cache.itemarr = itemarr;
                            var itemType = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                            Application.VirtualCategory.Cache.itemType = itemType;
                            Application.VirtualCategory.saveCheckedItem(cid, itemarr, itemType);
                        } else
                        {
                            Ext.MessageBox.alert("Notification", "Please check,Some box entries are not valid.");
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

    };
    var vendorGrid = function (cid) {
        var vendorcol = venderItemColmodel();
        var venderstore = venderItemStore();
        var vendorlist = vendortb();

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
            hideMode: 'display',
            loadMask: true,
            store: venderstore,
            colModel: vendorcol,
            tbar: vendorlist,
            plugins: [vendor_filter],
            width: 800,
            height: 400,
            frame: false,
            border: false,
            hideBorders: true,
            //iconCls: 'icon-grid',
            id: 'gridFinascopStockVenderitemGridgeneration',
            sm: check_box,
            viewConfig: {
                forceFit: true
            }


        });
        return vendorItemgrid;
    };
    var rowno = new Ext.grid.RowNumberer();
    rowno.width = 30;
    var check_box = new Ext.grid.CheckboxSelectionModel({
        multiSelect: true
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
                    header: 'Sub Category',
                    dataIndex: 'stit_category_name',
                    hideable: true,
                    sortable: false
                }
            ]
        });
        return colmodel;
    };
    var venderItemStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'POST',
            url: modURL + '&op=vcitemlisting',
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            fields: ['stit_itemName', 'stit_ID', 'stit_brand_name', 'stit_SKU', 'stit_quantity', 'least_package_type_name', 'stit_category_name'],
            listeners: {
                beforeload: function (thisStore, options) {
                    thisStore.baseParams.current_type = current_type;
                }
            }
        });


        return store;
    };
    var vendortb = function () {
        var tbar = new Ext.Toolbar({
            //layout: 'column',
            style: 'margin:5px 1px 5px 1px;',
            //labelWidth: 100,
            labelAlign: 'left',
            frame: false,
            border: false,
            hideBorders: true,
            items: [
                {
                    xtype: 'radiogroup',
                    width: 150,
                    id: 'radiobuttonFinascopStockId',
                    //columnWidth: 0.25,
                    items: [
                        {boxLabel: 'Medicine', name: 'rb-auto', inputValue: 1, labelWidth: 100},
                        {boxLabel: 'Product', name: 'rb-auto', inputValue: 2, labelWidth: 100, checked: true}

                    ],
                    listeners: {
                        change: function (event, checked)
                        {
                            var current_firstid = event.items.items[0].inputValue;
                            var current_secondid = event.items.items[1].inputValue;
                            //var current_thirdid = event.items.items[2].inputValue;
                            var radioid = Ext.getCmp('radiobuttonFinascopStockId').getValue();

                            if (radioid == current_secondid)
                            {
                                var item_name = '';
                                //filterItems(item_name, radioid);
                            } else if (radioid == current_firstid)
                            {
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
                    // columnWidth: 0.025
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/search.png',
                    xtype: 'button',
                    //columnWidth: 0.075,
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
                    // columnWidth: 0.025
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/reset.png',
                    xtype: 'button',
                    //columnWidth: 0.075,
                    text: 'Reset',
                    handler: function () {
                        Ext.getCmp('radiosearch').reset();
                        filterItems('', '');
                        //Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getStore().reload();
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
    var categoryuploadForm = function (img) {
        return new Ext.Panel({
            height: "400",
            items: [new Ext.Panel({
                    layout: "fit",
                    id: 'cat_main_image_panel',
                    tpl: new Ext.XTemplate('<div class="details-outer">',
                            '<img width="200" id="demo123" height="200" src="{img_root}"></img>',
                            '</div>')
                }),
                {
                    xtype: 'hidden',
                    id: 'aws_file_location',
                    name: 'aws_file_location'
                }, {
                    xtype: 'hidden',
                    id: 'aws_file_bucket',
                    name: 'aws_file_bucket'
                },
                new Ext.form.FormPanel({
                    id: 'category_image_upload',
                    layout: 'form',
                    fileUpload: true,
                    autoHeight: true,
                    frame: true,
                    items: [
                        {
                            xtype: 'hidden',
                            id: 'file_name', name: 'file_name'
                        }, {
                            xtype: 'hidden',
                            id: 'albumBucketName',
                            name: 'albumBucketName'
                        }, {
                            xtype: 'hidden',
                            id: 'accessKey',
                            name: 'accessKey'
                        }, {
                            xtype: 'hidden',
                            id: 'secretKey',
                            name: 'secretKey'
                        }, {
                            xtype: 'hidden',
                            id: 'bucketRegion',
                            name: 'bucketRegion'
                        },
                        {
                            xtype: 'hidden',
                            id: 'oncompleteurl',
                            name: 'oncompleteurl'
                        },
                        {
                            xtype: 'hidden',
                            id: 'img_path_db',
                            name: 'img_path_db'
                        },
                        {
                            xtype: 'box',
                            width: 200,
                            height: 200,
                            id: 'exist_img_box',
                            autoEl: {tag: 'img', src: img, width: 200, height: 200}
                        }
                    ],
                    buttons: [
                        {
                            xtype: 'fileuploadfield',
                            id: 'categoryimg_file',
                            anchor: '98%',
                            fieldLabel: 'Select File',
                            name: 'file',
                            allowBlank: true,
                            buttonOnly: true,
                            // hidden: true,
                            buttonCfg: {
                                text: 'Choose Image',
                                //iconCls: 'finascop_upload_file',
                                width: 80
                            },
                            validator: function (v) {
                                if (v != '') {
                                    v = v.toLowerCase();
                                    var exp = /^.*\.(png|jpg|gif)$/i;
                                    if (!(exp.test(v))) {
                                        Ext.Msg.alert("Notification", "Upload a valid image file");
                                        return;
                                        //return 'Upload a valid image file of format JPG.';
                                    }

                                    var categoryimg_file = Ext.getCmp('categoryimg_file').getValue();
                                    if (categoryimg_file == '') {
                                        Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                        return;
                                    }

                                    var category_image_upload = Ext.getCmp('category_image_upload').getForm();
                                    if (category_image_upload.isValid()) {
                                        Ext.getCmp('exist_img_box').hide();
                                        winLoadMask.show();
                                        addPhoto();
                                    }
                                    return true;
                                }
                            }
                        }]
                })]
        });
        // });
    };
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    ;
    function addPhoto() {

        var albumBucketName = Ext.getCmp('albumBucketName').getValue();
        var bucketRegion = Ext.getCmp('bucketRegion').getValue();
        var filepath = Ext.getCmp('oncompleteurl').getValue();
        AWS.config.update({
            region: bucketRegion,
            credentials: new AWS.Credentials(
                    Ext.getCmp('accessKey').getValue(),
                    Ext.getCmp('secretKey').getValue(), null
                    )
        });
        var s3 = new AWS.S3({
            apiVersion: '2006-03-01',
            params: {Bucket: albumBucketName}
        });
        var files = document.getElementById('categoryimg_file-file').files;
        if (!files.length) {
            winLoadMask.hide();
            return alert('Please choose a file to upload first.');
        }
        var file = files[0];
        var actualfileName = file.name;
        var file_Name = JSON.stringify(actualfileName).slice(1, -1);
        var fileExt = file_Name.split('.').pop();

        var fileName = uuidv4();
        fileName = fileName + '.' + fileExt;

        s3.upload({
            Key: filepath + fileName, /*file_Name*/ /*from server*/
            Body: file,
            ACL: 'public-read'
        }, function (err, data) {

            if (err) {
                winLoadMask.hide();
                var img_src = Ext.BLANK_IMAGE_URL;
                Ext.getCmp('cat_main_image_panel').update({'img_root': img_src});
                return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location)) {

                winLoadMask.hide();
                Application.example.msg("Notification", 'File uploaded successfully.');
                Application.VirtualCategory.UploadedFileLocation = data.Location;
                Application.VirtualCategory.UploadedFileBucket = data.Bucket;
                Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
                Ext.getCmp('aws_file_location').setValue(data.Location);
                /* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
                Ext.getCmp('cat_main_image_panel').update({'img_root': Application.VirtualCategory.UploadedFileLocation});
            }
        });
    }
    ;
    return{
        Cache: {},
        initVirtualCategory: function (type) {
            loadCount = 0;
            var panelId = 'virtualCategoryMainPanel';
            var listVendor = Ext.getCmp(panelId);
            if (Ext.isEmpty(listVendor))
            {
                listVendor = ListmainVCPanel(panelId);
                Application.UI.addTab(listVendor);
                listVendor.doLayout();
            } else
            {
                Application.UI.addTab(listVendor);
            }

        }, addNewVirtualCategory: function (vc_id) {
            var title;
            if (vc_id > 0) {
                title = 'Edit Virtual Category';
            } else {
                title = 'Create Virtual Category';
            }

            var win_id = "windowAddNewVC";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {
                win = new Ext.Window({
                    id: win_id,
                    title: title,
                    layout: 'fit',
                    width: winsize.width * 0.4,
                    height: 250,
                    plain: false,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    border: false,
                    resizable: false,
                    items: new Ext.FormPanel({
                        id: 'formpanelVirtualCategory',
                        autoHeight: true,
                        frame: true,
                        border: false,
                        labelAlign: 'top',
                        bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 0px 5px"},
                        //bodyStyle: {"padding": "5px"},
                        items: [{
                                xtype: 'textfield',
                                id: 'vc_id',
                                name: 'n[vc_id]',
                                hidden: true
                            }, {
                                xtype: 'panel',
                                layout: 'column',
                                frame: false,
                                border: false,
                                bodyStyle: {"background-color": "F1F1F1", "padding": "5px 2px 0px 2px"},
                                items: [{
                                        columnWidth: 1,
                                        layout: 'form',
                                        frame: false,
                                        border: false,
                                        bodyStyle: {"background-color": "F1F1F1", "padding": "5px 2px 0px 2px"},
                                        items: [
                                            {
                                                xtype: 'textfield',
                                                id: 'vc_name',
                                                name: 'n[vc_name]',
                                                fieldLabel: 'Virtual Category',
                                                allowBlank: false,
                                                tabIndex: 101,
                                                anchor: '100%'
                                            }
                                        ]
                                    }]
                            }, {
                                xtype: 'panel',
                                columnWidth: 1,
                                layout: 'column',
                                frame: false,
                                border: false,
                                bodyStyle: {"background-color": "F1F1F1", "padding": "0px 2px 0px 3px"},
                                items: [{
                                        columnWidth: .35,
                                        layout: 'form',
                                        frame: false,
                                        border: false,
                                        bodyStyle: {"background-color": "F1F1F1", "padding": "0px 2px 0px 3px"},
                                        items: [
                                            {
                                                xtype: 'checkbox',
                                                checked: false,
                                                id: 'vc_isHome',
                                                tabIndex: 102,
                                                anchor: '99%',
                                                name: 'n[vc_isHome]',
                                                labelAlign: 'right',
                                                inputValue: 1,
                                                boxLabel: 'Include in Home Menu'
                                            }
                                        ]
                                    }, {
                                        columnWidth: 0.35,
                                        layout: 'form',
                                        frame: false,
                                        border: false,
                                        bodyStyle: {"background-color": "F1F1F1", "padding": "0px 2px 0px 2px"},
                                        items: [
                                            {
                                                xtype: 'checkbox',
                                                checked: false,
                                                anchor: '99%',
                                                id: 'vc_isInCategory',
                                                name: 'n[vc_isInCategory]',
                                                inputValue: 1,
                                                labelAlign: 'right',
                                                tabIndex: 103,
                                                boxLabel: 'Show in Category List',
                                                listeners: {
                                                    check: function (cbo, checked) {
                                                        if (checked == true) {
                                                            Ext.getCmp('vc_parentCategoryId').enable();
                                                            Ext.getCmp('vc_categoryId').enable();
                                                            Ext.getCmp('vc_parentCategoryId').allowBlank = false;
                                                            Ext.getCmp('vc_categoryId').allowBlank = false;
                                                        } else {
                                                            Ext.getCmp('vc_parentCategoryId').reset();
                                                            Ext.getCmp('vc_categoryId').reset();
                                                            Ext.getCmp('vc_parentCategoryId').disable();
                                                            Ext.getCmp('vc_categoryId').disable();
                                                            Ext.getCmp('vc_parentCategoryId').allowBlank = true;
                                                            Ext.getCmp('vc_categoryId').allowBlank = true;
                                                        }
                                                    },
                                                    change: function () {
                                                        if (Ext.getCmp('vc_isInCategory').getValue() == false) {
                                                            Ext.getCmp('vc_parentCategoryId').reset();
                                                            Ext.getCmp('vc_categoryId').reset();
                                                        }
                                                    }
                                                }
                                            }
                                        ]
                                    }]

                            }, {
                                xtype: 'panel',
                                columnWidth: 1,
                                layout: 'column',
                                frame: false,
                                border: false,
                                bodyStyle: {"background-color": "F1F1F1", "padding": "10px 2px 10px 2px"},
                                items: [{
                                        columnWidth: 0.35,
                                        layout: 'form',
                                        frame: false,
                                        border: false,
                                        labelAlign: 'top',
                                        bodyStyle: {"background-color": "F1F1F1", "padding": "10px 5px 10px 5px"},
                                        items: [mkCombo({
                                                "type": 'mypha_productparent_category',
                                                "value": "parent_category_id",
                                                "display": "parent_category",
                                                "name": "n[vc_parentCategoryId]",
                                                "fieldLabel": "Show Under",
                                                "emptyText": "Select Department",
                                                "tabIndex": 104,
                                                anchor: '99%',
                                                "id": "vc_parentCategoryId",
                                                "listeners": false,
                                                "cx": "S_1"
                                            })
                                        ]
                                    }, {
                                        columnWidth: 0.35,
                                        layout: 'form',
                                        frame: false,
                                        border: false,
                                        bodyStyle: {"background-color": "F1F1F1", "padding": "10px 5px 10px 5px"},
                                        items: [mkCombo({
                                                "type": 'mypha_productcategory',
                                                "value": "category_id",
                                                "display": "category_name",
                                                "name": "n[vc_categoryId]",
                                                "fieldLabel": "Category",
                                                "hideLabel": true,
                                                "emptyText": "Select Category",
                                                "allowBlank": true,
                                                "tabIndex": 105,
                                                anchor: '99%',
                                                "id": "vc_categoryId",
                                                "listeners": false,
                                                "cx": "S_1"
                                            })
                                        ]
                                    }, {
                                        columnWidth: 0.30,
                                        layout: 'form',
                                        frame: false,
                                        border: false,
                                        bodyStyle: {"background-color": "F1F1F1", "padding": "10px 2px 10px 2px"},
                                        items: [mkCombo({
                                                "type": STATUS_COMBO_DATA,
                                                "value": "id",
                                                "display": "text",
                                                "anchor": "100%",
                                                "name": "n[vc_status]",
                                                "fieldLabel": "Status",
                                                "tabIndex": 106,
                                                "emptyText": "Set status..",
                                                "id": 'vc_status'
                                            })
                                        ]
                                    }]

                            }
                        ]
                    }),
                    buttons: [
                        {
                            text: 'Cancel',
                            id: 'Cancel_btns',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            iconCls: 'finascop_my-icon1',
                            tabIndex: 108,
                            handler: function () {
                                win.close();
                            }
                        }, {
                            text: 'Save',
                            id: 'save_btn',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                            iconCls: 'finascop_my-icon1',
                            tabIndex: 107,
                            handler: function () {
                                Application.VirtualCategory.saveVirtualCategory();
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            if (Ext.getCmp('vc_isInCategory').getValue() == true) {
                                Ext.getCmp('vc_parentCategoryId').enable();
                                Ext.getCmp('vc_categoryId').enable();
                                Ext.getCmp('vc_parentCategoryId').allowBlank = false;
                                Ext.getCmp('vc_categoryId').allowBlank = false;
                            } else {
                                Ext.getCmp('vc_parentCategoryId').disable();
                                Ext.getCmp('vc_categoryId').disable();
                                Ext.getCmp('vc_parentCategoryId').allowBlank = true;
                                Ext.getCmp('vc_categoryId').allowBlank = true;
                            }
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            winLoadMask = new Ext.LoadMask(Ext.getCmp('windowAddNewVC').getEl());
                            winLoadMask.msg = 'Please wait...';
                            if (vc_id > 0)
                            {
                                Ext.getCmp('formpanelVirtualCategory').getForm().load({
                                    waitTitle: 'Please Wait',
                                    waitMsg: 'Loading...',
                                    url: modURL + '&op=getVCDetails',
                                    params: {
                                        vc_id: vc_id,
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    success: function (form, action) {

                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.data.vc_categoryId == 0) {
                                            Ext.getCmp('vc_categoryId').reset();
                                        }
                                        if (tmp.data.vc_parentCategoryId == 0) {
                                            Ext.getCmp('vc_parentCategoryId').reset();
                                        }
                                    },
                                    failure: function (form, action) {
                                        Ext.Msg.alert("Error.", "This error");
                                    }
                                });
                            }
                        }
                    }
                });
            }
            win.doLayout();
            win.show(this);
            win.center();
        }, saveVirtualCategory: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelVirtualCategory').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveVirtualCategory',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Ext.getCmp('windowAddNewVC').close();
                            Application.example.msg('Success', tmp.message);
                            if (Application.VirtualCategory.VCAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('gridpanelVirtualCategory'));
                                Ext.getCmp('formpanelVirtualCategory').getForm().reset();
                                Ext.getCmp('gridpanelVirtualCategory').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('gridpanelVirtualCategory').selModel.getSelected().data.vc_id = tmp.data.vc_id;
                                Ext.getCmp('gridpanelVirtualCategory').getStore().reload();
                                Ext.getCmp('gridpanelVirtualCategory').getView().refresh();
                            }
                            Application.VirtualCategory.VCAddEdit = '';
                            Application.VirtualCategory.ViewMode(tmp.data.vc_id);


                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else {
                            Ext.Msg.alert("Error", tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Error', result.message);
                        } else {
                            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Notification', 'Please enter all required fields');
            }
        }, ViewMode: function (data) {
            var vc_id = arguments[0];

            Ext.Ajax.request({
                url: modURL + '&op=vcDetailsView',
                method: 'POST',
                params: {vc_id: vc_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('property_grid_id');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('property_grid_id').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            var _addItemgrid_store = Ext.getCmp('gridpanelVendorAdditem').getStore();
            _addItemgrid_store.load({
                params: {
                    vc_id: vc_id
                }
            });
        }, saveCheckedItem: function (cid, itemarr) {
            var cid = Application.VirtualCategory.Cache.cid;
            var itemarr = Application.VirtualCategory.Cache.itemarr;
            var itemtype = Application.VirtualCategory.Cache.itemType;
            Ext.Ajax.request({
                url: modURL + '&op=saveitemVC',
                method: 'post',
                params: {cid: cid, itemarr: Ext.encode(itemarr), itemtype: itemtype},
                success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true)
                    {
                        Application.example.msg('Success', 'Item details has been saved successfully.');
                        Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
                        Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();
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

        }, uploadimageCategory: function (rid, uploadtype, img_url) {
            if (uploadtype === 'virtualCategory')
            {
                var main_img_panel = categoryuploadForm(img_url);
            }


            var window_id = "catuploadwindow";
            var catuploadwindow = new Ext.Window({
                id: window_id,
                title: 'Upload Image',
                layout: 'fit',
                width: 230,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                iconCls: 'finascop_dataentry_receipt',
                resizable: false,
                closable: false,
                items: main_img_panel,
                listeners: {
                    afterrender: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        winLoadMask = new Ext.LoadMask(Ext.getCmp('catuploadwindow').getEl());
                        winLoadMask.msg = 'Please wait...';
                        if (uploadtype === 'virtualCategory')
                        {
                            Ext.getCmp('category_image_upload').getForm().load({
                                waitTitle: 'Please Wait',
                                waitMsg: 'Loading...',
                                url: modURL + '&op=get_catimg_s3_details',
                                params: {
                                    rid: rid,
                                    uploadtype: uploadtype,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                }
                            });
                        }

                    }
                },
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        iconCls: 'finascop_my-icon1',
                        handler: function () {

                            catuploadwindow.close();
                        }
                    }, {
                        text: 'Upload',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        iconCls: 'finascop_my-icon1',
                        id: 'saveButton',
                        handler: function () {
                            if (uploadtype === 'virtualCategory')
                            {
                                Application.VirtualCategory.saveVCategoryImage()
                            }


                        }
                    }]
            });
            catuploadwindow.doLayout();
            catuploadwindow.show(this);
            catuploadwindow.center();
        }, saveVCategoryImage: function () {

            var bucket_name = Ext.getCmp('albumBucketName').getValue();
            var file_name = Ext.getCmp('file_name').getValue();

            if (bucket_name != '' && file_name != '')
            {
                Ext.Ajax.request({
                    url: modURL + '&op=saveVCategoryImage',
                    method: 'POST',
                    params: {
                        vc_id: Application.VirtualCategory.Cache.vc_id,
                        uploaded_file_name: file_name,
                        bucket: bucket_name,
                        filepath: Ext.getCmp('aws_file_location').getValue(),
                        bucket_path: Ext.getCmp('oncompleteurl').getValue()
                    },
                    success: function (resp) {
                        var res = Ext.decode(resp.responseText);
                        if (res.success === true) {
                            Application.example.msg('Notification', 'Image saved..');
                            Ext.getCmp('catuploadwindow').close();

                        } else {
                            Ext.Msg.alert('Error', "Image not saved. Try again");
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Notification', result.error);
                        } else {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.MessageBox.alert('Error', result.error);
                        }
                    }
                });
            }
        }
    };

}();