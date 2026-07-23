/*
 * @author Ratheesh Kumar CK <ratheesh@saturn.in>
 * @created on 29-Jul-2008
 *
 * All the Common Generic Utility functions will be written here
 * Like: Ajax Wrapper, etc..
 */

 var combotypes = ['combota', 'combo'];
  var setValueOnLoad = function(opt) {
    if (!Ext.isEmpty(loadedForm)) {
        loadedForm[0].loadRecord(Ext.decode(loadedForm[1]));
    }
}
 var cleanAllComboCache = function(form) {
    for (var i in combotypes) {
        var x = form.findByType(combotypes[i]);
        for (var j in x) {
            x[j].lastQuery = null;
        }
    }
};
 var formError = function(form, action) {
    var r = Ext.decode(action.response.responseText);
    if (r.hasOwnProperty("message") == false || r.message !== "Access denied") {
        switch (action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
                var msg = 'Form fields may not be submitted with invalid values';
                break;
            case Ext.form.Action.CONNECT_FAILURE:
                var msg = 'Issues with the network connection';
                break;
            case Ext.form.Action.SERVER_INVALID:
                var msg = action.result.msg;
        }
    } else {
        var msg = "User is not permitted to do this action";
    }
    Ext.MessageBox.alert("Failure", msg);
};
 var updateRecsPerPage = function(cmp) {

    var grid_height = cmp.getInnerHeight();
    var recsAllowed = Math.round(grid_height / GRID_ROW_HEIGHT);
    var cmpPaginBar = cmp.getBottomToolbar();
    cmpPaginBar.pageSize = recsAllowed;
    cmpPaginBar.doLoad(0);
    return recsAllowed;
};
var validateHhMm = function(time) {
    var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(time);
    if (isValid)
        return true;
    else
        return false;
};

var formatNumbertofixtothreedecimal = function(val) {
    return Ext.util.Format.number(val, "0000.000");
};

function decimalPlaces(decimal, place, code) {
    var pc = '0000' + '.';
    for (i = 0; i < parseInt(place); i++) {
        pc += 0;
    }
    var recimalValue;
    recimalValue = Ext.util.Format.number(decimal, pc);
    if (!Ext.isEmpty(code)) {
        recimalValue = code + ' ' + recimalValue;
    }
    return recimalValue;
};

 var update_recs_per_page = function(cmp) {
    var grid_height = cmp.getInnerHeight();
    var recs_allowed = Math.round(grid_height / GRID_ROW_HEIGHT);
    var cmpPaginBar = cmp.getBottomToolbar();
    cmpPaginBar.pageSize = recs_allowed;
    return recs_allowed;
    //cmpPaginBar.doLoad(0);
};


var hide_types = ['checkbox', 'combota', 'combo', 'datefield', 'numberfield', 'radio', 'textarea', 'textfield'];
var comboRelations = [];
var loadedForm;
var userLoadedForm;
var mkCombo = function (config) {
    var list_store;

    var config = Ext.apply({
        "type": "customer_master",
        "value": "customer_id",
        "display": "customer_name",
        "name": "customer_id",
        "listeners": false,
        "fieldLabel": "Customer",
        "emptyText": "Select a Customer ...",
        "allowBlank": false,
        "editable": true,
        "readOnly": false,
        "id": "customer_id",
        "multiple": false,
        "cascade": null,
        "linkedto": null,
        "hideTrigger": false,
        "af": 0
    }, config);
    if (typeof config.type == "string") {
        var list_store_config = {
            url: '/?module=ui&op=mkCombo',
            baseParams: {
                "type": config.type,
                "value": config.value,
                "display": config.display,
                "name": config.name,
                "cx": config.cx,
                "cd": config.cd,
                "af": config.af
            },
            method: "post",
            mode: "remote",
            id: 'store_' + config.id,
            autoLoad: true,
            reader: new Ext.data.ArrayReader({}, [{
                    name: config.value
                }, {
                    name: config.display
                }]),
            listeners: {
                load: setValueOnLoad
                        /*load: function (opt) {
                         if (!Ext.isEmpty(loadedForm)) {
                         loadedForm[0].loadRecord(Ext.decode(loadedForm[1]));
                         }
                         //  console.log(loadedForm);
                         //   Ext.getCmp(config.id).setValue(Ext.getCmp(config.id).getValue());
                         }*/
            }
        };

        if (config.listeners !== false) {
            list_store_config.listeners = config.listeners;
        }

        if (!Ext.isEmpty(config.linkedto)) {
            list_store_config.linkedto = config.linkedto;
            list_store_config.listeners.beforeload = function () {
                var linkedto = Ext.getCmp(this.linkedto);
                if (!Ext.isEmpty(linkedto) && !Ext.isEmpty(linkedto.value)) {
                    this.baseParams.limit_ext = linkedto.value;
                } else {
                    return false;
                }
            };
            comboRelations[config.linkedto] = config.id;
        }

        list_store = new Ext.data.Store(list_store_config);

    } else {
        list_store = new Ext.data.SimpleStore({
            fields: [config.value, config.display],
            data: config.type
        });
    }

    var cmb_config = {
        store: list_store,
        fieldLabel: config.fieldLabel,
        anchor: (config.anchor) ? config.anchor : '98%',
        lazyRender: true,
        triggerAction: 'all',
        emptyText: config.emptyText,
        allowBlank: (config.allowBlank) ? config.allowBlank : false,
        forceSelection: true,
        mode: ((typeof config.type == "string") ? 'remote' : 'local'),
        id: config.id,
        minChars: 1,
        displayField: config.display,
        valueField: config.value,
        hiddenName: config.name,
        readOnly: (config.readOnly) ? true : false,
        hideOnSelect: false,
        beforeBlur: Ext.emptyFn,
        editable: config.editable,
        hidden: (config.hidden) ? config.hidden : false,
        hideTrigger: (config.hideTrigger) ? config.hideTrigger : false,
        tabIndex: (config.tabIndex) ? config.tabIndex : 10
    };
    if (config.hasOwnProperty("combo_listeners")) {
        cmb_config.listeners = config.combo_listeners;
    }
    if (!Ext.isEmpty(config.cascadeto)) {
        cmb_config.cascadeto = config.cascadeto;
        cmb_config.listeners = {
            select: function () {
                Ext.getCmp(this.cascadeto).value = "";
                Ext.getCmp(this.cascadeto).reset();
                Ext.getCmp(this.cascadeto).getStore().load();
            }
        }
    }

    if (config.multiple == true) {
        var combo = new Ext.ux.form.LovCombo(cmb_config);
    } else {
        var combo = new Ext.form.ComboBox(cmb_config);
    }

    return combo;
};
var mkGrid = function(config) {

    var gridStore = new Ext.data.JsonStore({
        totalProperty: 'totalCount',
        root: 'data',
        fields: config.store_fields,
        remoteSort: true,
        baseParams: {
            "fields": config.store_fields.join(','),
            "type": config.type,
            "sort": config.sort,
            "dir": config.dir
        },
        proxy: new Ext.data.HttpProxy({
            url: '/?module=ui&op=mkGrid',
            method: "post"
        })
    });
    if (config.hasOwnProperty("listeners")) {
        for (var i in config.listeners) {
            gridStore.on(i, config.listeners[i]);
        }
    }
    if (Ext.isEmpty(config.gridConfig)) {
        config.gridConfig = {};
    }
    var needBbar = (!Ext.isEmpty(config.paginate) && config.paginate);
    if (needBbar) {
        delete config.paginate;
        config.gridConfig.bbar = new Ext.PagingToolbar({
            store: gridStore,
            pageSize: recs_per_page,
            displayInfo: true,
            displayMsg: 'Displaying Items {0} - {1} of {2}',
            emptyMsg: "No records to displayed"
        });
    }

    var gridConfig = Ext.apply({
            store: gridStore,
            columns: config.columns,
            frame: true,
            stripeRows: true,
            id: (!Ext.isEmpty(config.id)) ? config.id : Ext.id(),
            viewConfig: {
                forceFit: true,
                preserveScrollOnRefresh: true,
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'
            }
        },
        config.gridConfig
    );
    return new Ext.grid.GridPanel(gridConfig);
};
var showHideTrigger = function (element, state, enable_edit) {

    if (typeof (element.findByType) == "undefined")
        return;
    var xe = (typeof (element.addClass) === "function") ? element : element.el;
    if (state === false) {
        //xe.removeClass('viewOnly');
    } else {
        //xe.addClass('viewOnly');
    }
    for (var i in hide_types) {
        var x = element.findByType(hide_types[i]);
        for (var j in x) {
            var cmp = x[j].el;
            if (typeof (cmp) === "undefined")
                continue;
            if (enable_edit == true)
                cmp.dom.readOnly = state;

            if (state === false) {
                Ext.getCmp(cmp.id).enable();
            } else {
                Ext.getCmp(cmp.id).disable();
            }


            xtype = Ext.getCmp(cmp.id).getXType();
            if (xtype !== 'combo' && xtype !== 'lovcombo') {
                cmp.dom.editable = !state;
            } else {
                cmp.dom.editable = false;
            }
            if (xtype === 'combo' || xtype === 'combota' ||
                    xtype === 'lovcombo' ||
                    xtype === 'compositefield' ||
                    xtype === 'datefield' ||
                    xtype === 'timefield') {
                Ext.getCmp(cmp.id).setHideTrigger(state);
            }
        }
    }
};

