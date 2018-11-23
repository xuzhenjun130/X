<?php
/**
 * 用户登录操作类
 * User: sherman
 * Date: 2017/9/26
 * Time: 15:31
 */

namespace X;


class User extends Component
{

    private $isLogin = false;

    /**
     * 登录用户身份信息
     * @var array
     */
    private $identity = [];
    /**
     * session 或者 cookie 的key
     * @var string
     */
    public $storeKey = 'user_login';
    /**
     * @var int cookie 过期时间
     */
    public $cookieExpire = 604800;

    /**
     * 获取用户登录信息
     * @return array|mixed
     */
    public function getIdentity(){
        if(!empty($this->identity)){
            return $this->identity;
        }
        if(isset($_SESSION[$this->storeKey])) {
            $this->identity = $_SESSION[$this->storeKey];
        }
        if(!empty($_COOKIE[$this->storeKey])){
            $data = $this->authCode($_COOKIE[$this->storeKey],'DECODE');
            $this->identity = json_decode($data,true);
        }
        return $this->identity;
    }
    /**
     * 登录
     * @param $identity
     * @param bool $remember
     * @return array|bool
     */
    public function login($identity,$remember=false){
        $this->identity = $identity;
        $this->saveLoginSession();
        if($remember){
            return $this->saveLoginCookie();
        }
        return true;
    }

    /**
     * 检查是否已经登录
     * @return bool
     */
    public function getIsLogin(){
        if(isset($_SESSION[$this->storeKey])) return true;
        if(!empty($_COOKIE[$this->storeKey])){
            $data = $this->authCode($_COOKIE[$this->storeKey],'DECODE');
            $this->identity = json_decode($data,true);
            $this->saveLoginSession();
        }
        return false;
    }

    /**
     * 退出
     */
    public function logout(){
        $this->identity = null;
        $this->isLogin = false;
        unset($_SESSION[$this->storeKey]);
        setcookie($this->storeKey,'');
        @session_regenerate_id();
    }

    /**
     * 保存登录信息到session
     * @return array
     */
    private function saveLoginSession(){
        return $_SESSION[$this->storeKey] = $this->identity;
    }

    /**
     * 保存登录信息到cookie
     * @return bool
     */
    private function saveLoginCookie(){

        $data = $this->authCode(json_encode($this->identity));
        $rs =  setcookie($this->storeKey,$data,time()+ $this->cookieExpire);
        if($rs) $this->isLogin = true;
        return $rs;
    }

    /**
     * 密码验证
     * @param $password
     * @param $hash
     * @return bool
     */
    public  function passwordVerify($password,$hash){
        return password_verify($password,$hash);
    }

    /**
     * hash 密码
     * @param $password
     * @return bool|string
     */
    public function passwordHash($password){
        return password_hash($password,PASSWORD_DEFAULT);
    }


    /**
     * discuz 的可逆加密解密函数
     * @param string $string 明文 或 密文
     * @param string $operation DECODE 表示解密,其它表示加密
     * @param string $key 密匙
     * @param int $expiry 密文有效期
     * @return string
     */
    public function authCode($string, $operation = 'ENCODE', $key = '', $expiry = 0) {

        $ckey_length = 4; // 随机密钥长度 取值 0-32;
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
        // 当此值为 0 时，则不产生随机密钥

        $key = md5($key ? $key : 'fsdf*(&^&%^%^%$345345324523sdfsf');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }


}