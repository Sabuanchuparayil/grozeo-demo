Application.DeliveryRules = function () {
    var RECS_PER_PAGE = 20;
    var modURL = '?module=delivery_rules';
    var winsize = Ext.getBody().getViewSize();
    function onGridResize(cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    }
    ;
    var gridSelectionChangeddeliveryrules = function (sm) {
        if (!Ext.isEmpty(Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getSelectionModel().getSelections()[0].data.rdr_id;
            Application.DeliveryRules.ViewDeliveryRules(ID);
        }
    };
    var masterPanelforDeliveryRule = function (id) {
        var src = '?module=delivery_rules&op=dr_details&rdr_id=' + Application.DeliveryRules.Cache.rdr_id;
        var _mpanelforDeliveryRule = new Ext.Panel({
            frame: false,
            hideBorders: true,
            layout: 'border',
            border: false,
            title: 'Delivery Rules ',
            id: id,
            iconCls: 'my-icon448',
            items: [DeliveryRuleMainGrid(), new Ext.Panel({
                    title: 'Delivery Charge Calculation Rules Details',
                    frame: false,
                    border: true,
                    region: 'east',
                    width: winsize.width * 0.4,
                    autoScroll: true,
                    cls: 'left_side_panel',
                    id: 'panelMasterDeliveryRuleParent',
                    height: winsize.height * 0.6,
                    items: [{
                            id: 'details_view_panel_order',
                            hidden: true,
                            html: '<iframe id="iframe_productdtls" name="iframe_productdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }],
                    buttonAlign: 'right',
                    fbar: [
                    ]
                })
            ]
        });
        return _mpanelforDeliveryRule;
    };
    var storeOrGroupStore = new Ext.data.JsonStore({
        autoLoad: true,
        url: modURL + '&op=storeForGroupStre',
        method: 'post',
        fields: ['id', 'name']
    });
    var DeliveryRuleForm = function () {
        var _deliveryrulesFormPanel = new Ext.form.FormPanel({
            id: 'formpanelMasterDeliveryRules',
            frame: false,
            border: false,
            autoHeight: true,
            autoScroll: true,
            labelWidth: 120,
            bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 10px"},
            layout: 'column',
            items: [{
                    xtype: 'spacer',
                    height: 10
                }, {
                    xtype: 'hidden', id: 'rdr_id', name: 'rdr_id'
                }, {
                    layout: 'form',
                    columnWidth: 0.5,
                    frame: false,
                    border: false,
                    labelAlign: 'top',
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Delivery Mode',
                            emptyText: 'Choose Delivery Mode',
                            id: 'rdr_deliveryMode',
                            name: 'rdr_deliveryMode',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: new Ext.data.JsonStore({
                                fields: ['id', 'name'],
                                data: [{id: '1', name: 'Courier Delivery'}, {id: '2', name: 'Express Delivery'}, {id: '3', name: 'Scheduled Delivery'}]
                            }),
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'name',
                            valueField: 'id',
                            hiddenName: 'rdr_deliveryMode',
                            tabIndex: 1,
                            listeners: {
                                select: function () {
                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.5,
                    labelAlign: 'top',
                    frame: false,
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Calculation Mode',
                            emptyText: 'Choose Calculation Mode',
                            id: 'rdr_calculationMode',
                            name: 'rdr_calculationMode',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: new Ext.data.JsonStore({
                                fields: ['id', 'name'],
                                data: [{id: '1', name: 'Distance Rate'}, {id: '2', name: 'Rate'}]
                            }),
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'name',
                            valueField: 'id',
                            hiddenName: 'rdr_calculationMode',
                            tabIndex: 2,
                            listeners: {
                                select: function (combo, record) {
                                    var type = record.data.id;
                                    if (type == 2) {
                                        Ext.getCmp('fixedRate').show();
                                        Ext.getCmp('rdr_isFreeDelivery').show();
                                        Ext.getCmp('distanceRate1').hide();
                                        Ext.getCmp('distanceAdd').hide();
                                        Ext.getCmp('rdr_fromkm1').allowBlank = true;
                                        Ext.getCmp('rdr_tokm1').allowBlank = true;
                                        Ext.getCmp('rdr_amt1').allowBlank = true
                                        if (Application.DeliveryRules.Cache.aspLevel == 2) {
                                            Ext.getCmp('distanceRate2').hide();
                                            Ext.getCmp('rdr_fromkm2').allowBlank = true;
                                            Ext.getCmp('rdr_tokm2').allowBlank = true;
                                            Ext.getCmp('rdr_amt2').allowBlank = true
                                        } else if (Application.DeliveryRules.Cache.aspLevel >= 3) {
                                            Ext.getCmp('distanceRate2').hide();
                                            Ext.getCmp('rdr_fromkm2').allowBlank = true;
                                            Ext.getCmp('rdr_tokm2').allowBlank = true;
                                            Ext.getCmp('rdr_amt2').allowBlank = true
                                            Ext.getCmp('distanceRate3').hide();
                                            Ext.getCmp('rdr_fromkm3').allowBlank = true;
                                            Ext.getCmp('rdr_tokm3').allowBlank = true;
                                            Ext.getCmp('rdr_amt3').allowBlank = true
                                        }
                                        Ext.getCmp('rdr_fromkm1').allowBlank = true;
                                        Ext.getCmp('rdr_tokm1').allowBlank = true;
                                        Ext.getCmp('rdr_amt1').allowBlank = true
                                    } else if (type == 1) {
                                        Ext.getCmp('fixedRate').hide();
                                        Ext.getCmp('rdr_isFreeDelivery').show();
                                        Ext.getCmp('distanceRate1').show();
                                        Ext.getCmp('rdr_fromkm1').allowBlank = false;
                                        Ext.getCmp('rdr_tokm1').allowBlank = false;
                                        Ext.getCmp('rdr_amt1').allowBlank = false
                                        if (Application.DeliveryRules.Cache.aspLevel == 2) {
                                            Ext.getCmp('distanceRate2').show();
                                            Ext.getCmp('rdr_fromkm2').allowBlank = false;
                                            Ext.getCmp('rdr_tokm2').allowBlank = false;
                                            Ext.getCmp('rdr_amt2').allowBlank = false;
                                        } else if (Application.DeliveryRules.Cache.aspLevel >= 3) {
                                            Ext.getCmp('distanceRate2').show();
                                            Ext.getCmp('rdr_fromkm2').allowBlank = false;
                                            Ext.getCmp('rdr_tokm2').allowBlank = false;
                                            Ext.getCmp('rdr_amt2').allowBlank = false;
                                            Ext.getCmp('distanceRate3').show();
                                            Ext.getCmp('rdr_fromkm3').allowBlank = false;
                                            Ext.getCmp('rdr_tokm3').allowBlank = false;
                                            Ext.getCmp('rdr_amt3').allowBlank = false;
                                        }
                                        if (Application.DeliveryRules.Cache.aspLevel >= 3) {
                                            Ext.getCmp('distanceAdd').hide()
                                        } else {
                                            Ext.getCmp('distanceAdd').show();
                                        }

                                        Ext.getCmp('rdr_fromkm1').allowBlank = false;
                                        Ext.getCmp('rdr_tokm1').allowBlank = false;
                                        Ext.getCmp('rdr_amt1').allowBlank = false;
                                    }
                                    else
                                    {
                                        Ext.getCmp('fixedRate').hide();
                                        Ext.getCmp('rdr_isFreeDelivery').hide();
                                        Ext.getCmp('distanceRate1').hide();
                                        Ext.getCmp('distanceAdd').hide();
                                        Ext.getCmp('rdr_fromkm1').allowBlank = true;
                                        Ext.getCmp('rdr_tokm1').allowBlank = true;
                                        Ext.getCmp('rdr_amt1').allowBlank = true
                                        Ext.getCmp('distanceRate2').hide();
                                        Ext.getCmp('rdr_fromkm2').allowBlank = true;
                                        Ext.getCmp('rdr_tokm2').allowBlank = true;
                                        Ext.getCmp('rdr_amt2').allowBlank = true;
                                        Ext.getCmp('distanceRate3').hide();
                                        Ext.getCmp('rdr_fromkm3').allowBlank = true;
                                        Ext.getCmp('rdr_tokm3').allowBlank = true;
                                        Ext.getCmp('rdr_amt3').allowBlank = true;
                                    }

                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 1,
                    labelAlign: 'left',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            labelAlign: 'top',
                            xtype: 'compositefield',
                            hideLabel: true,
                            hidden: true,
                            fieldLabel: ' ',
                            id: 'fixedRate',
                            border: false,
                            combineErrors: false,
                            items: [{
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 1px"},
                                    border: false,
                                    html: 'Rate / km'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_fixedRateperkm',
                                    name: 'rdr_fixedRateperkm',
                                    fieldLabel: 'Nos',
                                    tabIndex: 3,
                                    width: 187,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 10px"},
                                    border: false,
                                    html: 'Min. Rate'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_fixedRateMin',
                                    name: 'rdr_fixedRateMin',
                                    fieldLabel: 'Nos',
                                    tabIndex: 4,
                                    width: 184,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "6px 5px 5px 10px"},
                                    border: false,
                                    html: 'Max. Rate'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_fixedRateMax',
                                    name: 'rdr_fixedRateMax',
                                    fieldLabel: 'Nos',
                                    tabIndex: 5,
                                    width: 184,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }
                            ]
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 1,
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            labelAlign: 'top',
                            xtype: 'compositefield',
                            hideLabel: true,
                            fieldLabel: ' ',
                            id: 'distanceRate1',
                            hidden: true,
                            combineErrors: false,
                            items: [{
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 1px"},
                                    border: false,
                                    html: 'From'

                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_fromkm1',
                                    name: 'rdr_fromkm1',
                                    fieldLabel: 'Nos',
                                    tabIndex: 6,
                                    width: 212,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 9px"},
                                    border: false,
                                    html: 'km To'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_tokm1',
                                    name: 'rdr_tokm1',
                                    fieldLabel: 'Nos',
                                    tabIndex: 7,
                                    width: 201,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 5px"},
                                    border: false,
                                    html: 'km Rs'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_amt1',
                                    name: 'rdr_amt1',
                                    fieldLabel: 'Nos',
                                    tabIndex: 8,
                                    width: 208,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            labelAlign: 'top',
                            xtype: 'compositefield',
                            hideLabel: true,
                            hidden: true,
                            fieldLabel: ' ',
                            id: 'distanceRate2',
                            combineErrors: false,
                            bodyStyle: {"background-color": "F1F1F1"},
                            items: [{
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 1px"},
                                    border: false,
                                    html: 'From'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_fromkm2',
                                    name: 'rdr_fromkm2',
                                    fieldLabel: 'Nos',
                                    width: 212,
                                    //tabIndex: 9,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 9px"},
                                    border: false,
                                    html: 'km To'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_tokm2',
                                    name: 'rdr_tokm2',
                                    fieldLabel: 'Nos',
                                    width: 201,
                                    //tabIndex: 10,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 5px"},
                                    border: false,
                                    html: 'km Rs'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_amt2',
                                    name: 'rdr_amt2',
                                    fieldLabel: 'Nos',
                                    tabIndex: 9,
                                    width: 208,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            labelAlign: 'top',
                            xtype: 'compositefield',
                            hideLabel: true,
                            fieldLabel: ' ',
                            hidden: true,
                            id: 'distanceRate3',
                            combineErrors: false,
                            //bodyStyle: {"background-color": "F1F1F1"},
                            items: [{
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 1px"},
                                    border: false,
                                    html: 'From'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_fromkm3',
                                    name: 'rdr_fromkm3',
                                    fieldLabel: 'Nos',
                                    tabIndex: 10,
                                    width: 212,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 9px"},
                                    border: false,
                                    html: 'km To'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_tokm3',
                                    name: 'rdr_tokm3',
                                    fieldLabel: 'Nos',
                                    tabIndex: 11,
                                    width: 201,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 5px"},
                                    border: false,
                                    html: 'km Rs'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rdr_amt3',
                                    name: 'rdr_amt3',
                                    fieldLabel: 'Nos',
                                    tabIndex: 12,
                                    width: 208,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }
                            ]
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: .93,
                    labelAlign: 'left',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'spacer',
                            height: 5
                        }]

                },
                {
                    layout: 'form',
                    columnWidth: .07,
                    labelAlign: 'left',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [
                        {
                            id: 'distanceAdd',
                            xtype: 'button',
                            hidden: true,
                            text: 'Add',
                            align: 'right',
                            tabIndex: 13,
                            iconCls: 'finascop_add',
                            style: 'margin-top:2px;',
                            handler: function () {
                                Application.DeliveryRules.Cache.aspLevel++;
                                if (Application.DeliveryRules.Cache.aspLevel <= 3) {
                                    Ext.getCmp('distanceRate' + Application.DeliveryRules.Cache.aspLevel).show();

                                }
                                if (Application.DeliveryRules.Cache.aspLevel >= 3) {
                                    Ext.getCmp('distanceAdd').hide()
                                }

                            }
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 1,
                    labelAlign: 'left',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [
                        {
                            labelAlign: 'top',
                            xtype: 'compositefield',
                            id: 'rdr_isFreeDelivery',
                            name: 'rdr_isFreeDelivery',
                            hidden: true,
                            hideLabel: true,
                            combineErrors: false,
                            border: false,
                            items: [{
                                    xtype: 'checkbox',
                                    checked: false,
                                    hideLabel: true,
                                    style: {
                                        marginRight: '1px'
                                    },
                                    id: 'rdr_isfreeDeliveryCbx',
                                    name: 'rdr_isfreeDeliveryCbx',
                                    inputValue: 1,
                                    tabIndex: 14
                                }, {
                                    bodyStyle: {"background-color": "F1F1F1", "padding": "5px 5px 5px 0px"},
                                    border: false,
                                    html: 'Free Delivery',
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: '-',
                                    hideLabel: true,
                                    emptyText: ' Above Rs',
                                    width: 146,
                                    id: 'rdr_isfreeDeliveryAmt',
                                    name: 'rdr_isfreeDeliveryAmt',
                                    labelAlign: 'left',
                                    anchor: '97%',
                                    tabIndex: 15
                                }]
                        }
                    ]
                },
                {
                    layout: 'form',
                    columnWidth: 0.5,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Rule For',
                            emptyText: 'Choose Rule For',
                            id: 'rdr_ruleFor',
                            name: 'rdr_ruleFor',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: new Ext.data.JsonStore({
                                fields: ['id', 'name'],
                                data: [{id: '1', name: 'Common Rule'}, {id: '2', name: 'Store Group'}, {id: '3', name: 'Store'}]
                            }),
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'name',
                            valueField: 'id',
                            hiddenName: 'rdr_ruleFor',
                            tabIndex: 16,
                            listeners: {
                                select: function (combo, record) {
                                    var type = record.data.id;
                                    console.log('type', type);
                                    Ext.getCmp('rdr_ruleForId').reset();
                                    switch (type) {
                                        case '1':
                                            Ext.getCmp('rdr_ruleForId').allowBlank = true;
                                            break;
                                        case '2':
                                            Ext.getCmp('rdr_ruleForId').allowBlank = false;
                                            Ext.getCmp('rdr_ruleForId').getStore().load({
                                                params: {
                                                    type: 2
                                                }
                                            });
                                            break;
                                        case '3':
                                            Ext.getCmp('rdr_ruleForId').allowBlank = false;
                                            Ext.getCmp('rdr_ruleForId').getStore().load({
                                                params: {
                                                    type: 3
                                                }
                                            });
                                            break;
                                    }

                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.5,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'combo',
                            fieldLabel: 'Store / Group',
                            emptyText: 'Choose Store / Group',
                            id: 'rdr_ruleForId',
                            name: 'rdr_ruleForId',
                            mode: 'local',
                            typeAhead: true,
                            forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: storeOrGroupStore,
                            triggerAction: 'all',
                            minChars: 2,
                            displayField: 'name',
                            valueField: 'id',
                            hiddenName: 'rdr_ruleForId',
                            tabIndex: 17,
                            listeners: {
                                select: function () {

                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 1,
                    labelAlign: 'top',
                    border: false,
                    bodyStyle: {"background-color": "F1F1F1"},
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Rule Name',
                            emptyText: 'Rule Name',
                            id: 'rdr_ruleName',
                            name: 'rdr_ruleName',
                            labelAlign: 'left',
                            allowBlank: false,
                            anchor: '99%',
                            tabIndex: 18
                        }]
                }
            ],
            listeners: {
                afterrender: function () {

                }
            }
        });
        return _deliveryrulesFormPanel;
    };
    var DeliveryRuleMasterDetailsView = function () {
        var src = '?module=order_processing&op=order_details&order_auto_id=' + Application.OrderProcessing.Cache.order_auto_id;
        return new Ext.Panel({
            region: 'east',
            frame: false,
            border: true,
            layout: 'fit',
            autoScroll: false,
            bodyStyle: {"background-color": "white"},
            id: 'panelMasterDeliveryRulesDetailsView',
            width: winsize.width * 0.45,
            items: [{
                    id: 'details_view_panel_order',
                    hidden: true,
                    html: '<iframe id="iframe_productdtls" name="iframe_productdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                }]
        })
    };
    var DeliveryRulesMasterStore = function () {
        var _deliveryrulesMasterStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listDeliveryRules',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'rdr_id',
                root: 'data'
            }, ['rdr_id', 'rdr_ruleName', 'rdr_deliveryMode', 'rdr_calculationMode', 'rdr_ruleFor', 'rdr_deliveryModeName', 'rdr_calculationModeName', 'is_default', 'freeDelivery', 'rdr_isfreeDeliveryAmt', 'rdr_isfreeDelivery']),
            sortInfo: {
                field: 'rdr_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            remoteSort: true,
            autoLoad: false,
            root: 'data',
            listeners: {
                load: function () {
                    Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getView().refresh();
                    Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getSelectionModel().selectRow(0);
                }
            }
        });
        return _deliveryrulesMasterStore;
    };
    var DeliveryRuleMainGrid = function () {
        var _deliveryrulesStore = DeliveryRulesMasterStore();
        var _deliveryrulesGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'rdr_ruleName'
                }, {
                    type: 'list',
                    options: ['Common Rule', 'Store Group', 'Store'],
                    phpMode: true,
                    dataIndex: 'rdr_ruleFor'
                },
//                {
//                    type: 'string',
//                    dataIndex: 'rdr_calculationModeName'
//                },
//                {
//                    type: 'string',
//                    dataIndex: 'rdr_deliveryModeName'
//                },
                {
                    type: 'list',
                    options: ['Distance Rate', 'Flat Rate'],
                    phpMode: true,
                    dataIndex: 'rdr_calculationMode'
                },
                {
                    type: 'list',
                    options: ['Courier Delivery', 'Express Delivery', 'Scheduled Delivery'],
                    phpMode: true,
                    dataIndex: 'rdr_deliveryMode'
                }, {
                    type: 'string',
                    dataIndex: 'freeDelivery'
                }]
        });
        _deliveryrulesGridFilter.remote = true;
        _deliveryrulesGridFilter.autoReload = true;
        var _deliveryrulesmaingridPanel = new Ext.grid.GridPanel({
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            store: _deliveryrulesStore,
            iconCls: 'money',
            id: 'gridpanelMasterDataviewDeliveryRulesdata',
//            view: new Ext.grid.GroupingView({
//                forceFit: true,
//                deferEmptyText: false,
//                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
//                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
//            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _deliveryrulesGridFilter],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Rule Name',
                    dataIndex: 'rdr_ruleName',
                    sortable: true,
                    tooltip: 'Rule Name',
                    hideable: false
                },
                {
                    header: 'Rule Type',
                    dataIndex: 'rdr_ruleFor',
                    sortable: true,
                    tooltip: 'Rule Type'
                }, {
                    header: 'Delivery Mode',
                    dataIndex: 'rdr_deliveryMode',
                    sortable: true,
                    tooltip: 'Delivery Mode'
                }, {
                    header: 'Calculation Mode',
                    dataIndex: 'rdr_calculationMode',
                    sortable: true,
                    tooltip: 'Calculation Mode'
                }, {
                    header: 'Free Above Rs',
                    dataIndex: 'freeDelivery',
                    sortable: true,
                    tooltip: 'Free Above Rs'
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _isDefault = data.is_default;
                                if (_isDefault == 0) {
                                    this.items[0].tooltip = 'Set Default';
                                    return 'drinacitve';
                                }
                                else {
                                    this.items[0].tooltip = 'Clear Default';
                                    return 'dractive';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.getStore().getAt(rowIndex);
                                var rdr_ruleFor = record.get('rdr_ruleFor');
                                if (rdr_ruleFor == 'Common Rule') {
                                    Ext.Ajax.request({waitMsg: 'Processing',
                                        url: modURL,
                                        params: {
                                            op: 'setDRDefault',
                                            rdr_id: record.get('rdr_id'),
                                            rdr_deliveryMode: record.get('rdr_deliveryMode')
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        },
                                        success: function (response, options) {
                                            eval("var tmp=" + response.responseText);
                                            if (tmp.success === true) {
                                                Application.example.msg('Notification', tmp.msg);
                                                Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getStore().reload();
                                            }
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.alert('Notification', "Rule type of Delivery rule should be Common Rule to make it default.");
                                }

                            }
                        }
                    ]
                }
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('is_default') == 1)
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
                    } else {
                        return '';
                    }
                },
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            bbar: new Ext.PagingToolbar({
                pageSize: RECS_PER_PAGE,
                store: _deliveryrulesStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: gridSelectionChangeddeliveryrules
                }
            }),
            listeners: {
                cellClick: function (grid, rowIndex, columnIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    var ID = record.get('rdr_id');
                    if (!Ext.isEmpty(ID)) {
                        Application.DeliveryRules.Cache.rdr_id = ID;
                        Application.DeliveryRules.ViewDeliveryRules(ID);
                    }
                },
                resize: onGridResize,
                afterrender: function () {
                    _deliveryrulesStore.load();
                }
            },
            tbar: [{
                    text: 'Create Delivery Rule',
                    tooltip: 'Create Delivery Delivery Rule',
                    icon: './resources/images/submenuicons/add.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        Application.DeliveryRules.Cache.aspLevel = 1;
                        Application.DeliveryRules.addDRules();
                    }
                }]
        });
        return _deliveryrulesmaingridPanel;
    };

    var saveDeliveryRules = function () {
        var ptId = Ext.getCmp('rdr_id').getValue();
        if (!Ext.isEmpty(Ext.getCmp('rdr_name').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('comboMasterDeliveryRulesStatus').getValue())) {
            Ext.Ajax.request({
                url: modURL + '&op=saveDeliveryRules',
                method: 'POST',
                params: {
                    id: Ext.getCmp('rdr_id').getValue(),
                    name: Ext.getCmp('rdr_name').getValue(),
                    status: Ext.getCmp('comboMasterDeliveryRulesStatus').getValue()

                },
                success: function (response) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true && tmp.valid === true) {
                        Application.example.msg('Success', tmp.message);
                        if (Application.DeliveryRules.DeliveryRulesAddEdit == 'Add') {
                            RECS_PER_PAGE = updateRecsPerPage(Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata'));
                            Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').store.reload({
                                params: {
                                    start: 0,
                                    limit: RECS_PER_PAGE
                                }
                            });
                        } else {
                            Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getStore().load(
                                    {
                                        callback: function (record, options, success) {
                                            var gridPanel = Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata');
                                            var index = gridPanel.store.find('rdr_id', ptId);
                                            gridPanel.getSelectionModel().selectRow(index);
                                        }
                                    }
                            );


//                            Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').selModel.getSelected().data = tmp.data;
//                            Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getStore().reload();
//                            Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getView().refresh();
                        }
                        Application.DeliveryRules.DeliveryRulesAddEdit = '';
                        Application.DeliveryRules.ViewDeliveryRules(tmp.data.rdr_id);
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
    return{
        Cache: {},
        initDeliveryRule: function () {
            var _businesstypePanelId = 'panelMasterMainDeliveryRule';
            var _masterPanelDeliveryRule = Ext.getCmp(_businesstypePanelId);
            if (Ext.isEmpty(_masterPanelDeliveryRule)) {
                _masterPanelDeliveryRule = masterPanelforDeliveryRule(_businesstypePanelId);
                Application.UI.addTab(_masterPanelDeliveryRule);
                _masterPanelDeliveryRule.doLayout();
            } else {
                Application.UI.addTab(_masterPanelDeliveryRule);
            }
        }, ViewDeliveryRules: function () {
            var rdr_id = arguments[0];
            Ext.getCmp('panelMasterDeliveryRuleParent').doLayout();
            Ext.getCmp('panelMasterDeliveryRuleParent').setTitle("View Delivery Rule Details");
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.getCmp('details_view_panel_order').show();

            Ext.get('iframe_productdtls').dom.src = modURL + '&op=dr_details&rdr_id=' + rdr_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;
        },
        EditDeliveryRulesView: function (rdrId) {
            Application.DeliveryRules.addDRules(rdrId);
        }, addDRules: function (poId) {
            var deliveryChargeForm = DeliveryRuleForm(poId);
            var deliveryChargeWindow = new Ext.Window({
                id: "windowFinascopStocknewPoView",
                title: 'Create Delivery Rule',
                shadow: false,
                height: 520,
                width: 800,
                modal: true,
                layout: 'fit',
                autoHeight: true,
                resizable: false,
                closable: true,
                items: [deliveryChargeForm],
                buttons: [{
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        text: 'Cancel',
                        tabIndex: 19,
                        handler: function () {
                            Ext.getCmp('windowFinascopStocknewPoView').close();
                        }
                    },
                    {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            var store_form = Ext.getCmp('formpanelMasterDeliveryRules').getForm();
                            if (store_form.isValid()) {
                                store_form.submit({
                                    url: modURL,
                                    waitMsg: 'Saving Details....',
                                    waitTitle: 'Please Wait...',
                                    params: {
                                        op: 'saveDeliveryRules',
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp,
                                    },
                                    success: function (response, action) {
                                        var tmp = Ext.decode(action.response.responseText);
                                        if (tmp.success === true) {
                                            Ext.getCmp('windowFinascopStocknewPoView').close();
                                            Application.example.msg('Success', tmp.msg);
                                            Ext.getCmp('gridpanelMasterDataviewDeliveryRulesdata').getStore().load();


                                        } else if (tmp.success === false) {
                                            Ext.Msg.alert("Notification.", tmp.msg);
                                            Ext.getCmp('windowFinascopStocknewPoView').close();
                                        } else {
                                            Ext.Msg.alert("Error", tmp.msg);
                                            Ext.getCmp('windowFinascopStocknewPoView').close();
                                        }
                                    },
                                    failure: function (elm, conf, action) {
                                        if (conf.failureType === 'server') {
                                            var result = Ext.decode(conf.response.responseText);
                                            console.log('result', result);
                                            Ext.Msg.alert('Error', result.error);
                                        } else {
                                            Ext.MessageBox.alert('Error', 'Check the required fields');
                                        }
                                    }
                                });
                            } else {
                                Ext.MessageBox.alert('Notification', 'Check the required fields.');
                            }

                        }
                    }
                ]
            });
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            if (!Ext.isEmpty(arguments[0])) {
                var deliveryrulesForm = Ext.getCmp('formpanelMasterDeliveryRules').getForm();
                Ext.Ajax.request({
                    params: {
                        rdr_id: arguments[0],
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=deliveryrules_form_load',
                    waitMsg: 'Loading...',
                    method: 'POST',
                    success: function (res) {
                        var tmp = Ext.decode(res.responseText);
                        Ext.getCmp('rdr_ruleName').setValue(tmp.rdr_ruleName);
                        Ext.getCmp('rdr_id').setValue(tmp.rdr_id);
                        Ext.getCmp('rdr_deliveryMode').setValue(tmp.rdr_deliveryMode);
                        Ext.getCmp('rdr_calculationMode').setValue(tmp.rdr_calculationMode);
                        Ext.getCmp('rdr_ruleFor').setValue(tmp.rdr_ruleFor);

                        Ext.getCmp('rdr_fixedRateperkm').setValue(tmp.rdr_fixedRateperkm);
                        Ext.getCmp('rdr_fixedRateMin').setValue(tmp.rdr_fixedRateMin);
                        Ext.getCmp('rdr_fixedRateMax').setValue(tmp.rdr_fixedRateMax);

                        Ext.getCmp('rdr_fromkm1').setValue(tmp.rdr_fromkm1);
                        Ext.getCmp('rdr_tokm1').setValue(tmp.rdr_tokm1);
                        Ext.getCmp('rdr_amt1').setValue(tmp.rdr_amt1);

                        Ext.getCmp('rdr_fromkm2').setValue(tmp.rdr_fromkm2);
                        Ext.getCmp('rdr_tokm2').setValue(tmp.rdr_tokm2);
                        Ext.getCmp('rdr_amt2').setValue(tmp.rdr_amt2);

                        Ext.getCmp('rdr_fromkm3').setValue(tmp.rdr_fromkm3);
                        Ext.getCmp('rdr_tokm3').setValue(tmp.rdr_tokm3);
                        Ext.getCmp('rdr_amt3').setValue(tmp.rdr_amt3);
                        if (tmp.rdr_isfreeDelivery == 1) {
                            Ext.getCmp('rdr_isfreeDelivery').setValue(true);
                        }
                        Ext.getCmp('rdr_isfreeDeliveryAmt').setValue(tmp.rdr_isfreeDeliveryAmt);
                        Ext.getCmp('rdr_ruleFor').setValue(tmp.rdr_ruleFor);
                        Ext.getCmp('rdr_ruleForId').setValue(tmp.rdr_ruleForId);
                        if (tmp.rdr_ruleFor == 2 || tmp.rdr_ruleFor == 3) {
                            Ext.getCmp('rdr_ruleForId').getStore().load({
                                params: {
                                    type: tmp.rdr_ruleFor
                                }
                            });
                        }

                        if (tmp.rdr_calculationMode == 2) {
                            Ext.getCmp('fixedRate').show();
                            Ext.getCmp('distanceRate1').hide();
                            Ext.getCmp('distanceAdd').hide();
                        } else if (tmp.rdr_calculationMode == 1) {
                            Ext.getCmp('distanceAdd').show();
                            Ext.getCmp('fixedRate').hide();
                            Ext.getCmp('distanceRate1').show();
                            if (tmp.rdr_amt2 > 0) {
                                Ext.getCmp('distanceRate2').show();
                            }
                            if (tmp.rdr_amt3 > 0) {
                                Ext.getCmp('distanceRate3').show();
                            }
                        } else {
                            Ext.getCmp('fixedRate').hide();
                            Ext.getCmp('distanceRate1').hide();
                            Ext.getCmp('distanceAdd').hide();
                        }
                    },
                    failure: function (form, action) {
                        Ext.Msg.alert("Error.", "This error");
                    }
                });
            }
            deliveryChargeWindow.doLayout();
            deliveryChargeWindow.show();
            deliveryChargeWindow.center();
        }
    };
}();