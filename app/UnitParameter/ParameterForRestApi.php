<?php
/**
 * UNITパラメータクラスのファイル
 * 
 * UNITパラメータとしての利用と共にグローバル領域としても活用
 */

namespace App\UnitParameter;


use SocketManager\Library\SocketManagerParameter;

use App\Common\IPsr7Factory;
use App\Common\RequestWrapper;
use App\Common\ResponseWrapper;

/**
 * UNITパラメータクラス
 * 
 * UNITパラメータクラスのSocketManagerParameterをオーバーライドする
 */
class ParameterForRestApi extends SocketManagerParameter
{
    //--------------------------------------------------------------------------
    // プロパティ
    //--------------------------------------------------------------------------

    /**
     * @var IPsr7Factory PSR-7準拠のファクトリ
     */
    public IPsr7Factory $factory;

    /**
     * @var RequestWrapper リクエストインスタンス
     */
    protected RequestWrapper $request;

    /**
     * @var array Access-Control-Allow-Originヘッダのリスト
     */
    protected array $origins;

    /**
     * @var string ETag生成モード（'strong' or 'weak'）
     */
    protected string $etag;


    //--------------------------------------------------------------------------
    // メソッド
    //--------------------------------------------------------------------------

    /**
     * コンストラクタ
     * 
     * @param IPsr7Factory $p_factory PSR-7準拠のファクトリ
     * @param array $p_origins Access-Control-Allow-Originヘッダのリスト
     * @param string $p_etag ETag生成モード（'strong' or 'weak'）
     */
    public function __construct(IPsr7Factory $p_factory, array $p_origins, string $p_etag)
    {
        parent::__construct();

        $this->factory = $p_factory;
        $this->request = new RequestWrapper();
        $this->origins = $p_origins;
        $this->etag = $p_etag;
    }

    /**
     * リクエストインスタンス
     * 
     */
    public function request(): RequestWrapper
    {
        $this->request->setInstance(null);
        $req_ins = $this->getTempBuff(['__req-ins']);
        if($req_ins !== null)
        {
            $this->request->setInstance($req_ins['__req-ins']);
        }

        $this->request->setParam('route_params', []);
        $req_params = $this->getTempBuff(['__req-params']);
        if($req_params !== null)
        {
            $this->request->setParam('route_params', $req_params['__req-params']);
        }

        return $this->request;
    }

    /**
     * リクエストインスタンス
     * 
     * @param bool $p_new_instance インスタンス生成フラグ（true：新規生成 or false：再利用）
     */
    public function response(bool $p_new_instance = false): ResponseWrapper
    {
        $response = null;
        $res_ins = $this->getTempBuff(['__res-ins']);
        if($res_ins !== null && $p_new_instance === false)
        {
            $response = $res_ins['__res-ins'];
        }
        else
        {
            $response = new ResponseWrapper($this->factory, $this, $this->etag);
            if(count($this->origins))
            {
                $response->header('Access-Control-Allow-Origin', implode(', ', $this->origins));
            }
            $this->setTempBuff(['__res-ins' => $response]);
        }

        return $response;
    }
}
