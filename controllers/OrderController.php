<?php
namespace app\controllers;

use app\controllers\CommonController;
use Yii;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Cart;
use app\models\Product;
use app\models\User;
use app\models\Address;
use app\models\Pay;
use dzer\express\Express;

class OrderController extends CommonController{

    public function actionIndex(){
        $this->layout='layout2';
        return $this->render('index');
    }

    public function actionCheck()
    {
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $orderid = Yii::$app->request->get('orderid');
        $status = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one()->status;
        if ($status != Order::CREATEORDER && $status != Order::CHECKORDER) {
            return $this->redirect(['order/index']);
        }
        $loginname = Yii::$app->session['loginname'];
        $userid = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one()->userid;
        $addresses = Address::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
        $details = OrderDetail::find()->where('orderid = :oid', [':oid' => $orderid])->asArray()->all();
        $data = [];
        foreach($details as $detail) {
            $model = Product::find()->where('productid = :pid' , [':pid' => $detail['productid']])->one();
            $detail['title'] = $model->title;
            $detail['cover'] = $model->cover;
            $data[] = $detail;
        }
        $express = Yii::$app->params['express'];
        $expressPrice = Yii::$app->params['expressPrice'];
        $this->layout = "layout1";
        return $this->render("check", ['express' => $express, 'expressPrice' => $expressPrice, 'addresses' => $addresses, 'products' => $data]);
    }

    public function actionAdd(){
        if(Yii::$app->session['isLogin'] != 1){
            return $this->redirect(['member/auth']);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if(Yii::$app->request->isPost){
                $post = Yii::$app->request->post();
                $orderModel = new Order;
                $orderModel->scenario = 'add';
                $userModel = User::find()->where('username = :name or useremail = :email',[':name'=>Yii::$app->session['loginname'],':email'=>Yii::$app->session['loginname']])->one();
                
                if(!$userModel){
                    throw new \Exception();
                }
                $userid = $userModel->userid;
                $orderModel->userid = $userid;
                $orderModel->status = Order::CREATEORDER;
                $orderModel->createtime = time();
                if(!$orderModel->save()){
                    throw new \Exception();
                }
                $orderid = $orderModel->getPrimaryKey();
                
                foreach($post['OrderDetail'] as $product){
                    $model = new OrderDetail;
                    $model['orderid'] = $orderid;
                    $model['createtime'] = time();
                    $data['OrderDetail'] = $product;
                    if (!$model->add($data)) {
                        throw new \Exception();
                    }
                    //删除购物车商品
                    Cart::deleteAll('productid = :pid',[':pid'=>$product['productid']]);
                    //修改商品库存
                    Product::updateAllCounters(['num'=>-$product['productnum']],'productid = :pid',[':pid'=>$product['productid']]);
                }
            }
            $transaction->commit();
        }catch(\Exception $e){
            $transaction->rollback();
            return $this->redirect(['cart/index']);
        }
        return $this->redirect(['order/check', 'orderid' => $orderid]);
    }

    public function actionConfirm(){
        if(Yii::$app->session['isLogin'] != 1){
            return $this->redirect(['member/auth']);
        }
        try{
            if(!Yii::$app->request->isPost){
                throw new \Exception();
            }
            $post = Yii::$app->request->post();
            $loginname = Yii::$app->session['loginname'];
            $userModel = User::find()->where('username = :name or useremail = :email',[':name'=>Yii::$app->session['loginname'],':email'=>Yii::$app->session['loginname']])->one();
            if(empty($userModel)){
                throw new \Exception(1);
            }
            $userid = $userModel->userid;
            $model = Order::find()->where('orderid = :oid and userid = :uid',[':oid'=>$post['orderid'],':uid'=>$userid])->one();
            if(empty($model)){
                throw new \Exception(2);
            }
            $model->scenario = 'update';
            $post['status'] = Order::CHECKORDER;
            $details = OrderDetail::find()->where('orderid = :oid',[':oid'=>$post['orderid']])->all();
            $amount = 0;
            foreach($details as $detail){
                $amount += $detail->productnum * $detail->price; 
            }
            if($amount <= 0){
                throw new \Exception(3);
            }
            $express = Yii::$app->params['expressPrice'][$post['expressid']];
            if($express < 0){
                throw new \Exception(4);
            }
            $amount += $express;
            $post['amount'] = $amount;
            $post['addressid'] = 3;
            $data['Order'] = $post;
            
            // print_r($data);exit;
            // if (empty($post['addressid'])) {
			// 	return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
			// }
            if($model->load($data) && $model->save()){
                return $this->redirect(['order/pay','orderid'=>$post['orderid'],'paymethod'=>$post['paymethod']]);
            }else{
                throw new \Exception(5);
            }
        }catch(\Exception $e){
            // return $this->redirect(['index/index']);
            var_dump($e->getMessage());
        }
    }
}