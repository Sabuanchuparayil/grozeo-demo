var JSApplet = function(cfg) {
    var config={};
    for (var v in cfg) {
        config[v] = cfg[v];
    }
    if (config.code===undefined || config.code===null || typeof(config.code)!='string' ||
        config.code == '') {
        return false;
    }
    var removeFromDOM = function() {
        var elm = document.getElementById(config.id);
        if (elm!=null) {
            try {
                elm.parentNode.removeChild(elm);
            } finally {
                config.applet = null;
                this.rendered = false;
            }
        }
    }
    var arrayToString = function(arr)  {
        var arg = "";
        for (var i=0;i<arr.length; i++) {
            arg += (arg!=""?",":"")
            if(arr[i]==undefined || arr[i]==null || typeof(arr[i])=='number' || typeof(arr[i])=='boolean') {
                arg += arr[i];
            } else {
                arg += "'" + arr[i].replace(/\'/g,'"').replace(/\\/g,'/') + "'";
            }
        }
        return arg;
    }
    return {
        initialConfig: cfg,
        rendered: false,
        init: function() {            
            if (config.id===undefined || config.id===null || typeof(config.id)!='string' ||
                config.id == '') {
                config.id = 'appl'+document.getElementsByTagName('applet').length;
            }
            if (config.parent!==undefined && config.parent!==null && typeof(config.parent)!='object') {
                config.parent = document.getElementById(config.parent);
            }
            if (config.parent===undefined || config.parent===null || 
                typeof(config.parent)!='object' && typeof(config.parent.innerHTML)=="string") {
                config.parent = document.getElementsByTagName('body')[0];
            }
            if (config.width==undefined || config.width===null ||
                typeof(config.width)!='number') {
                config.width = 1;
            }
            if (config.height==undefined || config.height===null ||
                typeof(config.height)!='number') {
                config.height = 1;
            }
            if (config.methods==undefined || config.methods===null ||
                typeof(config.methods)=='object' && typeof(config.methods.length)=='number') {
                for (var i=0;i<config.methods.length;i++) {
                    if (typeof(config.methods[i])=='string' && config.methods[i]!='') {
                        this.addMethod(config.methods[i]);
                    } else if(typeof(config.methods[i])=='object' && 
                        typeof(config.methods[i].method)=='string' && 
                        config.methods[i].method!='') {
                        this.addMethod(config.methods[i].method,(
                        (typeof(config.methods[i].defaultArgs)=="object" &&
                            typeof(config.methods[i].defaultArgs.length)=="number")?
                            config.methods[i].defaultArgs:undefined),(
                        (typeof(config.methods[i].alias)=="string")?
                            config.methods[i].alias:undefined));
                    }
                    
                }
            }
            removeFromDOM();
        },
        render: function() {
            if (arguments.length>0 && arguments[0]!==undefined && arguments[0]!==null) {
                if (typeof(arguments[0])=='string') {
                    config.parent = arguments[0];
                }
            }
            this.init();
            try {
                applet = document.getElementById(config.id);
                if (applet===undefined || applet === null) {
                    applet = document.createElement('applet');
                    applet.setAttribute("width",config.width);
                    applet.setAttribute("height",config.height);
                    applet.setAttribute("archive",config.archive);
                    applet.setAttribute("id",config.id);
                    applet.setAttribute("name",config.id);
                    applet.setAttribute("code",config.code);
                    config.parent.appendChild(applet);
                    applet = document.getElementById(config.id);
                    this.rendered = true;
                    config.applet = applet;
                }
                return true;
            } catch (e) {
                catchError(e);
            }
            return false;
        },
        destroy: function() {
            removeFromDOM();
            config = null;
            delete this;
        },
        addMethod: function(methodName,defaultArgs, alias) {
            if (methodName===undefined || methodName===null || 
                typeof(methodName)!="string" || methodName=='init' || 
                methodName=='render' || methodName=='destroy' || 
                methodName=='addMethod' || methodName=='callMethod'){
                return false;
            }
            eval("var fn = function(){"+
                "var arr1 = ["+(defaultArgs!=undefined && defaultArgs!=null &&
                typeof(defaultArgs)=='object' && 
                typeof(defaultArgs.length)=='number'?arrayToString(defaultArgs):"")+"];"+
                "eval('var arr2 = ['+arrayToString(arguments)+'];');"+
                "return this.callMethod('"+methodName+"',arr1.concat(arr2))"+
                "};");
            var fnName = (alias!=undefined && alias!=null && typeof(alias)=="string"?alias:methodName);
            this[fnName] = fn;           
            return true;
        },
        callMethod: function(method, args) {
            var fnc = "var ret = config.applet."+method+"("+arrayToString(args)+")";
            alert(fnc);
            try {
                eval(fnc);
            } catch (e) {
                throw e;
            }
            return ret;
        }
    };
}