<?php
require_once __DIR__ . '/config.php';

$category = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');

$sql = "SELECT e.*,
        e.seats - COALESCE((SELECT SUM(b.tickets) FROM bookings b WHERE b.event_id = e.id), 0) AS seats_left
        FROM events e WHERE e.event_date >= CURDATE()";
$params = [];
if ($category !== '') {
    $sql .= " AND e.category = ?";
    $params[] = $category;
}
if ($search !== '') {
    $sql .= " AND (e.title LIKE ? OR e.venue LIKE ? OR e.description LIKE ?)";
    $like = "%$search%";
    array_push($params, $like, $like, $like);
}
$sql .= " ORDER BY e.event_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM events ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function money($value) { return $value <= 0 ? 'Free' : '₹' . number_format((float)$value, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eventify | Event Management System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="navbar">
  <a class="brand" href="index.php"><span class="brand-icon">✦</span> Eventify</a>
  <nav><a href="#events">Explore Events</a><a href="my_bookings.php">My Bookings</a><a class="nav-button" href="#events">Find an Event</a></nav>
</header>
<main>
<section class="hero">
  <div class="hero-copy">
    <span class="eyebrow">YOUR MOMENTS. BEAUTIFULLY PLANNED.</span>
    <h1>Make every event<br><span>unforgettable.</span></h1>
    <p>Discover inspiring experiences, book your spot, and make memories that last. Your next great experience starts here.</p>
    <a class="button button-light" href="#events">Explore Events <span>↗</span></a>
    <div class="hero-note"><span class="avatar-stack">● ● ●</span> Great experiences are waiting for you</div>
  </div>
  <div class="hero-art" aria-label="Decorative event illustration">
    <div class="art-circle circle-one"></div><div class="art-circle circle-two"></div>
    <div class="ticket-card"><div class="ticket-top">YOUR NEXT EXPERIENCE</div><div class="ticket-star">✦</div><h3>Good times.<br>Great people.</h3><div class="ticket-bottom"><span>EVENTIFY</span><span>EST. 2026</span></div></div>
    <div class="floating-tag tag-top">✧ Discover</div><div class="floating-tag tag-bottom">♥ Make memories</div>
  </div>
</section>
<section class="stats">
  <div><strong><?= count($events) ?>+</strong><span>Upcoming events</span></div>
  <div><strong>Easy</strong><span>Online booking</span></div>
  <div><strong>One place</strong><span>All your plans</span></div>
</section>
<section class="events-section" id="events">
  <div class="section-heading"><div><span class="eyebrow">FIND YOUR NEXT EXPERIENCE</span><h2>Upcoming events<span class="accent-dot">.</span></h2><p>Handpicked moments worth showing up for.</p></div></div>
  <form class="filters" method="get" action="index.php">
    <div class="search-field"><span>⌕</span><input type="search" name="search" placeholder="Search events or venues..." value="<?= h($search) ?>"></div>
    <select name="category" aria-label="Filter by category"><option value="">All categories</option>
      <?php foreach ($categories as $c): ?><option value="<?= h($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= h($c) ?></option><?php endforeach; ?>
    </select>
    <button class="button button-dark" type="submit">Search events</button>
    <?php if ($search !== '' || $category !== ''): ?><a class="clear-link" href="index.php">Clear</a><?php endif; ?>
  </form>
  <?php if (!$events): ?>
    <div class="empty-state"><div>✧</div><h3>No events found</h3><p>Try another search or category.</p><a href="index.php">View all events</a></div>
  <?php else: ?>
  <div class="event-grid">
    <?php
    $icons = ['Technology' => '⌘', 'Workshop' => '✳', 'Entertainment' => '♫', 'Business' => '↗'];
    $colors = ['Technology' => 'lavender', 'Workshop' => 'peach', 'Entertainment' => 'pink', 'Business' => 'mint'];
    foreach ($events as $event):
      $date = new DateTime($event['event_date']);
      $left = max(0, (int)$event['seats_left']);
    ?>
    <article class="event-card">
      <div class="event-cover <?= h($colors[$event['category']] ?? 'lavender') ?>">
        <span class="category-pill"><?= h($event['category']) ?></span>
        <span class="cover-icon"><?= h($icons[$event['category']] ?? '✦') ?></span>
        <div class="cover-word"><?= h(strtoupper($event['category'])) ?></div>
        <div class="date-sticker"><strong><?= $date->format('d') ?></strong><span><?= strtoupper($date->format('M')) ?></span></div>
      </div>
      <div class="event-info">
        <div class="event-meta"><span>◷ <?= date('g:i A', strtotime($event['event_time'])) ?></span><span>♧ <?= $left ?> seats left</span></div>
        <h3><?= h($event['title']) ?></h3>
        <p class="venue">⌖ <?= h($event['venue']) ?></p>
        <p class="description"><?= h($event['description']) ?></p>
        <div class="card-footer"><div class="price"><?= money($event['price']) ?><small> / person</small></div>
          <?php if ($left > 0): ?><a class="book-link" href="book.php?event_id=<?= (int)$event['id'] ?>">Book now <span>↗</span></a><?php else: ?><span class="sold-out">Sold out</span><?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<section class="bottom-cta"><div><span class="eyebrow">YOUR NEXT CHAPTER STARTS HERE</span><h2>Make plans. Make memories.</h2><p>Find something exciting to look forward to.</p></div><a class="button button-light" href="#events">Browse events ↗</a></section>
</main>
<footer><a class="brand" href="index.php"><span class="brand-icon">✦</span> Eventify</a><span>Event Management System · Built with PHP & MySQL</span><a href="my_bookings.php">Manage bookings ↗</a></footer>
</body>
</html>
