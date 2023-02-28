<?php
session_start();
if (isset($_POST["search"])) {
    $_SESSION["search"] = $_POST["search"];
    header("Location: read_inventory.php?id=".$_GET['id']);
    exit;
}
require_once "../../conexion/pdo.php";
$url = "/adventureworksventas";
$page = (isset($_GET["page"]) ? $_GET["page"] : 1) + 0;
$previous_page = $page - 1;
$next_page = $page + 1;
$step = 10;
$start = ($page - 1) * $step;
//Falta implementar el buscador
if (isset($_SESSION["search"])) {
    $search = "'%" . $_SESSION["search"] . "%'";
    unset($_SESSION["search"]);
    $sql = "SELECT od.id,od.quantity,p.autogenerated_code,p.description FROM product_inventory od 
    inner join product p on od.product_id=p.id where od.inventory_header_id=:id 
    and (od.id LIKE $search or p.autogenerated_code LIKE $search or p.description LIKE $search)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $_GET['id']));
    $product_inventorys = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    //$sql = "SELECT * FROM inventory_header LIMIT $start,$step";
    $stmt = $pdo->query("SELECT pi.*, p.Name, l.Name AS 'LocationName'
    FROM production.productinventory pi
    INNER JOIN production.product p ON pi.ProductID = p.ProductID
    INNER JOIN production.location l ON pi.LocationID = l.LocationID
    ORDER BY p.ProductID
    OFFSET $start ROWS FETCH NEXT $step ROWS ONLY");
    $rows = $stmt->fetchAll();
}

$stmt = $pdo->query("SELECT count(*) as count
FROM production.productinventory pi
INNER JOIN production.product p ON pi.ProductID = p.ProductID
INNER JOIN production.location l ON pi.LocationID = l.LocationID");
$count_string = $stmt->fetch(PDO::FETCH_ASSOC);
$count = $count_string["count"] + 0;
$number_pages = intval(ceil($count / $step));



function printMessage(&$message, $type)
{
    if (isset($message)) {
        if ($type == "successful") {
            echo "<h4 class='text-center text-success'>";
            echo $message;
            echo "</h4>";
        } elseif ($type == "error") {
            echo "<h4 class='text-center text-danger'>";
            echo $message;
            echo "</h4>";
        }
    }
}

require "../../layouts/header.php"
?>
<div class="container-fluid">
    <div class="container-fluid mb-1 mt-1">
        <div class="row">
            <div class="col-12 col-md-4 border border-warning border-1 rounded-3 py-0">
                <h3 class="text-center">Inventario</h3>
            </div>
            <div class="col-12 col-md-3">
                <?php
                printMessage($_SESSION["delete_successful"], "successful");
                printMessage($_SESSION["delete_error"], "error");
                printMessage($_SESSION["edit_successful"], "successful");
                printMessage($_SESSION["edit_error"], "error");
                printMessage($_SESSION["create_successful"], "successful");
                printMessage($_SESSION["create_error"], "error");
                unset($_SESSION["delete_successful"]);
                unset($_SESSION["delete_error"]);
                unset($_SESSION["edit_successful"]);
                unset($_SESSION["edit_error"]);
                unset($_SESSION["create_successful"]);
                unset($_SESSION["create_error"]);
                ?>
            </div>
            <div class="col-12 col-md-5">
                <form method="post" class="d-flex" role="search">
                    <input class="form-control" name="search" placeholder="Buscar" disabled>
                    <button class="btn btn-outline-success disabled" type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center">
        <div class="table-responsive-xxl">
            <table class="table table-bordered" style="width: 1400px;">

                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Shelf</th>
                        <th>Location</th>
                        <th>Quantity</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row) { ?>
                    <tr>
                        <td><?php echo $row['Name']; ?></td>
                        <td><?php echo $row['Shelf']; ?></td>
                        <td><?php echo $row['LocationName']; ?></td>
                        <td><?php echo $row['Quantity']; ?></td>
                        <td>
                        <div class='d-flex align-items-center justify-content-center'>
                        <div class='btn-group'>
                            <a class='btn btn-secondary py-0' href="update_inventory.php?id=<?php echo $row['ProductID']; ?>&location_id=<?php echo $row['LocationID']; ?>&shelf_id=<?php echo $row['Shelf']; ?>"><i class='fa-solid fa-pen-to-square'></i></a>
                            <a class='btn btn-danger py-0 disabled'href="delete_inventory.php?id=<?php echo $row['ProductID']; ?>&location_id=<?php echo $row['LocationID']; ?>&shelf_id=<?php echo $row['Shelf']; ?>"><i class='fa-solid fa-trash-can'></i></a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php
            if ($page == 1) {
                $disabled = "";
                if ($number_pages >= 3) {
                    $x = 3;
                } elseif ($number_pages == 2) {
                    $x = 2;
                } else {
                    $x = 1;
                    $disabled = "disabled";
                }
                echo "<li class='page-item disabled'><a class='page-link'>Anterior</a></li>";
                for ($i = 1; $i <= $x; $i++) {
                    if ($i === $page) {
                        echo "<li class='page-item active'><a class='page-link' href='read_inventory.php?page=$i'>$i</a></li>";
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='read_inventory.php?page=$i'>$i</a></li>";
                    }
                }
                echo "<li class='page-item $disabled'><a class='page-link' href='read_inventory.php?page=$next_page'>Siguiente</a></li>";
            } elseif ($page !== $number_pages) {
                echo "<li class='page-item'><a class='page-link' href='read_inventory.php?page=$previous_page'>Anterior</a></li>";
                for ($i = $page - 1; $i <= $page + 1; $i++) {
                    if ($i === $page) {
                        echo "<li class='page-item active'><a class='page-link' href='read_inventory.php?page=$i'>$i</a></li>";
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='read_inventory.php?page=$i'>$i</a></li>";
                    }
                }
                echo "<li class='page-item'><a class='page-link' href='read_inventory.php?page=$next_page'>Siguiente</a></li>";
            } else {
                echo "<li class='page-item'><a class='page-link' href='read_inventory.php?page=$previous_page'>Anterior</a></li>";
                if ($number_pages<=2) {
                    for ($i = $page - 1; $i <= $page; $i++) {
                        if ($i === $page) {
                            echo "<li class='page-item active'><a class='page-link' href='read_inventory.php?page=$i'>$i</a></li>";
                        } else {
                            echo "<li class='page-item'><a class='page-link' href='read_inventory.php?page=$i'>$i</a></li>";
                        }
                    }
                    echo "<li class='page-item disabled'><a class='page-link'>Siguiente</a></li>";
                } else {
                    for ($i = $page - 2; $i <= $page; $i++) {
                        if ($i === $page) {
                            echo "<li class='page-item active'><a class='page-link' href='read_inventory.php?page=$i'>$i</a></li>";
                        } else {
                            echo "<li class='page-item'><a class='page-link' href='read_inventory.php?page=$i'>$i</a></li>";
                        }
                    }
                    echo "<li class='page-item disabled'><a class='page-link'>Siguiente</a></li>";
                }
            }
            ?>
        </ul>
    </nav>
</div>
<?php require "../../layouts/footer.php" ?>