<!DOCTYPE html>
<html lang="en">
<?php
$captcha_image = "./captcha.php";
?>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SITE_TITLE ?> Login</title>
    <link href="./resources/custom_files/favicon.ico" rel="shortcut icon" type="image/x-icon">

    <link rel="stylesheet" type="text/css" href="./ext-3.4.0/resources/css/ext-all.css" />
    <link rel="stylesheet" type="text/css" href="./ext-3.4.0/resources/css/xtheme-gray.css" />
   

    <!-- Font Family -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">


    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="./resources/custom_files/bootstrap.min.css">

    <link rel="stylesheet" href="./resources/custom_files/custom.css">


    <script src="./resources/custom_files/bootstrap.js"></script>


    <script type="text/javascript" src="./ext-3.4.0/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="./ext-3.4.0/ext-all.js"></script>
    <script type="text/javascript" src="./js/webtoolkit.md5.js"></script>
    <script type="text/javascript" src="./js/login<?= $debugJs ?>.js"></script>
    <script type="text/javascript">
        function submitForgotPassword() {
            Ext.getCmp('forgot_pwd').form.submit({
                waitTitle: 'Please be Patient!',
                waitMsg: 'Verifying your Login credentials...',
                success: function(uForm, action) {
                    eval('var tmp=' + action.response.responseText);
                    if (tmp.success !== undefined && tmp.success === true) {
                        Ext.MessageBox.alert('Notification', "New Password has been sent to your Mail id..");
                        Ext.getCmp('forgot-pswd').close();
                    } else if (tmp.success === 'invalid') {
                        Ext.MessageBox.alert('Notification', "Please Verify supplied data!!");
                        Ext.getCmp('forgot_pwd_submit').enable();
                        Ext.getCmp('txtEmail').focus(true);
                    }
                },
                failure: function(uForm, action) {
                    if (action.failureType == 'server') {
                        obj = Ext.util.JSON.decode(action.response.responseText);
                        Ext.MessageBox.show({
                            title: 'Error!',
                            msg: obj.errors.reason,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR,
                            width: 325
                        });
                    }
                    Ext.getCmp('forgot_pwd_submit').enable();
                }
            });
        }

        function forgotPassword() {
            var form = new Ext.form.FormPanel({
                layout: 'form',
                url: '?module=auth&op=forgot-password',
                defaultType: 'textfield',
                labelAlign: 'left',
                frame: true,
                labelWidth: 50,
                id: 'forgot_pwd',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Email Id',
                    id: 'txtEmail',
                    allowBlank: false,
                    name: 'admin_email',
                    vtype: 'email',
                    tabIndex: 1,
                    width: 205
                }],
                keys: [{
                    key: Ext.EventObject.ENTER,
                    fn: function() {
                        if (Ext.getCmp('forgot_pwd').form.isValid()) {
                            Ext.getCmp('forgot_pwd_submit').disable();
                            submitForgotPassword();
                        }
                    }
                }]
            });

            var window = new Ext.Window({
                title: 'Forgot Password',
                width: 300,
                height: 130,
                minWidth: 300,
                minHeight: 130,
                layout: 'fit',
                plain: true,
                bodyStyle: 'padding:5px;',
                buttonAlign: 'center',
                id: 'forgot-pswd',
                items: form,
                buttons: [{
                    text: 'Submit',
                    id: 'forgot_pwd_submit',
                    tabIndex: 2,
                    handler: function() {
                        if (form.form.isValid())
                            submitForgotPassword();
                    }
                }, {
                    text: 'Cancel',
                    tabIndex: 3,
                    handler: function() {
                        Ext.getCmp('forgot-pswd').close();
                    }
                }]
            });
            window.doLayout();
            window.show();
            Ext.getCmp('txtEmail').focus(true, true);
            window.center();
        }
    </script>
</head>

