<?php
namespace app\modules\models;
use yii\db\ActiveRecord;
use Yii;

class Admin extends ActiveRecord{

    public $rememberMe = true;
    public $repass;

    public static function tableName(){
        return "{{%admin}}";
    }

    public function attributeLabels(){
        return [
            'adminuser' => '管理员账号',
            'adminemail' => '管理员邮箱',
            'adminpass'  => '管理员密码',
            'repass'     => '确认密码'
        ];
    }

    public function rules(){
        return [
            ['adminuser','required','message'=>"管理员账号不能为空！",'on'=>['login','seekpass','adminadd','changeemail']],
            ['adminpass','required','message'=>"管理员密码不能为空！",'on'=>['login','changepass','adminadd','changeemail']],
            ['rememberMe','boolean','on'=>'login'],
            ['adminpass','validatePass','on'=>['login','changeemail']],
            ['adminemail','required','message'=>'电子邮箱不能为空！','on'=>['seekpass','adminadd','changeemail']],
            ['adminemail','email','message'=>'电子邮箱格式不正确','on'=>['seekpass','adminadd','changeemail']],
            ['adminemail','unique','message'=>'该邮箱已被注册','on'=>['adminadd','changeemail']],
            ['adminuser','unique','message'=>'该用户名已被注册','on'=>['adminadd']],
            ['adminemail','validateEmail','on'=>'seekpass'],
            ['repass','required','message'=>'确认密码不能为空','on'=>['changepass','adminadd']],
            ['repass','compare','compareAttribute'=>'adminpass','message'=>'两次密码不一致','on'=>['changepass','adminadd']]
        ];
    }

    public function validatePass(){
        if(!$this->hasErrors()){
            $data = self::find()->where('adminuser=:user and adminpass=:pass',[':user'=>$this->adminuser,':pass'=>md5($this->adminpass)])->one();
            if(is_null($data)){
                $this->addError('adminpass','用户名或密码错误！');
            }
        }
    }

    public function validateEmail(){
        if(!$this->hasErrors()){
            $res = self::find()->where('adminuser=:user and adminemail=:email',
                                       [':user'=>$this->adminuser,':email'=>$this->adminemail]
                                       )->one();
            if(is_null($res)){
                $this->addError('adminemail','电子邮箱不匹配!');
            }
        }
    }

    public function login($data){
        $this->scenario = 'login';
        if($this->load($data)&&$this->validate()){
            $lifetime = $this->rememberMe ? 24*3600 : 0;
            $session = Yii::$app->session;
            session_set_cookie_params($lifetime);
            $session['admin'] = [
                'adminuser'  => $this->adminuser,
                'isLogin'    => 1
            ];
            $this->updateAll(['logintime'=>time(),'loginip'=>ip2long(Yii::$app->request->userIP)],'adminuser=:user',[':user'=>$this->adminuser]);
            return (bool)$session['admin']['isLogin'];
        }
        return false;
    }

    public function seekpass($data){
        $this->scenario = 'seekpass';
        if($this->load($data) && $this->validate()){

        }
        return false;
    }

    public function createToken($adminuser,$time){
        return md5(md5($adminuser).base64_encode(Yii::$app->request->userIP)); //待完成。。。
    }

    public function changePass($data){
        $this->scenario = 'changepass';
        if($this->load($data) && $this->validate()){
            return (bool)$this->updateAll(['adminpass'=>md5($this->adminpass)],'adminuser=:user',[':user'=>$this->adminuser]);
        }
        return false;
    }

    public function reg($data){
        $this->scenario = 'adminadd';
        // $data['Admin']['adminpass'] = md5($data['Admin']['adminpass']);
        // $data['Admin']['repass'] = md5($data['Admin']['repass']);
        //直接new出来的model执行save方法时是更新或者创建，save默认执行validate方法
        if($this->load($data) && $this->validate()){
            $this->adminpass = md5($this->adminpass);
            $this->repass = md5($this->repass);
            if($this->save(false)){ //上面已执行过validate，所以save传false可以跳过validate验证，避免重复验证
                return true;
            }
            return false;
        }
        return false;
    }

    public function changeemail($data){
        $this->scenario = 'changeemail';
        if($this->load($data) && $this->validate()){
            return (bool)$this->updateAll(['adminemail'=>$this->adminemail],'adminuser =:user',[':user'=>$this->adminuser]);
        }
        return false;
    }
}