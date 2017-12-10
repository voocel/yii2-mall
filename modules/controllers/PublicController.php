<?php
namespace app\modules\controllers;

use yii\web\Controller;
use app\modules\models\Admin;
use Yii;
class PublicController extends Controller{
    public function actionLogin(){
        $this->layout = false;
        $model = new Admin;
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if($model->login($post)){
                $this->redirect(['default/index']);
                Yii::$app->end();
            }
        }
        
        return $this->render('login',['model'=>$model]);
    }
}