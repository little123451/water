define(function(require, exports, module){

    //{name : "deliver", type:"date", dateFormat:"Y-n-j"}

    exports.customer_record = Ext.data.Record.create([
        {name : "id"},
        {name : "name"},
        {name : "address"},
        {name : "tel"},
        {name : "sale"},
        {name : "owe"},
        {name : "debt"}
    ]);

    exports.order_record = Ext.data.Record.create([
        {name : "id"},
        {name : "sale"},
        {name : "recycle"},
        {name : "consignee"},
        {name : "consignee_id"},
        {name : "deliver"},
        {name : "date"},
        {name : "pay"},
        {name : "status"},
        {name : "mark"}
    ]);

    //下拉框的数据格式
    exports.comboBox_list = Ext.data.Record.create([
        {name : "text"},
        {name : "value"}
    ]);

    exports.item_record = Ext.data.Record.create([
        {name : "id"},
        {name : "name"},
        {name : "store"},
        {name : "sale"}
    ]);

    //饼图的数据格式
    exports.pie_recrod = Ext.data.Record.create([
        {name : "cate"},
        {name : "data"}
    ]);

    //曲线图的数据格式
    exports.graph_record = Ext.data.Record.create([
        {name : "x"},
        {name : "y"}
    ])
});
