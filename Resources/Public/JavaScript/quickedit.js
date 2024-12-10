import e from "@typo3/backend/storage/persistent.js";

let i = {
    toggle: ".quick-edit-toggle-button",
    container: "#quick-edit",
    getIdentifier: function (t) {
        return "quickedit.visible." + t
    }
};

const container = document.querySelector(i.container);

if (container) {
    container.addEventListener("shown.bs.collapse", function () {
        const n = this;
        const o = i.getIdentifier(n.dataset.page);
        e.set(o, 1);
    });

    container.addEventListener("hidden.bs.collapse", function () {
        const n = this;
        const o = i.getIdentifier(n.dataset.page);
        e.set(o, 0);
    });
};
