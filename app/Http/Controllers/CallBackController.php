<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\GuzzleHTTPClient;
use Goutte\Client;

class CallBackController extends Controller
{
    protected $config;
    protected $bot;
    protected $req;
    protected $row;
    protected $newsLink;
    protected $img;
    protected $youtube;

    public function __construct(Request $request)
    {
        $this->config = [
            'channelId' => getenv('CHANNEL_ID'),
            'channelSecret' => getenv('CHANNEL_SECRET'),
            'channelMid' => getenv('MID'),
            'defaults'  => [
                'proxy' => 'http://fixie:9pjgKxmbP33TyYY@velodrome.usefixie.com:80'
            ]
        ];
        $this->bot = new LINEBot($this->config, new GuzzleHTTPClient($this->config));
        $this->req = $request;
        $this->row = [
            'rank' => [],
            'name' => [],
            'win'  => [],
            'lose' => [],
            'draw' => [],
            'gap'  => []
        ];
        $this->newsLink = [
            'title' => [],
            'link' => []
        ];
        $this->img = [];
        $this->youtube = [
            'title' => [],
            'link' => []
        ];
    }

    public function callback()
    {
        $bot = $this->bot;
        $receives = $bot->createReceivesFromJSON($this->req->getContent());
        error_log( print_r( $receives[0]->getFromMid(), true ) );

        foreach ($receives as $receive) {
            if ($receive->isMessage()) {

                if ($receive->isText()) {
                    /** @var Text $receive */
                    if ($receive->getText() === 'me') {
                    //     $ret = $bot->getUserProfile($receive->getFromMid());
                    //     $contact = $ret['contacts'][0];
                    //     $multipleMsgs = (new MultipleMessages())
                    //         ->addText(sprintf(
                    //             'Hello! %s san! Your status message is %s',
                    //             $contact['displayName'],
                    //             $contact['statusMessage']
                    //         ))
                    //         ->addImage($contact['pictureUrl'], $contact['pictureUrl'])
                    //         ->addSticker(mt_rand(0, 10), 1, 100);
                    //     $bot->sendMultipleMessages($receive->getFromMid(), $multipleMsgs);
                    } else {
                        $text = $this->checkText($receive);
                        if ($text !== NULL && $text !== 'img') {
                            $bot->sendText($receive->getFromMid(), $text);
                        } elseif ($text === "img") {

                        } else {
                            $text = "コマンドが変更になりました!ごめんネ!\n【順位表コマンド】\n！順位\n【ニュースコマンド】\n！[チーム名]\nex.) ！横浜\nチーム名は\n巨人 ヤクルト 横浜 中日 阪神 広島 西武 日ハム ロッテ オリックス ソフトバンク 楽天 です。\n【動画】\n！動画";
                            $bot->sendText($receive->getFromMid(), $text);
                        }
                    }
                } elseif ($receive->isImage() || $receive->isVideo()) {
                    // $content = $bot->getMessageContent($receive->getContentId());
                    // $meta = stream_get_meta_data($content->getFileHandle());
                    // $contentSize = filesize($meta['uri']);
                    // $type = $receive->isImage() ? 'image' : 'video';
                    // $previewContent = $bot->getMessageContentPreview($receive->getContentId());
                    // $previewMeta = stream_get_meta_data($previewContent->getFileHandle());
                    // $previewContentSize = filesize($previewMeta['uri']);
                    // $bot->sendText(
                    //     $receive->getFromMid(),
                    //     "Thank you for sending a $type.\nOriginal file size: " .
                    //     "$contentSize\nPreview file size: $previewContentSize"
                    // );

                } elseif ($receive->isAudio()) {
                    // $bot->sendText($receive->getFromMid(), "Thank you for sending a audio.");
                } elseif ($receive->isLocation()) {
                    /** @var Location $receive */
                    // $bot->sendLocation(
                    //     $receive->getFromMid(),
                    //     sprintf("%s\n%s", $receive->getText(), $receive->getAddress()),
                    //     $receive->getLatitude(),
                    //     $receive->getLongitude()
                    // );
                } elseif ($receive->isSticker()) {
                    /** @var Sticker $receive */
                    // $bot->sendSticker(
                    //     $receive->getFromMid(),
                    //     $receive->getStkId(),
                    //     $receive->getStkPkgId(),
                    //     $receive->getStkVer()
                    // );
                } elseif ($receive->isContact()) {
                    /** @var Contact $receive */
                    // $bot->sendText(
                    //     $receive->getFromMid(),
                    //     sprintf("Thank you for sending %s information.", $receive->getDisplayName())
                    // );
                } else {
                    throw new \Exception("Received invalid message type");
                }
            } elseif ($receive->isOperation()) {

                if ($receive->isAddContact()) {
                    $bot->sendText($receive->getFromMid(), "野球の情報を教えるよ♡");
                } elseif ($receive->isBlockContact()) {

                } else {
                    throw new \Exception("Received invalid operation type");
                }
            } else {
                throw new \Exception("Received invalid receive type");
            }
        }
        return "ok";
    }

