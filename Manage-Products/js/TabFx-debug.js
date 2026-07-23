Ext.ns("Ext.ux", "Ext.ux.plugins");

Ext.ux.plugins.TabFx = Ext.extend(Ext.TabPanel, {
    cfg : { name: "frame", args: [] },
    constructor: function (cfg){
        Ext.apply(this.cfg, cfg);    
    },
    init : function (tp) {
        tp._setActiveTab = tp.setActiveTab;
        var self = this;
        tp.setActiveTab = function (item) {
            tp._setActiveTab(item);
            item = Ext.fly(tp.getTabEl(item));
            item[self.cfg.name].apply(item, Ext.isArray(self.cfg.args) ? self.cfg.args : []);
        };
    }
});
