<?php
namespace app\modules\controllers;
use yii\web\Controller;
use app\models\Category;
use Yii;

class CategoryController extends Controller{
    public function actionList(){
        $this->layout = 'layout1';
        $model = new Category;
        $cates = $model->getTreeList();
        return $this->render('cates',['cates'=>$cates]);
    }

    public function actionAdd(){
        $this->layout = 'layout1';
        $model = new Category;
        $list = $model->getOption();
        $list[0] = '添加顶级分类';
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if($model->add($post)){
                Yii::$app->session->setFlash('info','添加分类成功!');
            }
        }
        return $this->render('add',['list'=>$list,'model'=>$model]);
    }

    public function actionMod(){
        $this->layout = 'layout1';
        $cateid = Yii::$app->request->get('cateid');
        $model = Category::find()->where('cateid=:cateid',[':cateid'=>$cateid])->one();
        $list = $model->getOption();

        // todo 限制修改一级分类
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if($model->load($post) && $model->save()){
                Yii::$app->session->setFlash('info',"修改成功!");
            }
        }
        return $this->render('add',['model'=>$model,'list'=>$list]);
    }

    public function actionDel(){
        try {
            $cateid = Yii::$app->request->get('cateid');
            if (empty($cateid)) {
                throw new \Exception('参数错误!');
            }
 
            $res = Category::find()->where('parentid=:parentid', [':parentid'=>$cateid])->one();
            if ($res) {
                throw new \Exception('有子类的分类不允许删除');
            }
            if(!Category::deleteAll('cateid=:cateid',[':cateid'=>$cateid])){
                throw new \Exception('删除失败!');
            }
        }catch(\Exception $e){
            Yii::$app->session->setFlash('info',$e->getMessage());
        }

        return $this->redirect(['category/list']);
    }
}