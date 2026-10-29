<?php
require_once __DIR__ . '/config.php';
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

$eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
if (!$eventId) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT e.*, e.seats - COALESCE((SELECT SUM(b.tickets) FROM bookings b WHERE b.event_id=e.id),0) AS seats_left FROM events e WHERE e.id=? AND e.event_date >= CURDATE()");
$stmt->execute([$eventId]);
$event = $stmt->fetch();
if (!$event) { http_response_code(404); exit('Event not found or booking has closed. <a href=\"index.php\">Return to events</a>'); }

$errors = [];
$name = $email = $phone = '';
$tickets = 1;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['attendee_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $tickets = filter_input(INPUT_POST, 'tickets', FILTER_VALIDATE_INT) ?: 1;
    if ($name === '' || mb_strlen($name) > 120) $errors[] = 'Please enter your name (up to 120 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160) $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) $errors[] = 'Please enter a valid phone number.';
    if ($tickets < 1 || $tickets > 10) $errors[] = 'You can book between 1 and 10 tickets.';
    if ($tickets > (int)$event['seats_left']) $errors[] = 'Not enough seats are available for that ticket quantity.';
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            // Lock the event row so simultaneous bookings cannot exceed capacity.
            $lock = $pdo->prepare("SELECT seats FROM events WHERE id=? FOR UPDATE");
            $lock->execute([$eventId]);
            $capacity = (int)$lock->fetchColumn();
            $count = $pdo->prepare("SELECT COALESCE(SUM(tickets),0) FROM bookings WHERE event_id=?");
            $count->execute([$eventId]);
            $remaining = $capacity - (int)$count->fetchColumn();
            if ($tickets > $remaining) {
                $pdo->rollBack();
                $errors[] = 'Seats changed while you were booking. Please choose a smaller quantity.';
            } else {
                $insert = $pdo->prepare("INSERT INTO bookings (event_id, attendee_name, email, phone, tickets) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([$eventId, $name, $email, $phone, $tickets]);
                $bookingId = (int)$pdo->lastInsertId();
                $pdo->commit();
                header('Location: confirmation.php?id=' . $bookingId);
                exit;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Booking could not be completed. Please try again.';
        }
    }
}
$date = new DateTime($event['event_date']);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Book Event | Eventify</title><link rel="stylesheet" href="style.css"></head><body>
<header class="navbar"><a class="brand" href="index.php"><span class="brand-icon">✦</span> Eventify</a><nav><a href="index.php#events">Explore Events</a><a href="my_bookings.php">My Bookings</a></nav></header>
<main class="form-page"><a class="back-link" href="index.php#events">← Back to events</a><div class="booking-layout">
<section class="form-panel"><span class="eyebrow">SAVE YOUR SPOT</span><h1>Book your <span>experience.</span></h1><p class="muted">Fill in your details to reserve your tickets.</p>
<?php if ($errors): ?><div class="alert error"><strong>Please check the following:</strong><ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="book.php" class="booking-form">
<input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
<label>Full name<input type="text" name="attendee_name" maxlength="120" required autocomplete="name" value="<?= h($name) ?>" placeholder="Enter your full name"></label>
<label>Email address<input type="email" name="email" maxlength="160" required autocomplete="email" value="<?= h($email) ?>" placeholder="you@example.com"></label>
<label>Phone number<input type="tel" name="phone" maxlength="20" required autocomplete="tel" value="<?= h($phone) ?>" placeholder="+91 98765 43210"></label>
<label>Number of tickets<select name="tickets"><?php for($i=1;$i<=min(10,max(1,(int)$event['seats_left']));$i++): ?><option value="<?= $i ?>" <?= $tickets===$i?'selected':'' ?>><?= $i ?> ticket<?= $i>1?'s':'' ?></option><?php endfor; ?></select></label>
<button class="button button-dark button-full" type="submit" <?= (int)$event['seats_left']<1?'disabled':'' ?>>Confirm booking <span>↗</span></button>
<p class="fine-print">Your booking will be saved to the event database.</p>
</form></section>
<aside class="summary-panel"><div class="summary-cover"><span class="eyebrow">EVENT SUMMARY</span><div class="summary-symbol">✦</div><span class="category-pill"><?= h($event['category']) ?></span></div><div class="summary-content"><h2><?= h($event['title']) ?></h2><p>◷ <?= $date->format('l, d F Y') ?> · <?= date('g:i A',strtotime($event['event_time'])) ?></p><p>⌖ <?= h($event['venue']) ?></p><hr><div class="summary-line"><span>Price per ticket</span><strong><?= $event['price']<=0?'Free':'₹'.number_format((float)$event['price'],2) ?></strong></div><div class="summary-line"><span>Available seats</span><strong><?= max(0,(int)$event['seats_left']) ?></strong></div><p class="summary-description"><?= h($event['description']) ?></p></div></aside>
</div></main><footer><a class="brand" href="index.php"><span class="brand-icon">✦</span> Eventify</a><span>Event Management System · Built with PHP & MySQL</span></footer></body></html>
