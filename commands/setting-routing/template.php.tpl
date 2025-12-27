<?php

return [

    /**
     * @var array ルーティング定義リスト
     */
    'routes' =>
    [
        /**
         * 以下の形式でルーティングを設定します
         */
        // [
        //     'method' => <HTTPメソッド（'get'、'post'など）>,
        //     'uri' => <リソースURI>,
        //     'event' => <処理対象のメソッド名（メイン処理クラス内$classesプロパティの'event'キーで指定したクラスのメソッド名）>
        // ],
    ],

    /**
     * @var ?string Expectヘッダ受信時のルーティング先（null時はデフォルトの動作）
     */
    'expect' => null,   // 'routes'キーと同じくメソッド名を設定します

    /**
     * @var ?string ルーティングミスマッチ時のエラールーティング先（null時はデフォルトの動作）
     */
    'mismatch' => null  // 'routes'キーと同じくメソッド名を設定します
];
