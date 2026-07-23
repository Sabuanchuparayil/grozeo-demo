/* global Ext, ACTION_FAIL, Application */

Application.RetalineHomePage = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_homepage';
    var recs_per_page = 12;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }

    var loadRetalineHomePage = function () {
        var RetalineHomePage_grid = Ext.getCmp('RetalineHomePage_master_grid');
        RetalineHomePage_grid.getStore().removeAll();
        RetalineHomePage_grid.getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    };



    var RetalineHomePageMasterStore = function () {
        var RetalineHomePage_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listRetalineHomePage',
            fields: ['id', 'screen', 'type', 'type_id', 'updated_at', 'is_active'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true
        });
        return RetalineHomePage_store;
    };

    var changePageStatus = function (pageID, pageStatus, grid, rowIndex, type) {
        var data = Ext.pluck(grid.store.getRange(), 'data');
        Ext.Ajax.request({
            waitMsg: 'Processing',
            url: modURL,
            params: {
                op: 'changePageStatus',
                pageID: pageID,
                pageStatus: pageStatus === '1' ? 'Active' : 'Inactive',
                HomePageListdata: Ext.encode(data),
                type: type
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', ACTION_FAIL);
            },
            success: function (response, options) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success == true)
                {
                    var record = grid.store.getAt(rowIndex);
                    record.set('is_active', record.get('is_active') == '1' ? '0' : '1');
                    record.commit();
                }
            }
        });
    };
    var RetalineHomePageGrid = function () {
        var RetalineHomePage_master_store = RetalineHomePageMasterStore();
        // var item_store = itemComboStore();
        var del_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'screen'
                }, {
                    type: 'string',
                    dataIndex: 'type'
                }]
        });
        del_filter.remote = true;
        del_filter.autoReload = true;
        var RetalineHomePage_entries_grid = new Ext.grid.GridPanel({
            store: RetalineHomePage_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            ddGroup: 'testDDGroup',
            enableDragDrop: true,
            id: 'RetalineHomePage_master_grid',
            title: 'Delivery',
            plugins: [del_filter],
            style: 'margin-bottom:3px;',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Screen Name',
                    sortable: false,
                    width: 120,
                    dataIndex: 'screen',
                    tooltip: 'Screen Name'
                },
                {
                    header: 'Type',
                    sortable: false,
                    width: 120,
                    dataIndex: 'type',
                    tooltip: 'Type'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [
                        {
                            getClass: function (v, meta, rec) {
                                if (rec.get('is_active') === '1')
                                {
                                    return 'status_enabled';
                                } else
                                {
                                    return 'status_disabled';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                changePageStatus(record.get('id'), record.get('is_active'), grid, rowIndex, 'delivery');
                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            listeners: {
                render: function (g) {
                    var ddrow = new Ext.ux.dd.GridReorderDropTarget(g, {
                        copy: false
                        , listeners: {
                            beforerowmove: function (objThis, oldIndex, newIndex, records) {
                                // code goes here
                                // return false to cancel the move
                            }
                            , afterrowmove: function (objThis, oldIndex, newIndex, records) {
                                // code goes here
                                var data = Ext.pluck(records[0].store.getRange(), 'data');
                                Ext.Ajax.request({
                                    url: modURL + '&op=saveRetalineHomePageOrder',
                                    method: 'POST',
                                    params: {
                                        "HomePageListdata": Ext.encode(data),
                                        "type": 'delivery'
                                    },
                                    success: function (resp) {
                                        var res = Ext.decode(resp.responseText);
                                        if (res.success === true)
                                        {
                                            Ext.Msg.hide();

                                        }
                                    },
                                    failure: function (elm, conf) {
                                        if (conf.failureType === 'server')
                                        {
                                            var result = Ext.decode(conf.response.responseText);
                                            Ext.Msg.alert('Notification', result.error);
                                        } else
                                        {
                                            var result = Ext.decode(conf.response.responseText);
                                            Ext.MessageBox.alert('Error', result.error);
                                        }
                                    }
                                });
                            }
                            , beforerowcopy: function (objThis, oldIndex, newIndex, records) {
                                // code goes here
                                // return false to cancel the copy
                            }
                            , afterrowcopy: function (objThis, oldIndex, newIndex, records) {
                                // code goes here
                            }
                        }
                    });

                    // if you need scrolling, register the grid view's scroller with the scroll manager
                    Ext.dd.ScrollManager.register(g.getView().getEditorParent());
                }
                , beforedestroy: function (g) {
                    // if you previously registered with the scroll manager, unregister it (if you don't it will lead to problems in IE)
                    Ext.dd.ScrollManager.unregister(g.getView().getEditorParent());
                }
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            tbar: [{html: '&nbsp; Search by Screen Name : &nbsp;'},
                {
                    xtype: 'textfield',
                    width: 300,
                    text: 'Search by Screen Name',
                    id: 'screen',
                    name: 'screen'
                }
                , '-', {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        RetalineHomePage_master_store.baseParams = {
                            ScreenName: Ext.getCmp('screen').getValue()
                        };
                        RetalineHomePage_master_store.load();
                    }
                }],
            stripeRows: true
        });
        return RetalineHomePage_entries_grid;
    };
    var RatalineHomeCollectMasterStore = function () {
        var RetalineHomePage_store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listRatalineHomeCollect',
            fields: ['id', 'screen', 'type', 'type_id', 'updated_at', 'is_active'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true
        });
        return RetalineHomePage_store;
    };
    var RatalineHomeCollectGrid = function () {
        var RatalineHomeCollect_master_store = RatalineHomeCollectMasterStore();
        var coll_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'screen'
                }, {
                    type: 'string',
                    dataIndex: 'type'
                }]
        });
        coll_filter.remote = true;
        coll_filter.autoReload = true;
        var RatalineHomeCollect_entries_grid = new Ext.grid.GridPanel({
            store: RatalineHomeCollect_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            ddGroup: 'testDDGroup',
            plugins: [coll_filter],
            enableDragDrop: true,
            id: 'RatalineHomeCollect_master_grid',
            title: 'Collect',
            style: 'margin-bottom:3px;',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Screen Name',
                    sortable: false,
                    width: 120,
                    dataIndex: 'screen',
                    tooltip: 'Screen Name'
                },
                {
                    header: 'Type',
                    sortable: false,
                    width: 120,
                    dataIndex: 'type',
                    tooltip: 'Type'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [
                        {
                            getClass: function (v, meta, rec) {
                                if (rec.get('is_active') === '1')
                                {
                                    return 'status_enabled';
                                } else
                                {
                                    return 'status_disabled';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                changePageStatus(record.get('id'), record.get('is_active'), grid, rowIndex, 'collect');
                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            listeners: {render: function (g) {
                    var ddrow = new Ext.ux.dd.GridReorderDropTarget(g, {
                        copy: false
                        , listeners: {
                            beforerowmove: function (objThis, oldIndex, newIndex, records) {
                                // code goes here
                                // return false to cancel the move
                            }
                            , afterrowmove: function (objThis, oldIndex, newIndex, records) {
                                // code goes here
                                var data = Ext.pluck(records[0].store.getRange(), 'data');
                                console.log('data', data);
                                Ext.Ajax.request({
                                    url: modURL + '&op=saveRetalineHomePageOrder',
                                    method: 'POST',
                                    params: {
                                        "HomePageListdata": Ext.encode(data),
                                        "type": 'collect'
                                    },
                                    success: function (resp) {
                                        var res = Ext.decode(resp.responseText);
                                        if (res.success === true)
                                        {
                                            Ext.Msg.hide();

                                        }
                                    },
                                    failure: function (elm, conf) {
                                        if (conf.failureType === 'server')
                                        {
                                            var result = Ext.decode(conf.response.responseText);
                                            Ext.Msg.alert('Notification', result.error);
                                        } else
                                        {
                                            var result = Ext.decode(conf.response.responseText);
                                            Ext.MessageBox.alert('Error', result.error);
                                        }
                                    }
                                });
                            }
                            , beforerowcopy: function (objThis, oldIndex, newIndex, records) {
                                // code goes here
                                // return false to cancel the copy
                            }
                            , afterrowcopy: function (objThis, oldIndex, newIndex, records) {
                                // code goes here
                            }
                        }
                    });

                    // if you need scrolling, register the grid view's scroller with the scroll manager
                    Ext.dd.ScrollManager.register(g.getView().getEditorParent());
                }
                , beforedestroy: function (g) {
                    // if you previously registered with the scroll manager, unregister it (if you don't it will lead to problems in IE)
                    Ext.dd.ScrollManager.unregister(g.getView().getEditorParent());
                }
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            tbar: [{html: '&nbsp; Search by Screen Name : &nbsp;'},
                {
                    xtype: 'textfield',
                    width: 300,
                    text: 'Search by Screen Name',
                    id: 'screenCollect',
                    name: 'screen'
                }
                , '-', {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {
                        RatalineHomeCollect_master_store.baseParams = {
                            ScreenName: Ext.getCmp('screenCollect').getValue()
                        };
                        RatalineHomeCollect_master_store.load();
                    }
                }],
            stripeRows: true
        });
        return RatalineHomeCollect_entries_grid;
    };
    return {
        Cache: {},
        initRetalineHomePage: function () {
            var panelId = 'RetalineHomePage_panel';
            var RetalineHomePage_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(RetalineHomePage_panel))
            {
                RetalineHomePage_panel = RetalineHomePageGrid(panelId);
                Application.UI.addTab(RetalineHomePage_panel);
                RetalineHomePage_panel.doLayout();
            } else
            {
                Application.UI.addTab(RetalineHomePage_panel);
                RetalineHomePage_panel.doLayout();
            }


        }, initRatalineHomeCollect: function () {
            var panelId = 'RatalineHomeCollect_panel';
            var RatalineHomeCollect_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(RatalineHomeCollect_panel))
            {
                RatalineHomeCollect_panel = RatalineHomeCollectGrid(panelId);
                Application.UI.addTab(RatalineHomeCollect_panel);
                RatalineHomeCollect_panel.doLayout();
            } else
            {
                Application.UI.addTab(RatalineHomeCollect_panel);
                RatalineHomeCollect_panel.doLayout();
            }


        }
    };
}();













