<?php
class Authority{
    //登录时鉴别用户是否登录
    public function check_authority(){
    	//如果未开启身份验证，则自动用系统中第一个账户登录。
        if(!C('_auth_.auth_enabled')){
            $users = M('Users');
            $result = $users->select();
            if(count($result)>0){
                $_SESSION['login_user'] = $result[0];
            }
        }
        if(!isset($_SESSION['login_user']) && isset($_COOKIE['ngo20_login_email'])){
        	$user_model = M('Users');
        	$login_email = $_COOKIE['ngo20_login_email'];
        	$login_key = $_COOKIE['ngo20_login_key'];
        	$login_token = $_COOKIE['ngo20_login_token'];
        	
        	$user = $user_model->where(array('email'=>$_COOKIE['ngo20_login_email']))->find();
        	if(!empty($user)){
        		$verify_token = md5($login_key . SALT_KEY . $user['password']);
        		if($login_token == $verify_token){
        			$_SESSION['login_user'] = $user;
        		}
        	}
        }
        $pass=true;
        $admin_table=C('_auth_.auth_admin');
        $login_table = C('_auth_.auth_login');
        if(!isset($_SESSION['login_user']) && ( in_array(strtolower(MODULE_NAME), $login_table) ||
                in_array(strtolower(MODULE_NAME) . '/' . strtolower(ACTION_NAME), $login_table) )){
                    setflash('error','','您需要先登录才能进行以上操作');
                    $pass=false;
                }
        if(!$_SESSION['login_user']['is_admin'] && ( in_array(strtolower(MODULE_NAME), $admin_table) ||
                in_array(strtolower(MODULE_NAME) . '/' . strtolower(ACTION_NAME), $admin_table) )){
                    setflash('error','','您的权限不足');
                    $pass=false;
                }

        if(!$pass){
            $_SESSION['last_page'] = $_SERVER["REQUEST_URI"];
            redirect(__APP__.'/Index/index');
        }
		return true;
    }

    //处理登录时的验证工作
    //@$user_name : 用户名
    //@$pwd : 密码（未加密）
    //@$return : 布尔值，true代表验证成功，false代表验证失败
    public function authorize($user_name,$pwd){
        $users = M('Users');
        $result = $users->where(array('email' => $user_name, 'password' => md5($pwd)))->find();
        if(!$result){
            return false;
        }
        elseif($result['enabled'] == 0){
            return false;
        }
        else{
            //将用户信息存储于session
            $_SESSION['login_user']=$result;
            $authorities=array();
            if($result['is_admin'])
                $authorities['admin']=true;
            
            
            $_SESSION['user_authorities']=$authorities;  //找到并存储这个用户的所有权限
            return true;
        }
    }

    //判断用户是否有某种权限
    //@$auth_name : 正在判断的权限
    //@$return : 布尔值，true=有，false=没有
    public function has($auth_name){
        import('Think.Util.Session');
        if(isset($_SESSION['user_authorities'][$auth_name]))
            return true;
        else
            return false;
    }
    public function check_access($entity_name, $entity_id){
        $relation_model = M('R_user_' . $entity_name);
        $result = $relation_model->where(array('user_id' => $_SESSION['login_user']['id'],
                                               $entity_name . '_id' => $entity_id))->select();
        if(count($result)==0)return false;
        return $result['type'];
    }
}

function X($var){
    if (get_magic_quotes_gpc()) {
        return stripslashes($var);
    }else{
        return mysql_real_escape_string($var);
    }
}
?>