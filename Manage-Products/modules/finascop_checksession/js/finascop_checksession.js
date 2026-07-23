
Application.Finascop_checkSession = function ()
{
    var modURL = '?module=finascop_checksession';

    var switch_function = function (btn) {
        if (btn != 'no') {
            Application.Finascop_checkSession.switchCompany(0);
        }
    };

    var brComboStore = function (ind) {
        return  new Ext.data.ArrayStore({
            url: modURL + '&op=getComboStore&ind=' + ind,
            method: 'POST',
            id: 'comboStore' + ind,
            autoLoad: false,
            isLoaded: false,
            fields: ['id', 'name'],
            listeners: {
                load: function (store, rec, opt) {
                    if (store.getCount() == 1) {
                        var rec = store.getAt(0);
                        if (ind == 1) {
                            Ext.getCmp('current_company').setValue(rec.get("id"));
                            Ext.getCmp('current_company').fireEvent('select', Ext.getCmp('current_company'));
                        } else {
                            Ext.getCmp('current_branch').setValue(rec.get("id"));
                        }
                    }
                }
            }
        });
    };


    var switchCompanyForm = function () {
        var defaultForm = new Ext.form.FormPanel({
            url: modURL + '&op=setCompanyAndBranch',
            width: 600,
            autoHeight: true,
            border: true,
            frame: true,
            labelWidth: 70,
            labelAlign: 'top',
            id: 'current_settings_form',
            defaults: {
                xtype: 'combo',
                anchor: '98%',
                mode: 'remote',
                displayField: 'name',
                valueField: 'id',
                typeAhead: true,
                selectOnFocus: true,
                triggerAction: 'all',
                lazyRender: true,
                allowBlank: false
            },
            items: [
                {
                    store: brComboStore(1),
                    id: 'current_company',
                    fieldLabel: 'Company',
                    hiddenName: 'current_company',
                    listeners: {
                        select: function () {
                            var branch = Ext.getCmp('current_branch');
                            var branch_str = branch.getStore();
                            branch.reset();
                            branch_str.removeAll();
                            branch_str.baseParams.ind = 2;
                            branch_str.baseParams.company = this.getValue();
                            branch_str.load();
                        }
                    }
                }
                ,
                {
                    xtype: 'combo',
                    store: brComboStore(2),
                    id: 'current_branch',
                    fieldLabel: 'Branch',
                    hiddenName: 'current_branch',
                    typeAhead: true,
                    forceSelection: true,
                    editable: true,
                    minChars: 3,
                    mode: 'local',
                    triggerAction: 'all',
                    lazyRender: true
                }]
        });
        return defaultForm;
    };
    return {
        init: function () {
//            console.log('{SESSION.Finascop_ActiveAcctsSwitch == true:' + _SESSION.Finascop_ActiveAcctsSwitch);
//            console.log('|| _SESSION.UserId == 1 :' + _SESSION.UserId +"}");
//            console.log('_SESSION.IsApplicationLogin ==0 :' + _SESSION.IsApplicationLogin);
//            console.log('_SESSION.AssignedBranchCount > 0 :' + _SESSION.AssignedBranchCount);
//            console.log('_SESSION.finascop_current_company_id' +_SESSION.finascop_current_company_id);
            if ((_SESSION.Finascop_ActiveAcctsSwitch == true || _SESSION.UserId == 1) && _SESSION.IsApplicationLogin == 0 && _SESSION.AssignedBranchCount > 0) {
                if ((Ext.isEmpty(_SESSION.finascop_current_company_id) || _SESSION.finascop_current_branch_id < 1 || _SESSION.finascop_current_company_id == 0)) {
                    setTimeout(function () {
                        Application.Finascop_checkSession.switchCompany(1);
                    }, 400);
                } else if (_SESSION.finascop_current_company_isactive == '0') {
                    setTimeout(function () {
                        Ext.Msg.alert('Notification', _SESSION.finascop_current_company + " has been de-activated, so you will not be able to add new entries for this company");
                    }, 400);
                } else if (_SESSION.finascop_current_branch_isactive == '0') {
                    setTimeout(function () {
                        Ext.Msg.alert('Notification', _SESSION.current_branch + " has been de-activated, so you will not be able to add new entries for this branch");
                    }, 400);
                }

            } else {
                setTimeout(function () {
                    var msg = "Finascop_ActiveAcctsSwitch:" + _SESSION.Finascop_ActiveAcctsSwitch;
                    msg += "<br>    UserId:" + _SESSION.UserId;
                    msg += "<br>    IsApplicationLogin:" + _SESSION.IsApplicationLogin;
                    msg += "<br>    AssignedBranchCount:" + _SESSION.AssignedBranchCount;
                    //Ext.Msg.alert('Notification',msg );
                    console.log(msg);
                }, 400);
            }
        },
        switchFromMenu: function () {
            Application.Finascop_checkSession.switchCompany(1);
        },
        switchCompany: function (cancel_btn) {
            var hidden = true;

            var form = switchCompanyForm();
            var defaultWindow = Ext.getCmp('defaultWindowId');
            if (Ext.isEmpty(defaultWindow)) {
                defaultWindow = new Ext.Window({
                    closable: false,
                    layout: 'fit',
                    width: 400,
                    title: 'Current Settings',
                    modal: true,
                    autoHeight: true,
                    resizable: false,
                    items: form,
                    id: 'defaultWindowId',
                    listeners: {
                        afterrender: function () {


                            Ext.getCmp('current_company').getStore().load({
                                callback: function () {
                                    this.isLoaded = true;
                                    if (parseInt(_SESSION.finascop_current_company_id)>0 && !Ext.isEmpty(_SESSION.finascop_current_company_id)) {
                                        Ext.getCmp('current_company').setValue(_SESSION.finascop_current_company_id);
                                        var branch = Ext.getCmp('current_branch');
                                        var branch_str = branch.getStore();
                                        branch.reset();
                                        branch_str.removeAll();
                                        branch_str.baseParams.ind = 2;
                                        branch_str.baseParams.company = _SESSION.finascop_current_company_id;
                                        branch_str.load({
                                            callback: function () {
                                                this.isLoaded = true;
                                                Ext.getCmp('current_branch').setValue(_SESSION.finascop_current_branch_id);

                                            }
                                        });
                                    }
                                }
                            });



                        }

                    },
                    buttons: [{
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            id: 'settings_cancel_btn',
                            hidden: true,
                            handler: function () {
                                //history.go(0);
                                defaultWindow.close();
                            }
                        }, {
                            text: 'Save',
                            icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                            iconCls: 'my-icon1',
                            handler: function () {
                                if (form.getForm().isValid()) {

                                    var form_data = form.getForm().getValues();

                                    var params = {
                                        action: 'Update',
                                        module: 'finascop_checksession',
                                        op: 'setCompanyAndBranch',
                                        id: _SESSION.UserId,
                                        extrainfo: 'asd'
                                    };

                                    APICall(params, Application.Finascop_checkSession.CurrentSettings, form_data);

                                }
                                // history.go(0);
                            }

                        }
                        
                    ]
                });
            }
            if (cancel_btn == 0) {
                Ext.getCmp('settings_cancel_btn').hide();
            } else {
                Ext.getCmp('settings_cancel_btn').show();
            }

            defaultWindow.show();
        },
        switchSettings: function () {
            Ext.Msg.show({
                title: 'Confirm',
                msg: "Do you wish to switch the current company and branch?",
                buttons: Ext.MessageBox.YESNO,
                fn: switch_function
            });
        },
        CurrentSettings: function () {

            var t = new Date();
            var t_stamp = t.format("YmdHis");
            Ext.getCmp('current_settings_form').getForm().submit({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                params: {
                    cmp_name: Ext.getCmp('current_company').getRawValue(),
                    br_name: Ext.getCmp('current_branch').getRawValue(),
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (uForm, res) {
                    var result = Ext.decode(res.response.responseText);
                    if (result && result.success && result.valid) {
                        for (var i in result.data) {
                            _SESSION[i] = result.data[i];
                        }
                        Ext.getCmp('defaultWindowId').close();
                        Ext.get('current_settings').dom.innerHTML = _SESSION.current_branch + ' - ' + _SESSION.finascop_current_company;
                        /*if (_SESSION.is_auditor > 0) {
                         Ext.getCmp('main_menu_btn_6').show();
                         Ext.getCmp('menu_bar').doLayout();
                         } else {
                         Ext.getCmp('main_menu_btn_6').hide();
                         Ext.getCmp('menu_bar').doLayout();
                         }*/
                        history.go(0);
                    } else {
                        Ext.Msg.alert('Notification', "Unable to switch company");
                    }

                    console.log(_SESSION.IsActive);
                },
                failure: function (uForm, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: obj.errors,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    }
                }
            });
        }
    };
}();

