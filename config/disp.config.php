<?php
import("public.ActionConf");
import("@.actions.CustomerMngAction");
import("@.actions.OrderMngAction");
import("@.actions.ItemMngAction");

function load_actions(){

    $act_lst = array(

        "get_item_list" => new ActionConf("ItemMngAction","getItemList"),
        "update_item_info" => new ActionConf("ItemMngAction","updateItemInfo"),
        "get_item_combo" => new ActionConf("ItemMngAction","getItemCombo"),
        "add_item_store" => new ActionConf("ItemMngAction","addItemStore"),

        "get_order_list" => new ActionConf("OrderMngAction","getOrderList"),
        "create_order" => new ActionConf("OrderMngAction","createOrder"),
        "get_consignee_list" => new ActionConf("OrderMngAction","getConsigneeList"),
        "delete_order" => new ActionConf("OrderMngAction","deleteOrder"),
        "get_sale_tendency" => new ActionConf("OrderMngAction","getSaleTendency"),
        "pay_order" => new ActionConf("OrderMngAction","payOrder"),
        "get_order_statistics" => new ActionConf("OrderMngAction","getOrderStatistics"),

        "get_customer_list" => new ActionConf("CustomerMngAction","getCustomerList"),
        "update_customer_info" => new ActionConf("CustomerMngAction","updateCustomerInfo"),
        "add_customer" => new ActionConf("CustomerMngAction","addCustomer"),
        "delete_customer" => new ActionConf("CustomerMngAction","deleteCustomer"),
        "get_customer_sale_pie" => new ActionConf("CustomerMngAction","getCustomerSalePie"),
        "get_customer_owe_pie" => new ActionConf("CustomerMngAction","getCustomerOwePie"),
        "get_customer_debt_pie" => new ActionConf("CustomerMngAction","getCustomerDebtPie")
    );

    return $act_lst;
}