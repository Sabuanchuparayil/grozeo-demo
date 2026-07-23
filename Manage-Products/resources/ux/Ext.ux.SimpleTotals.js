/**
 * @class Ext.ux.GridTotals
 * <p>Copyright Nige (Animal) White and Athena Capital Research, LLC. This software may be used under
 * the terms of the <a href="http://en.wikipedia.org/wiki/BSD_licenses">BSD licence</a></p>
 * <p>Displays a fixed row at the bottom of the scroller containing totals of all
 * numeric columns.</p>
 */
Ext.ux.GridTotals = Ext.extend(Ext.util.Observable, {

    init: function(g) {
        g.cls = (g.cls || '') + 'x-grid3-simple-totals';
        var v = g.getView();
        g.gridTotals = this;
        this.grid = g;
        this.store = g.getStore();
        this.store.on({
            reconfigure: this.onGridReconfigure,
            scope: this
        });
        v.updateTotals = this.updateTotals;
        v.fixScrollerPosition = this.fixScrollerPosition;
        v.onLayout = v.onLayout.createSequence(this.onLayout);
        v.initElements = v.initElements.createSequence(this.initElements);
        v.onAllColumnWidthsUpdated = v.onAllColumnWidthsUpdated.createSequence(this.onLayout);
        v.onColumnWidthUpdated = v.onColumnWidthUpdated.createSequence(this.onLayout);
    },

    initElements: function() {
        var me = this;
        this.scroller.on('scroll', function() {
            me.totalsRow.setStyle({
                left: -me.scroller.dom.scrollLeft + 'px'
            });
        });
    },

    onLayout: function() {
        this.updateTotals();
        this.fixScrollerPosition();
    },

    fixScrollerPosition: function() {
        var bottomScrollbarWidth = this.scroller.getHeight() - this.scroller.dom.clientHeight;
        this.totalsRow.setStyle({
            bottom: bottomScrollbarWidth + 'px',
            width: Math.min(this.mainBody.getWidth(), this.scroller.dom.clientWidth) + 'px'
        });
    },

    updateTotals: function() {
        if (!this.totalsRow) {
            this.mainWrap.setStyle('position', 'relative');
            this.totalsRow = this.templates.row.append(this.mainWrap, {
                tstyle: 'width:' + this.mainBody.getWidth(),
                cells: ''
            }, true);
            this.totalsRow.addClass('x-grid-total-row');
            this.totalsTr = this.totalsRow.child('tr').dom;
        }
        var cs = this.getColumnData();
        var totals = new Array(cs.length);
        var store = this.grid.store;
        var fields = store.recordType.prototype.fields;
        for (var i = 0, l = this.grid.store.getCount(); i < l; i++) {
            var rec = store.getAt(i);
            for (var c = 0, nc = cs.length; c < nc; c++) {
                var f = cs[c].name;
                var t = fields.get(f).type;
                if (t.type == 'int' || t.type == 'float') {
                    var v = rec.get(f);
                    if (Ext.isDefined(totals[c])) {
                        totals[c] += v;
                    } else {
                        totals[c] = v;
                    }
                }
            }
        }
        var cells = '', p = {};
        for (var c = 0, nc = cs.length, last = nc - 1; c < nc; c++) {
            p.id = cs[c].id;
            p.style = cs[c].style;
            p.css = i == 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
            var v = Ext.isDefined(totals[c]) ? totals[c] : '';
                cells += this.templates.cell.apply(Ext.apply({
                    value: cs[c].renderer(v, p)
                }, cs[c]));
        }
        Ext.DomHelper.overwrite(this.totalsTr, cells);
    },

    onGridReconfigure: Ext.emptyFn
});
