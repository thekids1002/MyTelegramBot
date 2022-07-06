<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
define('API_KEY', '');

function bot($method, $data = [])
{
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    if (curl_error($ch))
    {
        var_dump(curl_error($ch));
    }
    else
    {   curl_close($ch);
        return json_decode($res);
    }

}
$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$text = $message->text;
$chat_id = $message->chat->id;

function sendMessage($chat_id, $text)
{
    bot("sendMessage", ['chat_id' => $chat_id, 'text' => $text]);
}

function sendPhoto($chat_id, $photo,$caption)
{
    bot("sendPhoto", ['chat_id' => $chat_id, 'photo' => $photo, 'caption' => $caption]);
}

function startsWith ($string, $startString)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function myChat($chat_id, $text)
{   if($text === "chatid"){
        SendMessage($chat_id,$chat_id);
    }
    if ($text === "/test")
    {
        bot("sendMessage", ['chat_id' => $chat_id, "text" => "test bot ok"]);
    }
    if ($text === "/image")
    {   $image_url = "https://gamek.mediacdn.vn/thumb_w/640/133514250583805952/2021/9/17/photo-1-1631856680040545802895.jpg";
        sendPhoto($chat_id,$image_url,"Captions/Captions.jpg" );
    }
    if (startsWith($text,"/tkb-"))
    {   
        $temp_array = explode("-",$text);
        $studenID = $temp_array[1];
        $json = file_get_contents("https://tkb.huukhuongit.com/Helpers/Process.php?id=$studenID");
        $json_decode = json_decode($json, true);
        $message = "";
        foreach($json_decode as $key){
            $message .= $key['id'] . "\n";
        }
        sendMessage($chat_id, $message);
    }
}
// fake chat_id, text
// $chat_id = 956607803;
// $text = "/tkb-3119410204";
myChat($chat_id,$text);
?>