<?php 

///Author: zhangzj<zhangzj@ucweb.com>
///Date: 2012-11-18
///Description: 一般数据库的增删改查操作，相信这个虚类封装的方法已经能解决90%的操作了
//              若有一些特别复杂的查询，可在子类中自定义这些sql操作

include_once dirname(__FILE__) . "/dbfactory.class.php";

abstract class AbstractDao{
    function __construct($tb_name, $dbCode){
        $this->tb_name = $tb_name;
        $this->db = DbFactory::getDbMng($dbCode);
    }

    /**
     * @brief 获取符合条件的一条记录
     *
     * @param mixed $wh where语句或数组
     * @return mixed
     */
    function get($wh)
    {
        $query = $this->db->get_where($this->tb_name, $wh);
        return $query->row();
    }

    /**
     * @brief 获取查询记录
     *
     * @param mixed $wh where语句或数组
     * @param string $field 选择的字段
     * @param int $offset 记录的开始位置
     * @param int $limit 取出数量
     * @param array $order 排序方式, array(field, ordStr)
     * @param array $like 用于like的数组, array(field => likeStr)
     * @return array 
     */
    function all($wh='', $field='*', $offset='', $limit='', $order='', $like='', $wh_in='')
    {
        $tbn = $this->tb_name;
        $this->db->select($field)->from($this->tb_name);
        if($wh){
            $this->db->where($wh);
        }
        if($wh_in && is_array($wh_in)){
            foreach($wh_in as $k=>$v){
                $this->db->where_in($k, $v);
            }
        }
        if($like){
            foreach ($like as $k => $v) {
                $this->db->like($k, $v);
            }
        }
        if($limit){
            $this->db->limit($limit, $offset);
        }
        if($order){
            foreach($order as $o=>$s){
                $this->db->order_by($o, $s);
            }
        }

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * @brief 添加记录
     *
     * @param mixed $data 要入库的对象或数组
     * @return bool
     */
    function add($data)
    {
        $rtn = $this->db->insert($this->tb_name, $data);
        if($rtn){
            return $this->db->insert_id();
        }else{
            return false;
        }
    }

    /**
     * @brief 根据条件更新记录
     *
     * @param mixed $data 更新的记录，可以是数组或对象
     * @param mixed $wh where语句或数组
     * @return bool
     */
    function update($data, $wh)
    {
        $this->db->where($wh);
        return $this->db->update($this->tb_name, $data);
    }

    /**
     * @brief 删除符合条件的记录
     *
     * @param mixed $wh where语句或数组
     * @return bool
     */
    function del($wh)
    {
        $this->db->where($wh);
        return $this->db->delete($this->tb_name);
    }

    /** 
     * @brief 获取记录数
     *
     * @param mixed $wh where语句或数组
     * @param array $like 用于like的数组, array(field => likeStr)
     * @return int 记录数
     */
    function getCount($wh='', $like='')
    {   
        if($wh){
            $this->db->where($wh);
        }   
        if($like){
            foreach ($like as $k => $v) {
                $this->db->like($k, $v);
            }   
        }   

        $this->db->select('count(*) as i_num');

        $res = $this->db->get($this->tb_name);
        return $res->row()->i_num;
    }

    /**
     * @brief 获取上一次查询语句
     *
     * @return 上次查询的sql语句
     */
    function last_query(){
        return $this->db->last_query();
    }

    /** 
     * @brief 执行原生sql，并返回一个查询数组
     * @return 返回一个查询结果数组
     */
    function query_result($sql){
        $query = $this->db->query($sql);
        return $query->result();
    }   

    /** 
     * @brief 执行原生sql，并返回第一个结果对象
     * @return 返回第一个查询结果
     */
    function query_row($sql){
        $query = $this->db->query($sql);
        return $query->row();
    }   

    /** 
     * @brief 执行原生sql
     * @return 返回执行结果，如果sql是查询语句则返回query对象，否则返回影响的行数
     */
    function execute($sql){
        return $this->db->query($sql);
    }
}
