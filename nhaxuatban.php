<?php
include("../config/database.php");
include("../includes/header.php");
include("../includes/sidebar.php");

$sql="SELECT * FROM publishers";
$result=mysqli_query($conn,$sql);
?>

<div class="main">

<h2>Quản lý Nhà Xuất Bản</h2>

<a href="add.php" class="btn btn-primary mb-3">
Thêm Nhà Xuất Bản
</a>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>

<th>Tên NXB</th>

<th>Địa chỉ</th>

<th>SĐT</th>

<th>Email</th>

<th>Chức năng</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?=$row['id']?></td>

<td><?=$row['ten_nxb']?></td>

<td><?=$row['dia_chi']?></td>

<td><?=$row['sdt']?></td>

<td><?=$row['email']?></td>

<td>

<a href="edit.php?id=<?=$row['id']?>" class="btn btn-warning btn-sm">Sửa</a>

<a href="delete.php?id=<?=$row['id']?>" class="btn btn-danger btn-sm">Xóa</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php include("../includes/footer.php"); ?>
<div class="row mb-4">

<div class="col-md-3">

<?php
include("../config/database.php");

$sqlCount = mysqli_query($conn, "SELECT COUNT(*) AS tong FROM publishers");
$count = mysqli_fetch_assoc($sqlCount);
?>

</div>

</div>

</div>
<a href="add.php"

class="btn btn-primary mb-3">

<i class="fa fa-plus"></i>

Thêm Nhà Xuất Bản

</a>
<a class="btn btn-danger btn-sm"

onclick="return confirm('Bạn muốn xóa?')"

href="delete.php?id=<?=$row['id']?>">

<i class="fa fa-trash"></i>

Xóa

</a>
</div>

<?php include("../includes/footer.php"); ?>
