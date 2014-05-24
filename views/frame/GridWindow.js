define(function(require, exports){
    var GridWindow = Ext.extend(Ext.Window, {

        constructor : function(cfg){

            GridWindow.superclass.constructor.call(this, {

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
                resizable : cfg.resizable == undefined ? true : cfg.resizable,
                width: cfg.width || 500,
                height: cfg.height || 400
            });

            //是否在点击提交时提示确定
            this.need_confirm = (cfg.need_confirm == undefined ? true : cfg.need_confirm);

            //创建表单
            var g = Ext.extend(Ext.grid.GridPanel, {
                constructor : function(){
                    g.superclass.constructor.call(this, {
                        autoScroll : true,
                        sm : cfg.sm,
                        tbar : cfg.tbar,
                        store : cfg.store,
                        columns : cfg.columns,
                        autoExpandColumn: cfg.autoExpandColumn == undefined ? 0 : cfg.autoExpandColumn
                    });
                }
            });

            this.grid = new g;

            this.add(this.grid);

            this.doLayout();
        },

        reload : function(){
            this.grid.store.reload();
        },

        getPanel : function(){
            return this.grid;
        }

    });

    exports.GridWindow = GridWindow;
});