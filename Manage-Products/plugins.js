

var agt=navigator.userAgent.toLowerCase();
var ie  = (agt.indexOf("msie") != -1);
var ns  = (navigator.appName.indexOf("Netscape") != -1);
var win = ((agt.indexOf("win")!=-1) || (agt.indexOf("32bit")!=-1));
var mac = (agt.indexOf("mac")!=-1);
var pluginlist;
if (ie && win) {	
		pluginlist = detectIE("ShockwaveFlash.ShockwaveFlash.1","Shockwave Flash"); 
}
if (ns || !win) {
		nse = ""; for (var i=0;i<navigator.mimeTypes.length;i++) nse += navigator.mimeTypes[i].type.toLowerCase();
		pluginlist = detectNS("application/x-shockwave-flash","Shockwave Flash");
}

function detectIE(ClassID,name) 
{ 
	result = false; 
	document.write('<SCRIPT LANGUAGE=VBScript>\n on error resume next \n result = IsObject(CreateObject("' + ClassID + '"))</SCRIPT>\n'); 
	if (result) 
		return name+','; 
	else 
		return ''; 
}

function detectNS(ClassID,name) 
{ 
	n = ""; 
	if (nse.indexOf(ClassID) != -1) 
		if (navigator.mimeTypes[ClassID].enabledPlugin != null) 
			n = name+","; 
	return n; 
}

pluginlist += navigator.javaEnabled() ? "Java" : "";