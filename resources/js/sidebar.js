document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.getElementById('app-sidebar');
  const toggle = document.getElementById('sidebar-toggle');
  const mobileBtn = document.getElementById('mobile-menu-btn');
  const mobileOverlay = document.getElementById('mobile-sidebar-overlay');
  const mobileContent = document.getElementById('mobile-sidebar-content');
  const mobileClose = document.getElementById('mobile-sidebar-close');

  if (!sidebar) return;

  // restore collapsed state
  const collapsed = localStorage.getItem('sidebar-collapsed') === '1';
  if (collapsed) sidebar.classList.add('collapsed');

  if (toggle) {
    toggle.addEventListener('click', function () {
      const isCollapsed = sidebar.classList.toggle('collapsed');
      localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
      const icon = toggle.querySelector('i');
      if (icon) icon.classList.toggle('fa-chevron-right', isCollapsed);
    });
  }

  // Mobile overlay: clone sidebar content into panel
  if (mobileBtn && mobileOverlay && mobileContent) {
    mobileBtn.addEventListener('click', function () {
      // populate content
      mobileContent.innerHTML = '';
      const inner = sidebar.querySelector('.relative.z-10');
      if (inner) {
        const clone = inner.cloneNode(true);
        // remove any id attributes inside clone
        clone.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));
        mobileContent.appendChild(clone);
      }
      mobileOverlay.classList.remove('hidden');
      mobileOverlay.classList.add('flex');
    });

    mobileClose.addEventListener('click', function () {
      mobileOverlay.classList.add('hidden');
      mobileOverlay.classList.remove('flex');
    });

    mobileOverlay.addEventListener('click', function (e) {
      if (e.target === mobileOverlay) {
        mobileOverlay.classList.add('hidden');
        mobileOverlay.classList.remove('flex');
      }
    });
  }
});
