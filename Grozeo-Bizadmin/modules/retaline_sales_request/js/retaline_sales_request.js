Application.RetalineSalesRequest = function () {
//GLOBAL DECLRATIONS BEGIN
    {
        var winsize = Ext.getBody().getViewSize();
        var modURL = '?module=retaline_sales_request';
        var recs_per_page = 23;
        var myMask, totalComboCount;
        var branchStockUpdateIntervalID;

    }
    var createB2BSalesRequestGrid = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id,
            title: 'Wholesale Requests',
            //iconCls: 'b2bicon24x24',
            items: [B2B_SalesRequestListGrid()]
        });
        return panel;
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
            }, ['bbsr_id', 'bbsr_SRNumber', 'b2bsr_requestDate', 'b2b_Customer_ID', 'b2b_Customer_Name', 'bbsr_Active']),
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
                    text: 'Create Wholesale Request',
                    tooltip: 'Create Wholesale Request',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        b2bCustomerWindow();

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
                    dataIndex: 'b2bsr_requestDate',
                    tooltip: 'B2B Sales Request Date & Time'
                },
                {
                    header: 'Customer',
                    sortable: true,
                    dataIndex: 'b2b_Customer_Name',
                    tooltip: 'Customer',
                    hideable: false
                },
                {
                    header: 'B2B SO Status',
                    sortable: true,
                    dataIndex: 'bbsr_Active',
                    tooltip: 'B2B Sales Order Status',
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
                            salesrequestActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: 'application_view_detail',
                            tooltip: 'View',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Application.RetalineSalesRequest.viewSRDetails(record.get('bbsr_id'));
                            }
                        },
                        {
                            tooltip: 'Convert to Sales Order.',
                            iconCls: 'show_linked_details',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Application.RetalineSalesRequest.convertToSalesOrder(record.get('bbsr_id'));
                            }
                        }, {
                            tooltip: 'Delete',
                            iconCls: 'finascop_delete',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Ext.Msg.confirm('Notification', 'Do you really want to remove this Sales Request?', function (btn) {
                                    if (btn == 'yes') {
                                        Ext.Ajax.request({
                                            url: modURL + '&op=deleteB2BSalesRequest',
                                            method: 'POST',
                                            params: {
                                                srId: record.get('bbsr_id')
                                            },
                                            success: function (response) {
                                                var res = Ext.decode(response.responseText);
                                                if (res.success === true)
                                                {
                                                    Ext.MessageBox.alert('Success', 'Removed  Sales Request.', function (btn) {
                                                        recs_per_page = updateRecsPerPage(Ext.getCmp('B2BSalesRequestGrid'));
                                                        Ext.getCmp('B2BSalesRequestGrid').store.reload({
                                                            params: {
                                                                start: 0,
                                                                limit: recs_per_page
                                                            }
                                                        });


                                                    });
                                                } else
                                                {
                                                    Ext.MessageBox.alert('Failed');
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        }]
                }*/
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
    var salesrequestActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View",
                handler: function () {
                    var bbsr_id = Ext.getCmp('B2BSalesRequestGrid').getSelectionModel().getSelections()[0].data.bbsr_id;
                    Application.RetalinTransferRequest.viewTransferItems(bbsr_id);
                }
            },
            {
                text: "Convert to SO",
                handler: function () {
                    var bbsr_id = Ext.getCmp('B2BSalesRequestGrid').getSelectionModel().getSelections()[0].data.bbsr_id;
                    Application.RetalineSalesRequest.convertToSalesOrder(bbsr_id);
                }
            }, {
                text: "Delete",
                handler: function () {
                                var bbsr_id = Ext.getCmp('B2BSalesRequestGrid').getSelectionModel().getSelections()[0].data.bbsr_id;
                                Ext.Msg.confirm('Notification', 'Do you really want to remove this Sales Request?', function (btn) {
                                    if (btn == 'yes') {
                                        Ext.Ajax.request({
                                            url: modURL + '&op=deleteB2BSalesRequest',
                                            method: 'POST',
                                            params: {
                                                srId: bbsr_id
                                            },
                                            success: function (response) {
                                                var res = Ext.decode(response.responseText);
                                                if (res.success === true)
                                                {
                                                    Ext.MessageBox.alert('Success', 'Removed  Sales Request.', function (btn) {
                                                        recs_per_page = updateRecsPerPage(Ext.getCmp('B2BSalesRequestGrid'));
                                                        Ext.getCmp('B2BSalesRequestGrid').store.reload({
                                                            params: {
                                                                start: 0,
                                                                limit: recs_per_page
                                                            }
                                                        });


                                                    });
                                                } else
                                                {
                                                    Ext.MessageBox.alert('Failed');
                                                }
                                            }
                                        });
                                    }
                                });
                }
            }]
    });
    var createB2BSalesRequest = function (pe_party) {

        var B2BSalesRequestForm = createB2BSalesRequestForm();
        var b2bSRrderWin = new Ext.Window({
            id: "b2bSalesRequestWinID",
            title: 'Create Wholesale Request',
            shadow: false,
            height: 520,
            width: 1180,
            modal: true,
            layout: 'fit',
            autoHeight: true,
            resizable: true,
            closable: true,
            listeners: {
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
                        var store = Ext.getCmp('salesRequestItemGrid').getStore();
                        if (store.getCount()) {
                            saveB2BSalesRequest('add', 'new');
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
                Application.RetalineSalesRequest.Cache.b2bsr_UniqueID = uid;
                Ext.getCmp('b2bsr_Customerid').setValue(pe_party);
            },
            failure: function (response) {
                Ext.MessageBox.alert('Error', response.responseText);
            }
        });


        b2bSRrderWin.doLayout();
        b2bSRrderWin.show();
        b2bSRrderWin.center();
    };
    var b2bSalesCustomerStore = new Ext.data.JsonStore({
        fields: ['b2b_Customer_ID', 'b2b_Customer_Name'],
        url: modURL + '&op=getB2BBrCustomers',
        autoLoad: true,
        method: 'post',
        totalProperty: 'totalCount',
        root: 'data'
    });
    var b2bCustomerItemStore = new Ext.data.JsonStore({
        fields: ['stit_ID', 'stit_SKU'],
        url: modURL + '&op=getB2BCustomerItems',
        autoLoad: true,
        method: 'post',
        totalProperty: 'totalCount',
        root: 'data',
    });
    var viewB2BSalesRequestForm = function (bbsr_id) {
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
                                        viewSalesRequestItemGrid(bbsr_id)
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
    var createB2BSalesRequestForm = function (pe_party) {
        var purUni = '';
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'newPOFormForVendor',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            xtype: 'hidden', id: 'uniqueId', name: 'uniqueId'
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            items: []
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'left',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'B2B Customer',
                                    hideLabel: true,
                                    emptyText: 'Choose B2B Customer',
                                    id: 'b2bsr_Customerid',
                                    name: 'b2bsr_Customerid',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true,
                                    anchor: '97%',
                                    store: b2bSalesCustomerStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'b2b_Customer_Name',
                                    valueField: 'b2b_Customer_ID',
                                    hiddenName: 'b2bsr_Customerid',
                                    tabIndex: 902,
                                    listeners: {
                                        select: function () {
                                            var pe_party = Ext.getCmp('b2bsr_Customerid').getValue();

                                            Ext.Ajax.request({
                                                url: modURL + '&op=b2bSOCustomerDetails',
                                                method: 'POST',
                                                params: {b2bSOCustomer: pe_party},
                                                success: function (res) {
                                                    var tmp = Ext.decode(res.responseText);
                                                    Ext.getCmp('vendorDetails').show();

                                                    Ext.getCmp('b2bSRContactPerson').setValue(tmp.b2b_Customer_Incharge);
                                                    Ext.getCmp('b2bSRCustomerMobile').setValue(tmp.b2b_Customer_Mobile);
                                                    Ext.getCmp('b2b_Customer_gst').setValue(tmp.b2b_Customer_gst);
                                                    Ext.getCmp('b2b_Customer_dlno1').setValue(tmp.b2b_Customer_dlno1);
                                                    Ext.getCmp('b2b_Customer_dlno2').setValue(tmp.b2b_Customer_dlno2);

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
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'SR Number',
                                    id: 'po_number',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_number]',
                                    anchor: '97%',
                                    value: 'Not Genereated',
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
                                    id: 'po_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    value: 'Not Genereated',
                                    anchor: '97%',
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 65,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Ordered By',
                                    id: 'po_ordered_by',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_ordered_by]',
                                    anchor: '97%',
                                    value: _SESSION.FirstName,
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: 0.20,
                            labelAlign: 'left',
                            labelWidth: 50,
                            items: []
                        }]
                }, {
                    xtype: 'fieldset',
                    title: 'B2B Customer Details',
                    collapsible: true,
                    columnWidth: 1,
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    id: 'vendorDetails',
                    hidden: true,
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            labelAlign: 'top',
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
                            columnWidth: .20,
                            labelAlign: 'top',
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
                        },
                        {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'GST',
                                    id: 'b2b_Customer_gst',
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
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'DL1',
                                    id: 'b2b_Customer_dlno1',
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
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'DL2',
                                    id: 'b2b_Customer_dlno2',
                                    style: {
                                        'font-weight': 'bold'
                                    },
                                    anchor: '97%'
                                }]
                        }]
                }, {
                    xtype: 'fieldset',
                    layout: 'column',
                    collapsible: true,
                    title: 'Item Previous Rates',
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
                                    id: 'itemSmaalStockUnit',
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
                                    fieldLabel: 'Least SKU MRP',
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
                                    fieldLabel: 'Last Rate',
                                    id: 'lastPurchaseRate',
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
                                    fieldLabel: 'Lowest SP',
                                    id: 'itemLPP',
                                    hidden: true,
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
                                    fieldLabel: 'Item Count',
                                    id: 'itemCount',
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
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Min Qty',
                                    id: 'itemMinQty',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }]
                }, {
                    layout: 'column',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'column',
                            style: 'margin-bottom:3px;',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.25,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Items',
                                            id: 'b2bSalesRequestItems',
                                            name: 'b2bSalesRequestItems',
                                            labelStyle: mandatory_label,
                                            allowBlank: false,
                                            labelAlign: 'top',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: b2bCustomerItemStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'stit_SKU',
                                            valueField: 'stit_ID',
                                            hiddenName: 'b2bSalesRequestItems',
                                            tabIndex: 904,
                                            listeners: {
                                                select: function () {
                                                    var b2bSalesRequestItems = Ext.getCmp('b2bSalesRequestItems').getValue();
                                                    var b2bsr_Customerid = Ext.getCmp('b2bsr_Customerid').getValue();
                                                    Ext.Ajax.request({
                                                        url: modURL + '&op=itemHistory',
                                                        method: 'POST',
                                                        params: {b2bSalesRequestItems: b2bSalesRequestItems, b2bsr_Customerid: b2bsr_Customerid},
                                                        success: function (res) {
                                                            var tmp = Ext.decode(res.responseText);
console.log('tmp.minQty',tmp.minQty);
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
                                                            Ext.getCmp('itemCount').setValue(tmp.itemCount);
                                                            Ext.getCmp('itemMinQty').setValue(tmp.minQty);
                                                            //Ext.getCmp('itemUnit').setValue(tmp.csb_package_type_name);
                                                            Ext.getCmp('itemUnitForm').setValue(tmp.itemUnitForm);
                                                            Ext.getCmp('itemSmaalStockUnit').setValue(tmp.itemSmaalStockUnit);
                                                            Ext.getCmp('b2bso_itemmrp').setValue(tmp.skuMRP);
                                                            Ext.getCmp('lastPurchaseRate').setValue(tmp.lastPurchaseRate);
                                                            console.log('tmp.skuRate', tmp.skuRate);
                                                            console.log('tmp.stit_GST', tmp.stit_GST);

                                                            Ext.getCmp('fpot_itemoffrrate').setValue(tmp.skuRate);
                                                            var offerateET = tmp.skuRate * 100 / (100 + parseInt(tmp.stit_GST));
                                                            //if (tmp.stit_fixedB2BRates == 0) {
                                                                Ext.getCmp('fpot_itemoffrrateExcT').setValue(offerateET);
                                                            //} else {
                                                             //   Ext.getCmp('fpot_itemoffrrateExcT').setValue(tmp.skuRate);
                                                           // }


                                                            Application.RetalineSalesRequest.Cache.stit_GST = tmp.stit_GST;
                                                            Application.RetalineSalesRequest.Cache.itemCount = tmp.itemCount;

                                                        },
                                                        failure: function () {
                                                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                        }
                                                    });

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.09,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'MRP (Inc. of GST)',
                                            id: 'b2bso_itemmrp',
                                            align: 'right',
                                            name: 'n[b2bso_itemmrp]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true,
                                            tabIndex: 905,
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Rate',
                                            align: 'right',
                                            id: 'fpot_itemoffrrate',
                                            name: 'n[fpot_itemoffrrate]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true,
                                            tabIndex: 906,
                                            value: 0,
                                            listeners: {
                                                change: function (e) {
                                                    var b2bsr_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    var amount = b2bsr_itemqty * fpot_itemoffrrate;
                                                    Ext.getCmp('fpot_amount').setValue(amount);
                                                    console.log('e', e);
//                                            var taxRate = this.data.stit_GST;
//                                            var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
//                                            var fpot_itemoffrrateet = (parseFloat(fpot_itemoffrrate) * 100) / (100 + parseFloat(taxRate));
                                                    //                                            Ext.getCmp('fpot_itemoffrrateet').setValue(fpot_itemoffrrateet);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.08,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Rate (Excl.GST)',
                                            align: 'right',
                                            id: 'fpot_itemoffrrateExcT',
                                            name: 'n[fpot_itemoffrrateExcT]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 907,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Qty',
                                            align: 'right',
                                            id: 'b2bsr_itemqty',
                                            name: 'n[b2bsr_itemqty]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 908,
                                            listeners: {
                                                change: function () {
                                                    var b2bsr_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (b2bsr_itemqty <= Application.RetalineSalesRequest.Cache.itemCount) {
                                                        var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                        var amount = b2bsr_itemqty * fpot_itemoffrrate;
                                                        Ext.getCmp('fpot_amount').setValue(amount);
                                                    } else {
                                                        Ext.getCmp('b2bsr_itemqty').reset();

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
                                            fieldLabel: 'Amount',
                                            align: 'right',
                                            id: 'fpot_amount',
                                            name: 'n[fpot_amount]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            //fieldLabel: 'Product Discount (Nos)',
                                            fieldLabel: 'Product Dis.',
                                            align: 'right',
                                            id: 'b2bsr_itemoffrqty',
                                            name: 'n[b2bsr_itemoffrqty]',
                                            anchor: '98%',
                                            tabIndex: 909,
                                            value: 0,
                                            listeners: {
                                                change: function () {
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Unit',
                                            id: 'itemUnitForm',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 910,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            align: 'right',
                                            //fieldLabel: 'Addl Discounts (Rs)',
                                            fieldLabel: 'Item Discounts',
                                            id: 'fpot_itemaddidisc',
                                            name: 'n[fpot_itemaddidisc]',
                                            anchor: '98%',
                                            value: 0,
                                            tabIndex: 911,
                                            listeners: {
                                                change: function () {
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
                                            id: 'po_idiscountcalculs',
                                            forceSelection: true,
                                            fieldLabel: 'Amt /Percent',
                                            emptyText: 'Amount / Percentage',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 912,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'Amount', name: 'Amount'}, {id: 'Percentage', name: 'Percentage'}]
                                            }), listeners: {
                                                select: function () {
                                                    var fpot_netamount, fpot_netRate;
                                                    var po_idiscountcalculs = Ext.getCmp('po_idiscountcalculs').getRawValue();
                                                    var fpot_amount = Ext.getCmp('fpot_amount').getValue();
                                                    var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    var fpot_itemaddidisc = Ext.getCmp('fpot_itemaddidisc').getValue();
                                                    var fpot_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (po_idiscountcalculs == 'Amount') {
                                                        fpot_netRate = fpot_itemoffrrateExcT - parseFloat(fpot_itemaddidisc);
                                                    } else {
                                                        fpot_netRate = parseFloat(fpot_itemoffrrateExcT) - (parseFloat(fpot_itemoffrrateExcT) * parseFloat(fpot_itemaddidisc) / 100);
                                                    }
                                                    fpot_netRate = Math.round((fpot_netRate + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netRate', fpot_netRate);
                                                    fpot_netamount = fpot_netRate * fpot_itemqty;
                                                    console.log('fpot_netamount1', fpot_netamount);
                                                    //fpot_netamount = fpot_netamount + (fpot_netamount * Application.RetalineSalesRequest.Cache.stit_GST / 100);
                                                    console.log('fpot_netamount2', fpot_netamount);
                                                    fpot_netamount = Math.round((fpot_netamount + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netamount3', fpot_netamount);
                                                    Ext.getCmp('fpot_netamount').setValue(fpot_netamount);

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.10,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Net Amt',
                                            id: 'fpot_netamount',
                                            align: 'right',
                                            name: 'n[fpot_netamount]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 913,
                                            readOnly: true,
                                            listeners: {
                                                focus: function () {
                                                    var fpot_netamount, fpot_netRate;
                                                    var po_idiscountcalculs = Ext.getCmp('po_idiscountcalculs').getRawValue();
                                                    var fpot_amount = Ext.getCmp('fpot_amount').getValue();
                                                    var fpot_itemaddidisc = Ext.getCmp('fpot_itemaddidisc').getValue();
                                                    var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    var fpot_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (po_idiscountcalculs == 'Amount') {
                                                        fpot_netRate = fpot_itemoffrrateExcT - parseFloat(fpot_itemaddidisc);
                                                    } else {
                                                        fpot_netRate = parseFloat(fpot_itemoffrrateExcT) - (parseFloat(fpot_itemoffrrateExcT) * parseFloat(fpot_itemaddidisc) / 100);
                                                    }
                                                    fpot_netRate = Math.round((fpot_netRate + Number.EPSILON) * 100) / 100;
                                                    fpot_netamount = fpot_netRate * fpot_itemqty;
                                                    //fpot_netamount = fpot_netamount + (fpot_netamount * Application.RetalineSalesRequest.Cache.stit_GST / 100);
                                                    fpot_netamount = Math.round((fpot_netamount + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netamount', fpot_netamount);
                                                    Ext.getCmp('fpot_netamount').setValue(fpot_netamount);
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
                                    columnWidth: .70,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Notes',
                                            id: 'fpot_notes',
                                            name: 'n[fpot_notes]',
                                            anchor: '98%',
                                            tabIndex: 915
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.10,
                                    items: [{
                                            xtype: 'button',
                                            text: 'Add',
                                            tabIndex: 916,
                                            id: 'addItemsToPO',
                                            iconCls: 'finascop_add',
                                            style: 'margin-top:17px;',
                                            handler: function () {
                                                if (Ext.getCmp('fpot_netamount').getValue() > 0) {
                                                    Ext.Ajax.request({
                                                        waitMsg: 'Processing',
                                                        method: 'POST',
                                                        url: modURL + '&op=saveB2BSRItemDetails',
                                                        params: {
                                                            b2bsr_UniqueID: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID,
                                                            b2bsr_Customerid: Ext.getCmp('b2bsr_Customerid').getValue(),
                                                            b2bsr_itemid: Ext.getCmp('b2bSalesRequestItems').getValue(),
                                                            b2bsr_itemname: Ext.getCmp('b2bSalesRequestItems').getRawValue(),
                                                            b2bsr_itemmrp: Ext.getCmp('b2bso_itemmrp').getValue(),
                                                            b2bsr_itemPkg: Ext.getCmp('itemUnitForm').getValue(),
                                                            b2bsr_itemqty: Ext.getCmp('b2bsr_itemqty').getValue(),
                                                            b2bsr_itemoffrqty: Ext.getCmp('b2bsr_itemoffrqty').getValue(),
                                                            b2bsr_itemrate: Ext.getCmp('fpot_itemoffrrate').getValue(),
                                                            //fpot_itemoffrrateet: Ext.getCmp('fpot_itemoffrrateet').getValue(),
                                                            b2bsr_itemaddidisc: Ext.getCmp('fpot_itemaddidisc').getValue(),
                                                            //fpot_effectiverate: Ext.getCmp('fpot_effectiverate').getValue(),
                                                            b2bsr_amount: Ext.getCmp('fpot_amount').getValue(),
                                                            b2bsr_netamount: Ext.getCmp('fpot_netamount').getValue(),
                                                            b2bsr_idiscountcalculs: Ext.getCmp('po_idiscountcalculs').getValue(),
                                                            b2bsr_notes: Ext.getCmp('fpot_notes').getValue(),
                                                            sradhoc_updatedon: Application.RetalineSalesRequest.Cache.sradhoc_updatedon,
                                                            adhoc_srValue: Ext.getCmp('fpot_poValue').getValue(),
                                                            adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                                            adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                                            adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                                            adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                                                            adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                            adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue(),
                                                            b2bsr_itemrateet: Ext.getCmp('fpot_itemoffrrateExcT').getValue(),
                                                            stit_GST: Application.RetalineSalesRequest.Cache.stit_GST
                                                        },
                                                        success: function (response) {
                                                            var tmp = Ext.decode(response.responseText);
                                                            if (tmp.success === true)
                                                            {
                                                                Application.example.msg('Success', tmp.msg);
                                                                Application.RetalineSalesRequest.Cache.sradhoc_updatedon = tmp.date;
                                                                Ext.getCmp('itemHistory').hide();
                                                                Ext.getCmp('salesRequestItemGrid').store.load({
                                                                    params: {
                                                                        b2bsr_UniqueID: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID
                                                                    },
                                                                    callback: function () {
                                                                        Ext.getCmp('fpot_poValue').focus(false, 100);
                                                                        Ext.getCmp('b2bSalesRequestItems').reset();
                                                                        Ext.getCmp('b2bso_itemmrp').reset();
                                                                        Ext.getCmp('b2bsr_itemqty').reset();
                                                                        Ext.getCmp('b2bsr_itemoffrqty').reset();
                                                                        Ext.getCmp('fpot_itemoffrrate').reset();
                                                                        Ext.getCmp('fpot_itemaddidisc').reset();
                                                                        Ext.getCmp('fpot_amount').reset();
                                                                        Ext.getCmp('fpot_netamount').reset();
                                                                        Ext.getCmp('po_idiscountcalculs').reset();
                                                                        Ext.getCmp('fpot_notes').reset();
                                                                    }
                                                                });
                                                                Ext.getCmp('salesRequestItemGrid').getStore().load({
                                                                    params: {
                                                                        b2bsr_UniqueID: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID
                                                                    }
                                                                });

                                                            } else
                                                            {
                                                                Ext.MessageBox.alert("Failure", "Error moving Data, Kindly reload.");
                                                                //Ext.getCmp('b2bSalesRequestWinID').close();

                                                            }
                                                        },
                                                        failure: function (response) {
                                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                                            Ext.MessageBox.alert('Error', tmp.msg);
                                                        }
                                                    });
                                                } else {
                                                    Ext.Msg.alert("Notification.", 'Please fill all the fields');
                                                }

                                            }
                                        }]
                                }]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'po_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [
                                        salesRequestItemGrid()
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
                            columnWidth: 0.75,
                            items: [{
                                    xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelWidth: 35,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    fieldLabel: 'Total',
                                    id: 'fpot_poValue',
                                    name: 'fpot_poValue',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 917,
                                    readOnly: true,
                                    anchor: '99%',
                                    listeners: {
                                        focus: function (txtbx) {
                                            var itemStore = Ext.getCmp('salesRequestItemGrid').getStore();
                                            var fpot_netamount = itemStore.sum('b2bsr_netamount');
                                            var total_initialnetamount = itemStore.sum('b2bsr_initialnetamount');
                                            var total_initialofferamount = itemStore.sum('b2bsr_amount');
                                            Application.RetalineSalesRequest.Cache.total_initialnetamount = total_initialnetamount;
                                            Application.RetalineSalesRequest.Cache.total_initialofferamount = total_initialofferamount;
                                            var totalItems = itemStore.getCount();
                                            if (parseFloat(Application.RetalineSalesRequest.Cache.totalhcg) > 0 && Ext.getCmp('fpot_shippingcharge').getValue() > 0) {
                                                Application.RetalineSalesRequest.Cache.totalhcg = Application.RetalineSalesRequest.Cache.totalhcg;
                                            } else {
                                                Application.RetalineSalesRequest.Cache.totalhcg = 0;
                                            }
                                            fpot_netamount = fpot_netamount + Application.RetalineSalesRequest.Cache.totalhcg;
                                            console.log('fpot_netamountpovalue2', fpot_netamount);
                                            Ext.getCmp('fpot_poValue').setValue(fpot_netamount);
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
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
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'ptc_name',
                                    valueField: 'ptc_id',
                                    mode: 'local',
                                    id: 'po_payment_terms',
                                    forceSelection: true,
                                    fieldLabel: 'Credit Terms',
                                    emptyText: 'Credit Terms',
                                    anchor: '98%',
                                    allowBlank: false,
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 918,
                                    store: pmtTermsStore,
                                    listeners: {
                                        select: function (combo, record) {
                                            var type = record.data.ptc_name;
                                            if (type == 'Custom') {
                                                Ext.getCmp('po_payment_value').show();
                                                Ext.getCmp('po_payment_value').allowBlank = true;
                                            } else {
                                                Ext.getCmp('po_payment_value').hide();
                                                Ext.getCmp('po_payment_value').allowBlank = false;
                                            }
                                        }

                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    fieldLabel: 'Credit Days',
                                    xtype: 'numberfield',
                                    id: 'po_payment_value',
                                    labelAlign: 'top',
                                    allowBlank: false,
                                    name: 'po_payment_value',
                                    hidden: true,
                                    anchor: '98%',
                                    tabIndex: 919
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'fpo_validityType',
                                    forceSelection: true,
                                    fieldLabel: 'Validity',
                                    emptyText: 'Validity',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 920,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: '15', ptname: '15 Days'},
                                            {ptid: '30', ptname: '30 Days'}, {ptid: '60', ptname: '60 Days'}, {ptid: '90', ptname: '90 Days'}]
                                    })
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    disabled: true,
                                    xtype: 'numberfield',
                                    fieldLabel: 'Shipping/Handling Charge',
                                    id: 'fpot_shippingcharge',
                                    name: 'n[fpot_shippingcharge]',
                                    anchor: '98%',
                                    value: 0,
                                    tabIndex: 921,
                                    listeners: {
                                        change: function () {
                                            var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
                                            var fpot_hshippingcharge = parseFloat(fpot_shippingcharge) * 100 / parseFloat(Application.RetalineSalesRequest.Cache.total_initialnetamount);
                                            Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = fpot_hshippingcharge;
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    disabled: true,
                                    xtype: 'numberfield',
                                    fieldLabel: 'General Discounts',
                                    id: 'fpot_generalDiscount',
                                    name: 'n[fpot_generalDiscount]',
                                    anchor: '98%',
                                    value: 0,
                                    tabIndex: 922,
                                    listeners: {
                                        change: function () {
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'name',
                                    valueField: 'id',
                                    mode: 'local',
                                    id: 'po_gdiscountcalculs',
                                    forceSelection: true,
                                    fieldLabel: 'Amt /Percent',
                                    emptyText: 'Amount / Percentage',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 925,
                                    store: new Ext.data.JsonStore({
                                        fields: ['id', 'name'],
                                        data: [{id: 'Percentage', name: 'Percentage'}]
                                    }), listeners: {
                                        select: function () {
                                            justCaculatehcgd();
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'button',
                                    text: 'Apply',
                                    tabIndex: 926,
                                    id: 'applyGeneralDiscount',
                                    style: 'margin-top:17px;',
                                    handler: function () {
                                        if ((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0)) {
                                            justCaculatehcgd();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=srGenDiscounCalculate',
                                                method: 'POST',
                                                params: {
                                                    b2bsr_UniqueID: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID,
                                                    total_initialnetamount: Application.RetalineSalesRequest.Cache.total_initialnetamount,
                                                    total_initialofferamount: Application.RetalineSalesRequest.Cache.total_initialofferamount,
                                                    fpot_gdiscpercent: Application.RetalineSalesRequest.Cache.fpot_gdiscpercent,
                                                    fpot_hshippingcharge: Application.RetalineSalesRequest.Cache.fpot_hshippingcharge,
                                                    adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                                    adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                                    adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                                    adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                                                    adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                    adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue(),
                                                    itemGST: Application.RetalineSalesRequest.Cache.stit_GST

                                                },
                                                success: function (response) {

                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success == true) {
                                                        Ext.getCmp('salesRequestItemGrid').store.load({
                                                            params: {
                                                                b2bsr_UniqueID: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID
                                                            },
                                                            callback: function () {
                                                                Application.RetalineSalesRequest.Cache.totalhcg = tmp.hcg;
                                                                Ext.getCmp('fpot_poValue').focus(false, 100);
                                                                Application.RetalineSalesRequest.Cache.fpot_gdiscpercent = 0;
                                                                Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = 0;
                                                                Application.RetalineSalesRequest.Cache.isDiscountApplied = 1;
                                                            }
                                                        });


                                                    } else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }

                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", 'if you want to apply  General discount / Handling Charge please input an amount above 0.');
                                        }

                                    }
                                }]
                        }
                    ]
                }]
        });
        return panel;
    };
    var viewSalesRequestItemGrid = function (bbsr_id) {
        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listSrItemsStore',
                    method: 'post'
                }),
                fields: ['b2bsr_itemid', 'b2bsr_itemname', 'b2bsr_itemmrp', 'b2bsr_itemqty', 'b2bsr_itemoffrqty', 'b2bsr_totalqty', 'b2bsr_balanceqty', 'b2bsr_itemrate', 'itemDisc',
                    'b2bsr_itemaddidisc', 'b2bsr_effectiverate', 'b2bsr_idiscountcalculs', {name: 'b2bsr_amount', type: 'float'}, {name: 'b2bsr_netamount', type: 'float'}, {name: 'b2bsr_initialnetamount', type: 'float'}, 'b2bsr_gendiscount', 'b2bsr_shippingcharge'],
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
            cm: viewpurchaseItemOrderColModel(),
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
    var salesRequestItemGrid = function () {

        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listSrDetailsStore',
                    method: 'post'
                }),
                fields: ['b2bsr_itemid', 'b2bsr_itemname', 'b2bsr_itemmrp', 'b2bsr_itemqty', 'b2bsr_itemoffrqty', 'b2bsr_totalqty', 'b2bsr_balanceqty', 'b2bsr_itemrate', 'itemDisc',
                    'b2bsr_itemaddidisc', 'b2bsr_effectiverate', 'b2bsr_idiscountcalculs', {name: 'b2bsr_amount', type: 'float'}, {name: 'b2bsr_netamount', type: 'float'}, {name: 'b2bsr_initialnetamount', type: 'float'}, 'b2bsr_gendiscount', 'b2bsr_shippingcharge'],
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
            id: 'salesRequestItemGrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: purchaseItemOrderColModel(),
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
    var purchaseItemOrderColModel = function () {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'MRP (Inc. of GST)',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'b2bsr_itemmrp',
                align: 'right',
                tooltip: 'MRP (Inc. of GST)',
                width: 60
            }, {
                header: 'Rate',
                sortable: true,
                dataIndex: 'b2bsr_itemrate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate',
                align: 'right',
                width: 60
            }, {
                header: 'Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_totalqty',
                tooltip: 'Qty',
                align: 'right',
                width: 50
            }, {
                header: 'Amount',
                sortable: true,
                hideable: false,
                xtype: 'finascopcurrency',
                dataIndex: 'b2bsr_amount',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Amount',
                align: 'right',
                width: 60
            }, {
                header: 'Product Dis.',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_itemoffrqty',
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
                hidden: true,
                dataIndex: 'b2bsr_shippingcharge',
                tooltip: 'Shipping / Handling Charge',
                align: 'right',
                width: 50
            }, {
                header: 'General Dis.',
                sortable: true,
                hidden: true,
                dataIndex: 'b2bsr_gendiscount',
                tooltip: 'General Discount',
                align: 'right',
                width: 50
            },
            {
                header: 'Net Amt',
                sortable: true,
                dataIndex: 'b2bsr_netamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Net Amt',
                width: 60
            }, {
                header: 'Initial Net Amt',
                hidden: true,
                dataIndex: 'b2bsr_initialnetamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Initial Net Amt',
                width: 60
            },
            {
                header: 'ESR',
                sortable: true,
                dataIndex: 'b2bsr_effectiverate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'ESR',
                width: 60
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
                                    removeFromItemStore(grid, rowIndex, record);
                                }
                            });
                        }
                    }]
            }
        ]);
    };
    var viewpurchaseItemOrderColModel = function () {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'MRP (Inc. of GST)',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'b2bsr_itemmrp',
                align: 'right',
                tooltip: 'MRP (Inc. of GST)',
                width: 60
            }, {
                header: 'Rate',
                sortable: true,
                dataIndex: 'b2bsr_itemrate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate',
                align: 'right',
                width: 60
            }, {
                header: 'Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_totalqty',
                tooltip: 'Qty',
                align: 'right',
                width: 50
            }, {
                header: 'Amount',
                sortable: true,
                hideable: false,
                xtype: 'finascopcurrency',
                dataIndex: 'b2bsr_amount',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Amount',
                align: 'right',
                width: 60
            }, {
                header: 'Product Dis.',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_itemoffrqty',
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
                hidden: true,
                dataIndex: 'b2bsr_shippingcharge',
                tooltip: 'Shipping / Handling Charge',
                align: 'right',
                width: 50
            }, {
                header: 'General Dis.',
                sortable: true,
                hidden: true,
                dataIndex: 'b2bsr_gendiscount',
                tooltip: 'General Discount',
                align: 'right',
                width: 50
            },
            {
                header: 'Net Amt',
                sortable: true,
                dataIndex: 'b2bsr_netamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Net Amt',
                width: 60
            }, {
                header: 'Initial Net Amt',
                hidden: true,
                dataIndex: 'b2bsr_initialnetamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Initial Net Amt',
                width: 60
            },
            {
                header: 'ESR',
                sortable: true,
                dataIndex: 'b2bsr_effectiverate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'ESR',
                width: 60
            }
        ]);
    };
    var pmtTermsStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + '&op=getPaymentTerms',
        method: 'post',
        fields: ['ptc_id', 'ptc_name'],
        root: 'data'
    });
    function justCaculatehcgd() {
        var fpot_gdiscpercent, fpot_hshippingcharge;
        var po_gdiscountcalculs = Ext.getCmp('po_gdiscountcalculs').getRawValue();
        var fpot_generalDiscount = Ext.getCmp('fpot_generalDiscount').getValue();
        var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
        if (po_gdiscountcalculs == 'Amount') {
            fpot_gdiscpercent = parseFloat(fpot_generalDiscount) * 100 / parseFloat(Application.RetalineSalesRequest.Cache.total_initialnetamount);
        } else {
            if (fpot_generalDiscount <= 100) {
                fpot_gdiscpercent = parseInt(fpot_generalDiscount);
            } else {
                Ext.MessageBox.alert("Notification", "Percentage value should be between 1 nd 100");
            }

        }
        fpot_hshippingcharge = parseFloat(fpot_shippingcharge) * 100 / parseFloat(Application.RetalineSalesRequest.Cache.total_initialnetamount);
        Application.RetalineSalesRequest.Cache.fpot_gdiscpercent = fpot_gdiscpercent;
        Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = fpot_hshippingcharge;
    }
    var saveB2BSalesRequest = function (type, typefrom) {
        var po_payment_terms = Ext.getCmp('po_payment_terms').getValue();
        var po_payment_value = Ext.getCmp('po_payment_value').getValue();
        var fpo_validityType = Ext.getCmp('fpo_validityType').getValue();
        var customerId = Ext.getCmp('b2bsr_Customerid').getValue();
        var customerName = Ext.getCmp('b2bsr_Customerid').getRawValue();
        if (Application.RetalineSalesRequest.Cache.isDiscountApplied == 1) {
            var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
            var fpot_generalDiscount = Ext.getCmp('fpot_generalDiscount').getValue();
            var po_gdiscountcalculs = Ext.getCmp('po_gdiscountcalculs').getValue();
        } else {
            Ext.getCmp('fpot_shippingcharge').setValue(0);
            Ext.getCmp('fpot_generalDiscount').setValue(0);
            Ext.getCmp('po_gdiscountcalculs').setValue('');
            var fpot_shippingcharge = 0
            var fpot_generalDiscount = 0;
            var po_gdiscountcalculs = 0;
        }
        if (Ext.isEmpty(Application.RetalineSalesRequest.Cache.b2bsr_UniqueID)) {
            Application.RetalineSalesRequest.Cache.b2bsr_UniqueID = Application.RetalineSalesRequest.Cache.b2bsr_UniqueID4edit;
        }

        //if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('fpo_validityType').getValue()) && (Ext.getCmp('fpot_poValue').getValue() > 0)) {

        // if (((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0) && Application.RetalineSalesRequest.Cache.isDiscountApplied == 1) || ((Ext.getCmp('fpot_generalDiscount').getValue() == 0) || (Ext.getCmp('fpot_shippingcharge').getValue() == 0) && Application.RetalineSalesRequest.Cache.isDiscountApplied == 0)) {

        Ext.Ajax.request({
            url: modURL + '&op=saveSalesRequest',
            method: 'POST',
            params: {
                po_payment_terms: po_payment_terms,
                po_payment_value: po_payment_value,
                fpo_validityType: fpo_validityType,
                customerId: customerId,
                customerName: customerName,
                uid: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID,
                poValue: Ext.getCmp('fpot_poValue').getValue(),
                fpot_gdiscpercent: Application.RetalineSalesRequest.Cache.fpot_gdiscpercent,
                fpot_hshippingcharge: Application.RetalineSalesRequest.Cache.fpot_hshippingcharge,
                fpot_shippingcharge: fpot_shippingcharge,
                fpot_generalDiscount: fpot_generalDiscount,
                po_gdiscountcalculs: po_gdiscountcalculs,
                total_initialnetamount: Application.RetalineSalesRequest.Cache.total_initialnetamount,
                total_initialofferamount: Application.RetalineSalesRequest.Cache.total_initialofferamount,
                type: type,
                typefrom: typefrom

            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true) {
                    Application.example.msg('Success', res.msg);
                    Ext.getCmp('b2bSalesRequestWinID').close();
                    if (type == 'add') {
                        recs_per_page = updateRecsPerPage(Ext.getCmp('B2BSalesRequestGrid'));
                        Ext.getCmp('B2BSalesRequestGrid').store.reload({
                            params: {
                                start: 0,
                                limit: recs_per_page
                            }
                        });
                    } else {
                        recs_per_page = updateRecsPerPage(Ext.getCmp('gridpaneSalesRequestadhoc'));
                        Ext.getCmp('gridpaneSalesRequestadhoc').store.reload({
                            params: {
                                start: 0,
                                limit: recs_per_page
                            }
                        });
                    }

                } else {
                    Ext.MessageBox.alert("Notification", response.responseText);
                    // Ext.getCmp('b2bSalesRequestWinID').close();
                }
            },
            failure: function (response) {
                Ext.MessageBox.alert('Error', response.responseText);
            }
        });
        //} else {
        //Ext.MessageBox.alert("Notification", "Mistake in Discount applied.");
        //}


        //} else {
        //Ext.MessageBox.alert("Notification", 'Please fill all Payment Terms , Validity ');
        //}
    };
    var removeFromItemStore = function (grid, indx, record)
    {

        Ext.Ajax.request({
            url: modURL + '&op=deleteB2BItemFromSR',
            method: 'POST',
            params: {
                itemid: record.get('b2bsr_itemid'),
                uid: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID,
                srId: 0
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true)
                {
                    Application.example.msg('Success', 'Removed item');
                    Ext.getCmp('salesRequestItemGrid').getStore().reload({
                            params: {
                                b2bsr_UniqueID: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID
                            }
                        });
                        console.log('here2');
                        Ext.getCmp('fpot_poValue').focus(false, 100);
//                    function (btn) {
//                        Ext.getCmp('salesRequestItemGrid').getStore().load({
//                            params: {
//                                b2bsr_UniqueID: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID
//                            }
//                        });
//                        console.log('here2');
//                        Ext.getCmp('fpot_poValue').focus(false, 100);
//                    });
                } else
                {
                    Ext.MessageBox.alert('Failed');
                }
            }
        });

    };
    var adSalesRequestPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id,
            title: 'Adhoc Sales Request',
            iconCls: 'finascop_invoice', items: [SalesRequestAdhocGrid()]
        });
        return panel;
    };
    var gridSelectionChangedadhoc = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpaneSalesRequestadhoc').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpaneSalesRequestadhoc').getSelectionModel().getSelections()[0].data.fpo_id;
        }
    };
    var SalesRequestAdhocGrid = function () {

        var _PoGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'adhoc_name'
                }, {
                    type: 'string',
                    dataIndex: 'adhoc_createdon'
                }, {
                    type: 'string',
                    dataIndex: 'vendorName'
                }]
        });
        _PoGridFilter.remote = true;
        _PoGridFilter.autoReload = true;
        var _gridStore = SalesRequestAdhocGridStore();
        var _gridPanel = new Ext.grid.GridPanel({
            id: 'gridpaneSalesRequestadhoc',
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
            tbar: [],
            plugins: [new Ext.ux.grid.GroupSummary(), _PoGridFilter],
            columns: [
                {
                    header: 'Unique Id',
                    sortable: true,
                    dataIndex: 'adhoc_uniqueid',
                    tooltip: 'Name',
                    hideable: false
                },
                {
                    header: 'Created Date',
                    sortable: true,
                    dataIndex: 'adhoc_createdon',
                    tooltip: 'PO Date'
                }, {
                    header: 'Customer',
                    sortable: true,
                    dataIndex: 'customerName',
                    tooltip: 'Customer',
                    hideable: false
                },
                {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: 'approve-reject',
                            tooltip: 'Confirm Sales Request',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                editB2BSalesRequest(record.get('adhoc_uniqueid'), 'isAdhoc');
                                //Application.Finascop_Purchase_Order.editPOWindowForVendor(record.get('adhoc_uniqueid'));
                            }
                        }, {
                            tooltip: 'DELETE',
                            iconCls: 'wrong',
                            handler: function () {
                                var adhoc_uniqueid = Ext.getCmp('gridpaneSalesRequestadhoc').getSelectionModel().getSelections()[0].data.adhoc_uniqueid;
                                Application.RetalineSalesRequest.deleteFeedback(adhoc_uniqueid);
                            }
                        }
                    ]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true,
                listeners: {
                    selectionchange: gridSelectionChangedadhoc
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
    var SalesRequestAdhocGridStore = function () {
        var _catStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getSalesRequestAdhoc',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'adhoc_uniqueid',
                root: 'data'
            }, ['adhoc_uniqueid', 'adhoc_customer', 'adhoc_createdon', 'adhoc_srValue', 'customerName']),
            sortInfo: {
                field: 'adhoc_createdon',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    //Ext.getCmp('gridpanelFinascopPurchaseOrderDataviewPurchaseorderdata').getView().refresh();
                    Ext.getCmp('gridpaneSalesRequestadhoc').getSelectionModel().selectRow(0);
                }
            }
        });
        return _catStore;
    };
    var editB2BSalesRequest = function (uniqueid, type) {

        var B2BSalesRequestForm = editB2BSalesRequestForm(uniqueid, type);
        var b2bSRrderWin = new Ext.Window({
            id: "b2bSalesRequestWinID",
            title: 'Sales Request',
            shadow: false,
            height: 520,
            width: 1180,
            modal: true,
            layout: 'fit',
            autoHeight: true,
            resizable: true,
            closable: true,
            listeners: {
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
                        var store = Ext.getCmp('salesRequestItemGrid').getStore();
                        if (store.getCount()) {
                            Application.RetalineSalesRequest.Cache.b2bsr_UniqueID4edit = uniqueid;
                            saveB2BSalesRequest('edit', type);
                        } else {
                            Ext.MessageBox.alert("Notification", 'Please fill B2B Sales Request details.');
                        }
                    }
                }]
        });

        var t = new Date();
        var t_stamp = t.format("YmdHis");
        Ext.Ajax.request({
            url: modURL + '&op=loadAdhocSalesRequest',
            params: {
                apikey: _SESSION.apikey,
                tstamp: t_stamp,
                uniqueid: uniqueid,
                type: type

            },
            method: 'POST', success: function (res) {
                var gridname;
                var tmp = Ext.decode(res.responseText);
                Ext.getCmp('itemHistory').hide();

                Ext.getCmp('salesRequestItemGrid').store.load({
                    params: {
                        b2bsr_UniqueID: uniqueid,
                        type: type
                    },
                    callback: function () {
                        Application.RetalineSalesRequest.Cache.sradhoc_updatedon = tmp.adhoc_updatedon;
                        console.log('focus3');
                        Ext.getCmp('fpot_poValue').focus(false, 100);
                        Ext.getCmp('b2bSalesRequestItems').reset();
                        Ext.getCmp('b2bso_itemmrp').reset();
                        Ext.getCmp('fpot_itemoffrrate').reset();
                        Ext.getCmp('fpot_itemoffrrateExcT').reset();
                        Ext.getCmp('b2bsr_itemqty').reset();
                        Ext.getCmp('fpot_amount').reset();
                        Ext.getCmp('b2bsr_itemoffrqty').reset();
                        Ext.getCmp('itemUnitForm').reset();
                        Ext.getCmp('fpot_itemaddidisc').reset();
                        Ext.getCmp('po_idiscountcalculs').reset();
                        Ext.getCmp('fpot_netamount').reset();
                        Ext.getCmp('fpot_notes').reset();
                        Ext.getCmp('po_payment_terms').reset();
                        Ext.getCmp('po_payment_value').reset();
                        Application.RetalineSalesRequest.Cache.Pouid = uniqueid;
                        Ext.getCmp('uniqueId').setValue(uniqueid);
                        Ext.getCmp('b2bsr_Customerid').setReadOnly(true);
                        Ext.getCmp('b2bsr_Customerid').setHideTrigger(true);
                        Ext.getCmp('b2bsr_Customerid').setValue(tmp.adhoc_customer);
                        Ext.getCmp('po_payment_terms').setValue(tmp.adhoc_paymentTerms);
                        Ext.getCmp('po_payment_value').setValue(tmp.adhoc_paymentValue);
                        Ext.getCmp('fpo_validityType').setValue(tmp.adhoc_validityType);
                        Ext.getCmp('fpot_shippingcharge').setValue(tmp.adhoc_shippingcharge);
                        Ext.getCmp('fpot_generalDiscount').setValue(tmp.adhoc_gdiscount);
                        Ext.getCmp('po_gdiscountcalculs').setValue(tmp.adhoc_gdiscounttype);
                        //                            Ext.getCmp('fpo_validityType').getStore().load();
                        //                            Ext.getCmp('po_gdiscountcalculs').getStore().load();
                        Ext.getCmp('b2bSalesRequestItems').getStore().load();
                        Ext.getCmp('po_payment_terms').getStore().load({
                            params: {
                                stpa_id: tmp.adhoc_vendor
                            }
                        });
                    }
                });
            }
        });


        b2bSRrderWin.doLayout();
        b2bSRrderWin.show();
        b2bSRrderWin.center();
    };
    var editB2BSalesRequestForm = function (uniqueid) {
        var purUni = '';
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'newPOFormForVendor',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            xtype: 'hidden', id: 'uniqueId', name: 'uniqueId'
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            items: []
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'left',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'B2B Customer',
                                    hideLabel: true,
                                    emptyText: 'Choose B2B Customer',
                                    id: 'b2bsr_Customerid',
                                    name: 'b2bsr_Customerid',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true, anchor: '97%',
                                    store: b2bSalesCustomerStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'b2b_Customer_Name',
                                    valueField: 'b2b_Customer_ID',
                                    hiddenName: 'b2bsr_Customerid',
                                    tabIndex: 902,
                                    listeners: {
                                        select: function () {
                                            var pe_party = Ext.getCmp('b2bsr_Customerid').getValue();

                                            Ext.Ajax.request({
                                                url: modURL + '&op=b2bSOCustomerDetails',
                                                method: 'POST',
                                                params: {b2bSOCustomer: pe_party},
                                                success: function (res) {
                                                    var tmp = Ext.decode(res.responseText);
                                                    Ext.getCmp('vendorDetails').show();

                                                    Ext.getCmp('b2bSRContactPerson').setValue(tmp.b2b_Customer_Incharge);
                                                    Ext.getCmp('b2bSRCustomerMobile').setValue(tmp.b2b_Customer_Mobile);
                                                    Ext.getCmp('b2b_Customer_gst').setValue(tmp.b2b_Customer_gst);
                                                    Ext.getCmp('b2b_Customer_dlno1').setValue(tmp.b2b_Customer_dlno1);
                                                    Ext.getCmp('b2b_Customer_dlno2').setValue(tmp.b2b_Customer_dlno2);

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
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'SR Number',
                                    id: 'po_number',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_number]', anchor: '97%',
                                    value: 'Not Genereated',
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
                                    fieldLabel: 'SR Date', id: 'po_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    value: 'Not Genereated',
                                    anchor: '97%',
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 65,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Ordered By',
                                    id: 'po_ordered_by',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_ordered_by]', anchor: '97%',
                                    value: _SESSION.FirstName,
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: 0.20,
                            labelAlign: 'left',
                            labelWidth: 50,
                            items: []
                        }]
                }, {
                    xtype: 'fieldset',
                    title: 'B2B Customer Details',
                    collapsible: true,
                    columnWidth: 1,
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    id: 'vendorDetails',
                    hidden: true,
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            labelAlign: 'top',
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
                            columnWidth: .20,
                            labelAlign: 'top',
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
                        },
                        {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'GST',
                                    id: 'b2b_Customer_gst',
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
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'DL1',
                                    id: 'b2b_Customer_dlno1',
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
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'DL2',
                                    id: 'b2b_Customer_dlno2',
                                    style: {
                                        'font-weight': 'bold'
                                    },
                                    anchor: '97%'
                                }]
                        }]
                }, {
                    xtype: 'fieldset',
                    layout: 'column',
                    collapsible: true,
                    title: 'Item Previous Rates',
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
                                    id: 'itemSmaalStockUnit',
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
                                    fieldLabel: 'HSN', id: 'itemHSN',
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
                                    fieldLabel: 'GST', id: 'itemGST',
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
                                    fieldLabel: 'Least SKU MRP',
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
                                    fieldLabel: 'Last Rate',
                                    id: 'lastPurchaseRate',
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
                                    fieldLabel: 'Lowest SP',
                                    id: 'itemLPP',
                                    hidden: true,
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
                                    fieldLabel: 'Item Count',
                                    id: 'itemCount',
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
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Min Qty',
                                    id: 'itemMinQty',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }]
                }, {
                    layout: 'column',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'column',
                            style: 'margin-bottom:3px;',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.25,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Items',
                                            id: 'b2bSalesRequestItems',
                                            name: 'b2bSalesRequestItems',
                                            labelStyle: mandatory_label,
                                            allowBlank: false,
                                            labelAlign: 'top',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: b2bCustomerItemStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'stit_SKU',
                                            valueField: 'stit_ID',
                                            hiddenName: 'b2bSalesRequestItems',
                                            tabIndex: 904,
                                            listeners: {
                                                select: function () {
                                                    var b2bSalesRequestItems = Ext.getCmp('b2bSalesRequestItems').getValue();
                                                    var b2bsr_Customerid = Ext.getCmp('b2bsr_Customerid').getValue();
                                                    Ext.Ajax.request({
                                                        url: modURL + '&op=itemHistory',
                                                        method: 'POST',
                                                        params: {b2bSalesRequestItems: b2bSalesRequestItems, b2bsr_Customerid: b2bsr_Customerid},
                                                        success: function (res) {
                                                            var tmp = Ext.decode(res.responseText);
                                                            console.log('tmp.minQty',tmp.minQty);
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
                                                            Ext.getCmp('itemCount').setValue(tmp.itemCount);
                                                            Ext.getCmp('itemMinQty').setValue(tmp.minQty);
                                                            //Ext.getCmp('itemUnit').setValue(tmp.csb_package_type_name);
                                                            Ext.getCmp('itemUnitForm').setValue(tmp.itemUnitForm);
                                                            Ext.getCmp('itemSmaalStockUnit').setValue(tmp.itemSmaalStockUnit);
                                                            Ext.getCmp('b2bso_itemmrp').setValue(tmp.skuMRP);
                                                            Ext.getCmp('fpot_itemoffrrate').setValue(tmp.skuRate);
                                                            console.log('tmp.skuRate', tmp.skuRate);
                                                            console.log('tmp.stit_GST', tmp.stit_GST);
                                                            Ext.getCmp('lastPurchaseRate').setValue(tmp.lastPurchaseRate);
                                                            var offerateET = tmp.skuRate * 100 / (100 + parseInt(tmp.stit_GST));
                                                            Ext.getCmp('fpot_itemoffrrateExcT').setValue(offerateET);
                                                            Application.RetalineSalesRequest.Cache.stit_GST = tmp.stit_GST;
                                                            Application.RetalineSalesRequest.Cache.itemCount = tmp.itemCount;

                                                        },
                                                        failure: function () {
                                                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                        }
                                                    });

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.09,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'MRP (Inc. of GST)',
                                            id: 'b2bso_itemmrp',
                                            align: 'right',
                                            name: 'n[b2bso_itemmrp]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true,
                                            tabIndex: 905,
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Rate',
                                            align: 'right',
                                            id: 'fpot_itemoffrrate',
                                            name: 'n[fpot_itemoffrrate]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true,
                                            tabIndex: 906,
                                            value: 0,
                                            listeners: {
                                                change: function (e) {
                                                    var b2bsr_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    var amount = b2bsr_itemqty * fpot_itemoffrrate;
                                                    Ext.getCmp('fpot_amount').setValue(amount);
                                                    console.log('e', e);
//                                            var taxRate = this.data.stit_GST;
//                                            var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
//                                            var fpot_itemoffrrateet = (parseFloat(fpot_itemoffrrate) * 100) / (100 + parseFloat(taxRate));
                                                    //                                            Ext.getCmp('fpot_itemoffrrateet').setValue(fpot_itemoffrrateet);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.08,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Rate (Excl.GST)',
                                            align: 'right',
                                            id: 'fpot_itemoffrrateExcT',
                                            name: 'n[fpot_itemoffrrateExcT]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 907,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Qty',
                                            align: 'right',
                                            id: 'b2bsr_itemqty',
                                            name: 'n[b2bsr_itemqty]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 908,
                                            listeners: {
                                                change: function () {
                                                    var b2bsr_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (b2bsr_itemqty <= Application.RetalineSalesRequest.Cache.itemCount) {
                                                        var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                        var amount = b2bsr_itemqty * fpot_itemoffrrate;
                                                        Ext.getCmp('fpot_amount').setValue(amount);
                                                    } else {
                                                        Ext.getCmp('b2bsr_itemqty').reset();

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
                                            fieldLabel: 'Amount',
                                            align: 'right',
                                            id: 'fpot_amount',
                                            name: 'n[fpot_amount]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            //fieldLabel: 'Product Discount (Nos)',
                                            fieldLabel: 'Product Dis.',
                                            align: 'right',
                                            id: 'b2bsr_itemoffrqty',
                                            name: 'n[b2bsr_itemoffrqty]',
                                            anchor: '98%',
                                            tabIndex: 909,
                                            value: 0,
                                            listeners: {
                                                change: function () {
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Unit',
                                            id: 'itemUnitForm',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 910,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            align: 'right',
                                            //fieldLabel: 'Addl Discounts (Rs)',
                                            fieldLabel: 'Item Discounts',
                                            id: 'fpot_itemaddidisc',
                                            name: 'n[fpot_itemaddidisc]',
                                            anchor: '98%',
                                            value: 0,
                                            tabIndex: 911,
                                            listeners: {
                                                change: function () {
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
                                            id: 'po_idiscountcalculs',
                                            forceSelection: true,
                                            fieldLabel: 'Amt /Percent',
                                            emptyText: 'Amount / Percentage',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 912,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'Amount', name: 'Amount'}, {id: 'Percentage', name: 'Percentage'}]
                                            }), listeners: {
                                                select: function () {
                                                    var fpot_netamount, fpot_netRate;
                                                    var po_idiscountcalculs = Ext.getCmp('po_idiscountcalculs').getRawValue();
                                                    var fpot_amount = Ext.getCmp('fpot_amount').getValue();
                                                    var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    var fpot_itemaddidisc = Ext.getCmp('fpot_itemaddidisc').getValue();
                                                    var fpot_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (po_idiscountcalculs == 'Amount') {
                                                        fpot_netRate = fpot_itemoffrrateExcT - parseFloat(fpot_itemaddidisc);
                                                    } else {
                                                        fpot_netRate = parseFloat(fpot_itemoffrrateExcT) - (parseFloat(fpot_itemoffrrateExcT) * parseFloat(fpot_itemaddidisc) / 100);
                                                    }
                                                    fpot_netRate = Math.round((fpot_netRate + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netRate', fpot_netRate);
                                                    fpot_netamount = fpot_netRate * fpot_itemqty;
                                                    console.log('fpot_netamount1', fpot_netamount);
                                                    //fpot_netamount = fpot_netamount + (fpot_netamount * Application.RetalineSalesRequest.Cache.stit_GST / 100);
                                                    console.log('fpot_netamount2', fpot_netamount);
                                                    fpot_netamount = Math.round((fpot_netamount + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netamount3', fpot_netamount);
                                                    Ext.getCmp('fpot_netamount').setValue(fpot_netamount);

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.10,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Net Amt',
                                            id: 'fpot_netamount',
                                            align: 'right',
                                            name: 'n[fpot_netamount]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 913,
                                            readOnly: true,
                                            listeners: {
                                                focus: function () {
                                                    var fpot_netamount, fpot_netRate;
                                                    var po_idiscountcalculs = Ext.getCmp('po_idiscountcalculs').getRawValue();
                                                    var fpot_amount = Ext.getCmp('fpot_amount').getValue();
                                                    var fpot_itemaddidisc = Ext.getCmp('fpot_itemaddidisc').getValue();
                                                    var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    var fpot_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (po_idiscountcalculs == 'Amount') {
                                                        fpot_netRate = fpot_itemoffrrateExcT - parseFloat(fpot_itemaddidisc);
                                                    } else {
                                                        fpot_netRate = parseFloat(fpot_itemoffrrateExcT) - (parseFloat(fpot_itemoffrrateExcT) * parseFloat(fpot_itemaddidisc) / 100);
                                                    }
                                                    fpot_netRate = Math.round((fpot_netRate + Number.EPSILON) * 100) / 100;
                                                    fpot_netamount = fpot_netRate * fpot_itemqty;
                                                    //fpot_netamount = fpot_netamount + (fpot_netamount * Application.RetalineSalesRequest.Cache.stit_GST / 100);
                                                    fpot_netamount = Math.round((fpot_netamount + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netamount', fpot_netamount);
                                                    Ext.getCmp('fpot_netamount').setValue(fpot_netamount);
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
                                    columnWidth: .70,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Notes',
                                            id: 'fpot_notes',
                                            name: 'n[fpot_notes]',
                                            anchor: '98%',
                                            tabIndex: 913
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.10,
                                    items: [{
                                            xtype: 'button',
                                            text: 'Add',
                                            tabIndex: 914,
                                            id: 'addItemsToPO',
                                            iconCls: 'finascop_add',
                                            style: 'margin-top:17px;',
                                            handler: function () {
                                                if (Ext.getCmp('fpot_netamount').getValue() > 0) {
                                                    Ext.Ajax.request({
                                                        waitMsg: 'Processing',
                                                        method: 'POST',
                                                        url: modURL + '&op=saveB2BSRItemDetails',
                                                        params: {
                                                            b2bsr_UniqueID: uniqueid,
                                                            b2bsr_Customerid: Ext.getCmp('b2bsr_Customerid').getValue(),
                                                            b2bsr_itemid: Ext.getCmp('b2bSalesRequestItems').getValue(),
                                                            b2bsr_itemname: Ext.getCmp('b2bSalesRequestItems').getRawValue(),
                                                            b2bsr_itemmrp: Ext.getCmp('b2bso_itemmrp').getValue(),
                                                            b2bsr_itemPkg: Ext.getCmp('itemUnitForm').getValue(),
                                                            b2bsr_itemqty: Ext.getCmp('b2bsr_itemqty').getValue(),
                                                            b2bsr_itemoffrqty: Ext.getCmp('b2bsr_itemoffrqty').getValue(),
                                                            b2bsr_itemrate: Ext.getCmp('fpot_itemoffrrate').getValue(),
                                                            //fpot_itemoffrrateet: Ext.getCmp('fpot_itemoffrrateet').getValue(),
                                                            b2bsr_itemaddidisc: Ext.getCmp('fpot_itemaddidisc').getValue(),
                                                            //fpot_effectiverate: Ext.getCmp('fpot_effectiverate').getValue(),
                                                            b2bsr_amount: Ext.getCmp('fpot_amount').getValue(),
                                                            b2bsr_netamount: Ext.getCmp('fpot_netamount').getValue(),
                                                            b2bsr_idiscountcalculs: Ext.getCmp('po_idiscountcalculs').getValue(),
                                                            b2bsr_notes: Ext.getCmp('fpot_notes').getValue(),
                                                            sradhoc_updatedon: Application.RetalineSalesRequest.Cache.sradhoc_updatedon,
                                                            adhoc_srValue: Ext.getCmp('fpot_poValue').getValue(),
                                                            adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                                            adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                                            adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                                            adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                                                            adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                            adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue(),
                                                            b2bsr_itemrateet: Ext.getCmp('fpot_itemoffrrateExcT').getValue(),
                                                            stit_GST: Application.RetalineSalesRequest.Cache.stit_GST
                                                        },
                                                        success: function (response) {
                                                            var tmp = Ext.decode(response.responseText);
                                                            if (tmp.success === true)
                                                            {
                                                                Application.example.msg('Success', tmp.msg);
                                                                Application.RetalineSalesRequest.Cache.sradhoc_updatedon = tmp.date;
                                                                Ext.getCmp('itemHistory').hide();
                                                                Ext.getCmp('salesRequestItemGrid').store.load({
                                                                    params: {
                                                                        b2bsr_UniqueID: uniqueid
                                                                    },
                                                                    callback: function () {
                                                                        Ext.getCmp('fpot_poValue').focus(false, 100);
                                                                        Ext.getCmp('b2bSalesRequestItems').reset();
                                                                        Ext.getCmp('b2bso_itemmrp').reset();
                                                                        Ext.getCmp('b2bsr_itemqty').reset();
                                                                        Ext.getCmp('b2bsr_itemoffrqty').reset();
                                                                        Ext.getCmp('fpot_itemoffrrate').reset();
                                                                        Ext.getCmp('fpot_itemaddidisc').reset();
                                                                        Ext.getCmp('fpot_amount').reset();
                                                                        Ext.getCmp('fpot_netamount').reset();
                                                                        Ext.getCmp('po_idiscountcalculs').reset();
                                                                        Ext.getCmp('fpot_notes').reset();
                                                                    }
                                                                });
                                                                Ext.getCmp('salesRequestItemGrid').getStore().load({
                                                                    params: {
                                                                        b2bsr_UniqueID: uniqueid
                                                                    }
                                                                });

                                                            } else
                                                            {
                                                                Ext.MessageBox.alert("Failure", "Error moving Data, Kindly reload.");
                                                                //Ext.getCmp('b2bSalesRequestWinID').close();

                                                            }
                                                        },
                                                        failure: function (response) {
                                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                                            Ext.MessageBox.alert('Error', tmp.msg);
                                                        }
                                                    });
                                                } else {
                                                    Ext.Msg.alert("Notification.", 'Please fill all the fields');
                                                }

                                            }
                                        }]
                                }]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'po_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [
                                        salesRequestItemGrid()
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
                            columnWidth: 0.75,
                            items: [{xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelWidth: 35,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    fieldLabel: 'Total',
                                    id: 'fpot_poValue',
                                    name: 'fpot_poValue',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 914,
                                    readOnly: true,
                                    anchor: '99%',
                                    listeners: {
                                        focus: function (txtbx) {
                                            var itemStore = Ext.getCmp('salesRequestItemGrid').getStore();
                                            var fpot_netamount = itemStore.sum('b2bsr_netamount');
                                            var total_initialnetamount = itemStore.sum('b2bsr_initialnetamount');
                                            var total_initialofferamount = itemStore.sum('b2bsr_amount');
                                            Application.RetalineSalesRequest.Cache.total_initialnetamount = total_initialnetamount;
                                            Application.RetalineSalesRequest.Cache.total_initialofferamount = total_initialofferamount;
                                            var totalItems = itemStore.getCount();
                                            if (parseFloat(Application.RetalineSalesRequest.Cache.totalhcg) > 0 && Ext.getCmp('fpot_shippingcharge').getValue() > 0) {
                                                Application.RetalineSalesRequest.Cache.totalhcg = Application.RetalineSalesRequest.Cache.totalhcg;
                                            } else {
                                                Application.RetalineSalesRequest.Cache.totalhcg = 0;
                                            }
                                            fpot_netamount = fpot_netamount + Application.RetalineSalesRequest.Cache.totalhcg;
                                            console.log('fpot_netamountpovalue2', fpot_netamount);
                                            Ext.getCmp('fpot_poValue').setValue(fpot_netamount);
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            items: [{xtype: 'label',
                                    html: '&nbsp;'
                                }]}, ]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'ptc_name',
                                    valueField: 'ptc_id',
                                    mode: 'local',
                                    id: 'po_payment_terms',
                                    forceSelection: true,
                                    fieldLabel: 'Credit Terms',
                                    emptyText: 'Credit Terms',
                                    anchor: '98%',
                                    allowBlank: false,
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2, tabIndex: 915,
                                    store: pmtTermsStore,
                                    listeners: {
                                        select: function (combo, record) {
                                            var type = record.data.ptc_name;
                                            if (type == 'Custom') {
                                                Ext.getCmp('po_payment_value').show();
                                                Ext.getCmp('po_payment_value').allowBlank = true;
                                            } else {
                                                Ext.getCmp('po_payment_value').hide();
                                                Ext.getCmp('po_payment_value').allowBlank = false;
                                            }
                                        }

                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    fieldLabel: 'Credit Days',
                                    xtype: 'numberfield',
                                    id: 'po_payment_value',
                                    labelAlign: 'top',
                                    allowBlank: false,
                                    name: 'po_payment_value', hidden: true,
                                    anchor: '98%',
                                    tabIndex: 916
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'fpo_validityType',
                                    forceSelection: true,
                                    fieldLabel: 'Validity',
                                    emptyText: 'Validity',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2, tabIndex: 917,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: '15', ptname: '15 Days'},
                                            {ptid: '30', ptname: '30 Days'}, {ptid: '60', ptname: '60 Days'}, {ptid: '90', ptname: '90 Days'}]
                                    })
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    disabled: true,
                                    xtype: 'numberfield',
                                    fieldLabel: 'Shipping/Handling Charge',
                                    id: 'fpot_shippingcharge',
                                    name: 'n[fpot_shippingcharge]', anchor: '98%',
                                    value: 0,
                                    tabIndex: 918,
                                    listeners: {
                                        change: function () {
                                            var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
                                            var fpot_hshippingcharge = parseFloat(fpot_shippingcharge) * 100 / parseFloat(Application.RetalineSalesRequest.Cache.total_initialnetamount);
                                            Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = fpot_hshippingcharge;
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    disabled: true,
                                    xtype: 'numberfield',
                                    fieldLabel: 'General Discounts',
                                    id: 'fpot_generalDiscount',
                                    name: 'n[fpot_generalDiscount]', anchor: '98%',
                                    value: 0,
                                    tabIndex: 919,
                                    listeners: {
                                        change: function () {
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'name',
                                    valueField: 'id',
                                    mode: 'local',
                                    id: 'po_gdiscountcalculs',
                                    forceSelection: true,
                                    fieldLabel: 'Amt /Percent',
                                    emptyText: 'Amount / Percentage',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2, tabIndex: 925,
                                    store: new Ext.data.JsonStore({
                                        fields: ['id', 'name'],
                                        data: [{id: 'Percentage', name: 'Percentage'}]
                                    }), listeners: {
                                        select: function () {
                                            justCaculatehcgd();
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'button',
                                    text: 'Apply',
                                    tabIndex: 921,
                                    id: 'applyGeneralDiscount',
                                    style: 'margin-top:17px;',
                                    handler: function () {
                                        if ((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0)) {
                                            justCaculatehcgd();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=srGenDiscounCalculate',
                                                method: 'POST',
                                                params: {
                                                    b2bsr_UniqueID: uniqueid,
                                                    total_initialnetamount: Application.RetalineSalesRequest.Cache.total_initialnetamount,
                                                    total_initialofferamount: Application.RetalineSalesRequest.Cache.total_initialofferamount,
                                                    fpot_gdiscpercent: Application.RetalineSalesRequest.Cache.fpot_gdiscpercent,
                                                    fpot_hshippingcharge: Application.RetalineSalesRequest.Cache.fpot_hshippingcharge,
                                                    adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                                    adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                                    adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                                    adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                                                    adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                    adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue(),
                                                    itemGST: Application.RetalineSalesRequest.Cache.stit_GST

                                                },
                                                success: function (response) {

                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success == true) {
                                                        Ext.getCmp('salesRequestItemGrid').store.load({
                                                            params: {
                                                                b2bsr_UniqueID: uniqueid
                                                            },
                                                            callback: function () {
                                                                Application.RetalineSalesRequest.Cache.totalhcg = tmp.hcg;
                                                                Ext.getCmp('fpot_poValue').focus(false, 100);
                                                                Application.RetalineSalesRequest.Cache.fpot_gdiscpercent = 0;
                                                                Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = 0;
                                                                Application.RetalineSalesRequest.Cache.isDiscountApplied = 1;
                                                            }
                                                        });


                                                    } else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }

                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", 'if you want to apply  General discount / Handling Charge please input an amount above 0.');
                                        }

                                    }
                                }]
                        }
                    ]
                }]});
        return panel;
    };
    var b2bCustomerWindow = function () {
        if (Ext.isEmpty(b2bCustomer_Window)) {
            var b2bCustomer_Window = new Ext.Window({
                id: 'upReturnQty_window',
                layout: 'fit',
                width: 400,
                title: 'B2B Customer',
                autoHeight: true,
                draggable: false, iconCls: 'my-icon98',
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [new Ext.FormPanel({
                        frame: true,
                        monitorValid: true,
                        id: 'upReturnQtyType_form',
                        height: 100,
                        items: [{
                                xtype: 'combo',
                                fieldLabel: 'B2B Customer',
                                hideLabel: true,
                                emptyText: 'Choose B2B Customer',
                                id: 'b2bsrchec_Customerid',
                                name: 'b2bsrchec_Customerid',
                                labelStyle: mandatory_label,
                                allowBlank: false,
                                mode: 'local',
                                typeAhead: true,
                                forceSelection: true,
                                editable: true,
                                anchor: '97%',
                                store: b2bSalesCustomerStore,
                                triggerAction: 'all',
                                minChars: 2,
                                displayField: 'b2b_Customer_Name',
                                valueField: 'b2b_Customer_ID',
                                hiddenName: 'b2bsrchec_Customerid',
                                tabIndex: 902,
                                listeners: {
                                    select: function () {
                                        var pe_party = Ext.getCmp('b2bsrchec_Customerid').getValue();

                                        Ext.Ajax.request({
                                            url: modURL + '&op=b2bSOCustomerDetails',
                                            method: 'POST',
                                            params: {b2bSOCustomer: pe_party},
                                            success: function (res) {
                                                var tmp = Ext.decode(res.responseText);
                                                console.log('tmp.bbsr_id', tmp.bbsr_id);
                                                if (tmp.bbsr_id > 0) {
                                                    b2bCustomer_Window.close();
                                                    editB2BSalesRequestNew(tmp.bbsr_id, 'SR');
                                                } else {
                                                    b2bCustomer_Window.close();
                                                    createB2BSalesRequest(pe_party);
                                                }

                                            },
                                            failure: function () {
                                                Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                            }
                                        });

                                    }
                                }
                            }]
                    })],
                buttons: [
//                    {
//                        text: 'Check',
//                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
//                        iconCls: 'my-icon1',
//                        handler: function () {
//                            var pe_party = Ext.getCmp('b2bsrchec_Customerid').getValue();
//                            Ext.Ajax.request({
//                                url: modURL + '&op=b2bSOCustomerDetails',
//                                method: 'POST',
//                                params: {b2bSOCustomer: pe_party},
//                                success: function (res) {
//                                    var tmp = Ext.decode(res.responseText);
//                                    if (tmp.bbsr_id > 0) {
//                                        editB2BSalesRequestNew(tmp.bbsr_id, 'SR');
//                                    } else {
//                                        createB2BSalesRequest();
//                                    }
//
//                                },
//                                failure: function () {
//                                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
//                                }
//                            });
//                        }
//                    }, 
                        {
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            b2bCustomer_Window.close();
                        }
                    }]
            });

        }
        b2bCustomer_Window.doLayout();
        b2bCustomer_Window.show(this);
        b2bCustomer_Window.center();
    };
    var editB2BSalesRequestNew = function (uniqueid, type) {

        var B2BSalesRequestForm = editB2BSalesRequestFormNew(uniqueid, type);
        var b2bSRrderWin = new Ext.Window({
            id: "b2bSalesRequestWinID",
            title: 'Sales Request',
            shadow: false,
            height: 520,
            width: 1180,
            modal: true,
            layout: 'fit',
            autoHeight: true,
            resizable: true,
            closable: true,
            listeners: {
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
                        var store = Ext.getCmp('salesRequestItemGridNew').getStore();
                        if (store.getCount()) {
                            Application.RetalineSalesRequest.Cache.b2bsr_UniqueID4edit = uniqueid;
                            saveB2BSalesRequestNew(uniqueid, type);
                        } else {
                            Ext.MessageBox.alert("Notification", 'Please fill B2B Sales Request details.');
                        }
                    }
                }]
        });

        var t = new Date();
        var t_stamp = t.format("YmdHis");
        Ext.Ajax.request({
            url: modURL + '&op=loadAdhocSalesRequest',
            params: {
                apikey: _SESSION.apikey,
                tstamp: t_stamp,
                uniqueid: uniqueid,
                type: type

            },
            method: 'POST',
            success: function (res) {
                var tmp = Ext.decode(res.responseText);
                Ext.getCmp('itemHistory').hide();
                Ext.getCmp('salesRequestItemGridNew').store.load({
                    params: {
                        b2bsr_UniqueID: uniqueid,
                        type: type
                    },
                    callback: function () {
                        Application.RetalineSalesRequest.Cache.sradhoc_updatedon = tmp.adhoc_updatedon;
                        console.log('focus3');
                        Ext.getCmp('b2bsr_Customerid').focus(false, 100);
                        Ext.getCmp('fpot_poValue').focus(false, 100);
                        Ext.getCmp('b2bSalesRequestItems').reset();
                        Ext.getCmp('b2bso_itemmrp').reset();
                        Ext.getCmp('fpot_itemoffrrate').reset();
                        Ext.getCmp('fpot_itemoffrrateExcT').reset();
                        Ext.getCmp('b2bsr_itemqty').reset();
                        Ext.getCmp('fpot_amount').reset();
                        Ext.getCmp('b2bsr_itemoffrqty').reset();
                        Ext.getCmp('itemUnitForm').reset();
                        Ext.getCmp('fpot_itemaddidisc').reset();
                        Ext.getCmp('po_idiscountcalculs').reset();
                        Ext.getCmp('fpot_netamount').reset();
                        Ext.getCmp('fpot_notes').reset();
                        Ext.getCmp('po_payment_terms').reset();
                        Ext.getCmp('po_payment_value').reset();
                        Application.RetalineSalesRequest.Cache.Pouid = uniqueid;
                        Ext.getCmp('uniqueId').setValue(uniqueid);
                        Ext.getCmp('b2bsr_Customerid').setReadOnly(true);
                        Ext.getCmp('b2bsr_Customerid').setHideTrigger(true);
                        Ext.getCmp('b2bsr_Customerid').setValue(tmp.adhoc_customer);
                        Ext.getCmp('po_payment_terms').setValue(tmp.adhoc_paymentTerms);
                        Ext.getCmp('po_payment_value').setValue(tmp.adhoc_paymentValue);
                        Ext.getCmp('fpo_validityType').setValue(tmp.adhoc_validityType);
                        Ext.getCmp('fpot_shippingcharge').setValue(tmp.adhoc_shippingcharge);
                        Ext.getCmp('fpot_generalDiscount').setValue(tmp.adhoc_gdiscount);
                        Ext.getCmp('po_gdiscountcalculs').setValue(tmp.adhoc_gdiscounttype);

                        Ext.getCmp('po_number').setValue(tmp.bbsr_SRNumber);
                        Ext.getCmp('po_date').setValue(tmp.adhoc_createdon);
                        //                            Ext.getCmp('fpo_validityType').getStore().load();
                        //                            Ext.getCmp('po_gdiscountcalculs').getStore().load();
                        Ext.getCmp('b2bSalesRequestItems').getStore().load();
                        Ext.getCmp('po_payment_terms').getStore().load({
                            params: {
                                stpa_id: tmp.adhoc_vendor
                            }
                        });
                    }
                });
            }
        });


        b2bSRrderWin.doLayout();
        b2bSRrderWin.show();
        b2bSRrderWin.center();
    };
    var editB2BSalesRequestFormNew = function (uniqueid, type) {
        var purUni = '';
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'newPOFormForVendor',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            xtype: 'hidden', id: 'uniqueId', name: 'uniqueId'
                        }, {
                            layout: 'form',
                            columnWidth: 0.15,
                            items: []
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelAlign: 'left',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'B2B Customer',
                                    hideLabel: true,
                                    emptyText: 'Choose B2B Customer',
                                    id: 'b2bsr_Customerid',
                                    name: 'b2bsr_Customerid',
                                    labelStyle: mandatory_label,
                                    allowBlank: false,
                                    mode: 'local',
                                    typeAhead: true,
                                    forceSelection: true,
                                    editable: true, anchor: '97%',
                                    store: b2bSalesCustomerStore,
                                    triggerAction: 'all',
                                    minChars: 2,
                                    displayField: 'b2b_Customer_Name',
                                    valueField: 'b2b_Customer_ID',
                                    hiddenName: 'b2bsr_Customerid',
                                    tabIndex: 902,
                                    listeners: {
                                        focus: function () {
                                            var pe_party = Ext.getCmp('b2bsr_Customerid').getValue();

                                            Ext.Ajax.request({
                                                url: modURL + '&op=b2bSOCustomerDetails',
                                                method: 'POST',
                                                params: {b2bSOCustomer: pe_party},
                                                success: function (res) {
                                                    var tmp = Ext.decode(res.responseText);
                                                    Ext.getCmp('vendorDetails').show();

                                                    Ext.getCmp('b2bSRContactPerson').setValue(tmp.b2b_Customer_Incharge);
                                                    Ext.getCmp('b2bSRCustomerMobile').setValue(tmp.b2b_Customer_Mobile);
                                                    Ext.getCmp('b2b_Customer_gst').setValue(tmp.b2b_Customer_gst);
                                                    Ext.getCmp('b2b_Customer_dlno1').setValue(tmp.b2b_Customer_dlno1);
                                                    Ext.getCmp('b2b_Customer_dlno2').setValue(tmp.b2b_Customer_dlno2);

                                                    Ext.getCmp('po_number').setValue(tmp.bbsr_SRNumber);
                                                    Ext.getCmp('po_date').setValue(tmp.b2bsr_requestDate);

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
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 70,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'SR Number',
                                    id: 'po_number',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_number]', anchor: '97%',
                                    value: 'Not Genereated',
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
                                    fieldLabel: 'SR Date', id: 'po_date',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_date]',
                                    value: 'Not Genereated',
                                    anchor: '97%',
                                }
                            ]}, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .15,
                            labelAlign: 'left',
                            labelWidth: 65,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Ordered By',
                                    id: 'po_ordered_by',
                                    style: {'font-weight': 'bold'},
                                    name: 'n[po_ordered_by]', anchor: '97%',
                                    value: _SESSION.FirstName,
                                }
                            ]
                        }, {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: 0.20,
                            labelAlign: 'left',
                            labelWidth: 50,
                            items: []
                        }]
                }, {
                    xtype: 'fieldset',
                    title: 'B2B Customer Details',
                    collapsible: true,
                    columnWidth: 1,
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    id: 'vendorDetails',
                    hidden: true,
                    items: [{
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            labelAlign: 'top',
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
                            columnWidth: .20,
                            labelAlign: 'top',
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
                        },
                        {
                            layout: 'form',
                            style: 'margin-bottom:3px;',
                            columnWidth: .20,
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'GST',
                                    id: 'b2b_Customer_gst',
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
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'DL1',
                                    id: 'b2b_Customer_dlno1',
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
                            labelAlign: 'top',
                            labelWidth: 37,
                            items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'DL2',
                                    id: 'b2b_Customer_dlno2',
                                    style: {
                                        'font-weight': 'bold'
                                    },
                                    anchor: '97%'
                                }]
                        }]
                }, {
                    xtype: 'fieldset',
                    layout: 'column',
                    collapsible: true,
                    title: 'Item Previous Rates',
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
                                    id: 'itemSmaalStockUnit',
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
                                    fieldLabel: 'HSN', id: 'itemHSN',
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
                                    fieldLabel: 'GST', id: 'itemGST',
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
                                    fieldLabel: 'Least SKU MRP',
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
                                    fieldLabel: 'Last Rate',
                                    id: 'lastPurchaseRate',
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
                                    fieldLabel: 'Lowest SP',
                                    id: 'itemLPP',
                                    hidden: true,
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
                                    fieldLabel: 'Item Count',
                                    id: 'itemCount',
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
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Min Qty',
                                    id: 'itemMinQty',
                                    style: {'font-weight': 'bold'},
                                    anchor: '97%'
                                }
                            ]
                        }]
                }, {
                    layout: 'column',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'column',
                            style: 'margin-bottom:3px;',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.25,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Items',
                                            id: 'b2bSalesRequestItems',
                                            name: 'b2bSalesRequestItems',
                                            labelStyle: mandatory_label,
                                            allowBlank: false,
                                            labelAlign: 'top',
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: b2bCustomerItemStore,
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'stit_SKU',
                                            valueField: 'stit_ID',
                                            hiddenName: 'b2bSalesRequestItems',
                                            tabIndex: 904,
                                            listeners: {
                                                select: function () {
                                                    var b2bSalesRequestItems = Ext.getCmp('b2bSalesRequestItems').getValue();
                                                    var b2bsr_Customerid = Ext.getCmp('b2bsr_Customerid').getValue();
                                                    Ext.Ajax.request({
                                                        url: modURL + '&op=itemHistory',
                                                        method: 'POST',
                                                        params: {b2bSalesRequestItems: b2bSalesRequestItems, b2bsr_Customerid: b2bsr_Customerid},
                                                        success: function (res) {
                                                            var tmp = Ext.decode(res.responseText);
                                                            console.log('tmp.minQty',tmp.minQty);
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
                                                            Ext.getCmp('itemCount').setValue(tmp.itemCount);
                                                            Ext.getCmp('itemMinQty').setValue(tmp.minQty);
                                                            //Ext.getCmp('itemUnit').setValue(tmp.csb_package_type_name);
                                                            Ext.getCmp('itemUnitForm').setValue(tmp.itemUnitForm);
                                                            Ext.getCmp('itemSmaalStockUnit').setValue(tmp.itemSmaalStockUnit);
                                                            Ext.getCmp('b2bso_itemmrp').setValue(tmp.skuMRP);
                                                            Ext.getCmp('fpot_itemoffrrate').setValue(tmp.skuRate);
                                                            console.log('tmp.skuRate', tmp.skuRate);
                                                            console.log('tmp.stit_GST', tmp.stit_GST);
                                                            Ext.getCmp('lastPurchaseRate').setValue(tmp.lastPurchaseRate);
                                                            var offerateET = tmp.skuRate * 100 / (100 + parseInt(tmp.stit_GST));
                                                            Ext.getCmp('fpot_itemoffrrateExcT').setValue(offerateET);
                                                            Application.RetalineSalesRequest.Cache.stit_GST = tmp.stit_GST;
                                                            Application.RetalineSalesRequest.Cache.itemCount = tmp.itemCount;

                                                        },
                                                        failure: function () {
                                                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                                        }
                                                    });

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.09,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'MRP (Inc. of GST)',
                                            id: 'b2bso_itemmrp',
                                            align: 'right',
                                            name: 'n[b2bso_itemmrp]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true,
                                            tabIndex: 905,
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.05,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Rate',
                                            align: 'right',
                                            id: 'fpot_itemoffrrate',
                                            name: 'n[fpot_itemoffrrate]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true,
                                            tabIndex: 906,
                                            value: 0,
                                            listeners: {
                                                change: function (e) {
                                                    var b2bsr_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
                                                    var amount = b2bsr_itemqty * fpot_itemoffrrate;
                                                    Ext.getCmp('fpot_amount').setValue(amount);
                                                    console.log('e', e);
//                                            var taxRate = this.data.stit_GST;
//                                            var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrate').getValue();
//                                            var fpot_itemoffrrateet = (parseFloat(fpot_itemoffrrate) * 100) / (100 + parseFloat(taxRate));
                                                    //                                            Ext.getCmp('fpot_itemoffrrateet').setValue(fpot_itemoffrrateet);
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.08,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Rate (Excl.GST)',
                                            align: 'right',
                                            id: 'fpot_itemoffrrateExcT',
                                            name: 'n[fpot_itemoffrrateExcT]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 907,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Qty',
                                            align: 'right',
                                            id: 'b2bsr_itemqty',
                                            name: 'n[b2bsr_itemqty]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 908,
                                            listeners: {
                                                change: function () {
                                                    var b2bsr_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (b2bsr_itemqty <= Application.RetalineSalesRequest.Cache.itemCount) {
                                                        var fpot_itemoffrrate = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                        var amount = b2bsr_itemqty * fpot_itemoffrrate;
                                                        Ext.getCmp('fpot_amount').setValue(amount);
                                                    } else {
                                                        Ext.getCmp('b2bsr_itemqty').reset();

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
                                            fieldLabel: 'Amount',
                                            align: 'right',
                                            id: 'fpot_amount',
                                            name: 'n[fpot_amount]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            //fieldLabel: 'Product Discount (Nos)',
                                            fieldLabel: 'Product Dis.',
                                            align: 'right',
                                            id: 'b2bsr_itemoffrqty',
                                            name: 'n[b2bsr_itemoffrqty]',
                                            anchor: '98%',
                                            tabIndex: 909,
                                            value: 0,
                                            listeners: {
                                                change: function () {
                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.05,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Unit',
                                            id: 'itemUnitForm',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 910,
                                            readOnly: true
                                        }]
                                }, {
                                    layout: 'form',
                                    columnWidth: 0.10,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'numberfield',
                                            align: 'right',
                                            //fieldLabel: 'Addl Discounts (Rs)',
                                            fieldLabel: 'Item Discounts',
                                            id: 'fpot_itemaddidisc',
                                            name: 'n[fpot_itemaddidisc]',
                                            anchor: '98%',
                                            value: 0,
                                            tabIndex: 911,
                                            listeners: {
                                                change: function () {
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
                                            id: 'po_idiscountcalculs',
                                            forceSelection: true,
                                            fieldLabel: 'Amt /Percent',
                                            emptyText: 'Amount / Percentage',
                                            anchor: '98%',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 912,
                                            store: new Ext.data.JsonStore({
                                                fields: ['id', 'name'],
                                                data: [{id: 'Amount', name: 'Amount'}, {id: 'Percentage', name: 'Percentage'}]
                                            }), listeners: {
                                                select: function () {
                                                    var fpot_netamount, fpot_netRate;
                                                    var po_idiscountcalculs = Ext.getCmp('po_idiscountcalculs').getRawValue();
                                                    var fpot_amount = Ext.getCmp('fpot_amount').getValue();
                                                    var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    var fpot_itemaddidisc = Ext.getCmp('fpot_itemaddidisc').getValue();
                                                    var fpot_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (po_idiscountcalculs == 'Amount') {
                                                        fpot_netRate = fpot_itemoffrrateExcT - parseFloat(fpot_itemaddidisc);
                                                    } else {
                                                        fpot_netRate = parseFloat(fpot_itemoffrrateExcT) - (parseFloat(fpot_itemoffrrateExcT) * parseFloat(fpot_itemaddidisc) / 100);
                                                    }
                                                    fpot_netRate = Math.round((fpot_netRate + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netRate', fpot_netRate);
                                                    fpot_netamount = fpot_netRate * fpot_itemqty;
                                                    console.log('fpot_netamount1', fpot_netamount);
                                                    //fpot_netamount = fpot_netamount + (fpot_netamount * Application.RetalineSalesRequest.Cache.stit_GST / 100);
                                                    console.log('fpot_netamount2', fpot_netamount);
                                                    fpot_netamount = Math.round((fpot_netamount + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netamount3', fpot_netamount);
                                                    Ext.getCmp('fpot_netamount').setValue(fpot_netamount);

                                                }
                                            }
                                        }]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.10,
                                    items: [{
                                            xtype: 'numberfield',
                                            fieldLabel: 'Net Amt',
                                            id: 'fpot_netamount',
                                            align: 'right',
                                            name: 'n[fpot_netamount]',
                                            anchor: '98%',
                                            allowBlank: false,
                                            tabIndex: 913,
                                            readOnly: true,
                                            listeners: {
                                                focus: function () {
                                                    var fpot_netamount, fpot_netRate;
                                                    var po_idiscountcalculs = Ext.getCmp('po_idiscountcalculs').getRawValue();
                                                    var fpot_amount = Ext.getCmp('fpot_amount').getValue();
                                                    var fpot_itemaddidisc = Ext.getCmp('fpot_itemaddidisc').getValue();
                                                    var fpot_itemoffrrateExcT = Ext.getCmp('fpot_itemoffrrateExcT').getValue();
                                                    var fpot_itemqty = Ext.getCmp('b2bsr_itemqty').getValue();
                                                    if (po_idiscountcalculs == 'Amount') {
                                                        fpot_netRate = fpot_itemoffrrateExcT - parseFloat(fpot_itemaddidisc);
                                                    } else {
                                                        fpot_netRate = parseFloat(fpot_itemoffrrateExcT) - (parseFloat(fpot_itemoffrrateExcT) * parseFloat(fpot_itemaddidisc) / 100);
                                                    }
                                                    fpot_netRate = Math.round((fpot_netRate + Number.EPSILON) * 100) / 100;
                                                    fpot_netamount = fpot_netRate * fpot_itemqty;
                                                    //fpot_netamount = fpot_netamount + (fpot_netamount * Application.RetalineSalesRequest.Cache.stit_GST / 100);
                                                    fpot_netamount = Math.round((fpot_netamount + Number.EPSILON) * 100) / 100;
                                                    console.log('fpot_netamount', fpot_netamount);
                                                    Ext.getCmp('fpot_netamount').setValue(fpot_netamount);
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
                                    columnWidth: .70,
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Notes',
                                            id: 'fpot_notes',
                                            name: 'n[fpot_notes]',
                                            anchor: '98%',
                                            tabIndex: 914
                                        }
                                    ]
                                }, {
                                    layout: 'form',
                                    labelAlign: 'top',
                                    columnWidth: 0.10,
                                    items: [{
                                            xtype: 'button',
                                            text: 'Add',
                                            tabIndex: 915,
                                            id: 'addItemsToPO',
                                            iconCls: 'finascop_add',
                                            style: 'margin-top:17px;',
                                            handler: function () {
                                                if (Ext.getCmp('fpot_netamount').getValue() > 0) {
                                                    Ext.Ajax.request({
                                                        waitMsg: 'Processing',
                                                        method: 'POST',
                                                        url: modURL + '&op=saveB2BSRItemDetailsNew',
                                                        params: {
                                                            b2bsr_UniqueID: uniqueid,
                                                            b2bsr_Customerid: Ext.getCmp('b2bsr_Customerid').getValue(),
                                                            b2bsr_itemid: Ext.getCmp('b2bSalesRequestItems').getValue(),
                                                            b2bsr_itemname: Ext.getCmp('b2bSalesRequestItems').getRawValue(),
                                                            b2bsr_itemmrp: Ext.getCmp('b2bso_itemmrp').getValue(),
                                                            b2bsr_itemPkg: Ext.getCmp('itemUnitForm').getValue(),
                                                            b2bsr_itemqty: Ext.getCmp('b2bsr_itemqty').getValue(),
                                                            b2bsr_itemoffrqty: Ext.getCmp('b2bsr_itemoffrqty').getValue(),
                                                            b2bsr_itemrate: Ext.getCmp('fpot_itemoffrrate').getValue(),
                                                            //fpot_itemoffrrateet: Ext.getCmp('fpot_itemoffrrateet').getValue(),
                                                            b2bsr_itemaddidisc: Ext.getCmp('fpot_itemaddidisc').getValue(),
                                                            //fpot_effectiverate: Ext.getCmp('fpot_effectiverate').getValue(),
                                                            b2bsr_amount: Ext.getCmp('fpot_amount').getValue(),
                                                            b2bsr_netamount: Ext.getCmp('fpot_netamount').getValue(),
                                                            b2bsr_idiscountcalculs: Ext.getCmp('po_idiscountcalculs').getValue(),
                                                            b2bsr_notes: Ext.getCmp('fpot_notes').getValue(),
                                                            sradhoc_updatedon: Application.RetalineSalesRequest.Cache.sradhoc_updatedon,
                                                            adhoc_srValue: Ext.getCmp('fpot_poValue').getValue(),
                                                            adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                                            adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                                            adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                                            adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                                                            adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                            adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue(),
                                                            b2bsr_itemrateet: Ext.getCmp('fpot_itemoffrrateExcT').getValue(),
                                                            stit_GST: Application.RetalineSalesRequest.Cache.stit_GST
                                                        },
                                                        success: function (response) {
                                                            var tmp = Ext.decode(response.responseText);
                                                            if (tmp.success === true)
                                                            {
                                                                Application.example.msg('Success', tmp.msg);
                                                                Application.RetalineSalesRequest.Cache.sradhoc_updatedon = tmp.date;
                                                                Ext.getCmp('itemHistory').hide();
                                                                Ext.getCmp('salesRequestItemGridNew').store.load({
                                                                    params: {
                                                                        b2bsr_UniqueID: uniqueid
                                                                    },
                                                                    callback: function () {
                                                                        Ext.getCmp('fpot_poValue').focus(false, 100);
                                                                        Ext.getCmp('b2bSalesRequestItems').reset();
                                                                        Ext.getCmp('b2bso_itemmrp').reset();
                                                                        Ext.getCmp('b2bsr_itemqty').reset();
                                                                        Ext.getCmp('b2bsr_itemoffrqty').reset();
                                                                        Ext.getCmp('fpot_itemoffrrate').reset();
                                                                        Ext.getCmp('fpot_itemaddidisc').reset();
                                                                        Ext.getCmp('fpot_amount').reset();
                                                                        Ext.getCmp('fpot_netamount').reset();
                                                                        Ext.getCmp('po_idiscountcalculs').reset();
                                                                        Ext.getCmp('fpot_notes').reset();
                                                                    }
                                                                });
                                                                Ext.getCmp('salesRequestItemGridNew').getStore().load({
                                                                    params: {
                                                                        b2bsr_UniqueID: uniqueid
                                                                    }
                                                                });

                                                            } else
                                                            {
                                                                Ext.MessageBox.alert("Failure", "Error moving Data, Kindly reload.");
                                                                //Ext.getCmp('b2bSalesRequestWinID').close();

                                                            }
                                                        },
                                                        failure: function (response) {
                                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                                            Ext.MessageBox.alert('Error', tmp.msg);
                                                        }
                                                    });
                                                } else {
                                                    Ext.Msg.alert("Notification.", 'Please fill all the fields');
                                                }

                                            }
                                        }]
                                }]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'po_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [
                                        salesRequestItemGridNew()
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
                            columnWidth: 0.75,
                            items: [{xtype: 'label',
                                    html: '&nbsp;'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.15,
                            labelWidth: 35,
                            items: [{
                                    xtype: 'numberfield',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    fieldLabel: 'Total',
                                    id: 'fpot_poValue',
                                    name: 'fpot_poValue',
                                    allowNegative: true,
                                    allowDecimals: true,
                                    tabIndex: 914,
                                    readOnly: true,
                                    anchor: '99%',
                                    listeners: {
                                        focus: function (txtbx) {
                                            var itemStore = Ext.getCmp('salesRequestItemGridNew').getStore();
                                            var fpot_netamount = itemStore.sum('b2bsr_netamount');
                                            var total_initialnetamount = itemStore.sum('b2bsr_initialnetamount');
                                            var total_initialofferamount = itemStore.sum('b2bsr_amount');
                                            Application.RetalineSalesRequest.Cache.total_initialnetamount = total_initialnetamount;
                                            Application.RetalineSalesRequest.Cache.total_initialofferamount = total_initialofferamount;
                                            var totalItems = itemStore.getCount();
                                            if (parseFloat(Application.RetalineSalesRequest.Cache.totalhcg) > 0 && Ext.getCmp('fpot_shippingcharge').getValue() > 0) {
                                                Application.RetalineSalesRequest.Cache.totalhcg = Application.RetalineSalesRequest.Cache.totalhcg;
                                            } else {
                                                Application.RetalineSalesRequest.Cache.totalhcg = 0;
                                            }
                                            fpot_netamount = fpot_netamount + Application.RetalineSalesRequest.Cache.totalhcg;
                                            console.log('fpot_netamountpovalue2', fpot_netamount);
                                            Ext.getCmp('fpot_poValue').setValue(fpot_netamount);
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            items: [{xtype: 'label',
                                    html: '&nbsp;'
                                }]}, ]
                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'ptc_name',
                                    valueField: 'ptc_id',
                                    mode: 'local',
                                    id: 'po_payment_terms',
                                    forceSelection: true,
                                    fieldLabel: 'Credit Terms',
                                    emptyText: 'Credit Terms',
                                    anchor: '98%',
                                    allowBlank: false,
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2, tabIndex: 915,
                                    store: pmtTermsStore,
                                    listeners: {
                                        select: function (combo, record) {
                                            var type = record.data.ptc_name;
                                            if (type == 'Custom') {
                                                Ext.getCmp('po_payment_value').show();
                                                Ext.getCmp('po_payment_value').allowBlank = true;
                                            } else {
                                                Ext.getCmp('po_payment_value').hide();
                                                Ext.getCmp('po_payment_value').allowBlank = false;
                                            }
                                        }

                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    fieldLabel: 'Credit Days',
                                    xtype: 'numberfield',
                                    id: 'po_payment_value',
                                    labelAlign: 'top',
                                    allowBlank: false,
                                    name: 'po_payment_value', hidden: true,
                                    anchor: '98%',
                                    tabIndex: 916
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'fpo_validityType',
                                    forceSelection: true,
                                    fieldLabel: 'Validity',
                                    emptyText: 'Validity',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2, tabIndex: 917,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: '15', ptname: '15 Days'},
                                            {ptid: '30', ptname: '30 Days'}, {ptid: '60', ptname: '60 Days'}, {ptid: '90', ptname: '90 Days'}]
                                    })
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    disabled: true,
                                    xtype: 'numberfield',
                                    fieldLabel: 'Shipping/Handling Charge',
                                    id: 'fpot_shippingcharge',
                                    name: 'n[fpot_shippingcharge]', anchor: '98%',
                                    value: 0,
                                    tabIndex: 918,
                                    listeners: {
                                        change: function () {
                                            var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
                                            var fpot_hshippingcharge = parseFloat(fpot_shippingcharge) * 100 / parseFloat(Application.RetalineSalesRequest.Cache.total_initialnetamount);
                                            Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = fpot_hshippingcharge;
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    disabled: true,
                                    xtype: 'numberfield',
                                    fieldLabel: 'General Discounts',
                                    id: 'fpot_generalDiscount',
                                    name: 'n[fpot_generalDiscount]', anchor: '98%',
                                    value: 0,
                                    tabIndex: 919,
                                    listeners: {
                                        change: function () {
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'combo',
                                    displayField: 'name',
                                    valueField: 'id',
                                    mode: 'local',
                                    id: 'po_gdiscountcalculs',
                                    forceSelection: true,
                                    fieldLabel: 'Amt /Percent',
                                    emptyText: 'Amount / Percentage',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2, tabIndex: 925,
                                    store: new Ext.data.JsonStore({
                                        fields: ['id', 'name'],
                                        data: [{id: 'Percentage', name: 'Percentage'}]
                                    }), listeners: {
                                        select: function () {
                                            justCaculatehcgd();
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    disabled: true,
                                    xtype: 'button',
                                    text: 'Apply',
                                    tabIndex: 921,
                                    id: 'applyGeneralDiscount',
                                    style: 'margin-top:17px;',
                                    handler: function () {
                                        if ((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0)) {
                                            justCaculatehcgd();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=srGenDiscounCalculate',
                                                method: 'POST',
                                                params: {
                                                    b2bsr_UniqueID: uniqueid,
                                                    total_initialnetamount: Application.RetalineSalesRequest.Cache.total_initialnetamount,
                                                    total_initialofferamount: Application.RetalineSalesRequest.Cache.total_initialofferamount,
                                                    fpot_gdiscpercent: Application.RetalineSalesRequest.Cache.fpot_gdiscpercent,
                                                    fpot_hshippingcharge: Application.RetalineSalesRequest.Cache.fpot_hshippingcharge,
                                                    adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                                    adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                                    adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                                    adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                                                    adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                    adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue(),
                                                    itemGST: Application.RetalineSalesRequest.Cache.stit_GST

                                                },
                                                success: function (response) {

                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success == true) {
                                                        Ext.getCmp('salesRequestItemGridNew').store.load({
                                                            params: {
                                                                b2bsr_UniqueID: uniqueid
                                                            },
                                                            callback: function () {
                                                                Application.RetalineSalesRequest.Cache.totalhcg = tmp.hcg;
                                                                Ext.getCmp('fpot_poValue').focus(false, 100);
                                                                Application.RetalineSalesRequest.Cache.fpot_gdiscpercent = 0;
                                                                Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = 0;
                                                                Application.RetalineSalesRequest.Cache.isDiscountApplied = 1;
                                                            }
                                                        });


                                                    } else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }

                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", 'if you want to apply  General discount / Handling Charge please input an amount above 0.');
                                        }

                                    }
                                }]
                        }
                    ]}]
        });
        return panel;
    };
    var salesRequestItemGridNew = function () {

        var PurchaseOrderItemStoreNew = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listSrDetailsStoreNew',
                    method: 'post'
                }),
                fields: ['b2bsr_itemid', 'b2bsr_itemname', 'b2bsr_itemmrp', 'b2bsr_itemqty', 'b2bsr_itemoffrqty', 'b2bsr_totalqty', 'b2bsr_balanceqty', 'b2bsr_itemrate', 'itemDisc', 'bbsr_id',
                    'b2bsr_itemaddidisc', 'b2bsr_effectiverate', 'b2bsr_idiscountcalculs', {name: 'b2bsr_amount', type: 'float'}, {name: 'b2bsr_netamount', type: 'float'}, {name: 'b2bsr_initialnetamount', type: 'float'}, 'b2bsr_gendiscount', 'b2bsr_shippingcharge'],
                totalProperty: 'totalCount',
                root: 'data', remoteSort: true, autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.poId = Application.Finascop_Accounts_Purchase.Cache.POID;
                    }
                }

            });
            return itemStore;
        };
        var itemGridStore = PurchaseOrderItemStoreNew();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemGridStore,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'salesRequestItemGridNew',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Item',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bsr_itemname',
                    tooltip: 'Item',
                    width: 130

                }, {
                    header: 'MRP (Inc. of GST)',
                    sortable: true,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'b2bsr_itemmrp',
                    align: 'right',
                    tooltip: 'MRP (Inc. of GST)',
                    width: 60
                }, {
                    header: 'Rate',
                    sortable: true,
                    dataIndex: 'b2bsr_itemrate',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    tooltip: 'Rate',
                    align: 'right',
                    width: 60
                }, {
                    header: 'Qty',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bsr_totalqty',
                    tooltip: 'Qty',
                    align: 'right',
                    width: 50
                }, {
                    header: 'Amount',
                    sortable: true,
                    hideable: false,
                    xtype: 'finascopcurrency',
                    dataIndex: 'b2bsr_amount',
                    format: FINASCOP_CURRENCY_FORMAT,
                    tooltip: 'Amount',
                    align: 'right',
                    width: 60
                }, {
                    header: 'Product Dis.',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bsr_itemoffrqty',
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
                    hidden: true,
                    dataIndex: 'b2bsr_shippingcharge',
                    tooltip: 'Shipping / Handling Charge',
                    align: 'right',
                    width: 50
                }, {
                    header: 'General Dis.',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'b2bsr_gendiscount',
                    tooltip: 'General Discount',
                    align: 'right',
                    width: 50
                },
                {
                    header: 'Net Amt',
                    sortable: true,
                    dataIndex: 'b2bsr_netamount',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Net Amt',
                    width: 60
                }, {
                    header: 'Initial Net Amt',
                    hidden: true,
                    dataIndex: 'b2bsr_initialnetamount',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'Initial Net Amt',
                    width: 60
                },
                {
                    header: 'ESR',
                    sortable: true,
                    dataIndex: 'b2bsr_effectiverate',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    tooltip: 'ESR',
                    width: 60
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
                                        Ext.Ajax.request({
                                            url: modURL + '&op=deleteB2BItemFromSR',
                                            method: 'POST',
                                            params: {
                                                itemid: record.get('b2bsr_itemid'),
                                                uid: 0,
                                                srId: record.get('bbsr_id')
                                            },
                                            success: function (response) {
                                                var res = Ext.decode(response.responseText);
                                                if (res.success === true)
                                                {
                                                    Ext.MessageBox.alert('Success', 'Removed item from Sales Request.', function (btn) {
                                                        Ext.getCmp('salesRequestItemGridNew').getStore().load({
                                                            params: {
                                                                b2bsr_UniqueID: record.get('bbsr_id')
                                                            },
                                                            callback: function () {
                                                                console.log('here');
                                                                Ext.getCmp('fpot_poValue').focus(false, 100);
                                                            }
                                                        });


                                                    });
                                                } else
                                                {
                                                    Ext.MessageBox.alert('Failed');
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        }]
                }
            ],
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
    var saveB2BSalesRequestNew = function (b2bsrid, typefrom) {
        var po_payment_terms = Ext.getCmp('po_payment_terms').getValue();
        var po_payment_value = Ext.getCmp('po_payment_value').getValue();
        var fpo_validityType = Ext.getCmp('fpo_validityType').getValue();
        var customerId = Ext.getCmp('b2bsr_Customerid').getValue();
        var customerName = Ext.getCmp('b2bsr_Customerid').getRawValue();
        if (Application.RetalineSalesRequest.Cache.isDiscountApplied == 1) {
            var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
            var fpot_generalDiscount = Ext.getCmp('fpot_generalDiscount').getValue();
            var po_gdiscountcalculs = Ext.getCmp('po_gdiscountcalculs').getValue();
        } else {
            Ext.getCmp('fpot_shippingcharge').setValue(0);
            Ext.getCmp('fpot_generalDiscount').setValue(0);
            Ext.getCmp('po_gdiscountcalculs').setValue('');
            var fpot_shippingcharge = 0
            var fpot_generalDiscount = 0;
            var po_gdiscountcalculs = 0;
        }
        if (Ext.isEmpty(Application.RetalineSalesRequest.Cache.b2bsr_UniqueID)) {
            Application.RetalineSalesRequest.Cache.b2bsr_UniqueID = Application.RetalineSalesRequest.Cache.b2bsr_UniqueID4edit;
        }

        //if (!Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue() && Ext.getCmp('fpo_validityType').getValue()) && (Ext.getCmp('fpot_poValue').getValue() > 0)) {

        // if (((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0) && Application.RetalineSalesRequest.Cache.isDiscountApplied == 1) || ((Ext.getCmp('fpot_generalDiscount').getValue() == 0) || (Ext.getCmp('fpot_shippingcharge').getValue() == 0) && Application.RetalineSalesRequest.Cache.isDiscountApplied == 0)) {

        Ext.Ajax.request({
            url: modURL + '&op=saveSalesRequestinSR',
            method: 'POST',
            params: {
                po_payment_terms: po_payment_terms,
                po_payment_value: po_payment_value,
                fpo_validityType: fpo_validityType,
                customerId: customerId,
                customerName: customerName,
                uid: Application.RetalineSalesRequest.Cache.b2bsr_UniqueID,
                poValue: Ext.getCmp('fpot_poValue').getValue(),
                fpot_gdiscpercent: Application.RetalineSalesRequest.Cache.fpot_gdiscpercent,
                fpot_hshippingcharge: Application.RetalineSalesRequest.Cache.fpot_hshippingcharge,
                fpot_shippingcharge: fpot_shippingcharge,
                fpot_generalDiscount: fpot_generalDiscount,
                po_gdiscountcalculs: po_gdiscountcalculs,
                total_initialnetamount: Application.RetalineSalesRequest.Cache.total_initialnetamount,
                total_initialofferamount: Application.RetalineSalesRequest.Cache.total_initialofferamount,
                b2bsrid: b2bsrid,
                typefrom: typefrom

            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true) {
                    Application.example.msg('Success', res.msg);
                    Ext.getCmp('b2bSalesRequestWinID').close();
                    if (type == 'add') {
                        recs_per_page = updateRecsPerPage(Ext.getCmp('B2BSalesRequestGrid'));
                        Ext.getCmp('B2BSalesRequestGrid').store.reload({
                            params: {
                                start: 0,
                                limit: recs_per_page
                            }
                        });
                    } else {
                        recs_per_page = updateRecsPerPage(Ext.getCmp('gridpaneSalesRequestadhoc'));
                        Ext.getCmp('gridpaneSalesRequestadhoc').store.reload({
                            params: {
                                start: 0,
                                limit: recs_per_page
                            }
                        });
                    }

                } else {
                    Ext.MessageBox.alert("Notification", response.responseText);
                    // Ext.getCmp('b2bSalesRequestWinID').close();
                }
            },
            failure: function (response) {
                Ext.MessageBox.alert('Error', response.responseText);
            }
        });
        //} else {
        //Ext.MessageBox.alert("Notification", "Mistake in Discount applied.");
        //}


        //} else {
        //Ext.MessageBox.alert("Notification", 'Please fill all Payment Terms , Validity ');
        //}
    };
    var viewB2BSalesOrderForm = function (bbsr_id) {
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
                            columnWidth: .30,
                            labelAlign: 'left',
                            labelWidth: 100,
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
                    title: 'Items',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [
                                        editSalesOrderItemGrid(bbsr_id)
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
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'ptc_name',
                                    valueField: 'ptc_id',
                                    mode: 'local',
                                    id: 'po_payment_terms',
                                    forceSelection: true,
                                    fieldLabel: 'Credit Terms',
                                    emptyText: 'Credit Terms',
                                    anchor: '98%',
                                    allowBlank: false,
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 915,
                                    store: pmtTermsStore,
                                    listeners: {
                                        select: function (combo, record) {
                                            var type = record.data.ptc_name;
                                            if (type == 'Custom') {
                                                Ext.getCmp('po_payment_value').show();
                                                Ext.getCmp('po_payment_value').allowBlank = true;
                                            } else {
                                                Ext.getCmp('po_payment_value').hide();
                                                Ext.getCmp('po_payment_value').allowBlank = false;
                                            }
                                        }

                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    fieldLabel: 'Credit Days',
                                    xtype: 'numberfield',
                                    id: 'po_payment_value',
                                    labelAlign: 'top',
                                    allowBlank: false,
                                    name: 'po_payment_value',
                                    hidden: true,
                                    anchor: '98%',
                                    tabIndex: 916
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.20,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'fpo_validityType',
                                    forceSelection: true,
                                    fieldLabel: 'Validity',
                                    emptyText: 'Validity',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 917,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: '15', ptname: '15 Days'},
                                            {ptid: '30', ptname: '30 Days'}, {ptid: '60', ptname: '60 Days'}, {ptid: '90', ptname: '90 Days'}]
                                    })
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.20,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Shipping/Handling Charge',
                                    id: 'fpot_shippingcharge',
                                    name: 'n[fpot_shippingcharge]',
                                    anchor: '98%',
                                    value: 0,
                                    tabIndex: 918,
                                    listeners: {
                                        change: function () {
                                            var fpot_shippingcharge = Ext.getCmp('fpot_shippingcharge').getValue();
                                            var fpot_hshippingcharge = parseFloat(fpot_shippingcharge) * 100 / parseFloat(Application.RetalineSalesRequest.Cache.total_initialnetamount);
                                            Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = fpot_hshippingcharge;
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'General Discounts',
                                    id: 'fpot_generalDiscount',
                                    name: 'n[fpot_generalDiscount]',
                                    anchor: '98%',
                                    value: 0,
                                    tabIndex: 919,
                                    listeners: {
                                        change: function () {
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'name',
                                    valueField: 'id',
                                    mode: 'local',
                                    id: 'po_gdiscountcalculs',
                                    forceSelection: true,
                                    fieldLabel: 'Amt /Percent',
                                    emptyText: 'Amount / Percentage',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 925,
                                    store: new Ext.data.JsonStore({
                                        fields: ['id', 'name'],
                                        data: [{id: 'Percentage', name: 'Percentage'}]
                                    }), listeners: {
                                        select: function () {
                                            justCaculatehcgd();
                                            Application.RetalineSalesRequest.Cache.isDiscountApplied = 0;
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.10,
                            hideBorders: true,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'button',
                                    text: 'Apply',
                                    tabIndex: 921,
                                    id: 'applyGeneralDiscount',
                                    style: 'margin-top:17px;',
                                    handler: function () {
                                        if ((Ext.getCmp('fpot_generalDiscount').getValue() >= 1) || (Ext.getCmp('fpot_shippingcharge').getValue() > 0)) {
                                            justCaculatehcgd();
                                            Ext.Ajax.request({
                                                url: modURL + '&op=soGenDiscounCalculate',
                                                method: 'POST',
                                                params: {
                                                    bbsr_id: bbsr_id,
                                                    fpot_gdiscpercent: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                    adhoc_paymentTerms: Ext.getCmp('po_payment_terms').getValue(),
                                                    adhoc_paymentValue: Ext.getCmp('po_payment_value').getValue(),
                                                    adhoc_validityType: Ext.getCmp('fpo_validityType').getValue(),
                                                    adhoc_shippingcharge: Ext.getCmp('fpot_shippingcharge').getValue(),
                                                    adhoc_gdiscount: Ext.getCmp('fpot_generalDiscount').getValue(),
                                                    adhoc_gdiscounttype: Ext.getCmp('po_gdiscountcalculs').getValue()

                                                },
                                                success: function (response) {

                                                    var tmp = Ext.decode(response.responseText);
                                                    if (tmp.success == true) {
                                                        Ext.getCmp('salesOrderItemGridView').store.load({
                                                            params: {
                                                                b2bsr_UniqueID: bbsr_id
                                                            },
                                                            callback: function () {
                                                                Ext.getCmp('po_poValue').setValue(tmp.bbsr_srValue);
                                                                Application.RetalineSalesRequest.Cache.fpot_gdiscpercent = 0;
                                                                Application.RetalineSalesRequest.Cache.fpot_hshippingcharge = 0;
                                                                Application.RetalineSalesRequest.Cache.isDiscountApplied = 1;
                                                            }
                                                        });


                                                    } else {
                                                        Ext.MessageBox.alert("Notification", tmp.msg);
                                                    }

                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", 'if you want to apply  General discount / Handling Charge please input an amount above 0.');
                                        }

                                    }
                                }]
                        }
                    ]
                }]
        });
        return panel;
    };
    var editSalesOrderItemGrid = function (bbsr_id) {
        var PurchaseOrderItemStore = function () {
            var itemStore = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listSoItemsStore',
                    method: 'post'
                }),
                fields: ['bbsr_id', 'b2bsr_itemid', 'b2bsr_itemname', 'b2bsr_itemmrp', 'b2bsr_itemqty', 'b2bsr_itemoffrqty', 'b2bsr_totalqty', 'b2bsr_balanceqty', 'b2bsr_itemrate', 'itemDisc', 'itemCount', 'srStatus',
                    'b2bsr_itemaddidisc', 'b2bsr_effectiverate', 'b2bsr_idiscountcalculs', {name: 'b2bsr_amount', type: 'float'}, {name: 'b2bsr_netamount', type: 'float'}, {name: 'b2bsr_initialnetamount', type: 'float'}, 'b2bsr_gendiscount', 'b2bsr_shippingcharge'],
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
            id: 'salesOrderItemGridView',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: viewSalesOrderColModel(),
            tbar: [],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('srStatus') == 1)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
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
    var viewSalesOrderColModel = function () {
        return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
            {
                header: 'Item',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_itemname',
                tooltip: 'Item',
                width: 130

            }, {
                header: 'MRP (Inc. of GST)',
                sortable: true,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'b2bsr_itemmrp',
                align: 'right',
                tooltip: 'MRP (Inc. of GST)',
                width: 60
            }, {
                header: 'Rate',
                sortable: true,
                dataIndex: 'b2bsr_itemrate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Rate',
                align: 'right',
                width: 60
            }, {
                header: 'Qty',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_totalqty',
                tooltip: 'Qty',
                align: 'right',
                width: 50
            }, {
                header: 'Amount',
                sortable: true,
                hideable: false,
                xtype: 'finascopcurrency',
                dataIndex: 'b2bsr_amount',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Amount',
                align: 'right',
                width: 60
            }, {
                header: 'Product Dis.',
                sortable: true,
                hideable: false,
                dataIndex: 'b2bsr_itemoffrqty',
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
                hidden: true,
                dataIndex: 'b2bsr_shippingcharge',
                tooltip: 'Shipping / Handling Charge',
                align: 'right',
                width: 50
            }, {
                header: 'General Dis.',
                sortable: true,
                hidden: true,
                dataIndex: 'b2bsr_gendiscount',
                tooltip: 'General Discount',
                align: 'right',
                width: 50
            },
            {
                header: 'Net Amt',
                sortable: true,
                dataIndex: 'b2bsr_netamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Net Amt',
                width: 60
            }, {
                header: 'Initial Net Amt',
                hidden: true,
                dataIndex: 'b2bsr_initialnetamount',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'Initial Net Amt',
                width: 60
            },
            {
                header: 'ESR',
                sortable: true,
                dataIndex: 'b2bsr_effectiverate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                align: 'right',
                tooltip: 'ESR',
                width: 60
            }, {
                xtype: 'actioncolumn',
                items: [
                    {
                        iconCls: 'edit',
                        tooltip: 'Edit quantity',
                        handler: function (grid, rowIndex, colIndex, itm, evn) {
                            var record = grid.getStore().getAt(rowIndex);
                            editSRQuantity(record.get('bbsr_id'), record.get('b2bsr_itemqty'), record.get('b2bsr_itemoffrqty'), record.get('itemCount'), record.get('b2bsr_totalqty'), record.get('b2bsr_itemid'));
                        }
                    }
                ]
            }
        ]);
    };
    var editSRQuantity = function (bbsr_id, b2bsr_itemqty, b2bsr_itemoffrqty, itemCount, b2bsr_totalqty, b2bsr_itemid) {
        if (Ext.isEmpty(srQuantity_Window)) {
            var srQuantity_Window = new Ext.Window({
                id: 'srQuantity_Window',
                layout: 'fit',
                width: 400,
                title: 'Update Quantity',
                autoHeight: true,
                draggable: false, iconCls: 'my-icon98',
                plain: true,
                constrain: true,
                modal: true,
                resizable: false,
                items: [new Ext.FormPanel({
                        frame: true,
                        monitorValid: true,
                        id: 'upReturnQtyType_form',
                        height: 100,
                        items: [{
                                fieldLabel: 'Change Quantity ',
                                xtype: 'numberfield',
                                id: 'changedQty',
                                width: 100,
                                name: 'changedQty',
                                tabIndex: 93,
                                allowBlank: false,
                                value: b2bsr_itemqty,
                                listeners: {
                                    change: function () {
                                        console.log('bbsr_id', bbsr_id, 'b2bsr_itemqty', b2bsr_itemqty, 'b2bsr_itemoffrqty', b2bsr_itemoffrqty, 'itemCount', itemCount, 'b2bsr_totalqty', b2bsr_totalqty, 'b2bsr_itemid', b2bsr_itemid);
                                        var changedQty = Ext.getCmp('changedQty').getValue();
                                        var newTotalqty = parseInt(changedQty) + parseInt(b2bsr_itemoffrqty);
                                        console.log('changedQty', changedQty);
                                        console.log('newTotalqty', newTotalqty);
                                        if (newTotalqty > itemCount) {
                                            Ext.getCmp('changedQty').reset();
                                        }

                                    }
                                }
                            }]
                    })],
                buttons: [{
                        text: 'Update',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var changedQty = Ext.getCmp('changedQty').getValue();
                            var newTotalqty = parseInt(changedQty) + parseInt(b2bsr_itemoffrqty);
                            if (newTotalqty <= itemCount) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=updateQtyinSr',
                                    method: 'POST',
                                    params: {bbsr_id: bbsr_id, changedQty: changedQty, b2bsr_itemqty: b2bsr_itemqty, b2bsr_itemoffrqty: b2bsr_itemoffrqty, itemCount: itemCount,
                                        bbsr_SRUpdatedOn: Application.RetalineSalesRequest.Cache.bbsr_SRUpdatedOn, b2bsr_itemid: b2bsr_itemid,
                                        b2bsr_totalqty: b2bsr_totalqty},
                                    success: function (res) {
                                        var tmp = Ext.decode(res.responseText);
                                        if (tmp.success === true) {
                                            srQuantity_Window.close();
                                            Ext.getCmp('salesOrderItemGridView').getStore().load({
                                                params: {
                                                    bbsr_id: bbsr_id
                                                }, callback: function () {
                                                    if (Ext.getCmp('fpot_shippingcharge').getValue() > 0 || Ext.getCmp('fpot_generalDiscount').getValue() > 0) {
                                                        Ext.get('applyGeneralDiscount').dom.click();
                                                    } else {
                                                        var itemStore = Ext.getCmp('salesOrderItemGridView').getStore();
                                                        var fpot_netamount = itemStore.sum('b2bsr_netamount');
                                                        fpot_netamount = fpot_netamount + 0;
                                                        console.log('fpot_netamountpovalue2', fpot_netamount);
                                                        Ext.getCmp('po_poValue').setValue(fpot_netamount);
                                                    }
                                                }
                                            });
                                        }

                                    },
                                    failure: function () {
                                        Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                    }
                                });
                            }

                        }
                    }, {
                        text: 'Cancel',
                        id: 'btnCancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            srQuantity_Window.close();
                        }
                    }]
            });

        }
        srQuantity_Window.doLayout();
        srQuantity_Window.show(this);
        srQuantity_Window.center();
    };
    return {
        Cache: {},
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
        },
        viewSRDetails: function (bbsr_id) {
            var vendornewPOForm = viewB2BSalesRequestForm(bbsr_id);
            var poViewWindow = new Ext.Window({
                id: "windowFinascopStocknewPoView",
                title: 'Sales Request',
                //iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1200,
                modal: true,
                layout: 'fit', autoHeight: true,
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
                url: modURL + '&op=getSRDetails',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    bbsr_id: bbsr_id

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);

                    console.log(tmp);

                    Ext.getCmp('vendor_name').setValue(tmp.b2b_Customer_Name);
                    Ext.getCmp('vpo_number').setValue(tmp.bbsr_SRNumber);
                    Ext.getCmp('vpo_date').setValue(tmp.b2bsr_requestDate);
                    Ext.getCmp('poValue').setValue(tmp.bbsr_srValue);
                    Ext.getCmp('po_poValue').setValue(tmp.bbsr_srValue);
                    Ext.getCmp('po_payment_terms').setValue(tmp.bbsr_paymentTerms);
                    Ext.getCmp('payment_value').setValue(tmp.bbsr_paymentValue);
                    Ext.getCmp('povalidityType').setValue(tmp.bbsr_validityType);
                    Ext.getCmp('pogeneralDiscount').setValue(tmp.bbsr_gdiscpercent);
                    Ext.getCmp('bbsr_shippingcharge').setValue(tmp.bbsr_shippingcharge);
                    Ext.getCmp('salesRequestItemGridView').getStore().load({
                        params: {
                            bbsr_id: bbsr_id
                        }
                    });
                }
            });
            poViewWindow.doLayout();
            poViewWindow.show();
            poViewWindow.center();
        },
        convertToSalesOrder: function (bbsr_id) {


            var srToSoForm = viewB2BSalesOrderForm(bbsr_id);
            var poViewWindow = new Ext.Window({
                id: "windowFinascopStocknewPoView",
                title: 'Sales Request',
                //iconCls: 'vender-items',
                shadow: false,
                height: 520,
                width: 1200,
                modal: true,
                layout: 'fit', autoHeight: true,
                resizable: false,
                closable: false,
                items: [srToSoForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 519,
                        handler: function () {
                            Ext.getCmp('windowFinascopStocknewPoView').close();
                        }
                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Convert',
                        tabIndex: 519,
                        handler: function () {
                            var store = Ext.getCmp('salesOrderItemGridView').getStore();
                            var tot, total = 0;
                            if (store.data.length > 0) {
                                store.each(function (record) {
                                    var srStatus = record.get('srStatus');
                                    total += parseInt(srStatus);
                                });
                            }
                            console.log('total', total);
                            if ((total == 0) && !Ext.isEmpty(Ext.getCmp('po_payment_terms').getValue()) && (Ext.getCmp('fpo_validityType').getValue() > 0)) {
                                var srpaymentTerms = Ext.getCmp('po_payment_terms').getValue();
                                var srpaymentValue = Ext.getCmp('po_payment_value').getValue();
                                var srvalidityType = Ext.getCmp('fpo_validityType').getValue();
                                var soValue = Ext.getCmp('po_poValue').getValue();
                                Ext.Ajax.request({
                                    url: modURL + '&op=convertToB2BSalesOrder',
                                    method: 'POST',
                                    params: {bbsr_id: bbsr_id, soValue: soValue, srpaymentTerms: srpaymentTerms, srpaymentValue: srpaymentValue, srvalidityType: srvalidityType},
                                    success: function (response) {

                                        var res = Ext.decode(response.responseText);
                                        if (res.success === true) {
                                            Application.example.msg('Success', res.msg);
                                            poViewWindow.close();
                                            recs_per_page = updateRecsPerPage(Ext.getCmp('B2BSalesRequestGrid'));
                                            Ext.getCmp('B2BSalesRequestGrid').store.reload({
                                                params: {
                                                    start: 0,
                                                    limit: recs_per_page
                                                }
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", res.msg);
                                            // Ext.getCmp('b2bSalesRequestWinID').close();
                                        }
                                    },
                                    failure: function () {
                                        Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert('Notification', 'Validity fields are missing or Stock is not available for some items..Please check');
                            }

                        }
                    }
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=getSRDetails',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    bbsr_id: bbsr_id

                },
                method: 'POST',
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);

                    console.log(tmp);

                    Ext.getCmp('vendor_name').setValue(tmp.b2b_Customer_Name);
                    Ext.getCmp('vpo_number').setValue(tmp.bbsr_SRNumber);
                    Ext.getCmp('vpo_date').setValue(tmp.b2bsr_requestDate);
                    Ext.getCmp('poValue').setValue(tmp.bbsr_srValue);
                    Ext.getCmp('po_poValue').setValue(tmp.bbsr_srValue);
                    Ext.getCmp('po_payment_terms').setValue(tmp.bbsr_paymentTerms);
                    // Ext.getCmp('payment_value').setValue(tmp.bbsr_paymentValue);
                    var validityType = tmp.bbsr_validityType.split(" ");
                    Ext.getCmp('fpo_validityType').setValue(validityType[0]);
                    Ext.getCmp('fpot_generalDiscount').setValue(tmp.bbsr_gdiscpercent);
                    if (tmp.bbsr_gdiscpercent > 0) {
                        Ext.getCmp('po_gdiscountcalculs').setValue('Percentage');
                    }

                    Ext.getCmp('fpot_shippingcharge').setValue(tmp.bbsr_shippingcharge);
                    Application.RetalineSalesRequest.Cache.bbsr_SRUpdatedOn = tmp.bbsr_SRUpdatedOn;
                    Ext.getCmp('salesOrderItemGridView').getStore().load({
                        params: {
                            bbsr_id: bbsr_id
                        }
                    });
                }
            });
            poViewWindow.doLayout();
            poViewWindow.show();
            poViewWindow.center();

        }, initadhocSalesRequest: function () {
            var _panelId = 'sales_request_panel_adhoc';
            var _adSalesRequestPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_adSalesRequestPanel)) {
                _adSalesRequestPanel = adSalesRequestPanel(_panelId);
                Application.UI.addTab(_adSalesRequestPanel);
                _adSalesRequestPanel.doLayout();
            } else {
                Application.UI.addTab(_adSalesRequestPanel);
                _adSalesRequestPanel.doLayout();
            }
        }, deleteFeedback: function () {
            var t = new Date();
            var adhoc_uniqueid = arguments[0];
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=deleteAdhocDetails',
                method: 'POST',
                params: {adhoc_uniqueid: adhoc_uniqueid},
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        recs_per_page = updateRecsPerPage(Ext.getCmp('gridpaneSalesRequestadhoc'));
                        Ext.getCmp('gridpaneSalesRequestadhoc').store.reload({
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
        },
    };
}();