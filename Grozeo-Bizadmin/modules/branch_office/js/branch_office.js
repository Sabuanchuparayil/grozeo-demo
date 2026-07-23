Application.BranchOffice = function () {
    var winsize = Ext.getBody().getViewSize();
    var modURL = '?module=branch_office';
    var recs_per_page = 12;
    var my_marker;
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var totalComboCount;
    var myMask, branchOfficeLoadedForm;
    var district_ID = new Array();
    var comboLoadComplete = function () {

        if (!Ext.isEmpty(branchOfficeLoadedForm) && branchOfficeLoadedForm != null) {
            branchOfficeLoadedForm[0].loadRecord(Ext.decode(branchOfficeLoadedForm[1]));
        }
        if (totalComboCount == 0) {
            myMask.hide();
            branchOfficeLoadedForm = null;
        }
    };
    var branchOfficeJsonStore = function () {
        var store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listBranchOffices',
                method: 'post'
            }),
            fields: ['id', 'boName', 'boDistrict', 'boState', 'boAddress', 'boEmail', 'boContactNo', 'boIncharge', 'boStatus', 'company', 'boShortCode', 'comp_id', 'boPincode', 'boLat', 'boLng', 'bocsdefault'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false
        });
        store.setDefaultSort('boName', 'ASC');
        return store;
    };

    var createBranchOfficeGrid = function (id) {
        var branchOffice_jsonstore = branchOfficeJsonStore();
        var branchOffice_filter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'boName'
                }, {
                    type: 'string',
                    dataIndex: 'boShortCode'
                }, {
                    type: 'string',
                    dataIndex: 'boDistrict'
                }, {
                    type: 'string',
                    dataIndex: 'boState'
                }, {
                    type: 'string',
                    dataIndex: 'boMobile'
                }, {
                    type: 'string',
                    dataIndex: 'boEmail'
                }, {
                    type: 'string',
                    dataIndex: 'boContactNo'
                }, {
                    type: 'string',
                    dataIndex: 'boIncharge'
                }, {
                    type: 'string',
                    dataIndex: 'company'
                }]
        });
        branchOffice_filter.remote = true;
        branchOffice_filter.autoReload = true;

        var grid_panel = new Ext.grid.GridPanel({
            store: branchOffice_jsonstore,
            layout: 'fit',
            frame: false,
            border: false,
            title: 'Branch Offices',
            plugins: [branchOffice_filter],
            id: id,
            loadMask: true,
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Branch Office Name',
                    id: 'branchOffice_name',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'boName',
                    tooltip: 'Branch Office Name'
                },
                {
                    header: 'Short Code',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'boShortCode',
                    tooltip: 'Short Code'
                },
                {
                    header: 'Company',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'company',
                    tooltip: 'Company'
                },
                {
                    header: 'District/City',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'boDistrict',
                    tooltip: 'District/City'
                },
                {
                    header: 'State/Province',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'boState',
                    tooltip: 'State/Province'
                },                
                {
                    header: 'Email',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'boEmail',
                    tooltip: 'Email'
                },
                {
                    header: 'Contact No.',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'boContactNo',
                    tooltip: 'Contact No.'
                },
                {
                    header: 'Incharge',
                    sortable: true,
                    hideable: true,
                    dataIndex: 'boIncharge',
                    tooltip: 'Incharge'
                },                
                {
                    header: 'Status',
                    id: 'boStatus',
                    sortable: true,
                    dataIndex: 'boStatus'
                },
                {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            var boStatus = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.boStatus;
                            if (boStatus == 'Inactive')
                                branchOfficeActionMenu2.showAt(e.getXY());
                            else
                                branchOfficeActionMenu1.showAt(e.getXY());
                        }
                    }
                }               
            ],
            viewConfig: {
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('bocsdefault') == 1)
                    {
                        return 'finascop_indicateColGUMLEAFGREEN';
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
                    text: 'Create Branch Office',
                    tooltip: 'Create Branch Office',
                    iconCls: 'add',
                    handler: function () {
                        branchOfficeDetails(0, 8.507007481504532, 76.95167541503906);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: branchOffice_jsonstore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No records to display",
                plugins: [branchOffice_filter]
            }),
            stripeRows: true,
            autoExpandColumn: 'branchOffice_name'
        });
        return grid_panel;
    };
    var branchOfficeActionMenu1 = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {

                    var id = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.id;
                    var boLat = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.boLat;
                    var boLng = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.boLng;
                    branchOfficeDetails(id, boLat, boLng);
                }
            }, {
                text: "Deactivate",
                handler: function () {
                    //var record = grid.store.getAt(rowIndex);
                    var id = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.id;
                    var boStatus = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.boStatus;
                    var comp_id = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.comp_id;
                    Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Branch Office?', function (btn, text) {
                        if (btn == 'yes')
                        {
                            changeBranchStatus(id, boStatus, comp_id);
                        }
                    });
                }
            }]
    });
    var branchOfficeActionMenu2 = new Ext.menu.Menu({
        items: [{
                text: "Edit",
                handler: function () {

                    var id = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.id;
                    var boLat = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.boLat;
                    var boLng = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.boLng;
                    branchOfficeDetails(id, boLat, boLng);
                }
            }, {
                text: "Activate",
                handler: function () {
                    var id = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.id;
                    var boStatus = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.boStatus;
                    var comp_id = Ext.getCmp('panelBranchOffice').getSelectionModel().getSelections()[0].data.comp_id;
                    Ext.MessageBox.confirm('Confirm', 'Do you wish to change the status of this Branch Office?', function (btn, text) {
                        if (btn == 'yes')
                        {
                            changeBranchStatus(id, boStatus, comp_id);
                        }
                    });
                }
            }]
    });

    var changeBranchStatus = function () {

        Application.BranchOffice.id = arguments[0];
        Application.BranchOffice.boStatus = arguments[1];
        Application.BranchOffice.comp_id = arguments[2];
        var form_data = {
            id: Application.BranchOffice.id,
            boStatus: Application.BranchOffice.boStatus,
            comp_id: Application.BranchOffice.comp_id
        };
        var params = {
            action: 'Update',
            module: 'branch_office',
            op: 'changeStatus',
            extrainfo: 'asd',
            id: Application.BranchOffice.id

        };

        APICall(params, Application.BranchOffice.changeStatus, form_data);
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
                            Ext.getCmp('boCompany').setValue(store.getAt(0).get("id"));
                        }

                    }
                }
            }
        });
    };

    var branchOfficeForm = function (map_lat, map_long) {
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
                        Ext.getCmp('boLat').setValue(markerAt.latLng.lat());
                        Ext.getCmp('boLng').setValue(markerAt.latLng.lng());
                    }
                }
            }];

        var form = new Ext.FormPanel({
            labelAlign: 'top',
            autoHeight: true,
            url: modURL + "&op=saveBranchOffice",
            width: 850,
            frame: true,
            id: 'branchoffice_form',
            layout: 'column',
            items: [{
                    layout: 'form',
                    columnWidth: 0.45,
                    hideBorders: true,
                    defaults: {
                        xtype: 'textfield',
                        anchor: '95%',
//                        allowBlank: false,
                        //style: 'margin:10px 0 10px 0',
                        hideBorders: true
                    },
                    items: [{
                            xtype: 'combo',
                            store: brComboStore(3),
                            mode: 'remote',
                            id: 'boCompany',
                            fieldLabel: 'Company',
                            hiddenName: 'boCompany',
                            displayField: 'name',
                            valueField: 'id',
                            editable: true,
                            typeAhead: true,
                            minChars: 2,
                            selectOnFocus: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            allowBlank: false,
                            tabIndex: 1,
                            listeners: {

                            }
                        }, {
                            xtype: 'hidden',
                            allowBlank: true,
                            id: 'id',
                            name: 'id'
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.7,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                            fieldLabel: 'Branch Office Name',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            id: 'boName',
                                            allowBlank: false,
                                            name: 'boName',
                                            maxLength: 300,
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
                                            fieldLabel: 'Branch Office Code',
                                            xtype: 'textfield',
                                            anchor: '98%',
                                            id: 'boShortCode',
                                            name: 'boShortCode',
                                            allowBlank: false,
                                            minLength: 4,
                                            maxLength: 4,
                                            tabIndex: 3
                                        }]
                                }]
                        },
                        {
                            fieldLabel: 'Address',
                            id: 'boAddress',
                            name: 'boAddress',
                            allowBlank: false,
                            maxLength: 765,
                            height: 90,
                            xtype: 'textarea',
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
                                            id: 'boState',
                                            anchor: '98%',
                                            fieldLabel: 'State/Province',
                                            hiddenName: 'boState',
                                            displayField: 'name',
                                            valueField: 'id',
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            allowBlank: false,
                                            tabIndex: 5,
                                            listeners: {
                                                select: function () {
                                                    var dist = Ext.getCmp('boDistrict');
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
                                            id: 'boDistrict',
                                            fieldLabel: 'District',
                                            hiddenName: 'boDistrict',
                                            displayField: 'name',
                                            valueField: 'id',
                                            editable: true,
                                            typeAhead: true,
                                            minChars: 2,
                                            forceSelection: true,
                                            triggerAction: 'all',
                                            lazyRender: true,
                                            allowBlank: false,
                                            tabIndex: 6,
                                        }]
                                },
                                {
                                    columnWidth: 0.2,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Post Code',
                                            id: 'boPincode',
                                            anchor: '98%',
                                            xtype: 'textfield',
                                            name: 'boPincode',
                                            maxLength: 10,
                                            allowBlank: false,
                                            tabIndex: 7,
                                            listeners: {
                                                focus: function () {
                                                    if (!Ext.isEmpty(Ext.getCmp('boPincode').getValue()))
                                                    {
                                                        Ext.getCmp('map_button1').enable();
                                                    }
                                                },
                                                change: function () {
                                                    var point = Ext.getCmp('bogooglemap').getCenterLatLng();
                                                    Ext.getCmp('boLat').setValue(point.lat);
                                                    Ext.getCmp('boLng').setValue(point.lng);
                                                    if (!Ext.isEmpty(Ext.getCmp('boPincode').getValue())) {
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
                                            id: 'boIncharge',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            fieldLabel: 'Contact Person',
                                            maxLength: 250,
                                            name: 'boIncharge',
                                            allowBlank: false,
                                            tabIndex: 8
                                        }],
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Telephone',
                                            xtype: 'numberfield',
                                            anchor: '99%',
                                            id: 'boContactNo',
                                            name: 'boContactNo',
                                            minLength: 10,
                                            maxLength: 10,
                                            msgTarget: 'under',
                                            minLengthText: 'The minimum length for this field is 10',
                                            maxLengthText: 'The maximum length for this field is 10',
                                            allowBlank: false,
                                            //vtype: 'phone',
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
                                            id: 'boEmail',
                                            name: 'boEmail',
                                            allowBlank: false,
                                            regex: /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/,
                                            regexText: 'Invalid Email',
                                            msgTarget: 'under',
                                            vtype: 'email',
                                            maxLength: 250,
                                            tabIndex: 10
                                        }],
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                            fieldLabel: 'Mobile',
                                            xtype: 'numberfield',
                                            anchor: '99%',
                                            id: 'boMobile',
                                            name: 'boMobile',
                                            allowBlank: false,
                                            //vtype: 'phone',
                                            minLength: 10,
                                            maxLength: 10,
                                            msgTarget: 'under',
                                            minLengthText: 'The minimum length for this field is 10',
                                            maxLengthText: 'The maximum length for this field is 10',
                                            tabIndex: 11
                                        }]
                                }]
                        }, {
                            xtype: 'panel',
                            layout: 'column',
                            items: [{
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                        id: 'boGstinRegular',
                                        xtype: 'textfield',
                                        anchor: '99%',
                                        fieldLabel: 'GSTIN Regular',
                                        maxLength: 30,
                                        name: 'boGstinRegular',
                                        tabIndex: 8
                                    }]
                                }, {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    items: [{
                                        id: 'boGstinTaxCollector',
                                        xtype: 'textfield',
                                        anchor: '99%',
                                        fieldLabel: 'GSTIN TaxCollector',
                                        maxLength: 30,
                                        name: 'boGstinTaxCollector',
                                        tabIndex: 8
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
                                        id: 'boTan',
                                        xtype: 'textfield',
                                        anchor: '99%',
                                        fieldLabel: 'TAN',
                                        maxLength: 30,
                                        name: 'boTan',
                                        tabIndex: 8
                                    }
                                    ],
                                },
                                {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                        id: 'boEpfNo',
                                        xtype: 'textfield',
                                        anchor: '99%',
                                        fieldLabel: 'EPF No',
                                        maxLength: 30,
                                        name: 'boEpfNo',
                                        tabIndex: 8
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
                                        id: 'boEsiNo',
                                        xtype: 'textfield',
                                        anchor: '99%',
                                        fieldLabel: 'ESI No',
                                        maxLength: 30,
                                        name: 'boEsiNo',
                                        tabIndex: 8
                                    }
                                    ],
                                },
                                {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                        id: 'boProffessioalTax',
                                        xtype: 'textfield',
                                        anchor: '99%',
                                        fieldLabel: 'Proffessional Tax',
                                        maxLength: 30,
                                        name: 'boProffessioalTax',
                                        tabIndex: 8
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
                                        columnWidth: 0.5,
                                        layout: 'form',
                                        labelAlign: 'top',
                                        hideBorders: true,
                                        items: [{
                                            id: 'boOtherLicense',
                                            xtype: 'textfield',
                                            anchor: '99%',
                                            fieldLabel: 'Other License',
                                            maxLength: 250,
                                            name: 'boOtherLicense',
                                            tabIndex: 8
                                        }]
                                    }
                                    ],
                                },
                                {
                                    columnWidth: 0.5,
                                    layout: 'form',
                                    labelAlign: 'top',
                                    hideBorders: true,
                                    items: [{
                                        id: 'boOtherLicenseNo',
                                        xtype: 'textfield',
                                        anchor: '99%',
                                        fieldLabel: 'Other License No',
                                        maxLength: 30,
                                        name: 'boOtherLicenseNo',
                                        tabIndex: 8
                                    }]
                                }]
                        }
                    ]
                },
                {
                    layout: 'form',
                    columnWidth: 0.55,
                    title: 'MAP',
                    region: 'center',
                    items: [{
                            xtype: 'gmappanel',
                            gmapType: 'map',
                            id: 'bogooglemap',
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
                                var gmappanel = Ext.getCmp('bogooglemap');
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
                                                            tabIndex: 13,
                                                            handler: function () {
                                                                Ext.getCmp('bogooglemap').clearMarkers();
                                                                my_marker = [];
                                                                //Ext.getCmp('bogooglemap').clearMarkers();
                                                                my_marker.push({
                                                                    geoCodeAddr: Ext.getCmp("boPincode").getValue(),
                                                                    setCenter: true,
                                                                    marker: {
                                                                        title: "Click and Drag to Move Around",
                                                                        draggable: true
                                                                    },
                                                                    listeners: {
                                                                        onFailure: function () {

                                                                        },
                                                                        "tilesloaded": function (markerAt) {
                                                                            Ext.getCmp('boLat').setValue(markerAt.latLng.lat());
                                                                            Ext.getCmp('boLng').setValue(markerAt.latLng.lng());
                                                                        },
                                                                        onSuccess: function (point) {
                                                                            Ext.getCmp('boLat').setValue(point.latLng.lat());
                                                                            Ext.getCmp('boLng').setValue(point.latLng.lng());
                                                                        },
                                                                        "dragend": function (markerAt) {
                                                                            Ext.getCmp('boLat').setValue(markerAt.latLng.lat());
                                                                            Ext.getCmp('boLng').setValue(markerAt.latLng.lng());
                                                                        }
                                                                    },
                                                                    icon: null
                                                                });
                                                                Ext.getCmp('bogooglemap').addScaleControl();
                                                                Ext.getCmp('bogooglemap').clearMarkers();
                                                                Ext.getCmp('bogooglemap').addMarkers(my_marker);
                                                                Ext.defer(function () {
                                                                    var point = Ext.getCmp('bogooglemap').getCenterLatLng();
                                                                    Ext.getCmp('boLat').setValue(point.lat);
                                                                    Ext.getCmp('boLng').setValue(point.lng);
                                                                    Ext.getCmp('bogooglemap').clearMarkers();
                                                                    Ext.getCmp('bogooglemap').repaint(13);
                                                                    Ext.getCmp('bogooglemap').addMarkers(my_marker);
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
                                                            id: 'boLat',
                                                            name: 'boLat',
                                                            tabIndex: 14,
                                                            allowBlank: false,
                                                            anchor: '95%'
                                                        }]
                                                }, {
                                                    layout: 'form',
                                                    columnWidth: .35,
                                                    items: [{
                                                            xtype: 'textfield',
                                                            fieldLabel: 'Longitude',
                                                            id: 'boLng',
                                                            name: 'boLng',
                                                            tabIndex: 15,
                                                            allowBlank: false,
                                                            anchor: '95%'
                                                        }]
                                                }]
                                        }]
                                }]
                        }
                    ]
                }]

        });
        return form;
    };

    var branchOfficeDetails = function () {

        var id = arguments[0];
        var map_lat = arguments[1];
        var map_long = arguments[2];
        var win_id = "branchoffice_details_window";

        var branchoffice_details_window = Ext.getCmp(win_id);
        if (Ext.isEmpty(branchoffice_details_window)) {
            var branchOffice_form = branchOfficeForm(map_lat, map_long);
            branchoffice_details_window = new Ext.Window({
                id: win_id,
                title: 'Create Branch Office',
                layout: 'fit',
                height: 585,
                width: 860,
                autoScroll: true,
                plain: true,
                modal: true,
                frame: true,
                resizable: false,
                items: branchOffice_form,
                fbar: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            branchoffice_details_window.close();
                        }
                    }, {
                        text: 'Save',
                        icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                        iconCls: 'my-icon1',
                        handler: function () {
                            if (branchOffice_form.getForm().isValid()) {
                                Application.BranchOffice.saveBranchOffice()

                            } else {
                                Ext.MessageBox.alert("Notification", "Please enter all required fields");
                            }
                        }
                    }],
                listeners: {
                    afterrender: function () {
                        //Ext.getCmp('boCompany').focus(true);
                        if (_SESSION.UserId == 1) {
//                            Ext.getCmp('branchIsCPD').show();
                        } else {
//                            Ext.getCmp('branchIsCPD').hide();
                        }
                        if (!Ext.isEmpty(id) && id > 0) {
                            var t = new Date();
                            var t_stamp = t.format("YmdHis");

                            myMask = new Ext.LoadMask(Ext.getCmp('branchoffice_details_window').getEl(), {msg: "Please wait..."});
                            myMask.show();

                            branchOffice_form.load({
                                url: modURL + '&op=getDetails',
                                params: {
                                    'id': id,
                                    apikey: _SESSION.apikey,
                                    tstamp: t_stamp
                                },
                                success: function (frm, action) {

                                    eval('var tmp=' + action.response.responseText);
                                    branchOfficeLoadedForm = [frm, action.response.responseText];
                                    totalComboCount = 3;


                                    branchoffice_details_window.setTitle("Edit Branch Office Details : " + Ext.getCmp("boName").getValue());
                                    var boState = Ext.getCmp('boState');


                                    boState.getStore().load({
                                        params: {
                                            ind: 1
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });

                                    var boDistrict = Ext.getCmp('boDistrict');
                                    boDistrict.getStore().baseParams.state = boState.getValue();

                                    boDistrict.getStore().load({
                                        params: {
                                            ind: 2
                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        }
                                    });

                                    Ext.getCmp('boCompany').getStore().load({
                                        params: {
                                            ind: 3

                                        },
                                        callback: function () {
                                            totalComboCount--;
                                            comboLoadComplete();
                                        },
                                    });
                                    Ext.getCmp('boCompany').setHideTrigger(true);
                                    Ext.getCmp('boCompany').setReadOnly(true);



                                }
                            });
                        }
                    }
                }
            });
        }

        branchoffice_details_window.doLayout();
        branchoffice_details_window.show(this);
        branchoffice_details_window.center();
    };




    return {
        Cache: {},
        init: function () {
            var _branchOfficePanelId = 'panelBranchOffice';
            var _branchOfficePanel = Ext.getCmp(_branchOfficePanelId);
            if (Ext.isEmpty(_branchOfficePanel)) {
                _branchOfficePanel = createBranchOfficeGrid(_branchOfficePanelId);
                Application.UI.addTab(_branchOfficePanel);
                _branchOfficePanel.doLayout();
            } else {
                Application.UI.addTab(_branchOfficePanel);
                _branchOfficePanel.doLayout();
            }
        },
        saveBranchOffice: function () {
            var branchoffice_form = Ext.getCmp('branchoffice_form');

            var t = new Date();
            var t_stamp = t.format("YmdHis");

            Ext.MessageBox.show({
                msg: 'Saving your data, please wait...',
                title: 'Wait...',
                progressText: 'Saving...',
                width: 300,
                wait: true
            });

            branchoffice_form.getForm().submit({
                waitTitle: 'Please Wait!',
                waitMsg: 'Saving data...',
                params: {
                    apikey: _SESSION.apikey,
                    tstamp: t_stamp
                },
                success: function (branchoffice_form, action) {
                    Ext.MessageBox.hide();
                    var tmp = Ext.decode(action.response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Ext.getCmp('branchoffice_details_window').close();
                        Application.example.msg("Success", "Branch Office details has been saved successfully.");
                        Ext.getCmp('panelBranchOffice').getStore().reload();
                    }
                },
                failure: function (branchoffice_form, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: (obj.error) ? obj.error : obj.errors.reason, buttons: Ext.MessageBox.OK,
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
                    id: Application.BranchOffice.id,
                    boStatus: Application.BranchOffice.boStatus,
                    comp_id: Application.BranchOffice.comp_id
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', ACTION_FAIL);
                },
                success: function (response, options) {
                    eval("var tmp=" + response.responseText);
                    if (tmp.success === true) {
                        Application.example.msg("Success", "Branch Office status has been changed successfully");
                        Ext.getCmp('panelBranchOffice').getStore().reload();


                    }
                }
            });
        }

    }
}();
