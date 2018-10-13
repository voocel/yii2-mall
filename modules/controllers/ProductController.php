<?php
namespace app\modules\controllers;

use yii\web\Controller;
use Yii;
use yii\data\Pagination;
use app\models\Product;
use app\models\Category;
use crazyfd\qiniu\Qiniu;

class ProductController extends Controller{
    public function actionList(){
        $this->layout = 'layout1';
        $model = Product::find();
        $count = $model->count();
        $pageSize = Yii::$app->params['pageSize']['product'];
        $pager = new Pagination(['totalCount'=>$count,'pageSize'=>$pageSize]);
        $products = $model->offset($pager->offset)->limit($pager->limit)->all();
        return $this->render('product',['pager'=>$pager,'products'=>$products]);
    }

    public function actionAdd(){
        $this->layout = 'layout1';
        $model = new Product;
        $cate = new Category;
        $list = $cate->getOptions();
        unset($list[0]);
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            $pics = $this->upload();
            if(!$pics){
                $model->addError('cover','封面不能为空!');
            }
        }
        return $this->render('add',['opts'=>$list,'model'=>$model]);
    }

    private function upload(){
        if($_FILES['Product']['error']['cover']>0){
            return false;
        }
        $qiniu = new Qiniu;
    }
}