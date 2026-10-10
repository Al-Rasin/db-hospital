<?php
// Small helpers used by every page.

// Escape output so that names with < or " do not break the HTML.
function e($text)
{
    return htmlspecialchars($text ?? "", ENT_QUOTES, "UTF-8");
}

// Run a SELECT with parameters and return all rows as an array.
function fetch_all($conn, $sql, $params = [], $types = "")
{
    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        if ($types === "") {
            $types = str_repeat("s", count($params));
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

// Same as fetch_all but returns only the first row (or null).
function fetch_one($conn, $sql, $params = [], $types = "")
{
    $rows = fetch_all($conn, $sql, $params, $types);
    return $rows ? $rows[0] : null;
}

// Run an INSERT / UPDATE / DELETE.
function run_query($conn, $sql, $params = [], $types = "")
{
    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        if ($types === "") {
            $types = str_repeat("s", count($params));
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Count rows of a simple COUNT(*) query.
function count_rows($conn, $sql, $params = [])
{
    $row = fetch_one($conn, $sql, $params);
    return $row ? (int) array_values($row)[0] : 0;
}

// Store a one time message shown on the next page.
function set_message($text, $type = "success")
{
    $_SESSION["flash"] = ["text" => $text, "type" => $type];
}

function show_message()
{
    if (empty($_SESSION["flash"])) {
        return;
    }
    $flash = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    echo '<div class="alert alert-' . e($flash["type"]) . ' alert-dismissible fade show">';
    echo '<i class="bi bi-info-circle me-1"></i> ' . e($flash["text"]);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

// Coloured badge for an appointment status.
function status_badge($status)
{
    if ($status == "done") {
        return '<span class="badge rounded-pill text-bg-success"><i class="bi bi-check-circle me-1"></i>Done</span>';
    }
    if ($status == "cancelled") {
        return '<span class="badge rounded-pill text-bg-secondary"><i class="bi bi-x-circle me-1"></i>Cancelled</span>';
    }
    return '<span class="badge rounded-pill text-bg-warning"><i class="bi bi-hourglass-split me-1"></i>Pending</span>';
}

function money($amount)
{
    return number_format((float) $amount, 2) . " Tk";
}

function show_date($value, $format = "d M Y, h:i A")
{
    return $value ? date($format, strtotime($value)) : "-";
}
?>
