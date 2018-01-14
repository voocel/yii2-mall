<?php
namespace app\modules\controllers;

use yii\web\Controller;
use Yii;
use app\modules\models\Admin;

class ManageController extends Controller{
    public function actionMailchangepass(){
        $time = Yii::$app->request->get("timestamp");
        $adminUser = Yii::$app->request->get("adminuser");
        $token = Yii::$app->request->get("token");
        $model = new Admin;
        $myToken = $model->createToken($adminUser,$time);
        if($token != $myToken){
            $this->redirect(['public/login']);
            Yii::$app->end();
        }
        if($time->time()>300){
            $this->redirect(['public/login']);
            Yii::$app->end();
        }
        $model->adminuser = $adminUser;
        return $this->render('mailchangepass',['model'=>$model]);
    }



}

