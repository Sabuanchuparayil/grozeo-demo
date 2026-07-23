/* global Ext, Application, IMAGE_BASE_PATH, _SESSION, t_stamp, mandatory_label, FINASCOP_CURRENCY_FORMAT */
Application.RetalineSales = function () {
//GLOBAL DECLRATIONS BEGIN
    {
        var winsize = Ext.getBody().getViewSize();
        var modURL = '?module=retaline_sales';
        var recs_per_page = 23;
        var myMask, totalComboCount;
        var branchStockUpdateIntervalID;
        var my_marker;

    }
//GLOBAL DECLARATIONS END

//Common Functions BEGIN
    {
        function updatePagination(cmp) {
            recs_per_page = finascop_update_recs_per_page(cmp);
        }
        ;

        var cronStockQtyUpdator = function () {
            //it  will be called once the SaleOrder window is opened and item is added.
            branchStockUpdateIntervalID = setInterval(function () {
                alert("Interval reached");
            }, 30000);
            //clearInterval(branchStockUpdateIntervalID);

        };

        var b2bSalesOrderColModel = function () {
            return new Ext.grid.ColumnModel([
                {
                    header: 'Item',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bso_itemname',
                    tooltip: 'Item',
                    width: 150

                },
                {
                    header: 'MRP',
                    sortable: true,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'b2bso_itemmrp',
                    align: 'right',
                    tooltip: 'MRP',
                    width: 30
                },
                {
                    header: 'Rate',
                    sortable: true,
                    dataIndex: 'b2bso_itemrate',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    tooltip: 'Rate',
                    align: 'right',
                    width: 30
                },
                {
                    header: 'Qty',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bso_itemqty',
                    tooltip: 'Qty',
                    align: 'right',
                    width: 25
                },
                {
                    header: 'GST %',
                    sortable: true,
                    hideable: false,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'b2bso_gst',
                    tooltip: 'GST Tax %',
                    align: 'right',
                    width: 25
                },
                {
                    header: 'Package Type',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bso_itemPkg',
                    tooltip: 'Package Type',
                    align: 'right',
                    width: 25
                },
                {
                    header: 'Amount',
                    sortable: true,
                    hideable: false,
                    xtype: 'finascopcurrency',
                    dataIndex: 'b2bso_amount',
                    format: FINASCOP_CURRENCY_FORMAT,
                    tooltip: 'Amount',
                    align: 'right',
                    width: 45
                },
                {
                    header: 'Discount Percentage',
                    hidden: true,
                    hideable: false,
                    xtype: 'numbercolumn',
                    dataIndex: 'b2bso_discountpercent',
                    tooltip: 'Discount',
                    align: 'right',
                    width: 30
                },
                {
                    header: 'Discount',
                    sortable: true,
                    hideable: false,
                    xtype: 'finascopcurrency',
                    dataIndex: 'b2bso_discountamt',
                    format: FINASCOP_CURRENCY_FORMAT,
                    tooltip: 'Discount',
                    align: 'right',
                    width: 30
                },
                {
                    header: 'Net Amt',
                    sortable: true,
                    dataIndex: 'b2bso_netamount',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Net Amt',
                    width: 45
                },
                {
                    xtype: 'actioncolumn',
                    hideable: false,
                    width: 16,
                    items: [{
                            tooltip: 'Remove Item',
                            getClass: function (v, meta, rec) {
                                var bbso_id = rec.get('bbso_id');
                                var bbdc_id = rec.get('bbdc_id');
                                if (bbso_id <= 0 && bbdc_id <= 0) {
                                    return 'finascop_delete';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.Msg.confirm('Notification', 'Do you really want to remove this item?', function (btn) {
                                    if (btn == 'yes') {
                                        removeFromItemStore(grid, rowIndex, record);
                                    }
                                });
                            }
                        },
                        {
                            tooltip: 'Edit Item',
                            getClass: function (v, meta, rec) {
                                var bbso_id = rec.get('bbso_id');
                                var customerID = Application.RetalineSales.Cache.CustomerID;
                                if (customerID > 0 && bbso_id <= 0) {
                                    return 'rate-settings';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                grid.getView().getRow(rowIndex).style.fontWeight = 'bold';
                                editAndUupdateRecord(record, grid, rowIndex);
                            }
                        }]
                },
                {
                    header: '',
                    sortable: false,
                    hideable: false,
                    width: 8,
                    dataIndex: ''
                }
            ]);
        };

        var newPOItemGrid = function () {

            var b2bso_ItemStore = function () {
                var itemStore = new Ext.data.JsonStore({
                    method: 'post',
                    proxy: new Ext.data.HttpProxy({
                        url: modURL + '&op=listB2BSOItems',
                        method: 'post'
                    }),
                    fields: ['bbso_id', 'bbdc_id', 'b2bso_itemid', 'b2bso_itemname', 'b2bso_itemmrp', 'b2bso_itemqty', 'b2bso_itemrate',
                        'b2bso_gst', 'b2bso_itemPkg', {name: 'isStockEnough', type: 'int'},
                        {name: 'b2bso_amount', type: 'float'}, 'b2bso_discountpercent', {name: 'b2bso_discountamt', type: 'float'},
                        {name: 'b2bso_netamount', type: 'float'}],
                    totalProperty: 'totalCount',
                    root: 'data',
                    remoteSort: true,
                    autoLoad: false,
                    listeners: {
                        beforeload: function (store, e) {
                            this.baseParams.b2bso_UniqueID = Application.RetalineSales.Cache.b2bso_UniqueID;
                        },
                        load: function (store, records, options) {
                            var handlingCharges = 0 + Ext.getCmp('b2bso_SOHandlingCharges').getValue();
                            var b2bso_totalnetamount = store.sum('b2bso_netamount') + parseFloat(handlingCharges);
                            Ext.getCmp('b2bso_SONetTotal').setValue(b2bso_totalnetamount);
                        }
                    }

                });
                return itemStore;
            };
            var itemGridStore = b2bso_ItemStore();
            var itemgrid_panel = new Ext.grid.GridPanel({
                store: itemGridStore,
                frame: false,
                border: false,
                title: '',
                height: 200,
                id: 'b2bso_ItemGrid',
                loadMask: true,
                sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true
                }),
                cm: b2bSalesOrderColModel(),
                tbar: [],
                viewConfig: {
                    forceFit: true,
                    getRowClass: function (record, index) {

                        if (record.get('isStockEnough') == 0)
                        {
                            return 'finascop_indicateColPINK ';
                        } else {
                            return '';
                        }
                    },
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

                },
                stripeRows: true
            });
            return itemgrid_panel;
        };


    }
//Common Functions END

//B2B Customer BEGIN
    {
        var createB2BCustomerGrid = function (id) {
            var retalineCustomer_jsonstore = salesCustomerJsonStore();
            var retalineCustomer_filter = new Ext.ux.grid.GridFilters({
                filters: [{
                        type: 'string',
                        dataIndex: 'b2b_Customer_Name'
                    }, {
                        type: 'string',
                        dataIndex: 'branch_shortname'
                    }, {
                        type: 'string',
                        dataIndex: 'b2b_Customer_District'
                    }, {
                        type: 'string',
                        dataIndex: 'b2b_Customer_State'
                    }, {
                        type: 'string',
                        dataIndex: 'b2b_Customer_Mobile'
                    }, {
                        type: 'string',
                        dataIndex: 'b2b_Customer_Email'
                    }, {
                        type: 'string',
                        dataIndex: 'b2b_Customer_Phone'
                    }, {
                        type: 'string',
                        dataIndex: 'b2b_Customer_Incharge'
                    }, {
                        type: 'string',
                        dataIndex: 'company'
                    }]
            });
            retalineCustomer_filter.remote = true;
            retalineCustomer_filter.autoReload = true;

            var grid_panel = new Ext.grid.GridPanel({
                store: retalineCustomer_jsonstore,
                layout: 'fit',
                frame: false,
                border: false,
                title: 'Wholesale Customers',
                //iconCls: 'b2bicon24x24',
                plugins: [retalineCustomer_filter],
                id: id,
                loadMask: true,
                columns: [new Ext.grid.RowNumberer(),
                    {
                        header: 'Name',
                        id: 'centralStore_name',
                        sortable: true,
                        hideable: true,
                        dataIndex: 'b2b_Customer_Name',
                        tooltip: 'CentralStore Name'
                    },
                    {
                        header: 'Contact Person',
                        sortable: true,
                        hideable: true,
                        dataIndex: 'b2b_Customer_Incharge',
                        tooltip: 'Contact Person'
                    },
                    {
                        header: 'Email',
                        sortable: true,
                        hideable: true,
                        dataIndex: 'b2b_Customer_Email',
                        tooltip: 'Email'
                    },
                    {
                        header: 'Contact No.',
                        sortable: true,
                        hideable: true,
                        dataIndex: 'b2b_Customer_Phone',
                        tooltip: 'Contact No.'
                    },
                    {
                        header: 'Mobile',
                        sortable: true,
                        hideable: true,
                        dataIndex: 'b2b_Customer_Mobile',
                        tooltip: 'Mobile'
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
                            b2bcustomerActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                },
                    /*{
                        xtype: 'actioncolumn',
                        header: 'Action',
                        hideable: false,
                        sortable: false,
                        groupable: false,
                        tooltip: 'Action',
                        items: [
                            {
                                iconCls: 'edit',
                                tooltip: 'Edit Details',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    var map_Lat = record.get('b2b_Customer_Lat');
                                    var map_Long = record.get('b2b_Customer_Lng');
                                    salesCustomerDetails(record.get('b2b_Customer_ID'), map_Lat, map_Long);
                                }
                            },
                            {
                                iconCls: 'dc_16x16',
                                tooltip: 'Apply B2B Sales Scheme.',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    applyB2BSalesScheme(record.get('b2b_Customer_ID'), record.get('rbsch_name'), grid_panel);
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
                    viewready: updatePagination
                },
                tbar: [{
                        xtype: 'button',
                        text: 'Create Wholesale Customer',
                        tooltip: 'Create Wholesale Customer',
                        iconCls: 'add',
                        handler: function () {
                            salesCustomerDetails(0, 8.507007481504532, 76.95167541503906);
                        }
                    }],
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: retalineCustomer_jsonstore,
                    displayInfo: true,
                    displayMsg: 'Displaying records {0} - {1} of {2}',
                    emptyMsg: "No records to display",
                    plugins: [retalineCustomer_filter]
                }),
                stripeRows: true,
                autoExpandColumn: 'centralStore_name'
            });
            return grid_panel;
        };
        var b2bcustomerActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {
                    var b2b_Customer_ID = Ext.getCmp('gridpanelSalesB2BCustomer').getSelectionModel().getSelections()[0].data.b2b_Customer_ID;
                    var b2b_Customer_Lat = Ext.getCmp('gridpanelSalesB2BCustomer').getSelectionModel().getSelections()[0].data.b2b_Customer_Lat;
                    var b2b_Customer_Lng = Ext.getCmp('gridpanelSalesB2BCustomer').getSelectionModel().getSelections()[0].data.b2b_Customer_Lng;
                    salesCustomerDetails(b2b_Customer_ID, b2b_Customer_Lat, b2b_Customer_Lng);
                }
            }, {
                text: "Apply B2B Sales Scheme",
                handler: function () {
                    var b2b_Customer_ID = Ext.getCmp('gridpanelSalesB2BCustomer').getSelectionModel().getSelections()[0].data.b2b_Customer_ID;
                    var rbsch_name = Ext.getCmp('gridpanelSalesB2BCustomer').getSelectionModel().getSelections()[0].data.rbsch_name;
                    var grid_panel = Ext.getCmp('gridpanelSalesB2BCustomer');
                    applyB2BSalesScheme(b2b_Customer_ID, rbsch_name, grid_panel);
                }
            }]
    });
        var salesCustomerJsonStore = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listB2BCustomers',
                    method: 'post'
                }),
                fields: ['b2b_Customer_ID', 'b2b_Customer_Name', 'b2b_Customer_District', 'b2b_Customer_State',
                    'b2b_Customer_Address', 'b2b_Customer_Mobile', 'b2b_Customer_Email', 'b2b_Customer_Phone',
                    'b2b_Customer_Incharge', 'b2b_Customer_status', 'company', 'branch_shortname', 'comp_id',
                    'b2b_Customer_pincode', 'b2b_Customer_Lat', 'b2b_Customer_Lng', 'rbsch_name'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false
            });
            store.setDefaultSort('b2b_Customer_Name', 'ASC');
            return store;
        };

        var applyB2BSalesScheme = function (b2b_Customer_ID, rbsch_name, grid_panel) {
            var createB2BSalesSchemeForm = function () {
                var b2bSaleSchemesStore = new Ext.data.JsonStore({
                    url: modURL + '&op=getSalesSchemes',
                    method: 'post',
                    totalProperty: 'totalCount',
                    root: 'data',
                    fields: ['rbsch_name'],
                    remoteSort: true,
                    autoLoad: true
                });
                var ssfrm = new Ext.FormPanel({
                    frame: true,
                    monitorValid: true,
                    id: 'salesSchemeform',
                    labelAlign: 'top',
                    autoHeight: true,
                    items: [
                        {
                            xtype: 'combo',
                            fieldLabel: 'B2B Sales Schemes',
                            id: 'b2bSslesSchemes',
                            name: 'b2bSslesSchemes',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            labelAlign: 'top',
                            mode: 'remote',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            value: rbsch_name,
                            store: b2bSaleSchemesStore,
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'rbsch_name',
                            valueField: 'rbsch_name',
                            hiddenName: 'b2bSslesSchemes',
                            tabIndex: 1,
                            listeners: {
                            }
                        }

                    ]

                });
                return ssfrm;

            };

            var B2BSalesSchemeForm = createB2BSalesSchemeForm();
            var b2bSalesSchemeWin = new Ext.Window({
                id: "b2bSalesSchemeWinID",
                title: 'Apply B2B Sales Schemes',
                shadow: false,
                width: 400,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [B2BSalesSchemeForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 523,
                        handler: function () {
                            Ext.getCmp('b2bSalesSchemeWinID').close();
                        }
                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Submit',
                        id: 'b2bssSubmitBtn',
                        tabIndex: 522,
                        handler: function () {
                            if (B2BSalesSchemeForm.getForm().isValid()) {
                                if (b2b_Customer_ID > 0) {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=setB2BSchemeToCustomer',
                                        method: 'POST',
                                        params: {
                                            b2b_Customer_ID: b2b_Customer_ID,
                                            rbsch_name: Ext.getCmp('b2bSslesSchemes').getValue()
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success == true) {
                                                Application.example.msg('Success', tmp.msg);
                                                grid_panel.getStore().reload();
                                                Ext.getCmp('b2bSalesSchemeWinID').close();
                                            } else if (tmp.success == false) {
                                                Ext.Msg.alert("Notification.", tmp.msg);
                                            } else {
                                                Ext.Msg.alert("Error", tmp);
                                            }
                                        },
                                        failure: function (response) {
                                            Ext.MessageBox.alert('Error', response.responseText);
                                        }
                                    });
                                }
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please select Sales Scheme to apply to this customer.');
                            }
                        }
                    }]
            });

            b2bSalesSchemeWin.doLayout();
            b2bSalesSchemeWin.show();
            b2bSalesSchemeWin.center();
        };

        var salesCustomerDetails = function (b2b_Customer_ID) {


            var b2b_Customer_ID = arguments[0];
            var map_lat = arguments[1];
            var map_long = arguments[2];
            var win_id = "retalinecustomer_details_window";

            var retalinecustomer_details_window = Ext.getCmp(win_id);
            if (Ext.isEmpty(retalinecustomer_details_window)) {
                var retalineCustomer_form = retalineCustomerForm(map_lat, map_long);
                retalinecustomer_details_window = new Ext.Window({
                    id: win_id,
                    title: 'Create Wholesale Customer',
                    layout: 'fit',
                    autoWidth: true,
                    autoHeight: true,
                    autoScroll: true,
                    plain: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: retalineCustomer_form,
                    bbar: new Ext.Toolbar({
                        height: 29,
                        buttonAlign: 'right',
                        ctCls: 'x-box-layout-ct custom-class',
                        width: 850,
                        items: [
                            {
                                border: true,
                                width: 70,
                                height: 25,
                                xtype: 'button',
                                text: 'Cancel',
                                icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    retalinecustomer_details_window.close();
                                }
                            },
                            {
                                border: true,
                                width: 60,
                                height: 25,
                                xtype: 'button',
                                text: 'Save',
                                icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    if (retalineCustomer_form.getForm().isValid()) {
                                        var t = new Date();
                                        var t_stamp = t.format("YmdHis");
                                        retalineCustomer_form.getForm().submit({
                                            url: modURL + '&op=saveB2BCustomer',
                                            waitMsg: 'Saving Details....',
                                            waitTitle: 'Please Wait...',
                                            params: {
                                                apikey: _SESSION.apikey,
                                                tstamp: t_stamp
                                            },
                                            success: function (response, action) {
                                                var tmp = Ext.decode(action.response.responseText);
                                                if (tmp.success == true) {
                                                    Application.example.msg('Success', 'B2B Customer Details Saved.');
                                                    Ext.getCmp('retalinecustomer_details_window').close();
                                                    Ext.getCmp('gridpanelSalesB2BCustomer').getStore().reload();
                                                } else if (tmp.success == false) {
                                                    Ext.Msg.alert("Notification.", tmp.msg);
                                                } else {
                                                    Ext.Msg.alert("Error", tmp);
                                                }
                                            },
                                            failure: function (form, action) {
                                                switch (action.failureType) {
                                                    case Ext.form.Action.CLIENT_INVALID:
                                                        Ext.Msg.alert('Failure', 'Form fields may be submitted with invalid values',
                                                                function (btn, text) {
                                                                    if (btn == 'ok') {
                                                                        retalinecustomer_details_window.close();
                                                                    }
                                                                });
                                                        break;
                                                    case Ext.form.Action.CONNECT_FAILURE:
                                                        Ext.Msg.alert('Failure', 'Ajax communication failed',
                                                                function (btn, text) {
                                                                    if (btn == 'ok') {
                                                                        retalinecustomer_details_window.close();
                                                                    }
                                                                });
                                                        break;
                                                    case Ext.form.Action.SERVER_INVALID:
                                                        Ext.Msg.alert('Failure', action.response.responseText,
                                                                function (btn, text) {
                                                                    if (btn == 'ok') {
                                                                        //retalinecustomer_details_window.close();
                                                                    }
                                                                });
                                                }

                                            }
                                        });
                                    }
                                }
                            }]
                    }),
                    listeners: {
                        afterrender: function () {

                            if (!Ext.isEmpty(b2b_Customer_ID) && b2b_Customer_ID > 0) {
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");

                                myMask = new Ext.LoadMask(Ext.getCmp('retalinecustomer_details_window').getEl(), {msg: "Please wait..."});
                                myMask.show();

                                retalineCustomer_form.load({
                                    url: modURL + '&op=getB2BClientDetails',
                                    params: {
                                        'id': b2b_Customer_ID,
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp
                                    },
                                    success: function (frm, action) {

                                        eval('var tmp=' + action.response.responseText);

                                        totalComboCount = 4;
                                        retalinecustomer_details_window.setTitle("Edit Wholesale Customer : " + Ext.getCmp("b2b_Customer_Name").getValue());
                                        frm.loadRecord(Ext.decode(action.response.responseText));
                                        myMask.hide();
                                    },
                                    failure: function (form, action) {
                                        switch (action.failureType) {
                                            case Ext.form.Action.CLIENT_INVALID:
                                                Ext.Msg.alert('Failure', 'Form fields may be submitted with invalid values',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                retalinecustomer_details_window.close();
                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.CONNECT_FAILURE:
                                                Ext.Msg.alert('Failure', 'Ajax communication failed',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                retalinecustomer_details_window.close();
                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.LOAD_FAILURE:
                                                Ext.Msg.alert('Failure', 'success:false, or no field values are returned.',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                retalinecustomer_details_window.close();
                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.SERVER_INVALID:
                                                Ext.Msg.alert('Failure', action.response.responseText,
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
                                                                retalinecustomer_details_window.close();
                                                            }
                                                        });
                                        }
                                        myMask.hide();
                                    }
                                });
                            }
                        }
                    }
                });
            }

            retalinecustomer_details_window.doLayout();
            retalinecustomer_details_window.show(this);
            retalinecustomer_details_window.center();
        };
        var retalineCustomerForm = function (map_lat, map_long) {

            my_marker = [{
                    lat: map_lat,
                    lng: map_long,
                    marker: {
                        title: "you are here",
                        draggable: false
                    },
                    listeners: {
                        "onFailure": function () {
                            Ext.MessageBox.alert('Failed locating city ');
                        },
                        "onSuccess": function (point) {

                        },
                        "dragend": function (markerAt) {
                            Ext.getCmp('b2b_Customer_Lat').setValue(markerAt.latLng.lat());
                            Ext.getCmp('b2b_Customer_Lng').setValue(markerAt.latLng.lng());
                        }
                    }
                }];

            var form = new Ext.FormPanel({
                labelAlign: 'top',
                autoHeight: true,
                width: 850,
                frame: true,
                id: 'centralstore_form',
                layout: 'column',
                items: [{
                        layout: 'form',
                        columnWidth: 0.45,
                        hideBorders: true,
                        defaults: {
                            xtype: 'textfield',
                            anchor: '95%',
//                        allowBlank: false,
                            //style: 'margin:10px 0 10px 0',
                            hideBorders: true
                        },
                        items: [{
                                xtype: 'hidden',
                                allowBlank: true,
                                id: 'b2b_Customer_ID',
                                name: 'b2b_Customer_ID'
                            }, {
                                xtype: 'panel',
                                layout: 'column',
                                items: [{
                                        columnWidth: 1,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        items: [{
                                                fieldLabel: 'B2B Client Name',
                                                xtype: 'textfield',
                                                anchor: '99%',
                                                id: 'b2b_Customer_Name',
                                                allowBlank: false,
                                                name: 'b2b_Customer_Name',
                                                maxLength: 250,
                                                tabIndex: 200,
                                                listeners: {
                                                    afterrender: function (field) {
                                                        Ext.defer(function () {
                                                            //field.focus(true, 100);
                                                        }, 1);
                                                    }
                                                }
                                            }]
                                    }]
                            },
                            {
                                fieldLabel: 'Address',
                                id: 'b2b_Customer_Address',
                                name: 'b2b_Customer_Address',
                                allowBlank: false,
                                maxLength: 765,
                                height: 90,
                                xtype: 'textarea',
                                tabIndex: 201
                            }, {
                                xtype: 'panel',
                                layout: 'column',
                                items: [
                                    {
                                        columnWidth: .5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        hideBorders: true,
                                        items: [{
                                                fieldLabel: 'Post Code',
                                                id: 'b2b_Customer_pincode',
                                                anchor: '98%',
                                                xtype: 'textfield',
                                                name: 'b2b_Customer_pincode',
                                                vtype: 'phonespec',
                                                allowBlank: false,
                                                tabIndex: 202,
                                                listeners: {
                                                    change: function () {
                                                        if (!Ext.isEmpty(Ext.getCmp('b2b_Customer_pincode').getValue())) {
                                                            Ext.getCmp('map_button1').enable();
                                                        }
                                                    }
                                                }
                                            }]
                                    }, {
                                        columnWidth: .5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        hideBorders: true,
                                        items: [{
                                                fieldLabel: 'GST / VAT',
                                                id: 'b2b_Customer_gst',
                                                anchor: '98%',
                                                xtype: 'textfield',
                                                name: 'b2b_Customer_gst',
                                                tabIndex: 203
                                            }]
                                    }]
                            },
                            {
                                xtype: 'panel',
                                layout: 'column',
                                items: [{
                                        columnWidth: 0.5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        items: [{
                                                id: 'b2b_Customer_Incharge',
                                                xtype: 'textfield',
                                                anchor: '99%',
                                                fieldLabel: 'Contact Person',
                                                maxLength: 250,
                                                name: 'b2b_Customer_Incharge',
                                                allowBlank: false,
                                                tabIndex: 204
                                            }]
                                    }, {
                                        columnWidth: 0.5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        hideBorders: true,
                                        items: [{
                                                fieldLabel: 'Telephone',
                                                xtype: 'numberfield',
                                                anchor: '99%',
                                                id: 'b2b_Customer_Phone',
                                                name: 'b2b_Customer_Phone',
                                                style: 'text-align:right',
                                                minLength: 10,
                                                maxLength: 10,
                                                msgTarget: 'under',
                                                minLengthText: 'The minimum length for this field is 10',
                                                maxLengthText: 'The maximum length for this field is 10',
                                                allowBlank: false,
                                                //vtype: 'phone',
                                                tabIndex: 205
                                            }]
                                    }]
                            },
                            {
                                xtype: 'panel',
                                layout: 'column',
                                items: [{
                                        columnWidth: 0.5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        items: [{
                                                fieldLabel: 'Email',
                                                xtype: 'textfield',
                                                anchor: '99%',
                                                id: 'b2b_Customer_Email',
                                                name: 'b2b_Customer_Email',
                                                allowBlank: false,
                                                regex: /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/,
                                                regexText: 'Invalid Email',
                                                msgTarget: 'under',
                                                vtype: 'email',
                                                tabIndex: 206
                                            }],
                                    }, {
                                        columnWidth: 0.5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        hideBorders: true,
                                        items: [{
                                                fieldLabel: 'Mobile',
                                                xtype: 'numberfield',
                                                style: 'text-align:right',
                                                anchor: '99%',
                                                id: 'b2b_Customer_Mobile',
                                                name: 'b2b_Customer_Mobile',
                                                allowBlank: false,
                                                //vtype: 'phone',
                                                minLength: 10,
                                                maxLength: 10,
                                                msgTarget: 'under',
                                                minLengthText: 'The minimum length for this field is 10',
                                                maxLengthText: 'The maximum length for this field is 10',
                                                tabIndex: 207
                                            }]
                                    }]
                            },
                            {
                                xtype: 'panel',
                                layout: 'column',
                                items: [{
                                        columnWidth: 0.5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        items: [{
                                                fieldLabel: 'Reg Number 1',
                                                xtype: 'textfield',
                                                anchor: '99%',
                                                id: 'b2b_Customer_dlno1',
                                                name: 'b2b_Customer_dlno1',
                                                allowBlank: false,
                                                tabIndex: 208
                                            }],
                                    }, {
                                        columnWidth: 0.5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        hideBorders: true,
                                        items: [{
                                                fieldLabel: 'Reg Number 2',
                                                xtype: 'textfield',
                                                anchor: '99%',
                                                id: 'b2b_Customer_dlno2',
                                                name: 'b2b_Customer_dlno2',
                                                allowBlank: false,
                                                tabIndex: 209
                                            }]
                                    }]
                            }, {
                                xtype: 'panel',
                                layout: 'column',
                                items: [{
                                        columnWidth: 0.5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        items: [{
                                                fieldLabel: 'FSSAI / EORI No.',
                                                xtype: 'textfield',
                                                anchor: '99%',
                                                id: 'b2b_Customer_fssaino',
                                                name: 'b2b_Customer_fssaino',
                                                allowBlank: false,
                                                tabIndex: 210
                                            }]
                                    }]
                            }
                        ]
                    },
                    {
                        layout: 'form',
                        columnWidth: 0.55,
                        title: 'MAP',
                        region: 'center',
                        items: [{
                                xtype: 'gmappanel',
                                gmapType: 'map',
                                id: 'branchgooglemap',
                                zoomLevel: 8,
                                height: 380,
                                minGeoAccuracy: 4,
                                scaleControl: true,
                                mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
                                mapControls: ['GSmallMapControl', 'GMapTypeControl'],
                                setCenter: {
                                    lat: map_lat,
                                    lng: map_long
                                },
                                repaint: function(zoomlevel){
                                    var gmappanel = Ext.getCmp('branchgooglemap');
                                    if(zoomlevel){
                                       gmappanel.zoomLevel = zoomlevel;
                                       gmappanel.getMap().setZoom(zoomlevel); 
                                    }
                                    gmappanel.onMapReady();        

                                },
                                markers: my_marker
                            },
                            {
                                layout: 'form',
                                columnWidth: .5,
                                //style: 'margin-left:5px;',
                                items: [{
                                        xtype: 'fieldset',
                                        title: 'Map Coordinates',
                                        //height: 200,
                                        autoHeight: true,
                                        items: [{
                                                layout: 'column',
                                                items: [{
                                                        layout: 'form',
                                                        columnWidth: .3,
                                                        items: [{
                                                                xtype: 'button',
                                                                fieldLabel: 'Coordinates',
                                                                tooltip: 'Locate latitude and longitude',
                                                                icon: IMAGE_BASE_PATH + "/default/icons/map.png",
                                                                iconCls: 'my-icon1',
                                                                id: 'map_button1',
                                                                name: 'map_button1',
                                                                disabled: true,
                                                                text: 'Find Coordinates',
                                                                tabIndex: 211,
                                                                handler: function () {
                                                                    Ext.getCmp('branchgooglemap').clearMarkers();
                                                                    my_marker = [];
                                                                    
                                                                    var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                                    //Ext.getCmp('branchgooglemap').clearMarkers();
                                                                    my_marker.push({
                                                                        geoCodeAddr: Ext.getCmp("b2b_Customer_pincode").getValue() + " ,India",
                                                                        setCenter: true,
                                                                        marker: {
                                                                            title: "Click and Drag to Move Around",
                                                                            draggable: true
                                                                        },
                                                                        listeners: {
                                                                            onFailure: function () {

                                                                            },
                                                                            "tilesloaded": function (markerAt) {
                                                                                Ext.getCmp('b2b_Customer_Lat').setValue(markerAt.latLng.lat());
                                                                                Ext.getCmp('b2b_Customer_Lng').setValue(markerAt.latLng.lng());
                                                                            },
                                                                            onSuccess: function (point) {
                                                                                Ext.getCmp('b2b_Customer_Lat').setValue(point.latLng.lat());
                                                                                Ext.getCmp('b2b_Customer_Lng').setValue(point.latLng.lng());
                                                                            },
                                                                            "dragend": function (markerAt) {
                                                                                Ext.getCmp('b2b_Customer_Lat').setValue(markerAt.latLng.lat());
                                                                                Ext.getCmp('b2b_Customer_Lng').setValue(markerAt.latLng.lng());
                                                                            }
                                                                        },
                                                                        icon: null
                                                                    });
                                                                    Ext.getCmp('branchgooglemap').addScaleControl();
                                                                    Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                    Ext.defer(function(){
                                                                         var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                                         Ext.getCmp('b2b_Customer_Lat').setValue(point.lat);
                                                                         Ext.getCmp('b2b_Customer_Lng').setValue(point.lng);
                                                                         Ext.getCmp('branchgooglemap').clearMarkers();
                                                                         Ext.getCmp('branchgooglemap').repaint(13);
                                                                         Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                     },1200);
                                                                }
                                                            }]
                                                    }, {
                                                        layout: 'form',
                                                        columnWidth: .35,
                                                        items: [{
                                                                xtype: 'textfield',
                                                                fieldLabel: 'Latitude',
                                                                //style: 'padding-left:5px;',
                                                                id: 'b2b_Customer_Lat',
                                                                name: 'b2b_Customer_Lat',
                                                                readOnly: true,
                                                                msgTarget: 'under',
                                                                tabIndex: 212,
                                                                allowBlank: false,
                                                                anchor: '95%'
                                                            }]
                                                    }, {
                                                        layout: 'form',
                                                        columnWidth: .35,
                                                        items: [{
                                                                xtype: 'textfield',
                                                                fieldLabel: 'Longitude',
                                                                id: 'b2b_Customer_Lng',
                                                                name: 'b2b_Customer_Lng',
                                                                readOnly: true,
                                                                msgTarget: 'under',
                                                                tabIndex: 213,
                                                                allowBlank: false,
                                                                anchor: '95%'
                                                            }]
                                                    }]
                                            }]
                                    }]
                            }
                        ]
                    }]

            });
            return form;
        };
    }
//B2B Customer END


//B2B Sales order BEGIN
    {

        var removeFromItemStore = function (grid, indx, record) {

            Ext.Ajax.request({
                url: modURL + '&op=deleteItemFromB2BSO',
                method: 'POST',
                params: {
                    itemid: record.get('b2bso_itemid'),
                    uid: Application.RetalineSales.Cache.b2bso_UniqueID
                },
                success: function (response) {
                    var res = Ext.decode(response.responseText);
                    if (res.success === true)
                    {
                        Application.example.msg('Success', 'Item removed item from Sales Order');
                        Ext.getCmp('b2bso_ItemGrid').getStore().load({
                            params: {
                                b2bso_UniqueID: Application.RetalineSales.Cache.b2bso_UniqueID
                            }
                        });
                        Ext.getCmp('b2bSOCustomerItems').setReadOnly(false);
                        Ext.getCmp('addItemsToPO').setText('Add');
                        //Ext.getCmp('b2bSOCustomerItems').focus(false, 100);
                        Ext.getCmp('b2bSOCustomerItems').reset();
                        Ext.getCmp('b2bso_itemmrp').reset();
                        Ext.getCmp('b2bso_itemqty').reset();
                        Ext.getCmp('b2bso_itemrate').reset();
                        Ext.getCmp('b2bso_itemPkg').reset();
                        Ext.getCmp('b2bso_amount').reset();
                        Ext.getCmp('b2bso_netamount').reset();
                        Ext.getCmp('b2bso_discountin').reset();
                        Ext.getCmp('b2bso_discount').reset();
                    } else
                    {
                        Ext.MessageBox.alert('Failed', response.responseText);
                    }
                },
                failure: function (response) {
                    Ext.MessageBox.alert('Error', response.responseText);
                }
            });

        };

        var updateFields = function () {
            Ext.defer(function () {
                var b2bso_itemqty = Ext.getCmp('b2bso_itemqty').getValue();
                var b2bso_itemrate = Ext.getCmp('b2bso_itemrate').getValue();
                var amount = b2bso_itemqty * b2bso_itemrate;
                Ext.getCmp('b2bso_amount').setValue(amount);
                var b2bso_netamount;
                var b2bso_discountin = Ext.getCmp('b2bso_discountin').getRawValue();
                var b2bso_amount = Ext.getCmp('b2bso_amount').getValue();
                var b2bso_itemdiscount = Ext.getCmp('b2bso_discount').getValue();
                if (b2bso_discountin == 'Amount') {
                    b2bso_netamount = parseFloat(b2bso_amount) - parseFloat(b2bso_itemdiscount);
                } else if (b2bso_discountin == 'Percentage') {
                    b2bso_netamount = parseFloat(b2bso_amount) - (parseFloat(b2bso_amount) * parseFloat(b2bso_itemdiscount) / 100);
                } else {
                    b2bso_netamount = b2bso_amount;
                }

                Ext.getCmp('b2bso_netamount').setValue(b2bso_netamount);
            }, 500);

        };

        var editAndUupdateRecord = function (record, grid, rowIndex) {

            Application.RetalineSales.Cache.editingItemRec = true;
            Application.RetalineSales.Cache.currentItemID = record.get('b2bso_itemid');
            Ext.getCmp('b2bSOCustomerItems').setValue(record.get('b2bso_itemid'));
            Application.RetalineSales.Cache.currentItem = record.get('b2bso_itemname');
            Ext.getCmp('b2bSOCustomerItems').setRawValue(record.get('b2bso_itemname'));

            Ext.getCmp('b2bSOCustomerItems').setReadOnly(true);
            Ext.getCmp('b2bso_itemqty').setValue(record.get('b2bso_itemqty'));
            Ext.getCmp('b2bso_itemPkg').setValue(record.get('b2bso_itemPkg'));
            Ext.getCmp('b2bso_itemPkg').setRawValue(record.get('b2bso_itemPkg'));

            Ext.getCmp('b2bso_itemqty').focus();
            Ext.getCmp('b2bso_itemqty').fireEvent('change', Ext.getCmp('b2bso_itemqty'));
            Ext.getCmp('addItemsToPO').setText('Update');
            Ext.getCmp('addItemsToPO').enable();

            Ext.defer(function () {
                if (parseFloat(Ext.getCmp('b2bso_itemrate').getValue()) <= 0) {
                    Ext.Msg.confirm('Alert', 'Selling price of this item is not set for your branch.<br>Do you really want to remove this item?', function (btn) {
                        if (btn === 'yes') {
                            removeFromItemStore(grid, rowIndex, record);
                        }
                    });
                }
            }, 500);


        };

        var getItemBatchDetails = function (itemID) {
            if (parseInt(Ext.getCmp('b2bso_itemqty').getValue()) > 0) {
                Ext.Ajax.request({
                    url: modURL + '&op=getItemBatchDetails',
                    params: {
                        itemID: itemID,
                        itemQty: parseInt(Ext.getCmp('b2bso_itemqty').getValue()),
                        rbsch_name: Ext.getCmp('sorbsch_name').getValue()
                    },
                    failure: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        Ext.MessageBox.alert('Error', tmp.msg);
                    },
                    success: function (response, options) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success === true) {
                            Ext.getCmp('fsbg_id').setValue(tmp.record.fsbg_id);
                            Ext.getCmp('b2bso_itemmrp').setValue(tmp.record.fsbg_mrp);
                            Ext.getCmp('b2bso_itemrate').setValue(tmp.record.fsbg_sellinprice);
                        } else {
                            if (tmp.msg) {
                                Ext.MessageBox.alert('Error', tmp.msg);
                            } else {
                                Ext.MessageBox.alert('Error', tmp.record.msg);
                            }
                        }
                    }
                });
            }
        };

        var createB2BSalesOrderForm = function (bbso_id, customerID, salesRequestIDs) {

            var b2bCustomerItemStore = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + '&op=getB2BCustomerSOItems',
                method: 'post',
                fields: ['stit_ID', 'stit_SKU'],
                totalProperty: 'totalCount',
                root: 'data'
            });

            var b2bProfitDividence = new Ext.data.JsonStore({
                autoLoad: false,
                url: modURL + '&op=getB2BDividence',
                method: 'post',
                fields: [
                    {name: 'company', type: 'int'},
                    {name: 'technology', type: 'int'},
                    {name: 'b2BCustomer', type: 'int'}
                ],
                totalProperty: 'totalCount',
                root: 'data'
            });

            var b2bSalesCustomerStore = new Ext.data.JsonStore({
                fields: ['b2b_Customer_ID', 'b2b_Customer_Name'],
                url: modURL + '&op=getB2BBrCustomers',
                autoLoad: true,
                method: 'post',
                totalProperty: 'totalCount',
                root: 'data'
            });

            var panel = new Ext.form.FormPanel({
                autoHeight: true,
                frame: true,
                border: false,
                id: 'b2bSaleOrderPanelID',
                layout: 'column',
                listeners: {
                    afterrender: function (thisPanel) {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        Ext.Ajax.request({
                            url: modURL + '&op=generateUniqueID',
                            params: {
                                apikey: _SESSION.apikey,
                                tstamp: t_stamp

                            },
                            method: 'POST',
                            success: function (res) {
                                var tmp = Ext.decode(res.responseText);
                                var uid = tmp.uid;
                                Application.RetalineSales.Cache.b2bso_UniqueID = uid;
                                Application.RetalineSales.Cache.salesRequestIDs = [];
                                Ext.getCmp('UniqueID').setValue(uid);
                                if (bbso_id > 0) {
                                    Ext.Ajax.request({
                                        url: modURL + '&op=viewB2BSODetails',
                                        method: 'POST',
                                        params: {
                                            bbso_id: bbso_id
                                        },
                                        success: function (response) {
                                            var res = Ext.decode(response.responseText);
                                            Ext.getCmp('b2bSOCustomer').setValue(res.SOdata.b2b_Customer_ID);
                                            Ext.getCmp('b2bSOCustomer').fireEvent('select', Ext.getCmp('b2bSOCustomer'));
                                            Ext.getCmp('b2bSOCustomer').setReadOnly(true);
                                            Ext.getCmp('b2bso_SOHandlingCharges').setValue(res.SOdata.bbso_HandlingCharges);
                                            Ext.getCmp('b2bso_SOHandlingCharges').setReadOnly(true);
                                            Ext.getCmp('b2bso_SONetTotal').setValue(res.SOdata.bbso_SOValue);
                                            Ext.getCmp('b2bso_SOHandlingCharges').setReadOnly(true);
                                            Ext.getCmp('addItemsToPO').disable();
                                            Ext.getCmp('b2bsoSubmitBtn').disable();
                                            Ext.getCmp('b2bso_ItemGrid').getStore().loadData(res);
                                        },
                                        failure: function (response) {
                                            Ext.MessageBox.alert('Error', response.responseText);
                                        }
                                    });
                                } else if (customerID > 0)
                                {
                                    Application.RetalineSales.Cache.CustomerID = customerID;
                                    Application.RetalineSales.Cache.salesRequestIDs = salesRequestIDs;
                                    Ext.getCmp('b2bSOCustomer').setReadOnly(true);
                                    Ext.Ajax.request({
                                        url: modURL + '&op=checkDuplicateItems',
                                        method: 'POST',
                                        params: {
                                            customerID: customerID,
                                            b2bso_UniqueID: Application.RetalineSales.Cache.b2bso_UniqueID,
                                            salesRequestIDs: Ext.encode(salesRequestIDs)
                                        },
                                        success: function (response) {
                                            var res = Ext.decode(response.responseText);
                                            var updateerFun = function (customerID, salesRequestIDs) {
                                                Ext.Ajax.request({
                                                    url: modURL + '&op=populateB2BSRDetailsInSO',
                                                    method: 'POST',
                                                    params: {
                                                        customerID: customerID,
                                                        b2bso_UniqueID: Application.RetalineSales.Cache.b2bso_UniqueID,
                                                        salesRequestIDs: Ext.encode(salesRequestIDs)
                                                    },
                                                    success: function (response) {
                                                        var res = Ext.decode(response.responseText);
                                                        Ext.getCmp('b2bSOCustomer').setValue(res.SOdata.b2b_Customer_ID);
                                                        Ext.getCmp('b2bSOCustomer').fireEvent('select', Ext.getCmp('b2bSOCustomer'));
                                                        Ext.getCmp('b2bSOCustomer').setReadOnly(true);
                                                        Ext.getCmp('b2bso_SONetTotal').setValue(res.SOdata.bbso_SOValue);
                                                        Ext.getCmp('b2bso_ItemGrid').getStore().loadData(res);
                                                    },
                                                    failure: function (response) {
                                                        Ext.MessageBox.alert('Error', response.responseText);
                                                    }
                                                });


                                            }
                                            if (res.count > 0) {
                                                Ext.MessageBox.confirm("Confirm", "Please repeated items lastest order only be selected. ",
                                                        //Ext.MessageBox.confirm("Confirm", "Please note for the following items lastest order only be selected. <br>" +  JSON.stringify(res.data,null,'\t'),
                                                                function (btn, text) {
                                                                    if (btn == 'yes') {
                                                                        updateerFun(customerID, salesRequestIDs);
                                                                    }
                                                                });
                                                    } else {

                                            }

                                        },
                                        failure: function (response) {
                                            Ext.MessageBox.alert('Error', response.responseText);
                                        }
                                    });

                                }
                            }
                        });

                    }
                },
                items: [{
                        layout: 'column',
                        style: 'margin-bottom:3px;',
                        columnWidth: 1,
                        items: [
                            {
                                layout: 'form',
                                columnWidth: 0.0,
                                items: [{
                                        xtype: 'hidden',
                                        id: 'UniqueID',
                                        name: 'UniqueID'
                                    }]
                            },
                            {
                                layout: 'form',
                                columnWidth: 0.19,
                                labelAlign: 'top',
                                items: [{
                                        xtype: 'combo',
                                        fieldLabel: 'B2B Client Name',
                                        emptyText: 'Choose B2B Customer',
                                        id: 'b2bSOCustomer',
                                        name: 'b2bSO[b2bSOCustomer]',
                                        labelStyle: mandatory_label,
                                        allowBlank: false,
                                        mode: 'remote',
                                        typeAhead: true,
                                        forceSelection: true,
                                        editable: true,
                                        anchor: '97%',
                                        store: b2bSalesCustomerStore,
                                        triggerAction: 'all',
                                        minChars: 2,
                                        displayField: 'b2b_Customer_Name',
                                        valueField: 'b2b_Customer_ID',
                                        hiddenName: 'b2bSOCustomer',
                                        tabIndex: 501,
                                        listeners: {
                                            select: function () {
                                                var b2bSOCustomer = Ext.getCmp('b2bSOCustomer').getValue();
                                                Ext.getCmp('b2bSOCustomerItems').reset();
                                                Ext.getCmp('b2bSOCustomerItems').getStore().load({
                                                    params: {
                                                        b2b_Customer_id: b2bSOCustomer
                                                    }
                                                });

                                                Ext.Ajax.request({
                                                    url: modURL + '&op=b2bSOCustomerDetails',
                                                    method: 'POST',
                                                    params: {
                                                        'b2bSOCustomer': b2bSOCustomer
                                                    },
                                                    success: function (res) {
                                                        var tmp = Ext.decode(res.responseText);

                                                        Ext.getCmp('b2bSOContactPerson').setValue(tmp.b2b_Customer_Incharge);
                                                        Ext.getCmp('b2bSOCustomerPincode').setValue(tmp.b2b_Customer_pincode);
                                                        Ext.getCmp('b2bSOCustomerMobile').setValue(tmp.b2b_Customer_Mobile);
                                                        Ext.getCmp('b2bSOCustomerGST').setValue(tmp.b2b_Customer_gst);
                                                        Ext.getCmp('sorbsch_name').setValue(tmp.rbsch_name);
                                                        if (Application.RetalineSales.Cache.currentItemID > 0) {
                                                            getItemBatchDetails(Application.RetalineSales.Cache.currentItemID);
                                                            Application.RetalineSales.Cache.currentItemID = 0;
                                                            updateFields();
                                                        }
                                                    },
                                                    failure: function (response) {
                                                        Ext.MessageBox.alert('Error', response.responseText);
                                                    }
                                                });
                                            }
                                        }
                                    }]
                            },
                            {
                                xtype: 'fieldset',
                                title: 'B2B Customer Details',
                                collapsible: true,
                                columnWidth: .80,
                                layout: 'column',
                                style: 'margin-bottom:3px; margin-left:3px;',
                                id: 'b2bSOCustomerDetails',
                                items: [
                                    {
                                        xtype: 'hidden',
                                        allowBlank: true,
                                        id: 'sorbsch_name',
                                        name: 'sorbsch_name'
                                    }, {
                                        layout: 'form',
                                        style: 'margin-bottom:3px;',
                                        columnWidth: .30,
                                        labelAlign: 'left',
                                        labelWidth: 100,
                                        items: [{
                                                xtype: 'displayfield',
                                                fieldLabel: 'Conntact Person',
                                                id: 'b2bSOContactPerson',
                                                style: {
                                                    'font-weight': 'bold'
                                                },
                                                anchor: '97%'
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        style: 'margin-bottom:3px;',
                                        columnWidth: .15,
                                        labelAlign: 'left',
                                        labelWidth: 45,
                                        items: [{
                                                xtype: 'displayfield',
                                                fieldLabel: 'Pincode',
                                                id: 'b2bSOCustomerPincode',
                                                style: {
                                                    'font-weight': 'bold'
                                                },
                                                anchor: '97%'
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        style: 'margin-bottom:3px;',
                                        columnWidth: .35,
                                        labelAlign: 'left',
                                        labelWidth: 37,
                                        items: [{
                                                xtype: 'displayfield',
                                                fieldLabel: 'Mobile',
                                                id: 'b2bSOCustomerMobile',
                                                style: {
                                                    'font-weight': 'bold'
                                                },
                                                anchor: '97%'
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        style: 'margin-bottom:3px;',
                                        columnWidth: .20,
                                        labelAlign: 'left',
                                        labelWidth: 25,
                                        items: [{
                                                xtype: 'displayfield',
                                                fieldLabel: 'GST',
                                                id: 'b2bSOCustomerGST',
                                                style: {
                                                    'font-weight': 'bold'
                                                },
                                                anchor: '97%'
                                            }]
                                    }
                                ]
                            }]
                    },
                    {
                        layout: 'column',
                        bodyStyle: {
                            "padding": "2px 2px 2px 2px"
                        },
                        style: 'margin-bottom:3px;',
                        id: 'itemImputPanel',
                        columnWidth: 1,
                        items: [{
                                layout: 'column',
                                style: 'margin-bottom:3px;',
                                columnWidth: 1,
                                items: [{
                                        layout: 'form',
                                        columnWidth: 0.55,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'combo',
                                                fieldLabel: 'Items',
                                                id: 'b2bSOCustomerItems',
                                                name: 'b2bSOCustomerItems',
                                                labelStyle: mandatory_label,
                                                allowBlank: true,
                                                labelAlign: 'top',
                                                mode: 'remote',
                                                typeAhead: true,
                                                editable: true,
                                                anchor: '97%',
                                                store: b2bCustomerItemStore,
                                                triggerAction: 'all',
                                                minChars: 3,
                                                displayField: 'stit_SKU',
                                                valueField: 'stit_ID',
                                                hiddenName: 'b2bSOCustomerItems',
                                                tabIndex: 502,
                                                listeners: {
                                                    select: function (combo, record, index) {
                                                        if (Ext.getCmp('b2bSOCustomer').getValue() <= 0) {
                                                            Ext.MessageBox.alert("Alert", "Please Select Customer.",
                                                                    function (btn) {
                                                                        // Ext.getCmp('b2bSOCustomer').focus();
                                                                    });

                                                        } else
                                                        {
                                                            getItemBatchDetails(record.get('stit_ID'));
                                                            updateFields();
                                                        }

                                                    },
                                                    valid: function (combo) {
                                                        if (Application.RetalineSales.Cache.currentItemID > 0) {
                                                            getItemBatchDetails(Application.RetalineSales.Cache.currentItemID);
                                                            Application.RetalineSales.Cache.currentItemID = 0;
                                                            updateFields();
                                                        }
                                                    }
                                                }
                                            }]
                                    },
                                    {
                                        xtype: 'hidden',
                                        allowBlank: true,
                                        id: 'fsbg_id',
                                        name: 'fsbg_id'
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.07,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'numberfield',
                                                fieldLabel: 'MRP',
                                                format: FINASCOP_CURRENCY_FORMAT,
                                                style: 'text-align:right',
                                                readOnly: true,
                                                id: 'b2bso_itemmrp',
                                                name: 'n[b2bso_itemmrp]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                listeners: {
                                                    afterrender: function (field) {

                                                    }
                                                }
                                            }]
                                    }, {
                                        layout: 'form',
                                        labelAlign: 'top',
                                        columnWidth: 0.06,
                                        items: [{
                                                xtype: 'numberfield',
                                                fieldLabel: 'Rate',
                                                format: FINASCOP_CURRENCY_FORMAT,
                                                style: 'text-align:right',
                                                readOnly: true,
                                                id: 'b2bso_itemrate',
                                                name: 'n[b2bso_itemrate]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                listeners: {
                                                    change: function (e) {

                                                    }
                                                }
                                            }]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.08,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'numberfield',
                                                fieldLabel: 'Quantity',
                                                style: 'text-align:right',
                                                id: 'b2bso_itemqty',
                                                selectOnFocus: true,
                                                allowDecimal: false,
                                                name: 'n[b2bso_itemqty]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                tabIndex: 505,
                                                listeners: {
                                                    change: function () {
                                                        getItemBatchDetails(Ext.getCmp('b2bSOCustomerItems').getValue());
                                                        updateFields();
                                                    }
                                                }
                                            }]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.07,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'combo',
                                                displayField: 'name',
                                                valueField: 'id',
                                                mode: 'local',
                                                id: 'b2bso_itemPkg',
                                                forceSelection: true,
                                                fieldLabel: 'Pack Type',
                                                emptyText: 'Package Type',
                                                anchor: '98%',
                                                allowBlank: false,
                                                typeAhead: true,
                                                triggerAction: 'all',
                                                lazyRender: true,
                                                editable: true,
                                                minChars: 2,
                                                tabIndex: 506,
                                                store: new Ext.data.JsonStore({
                                                    fields: ['id', 'name'],
                                                    data: [{id: 'Box', name: 'Box'}, {id: 'Carton', name: 'Carton'}, {id: 'Item', name: 'Item'}]
                                                }), listeners: {
                                                    select: function () {

                                                    }
                                                }
                                            }]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.09,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'numberfield',
                                                fieldLabel: 'Amount',
                                                format: FINASCOP_CURRENCY_FORMAT,
                                                style: 'text-align:right',
                                                hidden: true,
                                                id: 'b2bso_amount',
                                                name: 'n[b2bso_amount]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                readOnly: true
                                            }]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.05,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'numberfield',
                                                fieldLabel: 'Item Dis.',
                                                style: 'text-align:right',
                                                format: '00.00',
                                                hidden: true,
                                                id: 'b2bso_discount',
                                                name: 'n[b2bso_discount]',
                                                anchor: '98%',
                                                value: 0,
                                                tabIndex: 507,
                                                listeners: {
                                                    change: function () {
                                                        updateFields();
                                                    }
                                                }
                                            }]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.08,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'combo',
                                                displayField: 'name',
                                                valueField: 'id',
                                                mode: 'local',
                                                id: 'b2bso_discountin',
                                                forceSelection: true,
                                                hidden: true,
                                                fieldLabel: 'Amt /Percent',
                                                emptyText: 'Amt / Percentage',
                                                anchor: '98%',
                                                typeAhead: true,
                                                triggerAction: 'all',
                                                lazyRender: true,
                                                editable: true,
                                                minChars: 2,
                                                value: 'Percentage',
                                                tabIndex: 508,
                                                store: new Ext.data.JsonStore({
                                                    fields: ['id', 'name'],
                                                    data: [{id: 'Amount', name: 'Amount'}, {id: 'Percentage', name: 'Percentage'}]
                                                }), listeners: {
                                                    select: function (combo, record, index) {
                                                        updateFields();
                                                    }
                                                }
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        labelAlign: 'top',
                                        columnWidth: 0.11,
                                        items: [{
                                                xtype: 'numberfield',
                                                fieldLabel: 'Net Amt',
                                                format: FINASCOP_CURRENCY_FORMAT,
                                                id: 'b2bso_netamount',
                                                style: 'text-align:right',
                                                name: 'n[b2bso_netamount]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                tabIndex: 509,
                                                readOnly: true,
                                                listeners: {
                                                    focus: function (txtFld) {
                                                        updateFields();
                                                    }
                                                }
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        labelAlign: 'top',
                                        columnWidth: 0.06,
                                        items: [{
                                                xtype: 'button',
                                                text: 'Add',
                                                width: '20px',
                                                tabIndex: 513,
                                                id: 'addItemsToPO',
                                                iconCls: 'finascop_add',
                                                style: 'margin-top:19px; margin-left:2px;',
                                                handler: function () {
                                                    if (Ext.getCmp('b2bso_netamount').getValue() > 0 && Ext.getCmp('b2bso_itemPkg').isValid()) {
                                                        Ext.Ajax.request({
                                                            waitMsg: 'Processing',
                                                            method: 'POST',
                                                            url: modURL + '&op=saveB2BSOItemDetails',
                                                            params: {
                                                                b2bso_UniqueID: Application.RetalineSales.Cache.b2bso_UniqueID,
                                                                b2bso_Customerid: Ext.getCmp('b2bSOCustomer').getValue(),
                                                                b2bso_itemid: Ext.getCmp('b2bSOCustomerItems').getValue(),
                                                                b2bso_itemname: Ext.getCmp('b2bSOCustomerItems').getRawValue(),
                                                                b2bso_itemmrp: Ext.getCmp('b2bso_itemmrp').getValue(),
                                                                b2bso_itemrate: Ext.getCmp('b2bso_itemrate').getValue(),
                                                                b2bso_itemqty: Ext.getCmp('b2bso_itemqty').getValue(),
                                                                b2bso_itemPkg: Ext.getCmp('b2bso_itemPkg').getValue(),
                                                                b2bso_discount: Ext.getCmp('b2bso_discount').getValue(),
                                                                b2bso_discountin: Ext.getCmp('b2bso_discountin').getValue(),
                                                                b2bso_amount: Ext.getCmp('b2bso_amount').getValue(),
                                                                b2bso_netamount: Ext.getCmp('b2bso_netamount').getValue(),
                                                                rbsch_name: Ext.getCmp('sorbsch_name').getValue(),
                                                                fsbg_id: Ext.getCmp('fsbg_id').getValue()
                                                            },
                                                            success: function (response) {
                                                                var tmp = Ext.decode(response.responseText);
                                                                if (tmp.success === true) {
                                                                    Application.example.msg('Success', tmp.msg);
                                                                    Ext.getCmp('b2bso_ItemGrid').getStore().load({
                                                                        params: {
                                                                            b2bso_UniqueID: Application.RetalineSales.Cache.b2bso_UniqueID
                                                                        },
                                                                        callback: function () {
                                                                            Ext.getCmp('b2bSOCustomerItems').focus(false, 100);
                                                                            Ext.getCmp('b2bSOCustomerItems').reset();
                                                                            Ext.getCmp('b2bso_itemmrp').reset();
                                                                            Ext.getCmp('b2bso_itemqty').reset();
                                                                            Ext.getCmp('b2bso_itemrate').reset();
                                                                            Ext.getCmp('b2bso_itemPkg').reset();
                                                                            Ext.getCmp('b2bso_amount').reset();
                                                                            Ext.getCmp('b2bso_netamount').reset();
                                                                            Ext.getCmp('b2bso_discountin').reset();
                                                                            Ext.getCmp('b2bso_discount').reset();
                                                                        }
                                                                    });

                                                                } else {
                                                                    Ext.MessageBox.alert("Failure", "Error moving Data." + response.responseText);
                                                                }
                                                            },
                                                            failure: function (response) {
                                                                Ext.MessageBox.alert('Error', response.responseText);
                                                            }
                                                        });
                                                        Ext.getCmp('b2bSOCustomerItems').setReadOnly(false);
                                                        Ext.getCmp('addItemsToPO').setText('Add');
                                                    } else {
                                                        Ext.Msg.alert("Notification.", 'Unable to add items. <br>Please fill necessary fields.');
                                                    }

                                                }
                                            }]
                                    }]
                            }]
                    },
                    {
                        xtype: 'fieldset',
                        title: 'b2b Sales Order Items',
                        columnWidth: 1,
                        style: 'margin-bottom:3px;',
                        id: 'b2b_so_gridItems',
                        items: [{
                                layout: 'column',
                                items: [{
                                        layout: 'form',
                                        columnWidth: 1,
                                        items: [
                                            newPOItemGrid()
                                        ]
                                    }]
                            }]
                    },
                    {
                        layout: 'column',
                        columnWidth: 1,
                        items: [{
                                layout: 'form',
                                columnWidth: 0.70,
                                items: [{
                                        xtype: 'label',
                                        html: '&nbsp;'
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.15,
                                labelWidth: 55,
                                items: [{
                                        xtype: 'numberfield',
                                        format: FINASCOP_CURRENCY_FORMAT,
                                        fieldLabel: 'Handling Charges',
                                        style: 'text-align:right',
                                        id: 'b2bso_SOHandlingCharges',
                                        name: 'b2bso_SOHandlingCharges',
                                        allowNegative: false,
                                        allowDecimals: false,
                                        tabIndex: 515,
                                        anchor: '99%',
                                        listeners: {
                                            focus: function () {
                                                var store = Ext.getCmp('b2bso_ItemGrid').getStore();
                                                var handlingCharges = 0 + Ext.getCmp('b2bso_SOHandlingCharges').getValue();
                                                var b2bso_totalnetamount = store.sum('b2bso_netamount') + parseFloat(handlingCharges);
                                                Ext.getCmp('b2bso_SONetTotal').setValue(b2bso_totalnetamount);
                                            },
                                            blur: function () {
                                                var store = Ext.getCmp('b2bso_ItemGrid').getStore();
                                                var handlingCharges = 0 + Ext.getCmp('b2bso_SOHandlingCharges').getValue();
                                                var b2bso_totalnetamount = store.sum('b2bso_netamount') + parseFloat(handlingCharges);
                                                Ext.getCmp('b2bso_SONetTotal').setValue(b2bso_totalnetamount);
                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.15,
                                labelWidth: 35,
                                items: [{
                                        xtype: 'numberfield',
                                        format: FINASCOP_CURRENCY_FORMAT,
                                        fieldLabel: 'Total',
                                        style: 'text-align:right',
                                        id: 'b2bso_SONetTotal',
                                        name: 'b2bso_SONetTotal',
                                        allowNegative: true,
                                        allowDecimals: true,
                                        readOnly: true,
                                        anchor: '99%'
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.0,
                                items: [{
                                        xtype: 'label',
                                        html: '&nbsp;'
                                    }]
                            }]
                    }
                ]
            });

            return panel;
        };

        var saveB2BSalesOrder = function () {
            Ext.Ajax.request({
                url: modURL + '&op=saveB2BSalesOrder',
                method: 'POST',
                params: {
                    b2b_Customer_ID: Ext.getCmp('b2bSOCustomer').getValue(),
                    b2b_Customer_Name: Ext.getCmp('b2bSOCustomer').getRawValue(),
                    bbso_HandlingCharges: 0 + Ext.getCmp('b2bso_SOHandlingCharges').getValue(),
                    bbso_SOValue: Ext.getCmp('b2bso_SONetTotal').getValue(),
                    salesRequestIDs: Ext.encode(Application.RetalineSales.Cache.salesRequestIDs),
                    b2bso_UniqueID: Application.RetalineSales.Cache.b2bso_UniqueID,
                    rbsch_name: Ext.getCmp('sorbsch_name').getValue()
                },
                success: function (response) {
                    var res = Ext.decode(response.responseText);
                    if (res.success === true) {
                        Application.example.msg('Success', res.msg);
                        Ext.getCmp('b2bSalesOrderWinID').close();
                        Ext.isEmpty(Ext.getCmp('B2BSalesOrderGrid')) ? {} : Ext.getCmp('B2BSalesOrderGrid').getStore().load();
                        Ext.isEmpty(Ext.getCmp('B2BSalesRequestGrid')) ? {} : Ext.getCmp('B2BSalesRequestGrid').getStore().load();
                        Ext.isEmpty(Ext.getCmp('CustomerSRListGrid')) ? {} : Ext.getCmp('CustomerSRListGrid').getStore().load();
                        Application.RetalineSales.Cache.b2bso_UniqueID = '';
                    } else {
                        Ext.MessageBox.alert("Notification", response.responseText);
                    }
                },
                failure: function (response) {
                    Ext.MessageBox.alert('Error', response.responseText);
                }
            });
        };

        var createB2BSalesOrder = function (bbso_id, customerID, recIDs) {


            var B2BSalesOrderForm = createB2BSalesOrderForm(bbso_id, customerID, recIDs);
            var b2bSOrderWin = new Ext.Window({
                id: "b2bSalesOrderWinID",
                title: 'B2B Sales Order',
                shadow: false,
                height: 520,
                width: 1180,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                listeners: {
                    close: function (panel) {
                        if (Application.RetalineSales.Cache.b2bso_UniqueID != '')
                        {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            Ext.Ajax.request({
                                url: modURL + '&op=cancelTempB2BSO',
                                params: {
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp,
                                    b2bso_UniqueID: Application.RetalineSales.Cache.b2bso_UniqueID
                                },
                                method: 'POST',
                                success: function (res) {

                                },
                                failure: function (response) {
                                    Ext.MessageBox.alert('Error', response.responseText);
                                }
                            });
                        }
                    }
                },
                items: [B2BSalesOrderForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 523,
                        handler: function () {
                            Ext.getCmp('b2bSalesOrderWinID').close();
                            Application.RetalineSales.Cache.b2bso_UniqueID = '';
                        }
                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Submit',
                        id: 'b2bsoSubmitBtn',
                        tabIndex: 522,
                        handler: function () {
                            if ((parseFloat(Ext.getCmp('b2bso_SONetTotal').getValue()) > 0.00) && !Ext.isEmpty(Ext.getCmp('b2bSOCustomer').getValue())) {
                                var store = Ext.getCmp('b2bso_ItemGrid').getStore();
                                if (store.find('b2bso_itemmrp', '0.00') !== -1) {
                                    Ext.Msg.confirm('Notification', 'You have unprocessed items in Sales Order.<br>Do you really want to remove these item(s)?', function (btn) {
                                        if (btn === 'yes')
                                        {
                                            saveB2BSalesOrder();

                                        }
                                    });
                                } else {
                                    saveB2BSalesOrder();

                                }
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please fill B2B Sales Order details.');
                            }

                        }
                    }]
            });

            b2bSOrderWin.doLayout();
            b2bSOrderWin.show();
            b2bSOrderWin.center();
            //Ext.getCmp('b2bSOCustomer').focus(false, 100);
        };

        var pushPOToPackSure = function (bbso_id) {
            Ext.Ajax.request({
                url: modURL + '&op=pushB2BPOtoPackSure',
                method: 'POST',
                params: {
                    bbso_id: bbso_id
                },
                success: function (response, options) {
                    var res = Ext.decode(response.responseText);
                    if (res.success === true) {
                        Application.example.msg('Success', res.msg);

                        Ext.getCmp('B2BSalesOrderGrid').getStore().reload();

                    } else {
                        Ext.MessageBox.alert("Notification", response.responseText);
                    }
                },
                failure: function (response) {
                    Ext.MessageBox.alert('Error', response.responseText);
                }
            });
        };

        var B2BSalesOrderGridStore = function () {
            var _catStore = new Ext.data.GroupingStore({
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listB2BSalesOrders',
                    method: 'post'
                }),
                reader: new Ext.data.JsonReader({
                    totalProperty: 'totalCount',
                    idProperty: 'b2bSO_id',
                    root: 'data'
                }, ['bbso_id', 'bbso_SONumber', 'bbso_SODate', 'b2b_Customer_ID', 'b2b_Customer_Name', 'bbso_InvoiceStatusName',
                    'bbso_SOValue', 'bbso_HandlingCharges', 'bbso_Active', 'status_id', 'bbso_InvoiceStatus']),
                sortInfo: {
                    field: 'bbso_id',
                    direction: "DESC"
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

            return _catStore;
        };

        var B2B_SalesOrderEntryGrid = function () {
            var _b2bSO_GridFilter = new Ext.ux.grid.GridFilters({
                filters: [{
                        type: 'string',
                        dataIndex: 'bbso_SONumber'
                    }, {
                        type: 'string',
                        dataIndex: 'b2b_Customer_Name'
                    }, {
                        type: 'list',
                        value: '',
                        dataIndex: 'bbso_Active',
                        options: ['Active', 'Godown Boy Poll Rejected', 'Godown Boy Polled', 'Assigned Godown Boy', 'Queue For Manual Assignment'],
                        phpMode: true
                    }]
            });
            _b2bSO_GridFilter.remote = true;
            _b2bSO_GridFilter.autoReload = true;

            var _gridStore = B2BSalesOrderGridStore();

            var _gridPanel = new Ext.grid.GridPanel({
                id: 'B2BSalesOrderGrid',
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
                        text: 'Create B2B Sales Order',
                        tooltip: 'Create B2B Sales Order',
                        icon: './resources/images/submenuicons/add.png',
                        iconCls: 'my-icon1',
                        hidden: true,
                        handler: function () {
                            createB2BSalesOrder();
                        }
                    }],
                plugins: [new Ext.ux.grid.GroupSummary(), _b2bSO_GridFilter],
                columns: [
                    {
                        header: 'B2B SO Number',
                        sortable: true,
                        dataIndex: 'bbso_SONumber',
                        tooltip: 'B2B Sales Order Number',
                        hideable: false
                    },
                    {
                        header: 'B2B SO Date',
                        sortable: true,
                        dataIndex: 'bbso_SODate',
                        tooltip: 'B2B Sales Order Date'
                    },
                    {
                        header: 'Customer',
                        sortable: true,
                        dataIndex: 'b2b_Customer_Name',
                        tooltip: 'Customer',
                        hideable: false
                    },
                    {
                        header: 'B2B SO Total Value',
                        sortable: true,
                        dataIndex: 'bbso_SOValue',
                        tooltip: 'B2B Sales Order Status',
                        hideable: false
                    },
                    {
                        header: 'B2B SO Handling Charges',
                        sortable: true,
                        dataIndex: 'bbso_HandlingCharges',
                        tooltip: 'B2B Sales Order Status',
                        hideable: false
                    },
                    {
                        header: 'B2B SO Status',
                        sortable: true,
                        dataIndex: 'bbso_Active',
                        tooltip: 'B2B Sales Order Status',
                        hideable: false
                    }, {
                        header: 'Invoice Status',
                        sortable: true,
                        dataIndex: 'bbso_InvoiceStatusName',
                        tooltip: 'Invoice Status',
                        hideable: false
                    }, {
                    xtype: 'actioncolumn',
                    //header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            var bbso_InvoiceStatus = Ext.getCmp('B2BSalesOrderGrid').getSelectionModel().getSelections()[0].data.bbso_InvoiceStatus;    
                            salesorderrActionMenu(e,bbso_InvoiceStatus);
                            //action
                        }
                    }
                }
                    /*{
                        xtype: 'actioncolumn',
                        hideable: false,
                        sortable: false,
                        items: [
                            {
                                iconCls: 'application_view_detail',
                                tooltip: 'View',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    Application.RetalineSales.viewSODetails(record.get('bbso_id'));
                                }
                            }, {
                                tooltip: 'Create Sales Invoice',
                                getClass: function (v, meta, rec) {
                                    var status_id = rec.get('bbso_InvoiceStatus');
                                    if (status_id == 1)
                                    {
                                        return 'finascop_voucher';
                                    } else
                                    {
                                        return 'finascop_hideicon';
                                    }
                                },
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    Application.RetalineSalesInvoice.createB2BSalesInvoice(record.get('bbso_id'));
                                }
                            },
//                            {
//                                tooltip: 'Queue to Assign Carting',
//                                hidden: true,
//                                getClass: function (v, meta, rec) {
//                                    var status_id = rec.get('status_id');
//                                    if (status_id === '1')
//                                    {
//                                        return 'my-icon26';
//                                    } else
//                                    {
//                                        return 'finascop_hideicon';
//                                    }
//                                },
//                                handler: function (grid, rowIndex, colIndex) {
//                                    var record = grid.store.getAt(rowIndex);
//                                    pushPOToPackSure(record.get('bbso_id'));
//                                }
//                            },
                            {
                                tooltip: 'Create Transfer Order',
                                hidden: true,
                                getClass: function (v, meta, rec) {
                                    var status_id = rec.get('status_id');
                                    if (status_id === '1')
                                    {
                                        return 'my-icon26';
                                    } else
                                    {
                                        return 'finascop_hideicon';
                                    }
                                },
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);

                                    Ext.Ajax.request({
                                        url: modURL + '&op=createTransferOrder',
                                        method: 'POST',
                                        params: {
                                            bbso_id: record.get('bbso_id')
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true) {
                                                var tmp = Ext.decode(response.responseText);
                                                Application.example.msg('Success', tmp.msg);
                                                Ext.getCmp('B2BSalesOrderGrid').getStore().reload();
                                            }
                                            else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', response.responseText);
                                        }
                                    });
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
                        //selectionchange:
                    }
                }),
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: _gridStore,
                    displayInfo: true,
                    displayMsg: 'Displaying records {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                stripeRows: true,
                listeners: {
                    cellClick: function (grid, rowIndex, columnIndex, e) {
                        var record = grid.getStore().getAt(rowIndex);
                        var ID = record.get('SO_id');
                        if (!Ext.isEmpty(ID)) {

                        }
                    }
                    //resize: onGridResize
                }

            });
            return _gridPanel;
        };
        /*var salesorderrActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View",
                handler: function () {
                    var bbso_id = Ext.getCmp('B2BSalesOrderGrid').getSelectionModel().getSelections()[0].data.bbso_id;
                    Application.RetalineSales.viewSODetails(bbso_id);
                }
            }, {
                text: "Create Sales Invoice",
                handler: function () {
                    var bbso_id = Ext.getCmp('B2BSalesOrderGrid').getSelectionModel().getSelections()[0].data.bbso_id;
                    Application.RetalineSalesInvoice.createB2BSalesInvoice(bbso_id);
                }
            }]
    });*/
    var salesorderrActionMenu= function(e,bbso_InvoiceStatus){
        var salesorderrActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View",
                handler: function () {
                    var bbso_id = Ext.getCmp('B2BSalesOrderGrid').getSelectionModel().getSelections()[0].data.bbso_id;
                    Application.RetalineSales.viewSODetails(bbso_id);
                }
            },
            {
                text: "Create Sales Invoice",
                hidden: !(bbso_InvoiceStatus == 1),
                handler: function () {
                    var bbso_id = Ext.getCmp('B2BSalesOrderGrid').getSelectionModel().getSelections()[0].data.bbso_id;
                    Application.RetalineSalesInvoice.createB2BSalesInvoice(bbso_id);
                }
            }]
    });
    salesorderrActionMenu.showAt(e.getXY());
    };

        var createB2BSalesOrderGrid = function (id) {
            var panel = new Ext.Panel({
                layout: 'border',
                bodyStyle: {"background-color": "white"},
                border: false,
                frame: false,
                id: id,
                title: 'Wholesale Orders',
                //iconCls: 'b2bicon24x24',
                items: [B2B_SalesOrderEntryGrid()]
            });
            return panel;
        };
    }
//B2B Sales order END

//B2B Sales Request BEGIN
    {
        var showAllActiveCustomerSR = function (CustomerID, CustomerName) {

            var ck_selection = new Ext.grid.CheckboxSelectionModel({
                multiSelect: true,
                checkOnly: true,
                listeners: {
                    rowdeselect: function (sm, rowIndex, record) {

                    },
                    rowselect: function (sm, rowIndex, record) {

                    }
                }
            });

            var B2BCustomerSRGridStore = function () {
                var _catStore = new Ext.data.GroupingStore({
                    proxy: new Ext.data.HttpProxy({
                        url: modURL + '&op=listB2BSalesRequests',
                        method: 'post'
                    }),
                    reader: new Ext.data.JsonReader({
                        totalProperty: 'totalCount',
                        root: 'data'
                    }, ['bbsr_id', 'bbsr_SRNumber', 'bbsr_SRUpdatedOn', 'b2b_Customer_ID', 'b2b_Customer_Name', 'bbsr_Active']),
                    sortInfo: {
                        field: 'bbsr_id',
                        direction: "DESC"
                    },
                    groupField: '',
                    groupDir: 'ASC',
                    remoteSort: true,
                    autoLoad: true,
                    root: 'data',
                    listeners: {
                        load: function () {

                        },
                        beforeload: function (store, e) {
                            this.baseParams.CustomerID = CustomerID;
                        }
                    }
                });

                return _catStore;
            };

            var B2B_CustomerSRListGrid = function () {

                var _b2bSR_GridFilter = new Ext.ux.grid.GridFilters({
                    filters: [{
                            type: 'string',
                            dataIndex: 'bbsr_SRNumber'
                        }]
                });
                _b2bSR_GridFilter.remote = true;
                _b2bSR_GridFilter.autoReload = true;

                var _gridStore = B2BCustomerSRGridStore();

                var _gridPanel = new Ext.grid.GridPanel({
                    id: 'CustomerSRListGrid',
                    region: 'center',
                    layout: 'fit',
                    frame: false,
                    border: false,
                    height: 300,
                    iconCls: 'my-icon444',
                    store: _gridStore,
                    loadMask: true,
                    sm: ck_selection,
                    view: new Ext.grid.GroupingView({
                        forceFit: true,
                        deferEmptyText: false,
                        emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                        groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
                    }),
                    plugins: [new Ext.ux.grid.GroupSummary(), _b2bSR_GridFilter],
                    columns: [
                        ck_selection,
                        {
                            header: 'B2B Sales Request No.',
                            sortable: true,
                            dataIndex: 'bbsr_SRNumber',
                            tooltip: 'B2B Sales Request Number',
                            hideable: false
                        },
                        {
                            header: 'Date and Time',
                            sortable: true,
                            dataIndex: 'bbsr_SRUpdatedOn',
                            tooltip: 'B2B Sales Request Date & Time'
                        },
                        {
                            header: 'Customer',
                            sortable: true,
                            dataIndex: 'b2b_Customer_Name',
                            tooltip: 'Vendor',
                            hideable: false
                        },
                        {
                            header: 'B2B SO Status',
                            sortable: true,
                            dataIndex: 'bbsr_Active',
                            tooltip: 'B2B Sales Order Status',
                            hideable: false
                        }, {
                            xtype: 'actioncolumn',
                            hideable: false,
                            sortable: false,
                            items: [
                                {
                                    iconCls: 'application_view_detail',
                                    tooltip: 'View Sales Requst Details',
                                    handler: function (grid, rowIndex, colIndex) {
                                        var record = grid.store.getAt(rowIndex);
                                        createB2BSalesRequest(record.get('bbsr_id'));
                                    }
                                }
                            ]
                        }, {
                            header: '',
                            width: 5,
                            sortable: false,
                            hidden: true,
                            hideable: false
                        }
                    ],
                    viewConfig: {
                        forceFit: true
                    },
                    bbar: new Ext.PagingToolbar({
                        pageSize: recs_per_page,
                        store: _gridStore,
                        displayInfo: true,
                        displayMsg: 'Displaying records {0} - {1} of {2}',
                        emptyMsg: "No pages to display"
                    }),
                    stripeRows: true,
                    listeners: {
                        cellClick: function (grid, rowIndex, columnIndex, e) {
                            var record = grid.getStore().getAt(rowIndex);
                            var ID = record.get('SR_id');
                            if (!Ext.isEmpty(ID)) {

                            }
                        }

                    }

                });
                return _gridPanel;
            };

            var showB2BCustmerSalesRequest = function () {
                var CustomerSRListGrid = B2B_CustomerSRListGrid();
                var b2bSRCustWin = new Ext.Window({
                    id: "b2bCustomerSRsWinID",
                    title: 'Active Sales Requests of ' + CustomerName,
                    shadow: false,
                    height: 520,
                    width: 850,
                    modal: true,
                    layout: 'fit',
                    autoHeight: true,
                    resizable: true,
                    closable: true,
                    items: [CustomerSRListGrid],
                    buttons: [{
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            text: 'Cancel',
                            tabIndex: 523,
                            handler: function () {
                                Ext.getCmp('b2bCustomerSRsWinID').close();
                            }
                        }, {
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                            text: 'Create Sales Order',
                            id: 'b2bsrCustSubmitBtn',
                            tabIndex: 522,
                            handler: function () {
                                var saleReqIDs = [];
                                var store_fields = Ext.getCmp('CustomerSRListGrid').getSelectionModel().getSelections();
                                for (var i = 0; i < store_fields.length; i++) {
                                    saleReqIDs[i] = store_fields[i].data.bbsr_id;
                                }
                                createB2BSalesOrder(0, CustomerID, saleReqIDs);
                            }
                        }]
                });

                b2bSRCustWin.doLayout();
                b2bSRCustWin.show();
                b2bSRCustWin.center();

            };

            showB2BCustmerSalesRequest();
        };

        var saveB2BSalesRequest = function () {
            if (!Ext.getCmp('b2bSRCustomer').isValid(true)) {
                Ext.MessageBox.alert("Alert", "Please select Customer.");
            } else
            if (!Ext.getCmp('b2bsr_requesterName').isValid(true)) {
                Ext.MessageBox.alert("Alert", "Please enter requester name.");
            } else
            if (!Ext.getCmp('b2bsr_emailAndContactNo').isValid(true)) {
                Ext.MessageBox.alert("Alert", "Please enter requester contact info.");
            } else
            if (!Ext.getCmp('b2bsr_requestDate').isValid(true)) {
                Ext.MessageBox.alert("Alert", "Please enter Request Date.");
            } else
            if (!Ext.getCmp('b2bsr_requestTime').isValid(true)) {
                Ext.MessageBox.alert("Alert", "Please enter Request Time.");
            } else {
                Ext.Ajax.request({
                    url: modURL + '&op=saveB2BSalesRequest',
                    method: 'POST',
                    params: {
                        b2b_Customer_ID: Ext.getCmp('b2bSRCustomer').getValue(),
                        b2b_Customer_Name: Ext.getCmp('b2bSRCustomer').getRawValue(),
                        b2bsr_requesterName: Ext.getCmp('b2bsr_requesterName').getValue(),
                        b2bsr_emailAndContactNo: Ext.getCmp('b2bsr_emailAndContactNo').getValue(),
                        b2bsr_requestedThrough: Ext.getCmp('b2bsr_requestedThrough').getValue(),
                        b2bsr_requestDate: Ext.getCmp('b2bsr_requestDate').getValue(),
                        b2bsr_requestTime: Ext.getCmp('b2bsr_requestTime').getValue(),
                        b2bsr_UniqueID: Application.RetalineSales.Cache.b2bsr_UniqueID
                    },
                    success: function (response) {
                        var res = Ext.decode(response.responseText);
                        if (res.success === true) {
                            Application.example.msg('Success', res.msg);
                            Ext.getCmp('b2bSalesRequestWinID').close();
                            Ext.getCmp('B2BSalesRequestGrid').getStore().reload();

                        } else {
                            Ext.MessageBox.alert("Notification", response.responseText);
                        }
                    },
                    failure: function (response) {
                        Ext.MessageBox.alert('Error', response.responseText);
                    }
                });
            }
        };

        var b2bSalesRequestColModel = function () {
            return new Ext.grid.ColumnModel([
                {
                    header: 'Item',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bsr_itemname',
                    tooltip: 'Item',
                    width: 150

                },
                {
                    header: 'Qty',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bsr_itemqty',
                    tooltip: 'Qty',
                    align: 'right',
                    width: 25
                },
                {
                    header: 'Package Type',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bsr_itemPkg',
                    tooltip: 'Qty',
                    align: 'right',
                    width: 25
                },
                {
                    xtype: 'actioncolumn',
                    hideable: false,
                    width: 8,
                    items: [{
                            tooltip: 'Remove Item',
                            getClass: function (v, meta, rec) {
                                var bbsr_id = rec.get('bbsr_id');
                                if (bbsr_id <= 0)
                                {
                                    return 'finascop_delete';
                                } else
                                {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.Msg.confirm('Notification', 'Do you really want to remove this item?', function (btn) {
                                    if (btn == 'yes')
                                    {
                                        removeFromItemStore(grid, rowIndex, record);
                                    }
                                });
                            }
                        }
                    ]
                }
            ]);
        };

        var newSRItemGrid = function () {

            var b2bsr_ItemStore = function () {
                var itemStore = new Ext.data.JsonStore({
                    method: 'post',
                    proxy: new Ext.data.HttpProxy({
                        url: modURL + '&op=listB2BSRItems',
                        method: 'post'
                    }),
                    fields: ['bbso_id', 'b2bsr_itemid', 'b2bsr_itemname', 'b2bsr_itemPkg', 'b2bsr_itemqty'],
                    totalProperty: 'totalCount',
                    root: 'data',
                    remoteSort: true,
                    autoLoad: false,
                    listeners: {
                        beforeload: function (store, e) {
                            this.baseParams.b2bsr_UniqueID = Application.RetalineSales.Cache.b2bsr_UniqueID;
                        }
                    }

                });
                return itemStore;
            };
            var itemGridStore = b2bsr_ItemStore();
            var itemgrid_panel = new Ext.grid.GridPanel({
                store: itemGridStore,
                frame: false,
                border: false,
                title: '',
                height: 200,
                id: 'b2bsr_ItemGrid',
                loadMask: true,
                sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true
                }),
                cm: b2bSalesRequestColModel(),
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

        var createB2BSalesRequestForm = function (bbsr_id) {

            var b2bCustomerItemStore = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + '&op=getB2BCustomerItems',
                method: 'post',
                fields: ['stit_ID', 'stit_SKU'],
                totalProperty: 'totalCount',
                root: 'data'
            });

            var b2bSalesCustomerStore = new Ext.data.JsonStore({
                fields: ['b2b_Customer_ID', 'b2b_Customer_Name'],
                url: modURL + '&op=getB2BBrCustomers',
                autoLoad: true,
                method: 'post',
                totalProperty: 'totalCount',
                root: 'data'
            });

            var panel = new Ext.form.FormPanel({
                autoHeight: true,
                frame: true,
                border: false,
                id: 'b2bSaleOrderPanelID',
                layout: 'column',
                listeners: {
                    afterrender: function (thisPanel) {
                        if (bbsr_id > 0) {
                            Ext.Ajax.request({
                                url: modURL + '&op=viewB2BSRDetails',
                                method: 'POST',
                                params: {
                                    bbsr_id: bbsr_id
                                },
                                success: function (response) {
                                    var res = Ext.decode(response.responseText);
                                    Ext.getCmp('b2bSRCustomer').setValue(res.SRdata.b2b_Customer_ID);
                                    Ext.getCmp('b2bSRCustomer').fireEvent('select', Ext.getCmp('b2bSRCustomer'));
                                    Ext.getCmp('b2bSRCustomer').setReadOnly(true);
                                    Ext.getCmp('b2bsr_requesterName').setValue(res.SRdata.b2bsr_requesterName);
                                    Ext.getCmp('b2bsr_requesterName').setReadOnly(true);
                                    Ext.getCmp('b2bsr_emailAndContactNo').setValue(res.SRdata.b2bsr_emailAndContactNo);
                                    Ext.getCmp('b2bsr_emailAndContactNo').setReadOnly(true);
                                    Ext.getCmp('b2bsr_requestedThrough').setValue(res.SRdata.b2bsr_requestedThrough);
                                    Ext.getCmp('b2bsr_requestedThrough').setReadOnly(true);
                                    Ext.getCmp('b2bsr_requestDate').setValue(res.SRdata.b2bsr_requestDate);
                                    Ext.getCmp('b2bsr_requestDate').setReadOnly(true);
                                    Ext.getCmp('b2bsr_requestTime').setValue(res.SRdata.b2bsr_requestTime);
                                    Ext.getCmp('b2bsr_requestTime').setReadOnly(true);
                                    Ext.getCmp('addItemsToSR').disable();
                                    Ext.getCmp('b2bsrSubmitBtn').disable();
                                    Ext.getCmp('b2bsr_ItemGrid').getStore().loadData(res);
                                },
                                failure: function (response) {
                                    Ext.MessageBox.alert('Error', response.responseText);
                                }
                            });
                        }
                    }
                },
                items: [
                    {
                        layout: 'column',
                        style: 'margin-bottom:3px;',
                        columnWidth: 1,
                        items: [{
                                layout: 'form',
                                columnWidth: 0.23,
                                labelAlign: 'top',
                                items: [{
                                        xtype: 'combo',
                                        fieldLabel: 'B2B Client Name',
                                        emptyText: 'Choose B2B Customer',
                                        id: 'b2bSRCustomer',
                                        name: 'b2bSR[b2bSRCustomer]',
                                        labelStyle: mandatory_label,
                                        allowBlank: false,
                                        mode: 'remote',
                                        typeAhead: true,
                                        forceSelection: true,
                                        editable: true,
                                        anchor: '97%',
                                        store: b2bSalesCustomerStore,
                                        triggerAction: 'all',
                                        minChars: 2,
                                        displayField: 'b2b_Customer_Name',
                                        valueField: 'b2b_Customer_ID',
                                        hiddenName: 'b2bSOCustomer',
                                        tabIndex: 501,
                                        listeners: {
                                            select: function () {
                                                var b2bSRCustomer = Ext.getCmp('b2bSRCustomer').getValue();

                                                Ext.Ajax.request({
                                                    url: modURL + '&op=b2bSOCustomerDetails',
                                                    method: 'POST',
                                                    params: {
                                                        'b2bSOCustomer': b2bSRCustomer
                                                    },
                                                    success: function (res) {
                                                        var tmp = Ext.decode(res.responseText);

                                                        Ext.getCmp('b2bSRContactPerson').setValue(tmp.b2b_Customer_Incharge);
                                                        Ext.getCmp('b2bSRCustomerMobile').setValue(tmp.b2b_Customer_Mobile);
                                                    },
                                                    failure: function (response) {
                                                        Ext.MessageBox.alert('Error', response.responseText);
                                                    }
                                                });

                                            }
                                        }
                                    }]
                            },
                            {
                                xtype: 'fieldset',
                                title: 'B2B Customer Details',
                                collapsible: true,
                                columnWidth: .76,
                                layout: 'column',
                                style: 'margin-bottom:3px; margin-left:3px;',
                                id: 'b2bSRCustomerDetails',
                                items: [{
                                        layout: 'form',
                                        style: 'margin-bottom:3px;',
                                        columnWidth: .40,
                                        labelAlign: 'left',
                                        labelWidth: 100,
                                        items: [{
                                                xtype: 'displayfield',
                                                fieldLabel: 'Conntact Person',
                                                id: 'b2bSRContactPerson',
                                                style: {
                                                    'font-weight': 'bold'
                                                },
                                                anchor: '97%'
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        style: 'margin-bottom:3px;',
                                        columnWidth: .59,
                                        labelAlign: 'left',
                                        labelWidth: 37,
                                        items: [{
                                                xtype: 'displayfield',
                                                fieldLabel: 'Mobile',
                                                id: 'b2bSRCustomerMobile',
                                                style: {
                                                    'font-weight': 'bold'
                                                },
                                                anchor: '97%'
                                            }]
                                    }]
                            }]
                    },
                    {
                        layout: 'column',
                        style: 'margin-bottom:3px;',
                        columnWidth: .992,
                        items: [
                            {
                                xtype: 'fieldset',
                                title: 'B2B Sales Request Details',
                                collapsible: true,
                                columnWidth: 1,
                                layout: 'column',
                                style: 'margin-bottom:3px; margin-left:2px; margin-right:2px;',
                                id: 'b2bSaleRequestDetails',
                                items: [{
                                        layout: 'form',
                                        columnWidth: 0.20,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'textfield',
                                                fieldLabel: 'Name of Requester',
                                                style: 'text-align:right',
                                                id: 'b2bsr_requesterName',
                                                name: 'n[b2bsr_requesterName]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                tabIndex: 502,
                                                listeners: {
                                                    change: function () {

                                                    }
                                                }
                                            }]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.40,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'textfield',
                                                fieldLabel: 'Email & Contact Number',
                                                style: 'text-align:right',
                                                id: 'b2bsr_emailAndContactNo',
                                                name: 'n[b2bsr_emailAndContactNo]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                tabIndex: 503,
                                                listeners: {
                                                    change: function () {

                                                    }
                                                }
                                            }]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.14,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'combo',
                                                displayField: 'name',
                                                valueField: 'id',
                                                mode: 'local',
                                                id: 'b2bsr_requestedThrough',
                                                name: 'n[b2bsr_requestedThrough]',
                                                hiddenName: 'n[b2bsr_requestedThrough]',
                                                forceSelection: true,
                                                fieldLabel: 'Sales Request By',
                                                emptyText: 'Please Select',
                                                anchor: '98%',
                                                typeAhead: true,
                                                triggerAction: 'all',
                                                lazyRender: true,
                                                editable: true,
                                                minChars: 2,
                                                tabIndex: 504,
                                                store: new Ext.data.JsonStore({
                                                    fields: ['id', 'name'],
                                                    data: [{id: 'Telephone Call', name: 'Telephone Call'}, {id: 'E-mail', name: 'E-mail'}, {id: 'WebForm', name: 'WebForm'}]
                                                }), listeners: {
                                                    select: function () {

                                                    }
                                                }
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        columnWidth: 0.12,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'datefield',
                                                fieldLabel: 'Request Date',
                                                id: 'b2bsr_requestDate',
                                                name: 'n[b2bsr_requestDate]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                tabIndex: 505,
                                                listeners: {
                                                    change: function () {

                                                    }
                                                }
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        columnWidth: 0.12,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'timefield',
                                                fieldLabel: 'Request Time',
                                                increment: 5,
                                                id: 'b2bsr_requestTime',
                                                name: 'n[b2bsr_requestTime]',
                                                anchor: '98%',
                                                value: new Date().format("H:i A"),
                                                allowBlank: false,
                                                tabIndex: 506,
                                                listeners: {
                                                    change: function () {

                                                    }
                                                }
                                            }]
                                    }
                                ]

                            }]
                    },
                    {
                        layout: 'column',
                        bodyStyle: {
                            "padding": "5px 5px 5px 3px"
                        },
                        style: 'margin-bottom:3px;',
                        id: 'itemImputPanel',
                        columnWidth: 1,
                        items: [{
                                layout: 'column',
                                style: 'margin-bottom:3px;',
                                columnWidth: 1,
                                items: [
                                    {
                                        layout: 'form',
                                        columnWidth: 0.60,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'combo',
                                                fieldLabel: 'Items',
                                                id: 'b2bSRCustomerItems',
                                                name: 'b2bSRCustomerItems',
                                                labelStyle: mandatory_label,
                                                allowBlank: true,
                                                labelAlign: 'top',
                                                mode: 'remote',
                                                allowBlank: false,
                                                        typeAhead: true,
                                                forceSelection: true,
                                                preventMark: true,
                                                editable: true,
                                                anchor: '97%',
                                                store: b2bCustomerItemStore,
                                                triggerAction: 'all',
                                                minChars: 3,
                                                displayField: 'stit_SKU',
                                                valueField: 'stit_ID',
                                                hiddenName: 'b2bSRCustomerItems',
                                                tabIndex: 507,
                                                listeners: {
                                                    select: function () {

                                                    }
                                                }
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        columnWidth: 0.15,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'numberfield',
                                                fieldLabel: 'Quantity',
                                                style: 'text-align:right',
                                                id: 'b2bsr_itemqty',
                                                preventMark: true,
                                                allowNegative: false,
                                                allowDecimal: false,
                                                name: 'n[b2bso_itemqty]',
                                                anchor: '98%',
                                                allowBlank: false,
                                                tabIndex: 508,
                                                listeners: {
                                                    change: function () {

                                                    }
                                                }
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        columnWidth: 0.15,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'combo',
                                                displayField: 'name',
                                                valueField: 'id',
                                                mode: 'local',
                                                id: 'b2bsr_itemPkg',
                                                forceSelection: true,
                                                preventMark: true,
                                                fieldLabel: 'Pack Type',
                                                emptyText: 'Package Type',
                                                anchor: '98%',
                                                allowBlank: false,
                                                typeAhead: true,
                                                triggerAction: 'all',
                                                lazyRender: true,
                                                editable: true,
                                                minChars: 2,
                                                tabIndex: 509,
                                                store: new Ext.data.JsonStore({
                                                    fields: ['id', 'name'],
                                                    data: [{id: 'Box', name: 'Box'}, {id: 'Carton', name: 'Carton'}, {id: 'Item', name: 'Item'}]
                                                }), listeners: {
                                                    select: function () {

                                                    }
                                                }
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        labelAlign: 'top',
                                        columnWidth: 0.10,
                                        items: [{
                                                xtype: 'button',
                                                text: 'Add',
                                                width: '20px',
                                                tabIndex: 510,
                                                id: 'addItemsToSR',
                                                iconCls: 'finascop_add',
                                                style: 'margin-top:19px; margin-left:3px;',
                                                handler: function () {
                                                    if (!Ext.getCmp('b2bSRCustomerItems').isValid(true)) {
                                                        Ext.MessageBox.alert("Alert", "Please enter Item.");
                                                    } else
                                                    if (Ext.getCmp('b2bsr_itemqty').getValue() <= 0) {
                                                        Ext.MessageBox.alert("Alert", "Please enter valid quatity.");
                                                    } else

                                                    if (!Ext.getCmp('b2bsr_itemPkg').isValid(true)) {
                                                        Ext.MessageBox.alert("Alert", "Please enter Package type.");
                                                    } else {
                                                        Ext.Ajax.request({
                                                            waitMsg: 'Processing',
                                                            method: 'POST',
                                                            url: modURL + '&op=saveB2BSRItemDetails',
                                                            params: {
                                                                b2bsr_UniqueID: Application.RetalineSales.Cache.b2bsr_UniqueID,
                                                                b2bsr_itemid: Ext.getCmp('b2bSRCustomerItems').getValue(),
                                                                b2bsr_itemname: Ext.getCmp('b2bSRCustomerItems').getRawValue(),
                                                                b2bsr_itemqty: Ext.getCmp('b2bsr_itemqty').getValue(),
                                                                b2bsr_itemPkg: Ext.getCmp('b2bsr_itemPkg').getValue()
                                                            },
                                                            success: function (response) {
                                                                var tmp = Ext.decode(response.responseText);
                                                                if (tmp.success === true) {
                                                                    Application.example.msg('Success', tmp.msg);
                                                                    Ext.getCmp('b2bsr_ItemGrid').store.load({
                                                                        params: {
                                                                            b2bsr_UniqueID: Application.RetalineSales.Cache.b2bsr_UniqueID
                                                                        },
                                                                        callback: function () {
                                                                            //Ext.getCmp('b2bSRCustomerItems').focus(false, 100);
                                                                            Ext.getCmp('b2bSRCustomerItems').reset();
                                                                            Ext.getCmp('b2bsr_itemqty').reset();
                                                                            Ext.getCmp('b2bsr_itemPkg').reset();
                                                                        }
                                                                    });
                                                                    Ext.getCmp('b2bsr_ItemGrid').getStore().load({
                                                                        params: {
                                                                            b2bsr_UniqueID: Application.RetalineSales.Cache.b2bsr_UniqueID
                                                                        }
                                                                    });

                                                                } else {
                                                                    Ext.MessageBox.alert("Failure", "Error moving Data");
                                                                }
                                                            },
                                                            failure: function (response) {
                                                                Ext.MessageBox.alert('Error', response.responseText);
                                                            }
                                                        });
                                                    }
                                                }
                                            }]
                                    }]
                            }]
                    },
                    {
                        xtype: 'fieldset',
                        title: 'b2b Sales Request Items',
                        columnWidth: .992,
                        style: 'margin-bottom:3px; margin-left:2px; margin-right:2px;',
                        id: 'b2b_so_gridItems',
                        items: [{
                                layout: 'column',
                                items: [{
                                        layout: 'form',
                                        columnWidth: 1,
                                        items: [
                                            newSRItemGrid()
                                        ]
                                    }]
                            }]
                    }
                ]
            });
            return panel;
        };

        var createB2BSalesRequest = function (bbsr_id) {
            var B2BSalesRequestForm = createB2BSalesRequestForm(bbsr_id);
            var b2bSRrderWin = new Ext.Window({
                id: "b2bSalesRequestWinID",
                title: 'Sales Request',
                shadow: false,
                height: 520,
                width: 850,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                listeners: {
                    close: function (panel) {
                        if (Application.RetalineSales.Cache.b2bsr_UniqueID != '')
                        {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            Ext.Ajax.request({
                                url: modURL + '&op=cancelTempB2BSR',
                                params: {
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp,
                                    b2bsr_UniqueID: Application.RetalineSales.Cache.b2bsr_UniqueID
                                },
                                method: 'POST',
                                success: function (res) {

                                },
                                failure: function (response) {
                                    Ext.MessageBox.alert('Error', response.responseText);
                                }
                            });
                        }
                        Application.RetalineSales.Cache.b2bsr_UniqueID = '';
                    }
                },
                items: [B2BSalesRequestForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 523,
                        handler: function () {
                            Ext.getCmp('b2bSalesRequestWinID').close();
                        }
                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Submit',
                        id: 'b2bsrSubmitBtn',
                        tabIndex: 522,
                        handler: function () {
                            var store = Ext.getCmp('b2bsr_ItemGrid').getStore();
                            if (store.getCount()) {
                                saveB2BSalesRequest();
                            } else {
                                Ext.MessageBox.alert("Notification", 'Please fill B2B Sales Request details.');
                            }
                        }
                    }]
            });

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=generateUniqueID',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    var uid = tmp.uid;
                    Application.RetalineSales.Cache.b2bsr_UniqueID = uid;
                },
                failure: function (response) {
                    Ext.MessageBox.alert('Error', response.responseText);
                }
            });


            b2bSRrderWin.doLayout();
            b2bSRrderWin.show();
            b2bSRrderWin.center();
            //Ext.getCmp('b2bSRCustomer').focus(false, 100);
        };

        var B2BSalesRequestGridStore = function () {
            var _catStore = new Ext.data.GroupingStore({
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listB2BSalesRequests',
                    method: 'post'
                }),
                reader: new Ext.data.JsonReader({
                    totalProperty: 'totalCount',
                    root: 'data'
                }, ['bbsr_id', 'bbsr_SRNumber', 'bbsr_SRUpdatedOn', 'b2b_Customer_ID', 'b2b_Customer_Name', 'bbsr_Active']),
                sortInfo: {
                    field: 'bbsr_id',
                    direction: "DESC"
                },
                groupField: 'b2b_Customer_Name',
                groupDir: 'ASC',
                remoteSort: true,
                autoLoad: true,
                root: 'data',
                listeners: {
                    load: function () {

                    }
                }
            });

            return _catStore;
        };

        var B2B_SalesRequestListGrid = function () {

            var _b2bSR_GridFilter = new Ext.ux.grid.GridFilters({
                filters: [{
                        type: 'string',
                        dataIndex: 'bbsr_SRNumber'
                    }]
            });
            _b2bSR_GridFilter.remote = true;
            _b2bSR_GridFilter.autoReload = true;

            var _gridStore = B2BSalesRequestGridStore();

            var _gridPanel = new Ext.grid.GridPanel({
                id: 'B2BSalesRequestGrid',
                region: 'center',
                layout: 'fit',
                frame: false,
                border: false,
                iconCls: 'my-icon444',
                store: _gridStore,
                loadMask: true,
                sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true
                }),
                view: new Ext.grid.GroupingView({
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                    groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
                }),
                tbar: [{
                        text: 'Create B2B Sales Request',
                        tooltip: 'Create B2B Sales Request',
                        icon: './resources/images/submenuicons/add.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            createB2BSalesRequest();
                        }
                    }],
                plugins: [new Ext.ux.grid.GroupSummary(), _b2bSR_GridFilter],
                columns: [
                    {
                        header: 'B2B Sales Request No.',
                        sortable: true,
                        dataIndex: 'bbsr_SRNumber',
                        tooltip: 'B2B Sales Request Number',
                        hideable: false
                    },
                    {
                        header: 'Date and Time',
                        sortable: true,
                        dataIndex: 'bbsr_SRUpdatedOn',
                        tooltip: 'B2B Sales Request Date & Time'
                    },
                    {
                        header: 'Customer',
                        sortable: true,
                        dataIndex: 'b2b_Customer_Name',
                        tooltip: 'Vendor',
                        hideable: false
                    },
                    {
                        header: 'B2B SO Status',
                        sortable: true,
                        dataIndex: 'bbsr_Active',
                        tooltip: 'B2B Sales Order Status',
                        hideable: false
                    }, {
                        xtype: 'actioncolumn',
                        hideable: false,
                        sortable: false,
                        items: [{
                                iconCls: 'application_view_detail',
                                tooltip: 'View',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    createB2BSalesRequest(record.get('bbsr_id'));
                                }
                            },
                            {
                                tooltip: 'Show Customer\'s Active Sales Requests.',
                                iconCls: 'show_linked_details',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    showAllActiveCustomerSR(record.get('b2b_Customer_ID'), record.get('b2b_Customer_Name'));
                                }
                            }]
                    }
                ],
                viewConfig: {
                    forceFit: true
                },
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: _gridStore,
                    displayInfo: true,
                    displayMsg: 'Displaying records {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                stripeRows: true,
                listeners: {
                    cellClick: function (grid, rowIndex, columnIndex, e) {
                        var record = grid.getStore().getAt(rowIndex);
                        var ID = record.get('SR_id');
                        if (!Ext.isEmpty(ID)) {

                        }
                    }

                }

            });
            return _gridPanel;
        };

        var createB2BSalesRequestGrid = function (id) {
            var panel = new Ext.Panel({
                layout: 'border',
                bodyStyle: {"background-color": "white"},
                border: false,
                frame: false,
                id: id,
                title: 'Sales Request',
                iconCls: 'b2bicon24x24',
                items: [B2B_SalesRequestListGrid()]
            });
            return panel;
        };

    }
