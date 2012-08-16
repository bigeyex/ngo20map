<?php
if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6.0') !== false){
	$is_ie6 = true;
}
else{
	$is_ie6 = false;
}
//type can be 'error'/'notice'/'ok'/'mail'
function setflash($type, $title, $content){
    $_SESSION['flash']['type'] = $type;
    $_SESSION['flash']['title'] = $title;
    $_SESSION['flash']['content'] = $content;
}

function check_p($words){
    foreach(C('_profanity_.profanity_words') as $p_word){
        if(strpos($words, $p_word))
                return false;
    }
    return true;
}

function check_model(){
    $model_words = implode('',$_POST);
    return check_p($model_words);
}


/* 下面的部分是为了防止sql注入准备的。*/
function checknslash(){
    if(!get_magic_quotes_gpc()){
        foreach($_GET as $get_key=>$get_var){
            if(!is_numeric($get_var)){
                $_GET[$get_key] = addslashes($get_var);
            }
        }
        foreach($_POST as $get_key=>$get_var){
            if(!is_numeric($get_var)){
                $_POST[$get_key] = addslashes($get_var);
            }
        }
    }
}

//将事件主办方字段转换为方便显示的字符串
function build_view_host_string($string){
	$item_array = explode(',', $string);
	$output_array = array();
	foreach($item_array as $item){
		if(!empty($item)){
			$output_array[] = '<a href="'.U('User/search').'/keyword/'.$item.'" class="sub-title" target="_blank">'.$item.'</a>';
		}
	}
	return implode(', ',$output_array);
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr"))
        return mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}

?>
