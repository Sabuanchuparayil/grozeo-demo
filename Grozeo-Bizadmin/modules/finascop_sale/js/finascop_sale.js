Application.Finascop_Sale = function () {
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 12;
    var chkd;
    var party_id;
    var party_state_id;
    var branch_state_id;
    var br_id;
    var modURL = '?module=finascop_sale';
    var excludeItemIds = [];

    /*************MASTER DATA****************/
    /****  Terms *****/

    var termsStore = function (invoiceRecord) {

        if (invoiceRecord == null) {
            var term_store = new Ext.data.JsonStore({
                url: modURL + '&op=listTermsdata',
                fields: ['id', 'terms', 'details'],
                totalProperty: 'totalCount',
                root: 'data',
                id: 'sl_tm_ID',
                remoteSort: true
            });

        } else {
            var term_store = new Ext.data.Store({
                reader: new Ext.data.JsonReader({
                    fields: ['id', 'details']
                }),
                data: []
            });
        }

        return term_store;
    }

    var partyStore = function () {


        var partyStore = new Ext.data.JsonStore({
            url: modURL + '&op=listParty',
            fields: ['id', 'party', 'party_state_id', 'br_id'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'sl_party_store',
            autoload: true,
            remoteFilter: true

        });
        return partyStore;
    }

    var loadTermsData = function () {
        Ext.getCmp('sl_term_grid').getStore().setDefaultSort('sl_terms', 'asc');
        Ext.getCmp('sl_term_grid').getStore().removeAll();
        Ext.getCmp('sl_term_grid').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    }

    var termGridAction = function () {
        var action = new Ext.ux.grid.RowActions({
            hideMode: 'display',
            autoWidth: false,
            width: 30,
            actions: [{
                    sortable: false,
                    tooltip: 'Edit terms  ',
                    iconCls: 'finascop_edit',
                    callback: function (grid, rec, row, col) {
                        addTermDetails(rec.get('id'));
                    }
                }]
        });
        return action;
    }

    var createTermGrid = function () {
        var term_store = termsStore();
        var action = termGridAction();
        var _smsFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'terms'
                },
                {
                    type: 'string',
                    dataIndex: 'details'
                }
            ]
        });
        _smsFilter.remote = true;
        _smsFilter.autoReload = true;
        var term_grid = new Ext.grid.GridPanel({
            store: term_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true
            },
            plugins: [action ,_smsFilter],
            title: 'Terms',
            //iconCls: 'finascop_terms',
            id: 'sl_term_grid',
            columns: [{
                    header: '',
                    sortable: false,
                    dataIndex: 'rownum',
                    align: 'right',
                    width: 3
                }, {
                    header: 'Terms',
                    sortable: true,
                    dataIndex: 'terms'


                }, {
                    header: 'Details ',
                    sortable: true,
                    dataIndex: 'details'


                }, action],
            listeners: {
                viewready: updatePagination
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create New ',
                    iconCls: 'finascop_add',
                    handler: function () {
                        addTermDetails(0);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: term_store,
                displayInfo: true,
                plugins: [],
                displayMsg: 'Displaying pages {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'terms'

        });
        return term_grid;
    }

    var addTermsForm = function (readOnly) {
        var atfrm = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'sl_terms_form',
            autoHeight: true,
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Terms',
                    id: 'sl_terms',
                    name: 'terms',
                    anchor: '97%',
                    readOnly: readOnly,
                    allowBlank: false,
                    tabIndex: 1,
                    maxLength: 10,
                    listeners:{
                                       afterrender:function(field){
                                       Ext.defer(function(){
                                       field.focus(true,100);
                                       },1);
                                     }
                                   }

                }, {
                    xtype: "textarea",
                    height: 50,
                    fieldLabel: 'Details',
                    id: 'sl_term_details',
                    name: 'term_details',
                    anchor: '97%',
                    allowBlank: false,
                    tabIndex: 2

                }
            ]

        });
        return atfrm;
    }

    var addTermDetails = function (id) {
        var term_winTitle = (id == 0) ? "Add Terms Details" : "Edit Terms Details";

        var term_window = Ext.getCmp('sl_term_window');
        if (Ext.isEmpty(term_window)) {
            var termform = addTermsForm(id > 0);
            term_window = new Ext.Window({
                id: 'sl_term_window',
                layout: 'fit',
                width: 400,
                iconCls: 'finascop_terms',
                title: term_winTitle,
                autoHeight: true,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                resizable: false,
                items: [termform],
                buttons: [{
                        text: 'Save',
                        id: 'save_btn',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        iconCls: 'finascop_my-icon1',
                        handler: function () {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");
                            termform.getForm().submit({
                                waitMsg: 'Saving...',
                                url: modURL,
                                params: {
                                    op: 'saveTerms',
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp,
                                    'id': id
                                },
                                //for submit success
                                success: function (frm, action) {

                                    if (action && action.response.responseText) {
                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success == true) {
                                            Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {


                                                term_window.close();
                                                loadTermsData();
                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }

                                    }


                                },
                                //for submit failure
                                failure: function (form, action) {
                                    if (action.failureType == 'server') {
                                        obj = Ext.util.JSON.decode(action.response.responseText);
                                        Ext.MessageBox.show({
                                            title: 'Error!',
                                            msg: (obj.msg) ? obj.msg : obj.error,
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.ERROR,
                                            width: 325
                                        });
                                    }
                                }
                            });
                        }
                    }, {
                        text: 'Cancel',
                        id: 'Cancel_btns',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        iconCls: 'finascop_my-icon1',
                        handler: function () {
                            term_window.close();
                        }
                    }]
            });
        }
        if (!Ext.isEmpty(id)) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            termform.load({
                url: modURL + '&op=getTermsData',
                params: {
                    'id': id,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (frm, action) {
                    var obj = Ext.decode(action.response.responseText);
                }
            });
        }

        term_window.doLayout();
        term_window.show(this);
        term_window.center();
    }

    /*/////////////////////////////////////////////////////////////////////////////*/

    /* Acions in Item Grid(Action = Remove)*/

    var item_grid_Action = function () {

        var item_grid_action = new Ext.ux.grid.RowActions({
            header: 'Actions',
            autoWidth: false,
            width: 90,
            actions: [{
                    sortable: false,
                    tooltip: 'Remove Item',
                    iconCls: 'my-icon12',
                    callback: function (grid, rec, action, row, col) {
                        Ext.Msg.confirm('Notification', 'Do you want to remove this?', function (btn) {
                            if (btn == 'yes') {
                                grid.store.removeAt(row);
                                grid.getView().refresh();
                            }
                        });
                    }
                }]
        });
        return item_grid_action;
    }


    /* Creating Slider Grid   */
    var sliderGrid = function () {

        var invoiceItemStore = new Ext.data.JsonStore({
            fields: ['item', 'item_id', 'hsncode', {name: 'mrp', type: 'number'}, {name: 'rate', type: 'float'}, {name: 'qty', type: 'number'}, {name: 'igst', type: 'float'},
                {name: 'cgst', type: 'float'},
                {name: 'sgst', type: 'float'}, {name: 'amt_bf_tax', type: 'float'},
                {name: 'amt_af_tax', type: 'float'}],
            url: modURL + '&op=getInvoiceItems',
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        var slider_grid = new Ext.grid.GridPanel({
            store: invoiceItemStore,
            enableColumnMove: false,
            frame: false,
            border: false,
            layout: 'fit',
            title: '',
            flex: 2,
            id: 'sl_slidergrid',
            loadMask: true,
            cm: sidepanel_ColModel,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true

            }),
            stripeRows: true

        });
        return slider_grid;
    }; // slider grid ends here

    /* Store for Item grid */
    var itemStore = new Ext.data.JsonStore({
        fields: ['item', 'item_id', {name: 'mrp', type: 'number'}, {name: 'rate', type: 'float'},
            {name: 'quantity', type: 'number'}, 'hsn', 'tax_percentage',
            {name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'},
            {name: 'sgst', type: 'float'}, {name: 'amountwithouttax', type: 'float'},
            {name: 'amountwithtax', type: 'float'}],
        remoteSort: true,
        method: 'post',
        totalProperty: 'totalCount',
        root: 'data',
        autoLoad: false,
        listeners: {
            remove: function (obj, record, index) {
                invoiceTotalCalculation();
                Ext.getCmp('sl_item_field').focus();

            }
        }
    });

    var TotalAmount_ItemStore = function () {
        var purchase_invoice_store = new Ext.data.JsonStore({
            method: 'post',
            fields: [{name: 'tax_percentage'}, {name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'},
                {name: 'sgst', type: 'float'}, {name: 'amountwithouttax', type: 'float'},
                {name: 'amountwithtax', type: 'float'}, {name: 'hideCancelbtn', type: 'number'}]
        });
        return purchase_invoice_store;
    };

    var invoiceTotalCalculation = function () {
        var grid_store = Ext.getCmp('sl_itemgrid').getStore();
        var grandtotal = grid_store.sum('amountwithtax');
        var igsttotal = (grid_store.sum('igst'));
        var cgsttotal = (grid_store.sum('cgst'));
        var sgsttotal = (grid_store.sum('sgst'));
        var amtbftaxtotal = (grid_store.sum('amountwithouttax'));
        var amtaftaxtotal = (grid_store.sum('amountwithtax'));
        var tax = igsttotal + cgsttotal + sgsttotal;
        var totalquantity = grid_store.sum('quantity');
        var totalitems = (grid_store.getCount());
        var titletotal = 'Total :';
        var row_total = new Ext.data.Record.create([
            {name: 'tax_percentage'},
            {name: 'igst', type: 'float'},
            {name: 'cgst', type: 'float'},
            {name: 'sgst', type: 'float'},
            {name: 'amountwithouttax', type: 'float'},
            {name: 'amountwithtax', type: 'float'},
            {name: 'hideCancelbtn', type: 'number'}

        ]);
        var totalsdata = new row_total({
            tax_percentage: titletotal,
            igst: igsttotal,
            cgst: cgsttotal,
            sgst: sgsttotal,
            amountwithouttax: amtbftaxtotal,
            amountwithtax: amtaftaxtotal,
            hideCancelbtn: 1

        });
        Ext.getCmp('sl_itemgridTotal').getStore().removeAll(true);
        Ext.getCmp('sl_itemgridTotal').stopEditing(); //stops any acitve editing
        Ext.getCmp('sl_itemgridTotal').getStore().add(totalsdata);
        Ext.getCmp('sl_grandtotal').setValue(grandtotal), "0.00";
        Ext.getCmp('sl_netamount').setValue(Math.round(grandtotal));
        Ext.getCmp('sl_igsttotal').setValue(igsttotal);
        Ext.getCmp('sl_cgsttotal').setValue(cgsttotal);
        Ext.getCmp('sl_sgsttotal').setValue(sgsttotal);
        Ext.getCmp('sl_tax').setValue(tax);
        Ext.getCmp('sl_totalquantity').setValue(totalquantity);
        Ext.getCmp('sl_totalitems').setValue(totalitems);
    }
    /* Function for add items in item grid  */
    var item_add = function (item) {
        var rdata;
        var row = new Ext.data.Record.create([
            {name: 'item'},
            {name: 'item_id'},
            {name: 'mrp', type: 'float'},
            {name: 'rate', type: 'float'},
            {name: 'quantity', type: 'float'},
            {name: 'hsn'},
            {name: 'tax_percentage', type: 'float'},
            {name: 'igst', type: 'float'},
            {name: 'cgst', type: 'float'},
            {name: 'sgst', type: 'float'},
            {name: 'amountwithouttax', type: 'float'},
            {name: 'amountwithtax', type: 'float'}
        ]);
        var item = Ext.getCmp('sl_item_field').getRawValue();
        var item_id = Ext.getCmp('sl_item_field').getValue();
        var rate = Ext.getCmp('sl_rate_field').getRawValue();
        var quantity = Ext.getCmp('sl_qty_field').getValue();
        var amount = Ext.getCmp('sl_amount').getValue();
        Ext.Ajax.request({
            waitMsg: 'Processing',
            url: modURL,
            params: {
                op: 'getInvoiceItemdetails',
                item_id: Ext.getCmp('sl_item_field').getValue(),
                party_state_id: party_state_id,
                branch_state_id: branch_state_id
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', ACTION_FAIL);
            },
            success: function (response, options) {
                var tmp = response.responseText;
                tmp = Ext.decode(tmp);
                if (tmp.success === true) {
                    var igst = (amount * (tmp.data.IGST));
                    var cgst = (amount * (tmp.data.CGST));
                    var sgst = (amount * (tmp.data.SGST));

                    rdata = new row({
                        item: item,
                        item_id: item_id,
                        mrp: tmp.data.stit_MRP,
                        rate: rate,
                        quantity: quantity,
                        hsn: tmp.data.stit_HSNCode,
                        tax_percentage: tmp.data.stit_GST,
                        igst: igst,
                        cgst: cgst,
                        sgst: sgst,
                        amountwithouttax: amount,
                        amountwithtax: amount + igst + cgst + sgst

                    });
                    Ext.getCmp('sl_itemgrid').getStore().add(rdata);
                    invoiceTotalCalculation();

                } else
                {
                    Ext.MessageBox.alert('Notification', tmp.msg);
                }

                excludeItemIds.push(Ext.getCmp('sl_item_field').getValue());

                var itemstore = Ext.getCmp('sl_item_field').getStore();
                itemstore.removeAll();
                itemstore.fireEvent('load', itemstore, [], {});
                Ext.getCmp('sl_item_field').clearValue();
                Ext.getCmp('sl_item_field').lastQuery = null;
                Ext.getCmp('sl_rate_field').reset();
                Ext.getCmp('sl_qty_field').reset();
                Ext.getCmp('sl_amount').reset();
                Ext.getCmp('sl_discount').reset();
                Ext.getCmp('sl_netamount').reset();

            }
        });
    }

    /* Creating Item Grid   */
    var itemGrid = function (invoiceRecord) {
        var colModel = salesInvoiceColModel();
        var itemComboStore = function () {
            var store = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + '&op=getItems',
                method: 'post',
                fields: ['item_id', 'item_name'],
                totalProperty: 'totalCount',
                root: 'data',
                id: 'sl_itemcombo',
                name: 'itemcombo',
                remoteFilter: true,
                autoload: true,
                listeners: {
                    beforeload: function (st, opt) {
                        st.baseParams.excludeIds = Ext.encode(excludeItemIds);
                    }
                }

            });
            return store;
        };

        var item_action = item_grid_Action();
        var item_store = itemComboStore();
        var itemgrid_panel = new Ext.grid.GridPanel({
            store: itemStore,
            enableColumnMove: false,
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'sl_itemgrid',
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                keydown: function (e)
                {
                    if ((e.getCharCode() == 99) || (e.getCharCode() == 67))
                    {
                        var grid = Ext.getCmp('sl_itemgrid');
                        var selected = grid.getSelectionModel().getSelected();
                        var row = grid.getStore().indexOf(selected);
                        var record = grid.getStore().getAt(row);
                        removeFromItemStore(row);
                        excludeItemIds.remove(record.get('item_id'));
                        e.stopEvent();
                        Ext.getCmp('sl_item_field').lastQuery = null;
                        Ext.getCmp('sl_item_field').reset();
                    } else if (e.getCharCode() == e.ENTER) {

                        var gridItemCount = Ext.getCmp('sl_itemgrid').getStore().getCount();
                        console.log("itmcnt", gridItemCount);
                        if (gridItemCount > 0) {
                            e.stopEvent();
                            Ext.getCmp('sl_discount').focus();
                        } else
                        {
                            e.stopEvent();
                            Ext.Msg.alert("Notification", "Please add any item", function (btn) {
                                Ext.getCmp('sl_item_field').focus();
                            });
                        }

                    }
                }
            },

            cm: colModel,
            tbar: [
                {html: '&nbsp;Item : &nbsp;'},
                {xtype: 'combo',
                    anchor: '99%',
                    name: 'item_field',
                    id: 'sl_item_field',
                    width: 210,
                    store: item_store,
                    style: {'margin-bottom': '2px', 'margin-top': '2px'},
                    mode: 'remote',
                    hiddenName: 'item_field',
                    displayField: 'item_name',
                    valueField: 'item_id',
                    triggerAction: 'all',
                    forceSelection: true,
                    editable: true,
                    typeAhead: false,
                    enableKeyEvents: true,
                    autoSelect: true,
                    submitValue: true,
                    alloWBlank: false,
                    maxChars: 50,
                    minChars: 2,
                    listeners: {

                        select: function (combo, record, index)
                        {
                            getRate(combo, record, index);

                        },

                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                if (field.getValue() != '')
                                {
                                    Ext.getCmp('sl_item_field').lastQuery = null;
                                    Ext.getCmp('sl_rate_field').focus();
                                } else
                                {
                                    Ext.getCmp('sl_item_field').lastQuery = null;
                                    Ext.getCmp('sl_itemgrid').getSelectionModel().selectRow(0);
                                    Ext.getCmp('sl_itemgrid').getView().focusRow(0);
                                }
                            }

                        }

                    }

                },

                {html: '&nbsp;Rate : &nbsp;'},
                {
                    xtype: 'numberfield',
                    id: 'sl_rate_field',
                    name: 'rate_field',
                    allowNegative: false,
                    style: {'margin-bottom': '2px', 'margin-top': '2px'},
                    anchor: '97%',
                    width: 100,
                    fieldStyle: 'text-align: right;',
                    enableKeyEvents: true,
                    listeners: {
                        keyup: function ()
                        {
                            var qty = Ext.getCmp('sl_qty_field').getValue();
                            var rate = Ext.getCmp('sl_rate_field').getValue();
                            var item = Ext.getCmp('sl_item_field').getRawValue();
                            if (item !== '')
                            {
                                var amnt = (qty * rate);
                                Ext.getCmp('sl_amount').setValue(amnt);
                            }
                        },
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                Ext.getCmp('sl_qty_field').focus();
                            }
                        },
                        afterrender: function (numfield) {
                            this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                        }
                    }
                }, {html: '&nbsp;Quantity : &nbsp;'},
                {
                    xtype: 'numberfield',
                    id: 'sl_qty_field',
                    name: 'qty_field',
                    style: {'margin-bottom': '2px', 'margin-top': '2px'},
                    allowNegative: false,
                    allowDecimals: false,
                    anchor: '97%',
                    width: 100,
                    enableKeyEvents: true,
                    listeners: {
                        keyup: function ()
                        {
                            var qty = Ext.getCmp('sl_qty_field').getValue();
                            var rate = Ext.getCmp('sl_rate_field').getValue();
                            var item = Ext.getCmp('sl_item_field').getRawValue();
                            if (item !== '')
                            {
                                var amnt = (qty * rate);
                                Ext.getCmp('sl_amount').setValue(amnt);
                            }
                        },

                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {

                                var item = Ext.getCmp('sl_item_field').getValue();
                                var qty = Ext.getCmp('sl_qty_field').getValue();

                                if (item != "")
                                {
                                    if (qty != "")
                                    {
                                        item_add();
                                        Ext.getCmp('sl_item_field').focus();

                                    } else
                                    {
                                        Ext.MessageBox.alert('Notification', "Quantity Field cannot be blank", function () {
                                            Ext.getCmp('sl_qty_field').focus();
                                        });
                                    }
                                }

                            }
                        }
                    }
                },
                {html: '&nbsp;Amount : &nbsp;'},
                {
                    xtype: 'numberfield',
                    id: 'sl_amount',
                    name: 'amount',
                    style: {'margin-bottom': '2px', 'margin-top': '2px'},
                    allowNegative: false,
                    anchor: '97%',
                    width: 100,
                    readOnly: true
                },
                {html: '&nbsp;'},
                {
                    xtype: 'button',
                    text: 'Add',
                    iconCls: 'finascop_add',
                    style: {'margin-bottom': '2px', 'margin-top': '2px'},
                    id: 'sl_item_addbtn',
                    handler: function () {

                        if (chkd == 'true')
                        {
                            if (Ext.isEmpty(Ext.getCmp('sl_party').getValue()))
                            {
                                Ext.MessageBox.alert("Notification", "Please Select Party");
                                return;
                            }
                        }
                        if (Ext.isEmpty(Ext.getCmp('sl_item_field').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Please Select Item");
                            return;
                        } else if (Ext.isEmpty(Ext.getCmp('sl_rate_field').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Please Add Rate");
                            return;
                        } else if (Ext.isEmpty(Ext.getCmp('sl_qty_field').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Please Add Quantity");
                            return;
                        } else if (Ext.isEmpty(Ext.getCmp('sl_amount').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Please Add Amount");
                            return;
                        }

                        item_add();

                    }
                }],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true

        });
        return itemgrid_panel;
    };

    var getRate = function (combo, record, index)
    {

        var item_id = record.get('item_id');
        if (item_id != '')
        {
            if (chkd == false) {
                Ext.Ajax.request({
                    waitMsg: 'Processing',
                    url: modURL,
                    params: {
                        op: 'getCommonRate',
                        item_id: item_id
                    },
                    failure: function (response, options) {
                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                    },
                    success: function (response, options) {
                        eval("var tmp=" + response.responseText);
                        if (tmp.success === true) {
                            Ext.getCmp('sl_rate_field').setValue(tmp.data.stbr_CommonRate);
                        } else
                        {
                            Ext.MessageBox.alert('Notification', tmp.msg, function () {
                                Ext.getCmp('sl_item_field').clearValue();
                            });
                        }
                    }
                });
            } else
            {
                var party = Ext.getCmp('sl_party').getValue();
                if (party == '')
                {
                    Ext.MessageBox.alert('Alert', "Please select the Party");
                } else
                    Ext.Ajax.request({
                        waitMsg: 'Processing',
                        url: modURL,
                        params: {
                            op: 'getPartyRate',
                            item_id: item_id,
                            party_id: party_id
                        },
                        failure: function (response, options) {
                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                        },
                        success: function (response, options) {
                            eval("var tmp=" + response.responseText);
                            if (tmp.success === true) {
                                Ext.getCmp('sl_rate_field').setValue(tmp.data.stbp_Rate);
                            } else
                            {
                                Ext.MessageBox.alert('Notification', tmp.msg, function () {
                                    Ext.getCmp('sl_item_field').clearValue();
                                });
                            }
                        }
                    });
            }
        } else
        {
            Ext.getCmp('sl_rate_field').setValue(0);
        }

        Ext.getCmp('sl_qty_field').reset();
        Ext.getCmp('sl_amount').reset();
    }



    var salesInvoiceColModel = function ()
    {
//column model for invoiceItemGrid and invoiceTotalGrid
        return new Ext.grid.ColumnModel([
            {
                header: 'Item',
                sortable: false,
                hideable: false,
                dataIndex: 'item',
                tooltip: 'Item',
                width: 130

            },
            {
                header: 'MRP',
                sortable: false,
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                dataIndex: 'mrp',
                tooltip: 'MRP',
                width: 60,
                align: 'right'
            },
            {
                header: 'Rate',
                sortable: false,
                dataIndex: 'rate',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'RATE',
                width: 60,
                align: 'right'
            },
            {
                header: 'Qty',
                sortable: false,
                dataIndex: 'quantity',
                xtype: 'numbercolumn',
                format: '000',
                tooltip: 'Quantity',
                width: 60,
                align: 'right'
            },
            {
                header: 'HSN',
                sortable: false,
                dataIndex: 'hsn',
                tooltip: 'HSN',
                width: 60
            },
            {
                header: 'Tax %',
                sortable: false,
                dataIndex: 'tax_percentage',
                tooltip: 'Tax Percentage',
                width: 60,
                align: 'right'
            },
            {
                header: 'IGST',
                sortable: false,
                dataIndex: 'igst',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'IGST',
                width: 60,
                align: 'right'
            },
            {
                header: 'CGST',
                sortable: false,
                dataIndex: 'cgst',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'CGST',
                width: 60,
                align: 'right'
            },
            {
                header: 'SGST',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                sortable: false,
                dataIndex: 'sgst',
                tooltip: 'SGST',
                width: 60,
                align: 'right'
            },
            {
                header: 'Amt Bf . Tax',
                sortable: false,
                dataIndex: 'amountwithouttax',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Amount Before Tax',
                width: 100,
                align: 'right'
            },
            {
                header: 'Amt Af. Tax',
                sortable: false,
                dataIndex: 'amountwithtax',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT,
                tooltip: 'Amount After Tax',
                width: 100,
                align: 'right'
            },
            {
                xtype: 'actioncolumn',
                hideable: false,
                id: 'removeitem',
                width: 25,
                items: [{
                        sortable: false,
                        getClass: function (v, meta, rec) {
                            if (rec.get('hideCancelbtn') == 1)
                            {
                                return 'finascop_hideicon';
                            } else
                            {
                                return 'finascop_delete';
                            }
                        },
                        tooltip: 'Remove Item - Press c to remove item',

                        handler: function (grid, rowIndex, colIndex) {
                            var record = grid.getStore().getAt(rowIndex);
                            removeFromItemStore(rowIndex);
                            excludeItemIds.remove(record.get('item_id'));
                            Ext.getCmp('sl_item_field').lastQuery = null;
                            Ext.getCmp('sl_item_field').reset();
                            Ext.getCmp('sl_discount').setValue('0');
                            Ext.getCmp('sl_signature').reset();
                        }

                    }]
            }
        ]);
    }

    var removeFromItemStore = function (indx)
    {
        Ext.Msg.confirm('Confirmation', 'Do you really want to remove this?', function (btn) {
            if (btn == 'yes') {
                Ext.getCmp('sl_itemgrid').getStore().removeAt(indx);
                Ext.getCmp('sl_itemgrid').getView().refresh();
            }

        });
    }

