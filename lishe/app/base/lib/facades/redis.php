<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_facades_redis extends base_facades_facade
{
	/**
	 * Return the View instance
	 * 
	 * @var \Symfony\Component\HttpFoundation\Request;
	 */
    
    private static $__redis;
    
    protected static function getFacadeAccessor()
    {
        if (!static::$__redis)
        {
            static::$__redis = new base_redis_database();
        }
        return static::$__redis;
    }
}
