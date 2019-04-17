<?php
namespace PacoCommon;
include __DIR__ . '/CommonFunction.class.php';
use Home\Common\CommonFunction as CommonFunction;

class CommonClass{
	public static $Glist = array();//Session+Code+Clientid

	public static $Wlist=array();

    public static function getList(){
        return self::$Glist;
    }
    public static function setList($list){
        self::$Glist=$list;
	}
	
	public static function checkPermission($userid,$funcode,$actcode){
		
	}
	
	public static function  startWith($str, $needle) {
		return strpos($str, $needle) === 0;
	}
	
	public static function  endWith($haystack, $needle) {   
		$length = strlen($needle);  
		if($length == 0)
		{   
			return true;  
		}
		return (substr($haystack, -$length) === $needle);
	}

	/**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination  写入目标
     * @return void
     */
    public static function writeLog($log,$destination='') {
		ini_set('date.timezone','Asia/Shanghai');
        $now=date('y-m-d H:i:s');
        if(empty($destination)){
            $destination = date('y_m_d').'.log';
        }
        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }        
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if(is_file($destination) && floor(2097152) <= filesize($destination) ){
            rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        }
        error_log("[{$now}] \r\n{$log}\r\n", 3,$destination);
	}
	
	public static function printLog($log) {
		ini_set('date.timezone','Asia/Shanghai');
		$now=date('y-m-d H:i:s');
		echo("[{$now}] \r\n{$log}\r\n");
	}
	
	public static function doLog($log,$destination='') {
		self::printLog($log);
		self::writeLog($log,$destination);
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
	
	private static function GetKey($pass, $pass_len, &$out)
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
}