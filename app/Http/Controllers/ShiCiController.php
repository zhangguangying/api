<?php

namespace App\Http\Controllers;

use \Curl\Curl;

class ShiCiController extends Controller
{
    protected $url = 'https://so.gushiwen.org';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //
    public function getShiCi()
    {
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);

        $page = 1;
        $curl->get($this->url.'/mingju/default.aspx', [
            'p' => $page
        ]);
        
        $poems = [];
        $mingju = [];
        $response = $curl->response;
        $pattern = '/<div class=\"cont\".*?>.*?<\/div>/ism';// 匹配到所有的名句
        if (preg_match_all($pattern, $response, $matches)) {
            foreach ($matches[0] as $match) {
                $pattern = '/<a style=\" float:left;\".*?>.*?<\/a>/ism';// 分割名句、作者

                if (preg_match_all($pattern, $match, $matches_item)) {
                    $matches_item = $matches_item[0];

                    $sentence = strip_tags($matches_item[0]);// 名句
                    
                    $author_poem = explode("《", strip_tags($matches_item[1]));
                    
                    $author = $author_poem[0];// 作者
                    
                    $poem_name = explode('》', $author_poem[1])[0];// 诗名
                    
                    $pattern = '/href="(.*?)">/ism';
                    preg_match($pattern, $matches_item[1], $poem_url);
                    // dd($poem_url);                    
                    // dd($matches_item[1]);
                    $poem_url = $poem_url[1];// 诗地址   
                    
                    $curl->get("https://so.gushiwen.org/$poem_url");
                    $pattern = '/<div class=\"contson\" id="contson.*?>(.*?)<\/div>?/ism';
                    preg_match_all($pattern, $curl->response, $full);
                    $full = trim(strip_tags($full[1][0]));
                    
                    $poems[] = [
                        'author' => $author,
                        'name' => $poem_name,
                    ];
                    $mingju[] = [
                        'sentence' => $sentence,
                    ];
                }
            }
            // dd($matches);
        } else {
            echo '0';
        }
        dd($poems);
        // dd($result);
        // dd(strip_tags($response));
        // 正则匹配到名句，把名句拆分：句子、作者、名称
        // 匹配到名称的 url 
        // 获取完整的诗词
    }
}