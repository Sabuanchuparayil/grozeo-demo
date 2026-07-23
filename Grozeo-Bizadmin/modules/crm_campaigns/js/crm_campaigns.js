Application.CRM_Campaigns = function () {
    var winLoadMask;
    var RECS_PER_PAGE = 12;
    var modURL = '?module=crm_campaigns';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionCampaignChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('campaignsGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('campaignsGridPanel').getSelectionModel().getSelections()[0].data.referers_id;
            Application.CRM_Campaigns.ViewCampaignsMode(ID);
        }
    };
    var campaign_name = new Array();
    var campaignsPanel = function (id) {
        var _campPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Referers',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                campaignsGrid(),
                new Ext.Panel({
                    title: 'Campaigns',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'campaignsPanel',
                    height: winsize.height * 0.6,
                    items: [
                        campaignsDetailsView()
                    ]
                })
            ]
        });
        return _campPanel;
    };
    var campaignsGridstore = function () {
        var _advertisementList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCampaigns',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'referers_id',
                root: 'data'
            }, ['referers_id', 'referers_name', 'reference_mobile', 'reference_email', 'reference_count', 'sent_status']),
            sortInfo: {
                field: 'referers_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            //    remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('campaignsGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _advertisementList;
    };
    var campaignsDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplatecampaignsViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Referer Name </th><td>  {reference_name} </td></tr>',
                    '<tr><th width="40%">Reference Count </th><td>  {reference_count} </td></tr>',
                    '<tr><th width="40%">Mobile </th><td>  {reference_mobile} </td></tr>',
                    '<tr><th width="40%">Email </th><td>  {reference_email} </td></tr>',
