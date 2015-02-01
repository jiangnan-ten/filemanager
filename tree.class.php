<?php
class Tree
{
	public $path;
	public $handle;
	private $data;

	public function __construct($path)
	{
		return $this->path = self::dopath($path);
	}

	/**
	 * 处理路径 编码 特殊字符实体化
	 * @param  [string] $path [绝对路径]
	 * @return [string]
	 */
	public static function dopath($path)
	{
		return iconv('utf-8', 'gbk', htmlspecialchars($path, ENT_QUOTES));
	}

	/**
	 * 打开一个目录
	 * @return resource
	 */
	public function _opendir()
	{
		$handle = @opendir($this->path);
		$handle ? $this->handle = $handle : die($this->path . '不是一个目录');
		return;
	}

	/**
	 * 读取一个目录资源
	 * @return array 目录和文件数组
	 */
	public function _readdir()
	{
		if($this->handle)
		{
			$data = array();

			while( ($file = readdir($this->handle)) !=false )
			{
				if($file != '.' && $file != '..')
				{
					if( is_dir($this->path . '/' .$file) )
					{
						$data['dir'][] = $this->path . '\\' . $file;
					}
					else
					{
						$filetemp = array();
						$filetemp['file'] = $this->path . '\\'. $file; //文件路径
						$filetemp['size'] = $this->getfilesize($this->path . '\\'. $file); //格式化后的文件大小
						$data['file'][] = $filetemp;
					}
				}
			}
			return $this->data = $data;
		}
	}

	/**
	 * 关闭资源
	 */
	public function _closedir()
	{
		if($this->handle)
		{
			closedir($this->handle);
		}
	}

	/**
	 * 返回目录, 文件 数据
	 * @return [array]
	 */
	public function getdata()
	{
		return $this->data;
	}

	/**
	 * 输出的信息转为utf8编码
	 * @return [array]
	 */
	public function datatoutf8()
	{
		$data = $this->data;
		return $this->data = self::_array_map($data);
	}

	public static function _array_map($data)
	{
		$v = is_array($data) ? array_map('self::_array_map', $data) : iconv('gbk', 'utf-8', $data);
		return $v;
	}

	/**
	 * 获取指定路径的文件大小 包括文件
	 * @param [string] [绝对路径]
	 * @return [floatval] [数据size]
	 */
	public function getfilesize($path)
	{
		$filesize = filesize($path);

		if($filesize)
		{
			return self::formatsize($filesize);
		}
		else
			return '0 KB';
	}

	/**
	 * 获取目录信息 总大小(未格式化) 目录总数 文件总数
	 * @param  [string] $path [目录绝对路径]
	 * @return [array]
	 */
	private static function getdirinfo($path)
	{
		static $data = array('size' => 0, 'dir_count' => 0, 'file_count' => 0);

		if( is_dir($path) )
		{
			$h = opendir($path);

			while(  ($file = readdir($h)) != false )
			{
				if( $file != '.' && $file != '..' )
				{
					if( is_dir($path .'/'. $file) )
					{
						$data['dir_count'] += 1;
						self::getdirinfo($path .'/'. $file);
					}
					else
					{
						$data['file_count'] += 1;
						$data['size'] += filesize($path .'/'. $file);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * 格式化后的目录大小
	 * @param  [string] $path [目录绝对路径]
	 * @return [string]       [格式化后的size]
	 */
	public static function getformatdirsize($path)
	{
		$path = self::dopath($path);

		if(is_dir($path))
		{
			$dirinfo = self::getdirinfo($path);
			$stat = stat($path);
			$dirinfo['ctime'] = date('Y-m-d H:i:s', $stat['ctime']); //创建时间
			$dirinfo['utime'] = date('Y-m-d H:i:s', $stat['mtime']); //修改时间
			$dirinfo['size'] = self::formatsize($dirinfo['size']); //格式化大小
			return $dirinfo;
		}

		return;

	}

	/**
	 * 新建目录
	 * @param  [string] $path
	 * @return [bool]
	 */
	public static function newdir($path)
	{
		$status = false;
		$path = self::dopath($path);

		if(! file_exists($path))
		{
			$status = mkdir($path);
		}

		return $status;
	}

	/**
	 * 格式化bytes
	 * @param [floatval] bytes
	 * @return [string] [格式化后的大小]
	 */
	public static function formatsize($bytes)
	{
		if($bytes == 0)
			return 0;

	    $bytes = floatval($bytes);
	    $result = 0;
	    $arBytes = array(
	        0 => array(
	            "UNIT" => "TB","VALUE" => pow(1024, 4)
	        ),
	        1 => array(
	            "UNIT" => "GB", "VALUE" => pow(1024, 3)
	        ),
	        2 => array(
	            "UNIT" => "MB","VALUE" => pow(1024, 2)
	        ),
	        3 => array(
	            "UNIT" => "KB","VALUE" => 1024
	        ),
	        4 => array(
	            "UNIT" => "B","VALUE" => 1
	        ),
	    );

	    foreach($arBytes as $arItem)
	    {
	        if($bytes >= $arItem["VALUE"])
	        {
	            $result = strval(round($bytes / $arItem["VALUE"], 2)) ." ". $arItem["UNIT"];
	            break;
	        }
	    }
	    return $result;
	}
	/**
	 * 调度工作
	 * @return array
	 */
	public function work()
	{
		$this->_opendir();
		$this->_readdir();
		$this->_closedir();
		$this->datatoutf8();
		return $this->getdata();
	}
}