<body class="hold-transition login-page">

    <div class="login_sec_wrp d-flex">



        <div class="login_img col-12 col-lg-6 p-4 d-none d-lg-flex flex-wrap align-item-center justify-content-center position-relative">
            <div class="infogrp d-flex justify-content-center align-item-center p-3">
                <img src="./resources/custom_files/login_bg.png">
            </div>
        </div>

        <div class="login-box col-12 col-lg-6 d-flex flex-nowrap">



            <div class="card">
                <div class="card-body login-card-body">
                    <form method="post" name="login" id="login" onSubmit="return false;">

                        <div class="login_head mb-4">

                            <h2>Sign in to continue...</h2>
                        </div><!--login_head-->

                        <div class="loginform_wrap">
                            <div class="form-row">
                                <div class="input-group mb-4 form-input d-flex align-items-center validate-input" data-validate="Valid email is required: ex@abc.xyz">
                                    <div class="form-ico"><img src="./resources/custom_files/mail.svg"></div>
                                    <input placeholder="Enter your Email" type="text" id="loginUsername" name="loginUsername" class="form-control" required="" value="" tabindex='1'>
                                </div>
                                <div class="input-group mb-4 form-input d-flex align-items-center validate-input" data-validate="Password is required">
                                    <div class="form-ico"><img src="./resources/custom_files/password.svg"></div>
                                    <input name="loginPassword" id="loginPassword" tabindex="2" placeholder="Enter your Password" class="form-control" required="" value="" type="password">
                                </div>
                                <div class="input-group mb-4 d-flex ">
                                    <div class="input-group inputcode d-flex validate-input" data-validate="Enter Captcha">
                                    <?php echo "<img src= " . $captcha_image . " class='cap-left' id='imgcaptcha'/>"; ?>
                                    </div>
                                    <div class="input-group mb-0 inputcode-answer">
                                        <input type="text" id="loginCaptcha" name="loginCaptcha" autocomplete="off" class="form-control" required="" value="" tabindex='3'>
                                        <input type="hidden" autocomplete="off" class="userlog-field-small" name="captcha_total" id="captcha_total" value="<?php echo $_SESSION['rand_code']; ?>" />
                                    </div>

                                    <div class="formtbtn col d-flex justify-content-end ml-3 mr-0 pr-0">
                                    <button class="btn btn-primary" type="submit" tabindex="4">
                                Login
                            </button>
                                    </div>
                                </div>
                            </div><!--form-row-->


                            <!-- form-row d-flex justify-content-between align-item-center -->



                        </div><!--loginform_wrap-->









                    </form>

                </div>
                <!-- login-card-body -->
            </div>
        </div>
        <!-- login-box -->

    </div>

    <noscript>Your browser does not support JavaScript!</noscript>
    <script src="plugins.js"></script>
    <script type="text/JavaScript">

        function readCookie(name) {
                                                                            var nameEQ = name + "=";
                                                                            var ca = document.cookie.split(';');
                                                                            for(var i=0;i < ca.length;i++) {
                                                                            var c = ca[i];
                                                                            while (c.charAt(0)==' ') c = c.substring(1,c.length);
                                                                            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
                                                                            }
                                                                            return null;
                                                                            }
                                                                            var ignorebroswer = "";
                                                                            var enbrowser	= 1;
                                                                            /*
                                                                            var enflash  	= 1;
                                                                            for (i=0; i < navigator.plugins.length; i++)
                                                                            {
                                                                            var name = navigator.plugins[i].name.toLowerCase();
                                                                            var pName  = (name.indexOf('flash') > -1) ? 'Flash': 'NA';
                                                                            if(pName=="Flash"){
                                                                            enflash=0;
                                                                            fName=navigator.plugins[i].description;
                                                                            pVerFlash=navigator.plugins[i].description.substring(16);

                                                                            if(parseFloat(pVerFlash.substring(0,4))<9) enflash=2;
                                                                            }
                                                                            }
                                                                            */

                                                                            var useragent = navigator.userAgent.toLowerCase();
                                                                            //.................. Browser Check .....................//
                                                                            var bName = (useragent.indexOf('firefox') > -1) ? 
                                                                            'FireFox': 
                                                                            ((navigator.appVersion.indexOf(' Chrome/') > -1) ? 'Chrome' : navigator.appName);

                                                                            if (bName == "FireFox") {
                                                                            var pos = useragent.lastIndexOf('/');
                                                                            var bVer = useragent.substring(pos + 1);
                                                                            if(parseFloat(bVer.substring(0,3))<3.5)
                                                                            enbrowser=0;
                                                                            }
                                                                            else if(bName == "Chrome"){
                                                                            var pos = useragent.lastIndexOf('/');
                                                                            var bVer = useragent.substring(pos + 1);
                                                                            if(parseFloat(bVer.substring(0,3))<20)
                                                                            enbrowser=0;
                                                                            }
                                                                            else {
                                                                            enbrowser=0;
                                                                            }
                                                                            ignorebroswer = readCookie('ignorebrowser');

                                                                            if(enbrowser == 0 && ignorebroswer != 'true'){
                                                                            window.location="sysreq.htm";
                                                                            }
                                                                            else{
                                                                            document.getElementById('login').style.display='block';
                                                                            }
                                                                        </script>
    <!--===============================================================================================-->
    <script src="./resources/custom_files/js/jquery-3.2.1.min.js"></script>
    <!--===============================================================================================-->
    <script src="./resources/custom_files/js/popper.js"></script>
    <script src="./resources/custom_files/js/bootstrap.min.js"></script>
    <!--===============================================================================================-->
    <script src="./resources/custom_files/js/select2.min.js"></script>
    <!--===============================================================================================-->
    <script src="./resources/custom_files/js/tilt.jquery.min.js"></script>
    <script>
        $('.js-tilt').tilt({
            scale: 1.1
        })
    </script>
    <!--===============================================================================================-->
    <script src="./resources/custom_files/js/main.js"></script>
</body>

</html>