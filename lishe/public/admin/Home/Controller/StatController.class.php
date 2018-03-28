<?php
namespace Home\Controller;
class StatController extends CommonController {

	public function __construct(){
		parent::__construct();
		$this->url='/admin.php/Stat/';
	}

    public function index(){
    	$model = D('Stat');
    	header("Content-type:text/html;charset=utf-8");
		$date=isset($_GET['date'])?$_GET['date']:1464710400;
        //$date=isset($_GET['date'])?$_GET['date']:1478102400;
		//1484841600 > 1484980405
        
		if($date < 1464710399 && $date == 0){
			//时间不符合条件 直接停止
			exit('时间不符合条件');
		}
		if($date > time()){
			exit('已经执行完毕！');
		}
        $UnixTime = $model->getDaily($date);
		$header=$this->url."index/date/".$UnixTime;
    	echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
    }

    public function SupplierDaily(){
        $model = D('Stat');
        header("Content-type:text/html;charset=utf-8");
        $date=isset($_GET['date'])?$_GET['date']:1464710400;
        //1484841600 > 1484980405
        if($date < 1464710399 && $date == 0)
        {
            //时间不符合条件 直接停止
            exit;
        }
        if($date > time()){
            exit('已经执行完毕！');
        }
        $UnixTime = $model->getSupplierDaily($date);
        $header=$this->url."supplierdaily/date/".$UnixTime;
        echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
    }

    public function companydaily(){
        $model = D('Stat');
        header("Content-type:text/html;charset=utf-8");
        $date=isset($_GET['date'])?$_GET['date']:1464710400;
        //1484841600 > 1484980405
        if($date < 1464710399 && $date == 0)
        {
            //时间不符合条件 直接停止
            exit;
        }
        if($date > time()){
            exit('已经执行完毕！');
        }
        $UnixTime = $model->getCompanyDaily($date);
        $header=$this->url."companydaily/date/".$UnixTime;
        echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
    }

   

    public function goods(){
        $model = D('Stat');
        header("Content-type:text/html;charset=utf-8");
        $date=isset($_GET['date'])?$_GET['date']:0;
        $data = $model->getGoods($date);
        if($date>$data['count'])
        {
            exit('全部输出了');
        }
        $header = $this->url."Goods/date/".$data['do'];
        echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
    }

}