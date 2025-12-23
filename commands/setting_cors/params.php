<?php


return [

    /**
     * "file://"スキームを利用する場合は必ず必要
     * 
     * @var array Access-Control-Allow-Origin ヘッダの許可リスト
     */
    'allow_origins' => "'http://localhost:10000'",

    /**
     * @var array Access-Control-Allow-Headers ヘッダの許可リスト
     */
    'allow_headers' => "'Content-Type'"
];
