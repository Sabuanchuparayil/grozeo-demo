Ext.namespace('Ext.ux.panel');

Ext.ux.newComponent = Ext.extend(Ext.Panel, {
    border: false,
    hidden: true,
    url: '',
    initComponent: function () {

        this.addEvents(
                'servercommunication',
                'apicall'
                );


        Ext.ux.newComponent.superclass.initComponent.call(this);
        this.on('afterrender', this.apicall, this);

    },
    apicall: function () {
        console.log('afterrender');
        console.log(this);
    },
    afterRender: function () {
        Ext.ux.newComponent.superclass.afterRender.call(this);

    },
    servercommunication: function () {


    },
    start: function () {
       
    }
});

Ext.reg('newComponent', Ext.ux.newComponent); 