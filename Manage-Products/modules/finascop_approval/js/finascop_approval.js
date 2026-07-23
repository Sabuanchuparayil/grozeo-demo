/* global Ext */

Application.Finascop_Approval = function () {
    var isDblClick;
    var recs_per_page = 12;
    var winsize = Ext.getBody().getViewSize();
    var dateRange = parseInt(BETWEEN_DATES_TYPE_A);
    var modURL = '?module=finascop_approval';

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var dateFieldsInRange = function () {
        var from_date = new Date(Ext.getCmp('da_search_from').getValue());
        var to_date = new Date(Ext.getCmp('da_search_to').getValue());
        if (((to_date - from_date) / (1000 * 60 * 60 * 24)) > dateRange) {
            return false;
        } else {
            return true;
        }
    }

    var approvingStore = function () {
        return new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listApproval',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: '',
                root: 'data'
            },
            /* ['acet_NO', 'acet_Date', 'acet_Amount', 'acet_InWords', 'comp_id', 'accounts_ip', 'particular_ip',
             'acet_TypeId', 'company', 'acet_EntryBy', 'acet_ImageURL', 'entry_type', 'type_name', 'updated_on']),*/

            [{name: 'acet_NO'},
                {name: 'acet_DocNO'},
                {
                    name: 'acet_Date',
                    type: 'date',
                    dateFormat: 'd-m-y'
                }, {
                    name: 'RollBack',
                    type: 'number'
                }, {
                    name: 'acet_Amount', type: 'float'
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
                }, {
                    name: 'acet_SourceOfEntry'
                }, {
                    name: 'IsOtherAssignedUser'
                }, {
                    name: 'AssignedToName'
                }, {
                    name: 'escalateComments'
                }, {
                    name: 'rebutComments'
                }
            ]),
            groupField: 'company',
            //groupDir: 'ASC',
            sortInfo: {
                field: 'acet_Date',
                direction: "DESC"
            },
            root: 'data',
            //  remoteSort: true,
            autoLoad: false
        });
    };

    var click = 0;
    var clickFn = function (record) {
        Application.Finascop_Approval.acet_NO = record.get('acet_NO');
        Application.Finascop_Approval.acet_DocNO = record.get('acet_DocNO');
        Application.Finascop_Approval.account_type = record.get('type_name');
        Application.Finascop_Approval.ImageUrl = (!Ext.isEmpty(record.get('acet_ImageURL'))) ? record.get('acet_ImageURL') : Ext.BLANK_IMAGE_URL;
        Application.Finascop_Approval.updated_on = record.get('updated_on');
        Ext.getCmp('approval_parent_panel').expand(true);
        if (click == 1) {
            click = 0;
            validateandloadDetails(record.get('acet_NO'), record.get('type_name'), record.get('updated_on'), record.get('acet_Status'), 1, record);

        } else if (click == 2) {
            click = 0;
            Application.Finascop_Approval.update_type = 1;
            validateandloadDetails(record.get('acet_NO'), record.get('type_name'), record.get('updated_on'), record.get('acet_Status'), 2, record);
        } else {
            winLoadMask.hide();
        }
        Ext.getCmp('id_details').disable();
        Ext.getCmp('approval_particular_grid').disable();
        Ext.getCmp('id_narration').disable();

    };

    var tooltipRenderer = function (value, meta, record, rowindx, colindx, store) {
        meta.attr = 'ext:qtip="' + record.get("narration") + '"';
        return value;
    };


    var imageAttachedRenderer = function (value, meta, record, rowindx, colindx, store) {
        meta.attr = 'ext:qtip="' + record.get("narration") + '"';
        if (record.get('HasImage') == '1') {
            meta.css = 'finascop_my-icon54 ';
        } else {
            meta.css = 'finascop_my-icon55 ';
        }
        if (record.get('escalateComments') != '') {
            meta.css += 'finascop_my-icon56';
            meta.attr = 'ext:qtip="Narration:' + record.get("narration") + '<br>Escalation Comments:' + record.get("escalateComments") + '"';
        }
        if (record.get('rebutComments') != '') {
            meta.css += ' finascop_my-icon57';
            meta.attr = 'ext:qtip="Narration:' + record.get("narration") + '<br>Escalation Comments:' + record.get("rebutComments") + '"';
        }



        return value;
    };
    var approvalGrid = function () {

        var approving_store = approvingStore();

        var data_approval_filter = new Ext.ux.grid.GridFilters({
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
                },{
                    type: 'numeric',
                    dataIndex: 'acet_Amount'
                }]
        });

        var grid_panel = new Ext.grid.GridPanel({
            ds: approving_store,
            plugins: [data_approval_filter],
            view: new Ext.grid.GroupingView({
                forceFit: true,
                showGroupName: false,
                enableNoGroups: false,
                enableGroupingMenu: false,
                hideGroupedColumn: false,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {

                    if (record.get('acet_Status') == 0)/*Fresh Entry */
                    {
                        return 'finascop_indicateColWHITE';
                    } else if (record.get('acet_Status') == 1) { /* Taken Over   */
                        if (record.get('IsOtherAssignedUser') == 0)
                            return 'finascop_indicateColPALEGREEN';
                        if (record.get('IsOtherAssignedUser') == 1)
                            return 'finascop_indicateColGUMLEAFGREEN';

                    } else if (record.get('acet_Status') == 2) { /* Escalate    */
                        if (record.get('IsOtherAssignedUser') == 0)
                            return 'finascop_indicateColLIGHTORANGE';
                        if (record.get('IsOtherAssignedUser') == 1)
                            return 'finascop_indicateColCASHMERE';

                    } else if (record.get('acet_Status') == 3) {   /* Aproved */
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else if (record.get('acet_Status') == 4) {   /* Cancelled */
                        return 'finascop_indicateColPINK';
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
            id: 'approval_main_panel',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    id: 'approval_auto_exp',
                    xtype:'datecolumn',
                    format: 'd-m-y',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_Date',
                    renderer: function (value, metadata, record) {
                        metadata.attr = 'ext:qtip="' + record.get("narration") + '"';
                        dateret = Ext.util.Format.date(value, 'd-m-y');

                        return dateret;
                    }

                },
                {
                    header: 'Reference Number',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'acet_DocNO',
                    renderer: imageAttachedRenderer
                },
                {
                    header: 'Type',
                    sortable: true,
                    dataIndex: 'type_name',
                    renderer: tooltipRenderer,
                    tooltip: 'Type'
                },
                {
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
                },
                {
                    header: 'Accounts',
                    sortable: true,
                    dataIndex: 'accounts_ip',
                    renderer: tooltipRenderer
                },
                {
                    header: 'Particular',
                    sortable: true,
                    dataIndex: 'particular_ip',
                    renderer: tooltipRenderer
                },
                {
                    header: 'Amount',
                    sortable: true,
                    hideable: false,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'acet_Amount',
                    renderer: tooltipRenderer,
                    align: 'right'
                }, {
                    header: '',
                    hideHeaders: true,
                    id: 'spacer',
                    dataIndex: '',
                    width: 25
                } /*{
                 xtype: 'actioncolumn',
                 header: 'Action',
                 hideable: false,
                 sortable: false,
                 groupable: false,
                 tooltip: 'Action',
                 items: [
                 {tooltip: 'Roll Back',
                 getClass: function (v, meta, rec) {
                 if (rec.get('RollBack') == 1) {
                 
                 return 'rollback';
                 } else {
                 return 'hideicon';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Ext.MessageBox.confirm('Confirm', 'Do you wish to Rollback?', function (btn, text) {
                 if (btn == 'yes') {
                 
                 }
                 }, this);
                 
                 }
                 }]
                 }*/],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                rowclick: function (grid, rowIndex, e) {
                    /*var target = e.getTarget(null, null, true);
                     
                     if (target.hasClass('x-action-col-icon')) {
                     return;
                     }*/
                    window.setTimeout(function () {
                        if (!isDblClick) {
                            click = 1;
                            var record = grid.store.getAt(rowIndex);
                            Ext.getBody().mask('Loading...');
                            clickFn(record);
                            Ext.getBody().unmask();

                        }
                    }, 500);



                },
                rowdblclick: function (grid, row, e) {
                    isDblClick = true;
                    window.setTimeout(function () {
                        isDblClick = false;
                        Ext.getBody().mask('Loading...');
                        var record = grid.getStore().getAt(row);
                        click = 2;
                        clickFn(record);
                        Ext.getBody().unmask();

                    }, 1000);
                }
            },
            tbar: [{html: '&nbsp;Company :&nbsp;'}, {
                    xtype: 'combo',
                    store: brComboStore(1),
                    mode: 'local',
                    id: 'approval_company',
                    width: 80,
                    hiddenName: 'approval_company',
                    displayField: 'name',
                    editable: true,
                    valueField: 'id',
                    typeAhead: true,
                    forceSelection: true,
                    minChars: 2,
                    allowBlank: false,
                    triggerAction: 'all',
                    lazyRender: true,
                    listeners: {
                        select: function () {
                            var approval_cmp = Ext.getCmp('approval_branch');
                            var approval_cmp_str = approval_cmp.getStore();
                            approval_cmp.reset();
                            approval_cmp_str.removeAll();
                            approval_cmp_str.baseParams = {
                                ind: 2,
                                company: this.getValue()
                            };
                            approval_cmp_str.load();
                        }
                    }
                },
                {
                    html: '&nbsp;&nbsp;Branch : &nbsp;'
                },
                {
                    xtype: 'combo',
                    store: brComboStore(2),
                    mode: 'local',
                    id: 'approval_branch',
                    hiddenName: 'approval_branch',
                    displayField: 'name',
                    width: 80,
                    editable: true,
                    valueField: 'id',
                    allowBlank: false,
                    typeAhead: true,
                    forceSelection: true,
                    minChars: 2,
                    triggerAction: 'all',
                    lazyRender: true
                },
                {
                    html: '&nbsp;&nbsp;Type : &nbsp;'
                },
                {
                    xtype: 'combo',
                    store: brComboStore(3),
                    mode: 'local',
                    id: 'approval_type',
                    hiddenName: 'approval_type',
                    displayField: 'name',
                    editable: true,
                    width: 100,
                    allowBlank: false,
                    valueField: 'id',
                    typeAhead: true,
                    forceSelection: true,
                    minChars: 2,
                    triggerAction: 'all',
                    lazyRender: true
                },
                {
                    html: '&nbsp;Add Finalized : &nbsp;'
                },
                {
                    fieldLabel: 'Finalized',
                    xtype: 'checkbox',
                    id: 'cbx_finalzied',
                    name: 'cbx[finalzed]',
                    anchor: '98%',
                    listeners: {
                        check: function (cbo, checked) {
                            if (checked == true) {
                                Ext.getCmp('approval_parent_panel').collapse(true);
                                Ext.getCmp('da_search_from').show();
                                Ext.getCmp('da_search_to').show();
                                Ext.getCmp('fromLabel').show();
                                Ext.getCmp('toLabel').show();
                            } else {
                                Ext.getCmp('da_search_from').hide();
                                Ext.getCmp('da_search_to').hide();
                                Ext.getCmp('fromLabel').hide();
                                Ext.getCmp('toLabel').hide();
                            }
                        }
                    }
                },
                {
                    html: '&nbsp;From : &nbsp;',
                    id: 'fromLabel',
                    width: 20,
                    hidden: true
                },
                {
                    fieldLabel: 'From',
                    xtype: 'datefield',
                    id: 'da_search_from',
                    name: 'n[search_from]',
                    anchor: '98%',
                    width: 80,
                    editable: true,
                    hidden: true,
                    allowBlank: false,
                    value: new Date().add(Date.DAY, -(dateRange)),
                    format: 'd/m/Y',
                    maxValue: new Date()
                },
                {
                    html: '&nbsp;To: &nbsp;',
                    id: 'toLabel',
                    width: 20,
                    hidden: true
                },
                {
                    fieldLabel: 'To',
                    xtype: 'datefield',
                    id: 'da_search_to',
                    name: 'n[search_to]',
                    anchor: '98%',
                    width: 80,
                    editable: true,
                    hidden: true,
                    allowBlank: false,
                    value: (new Date()),
                    format: 'd/m/Y',
                    maxValue: new Date()
                }, '-',
                {
                    text: 'Filter',
                    iconCls: 'finascop_search_btn',
                    width: 50,
                    style: 'margin-left:10px;',
                    id: 'approval_filter_button',
                    handler: function () {
                        if ((Ext.getCmp('approval_company').getValue() > 0) && (Ext.getCmp('approval_branch').getValue() > 0) && (Ext.getCmp('approval_type').getValue() > 0)) {
                            if (dateFieldsInRange()) {
                                approving_store.baseParams = {
                                    approval_company: Ext.getCmp('approval_company').getValue(),
                                    approval_branch: Ext.getCmp('approval_branch').getValue(),
                                    approval_type: Ext.getCmp('approval_type').getValue(),
                                    approval_finalized: Ext.getCmp('cbx_finalzied').getValue(),
                                    approval_record_from_date: Ext.getCmp('da_search_from').getValue(),
                                    approval_record_to_date: Ext.getCmp('da_search_to').getValue()
                                };
                                clearRightPane();
                                approving_store.load();
                            } else {
                                Ext.Msg.alert("Notification", "Dates must be in a range of " + dateRange + "days maximum")
                            }
                        }else{
                            Ext.Msg.alert("Notification", "Choose fields to filter");
                        }

                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Reset',
                    width: 50,
                    iconCls: 'finascop_my-resetpass',
                    handler: function () {
                        approving_store.baseParams = {
                            approval_company: 0,
                            approval_branch: 0,
                            approval_type: 0
                        };
                        Ext.getCmp('approval_company').reset();
                        Ext.getCmp('approval_branch').reset();
                        Ext.getCmp('approval_type').reset();
                        Ext.getCmp('approval_branch').getStore().removeAll();
                        Ext.getCmp('cbx_finalzied').setValue(false);
                        Ext.getCmp('approval_branch').getStore().baseParams = {
                            ind: 2,
                            company: 0
                        };
                        clearRightPane();
                        approving_store.removeAll();
                    }
                }],
            /*bbar: new Ext.PagingToolbar({
             pageSize: recs_per_page,
             store: approving_store,
             displayInfo: true,
             displayMsg: 'Displaying records {0} - {1} of {2}',
             emptyMsg: "No records to display"
             }),*/
            bbar: [{
                    html: '<div class="finascop_color_wr"><div class="finascop_color-light-green_small"></div><div class="finascop_text_c"> Taken Over by me</div>\
					<div class="finascop_color-light-orange_small"></div> <div class="finascop_text_c">Escalated to me</div>\
					<div class="finascop_color-light-yellow_small"></div><div class="finascop_text_c"> Approved</div>\
					<div class="finascop_color-gumleaf-green_small"></div> <div class="finascop_text_c">Taken Over by Others</div>\
\n\                                     <div class="finascop_color-cashmere_small"></div> <div class="finascop_text_c">Escalated to Others</div>\
\n\                                     <div class="finascop_color-light-red_small"></div> <div class="finascop_text_c">Cancelled</div>\
					<div class="finascop_color-light-white_small"></div><div class="finascop_text_c"> Fresh Entry</div></div> '
                }],
            stripeRows: true,
            autoExpandColumn: 'approval_auto_exp'
        });
        return grid_panel;
    };

    var winLoadMask;

    var loadDetails = function (acet_NO, type, data, updated_on, Status, clicktype, record) {
        Ext.getCmp('approval_receipt_particular').getStore().baseParams.type = type;
        Ext.getCmp('approval_receipt_particular').getStore().load();
        Ext.getCmp('approval_receipt_particular').clearValue();
        var t = new Date();
        var t_stamp = t.format("YmdHis");


        (type == 'Journal Voucher') ? Ext.getCmp('radios').show() : Ext.getCmp('radios').hide();
        //(type == 'Journal Voucher') ? Ext.getCmp('approval_receipt_account').disable() : Ext.getCmp('approval_receipt_account').enable();
        var SourceOfEntry = record.get('acet_SourceOfEntry');
        var approval_parent_panel = Ext.getCmp('approval_parent_panel');
        if (SourceOfEntry == 1) {
            approval_parent_panel.setTitle(record.get('acet_DocNO') + ' via WALLET');
        } else {
            approval_parent_panel.setTitle(record.get('acet_DocNO'));
        }


        var AssignedToName = record.get('AssignedToName');
        var IsOtherAssignedUser = record.get('IsOtherAssignedUser');
        if (IsOtherAssignedUser == 1) {
            if (Status == 1) {
                approval_parent_panel.setTitle(approval_parent_panel.title + ' Assigned to: ' + AssignedToName);
            } else if (Status == 2) {
                approval_parent_panel.setTitle(approval_parent_panel.title + ' Escalated to: ' + AssignedToName);
            }
        }

        var activeTab = Ext.getCmp('approval_details_tab').getActiveTab();
        var ind = Ext.getCmp('approval_details_tab').items.findIndex('id', activeTab.id);
        if (ind == 1 && !Ext.isEmpty(Application.Finascop_Approval.ImageUrl))
            Ext.getCmp('approval_image_panel').update({'img_src': Application.Finascop_Approval.ImageUrl});

        Ext.getCmp('approval_particular_grid').getStore().removeAll();
        if (!Ext.isEmpty(data.account)) {
            Ext.getCmp('approval_particular_grid').getStore().load({
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    acet_NO: acet_NO,
                    type: type,
                    acc_ledger_id: data.account
                }
            });
        }

        if (data.acet_Date != '00-00-0000' && !Ext.isEmpty(data.acet_Date))
            Ext.getCmp('approval_receipt_date').setValue(data.acet_Date);
        Ext.getCmp('approval_receipt_total_amount').setValue(data.acet_Amount);
        Ext.getCmp('approval_receipt_narration').setValue(data.acet_Narration);
        Ext.getCmp('approval_ledger_type_selected').setValue(data.acet_TypeId);
        if (data.IsDebtor == 0) {
            Ext.getCmp('approval_account_type2').setValue(true);
        }
        if (data.IsDebtor == 1) {
            Ext.getCmp('approval_account_type1').setValue(true);
        }

        var approval_receipt_account = Ext.getCmp('approval_receipt_account');
        approval_receipt_account.getStore().load({
            callback: function () {
                approval_receipt_account.setValue(data.account);
                Ext.getCmp('approval_acet_NO').setValue(data.acet_NO);

                var index = approval_receipt_account.getStore().find('accled_Ledger_Id', data.account);
                if (index > -1) {
                    var rec = approval_receipt_account.getStore().getAt(index);
                    var Group_ID = rec.get('Group_ID');
                    if (type == 'Contra Entry') {
                        var approval_receipt_particular = Ext.getCmp('approval_receipt_particular');
                        var approval_receipt_particular_store = approval_receipt_particular.getStore();
                        approval_receipt_particular.reset();
                        approval_receipt_particular_store.removeAll();
                        approval_receipt_particular_store.baseParams.Group_ID = Group_ID;
                        approval_receipt_particular_store.load();
                    }
                }
            }
        });



        if (Status == 0 && clicktype == 2 && SourceOfEntry == 0) {
            Application.Finascop_Approval.selectedrecord = record;
            updateTransactionStatus(acet_NO, 1, updated_on);

        }
        var IsOtherAssignedUser = record.get('IsOtherAssignedUser');
        changeButtonDisplay(clicktype, Status, SourceOfEntry, IsOtherAssignedUser);
    }

    var validateandloadDetails = function (acet_NO, type, updated_on, Status, clicktype, record) {
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

                    Application.Finascop_Approval.company_id = data.comp_id;
                    Application.Finascop_Approval.branch_id = data.branch_id;
                    loadDetails(acet_NO, type, data, updated_on, Status, clicktype, record);
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

    var updateTransactionStatus = function (approval_acet_NO, type, updated_on, comment) {
        var form_data = {
            approval_acet_NO: approval_acet_NO,
            type: type,
            comment: Ext.isEmpty(comment) ? '' : comment,
            updated_on: updated_on
        };
        var params = {
            action: 'Update',
            module: 'approving',
            op: 'updateStatus',
            id: approval_acet_NO,
            extrainfo: 'asd'
        };
        //Application.Finascop_Approval.account_processing (comment);
        APICall(params, function () {
            return  Application.Finascop_Approval.account_processing(comment);
        }, form_data);
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
        return  new Ext.data.ArrayStore({
            url: modURL + '&op=getComboStore&ind=' + ind,
            method: 'POST',
            autoLoad: true,
            fields: ['id', 'name']
        });
    };





    var particularDetails = function () {
        var particular_store = particularStore();

        return new Ext.Panel({
            title: 'Details',
            id: 'details_tab',
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
                    autoHeight: true,
                    id: 'id_details',
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
                                id: 'approval_receipt_date',
                                anchor: '99%',
                                name: 'approval_receipt_date',
                                format: 'd-m-Y',
                                fieldLabel: 'Date',
                                listeners: {
                                    afterrender: function (field) {
                                        Ext.defer(function () {
                                            field.focus(true, 100);
                                        }, 1);
                                    },
                                    specialkey: function (field, e) {
                                        if (e.getKey() == e.ENTER) {
                                            Ext.getCmp('approval_receipt_account').focus();
                                        }
                                    }
                                }
                            }
                        }, {
                            layout: 'form',
                            columnWidth: 0.65,
                            style: 'margin:10px 0 5px 5px;',
                            labelWidth: 55,
                            labelAlign: 'left',
                            border: false,
                            hidden: true,
                            id: 'radios',
                            hideBorders: true,
                            items: [{
                                    xtype: 'radiogroup',
                                    id: 'approval_ctr_dtr_type',
                                    items: [
                                        {boxLabel: 'Debtor', name: 'account_type', inputValue: 'Debtor', id: 'approval_account_type1'},
                                        {boxLabel: 'Creditor', name: 'account_type', inputValue: 'Creditor', id: 'approval_account_type2'}
                                    ],
                                    listeners: {
                                        change: function () {
                                            Ext.getCmp('approval_receipt_account').enable();
                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('approval_receipt_account').focus();
                                            }
                                        }
                                    }
                                }, {
                                    xtype: 'hidden',
                                    id: 'approval_uploaded_file_name',
                                    name: 'approval_uploaded_file_name'
                                }, {
                                    xtype: 'hidden',
                                    id: 'approval_file_up_id',
                                    name: 'approval_file_up_id'
                                }, {
                                    xtype: 'hidden',
                                    id: 'approval_ledger_type_selected',
                                    name: 'approval_ledger_type_selected'
                                }, {
                                    xtype: 'hidden',
                                    id: 'approval_acet_NO',
                                    name: 'approval_acet_NO'
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
                                    name: 'approval_receipt_account',
                                    id: 'approval_receipt_account',
                                    anchor: "98%",
                                    store: accountComboStore(), /*'accled_LedgerName', 'accled_Ledger_Id',*/
                                    mode: 'remote',
                                    editable: false,
                                    hiddenName: 'approval_receipt_account',
                                    displayField: 'accled_LedgerName',
                                    valueField: 'accled_Ledger_Id',
                                    selectOnFocus: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    disabled: false,
                                    listeners: {
                                        change: function (cbo, value) {
                                            if (!Ext.isEmpty(value)) {
                                                var type = Application.Finascop_Approval.account_type;
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
                                                    Ext.getCmp('approval_ledger_type_selected').setValue(led_type);
                                                }
                                            }
                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER) {
                                                Ext.getCmp('approval_receipt_particular').focus();
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
                    id: 'approval_particular_grid',
                    columns: [new Ext.grid.RowNumberer(),
                        {header: 'Particular',
                            id: 'approval_particular_auto_exp',
                            sortable: true,
                            hideable: false,
                            width: 65,
                            dataIndex: 'particular_name'
                        }, {
                            header: 'Amount',
                            sortable: true,
                            dataIndex: 'amount',
                            xtype: 'finascopcurrency',
                            format: FINASCOP_CURRENCY_FORMAT,
                            width: 25,
                            align: 'right'
                        }, {
                            header: "Action",
                            xtype: 'actioncolumn',
                            width: 10,
                            items: [{
                                    text: 'Delete',
                                    iconCls: 'finascop_disable_settings',
                                    icon: IMAGE_BASE_PATH + "/default/icons/finascop_disable_settings.png",
                                    handler: function (grid, rowIndex, colIndex) {
                                        Ext.Msg.confirm('Notification', 'Do you want to remove this?', function (btn) {
                                            if (btn == 'yes') {
                                                var record = grid.store.getAt(rowIndex);
                                                grid.store.removeAt(rowIndex);
                                                grid.getView().refresh();
                                                Ext.getCmp('approval_receipt_total_amount').setValue(Ext.util.Format.round(grid.store.sum('amount'), 2));
                                                /*if (Application.Finascop_Approval.account_type == 'Journal Voucher' && grid.store.getCount() == 0) {
                                                 Ext.getCmp('approval_ctr_dtr_type').enable();
                                                 }*/
                                            }
                                        });
                                    }
                                }]
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
                            name: 'approval_receipt_particular',
                            id: 'approval_receipt_particular',
                            width: 230,
                            store: particularComboStore(),
                            mode: 'remote',
                            editable: true,
                            hiddenName: 'approval_receipt_particular',
                            displayField: 'accled_LedgerName',
                            valueField: 'accled_Ledger_Id',
                            selectOnFocus: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            typeAhead: true,
                            minChars: 1,
                            listeners: {
                                specialkey: function (thisfield, e) {
                                    if (e.getKey() == e.ENTER) {
                                        if (thisfield.getValue() == '') {
                                            if (!Ext.isEmpty(particular_store)) {

                                                Ext.getCmp('approval_receipt_narration').focus();
                                            }
                                        } else {
                                            Ext.getCmp('approval_receipt_amount').focus();
                                        }


                                    }
                                }
                            }
                        }, {html: '&nbsp;&nbsp;Amount : &nbsp;'},
                        {
                            width: 100,
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            id: 'approval_receipt_amount',
                            allowDecimal: true,
                            allowNegative: true,
                            listeners: {
                                specialkey: function (field, e) {
                                    if (e.getKey() == e.ENTER) {
                                        updateParticulars();
                                        Ext.getCmp('approval_receipt_particular').focus();
                                    }
                                }
                            }
                        }, {
                            xtype: 'button',
                            iconCls: 'finascop_add',
                            id: 'approval_add_particular',
                            style: 'margin-left:5px',
                            handler: function () {
                                updateParticulars();
                            }
                        }],
                    bbar: ['->', {html: 'Total : &nbsp;'}, {
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            readOnly: true,
                            value: 0,
                            id: 'approval_receipt_total_amount',
                            style: 'text-align: right'
                        }],
                    stripeRows: true,
                    autoExpandColumn: 'approval_particular_auto_exp'
                }), new Ext.Panel({
                    layout: 'form',
                    labelAlign: 'top',
                    region: 'south',
                    id: 'id_narration',
                    border: false,
                    autoHeight: true,
                    hideBorders: true,
                    items: [{
                            xtype: 'textarea',
                            labelStyle: 'margin-left:15px;',
                            fieldLabel: 'Narration',
                            id: 'approval_receipt_narration',
                            name: 'approval_receipt_narration',
                            anchor: '98%',
                            maxLength: 500,
                            style: 'margin-left:15px;'
                        }],
                    keys: [
                        {key: [Ext.EventObject.ENTER], handler: function (key, event) {
                                var elem = event.getTarget();
                                var component = Ext.getCmp(elem.id);
                                if (component instanceof Ext.form.TextArea) {
                                    Ext.Msg.show({
                                        title: 'Confirm',
                                        msg: "Do you wants to save the entry?",
                                        buttons: Ext.MessageBox.YESNO,
                                        fn: function (btn) {
                                            if (btn != 'no') {
                                                Ext.getCmp('approval_save_btn').focus();
                                            } else {
                                                Ext.getCmp('approval_receipt_narration').focus();
                                            }

                                        }
                                    });
                                }

                            }
                        }
                    ]
                })
            ]
        });
    };

    var updateParticulars = function () {
        var particular = Ext.getCmp('approval_receipt_particular').getValue();
        var particular_nm = Ext.getCmp('approval_receipt_particular').getRawValue();
        var amount = Ext.getCmp('approval_receipt_amount').getValue();


        if (!Ext.isEmpty(particular) && !Ext.isEmpty(amount)) {
            if (Application.Finascop_Approval.account_type == 'Journal Voucher' && Ext.getCmp('approval_account_type1').getValue() == false
                    && Ext.getCmp('approval_account_type2').getValue() == false) {
                Ext.Msg.alert("Notification", 'Please select Debtor/Creditor.');
                return false;
            }

            var grid = Ext.getCmp('approval_particular_grid');
            var grid_store = grid.getStore();
            var exist = grid_store.find('particular_id', particular);
            if (exist == -1) {
                var row = new Ext.data.Record.create({
                    name: 'particular_name',
                    name: 'particular_id',
                            name: 'amount'
                });
                var r = new row({
                    'particular_id': particular,
                    'particular_name': particular_nm,
                    'amount': amount
                });
                grid_store.add([r]);
                Ext.getCmp('approval_receipt_total_amount').setValue(Ext.util.Format.round(grid_store.sum('amount'), 2));

                /*if (Application.Finascop_Approval.account_type == 'Journal Voucher') {
                 Ext.getCmp('approval_ctr_dtr_type').disable();
                 }*/

                Ext.getCmp('approval_receipt_particular').reset();
                Ext.getCmp('approval_receipt_amount').reset();
                Ext.getCmp('approval_receipt_particular').focus();
            } else {
                Ext.Msg.alert("Notification", 'Particular already exists.');
            }
        } else {
            Ext.Msg.alert("Notification", 'Please fill Particular and Amount.', function (btn) {
                Ext.getCmp('approval_receipt_particular').focus();
            });

        }
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
                    this.baseParams.type = Application.Finascop_Approval.account_type;
                    this.baseParams.company_id = Application.Finascop_Approval.company_id;
                    this.baseParams.branch_id = Application.Finascop_Approval.branch_id;
                    this.baseParams.referedfrom = 'Approval';
                }
            }



        });
    };

    var particularComboStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: false,
            url: '?module=finascop_data_entry&op=getParticulars',
            method: 'post',
            fields: ['accled_LedgerName', 'accled_Ledger_Id', 'GroupName', 'ledgertypename', 'ledgertypeid'],
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.type = Application.Finascop_Approval.account_type;
                    this.baseParams.company_id = Application.Finascop_Approval.company_id;
                    this.baseParams.branch_id = Application.Finascop_Approval.branch_id;
                    this.baseParams.referedfrom = 'Approval';
                }
            }
        });
        return store;
    };

    var detailsTab = function () {
        var tab = new Ext.TabPanel({
            activeTab: 0,
            border: false,
            id: 'approval_details_tab',
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
                            id: 'approval_image_panel',
                            tpl: new Ext.XTemplate('<div class="details-outer">',
                                    '<img src="{img_src}"></img>',
                                    '</div>')
                        })]
                }],
            listeners: {
                tabchange: function (panel, currentTab) {
                    var activeTab = panel.getActiveTab();
                    var ind = panel.items.findIndex('id', activeTab.id);
                    if (ind == 1 && !Ext.isEmpty(Application.Finascop_Approval.ImageUrl))
                        Ext.getCmp('approval_image_panel').update({'img_src': Application.Finascop_Approval.ImageUrl});
                }
            }
        });
        return tab;
    };

    var changeButtonDisplay = function (t, stat, SourceOfEntry, IsOtherAssignedUser) {
        if (stat == 1 || stat == 2) {
            t = 2;

        } else if (stat == 3 || stat == 4) {
            t = 1;
        }
        if (SourceOfEntry != 0) {
            t = 1;
        }
        if (IsOtherAssignedUser == 1) {
            t = 1;
        }


        var ids = ['approval_approve_btn', 'approval_escalate_btn', 'approval_rebut_btn',
            'approval_delete_btn', 'approval_add_particular', 'approval_edit_btn'];

        Ext.each(ids, function (u) {
            if (t == 1) {
                Ext.getCmp(u).hide();
            } else if (t == 2) {
                Ext.getCmp(u).show();
            }
        });
        Ext.getCmp('approval_rollback_btn').hide();
        if ((stat == 3 || stat == 4) && (SourceOfEntry == 0)) {
            Ext.getCmp('approval_rollback_btn').show();
        }

        Ext.getCmp('approval_save_btn').hide();
        Ext.getCmp('approval_back_btn').hide();
        if (t == 1) {
            Ext.getCmp('approval_particular_grid').getColumnModel().setHidden(3, true);
        } else {
            Ext.getCmp('approval_particular_grid').getColumnModel().setHidden(3, false);
        }
        Ext.getCmp('approval_parent_panel').doLayout();

    };

    var clearRightPane = function () {

        var ids = ['approval_approve_btn', 'approval_escalate_btn', 'approval_rebut_btn',
            'approval_delete_btn', 'approval_rollback_btn', 'approval_add_particular', 'approval_edit_btn', 'approval_back_btn', 'approval_save_btn'];

        Ext.each(ids, function (u) {
            Ext.getCmp(u).hide();
        }
        );
        Ext.getCmp('id_details').disable();
        Ext.getCmp('approval_particular_grid').disable();
        Ext.getCmp('approval_receipt_narration').setValue("");
        Ext.getCmp('id_narration').disable();
        Ext.getCmp('approval_particular_grid').getStore().removeAll();
        Ext.getCmp('approval_receipt_date').setValue('');
        Ext.getCmp('approval_receipt_account').getStore().removeAll();
        Ext.getCmp('approval_receipt_account').setValue('');
        Ext.getCmp('approval_parent_panel').setTitle('');
        Ext.getCmp('approval_parent_panel').collapse(true);
        Ext.getCmp('approval_parent_panel').doLayout();
        Ext.getCmp('approval_receipt_total_amount').setValue('');


    };

    var approvalMasterPanel = function (id) {

        var panel = new Ext.Panel({
            frame: false,
            layout: 'border',
            bodyStyle: {"background-color": "white", "padding": "5px 3px"},
            border: false,
            id: id,
            title: 'Approval',
            //iconCls: 'finascop_approval',
            items: [approvalGrid(),
                new Ext.Panel({
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    id: 'approval_parent_panel',
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
                    },
                    buttons: [{
                            text: 'Edit',
                            icon: IMAGE_BASE_PATH + "/default/icons/finascop_edit.png",
                            id: 'approval_edit_btn',
                            hidden: true,
                            handler: function () {
                                Ext.getCmp('approval_approve_btn').hide();
                                Ext.getCmp('approval_escalate_btn').hide();
                                Ext.getCmp('approval_rebut_btn').hide();
                                Ext.getCmp('approval_delete_btn').hide();
                                Ext.getCmp('approval_rollback_btn').hide();
                                Ext.getCmp('id_details').enable();
                                Ext.getCmp('approval_particular_grid').enable();
                                Ext.getCmp('id_narration').enable();
                                Ext.getCmp('approval_edit_btn').hide();
                                Ext.getCmp('approval_save_btn').show();
                                Ext.getCmp('approval_back_btn').show();
                                Ext.getCmp('approval_receipt_date').focus(false, 200);
                            }
                        }, {
                            text: 'Save',
                            hidden: true,
                            id: 'approval_save_btn',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                            iconCls: 'finascop_my-icon1',
                            handler: function () {

                                var grid = Ext.getCmp('approval_particular_grid');
                                var grid_store = grid.getStore();
                                var data = Ext.pluck(grid_store.getRange(), 'data');
                                Ext.MessageBox.alert('Error', 'Reached here');
                                if (!Ext.isEmpty(data) && !Ext.isEmpty(Ext.getCmp('approval_receipt_date').getRawValue()) &&
                                        !Ext.isEmpty(Ext.getCmp('approval_receipt_account').getValue())) {

                                    var form_data = {
                                        particular_data: data,
                                        receipt_date: Ext.getCmp('approval_receipt_date').getRawValue(),
                                        receipt_account: Ext.getCmp('approval_receipt_account').getValue(),
                                        receipt_account_name: Ext.getCmp('approval_receipt_account').getRawValue(),
                                        ledger_type: Ext.getCmp('approval_ledger_type_selected').getValue(),
                                        total_amount: Ext.getCmp('approval_receipt_total_amount').getValue(),
                                        narration: Ext.getCmp('approval_receipt_narration').getValue(),
                                        type: Application.Finascop_Approval.account_type,
                                        ctr_dtr_type: Ext.getCmp('approval_ctr_dtr_type').getValue(),
                                        acet_NO: Application.Finascop_Approval.acet_NO,
                                        acet_DocNO: Application.Finascop_Approval.acet_DocNO,
                                    };
                                    var params = {
                                        action: (Application.Finascop_Approval.acet_NO == '', 'Insert', 'Update'),
                                        module: 'approving',
                                        op: 'saveParticularData',
                                        id: Application.Finascop_Approval.acet_NO,
                                        extrainfo: 'asd'
                                    };
                                    APICall(params, Application.Finascop_Approval.saveData, form_data);
                                } else {
                                    Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                                }
                            }
                        }, {
                            text: 'Exit',
                            icon: IMAGE_BASE_PATH + "/default/icons/finascop_action_stop.gif",
                            id: 'approval_back_btn',
                            hidden: true,
                            handler: function () {
                                Ext.getCmp('approval_main_panel').getStore().load();
                                clearRightPane();
                            }
                        }, {
                            text: 'Approve',
                            icon: IMAGE_BASE_PATH + "/default/icons/finascop_approve.png",
                            id: 'approval_approve_btn',
                            hidden: true,
                            handler: function () {
                                Application.Finascop_Approval.update_type = 3;
                                var particulars_store = Ext.getCmp('approval_particular_grid').getStore();
                                var hasRecords = particulars_store.getTotalCount() > 0;
                                if (hasRecords) {
                                    updateTransactionStatus(Application.Finascop_Approval.acet_NO, 3, Application.Finascop_Approval.updated_on);
                                    clearRightPane();
                                } else {
                                    Ext.MessageBox.alert('Error', "FINASCOP:To approve the entry it, must have at leaset one particulars entry.", function (btn) {

                                    });
                                }
                            }
                        }, {
                            text: 'Escalate',
                            id: 'approval_escalate_btn',
                            hidden: true,
                            icon: IMAGE_BASE_PATH + "/default/icons/finascop_arrow_up.png",
                            handler: function () {

                                var comment_window = new Ext.Window({
                                    id: 'comment_window',
                                    title: '',
                                    icon: IMAGE_BASE_PATH + "/default/icons/finascop_restart.png",
                                    modal: true,
                                    layout: 'fit',
                                    width: 500,
                                    height: 125,
                                    shadow: false,
                                    resizable: false,
                                    closable: false,
                                    items: new Ext.form.FormPanel({
                                        autoHeight: true,
                                        autoWidth: true,
                                        frame: true,
                                        border: false,
                                        id: 'comment_form',
                                        items: [{
                                                columnWidth: 1,
                                                xtype: 'textarea',
                                                height: 50,
                                                width: 300,
                                                name: 'commentField',
                                                id: 'commentField',
                                                fieldLabel: 'Comment'
                                            }],
                                        buttons: [
                                            {
                                                xtype: 'button',
                                                text: 'OK',
                                                iconCls: 'finascop_ok',
                                                handler: function () {
                                                    var comment = Ext.getCmp('commentField').getValue();
                                                    if (!Ext.isEmpty(comment)) {
                                                        Application.Finascop_Approval.update_type = 2;
                                                        updateTransactionStatus(Application.Finascop_Approval.acet_NO, 2, Application.Finascop_Approval.updated_on, comment);
                                                        //updateStatus(comment);
                                                        comment_window.close();
                                                    } else {
                                                        Ext.Msg.alert("Notification", "Enter the reason");
                                                    }
                                                }
                                            },
                                            {
                                                xtype: 'button',
                                                text: 'Cancel',
                                                icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                                                handler: function () {
                                                    Ext.Msg.hide();
                                                    comment_window.close();
                                                }
                                            }
                                        ]
                                    }),
                                });
                                comment_window.show();
                                comment_window.doLayout();
                                comment_window.center();


                            }
                        }, {
                            text: 'Rebut',
                            id: 'approval_rebut_btn',
                            hidden: true,
                            icon: IMAGE_BASE_PATH + "/default/icons/finascop_restart.png",
                            handler: function () {
                                var comment_window = new Ext.Window({
                                    id: 'comment_window',
                                    title: '',
                                    icon: IMAGE_BASE_PATH + "/default/icons/finascop_restart.png",
                                    modal: true,
                                    layout: 'fit',
                                    width: 500,
                                    height: 125,
                                    shadow: false,
                                    resizable: false,
                                    closable: false,
                                    items: new Ext.form.FormPanel({
                                        autoHeight: true,
                                        autoWidth: true,
                                        frame: true,
                                        border: false,
                                        id: 'comment_form',
                                        items: [{
                                                columnWidth: 1,
                                                xtype: 'textarea',
                                                height: 50,
                                                width: 300,
                                                name: 'commentField',
                                                id: 'commentField',
                                                fieldLabel: 'Comment'
                                            }],
                                        buttons: [
                                            {
                                                xtype: 'button',
                                                text: 'OK',
                                                iconCls: 'finascop_ok',
                                                handler: function () {
                                                    var comment = Ext.getCmp('commentField').getValue();
                                                    if (!Ext.isEmpty(comment)) {
                                                        Application.Finascop_Approval.update_type = 5;
                                                        updateTransactionStatus(Application.Finascop_Approval.acet_NO, 5, Application.Finascop_Approval.updated_on, comment);
                                                        //updateStatus(comment);
                                                        comment_window.close();
                                                    } else {
                                                        Ext.Msg.alert("Notification", "Enter the reason");
                                                    }
                                                }
                                            },
                                            {
                                                xtype: 'button',
                                                text: 'Cancel',
                                                icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                                                handler: function () {
                                                    Ext.Msg.hide();
                                                    comment_window.close();
                                                }
                                            }
                                        ]
                                    }),
                                });
                                comment_window.show();
                                comment_window.doLayout();
                                comment_window.center();




                            }
                        }, {
                            text: 'Delete', id: 'approval_delete_btn',
                            hidden: true,
                            icon: IMAGE_BASE_PATH + "/default/icons/finascop_cancel.png",
                            handler: function () {
                                Application.Finascop_Approval.update_type = 4;
                                updateTransactionStatus(Application.Finascop_Approval.acet_NO, 4, Application.Finascop_Approval.updated_on);
                            }
                        }, {
                            text: 'Roll Back',
                            id: 'approval_rollback_btn',
                            hidden: true,
                            icon: IMAGE_BASE_PATH + "/default/icons/finascop_revert.png",
                            handler: function () {
                                Application.Finascop_Approval.update_type = 6;
                                updateTransactionStatus(Application.Finascop_Approval.acet_NO, 6, Application.Finascop_Approval.updated_on);
                            }
                        }]
                })],
            listeners: {afterrender: function () {
                    winLoadMask = new Ext.LoadMask(Ext.getCmp(id).getEl());
                }}
        });
        return panel;
    };

    return {initApproval: function () {
            /*var panelId = 'approval_master_panel';
             var approval_main = Ext.getCmp(panelId);
             if (Ext.isEmpty(approval_main)) {
             approval_main = approvalMasterPanel(panelId);
             approval_main.doLayout();
             return approval_main;
             } else {
             approval_main.doLayout();
             }
             */
            Application.Finascop_Approval.store_add_edit = '';
            var panelId = 'approval_master_panel';
            var approval_master_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(approval_master_panel)) {
                approval_master_panel = approvalMasterPanel(panelId);
                Application.UI.addTab(approval_master_panel);
                approval_master_panel.doLayout();
            } else {
                Application.UI.addTab(approval_master_panel);
            }


        },
        ApproverMainPanel: function () {
            return [{
                    region: 'center',
                    border: false,
                    layout: 'fit',
                    items: Application.Finascop_Approval.initApproval()
                }];
        },
        account_processing: function (comment) {

            var type = Application.Finascop_Approval.update_type;
            // var updateStatus = function (comment) {
            Ext.Ajax.request({
                url: modURL + '&op=updateStatus',
                method: 'POST',
                params: {
                    acet_NO: Application.Finascop_Approval.acet_NO,
                    type: Application.Finascop_Approval.update_type,
                    reason: comment,
                    updated_on: Application.Finascop_Approval.updated_on

                },
                success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true) {
                        if (type == 1) {
                            if (res.takenover == true) {
                                Ext.Msg.hide();
                                Application.Finascop_Approval.updated_on = res.updated_on;
                                Ext.getCmp('approval_escalate_btn').show();
                                Ext.getCmp('approval_main_panel').getStore().load();
                                if (Application.Finascop_Approval.selectedrecord !== undefined) {
                                    Application.Finascop_Approval.selectedrecord.set('acet_Status', 1);
                                    Application.Finascop_Approval.selectedrecord.set('IsOtherAssignedUser', 0);
                                    Application.Finascop_Approval.selectedrecord.set('updated_on', Application.Finascop_Approval.updated_on);
                                }
                            }
                        } else {
                            var msg = (!Ext.isEmpty(res.msg)) ? res.msg : "Status has been changed successfully.";
                            Ext.Msg.alert("Notification", msg, function (btn) {
                                Ext.getCmp('approval_main_panel').getStore().load();
                                Ext.Msg.hide();
                                clearRightPane();

                            });
                        }
                        Ext.Msg.hide();
                        //Ext.getCmp('comment_window').close();
                    } else {
                        var msg = res.msg;
                        Ext.Msg.alert("Notification", msg, function (btn) {
                            Ext.getCmp('approval_main_panel').getStore().load();
                            Ext.Msg.hide();
                            clearRightPane();
                            //Ext.getCmp('comment_window').close();

                        });
                    }
                },
                failure: function (elm, conf) {
                    Ext.Msg.alert("Error", "Error occured while saving data.", function (btn) {
                        Ext.getCmp('approval_main_panel').getStore().load();
                        Ext.Msg.hide();
                        clearRightPane();

                    });
                }
            });
        },
        saveData: function () {

            var grid = Ext.getCmp('approval_particular_grid');
            var grid_store = grid.getStore();
            var data = Ext.pluck(grid_store.getRange(), 'data');
            Ext.Ajax.request({
                url: modURL + '&op=saveParticularData',
                method: 'POST',
                params: {
                    "particular_data": Ext.encode(data),
                    "receipt_date": Ext.getCmp('approval_receipt_date').getRawValue(),
                    "receipt_account": Ext.getCmp('approval_receipt_account').getValue(),
                    "receipt_account_name": Ext.getCmp('approval_receipt_account').getRawValue(),
                    "ledger_type": Ext.getCmp('approval_ledger_type_selected').getValue(),
                    "total_amount": Ext.getCmp('approval_receipt_total_amount').getValue(),
                    "narration": Ext.getCmp('approval_receipt_narration').getValue(),
                    "type": Application.Finascop_Approval.account_type,
                    "ctr_dtr_type": Ext.getCmp('approval_ctr_dtr_type').getValue(),
                    acet_NO: Application.Finascop_Approval.acet_NO,
                    acet_DocNO: Application.Finascop_Approval.acet_DocNO,
                    "updated_on": Application.Finascop_Approval.updated_on
                },
                success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true) {
                        Ext.Msg.alert('Success', 'Details has been saved successfully.', function (btn) {

                            console.log('BUTTON->' + btn);

                            Ext.getCmp('approval_main_panel').getStore().load();

                            console.log('BUTTON->' + btn);
                            Ext.getCmp('approval_main_panel').getStore().load();
                            Ext.Msg.hide();

                            clearRightPane();
                        });
                    } else {
                        Ext.MessageBox.alert('Error', res.msg, function (btn) {


                            Ext.getCmp('approval_main_panel').getStore().load();


                            Ext.getCmp('approval_main_panel').getStore().load();
                            Ext.Msg.hide();

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
                    Ext.Msg.hide();
                }
            });
        }
    };
}();		