function showWait() {
    Ext.Msg.show({
        title: 'Please Wait!',
        width: 300,
        progress: true,
        closable: false,
        wait: true
    });
}




function toTitleCase(str) {
    return str.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

/**
 * in_array() renvois la clef si la valeur est présente, sinon, renvoie -1. \\ exemple : \\
 * var t=new Array("coucou","test","plop");
 * alert( t.in_array("test") ); //affichera 1
 * alert( t.in_array("bobo") ); //affichera -1
 */
Array.prototype.in_array = function (valeur) {
    for (var i in this) {
        //		if (this[i] === valeur) return i;
        if (this[i] === valeur) {
            return true;
        }
    }

    return false;
};

function getScreenHW() {
    var width;
    var height;

    // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight

    if (typeof window.innerWidth != 'undefined') {
        width = window.innerWidth, height = window.innerHeight
    } else
    if (typeof document.documentElement != 'undefined' &&
            typeof document.documentElement.clientWidth !=
            'undefined' &&
            document.documentElement.clientWidth != 0) {
        // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
        width = document.documentElement.clientWidth, height = document.documentElement.clientHeight
    } else {
        // older versions of IE
        width = document.getElementsByTagName('body')[0].clientWidth, height = document.getElementsByTagName('body')[0].clientHeight
    }
    return {
        width: width,
        height: height
    }
}

var postToUrl = function (url, params, method, target) {
    method = method || "post";
    target = target || "_new";
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", url);
    form.setAttribute("target", target);

    for (var key in params) {
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", key);
        hiddenField.setAttribute("value", params[key]);

        form.appendChild(hiddenField);
    }
    document.body.appendChild(form);
    form.submit();
};


function buildAdminPanel(button, event) {
    button.toggle(true, true);
    var tabs = new Ext.TabPanel({
        region: 'center',
        id: 'id_' + button.MenuId,
        activeTab: 0,
        resizeTabs: true,
        tabWidth: 180,
        minTabWidth: 125,
        enableTabScroll: true,
        layoutOnTabChange: false,
        monitorResize: true,
        border: false,
        autoScroll: true,
        margins: '0 3 0 0',
        deferredRender: false,
        hidden: true,
        animCollapse: false,
        plugins: [new Ext.ux.TabCloseMenu()],
        listeners: {
            add: function (tp, cmp, index) {
                cmp.on('close', function () {
                    if (Ext.getCmp('id_' + button.MenuId).items.getCount() == 1)
                        Ext.getCmp('id_' + button.MenuId).hide();
                });
            },
            tabchange: function (tabpanel, actvetab) {
                if (actvetab && actvetab.lastSize) {
                    var tbHt = actvetab.lastSize.height - 30;
                    actvetab.setHeight(tbHt);
                }
            }
        }
    });
    //Add the Panel to ViewPort
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        id: 'vp_' + button.MenuId,
        animCollapse: false,
        autoShow: true,
        tbar: new Ext.Toolbar({
            hidden: true
        }),
        items: [tabs]
    }), button);
}
function buildCustomerPanel(button, event, defaultLoad) {
    buildAdminPanel(button, event);
    /*<?php if(user_access("data_entry","listCustomers")): ?>*/
    defaultLoad();
    /*<?php endif; ?>*/
}
function buildCentralStorePanel(button, event) {
    //button.toggle(true, true);
    //Add the Panel to ViewPort
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        animCollapse: false,
        id: 'vp_' + button.MenuId,
        items: Application.MyphaCentralStore.CentralStorePanel()
    }), button);
}
;

function buildUserPanel(button, event) {
    button.toggle(true, true);
    //Add the Panel to ViewPort
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        animCollapse: false,
        id: 'vp_' + button.MenuId,
        items: Application.Users.UserMainPanel()
    }), button);
}
;
function buildCompanyPanel(button, event) {
    button.toggle(true, true);
    //Add the Panel to ViewPort
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        animCollapse: false,
        id: 'vp_' + button.MenuId,
        items: Application.Finascop_Company.CompanyMainPanel()
    }), button);
}
;

function buildBranchPanel(button, event) {
    button.toggle(true, true);
    //Add the Panel to ViewPort
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        animCollapse: false,
        id: 'vp_' + button.MenuId,
        items: Application.Finascop_Branch.BranchMainPanel()
    }), button);
}
;

