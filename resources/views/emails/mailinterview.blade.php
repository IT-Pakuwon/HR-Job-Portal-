<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pakuwon Career</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 10px;">
        <h2>Halo {{ $name }},</h2>

        <br>

        <p>Kami mengundang Anda untuk Interview sebagai {{ $jobtitle }} pada: </p>
        <p><strong>Hari/Tanggal:</strong> {{ $startdate }}</p>
        <p><strong>Waktu:</strong> {{ $starttime }} - {{ $endtime }} WIB</p>
        <p><strong>Lokasi:</strong>  {{ $location }} - {{ $address }}</p>
        <p>Setelah menerima Email ini, mohon untuk konfirmasi atas untuk kehadiran.
            <br>Atas perhatiannya kami ucapkan terima kasih.
        </p>
    
        <p style="margin-top: 30px;">Jika Anda memiliki pertanyaan, silakan hubungi kami melalui email ini. </p><br>

        <p>Salam hangat,</p>
        <p><strong>Tim Pakuwon Career</strong></p>
    </div>
</body>

  
</html>
