<?php
session_start();
require_once __DIR__ . '/db.php';

$_SESSION['visits'] = ($_SESSION['visits'] ?? 0) + 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $author = trim($_POST['author'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $rememberName = isset($_POST['remember_name']);

    if ($author !== '' && $body !== '') {

        if ($rememberName) {
            setcookie('remembered_name', $author, [
                'expires' => time() + (60 * 60 * 24 * 30),
                'path' => '/',
                'samesite' => 'Lax',
                'httponly' => true
            ]);
        } else {
            setcookie('remembered_name', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'samesite' => 'Lax',
                'httponly' => true
            ]);
        }

        $insert = $pdo->prepare(
            'INSERT INTO messages (author, body)
             VALUES (:author, :body)'
        );

        $insert->execute([
            ':author' => $author,
            ':body' => $body
        ]);
    }

    header('Location: index.php');
    exit;
}

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $select = $pdo->prepare(
        'SELECT * FROM messages
         WHERE author ILIKE :search
         OR body ILIKE :search
         ORDER BY created_at DESC, id DESC'
    );

    $select->execute([
        ':search' => '%' . $search . '%'
    ]);
} else {

    $select = $pdo->query(
        'SELECT * FROM messages
         ORDER BY created_at DESC, id DESC'
    );
}

$messages = $select->fetchAll();

