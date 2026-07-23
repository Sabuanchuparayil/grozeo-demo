/* global Application, Ext */

Application.RetalineSalesInvoice = function () {

var smsLastParameters;
    var RECS_PER_PAGE = 23;
    var modURL = '?module=retaline_sales_invoice';
    var winsize = Ext.getBody().getViewSize();


//Common Functions BEGIN
    {
        function updatePagination(cmp) {
            RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
        }

        var _barcodeDetailsStore = function (bbsd_id) {
            var _Store = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=listBarCodesOfItem',
                    method: 'post'
                }),
                fields: ['slNo', 'stiid_barcode'],
                totalProperty: 'totalCount',
                root: 'data',
                remoteSort: true,
                autoLoad: false,
                listeners: {
                    beforeload: function (store, e) {
                        this.baseParams.bbsd_id = bbsd_id;
                    }
                }
            });
            return _Store;
        };
        var _barcodeDetailsGrid = function (bbsd_id) {

            var _InvoicegridPanel = new Ext.grid.GridPanel({
                frame: true,
                border: false,
                loadMask: true,
                store: _barcodeDetailsStore(bbsd_id),
                iconCls: 'barcode',
                width: 325,
                height: 500,
                bodyStyle: {"background-color": "white"},
                id: 'barcodeDetailsGridB2BSO',
                columns: [
                    {
                        header: 'Sl No.',
                        dataIndex: 'slNo',
                        sortable: true,
                        tooltip: 'Barcode',
                        hideable: false,
                        width: 50
                    },
                    {
                        header: 'Barcode',
                        dataIndex: 'stiid_barcode',
                        sortable: true,
                        tooltip: 'Barcode',
                        hideable: false,
                        width: 300
                    }
                ],
                viewConfig: {
                    forceFit: true,
                },
                bbar: new Ext.PagingToolbar({
                    pageSize: RECS_PER_PAGE,
                    store: _barcodeDetailsStore(bbsd_id),
                    displayInfo: true,
                    displayMsg: 'Displaying records {0} - {1} of {2}',
                    emptyMsg: "No pages to display"
                }),
                listeners: {
                    afterrender: function () {
                        Ext.getCmp('barcodeDetailsGridB2BSO').getStore().load({
                            params: {
                                bbsd_id: bbsd_id
                            }
                        });
                    }, rowdblclick: function (grid, rowIndex, e) {
                        var rec = grid.getStore().getAt(rowIndex);
                        Application.RetalineCurrentStock.displayBarcodeDetails(rec.get('stiid_barcode'));
                    },
                    viewready: updatePagination
                }
            });
            return _InvoicegridPanel;
        };

        var showBarcodeNumbers = function (bbso_id, bbsd_id, b2bso_itemname) {
            var b2bShowBarcodeDetailsWin = new Ext.Window({
                id: "b2bBarcodeDetailsWinID",
                title: 'BarCode details of Item: ' + b2bso_itemname,
                shadow: false,
                width: 340,
                autoHeight: true,
                modal: true,
                layout: 'fit',
                resizable: true,
                closable: true,
                items: [
                    {
                        xtype: 'panel',
                        anchor: '98$',
                        border: false,
                        frame: false,
                        autoHeight: true,
                        layout: 'form',
                        height: 50,
                        labelAlign: 'top',
                        items:
                                [
                                    _barcodeDetailsGrid(bbsd_id)
                                ]
                    }
                ],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        tabIndex: 523,
                        handler: function () {
                            Ext.getCmp('b2bBarcodeDetailsWinID').close();
                        }
                    }]
            });

            b2bShowBarcodeDetailsWin.doLayout();
            b2bShowBarcodeDetailsWin.show();
            b2bShowBarcodeDetailsWin.center();
        };

        var updateHandlingChrgInSO = function (bbso_id, bbso_HandlingCharges) {

            var b2bHandlingChargesWin = new Ext.Window({
                id: "handlingChrgInSOWinID",
                title: 'Handling Charges Updator',
                shadow: false,
                width: 200,
                modal: true,
                height: 50,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [
                    {
                        xtype: 'panel',
                        anchor: '98$',
                        border: false,
                        frame: false,
                        layout: 'form',
                        height: 50,
                        labelAlign: 'top',
                        items: [{
                                xtype: 'numberfield',
                                fieldLabel: 'Handling Charges',
                                id: 'hcw_bbso_HandlingCharges',
                                name: 'hcw_bbso_HandlingCharges',
                                allowBlank: true,
                                selectOnFocus: true,
                                allowNegative: false,
                                anchor: '99%',
                                value: bbso_HandlingCharges,
                                format: FINASCOP_CURRENCY_FORMAT,
                                style: 'text-align:right',
                                allowDecimal: false
                            }]
                    }
                ],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 523,
                        handler: function () {
                            Ext.getCmp('handlingChrgInSOWinID').close();
                        }
                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Update',
                        id: 'b2hacSubmitBtn',
                        tabIndex: 522,
                        handler: function () {
                            Ext.Ajax.request({
                                url: modURL + '&op=updateHandlingCharges',
                                params: {
                                    bbso_id: bbso_id,
                                    bbso_HandlingCharges: Ext.getCmp('hcw_bbso_HandlingCharges').getValue()
                                },
                                method: 'POST',
                                success: function (res, opt) {
                                    var tmp = Ext.decode(res.responseText);
                                    if (tmp.success === true) {
                                        Application.example.msg('Success', "Handling Charges Updated.");
                                        Ext.isEmpty(Ext.getCmp('B2BSalesInvoceGrid')) ? {} : Ext.getCmp('B2BSalesInvoceGrid').getStore().load();
                                        b2bHandlingChargesWin.close();
                                    } else {
                                        Ext.MessageBox.alert("Notification", res.responseText);
                                    }
                                },
                                failure: function (res, opt) {
                                    Ext.MessageBox.alert('Notification', res.responseText,
                                            function (btn) {
                                                b2bHandlingChargesWin.close();
                                            });
                                }
                            });
                        }
                    }]
            });

            b2bHandlingChargesWin.doLayout();
            b2bHandlingChargesWin.show();
            b2bHandlingChargesWin.center();
        };


        var b2bSalesInvoceColModel = function () {
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
                }, {
                    header: 'Product Discount',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'b2bso_itemoffrqty',
                    tooltip: 'Product Discount',
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
                    items: [
                        {
                            tooltip: 'View Barcode Details',
                            getClass: function (v, meta, rec) {
                                var bbso_id = rec.get('bbso_id');
                                if (bbso_id > 0) {
                                    return 'barcode';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                showBarcodeNumbers(record.get('bbso_id'), record.get('bbsd_id'), record.get('b2bso_itemname'));
                            }
                        },
                    ]
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
                        url: modURL + '&op=getB2BSODetails',
                        method: 'post'
                    }),
                    fields: ['bbso_id', 'bbsd_id', 'b2bso_itemid', 'b2bso_itemname', 'b2bso_itemmrp', 'b2bso_itemqty', 'b2bso_itemrate', 'b2bso_gst', 'b2bso_itemPkg', 'b2bso_itemoffrqty',
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
                cm: b2bSalesInvoceColModel(),
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


    }
