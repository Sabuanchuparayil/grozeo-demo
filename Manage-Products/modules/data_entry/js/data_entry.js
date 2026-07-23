Application.DataEntry = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 12;

    var modURL = '?module=data_entry';

    function updatePagination(cmp) {
        recs_per_page = update_recs_per_page(cmp);
    }
    var gridSelectionChangedcategory = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelforGBHistory').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelforGBHistory').getSelectionModel().getSelections()[0].data.deli_id;
            Application.DataEntry.daViewMode(ID);
        }
    };

    var data_entryStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCustomers',
                method: 'post'
            }),
            fields: ['cust_id', 'cust_customer_name', 'cust_mobile', 'cust_email', 'cust_walletbalance', 'cust_prom_reward_point', 'cust_status', 'cust_ref_code', 'cust_branch_id',
                'cust_mobile', 'cust_customer_id', 'customer_pin',
                'cust_status', 'customer_district', 'customer_state', 'enabled_delivery'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: true
        });
        store.setDefaultSort('cust_customer_name', 'ASC');
        return store;
    };

    var data_entryGrid = function (id) {
        var data_entry_store = data_entryStore();
        var data_entry_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'cust_customer_name'
                }, {
                    type: 'string',
                    dataIndex: 'cust_mobile'
                }, {
                    type: 'string',
                    dataIndex: 'cust_email'
                }, {
                    type: 'string',
                    dataIndex: 'cust_ref_code'
                }, {
                    type: 'string',
                    dataIndex: 'customer_pin'
                }, {
                    type: 'string',
                    dataIndex: 'customer_state'
                }, {
                    type: 'string',
                    dataIndex: 'cust_walletbalance'
                }, {
                    type: 'string',
                    dataIndex: 'cust_prom_reward_point'
                }, {
                    type: 'string',
                    dataIndex: 'cust_status'
                }]

        });
        data_entry_filter.remote = true;
        data_entry_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: data_entry_store,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Customers',
            iconCls: 'customer',
            plugins: [data_entry_filter],
            id: 'customer_grid_id',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    id: 'cust_name_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'cust_customer_name',
                    renderer: updateToolTip
                },
                {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'cust_mobile',
                    renderer: updateToolTip
                }, {
                    header: 'Email',
                    sortable: true,
                    dataIndex: 'cust_email',
                    renderer: updateToolTip
                }, {
                    header: 'Customer Code',
                    sortable: true,
                    dataIndex: 'cust_ref_code',
                    renderer: updateToolTip
                }, {
                    header: 'Reward Point',
                    sortable: true,
                    dataIndex: 'cust_prom_reward_point',
                    renderer: updateToolTip
                }, {
                    header: 'Wallet Balance',
                    sortable: true,
                    dataIndex: 'cust_walletbalance',
                    renderer: updateToolTip
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'cust_status',
                    renderer: updateToolTip
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
                            b2cActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 header: 'Action',
                 hideable: false,
                 sortable: false,
                 groupable: false,
                 tooltip: 'Action',
                 items: [{
                 iconCls: 'list_users',
                 tooltip: 'View Deliver Address',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 
                 Application.DataEntry.viewCustomerDeliverys(record.get('cust_id'));
                 }
                 }]
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
                resize: updatePagination
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Add New',
                    hidden: true,
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon20',
                    handler: function () {
                        data_entryDetails(0);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: data_entry_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [data_entry_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'cust_name_auto_exp'
        });
        return grid_panel;
    };
    var b2cActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View Delivery Address",
                handler: function () {
                    var cust_id = Ext.getCmp('customer_grid_id').getSelectionModel().getSelections()[0].data.cust_id;
                    Application.DataEntry.viewCustomerDeliverys(cust_id);
                }
            }, {
                text: 'View Wallet History',
                handler: function (grid, rowIndex, colIndex) {
                    var cust_id = Ext.getCmp('customer_grid_id').getSelectionModel().getSelections()[0].data.cust_id;
                    var cust_customer_name = Ext.getCmp('customer_grid_id').getSelectionModel().getSelections()[0].data.cust_customer_name;
                    var cust_walletbalance = Ext.getCmp('customer_grid_id').getSelectionModel().getSelections()[0].data.cust_walletbalance;
                    var cust_mobile = Ext.getCmp('customer_grid_id').getSelectionModel().getSelections()[0].data.cust_mobile;
                    Application.DataEntry.viewWalletHistory(cust_id, cust_customer_name, cust_walletbalance, cust_mobile);
                },
            }]
    });

    var changeCustomerStatus = function (rec) {

        Ext.Ajax.request({
            waitMsg: 'Processing',
            url: modURL,
            params: {
                op: 'changeStatus',
                cust_id: rec.get('cust_id'),
                status: rec.get('status')
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', 'Action Failed');
            },
            success: function (res) {
                var tmp = Ext.decode(res.responseText);
                if (tmp.success === true && tmp.valid === true) {
                    Ext.getCmp('customer_grid_id').getStore().load();
                    Ext.MessageBox.alert('Success', 'Status Changed successfully.');
                }
            }
        });
    };

    var updateToolTip = function (value, metadata, record) {
        if (record.get('enabled_delivery') == "0")
            metadata.attr = 'ext:qtip="Pin code of this customer is not enabled for delivery."';
        return value;
    };

    var enableForm = function () {
        Ext.getCmp('cust_customer_name').enable();
        Ext.getCmp('customer_email').enable();
        Ext.getCmp('customer_phone').enable();
        Ext.getCmp('customer_address').enable();
        Ext.getCmp('customer_sec_address').enable();
        Ext.getCmp('customer_pin').enable();
        Ext.getCmp('cust_customer_name').focus(false, 1000);

    };

    var resetForm = function () {
        Ext.getCmp('data_entry_details_form').getForm().reset();
        Ext.getCmp('cust_customer_name').disable();
        Ext.getCmp('customer_email').disable();
        Ext.getCmp('customer_phone').disable();
        Ext.getCmp('customer_address').disable();
        Ext.getCmp('customer_sec_address').disable();
        Ext.getCmp('customer_pin').disable();

        Ext.getCmp('cust_mobile').fireEvent('afterrender', Ext.getCmp('cust_mobile'));
    };

    var data_entryForm = function () {
        var form = new Ext.FormPanel({
            id: 'data_entry_details_form',
            labelAlign: 'top',
            autoHeight: true,
            url: modURL + "&op=saveDataEntry",
            frame: true,
            items: [{
                    layout: 'column',
                    items: [{
                            columnWidth: .5,
                            style: 'margin-left:5px',
                            layout: 'form',
                            items: [{
                                    xtype: 'textfield',
                                    id: 'cust_mobile',
                                    name: 'cust_mobile',
                                    allowBlank: false,
                                    fieldLabel: 'Mobile',
                                    regex: /^(\+\d{1,3}[\s]?)?\d{10}$/,
                                    disabled: false,
                                    tabIndex: 1,
                                    anchor: '97%',
                                    listeners: {
                                        afterrender: function (field) {
                                            field.focus(false, 1000);
                                        },
                                        change: function (t, n, o) {
                                            if (t.isValid()) {
                                                if (!Ext.isEmpty(n) && n != o)
                                                    Ext.Ajax.request({
                                                        waitMsg: 'Please wait...',
                                                        url: modURL,
                                                        params: {
                                                            op: 'checkMobile',
                                                            mobile: t.getValue(),
                                                            customer_id: Ext.getCmp('cust_id').getValue()
                                                        },
                                                        failure: function (response, options) {
                                                        },
                                                        success: function (response, options) {
                                                            if (response.responseText != "") {
                                                                eval('var tmp=' + response.responseText);
                                                                if (tmp.success !== undefined && tmp.success === true && tmp.valid === true) {
                                                                    enableForm();
                                                                } else {
                                                                    Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
                                                                        if (btn == 'ok') {
                                                                            resetForm();
                                                                        }
                                                                    });
                                                                }
                                                            }
                                                        }
                                                    });
                                            } else {
                                                resetForm();
                                            }
                                        }
                                    }

                                },
                                {
                                    xtype: 'textfield',
                                    id: 'customer_email',
                                    name: 'customer_email',
                                    allowBlank: true,
                                    fieldLabel: 'Email',
                                    vtype: 'email',
                                    maxLength: 100,
                                    tabIndex: 3,
                                    anchor: '97%'
                                },
                                {
                                    xtype: 'textfield',
                                    id: 'customer_pin',
                                    name: 'customer_pin',
                                    allowBlank: false,
                                    fieldLabel: 'PIN',
                                    maxLength: 6,
                                    tabIndex: 5,
                                    anchor: '97%',
                                    listeners: {
                                        change: function (t, n, o) {
                                            if (!Ext.isEmpty(n) && n != o) {
                                                Ext.Ajax.request({
                                                    waitMsg: 'Please wait...',
                                                    url: modURL,
                                                    params: {
                                                        op: 'pickData',
                                                        pin: t.getValue()
                                                    },
                                                    failure: function (response, options) {
                                                    },
                                                    success: function (response, options) {
                                                        if (response.responseText != "") {
                                                            eval('var tmp=' + response.responseText);
                                                            if (tmp.success !== undefined && tmp.success === true && tmp.valid === true) {
                                                                Ext.getCmp('customer_state').setValue(tmp.state);
                                                                Ext.getCmp('customer_district').setValue(tmp.district);
                                                            } else {
                                                                Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
                                                                    if (btn == 'ok') {
                                                                        t.reset();
                                                                    }
                                                                });
                                                            }
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }
                                },
                                {
                                    xtype: 'textfield',
                                    id: 'customer_district',
                                    name: 'customer_district',
                                    allowBlank: false,
                                    fieldLabel: 'District',
                                    maxLength: 100,
                                    anchor: '97%',
                                    tabIndex: 7
                                },
                                {
                                    xtype: 'textfield',
                                    id: 'customer_address',
                                    name: 'customer_address',
                                    allowBlank: false,
                                    fieldLabel: 'Address 2',
                                    xtype: 'textfield',
                                            maxLength: 500,
                                    tabIndex: 9,
                                    anchor: '97%'
                                }

                            ]

                        }, {
                            columnWidth: .5,
                            layout: 'form',
                            items: [
                                {
                                    xtype: 'hidden',
                                    id: 'cust_id',
                                    name: 'cust_id'
                                }, {
                                    xtype: 'textfield',
                                    id: 'cust_customer_name',
                                    name: 'cust_customer_name',
                                    allowBlank: false,
                                    fieldLabel: 'Name',
                                    maxLength: 100,
                                    tabIndex: 2,
                                    anchor: '97%'
                                },
                                {
                                    xtype: 'textfield',
                                    id: 'customer_phone',
                                    name: 'customer_phone',
                                    allowBlank: true,
                                    fieldLabel: 'Phone',
                                    maxLength: 20,
                                    maskRe: /[0-9+_()/]+$/,
                                    tabIndex: 4,
                                    anchor: '97%'
                                },
                                {
                                    xtype: 'textfield',
                                    id: 'customer_state',
                                    name: 'customer_state',
                                    allowBlank: false,
                                    fieldLabel: 'State',
                                    maxLength: 100,
                                    anchor: '97%',
                                    tabIndex: 6

                                },
                                {
                                    xtype: 'textfield',
                                    id: 'customer_sec_address',
                                    name: 'customer_sec_address',
                                    allowBlank: true,
                                    fieldLabel: 'Address 1',
                                    maxLength: 500,
                                    tabIndex: 8,
                                    anchor: '97%'

                                }]

                        }]
                }]
        });
        return form;
    };

    var data_entryDetails = function () {

        var win_id = "data_entry_details_window";

        var data_entry_details_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(data_entry_details_window)) {
            var cForm = data_entryForm();
            data_entry_details_window = new Ext.Window({
                id: win_id,
                title: 'Create New Entry',
                layout: 'fit',
                width: 600, autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                iconCls: '',
                resizable: false,
                items: cForm,
                buttons: [{
                        text: 'Save',
                        tabIndex: 10,
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            if (cForm.getForm().isValid()) {
                                Application.DataEntry.SaveDataEntry();
                            } else {
                                Ext.MessageBox.alert("Notification", "Please fill all mandatory fields");
                            }
                        }
                    }]
            });
        }

        data_entry_details_window.doLayout();
        data_entry_details_window.show(this);
        data_entry_details_window.center();
    };
    var _customerDeliverAddressGrid = function (customerId) {
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _gbHistoryStore(customerId),
            iconCls: 'money',
            autoScroll: true,
            width: winsize.width * 0.4,
            height: 300,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforGBHistory',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    dataIndex: 'deli_name',
                    sortable: true,
                    tooltip: 'Name',
                    hideable: false
                },
                {
                    header: 'Post Codes',
                    dataIndex: 'deli_delivery_pin',
                    sortable: true,
                    tooltip: 'Post Codes',
                    hideable: false
                }, {
                    header: 'House No.',
                    dataIndex: 'deli_house_no',
                    sortable: true,
                    tooltip: 'House No.',
                    hideable: false,
                    width: 150
                }, {
                    header: 'House Name',
                    dataIndex: 'deli_house_name',
                    sortable: true,
                    tooltip: 'House Name',
                    hideable: false
                }, {
                    header: 'Landmark',
                    dataIndex: 'deli_land_mark',
                    sortable: true,
                    tooltip: 'Landmark',
                    hideable: false
                }
            ],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('deli_is_primary') == 1)
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                }
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforGBHistory').getStore().load({
                        params: {
                            customerId: customerId
                        }
                    });
                }
            }
        });
        return _dispatchgridPanel;
    };
    var _gbHistoryStore = function (customerId) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listDeliveryAddress',
                method: 'post'
            }),
            fields: ['deli_name', 'deli_delivery_pin', 'deli_house_no', 'deli_house_name', 'deli_land_mark', 'deli_is_primary'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.customerId = customerId;
                }
            }
        });
        return _Store;
    };
