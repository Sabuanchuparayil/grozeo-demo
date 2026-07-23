Application.FstockUpload = function () {

    var RECS_PER_PAGE = 12;
    var modURL = '?module=finascop_stock_upload';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionFsuChanged = function () {
        if (!Ext.isEmpty(Ext.getCmp('finstockUploadGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('finstockUploadGridPanel').getSelectionModel().getSelections()[0].data.fsu_id;
        }
    };
    var gridSelectionRRPChanged = function () {
        if (!Ext.isEmpty(Ext.getCmp('manageRRPGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manageRRPGridPanel').getSelectionModel().getSelections()[0].data.fsu_id;
        }
    };
    var finascopStockUploadGridStore = function () {
        var _finascopStockUploadList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listfinascopStockUpload',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fbiu_id',
                root: 'data'
            }, ['fbiu_id', 'branch_name', 'fbiu_status', 'fbiu_uploadedbyapi', 'fbiu_branch', {
                    name: 'fsu_date',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                }]),
            sortInfo: {
                field: 'fbiu_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    // Ext.getCmp('upEventGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _finascopStockUploadList;
    };
    var finascopStockUploadMainPanel = function (id) {
        var _fsuPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Stock Upload',
            iconCls: 'dispatch',
            id: id,
            items: [
                finascopStockUploadGrid()
            ]
        });
        return _fsuPanel;
    };
    var vBranchStore = function () {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });
        return BranchStore;
    };
    var finascopStockUploadGrid = function () {
        var BranchStore = vBranchStore();
        var _fsuGridStore = finascopStockUploadGridStore();
        var _fsuFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'branch_name'
                }, {
                    type: 'string',
                    dataIndex: 'fbiu_id'
                }, {
                    type: 'string',
                    dataIndex: 'fbiu_uploadedbyapi'
                }, {
                    type: 'string',
                    dataIndex: 'fsu_date'
                }, {
                    type: 'string',
                    dataIndex: 'fbiu_status'
                }
            ]
        });
        _fsuFilter.remote = true;
        _fsuFilter.autoReload = true;
        var _fsuGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _fsuGridStore,
            //iconCls: 'money',
            id: 'finstockUploadGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _fsuFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Ref.Id',
                    dataIndex: 'fbiu_id',
                    sortable: true,
                    tooltip: 'Reference Id',
                    hideable: true
                },
                {
                    header: 'Branch',
                    id: 'fsubr_name_auto_exp',
                    dataIndex: 'branch_name',
                    sortable: true,
                    tooltip: 'Branch',
                    hideable: true,
                    width: 200
                },
                {
                    header: 'Date',
                    dataIndex: 'fsu_date',
                    sortable: true,
                    tooltip: 'Date',
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-Y');
                        return dateret;
                    }
                }, {
                    header: 'Uploaded Mode',
                    dataIndex: 'fbiu_uploadedbyapi',
                    sortable: true,
                    tooltip: 'Uploaded Mode'
                }, {
                    header: 'Status',
                    dataIndex: 'fbiu_status',
                    sortable: true,
                    tooltip: 'Status'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    //   iconCls: 'downarrow',
                    icon: './resources/images/submenuicons/action.png',
                    tooltip: 'Choose Actions',
                    items: [
                        {
                            iconCls: 'application_view_detail',
                            tooltip: 'View Details',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                Application.FstockUpload.viewUploadedStock(record.get('fbiu_id'), record.get('fbiu_branch'));
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
                store: _fsuGridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionFsuChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                },
                //resize: onGridResize,
                afterrender: function () {
                    if (_SESSION.current_branch_iscpd == 1) {
                        Ext.getCmp("comboxbranchnamefsbiu").show();
                        Ext.getCmp("textboxfsbiuDataeditCS").hide();
                    }
                    else {
                        Ext.getCmp("textboxfsbiuDataeditCS").show();
                        Ext.getCmp("textboxfsbiuDataeditCS").setValue(_SESSION.current_branch);
                        Ext.getCmp("comboxbranchnamefsbiu").hide();
                    }
                    var branchName = Ext.getCmp('comboxbranchnamefsbiu').getValue();
                    Ext.getCmp('finstockUploadGridPanel').getStore().load({
                        params: {
                            branchName: branchName
                        }
                    });
                }
            },
            tbar: [{
                    xtype: 'combo',
                    id: 'comboxbranchnamefsbiu',
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
                            var branchName = Ext.getCmp('comboxbranchnamefsbiu').getValue();
                            Ext.getCmp('finstockUploadGridPanel').getStore().load({
                                params: {
                                    branchName: branchName
                                }
                            });
                        }
                    }
                }, {
                    text: 'Upload CSV',
                    tooltip: 'Upload CSV',
                    iconCls: 'dispatch',
                    handler: function () {
                        if (_SESSION.current_branch_iscpd == 1) {
                            var branchName = Ext.getCmp('comboxbranchnamefsbiu').getValue();
                            var branchFullName = Ext.getCmp('comboxbranchnamefsbiu').getRawValue();
//                            if (branchName > 0) {
//                                //importCSV(branchName, branchFullName);
//                            } else {
//                                Ext.Msg.alert('Notification', 'Please choose a branch to import csv.');
//                            }

                        } else {
                            var branchName = _SESSION.finascop_current_branch_id;
                            var branchFullName = _SESSION.current_branch;
                            //importCSV(branchName, branchFullName);
                        }
                        if (branchName > 0) {
                            Ext.Ajax.request({
                                url: modURL + "&op=checkBranchStoreType",
                                method: "POST",
                                params: {
                                    branchName: branchName
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        importCSV(branchName, branchFullName);
                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", "Stock upload is possible only for Third party Retailers.");
                                    } else if (tmp.success === true & tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", "Stock upload is possible only for Third party Retailers.");
                                    } else {
                                        Ext.Msg.alert("Error", "Stock upload is possible only for Third party Retailers.");
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert("Error", "Stock upload is possible only for Third party Retailers.");
                                }
                            });

                        }

                    }
                }, {
                    text: 'Download Sample CSV',
                    tooltip: 'Download demo csv for importing',
                    iconCls: 'icon_download',
                    handler: function () {
                        var url = '/resources/demo/exportcsvstock.csv';
                        if (_SESSION.current_branch_iscpd == 1) {
                            var branchName = Ext.getCmp('comboxbranchnamefsbiu').getValue();
                            var branchFullName = Ext.getCmp('comboxbranchnamefsbiu').getRawValue();
                        } else {
                            var branchName = _SESSION.finascop_current_branch_id;
                            var branchFullName = _SESSION.current_branch;
                        }
//                        if (_SESSION.current_branch_iscpd == 1) {
//                            var branchName = Ext.getCmp('comboxbranchnamefsbiu').getValue();
//                            if (branchName > 0) {
//                                 postToUrl(modURL + '&op=buildStockUploadCsv&branchName=' + branchName);
//                            } else {
//                                Ext.Msg.alert('Notification', 'Please choose a branch to download csv.');
//                            }
//
//                        } else {
//                            postToUrl(modURL + '&op=buildStockUploadCsv&branchName=' + _SESSION.finascop_current_branch_id);
//                        }

                        //window.open(url, '_blank');
                        if (branchName > 0) {
                            Ext.Ajax.request({
                                url: modURL + "&op=checkBranchStoreType",
                                method: "POST",
                                params: {
                                    branchName: branchName
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true && tmp.valid === true) {
                                        postToUrl(modURL + '&op=buildStockUploadCsv&branchName=' + branchName);
                                    } else if (tmp.success === true && tmp.valid === false) {
                                        Ext.Msg.alert("Notification.", "Stock upload is possible only for Third party Retailers.");
                                    } else if (tmp.success === true & tmp.img_valid === false) {
                                        Ext.Msg.alert("Notification.", "Stock upload is possible only for Third party Retailers.");
                                    } else {
                                        Ext.Msg.alert("Error", "Stock upload is possible only for Third party Retailers.");
                                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert("Error", "Stock upload is possible only for Third party Retailers.");
                                }
                            });

                        }
                    }
                }, {
                    html: '&nbsp;BRANCH : &nbsp;',
                }, {
                    xtype: 'textfield',
                    id: 'textboxfsbiuDataeditCS',
                    name: 'textboxfsbiuDataeditCS',
                    anchor: '98%',
                    tabIndex: 1,
                    setReadOnly: true,
                    hidden: true
                }]
        });
        return _fsuGridPanel;
    };
    var importCSV = function (branch, brName) {
        var win_csv = new Ext.Window({
            layout: 'fit',
            width: 400,
            autoHeight: true,
            border: false,
            title: 'Import Stock',
            icon: './resources/images/submenuicons/upload_fl.png',
            //iconCls: 'upload',
            shadow: false,
            floating: true,
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            items: [
                new Ext.form.FormPanel({
                    labelAlign: 'top',
                    labelSeparator: '',
                    bodyStyle: {
                        "background-color": "white",
                        "padding": "5px 5px 5px 10px"
                    },
                    autoHeight: true,
                    id: 'csv_form',
                    frame: false,
                    border: false,
                    fileUpload: true,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .3,
                            items: [{
                                    fieldLabel: 'Upload File (.csv)',
                                    labelAlign: 'top',
                                    xtype: 'fileuploadfield',
                                    accept: '.csv',
                                    id: 'excel_file',
                                    allowBlank: false,
                                    name: 'excel_file',
                                    tabIndex: 1,
                                    msgTarget: 'under',
                                    anchor: '98%',
                                    validator: function (v) {
                                        if (v != '') {
                                            //var exp = /^.*.(.xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                                            var exp = /^.*\.csv$/;
                                            if (!(exp.test(v))) {
                                                return 'Upload a valid CSV file.';
                                            }
                                            return true;
                                        }
                                        var filen = Ext.getCmp('excel_file').getValue();
                                        if (filen == '') {
                                            return 'Upload a file.';
                                        }
                                    }
                                }],
                            buttons: [
                                {
                                    iconCls: 'csv',
                                    text: 'Upload',
                                    handler: function () {
                                        var csv_form = Ext.getCmp('csv_form').getForm();
                                        var t = new Date();
                                        var t_stamp = t.format("YmdHis");
                                        if (csv_form.isValid()) {
                                            csv_form.submit({
                                                url: modURL + '&op=uploadStockcsvFile',
                                                waitTitle: 'Please Wait..',
                                                waitMsg: 'Saving data...',
                                                params: {
                                                    apikey: _SESSION.apikey,
                                                    tstamp: t_stamp,
                                                    branch: branch
                                                },
                                                success: function (csv_form, action) {
                                                    var result = Ext.decode(action.response.responseText);
                                                    console.log('result', result);
                                                    if (result.valid === true && result.success === true) {
                                                        win_csv.close();
                                                        Application.FstockUpload.dispalyUploadedStock(result.fbiu_id, brName);
                                                        Ext.Msg.alert('Notification', result.msg, function (btn) {
                                                            if (btn === 'ok') {
                                                                Ext.Msg.hide();
                                                            }
                                                        });

                                                    } else if (result.valid === false && result.success === true) {
                                                        if (result.error) {
                                                            Ext.Msg.alert('Notification', result.error);
                                                        } else {
                                                            Ext.Msg.alert('Notification', 'Column in CSV doesnot Match');
                                                        }

                                                    } else {
                                                        if (result.error) {
                                                            Ext.Msg.alert('Notification', result.error);
                                                        }
                                                    }
                                                },
                                                failure: function () {
                                                    Ext.Msg.alert('Error', 'Supplied CSV File could not be validated. ', function (btn) {
                                                        if (btn === 'ok') {
                                                            Ext.Msg.hide();
                                                            win_csv.close();
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    }
                                }]
                        }
                    ]
                })
            ]
        });
        win_csv.show();
        win_csv.doLayout();
        win_csv.center();
    };
    var _fsbiudDetailsStore = function (fbiu_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listStockUploadedItems',
                method: 'post'
            }),
            fields: ['fbiu_id', 'stit_sku', 'stit_id', 'branch_id', 'item_count', 'mrp', 'selling_price', 'stit_itemERPId'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.fbiu_id = fbiu_id;
                }
            }
        });
        return _Store;
    };
    var _dispalyUploadedStockDetailsGrid = function (fbiu_id) {

        var _fsbiudDetailsgridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: _fsbiudDetailsStore(fbiu_id),
            iconCls: 'money',
            width: winsize.width * 0.4,
            height: 400,
            bodyStyle: {"background-color": "white"},
            id: 'fsbiudDetailsgridPanelDetails',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'ERP Id',
                    dataIndex: 'stit_itemERPId',
                    sortable: true,
                    tooltip: 'ERP Id',
                    hideable: false
                },
                {
                    header: 'Item Name',
                    dataIndex: 'stit_sku',
                    sortable: true,
                    tooltip: 'Barcode',
                    hideable: false,
                    width: 250
                },
                {
                    header: 'Count',
                    dataIndex: 'item_count',
                    sortable: true,
                    tooltip: 'Details',
                    hideable: false,
                    align: 'right'
                }, {
                    header: 'MRP',
                    dataIndex: 'mrp',
                    sortable: true,
                    tooltip: 'MRP',
                    hideable: false,
                    width: 100,
                    align: 'right'
                },
                {
                    header: 'Selling Price',
                    dataIndex: 'selling_price',
                    sortable: true,
                    tooltip: 'Selling Price',
                    hideable: false,
                    align: 'right'
                }, {}
            ], fbar: [{
                    text: "Save",
                    cls: 'left-right-buttons',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    handler: function () {
                        var branchName;
                        if (_SESSION.current_branch_iscpd == 1) {
                            branchName = Ext.getCmp('comboxbranchnamefsbiu').getValue();
                        } else {
                            branchName = _SESSION.finascop_current_branch_id;
                        }
                        Ext.Ajax.request({
                            url: modURL + "&op=confirmStockUpload",
                            method: "POST",
                            params: {
                                fbiu_id: fbiu_id,
                                branchName: branchName
                            },
                            success: function (response) {
                                var tmp = Ext.decode(response.responseText);
                                if (tmp.success === true && tmp.valid === true) {
                                    Application.example.msg("Success", tmp.msg);
                                    Ext.getCmp("finstockUploadGridPanel").store.load({
                                        params: {
                                            branchName: branchName
                                        }
                                    });
                                    Ext.getCmp('fsibiudWindow').close();
                                } else if (tmp.success === true && tmp.valid === false) {
                                    Ext.Msg.alert("Notification.", tmp.msg);
                                } else if (tmp.success === true & tmp.img_valid === false) {
                                    Ext.Msg.alert("Notification.", tmp.msg);
                                } else {
                                    Ext.Msg.alert("Error", tmp.msg);
                                }
                            },
                            failure: function (response) {
                                var tmp = Ext.util.JSON.decode(response.responseText);
                                Ext.MessageBox.alert("Error", tmp.message);
                            }
                        });

                    }

                }],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('fsbiudDetailsgridPanelDetails').getStore().load({
                        params: {
                            fbiu_id: fbiu_id
                        }
                    });
                }
            }
        });
        return _fsbiudDetailsgridPanel;
    };
    var _viewUploadedStockDetailsGrid = function (fbiu_id) {

        var _fsbiudDetailsgridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: _fsbiudDetailsStore(fbiu_id),
            iconCls: 'money',
            width: winsize.width * 0.4,
            height: 400,
            bodyStyle: {"background-color": "white"},
            id: 'fsbiudDetailsgridPanelDetailsView',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'ERP Id',
                    dataIndex: 'stit_itemERPId',
                    sortable: true,
                    tooltip: 'ERP Id',
                    hideable: false
                },
                {
                    header: 'Item Name',
                    dataIndex: 'stit_sku',
                    sortable: true,
                    tooltip: 'Barcode',
                    hideable: false,
                    width: 250
                },
                {
                    header: 'Count',
                    dataIndex: 'item_count',
                    sortable: true,
                    tooltip: 'Details',
                    hideable: false,
                    align: 'right'
                }, {
                    header: 'MRP',
                    dataIndex: 'mrp',
                    sortable: true,
                    tooltip: 'MRP',
                    hideable: false,
                    width: 100,
                    align: 'right'
                },
                {
                    header: 'Selling Price',
                    dataIndex: 'selling_price',
                    sortable: true,
                    tooltip: 'Selling Price',
                    hideable: false,
                    align: 'right'
                }, {
                    header: '',
                    dataIndex: '',
                    hideable: false
                }
            ], fbar: [],
            viewConfig: {
                forceFit: true
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('fsbiudDetailsgridPanelDetailsView').getStore().load({
                        params: {
                            fbiu_id: fbiu_id
                        }
                    });
                }
            }
        });
        return _fsbiudDetailsgridPanel;
    };
    var manageRRPMainPanel = function (id) {
        var _mrrpPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Manage RRP',
            iconCls: 'dispatch',
            id: id,
            items: [
                manageRRPGrid()
            ]
        });
        return _mrrpPanel;
    };
    var manageRRPGrid = function () {
        var rrpStore = new Ext.data.JsonStore({
            fields: ['rrp_detailId', 'rrp_detailName'],
            url: modURL + '&op=getRrpDetailStore',
            autoLoad: false,
            method: 'post',
        });
        var _rrpGridStore = rrpUploadGridStore();
        var _rrpFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'list',
                    options: ['SKU', 'Brand', 'Itemmaster', 'Subcategory'],
                    phpMode: true,
                    dataIndex: 'rrp_typeName'
                }, {
                    type: 'string',
                    dataIndex: 'rrp_detailName'
                }
            ]
        });
        _rrpFilter.remote = true;
        _rrpFilter.autoReload = true;
        var _mrrpGridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _rrpGridStore,
            id: 'manageRRPGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _rrpFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Type',
                    dataIndex: 'rrp_typeName',
                    sortable: true,
                    tooltip: 'Type',
                    hideable: true,
                    width: 20
                },
                {
                    header: 'Detail',
                    id: 'rrp_detailName_auto_exp',
                    dataIndex: 'rrp_detailName',
                    sortable: true,
                    tooltip: 'Detail',
                    hideable: true,
                    width: 200
                },
                {
                    header: 'RRP Factor',
                    dataIndex: 'rrp_factor',
                    sortable: true,
                    align: 'right',
                    tooltip: 'RRP Factor',
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
                            tooltip: 'Delete Code',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                deleteRRPFactor(record.get('rrp_id'));
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
                store: _rrpGridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionRRPChanged
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
                    id: 'rrp_type',
                    hiddenName: 'rrp_type',
                    name: 'rrp_type',
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
                            var type = Ext.getCmp('rrp_type').getValue();
                            Ext.getCmp('rrp_detail').getStore().load({
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
                    id: 'rrp_detail',
                    name: 'rrp_detail',
                    mode: 'local',
                    emptyText: 'Choose',
                    typeAhead: true,
                    forceSelection: true,
                    fieldLabel: 'Select Detail',
                    editable: true,
                    anchor: '97%',
                    width: 350,
                    store: rrpStore,
                    triggerAction: 'all',
                    minChars: 2,
                    displayField: 'rrp_detailName',
                    valueField: 'rrp_detailId',
                    hiddenName: 'rrp_detail',
                    tabIndex: 101,
                    listeners: {
                        select: function () {

                        }
                    }
                }, {
                    html: '&nbsp;RRP Factor : &nbsp;',
                }, {
                    fieldLabel: 'RRP Factor',
                    xtype: 'numberfield',
                    id: 'rrp_factor',
                    name: 'rrp_factor',
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
                        Application.FstockUpload.checkRRPAvailable();
                    }
                }, '->', {
                    html: '&nbsp;Minimum RRP Factor : &nbsp;',
                },
                {
                    xtype: 'textfield',
                    id: 'textboxMinRrpFactor',
                    name: 'textboxMinRrpFactor',
                    anchor: '98%',
                    tabIndex: 1,
                    maxLength: 20,
                    readOnly: true,
                    value: _SESSION.DEFAULT_RRP
                }]
        });
        return _mrrpGridPanel;
    };
    var rrpUploadGridStore = function () {
        var _manageRRPList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRRPFactor',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'rrp_id',
                root: 'data'
            }, ['rrp_id', 'rrp_type', 'rrp_detail', 'rrp_factor', 'rrp_typeName', 'rrp_detailName']),
            sortInfo: {
                field: 'rrp_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                }
            }
        });
        return _manageRRPList;
    };
    var createRRPWindow = function (medId) {
        var form = rrpForm(medId);
        var retalineRRP_window = Ext.getCmp('retalineRRP_window');
        if (Ext.isEmpty(retalineRRP_window)) {
            retalineRRP_window = new Ext.Window({
                id: 'retalineRRP_window',
                title: 'Add RRP',
                layout: 'fit',
                width: winsize.width * .2,
                autoHeight: true,
                plain: true,
                modal: true,
                constrainHeader: true,
                keepTop: true,
                align: 'center',
                frame: true,
                resizable: false,
                height: 150,
                items: [form],
                shadow: false,
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        tabindex: 6,
                        handler: function () {
                            Ext.getCmp('retalineRRP_window').close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        tabindex: 5,
                        handler: function () {
                            Application.FstockUpload.retalineRRPSave(medId);
                        }
                    }]
            });
        }

        retalineRRP_window.doLayout();
        retalineRRP_window.show(this);
        retalineRRP_window.center();
        //retalineRRP_window.alignTo(Ext.getBody(), "tr-tr", [-480, 10]);
    };
    var rrpDetailStore = function () {
        var rrpStore = new Ext.data.JsonStore({
            fields: ['rrp_detailId', 'rrp_detailName'],
            url: modURL + '&op=getRrpDetailStore',
            autoLoad: false,
            method: 'post',
        });
        return rrpStore;
    };
    var rrpForm = function () {
        var rrpComboStore = rrpDetailStore();
        var form = new Ext.FormPanel({
            layout: 'form',
            id: 'rrpFormPanel',
            height: 150,
            columnWidth: 1,
            frame: true,
            border: true,
            items: [{
                    layout: 'form',
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            id: 'rrp_id',
                            name: 'rrp_id'
                        }, {
                            fieldLabel: 'Select Type',
                            xtype: 'combo',
                            displayField: 'typeName',
                            valueField: 'typeId',
                            mode: 'local',
                            id: 'rrp_type',
                            hiddenName: 'rrp_type',
                            name: 'rrp_type',
                            typeAhead: true,
                            minChars: 1,
                            triggerAction: 'all',
                            selectOnFocus: true,
                            lazyRender: true,
                            anchor: '97%',
                            allowBlank: false,
                            store: new Ext.data.JsonStore({
                                fields: ['typeId', 'typeName'],
                                data: [{typeId: '1', typeName: 'SKU'}, {typeId: '2', typeName: 'Brand'}, {typeId: '3', typeName: 'Item Master'}, {typeId: '4', typeName: 'Subcategory'}]
                            }),
                            editable: true,
                            msgTarget: 'under',
                            tabIndex: 1,
                            listeners: {
                                select: function () {
                                    var type = Ext.getCmp('rrp_type').getValue();
                                    Ext.getCmp('rrp_detail').getStore().load({
                                        params: {
                                            type: type
                                        }
                                    });
                                }
                            }
                        }, {
                            xtype: 'combo',
                            id: 'rrp_detail',
                            name: 'rrp_detail',
                            mode: 'local',
                            emptyText: 'Choose',
                            typeAhead: true,
                            forceSelection: true,
                            fieldLabel: 'Select Detail',
                            editable: true,
                            anchor: '97%',
                            store: rrpComboStore,
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'rrp_detailName',
                            valueField: 'rrp_detailId',
                            hiddenName: 'rrp_detail',
                            allowBlank: false,
                            listeners: {
                                select: function () {

                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    labelAlign: 'left',
                    labelWidth: 65,
                    items: [{
                            fieldLabel: 'RRP Factor',
                            xtype: 'numberfield',
                            id: 'rrp_factor',
                            name: 'rrp_factor',
                            tabIndex: 93,
                            anchor: '97%',
                            allowBlank: false
                        }]
                }]
        });
        return form;
    };
    var deleteRRPFactor = function (id) {
        Ext.MessageBox.confirm('Confirm', 'Do you want to remove this item?', function (btn, text) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    method: 'POST',
                    url: modURL + '&op=deleteRRPFactor',
                    params: {
                        rrp_id: id
                    },
                    success: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', 'Removed item');
                            Ext.getCmp('manageRRPGridPanel').getStore().load();
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
    return {
        Cache: {},
        initFinStockUploadList: function () {
            var _finascopStockUploadPanelId = 'finascopStockUpload';
            var __finascopStockUploadPanelIdPanel = Ext.getCmp(_finascopStockUploadPanelId);
            if (Ext.isEmpty(__finascopStockUploadPanelIdPanel)) {
                __finascopStockUploadPanelIdPanel = finascopStockUploadMainPanel(_finascopStockUploadPanelId);
                Application.UI.addTab(__finascopStockUploadPanelIdPanel);
                __finascopStockUploadPanelIdPanel.doLayout();
            } else {
                Application.UI.addTab(__finascopStockUploadPanelIdPanel);
            }
        }, dispalyUploadedStock: function (fbiu_id, brName) {
            var _addnewItemsWindow = new Ext.Window({
                title: 'Current Stock Upload - ' + brName,
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: winsize.width * 0.45,
                id: 'fsibiudWindow',
                resizable: false, draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_dispalyUploadedStockDetailsGrid(fbiu_id)]
            });
            _addnewItemsWindow.doLayout();
            _addnewItemsWindow.show();
            _addnewItemsWindow.center();
        }, viewUploadedStock: function (fbiu_id, brName) {
            var _addnewItemsWindow = new Ext.Window({
                title: 'Current Stock Upload - ' + brName,
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: winsize.width * 0.45,
                id: 'fsibiudWindow',
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_viewUploadedStockDetailsGrid(fbiu_id)]
            });
            _addnewItemsWindow.doLayout();
            _addnewItemsWindow.show();
            _addnewItemsWindow.center();
        }, initManageRRP: function () {
            var _manageRRPPanelId = 'manageRRP';
            var ___manageRRPPanelIdPanelIdPanel = Ext.getCmp(_manageRRPPanelId);
            if (Ext.isEmpty(___manageRRPPanelIdPanelIdPanel)) {
                ___manageRRPPanelIdPanelIdPanel = manageRRPMainPanel(_manageRRPPanelId);
                Application.UI.addTab(___manageRRPPanelIdPanelIdPanel);
                ___manageRRPPanelIdPanelIdPanel.doLayout();
            } else {
                Application.UI.addTab(___manageRRPPanelIdPanelIdPanel);
            }
        }, retalineRRPSave: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var prescriptionForm = Ext.getCmp('rrpFormPanel').getForm();

            var rrp_type = Ext.getCmp('rrp_type').getValue();
            var rrp_detail = Ext.getCmp('rrp_detail').getValue();
            var rrp_factor = Ext.getCmp('rrp_factor').getValue();

            if ((rrp_type > 0) && (rrp_detail > 0) && (rrp_factor > 0)) {
                if (rrp_factor > _SESSION.DEFAULT_RRP) {
                    Ext.Ajax.request({
                        url: modURL + '&op=saveRRPData',
                        params: {rrp_type: rrp_type, rrp_detail: rrp_detail, rrp_factor: rrp_factor},
                        failure: function (response) {
                            Ext.MessageBox.alert('Error', response.responseText);

                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.example.msg('Success', tmp.message);
                                Ext.getCmp('manageRRPGridPanel').getStore().load();
                                Ext.getCmp('rrp_type').reset();
                                Ext.getCmp('rrp_detail').reset();
                                Ext.getCmp('rrp_factor').reset();
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
                    Ext.Msg.alert('Notification', 'RRP Factor should be greater than minmum value.');
                }


            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        },
        checkRRPAvailable: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            //var prescriptionForm = Ext.getCmp('rrpFormPanel').getForm();

            var rrp_type = Ext.getCmp('rrp_type').getValue();
            var rrp_detail = Ext.getCmp('rrp_detail').getValue();
            var rrp_factor = Ext.getCmp('rrp_factor').getValue();

            if ((rrp_type > 0) && (rrp_detail > 0) && (rrp_factor > 0)) {
                if (rrp_factor > _SESSION.DEFAULT_RRP) {
                    Ext.Ajax.request({
                        url: modURL + '&op=checkRRPData',
                        params: {rrp_type: rrp_type, rrp_detail: rrp_detail, rrp_factor: rrp_factor},
                        failure: function (response) {
                            Ext.MessageBox.alert('Error', response.responseText);

                        },
                        success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            console.log('tmp', tmp);
                            if (tmp.success === true && tmp.valid === true) {
                                Application.FstockUpload.retalineRRPSave();
                            } else if (tmp.success === true && tmp.valid === false) {
                                Ext.MessageBox.confirm('Confirm', tmp.message, function (btn) {
                                    if (btn == 'yes') {
                                        Application.FstockUpload.retalineRRPSave();
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
                    Ext.Msg.alert('Notification', 'RRP Factor should be greater than minmum value.');
                }


            } else {
                Ext.Msg.alert('Notification', 'Please enter all required fields');
            }
        }
    }
}();