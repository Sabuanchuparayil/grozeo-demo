// ---------------------------------------------------
//  Namespace
// ---------------------------------------------------

var App = window.App || {};
App.engine  = {}; // engine namspace
App.modules = {}; // modules (such as AddTaskForm, AddMessageForm etc)
App.widgets = {}; // widgets (such as GroupedBlock, UserBoxMenu, PageAction)


App.engine = {
    showStatus: function(message) {
    
    },
    hideStatus: function() {
    
    }
}

// ---------------------------------------------------
// Language
// ---------------------------------------------------
if (typeof _lang != 'object') _lang = {};
function lang(name) {
    var value = _lang[name];
    if (!value) {
	return "Missing lang.js: " + name;
    }
    for (var i=1; i < arguments.length; i++) {
	value = value.replace("{" + (i-1) + "}", arguments[i]);
    }
    return value;
}

function langhtml(name) {
    return '<span name="og-lang" id="og-lang-' + name + '">' + lang(name) + '</span>';
}

function addLangs(langs) {
    for (var k in langs) {
	_lang[k] = langs[k];
    }
}

// ---------------------------------------------------
// Logging
// ---------------------------------------------------
/**
 * A common logging function. Will O/P the passed Object o to the Console
 * if console is available or to alert. THis function works only in a '.sil.lab'
 * domain, if the third parameter force is not true
 *
 *   @TODO Can also include an ajax request which can log
 *   clientside javascript errors in a database table
 *
 * @param o Object / Array
 * @param d true / false - true for console.dir
 * @param f true / false - true to force logging on all domains
 */

function log(o,d,f){
    if ((typeof console != 'undefined'  && DEBUG_MODE) || f===true) {
	var con = alert;
	try {
	    if (d && d===true) {
		con = console.dir;
	    } else {
		con = console.log;
	    }
	} catch (e) {}
	con(o);
    }
}

// -----------------------------
// Cookies
// -----------------------------

var Cookies = {};
Cookies.set = function(name, value, expires, path, domain, secure){
    Ext.state.Manager.set(name, value);
    return;
    document.cookie = name + "=" + escape (value) +
    (expires ? "; expires=" + expires.toGMTString() : "") +
    (path ? "; path=" + path : "") +
    (domain ? "; domain=" + domain : "") +
    (secure ? "; secure" : "");
};

Cookies.get = function(name){
    return Ext.state.Manager.get(name);
    return;
    var start = document.cookie.indexOf(name + "=");
    if (start < 0) {
	return "";
    }
    var temp = document.cookie.substring(start + name.length + 1);
    var end = temp.indexOf(';');
    if (end < 0) {
	return unescape(temp);
    } else {
	return unescape(temp.substring(0, end));
    }
};

Cookies.clear = function(name) {
    if (Cookies.get(name)) {
	document.cookie = name + "=" +
	"; expires=Thu, 01-Jan-70 00:00:01 GMT";
    }
};


/***
* Simulate php array_merge function
*
... [more] * @param {Object/Array} arr1
* @param {Object/Array} arr2
* var a1 = {'aa':100, 'bb':2, 'cc':[6,7], 'dd':[12,13], 'ee':{'15':15,'16':16}};
* var b1 = {'xx':101, 'bb':5, 'cc':8, 'dd':[14,15], 'ee':{'17':17,'18':18}};
* var c = array_merge(a1, b1);
* console.log(c) [in firebug]
* Output: {'aa':100, 'bb': 5, 'cc':[6,7], 'dd':[12,13,14,15], 'ee':{'15':15,'16':16,'17':17,'18':18}, 'xx':101}
*/
var array_merge = function(arr1, arr2){
  if((arr1 && (arr1 instanceof Array)) && (arr2 && (arr2 instanceof Array))){
    for (var idx in arr2) {
      arr1.push(arr2[idx]);
    }
  }else if((arr1 && (arr1 instanceof Object)) && (arr2 && (arr2 instanceof Object))){
    for(var idx in arr2){
      if(idx in arr1){
        if (typeof arr1[idx] == 'object' && typeof arr2 == 'object') {
          arr1[idx] = array_merge(arr1[idx], arr2[idx]);
        }else{
          arr1[idx] = arr2[idx];
        }
      }else{
        arr1[idx] = arr2[idx];
      }
    }
  }
  return arr1;
};
/*
 * Written By :Sreeram
 * Pourpose : removing duplicate items in an js array (Used in issue 621, statuschangejob.js)
 * Date: 16/8/2010
 *  **/
function array_unique(a)
{
   var r = new Array();
   o:for(var i = 0, n = a.length; i < n; i++)
   {
      for(var x = 0, y = r.length; x < y; x++)
      {
         if(r[x]==a[i]) continue o;
      }
      r[r.length] = a[i];
   }
   return r;
}
