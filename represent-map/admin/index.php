<?php
$page = "index";
include "header.php";


// hide marker on map
if($task == "hide") {
  $place_id = htmlspecialchars($_GET['place_id']);
  pg_query($conn, "UPDATE places SET approved='FALSE' WHERE id='$place_id'") or die(pg_error());
  header("Location: index.php?view=$view&search=$search&p=$p");
  exit;
}

// show marker on map
if($task == "approve") {
  $place_id = htmlspecialchars($_GET['place_id']);
  pg_query($conn, "UPDATE places SET approved='TRUE' WHERE id='$place_id'") or die(pg_error());
  header("Location: index.php?view=$view&search=$search&p=$p");
  exit;
}

// completely delete marker from map
if($task == "delete") {
  $place_id = htmlspecialchars($_GET['place_id']);
  pg_query($conn, "DELETE FROM places WHERE id='$place_id'") or die(pg_error());
  header("Location: index.php?view=$view&search=$search&p=$p");
  exit;
}

// paginate
$items_per_page = 15;
$page_start = ($p-1) * $items_per_page;
$page_end = $page_start + $items_per_page;

// get results
if($view == "approved") {
  $places = pg_query($conn, "SELECT * FROM places WHERE approved ORDER BY title OFFSET $page_start LIMIT $items_per_page");
  $total = $total_approved;
} else if($view == "rejected") {
  $places = pg_query($conn, "SELECT * FROM places WHERE approved = FALSE ORDER BY title OFFSET $page_start LIMIT $items_per_page");
  $total = $total_rejected;
} else if($view == "pending") {
  $places = pg_query($conn, "SELECT * FROM places WHERE approved = NULL ORDER BY id DESC OFFSET $page_start LIMIT $items_per_page");
  $total = $total_pending;
} else if($view == "") {
  $places = pg_query($conn, "SELECT * FROM places ORDER BY title OFFSET $page_start LIMIT $items_per_page");
  $total = $total_all;
}
if($search != "") {
  $places = pg_query($conn, "SELECT * FROM places WHERE title LIKE '%$search%' ORDER BY title OFFSET $page_start LIMIT $items_per_page");
  $total = pg_num_rows(pg_query($conn, "SELECT id FROM places WHERE title LIKE '%$search%'")); 
}

echo $admin_head;

echo pg_last_error();
?>


<div id="admin">
  <h3>
    <? if($total > $items_per_page) { ?>
      <?=$page_start+1?>-<? if($page_end > $total) { echo $total; } else { echo $page_end; } ?>
      of <?=$total?> markers
    <? } else { ?>
      <?=$total?> markers
    <? } ?>
  </h3>
  <ul>
    <?
      while($place = pg_fetch_assoc($places)) {
        $place[uri] = str_replace("http://", "", $place[uri]);
        $place[uri] = str_replace("https://", "", $place[uri]);
        $place[uri] = str_replace("www.", "", $place[uri]);
        echo "
          <li>
            <div class='options'>
              <a class='btn btn-small' href='edit.php?place_id=$place[id]&view=$view&search=$search&p=$p'>Edit</a>
              ";
              if($place[approved] == 1) {
                echo "
                  <a class='btn btn-small btn-success disabled'>Approve</a>
                  <a class='btn btn-small btn-inverse' href='index.php?task=hide&place_id=$place[id]&view=$view&search=$search&p=$p'>Reject</a>
                ";
              } else if(is_null($place[approved])) {
                echo "
                  <a class='btn btn-small btn-success' href='index.php?task=approve&place_id=$place[id]&view=$view&search=$search&p=$p'>Approve</a>
                  <a class='btn btn-small btn-inverse' href='index.php?task=hide&place_id=$place[id]&view=$view&search=$search&p=$p'>Reject</a>
                ";
              } else if($place[approved] == 0) {
                echo "
                  <a class='btn btn-small btn-success' href='index.php?task=approve&place_id=$place[id]&view=$view&search=$search&p=$p'>Approve</a>
                  <a class='btn btn-small btn-inverse disabled'>Reject</a>
                ";
              }
              echo "
              <a class='btn btn-small btn-danger' href='index.php?task=delete&place_id=$place[id]&view=$view&search=$search&p=$p'>Delete</a>
            </div>
            <div class='place_info'>
              <a href='http://$place[uri]' target='_blank'>
                $place[title]
                <span class='url'>
                  $place[uri]
                </span>
              </a>
            </div>
          </li>
        ";
      }
    ?>
  </ul>
  
  <? if($p > 1 || $total >= $items_per_page) { ?>
    <ul class="pager">
      <? if($p > 1) { ?>
        <li class="previous">
          <a href="index.php?view=<?=$view?>&search=<?=$search?>&p=<? echo $p-1; ?>">&larr; Previous</a>
        </li>
      <? } ?>
      <? if($total >= $items_per_page * $p) { ?>
        <li class="next">
          <a href="index.php?view=<?=$view?>&search=<?=$search?>&p=<? echo $p+1; ?>">Next &rarr;</a>
        </li>
      <? } ?>
    </ul>
  <? } ?>

</div>


<? echo $admin_foot ?>