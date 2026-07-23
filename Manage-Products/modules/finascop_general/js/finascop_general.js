Application.Finascop_General = function () {
    var modURL = '?module=finascop_general';
    var winsize = Ext.getBody().getViewSize();
    var varFinascopUserTypeComboStore = "";

    var saveUserMapping = function () {
        var mapping_grid = Ext.getCmp("user_type_mapping_grid");
        mapping_grid.body.mask('Saving Record Please Wait...');
        var data = [];
        userTypeMappingGridStore.commitChanges();
        //mapping_grid.getStore().commitChanges();
        var modifiedRecords = mapping_grid.getStore().getRange();
        //console.log(modifiedRecords);
        data = Ext.encode(Ext.pluck(modifiedRecords, 'data'))

        Ext.Ajax.request({
            url: modURL + '&op=saveUserMapping',
            method: 'POST',
            timeout: 120000,
            params: {
                data: data
            }, // We are passing xml as a string here by using .xml
            success: function (response) {
                userTypeMappingGridStore.reload();
                var tmp = Ext.decode(response.responseText);
                if (tmp.success === true) {
                    Ext.Msg.alert('Notification', "Saved successfully");
                }
                if (tmp.success === false) {
                    Ext.Msg.alert('Notification', "Failed to save all data");
                }
                Ext.getCmp("user_type_mapping_grid").body.unmask();

            },
            failure: function (response) {
                alert('Failed to save data.');

                if (Ext.getCmp("user_type_mapping_grid")) {
                    Ext.getCmp("user_type_mapping_grid").body.unmask();
                }
            }
        });
    }

    /* store for item combo('finascop_user_type')*/
    var FinascopUserTypeComboStore = function () {
        var finascopUserTypeComboStore = new Ext.data.JsonStore({
            autoLoad: true,
            url: modURL + '&op=getFinascopUserTypes',
            method: 'post',
            fields: ['finascop_user_type_id', 'finascop_user_type'],
            totalProperty: 'totalCount',
            root: 'data'
        });

        return finascopUserTypeComboStore;
    }

    var FinascopUserTypeCombobox = function () {
        var finascopUserTypeCombobox = new Ext.form.ComboBox({
            name: 'finascop_user_type',
            id: 'finascop_user_type',
            store: varFinascopUserTypeComboStore,
            mode: 'remote',
            hiddenName: 'finascop_user_type',
            displayField: 'finascop_user_type',
            valueField: 'finascop_user_type_id',
            triggerAction: 'all',
            lazyRender: true
            //transform: 'finascop_user_type'
        });
        return finascopUserTypeCombobox;
    }

    /* Store for User Type Mapping Grid */
    var UserTypeMappingGridStore = function () {
        var store = new Ext.data.JsonStore({
            autoLoad: false,
            url: modURL + '&op=getUserTypeMapping',
            method: 'post',
            fields: ['project_user_type_id', 'project_user_type', 'finascop_user_type_id', 'finascop_user_type'],
            totalProperty: 'totalCount',
            root: 'data'
        });

        return store;
    };



    var UserTypeMappingGridColModel = function () {

        Ext.util.Format.comboRenderer = function (combo) {
            return function (value) {
                var record = combo.store.getAt(combo.store.find(combo.valueField, value));
                
                return record ? record.get(combo.displayField) : '';
            }
        }
        var finascopUserTypeCombobox = FinascopUserTypeCombobox();
        var UserTypeMappingGrid_cm = new Ext.grid.ColumnModel({
            columns: [{
                    header: 'Project User Type',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'project_user_type',
                    id: 'project_user_type',
                    tooltip: 'Project User Type',
                    width: 210
                },
                {
                    header: 'Finascope User Type',
                    sortable: true,
                    hideable: false,
                    dataIndex: 'finascop_user_type_id',
                    id: 'finascop_user_type',
                    tooltip: 'Finascop User Type',
                    width: 210,
                    editor: finascopUserTypeCombobox,
                    renderer: Ext.util.Format.comboRenderer(finascopUserTypeCombobox)
                }]
        });
        return UserTypeMappingGrid_cm;
    }

    var userTypeMappingGridStore = UserTypeMappingGridStore();

    var UserTypeMappingGrid = function () {



        var userTypeMapping_GridPanel = new Ext.grid.EditorGridPanel(
                {
                    store: userTypeMappingGridStore,
                    layout: 'fit',
                    border: false,
                    title: '',
                    height: 200,
                    id: 'user_type_mapping_grid',
                    loadMask: true,
                    frame: false,
                    clicksToEdit: 1,
                    cm: UserTypeMappingGridColModel(),
                    sm: new Ext.grid.RowSelectionModel({
                        singleSelect: true
                    })

                });

        return userTypeMapping_GridPanel;
    }

    var MapUserTypePanel = function () {
        var panel = new Ext.Panel({
            autoHeight: true,
            frame: true,
            border: false,
            id: 'user_type_mapping_panel',
            layout: 'column',
            items: [{xtype: 'fieldset',
                    columnWidth: 1,
                    id: 'inv_items',
                    items: [{
                            layout: 'column',
                            items: [{
                                    layout: 'form',
                                    columnWidth: 1,
                                    items: [UserTypeMappingGrid()]
                                }]
                        }]
                }]

        });
        return panel;
    }

    return{
        finascopUserTypeMapping: function () {
            varFinascopUserTypeComboStore = FinascopUserTypeComboStore();
            varFinascopUserTypeComboStore.load({
                // store loading is asynchronous, use a load listener or callback to handle results
                callback: function () {
                    var mapUserTypePanel = MapUserTypePanel();
                    var mapUserTypeWindow = new Ext.Window({
                        layout: 'fit',
                        width: winsize.width * 0.33,
                        title: "Finascop User Type Mapping",
                        autoHeight: true,
                        plain: true,
                        modal: true,
                        frame: true,
                        constrainHeader: true,
                        items: [mapUserTypePanel],
                        buttons: [{
                                text: 'Save',
                                id: 'btnsave',
                                icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    saveUserMapping();
                                }
                            },
                            {
                                text: 'Close',
                                id: 'btns_Cancel',
                                icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
                                iconCls: 'my-icon1',
                                handler: function () {
                                    mapUserTypeWindow.close();
                                }
                            }]
                    });
                    userTypeMappingGridStore.load();
                    //loadUserTypeMapping();
                    mapUserTypeWindow.doLayout();
                    mapUserTypeWindow.show();
                    mapUserTypeWindow.center();
                }
            });

        }
    };

}();

