/**
   * Overrides the default Ext.form.Form component to add information or help icons
   * with supplied information description as a tooltip for those icons, next to all the form components.
   *
   * @summary Two parameters are required to use this Ext Form extension. The first is a 'descriptionTitle'
   *	      which will replace the original fieldLabel property. The second is a 'description' which
   *		  represents the tooltip text that will be displayed upon a hover over the info icon.<b>
   * @published 23 October 2007
   * @version 0.1
   */

  // Create user extensions namespace (Ext.ux)
  Ext.namespace('Ext.ux');

  //var imgPath = "images/icons/";

    Ext.ux.FormWithInfoTip = function(config) {

		// call the parent constructor
		Ext.ux.FormWithInfoTip.superclass.constructor.call(this, config);

	};

	// f.descriptionname

	Ext.extend(Ext.ux.FormWithInfoTip, Ext.form.FormPanel, {
	});

  Ext.override(Ext.form.Field, {
  afterRender : function() {

        if(this.helpText){
            var label = findLabel(this);
            if(label)
            {
             	var helpImage = label.createChild({
             			tag: 'img',
             			src: '../images/default/icons/date.png',
             			style: 'margin-bottom: 0px; margin-left: 5px; padding: 0px;',
             			width: 10,
             			height: 11
             		});

                Ext.QuickTips.register({
                    target:  helpImage,
                    text: this.helpText,
                    enabled: true
                });
            }
          }
        Ext.form.Field.superclass.afterRender.call(this);
        this.initEvents();
  }
});

var findLabel = function(field) {

    var wrapDiv = null;
    var label = null

    //find form-item and label
    wrapDiv = field.getEl().up('div.x-form-item');
    if(wrapDiv)
    {
        label = wrapDiv.child('label');
    }
    if(label) {
        return label;
    }

    /*

    //find form-element and label?
    wrapDiv = field.getEl().up('div.x-form-element');
    if(wrapDiv)
    {
        label = wrapDiv.child('label');
    }
    if(label) {
        return label;
    }
    */

};