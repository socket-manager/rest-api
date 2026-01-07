<?php
/**
 * メイン処理クラスのファイル
 * 
 * SocketManagerの実行
 */

namespace App\MainClass;


/**
 * メイン処理クラス
 * 
 * メイン処理の初期化と実行
 */
class <%= name %> extends MainForRestApi
{
    /**
     * @var string $identifer サーバー識別子
     */
    protected string $identifer = 'app:<%= name %> {port?} {keep_alive?}';

    /**
     * @var string $description コマンド説明
     */
    protected string $description = '<%= description %>';

    /**
     * @var array $setting_files 設定ファイル指定
     */
    protected array $setting_files = [
        'cors'      => <%= setting_cors %>,
        'parameter' => <%= setting_parameter %>,
        'parser'    => <%= setting_parser %>,
        'routing'   => <%= setting_routing %>
    ];

    /**
     * @var array $classes 設定クラス群
     */
    protected array $classes = [
        'context'  => null,
        'event'    => null,
        'parallel' => null
    ];

    /**
     * ログライター
     *
     * @param string $p_level ログレベル
     * @param array $p_param ログ内容（['Json data' => $json] など）
     */
    public function logWriter(string $p_level, array $p_param)
    {
        /**
         * ここにログ出力処理を実装します
         */
    }
}
