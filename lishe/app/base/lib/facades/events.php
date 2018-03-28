<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class base_facades_events extends base_facades_facade
{

	/**
	 * Return the View instance
	 *
	 * @var \Symfony\Component\HttpFoundation\Request;
	 */

    private static $event;

    protected static function getFacadeAccessor() {
        if (!static::$event)
        {
            static::$event = new base_events_dispatcher();
        }
        return static::$event;
    }
}

