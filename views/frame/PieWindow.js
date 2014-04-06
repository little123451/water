define(function(require, exports){
    var PieWindow = Ext.extend(Ext.Window, {

        constructor : function(cfg){

            PieWindow.superclass.constructor.call(this, {
                title: cfg.title || '',
                layout: 'fit',
                autoScroll: false,
                style: 'padding:2px;',
                bodyStyle: 'padding:5px;',
                border: false,
                modal: true,
                frame : true,
                closable : cfg.closable == undefined ? true : cfg.closable,
                autoDestroy : cfg.autoDestroy == undefined ? true : cfg.autoDestroy,
                closeAction : cfg.closeAction || "close",
                resizable : cfg.resizable,
                width: cfg.width || 500,
                height: cfg.height || 400
            });
            this.store = cfg.store;
            this.chart = this.createChartPanel(this);
            this.add(this.chart);
            this.doLayout();
        },

        createChartPanel : function(me){
            return new Ext.Panel({
                items: {
                    xtype: 'piechart',
                    dataField: 'data',
                    categoryField: 'cate',
                    store: me.store,
                    //extra styles get applied to the chart defaults
                    extraStyle: {
                        legend: {
                            display: 'right',
                            padding: 5,
                            font: {family: 'Tahoma', size: 13}
                        }
                    }
                }
            });
        }

    });

    exports.PieWindow = PieWindow;
});