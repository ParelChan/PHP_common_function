<?php
namespace Home\Common;
class CommonFunction{
	public static function checkPermission($userid,$funcode,$actcode){
		
	}
	
	public static function getGUID(){  
		if (function_exists('com_create_guid')){  
			return com_create_guid();  
		}else{  
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.  
			$charid = strtoupper(md5(uniqid(rand(), true)));  
			$hyphen = chr(45);// "-"  
			$uuid = substr($charid, 0, 8).$hyphen  
				.substr($charid, 8, 4).$hyphen  
				.substr($charid,12, 4).$hyphen  
				.substr($charid,16, 4).$hyphen  
				.substr($charid,20,12);
			return $uuid;  
		}  
	} 

	//查询
	public static function getActionCodes($userid,$funcode)
	{
		$model = M('vpermission');
		$list=$model->distinct(true)->field('actcode')->where(" userid='".$userid."' and funcode='".$funcode."' ")->select();
		return $list;
	}
	
	//记录系统日志
	public static function addLog($funcode,$actcode,$desc,$whom){
		$model = M('log');
		$row=array();
		$row['funcode']=$funcode;
		$row['actcode']=$actcode;
		$row['text']=$desc;
		$row['lpt']=date('y-m-d H:i:s',time());
		$row['whom']=$whom;
		return $model->add($row);
	}
	
	//计算Ctext
	public static function calcCText($pwd,$code){
		$key_str=$pwd;
		if(empty($key_str)){
			$key_str="000000";
		}
		
		if(strlen($key_str)>6){
			$key_str=substr($key_str,0, 6);
		}
		$key_str=str_pad($key_str,6,"0",STR_PAD_LEFT);//补0
		
		$pwd=array();
		for($i=0;$i<6;$i++){
			array_push($pwd,(int)$key_str[$i]);
		}
		
		//data
		$random=CommonFunction::rand(4);
		//$random="0000";
		$hex_code=strtoupper(dechex((int)$code));
		$hex_code=str_pad($hex_code,4,"0",STR_PAD_LEFT);
		$hex_code_reverse=CommonFunction::reverseHexString($hex_code);
		$code1=$hex_code_reverse.$random;
		
		$data=array();
		for($i=0;$i<8;$i+=2){
			array_push($data,hexdec($code1[$i].$code1[$i+1]));
		}
		//加密
		$out=array();
		CommonFunction::Encrypt_RC4($data,4,$pwd,6,$out,$outlen);
		//密文
		$outstr='';
		foreach($out as $byte){
			$outstr.=str_pad(dechex($byte),2,"0",STR_PAD_LEFT); //补0 dechex($byte);
		}
		return strtoupper($outstr);
	}

	/// 在报文帧加入空白，方便阅读
	public static function AddSpace($msg){
		$sb='';
		$len = strlen($msg) / 2;
		for ($i = 0; $i < $len; $i++)
		{
			$sb.=(substr($msg,$i * 2, 2)." ");
		}
		return $sb;
	}


	/// 补齐数据长度(补零),在尾部补
	public static function AdjustLengthBottom($source, $length)
	{
		if ($length < strlen($source))
			return $source;
		else
		{
			$bin_len = strlen($source);
			for ($i = 0; $i < $length - $bin_len; $i++)
			{
				$source.="0";
			}
			return $source;
		}
	}
	/// 将16进制字符转化成byte[]，顺序从由高到低转为由低到高
	public static function hexStringToBytes($str)
	{
		$str = trim($str);
		$len = strlen($str) / 2;
		$bytes = array();
		for ($i = 0; $i < $len; $i++)
		{
			//$tem = substr($str,$i * 2,2);
			$tem = substr($str,strlen($str) - (2 * ($i + 1)), 2);
			array_push($bytes,hexdec($tem));
		}
		return $bytes;
	}

	/// 将16进制字符转化成byte[]，顺序不变
	public static function hexStringToBytesKeep($str)
	{
		$str = trim($str);
		$len = strlen($str) / 2;
		$bytes = array();
		for ($i = 0; $i < $len; $i++)
		{
			$tem = substr($str,$i * 2,2);
			array_push($bytes,hexdec($tem));
		}
		return $bytes;
	}
	
	/// 转化bytes成16进制的字符
	public static function BytesToHexStr($bytes)
	{
		$returnStr = "";
		if ($bytes != null)
		{
			for ($i = 0; $i <count($bytes); $i++)
			{
				$returnStr .=dechex($bytes[$i]);
			}
		}
		return $returnStr;
	}
		
	// 将16进制字符串高低字节对换
	public static function reverseHexString($str)
	{
		$buff = array();
		for ($i = 0; $i < strlen($str); $i += 2)
		{
			array_push($buff,$str[strlen($str) - $i - 2]);
			array_push($buff,$str[strlen($str) - 1 - $i]);
		}
		$s = implode('', $buff);
		return $s;
	}
	
