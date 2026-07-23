/* global Ext, Application, _SESSION */

Application.Finascop_DataEntry = function () {

	var recs_per_page = 12;
	var modURL = '?module=finascop_data_entry';
	var winLoadMask;
	var WinMask;
	function updatePagination(cmp) {
		recs_per_page = finascop_update_recs_per_page(cmp);
	}

	var dataEntryStore = function () {
		var store = new Ext.data.JsonStore({
			method: 'post',
			proxy: new Ext.data.HttpProxy({
				url: modURL + '&op=listDataEntries',
				method: 'post'
			}),
			fields: ['acet_NO', 'acet_DocNO', 'acetDate', 'acet_Amount', 'type_name', 'cmp_status', 'accounts_ip', 'particular_ip', {name: 'updated_on', type: 'string'}, 'narration', 'acet_Status', 'acet_IsRebutted', 'HasImage'],
			totalProperty: 'totalCount',
			root: 'data',
			remoteSort: true,
			autoLoad: false
		});
		store.setDefaultSort('acetDate', 'DESC');
		return store;
	};
	var tooltipRenderer = function (value, meta, record, rowindx, colindx, store) {
		meta.attr = 'ext:qtip="' + record.get("narration") + '"';
		return value;
	};
	var imageAttachedRenderer = function (value, meta, record, rowindx, colindx, store) {
		if (record.get('HasImage') == '1')
		{
			meta.css = 'finascop_my-icon54';
		} else
		{
			meta.css = 'finascop_my-icon55';
		}
		meta.attr = 'ext:qtip="' + record.get("narration") + '"';
		return value;
	};
	var loadDataEntry = function () {

		Ext.getCmp('data_entry_main_panel').getStore().removeAll();
		Ext.getCmp('data_entry_main_panel').getStore().load({
			params: {
				start: 0,
				limit: recs_per_page
			}
		});
	}


	var dataEntryGrid = function (id) {
		var data_entry_store = dataEntryStore();
		var data_entry_filter = new Ext.ux.grid.GridFilters({
			remote: true,
			filters: [{
					type: 'date',
					dataIndex: 'acetDate'
				}, {
					type: 'string',
					dataIndex: 'acet_DocNO'
				}, {
					type: 'list',
					dataIndex: 'type_name',
					options: ['Receipt', 'Payment', 'Journal Voucher', 'Contra Entry'],
					phpMode: true
				}, {
					type: 'string',
					dataIndex: 'accounts_ip'
				},  {
					type: 'string',
					dataIndex: 'particular_ip'
				},{
					type: 'numeric',
					dataIndex: 'acet_Amount'
				}]
		});
		data_entry_filter.remote = true;
		data_entry_filter.autoReload = true;
		var grid_panel = new Ext.grid.GridPanel({
			store: data_entry_store,
			layout: 'fit',
			frame: false,
			border: false,
			plugins: [data_entry_filter],
			id: id,
			title: 'Data Entry',
			//iconCls: 'finascop_dataentry',
			loadMask: true,
			columns: [new Ext.grid.RowNumberer(),
				{
					header: 'Date',
					sortable: true,
					dataIndex: 'acetDate',
					tooltip: 'Date',
					renderer: tooltipRenderer
				}, {
					header: 'Ref. No.',
					sortable: true,
					dataIndex: 'acet_DocNO',
					renderer: imageAttachedRenderer
				}, {
					header: 'Type',
					sortable: true,
					dataIndex: 'type_name',
					renderer: tooltipRenderer
				}, {
					header: 'Accounts',
					sortable: true,
					dataIndex: 'accounts_ip',
					renderer: tooltipRenderer
				}, {
					header: 'Particular',
					sortable: true,
					dataIndex: 'particular_ip',
					renderer: tooltipRenderer
				}, {
					header: 'Amount',
					sortable: true,
					xtype: 'finascopcurrency',
					format: FINASCOP_CURRENCY_FORMAT,
					dataIndex: 'acet_Amount',
					renderer: tooltipRenderer,
					align: 'right'
				}, {
					header: 'Updated_on',
					dataIndex: 'updated_on',
					align: 'right',
					hideable: false,
					hidden: true
				}, {
					header: 'Narration',
					dataIndex: 'narration',
					hideable: false,
					hidden: true
				}, {
					xtype: 'actioncolumn',
					header: 'Action',
					hideable: false,
					sortable: false,
					groupable: false,
					tooltip: 'Action',
					items: [{
							getClass: function (v, meta, rec) {
								var show = 0;
								if (rec.get('type_name') == 'Receipt')
								{
									/*     <?php if (user_access("finascop_data_entry", "de_receipt")) { ?> */
									show = 1;
									/*<?php } ?> */
								}
								if (rec.get('type_name') == 'Payment')
								{
									/*     <?php if (user_access("finascop_data_entry", "de_payment")) { ?> */
									show = 1;
									/*<?php } ?> */
								}
								if (rec.get('type_name') == 'Journal Voucher')
								{
									/*     <?php if (user_access("finascop_data_entry", "de_jv")) { ?> */
									show = 1;
									/*<?php } ?> */
								}
								if (rec.get('type_name') == 'Contra Entry')
								{
									/*     <?php if (user_access("finascop_data_entry", "de_cntr_entry")) { ?> */
									show = 1;
									/*<?php } ?> */
								}
								if (show == 1 && (rec.get('acet_Status') == '0' || rec.get('acet_Status') == '5'))
								{
									this.items[0].tooltip = 'Edit Details';
									return 'finascop_edit';
								} else
								{
									return 'finascop_hideicon';
								}

							},
							handler: function (grid, rowIndex, colIndex) {
								var record = grid.store.getAt(rowIndex);
								var t = new Date();
								var t_stamp = t.format("YmdHis");
								winLoadMask = new Ext.LoadMask(grid.getEl());
								winLoadMask.msg = 'Please wait...';
								winLoadMask.show();
								Application.Finascop_DataEntry.FileEdited = false;
								Ext.Ajax.request({
									url: modURL + '&op=getDetails',
									method: 'POST',
									params: {
										acet_NO: record.get('acet_NO'),
										type: record.get('type_name'),
										updated_on: record.get('updated_on')
									},
									success: function (response) {
										var tmp = Ext.decode(response.responseText);
										winLoadMask.hide();
										if (tmp.success === true)
										{
											var data = tmp.data;
											Application.Finascop_DataEntry.receipt_voucher(record.get('acet_NO'), record.get('type_name'), record.get('updated_on'), data, record.get('acet_DocNO'));
											if (data.imgsrc == "")
											{
												Ext.getCmp('image_panel').update({'img_src': Ext.BLANK_IMAGE_URL});
											} else
											{
												Ext.getCmp('image_panel').update({'img_src': data.imgsrc});
											}

											Application.Finascop_DataEntry.UploadedFileLocation = data.imgsrc;
											Application.Finascop_DataEntry.UploadedFileBucket = data.AWSBucket;
											Ext.getCmp('aws_file_bucket').setValue(data.AWSBucket);
											Ext.getCmp('aws_file_location').setValue(data.imgsrc);
										} else
										{
											//Ext.Msg.alert('Notification', tmp.msg);

											Ext.Msg.alert('Notification', tmp.msg, function (btn, text) {

												Ext.getCmp('data_entry_main_panel').getStore().reload();
												loadDataEntry();
											});
										}
									},
									failure: function (elm, conf) {
										winLoadMask.hide();
										var result = Ext.decode(conf.response.responseText);
										Ext.Msg.alert('Notification', result.msg);
									}
								});
							}
						},
						{

							getClass: function (v, meta, rec) {

								return 'finascop_print';
							},
							tooltip: 'Generate PDF',

							handler: function (grid, rowIndex, colIndex) {
								var record = grid.store.getAt(rowIndex);
								var t = new Date();
								var t_stamp = t.format("YmdHis");
								var acet_NO = record.get('acet_NO');
								var type = record.get('type_name');
								var url =
										modURL + '&op=generatepdf&acet_NO=' +
										acet_NO + '&type=' +
										type + '&apikey=' + _SESSION.apikey + '&tstamp=' + t_stamp;

								Ext.get('downloadIframe').dom.src = url;


							}
						}
					]
				}],
			viewConfig: {
				forceFit: true,
				getRowClass: function (record, index) {

					if (record.data.acet_Status == 5)
					{
						return 'finascop_indicateColPINK ';
					} else
					{
						return '';
					}


				},
				deferEmptyText: false,
				emptyText: '<div class="grid-data-empty"><div data-icon="" class="empty-grid-icon"></div><div class="empty-grid-headline"></div><div class="empty-grid-byline">There are no records to show you right now.</div></div>'

			},
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true
			}),
			tbar: ['-'/* <?php if (user_access("finascop_data_entry", "de_receipt")) { ?> */, {
					xtype: 'button',
					text: 'Creat Receipt',
                                        tooltip: 'Creat Receipt',
					iconCls: 'finascop_add',
					handler: function () {
						if (_SESSION.AssignedBranchCount == 0)
						{
							Ext.Msg.alert('Notification', 'You do not have any company/branch assigned');
						} else if (_SESSION.finascop_current_company_isactive == '0' || _SESSION.finascop_current_branch_isactive == '0')
						{
							var strg = (_SESSION.finascop_current_company_isactive == '0' ? 'Your selected company ' + _SESSION.finascop_current_company : 'Your selected branch ' + _SESSION.current_branch) + ' is disabled';
							Ext.Msg.alert('Notification', strg);
						} else
						{
							Application.Finascop_DataEntry.receipt_voucher(0, 'Receipt');
						}
					}
				}, '-'/* <?php } ?> */
						/* <?php if (user_access("finascop_data_entry", "de_payment")) { ?> */
						, {
							xtype: 'button',
							text: 'Create Payment',
                                                        tooltip: 'Create Payment',
							iconCls: 'finascop_add',
							handler: function () {
								if (_SESSION.AssignedBranchCount == 0)
								{
									Ext.Msg.alert('Notification', 'You do not have any company/branch assigned');
								} else if (_SESSION.finascop_current_company_isactive == '0' || _SESSION.finascop_current_branch_isactive == '0')
								{
									var strg = (_SESSION.finascop_current_company_isactive == '0' ? 'Your selected company ' + _SESSION.finascop_current_company : 'Your selected branch ' + _SESSION.current_branch) + ' is disabled';
									Ext.Msg.alert('Notification', strg);
								} else
								{
									Application.Finascop_DataEntry.receipt_voucher(0, 'Payment');
								}
							}
						}, '-'
						/* <?php } ?> */
						/* <?php if (user_access("finascop_data_entry", "de_jv")) { ?> */
						, {
							xtype: 'button',
							text: 'Create Journal Voucher',
                                                        tooltip: 'Create Journal Voucher',
							iconCls: 'finascop_add',
							handler: function () {
								if (_SESSION.AssignedBranchCount == 0)
								{
									Ext.Msg.alert('Notification', 'You do not have any company/branch assigned');
								} else if (_SESSION.finascop_current_company_isactive == '0' || _SESSION.finascop_current_branch_isactive == '0')
								{
									var strg = (_SESSION.finascop_current_company_isactive == '0' ? 'Your selected company ' + _SESSION.finascop_current_company : 'Your selected branch ' + _SESSION.current_branch) + ' is disabled';
									Ext.Msg.alert('Notification', strg);
								} else
								{
									Application.Finascop_DataEntry.receipt_voucher(0, 'Journal Voucher');
								}
							}
						}, '-'
						/* <?php } ?> */
						/* <?php if (user_access("finascop_data_entry", "de_cntr_entry")) { ?> */
						, {
							xtype: 'button',
							text: 'Create Contra Entry',
                                                        tooltip: 'Create Contra Entry',
							iconCls: 'finascop_add',
							handler: function () {
								if (_SESSION.AssignedBranchCount == 0)
								{
									Ext.Msg.alert('Notification', 'You do not have any company/branch assigned');
								} else if (_SESSION.finascop_current_company_isactive == '0' || _SESSION.finascop_current_branch_isactive == '0')
								{
									var strg = (_SESSION.finascop_current_company_isactive == '0' ? 'Your selected company ' + _SESSION.finascop_current_company : 'Your selected branch ' + _SESSION.current_branch) + ' is disabled';
									Ext.Msg.alert('Notification', strg);
								} else
								{
									Application.Finascop_DataEntry.receipt_voucher(0, 'Contra Entry');
								}
							}
						}, '-'  /* <?php } ?> */],
			bbar: new Ext.PagingToolbar({
				pageSize: recs_per_page,
				store: data_entry_store,
				displayInfo: true,
				displayMsg: 'Displaying records {0} - {1} of {2}',
				emptyMsg: "No records to display",
				plugins: [data_entry_filter],
				items: [
					'-',
					{
						html: '<div class="finascop_color_wr"><div class="finascop_color-light-red_small"></div><div class="finascop_text_c"> Rebutted </div></div>'

					},
					'-'
				]
			}),
			stripeRows: true,
			autoExpandColumn: 'data_entry_name_auto_exp'
		});
		data_entry_store.load();
		return grid_panel;
	};



	var particularDetails = function (type) {
		var particular_store = particularStore();
		var hide = (type == 'Journal Voucher') ? false : true;
		return new Ext.Panel({
			border: false,
			region: 'center',
			layout: "border",
			defaults: {
				border: false,
				hideBorders: true
			},
			items: [
				new Ext.Panel({
					layout: "column",
					region: 'north',
					border: false,
					autoHeight: true,
					hideBorders: true,
					items: [{
							layout: 'form',
							columnWidth: 0.35,
							style: 'margin:10px 0 5px 0px;',
							labelWidth: 55,
							labelAlign: 'left',
							border: false,
							hideBorders: true,
							items: {
								xtype: 'datefield',
								id: 'receipt_date',
								anchor: '99%',
								labelAlign: 'left',
								name: 'receipt_date',
								format: 'd-m-Y',
								fieldLabel: 'Date',
								//selectOnFocus: true,
								maxValue: new Date(),
								value: new Date(),
								listeners: {
									afterrender: function (field) {
										Ext.defer(function () {
											field.focus(true, 100);
										}, 1);
									},
									specialkey: function (field, e) {
										if (e.getKey() == e.ENTER)
										{
											if (type == 'Journal Voucher')
											{
												Ext.getCmp('receipt_account').focus();
											} else
											{
												Ext.getCmp('receipt_account').focus();
											}


										}
									}
								}

							}
						}, {
							layout: 'form',
							columnWidth: 0.65,
							style: 'margin:10px 0 5px 5px;',
							labelWidth: 55,
							labelAlign: 'left',
							border: false,
							hidden: hide,
							hideBorders: true,
							items: [{
									xtype: 'radiogroup',
									id: 'ctr_dtr_type',
									labelAlign: 'left',
									items: [
										{boxLabel: 'Debtor', name: 'account_type', inputValue: 'Debtor', id: 'account_type1'}, {boxLabel: 'Creditor', name: 'account_type', inputValue: 'Creditor', id: 'account_type2'}
									],
									listeners: {
										change: function () {
											Ext.getCmp('receipt_account').enable();
										},
										specialkey: function (field, e) {
											if (e.getKey() == e.ENTER)
											{
												Ext.getCmp('receipt_account').focus();
											}
										}
									}
								}, {
									xtype: 'hidden',
									id: 'uploaded_file_name',
									name: 'uploaded_file_name'
								}, {
									xtype: 'hidden',
									id: 'file_up_id',
									name: 'file_up_id'
								}, {
									xtype: 'hidden',
									id: 'ledger_type_selected',
									name: 'ledger_type_selected'
								}, {
									xtype: 'hidden',
									id: 'acet_NO',
									name: 'acet_NO'
								}, {
									xtype: 'hidden',
									id: 'acet_DocNO',
									name: 'acet_DocNO'
								}, {
									xtype: 'hidden',
									id: 'id_updatedon',
									name: 'updatedon'
								}]
						}, {
							layout: 'form',
							columnWidth: 0.65,
							style: 'margin:10px 0 5px 5px;',
							labelWidth: 55,
							labelAlign: 'left',
							border: false,
							hideBorders: true, items: [{
									xtype: 'combo',
									fieldLabel: 'Account',
									labelAlign: 'left',
									name: 'receipt_account_name',
									id: 'receipt_account',
									anchor: "98%",
									store: accountComboStore(type), /*'accled_LedgerName', 'accled_Ledger_Id',*/
									mode: 'remote',
									hiddenName: 'receipt_account',
									displayField: 'ledgertypename',
									submitValue: true,
									valueField: 'accled_Ledger_Id',
									triggerAction: 'all',
									allowBlank: false,
									forceSelection: true,
									editable: true,
									typeAhead: true,
									minChars: 1,
									listeners: {
										change: function (cbo, value) {
											if (!Ext.isEmpty(value))
											{
												var index = cbo.getStore().find('accled_Ledger_Id', value);
												if (index > -1)
												{
													var rec = cbo.getStore().getAt(index);
													var Group_ID = rec.get('Group_ID');
													var led_type = 0
													if (Group_ID == 1 && type == 'Receipt')
														led_type = 1;
													else if (Group_ID == 2 && type == 'Receipt')
														led_type = 3;
													else if (Group_ID == 1 && type == 'Payment')
														led_type = 2;
													else if (Group_ID == 2 && type == 'Payment')
														led_type = 4;
													else if (type == 'Journal Voucher')
													{
														led_type = 5;
													} else if (type == 'Contra Entry')
													{
														led_type = 6;
													}
													Ext.getCmp('ledger_type_selected').setValue(led_type);
												}
											}
										},
										select: function (cbo) {

											var value = cbo.getValue();
											if (!Ext.isEmpty(value))
											{

												if (Application.Finascop_DataEntry.AddEdit == 'Edit')
												{
													Ext.getCmp('particular_grid').getStore().removeAll();
													Ext.getCmp('receipt_total_amount').setValue(0);
												}

												var index = cbo.getStore().find('accled_Ledger_Id', value);
												if (index > -1)
												{
													var rec = cbo.getStore().getAt(index);
													var Group_ID = rec.get('Group_ID');
													var led_type = 0
													if (Group_ID == 1 && type == 'Receipt')
														led_type = 1;
													else if (Group_ID == 2 && type == 'Receipt')
														led_type = 3;
													else if (Group_ID == 1 && type == 'Payment')
														led_type = 2;
													else if (Group_ID == 2 && type == 'Payment')
														led_type = 4;
													else if (type == 'Journal Voucher')
													{
														led_type = 5;
													} else if (type == 'Contra Entry')
													{
														led_type = 6;
													}
													Ext.getCmp('ledger_type_selected').setValue(led_type);
													if (type == 'Contra Entry')
													{
														var receipt_particular = Ext.getCmp('receipt_particular');
														var receipt_particular_store = receipt_particular.getStore();
														receipt_particular.reset();
														receipt_particular_store.removeAll();
														receipt_particular_store.baseParams.Group_ID = Group_ID;
														receipt_particular_store.baseParams.selectedAccounts = this.getValue();
														receipt_particular_store.load();
													}

												}
											}
										},
										specialkey: function (field, e) {
											if (e.getKey() == e.ENTER)
											{
												Ext.getCmp('receipt_particular').focus();
											}
										}

									}
								}]
						}]
				}),
				new Ext.grid.GridPanel({
					store: particular_store,
					//   height: 360,
					region: 'center',
					layout: 'fit',
					frame: false,
					border: false,
					loadMask: true,
					id: 'particular_grid',
					columns: [new Ext.grid.RowNumberer(),
						{header: 'Particular',
							id: 'particular_auto_exp',
							sortable: true,
							hideable: false,
							dataIndex: 'particular_name',
							width: 65
						}, {
							header: 'Amount',
							sortable: true,
							xtype: 'finascopcurrency',
							format: FINASCOP_CURRENCY_FORMAT,
							dataIndex: 'amount',
							align: 'right',
							width: 25
						}, {
							header: "Action",
							xtype: 'actioncolumn',
							width: 10,
							items: [{
									text: 'Delete',
									iconCls: 'finascop_disable_settings',
									icon: IMAGE_BASE_PATH + "/default/icons/finascop_disable_settings.png",
									handler: function (grid, rowIndex, colIndex) {
										Ext.Msg.confirm('Notification', 'Do you want to remove this?', function (btn) {
											if (btn == 'yes')
											{
                                                                                                Application.example.msg('Success', 'Removed item');
												var record = grid.store.getAt(rowIndex);
												grid.store.removeAt(rowIndex);
												grid.getView().refresh();
												Ext.getCmp('receipt_total_amount').setValue(grid.store.sum('amount'));
												if (type == 'Journal Voucher' && grid.store.getCount() == 0)
												{
													Ext.getCmp('ctr_dtr_type').enable();
												}
											}
										});
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
					tbar: [{html: '&nbsp;Particular : &nbsp;'}, {xtype: 'combo',
							name: 'receipt_particular',
							id: 'receipt_particular',
							width: 230,
							store: particularComboStore(type),
							mode: 'remote',
							hiddenName: 'receipt_particular',
							displayField: 'ledgertypename',
							valueField: 'accled_Ledger_Id',
							triggerAction: 'all',
							allowBlank: false,
							forceSelection: true,
							editable: true,
							typeAhead: true,
							minChars: 1,
							listeners: {
								specialkey: function (thisfield, e) {
									if (e.getKey() == e.ENTER)
									{
										if (thisfield.getValue() == '')
										{
											if (!Ext.isEmpty(particular_store))
											{
												e.stopEvent();
												Ext.getCmp('receipt_narration').focus();
											}
										} else
										{
											Ext.getCmp('receipt_amount').focus();
										}


									}
								}
							}
						}, {html: '&nbsp;&nbsp;Amount : &nbsp;'},
						{
							width: 80,
							xtype: 'numberfield',
							format: FINASCOP_CURRENCY_FORMAT,
							style: 'text-align:right',
							id: 'receipt_amount',
							allowDecimal: true,
							listeners: {
								specialkey: function (field, e) {
									if (e.getKey() == e.ENTER)
									{
										updateParticulars(type);
										Ext.getCmp('receipt_particular').focus();
									}
								}
							}
						}, {

							xtype: 'button',
							iconCls: 'finascop_add',
							style: 'margin-left:5px',
							handler: function () {
								updateParticulars(type);
							}
						}],
					bbar: ['->', {html: 'Total : &nbsp;'}, {
							xtype: 'numberfield',
							format: FINASCOP_CURRENCY_FORMAT,
							style: 'text-align:right',
							readOnly: true,
							value: 0,
							id: 'receipt_total_amount',
						}],
					stripeRows: true,
					autoExpandColumn: 'particular_auto_exp'
				}),
				new Ext.Panel({
					layout: 'form',
					labelAlign: 'top',
					region: 'south',
					border: false,
					autoHeight: true,
					hideBorders: true,
					items: [{
							xtype: 'textarea',
							labelStyle: 'margin-left:15px;',
							fieldLabel: 'Narration',
							id: 'receipt_narration',
							name: 'receipt_narration',
							anchor: '98%',
							maxLength: 500,
							enableKeyEvents: true,
							style: 'margin-left:15px;'
						}],
					keys: [
						{key: [Ext.EventObject.ENTER], handler: function (key, event) {
								var elem = event.getTarget();
								var component = Ext.getCmp(elem.id);
								if (component instanceof Ext.form.TextArea)
								{
									event.stopEvent();
									Ext.Msg.show({
										title: 'Confirm',
										msg: "Do you wants to save the entry?",
										buttons: Ext.MessageBox.YESNO,
										fn: function (btn) {
											if (btn != 'no')
											{
												saveReceiptDetails();
											} else
											{
												Ext.getCmp('receipt_narration').focus();
											}

										}
									});
								}

							}
						}
					]
				})
			]
		});
	};
	var particularStore = function () {
		var store = new Ext.data.JsonStore({
			method: 'post',
			proxy: new Ext.data.HttpProxy({
				url: modURL + '&op=listParticulars',
				method: 'post'
			}),
			fields: ['particular_id', 'particular_name', {name: 'amount', type: 'float'}],
			totalProperty: 'totalCount',
			root: 'data',
			remoteSort: false,
			autoLoad: false
		});
		store.setDefaultSort('particular_name', 'ASC');
		return store;
	};
	var accountComboStore = function (type) {

		var store = new Ext.data.JsonStore({
			autoLoad: false,
			url: modURL + '&op=getAccounts',
			method: 'post',
			fields: ['accled_LedgerName', 'accled_Ledger_Id', 'GroupName', 'ledgertypename', 'ledgertypeid', 'Group_ID'],
			totalProperty: 'totalCount',
			root: 'data',
			remoteSort: true

		});
		store.baseParams.type = type;
		return store;
	};
	var particularComboStore = function (type) {
		var store = new Ext.data.JsonStore({
			autoLoad: false,
			url: modURL + '&op=getParticulars',
			method: 'post',
			fields: ['accled_LedgerName', 'accled_Ledger_Id', 'GroupName', 'ledgertypename', 'ledgertypeid', 'Group_ID'],
			totalProperty: 'totalCount',
			root: 'data'
		});
		store.baseParams.type = type;
		store.baseParams.selectedAccounts = Ext.getCmp('receipt_account').getValue();
		return store;
	};
	var updateParticulars = function (type) {
		var particular = Ext.getCmp('receipt_particular').getValue();
		var particular_nm = Ext.getCmp('receipt_particular').getRawValue();
		var amount = Ext.getCmp('receipt_amount').getValue();
		if (!Ext.isEmpty(particular) && !Ext.isEmpty(amount) && amount != 0)
		{
			if (type == 'Journal Voucher' && Ext.getCmp('account_type1').getValue() == false
					&& Ext.getCmp('account_type2').getValue() == false)
			{
				Ext.Msg.alert("Notification", 'Please select Debtor/Creditor.');
				return false;
			}

			var grid = Ext.getCmp('particular_grid');
			var grid_store = grid.getStore();
			var exist = grid_store.find('particular_id', particular);
			if (exist == -1)
			{
				var row = new Ext.data.Record.create({
					name: 'particular_name',
					name: 'particular_id',
					name: 'amount'
				});
				var r = new row({
					'particular_id': particular,
					'particular_name': particular_nm,
					'amount': amount
				});
				grid_store.add([r]);
				var amt = Ext.util.Format.number(grid_store.sum('amount'), "0.00");
				console.log(amt);
				Ext.getCmp('receipt_total_amount').setValue(amt);
				if (type == 'Journal Voucher')
				{
					Ext.getCmp('ctr_dtr_type').disable();
				}

				Ext.getCmp('receipt_particular').reset();
				Ext.getCmp('receipt_amount').reset();
			} else
			{
				Ext.Msg.alert("Notification", 'Particular already exists.');
			}
		} else
		{
			Ext.Msg.alert("Notification", 'Please fill Particular and Amount.');
		}
	};
	var uploadForm = function () {
		return new Ext.Panel({
			layout: "border",
			region: 'west',
			width: 350,
			border: false,
			items: [new Ext.Panel({
					layout: "fit",
					region: 'center',
					autoScroll: true,
					id: 'image_panel',
					tpl: new Ext.XTemplate('<div class="details-outer">',
							'<img src="{img_src}"></img>',
							'</div>')
							/* items: {html: '<iframe style="overflow:hidden;width:99%;height:99%" id="id_view_" frameborder="0"  src="http://cashex.sil.lab/admin/resources/images/cashex.png"></iframe>'
							 }*/
				}), {
					xtype: 'hidden',
					id: 'aws_file_location',
					name: 'aws_file_location'
				}, {
					xtype: 'hidden',
					id: 'aws_file_bucket',
					name: 'aws_file_bucket'
				},
				new Ext.form.FormPanel({
					region: 'south',
					// url: 'https://xeproof-trial.s3.amazonaws.com/',
					id: 'receipt_image_upload',
					layout: 'form',
					fileUpload: true,
					// autoHeight: true,
					height: 30,
					frame: true,
					labelWidth: 30,
					labelAlign: 'top',
					items: [{
							xtype: 'hidden',
							id: 'file_name', name: 'file_name'
						}, {
							xtype: 'hidden',
							id: 'albumBucketName',
							name: 'albumBucketName'
						}, {
							xtype: 'hidden',
							id: 'accessKey',
							name: 'accessKey'
						}, {
							xtype: 'hidden',
							id: 'secretKey',
							name: 'secretKey'
						}, {
							xtype: 'hidden',
							id: 'bucketRegion',
							name: 'bucketRegion'
						}/*, {
						 xtype: 'fileuploadfield',
						 id: 'associated_file',
						 anchor: '98%',
						 fieldLabel: '',
						 name: 'file',
						 allowBlank: true,
						 // hidden: true,
						 buttonOnly: true,
						 buttonCfg: {
						 text: 'Upload',
						 iconCls: 'upload_cloud'
						 },
						 validator: function (v) {
						 if (v != '') {
						 v = v.toLowerCase();
						 var exp = /^.*.(png|jpg|gif)$/i;
						 if (!(exp.test(v))) {
						 return 'Upload a valid image file of format PNG/JPG/GIF.';
						 }
						 return true;
						 }
						 }
						 }*/],
					buttons: [
						/*{
						 xtype: 'button',
						 text: 'Scan',
						 iconCls: 'scan'
						 },*/{
							xtype: 'fileuploadfield',
							id: 'associated_file',
							anchor: '98%',
							fieldLabel: 'Select File',
							name: 'file',
							allowBlank: true,
							buttonOnly: true,
							// hidden: true,
							buttonCfg: {
								text: '',
								iconCls: 'finascop_upload_file',
								width: 80
							},
							validator: function (v) {
								if (v != '')
								{
									v = v.toLowerCase();
									var exp = /^.*.(png|jpg|gif)$/i;
									if (!(exp.test(v)))
									{
										return 'Upload a valid image file of format PNG/JPG/GIF.';
									}

									var associated_file = Ext.getCmp('associated_file').getValue();
									if (associated_file == '')
									{
										Ext.Msg.alert("Notification", "Please choose a scanned file to upload");
										return;
									}

									var receipt_image_upload = Ext.getCmp('receipt_image_upload').getForm();
									if (receipt_image_upload.isValid())
									{
										Application.Finascop_DataEntry.FileEdited = true;
										var file_Name = JSON.stringify(associated_file).slice(1, -1);
										// file_Name = file_Name.replace(/^.*[\\\/]/, '');

										var url = 'https://a2dbxa6rca.execute-api.us-east-1.amazonaws.com/Production/apscashexasset/' + file_Name;
										winLoadMask.show();
										addPhoto();
									}
									return true;
								}
							}
						}, {
							xtype: 'button',
							text: '',
							tooltip: 'App Scan',
							iconCls: 'finascop_app_scan',
							width: 80

						}]
				})]
		});
	};
	function addPhoto() {


		var albumBucketName = Ext.getCmp('albumBucketName').getValue();
		var bucketRegion = Ext.getCmp('bucketRegion').getValue();
		AWS.config.update({
			region: bucketRegion,
			credentials: new AWS.Credentials(
					Ext.getCmp('accessKey').getValue(),
					Ext.getCmp('secretKey').getValue(), null
					)
		});
		var s3 = new AWS.S3({
			apiVersion: '2006-03-01',
			params: {Bucket: albumBucketName}
		});
		var files = document.getElementById('associated_file-file').files;
		if (!files.length)
		{
			winLoadMask.hide();
			return alert('Please choose a file to upload first.');
		}
		var file = files[0];
		var fileName = file.name;
		var file_Name = JSON.stringify(fileName).slice(1, -1);
		/* var albumPhotosKey = encodeURIComponent(albumName) + '//';*/

		/* var photoKey = fileName;*/

		s3.upload({
			Key: Ext.getCmp('file_name').getValue()/*file_Name*/, /*from server*/
			Body: file,
			ACL: 'public-read'
		}, function (err, data) {

			if (err)
			{
				winLoadMask.hide();
				var img_src = Ext.BLANK_IMAGE_URL;
				Ext.getCmp('image_panel').update({'img_src': img_src});
				return Ext.Msg.alert("Notification", 'There was an error uploading your photo: ' + err.message);
			}
			if (!Ext.isEmpty(data.Location))
			{

				winLoadMask.hide();
				Ext.Msg.alert("Notification", 'File has been uploaded successfully.');
				Application.Finascop_DataEntry.UploadedFileLocation = data.Location;
				Application.Finascop_DataEntry.UploadedFileBucket = data.Bucket;
				Ext.getCmp('aws_file_bucket').setValue(data.Bucket);
				Ext.getCmp('aws_file_location').setValue(data.Location);
				/* var img_src = 'http://cashex.sil.lab/admin/resources/images/cashex.png';*/
				Ext.getCmp('image_panel').update({'img_src': Application.Finascop_DataEntry.UploadedFileLocation});
			}
		});
	}


	var receiptPanel = function (type) {
		return new Ext.Panel({
			layout: "border",
			border: false,
			height: 490,
			id: 'receiptPanel',
			items: [uploadForm(), particularDetails(type)]
		});
	};
	var loadDetails = function (acet_NO, type, updated_on, data, acet_DocNO) {
		var t = new Date();
		var t_stamp = t.format("YmdHis");
		Ext.getCmp('particular_grid').getStore().load({
			params: {
				apikey: _SESSION.apikey,
				tstamp: t_stamp,
				acet_NO: acet_NO,
				type: type,
				acc_ledger_id: data.account
			}
		});
		if (data.acet_Date != '00-00-0000' && !Ext.isEmpty(data.acet_Date))
			Ext.getCmp('receipt_date').setValue(data.acet_Date);
		Ext.getCmp('receipt_total_amount').setValue(data.acet_Amount);
		Ext.getCmp('receipt_narration').setValue(data.acet_Narration);
		Ext.getCmp('ledger_type_selected').setValue(data.acet_TypeId);
		Ext.getCmp('id_updatedon').setValue(data.updated_on);
		if (data.IsDebtor == 0)
		{
			Ext.getCmp('account_type2').setValue(true);
		}
		if (data.IsDebtor == 1)
		{
			Ext.getCmp('account_type1').setValue(true);
		}

		Ext.getCmp('acet_NO').setValue(data.acet_NO);
		Ext.getCmp('receipt_account').getStore().load({
			callback: function () {
				Ext.getCmp('receipt_account').setValue(data.account);
				Ext.getCmp('receipt_account').fireEvent('select', Ext.getCmp('receipt_account'));
				Ext.getCmp('acet_NO').setValue(data.acet_NO);
				Ext.getCmp('acet_DocNO').setValue(acet_DocNO);
				Application.Finascop_DataEntry.FileEdited = false;
				Application.Finascop_DataEntry.AddEdit = 'Edit';
			}
		});
	};
	var saveReceiptDetails = function () {
		WinMask = new Ext.LoadMask(Ext.getCmp('receiptPanel').getEl());
		WinMask.show()
		var grid = Ext.getCmp('particular_grid');
		var grid_store = grid.getStore();
		var data = Ext.pluck(grid_store.getRange(), 'data');
		var AutoApproverCheck = ((_SESSION.IsAutoApprovalEnabled == 1) && Ext.getCmp('receipt_total_amount').getValue() <= 0);
		var haveParticularsEnterd = (grid_store.getCount() > 0);
		var ParticularEntryCheck = (haveParticularsEnterd && Ext.getCmp('receipt_total_amount').getValue() <= 0);
		if (AutoApproverCheck || ParticularEntryCheck)
		{
			Ext.Msg.alert("Error", 'Cannot Save.Total amount less than or equal to zero.', function (btn) {

				Ext.getCmp('receipt_account').focus();
			});
			WinMask.hide();
			return;
		}

		var validEntry = ((Ext.getCmp('receipt_total_amount').getValue() >= 0));
		if (validEntry)
		{
			if (!Ext.isEmpty(data) && !Ext.isEmpty(Ext.getCmp('receipt_date').getRawValue()) &&
					!Ext.isEmpty(Ext.getCmp('receipt_account').getValue()))
			{

				var form_data = {
					particular_data: data,
					receipt_date: Ext.getCmp('receipt_date').getRawValue(),
					receipt_account: Ext.getCmp('receipt_account').getValue(),
					receipt_account_name: Ext.getCmp('receipt_account').getRawValue(),
					ledger_type: Ext.getCmp('ledger_type_selected').getValue(),
					total_amount: Ext.getCmp('receipt_total_amount').getValue(),
					narration: Ext.getCmp('receipt_narration').getValue(),
					type: Ext.getCmp('receipt_window').account_type,
					uploaded_file_name: Ext.getCmp('uploaded_file_name').getValue(),
					file_up_id: Ext.getCmp('file_up_id').getValue(),
					ctr_dtr_type: Ext.getCmp('ctr_dtr_type').getValue(),
					location: Application.Finascop_DataEntry.UploadedFileLocation,
					bucket: Application.Finascop_DataEntry.UploadedFileBucket,
					acet_NO: Ext.getCmp('acet_NO').getValue(),
					acet_DocNO: Ext.getCmp('acet_DocNO').getValue(),
					updated_on: Ext.getCmp('id_updatedon').getValue()

				};
				var params = {
					action: (Ext.getCmp('acet_NO').getValue() == '' ? 'Insert' : 'Update'),
					module: 'data_entry',
					op: 'saveParticularData',
					id: (Ext.getCmp('acet_NO').getValue() == '' ? '-' : Ext.getCmp('acet_NO').getValue()),
					extrainfo: 'asd'
				};
				APICall(params, Application.Finascop_DataEntry.saveData, form_data);
			} else if (!Ext.isEmpty(Application.Finascop_DataEntry.UploadedFileLocation))
			{

				var params = {
					action: (Ext.getCmp('acet_NO').getValue() == '' ? 'Insert' : 'Update'),
					module: 'data_entry',
					op: 'saveFileDetails',
					id: (Ext.getCmp('acet_NO').getValue() == '' ? '-' : Ext.getCmp('acet_NO').getValue()),
					extrainfo: 'asd'
				};
				var form_data = {
					location: Application.Finascop_DataEntry.UploadedFileLocation,
					bucket: Application.Finascop_DataEntry.UploadedFileBucket,
					type: Ext.getCmp('receipt_window').account_type,
					acet_NO: Ext.getCmp('acet_NO').getValue(),
					updated_on: Ext.getCmp('id_updatedon').getValue()
				};
				APICall(params, Application.Finascop_DataEntry.saveFileData, form_data);
			} else
			{
				Ext.MessageBox.alert('Error', 'Check the required fields');
			}
		} else
		{
			Ext.MessageBox.alert('Error', 'Cannot Save.Total amount less than zero.');
		}
		WinMask.hide();
	};
	return {
		initDataEntry: function () {
			var panelId = 'data_entry_main_panel';
			var data_entry_panel = Ext.getCmp(panelId);
			if (Ext.isEmpty(data_entry_panel))
			{
				data_entry_panel = dataEntryGrid(panelId);
				Application.UI.addTab(data_entry_panel);
				data_entry_panel.doLayout();
			} else
			{
				Application.UI.addTab(data_entry_panel);
				data_entry_panel.doLayout();
			}
			updatePagination(data_entry_panel);
		},
		DataEntryMainPanel: function () {
			return [{
					region: 'center',
					border: false,
					layout: 'fit',
					items: Application.Finascop_DataEntry.initDataEntry()
				}];
		},
		receipt_voucher: function (acet_NO, type, updatedOn, entrydetails, acet_DocNO) {
			Application.Finascop_DataEntry.UploadedFileLocation = '';
			Application.Finascop_DataEntry.UploadedFileBucket = '';
			Application.Finascop_DataEntry.AddEdit = 'Add';
			Application.Finascop_DataEntry.FileEdited = false;
			var acet_NO = arguments[0];
			var type = arguments[1];
			var updatedOn = arguments[2];
			var entrydetails = arguments[3];
			var acet_DocNO = arguments[4];
			var title = (acet_NO != 0) ? 'Edit ' + type + ' : ' + acet_DocNO : 'Create ' + type;
			var win_id = "receipt_window";
			var receipt_window = Ext.getCmp(win_id);
			if (Ext.isEmpty(receipt_window))
			{
				var receipt_panel = receiptPanel(type);
				receipt_window = new Ext.Window({
					id: win_id,
					title: title,
					layout: 'fit',
					width: 850,
					autoHeight: true,
					plain: true,
					constrainHeader: true,
					modal: true,
					frame: true,
					//iconCls: 'finascop_dataentry_receipt',
					resizable: false,
					items: receipt_panel,
					account_type: type,
					listeners: {
						afterrender: function () {
							var t = new Date();
							var t_stamp = t.format("YmdHis");
							Ext.getCmp('receipt_date').focus(false, 200);
							winLoadMask = new Ext.LoadMask(Ext.getCmp('receipt_window').getEl());
							winLoadMask.msg = 'Please wait...';
							Ext.getCmp('receipt_image_upload').getForm().load({
								waitTitle: 'Please Wait',
								waitMsg: 'Loading...',
								url: modURL + '&op=get_s3_details',
								params: {
									apikey: _SESSION.apikey,
									tstamp: t_stamp
								}
							});
							if (acet_NO != 0)
							{
								loadDetails(acet_NO, type, updatedOn, entrydetails, acet_DocNO);
							}

						}
					},
					buttons: [{
							text: 'Cancel',
							icon: IMAGE_BASE_PATH + '/default/icons/finascop_cancel.png',
							iconCls: 'finascop_my-icon1',
							handler: function () {
								if (!Ext.isEmpty(Application.Finascop_DataEntry.UploadedFileLocation) && Application.Finascop_DataEntry.FileEdited == true)
								{
									Ext.MessageBox.confirm('Confirm', 'You have a file to be saved. Do you wish to save the file?', function (btn, text) {
										if (btn == 'yes')
										{
											var params = {
												action: 'Insert',
												module: 'data_entry',
												op: 'saveFileDetails',
												id: '0',
												extrainfo: 'asd'
											};
											var form_data = {
												location: Application.Finascop_DataEntry.UploadedFileLocation,
												bucket: Application.Finascop_DataEntry.UploadedFileBucket,
												type: receipt_window.account_type,
												acet_NO: Ext.getCmp('acet_NO').getValue()
											};
											APICall(params, Application.Finascop_DataEntry.saveFileData, form_data);
										} else
										{
											receipt_window.close();
										}
									});
								} else
								{
									receipt_window.close();
								}
							}
						},
						{
							text: 'Save',
							icon: IMAGE_BASE_PATH + '/default/icons/finascop_disk.png',
							iconCls: 'finascop_my-icon1',
							id: 'saveButton',
							handler: function () {
								saveReceiptDetails();
							}
						}]
				});
			}
			receipt_window.doLayout();
			receipt_window.show(this);
			receipt_window.center();
			var img_src = Ext.BLANK_IMAGE_URL;
			Ext.getCmp('image_panel').update({'img_src': img_src});
		},
		saveData: function () {
			var receipt_window = Ext.getCmp('receipt_window');
			var grid = Ext.getCmp('particular_grid');
			var grid_store = grid.getStore();
			var data = Ext.pluck(grid_store.getRange(), 'data');
			//var currentUpdatedTimeInDataBase;
			Ext.Ajax.request({
				url: modURL + '&op=saveParticularData',
				method: 'POST',
				params: {
					"particular_data": Ext.encode(data),
					"receipt_date": Ext.getCmp('receipt_date').getRawValue(),
					"receipt_account": Ext.getCmp('receipt_account').getValue(),
					"receipt_account_name": Ext.getCmp('receipt_account').getRawValue(),
					"ledger_type": Ext.getCmp('ledger_type_selected').getValue(),
					"total_amount": Ext.getCmp('receipt_total_amount').getValue(),
					"narration": Ext.getCmp('receipt_narration').getValue(),
					"type": receipt_window.account_type,
					"uploaded_file_name": Ext.getCmp('uploaded_file_name').getValue(),
					"file_up_id": Ext.getCmp('file_up_id').getValue(),
					"ctr_dtr_type": Ext.getCmp('ctr_dtr_type').getValue(),
					location: Application.Finascop_DataEntry.UploadedFileLocation,
					bucket: Application.Finascop_DataEntry.UploadedFileBucket,
					acet_NO: Ext.getCmp('acet_NO').getValue(),
					acet_DocNO: Ext.getCmp('acet_DocNO').getValue(),
					updated_on: Ext.getCmp('id_updatedon').getValue(),
					apikey: _SESSION.apikey
				},
				success: function (resp) {
					var res = Ext.decode(resp.responseText);
					if (res.success === true)
					{
						Ext.Msg.hide();
//                        
						Ext.Msg.confirm('Notification', 'Do you want to Print . Ref No:' + res.RefNo + ' ?', function (btn) {
							Ext.getCmp('data_entry_main_panel').getStore().reload();
							loadDataEntry();
							receipt_window.close();
							if (btn == 'yes')
							{
								var aws_s3_object_url = res.ObjectUrl;
								var url = aws_s3_object_url;
								Ext.get('iframedownload').dom.src = url;
							} else
							{

							}
						});

					} else
					{
						Ext.Msg.hide();
						receipt_window.close();
						Ext.MessageBox.alert('Error', res.msg, function (btn) {
							Ext.getCmp('data_entry_main_panel').getStore().reload();
							loadDataEntry();
						});
					}
				},
				failure: function (elm, conf) {
					if (conf.failureType === 'server')
					{
						var result = Ext.decode(conf.response.responseText);
						Ext.Msg.alert('Notification', result.error);
					} else
					{
						var result = Ext.decode(conf.response.responseText);
						Ext.MessageBox.alert('Error', result.error);
					}
				}
			});
		},
		saveFileData: function () {

			Ext.Ajax.request({
				url: modURL + '&op=saveFileDetails',
				method: 'POST',
				params: {
					Bucket: Application.Finascop_DataEntry.UploadedFileBucket,
					Location: Application.Finascop_DataEntry.UploadedFileLocation,
					type: Ext.getCmp('receipt_window').account_type,
					acet_NO: Ext.getCmp('acet_NO').getValue(),
					acet_DocNO: Ext.getCmp('acet_DocNO').getValue(),
					updated_on: Ext.getCmp('id_updatedon').getValue(),
					"receipt_date": Ext.getCmp('receipt_date').getRawValue()
				},
				success: function (resp) {
					var res = Ext.decode(resp.responseText);
					winLoadMask.hide();
					if (res.success === true)
					{
						Ext.getCmp('receipt_window').close();
						Ext.Msg.alert("Notification", 'File has been saved successfully. Ref No: ' + res.RefNo, function (btn) {

							Ext.getCmp('data_entry_main_panel').getStore().reload();
							loadDataEntry();
						});
					} else
					{

						Ext.MessageBox.alert('Error', res.msg);
					}
				},
				failure: function (elm, conf) {
					winLoadMask.hide();
					Ext.Msg.alert("Error", "Error occured while saving data.");
				}
			});
		}
	};
}();




