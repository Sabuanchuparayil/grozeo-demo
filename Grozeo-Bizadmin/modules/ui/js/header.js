/*
 * @author Ratheesh Kumar CK <ratheesh@saturn.in>
 * @created on 25-Jul-2008
 * @last modified on 06-Aug-2009
 *
 * All the components needs to be present in the header/north portion
 * of the base layout is defined here for better readability.
 */
//Initialize the Variable

//Get Json Menu
//var menubar = new Ext.Toolbar(jsonMenu);

var modURL = '?module=ui';

//The area holding the top logo only.
var logo_part = new Ext.Panel({
    region: "north",
    border: false,
    contentEl: "headerlogo",
    height: 100
});

var menu = new Ext.Toolbar({
    cls: "x-inline-toolbar",
    width: 250,
    layout: "auto",
    flat: true
});

//Define Header Layout
var header = new Ext.Panel({
    region: "north",
    id: 'vp-header',
	
    border: false,
    cls: "headerjerk",
    contentEl: "topheader",
    //bbar: toolbar,
     
});


/*var lefttools = new Ext.Panel({
 region: "west",
 id: "lefttools",
 layout: "column",
 items: [menu]
 });*/

var lefttools = new Ext.Panel(
        {
            autoWidth: true,
            layout: "column",
            region: "center",
            border: false,
            style: "background: transparent",
            items: [
                {
                    //width: 600,
                    autoWidth: true,
                    tbar: menu,
                    bodyStyle: "background-color:#d0def0;",
                    border: false
                }]
        });




