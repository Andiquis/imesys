<?php
// ping_replit.php
$url = "https://5daf4610-ebbe-4426-ad50-2fbe650ba4a8-00-3ujld7dwema2v.kirk.replit.dev/usuarios/inicio_imesys.php";
while (true) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    echo "[" . date("H:i:s") . "] Ping enviado a $url\n";
    sleep(60 * 5); // Espera 5 minutos
}
