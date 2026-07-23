
Application.Finascop_Ledger = function () {

///////////////////////////////////////////////////////////////////////////
//-----------------------------------------------------------------------//
//-----------------------------Private Area------------------------------//
//-----------------------------------------------------------------------//
//---------------------------Private Variables---------------------------//
//Configure the Number of records can display in Grid at a time.

    var genledgebalancelastParameters;
    var recs_per_page = 12;
    var modURL = '?module=finascop_ledger';
    var date_diff = BETWEEN_DATES_TYPE_A;
    //reads a data object from an Ext.data.Connection object configured to reference a certain URL.
    var proxy = new Ext.data.HttpProxy({
        url: modURL,
        method: 'post'
    });
    var winsize = Ext.getBody().getViewSize();
    //for pagination   
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    /** 
     * var branch_store = Store for branches grid
     *   getBranches contains data for branches grid 
     **/

    var branch_store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getBranches',
            fields: ['br_ID', 'br_Name', 'branch_shortname', 'isChecked', 'accled_IsEnabled','brsnnam',
                'accled_Ledger_Id', 'accled_Ledger_Id_true'],
            totalProperty: 'totalCount',
            root: 'branches',
            id: 'br_ID',
            remoteSort: true
        });
        store.baseParams.company = _SESSION.finascop_current_company_id;
        store.baseParams.lb = 1;
        return store;
    };
    var ledgerbranch_store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getLedgerBranches',
            fields: ['br_ID', 'br_Name', 'branch_shortname', 'isChecked', 'accled_IsEnabled',
                'accled_Ledger_Id', 'accled_Ledger_Id_true'],
            totalProperty: 'totalCount',
            root: 'branches',
            id: 'br_ID',
            remoteSort: true
        });
        store.baseParams.company = _SESSION.finascop_current_company_id;
        store.baseParams.lb = 1;
        return store;
    };
    var ledger_combo_store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getLedgerList',
            fields: ['accled_Ledger_Id', 'accled_LedgerName'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'br_ID',
            remoteSort: true
        });
        return store;
    };
    var groupStore = function () {
        var group_store = new Ext.data.JsonStore({
            url: modURL + '&op=listGroups',
            fields: ['id', 'SlNo', 'group_name', 'nature_of_group', {name: 'System', type: 'number'}, 'group_ReferenceId'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'group_store',
            autoLoad: true,
            //    remoteSort: true,
            listeners: {
                beforeload: function () {
                    this.baseParams.limit = recs_per_page;
                }
            }
        });

        return group_store;
    };
    /** 
     * var group_store = Store for group combo
     * getGroups contains data for group combo 
     **/
    var group_store = new Ext.data.Store({
        proxy: proxy,
        baseParams: {
            op: 'getGroups'
        },
        reader: new Ext.data.ArrayReader({}, ['Group_ID', 'GroupName'])
    });
    //var ledger_store = Store for 'Ledger Grid' , getLedgers contains the data for ledger grid 
    var ledger_store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: proxy,
            baseParams: {
                op: 'getLedgers'
            },
            fields: ['ledgertypeid', 'Group_ID', 'ledgertypename', 'GroupName', {name: 'isSystem', type: 'number'}, 'accled_Comp',
                'accled_Comp_id', 'accled_LedgerName', 'ledger_ReferenceId'],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'ledgertypeid',
            remoteSort: true,
            autoLoad: false
        });
        return store;
    };
    var ledger_popup_store = function (acet_NO) {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: proxy,
            baseParams: {
                op: 'listLedgers',
                acet_NO: acet_NO
            },
            fields: ['ledger_name', {name: 'dr', type: 'float'}, {name: 'cr', type: 'float'}],
            totalProperty: 'totalCount',
            root: 'data',
            localSort: true,
            autoLoad: true
        });
        return store;
    };
    //var filters = Plugin used for the 'ledgerGrid'    
    var filters = new Ext.ux.grid.GridFilters({
        filters: [{
                type: 'string',
                dataIndex: 'ledgertypename'
            }, {
                type: 'string',
                dataIndex: 'ledger_ReferenceId'
            },{
                type: 'string',
                dataIndex: 'GroupName'
            }]
    });
    filters.remote = true;
    filters.autoReload = true;

    var showLedgerRefID = function (ledgerRefID) {
        var win_id = "ledger_id_window";

        var ledger_id_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(ledger_id_window)) {
            ledger_id_window = new Ext.Window({
                id: win_id,
                title: 'View Ledger Refernce ID',
                layout: 'fit',
                modal: true,
                width: 300,
                items: [{
                        xtype: 'textfield',
                        value: ledgerRefID,
                        anchor: '98%',
                        fieldLabel: 'Ledger Ref ID'
                    }]

            });
            ledger_id_window.doLayout();
            ledger_id_window.show(this);
            ledger_id_window.center();
        }
    };

    var showGroupRefID = function (groupRefID) {
        var win_id = "group_id_window";
        console.log(groupRefID);
        var group_id_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(group_id_window)) {
            group_id_window = new Ext.Window({
                id: win_id,
                title: 'View Group Refernce ID',
                layout: 'fit',
                modal: true,
                width: 300,
                items: [{
                        xtype: 'textfield',
                        value: groupRefID,
                        anchor: '98%',
                        fieldLabel: 'Group Ref ID'
                    }]

            });
            group_id_window.doLayout();
            group_id_window.show(this);
            group_id_window.center();
        }
    };

    //create a Ledger grid to display all  Ledger,debtor and creditor    
    var ledgerListGrid = function () {
        var ledgerStore = ledger_store();
        var ledgerGrid = new Ext.grid.GridPanel({
            store: ledgerStore,
            layout: 'fit',
            plugins: [filters],
            title: 'Ledger',
            //iconCls: 'finascop_icon_ledger',
            id: 'gridLedgers',
            loadMask: true,
            columns: [{
                    header: 'Ledger',
                    sortable: true,
                    dataIndex: 'ledgertypename',
                    id: 'id_ledgerName',
                    width: 30
                }, {
                    header: 'Group.',
                    sortable: true,
                    dataIndex: 'GroupName',
                    width: 25

                }, {
                    header: 'Ledger Reference ID',
                    sortable: true,
                    width: 45,
                    dataIndex: 'ledger_ReferenceId',
                    tooltip: 'Reference ID'
                }, {
                    header: 'Company',
                    sortable: true,
                    dataIndex: 'accled_Comp',
                    width: 25

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
                            var isSystem = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.isSystem;
                            var ledger_ReferenceId = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.ledger_ReferenceId;
                            fledgerActionMenu(e,isSystem,ledger_ReferenceId);
                               
                            
                            //action
                        }
                    }
                }
                /*{
                    header: "Action",
                    xtype: 'actioncolumn',
                    width: 20,
                    items: [{
                            tooltip: 'Edit Ledger',
                            getClass: function (v, meta, rec) {
                                if (rec.get('isSystem') == 1)
                                    return 'finascop_hideicon';
                                else
                                    return 'finascop_edit';
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var rec = grid.store.getAt(rowIndex);
                                var ledger_id = rec.get('ledgertypeid');
                                var group_id = rec.get('Group_ID');
                                var ledger_name = rec.get('ledgertypename');
                                var group_name = rec.get('GroupName');
                                var accled_Comp = rec.get('accled_Comp');
                                var accled_Comp_id = rec.get('accled_Comp_id');
                                Application.Finascop_Ledger.addLedger(ledger_id, group_id, ledger_name, group_name, accled_Comp, accled_Comp_id);
                            }
                        }, {
                            tooltip: 'Show Ledger Reference ID',
                            getClass: function (v, meta, rec) {
                                if (Ext.isEmpty(rec.get('ledger_ReferenceId')))
                                    return 'finascop_hideicon';
                                else
                                    return 'finascop_viewfile';
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                showLedgerRefID(record.get('ledger_ReferenceId'));
                            }
                        }]
                }*/
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                getRowClass: function (record, index) {
                    //console.log('isSystem:'+record.get('isSystem'));
                    if (record.get('isSystem') == 1)/*System genereated Group */
                    {
                        return 'finascop_indicateCYAN';
                    } else {
                        return '';
                    }
                }
            },
            tbar: [{
                    text: 'Create Ledger',
                    id: 'ledger_add_button',
                    tooltip: 'Create Ledger',
                    icon: IMAGE_BASE_PATH + "/default/icons/finascop_add.png",
                    iconCls: 'finascop_my-icon1',
                    handler: function () {
                        Application.Finascop_Ledger.addLedger(0);
                        Ext.getCmp('ldg_company').setValue(_SESSION.finascop_current_company_id);
                        Ext.getCmp('ldg_company').setRawValue(_SESSION.finascop_current_company);
                        Ext.getCmp('ldg_company').setReadOnly(true);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: ledgerStore,
                plugins: [filters],
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            listeners: {
                viewready: updatePagination
            },
            stripeRows: true,
            autoExpandColumn: 'id_ledgerName'

        });
        /*var fledgerActionMenu1 = new Ext.menu.Menu({
        items: [{
                text: "Reference ID",
                handler: function () {
                                var ledger_ReferenceId = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.ledger_ReferenceId;
                                showLedgerRefID(ledger_ReferenceId);
                            }
            }]
    });*/
    
    
        
        //Ext.util.Observable.capture(ledgerGrid, function (evname) {
//            console.log(evname, arguments);
//        })
        return ledgerGrid;
    };
    var fledgerActionMenu= function(e,isSystem,ledger_ReferenceId){    
    var fledgerActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                hidden: (isSystem == 1),
                handler: function () {
                                var ledger_id = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.ledgertypeid;
                                var group_id = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.Group_ID;
                                var ledger_name = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.ledgertypename;
                                var group_name = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.GroupName;
                                var accled_Comp = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.accled_Comp;
                                var accled_Comp_id = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.accled_Comp_id;
                                Application.Finascop_Ledger.addLedger(ledger_id, group_id, ledger_name, group_name, accled_Comp_id, accled_Comp); 
                                //console.log('accled_Comp_id:' + accled_Comp_id);
                            }
            }, 
            {
                text: "Reference ID",
                hidden: (Ext.isEmpty(ledger_ReferenceId))? true : false,
                handler: function () {
                                var ledger_ReferenceId = Ext.getCmp('gridLedgers').getSelectionModel().getSelections()[0].data.ledger_ReferenceId;
                                showLedgerRefID(ledger_ReferenceId);
                            }
            }]
    });
     fledgerActionMenu.showAt(e.getXY());
    };

    var createBranchGrid = function (ledger_id, group_id, ledger_name, group_name) {
        //grid selection model,for selecting multi rows in a grid   
        var sm = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true
        });
        //create a grid to display all branches   
        var bGrid = new Ext.grid.GridPanel({
            store: ledgerbranch_store(),
            id: 'id_branch_grid',
            height: 300,
            sm: sm,
            loadMask: true,
            cm: new Ext.grid.ColumnModel([sm, {
                    id: 'id_branch_name',
                    header: 'Branches',
                    dataIndex: 'br_Name'
                }]),
            viewConfig: {
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true,
            autoExpandColumn: 'id_branch_name'
        });
        return bGrid;
    };

    var company_store = function () {
        var store = new Ext.data.Store({
            proxy: proxy,
            baseParams: {
                op: 'getCompany'
            },
            reader: new Ext.data.ArrayReader({}, ['comp_id', 'comp_name'])
        });
        return store;
    };

    var createLedgerForm = function (ledger_id, group_id, ledger_name, group_name, comp_id, comp_name) {

        var branch_grid = createBranchGrid(ledger_id, group_id, ledger_name, group_name);
        // create a form to add/edit Ledger       
        var lFrm = new Ext.form.FormPanel({
            frame: true,
            id: 'id_tEditForm',
            labelAlign: 'top',
            url: modURL + '&op=saveLedger',
            width: 600,
            autoHeight: true,
            defaults: {
                anchor: '99%'
            },
            items: [{
                    xtype: 'hidden',
                    id: 'id_ledgerId',
                    name: 'ledger[ledgertypeid]',
                    value: ledger_id
                }, {
                    xtype: 'hidden',
                    id: 'branches_grid',
                    name: 'branches'
                }, {
                    xtype: 'textfield',
                    id: 'id_ledgerName',
                    style: 'text-transform: uppercase',
                    fieldLabel: 'Ledger Name',
                    value: ledger_name,
                    name: 'ledger[ledgertypename]',
                    allowBlank: false,
                    vtype: 'custdataStringspec',
                    listeners: {
                        change: function (field, newValue) {
                            field.setValue(newValue.toUpperCase());
                        },
                        afterrender: function (field) {
                            Ext.defer(function () {
                                field.focus(true, 100);
                            }, 1);
                        }
                    }
                }, {
                    xtype: 'combo',
                    triggerAction: 'all',
                    id: 'id_ledgerGroup',
                    name: 'ledgertypegroupid',
                    hiddenName: 'ledger[Group_ID]',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    minChars: 0,
                    displayField: 'GroupName',
                    valueField: 'Group_ID',
                    allowBlank: false,
                    fieldLabel: 'Select Group',
                    store: group_store,
                    listeners: {
                        afterrender: function () {
                            this.setValue(group_id);
                            this.setRawValue(group_name);
                        }
                    }
                }, {
                    xtype: 'combo',
                    triggerAction: 'all',
                    allowBlank: false,
                    id: 'ldg_company',
                    fieldLabel: 'Ledger Company',
                    hiddenName: 'ldg_company',
                    forceSelection: true,
                    editable: true,
                    typeAhead: true,
                    minChars: 1,
                    displayField: 'comp_name',
                    valueField: 'comp_id',
                    store: company_store(),
                    listeners: {
                        afterrender: function () {
                            //this.setValue(comp_id);
                            //this.setRawValue(comp_name);
                            this.setValue(_SESSION.finascop_current_company_id);
                            this.setRawValue(_SESSION.finascop_current_company);
                            this.setReadOnly(true);
                        },
                        select: function (cbo) {
                            var company = this.getValue();
                            var grid = Ext.getCmp('id_branch_grid');
                            grid.getSelectionModel().clearSelections();
                            grid.getStore().load({
                                params: {
                                    company: company
                                },
                                callback: function (res) {
                                    var items = Ext.getCmp('id_branch_grid').getStore().data.items;
                                    var sm = Ext.getCmp('id_branch_grid').getSelectionModel();
                                    var count = items.length;
                                    for (var i = 0; i < count; i++) {
                                        if (items[i].data.isChecked == "1")
                                            sm.selectRow(i, true);
                                    }
                                }
                            });
                        }
                    }
                }, branch_grid]
        });
        return lFrm;
    };

    var ledgerbalancestore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listLedgerDetails',
                method: 'post'
            }),
            listeners: {
                load: function (a, b, options) {
                    genledgebalancelastParameters = options.params;
                    var cr = 0, dr = 0, str = store.getRange();
                    Ext.each(str, function (i) {
                        dr = dr + parseFloat((!i.get('dr') ? '0' : i.get('dr')));
                        cr = cr + parseFloat((!i.get('cr') ? '0' : i.get('cr')));
                    });
                    dr = dr + parseFloat((Ext.getCmp('ldg_dr').getValue() == '' ? '0' : Ext.getCmp('ldg_dr').getValue()));
                    cr = cr + parseFloat((Ext.getCmp('ldg_cr').getValue() == '' ? '0' : Ext.getCmp('ldg_cr').getValue()));
                    if (cr > dr) {
                        Ext.getCmp('ldg_cr1').setValue(formatNumber(cr - dr, FINASCOP_CURRENCY_FORMAT));
                        Ext.getCmp('ldg_dr1').setValue("");
                    } else if (dr > cr) {
                        Ext.getCmp('ldg_cr1').setValue("");
                        Ext.getCmp('ldg_dr1').setValue(formatNumber(dr - cr, FINASCOP_CURRENCY_FORMAT));
                    } else {
                        Ext.getCmp('ldg_cr1').setValue("");
                        Ext.getCmp('ldg_dr1').setValue("");
                    }
                    Ext.Msg.hide();
                },
                beforeload: function () {
                    Ext.Msg.show({
                        title: 'Loading',
                        msg: 'Please wait...',
                        progressText: '',
                        width: 300,
                        progress: true,
                        closable: false,
                        wait: true
                    });
                }
            },
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: '',
                root: 'data'
            }, ['type', 'acet_NO', 'acet_DocNO', 'date', {name: 'dr', type: 'float', useNull: true}, {name: 'cr', type: 'float', useNull: true}, 'narration',
                {name: 'sl_no', type: 'int'}, {name: 'actr_IsApproved', type: 'int'}]),
            groupField: 'date',
            groupDir: 'ASC',
            root: 'data',
            localSort: true
        });
        return store;
    };
    var ledgerDetailsGrid = function () {


        Ext.ux.grid.GroupSummary.Calculations['totalCount'] = function (v, record, field) {
            return  v + ' ' + record.data.type;
        };
        var summary = new Ext.ux.grid.GroupSummary();
        var grid = new Ext.grid.GridPanel({
            id: 'ledger_details_grid',
            ds: ledgerbalancestore(),
            view: new Ext.grid.GroupingView({
                forceFit: true,
                showGroupName: false,
                enableNoGroups: false,
                enableGroupingMenu: false,
                hideGroupedColumn: false,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {

                    if (record.get('actr_IsApproved') == 0)
                    {
                        return 'finascop_unAudited';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            }),
            plugins: summary,
            frame: true,
            width: winsize.width * 0.59,
            //height: 416,
            height: 390,
            trackMouseOver: false,
            loadMask: true,
            columns: [
                /* {
                 header: 'Sl. No.',
                 width: 30,
                 sortable: true,
                 dataIndex: 'sl_no',
                 summaryType: 'max'
                 },*/
                {
                    header: 'Date',
                    width: 40,
                    sortable: true,
                    dataIndex: 'date'
                }, {
                    header: 'Payment No.',
                    width: 40,
                    sortable: true,
                    dataIndex: 'acet_DocNO'
                }, {
                    header: 'Type',
                    width: 70,
                    sortable: true,
                    dataIndex: 'type',
                    summaryType: 'count',
                    hideable: false,
                    summaryRenderer: function (v, record, field) {
                        return 'Total ' + v + ' item(s)';
                    }
                }, {
                    header: 'Dr.',
                    width: 40,
                    sortable: true,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'dr',
                    align: 'right',
                    summaryType: 'sum',
                    summaryRenderer: function (v, record, field) {
                        return (v == 0 ? '' : formatNumber(v, FINASCOP_CURRENCY_FORMAT));
                    }
                }, {
                    header: 'Cr.',
                    width: 40,
                    sortable: false,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    groupable: false,
                    dataIndex: 'cr',
                    align: 'right',
                    /*summaryType: 'totalCost',*/
                    summaryType: 'sum',
                    summaryRenderer: function (v, record, field) {
                        return (v == 0 ? '' : formatNumber(v, FINASCOP_CURRENCY_FORMAT));
                    }
                }, {
                    header: 'Narration',
                    sortable: false,
                    groupable: false,
                    dataIndex: 'narration',
                    renderer: function (value, metaData) {
                        return '<div style="white-space:normal">' + value + '</div>';
                    }
                }
            ],
            listeners: {
                rowdblclick: function (grid, row, e) {
                    var record = grid.store.getAt(row);
                    console.log(row);
                    showDetails(record.get('acet_NO'), record.get('acet_DocNO'), record.get('narration'));
                }
            }
        });
        return grid;
    };
    var showDetails = function (acet_NO, acet_DocNO, narration) {
        var win_id = "ledger_popup";
        var win = Ext.getCmp(win_id);
        if (Ext.isEmpty(win)) {


            win = new Ext.Window({
                id: win_id,
                title: 'Details : ' + acet_DocNO,
                layout: 'fit',
                width: 500,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                resizable: false,
                items: new Ext.Panel({
                    height: 360,
                    border: false,
                    layout: 'form',
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textarea',
                            id: 'ledg_narration',
                            name: 'ledg_narration',
                            readOnly: true,
                            anchor: '98%',
                            fieldLabel: 'Narration',
                            value: narration,
                            labelStyle: 'margin:5px 0 2 5px;',
                            style: 'margin:5px 0 5px 5px;'
                        }, new Ext.grid.GridPanel({
                            store: ledger_popup_store(acet_NO),
                            layout: 'fit',
                            forceFit: true,
                            id: 'ledg_details_grid',
                            height: 260,
                            loadMask: true,
                            columns: [{
                                    header: 'Ledger',
                                    dataIndex: 'ledger_name',
                                    id: 'id_ledgerName'
                                }, {
                                    header: 'Dr.',
                                    align: 'right',
                                    xtype: 'finascopcurrency',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    dataIndex: 'dr'
                                }, {
                                    header: 'Cr.',
                                    align: 'right',
                                    xtype: 'finascopcurrency',
                                    format: FINASCOP_CURRENCY_FORMAT,
                                    dataIndex: 'cr'
                                }, {
                                    header: '',
                                    hideable: false,
                                    resizable: false,
                                    dataIndex: '',
                                    menuDisabled: true,
                                    sortable: false,
                                    width: 40
                                }],
                            viewConfig: {
                                forceFit: true
                            },
                            stripeRows: true,
                            autoExpandColumn: 'id_ledgerName'
                        })]
                }),
                buttons: [{
                        text: 'Close',
                        icon: IMAGE_BASE_PATH + "/default/icons/finascop_cross.png",
                        handler: function () {
                            win.close();
                        }
                    }]
            });
        }

        win.doLayout();
        win.show(this);
        win.center();
    };
    var trial_balance_store = function (id) {

        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=trialBalanceStore',
            fields: ['GroupName', {name: 'DrAmts', type: 'float'}, {name: 'CrAmts', type: 'float'}, 'GrpID', 'IsCreditor',
                'br_Name', 'BRID', 'grpid', 'accled_LedgerName'],
            totalProperty: 'totalCount',
            root: 'data',
            localSort: true,
            autoLoad: false,
            listeners: {
                load: function () {
                    var cr = 0, dr = 0, str = store.getRange();
                    Ext.each(str, function (i) {
                        dr = dr + parseFloat((i.get('DrAmts') == '' ? '0' : i.get('DrAmts')));
                        cr = cr + parseFloat((i.get('CrAmts') == '' ? '0' : i.get('CrAmts')));
                    });
                    Ext.getCmp('total_dr_' + id).setValue(Ext.util.Format.round(dr, 2));
                    Ext.getCmp('total_cr_' + id).setValue(Ext.util.Format.round(cr, 2));
                    Ext.Msg.hide();
                },
                beforeload: function () {
                    Ext.Msg.show({
                        title: 'Loading',
                        msg: 'Please wait...',
                        progressText: '',
                        width: 300,
                        progress: true,
                        closable: false,
                        wait: true
                    });
                }
            }
        });
        return store;
    };
    var trialBalanceDetailsGrid = function () {

        var grid = new Ext.grid.GridPanel({
            store: trial_balance_store(1),
            layout: 'fit',
            forceFit: true,
            id: 'trial_details_grid_1',
            height: 463,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer({
                    header: 'Sl. No.'
                }), {
                    header: 'Group',
                    dataIndex: 'GroupName'
                }, {
                    header: 'Dr.',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'DrAmts',
                    align: 'right'
                }, {
                    header: 'Cr.',
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    dataIndex: 'CrAmts',
                    align: 'right'
                }],
            bbar: ['->', {html: '<b>Total </b> &nbsp; Dr. &nbsp;'}, {
                    xtype: 'numberfield',
                    format: FINASCOP_CURRENCY_FORMAT,
                    readOnly: true,
                    value: 0,
                    id: 'total_dr_1',
                    style: 'text-align: right',
                    listeners: {
                        afterrender: function (numfield) {
                            this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                        }}
                }, {html: ' &nbsp; Cr. &nbsp;'}, {
                    xtype: 'numberfield',
                    format: FINASCOP_CURRENCY_FORMAT,
                    readOnly: true,
                    value: 0,
                    id: 'total_cr_1',
                    style: 'text-align: right',
                    listeners: {
                        afterrender: function (numfield) {
                            this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                        }}
                }, {
                    xtype: 'button',
                    text: 'Export',
                    iconCls: '',
                    handler: function () {
                        var dlysmmry_store = Ext.getCmp('trial_details_grid_1').getStore();
                        if (dlysmmry_store.getTotalCount() <= 0) {
                            Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                                if (btn == 'no') {
                                    return;
                                }
                            });
                        }
                        var wisize = 'trialbalancereport';
                        var activeTabtitle = 'Trial_Balance_Report';
                        var indexes = [];
                        var heads = [];

                        /**
                         * @modified by Aparna Ravvendran <aparna@saturn.in>
                         * @modified On 27-Mar-2013
                         *
                         * pass total no of headers of selected grid for export excel
                         */
                        var filterData = Ext.encode(genledgebalancelastParameters);
                        for (var i = 0; i < Ext.getCmp('trial_details_grid_1').getColumnModel().getColumnCount(); i++) {
                            if (Ext.getCmp('trial_details_grid_1').getColumnModel().isHidden(i) !== true) {

                                indexes[indexes.length] = Ext.getCmp('trial_details_grid_1').getColumnModel().config[i].dataIndex;
                                heads[heads.length] = Ext.getCmp('trial_details_grid_1').getColumnModel().config[i].header;
                            }
                            var dataindexes = Ext.encode(indexes);
                            var headers = Ext.encode(heads);
                        }
                        postToUrl(modURL + '&op=gettrialBalancerepexport', {dataindexes: dataindexes, headers: headers, filterData: filterData, wisize: wisize, activeTabtitle: activeTabtitle}, 'post', 'downloadIframe');
                        //postToUrl(modURL + '&op=getsalesexport', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                    }
                }],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true,
            listeners: {
                rowdblclick: function (grid, row, e) {
                    var record = grid.store.getAt(row);
                    trialBalance_popup(2, record.get('GrpID'), Ext.getCmp('ldg_balance_company').getValue(), Ext.getCmp('ldg_balance_branch').getValue());
                }
            }
        });
        return grid;
    };

    var updateDiffGrid = function ()
    {
        var ob_store = Ext.getCmp('openingbalance_details_grid').getStore();

        var drTot = Ext.util.Format.round((ob_store.sum('DrAmts')), 2);
        var crTot = Ext.util.Format.round((ob_store.sum('CrAmts')), 2);

        var row_total = new Ext.data.Record.create([
            {name: 'DrAmts', type: 'float'},
            {name: 'CrAmts', type: 'float'}
        ]);
        var drDiff = '';
        var crDiff = '';
        var ledgerName = '';
        if (drTot > crTot) {
            ledgerName = 'Cr Less by :'
            crDiff = Ext.util.Format.number(Ext.util.Format.round(drTot - crTot, 2), '0.00');
        }
        if (crTot > drTot) {
            ledgerName = 'Dr Less by :'
            drDiff = Ext.util.Format.number(Ext.util.Format.round(crTot - drTot, 2), '0.00');
        }
        var totalsdata = new row_total({
            LedgerName: ledgerName,
            DrAmts: drDiff,
            CrAmts: crDiff
        });

        Ext.getCmp('openingbalance_diff_grid').getStore().removeAll(true);
        Ext.getCmp('openingbalance_diff_grid').getStore().add(totalsdata);
    }

    var opening_balance_diff_store = function () {

        var store = new Ext.data.JsonStore({
            fields: ['accled_Ledger_Id', 'LedgerName', {name: 'DrAmts', type: 'string'}, {name: 'CrAmts', type: 'string'}],
            totalProperty: 'totalCount',
            root: 'data'
        });
        return store;
    };

    var opening_balanceDiffGrid = function () {

        var grid = new Ext.grid.GridPanel({
            store: opening_balance_diff_store(),
            layout: 'fit',
            forceFit: true,
            hideHeaders: true,
            id: 'openingbalance_diff_grid',
            height: 50,
            viewConfig: {
                getRowClass: function (record, rowIndex, rowParams, store) {
                    return 'finascop_boldFont td';
                }
            },
            columns: [{
                    width: 30
                }, {
                    header: 'Ledger',
                    dataIndex: 'LedgerName',
                    width: 275
                }, {
                    header: 'Dr.',
                    id: 'TotDr.',
                    dataIndex: 'DrAmts',
                    width: 150,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right'
                }, {
                    header: 'Cr.',
                    id: 'TotCr.',
                    dataIndex: 'CrAmts',
                    width: 150,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right'
                }]

        });

        return grid;
    }



    var opening_balance_store = function () {

        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=getOpeningBalance',
            fields: ['accled_Ledger_Id', 'LedgerName', {name: 'DrAmts', type: 'float'}, {name: 'CrAmts', type: 'float'}],
            totalProperty: 'totalCount',
            root: 'data',
            localSort: true,
            autoLoad: false
        });
        return store;
    };


    var opening_balanceDetailsGrid = function () {
        var Opbal_filter = new Ext.ux.grid.GridFilters({
            local: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'LedgerName'
                }, {
                    type: 'numeric',
                    dataIndex: 'DrAmts'
                }, {
                    type: 'numeric',
                    dataIndex: 'CrAmts'
                }]
        });
        Opbal_filter.remote = true;
        Opbal_filter.autoReload = true;

        var grid = new Ext.grid.EditorGridPanel({
            store: opening_balance_store(),
            layout: 'fit',
            plugins: [Opbal_filter],
            forceFit: true,
            id: 'openingbalance_details_grid',
            height: 425,
            enableColumnMove: false,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            columns: [new Ext.grid.RowNumberer({
                    width: 30
                }), {
                    header: 'Ledger',
                    dataIndex: 'LedgerName',
                    width: 275
                }, {
                    header: 'Dr.',
                    id: 'Dr.',
                    dataIndex: 'DrAmts',
                    width: 150,
                    editable: true,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        currRec: [],
                        previousCrAmt: '',
                        curDrAmt: 0,
                        enableKeyEvents: true,
                        validator: function (value) {
                            return !isNaN(value);
                        },
                        listeners: {
                            keyup: function (drField, e) {
                                this.curDrAmt = drField.getValue();
                            },
                            focus: function (drField) {
                                drField.selectText();
                                this.curDrAmt = drField.getValue();
                                this.currRec = grid.getSelectionModel().getSelected();
                                this.previousCrAmt = this.currRec.get("CrAmts");
                                //this.currRec.set("CrAmts", "");
                                this.currRec.set("DrAmts", "");
                                updateDiffGrid();

                            },
                            blur: function (drField) {
                                var val = this.currRec.get("DrAmts");
                                if (Number(val) == 0)
                                {
                                    this.currRec.set("CrAmts", this.previousCrAmt);
                                } else
                                {
                                    this.currRec.set("CrAmts", "");
                                }
                                if (this.curDrAmt != 0 && Number(val) == 0)
                                {
                                    this.currRec.set("DrAmts", this.curDrAmt);

                                }
                                updateDiffGrid();
                            }
                        }
                    })

                }, {
                    header: 'Cr.',
                    id: 'Cr.',
                    dataIndex: 'CrAmts',
                    width: 150,
                    editable: true,
                    xtype: 'finascopcurrency',
                    format: FINASCOP_CURRENCY_FORMAT,
                    align: 'right',
                    editor: new Ext.form.NumberField({
                        allowBlank: false,
                        allowNegative: false,
                        currRec: [],
                        previousDrAmt: '',
                        curCrAmt: 0,
                        enableKeyEvents: true,
                        validator: function (value) {
                            return !isNaN(value);
                        },
                        listeners: {
                            keyup: function (crField, e) {
                                this.curCrAmt = crField.getValue();
                            },
                            focus: function (crField) {
                                crField.selectText();
                                this.curCrAmt = crField.getValue();
                                this.currRec = grid.getSelectionModel().getSelected();
                                this.previousDrAmt = this.currRec.get("DrAmts");
                                //this.currRec.set("DrAmts", "");
                                this.currRec.set("CrAmts", "");
                                updateDiffGrid();


                            },
                            blur: function (crField) {
                                var val = this.currRec.get("CrAmts");
                                if (Number(val) == 0)
                                {
                                    this.currRec.set("DrAmts", this.previousDrAmt);
                                } else {
                                    this.currRec.set("DrAmts", "");
                                }
                                if (this.curCrAmt != 0 && Number(val) == 0)
                                {
                                    this.currRec.set("CrAmts", this.curCrAmt);

                                }
                                updateDiffGrid();
                            }
                        }
                    })

                }]

        });


        return grid;
    };
    var WinMask;
    var tree_panel = function () {

        var tree = new Ext.tree.TreePanel({
            region: 'center',
            useArrows: true,
            autoScroll: true,
            animate: true,
            enableDD: true,
            containerScroll: true,
            border: true,
            overClass: 'x-view-over',
            itemSelector: 'div.thumb-wrap',
            dataUrl: modURL + '&op=getAccountStructure',
            id: 'account_structure_tree',
            mask: true,
            maskDisabled: false,
            maskConfig: {msg: "Loading items..."},
            root: {
                nodeType: 'async',
                text: 'Account Tree',
                draggable: false,
                id: 'root'
            },
            listeners: {
                load: function () {
                    setTimeout(function () {
                        if (WinMask)
                            WinMask.hide();
                    }, 500);
                }
            }
        });
        //Ext.getCmp('ldg_company').setValue(store.getAt('0').get('comp_id'));
        tree.getRootNode().expand();
        return tree;
    };
    var trialBalance_popup = function (x, g, c, b) {

        var win_id = "trial_balance_win" + x;
        var show = false;
        if (x == 3) {
            var show = true;
        }

        var win = Ext.getCmp(win_id);
        if (Ext.isEmpty(win)) {

            win = new Ext.Window({
                id: win_id,
                title: 'Trial Balance',
                layout: 'fit',
                width: 700,
                autoHeight: true,
                plain: true,
                constrainHeader: true,
                modal: true,
                frame: true,
                resizable: false,
                items: new Ext.grid.GridPanel({
                    store: trial_balance_store(x),
                    layout: 'fit',
                    forceFit: true,
                    id: 'trial_details_grid_' + x,
                    height: 400,
                    loadMask: true,
                    columns: [new Ext.grid.RowNumberer({
                            header: 'Sl. No.'
                        }), {
                            header: 'Branch',
                            dataIndex: 'br_Name',
                            hidden: show
                        }, {
                            header: 'Ledger',
                            dataIndex: 'accled_LedgerName',
                            hidden: !show,
                            hideable: false
                        }, {
                            header: 'Dr.',
                            xtype: 'finascopcurrency',
                            format: FINASCOP_CURRENCY_FORMAT,
                            dataIndex: 'DrAmts',
                            align: 'right'
                        }, {
                            header: 'Cr.',
                            xtype: 'finascopcurrency',
                            format: FINASCOP_CURRENCY_FORMAT,
                            dataIndex: 'CrAmts',
                            align: 'right'
                        }],
                    viewConfig: {
                        forceFit: true,
                        deferEmptyText: false,
                        emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                    }, bbar: ['->', {html: '<b>Total </b> &nbsp; Dr. &nbsp;'}, {
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            readOnly: true,
                            value: 0,
                            id: 'total_dr_' + x,
                            listeners: {
                                afterrender: function (numfield) {
                                    this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                                }}
                        }, {
                            html: ' &nbsp; Cr. &nbsp;'}, {
                            xtype: 'numberfield',
                            format: FINASCOP_CURRENCY_FORMAT,
                            readOnly: true,
                            value: 0,
                            id: 'total_cr_' + x,
                            listeners: {
                                afterrender: function (numfield) {
                                    this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                                }}
                        }],
                    stripeRows: true,
                    autoExpandColumn: 'id_ledgerName',
                    listeners: {
                        rowdblclick: function (grid, row, e) {
                            var record = grid.store.getAt(row);
                            trialBalance_popup(3, record.get('grpid'), c, record.get('BRID'));
                        }
                    }
                }), buttons: [{
                        text: 'Close',
                        icon: IMAGE_BASE_PATH + "/default/icons/finascop_cross.png",
                        handler: function () {
                            win.close();
                        }
                    }]
            });
        }

        win.doLayout();
        win.show(this);
        win.center();
        var trial_details_grid = Ext.getCmp('trial_details_grid_' + x).getStore();
        trial_details_grid.baseParams = {
            group: g,
            company: c,
            branch: b,
            ind: x
        };
        trial_details_grid.load();
    };

