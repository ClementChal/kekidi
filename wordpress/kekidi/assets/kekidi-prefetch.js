(() => {
  const start = () => {
    if (!('IntersectionObserver' in window) || typeof HTMLScriptElement.supports !== 'function' || !HTMLScriptElement.supports('speculationrules')) return;

    const prefetched = new Set();
    const observer = new IntersectionObserver((entries, currentObserver) => {
      for (const entry of entries) {
        if (entry.intersectionRatio <= 0) continue;

        const link = entry.target;
        currentObserver.unobserve(link);
        if (link.hasAttribute('download') || link.relList.contains('nofollow') || link.closest('.no-prefetch')) continue;

        let url;
        try {
          url = new URL(link.href, location.href);
        } catch {
          continue;
        }
        if (url.origin !== location.origin || url.search || /^\/(?:wp-admin|wp-login\.php|wp-json)(?:\/|$)/.test(url.pathname)) continue;
        if (url.pathname === location.pathname && url.search === location.search) continue;

        url.hash = '';
        if (prefetched.has(url.href)) continue;
        prefetched.add(url.href);

        const rule = document.createElement('script');
        rule.type = 'speculationrules';
        rule.textContent = JSON.stringify({ prefetch: [{ urls: [url.href] }] });
        document.head.append(rule);
      }
    }, { rootMargin: '0px', threshold: 0 });

    document.querySelectorAll('a[href]').forEach((link) => observer.observe(link));
  };

  if (document.readyState === 'complete') start();
  else window.addEventListener('load', start, { once: true });
})();
