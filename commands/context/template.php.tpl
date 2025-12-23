<?php
/**
 * コンテキストクラスのファイル
 * 
 * UNITパラメータとしての利用と共にグローバル領域としても活用
 */

namespace App\ContextClass;

use App\UnitParameter\ParameterForRestApi;


/**
 * コンテキストクラス
 * 
 * ParameterForRestApiクラスをオーバーライドして利用する
 */
class <%= name %> extends ParameterForRestApi
{
}
