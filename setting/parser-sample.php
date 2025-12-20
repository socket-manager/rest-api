<?php

use App\BodyParser\JsonParser;
use App\BodyParser\XmlParser;
use App\BodyParser\UrlencodedParser;


return [

    /**
     * JSON形式
     * 
     * @var string クラス名
     */
    'application/json' => JsonParser::class,

    /**
     * XML形式
     * 
     * @var string クラス名
     */
    'application/xml' => XmlParser::class,

    /**
     * URLエンコード形式
     * 
     * @var string クラス名
     */
    'application/x-www-form-urlencoded' => UrlencodedParser::class,
];
