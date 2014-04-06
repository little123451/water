<?php

Class ItemMngAction {

    /**
     * 获取用户列表
     *
     * @param $param
     * @return string
     */
    public function getItemList($param){
        $dao = M('item');
        $res = $dao->all();
        return build_lst(0,$res);
    }

    /**
     * 更新产品的信息
     *
     * @param $param
     * @return string
     */
    public function updateItemInfo($param){
        $dao = M('item');

        $id = intval(get_val($param,"id",false));
        $store = intval(get_val($param,"store",false));
        $sale = intval(get_val($param,"sale",false));

        $sql = "UPDATE `item` SET `store`={$store},`sale`={$sale} WHERE `id`={$id}";
        $query = $dao->execute($sql);
        logger::debug("update_item_info.sql =>".$sql);

        if ($query) {
            return build_resp(true,"操作成功");
        } else {
            return build_resp(false,"更新失败,错误信息记录到日志");
        }
    }

    /**
     * 增加产品库存
     *
     * @param $param
     */
    public function addItemStore($param){
        $dao = M('item');

        $id = intval(get_val($param,"item",false));
        $count = intval(get_val($param,"count",false));

        $sql = "UPDATE `item` SET `store`=`store`+{$count} WHERE `id`={$id}";
        $query = $dao->execute($sql);
        logger::debug("add_item_store.sql=>".$sql);

        if ($query) {
            return build_resp(true,"操作成功");
        } else {
            return build_resp(false,"更新失败,错误信息记录到日志");
        }
    }

    /**
     * 获取产品用于显示下拉菜单的数据
     *
     * @return string
     */
    public function getItemCombo(){
        $dao = M('item');

        //准备查询语句并执行
        $sql = "SELECT `id` as value,`name` as text FROM `item`";
        $query = $dao->query_result($sql);
        logger::debug("get_item_combo.sql =>".$sql);

        return build_lst(-1,$query);
    }

}