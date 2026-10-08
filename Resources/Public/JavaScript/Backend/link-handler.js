import LinkBrowser from '@typo3/backend/link-browser.js';

document.addEventListener('click', (event) => {
    const link = event.target.closest('.recordlist [data-close]');
    const record = link?.closest('[data-table][data-uid]');
    if (record) {
        const template = document.body.dataset.typolinkTemplate;
        if (!template) return;
        event.preventDefault();
        LinkBrowser.finalizeFunction(template.replace('###RECORD_UID###', record.dataset.uid));
    }
});
