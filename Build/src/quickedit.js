import PersistentStorage from "@typo3/backend/storage/persistent.js";

let Quickedit = {
    toggle: '.quick-edit-toggle-button',
    container: '#quick-edit'
};

Quickedit.getIdentifier = function(pageId) {
    return 'quickedit.visible.' + pageId;
}

const container = document.querySelector(Quickedit.container);

if (container) {
    container.addEventListener("shown.bs.collapse", function () {
        const identifier = Quickedit.getIdentifier(this.dataset.page);
        PersistentStorage.set(identifier, 1);
    });

    container.addEventListener("hidden.bs.collapse", function () {
        const identifier = Quickedit.getIdentifier(this.dataset.page);
        PersistentStorage.set(identifier, 0);
    });
}
