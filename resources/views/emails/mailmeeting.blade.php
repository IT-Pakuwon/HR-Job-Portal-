<!DOCTYPE html>
<html>

<head>
    <title></title>
</head>

<body>   
    <h4>Info : {{ $info }}</h4>
    <p>Click Here : <a href={{ $url }}>{{ $url }}</a></p>
    <p>Requester : {{ $name }}</p>
    <p>Company : {{ $cpnyid }}</p>
    <p>Department : {{ $deptname }}</p>
    <p>Room : {{ $room }}</p>
    <p>Start : {{ $startx }}</p>
    <p>End : {{ $endx }}</p>
    <p>Zoom : {{ $zoom }}</p>
    <br>   
    {{-- <p>{{ $info_zoom }}</p> --}}
    <?php
    echo 'Description : ' . nl2br($descr);
        // function linkify($text) {
        //     return preg_replace('#\b(http|ftp)(s)?\://([^ \s\t\r\n]+?)([\s\t\r\n])+#smui', '<a href="$1$2://$3" target="_blank">$1$2://$3</a>$4', $text);
        // }  
    ?>
    <hr>
    <?php                                      
        echo 'Info Zoom : ' . nl2br($info_zoom);
    ?>
    <br> 
    <hr>
    <p>Click Add Google Calendar  : <a href="http://www.google.com/calendar/render?action=TEMPLATE&text={{ $info }}&dates={{ $start }}/{{ $end }}&details={{ $descr }}&location={{ $room }}&trp=false&sprop=&sprop=name:" target="_blank" rel="nofollow">Add to Google Calendar</a></p>
    
       
</body>

</html>