//total amount calculation-->
    var invoiceTotalGrid = function () {
        var itemTotalGridStore = TotalAmount_ItemStore();
        var colModel = salesInvoiceColModel();
        var itemgridTotal_panel = new Ext.grid.GridPanel({
            store: itemTotalGridStore,
            layout: 'fit',
            frame: false,
            border: false,
            title: '',
            autoHeight: true,
            id: 'sl_itemgridTotal',
            loadMask: true,
            autoScroll: true,
            hideHeaders: true,
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return 'finascop_boldFont td';
                }
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: colModel,
            stripeRows: true
        });
        return itemgridTotal_panel;
    };

    var totalPanel = function ()
    {
        var total_panel = new Ext.Panel({
            id: 'totalpanel',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnwidth: .20,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Grand Total',
                                    name: 'grandtotal',
                                    id: 'sl_grandtotal',
                                    allowNegative: false,
                                    anchor: '95%',
                                    width: 200,
                                    readOnly: true,
                                    style: {'text-align': 'right', 'margin-top': '4px'}
                                }]
                        }]

                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnwidth: .20,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Discount',
                                    id: 'sl_discount',
                                    name: 'discount',
                                    anchor: '95%',
                                    allowNegative: false,
                                    style: {'text-align': 'right'},
                                    enableKeyEvents: true,
                                    width: 200,
                                    listeners: {

                                        blur: function (txt)
                                        {
                                            txt.setValue(Ext.util.Format.number(txt.getValue(), FINASCOP_CURRENCY_FORMAT));
                                        },

                                        keyup: function ()
                                        {
                                            var discount = Ext.getCmp('sl_discount').getValue();
                                            var grandtotal = Ext.getCmp('sl_grandtotal').getValue();
                                            var netamnt = Math.round(grandtotal - discount);
                                            var netamount = Ext.util.Format.number(netamnt, "0.00");
                                            Ext.getCmp('sl_netamount').setValue(netamount);

                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {

                                                Ext.getCmp('sl_signature').focus();

                                            }
                                        }
                                    }
                                }]
                        }]

                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnwidth: .20,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Net Amount',
                                    id: 'sl_netamount',
                                    name: 'netamount',
                                    allowNegative: false,
                                    anchor: '95%',
                                    width: 200,
                                    format: '0.00',
                                    readOnly: true,
                                    style: {'text-align': 'right'}
                                }]
                        }]

                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnwidth: .20,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Signature',
                                    id: 'sl_signature',
                                    anchor: '95%',
                                    width: 200,
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {

                                                Ext.getCmp('sl_terms').focus();

                                            }
                                        }
                                    }
                                }]
                        }]

                },
                {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 0.20,
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'IGST Total',
                            id: 'sl_igsttotal',
                            anchor: '99%',
                            allowNegative: false,
                            allowDecimals: false,
                            hideLabel: true,
                            hidden: true
                        }]

                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'CGST Total',
                            id: 'sl_cgsttotal',
                            anchor: '99%',
                            allowNegative: false,
                            allowDecimals: false,
                            hideLabel: true,
                            hidden: true
                        }]

                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'SGST Total',
                            id: 'sl_sgsttotal',
                            anchor: '99%',
                            allowNegative: false,
                            allowDecimals: false,
                            hideLabel: true,
                            hidden: true
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Total Quantity ',
                            id: 'sl_totalquantity',
                            name: 'totalquantity',
                            allowNegative: false,
                            allowDecimals: false,
                            anchor: '99%',
                            hideLabel: true,
                            hidden: true
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'numberfield',
                            fieldLabel: 'Tax ',
                            id: 'sl_tax',
                            name: 'tax',
                            allowNegative: false,
                            allowDecimals: false,
                            anchor: '99%',
                            hideLabel: true,
                            hidden: true
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Total Items',
                            id: 'sl_totalitems',
                            name: 'totalitems',
                            anchor: '99%',
                            hideLabel: true,
                            hidden: true
                        }]
                }

            ]
        });
        return total_panel;
    };

    var termsPanel = function ()
    {
        var TermsStore = termsStore();
        var paymentmodestore = new Ext.data.ArrayStore({
            fields: ['id', 'paymode'],
            data: [['1', 'Cash'], ['2', 'Credit']
            ]
        });
        var terms_panel = new Ext.Panel({
            id: 'termspanel',
            layout: 'column',
            items: [
                {
                    layout: 'column',
                    columnwidth: 1,
                    items: [{
                            layout: 'form',
                            columnwidth: .20,
                            items: [
                                {xtype: 'combo',
                                    fieldLabel: 'Terms',
                                    anchor: '99%',
                                    displayField: 'terms',
                                    id: 'sl_terms',
                                    name: 'terms',
                                    hiddenName: 'terms',
                                    valueField: 'id',
                                    store: TermsStore,
                                    triggerAction: 'all',
                                    labelStyle: mandatory_label,
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    autoLoad: true,
                                    mode: 'remote',
                                    minChars: 1,
                                    width: 200,
                                    listeners: {
                                        select: function (combo, record, index)
                                        {
                                            var term = record.get('id');
                                            if (term)
                                            {
                                                Ext.Ajax.request({
                                                    waitMsg: 'Processing',
                                                    url: modURL,
                                                    params: {
                                                        op: 'getTermDetails',
                                                        term: term
                                                    },
                                                    failure: function (response, options) {
                                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                    },
                                                    success: function (response, options) {
                                                        eval("var tmp=" + response.responseText);
                                                        if (tmp.success === true) {
                                                            Ext.getCmp('sl_description').setValue(tmp.data.inte_termsDetails);

                                                        } else
                                                        {
                                                            Ext.MessageBox.alert('Notification', tmp.msg);
                                                        }
                                                    }
                                                });
                                            }
                                        },

                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {

                                                Ext.getCmp('sl_paymentmode').focus();

                                            }
                                        }
                                    }

                                }]
                        }

                    ]
                },
                {
                    layout: 'column',
                    columnwidth: 1,
                    items: [{
                            layout: 'form',
                            columnwidth: 1,
                            items: [{
                                    xtype: 'textarea',
                                    fieldLabel: '',
                                    name: 'description',
                                    id: 'sl_description',
                                    width: 360,
                                    readOnly: true

                                }]
                        }]

                },
                {
                    layout: 'column',
                    columnwidth: 1,
                    items: [{
                            layout: 'form',
                            columnwidth: .40,
                            items: [
                                {
                                    xtype: 'combo',
                                    fieldLabel: 'Payment Modes',
                                    name: 'paymentmode',
                                    id: 'sl_paymentmode',
                                    store: paymentmodestore,
                                    anchor: '99%',
                                    displayField: 'paymode',
                                    valueField: 'id',
                                    typeAhead: true,
                                    mode: 'local',
                                    labelStyle: mandatory_label,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    width: 190,
                                    editable: true,
                                    enableKeyEvents: true,
                                    listeners:
                                            {
                                                specialkey: function (field, e) {

                                                    if (e.getKey() == e.ENTER) {

                                                        Ext.Msg.confirm('Confirmation', 'Do you want to Save this?', function (btn) {
                                                            if (btn == 'yes') {
                                                                var inv_date = Ext.getCmp('sl_inv_date').getValue();
                                                                var party = Ext.getCmp('sl_party').getValue();
                                                                var paymentmode = Ext.getCmp('sl_paymentmode').getValue();
                                                                var terms = Ext.getCmp('sl_terms').getValue();
                                                                var description = Ext.getCmp('sl_paymentmode').getValue();
                                                                var purchase_order_date = Ext.getCmp('sl_purchase_order_date').getValue();
                                                                if (Ext.isEmpty(inv_date))
                                                                {
                                                                    Ext.MessageBox.alert('Alert', "Please Select Invoice Date");
                                                                } else if (Ext.isEmpty(party) && (chkd == true))
                                                                {
                                                                    Ext.MessageBox.alert('Alert', "Please Select Party");
                                                                } else if (Ext.isEmpty(terms))
                                                                {
                                                                    Ext.MessageBox.alert('Alert', "Please Select Terms");
                                                                } else if (Ext.isEmpty(paymentmode))
                                                                {
                                                                    Ext.MessageBox.alert('Alert', "Please Select the Payment Mode");
                                                                } else if (purchase_order_date > inv_date)
                                                                {
                                                                    Ext.MessageBox.alert('Alert', "Purchase Date cannot be greater than Invoice Date");
                                                                } else
                                                                {
                                                                    logInvoiceDetails();
                                                                }
                                                            }

                                                        });

                                                    }
                                                }
                                            }
                                }

                            ]
                        }]
                }

            ]
        });
        return terms_panel;
    };

    var viewinvoice_panel = function (invoiceRecord) {
        function addRawValueToStore(cbx) {
            var comboStore = cbx.getStore();
            comboStore.removeAll();
            var rec = new Ext.data.Record.create([
                {name: 'id'},
                {name: 'party'},
                {name: 'party_state_id'},
                {name: 'br_id'}
            ]);

            var data = new rec({
                id: 0,
                party: cbx.getRawValue(),
                party_state_id: '',
                br_id: ''
            });
            comboStore.add(data);
        }
        var PartyStore = partyStore();
        var TermsStore = termsStore(invoiceRecord);
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            url: modURL,
            id: 'sl_invoice_form',
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [
                        {layout: 'form',
                            columnWidth: 0.10,
                            items: [{
                                    xtype: 'checkbox',
                                    id: 'sl_cb1',
                                    name: 'cb1',
                                    bodyStyle: 'padding: 15px 10px;',
                                    labelSeparator: '',
                                    hideLabel: true,
                                    boxLabel: 'Is Party',
                                    fieldLabel: 'Is Party',
                                    enableKeyEvents: true,
                                    checked: true,
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1);
                                        },

                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('sl_party').focus();
                                            }
                                            if (e.getKey() == e.UP) {
                                                Ext.getCmp('sl_cb1').setValue(true);
                                            }
                                            if (e.getKey() == e.DOWN) {
                                                Ext.getCmp('sl_cb1').setValue(false);
                                            }
                                        },

                                        check: function (cb1, checked) {
                                            excludeItemIds = [];
                                            Ext.getCmp('sl_itemgridTotal').getStore().removeAll();
                                            Ext.getCmp('sl_itemgridTotal').getView().refresh();
                                            Ext.getCmp('sl_itemgrid').getStore().removeAll(true);
                                            Ext.getCmp('sl_itemgrid').getView().refresh();
                                            Ext.getCmp('sl_item_field').clearValue();
                                            Ext.getCmp('sl_rate_field').reset();
                                            Ext.getCmp('sl_qty_field').reset();
                                            Ext.getCmp('sl_amount').reset();
                                            Ext.getCmp('sl_purchcase_order_no').reset();
                                            Ext.getCmp('sl_purchase_order_date').reset();
                                            Ext.getCmp('sl_ref_no').reset();
                                            Ext.getCmp('sl_inv_date').reset();
                                            Ext.getCmp('sl_discount').reset();
                                            Ext.getCmp('sl_netamount').reset();
                                            Ext.getCmp('sl_signature').reset();
                                            Ext.getCmp('sl_grandtotal').reset();
                                            Ext.getCmp('sl_terms').reset();
                                            Ext.getCmp('sl_description').reset();
                                            Ext.getCmp('sl_paymentmode').reset();
                                            chkd = checked;
                                            if (chkd == false) {
                                                Ext.getCmp('sl_party').getStore().baseParams.isParty = false;
                                                Ext.getCmp('sl_party').getStore().load();
                                                Ext.getCmp('sl_party').setHideTrigger(true);
                                                var newLabel = 'Recieved From:';
                                                if (!Ext.getCmp('sl_party').rendered) {
                                                    Ext.getCmp('sl_party').fieldLabel = newLabel;
                                                } else
                                                {
                                                    Ext.getCmp('sl_party').label.update(newLabel);
                                                }
                                            } else
                                            {
                                                Ext.getCmp('sl_party').getStore().baseParams.isParty = true;
                                                Ext.getCmp('sl_party').getStore().load();
                                                Ext.getCmp('sl_party').setHideTrigger(false);
                                                var newLabel = 'Party:';
                                                if (!Ext.getCmp('sl_party').rendered) {
                                                    Ext.getCmp('sl_party').fieldLabel = newLabel;
                                                } else
                                                {
                                                    Ext.getCmp('sl_party').label.update(newLabel);
                                                }
                                            }
                                            Ext.getCmp('sl_item_field').clearValue();
                                            Ext.getCmp('sl_rate_field').reset();
                                            Ext.getCmp('sl_qty_field').reset();
                                            Ext.getCmp('sl_amount').reset();
                                        }

                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.18,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Party',
                                    labelStyle: mandatory_label,
                                    id: 'sl_party',
                                    name: 'party',
                                    anchor: '99%',
                                    displayField: 'party',
                                    valueField: 'id',
                                    hiddenName: 'party',
                                    store: PartyStore,
                                    triggerAction: 'all',
                                    mode: 'remote',
                                    autoLoad: false,
                                    selectOnFocus: false,
                                    forceSelection: true,
                                    editable: true,
                                    enableKeyEvents: true,
                                    autoSelect: true,
                                    maxChars: 50,
                                    firstId: 0,
                                    typeAhead: true,
                                    minChars: 1,
                                    firstValue: ' ',
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                var chk = Ext.getCmp('sl_cb1').getValue();
                                                //console.log(chk);
                                                if (chk == true) {
                                                    Ext.getCmp('sl_purchcase_order_no').focus();
                                                } else {
                                                    addRawValueToStore(field);
                                                    Ext.getCmp('sl_purchcase_order_no').focus();
                                                }
                                            }
                                        },
                                        select: function (combo, record, index) {
                                            excludeItemIds = [];
                                            party_id = record.get('id');
                                            party_state_id = record.get('party_state_id');
                                            br_id = record.get('br_id');
                                            Ext.getCmp('sl_item_field').clearValue();
                                            Ext.getCmp('sl_rate_field').reset();
                                            Ext.getCmp('sl_qty_field').reset();
                                            Ext.getCmp('sl_amount').reset();
                                            Ext.getCmp('sl_purchcase_order_no').reset();
                                            Ext.getCmp('sl_purchase_order_date').reset();
                                            Ext.getCmp('sl_ref_no').reset();
                                            Ext.getCmp('sl_inv_date').reset();
                                            Ext.getCmp('sl_igsttotal').reset();
                                            Ext.getCmp('sl_cgsttotal').reset();
                                            Ext.getCmp('sl_sgsttotal').reset();
                                            Ext.getCmp('sl_grandtotal').reset();
                                            Ext.getCmp('sl_totalitems').reset();
                                            Ext.getCmp('sl_tax').reset();
                                            Ext.getCmp('sl_discount').reset();
                                            Ext.getCmp('sl_grandtotal').reset();
                                            Ext.getCmp('sl_netamount').reset();
                                            Ext.getCmp('sl_terms').reset();
                                            Ext.getCmp('sl_paymentmode').reset();
                                            Ext.getCmp('sl_signature').reset();
                                            Ext.getCmp('sl_itemgridTotal').getStore().removeAll();
                                            Ext.getCmp('sl_itemgridTotal').getView().refresh();
                                            Ext.getCmp('sl_itemgrid').getStore().removeAll(true);
                                            Ext.getCmp('sl_itemgrid').getView().refresh();

                                        }

                                    }

                                }]
                        },

                        {
                            layout: 'form',
                            labelAlign: 'top',
                            columnWidth: 0.18,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Purchase Order No',
                                    id: 'sl_purchcase_order_no',
                                    name: 'purchcase_order_no',
                                    labelAlign: 'top',
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('sl_purchase_order_date').focus();
                                            }
                                        }
                                    }

                                }]

                        }, {
                            layout: 'form',
                            columnWidth: 0.18,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'datefield',
                                    fieldLabel: 'Purchase Order Date',
                                    id: 'sl_purchase_order_date',
                                    name: 'purchase_order_date',
                                    anchor: '99%',
                                    format: 'd/m/Y',
                                    maxValue: new Date(),
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('sl_ref_no').focus();
                                            }
                                        },
                                        blur: function (txt)
                                        {
                                            txt.setValue('');
                                        }
                                    }
                                }]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.18,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Ref. No',
                                    id: 'sl_ref_no',
                                    name: 'ref_no',
                                    anchor: '99%',
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('sl_inv_date').focus();
                                            }
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.18,
                            labelAlign: 'top',
                            items: [
                                {
                                    xtype: 'datefield',
                                    fieldLabel: 'Invoice Date',
                                    labelStyle: mandatory_label,
                                    id: 'sl_inv_date',
                                    name: 'inv_date',
                                    anchor: '99%',
                                    format: 'd/m/Y',
                                    maxValue: new Date(),
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('sl_item_field').focus();
                                            }
                                        },
                                        blur: function (txt)
                                        {
                                            txt.setValue('');
                                        }
                                    }
                                }]
                        }
                    ]
                },
                {
                    xtype: 'fieldset',
                    title: 'Invoice Items',
                    columnWidth: 1,
                    id: 'sl_inv_items',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [itemGrid(invoiceRecord), invoiceTotalGrid()]
                                }]
                        }]

                },
                {
                    columnWidth: 1,
                    layout: 'column',
                    items: [{
                            columnWidth: .70,
                            layout: 'form',
                            items: [termsPanel()]

                        },
                        {
                            columnWidth: .30,
                            layout: 'form',
                            items: [totalPanel()]
                        }
                    ]
                }
            ]
        });
        return panel;
    };

    var invoice = function (invoiceRecord) {
        var invoice_panel = viewinvoice_panel(invoiceRecord);
        var invoice_window = Ext.getCmp('sl_invoice_window');
        itemStore.removeAll();
        if (Ext.isEmpty(invoice_window)) {
            var invoice_window = new Ext.Window({
                id: 'sl_invoice_window',
                title: 'Sales Invoice',
               // iconCls: 'finascop_invoice',
                modal: true,
                layout: 'fit',
                width: 900,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [invoice_panel
                ],
                buttons: [{
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        hidden: (invoiceRecord == null ? false : true),
                        handler: function () {
                            var inv_date = Ext.getCmp('sl_inv_date').getValue();
                            var party = Ext.getCmp('sl_party').getValue();
                            var paymentmode = Ext.getCmp('sl_paymentmode').getValue();
                            var terms = Ext.getCmp('sl_terms').getValue();
                            var description = Ext.getCmp('sl_paymentmode').getValue();
                            var purchase_order_date = Ext.getCmp('sl_purchase_order_date').getValue();
                            if (Ext.isEmpty(inv_date))
                            {
                                Ext.MessageBox.alert('Alert', "Please Select Invoice Date");
                            } else if (Ext.isEmpty(party) && (chkd == true))
                            {
                                Ext.MessageBox.alert('Alert', "Please Select Party");
                            } else if (Ext.isEmpty(terms))
                            {
                                Ext.MessageBox.alert('Alert', "Please Select Terms");
                            } else if (Ext.isEmpty(paymentmode))
                            {
                                Ext.MessageBox.alert('Alert', "Please Select the Payment Mode");
                            } else if (purchase_order_date > inv_date)
                            {
                                Ext.MessageBox.alert('Alert', "Purchase Date cannot be greater than Invoice Date");
                            } else
                            {

                                logInvoiceDetails();
                            }

                        }
                    }, {
                        text: 'Close',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            excludeItemIds = [];
                            invoice_window.close();
                        }
                    }]
            });
        }
        Ext.getCmp('sl_party').getStore().baseParams.isParty = true;
        Ext.getCmp('sl_party').getStore().load();
        invoice_window.show();
        invoice_window.doLayout();
        invoice_window.center();
    }

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var salesStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listInvoices',
                method: 'post'
            }),
            fields: ['InvID', 'InvoiceNo', 'InvoiceDate', 'Party', 'amount', 'tax', 'totalitems', {name: 'itemquantity', type: 'number'}, 'ClientPO', 'ClientPODate', 'RefNo', 'Bank', {name: 'itemstotal', type: 'float'}, {name: 'total_amount', type: 'float'}, {name: 'total_tax', type: 'float'}, {name: 'totalitemqty', type: 'float'}],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {

                load: function (store, record, options) {
                    Ext.getCmp('sl_sales_invoice_grid').getSelectionModel().selectRow(0);
                    var record = Ext.getCmp('sl_sales_invoice_grid').getSelectionModel().getSelected();
                     if (record !== undefined)
                        {
                    var total_amount = record.get('total_amount');
                    var total_tax = record.get('total_tax');
                    var itemstotal = record.get('itemstotal');
                    var totalitemqty = record.get('totalitemqty');
                    var number_of_col = Ext.getCmp('sl_sales_invoice_grid').getColumnModel().config.length;
                    var tooltipmaker = Ext.getCmp('sl_sales_invoice_grid').getColumnModel().config;

                    for (var i = 1; i < number_of_col; i++) {
                        var column_header = tooltipmaker[i].header;
                        if (column_header == 'Amount')
                        {
                            tooltipmaker[i].tooltip = 'Total Amount : ' + total_amount;
                        }
                        if (column_header == 'Tax')
                        {
                            tooltipmaker[i].tooltip = 'Total Tax : ' + total_tax;
                        }

                        if (column_header == 'No Of items')
                        {
                            tooltipmaker[i].tooltip = 'Total Tax : ' + itemstotal;
                        }
                        if (column_header == 'Items Qty')
                        {
                            tooltipmaker[i].tooltip = 'Total Tax : ' + totalitemqty;
                        }



                    }
                }
                else
                {
                    Ext.MessageBox.alert('Alert', 'No data Found');
                }

                }
            }
        });
        store.setDefaultSort('InvoiceNo', 'ASC');
        return store;
    };

    var InvoiceDetailsViewpanel = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            title: 'Invoice Details',
            height: 200,
            autoScroll: true,
            id: 'sl_details_view_panel_invoice',
            tpl: new Ext.XTemplate('<div class="finascop_details-outer">',
                    '<table border="0" width="99%" class="finascop_details-slider_font">',
                    '<tr><th width=200px>Invoice No:</th><td class="finascop_details-slider_table"> {InvoiceNo} </td></tr>',
                    '<tr><th width=200px>Invoice Date :</th> <td> {InvoiceDate} <td> </tr>',
                    '<tr><th width=200px>Party :</th> <td> {Party}</td></tr>',
                    '<tr><th width=200px>Purchase Order No :</th> <td>  {ClientPO} </td> </tr>',
                    '<tr><th width=200px>Purchase Order Date :</th> <td> {ClientPODate} </td> </tr>',
                    '<tr><th width=200px>Reference No :</th><td>{RefNo}</td></tr>',
                    '</table>',
                    '</div>'),
            buttons: [
                {
                    text: "Edit",
                    id: 'invoice_edit_btn',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_edit.png',
                    handler: function () {
                        Ext.getCmp('sl_invoiceeditform').setVisible(true);
                        Ext.getCmp('sl_details_view_panel_invoice').setVisible(false);
                    }
                }
            ]
        });
    };

    var editItem_panel = function () {
        var party_store = partyStore();
        var itemPanel = new Ext.FormPanel({
            id: 'sl_invoiceeditform',
            height: 200,
            border: false,
            layout: 'column',
            hideBorders: true,
            hidden: true,
            items: [{layout: 'form',
                    columnWidth: 0.50,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'saen_Id',
                            labelStyle: 'width:150px',
                            id: 'sl_saen_Id',
                            name: 'saen_Id',
                            anchor: '85%',
                            hidden: true

                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'invoiceno',
                            labelStyle: 'width:150px',
                            id: 'sl_invoiceno',
                            name: 'invoiceno',
                            anchor: '85%',
                            allowBlank: false,
                            hidden: true

                        },
                        {
                            xtype: 'datefield',
                            fieldLabel: 'Invoice Date',
                            labelStyle: 'width:150px',
                            id: 'sl_invoice_date',
                            name: 'invoice_date',
                            anchor: '85%',
                            allowBlank: true,
                            format: 'Y/m/d',
                            maxValue: new Date()
                        },
                        {
                            xtype: 'combo',
                            fieldLabel: 'Party',
                            labelStyle: 'width:150px',
                            name: 'party_name',
                            id: 'sl_party_name',
                            store: party_store,
                            anchor: '85%',
                            displayField: 'party',
                            valueField: 'id',
                            typeAhead: true,
                            mode: 'local',
                            forceSelection: true,
                            triggerAction: 'all',
                            editable: false
                        },

                        {
                            xtype: 'textfield',
                            fieldLabel: 'Purchase Order No',
                            labelStyle: 'width:150px',
                            id: 'sl_purchase_orderno',
                            name: 'purchase_orderno',
                            anchor: '85%',
                            allowBlank: true
                        },

                        {
                            xtype: 'datefield',
                            fieldLabel: 'Purchase Order Date',
                            labelStyle: 'width:150px',
                            id: 'sl_purchase_orderdate',
                            name: 'purchase_orderdate',
                            anchor: '85%',
                            allowBlank: true,
                            format: 'Y/m/d',
                            maxValue: new Date()

                        },

                        {
                            xtype: 'textfield',
                            fieldLabel: 'Reference No',
                            labelStyle: 'width:150px',
                            id: 'sl_referenceno',
                            name: 'referenceno',
                            anchor: '85%',
                            allowBlank: true
                        }]
                }],

            buttons: [{
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    handler: function () {
                        saveInvoiceEdits();
                    }
                },

                {
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    handler: function () {
                        Ext.getCmp('sl_invoiceeditform').setVisible(false);
                        Ext.getCmp('sl_details_view_panel_invoice').setVisible(true);
                    }
                }]
        });
        return itemPanel;
    }

    var saveInvoiceEdits = function () {
        WinMask = new Ext.LoadMask(Ext.getCmp('sl_invoiceeditform').getEl());
        WinMask.show();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (
                !Ext.isEmpty(Ext.getCmp('sl_party_name').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('sl_invoice_date').getValue())
                ) {
            var form_data = {
                saen_Id: Ext.getCmp('sl_saen_Id').getValue(),
                party: Ext.getCmp('sl_party_name').getRawValue(),
                purchase_orderno: Ext.getCmp('sl_purchase_orderno').getValue(),
                purchase_orderdate: Ext.util.Format.date(Ext.getCmp('sl_purchase_orderdate').getValue(), 'd/m/Y'),
                invoice_date: Ext.util.Format.date(Ext.getCmp('sl_invoice_date').getValue(), 'd/m/Y'),
                referenceno: Ext.getCmp('sl_referenceno').getValue(),
                invoiceno: Ext.getCmp('sl_invoiceno').getValue(),
                tstamp: t_stamp
            };
            var params = {
                action: 'Update',
                module: 'Finascop_Sale',
                op: 'saveEditedData',
                id: Ext.getCmp('sl_saen_Id').getValue(),
                extrainfo: 'fse'
            };
            APICall(params, Application.Finascop_Sale.saveEditedData, form_data);
        } else {
            Ext.MessageBox.alert('Error', 'Check the required fields');
        }
        WinMask.hide();
    };

    var salesGrid = function () {
        var sales_store = salesStore();
        var sales_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    name: 'InvoiceNo'
                }]
        });
        sales_filter.remote = true;
        sales_filter.autoReload = true;
        var sales_grid_panel = new Ext.grid.GridPanel(
                {
                    ds: sales_store,
                    layout: 'fit',
                    enableColumnMove: false,
                    iconCls: 'finascop_sales',
                    region: 'center',
                    frame: false,
                    border: true,
                    height: winsize.height * 0.80,
                    id: 'sl_sales_invoice_grid',
                    plugins: [sales_filter],
                    columns: [new Ext.grid.RowNumberer(),
                        {
                            header: 'Sales Register No',
                            id: 'sl_salesregno',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'InvID',
                            align: 'left',
                            tooltip: 'Sales Register No'
                        },
                        {
                            header: 'Date',
                            id: 'invoice_date',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'InvoiceDate',
                            align: 'center',
                            tooltip: 'Invoice Date'
                        },
                        {
                            header: 'Invoice No',
                            id: 'sl_invoice_no',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'InvoiceNo',
                            align: 'center',
                            tooltip: 'Invoice Number'
                        },
                        {
                            header: 'Party',
                            id: 'party',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'Party',
                            align: 'left',
                            tooltip: 'Party'
                        },
                        {
                            header: 'Amount',
                            id: 'sl_amount_field',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'amount',
                            tooltip: 'Amount',
                            align: 'right',
                            type: 'integer'
                        },
                        {
                            header: 'Tax',
                            id: 'tax',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'tax',
                            align: 'right',
                            tooltip: 'Tax'

                        },
                        {
                            header: 'No Of items',
                            id: 'totalitems',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'totalitems',
                            align: 'right',
                            tooltip: 'No Of items'
                        },
                        {
                            header: 'Items Qty',
                            id: 'itemquantity',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'itemquantity',
                            align: 'right',
                            tooltip: 'Total Quantity'
                        },
                        {
                            header: '',
                            hideHeaders: true,
                            dataIndex: '',
                            width: 40
                        }

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
                        viewready: updatePagination,
                        rowclick: function (grid, rowIndex, e) {
                            var invoiceRecord = grid.getStore().getAt(rowIndex);
                            var invItemStore = Ext.getCmp('sl_slidergrid').getStore();
                            itemStore.baseParams.invID = invoiceRecord.get('InvID');
                            invItemStore.baseParams.invID = invoiceRecord.get('InvID');
                            invItemStore.removeAll();
                            invItemStore.load({
                                callback: function (record, options, success) {
                                    var store = invItemStore;
                                    var row_total = new Ext.data.Record.create([

                                        {name: 'qty'},
                                        {name: 'igst', type: 'float'},
                                        {name: 'cgst', type: 'float'},
                                        {name: 'sgst', type: 'float'},
                                        {name: 'amt_bf_tax', type: 'float'},
                                        {name: 'amt_af_tax', type: 'float'}

                                    ]);

                                    var igsttotal = Ext.util.Format.number((store.sum('igst')), "0.00");
                                    var cgsttotal = Ext.util.Format.number((store.sum('cgst')), "0.00");
                                    var sgsttotal = Ext.util.Format.number((store.sum('sgst')), "0.00");
                                    var totalamount_btTax = Ext.util.Format.number((store.sum('amt_bf_tax')), "0.00");
                                    var totalamount_atTax = Ext.util.Format.number((store.sum('amt_af_tax')), "0.00");
                                    var totalsdata = new row_total({
                                        qty: "Total :",
                                        igst: igsttotal,
                                        cgst: cgsttotal,
                                        sgst: sgsttotal,
                                        amt_bf_tax: totalamount_btTax,
                                        amt_af_tax: totalamount_atTax
                                    });
                                    Ext.getCmp('sl_sliderTotal').getStore().removeAll();
                                    Ext.getCmp('sl_sliderTotal').getStore().add(totalsdata);
                                }
                            });
                            Ext.getCmp('sl_invoice_parent_panel').expand(true);
                            var visualsDescPanel = Ext.getCmp('sl_details_view_panel_invoice');
                            visualsDescPanel.update(invoiceRecord.data);
                            Ext.getCmp('sl_party_name').setValue(invoiceRecord.data.Party);
                            Ext.getCmp('sl_invoice_date').setValue(invoiceRecord.data.InvoiceDate);
                            Ext.getCmp('sl_purchase_orderno').setValue(invoiceRecord.data.ClientPO);
                            if (invoiceRecord.data.ClientPODate != '0000-00-00')
                            {
                                Ext.getCmp('sl_purchase_orderdate').setValue(invoiceRecord.data.ClientPODate);
                            } else
                            {
                                Ext.getCmp('sl_purchase_orderdate').setValue('');
                            }
                            Ext.getCmp('sl_referenceno').setValue(invoiceRecord.data.RefNo);
                            Ext.getCmp('sl_saen_Id').setValue(invoiceRecord.data.InvID);
                            Ext.getCmp('sl_invoiceno').setValue(invoiceRecord.data.InvoiceNo);
                            Ext.getCmp('sl_invoiceeditform').setVisible(false);
                            Ext.getCmp('sl_details_view_panel_invoice').setVisible(true);
                        }

                    },
                    tbar: [{
                            xtype: 'button',
                            text: 'Create Invoice', iconCls: 'finascop_add',
                            handler: function () {
                                var invoiceRecord = null;
                                invoice(invoiceRecord);
                            }
                        }],
                    bbar: new Ext.PagingToolbar({
                        pageSize: recs_per_page,
                        store: sales_store,
                        displayInfo: true,
                        displayMsg: 'Displaying records {0} - {1} of {2}',
                        emptyMsg: "No records to display"
                    })
                }
        );
        return sales_grid_panel;
    };

    var salesMasterPanel = function (id) {
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white", "padding": "5px 3px"},
            border: false,
            id: id,
            title: 'Sales Invoice',
            iconCls: 'finascop_invoice',
            items: [salesGrid(), invoiceTab()
            ]

        });
        return panel;
    };

    //Left side panel contents
    var invoiceTab = function () {
        var invheader = InvoiceDetailsViewpanel();
        var edit_item = editItem_panel();
        var tab = new Ext.Panel({
            frame: false,
            border: true,
            layout: 'border',
            region: 'east',
            title: 'Edit Invoice',
            width: winsize.width * 0.6,
            id: 'sl_invoice_parent_panel',
            height: winsize.height * 0.6,
            collapsible: true,
            collapseMode: 'mini',
            collapsed: true,
            cls: 'left_side_panel',
            items: [

                new Ext.Panel({
                    frame: false,
                    border: true,
                    autoheight: true,
                    region: 'center',
                    layout: 'vbox',
                    cls: 'left_side_panel',
                    layoutConfig: {
                        align: 'stretch',
                        pack: 'start'
                    },
                    items: [

                        sliderGrid(),
                        {
                            height: 25,
                            items: [sliderTotalGrid()]
                        }]
                }),
                {
                    layout: 'fit',
                    height: 200,
                    minHeight: 200,
                    region: 'north',
                    items: [invheader, edit_item]
                }
            ]

        });
        return tab;
    };
    //Left side panel contents ends here

    var sliderTotalGrid = function () {

        var sliderTotalGridStore = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                fields: [{name: 'qty'}, {name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'}, {name: 'sgst', type: 'float'},
                    {name: 'amt_bf_tax', type: 'float'}, {name: 'amt_af_tax', type: 'float'}]
            });
            return store;
        };

        var slidertotalGridStore = sliderTotalGridStore();
        var slider_total_panel = new Ext.grid.GridPanel({
            store: slidertotalGridStore,
            layout: 'fit',
            frame: false,
            border: false,
            title: '',
            height: 25,
            style: 'margin-top:2px;',
            id: 'sl_sliderTotal',
            loadMask: true,
            autoScroll: true,
            hideHeaders: true,
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return 'finascop_boldFont td';
                }
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            cm: sidepanel_ColModel,
            stripeRows: true
        });
        return slider_total_panel;
    };

    var sidepanel_ColModel = new Ext.grid.ColumnModel([
        {
            header: 'Item',
            sortable: true,
            hideable: false,
            dataIndex: 'item',
            tooltip: 'ITEM',
            width: 110,
            align: 'left'
        },
        {
            header: 'MRP',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            sortable: true,
            dataIndex: 'mrp',
            tooltip: 'MRP',
            width: 65,
            align: 'right'
        },
        {
            header: 'HSN Code',
            sortable: true,
            dataIndex: 'hsncode',
            tooltip: 'HSN Code',
            width: 75,
            align: 'right'
        },
        {
            header: 'Rate',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            sortable: true,
            dataIndex: 'rate',
            tooltip: 'Rate',
            width: 65,
            align: 'right'
        },
        {
            header: 'Quantity',
            sortable: true,
            dataIndex: 'qty',
            tooltip: 'Quantity',
            width: 70,
            align: 'right'
        },
        {
            header: 'IGST',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            sortable: true,
            dataIndex: 'igst',
            tooltip: 'IGST',
            width: 70,
            align: 'right'
        },
        {
            header: 'CGST',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            sortable: true,
            dataIndex: 'cgst',
            tooltip: 'CGST',
            width: 70,
            align: 'right'
        },
        {
            header: 'SGST',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            sortable: true,
            dataIndex: 'sgst',
            tooltip: 'SGST',
            width: 70,
            align: 'right'
        },
        {
            header: 'Amt Bf . Tax',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            sortable: true,
            dataIndex: 'amt_bf_tax',
            align: 'right',
            tooltip: 'Amount Before Tax',
            width: 85
        },
        {
            header: 'Amt Af. Tax',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT,
            sortable: true,
            dataIndex: 'amt_af_tax',
            align: 'right',
            tooltip: 'Amount After Tax',
            width: 85
        }
    ]);

    getBranchStateId = function ()
    {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            url: modURL,
            params: {
                op: 'getBranchStateId'

            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', ACTION_FAIL);
            },
            success: function (response, options) {
                eval("var tmp=" + response.responseText);
                if (tmp.success === true) {
                    branch_state_id = tmp.data.br_State;
                } else
                {
                    Ext.MessageBox.alert('Notification', tmp.msg);
                }
            }
        });
    }

    var SalesReturnslistStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['sare_id', 'sare_InvoiceNo', 'sare_InvoiceDate', 'sare_TotalItems', {name: 'sare_TotalItemsQty', type: 'number'}, 'sare_GrossAmt', 'Entryby', 'saen_Id', {name: 'showCancelBtn', type: 'number'}, 'has_returns', 'InvID', 'updated_on', {name: 'returned_item_total', type: 'float'}, {name: 'total_items_qty', type: 'float'}, {name: 'totalamount', type: 'float'}],
            url: modURL + '&op=getSalesReturnslist',
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            autoload: true,
            listeners: {

                load: function (store, record, options) {
                    Ext.getCmp('sl_sales_return_grid').getSelectionModel().selectRow(0);
                    var record = Ext.getCmp('sl_sales_return_grid').getSelectionModel().getSelected();
                    var returned_item_total = record.get('returned_item_total');
                    var total_items_qty = record.get('total_items_qty');
                    var totalamount = record.get('totalamount');
                    var number_of_col = Ext.getCmp('sl_sales_return_grid').getColumnModel().config.length;
                    var tooltipmaker = Ext.getCmp('sl_sales_return_grid').getColumnModel().config;

                    for (var i = 1; i < number_of_col; i++) {
                        var column_header = tooltipmaker[i].header;
                        if (column_header == 'Returned Items Count')
                        {
                            tooltipmaker[i].tooltip = 'Total Returned Item Count : ' + returned_item_total;
                        }
                        if (column_header == 'Qty')
                        {
                            tooltipmaker[i].tooltip = 'Total Qty : ' + total_items_qty;
                        }

                        if (column_header == 'Amount')
                        {
                            tooltipmaker[i].tooltip = 'Total Amount : ' + totalamount;
                        }




                    }

                }
            }
        });
        return store;
    };

    var createSalesReturnGrid = function () {
        var sales_return_store = SalesReturnslistStore();
        var sales_return_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    name: 'sare_InvoiceNo'
                }]
        });
        sales_return_filter.remote = true;
        sales_return_filter.autoReload = true;
        var sales_return_grid = new Ext.grid.GridPanel(
                {
                    ds: sales_return_store,
                    enableColumnMove: false,
                    layout: 'fit',
                    //iconCls: 'finascop_sales_return',
                    region: 'center',
                    frame: false,
                    border: true,
                    id: 'sl_sales_return_grid',
                    title: 'Sales Return',
                    sm: new Ext.grid.RowSelectionModel({
                        singleSelect: true

                    }),
                    plugins: [sales_return_filter],
                    columns: [new Ext.grid.RowNumberer(),
                        {
                            header: 'Invoice No',
                            dataIndex: 'sare_InvoiceNo',
                            align: 'right',
                            tooltip: 'Sales Return Invoice No',
                            sortable: true
                        },
                        {
                            header: 'Invoice date',
                            dataIndex: 'sare_InvoiceDate',
                            align: 'right',
                            tooltip: 'Sales Return Invoice Date',
                            sortable: true
                        },
                        {
                            header: 'Returned Items Count',
                            dataIndex: 'sare_TotalItems',
                            align: 'right',
                            tooltip: 'Returned Items Count',
                            width: 200,
                            sortable: true
                        },
                        {
                            header: 'Qty',
                            id: 'qty',
                            dataIndex: 'sare_TotalItemsQty',
                            align: 'right',
                            tooltip: 'Quantity',
                            width: 100,
                            sortable: true
                        },
                        {
                            header: 'Amount',
                            dataIndex: 'sare_GrossAmt',
                            tooltip: 'Amount',
                            align: 'right',
                            sortable: true
                        },
                        {
                            header: 'Entry By',
                            dataIndex: 'Entryby',
                            tooltip: 'Entry By',
                            align: 'right',
                            sortable: true

                        },
                        {
                            xtype: 'actioncolumn',
                            width: 67,
                            hideable: false,
                            items: [{

                                    getClass: function (v, meta, rec) {

                                        return 'finascop_sale_view';
                                    },
                                    tooltip: 'View Sales Return',
                                    handler: function (grid, rowIndex, colIndex) {
                                        grid.getSelectionModel().selectRow(rowIndex);
                                        var rec = sales_return_store.getAt(rowIndex);
                                        var id = 1;
                                        var t = new Date();
                                        var t_stamp = t.format("YmdHis");
                                        Ext.Ajax.request({
                                            url: modURL + '&op=getSalesReturn_InvoiceNo',
                                            method: 'POST',
                                            params: {
                                                invID: rec.get('InvID'),
                                                apikey: _SESSION.apikey,
                                                tstamp: t_stamp,
                                                item_id: 0

                                            },
                                            success: function (response) {

                                                var tmp = Ext.decode(response.responseText);
                                                if (tmp.success === true) {
                                                    var sare_invNo = tmp.data.sare_invNo;
                                                    createSalesReturns(id, rec, sare_invNo);
                                                } else {
                                                    Ext.MessageBox.alert("Notification", tmp.msg);
                                                }

                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            }
                                        });
                                    }
                                },
                                {
                                    sortable: false,
                                    getClass: function (v, meta, rec) {
                                        if (rec.get('showCancelBtn') == 0)
                                        {
                                            return 'finascop_sale_cancel';
                                        } else
                                        {
                                            return 'finascop_hideicon';
                                        }
                                    },
                                    tooltip: 'Cancel Sales Return',
                                    handler: function (grid, rowIndex, colIndex) {
                                        Ext.Msg.confirm('Notification', 'Do you want to remove this?', function (btn) {
                                            if (btn == 'yes') {
                                                var rec = sales_return_store.getAt(rowIndex);
                                                grid.store.removeAt(rowIndex);
                                                grid.getView().refresh();
                                                Ext.Ajax.request({
                                                    url: modURL + '&op=removeSalesReturn',
                                                    method: 'POST',
                                                    params: {
                                                        remove_SRid: rec.get('sare_id'),
                                                        saen_Id: rec.get('saen_Id'),
                                                        previous_key: rec.get('updated_on')

                                                    },
                                                    success: function (response) {
                                                        //submit success
                                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                                        if (tmp.success !== undefined && tmp.success === true) {
                                                            Ext.MessageBox.alert('status', tmp.msg);
                                                        } else
                                                        {
                                                            Ext.MessageBox.alert('status', tmp.msg);
                                                        }
                                                    },
                                                    //submit failure
                                                    failure: function (response) {
                                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                                        Ext.MessageBox.alert('Error', tmp.errors);
                                                    }
                                                });
                                            }
                                        });
                                    }


                                }

                            ]
                        }

                    ],
                    viewConfig: {
                        forceFit: true,
                        getRowClass: function (record, index) {
                        },
                        deferEmptyText: false,
                        emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

                    },
                    tbar: [{
                            xtype: 'button',
                            text: 'Create New Sales Return', iconCls: 'finascop_add',
                            handler: function () {

                                createSalesReturn();
                            }
                        }],
                    bbar: new Ext.PagingToolbar({
                        pageSize: recs_per_page,
                        store: sales_return_store,
                        displayInfo: true,
                        displayMsg: 'Displaying items {0} - {1} of {2}',
                        emptyMsg: "No records to display"
                    }),
                    listeners: {
                        viewready: updatePagination
                    }
                }
        );
        Ext.getCmp('sl_sales_return_grid').getStore().load();
        return sales_return_grid;
    };

    var createSalesReturn = function () {
        var salesReturn_panel = viewsalesReturn_panel();
        var sales_return_window = Ext.getCmp('sl_sales_return_window');
        if (Ext.isEmpty(sales_return_window)) {
            var sales_return_window = new Ext.Window({
                id: 'sl_sales_return_window',
                title: 'Sales Invoice search',
                iconCls: 'finascop_invoice',
                modal: true,
                layout: 'fit',
                width: 700,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [salesReturn_panel
                ],
                buttons: [
                    {
                        text: 'Close',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        listeners: {
                            close: function (win) {
                                Win.removeAll();
                                Ext.getCmp('sl_sales_return_grid').getStore().reload();
                                Ext.getCmp('sl_sales_return_grid').getView().refresh();
                            },
                            hide: function (win) {
                                console.info('just hidden');
                            },
                            click: function () {
                                sales_return_window.close();
                                Ext.getCmp('sl_sales_return_grid').getStore().reload();
                            }
                        }

                    }]
            });
        }

        sales_return_window.show();
        sales_return_window.doLayout();
        sales_return_window.center();
    }

    var salesReturnStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listsearchsalesreturn',
                method: 'post'
            }),
            fields: ['InvID', 'item_id', 'InvoiceNo', 'InvoiceDate', 'totalitems', 'totalitemsqty', 'has_returns', 'discount', 'gross_amount', 'previous_key', 'tax', 'net_amount', 'stock_approval', 'stock_approved', 'stock_rejected', 'br_id'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        return store;
    };

    var salesReturnGridStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listsalesgridstore',
                method: 'post'
            }),
            fields: ['itemname', 'stock_enabled', 'itemid', 'partyId', 'state_id', {name: 'sold_qty', type: 'number'}, {name: 'return_qty', type: 'number'}, {name: 'return_resaleable', type: 'number'}, {name: 'purchase_return_qty', type: 'number'}, {name: 'scrap_qty', type: 'number'}, {name: 'rate', type: 'float'}, {name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'}, {name: 'sgst', type: 'float'}, {name: 'amount', type: 'float'}],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        return store;
    };

    var salesReturnGridViewStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listsalesreturnviewstore',
                method: 'post'
            }),
            fields: ['itemname', 'stock_enabled', 'itemid', 'partyId', 'state_id', {name: 'sold_qty', type: 'number'}, {name: 'return_qty', type: 'number'}, {name: 'return_resaleable', type: 'number'}, {name: 'purchase_return_qty', type: 'number'}, {name: 'scrap_qty', type: 'number'}, {name: 'rate', type: 'float'}, {name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'}, {name: 'sgst', type: 'float'}, {name: 'amount', type: 'float'}],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false

        });
        return store;
    };

    var sales_return_grid_panel = function (hasreturns) {

        if (hasreturns == false)
        {
            var sales_return_grid_store = salesReturnGridViewStore();
        } else
        {
            sales_return_grid_store = salesReturnGridStore();
        }
        var grid = new Ext.grid.EditorGridPanel({
            store: sales_return_grid_store,
            enableColumnMove: false,
            layout: 'fit',
            forceFit: true,
            id: 'sl_sales_returngrid',
            height: 40,
            loadMask: true,
            clicksToEdit: 1,
            cm: salesReturnColModel(hasreturns),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true

            })

        });
        var view = grid.getView();
        view.addListener('rowupdated', function (view, firstRow, rec) {
            setSalesReturnItemTotal();
        });
        return grid;
    };

    var createSalesReturns = function (id, rec, sare_invNo) {
        var hasreturns = null;
        if (rec.get('has_returns') == 'Yes') {
            hasreturns = false;
        } else {
            hasreturns = true;
        }

        var sales_return_grid = sales_return_grid_panel(hasreturns);
        var sales_return_total_grid = salesReturnTotalGrid();
        var sales_return_Window = Ext.getCmp('sales_return_Window');
        if (Ext.isEmpty(sales_return_Window)) {
            var sales_return_Window = new Ext.Window({
                id: 'sl_salesreturn_window',
                title: 'Sales Return' + ' ' + sare_invNo,
                iconCls: 'finascop_add',
                modal: true,
                layout: 'fit',
                width: 900,
                autoheight: true,
                //height: 240,
                shadow: false,
                resizable: false,
                items: [sales_return_grid, sales_return_total_grid, salesReturn_amount_panel(hasreturns)],
                buttons: [{
                        text: 'Save',
                        id: 'salesreturnviewbuton',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function ()
                        {

                            saveSalesReturnsDetails(rec);
                            Ext.MessageBox.alert('Notification', 'Data Saved Successfully');
                            sales_return_Window.close();
                            Ext.getCmp('sl_sales_return_window_grid').getStore().reload();
                            Ext.getCmp('sl_sales_return_window_grid').getView.refresh();
                            Ext.getCmp('sl_sales_return_grid').getStore().reload();
                            Ext.getCmp('sl_sales_return_grid').getView.refresh();
                        }
                    },
                    {
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {

                            sales_return_Window.close();
                        }
                    }]
            });
        }
        if (rec.get('has_returns') == 'Yes')
        {
            Ext.getCmp('salesreturnviewbuton').setVisible(false);
        }
        Ext.getCmp('sl_sales_returngrid').getStore().baseParams = {
            invoice_no: rec.get('InvoiceNo'),
            invoice_date: rec.get('InvoiceDate'),
            totalitems: rec.get('totalitems'),
            has_returns: rec.get('has_returns'),
            InvID: rec.get('InvID')
        };
        Ext.getCmp('sl_sales_returngrid').getStore().load({
            callback: function () {
                salesReturnTotalGridCalculation();
            }
        });
        sales_return_Window.show();
        sales_return_Window.doLayout();
        sales_return_Window.center();
    }

    var salesReturn_amount_panel = function (hasreturns) {

        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'sl_sales_return_amount_form',
            layout: 'column',
            items: [{
                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.6,
                            border: false,
                            labelWidth: 75,
                            style: 'margin-top:5px;',
                            items: [{
                                    xtype: 'hidden',
                                    fieldLabel: 'netamount',
                                    id: 'netamount',
                                    name: 'netamount',
                                    editable: true,
                                    anchor: '90%'

                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.4,
                            border: false,
                            labelWidth: 75,
                            style: 'margin-top:5px;',
                            items: [{
                                    fieldLabel: 'Net Amount',
                                    xtype: 'textfield',
                                    id: 'salesReturn_netAmount',
                                    name: 'salesReturn_netAmount',
                                    anchor: '88%',
                                    readOnly: 'true',
                                    editable: false,
                                    allowBlank: false
                                }]
                        }
                    ]
                }]
        })
        return panel;
    }

    var saveSalesReturnsDetails = function (rec) {
        var sales_returngrid = Ext.getCmp('sl_sales_returngrid');
        var sales_return_total_grid = Ext.getCmp('sl_salesreturnTotal');
        var sales_returngrid_store = sales_returngrid.getStore();
        var sales_returngrid_total_gridstore = sales_return_total_grid.getStore();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var sales_return_gridData = Ext.pluck(sales_returngrid_store.getRange(), 'data');
        var sales_return_total_gridData = Ext.pluck(sales_returngrid_total_gridstore.getRange(), 'data');
        var record = Ext.getCmp('sl_sales_returngrid').getSelectionModel().getSelected();
        var data = {
            invoice_no: rec.get('InvoiceNo'),
            invoice_date: Ext.util.Format.date(rec.get('InvoiceDate'), 'd/m/Y'),
            saen_id: rec.get('InvID'),
            br_id: rec.get('br_id'),
            stock_enabled: record.get('stock_enabled'),
            gross_amount: Ext.getCmp('salesReturn_netAmount').getValue(),
            previous_key: rec.get('previous_key'),
            sales_return_gridData: Ext.encode(sales_return_gridData),
            sales_return_total_gridData: Ext.encode(sales_return_total_gridData)

        };
        var params = {
            action: 'Insert',
            module: 'Sale',
            op: 'saveSalesReturn',
            id: '1',
            extrainfo: 'asd'
        };
        APICall(params, function () {
            return  Application.Finascop_Sale.saveSalesReturnData(rec);
        }, data);
    };

    // sales invoice return -->
    var viewsalesReturn_panel = function () {
        var sales_return_panel_store = salesReturnStore();
        var gridpanel = new Ext.grid.GridPanel({
            store: sales_return_panel_store,
            layout: 'fit',
            id: 'sl_sales_return_window_grid',
            height: 360,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true

            }),
            listeners: {
                viewready: updatePagination
            },
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Sales Invoice No',
                    sortable: true,
                    dataIndex: 'InvoiceNo',
                    tooltip: 'Sales Invoice No',
                    align: 'right'
                },
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'InvoiceDate',
                    tooltip: 'Invoice Date',
                    width: 150,
                    align: 'right'
                }, {
                    header: 'Item Count',
                    sortable: true,
                    dataIndex: 'totalitems',
                    tooltip: 'Item Count',
                    width: 150,
                    align: 'right'
                }, {
                    header: 'Have Returned',
                    sortable: true,
                    dataIndex: 'has_returns',
                    tooltip: 'Have Returned',
                    width: 150,
                    align: 'right'

                },
                {
                    xtype: 'actioncolumn',
                    width: 67,
                    hideable: false,
                    items: [{

                            getClass: function (v, meta, rec) {

                                if (rec.get('has_returns') != 'No') {

                                    return 'finascop_sale_view';
                                } else {
                                    return 'finascop_sale_edit';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                grid.getSelectionModel().selectRow(rowIndex);
                                var rec = sales_return_panel_store.getAt(rowIndex);
                                var id = 0;
                                var sare_invNo = "";
                                var sare_returnedQty = 0;
                                if (rec.get('has_returns') == 'Yes')
                                {

                                    var id = 1;
                                    var t = new Date();
                                    var t_stamp = t.format("YmdHis");
                                    Ext.Ajax.request({
                                        url: modURL + '&op=getSalesReturn_InvoiceNo',
                                        method: 'POST',
                                        params: {
                                            invID: rec.get('InvID'),

                                            apikey: _SESSION.apikey,
                                            tstamp: t_stamp,
                                            item_id: rec.get('item_id')
                                        },
                                        success: function (response) {

                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true) {
                                                sare_invNo = tmp.data.sare_invNo;
                                                createSalesReturns(id, rec, sare_invNo);
                                            } else {
                                                Ext.MessageBox.alert("Notification", tmp.msg);
                                            }

                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        }
                                    });
                                } else {
                                    createSalesReturns(id, rec, sare_invNo);
                                }
                            }
                        }]
                }
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: sales_return_panel_store,
                id: 'sales_return_panel_paging',
                displayInfo: true,
                displayMsg: 'Displaying items {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true

        });

        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'sl_sales_return_form',
            layout: 'column',
            items: [{
                    xtype: 'fieldset',
                    title: 'Search',

                    layout: 'column',
                    style: 'margin-bottom:3px;',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.4,
                            border: false,
                            labelWidth: 40,
                            style: 'margin-top:10px;',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Invoice',
                                    id: 'sl_invoice_num',
                                    name: 'invoice_num',
                                    anchor: '99%',
                                    allowBlank: false,
                                    tabIndex: 1,
                                    listeners:{
                                       afterrender:function(field){
                                       Ext.defer(function(){
                                       field.focus(true,100);
                                       },1);
                                     }
                                   }

                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.3,
                            border: false,
                            labelWidth: 30,
                            style: 'margin-top:10px;',
                            items: [{
                                    fieldLabel: 'From',
                                    xtype: 'datefield',
                                    id: 'sl_item_search_from',
                                    name: 'n[search_from]',
                                    anchor: '88%',
                                    editable: true,
                                    allowBlank: false,
                                    format: 'd/m/Y'

                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.3,
                            border: false,
                            labelWidth: 20,
                            style: 'margin-top:10px;',
                            items: [{
                                    fieldLabel: 'To',
                                    xtype: 'datefield',
                                    id: 'sl_item_search_to',
                                    name: 'n[search_to]',
                                    anchor: '86%',
                                    editable: true,
                                    allowBlank: false,
                                    value: (new Date()),
                                    format: 'd/m/Y'

                                }]
                        },
                        {
                            xtype: 'button',
                            text: 'Search',
                            iconCls: 'finascop_search_btn',
                            id: 'ch_filter_button',
                            style: 'margin-left:10px;margin-top:10px;',
                            handler: function () {
                                var item_search_from = Ext.getCmp('sl_item_search_from').getValue();
                                var item_search_to = Ext.getCmp('sl_item_search_to').getValue();
                                if (item_search_from > item_search_to)
                                {
                                    Ext.MessageBox.alert('Notification', "FromDate cannot be greater than ToDate");
                                } else
                                {
                                    sales_return_panel_store.baseParams = {
                                        invoice_no: Ext.getCmp('sl_invoice_num').getValue(),
                                        item_search_from: Ext.util.Format.date(item_search_from, 'd/m/Y'),
                                        item_search_to: Ext.util.Format.date(item_search_to, 'd/m/Y'),
                                        start: 0,
                                        limit: recs_per_page
                                    };
                                    sales_return_panel_store.load();
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'Reset',
                            iconCls: 'finascop_my-resetpass',
                            style: 'margin-left:15px;margin-top:10px;',
                            handler: function () {

                                Ext.getCmp('sl_invoice_num').reset();
                                Ext.getCmp('sl_item_search_from').reset();
                                sales_return_panel_store.baseParams = {};
                                sales_return_panel_store.removeAll();
                                sales_return_panel_store.fireEvent('load', sales_return_panel_store, [], {});
                            }
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Sales Invoice',
                    columnWidth: 1,
                    style: 'margin-bottom:3px;',
                    id: 'sl_invoice_gridItems',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: gridpanel
                                }]
                        }]
                }]
        });
        return panel;
    };

    //column model for invoiceItemGrid and invoiceTotalGrid
    var salesReturnTotalColumnModel = new Ext.grid.ColumnModel([
        {
            header: 'Item',
            dataIndex: 'itemname',
            width: 80
        }, {
            header: 'Sold Qty',
            id: 'sl_sold_qty',
            dataIndex: 'sold_qty',
            width: 70,
            editable: false,
            align: 'right',
            xtype: 'numbercolumn'

        }, {
            header: 'Return Qty.',
            id: 'return_qty',
            dataIndex: 'return_qty',
            width: 80,
            editable: true,
            align: 'right',
            format: '000',
            xtype: 'numbercolumn'

        },
        {
            header: 'Resaleable Qty.',
            id: 'return_resaleable',
            dataIndex: 'return_resaleable',
            width: 100,
            editable: true,
            align: 'right',
            xtype: 'numbercolumn'
        },
        {
            header: 'Purchase Rtn Qty',
            id: 'purchase_return_qty',
            dataIndex: 'purchase_return_qty',
            width: 100,
            editable: true,
            format: '0.00',
            align: 'right',
            xtype: 'numbercolumn'
        },
        {
            header: 'Scrap Qty',
            id: 'scrap_qty',
            dataIndex: 'scrap_qty',
            width: 70,
            editable: true,
            format: '0.00',
            align: 'right',
            xtype: 'numbercolumn'
        },
        {
            header: 'Rate',
            dataIndex: 'rate',
            width: 50,
            align: 'right',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT
        },
        {
            header: 'IGST',
            dataIndex: 'igst',
            width: 65,
            align: 'right',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT

        },
        {
            header: 'SGST',
            dataIndex: 'sgst',
            width: 65,
            align: 'right',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT
        },
        {
            header: 'CGST',
            dataIndex: 'cgst',
            width: 65,
            align: 'right',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT
        },
        {
            header: 'Amount',
            dataIndex: 'amount',
            width: 65,
            align: 'right',
            xtype: 'finascopcurrency',
            format: FINASCOP_CURRENCY_FORMAT

        }
    ]);

    var calculateItemTax = function (rec)
    {
        var rate = rec.get('rate');
        var totalqty = rec.get('return_qty');
        var amnt = Number(totalqty) * Number(rate);
        Ext.Ajax.request({
            waitMsg: 'Processing',
            url: modURL,
            params: {
                op: 'getSalesReturnItemdetails',
                item_id: rec.get('itemid'),
                partyId: rec.get('partyId'),
                party_state_id: rec.get('state_id')
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', ACTION_FAIL);
            },
            success: function (response, options) {
                var tmp = response.responseText;
                tmp = Ext.decode(tmp);
                if (tmp.success === true) {

                    var igst = Ext.util.Format.round((amnt * (tmp.data.IGST)), 2);
                    var cgst = Ext.util.Format.round((amnt * (tmp.data.CGST)), 2);
                    var sgst = Ext.util.Format.round((amnt * (tmp.data.SGST)), 2);
                    var amount = Ext.util.Format.round((amnt + Number(igst) + Number(cgst) + Number(sgst)), 2);
                    rec.set("igst", igst);
                    rec.set("cgst", cgst);
                    rec.set("sgst", sgst);
                    rec.set("amount", amount);
                    salesReturnTotalGridCalculation();
                } else
                {
                    Ext.MessageBox.alert('Notification', tmp.msg);
                }

            }
        });
    }

    var setSalesReturnItemTotal = function ()
    {
        var grid = Ext.getCmp('sl_sales_returngrid');
        var rec = grid.getSelectionModel().getSelected();
        var sold_qty = rec.get('sold_qty');
        var reasaleable_qty = rec.get('return_resaleable');
        var return_qty = rec.get('return_qty');
        var purchase_return_qty = rec.get('purchase_return_qty');
        var scrap_qty = rec.get('scrap_qty');
        var totalqty = purchase_return_qty + reasaleable_qty + scrap_qty;
        if (totalqty > sold_qty) {
            Ext.MessageBox.alert("Notification", "Return Quantity is greater than sold quantity.", function () {
                grid.getStore().rejectChanges();
            });
            return false;
        } else
        {
            rec.set('return_qty', totalqty);
            calculateItemTax(rec);
            salesReturnTotalGridCalculation();
            return true;
        }
    }

