<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/9/22
 * Time: 17:41
 */

namespace Tsy\Plugs\User;


trait UserTrait
{
//    protected $
    public $allowReg=true;
    public $LoginView='';
    protected $_map=[];
    /**
     * 用户注册
     * @param string $Account 注册帐号
     * @param string $PWD 注册密码
     * @param array $Properties 其他属性
     * @return UserObject
     */
    function reg(string $Account,string $PWD,array $Properties=[]){
        if(!$this->allowReg){
            return '禁止注册';
        }

        $PWD = password_hash($PWD,PASSWORD_DEFAULT);
        $data=array_merge([
            'Account'=>$Account,
            'PWD'=>$PWD
        ],$Properties);
        $data['data']=$data;
        return invokeClass($this,'add',$data);
    }

    /**
     * 用户登录
     * @param string $Account 账户名
     * @param string $PWD 账户密码
     * @return UserObject
     */
    function login(string $Account,string $PWD){
        $User = M($this->LoginView)->where(['Account'=>$Account])->field('UID,PWD')->find();
        if(false!==$User){
            return $this->loginSuccess($User);
        }else{
            return '账户名或密码错误';
        }
    }
    private function loginSuccess($User){
        session('UID',$User['UID']);
//        session('GIDs',)
        return $this->get($User['UID']);
    }
    /**
     * 退出登录
     */
    function logout(){
        session(null);
        return true;
    }

    /**
     * 查找我的账户
     * @param string $Account 账户名称
     * @return array {'Email':"","Phone":"",'Account':"","UID":1}
     */
    function findAccount(string $Account){}

    /**
     * 重置密码
     * @param int $UID 用户编号
     * @param string $PWD 新密码
     * @param string $Code 验证码或旧密码 当用户权限为管理员时不需要Code参数，如果不是则需要提供Code验证码或者旧密码做验证
     */
    function resetPWD(int $UID,string $PWD,string $Code=''){}

    /**
     * 检查账户是否存在
     * @param string $Account 账户名称
     * @return bool 存在true,不存在false
     */
    function checkAccount(string $Account){}

    /**
     * 自动登录
     * @param string $SID 自动登录的验证字符
     * @return UserObject|bool 成功返回用户对象，否则返回false
     */
    function reLogin(string $SID=''){}

    /**
     * 发送验证码
     * @param int $UID 用户名
     * @param int $Type 发送方式，默认为邮件，暂时支持邮件方式
     * @return bool true/false
     */
    function sendVerify(int $UID,int $Type=0){}

    /**
     * 生成验证码
     * @param int $UID
     * @return string
     */
    private function createVerifyCode(int $UID){
        //TODO 添加过期时间控制
        $Code = '';
        for($i=0;$i<rand(5,10);$i++){
            $Code.=chr(rand(65,90));
        }
        cache('VerifyCode'.$UID,$Code);
        return $Code;
    }

    /**
     * 验证验证码
     * @param string $Code
     * @param int $UID
     * @return bool
     */
    private function checkVerifyCode(string $Code,int $UID){
        return $Code==cache('VerifyCode'.$UID);
    }
}