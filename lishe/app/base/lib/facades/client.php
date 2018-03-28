<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

use GuzzleHttp\Client;


class base_facades_client extends base_facades_facade
{
	/**
	 * Return the View instance
	 * 
	 * @var \Symfony\Component\HttpFoundation\Request;
	 */
    
    private static $__client;
    
    protected static function getFacadeAccessor()
    {
        if (!static::$__client)
        {
            $client = new Client();
            if (strpos(strtolower(PHP_OS), 'win') === 0)
            {
                $client->setDefaultOption('verify', false);
            }
            static::$__client = $client;
        }
        return static::$__client;
    }
}
