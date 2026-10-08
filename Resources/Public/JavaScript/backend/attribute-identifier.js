import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Notification from '@typo3/backend/notification.js';

document.addEventListener('click', async (event) => {
    const button = event.target.closest('.attributeIdentifier');
    if (!button) return;
    event.preventDefault();
    const scope = button.closest('.form-section') || document;
    const name = scope.querySelector('input[data-formengine-input-name$="[name]"]') || document.querySelector('input[data-formengine-input-name$="[name]"]');
    const identifier = scope.querySelector('input[data-formengine-input-name$="[identifier]"]') || document.querySelector('input[data-formengine-input-name$="[identifier]"]');
    if (!name || !identifier) return;
    try {
        const response = await new AjaxRequest(TYPO3.settings.ajaxUrls['pxa-pm-attribute-identifier-convert']).post({value: name.value});
        const data = await response.resolve();
        if (!data.success) throw new Error('Conversion failed');
        identifier.value = data.output;
        identifier.dispatchEvent(new Event('change', {bubbles: true}));
    } catch (error) {
        Notification.error('Product Manager', 'Der Bezeichner konnte nicht erzeugt werden.');
    }
});
