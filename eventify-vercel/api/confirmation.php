<?php
require_once __DIR__ . '/config.php';
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT b.*, e.title, e.category, e.event_date, e.event_time, e.venue, e.price FROM bookings b JOIN events e ON e.id=b.event_id WHERE b.id=?");
$stmt->execute([$id ?: 0]);
$booking = $stmt->fetch();
if (!$booking) { http_response_code(404); exit('Booking not found. <a href=\"index.php\">Return to events</a>'); }
$total = (float)$booking['price'] * (int)$booking['tickets'];
$date = new DateTime($booking['event_date']);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Booking Confirmed | Eventify</title><link rel="stylesheet" href="style.css"></head><body>
<header class="navbar"><a class="brand" href="index.php"><span class="brand-icon">✦</span> Eventify</a><nav><a href="index.php#events">Explore Events</a><a href="my_bookings.php">My Bookings</a></nav></header>
<main class="confirmation-page"><div class="success-icon">✓</div><span class="eyebrow">IT'S OFFICIAL</span><h1>Your spot is <span>reserved!</span></h1><p class="muted">Thanks, <?= h($booking['attendee_name']) ?>. Your booking has been saved successfully.</p>
<section class="receipt"><div class="receipt-head"><div><span class="eyebrow">BOOKING CONFIRMATION</span><h2><?= h($booking['title']) ?></h2></div><span class="booking-number">#<?= (int)$booking['id'] ?></span></div><div class="receipt-grid"><div><small>DATE & TIME</small><strong><?= $date->format('d M Y') ?></strong><span><?= date('g:i A',strtotime($booking['event_time'])) ?></span></div><div><small>VENUE</small><strong><?= h($booking['venue']) ?></strong><span><?= h($booking['category']) ?></span></div><div><small>TICKETS</small><strong><?= (int)$booking['tickets'] ?> ticket<?= $booking['tickets']>1?'s':'' ?></strong><span><?= h($booking['attendee_name']) ?></span></div><div><small>TOTAL</small><strong><?= $total<=0?'Free':'₹'.number_format($total,2) ?></strong><span>Payment not collected online</span></div></div><div class="receipt-email">Confirmation contact: <?= h($booking['email']) ?></div></section>
<div class="confirmation-actions"><a class="button button-dark" href="my_bookings.php?email=<?= urlencode($booking['email']) ?>">View my bookings ↗</a><a class="button button-outline" href="index.php">Explore more events</a></div><p class="fine-print">Keep your booking number for your records.</p></main>
<footer><a class="brand" href="index.php"><span class="brand-icon">✦</span> Eventify</a><span>Event Management System · Built with PHP & MySQL</span></footer></body></html>
