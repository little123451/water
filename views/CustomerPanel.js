define(function(require, exports, module){
    var urls = require('./com/urls.js');
    var utils = require('./com/utils.js');
    var fw = require('./frame/FormWindow.js');
    var pw = require('./frame/PieWindow.js');
    var df = require('./com/datadef.js');

    var CustomerPanel = Ext.extend(Ext.grid.EditorGridPanel, {
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
            CustomerPanel.superclass.constructor.call(this, {
                id : "tab:"+module_id,
                title : "用户模块",
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
            });
        },

        createStore : function(){
            var data = new Ext.data.JsonStore({
                url : urls.customer_url("get_customer_list"),
                root : 'data',
                totalProperty : 'total',
                autoLoad : true,
                remoteSort : true,
                fields : df.customer_record
            });
            return data;
        },

        createColumns : function(){
            var me = this;
            return [
                new Ext.grid.RowNumberer,
                me.sm,
                {header:"编号", dataIndex:"id", aligin:"left"},
                {header:"名称", dataIndex:"name", aligin:"left"},
                {header:"地址", dataIndex:"address", aligin:"left"},
                {header:"电话", dataIndex:"tel", aligin:"left"},
                {header:"售出", dataIndex:"sale", aligin:"left", sortable:true},
                {header:"空桶", dataIndex:"owe", aligin:"left", sortable:true},
                {header:"未付款", dataIndex:"debt", aligin:"left", sortable:true},
                me.operationBtn()
            ]
        },

        editCustomerInfo : function(record){
            var me = this;
            var win = new fw.FormWindowUi({
                title : "编辑用户信息",
                height : 200,
                action : urls.customer_url('update_customer_info')
            });
            win.addItem([
                {xtype : "textfield", fieldLabel : "编号", readOnly:true, allowBlank:false, name : "id", anchor : "95%", value : record.get('id')},
                {xtype : "textfield", fieldLabel : "名称", allowBlank:false, name : "name", anchor : "95%", value : record.get('name')},
                {xtype : "textfield", fieldLabel : "地址", allowBlank:false, name : "address", anchor : "95%", value : record.get('address')},
                {xtype : "textfield", fieldLabel : "电话", allowBlank:false, name : "tel", anchor : "95%", value : record.get('tel')}
            ]);
            win.show();
            win.onSubmitSuccess = function(result){
                utils.show_msg("操作成功");
                me.store.reload();
                win.close();
            };
        },

        operationBtn : function(){
            var me = this;
            var renderer = function(value, metaData, record, rowIndex, colIndex, store){
                var editBtnId = utils.createGridBtn({
                    text: "编辑",
                    width : 60,
                    handler: function(){
                        me.editCustomerInfo(record);
                    }
                });
                return ('<div style="width:70px;float:left;"><span id="' + editBtnId + '" /></div>')
            };
            return {header:"操作", aligin:"left", width:80, renderer:renderer};
        },

        createTbarA : function(){
            var me = this;
            return [
                {xtype : "button", text : "添加用户", width : 80 , handler:function(){me.addCustomerFrame(me);}}, '-',
                {xtype : "button", text : "删除用户", width : 80 , handler:function(){me.deleteCustomer(me)}}, '-',
                //{xtype : "button", text : "快捷地图", width : 80 , handler:function(){me.quickMap(me)}},'-',
                {xtype : "button", text : "售出", width : 80 , handler:function(){me.createSalePie(me)}},'-',
                {xtype : "button", text : "空桶", width : 80 , handler:function(){me.createOwePie(me)}},'-',
                {xtype : "button", text : "未付款", width : 80 , handler:function(){me.createDebtPie(me)}},'-'
            ];
        },

        createTbarB : function(){
            var me = this;
            return new Ext.Toolbar({
                items : [
                    '编号：',{id : me.__make_id('id_search'), xtype : 'textfield', width: 100},'-',
                    '名称：',{id : me.__make_id('name_search'), xtype : 'textfield', width: 100},'-',
                    '电话：',{id : me.__make_id("tel_search"), xtype : "textfield", width : 100},'-',
                    {xtype : 'button', text : "过滤", width : 50, id : me.__make_id('search_btn'), handler: function(){
                        me.store.setBaseParam("id", me.__get_comp_value('id_search'));
                        me.store.setBaseParam("name", me.__get_comp_value('name_search'));
                        me.store.setBaseParam("tel", me.__get_comp_value("tel_search"));
                        me.store.load({params : {start: 0, limit: me.pageSize}});
                    }}
                ]
            })
        },

        addCustomerFrame : function(me){
            var win = new fw.FormWindowUi({
                title : "添加用户",
                height : 200,
                action : urls.customer_url('add_customer')
            });
            win.addItem([
                {xtype : "textfield", fieldLabel : "名称", allowBlank:false, name : "name", anchor : "95%"},
                {xtype : "textfield", fieldLabel : "地址", allowBlank:false, name : "address", anchor : "95%"},
                {xtype : "textfield", fieldLabel : "电话", allowBlank:false, name : "tel", anchor : "95%"}
            ]);
            win.show();
            win.onSubmitSuccess = function(result){
                utils.show_msg("操作成功");
                me.store.reload();
                win.close();
            };
        },

        deleteCustomer : function(me){
            var record = me.getSelectionModel().getSelections();
            var del = function(){
                utils.http_request(urls.customer_url("delete_customer"),{
                    id_str : id_str
                },function(result){
                    if (result.success) {
                        me.store.reload();
                    }
                    utils.show_msg(result.msg);
                })
            };
            //检查选项
            if (record.length==0) {
                utils.show_msg("没有选择用户");
            } else {
                var id_str = '';
                for(var i=0; i<record.length; i++){
                    id_str += ','+record[i].get("id");
                }
                utils.confirm("确认删除用户?",del);
            }
        },

        createSalePie : function(me){
            var store = new Ext.data.JsonStore({
                url : urls.customer_url("get_customer_sale_pie"),
                root : 'data',
                autoLoad : true,
                fields : df.pie_recrod
            });
            var win = new pw.PieWindow({
                title : "售出饼图",
                store : store
            });
            win.show();
        },

        createOwePie : function(me){
            var store = new Ext.data.JsonStore({
                url : urls.customer_url("get_customer_owe_pie"),
                root : 'data',
                autoLoad : true,
                fields : df.pie_recrod
            });
            var win = new pw.PieWindow({
                title : "空桶饼图",
                store : store
            });
            win.show();
        },

        createDebtPie : function(me){
            var store = new Ext.data.JsonStore({
                url : urls.customer_url("get_customer_debt_pie"),
                root : 'data',
                autoLoad : true,
                fields : df.pie_recrod
            });
            var win = new pw.PieWindow({
                title : "应付款饼图",
                store : store
            });
            win.show();
        },

        quickMap : function(){
            var  mapwin = new Ext.Window({
                    layout: 'fit',
                    title: '快捷地图',
                    closeAction: 'hide',
                    width:800,height:450,
                    items: {
                        xtype: 'gmappanel',
                        zoomLevel: 14,
                        gmapType: 'map',
                        mapConfOpts: ['enableScrollWheelZoom','enableDoubleClickZoom','enableDragging'],
                        mapControls: ['GSmallMapControl','GMapTypeControl','NonExistantControl'],
                        setCenter: {
                            geoCodeAddr: 'Hengli Shuibian Post Office',
                            marker: {title: '横沥水边邮政'}
                        }
                    }
            });
            mapwin.show();
        }

    });

    exports.CustomerPanel = CustomerPanel;
});
