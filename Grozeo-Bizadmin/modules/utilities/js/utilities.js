/**
 * @author Aparna Raveendran <aparna@saturn.in>
 * The JS module to handle the Complete UIs for the Utilities screen
 * Module handles following Menus:
 * a) Find in Files
 * b) Deploying Release
 * c) Patch Update
 * d) Live Restore
 * e) Authorization Key
 **/

var Utilities = function () {

    //------------------------------------------------------------------------//
    //--------------------------Find in Files--------------------------------//
    //-----------------------------------------------------------------------//

    /**
     * Function for Find in Files
     * Contains Form with textfields and grid
     **/
    var excludeFieldNames = [];
    var fieldsTodeleteArray = [];

    var storeOrder;


    /*var orderStore_fn = function(excludeFieldNames) {
     
     var storeValues = '';
     storeValues += '[';
     var cnt = excludeFieldNames.length;
     for (var i = 0; i < cnt; i++) {
     storeValues += '["' + excludeFieldNames[i] + '"]';
     }
     storeValues += ']';*/

    storeOrder = new Ext.data.JsonStore({
        fields: ['columnField'],
        url: '?module=utilities&op=getexcludeFieldNames',
        root: 'data'

    });
    /*storeOrder.load();
     return storeOrder;
     };*/

    var GridOrderStore = function (excludeFieldNames) {
        var store = new Ext.data.JsonStore({
            fields: ['columnField']
        });
        return store;
    };

    this.utilityWindow = function () {

        /** 
         * var utilstore = Store for Gridpanel 'utilgrid'
         * utilData Contains data for 'utilgrid' Gridpanel 
         **/
        var utilstore = new Ext.data.JsonStore({
            fields: [{
                    name: 'Filename'
                }, {
                    name: 'Line'
                }, {
                    name: 'Content'
                }],
            url: '?module=utilities&op=utilData',
            root: 'data',
            remoteSort: true
        });



        /** 
         * var utilgrid = Grid Panel for Utilities/Find in Files
         * To display Filename,Line,Content
         **/
        var utilgrid = new Ext.grid.GridPanel({
            store: utilstore,
            id: 'idutil',
            name: 'UtilGrid',
            autoScroll: true,
            height: 300,
            viewConfig: {
                forceFit: true
            },
            columns: [{
                    header: 'Filename',
                    width: 200,
                    dataIndex: 'Filename',
                    tooltip: 'Filename',
                    sortable: true
                }, {
                    header: 'Line',
                    width: 40,
                    dataIndex: 'Line',
                    tooltip: 'Line',
                    sortable: true
                }, {
                    header: 'Content',
                    dataIndex: 'Content',
                    tooltip: 'Content',
                    sortable: true
                }],
            stripeRows: true,
            autoExpandColumn: true,
            listeners: {
                dblClick: function () {
                    var content = Ext.getCmp('idutil').selModel.getSelected().data.Content;
                    //Ext.getCmp('view_content').setValue(content);
                    viewFileContent(content);
                }
            }
        });
        //utilgrid.hide();
        //Ext.getCmp('idutil').store.load();
        /**
         *location store to load location combo
         *getLocation contains the data for locationstore
         **/
        var locationstore = new Ext.data.JsonStore({
            fields: ['name', 'value'],
            url: '?module=utilities&op=getLocation',
            autoLoad: true,
            root: 'data'
        });
        /**
         * branch store to load Branch combo
         * getBranch contains the data for branch store
         **/

        var branchstore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: '?module=utilities&op=getBranch',
            autoLoad: true,
            root: 'data'
        });


        /** 
         * var utility Form Panel for 'Find in Files' Menu
         **/
        var utility = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'id_utility',
            autoHeight: true,
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
                                                    xtype: 'combo',
                                                    id: 'fileType',
                                                    mode: 'local',
                                                    triggerAction: 'all',
                                                    typeAhead: true,
                                                    editable: false,
                                                    anchor: '97%',
                                                    store: locationstore,
                                                    tabIndex: 200,
                                                    emptyText: 'Select',
                                                    displayField: 'name',
                                                    valueField: 'value',
                                                    name: 'fileType',
                                                    fieldLabel: 'Location/Path'
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .5,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'combo',
                                                    typeAhead: true,
                                                    fieldLabel: 'Format',
                                                    id: 'format_date',
                                                    name: 'format_date',
                                                    tabIndex: 201,
                                                    mode: 'local',
                                                    //for getting date format
                                                    store: new Ext.data.SimpleStore({
                                                        fields: ['format', 'date_formt'],
                                                        data: [['Y', 'Year'], ['Y/m', 'Year/Month'], ['Y/m/d', 'Year/Month/Day']]
                                                    }),
                                                    triggerAction: 'all',
                                                    displayField: 'date_formt',
                                                    valueField: 'format',
                                                    hiddenName: 'format',
                                                    anchor: '97%',
                                                    listeners: {
                                                        select: function (cbo) {
                                                            //condition checking for enable branch          
                                                            Ext.getCmp('search_date').format = cbo.getValue();
                                                            if (cbo.getValue() == 'Y') {
                                                                Ext.getCmp('find_branch').enable(true);
                                                                Ext.getCmp('find_branch').allowBlank = false;
                                                            }
                                                            else
                                                                Ext.getCmp('find_branch').disable(true);
                                                        }
                                                    }
                                                }]
                                        }]
                                }]
                        }/* end of form 1 */, {
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .5,
                                            bodyStyle: 'padding-top:7px',
                                            items: [new Ext.form.DateField({
                                                    fieldLabel: 'Search Date',
                                                    name: 'search_date',
                                                    id: 'search_date',
                                                    format: 'Y/m/d',
                                                    //minValue: '01/01/80',
                                                    tabIndex: 202,
                                                    anchor: '97%',
                                                    listeners: {
                                                        afterrender: function (field, newValue, oldValue) {
                                                            this.setMaxValue(new Date().format(this.format));
                                                            //this.validate();

                                                        }
                                                    }
                                                })]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .5,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'combo',
                                                    id: 'find_branch',
                                                    mode: 'local',
                                                    triggerAction: 'all',
                                                    typeAhead: true,
                                                    editable: false,
                                                    disabled: true,
                                                    anchor: '97%',
                                                    store: branchstore,
                                                    tabIndex: 203,
                                                    //emptyText:      'Select',
                                                    displayField: 'br_Name',
                                                    valueField: 'br_ID',
                                                    name: 'find_branch',
                                                    fieldLabel: 'Branch'
                                                }]
                                        }]
                                }]
                        }/* end of form 1 */, {
                            layout: 'form', /* start of form 2 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: 1,
                                            labelWidth: 95,
                                            bodyStyle: 'padding-top:7px;padding-left:5px;',
                                            items: [{
                                                    xtype: 'textfield',
                                                    fieldLabel: 'File Pattern',
                                                    id: 'idpattern',
                                                    name: 'pattern',
                                                    tabIndex: 204,
                                                    anchor: '99%',
                                                    allowBlank: false/*,                         
                                                     
                                                     plugins:[Ext.ux.plugins.RemoteValidator],
                                                     rvOptions: {
                                                     url: '?module=utilities&op=checkData'
                                                     }*/
                                                }]
                                        }]
                                }]
                        }/* end of form 2 */, {
                            layout: 'form', /* start of form 3 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .85,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'textfield',
                                                    fieldLabel: 'Search Pattern',
                                                    id: 'idsearch',
                                                    name: 'search',
                                                    tabIndex: 205,
                                                    width: 380,
                                                    allowBlank: false
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .15,
                                            buttonAlign: 'left',
                                            items: [{
                                                    buttons: [{
                                                            text: 'Search',
                                                            id: 'id_search',
                                                            tabIndex: 206,
                                                            // disabled: true,
                                                            handler: function () {
                                                                /*  if((!Ext.isEmpty(Ext.getCmp('idloc').getValue())) && 
                                                                 (!Ext.isEmpty(Ext.getCmp('idpattern').getValue())) && 
                                                                 (!Ext.isEmpty(Ext.getCmp('idsearch').getValue()))){*/
                                                                var branchID;
                                                                //validating branch
                                                                if (Ext.getCmp('format_date').getValue() == 'y') {
                                                                    if (Ext.isEmpty(Ext.getCmp('find_branch').getValue())) {
                                                                        Ext.Msg.alert("Notification", "Please select Branch");
                                                                        branchID = '';
                                                                        return;
                                                                    }
                                                                    else {
                                                                        branchID = Ext.getCmp('find_branch').getValue();
                                                                    }
                                                                }
                                                                utilstore.removeAll();
                                                                utilstore.baseParams = {
                                                                    fileType: Ext.getCmp('fileType').getValue(),
                                                                    searchdate: Ext.getCmp('search_date').getRawValue(),
                                                                    filePattern: Ext.getCmp('idpattern').getValue(),
                                                                    searchPattern: Ext.getCmp('idsearch').getValue(),
                                                                    branch: branchID
                                                                }
                                                                utilgrid.show();
                                                                Ext.getCmp('idutil').store.load();

                                                                /* }
                                                                 else{
                                                                 alert('Please enter all search keywords/filenames!');                                                    
                                                                 }*/
                                                            }
                                                        }]
                                                }]
                                        }]
                                }]
                        }, /* end of form 3 */{
                            layout: 'form', /* start of form 4 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: 1,
                                            items: [utilgrid]
                                        }]
                                }]
                        }/* end of form 4 */]
                }]
        })


        /**
         *  var utility = Window for Find in Files Menu
         **/
        var utilities = new Ext.Window({
            layout: 'fit',
            width: 600,
            height: 400,
            autoHeight: true,
            //constrainHeader: true,
            constrain: true,
            title: 'Find in Files',
            //iconCls: 'icon_findafile',
            closable: true,
            modal: true,
            resizable: false,
            plain: true,
            border: false,
            items: [utility],
            //buttonAlign: 'center',
            buttons: [{
                    text: 'OK',
                    tabIndex: 207,
                    buttonAlign: 'right',
                    icon: IMAGE_BASE_PATH + "/default/icons/ok.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        utilities.close();
                    }
                }]
        });
        utilities.show();
        function viewFileContent(content) {
            if (Ext.isEmpty(Ext.getCmp('file_content_view')))
            {
                var fileContentDisplay = new Ext.Window({
                    layout: 'fit',
                    width: 600,
                    height: 300,
                    //autoHeight: true,
                    id: 'file_content_view',
                    //constrainHeader: true,
                    constrain: true,
                    title: 'View Content',
                    closable: true,
                    modal: true,
                    resizable: false,
                    plain: true,
                    border: false,
                    items: [new Ext.FormPanel({
                            frame: true,
                            monitorValid: true,
                            id: 'content_form',
                            height: 220,
                            items: [{
                                    xtype: 'textarea',
                                    id: 'view_content',
                                    hideLabel: true,
                                    readOnly: true,
                                    anchor: '99%',
                                    height: 190,
                                    style: 'border:0px;',
                                    name: 'view_content',
                                    value: content

                                }]

                        })],
                    buttonAlign: 'right',
                    buttons: [{
                            text: 'OK',
                            tabIndex: 207,
                            icon: IMAGE_BASE_PATH + "/default/icons/ok.png",
                            iconCls: 'my-icon1',
                            handler: function () {
                                fileContentDisplay.close();
                            }
                        }]
                });
            }
            fileContentDisplay.show();
            fileContentDisplay.doLayout();
            fileContentDisplay.center();


        }
    }


    //------------------------------------------------------------------------//
    //---------------------------Live Restore---------------------------------//
    //------------------------------------------------------------------------//

    /**
     * Function for Live Restore
     * Contains Grid 
     **/
    this.liverestore = function () {

        /** 
         * var createlive Form Panel for Live Restore Menu
         **/
        var createlive = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'id_create',
            items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Branch Name',
                    id: 'branchname',
                    disabled: true
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Live Restore Date',
                    id: 'livrestore',
                    disabled: true
                }]
        });

        /**
         *  var createrestore = Window for Row double click action of listrestoregrid grid
         **/
        var createrestore = new Ext.Window({
            layout: 'fit',
            width: 300,
            height: 150,
            constrainHeader: true,
            constrain: true,
            title: 'Create Live Restore',
            closable: true,
            modal: true,
            resizable: false,
            //closeAction: 'hide',
            plain: true,
            border: false,
            items: [createlive],
            buttonAlign: 'center',
            buttons: [{
                    text: 'Start',
                    handler: function () {
                        Ext.Ajax.request({
                            url: '?module=utilities&op=startRestore',
                            success: function () {
                                createrestore.close();
                                Ext.Msg.alert('Status', 'Initiated!');
                            }
                        });
                    }
                }]
        });

        /**
         * var recs_per_page {Integer}
         * Number of records to be shown in a page by default
         * Currently set to 12
         **/
        var recs_per_page = 12;

        /**
         * To perform pagination based on grid height and row height.
         * Returns the number of records allowed in a window
         * @param  {Object}   grid panel
         * @return {Integer}  Number of records per page
         **/
        function updatePagination(cmp) {
            recs_per_page = finascop_update_recs_per_page(cmp);
        }

        /** 
         * var livestore = Store for Utilities/Live Restore grid 
         * restoreData Contains data for Live Restore grid
         **/
        var livestore = new Ext.data.JsonStore({
            fields: [{
                    name: 'code'
                }, {
                    name: 'name'
                }, {
                    name: 'city'
                }, {
                    name: 'district'
                }, {
                    name: 'authorization'
                }, {
                    name: 'livrestore',
                    type: 'datetime',
                    dateFormat: 'd/m/Y'
                }],
            url: '?module=utilities&op=restoreData',
            root: 'data',
            totalProperty: 'count',
            autoLoad: {
                params: {
                    start: 0,
                    limit: recs_per_page
                }
            },
            remoteSort: true
        });

        /** 
         * var viewgrid = Grid Panel for Branch View
         * create a grid to display Code,Branch Name,City,District,Authorization,Live Restore
         **/
        var listrestoregrid = new Ext.grid.GridPanel({
            store: livestore,
            autoWidth: true,
            name: 'livegrid',
            id: 'restoregrid',
            columns: [{
                    header: 'Code',
                    sortable: true,
                    dataIndex: 'code'
                }, {
                    header: 'Branch Name',
                    sortable: true,
                    dataIndex: 'name'
                }, {
                    header: 'City',
                    sortable: true,
                    dataIndex: 'city'
                }, {
                    header: 'District',
                    sortable: true,
                    dataIndex: 'district'
                }, {
                    header: 'Authorization',
                    sortable: true,
                    dataIndex: 'authorization'
                }, {
                    header: 'Live Restore',
                    sortable: true,
                    dataIndex: 'livrestore'
                }, {
                    header: 'Progress',
                    align: 'center',
                    width: 100,
                    sortable: true
                }],
            title: 'Live Restore',
            autoExpandColumn: true,
            autoExpandMin: 100,
            autoExpandMax: 300,
            listeners: {
                viewready: updatePagination,
                dblclick: function () {
                    /**
                     * variable brname contains the value of the field BranchName and
                     * variable livrestore contains the value of the field
                     * Live Restore in the grid restoregrid
                     * Both values get populated to the window createrestore
                     * while double clicking the grid restoregrid
                     **/
                    var brname = Ext.getCmp('restoregrid').selModel.getSelected().data.name;
                    var livedate = Ext.getCmp('restoregrid').selModel.getSelected().data.livrestore;
                    createrestore.show();
                    Ext.getCmp('branchname').setValue(brname);
                    Ext.getCmp('livrestore').setValue(livedate);
                }
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: livestore,
                displayInfo: true,
                displayMsg: 'Displaying topics {0} - {1} of {2}',
                emptyMsg: "No topics to display"
            })
        });
        Application.UI.addTab(listrestoregrid);
    };



    //------------------------------------------------------------------------//
    //--------------------------Authorization Key-----------------------------//
    //-----------------------------------------------------------------------//

    /**
     * Function for Authorization Key    
     **/
    this.authorizationWindow = function () {

        /** 
         * var authForm Form Panel for 'Authorization Key' Menu
         **/
        var authForm = new Ext.form.FormPanel({
            frame: true,
            autoHeight: true,
            id: 'idauthForm',
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'form',
                            columnWidth: 1,
                            labelWidth: 30,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            xtype: 'textfield',
                                            hideLabel: true,
                                            id: 'auth_key',
                                            name: 'auth_key'
                                        }, {
                                            id: 'copyto',
                                            html: '<div id="d_clipb_container" style="position:relative;float:right"><div id="d_clipb_button" class="my_clip_button" align="center" ><input type="image" src="' + IMAGE_BASE_PATH + '/default/icons/clipboard_icon.jpg" title="Copy to Clipbord" style = " font: 11px tahoma,verdana,helvetica;cursor:pointer;background-color: #FCFCFC; border-color: #D0D0D0;hover:background-color: #D0D0D0;border-color: #FCFCFC"  /><span title="copy"></span> </div></div>',
                                            style: 'z-index:99;float:left;border 1px solid red;float:right'

                                        }]
                                }]
                        }]
                }]
        });

        /**
         *  var authWin = Window for Deploying Release Menu
         *
         **/
        var authWin = new Ext.Window({
            width: 230,
            height: 68,
            layout: 'fit',
            id: 'auth_win',
            closable: true,
            resizable: false,
            title: 'Authorization Key',
            //iconCls: 'icon_authorization_window',
            plain: true,
            constrain: true,
            modal: true,
            border: false,
            items: [authForm]
        });

        var t = new Date();
        var t_stamp = t.format("YmdHis");

        authForm.load({
            url: '?module=utilities',
            params: {
                op: 'getauthKey',
                apikey: _SESSION.apikey,
                tstamp: t_stamp
            },
            success: function (form, act) {
                var key = act.result.data.key;

                Ext.getCmp('auth_key').setValue(key);
                var clip = new ZeroClipboard.Client();
                clip.setHandCursor(true);
                clip.setText(Ext.getCmp('auth_key').getValue());
                clip.glue('d_clipb_button', 'd_clipb_button');
            },
            failure: function () {

            }
        });
        authWin.show();

    };

    ///////////////////////////////////////////////   
    this.casheWindow = function () {

        /** 
         *
         **/
        var cacheForm = new Ext.form.FormPanel({
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
                                                    id: "cachetime",
                                                    readOnly: true,
                                                    anchor: '93%'
                                                }]
                                        },
                                        {
                                            layout: 'form',
                                            columnWidth: .5,
                                            buttonAlign: 'left',
                                            items: [{
                                                    buttons: [{text: 'Clear',
                                                            id: 'id_clr',
                                                            handler: function () {
                                                                var t = new Date();
                                                                var t_stamp = t.format("YmdHis");
                                                                Ext.Ajax.request({
                                                                    url: '?module=utilities&op=resetsvrcache',
                                                                    params: {
                                                                        apikey: _SESSION.apikey,
                                                                        tstamp: t_stamp
                                                                    },
                                                                    success: function () {
                                                                        //console.log(act.response);
                                                                        cacheWin.close();
                                                                    }, failure: function (elm, res) {

                                                                        Ext.Msg.alert('Notification', res.result.error);
                                                                    }
                                                                });

                                                            }




                                                        }]
                                                }]
                                        }]
                                }]
                        }]
                }]
        });
        /**
         *  
         *
         **/
        var cacheWin = new Ext.Window({
            width: 300,
            height: 80,
            layout: 'fit',
            id: 'cache_win',
            closable: true,
            resizable: false,
            title: 'Server Cache',
            //iconCls: 'server_cache',
            plain: true,
            constrain: true,
            modal: true,
            border: false,
            items: [cacheForm]
        });

        var t = new Date();
        var t_stamp = t.format("YmdHis");
        cacheForm.load({
            url: '?module=utilities',
            params: {
                op: 'getsvrcache',
                apikey: _SESSION.apikey,
                tstamp: t_stamp
            },
            success: function (form, act) {
                var time = act.result.data.time;
                Ext.getCmp('cachetime').setValue(time);
            },
            failure: function () {

            }
        });
        cacheWin.show();

    };


