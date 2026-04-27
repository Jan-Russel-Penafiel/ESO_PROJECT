</main>
</div>

<script>
  const sb = document.getElementById('sidebar');
  const tg = document.getElementById('sidebarToggle');
  const bd = document.getElementById('sidebarBackdrop');
  const sc = document.getElementById('sidebarClose');

  function openSidebar() {
    sb.classList.remove('sidebar-hidden');
    if (bd) bd.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    sb.classList.add('sidebar-hidden');
    if (bd) bd.style.display = 'none';
    document.body.style.overflow = '';
  }

  if (tg && sb) tg.addEventListener('click', () =>
    sb.classList.contains('sidebar-hidden') ? openSidebar() : closeSidebar()
  );
  if (bd) bd.addEventListener('click', closeSidebar);
  if (sc) sc.addEventListener('click', closeSidebar);
  // Auto-close sidebar on nav link tap (mobile only)
  document.querySelectorAll('#sidebar nav a').forEach(a =>
    a.addEventListener('click', () => { if (window.innerWidth < 768) closeSidebar(); })
  );
</script>
</body>
</html>
