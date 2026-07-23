Ext.ux.FieldHelp = Ext.extend(Object, {
    constructor: function(t) {
        this.helpText = t;
    },

    init: function(f) {
        f.helpText = this.helpText;
        f.afterRender = f.afterRender.createSequence(this.afterFieldRender);
    },

    afterFieldRender: function() {
        if (!this.wrap) {
            this.wrap = this.el.wrap({cls: 'x-form-field-wrap'});
        }
        this.wrap.createChild({
            cls: 'x-form-helptext',
            html: this.helpText
        });
    }
});