//B2B Sales Request END
    var viewSalesRequestItemGrid = function (bbsr_id) {
        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listB2BSOItemsView',
                    method: 'post'
                }),
                fields: ['bbso_id', 'bbdc_id', 'b2bso_itemid', 'b2bso_itemname', 'b2bso_itemmrp', 'b2bso_itemqty', 'b2bso_itemrate', 'b2bso_itemoffrqty', 'b2bso_itemaddidisc', 'b2bso_shippingcharge',
                    'b2bso_gst', 'b2bso_itemPkg', {name: 'isStockEnough', type: 'int'}, 'b2bso_gendiscount', 'b2bso_effectiverate', 'itemDisc',
                    {name: 'b2bso_amount', type: 'float'}, 'b2bso_discountpercent', {name: 'b2bso_discountamt', type: 'float'},
                    {name: 'b2bso_netamount', type: 'float'}],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.bbsr_id = bbsr_id;
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
            id: 'salesRequestItemGridView',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bso_itemname',
                    tooltip: 'Item',
                    width: 130

                }, {
                    header: 'MRP (Inc. of GST)',
                    sortable: true,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'b2bso_itemmrp',
                    align: 'right',
                    tooltip: 'MRP (Inc. of GST)',
                    width: 60
                }, {
                    header: 'Rate',
                    sortable: true,
                    dataIndex: 'b2bso_itemrate',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    tooltip: 'Rate',
                    align: 'right',
                    width: 60
                }, {
                    header: 'Qty',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bso_itemqty',
                    tooltip: 'Qty',
                    align: 'right',
                    width: 50
                }, {
                    header: 'Amount',
                    sortable: true,
                    hideable: false,
                    xtype: 'finascopcurrency',
                    dataIndex: 'b2bso_amount',
                    format: FINASCOP_CURRENCY_FORMAT,
                    tooltip: 'Amount',
                    align: 'right',
                    width: 60
                }, {
                    header: 'Product Dis.',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bso_itemoffrqty',
                    tooltip: 'Product Discount',
                    align: 'right',
                    width: 50
                },
                {
                    header: 'Item Discount',
                    sortable: true,
                    dataIndex: 'itemDisc',
                    align: 'right',
                    tooltip: 'Item Discount',
                    width: 50
                },
                {
                    header: 'Handling Chrg.',
                    sortable: true,
                    dataIndex: 'b2bso_shippingcharge',
                    tooltip: 'Shipping / Handling Charge',
                    align: 'right',
                    width: 50
                }, {
                    header: 'General Dis.',
                    sortable: true,
                    dataIndex: 'b2bso_gendiscount',
                    tooltip: 'General Discount',
                    align: 'right',
                    width: 50
                },
                {
                    header: 'Net Amt',
                    sortable: true,
                    dataIndex: 'b2bso_netamount',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Net Amt',
                    width: 60
                },
                {
                    header: 'ESR',
                    sortable: true,
                    dataIndex: 'b2bso_effectiverate',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'ESR',
                    width: 60
                }],
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
    var viewB2BSalesRequestForm = function (bbso_id) {
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
                                    fieldLabel: 'Customer',
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
                                    fieldLabel: 'SR Number',
                                    id: 'vpo_number',
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
                                    fieldLabel: 'SR Date',
                                    id: 'vpo_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    anchor: '98%',
                                    tabIndex: 2
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 53,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'SR Value',
                                    id: 'poValue',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 53,
                            items: [
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
                                        viewSalesRequestItemGrid(bbso_id)
                                    ]
                                }]
                        }]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .65,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: .10,
                            labelWidth: 35,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    style: {'text-align': 'right'},
                                    fieldLabel: 'Total',
                                    id: 'po_poValue',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 511,
                                    readOnly: true,
                                    anchor: '99%'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        }, ]
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
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            labelWidth: 60,
                            items: [{
                                    fieldLabel: 'Credit Days',
                                    xtype: 'displayfield',
                                    id: 'payment_value',
                                    labelAlign: 'left',
                                    hidden: true,
                                    anchor: '98%',
                                    tabIndex: 513
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            hideBorders: true,
                            labelAlign: 'left',
                            labelWidth: 65,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Validity',
                                    id: 'povalidityType',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'left',
                            hideBorders: true,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'General Discounts',
                                    id: 'pogeneralDiscount',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'left',
                            hideBorders: true,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Handling Charges',
                                    id: 'bbsr_shippingcharge',
                                    style: {'font-weight': 'bold'},
                                    anchor: '98%',
                                    tabIndex: 3
                                }]
                        }
                    ]
                }]
        });
        return panel;
    };
    return {
        Cache: {
            b2bso_UniqueID: '',
            salesRequestIDs: []
        },
        init: function () {
            var _rtlnSalesB2BCustomerPanelId = 'gridpanelSalesB2BCustomer';
            var _rtlnSalesB2BCustomerPane = Ext.getCmp(_rtlnSalesB2BCustomerPanelId);
            if (Ext.isEmpty(_rtlnSalesB2BCustomerPane)) {
                _rtlnSalesB2BCustomerPane = createB2BCustomerGrid(_rtlnSalesB2BCustomerPanelId);
                Application.UI.addTab(_rtlnSalesB2BCustomerPane);
                _rtlnSalesB2BCustomerPane.doLayout();
            } else {
                Application.UI.addTab(_rtlnSalesB2BCustomerPane);
                _rtlnSalesB2BCustomerPane.doLayout();
            }
        },
        initSalesOrder: function () {
            var _rtlnSalesB2BSalesOrderPanelId = 'gridpanelB2BSalesOrder';
            var _rtlnSalesB2BSalesOrderPane = Ext.getCmp(_rtlnSalesB2BSalesOrderPanelId);
            if (Ext.isEmpty(_rtlnSalesB2BSalesOrderPane)) {
                _rtlnSalesB2BSalesOrderPane = createB2BSalesOrderGrid(_rtlnSalesB2BSalesOrderPanelId);
                Application.UI.addTab(_rtlnSalesB2BSalesOrderPane);
                _rtlnSalesB2BSalesOrderPane.doLayout();
            } else {
                Application.UI.addTab(_rtlnSalesB2BSalesOrderPane);
                _rtlnSalesB2BSalesOrderPane.doLayout();
            }
        },
        initSalesRequest: function () {
            var _rtlnSalesB2BSalesRequestPanelId = 'gridpanelB2BSalesRequest';
            var _rtlnSalesB2BSalesRequestPane = Ext.getCmp(_rtlnSalesB2BSalesRequestPanelId);
            if (Ext.isEmpty(_rtlnSalesB2BSalesRequestPane)) {
                _rtlnSalesB2BSalesRequestPane = createB2BSalesRequestGrid(_rtlnSalesB2BSalesRequestPanelId);
                Application.UI.addTab(_rtlnSalesB2BSalesRequestPane);
                _rtlnSalesB2BSalesRequestPane.doLayout();
            } else {
                Application.UI.addTab(_rtlnSalesB2BSalesRequestPane);
                _rtlnSalesB2BSalesRequestPane.doLayout();
            }
        }, viewSODetails: function (bbso_id) {
            var vendornewPOForm = viewB2BSalesRequestForm(bbso_id);
            var poViewWindow = new Ext.Window({
                id: "windowFinascopStocknewPoView",
                title: 'Sales order',
                //iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1200,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: false,
                closable: false,
                items: [vendornewPOForm],
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
                url: modURL + '&op=getSODetails',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    bbso_id: bbso_id

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);

                    console.log(tmp);

                    Ext.getCmp('vendor_name').setValue(tmp.b2b_Customer_Name);
                    Ext.getCmp('vpo_number').setValue(tmp.bbso_SONumber);
                    Ext.getCmp('vpo_date').setValue(tmp.bbso_SODate);
                    Ext.getCmp('poValue').setValue(tmp.bbso_SOValue);
                    Ext.getCmp('po_poValue').setValue(tmp.bbso_SOValue);
                    Ext.getCmp('po_payment_terms').setValue(tmp.bbso_paymentTerms);
                    Ext.getCmp('payment_value').setValue(tmp.bbso_paymentValue);
                    Ext.getCmp('povalidityType').setValue(tmp.bbso_validityType);
                    Ext.getCmp('pogeneralDiscount').setValue(tmp.bbso_gdiscpercent);
                    Ext.getCmp('bbsr_shippingcharge').setValue(tmp.bbso_HandlingCharges);
                    Ext.getCmp('salesRequestItemGridView').getStore().load({
                        params: {
                            bbso_id: bbso_id
                        }
                    });
                }
            });
            poViewWindow.doLayout();
            poViewWindow.show();
            poViewWindow.center();
        },
    };
}();