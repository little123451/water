<?php

Class CustomerMngAction {

    /**
     * 获取用户列表
     *
     * @param $param
     * @return string
     */
    public function getCustomerList($param){
        $dao = M('customer');

        //获取请求参数
        $start = intval(get_val($param,"start",0));
        $limit = intval(get_val($param,"limit",50));
        $id = intval(get_val($param,"id",false));
        $name = get_val($param,"name",false);
        $tel = get_val($param,"tel",false);
        $sort = get_val($param,"sort",false);
        $dir = get_val($param,"dir",false);

        //组装查询语句
        $sql = "SELECT @fields@ FROM `customer` WHERE 1=1";
        if ($id) { $sql .= " AND `id`={$id}"; }
        if ($name) { $sql .= " AND `name` LIKE `%{$name}%`"; }
        if ($tel) { $sql .= " AND `tel`='{$tel}'"; }

        $sql1 = str_replace("@fields@","*",$sql);
        if ($sort) $sql1 .= " ORDER BY {$sort}";
        if ($dir) $sql1 .= " {$dir}";
        $sql1 .= " LIMIT {$start},{$limit}";
        logger::debug("get_customer_list.sql =>".$sql1);
        $res = $dao->query_result($sql1);

        $sql2 = str_replace("@fields@","COUNT(*) AS sum",$sql);
        logger::debug("get_customer_list.sql =>".$sql2);
        $query = $dao->query_row($sql2);
        $count = $query->sum;

        return build_lst($count,$res);
    }

    /**
     * 更新用户信息
     *
     * @param $param
     * @return string
     */
    public function updateCustomerInfo($param){

        $dao = M('customer');
        $id = $param['id'];

        //准备数据
        $data['name'] = $param['name'];
        $data['address'] = $param['address'];
        $data['tel'] = $param['tel'];

        //执行语句并记录日志
        $query = $dao->update($data,array('id'=>$id));
        logger::debug("update_customer_info.user_id({$id}) =>".json_encode($data));

        if ($query) {
            return build_resp(true,"操作成功");
        } else {
            return build_resp(false,"更新失败,错误信息记录到日志");
        }
    }

    /**
     * 添加用户
     *
     * @param $param
     * @return string
     */
    public function addCustomer($param){

        $dao = M('customer');

        //准备数据
        $data['name'] = $param['name'];
        $data['address'] = $param['address'];
        $data['tel'] = $param['tel'];

        //执行语句并记录日志
        $query = $dao->add($data);
        logger::debug("add_customer. =>".json_encode($data));

        if ($query) {
            return build_resp(true,"操作成功");
        } else {
            return build_resp(false,"更新失败,错误信息记录到日志");
        }

    }

    /**
     * 删除客户信息
     *
     * @param $param['id_str'] 需要被删除的客户信息的字符串,以逗号隔开
     * @return string
     */
    public function deleteCustomer($param){
        $dao = M('customer');

        //准备数据
        $id_str = trim($param['id_str'],',');

        //执行查询语句并打印日志
        $sql = "DELETE FROM `customer` WHERE `id` IN ({$id_str})";
        $query = $dao->execute($sql);
        logger::debug("delete_customer.sql =>".$sql);

        if ($query) {
            return build_resp(true,"操作成功");
        } else {
            return build_resp(false,"操作失败,错误信息记录到日志");
        }
    }

    /**
     * 获取生成售出饼图所需的数据
     *
     * @return string
     */
    public function getCustomerSalePie(){
        $dao = M('customer');

        //查询数据
        $sql = "SELECT `sale` as data,`name` as cate FROM `customer`";
        $data = $dao->query_result($sql);
        logger::debug($sql);

        return build_lst(0,$data);
    }

    /**
     * 获取生成空桶饼图所需的数据
     *
     * @return string
     */
    public function getCustomerOwePie(){
        $dao = M('customer');

        //查询数据
        $sql = "SELECT `owe` as data,`name` as cate FROM `customer`";
        $data = $dao->query_result($sql);
        logger::debug($sql);

        return build_lst(0,$data);
    }

    /**
     * 获取生成应付款饼图所需的数据
     *
     * @return string
     */
    public function getCustomerDebtPie(){
        $dao = M('customer');

        //查询数据
        $sql = "SELECT `debt` as data,`name` as cate FROM `customer`";
        $data = $dao->query_result($sql);
        logger::debug($sql);

        return build_lst(0,$data);
    }

}