function buildAuditingPanel(button, event) {
    button.toggle(true, true);
    //Add the Panel to ViewPort
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        animCollapse: false,
        id: 'vp_' + button.MenuId,
        items: Application.Finascop_Auditing.AuditMainPanel()
    }), button);
}
;

function buildApproverPanel(button, event) {
    button.toggle(true, true);
    //Add the Panel to ViewPort
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        animCollapse: false,
        id: 'vp_' + button.MenuId,
        items: Application.Finascop_Approval.ApproverMainPanel()
    }), button);
}
;
function buildBlankPanel(button, event) {
    button.toggle(true, true);
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        animCollapse: false,
        id: 'vp_' + button.MenuId,
        items: [{
                region: 'center',
                border: false,
                layout: 'fit',
                items: [{
                        html: '<div style="position: relative;background-position: center;margin-top:200px;font-size:50;"><center><h1>Integration is going on!!</h1></center></div>'
                    }]
            }]
    }), button);
}
;



//For Report Menu

function buildReportPanel(button, event) {
    //button.getEl().scale(35, 35);
    var tabs = new Ext.TabPanel({
        region: 'center',
        id: 'so_report_tab',
        activeTab: 0,
        resizeTabs: true, // turn on tab resizing
        tabWidth: 180,
        deferredRender: true,
        minTabWidth: 125,
        enableTabScroll: true,
        layoutOnTabChange: true,
        monitorResize: true,
        border: false,
        autoScroll: true,
        margins: '0 5 0 0',
        hidden: true,
        plugins: [new Ext.ux.TabCloseMenu()],
        listeners: {
            add: function (tp, cmp, index) {
                cmp.on('close', function () {
                    if (Ext.getCmp('so_report_tab').items.getCount() == 1)
                        Ext.getCmp('so_report_tab').hide();
                });
            }
        }
    });

    //Add the Panel to ViewPort
    Application.UI.switchMainView(new Ext.Panel({
        layout: "border",
        id: 'vp_report_center',
        autoShow: true,
        tbar: new Ext.Toolbar({
            hidden: true
        }),
        //tbar: function(){},//menubar,
        items: [tabs]
    }), button);
}
//VType to restrict the selection of date
/*validation  to restrict special characters*/

Ext.form.VTypes['vehicledataStringspecVal'] = /^([A-Z0-9a-z])[A-Z0-9a-z]+$/;
Ext.form.VTypes['vehicledataStringspecMask'] = /[A-Z0-9a-z]/;
Ext.form.VTypes['vehicledataStringspecText'] = 'Enter valid data';
Ext.form.VTypes['vehicledataStringspec'] = function (v) {
    return Ext.form.VTypes['vehicledataStringspecVal'].test(v);
};

Ext.form.VTypes['resrcdataStringspecVal'] = /^([A-Z0-9a-z])[A-Z0-9a-z\s\.]+$/;
Ext.form.VTypes['resrcdataStringspecMask'] = /[A-Z0-9a-z\s\.]/;
Ext.form.VTypes['resrcdataStringspecText'] = 'Enter valid data';
Ext.form.VTypes['resrcdataStringspec'] = function (v) {
    return Ext.form.VTypes['resrcdataStringspecVal'].test(v);
};
Ext.form.VTypes['dataStringspecVal'] = /^[A-Z0-9a-z_]+$|^[A-Z0-9a-z_]+(\s+[A-Z0-9a-z_]+)+$/;
Ext.form.VTypes['dataStringspecMask'] = /[A-Z0-9a-z_\s]/;
Ext.form.VTypes['dataStringspecText'] = 'Enter valid data';
Ext.form.VTypes['dataStringspec'] = function (v) {
    return Ext.form.VTypes['dataStringspecVal'].test(v);
};
Ext.form.VTypes['dataStringVal'] = /^[A-Z0-9a-z-]+$|^[A-Z0-9a-z-]+(\s+[A-Z0-9a-z-]+)+$/;
Ext.form.VTypes['dataStringMask'] = /[A-Z0-9a-z-\s]/;
Ext.form.VTypes['dataStringText'] = 'Enter valid data';
Ext.form.VTypes['dataString'] = function (v) {
    return Ext.form.VTypes['dataStringVal'].test(v);
};
Ext.form.VTypes['alphaStringspecVal'] = /^[A-Za-z0-9]+/;
Ext.form.VTypes['alphaStringspecMask'] = /[A-Za-z0-9]+/;
Ext.form.VTypes['alphaStringspecText'] = 'Enter valid data';
Ext.form.VTypes['alphaStringspec'] = function (v) {
    return Ext.form.VTypes['alphaStringspecVal'].test(v);
};

Ext.form.VTypes['hpaseriesStringspecVal'] = /^[A-Za-z]+/;
Ext.form.VTypes['hpaseriesStringspecMask'] = /[A-Za-z]+/;
Ext.form.VTypes['hpaseriesStringspecText'] = 'Enter valid data A to x';
Ext.form.VTypes['hpaseriesStringspec'] = function (v) {
    return Ext.form.VTypes['hpaseriesStringspecVal'].test(v);
};

Ext.form.VTypes['ofhaseriesStringspecVal'] = /^[A-Za-z]+/;
Ext.form.VTypes['ofhaseriesStringspecMask'] = /[A-Za-z]+/;
Ext.form.VTypes['ofhaseriesStringspecText'] = 'Enter valid data A to x';
Ext.form.VTypes['ofhaseriesStringspec'] = function (v) {
    return Ext.form.VTypes['ofhaseriesStringspecVal'].test(v);
};

Ext.form.VTypes['seriesStringspecVal'] = /^[1-9]+/;
Ext.form.VTypes['seriesStringspecMask'] = /[1-9]+/;
Ext.form.VTypes['seriesStringspecText'] = 'Enter valid data';
Ext.form.VTypes['seriesStringspec'] = function (v) {
    return Ext.form.VTypes['seriesStringspecVal'].test(v);
};


