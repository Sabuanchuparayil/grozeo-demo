/* global Application, Ext, BETWEEN_DATES_TYPE_A, _SESSION */

Application.Finascop_DataFinalised = function () {
    var isDblClick;
    var recs_per_page = 12;
    var winsize = Ext.getBody().getViewSize();
    var dateRange = parseInt(BETWEEN_DATES_TYPE_A);
    var modURL = '?module=finascop_data_finalised';

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var auditingStore = function () {
        return new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listAudit',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: '',
                root: 'data'
            },
            /* ['acet_NO', 'acet_Date', 'acet_Amount', 'acet_InWords', 'comp_id', 'accounts_ip', 'particular_ip',
             'acet_TypeId', 'company', 'acet_EntryBy', 'acet_ImageURL', 'entry_type', 'type_name', 'updated_on']),*/

            [{
                    name: 'acet_NO'
                },
                {
                    name: 'acet_DocNO'
                },
                {
                    name: 'acet_Date',
                    type: 'date',
                    dateFormat: 'd-m-y'
                }, {
                    name: 'acet_Amount'
                }, {
                    name: 'acet_InWords'
                }, {
                    name: 'comp_id'
                }, {
                    name: 'accounts_ip'
                }, {
                    name: 'particular_ip'
                }, {
                    name: 'acet_TypeId'
                }, {
                    name: 'company'
                }, {
                    name: 'branch'
                }, {
                    name: 'acet_EntryBy'
                }, {
                    name: 'acet_ImageURL'
                }, {
                    name: 'entry_type'
                }, {
                    name: 'type_name'
                }, {
                    name: 'updated_on'
                }, {
                    name: 'narration'
                }, {
                    name: 'acet_Status'
                }, {
                    name: 'HasImage'
                }]),
            groupField: 'company',
            //groupDir: 'ASC',
            sortInfo: {
                field: 'acet_Date',
                direction: "DESC"
            },
            root: 'data',
            //  remoteSort: true,
            autoLoad: false,
            id: 'df_auditingStore'
        });
    };

    var click = 0;
    var clickFn = function (record) {
        Application.Finascop_DataFinalised.acet_NO = record.get('acet_NO');
        Application.Finascop_DataFinalised.account_type = record.get('type_name');
        Application.Finascop_DataFinalised.ImageUrl = (!Ext.isEmpty(record.get('acet_ImageURL'))) ? record.get('acet_ImageURL') : Ext.BLANK_IMAGE_URL;
        Application.Finascop_DataFinalised.updated_on = record.get('updated_on');
        Ext.getCmp('df_audit_parent_panel').expand(true);
        if (click == 1) {
            click = 0;
            validateandloadDetails(record.get('acet_NO'), record.get('type_name'), record.get('updated_on'), record.get('acet_Status'), 1, record);
        } else {
            winLoadMask.hide();
        }
        //Ext.getCmp('df_id_details').disable();
        //Ext.getCmp('df_audit_particular_grid').disable();
        //Ext.getCmp('df_id_narration').disable();
    };

    var tooltipRenderer = function (value, meta, record, rowindx, colindx, store) {
        meta.attr = 'ext:qtip="' + record.get("narration") + '"';
        return value;
    };
    var dateFieldsInRange = function () {
        from_date = new Date(Ext.getCmp('df_search_from').getValue());
        to_date = new Date(Ext.getCmp('df_search_to').getValue());
        if (((to_date - from_date) / (1000 * 60 * 60 * 24)) > dateRange) {
            return false;
        } else {
            return true;
        }
    }
    var imageAttachedRenderer = function (value, meta, record, rowindx, colindx, store) {
        if (record.get('HasImage') == '1') {
            meta.css = 'finascop_my-icon54';
        } else {
            meta.css = 'finascop_my-icon55';
        }
        meta.attr = 'ext:qtip="' + record.get("narration") + '"';
        return value;
    };
    var auditGrid = function () {

        var auditing_store = auditingStore();

        var data_finalised_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'date',
                    dataIndex: 'acet_Date'
                }, {
                    type: 'string',
                    dataIndex: 'acet_DocNO'
                }, {
                    type: 'list',
                    dataIndex: 'type_name',
                    options: ['Receipt', 'Payment', 'Journal Voucher', 'Contra Entry'],
                    phpMode: true
                }, {
                    type: 'string',
                    dataIndex: 'acet_EntryBy'
                }, {
                    type: 'string',
                    dataIndex: 'accounts_ip'
                }, {
                    type: 'string',
                    dataIndex: 'particular_ip'
                }, {
                    type: 'numeric',
                    dataIndex: 'acet_Amount'
                }]
        });

        data_finalised_filter.remote = true;
        data_finalised_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            ds: auditing_store,
            plugins: [data_finalised_filter],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                showGroupName: false,
                enableNoGroups: false,
                enableGroupingMenu: false,
                hideGroupedColumn: false,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {

                    if (record.get('acet_Status') == 4)
                    {
                        return 'indicateCOL2';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            layout: 'fit',
            region: 'center',
            frame: false,
            border: true,
            id: 'df_audit_main_panel',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    id: 'df_audit_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_Date',
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.get("narration") + '"';
                        dateret = Ext.util.Format.date(value, 'd-m-y');

                        return dateret;
                    }

                }, {
                    header: 'Reference Number',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_DocNO',
                    renderer: imageAttachedRenderer
                }, {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'type_name',
                    renderer: tooltipRenderer,
                    tooltip: 'Type'
                }, {
                    header: 'Company',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'company',
                    renderer: tooltipRenderer
                },
                {
                    header: 'Branch',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'branch',
                    renderer: tooltipRenderer
                },
                {
                    header: 'Created By',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_EntryBy',
                    renderer: tooltipRenderer
                }, {
                    header: 'Accounts',
                    sortable: true,
                    dataIndex: 'accounts_ip',
                    renderer: tooltipRenderer
                }, {
                    header: 'Particular',
                    sortable: true,
                    dataIndex: 'particular_ip',
                    renderer: tooltipRenderer
                }, {
                    header: 'Amount',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_Amount',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            getClass: function (v, meta, rec) {

                                return 'finascop_print';
                            },
                            tooltip: 'Generate PDF'

                        }]
                },
                {
                    header: '',
                    hideHeaders: true,
                    id: 'spacer',
                    dataIndex: '',
                    width: 25
                }
            ],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                rowclick: function (grid, rowIndex, e) {
                    var target = e.getTarget(null, null, true);

                    if (target.hasClass('finascop_print')) {

                        var record = grid.store.getAt(rowIndex);
                        var t = new Date();
                        var t_stamp = t.format("YmdHis");
                        var acet_NO = record.get('acet_NO');
                        var type = record.get('type_name');
                        var url =
                                modURL + '&op=generatepdf&acet_NO=' +
                                acet_NO + '&type=' +
                                type + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;

                        Ext.get('downloadIframe').dom.src = url;
                    }
                    else
                    {
                        window.setTimeout(function () {
                            if (!isDblClick) {
                                click = 1;
                                var record = grid.store.getAt(rowIndex);
                                Ext.getBody().mask('Loading...');
                                clickFn(record);
                                Ext.getBody().unmask();
                            }
                        }, 500);
                    }
                }

            },
            tbar: [{
                    html: '&nbsp;&nbsp;Type : &nbsp;'
                }, {
                    xtype: 'combo',
                    store: brComboStore(3),
                    mode: 'local',
                    id: 'df_audit_type',
                    hiddenName: 'df_audit_type',
                    displayField: 'name',
                    editable: true,
                    valueField: 'id',
                    minChars: 2,
                    width: 120,
                    typeAhead: true,
                    selectOnFocus: true,
                    triggerAction: 'all',
                    lazyRender: true
                }, {
                    html: '&nbsp;From : &nbsp;'
                }, {
                    fieldLabel: 'From',
                    xtype: 'datefield',
                    id: 'df_search_from',
                    name: 'n[search_from]',
                    anchor: '98%',
                    width: 80,
                    editable: true,
                    allowBlank: false,
                    value: new Date().add(Date.DAY, -(dateRange)),
                    format: 'd/m/Y'
                }, {
                    html: '&nbsp;To: &nbsp;'
                }, {
                    fieldLabel: 'To',
                    xtype: 'datefield',
                    id: 'df_search_to',
                    name: 'n[search_to]',
                    anchor: '98%',
                    width: 80,
                    editable: true,
                    allowBlank: false,
                    value: (new Date()),
                    format: 'd/m/Y'
                }, {
                    html: '&nbsp;Status: &nbsp;'
                }, {
                    xtype: 'combo',
                    triggerAction: 'all',
                    id: 'id_status',
                    anchor: '95%',
                    mode: 'local',
                    name: 'id_status',
                    width: 120,
                    hiddenName: 'id_status',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    minChars: 2,
                    allowBlank: false,
                    // fieldLabel: 'Status',
                    store: [['1', 'All'], ['2', 'Approved'], ['3', 'Deleted']],
                    listeners: {
                        afterrender: function () {

                            this.setValue(1);
                        }
                    }

                }, '-', {
                    text: 'Filter',
                    iconCls: 'finascop_search_btn',
                    id: 'df_filter_button',
                    style: 'margin-left:10px;',
                    handler: function () {
                        if (dateFieldsInRange()) {
                            auditing_store.baseParams = {
                                audit_type: Ext.getCmp('df_audit_type').getValue(),
                                from_date: Ext.getCmp('df_search_from').getValue(),
                                to_date: Ext.getCmp('df_search_to').getValue(),
                                status: Ext.getCmp('id_status').getValue()
                            };


                            clearRightPane();
                            auditing_store.load();
                        } else {
                            Ext.Msg.alert("Notification", "Dates must be in a range of " + dateRange + "days maximum");
                        }
                    }
                }, '-', {
                    xtype: 'button',
                    text: 'Reset',
                    iconCls: 'finascop_my-resetpass',
                    handler: function () {
                        auditing_store.baseParams = {
                            audit_company: 0,
                            audit_branch: 0,
                            audit_type: 0
                        };

                        Ext.getCmp('df_audit_type').reset();
                        Ext.getCmp('df_search_from').reset();
                        Ext.getCmp('df_search_to').reset();
                        clearRightPane();
                        auditing_store.removeAll();
                    }
                }],
            /*bbar: new Ext.PagingToolbar({
             pageSize: recs_per_page,
             store: auditing_store,
             displayInfo: true,
             displayMsg: 'Displaying records {0} - {1} of {2}',
             emptyMsg: "No records to display"
             }),*/
            stripeRows: true,
            autoExpandColumn: 'df_audit_auto_exp'
        });
        return grid_panel;
    };

    var winLoadMask;

    var loadDetails = function (acet_NO, type, data, upddate, Status, clicktype, record) {
        Ext.getCmp('df_audit_receipt_particular').getStore().baseParams.type = type;
        Ext.getCmp('df_audit_receipt_particular').getStore().load();

        var t = new Date();
        var t_stamp = t.format("YmdHis");


        (type == 'Journal Voucher') ? Ext.getCmp('df_radios').show() : Ext.getCmp('df_radios').hide();
        //(type == 'Journal Voucher') ? Ext.getCmp('df_audit_receipt_account').disable() : Ext.getCmp('df_audit_receipt_account').enable();
        /* setting panel title   */
        Ext.getCmp('df_audit_parent_panel').setTitle(data.acet_DocNO);
        var activeTab = Ext.getCmp('df_audit_details_tab').getActiveTab();
        var ind = Ext.getCmp('df_audit_details_tab').items.findIndex('id', activeTab.id);
        if (ind == 1 && !Ext.isEmpty(Application.Finascop_DataFinalised.ImageUrl))
            Ext.getCmp('df_audit_image_panel').update({'img_src': Application.Finascop_DataFinalised.ImageUrl});

        Ext.getCmp('df_audit_particular_grid').getStore().load({
            params: {
                apikey: _SESSION.apikey,
                tstamp: t_stamp,
                acet_NO: acet_NO,
                type: type,
                acc_ledger_id: data.account
            }
        });

        if (data.acet_Date != '00-00-0000' && !Ext.isEmpty(data.acet_Date))
            Ext.getCmp('df_audit_receipt_date').setValue(data.acet_Date);
        Ext.getCmp('df_audit_receipt_total_amount').setValue(data.acet_Amount);
        Ext.getCmp('df_audit_receipt_narration').setValue(data.acet_Narration);
        Ext.getCmp('df_audit_ledger_type_selected').setValue(data.acet_TypeId);
        if (data.IsDebtor == 0) {
            Ext.getCmp('df_audit_account_type2').setValue(true);
        }
        if (data.IsDebtor == 1) {
            Ext.getCmp('df_audit_account_type1').setValue(true);
        }

        var audit_receipt_account = Ext.getCmp('df_audit_receipt_account');
        audit_receipt_account.getStore().load({
            callback: function () {
                audit_receipt_account.setValue(data.account);
                Ext.getCmp('df_audit_acet_NO').setValue(data.acet_NO);

                var index = audit_receipt_account.getStore().find('accled_Ledger_Id', data.account);
                if (index > -1) {
                    var rec = audit_receipt_account.getStore().getAt(index);
                    var Group_ID = rec.get('Group_ID');
                    if (type == 'Contra Entry') {
                        var audit_receipt_particular = Ext.getCmp('df_audit_receipt_particular');
                        var audit_receipt_particular_store = audit_receipt_particular.getStore();
                        audit_receipt_particular.reset();
                        audit_receipt_particular_store.removeAll();
                        audit_receipt_particular_store.baseParams.Group_ID = Group_ID;
                        audit_receipt_particular_store.load();
                    }
                }
            }
        });

//                if (Status == 0 && clicktype == 2) {
//                    updateTransactionStatus(acet_NO, 1, upddate);
//                    record.set('acet_Status', 1);
//                }
        //changeButtonDisplay(clicktype, Status);
    }

    var validateandloadDetails = function (acet_NO, type, upddate, Status, clicktype, record) {
        var isvalid = false;
        Ext.Ajax.request({
            url: modURL + '&op=getDetails',
            method: 'POST',
            params: {
                acet_NO: acet_NO,
                type: type
            },
            success: function (response) {
                //eval("var tmp=" + response.responseText);
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true) {
                    var data = tmp.data;
                    Application.Finascop_DataFinalised.company_id = data.comp_id;
                    Application.Finascop_DataFinalised.branch_id = data.branch_id;
                    Application.Finascop_DataFinalised.branch_id = data.branch_id;
                    loadDetails(acet_NO, type, data, upddate, Status, clicktype, record);
                    winLoadMask.hide();
                    isvalid = true;
                }
            },
            failure: function (elm, conf) {
                winLoadMask.hide();
                if (conf.failureType === 'server') {
                    var result = Ext.decode(conf.response.responseText);
                    Ext.Msg.alert('Notification', result.error);
                }
                isvalid = false;
            }
        });
        return isvalid;
    };


    var particularStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listParticulars',
                method: 'post'
            }),
            fields: ['particular_id', 'particular_name', {name: 'amount', type: 'float'}],
            totalProperty: 'totalCount',
            root: 'data',
            localSort: true,
            autoLoad: false
        });
        store.setDefaultSort('particular_name', 'ASC');
        return store;
    };

    var brComboStore = function (ind) {
        store = new Ext.data.ArrayStore({
            url: modURL + '&op=getComboStore&ind=' + ind,
            method: 'POST',
            autoLoad: true,
            fields: ['id', 'name']
        });

        return store;
    };

    var particularDetails = function () {
        var particular_store = particularStore();

        return new Ext.Panel({
            title: 'Details',
            id: 'df_details_tab',
            border: false,
            region: 'center',
            layout: "border",
            defaults: {
                border: false,
                hideBorders: true
            },
            items: [
                new Ext.Panel({
                    layout: "column",
                    region: 'north',
                    border: false,
                    //autoHeight: true,
                    height: 80,
                    id: 'df_id_details',
                    hideBorders: true,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.35,
                            style: 'margin:10px 0 5px 5px;',
                            labelWidth: 55,
                            labelAlign: 'left',
                            border: false,
                            hideBorders: true,
                            items: {
                                xtype: 'datefield',
                                id: 'df_audit_receipt_date',
                                anchor: '99%',
                                name: 'df_audit_receipt_date',
                                format: 'd-m-Y',
                                fieldLabel: 'Date'
                            }
                        }, {
                            layout: 'form',
                            columnWidth: 0.65,
                            style: 'margin:10px 0 5px 5px;',
                            labelWidth: 55,
                            labelAlign: 'left',
                            border: false,
                            hidden: true,
                            id: 'df_radios',
                            hideBorders: true,
                            items: [{
                                    xtype: 'radiogroup',
                                    id: 'df_audit_ctr_dtr_type',
                                    items: [
                                        {boxLabel: 'Debtor', name: 'account_type', inputValue: 'Debtor', id: 'df_audit_account_type1'},
                                        {boxLabel: 'Creditor', name: 'account_type', inputValue: 'Creditor', id: 'df_audit_account_type2'}
                                    ],
                                    listeners: {
                                        change: function () {
                                            Ext.getCmp('df_audit_receipt_account').enable();
                                        }
                                    }
                                }, {
                                    xtype: 'hidden',
                                    id: 'df_audit_uploaded_file_name',
                                    name: 'df_audit_uploaded_file_name'
                                }, {
                                    xtype: 'hidden',
                                    id: 'df_audit_file_up_id',
                                    name: 'df_audit_file_up_id'
                                }, {
                                    xtype: 'hidden',
                                    id: 'df_audit_ledger_type_selected',
                                    name: 'df_audit_ledger_type_selected'
                                }, {
                                    xtype: 'hidden',
                                    id: 'df_audit_acet_NO',
                                    name: 'df_audit_acet_NO'
                                }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.65,
                            style: 'margin:10px 0 5px 5px;',
                            labelWidth: 55,
                            labelAlign: 'left',
                            border: false,
                            hideBorders: true,
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'Account',
                                    name: 'df_audit_receipt_account',
                                    id: 'df_audit_receipt_account',
                                    anchor: "98%",
                                    store: accountComboStore(), /*'accled_LedgerName', 'accled_Ledger_Id',*/
                                    mode: 'remote',
                                    editable: false,
                                    hiddenName: 'df_audit_receipt_account',
                                    displayField: 'accled_LedgerName',
                                    valueField: 'accled_Ledger_Id',
                                    selectOnFocus: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    disabled: false,
                                    listeners: {
                                        change: function (cbo, value) {
                                            if (!Ext.isEmpty(value)) {
                                                var type = Application.Finascop_DataFinalised.account_type;
                                                var index = cbo.getStore().find('accled_Ledger_Id', value);
                                                if (index > -1) {
                                                    var rec = cbo.getStore().getAt(index);
                                                    var ledgertypeid = rec.get('ledgertypeid');
                                                    var led_type = 0
                                                    if (ledgertypeid == 1 && type == 'Receipt')
                                                        led_type = 1;
                                                    else if (ledgertypeid == 2 && type == 'Receipt')
                                                        led_type = 3;
                                                    else if (ledgertypeid == 1 && type == 'Payment')
                                                        led_type = 2;
                                                    else if (ledgertypeid == 2 && type == 'Payment')
                                                        led_type = 4;
                                                    else if (type == 'Journal Voucher') {
                                                        led_type = 5;
                                                    } else if (type == 'Contra Entry') {
                                                        led_type = 6;
                                                    }
                                                    Ext.getCmp('df_audit_ledger_type_selected').setValue(led_type);
                                                }
                                            }
                                        }



                                    }
                                }]
                        }]
                }),
                new Ext.grid.GridPanel({
                    store: particular_store,
                    region: 'center',
                    layout: 'fit',
                    frame: false,
                    border: false,
                    loadMask: true,
                    id: 'df_audit_particular_grid',
                    columns: [new Ext.grid.RowNumberer(),
                        {header: 'Particular',
                            id: 'df_audit_particular_auto_exp',
                            sortable: true,
                            hideable: false,
                            width: 65,
                            dataIndex: 'particular_name'
                        }, {
                            header: 'Amount',
                            sortable: true,
                            xtype: 'finascopcurrency',
                            format: FINASCOP_CURRENCY_FORMAT,
                            dataIndex: 'amount',
                            width: 25,
                            align: 'right'
                        }],
                    viewConfig: {
                        forceFit: true,
                        deferEmptyText: false,
                        emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                    },
                    sm: new Ext.grid.RowSelectionModel({
                        singleSelect: true
                    }),
                    tbar: [{html: '&nbsp;Particular : &nbsp;'},
                        {xtype: 'combo',
                            name: 'df_audit_receipt_particular',
                            id: 'df_audit_receipt_particular',
                            width: 230,
                            store: particularComboStore(),
                            mode: 'remote',
                            editable: false,
                            hiddenName: 'df_audit_receipt_particular',
                            displayField: 'accled_LedgerName',
                            valueField: 'accled_Ledger_Id',
                            selectOnFocus: true,
                            triggerAction: 'all',
                            lazyRender: true
                        }, {html: '&nbsp;&nbsp;Amount : &nbsp;'},
                        {
                            width: 100,
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            id: 'df_audit_receipt_amount',
                            allowDecimal: true,
                            allowNegative: false
                        }],
                    bbar: ['->', {html: 'Total : &nbsp;'}, {
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            readOnly: true,
                            value: 0,
                            id: 'df_audit_receipt_total_amount',
                            style: 'text-align: right'
                        }],
                    stripeRows: true,
                    autoExpandColumn: 'df_audit_particular_auto_exp'
                }), new Ext.Panel({
                    layout: 'form',
                    labelAlign: 'top',
                    region: 'south',
                    id: 'df_id_narration',
                    border: false,
                    autoHeight: true,
                    hideBorders: true,
                    items: [{
                            xtype: 'textarea',
                            labelStyle: 'margin-left:15px;',
                            fieldLabel: 'Narration',
                            id: 'df_audit_receipt_narration',
                            name: 'df_audit_receipt_narration',
                            anchor: '98%',
                            maxLength: 500,
                            style: 'margin-left:15px;'
                        }]
                })
            ]
        });
    };



    var accountComboStore = function () {

        return  new Ext.data.JsonStore({
            autoLoad: false,
            //url: '?module=data_entry&op=getAccounts&account_type='+receipt_window.account_type,
            url: '?module=finascop_data_entry&op=getAccounts',
            method: 'post',
            fields: ['accled_LedgerName', 'accled_Ledger_Id', 'GroupName', 'ledgertypename', 'ledgertypeid', 'Group_ID'],
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.type = Application.Finascop_DataFinalised.account_type;
                    this.baseParams.company_id = Application.Finascop_DataFinalised.company_id;
                    this.baseParams.branch_id = Application.Finascop_DataFinalised.branch_id;
                }
            }



        });
    };

    var particularComboStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: false,
            url: '?module=data_entry&op=getParticulars',
            method: 'post',
            fields: ['accled_LedgerName', 'accled_Ledger_Id', 'GroupName', 'ledgertypename', 'ledgertypeid'],
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.type = Application.Finascop_DataFinalised.account_type;
                    this.baseParams.company_id = Application.Finascop_DataFinalised.company_id;
                    this.baseParams.branch_id = Application.Finascop_DataFinalised.branch_id;
                }
            }
        });
        return store;
    };

    var detailsTab = function () {
        var tab = new Ext.TabPanel({
            activeTab: 0,
            border: false,
            id: 'df_audit_details_tab',
            deferredRender: false,
            enableTabScroll: true,
            tabPosition: 'top',
            items: [particularDetails(), {
                    title: 'Image',
                    layout: 'fit',
                    items: [new Ext.Panel({
                            layout: "fit",
                            region: 'center',
                            autoScroll: true,
                            id: 'df_audit_image_panel',
                            tpl: new Ext.XTemplate('<div class="details-outer">',
                                    '<img src="{img_src}"></img>',
                                    '</div>')
                        })]
                }],
            listeners: {
                tabchange: function (panel, currentTab) {
                    var activeTab = panel.getActiveTab();
                    var ind = panel.items.findIndex('id', activeTab.id);
                    if (ind == 1 && !Ext.isEmpty(Application.Finascop_DataFinalised.ImageUrl))
                        Ext.getCmp('df_audit_image_panel').update({'img_src': Application.Finascop_DataFinalised.ImageUrl});
                }
            }
        });
        return tab;
    };

    var changeButtonDisplay = function (t, stat) {

        if (stat == 1 || stat == 2) {
            t = 2;
        } else if (stat == 3 || stat == 4) {
            t = 1;
        }


//                if (t == 1) {
//                    Ext.getCmp('df_audit_particular_grid').getColumnModel().setHidden(3, true);
//                } else {
//                    Ext.getCmp('df_audit_particular_grid').getColumnModel().setHidden(3, false);
//                }
        Ext.getCmp('df_audit_parent_panel').doLayout();

    };

    var clearRightPane = function () {



        //Ext.getCmp('df_id_details').disable();
        //Ext.getCmp('df_audit_particular_grid').disable();
        Ext.getCmp('df_audit_receipt_narration').setValue("");
        //Ext.getCmp('df_id_narration').disable();
        Ext.getCmp('df_audit_particular_grid').getStore().removeAll();
        Ext.getCmp('df_audit_receipt_account').getStore().removeAll();
        Ext.getCmp('df_audit_receipt_account').setValue('');
        Ext.getCmp('df_audit_parent_panel').doLayout();


    };

    var auditMasterPanel = function (id) {

        var panel = new Ext.Panel({
            frame: false,
            layout: 'border',
            bodyStyle: {"background-color": "white", "padding": "5px 3px"},
            border: false,
            id: id,
            title: 'Data Finalised',
            //iconCls: 'finascop_audit',
            items: [auditGrid(),
                new Ext.Panel({
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    id: 'df_audit_parent_panel',
                    height: winsize.height * 0.6,
                    layout: 'border',
                    collapsible: true,
                    collapseMode: 'mini',
                    collapsed: true,
                    cls: 'left_side_panel',
                    items: {
                        region: 'center',
                        layout: 'fit',
                        items: detailsTab()
                    }

                })],
            listeners: {afterrender: function () {
                    winLoadMask = new Ext.LoadMask(Ext.getCmp(id).getEl());




                }}
        });
        return panel;
    };
    var auditMasterPanelDR = function (id, type) {
        var paneltitle;
        switch (type) {
            case 'DR':
                paneltitle = 'Sales';
                break;
            case 'CR':
                paneltitle = 'Cash Receipt';
                break;
            case 'BR':
                paneltitle = 'Bank Receipt';
                break;
        }
        var panel = new Ext.Panel({
            frame: false,
            layout: 'border',
            bodyStyle: {"background-color": "white", "padding": "5px 3px"},
            border: false,
            id: id,
            title: paneltitle,
            items: [auditGridDR(type),
                new Ext.Panel({
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    id: 'df_audit_parent_panel' + type,
                    height: winsize.height * 0.6,
                    layout: 'border',
                    collapsible: true,
                    collapseMode: 'mini',
                    collapsed: true,
                    cls: 'left_side_panel',
                    items: {
                        region: 'center',
                        layout: 'fit',
                        items: detailsTab(type)
                    }

                })],
            listeners: {afterrender: function () {
                    winLoadMask = new Ext.LoadMask(Ext.getCmp(id).getEl());




                }}
        });
        return panel;
    };
    var auditingStoreDR = function (type) {
        return new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listAuditDR',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: '',
                root: 'data'
            },
            /* ['acet_NO', 'acet_Date', 'acet_Amount', 'acet_InWords', 'comp_id', 'accounts_ip', 'particular_ip',
             'acet_TypeId', 'company', 'acet_EntryBy', 'acet_ImageURL', 'entry_type', 'type_name', 'updated_on']),*/

            [{
                    name: 'acet_NO'
                },
                {
                    name: 'acet_DocNO'
                },
                {
                    name: 'acet_Date',
                    type: 'date',
                    dateFormat: 'd-m-y'
                }, {
                    name: 'acet_Amount'
                }, {
                    name: 'acet_InWords'
                }, {
                    name: 'comp_id'
                }, {
                    name: 'accounts_ip'
                }, {
                    name: 'particular_ip'
                }, {
                    name: 'acet_TypeId'
                }, {
                    name: 'company'
                }, {
                    name: 'branch'
                }, {
                    name: 'acet_EntryBy'
                }, {
                    name: 'acet_ImageURL'
                }, {
                    name: 'entry_type'
                }, {
                    name: 'type_name'
                }, {
                    name: 'updated_on'
                }, {
                    name: 'narration'
                }, {
                    name: 'acet_Status'
                }, {
                    name: 'HasImage'
                }]),
            groupField: 'company',
            //groupDir: 'ASC',
            sortInfo: {
                field: 'acet_Date',
                direction: "DESC"
            },
            root: 'data',
            //  remoteSort: true,
            autoLoad: false,
            id: 'df_auditingStore'
        });
    };
    var auditGridDR = function (type) {

        var auditing_store = auditingStoreDR(type);

        var data_finalised_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'date',
                    dataIndex: 'acet_Date'
                }, {
                    type: 'string',
                    dataIndex: 'acet_DocNO'
                }, {
                    type: 'list',
                    dataIndex: 'type_name',
                    options: ['Receipt', 'Payment', 'Journal Voucher', 'Contra Entry'],
                    phpMode: true
                }, {
                    type: 'string',
                    dataIndex: 'acet_EntryBy'
                }, {
                    type: 'string',
                    dataIndex: 'accounts_ip'
                }, {
                    type: 'string',
                    dataIndex: 'particular_ip'
                }, {
                    type: 'numeric',
                    dataIndex: 'acet_Amount'
                }]
        });

        data_finalised_filter.remote = true;
        data_finalised_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            ds: auditing_store,
            plugins: [data_finalised_filter],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                showGroupName: false,
                enableNoGroups: false,
                enableGroupingMenu: false,
                hideGroupedColumn: false,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {

                    if (record.get('acet_Status') == 4)
                    {
                        return 'indicateCOL2';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            layout: 'fit',
            region: 'center',
            frame: false,
            border: true,
            id: 'df_audit_main_panel' + type,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    id: 'df_audit_auto_exp',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_Date',
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.get("narration") + '"';
                        dateret = Ext.util.Format.date(value, 'd-m-y');

                        return dateret;
                    }

                }, {
                    header: 'Reference Number',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_DocNO',
                    renderer: imageAttachedRenderer
                }, {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'type_name',
                    renderer: tooltipRenderer,
                    tooltip: 'Type'
                }, {
                    header: 'Company',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'company',
                    renderer: tooltipRenderer
                },
                {
                    header: 'Branch',
                    sortable: true,
                    hidden: true,
                    dataIndex: 'branch',
                    renderer: tooltipRenderer
                },
                {
                    header: 'Created By',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_EntryBy',
                    renderer: tooltipRenderer
                }, {
                    header: 'Accounts',
                    sortable: true,
                    dataIndex: 'accounts_ip',
                    renderer: tooltipRenderer
                }, {
                    header: 'Particular',
                    sortable: true,
                    dataIndex: 'particular_ip',
                    renderer: tooltipRenderer
                }, {
                    header: 'Amount',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_Amount',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right'
                },
                {
                    header: '',
                    hideHeaders: true,
                    id: 'spacer',
                    dataIndex: '',
                    width: 25
                }
            ],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
            },
            tbar: [{
                    html: '&nbsp;From : &nbsp;'
                }, {
                    fieldLabel: 'From',
                    xtype: 'datefield',
                    id: 'df_search_from' + type,
                    name: 'n[search_from]',
                    anchor: '98%',
                    width: 80,
                    editable: true,
                    allowBlank: false,
                    value: new Date(),
                    format: 'd/m/Y'
                }, {
                    html: '&nbsp;To: &nbsp;'
                }, {
                    fieldLabel: 'To',
                    xtype: 'datefield',
                    id: 'df_search_to' + type,
                    name: 'n[search_to]',
                    anchor: '98%',
                    width: 80,
                    editable: true,
                    allowBlank: false,
                    value: (new Date()),
                    format: 'd/m/Y'
                }, '-', {
                    text: 'Filter',
                    iconCls: 'finascop_search_btn',
                    id: 'df_filter_button' + type,
                    style: 'margin-left:10px;',
                    handler: function () {
                        auditing_store.baseParams = {
                            from_date: Ext.getCmp('df_search_from' + type).getValue(),
                            to_date: Ext.getCmp('df_search_to' + type).getValue(),
                            type: type
                        };
                        auditing_store.load();

                    }
                }, '-', {
                    xtype: 'button',
                    text: 'Reset',
                    iconCls: 'finascop_my-resetpass',
                    handler: function () {
                        auditing_store.baseParams = {
                            audit_company: 0,
                            audit_branch: 0,
                        };
                        Ext.getCmp('df_search_from' + type).reset();
                        Ext.getCmp('df_search_to' + type).reset();
                        clearRightPane();
                        auditing_store.removeAll();
                    }
                }],
            stripeRows: true,
            autoExpandColumn: 'df_audit_auto_exp'
        });
        return grid_panel;
    };
    return {
        initAudit: function () {
//		var panelId = 'audit_master_panel';
//		var audit_main = Ext.getCmp(panelId);
//		if (Ext.isEmpty(audit_main)) {
//			audit_main = auditMasterPanel(panelId);
//			audit_main.doLayout();
//			return audit_main;
//			} else {
//			audit_main.doLayout();
//		}

            Application.Finascop_DataFinalised.store_add_edit = '';
            var panelId = 'df_audit_master_panel';
            var audit_master_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(audit_master_panel)) {
                audit_master_panel = auditMasterPanel(panelId);
                Application.UI.addTab(audit_master_panel);
                audit_master_panel.doLayout();
            } else {
                Application.UI.addTab(audit_master_panel);
            }


        },
        AuditMainPanel: function () {
            return [{
                    region: 'center',
                    border: false,
                    layout: 'fit',
                    items: Application.Finascop_DataFinalised.initAudit()
                }];
        },
        account_processing: function () {

            var type = Application.Finascop_DataFinalised.update_type;
            Ext.Ajax.request({
                url: modURL + '&op=updateStatus',
                method: 'POST',
                params: {
                    acet_NO: Application.Finascop_DataFinalised.acet_NO,
                    type: Application.Finascop_DataFinalised.update_type,
                    updated_on: Application.Finascop_DataFinalised.updated_on
                },
                success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true) {
                        if (type == 1) {
                            if (res.escalate == true) {
                                Ext.getCmp('df_audit_escalate_btn').show();
                            }
                        } else {
                            var msg = (!Ext.isEmpty(res.msg)) ? res.msg : "Status has been changed successfully.";
                            Ext.Msg.alert("Notification", msg, function (btn) {

                                Ext.getCmp('df_audit_main_panel').getStore().load();
                                clearRightPane();

                            });
                        }
                    } else {
                        var msg = res.msg;
                        Ext.Msg.alert("Notification", msg, function (btn) {

                            Ext.getCmp('df_audit_main_panel').getStore().load();
                            clearRightPane();

                        });
                    }
                },
                failure: function (elm, conf) {
                    Ext.Msg.alert("Error", "Error occured while saving data.", function (btn) {

                        Ext.getCmp('df_audit_main_panel').getStore().load();
                        clearRightPane();

                    });
                }
            });
        },
        saveData: function () {

            var grid = Ext.getCmp('df_audit_particular_grid');
            var grid_store = grid.getStore();
            var data = Ext.pluck(grid_store.getRange(), 'data');
            Ext.Ajax.request({
                url: modURL + '&op=saveParticularData',
                method: 'POST',
                params: {
                    "particular_data": Ext.encode(data),
                    "receipt_date": Ext.getCmp('df_audit_receipt_date').getRawValue(),
                    "receipt_account": Ext.getCmp('df_audit_receipt_account').getValue(),
                    "receipt_account_name": Ext.getCmp('df_audit_receipt_account').getRawValue(),
                    "ledger_type": Ext.getCmp('df_audit_ledger_type_selected').getValue(),
                    "total_amount": Ext.getCmp('df_audit_receipt_total_amount').getValue(),
                    "narration": Ext.getCmp('df_audit_receipt_narration').getValue(),
                    "type": Application.Finascop_DataFinalised.account_type,
                    "ctr_dtr_type": Ext.getCmp('df_audit_ctr_dtr_type').getValue(),
                    acet_NO: Application.Finascop_DataFinalised.acet_NO,
                    "updated_on": Application.Finascop_DataFinalised.updated_on
                },
                success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true) {
                        Ext.Msg.alert('Success', 'Details has been saved successfully.', function (btn) {

                            Ext.getCmp('df_audit_main_panel').getStore().load();
                            clearRightPane();
                        });
                    } else {
                        Ext.MessageBox.alert('Error', res.msg, function (btn) {

                            Ext.getCmp('df_audit_main_panel').getStore().load();
                            clearRightPane();
                        });

                    }
                },
                failure: function (elm, conf) {
                    if (conf.failureType === 'server') {
                        var result = Ext.decode(conf.response.responseText);
                        Ext.Msg.alert('Notification', result.error);
                    } else {
                        Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                    }
                }
            });
        }, AuditMainPanelDR: function () {
            return [{
                    region: 'center',
                    border: false,
                    layout: 'fit',
                    items: Application.Finascop_DataFinalised.initAuditDR('DR')
                }];
        }, initAuditDR: function (type) {

            Application.Finascop_DataFinalised.store_add_edit = '';
            var panelId = 'df_audit_master_paneldr' + type;
            var audit_master_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(audit_master_panel)) {
                audit_master_panel = auditMasterPanelDR(panelId, type);
                Application.UI.addTab(audit_master_panel);
                audit_master_panel.doLayout();
            } else {
                Application.UI.addTab(audit_master_panel);
            }


        }, AuditMainPanelCR: function () {
            return [{
                    region: 'center',
                    border: false,
                    layout: 'fit',
                    items: Application.Finascop_DataFinalised.initAuditDR('CR')
                }];
        }, AuditMainPanelBR: function () {
            return [{
                    region: 'center',
                    border: false,
                    layout: 'fit',
                    items: Application.Finascop_DataFinalised.initAuditDR('BR')
                }];
        }
    };
}();