    private function checkText($receive)
    {
        $text = '';
        switch ($receive->getText()) {
            case '！巨人':
                $text = $this->getNewsLink('giants');
                break;
            case '！ヤクルト':
                $text = $this->getNewsLink('yakult_swallows');
                break;
            case '！横浜':
                $text = $this->getNewsLink('yokohama_baystars');
                break;
            case '！中日':
                $text = $this->getNewsLink('chunichi_dragons');
                break;
            case '！阪神':
                $text = $this->getNewsLink('hanshin_tigers');
                break;
            case '！広島':
                $text = $this->getNewsLink('hiroshima_carp');
                break;
            case '！西武':
                $text = $this->getNewsLink('seibu_lions');
                break;
            case '！日ハム':
                $text = $this->getNewsLink('fighters');
                break;
            case '！ロッテ':
                $text = $this->getNewsLink('chiba_lotte_marines');
                break;
            case '！オリックス':
                $text = $this->getNewsLink('orix_buffaloes');
                break;
            case '！ソフトバンク':
                $text = $this->getNewsLink('hawks');
                break;
            case '！楽天':
                $text = $this->getNewsLink('rakuten_eagles');
                break;
            case '我妻の画像おくれ':
                $text = $this->getImage('aga');
                $this->bot->sendImage($receive->getFromMid(), $text, $text);
                $text = 'img';
                break;
            case 'はのちさんの画像おくれ':
                $text = $this->getImage('hanochi');
                $this->bot->sendImage($receive->getFromMid(), $text, $text);
                $text = 'img';
                break;
            case 'ザキヤマの画像おくれ':
                $text = $this->getImage('zakiyama');
                $this->bot->sendImage($receive->getFromMid(), $text, $text);
                $text = 'img';
                break;
            case '隼人さんの画像おくれ':
                $img = [
                    '0' => 'http://baseball-bot-10do.herokuapp.com/img/hayato/hayato1.jpg',
                    '1' => 'http://baseball-bot-10do.herokuapp.com/img/hayato/hayato2.jpg'
                ];
                $text = $img[rand(0,1)];
                $this->bot->sendImage($receive->getFromMid(), $text, $text);
                $text = 'img';
                break;
            case '！順位':
                $text = $this->getrankText();
                break;
            case '！動画':
                $text = $this->getMovie();
                break;
            default:
                $text = NULL;
                break;
        }

        return $text;
    }

    public function getMovie() {
    // public function callback() {
        $client = new Client();
        $crawler = $client->request('GET', 'https://www.youtube.com/channel/UCUYj6hqtZSKl5Dsty6gDVUQ/feed');
        $crawler->filter('.yt-lockup-title')->each(function ($node){
            array_push($this->youtube['title'], '▼'.$node->text());
            array_push($this->youtube['link'], 'https://www.youtube.com'.$node->children()->attr('href'));
        });
        $text ="";
        $arr = $this->youtube;
        for ($i=0; $i < 5; $i++) {
            $text .= $arr['title'][$i]."\n".$arr['link'][$i]."\n";
        }
        $text .= '今日もたくさん動画があがっているなあ!';
        return $text;
    }

