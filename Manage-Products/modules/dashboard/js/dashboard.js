Application.Dashboard = function () {
    var recs_per_page = 15
    var winsize = Ext.getBody().getViewSize()
    var modURL = '?module=dashboard';
    // var approvalStore="approvalStore"
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var CmpRendered = false;
    var loadingMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Please wait...Building components of Dashboard...'})
    var creatMainPanel = function (id) {
        return new Ext.Panel({
            border: false,
            region: 'center',
            hideBorders: true,
            iconCls: 'project_dashboard',
            title: 'Scheduler Details',
            layout: 'fit',
            autoScroll: true,
            bodyStyle: 'background-color:white;',
            // items: dashboard_items(),
            items: [schedulerGrid()],
            id: id,
            listeners: {
                "afterrender": function () {
                    setTimeout(function () {
                    }, 200);

                }
            }
        });
    };
    var schedulerStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            waitMsg: 'Please wait...',
            url: modURL + '&op=schedulerGridStore',
            method: 'post',
            fields: ['prlk_name', 'prlk_status', 'prlk_updtime', 'prlk_isenabled', 'prlk_email', 'prlk_Description', 'prlk_interval', 'minuteDiff'],
            totalProperty: 'totalCount',
            root: 'data',
            //remoteSort: true
        });
        return store;
    };
    var schedulerGrid = function () {
        var store = schedulerStore();
        var grid_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'prlk_name'
                }]
        });
        grid_filter.remote = true;
        grid_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: store,
            loadMask: true,
            region: 'center',
            frame: false,
            border: true,
            title: '',
            height: 200,
            autoScroll: true,
            stripeRows: true,
            plugins: [grid_filter],
            style: 'margin:5px',
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('prlk_status') == 1 && record.get('minuteDiff') > 30)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else if (record.get('prlk_status') == 0 && record.get('minuteDiff') > 30)
                    {
                        return 'finascop_indicateColLIGHTYELLOW';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            id: 'runningEventsForDashboard',
            columns: [
                {
                    header: 'Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_name',
                    tooltip: 'Name',
                }, {
                    header: 'Email',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_email',
                    tooltip: 'Email',
                    width: 100
                }, {
                    header: 'Description',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'prlk_Description',
                    tooltip: 'Description',
                    width: 100
                }, {
                    header: 'Updated Time',
                    sortable: true,
                    dataIndex: 'prlk_updtime',
                    tooltip: 'Updated Time',
                    width: 100
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: '',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var prlk_status = data.prlk_status;
                                var minuteDiff = data.minuteDiff;
                                if ((prlk_status == 1) && (minuteDiff > 30)) {
                                    this.items[0].tooltip = 'Reset';
                                    return 'assign';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var prlk_name = record.data.prlk_name;
                                Ext.Ajax.request({
                                    url: modURL + '&op=changeStatus',
                                    method: 'POST',
                                    params: {
                                        prlk_name: prlk_name
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true) {
                                            var tmp = Ext.decode(response.responseText);
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('runningEventsForDashboard').getStore().load();
                                        }
                                        else {
                                            Ext.MessageBox.alert("Notification", tmp.msg);
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            }
                        }]
                }],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            })
        });
        return grid_panel;
    };

    var overallViewStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=overallGridStore',
                method: 'post'
            }),
            fields: ['RetailCategory','Department','category','sub_category','productCount','business_type_id'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            sortInfo: {
                field: 'business_type_id',
                direction: "ASC"
            },
            listeners: {
                load: function () {
                }
            }
        });
        return store;        
    };
    var issueGrid = function () {
        var store = overallViewStore();
        var grid_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'RetailCategory'
                },{
                    type: 'string',
                    dataIndex: 'Department'
                },{
                    type: 'string',
                    dataIndex: 'sub_category'
                },{
                    type: 'string',
                    dataIndex: 'productCount'
                }]
        });
        grid_filter.remote = true;
        grid_filter.autoReload = true;
        var grid_panel = new Ext.grid.GridPanel({
            store: store,
            loadMask: true,
            region: 'center',
            frame: false,
            border: true,
            title: 'Subcategory Wise Product Count',
            height: 400,
            autoScroll: true,
            stripeRows: true,
            plugins: [grid_filter],
            style: 'margin:5px',
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            id: 'overallForDashboard',
            columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'Retail Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'RetailCategory',
                    tooltip: 'Retail Category',
                }, {
                    header: 'Department',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'Department',
                    tooltip: 'Department'
                }, {
                    header: 'Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'category',
                    tooltip: 'Category'
                }, {
                    header: 'Sub Category',
                    sortable: true,
                    dataIndex: 'sub_category',
                    tooltip: 'Sub Category'
                },{
                    header: 'Product Count',
                    sortable: true,
                    dataIndex: 'productCount',
                    tooltip: 'Product Count',
                    width: 100
                }],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination,
                afterrender: function () {
                }
            },
            tbar: [],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [grid_filter]
            })
        });
        return grid_panel;
    };
    var currentStockStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=brandWisePrdctCount',
                method: 'post'
            }),
            fields: ['brand_id','brand_name','manufacture_id','manufacture_name','productCount'],
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            sortInfo: {
                field: 'brand_id',
                direction: "ASC"
            },
            listeners: {
                load: function () {
                }
            }
        });
        return store;
    };
    var currentStock = function () {
        var brand_product_store = currentStockStore();
        var branch_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'manufacture_name'
                }]
        });
        branch_filter.remote = true;
        branch_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: brand_product_store,
            layout: 'fit',
            height: 400,
            autoScroll: true,
            frame: false,
            border: false,
            title: 'Brand Wise Product count',
            plugins: [branch_filter],
            id: 'dashboard_brand_product',
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Manufacture',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'manufacture_name',
                    tooltip: 'Manufacture',
                    width: 200
                },
                {
                    header: 'Brand',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'brand_name',
                    tooltip: 'Brand'
                },  
                {
                    header: 'Product Count',
                    sortable: true,
                    hideable: false,
                    align: 'right',
                    dataIndex: 'productCount',
                    tooltip: 'Product Count'
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
                resize: updatePagination,
            },
            tbar: [],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: brand_product_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
        });
        return grid_panel;
    };
    var overallViewGrid = function () {
        var subcat_product_store = overallViewStore();
        var subcat_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                type: 'string',
                dataIndex: 'RetailCategory'
            },{
                type: 'string',
                dataIndex: 'Department'
            },{
                type: 'string',
                dataIndex: 'sub_category'
            },{
                type: 'string',
                dataIndex: 'productCount'
            }]
        });
        subcat_filter.remote = true;
        subcat_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: subcat_product_store,
            layout: 'fit',
            height: 400,
            autoScroll: true,
            frame: false,
            border: false,
            title: 'Sub Category Wise Product count',
            plugins: [subcat_filter],
            id: 'overallForDashboard',
            loadMask: true,
            columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'Retail Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'RetailCategory',
                    tooltip: 'Retail Category',
                }, {
                    header: 'Department',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'Department',
                    tooltip: 'Department'
                }, {
                    header: 'Category',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'category',
                    tooltip: 'Category'
                }, {
                    header: 'Sub Category',
                    sortable: true,
                    dataIndex: 'sub_category',
                    tooltip: 'Sub Category'
                },{
                    header: 'Product Count',
                    sortable: true,
                    dataIndex: 'productCount',
                    tooltip: 'Product Count',
                    width: 100
                }],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                resize: updatePagination,
            },
            tbar: [],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: subcat_product_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
        });
        return grid_panel;
    };
    return {
        loadCharts: function () {
            Ext.get('chartCombination').dom.src = modURL + '&op=chartCombination';

            Application.Dashboard.ViewMode();
        },
        ViewMode: function () {
            
            
                    },
        init: function () {
            Application.UI.switchMainView(new Ext.Panel({
                xtype: 'panel',
                border: false,
                hideBorders: true,
                iconCls: 'project_dashboard',
                title: 'Dashboard',
                layout: 'border',
                autoScroll: true,
                bodyStyle: 'background-color:white;',
                items: [{
                        region: 'center',
                        layout: 'column',
                        autoHeight: true,
                        border: false,
                        items: [/* <?php if (user_access("dashboard", "chartCombination")) { ?> */{
                                layout: 'form',
                                columnWidth: 1,
                                hideBorders: true,
                                items: [{
                                        height: 300,
                                        border: false,
                                        collapseFirst: false,
                                        html: '<iframe style="border:none" scrolling="no" id="chartCombination" width="100%" height="100%" ></iframe>'
                                    }]
                            }, /* <?php } ?> */ /* <?php if (user_access("dashboard", "brandWisePrdctCount")) { ?> */ {
                                layout: 'form',
                                columnWidth: 1,
                                hideBorders: true,
                                items: currentStock()
                            }, /* <?php } ?> *//* <?php if (user_access("dashboard", "overallGridStore")) { ?> */ {
                                layout: 'form',
                                columnWidth: 1,
                                hideBorders: true,
                                items: overallViewGrid()
                            }, /* <?php } ?> */{
                                layout: 'form',
                                columnWidth: 1,
                                hideBorders: true,
                                items: [{
                                        layout: 'column',
                                        hideBorders: true,
                                        items: [
                                            {
                                                layout: 'form',
                                                columnWidth: 0.30,
                                                hideBorders: true,
                                                items: [{
                                                        height: 200,
                                                        border: false,
                                                        collapseFirst: false,
                                                        html: '<iframe style="border:none" scrolling="no" id="lineChartMonthlySales" width="100%" height="100%" ></iframe>'
                                                    }]
                                            }, {
                                                layout: 'form',
                                                columnWidth: 0.30,
                                                hideBorders: true,
                                                items: [{
                                                        height: 200,
                                                        border: false,
                                                        collapseFirst: false,
                                                        html: '<iframe style="border:none" scrolling="no" id="pieChartDailySales" width="100%" height="100%" ></iframe>'
                                                    }]
                                            }, {
                                                layout: 'form',
                                                columnWidth: 0.30,
                                                hideBorders: true,
                                                items: [{
                                                        height: 200,
                                                        border: false,
                                                        collapseFirst: false,
                                                        html: '<iframe style="border:none" scrolling="no" id="barGraphBranchSales" width="100%" height="100%" ></iframe>'
                                                    }]
                                            }, {}]
                                    }]
                            }]
                    }],
                id: 'chartsPanel',
                listeners: {
                    "afterrender": function () {
                        setTimeout(function () {
                            Application.Dashboard.loadCharts();
                        }, 200);

                    }
                }
            }));

        },
        CRMainPanel: function () {
            Application.UI.switchMainView(new Ext.Panel({
                id: 'dashboard-panel',
                title: 'Dashboard',
                xtype: 'portal',
                region: 'center',
                closable: true,
                border: false,
                forceFit: true,
                autoScroll: true,
                layout: 'column',
                items: [{
                        columnWidth: .5,
                        border: false,
                        style: 'margin:5px',
                        items: [creatMainPanel('proceeLockPanel')]
                    }],
                listeners: {
                    afterrender: function () {
                        setTimeout(function () {

                        }, 7000);
                    }
                }
            }));
        },
        proceessLock: function () {
            var panelId = 'proceeLockPanel';
            var panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(panel)) {
                panel = creatMainPanel(panelId);
                Application.UI.addTab(panel);
                //panel.doLayout();
            } else {
                Application.UI.addTab(panel);
                //panel.doLayout();
            }
            return panel;
        }, activeWidgets: {
            "widget_0": "on",
            "widget_1": "on",
            "widget_2": "on"

        },
        initGA: function () {
            var winHeight = winsize.height * .5;
            Ext.Ajax.request({
                waitMsg: 'Dashboard is loading..',
                url: modURL + '&op=generateToken',
                success: function (response, options) {
                    var result = Ext.decode(response.responseText);
                    console.log('result');
                    console.log(result);

                    if (result) {

                        (function (w, d, s, g, js, fs) {
                            g = w.gapi || (w.gapi = {});
                            g.analytics = {q: [], ready: function (f) {
                                    this.q.push(f);
                                }};
                            js = d.createElement(s);
                            fs = d.getElementsByTagName(s)[0];
                            js.src = 'https://apis.google.com/js/platform.js';
                            fs.parentNode.insertBefore(js, fs);
                            js.onload = function () {
                                g.load('analytics');
                            };
                        }(window, document, 'script'));

                        Application.UI.switchMainView(new Ext.Panel({
                            id: 'dashboard-panel',
                            title: 'Google Analytics Chart',
                            xtype: 'portal',
                            region: 'center',
                            collapsible: true,
                            border: false,
                            forceFit: true,
                            autoScroll: true,
                            layout: 'column',
                            items: [
                                {
                                    columnWidth: .33,
                                    border: false,
                                    style: 'margin:5px',
                                    items: [{
                                            title: "Visitors",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-1-container"></div>'
                                        }, {
                                            title: "Pageviews",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-2-container"></div>'
                                        }]
                                }, {
                                    columnWidth: .33,
                                    border: false,
                                    style: 'margin:5px',
                                    items: [{
                                            title: "Geolocations",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-3-container"></div>'
                                        }, {
                                            title: "Top Devices",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-5-container"></div>'
                                        }]
                                }, {
                                    columnWidth: .33,
                                    border: false,
                                    style: 'margin:5px',
                                    items: [{
                                            title: "Top Countries",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-4-container"></div>'
                                        }, {
                                            title: "Visitors",
                                            height: 350,
                                            border: true,
                                            collapseFirst: false,
                                            html: '<div id="chart-6-container"></div>'
                                        }]
                                }],
                            listeners: {
                                afterrender: function () {
                                    setTimeout(function () {
                                        if (Application.UI.loadingMaskDash())
                                            Application.UI.loadingMaskDash().hide();
                                    }, 7000);
                                }
                            }
                        }));

                        var tokenacces = result.access_token;
                        gapi.analytics.ready(function () {

                            gapi.analytics.auth.authorize({
                                'serverAuth': {
                                    'access_token': tokenacces

                                }
                            });



                            /**
                             * Authorize the user immediately if the user has already granted access.
                             * If no access has been created, render an authorize button inside the
                             * element with the ID "embed-api-auth-container".
                             */
                            /*gapi.analytics.auth.authorize({
                             container: 'embed-api-auth-container',
                             clientid: clientId
                             });*/


                            /**
                             * Create a new ViewSelector instance to be rendered inside of an
                             * element with the id "view-selector-container".
                             */
                            var dataChart1 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'start-date': '30daysAgo',
                                    'end-date': 'today',
                                    'metrics': 'ga:visitors',
                                    'dimensions': 'ga:date'
                                },
                                chart: {
                                    'container': 'chart-1-container',
                                    'type': 'LINE',
                                    hAxis: {title: 'Date'},
                                    vAxis: {title: 'Visits'},
                                    'options': {
                                        'width': '100%',
                                        'fontSize': 12,
                                        'title': 'Visitors in site'
                                    }
                                }
                            });
                            dataChart1.execute();


                            /**
                             * Creates a new DataChart instance showing top 5 most popular demos/tools
                             * amongst returning users only.
                             * It will be rendered inside an element with the id "chart-3-container".
                             */
                            var dataChart2 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'start-date': '30daysAgo',
                                    'end-date': 'today',
                                    'metrics': 'ga:pageviews',
                                    'dimensions': 'ga:pagePathLevel1',
                                    'sort': '-ga:pageviews',
                                    'filters': 'ga:pagePathLevel1!=/',
                                    'max-results': 20
                                },
                                chart: {
                                    'container': 'chart-2-container',
                                    'type': 'PIE',
                                    'title': 'Pageviews in site',
                                    'options': {
                                        'width': '100%',
                                        'pieHole': 4 / 9,
                                    }
                                }
                            });
                            dataChart2.execute();

                            /**/
                            var dataChart3 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'start-date': '30daysAgo',
                                    'end-date': 'today',
                                    'metrics': 'ga:visitors',
                                    'dimensions': 'ga:country'
                                },
                                chart: {
                                    'container': 'chart-3-container',
                                    'type': 'GEO',
                                    'title': 'Visitors in site',
                                    'options': {
                                        'width': '100%'
                                    }
                                }
                            });
                            dataChart3.execute();

                            /**/
                            var dataChart4 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'dimensions': 'ga:country',
                                    'metrics': 'ga:sessions',
                                    'sort': '-ga:sessions',
                                    'max-results': 5
                                },
                                chart: {
                                    'container': 'chart-4-container',
                                    'type': 'PIE',
                                    'title': 'Top Browsers',
                                    'options': {
                                        'width': '100%',
                                        'pieHole': 4 / 9
                                    }
                                }
                            });
                            dataChart4.execute();

                            /**/
                            var dataChart5 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'dimensions': 'ga:deviceCategory',
                                    'metrics': 'ga:sessions',
                                    'sort': '-ga:sessions',
                                    'max-results': 5
                                },
                                chart: {
                                    'container': 'chart-5-container',
                                    'type': 'BAR',
                                    'title': 'Top Devices',
                                    'options': {
                                        'width': '100%'
                                    }
                                }
                            });
                            dataChart5.execute();

                            /**/
                            var dataChart6 = new gapi.analytics.googleCharts.DataChart({
                                query: {
                                    'ids': 'ga:183384251', // <-- Replace with the ids value for your view.
                                    'dimensions': 'ga:userType',
                                    'metrics': 'ga:users',
                                    'sort': '-ga:users',
                                    'max-results': 5
                                },
                                chart: {
                                    'container': 'chart-6-container',
                                    'type': 'PIE',
                                    'title': 'Top Devices',
                                    'options': {
                                        'width': '100%',
                                        'pieHole': 4 / 9
                                    }
                                }
                            });
                            dataChart6.execute();

                            var actuser = new gapi.client.analytics.data.realtime.get({
                                'ids': 'ga:183384251',
                                metrics: 'rt:activeUsers',
                                container: 'chart-7-container',
                                pollingInterval: 5
                            });
                            actuser.execute();
                        });
                    } else {
                        Ext.MessageBox.alert("Notification", "Token generation failed");
                    }
                }
            });
            // blocking for a production release


        }

    }
}();