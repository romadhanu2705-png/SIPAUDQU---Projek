<?php
// includes/footer.php
?>
  </div><!-- .layout-container -->
</div><!-- .page-wrapper -->

<!-- FOOTER -->
<footer class="site-footer">
  <p>&copy; <?= date('Y') ?> <strong>SIPAUDQU</strong> &mdash; Sistem Informasi PAUD Qur'an. All rights reserved.</p>
  <p style="margin-top:4px; opacity:0.7;">Developed with Hamba Allah for PAUD Qur'an</p>
</footer>

<script>
function toggleSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  if(sidebar && overlay) {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
  }
}
</script>
</body>
</html>