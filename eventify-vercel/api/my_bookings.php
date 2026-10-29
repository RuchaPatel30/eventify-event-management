<?php
require_once __DIR__ . '/config.php';
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$email = trim($_GET['email'] ?? '');
$bookings = [];
$searched = $email !== '';
$error = '';
if ($searched) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT b.*, e.title, e.event_date, e.event_time, e.venue, e.price FROM bookings b JOIN events e ON e.id=b.event_id WHERE b.email=? ORDER BY b.created_at DESC");
        $stmt->execute([$email]);
        $bookings = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Bookings | Eventify</title><link rel="stylesheet" href="style.css"></head><body>
<header class="navbar"><a class="brand" href="index.php"><span class="brand-icon">✦</span> Eventify</a><nav><a href="index.php#events">Explore Events</a><a class="nav-button" href="index.php#events">Find an Event</a></nav></header>
<main class="my-bookings-page"><a class="back-link" href="index.php">← Back to events</a><span class="eyebrow">YOUR EVENT PLANS</span><h1>My <span>bookings.</span></h1><p class="muted">Enter the email address used during booking to find your reservations.</p>
<form class="lookup-form" method="get"><input type="email" name="email" required placeholder="Enter your booking email" value="<?= h($email) ?>"><button class="button button-dark" type="submit">Find bookings</button></form>
<?php if($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<?php if($searched && !$error && !$bookings): ?><div class="empty-state"><div>✧</div><h3>No bookings found</h3><p>Check your email address or explore upcoming events.</p><a href="index.php#events">Explore events</a></div><?php endif; ?>
<?php if($bookings): ?><div class="booking-list"><?php foreach($bookings as $b): ?><article class="booking-row"><div class="booking-date"><strong><?= date('d',strtotime($b['event_date'])) ?></strong><span><?= strtoupper(date('M',strtotime($b['event_date']))) ?></span></div><div class="booking-details"><h3><?= h($b['title']) ?></h3><p>Booking #<?= (int)$b['id'] ?> · <?= date('g:i A',strtotime($b['event_time'])) ?></p><p>⌖ <?= h($b['venue']) ?></p></div><div class="booking-total"><strong><?= (float)$b['price']<=0?'Free':'₹'.number_format((float)$b['price']*(int)$b['tickets'],2) ?></strong><span><?= (int)$b['tickets'] ?> ticket(s)</span></div></article><?php endforeach; ?></div><?php endif; ?>
</main><footer><a class="brand" href="index.php"><span class="brand-icon">✦</span> Eventify</a><span>Event Management System · Built with PHP & MySQL</span></footer></body></html>
