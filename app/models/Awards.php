<?php

class Awards extends \Phalcon\Mvc\Model
{
    public $award_id;
    public $award_type;
    public $goods_id;
    public $goods_quantity;
    public $award_name;
    public $award_price;
    public $num;
    public $stock;
    public $update_time;

    public function getSource(){
        return "draw_awards";
    }


}