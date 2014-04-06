define(function(require, exports){

    var GraphWindow = Ext.extend(Ext.Window, {

        constructor : function(cfg){

            GraphWindow.superclass.constructor.call(this, {
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
                resizable : true,
                width: cfg.width || 500,
                height: cfg.height || 400
            });
            this.store = cfg.store;
            this.chart = this.createGraphPanel(this);
            this.add(this.chart);
            this.doLayout();
        },

        createGraphPanel : function(me){
            return new Ext.Panel({
                items: {
                    xtype: 'linechart',
                    store: me.store,
                    xField: 'x',
                    yField: 'y'
                }
            });
        }

    });

    exports.GraphWindow = GraphWindow;
});