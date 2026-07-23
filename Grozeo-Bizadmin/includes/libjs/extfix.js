Ext.override(Ext.tree.TreeEventModel, {
    initEvents : function(){
        var el = this.tree.getTreeEl();
        el.on('click', this.delegateClick, this);
        if(this.tree.trackMouseOver !== false){
            var innerCt = Ext.fly(el.dom.firstChild);
            innerCt.on('mouseover', this.delegateOver, this);
            innerCt.on('mouseout', this.delegateOut, this);
        }
        el.on('dblclick', this.delegateDblClick, this);
        el.on('contextmenu', this.delegateContextMenu, this);
    }
});



/*----------------------------------------------------------*/
/* To Resolve ExtJS checkbox  regarding work on Ext 2.2
 * Refer: http://extjs.com/forum/showthread.php?t=43217
 * Yes, Checkbox setValue calls this.wrap, so prior to render, it will cause an error. Try this (untested):
 * No, you should always be able to set values regardless of whether or not the control is rendered -- this was simply an oversight on our part and will be fixed
 */
/*----------------------------------------------------------*/
Ext.form.Checkbox.override({
    setValue : function(v) {
        var checked = this.checked;
        this.checked = (v === true || v === 'true' || v == '1' || String(v).toLowerCase() == 'on');

        if(this.rendered){
            this.el.dom.checked = this.checked;
            this.el.dom.defaultChecked = this.checked;
            this.wrap[this.checked? 'addClass' : 'removeClass'](this.checkedCls);
        }

        if(checked != this.checked){
            this.fireEvent("check", this, this.checked);
            if(this.handler){
                this.handler.call(this.scope || this, this, this.checked);
            }
        }
    },

    afterRender : function(){
        Ext.form.Checkbox.superclass.afterRender.call(this);
        this.wrap[this.checked? 'addClass' : 'removeClass'](this.checkedCls);
    }
});

/* Override radiogroup set & get values */
/*
 * refer:http://extjs.com/forum/showthread.php?t=39161
 *
 */
Ext.override(Ext.form.RadioGroup, {
    getName: function() {
        return this.items.first().getName();
    },

    getValue: function() {
        var v;

        this.items.each(function(item) {
            v = item.getRawValue();
            return !item.getValue();
        });

        return v;
    },

    setValue: function(v) {
        this.items.each(function(item) {
            item.setValue(item.getRawValue() == v);
        });
    }
});

if (Ext.version == "3.0") {
    Ext.override(Ext.Element, {
        getColor: function(attr, defaultValue, prefix){
            var v = this.getStyle(attr), color = typeof prefix == "undefined" ? "#" : prefix, h;
            if (!v || /transparent|inherit/.test(v)) {
                return defaultValue;
            }
            if (/^r/.test(v)) {
                Ext.each(v.slice(4, v.length - 1).split(','), function(s){
                    h = parseInt(s, 10);
                    color += (h < 16 ? '0' : '') + h.toString(16);
                });
            } else {
                v = v.replace('#', '');
                color += v.length == 3 ? v.replace(/^(\w)(\w)(\w)$/, '$1$1$2$2$3$3') : v;
            }
            return (color.length > 5 ? color.toLowerCase() : defaultValue);
        }
    });
}


//Refer: http://extjs.com/forum/showthread.php?t=43120
Ext.layout.SlideLayout = Ext.extend(Ext.layout.FitLayout, {

    deferredRender : false,


    renderHidden : false,
    easing: 'none',
    duration: .5,
    opacity: 1,


    setActiveItem : function(itemInt){
        if (typeof(itemInt) == 'string') { 
            itemInt = this.container.items.keys.indexOf(itemInt);
        }
        else if (typeof(itemInt) == 'object') { 
            itemInt = this.container.items.items.indexOf(itemInt);
        }
        var item = this.container.getComponent(itemInt);
        if(this.activeItem != item){
            if(this.activeItem){
                if(item && (!item.rendered || !this.isValidParent(item, this.container))){
                    this.renderItem(item, itemInt, this.container.getLayoutTarget()); item.show();
                }
                var s = [this.container.body.getX() - this.container.body.getWidth(), this.container.body.getX() + this.container.body.getWidth()];
                this.activeItem.el.shift({
                    duration: this.duration,
                    easing: this.easing,
                    opacity: this.opacity,
                    x:(this.activeItemNo < itemInt ? s[0] : s[1] )
                });
                item.el.setY(this.container.body.getY());
                item.el.setX((this.activeItemNo < itemInt ? s[1] : s[0] ));
                item.el.shift({
                    duration: this.duration,
                    easing: this.easing,
                    opacity: 1,
                    x:this.container.body.getX()
                });
            }
            this.activeItemNo = itemInt;
            this.activeItem = item;
            this.layout();
        }

    },


    renderAll : function(ct, target){
        if(this.deferredRender){
            this.renderItem(this.activeItem, undefined, target);
        }else{
            Ext.layout.CardLayout.superclass.renderAll.call(this, ct, target);
        }
    }
});
Ext.Container.LAYOUTS['slide'] = Ext.layout.SlideLayout;


// Advanced validation types for date range, password fields etc.

