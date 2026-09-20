<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attributeを承認してください。',
    'confirmed' => ':attributeが確認用の入力と一致しません。',
    'email' => ':attributeには、有効なメールアドレスを指定してください。',
    'integer' => ':attributeには整数を指定してください。',
    'max' => [
        'numeric' => ':attributeには、:max以下の数字を指定してください。',
        'string' => ':attributeは、:max文字以下で指定してください。',
    ],
    'min' => [
        'numeric' => ':attributeには、:min以上の数字を指定してください。',
        'string' => ':attributeは、:min文字以上で指定してください。',
    ],
    'numeric' => ':attributeには、数字を指定してください。',
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で指定してください。',
    'unique' => ':attributeにはすでに使用されているものが指定されています。',
    'url' => ':attributeの形式が正しくありません。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
    ],

];