//                    '<tr><th width="40%">Refered By </th><td>  {reference} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var templateStores = function () {
        var _templateStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getTemplates',
            method: 'post',
            fields: ['template_id', 'template_name'],
            remoteSort: true
        });
        return _templateStore;
    };
    var referenceStores = function () {
        var _referenceStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getReference',
            method: 'post',
            fields: ['referers_id', 'referers_contact_id'],
            remoteSort: true
        });
        return _referenceStore;
    };
    var districtStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: false,
            url: modURL + '&op=getDistrict',
            method: 'post',
            fields: ['dst_ID', 'dst_Name'],
            totalProperty: 'totalCount',
            root: 'data'
        });

        return store;
    };
    var refererform = function (template_id) {
        var dst_store = districtStore();
        var templateStore = templateStores();
        var referenceStore = referenceStores();
        var refererform = new Ext.Window({
            width: 700,
            autoheight: true,
            id: 'add_refererWindow',
            shadow: false,
            resizable: false,
            plain: true,
            title: 'Create Referer',
            constrain: true,
            draggable: true,
            modal: true,
            closable: false,
            items: [
                new Ext.form.FormPanel({
                    id: 'formpanelAddReferer',
                    bodyStyle: {"background-color": "white", "padding": "5px 5px 59px 10px"},
                    labelAlign: 'top',
                    items: [{
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 0.5,
                                    items: [{
                                            xtype: 'textfield',
                                            id: 'referers_id',
                                            hidden: true
                                        },
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Name',
                                            maxLength: 250,
                                            tabIndex: 100,
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'referer_name',
                                            name: 'referer_name',
                                            hideBorders: true,
                                            allowBlank: false,
                                            border: false,
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 0.5,
                                    items: [{
                                            layout: 'column',
                                            fieldLabel: 'Mobile',
                                            tabIndex: 100,
                                            xtype: 'numberfield',
                                            anchor: '99%',
                                            id: 'referer_mob',
                                            name: 'referer_mob',
                                            allowBlank: false,
                                            minLength: 10,
                                            maxLength: 10,
                                            msgTarget: 'under',
                                            maxLengthText: "Maximum length for the field is 12",
                                            minLengthText: "Minimum length for the field is 10",
                                            hideBorders: true,
                                            border: false,
                                            listeners: {
                                                change: function () {
                                                    var referer_mob = Ext.getCmp('referer_mob').getValue();
                                                    if (referer_mob) {
                                                        Ext.Ajax.request({
                                                            url: modURL + '&op=mobCheck',
                                                            method: 'POST',
                                                            params: {
                                                                referer_mob: referer_mob
                                                            },
                                                            success: function (response) {
                                                                var tmp = Ext.decode(response.responseText);
                                                                if (tmp.success === true) {
                                                                    var tmp = Ext.decode(response.responseText);
                                                                }
                                                                else {
                                                                    Ext.MessageBox.alert('Notification', 'Mobile not available');
                                                                    Ext.getCmp('referer_mob').reset();
                                                                    Ext.getCmp('referer_mob').focus();
                                                                }
                                                            },
                                                            failure: function (response, options) {
                                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                                                Ext.getCmp('referer_mob').focus();
                                                            }
                                                        });
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                }

                            ]
                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 0.5,
                                    items: [
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Email',
                                            maxLength: 250,
                                            tabIndex: 100,
                                            xtype: 'textfield',
                                            vtype: 'email',
                                            anchor: '99%',
                                            id: 'referer_email',
                                            name: 'referer_email',
                                            hideBorders: true,
                                            border: false,
                                        }
                                    ]
                                },
                                {
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 0.5,
                                    items: [{
                                            layout: 'column',
                                            fieldLabel: 'Address',
                                            maxLength: 250,
                                            tabIndex: 100,
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'referer_location',
                                            name: 'referer_location',
                                            hideBorders: true,
                                            border: false,
                                        }
                                    ]
                                }


                            ]
                        },
                        {
                            layout: 'column',
                            border: false,
                            items: [{
                                    layout: 'form',
                                    border: false,
                                    columnWidth: 1.0,
                                    items: [
                                        {
                                            layout: 'column',
                                            fieldLabel: 'Notes',
                                            maxLength: 250,
                                            tabIndex: 100,
                                            xtype: 'textarea',
                                            anchor: '99%',
                                            id: 'notes_id',
                                            name: 'notes_id',
                                            hideBorders: true,
                                            border: false
                                        }
                                    ]
                                }
                            ]
                        }
                    ]

                })
            ],
            fbar: [{
                    text: 'Cancel',
                    anchor: '90%',
                    columnWidth: 0.1,
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    tabIndex: 33,
                    handler: function () {
                        // Ext.getCmp('gridMarketingViewcontacts').getStore().load();
                        refererform.close();
                    }
                },
                {
                    text: "Save",
                    anchor: '90%',
                    columnWidth: 0.1,
                    xtype: 'button',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    tabIndex: 33,
                    handler: function () {
                        // Ext.getCmp('gridMarketingViewcontacts').getStore().load();
                        Application.CRM_Campaigns.addReferer();
                    }
                }



            ]

        });
        refererform.doLayout();
        refererform.show();
        refererform.center();
        return refererform;
    }
    var campaignsGrid = function () {
        var _campaignsGridstore = campaignsGridstore();
        var _campaignsFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'referers_name'
                },
                {
                    type: 'string',
                    dataIndex: 'reference_count'
                },
                {
                    type: 'string',
                    dataIndex: 'sent_status'
                }
            ]
        });
        _campaignsFilter.remote = true;
        _campaignsFilter.autoReload = true;
        var _campPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _campaignsGridstore,
            //iconCls: 'money',
            id: 'campaignsGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _campaignsFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Referer Name',
                    dataIndex: 'referers_name',
                    sortable: true,
                    tooltip: 'Referer Name',
                    hideable: true
                },
                {
                    header: 'Reference Count',
                    dataIndex: 'reference_count',
                    sortable: true,
                    tooltip: 'Reference Count',
                    hideable: true
                },
                {
                    header: 'Campaign Status',
                    dataIndex: 'sent_status',
                    sortable: true,
                    tooltip: 'Campaign Status',
                    hideable: true
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            referersActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                    header: 'Actions',
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    items: [
                        {
                            text: ' ',
                            tooltip: 'View',
                            iconCls: 'send_sms',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var ID = record.data.referers_id;
                                Application.CRM_Campaigns.winMapReferers(ID);
                            }
                        }

                    ]
                }*/
            ],
            tbar: [
                {
                    xtype: 'button',
                    text: 'Create Referer',
                    tooltip: 'Create Referer',
                    iconCls: 'finascop_add',
                    handler: function () {
                        refererform();
                    }
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _campaignsGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionCampaignChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('referers_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.CRM_Campaigns.Cache.referers_id = ID;
                        //Ext.getCmp('formpanelcampaigns').hide();
                        Application.CRM_Campaigns.ViewCampaignsMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _campaignsGridstore.load();
                }
            }
        });
        return _campPanel;
    };
    var referersActionMenu = new Ext.menu.Menu({
        items: [{
                text: "View",
                handler: function () {
                            var ID = Ext.getCmp('campaignsGridPanel').getSelectionModel().getSelections()[0].data.referers_id;
                            Application.CRM_Campaigns.winMapReferers(ID);
                }
            }]
    });
    var chargeStore = function (refrence_referers_id) {
        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=MapReference',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'id',
                root: 'data'
            }, ['refrence_referers_id', 'contactPerson', 'checkeds', 'itemcount', 'contactNumber', 'reference_cl_id', 'reference_id']),
            sortInfo: {
                field: 'reference_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            autoLoad: false,
            root: 'data',
            remoteSort: true,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.refrence_referers_id = refrence_referers_id;
                }
            }
        });
        return store;
    };
    var creatMapRefererGrid = function (id) {
        var refId = arguments[0];
        var grid_title = 'View Reference';
        var chk_model = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            checkOnly: true,
            listeners: {
                rowdeselect: function (sm, rowIndex, record) {
                    console.log(record);
                    var ind = campaign_name.indexOf(record.get('reference_cl_id'));
                    if (ind > -1)
                        campaign_name.splice(ind, 1);
                    record.set('checked', 'false');
                },
                rowselect: function (sm, rowIndex, record) {
                    console.log(record);
                    var ind = campaign_name.indexOf(record.get('reference_cl_id'));
                    if (ind == -1)
                        campaign_name.push(record.get('reference_cl_id'));
                    record.set('checked', 'true');
                }
            }
        });
        var charge_store = chargeStore(refId);
        var charge_grid = new Ext.grid.GridPanel({
            store: charge_store,
            layout: 'fit',
            title: grid_title,
            autoScroll: true,
            selModel: chk_model,
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            id: 'gidRefCharge',
            clicksToEdit: '2',
            columns: [chk_model, {
                    header: '',
                    hidden: true,
                    dataIndex: 'refrence_referers_id',
                    tooltip: 'Main Type'
                },
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'contactPerson',
                    tooltip: 'Name'
                },
                {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'contactNumber',
                    tooltip: 'Name'
                }],
            sm: chk_model,
            viewConfig: {
                forceFit: true
            },
            view: new Ext.grid.GroupingView({
                forceFit: true,
                showGroupName: false,
                enableNoGroups: false,
                enableGroupingMenu: false,
                hideGroupedColumn: false,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    switch (record.get('checkeds')) {
                        case '0':
                            return '';
                            break;
                        case '1':
                            return 'finascop_indicateColPINK';
                            break;
                    }

                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
            }),
                    listeners: {
                        afterrender: function () {
                            {
                                var me = this;
                                charge_store.on('load', function () {
                                    var data = me.getStore().data.items;
                                    var recs = [];
                                    Ext.each(data, function (item, index) {
                                        if (item.data.checked == 1) {
                                            recs.push(index);
                                        }
                                    });
                                    me.getSelectionModel().selectRows(recs);
                                });
                            }
                        }
                    },
            stripeRows: true,
            autoExpandColumn: 'contactPerson'

        });
        return charge_grid;
    };
    return {
        Cache: {},
        initCampaigns: function () {
            var _campaignsPanelId = 'panelCampaigns';
            var _campaignsPanel = Ext.getCmp(_campaignsPanelId);
            if (Ext.isEmpty(_campaignsPanel)) {
                _campaignsPanel = campaignsPanel(_campaignsPanelId);
                Application.UI.addTab(_campaignsPanel);
                _campaignsPanel.doLayout();
            } else {
                Application.UI.addTab(_campaignsPanel);
            }
        },
        ViewCampaignsMode: function () {
            var referers_id = arguments[0];
            Ext.getCmp('campaignsPanel').setTitle('View Referer Details');
            Ext.getCmp('xtemplatecampaignsViewDetails').show();
            Ext.getCmp('campaignsPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=campaignsDetailsView',
                method: 'POST',
                params: {referers_id: referers_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplatecampaignsViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('campaignsPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('campaignsPanel').doLayout();
        },
        winMapReferers: function () {
            Application.CRM_Campaigns.Cache.referers_id = arguments[0];
            var wbGrid = creatMapRefererGrid(Application.CRM_Campaigns.Cache.referers_id);
//            Application.VellnezLabPackages.EditPackageForm(Application.VellnezLabPackages.Cache.packageId);
            var resultWindow = new Ext.Window({
                id: "windowForReference",
                iconCls: 'vender-items',
                shadow: false,
                height: 600,
                width: 900,
                layout: 'fit',
                resizable: false,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                items: [wbGrid],
                buttons: [
                    {
                        //icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        handler: function () {
                            Ext.getCmp('windowForReference').close();
                        }

                    }, {
                        //icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Remove',
                        icon: './resources/images/default/icons/drop.png',
                        handler: function () {
                            var store_fields = Ext.getCmp('gidRefCharge').getSelectionModel().getSelections();
                            console.log('store_fields--', store_fields);
                            var mob_num = Array();
                            if (store_fields.length > 0) {
                                campaign_name = [];
                                mob_num = [];
                                for (var i = 0; i < store_fields.length; i++) {
                                    campaign_name[i] = store_fields[i].data.reference_cl_id;
                                    mob_num[i] = store_fields[i].data.contactNumber;
                                }
                                Ext.Ajax.request({
                                    url: modURL + '&op=removeCampaign',
                                    method: 'POST',
                                    params: {
                                        refIds: Ext.encode(campaign_name),
                                        mob_num: Ext.encode(mob_num),
                                        referers_id: Application.CRM_Campaigns.Cache.referers_id
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == false) {
                                            Ext.MessageBox.alert('Notification', tmp.msg);
                                        }
                                        else {
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('campaignsGridPanel').getStore().load({
                                                baseParams: {"referers_id": Application.CRM_Campaigns.Cache.referers_id}
                                            });
                                            Ext.getCmp('windowForReference').close();
                                        }
                                    },
                                    failure: function (response, options) {
                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                    }
                                });
                            }
                            else {
                                Ext.MessageBox.alert('Notification', 'Please select a value');
                            }
                        }

                    }
                ]
            });
            resultWindow.doLayout();
            resultWindow.show();
            resultWindow.center();
            Ext.getCmp('gidRefCharge').getStore().load({
                baseParams: {"refrence_referers_id": Application.CRM_Campaigns.Cache.referers_id}
            });
        },
        addReferer: function () {
            if (Ext.getCmp('formpanelAddReferer').getForm().isValid()) {
                Ext.Ajax.request({
                    url: modURL + '&op=insertReferer',
                    method: 'POST',
                    params: {
                        referer_name: Ext.getCmp('referer_name').getValue(),
                        referer_email: Ext.getCmp('referer_email').getValue(),
                        referer_mobile: Ext.getCmp('referer_mob').getValue(),
                        referer_location: Ext.getCmp('referer_location').getValue(),
                        notes_id: Ext.getCmp('notes_id').getValue()
                    },
                    success: function (response) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success === true) {
                            Application.example.msg('Success', tmp.msg);
                            Ext.getCmp('add_refererWindow').close();
                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('campaignsGridPanel'))
                            Ext.getCmp('campaignsGridPanel').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.msg);
                        } else {
                            Ext.Msg.alert("Error", tmp.msg);
                        }
                    },
                    failure: function (response) {
                        var tmp = Ext.util.JSON.decode(response.responseText);
                        Ext.MessageBox.alert('Error', tmp.msg);
                    }

                })

            }

        }
    }
}();
