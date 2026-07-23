Application.MyphaMedProduct = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=mypha_medicinepro';
    var recs_per_page = 12;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var medicineproductsData = function () {
        Ext.getCmp('medicineproducts_grid').getStore().setDefaultSort('stit_itemName', 'asc');
        Ext.getCmp('medicineproducts_grid').getStore().removeAll();
        Ext.getCmp('medicineproducts_grid').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    };
    var medicineproductsStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listItemMasterData',
                method: 'post'
            }),
            fields: ['ItemId', 'stit_itemName', 'hsn_code', 'product_is_home', 'stit_category_name', 'stit_brand_name', 'imgCount', 'stit_product_variant', 'stit_quantity', 'least_package_type_name',
                {name: 'tax', type: 'number'}, {name: 'mrp', type: 'float'}, 'groups', 'description',
                {name: 'convertable_on', type: 'number'}, {name: 'convertable_off', type: 'number'},
                {name: 'list_in_sales_on', type: 'number'}, {name: 'list_in_sales_off', type: 'number'},
                {name: 'stock_enabled', type: 'number'}, {name: 'stock_disabled', type: 'number'},
                {name: 'list_in_purchase_on', type: 'number'}, {name: 'list_in_purchase_off', type: 'number'},
                {name: 'tangible_on', type: 'number'}, {name: 'tangible_off', type: 'number'},
                {name: 'total_mrp', type: 'float'}, {name: 'tax_total', type: 'float'}, 'stit_status', 'statusName', 'stit_SKU', 'isVerified'
            ],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                load: function (store, record, options) {

//                    Ext.getCmp('medicineproducts_grid').getSelectionModel().selectRow(0);
//                    if (record.length > 0) {
//                        var record = Ext.getCmp('medicineproducts_grid').getSelectionModel().getSelected();
//
//                        total_mrp = record.get('total_mrp');
//                        var tax_total = record.get('tax_total');
//                        var number_of_col = Ext.getCmp('medicineproducts_grid').getColumnModel().config.length;
//                        var tooltipmaker = Ext.getCmp('medicineproducts_grid').getColumnModel().config;
//
//                        for (var i = 1; i < number_of_col; i++) {
//                            var column_header = tooltipmaker[i].header;
//                            if (column_header == 'MRP')
//                            {
//                                tooltipmaker[i].tooltip = 'Total MRP : ' + total_mrp;
//                            }
//                            if (column_header == 'Tax')
//                            {
//                                tooltipmaker[i].tooltip = 'Total Tax : ' + tax_total;
//                            }
//
//                        }
//                    }


                }
            }
        });
        store.setDefaultSort('stit_itemName', 'ASC');
        return store;
    };
    var updateSalesPackageTtpeStore = function (field, combofield) {
        var comboStore = Ext.getCmp(combofield).getStore();
        var newValue = Ext.getCmp(field).getValue();
        var exist = comboStore.find('id', newValue);
        var newRawValue = Ext.getCmp(field).getRawValue();
        Ext.getCmp('ccsb_package_type_id').getStore().load({
            params: {
                stdpckl11: Ext.getCmp('least_package_type_id').getValue(),
                stdpckl21: Ext.getCmp('stdpckl21_package_type_id').getValue(),
                stdpckl31: Ext.getCmp('stdpckl31_package_type_id').getValue(),
                stdpckl41: Ext.getCmp('stdpckl41_package_type_id').getValue()
            }
        });
    };
    var medicineGridAction = function () {
        var action = new Ext.ux.grid.RowActions({
            autoWidth: false,
            hideMode: 'display',
            width: 150,
            actions: [
                {
                    sortable: false,
                    tooltip: 'Edit Item',
                    iconCls: 'finascop_edit',
                    callback: function (grid, rec, row, col) {
                        addItem(rec.get('ItemId'), '');
                    }
                }, {
                    tooltip: 'Duplicate',
                    iconCls: 'product_duplicate',
                    callback: function (grid, rec, row, col) {
                        addItem(rec.get('ItemId'), 'D');
                    }
                }
            ]
        });
        return action;
    };
    var medproductActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                //iconCls: 'finascop_edit',
                handler: function () {

                    var ItemId = Ext.getCmp('medicineproducts_grid').getSelectionModel().getSelections()[0].data.ItemId;
                    Application.MyphaMedProduct.Cache.aspLevel = 0;
                    addItem(ItemId, '');
                }
            }, {
                text: "Duplicate",
                //iconCls: 'product_duplicate',
                handler: function () {

                    var ItemId = Ext.getCmp('medicineproducts_grid').getSelectionModel().getSelections()[0].data.ItemId;
                    Application.MyphaMedProduct.Cache.aspLevel = 0;
                    addItem(ItemId, 'D');
                }
            }, {
                text: 'Upload Images',
                //iconCls: 'product_duplicate',
                handler: function () {
                    var ItemId = Ext.getCmp('medicineproducts_grid').getSelectionModel().getSelections()[0].data.ItemId;
                    Application.VLSLUpload.vlslUpload(ItemId);
                }
            }, {
                text: 'Status Change',
                //iconCls: 'product_duplicate',
                handler: function () {
                    var ItemId = Ext.getCmp('medicineproducts_grid').getSelectionModel().getSelections()[0].data.ItemId;
                    var status = Ext.getCmp('medicineproducts_grid').getSelectionModel().getSelections()[0].data.stit_status;
                    Application.MyphaMedProduct.StatusChange(ItemId, status, 'Medicine');
                }
            }, {
                text: 'Product Codes',
                handler: function () {
                    var ItemId = Ext.getCmp('medicineproducts_grid').getSelectionModel().getSelections()[0].data.ItemId;
                    var stit_SKU = Ext.getCmp('medicineproducts_grid').getSelectionModel().getSelections()[0].data.stit_SKU;
                    Application.MyphaProduct.createProductCodes(ItemId, stit_SKU);
                }
            }]
    });
    var viewMedicinePanel = function (itemid) {
        var medicineMasterStore = new Ext.data.JsonStore({
            fields: ['medicineMaster_id', 'medicineMaster_name', 'medicine_type_name', 'composition_name', 'subCategory_name', 'medicine_type'],
            url: modURL + '&op=getMedicineName',
            autoLoad: false,
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.itemid = itemid;
                }
            }
        });
        var hsnStore = function () {
            var hsn_store = new Ext.data.JsonStore({
                url: modURL + '&op=gethsnStore',
                fields: ['hsn_id', 'hsn_code', 'gst_percent'],
                totalProperty: 'totalCount',
                root: 'data',
                autoload: true,
                remoteFilter: true,
                listeners: {
                }
            });
            return hsn_store;
        };
        var packTypeStore = function () {
            var pt_store = new Ext.data.JsonStore({
                url: modURL + '&op=getPTStore',
                fields: ['package_type_id', 'package_type_name'],
                totalProperty: 'totalCount',
                root: 'data',
                autoload: true,
                remoteFilter: true,
                listeners: {
                    beforeload: function () {
                        this.baseParams.stdpckl11 = Ext.getCmp('least_package_type_id').getValue();
                        this.baseParams.stdpckl21 = Ext.getCmp('stdpckl21_package_type_id').getValue();
                        this.baseParams.stdpckl31 = Ext.getCmp('stdpckl31_package_type_id').getValue();
                        this.baseParams.stdpckl41 = Ext.getCmp('stdpckl41_package_type_id').getValue();
                    }
                }
            });
            return pt_store;
        };
        var hsnStore = hsnStore();
        var packTypeStore = packTypeStore();
        var medicinePanel = new Ext.FormPanel({
            id: 'medicinePanel',
            width: winsize.width * .9,
            height: 500,
            autoScroll: true,
            frame: true,
            layout: 'column',
            monitorValid: true,
            items: [{
                    layout: 'form',
                    columnWidth: 0.30,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'hidden',
                            fieldLabel: 'Product Id',
                            id: 'itemId',
                            name: 'itemId',
                            anchor: '95%',
                            allowBlank: false
                        }, {
                            xtype: 'combo',
                            fieldLabel: 'Medicines',
                            id: 'medicineMasters',
                            name: 'medicineMasters',
                            labelStyle: mandatory_label,
                            allowBlank: false,
                            labelAlign: 'top',
                            mode: 'remote',
                            typeAhead: true,
                            lazyRender: true,
                            //forceSelection: true,
                            editable: true,
                            anchor: '97%',
                            store: medicineMasterStore,
                            hideTrigger: true,
                            triggerAction: 'all',
                            minChars: 1,
                            displayField: 'medicineMaster_name',
                            valueField: 'medicineMaster_id',
                            hiddenName: 'medicineMasters',
                            tabIndex: 501,
                            listeners: {
                                select: function (index, val) {
                                    console.log('val',val);
                                    var medicineMasters = Ext.getCmp('medicineMasters').getValue();
                                    Ext.getCmp('med_generic_name').setValue(val.data.composition_name);
                                    Ext.getCmp('med_drug_groupname').setValue(val.data.subCategory_name);
                                    Ext.getCmp('med_dosage_form').setValue(val.data.medicine_type_name);
                                    Ext.getCmp('ccs_package_type_id').setValue(val.data.medicine_type_name);
                                    Ext.getCmp('cos_package_type_id').setValue(val.data.medicine_type_name);
                                    Ext.getCmp('rs_package_type_id').setValue(val.data.medicine_type_name);
                                    Ext.getCmp('stdpckl12_package_type_id').setValue(val.data.medicine_type);
                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.30,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Generic Name',
                            id: 'med_generic_name',
                            name: 'med_generic_name',
                            anchor: '95%',
                            maxLength: 250,
                            tabIndex: 502,
                            allowBlank: false,
                            readOnly: true,
                            listeners: {
                                afterrender: function (field) {
                                    Ext.defer(function () {
                                        field.focus(true, 100);
                                    }, 1);
                                }
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Drug Group',
                            id: 'med_drug_groupname',
                            name: 'med_drug_groupname',
                            anchor: '95%',
                            maxLength: 250,
                            tabIndex: 503,
                            readOnly: true,
                            allowBlank: false
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Dosage Form',
                            id: 'med_dosage_form',
                            name: 'med_dosage_form',
                            anchor: '95%',
                            maxLength: 250,
                            tabIndex: 504,
                            readOnly: true,
                            allowBlank: false
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.30,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Variant',
                            id: 'stit_product_variant',
                            name: 'stit_product_variant',
                            anchor: '95%',
                            maxLength: 250,
                            tabIndex: 505
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.15,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Quantity',
                            id: 'stit_quantity',
                            name: 'stit_quantity',
                            anchor: '95%',
                            maxLength: 250,
                            tabIndex: 506
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.15,
                    labelAlign: 'top',
                    items: [
                        {
                            fieldLabel: 'HSN',
                            xtype: 'combo',
                            displayField: 'hsn_code',
                            valueField: 'hsn_id',
                            mode: 'remote',
                            id: 'HSN',
                            name: 'HSN',
                            emptyText: 'Select HSN Code',
                            anchor: '95%',
                            allowBlank: false,
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            store: hsnStore,
                            editable: true,
                            tabIndex: 507,
                            minChars: 2, listeners: {
                                select: function (index, val) {
                                    Ext.getCmp('GST').setValue(val.data.gst_percent);
                                }
                            }
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'GST %',
                            id: 'GST',
                            name: 'GST',
                            readOnly: true,
                            anchor: '95%',
                            tabIndex: 508,
                            allowBlank: false
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Display label in web',
                            id: 'stit_displaylabels',
                            name: 'stit_displaylabels',
                            anchor: '95%',
                            maxLength: 250,
                            tabIndex: 509
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Item ERP Id',
                            id: 'stit_itemERPId',
                            name: 'stit_itemERPId',
                            anchor: '95%',
                            maxLength: 100,
                            tabIndex: 510,
                            allowBlank: true,
                            hidden: true
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Item Barcode',
                            id: 'stit_itemBarcode',
                            name: 'stit_itemBarcode',
                            anchor: '95%',
                            maxLength: 100,
                            tabIndex: 511,
                            allowBlank: true,
                            hidden: true
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'right',
                    bodyStyle: {"padding-top": "15px"},
                    items: [{
                            xtype: 'checkbox',
                            checked: false,
                            width: 300,
                            id: 'stit_fixedB2BRates',
                            name: 'stit_fixedB2BRates',
                            tabIndex: 512,
                            inputValue: 1,
                            boxLabel: 'Fixed B2B Rates',
                            value: 0
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textfield',
                            width: 100,
                            id: 'stit_itemReturnTime',
                            name: 'stit_itemReturnTime',
                            tabIndex: 513,
                            allowBlank: true,
                            fieldLabel: 'Return Time (days)',
                            value: 0
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: 0.20,
                    labelAlign: 'top',
                    items: [{
                            width: 300,
                            xtype: 'checkbox',
                            checked: false,
                            id: 'stit_custInitiate',
                            name: 'stit_custInitiate',
                            inputValue: 1,
                            tabIndex: 514,
                            hidden: true,
                            fieldLabel: 'Customer can initiate return'
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: .10,
                    bodyStyle: {"padding-top": "15px"},
                    labelAlign: 'right',
                    labelWidth: 45,
                    items: [{
                            xtype: 'checkbox',
                            checked: false,
                            id: 'featured',
                            name: 'featured',
                            inputValue: 1,
                            tabIndex: 515,
                            boxLabel: 'Featured'
                        }]
                }, {
                    layout: 'form',
                    columnWidth: .10,
                    bodyStyle: {"padding-top": "15px"},
                    labelAlign: 'right',
                    labelWidth: 45,
                    items: [{
                            xtype: 'checkbox',
                            checked: false,
                            id: 'popular',
                            name: 'popular',
                            inputValue: 1,
                            tabIndex: 516,
                            boxLabel: 'Popular'
                        }]
                }, {
                    layout: 'form',
                    columnWidth: 1.04,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'textarea',
                            fieldLabel: 'Short Description',
                            id: 'description',
                            name: 'description',
                            anchor: '95%',
                            maxLength: 1000,
                            tabIndex: 517,
                            allowBlank: false,
                            height: 100,
                            style: {
                                color: 'black',
                                fontFamily: 'verdana',
                                fontSize: '12px'
                            }
                        }]
                }, {
                    layout: 'form',
                    columnWidth: .40,
                    labelAlign: 'top',
                    items: [mkCombo({
                            "type": 'mypha_productpackage_type',
                            "value": "package_type_id",
                            "display": "package_type_name",
                            "name": "least_package_type_id",
                            "fieldLabel": "SKU (Smallest Known Unit)",
                            "emptyText": "SKU (Smallest Known Unit)",
                            "id": "least_package_type_id",
                            "hiddenName": "least_package_type_id",
                            "listeners": false,
                            "anchor": '75%',
                            "tabIndex": 518,
                            "cx": "S_1",
                            "combo_listeners": {
                                select: function (combo, record, index) {
                                    var value = record.data.package_type_id;
                                    var stit_package_type_id = Ext.getCmp('med_dosage_form').getValue();
                                    if (value != stit_package_type_id) {
                                        Ext.getCmp('stit_stdPacking').setValue(true);
                                        Ext.getCmp('stit_salesUnit').setValue(true);
                                        Ext.getCmp('stdpckl1_nos').allowBlank = false;
                                    }
                                    Ext.getCmp('stdpckl11_package_type_id').setValue(value);
                                    updateSalesPackageTtpeStore('least_package_type_id', 'ccsb_package_type_id');
//                                    Ext.getCmp('ccsb_package_type_id').setValue(value);
//                                    Ext.getCmp('cosb_package_type_id').setValue(value);
//                                    Ext.getCmp('ds_package_type_id').setValue(value);
//                                    Ext.getCmp('rsb_package_type_id').setValue(value);
                                }
                            }
                        }), {
                            xtype: 'checkbox',
                            checked: false,
                            labelAlign: 'right',
                            id: 'stit_stdPacking',
                            name: 'stit_stdPacking',
                            inputValue: 1,
                            tabIndex: 519,
                            boxLabel: 'Apply Standard Packing'
                        }, {
                            labelAlign: 'top',
                            xtype: 'compositefield',
                            hideLabel: true,
                            fieldLabel: ' ',
                            id: 'astdpck0',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stdpckl1_nos',
                                    emptyText: 'Nos',
                                    name: 'stdpckl1_nos',
                                    fieldLabel: 'Nos',
                                    width: 50,
                                    tabIndex: 520,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                        }
                                    }
                                }, mkCombo({
                                    "type": 'mypha_medicineType',
                                    "value": "medicine_type_id",
                                    "display": "medicine_type_name",
                                    "name": "stdpckl12_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Contains",
                                    "id": "stdpckl12_package_type_id",
                                    "hiddenName": "stdpckl12_package_type_id",
                                    "listeners": false,
                                    "hideTrigger": true,
                                    "allowBlank": false,
                                    "width": 55,
                                    tabIndex: 521,
                                    "cx": "S_1",
                                    "combo_listeners": {
                                        select: function (combo, record, index) {
                                            var value = record.data.package_type_id;
                                            Ext.getCmp('cos_package_type_id').setValue(value);
                                            Ext.getCmp('rs_package_type_id').setValue(value);
                                        }
                                    }
                                }), {
                                    style: 'margin-top:7px;',
                                    html: ' = 1 '
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "stdpckl11_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Purchasing unit",
                                    "id": "stdpckl11_package_type_id",
                                    "hiddenName": "stdpckl11_package_type_id",
                                    "listeners": false,
                                    "hideTrigger": true,
                                    "width": 55,
                                    tabIndex: 522,
                                    "cx": "S_1",
                                    "combo_listeners": {
                                        select: function (combo, record, index) {
                                            var value = record.data.package_type_id;
                                            Ext.getCmp('stdpckl22_package_type_id').setValue(value);
                                        }
                                    }
                                }), {
                                    xtype: 'button',
                                    text: 'Add',
                                    align: 'right',
                                    tabIndex: 523,
                                    iconCls: 'finascop_add',
                                    handler: function () {
                                        Application.MyphaMedProduct.Cache.aspLevel++;
                                        if (Application.MyphaMedProduct.Cache.aspLevel <= 4) {
                                            Ext.getCmp('astdpck' + Application.MyphaMedProduct.Cache.aspLevel).show();
                                            var value = Ext.getCmp('stdpckl' + Application.MyphaMedProduct.Cache.aspLevel + '1_package_type_id').getValue();
                                            var level = Application.MyphaMedProduct.Cache.aspLevel + 1;
                                            Ext.getCmp('stdpckl' + level + '_nos').allowBlank = false;
                                            Ext.getCmp('stdpckl' + level + '2_package_type_id').setValue(value);
                                        }

                                    }
                                }
                            ]
                        }, {
                            xtype: 'compositefield',
                            labelAlign: 'top',
                            hideLabel: true,
                            fieldLabel: ' ',
                            id: 'astdpck1',
                            hidden: true,
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stdpckl2_nos',
                                    emptyText: 'Nos',
                                    name: 'stdpckl2_nos',
                                    fieldLabel: 'Nos',
                                    width: 50,
                                    tabIndex: 524,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                            var numbers = Ext.getCmp('cos_nos').getValue();
                                            Ext.getCmp('rs_nos').setValue(numbers);
                                        }
                                    }
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "stdpckl22_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Contains",
                                    "id": "stdpckl22_package_type_id",
                                    "hiddenName": "stdpckl22_package_type_id",
                                    "hideTrigger": true,
                                    "allowBlank": true,
                                    "width": 55,
                                    tabIndex: 525,
                                    "cx": "S_1",
                                    "readOnly": true

                                }), {
                                    style: 'margin-top:7px;',
                                    html: ' = 1 '
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "stdpckl21_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Purchasing unit",
                                    "id": "stdpckl21_package_type_id",
                                    "hiddenName": "stdpckl21_package_type_id",
                                    "listeners": false,
                                    "allowBlank": true,
                                    "width": 55,
                                    tabIndex: 526,
                                    "cx": "S_1",
                                    "combo_listeners": {
                                        select: function (combo, record, index) {
                                            var value = record.data.package_type_id;
                                            Ext.getCmp('stdpckl32_package_type_id').setValue(value);
                                            updateSalesPackageTtpeStore('stdpckl21_package_type_id', 'ccsb_package_type_id');
                                        }
                                    }
                                }), {
                                    xtype: 'button',
                                    text: 'Delete',
                                    iconCls: 'finascop_delete',
                                    align: 'right',
                                    tabIndex: 529,
                                    handler: function () {
                                        Ext.getCmp('stdpckl2_nos').reset();
                                        Ext.getCmp('stdpckl22_package_type_id').reset();
                                        Ext.getCmp('stdpckl21_package_type_id').reset();
                                        Ext.getCmp('astdpck1').hide();
                                        Application.MyphaMedProduct.Cache.aspLevel = 0;

                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'compositefield',
                            labelAlign: 'top',
                            hideLabel: true,
                            fieldLabel: ' ',
                            id: 'astdpck2',
                            hidden: true,
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stdpckl3_nos',
                                    emptyText: 'Nos',
                                    name: 'stdpckl3_nos',
                                    fieldLabel: 'Nos',
                                    width: 50,
                                    tabIndex: 527,
                                    maxLength: 10,
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "stdpckl32_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Contains",
                                    "id": "stdpckl32_package_type_id",
                                    "hiddenName": "stdpckl32_package_type_id",
                                    "listeners": false,
                                    "allowBlank": true,
                                    "width": 55,
                                    tabIndex: 528,
                                    "cx": "S_1",
                                    "readOnly": true
                                }), {
                                    style: 'margin-top:7px;',
                                    html: ' = 1 '
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "stdpckl31_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Purchasing Unit",
                                    "id": "stdpckl31_package_type_id",
                                    "hiddenName": "stdpckl31_package_type_id",
                                    "listeners": false,
                                    "allowBlank": true,
                                    "width": 55,
                                    tabIndex: 529,
                                    "cx": "S_1",
                                    "combo_listeners": {
                                        select: function (combo, record, index) {
                                            var value = record.data.package_type_id;
                                            Ext.getCmp('stdpckl42_package_type_id').setValue(value);
                                            updateSalesPackageTtpeStore('stdpckl31_package_type_id', 'ccsb_package_type_id');
                                        }
                                    }
                                }), {
                                    xtype: 'button',
                                    text: 'Delete',
                                    iconCls: 'finascop_delete',
                                    align: 'right',
                                    tabIndex: 532,
                                    handler: function () {
                                        Ext.getCmp('stdpckl3_nos').reset();
                                        Ext.getCmp('stdpckl32_package_type_id').reset();
                                        Ext.getCmp('stdpckl31_package_type_id').reset();
                                        Ext.getCmp('astdpck2').hide();
                                        Application.MyphaMedProduct.Cache.aspLevel = 1;

                                    }
                                }]
                        }, {
                            labelAlign: 'top',
                            xtype: 'compositefield',
                            hideLabel: true,
                            fieldLabel: ' ',
                            id: 'astdpck3',
                            hidden: true,
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stdpckl4_nos',
                                    emptyText: 'Nos',
                                    name: 'stdpckl4_nos',
                                    fieldLabel: 'Nos',
                                    width: 50,
                                    tabIndex: 530,
                                    maxLength: 10,
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "stdpckl42_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Contains",
                                    "id": "stdpckl42_package_type_id",
                                    "hiddenName": "stdpckl42_package_type_id",
                                    "width": 55,
                                    "allowBlank": true,
                                    "listeners": false,
                                    tabIndex: 531,
                                    "cx": "S_1",
                                    "hideTrigger": true,
                                    "readOnly": true
                                }), {
                                    style: 'margin-top:7px;',
                                    html: ' = 1 '
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "stdpckl41_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Purchasing Unit",
                                    "id": "stdpckl41_package_type_id",
                                    "hiddenName": "stdpckl41_package_type_id",
                                    "width": 65,
                                    "listeners": false,
                                    "allowBlank": true,
                                    tabIndex: 532,
                                    "cx": "S_1",
                                    "combo_listeners": {
                                        select: function (combo, record, index) {
                                            var value = record.data.package_type_id;
                                            updateSalesPackageTtpeStore('stdpckl41_package_type_id', 'ccsb_package_type_id');
                                        }
                                    }
                                }), {
                                    xtype: 'button',
                                    text: 'Delete',
                                    iconCls: 'finascop_delete',
                                    align: 'right',
                                    tabIndex: 535,
                                    handler: function () {
                                        Ext.getCmp('stdpckl4_nos').reset();
                                        Ext.getCmp('stdpckl42_package_type_id').reset();
                                        Ext.getCmp('stdpckl41_package_type_id').reset();
                                        Ext.getCmp('astdpck3').hide();
                                        Application.MyphaMedProduct.Cache.aspLevel = 2;
                                    }
                                }
                            ]
                        }
                    ]
                }, {
                    layout: 'form',
                    columnWidth: .30,
                    labelWidth: 150,
                    labelAlign: 'top',
                    items: [{
                            xtype: 'compositefield',
                            fieldLabel: 'Level 1 (Qty in nos)',
                            id: 'level1_details',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stitl1_optimumqty',
                                    emptyText: 'Opti Qty',
                                    name: 'stitl1_optimumqty',
                                    fieldLabel: 'Optimum Qty',
                                    width: 80,
                                    tabIndex: 533,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit11_minimumqty',
                                    emptyText: 'Min Qty',
                                    name: 'stit11_minimumqty',
                                    fieldLabel: 'Minimum Qty',
                                    width: 80,
                                    tabIndex: 534,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit11_maximumqty',
                                    emptyText: 'Max Qty',
                                    name: 'stit11_maximumqty',
                                    fieldLabel: 'Maximium Qty',
                                    width: 80,
                                    tabIndex: 535,
                                    maxLength: 10,
                                    allowBlank: false
                                }
                            ]
                        }, {
                            xtype: 'compositefield',
                            fieldLabel: 'Level 2 (Qty in nos)',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stitl2_optimumqty',
                                    emptyText: 'Opti Qty',
                                    name: 'stitl2_optimumqty',
                                    fieldLabel: 'Optimum Qty',
                                    width: 80,
                                    tabIndex: 536,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit12_minimumqty',
                                    emptyText: 'Min Qty',
                                    name: 'stit12_minimumqty',
                                    fieldLabel: 'Mininimum Qty',
                                    width: 80,
                                    tabIndex: 537,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit12_maximumqty',
                                    emptyText: 'Max Qty',
                                    name: 'stit12_maximumqty',
                                    fieldLabel: 'Maximium Qty',
                                    width: 80,
                                    tabIndex: 538,
                                    maxLength: 10,
                                    allowBlank: false
                                }]
                        }, {
                            xtype: 'compositefield',
                            fieldLabel: 'Level 3 (Qty in nos)',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stitl3_optimumqty',
                                    emptyText: 'Opti Qty',
                                    name: 'stitl3_optimumqty',
                                    fieldLabel: 'Optimum Qty',
                                    width: 80,
                                    tabIndex: 539,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit13_minimumqty',
                                    emptyText: 'Min Qty',
                                    name: 'stit13_minimumqty',
                                    fieldLabel: 'Minimum Qty',
                                    width: 80,
                                    tabIndex: 540,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit13_maximumqty',
                                    emptyText: 'Max Qty',
                                    name: 'stit13_maximumqty',
                                    fieldLabel: 'Maximium Qty',
                                    width: 80,
                                    tabIndex: 541,
                                    maxLength: 10,
                                    allowBlank: false
                                }
                            ]
                        }, {
                            xtype: 'compositefield',
                            fieldLabel: 'Buffer %',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    emptyText: 'Distributer Buffer %',
                                    id: 'stii_csb',
                                    name: 'stii_csb',
                                    anchor: '75%',
                                    tabIndex: 542,
                                    width: 120,
                                    maxValue: 100,
                                    allowBlank: false
                                }, {
                                    xtype: 'numberfield',
                                    emptyText: 'Retailer Buffer %',
                                    id: 'stii_csbretail',
                                    name: 'stii_csbretail',
                                    anchor: '75%',
                                    tabIndex: 543,
                                    width: 120,
                                    maxValue: 100,
                                    allowBlank: false
                                }]
                        }]
                }, {
                    layout: 'form',
                    labelWidth: 120,
                    columnWidth: .30,
                    items: [{
                            xtype: 'checkbox',
                            checked: false,
                            labelAlign: 'right',
                            id: 'stit_salesUnit',
                            name: 'stit_salesUnit',
                            inputValue: 1,
                            tabIndex: 544,
                            boxLabel: 'Sales Uinit Applicable'
                        },{
                            //hidden:true,
                            xtype: 'compositefield',
                            fieldLabel: 'Online Sales',
                            combineErrors: false,
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'package_type_name',
                                    valueField: 'package_type_id',
                                    mode: 'local',
                                    id: 'cosb_package_type_id',
                                    name: 'cosb_package_type_id',
                                    allowBlank: false,
                                    forceSelection: true,
                                    fieldLabel: 'Package Type',
                                    emptyText: 'Purchasing unit',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 552,
                                    store: packTypeStore,
                                    listeners: {
                                        select: function (combo, record, index) {
                                            var value = record.data.package_type_id;
                                            Ext.getCmp('ccsb_package_type_id').setValue(value);
                                            Ext.getCmp('rsb_package_type_id').setValue(value);
                                        }
                                    }
                                }, {
                                    xtype: 'numberfield',
                                    id: 'cos_nos',
                                    hidden: true,
                                    emptyText: 'Nos',
                                    name: 'cos_nos',
                                    fieldLabel: 'Nos',
                                    width: 80,
                                    tabIndex: 553,
                                    maxLength: 10,
                                    allowBlank: true,
                                    listeners: {
                                        change: function () {
                                            var numbers = Ext.getCmp('cos_nos').getValue();
                                            Ext.getCmp('rs_nos').setValue(numbers);
                                        }
                                    }
                                }, mkCombo({
                                    "type": 'mypha_medicineType',
                                    "value": "medicine_type_id",
                                    "display": "medicine_type_name",
                                    "name": "cos_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "",
                                    "id": "cos_package_type_id",
                                    "hiddenName": "cos_package_type_id",
                                    "width": 80,
                                    hidden: true,
                                    allowBlank: true,
                                    tabIndex: 554,
                                    "cx": "S_1",
                                    //"hideTrigger": true,
                                    "readOnly": true

                                }),
                                {
                                    xtype: 'numberfield',
                                    id: 'cos_length',
                                    hidden: true,
                                    emptyText: 'Length',
                                    name: 'cos_length',
                                    fieldLabel: 'Length',
                                    width: 100,
                                    tabIndex: 555,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                            var numbers = Ext.getCmp('cos_length').getValue();
                                            Ext.getCmp('rs_length').setValue(numbers);
                                        }
                                    }
                                }, {
                                    xtype: 'textfield',
                                    id: 'cos_breadth',
                                    hidden: true,
                                    emptyText: 'Breadth',
                                    name: 'cos_breadth',
                                    fieldLabel: 'Breadth',
                                    width: 100,
                                    tabIndex: 556,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                            var numbers = Ext.getCmp('cos_breadth').getValue();
                                            Ext.getCmp('rs_breadth').setValue(numbers);
                                        }
                                    }
                                }, {
                                    xtype: 'textfield',
                                    id: 'cos_height',
                                    hidden: true,
                                    emptyText: 'Height',
                                    name: 'cos_height',
                                    fieldLabel: 'Height',
                                    width: 100,
                                    tabIndex: 557,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                            var numbers = Ext.getCmp('cos_height').getValue();
                                            Ext.getCmp('rs_height').setValue(numbers);
                                        }
                                    }
                                }, {
                                    xtype: 'numberfield',
                                    hidden: true,
                                    emptyText: 'Weight in grams',
                                    id: 'cos_weight',
                                    name: 'cos_weight',
                                    tabIndex: 558,
                                    width: 100,
                                    maxLength: 10,
                                    listeners: {
                                        change: function () {
                                            var numbers = Ext.getCmp('cos_weight').getValue();
                                            Ext.getCmp('rs_weight').setValue(numbers);
                                        }
                                    }
                                }]
                        }, {
                            xtype: 'compositefield',
                            fieldLabel: 'SKU for Retailer (Counter Sale)',
                            disabled: true,
                            combineErrors: false,
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'package_type_name',
                                    valueField: 'package_type_id',
                                    mode: 'local',
                                    id: 'ccsb_package_type_id',
                                    name: 'ccsb_package_type_id',
                                    allowBlank: false,
                                    forceSelection: true,
                                    fieldLabel: 'Package Type',
                                    emptyText: 'Package Type',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 545, //reconciliation_template_store
                                    store: packTypeStore,
                                    listeners: {
                                        select: function (combo, record, index) {
                                            var value = record.data.package_type_id;
                                            Ext.getCmp('rsb_package_type_id').setValue(value);
                                        }
                                    }
                                }, {
                                    xtype: 'numberfield',
                                    id: 'ccs_nos',
                                    emptyText: 'Nos',
                                    hidden: true,
                                    name: 'ccs_nos',
                                    fieldLabel: 'Nos',
                                    width: 80,
                                    tabIndex: 546,
                                    maxLength: 10,
                                    //allowBlank: false,
                                    listeners: {
                                        change: function () {
                                            var numbers = Ext.getCmp('ccs_nos').getValue();
                                            Ext.getCmp('cos_nos').setValue(numbers);
                                            Ext.getCmp('rs_nos').setValue(numbers);
                                        }
                                    }
                                }, mkCombo({
                                    "type": 'mypha_medicineType',
                                    "value": "medicine_type_id",
                                    "display": "medicine_type_name",
                                    "name": "ccs_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "",
                                    "id": "ccs_package_type_id",
                                    "hiddenName": "ccs_package_type_id",
                                    "listeners": false,
                                    allowBlank: true,
                                    "width": 80,
                                    "hidden": true,
                                    "tabIndex": 547,
                                    "cx": "S_1",
                                    "combo_listeners": {
                                        select: function (combo, record, index) {
                                            var value = record.data.package_type_id;
                                            //Ext.getCmp('cos_package_type_id').setValue(value);
                                            //Ext.getCmp('rs_package_type_id').setValue(value);
                                        }
                                    }
                                }),
                                {
                                    xtype: 'numberfield',
                                    id: 'ccs_length',
                                    hidden: true,
                                    emptyText: 'Length',
                                    name: 'ccs_length',
                                    fieldLabel: 'Length',
                                    width: 100,
                                    tabIndex: 548,
                                    maxLength: 10,
                                }, {
                                    xtype: 'textfield',
                                    id: 'ccs_breadth',
                                    hidden: true,
                                    emptyText: 'Breadth',
                                    name: 'ccs_breadth',
                                    fieldLabel: 'Breadth',
                                    width: 100,
                                    tabIndex: 549,
                                    maxLength: 10,
                                }, {
                                    xtype: 'textfield',
                                    id: 'ccs_height',
                                    hidden: true,
                                    emptyText: 'Height',
                                    name: 'ccs_height',
                                    fieldLabel: 'Height',
                                    width: 100,
                                    tabIndex: 550,
                                    maxLength: 10,
                                }, {
                                    xtype: 'numberfield',
                                    hidden: true,
                                    emptyText: 'Weight in grams',
                                    id: 'ccs_weight',
                                    name: 'ccs_weight',
                                    ttabIndex: 551,
                                    width: 100,
                                }]
                        }, 
                        {
                            xtype: 'compositefield',
                            hidden: true,
                            fieldLabel: 'SKU for Retailers',
                            combineErrors: false,
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'package_type_name',
                                    valueField: 'package_type_id',
                                    mode: 'local',
                                    id: 'rsb_package_type_id',
                                    name: 'rsb_package_type_id',
                                    allowBlank: true,
                                    forceSelection: true,
                                    fieldLabel: 'Package Type',
                                    emptyText: 'From Distributor as',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 559,
                                    store: packTypeStore,
                                    listeners: {
                                    }
                                }, {
                                    html: 'Contains'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'rs_nos',
                                    emptyText: 'Nos',
                                    name: 'rs_nos',
                                    fieldLabel: 'Nos',
                                    width: 100,
                                    tabIndex: 560,
                                    maxLength: 10,
                                    allowBlank: true
                                }, mkCombo({
                                    "type": 'mypha_medicineType',
                                    "value": "medicine_type_id",
                                    "display": "medicine_type_name",
                                    "name": "rs_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "Contains",
                                    "id": "rs_package_type_id",
                                    "hiddenName": "rs_package_type_id",
                                    "listeners": false,
                                    allowBlank: true,
                                    "width": 100,
                                    tabIndex: 561,
                                    "cx": "S_1",
                                    "hideTrigger": true,
                                    "readOnly": true
                                }),
                                {
                                    xtype: 'numberfield',
                                    hidden: true,
                                    id: 'rs_length',
                                    emptyText: 'Length',
                                    name: 'rs_length',
                                    fieldLabel: 'Length',
                                    width: 100,
                                    tabIndex: 562,
                                    maxLength: 10,
                                }, {
                                    xtype: 'textfield',
                                    hidden: true,
                                    id: 'rs_breadth',
                                    emptyText: 'Breadth',
                                    name: 'rs_breadth',
                                    fieldLabel: 'Breadth',
                                    width: 100,
                                    tabIndex: 563,
                                    maxLength: 10,
                                }, {
                                    xtype: 'textfield',
                                    hidden: true,
                                    id: 'rs_height',
                                    emptyText: 'Height',
                                    name: 'rs_height',
                                    fieldLabel: 'Height',
                                    width: 100,
                                    tabIndex: 564,
                                    maxLength: 10,
                                }, {
                                    xtype: 'numberfield',
                                    hidden: true,
                                    emptyText: 'Weight in grams',
                                    id: 'rs_weight',
                                    name: 'rs_weight',
                                    tabIndex: 565,
                                    width: 100,
                                    maxLength: 10
                                }]
                        }, {
                            xtype: 'compositefield',
                            fieldLabel: 'Distributor Sales',
                            combineErrors: false,
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'package_type_name',
                                    valueField: 'package_type_id',
                                    mode: 'local',
                                    id: 'dsb_package_type_id',
                                    name: 'dsb_package_type_id',
                                    allowBlank: false,
                                    forceSelection: true,
                                    fieldLabel: 'Package Type',
                                    emptyText: 'From CS as',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 566,
                                    store: packTypeStore,
                                    listeners: {
                                    }
                                }, {
                                    xtype: 'numberfield',
                                    id: 'ds_nos',
                                    emptyText: '',
                                    name: 'ds_nos',
                                    hidden: true,
                                    fieldLabel: 'Nos',
                                    width: 100,
                                    tabIndex: 567,
                                    maxLength: 10,
                                    allowBlank: true
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "ds_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "",
                                    "id": "ds_package_type_id",
                                    "hiddenName": "ds_package_type_id",
                                    "width": 100,
                                    "listeners": false,
                                    allowBlank: true,
                                    hidden: true,
                                    tabIndex: 568,
                                    "cx": "S_1",
                                    "hideTrigger": true,
                                    "readOnly": true
                                }),
                                {
                                    xtype: 'numberfield',
                                    hidden: true,
                                    id: 'ds_length',
                                    emptyText: 'Length',
                                    name: 'ds_length',
                                    fieldLabel: 'Length',
                                    width: 100,
                                    tabIndex: 569,
                                    maxLength: 10,
                                }, {
                                    xtype: 'textfield',
                                    hidden: true,
                                    id: 'ds_breadth',
                                    emptyText: 'Breadth',
                                    name: 'ds_breadth',
                                    fieldLabel: 'Breadth',
                                    width: 100,
                                    tabIndex: 570,
                                    maxLength: 10,
                                }, {
                                    xtype: 'textfield',
                                    hidden: true,
                                    id: 'ds_height',
                                    emptyText: 'Height',
                                    name: 'ds_height',
                                    fieldLabel: 'Height',
                                    width: 100,
                                    tabIndex: 571,
                                    maxLength: 10,
                                }, {
                                    xtype: 'numberfield',
                                    hidden: true,
                                    emptyText: 'Weight in grams',
                                    id: 'ds_weight',
                                    name: 'ds_weight',
                                    tabIndex: 572,
                                    width: 100,
                                    maxLength: 10
                                }]
                        }, {
                            xtype: 'compositefield',
                            labelAlign: 'top',
                            fieldLabel: 'Stokist Sale',
                            combineErrors: false,
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'package_type_name',
                                    valueField: 'package_type_id',
                                    mode: 'local',
                                    id: 'csb_package_type_id',
                                    name: 'csb_package_type_id',
                                    allowBlank: false,
                                    forceSelection: true,
                                    fieldLabel: 'Package Type',
                                    emptyText: 'Central Store Package',
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 573,
                                    store: packTypeStore,
                                    listeners: {
                                    }
                                }, {
                                    xtype: 'numberfield',
                                    id: 'cs_nos',
                                    emptyText: '',
                                    name: 'cs_nos',
                                    fieldLabel: 'Nos',
                                    width: 80,
                                    hidden: true,
                                    tabIndex: 574,
                                    maxLength: 10,
                                    //allowBlank: false
                                }, mkCombo({
                                    "type": 'mypha_productpackage_type',
                                    "value": "package_type_id",
                                    "display": "package_type_name",
                                    "name": "cs_package_type_id",
                                    "fieldLabel": "Package Type",
                                    "emptyText": "",
                                    "id": "cs_package_type_id",
                                    "hiddenName": "cs_package_type_id",
                                    "listeners": false,
                                    allowBlank: true,
                                    tabIndex: 575,
                                    hidden: true,
                                    "width": 80,
                                    "cx": "S_1",
                                    "hideTrigger": true,
                                    "readOnly": true
                                }),
                                {
                                    xtype: 'numberfield',
                                    hidden: true,
                                    id: 'cs_length',
                                    emptyText: 'Length',
                                    name: 'cs_length',
                                    fieldLabel: 'Length',
                                    width: 100,
                                    tabIndex: 576,
                                    maxLength: 10,
                                }, {
                                    xtype: 'textfield',
                                    hidden: true,
                                    id: 'cs_breadth',
                                    emptyText: 'Breadth',
                                    name: 'cs_breadth',
                                    fieldLabel: 'Breadth',
                                    width: 100,
                                    tabIndex: 577,
                                    maxLength: 10,
                                }, {
                                    xtype: 'textfield',
                                    hidden: true,
                                    id: 'cs_height',
                                    emptyText: 'Height',
                                    name: 'cs_height',
                                    fieldLabel: 'Height',
                                    width: 100,
                                    tabIndex: 578,
                                    maxLength: 10,
                                }, {
                                    xtype: 'numberfield',
                                    hidden: true,
                                    emptyText: 'Weight in grams',
                                    id: 'cs_weight',
                                    name: 'cs_weight',
                                    tabIndex: 579,
                                    width: 100,
                                    maxLength: 10
                                }]
                        }]
                }, {
                    layout: 'form',
                    columnWidth: .20,
                    labelAlign: 'top',
                    items: []
                }
            ]
        });
        return medicinePanel;
    };
    var addItem = function (itemid, dup, type) {
        var title = (itemid == 0 || dup == 'D') ? 'Create Drug Product' : 'Edit Drug Product';
        var medicine_form = viewMedicinePanel(itemid);
        var addMedicine_window = Ext.getCmp('addMedicine_window');
        if (Ext.isEmpty(addMedicine_window)) {
            var addMedicine_window = new Ext.Window({
                id: 'addMedicine_window',
                title: title,
                modal: true,
                layout: 'fit',
                width: winsize.width * .9,
                height: 500,
                resizable: false,
                items: [medicine_form],
                buttonAlign: 'left',
                fbar: [{
                        xtype: 'button',
                        text: 'Verify',
                        icon: IMAGE_BASE_PATH + '/default/icons/approve.png',
                        tabindex: 6,
                        align: 'right',
                        hidden: true,
                        id: 'drugPrdctVerify',
                        handler: function () {
                            if (itemid > 0) {
                                Ext.Ajax.request({
                                    url: modURL + '&op=verifyDrugProdct',
                                    method: 'POST',
                                    params: {
                                        itemid: itemid
                                    },
                                    success: function (response) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success === true && tmp.valid === true) {
                                            Application.example.msg('Success', tmp.message);

                                            if (itemid > 0) {
                                                addMedicine_window.close();
                                                Ext.getCmp('medicineproducts_grid').getView().refreshRow(
                                                        {
                                                            callback: function (record, options, success) {
                                                                var gridPanel = Ext.getCmp('medicineproducts_grid');
                                                                var index = gridPanel.store.find('ItemId', itemid);
                                                                gridPanel.getSelectionModel().selectRow(index);
                                                            }
                                                        }
                                                );
                                            }
                                        } else if (tmp.success === true && tmp.valid === false) {
                                            Application.example.msg('Notification', tmp.message);
                                            //Ext.Msg.alert("Notification.", tmp.message);

                                            if (itemid > 0) {
                                                addMedicine_window.close();
                                                Ext.getCmp('medicineproducts_grid').getView().refreshRow(
                                                        {
                                                            callback: function (record, options, success) {
                                                                var gridPanel = Ext.getCmp('medicineproducts_grid');
                                                                var index = gridPanel.store.find('ItemId', itemid);
                                                                gridPanel.getSelectionModel().selectRow(index);
                                                            }
                                                        }
                                                );
                                            }
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


                        }
                    }, {
                        xtype: 'checkbox',
                        checked: false,
                        id: 'prescription',
                        name: 'prescription',
                        inputValue: 1,
                        tabIndex: 540,
                        boxLabel: 'Doctor Prescription Required'
                    }, '->', {
                        text: 'Cancel',
                        tabIndex: 542,
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            addMedicine_window.close();
                        }
                    }, {
                        text: 'Save',
                        tabIndex: 541,
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            saveItemMaster(itemid, dup);
                        }
                    }]
            });
        }
        if (!Ext.isEmpty(itemid)) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            medicine_form.load({
                url: modURL + '&op=getItemMaster_EditData',
                params: {
                    'id': itemid,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                }, success: function (frm, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success == true) {
                        Ext.getCmp('least_package_type_id').getStore().load();
                        Ext.getCmp('least_package_type_id').setRawValue(tmp.data.least_package_type_name);
                        Ext.getCmp('ccsb_package_type_id').getStore().load({
                            params: {
                                stdpckl11: tmp.data.least_package_type_id,
                                stdpckl21: tmp.data.stdpckl21_package_type_id,
                                stdpckl31: tmp.data.stdpckl31_package_type_id,
                                stdpckl41: tmp.data.stdpckl41_package_type_id
                            }
                        });
                        //Ext.getCmp('cosb_package_type_id').getStore().load();
                        Ext.getCmp('cosb_package_type_id').setRawValue(tmp.data.cosb_package_type_name);
                        //Ext.getCmp('cos_package_type_id').getStore().load();
                        Ext.getCmp('cos_package_type_id').setRawValue(tmp.data.cos_package_type_name);
                        //Ext.getCmp('ccs_package_type_id').getStore().load();
                        Ext.getCmp('ccs_package_type_id').setRawValue(tmp.data.ccs_package_type_name);
                        //Ext.getCmp('ccsb_package_type_id').getStore().load();
                        Ext.getCmp('ccsb_package_type_id').setRawValue(tmp.data.ccsb_package_type_name);
                        //Ext.getCmp('rs_package_type_id').getStore().load();
                        Ext.getCmp('rs_package_type_id').setRawValue(tmp.data.rs_package_type_name);
                        //Ext.getCmp('rsb_package_type_id').getStore().load();
                        Ext.getCmp('rsb_package_type_id').setRawValue(tmp.data.rsb_package_type_name);
                        //Ext.getCmp('cs_package_type_id').getStore().load();
                        Ext.getCmp('cs_package_type_id').setRawValue(tmp.data.cs_package_type_name);
                        //Ext.getCmp('csb_package_type_id').getStore().load();
                        Ext.getCmp('csb_package_type_id').setRawValue(tmp.data.csb_package_type_name);
                        //Ext.getCmp('ds_package_type_id').getStore().load();
                        Ext.getCmp('ds_package_type_id').setRawValue(tmp.data.ds_package_type_name);
                        //Ext.getCmp('dsb_package_type_id').getStore().load();
                        Ext.getCmp('dsb_package_type_id').setRawValue(tmp.data.dsb_package_type_name);
                        Ext.getCmp('medicineMasters').getStore().load();
                        Ext.getCmp('medicineMasters').setRawValue(tmp.data.stit_itemName);
                        Ext.getCmp('HSN').getStore().load();
                        Ext.getCmp('HSN').setRawValue(tmp.data.stit_HSN_code);
                        if (tmp.data.prescription == 1) {
                            Ext.getCmp('prescription').setValue(true);
                        } else {
                            Ext.getCmp('prescription').setValue(false);
                        }
                        if (tmp.data.stit_salesUnit == 1) {
                            Ext.getCmp('stit_salesUnit').setValue(true);
                        } else {
                            Ext.getCmp('stit_salesUnit').setValue(false);
                        }
                        if (tmp.data.stit_stdPacking == 1) {
                            Ext.getCmp('stit_stdPacking').setValue(true);
                        } else {
                            Ext.getCmp('stit_stdPacking').setValue(false);
                        }
                        if (tmp.data.stdpckl1_nos > 0) {
                            //Application.MyphaMedProduct.Cache.aspLevel = 1;
                        }
                        if (tmp.data.stdpckl2_nos > 0) {
                            //Application.MyphaMedProduct.Cache.aspLevel = 2;
                            Ext.getCmp('astdpck1').show();
                        }
                        if (tmp.data.stdpckl3_nos > 0) {
                            //Application.MyphaMedProduct.Cache.aspLevel = 3;
                            Ext.getCmp('astdpck2').show();
                        }
                        if (tmp.data.stdpckl4_nos > 0) {
                            //Application.MyphaMedProduct.Cache.aspLevel = 4;
                            Ext.getCmp('astdpck3').show();
                        }
                        Ext.getCmp('drugPrdctVerify').show();
                    }
                }
            });
        }
        addMedicine_window.show();
        addMedicine_window.doLayout();
        addMedicine_window.center();
    };
    var medicineProductsGrid = function (id) {
        var medicineproducts_store = medicineproductsStore();
        var action = medicineGridAction();
        var ItemMaster_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'stit_itemName'
                }, {
                    type: 'string',
                    dataIndex: 'stit_category_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_brand_name'
                }, {
                    type: 'string',
                    dataIndex: 'stit_product_variant'
                }, {
                    type: 'string',
                    dataIndex: 'stit_quantity'
                }, {
                    type: 'string',
                    dataIndex: 'hsn_code'
                }, {
                    type: 'string',
                    dataIndex: 'least_package_type_name'
                }, {
                    type: 'list',
                    options: ['Active', 'Inactive'],
                    phpMode: true,
                    dataIndex: 'statusName'
                }, 
