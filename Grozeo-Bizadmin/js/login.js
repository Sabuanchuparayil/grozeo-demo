Ext.SSL_SECURE_URL = "resources/images/default/s.gif";
Ext.BLANK_IMAGE_URL = "resources/images/default/s.gif";

Login = function () {

    var submitUrl = '?module=auth&op=authenticate';
    var success_url = '';

    var showSpinner = function () {
        Ext.Msg.show({
            title: 'Please Wait!',
            msg: 'Verifying your login details to proceed.',
            progressText: 'Authenticating...',
            width: 300,
            progress: true,
            closable: false,
            wait: true
        });
    };

    var hideSpinner = function () {
        Ext.Msg.hide();
    };

    var submitHandler = function () {
        Ext.Ajax.request({
            url: submitUrl,
            params: {
                loginUsername: Ext.get('loginUsername').dom.value,
                loginCaptcha: Ext.get('loginCaptcha').dom.value,
                loginPassword: (Ext.get('loginPassword').dom.value != "") ? MD5(Ext.get('loginPassword').dom.value) : "",
                 rememberMe: Ext.get('rememberMe').dom.checked?1:0
            },
            success: function (res) {

                var tmp;
                try {
                    tmp = JSON.parse(res.responseText);
                } catch (e) {
                    Ext.MessageBox.show({
                        title: 'Error!',
                        msg: 'Unexpected server response. Please try again.',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR,
                        width: 325
                    });
                    return;
                }

                if (tmp.success === true) {
                    window.location = success_url;
                }
                else {
                    setTimeout(function () {
                        var src = Ext.get('imgcaptcha').dom.src.split('?')[0];
                        Ext.get('imgcaptcha').dom.src = src + '?v=' + Math.random();
                        Ext.get('loginCaptcha').dom.value = '';
                    }, 10);
                    Ext.MessageBox.show({
                        title: 'Error!',
                        msg: (tmp.errors && tmp.errors.reason) ? tmp.errors.reason : 'Login failed. Please try again.',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR,
                        width: 325
                    });
                }
            },
            failure: function (res) {
                console.log(res);
                Ext.Msg.alert('Warning!', 'Authentication server is unreachable, please try later  ');
            }
        });

        //Prevenet the Form from normal submission
        return false;
    };

    return {
        init: function () {
            Ext.Ajax.defaultHeaders = {
                'Powered-By': 'Ext'
            };

            //Set focus to the UserName field on Load
            Ext.get('loginUsername').focus();

            //Set Event & Handler for the Submit Button
            Ext.get('login').on('submit', submitHandler);

            Ext.Ajax.on('beforerequest', showSpinner, this);
            Ext.Ajax.on('requestcomplete', hideSpinner, this);
            Ext.Ajax.on('requestexception', hideSpinner, this);
        }
    };
}();
/*
 * Note the parentheses (); this notation causes the anonymous
 * function to execute immediately, dumping us right into the
 * Private Area where we step through to load all Private Variables and
 * take an inventory of all of the Private Functions, finally the return
 * in the "Public Area" is executed in similar fashion but since we are
 * "returning" we now see this "Public Area" outside of the module which
 * gives us the ability to execute the line below which fires the
 * initialization method in the Public Area.
 */

/*
 * Since the above code has already executed, we are able to access any Public
 * Properties (Public Variables and Public Methods), including the "init"
 * method immediately.
 *
 * The following execution line executes the Login.init method after the
 * document has been completely loaded. This line also sets the Login.init
 * method scope to Login module, which means you can call Public Attributes (methods
 * and properties) with a preceding 'this'.
 */
Ext.onReady(Login.init, Login, true);
