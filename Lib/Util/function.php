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

function cif($path){
    $args = explode('/', $path);
    if($args[0] == MODULE_NAME && $args[1] == ACTION_NAME){
        echo 'class="current"';
    }
}

function gif($path){
    $args = explode('=', $path);
    if(isset($_GET[$args[0]]) && $_GET[$args[0]]==$args[1]){
        echo 'class="current"';
    }
}

function etype($type_str){
    $event_types = C('EVENT_TYPES');
    return $event_types[$type_str];
}

function check_p($words){
    foreach(C('_profanity_.profanity_words') as $p_word){
        if(strpos($words, $p_word))
                return false;
    }
    return true;
}

//security check procedure
$getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;  
$postfilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;  
$cookiefilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;  
function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq){  
    if(is_array($StrFiltValue))  
    {  
      $StrFiltValue=implode($StrFiltValue);  
    }  
    if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){  
        print "输入的内容不合适!Improper input!" ;  
        exit();  
    }
} 
foreach($_GET as $key=>$value){  
  StopAttack($key,$value,$getfilter);  
}
foreach($_POST as $key=>$value){  
  StopAttack($key,$value,$postfilter);  
}
foreach($_COOKIE as $key=>$value){  
  StopAttack($key,$value,$cookiefilter);  
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


function res($name){
    if(preg_match('/\.js$/', $name) === 1){
        return __APP__."/Public/js/$name";
    }
    else if(preg_match('/\.css$/', $name) === 1){
        return __APP__."/Public/css/$name";
    }
    else{
        return __APP__."/Public/img/$name";
    }
}

    function getRange($cF, $cE, $T)
    {
        if ($cE != null) {
            $cF = max($cF, $cE);
        }
        if ($T != null) {
            $cF = min($cF, $T);
        }
        return $cF;
    }
    function getLoop($cF, $cE, $T) {
        while ($cF > $T) {
            $cF -= $T - $cE;
        }
        while ($cF < $cE) {
            $cF += $T - $cE;
        }
        return $cF;
    }
        
    function convertLL2MC($lng, $lat) {
        $LLBAND = array(75, 60, 45, 30, 15, 0);
        $LL2MC = array(array( -0.0015702102444, 111320.7020616939, 1704480524535203, -10338987376042340, 26112667856603880, -35149669176653700, 26595700718403920, -10725012454188240, 1800819912950474, 82.5), array(0.0008277824516172526, 111320.7020463578, 647795574.6671607, -4082003173.641316, 10774905663.51142, -15171875531.51559, 12053065338.62167, -5124939663.577472, 913311935.9512032, 67.5), array(0.00337398766765, 111320.7020202162, 4481351.045890365, -23393751.19931662, 79682215.47186455, -115964993.2797253, 97236711.15602145, -43661946.33752821, 8477230.501135234, 52.5), array(0.00220636496208, 111320.7020209128, 51751.86112841131, 3796837.749470245, 992013.7397791013, -1221952.21711287, 1340652.697009075, -620943.6990984312, 144416.9293806241, 37.5), array( -0.0003441963504368392, 111320.7020576856, 278.2353980772752, 2485758.690035394, 6070.750963243378, 54821.18345352118, 9540.606633304236, -2710.55326746645, 1405.483844121726, 22.5), array( -0.0003218135878613132, 111320.7020701615, 0.00369383431289, 823725.6402795718, 0.46104986909093, 2351.343141331292, 1.58060784298199, 8.77738589078284, 0.37238884252424, 7.45));
    
        $T = array('lng'=>$lng, 'lat'=>$lat);
        $T['lng'] = getLoop($T['lng'], -180, 180);
        $T['lat'] = getRange($T['lat'], -74, 74);
        for ($cF = 0; $cF < count($LLBAND); $cF++) {
            if ($T['lat'] >= $LLBAND[$cF]) {
                $cG = $LL2MC[$cF];
                break;
            }
        }
        if (!$cG) {
            for ($cF = count($LLBAND) - 1; $cF >= 0; $cF--) {
                if ($T['lng'] <= - $LLBAND[$cF]) {
                    $cG = $LL2MC[$cF];
                    break;
                }
            }
        }
        $cH = convertor($T, $cG);
        $T = array(round($cH['lng'], 2), round($cH['lat'], 2));
        return $T;
    }
    function convertMC2LL($lng, $lat){
        $MCBAND = array(12890594.86, 8362377.87, 5591021, 3481989.83, 1678043.12, 0);
        $MC2LL =array(array(1.410526172116255e-8, 0.00000898305509648872, -1.9939833816331, 200.9824383106796, -187.2403703815547, 91.6087516669843, -23.38765649603339, 2.57121317296198, -0.03801003308653, 17337981.2), array( -7.435856389565537e-9, 0.000008983055097726239, -0.78625201886289, 96.32687599759846, -1.85204757529826, -59.36935905485877, 47.40033549296737, -16.50741931063887, 2.28786674699375, 10260144.86), array( -3.030883460898826e-8, 0.00000898305509983578, 0.30071316287616, 59.74293618442277, 7.357984074871, -25.38371002664745, 13.45380521110908, -3.29883767235584, 0.32710905363475, 6856817.37), array( -1.981981304930552e-8, 0.000008983055099779535, 0.03278182852591, 40.31678527705744, 0.65659298677277, -4.44255534477492, 0.85341911805263, 0.12923347998204, -0.04625736007561, 4482777.06), array(3.09191371068437e-9, 0.000008983055096812155, 0.00006995724062, 23.10934304144901, -0.00023663490511, -0.6321817810242, -0.00663494467273, 0.03430082397953, -0.00466043876332, 2555164.4), array(2.890871144776878e-9, 0.000008983055095805407, -3.068298e-8, 7.47137025468032, -0.00000353937994, -0.02145144861037, -0.00001234426596, 0.00010322952773, -0.00000323890364, 826088.5));

        $cE = array('lng'=>$lng, 'lat'=>$lat);
        $cF = array('lng'=>abs($lng), 'lat'=>abs($lat));
        for($cG=0; $cG<count($MCBAND); $cG++){
            if($cF['lat'] >= $MCBAND[$cG]){
                $cH = $MC2LL[$cG];
                break;
            }
        }
        $T = convertor($cE, $cH);
        $cE = array(round($T['lng'], 6), round($T['lat'], 6));
        return $cE;
    }
    function convertor($cF, $cG) {
        if (!$cF || !$cG) {
            return false;
        }
        $T = $cG[0] + $cG[1] * abs($cF['lng']);
        $cE = abs($cF['lat']) / $cG[9];
        $cH = $cG[2] + $cG[3] * $cE + $cG[4] * $cE * $cE + $cG[5] * $cE * $cE * $cE + $cG[6] * $cE * $cE * $cE * $cE + $cG[7] * $cE * $cE * $cE * $cE * $cE + $cG[8] * $cE * $cE * $cE * $cE * $cE * $cE;
        $T *= ($cF['lng'] < 0 ? -1: 1);
        $cH *= ($cF['lat'] < 0 ? -1: 1);
        return array("lng" => $T, "lat" => $cH);
    }
        
    

?>
