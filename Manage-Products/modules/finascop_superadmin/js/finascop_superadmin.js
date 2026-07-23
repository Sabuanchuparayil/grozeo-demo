var SuperAdminTools = function () {
    var recs_per_page = 12;
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=finascop_superadmin';
    var totalComboCount;
    var myMask;
    var current_waqs_id = '', current_waqs_Name = '';
    var newlyAdded_waqs_id = '';


    var proxy = new Ext.data.HttpProxy({
        url: modURL,
        method: 'post'
    });

    var createNewWalletQSettings = function (wtq_settingName) {
        Ext.Ajax.request({
            waitMsg: 'Creating New Wallet Settings',
            url: modURL + '&op=createNewWalletQSettings',
            params: {
                wtq_settingName: wtq_settingName
            },
            failure: function (response, options) {
                Ext.MessageBox.alert("Failure", "Failed to create New Wallet Settings!" + response.responseText, function (btn) {

                });
            },
            success: function (response, options) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true)
                {
                    Application.example.msg('Success', tmp.msg);
                    newlyAdded_waqs_id = tmp.waqs_id;
                    Ext.getCmp('walletQueueSettingsID').getStore().reload();

                } else {
                    Ext.MessageBox.alert("Failure", tmp.msg, function (btn) {

                    });
                }
            }
        });
    }

    var walletQSettingsFromJSON = function (waqs_id) {
        Ext.Ajax.request({
            waitMsg: 'Getting Wallet Json settings',
            url: modURL + '&op=populateFromJson',
            params: {
                waqs_id: waqs_id
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', response.responseText);
            },
            success: function (response, options) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true)
                {
                    Ext.getCmp('wst_WalletSettingsPanelID').getForm().reset();
                    Application.example.msg('Success', 'Wallet Settings Item added.');
                    var settingsStore = Ext.getCmp('wst_WalletSettingGrid').getStore();
                    settingsStore.load({
                        params: {
                            waqs_id: waqs_id
                        }
                    });
                    buildJsonPreview(settingsStore);
                    Ext.getCmp('wst_Comments').setValue(tmp.comments);
                } else {
                    Ext.MessageBox.alert("Failure", "Failed to Load Wallet Setting Json!" + response.responseText, function (btn) {

                    });
                }
            }
        });
    }

    var showSettingsJson = function (waqs_id) {
        Ext.Ajax.request({
            waitMsg: 'Getting Wallet Json settings',
            url: modURL + '&op=getWalletQSettingsJson',
            params: {
                waqs_id: waqs_id
            },
            failure: function (response, options) {
                Ext.MessageBox.alert('Notification', response.responseText);
            },
            success: function (response, options) {
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true)
                {
                    var waqsConfSetting = response.responseText.substring(response.responseText.indexOf(":{") + 1, response.responseText.length - 1);
                    Ext.getCmp('taWalletSetting').setValue(waqsConfSetting);
                    Ext.getCmp('wst_Comments').setValue(tmp.data.comments);
                    if (!Ext.isEmpty(tmp.data.crossbranch)) {
                        if (tmp.data.crossbranch == 'true') {
                            Ext.getCmp('wst_multiBranch').setValue(1);
                        } else {
                            Ext.getCmp('wst_multiBranch').setValue(0);
                        }
                    } else {
                        Ext.getCmp('wst_multiBranch').setValue(0);
                    }
                } else {
                    Ext.MessageBox.alert("Failure", "Failed to Load Wallet Setting Json!" + response.responseText, function (btn) {

                    });
                }
            }
        });
    };

    var loadWalletSettingsGrid = function (waqs_id) {
        Ext.getCmp('wst_WalletSettingGrid').getStore().load({
            params: {
                waqs_id: waqs_id
            }
        });
    };

    var buildJsonPreview = function (settingsStore) {


        var json, cr = '"cr" : {', dr = '"dr" : {', crComma = '', drComma = '';
        var comments = '"comments" : "' + new String(Ext.getCmp('wst_Comments').getValue()) + '",';
        var crossbranch = '"crossbranch" : "' + new String(Ext.getCmp('wst_multiBranch').getValue()) + '"';
        var crCount = 0;
        walletSettings = settingsStore.getRange();
        walletSettings.forEach(buildElements);

        cr = cr + '},';
        dr = dr + '},';
        if (crCount == 1)
            json = '{' + cr + dr + comments + crossbranch + '}';
        else
            json = '{' + dr + cr + comments + crossbranch + '}';

        Ext.getCmp('taWalletSetting').setValue(JSON.stringify(JSON.parse(json), null, 4));


        function buildElements(record, index)
        {
            if (record.get('waqt_drcr') == 'Credit') {
                cr = cr + crComma + '"' + record.get('waqt_account_name') + '" : { ';
                cr = cr + '"key" : "' + record.get('waqt_key') + '" ,';
                cr = cr + '"amt" : "0" ,';
                cr = cr + '"br_ReferenceID" : "-1" ,';
                cr = cr + '"type" : "' + record.get('waqt_type') + '"';
                cr = cr + '}';
                crComma = ',';
                crCount++;

            }

            if (record.get('waqt_drcr') == 'Debit') {
                dr = dr + drComma + '"' + record.get('waqt_account_name') + '" : { ';
                dr = dr + '"key" : "' + record.get('waqt_key') + '" ,';
                dr = dr + '"amt" : "0" ,';
                dr = dr + '"br_ReferenceID" : "-1" ,';
                dr = dr + '"type" : "' + record.get('waqt_type') + '"';
                dr = dr + '}';
                drComma = ',';
            }

        }
    };

    var removeWalletSettings = function (waqt_id) {

        Ext.Ajax.request({
            url: modURL + '&op=removeWalletSettingsItem',
            method: 'POST',
            params: {
                waqt_id: waqt_id
            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success == true)
                {
                    Application.example.msg('Success', 'Wallet Settings Item removed.');
                    var settingsStore = Ext.getCmp('wst_WalletSettingGrid').getStore();
                    settingsStore.load({
                        params: {
                            waqs_id: current_waqs_id
                        }
                    });
                    buildJsonPreview(settingsStore);
                } else
                {
                    Ext.MessageBox.alert('Failed', response.responseText);
                }
            },
            failure: function (response) {
                Ext.MessageBox.alert('Error', response.responseText);
            }
        });

    };

    var addWalletSettings = function () {
        var ledType = Ext.getCmp('wstLedgerType').getValue();
        comments = Ext.getCmp('wst_Comments').getValue();
        Ext.Ajax.request({
            url: modURL + '&op=addWalletSettingsItem',
            method: 'POST',
            params: {
                waqs_id: current_waqs_id,
                waqs_Name: current_waqs_Name,
                waqt_drcr: Ext.getCmp('wstCrDr').getValue(),
                waqt_account_name: Ext.getCmp('wstAccountName').getValue(),
                waqt_key: (ledType == 3) ? "<Enter ledt_referenceID here>" : Ext.getCmp('wstLedger').getValue(),
                waqt_type: ledType

            },
            success: function (response) {
                var res = Ext.decode(response.responseText);
                if (res.success === true)
                {
                    Ext.getCmp('wst_WalletSettingsPanelID').getForm().reset();
                    Application.example.msg('Success', 'Wallet Settings Item added.');
                    var settingsStore = Ext.getCmp('wst_WalletSettingGrid').getStore();
                    settingsStore.load({
                        params: {
                            waqs_id: current_waqs_id
                        }
                    });
                    buildJsonPreview(settingsStore);
                    Ext.getCmp('wst_Comments').setValue(comments);

                } else
                {
                    Ext.MessageBox.alert('Failed', response.responseText);
                }
            },
            failure: function (response) {

                Ext.MessageBox.alert('Error', response.responseText);
            }
        });

    };

    var editAndUupdateRecord = function (record, grid, rowIndex) {

        Application.RetalineSales.Cache.editingItemRec = true;
        Application.RetalineSales.Cache.currentItemID = record.get('wst_itemid');
        Ext.getCmp('wstCustomerItems').setValue(record.get('wst_itemid'));
        Application.RetalineSales.Cache.currentItem = record.get('wst_itemname');


    };



    var wstConfigColModel = function () {
        return new Ext.grid.ColumnModel([
            {
                header: 'Dr / Cr',
                sortable: true,
                hideable: false,
                dataIndex: 'waqt_drcr',
                tooltip: 'Debit / Credit',
                width: 50

            },
            {
                header: 'Account Name',
                sortable: false,
                dataIndex: 'waqt_account_name',
                tooltip: 'Account Name',
                width: 130
            },
            {
                header: 'Ledger Name',
                sortable: true,
                dataIndex: 'ledger_name',
                tooltip: 'Ledger Name',
                width: 130
            },
            {
                header: 'Ledger Key Type',
                sortable: false,
                hideable: false,
                dataIndex: 'waqt_type',
                tooltip: 'Ledger Key Type',
                width: 100
            },
            {
                xtype: 'actioncolumn',
                hideable: false,
                width: 16,
                items: [
                    {
                        tooltip: 'Remove Item',
                        getClass: function (v, meta, rec) {
                            return 'finascop_delete';
                        },
                        handler: function (grid, rowIndex, colIndex) {
                            var record = grid.store.getAt(rowIndex);
                            Ext.Msg.confirm('Notification', 'Do you really want to remove this item?', function (btn) {
                                if (btn == 'yes') {
                                    removeWalletSettings(record.get('waqt_id'));
                                }
                            });
                        }
                    }]
            },
            {
                header: '',
                sortable: false,
                hideable: false,
                width: 8,
                dataIndex: ''
            }
        ]);
    };

    this.WalletConfigSettings = function () {
        newlyAdded_waqs_id = '';
        var wstDetailsGrid = function (waqs_id) {
            var wst_ItemStore = function () {
                var settingsStore = new Ext.data.JsonStore({
                    method: 'post',
                    proxy: new Ext.data.HttpProxy({
                        url: modURL + '&op=getWalletQSettings',
                        method: 'post'
                    }),
                    fields: ['waqt_id', 'waqs_id', 'waqs_Name', 'waqt_drcr', 'waqt_account_name', 'waqt_type', 'waqt_key', 'ledger_name'],
                    totalProperty: 'totalCount',
                    root: 'data',
                    remoteSort: true,
                    autoLoad: false,
                    listeners: {
                        beforeload: function (store, e) {
                            this.baseParams.waqs_id = waqs_id;
                        },
                        load: function (store, records, options) {
                            buildJsonPreview(store);
                        }
                    }

                });
                return settingsStore;
            };
            var settingsGridStore = wst_ItemStore();
            var settingsgrid_panel = new Ext.grid.GridPanel({
                store: settingsGridStore,
                frame: false,
                border: false,
                title: '',
                height: 400,
                id: 'wst_WalletSettingGrid',
                loadMask: true,
                sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true
                }),
                cm: wstConfigColModel(),
                viewConfig: {
                    forceFit: true,
                    getRowClass: function (record, index) {

                        if (record.get('isStockEnough') == 0)
                        {
                            return 'finascop_indicateColPINK ';
                        } else {
                            return '';
                        }
                    },
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

                },
                stripeRows: true
            });
            return settingsgrid_panel;
        };

        var createWalletSettingsConfigForm = function (waqs_id) {

            var ledgerStore = new Ext.data.JsonStore({
                autoLoad: false,
                url: modURL + '&op=getLedgerDetails',
                method: 'post',
                fields: ['waqt_key', 'ledger_name'],
                totalProperty: 'totalCount',
                root: 'data'
            });

            var led_typeStore = new Ext.data.ArrayStore({
                fields: ['led_typeID', 'led_type'],
                data: [[1, 'ledgerdefaulttype'], [2, 'ledgertype'], [3, 'ledger']]

            });

            var trTypeStore = new Ext.data.ArrayStore({
                fields: ['tr_typeID', 'tr_type'],
                data: [['dr', 'Debit'], ['cr', 'Credit']]

            });



            var panel = new Ext.form.FormPanel({
                frame: true,
                border: false,
                title: 'Config List',
                id: 'wst_WalletSettingsPanelID',
                listeners: {
                    afterrender: function () {

                    }
                },
                layout: 'column',
                items: [
                    {
                        layout: 'column',
                        style: 'margin-bottom:3px;',
                        id: 'itemImputPanel',
                        columnWidth: 1,
                        items: [{
                                layout: 'column',
                                style: 'margin-bottom:3px;',
                                columnWidth: 1,
                                items: [{
                                        layout: 'form',
                                        columnWidth: 0.10,
                                        labelAlign: 'top',
                                        items: [
                                            {
                                                xtype: 'combo',
                                                fieldLabel: 'Cr / Dr',
                                                emptyText: 'Cr / Dr',
                                                id: 'wstCrDr',
                                                name: 'wst[wstCrDr]',
                                                labelStyle: mandatory_label,
                                                allowBlank: false,
                                                mode: 'local',
                                                typeAhead: true,
                                                forceSelection: true,
                                                editable: true,
                                                anchor: '99%',
                                                store: trTypeStore,
                                                triggerAction: 'all',
                                                minChars: 1,
                                                displayField: 'tr_type',
                                                valueField: 'tr_typeID',
                                                hiddenName: 'wstCrDr',
                                                tabIndex: 300,
                                                listeners: {
                                                }
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        columnWidth: 0.43,
                                        labelAlign: 'top',
                                        items: [{
                                                xtype: 'textfield',
                                                fieldLabel: 'Account Name',
                                                readOnly: false,
                                                id: 'wstAccountName',
                                                name: 'n[wstAccountName]',
                                                anchor: '98%',
                                                tabIndex: 301,
                                                allowBlank: false,
                                                listeners: {
                                                    afterrender: function (field) {

                                                    }
                                                }
                                            }]
                                    }, {
                                        layout: 'form',
                                        labelAlign: 'top',
                                        columnWidth: 0.20,
                                        items: [
                                            {
                                                xtype: 'combo',
                                                fieldLabel: 'Ledger Type',
                                                emptyText: 'Ledger Type',
                                                id: 'wstLedgerType',
                                                name: 'wst[wstLedgerType]',
                                                labelStyle: mandatory_label,
                                                allowBlank: false,
                                                mode: 'local',
                                                typeAhead: true,
                                                forceSelection: true,
                                                editable: true,
                                                anchor: '99%',
                                                store: led_typeStore,
                                                triggerAction: 'all',
                                                minChars: 1,
                                                displayField: 'led_type',
                                                valueField: 'led_typeID',
                                                hiddenName: 'wstCrDr',
                                                tabIndex: 302,
                                                listeners: {
                                                    change: function (cmb, newValue, oldValue) {
                                                        ledgerStore.load({
                                                            params: {
                                                                led_type: newValue
                                                            }
                                                        });
                                                    }
                                                }
                                            }
                                        ]
                                    }, {
                                        layout: 'form',
                                        columnWidth: 0.20,
                                        labelAlign: 'top',
                                        items: [
                                            {
                                                xtype: 'combo',
                                                fieldLabel: 'Ledger',
                                                emptyText: 'Ledger',
                                                id: 'wstLedger',
                                                name: 'wst[wstLedger]',
                                                labelStyle: mandatory_label,
                                                allowBlank: true,
                                                mode: 'local',
                                                typeAhead: false,
                                                forceSelection: true,
                                                editable: false,
                                                anchor: '99%',
                                                store: ledgerStore,
                                                triggerAction: 'all',
                                                displayField: 'ledger_name',
                                                valueField: 'waqt_key',
                                                hiddenName: 'wstCrDr',
                                                tabIndex: 303,
                                                listeners: {
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        layout: 'form',
                                        labelAlign: 'top',
                                        columnWidth: 0.07,
                                        items: [{
                                                xtype: 'button',
                                                text: 'Add',
                                                width: '20px',
                                                tabIndex: 304,
                                                id: 'addItemsToPO',
                                                iconCls: 'finascop_add',
                                                style: 'margin-top:19px; margin-left:2px;',
                                                handler: function () {
                                                    if (Ext.getCmp('wst_WalletSettingsPanelID').getForm().isValid()) {
                                                        addWalletSettings();
                                                    } else {
                                                        Ext.Msg.alert("Notification.", 'Unable to add items. <br>Please fill necessary fields.');
                                                    }

                                                }
                                            }]
                                    }]
                            }]
                    },
                    {
                        xtype: 'fieldset',
                        title: 'Wallet Settings',
                        tabIndex: 305,
                        columnWidth: 1,
                        style: 'margin-bottom:3px;',
                        id: 'b2b_so_gridItems',
                        items: [{
                                layout: 'fit',
                                items: [{
                                        layout: 'form',
                                        columnWidth: 1,
                                        items: [
                                            wstDetailsGrid(waqs_id)
                                        ]
                                    }]
                            }]
                    },
                    {
                        layout: 'column',
                        columnWidth: 1,
                        items: [
                            {
                                layout: 'form',
                                columnWidth: 1,
                                labelWidth: 55,
                                items: [{
                                        xtype: 'textfield',
                                        fieldLabel: 'Comments',
                                        style: 'text-align:left',
                                        id: 'wst_Comments',
                                        name: 'wst_Comments',
                                        allowBlank: true,
                                        tabIndex: 306,
                                        anchor: '99%',
                                        listeners: {
                                            change: function (txtFld, newVal, oldVal) {
                                                var settingsStore = Ext.getCmp('wst_WalletSettingGrid').getStore();
                                                buildJsonPreview(settingsStore);
                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: 1,
                                labelWidth: 55,
                                items: [{
                                        xtype: 'checkbox',
                                        boxLabel: 'Cross Store',
                                        id: 'wst_multiBranch',
                                        name: 'wst_multiBranch',
                                        tabIndex: 307,
                                        anchor: '99%',
                                        //inputValue: 1,
                                        checked: false,
                                        listeners: {
                                            check: function () {
                                                var settingsStore = Ext.getCmp('wst_WalletSettingGrid').getStore();
                                                buildJsonPreview(settingsStore);
                                            }
                                        }
                                    }]
                            }
                        ]
                    }
                ]
            });

            return panel;
        };

        var filter = new Ext.ux.grid.GridFilters({
                //remote: true,
                local: true,
                filters: [
         {
                        type: 'string',
                        dataIndex: 'waqs_Name'
                    }]
            });
        var wstListGrid = function () {
            //getWalletQueueSettingsList

            var walletQueueSettingsJsonStore = function () {
                var store = new Ext.data.JsonStore({
                    method: 'post',
                    proxy: new Ext.data.HttpProxy({
                        url: modURL + '&op=getWalletQueueSettingsList',
                        method: 'post'
                    }),
                    fields: ['waqs_id', 'waqs_Name'],
                    totalProperty: 'totalCount',
                    root: 'data',
                    remoteSort: false,
                    autoLoad: true,
                    listeners: {
                        load: function (store, records, options) {
                            var waqs_id;
                            var waqs_Name;
                            if (newlyAdded_waqs_id != '') {
                                var count = store.getCount()
                                Ext.getCmp('walletQueueSettingsID').getSelectionModel().selectRow(count - 1);
                                waqs_id = store.getAt(count - 1).get('waqs_id');
                                waqs_Name = store.getAt(count - 1).get('waqs_Name');
                            } else {
                                Ext.getCmp('walletQueueSettingsID').getSelectionModel().selectRow(0)
                                waqs_id = store.getAt(0).get('waqs_id');
                                waqs_Name = store.getAt(0).get('waqs_Name');
                            }

                            current_waqs_id = waqs_id, current_waqs_Name = waqs_Name;
                            showSettingsJson(waqs_id);
                            loadWalletSettingsGrid(waqs_id);
                        }
                    }

                });
                store.setDefaultSort('waqs_Name', 'ASC');
                return store;
            };
            var walletQueueSettingsStore = walletQueueSettingsJsonStore();
            var grid_panel = new Ext.grid.GridPanel({
                store: walletQueueSettingsStore,
                layout: 'fit',
                frame: false,
                border: false,
                title: 'Wallet Queue Settings',
                id: 'walletQueueSettingsID',
                loadMask: true,
                height: 400,
                plugins: [filter],
                autoScroll: true,
                columns: [new Ext.grid.RowNumberer(),
                    {
                        header: 'Wallet Q Name',
                        id: 'WalletQName',
                        sortable: true,
                        hideable: false,
                        dataIndex: 'waqs_Name',
                        tooltip: 'Wallet Queue Settings Name'
                    },
                    {
                        xtype: 'actioncolumn',
                        header: 'Action',
                        width: 30,
                        hideable: false,
                        sortable: false,
                        groupable: false,
                        tooltip: 'Action',
                        items: [{
                                iconCls: 'edit',
                                tooltip: 'Populate from JSON',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.store.getAt(rowIndex);
                                    var waqs_id = record.get('waqs_id');
                                    walletQSettingsFromJSON(waqs_id);
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
                tbar:
                        [
                            {
                                xtype: 'textfield',
                                fieldLabel: '',
                                id: 'wtq_SettingNameTxtFldID',
                                name: 'wtq_SettingNameTxtFldID',
                                allowBlank: true,
                                width: 200,
                                selectOnFocus: true,
                                anchor: '99%',
                                value: '',
                                listeners: {
                                    change: function (field, newValue, oldValue) {

                                    },
                                    afterrender: function (field) {
                                        Ext.defer(function () {
                                            field.focus(true, 100);
                                        }, 1);
                                    }
                                }
                            },
                            {
                                tooltip: 'Create New Wallet Queue Setting',
                                icon: './resources/images/submenuicons/add.png',
                                handler: function () {
                                    createNewWalletQSettings(Ext.getCmp('wtq_SettingNameTxtFldID').getValue());
                                }
                            }

                        ],
                listeners: {
                    rowclick: function (grid, rowIndex, e) {
                        var waqsSettingStore = grid.getStore();
                        var waqs_id = waqsSettingStore.getAt(rowIndex).get('waqs_id');
                        current_waqs_id = waqs_id, current_waqs_Name = waqsSettingStore.getAt(rowIndex).get('waqs_Name');
                        newlyAdded_waqs_id = '';
                        showSettingsJson(waqs_id);
                        loadWalletSettingsGrid(waqs_id);
                    }
                },
                stripeRows: true,
                autoExpandColumn: 'WalletQName'
            });
            return grid_panel;

        };

        var wstJsonTxtArea = function () {
            var textarea = new Ext.form.TextArea({
                id: 'taWalletSetting',
                title: 'Config JSON',
                name: 'taWalletSettingsJson',
                hidden: false,
                maxLength: 2500,
                readOnly: true,
                allowBlank: false
            });
            return textarea;
        };

        var walletPanel = function () {
            var panel = new Ext.Panel({
                frame: false,
                hideBorders: true,
                layout: 'border',
                border: false,
                id: 'walletConfigPanelID',
                items: [
                    {
                        region: 'center',
                        layout: 'fit',
                        hidden: false,
                        items: [new Ext.TabPanel({
                                activeTab: 0,
                                border: false,
                                id: 'wallet_details_tab',
                                deferredRender: false,
                                enableTabScroll: true,
                                tabPosition: 'top',
                                items: [createWalletSettingsConfigForm()
                                            , wstJsonTxtArea()],
                                listeners: {
                                    tabchange: function (panel, currentTab) {

                                    }
                                }
                            })]
                    },
                    {
                        region: 'west',
                        layout: 'fit',
                        width: winsize.width * 0.24,
                        items: wstListGrid()
                    }
                ],
                buttons: [{
                        text: 'Cancel',
                        tabIndex: 20,
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            WalletConfigWindow.close();
                        }
                    },
                    {
                        xtype: 'button',
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        iconCls: 'update',
                        handler: function () {
                            Ext.Ajax.request({
                                waitMsg: 'Processing & Creating New Auto Wallets',
                                url: modURL + '&op=saveNewWalletConfig',
                                params: {
                                    waqs_id: current_waqs_id,
                                    waqs_Configuration: Ext.getCmp('taWalletSetting').getValue(),
                                    wst_multiBranch: Ext.getCmp('wst_multiBranch').getValue()
                                },
                                failure: function (response, options) {
                                    Ext.MessageBox.alert('Notification', response.responseText);
                                },
                                success: function (response, options) {
                                    var tmp = Ext.decode(response.responseText);
                                    if (tmp.success == true)
                                    {
                                        Application.example.msg('Success', tmp.msg);
                                        newlyAdded_waqs_id = tmp.waqs_id;
                                        Ext.getCmp('walletQueueSettingsID').getStore().reload();

                                    } else {
                                        Ext.MessageBox.alert("Failure", tmp.msg, function (btn) {

                                        });
                                    }
                                }
                            });
                        }
                    }
                    ]
            });
            return panel;
        };

        var WalletConfigWindow = new Ext.Window({
            width: winsize.width * 0.8,
            height: 650,
            layout: 'fit',
            id: 'Wallet_win',
            closable: true,
            resizable: true,
            title: 'Wallet Config Editor Utility',
            plain: true,
            constrain: true,
            modal: true,
            border: false,
            autoScroll: true,
            items: [walletPanel()]
        });
        WalletConfigWindow.show();

    };

    this.CreateNewAutoLedgers = function () {

        var LedgerForm = function () {

            var PendingLedgerNameStore = function () {
                var LedgerName = new Ext.data.JsonStore({
                    fields: [
                        {name: 'ledgerName'}, 'isPaymentGateway','ledgertypedefaultid',
                        {name: 'status'}, 'GroupName', 'ledt_referenceID'
                    ],
                    remoteSort: false,
                    autoLoad: true,
                    url: modURL + '&op=getLedgerStatus',
                    method: 'post',
                    totalProperty: 'totalCount',
                    root: 'data',
                    listeners: {
                    }
                });
                return LedgerName;
            };




            var ledgerNameStore = PendingLedgerNameStore();

            var ledgeGridPanel = new Ext.grid.GridPanel({
                store: ledgerNameStore,
                frame: false,
                border: false,
                title: '',
                height: 200,
                id: 'ledger_grid',
                loadMask: true,
                autoScroll: true,
                sm: new Ext.grid.RowSelectionModel({
                    singleSelect: true
                }),
                columns: [new Ext.grid.RowNumberer(),
                    {
                        header: 'Ledger',
                        sortable: true,
                        hideable: false,
                        dataIndex: 'ledgerName',
                        tooltip: 'Ledger Name',
                        width: 130

                    }, {
                        header: 'Group',
                        sortable: true,
                        hideable: false,
                        dataIndex: 'GroupName',
                        tooltip: 'Ledger Name',
                        width: 130

                    }, {
                        header: 'Payment Gateway',
                        sortable: true,
                        hideable: false,
                        dataIndex: 'isPaymentGateway',
                        tooltip: 'Payment Gateway',
                        width: 130

                    }, {
                        header: 'Reference Id',
                        sortable: true,
                        hideable: false,
                        dataIndex: 'ledt_referenceID',
                        tooltip: 'Reference Id',
                        width: 130

                    },
                    {
                        xtype: 'actioncolumn',
                        hideable: false,
                        id: 'ledStatus',
                        width: 80,
                        items: [{
                                sortable: false,
                                getClass: function (v, meta, rec) {
                                    if (rec.get('status') == '1')
                                    {
                                        return 'now_active';
                                    } else
                                    {
                                        return 'now_inactive';
                                    }
                                },
                                tooltip: 'Ledger Status',
                            }, {
                                sortable: false,
                                getClass: function (v, meta, rec) {
                                    if (rec.get('GroupName') == 'Bank') {
                                        return 'arrow_rotate_clockwise';
                                    } else {
                                        return 'hideicon';
                                    }
                                },
                                tooltip: 'Payment Gateway',
                                handler: function (grid, rowIndex, colIndex) {
                                    var record = grid.getStore().getAt(rowIndex);
                                    Ext.Ajax.request({
                                        url: modURL + '&op=updatePaymentGw',
                                        method: 'POST',
                                        params: {
                                            GroupName: record.get('GroupName'),
                                            isPaymentGateway:record.get('isPaymentGateway'),
                                            ledgertypedefaultid: record.get('ledgertypedefaultid')
                                        },
                                        success: function (response) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true) {
                                                Ext.getCmp('ledger_grid').getStore().load();
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
                    }
                ],
                viewConfig: {
                    forceFit: true,
                    deferEmptyText: false,
                    emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
                },
                stripeRows: true
            });
            return ledgeGridPanel;
        };

        var LedListUpdaterWin = function () {
            var group_store = new Ext.data.Store({
                proxy: proxy,
                baseParams: {
                    op: 'getGroups'
                },
                reader: new Ext.data.ArrayReader({}, ['Group_ID', 'GroupName'])
            });
            var AddLedgerWin = new Ext.Window({
                id: "fssa_LedListUpdaterWinID",
                title: 'Ledger List Updator',
                shadow: false,
                width: 300,
                modal: true,
                height: 100,
                layout: 'fit',
                autoHeight: true,
                resizable: true,
                closable: true,
                items: [
                    {
                        xtype: 'panel',
                        anchor: '98$',
                        border: false,
                        frame: false,
                        layout: 'form',
                        height: 100,
                        labelAlign: 'top',
                        items: [{
                                xtype: 'textfield',
                                fieldLabel: 'Ledger List',
                                id: 'fssa_LedgerListUpdater',
                                name: 'fssa_LedgerListUpdater',
                                allowBlank: true,
                                selectOnFocus: true,
                                allowNegative: false,
                                anchor: '99%',
                                value: '',
                                style: {'text-transform': 'uppercase'},
                                listeners: {
                                    change: function (field, newValue, oldValue) {
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
                                id: 'fssa_ledgerGroup',
                                name: 'ledgertypegroupid',
                                hiddenName: 'ledger[Group_ID]',
                                forceSelection: true,
                                editable: true,
                                typeAhead: true,
                                anchor: '99%',
                                minChars: 0,
                                displayField: 'GroupName',
                                valueField: 'Group_ID',
                                allowBlank: false,
                                fieldLabel: 'Select Group',
                                store: group_store

                            }]
                    }
                ],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 523,
                        handler: function () {
                            Ext.getCmp('fssa_LedListUpdaterWinID').close();
                        }
                    }, {
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        text: 'Update',
                        id: 'fssaSubmitBtn',
                        tabIndex: 522,
                        handler: function () {
                            Ext.Ajax.request({
                                url: modURL + '&op=updateLedgerList',
                                params: {
                                    ledgertypedefaultname: Ext.getCmp('fssa_LedgerListUpdater').getValue().toUpperCase(),
                                    Group_ID: Ext.getCmp('fssa_ledgerGroup').getValue(),
                                    GroupName: Ext.getCmp('fssa_ledgerGroup').getRawValue()
                                },
                                method: 'POST',
                                success: function (res, opt) {
                                    var tmp = Ext.decode(res.responseText);
                                    if (tmp.success === true) {
                                        Application.example.msg('Success', "Ledger List Updated.");
                                        Ext.isEmpty(Ext.getCmp('ledger_grid')) ? {} : Ext.getCmp('ledger_grid').getStore().load();
                                        AddLedgerWin.close();
                                    } else {
                                        Ext.MessageBox.alert("Notification", res.responseText);
                                    }
                                },
                                failure: function (res, opt) {
                                    Ext.MessageBox.alert('Notification', res.responseText,
                                            function (btn) {
                                                AddLedgerWin.close();
                                            });
                                }
                            });
                        }
                    }]
            });

            AddLedgerWin.doLayout();
            AddLedgerWin.show();
            AddLedgerWin.center();
        };

        var LedgerWindow = new Ext.Window({
            width: winsize.width * 0.6,
            height: 500,
            layout: 'fit',
            id: 'Ledger_win',
            closable: true,
            resizable: true,
            title: 'Ledger Creation Utility',
            plain: true,
            constrain: true,
            modal: true,
            border: false,
            items: [LedgerForm()],
            buttons: [{
                    text: 'Cancel',
                    tabIndex: 20,
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        LedgerWindow.close();
                    }
                },
                {
                    xtype: 'button',
                    text: 'Create Ledgers for all branches',
                    iconCls: 'update',
                    handler: function () {
                        Ext.Ajax.request({
                            waitMsg: 'Processing & Creating New Auto Ledgers',
                            url: modURL,
                            params: {
                                op: 'createNewAutoLedgers'
                            },
                            failure: function (response, options) {
                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                            },
                            success: function (response, options) {
                                var tmp = Ext.decode(response.responseText);
                                if (tmp.success === true)
                                {
                                    Ext.MessageBox.alert("Success", "Created Ledgers", function (btn) {
                                        Ext.getCmp('ledger_grid').getStore().reload();
                                    });
                                } else {
                                    Ext.MessageBox.alert("Failure", "Failed to Created Ledgers for all branches", function (btn) {
                                        Ext.getCmp('ledger_grid').getStore().reload();
                                    });
                                }
                            }
                        });
                    }
                },
                {
                    text: 'Add Ledger',
                    tabIndex: 20,
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'finascop_add',
                    handler: function () {
                        LedListUpdaterWin();
                    }
                }
                ],
        });
        LedgerWindow.show();
    };
}

Application.FinascopSuperAdmin = new SuperAdminTools();