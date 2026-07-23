/*
 * Created by Lekshmi VS
 * On feb 23, 2017
 * */


Application.CSVUpload = function () {
    var modURL = '?module=csv_import';

    return {
        Cache: {},
        CSV_upload: function () {
            var win = new Ext.Window({
                layout: 'fit',
                width: 400,
                autoHeight: true,
                frame: false,
                border: false,
                title: 'Upload File',
                icon: './resources/images/default/icons/upload_fl.png',
                iconCls: 'upload',
                modal: true,
                shadow: false,
                floating: true,
                items: [
                    new Ext.form.FormPanel({
                        labelAlign: 'top',
                        labelSeparator: '',
                        bodyStyle: {
                            "background-color": "white",
                            "padding": "5px 5px 5px 10px"
                        },
                        autoHeight: true,
                        id: 'csv_form',
                        frame: false,
                        border: false,
                        fileUpload: true,
                        items: [
                            {
                                layout: 'form',
                                columnWidth: .3,
                                items: [{
                                        xtype: 'combo',
                                        displayField: 'name',
                                        valueField: 'id',
                                        mode: 'local',
                                        fieldLabel: 'Table',
                                        hiddenName: 'csv_table',
                                        id: 'csv_table',
                                        allowBlank: false,
                                        anchor: '98%',
                                        tabIndex: 9,
                                        triggerAction: 'all',
                                        lazyRender: true,
                                        store: new Ext.data.JsonStore(
                                                {
                                                    fields: ['id', 'name'],
                                                    data: [{
                                                            id: 'country',
                                                            name: 'country'
                                                        },
                                                        {
                                                            id: 'itemname',
                                                            name: 'itemname'
                                                        },
                                                        {
                                                            id: 'manufacture',
                                                            name: 'manufacture'
                                                        },
                                                        {
                                                            id: 'unit',
                                                            name: 'unit'
                                                        }, {
                                                            id: 'hsn',
                                                            name: 'hsn'
                                                        }, {
                                                            id: 'businesstype',
                                                            name: 'businesstype'
                                                        }, {
                                                            id: 'package',
                                                            name: 'package'
                                                        },
                                                        {
                                                            id: 'parentcategory',
                                                            name: 'parentcategory'
                                                        },
                                                        {
                                                            id: 'category',
                                                            name: 'category'
                                                        }, {
                                                            id: 'subcategory',
                                                            name: 'subcategory'
                                                        }, {
                                                            id: 'brand',
                                                            name: 'brand'
                                                        }
                                                    ]


                                                })
                                    }, {
                                        fieldLabel: 'Upload File',
                                        labelAlign: 'top',
                                        xtype: 'fileuploadfield',
                                        accept: '.csv',
                                        id: 'excel_file',
                                        allowBlank: false,
                                        name: 'excel_file',
                                        tabIndex: 1,
                                        msgTarget: 'under',
                                        anchor: '98%',
                                        validator: function (v) {
                                            if (v != '') {
                                                //var exp = /^.*.(.xlsx|xlsm|xls|xltx|xltm|xlt|xml|xlam|xla|xlw|csv)$/;
                                                var exp = /^.*\.csv$/;
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
                                            var table = Ext.getCmp('csv_table').getValue();
                                            if (csv_form.isValid()) {
                                                csv_form.submit({
                                                    url: modURL + '&op=uploadcsvFile',
                                                    waitTitle: 'Please Wait..',
                                                    waitMsg: 'Saving data...',
                                                    params: {
                                                        table: table,
                                                    },
                                                    success: function (csv_form, action) {
                                                        var result = Ext.decode(action.response.responseText);
                                                        if (result.valid === true && result.success === true) {
                                                            win.close();
                                                            Application.example.msg('Notification', 'Details saved Successfully. contentid' + result.contentid + '$dupvalues' + result.$dupvalues);
                                                        }
                                                    },
                                                    failure: function () {
                                                        Ext.Msg.alert('Error', 'Supplied CSV File could not be validated. ', function (btn) {
                                                            if (btn === 'ok') {
                                                                Ext.Msg.hide();
                                                                win.close();
                                                            }
                                                        });
                                                    }
                                                });
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
        }
    };
}();