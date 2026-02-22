<?php
$dbFile = 'data.txt';

// 生成卡密
if (isset($_GET['generate'])) {
    $newCard = strtoupper(substr(md5(uniqid()), 4, 16));
    file_put_contents($dbFile, $newCard . "|0|\n", FILE_APPEND);
    header("Location: admin.php");
    exit;
}

// 清理过期卡密
if (isset($_GET['cleanup'])) {
    $data = file_exists($dbFile) ? file($dbFile, FILE_IGNORE_NEW_LINES) : [];
    $newData = [];
    $now = time();
    foreach ($data as $line) {
        $line = trim($line);
        if (!$line) continue;
        $arr = explode('|', $line);
        $stat = $arr[1];
        $expire = $arr[3] ?? '';
        if ($stat === '1' && $expire && $now > $expire) {
            $newData[] = $arr[0] . "|2|" . $arr[2] . "|" . $arr[3];
        } else {
            $newData[] = $line;
        }
    }
    file_put_contents($dbFile, implode("\n", $newData) . "\n");
    header("Location: admin.php");
    exit;
}

// 显示卡密列表
echo "<h3>卡密管理后台</h3>";
echo "<a href='?generate=1'>生成一个卡密</a> | <a href='?cleanup=1'>清理过期卡密</a><br><br>";
echo "<pre>卡密 | 状态 | 设备ID | 过期时间</pre>";
echo "<hr>";

if (file_exists($dbFile)) {
    $data = file($dbFile, FILE_IGNORE_NEW_LINES);
    foreach ($data as $line) {
        $line = trim($line);
        if (!$line) continue;
        $arr = explode('|', $line);
        $c = $arr[0];
        $stat = $arr[1] === '0' ? '未使用' : ($arr[1] === '1' ? '已使用' : '已过期');
        $d = $arr[2] ?? '-';
        $expire = isset($arr[3]) && $arr[3] ? date('Y-m-d H:i', $arr[3]) : '-';
        echo "<pre>$c | $stat | $d | $expire</pre>";
    }
}
?>
