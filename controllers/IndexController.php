<?php
namespace app\controllers;

use app\models\Product;
use app\controllers\CommonController;

class IndexController extends CommonController{

    public function actionIndex(){
        $this->layout = "layout1";
        $data['tui'] = Product::find()->where('istui = "1" and ison = "1"')->orderby('createtime desc')->limit(4)->all();
        $data['new'] = Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(4)->all();
        $data['hot'] = Product::find()->where('ison = "1" and ishot = "1"')->orderby('createtime desc')->limit(4)->all();
        $data['all'] = Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(7)->all();
        
        // $last = $data['all'][count($data['all'])-1];
        // foreach ((array)json_decode($last->pics, true) as $key => $pic){
        //     var_dump($key);exit;
        // }

        return $this->render("index", ['data' => $data]);
    }
}