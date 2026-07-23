/*
 * @author Ratheesh Kumar CK <ratheesh@saturn.in>
 * @created on 29-Jul-2008
 *
 * The following comonents/things needs to be written in this file:
 * 1) Blank Image URL Initialization
 * 2) Namespace declaration
 * 3) Other Global Variable Declaration
 * 4) etc..
 *
 * This script needs to be included at first in the page.
 *
 */
///////////////////////////////////////////////////////////////////////////////
// This line should be included in all files in order to reference a local
// blank image (by default BLANK_IMAGE_URL = http://www.extjs.com)
Ext.SSL_SECURE_URL = "./resources/images/default/s.gif";
Ext.BLANK_IMAGE_URL = "./resources/images/default/s.gif";

/* Create a namespace Object
 * By specifying our own namespace we encapsulate all variables and methods in
 * one global object in order to avoid any conflicts or changes when working
 * with other javascript files. This pattern scales well as the project becomes
 * more complex and as its API grows. It stays out of the global namespace,
 * provides publicly addressable API methods, and supports protected or
 * private data and methods along the way.
 */
Ext.namespace('Application'); //define namespace with some 'Application'
/* So we have assigned an empty object 'Application' as a member of the Ext object
 * (Note: this doesn't overwrite 'Application' if it already exists).
 */
//Ext.QuickTips.init();


//Variable holds the viewport object
var viewport;
var admin_user_store  = {load:function(){}};
var posted_by_store   = {load:function(){}};
var member_store_grid,temp_runner;
var map = {disable:function(){}};
var designation_store={};
var IMAGE_BASE_PATH = "./resources/images";
var jsonMenu = {};
var testA = false;
var id_loss_of_pay = 4;
var today_leaves_store;
var store_available_production_status;
var mailtemplate_store_combo;
var grid_row_height = 25;
var GRID_ROW_HEIGHT = 25;
var DEBUG_MODE = true;

/*style for label color of mandatory fields*/
var mandatory_label_color_left = 'padding-left:10px; width:50; background:url(resources/images/default/icons/asterisk.gif) left center no-repeat;';
var mandatory_label_color_right = 'padding-right:10px; width:50; background:url(resources/images/default/icons/asterisk.gif) right center no-repeat;';
var mandatory_label_color_top = 'padding-right:10px; width:50; background:url(resources/images/default/icons/asterisk.gif) right center no-repeat;';
var mandatory_label_color_right_spl = 'padding-right:1px; width:50; background:url(resources/images/default/icons/asterisk.gif) right center no-repeat;';
var mandatory_label = mandatory_label_color_right;


var sort_order_array = [];
//Following spec_jobsubtypeid is used in Job.js. Issue #639. Useful while making advertiser field mandetory/non mandetory for spec sub type alone. 28-july
var spec_jobsubtypeid = 5;

function initializeDataStores(){

    if (Ext.isReady == true) {
   //store for email template combo
mailtemplate_store_combo = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: '?module=mail-templates&op=view_emailtemplate',
            method: 'GET'
        }),
        reader: new Ext.data.ArrayReader({}, [{
            name: 'id_template'
        }, {
            name: 'template_name'
        }])
    });
//store for todays leaves
map = new Ext.KeyMap(Ext.get(document));
		//To clear timeout
		clearTimeout(temp_runner);
    }
}



 /**
     * Returns a string representation of a Datastore
     * @param Datastore to be serialised
     * @return String The serialised data
     */
    function serializeGridData(ds){
        var arr = [];
        count = ds.data.items.length;
        for (var i = 0; i < count; i++) {
            arr[arr.length] = ds.data.items[i].data;
        }
        return Ext.encode(arr);
    }



