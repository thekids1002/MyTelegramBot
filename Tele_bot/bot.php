<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
define('API_KEY', '5445122835:AAGprbhbAK0RwNna2pdNCtNBphoqofGnEh4');
define('API_TKB','https://tkb.huukhuongit.com/Helpers/Process.php?id=');
// request dành cho cronjob;
include './botTele/connection.php';
include './botTele/amlich.php';
$action ="";
if(isset($_GET['request'])){
   $request = $_GET['request'];
    if($request === "ok"){
        $action = "timetable";
    }
}

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
    {
        curl_close($ch);
        return json_decode($res);
    }

}
$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$text = $message->text;
$chat_id = $message
    ->chat->id;

function sendMessage($chat_id, $text)
{
    bot("sendMessage", ['chat_id' => $chat_id, 'text' => $text]);
}

function sendPhoto($chat_id, $photo, $caption)
{
    bot("sendPhoto", ['chat_id' => $chat_id, 'photo' => $photo, 'caption' => $caption]);
}

function startsWith($string, $startString)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function setOrUpdateMSSV($text){
    global $chat_id;
    $temp_array = explode("-", $text);
    $studenID = $temp_array[1];
    $checkexitsSQL = "SELECT * FROM `telegrambot` WHERE `chatid` = '$chat_id'";
    $arr[] = executeSingleResult($checkexitsSQL);
    if($arr[0] == null){
        $insertSQL = "INSERT INTO `telegrambot`(`chatid`, `mssv`) VALUES ('$chat_id','$studenID')";
        if(execute($insertSQL)== true){
            $message = "Đã đăng kí mssv với bot thành công \nHằng ngày vào lúc 0h bot sẽ thông báo cho bạn lịch học !";
            echo $message;
            sendMessage($chat_id,$message);
        };
    }
    else{ 
        $updateMSSVSQL =  "UPDATE `telegrambot` SET `mssv`='$studenID' WHERE `chatid` = '$chat_id'" ;
        if(execute($updateMSSVSQL) == true ){
            $message = "Đã cập nhật mã số sinh viên thành công\nHằng ngày vào lúc 0h bot sẽ thông báo cho bạn lịch học !";
            echo $message;
            sendMessage($chat_id,$message);
        };
    }
}

