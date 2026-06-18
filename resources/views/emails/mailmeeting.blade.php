<!DOCTYPE html>
<html>

<head>
    <title></title>
</head>

<body>   
    <h4>Info : {{ $info }}</h4>
    <p>Click Here : <a href="{{ $url }}">{{ $url }}</a></p>
    <p>Requester : {{ $name }}</p>
    <p>Company : {{ $cpnyid }}</p>
    <p>Department : {{ $deptname }}</p>
    <p>Room : {{ $room }}</p>
    <p>Start : {{ $startx }}</p>
    <p>End : {{ $endx }}</p>
    <p>Zoom : {{ $zoom }}</p>
    <br>
    <?php
    echo 'Description : ' . nl2br(e($descr ?? ''));
    ?>
    <hr>
    <?php
    echo 'Info Zoom : ' . nl2br(e($info_zoom ?? ''));
    ?>
    <br>
    <hr>
    <p>Click Add Google Calendar  : <a href="http://www.google.com/calendar/render?action=TEMPLATE&text={{ urlencode($info) }}&dates={{ urlencode($start) }}/{{ urlencode($end) }}&details={{ urlencode($descr ?? '') }}&location={{ urlencode($room) }}&trp=false&sprop=&sprop=name:" target="_blank" rel="nofollow">Add to Google Calendar</a></p>
    
       
</body>

</html>