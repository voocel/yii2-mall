<?php

namespace app\models;
use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    private static $users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id' => '101',
            'username' => 'demo',
            'password' => 'demo',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ],
    ];


    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }

    /**
     * 根据userid获取关联表profile
     */
    public function getProfile(){
        return $this->hasOne(Profile::className(),['userid'=>'userid']);
    }

    public function regByMail($data){
        $data['User']['username'] = 'test_'.uniqid();
        $data['User']['userpass'] = uniqid();
        $this->scenario = 'regbymail';
        if($this->load($data) && $this->validate()){
            $mailer = Yii::$app->mailer->compose('createuser');
            $mailer->setFrom('naivman@163.com');
            $mailer->setTo($data['User']['useremail']);
            $mailer->setSubject("恭喜xxx-注册成为新会员");
            if($mailer->send() && $this->reg($data,'regbymail')){
                return true;
            }
        }
        return false;
    }

    public function reg($data,$scenario='reg'){
        $this->scenario = $scenario;
        if($this->load($data) && $this->validate()){
            $this->createtime = time();
            $this->userpass = md5($this->userpass);
            if($this->save(false)){
                return true;
            }
            return false;
        }
        return false;
    }

}
