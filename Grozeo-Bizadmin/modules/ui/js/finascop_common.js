
//function to calculate maximum no.of rows a grid can accomodate
var finascop_update_recs_per_page = function (cmp) {

    var grid_row_height = 23;
    var row = cmp.getView().getRow(0);
    if (!Ext.isEmpty(row)) {
        grid_row_height = Ext.get(row).getHeight();
    }

    var grid_height = cmp.getInnerHeight();
    var recs_allowed = Math.round((grid_height - grid_row_height) / grid_row_height);
    var cmpPaginBar = cmp.getBottomToolbar();
    cmpPaginBar.pageSize = recs_allowed;
    cmpPaginBar.doLoad(0);
    return recs_allowed;
};


var addParams = function (c, o) {

    if (!Ext.isEmpty(o.params)) {
       if (!Ext.isEmpty(o.url)) {
            var t = new Date();
            var t_stamp = t.format("YmdHis");
            o.params.apikey = _SESSION.apikey;
            //console.log((_SESSION.t_stamp == "" || _SESSION.t_stamp == null ? "MIss " + t_stamp : "Hit " + _SESSION.t_stamp));
            o.params.tstamp = (_SESSION.t_stamp == "" || _SESSION.t_stamp == null ? t_stamp : _SESSION.t_stamp);
        }
    }

};
Ext.Ajax.on('beforerequest', addParams);
function APICall(params, next_process, form_data) {
    //next_process();
    //return;

    /*Ext.Msg.show({
        title: 'Saving!...',
        msg: '',
        progressText: 'Please Wait...',
        width: 300,
        progress: true,
        closable: false,
        wait: true
    });*/

    var t = new Date();
    var tstamp = t.format("YmdHis");
    var apikey = _SESSION.apikey;

    //var url = "https://9vr02w0h8a.execute-api.us-east-1.amazonaws.com/Staging/actionlog/" + apikey + "/" + tstamp;
    //var url = "https://4ja7np75bb.execute-api.ap-south-1.amazonaws.com/Production/actionlog/" + apikey + "/" + tstamp;
    var url = API_ACTIONLOG_URL + "/" + apikey + "/" + tstamp;
    //_SESSION.t_stamp = tstamp;
    //console.log(form_data);

    var json_data = convertInput(form_data);
    // console.log(json_data);
    var tst = JSON.parse(json_data);
    var data;
    params.userid = _SESSION.UserEmail + '(' + _SESSION.UserType + ')';
    params.bodytype = Object.keys(tst)[0];

    //params["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
    for (var p in tst) {
        if (tst.hasOwnProperty(p)) {
            data = JSON.stringify(tst[p]);
        }
    }
    Ext.Ajax.on('beforerequest', addParams);
    Ext.Ajax.request({
        url: url,
        method: 'POST',
        headers: params,
        jsonData: data,
        success: function (response, options) {
            Ext.Msg.hide();
             console.log('response',response);
            if (response.responseText == '{}') {
                savingProgress();
                next_process();

            } else {
                console.log(response);
                Ext.Msg.alert('Notification', 'Error occured during the process.');
            }
        },
        failure: function (elm, msg) {
            console.log(elm);
            //Ext.Msg.alert('Notification', elm.statusText);
            savingProgress();
                next_process();
        }
    });
}
;

