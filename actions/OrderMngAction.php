<?php

Class OrderMngAction {

    /**
     * 获取订单列表
     *
     * @return string
     */
    public function getOrderList($param){
        $dao = M('order');

        //获取请求参数
        $start = intval(get_val($param,"start",0));
        $limit = intval(get_val($param,"limit",50));
        $sale = get_val($param,"sale",false);
        $recycle = get_val($param,"recycle",false);
        $date_up = get_val($param,"date_up",false);
        $date_down = get_val($param,"date_down",false);
        $consignee = get_val($param,"consignee",false);
        $deliver = get_val($param,"deliver",false);
        $status = get_val($param,"status",false);
        $sort = get_val($param,"sort",false);
        $dir = get_val($param,"dir",false);

        //组装查询语句
        $sql = "SELECT @fields@ FROM `order` WHERE 1=1";
        if ($sale && is_numeric($sale)) { $sql .= " AND `sale`={$sale}"; }
        if ($recycle && is_numeric($recycle)) { $sql .= " AND `recycle`={$recycle}"; }
        if ($date_up) { $sql .= " AND `date`<='{$date_up}'"; }
        if ($date_down) { $sql .= " AND `date`>='{$date_down}'"; }
        if ($consignee) { $sql .= " AND `consignee_id`={$consignee}"; }
        if ($deliver) { $sql .= " AND `deliver` LIKE '%{$deliver}%'"; }
        if (is_numeric($status)) { $sql .= " AND `status`={$status}"; }

        $sql1 = str_replace("@fields@","*",$sql);
        if ($sort) $sql1 .= " ORDER BY {$sort}";
        if ($dir) $sql1 .= " {$dir}";
        $sql1 .= " LIMIT {$start},{$limit}";
        logger::debug("get_order_list.sql =>".$sql1);
        $res = $dao->query_result($sql1);

        $sql2 = str_replace("@fields@","COUNT(*) AS sum",$sql);
        logger::debug("get_order_list.count =>".$sql2);
        $query = $dao->query_row($sql2);
        $count = $query->sum;

        return build_lst($count,$res);
    }

    /**
     * 获取收货人列表
     *
     * @return string
     */
    public function getConsigneeList(){
        $dao = M('customer');

        //准备查询语句并执行
        $sql = "SELECT `id` as value,`name` as text FROM `customer`";
        $query = $dao->query_result($sql);
        logger::debug("get_consignee_list.sql =>".$sql);

        return build_lst(-1,$query);
    }

    /**
     * 创建订单
     *
     * @param $param
     */
    public function createOrder($param){
        $dao = M('order');

        //准备数据
        $data["sale"] = intval(get_val($param,"sale",0));
        $data["recycle"] = intval(get_val($param,"recycle",0));
        $data["date"] = get_val($param,"date","");
        $data["deliver"] = get_val($param,"deliver","");
        $data["mark"] = get_val($param,"mark","");
        $data["pay"] = get_val($param,"pay","");

        //补充收货人信息
        $c_id = intval(get_val($param,"consignee",-1));
        $sql = "SELECT `name` FROM `customer` WHERE `id`={$c_id}";
        logger::debug("get_consignee_name.sql =>".$sql);
        $c = $dao->query_row($sql);
        $c_name = $c->name;
        $data["consignee"] = $c_name;
        $data["consignee_id"] = $c_id;

        //更新客户信息
        $sale = $data["sale"];
        $pay = $data["pay"];
        $recycle = $sale - $data["recycle"];
        $sql = "UPDATE `customer` SET `owe`=`owe`+{$recycle},`sale`=`sale`+{$sale},`debt`=`debt`+{$pay} WHERE `id`={$c_id}";
        $query[] = $dao->execute($sql);
        logger::debug("create_order.customer=>".$sql);

        //更新库存信息
        $sql = "UPDATE `item` SET `store`=`store`-{$sale},`sale`=`sale`+{$sale} WHERE `id`=1";
        $query[] = $dao->execute($sql);
        logger::debug("create_order.item=>".$sql);

        //执行操作并打印日志
        $query[] = $dao->add($data);
        logger::debug(json_encode($data));

        if (!in_array(false,$query)) {
            return build_resp(true,"操作成功");
        } else {
            return build_resp(false,"操作失败，错误信息已记录到日志");
        }
    }

    /**
     * 删除订单
     *
     * @param $param[""]
     */
    public function deleteOrder($param){
        $dao = M('order');

        $id_str = trim($param["id_str"],",");
        $id_arr = explode(",",$id_str);
        $query = array();

        foreach($id_arr as $order_id) {

            //获取订单数据和用户ID
            $order = $dao->get(array('id'=>$order_id));
            $sale = $order->sale;
            $recycle = $sale - $order->recycle;
            $pay = $order->pay;
            $c_id = $order->consignee_id;

            //撤销用户订单信息
            if ($order->status == 1) {//如果是已付款订单则不用更改未付款信息
                $sql = "UPDATE `customer` SET `owe`=`owe`-{$recycle},`sale`=`sale`-{$sale} WHERE `id`={$c_id}";
            } else {//否则撤销未付款信息
                $sql = "UPDATE `customer` SET `owe`=`owe`-{$recycle},`sale`=`sale`-{$sale},`debt`=`debt`-{$pay} WHERE `id`={$c_id}";
            }
            $query[] = $dao->execute($sql);
            logger::debug("delete_order.customer=>".$sql);

            //恢复库存信息
            $sql = "UPDATE `item` SET `store`=`store`+{$sale},`sale`=`sale`-{$sale}";
            $query[] = $dao->execute($sql);
            logger::debug("delete_order.item=>".$sql);

            //删除订单
            $sql = "DELETE FROM `order` WHERE `id`={$order_id}";
            $query[] = $dao->execute($sql);
            logger::debug("delete_order.sql=>".$sql);
        }

        if (!in_array(false,$query)) {
            return build_resp(true,"操作成功");
        } else {
            return build_resp(false,"操作失败，错误信息已记录到日志");
        }
    }

    /**
     * 获取销量趋势图的数据以显示
     *
     * @return string
     */
    public function getSaleTendency(){
        $dao = M('order');
        $last = 30;
        $day_time = 24*60*60;
        $beg_date = date("Y-m-d",time()-$last*$day_time);

        //获取原始数据
        $sql = "SELECT sum(`sale`) as y,`date` as x FROM `order` WHERE `date`>'{$beg_date}' GROUP BY `date`";
        $data = $dao->query_result($sql);
        logger::debug("get_owe_tendency.sql =>".$sql);

        //调整组装图表化数据并返回
        $default = array();
        $return = "";
        for($i=$last-1;$i>=0;$i--){
            $key = date("Y-m-d",time()-$i*$day_time);
            $default[$key] = 0;
        }
        foreach($data as $record){
            $default[$record->x] = $record->y;
        }
        foreach($default as $key=>$value){
            $ret['x'] = substr($key,-2,2);
            $ret['y'] = $value;
            $return[] = (object)$ret;
        }

        return build_lst(0,$return);
    }

    /**
     * 订单结数
     *
     * @param $param['id_str'] 结数的订单id
     * @return string
     */
    public function payOrder($param){
        $dao = M('order');
        $id_arr = explode(",",trim($param["id_str"],","));
        $query = array();

        foreach($id_arr as $order_id) {
            //获取订单数据和用户ID
            $order = $dao->get(array('id'=>$order_id));
            $pay = $order->pay;
            $c_id = $order->consignee_id;

            //更新用户欠账信息
            if ($order->status == 0) {
                $sql = "UPDATE `customer` SET `debt`=`debt`-{$pay} WHERE `id`={$c_id}";
                $query[] = $dao->execute($sql);
                logger::debug("delete_order.customer=>".$sql);

                //更改订单状态
                $sql = "UPDATE `order` SET `status`=1 WHERE `id`={$order_id}";
                $query[] = $dao->execute($sql);
                logger::debug("pay_order.sql=>".$sql);
            } else {
                $query[] = true;
            }
        }


        if ($query) {
            return build_resp(true,"操作成功");
        } else {
            return build_resp(false,"操作失败，错误信息已记录到日志");
        }
    }

    /**
     * 统计列表数据
     * 参数与获取订单列表相似
     *
     * @param $param
     * @return string
     */
    public function getOrderStatistics($param){
        $dao = M('order');

        //获取请求参数
        $sale = get_val($param,"sale",false);
        $recycle = get_val($param,"recycle",false);
        $date_up = get_val($param,"date_up",false);
        $date_down = get_val($param,"date_down",false);
        $consignee = get_val($param,"consignee",false);
        $deliver = get_val($param,"deliver",false);
        $status = get_val($param,"status",false);

        //组装查询语句
        $sql = "SELECT @fields@ FROM `order` WHERE 1=1";
        if ($sale && is_numeric($sale)) { $sql .= " AND `sale`={$sale}"; }
        if ($recycle && is_numeric($recycle)) { $sql .= " AND `recycle`={$recycle}"; }
        if ($date_up) { $sql .= " AND `date`<='{$date_up}'"; }
        if ($date_down) { $sql .= " AND `date`>='{$date_down}'"; }
        if ($consignee) { $sql .= " AND `consignee_id`={$consignee}"; }
        if ($deliver) { $sql .= " AND `deliver` LIKE '%{$deliver}%'"; }
        if (is_numeric($status)) { $sql .= " AND `status`={$status}"; }

        $sql1 = str_replace("@fields@","SUM(`sale`) AS `sale`,SUM(`recycle`) AS `recycle`,SUM(`pay`) AS `pay`",$sql);
        logger::debug("get_order_list.sql =>".$sql1);
        $res = $dao->query_result($sql1);

        return build_lst(0,$res);
    }

}