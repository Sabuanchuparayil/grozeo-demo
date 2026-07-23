Application.Finascop_Stock = function () {
    //var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 18;
    var partytype = 0;
    var modURL = '?module=finascop_stock';
    var promodURL = '?module=products';
    var dateRange = parseInt(BETWEEN_DATES_TYPE_A);
    var my_marker;
    var total_mrp = 0;
    var current_type;
    var centerParam;
    var onGridResize = function (cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    };
    /*************MASTER DATA****************/

    var WinMask;

    var getWindowSize = function () {
        var winW = 0, winH = 0;
        if (document.body && document.body.offsetWidth)
        {
            winW = document.body.offsetWidth;
            winH = document.body.offsetHeight;
        }
        if (document.compatMode == 'CSS1Compat' && document.documentElement && document.documentElement.offsetWidth)
        {
            winW = document.documentElement.offsetWidth;
            winH = document.documentElement.offsetHeight;
        }
        if (window.innerWidth && window.innerHeight)
        {
            winW = window.innerWidth;
            winH = window.innerHeight;
        }
        return {width: winW, height: winH};
    };

    //var winsize = Ext.getBody().getViewSize();
    var winsize = getWindowSize();


    var purchaseVendorDetailsFormReset = function () {
        Ext.getCmp('stpa_Fname').setValue('');
        Ext.getCmp('stpa_Lname').setValue('');
        Ext.getCmp('stpa_Address').setValue('');
        Ext.getCmp('stpa_City').setValue('');
        Ext.getCmp('st_id').setValue('');
        Ext.getCmp('dst_Id').setValue('');
        Ext.getCmp('stpa_PINCODE').setValue('');
        Ext.getCmp('stpa_GSTIN').setValue('');
        Ext.getCmp('stpa_PanNo').setValue('');
        Ext.getCmp('stpa_ContactPerson').setValue('');

        Ext.getCmp('stpa_MobileNo').setValue('');
        Ext.getCmp('stpa_Email').setValue('');
        Ext.getCmp('visit_frequency').setValue('');
        Ext.getCmp('oncall_delivery').setValue('');


    }


    var groupStore = function () {
        var group_store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getStockGroups',
            method: 'post',
            loaded: false,
            fields: ['group_id', 'group_name', 'fqGroupName', 'parent_group'],
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function (store, options) {
                    store.loaded = false;
                }
            }
        });
        return group_store;
    };
    var loadGroup = function () {
        var group_grid = Ext.getCmp('group_grid');
        group_grid.getStore().removeAll();
        group_grid.getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    };

    var conversionStore = function () {
        var store = new Ext.data.JsonStore({
            fields: ['conversion_id', 'conversion_no', 'converted_on', 'consumed_items_count', 'produced_items_count', 'added_by'],
            url: modURL + '&op=getConversionList',
            method: 'post',
            totalProperty: 'totalCount',
            root: 'data',
            autoLoad: true,
            listeners: {
                beforeload: function (st, opt) {
                    st.baseParams.excludeIds = Ext.encode(excludePartyIds);
                }
            }

        });
        return store;
    };

    var consumptionStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            fields: ['consumed', {name: 'qty', type: 'number'}, 'item_id']
        });
        return store;

    };


    var productionStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            fields: ['produced', {name: 'qty', type: 'number'}, 'item_id']
        });
        return store;
    };

    var groupComboStore = function () {
        var group_store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getStockGroups',
            method: 'post',
            loaded: false,
            fields: ['group_id', 'group_name', 'fqGroupName', 'parent_group'],
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function (store, options) {
                    store.loaded = false;
                },
                load: function (store, records, options) {

                    var row = new Ext.data.Record.create([{
                            name: 'group_id',
                            name: 'group_name',
                                    name: 'parent_group'
                        }]);

                    var r = new row({
                        group_id: 0,
                        group_name: '',
                        parent_group: ''
                    });

                    store.insert(0, r);
                    store.loaded = true;

                }

            }
        });
        return group_store;
    };

    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    ;

    var itemGridAction = function () {
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
                },
                {
                    sortable: false,
                    tooltip: 'Convertable On',
                    hideIndex: 'convertable_on',
                    iconCls: 'finascop_convertable_off',
                    callback: function (grid, rec, row, col) {

                        rec.set('convertable_off', 0);
                        rec.set('convertable_on', 1);
                        var stit_Convertible = 0;
                        saveConvertable(rec.get('ItemId'), stit_Convertible);
                    }
                },
                {
                    sortable: false,
                    tooltip: 'Convertable Off',
                    hideIndex: 'convertable_off',
                    iconCls: 'finascop_convertable_on',
                    callback: function (grid, rec, row, col) {
                        //if (rec.get('convertable_off') == 1) {
                        rec.set('convertable_off', 1);
                        rec.set('convertable_on', 0);

                        var stit_Convertible = 1;
                        //}
                        saveConvertable(rec.get('ItemId'), stit_Convertible);

                    }
                },
                {
                    sortable: false,
                    tooltip: 'Item NOT listed in Sales',
                    hideIndex: 'list_in_sales_off',
                    iconCls: 'finascop_listin_sales_off',
                    callback: function (grid, rec, row, col) {

                        rec.set('list_in_sales_off', 1);
                        rec.set('list_in_sales_on', 0);
                        var stit_SalesEnabled = 1;
                        saveSalesEnabled(rec.get('ItemId'), stit_SalesEnabled);
                    }
                },
                {
                    sortable: false,
                    tooltip: 'Item Listed in Sale',
                    hideIndex: 'list_in_sales_on',
                    iconCls: 'finascop_listin_sales_on',
                    callback: function (grid, rec, row, col) {
                        if (rec.get('list_in_sales_off') == 1)
                        {
                            rec.set('list_in_sales_off', 0);
                            rec.set('list_in_sales_on', 1);
                            var stit_SalesEnabled = 0;
                            saveSalesEnabled(rec.get('ItemId'), stit_SalesEnabled);
                        }
                    }
                },
                {
                    sortable: false,
                    tooltip: 'Stock Disabled',
                    hideIndex: 'stock_disabled',
                    iconCls: 'finascop_stock_disable',
                    callback: function (grid, rec, row, col) {

                        rec.set('stock_disabled', 1);
                        rec.set('stock_enabled', 0);
                        var stit_StockEnabled = 1;
                        saveStockEnabled(rec.get('ItemId'), stit_StockEnabled);

                    }
                },
                {
                    sortable: false,
                    tooltip: 'Stock Enabled',
                    hideIndex: 'stock_enabled',
                    iconCls: 'finascop_stock_enable',
                    callback: function (grid, rec, row, col) {
                        if (rec.get('stock_disabled') == 1)
                        {
                            rec.set('stock_disabled', 0);
                            rec.set('stock_enabled', 1);

                            var stit_StockEnabled = 0;
                            saveStockEnabled(rec.get('ItemId'), stit_StockEnabled);
                        }
                    }
                },
                {
                    sortable: false,
                    tooltip: 'Branch Stock and Rate',
                    iconCls: 'finascop_hideicon',
                    //iconCls: 'finascop_branch_stock_rate',
                    callback: function (grid, rec, row, col) {

                        branchStockRate(rec.get('ItemId'), rec.get('ItemName'), rec.get('hsn_code'));

                    }
                },
                {
                    sortable: false,
                    tooltip: 'Item NOT listed in Purchase',
                    hideIndex: 'list_in_purchase_off',
                    iconCls: 'finascop_listnotin_purchase',
                    callback: function (grid, rec, row, col) {

                        rec.set('list_in_purchase_off', 1);
                        rec.set('list_in_purchase_on', 0);
                        var stit_PurchaseEnabled = 1;
                        savePurchaseEnabled(rec.get('ItemId'), stit_PurchaseEnabled);
                    }
                },
                {
                    sortable: false,
                    tooltip: 'Item Listed in Purchase',
                    hideIndex: 'list_in_purchase_on',
                    iconCls: 'finascop_listin_purchase',
                    callback: function (grid, rec, row, col) {
                        if (rec.get('list_in_purchase_off') == 1)
                        {
                            rec.set('list_in_purchase_off', 0);
                            rec.set('list_in_purchase_on', 1);
                            var stit_PurchaseEnabled = 0;
                            savePurchaseEnabled(rec.get('ItemId'), stit_PurchaseEnabled);
                        }
                    }
                },
                {
                    sortable: false,
                    tooltip: 'Intangible (Service)',
                    hideIndex: 'tangible_off',
                    iconCls: 'finascop_hideicon',
//                    iconCls: 'finascop_intangible',
                    callback: function (grid, rec, row, col) {

                        rec.set('tangible_off', 1);
                        rec.set('tangible_on', 0);
                        var stit_Tangible = 0;
                        saveTangibleEnabled(rec.get('ItemId'), stit_Tangible);

                    }
                },
                {
                    sortable: false,
                    tooltip: 'Tangible (Item)',
                    hideIndex: 'tangible_on',
                    iconCls: 'finascop_hideicon',
                    // iconCls: 'finascop_tangible',
                    callback: function (grid, rec, row, col) {
                        if (rec.get('tangible_off') == 1)
                        {
                            rec.set('tangible_off', 0);
                            rec.set('tangible_on', 1);

                            var stit_Tangible = 1;
                            saveTangibleEnabled(rec.get('ItemId'), stit_Tangible);
                        }
                    }
                }, {
                    tooltip: 'Upload Main Image',
                    iconCls: 'upload',
                    //handler: function (grid, rowIndex, colIndex) {
                    callback: function (grid, rec, row, col) {
                        console.log(rec);
                        var main_img = 1;
                        // var record = grid.store.getAt(rowIndex);
                        var product_id = rec.get('ItemId');
                        Ext.Ajax.request({
                            url: promodURL + '&op=getproductImage',
                            method: 'POST',
                            params: {
                                product_id: product_id,
                                main_img: main_img
                            },
                            success: function (res) {
                                var tmp = Ext.decode(res.responseText);
                                console.log("temp is -", tmp);
                                if (tmp.data != '')
                                {
                                    var img_url = tmp.data[0].image_url;
                                    Application.Products.uploadimageProduct(product_id, main_img, img_url);
                                } else
                                {
                                    var img_url = '';
                                    Application.Products.uploadimageProduct(product_id, main_img, img_url);

                                }
                            }
                        });

                    }
                },
                {
                    tooltip: 'Upload Additional Images',
                    iconCls: 'product_multiple_img',
                    callback: function (grid, rec, row, col) {
                        var main_img = 0;
                        var product_id = rec.get('ItemId');
                        Ext.Ajax.request({
                            url: promodURL + '&op=getproductImage',
                            method: 'POST',
                            params: {
                                product_id: product_id,
                                main_img: main_img
                            },
                            success: function (res) {
                                var tmp = Ext.decode(res.responseText);
                                console.log("temp is -", tmp);
                                var image_array = tmp.data;
                                Application.Products.uploadimageProduct(product_id, main_img, image_array);
                            }
                        });
                    }
                }, {
                    tooltip: 'Product@home',
                    iconCls: 'user_enabledtick',
                    callback: function (grid, rec, row, col) {
                        saveItemAtHome(grid, rec);
                    }
                }, /*{
                 sortable: false,
                 tooltip: 'Product@home',
                 hideIndex: 'tangible_on',
                 iconCls: 'user_enabledtick',
                 callback: function (grid, rec, row, col) {
                 if (rec.get('product_is_home') == 'Yes') {
                 rec.set('tangible_off', 0);
                 rec.set('tangible_on', 1);
                 saveItemAtHome(grid, rec);
                 }
                 }
                 },*/
                {
                    iconCls: 'my-icon107',
                    tooltip: 'Add Tags',
                    callback: function (grid, rec, row, col) {
                        Application.Products.addTagsWindow(rec.data.ItemId);
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

    var saveConvertable = function (ItemId, stit_Convertible) {
        Ext.Ajax.request({
            url: modURL + '&op=saveConvertableStatus',
            method: 'POST',
            params: {
                ItemId: ItemId,
                stit_Convertible: stit_Convertible
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success !== undefined && tmp.success === true)
                {
                    Ext.MessageBox.alert('status', tmp.msg);
                }
            },
            //submit failure
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
    var saveSalesEnabled = function (ItemId, stit_SalesEnabled) {
        Ext.Ajax.request({
            url: modURL + '&op=saveSalesEnabledStatus',
            method: 'POST',
            params: {
                ItemId: ItemId,
                stit_SalesEnabled: stit_SalesEnabled
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success !== undefined && tmp.success === true)
                {
                    Ext.MessageBox.alert('status', tmp.msg);
                }
            },
            //submit failure
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
    var saveStockEnabled = function (ItemId, stit_StockEnabled) {
        Ext.Ajax.request({
            url: modURL + '&op=saveStockEnabledStatus',
            method: 'POST',
            params: {
                ItemId: ItemId,
                stit_StockEnabled: stit_StockEnabled
            },
            success: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success !== undefined && tmp.success === true)
                {
                    Ext.MessageBox.alert('status', tmp.msg);
                }
            },
            //submit failure
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };

    var excludePartyIds = [];

    var branchStockRate = function (ItemId, ItemName, hsn_code) {
        var branchStockRateWindow = "branch_stock_rate_window";
        var branch_StockRate_window = Ext.getCmp(branchStockRateWindow);
        if (Ext.isEmpty(branch_StockRate_window))
        {
            var branch_stockrate_form = addBranchStockRateForm(ItemId, ItemName, hsn_code);

            branch_StockRate_window = new Ext.Window({
                id: 'branch_stock_rate_window',
                layout: 'fit',
                width: 650,
                iconCls: '',
                title: 'Branch Stock Rate',
                height: 360,
                plain: true,
                constrain: true,
                draggable: true,
                modal: true,
                resizable: false,
                items: [branch_stockrate_form],
                buttons: [
                    {
                        text: 'Save',
                        id: 'save_btn',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        iconCls: 'finascop_my-icon1',
                        handler: function () {
                            saveBranchStockRateDetails(ItemId);
                        }
                    },
                    {
                        text: 'Cancel',
                        id: 'Cancel_btns',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        iconCls: 'finascop_my-icon1',
                        handler: function () {
                            branch_StockRate_window.close();
                        }
                    }]
            });

        }
        if (!Ext.isEmpty(ItemId))
        {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            excludePartyIds = [];
            branch_stockrate_form.load({
                url: modURL + '&op=getBranchStockRate_EditData',
                params: {
                    id: ItemId,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (frm, action) {

                    var obj = Ext.decode(action.response.responseText);

                    if (obj.success != undefined && obj.success == true)
                    {

                        Ext.getCmp('branchStockRate_grid').getStore().loadData(obj.gridData);
                        for (var i in obj.gridData)
                        {
                            var id = obj.gridData[i].partyId;
                            excludePartyIds.push(id);
                            Ext.getCmp('tb_party').getStore().load();
                        }

                    }

                }
            });


        }


        branch_StockRate_window.doLayout();
        branch_StockRate_window.show(this);
        branch_StockRate_window.center();
    };

    var savePurchaseEnabled = function (ItemId, stit_PurchaseEnabled) {
        Ext.Ajax.request({
            url: modURL + '&op=savePurchaseEnabledStatus',
            method: 'POST',
            params: {
                ItemId: ItemId,
                stit_PurchaseEnabled: stit_PurchaseEnabled
            },
            success: function (response) {
                //submit success
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success !== undefined && tmp.success === true)
                {
                    Ext.MessageBox.alert('status', tmp.msg);
                }
            },
            //submit failure
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
    var changeStatus = function () {

        //changeStatus(grid,record,splash_slide_id,status);
        var grid = arguments[0];
        var record = arguments[1];
        var product_id = arguments[2];
        var product_is_home = arguments[3];
        Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status?', function (btn) {
            if (btn == 'yes')
            {
                if (Ext.isEmpty(product_id))
                {
                    if (Ext.isEmpty(product_is_home))
                    {
                        record.set('product_is_home', 'Yes');
                        grid.getView().refresh();
                        console.log(record.data.product_is_home);
                    } else
                    {
                        record.set('product_is_home', 'No');
                        grid.getView().refresh();
                        console.log(record.data.product_is_home);
                    }

                } else
                {

                    Ext.Ajax.request({
                        url: modURL + '&op=ishomeactiveitem',
                        method: 'POST',
                        params: {
                            product_id: product_id,
                            product_is_home: product_is_home
                        },
                        success: function (res) {
                            var tmp = Ext.decode(res.responseText);
                            if (tmp.success === true && tmp.valid === true)
                            {
                                var crrstatus = tmp.status;
                                record.set('product_is_home', crrstatus);
                                grid.getView().refresh();
                                Ext.MessageBox.alert('Success', 'Changed');
                            } else
                            {
                                Ext.MessageBox.alert('Error', 'Could not change the status');
                            }
                        }
                    });
                }
            }
        });
    };
    var saveItemAtHome = function (grid, rec) {
        var product_id = rec.data.ItemId;
        var product_is_home = rec.data.product_is_home;
        Ext.Ajax.request({
            url: modURL + '&op=checkisHomeItemCount',
            method: 'POST',
            params: {
                product_id: product_id
            },
            success: function (res) {
                var tmp = Ext.decode(res.responseText);
                var count = tmp.count;
                console.log(count);
                if (tmp.success && tmp.valid === true)
                {
                    if (count < 20)
                    {
                        changeStatus(grid, rec, product_id, product_is_home);
                    } else
                    {
                        if (product_is_home == 'Yes')
                        {
                            changeStatus(grid, rec, product_id, product_is_home);
                        } else
                        {
                            console.log("Hai");
                            Ext.MessageBox.alert('Notification', 'Count Exceeds 20');
                        }
                    }
                } else
                {
                    Ext.MessageBox.alert('Error', 'Could not change the status');
                }
            }
        });
    };
    var saveTangibleEnabled = function (ItemId, stit_Tangible) {
        Ext.Ajax.request({
            url: modURL + '&op=saveTangibleEnabledStatus',
            method: 'POST',
            params: {
                ItemId: ItemId,
                stit_Tangible: stit_Tangible
            },
            success: function (response) {
                //submit success
                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success !== undefined && tmp.success === true)
                {
                    Ext.MessageBox.alert('status', tmp.msg);
                }
            },
            //submit failure
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };


    var addBranchStockRateForm = function (ItemId, ItemName, hsn_code) {
        var PartyStore = partyStore();
        var PartyGrid_Store = partygridStore();

        var branch_stock_rate_Panel = new Ext.FormPanel({
            id: 'branchStockRatePanel',
            height: 360,
            frame: true,
            monitorValid: true,
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.45,
                            items: [{
                                    xtype: 'hidden',
                                    fieldLabel: 'Item Id',
                                    id: 'itemid',
                                    name: 'itemid',
                                    value: ItemId,
                                    anchor: '50%',
                                    allowBlank: false,
                                    readOnly: true
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.60,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Item Name',
                                    id: 'itemName',
                                    name: 'itemName',
                                    value: ItemName,
                                    anchor: '99%',
                                    allowBlank: false,
                                    readOnly: true,
                                    style: 'margin-bottom:5px;'
                                }]

                        },
                        {
                            layout: 'form',
                            columnWidth: 0.30,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'HSN Code',
                                    id: 'hsnCode',
                                    name: 'hsnCode',
                                    value: hsn_code,
                                    anchor: '99%',
                                    allowBlank: false,
                                    readOnly: true,
                                    style: 'margin-bottom:5px;'
                                }]
                        }

                    ]


                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.45,
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Common Rate',
                                    id: 'commonRate',
                                    name: 'commonRate',
                                    anchor: '99%',
                                    allowBlank: false,
                                    style: 'margin-bottom:5px; text-align:right',
                                    listeners: {
                                        afterrender: function (field) {
                                            Ext.defer(function () {
                                                field.focus(true, 100);
                                            }, 1);
                                        },
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER)
                                            {
                                                Ext.getCmp('tb_party').focus();
                                            }

                                        },
                                        blur: function (txt) {
                                            this.setValue(Ext.util.Format.number(this.getValue(), FINASCOP_CURRENCY_FORMAT));
                                        }
                                    }

                                }]
                        }]
                }, {
                    layout: 'column',
                    columnWidth: 1,
                    items: new Ext.grid.GridPanel(
                            {
                                ds: PartyGrid_Store,
                                layout: 'fit',
                                title: 'Party Rate',
                                height: '175',
                                region: 'center',
                                frame: false,
                                border: true,
                                id: 'branchStockRate_grid',
                                style: 'margin-bottom:5px;',
                                columns: [new Ext.grid.RowNumberer(),
                                    {
                                        header: 'Party',
                                        id: 'b_party',
                                        sortable: true,
                                        hideable: false,
                                        dataIndex: 'party',
                                        width: 40
                                    }, {
                                        header: 'Rate',
                                        id: 'b_rate',
                                        sortable: true,
                                        hideable: false,
                                        xtype: 'finascopcurrency',
                                        format: FINASCOP_CURRENCY_FORMAT,
                                        align: 'right',
                                        dataIndex: 'rate',
                                        width: 30
                                    },
                                    {
                                        xtype: 'actioncolumn',
                                        hideable: false,
                                        width: 30,
                                        items: [{
                                                sortable: false,
                                                getClass: function (v, meta, rec) {
                                                    return 'icon-del-table';
                                                },
                                                tooltip: 'Remove from list',
                                                handler: function (grid, rowIndex, colIndex) {
                                                    var record = grid.store.getAt(rowIndex);
                                                    removeBranchStockRate(record, grid, rowIndex);

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
                                tbar: [
                                    {html: '&nbsp;Party : &nbsp;'},
                                    {
                                        xtype: 'combo',
                                        store: PartyStore,
                                        id: 'tb_party',
                                        hiddenName: 'party',
                                        allowBlank: false,
                                        displayField: 'party',
                                        valueField: 'party_id',
                                        triggerAction: 'all',
                                        forceSelection: false,
                                        typeAhead: true,
                                        editable: 'true',
                                        mode: 'remote',
                                        autoLoad: true,
                                        minChars: 1,
                                        selectOnFocus: true,
                                        enableKeyEvents: true,
                                        autoSelect: false,
                                        listeners: {
                                            specialkey: function (field, e) {
                                                if (e.getKey() == e.ENTER)
                                                {
                                                    if (field.getRawValue() != '')
                                                    {
                                                        Ext.getCmp('tb_rate').focus();
                                                    } else
                                                    {
                                                        Ext.getCmp('branchStockRate_grid').getSelectionModel().selectRow(0);
                                                        Ext.getCmp('branchStockRate_grid').getView().focusRow(0);
                                                    }

                                                }
                                            }

                                        }
                                    },
                                    {html: '&nbsp;Rate : &nbsp;'},
                                    {
                                        xtype: 'numberfield',
                                        id: 'tb_rate',
                                        name: 'tb_rate',
                                        allowBlank: false,
                                        listeners: {
                                            specialkey: function (field, e) {
                                                if (e.getKey() == e.ENTER)
                                                {
                                                    addPartyRate();
                                                }

                                            }

                                        }
                                    },
                                    {
                                        style: 'margin-left:5px;',
                                        xtype: 'button',
                                        iconCls: 'finascop_add',
                                        text: 'Add',
                                        id: 'tb_addButton',
                                        name: 'tb_addButton',
                                        listeners: {
                                            click: function () {

                                                addPartyRate();

                                            }

                                        }
                                    }
                                ],
                                listeners: {
                                    keydown: function (e)
                                    {
                                        if ((e.getCharCode() == 99) || (e.getCharCode() == 67))
                                        {
                                            var grid = Ext.getCmp('branchStockRate_grid');
                                            var selected = grid.getSelectionModel().getSelected();
                                            var row = grid.getStore().indexOf(selected);
                                            var record = grid.getStore().getAt(row);
                                            removeBranchStockRate(record, grid, row);
                                        } else if (e.getCharCode() == e.ENTER)
                                        {
                                            Ext.getCmp('min_stock').focus();

                                        }
                                    }
                                }
                            }
                    )

                },
                {
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 0.50,
                            style: 'margin-top:5px;',
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Minimum Stock',
                                    id: 'min_stock',
                                    name: 'min_stock',
                                    anchor: '99%',
                                    allowBlank: false,
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER)
                                            {
                                                Ext.getCmp('curr_stock').focus();
                                            }

                                        }

                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.50,
                            style: 'margin-top:5px;',
                            items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Current Stock',
                                    id: 'curr_stock',
                                    name: 'curr_stock',
                                    anchor: '99%',
                                    labelAlign: 'right',
                                    allowBlank: false,
                                    listeners: {
                                        specialkey: function (field, e) {
                                            if (e.getKey() == e.ENTER)
                                            {
                                                var itemid = Ext.getCmp('itemid').getValue();
                                                saveBranchStockRateDetails(itemid);
                                            }

                                        }

                                    }

                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 0.50,
                            style: 'margin-top:5px;',
                            items: [{
                                    xtype: 'hidden',
                                    id: 'hide_curr_stock',
                                    name: 'hide_curr_stock',
                                    anchor: '99%',
                                    readOnly: true
                                }]
                        }, {
                            xtype: 'hidden',
                            id: 'integrity_key',
                            name: 'integrity_key',
                            readOnly: true
                        }
                    ]
                }

            ]

        });
        return branch_stock_rate_Panel;
    };

    var removeBranchStockRate = function (record, grid, rowIndex)
    {
        Ext.Msg.confirm('Confirmation', 'Do you really want to remove this?', function (btn) {
            if (btn == 'yes')
            {
                grid.store.removeAt(rowIndex);
                grid.getView().refresh();
                excludePartyIds.remove(record.data.partyId);
                Ext.getCmp('tb_party').reset();
                Ext.getCmp('tb_party').focus();
            }

        });


    };

    var addPartyRate = function () {
        var tb_party = Ext.getCmp('tb_party').getValue();
        var tb_rate = Ext.getCmp('tb_rate').getValue();
        if ((tb_party != '') && (tb_rate != ''))
        {
            excludePartyIds.push(Ext.getCmp('tb_party').getValue());

            var row = new Ext.data.Record.create([{
                    name: 'partyId',
                    name: 'party',
                            name: 'rate'
                }]);

            var r = new row({
                partyId: Ext.getCmp('tb_party').getValue(),
                party: Ext.getCmp('tb_party').getRawValue(),
                rate: Ext.getCmp('tb_rate').getValue()});

            Ext.getCmp('branchStockRate_grid').getStore().insert(0, r);

            var PartyStore = Ext.getCmp('tb_party').getStore();
            Ext.getCmp('tb_party').reset;
            Ext.getCmp('tb_party').lastQuery = '';
            Ext.getCmp('tb_party').lastQuery = null;
            PartyStore.baseParams = {};
            Ext.getCmp('tb_party').setRawValue("");
            Ext.getCmp('tb_party').reset();
            Ext.getCmp('tb_party').focus();
            PartyStore.removeAll();

            PartyStore.load({
                callback: function (record, options, success) {
                    Ext.getCmp('tb_party').setRawValue("");
                    Ext.getCmp('tb_rate').setRawValue("");
                    Ext.getCmp('tb_rate').reset();
                    Ext.getCmp('tb_party').focus();
                }
            });

        } else
        {

            Ext.MessageBox.alert("Notification", 'Rate Field Cannot be empty', function (btn) {
                Ext.getCmp('tb_rate').focus();
            });

        }
    };

    var partyStore = function () {
        var partyStore = new Ext.data.JsonStore({
            autoLoad: true,
            id: 'party_store',
            url: modURL + '&op=getParty',
            method: 'post',
            fields: ['party_id', 'party'],
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function (st, opt) {
                    st.baseParams.excludeIds = Ext.encode(excludePartyIds);
                },
                load: function (ob, records, options)
                {
                    var recordSelected = Ext.getCmp('tb_party').getStore().getAt(0);
                    Ext.getCmp('tb_party').setValue(recordSelected.get('party_id'));
                    Ext.getCmp('tb_party').setRawValue(recordSelected.get('party'));
                },
                remove: function (obj, record, index) {
                    Ext.getCmp('tb_party').lastQuery = null;
                }

            }

        });
        return partyStore;
    };

    var partygridStore = function () {
        var partyStore = new Ext.data.JsonStore({
            autoLoad: true,
            id: 'party_Gridstore',
            method: 'post',
            fields: ['partyId', 'party', {name: 'rate', type: 'float'}]
        });
        return partyStore;
    };

    // saving branch stock rate data //

    var saveBranchStockRateDetails = function (ItemId) {
        WinMask = new Ext.LoadMask(Ext.getCmp('branchStockRatePanel').getEl());
        WinMask.show();
        var grid = Ext.getCmp('branchStockRate_grid');
        var grid_store = grid.getStore();
        var data = Ext.pluck(grid_store.getRange(), 'data');
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (!Ext.isEmpty(data) &&
                !Ext.isEmpty(Ext.getCmp('commonRate').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('min_stock').getValue()) &&
                !Ext.isEmpty(Ext.getCmp('curr_stock').getValue()))
        {

            var form_data = {
                party_data: data,
                commonRate: Ext.getCmp('commonRate').getValue(),
                min_stock: Ext.getCmp('min_stock').getValue(),
                curr_stock: Ext.getCmp('curr_stock').getValue(),
                hide_curr_stock: Ext.getCmp('hide_curr_stock').getValue(),
                partyId: Ext.getCmp('tb_party').getValue(),
                ItemId: Ext.getCmp('itemid').getValue(),
                integrity_key: Ext.getCmp('integrity_key').getValue(),
                tstamp: t_stamp
            };
            var params = {
                action: 'Insert',
                module: 'finascop_stock',
                op: 'setBranchStockRateData',
                id: ItemId,
                extrainfo: 'fsr'
            };
            APICall(params, Application.Finascop_Stock.saveBranchStockRateData, form_data);

        } else
        {
            Ext.MessageBox.alert('Error', 'Check the required fields');
        }
        WinMask.hide();
    };

    // saving branch stock rate data ends //


    var viewItemPanel = function () {
        // var SKU=function()
        // {
        //     var stit_SKU=Ext.getCmp('pdt_brand').getRawValue()+" "+Ext.getCmp('item').getRawValue()+" "+Ext.getCmp('stit_product_variant').getValue()+" "+Ext.getCmp('stit_quantity').getValue();
        //     Ext.getCmp('stit_SKU').setValue(stit_SKU);
        // }

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
        var itemMastergroupStore = function () {
            var group_store = new Ext.data.JsonStore({
                autoLoad: true,
                url: modURL + '&op=getItemMasterStockGroups',
                method: 'post',
                loaded: false,
                fields: ['group_id', 'group_name', 'parent_group'],
                totalProperty: 'totalCount',
                root: 'data',
                listeners: {
                    beforeload: function (store, options) {
                        store.loaded = false;
                    },
                    load: function (store, records, options) {

                        var row = new Ext.data.Record.create([{
                                name: 'group_id',
                                name: 'group_name',
                                        name: 'parent_group'
                            }]);

                        var r = new row({
                            group_id: 0,
                            group_name: '',
                            parent_group: ''
                        });

                        store.insert(0, r);
                        store.loaded = true;

                    }

                }
            });
            return group_store;
        };



        var itemMaster_GroupStore = itemMastergroupStore();
        var hsnStore = hsnStore();
        var itemPanel = new Ext.FormPanel({
            id: 'itemPanel',
            // height:400,
            autoScroll: true,
            frame: true,
            monitorValid: true,
            items: [{
                    xtype: 'hidden',
                    fieldLabel: 'Item Id',
                    id: 'itemId',
                    name: 'itemId',
                    anchor: '95%',
                    allowBlank: false
                }, mkCombo({
                    "type": 'brm_subcategory',
                    "value": "sub_category_id",
                    "display": "sub_category",
                    "name": "product_category",
                    "fieldLabel": "Category",
                    "emptyText": "Select Product Category..",
                    "id": "product_category",
                    "listeners": false,
                    tabIndex: 1,
                    anchor: '95%',
                    "cx": "S_1"
                }), mkCombo({
                    "type": 'mst_brands',
                    "value": "brand_id",
                    "display": "brand_name",
                    "name": "pdt_brand",
                    "fieldLabel": "Brand",
                    "emptyText": "Select Brand..",
                    "id": "pdt_brand",
                    "listeners": false,
                    tabIndex: 2,
                    anchor: '95%',
                    "cx": "S_1"

                }) /*{
                 layout: 'column',
                 items: [{
                 layout: 'form',
                 columnWidth: 0.96,
                 items: []
                 }, {
                 layout: 'form',
                 columnWidth: 0.04,
                 items: {
                 xtype: 'button',
                 tooltip: 'Add new brand',
                 iconCls: 'add_feild',
                 handler: function () {
                 Application.Products.addNewBrand();
                 }
                 }
                 }]
                 }*/, mkCombo({
                    "type": 'finascop_stock_itemmastername',
                    "value": "itemname_id",
                    "display": "item_name",
                    "name": "item",
                    "fieldLabel": "Item",
                    "emptyText": "Select Item Name",
                    "id": "item",
                    "listeners": false,
                    tabIndex: 3,
                    anchor: '95%',
                    "cx": "S_1"
                }), {
                    xtype: 'textfield',
                    fieldLabel: 'Variant',
                    id: 'stit_product_variant',
                    name: 'stit_product_variant',
                    anchor: '95%',
                    maxLength: 250,
                    tabIndex: 4,
                    allowBlank: false
                },
                // {
                //     xtype: 'textfield',
                //     fieldLabel: 'SKU',
                //     id: 'stit_SKU',
                //     name: 'stit_SKU',
                //     anchor: '95%',
                //     tabIndex: 6,
                //     allowBlank: false,
                //     readOnly:true
                // },
                {
                    layout: 'column',
                    items: [{
                            layout: 'form',
                            columnWidth: 0.5,
                            items: [
                                {
                                    xtype: 'checkbox',
                                    checked: true,
                                    id: 'featured',
                                    name: 'featured',
                                    inputValue: 1,
                                    tabIndex: 7,
                                    fieldLabel: 'Featured'
                                }
                            ]
                        }, {
                            layout: 'form',
                            columnWidth: 0.5,
                            items: [
                                {
                                    xtype: 'checkbox',
                                    checked: false,
                                    id: 'popular',
                                    name: 'popular',
                                    inputValue: 1,
                                    tabIndex: 8,
                                    fieldLabel: 'Popular'
                                }
                            ]
                        }]
                }, {
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
                    tabIndex: 9,
                    minChars: 2, listeners: {
                        select: function (index, val) {
                            Ext.getCmp('GST').setValue(val.data.gst_percent);
                        }
                    }
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'GST / VAT %',
                    id: 'GST',
                    name: 'GST',
                    readOnly: true,
                    anchor: '95%',
                    tabIndex: 10,
                    allowBlank: false
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'MRP',
                    id: 'MRP',
                    name: 'MRP',
                    anchor: '95%',
                    tabIndex: 11,
                    hidden: true,
                    listeners: {
                        blur: function (txt) {
                            txt.setValue(Ext.util.Format.number(txt.getValue(), FINASCOP_CURRENCY_FORMAT));
                        }
                    }
                }, {
                    xtype: 'numberfield',
                    id: 'pdt_sale_rate',
                    name: 'pdt_sale_rate',
                    allowNegative: false,
                    allowDecimals: true,
                    fieldLabel: 'Sale Rate',
                    anchor: '95%',
                    tabIndex: 12,
                    msgTarget: "qtip",
                    hidden: true
                },
                {
                    xtype: 'combo',
                    fieldLabel: 'Group',
                    id: 'itemgroup',
                    hiddenName: 'itemgroup',
                    anchor: '95%',
                    tabIndex: 13,
                    hidden: true,
                    displayField: 'parent_group',
                    valueField: 'group_id',
                    store: itemMaster_GroupStore,
                    triggerAction: 'all',
                    forceSelection: true,
                    typeAhead: true
                },
                {
                    xtype: 'textarea',
                    fieldLabel: 'Short Description',
                    id: 'description',
                    name: 'description',
                    anchor: '95%',
                    maxLength: 1000,
                    tabIndex: 14,
                    allowBlank: false,
                    height: 100
                }, {
                    xtype: 'templateeditormce',
                    fieldLabel: 'Long Description',
                    anchor: '95%',
                    id: 'stit_long_description',
                    name: 'stit_long_description',
                    maxLength: 4000,
                    height: 305,
                    tabIndex: 15,
                    listeners: {
                    }
                }, {
                    xtype: 'fieldset',
                    title: 'Package Details',
                    columnWidth: 1,
                    id: 'package_details',
                    items: [
                        mkCombo({
                            "type": 'package_type',
                            "value": "package_type_id",
                            "display": "package_type_name",
                            "name": "pdt_package_type_id",
                            "fieldLabel": "Package Type",
                            "emptyText": "Select Package Type..",
                            "id": "pdt_package_type_id",
                            "hiddenName": "pdt_package_type_id",
                            "listeners": false,
                            tabIndex: 16,
                            anchor: '95%',
                            "cx": "S_1"
                        }), {
                            xtype: 'compositefield',
                            fieldLabel: 'Size',
                            id: 'item_size',
                            combineErrors: false,
                            items: [
                                {
                                    xtype: 'numberfield',
                                    id: 'item_length',
                                    emptyText: 'Length',
                                    name: 'item_length',
                                    fieldLabel: 'Length',
                                    width: 100,
                                    tabIndex: 17,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'cm', border: false, width: 15, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'textfield',
                                    id: 'item_breadth',
                                    emptyText: 'Breadth',
                                    name: 'item_breadth',
                                    fieldLabel: 'Breadth',
                                    width: 100,
                                    tabIndex: 18,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'cm', border: false, width: 15, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'textfield',
                                    id: 'item_height',
                                    emptyText: 'Height',
                                    name: 'textfield',
                                    fieldLabel: 'Height',
                                    width: 100,
                                    tabIndex: 19,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'cm', border: false, width: 15, style: 'margin:5px 5px 0 -3px;'
                                }
                            ]
                        }, {
                            xtype: 'compositefield',
                            fieldLabel: 'Weight',
                            id: 'item_weightingrams',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    emptyText: 'Weight in grams',
                                    id: 'item_weight',
                                    name: 'item_weight',
                                    tabIndex: 20,
                                    width: 200,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'grams', border: false, style: 'margin:5px 5px 0 -3px;'}]
                        }
                    ]}, {
                    xtype: 'fieldset',
                    title: 'Stock Levels',
                    columnWidth: .75,
                    id: 'stock_details',
                    items: [{
                            xtype: 'compositefield',
                            fieldLabel: 'Level 1',
                            id: 'level1_details',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stitl1_optimumqty',
                                    emptyText: 'Optimum Qty',
                                    name: 'stitl1_optimumqty',
                                    fieldLabel: 'Optimum Qty',
                                    width: 100,
                                    tabIndex: 21,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit11_minimumqty',
                                    emptyText: 'Minimum Qty',
                                    name: 'stit11_minimumqty',
                                    fieldLabel: 'Minimum Qty',
                                    width: 100,
                                    tabIndex: 22,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit11_maximumqty',
                                    emptyText: 'Maximium Qty',
                                    name: 'stit11_maximumqty',
                                    fieldLabel: 'Maximium Qty',
                                    width: 100,
                                    tabIndex: 23,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                },
                                {html: 'Central Stock Buffer %', border: false, width: 150, style: 'margin:5px 5px 0 -3px;'}
                            ]
                        }, {
                            xtype: 'compositefield',
                            fieldLabel: 'Level 2',
                            id: 'level2_details',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stitl2_optimumqty',
                                    emptyText: 'Optimum Qty',
                                    name: 'stitl2_optimumqty',
                                    fieldLabel: 'Optimum Qty',
                                    width: 100,
                                    tabIndex: 24,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit12_minimumqty',
                                    emptyText: 'Minimum Qty',
                                    name: 'stit12_minimumqty',
                                    fieldLabel: 'Mininimum Qty',
                                    width: 100,
                                    tabIndex: 25,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit12_maximumqty',
                                    emptyText: 'Maximium Qty',
                                    name: 'stit12_maximumqty',
                                    fieldLabel: 'Maximium Qty',
                                    width: 100,
                                    tabIndex: 26,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: '',
                                    id: 'stii_csb',
                                    name: 'stii_csb',
                                    anchor: '95%',
                                    emptyText: 'Central Stock Buffer %',
                                    tabIndex: 30,
                                    width: 100,
                                    maxValue: 100,
                                    allowBlank: false
                                }]
                        }, {
                            xtype: 'compositefield',
                            fieldLabel: 'Level 3',
                            id: 'level3_details',
                            combineErrors: false,
                            items: [{
                                    xtype: 'numberfield',
                                    id: 'stitl3_optimumqty',
                                    emptyText: 'Optimum Qty',
                                    name: 'stitl3_optimumqty',
                                    fieldLabel: 'Optimum Qty',
                                    width: 100,
                                    tabIndex: 27,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit13_minimumqty',
                                    emptyText: 'Minimum Qty',
                                    name: 'stit13_minimumqty',
                                    fieldLabel: 'Minimum Qty',
                                    width: 100,
                                    tabIndex: 28,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                }, {
                                    xtype: 'numberfield',
                                    id: 'stit13_maximumqty',
                                    emptyText: 'Maximium Qty',
                                    name: 'stit13_maximumqty',
                                    fieldLabel: 'Maximium Qty',
                                    width: 100,
                                    tabIndex: 29,
                                    maxLength: 10,
                                    allowBlank: false
                                }, {html: 'nos', border: false, width: 18, style: 'margin:5px 5px 0 -3px;'
                                },
                            ]
                        }


                    ]},
            ]

        });
        return itemPanel;
    };

    var addItem = function (itemid, dup) {
        var title = (itemid == 0 || dup == 'D') ? 'Add Item' : 'Edit Item';
        var item_form = viewItemPanel();
        var addItem_window = Ext.getCmp('addItem_window');
        if (Ext.isEmpty(addItem_window))
        {
            var addItem_window = new Ext.Window({
                id: 'addItem_window',
                title: title,
                //iconCls: 'finascop_additem',
                modal: true,
                layout: 'fit',
                width: 730,
                height: 400,
                shadow: false,
                resizable: false,
                items: [item_form],
                buttons: [{
                        text: 'Save',
                        tabIndex: 20,
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            saveItemMaster(itemid, dup);
                        }
                    }, {
                        text: 'Cancel',
                        tabIndex: 21,
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            addItem_window.close();
                        }
                    }]
            });
        }
        if (!Ext.isEmpty(itemid))
        {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            item_form.load({
                url: modURL + '&op=getItemMaster_EditData',
                params: {
                    'id': itemid,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                }, success: function (frm, action) {
                    var tmp = Ext.decode(action.response.responseText);

                    if (tmp.success == true)
                    {
                        Ext.getCmp('pdt_package_type_id').getStore().load();
                        Ext.getCmp('pdt_package_type_id').setRawValue(tmp.data.stit_package_type_namme);
                        Ext.getCmp('item').getStore().load();
                        Ext.getCmp('item').setRawValue(tmp.data.stit_itemName);
                        Ext.getCmp('pdt_brand').getStore().load();
                        Ext.getCmp('pdt_brand').setRawValue(tmp.data.stit_brand_name);
                        Ext.getCmp('product_category').getStore().load();
                        Ext.getCmp('product_category').setRawValue(tmp.data.stit_category_name);
                        Ext.getCmp('HSN').getStore().load();
                        Ext.getCmp('HSN').setRawValue(tmp.data.stit_HSN_code);
                        Ext.getCmp('stit_long_description').setValue(tmp.data.stit_long_description);
                    }
                }
            });


        }
        addItem_window.show();
        addItem_window.doLayout();
        addItem_window.center();
    };

    // saving Item master data //

    var saveItemMaster = function (ItemId, dup) {
        WinMask = new Ext.LoadMask(Ext.getCmp('itemPanel').getEl());
        WinMask.show();
        var is_featured = Ext.getCmp('featured').getValue();
        var is_popular = Ext.getCmp('popular').getValue();
        var ItemVolume;
        var length = Ext.getCmp('item_length').getValue();
        var breadth = Ext.getCmp('item_breadth').getValue();
        var height = Ext.getCmp('item_height').getValue();
        ItemVolume = length * breadth * height;
        var featured = is_featured === true ? '1' : '0';
        var popular = is_popular === true ? '1' : '0';
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (Ext.getCmp('stit_long_description').getValue().length <= 4000)
        {
            if (Ext.getCmp('itemPanel').getForm().isValid())
            {
                var form_data = {
                    dupitem: dup,
                    item: Ext.getCmp('item').getValue(),
                    item_name: Ext.getCmp('item').getRawValue(),
                    id: ItemId,
                    HSN: Ext.getCmp('HSN').getValue(),
                    HSN_code: Ext.getCmp('HSN').getRawValue(),
                    GST: Ext.getCmp('GST').getValue(),
                    MRP: Ext.getCmp('MRP').getValue(),
                    itemgroup: Ext.getCmp('itemgroup').getValue(),
                    description: Ext.getCmp('description').getValue(),
                    stit_product_variant: Ext.getCmp('stit_product_variant').getValue(),
                    pdt_package_type_id: Ext.getCmp('pdt_package_type_id').getValue(),
                    stit_package_type_namme: Ext.getCmp('pdt_package_type_id').getRawValue(),
                    product_category: Ext.getCmp('product_category').getValue(),
                    stit_category_name: Ext.getCmp('product_category').getRawValue(),
                    pdt_brand: Ext.getCmp('pdt_brand').getValue(),
                    stit_brand_name: Ext.getCmp('pdt_brand').getRawValue(),
                    featured: featured,
                    pdt_sale_rate: Ext.getCmp('pdt_sale_rate').getValue(),
                    item_length: Ext.getCmp('item_length').getValue(),
                    item_breadth: Ext.getCmp('item_breadth').getValue(),
                    item_height: Ext.getCmp('item_height').getValue(),
                    item_weight: Ext.getCmp('item_weight').getValue(),
                    stit_item_volume: ItemVolume,
                    stit_long_description: Ext.getCmp('stit_long_description').getValue(),
                    stit_quantity: Ext.getCmp('stit_quantity').getValue(),
                    popular: popular,
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
                    tstamp: t_stamp
                };
                var params = {
                    action: 'Insert',
                    module: 'finascop_stock',
                    op: 'saveItemMaster',
                    id: ItemId,
                    extrainfo: 'fsr'
                };
                Application.Finascop_Stock.Cache.dup = dup;
                APICall(params, Application.Finascop_Stock.saveItemData, form_data);

            } else
            {
                Ext.MessageBox.alert('Error', 'Check the required fields');
            }
        } else
        {
            Ext.MessageBox.alert('Error', 'Long Description exceeds 4000 characters');
        }
        WinMask.hide();
    };

    var itemmasterStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listItemMasterData',
            fields: ['ItemId', 'stit_itemName', 'hsn_code', 'product_is_home', 'stit_category_name', 'stit_brand_name', 'imgCount', 'stit_product_variant', 'stit_quantity',
                {name: 'tax', type: 'number'}, {name: 'mrp', type: 'float'}, 'groups', 'description',
                {name: 'convertable_on', type: 'number'}, {name: 'convertable_off', type: 'number'},
                {name: 'list_in_sales_on', type: 'number'}, {name: 'list_in_sales_off', type: 'number'},
                {name: 'stock_enabled', type: 'number'}, {name: 'stock_disabled', type: 'number'},
                {name: 'list_in_purchase_on', type: 'number'}, {name: 'list_in_purchase_off', type: 'number'},
                {name: 'tangible_on', type: 'number'}, {name: 'tangible_off', type: 'number'},
                {name: 'total_mrp', type: 'float'}, {name: 'tax_total', type: 'float'}
            ],
            totalProperty: 'totalCount',
            root: 'data',
            id: 'stit_ID',
            remoteSort: true,
            autoLoad: true,
            listeners: {
                load: function (store, record, options) {

                    Ext.getCmp('itemmaster_grid').getSelectionModel().selectRow(0);
                    if (record.length > 0)
                    {
                        var record = Ext.getCmp('itemmaster_grid').getSelectionModel().getSelected();

                        total_mrp = record.get('total_mrp');
                        var tax_total = record.get('tax_total');
                        var number_of_col = Ext.getCmp('itemmaster_grid').getColumnModel().config.length;
                        var tooltipmaker = Ext.getCmp('itemmaster_grid').getColumnModel().config;

                        for (var i = 1; i < number_of_col; i++)
                        {
                            var column_header = tooltipmaker[i].header;
                            if (column_header == 'MRP')
                            {
                                tooltipmaker[i].tooltip = 'Total MRP : ' + total_mrp;
                            }
                            if (column_header == 'Tax')
                            {
                                tooltipmaker[i].tooltip = 'Total Tax : ' + tax_total;
                            }

                        }
                    }


                }
            }
        });
        return store;
    };

    var itemmasterGrid = function () {
        var itemmaster_store = itemmasterStore();
        var action = itemGridAction();
        var ItemMaster_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
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
                },
                {
                    type: 'string',
                    dataIndex: 'hsn_code'
                },
                {
                    type: 'numeric',
                    dataIndex: 'tax'
                }
            ]
        });
        ItemMaster_filter.remote = true;
        ItemMaster_filter.autoReload = true;
        var itemmaster_grid_panel = new Ext.grid.GridPanel(
                {
                    ds: itemmaster_store,
                    //iconCls: 'finascop_itemmaster',
                    frame: false,
                    border: false,
                    height: 360,
                    id: 'itemmaster_grid',
                    title: 'Item Master',
                    loadMask: true,
                    plugins: [ItemMaster_filter, action],
                    columns: [new Ext.grid.RowNumberer(),
                        {
                            header: 'Item Name',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'stit_itemName'
                        }, {
                            header: 'Category',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'stit_category_name'
                        }, {
                            header: 'Brand',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'stit_brand_name'
                        }, {
                            header: 'Variant',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'stit_product_variant'
                        }, {
                            header: 'Quantity',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'stit_quantity'
                        }, {
                            header: 'HSN Code',
                            id: 'hsn_code',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'hsn_code'
                        },
                        {
                            header: 'Tax',
                            id: 'tax',
                            xtype: 'numbercolumn',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'tax',
                            align: 'right',
                            width: 50
                        }, {
                            header: 'Image Count',
                            sortable: true,
                            hideable: false,
                            dataIndex: 'imgCount',
                            align: 'right',
                            width: 50
                        }, action],
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
                        /*     <?php if (user_access("finascop_stock", "saveItemMaster")) { ?> */
                        {
                            xtype: 'button',
                            text: 'Create New Item', iconCls: 'finascop_add',
                            handler: function () {
                                addItem(0, '');
                            }
                        }
                        /*<?php } ?> */
                    ],
                    bbar: new Ext.PagingToolbar({
                        pageSize: 18,
                        store: itemmaster_store,
                        displayInfo: true,
                        displayMsg: 'Displaying records {0} - {1} of {2}',
                        emptyMsg: "No records to display"
                                // plugins: [ItemMaster_filter]

                    })
                }
        );

        return itemmaster_grid_panel;
    };
    var itemmasterData = function () {
        Ext.getCmp('itemmaster_grid').getStore().setDefaultSort('ItemName', 'asc');
        Ext.getCmp('itemmaster_grid').getStore().removeAll();
        Ext.getCmp('itemmaster_grid').getStore().load({
            params: {
                start: 0,
                limit: recs_per_page
            }
        });
    };

    var viewOrAprroveGrid = function () {

        var viewOrAprrovegrid_Store = function () {
            var store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=listViewApproveItemDetail',
                fields: ['stregApproveNo', 'stregItem', 'stregQty', 'stregSource', 'stregSourceRefNo', 'stregApprovedBy',
                    'stregApprovalDate'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: false
            });
            return store;
        }
        var viewOrAprroveStore = viewOrAprrovegrid_Store();
        var panel = new Ext.grid.GridPanel({
            store: viewOrAprroveStore,
            layout: 'fit',
            id: 'streg_viewOrAprroveGrid',
            height: 150,
            loadMask: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Approval No.',
                    sortable: true,
                    dataIndex: 'stregApproveNo',
                    tooltip: 'Approval Number',
                    width: 100
                },
                {
                    header: 'Item',
                    sortable: true,
                    dataIndex: 'stregItem',
                    tooltip: 'Item Name',
                    width: 100
                },
                {
                    header: 'Quantity',
                    sortable: true,
                    dataIndex: 'stregQty',
                    tooltip: 'Item Quantity',
                    width: 100
                },
                {
                    header: 'Source',
                    sortable: true,
                    dataIndex: 'stregSource',
                    tooltip: 'Source of Item',
                    width: 100
                },
                {
                    header: 'Source RefNo. ',
                    sortable: true,
                    dataIndex: 'stregSourceRefNo',
                    tooltip: 'Source Reference Number',
                    width: 100
                },
                {
                    header: 'Approved By',
                    sortable: true,
                    dataIndex: 'stregApprovedBy',
                    tooltip: 'Approved Person',
                    width: 100
                },
                {
                    header: 'Approval Date',
                    sortable: true,
                    dataIndex: 'stregApprovalDate',
                    tooltip: 'Date of Approval',
                    width: 100
                }
            ]
        });
        return panel;
    };


    var saveApprovedData = function () {
        var viewOrAprrovedgrid = Ext.getCmp('streg_viewOrAprroveGrid');
        var viewOrAprroveGridStore = viewOrAprrovedgrid.getStore();
        var viewOrAprroveGridData = Ext.pluck(viewOrAprroveGridStore.getRange(), 'data');
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (!Ext.isEmpty(viewOrAprrovedgrid))
        {

            var grid_data = {
                purchase_return_gridData: Ext.encode(viewOrAprroveGridData),
                apikey: _SESSION.apikey,
                tstamp: t_stamp
            };
            var params = {
                action: 'Update',
                module: 'finascop_purchase',
                op: 'saveApprovedItems',
                extrainfo: 'fsr',
                id: '1'
            };
            APICall(params, Application.Finascop_Stock.saveApprovedItems, grid_data);
        } else
        {
            Ext.MessageBox.alert('Error', 'Data not Found');
        }
    };

    var viewItems = function (rec) {
        var approve = rec.get('approved');

        var title = (approve > 0) ? 'Show Items' : 'Approve';
        var icon = (approve > 0) ? 'finascop_purchase_show' : 'finascop_approved';
        var viewOrAprrovegrid = viewOrAprroveGrid();
        var viewitems_window = Ext.getCmp('viewItems_window');
        if (Ext.isEmpty(viewitems_window))
        {
            var viewitems_window = new Ext.Window({
                id: 'viewItems_window',
                title: title,
                //iconCls: icon,
                modal: true,
                layout: 'fit',
                width: 795,
                autoHeight: true,
                shadow: false,
                resizable: false,
                closable: false,
                items: [viewOrAprrovegrid],
                buttons: [{
                        text: 'APPROVE',
                        id: 'btn_approve',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        disabled: button_disable,
                        handler: function () {
                            saveApprovedData();
                        }
                    },
                    {
                        text: 'CANCEL',
                        id: 'btn_close',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            viewitems_window.close();
                        }
                    }]
            });
        }

        if (approve <= 0)
        {
            button_disable = false;
        } else
        {
            Ext.getCmp('btn_approve').setVisible(false)
            Ext.getCmp('btn_close').setText('CLOSE');
            var button_disable = true;
        }

        var viewOrAprroveGridstore = Ext.getCmp('streg_viewOrAprroveGrid').getStore();
        viewOrAprroveGridstore.baseParams = {
            stregInvNo: rec.get('stregInvNo'),
            stregItems: rec.get('stregItems')
        };
        viewOrAprroveGridstore.load();

        viewitems_window.show();
        viewitems_window.doLayout();
        viewitems_window.center();
    };

    // Stock Register Grid: -->
    var stockRegisterGrid = function () {

        var stockregisterMasterStore = function () {
            var purchase_returnable_store = new Ext.data.JsonStore({
                method: 'post',
                url: modURL + '&op=listStockRegisterData',
                fields: ['stregInvNo', 'stregInvDate', 'stregSource', 'stregCount', 'approved', 'stregItems'],
                totalProperty: 'totalCount',
                root: 'data',
                autoLoad: true
            });
            return purchase_returnable_store;
        };
        var stock_register_master_store = stockregisterMasterStore();
        var stock_register_main_grid = new Ext.grid.GridPanel({
            store: stock_register_master_store,
            layout: 'fit',
            frame: false,
            border: false,
            plugins: [],
            id: 'streg_master_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Invoice Number',
                    sortable: true,
                    dataIndex: 'stregInvNo',
                    tooltip: 'Invoice Number'
                },
                {
                    header: 'Invoive Date',
                    sortable: true,
                    dataIndex: 'stregInvDate',
                    tooltip: 'Invoive Date'
                }, {
                    header: 'Source',
                    sortable: true,
                    dataIndex: 'stregSource',
                    tooltip: 'Source'
                }, {
                    header: 'Count',
                    sortable: true,
                    align: 'right',
                    dataIndex: 'stregCount',
                    tooltip: 'Count'
                }, {
                    xtype: 'actioncolumn',
                    hideable: false,
                    items: [{
                            sortable: false,
                            getClass: function (v, meta, rec) {

                                var approved = rec.get('approved');

                                if (approved <= 0)
                                {
                                    this.items[0].tooltip = 'Approve';
                                    return 'finascop_approved';
                                } else
                                {
                                    this.items[0].tooltip = 'View Invoice';
                                    return 'finascop_purchase_show';

                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var rec = grid.getStore().getAt(rowIndex);
                                viewItems(rec);
                            }
                        }
                    ]
                }],
            viewConfig: {
                forceFit: true,
                getRowClass: function (record, index) {
                },
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

            },
            listeners: {
                viewready: updatePagination
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            tbar: ['-',
                {html: '&nbsp;Source : &nbsp;'},
                {
                    xtype: 'combo',
                    text: 'Item',
                    store: new Ext.data.SimpleStore({
                        fields: ["value", "text"],
                        data: [["1", "Purchase"], ["0", "Sales Return"]]}),
                    id: 'streg_Source',
                    name: 'source',
                    mode: 'local',
                    hiddenName: 'source',
                    displayField: 'text',
                    valueField: 'value',
                    triggerAction: 'all',
                    forceSelection: false,
                    editable: true,
                    typeAhead: false,
                    enableKeyEvents: true,
                    autoSelect: false,
                    width: 200
                }, {html: '&nbsp; View Closed : &nbsp;'},
                {
                    xtype: 'checkbox',
                    id: 'streg_ViewClosed',
                    name: 'viewClosed',
                    checked: false
                }
                , {html: '&nbsp; Invoice From : &nbsp;'},
                {
                    xtype: 'datefield',
                    width: 120,
                    text: 'From Date',
                    id: 'streg_FromDate',
                    name: 'invFromDate',
                    enableKeyEvents: true
                }
                , {html: '&nbsp; To : &nbsp;'},
                {
                    xtype: 'datefield',
                    text: 'To Date',
                    width: 120,
                    id: 'streg_ToDate',
                    name: 'invToDate',
                    enableKeyEvents: true
                }
                , '-', {
                    xtype: 'button',
                    text: 'Search',
                    iconCls: 'finascop_search_btn',
                    handler: function () {

                        var params_to_store = function () {
                            stock_register_master_store.baseParams = {
                                sourceValue: Ext.getCmp('streg_Source').getValue(),
                                viewClosed: Ext.getCmp('streg_ViewClosed').getValue(),
                                fromDate: Ext.util.Format.date(Ext.getCmp('streg_FromDate').getValue(), 'd/m/Y'),
                                toDate: Ext.util.Format.date(Ext.getCmp('streg_ToDate').getValue(), 'd/m/Y'),
                            };
                            stock_register_master_store.load();
                        }

                        var fromdate = Ext.getCmp('streg_FromDate').getValue();
                        var todate = Ext.getCmp('streg_ToDate').getValue();
                        if (fromdate != '' && todate == '')
                        {
                            //Ext.MessageBox.alert("Notification", "FromDate and ToDate must be required");
                            Ext.MessageBox.alert('Notification', 'To Date is required');
                        } else if (fromdate == '' && todate != '')
                        {
                            Ext.MessageBox.alert('Notification', 'From Date is required');
                        } else
                        {
                            params_to_store();
                        }

                    }
                }, '-',
                {
                    xtype: 'button',
                    text: 'Reset',
                    iconCls: 'finascop_my-resetpass',
                    handler: function () {

                        Ext.getCmp('streg_Source').reset();
                        Ext.getCmp('streg_Source').clearValue();
                        Ext.getCmp('streg_ViewClosed').reset();
                        Ext.getCmp('streg_FromDate').reset();
                        Ext.getCmp('streg_ToDate').reset();
                        stock_register_master_store.removeAll();
                    }
                }, '-'
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: stock_register_master_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true
        });
        return stock_register_main_grid;
    };
    // <-- Stock Register Grid //



    var stockRegisterMasterPanel = function () {

        var panel = new Ext.Panel({
            layout: 'fit',
            frame: false,
            border: false,
            id: 'streg_MasterPanel',
            title: 'Stock Register',
            //iconCls: 'finascop_StockRegister',
            items: [stockRegisterGrid()
            ]

        });
        return panel;
    };


    //group//

    var groupGridAction = function () {
        var action = new Ext.ux.grid.RowActions({
            autoWidth: false,
            hideMode: 'display',
            width: 75,
            actions: [{
                    sortable: false,
                    tooltip: 'Edit Group',
                    iconCls: 'finascop_edit',
                    callback: function (grid, rec, row, col) {
                        addOrEditGroup(rec.get('group_id'));
                    }
                }]
        });
        return action;
    };

    var group_panel = function () {
        var parentGStore = groupComboStore();
        var groupPanel = new Ext.FormPanel({
            autoHeight: true,
            id: 'groupPanel',
            frame: true,
            border: false,
            layout: 'column',
            items: [{
                    layout: 'column',
                    columnWidth: 1,
                    items: [{
                            layout: 'form',
                            columnWidth: 1,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Group Name',
                                    labelStyle: mandatory_label,
                                    id: 'group',
                                    allowBlank: false,
                                    name: 'group_name',
                                    anchor: '99%',
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 1,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'hidden',
                                    fieldLabel: 'Group Id',
                                    id: 'groupId',
                                    allowBlank: false,
                                    name: 'groupId',
                                    anchor: '99%'
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 1,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'hidden',
                                    fieldLabel: 'Group Name',
                                    id: 'hide_group_name',
                                    allowBlank: false,
                                    name: 'hide_group_name',
                                    anchor: '99%',
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 1,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'hidden',
                                    fieldLabel: 'Parent Group Name',
                                    id: 'hide_ParentGroup_name',
                                    allowBlank: false,
                                    name: 'hide_ParentGroup_name',
                                    anchor: '99%',
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        }
                                    }
                                }]
                        },
                        {
                            layout: 'form',
                            columnWidth: 1,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    hiddenName: 'parent_group_name',
                                    fieldLabel: 'Parent Group Name',
                                    id: 'ParentGroup',
                                    anchor: '99%',
                                    style: {'text-transform': 'uppercase'},
                                    displayField: 'fqGroupName',
                                    valueField: 'group_id',
                                    value: 0,
                                    allowBlank: true,
                                    store: parentGStore,
                                    triggerAction: 'all',
                                    forceSelection: true,
                                    typeAhead: true,
                                    listClass: 'finascop_x-itemgroup-item',
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        }
                                    }

                                }]
                        }]
                }]
        });
        return groupPanel;
    };

    var createGroupPanel = function () {
        var group_grid = createGroupGrid();
        var group_tree = createGroupTree();
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            //iconCls: 'finascop_stockgroup',
            title: 'Stock Group',
            frame: false,
            border: false,
            url: modURL,
            id: 'grouppanel',
            layout: 'column',
            items: [
                {
                    layout: 'form',
                    columnWidth: .15,
                    items: [group_tree]
                },
                {
                    layout: 'form',
                    columnWidth: .85,
                    items: [group_grid]
                }
            ]
        });
        return panel;
    };

    var createGroupGrid = function () {
        var group_store = groupStore();
        var action = groupGridAction();
        var groupGrid_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [
                {
                    type: 'string',
                    dataIndex: 'group_name'
                },
                {
                    type: 'string',
                    dataIndex: 'parent_group'
                }
            ]
        });
        groupGrid_filter.remote = true;
        groupGrid_filter.autoReload = true;
        var group_grid = new Ext.grid.GridPanel({
            store: group_store,
            layout: 'fit',
            //autoHeight: true,
            //iconCls: 'finascop_stockgroup',
            id: 'group_grid',
            height: winsize.height * 0.65,
            plugins: [groupGrid_filter, action],
            columns: [
                {
                    header: 'Group Name',
                    sortable: true,
                    dataIndex: 'group_name',
                    width: 325
                },
                {
                    header: 'Group name With Parent Name',
                    sortable: true,
                    dataIndex: 'fqGroupName',
                    width: 390
                },
                {
                    header: 'Parent',
                    sortable: true,
                    dataIndex: 'parent_group',
                    width: 330
                }, action],
            tbar: [{
                    xtype: 'button',
                    text: 'Add Group',
                    iconCls: 'finascop_add',
                    handler: function () {
                        addOrEditGroup(0);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: group_store,
                displayInfo: true,
                plugins: [groupGrid_filter],
                displayMsg: 'Displaying items {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            listeners: {
                viewready: updatePagination

            }
        });
        return group_grid;
    };


    var createGroupTree = function () {
        var tree = new Ext.tree.TreePanel({
            region: 'center',
            title: 'Group',
            //iconCls: 'finascop_stockgroup',
            useArrows: true,
            autoScroll: true,
            layout: 'fit',
            height: winsize.height * 0.65,
            //autoHeight: true,
            width: 210,
            animate: true,
            enableDD: true,
            containerScroll: true,
            border: true,
            overClass: 'finascop_parent-group',
            itemSelector: 'finascop_child-group',
            dataUrl: modURL + '&op=getgroupitems',
            id: 'group_tree',
            mask: true,
            draggable: false,
            maskDisabled: false,
            maskConfig: {msg: "Loading items..."},
            root: {
                nodeType: 'async',
                text: 'GROUPS',
                draggable: false,
                id: 'root'
            },
            contextMenu: new Ext.menu.Menu({
                items: [{
                        id: 'edit-node',
                        text: 'Edit'
                    }],
                listeners: {
                    itemclick: function (item) {
                        switch (item.id)
                        {
                            case 'edit-node':
                                var n = item.parentMenu.contextNode;
                                if (n.parentNode)
                                {
                                    addOrEditGroup(n.id);
                                }
                                break;
                        }
                    },
                    load: function () {
                        setTimeout(function () {
                            if (WinMask)
                                WinMask.hide();
                        }, 500);
                    }
                }
            }),
            listeners: {
                contextmenu: function (node, e) {
//          Register the context node with the menu so that a Menu Item's handler function can access
//          it via its parentMenu property.
                    node.select();
                    var c = node.getOwnerTree().contextMenu;
                    c.contextNode = node;
                    c.showAt(e.getXY());
                }
            }
        });
        //Ext.getCmp('ldg_company').setValue(store.getAt('0').get('comp_id'));
        tree.getRootNode().expand();
        return tree;
    };

    //var excludeGroupIds = [];

    var addOrEditGroup = function (groupId) {
        var title = (groupId == 0) ? 'Add Group' : 'Edit Group';
        var GroupPanel = group_panel();
        var stGroupWindow = Ext.getCmp('stGroup_window');
        if (Ext.isEmpty(stGroupWindow))
        {
            var stGroupWindow = new Ext.Window({
                id: 'stGroup_window',
                title: title,
                //iconCls: 'finascop_additem',
                modal: true,
                layout: 'fit',
                width: 360,
                autoHeight: true,
                shadow: false,
                resizable: false,
                items: [GroupPanel],
                buttons: [{
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            saveStockGroups(groupId);
                        }
                    },
                    {
                        text: 'Close',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            stGroupWindow.close();
                        }
                    }]
            });
        }
        if (!Ext.isEmpty(groupId))
        {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var parentGStore = Ext.getCmp('ParentGroup').getStore();
            parentGStore.baseParams.excludeId = groupId;
            parentGStore.load();
            GroupPanel.load({
                url: modURL + '&op=getGroup_EditData',
                params: {
                    'id': groupId,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                }
            });
        }
        stGroupWindow.show();
        stGroupWindow.doLayout();
        stGroupWindow.center();
    };
    var dateFieldsInRange = function () {
        from_date = new Date(Ext.getCmp('ch_search_from').getValue());
        to_date = new Date(Ext.getCmp('ch_search_to').getValue());
        if (((to_date - from_date) / (1000 * 60 * 60 * 24)) > dateRange)
        {
            return false;
        } else
        {
            return true;
        }
    };

    var createStockConversionGrid = function () {
        var conversionliststore = conversionStore();
        var stock_conversion_grid = new Ext.grid.GridPanel({
            store: conversionliststore,
            layout: 'fit',
            enableColumnMove: false,
            title: 'Stock Conversion',
            //iconCls: 'finascop_stock_conversion',
            id: 'stock_conversion_grid',
            //plugins: [groupGrid_filter, action],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true

            }),
            columns: [
                {
                    header: 'Conversion No',
                    //sortable: true,
                    dataIndex: 'conversion_no',
                    width: 150,
                    align: 'right'
                },
                {
                    header: 'Converted On',
                    //sortable: true,
                    dataIndex: 'converted_on',
                    width: 150,
                    align: 'right'
                },
                {
                    header: 'Consumed Items Count',
                    //sortable: true,
                    dataIndex: 'consumed_items_count',
                    width: 200,
                    align: 'right'
                },
                {
                    header: 'Produced Items Count',
                    //sortable: true,
                    dataIndex: 'produced_items_count',
                    width: 200,
                    align: 'right'
                },
                {
                    header: 'Added by',
                    //sortable: true,
                    dataIndex: 'added_by',
                    width: 200,
                    align: 'right'
                },
                {
                    xtype: 'actioncolumn',
                    width: 67,
                    hideable: false,
                    items: [{
                            getClass: function (v, meta, rec) {

                                return 'finascop_sale_view';
                            },
                            tooltip: 'View Stock Conversion',
                            handler: function (grid, rowIndex, colIndex) {
                                grid.getSelectionModel().selectRow(rowIndex);
                                var rec = conversionliststore.getAt(rowIndex);
                                var t = new Date();
                                var t_stamp = t.format("YmdHis");
                                Ext.Ajax.request({
                                    url: modURL + '&op=getStockConversionDetails',
                                    method: 'POST',
                                    params: {
                                        conversion_id: rec.get('conversion_id'),
                                        apikey: _SESSION.apikey,
                                        tstamp: t_stamp

                                    },
                                    success: function (response) {

                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == true)
                                        {
                                            var row = new Ext.data.Record.create([
                                                {name: 'consumed_item'},
                                                {name: 'consumed_qty', type: 'number'},
                                                {name: 'produced_item'},
                                                {name: 'produced_qty', type: 'number'}

                                            ]);

                                            var totalsdata = new row({
                                                consumed_item: tmp.data[0].itemname,
                                                consumed_qty: tmp.data[0].qty,
                                                produced_item: tmp.data[1].itemname,
                                                produced_qty: tmp.data[1].qty

                                            });

                                            getConversionDetails();
                                            Ext.getCmp('view_conversion_panel').getStore().removeAll();
                                            Ext.getCmp('view_conversion_panel').getStore().add(totalsdata);
                                        } else
                                        {
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
            tbar: [{
                    xtype: 'button',
                    text: 'Create New Stock Conversion',
                    iconCls: 'finascop_add',
                    handler: function () {
                        addConversion();
                    }
                },
                {
                    html: '&nbsp; &nbsp;  &nbsp;'
                },
                {
                    html: '&nbsp;From : &nbsp;'
                }, {
                    fieldLabel: 'From',
                    xtype: 'datefield',
                    id: 'ch_search_from',
                    name: 'n[search_from]',
                    anchor: '98%',
                    width: 100,
                    editable: true,
                    allowBlank: false,
                    value: new Date().add(Date.DAY, -(dateRange)),
                    format: 'd/m/Y'
                }, {
                    html: '&nbsp; &nbsp;  &nbsp;'
                },
                {
                    html: '&nbsp;To: &nbsp;'
                }, {
                    fieldLabel: 'To',
                    xtype: 'datefield',
                    id: 'ch_search_to',
                    name: 'n[search_to]',
                    anchor: '98%',
                    width: 100,
                    editable: true,
                    allowBlank: false,
                    value: (new Date()),
                    format: 'd/m/Y'
                },
                {
                    text: 'Filter',
                    iconCls: 'finascop_search_btn',
                    id: 'ch_filter_button',
                    style: 'margin-left:10px;',
                    handler: function () {
                        if (dateFieldsInRange())
                        {
                            conversionliststore.baseParams = {
                                from_date: Ext.getCmp('ch_search_from').getValue(),
                                to_date: Ext.getCmp('ch_search_to').getValue()

                            };

                            conversionliststore.load();
                        } else
                        {
                            Ext.Msg.alert("Notification", "Dates must be in a range of " + dateRange + "days maximum");
                        }
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: conversionliststore,
                displayInfo: true,
                displayMsg: 'Displaying items {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            listeners: {
                viewready: updatePagination
            },
            stripeRows: true
        });
        return stock_conversion_grid;
    };

    var getConversionDetails = function (conversiondata)
    {
        var title = 'Conversion Details';
        var ViewConversionPanel = view_conversion_panel();
        var conversionWindow = Ext.getCmp('conversion_details_window');
        if (Ext.isEmpty(conversionWindow))
        {
            var conversionWindow = new Ext.Window({
                id: 'conversion_details_window',
                title: title,
                //iconCls: 'finascop_sale_view',
                modal: true,
                layout: 'fit',
                width: 700,
                height: 130,
                shadow: false,
                resizable: false,
                items: [ViewConversionPanel],
                buttons: [
                    {
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            conversionWindow.close();
                        }
                    }]
            });
        }
        conversionWindow.show();
        conversionWindow.doLayout();
        conversionWindow.center();


    };
    var view_conversion_Store = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            fields: [{name: 'consumed_item'}, {name: 'consumed_qty', type: 'number'}, {name: 'produced_item'}, {name: 'produced_qty', type: 'number'}]
        });
        return store;
    };
    var view_conversion_panel = function () {
        var viewConversionStore = view_conversion_Store();
        var grid = new Ext.grid.GridPanel({
            store: viewConversionStore,
            enableColumnMove: false,
            layout: 'fit',
            forceFit: true,
            id: 'view_conversion_panel',
            height: 40,
            loadMask: true,
            columns: [
                {
                    header: 'Consumed Item',
                    sortable: true,
                    dataIndex: 'consumed_item',
                    width: 230,
                    align: 'left'

                },
                {
                    header: 'Consumed Qty',
                    sortable: true,
                    xtype: 'numbercolumn',
                    dataIndex: 'consumed_qty',
                    width: 100,
                    align: 'right'
                },
                {
                    header: 'Produced Item',
                    sortable: true,
                    dataIndex: 'produced_item',
                    width: 230,
                    align: 'left'

                },
                {
                    header: 'Produced Qty',
                    sortable: true,
                    xtype: 'numbercolumn',
                    dataIndex: 'produced_qty',
                    width: 100,
                    align: 'right'
                }
            ]
        });

        return grid;
    };


    var addConversion = function () {
        var title = 'Create New Stock Conversion Item';
        var ConversionPanel = create_conversion_panel();
        var conversionWindow = Ext.getCmp('create_conversion_window');
        if (Ext.isEmpty(conversionWindow))
        {
            var conversionWindow = new Ext.Window({
                id: 'create_conversion_window',
                title: title,
                //iconCls: 'finascop_additem',
                modal: true,
                layout: 'fit',
                width: 700,
                autoHeight: true,
                shadow: false,
                resizable: false,
                items: [ConversionPanel],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                        handler: function () {
                            conversionWindow.close();
                        }
                    },
                    {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                        handler: function () {
                            saveConversionDetails();
                            Ext.getCmp('stock_conversion_grid').getStore().reload();
                        }
                    }]
            });
        }
        conversionWindow.show();
        conversionWindow.doLayout();
        conversionWindow.center();
    };

    var create_conversion_panel = function () {
        var consumptionGrid = consumption_grid();
        var productionGrid = production_grid();
        var panel = new Ext.form.FormPanel({
            autoHeight: true,
            frame: true,
            border: false,
            url: modURL,
            id: 'conversion_form',
            layout: 'column',
            items: [
                {
                    xtype: 'fieldset',
                    title: 'Consumption',
                    columnWidth: 1,
                    id: 'consumption',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [consumptionGrid]
                                }]
                        }]
                },
                {
                    xtype: 'fieldset',
                    title: 'Production',
                    columnWidth: 1,
                    id: 'production',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [productionGrid]
                                }]
                        }]
                }]
        });
        return panel;
    };

    /* Acions in Item Grid(Action = Remove)*/

    var consumption_grid_Action = function () {

        var item_grid_action = new Ext.ux.grid.RowActions({
            header: 'Actions',
            autoWidth: false,
            width: 90,
            actions: [{
                    sortable: false,
                    tooltip: 'Remove Item',
                    iconCls: 'my-icon12',
                    callback: function (grid, rec, action, row, col) {
                        Ext.Msg.confirm('Notification', 'Do you want to remove this?', function (btn) {
                            if (btn == 'yes')
                            {
                                grid.store.removeAt(row);
                                grid.getView().refresh();
                            }
                        });
                    }
                }]
        });
        return item_grid_action;
    };
    var production_grid_Action = function () {

        var item_grid_action = new Ext.ux.grid.RowActions({
            header: 'Actions',
            autoWidth: false,
            width: 90,
            actions: [{
                    sortable: false,
                    tooltip: 'Remove Item',
                    iconCls: 'my-icon12',
                    callback: function (grid, rec, action, row, col) {
                        Ext.Msg.confirm('Notification', 'Do you want to remove this?', function (btn) {
                            if (btn == 'yes')
                            {
                                grid.store.removeAt(row);
                                grid.getView().refresh();
                            }
                        });
                    }
                }]
        });
        return item_grid_action;
    };

    var consumptionitemStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getConsumptionItems',
            method: 'post',
            fields: ['item_id', 'item_name'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteFilter: true,
            autoload: true
        });
        return store;
    };

    var productionitemStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getProductionItems',
            method: 'post',
            fields: ['item_id', 'item_name'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteFilter: true,
            autoload: true
        });
        return store;
    };

    /* Creating consumption Grid   */
    var consumption_grid = function () {
        var consumption_action = consumption_grid_Action();
        var consumption_store = consumptionStore();
        var consumption_items_store = consumptionitemStore();
        var consumption_grid_panel = new Ext.grid.GridPanel({
            store: consumption_store,
            enableColumnMove: false,
            //layout: 'fit',
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'consumption_grid_panel',
            //loadMask: true,
            plugins: [consumption_action],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            columns: [
                {
                    header: 'Consumed',
                    sortable: true,
                    dataIndex: 'consumed',
                    width: 230,
                    align: 'right'

                },
                {
                    header: 'Qty',
                    sortable: true,
                    xtype: 'numbercolumn',
                    dataIndex: 'qty',
                    width: 60,
                    align: 'right'
                },
                consumption_action
            ],
            tbar: [
                {html: '&nbsp;Consumed : &nbsp;'},
                {xtype: 'combo',
                    anchor: '99%',
                    name: 'consumed',
                    id: 'consumed',
                    width: 210,
                    store: consumption_items_store,
                    mode: 'local',
                    hiddenName: 'consumed',
                    displayField: 'item_name',
                    valueField: 'item_id',
                    //triggerAction: 'all',
                    //allowBlank: false,
                    triggerAction: 'all',
                    autoLoad: false,
                    selectOnFocus: false,
                    forceSelection: false,
                    editable: true,
                    typeAhead: false,
                    enableKeyEvents: true,
                    autoSelect: false,
                    maxChars: 50,
                    firstId: 0,
                    firstValue: '',
                    lastQuery: ''


                }, {html: '&nbsp;Quantity : &nbsp;'},
                {
                    xtype: 'numberfield',
                    id: 'qty',
                    name: 'qty',
                    allowNegative: false,
                    anchor: '97%',
                    width: 100
                },
                {html: '&nbsp;'},
                {
                    xtype: 'button',
                    text: 'Add',
                    iconCls: 'finascop_add',
                    id: 'item_addbtn',
                    handler: function () {
                        if (Ext.isEmpty(Ext.getCmp('consumed').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Consumed Field cannot be empty");
                            return;
                        } else if (Ext.isEmpty(Ext.getCmp('qty').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Quantity Field cannot be empty");
                            return;
                        } else
                        {
                            addconsumption();
                            Ext.getCmp('qty').reset();
                            Ext.getCmp('consumed').reset();
                        }
                    }
                }
            ],
            stripeRows: true

        });
        return consumption_grid_panel;
    };

    /* Function for add items in consumption grid  */
    var addconsumption = function () {
        var rdata;
        var row = new Ext.data.Record.create([
            {name: 'consumed'},
            {name: 'qty'},
            {name: 'item_id'}

        ]);
        var grid_store = Ext.getCmp('consumption_grid_panel').getStore();
        var consumptioncount = (grid_store.sum('qty'));
        var consumed = Ext.getCmp('consumed').getRawValue();
        var qty = Ext.getCmp('qty').getValue();
        var item_id = Ext.getCmp('consumed').getValue();
        rdata = new row({
            consumed: consumed,
            qty: qty,
            item_id: item_id

        });

        Ext.getCmp('consumption_grid_panel').stopEditing(); //stops any acitve editing
        Ext.getCmp('consumption_grid_panel').getStore().add(rdata);


    };

    /* Creating Production Grid   */
    var production_grid = function () {
        var prodution_action = production_grid_Action();
        var production_store = productionStore();
        var production_items_store = productionitemStore();
        var production_grid_panel = new Ext.grid.GridPanel({
            store: production_store,
            enableColumnMove: false,
            //layout: 'fit',
            frame: false,
            border: false,
            title: '',
            height: 200,
            id: 'production_grid_panel',
            //loadMask: true,
            plugins: [prodution_action],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            columns: [
                {
                    header: 'Produced',
                    sortable: true,
                    dataIndex: 'produced',
                    width: 230,
                    align: 'right'

                },
                {
                    header: 'Qty',
                    sortable: true,
                    xtype: 'numbercolumn',
                    dataIndex: 'qty',
                    width: 60,
                    align: 'right'
                },
                prodution_action


            ],
            tbar: [
                {html: '&nbsp;Produced : &nbsp;'},
                {xtype: 'combo',
                    anchor: '99%',
                    name: 'produced',
                    id: 'produced',
                    width: 210,
                    store: production_items_store,
                    mode: 'local',
                    hiddenName: 'produced',
                    displayField: 'item_name',
                    valueField: 'item_id',
                    //triggerAction: 'all',
                    //allowBlank: false,
                    triggerAction: 'all',
                    autoLoad: false,
                    selectOnFocus: false,
                    forceSelection: false,
                    editable: true,
                    typeAhead: false,
                    enableKeyEvents: true,
                    autoSelect: false,
                    maxChars: 50,
                    firstId: 0,
                    firstValue: '',
                    lastQuery: ''


                }, {html: '&nbsp;Quantity : &nbsp;'},
                {
                    xtype: 'numberfield',
                    id: 'qty_field',
                    name: 'qty_field',
                    allowNegative: false,
                    allowDecimals: false,
                    anchor: '97%',
                    width: 100
                },
                {html: '&nbsp;'},
                {
                    xtype: 'button',
                    text: 'Add',
                    iconCls: 'finascop_add',
                    id: 'item_addbtn',
                    handler: function () {
                        if (Ext.isEmpty(Ext.getCmp('produced').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Produced Field cannot be empty");
                            return;
                        } else if (Ext.isEmpty(Ext.getCmp('qty_field').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Quantity Field cannot be empty");
                            return;
                        } else
                        {
                            addproduction();
                            Ext.getCmp('qty_field').reset();
                            Ext.getCmp('produced').reset();
                        }
                    }
                }
            ],
            stripeRows: true

        });
        return production_grid_panel;
    };


    /* Function for add items in production grid  */
    var addproduction = function () {
        var rdata;
        var row = new Ext.data.Record.create([
            {name: 'produced'},
            {name: 'qty'},
            {name: 'item_id'}

        ]);
        var grid_store = Ext.getCmp('production_grid_panel').getStore();
        var productioncount = (grid_store.sum('qty_rate'));
        var produced = Ext.getCmp('produced').getRawValue();
        var qty = Ext.getCmp('qty_field').getValue();
        var item_id = Ext.getCmp('produced').getValue();
        rdata = new row({
            produced: produced,
            qty: qty,
            item_id: item_id

        });

        Ext.getCmp('production_grid_panel').stopEditing(); //stops any acitve editing
        Ext.getCmp('production_grid_panel').getStore().add(rdata);

    };

    //save stock groups data//
    var saveStockGroups = function (groupId) {
        WinMask = new Ext.LoadMask(Ext.getCmp('groupPanel').getEl());
        WinMask.show();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        if (!Ext.isEmpty(Ext.getCmp('group').getValue()))
        {
            var form_data = {
                id: groupId,
                group_name: Ext.getCmp('group').getValue(),
                parent_group_name: Ext.getCmp('ParentGroup').getValue(),
                hide_group_name: Ext.getCmp('hide_group_name').getValue(),
                hide_ParentGroup_name: Ext.getCmp('hide_ParentGroup_name').getValue(),
                tstamp: t_stamp
            }
            var params = {
                action: 'Insert',
                module: 'finascop_stock',
                op: 'saveStockGroups',
                id: groupId,
                extrainfo: 'fsr'
            };
            APICall(params, Application.Finascop_Stock.saveStockGroupData, form_data);

        } else
        {
            Ext.MessageBox.alert('Error', 'Check the required fields');
        }
        WinMask.hide();
    };


    var spGridStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getpartyData',
            method: 'post',
            fields: ['customerId', 'stpa_Fname', 'stpa_Lname', 'stpa_GSTIN', 'stpa_Address', 'stpa_City', 'stpa_PINCODE', 'st_name', 'dst_Name', 'st_id', 'dst_Id', 'stpa_ContactPerson', 'stpa_MobileNo',
                'stpa_Email', 'stpa_PanNo', 'stpa_dlno1', 'stpa_dlno2', 'stpa_fssaino', 'stpa_latitude', 'stpa_longitude', 'accled_ReferenceId', 'stpa_isLiveStock'],
            totalProperty: 'totalCount',
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.partytype = partytype;
                }, load: function () {
                    Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()))
                    {
                        var form = Ext.getCmp('panelPurchaseEditvendordetails').getForm();
                        form.trackResetOnLoad = false;
                        form.reset();

                        Ext.getCmp('gridpanelVendorAdditem').hide();
                        Ext.getCmp('property_grid_id').hide();

                        Ext.getCmp('fbarhiddenid').reset();

                        Ext.getCmp('stpa_Lname').reset();
                        Ext.getCmp('panelPurchaseEditvendordetails').setTitle('Add Form');

                        Ext.getCmp('panelPurchaseEditvendordetails').show();
                        Ext.getCmp('buttonPurchaseVendordetailsCancel').show();
                        Ext.getCmp('buttonPurchaseVendordetailsSave').show();
                        Ext.getCmp('buttonPurchaseVendordetailsEdit').hide();
                        Ext.getCmp('tabpanelPurchaseVendor').hide();
                        var data = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data;
                        Application.Finascop_Stock.ViewMode(data);

                    }
                }
            }
        });
        return store;
    };

    var customerGridAction = function () {
        var action = new Ext.ux.grid.RowActions({
            autoWidth: false,
            hideMode: 'display',
            width: 50,
            actions: [{
                    sortable: false,
                    tooltip: 'Edit Customer',
                    iconCls: 'finascop_edit',
                    callback: function (grid, rec, row, col) {

                        Ext.getCmp('gridpanelVendorAdditem').hide();
                        Ext.getCmp('property_grid_id').hide();
                        Ext.getCmp('panelPurchaseEditvendordetails').show();
                        Ext.getCmp('buttonPurchaseVendordetailsCancel').show();
                        Ext.getCmp('buttonPurchaseVendordetailsSave').show();
                        Ext.getCmp('tabpanelPurchaseVendor').hide();
                        //var mid = rec.get('customerId');
                        vendorItem(rec.data);
                        //addOrEditParty(rec.get('customerId'), rec.get('stpa_Fname'), rec.get('stpa_Lname'));

                    }
                }]
        });
        return action;
    };

    var createSPGrid = function () {
        var district_store = districtStoreEdit();
        var grid_store = spGridStore();
        var action = customerGridAction();
        var customerGrid_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [{
                    type: 'string',
                    dataIndex: 'stpa_Fname'
                },
                {
                    type: 'string',
                    dataIndex: 'stpa_Lname'
                },
                {
                    type: 'string',
                    dataIndex: 'stpa_GSTIN'
                }, {
                    type: 'string',
                    dataIndex: 'stpa_City'
                }, {
                    type: 'string',
                    dataIndex: 'stpa_PINCODE'
                }, {
                    type: 'string',
                    dataIndex: 'st_name'
                }, {
                    type: 'string',
                    dataIndex: 'dst_Name'
                }]
        });
        customerGrid_filter.remote = true;
        customerGrid_filter.autoReload = true;

        var SP_grid = new Ext.grid.GridPanel({
            store: grid_store,
            id: 'gridpanelPurchaseVendordetails',
            region: 'center',
            frame: true,
            border: false,
            layout: 'fit',
            loadMask: true,
            plugins: [customerGrid_filter, action],
            columns: [
                {
                    header: 'Name',
                    sortable: true,
                    dataIndex: 'stpa_Fname',
                    width: 175
                },
                {
                    header: 'GST / VAT ',
                    sortable: true,
                    dataIndex: 'stpa_GSTIN',
                    width: 175
                },
                {
                    header: 'City',
                    sortable: true,
                    dataIndex: 'stpa_City',
                    width: 175
                },
                {
                    header: 'Post Codes',
                    sortable: true,
                    dataIndex: 'stpa_PINCODE',
                    width: 175
                },
                {
                    header: 'State',
                    sortable: true,
                    dataIndex: 'st_name',
                    width: 175
                },
                {
                    header: 'District',
                    sortable: true,
                    dataIndex: 'dst_Name',
                    width: 175
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
                            var stpa_id = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data.customerId;
                            var accled_ReferenceId = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data.accled_ReferenceId;
                            var stpa_isLiveStock = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data.stpa_isLiveStock;

                            vendorActionMenu(e, stpa_isLiveStock, accled_ReferenceId);
                        }
                    }
                }
//                                {
//					xtype: 'actioncolumn',
//					hideable: false,
//					sortable: false,
//					items: [/*     <?php if (user_access("finascop_purchase_order", "poVendorItemsGridStore")) { ?> */{
//							iconCls: 'my-icon96',
//							tooltip: 'Create PO',
//							handler: function (grid, rowIndex, colIndex) {
//								var record = grid.store.getAt(rowIndex);
//								Application.Finascop_Purchase_Order.createPOforVendor(record.get('customerId'), record.get('stpa_Fname'));
//
//							}
//						} /*<?php } ?> */]
//				}
            ],
            viewConfig: {
                forceFit: true,
                emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            },
            tbar: [{
                    xtype: 'button',
                    text: 'Create Vendor',
                    tooltip: 'Create Vendor',
                    iconCls: 'finascop_add',
                    handler: function () {
                        if (_SESSION.br_PyramidLevel == 1) {
                            var form = Ext.getCmp('panelPurchaseEditvendordetails').getForm();
                            form.trackResetOnLoad = false;
                            form.reset();

                            Ext.getCmp('gridpanelVendorAdditem').hide();
                            Ext.getCmp('property_grid_id').hide();

                            Ext.getCmp('fbarhiddenid').reset();

                            Ext.getCmp('stpa_Lname').reset();
                            Ext.getCmp('panelPurchaseEditvendordetails').setTitle('Add Form');

                            Ext.getCmp('panelPurchaseEditvendordetails').show();
                            Ext.getCmp('buttonPurchaseVendordetailsCancel').show();
                            Ext.getCmp('buttonPurchaseVendordetailsSave').show();
                            Ext.getCmp('buttonPurchaseVendordetailsEdit').hide();
                            Ext.getCmp('tabpanelPurchaseVendor').hide();
                        }


                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: grid_store,
                displayInfo: true,
                plugins: [customerGrid_filter],
                displayMsg: 'Displaying items {0} - {1} of {2}',
                emptyMsg: "No records to display"
            }),
            stripeRows: true,
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true
            }),
            listeners: {
                viewready: updatePagination,
                rowclick: function (grid, rowIndex, e) {

                    var record = grid.getStore().getAt(rowIndex);

                    //Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getStore().load();
                    var ID = record.get('customerId');
                    var data = record.data;
                    Application.Finascop_Stock.ViewMode(data);
                }
            }
        });
        return SP_grid;
    };
    var vendorActionMenu = function (e, stpa_isLiveStock, accled_ReferenceId) {
        var vendorActionMenu = new Ext.menu.Menu({
            items: [{
                    text: "Set Delivery Rule",
                    handler: function () {
                        var stpa_id = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data.customerId;
                        var stpa_latitude = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data.stpa_latitude;
                        var stpa_longitude = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data.stpa_longitude;
                        setDeliveryRulesWindow(stpa_id, stpa_latitude, stpa_longitude);
                    }
                }, {
                    text: "API",
                    hidden: !(stpa_isLiveStock == 1),
                    handler: function () {
                        var stpa_id = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data.customerId;
                        var accled_ReferenceId = Ext.getCmp('gridpanelPurchaseVendordetails').getSelectionModel().getSelections()[0].data.accled_ReferenceId;
                        editAPIKey(accled_ReferenceId, stpa_id);
                    }
                }]
        });
        vendorActionMenu.showAt(e.getXY());
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
                                                Ext.getCmp('gridpanelPurchaseVendordetails').getStore().reload();
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
    var setDeliveryRulesWindow = function (stpa_id, stpa_latitude, stpa_longitude) {

        var _associated_Branch = associatedBranch(stpa_latitude, stpa_longitude);

        var win_id = "set_dr_window";

        var set_dr_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(set_dr_window))
        {
            set_dr_window = new Ext.Window({
                id: win_id,
                title: 'Set Delivery Rules',
                layout: 'fit',
                modal: true,
                width: 600,
                height: 250,
                items: [new Ext.FormPanel({
                        labelAlign: 'top',
                        width: 600,
                        height: 250,
                        frame: true,
                        id: 'setDeliveryRule_form',
                        layout: 'column',
                        items: [{
                                layout: 'form',
                                columnWidth: 0.5,
                                frame: false,
                                border: false,
                                labelAlign: 'top',
                                hideBorders: true,
                                items: [
                                    {
                                        xtype: 'combo',
                                        displayField: 'name',
                                        valueField: 'id',
                                        mode: 'local',
                                        id: 'deliverMode_stdDelivery',
                                        name: 'deliverMode_stdDelivery',
                                        hiddenName: 'deliverMode_stdDelivery',
                                        forceSelection: true,
                                        fieldLabel: 'Delivery Mode for Standard Delivery',
                                        emptyText: 'Delivery Mode for Standard Delivery',
                                        allowBlank: false,
                                        anchor: '98%',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        editable: true,
                                        minChars: 2,
                                        tabIndex: 417,
                                        store: new Ext.data.JsonStore({
                                            fields: ['id', 'name'],
                                            data: [{id: '1', name: 'Delivered by Vendor'}, {id: '2', name: 'Pickup by Branch'}]
                                        }),
                                        listeners: {
                                            select: function (combo, record) {
                                                var type = record.data.id;
                                                if (type == 2) {
                                                    Ext.getCmp('asctedbrach_stdDelivery').enable();
                                                    Ext.getCmp('asctedbrach_stdDelivery').allowBlank = false;
                                                } else {
                                                    Ext.getCmp('asctedbrach_stdDelivery').reset();
                                                    Ext.getCmp('asctedbrach_stdDelivery').disable();
                                                    Ext.getCmp('asctedbrach_stdDelivery').allowBlank = true;
                                                }

                                            }
                                        }
                                    }]
                            }, {
                                columnWidth: 0.5,
                                layout: 'form',
                                frame: false,
                                border: false,
                                labelAlign: 'top',
                                hideBorders: true,
                                items: [
                                    {
                                        xtype: 'combo',
                                        displayField: 'br_Name',
                                        valueField: 'br_ID',
                                        mode: 'local',
                                        id: 'asctedbrach_stdDelivery',
                                        name: 'asctedbrach_stdDelivery',
                                        hiddenName: 'asctedbrach_stdDelivery',
                                        forceSelection: true,
                                        fieldLabel: 'Associated Branch',
                                        emptyText: 'Associated Branch',
                                        anchor: '98%',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        editable: true,
                                        minChars: 2,
                                        tabIndex: 418,
                                        store: _associated_Branch
                                    }]
                            }, {
                                columnWidth: 0.5,
                                layout: 'form',
                                frame: false,
                                border: false,
                                labelAlign: 'top',
                                hideBorders: true,
                                items: [
                                    {
                                        xtype: 'combo',
                                        displayField: 'name',
                                        valueField: 'id',
                                        mode: 'local',
                                        id: 'deliverMode_cpr',
                                        name: 'deliverMode_cpr',
                                        hiddenName: 'deliverMode_cpr',
                                        forceSelection: true,
                                        fieldLabel: 'Delivery Mode for Rate Contract Purchase',
                                        emptyText: 'Delivery Mode for Rate Contract Purchase',
                                        allowBlank: false,
                                        anchor: '98%',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        editable: true,
                                        minChars: 2,
                                        tabIndex: 419,
                                        store: new Ext.data.JsonStore({
                                            fields: ['id', 'name'],
                                            data: [{id: '3', name: 'NA'}, {id: '1', name: 'Delivered by Vendor'}, {id: '2', name: 'Pickup by Branch'}]
                                        }),
                                        listeners: {
                                            select: function (combo, record) {
                                                var type = record.data.id;
                                                console.log('type', type);
                                                switch (type) {
                                                    case '1':
                                                        Ext.getCmp('deliveryRule_courier').show();
                                                        Ext.getCmp('deliveryRule_courier').allowBlank = false;
                                                        Ext.getCmp('deliveryRule_express').show();
                                                        Ext.getCmp('deliveryRule_express').allowBlank = true;
                                                        Ext.getCmp('deliveryRule_slotted').show();
                                                        Ext.getCmp('deliveryRule_slotted').allowBlank = true;
                                                        Ext.getCmp('asctedbrach_cpr').hide();
                                                        Ext.getCmp('asctedbrach_cpr').reset();
                                                        Ext.getCmp('asctedbrach_cpr').allowBlank = true;
                                                        Ext.getCmp('stpa_isLiveStock').enable();

                                                        break;
                                                    case '2':
                                                        Ext.getCmp('asctedbrach_cpr').show();
                                                        Ext.getCmp('asctedbrach_cpr').allowBlank = false;
                                                        Ext.getCmp('deliveryRule_courier').reset();
                                                        Ext.getCmp('deliveryRule_courier').hide();
                                                        Ext.getCmp('deliveryRule_courier').allowBlank = true;
                                                        Ext.getCmp('deliveryRule_express').reset();
                                                        Ext.getCmp('deliveryRule_express').hide();
                                                        Ext.getCmp('deliveryRule_express').allowBlank = true;
                                                        Ext.getCmp('deliveryRule_slotted').reset();
                                                        Ext.getCmp('deliveryRule_slotted').hide();
                                                        Ext.getCmp('deliveryRule_slotted').allowBlank = true;
                                                        Ext.getCmp('stpa_isLiveStock').enable();
                                                        break;
                                                    case '3':
                                                        Ext.getCmp('asctedbrach_cpr').reset();
                                                        Ext.getCmp('asctedbrach_cpr').hide();
                                                        Ext.getCmp('asctedbrach_cpr').allowBlank = true;
                                                        Ext.getCmp('deliveryRule_courier').reset();
                                                        Ext.getCmp('deliveryRule_courier').hide();
                                                        Ext.getCmp('deliveryRule_courier').allowBlank = true;
                                                        Ext.getCmp('deliveryRule_express').reset();
                                                        Ext.getCmp('deliveryRule_express').hide();
                                                        Ext.getCmp('deliveryRule_express').allowBlank = true;
                                                        Ext.getCmp('deliveryRule_slotted').reset();
                                                        Ext.getCmp('deliveryRule_slotted').hide();
                                                        Ext.getCmp('deliveryRule_slotted').allowBlank = true;
                                                        Ext.getCmp('stpa_isLiveStock').disable();
                                                        break;

                                                }

                                            },
                                            load: function (combo, record) {


                                            }
                                        }
                                    }]
                            }, {
                                columnWidth: 0.5,
                                layout: 'form',
                                frame: false,
                                border: false,
                                labelAlign: 'top',
                                hideBorders: true,
                                items: [
                                    {
                                        xtype: 'combo',
                                        displayField: 'br_Name',
                                        valueField: 'br_ID',
                                        mode: 'local',
                                        id: 'asctedbrach_cpr',
                                        name: 'asctedbrach_cpr',
                                        hiddenName: 'asctedbrach_cpr',
                                        forceSelection: true,
                                        fieldLabel: 'Associated Branch',
                                        emptyText: 'Associated Branch',
                                        anchor: '98%',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        editable: true,
                                        minChars: 2,
                                        tabIndex: 420,
                                        store: _associated_Branch
                                    }]
                            }, {
                                columnWidth: 0.5,
                                layout: 'form',
                                frame: false,
                                border: false,
                                labelAlign: 'top',
                                hideBorders: true,
                                items: [
                                    {
                                        xtype: 'combo',
                                        displayField: 'rdr_ruleName',
                                        valueField: 'rdr_id',
                                        mode: 'local',
                                        id: 'deliveryRule_courier',
                                        name: 'deliveryRule_courier',
                                        hiddenName: 'deliveryRule_courier',
                                        forceSelection: true,
                                        fieldLabel: 'Delivery Rule for Courier Direct Delivery',
                                        emptyText: 'Delivery Rule for Courier Direct Delivery',
                                        anchor: '98%',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        editable: true,
                                        minChars: 2,
                                        tabIndex: 421,
                                        store: deliveryRule(1)
                                    }]
                            }, {
                                columnWidth: 0.5,
                                layout: 'form',
                                frame: false,
                                border: false,
                                labelAlign: 'top',
                                hideBorders: true,
                                items: [{
                                        xtype: 'combo',
                                        displayField: 'rdr_ruleName',
                                        valueField: 'rdr_id',
                                        mode: 'local',
                                        id: 'deliveryRule_express',
                                        name: 'deliveryRule_express',
                                        hiddenName: 'deliveryRule_express',
                                        forceSelection: true,
                                        fieldLabel: 'Delivery Rule for Express Delivery',
                                        emptyText: 'Delivery Rule for Express Delivery',
                                        anchor: '98%',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        editable: true,
                                        minChars: 2,
                                        tabIndex: 422,
                                        store: deliveryRule(2)
                                    }]
                            },
                            {
                                columnWidth: 0.5,
                                layout: 'form',
                                frame: false,
                                border: false,
                                labelAlign: 'top',
                                hideBorders: true,
                                items: [
                                    {
                                        xtype: 'combo',
                                        displayField: 'rdr_ruleName',
                                        valueField: 'rdr_id',
                                        mode: 'local',
                                        id: 'deliveryRule_slotted',
                                        name: 'deliveryRule_slotted',
                                        hiddenName: 'deliveryRule_slotted',
                                        forceSelection: true,
                                        fieldLabel: 'Delivery Rule for Schedule Delivery',
                                        emptyText: 'Delivery Rule for Schedule Delivery',
                                        anchor: '98%',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        editable: true,
                                        minChars: 2,
                                        tabIndex: 423,
                                        store: deliveryRule(3)
                                    }]
                            }, {
                                columnWidth: 0.5,
                                layout: 'form',
                                frame: false,
                                border: false,
                                labelAlign: 'top',
                                hideBorders: true,
                                items: [
                                    {
                                        xtype: 'combo',
                                        displayField: 'name',
                                        valueField: 'id',
                                        mode: 'local',
                                        id: 'packMode_std',
                                        name: 'packMode_std',
                                        hiddenName: 'packMode_std',
                                        forceSelection: true,
                                        fieldLabel: 'Packing Mode',
                                        emptyText: 'Packing Mode',
                                        allowBlank: false,
                                        anchor: '98%',
                                        typeAhead: true,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        editable: true,
                                        minChars: 2,
                                        tabIndex: 419,
                                        store: new Ext.data.JsonStore({
                                            fields: ['id', 'name'],
                                            data: [{id: '3', name: 'NA'}, {id: '1', name: 'Packed by Vendor'}, {id: '2', name: 'Packed by Branch'}]
                                        }),
                                        listeners: {
                                            select: function (combo, record) {
                                                var type = record.data.id;
                                                console.log('type', type);


                                            },
                                            load: function (combo, record) {


                                            }
                                        }
                                    }]
                            }, {
                                layout: 'form',
                                columnWidth: .5,
                                bodyStyle: {"padding-top": "15px"},
                                labelAlign: 'right',
                                labelWidth: 45,
                                items: [{
                                        xtype: 'checkbox',
                                        checked: false,
                                        id: 'stpa_isLiveStock',
                                        name: 'stpa_isLiveStock',
                                        inputValue: 1,
                                        tabIndex: 511,
                                        boxLabel: 'Is Live Stock'
                                    }]
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
                                icon: IMAGE_BASE_PATH + '/default/icons/save.png',
                                handler: function () {
                                    var isLiveStock = Ext.getCmp('stpa_isLiveStock').getValue();
                                    var stpa_isLiveStock = isLiveStock === true ? '1' : '0';
                                    Ext.Ajax.request({
                                        url: modURL,
                                        params: {
                                            op: 'updateDeliveryModes',
                                            stpa_id: stpa_id, stpa_latitude: stpa_latitude, stpa_longitude: stpa_longitude,
                                            deliverMode_stdDelivery: Ext.getCmp('deliverMode_stdDelivery').getValue(),
                                            asctedbrach_stdDelivery: Ext.getCmp('asctedbrach_stdDelivery').getValue(),
                                            asctedbrach_cpr: Ext.getCmp('asctedbrach_cpr').getValue(),
                                            deliverMode_cpr: Ext.getCmp('deliverMode_cpr').getValue(),
                                            deliveryRule_courier: Ext.getCmp('deliveryRule_courier').getValue(),
                                            deliveryRule_express: Ext.getCmp('deliveryRule_express').getValue(),
                                            deliveryRule_slotted: Ext.getCmp('deliveryRule_slotted').getValue(),
                                            packMode_std: Ext.getCmp('packMode_std').getValue(),
                                            stpa_isLiveStock: stpa_isLiveStock
                                        },
                                        failure: function (response, options) {
                                            Ext.MessageBox.alert('Notification', ACTION_FAIL);
                                        },
                                        success: function (response, options) {
                                            var tmp = Ext.decode(response.responseText);
                                            if (tmp.success === true) {
                                                Application.example.msg('Notification', tmp.msg);
                                                set_dr_window.close();
                                                Ext.getCmp('gridpanelPurchaseVendordetails').getStore().reload();
                                            }
                                        }
                                    });
                                }
                            }/*<?php } ?> */]

            });
            if (!Ext.isEmpty(stpa_id)) {
                var t = new Date();
                var t_stamp = t.format("YmdHis");
                var dept_form = Ext.getCmp('setDeliveryRule_form').getForm();
                dept_form.load({
                    params: {
                        stpa_id: stpa_id,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=loadVendorDeliveryRules',
                    waitMsg: 'Loading...', success: function (form, action) {
                        eval('var tmp=' + action.response.responseText);
                        var tmp = Ext.decode(action.response.responseText);
                        console.log(tmp);
                        var type = tmp.data.deliverMode_cpr;
                        var deliverMode_stdDelivery = tmp.data.deliverMode_stdDelivery;
                        if (deliverMode_stdDelivery == 1) {
                            Ext.getCmp('asctedbrach_stdDelivery').reset();
                            Ext.getCmp('asctedbrach_stdDelivery').disable();
                        } else {
                            Ext.getCmp('asctedbrach_stdDelivery').enable();
                            Ext.getCmp('asctedbrach_stdDelivery').setRawValue(tmp.data.asctedbrach_stdName);
                            Ext.getCmp('asctedbrach_stdDelivery').getStore().load();
                        }
                        var packMode_std = tmp.data.packMode_std;

                        console.log('type', type);
                        switch (type) {
                            case '1':
                                Ext.getCmp('deliveryRule_courier').show();
                                Ext.getCmp('deliveryRule_courier').allowBlank = false;
                                Ext.getCmp('deliveryRule_express').show();
                                Ext.getCmp('deliveryRule_express').allowBlank = false;
                                Ext.getCmp('deliveryRule_slotted').show();
                                Ext.getCmp('deliveryRule_slotted').allowBlank = false;
                                Ext.getCmp('asctedbrach_cpr').hide();
                                Ext.getCmp('asctedbrach_cpr').allowBlank = true;
                                Ext.getCmp('stpa_isLiveStock').enable();

                                break;
                            case '2':
                                Ext.getCmp('asctedbrach_cpr').show();
                                Ext.getCmp('asctedbrach_cpr').setRawValue(tmp.data.asctedbrach_cprName);
                                Ext.getCmp('asctedbrach_cpr').getStore().load();
                                Ext.getCmp('asctedbrach_cpr').allowBlank = false;
                                Ext.getCmp('deliveryRule_courier').hide();
                                Ext.getCmp('deliveryRule_courier').allowBlank = true;
                                Ext.getCmp('deliveryRule_express').hide();
                                Ext.getCmp('deliveryRule_express').allowBlank = true;
                                Ext.getCmp('deliveryRule_slotted').hide();
                                Ext.getCmp('deliveryRule_slotted').allowBlank = true;
                                Ext.getCmp('stpa_isLiveStock').enable();
                                break;
                            case '3':
                                Ext.getCmp('asctedbrach_cpr').hide();
                                Ext.getCmp('asctedbrach_cpr').allowBlank = true;
                                Ext.getCmp('deliveryRule_courier').hide();
                                Ext.getCmp('deliveryRule_courier').allowBlank = true;
                                Ext.getCmp('deliveryRule_express').hide();
                                Ext.getCmp('deliveryRule_express').allowBlank = true;
                                Ext.getCmp('deliveryRule_slotted').hide();
                                Ext.getCmp('deliveryRule_slotted').allowBlank = true;
                                Ext.getCmp('stpa_isLiveStock').disable();
                                break;

                        }

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

    //Right panel for tab panel starts here
    function vendorItem(mid)
    {
        //if(mid != 0)
        console.log('mid', mid);
        var lati, longi;
        if (mid == 0) {
            lati = 8.507007481504532;
            longi = 76.95167541503906;
        }
        var PartyEditPanel = editPartyPanel(mid, lati, longi);

        var vendoritempanel = new Ext.Panel({
            frame: false,
            border: false,
            collapsible: true,
            title: 'Vendor details',
            flex: 1,
            layout: 'vbox',
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            id: 'panelPurchaseVendordetails',
            items: [
                PartyEditPanel,
                new Ext.TabPanel({
                    activeTab: 0,
                    flex: 1,
                    plain: true,
                    frame: true,
                    id: 'tabpanelPurchaseVendor',
                    items: [
                        {
                            title: 'Profile',
                            id: 'property_grid_id',
                            width: winsize.width * 0.4,
                            items: [{
                                    xtype: 'hidden',
                                    id: 'fbarhiddenid',
                                    hidden: true
                                }],
                            tpl: new Ext.XTemplate('<div class="details-outer">',
                                    '<table border="0" width="99%" class="details_view_table">',
                                    '<tr><th>Name :</th><td> {stpa_Fname} {stpa_Lname}</td></tr>',
                                    '<tr><th>Address:</th><td>  {stpa_Address}</td></tr>',
                                    '<tr><th>Email:</th><td>  {stpa_Email}</td></tr>',
                                    '<tr><th>Contact Person:</th><td>  {stpa_ContactPerson}</td></tr>',
                                    '<tr><th>PAN No.:</th><td>  {stpa_PanNo}</td></tr>',
                                    '<tr><th>Mobile No.:</th><td>  {stpa_MobileNo}</td></tr>',
                                    '<tr><th>City :</th><td>  {stpa_City}</td></tr>',
                                    '<tr><th>Post Codes :</th><td>  {stpa_PINCODE}</td></tr>',
                                    '<tr><th>State :</th><td>  {st_name}</td></tr>',
                                    '<tr><th>District :</th><td>  {dst_Name}</td></tr>',
                                    '<tr><th>GST / VAT :</th><td>  {stpa_GSTIN}</td></tr>',
                                    '<tr><th>Reg Number 1:</th><td>  {stpa_dlno1}</td></tr>',
                                    '<tr><th>Reg Number 2:</th><td>  {stpa_dlno2}</td></tr>',
                                    '<tr><th>FSSAI / EORI  No :</th><td>  {stpa_fssaino}</td></tr>',
                                    '</table>',
                                    '</div>')
                        },
                        {
                            id: 'gridpanelPurchaseAdditem',
                            title: 'Items',
                            frame: false,
                            width: winsize.width * 0.4,
                            border: false,
                            items: [additemGrid()]
                        }
                    ],
                    listeners: {
                        tabchange: function (sd, tab)
                        {
                            if (tab.id == 'gridpanelPurchaseAdditem')
                            {
                                Ext.getCmp('buttonPurchaseVendordetailsEdit').hide();
                            } else if (tab.id == 'property_grid_id')
                            {
                                var _vendorId = Ext.getCmp('fbarhiddenid').getValue();
                                console.log("vendor id is", _vendorId);
                                if ((_vendorId == '') || (_vendorId == undefined))
                                {
                                    Ext.getCmp('buttonPurchaseVendordetailsEdit').hide();

                                } else
                                {
                                    Ext.getCmp('buttonPurchaseVendordetailsEdit').show();

                                }
                            }


                        }
                    }
                }),
            ]
        });
        return vendoritempanel;
    }
    ;


    var ListvendoritemPanel = function (id) {
        var mid = 0;
        var panel = new Ext.Panel({
            layout: 'border',
            border: false,
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            id: id,
            title: (partytype == 1) ? 'Vendors' : 'Customer',
            //iconCls: 'finascop_salescustomer',
            items: [createSPGrid(),
                {
                    border: false,
                    frame: false,
                    region: 'east',
                    layout: 'vbox',
                    layoutConfig: {
                        align: 'stretch',
                        pack: 'start'
                    },
                    width: winsize.width * 0.4,
                    items: [
                        vendorItem(mid),
                        new Ext.Panel({
                            border: false,
                            frame: true,
                            layout: 'column',
                            height: 50,
                            items: [
                                new Ext.Panel({
                                    id: 'bufferPanel',
                                    columnWidth: .70,
                                    border: false,
                                    hidden: true,
                                    frame: false,
                                    items: [{html: '&nbsp'}]
                                }),
                                new Ext.Panel({
                                    id: 'bufferPanel_2',
                                    columnWidth: .85,
                                    border: false,
                                    hidden: true,
                                    frame: false,
                                    items: [{html: '&nbsp'}]
                                }),
                                {
                                    text: "Cancel",
                                    xtype: 'button',
                                    width: 75,
                                    columnWidth: .15,
                                    id: 'buttonPurchaseVendordetailsCancel',
                                    tabIndex: 414,
                                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                                    iconCls: 'my-icon61',
                                    hidden: true,
                                    handler: function () {
                                        Ext.getCmp('panelPurchaseEditvendordetails').getForm().trackResetOnLoad = false;
                                        Ext.getCmp('panelPurchaseEditvendordetails').hide();

                                        Ext.getCmp('buttonPurchaseVendordetailsSave').hide();
                                        Ext.getCmp('buttonPurchaseVendordetailsCancel').hide();
                                        Ext.getCmp('tabpanelPurchaseVendor').show();
                                        var grid = Ext.getCmp('gridpanelPurchaseVendordetails');
                                        grid.getSelectionModel().clearSelections();

                                        var form = Ext.getCmp('panelPurchaseEditvendordetails').getForm();
                                        form.reset();
                                        Ext.getCmp('gridpanelPurchaseVendordetails').getStore().load();
                                    }
                                },
                                {
                                    text: "Save",
                                    xtype: 'button',
                                    width: 75,
                                    columnWidth: .15,
                                    id: 'buttonPurchaseVendordetailsSave',
                                    tabIndex: 413,
                                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                                    iconCls: 'thes_save',
                                    hidden: true,
                                    handler: function () {
                                        var cust_id = Ext.getCmp('fbarhiddenid').getValue();
                                        //var deliverMode_cpr = Ext.getCmp('deliverMode_cpr').getValue();
                                        //var deliveryRule_slotted = Ext.getCmp('deliveryRule_slotted').getValue();
                                        //deliveryRule_slotted = (deliveryRule_slotted > 0 && deliveryRule_slotted != null) ? Ext.getCmp('deliveryRule_slotted').getValue() : parseInt(0);
                                        //var deliveryRule_express = Ext.getCmp('deliveryRule_express').getValue();
                                        //deliveryRule_express = (deliveryRule_express > 0 && deliveryRule_express != null) ? Ext.getCmp('deliveryRule_express').getValue() : parseInt(0);
                                        if (Ext.getCmp('panelPurchaseEditvendordetails').getForm().isValid())
                                        {
                                            if (cust_id == '' || cust_id == 0 || cust_id == 'undefined')
                                            {
                                                editDataSubmit(0);
                                            } else
                                            {
                                                editDataSubmit(cust_id);
                                            }


                                        } else
                                        {
                                            console.log("form is not valid")
                                        }
                                    }
                                },
                                {
                                    text: "Edit",
                                    xtype: 'button',
                                    width: 75,
                                    columnWidth: .15,
                                    id: 'buttonPurchaseVendordetailsEdit',
                                    icon: IMAGE_BASE_PATH + '/default/icons/edit.png',
                                    hidden: true,
                                    listeners: {
                                        //'bufferPanel'
                                        hide: function (thisBtn) {
                                            Ext.getCmp('bufferPanel').show();
                                            Ext.getCmp('bufferPanel_2').hide();
                                        },
                                        show: function (thisBtn) {
                                            Ext.getCmp('bufferPanel').hide();
                                            Ext.getCmp('bufferPanel_2').show();
                                        }
                                    },
                                    handler: function () {

                                        Ext.getCmp('panelPurchaseEditvendordetails').setTitle('Edit Form');
                                        Ext.getCmp('gridpanelVendorAdditem').hide();
                                        Ext.getCmp('property_grid_id').hide();
                                        Ext.getCmp('panelPurchaseEditvendordetails').show();
                                        Ext.getCmp('buttonPurchaseVendordetailsCancel').show();
                                        Ext.getCmp('buttonPurchaseVendordetailsSave').show();
                                        Ext.getCmp('buttonPurchaseVendordetailsEdit').hide();
                                        Ext.getCmp('tabpanelPurchaseVendor').hide();
                                        var editform = Ext.getCmp('panelPurchaseEditvendordetails').getForm();
                                        vendorid_load = Ext.getCmp('fbarhiddenid').getValue();
                                    }
                                }
                            ]
                        })
                    ]
                }]
        });
        return panel;
    };


//  store for additems
    var addItemStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            url: modURL + '&op=listitemvendor',
            fields: ['itemType', 'itemId', 'itemName', 'stit_type', 'stpi_id', 'stit_brand_name', 'stit_quantity', 'least_package_type_name'],
            //fields: ['stit_ID', 'stit_itemId', 'stit_Description', 'stit_MRP', 'stit_itemName', 'stit_SKU'],
            remoteSort: true,
            root: 'data',
            totalProperty: 'totalCount'

        });
        return store;
    };


    var additemGrid = function () {
        // console.log(cust_id);
        var addItemStorenew = addItemStore();
        var addItemColmodelnew = addItemColmodel();

        var addItem = new Ext.grid.GridPanel({
            store: addItemStorenew,
            colModel: addItemColmodelnew,
            //autoHeight:true,
            height: winsize.height * 0.7,
            frame: true,
            border: false,
            layout: 'fit',
            region: 'center',
            loadMask: true,
            plugins: [vendoritem_filter],
            id: 'gridpanelVendorAdditem',
            tbar: new Ext.Toolbar({
                items: [{
                        xtype: 'button',
                        text: 'Add Items',
                        tooltip: 'Add Items',
                        iconCls: 'add',
                        handler: function () {
                            var hidfield = Ext.getCmp('hidtextitem').getValue();
                            addVendorItem(hidfield);
                        }
                    }, {
                        xtype: 'textfield',
                        hidden: true,
                        id: 'hidtextitem'
                    },
                    {
                        xtype: 'textfield',
                        hidden: true,
                        id: 'hidgriditem'
                    }
                ]
            }),
            // bbar: new Ext.PagingToolbar({
            //     pageSize: recs_per_page,
            //     store: addItemStorenew,
            //     displayInfo: true,
            //     displayMsg: "Displaying pages {0} - {1} of {2}",
            //     emptyMsg: "No Pages to display"
            // }),
            viewConfig: {
                forceFit: true
            },
            listeners: {
                viewready: updatePagination,
                rowclick: function (grid, rowIndex, e) {
                    var record = grid.getStore().getAt(rowIndex);
                    console.log("rightgrid:", record);
                    var rowid = record.data.stit_ID;
                    console.log('itemid', rowid);
                    Ext.getCmp('hidgriditem').setValue(rowid);
                }
            }
        });
        return addItem;
    };
    // add item col model
//fields: ['itemType', 'itemId', 'itemName',stpi_id,stit_type],
    var vendoritem_filter = new Ext.ux.grid.GridFilters({
        //remote: true,
        local: true,
        filters: [
            {
                type: 'string',
                dataIndex: 'itemName'
            }, {
                type: 'string',
                dataIndex: 'itemType'
            }, {
                type: 'string',
                dataIndex: 'stit_brand_name'
            }, {
                type: 'string',
                dataIndex: 'stit_quantity'
            }, {
                type: 'string',
                dataIndex: 'least_package_type_name'
            }]
    });
    vendoritem_filter.remote = true;
    vendoritem_filter.autoReload = true;
    var addItemColmodel = function () {
        var colmodel = new Ext.grid.ColumnModel({
            sortable: true, columns: [
                rowno,
                {
                    header: 'Item Name',
                    width: 400,
                    dataIndex: 'itemName',
                    hideable: true,
                    sortable: true
                },
                {
                    header: 'Item Type',
                    dataIndex: 'itemType',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Brand',
                    dataIndex: 'stit_brand_name',
                    hideable: true,
                    sortable: true
                }, {
                    header: 'Quantity',
                    dataIndex: 'stit_quantity',
                    hideable: true,
                    sortable: false
                }, {
                    header: 'Least Packing Unit',
                    dataIndex: 'least_package_type_name',
                    hideable: true,
                    sortable: true
                },
                {
                    xtype: 'actioncolumn',
                    hideable: false,
                    items: [{
                            tooltip: 'Remove Item',
                            icon: IMAGE_BASE_PATH + '/default/icons/delete.png',
                            handler: function (grid, rowIndex, colIndex)
                            {
                                var record = grid.store.getAt(rowIndex);
                                console.log("record from grid", record);
                                var stpi_id = record.data.stpi_id;
                                var stit_type = record.data.stit_type;
                                var _cust_id = Ext.getCmp('hidtextitem').getValue();
                                console.log("Current customer", _cust_id);
                                Ext.MessageBox.confirm("Confirm", "Are you sure to delete this record?", function (btn) {
                                    if (btn == "yes")
                                    {
                                        removeItemfromGrid(rowIndex, stpi_id, _cust_id, stit_type);
                                    }
                                });

                            }
                        }
                    ]
                }
            ]
        });
        return colmodel;
    };
    //Right panel for tab panel ends here

    // remove item from grid
    var removeItemfromGrid = function (rowIndex, stpi_id, cust_id, stit_type) {
        Application.Finascop_Stock.Cache.delitemid = stpi_id;
        Application.Finascop_Stock.Cache.delcust_id = cust_id;
        Application.Finascop_Stock.Cache.delstit_type = stit_type;

        var form_data = {
            itemid: stpi_id,
            cust_id: cust_id,
            stit_type: stit_type
        };
        var params = {
            action: 'Delete',
            module: 'Finascop_Stock',
            op: 'deleteVendorItemFromgrid',
            extrainfo: 'Delete Items from Vendors',
            id: cust_id
        };
        APICall(params, Application.Finascop_Stock.removeItemfromGrid, form_data);
    };
    // end



    function ComboSetter(comboBox, value) {
        var store = comboBox.getStore();
        var valueField = comboBox.valueField;
        var displayField = comboBox.displayField;

        var recordNumber = store.findExact(valueField, value, 0);

        if (recordNumber == -1)
            return -1;

        var displayValue = store.getAt(recordNumber).data[displayField];
        comboBox.setValue(value);
        comboBox.setRawValue(displayValue);
        comboBox.selectedIndex = recordNumber;
        return recordNumber;
    }
    ;
// reshma and greeshma 

    var addVendorItem = function (cid) {
        current_type = 1;

        var resultWindow = new Ext.Window({
            id: "windowFinascopStockAddvenderitemCreatevendoritem",
            title: 'Vendor Items',
            //iconCls: 'vender-items',
            shadow: false,
            height: 400,
            width: 800,
            layout: 'fit',
            resizable: false,
            plain: true,
            constrain: true,
            draggable: true,
            modal: true,
            items: [vendorGrid(cid)],
            buttons: [
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                    text: 'Cancel',
                    handler: function () {
                        Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();
                    }

                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                    text: 'Save',
                    handler: function () {
                        var selectitem = Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getSelectionModel().getSelections();
                        var selectedcount = selectitem.length;
                        var itemarr = [];
                        for (var i = 0; i < selectedcount; i++)
                        {
                            itemarr.push(selectitem[i].data.stit_ID);
                        }
                        if (selectedcount != 0)
                        {
                            Application.Finascop_Stock.Cache.cid = cid;
                            Application.Finascop_Stock.Cache.itemarr = itemarr;
                            var itemType = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                            Application.Finascop_Stock.Cache.itemType = itemType;
                            saveCheckedItem(cid, itemarr, itemType);
                        } else
                        {
                            Ext.MessageBox.alert("Notification", "Please check,Some box entries are not valid.");
                        }
                    }
                }

            ], listeners: {
                afterrender: function () {
                    if (_SESSION.IS_MEDICINE_REQUIRED != 1) {
                        Ext.getCmp('radiobuttonFinascopStockId').hide();
                    }

                }

            }

        });

        resultWindow.doLayout();
        resultWindow.show();
        resultWindow.center();

    };


    var saveCheckedItem = function (cid, itemarr, itemtype) {

        var form_data = {
            cid: cid,
            itemarr: itemarr,
            itemtype: itemtype
        };
        var params = {
            action: 'Insert',
            module: 'Finascop_Stock',
            op: 'saveitemvendor',
            extrainfo: 'Save Items to Vendors',
            id: cid
        };
        APICall(params, Application.Finascop_Stock.saveCheckedItem, form_data);

    };
    var vendorGrid = function (cid) {
        //console.log('vendor id',cid);
        var vendorcol = venderItemColmodel();
        var venderstore = venderItemStore();
        var vendorlist = vendortb();

        var vendor_filter = new Ext.ux.grid.GridFilters({
            remote: true,
            filters: [
                {
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }, {
                    type: 'string',
                    dataIndex: 'category'
                }, {
                    type: 'string',
                    dataIndex: 'brand',
                }, {
                    type: 'string',
                    dataIndex: 'variant'
                }
            ]
        });
        vendor_filter.remote = true;
        vendor_filter.autoReload = true;

        var vendorItemgrid = new Ext.grid.GridPanel({
            hideMode: 'display',
            loadMask: true,
            store: venderstore,
            colModel: vendorcol,
            tbar: vendorlist,
            plugins: [vendor_filter],
            width: 800,
            height: 400,
            frame: false,
            border: false,
            hideBorders: true,
            //iconCls: 'icon-grid',
            id: 'gridFinascopStockVenderitemGridgeneration',
            sm: check_box,
            viewConfig: {
                forceFit: true
            }


        });
        return vendorItemgrid;
    };

    var vendortb = function () {
        var tbar = new Ext.Toolbar({
            //layout: 'column',
            style: 'margin:5px 1px 5px 1px;',
            //labelWidth: 100,
            labelAlign: 'left',
            frame: false,
            border: false,
            hideBorders: true,
            items: [
                {
                    xtype: 'radiogroup',
                    width: 150,
                    id: 'radiobuttonFinascopStockId',
                    //columnWidth: 0.25,
                    items: [
                        {boxLabel: 'Medicine', name: 'rb-auto', inputValue: 1, labelWidth: 100},
                        {boxLabel: 'Product', name: 'rb-auto', inputValue: 2, labelWidth: 100, checked: true}

                    ],
                    listeners: {
                        change: function (event, checked)
                        {
                            var current_firstid = event.items.items[0].inputValue;
                            var current_secondid = event.items.items[1].inputValue;
                            //var current_thirdid = event.items.items[2].inputValue;
                            var radioid = Ext.getCmp('radiobuttonFinascopStockId').getValue();

                            if (radioid == current_secondid)
                            {
                                var item_name = '';
                                //filterItems(item_name, radioid);
                            } else if (radioid == current_firstid)
                            {
                                var item_name = '';
                                //filterItems(item_name, radioid);
                            }


                        }
                    }
                },
                {
                    //columnWidth: 0.4,
                    xtype: 'textfield',
                    id: 'radiosearch',
                    width: 500,
                    listeners: {
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                                var search_bar = Ext.getCmp('radiosearch').getValue();
                                if (item_search_item != 0 && search_bar != '')
                                {
                                    filterItems(search_bar, item_search_item);
                                }
                            }
                        }
                    }
                },
                {
                    frame: false,
                    border: false,
                    // columnWidth: 0.025
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/search.png',
                    xtype: 'button',
                    //columnWidth: 0.075,
                    text: 'Search',
                    handler: function () {
                        var item_search_item = Ext.getCmp('radiobuttonFinascopStockId').getValue();
                        var search_bar = Ext.getCmp('radiosearch').getValue();
                        if (item_search_item != 0 && search_bar != '')
                        {
                            filterItems(search_bar, item_search_item);
                        }
                    }
                },
                {
                    frame: false,
                    border: false,
                    // columnWidth: 0.025
                },
                {
                    icon: IMAGE_BASE_PATH + '/default/icons/reset.png',
                    xtype: 'button',
                    //columnWidth: 0.075,
                    text: 'Reset',
                    handler: function () {
                        Ext.getCmp('radiosearch').reset();
                        filterItems('', '');
                        //Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getStore().reload();
                    }

                }

            ]



        });


        return tbar;

    };
//  filter the items 

    var filterItems = function (item_name, radio_id)
    {
        var gridvalue = Ext.getCmp('gridFinascopStockVenderitemGridgeneration').getStore();
        current_type = radio_id;

        gridvalue.baseParams = {
            currentItem: item_name,
            current_type: radio_id
        }
        gridvalue.load();

    };
    var venderItemColmodel = function () {
        var colmodel = new Ext.grid.ColumnModel({
            sortable: true,
            columns: [
                check_box,
                rowno,
                {header: 'Item', dataIndex: 'stit_SKU', sortable: true}
            ]
        });
        return colmodel;
    };
    var rowno = new Ext.grid.RowNumberer();
    rowno.width = 30;
    var check_box = new Ext.grid.CheckboxSelectionModel({
        multiSelect: true
    });
    var venderItemStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'POST',
            url: modURL + '&op=vendoritemlisting',
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            fields: ['stit_itemName', 'stit_ID', 'brand', 'stit_SKU', 'stit_quantity', 'least_package_type_name'],
            listeners: {
                beforeload: function (thisStore, options) {
                    thisStore.baseParams.current_type = current_type;
                }
            }
        });

//        store.load({
//            params: {
//                start: 0,
//                limit: recs_per_page,
//            }
//        });
        return store;
    };

    var selectvendor = function () {
        var vendor = new Ext.data.ArrayStore({
            fields: ['vendername', 'venderid'],
            data: [
                ['reshma', '1'],
                ['greeshma', '2']
            ]
        });
        return vendor;
    };
// code end

//Reshmapm creating grid
    var vendorItemStoreFn = function () {
        var vendorItemStore = new Ext.data.ArrayStore({
            fields: ['VendorName', 'GSTNO', 'ContactPerson'],
            data: [['Reshma PM', '2314d', 'Sibin John'],
                ['Kavya S', '5678', 'Sibin John']],
            autoLoad: true
        });

        return vendorItemStore;
    };

    var vendorItemGridFn = function () {
        var tbar = new Ext.Toolbar({
            items: [{
                    xtype: 'button',
                    text: 'Add Vendor Item',
                    iconCls: 'add',
                    handler: function () {
                        addVendorItem();
                    }
                }]
        })
        var vendorstore = vendorItemStoreFn();
        var vendorItemGrid = new Ext.grid.GridPanel({
            id: 'gridStockVendoritemList',
            tbar: tbar,
            title: 'Vendor Item',
            store: vendorstore,
            layout: 'fit',
            frame: false,
            border: false,
            loadMask: true,
            stripeRows: true,
            //iconCls: 'vender-items',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Vendor Name',
                    dataIndex: 'VendorName',
                    tooltip: 'Name',
                    sortable: true
                },
                {
                    header: 'GST / VAT',
                    dataIndex: 'GSTNO',
                    tooltip: 'gstno',
                    sortable: true
                },
                {
                    header: 'Contact Person',
                    dataIndex: 'ContactPerson',
                    tooltip: 'Contact Person',
                    sortable: true
                },
                {
                    xtype: 'actioncolumn',
                    items: [
                        {
                            iconCls: 'edit',
                            tooltip: 'Edit details',
                            handler: function ()
                            {

                            }
                        }
                    ]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            bbar: new Ext.PagingToolbar({
                pageSize: 10,
                displayInfo: true,
                displayMsg: "Displaying pages {0} - {1} of {2}",
                emptyMsg: "No Pages to display"
            })
        });
        return vendorItemGrid;
    };
// store for state// edit party
    var stateStoreEdit = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getStates',
            method: 'post',
            fields: ['st_ID', 'st_name'],
            totalProperty: 'totalCount',
            root: 'data'

        });

        return store;
    };
    var districtStoreEdit = function () {
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
    var pmtTermsStoreEdit = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getPaymentTerms',
            method: 'post',
            fields: ['ptc_id', 'ptc_name'],
            root: 'data'
        });
        return store;
    };
    var associatedBranch = function (stpa_latitude, stpa_longitude) {
        var store = new Ext.data.JsonStore({
            url: modURL + '&op=getassociatedBranch',
            method: 'post',
            fields: ['br_ID', 'br_Name'],
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.stpa_latitude = stpa_latitude;
                    this.baseParams.stpa_longitude = stpa_longitude;
                }

            }
        });
        return store;
    };
    var deliveryRule = function (deliveryMode) {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getDeliveryRules',
            method: 'post',
            fields: ['rdr_id', 'rdr_ruleName'],
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.deliveryMode = deliveryMode;
                }

            }
        });
        return store;
    };



    var editPartyPanel = function (mid, map_lat, map_long) {
        //var _associated_Branch = associatedBranch();
        var _payment_store = pmtTermsStoreEdit();
        var _state_store = stateStoreEdit();
        var district_store = districtStoreEdit();
        my_marker = [{
                lat: map_lat,
                lng: map_long,
                marker: {
                    title: "you are here",
                    draggable: false
                },
                listeners: {
                    "onFailure": function () {
                        Ext.MessageBox.alert('Failed locating city ');
                    },
                    "onSuccess": function (point) {

                    },
                    "dragend": function (markerAt) {
                        Ext.getCmp('stpa_latitude').setValue(markerAt.latLng.lat());
                        Ext.getCmp('stpa_longitude').setValue(markerAt.latLng.lng());
                    }
                }
            }];

        var _partyPanel = new Ext.FormPanel({
            frame: false,
            border: false,
            hideBorders: true,
            bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
            monitorValid: true,
            id: 'panelPurchaseEditvendordetails',
            width: winsize.width * 0.4,
            flex: 1,
            layout: 'form',
            //autoHeight: true,
            header: false,
            trackResetOnLoad: false,
            title: 'Edit Party',
            autoScroll: true,
            height: 500,
            labelAlign: 'top',
            hidden: true,
            items: [{
                    xtype: 'panel',
                    layout: 'column',
                    items: [{
                            columnWidth: 0.65,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Name',
                                    id: 'stpa_Fname',
                                    name: 'stpa_Fname',
                                    tabIndex: 400,
                                    anchor: '97%',
                                    allowBlank: false,
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
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Last Name',
                                    id: 'stpa_Lname',
                                    hidden: true,
                                    name: 'stpa_Lname',
                                    //tabIndex: 401,
                                    anchor: '97%',
                                    allowBlank: true,
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        }
                                    }
                                }],
                        }, {
                            columnWidth: 0.35,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'City',
                                    id: 'stpa_City',
                                    name: 'stpa_City',
                                    tabIndex: 401,
                                    inputType: 'text',
                                    anchor: '97%',
                                    allowBlank: false,
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        }
                                    }

                                }]
                        }]
                },
                {
                    xtype: "textarea",
                    height: 50,
                    fieldLabel: 'Addresss',
                    id: 'stpa_Address',
                    name: 'stpa_Address',
                    tabIndex: 402,
                    anchor: '99%',
                    allowBlank: false,
                    style: {'text-transform': 'uppercase'},
                    listeners: {
                        change: function (field, newValue, oldValue) {
                            field.setValue(newValue.toUpperCase());
                        }
                    }
                }, {
                    xtype: 'panel',
                    frame: false,
                    border: false,
                    layout: 'column',
                    items: [{
                            columnWidth: 0.4,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'State',
                                    name: 'st_id',
                                    id: 'st_id',
                                    hiddenName: 'state',
                                    anchor: '97%',
                                    store: _state_store,
                                    valueField: 'st_ID',
                                    tabIndex: 403,
                                    displayField: 'st_name',
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    typeAhead: true,
                                    allowBlank: false,
                                    selectOnFocus: true,
                                    mode: 'local',
                                    listeners: {
                                        select: function () {

                                            var value = Ext.getCmp('dst_Id').getValue();
                                            console.log("District value after dst", value, this.value);

                                            district_store.baseParams.st_Id = this.value;
                                            district_store.load();
                                        }
                                    }
                                }],
                        }, {
                            columnWidth: 0.4,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'combo',
                                    fieldLabel: 'District',
                                    id: 'dst_Id',
                                    name: 'dst_Id',
                                    tabIndex: 404,
                                    hiddenName: 'c_district',
                                    anchor: '97%',
                                    allowBlank: false,
                                    store: district_store,
                                    valueField: 'dst_ID',
                                    displayField: 'dst_Name',
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    typeAhead: true,
                                    selectOnFocus: true,
                                    mode: 'local',
                                }]
                        }, {
                            columnWidth: 0.2,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    fieldLabel: 'Post Codes',
                                    id: 'stpa_PINCODE',
                                    anchor: '98%',
                                    xtype: 'textfield',
                                    name: 'stpa_PINCODE',
                                    allowBlank: false,
                                    //vtype: 'phonespec',
                                    tabIndex: 405,
                                    listeners: {
                                        change: function () {
                                            if (!Ext.isEmpty(Ext.getCmp('stpa_PINCODE').getValue())) {
                                                Ext.getCmp('stpa_mapbutton').enable();
                                            }
                                        }
                                    }
                                }]
                        }]
                },
                {
                    xtype: 'panel',
                    frame: false,
                    border: false,
                    layout: 'column',
                    items: [{
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'GST / VAT',
                                    id: 'stpa_GSTIN',
                                    name: 'stpa_GSTIN',
                                    tabIndex: 406,
                                    anchor: '97%',
                                    //allowBlank: false,
                                    msgTarget: 'under',
                                    //gstText: 'Not a valid GST Number.',
                                    //vtype: 'gst'

                                }]

                        }, {
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'PAN No',
                                    id: 'stpa_PanNo',
                                    tabIndex: 407,
                                    name: 'stpa_PanNo',
                                    anchor: '97%'
                                            //allowBlank: false,
                                }]
                        }
                    ]
                },
                {
                    xtype: 'panel',
                    frame: false,
                    border: false,
                    layout: 'column',
                    items: [{
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Contact Person',
                                    id: 'stpa_ContactPerson',
                                    name: 'stpa_ContactPerson',
                                    tabIndex: 408,
                                    anchor: '97%',
                                    allowBlank: false,
                                    style: {'text-transform': 'uppercase'},
                                    listeners: {
                                        change: function (field, newValue, oldValue) {
                                            field.setValue(newValue.toUpperCase());
                                        }
                                    }

                                }]

                        }, {
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Mobile No',
                                    id: 'stpa_MobileNo',
                                    name: 'stpa_MobileNo',
                                    vtype: 'phonespec',
                                    tabIndex: 409,
                                    anchor: '97%',
                                    allowBlank: false,
                                    maxLength: 10,
                                    minLength: 10,
                                    maxLengthText: 'The maximum length for this field is 10'
                                }]
                        }
                    ]
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Email ID',
                    id: 'stpa_Email',
                    name: 'stpa_Email',
                    tabIndex: 410,
                    anchor: '99%',
                    // allowBlank: false,
                    vtype: 'email'

                },
                {
                    xtype: 'panel',
                    frame: false,
                    border: false,
                    layout: 'column',
                    items: [{
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            items: [{
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'visit_frequency',
                                    forceSelection: true,
                                    fieldLabel: 'Visit Frequency',
                                    emptyText: 'Visit Frequency',
                                    allowBlank: false,
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 411,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: 'Monthly', ptname: 'Monthly'}, {ptid: 'Fortnightly', ptname: 'Fortnightly'}, {ptid: 'Weekly', ptname: 'Weekly'}, {ptid: 'Daily', ptname: 'Daily'}]
                                    })
                                }]

                        },
                        {
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [
                                {
                                    xtype: 'combo',
                                    displayField: 'ptname',
                                    valueField: 'ptid',
                                    mode: 'local',
                                    id: 'oncall_delivery',
                                    forceSelection: true,
                                    fieldLabel: 'On Call delivery',
                                    emptyText: 'On Call delivery',
                                    allowBlank: false,
                                    anchor: '98%',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    editable: true,
                                    minChars: 2,
                                    tabIndex: 412,
                                    store: new Ext.data.JsonStore({
                                        fields: ['ptid', 'ptname'],
                                        data: [{ptid: 'Yes', ptname: 'Yes'}, {ptid: 'No', ptname: 'No'}]
                                    })
                                }]
                        }, {
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    fieldLabel: 'Payment Terms',
                                    xtype: 'lovcombo',
                                    displayField: 'ptc_name',
                                    allowBlank: false,
                                    valueField: 'ptc_id',
                                    mode: 'local',
                                    id: 'paymentTerms',
                                    emptyText: 'Select Payment Terms..',
                                    hiddenName: 'paymentTerms',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    tabIndex: 413,
                                    anchor: '98%',
                                    minChars: 2,
                                    store: _payment_store,
                                    editable: true
                                }]
                        }, {
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Reg Number 1',
                                    id: 'stpa_dlno1',
                                    tabIndex: 414,
                                    name: 'stpa_dlno1',
                                    anchor: '97%'
                                }]
                        }, {
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Reg Number 2',
                                    id: 'stpa_dlno2',
                                    tabIndex: 415,
                                    name: 'stpa_dlno2',
                                    anchor: '97%'
                                }]
                        }, {
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'FSSAI / EORI No.',
                                    id: 'stpa_fssaino',
                                    tabIndex: 416,
                                    name: 'stpa_fssaino',
                                    anchor: '97%'
                                }]
                        }, {
                            columnWidth: 0.5,
                            layout: 'form',
                            frame: false,
                            border: false,
                            labelAlign: 'top',
                            hideBorders: true,
                            items: [
                            ]
                        }
                    ]
                },
                {
                    layout: 'form',
                    columnWidth: .35,
                    border: false,
                    items: [{
                            xtype: 'gmappanel',
                            gmapType: 'map',
                            id: 'stpagooglemap',
                            zoomLevel: 8,
                            height: 380,
                            minGeoAccuracy: 4,
                            scaleControl: true,
                            mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging'],
                            mapControls: ['GSmallMapControl', 'GMapTypeControl'],
                            setCenter: {
                                lat: map_lat,
                                lng: map_long
                            },
                            repaint: function (zoomlevel) {
                                var gmappanel = Ext.getCmp('stpagooglemap');
                                if (zoomlevel) {
                                    gmappanel.zoomLevel = zoomlevel;
                                    gmappanel.getMap().setZoom(zoomlevel);
                                }
                                gmappanel.onMapReady();

                            },
                            markers: my_marker
                        }]
                }, {
                    layout: 'form',
                    columnWidth: .45,
                    border: false,
                    //style: 'margin-left:5px;',
                    items: [{
                            xtype: 'fieldset',
                            title: 'Map Coordinates',
                            border: false,
                            //height: 200,
                            autoHeight: true,
                            items: [{
                                    layout: 'column',
                                    border: false,
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .3,
                                            border: false,
                                            items: [{
                                                    xtype: 'button',
                                                    fieldLabel: 'Coordinates',
                                                    tooltip: 'Locate latitude and longitude',
                                                    icon: IMAGE_BASE_PATH + "/default/icons/map.png",
                                                    iconCls: 'my-icon1',
                                                    id: 'stpa_mapbutton',
                                                    name: 'stpa_mapbutton',
                                                    disabled: true,
                                                    text: 'Find Coordinates',
                                                    tabIndex: 424,
                                                    handler: function () {
                                                        Ext.getCmp('stpagooglemap').clearMarkers();
                                                        my_marker = [];
                                                        my_marker.push({
                                                            geoCodeAddr: Ext.getCmp("stpa_PINCODE").getValue(),
                                                            setCenter: true,
                                                            marker: {
                                                                title: "Click and Drag to Move Around",
                                                                draggable: true
                                                            },
                                                            listeners: {
                                                                onFailure: function () {

                                                                },
                                                                "tilesloaded": function (markerAt) {
                                                                    console.log('markerAt', markerAt);
                                                                    Ext.getCmp('stpa_latitude').setValue(markerAt.latLng.lat());
                                                                    Ext.getCmp('stpa_longitude').setValue(markerAt.latLng.lng());
                                                                },
                                                                onSuccess: function (point) {
                                                                    console.log('point', point);
                                                                    Ext.getCmp('stpa_latitude').setValue(point.latLng.lat());
                                                                    Ext.getCmp('stpa_longitude').setValue(point.latLng.lng());
                                                                },
                                                                "dragend": function (markerAt) {
                                                                    Ext.getCmp('stpa_latitude').setValue(markerAt.latLng.lat());
                                                                    Ext.getCmp('stpa_longitude').setValue(markerAt.latLng.lng());
                                                                }
                                                            },
                                                            icon: null
                                                        });
                                                        Ext.getCmp('stpagooglemap').addScaleControl();
                                                        Ext.getCmp('stpagooglemap').clearMarkers();
                                                        Ext.getCmp('stpagooglemap').addMarkers(my_marker);
                                                        Ext.defer(function () {
                                                            var point = Ext.getCmp('stpagooglemap').getCenterLatLng();
                                                            Ext.getCmp('stpa_latitude').setValue(point.lat);
                                                            Ext.getCmp('stpa_longitude').setValue(point.lng);
                                                            Ext.getCmp('stpagooglemap').clearMarkers();
                                                            Ext.getCmp('stpagooglemap').repaint(13);
                                                            Ext.getCmp('stpagooglemap').addMarkers(my_marker);
                                                        }, 1200);

                                                    }
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .35,
                                            border: false,
                                            items: [{
                                                    xtype: 'textfield',
                                                    fieldLabel: 'Latitude',
                                                    //style: 'padding-left:5px;',
                                                    allowBlank: false,
                                                    tabIndex: 425,
                                                    id: 'stpa_latitude',
                                                    name: 'stpa_latitude',
                                                    anchor: '95%'
                                                }]
                                        }, {
                                            layout: 'form',
                                            border: false,
                                            columnWidth: .35,
                                            items: [{
                                                    xtype: 'textfield',
                                                    fieldLabel: 'Longitude',
                                                    allowBlank: false,
                                                    tabIndex: 426,
                                                    id: 'stpa_longitude',
                                                    name: 'stpa_longitude',
                                                    anchor: '95%'
                                                }]
                                        }]
                                }]
                        }]
                }
            ]
        });
        return _partyPanel;
    };


    var stateStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getStates',
            method: 'post',
            fields: ['st_ID', 'st_name'],
            totalProperty: 'totalCount',
            root: 'data'
        });

        return store;
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



    var editDataSubmit = function (cust_id)
    {
        if (cust_id > 0)
        {
            var cust_id = cust_id;
            var act = 'Update';
        } else
        {
            var act = 'Insert';
            var cust_id = 'I0';
        }
        var form_data = {
            stpa_Fname: Ext.getCmp('stpa_Fname').getValue(),
            stpa_Lname: Ext.getCmp('stpa_Lname').getValue(),
            stpa_GSTIN: Ext.getCmp('stpa_GSTIN').getValue(),
            stpa_Address: Ext.getCmp('stpa_Address').getValue(),
            stpa_ContactPerson: Ext.getCmp('stpa_ContactPerson').getValue(),
            stpa_MobileNo: Ext.getCmp('stpa_MobileNo').getValue(),
            stpa_Email: Ext.getCmp('stpa_Email').getValue(),
            stpa_PanNo: Ext.getCmp('stpa_PanNo').getValue(),
            stpa_City: Ext.getCmp('stpa_City').getValue(),
            stpa_PINCODE: Ext.getCmp('stpa_PINCODE').getValue(),
            st_id: Ext.getCmp('st_id').getValue(),
            dst_Id: Ext.getCmp('dst_Id').getValue(),
            visit_frequency: Ext.getCmp('visit_frequency').getValue(),
            oncall_delivery: Ext.getCmp('oncall_delivery').getValue(),
            stpa_dlno2: Ext.getCmp('stpa_dlno2').getValue(),
            stpa_dlno1: Ext.getCmp('stpa_dlno1').getValue(),
            stpa_fssaino: Ext.getCmp('stpa_fssaino').getValue(),
            stpa_id: cust_id


        };
        var params = {
            action: act,
            module: 'Finascop_Stock',
            op: 'EditVendorDetails',
            extrainfo: 'Save Vendors - ' + act,
            id: cust_id
        };
        console.log('form_data', form_data);
        console.log('params', params);
        APICall(params, Application.Finascop_Stock.editDataSubmit, form_data);
    };



    var saveConversionDetails = function () {
        var consumptiongrid = Ext.getCmp('consumption_grid_panel');
        var producedgrid = Ext.getCmp('production_grid_panel');
        var consumptiongrid_store = consumptiongrid.getStore();
        var producedgrid_store = producedgrid.getStore();
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var consumptiongridData = Ext.pluck(consumptiongrid_store.getRange(), 'data');
        var producedgridData = Ext.pluck(producedgrid_store.getRange(), 'data');
        var items_consumed = (consumptiongrid_store.getCount());
        var items_produced = (producedgrid_store.getCount());

        var form_data = {
            consumptiongridData: consumptiongridData,
            producedgridData: producedgridData,
            items_consumed: items_consumed,
            items_produced: items_produced
        };

        var params = {
            action: 'Insert',
            module: 'finascop_stock',
            op: 'saveConversion',
            id: '1',
            extrainfo: 'asd'
        };
        APICall(params, Application.Finascop_Stock.saveConversionData, form_data);
    };




    return{
        Cache: {},
        initItemmaster: function () {
            //var panelId = 'itemmaster_master_panel';
            var panelId = 'itemmaster_grid';
            var itemmaster_main = Ext.getCmp(panelId);
            if (Ext.isEmpty(itemmaster_main))
            {
                //itemmaster_main = itemmasterMasterPanel(panelId);
                itemmaster_main = itemmasterGrid(panelId);
            }
            itemmasterData();
            Application.UI.addTab(itemmaster_main);
            itemmaster_main.doLayout();
            return itemmaster_main;
        },
        initStockconversion: function () {

            var stockconversion_grid = Ext.getCmp('stock_conversion_grid');
            if (!stockconversion_grid)
            {
                stockconversion_grid = createStockConversionGrid();
            }
            Application.UI.addTab(stockconversion_grid);
            stockconversion_grid.doLayout();
            updatePagination(stockconversion_grid);
            return stockconversion_grid;


        },
        group: function () {
            var groupgrid = Ext.getCmp('group_grid');

            if (!groupgrid)
            {
                groupgrid = createGroupPanel();

            }

            Application.UI.addTab(groupgrid);
            groupgrid.doLayout();
        },
        initParty: function (type) {
            partytype = type;
            var panelId = 'panelFinascopStockListvendoritem';
            var listVendor = Ext.getCmp(panelId);
            if (Ext.isEmpty(listVendor))
            {
                listVendor = ListvendoritemPanel(panelId);
                Application.UI.addTab(listVendor);
                listVendor.doLayout();
            } else
            {
                Application.UI.addTab(listVendor);
            }

        },
        initVendorItems: function () {

            var panelId = 'panelVendoritem';
            var vendoritemMain = Ext.getCmp(panelId);
            if (Ext.isEmpty(vendoritemMain))
            {
                vendoritemMain = vendorItemGridFn(panelId);
                Application.UI.addTab(vendoritemMain);
                vendoritemMain.doLayout();
            } else
            {
                Application.UI.addTab(vendoritemMain);
                vendoritemMain.doLayout();
            }
        },
        initStockRegister: function () {
            var panelId = 'stockregister_master_panel';
            var stockregister_main = Ext.getCmp(panelId);
            if (Ext.isEmpty(stockregister_main))
            {
                stockregister_main = stockRegisterMasterPanel(panelId);
            }
            Application.UI.addTab(stockregister_main);
            stockregister_main.doLayout();
            return stockregister_main;
        },
        saveBranchStockRateData: function () {
            var branch_StockRate_window = Ext.getCmp('branch_stock_rate_window');
            var grid = Ext.getCmp('branchStockRate_grid');
            var grid_store = grid.getStore();
            var data = Ext.pluck(grid_store.getRange(), 'data');
            //var currentUpdatedTimeInDataBase;
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=setBranchStockRateData',
                method: 'POST',
                params: {
                    "party_data": Ext.encode(data),
                    "commonRate": Ext.getCmp('commonRate').getValue(),
                    "min_stock": Ext.getCmp('min_stock').getValue(),
                    "curr_stock": Ext.getCmp('curr_stock').getRawValue(),
                    "hide_curr_stock": Ext.getCmp('hide_curr_stock').getValue(),
                    apikey: _SESSION.apikey,
                    "partyId": Ext.getCmp('tb_party').getValue(),
                    "ItemId": Ext.getCmp('itemid').getValue(),
                    integrity_key: Ext.getCmp('integrity_key').getValue(),
                    tstamp: t_stamp


                },
                success: function (response) {


                    var tmp = Ext.decode(response.responseText);

                    if (tmp.success === true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg, function (btn) {
                            branch_StockRate_window.close();
                        });

                    } else
                    {
                        Ext.MessageBox.alert('Error', tmp.msg, function (btn) {

                        });
                    }

                },
//               

                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg, function (btn) {
                        branch_StockRate_window.close();
                    });

                }
            });
        },
        //saving branch stock rate data ends//

        saveConversionData: function () {
            var consumptiongrid = Ext.getCmp('consumption_grid_panel');
            var producedgrid = Ext.getCmp('production_grid_panel');
            var consumptiongrid_store = consumptiongrid.getStore();
            var producedgrid_store = producedgrid.getStore();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var consumptiongridData = Ext.pluck(consumptiongrid_store.getRange(), 'data');
            var producedgridData = Ext.pluck(producedgrid_store.getRange(), 'data');
            var items_consumed = (consumptiongrid_store.getCount());
            var items_produced = (producedgrid_store.getCount());

            Ext.Ajax.request({
                url: modURL + '&op=saveConversion',
                method: 'POST',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    consumptiongridData: Ext.encode(consumptiongridData),
                    producedgridData: Ext.encode(producedgridData),
                    items_consumed: Ext.encode(items_consumed),
                    items_produced: Ext.encode(items_produced)

                },
                success: function (response) {

                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success !== undefined && tmp.success === true)
                    {
                        //excludePartyIds = [];
                        Ext.MessageBox.alert("Success", "Conversion details has been saved successfully", function (btn) {

                            Ext.getCmp('stock_conversion_grid').getStore().reload();
                            Ext.getCmp('create_conversion_window').close();

                        });
                    }
                },
                failure: function () {
                    if (action.failureType == 'server')
                    {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: (obj.error) ? obj.error : obj.errors.reason, buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    } else
                    {
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
        //save itemMaster data//
        saveItemData: function () {
            var itemMaster_window = Ext.getCmp('addItem_window');
            var t = new Date();
            var t_stamp = t.format("YmdHis");


            var is_featured = Ext.getCmp('featured').getValue();
            var is_popular = Ext.getCmp('popular').getValue();
            var ItemVolume;
            var length = Ext.getCmp('item_length').getValue();
            var breadth = Ext.getCmp('item_breadth').getValue();
            var height = Ext.getCmp('item_height').getValue();
            ItemVolume = length * breadth * height;

            var featured = is_featured === true ? '1' : '0';
            var popular = is_popular === true ? '1' : '0';
            /*
             
             */
            Ext.Ajax.request({
                url: modURL + '&op=saveItemMaster',
                method: 'POST',
                params: {
                    dupitem: Application.Finascop_Stock.Cache.dup,
                    item_name: Ext.getCmp('item').getRawValue(),
                    HSN_code: Ext.getCmp('HSN').getRawValue(),
                    stit_package_type_namme: Ext.getCmp('pdt_package_type_id').getRawValue(),
                    stit_category_name: Ext.getCmp('product_category').getRawValue(),
                    stit_brand_name: Ext.getCmp('pdt_brand').getRawValue(),
                    item: Ext.getCmp('item').getValue(),
                    id: Ext.getCmp('itemId').getValue(),
                    HSN: Ext.getCmp('HSN').getValue(),
                    GST: Ext.getCmp('GST').getValue(),
                    MRP: Ext.getCmp('MRP').getValue(),
                    itemgroup: Ext.getCmp('itemgroup').getValue(),
                    description: Ext.getCmp('description').getValue(),
                    stit_product_variant: Ext.getCmp('stit_product_variant').getValue(),
                    pdt_package_type_id: Ext.getCmp('pdt_package_type_id').getValue(),
                    product_category: Ext.getCmp('product_category').getValue(),
                    pdt_brand: Ext.getCmp('pdt_brand').getValue(),
                    featured: featured,
                    pdt_sale_rate: Ext.getCmp('pdt_sale_rate').getValue(),
                    popular: popular,
                    item_length: Ext.getCmp('item_length').getValue(),
                    item_breadth: Ext.getCmp('item_breadth').getValue(),
                    item_height: Ext.getCmp('item_height').getValue(),
                    item_weight: Ext.getCmp('item_weight').getValue(),
                    stit_item_volume: ItemVolume,
                    stit_long_description: Ext.getCmp('stit_long_description').getValue(),
                    stit_quantity: Ext.getCmp('stit_quantity').getValue(),
                    apikey: _SESSION.apikey,
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
                    tstamp: t_stamp
                },
                success: function (response) {

                    var tmp = Ext.decode(response.responseText);

                    if (tmp.success === true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        itemMaster_window.close();
                        Ext.getCmp('itemmaster_grid').getStore().load();

                    } else
                    {
                        Ext.MessageBox.alert("Error", tmp.msg);
                        itemMaster_window.close();
                    }

                },
//               

                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', 'tmp.msg');
                }
            });

        },
        //saving itemMaster ends//

        //save StockGroup data//
        saveStockGroupData: function () {
            var stcokGroup_window = Ext.getCmp('stGroup_window');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=saveStockGroups',
                method: 'POST',
                params: {
                    id: Ext.getCmp('groupId').getValue(),
                    group_name: Ext.getCmp('group').getValue(),
                    parent_group_name: Ext.getCmp('ParentGroup').getValue(),
                    hide_group_name: Ext.getCmp('hide_group_name').getValue(),
                    hide_ParentGroup_name: Ext.getCmp('hide_ParentGroup_name').getValue(),
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (response) {

                    var tmp = Ext.decode(response.responseText);

                    if (tmp.success === true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        stcokGroup_window.close();
                        Ext.getCmp('group_grid').getStore().load();
                        Ext.getCmp('group_tree').getLoader().dataUrl = modURL + '&op=getgroupitems';
                        Ext.getCmp('group_tree').getRootNode().reload();

                        loadGroup();

                    } else
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        stcokGroup_window.close();
                    }

                },
//               

                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        },
        //save viewOrAprroved data//
        saveApprovedItems: function () {
            var view_approve_window = Ext.getCmp('viewItems_window');
            var viewOrAprrovedgrid = Ext.getCmp('streg_viewOrAprroveGrid');
            var viewOrAprroveGridStore = viewOrAprrovedgrid.getStore();
            var viewOrAprroveGridData = Ext.pluck(viewOrAprroveGridStore.getRange(), 'data');
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.Ajax.request({
                url: modURL + '&op=saveApprovedItems',
                method: 'POST',
                params: {
                    gridData: Ext.encode(viewOrAprroveGridData),
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (response) {

                    var tmp = Ext.decode(response.responseText);

                    if (tmp.success === true)
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        view_approve_window.close();
                        Ext.getCmp('streg_master_grid').getStore().load();

                    } else
                    {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                        view_approve_window.close();
                    }

                },
                failure: function (response) {
                    var tmp = Ext.util.JSON.decode(response.responseText);
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            });
        }, ViewMode: function (data) {
            Ext.getCmp('gridpanelVendorAdditem').show();
            Ext.getCmp('property_grid_id').show();
            Ext.getCmp('panelPurchaseEditvendordetails').hide();
            Ext.getCmp('buttonPurchaseVendordetailsCancel').hide();
            Ext.getCmp('buttonPurchaseVendordetailsEdit').show();
            Ext.getCmp('buttonPurchaseVendordetailsSave').hide();
            Ext.getCmp('tabpanelPurchaseVendor').show();
            Ext.getCmp('panelPurchaseEditvendordetails').getForm().reset();
            var propertygridPanel = Ext.getCmp('property_grid_id');
            // propertygridPanel.update(name);

            console.log("data:", data);
            var _Editformdata = Ext.getCmp('panelPurchaseEditvendordetails').getForm();
            Ext.getCmp('fbarhiddenid').setValue(data.customerId);

            propertygridPanel.update(data);
            // var _Editformdata = Ext.getCmp('sc_form').getForm();
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var customerId = data.customerId;

            var cust = Ext.getCmp('fbarhiddenid').getValue();
            console.log("customer...", cust);
            var _addItemgrid_store = Ext.getCmp('gridpanelVendorAdditem').getStore();
            _addItemgrid_store.load({
                params: {
                    cust_id: cust
                }
            });

            //console.log("customer id new ",customerId);
            Ext.getCmp('hidtextitem').setValue(data.customerId);
            if (customerId != 0 || customerId != '')
            {
                _Editformdata.load({
                    params: {
                        customerId: customerId,
                        apikey: _SESSION.apikey,
                        tstamp: t_stamp
                    },
                    url: modURL + '&op=editFormDataLoad',
                    waitMsg: 'Loading...',
                    success: function (form, action) {
                        var tmp = Ext.decode(action.response.responseText);
                        console.log("temp:", tmp);
                        //Ext.getCmp('SP_grid').getStore().load();
                        Ext.getCmp('st_id').getStore().load();
                        //console.log("newis:",newone);
                        console.log("state", tmp.data.st_id)
                        Ext.getCmp('dst_Id').getStore().baseParams.st_Id = tmp.data.st_id;
                        Ext.getCmp('dst_Id').getStore().load();
                        Ext.getCmp('dst_Id').setRawValue(tmp.data.dst_Name);
                        Ext.getCmp('paymentTerms').setValue(tmp.data.stpa_paymentTerms);
                        Ext.getCmp('paymentTerms').getStore().load();



                    }
                });
            }
        },
        editDataSubmit: function ()
        {
            var _editParty = Ext.getCmp('panelPurchaseEditvendordetails');
            var cust_id = Ext.getCmp('fbarhiddenid').getValue();

            var t = new Date();
            var t_stamp = t.format("YmdHis");


            _editParty.getForm().submit({
                waitMsg: "saving.... ",
                url: modURL,
                params: {
                    op: 'EditVendorDetails',
                    customer_id: cust_id,
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp,
                    partytype: partytype
                },
                success: function (form, action) {

                    var tmp = Ext.decode(action.response.responseText);
                    //console.log("Temp is", tmp);
                    if (tmp.success == true)
                    {
                        Application.example.msg("Notification", tmp.msg);
                        Ext.getCmp('gridpanelPurchaseVendordetails').getStore().reload();
                        Ext.getCmp('panelPurchaseEditvendordetails').getForm().reset();
                        Ext.getCmp('panelPurchaseEditvendordetails').hide();
                        Ext.getCmp('tabpanelPurchaseVendor').show();
                        Ext.getCmp('buttonPurchaseVendordetailsSave').hide();
                        Ext.getCmp('buttonPurchaseVendordetailsCancel').hide();

//                        function (btn) {
//
//                            Ext.getCmp('gridpanelPurchaseVendordetails').getStore().reload();
//                            Ext.getCmp('panelPurchaseEditvendordetails').getForm().reset();
//                            Ext.getCmp('panelPurchaseEditvendordetails').hide();
//                            Ext.getCmp('tabpanelPurchaseVendor').show();
//                            Ext.getCmp('buttonPurchaseVendordetailsSave').hide();
//                            Ext.getCmp('buttonPurchaseVendordetailsCancel').hide();
//
//                        }
//                                );
                    } else {
                        Ext.MessageBox.alert("Notification", tmp.msg);
                    }
                },
                failure: function (form, action) {
                    var tmp = Ext.decode(action.response.responseText);
                    Ext.MessageBox.alert('Notification', tmp.msg,
                            function (btn) {

                            });
                }
            });

        }, saveCheckedItem: function (cid, itemarr) {
            console.log('itemarr', Application.Finascop_Stock.Cache.itemarr);
            console.log("user", Application.Finascop_Stock.Cache.cid);
            var cid = Application.Finascop_Stock.Cache.cid;
            var itemarr = Application.Finascop_Stock.Cache.itemarr;
            var itemtype = Application.Finascop_Stock.Cache.itemType;
            Ext.Ajax.request({
                url: modURL + '&op=saveitemvendor',
                method: 'post',
                params: {cid: cid, itemarr: Ext.encode(itemarr), itemtype: itemtype},
                success: function (resp) {
                    var res = Ext.decode(resp.responseText);
                    if (res.success === true)
                    {
                        Application.example.msg('Success', 'Item details has been saved successfully.');
                        Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
                        Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();
//                                function (btn) {
//                                    Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
//                                    Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();
//
//                                });
                    } else
                    {
                        Ext.MessageBox.alert('Notification', 'Product(s) already mapped.',
                                function (btn) {
                                    Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
                                    Ext.getCmp('windowFinascopStockAddvenderitemCreatevendoritem').close();

                                });
                    }
                }
            });

        }, removeItemfromGrid: function (itemid, cust_id) {
            var itemid = Application.Finascop_Stock.Cache.delitemid;
            var cust_id = Application.Finascop_Stock.Cache.delcust_id;
            var stit_type = Application.Finascop_Stock.Cache.delstit_type;
            console.log("itemidddd", itemid);
            Ext.Ajax.request({
                url: modURL + '&op=deleteVendorItemFromgrid',
                method: 'POST',
                params: {
                    current_id: itemid,
                    current_cust: cust_id
                },
                success: function (response) {
                    var res = Ext.decode(response.responseText);
                    if (res.success === true)
                    {
                        Application.example.msg('Success', 'Removed item');
                        Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
//                        function (btn) {
//                            Ext.getCmp('gridpanelVendorAdditem').getStore().reload();
//                        });
                    } else
                    {
                        Ext.MessageBox.alert('Failed');
                    }
                }
            });
        }
//ends viewOrAprroved save

    };

}();