function convertInput(data, options) {
    options = options || {};
    var type = typeof (data);

    if (Object.prototype.toString.call(data) == '[object Array]') {
        var list = "{\"L\": [";

        for (var i = 0; i < data.length; i++) {

            if (i == 0) {
                list = list + convertInput(data[i], options);
            } else {
                list = list + "," + convertInput(data[i], options);
            }
        }
        list = list + "]}"
        return list;
    } else if (Object.prototype.toString.call(data) == '[object Null]' || type === 'null') {
        return "{\"NULL\": true }";
    } else if (type === 'object') {
        var map = "{\"M\": {";
        var t = 0;
        for (var key in data) {
            if (t == 0) {
                map = map + "\"" + key + "\":" + convertInput(data[key], options);
            } else {
                map = map + ",\"" + key + "\":" + convertInput(data[key], options);
            }
            t++;
        }
        map = map + "}}";
        return map;
    } else if (type === 'string') {
        //if (data.length === 0 && options.convertEmptyValues) {
        if (data.length === 0) {
            return convertInput(null);
        }
        var testdata = "[" + data + "]";

        if (!IsJsonString(testdata)) {
            data = addslashes(data);
        }
        return "{\"S\":\"" + data + "\"}";
    } else if (type === 'number') {
        return "{\"N\":\"" + data + "\"}";

    } else if (type === 'boolean') {
        return "{\"BOOL\":" + data + "}";
    }
}

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function addslashes(string) {
    return string.replace(/\\/g, '\\\\').
            replace(/\u0008/g, '\\b').
            replace(/\t/g, '\\t').
            replace(/\n/g, '\\n').
            replace(/\f/g, '\\f').
            replace(/\r/g, '\\r').
            replace(/"/g, '\\"');
}

function savingProgress() {
    Ext.Msg.show({
        msg: 'Please wait to complete the process...',
        title: 'Wait...',
        // progressText: 'Saving...',
        width: 300,
        closable: false,
        wait: true
    });
}

/*to fix issue of vtype email taking spaces while copy pasting*/
Ext.apply(Ext.form.VTypes, {
    emailText: "Not a valid email address. Must be in the following format: yourname@company.domain",
    emailRe: /^(\w+)([-+.][\w]+)*@(\w[-\w]*\.){1,5}([A-Za-z]){2,4}$/,
    email: function (v) {
        return this.emailRe.test(v);
    }
});
/*end; to fix issue of vtype email taking spaces while copy pasting*/

/*checking the GST number is valid or not */
Ext.apply(Ext.form.VTypes, {
    gstText: "Not a valid GST Number.",
    gstRe: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/,
    gst: function (v) {
        return this.gstRe.test(v);
    }
});
/*end; checking the GST number is valid or not*/
/*checking the GST number is valid or not */
Ext.apply(Ext.form.VTypes, {
    hsnText: "Not a valid HSN Number.",
    hsnRe: /^[0-9]*$/,
    hsn: function (v) {
        return this.hsnRe.test(v);
    }
});
/*end; checking the GST number is valid or not*/




/*validation  to restrict special characters*/
Ext.apply(Ext.form.VTypes, {
    daterange: function (val, field) {

        var date = field.parseDate(val);

        if (!date) {
            return;
        }
        if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
            var start = Ext.getCmp(field.startDateField);
            start.setMaxValue(date);
            start.validate();
            this.dateRangeMax = date;
        } else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
            var end = Ext.getCmp(field.endDateField);
            end.setMinValue(date);
            end.validate();
            this.dateRangeMin = date;
        }

    }
});

Ext.form.VTypes['custdataStringspecVal'] = /^([A-Z0-9a-z])[A-Z0-9a-z\s\.&@#]+$/;
Ext.form.VTypes['custdataStringspecMask'] = /[A-Z0-9a-z\s\.&@#]/;
Ext.form.VTypes['custdataStringspecText'] = 'Enter valid data';
Ext.form.VTypes['custdataStringspec'] = function (v) {
    return Ext.form.VTypes['custdataStringspecVal'].test(v);
};

Ext.form.VTypes['SPStringspecVal'] = /^[A-Z0-9a-z_]+$|^[A-Z0-9a-z_]+([A-Z0-9a-z_]+)+$/;
Ext.form.VTypes['SPStringspecMask'] = /[A-Z0-9a-z_]/;
Ext.form.VTypes['SPStringspecText'] = 'Enter valid data';
Ext.form.VTypes['SPStringspec'] = function (v) {
    return Ext.form.VTypes['SPStringspecVal'].test(v);
};

var removeFalsy = function removeFalsy(obj) {
    var newObj = {};
    Object.keys(obj).forEach(function (prop) {
        if (obj[prop]) {
            newObj[prop] = obj[prop];
        }
    });
    return newObj;
};

function removeEmptyStringElements(obj) {
    for (var prop in obj) {
        if (typeof obj[prop] === 'object') {// dive deeper in
            removeEmptyStringElements(obj[prop]);
        } else if (obj[prop] === '') {// delete elements that are empty strings
            delete obj[prop];
        }
    }
    return obj;
}