Ext.form.VTypes['phonespecVal'] = /^[+0-9-\s\,]+$/;
Ext.form.VTypes['phonespecMask'] = /[+0-9-\s\,]/;
Ext.form.VTypes['phonespecText'] = 'Enter valid data';
Ext.form.VTypes['phonespec'] = function (v) {
    return Ext.form.VTypes['phonespecVal'].test(v);
};
Ext.form.VTypes['quoteRestrictedStringVal'] = /^[A-Z0-9a-z_\'\.\,\-\"\/\(\)\:\&\%\[\]]+$|^[\'\"A-Z0-9a-z_\'\.\,\-\"\/\(\)\:\&\%\[\]]+(\s+[A-Z0-9a-z_\'\.\,\-\"\/\(\)\:\&\%\[\]]+)+$/;

Ext.form.VTypes['quoteRestrictedStringMask'] = /[A-Z0-9a-z_\s\.\,\-\/\(\)\:\&\%\[\]]/;
Ext.form.VTypes['quoteRestrictedStringText'] = 'Enter valid data';
Ext.form.VTypes['quoteRestrictedString'] = function (v) {
    return Ext.form.VTypes['quoteRestrictedStringVal'].test(v);
};
Ext.form.VTypes['specialmastertypeVal'] = /^[A-Z0-9a-z_\'\.\,\-\"\/\(\)\:\&\%\[\]]+$|^[\'\"A-Z0-9a-z_\'\.\,\-\"\/\(\)\:\&\%\[\]]+(\s+[A-Z0-9a-z_\'\.\,\-\"\/\(\)\:\&\%\[\]]+)+$/;

Ext.form.VTypes['specialmastertypeMask'] = /[A-Z0-9a-z_\s\-\/\(\)\&]/;
Ext.form.VTypes['specialmastertypeText'] = 'Enter valid data';
Ext.form.VTypes['specialmastertype'] = function (v) {
    return Ext.form.VTypes['specialmastertypeVal'].test(v);
};


/*Ext.form.VTypes['quoteRestrictedStringVal']  = /[^"]/;
 Ext.form.VTypes['quoteRestrictedStringMask'] = /.+/;
 Ext.form.VTypes['quoteRestrictedStringText'] = 'Enter valid data';
 Ext.form.VTypes['quoteRestrictedString'] = function(v) {
 return Ext.form.VTypes['alphaStringspecVal'].test(v);
 };*/

//function to calculate maximum no.of rows a grid can accomodate
var finascop_update_recs_per_page = function (cmp) {
    if (cmp.id == 'deadlinesPanel')
        grid_row_height = 34;
    var grid_height = cmp.getInnerHeight();
    var recs_allowed = Math.round(grid_height / grid_row_height);
    var cmpPaginBar = cmp.getBottomToolbar();
    cmpPaginBar.pageSize = recs_allowed;
    cmpPaginBar.doRefresh();
    return recs_allowed;
};

var updateRecsPerPage = function (cmp) {
    grid_row_height = 23;
    var grid_height = cmp.getInnerHeight();
    var recsAllowed = Math.round(grid_height / grid_row_height);
    var cmpPaginBar = cmp.getBottomToolbar();
    cmpPaginBar.pageSize = recsAllowed;
    cmpPaginBar.doLoad(0);
    return recsAllowed;
};
function getSizeControlText(cmp) {

    var text = {
        xtype: 'numberfield',
        id: 'perPage',
        width: '30',
        listeners: {
            blur: function (txt, obj) {
                try {

                    var bbar = Ext.getCmp(cmp.id).getBottomToolbar();

                    if (txt.getValue() != '' && txt.getValue() > 0)
                        bbar.pageSize = txt.getValue();
                    else
                        bbar.pageSize = autoAdjustRecsPerPage(cmp);
                    /* Updated By: Anju Prema <anju@saturn.in>
                     * Updated On: 3 Nov 2011
                     *
                     * Updated this to fix the issue with pagination when all option is
                     * selected after the user visits 2nd page. This helped to switch the page
                     * to 1st when pagination option is changed.*/
                    //bbar.doLoad(bbar.cursor);
                    bbar.doLoad(0);
                } catch (e) {
                    console.log(e);
                }
            }
        }
    };
    return text;
}
function autoAdjustRecsPerPage(gPanel) {
    var grid_row_height = 25;
    var grid_height = Ext.getCmp(gPanel.id).getInnerHeight();
    var recs_allowed = Math.round(grid_height / grid_row_height);
    return recs_allowed;
}
function selectallItemfn(combo, record, index) {

    if (index == 0 && (record.data.checked == true)) {
        combo.selectAll();
        // var checked_val = combo.getRawValue();
        //checked_val = checked_val.replace("All, ", "");  
        combo.setRawValue('All');
    } else if ((index == 0) && (record.data.checked != true)) {
        combo.deselectAll();
    } else {
        //If any value is selected other than 'all', deselect all first.

        var checked_vals = combo.getRawValue();
        checked_vals = checked_vals.replace("All, ", "");
        if (combo.store.data.items[0].data.checked == true)
        {
            var chkvals = combo.getCheckedValue();
            chkvals = chkvals.replace("0", "");
            combo.setValue(chkvals);
        }
        combo.setRawValue(checked_vals);
        // combo.setValue(checked_vals);
    }
}
var hideSpinner = function (obj, response) {
    Ext.Msg.hide();
    try {
        var resp = response.responseText;
        if (resp) {
            var tmp = Ext.decode(resp);
            if (resp.match("\<!DOCTYPE html PUBLIC")) {
                Ext.Msg.alert('Warning', 'Browser session has expired !!!', function () {
                    showSpinner();
                    document.location.href = SITE_URL;
                });
                return false;
            } else if (tmp.invalid_api) {

                Ext.Msg.alert('Notification', 'This user-id is already signed on using another browser');

                setTimeout(function () {
                    Ext.Ajax.request({
                        waitTitle: '',
                        waitMsg: 'This user-id is already signed on using another browser',
                        url: '?module=user',
                        params: {
                            'op': 'logout'
                        },
                        failure: function (response, options) {
                            Ext.Msg.hide();
                        },
                        success: function (response, options) {
                            Ext.Msg.hide();
                            window.location = '';
                        }
                    });
                }, 1000);


            } else {
                Ext.decode(response.responseText);
            }
        }
    } catch (e) {
        Ext.Msg.show({
            title: 'Error - ' + e.message,
            icon: Ext.Msg.ERROR,
            msg: 'Bad response from server: ' + response.responseText,
            buttons: Ext.Msg.OK
        });
        return false;
    }
};

Ext.Ajax.on('requestcomplete', hideSpinner);
Ext.Ajax.on('requestexception', hideSpinner);

Ext.override(Ext.grid.NumberColumn, {    
    align: 'right'
  });

Ext.override(Ext.form.NumberField, {
    getErrors: function (value) {
        var errors = Ext.form.NumberField.superclass.getErrors.apply(this, arguments);

        value = Ext.isDefined(value) ? value : this.processValue(this.getRawValue());

        if (value.length < 1) { // if it's blank and textfield didn't flag it then it's valid
            return errors;
        }
        value = String(value).replace(/,/g, "");
        value = String(value).replace(this.decimalSeparator, ".");

        if (isNaN(value)) {
            errors.push(String.format(this.nanText, value));
        }

        var num = this.parseValue(value);

        if (num < this.minValue) {
            errors.push(String.format(this.minText, this.minValue));
        }

        if (num > this.maxValue) {
            errors.push(String.format(this.maxText, this.maxValue));
        }

        return errors;
    },
    getValue: function () {
        var val = Ext.form.NumberField.superclass.getValue.call(this);
        val = String(val).replace(/,/g, "");
        val = this.fixPrecision(this.parseValue(val));
        return val;
    },
    setValue: function (v) {
        v = String(v).replace(/,/g, "");
        v = Ext.isNumber(v) ? v : parseFloat(String(v).replace(this.decimalSeparator, "."));
        v = this.fixPrecision(v);
        v = isNaN(v) ? '' : String(v).replace(".", this.decimalSeparator);

        if (this.format == FINASCOP_CURRENCY_FORMAT) {
            this.rawValue = v;
            v = formatNumber(v, this.format);
        } else {
            v = Ext.util.Format.number(v, this.format);
        }
        return Ext.form.NumberField.superclass.setValue.call(this, v);
    },
    beforeBlur: function () {
        var v = this.parseValue(this.getRawValue().replace(/,/g, ""));

        if (!Ext.isEmpty(v)) {
            this.setValue(v);
        }
    }

});
Ext.grid.FinascopNumberColumn = Ext.extend(Ext.grid.Column, {
    format: '0,000.00',
    constructor: function (cfg) {
        Ext.grid.FinascopNumberColumn.superclass.constructor.call(this, cfg);
        if (this.format == FINASCOP_CURRENCY_FORMAT) {
            this.renderer = finascopNumberRenderer(this.format);
        } else {
            this.renderer = Ext.util.Format.numberRenderer(this.format);
        }

    }
});
// registre xtype
Ext.reg('finascopcurrency', Ext.grid.FinascopNumberColumn);

Ext.apply(Ext.grid.Column.types, {finascopcurrency: Ext.grid.FinascopNumberColumn});

var finascopNumberRenderer = function (format) {
    return function (v) {
        return formatNumber(v, format);
    }

}

var formatNumber = function (v, format) {
    if (!format) {
        return v;
    }
    v = Ext.num(v, NaN);
    if (isNaN(v)) {
        return '';
    }
    var comma = ',',
            dec = '.',
            i18n = false,
            neg = v < 0;

    v = Math.abs(v);
    if (format.substr(format.length - 2) == '/i') {
        format = format.substr(0, format.length - 2);
        i18n = true;
        comma = '.';
        dec = ',';
    }

    var hasComma = format.indexOf(comma) != -1,
            psplit = (i18n ? format.replace(/[^\d,]/g, '') : format.replace(/[^\d\.]/g, '')).split(dec);

    if (1 < psplit.length) {
        v = v.toFixed(psplit[1].length);
    } else if (2 < psplit.length) {
        throw ('NumberFormatException: invalid format, formats should have no more than 1 period: ' + format);
    } else {
        v = v.toFixed(0);
    }

    var fnum = v.toString();
    var int_part_format = format.split('.')[0];
    var fsplit = int_part_format.split(',');
    psplit = fnum.split('.');

    if (hasComma) {
        var cnum = psplit[0],
                parr = [],
                numformatindex = fsplit.length - 1,
                numsplitlen = (cnum.length <= fsplit[numformatindex].length ? cnum.length : fsplit[numformatindex].length)
                , numstrindex, numformatindex;

        for (numstrindex = cnum.length - numsplitlen; numstrindex > 0; ) {
            parr[parr.length] = cnum.substr(numstrindex, numsplitlen);
            if (numstrindex > fsplit[--numformatindex].length) {
                numsplitlen = fsplit[numformatindex].length;
                numstrindex -= numsplitlen;
            } else {
                numsplitlen = numstrindex;
                numstrindex = 0;
            }
        }
        parr[parr.length] = cnum.substr(numstrindex, numsplitlen);
        parr.reverse();
        fnum = parr.join(comma);
        if (psplit[1]) {
            fnum += dec + psplit[1];
        }
    } else {
        if (psplit[1]) {
            fnum = psplit[0] + dec + psplit[1];
        }
    }

    return (neg ? '-' : '') + format.replace(/[\d,?\.?]+/, fnum);

};

Ext.override(Ext.grid.RowNumberer, {
    renderer: function (value, metaData, record, rowIdx, colIdx, store) {
        var rowspan = this.rowspan;
        if (rowspan) {
            metaData.tdAttr = 'rowspan="' + rowspan + '"';
        }
        metaData.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
        //return store.indexOfTotal(record) + 1;
        var incrementedRowId = rowIdx + 1; //doing +1 as row id starts from 0

        return incrementedRowId;
    }
});


Ext.override(Ext.form.Field,
        {afterRender: Ext.form.Field.prototype.afterRender.createSequence(function ()
            {
                var qt = this.qtip;
                if (qt)
                {
                    Ext.QuickTips.register({
                        target: this,
                        title: '',
                        text: qt,
                        enabled: true,
                        showDelay: 20
                    });
                }
            })
        });


function isLoadedJS(path) {
    var scripts = document.getElementsByTagName('script');
    isLoaded = false;
    for (var i in scripts) {
        if (scripts[i].src == path) {
            isLoaded = true;
        }
    }
    return isLoaded;
}

function loadJS(path) {
    //if(!isLoadedJS(path)){
    //Create the script element
    var jsFile = document.createElement('script');
    //set the type
    jsFile.type = 'text/javascript';
    //load the file
    jsFile.src = path;
    //append to document                  
    document.body.appendChild(jsFile);

    //}  
}
;

var EditorContent = "";
var EditorCmp = "";
//template editor with no buttons
Ext.ux.TemplateEditorProduct = Ext.extend(Ext.Panel, {
    isReady: false,
    initComponent: function() {
        this.isReady = false;
        this.layout = 'fit';
        this.on('afterrender', this.onAfterRender);
        this.border = true;
        this.html = '<textarea name="editor_' + this.id + '" id="editor_' + this.id + '" rows="10" cols="80"></textarea>';
        Ext.ux.TemplateEditorProduct.superclass.initComponent.call(this);
        this.addEvents("mceready");
    },
    setValue: function(data) {
        if (this.isReady) {
            this.setRawContent(data);
        } else {
            EditorContent = data;
        }
    },
    getValue: function() {
        return this.getContent();
    },
    setContent: function(data) {
        this.setValue(data);
    },
    setRawContent: function(value) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.setData(value);
    },
    getContent: function() {
        var editor = CKEDITOR.instances['editor_' + this.id];
        return editor.getData();
    },
    insertTag: function(tag) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.insertHtml(tag);
    },
    mceReady: function(id, baseElementId) {
        var me = Ext.getCmp(baseElementId);
        me.isReady = true;
        me.setRawContent(EditorContent);
        me.fireEvent('mceready', me, id);
        EditorContent = "";
    },
    onAfterRender: function() {
        var height = this.getHeight() - 75;
        var myEditor = 'editor_' + this.id;
                CKEDITOR.replace(myEditor, {
            height: height,
            resize_maxHeight: height,
            removePlugins: 'elementspath,resize,autogrow',
            extraPlugins: 'pagebreak',
            resize_enabled: false,
            disableNativeSpellChecker: false,
            skin: 'moono_blue,/ckeditor/moono_blue/',
            filebrowserBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html',
            filebrowserImageBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html?type=Images',
            filebrowserUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Files',
            filebrowserImageUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Images',
            toolbar: [
               // { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'insert', items: ['Image'] }
               // { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
               // { name: 'styles', items: ['PageBreak', 'Maximize'] },
               // '/',
               // { name: 'basicstyles', items: ["Font", "FontSize", 'Bold', 'Italic', 'Underline', '-', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                //{ name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] }

            ]
        });

    }
});

