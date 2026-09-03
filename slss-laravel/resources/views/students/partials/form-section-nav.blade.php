{{-- Sticky jump navigation for the long student forms. Builds one pill per
     .section-header on the page and highlights the section in view.
     Styles live in public/css/slss.css (.form-section-nav). --}}
<nav class="form-section-nav no-print" id="formSectionNav" aria-label="Form sections"></nav>

@push('scripts')
<script>
(function() {
    const nav = document.getElementById('formSectionNav');
    const sections = document.querySelectorAll('.section-header');
    if (!nav || sections.length < 2) {
        nav?.remove();
        return;
    }

    sections.forEach(function(section, index) {
        section.id = section.id || 'form-section-' + index;

        // Native anchor navigation: scroll-margin-top on .section-header keeps
        // the target clear of the fixed header, and html { scroll-behavior }
        // makes it smooth — no scripted scrolling needed.
        const link = document.createElement('a');
        link.href = '#' + section.id;
        link.textContent = section.textContent.trim();
        nav.appendChild(link);
    });

    // Highlight the section currently in view
    const links = nav.querySelectorAll('a');
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (!entry.isIntersecting) return;
            const index = Array.prototype.indexOf.call(sections, entry.target);
            links.forEach(function(l, i) { l.classList.toggle('active', i === index); });
        });
    }, { rootMargin: '-120px 0px -70% 0px' });

    sections.forEach(function(section) { observer.observe(section); });
})();
</script>
@endpush