    private function getImage($select)
    {
        $client = new Client();
        if ($select === 'aga') {
            $crawler = $client->request('GET', 'https://www.google.co.jp/search?q=%E9%96%93%E5%AF%9B%E5%B9%B3&espv=2&biw=1128&bih=805&site=webhp&source=lnms&tbm=isch&sa=X&ved=0ahUKEwiDrePj6YXNAhXCoJQKHfKABtQQ_AUIBygC#imgrc=_');
        } elseif ($select ==='hanochi') {
            $crawler = $client->request('GET', 'https://www.google.co.jp/search?q=%E3%82%B4%E3%83%AB%E3%82%B413&espv=2&biw=1128&bih=805&source=lnms&tbm=isch&sa=X&ved=0ahUKEwj6vcHg7oXNAhXDYaYKHWoyAC0Q_AUIBigB');
        } elseif ('zakiyama') {
            $crawler = $client->request('GET', 'https://www.google.co.jp/search?q=%E3%82%B6%E3%82%AD%E3%83%A4%E3%83%9E&espv=2&biw=1073&bih=720&source=lnms&tbm=isch&sa=X&ved=0ahUKEwiuzMylnYbNAhXEkJQKHXP0Dm4Q_AUIBigB');
        }
        $crawler->filter('img')->each(function ($node){
            array_push($this->img, $node->attr("src"));
        });
        return $this->img[rand(0,19)];
    }

    private function getNewsLink($query) {
        $client = new Client();
        $crawler = $client->request('GET', 'http://news.yahoo.co.jp/related_newslist/'.$query.'/');
        $crawler->filter('ul.listArea li')->each(function ($node){
            array_push($this->newsLink['title'], $node->filter('.ttl')->text());
            array_push($this->newsLink['link'], $node->children()->attr('href'));
        });

        $text = $this->newsLink['title'][0]."\n".$this->newsLink['link'][0]."\n".$this->newsLink['title'][1]."\n".$this->newsLink['link'][1]."\n".$this->newsLink['title'][2]."\n".$this->newsLink['link'][2];
        return $text;
    }

    private function getrankText()
    {
        $client = new Client();
        $crawler = $client->request('GET', 'http://baseball.yahoo.co.jp/npb/standings/');
        $crawler->filter('table tr')->each(function ($node){
            $tds = $node->children();
            array_push($this->row['rank'], $tds->eq(0)->text());
            array_push($this->row['name'], $tds->eq(1)->text());
            array_push($this->row['win'], $tds->eq(3)->text());
            array_push($this->row['lose'], $tds->eq(4)->text());
            array_push($this->row['draw'], $tds->eq(5)->text());
            array_push($this->row['gap'], $tds->eq(7)->text());
        });
        foreach ($this->row as $key => $value) {
            $this->row[$key][0] = "";
            $this->row[$key][7] = "";
            $this->row[$key][14] = "";
        }

        $rankArray = $this->row;
        $rankText = "【最新の順位表】だお\n順位 名 勝 負 引 差\n【セ・リーグ】\n";
        for ($i=1; $i <= 26 ; $i++) {
            if($i === 7){
                $rankText .= "【パ・リーグ】\n";
            } elseif ($i === 14) {
                $rankText .= "【交流戦】\n";
            } else {
                $rankText .= $rankArray['rank'][$i]." ".$rankArray['name'][$i]." ".$rankArray['win'][$i]." ".$rankArray['lose'][$i]." ".$rankArray['draw'][$i]." ".$rankArray['gap'][$i]."\n";
            }
        }

        return $rankText;
    }
}
