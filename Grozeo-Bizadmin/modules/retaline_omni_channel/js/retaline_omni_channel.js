Application.RetalineOmni = function () {

    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=retaline_omni_channel';
    var outmodURL = '?module=outbound_calls';
    var recs_per_page = 16;
    var WinMask;
    var imgpath = IMAGE_BASE_PATH;
    var mymarker;
    var centerParam;

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    ;
    var onGridResize = function (cmp) {
        recs_per_page = updateRecsPerPage(cmp);
    };
    var qtipRenderer = function (value, metadata, record, rowIndex, colIndex, store) {


        metadata.attr = 'ext:qtip="' + value + '"  ext:qwidth="auto" ';
        return value;

    };

    var omniMainTabPanel = function (id,contact) {
        var src = modURL + '&op=order_details_view&order_auto_id=0';
        var iframesrc = Application.RetalineOmni.Cache.loginpath;
        var _leadPanel = new Ext.Panel({
            layout: 'column',
            border: false,
            frame: true,
            bodyStyle: {"background-color": "white"},
            title: 'Customer Suppport',
            hideBorders: true,
            id: id,
            tbar: [{
                    xtype: 'textfield',
                    tabIndex: 602,
                    vtype: 'phonespec',
                    id: 'omniSearchParameter',
                    name: 'omniSearchParameter',
                    emptyText: 'Enter Mobile Number',
                    allowBlank: false,
                    anchor: '95%',
                    maxLength: 100,
                    listeners: {
                        /*specialkey: function (field, e) {
                            var customerPhone = Ext.getCmp('omniSearchParameter').getValue();
                            if (!Ext.isEmpty(customerPhone)) {
                                checkCustomerExistance(customerPhone);

                            } else {
                                Ext.MessageBox.alert('Error', "Check the data entered");
                            }
                        }*/
                    }
                }, {
                    xtype: 'button',
                    text: 'Load',
                    tabIndex: 603,
                    id: 'showBtnto',
                    iconCls: 'rollback',
                    style: "padding-left: 10px;",
                    handler: function () {
                        var customerPhone = Ext.getCmp('omniSearchParameter').getValue();
                        searchCustomer(customerPhone,'direct');
                        

                    }
                }, {
                    xtype: 'button',
                    text: 'Search',
                    id: 'showBtnSearchcust',
                    iconCls: 'finascop_search_btn',
                    style: "padding-left: 10px;",
                    handler: function () {
                        CustomerSearch();
                    }
                },{
                    xtype: 'button',
                    text: 'Call',
                    tooltip: 'Call',
                    hidden: true,
                    id: 'customerCall',
                    icon: './resources/images/default/icons/call.png',
                    handler: function () {
                        var customerPhone = Ext.getCmp('omniSearchParameter').getValue();
                        if (!Ext.isEmpty(customerPhone)) {
                            Ext.Ajax.request({
                                url: modURL + '&op=outboundCall',
                                method: 'POST',
                                params: {
                                    phone: customerPhone,
                                    jobId : Application.OutboundCalls.Cache.jobId
                                },
                                success: function (response) {
                                     var tmp = Ext.decode(response.responseText);
                    if (tmp.success == false) {
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }else{
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', 'Issue in saving');
                                }
                            });
                            /*Ext.Ajax.request({
                                url: modURL + '&op=kaleyeraOutCall',
                                method: 'POST',
                                params: {
                                    phone: customerPhone,
                                },
                                success: function (response) {
                                     var tmp = Ext.decode(response.responseText);
                                     console.log('tmp');
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', 'Issue in saving');
                                }
                            });*/
                        }

                    }
                },{
                    xtype: 'button',
                    id: 'createCustomer',
                    hidden: true,
                    text: 'Create Customer',
                    tooltip: 'Create Customer',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        var customerPhone = Ext.getCmp('omniSearchParameter').getValue();
                        addRetalineCustomers(customerPhone)
                    }
                }, {
                    xtype: 'button',
                    id: 'customerLogin',
                    hidden: true,
                    text: 'Impersonate',
                    tooltip: 'Impersonate',
                    iconCls: 'my-icon1',
                    handler: function () {
                        // GuestView(Application.RetalineOmni.Cache.loginpath);
                        //location.href = "http://www.google.com";
                        //window.open(Application.RetalineOmni.Cache.loginpath, '_blank');
                        Application.RetalineOmni.customerImersonateTab(Application.RetalineOmni.Cache.loginpath);

                    }
                },'->',
                {
                    xtype: 'button',
                    text: 'Call Failed',
                    tooltip: 'Call Failed',
                    hidden: true,
                    iconCls: 'my-icon1',
                    icon: './resources/images/default/icons/call_failed.png',
                    id: 'customerCallFail',
                    handler: function () {                    
                        customerCallCommunicationWindow('Failed');
                    }
                },'|',{
                    xtype: 'button',
                    text: 'Call Dropped',
                    tooltip: 'Call Dropped',
                    hidden: true,
                    icon: './resources/images/default/icons/call_dropped.png',
                    id: 'customerCallDrop',
                    handler: function () {                    
                        customerCallCommunicationWindow('Dropped');
                    }
                },'|',{
                    xtype: 'button',
                    text: 'Call Completed',
                    tooltip: 'Call Completed',
                    hidden: true,
                    icon: './resources/images/default/icons/call_completed.png',
                    id: 'customerCallCompleted',
                    handler: function () {   
                        customerCallCommunicationWindow('Completed');           
    
                    }
                }],
            items: [{
                    layout: 'column',
                    frame: true,
                    columnWidth: 0.33,
                    items: [panelCustomerCommunication(),
                        new Ext.Panel({
                            title: 'Details',
                            frame: false,
                            border: false,
                            width: winsize.width * 0.32,
                            id: 'DetailsViePanelCust',
                            height: winsize.height * 0.7,
                            items: [CustTabDetailsView()],
                            buttonAlign: 'right',
                            fbar: []
                        })]
                }, {
                    layout: 'column',
                    frame: true,
                    columnWidth: 0.33,
                    items: [orderGrid(),
                        new Ext.Panel({
                            frame: true,
                            border: false,
                            width: winsize.width * 0.32,
                            title: '',
                            height: winsize.height * 0.7,
                            bodyStyle: {
                                "background-color": "white"
                            },
                            items: [{
                                    id: 'details_view_panel_scheduledorder',
                                    html: '<iframe id="iframe_omniorder_productdtls" name="iframe_omniorder_productdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                                }
                            ],
                            buttonAlign: 'left',
                            tbar: ['->',{
                                buttonAlign:'left',
                                type: 'button',
                                iconCls: 'rollback',
                                style: 'font-weight:bold',
                                text: 'Access Site',
                                id: 'cgCustomerSite',
                                hidden: true,
                                handler: function () {
                                    var order_id = Ext.getCmp('customer_order_grid').getSelectionModel().getSelections()[0].data.order_id;
                                    Ext.Ajax.request({
                                        url: modURL + '&op=loadOrdersite',
                                        method: 'POST',
                                        params: {order_id: order_id},
                                        success: function (res) {
                                            var tmp = Ext.decode(res.responseText);
                                            if (tmp.success === true) {
                                                Application.RetalineOmni.Cache.loginpath = tmp.data; 
                                                //+ Ext.getCmp('omniSearchParameter').getValue();
                                                Ext.get('iframe_omniorder_impersonate').dom.src = Application.RetalineOmni.Cache.loginpath;                          
                                            } else {
                                                Ext.get('iframe_omniorder_impersonate').dom.src = '';  
                                            }
                                            Ext.getCmp('CustViePanel').doLayout();
                                        },
                                        failure: function () {
                                            Ext.MessageBox.alert('Error', 'Error occured while sending data');
                                        }
                                    });
                                                                  }
                            }
                            ]

                        })
                    ]
                }, {
                    layout: 'column',
                    frame: true,
                    columnWidth: 0.33,
                    items: [
                        new Ext.Panel({
                            frame: false,
                            border: false,
                            width: winsize.width * 0.32,
                            autoScroll: true,
                            id: 'impersonatepanelParent',
                            title: 'Impersonate',
                            height: winsize.height * 0.75,
                            items: [{
                                    id: 'impersonatepanel',
                                    html: '<iframe id="iframe_omniorder_impersonate" name="iframe_omniorder_impersonate"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + iframesrc + '"; ></iframe>',
                                }
                            ],
                            buttonAlign: 'right',
                            tools: [{
                                    id: 'maximize',
                                    qtip: 'Full Window',
                                    handler: function (event, toolEl, panel) {
                                        GuestView(Application.RetalineOmni.Cache.loginpath, Application.RetalineOmni.Cache.customerName);
                                    }
                                }],
                        })
                    ]
                }],
                listeners:{
                    afterrender:function(){
                        var params = (new URL(document.location)).searchParams;
                        if(params.has("phone") === true){
                        var phone = params.get("phone");
                        Ext.getCmp('omniSearchParameter').setValue(phone);
                        }else if(!Ext.isEmpty(contact)){
                            Ext.getCmp('omniSearchParameter').setValue(contact);
                            searchCustomer(contact,'indirect');
                        }else{
                            Ext.getCmp('omniSearchParameter').setValue('');
                        }
                        if(Ext.isEmpty(Ext.getCmp('omniSearchParameter').getValue())){
                            console.log('part4');
                            Ext.getCmp('omniSearchParameter').setValue('');

                        }
                    }
                }
        });
        return _leadPanel;
    };
    var orderGrid = function () {
        var hfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'order_generated_id'
                }, {
                    type: 'date',
                    dataIndex: 'order_created_on'
                }, {
                    type: 'string',
                    dataIndex: 'payment_by'
                }, {
                    type: 'list',
                    options: ['Customer', 'BA'],
                    phpMode: true,
                    dataIndex: 'order_user_type'
                }, {
                    type: 'numeric',
                    dataIndex: 'order_total_amount'
                }, {
                    type: 'list',
                    options: ['Order Created', 'Order Success', 'Order Failed', 'Order Processing', 'Ready to Dispatch', 'Dispatched', 'Delivered'],
                    phpMode: true,
                    dataIndex: 'order_status'
                }]
        });
        hfilters.remote = true;
        hfilters.autoReload = true;
        var order_store = orderStore();
        var grid = new Ext.grid.GridPanel({
            store: order_store,
            frame: false,
            border: false,
            width: winsize.width * 0.32,
            height: winsize.height * 0.35,
            title: 'Orders',
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), hfilters],
            id: 'customer_order_grid',
            fields: ['order_auto_id', 'order_generated_id', 'order_status_sort_order', 'order_user_type', 'cust_mobile', 'ordertime',
                'order_total_amount', 'order_created_on', 'order_status', 'order_tax', 'total'
            ],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-Y');
                        return dateret;
                    }
                },
                {
                    header: 'Order No',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Amount',
                    sortable: true,
                    dataIndex: 'total',
                    tooltip: 'Amount',
                    width: 200,
                    hideable: false,
                    renderer: qtipRenderer
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'PayGatewayId',
                    sortable: true,
                    dataIndex: 'order_payment_gateway_refid',
                    tooltip: 'PayGatewayId',
                    width: 200,
                    hidden: true,
                    renderer: qtipRenderer
                }
            ],
            viewConfig: {
                forceFit: true
            },
            tbar: [],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('order_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.RetalineOmni.Cache.order_auto_id = ID;
                        Application.RetalineOmni.ViewOrderDetails(Application.RetalineOmni.Cache.order_auto_id,2);
                    }
                },
                afterrender: function () {
                    order_store.load();
                }
            },
            stripeRows: true,
        });
        return grid;
    };
    var orderStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listorders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_payment_gateway_refid', 'order_payment_gateway_refid_crc32', 'order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to',
                {
                    name: 'order_created_on',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                },
                'dispatch_courier', 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'total'
            ]),
            sortInfo: {
                field: 'order_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function (store, e) {
                    Ext.getCmp('customer_order_grid').getView().refresh();
                    Ext.getCmp('customer_order_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('customer_order_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('customer_order_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.RetalineOmni.Cache.order_auto_id = ID;
                        Application.RetalineOmni.ViewOrderDetails(Application.RetalineOmni.Cache.order_auto_id,2);
                    } else {
                        Application.RetalineOmni.ViewOrderDetails(0,2);
                    }
                }
            }
        });
        return store;
    };
    var gridSelectionChanged = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('customer_order_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('customer_order_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.RetalineOmni.Cache.order_id = ID;
            Ext.getCmp('cgCustomerSite').show();
            Application.RetalineOmni.ViewOrderDetails(ID,2);
        }
    };
    var FaqDetailsView = function () {
        return new Ext.Panel({
            border: false,
            hideBorders: true,
            width: winsize.width * 0.32,
            autoScroll: true,
            id: 'CustDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<tpl if="isCustomer == \'1\'"><table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td> {cust_customer_name} </td></tr>',
                    '<tr><th width="40%">Mobile </th><td> {cust_mobile} </td></tr>',
                    '<tr><th width="40%">Email </th><td> {cust_email} </td></tr>',
                    '<tr><th width="40%">Wallet Balance </th><td> {cust_walletbalance} &nbsp;<button type="button" hidden="true">Refund</button></td></tr>',
                    '</table></tpl><tpl if="isCustomer == \'0\'"><p>User is not registered as Customer. Impersonation can be provided only if they registered as a user</p></tpl>',
                    '</div>')
        });
    };
    var panelCustomerCommunication = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '?module=RetalineTeleCaller&op=loadcustomerEditData&crcu_id=' + Application.RetalineOmni.Cache.crcu_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;

        var panel = new Ext.TabPanel({
            frame: false,
            activeTab: 0,
            tabPosition: 'top',
            border: false,
            bodyStyle: {
                "background-color": "white"
            },
            id: 'tabpanelMarketingCustomerform',
            width: winsize.width * 0.32,
            height: winsize.height * 0.35,
            items: [{
                title: 'Customer Details',
                id: 'titleMarketingCustomerdetails',
                items: [new Ext.Panel({
                    title: 'Details',
                    frame: false,
                    border: false,
                    width: winsize.width * 0.32,
                    id: 'CustViePanel',
                    height: winsize.height * 0.35,
                    items: [FaqDetailsView()],
                    buttonAlign: 'right',
                    fbar: []
                })]
            },{
                    title: 'Communication',
                    id: 'titleMarketingCustomercommunication',
                    buttonAlign: 'right',
                    fileUpload: true,
                    defaults: {
                        border: false
                    },
                    items: [crmCoustomerCommunicationGrid()], 
                    tbar: [{
                            type: 'button',
                            text: 'Add Communication',
                            id: 'buttonAddcommunicationtab',
                            iconCls: 'finascop_add',
                            hidden: true,
                            handler: function () {
                                var custid = Application.RetalineOmni.Cache.customerId;
                                customerCommunicationWindow(custid);
                            }
                        }]
                },{
                    title: 'Tickets',
                    id: 'titleMarketingCustomertickets',
                    items: [crmCustomerTicketGrid()]
                }/*, {
                    title: 'Documents',
                    id: 'titleMarketingCustomerdocuments',
                    items: [crmCustomerDocumentGrid()]
                }*/

            ],
            listeners: {
                tabchange: function (sd, tab) {
                    if (tab.id == 'titleMarketingCustomercommunication')
                    {
                        communicationTabchange();
                    } else if (tab.id == 'titleMarketingCustomertickets')
                    {
                        var ticketGrid_store = Ext.getCmp('gridMarketingCustomerTicketList').getStore();

                        ticketGrid_store.load({
                            params: {
                                phone: Application.RetalineOmni.Cache.searchPhone
                            }
                        });
                    }else if (tab.id == 'titleMarketingCustomerdocuments')
                    {
                       /* var documentGrid_store = Ext.getCmp('gridMarketingCustomerDocumentList').getStore();

                        documentGrid_store.load({
                            params: {
                                crcu_id: Application.RetalineOmni.Cache.customerId

                            }
                        });*/
                    }
                }
            },
            fbar: [
            ],
        });

        return panel;
    };
    var crmCustomerCommunicationStore = function () {
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getAllCommunications',
            fields: ['createdOn', 'entryAction', 'entryMode','callInitiatedBy','entryFrom','id','entryFromName'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('_gridCustomerMarketingCommunication').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var crmCoustomerCommunicationGrid = function(){
        var _documentsStore = crmCustomerCommunicationStore();
        var grid = new Ext.grid.GridPanel({
            id: '_gridCustomerMarketingCommunication',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _documentsStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Date and Time',
                        dataIndex: 'createdOn',
                        sortable: true,
                        width: 175
                    }, {
                        header: 'Created By',
                        dataIndex: 'callInitiatedBy'
                    },
                    {
                        header: 'Created From',
                        dataIndex: 'entryFromName'
                    }
                ]

            }),
            iconCls: 'icon-grid',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedCustCommunication
                }
            }),
            bbar: new Ext.PagingToolbar({
                pageSize: 12,
                store: _documentsStore,
                displayInfo: true,
                displayMsg: "Displaying records {0} - {1} of {2}",
                emptyMsg: "No pages to display",
              })
        });
        return grid;
    };
    var crmCoustomerCommunicationGridTemplate = function (val) {
        var grid = new Ext.Panel({
            region: 'center',
            id: '_gridCustomerMarketingCommunication',
            height: winsize.height * 0.33,
            autoScroll: true,
            tpl: new Ext.XTemplate(
                    '<div class="cdetails-outer no-border" style="width:100%; margin:15px auto;"><h3 class="history"></h3><ul class="anexure">',
                    '<tpl for=".">',
                    '<div class="details-outer">',
                    '<table style="width:100%;">',
                    '<tr>',
                    '<td ><span class="field" style="color:grey;font-weight:bold"><img src = {calender}></span><span class="crmdate">{date_and_time}</span></td>',
                    '<td ><span class="field" style="color:grey;font-weight:bold;align:right;"><img src={crmm_name}></span><span class="crmname" >{resource}</span></td>',
                    '</tr>',
                    '<tpl if="remark != \'\'">',
                    '<tr>',
                    '<td colspan="2" style= "padding-top : 5px">',
                    '<span span class="crmtext">{remark}</span>',
                    '</td>',
                    '</tr>',
                    '</tpl>',
                    '<tr>',
                    '<td style= "padding-top : 5px"><span class="crmresponse">{response} </span> </td>',
                    '</tr>',
                    '<td colspan="2">',
                    '<hr style="color:grey"></td></tr>',
                    '</table>',
                    '</div></li>',
                    '</tpl>',
                    '</ul></div>',
                    '<style>.field{ padding-right: 10px; }</style>'
                    )

        })
        return grid;
    };
    var customerCommunicationWindow = function (_val) {
        var _custAction_store = comboselectionActionCustomersStore();
        var _customerAction_mode = combomodeContactCustomersStore();
        fileS3BucketView();
        var reswindow = new Ext.Window({
            title: 'Communication Details',
            width: 600,
            autoHeight: true,
            plain: true,
            constrainHeader: true,
            modal: true,
            frame: true,
            border: false,
            iconCls: '',
            resizable: false,
            id: 'CrmCustomersCommunicationWindow',
            items: [
                new Ext.form.FormPanel({
                    fileUpload: true,
                    layout: 'column',
                    id: 'formpanelCrmCustomersCommunication',
                    labelAlign: 'top',
                    bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
                    items: [
                        {
                            xtype: 'hidden',
                            id: 's3_filename',
                            name: 's3_filename'
                        }, {
                            xtype: 'hidden',
                            id: 's3_albumBucketName',
                            name: 's3_albumBucketName'
                        }, {
                            xtype: 'hidden',
                            id: 's3_accessKey',
                            name: 's3_accessKey'
                        }, {
                            xtype: 'hidden',
                            id: 's3_secretKey',
                            name: 's3_secretKey'
                        }, {
                            xtype: 'hidden',
                            id: 's3_bucketRegion',
                            name: 's3_bucketRegion'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3_oncompleteurl',
                            name: 's3_oncompleteurl'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3_img_path_db',
                            name: 's3_img_path_db'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3filepath',
                            name: 's3filepath'
                        },
                        {
                            columnWidth: .5,
                            layout: 'form',
                            items: [
                                {
                                    xtype: 'combo',
                                    id: 'comboSelectActionCustomers',
                                    name: 'comboSelectActionCustomers',
                                    fieldLabel: 'Select Action',
                                    hiddenName: '_comboSelectActionCustomers',
                                    store: _custAction_store,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    typeAhead: true,
                                    selectOnFocus: true,
                                    mode: 'local',
                                    allowBlank: false,
                                    valueField: 'crma_id',
                                    displayField: 'crma_name',
                                    anchor: '95%'
                                }
                            ]
                        },
                        {
                            columnWidth: .47,
                            layout: 'form',
                            items: [{
                                    xtype: 'combo',
                                    id: 'comboModeOfContactCustomers',
                                    fieldLabel: 'Mode of Contact',
                                    name: 'comboModeOfContactCustomers',
                                    hiddenName: '_comboModeOfContactCustomers',
                                    store: _customerAction_mode,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    typeAhead: true,
                                    selectOnFocus: true,
                                    allowBlank: false,
                                    mode: 'local',
                                    valueField: 'crmm_id',
                                    displayField: 'crmm_name',
                                    anchor: '95%'
                                }]
                        },
                        {
                            columnWidth: 1,
                            layout: 'form',
                            items: [{
                                    xtype: 'textarea',
                                    id: 'textareaRemarksCustomers',
                                    fieldLabel: 'Remarks',
                                    name: 'textareaRemarksCustomers',
                                    anchor: '97%',
                                    width: 500,
                                    maxLength: 500
                                }]
                        },
                        {
                            columnWidth: .16,
                            border: false,
                            buttons: [
                                {
                                    xtype: 'fileuploadfield',
                                    style: 'margin-bottom:5px;',
                                    id: 'fileuploadfieldAttachFileCustomers',
                                    border: false,
                                    anchor: '97%',
                                    name: 'fileuploadfieldAttachFileCustomers',
                                    allowBlank: true,
                                    buttonOnly: true,
                                    buttonCfg: {
                                        text: 'Upload File',
                                        border: false,
                                        width: 100
                                    },
                                    validator: function (v) {
                                        if (v != '')
                                        {
                                            v = v.toLowerCase();
                                            var exp = /^.*\.(png|jpg|gif|pdf|docx)$/i;
                                            if (!(exp.test(v)))
                                            {
                                                return 'Upload a valid image file of format JPG.';
                                            }

                                            var associated_file = Ext.getCmp('fileuploadfieldAttachFileCustomers').getValue();
                                            if (associated_file == '')
                                            {
                                                Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                                return;
                                            }
                                            addFile();
                                            return true;
                                        }
                                    }
                                }
                            ]
                        }, {
                            columnWidth: 1,
                            layout: 'form',
                            border: false,
                            items: [{xtype: 'displayfield', id: 'supporting', cls: 'fpcls', value: 'File uploaded successfully', hidden: true}]
                        }
                    ]
                })],
            buttons: [
                {
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'so_upload',
                    handler: function () {
                        reswindow.close();
                    }

                }, {
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    iconCls: 'so_upload',
                    handler: function () {
                        Application.RetalineOmni.communicationDataSaveApi();
                    }

                }
            ]
        });
        reswindow.doLayout();
        reswindow.show();
        reswindow.center();
    };
    var crmCustomerDocumentStore = function () {
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getDocumentGridData',
            fields: ['date_and_time', 'resource', 'crma_name', 'crmf_filepath', 'crmf_filename', 'fileextension'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingCustomerDocumentList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var crmCustomerDocumentGrid = function () {
        var _documentsStore = crmCustomerDocumentStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMarketingCustomerDocumentList',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _documentsStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Date and Time',
                        dataIndex: 'date_and_time',
                        sortable: true,
                        width: 175
                    },
                    {
                        header: 'Resource',
                        dataIndex: 'resource'
                    },
                    {
                        header: 'Attachment',
                        xtype: 'actioncolumn',
                        items: [
                            {
                                getClass: function (v, meta, rec) {
                                    var data = rec.data;
                                    var _fileext = data.fileextension;
                                    if (_fileext == 'pdf')
                                    {

                                        return 'finascop_marketing_pdfdoc';
                                    } else if (_fileext == 'jpg' || _fileext == 'png')
                                    {

                                        return 'finascop_marketing_imgicon';
                                    } else
                                    {
                                        return 'finascop_marketing_worddoc';
                                    }

                                },
                                handler: function (grid, rowIndex, colIndex, itm, evn) {
                                    var record = grid.getStore().getAt(rowIndex);
                                    var _filepath = record.data.crmf_filepath;
                                    window.open(_filepath, '_blank');
                                }
                            }
                        ]
                    }
                ]

            }),
            iconCls: 'icon-grid',
        });
        return grid;
    };
    var GuestView = function () {
        var fun = arguments[0];
        var wintitle = arguments[1];
        var guest_window = new Ext.Window({
            id: 'guest_window',
            title: wintitle,
            modal: true,
            constrain: true,
            layout: 'fit',
            maximized: true,
            floating: true,
            shadow: false,
            closeAction: 'close',
            items: new Ext.Panel({
                layout: 'fit',
                height: 580,
                items: [{
                        html: '<iframe id="iframe_reportadd" name="iframe_reportadd" style="overflow:auto;width:100%;height:100%" frameborder="0"  src="' + fun + '"; ></iframe>'
                    }]
            }),
            buttons: [{
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    text: 'Cancel',
                    tabIndex: 32,
                    handler: function () {
                        guest_window.close();
                    }
                }]
        });
        guest_window = Ext.getCmp('guest_window');
        guest_window.show();
        guest_window.doLayout();
        guest_window.center();
    };
    var comboselectionActionCustomersStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=customerAction',
            method: 'post',
            fields: ['crma_id', 'crma_name']

        });
        return store;
    };
    var comboselectionActionCall = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=callAction',
            method: 'post',
            fields: ['id', 'name']

        });
        return store;
    };
    var combomodeContactCustomersStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=customerModeofAction',
            method: 'post',
            fields: ['crmm_id', 'crmm_name']

        });
        return store;
    };
    var fileS3BucketView = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var uuid = uuidv4(); // for xxxx to field

        Ext.Ajax.request({
            url: modURL + '&op=get_file_s3_details',
            method: 'POST',
            params: {
                rid: uuid,
                apikey: _SESSION.apikey,
                tstamp: t_stamp

            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success !== undefined && tmp.success === true)
                {

                    Ext.getCmp('s3_accessKey').setValue(tmp.data.accessKey);
                    Ext.getCmp('s3_albumBucketName').setValue(tmp.data.albumBucketName);
                    Ext.getCmp('s3_secretKey').setValue(tmp.data.secretKey);
                    Ext.getCmp('s3_bucketRegion').setValue(tmp.data.bucketRegion);
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', 'Issue in saving');
            }
        });
    };
    var uuidv4 = function () {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    };
    var communicationTabchange = function () {
        var custCommunstore = Ext.getCmp('_gridCustomerMarketingCommunication').getStore();

        custCommunstore.load({
                            params: {
                                userId:Application.RetalineOmni.Cache.customerId,
                                userType:1,
                                mobile:Ext.getCmp('omniSearchParameter').getValue()
                            }
                        });
    };
    var addFile = function () {
        var albumBucketName = Ext.getCmp('s3_albumBucketName').getValue();
        var bucketRegion = Ext.getCmp('s3_bucketRegion').getValue();
        AWS.config.update({
            region: bucketRegion,
            credentials: new AWS.Credentials(
                    Ext.getCmp('s3_accessKey').getValue(),
                    Ext.getCmp('s3_secretKey').getValue(), null
                    )
        });
        var s3 = new AWS.S3({
            apiVersion: '2006-03-01',
            params: {Bucket: albumBucketName}
        });
        var files = document.getElementById('fileuploadfieldAttachFileCustomers-file').files;
        if (!files.length)
        {
            return alert('Please choose a file to upload first.');
        }
        var file = files[0];
        var actualfileName = file.name;
        var file_Name = JSON.stringify(actualfileName).slice(1, -1);
        var fileExt = file_Name.split('.').pop();
        var fileName = uuidv4();
        fileName = fileName + '.' + fileExt;
        Ext.getCmp('s3_filename').setValue(fileName);
        s3.upload({
            Key: fileName,
            Body: file,
            ACL: 'public-read'
        },
        function (err, data) {

            if (err)
            {
                var img_src = Ext.BLANK_IMAGE_URL;
                return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location))
            {
                Ext.MessageBox.show({
                    msg: 'Uploading, Please wait...',
                    progressText: '',
                    width: 300,
                    wait: true,
                    waitConfig:
                            {
                                duration: 5000,
                                increment: 15,
                                text: 'Saving...',
                                scope: this,
                                fn: function () {
                                    Ext.MessageBox.hide();
                                    Application.example.msg('Notification', 'File uploaded successfully.');
                                    Ext.getCmp('supporting').show();
                                    Ext.getCmp('supporting').disable();
                                }
                            }
                });
                Ext.getCmp('s3filepath').setValue(data.Location);
            }
        });
    };
    var verifyOtp = function(mobile,veri_id){
        
        var otpverify_window = new Ext.Window({
            id: 'otpverify_window',
            title: 'OTP Verify',
            modal: true,
            constrain: true,
            layout: 'fit',
            width: winsize.width * 0.2,
            height: winsize.height * 0.2,
            closeAction: 'close',
            items: [new Ext.form.FormPanel({
                    layout: 'column',
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
                    frame: true,
                    items: [                                              
                        {
                            columnWidth: 1,
                            layout: 'form',
                            border: false,
                            bodyStyle: {"padding": "5px"},
                            items: [{
                                xtype: 'numberfield',
                                id: 'veri_sms_code',
                                name: 'veri_sms_code',
                                allowBlank: false,
                                anchor: '97%',
                                tabIndex: 542,
                                fieldLabel: 'OTP'
                                }]
                        }
                    ]
                })],
            buttons: [{
                        tabIndex: 543,
                        xtype: "button",
                        text: 'Verify',
                        id: 'verifyOtp',
                        icon: IMAGE_BASE_PATH + '/default/icons/approve.png',
                        handler: function () {
                            var customerPhone = Ext.getCmp('cust_mobile').getValue();
                            var customerOtp = Ext.getCmp('veri_sms_code').getValue();
                            var cust_customer_id = Ext.getCmp('cust_customer_id').getValue();
                            if (!Ext.isEmpty(customerPhone)) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=verifyCustomer',
                                    method: 'POST',
                                    params: {
                                        phone: mobile,
                                        otp:customerOtp,
                                        veri_id: veri_id,
                                        cust_customer_id: cust_customer_id
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == true) {
                                            Application.example.msg("Success", tmp.msg);
                                            otpverify_window.close();
                                        }else{
                                            Ext.MessageBox.alert('Notification', tmp.msg);
                                        }
                                    },
                                    failure: function (response) {
                                        var tmp = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.alert('Error', 'Issue in sending otp');
                                    }
                                });
                            }
                        }

                    },{
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    text: 'Cancel',
                    tabIndex: 32,
                    handler: function () {
                        otpverify_window.close();
                    }
                }]
        });
        otpverify_window = Ext.getCmp('otpverify_window');
        otpverify_window.show();
        otpverify_window.doLayout();
        otpverify_window.center();
    };
    var addRetalineCustomerFrm = function (customerPhone) {
        var formRetalineCustomer = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'retslCustomer_form',
            autoHeight: true,
            labelAlign: 'top',
            items: [
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .20,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'cust_mobile',
                                            name: 'cust_mobile',
                                            allowBlank: false,
                                            vtype: 'phonespec',
                                            anchor: '97%',
                                            tabIndex: 540,
                                            fieldLabel: 'Mobile Number',
                                            value: customerPhone,
                                            editable: false
                                        }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .20,
                                    items: [
                                        {
                                            style: "padding-top: 13px;",
                                            xtype: "button",
                                            text: 'Generate OTP',
                                            tabIndex: 541,
                                            id: 'generateOtp',
                                            icon: IMAGE_BASE_PATH + '/default/icons/arrow_refresh.png',
                                            handler: function () {
                                                var customerPhone = Ext.getCmp('cust_mobile').getValue();
                                                if (!Ext.isEmpty(customerPhone)) {
                                                    Ext.Ajax.request({
                                                        url: modURL + '&op=sendOtpToCustomer',
                                                        method: 'POST',
                                                        params: {
                                                            phone: customerPhone,
                                                        },
                                                        success: function (response) {
                                                            var tmp = Ext.decode(response.responseText);
                                                            if (tmp.success == true) {
                                                                Ext.getCmp('generateOtp').disable();                                                                
                                                                //Ext.getCmp('veri_sms_code').show();
                                                                //Ext.getCmp('verifyOtp').show();
                                                                Ext.getCmp('veri_id').setValue(tmp.veri_id);
                                                                Ext.getCmp('cust_customer_id').setValue(tmp.cust_customer_id);
                                                                Application.example.msg('Notification', tmp.msg);
                                                                verifyOtp(customerPhone,tmp.veri_id);
                                                            }else{
                                                                Ext.MessageBox.alert('Notification', tmp.msg);
                                                            }
                                                        },
                                                        failure: function (response) {
                                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                                            Ext.MessageBox.alert('Error', 'Issue in sending otp');
                                                        }
                                                    });
                                                }
                                            }

                                        }                                       
                                    ]
                                }, {
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'cust_customer_name',
                                            name: 'cust_customer_name',
                                            allowBlank: false,
                                            vtype: 'dataStringspec',
                                            maxLength: 250,
                                            maxLengthText: 'The maximum length for this field is 250',
                                            anchor: '97%',
                                            tabIndex: 544,
                                            fieldLabel: 'Name',
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1);
                                                }
                                            }
                                        }
                                         
                                    ]
                                },{
                                    layout: 'form',
                                    columnWidth: .30,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'cust_email',
                                            name: 'cust_email',
                                            maxLength: 50,
                                            maxLengthText: 'The maximum length for this field is 50',
                                            tabIndex: 545,
                                            hidden: false,
                                            vtype: 'email',
                                            anchor: '97%',
                                            fieldLabel: 'E-mail'
                                        }
                                         
                                    ]
                                }]
                        }]

                },
                {
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .60,
                                    items: [{
                                    xtype: 'textfield',
                                    id: 'customer_Location',
                                    name: 'customer_Location',
                                    maxLength: 100,
                                    maxLengthText: 'The maximum length for this field is 100',
                                    tabIndex: 550,
                                    allowBlank: true,
                                    anchor: '97%',
                                    fieldLabel: 'Search Location',
                                    listeners: {
                                        focus: function () {
                                            initAutocompleteText();
                                        }
                                    }
                                }]
                                },
                                {
                                    layout: 'form',
                                    columnWidth: .20,
                                    items: [{
                                    xtype: 'textfield',
                                    id: 'deli_latitude',
                                    name: 'deli_latitude',
                                    maxLength: 100,
                                    tabIndex: 558,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'Latitude'
                                }]
                                }, {
                                    layout: 'form',
                                    columnWidth: .20,
                                    items: [{
                                    xtype: 'textfield',
                                    id: 'deli_longitude',
                                    name: 'deli_longitude',
                                    maxLength: 100,
                                    tabIndex: 559,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'Longitude'
                                }]
                                }]
                        }]

                },{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: .98,
                            items: [{
                                    tabIndex: 551,
                                    xtype: 'gmappanel',
                                    gmapType: 'map',
                                    zoomLevel: 14,
                                    id: "custgooglemap",
                                    height: 230,
                                    minGeoAccuracy: 4,
                                    scaleControl: true,
                                    mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
                                    mapControls: ['GSmallMapControl', 'GMapTypeControl'],
                                    setCenter: {
                                        lat: DEF_LATITUDE,
                                        lng: DEF_LONGITUDE,
                                    },
                                    markers: mymarker,
                                    repaint: function (zoomlevel) {
                                        var gmappanel = Ext.getCmp('custgooglemap');
                                        if (zoomlevel) {
                                            gmappanel.zoomLevel = zoomlevel;
                                            gmappanel.getMap().setZoom(zoomlevel);
                                        }
                                        gmappanel.onMapReady();

                                    },
                                }
                            ]
                        }]
                },
                {
                    hidden:true,
                    layout: 'column',
                    items: [{
                            layout: 'column',
                            columnWidth: 1,
                            items: [{
                                    layout: 'form',
                                    columnWidth: .25,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'cust_alternate_email',
                                            name: 'cust_alternate_email',
                                            maxLength: 50,
                                            maxLengthText: 'The maximum length for this field is 50',
                                            tabIndex: 546,
                                            hidden: false,
                                            vtype: 'email',
                                            anchor: '97%',
                                            fieldLabel: 'Alternate E-mail'
                                        }]
                                    },{
                                    layout: 'form',
                                    columnWidth: .25,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            id: 'cust_alt_phone',
                                            name: 'cust_alt_phone',
                                            maxLength: 10,
                                            vtype: 'phonespec',
                                            anchor: '97%',
                                            tabIndex: 547,
                                            fieldLabel: 'Alternate Phone'
                                        }
                                    ]
                                }]
                        }
                    ]

                },                
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'deli_house_no',
                                    name: 'deli_house_no',
                                    maxLength: 100,
                                    maxLengthText: 'The maximum length for this field is 100',
                                    tabIndex: 552,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'House Name / Number'
                                }
                            ]
                        },{
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'deli_house_name',
                                    name: 'deli_house_name',
                                    maxLength: 100,
                                    maxLengthText: 'The maximum length for this field is 100',
                                    tabIndex: 552,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'Street Name / Number'
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'deli_address',
                                    name: 'deli_address',
                                    maxLength: 100,
                                    maxLengthText: 'The maximum length for this field is 100',
                                    tabIndex: 553,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'Address Line 1'
                                }
                            ]
                        }
                    ]

                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'deli_address2',
                                    name: 'deli_address2',
                                    maxLength: 100,
                                    maxLengthText: 'The maximum length for this field is 100',
                                    tabIndex: 554,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'Address Line 2'
                                }
                            ]
                        },{
                            layout: 'form',
                            columnWidth: 0.3,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'deli_state',
                                    name: 'deli_state',
                                    maxLength: 100,
                                    maxLengthText: 'The maximum length for this field is 100',
                                    tabIndex: 557,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'State/Province'
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.3,
                            items: [
                                {
                                    xtype: 'textfield',
                                    id: 'deli_district',
                                    name: 'deli_district',
                                    maxLength: 100,
                                    maxLengthText: 'The maximum length for this field is 100',
                                    tabIndex: 556,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'District/City'
                                }
                            ]
                        }]
                },{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.20,
                            items: [{
                                    xtype: 'textfield',
                                    id: 'deli_post',
                                    name: 'deli_post',
                                    maxLength: 100,
                                    tabIndex: 555,
                                    allowBlank: false,
                                    anchor: '97%',
                                    fieldLabel: 'Pincode'
                                }]
                        },{
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [{
                                fieldLabel:'Address Type',
                                xtype: 'radiogroup',
                                anchor: '98%',
                                mode: 'remote',
                                forceSelection: true,
                                triggerAction: 'all',
                                lazyRender: true,
                                tabIndex: 15,
                                id:'deli_type',
                                items: [{
                                        boxLabel: 'Home',
                                        id: 'deli_type1',
                                        name: 'deli_type',
                                        inputValue: 'Home',
                                    }, {
                                        boxLabel: 'Office',
                                        id: 'deli_type2',
                                        name: 'deli_type',
                                        inputValue: 'Office',
                                    }, {
                                        boxLabel: 'Other',
                                        id: 'deli_type3',
                                        name: 'deli_type',
                                        inputValue: 'Other',
                                    }],
                                    listeners: {
                                        change: function (event, checked) {
                                            var current_thirdid = event.items.items[2].inputValue;
                                            var radioid = Ext.getCmp('deli_type').getValue();
                                            console.log('radioid', radioid);
                                            if (radioid == current_thirdid)
                                            {
                                                Ext.getCmp('deli_type_name').enable();
                                            } else 
                                            {
                                                Ext.getCmp('deli_type_name').disable();
                                            }
                                        }
                                    }
                            }]
                        }, {
                            layout: 'form',
                            columnWidth: 0.40,
                            items: [{
                                    xtype: 'textfield',
                                    id: 'deli_type_name',
                                    name: 'deli_type_name',
                                    maxLength: 100,
                                    maxLengthText: 'The maximum length for this field is 100',
                                    tabIndex: 549,
                                    anchor: '97%',
                                    fieldLabel: 'Address Name'
                                }                               
                            ]
                        }
                    ]

                },
                {
                    xtype: 'hidden',
                    id: 'cust_customer_id',
                    name: 'cust_customer_id'
                },{
                    xtype: 'hidden',
                    id: 'veri_id',
                    name: 'veri_id'
                }
            ]

        });
        return formRetalineCustomer;
    };
    var addRetalineCustomers = function (customerPhone) {
        var retalineCustomer_winId = "retalineCustomer_window";
        var retalineCustomer_window = Ext.getCmp(retalineCustomer_winId);
        if (Ext.isEmpty(retalineCustomer_window)) {
            var retalineCustomerForm = addRetalineCustomerFrm(customerPhone);
            retalineCustomer_window = new Ext.Window({
                id: 'retalineCustomer_window',
                layout: 'fit',
                width: winsize.width * 0.6,
                height: winsize.height * 0.8,
                title: 'Create Customer',
                autoScroll: true,
                //iconCls: (id) ? 'my-icon24' : 'my-icon26',
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                resizable: false,
                items: [retalineCustomerForm],
                buttons: [{
                        text: 'Cancel',
                        id: 'btnCancel',
                        tabIndex: 507,
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            retalineCustomer_window.close();
                        }
                    },
                    {
                        text: 'Save',
                        id: 'btnsave',
                        tabIndex: 506,
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            if (Ext.getCmp('retslCustomer_form').getForm().isValid()) {
                                if (!Ext.isEmpty(Ext.getCmp('cust_customer_name').getValue()) &&
                                        !Ext.isEmpty(Ext.getCmp('cust_mobile').getValue())) {

                                    Ext.Ajax.request({
                                        url: modURL + '&op=saveretalineCustomers',
                                        method: 'POST',
                                        params: {
                                            veri_id: Ext.getCmp('veri_id').getValue(),
                                            cust_customer_id: Ext.getCmp('cust_customer_id').getValue(),
                                            cust_customer_name: Ext.getCmp('cust_customer_name').getValue(),
                                            cust_email: Ext.getCmp('cust_email').getValue(),
                                            cust_mobile: Ext.getCmp('cust_mobile').getValue(),
                                            deli_type: Ext.getCmp('deli_type').getValue(),
                                            deli_type_name: Ext.getCmp('deli_type_name').getValue(),
                                            deli_post: Ext.getCmp('deli_post').getValue(),
                                            cust_alternate_email: Ext.getCmp('cust_alternate_email').getValue(),
                                            cust_alt_phone: Ext.getCmp('cust_alt_phone').getValue(),
                                            deli_state: Ext.getCmp('deli_state').getValue(),
                                            deli_district: Ext.getCmp('deli_district').getValue(),
                                            deli_latitude: Ext.getCmp('deli_latitude').getValue(),
                                            deli_longitude: Ext.getCmp('deli_longitude').getValue(),
                                            deli_house_name: Ext.getCmp('deli_house_name').getValue(),
                                            deli_address: Ext.getCmp('deli_address').getValue(),
                                            deli_address2: Ext.getCmp('deli_address2').getValue()

                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true && tmp.valid === true) {
                                                Application.example.msg('Success', tmp.message);

                                                Ext.getCmp('omniSearchParameter').setValue(Ext.getCmp('cust_mobile').getValue());
                                                retalineCustomer_window.close();
                                                var customerPhone = Ext.getCmp('omniSearchParameter').getValue();
                                                checkCustomerExistance(customerPhone);
                                            } else if (tmp.success === true && tmp.valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else if (tmp.success === true && tmp.img_valid === false) {
                                                Ext.Msg.alert("Notification.", tmp.message);
                                            } else {
                                                Ext.Msg.alert("Error", 'Entered data is not valid.');
                                            }
                                        },
                                        failure: function (response) {
                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                            Ext.MessageBox.alert('Error', tmp.message);
                                        }
                                    });
                                }
                            }
                        }
                    }]
            });

        }
        ;

        retalineCustomer_window.doLayout();
        retalineCustomer_window.show(this);
        retalineCustomer_window.center();
    };
    function initAutocompleteText() {

        var input = document.getElementById('customer_Location');
        var searchBox = new google.maps.places.SearchBox(input);
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            var latitude = place.geometry.location.lat();
            var longitude = place.geometry.location.lng();
            var latlng = new google.maps.LatLng(latitude, longitude);
            document.getElementById('deli_latitude').value = latitude;
            document.getElementById('deli_longitude').value = longitude;


            var geocoder = geocoder = new google.maps.Geocoder();
            geocoder.geocode({'latLng': latlng}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        var address = results[0].formatted_address;
                        var pin = results[0].address_components[results[0].address_components.length - 1].long_name;
                        var country = results[0].address_components[results[0].address_components.length - 2].long_name;
                        var state = results[0].address_components[results[0].address_components.length - 3].long_name;
                        var city = results[0].address_components[results[0].address_components.length - 4].long_name;
                        //document.getElementById('txtCountry').value = country;
                        document.getElementById('deli_state').value = state;
                        document.getElementById('deli_district').value = city;
                        document.getElementById('deli_post').value = pin;
                    }
                }
            });

            //custgooglemap
            Ext.getCmp('custgooglemap').clearMarkers();
            var my_marker = [];
            my_marker.push({
                geoCodeAddr: Ext.getCmp("deli_post").getValue(),
                setCenter: true,
                marker: {
                    title: "Click and Drag to Move Around",
                    draggable: true
                },
                listeners: {
                    onFailure: function () {

                    },
                    "tilesloaded": function (markerAt) {
                        Ext.getCmp('deli_latitude').setValue(markerAt.latLng.lat());
                        Ext.getCmp('deli_longitude').setValue(markerAt.latLng.lng());
                    },
                    onSuccess: function (point) {
                        Ext.getCmp('deli_latitude').setValue(point.latLng.lat());
                        Ext.getCmp('deli_longitude').setValue(point.latLng.lng());
                    },
                    "dragend": function (markerAt) {
                        Ext.getCmp('deli_latitude').setValue(markerAt.latLng.lat());
                        Ext.getCmp('deli_longitude').setValue(markerAt.latLng.lng());
                    }
                },
                icon: null
            });

            Ext.getCmp('custgooglemap').addScaleControl();
            //Ext.getCmp('custgooglemap').clearMarkers();
            //Ext.getCmp('custgooglemap').addMarkers(my_marker);
            Ext.defer(function () {
//                var point = Ext.getCmp('custgooglemap').getCenterLatLng();
//                Ext.getCmp('deli_latitude').setValue(point.lat);
//                Ext.getCmp('deli_longitude').setValue(point.lng);
                //Ext.getCmp('custgooglemap').clearMarkers();
                Ext.getCmp('custgooglemap').repaint(13);
                Ext.getCmp('custgooglemap').addMarkers(my_marker);
            }, 1200);
        });
    }
    ;
    var checkCustomerExistance = function (customerPhone) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=checkCustomerExist',
            params: {
                customerPhone: customerPhone
            },
            success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true) {
                    Ext.getCmp('customerLogin').hide();

                    Ext.getCmp('createCustomer').hide();
                    Application.RetalineOmni.Cache.customerId = tmp.cust_id;
                    Application.RetalineOmni.Cache.loginpath = tmp.path;
                    Application.RetalineOmni.Cache.customerName = tmp.cust_customer_name;
                    Ext.getCmp('impersonatepanelParent').setTitle('Logged in as ' + tmp.cust_customer_name);
                    Ext.getCmp('impersonatepanel').setTitle('Logged in as ' + tmp.cust_customer_name);

                    Ext.get('iframe_omniorder_impersonate').dom.src = Application.RetalineOmni.Cache.loginpath;
                    Application.RetalineOmni.customerViewMode(tmp.cust_id);
                    Ext.getCmp('customer_order_grid').getStore().load({
                        params: {
                            cust_id: tmp.cust_id
                        }
                    });

                    communicationTabchange();
                } else {
                    Ext.getCmp('tabpanelMarketingCustomerform').setActiveTab(0);
                    Ext.getCmp('customerLogin').hide();
                    Ext.getCmp('createCustomer').show();
                    Ext.getCmp('impersonatepanelParent').setTitle('');
                    Application.RetalineOmni.customerViewMode(0);
                    Application.RetalineOmni.Cache.customerId = 0;
                    Application.RetalineOmni.Cache.customerName = '';
                    Ext.getCmp('customer_order_grid').getStore().load({
                        params: {
                            cust_id: 0
                        }
                    });
                    communicationTabchange();
                    Ext.getCmp('gridMarketingCustomerDocumentList').getStore().load({
                        params: {
                            cust_id: 0
                        }
                    });
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
    var tree_panel = function () {

        var tree = new Ext.tree.TreePanel({
            region: 'center',
            useArrows: false,
            autoScroll: true,
            width: winsize.width * 0.25,
            height: winsize.height * 0.7,
            animate: true,
            enableDD: true,
            containerScroll: true,
            border: true,
            overClass: 'x-view-over',
            itemSelector: 'div.thumb-wrap',
            dataUrl: modURL + '&op=getCategoryStructure',
            id: 'account_structure_tree',
            mask: true,
            maskDisabled: false,
            maskConfig: {msg: "Loading items..."},
            root: {
                nodeType: 'async',
                text: 'Categories',
                draggable: false,
                id: 'root'
            },
            listeners: {
                load: function () {
                    setTimeout(function () {
                        if (WinMask)
                            WinMask.hide();
                    }, 500);
                },
                click: function (node) {
                    var idstrip = node.id;
                    var idstripArr = idstrip.split("_");
                    if (idstripArr[0] === 'L4') {
                        Ext.getCmp('subcatItemsGrid').getStore().load({
                            params: {
                                subcategoryId: idstripArr[1]
                            }
                        });
                    } else {
                        Ext.getCmp('subcatItemsGrid').getStore().load({
                            params: {
                                subcategoryId: 0
                            }
                        });
                    }
                }
            }
        });
        tree.getRootNode().expand();
        return tree;
    };
    var itemmasterGrid = function () {
        var itemmaster_store = itemmasterStore();
        var ItemMaster_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_itemName'
                }, {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                },
            ]
        });
        ItemMaster_filter.remote = true;
        ItemMaster_filter.autoReload = true;
        var itemmaster_grid_panel = new Ext.grid.GridPanel(
                {
                    ds: itemmaster_store,
                    frame: false,
                    width: winsize.width * 0.75,
                    height: winsize.height * 0.7,
                    autoScroll: true,
                    border: false,
                    id: 'subcatItemsGrid',
                    title: ' ',
                    loadMask: true,
                    plugins: [ItemMaster_filter],
                    columns: [new Ext.grid.RowNumberer(),
                        {
                            header: 'SKU',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'stit_SKU'
                        },{
                            header: 'Brand',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'stit_brand_name'
                        },{
                            header: 'Sub Category',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'sub_category'
                        },{
                            header: 'Category',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'category_name'
                        },{
                            header: 'Department',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'deptName'
                        },{
                            header: 'Retail Category',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'business_type_name'
                        },
                        {
                            header: 'Item Name',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'stit_itemName'
                        }, ],
                    viewConfig: {
                        forceFit: true,
                        deferEmptyText: false,
                        emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                    },
                    sm: new Ext.grid.RowSelectionModel({
                        singleSelect: true
                    })
                }
        );

        return itemmaster_grid_panel;
    };
    var itemmasterStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listItemsofSubcat',
            fields: ['stit_ID', 'stit_itemName', 'stit_SKU','stit_brand_name','sub_category','category_name', 'deptName','business_type_name'
            ],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'stit_ID',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function (store, record, options) {


                }
            }
        });
        return store;
    };
    var allcategory_panelTabPanel = function (id) {
        var _leadPanel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            title: 'Category Tree',
            hideBorders: true,
            id: id,
            tbar: [],
            items: [tree_panel(),
                new Ext.Panel({
                    width: winsize.width * 0.75,
                    height: winsize.height * 0.7,
                    border: false,
                    layout: 'form',
                    region: 'east',
                    title: '',
                    labelAlign: 'top',
                    items: [itemmasterGrid()]
                })]
        });
        return _leadPanel;
    };
    var merchantSupportMainTabPanel = function (id,merchantContact) {
        var src = modURL + '&op=order_details_view&order_auto_id=0';
        var iframesrc = Application.RetalineOmni.Cache.merchantpath;
        var _leadPanel = new Ext.Panel({
            layout: 'column',
            //autoHeight:true,
            height: winsize.height*.9,
            frame: true,
            bodyStyle: {"background-color": "white"},
            title: 'Merchant Suppport',
            hideBorders: true,
            id: id,
            tbar: [{
                    xtype: "textfield",
                    id: "merchantSearchParameterId",
                    name: "merchantSearchParameterId",
                    hidden: true,
                    },{
                    xtype: 'textfield',
                    tabIndex: 602,
                    vtype: 'phonespec',
                    id: 'merchantSearchParameter',
                    name: 'merchantSearchParameter',
                    emptyText: 'Enter Mobile Number',
                    allowBlank: false,
                    anchor: '95%',
                    maxLength: 100,
                    listeners: {
                        /*specialkey: function (field, e) {
                            var customerPhone = Ext.getCmp('merchantSearchParameter').getValue();
                            if (!Ext.isEmpty(customerPhone)) {
                                checkMerchantExistance(customerPhone);

                            } else {
                                Ext.MessageBox.alert('Error', "Check the data entered");
                            }
                        }*/
                    }
                }, {
                    xtype: 'button',
                    text: 'Load',
                    id: 'showBtntoms',
                    iconCls: 'rollback',
                    style: "padding-left: 10px;",
                    handler: function () {
                        var customerPhone = Ext.getCmp('merchantSearchParameter').getValue();
                        searchMerchant(customerPhone,'direct');
                        

                    }
                },{
                    xtype: 'button',
                    text: 'Search',
                    id: 'showBtnSearchms',
                    iconCls: 'finascop_search_btn',
                    style: "padding-left: 10px;",
                    handler: function () {
                        MerchantSearch();

                    }
                }, {
                    xtype: 'button',
                    id: 'createMerchant',
                    hidden: true,
                    text: 'Create Merchant',
                    tooltip: 'Create Merchant',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        
                    }
                }, {
                    xtype: 'button',
                    id: 'merchantLogin',
                    hidden: true,
                    text: 'Deligate',
                    tooltip: 'Deligate',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.RetalineOmni.merchantImersonateTab(Application.RetalineOmni.Cache.merchantpath);
                    }
                }, {
                    xtype: 'button',
                    text: 'Call',
                    tooltip: 'Call',
                    hidden: true,
                    icon: './resources/images/default/icons/call.png',
                    id: 'merchantCall',
                    iconCls: 'my-icon1',
                    handler: function () {
                        var customerPhone = Ext.getCmp('merchantSearchParameter').getValue();
                        if (!Ext.isEmpty(customerPhone)) {
                            Ext.Ajax.request({
                                url: modURL + '&op=outboundCall',
                                method: 'POST',
                                params: {
                                    phone: customerPhone,
                                    jobId : Application.OutboundCalls.Cache.jobId
                                },
                                success: function (response) {
                                     var tmp = Ext.decode(response.responseText);
                    if (tmp.success == false) {
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }else{
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', 'Issue in saving');
                                }
                            });
                        }

                    }
                },'->',
                {
                    xtype: 'button',
                    text: 'Call Failed',
                    tooltip: 'Call Failed',
                    hidden: true,
                    iconCls: 'my-icon1',
                    icon: './resources/images/default/icons/call_failed.png',
                    id: 'merchantCallFail',
                    handler: function () {                    
                        merchantCallCommunicationWindow('Failed');
                    }
                },'|',{
                    xtype: 'button',
                    text: 'Call Dropped',
                    tooltip: 'Call Dropped',
                    hidden: true,
                    icon: './resources/images/default/icons/call_dropped.png',
                    id: 'merchantCallDrop',
                    handler: function () {                    
                        merchantCallCommunicationWindow('Dropped');
                    }
                },'|',{
                    xtype: 'button',
                    text: 'Call Completed',
                    tooltip: 'Call Completed',
                    hidden: true,
                    icon: './resources/images/default/icons/call_completed.png',
                    id: 'merchantCallCompleted',
                    handler: function () {   
                        merchantCallCommunicationWindow('Completed');           
    
                    }
                }],
            items: [{
                layout: 'column',
                frame: true,
                columnWidth: 0.33,
                items: [panelMerchantCommunication(),
                    new Ext.Panel({
                        title: 'Details',
                        frame: false,
                        border: false,
                        bodyStyle: {"background-color": "white"},
                        width: winsize.width * 0.32,
                        id: 'DetailsViePanel',
                        height: winsize.height * 0.7,
                        items: [TabDetailsView()],
                        buttonAlign: 'right',
                        fbar: []
                    })
                ]
            },{
                    layout: 'column',
                    frame: true,
                    columnWidth: 0.33,
                    items: [leftPanelMerchant(), new Ext.Panel({
                        frame: false,
                        border: false,
                        width: winsize.width * 0.32,
                        title: 'Details',						
                        height: winsize.height * 0.35,
                        bodyStyle: {"background-color": "white"},
                        items: [{
							frame: false,
							border: false,
							autoScroll: true,
							layout: 'fit',
                                id: 'details_view_panel_merchantorder',
                                html: '<iframe id="iframe_merchantorder_productdtls" name="iframe_merchantorder_productdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                            }
                        ],
                        buttonAlign: 'right',
                        fbar: [
                        ]

                    })]
                }, {
                    layout: 'column',
                    frame: true,
                    columnWidth: 0.33,
                    items: [
                        new Ext.Panel({
                            frame: false,
                            border: false,
                            width: winsize.width * 0.32,
                            autoHeight: true,
                            id: 'impersonateMerchantpanelParent',
                            title: 'Deligate',
                            //height: winsize.height * 0.9,
                            items: [{
                                    //height: winsize.height * 0.9,
                                    autoScroll: true,
                                    id: 'impersonateMerchantpanel',
                                    html: '<iframe id="iframe_merchant_impersonate" name="iframe_merchant_impersonate"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + iframesrc + '"; ></iframe>',
                                }
                            ],
                            buttonAlign: 'right',
                            tools: [{
                                    id: 'maximize',
                                    qtip: 'Full Window',
                                    handler: function (event, toolEl, panel) {
                                        GuestMerchantView(Application.RetalineOmni.Cache.merchantpath, Application.RetalineOmni.Cache.customerName);
                                    }
                                }],
                        })
                    ]
                }],
                fbar:[{
                    xtype: 'button',
                    text: 'Close',
                    tooltip: 'Close',
                    hidden: true,
                    iconCls: 'my-icon1',
                    icon: './resources/images/default/icons/delete.png',
                    id: 'merchantCallClose',
                    handler: function () {                    
                        Ext.Ajax.request({
                            waitMsg: "Processing",
                            url: outmodURL,
                            params: {
                              op: "exitJobFromUser",
                            },
                            failure: function (response, options) {
                              Ext.MessageBox.alert("Notification", ACTION_FAIL);
                            },
                            success: function (response, options) {
                              eval("var tmp=" + response.responseText);
                              if (tmp.success === true) {
                                Application.example.msg("Success", tmp.msg);                                
                                Ext.getCmp('outmerchantsupportmainWindow').close();
                                Ext.getCmp('gridpanelMasterDataviewOutboundCallsdata').getStore().load();
                                Ext.getCmp('initiatemerchantsupportmainWindow').close();
                                
                              }
                            },
                          });
                    }
                }],
                listeners:{
                    afterrender:function(){
                        var params = (new URL(document.location)).searchParams;
                        if(params.has("phone") === true){
                        var phone = params.get("phone");
                        Ext.getCmp('merchantSearchParameter').setValue(phone);
                        }else if(parseInt(merchantContact)){
                            console.log('part2');
                            Ext.getCmp('merchantSearchParameter').setValue(merchantContact);
                            searchMerchant(merchantContact,'indirect');
                        }else{
                            console.log('part3');
                            Ext.getCmp('merchantSearchParameter').setValue('');
                        }
                        if(Ext.isEmpty(Ext.getCmp('merchantSearchParameter').getValue())){
                            console.log('part4');
                            Ext.getCmp('merchantSearchParameter').setValue('');

                        }
                    }
                }
        });
        return _leadPanel;
    };
    var searchMerchant = function(customerPhone,method) {
        if (!Ext.isEmpty(customerPhone)) {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                method: 'POST',
                url: modURL + '&op=checkMerchantExist',
                params: {
                    customerPhone: customerPhone
                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        if(method == 'direct'){
                            Ext.getCmp('showBtntoms').show();
                            Ext.getCmp('showBtnSearchms').show();
                            Ext.getCmp('merchantCall').show();
                            
                        }else{
                            Ext.getCmp('showBtntoms').hide();
                            Ext.getCmp('showBtnSearchms').hide();
                            Ext.getCmp('merchantCall').show();
                        }
                        if (_SESSION.IsSuperUser == 'Yes') {
                            Ext.getCmp('merchantCallClose').show();
                        }
                        Ext.getCmp('merchantCallFail').show();
                        Ext.getCmp('merchantCallDrop').show();
                        Ext.getCmp('merchantCallCompleted').show();

                        Ext.getCmp('merchantLogin').hide();
                        Ext.getCmp('merchantSptTct').show();
                        Ext.getCmp('createMerchant').hide();
                        Application.RetalineOmni.Cache.searchPhone = customerPhone;
                        Application.RetalineOmni.Cache.merchantId = tmp.cust_id;
                        Application.RetalineOmni.Cache.partnerId = tmp.partnerId;
                        Application.RetalineOmni.Cache.merchantpath = tmp.path;
                        Ext.get('iframe_merchant_impersonate').dom.src = Application.RetalineOmni.Cache.merchantpath;
                        Application.RetalineOmni.MerchantViewMode(tmp.cust_id);
                        Ext.getCmp('merchant_order_grid').getStore().load({
                            params: {
                                merchantId: Application.RetalineOmni.Cache.merchantId
                            }
                        });
                        
                        //callLogTabchangeMerchant();
                        communicationTabchangeMerchant();
                    } else {
                        Ext.getCmp('merchantLogin').hide();
                        Ext.getCmp('createMerchant').hide();
                        Application.RetalineOmni.Cache.merchantId = 0;
                        Application.RetalineOmni.Cache.merchantpath = '';
                        Ext.get('iframe_merchant_impersonate').dom.src = Application.RetalineOmni.Cache.merchantpath;
                        Application.RetalineOmni.MerchantViewMode(0);
                        Ext.getCmp('merchant_order_grid').getStore().load({
                            params: {
                                cust_id: 0
                            }
                        });
                        
                        //callLogTabchangeMerchant();
                        communicationTabchangeMerchant();
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert('Error', "Check the data entered");
        }
    };
    var GuestMerchantView = function () {
        var fun = arguments[0];
        var wintitle = arguments[1];
        var guest_merchant_window = new Ext.Window({
            id: 'guest_merchant_window',
            title: wintitle,
            modal: true,
            constrain: true,
            layout: 'fit',
            maximized: true,
            floating: true,
            shadow: false,
            closeAction: 'close',
            items: new Ext.Panel({
                layout: 'fit',
                height: 580,
                items: [{
                        html: '<iframe id="iframe_reportadd" name="iframe_reportadd" style="overflow:auto;width:100%;height:100%" frameborder="0"  src="' + fun + '"; ></iframe>'
                    }]
            }),
            buttons: [{
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    text: 'Cancel',
                    tabIndex: 32,
                    handler: function () {
                        guest_merchant_window.close();
                    }
                }]
        });
        guest_merchant_window = Ext.getCmp('guest_merchant_window');
        guest_merchant_window.show();
        guest_merchant_window.doLayout();
        guest_merchant_window.center();
    };
    var checkMerchantExistance = function (customerPhone) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=checkMerchantExist',
            params: {
                customerPhone: customerPhone
            },
            success: function (response) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true) {
                    Ext.getCmp('merchantLogin').hide();

                    Ext.getCmp('createMerchant').hide();
                    Application.RetalineOmni.Cache.merchantId = tmp.cust_id;
                    Application.RetalineOmni.Cache.merchantpath = tmp.path;
                    Application.RetalineOmni.Cache.customerName = tmp.data.FullName;
                    Ext.getCmp('impersonateMerchantpanelParent').setTitle('Logged in as ' + tmp.data.FullName);
                    Ext.getCmp('impersonateMerchantpanel').setTitle('Logged in as ' + tmp.data.FullName);

                    Ext.get('iframe_merchant_impersonate').dom.src = Application.RetalineOmni.Cache.merchantpath;
                    Application.RetalineOmni.MerchantViewMode(tmp.cust_id);
                    Ext.getCmp('merchant_order_grid').getStore().load({
                        params: {
                            cust_id: tmp.cust_id
                        }
                    });

                    communicationTabchangeMerchant();
                } else {
                    Ext.getCmp('tabpanelMarketingMerchantform').setActiveTab(0);
                    Ext.getCmp('merchantLogin').hide();
                    Ext.getCmp('createMerchant').hide();
                    Ext.getCmp('impersonateMerchantpanelParent').setTitle('');
                    Application.RetalineOmni.MerchantViewMode(0);
                    Application.RetalineOmni.Cache.merchantId = 0;
                    Application.RetalineOmni.Cache.customerName = '';
                    Ext.getCmp('merchant_order_grid').getStore().load({
                        params: {
                            cust_id: 0
                        }
                    });
                    communicationTabchangeMerchant();
                    Ext.getCmp('gridMarketingMerchantDocumentList').getStore().load({
                        params: {
                            cust_id: 0
                        }
                    });
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
    var leftPanelMerchant = function(){
        var panel = new Ext.TabPanel({
            frame: true,
            activeTab: 0,
            tabPosition: 'top',
            border: false,
            bodyStyle: {
                "background-color": "white"
            },
            id: 'tabpanelMarketingMerchantform',
            width: winsize.width * 0.32,
            height: winsize.height * 0.35,
            items: [{
                title: 'Store Details',
                id: 'titleMarketingMerchantDetails',
                items: [new Ext.Panel({
                    frame: false,
                    border: false,
                    width: winsize.width * 0.32,
                    id: 'MerchantViePanel',
                    height: winsize.height * 0.33,
                    items: [MerchantDetailsView()],
                    buttonAlign: 'right',
                    fbar: []
                })]
            },{
                title: 'Orders',
                id: 'titleMarketingMerchantOrders',
                items: [merchantOrderGrid()]
            },{
                title: 'Customers',
                id: 'titleMarketingMerchantCustomers',
                items: [merchantCustomerGrid()]
            }
            ],
            listeners: {
                tabchange: function (sd, tab) {
                    if (tab.id == 'titleMarketingMerchantDetails')
                    {
                        Application.RetalineOmni.MerchantViewMode(Application.RetalineOmni.Cache.merchantId);
                    } else if (tab.id == 'titleMarketingMerchantOrders')
                    {
                        Ext.getCmp('merchant_order_grid').getStore().load({
                            params: {
                                merchantId: Application.RetalineOmni.Cache.merchantId
                            }
                        });
                    }else if (tab.id == 'titleMarketingMerchantCustomers')
                    {
                        Ext.getCmp('gridMarketingMerchantCustomerList').getStore().load({
                            params: {
                                merchantId: Application.RetalineOmni.Cache.merchantId
                            }
                        });
                    }
                }
            },
            fbar: [],
        });

        return panel;
    };
    var panelMerchantCommunication = function () {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var apikey = _SESSION.apikey;
        var src = '?module=RetalineTeleCaller&op=loadcustomerEditData&crcu_id=' + Application.RetalineOmni.Cache.crcu_id + '&tstamp=' + t_stamp + '&apikey=' + apikey;

        var panel = new Ext.TabPanel({
            frame: false,
            activeTab: 0,
            tabPosition: 'top',
            border: false,
            bodyStyle: {
                "background-color": "white"
            },
            id: 'tabpanelMarketingMerchantform',
            width: winsize.width * 0.32,
            height: winsize.height * 0.35,
            items: [{
                title: 'Call Reason',
                id: 'titleMarketingMerchantCallReason',
                items: [MerchantCallReasonView()]
            },{
                title: 'Tickets',
                id: 'titleMarketingMerchantTickets',
                items: [crmMerchantTicketGrid()]
            },{
                    title: 'Communication',
                    id: 'titleMarketingMerchantcommunication',
                    buttonAlign: 'right',
                    fileUpload: true,
                    defaults: {
                        border: false
                    },
                    items: [crmMerchantCommunicationGrid()], 
                    tbar: [{
                            type: 'button',
                            text: 'Add Communication',
                            id: 'inboundAddcommunicationtabMerchant',
                            iconCls: 'finascop_add',
                            hidden: true,
                            handler: function () {
                                var merchantId = Application.RetalineOmni.Cache.merchantId;
                                merchantCommunicationWindow(merchantId);
                            }
                        }]
                }/*, {
                    title: 'Call Log',
                    hidden:true,
                    id: 'titleMarketingMerchantCallLog',
                    items: [crmMerchantCallLogGrid()]
                },{
                    title: 'Documents',
                    hidden:true,
                    id: 'titleMarketingMerchantdocuments',
                    items: [crmMerchantDocumentGrid()]
                },{
                    title: 'Call Recordings',
                    hidden:true,
                    id: 'titleMarketingMerchantRecordings',
                    items: [crmMerchantRecordingsGrid()]
                }*/

            ],
            listeners: {
                tabchange: function (sd, tab) {
                    
                    if (tab.id == 'titleMarketingMerchantCallReason')
                    {                        
                        var visualsDescPanel = Ext.getCmp('MerchantCallReasonViewPanel');
                        visualsDescPanel.update();
                    } else if (tab.id == 'titleMarketingMerchantTickets'){
                        var ticketGrid_store = Ext.getCmp('gridMarketingMerchantTicketList').getStore();

                        ticketGrid_store.load({
                            params: {
                                phone: Application.RetalineOmni.Cache.searchPhone
                            }
                        });

                    }else if (tab.id == 'titleMarketingMerchantcommunication')
                    {                        
                        communicationTabchangeMerchant();
                    } 
                   /* else if (tab.id == 'titleMarketingMerchantdocuments')
                    {
                        var documentGrid_store = Ext.getCmp('gridMarketingMerchantDocumentList').getStore();

                        documentGrid_store.load({
                            params: {
                                crcu_id: Application.RetalineOmni.Cache.merchantId

                            }
                        });
                    }else if (tab.id == 'titleMarketingMerchantCallLog'){
                        callLogTabchangeMerchant();
                    }else if (tab.id == 'titleMarketingMerchantRecordings'){
                        var recordingGrid_store = Ext.getCmp('gridMarketingMerchantRecordingList').getStore();

                        recordingGrid_store.load({
                            params: {
                                phone: Application.RetalineOmni.Cache.searchPhone
                            }
                        });

                    }*/
                }
            },
            fbar: [
            ],
        });

        return panel;
    };
    var MerchantDetailsView = function () {
        return new Ext.Panel({
            border: false,
            hideBorders: true,
            width: winsize.width * 0.32,
            autoHeight: true,
            id: 'MerchantDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<tpl if="isCustomer == \'1\'"><table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td> {store_group_name} </td></tr>',
                    '<tr><th width="40%">Partner Id </th><td> {partnerId} </td></tr>',
                    '<tr><th width="40%">ID </th><td> {store_group_id} </td></tr>',
                    '<tr><th width="40%">Url </th><td> {siteUrl} </td></tr>',
                    '<tr><th width="40%">Gross Merchant </th><td> {store_group_grosmartMerchant} </td></tr>',
                    '</table></tpl><tpl if="isCustomer == \'0\'"><p>Check Merchant Details</p></tpl>',
                    '</div>')
        });
    };
    var MerchantCallReasonView = function () {
        return new Ext.Panel({
            border: false,
            hideBorders: true,
            width: winsize.width * 0.32,
            autoHeight: true,
            id: 'MerchantCallReasonViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Reason </th><td> {Application.OutboundCalls.Cache.jobTitle} </td></tr>',
                    '</table>',
                    '</div>')
        });
    };

    var TabDetailsView = function () {
        return new Ext.Panel({
            border: false,
            hideBorders: true,
            width: winsize.width * 0.32,
            autoHeight: true,
            id: 'MerchantTabDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<tpl if="tabId == \'1\'"><table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Call Initiated By </th><td> {callInitiatedBy} </td></tr>',
                    '<tr><th width="40%">Date & Time </th><td> {createdOn} </td></tr>',
                    '<tr><th width="40%">Status </th><td> {callActionName} </td></tr>',
                    '<tr><th width="40%">Follow Up Date </th><td> {followupDate} </td></tr>',
                    '<tr><th width="40%">Remarks </th><td> {callRemarks} </td></tr>',                    
                    '</table></tpl>',
                    '<tpl if="tabId == \'2\'"><table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Ticket Number </th><td> {ticketNumber} </td></tr>',
                    '<tr><th width="40%">Ticket Title </th><td> {ticketTitle} </td></tr>',
                    '<tr><th width="40%">Support Beneficiary </th><td> {ticketSupTypeName} </td></tr>', 
                    '<tr><th width="40%">Support Unit </th><td> {ticketSuName} </td></tr>',                    
                    '<tr><th width="40%">Created On </th><td> {createdOn} </td></tr>',
                    '<tr><th width="40%">Created By </th><td> {ticketOwner} </td></tr>',
                    '<tr><th width="40%">Mobile </th><td> {ticketContactNo} </td></tr>',
                    '<tr><th width="40%">Status </th><td> {ticketStatusName} </td></tr>',
                    '<tr><th width="40%">Description </th><td> {ticketDescription} </td></tr>',
                    '<tr><th width="40%">Attachment </th><td>{filename}</td><tpl if="filename != \'\'"><td><button onclick="Application.RetalineOmni.loadUrl(\'{filepath}\')">View</button></td></tpl></tr>',
                    '</table></tpl>',
                    '<tpl if="tabId == \'3\'"><table border="0" width="100%" class="details_view_table">',
                    '<tpl if="entryFrom == \'1\'">',                    
                    '<tr><th width="40%">Date & Time </th><td> {createdOn} </td></tr>',
                    '<tr><th width="40%">Action </th><td> {entryAction} </td></tr>',
                    '<tr><th width="40%">Mode </th><td> {entryMode} </td></tr>',
                    '<tr><th width="40%">Remarks </th><td> {callRemarks} </td></tr>',  
                    '<tr><th width="40%">Attachment </th><td>{filename}</td><tpl if="filename != \'\'"><td><button onclick="Application.RetalineOmni.loadUrl(\'{callRecords}\')">View</button></td></tpl></tr>',
                    '</tpl>',
                    '<tpl if="entryFrom == \'2\'">',                    
                    '<tr><th width="40%">Call Initiated By </th><td> {callInitiatedBy} </td></tr>',
                    '<tr><th width="40%">Date & Time </th><td> {createdOn} </td></tr>',
                    '<tr><th width="40%">Status </th><td> {callActionName} </td></tr>',
                    '<tr><th width="40%">Follow Up Date </th><td> {followupDate} </td></tr>',
                    '<tr><th width="40%">Remarks </th><td> {callRemarks} </td></tr>',  
                    '</tpl>',     
                    '<tpl if="entryFrom == \'3\'">', 
                    '<tr><th width="40%">Recordings </th><td><audio style ="width:100px;height:20px;" controls src={callRecords}></audio></td></tr>',  
                    '</tpl>',    
                    '</table></tpl>',
                    '<tpl if="tabId == \'4\'"><table border="0" width="100%" class="details_view_table">',                    
                    '<tr><th width="40%">Created By </th><td> {resource} </td></tr>',
                    '<tr><th width="40%">Created On </th><td> {date_and_time} </td></tr>',
                    '<tr><th width="40%">Action </th><td> {crma_name} </td></tr>',
                    '<tr><th width="40%">Mode </th><td> {crmm_name} </td></tr>',
                    '<tr><th width="40%">Remark </th><td> {crmc_remark} </td></tr>',
                    '</table></tpl>',
                    '<tpl if="tabId == \'5\'"><table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Date & Time </th><td> {date_and_time} </td></tr>',
                    '<tr><th width="40%">Call Type </th><td> {crma_name} </td></tr>',
                    '<tr><th width="40%">Start Time </th><td> {resource} </td></tr>',
                    '<tr><th width="40%">End Time </th><td> {resource} </td></tr>',  
                    '<tr><th width="40%">Duration </th><td> {resource} </td></tr>',                    
                    '</table></tpl><tpl if="tabId == \'0\'"><p>Details not available</p></tpl>',
                    '</div>')
        });
    };
    var CustTabDetailsView = function () {
        return new Ext.Panel({
            border: false,
            hideBorders: true,
            width: winsize.width * 0.32,
            autoHeight: true,
            id: 'CustomerTabDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',                    
                    '<tpl if="tabId == \'2\'"><table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Ticket Number </th><td> {ticketNumber} </td></tr>',
                    '<tr><th width="40%">Ticket Title </th><td> {ticketTitle} </td></tr>',
                    '<tr><th width="40%">Support Beneficiary </th><td> {ticketSupTypeName} </td></tr>', 
                    '<tr><th width="40%">Support Unit </th><td> {ticketSuName} </td></tr>',                    
                    '<tr><th width="40%">Created On </th><td> {createdOn} </td></tr>',
                    '<tr><th width="40%">Created By </th><td> {ticketOwner} </td></tr>',
                    '<tr><th width="40%">Mobile </th><td> {ticketContactNo} </td></tr>',
                    '<tr><th width="40%">Status </th><td> {ticketStatusName} </td></tr>',
                    '<tr><th width="40%">Description </th><td> {ticketDescription} </td></tr>',
                    '<tr><th width="40%">Attachment </th><td>{filename}</td><tpl if="filename != \'\'"><td><button onclick="Application.RetalineOmni.loadUrl(\'{filepath}\')">View</button></td></tpl></tr>',
                    '</table></tpl>',
                    '<tpl if="tabId == \'3\'"><table border="0" width="100%" class="details_view_table">',
                    '<tpl if="entryFrom == \'1\'">',                    
                    '<tr><th width="40%">Date & Time </th><td> {createdOn} </td></tr>',
                    '<tr><th width="40%">Action </th><td> {entryAction} </td></tr>',
                    '<tr><th width="40%">Mode </th><td> {entryMode} </td></tr>',
                    '<tr><th width="40%">Remarks </th><td> {callRemarks} </td></tr>',  
                    '<tr><th width="40%">Attachment </th><td>{filename}</td><tpl if="filename != \'\'"><td><button onclick="Application.RetalineOmni.loadUrl(\'{callRecords}\')">View</button></td></tpl></tr>',
                    '</tpl>',
                    '<tpl if="entryFrom == \'2\'">',                    
                    '<tr><th width="40%">Call Initiated By </th><td> {callInitiatedBy} </td></tr>',
                    '<tr><th width="40%">Date & Time </th><td> {createdOn} </td></tr>',
                    '<tr><th width="40%">Status </th><td> {callActionName} </td></tr>',
                    '<tr><th width="40%">Follow Up Date </th><td> {followupDate} </td></tr>',
                    '<tr><th width="40%">Remarks </th><td> {callRemarks} </td></tr>',  
                    '</tpl>',     
                    '<tpl if="entryFrom == \'3\'">', 
                    '<tr><th width="40%">Recordings </th><td><audio style ="width:100px;height:20px;" controls src={callRecords}></audio></td></tr>',  
                    '</tpl>',    
                    '</table></tpl>',                 
                    '<tpl if="tabId == \'0\'"><p>Details not available</p></tpl>',
                    '</div>')
        });
    };
    var callLogTabchangeMerchant = function(){
        var callLogGrid_store = Ext.getCmp('gridMarketingMerchantCallLogList').getStore();

        callLogGrid_store.load({
            params: {
                userId:Application.RetalineOmni.Cache.merchantId,
                userType:2,
                mobile:Ext.getCmp('merchantSearchParameter').getValue()
            }
        });
    };
    var communicationTabchangeMerchant = function(){
        var ticketGrid_store = Ext.getCmp('gridMarketingMerchantCommunicationList').getStore();

                        ticketGrid_store.load({
                            params: {
                                userId:Application.RetalineOmni.Cache.merchantId,
                                userType:2,
                                mobile:Ext.getCmp('merchantSearchParameter').getValue()
                            }
                        });
    };
    var communicationTabchangeMerchantOld = function () {
        var crcu_id = Application.RetalineOmni.Cache.merchantId;
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        Ext.Ajax.request({
            url: modURL + '&op=getCommunication',
            method: 'POST',
            params: {
                crcu_id: crcu_id,
                apikey: _SESSION.apikey,
                tstamp: t_stamp
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                var propertygridPanel = Ext.getCmp('_gridCustomerMarketingCommunication');
                propertygridPanel.update(tmp.data);
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', 'Issue in saving');
            }
        });
    };
    var merchantOrderGrid = function () {
        var hfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'order_generated_id'
                }, {
                    type: 'date',
                    dataIndex: 'order_created_on'
                }, {
                    type: 'string',
                    dataIndex: 'payment_by'
                }, {
                    type: 'list',
                    options: ['Customer', 'BA'],
                    phpMode: true,
                    dataIndex: 'order_user_type'
                }, {
                    type: 'numeric',
                    dataIndex: 'order_total_amount'
                }, {
                    type: 'list',
                    options: ['Order Created', 'Order Success', 'Order Failed', 'Order Processing', 'Ready to Dispatch', 'Dispatched', 'Delivered'],
                    phpMode: true,
                    dataIndex: 'order_status'
                }]
        });
        hfilters.remote = true;
        hfilters.autoReload = true;
        var order_store = merchantOrderStore();
        var grid = new Ext.grid.GridPanel({
            store: order_store,
            frame: false,
            border: false,
            width: winsize.width * 0.32,
            height: winsize.height * 0.4,
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), hfilters],
            id: 'merchant_order_grid',
            fields: ['order_auto_id', 'order_generated_id', 'order_status_sort_order', 'order_user_type', 'cust_mobile', 'ordertime',
                'order_total_amount', 'order_created_on', 'order_status', 'order_tax', 'total'
            ],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: function (value, metadata, record) {
                        dateret = Ext.util.Format.date(value, 'd-m-Y');
                        return dateret;
                    }
                },
                {
                    header: 'Order No',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Amount',
                    sortable: true,
                    dataIndex: 'total',
                    tooltip: 'Amount',
                    width: 200,
                    hideable: false,
                    renderer: qtipRenderer
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'PayGatewayId',
                    sortable: true,
                    dataIndex: 'order_payment_gateway_refid',
                    tooltip: 'PayGatewayId',
                    width: 200,
                    hidden: true,
                    renderer: qtipRenderer
                }
            ],
            viewConfig: {
                forceFit: true
            },
            tbar: [],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMerchant
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('order_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.RetalineOmni.Cache.order_auto_id = ID;
                        Application.RetalineOmni.ViewMerchantOrderDetails(Application.RetalineOmni.Cache.order_auto_id,2);
                    }
                },
                afterrender: function () {
                    order_store.load();
                }
            },
            stripeRows: true,
        });
        return grid;
    };
    var merchantOrderStore = function () {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMerchantorders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_payment_gateway_refid', 'order_payment_gateway_refid_crc32', 'order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to',
                {
                    name: 'order_created_on',
                    type: 'date',
                    dateFormat: 'd-m-Y'
                },
                'dispatch_courier', 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time', 'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'total'
            ]),
            sortInfo: {
                field: 'order_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function (store, e) {
                    Ext.getCmp('merchant_order_grid').getView().refresh();
                    Ext.getCmp('merchant_order_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('merchant_order_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('merchant_order_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.RetalineOmni.Cache.order_auto_id = ID;
                        Application.RetalineOmni.ViewMerchantOrderDetails(Application.RetalineOmni.Cache.order_auto_id,2);
                    } else {
                        Application.RetalineOmni.ViewMerchantOrderDetails(0,2);
                    }
                }
            }
        });
        return store;
    };
    var gridSelectionChangedMerchantCustomer = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridMarketingMerchantCustomerList').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridMarketingMerchantCustomerList').getSelectionModel().getSelections()[0].data.cust_id;
            Application.RetalineOmni.ViewMerchantOrderDetails(ID,3);
        }
    };
    var gridSelectionChangedMerchant = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('merchant_order_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('merchant_order_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.RetalineOmni.Cache.order_id = ID;
            Application.RetalineOmni.ViewMerchantOrderDetails(ID,2);
        }
    };
    var gridSelectionChangedMerDoc = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridMarketingMerchantDocumentList').getSelectionModel().getSelections())) {
            var pkid = Ext.getCmp('gridMarketingMerchantDocumentList').getSelectionModel().getSelections()[0].data.crmf_id;
            Application.RetalineOmni.MerchantViewModeonTab('document',Application.RetalineOmni.Cache.merchantId,pkid);

        }else{
            Application.RetalineOmni.MerchantViewModeonTab('document',Application.RetalineOmni.Cache.merchantId,0);
        }
    };
    var gridSelectionChangedMerTicket = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridMarketingMerchantTicketList').getSelectionModel().getSelections())) {
            var pkid = Ext.getCmp('gridMarketingMerchantTicketList').getSelectionModel().getSelections()[0].data.ticketId;
            Application.RetalineOmni.MerchantViewModeonTab('ticket',Application.RetalineOmni.Cache.merchantId,pkid);

        }else{
            Application.RetalineOmni.MerchantViewModeonTab('ticket',Application.RetalineOmni.Cache.merchantId,0);
        }
    };
    var gridSelectionChangedCustTicket = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridMarketingCustomerTicketList').getSelectionModel().getSelections())) {
            var pkid = Ext.getCmp('gridMarketingCustomerTicketList').getSelectionModel().getSelections()[0].data.ticketId;
            Application.RetalineOmni.CustomerViewModeonTab('ticket',Application.RetalineOmni.Cache.customerId,pkid);

        }else{
            Application.RetalineOmni.CustomerViewModeonTab('ticket',Application.RetalineOmni.Cache.customerId,0);
        }
    };
    var gridSelectionChangedMerCalllog = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridMarketingMerchantCallLogList').getSelectionModel().getSelections())) {
            var pkid = Ext.getCmp('gridMarketingMerchantCallLogList').getSelectionModel().getSelections()[0].data.id;
            Application.RetalineOmni.MerchantViewModeonTab('calllog',Application.RetalineOmni.Cache.merchantId,pkid);

        }else{
            Application.RetalineOmni.MerchantViewModeonTab('calllog',Application.RetalineOmni.Cache.merchantId,0);
        }
    };
    var gridSelectionChangedMerCommunication = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridMarketingMerchantCommunicationList').getSelectionModel().getSelections())) {
            var pkid = Ext.getCmp('gridMarketingMerchantCommunicationList').getSelectionModel().getSelections()[0].data.id;
            Application.RetalineOmni.MerchantViewModeonTab('communication',Application.RetalineOmni.Cache.merchantId,pkid);

        }else{
            Application.RetalineOmni.MerchantViewModeonTab('communication',Application.RetalineOmni.Cache.merchantId,0);
        }
    };
    var gridSelectionChangedCustCommunication = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('_gridCustomerMarketingCommunication').getSelectionModel().getSelections())) {
            var pkid = Ext.getCmp('_gridCustomerMarketingCommunication').getSelectionModel().getSelections()[0].data.id;
            Application.RetalineOmni.CustomerViewModeonTab('communication',Application.RetalineOmni.Cache.customerId,pkid);

        }else{
            Application.RetalineOmni.CustomerViewModeonTab('communication',Application.RetalineOmni.Cache.customerId,0);
        }
    };
    var crmMerchantCommunicationStore = function () {
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getAllCommunications',
            fields: ['createdOn', 'entryAction', 'entryMode','callInitiatedBy','entryFrom','id','entryFromName'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingMerchantCommunicationList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var crmMerchantCommunicationGrid = function(){
        var _documentsStore = crmMerchantCommunicationStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMarketingMerchantCommunicationList',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _documentsStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Date and Time',
                        dataIndex: 'createdOn',
                        sortable: true,
                        width: 175
                    }, {
                        header: 'Created By',
                        dataIndex: 'callInitiatedBy'
                    },
                    {
                        header: 'Created From',
                        dataIndex: 'entryFromName'
                    }
                ]

            }),
            iconCls: 'icon-grid',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMerCommunication
                }
            }),
            bbar: new Ext.PagingToolbar({
                pageSize: 12,
                store: _documentsStore,
                displayInfo: true,
                displayMsg: "Displaying records {0} - {1} of {2}",
                emptyMsg: "No pages to display",
              })
        });
        return grid;
    };
    var crmMerchantCommunicationGridTemplate = function (val) {
        var grid = new Ext.Panel({
            region: 'center',
            id: '_gridCustomerMarketingCommunication',
            height: winsize.height * 0.33,
            autoScroll: true,
            tpl: new Ext.XTemplate(
                    '<div class="cdetails-outer no-border" style="width:100%; margin:15px auto;"><h3 class="history"></h3><ul class="anexure">',
                    '<tpl for=".">',
                    '<div class="details-outer">',
                    '<table style="width:100%;">',
                    '<tr>',
                    '<td ><span class="field" style="color:grey;font-weight:bold"><img src = {calender}></span><span class="crmdate">{date_and_time}</span></td>',
                    '<td ><span class="field" style="color:grey;font-weight:bold;align:right;"><img src={crmm_name}></span><span class="crmname" >{resource}</span></td>',
                    '</tr>',
                    '<tpl if="remark != \'\'">',
                    '<tr>',
                    '<td colspan="2" style= "padding-top : 5px">',
                    '<span span class="crmtext">{remark}</span>',
                    '</td>',
                    '</tr>',
                    '</tpl>',
                    '<tr>',
                    '<td style= "padding-top : 5px"><span class="crmresponse">{response} </span> </td>',
                    '</tr>',
                    '<td colspan="2">',
                    '<hr style="color:grey"></td></tr>',
                    '</table>',
                    '</div></li>',
                    '</tpl>',
                    '</ul></div>',
                    '<style>.field{ padding-right: 10px; }</style>'
                    )

        })
        return grid;
    };
    var merchantCommunicationWindow = function (_val) {
        var _custAction_store = comboselectionActionCustomersStore();
        var _customerAction_mode = combomodeContactCustomersStore();
        fileS3BucketView();
        var reswindow = new Ext.Window({
            title: 'Communication Details',
            width: 600,
            autoHeight: true,
            plain: true,
            constrainHeader: true,
            modal: true,
            frame: true,
            border: false,
            iconCls: '',
            resizable: false,
            id: 'CrmCustomersCommunicationWindow',
            items: [
                new Ext.form.FormPanel({
                    fileUpload: true,
                    layout: 'column',
                    id: 'formpanelCrmCustomersCommunication',
                    bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
                    labelAlign: 'top',
                    items: [
                        {
                            xtype: 'hidden',
                            id: 's3_filename',
                            name: 's3_filename'
                        }, {
                            xtype: 'hidden',
                            id: 's3_albumBucketName',
                            name: 's3_albumBucketName'
                        }, {
                            xtype: 'hidden',
                            id: 's3_accessKey',
                            name: 's3_accessKey'
                        }, {
                            xtype: 'hidden',
                            id: 's3_secretKey',
                            name: 's3_secretKey'
                        }, {
                            xtype: 'hidden',
                            id: 's3_bucketRegion',
                            name: 's3_bucketRegion'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3_oncompleteurl',
                            name: 's3_oncompleteurl'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3_img_path_db',
                            name: 's3_img_path_db'
                        },
                        {
                            xtype: 'hidden',
                            id: 's3filepath',
                            name: 's3filepath'
                        },
                        {
                            columnWidth: .5,
                            layout: 'form',
                            border: false,
                            items: [
                                {
                                    xtype: 'combo',
                                    id: 'comboSelectActionCustomers',
                                    name: 'comboSelectActionCustomers',
                                    fieldLabel: 'Select Action',
                                    hiddenName: '_comboSelectActionCustomers',
                                    store: _custAction_store,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    typeAhead: true,
                                    selectOnFocus: true,
                                    mode: 'local',
                                    allowBlank: false,
                                    valueField: 'crma_id',
                                    displayField: 'crma_name',
                                    anchor: '95%'
                                }
                            ]
                        },
                        {
                            columnWidth: .47,
                            layout: 'form',
                            border: false,
                            items: [{
                                    xtype: 'combo',
                                    id: 'comboModeOfContactCustomers',
                                    fieldLabel: 'Mode of Contact',
                                    name: 'comboModeOfContactCustomers',
                                    hiddenName: '_comboModeOfContactCustomers',
                                    store: _customerAction_mode,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    typeAhead: true,
                                    selectOnFocus: true,
                                    allowBlank: false,
                                    mode: 'local',
                                    valueField: 'crmm_id',
                                    displayField: 'crmm_name',
                                    anchor: '95%'
                                }]
                        },
                        {
                            columnWidth: 1,
                            layout: 'form',
                            border: false,
                            items: [{
                                    xtype: 'textarea',
                                    id: 'textareaRemarksCustomers',
                                    fieldLabel: 'Remarks',
                                    name: 'textareaRemarksCustomers',
                                    anchor: '95%',
                                    width: 500,
                                    maxLength: 500
                                }]
                        },
                        {
                            columnWidth: .16,
                            border: false,
                            buttons: [
                                {
                                    xtype: 'fileuploadfield',
                                    style: 'margin-bottom:5px;',
                                    id: 'fileuploadfieldAttachFileCustomers',
                                    border: false,
                                    anchor: '97%',
                                    name: 'fileuploadfieldAttachFileCustomers',
                                    allowBlank: true,
                                    buttonOnly: true,
                                    buttonCfg: {
                                        text: 'Upload File',
                                        border: false,
                                        width: 100
                                    },
                                    validator: function (v) {
                                        if (v != '')
                                        {
                                            v = v.toLowerCase();
                                            var exp = /^.*\.(png|jpg|gif|pdf|docx)$/i;
                                            if (!(exp.test(v)))
                                            {
                                                return 'Upload a valid image file of format JPG.';
                                            }

                                            var associated_file = Ext.getCmp('fileuploadfieldAttachFileCustomers').getValue();
                                            if (associated_file == '')
                                            {
                                                Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
                                                return;
                                            }
                                            addFile();
                                            return true;
                                        }
                                    }
                                }
                            ]
                        }, {
                            columnWidth: 1,
                            layout: 'form',
                            border: false,
                            items: [{xtype: 'displayfield', id: 'supporting', cls: 'fpcls', value: 'File uploaded successfully', hidden: true}]
                        }
                    ]
                })],
            buttons: [
                {
                    text: 'Cancel',
                    iconCls: 'so_upload',
                    handler: function () {
                        reswindow.close();
                    }

                }, {
                    text: 'Save',
                    iconCls: 'so_upload',
                    handler: function () {
                        Application.RetalineOmni.communicationDataSaveApiMerchant();
                    }

                }
            ]
        });
        reswindow.doLayout();
        reswindow.show();
        reswindow.center();
    };
    var merchantCallCommunicationWindow = function (type) {
        fileS3BucketView();
        var reswindow = new Ext.Window({
            title: 'Call Details - ' + type,
            width: 750,
            autoHeight: true,
            plain: true,
            constrainHeader: true,
            modal: true,
            frame: true,
            border: false,
            iconCls: '',
            resizable: false,
            id: 'CallCommunicationWindow',
            items: [
                new Ext.form.FormPanel({
                    layout: 'column',
                    id: 'formpanelCallCommunication',
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
                    frame: true,
                    items: [                                              
                        {
                            columnWidth: 1,
                            layout: 'form',
                            border: false,
                            bodyStyle: {"padding": "5px"},
                            items: [{
                                    xtype: 'textarea',
                                    id: 'callRemarks',
                                    fieldLabel: 'Remarks',
                                    name: 'callRemarks',
                                    allowblank: false,
                                    anchor: '99%',
                                    maxLength: 500
                                }]
                        }
                    ]
                })],
            fbar: [{ id:"alternateNoLabel",hidden:true,html: "Alternate No : &nbsp;" },{
                fieldLabel: "Alternate No",
                xtype: "textfield",
                allowblank: false,
                id: "alternateNo",
                anchor: "98%",
                hidden: true,
                width:100,
                maxLength: 14
              },{ html: "Followup Date : &nbsp;" },{
                fieldLabel: "Followup Date",
                xtype: "datefield",
                allowblank: false,
                id: "followupDate",
                anchor: "98%",
                width:100,
                format: "d-m-Y",
                value: new Date().format("d-m-Y"),
              },{html: '&nbsp;Time : &nbsp;'}, {
                xtype: 'timefield',
                fieldLabel: 'Time',
                id: 'followupTime',
                name: 'followupTime',
                anchor: '98%',
                allowBlank: false,
                format: "H:i",
                //value: new Date().getHours() + 1,
                width: 80,
                tabIndex: 501,
                listeners:{
                    afterrender: function(timefield) {
                        // Get the current time
                        var currentTime = new Date();
                
                        // Add 1 hour
                        currentTime.setHours(currentTime.getHours() + 1);
                
                        // Format the time to HH:mm
                        var formattedTime = Ext.util.Format.date(currentTime, 'H:i');
                
                        // Set the initial value of the time field
                        timefield.setRawValue(formattedTime);
                    }
                }
            },'-',
            {
                xtype: "button",
                text: 'Retry',
                hidden: true,
                icon: './resources/images/default/icons/call.png',
                id: 'merchantCallRetry',
                handler: function () {
                    var customerPhone = Ext.getCmp('merchantSearchParameter').getValue();
                        if (!Ext.isEmpty(customerPhone)) {
                            Ext.Ajax.request({
                                url: modURL + '&op=outboundCall',
                                method: 'POST',
                                params: {
                                    phone: customerPhone,
                                    jobId : Application.OutboundCalls.Cache.jobId
                                },
                                success: function (response) {
                                     var tmp = Ext.decode(response.responseText);
                    if (tmp.success == false) {
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }else{
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', 'Issue in saving');
                                }
                            });
                        }
                }

            },
                {
                    xtype: "button",
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'so_upload',
                    handler: function () {
                        reswindow.close();
                    }

                }, {
                    xtype: "button",
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    iconCls: 'so_upload',
                    handler: function () {
                        Application.RetalineOmni.callLogApiMerchant(type);
                    }

                }
            ]
        });
        if(type == 'Dropped'){
            Ext.getCmp('merchantCallRetry').show();
        }
        if(type == 'Completed'){
            Ext.getCmp('alternateNoLabel').show();
            Ext.getCmp('alternateNo').show();
        }
        reswindow.doLayout();
        reswindow.show();
        reswindow.center();
    };
    var crmMerchantDocumentStore = function () {
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getDocumentGridData',
            fields: ['date_and_time', 'resource', 'crma_name', 'crmf_filepath', 'crmf_filename', 'fileextension','crmf_id'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingMerchantDocumentList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var crmMerchantDocumentGrid = function () {
        var _documentsStore = crmMerchantDocumentStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMarketingMerchantDocumentList',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _documentsStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Resource',
                        dataIndex: 'resource'
                    },
                    {
                        header: 'Date and Time',
                        dataIndex: 'date_and_time',
                        sortable: true,
                        width: 175
                    },{
                        header: 'Action',
                        dataIndex: 'crma_name'
                    },                    
                    {
                        header: 'Attachment',
                        xtype: 'actioncolumn',
                        items: [
                            {
                                getClass: function (v, meta, rec) {
                                    var data = rec.data;
                                    var _fileext = data.fileextension;
                                    if (_fileext == 'pdf')
                                    {

                                        return 'finascop_marketing_pdfdoc';
                                    } else if (_fileext == 'jpg' || _fileext == 'png')
                                    {

                                        return 'finascop_marketing_imgicon';
                                    } else
                                    {
                                        return 'finascop_marketing_worddoc';
                                    }

                                },
                                handler: function (grid, rowIndex, colIndex, itm, evn) {
                                    var record = grid.getStore().getAt(rowIndex);
                                    var _filepath = record.data.crmf_filepath;
                                    window.open(_filepath, '_blank');
                                }
                            }
                        ]
                    }
                ]

            }),
            iconCls: 'icon-grid',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMerDoc
                }
            })
        });
        return grid;
    };
    var crmMerchantTicketStore = function () {
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getTicketGridData',
            fields: ['ticketNumber','ticketDescription','createdOn','ticketStatusName','ticketId','ticketOwner','createdFrom'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {                
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingMerchantTicketList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var crmMerchantTicketGrid = function () {
        var _ticketStore = crmMerchantTicketStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMarketingMerchantTicketList',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _ticketStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            tbar:[{
                iconCls: 'finascop_add',
                xtype: 'button',
                id: 'merchantSptTct',
                hidden: true,
                text: 'Create Ticket',
                tooltip: 'Create Ticket',
                icon: './resources/images/default/icons/add.png',
                handler: function () {
                    var type = Application.OutboundCalls.Cache.mctType;
                    var mobile = Application.OutboundCalls.Cache.mctMobile;
                    var name = Application.OutboundCalls.Cache.mctName;
                    var email = Application.OutboundCalls.Cache.mctEmail;
                    Application.SupportTicket.supportRequestExtWindow(type,mobile,name,email);

                }
            }],
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Ticket Number',
                        dataIndex: 'ticketNumber'
                    },
                    {
                        header: 'Created By',
                        dataIndex: 'ticketOwner'
                    },
                    {
                        header: 'Date and Time',
                        dataIndex: 'createdOn',
                        sortable: true,
                        width: 175
                    },                    
                    {
                        header: 'Status',
                        dataIndex: 'ticketStatusName'
                    }
                ]

            }),
            iconCls: 'icon-grid',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMerTicket
                }
            })
        });
        return grid;
    };
    var crmMerchantCallLogStore = function(){
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getCallLogGridData',
            fields: ['id','userType','userId','followupDate','callRemarks','callRecords','createdOn','createdBy','callActionName','callInitiatedBy'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload:function(){
                    this.baseParams.phone = Application.OutboundCalls.Cache.mctMobile;
                    this.baseParams.userType = Application.OutboundCalls.Cache.mctType;
                },
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingMerchantCallLogList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var crmMerchantCallLogGrid = function () {
        var _calllogStore = crmMerchantCallLogStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMarketingMerchantCallLogList',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _calllogStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Call Initiated By',
                        dataIndex: 'callInitiatedBy',
                        sortable: true,
                    },
                    {
                        header: 'Date and Time',
                        dataIndex: 'createdOn',
                        sortable: true,
                    },{
                        header: 'Status',
                        dataIndex: 'callActionName',
                        sortable: true,
                    }, {
                        header: 'Followup Date',
                        dataIndex: 'followupDate',
                        sortable: true,
                    }               
                    
                ]

            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMerCalllog
                }
            }),
            tbar:[],
            iconCls: 'icon-grid',
        });
        return grid;
    };
    var crmMerchantRecordingStore = function () {
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getRecordingGridData',
            fields: ['id','StartTime', 'Duration', 'DialStatus', 'CustomerStatus', 'AudioFile', 'AgentName'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingMerchantRecordingList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var crmMerchantRecordingsGrid = function () {
        var _documentsStore = crmMerchantRecordingStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMarketingMerchantRecordingList',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _documentsStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Date and Time',
                        dataIndex: 'StartTime',
                        sortable: true,
                        width: 175
                    },
                    {
                        header: 'Duration',
                        dataIndex: 'Duration'
                    },{
                        header: 'DialStatus',
                        dataIndex: 'DialStatus'
                    },{
                        header: 'Dailed By',
                        dataIndex: 'AgentName'
                    },{
                        xtype: 'templatecolumn',
                        dataIndex: 'AudioFile',
                        width:100,
                        tpl: '<audio style ="width:100px;height:20px;" controls src={AudioFile}></audio>'
                    },
                    {
                        header: 'Recordings',
                        xtype: 'actioncolumn',
                        hidden:true,
                        items: [
                            {
                                getClass: function (v, meta, rec) {
                                    var AudioFile = rec.data.AudioFile;
                                    if(!Ext.isEmpty(AudioFile)){
                                        return 'call_play';
                                    }else{
                                        return '';
                                    }

                                },
                                handler: function (grid, rowIndex, colIndex, itm, evn) {
                                    var record = grid.getStore().getAt(rowIndex);
                                    var _filepath = record.data.AudioFile;
                                    //window.open(_filepath, '_blank');
                                    var audio = new Audio(_filepath);
                                    audio.play();
                                }
                            }
                        ]
                    }
                ]

            }),
            iconCls: 'icon-grid',
        });
        return grid;
    };
    var crmMerchantCustomerStore = function(){
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getCustomerGridData',
            fields: ['cust_id','cust_mobile','cust_email','cust_customer_name'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload:function(){
                    this.baseParams.merchantId = Application.RetalineOmni.Cache.merchantId;
                },
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingMerchantCustomerList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var merchantCustomerGrid = function(){
        var _calllogStore = crmMerchantCustomerStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMarketingMerchantCustomerList',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _calllogStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Name',
                        dataIndex: 'cust_customer_name',
                        sortable: true,
                    }, {
                        header: 'Mobile',
                        dataIndex: 'cust_mobile',
                        sortable: true,
                    },{
                        header: 'Email',
                        dataIndex: 'cust_email',
                        sortable: true,
                        width: 175,
                        renderer: qtipRenderer
                    }                   
                    
                ]

            }),
            tbar:[],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedMerchantCustomer
                }
            }),
            iconCls: 'icon-grid',
        });
        return grid;
    };
    var MerchantSearch = function () {
        var wintitle = "Store Search";
        var merchant_search_window = new Ext.Window({
            id: 'merchant_search_window',
            title: wintitle,
            modal: true,
            width: winsize.width * 0.45,
            height: winsize.height * 0.5,
            constrain: true,
            layout: 'fit',
            floating: true,
            shadow: false,
            closeAction: 'close',
            items: [merchantSearchResultGrid()],
            buttons: []     
            
        });
        merchant_search_window = Ext.getCmp('merchant_search_window');
        merchant_search_window.show();
        merchant_search_window.doLayout();
        merchant_search_window.center();
    };
    var merchantSearchResultGrid = function(){
        var _storeSearchStore = crmMerchantSearchStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMerchatStoreSearch',
            height: winsize.height * 0.40,
            autoScroll: true,
            frame: true,
            border: false,
            store: _storeSearchStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
              }),
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Store Name',
                        dataIndex: 'store_group_name',
                        sortable: true,
                    }, {
                        header: 'Contact No',
                        dataIndex: 'br_Phone',
                        sortable: true,
                    },{
                        header: 'State',
                        dataIndex: 'stateName',
                        sortable: true,
                        width: 175,
                        renderer: qtipRenderer
                    },{
                        header: 'Select',
                        xtype: 'actioncolumn',
                        items: [
                            {
                                iconCls:'my-icon46',   
                                tooltip: 'Select',   
                                text: 'Select',                          
                                handler: function (grid, rowIndex, colIndex, itm, evn) {                                   

                                    var record = grid.getStore().getAt(rowIndex);
                                    var br_Phone = record.get('br_Phone');
                                    var merchantId = record.get('store_group_id');
                                    Ext.getCmp('merchantSearchParameter').setValue(br_Phone);
                                    Ext.getCmp('merchant_search_window').close();
                                    searchMerchant(br_Phone,'direct');
                                }
                            }
                        ]
                    }]

            }),
            tbar:[{
                xtype: 'textfield',
                tabIndex: 702,
                id: 'storeSearchParameter',
                name: 'storeSearchParameter',
                allowBlank: false,
                width:300,
                anchor: '95%',
                maxLength: 100,
                listeners: {
                    afterrender: function (field) {
                        Ext.defer(function () {
                            field.focus(true, 100);
                        }, 1);
                    }
                }
            }, {
                xtype: 'button',
                text: 'Search',
                iconCls: 'finascop_search_btn',
                style: "padding-left: 10px;",
                tabIndex: 703,
                handler: function () {
                    Ext.getCmp('gridMerchatStoreSearch').getStore().load({
                        params:{
                            searchName:Ext.getCmp('storeSearchParameter').getValue()
                        }
                });
                }
            }],
            iconCls: 'icon-grid',
            listeners: {
                afterrender: function (grid) {},
                celldblClick: function (grid, rowIndex, e) {
                    console.log('here');
                    var record = grid.getStore().getAt(rowIndex);
                    var br_Phone = record.get('br_Phone');
                    Ext.getCmp('merchantSearchParameter').setValue(br_Phone);
                    Ext.getCmp('merchant_search_window').close();
                },
              },
        });
        return grid;
    };
    var crmMerchantSearchStore = function(){
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getStoreSearchGridData',
            fields: ['store_group_id','store_group_name','br_Phone','stateName'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload:function(){
                },
                load: function (store, record, options) {
                   
                }
            }
        });        
        return _store;
    };
    var CustomerSearch = function () {
        var wintitle = "Customer Search";
        var customer_search_window = new Ext.Window({
            id: 'customer_search_window',
            title: wintitle,
            modal: true,
            width: winsize.width * 0.45,
            height: winsize.height * 0.5,
            constrain: true,
            layout: 'fit',
            floating: true,
            shadow: false,
            closeAction: 'close',
            items: [customerSearchResultGrid()],
            buttons: []     
            
        });
        customer_search_window = Ext.getCmp('customer_search_window');
        customer_search_window.show();
        customer_search_window.doLayout();
        customer_search_window.center();
    };
    var customerSearchResultGrid = function(){
        var _customerSearchStore = crmCustomerSearchStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridCustomerSearch',
            height: winsize.height * 0.40,
            autoScroll: true,
            frame: true,
            border: false,
            store: _customerSearchStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
              }),
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Name',
                        dataIndex: 'cust_customer_name',
                        sortable: true,
                    }, {
                        header: 'Contact No',
                        dataIndex: 'cust_mobile',
                        sortable: true,
                    },{
                        header: 'Wallet',
                        dataIndex: 'cust_walletbalance',
                        sortable: true,
                    },{
                        header: 'Select',
                        xtype: 'actioncolumn',
                        items: [
                            {
                                iconCls:'my-icon46',   
                                tooltip: 'Select',   
                                text: 'Select',                          
                                handler: function (grid, rowIndex, colIndex, itm, evn) {                                   

                                    var record = grid.getStore().getAt(rowIndex);
                                    var cust_mobile = record.get('cust_mobile');
                                    Ext.getCmp('omniSearchParameter').setValue(cust_mobile);
                                    Ext.getCmp('customer_search_window').close();
                                    searchCustomer(cust_mobile,'direct');
                                }
                            }
                        ]
                    }]

            }),
            tbar:[{
                xtype: 'textfield',
                tabIndex: 702,
                id: 'custSearchParameter',
                name: 'custSearchParameter',
                allowBlank: false,
                width:300,
                anchor: '95%',
                maxLength: 100,
                listeners: {
                    afterrender: function (field) {
                        Ext.defer(function () {
                            field.focus(true, 100);
                        }, 1);
                    }
                }
            }, {
                xtype: 'button',
                text: 'Search',
                tabIndex: 703,
                iconCls: 'finascop_search_btn',
                style: "padding-left: 10px;",
                handler: function () {
                    Ext.getCmp('gridCustomerSearch').getStore().load({
                        params:{
                            searchName:Ext.getCmp('custSearchParameter').getValue()
                        }
                });
                }
            }],
            iconCls: 'icon-grid',
            listeners: {
                afterrender: function (grid) {},
                celldblClick: function (grid, rowIndex, e) {
                    console.log('here');
                    var record = grid.getStore().getAt(rowIndex);
                    var cust_mobile = record.get('cust_mobile');
                    Ext.getCmp('omniSearchParameter').setValue(cust_mobile);
                    Ext.getCmp('customer_search_window').close();
                    searchCustomer(cust_mobile,'direct');
                },
              },
        });
        return grid;
    };
    var crmCustomerSearchStore = function(){
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getCustomerSearchGridData',
            fields: ['cust_id','cust_customer_name','cust_mobile','cust_walletbalance'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload:function(){
                },
                load: function (store, record, options) {
                   
                }
            }
        });        
        return _store;
    };
    var searchCustomer = function(customerPhone,method){
        if (!Ext.isEmpty(customerPhone)) {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                method: 'POST',
                url: modURL + '&op=checkCustomerExist',
                params: {
                    customerPhone: customerPhone
                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        Ext.getCmp('customerLogin').hide();

                        Ext.getCmp('createCustomer').hide();

                        if(method == 'direct'){
                            Ext.getCmp('showBtnto').show();
                            Ext.getCmp('showBtnSearchcust').show();
                            Ext.getCmp('customerCall').show();
                            
                        }else{
                            Ext.getCmp('showBtnto').hide();
                            Ext.getCmp('showBtnSearchcust').hide();
                            Ext.getCmp('customerCall').show();
                        }
                        Ext.getCmp('customerCallFail').show();
                        Ext.getCmp('customerCallDrop').show();
                        Ext.getCmp('customerCallCompleted').show();

                        Application.RetalineOmni.Cache.searchPhone = customerPhone;
                        Application.RetalineOmni.Cache.customerId = tmp.cust_id;
                        Application.RetalineOmni.Cache.loginpath = tmp.path;
                        Ext.get('iframe_omniorder_impersonate').dom.src = Application.RetalineOmni.Cache.loginpath;
                        Application.RetalineOmni.customerViewMode(tmp.cust_id);
                        Ext.getCmp('customer_order_grid').getStore().load({
                            params: {
                                cust_id: tmp.cust_id
                            }
                        });
                        communicationTabchange();
                    } else {
                        Ext.getCmp('customerLogin').hide();
                        Ext.getCmp('createCustomer').show();
                        Application.RetalineOmni.Cache.customerId = 0;
                        Application.RetalineOmni.Cache.loginpath = '';
                        Ext.get('iframe_omniorder_impersonate').dom.src = Application.RetalineOmni.Cache.loginpath;
                        Application.RetalineOmni.customerViewMode(0);
                        Ext.getCmp('customer_order_grid').getStore().load({
                            params: {
                                cust_id: 0
                            }
                        });
                        communicationTabchange();
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert('Error', "Check the data entered");
        }
        
    };
    var merchantImpersonatePanel = function (merctImpersoPath,id) {
        return new Ext.Panel({
            layout: "fit",
            title: 'Upload',
            id: id,
            html: '<iframe id="downloadContactsIframe" name="downloadContactsIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="'+merctImpersoPath+'"; ></iframe>'
        });
    };
    var crmCustomerTicketStore = function () {
        var _store = new Ext.data.JsonStore({
            url: modURL + '&op=getTicketGridData',
            fields: ['ticketNumber','ticketDescription','createdOn','ticketStatusName','ticketId','ticketOwner','createdFrom'],
            remoteSort: true,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {                
                load: function (store, record, options) {
                    if (record.length > 0) {
                        Ext.getCmp('gridMarketingCustomerTicketList').getSelectionModel().selectRow(0);
                    }
                }
            }
        });
        _store.load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
        return _store;
    };
    var crmCustomerTicketGrid = function () {
        var _ticketStore = crmCustomerTicketStore();
        var grid = new Ext.grid.GridPanel({
            id: 'gridMarketingCustomerTicketList',
            region: 'center',
            columnWidth: .7,
            height: winsize.height * 0.68,
            autoScroll: true,
            frame: true,
            border: false,
            store: _ticketStore,
            loadMask: true,
            viewConfig: {
                forceFit: true
            },
            tbar:[{
                iconCls: 'finascop_add',
                xtype: 'button',
                id: 'customerSptTct',
                hidden: true,
                text: 'Create Ticket',
                tooltip: 'Create Ticket',
                icon: './resources/images/default/icons/add.png',
                handler: function () {
                    var type = Application.OutboundCalls.Cache.mctType;
                    var mobile = Application.OutboundCalls.Cache.mctMobile;
                    var name = Application.OutboundCalls.Cache.mctName;
                    var email = Application.OutboundCalls.Cache.mctEmail;
                    Application.SupportTicket.supportRequestExtWindow(type,mobile,name,email);

                }
            }],
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Ticket Number',
                        dataIndex: 'ticketNumber'
                    },
                    {
                        header: 'Created By',
                        dataIndex: 'ticketOwner'
                    },
                    {
                        header: 'Date and Time',
                        dataIndex: 'createdOn',
                        sortable: true,
                        width: 175
                    },                    
                    {
                        header: 'Status',
                        dataIndex: 'ticketStatusName'
                    }
                ]

            }),
            iconCls: 'icon-grid',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedCustTicket
                }
            })
        });
        return grid;
    };
    var customerCallCommunicationWindow = function (type) {
        fileS3BucketView();
        var reswindow = new Ext.Window({
            title: 'Call Details - ' + type,
            width: 750,
            autoHeight: true,
            plain: true,
            constrainHeader: true,
            modal: true,
            frame: true,
            border: false,
            iconCls: '',
            resizable: false,
            id: 'CallCommunicationWindow',
            items: [
                new Ext.form.FormPanel({
                    layout: 'column',
                    id: 'formpanelCallCommunication',
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: { "background-color": "white", padding: "5px 5px 5px 10px" },
                    frame: true,
                    items: [                                              
                        {
                            columnWidth: 1,
                            layout: 'form',
                            border: false,
                            bodyStyle: {"padding": "5px"},
                            items: [{
                                    xtype: 'textarea',
                                    id: 'callRemarks',
                                    fieldLabel: 'Remarks',
                                    name: 'callRemarks',
                                    allowblank: false,
                                    anchor: '99%',
                                    maxLength: 500
                                }]
                        }
                    ]
                })],
            fbar: [{ html: "Followup Date : &nbsp;" },{
                fieldLabel: "Followup Date",
                xtype: "datefield",
                allowblank: false,
                id: "followupDate",
                anchor: "98%",
                width:150,
                format: "d-m-Y",
                value: new Date().format("d-m-Y"),
              },{html: '&nbsp;Time : &nbsp;'}, {
                xtype: 'timefield',
                fieldLabel: 'Time',
                id: 'followupTime',
                name: 'followupTime',
                anchor: '98%',
                allowBlank: false,
                format: "H:i",
                //value: new Date().getHours() + 1,
                width: 100,
                tabIndex: 501,
                listeners:{
                    afterrender: function(timefield) {
                        // Get the current time
                        var currentTime = new Date();
                
                        // Add 1 hour
                        currentTime.setHours(currentTime.getHours() + 1);
                
                        // Format the time to HH:mm
                        var formattedTime = Ext.util.Format.date(currentTime, 'H:i');
                
                        // Set the initial value of the time field
                        timefield.setRawValue(formattedTime);
                    }
                }
            },'-',
            {
                xtype: "button",
                text: 'Retry',
                hidden: true,
                icon: './resources/images/default/icons/call.png',
                id: 'merchantCallRetry',
                handler: function () {
                    var customerPhone = Ext.getCmp('omniSearchParameter').getValue();
                        if (!Ext.isEmpty(customerPhone)) {
                            Ext.Ajax.request({
                                url: modURL + '&op=outboundCall',
                                method: 'POST',
                                params: {
                                    phone: customerPhone,
                                    jobId : Application.OutboundCalls.Cache.jobId
                                },
                                success: function (response) {
                                     var tmp = Ext.decode(response.responseText);
                    if (tmp.success == false) {
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }else{
                        Ext.MessageBox.alert('Notification', tmp.msg);
                    }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', 'Issue in saving');
                                }
                            });
                        }
                }

            },
                {
                    xtype: "button",
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'so_upload',
                    handler: function () {
                        reswindow.close();
                    }

                }, {
                    xtype: "button",
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    iconCls: 'so_upload',
                    handler: function () {
                        Application.RetalineOmni.callLogApiCustomer(type);
                    }

                }
            ]
        });
        if(type == 'Dropped'){
            Ext.getCmp('merchantCallRetry').show();
        }
        
        reswindow.doLayout();
        reswindow.show();
        reswindow.center();
    };
    return {
        Cache: {},
        initOmni: function () {
            var panelId = 'omnimainpanel';
            var omni_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(omni_panel))
            {
                omni_panel = omniMainTabPanel(panelId);
                Application.UI.addTab(omni_panel);
                omni_panel.doLayout();
            } else
            {
                Application.UI.addTab(omni_panel);
                omni_panel.doLayout();
            }


        }, ViewOrderDetails: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var fsto_id = arguments[0];
            var tabId = arguments[1];
            Ext.getCmp('details_view_panel_scheduledorder').show();

            Ext.get('iframe_omniorder_productdtls').dom.src = modURL + '&op=order_details_view&order_auto_id=' + fsto_id + "&tabId=" + tabId + "&tstamp=" + t_stamp;
        }, customerViewMode: function () {
            var cust_id = arguments[0];
            Ext.getCmp('CustViePanel').setTitle('View Details');
            Ext.getCmp('CustViePanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=custDetailsView',
                method: 'POST',
                params: {cust_id: cust_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('CustDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                        if (tmp.cust_id > 0) {
                            Application.OutboundCalls.Cache.mctType = 'Customer';
                            Application.OutboundCalls.Cache.mctMobile = tmp.cust_mobile;
                            Application.OutboundCalls.Cache.mctName = tmp.cust_customer_name;
                            Application.OutboundCalls.Cache.mctEmail = tmp.cust_email;
                            Application.OutboundCalls.Cache.userId = cust_id;
                            Ext.getCmp('customerSptTct').show();
                            Ext.getCmp('buttonAddcommunicationtab').show();
                        } else {
                            Ext.getCmp('customerSptTct').hide();
                            Ext.getCmp('buttonAddcommunicationtab').hide();
                        }

                    } else {
                        var visualsDescPanel = Ext.getCmp('CustDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('CustViePanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('CustViePanel').doLayout();
        }, communicationDataSaveApi: function () {
            var _customerCommunication_save = Ext.getCmp('formpanelCrmCustomersCommunication');
            var cust_id = Application.RetalineOmni.Cache.customerId;
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            _customerCommunication_save.getForm().submit({
                waitTitle: 'Please Wait',
                waitMsg: 'Saving',
                url: modURL + '&op=insertCommunicationDetails',
                params: {
                    crmc_id: cust_id,
                    mobile:Ext.getCmp('omniSearchParameter').getValue(),
                    jobId:Application.OutboundCalls.Cache.jobId,                  
                    type:1,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                },
                success: function (form, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success == true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
                            communicationTabchange();
                            Ext.getCmp('CrmCustomersCommunicationWindow').close();
                        });
                    }
                },
                failure: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp);
                }
            });
        }, categoryStructure: function () {
            var panelId = 'allcategorypanel';
            var allcategory_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(allcategory_panel))
            {
                allcategory_panel = allcategory_panelTabPanel(panelId);
                Application.UI.addTab(allcategory_panel);
                allcategory_panel.doLayout();
            } else
            {
                Application.UI.addTab(allcategory_panel);
                allcategory_panel.doLayout();
            }
        },initMerchantSupport: function (merchantContact) {
            var panelId = 'merchantsupportmainpanel';
            var merchantsupport_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(merchantsupport_panel))
            {
                merchantsupport_panel = merchantSupportMainTabPanel(panelId,merchantContact);
                Application.UI.addTab(merchantsupport_panel);
                merchantsupport_panel.doLayout();
            } else
            {
                Application.UI.addTab(merchantsupport_panel);
                merchantsupport_panel.doLayout();
            }


        },ViewMerchantOrderDetails: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var fsto_id = arguments[0];
            var tabId = arguments[1];
            Ext.getCmp('details_view_panel_merchantorder').show();

            Ext.get('iframe_merchantorder_productdtls').dom.src = modURL + '&op=order_details_view&order_auto_id=' + fsto_id + "&tabId=" + tabId + "&tstamp=" + t_stamp;
        },MerchantViewMode: function () {
            var cust_id = arguments[0];
            Ext.getCmp('MerchantViePanel').setTitle('View Details');
            Ext.getCmp('MerchantViePanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=merchantDetailsView',
                method: 'POST',
                params: {cust_id: cust_id,partnerId:Application.RetalineOmni.Cache.partnerId},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('MerchantDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                        if (tmp.store_group_id > 0) {
                            Application.OutboundCalls.Cache.mctType = 'Merchant';
                            Application.OutboundCalls.Cache.mctMobile = tmp.br_Phone;
                            Application.OutboundCalls.Cache.mctName = tmp.store_group_name;
                            Application.OutboundCalls.Cache.mctEmail = tmp.br_Email;
                            Application.OutboundCalls.Cache.userId = cust_id;
                            Ext.getCmp('merchantSptTct').show();
                            //callLogTabchangeMerchant();
                            communicationTabchangeMerchant();
                            Ext.getCmp('inboundAddcommunicationtabMerchant').show();

                            Application.RetalineOmni.ViewMerchantOrderDetails(cust_id,1);
                        } else {
                            Ext.getCmp('merchantSptTct').hide();
                            Ext.getCmp('inboundAddcommunicationtabMerchant').hide();
                        }

                    } else {
                        var visualsDescPanel = Ext.getCmp('MerchantDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('MerchantViePanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('MerchantViePanel').doLayout();
        },communicationDataSaveApiMerchant: function () {
            var _customerCommunication_save = Ext.getCmp('formpanelCrmCustomersCommunication');
            var cust_id = Application.RetalineOmni.Cache.merchantId;
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            _customerCommunication_save.getForm().submit({
                waitTitle: 'Please Wait',
                waitMsg: 'Saving',
                url: modURL + '&op=insertCommunicationDetails',
                params: {
                    crmc_id: cust_id,
                    mobile:Ext.getCmp('merchantSearchParameter').getValue(),
                    jobId:Application.OutboundCalls.Cache.jobId,
                    type:2,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                },
                success: function (form, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success == true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
                            communicationTabchangeMerchant();
                            Ext.getCmp('CrmCustomersCommunicationWindow').close();
                        });
                    }
                },
                failure: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp);
                }
            });
        },outboundCallsMerchant: function (merchantContact) {
            var panelId = 'outmerchantsupportmainpanel';            
        var reswindow = new Ext.Window({
            title: Application.OutboundCalls.Cache.jobTitle,
            id:'outmerchantsupportmainWindow',
            modal: true,
            constrain: true,
            layout: 'fit',
            maximized: true,
            floating: true,
            shadow: false,
            closable:false,
            //closeAction: 'close',
            items: [merchantSupportMainTabPanel(panelId,merchantContact)],
            buttons: [
            ]
        });
        reswindow.doLayout();
        reswindow.show();
        reswindow.center();


        },callLogApiMerchant: function (type) {
            var _customerCommunication_save = Ext.getCmp('formpanelCallCommunication');
            var merchantId = Application.RetalineOmni.Cache.merchantId;
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            _customerCommunication_save.getForm().submit({
                waitTitle: 'Please Wait',
                waitMsg: 'Saving',
                url: modURL + '&op=insertCallLogDetails',
                params: {
                    userId: merchantId,
                    partnerId: Application.RetalineOmni.Cache.partnerId,
                    type: type,
                    userType:Application.OutboundCalls.Cache.mctType,
                    jobId:Application.OutboundCalls.Cache.jobId,
                    followupDate: Ext.getCmp('followupDate').getValue(),
                    followupTime: Ext.getCmp('followupTime').getValue(),
                    alternateNo: Ext.getCmp('alternateNo').getValue()
                },
                success: function (form, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success == true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                            //callLogTabchangeMerchant();
                            Ext.getCmp('CallCommunicationWindow').close();
                            Ext.getCmp('outmerchantsupportmainWindow').close();
                            Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata").getStore().load({
                                callback: function () {
                                    setTimeout(function () {
                                        Ext.Msg.show({
                                                        title: 'Confirm',
                                                        msg: "Do you want to get assigned with another job?",
                                                        buttons: Ext.MessageBox.YESNO,
                                                        fn: function (btn) {
                                                            if (btn == 'yes') {
                                                                Application.OutboundCalls.ChooseJob();
                                                            }
                                                        }
                                                    });
                                                    /*Ext.MessageBox.confirm(
                                                        "Confirm",
                                                        "Do you want to get assigned with another job?",
                                                        function (btn, text) {
                                                          if (btn == "yes") {
                                                            Application.OutboundCalls.ChooseJob();
                                                          }
                                                        }
                                                      );*/
                                    }, 500);
                                }
                            });
                       
                    }
                },
                failure: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp);
                }
            });
        },outboundCallsCustomer:function(contact){
            var panelId = 'outcustomersupportmainpanel';
            
            var reswindow = new Ext.Window({
                title: Application.OutboundCalls.Cache.jobTitle,
                id:'outmerchantsupportmainWindow',
                modal: true,
                constrain: true,
                layout: 'fit',
                maximized: true,
                floating: true,
                shadow: false,
                closable:false,
                items: [omniMainTabPanel(panelId,contact)],
                buttons: []
            });
            reswindow.doLayout();
            reswindow.show();
            reswindow.center();
        },MerchantViewModeonTab: function () {
            var cust_id = arguments[1];
            var tabType = arguments[0];
            var pkid = arguments[2];
            Ext.getCmp('DetailsViePanel').setTitle('View Details');
            Ext.getCmp('DetailsViePanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=merchantTabDetailsView',
                method: 'POST',
                params: {cust_id: cust_id,tabType:tabType,pkid:pkid},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('MerchantTabDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                        

                    } else {
                        var visualsDescPanel = Ext.getCmp('MerchantTabDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('DetailsViePanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('DetailsViePanel').doLayout();
        },merchantImersonateTab: function (path) {
            var panelId = 'merchant_impersonate_main_panel';
            var merchant_impersonate_main_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(merchant_impersonate_main_panel)) {
                merchant_impersonate_main_panel = merchantImpersonatePanel(path,'merchantImpersona');
            }
            Application.UI.addTab(merchant_impersonate_main_panel);
            merchant_impersonate_main_panel.doLayout();
        },customerImersonateTab: function (path) {
            var panelId = 'customer_impersonate_main_panel';
            var customer_impersonate_main_panel = Ext.getCmp(panelId);
            if (Ext.isEmpty(customer_impersonate_main_panel)) {
                customer_impersonate_main_panel = merchantImpersonatePanel(path,'custImpersona');
            }
            Application.UI.addTab(customer_impersonate_main_panel);
            customer_impersonate_main_panel.doLayout();
        },loadUrl : function(path){
            var extension = path.split(".").pop();
            var embedhtml ;
            switch(extension){
                case 'pdf':
                    path = "https://mozilla.github.io/pdf.js/web/viewer.html?file="+path;
                    embedhtml = '<iframe src="'+path+'" width="100%" height="100%" style="border: none;"></iframe>';
                    break;
                case 'docx':
                    path = "https://docs.google.com/viewer?url="+path+"&embedded=true";
                    embedhtml = '<iframe src="'+path+'" width="100%" height="100%" style="border: none;"></iframe>';
                    break;
                default:                   
                    path = path;
                    embedhtml = '<embed src="'+path+'" width="100%" height="100%" style="border: none;">';
                    break;
            }
            var win_id = "view_documents";
            var view_documents_window = Ext.getCmp(win_id);
            if (Ext.isEmpty(view_documents_window)) {
                view_documents_window = new Ext.Window({
                    id: win_id,
                    title: 'View Details',
                    layout: 'fit',
                    width: winsize.width * 0.7,
                    height: 500,
                    iconCls: 'icon-add-table',
                    plain: false,
                    constrain: true,
                    modal: true,
                    frame: true,
                    resizable: true,
                    items: [{
                            region: 'center',
                            border: false,
                            html: embedhtml
                        }]
                });

            }

            view_documents_window.doLayout();
            view_documents_window.show();
            view_documents_window.center();
        },CustomerViewModeonTab: function () {
            var cust_id = arguments[1];
            var tabType = arguments[0];
            var pkid = arguments[2];
            Ext.getCmp('DetailsViePanelCust').setTitle('View Details');
            Ext.getCmp('DetailsViePanelCust').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=customerTabDetailsView',
                method: 'POST',
                params: {cust_id: cust_id,tabType:tabType,pkid:pkid},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('CustomerTabDetailsViewPanel');
                        visualsDescPanel.update(tmp);                      

                    } else {
                        var visualsDescPanel = Ext.getCmp('CustomerTabDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('DetailsViePanelCust').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('DetailsViePanelCust').doLayout();
        },callLogApiCustomer: function (type) {
            var _customerCommunication_save = Ext.getCmp('formpanelCallCommunication');
            var customerId = Application.RetalineOmni.Cache.customerId;
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            _customerCommunication_save.getForm().submit({
                waitTitle: 'Please Wait',
                waitMsg: 'Saving',
                url: modURL + '&op=insertCallLogForCustomer',
                params: {
                    userId: customerId,
                    type: type,
                    userType:Application.OutboundCalls.Cache.mctType,
                    jobId:Application.OutboundCalls.Cache.jobId,
                    followupDate: Ext.getCmp('followupDate').getValue(),
                    followupTime: Ext.getCmp('followupTime').getValue()
                },
                success: function (form, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success == true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
                            //callLogTabchangeMerchant();
                            Ext.getCmp('CallCommunicationWindow').close();
                            Ext.getCmp('outmerchantsupportmainWindow').close();
                            Ext.getCmp("gridpanelMasterDataviewOutboundCallsdata").getStore().load({
                                callback: function () {
                                    Ext.MessageBox.confirm(
                                        "Confirm",
                                        "Do you want to get assigned with another job?",
                                        function (btn, text) {
                                          if (btn == "yes") {
                                            Application.OutboundCalls.ChooseJob()
                                          }
                                        }
                                      );
                                }
                            });
                        });
                    }
                },
                failure: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp);
                }
            });
        },initiateCallsMerchant: function (merchantContact) {
            var panelId = 'initiatemerchantsupportmainpanel';            
        var reswindow = new Ext.Window({
            title: 'Initiate Support',
            id:'initiatemerchantsupportmainWindow',
            modal: true,
            constrain: true,
            layout: 'fit',
            maximized: true,
            floating: true,
            shadow: false,
            closable:true,
            closeAction: 'close',
            items: [merchantSupportMainTabPanel(panelId,merchantContact)],
            buttons: [
            ]
        });
        reswindow.doLayout();
        reswindow.show();
        reswindow.center();


        },visitPartnerCustomerSite: function (type,phone,sgId) {
            var path = '';
            var guest_merchant_window = new Ext.Window({
                id: 'visit_merchant_sites',
                title: 'View Sites',
                modal: true,
                constrain: true,
                layout: 'fit',
                maximized: true,
                floating: true,
                shadow: false,
                closeAction: 'close',
                items: new Ext.Panel({
                    layout: 'fit',
                    height: 580,
                    items: [{
                            html: '<iframe id="iframe_visitSite" name="iframe_visitSite" style="overflow:auto;width:100%;height:100%" frameborder="0"  src="' + path + '"; ></iframe>'
                        }]
                }),
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        text: 'Cancel',
                        tabIndex: 32,
                        handler: function () {
                            guest_merchant_window.close();
                        }
                    }],
                    listeners:{
                        afterrender:function(){
                            Ext.Ajax.request({
                                waitMsg: 'Processing',
                                method: 'POST',
                                url: modURL + '&op=getSiteUrls',
                                params: {
                                    customerPhone: phone,
                                    sgId:sgId
                                },
                                success: function (response) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success === true) {
                                        if(type == 'Partner'){
                                        Ext.get('iframe_visitSite').dom.src = tmp.partnerSite; 
                                    } else {
                                        Ext.get('iframe_visitSite').dom.src = tmp.publicSite;  
                                    }
                                }else{
                                    Ext.MessageBox.alert('Error', tmp.msg);
                                }
                                },
                                failure: function (response) {
                                    var tmp = Ext.util.JSON.decode(response.responseText);
                                    Ext.MessageBox.alert('Error', tmp.msg);
                                }
                            });
                        }
                    }
            });
            guest_merchant_window = Ext.getCmp('visit_merchant_sites');
            guest_merchant_window.show();
            guest_merchant_window.doLayout();
            guest_merchant_window.center();
        }
    }
}();