//column model for invoiceItemGrid and invoiceTotalGrid
    var salesReturnColModel = function (hasreturns)
    {
        var salesReturnColumnsModel = new Ext.grid.ColumnModel([
            {
                header: 'Item',
                dataIndex: 'itemname',
                width: 80
            }, {
                header: 'Sold Qty',
                id: 'sl_sold_qty',
                dataIndex: 'sold_qty',
                width: 70,
                editable: false,
                align: 'right',
                xtype: 'numbercolumn',
                format: '000',
                allowNegative: false,
                allowDecimals: false

            }, {
                header: 'Return Qty.',
                id: 'return_qty',
                dataIndex: 'return_qty',
                width: 80,
                editable: false,
                format: '000',
                editor: new Ext.form.NumberField({
                    xtype: 'textfield',
                    id: 'ret_qty',
                    allowBlank: false,
                    allowNegative: false,
                    allowDecimals: false,
                    disabled: false,
                    enableKeyEvents: true,
                    validator: function (value) {
                        return !isNaN(value);
                    },
                    listeners: {

                        keyup: function (numfield, event) {
                            var rec = Ext.getCmp('sl_sales_returngrid').getSelectionModel().getSelected();
                            rec.set('return_qty', numfield.getValue());
                            calculateItemTax(rec);
                            return true;
                        }
                    }


                }),
                align: 'right',
                xtype: 'numbercolumn'
            },
            {
                header: 'Resaleable Qty.',
                id: 'return_resaleable',
                dataIndex: 'return_resaleable',
                width: 100,
                editable: hasreturns,
                format: '000',
                editor: new Ext.form.NumberField({
                    id: 'rtn_resaleable',
                    xtype: 'textfield',
                    allowBlank: false,
                    enableKeyEvents: true,
                    allowNegative: false,
                    allowDecimals: false,
                    validator: function (value) {
                        return !isNaN(value);
                    },
                    listeners: {
                        keyup: function (numfield, event) {
                            var rec = Ext.getCmp('sl_sales_returngrid').getSelectionModel().getSelected();
                            rec.set('return_resaleable', numfield.getValue());
                        }
                    }
                }),
                align: 'right',
                xtype: 'numbercolumn'
            },
            {
                header: 'Purchase Rtn Qty',
                id: 'purchase_return_qty',
                dataIndex: 'purchase_return_qty',
                width: 100,
                editable: hasreturns,
                format: '000',
                editor: new Ext.form.NumberField({
                    xtype: 'textfield',
                    id: 'purchase_rtn_qty',
                    allowBlank: false,
                    enableKeyEvents: true,
                    allowNegative: false,
                    allowDecimals: false,
                    validator: function (value) {
                        return !isNaN(value);
                    },
                    listeners: {
                        keyup: function (numfield, event) {

                            var rec = Ext.getCmp('sl_sales_returngrid').getSelectionModel().getSelected();
                            rec.set('purchase_return_qty', numfield.getValue());
                        }}
                }),
                align: 'right',
                xtype: 'numbercolumn'
            },
            {
                header: 'Scrap Qty',
                id: 'scrap_qty',
                dataIndex: 'scrap_qty',
                width: 70,
                editable: hasreturns,
                format: '000',
                editor: new Ext.form.NumberField({
                    xtype: 'textfield',
                    id: 'scrapqty',
                    allowBlank: false,
                    allowNegative: false,
                    enableKeyEvents: true,
                    allowDecimals: false,
                    validator: function (value) {
                        return !isNaN(value);
                    },
                    listeners: {
                        keyup: function (numfield, event) {
                            var rec = Ext.getCmp('sl_sales_returngrid').getSelectionModel().getSelected();
                            rec.set('scrap_qty', numfield.getValue());
                        }
                    }
                }),
                align: 'right',
                xtype: 'numbercolumn'
            },
            {
                header: 'Rate',
                dataIndex: 'rate',
                width: 50,
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT
            },
            {
                header: 'IGST',
                dataIndex: 'igst',
                width: 65,
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT

            },
            {
                header: 'SGST',
                dataIndex: 'sgst',
                width: 65,
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT
            },
            {
                header: 'CGST',
                dataIndex: 'cgst',
                width: 65,
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT
            },
            {
                header: 'Amount',
                dataIndex: 'amount',
                width: 65,
                align: 'right',
                xtype: 'finascopcurrency',
                format: FINASCOP_CURRENCY_FORMAT

            }
        ]);
        return salesReturnColumnsModel;
    }

    var salesReturnTotalGridCalculation = function (returns)
    {
        var grid = Ext.getCmp('sl_sales_returngrid');
        grid.getStore().commitChanges();
        var store = grid.getStore();
        var igsttotal = Ext.util.Format.round(store.sum('igst'), 2);
        var cgsttotal = Ext.util.Format.round(store.sum('cgst'), 2);
        var sgsttotal = Ext.util.Format.round(store.sum('sgst'), 2);
        var totalamount = Ext.util.Format.round(store.sum('amount'), 2);
        var return_qty = Ext.util.Format.round((store.sum('return_qty')), 2);
        var row_total = new Ext.data.Record.create([
            {name: 'return_qty', type: 'float'},
            {name: 'isgt', type: 'float'},
            {name: 'cgst', type: 'float'},
            {name: 'sgst', type: 'float'},
            {name: 'amount', type: 'float'},
            {name: 'itemId'}

        ]);
        var totalsdata = new row_total({
            return_qty: return_qty,
            itemId: '0',
            igst: igsttotal,
            cgst: cgsttotal,
            sgst: sgsttotal,
            amount: totalamount

        });
        Ext.getCmp('salesReturn_netAmount').setValue(totalamount);
        Ext.getCmp('sl_salesreturnTotal').getStore().removeAll();
        Ext.getCmp('sl_salesreturnTotal').getStore().add(totalsdata);
    }

    var TotalAmount_SalesReturn_Store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            fields: [{name: 'itemID'}, {name: 'return_qty', type: 'number'}, {name: 'return_resaleable', type: 'number'}, {name: 'igst', type: 'float'}, {name: 'cgst', type: 'float'}, {name: 'sgst', type: 'float'}, {name: 'amount', type: 'float'}]
        });
        return store;
    };

    //total amount calculation for sales return-->
    var salesReturnTotalGrid = function () {
        var totalGridStore = TotalAmount_SalesReturn_Store();
        var salesReturn_total_panel = new Ext.grid.GridPanel({
            store: totalGridStore,
            layout: 'fit',
            frame: false,
            border: false,
            title: '',
            height: 17,
            id: 'sl_salesreturnTotal',
            loadMask: true,
            autoScroll: true,
            hideHeaders: true,
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return 'finascop_boldFont td';
                }
            },
            cm: salesReturnTotalColumnModel,
            stripeRows: true
        });
        return salesReturn_total_panel;
    };

    var logInvoiceDetails = function () {

        var invoice_form = Ext.getCmp('sl_invoice_form');
        var grid = Ext.getCmp('sl_itemgrid');
        var grid_store = grid.getStore();
        var gridData = Ext.pluck(grid_store.getRange(), 'data');

        if (!Ext.isEmpty(Ext.getCmp('sl_party').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('sl_inv_date').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('sl_netamount').getValue()))
        {
            var form_data = {
                party: Ext.getCmp('sl_party').getValue(),
                purchcase_order_no: Ext.getCmp('sl_purchcase_order_no').getValue(),
                purchase_order_date: Ext.util.Format.date(Ext.getCmp('sl_purchase_order_date').getRawValue(), 'd/m/Y'),
                ref_no: Ext.getCmp('sl_ref_no').getValue(),
                inv_date: Ext.util.Format.date(Ext.getCmp('sl_inv_date').getValue(), 'd/m/Y'),
                igsttotal: Ext.getCmp('sl_igsttotal').getValue(),
                cgsttotal: Ext.getCmp('sl_cgsttotal').getValue(),
                sgsttotal: Ext.getCmp('sl_sgsttotal').getValue(),
                grandtotal: Ext.getCmp('sl_grandtotal').getValue(),
                discount: Ext.getCmp('sl_discount').getValue(),
                netamount: Ext.getCmp('sl_netamount').getValue(),
                terms: Ext.getCmp('sl_terms').getValue(),
                paymentmode: Ext.getCmp('sl_paymentmode').getValue(),
                totalquantity: Ext.getCmp('sl_totalquantity').getValue(),
                tax: Ext.getCmp('sl_tax').getValue()
            };
            var params = {
                action: 'Insert',
                module: 'Sale',
                op: 'saveInvoice',
                id: '1',
                extrainfo: 'asd'
            };
            APICall(params, Application.Finascop_Sale.saveInvoice, form_data);
        } else {
            Ext.MessageBox.alert("Notification", "PLease fill all the mandatory fields");
        }
    };

    return{initSales: function () {
            var panelId = 'sales_master_panel';
            var sales_main = Ext.getCmp(panelId);
            if (Ext.isEmpty(sales_main)) {
                sales_main = salesMasterPanel(panelId);
            }
            Ext.getCmp('sl_sales_invoice_grid').getStore().removeAll();
            Ext.getCmp('sl_sales_invoice_grid').getStore().load({
                params: {
                    start: 0,
                    limit: recs_per_page
                }
            });
            getBranchStateId();
            Application.UI.addTab(sales_main);
            sales_main.doLayout();
            return sales_main;
        },
        inittermsmaster: function () {
            var termgrid = Ext.getCmp('sl_term_grid');
            if (!termgrid) {
                termgrid = createTermGrid();
            }
            loadTermsData();
            Application.UI.addTab(termgrid);
            termgrid.doLayout();
        },
        initSalesReturns: function () {
            var salesreturnsgrid = Ext.getCmp('sl_sales_return_grid');
            if (!salesreturnsgrid) {
                salesreturnsgrid = createSalesReturnGrid();
            }
            Application.UI.addTab(salesreturnsgrid);
            salesreturnsgrid.doLayout();
        },
        saveEditedData: function () {
            var invoiceeditform = Ext.getCmp('sl_invoiceeditform');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=saveEditedData',
                method: 'POST',
                params: {
                    saen_Id: Ext.getCmp('sl_saen_Id').getValue(),
                    party: Ext.getCmp('sl_party_name').getRawValue().toUpperCase(),
                    purchase_orderno: Ext.getCmp('sl_purchase_orderno').getValue(),
                    purchase_orderdate: Ext.util.Format.date(Ext.getCmp('sl_purchase_orderdate').getValue(), 'd/m/Y'),
                    invoice_date: Ext.util.Format.date(Ext.getCmp('sl_invoice_date').getValue(), 'd/m/Y'),
                    referenceno: Ext.getCmp('sl_referenceno').getValue(),
                    invoiceno: Ext.getCmp('sl_invoiceno').getValue(),
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp

                },
                success: function (response) {


                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        Ext.getCmp('sl_invoiceeditform').setVisible(false);
                        Ext.getCmp('sl_details_view_panel_invoice').setVisible(true);
                        Ext.getCmp('sl_sales_invoice_grid').getStore().reload();
                        var visualsDescPanel = Ext.getCmp('sl_details_view_panel_invoice');
                        visualsDescPanel.update(tmp.data);
                    } else {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                    }

                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        },
        saveSalesReturnData: function (rec) {

            var sales_returngrid = Ext.getCmp('sl_sales_returngrid');
            var sales_return_total_grid = Ext.getCmp('sl_salesreturnTotal');
            var sales_returngrid_store = sales_returngrid.getStore();
            var sales_returngrid_total_gridstore = sales_return_total_grid.getStore();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var sales_return_gridData = Ext.pluck(sales_returngrid_store.getRange(), 'data');
            var sales_return_total_gridData = Ext.pluck(sales_returngrid_total_gridstore.getRange(), 'data');
            var record = Ext.getCmp('sl_sales_returngrid').getSelectionModel().getSelected();
            Ext.Ajax.request({
                url: modURL + '&op=saveSalesReturn',
                method: 'POST',
                params: {

                    invoice_no: rec.get('InvoiceNo'),
                    invoice_date: rec.get('InvoiceDate'),
                    saen_id: rec.get('InvID'),
                    previous_key: rec.get('previous_key'),
                    br_id: rec.get('br_id'),
                    stock_enabled: record.get('stock_enabled'),
                    gross_amount: Ext.getCmp('salesReturn_netAmount').getValue(),
                    sales_return_gridData: Ext.encode(sales_return_gridData),
                    sales_return_total_gridData: Ext.encode(sales_return_total_gridData),
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp

                },
                success: function (response) {


                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        Ext.getCmp('sl_salesreturn_window').close();
                        Ext.getCmp('sl_sales_return_window_grid').getStore().reload();
                        Ext.getCmp('sl_sales_return_window_grid').getView.refresh();
                    } else {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        Ext.getCmp('sl_salesreturn_window').close();
                    }

                },

                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        },
        saveInvoice: function () {
            var invoice_form = Ext.getCmp('sl_invoice_form');
            var grid = Ext.getCmp('sl_itemgrid');
            var grid_store = grid.getStore();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var gridData = Ext.pluck(grid_store.getRange(), 'data');
            invoice_form.getForm().submit({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                params: {
                    op: 'saveInvoice',
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    itemData: Ext.encode(gridData),
                    br_id: br_id,
                    party_id_no: Ext.getCmp('sl_party').getValue(),
                    party_name: Ext.getCmp('sl_party').getRawValue().toUpperCase()

                },
                success: function (form, action) {

                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        excludeItemIds = [];
                        Ext.MessageBox.alert("Success", "Invoice details saved successfully", function (btn) {

                            Ext.getCmp('sl_sales_invoice_grid').getStore().reload();
                            Ext.getCmp('sl_invoice_window').close();
                        });
                    }
                },
                failure: function (invoice_form, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: (obj.error) ? obj.error : obj.errors.reason, buttons: Ext.MessageBox.OK,
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
        }

    };
}();