$rememberedName = $_COOKIE['remembered_name'] ?? '';

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FREEDOM | A Moment of Peace</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="page">


        <video autoplay muted loop playsinline id="background-video">
            <source src="assets/background.mp4" type="video/mp4">
            Your browser does not support video backgrounds.
        </video>

        <div class="background-overlay"></div>

        <header class="navbar">

            <a href="index.php" class="brand">
                <span class="brand-icon">FW</span>
                <span>FREEDOM</span>
            </a>

            <div class="nav-description">
                A quiet place for shared thoughts.
            </div>

            <div class="visit-counter">
                VISITS
                    <strong><?= e($_SESSION['visits']) ?></strong>
            </div>

        </header>


        <!-- HERO SECTION -->
        <section class="hero">

            <div class="hero-content">

                <p class="eyebrow">WELCOME TO FREEDOM WALL</p>

                <h1>
                    A little space
                    <br>
                    <span>for your thoughts.</span>
                </h1>

                <p class="hero-description">
                    Sometimes, a few words are enough.
                    Leave a message, share an experience,
                    or simply take a moment to read what
                    others have to say.
                </p>

                <a href="#write-message" class="hero-link">
                    Leave a message <span>↓</span>
                </a>

            </div>


            <!-- HERO ART -->
            <div class="hero-art">

                <div class="art-orbit orbit-one"></div>
                <div class="art-orbit orbit-two"></div>
                <div class="art-orbit orbit-three"></div>

                <div class="art-glow"></div>

                <div class="art-center">
                    <img src="assets/badApple.png" alt="woman">
                </div>

                <div class="art-caption">
                    <span>A MOMENT OF PEACE</span>
                    <small>— FREEDOM</small>
                </div>

            </div>

        </section>


        <!-- INTRODUCTION -->
        <section class="intro-section">

            <div>
                <p class="eyebrow">A SHARED JOURNAL</p>
                <h2>Every thought has a place.</h2>
            </div>

            <p>
                A collection of small moments, ideas,
                and messages from our community.
            </p>

        </section>


        <!-- MESSAGE COMPOSER -->
        <section class="composer" id="write-message">

            <div class="section-heading">

                <p class="eyebrow">TAKE YOUR TIME</p>

                <h2>Write something.</h2>

                <p>
                    It doesn't have to be perfect.
                    Just write what is on your mind.
                </p>

            </div>


            <form method="POST" action="index.php" class="form-card">

                <div class="form-top">
                    <span class="form-icon">✎</span>
                    <span>NEW MESSAGE</span>
                </div>

                <label for="author">Your name</label>

                <input
                    type="text"
                    id="author"
                    name="author"
                    maxlength="100"
                    placeholder="Enter your name..."
                    value="<?= e($rememberedName) ?>"
                    required>

                <label for="body">Your message</label>

                <textarea
                    id="body"
                    name="body"
                    maxlength="2000"
                    placeholder="Write something you'd like to share..."
                    required></textarea>


                <div class="form-bottom">

                    <label class="remember-option">

                        <input
                            type="checkbox"
                            name="remember_name"
                            value="1"
                            <?= $rememberedName !== '' ? 'checked' : '' ?>>

                        <span>Remember my name</span>

                    </label>

                    <button type="submit" class="submit-button">
                        Post message <span>↗</span>
                    </button>

                </div>

                <p class="form-note">
                    Your message will be visible to everyone.
                </p>

            </form>

        </section>


        <!-- COMMUNITY WALL -->
        <section class="wall" id="community-wall">

            <div class="wall-header">

                <div>
                    <p class="eyebrow">FROM OUR COMMUNITY</p>
                    <h2>The message wall.</h2>
                    <p class="wall-description">
                        Little thoughts left by everyone.
                    </p>
                </div>

                <span class="message-count">
                    <?= count($messages) ?>
                    <?= count($messages) === 1 ? 'MESSAGE' : 'MESSAGES' ?>
                </span>

            </div>


            <!-- SEARCH -->
            <form method="GET" action="index.php" class="search-form">

                <span class="search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    placeholder="Find a name or message..."
                    value="<?= e($search) ?>">

                <?php if ($search !== ''): ?>

                    <a href="index.php" class="clear-search">
                        Clear
                    </a>

                <?php endif; ?>

                <button type="submit">Search</button>

            </form>


            <?php if ($search !== ''): ?>

                <p class="search-result">
                    Showing results for "<?= e($search) ?>"
                </p>

            <?php endif; ?>


            <!-- MESSAGES -->
            <div class="message-list">

                <?php if (count($messages) === 0): ?>

                    <div class="empty-state">

                        <span>✳</span>

                        <h3>No messages yet.</h3>

                        <p>
                            <?php if ($search !== ''): ?>

                                We couldn't find anything.
                                Try another search.

                            <?php else: ?>

                                This space is still waiting
                                for its first message.

                            <?php endif; ?>
                        </p>

                        <?php if ($search !== ''): ?>

                            <a href="index.php">
                                View all messages
                            </a>

                        <?php endif; ?>

                    </div>

                <?php else: ?>

                    <?php foreach ($messages as $message): ?>

                        <article class="message-card">

                            <div class="message-header">

                                <div class="avatar">
                                    <?= e(
                                        strtoupper(
                                            substr($message['author'], 0, 1)
                                        )
                                    ) ?>
                                </div>

                                <div class="author-details">

                                    <h3>
                                        <?= e($message['author']) ?>
                                    </h3>

                                    <time>
                                        <?= e(
                                            date(
                                                'M j, Y · g:i A',
                                                strtotime($message['created_at'])
                                            )
                                        ) ?>
                                    </time>

                                </div>

                            </div>

                            <p class="message-body">
                                <?= e($message['body']) ?>
                            </p>

                        </article>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>


            <div class="wall-footer">

                <p>THANK YOU FOR BEING HERE</p>

            </div>

        </section>


        <!-- FOOTER -->
        <footer class="footer">

            <a href="index.php" class="brand">
                <span class="brand-icon">FW</span>
                <span>FREEDOM</span>
            </a>

            <p>A quiet corner for our community.</p>

            <small>
                SESSION VISITS: <?= e($_SESSION['visits']) ?>
            </small>

        </footer>

    </div>

    <audio id="background-audio" loop autoplay>
        <source src="assets/music.mp3" type="audio/mpeg">
        Your browser does not support audio.
    </audio>

</body>

</html>