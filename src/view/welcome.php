<?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form action="?command=login" method="POST">
  <label>Name <input name="name" required></label><br>
  <label>Email <input type="email" name="email" required></label><br>
  <label>Password <input type="password" name="password" required></label><br>
  <button>Start</button>
</form>