function myChat($chat_id, $text)
{   
    if($text === '/cmd' || $text === '/start'){
        $message = "Command : \n 
        /start\n
        /website\n
        /chatid\n
        /setid-yourID : cài đặt mssv của bạn cho bot đăng kí thời khoá biểu \n
        /tkb-yournumberID : Nhập ID của bạn vd : /tkb-3119410215 \n
        /homnayrasao\n
        /amlich : xem âm lịch hôm nay\n
        /deleteID : xoá đăng kí thông báo thời khoá biểu\n
        ";
        sendMessage($chat_id, $message);
    }
    if($text === '/website'){
        $message = "https://hoangkiet.tk";
        SendMessage($chat_id, $message);
    }
    if ($text === "/chatid")
    {
        SendMessage($chat_id, "Your chat id is : ".$chat_id);
    }
    if ($text === "/test")
    {
        bot("sendMessage", ['chat_id' => $chat_id, "text" => "test bot ok"]);
    }
    if ($text === "/image")
    {
        $image_url = "https://gamek.mediacdn.vn/thumb_w/640/133514250583805952/2021/9/17/photo-1-1631856680040545802895.jpg";
        sendPhoto($chat_id, $image_url, "Captions/Captions.jpg");
    }
    if(startsWith($text, "/setid-")){
        setOrUpdateMSSV($text);
    }
    if (startsWith($text, "/tkb-"))
    {
        $temp_array = explode("-", $text);
        $studenID = $temp_array[1];
        $json = file_get_contents(API_TKB.$studenID);
        $json_decode = json_decode($json, true);
        $message = "";
        $size =  sizeof($json_decode);
        $index = -1 ;
        $lastindex = $size -1 ;
        foreach ($json_decode as $key)
        {   $index++;
            if($index == $size - 1){
                break;
            }
            $message .= " + Thứ ". $key['day'].", Môn : ".  $key['name'] . " , Tiết : " 
                              . $key['start'] . " -> Tiết : " 
                              . ($key['start'] + $key['total'] - 1) 
                              . ",  Phòng " .$key['room']. "\n";
        }
        $message_temp = "Thời khoá biểu của " .$json_decode[$lastindex]['name'] ." \n- ". $json_decode[$lastindex]['day'] . "\n- " .$json_decode[$lastindex]['start']."\n";
        sendMessage($chat_id, $message_temp . $message);
    }
    if($text === "/homnayrasao"){
        // global $action ;
        // $action = "timetable";
        $message = " Đã hết thời gian test";
        sendMessage($chat_id, $message);
    }
    if($text === "/amlich"){
        
        $lunarDate  = date("d/m/Y")." ( Âm lịch :".alhn(). ")";
        sendMessage($chat_id, $lunarDate);

    }
    if($text === "/deleteID"){
        $deleteSQL = "DELETE FROM `telegrambot` WHERE chatid = '$chat_id'";
        
        if( execute($deleteSQL) == true){
           
            sendMessage($chat_id, "Bạn đã huỷ đăng kí thông báo lịch học thành công. Nếu có nhu cầu vui lòng chat /setid-yourID");
        }
       
    }
}
// fake chat_id, text
// $chat_id = 956607803;
// $text = "/tkb-3119410204";
// lệnh thực thi cronjob : curl -s "https://hoangkiet.tk/bot.php" > /dev/null
myChat($chat_id, $text);
//sendMessage(956607803, "cron job");
// for cronJob
function getWeekday($date)
{
    return date('w', strtotime($date)) + 1;
}

function convertDate()
{
    switch (getWeekday(date("Y-m-d")))
    {
        case '1':
            return "Chủ Nhật";
        case '2':
            return "Hai";

        case '3':
            return "Ba";

        case '4':
            return "Tư";

        case '5':
            return "Năm";

        case '6':
            return "Sáu";

        case '7':
            return "Bảy";
    }

}

function myCronJob()
{   
    $day = convertDate();
    $getArrayStudentsSQL = "SELECT * FROM telegrambot";
    $arrayStudent = executeResult($getArrayStudentsSQL);
   
    foreach ($arrayStudent as $student)
    {
        $json = file_get_contents(API_TKB.$student['mssv']);
        $json_decode = json_decode($json, true);
        $message = "";
        $flag = true;
        $chat_id = $student['chatid'];
        foreach ($json_decode as $key)
        { if($day === "Chủ Nhật")
                { break;}
            if ($day == $key['day'])
            {   
                if ($flag)
                {
                    if ($day != "Chủ Nhật")
                    {
                        $message .= " Hôm nay là thứ $day có các tiết học : \n ";
                        $flag = false;
                    }
    
                }
                $message .= " + " . $key['name'] . " , Tiết : " 
                                  . $key['start'] . " -> Tiết : " 
                                  . ($key['start'] + $key['total'] - 1) 
                                  . "  Phòng " .$key['room']. "\n";
            }
        }
        $lunarDate  = date("d/m/Y")." ( Âm lịch :".alhn(). ")";
        if ($message != '')
        { 
            sendMessage($chat_id, $message. "\n" .$lunarDate);
        }
        else
        {
            $message_2 = "Hôm nay mày được nghỉ nguyên ngày đấy mày à.\n".$lunarDate;
            sendMessage($chat_id, $message_2);
        }
    }
    
   
}
// thực hiện chức năng cronjob với request = timetable
switch($action){
    case "timetable" : myCronJob(); break;
}
// if($action === "timetable"){
//     myCronJob();
// }
// else{

// }
?>
