<?php

#~~~~~~~ Var Set ~~~~~~~#
define('Account', '--username--:--password--');
#~~~~~~~ Var Set ~~~~~~~#

#--------- GetPageID ---------#

if(isset($_REQUEST['page_url']))// GetPageID
{
    $link = $_REQUEST['page_url'];
    $GetSource = file_get_contents($link);
    preg_match('/"page_id":"(.*?)"/', $GetSource, $matches);
    $MovieID =  $matches[1];

    $Username = explode(":" , Account)[0];
    $Password = explode(":" , Account)[1];

    f_DigiMoviezLeecher($MovieID,$Username,$Password);
}

#--------- GetPageID ---------#

#--------- DigiMoviez-Leecher ---------#

function f_DigiMoviezLeecher($MovieID , $Username , $Password)
{

#--------------- LOGIN ---------------#

    $REQ_LOGIN = curl_init();

    curl_setopt($REQ_LOGIN, CURLOPT_URL, 'https://digimovie322.sbs/api/app/v1/login');
    curl_setopt($REQ_LOGIN, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($REQ_LOGIN, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($REQ_LOGIN, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($REQ_LOGIN, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($REQ_LOGIN, CURLOPT_HTTPHEADER, array('user-agent: Dart/3.4 (dart:io)', 'content-type: application/json', 'host: digimovie322.sbs'));
    curl_setopt($REQ_LOGIN, CURLOPT_POSTFIELDS, "{\"username\":\"$Username\",\"password\":\"$Password\"}");
    curl_setopt($REQ_LOGIN, CURLOPT_TIMEOUT, 30);
    //echo curl_exec($REQ_LOGIN);
    $RES_LOGIN_JSON = json_decode(curl_exec($REQ_LOGIN) , TRUE);

#--------------- Get Movie ---------------#

    if($RES_LOGIN_JSON['status'] == true and $RES_LOGIN_JSON['user_info']['has_subscription'] == true)
    {
        $AUTH_TOKEN = $RES_LOGIN_JSON['auth_token'];

        $REQ_GET_MOVIE = curl_init();

        curl_setopt($REQ_GET_MOVIE, CURLOPT_URL, 'https://digimovie322.sbs/api/app/v1/get_movie_detail');
        curl_setopt($REQ_GET_MOVIE, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($REQ_GET_MOVIE, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($REQ_GET_MOVIE, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($REQ_GET_MOVIE, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($REQ_GET_MOVIE, CURLOPT_HTTPHEADER, array('user-agent: Dart/3.4 (dart:io)', 'content-type: application/json', 'host: digimovie322.sbs' , 'Authorization: '.$AUTH_TOKEN));
        curl_setopt($REQ_GET_MOVIE, CURLOPT_POSTFIELDS, "{\"movie_id\":\"$MovieID\"}");
        curl_setopt($REQ_GET_MOVIE, CURLOPT_TIMEOUT, 30);
        $AA =  curl_exec($REQ_GET_MOVIE);
        $RES_MOVIE_JSON = json_decode($AA , TRUE);

        $MOVIE_TITLE = $RES_MOVIE_JSON['movie_info']['title_en'];
        $MOVIE_POSTER = $RES_MOVIE_JSON['movie_info']['featured_image_url'];
        //$MOVIE_TYPE_TITLE = $RES_MOVIE_JSON['result']['options']['title_type'];
        $MOVIE_YEAR = $RES_MOVIE_JSON['movie_info']['release_year'];
        $MOVIE_GENRE = $RES_MOVIE_JSON['movie_info']['genre'][0];
        $MOVIE_COUNTRY = $RES_MOVIE_JSON['movie_info']['country'];
        //$MOVIE_TIME = $RES_MOVIE_JSON['result']['info']['time']['default'];
        $MOVIE_IMDB = $RES_MOVIE_JSON['movie_info']['imdb_rate'];
        //$MOVIE_POSTER = $RES_MOVIE_JSON['result']['image']['poster']['jpg']['big'];
        $MOVIE_DIRECTOR = $RES_MOVIE_JSON['writer'][0];

        if(strstr($AA, "movie_download_urls"))
        {
            #--------------- Get_Download_link(Movie) ---------------#

            $MOVIE_TYPE = "movie";
            $dl_Movie_Count =  count($RES_MOVIE_JSON['movie_download_urls']);

            for($i=0; $i <= $dl_Movie_Count - 1; $i++)//Get Download List Link
            {
                $dl_Movie_Label[$i] =  $RES_MOVIE_JSON['movie_download_urls'][$i]['label'];
                $dl_Movie_Quality[$i] =  $RES_MOVIE_JSON['movie_download_urls'][$i]['quality'];
                $dl_Movie_Size[$i] = $RES_MOVIE_JSON['movie_download_urls'][$i]['size'];
                $dl_Movie_Encoder[$i] = $RES_MOVIE_JSON['movie_download_urls'][$i]['encode'];
                $dl_Movie_Link[$i] =  $RES_MOVIE_JSON['movie_download_urls'][$i]['file'];
            }

            #~~~~ Movie Json ~~~~#

            echo(json_encode(array(

                'code' => http_response_code(),
                'message' => 'success' ,
                'developer' => 'AGC007',

                'data' =>   array(
                    'MovieName' => $MOVIE_TITLE ,
                    'isSeries' => $MOVIE_TYPE ,
                    'MovieYear' => $MOVIE_YEAR ,
                    'MovieGenre' => $MOVIE_GENRE ,
                    'MovieCountry' => $MOVIE_COUNTRY ,
                    'MoviePoster' => $MOVIE_POSTER ,
                    'MovieIMDB' => $MOVIE_IMDB ,
                    'MovieDirector' => $MOVIE_DIRECTOR ,

                    'dl' => array(
                        'DL_Movie_Label' => $dl_Movie_Label,
                        'DL_Movie_Quality' => $dl_Movie_Quality ,
                        'DL_Movie_Size' => $dl_Movie_Size ,
                        'DL_Movie_Encoder' => $dl_Movie_Encoder ,
                        'DL_Movie_Link' => $dl_Movie_Link ,
                        'Developer' => "AGC007"
                    )))));

            #~~~~ Movie Json ~~~~#
        }
        else if (strstr($AA, "serie_download_urls"))
        {
            #--------------- Get_Download_link(Series) ---------------#

             $SERIES_SEASONS = $RES_MOVIE_JSON['movie_info']['seasons_count'];

            #~~~~~ HTML SOURCE ~~~~~#
            ?>

            <html style="text-align: center;background-color: black; color:white;background-image: url('https://agc007.top/AGC007/Robot/KingMovieLeecher/KingMovieService/backiee-252055.jpg');" >
            <title>DigiMoviez [SD] By AGC007</title>

            <img style="height: 300px;width: 300px; border-radius:30px; margin-bottom:8px;" src=<?php echo($MOVIE_POSTER)  ?>>
            </br>
            <a style="background-color:darkslategrey;">- SerialName : <?php echo($MOVIE_TITLE."(".$MOVIE_YEAR.")") ?> -</a>
            </br>
            <a style="background-color:darkslategrey;">- SerialDirector : <?php echo($MOVIE_DIRECTOR) ?> -</a>
            </br>
            <a style="background-color:darkslategrey;">- SerialIMDB : <?php echo($MOVIE_IMDB) ?> -</a>
            </br>
            <a style="background-color:darkslategrey;">- SerialSeasons : <?php echo($SERIES_SEASONS) ?> -</a>
            </br>
            </html>

            <?php

            #~~~~~ HTML SOURCE ~~~~~#

            $dl_SERIES_Count =  count($RES_MOVIE_JSON['serie_download_urls']);

            for($A=0; $A <= $dl_SERIES_Count - 1; $A++)//Go To List & Get DATA
            {


                    echo "</br>";

                    echo $dl_Series_Season[$A] = "Season  " . $RES_MOVIE_JSON['serie_download_urls'][$A]['season_name'] ." - ";
                    echo $dl_Series_Episode[$A] = "Last Episode : " . $RES_MOVIE_JSON['serie_download_urls'][$A]['parts_count'];

                    echo "</br>";
                    echo $dl_Series_Quality[$A] = "Quality : " . $RES_MOVIE_JSON['serie_download_urls'][$A]['quality'];
                    echo "</br>";
                    echo $dl_Series_Size[$A] = "Size : " . $RES_MOVIE_JSON['serie_download_urls'][$A]['size'];
                    echo "</br>";
                    echo $dl_Series_Encoder[$A] = "Encoder : " . $RES_MOVIE_JSON['serie_download_urls'][$A]['encoder'];
                    echo "</br>";

                    $dl_SERIES_Episode_Count =  $RES_MOVIE_JSON['serie_download_urls'][$A]['parts_count'];

                    for($B=0; $B <= $dl_SERIES_Episode_Count - 1;$B++)//Go To Download Episode List & Res
                    {

                        echo $dl_Series_Episode_Part[$B] = $RES_MOVIE_JSON['serie_download_urls'][$A]['links'][$B]['part_id']." : ";

                        //$dl_Series_Episode_Source[$B] = $RES_SERIES_DOWN_LINK_JSON['result']['download']["s".$A+1][$AA]['link'][$B]['source'];

                        //if($dl_Series_Episode_Source[$B] == null)
                        //{
                        $dl_Series_Episode_Source[$B] = "Download Link - ".$RES_MOVIE_JSON['serie_download_urls'][$A]['links'][$B]['parent_id']." - ". $B+1 ." - ".$RES_MOVIE_JSON['serie_download_urls'][$A]['quality'];
                        //}

                        $dl_Series_Episode_Link[$B] = $RES_MOVIE_JSON['serie_download_urls'][$A]['links'][$B]['movie'];

                        ?>
                        <a style="color:bisque;" href="<?php echo $dl_Series_Episode_Link[$B]; ?>"><?php echo $dl_Series_Episode_Source[$B]; ?> </a>
                        </br>

                        <?php
                }
            }
            echo("</br> ~ Developer : AGC007 ~");
        }
    } else {
        echo "Login Error Or No Sub";
    }

}

#--------- DigiMoviez-Leecher ---------#

#~AGC007

?>
