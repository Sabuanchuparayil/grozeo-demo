Application.RetalineCurrentStock = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_currentstock';
    var recs_per_page = 22;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var currentStockStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCurrentStock',
                method: 'post'
            }),
            fields: ['blocked_count', 'cart_count', 'br_Name', 'stit_SKU', 'item_count', 'bom_offrPlacement', 'mrp', 'selling_price', 'optimumqty', 'minimumqty', 'maximumqty', 'stit_id', 'branchId', 'isAvailable',
                'rack_count', 'dispatch_count', 'receive_count', 'deliver_count', 'fsbg_name', 'fsbg_id', 'purchasing_unit', 'purchasing_unitname', 'least_package_type_name', 'isOnDemand', 'OnDemand', 'id', 'Availability',
            'product_category','stit_category_name','stit_brand_name','med_manufacturename','category_name', 'parent_category'],
            totalProperty: 'totalCount',
            root: 'data',
            //    remoteSort: true,
            autoLoad: true,
            sortInfo: {
                field: 'stit_id',
                direction: "ASC"
            },
            listeners: {
                load: function () {
                    this.baseParams.branchName = Ext.getCmp('comboxbranchname').getValue();
                }
            }
        });
        //store.setDefaultSort('stit_id', 'ASC');
        return store;
    };

    var currentStockActionMenu = function (e) {
        var packingExeActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "Update On Demand",
                    handler: function () {
                        var id = Ext.getCmp('panelMasterCurentStock').getSelectionModel().getSelections()[0].data.id;
                        var isOnDemand = Ext.getCmp('panelMasterCurentStock').getSelectionModel().getSelections()[0].data.isOnDemand;
                        selectAsOnDemand(id, isOnDemand);
                    }
                }, {
                    text: "Update Product Availability",
                    handler: function () {
                        var id = Ext.getCmp('panelMasterCurentStock').getSelectionModel().getSelections()[0].data.id;
                        var isAvailable = Ext.getCmp('panelMasterCurentStock').getSelectionModel().getSelections()[0].data.isAvailable;
                        selectAsIsAvailable(id, isAvailable);
                    }
                }]
        });
        packingExeActionMenu.showAt(e.getXY());
    };


    var masterPanelforCurrentStockGrid = function (id) {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });
        var branch_store = currentStockStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_category_name'
                },{
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                },{
                    type: 'string',
                    dataIndex: 'category_name'
                },{
                    type: 'string',
                    dataIndex: 'parent_category'
                },{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'fsbg_name'
                }, {
                    type: 'string',
                    dataIndex: 'receive_count'
                }, {
                    type: 'string',
                    dataIndex: 'blocked_count'
                }, {
                    type: 'string',
                    dataIndex: 'cart_count'
                }, {
                    type: 'string',
                    dataIndex: 'deliver_count'
                }, {
                    type: 'string',
                    dataIndex: 'dispatch_count'
                }, {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'item_count'
                }, {
                    type: 'string',
                    dataIndex: 'mrp'
                }, {
                    type: 'string',
                    dataIndex: 'selling_price'
                }, {
                    type: 'string',
                    dataIndex: 'optimumqty'
                }, {
                    type: 'string',
                    dataIndex: 'minimumqty'
                }, {
                    type: 'string',
                    dataIndex: 'maximumqty'
                }, {
                    type: 'string',
                    dataIndex: 'Availability'
                }, {
                    type: 'string',
                    dataIndex: 'OnDemand'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branch_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Current Stock',
            //iconCls: 'branch',
            plugins: [branch_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Name',
                    id: 'branch_name_auto_exp',
                    sortable: true,
                    hideable: true,
                    hidden: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: 'Item SKU',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item SKU',
                    width: 200
                }, {
                    header: 'Department',
                    hidden: true,
                    hideable: true,
                    dataIndex: 'parent_category',
                    tooltip: 'Department'
                },{
                    header: 'Category',
                    hidden: true,
                    hideable: true,
                    dataIndex: 'category_name',
                    tooltip: 'Category'
                },{
                    header: 'Subcategory',
                    hidden: true,
                    hideable: true,
                    dataIndex: 'stit_category_name',
                    tooltip: 'Subcategory'
                },{
                    header: 'Brand',
                    hidden: true,
                    hideable: true,
                    dataIndex: 'stit_brand_name',
                    tooltip: 'Brand'
                },{
                    header: 'Group',
                    hidden: true,
                    dataIndex: 'fsbg_name',
                    tooltip: 'Group'
                }, {
                    header: 'Offer',
                    dataIndex: 'bom_offrPlacement',
                    sortable: true,
                    hideable: false,
                    tooltip: 'Offer',
                    hidden: true
                }, {
                    header: 'Receivable',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'receive_count',
                    tooltip: 'Receivable'
                }, {
                    header: 'Blocked',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'blocked_count',
                    tooltip: 'Customer checked out but not ordered'
                }, {
                    header: 'To Cart',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'cart_count',
                    tooltip: 'Customer orderd to be packed'
                }, {
                    header: 'To Deliver',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'deliver_count',
                    tooltip: 'To Deliver'
                },
                {
                    header: 'Total Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'item_count',
                    tooltip: 'Total Count'
                }, {
                    header: 'Unit',
                    sortable: true,
                    align: 'right',
                    hideable: false,
                    dataIndex: 'least_package_type_name',
                    tooltip: 'Unit'
                }, {
                    header: 'Rack Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'rack_count',
                    tooltip: 'Rack Count',
                    hidden: true
                }, {
                    header: 'To Dispatch',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'dispatch_count',
                    tooltip: 'To Dispatch'
                },
                {
                    header: MRP,
                    sortable: true,
                    align: 'right',
                    dataIndex: 'mrp',
                    tooltip: MRP,
                    hidden: true
                },
                {
                    header: 'Selling Price',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'selling_price',
                    tooltip: 'Selling Price',
                    hidden: true
                },
                {
                    header: 'Optimum Quantity',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'optimumqty',
                    tooltip: 'Optimum',
                    hidden: true
                },
                {
                    header: 'Minimum Quantity',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'minimumqty',
                    tooltip: 'Minimum Quantity',
                    hidden: true
                },
                {
                    header: 'Maximum Quantity',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'maximumqty',
                    tooltip: 'Maximum Quantity',
                    hidden: true
                }, {
                    header: 'On Demand',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'OnDemand',
                    tooltip: 'On Demand'
                }, {
                    header: 'Available',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'Availability',
                    tooltip: 'Available'
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
                            currentStockActionMenu(e);
                        }
                    }
                },
                /* {
                 xtype: 'actioncolumn',
                 //header: 'Action',
                 hideable: true,
                 iconCls: 'downarrow',
                 tooltip: 'Choose Actions',
                 listeners: {
                 click: function (a, grid, rowindex, e) {
                 var record = grid.store.getAt(rowindex);
                 grid.getSelectionModel().selectRow(rowindex);
                 if (_SESSION.IS_RETALINE_LITE != '1') {
                 currentstockActionMenu.showAt(e.getXY());
                 }
                 
                 //action
                 }
                 }
                 }
                 {
                 header: 'Actions',
                 xtype: 'actioncolumn',
                 hideable: false,
                 sortable: false,
                 groupable: false,
                 items: [
                 {
                 iconCls: 'my-icon96',
                 tooltip: 'Barcodes',
                 handler: function (grid, rowIndex, colIndex, itm, evn) {
                 var record = grid.getStore().getAt(rowIndex);
                 Application.RetalineCurrentStock.viewBarcodes(record.get('stit_id'), record.get('branchId'), record.get('fsbg_id'));
                 }
                 }
                 ]
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
                resize: updatePagination,
                //viewready: updatePagination,
                afterrender: function () {
                    if (_SESSION.br_PyramidLevel == 1) {
                        Ext.getCmp("comboxbranchname").show();
                        Ext.getCmp("textboxBrmCpdDataeditCS").hide();
                    }
                    else {
                        Ext.getCmp("textboxBrmCpdDataeditCS").show();
                        Ext.getCmp("textboxBrmCpdDataeditCS").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchname").hide();
                    }
                }
            },
            tbar: [{
                    html: '&nbsp;BRANCH : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxBrmCpdDataeditCS',
                    name: 'textboxBrmCpdDataeditCS',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    readOnly: true,
                    hidden: true
                },
                {
                    xtype: 'combo',
                    id: 'comboxbranchname',
                    name: 'comboxbranchname',
                    mode: 'local',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Branch Name',
                    editable: true,
                    anchor: '97%',
                    store: BranchStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'br_Name',
                    valueField: 'br_ID',
                    hiddenName: 'comboxbranchname',
                    listeners: {
                        select: function () {
                            var branchName = Ext.getCmp('comboxbranchname').getValue();
                            Ext.getCmp('panelMasterCurentStock').getStore().load({
                                params: {
                                    branchName: branchName
                                }
                            });
                        }
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branch_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'branch_name_auto_exp'
        });
        return grid_panel;
    };
    var selectAsOnDemand = function (id, isOnDemand) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=addItemasOnDemand',
            params: {
                id: id,
                isOnDemand: isOnDemand
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {
                    Application.example.msg('Success', 'Item Added');
                    var branchName = Ext.getCmp('comboxbranchname').getValue();
                    Ext.getCmp('panelMasterCurentStock').getStore().load({
                        params: {
                            branchName: branchName
                        }
                    });
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });

    };
    var selectAsIsAvailable = function (id, isAvailable) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=addItemasAvailable',
            params: {
                id: id,
                isAvailable: isAvailable
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {
                    Application.example.msg('Success', 'Item Added');
                    var branchName = Ext.getCmp('comboxbranchname').getValue();
                    Ext.getCmp('panelMasterCurentStock').getStore().load({
                        params: {
                            branchName: branchName
                        }
                    });
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', "Error occurred");
            }
        });

    };
    var currentstockActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Details",
                handler: function () {
                    var stit_id = Ext.getCmp('panelMasterCurentStock').getSelectionModel().getSelections()[0].data.stit_id;
                    var branchId = Ext.getCmp('panelMasterCurentStock').getSelectionModel().getSelections()[0].data.branchId;
                    var fsbg_id = Ext.getCmp('panelMasterCurentStock').getSelectionModel().getSelections()[0].data.fsbg_id;
                    Application.RetalineCurrentStock.viewBarcodes(stit_id, branchId, fsbg_id);
                }
            }]
    });
    var _barcodeDetailsStore = function (stit_id, branchId, fsbg_id) {
        var _Store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listbarcodesinCurrentStock',
                method: 'post'
            }), reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data'
            }, ['stiid_barcode', 'stiid_createdon', 'stiid_updatedon', 'stiid_statusStat', 'stiid_customerRateHmDel', 'stiid_customerRateCouDel', 'stiid_customerRatePikup', 'stiid_expirydate',
                'stiid_leastSKUmrp', 'stiid_itemleastSKUpts', 'stiid_itemleastSKUptr', 'stiid_batchno', 'retailPrice', 'csPrice']),
            sortInfo: {
                field: 'stiid_barcode',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.stit_id = stit_id;
                    this.baseParams.branchId = branchId;
                    this.baseParams.fsbg_id = fsbg_id;
                }
            }
        });
        return _Store;
    };

    var _barcodeDetailsStatusStore = function (barcode) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listbarcodesMovement',
                method: 'post'
            }),
            fields: ['stiidm_id', 'stiidm_details', 'created_at'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.barcode = barcode;
                }
            }
        });
        return _Store;
    };
    var _barcodeDetailsGrid = function (stit_id, branchId, fsbg_id) {
        var spfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stiid_barcode'
                }, {
                    type: 'string',
                    dataIndex: 'stiid_batchno'
                }, {
                    type: 'string',
                    dataIndex: 'stiid_statusStat'
                }]
        });
        spfilters.remote = true;
        spfilters.autoReload = true;
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: _barcodeDetailsStore(stit_id, branchId, fsbg_id),
            iconCls: 'money',
            width: winsize.width * 0.8,
            height: 500,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforbarcodeDetails',
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    switch (record.get('stiid_statusStat')) {
                        case 'Godown and available for transfer':
                            return '';
                            break;
                        case 'Godown and marked for outward':
                            return 'finascop_indicateColCASHMERE';
                            break;
                        case 'Dispatched and in transit to branch':
                            return 'finascop_indicateColLIGHTORANGE';
                            break;
                        case 'Received at branch and ready for sale':
                            return 'finascop_indicateColPINK';
                            break;
                        case 'Item in Delivery Cart':
                            return 'finascop_indicateColLIGHTYELLOW';
                            break;
                        case 'Item Out For Delivery':
                            return 'finascop_indicateColPALEGREEN';
                            break;
                        case 'Item Delivered':
                            return 'finascop_indicateColGUMLEAFGREEN';
                            break;

                    }

                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [spfilters],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Barcode',
                    dataIndex: 'stiid_barcode',
                    sortable: true,
                    tooltip: 'Barcode',
                    hideable: false,
                    width: 100
                }, {
                    header: 'Batch',
                    dataIndex: 'stiid_batchno',
                    sortable: true,
                    tooltip: 'Batch',
                    hideable: false,
                    width: 100
                }, {
                    header: MRP,
                    dataIndex: 'stiid_leastSKUmrp',
                    sortable: true,
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: MRP,
                    hideable: false
                },
                {
                    header: 'Door Del. Rate',
                    dataIndex: 'stiid_customerRateHmDel',
                    sortable: true,
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Door Delivery Rate',
                    hideable: false
                }, {
                    header: 'Courier Rate',
                    dataIndex: 'stiid_customerRateCouDel',
                    sortable: true,
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Courier Rate',
                    hideable: false
                }, {
                    header: 'Pick Up Rate',
                    dataIndex: 'stiid_customerRatePikup',
                    sortable: true,
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Pick Up Rate',
                    hideable: false
                }, {
                    header: 'Distridutor Price',
                    dataIndex: 'csPrice',
                    sortable: true,
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Distridutor Price',
                    hideable: false
                }, {
                    header: 'Retail Price',
                    dataIndex: 'retailPrice',
                    sortable: true,
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Retail Price',
                    hideable: false
                }, {
                    header: 'Expiry Date',
                    dataIndex: 'stiid_expirydate',
                    sortable: true,
                    tooltip: 'Expiry Date',
                    hideable: false
                }, {
                    header: 'Created On',
                    dataIndex: 'stiid_createdon',
                    sortable: true,
                    tooltip: 'Created On',
                    hideable: false
                }, {
                    header: 'Updated On',
                    dataIndex: 'stiid_updatedon',
                    sortable: true,
                    tooltip: 'Created On',
                    hideable: false
                }, {
                    header: 'Status',
                    dataIndex: 'stiid_statusStat',
                    sortable: true,
                    tooltip: 'Status',
                    hideable: false,
                    width: 150,
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.stiid_statusStat + '"';
                        return value;
                    }
                }, {
                    header: ' ',
                    width: 15,
                }
            ],
            viewConfig: {
                forceFit: true,
                markDirty: false
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforbarcodeDetails').getStore().load({
                        params: {
                            stit_id: stit_id,
                            branchId: branchId,
                            fsbg_id: fsbg_id
                        }
                    });
                }, rowdblclick: function (grid, rowIndex, e) {
                    var rec = grid.getStore().getAt(rowIndex);
                    Application.RetalineCurrentStock.displayBarcodeDetails(rec.get('stiid_barcode'));
                }
            }
        });
        return _dispatchgridPanel;
    };
    var _barcodeMovementDetailsGrid = function (barcode) {
        var _barcodeDetailsgridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: _barcodeDetailsStatusStore(barcode),
            iconCls: 'money',
            width: winsize.width * 0.4,
            height: 400,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforbarcodeStatusDetails',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    dataIndex: 'created_at',
                    sortable: true,
                    tooltip: 'Barcode',
                    hideable: false,
                    width: 100
                },
                {
                    header: 'Details',
                    dataIndex: 'stiidm_details',
                    sortable: true,
                    tooltip: 'Details',
                    hideable: false,
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.data.stiidm_details + '"';
                        return value;
                    }
                }
            ],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforbarcodeStatusDetails').getStore().load({
                        params: {
                            barcode: barcode
                        }
                    });
                }
            }
        });
        return _barcodeDetailsgridPanel;
    };


    {//LiveStock Starts here

        var liveStockStore = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=getLiveStock',
                    method: 'post'
                }),
                fields: ['stit_ID', 'stit_SKU', 'branch_id', 'br_Name', 'item_count', 'mrp', 'epr', 'sup_Name', 'selling_price', 'stock_value'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    beforeload: function (store, e) { //

                        this.baseParams.stit_IDs = Ext.isEmpty(Ext.getCmp('cmbLSSKU').getValue()) ? 'All' : Ext.getCmp('cmbLSSKU').getValue();
                        this.baseParams.br_IDs = Ext.isEmpty(Ext.getCmp('cmbLSBranch').getValue()) ? 'Combined' : Ext.getCmp('cmbLSBranch').getValue();
                        this.baseParams.mrp = Ext.isEmpty(Ext.getCmp('cmbLSMRPGrouping').getValue()) ? 'Combined' : Ext.getCmp('cmbLSMRPGrouping').getValue();
                        this.baseParams.epr = Ext.isEmpty(Ext.getCmp('cmbLSEPRGrouping').getValue()) ? 'Combined' : Ext.getCmp('cmbLSEPRGrouping').getValue();
                        this.baseParams.partyId = Ext.isEmpty(Ext.getCmp('cmbLSSupplier').getValue()) ? 'Combined' : Ext.getCmp('cmbLSSupplier').getValue();
                        this.baseParams.excludeZeroStock = Ext.getCmp('lscb_exclude_zerostock').getValue();
                        var lsgColModel = Ext.getCmp('panelMasterLiveStock').getColumnModel();
                        lsgColModel.getColumnById('lsgSKUCol').hidden = (this.baseParams.stit_IDs == 'Combined');
                        lsgColModel.getColumnById('lsgBranchCol').hidden = (this.baseParams.br_IDs == 'Combined');
                        lsgColModel.getColumnById('lsgMRPCol').header = (this.baseParams.mrp == 'Combined') ? 'Average '+MRP : MRP;
                        lsgColModel.getColumnById('lsgEPRCol').header = (this.baseParams.epr == 'Combined') ? 'Average EPR' : 'EPR';
                        lsgColModel.getColumnById('lsgSupplierCol').header = (this.baseParams.partyId == 'Combined') ? 'No. of Suppliers' : 'Supplier';
                        //lsgColModel.getColumnById('lsgMRPCol').hidden = (this.baseParams.mrp == 'Combined');
                        //lsgColModel.getColumnById('lsgSupplierCol').hidden = (this.baseParams.partyId == 'Combined');

                        return !Application.RetalineCurrentStock.Cache.LiveStockInitatedOnly;
                    },
                    load: function (store, records, options) {

                        Ext.Ajax.request({
                            url: modURL + '&op=getAggreateDetails',
                            params: options.params,
                            failure: function (response) {
                                Ext.MessageBox.alert('Error', response.responseText);

                            },
                            success: function (response, options) {
                                var tmp = Ext.decode(response.responseText);
                                if (tmp.success === true) {
                                    var aggregateValues = tmp.aggregateValues;
                                    Ext.getCmp('pagingTotPanel').setTitle('<div style="font-weight:bold">Total Items: ' + aggregateValues.totItemCount + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total '+MRP+' Value : ' + aggregateValues.totMRP + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total  EPR : ' + aggregateValues.totEPR + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total Stock Value : ' + aggregateValues.totSellingPrice + '</div>');
                                    Ext.getCmp('panelMasterLiveStock').getBottomToolbar().doLayout(false, true);
                                } else {
                                    Ext.MessageBox.alert('Error', response.responseText);

                                }
                            }
                        });

                    },
                    exception: function (misc) {

                    }
                }
            });

            store.setDefaultSort('stit_ID', 'ASC');
            return store;
        };

        var getLsBrStore = function () {
            var BranchStore = new Ext.data.JsonStore({
                fields: ['br_ID', 'br_Name'],
                url: modURL + '&op=getViewableBranches',
                autoLoad: true,
                method: 'post',
            });

            return BranchStore;
        }

        var getSKUStore = function () {
            var SKUStore = new Ext.data.JsonStore({
                fields: ['stit_ID', 'stit_SKU'],
                url: modURL + '&op=getSKUs',
                autoLoad: true,
                method: 'post',
            });

            return SKUStore;
        }

        var getSupplierStore = function () {
            var PartyStore = new Ext.data.JsonStore({
                fields: ['partyId', 'partyName'],
                url: modURL + '&op=getpartyData',
                autoLoad: true,
                method: 'post',
            });

            return PartyStore;
        }

        var createLiveStockDCM = function () {
            return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
                {
                    header: 'SKU',
                    id: 'lsgSKUCol',
                    sortable: true,
                    hideable: true,
                    hidden: false,
                    dataIndex: 'stit_SKU',
                    tooltip: 'Item SKU',
                    width: 200
                }, {
                    header: 'Stock Count',
                    id: 'lsgStockCountCol',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'item_count',
                    tooltip: 'Stock'
                }, {
                    header: 'Branch',
                    id: 'lsgBranchCol',
                    sortable: true,
                    hideable: true,
                    hidden: false,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                }, {
                    header: MRP,
                    id: 'lsgMRPCol',
                    sortable: true,
                    align: 'right',
                    hidable: true,
                    hidden: false,
                    dataIndex: 'mrp',
                    tooltip: MRP
                }, {
                    header: 'EPR',
                    id: 'lsgEPRCol',
                    sortable: true,
                    align: 'right',
                    hidable: true,
                    hidden: false,
                    dataIndex: 'epr',
                    tooltip: 'EPR'
                }, {
                    header: 'Supplier',
                    id: 'lsgSupplierCol',
                    sortable: true,
                    hideable: true,
                    hidden: false,
                    dataIndex: 'sup_Name',
                    tooltip: 'Supplier Name'
                },
                {
                    header: 'Selling Price',
                    id: 'lsgSellingPriceCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'selling_price',
                    tooltip: 'Optimum'
                },
                {
                    header: 'Stock Value',
                    id: 'lsgStockValueCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'stock_value',
                    tooltip: 'Stock Value'
                },
                {
                    header: 'Actions',
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    items: [
                    ]
                }]);
        }

        var createLiveStockGridPanel = function (_liveStockPanelId) {
            var _liveStockStore = liveStockStore();
            var _liveStock_filter = new Ext.ux.grid.GridFilters({
                filters: [{
                        type: 'string',
                        dataIndex: 'stit_SKU'
                    }, {
                        type: 'string',
                        dataIndex: 'sup_Name'
                    }, {
                        type: 'string',
                        dataIndex: 'selling_price'
                    }, {
                        type: 'string',
                        dataIndex: 'stock_value'
                    }, {
                        type: 'numeric',
                        dataIndex: 'item_count'
                    }, {
                        type: 'numeric',
                        dataIndex: 'mrp'
                    },
                    {
                        type: 'numeric',
                        dataIndex: 'epr'
                    }
                ]
            });

            var lscb_Branch = getLsBrStore();
            var lscb_SKU = getSKUStore();
            var lscb_Supplier = getSupplierStore();
            var sm = new Ext.grid.RowSelectionModel({
                singleSelected: true
            });

            var grid_panel = new Ext.grid.GridPanel({
                store: _liveStockStore,
                layout: 'fit',
                frame: false,
                border: false,
                title: 'Stock Reports',
                iconCls: 'retaline_inventory_report',
                plugins: [_liveStock_filter],
                id: _liveStockPanelId,
                loadMask: true,
                cm: createLiveStockDCM(),
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                sm: sm,
                listeners: {
                    resize: updatePagination,
                    //viewready: updatePagination,
                    afterrender: function () {

                    }
                },
                tbar: [
                    {
                        html: '&nbsp;Items : &nbsp;',
                        id: 'lssku_label',
                    },
                    {
                        xtype: 'lovcombo',
                        displayField: 'stit_SKU',
                        valueField: 'stit_ID',
                        mode: 'remote',
                        id: 'cmbLSSKU',
                        width: 400,
                        emptyText: 'Select SKU(s)',
                        name: 'stit_SKU',
                        hiddenName: 'stit_ID',
                        typeAhead: false,
                        forceSelection: true,
                        triggerAction: 'all',
                        selectOnFocus: true,
                        lazyRender: true,
                        tabIndex: 413,
                        anchor: '98%',
                        minChars: 3,
                        store: lscb_SKU,
                        editable: true
                    },
                    {
                        html: '&nbsp;BRANCH : &nbsp;',
                        id: 'lsbranch_label',
                    },
                    {
                        xtype: 'lovcombo',
                        displayField: 'br_Name',
                        valueField: 'br_ID',
                        mode: 'remote',
                        id: 'cmbLSBranch',
                        emptyText: 'Select Branch(s)',
                        name: 'br_Name',
                        selectOnFocus: true,
                        forceSelection: true,
                        hiddenName: 'br_ID',
                        typeAhead: false,
                        triggerAction: 'all',
                        lazyRender: true,
                        tabIndex: 413,
                        minChars: 1,
                        anchor: '98%',
                        store: lscb_Branch,
                        editable: true
                    },
                    {
                        html: '&nbsp;'+MRP+' : &nbsp;',
                        id: 'lsMRP_label',
                    },
                    {
                        xtype: 'combo',
                        id: 'cmbLSMRPGrouping',
                        name: 'mrpGrouping',
                        mode: 'local',
                        width: 80,
                        typeAhead: false,
                        forceSelection: true,
                        fieldLabel: MRP,
                        editable: true,
                        anchor: '97%',
                        store: new Ext.data.ArrayStore({
                            fields: ['gr_ID', 'gr_Name'],
                            data: [['Combined', 'Combined'], ['Specific', 'Specific']]
                        }),
                        triggerAction: 'all',
                        minChars: 2,
                        displayField: 'gr_Name',
                        valueField: 'gr_ID',
                        hiddenName: 'cmbLSMRPGrouping',
                        listeners: {
                            select: function (combo, record, index) {
                            }
                        }
                    },
                    {
                        html: '&nbsp;EPR : &nbsp;',
                        id: 'lsEPR_label',
                    },
                    {
                        xtype: 'combo',
                        id: 'cmbLSEPRGrouping',
                        name: 'eprGrouping',
                        mode: 'local',
                        width: 80,
                        typeAhead: false,
                        forceSelection: true,
                        fieldLabel: 'EPR',
                        editable: true,
                        anchor: '97%',
                        store: new Ext.data.ArrayStore({
                            fields: ['grEPR_ID', 'grEPR_Name'],
                            data: [['Combined', 'Combined'], ['Specific', 'Specific']]
                        }),
                        triggerAction: 'all',
                        minChars: 2,
                        displayField: 'grEPR_Name',
                        valueField: 'grEPR_ID',
                        hiddenName: 'cmbLSEPRGrouping',
                        listeners: {
                            select: function (combo, record, index) {
                            }
                        }
                    },
                    {
                        html: '&nbsp;SUPPLIER : &nbsp;',
                        id: 'lsSupplier_label',
                    },
                    {
                        xtype: 'lovcombo',
                        displayField: 'partyName',
                        valueField: 'partyId',
                        mode: 'remote',
                        id: 'cmbLSSupplier',
                        emptyText: 'Select Suppliers(s)',
                        name: 'partyName',
                        hiddenName: 'partyId',
                        typeAhead: true,
                        forceSelection: true,
                        triggerAction: 'all',
                        lazyRender: true,
                        tabIndex: 413,
                        minChars: 1,
                        anchor: '98%',
                        store: lscb_Supplier,
                        editable: true
                    },
                    {
                        html: '&nbsp; &nbsp; &nbsp; Exclude Zero Stock : &nbsp;'
                    },
                    {
                        fieldLabel: 'Finalized',
                        xtype: 'checkbox',
                        id: 'lscb_exclude_zerostock',
                        name: 'lscb_reset_button',
                        anchor: '98%',
                        listeners: {
                            check: function (cbo, checked) {

                            }
                        }
                    }, '-', {
                        text: 'SUBMIT',
                        iconCls: 'finascop_search_btn',
                        id: 'lscb_filter_button',
                        style: 'margin-left:10px;',
                        handler: function () {
                            Application.RetalineCurrentStock.Cache.LiveStockInitatedOnly = false;
                            Ext.getCmp('panelMasterLiveStock').getStore().load(
                                    {
                                        params: {
                                            stit_IDs: Ext.isEmpty(Ext.getCmp('cmbLSSKU').getValue()) ? 'All' : Ext.getCmp('cmbLSSKU').getValue(),
                                            br_IDs: Ext.isEmpty(Ext.getCmp('cmbLSBranch').getValue()) ? 'Combined' : Ext.getCmp('cmbLSBranch').getValue(),
                                            mrp: Ext.isEmpty(Ext.getCmp('cmbLSMRPGrouping').getRawValue()) ? 'Combined' : Ext.getCmp('cmbLSMRPGrouping').getValue(),
                                            epr: Ext.isEmpty(Ext.getCmp('cmbLSEPRGrouping').getRawValue()) ? 'Combined' : Ext.getCmp('cmbLSEPRGrouping').getValue(),
                                            partyId: Ext.isEmpty(Ext.getCmp('cmbLSSupplier').getValue()) ? 'Combined' : Ext.getCmp('cmbLSSupplier').getValue(),
                                            excludeZeroStock: Ext.getCmp('lscb_exclude_zerostock').getValue(),
                                            start: 0,
                                            limit: recs_per_page
                                        },
                                        callback: function (records, optoins, success) {

                                            if (success == true) {


                                            }
                                            if (success === false) {
                                                Ext.Msg.alert('Error', "Error");
                                            }
                                        }
                                    }
                            );
                        }
                    }, '-', {
                        xtype: 'button',
                        text: 'Reset',
                        id: 'lscb_reset_button',
                        iconCls: 'finascop_my-resetpass',
                        handler: function () {
                            Ext.getCmp('cmbLSSKU').reset();
                            Application.RetalineCurrentStock.Cache.LiveStockInitatedOnly = true;
                            Ext.getCmp('cmbLSBranch').reset();
                            Ext.getCmp('cmbLSMRPGrouping').reset();
                            Ext.getCmp('cmbLSEPRGrouping').reset();
                            Ext.getCmp('lscb_exclude_zerostock').reset();
                            Ext.getCmp('cmbLSSupplier').reset();
                            Ext.getCmp('panelMasterLiveStock').getStore().removeAll();
                            Ext.getCmp('panelMasterLiveStock').filters.clearFilters();
                            Ext.getCmp('pagingTotPanel').setTitle('_');
                            Ext.getCmp('panelMasterLiveStock').getBottomToolbar().doLayout(false, true);


                            var filterItems = Ext.getCmp('panelMasterLiveStock').filters.filters.items;

                            for (var i = 0; i < filterItems.length; i++) {

                                var filter = filterItems[i];

                                filter.menu.items.each(function (element) {

                                    if (element.setChecked) {
                                        element.setChecked(false, true);
                                    }

                                    if (typeof element.getValue !== "undefined" && element.getValue() !== "") {
                                        element.setValue("");
                                    }
                                });
                            }
                        }
                    }
                ],
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: _liveStockStore,
                    plugins: [_liveStock_filter],
                    displayInfo: true,
                    items: [{
                            xtype: 'panel',
                            baseCls: 'x-plain',
                            width: winsize.width * 0.72,
                            layout: {
                                type: 'hbox',
                                align: 'center',
                                pack: 'center'
                            },
                            items: [{
                                    xtype: 'panel',
                                    baseCls: 'x-plain',
                                    id: 'pagingTotPanel',
                                    title: '-'
                                }]
                        }],
                    displayMsg: 'Displaying records {0} - {1} of {2}',
                    emptyMsg: "No records to display"
                }),
                stripeRows: true
            });
            return grid_panel;
        }

    }


    return {
        Cache: {},
        showLiveStock: function () {
            var _liveStockPanelId = 'panelMasterLiveStock';
            var _masterPanelLiveStock = Ext.getCmp(_liveStockPanelId);
            if (Ext.isEmpty(_masterPanelLiveStock)) {
                Application.RetalineCurrentStock.Cache.LiveStockInitatedOnly = true;
                _masterPanelLiveStock = createLiveStockGridPanel(_liveStockPanelId);
                Application.UI.addTab(_masterPanelLiveStock);
                _masterPanelLiveStock.doLayout();
            } else {
                Application.UI.addTab(_masterPanelLiveStock);
            }
        },
        initCurrentStock: function () {
            var _currentStockPanelId = 'panelMasterCurentStock';
            var _masterPanelCurrentStock = Ext.getCmp(_currentStockPanelId);
            if (Ext.isEmpty(_masterPanelCurrentStock)) {
                _masterPanelCurrentStock = masterPanelforCurrentStockGrid(_currentStockPanelId);
                Application.UI.addTab(_masterPanelCurrentStock);
                _masterPanelCurrentStock.doLayout();
            } else {
                Application.UI.addTab(_masterPanelCurrentStock);
            }
        }, viewBarcodes: function (stit_id, branchId, fsbg_id) {
            var _addnewItemsWindow = new Ext.Window({
                title: 'Item Details',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 500,
                width: winsize.width * 0.85,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_barcodeDetailsGrid(stit_id, branchId, fsbg_id)]
            });
            _addnewItemsWindow.doLayout();
            _addnewItemsWindow.show();
            _addnewItemsWindow.center();
        }, displayBarcodeDetails: function (barcode) {
            var _addnewItemsWindow = new Ext.Window({
                title: 'Barcode Movement of ' + barcode,
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 600,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_barcodeMovementDetailsGrid(barcode)]
            });
            _addnewItemsWindow.doLayout();
            _addnewItemsWindow.show();
            _addnewItemsWindow.center();
        }
    }
}();