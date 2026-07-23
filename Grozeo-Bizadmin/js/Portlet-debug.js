/*
 * Ext JS Library 2.2
 * Copyright(c) 2006-2008, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */

Ext.ux.Portlet = Ext.extend(Ext.Panel, {
    anchor: '100%',
    frame:false,
    collapsible:true,
    draggable:true,
    cls:'x-portlet',
	bodyBorder: false,
	border: false
});
Ext.reg('portlet', Ext.ux.Portlet);