/////////////////Initiate Live Restore//////////////////////////////   
    this.initiatelrsWindow = function () {

        var proxy = new Ext.data.HttpProxy({
            url: '?module=utilities',
            method: 'post'
        });


        // var branch_store = store for branch combo
        var branchstore = new Ext.data.JsonStore({
            fields: ['br_ID', 'br_Name'],
            url: '?module=utilities&op=getBranch',
            autoLoad: true,
            root: 'data'
        });
        var branch_store = new Ext.data.Store({
            proxy: proxy,
            baseParams: {
                op: 'getBranches'
            },
            reader: new Ext.data.ArrayReader({}, ['br_ID', 'br_Name'])
        });



        var recs_per_page = 10;
        /* history_store = store for history grid   */
        var cratehistorytore = function () {
            var history_store = new Ext.data.JsonStore({
                url: '?module=utilities&op=getHistory',
                fields: ['addon', 'added_by'],
                root: 'data',
                id: 'addon',
                totalProperty: 'totalcount',
                // turn on remote sorting
                remoteSort: true
            });
            return history_store;
        };

        /* For loading history grid   */
        var loadHistory = function () {
            Ext.getCmp('gridHistory').getStore().setDefaultSort('addon', 'asc');
            Ext.getCmp('gridHistory').getStore().removeAll();
            Ext.getCmp('gridHistory').getStore().load({
                params: {
                    start: 0,
                    limit: recs_per_page,
                    bid: Ext.getCmp('id_brid').getValue()
                }
            });
        };

        /* historyGrid = Create a grid to display Add On,Added By  */
        var createhistoryGrid = function () {
            var history_store = cratehistorytore();

            var historyGrid = new Ext.grid.GridPanel({
                store: history_store,
                layout: 'fit',
                forceFit: true,
                height: 150,
                // title: '',
                //iconCls: '',
                id: 'gridHistory',
                remoteSort: false,
                columns: [{
                        header: 'Add On',
                        dataIndex: 'addon',
                        id: 'addon',
                        sortable: false

                    }, {
                        header: 'Added By',
                        dataIndex: 'added_by',
                        sortable: false

                    }],
                viewConfig: {
                    forceFit: true
                },
                stripeRows: true


            });
            return historyGrid;
        };


        /*var lrsFrm = Create live restore form */
        /*
         var lrsFrm = new Ext.FormPanel ({
         frame : true,
         url:'?module=utilities&op=initiate',
         id: 'id_lrsform',
         border:false,
         autoHeight: true,
         labelAlign: 'left',
         
         items:[{
         layout: 'column',
         items:[{
         layout: 'form',  
         labelAlign: 'top',
         columnWidth: .5,
         labelWidth:60,
         //style: 'padding-right:2px;',
         items:[{
         
         xtype: 'combo',
         triggerAction: 'all',
         id: 'id_brid',
         anchor: '95%',
         labelWidth:10,
         name: 'branchid',
         hiddenName: 'branchid',
         forceSelection: true,
         editable: true,
         typeAhead: true,
         minChars: 0,
         displayField: 'br_Name',
         valueField: 'br_ID',
         // allowBlank: false,
         fieldLabel: 'Branch',
         store: branch_store,
         listeners:{
         select: function(cbo){
         if( !Ext.isEmpty(Ext.getCmp('id_brid').getValue()) ){
         //get vehicle details  
         Ext.Ajax.request({                              
         url:'?module=utilities&op=getentry',  
         params: {
         
         bid:Ext.getCmp('id_brid').getValue()
         },
         callback: function(options, success, response){
         if (success) {
         if (response && response.responseText) {
         var tm =    Ext.decode(response.responseText);
         
         var tmp = tm.data;
         // console.log(tmp);
         
         if(!Ext.isEmpty(tmp.entry)){
         // console.log('hi1');   
         Ext.getCmp('frstuploaddt').setValue(tmp.entry);   
         }                                              
         else{
         //  console.log('hi2');
         Ext.getCmp('frstuploaddt').setValue("-NA-");    
         } 
         
         }
         
         
         }
         else {
         //  Ext.Msg.alert("Notification",tmp.msg);
         // Ext.MessageBox.alert('Sorry, please try again. ');
         
         }
         
         }
         } 
         );
         }
         loadHistory();   
         }
         }
         
         
         } ]
         },{
         
         layout: 'form',
         columnWidth: .5,
         labelAlign: 'top',
         items: [{
         xtype:"textfield",
         fieldLabel:"First Upload Date",
         id: "frstuploaddt",
         readOnly: true,
         anchor : '93%',
         style: 'text-align: center'
         }                  
         ]
         }]
         },{
         layout: 'column',
         items:[{
         layout: 'form',  
         labelAlign: 'top',
         columnWidth: .5,
         labelWidth:60,
         //style: 'padding-right:2px;',
         items:[{
         xtype:"textarea",
         height: 130,
         fieldLabel:"Comments",
         id:"lrscomments",
         anchor: '95%',
         name:"lrscomments",
         maxLength: 500,
         maxLengthText: 'The maximum length for this field is 500'
         }]
         },{
         layout: 'form',                    
         columnWidth: .5,
         labelAlign: 'top',
         labelWidth:30,
         items:[createhistoryGrid()]
         }]
         },{
         layout: 'column',
         items:[{
         layout: 'form',  
         labelAlign: 'top',
         columnWidth: .3,
         labelWidth:60,
         //style: 'padding-right:2px;',
         items:[{
         xtype: 'checkbox',
         id: 'billable',
         name: 'billable',   
         //tabIndex: 1001,
         boxLabel: 'Billable',
         inputValue: 1,
         listeners: {
         check: function (checkbox, checked) {
         
         if(checked == false) {
         
         
         Ext.getCmp('lrscomments').allowBlank = false;
         // console.log("ding");
         
         }
         else {
         
         Ext.getCmp('lrscomments').allowBlank = true;     
         //  console.log("ding1");
         
         }
         }
         }   
         
         
         
         }]
         },{
         xtype:'hidden',
         id : 'brname',
         name : 'brname'
         }]
         }]
         });*/

        /*var lrsFrm = Create live restore form */

        var liverestorefrm = function () {
            var lrsFrm = new Ext.FormPanel({
                frame: true,
                url: '?module=utilities&op=initiate',
                id: 'id_lrsform',
                border: false,
                autoHeight: true,
                labelAlign: 'left',
                items: [{
                        layout: 'column',
                        items: [{
                                layout: 'form',
                                labelAlign: 'top',
                                columnWidth: 1,
                                labelWidth: 60,
                                //style: 'padding-right:2px;',
                                items: [{
                                        xtype: 'combo',
                                        triggerAction: 'all',
                                        id: 'id_brid',
                                        anchor: '95%',
                                        labelWidth: 10,
                                        name: 'branchid',
                                        hiddenName: 'branchid',
                                        forceSelection: true,
                                        editable: true,
                                        typeAhead: true,
                                        minChars: 0,
                                        displayField: 'br_Name',
                                        valueField: 'br_ID',
                                        // allowBlank: false,
                                        fieldLabel: 'Branch',
                                        store: branch_store,
                                        listeners: {
                                            select: function (cbo) {
                                                if (!Ext.isEmpty(Ext.getCmp('id_brid').getValue())) {
                                                }
                                                loadHistory();
                                            }

                                        }


                                    }, {
                                        items: [createhistoryGrid()]
                                    }, {
                                        xtype: 'hidden',
                                        id: 'brname',
                                        name: 'brname'
                                    }]
                            }]
                    }]
            });
            return lrsFrm;
        };



        /* For live restore window */
        var lrs_Frm = liverestorefrm();
        var liverestorewindow = new Ext.Window({
            layout: 'fit',
            // width: 800,
            width: 350,
            autoHeight: true,
            modal: true,
            //constrainHeader: true,
            constrain: true,
            title: 'Initiate Live Restore',
            // iconCls: '',
            closable: true,
            resizable: false,
            plain: true,
            border: false,
            items: [lrs_Frm],
            buttons: [{
                    text: 'Initiate',
                    id: 'initiate',
                    tabIndex: 984,
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        //  var check =Ext.getCmp('billable').getValue() ;  
                        var branchid;
                        var bname = Ext.getCmp('id_brid').getRawValue();
                        Ext.getCmp('brname').setValue(bname);
                        if (Ext.isEmpty(Ext.getCmp('id_brid').getValue()))
                        {
                            Ext.MessageBox.alert("Notification", "Please Select Branch");
                            branchid = '';
                            return;
                        }



                        // var comment=Ext.getCmp('lrscomments').getValue() ;
                        /*      if (!comment.trim()&& check==false) {
                         
                         Ext.MessageBox.alert("Notification", "Please Specify Reason for Deselect Billable");  
                         }*/

                        /*  else   {  */

                        /*  Submitting Form   */

                        lrs_Frm.getForm().submit({
                            waitMsg: 'Saving...',
                            waitTitle: 'Please Wait',
                            success: function (elm, msg) {
                                var res = Ext.decode(msg.response.responseText);
                                if (res && res.success) {
                                    Ext.Msg.alert('Notification', res.msg, function () {

                                        liverestorewindow.close();

                                    });
                                }
                            },
                            failure: function (elm, res) {

                                Ext.Msg.alert('Error', 'Unable to save', function () {

                                });
                            }
                        });

                        /*  }*/


                    }
                }, {
                    text: 'Cancel',
                    id: 'cancelBtn',
                    tabIndex: 985,
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    handler: function () {
                        liverestorewindow.close();
                    }
                }]
        });
        liverestorewindow.show();
        loadHistory();

    };
    /////////////////Report Switch Board START//////////////////////////////    

    this.ReportSwitchBoard = function () {

        var idRSBGrid = Ext.getCmp('idRSBGrid');
        if (Ext.isEmpty(idRSBGrid)) {
            idRSBGrid = createReportSwitchBoardGrid();
            idRSBGrid.getStore().load();
        }
        //loadMhire();
        Application.UI.addTab(idRSBGrid);
        idRSBGrid.doLayout();

    };

    var createReportSwitchBoardGrid = function () {
        var RSB_store = createStore();
        //var RSB_filters = createFilter();
        //var RSB_actions = createActions();
        var RSBGrid = new Ext.grid.GridPanel({
            title: 'Report Switch Borad',
            //iconCls: '',
            id: 'idRSBGrid',
            layout: 'fit',
            forceFit: true,
            store: RSB_store,
            mode: 'remote',
            //rptname, birtrptname,headers,proc_name,report_id
            columns: [{
                    header: 'Report Name',
                    dataIndex: 'rptname',
                    id: 'rptname',
                    sortable: true
                }, {
                    header: 'Birt Name',
                    dataIndex: 'birtrptname',
                    id: 'birtrptname',
                    sortable: true
                }, {
                    header: 'Headers',
                    dataIndex: 'headers',
                    id: 'headers',
                    sortable: true
                }, {
                    header: 'Stored Procedure Name',
                    dataIndex: 'proc_name',
                    id: 'proc_name',
                    sortable: true
                }, {
                    header: 'Report Id',
                    dataIndex: 'report_id',
                    hidden: true,
                    id: 'report_id',
                    sortable: true
                }, {
                    header: "Actions",
                    xtype: 'actioncolumn',
                    width: 75,
                    items: [
                        {
                            tooltip: 'Edit',
                            icon: IMAGE_BASE_PATH + "/default/icons/edit_rate_settings.png",
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var report_id = record.data.report_id;
                                Ext.Ajax.request({
                                    url: '?module=utilities&op=getEditDetails',
                                    waitMsg: 'Wait...',
                                    params: {
                                        'report_id': report_id
                                    },
                                    success: function (response, options) {
                                        var tmp = Ext.decode(response.responseText);
                                        if (tmp.success == true) {
                                            showWindowForEdit(tmp.data);
                                        }
                                    }
                                });
                            }
                        }
                    ]

                }],
            viewConfig: {
                forceFit: true
            },
            tbar: [{
                    text: 'Add New',
                    id: 'rsb_add_button',
                    tooltip: 'Add New Report Settings',
                    icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                    iconCls: 'my-icon1',
                    handler: function () {
                        Ext.Ajax.request({
                            url: '?module=utilities&op=getMaxRSBId',
                            method: 'POST',
                            success: function (response, options) {
                                var tmp = Ext.decode(response.responseText);
                                if (tmp.success === true) {
                                    var newRptId = tmp.data;
                                    createRSBPopup(newRptId, 0);
                                }
                            }
                        });
                        //Application.Markethire.addMarketHire(0);
                    }
                }]
        });
        return RSBGrid;

    };

    var createStore = function () {
        var RSBGrid_store = new Ext.data.JsonStore({
            fields: ['rptname', 'birtrptname', 'headers', 'proc_name', 'report_id'],
            url: '?module=utilities&op=loadRPTList',
            root: 'data'
        });
        return RSBGrid_store;
    };
    var createRSBPopup = function (newRptId, IsEdit) {
        var window_title;
        if (IsEdit == 1)
            window_title = 'Edit Report Settings';
        else
            window_title = 'Add New Report Settings';
        var window_id = "idRSBWin";
        var RSBPopupItems = createRSBPopupItems(newRptId);
        var cwin = new Ext.Window({
            id: window_id,
            title: window_title,
            layout: 'fit',
            width: 800,
            height: 500,
            plain: true,
            constrainHeader: true,
            modal: true,
            frame: true,
            resizable: false,
            items: [RSBPopupItems],
            buttons: [{
                    text: 'Save',
                    handler: function () {
                        var id_SPName = Ext.getCmp('id_SPName').getValue();
                        var hdnIdNoFields = Ext.getCmp('hdnIdNoFields').getValue();
                        var hdnIdFieldOrder = Ext.getCmp('hdnIdFieldOrder').getValue();
                        var id_txt_ColumnNames = Ext.getCmp('id_txt_ColumnNames').getValue();
                        var id_BIRTName = Ext.getCmp('id_BIRTName').getValue();
                        var id_RPTName = Ext.getCmp('id_RPTName').getValue();
                        if (id_SPName == '')
                            alert('Please enter stored procedure name');
                        else if (hdnIdNoFields == '')
                            alert('Please set no: of fields');
                        else if (hdnIdFieldOrder == '')
                            alert('Please set the order');
                        else if (id_txt_ColumnNames == '')
                            alert('Please execute the stored procedure');
                        else if (id_BIRTName == '')
                            alert('Please enter BIRT name');
                        else if (id_RPTName == '')
                            alert('Please enter report name');
                        else
                        {
                            RSBPopupItems.getForm().submit({
                                waitMsg: 'Saving...',
                                waitTitle: 'Please Wait',
                                //var tmp = Ext.decode(res.responseText);
                                //success: function(res) {
                                success: function (elm, msg) {
                                    var res = Ext.decode(msg.response.responseText);
                                    if (res.success == true) {
                                        Ext.Msg.alert('Status', res.mesg, function () {
                                            cwin.close();
                                            Ext.getCmp('idRSBGrid').getStore().load();
                                        });
                                    }
                                    else {
                                        Ext.Msg.alert('Error', res.mesg, function () {

                                        });
                                    }
                                },
                                failure: function (elm, msg) {
                                    var res = Ext.decode(msg.response.responseText);

                                    Ext.Msg.alert('Notification', res.mesg);
                                }
                            });
                        }

                    }
                }, {
                    text: 'Cancel',
                    handler: function () {

                        cwin.close();
                    }
                }]
        });
        cwin.doLayout();
        cwin.show(this);
        cwin.center();
    };
    var createRSBPopupItems = function (newRptId) {
        var RSBPopupItems = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'id_frmRSBPopupItems',
            url: '?module=utilities&op=saveRPTSettings',
            items: [{
                    layout: 'column',
                    items: [{
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            items: [{
                                                    xtype: 'hidden',
                                                    name: 'hdnIdnewRpt',
                                                    id: 'hdnIdnewRpts',
                                                    value: newRptId
                                                }, {
                                                    xtype: 'hidden',
                                                    name: 'IsEdit',
                                                    id: 'IsEdit',
                                                    value: 0
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .26,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'label',
                                                    html: '<b>Step 1 :</b> Enter Stored Procedure Name',
                                                    name: 'lbl_sp_name',
                                                    id: 'lbl_sp_name',
                                                    anchor: '40%'
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .6,
                                            lableWidth: 0,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'textfield',
                                                    vtype: 'SPStringspec',
                                                    fieldLable: '',
                                                    id: 'id_SPName',
                                                    anchor: '97%',
                                                    tabIndex: 200,
                                                    allowBlank: false,
                                                    minLength: 5,
                                                    enableKeyEvents: true,
                                                    name: 'id_SPName',
                                                    listeners: {
                                                        keyup: function (field) {
                                                            if (field.getValue().length >= 5) {
                                                                Ext.getCmp('id_btnSetFields').enable();
                                                            }
                                                            else {
                                                                Ext.getCmp('id_btnSetFields').disable();
                                                            }
                                                        }

                                                    }
                                                }]
                                        }]
                                }]
                        }/* end of form 1 */, {
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .4,
                                            bodyStyle: 'padding-top:7px',
                                            labelWidth: 250,
                                            items: [{
                                                    xtype: 'label',
                                                    html: '<b>Step 2 :</b> Please set no: of fields',
                                                    name: 'lbl_no_fields',
                                                    id: 'lbl_no_fields',
                                                    tabIndex: 202,
                                                    anchor: '57%'
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .6,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    layout: 'column',
                                                    items: [{
                                                            layout: 'form',
                                                            columnWidth: .2,
                                                            items: [{
                                                                    xtype: 'button',
                                                                    text: 'Set',
                                                                    id: 'id_btnSetFields',
                                                                    disabled: true,
                                                                    anchor: '60%',
                                                                    tabIndex: 201,
                                                                    name: 'id_btnSetFields',
                                                                    handler: function () {
                                                                        createNoFiledsPopup(newRptId);
                                                                    }
                                                                }]

                                                        },
                                                        {
                                                            layout: 'form',
                                                            columnWidth: .2,
                                                            items: [{
                                                                    xtype: 'button',
                                                                    text: 'Reset',
                                                                    id: 'id_btnResetFields',
                                                                    disabled: true,
                                                                    anchor: '60%',
                                                                    tabIndex: 201,
                                                                    name: 'id_btnResetFields',
                                                                    handler: function () {
                                                                        excludeFieldNames = [];
                                                                        //Ext.getCmp('hdnIdNoFields').setValue('');
                                                                        Ext.getCmp('id_btnResetFields').disable();
                                                                        Ext.getCmp('id_txt_ColumnNames').setValue('');
                                                                        Ext.getCmp('hdnIdFieldTotal').setValue('');
                                                                        Ext.getCmp('id_btnResetTotal').disable();
                                                                        Ext.getCmp('id_btnSetTotal').disable();
                                                                        Ext.getCmp('id_btnExeSP').disable();
                                                                        Ext.getCmp('id_btnResetOrder').disable();
                                                                        Ext.getCmp('id_btnSetOrder').disable();
                                                                        Ext.getCmp('hdnIdFieldOrder').setValue('');
                                                                        Ext.getCmp('hdnIdNoFields').setValue('');
                                                                        Ext.getCmp('hdnIdFieldOrderStore').setValue('');
                                                                        Ext.getCmp('id_btnSetFields').enable();


                                                                    }
                                                                }]
                                                        }
                                                    ]
                                                }
                                            ]
                                        }, {
                                            layout: 'form',
                                            items: [{
                                                    xtype: 'hidden',
                                                    name: 'hdnIdNoFields',
                                                    id: 'hdnIdNoFields'
                                                }]
                                        }]
                                }]
                        }, {
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .4,
                                            bodyStyle: 'padding-top:7px',
                                            labelWidth: 250,
                                            items: [{
                                                    xtype: 'label',
                                                    html: '<b>Step 3 :</b> Please select the order ',
                                                    name: 'lbl_order',
                                                    id: 'lbl_order',
                                                    tabIndex: 202,
                                                    anchor: '57%'
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .6,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    layout: 'column',
                                                    items: [{
                                                            layout: 'form',
                                                            columnWidth: .2,
                                                            items: [{
                                                                    xtype: 'button',
                                                                    text: 'Set',
                                                                    id: 'id_btnSetOrder',
                                                                    disabled: true,
                                                                    anchor: '60%',
                                                                    tabIndex: 201,
                                                                    name: 'id_btnSetOrder',
                                                                    handler: function () {
                                                                        createFiledOrderPopup(excludeFieldNames);
                                                                    }
                                                                }]

                                                        },
                                                        {
                                                            layout: 'form',
                                                            columnWidth: .2,
                                                            items: [{
                                                                    xtype: 'button',
                                                                    text: 'Reset',
                                                                    id: 'id_btnResetOrder',
                                                                    disabled: true,
                                                                    anchor: '60%',
                                                                    tabIndex: 201,
                                                                    name: 'id_btnResetOrder',
                                                                    handler: function () {

                                                                        excludeFieldNames = Ext.getCmp('hdnIdNoFields').getValue();
                                                                        //alert(excludeFieldNames);
                                                                        storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
                                                                        Ext.getCmp('hdnIdFieldOrderStore').setValue('');
                                                                        Ext.getCmp('hdnIdFieldOrder').setValue('');
                                                                        Ext.getCmp('id_txt_ColumnNames').setValue('');
                                                                        Ext.getCmp('id_btnExeSP').disable();
                                                                        Ext.getCmp('id_btnResetOrder').disable();
                                                                        Ext.getCmp('id_btnSetOrder').enable();
                                                                        Ext.getCmp('id_btnSetTotal').disable();
                                                                    }
                                                                }]
                                                        }
                                                    ]
                                                }
                                            ]
                                        },
                                        {
                                            layout: 'form',
                                            items: [{
                                                    xtype: 'hidden',
                                                    name: 'hdnIdFieldOrder',
                                                    id: 'hdnIdFieldOrder'
                                                }, {
                                                    xtype: 'hidden',
                                                    name: 'hdnIdFieldOrderStore',
                                                    id: 'hdnIdFieldOrderStore'
                                                }]
                                        }]
                                }]
                        },
                        {
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .4,
                                            bodyStyle: 'padding-top:7px',
                                            labelWidth: 250,
                                            items: [{
                                                    xtype: 'label',
                                                    html: '<b>Step 4 :</b> Execute stored procedure ',
                                                    name: 'lbl_order',
                                                    id: 'lbl_order',
                                                    tabIndex: 202,
                                                    anchor: '57%'
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .6,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    layout: 'column',
                                                    items: [{
                                                            layout: 'form',
                                                            columnWidth: .2,
                                                            items: [{
                                                                    xtype: 'button',
                                                                    text: 'Execute',
                                                                    id: 'id_btnExeSP',
                                                                    disabled: true,
                                                                    anchor: '60%',
                                                                    tabIndex: 201,
                                                                    name: 'id_btnExeSP',
                                                                    handler: function () {
                                                                        Ext.Ajax.request({
                                                                            url: '?module=utilities&op=executeSP&SPName=' + Ext.getCmp('id_SPName').getValue(),
                                                                            params: {
                                                                                method: 'post',
                                                                                columns: Ext.getCmp('hdnIdFieldOrderStore').getValue()
                                                                            },
                                                                            success: function (res) {
                                                                                var tmp = Ext.decode(res.responseText);
                                                                                if (tmp.success == true && tmp.data != '') {
                                                                                    //alert(tmp.data);
                                                                                    Ext.getCmp('id_txt_ColumnNames').setValue(tmp.data);
                                                                                    Ext.getCmp('hdnIdColumnNames').setValue(Ext.encode(tmp.data));
                                                                                    Ext.getCmp('id_btnSetTotal').enable();
                                                                                    Ext.getCmp('id_btnResetTotal').disable();
                                                                                    Ext.getCmp('hdnIdFieldTotal').setValue('');
                                                                                }
                                                                                else {
                                                                                    Ext.Msg.alert('Error', 'Invalid stored procedure or parameters.', function () {

                                                                                    });
                                                                                }
                                                                            }
                                                                        });
                                                                    }
                                                                }]

                                                        }
                                                    ]
                                                }
                                            ]
                                        }, {
                                            layout: 'form',
                                            items: [{
                                                    xtype: 'hidden',
                                                    name: 'hdnIdSPParms',
                                                    id: 'hdnIdSPParms'
                                                }]
                                        }]
                                }]
                        },
                        {
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .85,
                                            labelWidth: 262,
                                            bodyStyle: 'padding-top:7px;padding-left:42px',
                                            items: [{
                                                    xtype: 'textfield',
                                                    //vtype: 'SPStringspec',
                                                    fieldLabel: 'Enter column names',
                                                    id: 'id_txt_ColumnNames',
                                                    anchor: '100%',
                                                    tabIndex: 200,
                                                    allowBlank: false,
                                                    name: 'id_txt_ColumnNames'
                                                }, {
                                                    xtype: 'hidden',
                                                    //vtype: 'SPStringspec',
                                                    fieldLabel: 'Enter column names',
                                                    id: 'hdnIdColumnNames',
                                                    anchor: '100%',
                                                    tabIndex: 200,
                                                    allowBlank: false,
                                                    name: 'hdnIdColumnNames'
                                                }]
                                        }]
                                }]
                        }, {
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .4,
                                            bodyStyle: 'padding-top:7px',
                                            labelWidth: 250,
                                            items: [{
                                                    xtype: 'label',
                                                    html: '<b>Step 5 :</b> Please select the total ',
                                                    name: 'lbl_total',
                                                    id: 'lbl_total',
                                                    tabIndex: 202,
                                                    anchor: '57%'
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .6,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    layout: 'column',
                                                    items: [{
                                                            layout: 'form',
                                                            columnWidth: .2,
                                                            items: [{
                                                                    xtype: 'button',
                                                                    text: 'Set',
                                                                    id: 'id_btnSetTotal',
                                                                    disabled: true,
                                                                    anchor: '60%',
                                                                    tabIndex: 201,
                                                                    name: 'id_btnSetTotal',
                                                                    handler: function () {
                                                                        createFiledTotalPopup(Ext.getCmp('id_txt_ColumnNames').getValue());

                                                                    }
                                                                }]

                                                        },
                                                        {
                                                            layout: 'form',
                                                            columnWidth: .2,
                                                            items: [{
                                                                    xtype: 'button',
                                                                    text: 'Reset',
                                                                    id: 'id_btnResetTotal',
                                                                    disabled: true,
                                                                    anchor: '60%',
                                                                    tabIndex: 201,
                                                                    name: 'id_btnResetTotal',
                                                                    handler: function () {
                                                                        Ext.getCmp('hdnIdFieldTotal').setValue('');
                                                                        Ext.getCmp('id_btnResetTotal').disable();
                                                                        Ext.getCmp('id_btnSetTotal').enable();
                                                                        /*excludeFieldNames = Ext.getCmp('hdnIdNoFields').getValue();
                                                                         
                                                                         storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
                                                                         Ext.getCmp('hdnIdFieldOrderStore').setValue('');
                                                                         Ext.getCmp('hdnIdFieldOrder').setValue('');
                                                                         Ext.getCmp('id_btnExeSP').disable();
                                                                         Ext.getCmp('id_btnResetOrder').disable();
                                                                         Ext.getCmp('id_btnSetOrder').enable();*/

                                                                    }
                                                                }]
                                                        }
                                                    ]
                                                }
                                            ]
                                        },
                                        {
                                            layout: 'form',
                                            items: [{
                                                    xtype: 'hidden',
                                                    name: 'hdnIdFieldTotal',
                                                    id: 'hdnIdFieldTotal'
                                                }]
                                        }]
                                }]
                        }, {
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .26,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'label',
                                                    html: '<b>Step 6 :</b> Enter BIRT Report Name',
                                                    name: 'lbl_birt_rpt_name',
                                                    id: 'lbl_birt_rpt_name',
                                                    anchor: '40%'
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .6,
                                            lableWidth: 0,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'textfield',
                                                    //vtype: 'SPStringspec',
                                                    fieldLable: '',
                                                    id: 'id_BIRTName',
                                                    anchor: '97%',
                                                    tabIndex: 200,
                                                    allowBlank: false,
                                                    minLength: 5,
                                                    enableKeyEvents: true,
                                                    name: 'id_BIRTName'
                                                }]
                                        }]
                                }]
                        }, {
                            layout: 'form', /* start of form1 */
                            columnWidth: 1,
                            items: [{
                                    layout: 'column',
                                    items: [{
                                            layout: 'form',
                                            columnWidth: .26,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'label',
                                                    html: '<b>Step 7 :</b> Enter Report Name',
                                                    name: 'lbl_birt_rpt_name',
                                                    id: 'lbl_birt_rpt_name',
                                                    anchor: '40%'
                                                }]
                                        }, {
                                            layout: 'form',
                                            columnWidth: .6,
                                            lableWidth: 0,
                                            bodyStyle: 'padding-top:7px',
                                            items: [{
                                                    xtype: 'textfield',
                                                    //vtype: 'SPStringspec',
                                                    fieldLable: '',
                                                    id: 'id_RPTName',
                                                    anchor: '97%',
                                                    tabIndex: 200,
                                                    allowBlank: false,
                                                    minLength: 5,
                                                    enableKeyEvents: true,
                                                    name: 'id_RPTName'
                                                }]
                                        }]
                                }]
                        }]
                }]
        });
        return RSBPopupItems;
    };

    var createNoFiledsPopup = function (newRptId) {
        var window_title = 'Set No: of Fields';
        var window_id = "idNoFieldsWin";
        var NoFieldsPopupItems = createNoFieldsPopupItems(newRptId);
        var cwin = new Ext.Window({
            id: window_id,
            title: window_title,
            layout: 'fit',
            width: 440,
            height: 180,
            plain: true,
            constrainHeader: true,
            modal: true,
            frame: true,
            resizable: false,
            items: [NoFieldsPopupItems],
            buttons: [{
                    text: 'Save',
                    handler: function () {
                        createExcludeFilenames(newRptId);
                        cwin.close();
                    }
                }, {
                    text: 'Cancel',
                    handler: function () {

                        cwin.close();
                    }
                }]
        });
        cwin.doLayout();
        cwin.show(this);
        cwin.center();
    };
    function createExcludeFilenames(newRptId) {

        var pushValue = '';
        var no_combofield = Ext.getCmp('id_txt_combo').getValue();
        var no_dtpkerfield = Ext.getCmp('id_txt_dttime').getValue();
        var no_chkboxfield = Ext.getCmp('id_txt_chkbox').getValue();
        var isCompanyCombo = Ext.getCmp('id_chkbox_company').checked;
        if (no_combofield !== '' || no_combofield !== 0)
            for (var i = 1; i <= no_combofield; i++) {
                pushValue = 'Combo' + i + '_' + newRptId;
                excludeFieldNames.push(pushValue);
            }
        if (no_dtpkerfield !== '' || no_dtpkerfield !== 0)
            for (var i = 1; i <= no_dtpkerfield; i++) {
                pushValue = 'dtPick' + i + '_' + newRptId;
                excludeFieldNames.push(pushValue);
            }
        if (no_chkboxfield !== '' || no_chkboxfield !== 0)
            for (var i = 1; i <= no_chkboxfield; i++) {
                pushValue = 'CheckBox' + i + '_' + newRptId;
                excludeFieldNames.push(pushValue);
            }
        if (isCompanyCombo == true)
        {
            pushValue = 'cmbCompany_' + newRptId;
            excludeFieldNames.push(pushValue);
        }
        //alert(excludeFieldNames);
        var index;
        var fieldsTodelete = Ext.getCmp('hdnIdFieldOrder').getValue();
        if (fieldsTodelete != '')
        {
            fieldsTodeleteArray = fieldsTodelete.split("|");
            for (var flag = 0; flag < fieldsTodeleteArray.length; flag++) {
                index = excludeFieldNames.indexOf(fieldsTodeleteArray[flag]);

                if (index > -1)
                    excludeFieldNames.splice(index, 1);
            }
        }
        //storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
        //storeOrder.baseParams.excludeFieldNames = excludeFieldNames;
        //storeOrder.load();
        Ext.getCmp('id_btnSetFields').disable();
        Ext.getCmp('id_btnSetOrder').enable();
        Ext.getCmp('id_btnResetFields').enable();
        Ext.getCmp('hdnIdNoFields').setValue(excludeFieldNames);
    }
    var createNoFieldsPopupItems = function (newRptId) {
        var NoFieldsPopupItems = new Ext.FormPanel({
            frame: true,
            monitorValid: true,
            id: 'id_frmNoFieldsPopupItems',
            items: [
                {
                    xtype: 'numberfield',
                    id: 'id_txt_combo',
                    labelStyle: 'width:240px',
                    fieldLabel: 'No: of combo box fields',
                    name: 'id_txt_combo'


                }, {
                    xtype: 'numberfield',
                    id: 'id_txt_dttime',
                    labelStyle: 'width:240px',
                    fieldLabel: 'No: of date time picker fields',
                    name: 'id_txt_dttime'


                }, {
                    xtype: 'numberfield',
                    id: 'id_txt_chkbox',
                    labelStyle: 'width:240px',
                    fieldLabel: 'No: of check box fields',
                    name: 'id_txt_chkbox'
                }, {
                    xtype: 'checkbox',
                    id: 'id_chkbox_company',
                    labelStyle: 'width:240px',
                    fieldLabel: 'Whether exist company combo box ? If yes,',
                    name: 'id_chkbox_company'


                }]
        });
        return NoFieldsPopupItems;
    };
    var createFiledOrderPopup = function (newRptId, excludeFieldName) {
        //alert(excludeAccHeadIds);
        //var grid_store = GrpBy_store(report_id);
        //var getOrderStore_fn = orderStore_fn(report_id);
        var grid_store = GridOrderStore(excludeFieldNames);
        //var getOrderStore_fn = orderStore_fn(excludeFieldNames);
        if (Ext.isEmpty(FieldOrder_win)) {
            var FieldOrder_win = new Ext.Window({
                id: 'idFiledOrderWin',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                hideHeaders: true,
                width: 500,
                title: 'Set Field Orders',
                autoHeight: true,
                sm: select,
                items: [{
                        xtype: 'hidden',
                        id: 'hdnIdSelectedField'
                                // sortable: true,
                                //: 'pkid2'

                    }, new Ext.grid.EditorGridPanel({
                        title: '',
                        anchor: '99%',
                        height: 500,
                        forceFit: true,
                        id: 'idFiledOrderGrid',
                        store: grid_store,
                        tbar: [{
                                text: 'Add',
                                iconCls: 'my-icon1',
                                icon: IMAGE_BASE_PATH + "/default/icons/add.png",
                                handler: function () {

                                    //editor.stopEditing();
                                    if (excludeFieldNames != '')
                                        addFunction();
                                    //storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
                                }
                            }],
                        columns: [{
                                header: 'Field Name',
                                sortable: false,
                                groupable: true,
                                dataIndex: 'columnField',
                                id: 'columnField',
                                editor: {
                                    xtype: 'combo',
                                    mode: 'local',
                                    triggerAction: 'all',
                                    listClass: 'x-combo-list-small',
                                    lazyRender: true,
                                    name: 'columnField',
                                    displayField: 'columnField',
                                    valueField: 'columnField',
                                    hiddenName: 'columnField',
                                    forceSelection: true,
                                    allowBlank: false,
                                    autoLoad: false,
                                    id: 'idCmb',
                                    store: storeOrder,
                                    listeners: {
                                        select: function (selModel, record, index, options) {
                                            Ext.getCmp('hdnIdSelectedField').setValue(this.getValue());
                                            //Ext.getCmp('idCmbHead').getEl().dom.setAttribute('readOnly', true);


                                        },
                                        blur: function () {

                                            //console.log(rowIndex);
                                            //grid.store.removeAt(index);
                                            //grid.getView().refresh();
                                            var index = excludeFieldNames.indexOf(Ext.getCmp('hdnIdSelectedField').getValue());

                                            if (index > -1)
                                                excludeFieldNames.splice(index, 1);


                                            //st.baseParams.excludeIds = Ext.encode(excludeAccHeadIds);
                                            storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
                                            //storeOrder.baseParams.excludeFieldNames = excludeFieldNames;
                                            //storeOrder.load();
                                            Ext.getCmp('hdnIdSelectedField').setValue('');
                                            //orderStore_fn(excludeFieldNames);

                                            //Ext.getCmp('idCmb').store.removeAt(index);
                                            //Ext.getCmp('idCmb').getView().refresh();

                                            //excludeAccHeadIds.splice(excludeAccHeadIds.indexOf(record.data.colHead), 1);.push(this.getValue());
                                        },
                                        beforeselect: function (cmo) {

                                        }
                                    }
                                }
                            }, {
                                header: "Actions",
                                xtype: 'actioncolumn',
                                width: 75,
                                items: [
                                    {
                                        tooltip: 'Delete',
                                        iconCls: 'my-icon122 ',
                                        icon: IMAGE_BASE_PATH + "/default/icons/disable_settings.png",
                                        handler: function (grid, rowIndex, colIndex) {
                                            var record = grid.store.getAt(rowIndex);
                                            //console.log(rowIndex);

                                            excludeFieldNames.push(record.data.columnField);
                                            storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
                                            grid.store.removeAt(rowIndex);
                                            grid.getView().refresh();
                                        }
                                    }
                                ]
                            }],
                        listeners: {
                            beforeedit: function (e) {
                                if (!Ext.isEmpty(e.record.get('columnField')))
                                    return false;
                            }
                        },
                        stripeRows: true,
                        autoExpandColumn: 'columnField'
                    })],
                buttons: [{
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            //encode grid data and set in an hidden field in settings window

                            var grid_Data;
                            var grid = Ext.getCmp('idFiledOrderGrid');
                            var store = grid.getStore();
                            var count = store.getCount();
                            if (count > 0) {
                                var selected_value = [];
                                var FieldOrder_data = '';
                                for (var i = 0; i < count; i++) {
                                    var rec = store.getAt(i);
                                    selected_value[i] = rec.data;
                                    FieldOrder_data = FieldOrder_data + Ext.encode(rec.data) + '|';
                                    //alert(GrpBy_data);
                                }
                                grid_Data = Ext.encode(selected_value);
                                Ext.getCmp('hdnIdFieldOrder').setValue(FieldOrder_data);
                                Ext.getCmp('hdnIdFieldOrderStore').setValue(grid_Data);
                                //grid_Data = Ext.encode(selected_value);
                                //GrpBy_data = Ext.encode(GrpBy_data);
                                //alert(GrpBy_data);

                                //Ext.getCmp('GrpBy_store_data').setValue(grid_Data);
                                //Application.Reports.tmp_GrpBy(selected_value, report_id);


                                /*Ext.Msg.alert('Notification', 'Ok', function(btn) {
                                 if (btn == 'ok') {
                                 FieldOrder_win.close();
                                 }
                                 });
                                 
                                 }
                                 else{
                                 Ext.getCmp('hdnIdFieldOrder').setValue('');
                                 FieldOrder_win.close();
                                 /*Ext.getCmp('GrpBy_data').setValue('{}|-|');
                                 Ext.getCmp('GrpBy_store_data').setValue('[{}]');
                                 Application.Reports.tmp_GrpBy('[{}]', report_id);
                                 Ext.Msg.alert('Notification', 'Ok', function(btn) {
                                 if (btn == 'ok') {
                                 FieldOrder_win.close();
                                 }
                                 });
                                 */
                                FieldOrder_win.close();
                                Ext.getCmp('id_btnSetOrder').disable();
                                Ext.getCmp('id_btnResetOrder').enable();
                                Ext.getCmp('id_btnExeSP').enable();
                            }

                        }
                    }, {
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            //excludeAccHeadIds = [];
                            FieldOrder_win.close();
                        }
                    }]
            });
        }
        FieldOrder_win.doLayout();
        if (Ext.isEmpty(Ext.getCmp('hdnIdFieldOrderStore').getValue()))
        {
            var fieldNames = Ext.getCmp('hdnIdNoFields').getValue();
            //alert(2);
            //alert(fieldNames);
            excludeFieldNames = [];
            var excludeFieldNamesArray = [];
            excludeFieldNamesArray = fieldNames.split(",");
            for (var f = 0; f < excludeFieldNamesArray.length; f++) {
                excludeFieldNames.push(excludeFieldNamesArray[f]);

            }
            storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
            //alert('if');
            /*Ext.Ajax.request({
             url: modURL + '&op=get_GrpBy&rep_id=' + report_id,
             method: 'POST',
             success: function(resp) {
             var tmp = Ext.decode(resp.responseText);
             if (tmp.success !== undefined && tmp.success === true) {
             Ext.getCmp('GrpBy_grid').getStore().loadData(tmp.data);
             Application.Reports.tmp_GrpBy(tmp.data, report_id);
             }
             }
             });*/
        } else {
            //alert('else');
            //alert(Ext.getCmp('GrpBy_store_data').getValue());
            if (Ext.getCmp('hdnIdFieldOrderStore').getValue() != '[]')
            {
                /*var strCnt = Ext.decode(Ext.getCmp('GrpBy_store_data').getValue()).length;
                 var pushValue = '';
                 for (var q = 0; q < strCnt; q++)
                 {
                 pushValue = Ext.decode(Ext.getCmp('GrpBy_store_data').getValue())[q]['colHead'];
                 var isExist = excludeAccHeadIds.indexOf(pushValue);
                 if (isExist < 0) //check the value is already exist in excludeAccHeadIds
                 excludeAccHeadIds.push(pushValue);
                 }*/
                var loadForEdit = Ext.decode(Ext.getCmp('hdnIdFieldOrderStore').getValue());
                //loadForEdit = loadForEdit.replace('|','');
                //alert(1);
                //alert(loadForEdit);
                Ext.getCmp('idFiledOrderGrid').getStore().loadData(loadForEdit);
            }

        }
        FieldOrder_win.show();
        FieldOrder_win.center();
        //getOrderStore_fn.load();
        storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
        //storeOrder.baseParams.excludeFieldNames = excludeFieldNames;
        //storeOrder.load();
    };
    var select = new Ext.grid.RowSelectionModel({
        multiSelect: false,
        listeners: {
            rowselect: function (sm, row, rec) {
            }
        }
    });

    /*
     var assignStore = new Ext.data.SimpleStore({
     fields: ['id',  'name'],
     
     data: [['0','No'],['1','Yes']]
     }); 
     * 
     */

    var addFunction = function () {
        var e = new demoData({});
        var idFiledOrderGrid = Ext.getCmp('idFiledOrderGrid');
        idFiledOrderGrid.stopEditing();
        var pos = 0;
        var cnt = idFiledOrderGrid.store.getCount();
        if (cnt > 0 && idFiledOrderGrid.selModel.selection != null) {
            pos = idFiledOrderGrid.selModel.selection.cell[0];
        }

        idFiledOrderGrid.getStore().insert(pos, e);

        //wgrid.getView().refresh();
        //Ext.getCmp('id_wgrid').getSelectionModel().selectRow(0,true);

        idFiledOrderGrid.startEditing(pos, 0);
    };
    var demoData = Ext.data.Record.create([{
            name: 'demoValue',
            type: 'string'
        }]);
    var ColumnTotal_store = function () {
        var store = new Ext.data.JsonStore({
            fields: ['columnFieldTotal']
        });
        return store;
    };
    function columnSelect() {
        var grid_selection = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true,
            loadMask: true,
            listeners: {
                rowselect: function (SM, rowIndex, r) {
                    //setPayableAmountTotal();
                },
                rowdeselect: function (SM, rowIndex, r) {
                    //setPayableAmountTotal();   
                }
            }
        });
        return grid_selection;
    }
    var createFiledTotalPopup = function (ColumnNamesForTotal) {
        //alert(ColumnNamesForTotal);
        //var grid_store = GrpBy_store(report_id);
        //var getOrderStore_fn = orderStore_fn(report_id);
        var gridStore = ColumnTotal_store();
        var column_select = columnSelect();
        //var getOrderStore_fn = orderStore_fn(excludeFieldNames);
        if (Ext.isEmpty(FieldTotal_win)) {
            var FieldTotal_win = new Ext.Window({
                id: 'idFiledTotalWin',
                plain: true,
                modal: true,
                constrain: true,
                resizable: false,
                hideHeaders: true,
                width: 500,
                title: 'Set Field Total',
                autoHeight: true,
                items: [{
                        xtype: 'hidden',
                        id: 'hnIdSelectedField'
                                // sortable: true,
                                //: 'pkid2'

                    }, new Ext.grid.GridPanel({
                        store: gridStore,
                        layout: 'fit',
                        height: 225,
                        viewConfig: {
                            forceFit: true
                        },
                        enableColumnHide: false,
                        title: 'Select Column for Total',
                        id: 'gridColumnForTotal',
                        cm: new Ext.grid.ColumnModel([column_select, {
                                header: 'Column Name',
                                id: 'columnFieldTotal',
                                name: 'columnFieldTotal',
                                sortable: true,
                                dataIndex: 'columnFieldTotal'
                            }]),
                        sm: column_select,
                        stripeRows: true,
                        autoExpandColumn: 'columnFieldTotal'

                    })],
                buttons: [{
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            var grd = Ext.getCmp('gridColumnForTotal');
                            var selTotalStr;
                            //select '1,2,3,4,5,6,7' as total        
                            var rwindex = '';
                            var selectedRecord = grd.getSelectionModel().getSelections();
                            if (selectedRecord.length > 0)
                                selTotalStr = " select '";
                            for (var i = 0; i < selectedRecord.length; i++) {
                                rwindex += parseInt(grd.store.indexOf(selectedRecord[i]) + 1) + ',';

                            }
                            rwindex = rwindex.substring(0, rwindex.length - 1);
                            selTotalStr += rwindex + "' as total ";
                            Ext.getCmp('hdnIdFieldTotal').setValue(selTotalStr);
                            FieldTotal_win.close();
                            Ext.getCmp('id_btnResetTotal').enable();
                            Ext.getCmp('id_btnSetTotal').disable();
                            ///alert(selTotalStr);

                        }
                    }, {
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            //excludeAccHeadIds = [];
                            FieldTotal_win.close();

                        }
                    }]
            });
        }
        FieldTotal_win.doLayout();
        if (Ext.isEmpty(ColumnNamesForTotal))
        {

        } else {
            //alert(ColumnNamesForTotal);
            var loadForColumnNamesTotal = '';
            var ColumnNamesForTotalArray = [];
            ColumnNamesForTotalArray = ColumnNamesForTotal.split("|");
            loadForColumnNamesTotal = loadForColumnNamesTotal + '[';
            for (var cnt = 0; cnt < ColumnNamesForTotalArray.length; cnt++) {
                loadForColumnNamesTotal = loadForColumnNamesTotal + '{"columnFieldTotal":"' + ColumnNamesForTotalArray[cnt] + '"},';

            }
            loadForColumnNamesTotal = loadForColumnNamesTotal.substring(0, loadForColumnNamesTotal.length - 1);
            loadForColumnNamesTotal = loadForColumnNamesTotal + ']';
            //alert(loadForColumnNamesTotal);
            Ext.getCmp('gridColumnForTotal').getStore().loadData(Ext.decode(loadForColumnNamesTotal));


        }
        FieldTotal_win.show();
        FieldTotal_win.center();
        //getOrderStore_fn.load();
        //storeOrder.load({params: {excludeFieldNames: Ext.encode(excludeFieldNames)}});
        //storeOrder.baseParams.excludeFieldNames = excludeFieldNames;
        //storeOrder.load();
    };
    function showWindowForEdit(data) {
        createRSBPopup(data[0].report_id, 1);
        setFields(data);
    }
    function setFields(data) {
        Ext.getCmp('id_SPName').setValue(data[0].proc_name);
        Ext.getCmp('id_txt_ColumnNames').setValue(data[0].colname);
        Ext.getCmp('hdnIdFieldTotal').setValue(data[0].rptQry);
        Ext.getCmp('id_RPTName').setValue(data[0].rptname);
        Ext.getCmp('IsEdit').setValue(1);
        Ext.getCmp('id_BIRTName').setValue(data[0].birtrptname);

        //hdnIdFieldOrder  -    {"columnField":"CheckBox1_70"}|{"columnField":"dtPick1_70"}|
        //hdnIdFieldOrderStore -  [{"columnField":"CheckBox1_70"},{"columnField":"dtPick1_70"}]
        var field_order = data[0].field_order;
        var fieldsArray = [];

        var hdnIdNoFieldsString = replaceAll('|', ',', data[0].field_order);
        var hdnIdFieldOrderString = '';
        var hdnIdFieldOrderStoreString = '';
        fieldsArray = field_order.split("|");
        for (var flag = 0; flag < fieldsArray.length; flag++) {
            hdnIdFieldOrderString += '{"columnField":"' + fieldsArray[flag] + '"}|';
        }
        if (fieldsArray.length > 0)
            hdnIdFieldOrderStoreString += '[';

        for (var flg = 0; flg < fieldsArray.length; flg++) {
            hdnIdFieldOrderStoreString += '{"columnField":"' + fieldsArray[flg] + '"},';
        }
        hdnIdFieldOrderStoreString = hdnIdFieldOrderStoreString.substring(0, hdnIdFieldOrderStoreString.length - 1);

        if (fieldsArray.length > 0)
            hdnIdFieldOrderStoreString += ']';

        Ext.getCmp('hdnIdFieldOrder').setValue(hdnIdFieldOrderString);
        Ext.getCmp('hdnIdFieldOrderStore').setValue(hdnIdFieldOrderStoreString);
        Ext.getCmp('hdnIdNoFields').setValue(hdnIdNoFieldsString);

        Ext.getCmp('id_btnResetFields').enable();
        Ext.getCmp('id_btnResetOrder').enable();
        Ext.getCmp('id_btnExeSP').enable();
        Ext.getCmp('id_btnResetTotal').enable();
    }
    function replaceAll(find, replace, str)
    {
        while (str.indexOf(find) > -1)
        {
            str = str.replace(find, replace);
        }
        return str;
    }
/////////////////Report Switch Board END//////////////////////////////
};
Application.Utilities = new Utilities();