Ext.apply(Ext.form.VTypes, {
    daterange: function(val, field){
    
       
        
        var date = field.parseDate(val);
        if (!date) {
            return;
        }
        if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
            var start = Ext.getCmp(field.startDateField);
            start.setMaxValue(date);
            start.validate();
            this.dateRangeMax = date;
        }
        else
        if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
            var end = Ext.getCmp(field.endDateField);
            end.setMinValue(date);
            end.validate();
            this.dateRangeMin = date;
        }
        
    },
    password: function(val, field){
        if (field.initialPassField) {
            var pwd = Ext.getCmp(field.initialPassField);
            return (val == pwd.getValue());
        }
        return true;
    },
    check_validation: function(val, field){
        //check_space = /^\s+|\s$/;
        var new_val = Ext.util.Format.trim(val);
        //var reg_exp=/\w+|\s+/;
        var reg_exp = /^\w+$|^\w+\s+\w+$/;
        if (!val.match(reg_exp)) {
            return false;
        }
        else {
            return true;
        }
    },
    passwordText: 'New and Confirm do not match'
});

Ext.override(Ext.ToolTip, {
    onTargetOver : function(e){
        if(this.disabled || e.within(this.target.dom, true)){
            return;
        }
        var t = e.getTarget(this.delegate);
        if (t) {
            this.triggerElement = t;
            this.clearTimer('hide');
            this.targetXY = e.getXY();
            this.delayShow();
        }
    },
    onMouseMove : function(e){
        var t = e.getTarget(this.delegate);
        if (t) {
            this.targetXY = e.getXY();
            if (t === this.triggerElement) {
                if(!this.hidden && this.trackMouse){
                    this.setPagePosition(this.getTargetXY());
                }
            } else {
                this.hide();
                this.lastActive = new Date(0);
                this.onTargetOver(e);
            }
        } else if (!this.closable && this.isVisible()) {
            this.hide();
        }
    },
    hide: function(){
        this.clearTimer('dismiss');
        this.lastActive = new Date();
        delete this.triggerElement;
        Ext.ToolTip.superclass.hide.call(this);
    }
});

/*
 *  Added By Lakshmi
 *  To fix the permission denied error with extjs and firefox
 *  Ref:
 */

Ext.lib.Event.resolveTextNode = Ext.isGecko ? function(node){
	if(!node){
		return;
	}
	var s = HTMLElement.prototype.toString.call(node);
	if(s == '[xpconnect wrapped native prototype]' || s == '[object XULElement]'){
		return;
	}
	return node.nodeType == 3 ? node.parentNode : node;
} : function(node){
	return node && node.nodeType == 3 ? node.parentNode : node;
};

Ext.override(Ext.grid.ColumnModel, {
    // private
    destroyConfig: function() {
        for (var i = 0, len = this.config.length; i < len; i++) {
            Ext.destroy(this.config[i]);
        }
    },

    destroy : function() {
        this.destroyConfig();
        this.purgeListeners();
    },

    setConfig: function(config, initial) {
        var i, c, len;
        if (!initial) { // cleanup
            delete this.totalWidth;
            this.destroyConfig();
        }

        // backward compatibility
        this.defaults = Ext.apply({
            width: this.defaultWidth,
            sortable: this.defaultSortable
        }, this.defaults);

        this.config = config;
        this.lookup = {};

        for (i = 0, len = config.length; i < len; i++) {
            c = Ext.applyIf(config[i], this.defaults);
            // if no id, create one using column's ordinal position
            if (Ext.isEmpty(c.id)) {
                c.id = i;
            }
            if (!c.isColumn) {
                var Cls = Ext.grid.Column.types[c.xtype || 'gridcolumn'];
                c = new Cls(c);
                config[i] = c;
            }
            this.lookup[c.id] = c;
        }
        if (!initial) {
            this.fireEvent('configchange', this);
        }
    }
});

/*
*  Ext Fix -- Added By LAkshmi Jayaram 
*  Reason: Html editor posting junk characters.
*  ie, avoid \u200b - Zero Width Space in code
*  SOURCE: https://www.extjs.com/forum/showthread.php?t=73293&page=2
*
*/
Ext.override(Ext.form.HtmlEditor, {
    // private
    defaultValue: (Ext.isOpera || Ext.isIE6) ? '&#160;' : '&#8203;',

    cleanHtml: function(html) {
        var dv = this.defaultValue;
        
        html = String(html);

        // if (html.length > 5) {
        if (Ext.isWebKit) { // strip safari nonsense
            html = html.replace(/\sclass="(?:Apple-style-span|khtml-block-placeholder)"/gi, '');
        }
        // }

        if (html.charCodeAt(0) == dv.replace(/\D/g, '')) {
            html = html.substring(1);
        }
        
        return html;
    }
});
/*
// FIx added fro correcting error in IE 9

if (typeof Range.prototype.createContextualFragment == "undefined") {
    Range.prototype.createContextualFragment = function (html) {
        var doc = window.document;
        var container = doc.createElement("div");
        container.innerHTML = html;
        var frag = doc.createDocumentFragment(), n;
        while ((n = container.firstChild)) {
            frag.appendChild(n);
        }
        return frag;
    };
}
*/




