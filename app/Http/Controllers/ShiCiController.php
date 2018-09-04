<?php

namespace App\Http\Controllers;

use \Curl\Curl;
use App\Models\Poem;
use App\Models\Author;
use App\Models\Rhesis;
use Illuminate\Support\Facades\DB;

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
        while ($page <= 200) {
            $curl->get($this->url.'/mingju/default.aspx', [
                'p' => $page
            ]);

            $pattern = '/<div class=\"cont\".*?>.*?<\/div>/ism';// 匹配到所有的名句
            if (preg_match_all($pattern, $curl->response, $matches)) {
                foreach ($matches[0] as $match) {
                    $pattern = '/<a style=\" float:left;\".*?>.*?<\/a>/ism';// 分割名句、作者
                    
                    if (preg_match_all($pattern, $match, $matches_item)) {
                        $matches_item = $matches_item[0];
    
                        $sentence = strip_tags($matches_item[0]);// 名句
                        
                        // 插入作者
                        $author_poem = explode("《", strip_tags($matches_item[1]));
                        $author_id = Author::insertGetId(['name' => $author_poem[0]]);
                        
                        // 插入诗词
                        $poem_name = explode('》', $author_poem[1])[0];// 诗名
                        preg_match('/href="(.*?)">/ism', $matches_item[1], $poem_url);
                        $poem_url = $poem_url[1];// 诗地址   
                        $curl->get("$this->url/$poem_url");
                        preg_match_all('/<div class=\"contson\" id="contson.*?>(.*?)<\/div>?/ism', $curl->response, $full);
                        $full = trim(strip_tags($full[1][0]));
                        $poem_id = Poem::insertGetId([
                            'author_id' => $author_id,
                            'name' => $poem_name,
                            'content' => $full,
                        ]);

                        // 插入名句
                        Rhesis::insert([
                            'poem_id' => $poem_id,
                            'sentence' => $sentence,
                        ]);
                    }
                }
            } else {
                echo '0';
            }

            $page++;
        }
        // dd(strip_tags($response));
        // 正则匹配到名句，把名句拆分：句子、作者、名称
        // 匹配到名称的 url 
        // 获取完整的诗词
    }
}