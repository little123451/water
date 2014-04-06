define(function(require, exports, module){

    var mp = require('./MenuPanel.js');

    var MainPanel = Ext.extend(Ext.Viewport, {
        constructor : function (){
            var me = this;
            MainPanel.superclass.constructor.call(this ,{
                enableTabScroll:true,
                layout:"border",
                items:[
                    this.TopPanel(),
                    this.RightPanel(),
                    this.BottomPanel(),
                    this.LeftPanel(),
                    this.CenterPanel()
                ]
            });
        },
        LeftPanel : function(){
            return {
                id:"menu",
                title:"菜单",
                region:"west",
                width:150,
                height:200,
                layout:"fit",
                collapsible:true,
                items:[mp.MenuPanel]
            }
        },
        CenterPanel : function(){
            return {
                id:"main-panel",
                xtype:"tabpanel",
                region:"center",
                activeTab: 0,
                items:[]
            }
        },
        TopPanel : function(){return {}},
        RightPanel : function(){return{}},
        BottomPanel : function(){return {}}
    });

    exports.MainPanel = MainPanel;

});