var _walletHistoryStore = function (customerId) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listWalletHistory',
                method: 'post'
            }),
            fields: ['orderno', 'orderamount', 'orderinfo', 'date'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.customerId = customerId;
                }, load: function () {
                    Ext.getCmp('gridpanelforWalletHistory').getView().refresh();
                    Ext.getCmp('gridpanelforWalletHistory').getSelectionModel().selectRow(0);
                }
            }
        });
        return _Store;
    };
    var _customerWalletHistoryGrid = function (customerId) {
        var _dispatchgridPanel = new Ext.grid.GridPanel({
            layout: 'fit',
            region: 'center',
            frame: false,
            border: false,
            loadMask: false,
            store: _walletHistoryStore(customerId),
            iconCls: 'money',
            autoScroll: true,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforWalletHistory',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Order No',
                    dataIndex: 'orderno',
                    sortable: true,
                    tooltip: 'Order No',
                    hideable: false
                }, {
                    header: 'Info',
                    dataIndex: 'orderinfo',
                    sortable: true,
                    tooltip: 'Info',
                    hideable: false
                }, {
                    header: 'Entry Date',
                    dataIndex: 'date',
                    sortable: true,
                    tooltip: 'Created On',
                    hideable: false,
                    align: 'right'
                }, {
                    header: 'Amount',
                    dataIndex: 'orderamount',
                    sortable: true,
                    tooltip: 'Amount',
                    hideable: false,
                    align: 'right'
                }, {
                    header: '',
                    width: 25
                }
            ],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {

                }
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedcategory
                }
            }),
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforWalletHistory').getStore().load({
                        params: {
                            customerId: customerId
                        }
                    });
                }
            }
        });
        return _dispatchgridPanel;
    };
    return {
        initDataEntry: function () {
            var panelId = 'customer_main_panel';
            var data_entry_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(data_entry_panel)) {
                data_entry_panel = data_entryGrid(panelId);
                Application.UI.addTab(data_entry_panel);
                data_entry_panel.doLayout();
            } else {
                Application.UI.addTab(data_entry_panel);
            }
        },
        SaveDataEntry: function () {
            var cForm = Ext.getCmp('data_entry_details_form');
            cForm.getForm().submit({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                success: function (cForm, action) {
                    eval('var tmp=' + action.response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Ext.MessageBox.alert("Success", "Details saved successfully", function (btn) {
                            if (btn == 'ok') {
                                resetForm();
                                Ext.getCmp('data_entry_details_window').close();
                                Ext.getCmp('customer_main_panel').getStore().reload();
                            }
                        });
                    }
                },
                failure: function (cForm, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: (obj.error) ? obj.error : obj.errors.reason,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    } else {
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: MAN_FIELD,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    }
                }
            });
        }, viewCustomerDeliverys: function (customerId) {
            var _polledItemsWindow = new Ext.Window({
                title: 'Delivery Address',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 400,
                width: 900,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_customerDeliverAddressGrid(customerId)],
                fbar: []

            });
            _polledItemsWindow.doLayout();
            _polledItemsWindow.show();
            _polledItemsWindow.center();
        }, viewWalletHistory: function (customerId, customerName, balance, mobile) {
            var title = "Wallet Transaction of " + customerName + " " + mobile + " - Balance " + balance;
            var _polledItemsWindow = new Ext.Window({
                title: title,
                iconCls: 'dispatch',
                layout: 'border',
                height: winsize.height * 0.8,
                width: winsize.width * 0.8,
                resizable: false,
                draggable: true,
                closable: true,
                modal: true,
                bodyStyle: {"background-color": "white"},
                items: [_customerWalletHistoryGrid(customerId)],
                fbar: []

            });
            _polledItemsWindow.doLayout();
            _polledItemsWindow.show();
            _polledItemsWindow.center();
        }
    };

}();
