pimcore.registerNS("pimcore.plugin.TorqITPortableClassificationStoreBundle");

pimcore.plugin.TorqITPortableClassificationStoreBundle = Class.create({

    initialize: function () {
        document.addEventListener(pimcore.events.pimcoreReady, this.pimcoreReady.bind(this));
    },

    pimcoreReady: function (e) {
        // alert("TorqITPortableClassificationStoreBundle ready!");
    }
});

var TorqITPortableClassificationStoreBundlePlugin = new pimcore.plugin.TorqITPortableClassificationStoreBundle();
