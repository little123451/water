define(function(require, exports){
    var FormWindowUi = Ext.extend(Ext.Window, {

        constructor : function(cfg){

            FormWindowUi.superclass.constructor.call(this, {

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

            //是否在点击提交时提示确定
            this.need_confirm = (cfg.need_confirm == undefined ? true : cfg.need_confirm);

            //创建表单
            this.form = new Ext.form.FormPanel({
                autoScroll : true,
                fileUpload : cfg.fileUpload
            });
            this.add(this.form);

            //创建按钮和提示
            var me = this,url = cfg.action;
            this.addButton({text : "提交", scope : this}, function(btn){
                if(me.need_confirm){
                    Ext.Msg.confirm("提示","确认提交", function(msg){
                        if (msg == "yes") { me.submit(url); }
                    });
                } else { me.submit(url); }
            });
            this.addButton({text : "取消", scope : this}, function(btn){
                me.close();
            });

            this.doLayout();
        },

        submit : function(url, params){
            var me = this;
            var form = this.form.getForm();
            if (!form.isValid()) {
                Ext.Msg.alert("提示", "内容不合法或不完整，请正确输入");
                return;
            }
            form.submit({
                url: url,
                clientValidation : true,
                params : params,
                method: 'POST',
                waitMsg: '正在上传...',
                success: function () { me.onSubmitSuccess() },
                failure: function(form, action) {
                    switch (action.failureType) {
                        case Ext.form.Action.CLIENT_INVALID:
                            Ext.Msg.alert('提示', '请按条件输入数据');
                            break;
                        case Ext.form.Action.CONNECT_FAILURE:
                            Ext.Msg.alert('提示', '连接失败');
                            break;
                        case Ext.form.Action.SERVER_INVALID:
                            Ext.Msg.alert("提示", action.result.msg);
                            break;
                    }
                }
            });
        },

        addItem : function(cfg){
            this.form.add(cfg);
            this.form.doLayout();
            return this;
        },

        onSubmitSuccess : function(result){
            Ext.Msg.alert("提示", "提交成功");
            this.close();
        }

    });

    exports.FormWindowUi = FormWindowUi;
});