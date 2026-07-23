Application.MyphaRetail = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=mypha_retail';
    var modcsURL = '?module=mypha_centralstore';
    var my_marker;
    var recs_per_page = 12;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var totalComboCount;
    var myMask, retailStoreLoadedForm;

    var comboLoadComplete = function () {

        if (!Ext.isEmpty(retailStoreLoadedForm) && retailStoreLoadedForm != null) {
            retailStoreLoadedForm[0].loadRecord(Ext.decode(retailStoreLoadedForm[1]));
        }
        if (totalComboCount == 0) {
            myMask.hide();
            retailStoreLoadedForm = null;
        }
    };
    var changeBranchStatus = function () {

        Application.MyphaRetail.br_ID = arguments[0];
        Application.MyphaRetail.br_status = arguments[1];
        Application.MyphaRetail.comp_id = arguments[2];
        var form_data = {
            br_ID: Application.MyphaRetail.br_ID,
            br_status: Application.MyphaRetail.br_status,
            comp_id: Application.MyphaRetail.comp_id
        };
        var params = {
            action: 'Update',
            module: 'mypha_retail',
            op: 'changeStatus',
            extrainfo: 'asd',
            id: Application.MyphaRetail.br_ID

        };

        APICall(params, Application.MyphaRetail.changeStatus, form_data);
    };

    var retailtypesStoreJsonStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetailtypeStores',
                method: 'post'
            }),
            fields: ['mprt_id', 'mprt_Type'],
            remoteSort: true,
            autoLoad: true
        });
        store.setDefaultSort('mprt_Type', 'ASC');
        return store;
    };

    var brComboStore = function (ind) {
        return  new Ext.data.ArrayStore({
            url: modURL + '&op=getComboStore&ind=' + ind,
            method: 'POST',
            autoLoad: true,
            fields: ['id', 'name'],
            listeners: {
                load: function (store, rec, opt) {
                    if (store.totalLength > 0) { //totalLength == 1
                        if (ind == 3) {
                            Ext.getCmp('br_Company').setValue(store.getAt(0).get("id"));
                        }

                    }
                }
            }
        });
    };

    var retailStoreForm = function (map_lat, map_long, br_ID) {
        my_marker = [{
                lat: map_lat,
                lng: map_long,
                marker: {
                    title: "you are here",
                    draggable: false
                },
                listeners: {
//                    "onFailure": function () {
//                        Ext.MessageBox.alert('Failed locating city ');
//                    },
//                    "onSuccess": function (point) {
//
//                    },
                    "dragend": function (markerAt) {
                        Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                        Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                    }
                }
            }];
        var deliveryRuleStoreFnc = function (deliveryMode) {
            var deliveryRuleStore = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + '&op=storeForDeliveryRule',
                method: 'post',
                fields: ['id', 'name'],
                listeners: {
                    beforeload: function () {
                        this.baseParams.deliveryMode = deliveryMode;
                        this.baseParams.branchId = br_ID;
                    }

                }
            });
            return deliveryRuleStore;
        };

        var form = new Ext.FormPanel({
            labelAlign: 'top',
            autoHeight: true,
            url: modURL + "&op=saveRetailStore",
            width: 850,
            frame: true,
            id: 'retailstore_form',
            layout: 'column',
            items: [{
                    layout: 'form',
                    columnWidth: 0.45,
                    hideBorders: true,
                    defaults: {
                        xtype: 'textfield',
                        anchor: '95%',
                        //allowBlank: false,
                        //style: 'margin:10px 0 10px 0',
                        hideBorders: true
                    },
                    items: [{
                            xtype: 'combo',
                            store: brComboStore(3),
                            mode: 'remote',
                            id: 'br_Company',
                            fieldLabel: 'Company',
                            hiddenName: 'br_Company',
                            displayField: 'name',
                            valueField: 'id',
                            typeAhead: true,
                            allowBlank: false,
                            selectOnFocus: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            tabIndex: 1,
                            listeners: {
//                                        afterrender: function (field) {
//                                            Ext.defer(function () {
//                                                field.focus(true, 100);
//                                            }, 1);
//                                        }
                            }
                        }, {
                            xtype: 'hidden',
                            allowBlank: true,
                            id: 'br_ID',
                            name: 'br_ID'
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.7,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            fieldLabel: 'Store Name',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'br_Name',
                                            name: 'br_Name',
                                            allowBlank: false,
                                            maxLength: 250,
                                            tabIndex: 2,
                                            listeners: {
                                                afterrender: function (field) {
                                                    Ext.defer(function () {
                                                        field.focus(true, 100);
                                                    }, 1);
                                                }
                                            }
                                        }],
                                }, {
                                    columnWidth: 0.3,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Store Code',
                                            xtype: 'textfield',
                                            anchor: '98%',
                                            id: 'branch_shortname',
                                            name: 'branch_shortname',
                                            allowBlank: false,
                                            minLength: 4,
                                            maxLength: 4,
                                            tabIndex: 3
                                        }]
                                }]
                        },
                        {
                            fieldLabel: 'Address',
                            id: 'br_Address',
                            name: 'br_Address',
                            maxLength: 765,
                            height: 90,
                            xtype: 'textarea',
                            allowBlank: false,
                            tabIndex: 4
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.4,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            store: brComboStore(1),
                                            mode: 'local',
                                            id: 'br_State',
                                            name: 'br_State',
                                            anchor: '98%',
                                            fieldLabel: 'State',
                                            hiddenName: 'br_State',
                                            displayField: 'name',
                                            valueField: 'id',
                                            allowBlank: false,
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 5,
                                            listeners: {
                                                select: function () {
                                                    var dist = Ext.getCmp('br_District');
                                                    var dist_str = dist.getStore();
                                                    dist.reset();
                                                    dist_str.removeAll();
                                                    dist_str.baseParams = {
                                                        ind: 2,
                                                        state: this.getValue()
                                                    };
                                                    dist_str.load();
                                                }
                                            }
                                        }],
                                }, {
                                    columnWidth: 0.4,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            xtype: 'combo',
                                            store: brComboStore(2),
                                            mode: 'local',
                                            anchor: '98%',
                                            id: 'br_District',
                                            name: 'br_District',
                                            fieldLabel: 'District',
                                            hiddenName: 'br_District',
                                            displayField: 'name',
                                            valueField: 'id',
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            allowBlank: false,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 6
                                        }]
                                },
                                {
                                    columnWidth: 0.2,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Post Code',
                                            id: 'br_pincode',
                                            anchor: '98%',
                                            xtype: 'textfield',
                                            name: 'br_pincode',
                                            allowBlank: false,
                                            //vtype: 'phonespec',
                                            tabIndex: 7,
                                            listeners: {
                                                focus: function () {
                                                    if (!Ext.isEmpty(Ext.getCmp('br_pincode').getValue()))
                                                    {
                                                        Ext.getCmp('map_button1').enable();
                                                    }
                                                },
                                                change: function () {
                                                    var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                    Ext.getCmp('br_Lat').setValue(point.lat);
                                                    Ext.getCmp('br_Lng').setValue(point.lng);
//                                                    Ext.getCmp('branchgooglemap').zoomLevel = 12;
//                                                    Ext.getCmp('branchgooglemap').getMap().setCenter(point, 12);  
                                                    if (!Ext.isEmpty(Ext.getCmp('br_pincode').getValue())) {
                                                        Ext.getCmp('map_button1').enable();
                                                    }
                                                }
                                            }
                                        }]
                                }]
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            id: 'br_Incharge',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            allowBlank: false,
                                            fieldLabel: 'Contact Person',
                                            maxLength: 250,
                                            name: 'br_Incharge',
                                            tabIndex: 8
                                        }],
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Telephone',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'br_Phone',
                                            name: 'br_Phone',
                                            allowBlank: false,
                                            vtype: 'phonespec',
                                            tabIndex: 9
                                        }]
                                }]
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            fieldLabel: 'Email',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'br_Email',
                                            name: 'br_Email',
                                            allowBlank: false,
                                            vtype: 'email',
                                            tabIndex: 10
                                        }],
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Mobile',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'br_Fax',
                                            name: 'br_Fax',
                                            allowBlank: false,
                                            vtype: 'phonespec',
                                            minLength: 10,
                                            maxLength: 10,
                                            tabIndex: 11
                                        }]
                                }]
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 1,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [
                                        {
                                            xtype: 'radiogroup',
                                            anchor: '98%',
                                            mode: 'remote',
                                            allowBlank: false,
                                            forceSelection: true,
                                            //id:'br_StoreType',
                                            // name:'br_StoreType',
                                            // hiddenName:'br_StoreType',
                                            //typeAhead:true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            minChars: 2,
                                            tabIndex: 12,
                                            items: [{
                                                    boxLabel: 'Spoke',
                                                    id: 'br_RetailstoreType1',
                                                    name: 'br_StoreType',
                                                    inputValue: 'Owned',
                                                    checked: true,
                                                    listeners: {
                                                        check: function (rgp, checked) {
                                                            if (checked == true) {
                                                                Ext.getCmp('br_stockLevel').allowBlank = false;
                                                                Ext.getCmp('br_stockLevel').show();
                                                                Ext.getCmp('br_ranking').setValue(1);
                                                                Ext.getCmp('br_ranking').setHideTrigger(true);
                                                                Ext.getCmp('br_cpd').allowBlank = false;
                                                                Ext.getCmp('br_cpd').show();
                                                                Ext.getCmp('br_storeGroup').allowBlank = true;
                                                                Ext.getCmp('br_storeGroup').hide();
                                                                Ext.getCmp('br_RetailType').allowBlank = true;
                                                                Ext.getCmp('br_RetailType').hide();
                                                            }
                                                        }
                                                    }
                                                }, {
                                                    boxLabel: 'Franchise',
                                                    id: 'br_RetailstoreType2',
                                                    name: 'br_StoreType',
                                                    inputValue: 'Leased',
                                                    listeners: {
                                                        check: function (rgp, checked) {
                                                            if (checked == true) {
                                                                Ext.getCmp('br_storeGroup').allowBlank = true;
                                                                Ext.getCmp('br_storeGroup').hide();
                                                                Ext.getCmp('br_RetailType').allowBlank = true;
                                                                Ext.getCmp('br_RetailType').hide();
                                                                Ext.getCmp('br_stockLevel').allowBlank = false;
                                                                Ext.getCmp('br_stockLevel').show();
                                                                Ext.getCmp('br_cpd').allowBlank = false;
                                                                Ext.getCmp('br_cpd').show();
                                                                Ext.getCmp('br_ranking').setValue(1);
                                                                Ext.getCmp('br_ranking').setHideTrigger(true);
                                                            }
                                                        }
                                                    }
                                                }, {
                                                    boxLabel: 'Store',
                                                    id: 'br_RetailstoreType3',
                                                    name: 'br_StoreType',
                                                    inputValue: 'Dealer',
                                                    listeners: {
                                                        check: function (rgp, checked) {
                                                            if (checked == true) {
                                                                Ext.getCmp('br_stockLevel').allowBlank = true;
                                                                Ext.getCmp('br_stockLevel').hide();
                                                                Ext.getCmp('br_RetailType').allowBlank = true;
                                                                Ext.getCmp('br_RetailType').hide();
                                                                Ext.getCmp('br_cpd').allowBlank = true;
                                                                Ext.getCmp('br_cpd').hide();
                                                                Ext.getCmp('br_storeGroup').allowBlank = false;
                                                                Ext.getCmp('br_storeGroup').show();
                                                                Ext.getCmp('br_ranking').getStore().removeAt(Ext.getCmp('br_ranking').getStore().find('ptid', 1));
                                                            }

                                                        }
                                                    }
                                                }]
                                        }
                                    ],
                                }]
                        },
                        {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.3,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            xtype: 'combo',
                                            displayField: 'ptname',
                                            valueField: 'ptid',
                                            anchor: '98%',
                                            mode: 'local',
                                            allowBlank: false,
                                            id: 'br_ranking',
                                            name: 'br_ranking',
                                            hiddenName: 'br_ranking',
                                            forceSelection: true,
                                            fieldLabel: 'Ranking',
                                            emptyText: 'Ranking',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 13,
                                            store: new Ext.data.JsonStore({
                                                fields: ['ptid', 'ptname'],
                                                data: [{ptid: 1, ptname: '1'}, {ptid: 2, ptname: '2'}, {ptid: 3, ptname: '3'}]
                                            })
                                        }]
                                }, {
                                    columnWidth: 0.3,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [
                                        {
                                            xtype: 'combo',
                                            displayField: 'ptname',
                                            valueField: 'ptid',
                                            anchor: '98%',
                                            mode: 'local',
                                            allowBlank: false,
                                            id: 'br_stockLevel',
                                            name: 'br_stockLevel',
                                            hiddenName: 'br_stockLevel',
                                            forceSelection: true,
                                            fieldLabel: 'Stock Level',
                                            emptyText: 'Stock Level',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 13,
                                            store: new Ext.data.JsonStore({
                                                fields: ['ptid', 'ptname'],
                                                data: [{ptid: 1, ptname: 'Level 1'}, {ptid: 2, ptname: 'Level 2'}, {ptid: 3, ptname: 'Level 3'}]
                                            })
                                        }
                                    ],
                                },
                                {
                                    columnWidth: 0.4,
                                    layout: 'form',
                                    hidden: false,
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            xtype: 'combo',
                                            store: brComboStore(4),
                                            mode: 'local',
                                            id: 'br_cpd',
                                            name: 'br_cpd',
                                            anchor: '98%',
                                            fieldLabel: 'Parent Distributor',
                                            hiddenName: 'br_cpd',
                                            displayField: 'name',
                                            valueField: 'id',
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            forceSelection: true,
                                            allowBlank: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 14
                                        }]
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            xtype: 'combo',
                                            store: brComboStore(5),
                                            mode: 'remote',
                                            id: 'br_storeGroup',
                                            allowBlank: true,
                                            fieldLabel: 'Store Group',
                                            hiddenName: 'br_storeGroup',
                                            displayField: 'name',
                                            valueField: 'id',
                                            anchor: '95%',
                                            typeAhead: true,
                                            hidden: true,
                                            selectOnFocus: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            tabIndex: 15
                                        }]
                                }]
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Express Delivery Rule',
                                            emptyText: 'Choose Delivery Rule',
                                            id: 'br_rdrIdExpress',
                                            name: 'br_rdrIdExpress',
                                            hidden: true,
                                            labelStyle: mandatory_label,
                                            //allowBlank: false,
                                            mode: 'local',
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: deliveryRuleStoreFnc(2),
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'name',
                                            valueField: 'id',
                                            hiddenName: 'br_rdrIdExpress',
                                            tabIndex: 16,
                                            listeners: {
                                                select: function (combo, record) {


                                                }
                                            }
                                        }, {
                                            xtype: 'combo',
                                            displayField: 'mprt_Type',
                                            valueField: 'mprt_id',
                                            anchor: '98%',
                                            mode: 'remote',
                                            hidden: true,
                                            id: 'br_RetailType',
                                            name: 'br_RetailType',
                                            hiddenName: 'br_RetailType',
                                            forceSelection: true,
                                            fieldLabel: 'Retail Type',
                                            emptyText: 'Retail Type',
                                            typeAhead: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            editable: true,
                                            minChars: 2,
                                            tabIndex: 17,
                                            store: retailtypesStoreJsonStore()
                                        }
                                    ]

                                }, {
                                    layout: 'form',
                                    columnWidth: 0.5,
                                    labelAlign: 'top',
                                    border: false,
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Schedule Delivery Rule',
                                            emptyText: 'Choose Delivery Rule',
                                            id: 'br_rdrIdSlotted',
                                            name: 'br_rdrIdSlotted',
                                            mode: 'local',
                                            hidden: true,
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: deliveryRuleStoreFnc(3),
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'name',
                                            valueField: 'id',
                                            hiddenName: 'br_rdrIdSlotted',
                                            tabIndex: 18,
                                            listeners: {
                                                select: function () {

                                                }
                                            }
                                        }]
                                }
                            ]
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 0.5,
                                    labelAlign: 'top',
                                    border: false,
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Courier Delivery Rule',
                                            emptyText: 'Choose Delivery Rule',
                                            id: 'br_rdrIdCourier',
                                            name: 'br_rdrIdCourier',
                                            mode: 'local',
                                            hidden: true,
                                            typeAhead: true,
                                            forceSelection: true,
                                            editable: true,
                                            anchor: '97%',
                                            store: deliveryRuleStoreFnc(1),
                                            triggerAction: 'all',
                                            minChars: 2,
                                            displayField: 'name',
                                            valueField: 'id',
                                            hiddenName: 'br_rdrIdCourier',
                                            tabIndex: 18,
                                            listeners: {
                                                select: function () {

                                                }
                                            }
                                        }]
                                }]
                        }, {
                            xtype: 'checkbox',
                            id: 'id_apibranch',
                            style: 'margin:0px 0px 0px 0px; padding:0px 0px 0px 0px;',
                            name: 'br_defaultapi',
                            tabIndex: 19,
                            allowBlank: true,
                            hidden: true,
                            boxLabel: 'Default Retail Store',
                            listeners: {
                                check: function (checkbox, checked) {
                                    if (checked == true) {
                                        Ext.getCmp('apibranch').setValue('1');
                                    } else {
                                        Ext.getCmp('apibranch').setValue('0');
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'hidden',
                            id: 'apibranch',
                            allowBlank: true,
                            name: 'br_defaultapibranch',
                            value: 0
                        },
                        {
                            xtype: 'hidden',
                            allowBlank: true,
                            id: 'br_PyramidLevel',
                            name: 'br_PyramidLevel',
                            value: 4
                        },
                        /*{
                         xtype: 'hidden',
                         id: 'br_IsCPD',
                         name: 'br_IsCPD',
                         allowBlank:true,
                         value: 1
                         },*/
                        /*{
                         columnWidth: .3,
                         xtype:'hidden',
                         id:'new',
                         layout: 'form',
                         labelAlign: 'top',
                         hidden: true,
                         hideBorders: true,
                         items: [{
                         xtype: 'checkbox',
                         id: 'branchIsCPD',
                         hidden: true,
                         hiddenName: 'branchIsCPD',
                         name: 'branchIsCPD',
                         allowBlank:true,
                         boxLabel: 'Is Retail Store',
                         listeners: {
                         check: function (checkbox, checked) {
                         if (checked == true) {
                         Ext.getCmp('br_IsCPD').setValue(1);
                         Ext.getCmp('br_cpd').hide();
                         } else {
                         Ext.getCmp('br_IsCPD').setValue(0);
                         Ext.getCmp('br_cpd').show();
                         }
                         }
                         }
                         }]
                         },*/
                        {
                            xtype: 'checkbox',
                            hidden: true,
                            id: 'br_sales',
                            name: 'br_sales',
                            boxLabel: 'Sales',
                            allowBlank: true,
                            checked: true,
                            inputValue: 1,
                            listeners: {
                                check: function (checkbox, checked) {
                                }
                            }
                        }]
                },
                {
                    layout: 'form',
                    columnWidth: 0.55,
                    title: 'MAP',
                    region: 'center',
                    items: [{
                            xtype: 'gmappanel',
                            gmapType: 'map',
                            id: 'branchgooglemap',
                            zoomLevel: 8,
                            height: 340,
                            minGeoAccuracy: 4,
                            scaleControl: true,
                            mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
                            mapControls: ['GSmallMapControl', 'GMapTypeControl'],
                            setCenter: {
                                lat: map_lat,
                                lng: map_long
                            },
                            repaint: function (zoomlevel) {
                                var gmappanel = Ext.getCmp('branchgooglemap');
                                if (zoomlevel) {
                                    gmappanel.zoomLevel = zoomlevel;
                                    gmappanel.getMap().setZoom(zoomlevel);
                                }
                                gmappanel.onMapReady();

                            },
                            markers: my_marker
                        },
                        {
                            layout: 'form',
                            columnWidth: .5,
                            //style: 'margin-left:5px;',
                            items: [{
                                    xtype: 'fieldset',
                                    title: 'Map Coordinates',
                                    //height: 200,
                                    autoHeight: true,
                                    items: [{
                                            layout: 'column',
                                            items: [{
                                                    layout: 'form',
                                                    columnWidth: .3,
                                                    items: [{
                                                            xtype: 'button',
                                                            fieldLabel: 'Coordinates',
                                                            tooltip: 'Locate latitude and longitude',
                                                            icon: IMAGE_BASE_PATH + "/default/icons/map.png",
                                                            iconCls: 'my-icon1',
                                                            id: 'map_button1',
                                                            name: 'map_button1',
                                                            disabled: true,
                                                            text: 'Find Coordinates',
                                                            tabIndex: 20,
                                                            handler: function () {
                                                                Ext.getCmp('branchgooglemap').clearMarkers();
                                                                var my_marker = [];
                                                                my_marker.push({
                                                                    geoCodeAddr: Ext.getCmp("br_pincode").getValue(),
                                                                    setCenter: true,
                                                                    marker: {
                                                                        title: "Click and Drag to Move Around",
                                                                        draggable: true
                                                                    },
                                                                    listeners: {
                                                                        onFailure: function () {

                                                                        },
                                                                        "tilesloaded": function (markerAt) {
                                                                            Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                                                                            Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                                                                        },
                                                                        onSuccess: function (point) {
                                                                            Ext.getCmp('br_Lat').setValue(point.latLng.lat());
                                                                            Ext.getCmp('br_Lng').setValue(point.latLng.lng());
                                                                        },
                                                                        "dragend": function (markerAt) {
                                                                            Ext.getCmp('br_Lat').setValue(markerAt.latLng.lat());
                                                                            Ext.getCmp('br_Lng').setValue(markerAt.latLng.lng());
                                                                        }
                                                                    },
                                                                    icon: null
                                                                });

                                                                Ext.getCmp('branchgooglemap').addScaleControl();
                                                                Ext.getCmp('branchgooglemap').clearMarkers();
                                                                //Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                Ext.defer(function () {
                                                                    var point = Ext.getCmp('branchgooglemap').getCenterLatLng();
                                                                    Ext.getCmp('br_Lat').setValue(point.lat);
                                                                    Ext.getCmp('br_Lng').setValue(point.lng);
                                                                    Ext.getCmp('branchgooglemap').clearMarkers();
                                                                    Ext.getCmp('branchgooglemap').repaint(13);
                                                                    Ext.getCmp('branchgooglemap').addMarkers(my_marker);
                                                                }, 1200);

                                                            }
                                                        }]
                                                }, {
                                                    layout: 'form',
                                                    columnWidth: .35,
                                                    items: [{
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Latitude',
                                                            //style: 'padding-left:5px;',
                                                            allowBlank: false,
                                                            tabIndex: 21,
                                                            id: 'br_Lat',
                                                            name: 'br_Lat',
                                                            anchor: '95%'
                                                        }]
                                                }, {
                                                    layout: 'form',
                                                    columnWidth: .35,
                                                    items: [{
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Longitude',
                                                            allowBlank: false,
                                                            tabIndex: 22,
                                                            id: 'br_Lng',
                                                            name: 'br_Lng',
                                                            anchor: '95%'
                                                        }]
                                                }]
                                        }]
                                }]
                        }
                    ]
                },
                {
                    layout: 'form',
                    labelAlign: 'top',
                    columnWidth: 1,
                    items: [
                    ]

                }]

        });
        return form;
    };

    var retailStoreDetails = function () {

        var br_ID = arguments[0];
        var map_lat = arguments[1];
        var map_long = arguments[2];
        var win_id = "retailstore_details_window";

        var retailstore_details_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(retailstore_details_window)) {
            var retailStore_form = retailStoreForm(map_lat, map_long, br_ID);
            retailstore_details_window = new Ext.Window({
                id: win_id,
                title: 'Create Retail Store',
                layout: 'fit',
                height: 535,
                width: 855,
                plain: true,
                modal: true,
                frame: true,
                resizable: false,
                items: retailStore_form,
                fbar: [{
                        text: 'Cancel',
                        tabIndex: 23,
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            retailstore_details_window.close();
                        }
                    }, {
                        text: 'Save',
                        tabIndex: 24,
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            if (retailStore_form.getForm().isValid()) {
                                var form_data = retailStore_form.getForm().getValues();
                                var params = {
                                    action: 'Insert',
                                    module: 'mypha_retailstore',
                                    op: 'saveRetailStore',
                                    id: '0',
                                    extrainfo: 'asd'
                                };
                                if (br_ID > 0) {
                                    params.action = 'Update';
                                    params.id = br_ID;
                                }
                                APICall(params, Application.MyphaRetail.saveRetailStore, form_data);

                            } else {
                                Ext.MessageBox.alert("Notification", "Please enter all required fields");
                            }
                        }
                    }],
                listeners: {
                    afterrender: function () {
                        if (_SESSION.UserId == 1) {
                            //  Ext.getCmp('branchIsCPD').show();
                        } else {
                            // Ext.getCmp('branchIsCPD').hide();
                        }
                        if (!Ext.isEmpty(br_ID) && br_ID > 0) {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");

                            myMask = new Ext.LoadMask(Ext.getCmp('retailstore_details_window').getEl(), {msg: "Please wait..."});
                            myMask.show();

                            retailStore_form.load({
                                url: modURL + '&op=getDetails',
                                params: {
                                    'id': br_ID,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                },
                                success: function (frm, action) {

                                    eval('var tmp=' + action.response.responseText);
                                    retailStoreLoadedForm = [frm, action.response.responseText];
                                    totalComboCount = 5;


                                    retailstore_details_window.setTitle("Edit Retail Store Details : " + Ext.getCmp("br_Name").getValue());
                                    var br_State = Ext.getCmp('br_State');


                                    br_State.getStore().load({
                                        params: {
                                            ind: 1
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });

                                    var br_District = Ext.getCmp('br_District');
                                    br_District.getStore().baseParams.state = br_State.getValue();

                                    br_District.getStore().load({
                                        params: {
                                            ind: 2
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });

                                    Ext.getCmp('br_Company').getStore().load({
                                        params: {
                                            ind: 3

                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        },
                                    });
                                    Ext.getCmp('br_Company').setHideTrigger(true);
                                    Ext.getCmp('br_Company').setReadOnly(true);
                                    /*var brIsCpd = Ext.getCmp('branchIsCPD').getValue();
                                     if (brIsCpd == 'on') {
                                     Ext.getCmp('br_IsCPD').setValue(1);
                                     }*/
                                    var br_Cpd = Ext.getCmp('br_cpd');
                                    br_Cpd.getStore().load({
                                        params: {
                                            ind: 4
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });
                                    var br_storeGroup = Ext.getCmp('br_storeGroup');
                                    br_storeGroup.getStore().load({
                                        params: {
                                            ind: 5
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });
                                    Ext.getCmp('br_rdrIdSlotted').getStore().load({
                                        params: {
                                            deliveryMode: tmp.data.br_rdrIdExpress
                                        }
                                    });
                                    switch (tmp.data.br_StoreType) {
                                        case 'Owned':
                                            Ext.getCmp('br_stockLevel').allowBlank = false;
                                            Ext.getCmp('br_stockLevel').show();
                                            Ext.getCmp('br_storeGroup').allowBlank = true;
                                            Ext.getCmp('br_storeGroup').hide();
                                            Ext.getCmp('br_cpd').allowBlank = false;
                                            Ext.getCmp('br_cpd').show();
                                            Ext.getCmp('br_ranking').setValue(1);
                                            Ext.getCmp('br_ranking').setHideTrigger(true);

                                            break;
                                        case 'Leased':
                                            Ext.getCmp('br_storeGroup').allowBlank = true;
                                            Ext.getCmp('br_storeGroup').hide();
                                            Ext.getCmp('br_stockLevel').allowBlank = false;
                                            Ext.getCmp('br_stockLevel').show();
                                            Ext.getCmp('br_cpd').allowBlank = false;
                                            Ext.getCmp('br_cpd').show();
                                            Ext.getCmp('br_ranking').setValue(1);
                                            Ext.getCmp('br_ranking').setHideTrigger(true);
                                            break;
                                        case 'Dealer':
                                            Ext.getCmp('br_stockLevel').allowBlank = true;
                                            Ext.getCmp('br_stockLevel').hide();
                                            Ext.getCmp('br_cpd').allowBlank = true;
                                            Ext.getCmp('br_cpd').hide();
                                            Ext.getCmp('br_storeGroup').allowBlank = false;
                                            Ext.getCmp('br_storeGroup').show();
                                            Ext.getCmp('br_ranking').getStore().removeAt(Ext.getCmp('br_ranking').getStore().find('ptid', 1));
                                            break;

                                    }


                                }
                            });
                        }
                    }
                }
            });
        }

        retailstore_details_window.doLayout();
        retailstore_details_window.show(this);
        retailstore_details_window.center();
    };

    var retailStoreJsonStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listRetailStores',
                method: 'post'
            }),
            fields: ['br_ID', 'br_Name', 'br_District', 'br_State', 'br_Address', 'br_Fax', 'br_csdefault', 'br_defCS', 'br_defDistributor', 'br_StoreType', 'br_ReferenceID', 'br_csdefault',
                'br_Email', 'br_Phone', 'br_Incharge', 'br_status', 'company', 'branch_shortname', 'comp_id', 'br_pincode', 'br_Lat', 'br_Lng', 'br_rdrIdExpress', 'br_rdrIdSlotted'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        store.setDefaultSort('br_Name', 'ASC');
        return store;
    };


    var createRetailStoreGrid = function (id) {
        var retailStore_jsonstore = retailStoreJsonStore();
        var retailStore_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'br_Name'
                }, {
                    type: 'string',
                    dataIndex: 'branch_shortname'
                }, {
                    type: 'string',
                    dataIndex: 'br_District'
                }, {
                    type: 'string',
                    dataIndex: 'br_State'
                }, {
                    type: 'string',
                    dataIndex: 'br_Fax'
                }, {
                    type: 'string',
                    dataIndex: 'br_Email'
                }, {
                    type: 'string',
                    dataIndex: 'br_Phone'
                }, {
                    type: 'string',
                    dataIndex: 'br_Incharge'
                }, {
                    type: 'string',
                    dataIndex: 'company'
                }, {
                    type: 'string',
                    dataIndex: 'br_StoreType'
                }]
        });
        retailStore_filter.remote = true;
        retailStore_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: retailStore_jsonstore,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Retail Stores',
            plugins: [retailStore_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Store Name',
                    id: 'retailStore_name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'br_Name',
                    tooltip: 'Store Name'
                },
                {
                    header: 'Store Short Name',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'branch_shortname',
                    tooltip: 'Store Short Name'
                }, {
                    header: 'Store Type',
                    sortable: true,
                    dataIndex: 'br_StoreType',
                    tooltip: 'Store Type'
                },
                {
                    header: 'Company',
                    sortable: true,
                    dataIndex: 'company',
                    tooltip: 'Company'
                },
                {
                    header: 'District',
                    sortable: true,
                    dataIndex: 'br_District',
                    tooltip: 'District'
                },
                {
                    header: 'State',
                    sortable: true,
                    dataIndex: 'br_State',
                    tooltip: 'State'
                },
                {
                    header: 'Smart Phone',
                    sortable: true,
                    dataIndex: 'br_Fax',
                    tooltip: 'Smart Phone'
                },
                {
                    header: 'Email',
                    sortable: true,
                    dataIndex: 'br_Email',
                    tooltip: 'Email'
                },
                {
                    header: 'Contact No.',
                    sortable: true,
                    dataIndex: 'br_Phone',
                    tooltip: 'Contact No.'
                },
                {
                    header: 'Incharge',
                    sortable: true,
                    dataIndex: 'br_Incharge',
                    tooltip: 'Incharge'
                },
                {
                    header: 'Default CS',
                    sortable: true,
                    dataIndex: 'br_defCS',
                    tooltip: 'Default Central Store'
                },
                {
                    header: 'Default Distributor',
                    sortable: true,
                    dataIndex: 'br_defDistributor',
                    tooltip: 'Default Distributor'
                }, {
                    header: 'Status',
                    id: 'br_status',
                    sortable: true,
                    dataIndex: 'br_status'
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
                            var br_StoreType = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_StoreType;
                            //var br_csdefault = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_csdefault;
                            var br_rdrIdSlotted = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_rdrIdSlotted;
                            var br_status = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_status;
                            if (br_status == 'Inactive')
                                retailStoreActionMenu2(e, br_StoreType, br_rdrIdSlotted);
                            else
                                retailStoreActionMenu1(e, br_StoreType, br_rdrIdSlotted);
                            //action
                        }
                    }
                }
                /*{
                 xtype: 'actioncolumn',
                 header: 'Action',
                 hideable: false,
                 sortable: false,
                 groupable: false,
                 tooltip: 'Action',
                 items: [{
                 iconCls: 'edit',
                 tooltip: 'Edit Retail Store Details',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 var map_Lat = record.get('br_Lat');
                 var map_Long = record.get('br_Lng');
                 retailStoreDetails(record.get('br_ID'), map_Lat, map_Long);
                 
                 
                 }
                 },
                 {
                 getClass: function (v, meta, rec) {
                 if (rec.get('br_status') == 'Active') {
                 this.items[1].tooltip = 'Deactivate Retail Store';
                 return 'now_active';
                 } else {
                 this.items[1].tooltip = 'Activate RetailStore';
                 return 'now_inactive';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Retail Store?', function (btn, text) {
                 if (btn == 'yes') {
                 changeBranchStatus(record.get('br_ID'), record.get('br_status'), record.get('comp_id'));
                 }
                 });
                 }
                 }, 
                 {
                 getClass: function (v, meta, rec) {
                 var data = rec.data;
                 var _isDefault = data.br_csdefault;
                 if (_isDefault == 0) {
                 this.items[2].tooltip = 'Set Default';
                 return 'hideicon';
                 }
                 else {
                 return 'user_enabled';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex, itm, evn) {
                 var record = grid.getStore().getAt(rowIndex);
                 //                                Ext.Ajax.request({waitMsg: 'Processing',
                 //                                    url: modcsURL,
                 //                                    params: {
                 //                                        op: 'setCSDefault',
                 //                                        br_ID: record.get('br_ID'),
                 //                                        pyramid: 4
                 //                                    },
                 //                                    failure: function (response, options) {
                 //                                        Ext.MessageBox.alert('Notification', ACTION_FAIL);
                 //                                    },
                 //                                    success: function (response, options) {
                 //                                        eval("var tmp=" + response.responseText);
                 //                                        if (tmp.success === true) {
                 //                                            Application.example.msg('Notification', tmp.msg);
                 //                                            Ext.getCmp(id).getStore().reload();
                 //                                        }
                 //                                    }
                 //                                });
                 }
                 }, 
                 {
                 getClass: function (v, meta, rec) {
                 var data = rec.data;
                 var _isDefault = data.br_StoreType;
                 if (_isDefault == 'Dealer') {
                 this.items[3].tooltip = 'API Key';
                 return 'apikey';
                 }
                 else {
                 return 'hideicon';
                 }
                 },
                 iconCls: 'apikey',
                 tooltip: 'API Key ',
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 editAPIKey(record.get('br_ReferenceID'), record.get('br_ID'));
                 }
                 }, 
                 {
                 getClass: function (v, meta, rec) {
                 var data = rec.data;
                 var br_rdrIdSlotted = data.br_rdrIdSlotted;
                 if (br_rdrIdSlotted > 0) {
                 this.items[3].tooltip = 'Slot';
                 return 'my-icon32';
                 }
                 else {
                 return 'hideicon';
                 }
                 },
                 handler: function (grid, rowIndex, colIndex) {
                 var record = grid.store.getAt(rowIndex);
                 Application.RetalinePincodeGroup.addTimeRangeForBranch(record.get('br_ID'));
                 }
                 }]
                 }*/
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('br_csdefault') == 1)
                    {
                        return '';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            listeners: {
                viewready: updatePagination
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Retail Store',
                    tooltip: 'Create Retail Store ',
                    iconCls: 'add',
                    handler: function () {
                        retailStoreDetails(0, 8.507007481504532, 76.95167541503906);
                    }
                }, {
                    xtype: 'button',
                    text: 'Set Default Retail Store',
                    tooltip: 'Set Default Retail Store',
                    hidden: true,
                    iconCls: 'add',
                    handler: function () {
                        setDefaultRetailor(id);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: retailStore_jsonstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [retailStore_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'retailStore_name'
        });
        return grid_panel;
    };
    var retailStoreActionMenu1 = function (e, br_StoreType, br_rdrIdSlotted) {
        var retailStoreActionMenu1 = new Ext.menu.Menu({
            items: [{
                    text: "Edit",
                    handler: function () {
                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        var br_Lat = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_Lat;
                        var br_Lng = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_Lng;
                        retailStoreDetails(br_ID, br_Lat, br_Lng);
                    }
                }, {
                    text: "Set Delivery Rule",
                    handler: function () {
                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        setDeliveryRulesWindow(br_ID);
                    }
                }, {
                    text: "Deactivate",
                    handler: function () {
                        //var record = grid.store.getAt(rowIndex);
                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        var br_status = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_status;
                        var comp_id = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.comp_id;
                        Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Central Store?', function (btn, text) {
                            if (btn == 'yes')
                            {
                                changeBranchStatus(br_ID, br_status, comp_id);
                            }
                        });
                    }
                }, {
                    text: "API",
                    hidden: !(br_StoreType == 'Dealer'),
                    handler: function () {
                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        var br_ReferenceID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ReferenceID;
                        editAPIKey(br_ReferenceID, br_ID);
                    }
                },
//            {
//                text: "Set Default",
//                hidden: !(br_csdefault == 0),
//                handler: function () {
//                   
//                }
//            },  
                {
                    text: "Time Range",
                    hidden: !(br_rdrIdSlotted > 0),
                    handler: function () {

                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        Application.RetalinePincodeGroup.addTimeRangeForBranch(br_ID);
                    }
                }]
        });
        retailStoreActionMenu1.showAt(e.getXY());
    };
    var retailStoreActionMenu2 = function (e, br_StoreType, br_rdrIdSlotted) {
        var retailStoreActionMenu2 = new Ext.menu.Menu({
            items: [{
                    text: "Edit",
                    handler: function () {
                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        var br_Lat = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_Lat;
                        var br_Lng = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_Lng;
                        retailStoreDetails(br_ID, br_Lat, br_Lng);
                    }
                }, {
                    text: "Set Delivery Rule",
                    handler: function () {
                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        setDeliveryRulesWindow(br_ID);
                    }
                }, {
                    text: "Activate",
                    handler: function () {
                        //var record = grid.store.getAt(rowIndex);
                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        var br_status = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_status;
                        var comp_id = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.comp_id;
                        Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Central Store?', function (btn, text) {
                            if (btn == 'yes')
                            {
                                changeBranchStatus(br_ID, br_status, comp_id);
                            }
                        });
                    }
                }, {
                    text: "API",
                    hidden: !(br_StoreType == 'Dealer'),
                    handler: function () {
                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        var br_ReferenceID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ReferenceID;
                        editAPIKey(br_ReferenceID, br_ID);
                    }
                },
//            {
//                text: "Set Default",
//                hidden: !(br_csdefault == 0),
//                handler: function () {
//                    
//                }
//            },  
                {
                    text: "Time Range",
                    hidden: !(br_rdrIdSlotted > 0),
                    handler: function () {

                        var br_ID = Ext.getCmp('panelRetailStore').getSelectionModel().getSelections()[0].data.br_ID;
                        Application.RetalinePincodeGroup.addTimeRangeForBranch(br_ID);
                    }
                }]
        });
        retailStoreActionMenu2.showAt(e.getXY());
    };
    var setDeliveryRulesWindow = function (br_ID) {
        var deliveryRuleStoreFnc = function (deliveryMode) {
            var deliveryRuleStore = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + '&op=storeForDeliveryRule',
                method: 'post',
                fields: ['id', 'name'],
                listeners: {
                    beforeload: function () {
                        this.baseParams.deliveryMode = deliveryMode;
                        this.baseParams.branchId = br_ID;
                    }

                }
            });
            return deliveryRuleStore;
        };

        var win_id = "set_dr_window";

        var set_dr_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(set_dr_window))
        {
            set_dr_window = new Ext.Window({
                id: win_id,
                title: 'Set Delivery Rules',
                layout: 'fit',
                modal: true,
                width: 300,
                items: [new Ext.FormPanel({
                        labelAlign: 'top',
                        autoHeight: true,
                        width: 300,
                        frame: true,
                        id: 'setDeliveryRule_form',
                        items: [{
                                xtype: 'combo',
                                fieldLabel: 'Express Delivery Rule',
                                emptyText: 'Choose Delivery Rule',
                                id: 'br_rdrIdExpress',
                                name: 'br_rdrIdExpress',
                                labelStyle: mandatory_label,
                                //allowBlank: false,
                                mode: 'local',
                                typeAhead: true,
                                forceSelection: true,
                                editable: true,
                                anchor: '97%', store: deliveryRuleStoreFnc(2),
                                triggerAction: 'all',
                                minChars: 2,
                                displayField: 'name',
                                valueField: 'id',
                                hiddenName: 'br_rdrIdExpress',
                                tabIndex: 16,
                                listeners: {
                                    select: function (combo, record) {


                                    }
                                }
                            }, {
                                xtype: 'combo',
                                fieldLabel: 'Schedule Delivery Rule',
                                emptyText: 'Choose Delivery Rule',
                                id: 'br_rdrIdSlotted',
                                name: 'br_rdrIdSlotted',
                                mode: 'local',
                                typeAhead: true,
                                forceSelection: true,
                                editable: true,
                                anchor: '97%',
                                store: deliveryRuleStoreFnc(3),
                                triggerAction: 'all',
                                minChars: 2, displayField: 'name',
                                valueField: 'id',
                                hiddenName: 'br_rdrIdSlotted',
                                tabIndex: 18,
                                listeners: {
                                    select: function () {

                                    }}
                            }, {
                                xtype: 'combo',
                                fieldLabel: 'Courier Delivery Rule',
                                emptyText: 'Choose Delivery Rule',
                                id: 'br_rdrIdCourier',
                                name: 'br_rdrIdCourier',
                                mode: 'local',
                                typeAhead: true,
                                forceSelection: true,
                                editable: true,
                                anchor: '97%',
                                store: deliveryRuleStoreFnc(1),
                                triggerAction: 'all',
                                minChars: 2,
                                displayField: 'name',
                                valueField: 'id',
                                hiddenName: 'br_rdrIdCourier',
                                tabIndex: 18,
                                listeners: {
                                    select: function () {

                                    }
                                }
                            }]
                    })],
                buttons:
                        [{
                                text: 'Cancel',
                                icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    set_dr_window.close();
                                }
                            },
                            /*     <?php if (user_access("finascop_branch", "isSuperAdmin")) { ?> */{
                                text: 'Save',
                                icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    Ext.Ajax.request({
                                        url: modURL,
                                        params: {
                                            op: 'setBranchDeliveryRule',
                                            br_ID: br_ID,
                                            br_rdrIdCourier: Ext.getCmp('br_rdrIdCourier').getValue(),
                                            br_rdrIdSlotted: Ext.getCmp('br_rdrIdSlotted').getValue(),
                                            br_rdrIdExpress: Ext.getCmp('br_rdrIdExpress').getValue(),
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        },
                                        success: function (response, options) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true) {
                                                Application.example.msg('Notification', tmp.msg);
                                                set_dr_window.close();
                                                Ext.getCmp('branch_main_panel').getStore().reload();
                                            }
                                        }
                                    });
                                }
                            }/*<?php } ?> */]

            });
            if (!Ext.isEmpty(br_ID)) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var dept_form = Ext.getCmp('setDeliveryRule_form').getForm();
                dept_form.load({
                    params: {
                        br_ID: br_ID,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadBranchDeliveryRules',
                    waitMsg: 'Loading...', success: function (form, action) {
                        eval('var tmp=' + action.response.responseText);
                        var tmp = Ext.decode(action.response.responseText);
                        console.log(tmp);

                    },
                    failure: function (form, action) {
                    }
                });
            }
            set_dr_window.doLayout();
            set_dr_window.show(this);
            set_dr_window.center();
        }
    };
    var editAPIKey = function (apiKey, br_ID) {
        var win_id = "api_key_window";

        var api_key_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(api_key_window))
        {
            api_key_window = new Ext.Window({
                id: win_id,
                title: 'API Key Edit Window',
                layout: 'fit',
                modal: true,
                width: 300,
                items: [{
                        xtype: 'textfield',
                        id: 'brAPIKey',
                        value: apiKey,
                        anchor: '98%',
                        fieldLabel: 'Store API Key'
                    }],
                buttons:
                        [{
                                text: 'Cancel',
                                icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    api_key_window.close();
                                }
                            },
                            /*     <?php if (user_access("finascop_branch", "isSuperAdmin")) { ?> */{
                                text: 'Renew',
                                icon: IMAGE_BASE_PATH + '/default/icons/reset.png',
                                handler: function () {
                                    Ext.Ajax.request({
                                        url: modURL,
                                        params: {
                                            op: 'renewBranchAPIKey',
                                            br_ID: br_ID,
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        },
                                        success: function (response, options) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true) {
                                                Ext.getCmp('branch_main_panel').getStore().reload();
                                                Ext.getCmp('brAPIKey').setValue(tmp.newBrAPIKey);
                                            }
                                        }
                                    });
                                }
                            }/*<?php } ?> */]


            });
            api_key_window.doLayout();
            api_key_window.show(this);
            api_key_window.center();
        }
    };
    var vBranchStore = function () {
        var BranchStore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: modURL + '&op=getBranchName',
            autoLoad: true,
            method: 'post',
        });
        return BranchStore;
    };
    var setDefaultRetailor = function (id) {
        var BranchStore = vBranchStore();
        var deliveryRuleStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=storeForDeliveryRule',
            method: 'post',
            fields: ['id', 'name']
        });
        var win_id = "defaultRetailorWindow";

        var defaultRetailorWindow = Ext.getCmp(win_id);
        if (Ext.isEmpty(defaultRetailorWindow))
        {
            defaultRetailorWindow = new Ext.Window({
                id: win_id,
                title: 'Set Default Retailor', layout: 'fit',
                modal: true,
                width: 300,
                items: [new Ext.FormPanel({
                        labelAlign: 'top',
                        autoHeight: true,
                        width: 300,
                        frame: true,
                        id: 'defaultstore_form',
                        items: [{
                                xtype: 'combo',
                                id: 'retailerBranch',
                                name: 'comboxbranchname',
                                mode: 'local',
                                typeAhead: true,
                                forceSelection: true,
                                fieldLabel: 'Branch Name',
                                editable: true,
                                anchor: '97%',
                                store: BranchStore,
                                triggerAction: 'all',
                                minChars: 2,
                                displayField: 'br_Name',
                                valueField: 'br_ID',
                                hiddenName: 'comboxbranchname',
                                listeners: {
                                    select: function () {

                                    }
                                }
                            }]
                    })],
                buttons:
                        [{
                                text: 'Cancel',
                                icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    defaultRetailorWindow.close();
                                }
                            },
                            {
                                text: 'Update',
                                icon: IMAGE_BASE_PATH + '/default/icons/reset.png',
                                handler: function () {
                                    if (Ext.getCmp('retailerBranch').getValue() > 0) {
                                        Ext.Ajax.request({waitMsg: 'Processing',
                                            url: modcsURL,
                                            params: {
                                                op: 'setCSDefault',
                                                br_ID: Ext.getCmp('retailerBranch').getValue(),
                                                pyramid: 4
                                            },
                                            failure: function (response, options) {
                                                Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                            },
                                            success: function (response, options) {
                                                eval("var tmp=" + response.responseText);
                                                if (tmp.success === true) {
                                                    Application.example.msg('Notification', tmp.msg);
                                                    defaultRetailorWindow.close();
                                                    Ext.getCmp(id).getStore().reload();
                                                }
                                            }
                                        });
                                    } else {
                                        Ext.MessageBox.alert("Notification", "Please select Retail branch.");
                                    }

                                }
                            }
                        ]


            });
            defaultRetailorWindow.doLayout();
            defaultRetailorWindow.show(this);
            defaultRetailorWindow.center();
        }
    };
    return {
        Cache: {},
        init: function () {
            var _retailStorePanelId = 'panelRetailStore';
            var _retailStorePanel = Ext.getCmp(_retailStorePanelId);
            if (Ext.isEmpty(_retailStorePanel)) {
                _retailStorePanel = createRetailStoreGrid(_retailStorePanelId);
                Application.UI.addTab(_retailStorePanel);
                _retailStorePanel.doLayout();
            } else {
                Application.UI.addTab(_retailStorePanel);
                _retailStorePanel.doLayout();
            }
        },
        saveRetailStore: function () {
            var retailstore_form = Ext.getCmp('retailstore_form');

            var t = new Date();
            var t_stamp = t.format("YmdHis");

            Ext.MessageBox.show({
                msg: 'Saving your data, please wait...',
                title: 'Wait...',
                progressText: 'Saving...',
                width: 300,
                wait: true
            });

            retailstore_form.getForm().submit({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (retailstore_form, action) {
                    Ext.MessageBox.hide();
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Ext.getCmp('retailstore_details_window').close();
                        Application.example.msg("Success", "Retail Store details has been saved successfully");
                        Ext.getCmp('panelRetailStore').getStore().reload();
                    }
                },
                failure: function (retailstore_form, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        if (obj.errors.reason !== undefined) {
                            obj.errors = obj.success.reason;
                        }
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: obj.errors,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    } else {
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: MAN_FIELD,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    }
                }
            });
        },
        changeStatus: function () {
            Ext.Ajax.request({
                waitMsg: 'Processing',
                url: modURL,
                params: {
                    op: 'changeStatus',
                    br_ID: Application.MyphaRetail.br_ID,
                    br_status: Application.MyphaRetail.br_status,
                    comp_id: Application.MyphaRetail.comp_id
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {
                        Application.example.msg("Success", "Retail Store status has been changed successfully");
                        Ext.getCmp('panelRetailStore').getStore().reload();
//                        function (btn) {
//
//                            Ext.getCmp('panelRetailStore').getStore().reload();
//                        });
                    }
                }
            });
        }

    }
}();