Ext.ux.TemplateEditor = Ext.extend(Ext.Panel, {
    isReady: false,
    initComponent: function() {
        this.isReady = false;
        this.layout = 'fit';
        this.on('afterrender', this.onAfterRender);
        this.border = true;
        this.html = '<textarea name="editor_' + this.id + '" id="editor_' + this.id + '" rows="10" cols="80"></textarea>';
        Ext.ux.TemplateEditor.superclass.initComponent.call(this);
        this.addEvents("mceready");
    },
    setValue: function(data) {
        if (this.isReady) {
            this.setRawContent(data);
        } else {
            EditorContent = data;
        }
    },
    getValue: function() {
        return this.getContent();
    },
    setContent: function(data) {
        this.setValue(data);
    },
    setRawContent: function(value) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.setData(value);
    },
    getContent: function() {
        var editor = CKEDITOR.instances['editor_' + this.id];
        return editor.getData();
    },
    insertTag: function(tag) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.insertHtml(tag);
    },
    mceReady: function(id, baseElementId) {
        var me = Ext.getCmp(baseElementId);
        me.isReady = true;
        me.setRawContent(EditorContent);
        me.fireEvent('mceready', me, id);
        EditorContent = "";
    },
    onAfterRender: function() {
        var height = this.getHeight() - 75;
        var myEditor = 'editor_' + this.id;
        CKEDITOR.replace(myEditor, {
            allowedContent: true,
            contentsCss: [
                'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap',
                '//cdn.ckeditor.com/4.22.1/full-all/contents.css'
                ],
            bodyClass: 'document-editor',
            height: height,
            resize_maxHeight: height,
            removePlugins: 'elementspath,resize,autogrow',
            extraPlugins: 'pagebreak,colorbutton,colordialog',
            resize_enabled: false,
            disableNativeSpellChecker: false,
            skin: 'moono_blue,/ckeditor/moono_blue/',
            filebrowserBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html',
            filebrowserImageBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html?type=Images',
            filebrowserUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Files',
            filebrowserImageUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Images',
            toolbar: [
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
                { name: 'styles', items: ['PageBreak', 'Maximize','Source'] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                { name: 'basicstyles', items: ['Font', 'FontSize','Bold', 'Italic', 'Underline', '-', 'Strike'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] },
                { name: 'clipboard', items: ['PasteFromWord'] }
               /* { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
                { name: 'styles', items: ['PageBreak', 'Maximize'] },
                '/',
                { name: 'basicstyles', items: ["Font", "FontSize", 'Bold', 'Italic', 'Underline', '-', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] }*/

            ],stylesSet: [
                { name: 'Arial', element: 'span', styles: { 'font-family': 'Arial, Helvetica, sans-serif' } },
                { name: 'Comic Sans MS', element: 'span', styles: { 'font-family': 'Comic Sans MS, cursive, sans-serif' } },
                { name: 'Courier New', element: 'span', styles: { 'font-family': 'Courier New, Courier, monospace' } },
                { name: 'Courier New', element: 'span', styles: { 'font-family': 'Courier New, Courier, monospace' } },
                { name: 'Poppins', element: 'span', styles: { 'font-family': 'Poppins, sans-serif' } },
        // Add more font families as needed
                // Add more font families as needed
              ]
        });
    }
});
Ext.ux.TemplateEditorPU = Ext.extend(Ext.Panel, {
    isReady: false,
    initComponent: function() {
        this.isReady = false;
        this.layout = 'fit';
        this.on('afterrender', this.onAfterRender);
        this.border = true;
        this.html = '<textarea name="editor_' + this.id + '" id="editor_' + this.id + '" rows="10" cols="80"></textarea>';
        Ext.ux.TemplateEditorPU.superclass.initComponent.call(this);
        this.addEvents("mceready");
    },
    setValue: function(data) {
        if (this.isReady) {
            this.setRawContent(data);
        } else {
            EditorContent = data;
        }
    },
    getValue: function() {
        return this.getContent();
    },
    setContent: function(data) {
        this.setValue(data);
    },
    setRawContent: function(value) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.setData(value);
    },
    getContent: function() {
        var editor = CKEDITOR.instances['editor_' + this.id];
        return editor.getData();
    },
    insertTag: function(tag) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.insertHtml(tag);
    },
    mceReady: function(id, baseElementId) {
        var me = Ext.getCmp(baseElementId);
        me.isReady = true;
        me.setRawContent(EditorContent);
        me.fireEvent('mceready', me, id);
        EditorContent = "";
    },
    onAfterRender: function() {
        var height = this.getHeight() - 75;
        var myEditor = 'editor_' + this.id;
        CKEDITOR.replace(myEditor, {
            height: height,
            resize_maxHeight: height,
            removePlugins: 'elementspath,resize,autogrow',
            extraPlugins: 'pagebreak',
            resize_enabled: false,
            disableNativeSpellChecker: false,
            skin: 'moono_blue,/ckeditor/moono_blue/',
            filebrowserBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html',
            filebrowserImageBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html?type=Images',
            filebrowserUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Files',
            filebrowserImageUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Images',
            toolbar: [
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
                { name: 'styles', items: ['PageBreak', 'Maximize','Source'] },
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-', 'Strike'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] }
               /* { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
                { name: 'styles', items: ['PageBreak', 'Maximize'] },
                '/',
                { name: 'basicstyles', items: ["Font", "FontSize", 'Bold', 'Italic', 'Underline', '-', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] }*/

            ]
        });
    }
});
Ext.ux.TemplateEditorNL = Ext.extend(Ext.Panel, {
    isReady: false,
    initComponent: function() {
        this.isReady = false;
        this.layout = 'fit';
        this.on('afterrender', this.onAfterRender);
        this.border = true;
        this.html = '<textarea name="editor_' + this.id + '" id="editor_' + this.id + '" rows="10" cols="80"></textarea>';
        Ext.ux.TemplateEditorNL.superclass.initComponent.call(this);
        this.addEvents("mceready");
    },
    setValue: function(data) {
        if (this.isReady) {
            this.setRawContent(data);
        } else {
            EditorContent = data;
        }
    },
    getValue: function() {
        return this.getContent();
    },
    setContent: function(data) {
        this.setValue(data);
    },
    setRawContent: function(value) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.setData(value);
    },
    getContent: function() {
        var editor = CKEDITOR.instances['editor_' + this.id];
        return editor.getData();
    },
    insertTag: function(tag) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.insertHtml(tag);
    },
    mceReady: function(id, baseElementId) {
        var me = Ext.getCmp(baseElementId);
        me.isReady = true;
        me.setRawContent(EditorContent);
        me.fireEvent('mceready', me, id);
        EditorContent = "";
    },
    onAfterRender: function() {
        var height = this.getHeight() - 75;
        var myEditor = 'editor_' + this.id;
        CKEDITOR.replace(myEditor, {
            height: height,
            resize_maxHeight: height,
            removePlugins: 'elementspath,resize,autogrow',
            extraPlugins: 'pagebreak',
            resize_enabled: false,
            disableNativeSpellChecker: false,
            skin: 'moono_blue,/ckeditor/moono_blue/',
            filebrowserBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html',
            filebrowserImageBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html?type=Images',
            filebrowserUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Files',
            filebrowserImageUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Images',
            toolbar: [
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
                { name: 'styles', items: ['PageBreak', 'Maximize','Source'] },
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-', 'Strike'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] }
               /* { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
                { name: 'styles', items: ['PageBreak', 'Maximize'] },
                '/',
                { name: 'basicstyles', items: ["Font", "FontSize", 'Bold', 'Italic', 'Underline', '-', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] }*/

            ]
        });
    }
});
Ext.ux.TemplateEditorAL = Ext.extend(Ext.Panel, {
    isReady: false,
    initComponent: function() {
        this.isReady = false;
        this.layout = 'fit';
        this.on('afterrender', this.onAfterRender);
        this.border = true;
        this.html = '<textarea name="editor_' + this.id + '" id="editor_' + this.id + '" rows="10" cols="80"></textarea>';
        Ext.ux.TemplateEditorAL.superclass.initComponent.call(this);
        this.addEvents("mceready");
    },
    setValue: function(data) {
        if (this.isReady) {
            this.setRawContent(data);
        } else {
            EditorContent = data;
        }
    },
    getValue: function() {
        return this.getContent();
    },
    setContent: function(data) {
        this.setValue(data);
    },
    setRawContent: function(value) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.setData(value);
    },
    getContent: function() {
        var editor = CKEDITOR.instances['editor_' + this.id];
        return editor.getData();
    },
    insertTag: function(tag) {
        var editor = CKEDITOR.instances['editor_' + this.id];
        editor.insertHtml(tag);
    },
    mceReady: function(id, baseElementId) {
        var me = Ext.getCmp(baseElementId);
        me.isReady = true;
        me.setRawContent(EditorContent);
        me.fireEvent('mceready', me, id);
        EditorContent = "";
    },
    onAfterRender: function() {
        var height = this.getHeight() - 75;
        var myEditor = 'editor_' + this.id;
        CKEDITOR.replace(myEditor, {
            height: height,
            resize_maxHeight: height,
            removePlugins: 'elementspath,resize,autogrow',
            extraPlugins: 'pagebreak',
            resize_enabled: false,
            disableNativeSpellChecker: false,
            skin: 'moono_blue,/ckeditor/moono_blue/',
            filebrowserBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html',
            filebrowserImageBrowseUrl: '/ckeditor/ckfinder2/ckfinder.html?type=Images',
            filebrowserUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Files',
            filebrowserImageUploadUrl: '/ckeditor/ckfinder2/core/connector/php/connector.php?command=QuickUpload&type=Images',
            toolbar: [
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
                { name: 'styles', items: ['PageBreak', 'Maximize','Source'] },
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-', 'Strike'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] }
               /* { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                { name: 'format', items: ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"] },
                { name: 'styles', items: ['PageBreak', 'Maximize'] },
                '/',
                { name: 'basicstyles', items: ["Font", "FontSize", 'Bold', 'Italic', 'Underline', '-', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] }*/

            ]
        });
    }
});
var checkAllComboAutoFillExists = function(comboList) {
    for (var key in comboList) {
        if (isNaN(key))
            continue;

        var combo = comboList[key];
        if (combo.getXType() === 'lovcombo')
            continue;
        var valuefield = combo.valueField;
        var extactvalue = combo.getValue();
        var isHidden = combo.hidden;
        var mandatoryvalue = combo.allowBlank;
        if (!Ext.isEmpty(extactvalue)) {
            var index = combo.getStore().find(valuefield, extactvalue);
            if (index === -1) {
                if (isHidden === false && mandatoryvalue === true) {
                    combo.markInvalid('Select one from list');
                    isValid = false;
                } else if (isHidden === true && mandatoryvalue === false) {
                    combo.reset();
                    combo.clearInvalid();
                } else
                /*if (isHidden === true && mandatoryvalue === true) {
                                combo.markInvalid('Select one from list');
                                isValid = false;
                                } else*/
                {
                    combo.reset();
                    combo.clearInvalid();
                }
            }
        }
    }
    return true;
};
var isAllComboValid = function(form) {
    var cxlist = ["combo", "combota"];
    for (var i in cxlist) {
        if (!Ext.isEmpty(form.findByType(cxlist[i]))) {
            var comboList = form.findByType(cxlist[i]);
            if (!Ext.isEmpty(comboList)) {
                if (!checkAllComboAutoFillExists(comboList)) {
                    return false;
                }
            }
        }

    }
    return true;
}
Ext.reg('templateeditormce', Ext.ux.TemplateEditor);
Ext.reg('templateeditormcepu', Ext.ux.TemplateEditorPU);
Ext.reg('templateeditormcenl', Ext.ux.TemplateEditorNL);
Ext.reg('templateeditormceal', Ext.ux.TemplateEditorAL);
Ext.reg('templateeditorproduct', Ext.ux.TemplateEditorProduct);
// create our namespace 
Ext.ns('sil.ux');