//    var groupGridAction = function () {
//        var action = new Ext.ux.grid.RowActions({
//            hideMode: 'display',
//            autoWidth: false,
//            width: 30,
//            actions: [{
//                    sortable: false,
//                    tooltip: 'Edit group',
//                    iconCls: 'finascop_edit',
//                    hideIndex: 'System',
//                    callback: function (grid, rec, row, col) {
//                        addOrEditGroup(rec.get('id'));
//                    }
//                }, {
//                    iconCls: 'finascop_viewfile',
//                    tooltip: 'Show Group Reference ID',
//                    callback: function (grid, rec, row, col) {
//                        var refId = rec.get('group_ReferenceId');
//
//                        showGroupRefID(refId);
//                    }
//                }]
//        });
//        return action;
//    }
var groupActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View Reference ID",
                handler: function () {
                    var group_ReferenceId = Ext.getCmp('account_group_grid').getSelectionModel().getSelections()[0].data.group_ReferenceId;
                    showGroupRefID(group_ReferenceId);
                }
            }]
    }); 
    var loadGroupData = function () {
        var group_grid = Ext.getCmp('account_group_grid');
        var group_grid_store = group_grid.getStore();
        updatePagination(group_grid);
    }

    var NatureOfGroupStore = function () {
        var natureOfGroupStore = new Ext.data.JsonStore({
            url: modURL + '&op=listNatureOfGroups',
            fields: ['id', 'nog'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            id: 'client_store'
                    // turn on remote sorting
        });
        return natureOfGroupStore;

    }

    var group_panel = function () {
        var natureOfGroupStore = NatureOfGroupStore();
        var groupPanel = new Ext.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 1,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Group',
                                    id: 'group',
                                    allowBlank: false,
                                    name: 'group_name',
                                    anchor: '99%',
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1);
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 1,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Group Short Name',
                                    id: 'groupShortName',
                                    minLength: 3,
                                    maxLength: 3,
                                    allowBlank: false,
                                    name: 'group_shortname',
                                    anchor: '99%',
                                    msgTarget: 'under',
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 1,
                            labelAlign: 'top',
                            items: [{
                                    hiddenName: 'natureOfGroup',
                                    xtype: 'combo',
                                    name: 'natureOfGroup',
                                    fieldLabel: 'Nature of Group',
                                    id: 'natureOfGroup',
                                    anchor: '99%',
                                    displayField: 'nog',
                                    valueField: 'id',
                                    mode: 'local',
                                    allowBlank: false,
                                    store: natureOfGroupStore,
                                    triggerAction: 'all',
                                    forceSelection: true,
                                    editable: true,
                                    typeAhead: true,
                                    minChars: 2
                                }]
                        }]
                }]
        });
        return groupPanel;
    }

    var addOrEditGroup = function (id) {
        var title = (id == 0) ? 'Create Group' : 'Edit Group';
        var GroupPanel = group_panel();
        var accGroupWindow = Ext.getCmp('AccGroup_window');
        if (Ext.isEmpty(accGroupWindow)) {
            var accGroupWindow = new Ext.Window({
                id: 'AccGroup_window',
                title: title,
//                iconCls: 'finascop_additem',
                modal: true,
                layout: 'fit',
                width: 360,
                autoHeight: true,
                shadow: false,
                resizable: false,
                items: [GroupPanel
                ],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            accGroupWindow.close();
                        }
                    },
                    {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");

                            GroupPanel.getForm().submit({
                                waitMsg: 'Saving...',
                                url: modURL,
                                params: {
                                    op: 'saveGroup',
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp,
                                    id: id
                                },
                                //for submit success
                                success: function (frm, action) {

                                    if (action && action.response.responseText) {
                                        var tmp = Ext.decode(action.response.responseText);

                                        if (tmp.success == true) {
                                            Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {


                                                accGroupWindow.close();
                                                loadGroupData();


                                            });
                                        } else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }

                                    }



                                },
                                //for submit failure
                                failure: function (frm, action) {
                                    if (action.failureType == 'server') {
                                        obj = Ext.util.JSON.decode(action.response.responseText);
                                        Ext.Msg.alert('Notification', result.error);
                                    } else {
                                        Ext.MessageBox.alert('Notification', 'Please enter all required fields');
                                    }
                                    /*    Ext.MessageBox.show({
                                     title: 'Error!',
                                     msg: (obj.msg) ? obj.msg : obj.error,
                                     buttons: Ext.MessageBox.OK,
                                     icon: Ext.MessageBox.ERROR,
                                     width: 325
                                     });*/

                                }
                            });

                        }
                    }]
            });
        }

        if (id != 0) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            GroupPanel.load({
                url: modURL + '&op=getGroupDetails',
                params: {
                    'id': id,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (frm, action) {
                    var obj = Ext.decode(action.response.responseText);
                    Ext.getCmp('natureOfGroup').setValue(obj.data.id);
                    Ext.getCmp('natureOfGroup').setRawValue(obj.data.natureOfGroup);
                }
            });
        }
        accGroupWindow.show();
        accGroupWindow.doLayout();
        accGroupWindow.center();
    }

    var viewGroupFilter = function () {
        var GroupFilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'group_name'
                }, {
                    type: 'string',
                    dataIndex: 'group_ReferenceId'
                }, {
                    type: 'string',
                    dataIndex: 'nature_of_group'
                }]
        });
        GroupFilters.remote = true;
        GroupFilters.autoReload = true;
        return GroupFilters;
    }

    var createGroupGrid = function () {
        var group_store = groupStore();
        var GroupFilters = viewGroupFilter();
        //var action = groupGridAction();

        var group_grid = new Ext.grid.GridPanel({
            store: group_store,
            layout: 'fit',
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index, params, store) {
                    if (record.get('System') == 1)/*System genereated Group */
                    {
                        return 'finascop_indicateCYAN';
                    } else {
                        return '';
                    }
                }
            },
            plugins: [GroupFilters],
            title: 'Group',
//            iconCls: 'finascop_accountsgroup',
            id: 'account_group_grid',
            columns: [{
                    header: '',
                    sortable: false,
                    dataIndex: 'SlNo',
                    align: 'right',
                    width: 10
                },
                {
                    header: 'Group Name',
                    sortable: true,
                    dataIndex: 'group_name'

                },
                {
                    header: 'Nature of Group ',
                    sortable: true,
                    dataIndex: 'nature_of_group'

                }, {
                    header: 'Group Reference ID',
                    sortable: true,
                    dataIndex: 'group_ReferenceId',
                    tooltip: 'Reference ID'
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
                            groupActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }],
            //sm: pincode_select,
            listeners: {
                viewready: function (grid) {
                    updatePagination(grid)
                }
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Group',
                    tooltip: 'Create Group',
                    iconCls: 'finascop_add',
                    handler: function () {
                        addOrEditGroup(0);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: group_store,
                displayInfo: true,
                plugins: [],
                displayMsg: 'Displaying items {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            stripeRows: true,
            autoExpandColumn: 'accountno'

        });

        //Ext.util.Observable.capture(group_grid, function(evname) {console.log(evname, arguments);})
        return group_grid;
    };
    //
    /////////////////////////////EO Private Area///////////////////////////////

    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //------------------------Event Definition Area--------------------------//

    //-----------------------------------------------------------------------//
    ///////////////////////////////////////////////////////////////////////////


    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Public Area-------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Public Variables ---------------------------//
    //----------------------------Public Methods-----------------------------//
    return {
        init: function () {

            var panelId = 'gridLedgers';
            var lGrid = Ext.getCmp(panelId);
            if (Ext.isEmpty(lGrid)) {
                lGrid = ledgerListGrid(panelId);
                Application.UI.addTab(lGrid);
                lGrid.doLayout();
            } else {
                Application.UI.addTab(lGrid);
            }
        },
        group: function () {
            var groupgrid = Ext.getCmp('account_group_grid');
            if (!groupgrid) {
                groupgrid = createGroupGrid();
            }
            Application.UI.addTab(groupgrid);
            groupgrid.doLayout();
        },
        OpeningBalance: function () {
            var win_id = "opening_balance_win";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {

                win = new Ext.Window({
                    id: win_id,
                    title: 'Opening Balance',
//                    iconCls: 'finascop_openingbalance',
                    layout: 'fit',
                    width: 700,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: new Ext.Panel({
                        autoHeight: true,
                        border: false,
                        layout: 'column',
                        items: [{
                                layout: 'form',
                                columnWidth: 0.5,
                                border: false,
                                labelWidth: 70,
                                style: 'margin-top:10px;',
                                items: [{
                                        xtype: 'textfield',
                                        allowBlank: false,
                                        id: 'txf_op_ledger',
                                        fieldLabel: 'Ledger',
                                        anchor: '98%'

                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.1,
                                style: 'margin-top:10px;',
                                border: false,
                                items: [{
                                        xtype: 'button',
                                        text: 'Show',
                                        iconCls: 'finascop_search_btn',
                                        handler: function ()
                                        {

                                            Ext.getCmp('openingbalance_details_grid').getStore().load({
                                                params: {
                                                    ledger_filter: Ext.getCmp('txf_op_ledger').getValue()

                                                }
                                            });
                                        }
                                    }]
                            },
                            {
                                columnWidth: 1,
                                hideBorders: true,
                                border: false,
                                items: opening_balanceDetailsGrid()
                            },
                            {
                                columnWidth: 1,
                                hideBorders: true,
                                border: false,
                                items: opening_balanceDiffGrid()
                            },
                            {
                                layout: 'form',
                                columnWidth: 0.8,
                                border: false,
                                labelWidth: 70,
                                style: 'margin-top:10px;',
                                items: [{
                                        xtype: 'label',
                                        allowBlank: false,
                                        anchor: '98%'

                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.1,
                                style: 'margin-top:10px;',
                                border: false,
                                items: [{
                                        xtype: 'button',
                                        text: 'Cancel',
                                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                                        handler: function () {
                                            win.close();
                                            win.destroy();
                                        }
                                    }
                                ]
                            }, {
                                layout: 'form',
                                columnWidth: 0.1,
                                style: 'margin-top:10px;',
                                border: false,
                                items: [{
                                        xtype: 'button',
                                        text: 'Save',
                                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                                        handler: function () {
                                            var grid = Ext.getCmp('openingbalance_details_grid');
                                            var grid_store = grid.getStore();
                                            grid_store.commitChanges();

                                            var records = grid_store.getRange();
                                            var storedata = [];
                                            for (var i = 0; i < records.length; i++) {
                                                storedata[i] = records[i].data;

                                            }

                                            Ext.Ajax.request({
                                                url: modURL + '&op=SaveOpeningBalance',
                                                params: {
                                                    jsonData: JSON.stringify(storedata)

                                                },
                                                failure: function (response, options) {
                                                    Ext.MessageBox.alert('Notification', 'Action Failed');
                                                },
                                                success: function (response, options) {
                                                    //Ext.getCmp('openingbalance_details_grid').getStore().reload();
                                                    /*  var act = (v=='open')?'close':'open';
                                                     
                                                     Application.ActLog.actionLog('all', 'update status to '+act);*/
                                                    Ext.Msg.alert("Notification", "Saved Successfully", function (btn) {

                                                        win.close();

                                                    });
                                                }
                                            });


                                        }
                                    }
                                ]
                            }]
                    })



                });
            }
            Ext.getCmp('openingbalance_details_grid').getStore().load({
                callback: function () {
                    updateDiffGrid();
                }
            });
            win.doLayout();
            win.show(this);
            win.center();
        },
        //for addLedger 
        addLedger: function (ledger_id, group_id, ledger_name, group_name, accled_Comp_id, accled_Comp) {
            //creating ledger window and set title  dynamically 
            var window_title = (ledger_id == 0) ? 'Create Ledger' : 'Edit Ledger - ' + ledger_name;
            var win_id = "id_addEditLedger";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {

                var lForm = createLedgerForm(ledger_id, group_id, ledger_name, group_name, accled_Comp_id, accled_Comp);
                win = new Ext.Window({
                    id: win_id,
                    title: window_title,
                    layout: 'fit',
                    width: 500,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
//                    iconCls: 'finascop_icon_ledger',
                    resizable: false,
                    items: [lForm],
                    buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            iconCls: 'finascop_my-icon1',
                            handler: function () {
                                win.close();
                            }
                        },
                        {
                            text: 'Save',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                            iconCls: 'finascop_my-icon1',
                            handler: function () {
                                /*ledger_form_submit(lForm, win);*/

                                var branch_grid = Ext.getCmp('id_branch_grid');
                                var s = branch_grid.getSelectionModel().getSelections();
                                if (!Ext.isEmpty(branch_grid) && !Ext.isEmpty(s)) {

                                    var selected = [];
                                    Ext.each(s, function (item) {
                                        selected.push(item.data);
                                    });
                                    var form_data = Ext.getCmp('id_tEditForm').getForm().getValues();
                                    form_data.branches = selected;
                                    var act = (ledger_id > 0) ? 'Update' : 'Insert';
                                    var params = {
                                        action: act,
                                        module: 'ledger',
                                        op: 'saveLedger',
                                        id: ledger_id,
                                        extrainfo: 'asd'
                                    };
                                    APICall(params, Application.Finascop_Ledger.ledger_form_submit, form_data);
                                } else {
                                    Ext.MessageBox.alert("Notification", "Please select a branch from the list.");
                                }
                            }
                        }]
                });
            }

            win.doLayout();
            win.show(this);
            win.center();
            Ext.getCmp('id_branch_grid').getStore().load({
                params: {
                    ledger_id: ledger_id,
                    company: accled_Comp_id
                },
                callback: function (res) {
                    var items = Ext.getCmp('id_branch_grid').getStore().data.items;
                    var sm = Ext.getCmp('id_branch_grid').getSelectionModel();
                    var count = items.length;
                    for (var i = 0; i < count; i++) {
                        if (items[i].data.isChecked == "1")
                            sm.selectRow(i, true);
                    }
                }
            });
            if (accled_Comp_id > 0 && ledger_id > 0) {
                Ext.getCmp('ldg_company').setReadOnly(true);
            }
        },
        ledger_form_submit: function () {

            var branch_grid = Ext.getCmp('id_branch_grid');
            var s = branch_grid.getSelectionModel().getSelections();
            var selected = [];
            Ext.each(s, function (item) {
                selected.push(item.data);
            });
            Ext.getCmp('branches_grid').setValue(Ext.encode(selected));
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.getCmp('id_tEditForm').getForm().submit({
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving...',
                success: function (form, action) {

                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success == true && tmp.valid == true) {

                        Ext.getCmp('gridLedgers').store.reload({
                            callback: function () {
                                Ext.MessageBox.alert("Success", "Your data has been saved successfully!..");
                            }
                        });
                        Ext.getCmp('id_addEditLedger').close();
                    } else if (tmp.success == false && tmp.valid == false) {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                    }
                },
//                failure: function (form, action) {
//                    var tmp = Ext.decode(action.response.responseText);
//                    if (tmp.success == false && tmp.valid == false) {
//                        Ext.MessageBox.alert("Notification", tmp.msg);
//                    }
//                }
                  failure: function (form, action) {
                                        switch (action.failureType) {
                                            case Ext.form.Action.CLIENT_INVALID:
                                                Ext.Msg.alert('Failure', 'Form fields may be submitted with invalid values',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {
         
                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.CONNECT_FAILURE:
                                                Ext.Msg.alert('Failure', 'Ajax communication failed',
                                                        function (btn, text) {
                                                            if (btn == 'ok') {

                                                            }
                                                        });
                                                break;
                                            case Ext.form.Action.SERVER_INVALID:
                                                Ext.Msg.alert('Failure', action.response.responseText,
                                                        function (btn, text) {
                                                            if (btn == 'ok') {

                                                            }
                                                        });
                                        }

                                    }

            });
        },
        ledgerBalance: function () {

            var win_id = "ledger_balance_win";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {

                win = new Ext.Window({
                    id: win_id,
                    title: 'Ledger',
//                    iconCls: 'finascop_balance',
                    layout: 'fit',
                    width: winsize.width * 0.6,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: new Ext.Panel({
                        height: 520,
                        border: false,
                        layout: 'column',
                        items: [{
                                layout: 'form',
                                columnWidth: 0.5,
                                border: false,
                                labelWidth: 70,
                                style: 'margin:10px 0 0 5px;',
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'ldg_balance_company',
                                        fieldLabel: 'Company',
                                        hiddenName: 'ldg_balance_company',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 1,
                                        displayField: 'comp_name',
                                        valueField: 'comp_id',
                                        tabIndex: 200,
                                        store: company_store(),
                                        anchor: '98%',
                                        listeners: {
                                            select: function (cbo) {
                                                var company = this.getValue();
                                                var ldg_balance_branch = Ext.getCmp('ldg_balance_branch');
                                                var ldg_balance_branch_store = ldg_balance_branch.getStore();
                                                ldg_balance_branch.reset();
                                                ldg_balance_branch_store.removeAll();
                                                ldg_balance_branch_store.load();
                                                var ldg_balance_ledger = Ext.getCmp('ldg_balance_ledger');
                                                ldg_balance_ledger.reset();
                                                ldg_balance_ledger.getStore().removeAll();
                                            }
                                        }
                                    }, {layout: 'column',
                                        border: false,
                                        items: [{
                                                layout: 'form',
                                                columnWidth: 0.6,
                                                border: false,
                                                labelWidth: 70,
                                                items: [{
                                                        fieldLabel: 'Date From',
                                                        xtype: 'datefield',
                                                        id: 'ledger_from',
                                                        name: 'ledger_from',
                                                        anchor: '98%',
                                                        maxValue: new Date(),
                                                        allowBlank: false,
                                                        tabIndex: 202,
                                                        value: (new Date()),
                                                        format: 'd-m-Y',
                                                        listeners: {
                                                            select: function () {
                                                                //reseting to date
                                                                //Ext.getCmp('ledger_to').setValue("");
                                                            }
                                                        }


                                                    }]
                                            }, {
                                                layout: 'form',
                                                columnWidth: 0.4,
                                                border: false,
                                                labelWidth: 25,
                                                items: [{
                                                        fieldLabel: 'To',
                                                        xtype: 'datefield',
                                                        id: 'ledger_to',
                                                        name: 'ledger_to',
                                                        anchor: '97.4%',
                                                        allowBlank: false,
                                                        maxValue: new Date(),
                                                        value: (new Date()),
                                                        tabIndex: 203,
                                                        format: 'd-m-Y',
                                                        listeners: {
                                                            select: function () {
                                                                var frm = Ext.getCmp('ledger_from').getValue();
                                                                var dfrom = new Date(frm);
                                                                var to = Ext.getCmp('ledger_to').getValue();
                                                                var dto = new Date(to);
                                                                //validating date fields

                                                                if (to < frm) {
                                                                    Ext.getCmp('ledger_to').setValue('');
                                                                    Ext.MessageBox.alert('Warning', 'Invalid Selection, From date should be lesser than or equal to To date.');
                                                                }

                                                                var diff = (dto.getTime() - dfrom.getTime()) / (1000 * 60 * 60 * 24);

                                                                // console.log(date_diff);
                                                                //checking date differencce is greater than 31 days
                                                                if (diff > date_diff) {
                                                                    Ext.getCmp('ledger_to').setValue("");
                                                                    Ext.MessageBox.alert('Warning', 'Date range must be in 31 days');



                                                                }

                                                            }
                                                        }









                                                    }]}]
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.5,
                                border: false,
                                labelWidth: 70,
                                style: 'margin-top:10px;',
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'ldg_balance_branch',
                                        fieldLabel: 'Branch',
                                        hiddenName: 'ldg_balance_branch',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 2,
                                        mode: 'local',
                                        tabIndex: 201,
                                        displayField: 'brsnnam',
                                        valueField: 'br_ID',
                                        store: branch_store(), anchor: '98%',
                                        listeners: {
                                            select: function (cbo) {
                                                var ldg_balance_ledger = Ext.getCmp('ldg_balance_ledger');
                                                ldg_balance_ledger.reset();
                                                var ldg_balance_ledger_store = ldg_balance_ledger.getStore();
                                                ldg_balance_ledger_store.removeAll();
                                                ldg_balance_ledger_store.baseParams = {
                                                    company: Ext.getCmp('ldg_balance_company').getValue(),
                                                    branch: Ext.getCmp('ldg_balance_branch').getValue()
                                                };
                                                ldg_balance_ledger_store.load();
                                            }
                                        }
                                    }, {
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'ldg_balance_ledger',
                                        fieldLabel: 'Ledger',
                                        hiddenName: 'ldg_balance_ledger',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 1,
                                        tabIndex: 204,
                                        displayField: 'accled_LedgerName',
                                        valueField: 'accled_Ledger_Id',
                                        store: ledger_combo_store(),
                                        anchor: '98%'

                                    }]
                            }, /*{
                             layout: 'form',
                             columnWidth: 0.10,
                             border: false,
                             items: [{
                             xtype: 'displayfield',
                             anchor: '100%'
                             }]
                             },*/ {
                                layout: 'form',
                                columnWidth: 0.35,
                                border: false,
                                items: [{
                                        xtype: 'combo',
                                        id: 'auditstatus_cmb',
                                        displayField: 'audit_status',
                                        valueField: 'id',
                                        mode: 'local',
                                        emptyText: 'Select ...',
                                        allowBlank: false,
                                        anchor: '98%',
                                        tabIndex: 205,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        fieldLabel: '&nbsp;Approval Status',
                                        store: new Ext.data.JsonStore({
                                            fields: ['id', 'audit_status'],
                                            data: [{id: '1', audit_status: 'All'},
                                                {id: '2', audit_status: 'Approved'},
                                                {id: '3', audit_status: 'UnApproved'}
                                            ]
                                        }),
                                        listeners: {
                                            afterrender: function () {
                                                this.setValue(1);
                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.35,
                                border: false,
                                labelWidth: 151,
                                items: [{
                                        xtype: 'numberfield',
                                        format: FINASCOP_CURRENCY_FORMAT,
                                        id: 'ldg_dr',
                                        anchor: '98%',
                                        fieldLabel: '&nbsp;Opening Balance : &nbsp; Dr. ',
                                        readOnly: true,
                                        tabIndex: 206,
                                        style: 'text-align: right',
                                        listeners: {
                                            afterrender: function (numfield) {
                                                this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                                            }}
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.2,
                                border: false,
                                labelWidth: 40,
                                items: [{
                                        xtype: 'numberfield',
                                        format: FINASCOP_CURRENCY_FORMAT,
                                        id: 'ldg_cr',
                                        anchor: '98%',
                                        fieldLabel: 'Cr. ',
                                        readOnly: true,
                                        tabIndex: 207,
                                        style: 'text-align: right',
                                        listeners: {
                                            afterrender: function (numfield) {
                                                this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                                            }}
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.1,
                                border: false,
                                items: [{
                                        xtype: 'button',
                                        text: 'Show',
                                        iconCls: 'finascop_search_btn',
                                        handler: function () {
                                            var ldg_balance_ledger = Ext.getCmp('ldg_balance_ledger').getValue();
                                            var from = Ext.getCmp('ledger_from').getRawValue();
                                            var to = Ext.getCmp('ledger_to').getRawValue();
                                            var auidt_status = Ext.getCmp('auditstatus_cmb').getValue();
                                            if (!Ext.isEmpty(ldg_balance_ledger) && !Ext.isEmpty(from) &&
                                                    !Ext.isEmpty(to)) {

                                                Ext.Ajax.request({
                                                    waitTitle: 'Please Wait',
                                                    waitMsg: 'Sending...',
                                                    url: modURL + '&op=updateAmount',
                                                    params: {
                                                        ledger: ldg_balance_ledger,
                                                        date_from: from,
                                                        date_to: to,
                                                        audit_status: auidt_status

                                                    },
                                                    success: function (response, options) {
                                                        var tmp = Ext.decode(response.responseText);
                                                        if (tmp.success !== undefined && tmp.success === true) {
                                                            Ext.getCmp('ldg_cr').setValue(Ext.util.Format.number(tmp.ldg_cr, FINASCOP_CURRENCY_FORMAT));
                                                            Ext.getCmp('ldg_dr').setValue(Ext.util.Format.number(tmp.ldg_dr, FINASCOP_CURRENCY_FORMAT));
                                                            var ledger_details_grid = Ext.getCmp('ledger_details_grid').getStore();
                                                            ledger_details_grid.baseParams = {
                                                                ledger: ldg_balance_ledger,
                                                                date_from: from,
                                                                date_to: to,
                                                                audit_status: auidt_status
                                                            };
                                                            ledger_details_grid.load();
                                                        }
                                                    }
                                                });
                                            } else {
                                                Ext.MessageBox.alert("Notification", "Please select the date range and ledger.");
                                            }
                                        }
                                    }]
                            }, {
                                columnWidth: 1,
                                hideBorders: true,
                                border: false,
                                items: ledgerDetailsGrid()},
                            {
                                layout: 'form',
                                columnWidth: 0.20,
                                border: false,
                                items: [{
                                        html: '<div class="finascop_color_wrap"><div class="finascop_color-lightpink_small"></div> <div class="finascop_color_text">UnApproved</div></div>'
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.35,
                                border: false,
                                labelWidth: 151,
                                items: [{
                                        xtype: 'numberfield',
                                        format: FINASCOP_CURRENCY_FORMAT,
                                        id: 'ldg_dr1',
                                        anchor: '98%',
                                        tabIndex: 208,
                                        fieldLabel: '&nbsp;Closing Balance : &nbsp; Dr. ',
                                        readOnly: true,
                                        style: 'text-align: right',
                                        listeners: {
                                            afterrender: function (numfield) {
                                                this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                                            }}
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.20,
                                border: false,
                                labelWidth: 40,
                                items: [{
                                        xtype: 'numberfield',
                                        format: FINASCOP_CURRENCY_FORMAT,
                                        id: 'ldg_cr1',
                                        anchor: '98%',
                                        fieldLabel: 'Cr. ',
                                        readOnly: true,
                                        tabIndex: 209,
                                        style: 'text-align: right',
                                        listeners: {
                                            afterrender: function (numfield) {
                                                this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                                            }}
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.10,
                                border: false,
                                items: [{
                                        xtype: 'button',
                                        text: 'Export',
                                        tabIndex: 210,
                                        iconCls: '',
                                        handler: function () {
                                            var dlysmmry_store = Ext.getCmp('ledger_details_grid').getStore();
                                            if (dlysmmry_store.getTotalCount() <= 0) {
                                                Ext.Msg.confirm('Notification', 'Grid does not contain any data to export!<br> Do you still wants to export?', function (btn) {
                                                    if (btn == 'no') {
                                                        return;
                                                    }
                                                });
                                            }
                                            var wisize = 'ledgerbalancereport';
                                            var activeTabtitle = 'Ledger_Balance_Report';
                                            var indexes = [];
                                            var heads = [];

                                            /**
                                             * @modified by Aparna Ravvendran <aparna@saturn.in>
                                             * @modified On 27-Mar-2013
                                             *
                                             * pass total no of headers of selected grid for export excel
                                             */
                                            var filterData = Ext.encode(genledgebalancelastParameters);
                                            for (var i = 0; i < Ext.getCmp('ledger_details_grid').getColumnModel().getColumnCount(); i++) {
                                                if (Ext.getCmp('ledger_details_grid').getColumnModel().isHidden(i) !== true) {

                                                    indexes[indexes.length] = Ext.getCmp('ledger_details_grid').getColumnModel().config[i].dataIndex;
                                                    heads[heads.length] = Ext.getCmp('ledger_details_grid').getColumnModel().config[i].header;
                                                }
                                                var dataindexes = Ext.encode(indexes);
                                                var headers = Ext.encode(heads);
                                            }
                                            postToUrl(modURL + '&op=getledgerBalancerepexport', {dataindexes: dataindexes, headers: headers, filterData: filterData, wisize: wisize, activeTabtitle: activeTabtitle}, 'post', 'downloadIframe');
                                            //postToUrl(modURL + '&op=getsalesexport', {dataindexes: dataindexes, headers: headers, filterData: filterData}, 'post', 'downloadIframe');
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.10,
                                border: false,
                                items: [{
                                        xtype: 'button',
                                        text: 'Cancel',
                                        tabIndex: 211,
                                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                                        handler: function () {
                                            win.close();
                                        }
                                    }]
                            }]
                    }), /*** buttons: [{
                     text: 'Close',
                     icon: IMAGE_BASE_PATH + "/default/icons/cross.png",
                     handler: function () {
                     win.close();
                     }
                     }],****/
                    listeners: {
                        afterrender: function () {
                            Ext.getCmp('ldg_balance_company').setValue(_SESSION.finascop_current_company_id);
                            Ext.getCmp('ldg_balance_company').setRawValue(_SESSION.finascop_current_company);
                            Ext.getCmp('ldg_balance_company').setReadOnly(true);
                            Ext.getCmp('ldg_balance_branch').setValue(_SESSION.finascop_current_branch_id);
                            Ext.getCmp('ldg_balance_branch').setRawValue(_SESSION.current_branch);
                            var ldg_balance_ledger = Ext.getCmp('ldg_balance_ledger');
                            ldg_balance_ledger.reset();
                            var ldg_balance_ledger_store = ldg_balance_ledger.getStore();
                            ldg_balance_ledger_store.removeAll();
                            ldg_balance_ledger_store.baseParams = {
                                company: Ext.getCmp('ldg_balance_company').getValue(),
                                branch: Ext.getCmp('ldg_balance_branch').getValue()
                            };
                            ldg_balance_ledger_store.load();
                        }
                    }



                });
            }

            win.doLayout();
            win.show(this);
            win.center();
        },
        trialBalance: function () {

            var win_id = "trial_balance_win";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {

                win = new Ext.Window({
                    id: win_id,
                    title: 'Trial Balance',
//                    iconCls: 'finascop_trialbalance',
                    layout: 'fit',
                    width: 700,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: new Ext.Panel({
                        height: 500,
                        border: false,
                        layout: 'column',
                        items: [{
                                layout: 'form',
                                columnWidth: 0.45,
                                border: false,
                                labelWidth: 70,
                                style: 'margin:10px 0 0 5px;',
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'ldg_balance_company',
                                        fieldLabel: 'Company',
                                        hiddenName: 'ldg_balance_company',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 1,
                                        displayField: 'comp_name',
                                        valueField: 'comp_id',
                                        store: company_store(), anchor: '98%',
                                        listeners: {
                                            select: function (cbo) {
                                                var company = this.getValue();
                                                var ldg_balance_branch = Ext.getCmp('ldg_balance_branch');
                                                var ldg_balance_branch_store = ldg_balance_branch.getStore();
                                                ldg_balance_branch.reset();
                                                ldg_balance_branch_store.removeAll();
                                                ldg_balance_branch_store.baseParams = {
                                                    company: company,
                                                    lb: 1
                                                };
                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.45,
                                border: false,
                                labelWidth: 70,
                                style: 'margin-top:10px;',
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'ldg_balance_branch',
                                        fieldLabel: 'Branch',
                                        hiddenName: 'ldg_balance_branch',
                                        mode: 'local',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 2,
                                        displayField: 'br_Name',
                                        valueField: 'br_ID',
                                        store: branch_store(),
                                        anchor: '98%'
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.1,
                                border: false,
                                items: [{
                                        xtype: 'button',
                                        iconCls: 'finascop_search_btn',
                                        text: 'Show',
                                        style: 'margin-top:10px;',
                                        handler: function () {
                                            var ldg_balance_branch = Ext.getCmp('ldg_balance_branch').getValue();
                                            var ldg_balance_company = Ext.getCmp('ldg_balance_company').getValue();
                                            if (!Ext.isEmpty(ldg_balance_company)) {
                                                var trial_details_grid = Ext.getCmp('trial_details_grid_1').getStore();
                                                trial_details_grid.baseParams = {
                                                    company: ldg_balance_company,
                                                    branch: ldg_balance_branch,
                                                    ind: 1
                                                };
                                                trial_details_grid.load();
                                            } else {
                                                Ext.MessageBox.alert("Notification", "Please select the company and branch.");
                                            }
                                        }
                                    }]
                            }, {
                                columnWidth: 1,
                                hideBorders: true,
                                border: false,
                                items: trialBalanceDetailsGrid()}]
                    }), buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            handler: function () {
                                win.close();
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            Ext.getCmp('ldg_balance_company').setValue(_SESSION.finascop_current_company_id);
                            Ext.getCmp('ldg_balance_company').setRawValue(_SESSION.finascop_current_company);
                            Ext.getCmp('ldg_balance_company').setReadOnly(true);
                            var company = Ext.getCmp('ldg_balance_company').getValue();
                            var ldg_balance_branch = Ext.getCmp('ldg_balance_branch');
                            var ldg_balance_branch_store = ldg_balance_branch.getStore();
                            ldg_balance_branch.reset();
                            ldg_balance_branch_store.removeAll();
                            ldg_balance_branch_store.baseParams = {
                                company: company,
                                lb: 1
                            };
                            ldg_balance_branch_store.load();
                        }
                    }





                });
            }

            win.doLayout();
            win.show(this);
            win.center();
        },
        accoutStructure: function () {

            var win_id = "account_structure";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {

                win = new Ext.Window({
                    id: win_id,
                    title: 'Accounts Structure',
                    layout: 'fit',
                    width: 700,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    items: new Ext.Panel({
                        height: 500,
                        border: false,
                        layout: 'border',
                        items: [tree_panel(),
                            new Ext.Panel({
                                width: 200,
                                border: false,
                                layout: 'form',
                                region: 'east',
                                title: 'Legend',
                                labelAlign: 'top',
                                items: [{
                                        html: '<div class="finascop_color_wrap"><div class="finascop_color-red"></div> <div class="finascop_color_text">Group </div></div><div class="finascop_color_wrap"><div class="finascop_color-green"></div> <div class="finascop_color_text">Ledger</div></div><div class="finascop_color_wrap"><div class="finascop_color-l-blue"></div><div class="finascop_color_text"> Branch Ledger</div></div>'
                                    }, {
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'ldg_company',
                                        fieldLabel: 'Company',
                                        hiddenName: 'ldg_company',
                                        forceSelection: true,
                                        hidden: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 1,
                                        displayField: 'comp_name',
                                        valueField: 'comp_id',
                                        store: company_store(),
                                        anchor: '98%',
                                        listeners: {
                                            afterrender: function () {
                                                //this.setValue(comp_id);
                                                //this.setRawValue(comp_name);
                                                this.setValue(_SESSION.finascop_current_company_id);
                                                this.setRawValue(_SESSION.finascop_current_company);
                                                this.setReadOnly(true);
                                                var ldg_company = Ext.getCmp('ldg_company').getValue();
                                                if (ldg_company > 0) {
                                                    Ext.getCmp('account_structure_tree').loader.baseParams.company = ldg_company;
                                                    Ext.getCmp('account_structure_tree').getRootNode().reload();
                                                }
                                            }}
                                    }]
                            })]
                    }), buttons: [{
                            text: 'Cancel',
                            tabIndex: 502,
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            handler: function () {
                                win.close();
                            }
                        },
                        {
                            text: 'Refresh',
                            tabIndex: 501,
                            icon: IMAGE_BASE_PATH + "/default/icons/finascop_restart.png",
                            handler: function () {
                                var ldg_company = Ext.getCmp('ldg_company').getValue();
                                if (ldg_company > 0) {
                                    Ext.getCmp('account_structure_tree').loader.baseParams.company = ldg_company;
                                    Ext.getCmp('account_structure_tree').getRootNode().reload();
                                }
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            WinMask = new Ext.LoadMask(Ext.getCmp('account_structure').getEl());
                            WinMask.show();
                        }
                    }
                });
            }

            win.doLayout();
            win.show(this);
            win.center();
        },
        profitLoss: function () {

            var win_id = "profit_loss";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {

                win = new Ext.Window({
                    id: win_id,
                    title: 'Profit & Loss',
//                    iconCls: 'finascop_profit_loss',
                    layout: 'fit',
                    width: 700,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    defaults: {border: false},
                    buttonAlign: 'left',
                    items: new Ext.Panel({
                        height: 500,
                        border: false,
                        layout: 'column',
                        items: [{
                                layout: 'form',
                                border: false,
                                columnWidth: 0.45,
                                style: 'margin:15px 5px 15px 10px',
                                labelWidth: 60,
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'inc_exp_company',
                                        fieldLabel: 'Company',
                                        hiddenName: 'inc_exp_company',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 1,
                                        displayField: 'comp_name',
                                        valueField: 'comp_id',
                                        store: company_store(),
                                        anchor: '98%',
                                        listeners: {
                                            select: function (cbo) {
                                                var company = this.getValue();
                                                var inc_exp_branch = Ext.getCmp('inc_exp_branch');
                                                var inc_exp_branch_str = inc_exp_branch.getStore();
                                                inc_exp_branch.reset();
                                                inc_exp_branch_str.removeAll();
                                                inc_exp_branch_str.baseParams.company = company;
                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                border: false,
                                columnWidth: 0.45,
                                style: 'margin:15px 5px 15px 5px',
                                labelWidth: 60,
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'inc_exp_branch',
                                        fieldLabel: 'Branch',
                                        hiddenName: 'inc_exp_branch',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 2,
                                        mode: 'local',
                                        displayField: 'br_Name',
                                        valueField: 'br_ID',
                                        store: branch_store(),
                                        anchor: '98%'
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.1,
                                border: false,
                                style: 'margin:15px 5px 15px 5px',
                                items: [{
                                        xtype: 'button',
                                        text: 'Show',
                                        iconCls: 'finascop_search_btn',
                                        handler: function () {
                                            var inc_exp_grid = Ext.getCmp('inc_exp_grid');
                                            if (Ext.getCmp('inc_exp_company').getValue() > 0) {
                                                inc_exp_grid.loader.baseParams.company = Ext.getCmp('inc_exp_company').getValue();
                                                inc_exp_grid.loader.baseParams.branch = Ext.getCmp('inc_exp_branch').getValue();
                                                inc_exp_grid.getRootNode().reload();
                                            } else {
                                                Ext.MessageBox.alert("Notification", "Please select a company from the list.");
                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 1,
                                border: false,
                                items: new Ext.ux.tree.TreeGrid({
                                    id: 'inc_exp_grid',
                                    layout: 'fit',
                                    forceFit: true,
                                    height: 442,
                                    hideHeaders: true,
                                    defaultSortable: false,
                                    enableSort: false,
                                    columns: [{
                                            header: '',
                                            dataIndex: 'NatGroupName',
                                            width: 250,
                                            sortable: false
                                        }, {
                                            header: '',
                                            dataIndex: 'grp_amt',
                                            align: 'right',
                                            width: 140,
                                            sortable: false
                                        }, {
                                            dataIndex: 'nt_amt',
                                            width: 140,
                                            align: 'right',
                                            sortable: false
                                        }, {
                                            dataIndex: 'final_amt',
                                            align: 'right',
                                            width: 140,
                                            sortable: false
                                        }],
                                    dataUrl: modURL + '&op=getProfitLoss',
                                    listeners: {
                                        load: function () {
                                            setTimeout(function () {
                                                if (WinMask)
                                                    WinMask.hide();
                                            }, 500);
                                        }
                                    }
                                })
                            }]
                    }), buttons: [
                        {html: '<div class="finascop_color_wrap"><div class="finascop_color-blue_small"></div> <div class="finascop_text_c">Nature of Group</div></div>'},
                        {html: '<div class="finascop_color_wrap"><div class="finascop_color-red_small"></div> <div class="finascop_text_c">Group</div></div>'},
                        {html: '<div class="finascop_color_wrap"><div class="finascop_color-green_small"></div><div class="finascop_text_c">Ledger</div></div>'},
                        '->',
                        {
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                            handler: function () {
                                win.close();
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            WinMask = new Ext.LoadMask(Ext.getCmp('profit_loss').getEl());
                            Ext.getCmp('inc_exp_grid').loader.on("beforeload", function (treeLoader, node) {
                                if (Ext.isEmpty(Ext.getCmp('inc_exp_company').getValue()))
                                    return false;
                                else {
                                    WinMask.show();
                                }
                            }, this);
                            Ext.getCmp('inc_exp_company').setValue(_SESSION.finascop_current_company_id);
                            Ext.getCmp('inc_exp_company').setRawValue(_SESSION.finascop_current_company);
                            Ext.getCmp('inc_exp_company').setReadOnly(true);
                            var company = Ext.getCmp('inc_exp_company').getValue();
                            var inc_exp_branch = Ext.getCmp('inc_exp_branch');
                            var inc_exp_branch_str = inc_exp_branch.getStore();
                            inc_exp_branch.reset();
                            inc_exp_branch_str.removeAll();
                            inc_exp_branch_str.baseParams.company = company;
                            inc_exp_branch_str.load();
                        }
                    }
                });
            }


            win.doLayout();
            win.show(this);
            win.center();
        },
        balanceSheet: function () {
            var win_id = "balancesheet_win";
            var win = Ext.getCmp(win_id);
            if (Ext.isEmpty(win)) {

                win = new Ext.Window({
                    id: win_id,
                    title: 'Balancesheet',
//                    iconCls: 'finascop_balancesheet',
                    layout: 'fit',
                    width: 700,
                    autoHeight: true,
                    plain: true,
                    constrainHeader: true,
                    modal: true,
                    frame: true,
                    resizable: false,
                    defaults: {border: false},
                    buttonAlign: 'left',
                    items: new Ext.Panel({
                        height: 500,
                        border: false,
                        layout: 'column',
                        items: [{
                                layout: 'form',
                                border: false,
                                columnWidth: 0.45,
                                style: 'margin:15px 5px 15px 10px',
                                labelWidth: 60,
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'bs_company',
                                        fieldLabel: 'Company',
                                        hiddenName: 'bs_company',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 1,
                                        displayField: 'comp_name',
                                        valueField: 'comp_id',
                                        store: company_store(),
                                        anchor: '98%',
                                        listeners: {
                                            select: function (cbo) {
                                                var company = this.getValue();
                                                var bs_branch = Ext.getCmp('bs_branch');
                                                var bs_branch_str = bs_branch.getStore();
                                                bs_branch.reset();
                                                bs_branch_str.removeAll();
                                                bs_branch_str.baseParams.company = company;
                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                border: false,
                                columnWidth: 0.45,
                                style: 'margin:15px 5px 15px 5px',
                                labelWidth: 60,
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        allowBlank: false,
                                        id: 'bs_branch',
                                        fieldLabel: 'Branch',
                                        hiddenName: 'bs_branch',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 2,
                                        mode: 'local',
                                        displayField: 'br_Name',
                                        valueField: 'br_ID',
                                        store: branch_store(),
                                        anchor: '98%'
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 0.1,
                                border: false,
                                style: 'margin:15px 5px 15px 5px',
                                items: [{
                                        xtype: 'button',
                                        text: 'Show',
                                        iconCls: 'finascop_search_btn',
                                        handler: function () {
                                            var bs_grid = Ext.getCmp('bs_grid');
                                            if (Ext.getCmp('bs_company').getValue() > 0) {
                                                bs_grid.loader.baseParams.company = Ext.getCmp('bs_company').getValue();
                                                bs_grid.loader.baseParams.branch = Ext.getCmp('bs_branch').getValue();
                                                bs_grid.getRootNode().reload();
                                            } else {

                                                Ext.MessageBox.alert("Notification", "Please select a company from the list.");
                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 1,
                                border: false,
                                items: new Ext.ux.tree.TreeGrid({
                                    id: 'bs_grid',
                                    layout: 'fit',
                                    forceFit: true,
                                    height: 442,
                                    hideHeaders: true,
                                    defaultSortable: false,
                                    enableSort: false,
                                    columns: [{
                                            header: '',
                                            dataIndex: 'NatGroupName',
                                            width: 250,
                                            sortable: false
                                        }, {
                                            header: '',
                                            dataIndex: 'grp_amt',
                                            align: 'right',
                                            width: 140,
                                            sortable: false
                                        }, {
                                            dataIndex: 'nt_amt',
                                            align: 'right',
                                            width: 140,
                                            sortable: false
                                        }, {
                                            dataIndex: 'final_amt',
                                            align: 'right',
                                            width: 140,
                                            sortable: false
                                        }],
                                    dataUrl: modURL + '&op=getBalancesheet',
                                    listeners: {
                                        load: function () {
                                            setTimeout(function () {
                                                if (WinMask)
                                                    WinMask.hide();
                                            }, 500);
                                        }
                                    }
                                })
                            }]
                    }), buttons: [
                        {html: '<div class="finascop_color_wr"><div class="finascop_color-blue_small"></div><div class="finascop_text_c"> Nature of Group</div></div>'},
                        {html: '<div class="finascop_color_wr"><div class="finascop_color-red_small"></div> <div class="finascop_text_c">Group</div></div>'},
                        {html: '<div class="finascop_color_wr"><div class="finascop_color-green_small"></div><div class="finascop_text_c"> Ledger</div></div>'},
                        {html: '<div class="finascop_color_wr"><div class="finascop_color-brown_small"></div><div class="finascop_text_c"> Branch Ledger</div></div>'},
                        {html: '<div class="finascop_color_wr"><div class="finascop_color-violet_small"></div><div class="finascop_text_c"> Opening Balance</div></div>'},
                        '->',
                        {
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            handler: function () {
                                win.close();
                            }
                        }],
                    listeners: {
                        afterrender: function () {
                            WinMask = new Ext.LoadMask(Ext.getCmp('balancesheet_win').getEl());
                            Ext.getCmp('bs_grid').loader.on("beforeload", function (treeLoader, node) {
                                if (Ext.isEmpty(Ext.getCmp('bs_company').getValue()))
                                    return false;
                                else
                                    WinMask.show();
                            }, this);
                            Ext.getCmp('bs_company').setValue(_SESSION.finascop_current_company_id);
                            Ext.getCmp('bs_company').setRawValue(_SESSION.finascop_current_company);
                            Ext.getCmp('bs_company').setReadOnly(true);
                            var company = Ext.getCmp('bs_company').getValue();
                            var bs_branch = Ext.getCmp('bs_branch');
                            var bs_branch_str = bs_branch.getStore();
                            bs_branch.reset();
                            bs_branch_str.removeAll();
                            bs_branch_str.baseParams.company = company;
                            bs_branch_str.load();
                        }
                    }
                });
            }


            win.doLayout();
            win.show(this);
            win.center();
        }
    };
    //////////////////////////////EO Public Area///////////////////////////////
}
();