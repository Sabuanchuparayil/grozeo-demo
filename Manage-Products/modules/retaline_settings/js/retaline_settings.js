Application.RetalineSettings = function () {

    var RECS_PER_PAGE = 12;
    var modURL = '?module=retaline_settings';
    var winsize = Ext.getBody().getViewSize();
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var gridSelectionChangedcourier = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewCourierdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewCourierdata').getSelectionModel().getSelections()[0].data.mst_courier_id;
            Application.RetalineSettings.ViewCourier(ID);
        }
    };
    var gridSelectionChangedfaq = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('faqMasterGrid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('faqMasterGrid').getSelectionModel().getSelections()[0].data.faq_id;
            Application.RetalineSettings.faqViewMode(ID);
        }
    };
    var gridSelectionChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('pageMasterGrid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('pageMasterGrid').getSelectionModel().getSelections()[0].data.page_id;
            Application.RetalineSettings.ViewMode(ID);
        }
    };
    var gridSelectionFeedbackChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('feedBackGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('feedBackGridPanel').getSelectionModel().getSelections()[0].data.fb_id;
            Application.RetalineSettings.ViewFeedbackMode(ID);
        }
    };
    var gridSelectionappNotificationChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('appNotificationGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('appNotificationGridPanel').getSelectionModel().getSelections()[0].data.notification_id;
            Application.RetalineSettings.ViewNotificationMode(ID);
        }
    };
    var gridSelectionpaymentTermsChanged = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('paymentTermsGridPanel').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('paymentTermsGridPanel').getSelectionModel().getSelections()[0].data.ptc_id;
            Application.RetalineSettings.ViewpaymentTermsMode(ID);
        }
    };
    var gridSelectionChangedstoregroups = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getSelectionModel().getSelections()[0].data.store_group_id;
            Application.RetalineSettings.ViewStoreGroups(ID);
        }
    };
    var faqStore = function () {
        var _faqsStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listFaq',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'faq_id',
                root: 'data'
            }, ['faq_id', 'faq_title', 'faq_description', 'faq_status']),
            sortInfo: {
                field: 'faq_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('faqMasterGrid').getView().refresh();
                    Ext.getCmp('faqMasterGrid').getSelectionModel().selectRow(0);
                }
            }
        });
        return _faqsStore;
    };
    var FaqGrid = function () {

        var _FaqGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'faq_title'
                },
                {
                    type: 'string',
                    dataIndex: 'faq_description'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'faq_status'
                }

            ]
        });
        _FaqGridFilter.remote = true;
        _FaqGridFilter.autoReload = true;
        var _faqStore = faqStore();
        var _faqgridPanel = new Ext.grid.GridPanel({
            store: _faqStore,
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            //iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _FaqGridFilter],
            id: 'faqMasterGrid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Question',
                    sortable: true,
                    dataIndex: 'faq_title',
                    tooltip: 'Question',
                    hideable: true
                },
                {
                    header: 'Answer',
                    sortable: true,
                    dataIndex: 'faq_description',
                    tooltip: 'Answer'
                },
                {
                    header: 'Status',
                    dataIndex: 'faq_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedfaq
                }
            }),
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                    _faqStore.load();
                }
            },
            tbar: [{
                    text: 'Create FAQ',
                    tooltip: 'Create FAQ',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.RetalineSettings.catAddEdit = 'Add';
                        var masterForm = Ext.getCmp('faqMasterForm').getForm();
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('faq_title').focus(false, 100);
                        /*<?php if (user_access("retaline_settings", "saveFaq")) { ?> */
                        Ext.getCmp('FaqEditBtn').hide();
                        Ext.getCmp('FaqSaveBtn').show();
                        /*<?php } ?> */
                        Ext.getCmp('FaqCancelBtn').show();
                        Ext.getCmp('faqMasterForm').show();
                        Ext.getCmp('FaqDetailsViewPanel').hide();

                        Ext.getCmp('faq_status').setValue(1);
                        Ext.getCmp('FaqPanel').doLayout();
                        Ext.getCmp('FaqPanel').setTitle("Create FAQ Details");
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _faqStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
        });
        return _faqgridPanel;
    };
    var FaqForms = function () {
        var _faqFormPanel = new Ext.form.FormPanel({
            frame: false,
            border: false,
            hidden: true,
            labelAlign: 'top',
            autoHeight: true,
            labelWidth: 100,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            id: 'faqMasterForm',
            items: [{
                    xtype: 'spacer',
                    height: 10
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Question',
                    id: 'faq_title',
                    name: 'n[faq_title]',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 1,
                    maxLength: 300
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'Answer',
                    id: 'faq_description',
                    name: 'n[faq_description]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 2,
                    maxValue: 100
                }, {
                    xtype: 'hidden',
                    id: 'faq_id',
                    name: 'n[faq_id]'
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "emptyText": "Set status..",
                    "editable": false,
                    "typeAhead": false,
                    tabIndex: 3,
                    "id": "faq_status"
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('faq_id').getValue())) {
                        var recordSelected = Ext.getCmp('faq_status').getStore().getAt(0);
                        Ext.getCmp('faq_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _faqFormPanel;
    };
    var FaqDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'FaqDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Question </th><td> {faq_title} </td></tr>',
                    '<tr><th width="40%">Answer </th><td> {faq_description} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="faq_status == \'1\'">Active</tpl>',
                    '<tpl if="faq_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var saveFaq = function () {
        var catId = Ext.getCmp('faq_id').getValue();
        if (!Ext.isEmpty(Ext.getCmp('faq_title').getValue() &&
                Ext.getCmp('faq_description').getValue() && Ext.getCmp('faq_status').getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=saveFaq',
                method: 'POST',
                params: {
                    faq_id: Ext.getCmp('faq_id').getValue(),
                    faq_title: Ext.getCmp('faq_title').getValue(),
                    faq_description: Ext.getCmp('faq_description').getValue(),
                    faq_status: Ext.getCmp('faq_status').getValue()

                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        if (Application.RetalineSettings.catAddEdit == 'Add') {
                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('faqMasterGrid'));
                            Ext.getCmp('faqMasterGrid').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });
                        } else {
                            Ext.getCmp('faqMasterGrid').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            var gridPanel = Ext.getCmp('faqMasterGrid');
                                            var index = gridPanel.store.find('faq_id', catId);
                                            gridPanel.getSelectionModel().selectRow(index);
                                        }
                                    }
                            );
                        }
                        Application.RetalineSettings.catAddEdit = '';
                        Application.RetalineSettings.faqViewMode(tmp.data.faq_id);
                    } else if (tmp.success === true && tmp.valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else if (tmp.success === true && tmp.img_valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else {
                        Ext.Msg.alert("Error", tmp.message);
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert("Notification", 'Please fill all Mandatory fields');
        }

    };
    var masterPanelforFaq = function (id) {
        var _mpanelforfaq = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'FAQ',
            id: id,
            //iconCls: 'my-icon444',
            items: [FaqGrid(), new Ext.Panel({
                    title: 'FAQ Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    id: 'FaqPanel',
                    height: winsize.height * 0.6,
                    items: [FaqForms(), FaqDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 4,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'FaqCancelBtn',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('faqMasterGrid').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('faqMasterGrid').getSelectionModel().getSelections()[0].data.faq_id;
                                    Application.RetalineSettings.faqViewMode(ID);
                                }
                            }
                        }, /*<?php if (user_access("retaline_settings", "saveFaq")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'FaqEditBtn',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 5,
                            hidden: false,
                            handler: function () {
                                var ID = Ext.getCmp('faqMasterGrid').getSelectionModel().getSelections()[0].data.faq_id;
                                Application.RetalineSettings.faqEditView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 6,
                            cls: 'left-right-buttons',
                            id: 'FaqSaveBtn',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveFaq(id);
                            }
                        } /*<?php } ?> */]
                })
            ],
        });
        return _mpanelforfaq;
    };
    var pageForms = function () {
        var panel = new Ext.form.FormPanel({
            frame: false,
            border: false,
            hidden: true,
            labelAlign: 'top',
            autoHeight: true,
            labelWidth: 100,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            id: 'pageMasterForm',
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Title',
                    id: 'page_name',
                    name: 'n[page_name]',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 100,
                    maxLength: 300
                }, {
                    xtype: 'templateeditormce',
                    fieldLabel: 'Content',
                    id: 'page_content',
                    name: 'n[page_content]',
                    anchor: '98%',
                    height: 270,
                    allowBlank: false,
                    tabIndex: 101,
                    maxLength: 900
                }, {
                    xtype: 'hidden',
                    id: 'page_id',
                    name: 'n[page_id]'
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "emptyText": "Set status..",
                    "editable": false,
                    "typeAhead": false,
                    tabIndex: 102,
                    "id": "page_status"
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('page_id').getValue())) {
                        var recordSelected = Ext.getCmp('page_status').getStore().getAt(0);
                        Ext.getCmp('page_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return panel;
    };
    var pageDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'pageDetailsViewPanel',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Title </th><td> {page_name} </td></tr>',
                    '<tr><th width="40%">Content </th><td> {page_content} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="page_status == \'1\'">Active</tpl>',
                    '<tpl if="page_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var masterPanelforPage = function (id) {
        var panel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Page',
            id: id,
            //iconCls: 'my-icon444',
            items: [pageGrid(), new Ext.Panel({
                    title: 'Page Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    id: 'pageparentPanel',
                    height: winsize.height * 0.6,
                    items: [pageForms(), pageDetailsView()],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 10,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'pageCancelBtn',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('pageMasterGrid').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('pageMasterGrid').getSelectionModel().getSelections()[0].data.page_id;
                                    Application.RetalineSettings.ViewMode(ID);
                                }
                            }
                        }, /*<?php if (user_access("retaline_settings", "savePage")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'pageEditBtn',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 11,
                            handler: function () {
                                var ID = Ext.getCmp('pageMasterGrid').getSelectionModel().getSelections()[0].data.page_id;
                                Application.RetalineSettings.EditView(ID);
                            }
                        }, {
                            text: "Save",
                            tabIndex: 12,
                            cls: 'left-right-buttons',
                            id: 'pageSaveBtn',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                savePage();
                            }
                        } /*<?php } ?> */]
                })
            ]
        });
        return panel;
    };
    var pageGrid = function () {
        var _pageGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'page_name'
                }, {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'page_status'
                }
            ]
        });
        _pageGridFilter.remote = true;
        _pageGridFilter.autoReload = true;
        var _masterStore = masterStore();
        var _faqgridPanel = new Ext.grid.GridPanel({
            store: _masterStore,
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            //iconCls: 'my-icon444',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _pageGridFilter],
            id: 'pageMasterGrid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Title',
                    sortable: true,
                    dataIndex: 'page_name',
                    tooltip: 'Title',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'page_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('page_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.RetalineSettings.Cache.page_id = ID;
                        Ext.getCmp('pageMasterForm').hide();
                        Application.RetalineSettings.ViewMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _masterStore.load();
                }
            },
            tbar: [{
                    text: 'Create Page',
                    tooltip: 'Create Page',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.RetalineSettings.subcatAddEdit = 'Add';
                        var masterForm = Ext.getCmp('pageMasterForm').getForm();
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('page_name').focus(false, 100);
                        /*<?php if (user_access("retaline_settings", "savePage")) { ?> */
                        Ext.getCmp('pageEditBtn').hide();
                        Ext.getCmp('pageSaveBtn').show();
                        /*<?php } ?> */
                        Ext.getCmp('pageCancelBtn').show();
                        Ext.getCmp('pageMasterForm').show();

                        Ext.getCmp('pageDetailsViewPanel').hide();

                        Ext.getCmp('page_content').setValue();
                        Ext.getCmp('page_status').setValue(1);
                        Ext.getCmp('pageparentPanel').doLayout();
                        Ext.getCmp('pageparentPanel').setTitle("Create Page Details");
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _masterStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            autoExpandColumn: 'pdt_name_col'

        });
        return _faqgridPanel;
    };
    var masterStore = function () {
        var _store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listPage',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'page_id',
                root: 'data'
            }, ['page_name', 'page_id', 'page_content', 'page_status']),
            sortInfo: {
                field: 'page_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('pageMasterGrid').getView().refresh();
                    Ext.getCmp('pageMasterGrid').getSelectionModel().selectRow(0);
                }
            }
        });

        return _store;
    };
    var savePage = function () {
        var subcatid = Ext.getCmp('page_id').getValue();
        if (!Ext.isEmpty(Ext.getCmp('page_name').getValue() &&
                Ext.getCmp('page_content').getValue() && Ext.getCmp('page_status').getValue())) {

            Ext.Ajax.request({
                url: modURL + '&op=savePage',
                method: 'POST',
                params: {
                    page_id: Ext.getCmp('page_id').getValue(),
                    page_name: Ext.getCmp('page_name').getValue(),
                    page_content: Ext.getCmp('page_content').getValue(),
                    page_status: Ext.getCmp('page_status').getValue()

                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        if (Application.RetalineSettings.subcatAddEdit == 'Add') {

                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('pageMasterGrid'));
                            Ext.getCmp('pageMasterGrid').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });

                        } else {

                            Ext.getCmp('pageMasterGrid').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            var gridPanel = Ext.getCmp('pageMasterGrid');
                                            var index = gridPanel.store.find('page_id', subcatid);
                                            gridPanel.getSelectionModel().selectRow(index);
                                        }
                                    }
                            );

                        }
                        Application.RetalineSettings.subcatAddEdit = '';
                        Application.RetalineSettings.ViewMode(tmp.data.page_id);
                    } else if (tmp.success === true && tmp.valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else if (tmp.success === true && tmp.img_valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else {
                        Ext.Msg.alert("Error", tmp.message);
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert("Notification", 'Please fill all mandatory fields');
        }

    };
    var feedbackPanel = function (id) {
        var _adPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Feedback',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                feedbackGrid(),
                new Ext.Panel({
                    title: 'Feedback Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'feedbackPanel',
                    height: winsize.height * 0.6,
                    items: [
                        feedbackDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 13,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonfeedbackCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('feedBackGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('feedBackGridPanel').getSelectionModel().getSelections()[0].data.fb_id;
                                    Application.RetalineSettings.ViewFeedbackMode(ID);
                                }
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 14,
                            cls: 'left-right-buttons',
                            id: 'buttonfeedbackSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveFeedback();
                            }
                        }
                    ]
                })
            ]
        });
        return _adPanel;
    };
    var feedbackGridstore = function () {
        var _feedbackList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listfeedback',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'fb_id',
                root: 'data'
            }, ['fb_id', 'fb_mobile', 'fb_email', 'fb_comments']),
            sortInfo: {
                field: 'fb_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('feedBackGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _feedbackList;
    };
    var feedbackDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplatefeedbackViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Mobile </th><td>  {fb_mobile} </td></tr>',
                    '<tr><th width="40%">Email </th><td>  {fb_email} </td></tr>',
                    '<tr><th width="40%">Comments </th><td>  {fb_comments} </td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var feedbackGrid = function () {
        var _feedbackGridstore = feedbackGridstore();
        var _feedbackFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'fb_mobile'
                },
                {
                    type: 'string',
                    dataIndex: 'fb_email'
                },
                {
                    type: 'string',
                    dataIndex: 'fb_comments'
                },
            ]
        });
        _feedbackFilter.remote = true;
        _feedbackFilter.autoReload = true;
        var _gridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _feedbackGridstore,
            //iconCls: 'money',
            id: 'feedBackGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _feedbackFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Mobile',
                    id: 'fb_mobile_auto_exp',
                    dataIndex: 'fb_mobile',
                    sortable: true,
                    tooltip: 'Mobile',
                    hideable: true
                },
                {
                    header: 'Email',
                    dataIndex: 'fb_email',
                    sortable: true,
                    tooltip: 'Email',
                    hideable: true
                },
                {
                    header: 'Comments',
                    dataIndex: 'fb_comments',
                    sortable: true,
                    tooltip: 'Comments'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _feedbackGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionFeedbackChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('fb_id');

                    if (!Ext.isEmpty(ID)) {
                        Application.RetalineSettings.Cache.fb_id = ID;
//                        Ext.getCmp('formpanelFeedback').hide();
                        Application.RetalineSettings.ViewFeedbackMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _feedbackGridstore.load();
                }
            },
        });
        return _gridPanel;
    };
    var notificationPanel = function (id) {
        var _notificationPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Notification',
            id: id,
            //iconCls: 'my-icon444',
            items: [
                notificationGrid(),
                new Ext.Panel({
                    title: 'Notification View',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'notificationPanel',
                    height: winsize.height * 0.6,
                    items: [
                        notificationForm(), notificationDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 15,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonnotificationCancel',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('appNotificationGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('appNotificationGridPanel').getSelectionModel().getSelections()[0].data.notification_id;
                                    Application.RetalineSettings.ViewNotificationMode(ID);
                                }
                            }
                        }, /* <?php if (user_access("retaline_settings", "saveNotification")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonnotificationtEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 16,
                            handler: function () {
                                var ID = Ext.getCmp('appNotificationGridPanel').getSelectionModel().getSelections()[0].data.notification_id;
                                Application.RetalineSettings.EditNotificationView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 17,
                            cls: 'left-right-buttons',
                            id: 'buttonnotificationSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveNotification();
                            }
                        } /*<?php  } ?>*/
                    ]
                })
            ]
        });
        return _notificationPanel;
    };
    var notificationGridstore = function () {
        var _notificationList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listnotification',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'notification_id',
                root: 'data'
            }, ['notification_id', 'notification_content', 'notification_status']),
            sortInfo: {
                field: 'notification_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('appNotificationGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _notificationList;
    };
    var notificationForm = function () {
        var _notificationForm = new Ext.FormPanel({
            id: 'formpanelNotification',
            frame: false,
            border: false,
            hidden: true,
            labelAlign: 'top',
            autoHeight: true,
            labelWidth: 100,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Notification Content',
                    id: 'notification_content',
                    name: 'n[notification_content]',
                    anchor: '98%',
                    allowBlank: false,
                    width: 300,
                    tabIndex: 18,
                    maxLength: 300
                },
                {
                    xtype: 'textfield',
                    id: 'notification_id',
                    name: 'n[notification_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[notification_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 19,
                    "emptyText": "Set status..",
                    "id": 'notification_status'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('notification_id').getValue())) {
                        var recordSelected = Ext.getCmp('notification_status').getStore().getAt(0);
                        Ext.getCmp('notification_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _notificationForm;
    };
    var saveNotification = function () {
        var forms_data = {
            form: Ext.getCmp('formpanelNotification').getForm().getValues()
        };
        if (Ext.getCmp('notification_id').getValue() > 0) {
            var adId = Ext.getCmp('notification_id').getValue();
        } else {
            var d = new Date();
            var adId = '-';
        }
        var params = {
            action: 'Save Notification Details',
            module: 'Settings',
            op: 'saveNotification',
            extrainfo: 'Settings Details save',
            id: adId
        };
        APICall(params, Application.RetalineSettings.saveNotification, forms_data);
    };
    var notificationDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplatenotificationViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Notification Content </th><td>  {notification_content} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="notification_status == \'1\'">Active</tpl>',
                    '<tpl if="notification_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var notificationGrid = function () {
        var _notificationGridstore = notificationGridstore();
        var _notificationFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'notification_content'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'notification_status'
                }
            ]
        });
        _notificationFilter.remote = true;
        _notificationFilter.autoReload = true;
        var _notificationgridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _notificationGridstore,
            //iconCls: 'money',
            id: 'appNotificationGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _notificationFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Notification Content',
                    id: 'notification_content_auto_exp',
                    dataIndex: 'notification_content',
                    sortable: true,
                    tooltip: 'Notification Content',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'notification_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _notificationGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionappNotificationChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('notification_id');

                    if (!Ext.isEmpty(ID)) {
                        Application.RetalineSettings.Cache.notification_id = ID;
                        Ext.getCmp('formpanelNotification').hide();
                        Application.RetalineSettings.ViewNotificationMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _notificationGridstore.load();
                }
            }
        });
        return _notificationgridPanel;
    };
    var paymentTermsPanel = function (id) {
        var _adPanel = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Payment Terms',
            id: id,
            items: [
                paymentTermsGrid(),
                new Ext.Panel({
                    title: 'Payment Terms Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'paymentTermsPanel',
                    height: winsize.height * 0.6,
                    items: [paymentTermsForm(),
                        paymentTermsDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 20,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'PTCancelBtn',
                            hidden: true,
                            handler: function () {
                                if (!Ext.isEmpty(Ext.getCmp('paymentTermsGridPanel').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('paymentTermsGridPanel').getSelectionModel().getSelections()[0].data.ptc_id;
                                    Application.RetalineSettings.ViewpaymentTermsMode(ID);
                                }
                            }
                        }, {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'PTEditBtn',
                            icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                            tabIndex: 21,
                            hidden: false,
                            handler: function () {
                                var ID = Ext.getCmp('paymentTermsGridPanel').getSelectionModel().getSelections()[0].data.ptc_id;
                                Application.RetalineSettings.ptEditView(ID);
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 22,
                            cls: 'left-right-buttons',
                            id: 'PTSaveBtn',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                savePaymentTerms();
                            }
                        }
                    ]
                })
            ]
        });
        return _adPanel;
    };
    var paymentTermsGridstore = function () {
        var _paymentTermsList = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listpaymentTerms',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'ptc_id',
                root: 'data'
            }, ['ptc_id', 'ptc_name', 'ptc_days', 'ptc_status']),
            sortInfo: {
                field: 'ptc_id',
                direction: "ASC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('paymentTermsGridPanel').getSelectionModel().selectRow(0);
                }
            }
        });
        return _paymentTermsList;
    };
    var paymentTermsDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'xtemplatepaymentTermsViewDetails',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>  {ptc_name} </td></tr>',
                    '<tr><th width="40%">Days </th><td>  {ptc_days} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="ptc_status == \'1\'">Active</tpl>',
                    '<tpl if="ptc_status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var paymentTermsGrid = function () {
        var _paymentTermsGridstore = paymentTermsGridstore();
        var _paymentTermsFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'ptc_name'
                },
                {
                    type: 'string',
                    dataIndex: 'ptc_days'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'ptc_status'
                }
            ]
        });

        _paymentTermsFilter.remote = true;
        _paymentTermsFilter.autoReload = true;
        var _gridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _paymentTermsGridstore,
            //iconCls: 'money',
            id: 'paymentTermsGridPanel',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _paymentTermsFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    id: 'ptc_name_auto_exp',
                    dataIndex: 'ptc_name',
                    sortable: true,
                    tooltip: 'Name',
                    hideable: true
                },
                {
                    header: 'Days',
                    dataIndex: 'ptc_days',
                    sortable: true,
                    tooltip: 'Days',
                    hideable: true
                },
                {
                    header: 'Status',
                    dataIndex: 'ptc_status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            tbar: [{
                    text: 'Create Payment Terms',
                    tooltip: 'Create Payment Terms',
                    icon: './resources/images/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.RetalineSettings.catAddEdit = 'Add';
                        var masterForm = Ext.getCmp('formpanelPaymentTerms').getForm();
                        loadedForm = null;
                        masterForm.reset();
                        Ext.getCmp('ptc_name').focus(false, 100);
                        /*<?php if (user_access("retaline_settings", "savePaymentTerms")) { ?> */
                        Ext.getCmp('PTEditBtn').hide();
                        Ext.getCmp('PTSaveBtn').show();
                        /*<?php } ?> */
                        Ext.getCmp('PTCancelBtn').show();
                        Ext.getCmp('formpanelPaymentTerms').show();
                        Ext.getCmp('xtemplatepaymentTermsViewDetails').hide();

                        Ext.getCmp('ptc_status').setValue(1);
                        Ext.getCmp('paymentTermsPanel').doLayout();
                        Ext.getCmp('paymentTermsPanel').setTitle("Create Payment Terms");
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _paymentTermsGridstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionpaymentTermsChanged
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('ptc_id');

                    if (!Ext.isEmpty(ID)) {
                        Application.RetalineSettings.Cache.ptc_id = ID;
//                        Ext.getCmp('formpanelpaymentTerms').hide();
                        Application.RetalineSettings.ViewpaymentTermsMode(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _paymentTermsGridstore.load();
                }
            },
        });
        return _gridPanel;
    };
    var paymentTermsForm = function () {
        var _paymentTermsForm = new Ext.FormPanel({
            id: 'formpanelPaymentTerms',
            frame: false,
            border: false,
            hidden: true,
            labelAlign: 'top',
            autoHeight: true,
            labelWidth: 100,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Title',
                    id: 'ptc_name',
                    name: 'n[ptc_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 23,
                    maxLength: 150
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Days',
                    id: 'ptc_days',
                    name: 'n[ptc_days]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 24,
                },
                {
                    xtype: 'textfield',
                    id: 'ptc_id',
                    name: 'n[ptc_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[ptc_status]",
                    "fieldLabel": "Status",
                    "tabIndex": 25,
                    "emptyText": "Set status..",
                    "id": 'ptc_status'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('ptc_id').getValue())) {
                        var recordSelected = Ext.getCmp('ptc_status').getStore().getAt(0);
                        Ext.getCmp('ptc_status').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _paymentTermsForm;
    };
    var savePaymentTerms = function () {

        var ptc_id = Ext.getCmp('ptc_id').getValue();
        if (!Ext.isEmpty(Ext.getCmp('ptc_name').getValue() &&
                Ext.getCmp('ptc_days').getValue() && Ext.getCmp('ptc_status').getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=savePaymentTerms',
                method: 'POST',
                params: {
                    ptc_id: Ext.getCmp('ptc_id').getValue(),
                    ptc_name: Ext.getCmp('ptc_name').getValue(),
                    ptc_days: Ext.getCmp('ptc_days').getValue(),
                    ptc_status: Ext.getCmp('ptc_status').getValue()

                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        if (Application.RetalineSettings.catAddEdit == 'Add') {
                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('paymentTermsGridPanel'));
                            Ext.getCmp('paymentTermsGridPanel').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });
                        } else {
                            Ext.getCmp('paymentTermsGridPanel').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            var gridPanel = Ext.getCmp('paymentTermsGridPanel');
                                            var index = gridPanel.store.find('ptc_id', ptc_id);
                                            gridPanel.getSelectionModel().selectRow(index);
                                        }
                                    }
                            );
                        }
                        Application.RetalineSettings.catAddEdit = '';
                        Application.RetalineSettings.ViewpaymentTermsMode(tmp.data.ptc_id);
                    } else if (tmp.success === true && tmp.valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else if (tmp.success === true && tmp.img_valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else {
                        Ext.Msg.alert("Error", tmp.message);
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        } else {
            Ext.MessageBox.alert("Notification", 'Please fill all Mandatory fields');
        }
    };
    var CourierForm = function () {
        var _courierFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterCourier',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Name',
                    id: 'mst_courier_name',
                    name: 'n[mst_courier_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 26,
                    maxValue: 100,
                    maxLength: 20
                },
                {
                    xtype: 'textfield',
                    id: 'mst_courier_id',
                    name: 'n[mst_courier_id]',
                    hidden: true
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Phone',
                    id: 'mst_courier_phone',
                    name: 'n[mst_courier_phone]',
                    vtype: 'phonespec',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 27,
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'URL',
                    id: 'mst_courier_url',
                    name: 'n[mst_courier_url]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 28,
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 29,
                    "emptyText": "Set status..",
                    "id": 'comboMasterCourierStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('mst_courier_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterCourierStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterCourierStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _courierFormPanel;
    };
    var CourierMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterCourierDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>  {mst_courier_name} </td></tr>',
                    '<tr><th width="40%">URL </th><td>{mst_courier_url}  </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var CourierMasterStore = function () {
        var _courierMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listCourier',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'mst_courier_id',
                root: 'data'
            }, ['mst_courier_id', 'mst_courier_name', 'mst_courier_url', 'status']),
            sortInfo: {
                field: 'mst_courier_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewCourierdata').getView().refresh();
                    Ext.getCmp('gridpanelMasterDataviewCourierdata').getSelectionModel().selectRow(0);
                }
            }
        });

        return _courierMasterStore;
    };
    var CourierMainGrid = function () {
        var _courierStore = CourierMasterStore();
        var _courierGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'mst_courier_name'

                }, {
                    type: 'string',
                    dataIndex: 'mst_courier_url'
                }, {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }

            ]
        });
        _courierGridFilter.remote = true;
        _courierGridFilter.autoReload = true;
        var _couriermaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _courierStore,
            iconCls: 'money',
            id: 'gridpanelMasterDataviewCourierdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _courierGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Name',
                    dataIndex: 'mst_courier_name',
                    sortable: true,
                    tooltip: 'Courier Name',
                    hideable: false
                },
                {
                    header: 'URL',
                    dataIndex: 'mst_courier_url',
                    sortable: true,
                    tooltip: 'url',
                    hideable: false
                },
                {
                    header: 'status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _courierStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedcourier
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('hsn_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.RetalineSettings.Cache.mst_courier_id = ID;
                        Ext.getCmp('formpanelMasterCourier').hide();
                        Application.RetalineSettings.ViewCourier(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _courierStore.load();
                }
            },
            tbar: [{
                    text: 'Create Courier',
                    tooltip: 'Create Courier ',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.RetalineSettings.CourierAddEdit = 'Add';
                        var courierForm = Ext.getCmp('formpanelMasterCourier').getForm();
                        Ext.getCmp('panelMasterCourierParent').setTitle('Create Courier Details');
                        loadedForm = null;
                        courierForm.reset();
                        Ext.getCmp('mst_courier_name').focus(false, 100);
                        /*<?php if (user_access("retaline_settings", "saveCourier")) { ?> */
                        Ext.getCmp('buttonMasterCourierEdit').hide();
                        Ext.getCmp('buttonMasterCourierSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonMasterCourierCancel').show();
                        Ext.getCmp('formpanelMasterCourier').show();
                        Ext.getCmp('panelMasterCourierDetailsView').hide();
                        Ext.getCmp('panelMasterCourierParent').doLayout();
                    }
                }
            ]
        });
        return _couriermaingridPanel;
    };

    var saveCourier = function () {
        if (!Ext.isEmpty(Ext.getCmp('mst_courier_name').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('comboMasterCourierStatus').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('mst_courier_url').getValue())) {

            Ext.Ajax.request({
                url: modURL + '&op=saveCourier',
                method: 'POST',
                params: {
                    id: Ext.getCmp('mst_courier_id').getValue(),
                    name: Ext.getCmp('mst_courier_name').getValue(),
                    status: Ext.getCmp('comboMasterCourierStatus').getValue(),
                    url: Ext.getCmp('mst_courier_url').getValue(),
                    phone: Ext.getCmp('mst_courier_phone').getValue()

                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        if (Application.RetalineSettings.CourierAddEdit == 'Add') {
                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewCourierdata'));
                            Ext.getCmp('formpanelMasterCourier').getForm().reset();
                            Ext.getCmp('gridpanelMasterDataviewCourierdata').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });
                        } else {
                            Ext.getCmp('gridpanelMasterDataviewCourierdata').selModel.getSelected().data = tmp.data;
                            Ext.getCmp('gridpanelMasterDataviewCourierdata').getStore().reload();
                            Ext.getCmp('gridpanelMasterDataviewCourierdata').getView().refresh();
                        }
                        Application.RetalineSettings.CourierAddEdit = '';
                        Application.RetalineSettings.ViewCourier(tmp.data.mst_courier_id);


                    } else if (tmp.success === true && tmp.valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else if (tmp.success === true && tmp.img_valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else {
                        Ext.Msg.alert("Error", tmp.message);
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        }
        else {
            Ext.MessageBox.alert("Notification", 'Please fill all mandatory fields');
        }

    };

    var masterPanelforCourier = function (id) {
        var _mpanelforCourier = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Courier',
            id: id,
            iconCls: 'my-icon446',
            items: [CourierMainGrid(), new Ext.Panel({
                    title: 'Courier Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterCourierParent',
                    height: winsize.height * 0.6,
                    items: [
                        CourierForm(), CourierMasterDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [/*<?php if (user_access("retaline_settings", "saveCourier")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            id: 'buttonMasterCourierEdit',
                            iconCls: 'edit',
                            icon: IMAGE_BASE_PATH + '/default/icons/mem_edit.png',
                            tabIndex: 30,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewCourierdata').getSelectionModel().getSelections()[0].data.mst_courier_id;
                                Application.RetalineSettings.EditCourierView(ID);
                            }
                        }, 
                         {
                            text: "Cancel",
                            tabIndex: 31,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterCourierCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewCourierdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewCourierdata').getSelectionModel().getSelections()[0].data.mst_courier_id;
                                    Application.RetalineSettings.ViewCourier(ID);
                                }
                            }
                        },
                        {
                            text: "Save",
                            tabIndex: 32,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterCourierSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveCourier();
                            }
                        }, /*<?php } ?> */
                       ]
                })
            ]
        });
        return _mpanelforCourier;
    };
     var businessTypeComboStorePrimary = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getBusinessType',
            method: 'post',
            fields: ['business_type_id', 'business_type_name'],
            //totalProperty: 'totalCount',
            root: 'data'

        });
        return store;
    };
    var businessTypeComboStoreSecondary = function () {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=getBusinessType',
            method: 'post',
            fields: ['business_type_id', 'business_type_name'],
            //totalProperty: 'totalCount',
            root: 'data'

        });
        return store;
    };
    var StoreGroupForm = function () {
        var pribusinessTypeComboStore = businessTypeComboStorePrimary();
        var secbusinessTypeComboStore = businessTypeComboStoreSecondary();
        var _storegroupsFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterStoreGroups',
            frame: false,
            border: false,
            hidden: true,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            labelAlign: 'top',
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            items: [{
                    xtype: 'spacer',
                    height: 10
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Store Group',
                    id: 'store_group_name',
                    name: 'n[store_group_name]',
                    anchor: '98%',
                    allowBlank: false,
                    tabIndex: 33,
                    maxValue: 100,
                    maxLength: 20
                }, {
                    xtype: 'combo',
                    store: pribusinessTypeComboStore,
                    mode: 'local',
                    id: 'store_group_primary_businessType',
                    allowBlank: true,
                    fieldLabel: 'Primary Business',
                    hiddenName: 'n[store_group_primary_businessType]',
                    displayField: 'business_type_name',
                    valueField: 'business_type_id',
                    typeAhead: true,
                    forceSelection: true,
                    editable: true,
                    minChars: 2,
                    anchor: '98%',
                    //selectOnFocus: true,
                    triggerAction: 'all',
                    lazyRender: true,
                    tabIndex: 34,
                    listeners: {
                        select: function () {
                            var value = Ext.getCmp('store_group_primary_businessType').getValue();
                            secbusinessTypeComboStore.baseParams.primaryBt = this.value;
                            secbusinessTypeComboStore.load();
                        }
                    }
                }, {
                    xtype: 'lovcombo',
                    store: secbusinessTypeComboStore,
                    mode: 'local',
                    id: 'store_group_additional_businessType',
                    allowBlank: true,
                    fieldLabel: 'Additional Business',
                    hiddenName: 'n[store_group_additional_businessType]',
                    displayField: 'business_type_name',
                    valueField: 'business_type_id',
                    typeAhead: true,
                    anchor: '98%',
                    editable: true,
                    minChars: 2,
                    selectOnFocus: true,
                    triggerAction: 'all',
                    lazyRender: true,
                    tabIndex: 35,
                    listeners: {
                        select: function () {
                            var primaryBt = Ext.getCmp('store_group_primary_businessType').getValue();
                            console.log('primaryBt', primaryBt);
                            if (Ext.isEmpty(primaryBt)) {
                                Ext.MessageBox.alert("Notification", "Choose Primary Businness Before choosing Additional Business.");
                                Ext.getCmp('store_group_additional_businessType').reset();
                            }
                        }
                    }
                }, {
                    xtype: 'textfield',
                    id: 'store_group_id',
                    name: 'n[store_group_id]',
                    hidden: true
                },
                mkCombo({
                    "type": STATUS_COMBO_DATA,
                    "value": "id",
                    "display": "text",
                    "name": "n[status]",
                    "fieldLabel": "Status",
                    "tabIndex": 36,
                    "emptyText": "Set status..",
                    "id": 'comboMasterStoreGroupsStatus'
                })
            ],
            listeners: {
                afterrender: function () {
                    if (Ext.isEmpty(Ext.getCmp('store_group_id').getValue())) {
                        var recordSelected = Ext.getCmp('comboMasterStoreGroupsStatus').getStore().getAt(0);
                        Ext.getCmp('comboMasterStoreGroupsStatus').setValue(recordSelected.get('id'));
                    }
                }
            }
        });
        return _storegroupsFormPanel;
    };
    var StoreGroupMasterDetailsView = function () {
        return new Ext.Panel({
            layout: 'fit',
            border: false,
            hideBorders: true,
            autoHeight: true,
            id: 'panelMasterStoreGroupsDetailsView',
            tpl: new Ext.XTemplate('<div class="details-outer">',
                    '<table border="0" width="100%" class="details_view_table">',
                    '<tr><th width="40%">Name </th><td>  {store_group_name} </td></tr>',
                    '<tr><th width="40%">Primary business </th><td>  {sg_primary_bt} </td></tr>',
                    '<tr><th width="40%">Additional business </th><td>  {sg_additional_bt} </td></tr>',
                    '<tr><th width="40%">No of stores </th><td>  {sg_store_count} </td></tr>',
                    '<tr><th width="40%">Status</th><td>',
                    '<tpl if="status == \'1\'">Active</tpl>',
                    '<tpl if="status == \'0\'">Inactive</tpl>',
                    '</td></tr>',
                    '</table>',
                    '</div>')
        });
    };
    var StoreGroupsMasterStore = function () {
        var _storegroupsMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listStoreGroups',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'store_group_id',
                root: 'data'
            }, ['store_group_id', 'store_group_name', 'status', 'defStrGrp', 'sg_store_count', 'sg_primary_bt', 'sg_additional_bt']),
            sortInfo: {
                field: 'store_group_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getView().refresh();
                    Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _storegroupsMasterStore;
    };
    var StoreGroupMainGrid = function () {
        var _storegroupsStore = StoreGroupsMasterStore();
        var _storegroupsGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'store_group_name'
                },
                {
                    type: 'string',
                    dataIndex: 'sg_primary_bt'
                },
                {
                    type: 'string',
                    dataIndex: 'sg_additional_bt'
                },
                {
                    type: 'string',
                    dataIndex: 'defStrGrp'
                },
                {
                    type: 'string',
                    dataIndex: 'status'
                },
                {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'status'
                }

            ]
        });
        _storegroupsGridFilter.remote = true;
        _storegroupsGridFilter.autoReload = true;
        var _storegroupsmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _storegroupsStore,
            iconCls: 'money',
            id: 'gridpanelMasterDataviewStoreGroupsdata',
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _storegroupsGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'ID',
                    dataIndex: 'store_group_id',
                    sortable: true,
                    hidden: true,
                    tooltip: 'ID'

                },
                {
                    header: 'Store Group',
                    dataIndex: 'store_group_name',
                    sortable: true,
                    tooltip: 'Store Group',
                    hideable: false
                }, {
                    header: 'No. of Stores',
                    dataIndex: 'sg_store_count',
                    sortable: true,
                    tooltip: 'No. of Stores',
                    hideable: false
                }, {
                    header: 'Primary Business',
                    dataIndex: 'sg_primary_bt',
                    sortable: true,
                    tooltip: 'Primary Business',
                    hideable: false
                }, {
                    header: 'Additional Business',
                    dataIndex: 'sg_additional_bt',
                    sortable: true,
                    tooltip: 'Additional Business',
                    hideable: false
                }, {
                    header: 'Default Branch',
                    dataIndex: 'defStrGrp',
                    sortable: true,
                    tooltip: 'Default Branch',
                    hideable: false
                },
                {
                    header: 'Status',
                    dataIndex: 'status',
                    sortable: true,
                    tooltip: 'Status'
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _storegroupsStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangedstoregroups
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('store_group_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.RetalineSettings.Cache.store_group_id = ID;
                        Ext.getCmp('formpanelMasterStoreGroups').hide();
                        Application.RetalineSettings.ViewStoreGroups(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _storegroupsStore.load();
                }
            },
            tbar: [{
                    text: 'Create Store Group',
                    tooltip: 'Create Store Group ',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.RetalineSettings.StoreGroupsAddEdit = 'Add';
                        var storegroupsForm = Ext.getCmp('formpanelMasterStoreGroups').getForm();
                        Ext.getCmp('panelMasterStoreGroupParent').setTitle('Create Store Group Details');
                        loadedForm = null;
                        storegroupsForm.reset();
                        Ext.getCmp('store_group_name').focus(false, 100);
                        /*<?php if (user_access("retaline_settings", "saveStoreGroups")) { ?> */
                        Ext.getCmp('buttonMasterStoreGroupEdit').hide();
                        Ext.getCmp('buttonMasterStoreGroupSave').show();
                        /*<?php } ?> */
                        Ext.getCmp('buttonMasterStoreGroupCancel').show();
                        Ext.getCmp('formpanelMasterStoreGroups').show();
                        Ext.getCmp('panelMasterStoreGroupsDetailsView').hide();
                        Ext.getCmp('panelMasterStoreGroupParent').doLayout();
                    }
                }]
        });
        return _storegroupsmaingridPanel;
    };
    var saveStoreGroups = function () {
        var ptId = Ext.getCmp('store_group_id').getValue();
        if (!Ext.isEmpty(Ext.getCmp('store_group_name').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('comboMasterStoreGroupsStatus').getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=saveStoreGroups',
                method: 'POST',
                params: {
                    id: Ext.getCmp('store_group_id').getValue(),
                    name: Ext.getCmp('store_group_name').getValue(),
                    store_group_primary_businessType: Ext.getCmp('store_group_primary_businessType').getValue(),
                    store_group_additional_businessType: Ext.getCmp('store_group_additional_businessType').getValue(),
                    status: Ext.getCmp('comboMasterStoreGroupsStatus').getValue()

                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        if (Application.RetalineSettings.StoreGroupsAddEdit == 'Add') {
                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata'));
                            Ext.getCmp('formpanelMasterStoreGroups').getForm().reset();
                            Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });
                        } else {
                            Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            var gridPanel = Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata');
                                            var index = gridPanel.store.find('store_group_id', ptId);
                                            gridPanel.getSelectionModel().selectRow(index);
                                        }
                                    }
                            );