// define a custom button for the left toolbar
sil.ux.ltitem = Ext.extend(Ext.Button, {
    template: new Ext.Template('<div id="{4}" class="x-btn {3}"><div class="{1}">',
            '<i class="fa fa-{3}"></i><em class="{2}" unselectable="on"><button type="{0}"></button></em>',
            '</div></div>')
});

Ext.reg('sil-ltbutton', sil.ux.ltitem);

Application.example = function () {
    var msgCt;
    function createBox(t, s) {
        return ['<div class="msg">',
            '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
            '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>', t,
            '</h3>', s, '</div></div></div>',
            '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
            '</div>'].join('');
    }
    return {
        msg: function (title, format) {

            if (!msgCt) {
                msgCt = Ext.DomHelper.insertFirst(document.body, {
                    id: 'msg-div'
                }, true);
            }
            msgCt.alignTo(document, 'bl-bl', [10, -90]);
            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 1));
            var m = Ext.DomHelper.append(msgCt, {
                html: createBox(title, s)
            }, true);
            m.slideIn('b').pause(2).ghost("t", {
                remove: true
            });
        }
    };
}();
Application.CacheControl = function () {
    return{
        /**
         * Function to delete the cache file
         * @param {integer} uidnr_admin
         */
        deleteCache: function (uidnr_admin) {
            Ext.Ajax.request({
                url: SITE_URL,
                params: {
                    uidnr_admin: uidnr_admin,
                    module: 'ui',
                    op: 'cache'
                },
                success: function () {
                    Application.example.msg('Notification', 'Cache files deleted...!');
                }
            });
        }
    }
}();

