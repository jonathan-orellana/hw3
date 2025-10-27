<div class="game-over">
  <h2>Game Over</h2>
  <p>Final score: <?= (int)$score ?></p>
  <p>Valid words: <?= htmlspecialchars(implode(', ', $validGuesses)) ?></p>
  <p>Invalid guesses: <?= (int)$invalidCount ?></p>
  <form action="?command=play" method="GET">
    <button>Play again</button>
  </form>
  <form action="?command=logout" method="POST">
    <button id="exit-button">Exit</button>
  </form>
</div>
