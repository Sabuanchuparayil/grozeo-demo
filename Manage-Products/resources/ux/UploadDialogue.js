/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function catchError(e){
    log(e);
}

AppletUploadDialog = Ext.extend(Ext.Window, {
    progressInfo: null,
    addJobApplet: null,
    uploaderApplet: null,
    appletFailureCount: 0,
    zipFileName: 'zipFile',
    uploadFilePath: '/home/web/xeproof/ftp/FilesToSO',
    task:{
        run: this.initUploaderApplet,
        interval: 1000 //1 second
    },
    runner: new Ext.util.TaskRunner(),
    uploadStatusBar: {},
    instanceName: "",
    post_data:{},
    uploadSpecificCallback:{},
    constructor: function(config){
        //Create Object for the Invisible Add Files Applet
        //archive: './applets/xeproofapplet.jar',
        //code: 'com.saturn.applets.util.UtilsApplet',
        this.uploaderApplet = this.getJSApplet({
            //id: 'ftpUploader',
            //archive: './applets/uploaderapplet.jar',
            // archive: './applets/soutils.jar',
            //code: 'com.saturn.applets.upload.FTPUploader',
            //code: 'com.saturn.applets.upload.FTPUpload',
            archive: './applets/xeproofapplet.jar',
            code: 'com.saturn.applets.util.UtilsApplet',
            //methods: ['startBrowse','getSelectedPath','getSelectedFiles','copyFileToDir','createZip','getListSize','getName','getProgress','getStatus','init','queue','setAddress','setEndFn','setFtpDetails','setFunctions','setPassword','setProgressFn','setRunner','setStartFn','setUserName','setFailedFn','getFailedFn']
            methods: ['startBrowse','getSelectedPath','getSelectedFiles','copyFileToDir','createZip','getListSize','getName','getProgress','getStatus','init','queue','setAddress','setEndFn','setFtpDetails','setFunctions','setPassword','setProgressFn','setRunner','setStartFn','setUserName','setFailedFn','getFailedFn']
        });
        this.uploaderApplet.render();
        //setTimeout('Application.AppletUploader.initUploaderApplet', 20);

        var task = {
            run: this.initUploaderApplet.createDelegate(this),
            interval: 1000 //1 second
        };
        this.runner.start(task);
        var buttons = [];

        if(Ext.isEmpty(config.JobId)) this.JobId = 0;
        if(Ext.isEmpty(config.referenceModule)) config.referenceModule = "";
        else this.referenceModule = config.referenceModule;
        if(Ext.isEmpty(config.JobId))  config.JobId = 0;
        if(parseInt(config.JobId)==0 && config.referenceModule!="dam"){
            buttons[0] = {
                text: 'Add to Upload Queue',
                handler: function(){
                    var tWindow = this;
                    if(this.addJobApplet.getFileListSize()==0){
                        tWindow.hide();
                        Ext.Msg.alert('No files added','Please add files to add to Upload Queue',function(){
                            tWindow.show();
                        });
                        
                    }else{
                        this.hide();
                        // Add the selected files into Grid's store.
                        var selected_files = this.addJobApplet.getListData().toString();
                        selected_files = Ext.util.JSON.decode(selected_files);
                        var fileQueJSON = new Array();
                        for(var i =0; i<selected_files.length; i++) {
                            fileQueJSON[i] = {
                                fileName: selected_files[i],
                                fileSource: "User Upload",
                                uploadStatus: "Waiting.",
                                sourceId: "USR_UPLOAD"
                            };
                        }
                        if(Ext.isFunction(config.addQueCallback))
                            config.addQueCallback(fileQueJSON);
                    }
                //Ext.getCmp('launch_dam_files_grid').store.loadData(fileQueJSON);

                // You can use this.addJobApplet.getListData().toString(); for this.
                //then  show that grid.. set active item..
                },
                scope: this
            };
        }else{
            buttons[0] = {
                text: 'Upload',
                handler: this.uploadFiles,
                scope: this
            };
        }

        buttons[1] = {
            text: 'Cancel',
            handler: this.onClose,
            scope: this
        };

        var text_area = new Ext.form.TextArea({
            emptyText:'Type Here',
            fieldLabel:'Comments',
            id:'comment_box',
            width:685
        });
        var top_panel;
        if(!Ext.isEmpty(Ext.getCmp('id_top_panel'))){
            Ext.getCmp('id_top_panel').destroy();
        }

        top_panel = new Ext.Panel({
            autoHeight:true,
            layout:'form',
            frame:true,
            border:false,
            labelAlign:'top',
            id:'id_top_panel',
            width:700,
            items:[{
                html:"",
                id: 'question_id'
            },text_area]
        });
        
        AppletUploadDialog.superclass.constructor.call(this, Ext.applyIf(config ||
        {}, {
            title: 'Upload Assets',
            //id:'appletuploaddialog',
            minimizable: true,
            constrainHeader: true,
            closeAction: 'close',
            iconCls: 'db-icn-upload-local',
            //modal : true,
            width: 730,
            bodyStyle:"padding: 0 7px;",
            //height: 225,
            height:230,
            buttonAlign: 'center',
            modal:true,
            buttons: buttons,
            items:{
                border:false
            },
            listeners:{
                render: function(){
                    //Create Object for the Drag & Drop Applet
                    //archive: './applets/addjobapplet.jar',
                    //code: 'com.saturn.applets.adddjob.AddJobApplet',
                    this.addJobApplet = this.getJSApplet({
                        //archive: './applets/soutils.jar',
                        //code: 'com.saturn.applets.adddjob.AddJobApplet',
                        archive: './applets/addjobapplet.jar',
                        code: 'com.saturn.applets.adddjob.AddJobApplet',
                        height: 160,
                        width: 700,
                        //i d: 'addJobApplet',
                        methods: ['getAdNumber','getConstraints','getCopyPath','getError','getFailureFunction','getFileListSize','getFtpList','getFtpToDelete','getListData','getLoadCompleteFunction','getRevision','getSuccessFunction','getTransferCompleteFunction','getVersion','isOpUpload','loadFromFTP','setAdNumber','setAdnVerRev','setConstraints','setFTPDetails','setFailureFunction','setLoadCompleteFunction','setOpUpload','setPassword','setRevision','setServerName','setSuccessFunction','setTransferCompleteFunction','setUserName','setVersion','startTransfer','setFileList','getFtpToDelete','setFileChooserCurrentDir','getFileChooserCurrentDir','setDebug']
                    });
                    this.addJobApplet.render(this.body.id);
                    /*if(this.showNotes){
                        this.setTitle("Respond: " + this.JobNumber);
                        //alert(this.Question);
                        Ext.getCmp("question_id").html="<div style='font-size:12px;'>Question:<br/>"+this.Question+"</div><br/>";
                        this.setHeight(380);
                        this.items.add(top_panel);
                    }*/
                    if(this.showNotes){
                        this.setTitle("Respond: " + this.JobNumber);
                        Ext.Ajax.request({
                            url:'?module=job&op=getResponseQuestion',
                            params:{
                                JobId:this.JobId
                            },
                            success:function(res){
                                var tmp = Ext.decode(res.responseText);
                                Ext.getCmp("question_id").body.update("<div style='font-size:12px;'>Question:<br/>"+tmp.Question+"</div><br/>");
                            }
                        });
                        this.setHeight(380);
                        this.items.add(top_panel);
                    }
                }
            }
        }));

        /**
         * Function Checks whether a job number exists or not.
         * If exists jobnumber will be set as the zipFileName to be used while uploading
         * Otherwise, this zipFilename will be set to a random number
         * @author: Ratheesh on 14th Sep 2009
         * @return  {Void}
         **/
        this.setZipFileName = function(){
            if(Ext.isEmpty(this.JobNumber))
            {
                this.zipFileName = Math.ceil((Math.random())*10000);
            }else{
                this.zipFileName = this.JobNumber;
            }
            
            this.zipFileName = "J" + ( (Ext.isEmpty(this.JobId))? "" : this.JobId ) + "_" + this.zipFileName;
            this.zipFileName = "U" + ( (Ext.isEmpty(_SESSION.UserId))? "" : _SESSION.UserId ) + this.zipFileName;            
            
            if(Ext.isFunction(this.uploadCallback)) {
                this.uploadSpecificCallback[this.zipFileName] = this.uploadCallback;
            }

            //Set Post data associated with this Zip
            var tmp = {
                'TODO'    : 'extractZip',
                'SiteId'  : this.SiteId,
                //'FilePath': new String(this.uploadFilePath)+'/'+fileName,
                'JobId'   : this.JobId,
                'ParentId': this.ParentId
            };
            if(!Ext.isEmpty(this.assetID)) tmp.AssetId = this.assetID;
            if(!Ext.isEmpty(this.AdvertiserId)) tmp.AdvertiserId = this.AdvertiserId;
            if(!Ext.isEmpty(this.viewRestricted)) tmp.ViewRestricted = this.viewRestricted;
            //If any AssetType (CO, WO, NONE, PROOF_PDF) is associated with upload pass that as well
            if(!Ext.isEmpty(this.AssetType)) tmp.AssetType = this.AssetType;
            this.post_data[this.zipFileName] = tmp;
        //--
        };

    },
    onClose: function(){
        this.close();
    },
    getJSApplet: function(){
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
                        //arg += "'" + arr[i].replace(/\'/g,'"').replace(/\\/g,'/') + "'";
                        arg += "'" + arr[i].replace(/\\/g,'/').replace(/\'/g, "\\\'") + "'";
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
                        log(e);
                    //catchError(e);
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
                    try {
                        eval(fnc);
                    } catch (e) {
                        throw e;
                    }
                    return ret;
                }
            };
        };

        //Create Applet Object and pass the passed JSON Object as argument
        return new JSApplet(arguments[0]);
    },
    initUploaderApplet: function() {
        var url  = '?module=dam&op=get_ftp_info';
        var obj = this;
        Ext.Ajax.request({
            // url : modURL+'/AssetServer/operations.php',
            url : url,
            method:'GET',
            scriptTag:true,
            params:{
                'SiteId'   : obj.SiteId
            },
            success:function(res,opt){
                try {
                    var tmp            = Ext.util.JSON.decode(res.responseText);
                    var ftp_host       = tmp.data.ftp_host;
                    var ftp_username   = tmp.data.ftp_username;
                    var ftp_password   = tmp.data.ftp_password;
                    obj.http_host      = tmp.data.ast_http_location;
                    obj.uploadFilePath = tmp.data.ftp_root;

                    //this.uploaderApplet.setFtpDetails('ftp2.expresskcs.com','atmxshare','$!atm@x*!');
                    obj.uploaderApplet.setFtpDetails(ftp_host,ftp_username,ftp_password);
                    obj.uploaderApplet.setFunctions("Application.AppletUploader.startUpload",null,"Application.AppletUploader.stopUpload");
                    obj.uploaderApplet.setFailedFn("Application.AppletUploader.uploadFailed");
                    obj.runner.stopAll();
                } catch (e) {
                    log(e);
                    if (obj.appletFailureCount==10) {
                        catchError("FTP Uploader Applet is not initialized: Permission denied.",null,null,true);
                        obj.runner.stopAll();
                    } else {
                        /*var task = {
                            run: this.initUploaderApplet.createDelegate(this),
                            interval: 1000 //1 second
                        };
                        obj.runner.start(task);*/
                        obj.appletFailureCount++;
                    }
                }
            }
        });
    },
    //Will be called from Applet when Upload Starts
    startUpload: function(fileName, status){
        procId = setTimeout('Application.AppletUploader.progressFn("'+fileName+'.zip'+'")',1000);
    },
    //Will be called from Applet when Upload Stoped
    stopUpload: function(fileName, arg2, arg3, arg4){
        try{
        //this.uploadStatusBar.hide();
        //this.uploadStatusBar.close();
        }catch(e){
            log(e);
        }
        var post_params;

        var tmpIndex = fileName.substring(0,fileName.indexOf('.'));

        //Ajax: Once Upload finished, send request to extract and process file ie.,
        // step:1 Extract zip file to the specified folder
        uploadCallback = this.uploadCallback;
        if(!Ext.isEmpty(this.uploadSpecificCallback[tmpIndex])){
            uploadCallback = this.uploadSpecificCallback[tmpIndex];
        }

        this.uploadStatusBar[tmpIndex].setMessage("<b>Checking for Duplicate Files</b>");
        
        post_params = this.post_data[tmpIndex];
        generateProofCallback = this.generateProofCallback;

        if(Ext.isEmpty(post_params)){
            post_params = {
                'TODO'    : 'extractZip',
                'SiteId'  : this.SiteId,
                'FilePath': new String(this.uploadFilePath)+'/'+fileName,
                'JobId'   : this.JobId,
                'ParentId': this.ParentId
            };
            if(!Ext.isEmpty(this.assetID)) post_params.AssetId = this.assetID;
            if(!Ext.isEmpty(this.AdvertiserId)) post_params.AdvertiserId = this.AdvertiserId;
            if(!Ext.isEmpty(this.viewRestricted)) post_params.ViewRestricted = this.viewRestricted;
        }
        post_params.FilePath = new String(this.uploadFilePath)+'/'+fileName;

        //If any AssetType (CO, WO, NONe, PRoof_PDF) is associated with upload pass that as well
        if(!Ext.isEmpty(this.AssetType)) post_params.AssetType = this.AssetType;

        /**
		 * Updated by Anju Prema
		 * Updated On 23 Dec 2009
		 *
		 * For Validation of File Name Duplication
		 */
        Ext.Ajax.request({
            url: '?module=dam&op=getDuplicateFiles',
            params: {
                JobId: this.post_data[tmpIndex].JobId,
                SiteId: this.post_data[tmpIndex].SiteId,
                ParentId: this.post_data[tmpIndex].ParentId,
                selectedFiles: this.post_data[tmpIndex].uploadedFiles
            },
            success:function(res, opt){
                var tmp = Ext.decode(res.responseText);
                if(tmp.success === true){
                    var duplicate = tmp.duplicate_files;
                    if(duplicate.length > 0){
                        //call function to select files to be renamed
                        Application.AppletUploader.listFilesToRename(tmp.duplicate_files,tmpIndex,post_params);
                    }else{
                        Application.AppletUploader.extractZipFile(post_params);
                    }
                }
            }
        });
        /********/

        //3) Save entries into DB

        try{
        //Destroy current Object
        //  this.close();
        }catch(e){
            log(e);
        }
    },
    uploadFailed: function(){},
    //Will be called from the applet to display the uploading progressbar
    progressFn: function(filename, progress){
        if (Ext.isEmpty(Ext.get('uploadProgressBar'))) {
            //Ext.get('uploadProgressBar').remove();
            Ext.DomHelper.append(Ext.getBody(), '<div id="uploadProgressBar" style="display:none;"></div>');
        }

        var progress = ""+this.uploaderApplet.getStatus();
        var progressHTML;

        if(isNaN(progress)){
            progressHTML = '<b>'+progress+"!</b>";
        }else{
            progressHTML    = 'Uploading : '+progress+"% completed!";
          //document.getElementById('console_tmp').innerHTML = '<b>Uploading : '+progress+"% completed!</b>";
        }
        if (parseInt(progress)!=100 && progress!='Finished') {
            setTimeout("Application.AppletUploader.progressFn('"+filename+"')",1000);
        }
        try{
            var tmpZipName = filename.substring(0,filename.indexOf('.'));

            //Ext.getCmp('taskbar').setMessage(progressHTML);
            //Ext.getCmp('taskbar').show();
            this.uploadStatusBar[tmpZipName].setMessage(progressHTML);
        //this.uploadStatusBar.show();
        }catch(e){

        }

    },
    uploadFiles: function (){
        var tWindow = this;
        if((this.showNotes==false||Ext.isEmpty(this.showNotes))&&this.addJobApplet.getFileListSize()==0){
            tWindow.hide();
            Ext.Msg.alert('No files added','Please add files to add to Upload Queue',function(){
                tWindow.show();
            });
        }else{
            this.hide();

            //Call function to set Target Fillename for the zip to be used for uploading
            this.setZipFileName();
            try {
                //this.addJobApplet.startTransfer("1","1","1", 'Application.AppletUploader.fileTransferComplete','appletFailed');

                if(this.fileTransferComplete()!==false){

                    this.uploadStatusBar[this.zipFileName] = new Ext.ux.window.MessageWindow({
                        hidden: true,
                        //id: 'taskbar',
                        title: 'Upload Progress',
                        autoDestroy: true,//default = true
                        autoHeight: true,
                        autoHide: true,//default = true
                        bodyStyle: 'text-align:center',
                        //id:'msg_upload_progress',
                        id:'uploadProgressBar4'+this.zipFileName,
                        closable: true,
                        help: false,//no help tool
                        html:'',
                        //html: 'This window is initially shown as pinned.  <b>Message</b> window hide mode. The height can be left to autosize itself, you can explicitly state a height, or just use the minimum default height.',
                        //iconCls:	'x-icon-error',
                        pinState: 'pin',//render pinned
                        //textUnpin:'click to close',
                        origin: {
                            offX: 10, //amount to offset horizontally (-20 by default)
                            offY: -30 //amount to offset vertically (-20 by default)
                        },
                        showFx: {
                            duration: 0.25, //defaults to 1 second
                            mode: 'standard',//null,'standard','custom',or default ghost
                            useProxy: false //default is false to hide window instead
                        },
                        width: 250 //optional (can also set minWidth which = 200 by default)
                    }).show(Ext.getDoc());

                    this.uploadStatusBar[this.zipFileName].setMessage("<b>Added to Upload Queue!</b>");
                    //Close Uploader window immediately after click on 'Upload' button.
                    this.hide();
                }
                /*
                 * Modified by LAkshmi.
                 * Purpose: If no files uploaded, then for respond action(ie, showNotes true), message box will ask user whether want to continue without uploading files.
                 * If no, upload applet will be shown, else upload callback will be called.
                 */
                else{
                    if(this.showNotes){
                        Ext.Msg.show({
                            title: 'Confirm',
                            msg: 'Are you sure you want to continue without uploading files?',
                            buttons: Ext.MessageBox.YESNO,
                            icon: Ext.MessageBox.WARNING,
                            width: 325,
                            fn: function(btn){
                                if(btn == 'no'){
                                    this.show();
                                //this.uploaderApplet.show();
                                }else{
                                    var tmpComments ="";
                                    if(!Ext.isEmpty(Ext.getCmp('comment_box'))) {
                                        tmpComments = Ext.getCmp('comment_box').getValue();
                                    }
                                    this.uploadCallback(tmpComments);
                                }
                            },
                            scope: this
                        });

                    }
                }
            }catch (e){
                log(e);
            }
        }
    },
    uploadJobFiles: function(conf){
        for (i in conf){
            this[i] = conf[i];
        }
        this.uploadFiles();
    },
    setConfig: function(conf){
        for (i in conf){
            this[i] = conf[i];
        }
    //RCK: 2009-11-02: 3:41pM   this.uploadFiles();
    },

    /**
     * Upload the given set of files (may be one or more).
     * What it does is:
     *  - Set Zip file name if it is not already given in the configuration object
     *  - Starts upload by calling 'fileTransferComplete' -
     *    actually 'fileTransferComplete' is the method communicates with Applet and initiates upload
     *  - then of course start showing progress - (acutally main reason for writing this function is this :D )
     *
     * @author: Ratheesh on 28th Nov 2009
     *
     * The object has the following properties. or this are the main configurations
     * @param   {Array/String- files}   List of files to be uploaded otherwise a single file path
     * @param   {String - progressInfoMsg}  Message needs to be displayed in the Progress Meter as title!
     * @param   {Object - jobData}  Complete/required job data as a single object
     * @param   {Object - uploaderConf}  Object to reconfigure this AppletUploader Object
     *
     * @return  {Void}
     **/
    startFileTransfer: function (cfg){

        //Call function to set Target Fillename for the zip to be used for uploading
        this.setZipFileName();
        try{
            var uploadPossible = this.fileTransferComplete(cfg.files);

            if(uploadPossible !== false){
                
                this.uploadStatusBar[this.zipFileName] = new Ext.ux.window.MessageWindow({
                    hidden: true,
                    //id: 'taskbar',
                    title: Ext.isEmpty(cfg.progressInfoMsg)?'Upload Progress':cfg.progressInfoMsg,
                    autoDestroy: true,//default = true
                    autoHeight: true,
                    autoHide: true,//default = true
                    bodyStyle: 'text-align:center',
                    closable: true,
                    help: false,//no help tool,
                    //id:'msg_start_file_transfer',
                    id:'uploadProgressBar4'+this.zipFileName,
                    html:'',
                    //html: 'This window is initially shown as pinned.  <b>Message</b> window hide mode. The height can be left to autosize itself, you can explicitly state a height, or just use the minimum default height.',
                    //iconCls:	'x-icon-error',
                    pinState: 'pin',//render pinned,
                    textUnpin:'click to close',
                    origin: {
                        offX: 10, //amount to offset horizontally (-20 by default)
                        offY: -30 //amount to offset vertically (-20 by default)
                    },
                    showFx: {
                        duration: 0.25, //defaults to 1 second
                        mode: 'standard',//null,'standard','custom',or default ghost
                        useProxy: false //default is false to hide window instead
                    },
                    width: 250 //optional (can also set minWidth which = 200 by default)
                }).show(Ext.getDoc());

                this.uploadStatusBar[this.zipFileName].setMessage("<b>Added to Upload Queue!</b>");
                this.uploadStatusBar[this.zipFileName].show();
            }else{
                if(Ext.isFunction(this.uploadCallback)) {
                    this.uploadCallback();
                }
            }
        }catch (e){
            catchError('Error in : Application.AppletUploader.startFileTransfer:'+e.toString());
        }
    },

    fileTransferComplete: function(arrStr){
        //var arrStr;
        /*
        try {
            arrStr = "" + this.addJobApplet.getListData().toString();
        } catch (e) {
            arrStr = "{error: {msg: 'Applet communication error'}}";
            catchError(e);
        }*/
        try {
            if(Ext.isEmpty(arrStr)){
                if(Ext.isEmpty(this.addJobApplet)){
                    return false;
                    if(this.uploaderApplet.getSelectedPath()!="" && !Ext.isEmpty(this.uploaderApplet.getSelectedPath())){
                        arrStr = '[';
                        arrStr += (arrStr == '[' ? '' : ',') + '"' + (this.uploaderApplet.getSelectedPath()).replace(/\\/g, '\\\\') + '"';
                        arrStr += ']';
                    }
                }else{
                    arrStr = "" + this.addJobApplet.getListData().toString();
                }
            }else{
                if(Ext.isArray(arrStr))
                    arrStr = Ext.encode(arrStr);
                else
                    arrStr = '["'+arrStr+'"]';
            }

            if(Ext.isEmpty(Ext.decode(arrStr)) || Ext.isEmpty(arrStr)){
                return false;
            }

            //var dest = ""+this.addJobApplet.getCopyPath();
            var dest = new String(this.zipFileName);

            /**
			 * Updated by Anju Prema
			 * Updated On 23 Dec 2009
			 *
			 * For Validation of File Name Duplication
			 */
            this.post_data[this.zipFileName].uploadedFiles = arrStr;
            this.uploaderApplet.queue(arrStr, this.uploadFilePath, dest.replace(/\\/g,'/'));

            //procId = setTimeout('Application.AppletUploader.progressFn("'+dest+'.zip'+'")',1000);
        } catch (e){
            catchError('Application.AppletUploader.fileTransferComplete-ftpUploader.queue:'+e.toString());
        }

        return true;
    },
    /**
    * Fetches the Selected files/folders from Uploader Applet and pass it to the callback function
    * @author: Ratheesh on 25th Sep 2009
    *
    * @return  {Void}
    **/
    assetSelected: function(){
        var selectedFiles, tmp;
        //eval('selectedFiles='+this.uploaderApplet.getSelectedFiles());
        selectedFiles = [this.uploaderApplet.getSelectedPath()];
        if (selectedFiles.length>0) {
            tmp = '[';
            for(var i=0;i<selectedFiles.length;i++) {
                //tmp += (tmp=='['?'':',')+'"'+selectedFiles[i].path.replace(/\\/g,'\\\\')+'"';
                tmp += (tmp=='['?'':',')+'"'+selectedFiles[i].replace(/\\/g,'\\\\')+'"';
            }
            tmp += ']';
        }
        this.uploadCallback(tmp);
    },
    /**
	 * List Files in Grid for the user to select files that needs to be replaced
	 * @author: Anju Prema on 24th Dec 2009
	 *
	 * @return  {Void}
	 */
    listFilesToRename: function(){
        var win_title   = 'Asset with the same name exists in the system, Select files that needs to be automatically renamed';
        var win_id      = "duplicate_asset";
        var zipFileName = arguments[1];
        var post_params = arguments[2];
        //create the window on the first click and reuse on subsequent clicks
        var select_property_window = Ext.getCmp(win_id);
        var duplicateFile_selection_model = new Ext.grid.CheckboxSelectionModel({
            multiSelect: true
        });

        Ext.grid.duplicateFiles  = arguments[0];
        var duplicate_file_store = new Ext.data.Store({
            reader: new Ext.data.ArrayReader({}, [
            {
                name: 'file_name'
            }
            ]),
            data: Ext.grid.duplicateFiles
        });

        if (Ext.isEmpty(select_property_window)) {
            duplicate_asset_window = new Ext.Window({
                id: win_id,
                layout: 'fit',
                width: 300,
                height: 400,
                plain: true,
                modal: true,
                frame: true,
                resizable: false,
                title: win_title,
                constrainHeader: true,
                maskDisabled: false,
                items: [new Ext.grid.GridPanel({
                    store: duplicate_file_store,
                    layout: 'fit',
                    id: 'duplicateFileGrid',
                    autoScroll: true,
                    columns: [duplicateFile_selection_model, {
                        header: 'File Name',
                        id: 'file_name',
                        sortable: true,
                        dataIndex: 'file_name'
                    }],
                    sm: duplicateFile_selection_model,
                    viewConfig: {
                        forceFit: true
                    },
                    stripeRows: true,
                    autoExpandColumn: 'file_name'
                })],
                buttons: [{
                    text: 'Done',
                    icon: IMAGE_BASE_PATH + '/default/icons/add.png',
                    iconCls: 'my-icon1',
                    handler: function(){
                        var arr = [];
                        var selectedRows = duplicateFile_selection_model.getSelections();
                        for (var i = 0; i < selectedRows.length; i++) {
                            arr[arr.length] = selectedRows[i].data;
                        }
                        post_params.renameFiles = Ext.encode(arr);
                        duplicate_asset_window.close();
                        Application.AppletUploader.extractZipFile(post_params);
                    }
                }, {
                    text: 'Cancel',
                    icon: IMAGE_BASE_PATH + '/default/icons/cancel.png',
                    iconCls: 'my-icon1',
                    handler: function(){
                        Application.AppletUploader.extractZipFile(post_params);
                        duplicate_asset_window.close();
                    }
                }]
            });
        }
        duplicate_asset_window.doLayout();
        duplicate_asset_window.show(this);
        duplicate_asset_window.center();
    },/**
	 * Function to extract Zip File
	 * @author: Anju Prema
	 * @created on:24 Dec 2009
	 */
    extractZipFile: function(post_params){
        var tmpIndex = (post_params.FilePath).substring(0,(post_params.FilePath).indexOf('.'));
            tmpIndex = tmpIndex.replace(/\//g, '');
        var tmpUploadStatusBar = this.uploadStatusBar[tmpIndex];
        if(!Ext.isEmpty(tmpUploadStatusBar)){
            tmpUploadStatusBar.setMessage("<b>Unzipping on Server and Copying to Job Jacket!</b>");
             }

        var verify_job_Asset = this.verifyJobAsset;       
        Ext.Ajax.request({
            url : 'http://'+this.http_host+'/AssetServer/operations.php',
            method: 'POST',
            params: post_params,
            timeout: 1800000,
            success:function(res, opt){
                if(Ext.isFunction(uploadCallback)){
                    if(!Ext.isEmpty(Ext.getCmp('comment_box'))) {
                        uploadCallback(Ext.getCmp('comment_box').getValue());
                    }
                    else {
                        uploadCallback();
                    }
                }

                //Condition added by Ratheesh on 24 DEC 09 07:30 PM
                //Purpose: We don't need to ask confirmation for
                //files like WO, CO & if the Job Id is blank or zero
                if (post_params.AssetType != "WO" && post_params.AssetType != "CO" && ((Ext.isNumber(post_params.JobId) ? post_params.JobId : 0) > 0)) {
                    /**
	                 * Updated by Anju Prema
	                 * Updated On 18 Dec 2009
	                 *
	                 * For Validation of Proof File
	                 */
                      
                    var tmp = Ext.decode(res.responseText);
                    if(verify_job_Asset === true){
                        //if(tmp.current_user_type=='PMOP')
                        if(tmp.proof_exist && tmp.proof_exist == 'no'){
                            Ext.Msg.confirm('Confirm','Have you uploaded Proof File?',function(btn){
                                if(btn == 'yes'){
                                    if(Ext.isFunction(generateProofCallback)){
                                        generateProofCallback(tmp.filter,'',true);
                                    }else{
                                        var initProof = {
                                            SiteId: post_params.SiteId,
                                            JobId: post_params.JobId,
                                            uploadCallback: function(){}
                                        };
                                        initProof.filter = {
                                            filetype: tmp.filter
                                        };
                                        // log('calling generate proof');
                                        var dam = new DAM(initProof);
                                        dam.generateProof(initProof,'',true);
                                    }
                                }
                            //Application.Jobs.getActiveGrid().store.reload();
                            //getActiveGrid().reload();
                            });
                        }
                    }else Application.Jobs.getActiveGrid().store.reload();
                }
               tmpUploadStatusBar.setMessage("<b>Completed!</b>");
               // Added by Preejith to destroy Upload Status Bar applet
               tmpUploadStatusBar.close();
            },
            failure: function(response, opts) {
                var obj = Ext.decode(response.responseText);
                tmpUploadStatusBar.setMessage("<b style='color:red'>" + obj.message + "</b>");
            }
        });
    }
});
