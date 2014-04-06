define(function(require, exports, module){
    var urls = require('./com/urls.js');
    var utils = require('./com/utils.js');
    var fw = require('./frame/FormWindow.js');
    var df = require('./com/datadef.js');

    var ItemPanel = Ext.extend(Ext.grid.EditorGridPanel, {
        __make_id : function(name) {
            return this.module_id + name;
        },
        __get_comp : function(name) {
            var id = this.__make_id(name);
            return Ext.getCmp(id);
        },
        __get_comp_value : function(name, default_val){
            var comp = this.__get_comp(name);
            if(!comp)
                return default_val ? default_val : "";
            return comp.getValue();
        },

        pageSize : 50,

        constructor : function(module_id){
            var me = this;
            me.module_id = module_id;
            me.store = this.createStore();
            me.sm = new Ext.grid.CheckboxSelectionModel;
            ItemPanel.superclass.constructor.call(this, {
                id : "tab:"+module_id,
                title : "库存模块",
                closable : true,
                width : 600,
                height : 200,
                columns : this.createColumns(),
                tbar : this.createTbar(),
                bbar : new Ext.PagingToolbar({
                    store : this.store,
                    pageSize : this.pageSize,
                    displayInfo : true
                })
            })
        },

        createStore : function(){
            var data = new Ext.data.JsonStore({
                url : urls.item_url('get_item_list'),
                root : 'data',
                totalProperty : "total",
                autoLoad : true,
                fields : df.item_record
            });
            return data;
        },

        createColumns : function(){
            var me = this;
            return [
                new Ext.grid.RowNumberer,
                //me.sm,
                {header:"编号", dataIndex:"id", aligin:"left"},
                {header:"名称", dataIndex:"name", aligin:"left"},
                {header:"库存", dataIndex:"store", aligin:"left"},
                {header:"售出", dataIndex:"sale", aligin:"left"},
                me.operationBtn()
            ];
        },

        operationBtn : function(){
            var me = this;
            var renderer = function(value, metaData, record, rowIndex, colIndex, store){
                var editBtnId = utils.createGridBtn({
                    text: "编辑",
                    width : 60,
                    handler: function(){
                        me.editItemInfo(record);
                    }
                });
                return ('<div style="width:70px;float:left;"><span id="' + editBtnId + '" /></div>')
            };
            return {header:"操作", aligin:"left", width:80, renderer:renderer};
        },

        editItemInfo : function(record){
            var me = this;
            var win = new fw.FormWindowUi({
                title : "编辑商品信息",
                height : 200,
                action : urls.item_url('update_item_info')
            });
            win.addItem([
                {xtype : "textfield", fieldLabel : "编号", readOnly:true, allowBlank:false, name : "id", anchor : "95%", value : record.get('id')},
                {xtype : "textfield", fieldLabel : "名称", allowBlank:false, name : "name", anchor : "95%", value : record.get('name')},
                {xtype : "textfield", fieldLabel : "库存", allowBlank:false, name : "store", anchor : "95%", value : record.get('store')},
                {xtype : "textfield", fieldLabel : "售出", allowBlank:false, name : "sale", anchor : "95%", value : record.get('sale')}
            ]);
            win.show();
            win.onSubmitSuccess = function(result){
                utils.show_msg("操作成功");
                me.store.reload();
                win.close();
            };
        },

        createTbar : function(){
            var me = this;
            return [
                {xtype : "button", text : "调整库存", width : 80 , handler:function(){me.addStoreFrame(me);}}
            ];
        },

        addStoreFrame : function(){
            var me = this;
            var win = new fw.FormWindowUi({
                title : "调整库存",
                width : 400,
                height : 150,
                action : urls.item_url("add_item_store")
            });
            var item = {
                xtype : "combo", fieldLabel : "物品", hiddenName : "item", store : me.createComboStore(),
                displayField : "text", valueField : "value", mode : 'local', triggerAction : 'all', editable : false,
                emptyText : '请选择', allowBlank : false, blankText : "请选择", width : 150, forceSelection : true
            };
            win.addItem([
                item,
                {xtype:"textfield", fieldLabel:"数量", name:"count", allowBlank:false}
            ]);
            win.show();
            win.onSubmitSuccess = function(){
                utils.show_msg("操作成功");
                me.store.reload();
                win.close();
            }
        },

        createComboStore : function(){
            var data = new Ext.data.JsonStore({
                url : urls.item_url("get_item_combo"),
                root : "data",
                autoLoad : true,
                fields : df.comboBox_list
            });
            return data;
        }

    });

    exports.ItemPanel = ItemPanel;
});
