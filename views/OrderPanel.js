define(function(require, exports, module){
    var urls = require('./com/urls.js');
    var utils = require('./com/utils.js');
    var fw = require('./frame/FormWindow.js');
    var gw = require('./frame/GraphWindow.js');
    var df = require('./com/datadef.js');

    var OrderPanel = Ext.extend(Ext.grid.EditorGridPanel, {
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
            OrderPanel.superclass.constructor.call(this, {
                id : "tab:"+module_id,
                title : "订单模块",
                closable : true,
                width : 600,
                height : 200,
                columns : this.createColumns(),
                tbar : this.createTbarA(),
                bbar : new Ext.PagingToolbar({
                    store : this.store,
                    pageSize : this.pageSize,
                    displayInfo : true
                }),
                listeners : {
                    render : function(){
                        me.store.load({params:{start:0, limit:me.pageSize}});
                        tbarB = me.createTbarB(module_id);
                        tbarB.render(me.tbar);
                    }
                }
            })
        },

        createStore : function(){
            var data = new Ext.data.JsonStore({
                url : urls.order_url('get_order_list'),
                root : 'data',
                totalProperty : "total",
                autoLoad : true,
                remoteSort : true,
                fields : df.order_record
            });
            return data;
        },

        createColumns : function(){
            var me = this;
            var status_render = function(value){
                if (value == 0) return '<font color="red">未结清</font>';
                    else return '<font color="green">已付款</font>'
            }
            return [
                new Ext.grid.RowNumberer,
                me.sm,
                {header:"编号", dataIndex:"id", aligin:"left"},
                {header:"出售", dataIndex:"sale", aligin:"left", sortable:true},
                {header:"回收", dataIndex:"recycle", aligin:"left", sortable:true},
                {header:"收货", dataIndex:"consignee", aligin:"left"},
                {header:"应付款", dataIndex:"pay", aligin:"left", sortable:true},
                {header:"状态", dataIndex:"status", aligin:"left", renderer:status_render},
                {header:"日期", dataIndex:"date", aligin:"left", sortable:true},
                {header:"送货", dataIndex:"deliver", aligin:"left"},
                {header:"备注", dataIndex:"mark", aligin:"left"}
            ];
        },

        createTbarA : function(){
            var me = this;
            return [
                {xtype : "button", text : "添加订单", width : 80 , handler:function(){me.addOrderFrame(me);}}, '-',
                {xtype : "button", text : "删除订单", width : 80 , handler:function(){me.deleteOrder(me)}}, '-',
                {xtype : "button", text : "销量趋势", width : 80 , handler:function(){me.saleTendency(me)}}, '-',
                {xtype : "button", text : "订单结数", width : 80 , handler:function(){me.orderPay(me)}}, '-'
            ];
        },

        createTbarB : function(){
            var me = this;
            var status_store = new Ext.data.SimpleStore({
                    fields : ['value','text'],
                    data :  [['1','已付款'],['0','未结清']]
            });
            var consignee = {
                xtype : "combo", id : me.__make_id("consignee_search"), store : me.createComboStore(),
                displayField : "text", valueField : "value", mode : 'local', triggerAction : 'all', editable : false,
                emptyText : '请选择', blankText : "请选择", width : 80
            };
            var status = {
                xtype : "combo", id : me.__make_id("status_search"), store :status_store,
                displayField : "text", valueField : "value", mode : 'local', triggerAction : 'all', editable : false,
                emptyText : '请选择', blankText : "请选择", width : 80
            };
            return new Ext.Toolbar({
                items:[
                    '出售：',{id : me.__make_id('sale_search'), xtype : 'textfield', width: 70},'-',
                    '回收：',{id : me.__make_id("recycle_search"), xtype : "textfield", width : 70},'-',
                    '日期：',{id : me.__make_id("date_down_search"), xtype : "datefield", width : 80, format:"y/m/d"},'--',
                    {id : me.__make_id("date_up_search"), xtype : "datefield", width : 80, format:"y/m/d"},'-',
                    '收货：',consignee,'-',
                    '状态：',status,'-',
                    '送货：',{id : me.__make_id("deliver_search"), xtype : "textfield", width : 100},'-',
                    {xtype : 'button', text : "过滤", width : 50, id : me.__make_id('search_btn'), handler: function(){
                        me.store.setBaseParam("sale", me.__get_comp_value('sale_search'));
                        me.store.setBaseParam("recycle", me.__get_comp_value("recycle_search"));
                        me.store.setBaseParam("date_up", me.__get_comp_value("date_up_search"));
                        me.store.setBaseParam("date_down", me.__get_comp_value("date_down_search"));
                        me.store.setBaseParam("consignee", me.__get_comp_value("consignee_search"));
                        me.store.setBaseParam("status", me.__get_comp_value("status_search"));
                        me.store.setBaseParam("deliver", me.__get_comp_value("deliver_search"));
                        me.store.load({params : {start: 0, limit: me.pageSize}});
                    }}
                ]
            });
        },

        createComboStore : function(){
            var data = new Ext.data.JsonStore({
                url : urls.order_url("get_consignee_list"),
                root : "data",
                autoLoad : true,
                fields : df.comboBox_list
            });
            return data;
        },

        addOrderFrame : function(me){

            var date = new Date();
            var d = date.getDate() < 10 ? "0"+date.getDate():date.getDate();
            var m = date.getMonth()+1;
            var y = date.getFullYear();

            var win = new fw.FormWindowUi({
                title : "添加订单",
                height : 280,
                action : urls.order_url('create_order')
            });
            var consignee = {
                xtype : "combo", fieldLabel : "收货", hiddenName : "consignee", store : me.createComboStore(),
                displayField : "text", valueField : "value", mode : 'local', triggerAction : 'all', editable : false,
                emptyText : '请选择', allowBlank : false, blankText : "请选择", width : 150, forceSelection : true
            };
            win.addItem([
                {xtype : "textfield", fieldLabel : "出售", allowBlank:false, name : "sale", anchor : "95%"},
                {xtype : "textfield", fieldLabel : "回收", allowBlank:false, name : "recycle", anchor : "95%"},
                {xtype : "datefield", fieldLabel : "日期", allowBlank:false, name : "date", format:'Y-n-d', value:y+"-"+m+"-"+d},
                consignee,
                {xtype : "textfield", fieldLabel : "送货", allowBlank:false, name : "deliver", anchor : "95%"},
                {xtype : "textfield", fieldLabel : "应付款", allowBlank:false, name : "pay", anchor : "95%"},
                {xtype : "textfield", fieldLabel : "备注", name : "mark", anchor : "95%"}
            ]);
            win.show();
            win.onSubmitSuccess = function(result){
                utils.show_msg("操作成功");
                me.store.reload();
                win.close();
            };
        },

        deleteOrder : function(me){
            var record = me.getSelectionModel().getSelections();
            var del = function(){
                utils.http_request(urls.order_url("delete_order"),{
                    id_str : id_str
                },function(result){
                    if (result.success) {
                        me.store.reload();
                    }
                    utils.show_msg(result.msg);
                })
            };
            //检查选项
            if (record.length == 0) {
                utils.show_msg("没有选择订单");
            } else {
                var id_str = '';
                for(var i=0; i<record.length; i++){
                    id_str += ','+record[i].get("id");
                }
            }
            utils.confirm("确认删除订单?",del);
        },

        saleTendency : function(me){
            var store = new Ext.data.JsonStore({
                url : urls.order_url("get_sale_tendency"),
                root : "data",
                autoLoad : true,
                fields : df.graph_record
            });
            var win = new gw.GraphWindow({
                store : store,
                width : 1000,
                title : "销量趋势"
            });
            win.show();
        },

        orderPay : function(me){
            var record = me.getSelectionModel().getSelections();
            var pay = function(){
                utils.http_request(urls.order_url("pay_order"),{
                    id_str : id_str
                },function(result){
                    if (result.success) {
                        me.store.reload();
                    }
                    utils.show_msg(result.msg);
                })
            };
            //检查选项
            if (record.length == 0) {
                utils.show_msg("没有选择订单");
            } else {
                var id_str = '';
                for(var i=0; i<record.length; i++){
                    id_str += ','+record[i].get("id");
                }
                utils.confirm("确认结数?",pay);
            }
        }

    });

    exports.OrderPanel = OrderPanel;
});
