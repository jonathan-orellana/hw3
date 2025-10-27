<?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form class="sign-in" action="?command=login" method="POST">
  <label>Name <input name="name" required></label><br>
  <label>Email <input type="email" name="email" required></label><br>
  <label>Password <input type="password" name="password" required></label><br>
  <div class="start-button-container">
    <button>Start</button>
  </div>
</form>