//Common Functions END

//B2B B2B Sales Invoice  BEGIN
    {

        var InvoiceDetailsView = function (url) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var apikey = _SESSION.apikey;
            var src = '';
            var contactsPanel = new Ext.Panel({
                id: 'panelInvoiceDetailsView',
                width: winsize.width * 0.4,
                html: '<iframe id="downloadInvoiceIframe" name="downloadInvoiceIframe" style="overflow:auto;height:100%;width: 100%" frameborder="1"  src="' + src + '"; ></iframe>',
            });

            return contactsPanel;
        };


        var showInvoice = function (InvoieNo, url) {
            var b2bInvoiceWin = new Ext.Window({
                id: "b2bInvoiceWinID",
                title: 'Invoice : ' + InvoieNo,
                shadow: false,
                width: 800,
                height: 600,
                modal: true,
                layout: 'fit',
                resizable: true,
                closable: true,
                items: [
                    InvoiceDetailsView(url)
                ],
                buttons: [
                    {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Close',
                        tabIndex: 523,
                        handler: function () {
                            b2bInvoiceWin.close();
                        }
                    }, {
                        text: 'Print',
                        iconCls: 'printer_add',
                        handler: function () {
                            downloadInvoiceIframe.focus();
                            downloadInvoiceIframe.print();
                        }
                    }]
            });

            b2bInvoiceWin.doLayout();
            b2bInvoiceWin.show();
            b2bInvoiceWin.center();
            Ext.get('downloadInvoiceIframe').dom.src = url;
        };
        var saveB2BSalesInvoice = function () {
            Ext.Ajax.request({
                url: modURL + '&op=saveB2BSalesInvoice',
                method: 'POST',
                params: {
                    bbso_id: Application.RetalineSales.Cache.bbso_id
                },
                success: function (response) {
                    var res = Ext.decode(response.responseText);
                    if (res.success === true) {
                        Application.example.msg('Success', res.msg);
                        Ext.isEmpty(Ext.getCmp('B2BSalesInvoceGrid')) ? {} : Ext.getCmp('B2BSalesInvoceGrid').getStore().load();
                        Ext.isEmpty(Ext.getCmp('B2BSalesOrderGrid')) ? {} : Ext.getCmp('B2BSalesOrderGrid').getStore().load();
                        Ext.getCmp('b2bSalesInvoiceWinID').close();
                        Application.RetalineSales.Cache.b2bdc_UniqueID = '';

                    } else {
                        Ext.MessageBox.alert("Notification", response.responseText);
                    }
                },
                failure: function (response) {
                    Ext.MessageBox.alert('Error', response.responseText);
                }
            });
        };

        var createB2BSalesInvoiceForm = function (bbso_id, status_id, fromView) {
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
                id: 'b2bSalesInvoicePanelID',
                layout: 'column',
                listeners: {
                    afterrender: function (thisPanel) {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        if (bbso_id > 0) {
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
                                    Application.RetalineSales.Cache.b2bdc_UniqueID = uid;
                                    Application.RetalineSales.Cache.bbso_id = bbso_id;
                                    if (bbso_id > 0) {
                                        Ext.Ajax.request({
                                            url: modURL + '&op=getB2BSODetails',
                                            method: 'POST',
                                            params: {
                                                bbso_id: bbso_id
                                            },
                                            success: function (response) {
                                                var res = Ext.decode(response.responseText);
                                                Ext.getCmp('b2bInvCustomer').setValue(res.SOdata.b2b_Customer_ID);
                                                Ext.getCmp('b2bInvCustomer').fireEvent('select', Ext.getCmp('b2bInvCustomer'));
                                                Ext.getCmp('b2bInvCustomer').setReadOnly(true);
                                                Ext.getCmp('b2binv_HandlingCharges').setValue(res.SOdata.bbso_HandlingCharges);
                                                Ext.getCmp('b2binv_HandlingCharges').setReadOnly(true);
                                                Ext.getCmp('b2binv_NetTotal').setValue(res.SOdata.bbso_SOValue);
                                                Ext.getCmp('b2binv_NetTotal').setReadOnly(true);
                                                Ext.getCmp('b2bso_ItemGrid').getStore().loadData(res);
                                            }
                                        });
                                    }
                                },
                                failure: function (response) {
                                    Ext.MessageBox.alert('Error', response.responseText);
                                }
                            });
                        }
                        if (fromView == 1) {
                            Ext.getCmp('b2bInvSubmitBtn').setVisible(false);
                        } else
                        {
                            Ext.getCmp('b2bInvSubmitBtn').setVisible(true);
                        }
                        ;
                    }
                },
                items: [
                    {
                        layout: 'column',
                        style: 'margin-bottom:3px;',
                        columnWidth: 1,
                        items: [
                            {
                                layout: 'form',
                                columnWidth: 0.19,
                                labelAlign: 'top',
                                items: [{
                                        xtype: 'combo',
                                        fieldLabel: 'B2B Client Name',
                                        emptyText: 'Choose B2B Customer',
                                        id: 'b2bInvCustomer',
                                        name: 'b2binv[b2bInvCustomer]',
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
                                        hiddenName: 'b2bInvCustomer',
                                        tabIndex: 501,
                                        listeners: {
                                            select: function (combo, record, index) {


                                                Ext.Ajax.request({
                                                    url: modURL + '&op=b2bSOCustomerDetails',
                                                    method: 'POST',
                                                    params: {
                                                        'b2bSOCustomer': combo.getValue()
                                                    },
                                                    success: function (res) {
                                                        var tmp = Ext.decode(res.responseText);

                                                        Ext.getCmp('b2binvContactPerson').setValue(tmp.b2b_Customer_Incharge);
                                                        Ext.getCmp('b2bInvCustomerPincode').setValue(tmp.b2b_Customer_pincode);
                                                        Ext.getCmp('b2bInvCustomerMobile').setValue(tmp.b2b_Customer_Mobile);
                                                        Ext.getCmp('b2bInvCustomerGST').setValue(tmp.b2b_Customer_gst);
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
                                id: 'b2bInvCustomerDetails',
                                items: [{
                                        layout: 'form',
                                        style: 'margin-bottom:3px;',
                                        columnWidth: .30,
                                        labelAlign: 'left',
                                        labelWidth: 100,
                                        items: [{
                                                xtype: 'displayfield',
                                                fieldLabel: 'Conntact Person',
                                                id: 'b2binvContactPerson',
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
                                                id: 'b2bInvCustomerPincode',
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
                                                id: 'b2bInvCustomerMobile',
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
                                                id: 'b2bInvCustomerGST',
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
                        xtype: 'fieldset',
                        title: 'b2b Sales Invoce Items',
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
                                        id: 'b2binv_HandlingCharges',
                                        name: 'b2binv_HandlingCharges',
                                        allowNegative: false,
                                        allowDecimals: false,
                                        tabIndex: 515,
                                        anchor: '99%',
                                        listeners: {
                                            focus: function () {
                                                var store = Ext.getCmp('b2bdc_ItemGrid').getStore();
                                                var handlingCharges = 0 + Ext.getCmp('b2binv_HandlingCharges').getValue();
                                                var b2bdc_totalnetamount = store.sum('b2bdc_netamount') + parseFloat(handlingCharges);
                                                Ext.getCmp('b2binv_NetTotal').setValue(b2bdc_totalnetamount);
                                            },
                                            blur: function () {
                                                var store = Ext.getCmp('b2bdc_ItemGrid').getStore();
                                                var handlingCharges = 0 + Ext.getCmp('b2binv_HandlingCharges').getValue();
                                                var b2bdc_totalnetamount = store.sum('b2bdc_netamount') + parseFloat(handlingCharges);
                                                Ext.getCmp('b2binv_NetTotal').setValue(b2bdc_totalnetamount);
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
                                        id: 'b2binv_NetTotal',
                                        name: 'b2binv_NetTotal',
                                        allowNegative: true,
                                        allowDecimals: true,
                                        tabIndex: 520,
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

        var createB2BSalesInvoice = function (bbso_id, status_id, fromView) {
            var B2BSalesInvoiceForm = createB2BSalesInvoiceForm(bbso_id, status_id, fromView);
            var b2bSalesInvoiceWin = new Ext.Window({
                id: "b2bSalesInvoiceWinID",
                title: 'B2B Sales Invoice',
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

                    }
                },
                items: [B2BSalesInvoiceForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 523,
                        handler: function () {
                            Ext.getCmp('b2bSalesInvoiceWinID').close();
                            Application.RetalineSales.Cache.b2bdc_UniqueID = '';
                        }
                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Submit',
                        id: 'b2bInvSubmitBtn',
                        tabIndex: 522,
                        handler: function () {
                            if (parseFloat(Ext.getCmp('b2binv_NetTotal').getValue()) > 0.00) {
                                saveB2BSalesInvoice();

                            } else {
                                Ext.MessageBox.alert("Notification", 'Please fill B2B Sales Invoice details.');
                            }

                        }
                    }]
            });


            b2bSalesInvoiceWin.doLayout();
            b2bSalesInvoiceWin.show();
            b2bSalesInvoiceWin.center();
        };


    }

    var B2BSalesInvoceGridStore = function () {
        var _catStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listB2BSalesInvoces',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'bbso_id',
                root: 'data'
            }, ['bbso_id', 'bbso_InvNumber', 'bbso_InvDate', 'b2b_Customer_ID', 'b2b_Customer_Name', 'bbso_InvoiceStatus', 'bbso_InvoiceStatusName',
                'bbso_SOValue', 'bbso_HandlingCharges', 'bbso_Active', 'status_id']),
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

    var B2B_SalesInvoceEntryGrid = function () {
        var _b2bSO_GridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'bbso_SONumber'
                }, {
                    type: 'string',
                    dataIndex: 'b2b_Customer_Name'
                }, {
                    type: 'list',
                    dataIndex: 'bbso_InvoiceStatusName',
                    options: ['Invoiced', 'Ready for Invoice'],
                    phpMode: true
                }]
        });
        _b2bSO_GridFilter.remote = true;
        _b2bSO_GridFilter.autoReload = true;

        var _gridStore = B2BSalesInvoceGridStore();

        var _gridPanel = new Ext.grid.GridPanel({
            id: 'B2BSalesInvoceGrid',
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
            plugins: [new Ext.ux.grid.GroupSummary(), _b2bSO_GridFilter],
            columns: [
                {
                    header: 'B2B Invoice Number',
                    sortable: true,
                    dataIndex: 'bbso_InvNumber',
                    tooltip: 'B2B Sales Invoce Number',
                    hideable: false
                },
                {
                    header: 'B2B Invoice Date',
                    sortable: true,
                    dataIndex: 'bbso_InvDate',
                    tooltip: 'B2B Sales Invoce Date'
                },
                {
                    header: 'Customer',
                    sortable: true,
                    dataIndex: 'b2b_Customer_Name',
                    tooltip: 'Vendor',
                    hideable: false
                },
                {
                    header: 'B2B Invoice Total Value',
                    sortable: true,
                    dataIndex: 'bbso_SOValue',
                    tooltip: 'B2B Sales Invoce Status',
                    hideable: false
                },
                {
                    header: 'B2B SO Handling Charges',
                    sortable: true,
                    dataIndex: 'bbso_HandlingCharges',
                    tooltip: 'B2B Sales Invoce Status',
                    hideable: false
                },
                {
                    header: 'B2B Invoice Status',
                    sortable: true,
                    dataIndex: 'bbso_InvoiceStatusName',
                    tooltip: 'B2B Sales Invoce Status',
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
                            var status_id = Ext.getCmp('B2BSalesInvoceGrid').getSelectionModel().getSelections()[0].data.status_id;
                            salesinvoicesActionMenu(e, status_id);
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
                 tooltip: 'View Sales Order Details',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var fromView = 1;
                 createB2BSalesInvoice(record.get('bbso_id'), record.get('status_id'), fromView);
                 }
                 },
                 {
                 tooltip: 'Update Handling Charges',
                 hidden: true,
                 getClass: function (v, meta, rec) {
                 return 'finascop_hideicon';
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 updateHandlingChrgInSO(record.get('bbso_id'), record.get('bbso_HandlingCharges'));
                 }
                 },
                 {
                 tooltip: 'Print Invoice',
                 getClass: function (v, meta, rec) {
                 var status_id = rec.get('status_id');
                 if (status_id == 9 || status_id == 29) {
                 return 'print_invoice';
                 } else {
                 return 'finascop_hideicon';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 
                 var t = new Date();
                 var t_stamp = t.format("YmdHis");
                 var bbso_id = record.get('bbso_id');
                 var bbso_InvNumber = record.get('bbso_InvNumber');
                 var type = record.get('type_name');
                 var url =
                 modURL + '&op=printInvoice&bbso_id=' +
                 bbso_id + '&bbso_InvNumber=' +
                 bbso_InvNumber + '&type=' +
                 type + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;
                 //showInvoice(bbso_InvNumber, url);
                 
                 Ext.get('downloadIframe').dom.src = url;
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
                pageSize: RECS_PER_PAGE,
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
    var salesinvoicesActionMenu = function (e, status_id) {
        var salesinvoicesActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "View",
                    handler: function () {
                        var bbso_id = Ext.getCmp('B2BSalesInvoceGrid').getSelectionModel().getSelections()[0].data.bbso_id;
                        var status_id = Ext.getCmp('B2BSalesInvoceGrid').getSelectionModel().getSelections()[0].data.status_id;
                        var fromView = 1;
                        createB2BSalesInvoice(bbso_id, status_id, fromView);
                    }
                },
                {
                    text: "Print Invoice",
                    hidden: !(status_id == 9 || status_id == 29),
                    handler: function () {
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        var bbso_id = Ext.getCmp('B2BSalesInvoceGrid').getSelectionModel().getSelections()[0].data.bbso_id;
                        var bbso_InvNumber = Ext.getCmp('B2BSalesInvoceGrid').getSelectionModel().getSelections()[0].data.bbso_InvNumber;
                        var type_name = Ext.getCmp('B2BSalesInvoceGrid').getSelectionModel().getSelections()[0].data.type_name;
                        var url =
                                modURL + '&op=printInvoice&bbso_id=' +
                                bbso_id + '&bbso_InvNumber=' +
                                bbso_InvNumber + '&type_name=' +
                                type_name + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;
                        //showInvoice(bbso_InvNumber, url);

                        Ext.get('downloadIframe').dom.src = url;
                    }
                }]
        });
        salesinvoicesActionMenu.showAt(e.getXY());
    };

    var createB2BSalesInvoceGrid = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id,
            title: 'Sales Invoices',
            //iconCls: 'b2bicon24x24',
            items: [B2B_SalesInvoceEntryGrid()]
        });
        return panel;
    };
    var getSMSReportPanel = function (panelId) {
        var smsReportGrid = getSMSReportGrid();
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'fit',
            border: false,
            id: panelId,
            title: 'SMS Report',
            items: [smsReportGrid]
        });
        return panel;
    };
    var getSMSReportGrid = function () {

        var getSMSReportStore = function () {
            var B2CSalesReportStore = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=getSMSReportGridData',
                fields: ['smsemaillog_id', 'smsemail_id', 'smsemail_datetime', 'smsemail_text', 'issms', 'sms_responseid','typeof'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    beforeload: function (st, opt) {
                        var smssearch_stock_from_date = Ext.getCmp('smssearch_stock_from_date').getValue();
                        st.baseParams.smssearch_stock_from_date = smssearch_stock_from_date
                    },
                    load: function (a, b, options) {
                        smsLastParameters = options.params;
                    }
                }

            });
            return B2CSalesReportStore;
        };
        var gridFilters = new Ext.ux.grid.GridFilters(
                {
                    filters: [{
                            type: 'string',
                            dataIndex: 'smsemail_id'
                        }, {
                            type: 'date',
                            dataIndex: 'smsemail_datetime'
                        }]
                });
        gridFilters.remote = true;
        gridFilters.autoReload = true;
        var smsReport_store = getSMSReportStore();
        var grid = new Ext.grid.GridPanel({
            store: smsReport_store,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            plugins: [gridFilters],
            id: 'sms_report_grid',
            columns: [new Ext.grid.RowNumberer(), {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'smsemail_id',
                    tooltip: 'Mobile',
                }, {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'smsemail_datetime',
                    tooltip: 'Date',
                    hideable: false,
                }, {
                    header: 'SMS',
                    sortable: true,
                    dataIndex: 'smsemail_text',
                    tooltip: 'SMS',
                    width: 250
                },{
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'typeof',
                    tooltip: 'Type',
                    width: 250
                }],
            viewConfig: {
                forceFit: true
            }, sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                }
            }),
            tbar: [
                {html: '&nbsp; Date : &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    id: 'smssearch_stock_from_date',
                    name: 'smssearch_stock_from_date',
                    format: 'd/m/Y',
                    value: new Date()
                }, '-',
                {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        var smssearch_stock_from_date = Ext.getCmp('smssearch_stock_from_date').getValue();
                        Ext.getCmp('sms_report_grid').filters.clearFilters();
                        Ext.getCmp('sms_report_grid').getStore().load({
                            params: {
                                smssearch_stock_from_date: smssearch_stock_from_date,
                            }
                        });


                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Export to Excel',
                    iconCls: 'icon_excel',
                    handler: function () {
                        if (smsReport_store.getTotalCount() <= 0) {
                            Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                                if (btn == 'no') {
                                    return;
                                }
                            });
                        }

                        var indexes = [];
                        var heads = [];
                        /**
                         * @modified by Aparna Ravvendran <aparna@saturn.in>
                         * @modified On 27-Mar-2013
                         *
                         * pass total no of headers of selected grid for export excel
                         */
                        var filterData = Ext.encode(smsLastParameters);
                        for (var i = 0; i < Ext.getCmp('sms_report_grid').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('sms_report_grid').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('sms_report_grid').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('sms_report_grid').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }

                        postToUrl(modURL + '&op=fmSalesReportsexportexcel', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: smsReport_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            //autoExpandColumn: 'cust_name_auto_exp'

        });
        return grid;
    };
    //B2B Sales Invoice END
    return {
        Cache: {},
        initListB2BSalesInvoces: function () {
            var sales_invoice_listPanelID = 'rln_sales_invoice_listPanelID';
            var sales_invoice_listPanel = Ext.getCmp('rln_sales_invoice_listPanelID');
            if (Ext.isEmpty(sales_invoice_listPanel)) {
                sales_invoice_listPanel = createB2BSalesInvoceGrid(sales_invoice_listPanelID);
                Application.UI.addTab(sales_invoice_listPanel);
                sales_invoice_listPanel.doLayout();
            } else {
                Application.UI.addTab(sales_invoice_listPanel);
            }
        },
        createB2BSalesInvoice: function (bbso_id) {
            Application.RetalineSales.Cache.bbso_id = bbso_id;
            createB2BSalesInvoice(bbso_id);
        }, smsReport: function ()
        {
            var panelId = 'smsReportPanelID';
            var smsReportPanel = Ext.getCmp(panelId);
            if (Ext.isEmpty(smsReportPanel)) {
                smsReportPanel = getSMSReportPanel(panelId);
                Application.UI.addTab(smsReportPanel);
                smsReportPanel.doLayout();
            } else {
                Application.UI.addTab(smsReportPanel);
            }
        }
    };
}();
