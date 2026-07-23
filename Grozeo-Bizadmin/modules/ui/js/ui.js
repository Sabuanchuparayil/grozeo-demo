// Now that we have defined our namespace we can begin adding members to the
// Ext.Application using the Module Pattern
Application.fTmp = function () {
    alert('init of course; ');
};

Application.UI = function () {
    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Private Area------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Private Variables---------------------------//

    var mainPanelHeight = Ext.getBody().getViewSize().height * 0.87;
    var mainPanelWidth = Ext.getBody().getViewSize().width;
    var modURL = '?module=ui&op=';
    var getModMenuURL = modURL + 'get-mod-menu';

    //Define Status Bar
    var statusBar = {
        region: 'south',
        //height:10,
        border: false,
        bodyStyle: 'height:15px; font-size:0px;',
        bbar: new Ext.ux.StatusBar({
            //text: 'Ready',
            //defaultText: 'Ready   ',
            //cls:'x-statusbar status-load',
            //defaultIconCls: 'my-icon32',
            busyText: 'Loading...',
            id: 'status-bar',
            statusAlign: 'left',
            items: []
        })
    };
    //EO Status Bar

    var status_bar = Ext.getCmp('status-bar');

    //Define South Status region
    var footer = {
        region: 'south',
        id: 'vp-south',
        border: false,
        bodyStyle: 'height:0px; font-size:0px;',
        bbar: new Ext.ux.StatusBar({
            defaultText: 'Ready',
            busyText: 'Loading...',
            id: 'status-bar',
            statusAlign: 'right'
        })
    };

    var tabPanel = new Ext.TabPanel({
        resizeTabs: true, // turn on tab resizing
        tabWidth: 180,
        minTabWidth: 125,
        enableTabScroll: true,
        layoutOnTabChange: true,
        monitorResize: true,
        border: false,
        region: 'center',
        autoScroll: true,
        margins: '0 5 0 0'
    });
    // Define MainPanel Region and Set Default Value
    var mainPanel = new Ext.Panel({
        bodyStyle: 'background: #F0F0F0 none repeat scroll 0 0;',
        id: 'vp-center',
        region: 'center',
        height: mainPanelHeight,
        activeItem: 0, // make sure the active item is set on the container config!
        layoutOnCardChange: true,
        deferredRender: true,
        defaults: {
            // applied to each contained panel
            border: false
        },
        layout: 'card',
        layoutConfig: {
            easing: 'easeOut',
            duration: 0.4,
            opacity: .1
        }
    });




    //-----------------------------------------------------------------------//
    //---------------------------Private Methods-----------------------------//
    //-----------------------------------------------------------------------//


    var hideUILoading = function () {
        Ext.get('loading').hide();
        Ext.get('loading-mask').fadeOut({
            remove: false
        });
    };

    var showUILoading = function () {
        return;
        Ext.get('loading').show();
        Ext.get('loading-mask').show();
        document.getElementById('loading-msg').innerHTML = 'Loading Module. Please be patient...';
    };



    /**
     * Swith the CENTER region of the viewport with the passed component
     * 1. Hide existing Center Regions
     * 2. Highlight the trigger button (to indicate panel exists)
     * 3. Update CENTER region with the new panel
     * @param	{Object} Component to be activated.
     * @param	{Object} Object/Button from where the Module is invoked! Probably a button
     * @return	void
     **/
    var switchMainView = function (cmp, button) {

        if (Ext.isEmpty(cmp))
            return;




        //Check if the Component is aleady Exists, if Exists, activate it

        var existingCMP = mainPanel.getComponent(cmp.id);
        if (!Ext.isEmpty(existingCMP)) {
            delete (cmp);
            mainPanel.layout.setActiveItem(existingCMP.id);
            return;
        }
        /*
         * Check wheter the new PANEL (center) has any toolbar provion
         * If toolbar is given, load the menus for the Toolbar dynamically based on the User's capabilities.
         * If no Menu is permitted for the logged in user, then remove Toolbar
         */
        var tbar = cmp.getTopToolbar();
        if (!Ext.isEmpty(tbar)) {
            loadModToolbar(tbar, button.MenuId);
        }


        /**
         * If the new Panel needs to be added, the show Loading mask.
         * To indicate the reason for dealy to the user that, the the panel is loading
         **/

        document.getElementById('loading-msg').innerHTML = 'Loading Module. Please be patient...'

        // showUILoading();
        cmp.on('render', function () {
            hideUILoading();
        });
        //If the Component is not exists already, then add the Component to the UI and activate it

        mainPanel.add(cmp);
        mainPanel.layout.setActiveItem(cmp.id)

        //Refresh ViewPort for affecting the changes as per the new component.

        viewport.doLayout();
        mainPanel.setHeight(mainPanelHeight);
        //console.log("internal mainPanel  height:" +mainPanel.getHeight());
    };



    /**
     * Add the Passed component as a Tab to the currently Active CENTER region
     * 1. Only Applicable if the CENTER region is a Tab Panel
     * @param	{Object} Component to be added.
     * @param	{Object} Container, to where the component needs to be added. If the given container is NULL, component will be added to the active panel
     * @param	{Boolean} Indicates whether the newly added tabe needs be activated immediately or not. By default TRUE
     * @param	{Integer} The index at which the Component will be inserted into the Container's items collection
     * @return	void
     **/
    var addTab = function (cmp, container, activateOnAdd, index, SortOrder) {
        var tabPanel;
        if (!Ext.isEmpty(SortOrder))
            sort_order_array[sort_order_array.length] = SortOrder;
        if (Ext.isEmpty(container))
            container = mainPanel.layout.activeItem;
        else
            container = Ext.getCmp(container);

        activateOnAdd = (Ext.isEmpty(activateOnAdd)) ? true : activateOnAdd;

        if (container.getXType() != "tabpanel") {
            tabPanel = container.findByType('tabpanel')[0];
        } else {
            tabPanel = container;
        }
        if (Ext.isEmpty(tabPanel))
            return;

        //Since the TABPanel is hidden by default, Make it visible first
        tabPanel.show();
        //Check if the Tab is aleady Exists, just show and break the remaining execution
        var existingCMP = tabPanel.getComponent(cmp.id);
        if (!Ext.isEmpty(existingCMP)) {
            if (activateOnAdd)
                tabPanel.setActiveTab(existingCMP);
            return;
        }
        //---

        //Set Common Properities to the new TAB
        cmp.closable = true;
        cmp.autoScroll = true;
        cmp.border = false;
        if (cmp.id == 'deadlinesPanel' || cmp.id == 'publicationsPanel')
            cmp.closable = false;
        //Add Listner
        cmp.addListener("destroy", function () {
            container.fireEvent("checkCount");
        });

        if (Ext.isEmpty(index)) {
            tabPanel.add(cmp);
        } else {
            //tabPanel.insert(index, cmp);
            var sortorder_flag = false;
            for (var i = 0; i < sort_order_array.length; i++) {
                if (sort_order_array[i] > index) {
                    sortorder_flag = true;
                    sort_order_array.splice(i, 0, index);// = index;
                    tabPanel.insert(i, cmp);
                    //else tabPanel.insert(i, cmp);
                    break;
                }
            }
            if (sortorder_flag == false)
                tabPanel.add(cmp);
        }
        if (activateOnAdd)
            cmp.show();

        tabPanel.doLayout();
        //Refresh ViewPort for affecting the changes as per the new component.
        var tabSize = Ext.getCmp(tabPanel.id);
        if (tabSize.lastSize) {
            var tbHt = tabSize.lastSize.height - 30;
            Ext.getCmp(cmp.id).setHeight(tbHt);

        }

        Ext.getCmp(cmp.id).doLayout();
    };

    /**
     * Render Module Menus to be displayed on the header for the
     * Logged in User.
     * 1. Ajax Request to get Menu Object
     * 2. Add Menu object to a panel
     * 3. Then Render the panel to the top menu container.
     * @return	void
     **/
    var renderModuleMenus = function () {
        Ext.Ajax.request({
            url: getModMenuURL,
            params: {
                showText: false
            },
            success: function (response) {
                var response = Ext.util.JSON.decode(response.responseText);
                var tmp = new Ext.Container({
                    autoWidth: true,
                    height: 70,
                    id: 'menu_bar',
                    layoutConfig: {
                        padding: '10px',
                        align: 'middle',
                        bodyStyle: 'background:none;'
                    },
                    defaults: {
                        margins: '5 5 0 0'
                    },
                    layout: 'hbox'
                });

                var menus = response.items;
                Ext.each(menus, function (conf) {
                    //console.log('conf',conf);
                    if (conf == "-")
                        return;
                    conf.scale = "medium";
                    conf.enableToggle = true;
                    conf.id = 'main_menu_btn_' + conf.MenuId;
                    conf.text = '<span class="menutooltip">' + conf.tooltip + '</span>';
                    //conf.cls = 'menutooltip';
                    conf.toggleGroup = 'mygroup';
                    var item = new Ext.Button(conf);
                    tmp.add(item);
                });
                tmp.render('top_module_container');
                tmp.doLayout();
                //console.log('Auditor ano ? ' + _SESSION.is_auditor);
                if (_SESSION.is_auditor > 0) {
                    Ext.getCmp('main_menu_btn_6').show();
                    Ext.getCmp('menu_bar').doLayout();
                } else {
                    Ext.getCmp('main_menu_btn_6').hide();
                    Ext.getCmp('menu_bar').doLayout();
                }
            }
        });
    };

    /**
     * Load Module Toolbar Items permitted for the logged in user
     * Logged in User.
     * 1. Parent Menu ID will be passed to fetch the Toolbar Items
     * 2. If no Items found / not permitted for the user, then hides toolbar
     * @param	{Object} Toolbar to be updated
     * @param 	{Integer} MenuId of the Parent Menu
     * @return	void
     **/
    //var toolbar = menu;
    var loadModToolbar = function (toolbar, parentMenuId) {
        Ext.Ajax.request({
            url: getModMenuURL,
            params: {
                ParentMenuId: parentMenuId
            },
            success: function (response) {
                var response = Ext.util.JSON.decode(response.responseText);
                if (response.items.length > 0) {
                    toolbar.show();
                    if (!Ext.isEmpty(response.items[0].menu)) {
                        var menus = response.items[0].menu.items;
                        Ext.each(menus, function (conf) {
                            if (Ext.type(conf) == 'object')
                                conf.scale = "medium";
                            toolbar.add(conf);
                            toolbar.add({xtype: 'tbseparator'});
                        });
                    }
                    toolbar.doLayout();
                }
            }
        });
    };

    var putSubMenu = function (menu, item) {
        var tb = new Ext.Toolbar({
            id: item + '-menu',
            hidden: true,
            items: menu.items
        });
        //console.log(tb);
        Ext.getCmp('submenus').add(tb);
    };


    var processEmailAction = function () {
        //alert("Call function");
        if (Ext.isEmpty(_SESSION.email_action))
            return;
        //alert("Call function");
        switch (_SESSION.email_action.TODO) {
            case 'JOB_STATUS_CHANGE':
                if (Ext.isEmpty(Application['Jobs'])) {
                    Ext.MessageBox.show({
                        title: 'Error while loading Email action!',
                        msg: 'User has no priviliage to Jobs Module or Jobs module is not loaded',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR,
                        width: 325
                    });
                }
                break;
        }
    };

    var userBranchStore = new Ext.data.JsonStore({
        fields: ['br_Name', 'br_ID'],
        url: '?module=ui&op=userBranchStore',
        autoLoad: false,
        root: 'data'
    });
    var selectBranchForm = function () {
        var defaultForm = new Ext.form.FormPanel({
            url: modURL + '&op=setSelectedBranch',
            width: 500,
            autoHeight: true,
            border: true,
            frame: true,
            labelWidth: 60,
            items: {
                xtype: 'combo',
                anchor: '98%',
                id: 'branch_id',
                hiddenName: 'branch_id',
                selectOnFocus: true,
                editable: false,
                allowBlank: false,
                mode: 'remote',
                emptyText: 'Select Branch',
                lazyRender: true,
                typeAhead: true,
                fieldLabel: 'Branch',
                store: userBranchStore,
                triggerAction: 'all',
                displayField: 'br_Name',
                valueField: 'br_ID'
            }
        });
        return defaultForm;
    };

    var addTab = function (cmp, container, activateOnAdd, index, SortOrder) {
        var tabPanel;
        if (!Ext.isEmpty(SortOrder))
            sort_order_array[sort_order_array.length] = SortOrder;
        if (Ext.isEmpty(container))
            container = mainPanel.layout.activeItem;
        else
            container = Ext.getCmp(container);

        activateOnAdd = (Ext.isEmpty(activateOnAdd)) ? true : activateOnAdd;

        if (container.getXType() != "tabpanel") {
            tabPanel = container.findByType('tabpanel')[0];
        } else {
            tabPanel = container;
        }
        if (Ext.isEmpty(tabPanel))
            return;

        //Since the TABPanel is hidden by default, Make it visible first
        tabPanel.show();
        //Check if the Tab is aleady Exists, just show and break the remaining execution
        var existingCMP = tabPanel.getComponent(cmp.id);
        if (!Ext.isEmpty(existingCMP)) {
            if (activateOnAdd)
                tabPanel.setActiveTab(existingCMP);
            return;
        }
        //---
        /*
         try {
         tabPanel.getItem(0).destroy();
         } catch (e) {
         }*/

        //Set Common Properities to the new TAB
        cmp.closable = true;
        cmp.autoScroll = false;
        cmp.border = false;
        cmp.setWidth(mainPanelWidth + 5);
        //Add Listner
        cmp.addListener("destroy", function () {
            container.fireEvent("checkCount");
        });

        if (Ext.isEmpty(index)) {
            tabPanel.add(cmp);
        } else {
            //tabPanel.insert(index, cmp);
            var sortorder_flag = false;
            for (var i = 0; i < sort_order_array.length; i++) {
                if (sort_order_array[i] > index) {
                    sortorder_flag = true;
                    sortorder_flag = true;
                    sort_order_array.splice(i, 0, index);// = index;
                    tabPanel.insert(i, cmp);
                    //else tabPanel.insert(i, cmp);
                    break;
                }
            }
            if (sortorder_flag == false)
                tabPanel.add(cmp);
        }
        if (activateOnAdd)
            cmp.show();

        tabPanel.doLayout();

        var tabSize = Ext.getCmp(cmp.id);
        if (tabSize.lastSize) {
            var tbHt = tabSize.lastSize.height - 30;
            Ext.getCmp(cmp.id).setHeight(tbHt);
        }

        Ext.getCmp(cmp.id).doLayout();


        //Refresh ViewPort for affecting the changes as per the new component.
        //   viewport.doLayout();
    };


    //-----------------------------------------------------------------------//
    ///////////////////////////////////////////////////////////////////////////


    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //-----------------------------Public Area-------------------------------//
    //-----------------------------------------------------------------------//
    //---------------------------Public Variables ---------------------------//
    //----------------------------Public Methods-----------------------------//
    //-----------------------------------------------------------------------//
    return {//returns an object=Application.UI with the following properties:
        activeTopMenu: false,
        init: function () {
            //Load Top Module Menus to be displayed for the logged in User 
            renderModuleMenus();
            Ext.QuickTips.init();

            // The default Provider implementation which saves state via cookies.
            //Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
            //tabPanel.hide();
            viewport = new Ext.Viewport({
                layout: 'border',
                id: 'viewport',
                autoShow: true,
                animCollapse: true,
                items: [header, mainPanel /*,statusBar*/],
                listeners: {
                    show: function () {
                        hideUILoading();
                    },
                    afterrender: function () {
                        if (_SESSION.IsSuperUser == 'Yes') {
                            setTimeout(function () {
                                //Application.Dashboard.CRMainPanel();
                                Application.Dashboard.init();
                            }, 50);
                        }
                    }
                }
            });


            viewport.doLayout();
            viewport.show();
            mainPanel.setHeight(mainPanelHeight);


            if (Application && Application.Finascop_checkSession) {
                Application.Finascop_checkSession.init();
            }
            //To show status bar Loading in ajax request and Ready in request complete
            Ext.Ajax.on('beforerequest', this.staus_busy, this);
            Ext.Ajax.on('requestcomplete', this.status_completed, this);

        }, //end of init method
        currentBranch: function () {
            var currentBranchForm = selectBranchForm();
            var hidden = arguments[0];
            var defaultWindow = Ext.getCmp('defaultWindowId');
            if (Ext.isEmpty(defaultWindow)) {
                defaultWindow = new Ext.Window({
                    closable: false,
                    id: 'defaultWindowId',
                    layout: 'fit',
                    width: 500,
                    title: 'Choose Branch',
                    autoHeight: true,
                    allowBlank: false,
                    resizable: false,
                    items: currentBranchForm,
                    modal: true,
                    constrain: true,
                    buttons: [{
                            text: 'OK',
                            icon: IMAGE_BASE_PATH + "/default/icons/chk_tick.png",
                            iconCls: 'my-icon1',
                            handler: function () {
                                if (currentBranchForm.getForm().isValid()) {
                                    currentBranchForm.getForm().submit({
                                        waitTitle: 'Please Wait!',
                                        waitMsg: 'Saving data...',
                                        success: function (uForm, res) {
                                            var result = Ext.decode(res.response.responseText);
                                            if (result && result.success && result.valid) {
                                                _SESSION.typdetsid = result.typdetsid;
                                                defaultWindow.close();
                                            }
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
                            }
                        }, {
                            text: 'Cancel',
                            icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                            iconCls: 'my-icon1',
                            hidden: hidden,
                            handler: function () {
                                defaultWindow.close();
                            }
                        }]

                });
            }
            defaultWindow.show();
            Ext.getCmp('branch_id').getStore().load();
        },
        switchMainView: function (cmp, button) {
            switchMainView(cmp, button);
        },
        addTab: function (cmp, container, activateOnAdd, index, sortOrder) {
            addTab(cmp, container, activateOnAdd, index, sortOrder);
        },
        addTab1: function (cmp, container, activateOnAdd, index, sortOrder) {
            addTab(cmp, container, activateOnAdd, index, sortOrder);
        },
        staus_busy: function () {
            status_bar.showBusy();
            //mainPanel.getEl().mask("Loading..");
        },
        set_status_text: function (text) {
            status_bar.showBusy(text);
        },
        set_text: function (text) {
            status_bar.setText(text);
        },
        status_completed: function (conn, response, options) {
            status_bar.setStatus({
                text: 'Ready ',
                iconCls: ' '
            });
            //mainPanel.getEl().unmask();
            //status_bar.clearStatus();
            if (response.responseText != "") {
                var tmp = Ext.decode(response.responseText);
                if (!Ext.isEmpty(tmp))
                    if (!Ext.isEmpty(tmp.session_expired)) {
                        window.location = "";
                    }
            }
        },
        update_header: function () {
            Ext.get('sess_fname').update(_SESSION.FirstName);
            Ext.get('sess_lname').update(_SESSION.LastName);
        },
        logMenuClicks: function () {
            if (!Ext.isEmpty(this.initialConfig.handler) && typeof (this.initialConfig.handler) == 'function') {
                // log this.MenuId to server 
                Ext.Ajax.request({
                    url: modURL + 'getMenuId',
                    method: 'POST',
                    params: {
                        'menuID': this.MenuId
                    },
                    success: function (resp) {
                        // var res = Ext.decode(resp.responseText);

                    }
                });
            }
            if (!Ext.isEmpty(this.initialConfig.extraHandler) && this.initialConfig.extraHandler === true) {

                if (Application.UI.activeTopMenu !== false) {
                    Ext.getCmp(Application.UI.activeTopMenu).hide();
                }
                var key = 'handler_' + this.MenuId + '-menu';
                Ext.getCmp(key).show();
                Application.UI.activeTopMenu = key;

            }
        },
        sysReqPopUp: function () {
            var sysReq;
            if (Ext.isEmpty(Ext.getCmp('minSysReqMsgWindow'))) {
                sysReq = new Ext.ux.window.MessageWindow({
                    hidden: true,
                    title: 'Minimum System Requirements',
                    autoDestroy: false, //default = true
                    autoHeight: true,
                    autoHide: false, //default = true
                    bodyStyle: 'text-align:left',
                    id: 'minSysReqMsgWindow',
                    name: 'minSysReqMsgWindow',
                    closable: true,
                    closeAction: 'destroy',
                    help: false, //no help tool
                    html: 'Operating System -  Windows XP or higher, Ubuntu Linux 10.04,  Mac OS 10.4.x or higher <br/> Browser -  Firefox 3.5+ ( Windows, Linux and Mac), Safari 4.0+ (Mac), Chrome 20+ (Windows and Linux)',
                    iconCls: 'icon_sysreq',
                    pinState: false, //render pinned
                    origin: {
                        offX: 10, //amount to offset horizontally (-20 by default)
                        offY: -30 //amount to offset vertically (-20 by default)
                    },
                    showFx: {
                        duration: 0.5, //defaults to 1 second
                        mode: 'standard', //null,'standard','custom',or default ghost
                        useProxy: false //default is false to hide window instead
                    },
                    width: 500 //optional (can also set minWidth which = 200 by default)
                });
                sysReq.show(Ext.getDoc());
            } else {
                sysReq = Ext.getCmp('minSysReqMsgWindow');
                sysReq.destroy();
            }
        }
    }//end of return
    //-----------------------------------------------------------------------//
    ///////////////////////////////////////////////////////////////////////////


    ///////////////////////////////////////////////////////////////////////////
    //-----------------------------------------------------------------------//
    //------------------------Event Definition Area--------------------------//
    //-----------------------------------------------------------------------//
    ///////////////////////////////////////////////////////////////////////////
}();
/* End of application. Note the parentheses (); this notation causes the anonymous
 function to execute immediately, dumping us right into the
 Private Area where we step through to load all Private Variables and
 take an inventory of all of the Private Functions, finally the return
 in the "Public Area" is executed in similar fashion but since we are
 "returning" we now see this "Public Area" outside of the module which
 gives us the ability to execute the line below which fires the
 initialization method in the Public Area.
 So, as soon as the anonymous function returns:
 1. we have an object containing Public  Properties and Public Methods
 2. we can address that returned object as Applicaiton.UI */
/* Since the above code has already executed, we are able to access any Public
 Properties (Public Variables and Public Methods), including the "init"
 method immediately.
 The following execution line executes the Application.init method after the
 document has been completely loaded. This line also sets the Application.init
 method scope to UI module, which means you can call Public Attributes (methods
 and properties) with a preceding 'this'; */
Ext.onReady(Application.UI.init, Application.UI, true);






