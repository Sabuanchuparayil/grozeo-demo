<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
$captcha_image = "./captcha.php";
?>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= SITE_TITLE ?> Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="./resources/mypharmacy/img/favicon.ico" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="./resources/mypharmacy/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="./resources/mypharmacy/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="./resources/mypharmacy/css/animate.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="./resources/mypharmacy/css/hamburgers.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="./resources/mypharmacy/css/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="./resources/mypharmacy/css/util.css">
    <link rel="stylesheet" type="text/css" href="./resources/mypharmacy/css/main.css">


    <link rel="stylesheet" type="text/css" href="./ext-3.4.0/resources/css/ext-all.css" />
    <link href='http://fonts.googleapis.com/css?family=Raleway:400,700,300,900' rel='stylesheet' type='text/css' />
    <!-- <link rel="stylesheet" type="text/css" href="./resources/themes/xtheme-vistablack/css/xtheme-vistablack.css">-->
    <link rel="stylesheet" type="text/css" href="./ext-3.4.0/resources/css/xtheme-gray.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display|Sedgwick+Ave+Display" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./resources/css/styles.css" />
    <link rel="stylesheet" type="text/css" href="./resources/css/login.css" />
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
<style type="text/css">
    body {
        display: table;
        margin: 0;
    }

    html,
    body {
        height: 100%;
        width: 100%;
    }

    .cap-left {
        float: left;
        padding-right: 10px;
    }
</style>

<body>
    <section>
        <div class="limiter">
            <div class="container-login100">
                <div class="wrap-login100">
                    <div class="login100-pic js-tilt" data-tilt>
                        <img src="./resources/mypharmacy/img/logo_admin.png" alt="IMG">
                    </div>

                    <form class="login100-form validate-form" role="form" name="login" style="display:none;" id="login" onSubmit="return false;" method="post">
                        <span class="login100-form-title">
                            <span class="detailtxt">Sign in to continue...</span>
                        </span>

                        <div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
                            <input class="input100" id="loginUsername" name="loginUsername" tabindex="1">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" name="loginPassword" id="loginPassword" tabindex="2" placeholder="Password" type="password" tabindex="2">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                        </div>
                        <div class="wrap-input46 validate-input" data-validate="Enter Captcha">
                            <?php echo "<img src= " . $captcha_image . " class='cap-left' id='imgcaptcha'/>"; ?>
                        </div>
                        <div class="wrap-input54 validate-input" data-validate="Enter Captcha">
                            <input class="input100 input54" name="loginCaptcha" autocomplete="off" type="text" id="loginCaptcha" size="5" maxlength="2" tabindex='3' placeholder="">
                            <input type="hidden" autocomplete="off" class="userlog-field-small" name="captcha_total" id="captcha_total" value="<?php echo $_SESSION['rand_code']; ?>" />

                        </div>
                        <div class="container-login100-form-btn">
                            <button class="login100-form-btn" type="submit" tabindex="4">
                                Login
                            </button>
                        </div>

                        <!--                                                                                            <div class="text-center p-t-12">
                                                                                                <span class="txt1">
                                                                                                    Forgot
                                                                                                </span>
                                                                                                <a class="txt2" href="#">
                                                                                                    Username / Password?
                                                                                                </a>
                                                                                            </div>-->

                    </form>
                </div>
            </div>
        </div>



    </section>


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
    <script src="./resources/mypharmacy/js/jquery-3.2.1.min.js"></script>
    <!--===============================================================================================-->
    <script src="./resources/mypharmacy/js/popper.js"></script>
    <script src="./resources/mypharmacy/js/bootstrap.min.js"></script>
    <!--===============================================================================================-->
    <script src="./resources/mypharmacy/js/select2.min.js"></script>
    <!--===============================================================================================-->
    <script src="./resources/mypharmacy/js/tilt.jquery.min.js"></script>
    <script>
        $('.js-tilt').tilt({
            scale: 1.1
        })
    </script>
    <!--===============================================================================================-->
    <script src="./resources/mypharmacy/js/main.js"></script>
</body>

</html>