	//计算cs码
	public static function CalcCSCode($bytes)
	{
		$temp = 0;
		for ($i = 0; $i <count($bytes); $i++)
		{
			$temp = $temp + ($bytes[$i]);
		}
		return ($temp % 256);
	}
	
	//计算crc16
	function crc16($string) {
	  $crc = 0xFFFF;
	  for ($x = 0; $x < strlen ($string); $x++) {
		$crc = $crc ^ ord($string[$x]);
		for ($y = 0; $y < 8; $y++) {
		  if (($crc & 0x0001) == 0x0001) {
			$crc = (($crc >> 1) ^ 0xA001);
		  } else { $crc = $crc >> 1; }
		}
	  }
	  return $crc;
	}
		

	/**  
     * 转换一个String字符串为byte数组  
     * @param $str 需要转换的字符串  
     * @param $bytes 目标byte数组  
     * @author Zikie  
     */  
      
    public static function getBytes($str) {  
  
        $len = strlen($str);  
        $bytes = array();  
           for($i=0;$i<$len;$i++) {  
               if(ord($str[$i]) >= 128){  
                   $byte = ord($str[$i]);
               }else{  
                   $byte = ord($str[$i]);  
               }  
            $bytes[] =  $byte ;  
        }  
        return $bytes;  
    }
	
	/*public static function getBytes($string) { 
        $bytes = array(); 
        for($i = 0; $i < strlen($string); $i ){ 
             $bytes[] = ord($string[$i]); 
        } 
        return $bytes; 
    } */
     
    /**  
     * 将字节数组转化为String类型的数据  
     * @param $bytes 字节数组  
     * @param $str 目标字符串  
     * @return 一个String类型的数据  
     */  
      
    public static function toStr($bytes) {  
        $str = '';  
        foreach($bytes as $ch) {  
			
            $str .= chr($ch);  
			//dump($str);
        }  
  
        return $str;  
    }  
     
    /**  
     * 转换一个int为byte数组  
     * @param $byt 目标byte数组  
     * @param $val 需要转换的字符串  
     * @author Zikie  
     */  
     
    public static function integerToBytes($val) {  
        $byt = array();  
        $byt[0] = ($val & 0xff);  
        $byt[1] = ($val >> 8 & 0xff);    //   >>：移位    &：与位  
        $byt[2] = ($val >> 16 & 0xff);  
        $byt[3] = ($val >> 24 & 0xff);  
        return $byt;  
    }  
     
    /**  
     * 从字节数组中指定的位置读取一个Integer类型的数据  
     * @param $bytes 字节数组  
     * @param $position 指定的开始位置  
     * @return 一个Integer类型的数据  
     */  
      
    public static function bytesToInteger($bytes, $position) {  
        $val = 0;  
        $val = $bytes[$position + 3] & 0xff;  
        $val <<= 8;  
        $val |= $bytes[$position + 2] & 0xff;  
        $val <<= 8;  
        $val |= $bytes[$position + 1] & 0xff;  
        $val <<= 8;  
        $val |= $bytes[$position] & 0xff;  
        return $val;  
    }  
  
    /**  
     * 转换一个shor字符串为byte数组  
     * @param $byt 目标byte数组  
     * @param $val 需要转换的字符串  
     * @author Zikie  
     */  
     
    public static function shortToBytes($val) {  
        $byt = array();  
        $byt[0] = ($val & 0xff);  
        $byt[1] = ($val >> 8 & 0xff);  
        return $byt;  
    }  
     
    /**  
     * 从字节数组中指定的位置读取一个Short类型的数据。  
     * @param $bytes 字节数组  
     * @param $position 指定的开始位置  
     * @return 一个Short类型的数据  
     */  
      
    public static function bytesToShort($bytes, $position) {  
        $val = 0;  
        $val = $bytes[$position + 1] & 0xFF;  
        $val = $val << 8;  
        $val |= $bytes[$position] & 0xFF;  
        return $val;  
    }  
	
	//生成随机数据
	public static function rand($len)
    {
        $randStr = str_shuffle('ABCDEF1234567890');
		$rand = substr($randStr,0,$len);
		return $rand;
    }
	
	/////////////////////////////////////////////////////////////////////
	// 功能描述:    RC4加密解密算法;
	// 入口参数:    data[data_len] = 要加密或者解密的内容byte[];
	//              key[key_len] = 密钥byte[]
	//              out[out_len] = 结果byte[]
	// 返回值:      0 = 成功 ，-1 = 失败
	/////////////////////////////////////////////////////////////////////
	public static function Encrypt_RC4($data, $data_len, $key, $key_len, &$out, &$out_len)
	{
		//dump($data);dump($data_len);dump($key);dump($key_len);dump($out);dump($out_len);
		if ($data == NULL || $key == NULL)
			return -1;
		
		//dump($key);dump($key_len);dump($mBox);
		if (CommonFunction::GetKey($key, $key_len, $mBox) == -1)
			return -1;
		$i=0;
		$x=0;
		$y=0;

		
		//dump($mBox);
		for ($k = 0; $k < $data_len; $k++)
		{
			$x = ($x + 1) % 256;
			$y = ($mBox[$x] + $y) % 256;
			//CommonFunction::swap_byte($mBox[$x], $mBox[$y]);
			$t=$mBox[$x];
			$mBox[$x] = $mBox[$y];
			$mBox[$y] = $t;
			$out[$k] = $data[$k] ^ $mBox[($mBox[$x] + $mBox[$y]) % 256];
		}
		$out_len = $data_len;
		//dump($out);
		return 0;
	}
	
