
Application.JobConfirmation = function () {


    var recs_per_page = 12;
    var selectedrecord;
    var modURL = '?module=qugeo_job_confirmation';
    /*Branch combo store*/
    var branchstore = new Ext.data.JsonStore({
        url: modURL + '&op=getbranchStore',
        method: 'post',
        fields: ['br_Name', 'br_ID'],
        totalProperty: 'totalCount',
        root: 'data',
        autoLoad: false
    });
    /*Vehicle Combo Store*/

    var vehicleStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['v_id', 'v_no'],
            url: modURL + '&op=getVehicleStore',
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: false,
            listeners: {
                beforeload: function () {
                    if (!Ext.isEmpty(Ext.getCmp('date_tbar').getValue())
                            && !Ext.isEmpty(Ext.getCmp('job_confirm_branch').getValue())) {
                        var date = Ext.getCmp('date_tbar').getValue().format('Y-m-d');
                        var branch = Ext.getCmp('job_confirm_branch').getValue();
                        var vehicle = Ext.getCmp('job_confirm_vehicle');

                        vehicle.getStore().baseParams.date = date;
                        vehicle.getStore().baseParams.br_id = branch;
                    }
                },
                load: function () {
                    Application.JobConfirmation.SelectionChanged = false;
                }
            }
        });
        return store;
    };
    /*Grid store*/
    var gridStore = function () {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=listJobGrid',
            method: 'post',
            fields: ['orderid', 'date', 'quor_RefNo', 'From', 'To',
                {name: 'NetAmt', type: 'float'}, 'Paymode', 'HasReCalculatedCharges',
                'ReCalculatedCharges', 'ReCalculcationPaymentType', 'IsPickup', 'status', 'IsEditable', 'v_no',
                'BkLastEditTime', 'quor_id', 'bk_brk_br_id', 'status_time', {name: 'ex_pay', type: 'float'},
                {name: 'total', type: 'float'}, {name: 'ex_amt', type: 'float'}, {name: 'quor_AmountCollectible', type: 'float'}, 'closing_status', 'dls_id', 'quor_ItemReturnUpdate', 'quor_ItemReturnUpdate', 'quor_ItemReturned'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: false,
            sortInfo: {
                field: 'quor_RefNo',
                direction: "ASC"
            },
            listeners: {
                load: function (str) {
                    if (str.getCount() > 0) {
                        if (Application.JobConfirmation.DateTime == '')
                            Application.JobConfirmation.DateTime = this.reader.jsonData.datetime;
                    }
                }
            }
        });
        return store;
    };


    /*Pickup Grid*/
    var pickupGrid = function () {
        var store = gridStore();
        var sm = new Ext.grid.RowSelectionModel({
            singleSelect: true
        });
        var grid = new Ext.grid.GridPanel({
            store: store,
            iconCls: 'job_confirm',
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Job Confirmation',
            bbar: ['->', {
                    xtype: 'numberfield',
                    format: FINASCOP_CURRENCY_FORMAT,
                    fieldLabel: 'Total',
                    id: 'total_payable',
                    name: 'total_payable',
                    allowDecimals: true,
                    tabIndex: 514,
                    readOnly: true,
                    anchor: '99%',
                    listeners: {
                        focus: function (txtbx) {
                            var itemStore = Ext.getCmp('pickup_grid').getStore();
                            var quor_AmountCollectible = itemStore.sum('quor_AmountCollectible');
                            Ext.getCmp('total_payable').setValue(quor_AmountCollectible);
                        }
                    }
                }, {
                    xtype: 'spacer',
                    width: 150
                }],
            viewConfig: {
                getRowClass: function (record, index, row) {
                    /*
                     editable false -> white
                     */
                    if (record.get('IsEditable') == true)
                        return 'IsPickup_' + record.get('IsPickup');
                    else
                        return 'default_row';
                },
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: sm,
            stripeRows: true,
            id: 'pickup_grid',
            autoExpandColumn: 'exp_id_type',
            columns: [
                {
                    header: 'Booking No',
                    dataIndex: 'quor_RefNo',
                    width: 40,
                    sortable: true
                }, {
                    header: 'Date',
                    dataIndex: 'date',
                    width: 50,
                    sortable: true
                }, {
                    header: 'Vehicle No.',
                    dataIndex: 'v_no',
                    width: 100,
                    sortable: true
                }, {
                    header: 'Source',
                    dataIndex: 'From',
                    width: 100,
                    sortable: true
                }, {
                    header: 'Destination',
                    dataIndex: 'To',
                    width: 100,
                    sortable: true
                }, {
                    header: 'Status',
                    dataIndex: 'closing_status',
                    width: 100,
                    sortable: true
                },
                {
                    header: 'Status Time',
                    dataIndex: 'status_time',
                    width: 100,
                    sortable: true
                }, {
                    header: 'Current Status',
                    dataIndex: 'status',
                    width: 100,
                    sortable: true
                },
                {
                    header: 'Pay mode',
                    dataIndex: 'Paymode',
                    width: 80,
                    sortable: true
                }, {
                    header: 'Total',
                    dataIndex: 'quor_AmountCollectible',
                    align: 'right',
                    width: 80,
                    sortable: true
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    sortable: false,
                    items: [
                        {
                            icon: 'resources/images/submenuicons/application_view_detail.png',
                            tooltip: 'Verify',
                            handler: function (search_grid, rowIndex, colIndex) {
                                selectedrecord = grid.store.getAt(rowIndex);
                                Application.JobConfirmation.verify_btn_window(selectedrecord);
                            }
                        }, {
                            sortable: false,
                            getClass: function (v, meta, rec) {
                                // return 'my-icon109';
                                return 'my-icon10';
                            },
                            tooltip: 'View Jobs and Route',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                Application.Qugeo.JobsRoute(record.get('orderid'), 'Order', 1);
                            }
                        }
                    ]
                }],
            tbar: [{
                    html: '&nbsp; Branch: &nbsp;'
                }, {
                    xtype: 'combo',
                    mode: 'remote',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: branchstore,
                    id: 'job_confirm_branch',
                    triggerAction: 'all',
                    displayField: 'br_Name',
                    allowBlank: false,
                    valueField: 'br_ID',
                    hiddenName: 'br_id',
                    name: 'job_confirm_branch',
                    minChars: 1,
                    listeners: {
                        select: function () {
                            var vehicle = Ext.getCmp('job_confirm_vehicle');
                            vehicle.reset();
                            vehicle.getStore().removeAll();
                            Ext.getCmp('date_tbar').reset();
                            Ext.getCmp('pickup_grid').getStore().removeAll();

                            vehicle.getStore().baseParams.br_id = Ext.getCmp('job_confirm_branch').getValue();
                        },
                        change: function (cmb, new_val, old_val) {

                            if (new_val != old_val) {
                                Application.JobConfirmation.SelectionChanged = true;
                            } else {
                                Application.JobConfirmation.SelectionChanged = false;
                            }
                        }
                    }
                },
                {
                    html: '&nbsp; Date: &nbsp;'
                }, {
                    xtype: 'datefield',
                    id: 'date_tbar',
                    allowBlank: false,
                    editable: true,
                    format: 'Y/m/d',
                    value: new Date(),
                    listeners: {
                        select: function () {
                            var date = Ext.getCmp('date_tbar').getValue().format('Y-m-d');
                            var branch = Ext.getCmp('job_confirm_branch').getValue();

                            var vehicle = Ext.getCmp('job_confirm_vehicle');
                            vehicle.reset();
                            vehicle.getStore().removeAll();

                            Ext.getCmp('pickup_grid').getStore().removeAll();

                            vehicle.getStore().baseParams.date = date;
                            vehicle.getStore().baseParams.br_id = branch;
                        },
                        change: function (dt, new_val, old_val) {
                            if (new_val != old_val) {
                                Application.JobConfirmation.SelectionChanged = true;
                            } else {
                                Application.JobConfirmation.SelectionChanged = false;
                            }
                        }
                    }
                },
                {
                    html: '&nbsp; Vehicle: &nbsp;'
                },
                {
                    xtype: 'lovcombo',
                    mode: 'local',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: vehicleStore(),
                    id: 'job_confirm_vehicle',
                    triggerAction: 'all',
                    displayField: 'v_no',
                    allowBlank: false,
                    valueField: 'v_id',
                    hiddenName: 'vehicle_id',
                    name: 'job_confirm_vehicle',
                    minChars: 1,
                    listeners: {
                        beforequery: function (queryEvent) {
                            if (Ext.isEmpty(Ext.getCmp('date_tbar').getValue())
                                    || Ext.isEmpty(Ext.getCmp('job_confirm_branch').getValue()))
                            {
                                Ext.MessageBox.alert("Notification", "Please select branch and date");
                                return false;
                            }
                            if (Application.JobConfirmation.SelectionChanged == true)
                                this.getStore().load();
                        },
                        select: function () {
                            Ext.getCmp('pickup_grid').getStore().removeAll();
                        }
                    }
                },
                {
                    html: '&nbsp; &nbsp;'
                }, {
                    style: 'margin-top:0px;',
                    xtype: 'checkbox',
                    boxLabel: 'Pickup',
                    id: 'chk_pickup',
                    name: 'chk_pickup'
                }, {
                    html: '&nbsp; &nbsp;'
                }, {
                    style: 'margin-top:0px;',
                    xtype: 'checkbox',
                    boxLabel: 'Delivery',
                    id: 'chk_delivery',
                    name: 'chk_del'
                },
                {
                    xtype: 'button',
                    text: 'Show',
                    iconCls: 'show',
                    style: "padding-left: 10px;",
                    id: 'job_confirm_show',
                    handler: function () {
                        var chk1 = Ext.getCmp('chk_pickup').getValue();
                        var chk2 = Ext.getCmp('chk_delivery').getValue();
                        if (chk1 == false && chk2 == false)
                        {
                            Ext.MessageBox.alert("Notification", "Select Pickup/Delivery");
                            return;
                        }
                        if (Ext.getCmp('job_confirm_branch').getValue() != '' && Ext.getCmp('date_tbar').getValue() != '') {
                            var loadingMask = new Ext.LoadMask(Ext.getCmp('pickup_grid').el, {msg: "Please wait..."});
                            loadingMask.show();

                            Ext.getCmp('pickup_grid').getStore().load({
                                params: {
                                    chk_box1: Ext.getCmp('chk_pickup').getValue(),
                                    chk_box2: Ext.getCmp('chk_delivery').getValue(),
                                    vehicle_id: Ext.getCmp('job_confirm_vehicle').getValue(),
                                    date: Ext.getCmp('date_tbar').getValue().format('Y-m-d'),
                                    br_id: Ext.getCmp('job_confirm_branch').getValue()
                                },
                                callback: function () {
                                    loadingMask.hide();
                                    Ext.getCmp('total_payable').focus(false, 100);
                                }
                            });
                        }
                        else
                            Ext.MessageBox.alert("Notification", "Select select Branch and Date.");
                    }
                }
            ],
            listeners: {
                afterrender: function () {
                    if (_SESSION.typId == '3' || _SESSION.typId == '4')
                    {
                        Ext.getCmp('job_confirm_branch').store.load({
                            callback: function () {

                                Ext.getCmp('job_confirm_branch').setValue(_SESSION.typdetsid);

                            }
                        });
                    }
                }
            }

        });

        return grid;
    };

    var pickup_verify_btn_form = function () {

        var form = new Ext.FormPanel({
            id: 'verify_form_id',
            frame: true,
            border: false,
            labelAlign: 'left',
            autoHeight: true,
            layout: 'column',
            labelWidth: 105,
            items: [
                {
                    layout: 'form',
                    columnWidth: 0.50,
                    defaults: {
                        submitValue: true,
                        anchor: '98%'},
                    items: [{
                            xtype: 'hidden',
                            id: 'pickup_del_time',
                            name: 'pickup_del_time'
                        }, {
                            xtype: 'hidden',
                            id: 'BkLastEditTime',
                            name: 'BkLastEditTime'
                        }, {
                            xtype: 'hidden',
                            id: 'status',
                            name: 'status'
                        }, {
                            xtype: 'hidden',
                            id: 'bk_brk_br_id',
                            name: 'bk_brk_br_id'
                        }, {
                            xtype: 'hidden',
                            id: 'dls_id',
                            name: 'dls_id'
                        }, {
                            xtype: 'hidden',
                            id: 'IsPickup',
                            name: 'IsPickup'
                        }, {
                            xtype: 'hidden',
                            id: 'orderid',
                            name: 'orderid'
                        }, {
                            xtype: 'hidden',
                            id: 'quor_id',
                            name: 'quor_id'
                        }, {
                            fieldLabel: 'Booking No',
                            id: 'quor_RefNo',
                            name: 'quor_RefNo',
                            xtype: 'textfield',
                            readOnly: true
                        },
                        {xtype: 'textfield',
                            readOnly: true,
                            fieldLabel: 'Vehicle No',
                            id: 'v_no',
                            name: 'v_no'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            fieldLabel: 'Source',
                            id: 'From',
                            name: 'From'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            hidden: true,
                            fieldLabel: 'Excess Amount',
                            id: 'ex_amt',
                            name: 'ex_amt'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            fieldLabel: 'Status',
                            id: 'status',
                            name: 'status'
                        }, {
                            xtype: 'datefield',
                            format: 'Y-m-d H:i:s',
                            readOnly: true,
                            fieldLabel: 'Status Time',
                            id: 'status_time',
                            name: 'status_time'
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.50,
                    defaults: {
                        anchor: '98%',
                        submitValue: true
                    },
                    items: [{
                            xtype: 'hidden',
                            id: 'reason_id',
                            name: 'reason_id'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            fieldLabel: 'Date',
                            id: 'date',
                            name: 'date'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            fieldLabel: 'Destination',
                            id: 'To',
                            name: 'To'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            fieldLabel: 'Paymode',
                            id: 'Paymode',
                            name: 'Paymode'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            fieldLabel: 'Excess Payment',
                            id: 'ex_pay',
                            hidden: true,
                            name: 'ex_pay'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            hidden: true,
                            fieldLabel: 'Net Amount',
                            id: 'NetAmt',
                            name: 'NetAmt'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            hidden: true,
                            fieldLabel: 'Amount Collectible',
                            id: 'quor_AmountCollectible',
                            name: 'quor_AmountCollectible'
                        }, {xtype: 'textfield',
                            readOnly: true,
                            fieldStyle: 'text-align: right;',
                            fieldLabel: 'Total',
                            id: 'quor_AmountCollectible',
                            name: 'quor_AmountCollectible'
                        }, {
                            xtype: 'datefield',
                            format: 'Y-m-d H:i:s',
                            increment: 5,
                            fieldLabel: 'Confirmation Time',
                            id: 'confirmation_time',
                            name: 'confirmation_time',
                            anchor: '98%',
                            allowBlank: false
                        }]
                }

            ]
        });
        return form;
    };
    /*Failed reason*/
    /*Combo store for reason*/
    var reasonStore = function (type) {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=getReasons',
            method: 'post',
            baseParams: {
                IsPickup: type
            },
            fields: ['reason_txt', 'reason_id'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: false
        });
        return store;
    };

    var failed_reason_form = function (type) {

        var form = new Ext.FormPanel({
            id: 'failed_reason_form_id',
            frame: true,
            border: false,
            labelAlign: 'left',
            autoHeight: true,
            labelWidth: 50,
            items: [
                {
                    fieldLabel: 'Reason',
                    xtype: 'combo',
                    mode: 'remote',
                    typeAhead: true,
                    editable: true,
                    emptyText: 'Select',
                    anchor: '100%',
                    store: reasonStore(type),
                    id: 'failed_reason_id',
                    triggerAction: 'all',
                    displayField: 'reason_txt',
                    allowBlank: false,
                    valueField: 'reason_id',
                    hiddenName: 'reason_id',
                    name: 'failed_reason_id'
                }
            ]
        });
        return form;
    };
    var failed_reason_window = function () {
        var type = arguments[0];

        var failed_reason_window = Ext.getCmp('failed_reason_window');
        if (Ext.isEmpty(failed_reason_window)) {
            var failed_reason_window = new Ext.Window({
                id: 'failed_reason_window',
                title: '',
                iconCls: '',
                modal: true,
                layout: 'fit',
                width: 300,
                height: 100,
                shadow: false,
                resizable: false,
                items: failed_reason_form(type),
                buttons: [
                    {
                        text: 'Ok',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        handler: function () {
                            Ext.getCmp('reason_id').setValue(Ext.getCmp('failed_reason_id').getValue());
                            failed_reason_window.close();
                            save('Failed');
                        }

                    }]
            });
        }
        failed_reason_window.show();
        failed_reason_window.doLayout();
        failed_reason_window.center();

    };
    var save = function () {
        Ext.Msg.show({
            title: 'Saving!',
            msg: '',
            progressText: 'Please Wait...',
            width: 300,
            progress: true,
            closable: false,
            wait: true
        });
        var button_type = arguments[0];
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        Ext.getCmp('verify_form_id').getForm().submit({
            url: modURL + '&op=save',
            waitTitle: 'Please Wait..',
            waitMsg: 'Saving data...',
            params: {
                apikey: _SESSION.apikey,
                tstamp: t_stamp,
                action: button_type
            },
            success: function (form, action) {
                var result = Ext.decode(action.response.responseText);
                if (result.success !== undefined && result.success === true) {
                    Ext.Msg.hide();
                    Ext.MessageBox.alert('Notification', result.msg, function (btn) {
                        if (btn == 'ok') {
                            Ext.getCmp('pickup_grid').getStore().remove(selectedrecord)
                            Ext.getCmp('verify_btn_window').close();
                        }
                    });
                }
            },
            failure: function () {
                Ext.Msg.hide();
                Ext.MessageBox.alert('Error', 'Error occured while uploading data.');
            }
        });
    };

    var getPickupDeliveryTime = function (type, date_only) {
        var confirmation_time = Ext.getCmp('confirmation_time').getValue().format("Y-m-d\\TH:i:sP");

        var time_win = new Ext.Window({
            id: 'time_win',
            title: type + ' Time',
            modal: true,
            layout: 'fit',
            width: 300,
            autoHeight: true,
            shadow: false,
            resizable: false,
            items: new Ext.FormPanel({
                id: 'time_form',
                frame: true,
                border: false,
                autoHeight: true,
                layout: 'fit',
                items: {
                    xtype: 'datefield',
                    format: 'Y-m-d H:i:s',
                    id: 'time_input',
                    name: 'time_input',
                    anchor: '98%',
                    allowBlank: false,
                    emptyText: 'Please enter ' + type + ' time'
                },
                listeners: {
                    afterrender: function () {
                        Ext.getCmp('time_input').setValue(date_only);
                    }
                }
            }),
            buttons: [{
                    text: 'Ok',
                    iconCls: 'icon_approve',
                    handler: function () {
                        if (Ext.getCmp('time_form').getForm().isValid()) {
                            var popup_time = Ext.getCmp('time_input').getValue();
                            var time = popup_time.format("Y-m-d\\TH:i:sP");

                            if (confirmation_time >= time) {

                                if (popup_time.format("H:i:s") == '00:00:00') {
                                    Ext.MessageBox.alert("Notification", 'Please enter a valid time');
                                } else {
                                    Ext.getCmp('pickup_del_time').setValue(popup_time.format("Y-m-d H:i:s"));
                                    time_win.close();
                                    Application.JobConfirmation.DateTime = Ext.getCmp('confirmation_time').getValue().format("Y-m-d H:i:s");

                                    save(type);
                                }
                            }
                            else {
                                Ext.MessageBox.alert("Notification", 'Invalid date and time selection for confirmation time/' + type + ' time');
                            }
                        }
                        else {
                            Ext.MessageBox.alert("Notification", 'Please enter valid ' + type + ' time');
                        }
                    }
                }, {
                    text: 'Close', icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        time_win.close();
                    }
                }]
        });


        time_win.show();
        time_win.doLayout();
        time_win.center();
    };

    var updateStatus = function (data, type, action) {
        var status_time = Ext.getCmp('status_time').getValue().format("Y-m-d\\TH:i:sP");

        var cnf = Ext.getCmp('confirmation_time').getValue();

        var confirmation_time = cnf.format("Y-m-d\\TH:i:sP");
        var date_only = cnf.format("Y-m-d");
        if (type == 1) {
            if (data.dls_id == 28)
                save(action);
            else {
                if (status_time <= confirmation_time)
                    getPickupDeliveryTime(action, date_only);
                else {
                    Ext.MessageBox.alert("Notification", "Invalid date selection.");
                }
            }
        } else if (type == 2)
            if (data.dls_id == 38)
                save(action);
            else {
                if (status_time <= confirmation_time)
                    getPickupDeliveryTime(action, date_only);
                else {
                    Ext.MessageBox.alert("Notification", "Invalid date selection.");
                }
            }
    };
    var _barcodeDetailsStore = function (quor_id) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listbarcodesinQreturn',
                method: 'post'
            }),
            fields: ['stiid_barcode', 'stiid_createdon', 'stiid_updatedon', 'stiid_statusStat'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.quor_id = quor_id;
                }
            }
        });
        return _Store;
    };
    var _barcodeDetailsGrid = function (quor_id) {

        var _dispatchgridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: _barcodeDetailsStore(quor_id),
            iconCls: 'money',
            width: 600,
            height: 500,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforbarcodeDetails',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Barcode',
                    dataIndex: 'stiid_barcode',
                    sortable: true,
                    tooltip: 'Barcode',
                    hideable: false,
                    width: 100
                }
            ],
            viewConfig: {
                forceFit: true,
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforbarcodeDetails').getStore().load({
                        params: {
                            quor_id: quor_id
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
    return {
        job_conform: function () {
            Application.JobConfirmation.SelectionChanged = false;
            Application.JobConfirmation.DateTime = '';
            var main_panel_id = 'pickup_grid';
            var main_panel = Ext.getCmp(main_panel_id);
            if (Ext.isEmpty(main_panel)) {
                main_panel = pickupGrid();
                Application.UI.addTab(main_panel);
                main_panel.doLayout();
            } else {
                Application.UI.addTab(main_panel);
            }

        },
        verify_btn_window: function () {

            var record = arguments[0];
            console.log('record', record);
            var data = record.data;
            var type, hidfrm, ItemReturned, retCount;
            ItemReturned = JSON.parse(data.quor_ItemReturned);
            retCount = ItemReturned.length;
            console.log(data.quor_ItemReturned);
            console.log('quor_ItemReturned', ItemReturned);
            console.log('retCount', retCount);
            console.log('IsPickup', data.IsPickup);
            if (data.IsPickup != 1 && retCount > 0) {
                hidfrm = false;
            } else {
                hidfrm = true;
            }
            if (data.IsPickup == 1)
            {
                type = 1;
            } else
            {
                type = 2;
            }

            var verify_btn_window = Ext.getCmp('verify_btn_window');
            if (Ext.isEmpty(verify_btn_window)) {
                var verify_btn_window = new Ext.Window({
                    id: 'verify_btn_window',
                    title: 'Details',
                    iconCls: '',
                    modal: true,
                    layout: 'fit',
                    width: 650,
                    autoHeight: true,
                    shadow: false,
                    resizable: false,
                    items: pickup_verify_btn_form(),
                    buttons: [{
                            text: 'View Return',
                            id: 'view_return',
                            hidden: hidfrm,
                            iconCls: 'price_list',
                            handler: function () {
                                Application.JobConfirmation.viewBarcodes(data.quor_id);
                            }
                        }, {
                            text: 'Picked up',
                            id: 'picked_up_btn',
                            iconCls: 'pickup',
                            handler: function () {
                                if (Ext.getCmp('verify_form_id').getForm().isValid()) {
                                    Ext.Msg.show({
                                        title: 'Confirm',
                                        msg: "Do you want to mark it as Picked up?",
                                        buttons: Ext.MessageBox.OKCANCEL,
                                        fn: function (btn) {
                                            if (btn == 'ok') {
                                                var action = 'PickUp';
                                                updateStatus(data, type, action);
                                            }
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.alert("Notification", "Please enter valid data.");
                                }
                            }
                        }, {
                            text: 'Delivered',
                            id: 'delivered_btn',
                            iconCls: 'delivered',
                            handler: function () {
                                if (Ext.getCmp('verify_form_id').getForm().isValid()) {
                                    Ext.Msg.show({
                                        title: 'Confirm',
                                        msg: "Do you want to mark it as Delivered?",
                                        buttons: Ext.MessageBox.OKCANCEL,
                                        fn: function (btn) {
                                            if (btn == 'ok') {
                                                var action = 'Delivery';
                                                updateStatus(data, type, action);
                                            }
                                        }
                                    });

                                } else {
                                    Ext.MessageBox.alert("Notification", "Please enter valid data.");
                                }
                            }
                        }, {
                            text: 'Failed',
                            id: 'failed_btn',
                            iconCls: 'icon-del-table',
                            handler: function () {
                                if (Ext.getCmp('verify_form_id').getForm().isValid()) {
                                    if (retCount > 0) {
                                        Ext.Msg.show({
                                            title: 'Confirm',
                                            msg: "Do you want to go back and view the returned items?",
                                            buttons: Ext.MessageBox.YESNO,
                                            fn: function (btn) {
                                                if (btn == 'no') {
                                                    Ext.Msg.show({
                                                        title: 'Confirm',
                                                        msg: "Do you want to mark it as Failed?",
                                                        buttons: Ext.MessageBox.OKCANCEL,
                                                        fn: function (btn) {
                                                            if (btn == 'ok') {
                                                                // failed_reason_window(type);
                                                                Ext.getCmp('reason_id').setValue(0);
                                                                save('Failed');
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    } else {
                                        Ext.Msg.show({
                                            title: 'Confirm',
                                            msg: "Do you want to mark it as Failed?",
                                            buttons: Ext.MessageBox.OKCANCEL,
                                            fn: function (btn) {
                                                if (btn == 'ok') {
                                                    //failed_reason_window(type);
                                                    Ext.getCmp('reason_id').setValue(0);
                                                    save('Failed');
                                                }
                                            }
                                        });
                                    }

                                } else {
                                    Ext.MessageBox.alert("Notification", "Please enter valid data.");
                                }
                            }
                        }, {
                            text: 'Manual Shedule',
                            id: 'manual_schedule_btn',
                            iconCls: 'scheduled',
                            handler: function () {
                                if (Ext.getCmp('verify_form_id').getForm().isValid()) {
                                    Ext.MessageBox.confirm('Confirm', 'Are you sure to push manual sheduling?', function (btn, text) {
                                        if (btn == 'yes') {
                                            save('manualschedule');
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.alert("Notification", "Please enter valid data.");
                                }
                            }
                        }, {
                            text: 'Close',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                verify_btn_window.close();
                            }
                        }],
                    listeners: {
                        afterrender: function () {

                            if (data.IsEditable == false) {
                                Ext.getCmp('picked_up_btn').disable();
                                Ext.getCmp('delivered_btn').disable();
                                Ext.getCmp('failed_btn').disable();
                                Ext.getCmp('manual_schedule_btn').disable();
                            }

                            Ext.getCmp('verify_form_id').getForm().loadRecord(record);

                            setTimeout(function () {
                                if (Application.JobConfirmation.DateTime != '' && Ext.isEmpty(Ext.getCmp('confirmation_time').getValue()))
                                    Ext.getCmp('confirmation_time').setValue(Application.JobConfirmation.DateTime);
                            }, 50);
                        }
                    }
                });
            }

            verify_btn_window.show();
            verify_btn_window.doLayout();
            verify_btn_window.center();

            if (data.IsPickup == 1)
            {

            } else
            {
                Ext.getCmp('picked_up_btn').hide();
            }

        }, viewBarcodes: function (quor_id) {
            var _addnewItemsWindow = new Ext.Window({
                title: 'Barcode Details',
                iconCls: 'dispatch',
                layout: 'fit',
                height: 500,
                width: 700,
                resizable: false,
                draggable: true,
                closable: true,
                bodyStyle: {"background-color": "white"},
                items: [_barcodeDetailsGrid(quor_id)]
            });
            _addnewItemsWindow.doLayout();
            _addnewItemsWindow.show();
            _addnewItemsWindow.center();
        }
    };
}
();
