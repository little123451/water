define(function(require, exports, module){
    var utils = require('../com/utils.js');
    var cp = require('../CustomerPanel.js');
    var op = require('../OrderPanel.js');
    var ip = require('../ItemPanel.js');

    var MenuPanel = Ext.extend(Ext.tree.TreePanel, {

        constructor : function(){
            MenuPanel.superclass.constructor.call(this, {
                id : "menu-tree",
                root: this.createRoot(),
                width: 100
            });
        },

        createRoot : function(){
            var root = Ext.extend(Ext.tree.TreeNode, {
                constructor : function(){
                    root.superclass.constructor.call(this, {
                        id:"root",
                        text:"管理系统"
                    });
                    utils.show_menu(this,"customer","用户模块",cp.CustomerPanel);
                    utils.show_menu(this,"order","订单模块",op.OrderPanel);
                    utils.show_menu(this,"item","库存模块",ip.ItemPanel)
                }
            });
            return new root();
        }

    });

    exports.MenuPanel = new MenuPanel();
});