//                {
//                    type: 'string',
//                    dataIndex: 'imgCount'
//                },
                {
                    type: 'numeric',
                    dataIndex: 'tax'
                }, {
                    type: 'list',
                    options: ['Yes', 'No'],
                    phpMode: true,
                    dataIndex: 'isVerified'
                }
            ]
        });
        ItemMaster_filter.remote = true;
        ItemMaster_filter.autoReload = true;
        var medicineproducts_grid_panel = new Ext.grid.GridPanel(
                {
                    store: medicineproducts_store,
                    layout: 'fit',
                    frame: false,
                    border: false,
                    title: 'Drug Products',
                    plugins: [ItemMaster_filter],
                    id: id,
                    loadMask: true,
                    columns: [new Ext.grid.RowNumberer(),
                        {
                            header: 'SKU',
                            sortable: true,
                            hideable: true,
                            dataIndex: 'stit_SKU'
                        },
                        {
                            header: 'Product Name',
                            sortable: true,
                            hideable: true,
                            dataIndex: 'stit_itemName'
                        }, {
                            header: 'Quantity',
                            sortable: true,
                            hideable: true,
                            dataIndex: 'stit_quantity',
                            width: 50
                        }, {
                            header: 'Variant',
                            sortable: true,
                            hideable: true,
                            dataIndex: 'stit_product_variant',
                            width: 80
                        }, {
                            header: 'Drug Group',
                            sortable: true,
                            hideable: true,
                            dataIndex: 'stit_category_name'
                        }, {
                            header: 'Generic Name',
                            sortable: true,
                            hideable: true,
                            dataIndex: 'stit_brand_name',
                            width: 325
                        }, {
                            header: 'HSN Code',
                            id: 'hsn_code',
                            sortable: true,
                            hideable: true,
                            align: 'right',
                            dataIndex: 'hsn_code',
                            width: 80
                        },
                        {
                            header: 'Tax',
                            id: 'tax',
                            xtype: 'numbercolumn',
                            sortable: true,
                            hideable: true,
                            dataIndex: 'tax',
                            align: 'right',
                            width: 50
                        }, {
                            header: 'Least Package',
                            sortable: true,
                            hideable: true,
                            align: 'right',
                            dataIndex: 'least_package_type_name',
                            width: 80
                        }, {
                            header: 'Status',
                            hideable: true,
                            dataIndex: 'statusName',
                            align: 'right',
                            width: 50
                        }, {
                            header: 'Img',
                            hideable: true,
                            dataIndex: 'imgCount',
                            align: 'right',
                            width: 30,
                            tooltip: 'Image Count'
                        }, {
                            header: 'Is Verified',
                            hideable: true,
                            dataIndex: 'isVerified',
                            width: 50,
                            tooltip: 'Is Verified'
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
                                    medproductActionMenu.showAt(e.getXY());
                                }
                            }
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
                        viewready: updatePagination
                    },
                    tbar: [
                        /*     <?php if (user_access("mypha_medicinepro", "saveItemMaster")) { ?> */
                        {
                            xtype: 'button',
                            text: 'Create Drug Product',
                            tooltip: 'Create Drug Product',
                            iconCls: 'finascop_add',
                            handler: function () {
                                Application.MyphaMedProduct.Cache.aspLevel = 0;
                                addItem(0, '');
                            }
                        }
                        /*<?php } ?> */
                    ],
                    bbar: new Ext.PagingToolbar({
                        pageSize: recs_per_page,
                        store: medicineproducts_store,
                        displayInfo: true,
                        displayMsg: 'Displaying records {0} - {1} of {2}',
                        emptyMsg: "No records to display",
                        plugins: [ItemMaster_filter]
                    }),
                    stripeRows: true,
                    autoExpandColumn: ''
                }
        );
        return medicineproducts_grid_panel;
    };
    var saveItemMaster = function (ItemId, dup) {
        WinMask = new Ext.LoadMask(Ext.getCmp('medicinePanel').getEl());
        WinMask.show();
        var is_prescription = Ext.getCmp('prescription').getValue();
        var is_featured = Ext.getCmp('featured').getValue();
        var is_popular = Ext.getCmp('popular').getValue();
        var isstit_custInitiate = Ext.getCmp('stit_custInitiate').getValue();
        var featured = is_featured === true ? '1' : '0';
        var popular = is_popular === true ? '1' : '0';
        var prescription = is_prescription === true ? '1' : '0';
        var stit_custInitiate = isstit_custInitiate === true ? '1' : '0';
        var isstit_fixedB2BRates = Ext.getCmp('stit_fixedB2BRates').getValue();
        var stit_fixedB2BRates = isstit_fixedB2BRates === true ? '1' : '0';
        var is_stdPacking = Ext.getCmp('stit_stdPacking').getValue();
        var is_salesUnit = Ext.getCmp('stit_salesUnit').getValue();
        var stit_stdPacking = is_stdPacking === true ? '1' : '0';
        var stit_salesUnit = is_salesUnit === true ? '1' : '0';
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (Ext.getCmp('medicinePanel').getForm().isValid()) {
            var form_data = {
                stit_stdPacking: stit_stdPacking,
                stit_salesUnit: stit_salesUnit,
                stdpckl11_package_type_id: Ext.getCmp('stdpckl11_package_type_id').getValue(),
                stdpckl1_nos: Ext.getCmp('stdpckl1_nos').getValue(),
                stdpckl12_package_type_id: Ext.getCmp('stdpckl12_package_type_id').getValue(),
                stdpckl21_package_type_id: Ext.getCmp('stdpckl21_package_type_id').getValue(),
                stdpckl2_nos: Ext.getCmp('stdpckl2_nos').getValue(),
                stdpckl22_package_type_id: Ext.getCmp('stdpckl22_package_type_id').getValue(),
                stdpckl31_package_type_id: Ext.getCmp('stdpckl31_package_type_id').getValue(),
                stdpckl3_nos: Ext.getCmp('stdpckl3_nos').getValue(),
                stdpckl32_package_type_id: Ext.getCmp('stdpckl32_package_type_id').getValue(),
                stdpckl41_package_type_id: Ext.getCmp('stdpckl41_package_type_id').getValue(),
                stdpckl4_nos: Ext.getCmp('stdpckl4_nos').getValue(),
                stdpckl42_package_type_id: Ext.getCmp('stdpckl42_package_type_id').getValue(),
                dupitem: dup,
                item: Ext.getCmp('medicineMasters').getValue(),
                item_name: Ext.getCmp('medicineMasters').getRawValue(),
                id: ItemId,
                HSN: Ext.getCmp('HSN').getValue(),
                HSN_code: Ext.getCmp('HSN').getRawValue(),
                display_label: Ext.getCmp('stit_displaylabels').getValue(),
                GST: Ext.getCmp('GST').getValue(),
                stit_itemReturnTime: Ext.getCmp('stit_itemReturnTime').getValue(),
                description: Ext.getCmp('description').getValue(),
                stit_product_variant: Ext.getCmp('stit_product_variant').getValue(),
                med_generic_name: Ext.getCmp('med_generic_name').getValue(),
                med_dosage_form: Ext.getCmp('med_dosage_form').getValue(),
                med_drug_groupname: Ext.getCmp('med_drug_groupname').getValue(),
                cos_nos: Ext.getCmp('cos_nos').getValue(),
                cos_package_type_id: Ext.getCmp('cos_package_type_id').getValue(),
                cos_package_type_name: Ext.getCmp('cos_package_type_id').getRawValue(),
                cosb_package_type_id: Ext.getCmp('cosb_package_type_id').getValue(),
                cosb_package_type_name: Ext.getCmp('cosb_package_type_id').getRawValue(),
                cos_length: Ext.getCmp('cos_length').getValue(),
                cos_breadth: Ext.getCmp('cos_breadth').getValue(),
                cos_height: Ext.getCmp('cos_height').getValue(),
                cos_weight: Ext.getCmp('cos_weight').getValue(),
                cos_volume: Ext.getCmp('cos_length').getValue() * Ext.getCmp('cos_breadth').getValue() * Ext.getCmp('cos_height').getValue(),
                ccs_nos: Ext.getCmp('ccs_nos').getValue(),
                ccs_package_type_id: Ext.getCmp('ccs_package_type_id').getValue(),
                ccs_package_type_name: Ext.getCmp('ccs_package_type_id').getRawValue(),
                ccsb_package_type_id: Ext.getCmp('ccsb_package_type_id').getValue(),
                ccsb_package_type_name: Ext.getCmp('ccsb_package_type_id').getRawValue(),
                ccs_length: Ext.getCmp('ccs_length').getValue(),
                ccs_breadth: Ext.getCmp('ccs_breadth').getValue(),
                ccs_height: Ext.getCmp('ccs_height').getValue(),
                ccs_weight: Ext.getCmp('ccs_weight').getValue(),
                ccs_volume: Ext.getCmp('ccs_length').getValue() * Ext.getCmp('ccs_breadth').getValue() * Ext.getCmp('ccs_height').getValue(),
                rs_nos: Ext.getCmp('rs_nos').getValue(),
                rs_package_type_id: Ext.getCmp('rs_package_type_id').getValue(),
                rs_package_type_name: Ext.getCmp('rs_package_type_id').getRawValue(),
                rsb_package_type_id: Ext.getCmp('rsb_package_type_id').getValue(),
                rsb_package_type_name: Ext.getCmp('rsb_package_type_id').getRawValue(),
                rs_length: Ext.getCmp('rs_length').getValue(),
                rs_breadth: Ext.getCmp('rs_breadth').getValue(),
                rs_height: Ext.getCmp('rs_height').getValue(),
                rs_weight: Ext.getCmp('rs_weight').getValue(),
                rs_volume: Ext.getCmp('rs_length').getValue() * Ext.getCmp('rs_breadth').getValue() * Ext.getCmp('rs_height').getValue(),
                cs_nos: Ext.getCmp('cs_nos').getValue(),
                cs_package_type_id: Ext.getCmp('cs_package_type_id').getValue(),
                cs_package_type_name: Ext.getCmp('csb_package_type_id').getRawValue(),
                csb_package_type_id: Ext.getCmp('csb_package_type_id').getValue(),
                csb_package_type_name: Ext.getCmp('cs_package_type_id').getRawValue(),
                cs_length: Ext.getCmp('cs_length').getValue(),
                cs_breadth: Ext.getCmp('cs_breadth').getValue(),
                cs_height: Ext.getCmp('cs_height').getValue(),
                cs_weight: Ext.getCmp('cs_weight').getValue(),
                cs_volume: Ext.getCmp('cs_length').getValue() * Ext.getCmp('cs_breadth').getValue() * Ext.getCmp('cs_height').getValue(),
                ds_nos: Ext.getCmp('ds_nos').getValue(),
                ds_package_type_id: Ext.getCmp('ds_package_type_id').getValue(),
                ds_package_type_name: Ext.getCmp('ds_package_type_id').getRawValue(),
                dsb_package_type_id: Ext.getCmp('dsb_package_type_id').getValue(),
                dsb_package_type_name: Ext.getCmp('dsb_package_type_id').getRawValue(),
                ds_length: Ext.getCmp('ds_length').getValue(),
                ds_breadth: Ext.getCmp('ds_breadth').getValue(),
                ds_height: Ext.getCmp('ds_height').getValue(),
                ds_weight: Ext.getCmp('ds_weight').getValue(),
                ds_volume: Ext.getCmp('ds_length').getValue() * Ext.getCmp('ds_breadth').getValue() * Ext.getCmp('ds_height').getValue(),
                stit_category_name: Ext.getCmp('med_dosage_form').getValue(),
                stit_brand_name: Ext.getCmp('med_generic_name').getValue(),
                featured: featured,
                stit_quantity: Ext.getCmp('stit_quantity').getValue(),
                popular: popular,
                stit_custInitiate: stit_custInitiate,
                stitl1_optimumqty: Ext.getCmp('stitl1_optimumqty').getValue(),
                stitl2_optimumqty: Ext.getCmp('stitl2_optimumqty').getValue(),
                stitl3_optimumqty: Ext.getCmp('stitl3_optimumqty').getValue(),
                stit11_minimumqty: Ext.getCmp('stit11_minimumqty').getValue(),
                stit12_minimumqty: Ext.getCmp('stit12_minimumqty').getValue(),
                stit13_minimumqty: Ext.getCmp('stit13_minimumqty').getValue(),
                stit11_maximumqty: Ext.getCmp('stit11_maximumqty').getValue(),
                stit12_maximumqty: Ext.getCmp('stit12_maximumqty').getValue(),
                stit13_maximumqty: Ext.getCmp('stit13_maximumqty').getValue(),
                stii_csb: Ext.getCmp('stii_csb').getValue(),
                least_package_type_id: Ext.getCmp('least_package_type_id').getValue(),
                least_package_type_name: Ext.getCmp('least_package_type_id').getRawValue(),
                stii_csbretail: Ext.getCmp('stii_csbretail').getValue(),
                tstamp: t_stamp,
                stit_fixedB2BRates: stit_fixedB2BRates
            };
            var params = {
                action: 'Insert',
                module: 'mypha_product',
                op: 'saveItemMaster',
                id: ItemId,
                extrainfo: 'fsr'
            };
            Application.MyphaMedProduct.Cache.dup = dup;
            if (Ext.getCmp('medicineMasters').getValue() > 0) {
                APICall(params, Application.MyphaMedProduct.saveItemData, form_data);
            } else {
                Ext.MessageBox.alert('Notification', 'Choose valid medicine');
            }


        } else {
            Ext.MessageBox.alert('Notification', 'Please enter all required fields');
        }

        WinMask.hide();
    };
    return{
        Cache: {},
        initMedicineProducts: function () {
            var _currentStockPanelId = 'medicineproducts_grid';
            var _masterPanelBrand = Ext.getCmp(_currentStockPanelId);
            if (Ext.isEmpty(_masterPanelBrand)) {
                _masterPanelBrand = medicineProductsGrid(_currentStockPanelId);
                Application.UI.addTab(_masterPanelBrand);
                _masterPanelBrand.doLayout();
            } else {
                Application.UI.addTab(_masterPanelBrand);
            }


//            var panelId = 'medicineproducts_grid';
//            var medicineproducts_main = Ext.getCmp(panelId);
//            if (Ext.isEmpty(medicineproducts_main)) {
//                medicineproducts_main = medicineProductsGrid(panelId);
//            }
//            medicineproductsData();
//            Application.UI.addTab(medicineproducts_main);
//            medicineproducts_main.doLayout();
//            return medicineproducts_main;
        }, saveItemData: function () {
            var itemId = Ext.getCmp('itemId').getValue();
            var itemMaster_window = Ext.getCmp('addMedicine_window');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var is_prescription = Ext.getCmp('prescription').getValue();
            var is_featured = Ext.getCmp('featured').getValue();
            var is_popular = Ext.getCmp('popular').getValue();
            var isstit_custInitiate = Ext.getCmp('stit_custInitiate').getValue();
            var featured = is_featured === true ? '1' : '0';
            var popular = is_popular === true ? '1' : '0';
            var prescription = is_prescription === true ? '1' : '0';
            var stit_custInitiate = isstit_custInitiate === true ? '1' : '0';
            var isstit_fixedB2BRates = Ext.getCmp('stit_fixedB2BRates').getValue();
            var stit_fixedB2BRates = isstit_fixedB2BRates === true ? '1' : '0';

            var is_stdPacking = Ext.getCmp('stit_stdPacking').getValue();
            var is_salesUnit = Ext.getCmp('stit_salesUnit').getValue();
            var stit_stdPacking = is_stdPacking === true ? '1' : '0';
            var stit_salesUnit = is_salesUnit === true ? '1' : '0';
            /*
             
             */
            Ext.Ajax.request({
                url: modURL + '&op=saveItemMaster',
                method: 'POST',
                params: {
                    stit_stdPacking: stit_stdPacking,
                    stit_salesUnit: stit_salesUnit,
                    stit_package_type_id: Ext.getCmp('med_dosage_form').getValue(),
                    stdpckl11_package_type_id: Ext.getCmp('stdpckl11_package_type_id').getValue(),
                    stdpckl1_nos: Ext.getCmp('stdpckl1_nos').getValue(),
                    stdpckl12_package_type_id: Ext.getCmp('stdpckl12_package_type_id').getValue(),
                    stdpckl21_package_type_id: Ext.getCmp('stdpckl21_package_type_id').getValue(),
                    stdpckl2_nos: Ext.getCmp('stdpckl2_nos').getValue(),
                    stdpckl22_package_type_id: Ext.getCmp('stdpckl22_package_type_id').getValue(),
                    stdpckl31_package_type_id: Ext.getCmp('stdpckl31_package_type_id').getValue(),
                    stdpckl3_nos: Ext.getCmp('stdpckl3_nos').getValue(),
                    stdpckl32_package_type_id: Ext.getCmp('stdpckl32_package_type_id').getValue(),
                    stdpckl41_package_type_id: Ext.getCmp('stdpckl41_package_type_id').getValue(),
                    stdpckl4_nos: Ext.getCmp('stdpckl4_nos').getValue(),
                    stdpckl42_package_type_id: Ext.getCmp('stdpckl42_package_type_id').getValue(),
                    dupitem: Application.MyphaMedProduct.Cache.dup,
                    item: Ext.getCmp('medicineMasters').getValue(),
                    item_name: Ext.getCmp('medicineMasters').getRawValue(),
                    id: Ext.getCmp('itemId').getValue(),
                    HSN: Ext.getCmp('HSN').getValue(),
                    HSN_code: Ext.getCmp('HSN').getRawValue(),
                    GST: Ext.getCmp('GST').getValue(),
                    stit_itemReturnTime: Ext.getCmp('stit_itemReturnTime').getValue(),
                    display_label: Ext.getCmp('stit_displaylabels').getValue(),
                    description: Ext.getCmp('description').getValue(),
                    stit_product_variant: Ext.getCmp('stit_product_variant').getValue(),
                    med_generic_name: Ext.getCmp('med_generic_name').getValue(),
                    med_dosage_form: Ext.getCmp('med_dosage_form').getValue(),
                    med_drug_groupname: Ext.getCmp('med_drug_groupname').getValue(),
                    cos_nos: Ext.getCmp('cos_nos').getValue(),
                    cos_package_type_id: Ext.getCmp('cos_package_type_id').getValue(),
                    cos_package_type_name: Ext.getCmp('cos_package_type_id').getRawValue(),
                    cosb_package_type_id: Ext.getCmp('cosb_package_type_id').getValue(),
                    cosb_package_type_name: Ext.getCmp('cosb_package_type_id').getRawValue(),
                    cos_length: Ext.getCmp('cos_length').getValue(),
                    cos_breadth: Ext.getCmp('cos_breadth').getValue(),
                    cos_height: Ext.getCmp('cos_height').getValue(),
                    cos_weight: Ext.getCmp('cos_weight').getValue(),
                    cos_volume: Ext.getCmp('cos_length').getValue() * Ext.getCmp('cos_breadth').getValue() * Ext.getCmp('cos_height').getValue(),
                    ccs_nos: Ext.getCmp('ccs_nos').getValue(),
                    ccs_package_type_id: Ext.getCmp('ccs_package_type_id').getValue(),
                    ccs_package_type_name: Ext.getCmp('ccs_package_type_id').getRawValue(),
                    ccsb_package_type_id: Ext.getCmp('ccsb_package_type_id').getValue(),
                    ccsb_package_type_name: Ext.getCmp('ccsb_package_type_id').getRawValue(),
                    ccs_length: Ext.getCmp('ccs_length').getValue(),
                    ccs_breadth: Ext.getCmp('ccs_breadth').getValue(),
                    ccs_height: Ext.getCmp('ccs_height').getValue(),
                    ccs_weight: Ext.getCmp('ccs_weight').getValue(),
                    ccs_volume: Ext.getCmp('ccs_length').getValue() * Ext.getCmp('ccs_breadth').getValue() * Ext.getCmp('ccs_height').getValue(),
                    rs_nos: Ext.getCmp('rs_nos').getValue(),
                    rs_package_type_id: Ext.getCmp('rs_package_type_id').getValue(),
                    rs_package_type_name: Ext.getCmp('rs_package_type_id').getRawValue(),
                    rsb_package_type_id: Ext.getCmp('rsb_package_type_id').getValue(),
                    rsb_package_type_name: Ext.getCmp('rsb_package_type_id').getRawValue(),
                    rs_length: Ext.getCmp('rs_length').getValue(),
                    rs_breadth: Ext.getCmp('rs_breadth').getValue(),
                    rs_height: Ext.getCmp('rs_height').getValue(),
                    rs_weight: Ext.getCmp('rs_weight').getValue(),
                    rs_volume: Ext.getCmp('rs_length').getValue() * Ext.getCmp('rs_breadth').getValue() * Ext.getCmp('rs_height').getValue(),
                    cs_nos: Ext.getCmp('cs_nos').getValue(),
                    cs_package_type_id: Ext.getCmp('cs_package_type_id').getValue(),
                    cs_package_type_name: Ext.getCmp('cs_package_type_id').getRawValue(),
                    csb_package_type_id: Ext.getCmp('csb_package_type_id').getValue(),
                    csb_package_type_name: Ext.getCmp('csb_package_type_id').getRawValue(),
                    cs_length: Ext.getCmp('cs_length').getValue(),
                    cs_breadth: Ext.getCmp('cs_breadth').getValue(),
                    cs_height: Ext.getCmp('cs_height').getValue(),
                    cs_weight: Ext.getCmp('cs_weight').getValue(),
                    cs_volume: Ext.getCmp('cs_length').getValue() * Ext.getCmp('cs_breadth').getValue() * Ext.getCmp('cs_height').getValue(),
                    ds_nos: Ext.getCmp('ds_nos').getValue(),
                    ds_package_type_id: Ext.getCmp('ds_package_type_id').getValue(),
                    ds_package_type_name: Ext.getCmp('ds_package_type_id').getRawValue(),
                    dsb_package_type_id: Ext.getCmp('dsb_package_type_id').getValue(),
                    dsb_package_type_name: Ext.getCmp('dsb_package_type_id').getRawValue(),
                    ds_length: Ext.getCmp('ds_length').getValue(),
                    ds_breadth: Ext.getCmp('ds_breadth').getValue(),
                    ds_height: Ext.getCmp('ds_height').getValue(),
                    ds_weight: Ext.getCmp('ds_weight').getValue(),
                    ds_volume: Ext.getCmp('ds_length').getValue() * Ext.getCmp('ds_breadth').getValue() * Ext.getCmp('ds_height').getValue(),
                    stit_category_name: Ext.getCmp('med_dosage_form').getValue(),
                    stit_brand_name: Ext.getCmp('med_generic_name').getValue(),
                    featured: featured,
                    stit_quantity: Ext.getCmp('stit_quantity').getValue(),
                    popular: popular,
                    prescription: prescription,
                    stit_custInitiate: stit_custInitiate,
                    stitl1_optimumqty: Ext.getCmp('stitl1_optimumqty').getValue(),
                    stitl2_optimumqty: Ext.getCmp('stitl2_optimumqty').getValue(),
                    stitl3_optimumqty: Ext.getCmp('stitl3_optimumqty').getValue(),
                    stit11_minimumqty: Ext.getCmp('stit11_minimumqty').getValue(),
                    stit12_minimumqty: Ext.getCmp('stit12_minimumqty').getValue(),
                    stit13_minimumqty: Ext.getCmp('stit13_minimumqty').getValue(),
                    stit11_maximumqty: Ext.getCmp('stit11_maximumqty').getValue(),
                    stit12_maximumqty: Ext.getCmp('stit12_maximumqty').getValue(),
                    stit13_maximumqty: Ext.getCmp('stit13_maximumqty').getValue(),
                    stii_csb: Ext.getCmp('stii_csb').getValue(),
                    stii_csbretail: Ext.getCmp('stii_csbretail').getValue(),
                    least_package_type_id: Ext.getCmp('least_package_type_id').getValue(),
                    least_package_type_name: Ext.getCmp('least_package_type_id').getRawValue(),
                    tstamp: t_stamp,
                    stit_fixedB2BRates: stit_fixedB2BRates
                },
                success: function (response) {

                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success === true) {
                        //itemMaster_window.close();
                        if (itemId > 0) {
                            itemMaster_window.close();
                            Ext.getCmp('medicineproducts_grid').getView().refreshRow(
                                    {
                                        callback: function (record, options, success) {
                                            var gridPanel = Ext.getCmp('medicineproducts_grid');
                                            var index = gridPanel.store.find('ItemId', itemId);
                                            gridPanel.getSelectionModel().selectRow(index);
                                        }
                                    }
                            );
                        } else {
                            Ext.getCmp('medicineproducts_grid').getStore().load();
                            Ext.getCmp('medicinePanel').getForm().reset();
                        }

                        Application.example.msg('Success', tmp.msg);
                    } else {
                        Ext.MessageBox.alert("Error", tmp.msg);
                        //itemMaster_window.close();
                    }
                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', 'tmp.msg');
                }
            });
        }
    }
}();