	public static function GetKey($pass, $pass_len, &$out)
	{
		
		$i=0;$j=0;
		for ($i = 0; $i < 256; $i++)
			$out[$i] = $i;
		for ($i = 0; $i < 256; $i++)
		{
			$j = ($pass[$i % $pass_len] + $out[$i] + $j) % 256;
			//CommonFunction::swap_byte(out[$i], out[$j]);
			$t=$out[$i];
			$out[$i] = $out[$j];
			$out[$j] = $t;
		}
		return 0;
	}
	
	//交换
	private static function swap_byte(&$a, &$b)
	{
		$t=$a;
		$a = $b;
		$b = $t;
	}
	
	public static function getExcel($fileName,$headArr,$data){
		//对数据进行检验
		if(empty($data) || !is_array($data)){
			die("data must be a array");
		}
		//检查文件名
		if(empty($fileName)){
			exit;
		}
		$date = date("Y_m_d",time());
		//$fileName .= "_{$date}.xls";
		//创建PHPExcel对象，注意，不能少了\
		$objPHPExcel = new \PHPExcel();
		$objProps = $objPHPExcel->getProperties();

		//设置表头
		$key = ord("A");
		foreach($headArr as $v){
			$colum = chr($key);
			$objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
			//$objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setAutoSize(true);  
			$objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth(25);
			$key += 1;
		}
		$column = 2;
		$objActSheet = $objPHPExcel->getActiveSheet();
		foreach($data as $key => $rows){ //行写入
			$span = ord("A");
			foreach($rows as $keyName=>$value){// 列写入
				$j = chr($span);
				$objActSheet->setCellValue($j.$column, $value);
				$span++;
			}
			$column++;
		}
		$fileName = iconv("utf-8", "gb2312", $fileName);
		//重命名表
		// $objPHPExcel->getActiveSheet()->setTitle('test');
		//设置活动单指数到第一个表,所以Excel打开这是第一个表
		$objPHPExcel->setActiveSheetIndex(0);
		ob_end_clean();
		ob_start();
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); //文件通过浏览器下载
		exit;

	}

	public static function saveExcel($fileName,$headArr,$data){
		if(empty($data) || !is_array($data)){
			die("data must be a array");
		}
		if(empty($fileName)){
			exit;
		}
		if(!is_dir(dirname($fileName))){
			die("目录不存在");
		}else{
			//dump(dirname($fileName));
		}
		$date = date("Y_m_d",time());

		$objPHPExcel = new \PHPExcel();
		$objProps = $objPHPExcel->getProperties();

		$key = ord("A");
		foreach($headArr as $v){
			$colum = chr($key);
			$objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
			//$objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setAutoSize(true);  
			$objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth(25);
			$key += 1;
		}
		$column = 2;
		$objActSheet = $objPHPExcel->getActiveSheet();
		foreach($data as $key => $rows){ //��д��
			$span = ord("A");
			foreach($rows as $keyName=>$value){// ��д��
				$j = chr($span);
				$objActSheet->setCellValue($j.$column, $value);
				$span++;
			}
			$column++;
		}
		$fileName = iconv("utf-8", "gb2312", $fileName);
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save(str_replace('.php', '.xlsx', $fileName));// __FILE__ =>  excel文件的绝对路径
		return $fileName;
	}

	public static function downloadFile($file_path){
		//$file_path="./Public/file/test.txt";
		//header("Content-type: text/html;charset=utf-8");
		$file_path = iconv("utf-8","gbk",$file_path);
		$file_name=basename($file_path);
		if (!file_exists($file_path)){  //判断文件是否存在
			echo "文件不存在";
			exit();
		}
		$fp = fopen($file_path,"r+") or die('打开文件错误');   //下载文件必须要将文件先打开。写入内存
		$file_size = filesize($file_path);

		//清空缓冲区，不然则乱码
		ob_end_clean();
		ob_start();

		header('Content-Type: application/vnd.ms-excel');
		//按照字节格式返回
		Header("Accept-Ranges:bytes");
		//返回文件大小
		Header("Accept-Length:".$file_size);
		//弹出客户端对话框，对应的文件名
		Header("Content-Disposition:attachment;filename=".$file_name);
		
		//防止服务器瞬间压力增大，分段读取
		$buffer = 1024;
		while(!feof($fp)){
			$file_data = fread($fp,$buffer);
			echo $file_data;
		}
		fclose($fp);
		unlink($file_path);
	}
}