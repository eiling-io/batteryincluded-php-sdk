(() => {
    'use strict';

    // light / dark mode
    document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
        const theme = document.documentElement.dataset.bsTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.bsTheme = theme;
        try {
            localStorage.setItem('bi-demo-theme', theme);
        } catch (e) {
            // storage not available, theme is kept for this page only
        }
    });

    // submit the filter form on every change of checkboxes and selects (also those linked by the form attribute)
    const filterForm = document.querySelector('form[data-auto-submit]');
    document.addEventListener('change', (event) => {
        const field = event.target;
        if (!filterForm || field.form !== filterForm || field.type === 'number') {
            return;
        }
        filterForm.requestSubmit();
    });
    filterForm?.addEventListener('submit', () => {
        // drop empty range inputs to keep the url short
        filterForm.querySelectorAll('input[type="number"]').forEach((input) => {
            input.disabled = input.value === '';
        });
        document.body.classList.add('opacity-75');
    });

    // search suggestions
    const searchForm = document.querySelector('form[data-suggest-url]');
    if (!searchForm) {
        return;
    }

    const input = searchForm.querySelector('input[name="q"]');
    const list = searchForm.querySelector('.suggest');
    const language = searchForm.querySelector('input[name="lang"]').value;
    let controller = null;
    let timer = null;
    let activeIndex = -1;

    const escape = (value) => String(value).replace(/[&<>"']/g, (char) => `&#${char.charCodeAt(0)};`);
    const options = () => [...list.querySelectorAll('.dropdown-item')];

    const close = () => {
        list.classList.remove('show');
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        activeIndex = -1;
    };

    const highlightOption = (index) => {
        const items = options();
        items.forEach((item) => item.classList.remove('active'));
        if (items.length === 0) {
            return;
        }
        activeIndex = (index + items.length) % items.length;
        items[activeIndex].classList.add('active');
        items[activeIndex].scrollIntoView({block: 'nearest'});
        input.setAttribute('aria-activedescendant', items[activeIndex].id);
    };

    const searchUrl = (query) => `index.php?${new URLSearchParams({q: query, lang: language})}`;

    const render = (data, query) => {
        let html = '';
        let id = 0;
        if (data.completions.length > 0) {
            html += `<h6 class="dropdown-header">${escape(list.dataset.labelSuggestions)}</h6>`;
            html += data.completions.map((completion) => `
                <a class="dropdown-item" role="option" id="suggest-${id++}" href="${escape(searchUrl(completion.value))}">
                    <i class="bi bi-search me-2 text-body-secondary" aria-hidden="true"></i>${completion.html}
                </a>`).join('');
        }
        if (data.products.length > 0) {
            html += `<h6 class="dropdown-header border-top">${escape(list.dataset.labelProducts)}</h6>`;
            html += data.products.map((product) => `
                <a class="dropdown-item suggest-product d-flex align-items-center gap-2" role="option" id="suggest-${id++}"
                   href="${escape(product.url || searchUrl(query))}">
                    <img src="${escape(product.image)}" alt="" loading="lazy">
                    <span class="text-truncate flex-grow-1">${product.html}<br><small class="text-body-secondary">${escape(product.ordernumber)}</small></span>
                    ${product.price ? `<span class="suggest-price text-end">
                        <span class="fw-semibold">${escape(product.price)}</span>
                        ${product.stock ? `<br><small class="${escape(product.stock.class)}">${escape(product.stock.label)}</small>` : ''}
                    </span>` : ''}
                </a>`).join('');
        }
        if (data.found > 0) {
            html += `<a class="dropdown-item border-top text-primary fw-semibold py-2" role="option" id="suggest-${id++}" href="${escape(searchUrl(query))}">
                ${escape(list.dataset.labelAll.replace('%s', data.found))} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>`;
        }

        list.innerHTML = html;
        activeIndex = -1;
        if (html === '') {
            close();
            return;
        }
        list.classList.add('show');
        input.setAttribute('aria-expanded', 'true');
    };

    const suggest = async (query) => {
        controller?.abort();
        controller = new AbortController();
        try {
            const url = `${searchForm.dataset.suggestUrl}?${new URLSearchParams({q: query, lang: language})}`;
            const response = await fetch(url, {signal: controller.signal});
            if (!response.ok) {
                close();
                return;
            }
            render(await response.json(), query);
        } catch (e) {
            if (e.name !== 'AbortError') {
                close();
            }
        }
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const query = input.value.trim();
        if (query.length < 2) {
            controller?.abort();
            close();
            return;
        }
        timer = setTimeout(() => suggest(query), 150);
    });

    input.addEventListener('keydown', (event) => {
        const isOpen = list.classList.contains('show');
        if (event.key === 'ArrowDown' && isOpen) {
            event.preventDefault();
            highlightOption(activeIndex + 1);
        } else if (event.key === 'ArrowUp' && isOpen) {
            event.preventDefault();
            highlightOption(activeIndex - 1);
        } else if (event.key === 'Enter' && isOpen && activeIndex >= 0) {
            event.preventDefault();
            options()[activeIndex].click();
        } else if (event.key === 'Escape') {
            close();
        }
    });

    input.addEventListener('focus', () => {
        if (list.innerHTML.trim() !== '' && input.value.trim().length >= 2) {
            list.classList.add('show');
            input.setAttribute('aria-expanded', 'true');
        }
    });

    document.addEventListener('click', (event) => {
        if (!searchForm.contains(event.target)) {
            close();
        }
    });
})();
