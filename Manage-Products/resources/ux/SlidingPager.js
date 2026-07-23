/*!
 * Ext JS Library 3.0.0
 * Copyright(c) 2006-2009 Ext JS, LLC
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.ux.SlidingPager = Ext.extend(Object, {
    init : function(pbar){
        Ext.each(pbar.items.getRange(2,6), function(c){
            c.hide();
        });

        var slider_conf = {
            /*width: 114,*/
            width: 200,
            minValue: 1,
            maxValue: 1,
            plugins: new Ext.ux.SliderTip({
                getText : function(s){
                    return String.format('Page <b>{0}</b> of <b>{1}</b>', s.value, s.maxValue);
                }
            }),
            listeners: {
                changecomplete: function(s, v){
                    pbar.changePage(v);
                }
            }
        };

        /*Ext.each(Object, function(obj){

		});*/

        var slider = new Ext.Slider(slider_conf);
        pbar.insert(5, slider);
        pbar.on({
            change: function(pb, data){
                slider.maxValue = data.pages;
                slider.setValue(data.activePage);
            },
            beforedestroy: function(){
                slider.destroy();
            }
        });
    }
});