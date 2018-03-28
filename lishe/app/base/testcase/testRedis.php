<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use Predis\Response\ServerException;

class testRedis extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //$this->model = app::get('base')->model('members');
    }

    public function testRequest()
    {
        redis::scene('queue')->loadScripts('queueGet');
        
        
        //echo redis::scene('queue')->set('ax',21);
        #redis::scene('queue')->rpush('dd' ,  'bar0' ) ; //3
        #redis::scene('queue')->rpush('dd' ,  'bar2' ) ; //3
        #redis::scene('queue')->rpush('dd' ,  'bar3' ) ; //3


        #$data =  redis::scene('queue')->lrange('dd',0,-1); //3
        #var_dump($data);

        redis::scene('queue')->ltrim('dd',-1,0) ;

        $data =  redis::scene('queue')->lrange('dd',0,-1); //3
        var_dump($data);


        #redis::scene('queue')->lpop('dd') ; //3


        #$job =  redis::scene('queue')->lpop('dd') ; //3
        #echo $job;
        #if( $job )
        #{
        #    redis::scene('queue')->zadd('dd:reserved', time() + 60, $job);
        #    echo '执行队列'.$job;

        #    #redis::scene('queue')->zrem('dd:reserved', $job);
        #    #echo '移除队列'.$job;
        #}

        #$data =  redis::scene('queue')->zrange('dd:reserved',0,-1); //3
        #var_dump($data);

        //echo redis::scene('queue')->lpop('dd') ; //3
        //var_dump((string)redis::scene('queue')->ping()==='PONG');

        #echo redis::scene('queue')->rpush('dd' ,  'bar100' ) ; //3
    }
}
