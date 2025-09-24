<?php
namespace app\api\controller;

use think\Controller;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use think\Db;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
 // config.php
class Upload extends Controller
{
	protected $auth;
	protected $uploadMgr;
	protected $token;
    protected function initialize()
    {
        parent::initialize();
        if (!Session::has('admin_id')) {
            $result = [
                'error'   => 1,
                'message' => '未登录'
            ];
            return json($result);
        }
		
		$this->auth = new Auth('DiVaxaEuuNtojBFs_nyib1MuAO2WHppGJj8mhMzP', 'lYwihpJogUfEtEn6xC05PzzahQxmFpDgwCWuIA0T');
		$this->token = $this->auth->uploadToken('cytask');
		$this->uploadMgr = new UploadManager();
    }
	
	public function loadfile()
	{
		$param =$this->request->param();
		$httphost = dirname(dirname(dirname(dirname(__FILE__))));
		$filename = session($param[1]);
        $filepath = $httphost.'/themes/'.$param[1].".xls";
        $this->download($filepath, $filename.".xls");
	}
	
    function download ($filename, $showname='',$content='',$expire=180) 
	{
		$path = dirname(dirname(dirname(dirname(__FILE__))));
        if(is_file($filename)) {
            $length = filesize($filename);
        }elseif(is_file($path.$filename)) {
            $filename = $path.$filename;
            $length = filesize($filename);
        }elseif($content != '') {
            $length = strlen($content);
        }else {
           
        }
        if(empty($showname)) {
            $showname = $filename;
        }
        $showname = basename($showname);
		if(!empty($filename)) {
			$finfo 	= 	new \finfo(FILEINFO_MIME);
			$type 	= 	$finfo->file($filename);			
		}else{
			$type	=	"application/octet-stream";
		}
        //发送Http Header信息 开始下载
        header("Pragma: public");
        header("Cache-control: max-age=".$expire);
        //header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Expires: " . gmdate("D, d M Y H:i:s",time()+$expire) . "GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
        header("Content-Disposition: attachment; filename=".$showname);
        header("Content-Length: ".$length);
        header("Content-type: ".$type);
        header('Content-Encoding: none');
        header("Content-Transfer-Encoding: binary" );
        if($content == '' ) {
            readfile($filename);
        }else {
        	echo($content);
        }
        exit();
    }
	
    public function upload()
    {
        $config = [
            'size' => 50566629,
            'ext'  => 'jpg,gif,png,bmp,jpeg'
        ];
        $file = $this->request->file('file');
        $upload_path = str_replace('\\', '/', dirname(dirname(dirname(dirname(__FILE__)))) . '/public/uploads');
        $save_path   = '/uploads/';
        $info        = $file->validate($config)->move($upload_path);
		$url = str_replace('\\', '/', $save_path . $info->getSaveName());
		$xurl = Str_Replace(".","_x.",$url);
		$thumb = $this->Img(dirname(__FILE__).'public'.$url);
        if ($info) {
            $result = [
                'error' => 0,
                'url'   => $url,
				'xurl'  => $xurl
            ];
        } else {
            $result = [
                'error'   => 1,
                'message' => $file->getError()
            ];
        }
        return json($result);
    }

    // 上传字体文件
	public function upload_font()
    {
        $config = [
            'size' => 50566629,
            'ext'  => 'ttf,ttc'
        ];
        $file = $this->request->file('file');
        
        $upload_path = str_replace('\\', '/', dirname(dirname(dirname(dirname(__FILE__)))) . '/public/fonts');
        $save_path   = '/fonts/';
        $info        = $file->validate($config)->move($upload_path);
		$url = str_replace('\\', '/', $save_path . $info->getSaveName());
        if ($info) {
            $result = [
                'error' => 0,
                'url'   => $url
            ];
        } else {
            $result = [
                'error'   => 1,
                'message' => $file->getError()
            ];
        }
        return json($result);
    }
	
