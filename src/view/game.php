<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Game</title>
  <link rel="stylesheet" href="static/styles/game.css">
</head>
<body>
  <section id="game-section">
    <h2>Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
    <?php if (!empty($flash)): ?><p><?= htmlspecialchars($flash) ?></p><?php endif; ?>
    <p>Score: <?= (int)$score ?></p>
    <div class="letters">
      <?php foreach (str_split($letters) as $ch): ?>
        <span class="letter"><?= htmlspecialchars($ch) ?></span>
      <?php endforeach; ?>
    </div>

    <form class="guess-form" action="?command=guess" method="POST">
      <input class="guess-input" name="guess" placeholder="Enter word" required>
      <button class="button">Submit</button>
    </form>
    <div class="other-button">
      <form action="?command=reshuffle" method="POST" style="display:inline"><button class="button reshuffle-button">Reshuffle</button></form>
      <form action="?command=quit" method="POST" style="display:inline"><button class="button quit-button">Quit</button></form>
    </div>

    <h3>Valid guesses</h3>
    <ul class="valid-guessed-list">
    <?php foreach ($validGuesses as $w): ?>
      <li class="valid-guessed"><?= htmlspecialchars($w) ?></li>
    <?php endforeach; ?>
    </ul>
  </section>
</body>
</html>

