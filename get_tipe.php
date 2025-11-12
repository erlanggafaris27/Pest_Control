<?php
include "koneksi.php";
$id = (int)$_GET['id'];
$q = mysqli_query($koneksi,"SELECT tipe FROM pestisida WHERE id_pestisida=$id LIMIT 1");
$d = mysqli_fetch_assoc($q);
echo json_encode($d);
