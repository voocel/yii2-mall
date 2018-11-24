<?php
namespace app\controllers;
use app\controllers\CommonController;
use app\models\User;
use Yii;

class MemberController extends CommonController{

    public function actionAuth(){
        $this->layout='layout2';
        $model = new User;
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if($model->login($post)){
                return $this->goBack(Yii::$app->request->referrer);
            }
        }
        return $this->render('auth',['model'=>$model]);
    }

    public function actionLogout(){
        Yii::$app->session->remove('loginname');
        Yii::$app->session->remove('isLogin');
        if(isset(Yii::$app->session['isLogin'])){
            return $this->goBack(Yii::$app->request->referrer);
        }
        return $this->goBack(Yii::$app->request->referrer);
    }

    public function actionReg(){
        $this->layout='layout2';
        $model = new User;
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if($model->regByMail($post)){
                Yii::$app->session->setFlash('info','电子邮件发送成功!');
            }
        }
        return $this->render('auth',['model'=>$model]);
    }
}