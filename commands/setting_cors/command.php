<?php

return [
    'name'        => 'setting_cors',
    'description' => 'CORSヘッダ設定ファイルの生成',
    'template'    => 'template.php.tpl',
    'output'      => 'setting/<%= name %>.php',
];
