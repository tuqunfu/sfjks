<?php

use think\Db;

/**
 * 获取分类所有子分类
 * @param int $cid 分类ID
 * @return array|bool
 */
function get_category_children($cid)
{
    if (empty($cid)) {
        return false;
    }

    $children = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->select();

    return array2tree($children);
}

/**
 * 根据分类ID获取文章列表（包括子分类）
 * @param int   $cid   分类ID
 * @param int   $limit 显示条数
 * @param array $where 查询条件
 * @param array $order 排序
 * @param array $filed 查询字段
 * @return bool|false|PDOStatement|string|\think\Collection
 */
function get_articles_by_cid($cid, $limit = 10, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->limit($limit)->select();

    return $article_list;
}

/**
 * 根据分类ID获取文章列表，带分页（包括子分类）
 * @param int   $cid       分类ID
 * @param int   $page_size 每页显示条数
 * @param array $where     查询条件
 * @param array $order     排序
 * @param array $filed     查询字段
 * @return bool|\think\paginator\Collection
 */
function get_articles_by_cid_paged($cid, $page_size = 15, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->paginate($page_size);

    return $article_list;
}

/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int   $pid
 * @param int   $level
 * @return array
 */
function array2level($array, $pid = 0, $level = 1)
{
    static $list = [];
    foreach ($array as $v) {
        if ($v['pid'] == $pid) {
            $v['level'] = $level;
            $list[]     = $v;
            array2level($array, $v['id'], $level + 1);
        }
    }
    return $list;
}

/**
 * 构建层级（树状）数组
 * @param array  $array          要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid_name       父级ID的字段名
 * @param string $child_key_name 子元素键名
 * @return array|bool
 */
function array2tree(&$array, $pid_name = 'pid', $child_key_name = 'children')
{
    $counter = array_children_count($array, $pid_name);
    if (!isset($counter[0]) || $counter[0] == 0) {
        return $array;
    }
    $tree = [];
    while (isset($counter[0]) && $counter[0] > 0) {
        $temp = array_shift($array);
        if (isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) {
            array_push($array, $temp);
        } else {
            if ($temp[$pid_name] == 0) {
                $tree[] = $temp;
            } else {
                $array = array_child_append($array, $temp[$pid_name], $temp, $child_key_name);
            }
        }
        $counter = array_children_count($array, $pid_name);
    }

    return $tree;
}

/**
 * 子元素计数器
 * @param array $array
 * @param int   $pid
 * @return array
 */
function array_children_count($array, $pid)
{
    $counter = [];
    foreach ($array as $item) {
        $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
        $count++;
        $counter[$item[$pid]] = $count;
    }

    return $counter;
}

/**
 * 把元素插入到对应的父元素$child_key_name字段
 * @param        $parent
 * @param        $pid
 * @param        $child
 * @param string $child_key_name 子元素键名
 * @return mixed
 */
function array_child_append($parent, $pid, $child, $child_key_name)
{
    foreach ($parent as &$item) {
        if ($item['id'] == $pid) {
            if (!isset($item[$child_key_name]))
                $item[$child_key_name] = [];
            $item[$child_key_name][] = $child;
        }
    }

    return $parent;
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
function delete_dir_file($dir_name)
{
    $result = false;
    if (is_dir($dir_name)) {
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DS . $item)) {
                        delete_dir_file($dir_name . DS . $item);
                    } else {
                        unlink($dir_name . DS . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) {
                $result = true;
            }
        }
    }

    return $result;
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function is_mobile()
{
    static $is_mobile;

    if (isset($is_mobile)) {
        return $is_mobile;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
    ) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}

/**
 * 手机号格式检查
 * @param string $mobile
 * @return bool
 */
function check_mobile_number($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    $reg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';

    return preg_match($reg, $mobile) ? true : false;
}

/*步数转积分*/
function get_jifen($step){
	return $step;
	
}

/*字符串剪切*/
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(mb_strlen($str,$charset)>$length){
        if(function_exists("mb_substr")){
            if($suffix)
                return mb_substr($str, $start, $length, $charset)."...";
            else
                return mb_substr($str, $start, $length, $charset);
        }elseif(function_exists('iconv_substr')) {
            if($suffix)
                return iconv_substr($str,$start,$length,$charset)."...";
            else
                return iconv_substr($str,$start,$length,$charset);
        }
        $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
        $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
        $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
        $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
        if($suffix) return $slice."…";
        return $slice;
    }else{
        return $str;
    }
}

// 地址URL
function getFileUrl($url)
{
	return 'http://'.$_SERVER['SERVER_NAME'].'/public'.$url;
}

/**
    * 获取客户端IP地址
    * @param integer $type
    * @return mixed
    */
