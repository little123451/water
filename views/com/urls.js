define(function(require, exports, module){

    function proxy_url(ac_name){
        return "core/proxy.php?action="+ac_name;
    }

    exports.order_url = function(ac_name){
        return proxy_url(ac_name);
    };

    exports.customer_url = function(ac_name){
        return proxy_url(ac_name);
    };

    exports.item_url = function(ac_name){
        return proxy_url(ac_name);
    };

});
