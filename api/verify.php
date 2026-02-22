<?php
$card = $_GET['card'] ?? '';
$device = $_GET['device'] ?? '';
$days = (int)($_GET['days'] ?? 30);
$dbFile = 'data.txt';

if (!$card || !$device) {
    die("参数错误");
}

// 读取数据库
$data = file_exists($dbFile) ? file($dbFile, FILE_IGNORE_NEW_LINES) : [];
$newData = [];
$found = false;
$used = false;
$bindDevice = '';
$expire = '';

foreach ($data as $line) {
    $line = trim($line);
    if (!$line) continue;
    $arr = explode('|', $line);
    $c = $arr[0];
    $stat = $arr[1];
    $d = $arr[2] ?? '';
    $e = $arr[3] ?? '';

    if ($c === $card) {
        $found = true;
        if ($stat === '1') {
            $used = true;
            $bindDevice = $d;
            $expire = $e;
        } else {
            $newStat = '1';
            $newDevice = $device;
            $newExpire = time() + $days * 86400;
            $newData[] = "$card|$newStat|$newDevice|$newExpire";
        }
    } else {
        $newData[] = $line;
    }
}

if (!$found) {
    die("卡密不存在");
}

if ($used) {
    if ($bindDevice === $device) {
        $left = $expire - time();
        $leftDays = max(0, ceil($left / 86400));
        die("已绑定本设备 剩余$leftDays 天");
    } else {
        die("已绑定其他设备");
    }
} else {
    file_put_contents($dbFile, implode("\n", $newData) . "\n");
    die("激活成功 有效期$days 天");
}
?>
