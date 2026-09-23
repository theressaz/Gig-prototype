<?php
declare(strict_types=1);
?>
      </main>
      <div class="toast-container" id="toastContainer"></div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.profile-menu')) {
      document.querySelectorAll('.profile-dropdown').forEach(function (drop) {
        drop.classList.remove('open');
      });
    }
  });
  function showToast(message) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'toast-card';
    toast.innerHTML = '<span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(function () { toast.remove(); }, 300);
    }, 3500);
  }
</script>
</body>
</html>
