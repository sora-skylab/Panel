<?php

return [
    'notices' => [
        'created' => '新しい Nest :name を作成しました。',
        'deleted' => '指定された Nest を Panel から削除しました。',
        'updated' => 'Nest の設定オプションを更新しました。',
    ],
    'eggs' => [
        'notices' => [
            'imported' => 'この Egg と関連する変数を正常にインポートしました。',
            'updated_via_import' => 'この Egg は指定されたファイルを使用して更新されました。',
            'deleted' => '指定された Egg を Panel から削除しました。',
            'updated' => 'Egg 設定を更新しました。',
            'script_updated' => 'Egg のインストールスクリプトを更新しました。サーバーのインストール時に実行されます。',
            'egg_created' => '新しい Egg を作成しました。この新しい Egg を反映するには、稼働中のデーモンを再起動してください。',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => '変数 ":variable" を削除しました。再ビルド後はサーバーで利用できなくなります。',
            'variable_updated' => '変数 ":variable" を更新しました。変更を反映するには、この変数を使用しているサーバーを再ビルドしてください。',
            'variable_created' => '新しい変数を作成し、この Egg に割り当てました。',
        ],
    ],
];