/**
 * @class Ext.ColorPalette
 * @extends Ext.Component
 * Simple color palette class for choosing colors.  The palette can be rendered to any container.<br />
 * Here's an example of typical usage:
 * <pre><code>
var cp = new Ext.ColorPalette({value:'993300'});  // initial selected color
cp.render('my-div');

cp.on('select', function(palette, selColor){
    // do something with selColor
});
</code></pre>
 * @constructor
 * Create a new ColorPalette
 * @param {Object} config The config object
 * @xtype colorpalette
 */
Ext.ColorPalette = Ext.extend(Ext.Component, {
	/**
	 * @cfg {String} tpl An existing XTemplate instance to be used in place of the default template for rendering the component.
	 */
    /**
     * @cfg {String} itemCls
     * The CSS class to apply to the containing element (defaults to 'x-color-palette')
     */
    itemCls : 'x-color-palette',
    /**
     * @cfg {String} value
     * The initial color to highlight (should be a valid 6-digit color hex code without the # symbol).  Note that
     * the hex codes are case-sensitive.
     */
    value : null,
    /**
     * @cfg {String} clickEvent
     * The DOM event that will cause a color to be selected. This can be any valid event name (dblclick, contextmenu). 
     * Defaults to <tt>'click'</tt>.
     */
    clickEvent :'click',
    // private
    ctype : 'Ext.ColorPalette',

    /**
     * @cfg {Boolean} allowReselect If set to true then reselecting a color that is already selected fires the {@link #select} event
     */
    allowReselect : false,

    /**
     * <p>An array of 6-digit color hex code strings (without the # symbol).  This array can contain any number
     * of colors, and each hex code should be unique.  The width of the palette is controlled via CSS by adjusting
     * the width property of the 'x-color-palette' class (or assigning a custom class), so you can balance the number
     * of colors with the width setting until the box is symmetrical.</p>
     * <p>You can override individual colors if needed:</p>
     * <pre><code>
var cp = new Ext.ColorPalette();
cp.colors[0] = 'FF0000';  // change the first box to red
</code></pre>

Or you can provide a custom array of your own for complete control:
<pre><code>
var cp = new Ext.ColorPalette();
cp.colors = ['000000', '993300', '333300'];
</code></pre>
     * @type Array
     */
    colors : [
        '000000', '993300', '333300', '003300', '003366', '000080', '333399', '333333',
        '800000', 'FF6600', '808000', '008000', '008080', '0000FF', '666699', '808080',
        'FF0000', 'FF9900', '99CC00', '339966', '33CCCC', '3366FF', '800080', '969696',
        'FF00FF', 'FFCC00', 'FFFF00', '00FF00', '00FFFF', '00CCFF', '993366', 'C0C0C0',
        'FF99CC', 'FFCC99', 'FFFF99', 'CCFFCC', 'CCFFFF', '99CCFF', 'CC99FF', 'FFFFFF'
    ],

    /**
     * @cfg {Function} handler
     * Optional. A function that will handle the select event of this palette.
     * The handler is passed the following parameters:<div class="mdetail-params"><ul>
     * <li><code>palette</code> : ColorPalette<div class="sub-desc">The {@link #palette Ext.ColorPalette}.</div></li>
     * <li><code>color</code> : String<div class="sub-desc">The 6-digit color hex code (without the # symbol).</div></li>
     * </ul></div>
     */
    /**
     * @cfg {Object} scope
     * The scope (<tt><b>this</b></tt> reference) in which the <code>{@link #handler}</code>
     * function will be called.  Defaults to this ColorPalette instance.
     */
    
    // private
    initComponent : function(){
        Ext.ColorPalette.superclass.initComponent.call(this);
        this.addEvents(
            /**
             * @event select
             * Fires when a color is selected
             * @param {ColorPalette} this
             * @param {String} color The 6-digit color hex code (without the # symbol)
             */
            'select'
        );

        if(this.handler){
            this.on('select', this.handler, this.scope, true);
        }    
    },

    // private
    onRender : function(container, position){
        this.autoEl = {
            tag: 'div',
            cls: this.itemCls
        };
        Ext.ColorPalette.superclass.onRender.call(this, container, position);
        var t = this.tpl || new Ext.XTemplate(
            '<tpl for="."><a href="#" class="color-{.}" hidefocus="on"><em><span style="background:#{.}" class="x-unselectable" unselectable="on">&#160;</span></em></a></tpl>'
        );
        t.overwrite(this.el, this.colors);
        this.mon(this.el, this.clickEvent, this.handleClick, this, {delegate: 'a'});
        if(this.clickEvent != 'click'){
        	this.mon(this.el, 'click', Ext.emptyFn, this, {delegate: 'a', preventDefault: true});
        }
    },

    // private
    afterRender : function(){
        Ext.ColorPalette.superclass.afterRender.call(this);
        if(this.value){
            var s = this.value;
            this.value = null;
            this.select(s, true);
        }
    },

    // private
    handleClick : function(e, t){
        e.preventDefault();
        if(!this.disabled){
            var c = t.className.match(/(?:^|\s)color-(.{6})(?:\s|$)/)[1];
            this.select(c.toUpperCase());
        }
    },

    /**
     * Selects the specified color in the palette (fires the {@link #select} event)
     * @param {String} color A valid 6-digit color hex code (# will be stripped if included)
     * @param {Boolean} suppressEvent (optional) True to stop the select event from firing. Defaults to <tt>false</tt>.
     */
    select : function(color, suppressEvent){
        color = color.replace('#', '');
        if(color != this.value || this.allowReselect){
            var el = this.el;
            if(this.value){
                el.child('a.color-'+this.value).removeClass('x-color-palette-sel');
            }
            el.child('a.color-'+color).addClass('x-color-palette-sel');
            this.value = color;
            if(suppressEvent !== true){
                this.fireEvent('select', this, color);
            }
        }
    }

    /**
     * @cfg {String} autoEl @hide
     */
});
Ext.reg('colorpalette', Ext.ColorPalette);


var activateFilter = function(p,header,value) {
    var f = p.filters.getFilter(header);
    f.setValue(value);
    f.setActive(true);
    //p.getStore().reload();
};





