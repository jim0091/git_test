<?php
/*
 * 京东产品导入
 */

namespace Home\Controller;
class SyncJdController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->url='http://www.lishe.cn';
		$this->modelItem=M('sysitem_item');
		$this->modelItemImg=M('sysitem_outer_img');
	}
	
	public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents("/data/www/b2b2c/public/business/logs/".$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
		}
	}
	
	public function insertJd(){
		header("content-type:text/html;charset=utf-8");
		$data=trim($_POST['data']);
		$arr=json_decode($data,true);
	    $this->makeLog('syncJd',$data);
	    if(empty($arr)){		
		    $jdInfo=array(
				'code'=>'-2',
				'result'=>'Data is empty'
			);
			echo json_encode($jdInfo);
			exit;
		}
	    $Item = M('sysitem_item');
	    $Brand = M('syscategory_brand');
		foreach($arr as $key=>$goods){			
			$good = $goods['detail'];
			if($good['sku']>0){			
				$Item_List = $Item->where('jd_sku="'.$good['sku'].'"')->field('item_id,image_default_id')->find();
				if($Item_List['item_id']>0){
					$this->saveImg($Item_List['item_id'],$good['imagepath'],$goods['imageList']);
					
					if(!file_exists($Item_List['image_default_id'])){					
						$image_default_path = C('JD_IMG_PATH').$this->GrabImage($good['imagepath'],$good['sku'],'default','d');			
						$imageList = $goods['imageList'];
						$list_image = '';
						$outerImg='';
						foreach($imageList as $keys=>$list){
							$list_image.=C('JD_IMG_PATH').$this->GrabImage($list['path'],$list['sku'],$list['id'],'l').",";
							$outerImg.=$list['path'].',';
						}
						$list_image = substr($list_image, 0, -1); 
						$outerImg = substr($outerImg, 0, -1);
						$data=array(
							'image_default_id'=>$image_default_path,//默认图片地址
						 	'list_image'=>$list_image,//图片
						);
						$Item->where('item_id='.$Item_List['item_id'])->save($data);
					}

					//更新商品信息 20170329 by zhangxiaobo
					$itemId = $Item_List['item_id'];
					$price = $good['jdPrice']*C('JD_PRICE_DISCOUNT')/100;//销售价
					$price = round($price,1);
					if($price<$good['price']){
						$price=$good['jdPrice'];
					}
					$ItemInfo = array(
							'title'=>$good['name'],//商品名称
							'price'=>$price,//销售价
							'cost_price'=>$good['price'],//商品成本价
							'mkt_price'=>$good['jdPrice'],//市场价
							'jd_price'=>$good['jdPrice'],//京东价
							'profit_rate'=>($price-$good['price'])*100/$price,//利润率
							'weight'=>$good['weight'],//重量
							'point'=>round($price*100),
							'modified_time'=>time()//更新时间
							);
					$Item->where("item_id = ".$itemId)->save($ItemInfo);
					//更新SKU信息
					$SkuModel = M('sysitem_sku');
					$SkuInfo = array(
							'title'=>$good['name'],
							'price'=>$price,
							'cost_price'=>$good['price'],
							'mkt_price'=>$good['jdPrice'],
							'weight'=>$good['weight'],
							'point'=>round($price*100),
							'modified_time'=>time()//更新时间
					);
					$SkuModel->where("item_id = %d", array($itemId))->save($SkuInfo);
					//更新商品详情
					$ItemDescModel = M('sysitem_item_desc');
					$ItemDescModel->where("item_id = %d", $itemId)->setField('pc_desc', addslashes($good['introduction']));
					//更新上下架状态
					$ItemStatusModel = M('sysitem_item_status');
					$profit=($price-$good['price'])*100/$good['price'];
					if($profit<C('JD_PROFIT_RATE')){
						$status='instock';//下架
						$ItemStatusModel->where("item_id = %d", array($itemId))->setField('approve_status', $status);
					}else{
						$status='onsale';//上架
					}

					$jdInfo=array(
						'code'=>'1',
						'result'=>'exists'
					);
					echo json_encode($jdInfo);
					exit;
				}
				 
				$price = $good['jdPrice']*C('JD_PRICE_DISCOUNT')/100;//销售价
				$price = round($price,1);
				if($price<$good['price']){
					$price=$good['jdPrice'];
				}			
				$Brand_List = $Brand->where('brand_name like "%'.$good['brandname'].'%"')->field('brand_id')->find();
				$Brand_id = 0;			
				if($Brand_List){
					$Brand_id = $Brand_List['brand_id'];
				}else{
					$Brand_Data['brand_name'] = $good['brandname'];
					$Brand_id = $Brand->data($Brand_Data)->add();
				}
				 
				$image_default_path = C('JD_IMG_PATH').$this->GrabImage($good['imagepath'],$good['sku'],'default','d');			
				$imageList = $goods['imageList'];
				$list_image = '';
				$outerImg='';
				foreach($imageList as $keys=>$list){
					$list_image.=C('JD_IMG_PATH').$this->GrabImage($list['path'],$list['sku'],$list['id'],'l').",";
					$outerImg.=$list['path'].',';
				}
				$list_image = substr($list_image, 0, -1); 
				$outerImg = substr($outerImg, 0, -1);      
				//price ,jdprice 
				$Item_Data = array('jd_sku'=>$good['sku'],//京东SKU
					 'shop_id'=>C('JD_SHOP_ID'),//店铺ID
					 'cat_id'=>0,//系统商品分类ID
					 'brand_id'=>$Brand_id,//品牌ID
					 'shop_cat_id'=>0,//店铺分类ID
					 'title'=>$good['name'],//商品名称
					 'sub_title'=>'',//商品子标题
					 'bn'=>$good['sku'],//商品编码
					 'price'=>$price,//销售价
					 'point'=>round($price*100),
					 'cost_price'=>$good['price'],//商品成本价
					 'mkt_price'=>$good['jdPrice'],//市场价
					 'jd_price'=>$good['jdPrice'],//京东价
					 'profit_rate'=>($price-$good['price'])*100/$price,//利润率
					 'weight'=>$good['weight'],//重量
					 'image_default_id'=>$image_default_path,//默认图片地址
					 'list_image'=>$list_image,//图片
					 'order_sort'=>0,//排序
					 'modified_time'=>time(),//更新时间
					 'has_discount'=>0,//是否支持会员打折
					 'is_virtual'=>0,//是否虚拟商品
					 'is_timing'=>0,//是否定时上下架
					 'violation'=>0,//是否违规
					 'is_selfshop'=>0,//是否自营
					 'nospec'=>0,//无说明
					 'spec_desc'=>'',//销售属性序列化
					 'props_name'=>'',//商品属性序列化
					 'params'=>'',//商品参数序列化
					 'sub_stock'=>1,//是否支持下单减库存
					 'outer_id'=>$good['sku'],//商家外部编码
					 'is_offline'=>0,//是否线下商品
					 'barcode'=>$good['sku'],//商品级别的条形码
					 'disabled'=>0,
					 'use_platform'=>1,//使用平台 0全部1PC2WAP端
					 'jd_category'=>$good['category'], //'京东商品分类ID串
					);
				$Item_Id = $Item->data($Item_Data)->add();
				$this->saveImg($Item_Id,$good['imagepath'],$goods['imageList']);				
				$Item_Desc = M('sysitem_item_desc');
				$Item_Desc_Data = array('item_id'=>$Item_Id,'pc_desc'=>addslashes($good['introduction']));
				$Item_Desc->data($Item_Desc_Data)->add();
				$Item_Status = M('sysitem_item_status');			
				//自动判断上下架 赵尊杰
				$profit=($price-$good['price'])*100/$good['price'];
				if($profit<C('JD_PROFIT_RATE')){
					//下架
					$status='instock';
				}else{
					//上架
					$status='onsale';
				}
				$Item_Status_Data = array('item_id'=>$Item_Id,'shop_id'=>C('JD_SHOP_ID'),'approve_status'=>$status);
				$Item_Status->data($Item_Status_Data)->add();			
				$Item_Store = M('sysitem_item_store');
				$Item_Store_Data = array('item_id'=>$Item_Id,'store'=>100000,'freez'=>0);
				$Item_Store->data($Item_Store_Data)->add();			
				$Item_Count = M('sysitem_item_count');
				$Item_Count_Data = array('item_id'=>$Item_Id,'sold_quantity'=>0,'rate_count'=>0,'rate_good_count'=>0,'rate_neutral_count'=>0,'rate_bad_count'=>0,'view_count'=>0,'buy_count'=>0);
				$Item_Count->data($Item_Count_Data)->add();				
				$Sku_Data = array('item_id'=>$Item_Id,
								'title'=>$good['name'],
								'bn'=>$good['sku'],
								'price'=>$price,
								'point'=>round($price*100),
								'cost_price'=>$good['price'],
								'mkt_price'=>$good['jdPrice'],
								'barcode'=>$good['sku'],
								'weight'=>$good['weight'],
								'properties'=>'',
								'spec_info'=>'',
								'spec_desc'=>'',
								'status'=>'normal',
								'outer_id'=>'',
								);
				$Sku = M('sysitem_sku');
				$sku_id = $Sku->data($Sku_Data)->add();			
				$Item_Store = M('sysitem_sku_store');
				$Item_Store_Data = array('item_id'=>$Item_Id,'sku_id'=>$sku_id,'store'=>100000,'freez'=>0);
				$Item_Store->data($Item_Store_Data)->add();			
				if($Item_Id){
					$jdInfo=array(
					'code'=>'1',
					'result'=>'success'
					);
	            	 
				}else{
					$jdInfo=array(
					'code'=>'0',
					'result'=>'failed'
					);				
				}
			}else{
				$jdInfo=array(
					'code'=>'-1',
					'result'=>'Data exception'
				);
			}
			echo json_encode($jdInfo);		
		}
	}
	
	public function InsertData(){
		set_time_limit(0);
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		
		$header_url = $this->url."/business/index.php/SyncJd/InsertData/?page=".($page+1);
		$JD_API = C('JD_API')."?size=2&page=".$page;
		echo '正在请求第'.$page.'页...<br />';
		$goods_list = $this->doRequest($JD_API);
		echo '数据请求完毕...<br />';
		sleep(2);
		echo '数据处理开始...<br />';
		if($goods_list){
			if($goods_list['code']==100)
			{
				if($goods_list['data'])
				{
						
					foreach($goods_list['data'] as $key=>$goods)
					{
						$Item = M('sysitem_item');
						$good = $goods['detail'];
						echo $good['sku'].'请求开始...<br />';
						if(empty($good['sku'])){
							echo '数据错误...<br />';
							continue;
						}						
						
						$Item_List = $Item->where('jd_sku="'.$good['sku'].'"')->field('item_id,image_default_id')->find();
						if($Item_List){
							echo $good['sku'].'数据已存在，跳过写入...<br />';
							if(!file_exists(str_replace('http://www.lishe.cc/images/',C('JD_UPLOAD'),$Item_List['image_default_id']))){
								echo $Item_List['item_id'].'图片不存在，请求中...<br />';
								echo '请求.'.$Item_List['image_default_id'].'<br />';
								$this->GrabImage($good['imagepath'],$good['sku'],'default','d');
								$imageList=$goods['imageList'];
								foreach($imageList as $keys=>$list){
									echo '请求.'.$list['path'].'<br />';
									$this->GrabImage($list['path'],$list['sku'],$list['id'],'l').",";
								}
								echo $Item_List['item_id'].'-'.$good['sku'].'请求完成<br />';
								//exit;
							}
							continue;
						}
						$price = $good['jdPrice']*C('JD_PRICE_DISCOUNT')/100;//销售价
						$price = round($price,1);
						$Brand = M('syscategory_brand');
						$Brand_List = $Brand->where('brand_name like "%'.$good['brandname'].'%"')->field('brand_id')->find();
						$Brand_id = 0;
						
						if($Brand_List){
							$Brand_id = $Brand_List['brand_id'];
						}else{
							$Brand_Data['brand_name'] = $good['brandname'];
							$Brand_id = $Brand->data($Brand_Data)->add();
						}
						echo $good['sku'].'抓取主图<br />';	
						$image_default_path = C('JD_IMG_PATH').$this->GrabImage($good['imagepath'],$good['sku'],'default','d');
						echo $good['sku'].'抓取列表图<br />';
						$imageList = $goods['imageList'];
						$list_image = '';
						foreach($imageList as $keys=>$list){
							$list_image.=C('JD_IMG_PATH').$this->GrabImage($list['path'],$list['sku'],$list['id'],'l').",";
						}
						echo $good['sku'].'写入数据<br />';
						//price ,jdprice 
						$Item_Data = array('jd_sku'=>$good['sku'],//京东SKU
							 'shop_id'=>C('JD_SHOP_ID'),//店铺ID
							 'cat_id'=>0,//系统商品分类ID
							 'brand_id'=>$Brand_id,//品牌ID
							 'shop_cat_id'=>0,//店铺分类ID
							 'title'=>$good['name'],//商品名称
							 'sub_title'=>'',//商品子标题
							 'bn'=>$good['sku'],//商品编码
							 'price'=>$price,//销售价
							 'cost_price'=>$good['price'],//商品成本价
							 'mkt_price'=>$good['jdPrice'],//市场价
							 'jd_price'=>$good['jdPrice'],//京东价
							 'profit_rate'=>($price-$good['price'])*100/$price,//利润率
							 'weight'=>$good['weight'],//重量
							 'image_default_id'=>$image_default_path,//默认图片地址
							 'list_image'=>$list_image,//图片
							 'order_sort'=>0,//排序
							 'modified_time'=>time(),//更新时间
							 'has_discount'=>0,//是否支持会员打折
							 'is_virtual'=>0,//是否虚拟商品
							 'is_timing'=>0,//是否定时上下架
							 'violation'=>0,//是否违规
							 'is_selfshop'=>0,//是否自营
							 'nospec'=>0,//无说明
							 'spec_desc'=>'',//销售属性序列化
							 'props_name'=>'',//商品属性序列化
							 'params'=>'',//商品参数序列化
							 'sub_stock'=>1,//是否支持下单减库存
							 'outer_id'=>$good['sku'],//商家外部编码
							 'is_offline'=>0,//是否线下商品
							 'barcode'=>$good['sku'],//商品级别的条形码
							 'disabled'=>0,
							 'use_platform'=>1,//使用平台 0全部1PC2WAP端
							 'jd_category'=>$good['category'], //'京东商品分类ID串
							);
						$Item_Id = $Item->data($Item_Data)->add();			
						$Item_Desc = M('sysitem_item_desc');
						$Item_Desc_Data = array('item_id'=>$Item_Id,'pc_desc'=>addslashes($good['introduction']));
						$Item_Desc->data($Item_Desc_Data)->add();
						$Item_Status = M('sysitem_item_status');			
						//自动判断上下架 赵尊杰
						$profit=($price-$good['price'])*100/$good['price'];
						if($profit<C('JD_PROFIT_RATE')){
							//下架
							$status='instock';
						}else{
							//上架
							$status='onsale';
						}
						$Item_Status_Data = array('item_id'=>$Item_Id,'shop_id'=>C('JD_SHOP_ID'),'approve_status'=>$status);
						$Item_Status->data($Item_Status_Data)->add();
						
						$Item_Store = M('sysitem_item_store');
						$Item_Store_Data = array('item_id'=>$Item_Id,'store'=>100000,'freez'=>0);
						$Item_Store->data($Item_Store_Data)->add();
						
						$Item_Count = M('sysitem_item_count');
						$Item_Count_Data = array('item_id'=>$Item_Id,'sold_quantity'=>0,'rate_count'=>0,'rate_good_count'=>0,'rate_neutral_count'=>0,'rate_bad_count'=>0,'view_count'=>0,'buy_count'=>0);
						$Item_Count->data($Item_Count_Data)->add();
							
						$Sku_Data = array('item_id'=>$Item_Id,
										'title'=>$good['name'],
										'bn'=>$good['sku'],
										'price'=>$price,
										'cost_price'=>$good['price'],
										'mkt_price'=>$good['jdPrice'],
										'barcode'=>$good['sku'],
										'weight'=>$good['weight'],
										'properties'=>'',
										'spec_info'=>'',
										'spec_desc'=>'',
										'status'=>'normal',
										'outer_id'=>'',
										);
						$Sku = M('sysitem_sku');
						$sku_id = $Sku->data($Sku_Data)->add();
						
						$Item_Store = M('sysitem_sku_store');
						$Item_Store_Data = array('item_id'=>$Item_Id,'sku_id'=>$sku_id,'store'=>100000,'freez'=>0);
						$Item_Store->data($Item_Store_Data)->add();
													
						sleep(2);
						echo $good['sku'].'请求完成<br />';
					}
					echo '第'.$page.'页请求完成！<br />';		
					//跳转请求至下一页
					echo '<script type="text/javascript">window.location.href="'.$header_url.'"</script>';
				}
				else
				{
					exit("没有产品信息了");
				}
			}
		}
	}
	
	public function saveImg($id,$default,$imageList){
		$this->modelItemImg->where('item_id='.$id)->delete();
		$list_image = '';
		foreach($imageList as $keys=>$list){
			$list_image.=$list['path'].",";
		}
		$data=array(
			'item_id'=>$id,
			'outer_default_img'=>$default,
			'outer_imglist'=>substr($list_image, 0, -1)
		);
		$this->modelItemImg->add($data);
	}
	
    public function doRequest($url)
    {
        $result = file_get_contents($url);
		$res = json_decode($result,true);
        return $res;
    }	
	
	public function GrabImage($url,$foo,$n,$type='')
	{
		if($url==""){return false;}
		$path = C('JD_UPLOAD')."jd".date('Ymd')."/".$foo."/";

		if (!is_dir($path)){
			mkdir($path,0777,true);
		}
		
		$ext=strrchr($url,".");	
		
		$filename=$path.$n.$ext;
		$reFilename="jd".date('Ymd')."/".$foo."/".$n.$ext;
		
	
		ob_start();
		readfile($url);
		$img = ob_get_contents();
		ob_end_clean();
		if(empty($img)){
			$img = $this->imgReget($url);
		}
		$size = strlen($img);
		
		$fp2=@fopen($filename, "a");
		
		$u = fwrite($fp2,$img);
		fclose($fp2);
		
		if(!empty($img)){
			if($type=='d'){
				$img_m=$filename."_m".$ext;
				$img_t=$filename."_t".$ext;
				$this->img2thumb($filename, $img_m, 300, 300);
				$this->img2thumb($filename, $img_t, 100, 100);
			}elseif($type=='l'){
				$img_l=$filename."_l".$ext;
				$img_t=$filename."_t".$ext;
				$this->img2thumb($filename, $img_l, 500, 500);
				$this->img2thumb($filename, $img_t, 100, 100);
			}
		}
		
		return $reFilename;
	}
	
	public function imgReget($img_url,$i=0)
	{
		$i++;
		ob_start();
		readfile($img_url);
		$img = ob_get_contents();
		ob_end_clean();
		if($i>5){
			echo $img_url."图片读取不到数据，请检查"."<br>";
			file_put_contents('logs/synsJd.txt',$img_url."图片读取不到数据\n", FILE_APPEND);
		}
		if(empty($img))
		{
			sleep(1);
			$img = $this->imgReget($img_url,$i);
		}
		return $img;
	}
	
	/**
	 * 生成缩略图
	 * @author yangzhiguo0903@163.com
	 * @param string     源图绝对完整地址{带文件名及后缀名}
	 * @param string     目标图绝对完整地址{带文件名及后缀名}
	 * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
	 * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
	 * @param int        是否裁切{宽,高必须非0}
	 * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
	 * @return boolean
	 */
	public function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0)
	{
		if(!is_file($src_img))
		{
			return false;
		}
		$ot = $this->fileext($dst_img);
		$otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
		$srcinfo = getimagesize($src_img);

		$src_w = $srcinfo[0];
		$src_h = $srcinfo[1];
		$type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
		
		$createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
	 
		$dst_h = $height;
		$dst_w = $width;
		$x = $y = 0;
	 
		/**
		 * 缩略图不超过源图尺寸（前提是宽或高只有一个）
		 */
		if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
		{
			$proportion = 1;
		}
		if($width> $src_w)
		{
			$dst_w = $width = $src_w;
		}
		if($height> $src_h)
		{
			$dst_h = $height = $src_h;
		}
	 
		if(!$width && !$height && !$proportion)
		{
			return false;
		}
		if(!$proportion)
		{
			if($cut == 0)
			{
				if($dst_w && $dst_h)
				{
					if($dst_w/$src_w> $dst_h/$src_h)
					{
						$dst_w = $src_w * ($dst_h / $src_h);
						$x = 0 - ($dst_w - $width) / 2;
					}
					else
					{
						$dst_h = $src_h * ($dst_w / $src_w);
						$y = 0 - ($dst_h - $height) / 2;
					}
				}
				else if($dst_w xor $dst_h)
				{
					if($dst_w && !$dst_h)  //有宽无高
					{
						$propor = $dst_w / $src_w;
						$height = $dst_h  = $src_h * $propor;
					}
					else if(!$dst_w && $dst_h)  //有高无宽
					{
						$propor = $dst_h / $src_h;
						$width  = $dst_w = $src_w * $propor;
					}
				}
			}
			else
			{
				if(!$dst_h)  //裁剪时无高
				{
					$height = $dst_h = $dst_w;
				}
				if(!$dst_w)  //裁剪时无宽
				{
					$width = $dst_w = $dst_h;
				}
				$propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
				$dst_w = (int)round($src_w * $propor);
				$dst_h = (int)round($src_h * $propor);
				$x = ($width - $dst_w) / 2;
				$y = ($height - $dst_h) / 2;
			}
		}
		else
		{
			$proportion = min($proportion, 1);
			$height = $dst_h = $src_h * $proportion;
			$width  = $dst_w = $src_w * $proportion;
		}
	 
		$src = $createfun($src_img);
		$dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
		$white = imagecolorallocate($dst, 255, 255, 255);
		imagefill($dst, 0, 0, $white);
	 
		if(function_exists('imagecopyresampled'))
		{
			imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		}
		else
		{
			imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		}
		$otfunc($dst, $dst_img);
		imagedestroy($dst);
		imagedestroy($src);
		return true;
	}

	
	public function fileext($file)
	{
		return pathinfo($file, PATHINFO_EXTENSION);
	}
}
?>