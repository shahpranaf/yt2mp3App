<html>

<head>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
</head>

<body>

    <?php
// A simple web site in Cloud9 that runs through Apache
// Press the 'Run' button on the top to start the web server,
// then click the URL that is emitted to the Output tab of the console
ini_set('display_errors',0); 
error_reporting(E_ALL);
class downloadFile {	

    public function download($url, $path)
    {
        $newfname = $path;
        $file = fopen ($url, 'rb');print_r($file);exit;
        $i= 0 ;
        if ($file) {
            $newf = fopen ($newfname, 'wb');
            if ($newf) {
                echo "Please wait while download is in progress..";
                while(!feof($file)) {
                    print($i."\r\n");
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                    $i++;
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
    }
    
    public function download2($url, $path, $mp3_data) {
        ob_start();
        echo "<pre>";
        echo "Downloading ...";
        
        ob_flush();
        flush();
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 2);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array('downloadFile', 'progress'));
        curl_setopt($ch, CURLOPT_NOPROGRESS, false); // needed to make progress function work
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
    
        echo "Done";
        ob_flush();
        flush();

        
        //print_r($url);print_r($output);
        if ($status == 200) {
            echo "Success";
            echo '<pre>'; print_r($mp3_data);echo '</pre>';
            file_put_contents($path, $output);
        }else {
            echo "Error";
            print_r($status);
            echo '<pre>'; print_r($mp3_data);echo '</pre>';
        }
    }
    
    public function progress($resource,$download_size, $downloaded, $upload_size, $uploaded)
    {
        if($download_size > 0) {
             $perc =  ($downloaded / $download_size)  * 100;
             echo sprintf ("<script type=\"text/javascript\">document.getElementsByTagName('progress')[0].setAttribute('value',%d);</script>",$perc);
        }
             
        ob_flush();
        flush();
        sleep(1); // just to see effect
    }
        
}

$download = new downloadFile;

//$video_url = "https://www.youtube.com/watch?v=sK7riqg2mr4";
$video_id = isset($_REQUEST["vid"]) ? $_REQUEST["vid"] : "";
$down_to_server = isset($_REQUEST["dtos"]) ? $_REQUEST["dtos"] : "";
//$api_url = "https://www.youtubeinmp3.com/fetch/?format=JSON&bitrate=1&filesize=1&video=".$video_url;
//$api_url = "https://savevideos.xyz/api?v=".$video_id;
$api_url = "http://www.yt-mp3.com/fetch?v=".$video_id."&apikey=1234567";

//$response = file_get_contents("https://www.yt2mp3s.me/api-console/mp3/sK7riqg2mr4");
$response = file_get_contents($api_url);


if( !$response ) {
    print_r("Error in response!!!");exit;
}

$mp3_data = json_decode($response, true);

//echo '<pre>';print_r($mp3_data);echo '</pre>';exit;
/* Download other songs */
$dir = new DirectoryIterator(dirname(__FILE__)."/uploads/");
$i=1;
echo "<div class='table-responsive'><table class='.table-striped'>";
echo "<tr><th colspan=2><h1 class='text-center'> Download other songs</h1></th></tr>";
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        $url = "/uploads/".$fileinfo->getFilename();
        echo "<td><a target='_blank' href='".$url."'>".$fileinfo->getFilename()."</a></td>";
        echo "</tr>";
        $i++;
    }
}
echo "</table>";
    
if( $mp3_data['status'] == "ok"  && $mp3_data['progress'] == "100" ){
    $mp3_url = $mp3_data['url'];
    
    $mp3_url = str_replace("//","",preg_replace("/ip=\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i","ip=1.1.1.1",$mp3_url));
    $path = __DIR__."/uploads/".$mp3_data['title'].".mp3";
    
    echo "<br><br><h3><b>Download now:</b> <a target='_blank' href='http://".$mp3_url."'>Here</a></h3><br><br><br>";
    
    echo "<progress value='0' max='100'></progress><span id='percentage'></span>";
    
    if( $down_to_server ) {
        $download->download2($mp3_url, $path, $mp3_data);
    }
}
else {
    echo "Error in api call";
    echo '<pre>';print_r($mp3_data);echo '</pre>';
    
    if( $mp3_data['progress'] > 0 && $mp3_data['progress'] < 100 ){
        echo "Please refresh after 5 mins. As your video is being converted.";
    }
}


?>

</body>

</html>