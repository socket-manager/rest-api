<?php

return [
    'name'        => 'parallel-runtime-manager',
    'description' => 'パラレルクラス（RuntimeManager用）の生成',
    'template'    => 'template.php.tpl',
    'output'      => 'app/ParallelClass/<%= name %>.php',
];
