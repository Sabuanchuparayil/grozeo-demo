Application.FinascopProfitabilityStatement = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=finascop_profitability_statement';
    var recs_per_page = 22;
    var dateRange = parseInt(BETWEEN_DATES_TYPE_A);
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    {//ProfitabilityStatement start

        var pftyStmtStore = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                proxy: new Ext.data.HttpProxy({
                    url: modURL + '&op=getProfitabilityStatement',
                    method: 'post'
                }),
                fields: ['SODate','SONumber','br_ID','br_Name','customer_ID', 'customer_Name', 'customer_Mobile', 
                'invValAtax','GST', ' purchaseEPR','grossProfit','grossProfitPercentage', 'companyProfit', 'companyProfitPercentage',
                'branchProfit','branchProfitPercentage','incentitve','technology' ],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true,
                remoteSort: true,
                listeners: {
                    beforeload:function(store,e){ //
                           return !Application.FinascopProfitabilityStatement.Cache.InitatedOnly;
                    },
                    load: function (store, records, options) {
    
                    },
                    exception : function( misc ){
    
                    }
                }
            });
            
            store.setDefaultSort('stit_ID', 'ASC');
            return store;
        };

        var createLiveStockDCM = function(){
            return new Ext.grid.ColumnModel([new Ext.grid.RowNumberer(),
                {
                    header: 'SO Date',
                    id:'pfstSODateCol',
                    sortable: true,
                    hideable: true,
                    hidden:false,
                    dataIndex: 'SODate',
                    tooltip: 'Sales Order Number',
                    width: 200
                },{
                    header: 'SO Number',
                    id:'pfstSONumber',
                    sortable: true,
                    hideable: false,
                    align: 'left',
                    dataIndex: 'SONumber',
                    tooltip: 'Sales Order Number'
                }, {
                    header: 'Branch',
                    id:'pfstBranchCol',
                    sortable: true,
                    hideable: true,
                    hidden:false,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch Name'
                },{
                    header: 'Customer Name',
                    id:'pfstCustomerCol',
                    sortable: true,
                    align: 'left',
                    hidable:true,
                    hidden:false,
                    dataIndex: 'customer_Name',
                    tooltip: 'Customer Name'
                },{
                    header: 'Customer Mobile',
                    id:'pfstCustMobileCol',
                    sortable: true,
                    hideable: true,
                    hidden:false,
                    dataIndex: 'customer_Mobile',
                    tooltip: 'Customer Mobile'
                },
                {
                    header: 'Invoice Amt Items',
                    id:'pfstInvAmtItemsCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'invValAtax',
                    tooltip: 'Invoice Amount of Items'
                },            
                {
                    header: 'GST / VAT',
                    id:'pfstGSTCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'GST',
                    tooltip: 'GST / VAT'
                },
                {
                    header: 'Purchase EPR',
                    id:'pfstEPRCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'purchaseEPR',
                    tooltip: 'EPR'
                },
                {
                    header: 'Gross Profit',
                    id:'pfstGrossProfitCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'grossProfit',
                    tooltip: 'Gross Profit'
                },                
                {
                    header: 'Gross Profit Percentage',
                    id:'pfstGrPftPercCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'grossProfitPercentage',
                    tooltip: 'Gross Profit Percentage'
                }, 
                {
                    header: 'Incentive',
                    id:'pfstIncentiveCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'incentitve',
                    tooltip: 'Incentive'
                }, 
                {
                    header: 'Technology',
                    id:'pfstTechnologyCol',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'technology',
                    tooltip: 'Technology'
                }, 
                {
                    header: 'Actions',
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    items: [
    
                    ]
                }]);
        }

        var getPFBrStore = function(){
            var BranchStore = new Ext.data.JsonStore({
                fields: ['br_ID', 'br_Name'],
                url: modURL + '&op=getViewableBranches',
                autoLoad: true,
                method: 'post',
            });
    
            return BranchStore;
        }
    
        var masterPanelforPSGrid = function(_pStmtGridPanelId){
            var _pftyStmtStore = pftyStmtStore();
            var pfst_Branch = getPFBrStore();
            var _pftyStmt_filter = new Ext.ux.grid.GridFilters({
                filters: [
                    {
                        type: 'string',
                        dataIndex: 'br_Name'
                    }, 
                    {
                        type: 'date',
                        dataIndex: 'SODate'
                    },
                    {
                        type: 'string',
                        dataIndex: 'SONumber'
                    }, 
                    {
                        type: 'string',
                        dataIndex: 'customer_Name'
                    }, 
                    {
                        type: 'string',
                        dataIndex: 'customer_Mobile'
                    }, 
                    {
                        type: 'numeric',
                        dataIndex: 'invValAtax'
                    },
                    {
                        type: 'numeric',
                        dataIndex: 'GST'
                    },
                    {
                        type: 'numeric',
                        dataIndex: 'purchaseEPR'
                    },
                    {
                        type: 'numeric',
                        dataIndex: 'grossProfit'
                    },
                    {
                        type: 'numeric',
                        dataIndex: 'grossProfitPercentage'
                    },
                    {
                        type: 'numeric',
                        dataIndex: 'incentitve'
                    },
                    {
                        type: 'numeric',
                        dataIndex: 'technology'
                    }
                ]
            });
    

            var sm =  new Ext.grid.RowSelectionModel({
                singleSelected: true
            });
    
            var grid_panel = new Ext.grid.GridPanel({
                store: _pftyStmtStore,
                layout: 'fit',
                frame: false,
                border: false,
                title: 'Profitability Statement',
                iconCls: 'profitability_statement',
                plugins: [_pftyStmt_filter],
                id: _pStmtGridPanelId,
                loadMask: true,
                cm:createLiveStockDCM(),
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                sm:sm,
                listeners: {
                    resize: updatePagination,
                    //viewready: updatePagination,
                    afterrender: function () {
    
                    }
                },
                tbar: [

                    {
                        html: '&nbsp;From Date: &nbsp;'
                    }, {
                        fieldLabel: 'From',
                        xtype: 'datefield',
                        id: 'pfst_search_from',
                        name: 'n[search_from]',
                        anchor: '98%',
                        width: 100,
                        editable: true,
                        allowBlank: false,
                        value: new Date().add(Date.DAY, -(dateRange)),
                        format: 'd/m/Y'
                    }, {
                        html: '&nbsp;To Date: &nbsp;'
                    }, {
                        fieldLabel: 'To',
                        xtype: 'datefield',
                        id: 'pfst_search_to',
                        name: 'n[search_to]',
                        anchor: '98%',
                        width: 100,
                        editable: true,
                        allowBlank: false,
                        value: (new Date()),
                        format: 'd/m/Y'
                    },
                    {
                        html: '&nbsp;BRANCH : &nbsp;',
                        id: 'lsbranch_label',
                    },
                    {
                        xtype: 'lovcombo',
                        displayField: 'br_Name',
                        valueField: 'br_ID',
                        mode: 'remote',
                        id: 'pfstLSBranch',
                        emptyText: 'Select Branch(s)',
                        name:'br_Name',
                        selectOnFocus:true,
                        forceSelection:true,
                        hiddenName: 'br_ID',
                        typeAhead: false,
                        triggerAction: 'all',
                        lazyRender: true,
                        tabIndex: 413,
                        minChars:1,
                        anchor: '98%',
                        store: pfst_Branch,
                        editable: true
                    },
                    {
                        html: '&nbsp; &nbsp; &nbsp; Exclude Return : &nbsp;'
                    },
                    {
                        fieldLabel: 'Esclude Return',
                        xtype: 'checkbox',
                        id: 'pfst_exclude_return',
                        name: 'pfst_exclude_return',
                        anchor: '98%',
                        listeners: {
                            check: function (cbo, checked) {
    
                            }
                        }
                    },
                    '-', 
                    {
                        text: 'SUBMIT',
                        iconCls: 'finascop_search_btn',
                        id: 'pfst_filter_button',
                        style: 'margin-left:10px;',
                        handler: function () {
                            Application.RetalineCurrentStock.Cache.ProfitStmtInitatedOnly = false;
                            Ext.getCmp('pfstGPanelID').getStore().load(
                                {
                                    params: {
                                        br_IDs :  Ext.isEmpty(Ext.getCmp('pfstLSBranch').getValue())?'Combined':Ext.getCmp('pfstLSBranch').getValue(),
                                        start: 0,
                                        limit: recs_per_page
                                    },
                                    callback:function(records, optoins, success){
                                        
                                        if(success == true){
                                           
                                        }
                                        if(success == false){
                                            Ext.Msg.alert('Error', "Error");
                                        }
                                    }
                                }
                            );
                        }
                    }
                    ,'-', {
                        xtype: 'button',
                        text: 'Reset',
                        id: 'pfst_reset_button',
                        iconCls: 'finascop_my-resetpass',
                        handler: function () {
                            Ext.getCmp('cmbLSSKU').reset();
                            Ext.getCmp('cmbLSBranch').reset();
                            Ext.getCmp('cmbLSMRPGrouping').reset();
                            Ext.getCmp('lscb_exclude_zerostock').reset();
                            Ext.getCmp('cmbLSSupplier').reset();
                            Ext.getCmp('panelMasterLiveStock').getStore().removeAll();
                        }
                    }
                ],
                bbar: new Ext.PagingToolbar({
                    pageSize: recs_per_page,
                    store: _pftyStmtStore,
                    plugins: [_pftyStmt_filter],
                    displayInfo: true,
                    displayMsg: 'Displaying records {0} - {1} of {2}',
                    emptyMsg: "No records to display"
                }),
                stripeRows: true
            });
            return grid_panel;
        }    


    }//ProfitabilityStatement end 

    return {
        Cache: {},
        initProfitabilityStatement: function () {
            var _pfstGridPanelId = 'pfstGPanelID';
            var _masterPanelPS = Ext.getCmp(_pfstGridPanelId);
            if (Ext.isEmpty(_masterPanelPS)) {
                _masterPanelPS = masterPanelforPSGrid(_pfstGridPanelId);
                Application.UI.addTab(_masterPanelPS);
                _masterPanelPS.doLayout();
            } else {
                Application.UI.addTab(_masterPanelPS);
            }
        }
    }
}();