function getclientipInfo() {
  static $realip = NULL;
	
  if($realip !== NULL){
	  return $realip;
  }
  if(isset($_SERVER)){
	  if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ //但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。要想透过代理服务器取得客户端的真实 IP 地址，就要使用 $_SERVER["HTTP_X_FORWARDED_FOR"] 来读取。
		  $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		  /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
		  foreach ($arr AS $ip){
			  $ip = trim($ip);
			  if ($ip != 'unknown'){
				  $realip = $ip;
				  break;
			  }
		  }
	  }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){//HTTP_CLIENT_IP 是代理服务器发送的HTTP头。如果是"超级匿名代理"，则返回none值。同样，REMOTE_ADDR也会被替换为这个代理服务器的IP。
		  $realip = $_SERVER['HTTP_CLIENT_IP'];
	  }else{
		  if (isset($_SERVER['REMOTE_ADDR'])){ //正在浏览当前页面用户的 IP 地址
			  $realip = $_SERVER['REMOTE_ADDR'];
		  }else{
			  $realip = '0.0.0.0';
		  }
	  }
  }else{
	  //getenv环境变量的值
	  if (getenv('HTTP_X_FORWARDED_FOR')){//但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。要想透过代理服务器取得客户端的真实 IP 地址
		  $realip = getenv('HTTP_X_FORWARDED_FOR');
	  }elseif (getenv('HTTP_CLIENT_IP')){ //获取客户端IP
		  $realip = getenv('HTTP_CLIENT_IP');
	  }else{
		  $realip = getenv('REMOTE_ADDR');  //正在浏览当前页面用户的 IP 地址
	  }
  }
  preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
  $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
  return $realip;
}



function TryRequest($url, $params, $method = 'GET', $header = array(), $multi = false)
{
	$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER     => $header
	);
	/* 根据请求类型设置特定参数 */
	switch(strtoupper($method)){
		case 'GET':
			$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
			break;
		case 'POST':
			//判断是否传输文件
			$params = $multi ? $params : http_build_query($params);
			$opts[CURLOPT_URL] = $url;
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = $params;
			break;
		default:
			throw new Exception('不支持的请求方式！');
	}
	/* 初始化并执行curl请求 */
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data  = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if($error) var_dump('请求发生错误：' . $error);
	return  $data;
}

function sortbydate($a,$b)
{
	if($a['time']>$b['time'])
	{
		return  -1;
	}
	else
	{
		return 1;
	}
}

function sortbytimeint($a,$b)
{
	if($a['timeint']>$b['timeint'])
	{
		return  -1;
	}
	else
	{
		return 1;
	}
}


function sort_by_tasknum($a,$b)
{
	if($a['total']>$b['total'])
	{
		return  -1;
	}
	else
	{
		return 1;
	}
}

function getStartAndEndUnixTimestamp($year = 0, $month = 0, $day = 0)
{
	if(empty($year))
	{
		$year = date("Y");
	}

	$start_year = $year;
	$start_year_formated = str_pad(intval($start_year), 4, "0", STR_PAD_RIGHT);
	$end_year = $start_year + 1;
	$end_year_formated = str_pad(intval($end_year), 4, "0", STR_PAD_RIGHT);

	if(empty($month))
	{
		//只设置了年份
		$start_month_formated = '01';
		$end_month_formated = '01';
		$start_day_formated = '01';
		$end_day_formated = '01';
	}
	else
	{

		$month > 12 || $month < 1 ? $month = 1 : $month = $month;
		$start_month = $month;
		$start_month_formated = sprintf("%02d", intval($start_month));

		if(empty($day))
		{
			//只设置了年份和月份
			$end_month = $start_month + 1;
			
			if($end_month > 12)
			{
				$end_month = 1;
			}
			else
			{
				$end_year_formated = $start_year_formated;
			}
			$end_month_formated = sprintf("%02d", intval($end_month));
			$start_day_formated = '01';
			$end_day_formated = '01';
		}
		else
		{
			//设置了年份月份和日期
			$startTimestamp = strtotime($start_year_formated.'-'.$start_month_formated.'-'.sprintf("%02d", intval($day))." 00:00:00");
			$endTimestamp = $startTimestamp + 24 * 3600 - 1;
			return array('start' => $startTimestamp, 'end' => $endTimestamp);
		}
	}

	$startTimestamp = strtotime($start_year_formated.'-'.$start_month_formated.'-'.$start_day_formated." 00:00:00");			
	$endTimestamp = strtotime($end_year_formated.'-'.$end_month_formated.'-'.$end_day_formated." 00:00:00") - 1;
	return array('start' => $startTimestamp, 'end' => $endTimestamp);
}

function writeLog($url,$msg){
	$myfile = fopen($url, "a") or die("Unable to open file!");
	$time = date("Y-m-d H:i:s",time());
	$txt = $msg."\t\t".$time."\n";
	fwrite($myfile, $txt);
	fclose($myfile);
}




