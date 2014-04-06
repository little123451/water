define(function(require, exports, module){

    function show_panel(module_id, create_func){
        var panel = Ext.getCmp('main-panel');
        var tab = panel.getComponent('tab:' + module_id);
        if (tab) {
            panel.setActiveTab(tab);
            return;
        }
        var subPanel = create_func(panel, module_id);
        panel.setActiveTab(panel.add(subPanel));
    }

    function show_menu(tree, module_id, text, panel){
        var node = new Ext.tree.TreeNode({
            id : "menu:"+module_id,
            text : text
        });
        node.on("click",function(node,event){
            show_panel(module_id, function(p,module_id){
                return new panel(module_id);
            });
        });
        tree.appendChild(node);
    }

    function createGridBtn(cfg){
        var btnId = Ext.id();
        var btn = (function(){
            return new Ext.Button(cfg).render(document.body, btnId);
        }).defer(1, this);

        return btnId;
    }

    function show_msg(msg){
        Ext.Msg.alert("提示", msg);
    }

    function http_request(url, param, callback){
        Ext.Ajax.request({
            url : url,
            params : param,
            success : function(resp){
                var ret = eval("("+resp.responseText+")");
                callback(ret);
            }
        })
    }

    function confirm(msg,callback){
        Ext.MessageBox.confirm("确认",msg,function(button,text){
            if (button == "yes") callback();
        })
    }

    exports.confirm = confirm;
    exports.http_request = http_request;
    exports.show_msg = show_msg;
    exports.createGridBtn = createGridBtn;
    exports.show_panel = show_panel;
    exports.show_menu = show_menu;

});