    public function uploadapk()
    {
        $config = [
            'size' => 20971520000,
            'ext'  => 'apk,lua,json'
        ];
		$result = array();
		// 上传到七牛云
		$fileinfo = $_FILES['file'];
        $file = $this->request->file('file');
        $upload_path = str_replace('\\', '/', dirname(dirname(dirname(dirname(__FILE__)))) . '/public/uploads/app/');
        $save_path   = '/uploads/app/';
		$app_name 	 = $fileinfo['name'];
        $info        = $file->validate($config)->move($upload_path,$app_name);
		
        list($ret, $err) = $this->uploadMgr->putFile($this->token, 'public/uploads/app/'.$info->getSaveName(), $fileinfo['tmp_name']);
        if ($err !== null) {
            $s =  ['err' => 1, 'msg' => $err, 'data' => ''];
			$result = [
                'error'   => 1,
                'message' => '文件上传七牛云失败！'
            ];
        } else {
            //返回图片的完整URL
			$url = str_replace('\\', '/', $save_path.$info->getSaveName());
			$xurl = Str_Replace(".","_x.",$url);
            if ($info) {
				$result = [
					'error' => 0,
					'url'   => $url,
					'xurl'  => $xurl
				];
			} else {
				$result = [
					'error'   => 1,
					'message' => $file->getError()
				];
			}
        }
        return json($result);
    }
	
	
	/*补丁*/
	public function uploadpatch()
    {
        $config = [
            'size' => 20971520000,
            'ext'  => 'apk,lua,json'
        ];
        $file = $this->request->file('file');
        $upload_path = str_replace('\\', '/', dirname(dirname(dirname(dirname(__FILE__)))) . '/public/uploads/app/');
        $save_path   = '/uploads/app/';
		$app_name 	 = 'com_chuangyun_auto';
        $info        = $file->validate($config)->move($upload_path,$app_name);
		$url = str_replace('\\', '/', $save_path . $info->getSaveName());
		$xurl = Str_Replace(".","_x.",$url);
        if ($info) {
            $result = [
                'error' => 0,
                'url'   => $url,
				'xurl'  => $xurl
            ];
        } else {
            $result = [
                'error'   => 1,
                'message' => $file->getError()
            ];
        }
        return json($result);
    }
	
	/*json*/
	public function uploadjson()
    {
        $config = [
            'size' => 20971520000,
            'ext'  => 'json'
        ];
        $file = $this->request->file('file');
        $upload_path = str_replace('\\', '/', Config('application.jsonurl'));
		$app_name 	 = 'app_'.time();
        $info        = $file->validate($config)->move($upload_path,$app_name);
		$url = $info->getSaveName();
        if ($info) {
            $result = [
                'error' => 0,
                'url'   => $url
            ];
        } else {
            $result = [
                'error'   => 1,
                'message' => $file->getError()
            ];
        }
        return json($result);
    }
	
	
	function  Img($Image,$Dw=450,$Dh=450,$Type=2)
	{
	  IF(!File_Exists($Image)){
	  Return False;
	  }
	  //如果需要生成缩略图,则将原图拷贝一下重新给$Image赋值
	  IF($Type!=1){
	  Copy($Image,Str_Replace(".","_x.",$Image));
	  $Image=Str_Replace(".","_x.",$Image);
	  }
	  //取得文件的类型,根据不同的类型建立不同的对象
	  $ImgInfo=GetImageSize($Image);
	  Switch($ImgInfo[2]){
	  Case 1:
	  $Img = @ImageCreateFromGIF($Image);
	  Break;
	  Case 2:
	  $Img = @ImageCreateFromJPEG($Image);
	  Break;
	  Case 3:
	  $Img = @ImageCreateFromPNG($Image);
	  Break;
	  }
	  //如果对象没有创建成功,则说明非图片文件
	  IF(Empty($Img)){
	  //如果是生成缩略图的时候出错,则需要删掉已经复制的文件
	  IF($Type!=1){Unlink($Image);}
	  Return False;
	  }
	  //如果是执行调整尺寸操作则
	  IF($Type==1){
	  $w=ImagesX($Img);
	  $h=ImagesY($Img);
	  $width = $w;
	  $height = $h;
	  IF($width>$Dw){
	   $Par=$Dw/$width;
	   $width=$Dw;
	   $height=$height*$Par;
	   IF($height>$Dh){
	   $Par=$Dh/$height;
	   $height=$Dh;
	   $width=$width*$Par;
	   }
	  }ElseIF($height>$Dh){
	   $Par=$Dh/$height;
	   $height=$Dh;
	   $width=$width*$Par;
	   IF($width>$Dw){
	   $Par=$Dw/$width;
	   $width=$Dw;
	   $height=$height*$Par;
	   }
	  }Else{
	   $width=$width;
	   $height=$height;
	  }
	  $nImg = ImageCreateTrueColor($width,$height);   //新建一个真彩色画布
	  ImageCopyReSampled($nImg,$Img,0,0,0,0,$width,$height,$w,$h);//重采样拷贝部分图像并调整大小
	  ImageJpeg ($nImg,$Image);     //以JPEG格式将图像输出到浏览器或文件
	  Return True;
	  //如果是执行生成缩略图操作则
	  }Else{
	  $w=ImagesX($Img);
	  $h=ImagesY($Img);
	  $width = $w;
	  $height = $h;
	  $nImg = ImageCreateTrueColor($Dw,$Dh);
	  IF($h/$w>$Dh/$Dw){ //高比较大
	   $width=$Dw;
	   $height=$h*$Dw/$w;
	   $IntNH=$height-$Dh;
	   ImageCopyReSampled($nImg, $Img, 0, -$IntNH/1.8, 0, 0, $Dw, $height, $w, $h);
	  }Else{   //宽比较大
	   $height=$Dh;
	   $width=$w*$Dh/$h;
	   $IntNW=$width-$Dw;
	   ImageCopyReSampled($nImg, $Img, -$IntNW/1.8, 0, 0, 0, $width, $Dh, $w, $h);
	  }
	  ImageJpeg ($nImg,$Image);
	  Return True;
	  }
	}
}