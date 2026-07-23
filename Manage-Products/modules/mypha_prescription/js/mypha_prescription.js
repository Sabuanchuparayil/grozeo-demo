Application.PrescriptionManagement = function () {
    var winLoadMask;
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 18;
    var modURL = '?module=mypha_prescription';
    function updatePagination(cmp) {
        recs_per_page = finascop_update_recs_per_page(cmp);
    }
    var onGridResize = function (cmp) {
        RECS_PER_PAGE = finascop_update_recs_per_page(cmp);
    };
    var unverifiedPrescriptionPanel = function (id, type) {
        var title;
        switch (type) {
            case 'NV':
                title = 'Verify Prescriptions';
                break;
            case 'Verified':
                title = 'Valid Prescriptions';
                break;
            case 'Approved':
                title = 'Mapped Prescriptions';
                break;
        }
        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id + type,
            title: title,
            items: [unverifiedPrescriptionGrid(type)]
        });
        return panel;
    };
    var unverifiedPrescriptionGridStore = function (type) {
        var _ppoStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getUserPrescriptonData',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'id',
                root: 'data'
            }, ['id', 'customerId', 'status', 'created_at', 'updated_at', 'cust_customer_name', 'cust_email', 'cust_mobile', 'isAssigned', 'assignedUser', 'statusName', 'expiry_date', 'order_id', 'priority',
                'prescription_json', 'preCount', 'isSkipped']),
            sortInfo: {
                field: 'id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            //    remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.type = type;
                },
                load: function () {
                    Ext.getCmp('gridpanelUserPrescription' + type).getSelectionModel().selectRow(0);
                }
            }
        });
        return _ppoStore;
    };
    var unverifiedPrescriptionGrid = function (type) {

        var _userPrescripGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'cust_customer_name'
                }, {
                    type: 'string',
                    dataIndex: 'cust_email'
                }, {
                    type: 'string',
                    dataIndex: 'cust_mobile'
                }, {
                    type: 'string',
                    dataIndex: 'statusName'
                }, {
                    type: 'string',
                    dataIndex: 'created_at'
                }, {
                    type: 'string',
                    dataIndex: 'preCount'
                }]
        });
        _userPrescripGridFilter.remote = true;
        _userPrescripGridFilter.autoReload = true;

        var _gridStore = unverifiedPrescriptionGridStore(type);

        var _gridPanel = new Ext.grid.GridPanel({
            id: 'gridpanelUserPrescription' + type,
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            store: _gridStore,
            loadMask: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('isSkipped') == 1)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _userPrescripGridFilter],
            columns: [
                {
                    header: 'Customer Name',
                    sortable: true,
                    dataIndex: 'cust_customer_name',
                    tooltip: 'Customer Name',
                    hideable: false
                }, {
                    header: 'Email',
                    sortable: true,
                    dataIndex: 'cust_email',
                    tooltip: 'Email'
                }, {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'cust_mobile',
                    tooltip: 'Mobile'
                },
                {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'statusName',
                    tooltip: 'Status'
                },
                {
                    header: 'Created On',
                    sortable: true,
                    dataIndex: 'created_at',
                    tooltip: 'Created On',
                    hideable: false
                }, {
                    header: 'Count',
                    sortable: true,
                    dataIndex: 'preCount',
                    tooltip: 'Count',
                    hideable: false
                }, 
                /*{
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: true,
                    iconCls: 'downarrow',
                    tooltip: 'Choose Actions',
                    listeners: {
                        click: function (a, grid, rowindex, e) {
                            var record = grid.store.getAt(rowindex);
                            grid.getSelectionModel().selectRow(rowindex);
                            var status = Ext.getCmp('prescription_user_panel').getSelectionModel().getSelections()[0].data.status;    
                            verifyprescriptionActionMenu(e,status)
                            //action
                        }
                    }
                },*/
                {
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: '',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _currentstatus = data.isAssigned;
                                var _currentordstatus = data.status;
                                if ((_currentordstatus == 1 || _currentordstatus == 0) && _currentstatus == 0) {
                                    this.items[0].tooltip = 'Assign';
                                    return 'assign';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var id = record.data.id;
                                var _status = record.data.status;
                                var order_id = record.data.order_id;
                                var _active = record.data.isAssigned;
                                var customerId = record.data.customerId;
                                if ((_status == 1 || _status == 0) && _active == 0) {
                                    Ext.MessageBox.confirm('Confirm', 'Do you want to get this assigned?', function (btn, text) {
                                        if (btn == 'yes') {
                                            assignUserForMappingUserPrescription(id, type, order_id, customerId);
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.alert("Message Box", "Assiging is not possible.", function (btn) {

                                    });
                                }
                            }
                        }, {
                            text: ' ',
                            getClass: function (v, meta, rec) {
                                var _statusVal, _tooltipVal;
                                var data = rec.data;
                                var _status = data.status;
                                var _currentstatus = data.isAssigned;//preord_isAssigned
                                var _currentuser = data.assignedUser;
                                switch (type) {
                                    case 'NV':
                                        _statusVal = 0;
                                        _tooltipVal = 'Verify Prescription';
                                        break;
                                    case 'Verified':
                                        _statusVal = 1;
                                        _tooltipVal = 'Map Precriptions';
                                        break;
                                }
                                if ((_currentstatus == 1) && (_currentuser == _SESSION.UserId) && (_status == _statusVal)) {
                                    this.items[1].tooltip = _tooltipVal;
                                    return 'edit_profile';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.store.getAt(rowIndex);
                                var id = record.data.id;
                                var order_id = record.data.order_id;
                                var customerId = record.data.customerId;
                                switch (type) {
                                    case 'NV':
                                        userPreescriptionMapWindow(id, type, order_id, customerId);
                                        break;
                                    case 'Verified':
                                        preescriptionMapWindow(id, type, order_id, customerId);
                                        break;
                                }
                            }
                        }, {
                            iconCls: '',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _currentordstatus = data.status;
                                if (_currentordstatus == 3) {
                                    this.items[2].tooltip = 'View Prescription';
                                    return 'viewfile';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var id = record.data.id;
                                var _status = record.data.status;
                                var order_id = record.data.order_id;
                                var _active = record.data.isAssigned;
                                var customerId = record.data.customerId;
                                preescriptionMapViewWindow(id, type, order_id, customerId);

                            }
                        }, {
                            iconCls: '',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var _currentordstatus = data.status;
                                if (_currentordstatus == 3) {
                                    this.items[3].tooltip = 'View Medicines';
                                    return 'application_view_detail';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var id = record.data.id;
                                var _status = record.data.status;
                                var order_id = record.data.order_id;
                                var _active = record.data.isAssigned;
                                var customerId = record.data.customerId;
                                preescriptionMedicineViewWindow(id, type, order_id, customerId);

                            }
                        }]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true
            }),
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            listeners: {
                resize: onGridResize
            }

        });
        return _gridPanel;
    };
    /*var verifyprescriptionActionMenu= function(e,status){
        var verifyprescriptionActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Asign",
                hidden: !((status == 1 || status == 0) && status == 0),
                handler: function () {
                                var id = Ext.getCmp('prescription_user_panel').getSelectionModel().getSelections()[0].data.id;
                                var status = Ext.getCmp('prescription_user_panel').getSelectionModel().getSelections()[0].data.status;
                                var order_id = Ext.getCmp('prescription_user_panel').getSelectionModel().getSelections()[0].data.order_id;
                                var order_id = Ext.getCmp('prescription_user_panel').getSelectionModel().getSelections()[0].data.order_id;
                                var isAssigned = Ext.getCmp('prescription_user_panel').getSelectionModel().getSelections()[0].data.isAssigned;
                                var customerId = Ext.getCmp('prescription_user_panel').getSelectionModel().getSelections()[0].data.customerId;
                                if ((status == 1 || status == 0) && isAssigned == 0) {
                                    Ext.MessageBox.confirm('Confirm', 'Do you want to get this assigned?', function (btn, text) {
                                        if (btn == 'yes') {
                                            assignUserForMappingUserPrescription(id, type, order_id, customerId);
                                        }
                                    });
                                } else {
                                    Ext.MessageBox.alert("Message Box", "Assiging is not possible.", function (btn) {

                                    });
                                }
                }
            }]
    });
    verifyprescriptionActionMenu.showAt(e.getXY());
    };*/
    
    var assignUserForMappingUserPrescription = function (id, type, order_id, customerId) {
        Ext.Ajax.request({
            waitMsg: 'Processing',
            method: 'POST',
            url: modURL + '&op=getUserForMappingUserPrescription',
            params: {
                id: id,
                type: type,
                customerId: customerId
            },
            success: function (response) {

                var tmp = Ext.util.JSON.decode(response.responseText);
                if (tmp.success === true) {

                    Ext.getCmp('gridpanelUserPrescription' + type).getStore().load(
                            {
                                callback: function (record, options, success) {
                                    var gridPanel = Ext.getCmp('gridpanelUserPrescription' + type);
                                    var index = gridPanel.store.find('id', id);
                                    gridPanel.getSelectionModel().selectRow(index);
                                    switch (type) {
                                        case 'NV':
                                            userPreescriptionMapWindow(id, type, order_id, customerId);
                                            break;
                                        case 'Verified':
                                            preescriptionMapWindow(id, type, order_id, customerId);
                                            break;
                                    }
                                }
                            }
                    );


                } else {
                    Ext.MessageBox.alert('Error', tmp.msg);
                }
            },
            failure: function (response) {
                var tmp = Ext.util.JSON.decode(response.responseText);
                Ext.MessageBox.alert('Error', tmp.msg);
            }
        });
    };
    var userPreescriptionMapWindow = function (id, type, order_id, customerId) {
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var oreder_userlog_win = Ext.getCmp('ORDER_userlog_win');
        if (Ext.isEmpty(oreder_userlog_win)) {
            var oreder_userlog_win = new Ext.Window({
                id: 'precriptionMapWindow' + type,
                title: 'Verify Prescription',
                modal: true,
                height: winsize.height * 0.9,
                width: winsize.width * 0.9,
                shadow: false,
                resizable: false,
                items: [
                    new Ext.Panel({
                        layout: 'fit',
                        border: false,
                        width: winsize.width * 0.9,
                        autoScroll: true,
                        items: [{
                                region: 'center',
                                border: false,
                                html: '<iframe id="downloadContactsIframe" name="downloadContactsIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="/myphaprescription/validation/?module=mypha_prescription&preord_id=' + id + '&type=' + type + '&order_id=' + order_id + '&customerId=' + customerId + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp + '"; ></iframe>'
                            }]
                    })
                ],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        tabIndex: 130,
                        handler: function () {
                            Application.PrescriptionManagement.pharmaWindow(type);
                            //Ext.getCmp('precriptionMapWindow' + type).close();
                        }
                    }]
            });
        }

        oreder_userlog_win.doLayout();
        oreder_userlog_win.show();
        oreder_userlog_win.center();
    };
    var preescriptionMapWindow = function (id, type, order_id, customerId) {

        var preord_id = id;
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var oreder_userlog_win = Ext.getCmp('ORDER_userlog_win');
        if (Ext.isEmpty(oreder_userlog_win)) {
            var oreder_userlog_win = new Ext.Window({
                id: 'precriptionMapWindow' + type,
                title: 'Map Prescription',
                modal: true,
                height: winsize.height * 0.9,
                width: winsize.width * 0.9,
                shadow: false,
                resizable: false,
                items: [
                    new Ext.Panel({
                        layout: 'fit',
                        border: false,
                        width: winsize.width * 0.9,
                        autoScroll: true,
                        items: [{
                                region: 'center',
                                border: false,
                                html: '<iframe id="downloadContactsIframe" name="downloadContactsIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="/myphaprescription/prescriptionmapping/?module=mypha_prescription&preord_id=' + preord_id + '&type=' + type + '&order_id=' + order_id + '&customerId=' + customerId + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp + '"; ></iframe>'
                            }]
                    })
                ],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        tabIndex: 130,
                        handler: function () {
                            Application.PrescriptionManagement.pharmaWindow(type);
                            //Ext.getCmp('precriptionMapWindow' + type).close();
                        }
                    }, {
                        text: 'Skip',
                        tabIndex: 130,
                        handler: function () {
                            Ext.MessageBox.confirm('Confirm', 'Do you want to skip the prescriotion(s)?', function (btn, text) {
                                if (btn == 'yes') {
                                    Ext.Ajax.request({
                                        waitMsg: 'Processing',
                                        method: 'POST',
                                        url: modURL + '&op=skipPrescription',
                                        params: {
                                            id: id,
                                            type: type,
                                            customerId: customerId
                                        },
                                        success: function (response) {

                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                            if (tmp.success === true) {
                                                Application.PrescriptionManagement.pharmaWindow(type);
                                                Ext.getCmp('gridpanelUserPrescription' + type).getStore().load(
                                                        {
                                                            callback: function (record, options, success) {
                                                                var gridPanel = Ext.getCmp('gridpanelUserPrescription' + type);
                                                                var index = gridPanel.store.find('id', id);
                                                                gridPanel.getSelectionModel().selectRow(index);
                                                            }
                                                        }
                                                );


                                            } else {
                                                Ext.MessageBox.alert('Error', tmp.msg);
                                            }
                                        },
                                        failure: function (response) {
                                            var tmp = Ext.util.JSON.decode(response.responseText);
                                            Ext.MessageBox.alert('Error', tmp.msg);
                                        }
                                    });
                                }
                            });
                        }
                    }]
            });
        }

        oreder_userlog_win.doLayout();
        oreder_userlog_win.show();
        oreder_userlog_win.center();
    };
    var preescriptionMapViewWindow = function (id, type, order_id, customerId) {

        var preord_id = id;
        var t = new Date();
        var t_stamp = t.format("YmdHis");
        var oreder_userlog_win = Ext.getCmp('ORDER_userlog_win');
        if (Ext.isEmpty(oreder_userlog_win)) {
            var oreder_userlog_win = new Ext.Window({
                id: 'precriptionMapWindow' + type,
                title: 'View Prescription',
                modal: true,
                height: winsize.height * 0.9,
                width: winsize.width * 0.9,
                shadow: false,
                resizable: false,
                items: [
                    new Ext.Panel({
                        layout: 'fit',
                        border: false,
                        width: winsize.width * 0.9,
                        autoScroll: true,
                        items: [{
                                region: 'center',
                                border: false,
                                html: '<iframe id="downloadContactsIframe" name="downloadContactsIframe" style="overflow:auto;height:100%;width: 100%" frameborder="0"  src="/myphaprescription/prescriptionview/?module=mypha_prescription&preord_id=' + preord_id + '&type=' + type + '&order_id=' + order_id + '&customerId=' + customerId + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp + '"; ></iframe>'
                            }]
                    })
                ],
                buttons: [{
                        text: 'Cancel',
                        icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                        tabIndex: 130,
                        handler: function () {
                            Application.PrescriptionManagement.pharmaWindow(type);
                            //Ext.getCmp('precriptionMapWindow' + type).close();
                        }
                    }]
            });
        }

        oreder_userlog_win.doLayout();
        oreder_userlog_win.show();
        oreder_userlog_win.center();
    };
    var queuedPrescriptionPanel = function (id, type) {
        var title;

        var panel = new Ext.Panel({
            layout: 'border',
            bodyStyle: {"background-color": "white"},
            border: false,
            frame: false,
            id: id + type,
            title: 'Queued Prescriptions',
            items: [queuedPrescriptionGrid(type)]
        });
        return panel;
    };
    var queuedPrescriptionGrid = function (type) {

        var _userPrescripGridFilter = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'cust_customer_name'
                }, {
                    type: 'string',
                    dataIndex: 'cust_mobile'
                }, {
                    type: 'string',
                    dataIndex: 'order_order_id'
                }]
        });
        _userPrescripGridFilter.remote = true;
        _userPrescripGridFilter.autoReload = true;

        var _gridStore = queuedPrescriptionGridStore(type);

        var _gridPanel = new Ext.grid.GridPanel({
            id: 'gridpanelUserPrescription' + type,
            region: 'center',
            layout: 'fit',
            frame: false,
            border: false,
            store: _gridStore,
            loadMask: true,
            view: new Ext.grid.GroupingView({
                forceFit: true,
                deferEmptyText: false,
                getRowClass: function (record, index, params, store) {
                    if (record.get('isSkipped') == 1)
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else {
                        return '';
                    }
                },
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), _userPrescripGridFilter],
            columns: [
                {
                    header: 'Customer Name',
                    sortable: true,
                    dataIndex: 'cust_customer_name',
                    tooltip: 'Customer Name',
                    hideable: false
                }, {
                    header: 'Order',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order'
                }, {
                    header: 'Mobile',
                    sortable: true,
                    dataIndex: 'cust_mobile',
                    tooltip: 'Mobile'
                },
                {
                    header: 'Created On',
                    sortable: true,
                    dataIndex: 'created_at',
                    tooltip: 'Created On',
                    hideable: false
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
                            queuedpreActionMenu.showAt(e.getXY());
                            //action
                        }
                    }
                }
                /*{
                    xtype: 'actioncolumn',
                    hideable: false,
                    sortable: false,
                    items: [{
                            iconCls: '',
                            getClass: function (v, meta, rec) {
                                var data = rec.data;
                                var order_prescription_validated = data.order_prescription_validated;
                                if (order_prescription_validated == 1) {
                                    this.items[0].tooltip = 'Move to Packing Order';
                                    return 'assign';
                                } else {
                                    return 'finascop_hideicon';
                                }
                            },
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var order_id = record.data.order_id;
                                var customer_id = record.data.order_customer_id;

                            }
                        }, {
                            tooltip: 'Upload Prescription',
                            iconCls: 'upload',
                            handler: function (grid, rowIndex, colIndex, itm, evn) {
                                var record = grid.store.getAt(rowIndex);
                                var order_id = record.data.order_id;
                                var customer_id = record.data.order_customer_id;
                                file_window(order_id, customer_id);
                            }
                        }]
                }*/
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelected: true
            }),
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: _gridStore,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true,
            listeners: {
                resize: onGridResize
            }

        });
        return _gridPanel;
    };
    var queuedpreActionMenu = new Ext.menu.Menu({
        items: [{
                text: "Upload Prescription",
                handler: function () {
                                var type = 'Queued';
                                var order_id = Ext.getCmp('gridpanelUserPrescription' + type).getSelectionModel().getSelections()[0].data.order_id;
                                var order_customer_id = Ext.getCmp('gridpanelUserPrescription' + type).getSelectionModel().getSelections()[0].data.order_customer_id;
                                file_window(order_id, order_customer_id);
                            }
            }]
    });
    var queuedPrescriptionGridStore = function (type) {
        var _ppoStore = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getQueuedPrescriptonData',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_id', 'order_order_id', 'order_isB2b', 'order_customer_id', 'order_branch_id', 'order_prescription_validated', 'cust_customer_name', 'cust_mobile', 'created_at', 'order_customer_id']),
            sortInfo: {
                field: 'order_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            //    remoteSort: true,
            autoLoad: true,
            root: 'data',
            listeners: {
                beforeload: function () {
                    this.baseParams.type = type;
                },
                load: function () {
                    Ext.getCmp('gridpanelUserPrescription' + type).getSelectionModel().selectRow(0);
                }
            }
        });
        return _ppoStore;
    };
    var file_window = function (orderId, customerId) {
        var assignment_fileForm = fileForminAssignment();
        var assgmtFileWindows = new Ext.Window({
            id: 'assgmtFileWindows',
            modal: true,
            autoHeight: true,
            constrain: true,
            width: 400,
            height: 500,
            resizable: false,
            floating: true,
            title: 'Add Prescription',
            shadow: false,
            layout: 'column',
            items: [assignment_fileForm],
            buttons: [{
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    tabIndex: 4,
                    width: 98,
                    handler: function () {
                        Ext.getCmp('assgmtFileWindows').close();
                    }
                },
                {
                    text: 'Save',
                    icon: IMAGE_BASE_PATH + '/default/icons/disk.png',
                    tabIndex: 3,
                    width: 98,
                    handler: function () {

                        var vcasf_status;
                        if (!Ext.isEmpty(Ext.getCmp('assignment_files').getValue())) {
                            Ext.getCmp('assignment_fileForm').getForm().submit({
                                waitMsg: 'Please wait....',
                                method: 'POST',
                                url: modURL + '&op=savePrescriptionFiles',
                                params: {
                                    orderId: orderId,
                                    customerId: customerId,
                                    vcasf_status: vcasf_status
                                },
                                success: function (form, action) {
                                    var tmp = Ext.decode(action.response.responseText);
                                    if (tmp.success == true && tmp.valid == true) {
                                        Application.example.msg('Success', tmp.message);
                                        assgmtFileWindows.close();

//                                        tmpFileStore(vcasf_fileremark, tmp.data, tmp.thump);
//                                        updateSortOrder();

                                    } else if (tmp.success == false && tmp.valid == false) {
                                        Ext.Msg.alert('Notification', tmp.message);

                                    } else {
                                        Ext.Msg.alert("Error", tmp.message);
                                    }
                                }, failure: function (response, options) {
                                    Ext.Msg.alert('Notification', 'Server Error');
                                }
                            });
                        } else {
                            Ext.Msg.alert("Error", 'Upload File and Enter remarks to proceed');
                        }

                    }
                }
            ]

        });
        var assgmtFileWindows = Ext.getCmp('assgmtFileWindows');
        assgmtFileWindows.show();
        assgmtFileWindows.doLayout();
        assgmtFileWindows.center();
    };
    var fileForminAssignment = function () {
        var form = new Ext.form.FormPanel({
            labelAlign: 'top',
            id: 'assignment_fileForm',
            frame: false,
            //columnWidth: 0.3,
            //height: 50,
            border: false,
            hideBorders: true,
            hideLabel: true,
            fileUpload: true,
            width: 400,
            //labelWidth: 70,
            defaults: {
                bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"}
            },
            items: [{
                    xtype: 'hidden',
                    id: 'assignment_files',
                    name: 'assignment_files'
                }, {
                    xtype: 'hidden',
                    id: 'assignment_filename',
                    name: 'assignment_filename'
                }, {
                    xtype: 'hidden',
                    id: 'assignment_filetype',
                    name: 'assignment_filetype'
                }, {
                    xtype: 'hidden',
                    id: 'vcrbucket',
                    name: 'vcrbucket'
                }, {
                    xtype: 'hidden',
                    id: 'vcrresizer',
                    name: 'vcrresizer'
                }, {
                    xtype: 'hidden',
                    id: 'access_Key',
                    name: 'access_Key'
                }, {
                    xtype: 'hidden',
                    id: 'secret_Key',
                    name: 'secret_Key'
                }, {
                    xtype: 'hidden',
                    id: 'bucket_Region',
                    name: 'bucket_Region'
                },
                {
                    xtype: 'hidden',
                    id: 'oncompleteurl',
                    name: 'oncompleteurl'
                },
                {
                    xtype: 'fileuploadfield',
                    id: 'assignment_associate_file',
                    name: 'assignment_associate_file',
                    buttonOnly: true,
                    buttonText: 'Browse',
                    tabIndex: 6,
                    buttonCfg: {
                        //iconCls: 'csv',
                        width: 80,
                        text: 'Browse File',
                    }, validator: function (v2) {
                        var upload = 2;
                        if (v2 != '') {
                            v2 = v2.toLowerCase();
                            var exp = /^.*\.(png|jpg|jpeg)$/i;
                            if (!(exp.test(v2))) {
                                // return 'Upload a valid  file.';
                                Ext.Msg.alert("Notification", "Upload a valid  file.");
                                return;
                            }
                            var assignment_associate_file = Ext.getCmp('assignment_associate_file').getValue();
                            if (assignment_associate_file == '') {
                                Ext.Msg.alert("Notification", "Please choose a file to upload");
                                return;
                            } else {
                                winLoadMask.show();
                                addAssignmentFiles('assignment_associate_file', upload);
                                return true;
                            }
                        }
                    }
                }, {
                    xtype: 'displayfield',
                    hidden: true,
                    style: {"padding": "5px"},
                    id: 'fileuploadedmessage',
                    name: 'fileuploadedmessage',
                    width: 200,
                    value: 'File uploaded....'
                }
            ], listeners: {
                afterrender: function () {
                    winLoadMask = new Ext.LoadMask(Ext.getCmp('assgmtFileWindows').getEl());
                    winLoadMask.msg = 'Please wait...';
                    var t = new Date();
                    var t_stamp = t.format("YmdHis");
                    Ext.getCmp('assignment_fileForm').getForm().load({
                        waitTitle: 'Please Wait',
                        waitMsg: 'Loading...',
                        url: modURL + '&op=getS3detailsforFiles',
                        params: {
                            apikey: _SESSION.apikey,
                            tstamp: t_stamp
                        }, success: function (form, action) {
                            var tmp = Ext.decode(action.response.responseText);
                            Ext.getCmp('fileuploadedmessage').hide();

                        }
                    });
                }
            }

        });
        return form;
    };
    function getFileType(t) {
        var type = "image";
        switch (t) {
            case "application/pdf":
                type = "pdf";
                break;
            case "video/mp4":
            case "video/quicktime":
                type = "video";
                break;
            case "image/png":
            case "image/jpg":
            case "image/jpeg":
                type = "image";
                break;
            case "audio/mpeg":
                type = "audio";
                break;
        }
        return type;
    }
    var addAssignmentFiles = function (id, upload) {
        var vcrresizer = Ext.getCmp('vcrresizer').getValue();
        var vcrbucket = Ext.getCmp('vcrbucket').getValue();
        var bucketRegion = Ext.getCmp('bucket_Region').getValue();
        var filepath = Ext.getCmp('oncompleteurl').getValue();
        AWS.config.update({
            region: bucketRegion,
            credentials: new AWS.Credentials(
                    Ext.getCmp('access_Key').getValue(),
                    Ext.getCmp('secret_Key').getValue(), null
                    )
        });
        var s3 = new AWS.S3({
            apiVersion: '2006-03-01',
            params: {Bucket: vcrbucket}
        });
        var files = document.getElementById(id + '-file').files;
        if (!files.length) {
            winLoadMask.hide();
            return alert('Please choose a file to upload first.');
        }
        var file = files[0];
        var actualfileName = file.name;
        var file_Name = JSON.stringify(actualfileName).slice(1, -1);
        var fileExt = file_Name.split('.').pop();
        var fileType = file.type;
        var mediaType = getFileType(fileType);
        var fileName = uuidv4();
        fileExt = fileExt.toLowerCase();
        fileName = fileName + '.' + fileExt;
        console.log('file', mediaType);
        s3.upload({
            Key: fileName,
            /*file_Name*/ /*from server*/
            Body: file,
            ACL: 'public-read',
            Bucket: vcrresizer, //lambdabucket
            ContentType: fileType,
            Metadata: {
                'filepath': filepath,
                'bucket': vcrbucket, //savebucket
                'fileType': fileType,
                'mediaType': mediaType
            }
        }, function (err, data) {

            if (err) {
                winLoadMask.hide();
                var img_src = Ext.BLANK_IMAGE_URL;
                Ext.getCmp('image_panel').update({'img_src': img_src}, {'type': id});
                return Ext.Msg.alert("Notification", 'There was an error uploading your document: ' + err.message);
            }
            if (!Ext.isEmpty(data.Location)) {
                winLoadMask.hide();
//                Ext.Msg.alert("Notification", 'File uploaded for' + id + '.');
//                Ext.Msg.alert('Notification', "File uploaded..", function (btn, text) {
//                    if (btn == 'ok') {
                Application.PrescriptionManagement.UploadedFileLocation = data.Location;
                Application.PrescriptionManagement.UploadedFileBucket = data.Bucket;
                Ext.getCmp('assignment_files').setValue(data.Location);
                Ext.getCmp('assignment_filename').setValue(fileName);
                Ext.getCmp('assignment_filetype').setValue(fileType);
                Ext.getCmp('fileuploadedmessage').show();

//                    }
//                });

            }
        });
    };
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                    v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    ;
    var preescriptionMedicineViewWindow = function (id, type, order_id, customerId) {
        var _addnewItemsWindow = new Ext.Window({
            title: 'Mapped Medicines',
            iconCls: 'dispatch',
            layout: 'fit',
            height: 500,
            width: winsize.width * 0.85,
            resizable: false,
            draggable: true,
            closable: true,
            bodyStyle: {"background-color": "white"},
            items: [_mappedMedicineDetailsGrid(id, type, order_id, customerId)]
        });
        _addnewItemsWindow.doLayout();
        _addnewItemsWindow.show();
        _addnewItemsWindow.center();
    };
    var _mappedMedicineDetailsGrid = function (id, type, order_id, customerId) {

        var spfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'stit_SKU'
                }]
        });
        spfilters.remote = true;
        spfilters.autoReload = true;
        var _mappedMedicineDetailsGridPanel = new Ext.grid.GridPanel({
            frame: true,
            border: false,
            loadMask: true,
            store: _mappedMedicineDetailsGridStore(id, type, order_id, customerId),
            iconCls: 'money',
            width: winsize.width * 0.8,
            height: 500,
            bodyStyle: {"background-color": "white"},
            id: 'gridpanelforMappedMedicinesinPrescription',
            plugins: [spfilters],
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Medicines',
                    dataIndex: 'stit_SKU',
                    sortable: true,
                    tooltip: 'Medicines',
                    hideable: false,
                    width: 100
                }, {
                    header: 'Expiry Date',
                    dataIndex: 'pmm_expirydate',
                    sortable: true,
                    tooltip: 'Expiry Date',
                    hideable: false,
                    width: 100

                }
            ],
            viewConfig: {
                forceFit: true,
            },
            listeners: {
                afterrender: function () {
                    Ext.getCmp('gridpanelforMappedMedicinesinPrescription').getStore().load({
                        params: {
                            customerId: customerId,
                            type: type,
                            id: id,
                            order_id: order_id
                        }
                    });
                }
            }
        });
        return _mappedMedicineDetailsGridPanel;
    };
    var _mappedMedicineDetailsGridStore = function (id, type, order_id, customerId) {
        var _Store = new Ext.data.JsonStore({
            method: 'post',
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=listMedicinesMapped',
                method: 'post'
            }),
            fields: ['pmm_id', 'cust_id', 'pmm_expirydate', 'stit_SKU'],
            totalProperty: 'totalCount',
            root: 'data',
            remoteSort: true,
            autoLoad: false,
            listeners: {
                beforeload: function (store, e) {
                    this.baseParams.customerId = customerId;
                    this.baseParams.type = type;
                    this.baseParams.id = id;
                    this.baseParams.order_id = order_id;
                }
            }
        });
        return _Store;
    };

    var holdPrescriptionPanel = function (id, _type) {
        var src = '?module=mypha_prescription';
        var panel = new Ext.Panel({
            frame: false,
            bodyStyle: {"background-color": "white"},
            hideBorders: true,
            layout: 'border',
            border: false,
            id: id,
            title: 'Adjourn Orders',
            items: [manageHoldOrderGrid(),
                new Ext.Panel({
                    region: 'east',
                    frame: false,
                    border: true,
                    layout: 'fit',
                    autoScroll: false,
                    title: 'Order Details',
                    bodyStyle: {"background-color": "white"},
                    id: 'order_canc_manage_parent_panel',
                    width: winsize.width * 0.45,
                    items: [{
                            id: 'order_hold_details_view_panel',
                            hidden: true,
                            html: '<iframe id="iframe_hold_orderdtls" name="iframe_hold_orderdtls"   frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%  src="' + src + '"; ></iframe>',
                        }]
                })
            ]
        });
        return panel;
    };
    var manageHoldOrderGrid = function () {

        var hfilters = new Ext.ux.grid.GridFilters({
            filters: [{
                    type: 'string',
                    dataIndex: 'order_generated_id'
                }, {
                    type: 'string',
                    dataIndex: 'order_order_id'
                }, {
                    type: 'date',
                    dataIndex: 'order_created_on'
                }, {
                    type: 'string',
                    dataIndex: 'delivery_to'
                }, {
                    type: 'string',
                    dataIndex: 'district'
                }, {
                    type: 'string',
                    dataIndex: 'pin'
                }, {
                    type: 'string',
                    dataIndex: 'cust_mobile'
                }, {
                    type: 'list',
                    options: ['Customer', 'BA'],
                    phpMode: true,
                    dataIndex: 'order_user_type'
                }, {
                    type: 'numeric',
                    dataIndex: 'order_total_amount'
                }, {
                    type: 'string',
                    dataIndex: 'order_status'
                }, {
                    type: 'string',
                    dataIndex: 'br_Name'
                }]
        });
        hfilters.remote = true;
        hfilters.autoReload = true;
        var order_store = manageHoldOrderStore();
        var grid = new Ext.grid.GridPanel({
            store: order_store,
            region: 'center',
            frame: true,
            layout: 'fit',
            width: winsize.width * 0.6,
            border: false,
            view: new Ext.grid.GroupingView({
                deferEmptyText: false,
                emptyText: '<div class="grid-data-empty"><div data-icon="/" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>',
                forceFit: true,
                groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
                getRowClass: function (record, index, params, store) {
                    if ((record.get('order_ReturnVerified') == 0) && (record.get('order_HasReturn') == 1))
                    {
                        return 'finascop_indicateColLIGHTORANGE';
                    } else {
                        return '';
                    }
                }
            }),
            plugins: [new Ext.ux.grid.GroupSummary(), hfilters],
            id: 'manage_order_hold_grid',
            columns: [new Ext.grid.RowNumberer(),
                {
                    header: 'Date',
                    sortable: true,
                    dataIndex: 'order_created_on',
                    tooltip: 'Date',
                    width: 150,
                    renderer: qtipRenderer
                }, {
                    header: 'Time',
                    sortable: true,
                    dataIndex: 'ordertime',
                    tooltip: 'Date',
                    width: 100,
                    renderer: qtipRenderer
                },
                {
                    header: 'Order ID',
                    sortable: true,
                    dataIndex: 'order_order_id',
                    tooltip: 'Order ID',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Branch',
                    sortable: true,
                    dataIndex: 'br_Name',
                    tooltip: 'Branch',
                    width: 200,
                    hideable: false,
                    renderer: qtipRenderer
                },
                {
                    header: 'Delivery To',
                    sortable: true,
                    dataIndex: 'delivery_to',
                    tooltip: 'Delivery To',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Delivery Mode',
                    sortable: true,
                    dataIndex: 'order_methodName',
                    tooltip: 'Delivery Mode',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Total Items',
                    sortable: true,
                    dataIndex: 'itemCount',
                    tooltip: 'Total Items',
                    width: 200,
                    renderer: qtipRenderer
                },
                {
                    header: 'Order Status',
                    sortable: true,
                    dataIndex: 'order_status',
                    tooltip: 'Order Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    header: 'Status',
                    sortable: true,
                    dataIndex: 'prescStatusName',
                    tooltip: 'Status',
                    width: 200,
                    renderer: qtipRenderer
                }, {
                    xtype: 'actioncolumn',
                    header: 'Action',
                    hideable: false,
                    sortable: false,
                    groupable: false,
                    tooltip: 'Action',
                    items: [{
                            iconCls: 'upload',
                            tooltip: 'Upload Prescription',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.store.getAt(rowIndex);
                                var order_id = record.data.order_id;
                                var customer_id = record.data.order_customer_id;
                                file_window(order_id, customer_id);
                            }
                        }
                    ]
                }
            ],
            viewConfig: {
                forceFit: true
            },
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true,
                listeners: {
                    selectionchange: ordergridSelectionChanged
                }
            }), tbar: [],
            listeners: {
                resize: onGridResize,
                afterrender: function () {
                }
            },
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: order_store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display"
            }),
            stripeRows: true
        });
        return grid;
    };
    var ordergridSelectionChanged = function (sm) {

        if (!Ext.isEmpty(Ext.getCmp('manage_order_hold_grid').getSelectionModel().getSelections())) {
            var ID = Ext.getCmp('manage_order_hold_grid').getSelectionModel().getSelections()[0].data.order_id;
            Application.PrescriptionManagement.Cache.orderAutoId = ID;
            Application.PrescriptionManagement.UpdateViewMode(ID);
        }
    };
    var qtipRenderer = function (value, metadata, record, rowIndex, colIndex, store) {
        metadata.attr = 'ext:qtip="' + value + '"  ext:qwidth="auto" ';
        return value;
    };
    var manageHoldOrderStore = function () {

        var store = new Ext.data.GroupingStore({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=getHoldOrders',
                method: 'post'
            }),
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                idProperty: 'order_id',
                root: 'data'
            }, ['order_id', 'order_order_id', 'order_packedbags_count', 'order_customer_id', 'order_branch_id', 'status_id', 'order_status', 'ordertime', 'delivery_to', 'order_created_on', 'itemCount', 'order_methodName',
                'quor_Type', 'dispatch_courier', 'reason', 'cancelled_by_type_name', 'cancelled_by_type', 'cancelled_by_name', 'cancelled_by_id', , 'dispatch_consignment', 'delivery_at_date', 'delivery_at_time',
                'delivery_notes', 'status', 'br_Name', 'order_HasReturn', 'order_ItemsReturned', 'order_ReturnVerified', 'quor_DeliveryMethodsAllowed', 'order_method', 'prescStatusId', 'prescStatusName'
            ]),
            sortInfo: {
                field: 'order_id',
                direction: "DESC"
            },
            groupField: '',
            groupDir: 'ASC',
            root: 'data',
            //    remoteSort: true,
            autoLoad: true,
            listeners: {
                beforeload: function () {

                },
                load: function (store, e) {
                    Ext.getCmp('manage_order_hold_grid').getView().refresh();
                    Ext.getCmp('manage_order_hold_grid').getSelectionModel().selectRow(0);
                    if (!Ext.isEmpty(Ext.getCmp('manage_order_hold_grid').getSelectionModel().getSelections())) {
                        var ID = Ext.getCmp('manage_order_hold_grid').getSelectionModel().getSelections()[0].data.order_id;
                        Application.PrescriptionManagement.Cache.orderAutoId = ID;
                        Application.PrescriptionManagement.UpdateViewMode(Application.PrescriptionManagement.Cache.orderAutoId);
                    }
                }
            }
        });
        return store;
    };
    return{
        Cache: {},
        initUVPrescription: function () {
            var _panelId = 'prescription_user_panel';
            var _type = 'NV';
            var _prescriptionUserPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_prescriptionUserPanel)) {
                _prescriptionUserPanel = unverifiedPrescriptionPanel(_panelId, _type);
                Application.UI.addTab(_prescriptionUserPanel);
                _prescriptionUserPanel.doLayout();
            } else {
                Application.UI.addTab(_prescriptionUserPanel);
                _prescriptionUserPanel.doLayout();
            }
        }, initVerifiedPrescription: function () {
            var _panelId = 'prescription_user_panel';
            var _type = 'Verified';
            var _prescriptionUserPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_prescriptionUserPanel)) {
                _prescriptionUserPanel = unverifiedPrescriptionPanel(_panelId, _type);
                Application.UI.addTab(_prescriptionUserPanel);
                _prescriptionUserPanel.doLayout();
            } else {
                Application.UI.addTab(_prescriptionUserPanel);
                _prescriptionUserPanel.doLayout();
            }
        }, initApprovedPrescription: function () {
            var _panelId = 'prescription_user_panel';
            var _type = 'Approved';
            var _prescriptionUserPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_prescriptionUserPanel)) {
                _prescriptionUserPanel = unverifiedPrescriptionPanel(_panelId, _type);
                Application.UI.addTab(_prescriptionUserPanel);
                _prescriptionUserPanel.doLayout();
            } else {
                Application.UI.addTab(_prescriptionUserPanel);
                _prescriptionUserPanel.doLayout();
            }
        }, pharmaWindow: function (type) {
            console.log('type', type);
            Ext.getCmp('precriptionMapWindow' + type).close();
            Ext.getCmp('gridpanelUserPrescription' + type).getStore().load();
        }, initQueuedPrescription: function () {
            var _panelId = 'prescription_user_panel';
            var _type = 'Queued';
            var _prescriptionUserPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_prescriptionUserPanel)) {
                _prescriptionUserPanel = queuedPrescriptionPanel(_panelId, _type);
                Application.UI.addTab(_prescriptionUserPanel);
                _prescriptionUserPanel.doLayout();
            } else {
                Application.UI.addTab(_prescriptionUserPanel);
                _prescriptionUserPanel.doLayout();
            }
        }, initHoldPrescription: function () {
            var _panelId = 'prescription_hold_panel';
            var _type = 'Hold';
            var _prescriptionHoldPanel = Ext.getCmp(_panelId);
            if (Ext.isEmpty(_prescriptionHoldPanel)) {
                _prescriptionHoldPanel = holdPrescriptionPanel(_panelId, _type);
                Application.UI.addTab(_prescriptionHoldPanel);
                _prescriptionHoldPanel.doLayout();
            } else {
                Application.UI.addTab(_prescriptionHoldPanel);
                _prescriptionHoldPanel.doLayout();
            }
        },
        UpdateViewMode: function () {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            var order_auto_id = arguments[0];
            Ext.getCmp('order_hold_details_view_panel').show();

            Ext.get('iframe_hold_orderdtls').dom.src = modURL + '&op=holdorder_details&order_auto_id=' + order_auto_id + "&apikey=" + _SESSION.apikey + "&tstamp=" + t_stamp;


        }
    }
}();