//                            Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').selModel.getSelected().data = tmp.data;
//                            Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getStore().reload();
//                            Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getView().refresh();
                        }
                        Application.RetalineSettings.StoreGroupsAddEdit = '';
                        Application.RetalineSettings.ViewStoreGroups(tmp.data.store_group_id);
                    } else if (tmp.success === true && tmp.valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else if (tmp.success === true && tmp.img_valid === false) {
                        Ext.Msg.alert("Notification.", tmp.message);
                    } else {
                        Ext.Msg.alert("Error", 'Data you are trying to save is not valid.');
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        }
        else {
            Ext.MessageBox.alert("Notification", 'Please fill all mandatory fields');
        }

    };
    var masterPanelforStoreGroup = function (id) {
        var _mpanelforStoreGroup = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Store Groups',
            id: id,
            iconCls: 'my-icon448',
            items: [StoreGroupMainGrid(), new Ext.Panel({
                    title: 'Store Group Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterStoreGroupParent',
                    height: winsize.height * 0.6,
                    items: [
                        StoreGroupForm(), StoreGroupMasterDetailsView()
                    ],
                    buttonAlign: 'right',
                    fbar: [{
                            text: "Cancel",
                            tabIndex: 38,
                            cls: 'left-right-buttons',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            id: 'buttonMasterStoreGroupCancel',
                            hidden: true,
                            handler: function () {

                                if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getSelectionModel().getSelections())) {
                                    var ID = Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getSelectionModel().getSelections()[0].data.store_group_id;
                                    Application.RetalineSettings.ViewStoreGroups(ID);
                                }
                            }
                        },/*<?php if (user_access("retaline_settings", "saveStoreGroups")) { ?> */ {
                            text: "Edit",
                            cls: 'left-right-buttons',
                            iconCls: 'edit',
                            id: 'buttonMasterStoreGroupEdit',
                            icon: IMAGE_BASE_PATH + '/default/icons/mem_edit.png',
                            tabIndex: 37,
                            handler: function () {
                                var ID = Ext.getCmp('gridpanelMasterDataviewStoreGroupsdata').getSelectionModel().getSelections()[0].data.store_group_id;
                                Application.RetalineSettings.EditStoreGroupsView(ID);
                            }
                        }, 
                        
                        {
                            text: "Save",
                            tabIndex: 39,
                            cls: 'left-right-buttons',
                            id: 'buttonMasterStoreGroupSave',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            hidden: true,
                            handler: function () {
                                saveStoreGroups();
                            }
                        } /*<?php } ?> */
                        
                    ]
                })
            ]
        });
        return _mpanelforStoreGroup;
    };
    return {
        Cache: {},
        initfaq: function () {
            var _faqPanelId = 'panelidforFaq';
            var _masterPanelFaq = Ext.getCmp(_faqPanelId);
            if (Ext.isEmpty(_masterPanelFaq)) {
                _masterPanelFaq = masterPanelforFaq(_faqPanelId);
                Application.UI.addTab(_masterPanelFaq);
                _masterPanelFaq.doLayout();
            } else {
                Application.UI.addTab(_masterPanelFaq);
            }
        },
        faqEditView: function () {
            Application.RetalineSettings.catAddEdit = 'Edit';
            Ext.getCmp('FaqPanel').doLayout();
            Ext.getCmp('FaqPanel').setTitle('Edit FAQ details');
            Ext.getCmp('faqMasterForm').show();
            Ext.getCmp('FaqDetailsViewPanel').hide();
            /*<?php if (user_access("retaline_settings", "saveFaq")) { ?> */
            Ext.getCmp('FaqEditBtn').hide();
            Ext.getCmp('FaqSaveBtn').show();
            /*<?php } ?> */
            Ext.getCmp('FaqCancelBtn').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var masterForm = Ext.getCmp('faqMasterForm').getForm();
                masterForm.load({
                    params: {
                        faq_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=faq_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {
                        //pdtLoadedForm = [form, action.response.responseText];
                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        faqViewMode: function () {
            var faq_id = arguments[0];
            /*<?php if (user_access("retaline_settings", "saveFaq")) { ?> */
            Ext.getCmp('FaqEditBtn').show();
            Ext.getCmp('FaqSaveBtn').hide();
            /*<?php } ?> */
            Ext.getCmp('FaqCancelBtn').hide();
            Ext.getCmp('faqMasterForm').hide();
            Ext.getCmp('FaqDetailsViewPanel').show();
            Ext.getCmp('FaqPanel').setTitle('View FAQ Details');
            Ext.getCmp('FaqPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=FaqdetailsView',
                method: 'POST',
                params: {faq_id: faq_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('FaqDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('FaqPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('FaqPanel').doLayout();
        },
        initPage: function () {
            var _pagepanelId = 'panelidforPage';
            var _masterPanelPage = Ext.getCmp(_pagepanelId);
            if (Ext.isEmpty(_masterPanelPage)) {
                _masterPanelPage = masterPanelforPage(_pagepanelId);
                Application.UI.addTab(_masterPanelPage);
                _masterPanelPage.doLayout();
            } else {
                Application.UI.addTab(_masterPanelPage);
            }
        },
        EditView: function () {
            Application.RetalineSettings.subcatAddEdit = 'Edit';
            Ext.getCmp('pageparentPanel').doLayout();
            Ext.getCmp('pageparentPanel').setTitle('Edit Page Details');
            Ext.getCmp('pageMasterForm').show();
            Ext.getCmp('pageDetailsViewPanel').hide();
            /*<?php if (user_access("retaline_settings", "savePage")) { ?> */
            Ext.getCmp('pageEditBtn').hide();
            Ext.getCmp('pageSaveBtn').show();
            /*<?php } ?> */
            Ext.getCmp('pageCancelBtn').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var masterForm = Ext.getCmp('pageMasterForm').getForm();
                masterForm.load({
                    params: {
                        page_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=page_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        Ext.getCmp('page_content').setValue(tmp.data.page_content);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        ViewMode: function () {
            var page_id = arguments[0];
            /*<?php if (user_access("retaline_settings", "savePage")) { ?> */
            Ext.getCmp('pageEditBtn').show();
            Ext.getCmp('pageSaveBtn').hide();
            /*<?php } ?> */
            Ext.getCmp('pageCancelBtn').hide();
            Ext.getCmp('pageMasterForm').hide();
            Ext.getCmp('pageDetailsViewPanel').show();
            Ext.getCmp('pageparentPanel').doLayout();
            Ext.getCmp('pageparentPanel').setTitle("View Page Details");
            Ext.Ajax.request({
                url: modURL + '&op=pagedetailsView',
                method: 'POST',
                params: {page_id: page_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('pageDetailsViewPanel');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('pageparentPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('pageparentPanel').doLayout();
//            Ext.getCmp('page_content').getStore().load();
        },
        initFeedback: function () {
            var _feedbackPanelId = 'panelFeedback';
            var _feedbackPanel = Ext.getCmp(_feedbackPanelId);
            if (Ext.isEmpty(_feedbackPanel)) {
                _feedbackPanel = feedbackPanel(_feedbackPanelId);
                Application.UI.addTab(_feedbackPanel);
                _feedbackPanel.doLayout();
            } else {
                Application.UI.addTab(_feedbackPanel);
            }
        },
        ViewFeedbackMode: function () {
            var fb_id = arguments[0];
            /*<?php if (user_access("retaline_settings", "saveFeedback")) { ?> */
//            Ext.getCmp('buttonfeedbackEdit').show();
            Ext.getCmp('buttonfeedbackSave').hide();
            /*<?php } ?> */
            Ext.getCmp('feedbackPanel').setTitle('View Feedback');
            Ext.getCmp('buttonfeedbackCancel').hide();
//            Ext.getCmp('formpanelFeedback').hide();
            Ext.getCmp('xtemplatefeedbackViewDetails').show();
            Ext.getCmp('feedbackPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=feedbackDetailsView',
                method: 'POST',
                params: {fb_id: fb_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplatefeedbackViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('feedbackPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('feedbackPanel').doLayout();
        },
        initNotification: function () {
            var _notificationPanelId = 'panelAdManagement';
            var _notificationPanel = Ext.getCmp(_notificationPanelId);
            if (Ext.isEmpty(_notificationPanel)) {
                _notificationPanel = notificationPanel(_notificationPanelId);
                Application.UI.addTab(_notificationPanel);
                _notificationPanel.doLayout();
            } else {
                Application.UI.addTab(_notificationPanel);
            }
        },
        EditNotificationView: function () {
            Application.RetalineSettings.NotificationAddEdit = 'Edit';
            Ext.getCmp('notificationPanel').doLayout();
            Ext.getCmp('notificationPanel').setTitle('Edit Notification Details');
            Ext.getCmp('formpanelNotification').show();
            Ext.getCmp('xtemplatenotificationViewDetails').hide();
            /*<?php if (user_access("retaline_settings", "saveNotification")) { ?> */
            Ext.getCmp('buttonnotificationtEdit').hide();
            Ext.getCmp('buttonnotificationSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonnotificationCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {

                var masterForm = Ext.getCmp('formpanelNotification').getForm();
                masterForm.load({
                    params: {
                        notification_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=notification_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        },
        saveNotification: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var store_form = Ext.getCmp('formpanelNotification').getForm();
            if (store_form.isValid()) {
                store_form.submit({
                    url: modURL + '&op=saveNotification',
                    waitMsg: 'Saving Details....',
                    waitTitle: 'Please Wait...',
                    params: {
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    success: function (response, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        if (tmp.success === true && tmp.valid === true) {
                            Application.example.msg('Success', tmp.message);
                            if (Application.RetalineSettings.NotificationAddEdit == 'Add') {
                                recs_per_page = updateRecsPerPage(Ext.getCmp('appNotificationGridPanel'));
                                Ext.getCmp('formpanelNotification').getForm().reset();
                                Ext.getCmp('appNotificationGridPanel').store.reload({
                                    params: {
                                        start: 0,
                                        limit: recs_per_page
                                    }
                                });
                            } else {
                                Ext.getCmp('appNotificationGridPanel').selModel.getSelected().data = tmp.data;
                                Ext.getCmp('appNotificationGridPanel').getStore().load();
                            }
                            Application.RetalineSettings.NotificationAddEdit = '';
                            Application.RetalineSettings.ViewNotificationMode(tmp.data.notification_id);
                        } else if (tmp.success === true && tmp.valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else if (tmp.success === true && tmp.img_valid === false) {
                            Ext.Msg.alert("Notification.", tmp.message);
                        } else {
                            Ext.Msg.alert("Error", tmp.message);
                        }
                    },
                    failure: function (elm, conf) {
                        if (conf.failureType === 'server') {
                            var result = Ext.decode(conf.response.responseText);
                            Ext.Msg.alert('Error', result.message);
                        } else {
                            Ext.MessageBox.alert('Error', 'Check the required fields');
                        }
                    }
                });
            } else {
                Ext.MessageBox.alert('Error', 'Check the required fields');
            }
        },
        ViewNotificationMode: function () {
            var notification_id = arguments[0];
            /*<?php if (user_access("retaline_settings", "saveNotification")) { ?> */
            Ext.getCmp('buttonnotificationtEdit').show();
            Ext.getCmp('buttonnotificationSave').hide();
            /*<?php } ?> */
            Ext.getCmp('notificationPanel').setTitle('View Notification Details');
            Ext.getCmp('buttonnotificationCancel').hide();
            Ext.getCmp('formpanelNotification').hide();
            Ext.getCmp('xtemplatenotificationViewDetails').show();
            Ext.getCmp('notificationPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=notificationDetailsView',
                method: 'POST',
                params: {notification_id: notification_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplatenotificationViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('notificationPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('notificationPanel').doLayout();
        },
        initPaymentTerms: function () {
            var _PaymentTermsPanelId = 'panelPaymentTerms';
            var _PaymentTermsPanel = Ext.getCmp(_PaymentTermsPanelId);
            if (Ext.isEmpty(_PaymentTermsPanel)) {
                _PaymentTermsPanel = paymentTermsPanel(_PaymentTermsPanelId);
                Application.UI.addTab(_PaymentTermsPanel);
                _PaymentTermsPanel.doLayout();
            } else {
                Application.UI.addTab(_PaymentTermsPanel);
            }
        }, ViewpaymentTermsMode: function () {
            var ptc_id = arguments[0];
            /*<?php if (user_access("retaline_settings", "savePaymentTerms")) { ?> */
            Ext.getCmp('PTEditBtn').show();
            Ext.getCmp('PTSaveBtn').hide();
            /*<?php } ?> */
            Ext.getCmp('paymentTermsPanel').setTitle('View Payment Terms');
            Ext.getCmp('PTCancelBtn').hide();
            Ext.getCmp('formpanelPaymentTerms').hide();
            Ext.getCmp('xtemplatepaymentTermsViewDetails').show();
            Ext.getCmp('paymentTermsPanel').doLayout();
            Ext.Ajax.request({
                url: modURL + '&op=paymentTermsDetailsView',
                method: 'POST',
                params: {ptc_id: ptc_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('xtemplatepaymentTermsViewDetails');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('paymentTermsPanel').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('paymentTermsPanel').doLayout();
        },
        ptEditView: function () {
            Application.RetalineSettings.catAddEdit = 'Edit';
            Ext.getCmp('paymentTermsPanel').doLayout();
            Ext.getCmp('paymentTermsPanel').setTitle('Edit Payment Terms');
            Ext.getCmp('formpanelPaymentTerms').show();
            Ext.getCmp('xtemplatepaymentTermsViewDetails').hide();
            /*<?php if (user_access("retaline_settings", "savePaymentTerms")) { ?> */
            Ext.getCmp('PTEditBtn').hide();
            Ext.getCmp('PTSaveBtn').show();
            /*<?php } ?> */
            Ext.getCmp('PTCancelBtn').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var masterForm = Ext.getCmp('formpanelPaymentTerms').getForm();
                masterForm.load({
                    params: {
                        ptc_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=pt_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {
                        //pdtLoadedForm = [form, action.response.responseText];
                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        }, initCourier: function () {
            var _courierPanelId = 'panelMasterMainCourier';
            var _masterPanelCourier = Ext.getCmp(_courierPanelId);
            if (Ext.isEmpty(_masterPanelCourier)) {
                _masterPanelCourier = masterPanelforCourier(_courierPanelId);
                Application.UI.addTab(_masterPanelCourier);
                _masterPanelCourier.doLayout();
            } else {
                Application.UI.addTab(_masterPanelCourier);
            }
        },
        ViewCourier: function () {
            var mst_courier_id = arguments[0];
            /*<?php if (user_access("retaline_settings", "saveCourier")) { ?> */
            Ext.getCmp('buttonMasterCourierEdit').show();
            Ext.getCmp('buttonMasterCourierSave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterCourierCancel').hide();
            Ext.getCmp('formpanelMasterCourier').hide();
            Ext.getCmp('panelMasterCourierDetailsView').show();
            Ext.getCmp('panelMasterCourierParent').doLayout();
            Ext.getCmp('panelMasterCourierParent').setTitle('View Courier Details');
            Ext.Ajax.request({
                url: modURL + '&op=courierdetailsView',
                method: 'POST',
                params: {mst_courier_id: mst_courier_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterCourierDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterCourierParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterCourierParent').doLayout();
        },
        EditCourierView: function () {
            Application.RetalineSettings.CourierAddEdit = 'Edit';
            Ext.getCmp('panelMasterCourierParent').doLayout();
            Ext.getCmp('panelMasterCourierParent').setTitle('Edit Courier Details');
            Ext.getCmp('formpanelMasterCourier').show();
            Ext.getCmp('panelMasterCourierDetailsView').hide();
            /*<?php if (user_access("retaline_settings", "saveCourier")) { ?> */
            Ext.getCmp('buttonMasterCourierEdit').hide();
            Ext.getCmp('buttonMasterCourierSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterCourierCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");

            if (!Ext.isEmpty(arguments[0])) {
                var courierForm = Ext.getCmp('formpanelMasterCourier').getForm();
                courierForm.load({
                    params: {
                        mst_courier_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=courier_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        }, barcode: function () {
            var cacheWin = new Ext.Window({
                width: 300,
                height: 80,
                layout: 'fit',
                id: 'cache_win',
                closable: true,
                resizable: false,
                title: 'Barcode',
                iconCls: 'server_cache',
                plain: true,
                constrain: true,
                modal: true,
                border: false,
                items: [new Ext.form.FormPanel({
                        frame: true,
                        autoHeight: true,
                        id: 'idcacheForm',
                        items: [{
                                layout: 'column',
                                items: [{
                                        layout: 'form', /* start of form1 */
                                        columnWidth: 1,
                                        items: [{
                                                layout: 'column',
                                                items: [{
                                                        layout: 'form',
                                                        columnWidth: .5,
                                                        bodyStyle: 'padding-top:7px',
                                                        items: [{
                                                                xtype: "textfield",
                                                                hideLabel: true,
                                                                id: "bcentry",
                                                                anchor: '93%',
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
                                                        columnWidth: .5,
                                                        buttonAlign: 'left',
                                                        items: [{
                                                                buttons: [{
                                                                        text: 'Submit',
                                                                        id: 'id_clr',
                                                                        handler: function () {
                                                                            Application.RetalineCurrentStock.displayBarcodeDetails(Ext.getCmp('bcentry').getValue());

                                                                        }
                                                                    }]
                                                            }]
                                                    }]
                                            }]
                                    }]
                            }]
                    })]
            });

            cacheWin.show();
        }, initStoreGroup: function () {
            var _storegroupPanelId = 'panelMasterMainStoreGroup';
            var _masterPanelStoreGroup = Ext.getCmp(_storegroupPanelId);
            if (Ext.isEmpty(_masterPanelStoreGroup)) {
                _masterPanelStoreGroup = masterPanelforStoreGroup(_storegroupPanelId);
                Application.UI.addTab(_masterPanelStoreGroup);
                _masterPanelStoreGroup.doLayout();
            } else {
                Application.UI.addTab(_masterPanelStoreGroup);
            }
        },
        ViewStoreGroups: function () {
            var store_group_id = arguments[0];
            /*<?php if (user_access("retaline_settings", "saveStoreGroups")) { ?> */
            Ext.getCmp('buttonMasterStoreGroupEdit').show();
            Ext.getCmp('buttonMasterStoreGroupSave').hide();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterStoreGroupCancel').hide();
            Ext.getCmp('formpanelMasterStoreGroups').hide();
            Ext.getCmp('panelMasterStoreGroupsDetailsView').show();
            Ext.getCmp('panelMasterStoreGroupParent').doLayout();
            Ext.getCmp('panelMasterStoreGroupParent').setTitle("View Store Group Details");
            Ext.Ajax.request({
                url: modURL + '&op=storegroupsdetailsView',
                method: 'POST',
                params: {store_group_id: store_group_id},
                success: function (res) {
                    var tmp = Ext.decode(res.responseText);
                    if (tmp.success === true) {
                        var visualsDescPanel = Ext.getCmp('panelMasterStoreGroupsDetailsView');
                        visualsDescPanel.update(tmp);
                    }
                    Ext.getCmp('panelMasterStoreGroupParent').doLayout();
                },
                failure: function () {
                    Ext.MessageBox.alert('Error', 'Error occured while sending data');
                }
            });
            Ext.getCmp('panelMasterStoreGroupParent').doLayout();
        },
        EditStoreGroupsView: function () {
            Application.RetalineSettings.StoreGroupsAddEdit = 'Edit';
            Ext.getCmp('panelMasterStoreGroupParent').doLayout();
            Ext.getCmp('panelMasterStoreGroupParent').setTitle('Edit Store Group Details');
            Ext.getCmp('formpanelMasterStoreGroups').show();
            Ext.getCmp('panelMasterStoreGroupsDetailsView').hide();
            /*<?php if (user_access("retaline_settings", "saveStoreGroups")) { ?> */
            Ext.getCmp('buttonMasterStoreGroupEdit').hide();
            Ext.getCmp('buttonMasterStoreGroupSave').show();
            /*<?php } ?> */
            Ext.getCmp('buttonMasterStoreGroupCancel').show();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var storegroupsForm = Ext.getCmp('formpanelMasterStoreGroups').getForm();
                storegroupsForm.load({
                    params: {
                        store_group_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=storegroups_form_load',
                    waitMsg: 'Loading...',
                    success: function (form, action) {

                        var tmp = Ext.decode(action.response.responseText);
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
        }
    };
}();