
/**
 * This Function handles all the operations related to the CSV
 * 
 * @namespace Application
 * @package CSV Import
 * @copywrite (c) Saturn SPL 2011 - 2016
 */

Application.CSVImport = function ()
{
    var winsize = Ext.getBody().getViewSize();
    var recs_per_page = 18;
    var modURL = '?module=csv_import';

    var gridSelectionChanged = function (sm) {
        Application.CSVImport.current_rec = rec;
        /* if (!Ext.isEmpty(Ext.getCmp('mismatchGrid' + repId).getSelectionModel().getSelections())) {
         var ID = Ext.getCmp('mismatchGrid' + repId).getSelectionModel().getSelections()[0].data.work_sheet_id;
         Application.CSVImport.current_rec = ID;
         }*/
    };
    var csvUploadWindow = function (repId)
    {
        var win = new Ext.Window({
            layout: 'fit',
            width: 400,
            autoHeight: true,
            frame: true,
            title: 'Upload File',
            icon: './resources/images/default/icons/upload_fl.png',
            iconCls: 'upload',
            modal: true,
            shadow: false,
            floating: true,
            items: [
                new Ext.form.FormPanel({
                    labelAlign: 'top',
                    labelSeparator: '', bodyStyle: {"background-color": "white", "padding": "5px 5px 5px 10px"},
                    //width: winsize.width * 0.6,
                    autoHeight: true,
                    id: 'csv_form',
                    frame: true,
                    fileUpload: true,
                    items: [
                        {
                            layout: 'form',
                            columnWidth: .3,
                            items: [/*{
                             xtype: "hidden",
                             value: repId,
                             name: 'repId'
                             },*/
                                {
                                    fieldLabel: 'Upload File',
                                    labelAlign: 'top',
                                    xtype: 'fileuploadfield',
                                    accept: '.csv, .xlsx, .xls',
                                    id: 'excel_file',
                                    allowBlank: false,
                                    name: 'excel_file',
                                    tabIndex: 1,
                                    msgTarget: 'under',
                                    anchor: '98%',
                                    validator: function (v) {
                                        if (v != '') {
                                            //var exp = /^.*\.(xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                                            var exp = /^.*\.(csv|xlsx|xls)$/i;
                                            if (!(exp.test(v))) {
                                                return 'Upload a valid CSV file.';
                                            }
                                            return true;
                                        }
                                        var filen = Ext.getCmp('excel_file').getValue();
                                        if (filen == '') {
                                            return 'Upload a file.';
                                        }
                                    }
                                }],
                            buttons: [
                                {
                                    iconCls: 'csv',
                                    text: 'Upload',
                                    handler: function () {
                                        var csv_form = Ext.getCmp('csv_form').getForm();
                                        if (csv_form.isValid()) {

                                            SpreadSheetParser.parse(
                                                    'excel_file-file',
                                                    function (data) {
                                                        // if CSV
                                                        if (data.hasOwnProperty("sheets") === false) {
                                                            var options = {
                                                                "columns": SpreadSheetParser.getCols(data),
                                                                "conData": Application.CSVImport.cfg["ID" + repId],
                                                                "data": data
                                                            };
                                                            win.close();
                                                            buildCSVPanel(options);

                                                        } else {
                                                            // if XLSX/XLS
                                                            console.log(data.sheets);
                                                            // with the sheets, a combo need to be shown on the upload form
                                                            // and user has to choose which sheet to import from.

                                                            // here fetch the configuration for this import
                                                            // 
                                                        }
                                                    },
                                                    function (err) {
                                                        console.log(err);
                                                    }
                                            );

                                        }
                                    }
                                }]
                        }
                    ]
                })
            ]
        });
        win.show();
        win.doLayout();
        win.center();
    };


    var createGridStore = function (config, options)
    {
        return new Ext.data.JsonStore({
            fields: options.columns,
            data: options.data
        });
        
/*
        return new Ext.data.Store({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=csvload'
            }),
            root: 'data',
            fields: options.columns,
            totalProperty: 'totalCount',
            baseParams: {
                'rep_id': config.import_cfg_id,
            },
            reader: new Ext.data.JsonReader({
                fields: columns,
                totalProperty: 'totalCount',
                root: 'data'
            }),
            listeners: {
                load: function () {
                    checkSelections(config);
                }
            }
        });
*/
    };

    /*
     * Added By Lakshmi
     * For recalculating recs_per_page according to the screen size
     */
    var updatePagination = function (cmp) {
        recs_per_page = update_recs_per_page(cmp);
        var cmpPaginBar = cmp.getBottomToolbar();
        cmpPaginBar.pageSize = recs_per_page;
        cmpPaginBar.doLoad(0);
    };

    var updateMasters = function (e, repId) {
        var record = e;
        var parames = new Array();
        //Application.CSVImport.rowId = record; e= record.data
        Ext.MessageBox.confirm('Confirm', "Do you want to save the selected Item ?", function (btn) {
            if (btn === 'yes') {
                parames = buildParams(Application.CSVImport.gridColumns, record);
                //var parames = buildParams(Application.CSVImport.gridColumns, e);
                console.log(parames);
                var parsedJSON = Ext.encode(parames);
                if (record.data.id) {
                    var id = record.data.id;
                    //var id = Application.CSVImport.rowId;
                } else {
                    var id = 0;
                }
                Ext.Ajax.request({
                    waitMsg: 'Please wait...',
                    url: modURL + '&op=savemismatchChanges',
                    params: {
                        data: parsedJSON
                    }, success: function (response, options) {
                        var tmp = Ext.decode(response.responseText);
                        if (tmp.success == true && tmp.valid === true) {
                            Application.example.msg('Notification', tmp.msg);
                        } else {
                            Ext.MessageBox.alert('Notification', 'Save failed', function (btn) {
                            });
                        }
                    },
                    failure: function (response, options) {
                        Ext.MessageBox.alert('Notification', 'Save Failed');
                    }
                });


            }
        });
    };
    //Function for combo field in pagination toolbar
    function getSizeControlCombo(config) {
        var combo = new Ext.form.ComboBox({
            name: config.import_cfg_id + '_perpage',
            id: config.import_cfg_id + '_combo',
            width: 50,
            store: new Ext.data.ArrayStore({
                fields: ['id'],
                data: [['Auto'], ['20'], ['50'], ['100']]
            }),
            mode: 'local',
            listWidth: 40,
            triggerAction: 'all',
            displayField: 'id',
            valueField: 'id',
            editable: false,
            forceSelection: true,
            listeners: {
                select: function (combo, record) {
                    var grid = Ext.getCmp('importGrid' + config.import_cfg_id);
                    var bbar = grid.getBottomToolbar();
                    if (record.data.id === 'Auto') {
                        update_recs_per_page(grid);
                    } else {
                        bbar.pageSize = parseInt(record.get('id'), 10);
                    }
                    bbar.doLoad(0);
                },
                afterrender: function (combo) {
                    combo.setValue('Auto');
                }
            }
        });
        return combo;
    }
    var mstStore = function (mstColumn, mstTable) {
        return new Ext.data.ArrayStore({
            autoLoad: true,
            url: modURL + '&op=mstStore',
            method: 'post',
            baseParams: {
                mstColumn: mstColumn,
                mstTable: mstTable
            },
            fields: [mstColumn, mstColumn]
        });
    };

    var buildParams = function (colums, record) {
        var parames = [];
        for (var k = 0; k < colums.length; k++) {
            parames.push({
                name: colums[k],
                value: record.data[colums[k]]
                        //value: record[colums[k]]

                        //[colums[k]]=record.data[colums[k]];
            });
            //parames[colums[k]]=record.data[colums[k]];
        }
        return parames;
    };

    var buildColumns = function (fields, multiple, mstImpcolumn, mstColumn, mstTable) {
        //var count = Object.keys(config.import_cfg_db).length;
        var columns = [];
        for (var k = 0; k < fields.length; k++) {
            if (fields[k] !== 'ignore_row' && fields[k] !== 'id' && fields[k] !== 'insert_status') {
                if (fields[k] === mstImpcolumn) {
                    columns.push({
                        header: fields[k],
                        dataIndex: fields[k],
                        sortable: true,
                        width: 130,
                        id: fields[k],
                        editor: {
                            allowBlank: false,
                            xtype: 'combo',
                            id: fields[k],
                            name: fields[k],
                            editable: false,
                            displayField: mstColumn,
                            valueField: mstColumn,
                            mode: 'remote',
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender: true,
                            store: mstStore(mstColumn, mstTable),
                            listeners: {
                                select: function (combo) {
                                    var rec = Application.CSVImport.current_rec;
                                    rec.set(fields[k], combo.getValue());
                                    rec.commit();
                                    rec.endEdit();
                                    //console.log(Ext.getCmp('mismatchGrid' + Application.CSVImport.repId).getSelectionModel().getSelections()[0]);
                                    console.log(rec);
                                }
                            }
                        }
                    });
                } else {
                    columns.push({
                        header: fields[k],
                        dataIndex: fields[k],
                        sortable: true,
                        width: 130,
                        id: fields[k]
                    });
                }
            }
        }

        columns.push({
            xtype: 'actioncolumn',
            header: 'Action',
            width: 130,
            items: [
                {
                    getClass: function (v, meta, rec) {  // Or return a class from a function
                        if (rec.get('ignore_row') == '1') {
                            return 'cross';
                        } else {
                            return 'my-icon64';
                        }
                    },
                    tooltip: 'Change Status',
                    text: ' ',
                    align: 'center',
                    handler: function (grid, rowIndex, colIndex) {
                        var record = grid.store.getAt(rowIndex);
                        Ext.Ajax.request({
                            waitMsg: 'Removing..',
                            url: modURL + '&op=removeUnwanted',
                            params: {
                                id: record.get('id'),
                                ignore_row: record.get('ignore_row')
                            },
                            success: function (response, options) {

                                var result = Ext.decode(response.responseText);
                                if (result.success !== undefined && result.success === true) {
                                    Application.example.msg('Notification', result.msg);
                                    grid.getStore().load();
                                }
                            }
                        });
                    }
                }

            ]
        });
        return columns;
    };

    var createCSVGridPanel = function (config, repId, options)
    {
        // try {
        var store = createGridStore(config, options);
        var columns = buildColumns(options.columns);

        var gridConfig = {
            store: store,
            id: 'importGrid' + config.import_cfg_id,
            frame: true,
            columns: columns,
            renderer: function (value, meta, record, row, col, store) {
                return Ext.ux.renderer.ComboBoxRenderer({
                    value: value,
                    meta: meta,
                    record: record,
                    row: row,
                    col: col,
                    store: store,
                    combo: comboApplier,
                    displayField: 'id_display'
                });
            },
            viewConfig: {
                //forceFit: true,
                getRowClass: function (record, rowIndex, p, store) {
                    if (record.get('ignore_row') == "1") {
                        return 'gridstrike';
                    }
                    //   return '';
                }
            }/*,
            bbar: new Ext.PagingToolbar({
                pageSize: recs_per_page,
                store: store,
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: "No pages to display",
                items: ['-', 'Records Per Page: ', getSizeControlCombo(config), '-']
            })*/,
            listeners: {
                resize: function (cmp) {
                    /*to identify whether the grid taken for the first time or not
                     * if loading for the first time, this param will not be available & 
                     * initially data will not be loaded			
                     */
                    if (store.baseParams.loadGrid) {
                        updatePagination(cmp);
                    }
                },
                afterrender: function(){
                    setTimeout(function(){
                        checkSelections(config);
                    },250);
                }
            }
        };

        if (!Ext.isEmpty(config.cfg_build_json.GridConfig)) {
            Ext.apply(gridConfig, config.cfg_build_json.GridConfig);
        }


        var myGrid = new Ext.grid.GridPanel(gridConfig);
        return myGrid;
    };

    var loadGridStore = function (repId)
    {

        try {
//              var expid= Ext.getCmp('exportToExcelButton' + config.rep_id);
//expid.enable();
            var myGrid = Ext.getCmp('importGrid' + repId);
            var myForm = Ext.getCmp('csvForm' + repId);
            if (!Ext.isEmpty(myGrid)) {
                var myStore = myGrid.getStore();
                if (!Ext.isEmpty(myForm) && myForm.getForm().isValid()) {
                    myStore.baseParams = myForm.getForm().getValues();
                }
            }
            myStore.baseParams.rep_id = repId;
            /*set this param, to identify the grid id not taken for the first time*/
            myStore.baseParams.loadGrid = true;
            myStore.load({
                params: {
                    limit: recs_per_page,
                    start: 0
                }
            });
            //  Ext.getCmp('but_search').disable();


        } catch (ex) {
            Ext.Msg.alert('Button Error', 'Exceptions : ' + ex.message);
        }
    };

    var checkSelections = function (config) {
        var selcols = [];
        var cols = false;
        for (var i in config.import_cfg_db) {
            for (var j = 0; j < config.import_cfg_db[i].length; j++) {
                if (config.import_cfg_db[i][j][1] != "") {
                    var id = i + '-' + config.import_cfg_db[i][j][0];
                    var cmp = Ext.getCmp(id);
                    var col = cmp.getValue();
                    selcols.push(col);
                    if (cols === false) {
                        cols = cmp.getStore();
                    }
                }
            }
        }
        cols.each(function (x) {
            var col = x.get("value");
            Ext.each(Ext.DomQuery.jsSelect('.x-grid3-td-' + col), function (x) {
                if (selcols.indexOf(col) !== -1) {
                    Ext.get(x).addClass('c-importing');
                } else {
                    Ext.get(x).removeClass('c-importing');
                }

            });
        });
    }


    var setFormFields = function (config, columns, repId)
    {
        try {
            var fields = [], data = [];
            for (var k = 0; k < columns.length; k++) {
                if (columns[k] !== 'id' && columns[k] !== 'ignore_row' && columns[k] !== 'insert_status')
                    data.push(new Array(columns[k]));
            }

            var table;
            for (var i in config.import_cfg_db) {
                console.log(config.import_cfg_db);
                console.log(config.import_cfg_db[i].length);
                for (var j = 0; j < config.import_cfg_db[i].length; j++) {
                    table = i;
                    if (config.import_cfg_db[i][j][2] == 'true') {
                        var label = config.import_cfg_db[i][j][1] + '[Primary]';
                    } else {
                        var label = config.import_cfg_db[i][j][1];
                    }
                    var sortOrder = config.import_cfg_db[i][j][5];
                    Application.CSVImport.cx[i + '_' + config.import_cfg_db[i][j][0]] = "";
                    if (config.import_cfg_db[i][j][1] != "") {
                        fields[sortOrder] = ({
                            xtype: 'combo',
                            allowBlank: false,
                            fieldLabel: label,
                            store: new Ext.data.SimpleStore({
                                fields: ['value'],
                                data: data
                            }),
                            mode: 'local',
                            anchor: '98%',
                            viewConfig: {
                                deferEmptyText: false,
                                emptyText: '-'
                            },
                            displayField: 'value',
                            valueField: 'value',
                            triggerAction: 'all',
                            id: i + '-' + config.import_cfg_db[i][j][0],
                            hiddenName: i + '[' + config.import_cfg_db[i][j][0] + ']',
                            typeAhead: true,
                            minChars: 1,
                            msgTarget: 'under',
                            listeners: {
                                select: function (cmb, record, index) {
                                    checkSelections(config);
                                },
                                beforeselect: function (combo, r, index) {
                                    var values = Ext.getCmp('csvForm' + repId).getForm().getFieldValues();
                                    for (var i in values) {
                                        if (combo.getName() != i && values[i] == r.data.value)
                                        {
                                            Ext.Msg.alert('Notification', 'Already selected.');
                                            return false;
                                        }
                                    }
                                    return true;
                                },
                                change: function (cbo, value) {
                                    if (!Ext.isEmpty(value)) {
                                        var index = cbo.getStore().find('value', value);
                                        if (index == -1) {
                                            cbo.markInvalid('Select one from list');
                                        }
                                    } else {
                                        cbo.clearInvalid();
                                    }
                                }
                            }

                        });
                        /*fields.push({
                         xtype: 'combo',
                         allowBlank: false,
                         fieldLabel: label,
                         store: new Ext.data.SimpleStore({
                         fields: ['value'],
                         data: data
                         }),
                         mode: 'local',
                         anchor: '98%',
                         viewConfig: {
                         deferEmptyText: false,
                         emptyText: '-'
                         },
                         displayField: 'value',
                         valueField: 'value',
                         triggerAction: 'all',
                         id: i + '-' + config.import_cfg_db[i][j][0],
                         hiddenName: i + '[' + config.import_cfg_db[i][j][0] + ']',
                         typeAhead: true,
                         minChars: 1,
                         msgTarget: 'under',
                         listeners: {
                         select: function (cmb, record, index) {
                         checkSelections(config);
                         },
                         beforeselect: function (combo, r, index) {
                         var values = Ext.getCmp('csvForm' + repId).getForm().getFieldValues();
                         for (var i in values) {
                         if (combo.getName() != i && values[i] == r.data.value)
                         {
                         Ext.Msg.alert('Notification', 'Already selected.');
                         return false;
                         }
                         }
                         return true;
                         },
                         change: function (cbo, value) {
                         if (!Ext.isEmpty(value)) {
                         var index = cbo.getStore().find('value', value);
                         if (index == -1) {
                         cbo.markInvalid('Select one from list');
                         }
                         } else {
                         cbo.clearInvalid();
                         }
                         }
                         }
                         
                         });*/
                    }
                }
            }
            fields.push({xtype: 'hidden', id: table, name: 'table', value: table});

            return fields;

        } catch (ex) {
            Ext.Msg.alert('Field Error', 'Exceptions : ' + ex.message);
        }
    };

    var csvUploadForm = function (config, repId, columns)
    {
        var formConfig = {
            //defaults: {
            anchor: '98%',
            labelAlign: 'top',
            id: 'csvForm' + repId,
            frame: true,
            labelWidth: 100,
            autoScroll: true,
            items: [setFormFields(config, columns, repId)]
        };

        return new Ext.form.FormPanel(formConfig);
    };

    var mismatchGridStore = function (repId, dup, colums, mstTable, mstImpcolumn, mstColumn) {
        return new Ext.data.Store({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=mismatchload'
            }),
            root: 'data',
            fields: colums,
            totalProperty: 'totalCount',
            baseParams: {
                'rep_id': repId,
                'dup': Ext.encode(dup),
                'mstTable': mstTable,
                'mstImpcolumn': mstImpcolumn,
                'mstColumn': mstColumn
            },
            reader: new Ext.data.JsonReader({
                fields: colums,
                totalProperty: 'totalCount',
                root: 'data'
            }),
            listeners: {
                load: function () {


                    //Application.CSVImport.rowId = Ext.getCmp('mismatchGrid' + repId).getSelectionModel().getSelections()[0];
                    // Application.CSVImport.rowId = Ext.getCmp('mismatchGrid' + repId).selModel.getSelected();
                    //checkSelections(config);Ext.getCmp('schememain_grid').selModel.getSelected()
                },
                update: function (store, rec, operation) {
                    console.log(arguments);
                    if (operation === 'commit') {
                        updateMasters(rec, Application.CSVImport.repId);
                    }

                }
            }
        });
    };
    var dupGridStore = function (repId, dup, colums)
    {
        return new Ext.data.Store({
            proxy: new Ext.data.HttpProxy({
                url: modURL + '&op=dupload'
            }),
            root: 'data',
            fields: colums,
            totalProperty: 'totalCount',
            baseParams: {
                'rep_id': repId,
                'dup': Ext.encode(dup)
            },
            reader: new Ext.data.JsonReader({
                fields: colums,
                totalProperty: 'totalCount',
                root: 'data'
            }),
            listeners: {
                load: function () {
                    //checkSelections(config);
                }
            }
        });

    };

    function showMisMatch(repId, dup, colums, msg, multiple, mstTable, mstImpcolumn, mstColumn, columnCount) {

        var chk_select = new Ext.grid.CheckboxSelectionModel({
            singleSelect: true
        });

        var mismatchStore = mismatchGridStore(repId, dup, colums, mstTable, mstImpcolumn, mstColumn);
        var columns = buildColumns(colums, multiple, mstImpcolumn, mstColumn, mstTable);

        Application.CSVImport.gridColumns = colums;

        var editor = new Ext.ux.grid.RowEditor({
            saveText: 'Save',
            iconCls: 'csv_save',
            tabIndex: 12,
            errorSummary: false,
            listeners: {
                afteredit: function (cmp, val, record, row) {
                    var parames = buildParams(colums, record);
                    var parsedJSON = Ext.encode(parames);
                    if (record.data.id) {
                        var id = record.data.id;
                    } else {
                        var id = 0;
                    }
                    Ext.Ajax.request({
                        waitMsg: 'Please wait...',
                        url: modURL + '&op=savemismatchChanges',
                        params: {
                            data: parsedJSON
                        }, success: function (response, options) {
                            var tmp = Ext.decode(response.responseText);
                            if (tmp.success == true && tmp.valid === true) {
                                Application.example.msg('Notification', tmp.msg);
                            } else {
                                Ext.MessageBox.alert('Notification', 'Save failed', function (btn) {
                                });
                            }
                        },
                        failure: function (response, options) {
                            Ext.MessageBox.alert('Notification', 'Save Failed');
                        }

                    });
                },
                canceledit: function () {
                    //Ext.getCmp('mismatchGrid' + repId).getStore().remove(Ext.getCmp('mismatchGrid' + repId).getStore().getAt(0));
                }

            }
        });
        var writer = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true // write all fields, not just those that changed
        });
        //Ext.getCmp('mismatchGrid' + repId).store.add(record);
        var gridWidth = columnCount * 130;
        var winWidth = winsize.width * 0.95;
        //if (gridWidth > winWidth)
        //gridWidth = winWidth;
        var mismatch_win = new Ext.Window({
            title: msg,
            //autoWidth:true,
            width: winWidth,
            height: 400,
            layout: 'fit',
            items: [new Ext.Panel({
                    frame: true,
                    border: true,
                    autoScroll: true,
                    height: 390,
                    hideBorders: true,
                    //layout: 'fit',
                    items: [new Ext.grid.EditorGridPanel({
                            layout: 'fit',
                            frame: true,
                            autoScroll: true,
                            border: false,
                            width: gridWidth,
                            height: 400,
                            store: mismatchStore,
                            clicksToEdit: 2,
                            //plugins: [editor],
                            forceFit: false,
                            writer: writer,
                            id: 'mismatchGrid' + repId,
                            columns: columns,
                            viewConfig: {
                                forceFit: true,
                                getRowClass: function (record, rowIndex, p, store) {
                                    if (record.get('ignore_row') == "1") {
                                        return 'gridstrike';
                                    }
                                }
                            },
                            sm: new Ext.grid.RowSelectionModel({
                                singleSelect: true,
                                listeners: {
                                    selectionchange: function (sm) {
                                        //Application.CSVImport.current_rec = rec;
                                        if (!Ext.isEmpty(Ext.getCmp('mismatchGrid' + repId).getSelectionModel().getSelections())) {
                                            Application.CSVImport.repId = repId;
                                            Application.CSVImport.current_rec = Ext.getCmp('mismatchGrid' + repId).getSelectionModel().getSelections()[0];
                                        }
                                    }
                                }
                            }),
                            listeners: {
                                resize: function (cmp) {
                                    /*to identify whether the grid taken for the first time or not
                                     * if loading for the first time, this param will not be available & 
                                     * initially data will not be loaded			
                                     */
                                    if (mismatchStore.baseParams.loadGrid) {
                                        updatePagination(cmp);
                                    }
                                }, afteredit: function (e) {
                                    //updateMasters(e, repId);
                                }
                            }
                        })
                    ]
                })],
            buttons: [
                {
                    text: 'Save',
                    iconCls: 'csv_save',
                    tabIndex: 19,
                    width: 80,
                    handler: function () {
                        mismatch_win.close();
                        Ext.getCmp('importGrid' + repId).getStore().reload();
                        //showDuplicates(repId, dup, colums, msg);
                    }
                }, {
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    text: 'Cancel',
                    tabIndex: 20,
                    handler: function () {
                        mismatch_win.close();
                    }
                }
            ]

        });

        mismatch_win.show();
        var col = '';
        var length = columns.length;
        for (var i = 0; i < length; i++) {
            col = col + "{ name:'" + colums[i] + "'},";
        }
        var row = new Ext.data.Record.create(col);
        var record = new row(dup);
        Ext.getCmp('mismatchGrid' + repId).store.add(record);
        Ext.getCmp('mismatchGrid' + repId).getStore().load();
        mismatch_win.doLayout();
        mismatch_win.center();

    }
    ;

    function showDuplicates(repId, dup, colums, msg, multiple, columnCount) {
        var chk_select = new Ext.grid.CheckboxSelectionModel({
            singleSelect: true
        });
        if (multiple) {
            var dupStore = mismatchGridStore(repId, dup, colums);
            //var dupStore = mismatchGridStore(repId, dup, colums, mstTable, mstImpcolumn, mstColumn);
        } else {
            var dupStore = dupGridStore(repId, dup, colums);
        }
        var gridWidth = columnCount * 130;
        var winWidth = winsize.width * 0.95;
        var columns = buildColumns(colums);
        var dup_win = new Ext.Window({
            title: msg,
            width: winWidth,
            height: 400,
            layout: 'fit',
            items: [new Ext.Panel({
                    frame: true,
                    border: true,
                    autoScroll: true,
                    height: 390,
                    hideBorders: true,
                    //layout: 'fit',
                    items: [new Ext.grid.GridPanel({
                            layout: 'fit',
                            frame: true,
                            width: gridWidth,
                            autoScroll: true,
                            height: 400,
                            border: false,
                            store: dupStore,
                            autoHeight: true,
                            id: 'dupGrid' + repId,
                            columns: columns,
                            viewConfig: {
                                forceFit: true,
                                getRowClass: function (record, rowIndex, p, store) {
                                    if (record.get('ignore_row') == "1") {
                                        return 'gridstrike';
                                    }
                                }
                            },
                            listeners: {
                                resize: function (cmp) {
                                    /*to identify whether the grid taken for the first time or not
                                     * if loading for the first time, this param will not be available & 
                                     * initially data will not be loaded			
                                     */
                                    if (dupStore.baseParams.loadGrid) {
                                        updatePagination(cmp);
                                    }
                                }
                            }
                        })

                    ]
                })],
            buttons: [
                {
                    text: 'Save',
                    iconCls: 'csv_save',
                    tabIndex: 19,
                    width: 80,
                    handler: function () {
                        dup_win.close();
                        Ext.getCmp('importGrid' + repId).getStore().reload();
                    }
                }, {
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    text: 'Cancel',
                    tabIndex: 20,
                    handler: function () {
                        dup_win.close();
                    }
                }
            ]
        });
        dup_win.show();
        Ext.getCmp('dupGrid' + repId).getStore().load();
        dup_win.doLayout();
        dup_win.center();
    }
    ;

    var buildCSVPanel = function (result)
    {
        /*try*/ {
            // var result = Ext.decode(res.responseText);
            if (result && result.columns && result.conData.import_cfg_id) {
                var config = result.conData;
                var columns = result.columns;
                if (config.cfg_build_json) {
                    config.cfg_build_json = Ext.decode(config.cfg_build_json);
                }
                if (config.import_cfg_db) {
                    config.import_cfg_db = Ext.decode(config.import_cfg_db);
                }
                var repId = config.import_cfg_id;
                var importGrid = createCSVGridPanel(config, repId, result);
                var filterForm = csvUploadForm(config, repId, columns);

                var formPanelConfig = {
                    region: 'north',
                    title: 'Mapping Form',
                    layout: 'form',
                    collapsible: false,
                    split: true,
                    items: [filterForm],
                    buttons: [{
                            text: 'Apply',
                            iconCls: 'csv_apply',
                            scope: filterForm,
                            handler: function () {
                                /*if (!isAllComboValid(Ext.getCmp('csvForm' + repId))) {
                                 return;
                                 }*/
                                if (Ext.getCmp('csvForm' + repId).getForm().isValid()) {
                                    Ext.getCmp('csvForm' + repId).getForm().submit({
                                        url: modURL + '&op=checkDuplicate',
                                        waitMsg: 'Loading...',
                                        params: {
                                            repId: repId
                                        },
                                        success: function (response, action) {
                                            var result = Ext.decode(action.response.responseText);
                                            if (result.success == true && result.valid == true) {
                                                Application.example.msg('Success', result.msg);
                                                if (result.msg == 'Data Imported Successfully.') {
                                                    var parent_tab = Ext.getCmp('csvImportPanel' + repId).findParentByType('tabpanel').id;
                                                    Ext.getCmp(parent_tab).getActiveTab().destroy();
                                                }


                                                //}
                                            } else {
                                                console.log('hi' + result);
                                                var dupEntry = result.data;
                                                var colums = result.columns;
                                                var msg = result.msg;
                                                var multiple = result.multiple;
                                                var mstTable = result.mstTable;
                                                var mstImpcolumn = result.mstImpcolumn;
                                                var mstColumn = result.mstColumn;
                                                var columnCount = colums.length;

                                                if (multiple == true) {
                                                    showMisMatch(repId, dupEntry, colums, msg, multiple, mstTable, mstImpcolumn, mstColumn, columnCount);
                                                } else {
                                                    showDuplicates(repId, dupEntry, colums, msg, multiple, columnCount);
                                                }

                                            }
                                        }, failure: function (response, action) {
                                            var result = Ext.decode(action.response.responseText);
                                            // Application.example.msg('Notification', result.msg);
                                            Ext.Msg.show({
                                                title: 'Notification',
                                                msg: "Please Download the Csv contain un inserted rows",
                                                buttons: Ext.MessageBox.YESNO,
                                                fn: function (btn) {
                                                    if (btn == 'yes') {
                                                        /* var url = document.URL +  _SESSION.error_path;
                                                         window.open(url, '_blank');*/
                                                        postToUrl(modURL + '&op=export');
                                                        var parent_tab = Ext.getCmp('csvImportPanel' + repId).findParentByType('tabpanel').id;
                                                        Ext.getCmp(parent_tab).getActiveTab().destroy();
                                                    }
                                                }
                                            });
                                            if (result.msg == 'No data to import') {
                                                var parent_tab = Ext.getCmp('csvImportPanel' + repId).findParentByType('tabpanel').id;
                                                Ext.getCmp(parent_tab).getActiveTab().destroy();
                                            }
                                        }
                                    });
                                }

                                //console.log([arguments, this]);
                            }
                        }, {text: 'Reset',
                            iconCls: 'csv_reset',
                            scope: filterForm,
                            handler: function () {
                                Ext.getCmp('csvForm' + repId).getForm().reset();
                            }
                        }]
                };

                if (!Ext.isEmpty(config.cfg_build_json.FormPanelConfig)) {
                    Ext.apply(formPanelConfig, config.cfg_build_json.FormPanelConfig);
                }

                if (Ext.isEmpty(formPanelConfig.width) && Ext.isEmpty(formPanelConfig.height)) {
                    formPanelConfig.autoHeight = true;
                }
                var panelConfig = {
                    id: 'csvImportPanel' + repId,
                    layout: 'border',
                    frame: true,
                    defaults: {
                        forceLayout: true
                    },
                    //anchor:'100% 99%',
                    items: [formPanelConfig, new Ext.Panel({
                            region: 'center',
                            layout: 'fit',
                            hideBorders: true,
                            items: importGrid
                        })]
                };

                if (!Ext.isEmpty(config.cfg_build_json.Panel)) {
                    Ext.apply(panelConfig, config.cfg_build_json.Panel);
                }

                var csvImportPanel = new Ext.Panel(panelConfig);
                setUpGrid(csvImportPanel, repId);
            } else {
                Ext.Msg.alert('Config Error', 'Invalid configuration');
            }
        }/* catch(ex) {
         Ext.Msg.alert('Build Error', 'Build Exceptions : ' + ex.message);
         }*/
    };

    var setUpGrid = function (csvImportPanel, repId)
    {
        try {
            Application.UI.addTab(csvImportPanel);
            csvImportPanel.doLayout();
            //loadGridStore(repId);
        } catch (ex) {
            Ext.Msg.alert('SetUp Error', 'Build Exceptions : ' + ex.message);
        }
    };

    var getImportCfg = function (repId) {
        if (Application.CSVImport.cfg.hasOwnProperty("ID" + repId)) {
            csvUploadWindow(repId);
        } else {
            Ext.Ajax.request({
                waitMsg: 'Please wait...',
                url: modURL + '&op=build_config',
                method: "post",
                params: {
                    repId: repId
                }, success: function (response, options) {
                    var tmp = Ext.decode(response.responseText);
                    if (tmp.success == true && tmp.valid === true) {
                        Application.CSVImport.cfg["ID" + repId] = tmp.conData;
                        csvUploadWindow(repId);
                    } else {
                        Ext.MessageBox.alert('Notification', 'Failed getting Import Configuration', function (btn) {
                        });
                    }
                },
                failure: function (response, options) {
                    Ext.MessageBox.alert('Notification', 'Failed getting Import Configuration');
                }
            });

        }
    }

    return {
        cx: {},
        cfg: {},
        csv: function (repId) {
            try {
                getImportCfg(repId);
            } catch (ex) {
                Ext.Msg.alert('Exception', 'Report Exception : ' + ex.message);
            }

        }

    };
}();